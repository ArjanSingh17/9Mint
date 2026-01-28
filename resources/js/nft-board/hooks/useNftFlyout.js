import { useCallback, useEffect, useRef, useState } from 'react';

// Flyout sizing + timing
const HOVER_PANEL_WIDTH = 190; // panel width
const HOVER_PANEL_GAP = 0; // panel gap
const HOVER_PANEL_SEAM_OVERLAP = 2; // seam overlap
const HOVER_RECT_SYNC_MS = 450; // sync ms
const FLYOUT_ANIM_MS = 280; // anim ms
const FLYOUT_OPEN_MIN_H = 190; // min height

export default function useNftFlyout({ preset, acquirePause, releasePause }) {
    const [hoveredNft, setHoveredNft] = useState(null);
    const [hoverRect, setHoverRect] = useState(null);
    const [hoverSide, setHoverSide] = useState('right');
    const [hoverCardSize, setHoverCardSize] = useState({ w: 0, h: 0 });
    const [flyoutPhase, setFlyoutPhase] = useState('closed');

    const hoveredElRef = useRef(null);
    const hoverReleaseTimerRef = useRef(null);
    const flyoutCloseTimerRef = useRef(null);
    const flyoutOpenRafRef = useRef(null);
    const hoverSyncRafRef = useRef(null);

    const cardHoverActiveRef = useRef(false);
    const hoverLogCountRef = useRef(0);

    const clearHoverDatasets = useCallback((el) => {
        if (!el?.dataset) return;
        delete el.dataset.hovered;
        delete el.dataset.hoverSide;
        delete el.dataset.flyout;
    }, []);

    const cancelTimers = useCallback(() => {
        if (hoverReleaseTimerRef.current) {
            clearTimeout(hoverReleaseTimerRef.current);
            hoverReleaseTimerRef.current = null;
        }
        if (flyoutCloseTimerRef.current) {
            clearTimeout(flyoutCloseTimerRef.current);
            flyoutCloseTimerRef.current = null;
        }
        if (flyoutOpenRafRef.current) {
            cancelAnimationFrame(flyoutOpenRafRef.current);
            flyoutOpenRafRef.current = null;
        }
        if (hoverSyncRafRef.current) {
            cancelAnimationFrame(hoverSyncRafRef.current);
            hoverSyncRafRef.current = null;
        }
    }, []);

    useEffect(() => cancelTimers, [cancelTimers]);

    const handleCardHoverEnd = useCallback((elToken) => {
        const token = elToken || hoveredElRef.current;
        if (!token) return;

        if (hoverLogCountRef.current < 6) {
            hoverLogCountRef.current += 1;
            fetch('http://127.0.0.1:7243/ingest/d0a22aaf-237c-44e3-b6ae-01ddc8de8397',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({sessionId:'debug-session',runId:'design-run1',hypothesisId:'A',location:'useNftFlyout.js:hoverEnd',message:'flyout_hover_end',data:{hadNft:Boolean(hoveredNft),phase:flyoutPhase},timestamp:Date.now()})}).catch(()=>{});
        }
        // #endregion

        if (hoverReleaseTimerRef.current) {
            clearTimeout(hoverReleaseTimerRef.current);
            hoverReleaseTimerRef.current = null;
        }

        hoverReleaseTimerRef.current = setTimeout(() => {
            hoverReleaseTimerRef.current = null;

            if (hoveredElRef.current !== token) return;

            if (hoverSyncRafRef.current) {
                cancelAnimationFrame(hoverSyncRafRef.current);
                hoverSyncRafRef.current = null;
            }

            setFlyoutPhase('closing');
            flyoutCloseTimerRef.current = setTimeout(() => {
                flyoutCloseTimerRef.current = null;

                if (hoveredElRef.current !== token) return;

                clearHoverDatasets(token);

                hoveredElRef.current = null;
                setHoveredNft(null);
                setHoverRect(null);
                setHoverSide('right');
                setHoverCardSize({ w: 0, h: 0 });
                setFlyoutPhase('closed');

                if (cardHoverActiveRef.current) {
                    cardHoverActiveRef.current = false;
                    releasePause();
                }
            }, FLYOUT_ANIM_MS);
        }, 60); // hover-out delay
    }, [clearHoverDatasets, releasePause]);

    const handleCardHoverStart = useCallback((nft, el) => {
        if (!el) return;
        cancelTimers();

        if (hoverLogCountRef.current < 6) {
            hoverLogCountRef.current += 1;
            fetch('http://127.0.0.1:7243/ingest/d0a22aaf-237c-44e3-b6ae-01ddc8de8397',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({sessionId:'debug-session',runId:'design-run1',hypothesisId:'D',location:'useNftFlyout.js:hoverStart',message:'flyout_hover_start',data:{nftId:nft?.id,hasCollectionUrl:Boolean(nft?.collection_url),viewportW:window.innerWidth,viewportH:window.innerHeight},timestamp:Date.now()})}).catch(()=>{});
        }

        const prevEl = hoveredElRef.current;
        if (prevEl && prevEl !== el) {
            clearHoverDatasets(prevEl);
        }

        hoveredElRef.current = el;
        if (el?.dataset) el.dataset.flyout = '1';

        setHoveredNft(nft);
        const img = el.querySelector?.('.nft-board__card-image');
        const rect = img ? img.getBoundingClientRect() : el.getBoundingClientRect();
        setHoverRect(rect);
        setHoverCardSize({
            w: img?.offsetWidth || rect.width || 0,
            h: img?.offsetHeight || rect.height || 0,
        });

        if (!cardHoverActiveRef.current) {
            cardHoverActiveRef.current = true;
            acquirePause();
        }

        setFlyoutPhase('enter');
        flyoutOpenRafRef.current = requestAnimationFrame(() => {
            flyoutOpenRafRef.current = null;
            setFlyoutPhase('open');
        });

        const start = performance.now();
        const sync = () => {
            if (hoveredElRef.current !== el) return;
            const imgNow = el.querySelector?.('.nft-board__card-image');
            const rectNow = imgNow ? imgNow.getBoundingClientRect() : el.getBoundingClientRect();
            setHoverRect(rectNow);
            if (performance.now() - start < HOVER_RECT_SYNC_MS) {
                hoverSyncRafRef.current = requestAnimationFrame(sync);
            } else {
                hoverSyncRafRef.current = null;
            }
        };
        hoverSyncRafRef.current = requestAnimationFrame(sync);
    }, [acquirePause, cancelTimers, clearHoverDatasets]);

    const handleFlyoutMouseEnter = useCallback(() => {
        if (hoverReleaseTimerRef.current) {
            clearTimeout(hoverReleaseTimerRef.current);
            hoverReleaseTimerRef.current = null;
        }
        if (flyoutCloseTimerRef.current) {
            clearTimeout(flyoutCloseTimerRef.current);
            flyoutCloseTimerRef.current = null;
        }
        if (flyoutPhase !== 'open') {
            setFlyoutPhase('open');
        }
    }, [flyoutPhase]);

    const handleFlyoutMouseLeave = useCallback(() => {
        handleCardHoverEnd(hoveredElRef.current);
    }, [handleCardHoverEnd]);

    const handleBoardMouseLeaveSafetyClose = useCallback(() => {
        if (cardHoverActiveRef.current && hoveredElRef.current) {
            handleCardHoverEnd(hoveredElRef.current);
        }
    }, [handleCardHoverEnd]);

    useEffect(() => {
        const el = hoveredElRef.current;
        if (!el || !hoverRect || !hoveredNft) return;

        const rightFits = (hoverRect.right + HOVER_PANEL_GAP - HOVER_PANEL_SEAM_OVERLAP + HOVER_PANEL_WIDTH) <= (window.innerWidth - 8);
        const side = rightFits ? 'right' : 'left';
        setHoverSide(side);

        el.dataset.hovered = '1';
        el.dataset.hoverSide = side;

        return () => {
            if (el.dataset) {
                delete el.dataset.hovered;
                delete el.dataset.hoverSide;
            }
        };
    }, [hoverRect, hoveredNft]);

    useEffect(() => {
        if (!hoveredNft) return;
        const onScroll = () => {
            const el = hoveredElRef.current;
            if (!el) return;
            const img = el.querySelector?.('.nft-board__card-image');
            const rect = img ? img.getBoundingClientRect() : el.getBoundingClientRect();
            setHoverRect(rect);
        };
        const onResize = onScroll;
        window.addEventListener('scroll', onScroll, { passive: true });
        window.addEventListener('resize', onResize);
        return () => {
            window.removeEventListener('scroll', onScroll);
            window.removeEventListener('resize', onResize);
        };
    }, [hoveredNft]);

    const syncHoverRectNow = useCallback(() => {
        const el = hoveredElRef.current;
        if (!el) return;
        const img = el.querySelector?.('.nft-board__card-image');
        const rect = (img || el).getBoundingClientRect();

        setHoverRect((prev) => {
            if (!prev) return rect;
            const dx = Math.abs(prev.left - rect.left);
            const dy = Math.abs(prev.top - rect.top);
            return (dx < 0.5 && dy < 0.5) ? prev : rect;
        });
    }, []);

    return {
        hoveredNft,
        hoverRect,
        hoverSide,
        hoverCardSize,
        flyoutPhase,
        presetTiltDeg: preset?.tiltDeg || 0,

        handleCardHoverStart,
        handleCardHoverEnd,
        handleFlyoutMouseEnter,
        handleFlyoutMouseLeave,
        handleBoardMouseLeaveSafetyClose,
        syncHoverRectNow,
    };
}

