export default function NftInfoPanel({ nft }) {
    const prices = nft?.prices || {};
    const currency = nft?.currency || 'GBP'; // default currency

    // Price format
    const formatMoney = (value) => {
        if (value === null || value === undefined) return '--';
        const normalized = typeof value === 'string'
            ? value.trim().replace(',', '.').replace(/[^0-9.\-]/g, '')
            : String(value);
        const amount = Number(normalized);
        if (Number.isNaN(amount)) return '--';
        if (currency === 'GBP') return `£${amount.toFixed(2)}`;
        return `${amount} ${currency}`;
    };

    const small = formatMoney(prices.small);
    const medium = formatMoney(prices.medium);
    const large = formatMoney(prices.large);
    const stockText = (nft?.editions_remaining === null || nft?.editions_remaining === undefined)
        ? '--'
        : String(nft.editions_remaining);

    const href = nft?.collection_url || '#';

    return (
        <div className="nft-board__info">
            <div className="nft-board__info-top">
                <div className="nft-board__info-titles">
                    <h3 className="nft-board__info-name">{nft.name}</h3>
                    <p className="nft-board__info-collection">{nft.collection_name || 'Collection'}</p>
                </div>
                <div className="nft-board__info-stock" title="NFTs in stock">
                    {stockText}
                </div>
            </div>

            <div className="nft-board__info-mid">
                <div className="nft-board__info-price-sizes" aria-label="Prices by size">
                    <div className="nft-board__info-price-size">
                        <span>Small</span>
                        <strong>{small}</strong>
                    </div>
                    <div className="nft-board__info-price-size">
                        <span>Medium</span>
                        <strong>{medium}</strong>
                    </div>
                    <div className="nft-board__info-price-size">
                        <span>Large</span>
                        <strong>{large}</strong>
                    </div>
                </div>
            </div>

            <div className="nft-board__info-cta">
                <a className="nft-board__card-cta" href={href}>
                    View Collection
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                        <path d="M5 12h14M12 5l7 7-7 7" />
                    </svg>
                </a>
            </div>
        </div>
    );
}
