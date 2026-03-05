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

// Keys — cached on the NFT object to avoid repeated string work
export function nftKey(nft) {
    if (!nft) return '';
    if (nft._cachedKey !== undefined) return nft._cachedKey;
    const name = (nft.name || '').toLowerCase();
    const collection = (nft.collection_slug || nft.collection_name || '').toLowerCase();
    const image = (nft.thumbnail_url || nft.image_url || '').toLowerCase();
    const key = `${name}::${collection}::${image}`;
    nft._cachedKey = key;
    return key;
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

// ─── Persistent column cache ───────────────────────────────────────────
// Survives across renders; invalidated explicitly when NFTs or rows change.

let _deckCache = new Map();   // cycleIndex → deck[]
let _colCache  = new Map();   // colIndex  → { colIndex, isStaggered, items }
let _cacheNfts = null;        // reference equality check
let _cacheRows = 0;
let _cacheSeed = 0;
let _cacheCycleCols = 0;

function invalidateCaches(uniqueNfts, rows, seed, cycleCols) {
    if (
        _cacheNfts === uniqueNfts &&
        _cacheRows === rows &&
        _cacheSeed === seed &&
        _cacheCycleCols === cycleCols
    ) {
        return; // nothing changed
    }
    _deckCache.clear();
    _colCache.clear();
    _cacheNfts = uniqueNfts;
    _cacheRows = rows;
    _cacheSeed = seed;
    _cacheCycleCols = cycleCols;
}

/**
 * Force-clear all caches (call when the NFT list is replaced).
 */
export function clearColumnCaches() {
    _deckCache.clear();
    _colCache.clear();
    _cacheNfts = null;
    _cacheRows = 0;
    _cacheSeed = 0;
    _cacheCycleCols = 0;
}

// Reusable placeholder template per (colIndex, row) — avoids object churn
const _placeholderPool = new Map();
function getPlaceholder(colIndex, row) {
    const k = (colIndex << 4) | row; // rows ≤ ~8, so 4 bits is fine
    let p = _placeholderPool.get(k);
    if (!p) {
        p = Object.freeze({ isPlaceholder: true, _key: `placeholder-${colIndex}-r${row}` });
        // Keep pool bounded (LRU-ish: just cap size)
        if (_placeholderPool.size > 2000) _placeholderPool.clear();
        _placeholderPool.set(k, p);
    }
    return p;
}

function getDeckForCycle(cycleIndex, uniqueNfts, rows, seedBase, cycleCols) {
    if (_deckCache.has(cycleIndex)) return _deckCache.get(cycleIndex);

    const cycleCells = cycleCols * rows;
    const mixed = (seedBase ^ ((cycleIndex * 2654435761) | 0)) >>> 0;
    const rng = seededRng(mixed);

    const shuffledNfts = seededShuffle(uniqueNfts, rng);
    const take = Math.min(shuffledNfts.length, cycleCells);
    const deck = shuffledNfts.slice(0, take);

    const placeholdersNeeded = Math.max(0, cycleCells - take);
    for (let i = 0; i < placeholdersNeeded; i++) deck.push(null); // null = placeholder sentinel

    const finalDeck = placeholdersNeeded > 0 ? seededShuffle(deck, rng) : deck;
    _deckCache.set(cycleIndex, finalDeck);
    return finalDeck;
}

function buildSingleColumn(colIndex, uniqueNfts, rows, seedBase, cycleCols) {
    if (_colCache.has(colIndex)) return _colCache.get(colIndex);

    const cycleIndex = floorDiv(colIndex, cycleCols);
    const colInCycle = mod(colIndex, cycleCols);
    const deck = getDeckForCycle(cycleIndex, uniqueNfts, rows, seedBase, cycleCols);

    const items = new Array(rows);
    for (let row = 0; row < rows; row++) {
        const pos = colInCycle * rows + row;
        const entry = deck[pos];
        if (entry) {
            // Stamp rendering key directly — no object spread
            const idPart = entry.id ?? entry._cachedKey ?? pos;
            entry._key = `nft-${colIndex}-r${row}-${idPart}`;
            entry.isPlaceholder = false;
            items[row] = entry;
        } else {
            items[row] = getPlaceholder(colIndex, row);
        }
    }

    const col = {
        colIndex,
        isStaggered: (colIndex & 1) === 1 || (colIndex < 0 && ((-colIndex) & 1) === 1),
        items,
    };
    _colCache.set(colIndex, col);
    return col;
}

export function buildWindowColumns({
    leftIndex,
    rightIndex,
    uniqueNfts,
    rows,
    seed,
    colsPerCycle,
}) {
    const cycleCols = Math.max(1, colsPerCycle || (rightIndex - leftIndex + 1));
    const seedBase = (Number(seed) || 1) >>> 0;

    invalidateCaches(uniqueNfts, rows, seedBase, cycleCols);

    // Fast path: no NFTs → all placeholders
    if (!uniqueNfts || uniqueNfts.length === 0) {
        const cols = new Array(rightIndex - leftIndex + 1);
        for (let i = 0, colIndex = leftIndex; colIndex <= rightIndex; colIndex++, i++) {
            const items = new Array(rows);
            for (let row = 0; row < rows; row++) {
                items[row] = getPlaceholder(colIndex, row);
            }
            cols[i] = {
                colIndex,
                isStaggered: Math.abs(colIndex) % 2 === 1,
                items,
            };
        }
        return cols;
    }

    const cols = new Array(rightIndex - leftIndex + 1);
    for (let i = 0, colIndex = leftIndex; colIndex <= rightIndex; colIndex++, i++) {
        cols[i] = buildSingleColumn(colIndex, uniqueNfts, rows, seedBase, cycleCols);
    }
    return cols;
}

// ----- Legacy exports (kept for compatibility) -----

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

export function buildColumnByIndex({ colIndex, uniqueNfts, rows, totalNfts }) {
    if (!uniqueNfts || uniqueNfts.length === 0) {
        return Array.from({ length: rows }, (_, row) => getPlaceholder(colIndex, row));
    }
    const seed = colIndex * 2654435761;
    const rng = seededRng(seed);
    const placeholderProbability = totalNfts < rows ? (rows - totalNfts) / rows : 0;
    const rowOrder = seededShuffle(Array.from({ length: rows }, (_, i) => i), rng);
    const col = new Array(rows);
    const usedInColumn = new Set();

    for (const row of rowOrder) {
        if (placeholderProbability > 0 && rng() < placeholderProbability) {
            col[row] = getPlaceholder(colIndex, row);
            continue;
        }
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
            col[row] = { ...nft, isPlaceholder: false, _key: `nft-${colIndex}-r${row}-${nft.id}` };
        } else {
            col[row] = getPlaceholder(colIndex, row);
        }
    }
    return col;
}

export function getOrBuildColumn({ colIndex, uniqueNfts, rows, cache }) {
    if (cache.has(colIndex)) return cache.get(colIndex);
    const column = buildColumnByIndex({ colIndex, uniqueNfts, rows, totalNfts: uniqueNfts.length });
    cache.set(colIndex, column);
    return column;
}

export function makeScarcityPlan({ uniqueCount, cols, rows, seed }) {
    const totalCells = Math.max(0, (cols || 0) * (rows || 0));
    const placeholdersNeeded = Math.max(0, totalCells - Math.max(0, uniqueCount || 0));
    let processedCells = 0;
    let placedPlaceholders = 0;
    let state = (Number(seed) || 1) >>> 0;
    const rand = () => { state = (1664525 * state + 1013904223) >>> 0; return state / 4294967296; };
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
        return rand() < remainingPlaceholders() / remCells;
    };
    const record = ({ isPlaceholder }) => {
        processedCells = Math.min(totalCells, processedCells + 1);
        if (isPlaceholder) placedPlaceholders = Math.min(placeholdersNeeded, placedPlaceholders + 1);
    };
    return { totalCells, placeholdersNeeded, consumeFixedCells, shouldPlacePlaceholderNow, record };
}

export function buildColumn({ uniqueNfts, usedKeys, rowCount, placeholderSeed, neighborItems, scarcityPlan }) {
    const rowOrder = shuffle(Array.from({ length: rowCount }, (_, i) => i));
    const col = new Array(rowCount);
    for (const row of rowOrder) {
        const avoidCollections = new Set();
        const sideNeighbor = neighborItems?.[row];
        const sideCollection = collectionKey(sideNeighbor);
        if (sideCollection) avoidCollections.add(sideCollection);
        const up = row > 0 ? col[row - 1] : null;
        const down = row < rowCount - 1 ? col[row + 1] : null;
        if (collectionKey(up)) avoidCollections.add(collectionKey(up));
        if (collectionKey(down)) avoidCollections.add(collectionKey(down));
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
                if (key && !usedKeys?.has(key)) { nft = candidate; break; }
            }
        }
        const canPlaceNft = !!nft;
        const neighborHasPlaceholder = Boolean(sideNeighbor?.isPlaceholder || up?.isPlaceholder || down?.isPlaceholder);
        const placePlaceholder = scarcityPlan?.shouldPlacePlaceholderNow?.({ canPlaceNft, neighborHasPlaceholder }) ?? (!canPlaceNft);
        if (placePlaceholder) {
            col[row] = { isPlaceholder: true, _key: `placeholder-${placeholderSeed}-r${row}` };
            scarcityPlan?.record?.({ isPlaceholder: true });
        } else {
            const key = nftKey(nft);
            if (key) usedKeys.add(key);
            col[row] = { ...nft, isPlaceholder: false, _key: `nft-${nft.id}-r${row}` };
            scarcityPlan?.record?.({ isPlaceholder: false });
        }
    }
    return col;
}
