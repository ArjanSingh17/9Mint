import { useRef } from 'react';

export default function NftCard({ nft, onHoverStart, onHoverEnd }) {
    const cardRef = useRef(null);
    const href = nft?.collection_url || '#';
    const isLink = Boolean(nft?.collection_url);

    // Hover tracking
    const handleEnter = () => onHoverStart?.(nft, cardRef.current);
    const handleLeave = () => onHoverEnd?.(cardRef.current);

    return (
        <a
            ref={cardRef}
            className="nft-board__card"
            href={href}
            aria-label={isLink ? `View ${nft.name} collection` : nft.name}
            onMouseEnter={handleEnter}
            onMouseLeave={handleLeave}
            onFocus={handleEnter}
            onBlur={handleLeave}
            onClick={(e) => {
                if (!isLink) e.preventDefault();
            }}
            onKeyDown={(e) => {
                if (e.key === ' ') {
                    e.preventDefault();
                    cardRef.current?.click();
                }
            }}
        >
            <img
                className="nft-board__card-image"
                src={nft.image_url}
                alt={nft.name}
                loading="lazy"
                draggable={false}
            />
        </a>
    );
}
