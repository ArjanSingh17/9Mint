// Presets (tweak cols/rows/tilt)
export const PRESETS = [
    { minWidth: 0, cols: 5, rows: 3, tiltDeg: 14 }, // default
    { minWidth: 600, cols: 8, rows: 3, tiltDeg: 16 },
    { minWidth: 900, cols: 12, rows: 3, tiltDeg: 17 },
    { minWidth: 1200, cols: 14, rows: 3, tiltDeg: 18 },
];

// Extra off-screen columns
export const BUFFER_COLS = 3;

// Math
export function mod(n, m) {
    return ((n % m) + m) % m;
}

export function floorDiv(n, d) {
    return Math.floor(n / d);
}

// RNG
export function seededRng(seed) {
    let state = (Math.abs(seed) || 1) >>> 0;
    return () => {
        state = (1664525 * state + 1013904223) >>> 0;
        return state / 4294967296;
    };
}

export function seededShuffle(array, rng) {
    const arr = [...(array || [])];
    for (let i = arr.length - 1; i > 0; i--) {
        const j = Math.floor(rng() * (i + 1));
        [arr[i], arr[j]] = [arr[j], arr[i]];
    }
    return arr;
}

// Keys
export function nftKey(nft) {
    if (!nft) return '';
    const name = (nft.name || '').toLowerCase();
    const collection = (nft.collection_slug || nft.collection_name || '').toLowerCase();
    const image = (nft.image_url || '').toLowerCase();
    return `${name}::${collection}::${image}`;
}

export function collectionKey(nft) {
    if (!nft || nft.isPlaceholder) return '';
    return (nft.collection_slug || nft.collection_name || '').toLowerCase();
}

// Lists
export function uniqueByKey(nfts) {
    const map = new Map();
    for (const nft of nfts || []) {
        const key = nftKey(nft);
        if (key && !map.has(key)) {
            map.set(key, nft);
        }
    }
    return Array.from(map.values());
}

// Preset pick
export function pickPreset(width) {
    let chosen = PRESETS[0];
    for (const preset of PRESETS) {
        if (width >= preset.minWidth) {
            chosen = preset;
        }
    }
    return chosen;
}

// Windowed render
export function computeVisibleRange({ scrollX, pitch, viewportWidth, bufferCols = BUFFER_COLS }) {
    if (pitch <= 0) {
        return { leftIndex: 0, rightIndex: 0 };
    }
    const leftIndex = Math.floor(scrollX / pitch) - bufferCols;
    const rightIndex = Math.ceil((scrollX + viewportWidth) / pitch) + bufferCols;
    return { leftIndex, rightIndex };
}

export function buildWindowColumns({
    leftIndex,
    rightIndex,
    uniqueNfts,
    rows,
    seed,
    colsPerCycle,
}) {
    const windowCols = rightIndex - leftIndex + 1;
    const cycleCols = Math.max(1, colsPerCycle || windowCols);
    const cycleCells = cycleCols * rows;

    // If no NFTs, all placeholders
    if (!uniqueNfts || uniqueNfts.length === 0) {
        const cols = [];
        for (let colIndex = leftIndex; colIndex <= rightIndex; colIndex++) {
            const items = [];
            for (let row = 0; row < rows; row++) {
                items.push({
                    isPlaceholder: true,
                    _key: `placeholder-${colIndex}-r${row}`,
                });
            }
            cols.push({
                colIndex,
                isStaggered: Math.abs(colIndex) % 2 === 1,
                items,
            });
        }
        return cols;
    }

    const seedBase = (Number(seed) || 1) >>> 0;
    const deckCache = new Map();

    const getDeckForCycle = (cycleIndex) => {
        if (deckCache.has(cycleIndex)) return deckCache.get(cycleIndex);

        const mixed = (seedBase ^ ((cycleIndex * 2654435761) | 0)) >>> 0;
        const rng = seededRng(mixed);

        const shuffledNfts = seededShuffle(uniqueNfts, rng);
        const take = Math.min(shuffledNfts.length, cycleCells);

        const deck = shuffledNfts.slice(0, take);

        const placeholdersNeeded = Math.max(0, cycleCells - take);
        for (let i = 0; i < placeholdersNeeded; i++) deck.push({ isPlaceholder: true });

        const finalDeck = placeholdersNeeded > 0 ? seededShuffle(deck, rng) : deck;
        deckCache.set(cycleIndex, finalDeck);
        return finalDeck;
    };

    const cols = [];
    for (let colIndex = leftIndex; colIndex <= rightIndex; colIndex++) {
        const cycleIndex = floorDiv(colIndex, cycleCols);
        const colInCycle = mod(colIndex, cycleCols);
        const deck = getDeckForCycle(cycleIndex);

        const items = [];
        for (let row = 0; row < rows; row++) {
            const pos = colInCycle * rows + row;
            const entry = deck[pos];
            if (entry && !entry.isPlaceholder) {
                const idPart = entry.id ?? nftKey(entry) ?? `${pos}`;
                items.push({
                    ...entry,
                    isPlaceholder: false,
                    _key: `nft-${colIndex}-r${row}-${idPart}`,
                });
            } else {
                items.push({
                    isPlaceholder: true,
                    _key: `placeholder-${colIndex}-r${row}`,
                });
            }
        }
        cols.push({
            colIndex,
            isStaggered: Math.abs(colIndex) % 2 === 1,
            items,
        });
    }

    return cols;
}

/**
 * Build a column deterministically from its global index.
 * The same colIndex + uniqueNfts + rows will always produce the same result.
 */
export function buildColumnByIndex({ colIndex, uniqueNfts, rows, totalNfts }) {
    if (!uniqueNfts || uniqueNfts.length === 0) {
        // All placeholders if no NFTs
        return Array.from({ length: rows }, (_, row) => ({
            isPlaceholder: true,
            _key: `placeholder-${colIndex}-r${row}`,
        }));
    }

    // Create a seeded RNG based on column index for deterministic results
    const seed = colIndex * 2654435761; // Knuth's multiplicative hash
    const rng = seededRng(seed);

    // Determine how many placeholders this column should have (for scarcity)
    // Use a simple rule: if we have fewer NFTs than cells in the visible window,
    // spread placeholders deterministically
    const placeholderProbability = totalNfts < rows ? (rows - totalNfts) / rows : 0;

    // Shuffle row order deterministically
    const rowOrder = seededShuffle(Array.from({ length: rows }, (_, i) => i), rng);

    // Pick NFTs for this column deterministically
    // Use colIndex to offset into the NFT pool so adjacent columns differ
    const col = Array.from({ length: rows }, () => null);
    const usedInColumn = new Set();

    for (const row of rowOrder) {
        // Decide if this cell should be a placeholder (for scarcity)
        if (placeholderProbability > 0 && rng() < placeholderProbability) {
            col[row] = {
                isPlaceholder: true,
                _key: `placeholder-${colIndex}-r${row}`,
            };
            continue;
        }

        // Pick an NFT deterministically based on colIndex + row
        // Use multiple hash iterations to find an unused NFT in this column
        let nft = null;
        for (let attempt = 0; attempt < uniqueNfts.length; attempt++) {
            const idx = Math.floor(rng() * uniqueNfts.length);
            const candidate = uniqueNfts[idx];
            const key = nftKey(candidate);
            if (!usedInColumn.has(key)) {
                nft = candidate;
                usedInColumn.add(key);
                break;
            }
        }

        if (nft) {
            col[row] = {
                ...nft,
                isPlaceholder: false,
                _key: `nft-${colIndex}-r${row}-${nft.id}`,
            };
        } else {
            // Fallback to placeholder if we somehow can't find an NFT
            col[row] = {
                isPlaceholder: true,
                _key: `placeholder-${colIndex}-r${row}`,
            };
        }
    }

    return col;
}

/**
 * Get or build a column from cache.
 * Cache is keyed by colIndex and invalidated when uniqueNfts or rows change.
 */
export function getOrBuildColumn({ colIndex, uniqueNfts, rows, cache }) {
    const cacheKey = colIndex;
    if (cache.has(cacheKey)) {
        return cache.get(cacheKey);
    }

    const column = buildColumnByIndex({
        colIndex,
        uniqueNfts,
        rows,
        totalNfts: uniqueNfts.length,
    });

    cache.set(cacheKey, column);
    return column;
}

// ----- Legacy exports (kept for compatibility but no longer used) -----

export function shuffle(array) {
    const arr = [...(array || [])];
    for (let i = arr.length - 1; i > 0; i--) {
        const j = Math.floor(Math.random() * (i + 1));
        [arr[i], arr[j]] = [arr[j], arr[i]];
    }
    return arr;
}

export function addColumnKeys(items, set) {
    for (const item of items || []) {
        if (!item || item.isPlaceholder) continue;
        const key = nftKey(item);
        if (key) set.add(key);
    }
}

export function countPlaceholders(items) {
    let count = 0;
    for (const item of items || []) {
        if (item?.isPlaceholder) count += 1;
    }
    return count;
}

export function makeScarcityPlan({ uniqueCount, cols, rows, seed }) {
    const totalCells = Math.max(0, (cols || 0) * (rows || 0));
    const placeholdersNeeded = Math.max(0, totalCells - Math.max(0, uniqueCount || 0));
    let processedCells = 0;
    let placedPlaceholders = 0;

    let state = (Number(seed) || 1) >>> 0;
    const rand = () => {
        state = (1664525 * state + 1013904223) >>> 0;
        return state / 4294967296;
    };

    const remainingCells = () => Math.max(0, totalCells - processedCells);
    const remainingPlaceholders = () => Math.max(0, placeholdersNeeded - placedPlaceholders);

    const consumeFixedCells = ({ cells = 0, placeholders = 0 }) => {
        processedCells = Math.min(totalCells, processedCells + Math.max(0, cells));
        placedPlaceholders = Math.min(placeholdersNeeded, placedPlaceholders + Math.max(0, placeholders));
    };

    const shouldPlacePlaceholderNow = ({ canPlaceNft, neighborHasPlaceholder }) => {
        if (!canPlaceNft) return true;
        if (placeholdersNeeded === 0) return false;
        if (remainingPlaceholders() <= 0) return false;
        if (neighborHasPlaceholder) return false;
        const remCells = remainingCells();
        if (remCells <= 0) return false;
        const p = remainingPlaceholders() / remCells;
        return rand() < p;
    };

    const record = ({ isPlaceholder }) => {
        processedCells = Math.min(totalCells, processedCells + 1);
        if (isPlaceholder) placedPlaceholders = Math.min(placeholdersNeeded, placedPlaceholders + 1);
    };

    return {
        totalCells,
        placeholdersNeeded,
        consumeFixedCells,
        shouldPlacePlaceholderNow,
        record,
    };
}

export function buildColumn({ uniqueNfts, usedKeys, rowCount, placeholderSeed, neighborItems, scarcityPlan }) {
    const rowOrder = shuffle(Array.from({ length: rowCount }, (_, i) => i));
    const col = Array.from({ length: rowCount }, () => null);

    for (const row of rowOrder) {
        const avoidCollections = new Set();
        const sideNeighbor = neighborItems?.[row];
        const sideCollection = collectionKey(sideNeighbor);
        if (sideCollection) avoidCollections.add(sideCollection);

        const up = row > 0 ? col[row - 1] : null;
        const down = row < rowCount - 1 ? col[row + 1] : null;
        const upCollection = collectionKey(up);
        const downCollection = collectionKey(down);
        if (upCollection) avoidCollections.add(upCollection);
        if (downCollection) avoidCollections.add(downCollection);

        let nft = null;
        for (const candidate of uniqueNfts || []) {
            const key = nftKey(candidate);
            if (key && usedKeys?.has(key)) continue;
            const c = collectionKey(candidate);
            if (c && avoidCollections.has(c)) continue;
            nft = candidate;
            break;
        }
        if (!nft) {
            for (const candidate of uniqueNfts || []) {
                const key = nftKey(candidate);
                if (key && !usedKeys?.has(key)) {
                    nft = candidate;
                    break;
                }
            }
        }

        const canPlaceNft = !!nft;
        const neighborHasPlaceholder = Boolean(sideNeighbor?.isPlaceholder || up?.isPlaceholder || down?.isPlaceholder);
        const placePlaceholder =
            scarcityPlan?.shouldPlacePlaceholderNow?.({ canPlaceNft, neighborHasPlaceholder }) ?? (!canPlaceNft);

        if (placePlaceholder) {
            col[row] = { isPlaceholder: true, _key: `placeholder-${placeholderSeed}-r${row}` };
            scarcityPlan?.record?.({ isPlaceholder: true });
        } else {
            const key = nftKey(nft);
            if (key) usedKeys.add(key);
            col[row] = {
                ...nft,
                isPlaceholder: false,
                _key: `nft-${nft.id}-r${row}`,
            };
            scarcityPlan?.record?.({ isPlaceholder: false });
        }
    }

    return col;
}
