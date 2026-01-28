import '../../css/nft-board.css';
import { useCallback, useEffect, useLayoutEffect, useRef, useState } from 'react';

import {
    PRESETS,
    BUFFER_COLS,
    uniqueByKey,
    pickPreset,
    computeVisibleRange,
    buildWindowColumns,
} from './engine';

import NftCard from './components/NftCard';
import NftFlyout from './components/NftFlyout';
import useHeaderHeightCssVar from './hooks/useHeaderHeightCssVar';
import useNftFlyout from './hooks/useNftFlyout';

// Motion (tweak values)
const AMBIENT_SPEED = 0.5; // idle speed
const MAX_WHEEL_SPEED = 5; // max wheel speed
const ACCEL_FACTOR = 0.08; // ease factor
const FRICTION = 0.98; // damping

export default function NftDiscoveryBoard({ nfts }) {
    const containerRef = useRef(null);
    const rafRef = useRef(null);
    const laneRef = useRef(null);

    // Core state for virtual window approach
    const scrollXRef = useRef(0);
    const velocityRef = useRef(AMBIENT_SPEED);
    const targetVelocityRef = useRef(AMBIENT_SPEED);
    const pitchRef = useRef(0);
    const viewportWidthRef = useRef(0);

    const uniqueNftsRef = useRef([]);

    // Pause/hover state
    const isPointerInsideRef = useRef(false);
    const pauseTokensRef = useRef(0);

    // React state (only updates when range changes)
    const [preset, setPreset] = useState(PRESETS[0]);
    const [range, setRange] = useState({ leftIndex: 0, rightIndex: 10 });

    // keep DOM + RAF in sync (prevents snap on window advance)
    const renderedRangeRef = useRef(range);
    useLayoutEffect(() => {
        renderedRangeRef.current = range;
    }, [range]);

    useHeaderHeightCssVar();

    const acquirePause = useCallback(() => {
        pauseTokensRef.current += 1;
        targetVelocityRef.current = 0;
    }, []);

    const releasePause = useCallback(() => {
        pauseTokensRef.current = Math.max(0, pauseTokensRef.current - 1);
        if (pauseTokensRef.current === 0) {
            targetVelocityRef.current = AMBIENT_SPEED;
        }
    }, []);

    const {
        hoveredNft,
        hoverRect,
        hoverSide,
        hoverCardSize,
        flyoutPhase,
        presetTiltDeg,
        handleCardHoverStart,
        handleCardHoverEnd,
        handleFlyoutMouseEnter,
        handleFlyoutMouseLeave,
        handleBoardMouseLeaveSafetyClose,
        syncHoverRectNow,
    } = useNftFlyout({ preset, acquirePause, releasePause });

    // Track hoveredNft in a ref so animation loop can access it
    const hoveredNftRef = useRef(null);
    useEffect(() => {
        hoveredNftRef.current = hoveredNft;
    }, [hoveredNft]);

    // Track syncHoverRectNow in a ref so animation loop can call it
    const syncHoverRectNowRef = useRef(syncHoverRectNow);
    useEffect(() => {
        syncHoverRectNowRef.current = syncHoverRectNow;
    }, [syncHoverRectNow]);

    // Initialize unique NFTs when NFTs change
    useEffect(() => {
        if (!nfts || nfts.length === 0) {
            uniqueNftsRef.current = [];
            return;
        }

        const uniqueNfts = uniqueByKey(nfts);
        uniqueNftsRef.current = uniqueNfts;
    }, [nfts]);

    // Update preset on resize
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

    // Measure pitch and viewport width
    useEffect(() => {
        const measure = () => {
            const lane = laneRef.current;
            const container = containerRef.current;
            if (!lane || !container) return;

            const firstCol = lane.querySelector('.nft-board__col');
            if (!firstCol) return;

            const firstCard =
                firstCol.querySelector('.nft-board__card-image') ||
                firstCol.querySelector('.nft-board__placeholder');
            if (!firstCard) return;

            const containerStyles = window.getComputedStyle(container);
            const cardW =
                parseFloat(containerStyles.getPropertyValue('--card-width') || '0')
                || firstCard.offsetWidth
                || 0;
            const gap =
                parseFloat(containerStyles.getPropertyValue('--gap') || '0')
                || parseFloat(window.getComputedStyle(firstCol).marginRight || '0')
                || 0;
            pitchRef.current = Math.max(1, cardW + gap);

            const rect = container.getBoundingClientRect();
            const theta = Math.abs((preset.tiltDeg || 0) * Math.PI / 180);
            const effectiveWidth = rect.width * Math.cos(theta) + rect.height * Math.sin(theta);
            viewportWidthRef.current = effectiveWidth;
        };

        const raf = requestAnimationFrame(measure);
        const onResize = () => requestAnimationFrame(measure);
        window.addEventListener('resize', onResize);

        return () => {
            cancelAnimationFrame(raf);
            window.removeEventListener('resize', onResize);
        };
    }, [preset]);

    useEffect(() => {
        if (!nfts || nfts.length === 0) return;

        const animate = () => {
            const diff = targetVelocityRef.current - velocityRef.current;
            velocityRef.current += diff * ACCEL_FACTOR;

            if (targetVelocityRef.current === AMBIENT_SPEED && Math.abs(velocityRef.current) > AMBIENT_SPEED * 1.5) {
                velocityRef.current *= FRICTION;
            }

            scrollXRef.current += velocityRef.current;

            const pitch = pitchRef.current;
            const viewportWidth = viewportWidthRef.current;
            const lane = laneRef.current;

            if (lane && pitch > 0 && viewportWidth > 0) {
                const newRange = computeVisibleRange({
                    scrollX: scrollXRef.current,
                    pitch,
                    viewportWidth,
                    bufferCols: BUFFER_COLS,
                });

                setRange((prevRange) => {
                    if (prevRange.leftIndex === newRange.leftIndex && prevRange.rightIndex === newRange.rightIndex) {
                        return prevRange;
                    }
                    return newRange;
                });

                const committedLeftIndex = renderedRangeRef.current.leftIndex;
                const laneShiftPx = scrollXRef.current - committedLeftIndex * pitch;
                lane.style.transform = `translate3d(${-laneShiftPx}px, 0, 0)`;

                if (hoveredNftRef.current) {
                    syncHoverRectNowRef.current?.();
                }
            }

            rafRef.current = requestAnimationFrame(animate);
        };

        rafRef.current = requestAnimationFrame(animate);
        return () => {
            if (rafRef.current) cancelAnimationFrame(rafRef.current);
        };
    }, [nfts]);

    const handleBoardMouseEnter = useCallback(() => {
        isPointerInsideRef.current = true;
        if (pauseTokensRef.current === 0) {
            targetVelocityRef.current = AMBIENT_SPEED;
        }
    }, []);

    const handleBoardMouseLeave = useCallback(() => {
        isPointerInsideRef.current = false;
        if (pauseTokensRef.current === 0) {
            targetVelocityRef.current = AMBIENT_SPEED;
        }
        handleBoardMouseLeaveSafetyClose();
    }, [handleBoardMouseLeaveSafetyClose]);

    const handleWheel = useCallback((e) => {
        if (!isPointerInsideRef.current) return;

        e.preventDefault();
    const delta = e.deltaY * 0.02; // wheel sensitivity
        targetVelocityRef.current = Math.max(
            -MAX_WHEEL_SPEED,
            Math.min(MAX_WHEEL_SPEED, targetVelocityRef.current + delta)
        );
    }, []);

    useEffect(() => {
        const container = containerRef.current;
        if (!container) return;
        container.addEventListener('wheel', handleWheel, { passive: false });
        return () => container.removeEventListener('wheel', handleWheel);
    }, [handleWheel]);

    const columnsToRender = buildWindowColumns({
        leftIndex: range.leftIndex,
        rightIndex: range.rightIndex,
        uniqueNfts: uniqueNftsRef.current,
        rows: preset.rows,
        seed: 1337, // shuffle seed
        colsPerCycle: Math.max(8, preset.cols + BUFFER_COLS * 2), // cycle width
    });

    if (!nfts || nfts.length === 0) {
        return (
            <div className="nft-board__empty">
                <p>No NFTs available at the moment. Check back soon!</p>
            </div>
        );
    }

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
                            <div
                                className={`nft-board__col${col.isStaggered ? ' nft-board__col--stagger' : ''}`}
                                key={`col-${col.colIndex}`}
                            >
                                {col.items.map((item) => (
                                    item.isPlaceholder ? (
                                        <div key={item._key} className="nft-board__placeholder" />
                                    ) : (
                                        <NftCard
                                            key={item._key}
                                            nft={item}
                                            onHoverStart={handleCardHoverStart}
                                            onHoverEnd={handleCardHoverEnd}
                                        />
                                    )
                                ))}
                            </div>
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
                nft={hoveredNft}
                hoverRect={hoverRect}
                hoverSide={hoverSide}
                hoverCardSize={hoverCardSize}
                flyoutPhase={flyoutPhase}
                presetTiltDeg={presetTiltDeg}
                onMouseEnter={handleFlyoutMouseEnter}
                onMouseLeave={handleFlyoutMouseLeave}
            />
        </>
    );
}
