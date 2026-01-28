import { createPortal } from 'react-dom';
import NftInfoPanel from './NftInfoPanel';

// Flyout sizing (tweak values)
const HOVER_PANEL_WIDTH = 190; // panel width
const FLYOUT_OPEN_MIN_H = 190; // min height

export default function NftFlyout({
    nft,
    hoverRect,
    hoverSide,
    hoverCardSize,
    flyoutPhase,
    presetTiltDeg,
    onMouseEnter,
    onMouseLeave,
}) {
    if (!nft || !hoverRect) return null;

    const side = hoverSide;
    const cardW = hoverCardSize?.w || hoverRect.width || 0;
    const cardH = hoverCardSize?.h || hoverRect.height || 0;
    const centerX = hoverRect.left + hoverRect.width / 2;
    const centerY = hoverRect.top + hoverRect.height / 2;
    const isOpen = flyoutPhase === 'open';

    const openW = cardW + HOVER_PANEL_WIDTH;
    const openCenterX = centerX + (side === 'right' ? HOVER_PANEL_WIDTH / 2 : -HOVER_PANEL_WIDTH / 2);
    const openH = Math.max(cardH, FLYOUT_OPEN_MIN_H);

    const posX = isOpen ? openCenterX : centerX;
    const width = isOpen ? openW : cardW;
    const height = isOpen ? openH : cardH;
    const rotateDeg = isOpen ? 0 : (presetTiltDeg || 0);

    const href = nft?.collection_url || '#';

    return createPortal(
        <div
            className="nft-board__flyout"
            data-side={side}
            data-state={isOpen ? 'open' : 'closed'}
            style={{
                position: 'fixed',
                top: `${centerY}px`,
                left: `${posX}px`,
                width: `${Math.max(1, width)}px`,
                height: `${Math.max(1, height)}px`,
                transform: `translate(-50%, -50%) rotate(${rotateDeg}deg)`,
            }}
            onMouseEnter={onMouseEnter}
            onMouseLeave={onMouseLeave}
        >
            <a
                href={href}
                className="nft-board__flyout-image-link"
                aria-label={`View ${nft.name} collection`}
            >
                <img
                    className="nft-board__flyout-image"
                    src={nft.image_url}
                    alt={nft.name}
                    draggable={false}
                    style={{
                        width: `${Math.max(1, cardW)}px`,
                        height: `${Math.max(1, height)}px`,
                    }}
                />
            </a>
            <div className="nft-board__flyout-info">
                <NftInfoPanel nft={nft} />
            </div>
        </div>,
        document.body
    );
}
