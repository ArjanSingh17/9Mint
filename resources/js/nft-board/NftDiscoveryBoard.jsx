import '../../css/nft-board.css';
import { useCallback, useEffect, useLayoutEffect, useMemo, useRef, useState, memo } from 'react';

import {
    PRESETS,
    BUFFER_COLS,
    uniqueByKey,
    pickPreset,
    computeVisibleRange,
    buildWindowColumns,
    clearColumnCaches,
} from './engine';

import NftCard from './components/NftCard';
import NftFlyout from './components/NftFlyout';
import useHeaderHeightCssVar from './hooks/useHeaderHeightCssVar';
import useNftFlyout from './hooks/useNftFlyout';

// Motion (tweak values)
const AMBIENT_SPEED = 0.5;
const MAX_WHEEL_SPEED = 45;
const WHEEL_IMPULSE = 0.055;
const LOW_SPEED_DRAG = 0.9;
const HIGH_SPEED_DRAG = 0.995;
const DRAG_CURVE = 1.4;
const ACCEL_FACTOR = 0.05;

// Memoized column wrapper — only re-renders when its items actually change
const MemoColumn = memo(function MemoColumn({ col, onHoverStart, onHoverEnd }) {
    return (
        <div
            className={`nft-board__col${col.isStaggered ? ' nft-board__col--stagger' : ''}`}
        >
            {col.items.map((item) =>
                item.isPlaceholder ? (
                    <div key={item._key} className="nft-board__placeholder" />
                ) : (
                    <NftCard
                        key={item._key}
                        nft={item}
                        onHoverStart={onHoverStart}
                        onHoverEnd={onHoverEnd}
                    />
                ),
            )}
        </div>
    );
});

export default function NftDiscoveryBoard({ nfts, currencies = [], csrfToken, isAuthed, loginUrl }) {
    const containerRef = useRef(null);
    const rafRef = useRef(null);
    const laneRef = useRef(null);

    const scrollXRef = useRef(0);
    const velocityRef = useRef(AMBIENT_SPEED);
    const targetVelocityRef = useRef(AMBIENT_SPEED);
    const wheelMomentumRef = useRef(0);
    const pitchRef = useRef(0);
    const viewportWidthRef = useRef(0);

    const uniqueNftsRef = useRef([]);
    const [displayNfts, setDisplayNfts] = useState(nfts || []);

    const isPointerInsideRef = useRef(false);
    const pauseTokensRef = useRef(0);

    const [preset, setPreset] = useState(PRESETS[0]);
    const [range, setRange] = useState({ leftIndex: 0, rightIndex: 10 });

    const renderedRangeRef = useRef(range);
    useLayoutEffect(() => { renderedRangeRef.current = range; }, [range]);

    useHeaderHeightCssVar();

    const acquirePause = useCallback(() => {
        pauseTokensRef.current += 1;
        targetVelocityRef.current = 0;
    }, []);

    const releasePause = useCallback(() => {
        pauseTokensRef.current = Math.max(0, pauseTokensRef.current - 1);
        if (pauseTokensRef.current === 0) targetVelocityRef.current = AMBIENT_SPEED;
    }, []);

    const {
        hoveredNft, hoverRect, hoverSide, hoverCardSize, flyoutPhase, presetTiltDeg,
        handleCardHoverStart, handleCardHoverEnd,
        handleFlyoutMouseEnter, handleFlyoutMouseLeave,
        handleBoardMouseLeaveSafetyClose, syncHoverRectNow,
    } = useNftFlyout({ preset, acquirePause, releasePause });

    const hoveredNftRef = useRef(null);
    useEffect(() => { hoveredNftRef.current = hoveredNft; }, [hoveredNft]);

    // Build a stable id→nft lookup so flyout can resolve without .find()
    const nftByIdRef = useRef(new Map());
    useEffect(() => {
        const map = new Map();
        for (const nft of displayNfts) {
            if (nft.id != null) map.set(nft.id, nft);
        }
        nftByIdRef.current = map;
    }, [displayNfts]);

    const activeFlyoutNft = useMemo(() => {
        if (!hoveredNft) return null;
        return nftByIdRef.current.get(hoveredNft.id) || hoveredNft;
    }, [displayNfts, hoveredNft]); // eslint-disable-line react-hooks/exhaustive-deps

    const syncHoverRectNowRef = useRef(syncHoverRectNow);
    useEffect(() => { syncHoverRectNowRef.current = syncHoverRectNow; }, [syncHoverRectNow]);

    useEffect(() => {
        const mapped = (nfts || []).map((nft) => ({
            ...nft,
            isLiked: nft.isLiked ?? nft.is_liked ?? false,
        }));
        setDisplayNfts(mapped);
    }, [nfts]);

    // Compute unique NFTs and clear caches when the list changes
    useEffect(() => {
        if (!displayNfts || displayNfts.length === 0) {
            uniqueNftsRef.current = [];
            clearColumnCaches();
            return;
        }
        const unique = uniqueByKey(displayNfts);
        if (unique !== uniqueNftsRef.current) {
            uniqueNftsRef.current = unique;
            clearColumnCaches();
        }
    }, [displayNfts]);

    // Quotes refresh
    useEffect(() => {
        if (!nfts || nfts.length === 0) return;
        let cancelled = false;

        const refreshQuotes = async () => {
            const preferredCurrency = localStorage.getItem('walletCurrency');
            const currencyList = currencies.length ? currencies : ['GBP'];
            const listings = nfts
                .map((nft) => ({ id: nft.listing_id, currency: preferredCurrency || nft.currency || 'GBP' }))
                .filter((entry) => entry.id);
            if (listings.length === 0) return;

            try {
                const items = listings.flatMap((entry) =>
                    currencyList.map((currency) => ({ listing_id: entry.id, currency })),
                );
                const res = await fetch('/api/v1/quotes/bulk', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ items }),
                });
                if (cancelled) return;

                const quoteMap = new Map();
                if (res.ok) {
                    const payload = await res.json();
                    (payload?.data || []).forEach((quote) => {
                        if (!quote?.listing_id) return;
                        if (!quoteMap.has(quote.listing_id)) quoteMap.set(quote.listing_id, {});
                        quoteMap.get(quote.listing_id)[quote.display_currency] = quote.display_amount;
                    });
                }

                setDisplayNfts((prev) => prev.map((nft) => {
                    const m = quoteMap.get(nft.listing_id);
                    if (!m) return nft;
                    const preferred = preferredCurrency || nft.currency || 'GBP';
                    const preferredAmount = m[preferred];
                    return {
                        ...nft,
                        price: preferredAmount ?? nft.price,
                        currency: preferredAmount !== undefined ? preferred : nft.currency,
                        pricesByCurrency: m,
                        priceCurrencies: currencyList.filter((cur) => m[cur] !== undefined),
                    };
                }));
            } catch (_) { /* swallow */ }
        };

        const handleWalletChange = () => refreshQuotes();
        refreshQuotes();
        const interval = setInterval(refreshQuotes, 60000);
        window.addEventListener('walletCurrencyChange', handleWalletChange);
        return () => {
            cancelled = true;
            clearInterval(interval);
            window.removeEventListener('walletCurrencyChange', handleWalletChange);
        };
    }, [nfts, currencies]);

    const handleToggleLike = useCallback(async (nftId, currentLiked) => {
        if (!isAuthed) { window.location.href = loginUrl || '/login'; return; }
        setDisplayNfts((prev) => prev.map((nft) =>
            nft.id === nftId ? { ...nft, isLiked: !currentLiked } : nft,
        ));
        try {
            const res = await fetch(`/nfts/${nftId}/toggle-like`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken || '' },
            });
            if (!res.ok) throw new Error('Failed');
        } catch (_) {
            setDisplayNfts((prev) => prev.map((nft) =>
                nft.id === nftId ? { ...nft, isLiked: currentLiked } : nft,
            ));
        }
    }, [csrfToken, isAuthed, loginUrl]);

    // Preset on resize
    useEffect(() => {
        const el = containerRef.current;
        if (!el) return;
        const updatePreset = () => {
            const rect = el.getBoundingClientRect();
            const nextPreset = pickPreset(rect.width);
            setPreset((prev) => {
                if (prev.cols === nextPreset.cols && prev.rows === nextPreset.rows && prev.tiltDeg === nextPreset.tiltDeg) return prev;
                return nextPreset;
            });
            el.style.setProperty('--cols', String(nextPreset.cols));
            el.style.setProperty('--rows', String(nextPreset.rows));
            el.style.setProperty('--tilt', `${nextPreset.tiltDeg}deg`);
        };
        updatePreset();
        const onResize = () => requestAnimationFrame(updatePreset);
        window.addEventListener('resize', onResize);
        return () => window.removeEventListener('resize', onResize);
    }, []);

    // Measure pitch + viewport
    useEffect(() => {
        const measure = () => {
            const lane = laneRef.current;
            const container = containerRef.current;
            if (!lane || !container) return;
            const firstCol = lane.querySelector('.nft-board__col');
            if (!firstCol) return;
            const firstCard = firstCol.querySelector('.nft-board__card-image') || firstCol.querySelector('.nft-board__placeholder');
            if (!firstCard) return;
            const cs = window.getComputedStyle(container);
            const cardW = parseFloat(cs.getPropertyValue('--card-width') || '0') || firstCard.offsetWidth || 0;
            const gap = parseFloat(cs.getPropertyValue('--gap') || '0') || parseFloat(window.getComputedStyle(firstCol).marginRight || '0') || 0;
            pitchRef.current = Math.max(1, cardW + gap);
            const rect = container.getBoundingClientRect();
            const theta = Math.abs((preset.tiltDeg || 0) * Math.PI / 180);
            viewportWidthRef.current = rect.width * Math.cos(theta) + rect.height * Math.sin(theta);
        };
        const raf = requestAnimationFrame(measure);
        const onResize = () => requestAnimationFrame(measure);
        window.addEventListener('resize', onResize);
        return () => { cancelAnimationFrame(raf); window.removeEventListener('resize', onResize); };
    }, [preset]);

    // Animation loop — NO dependency on displayNfts so it never restarts on data changes
    useEffect(() => {
        const animate = () => {
            if (pauseTokensRef.current === 0) {
                const momentumAbs = Math.abs(wheelMomentumRef.current);
                const speedRatio = Math.min(1, momentumAbs / MAX_WHEEL_SPEED);
                const easedRatio = Math.pow(speedRatio, DRAG_CURVE);
                const dynamicDecay = LOW_SPEED_DRAG + (HIGH_SPEED_DRAG - LOW_SPEED_DRAG) * easedRatio;

                wheelMomentumRef.current *= dynamicDecay;
                if (Math.abs(wheelMomentumRef.current) < 0.001) {
                    wheelMomentumRef.current = 0;
                }
                targetVelocityRef.current = Math.max(
                    -MAX_WHEEL_SPEED,
                    Math.min(MAX_WHEEL_SPEED, AMBIENT_SPEED + wheelMomentumRef.current),
                );
            }

            const diff = targetVelocityRef.current - velocityRef.current;
            velocityRef.current += diff * ACCEL_FACTOR;
            scrollXRef.current += velocityRef.current;

            const pitch = pitchRef.current;
            const viewportWidth = viewportWidthRef.current;
            const lane = laneRef.current;

            if (lane && pitch > 0 && viewportWidth > 0) {
                const newRange = computeVisibleRange({ scrollX: scrollXRef.current, pitch, viewportWidth, bufferCols: BUFFER_COLS });
                setRange((prev) => {
                    if (prev.leftIndex === newRange.leftIndex && prev.rightIndex === newRange.rightIndex) return prev;
                    return newRange;
                });
                const committedLeftIndex = renderedRangeRef.current.leftIndex;
                lane.style.transform = `translate3d(${-(scrollXRef.current - committedLeftIndex * pitch)}px, 0, 0)`;
                if (hoveredNftRef.current) syncHoverRectNowRef.current?.();
            }
            rafRef.current = requestAnimationFrame(animate);
        };
        rafRef.current = requestAnimationFrame(animate);
        return () => { if (rafRef.current) cancelAnimationFrame(rafRef.current); };
    }, []); // ← stable, never restarts

    const handleBoardMouseEnter = useCallback(() => {
        isPointerInsideRef.current = true;
        if (pauseTokensRef.current === 0) targetVelocityRef.current = AMBIENT_SPEED;
    }, []);

    const handleBoardMouseLeave = useCallback(() => {
        isPointerInsideRef.current = false;
        if (pauseTokensRef.current === 0) targetVelocityRef.current = AMBIENT_SPEED;
        handleBoardMouseLeaveSafetyClose();
    }, [handleBoardMouseLeaveSafetyClose]);

    const handleWheel = useCallback((e) => {
        if (!isPointerInsideRef.current) return;
        e.preventDefault();
        const magnitude = Math.pow(Math.abs(e.deltaY), 1.03);
        const delta = Math.sign(e.deltaY) * magnitude * WHEEL_IMPULSE;
        wheelMomentumRef.current = Math.max(
            -MAX_WHEEL_SPEED - AMBIENT_SPEED,
            Math.min(MAX_WHEEL_SPEED - AMBIENT_SPEED, wheelMomentumRef.current + delta),
        );
    }, []);

    useEffect(() => {
        const container = containerRef.current;
        if (!container) return;
        container.addEventListener('wheel', handleWheel, { passive: false });
        return () => container.removeEventListener('wheel', handleWheel);
    }, [handleWheel]);

    // Memoize column build — only recomputes when range, uniqueNfts, or preset change
    const columnsToRender = useMemo(() => buildWindowColumns({
        leftIndex: range.leftIndex,
        rightIndex: range.rightIndex,
        uniqueNfts: uniqueNftsRef.current,
        rows: preset.rows,
        seed: 1337,
        colsPerCycle: Math.max(8, preset.cols + BUFFER_COLS * 2),
    }), [range.leftIndex, range.rightIndex, preset.rows, preset.cols, displayNfts]); // displayNfts triggers uniqueNfts refresh

    return (
        <>
            <div className="nft-board__header">
                <h2 className="nft-board__title">Discover NFTs</h2>
                <p className="nft-board__subtitle">Trending and hand-picked digital collectibles</p>
            </div>

            <div
                ref={containerRef}
                className="nft-board nft-board--tilted"
                onMouseEnter={handleBoardMouseEnter}
                onMouseLeave={handleBoardMouseLeave}
            >
                <div className="nft-board__lane-tilt">
                    <div ref={laneRef} className="nft-board__lane">
                        {columnsToRender.map((col) => (
                            <MemoColumn
                                key={`col-${col.colIndex}`}
                                col={col}
                                onHoverStart={handleCardHoverStart}
                                onHoverEnd={handleCardHoverEnd}
                            />
                        ))}
                    </div>
                </div>
            </div>

            <div className="nft-board__hint">
                <span className="nft-board__hint-text">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                        <circle cx="12" cy="12" r="10" />
                        <path d="M12 16v-4M12 8h.01" />
                    </svg>
                    Hover to pause • Scroll to explore • Click to view
                </span>
            </div>

            <NftFlyout
                nft={activeFlyoutNft}
                hoverRect={hoverRect}
                hoverSide={hoverSide}
                hoverCardSize={hoverCardSize}
                flyoutPhase={flyoutPhase}
                presetTiltDeg={presetTiltDeg}
                onMouseEnter={handleFlyoutMouseEnter}
                onMouseLeave={handleFlyoutMouseLeave}
                onToggleLike={handleToggleLike}
            />
        </>
    );
}
