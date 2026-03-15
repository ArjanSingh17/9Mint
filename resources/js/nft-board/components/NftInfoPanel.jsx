export default function NftInfoPanel({ nft }) {
    const currency = nft?.currency || 'GBP'; // default currency
    const price = nft?.price;
    const pricesByCurrency = nft?.pricesByCurrency || null;
    const priceOrder = nft?.priceCurrencies || (pricesByCurrency ? Object.keys(pricesByCurrency) : []);

    // Price format
    const formatMoney = (value, formatCurrency = currency) => {
        if (value === null || value === undefined) return '--';
        const normalized = typeof value === 'string'
            ? value.trim().replace(',', '.').replace(/[^0-9.\-]/g, '')
            : String(value);
        const amount = Number(normalized);
        if (Number.isNaN(amount)) return '--';
        const isCrypto = ['BTC', 'ETH'].includes(formatCurrency);
        const decimals = isCrypto ? 8 : 2;
        const symbols = {
            GBP: '£',
            USD: '$',
            EUR: '€',
            BTC: '₿',
            ETH: 'Ξ',
        };
        const symbol = symbols[formatCurrency];
        if (symbol) return `${symbol}${amount.toFixed(decimals)}`;
        return `${amount.toFixed(decimals)} ${formatCurrency}`;
    };

    const displayPrice = formatMoney(price);
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
                {pricesByCurrency && priceOrder.length ? (
                    <div className="nft-board__info-price-sizes" aria-label="Prices by currency">
                        {priceOrder.map((cur) => (
                            <div key={cur} className="nft-board__info-price-size">
                                <span>{cur}</span>
                                <strong>{formatMoney(pricesByCurrency[cur], cur)}</strong>
                            </div>
                        ))}
                    </div>
                ) : (
                    <div className="nft-board__info-price-single" aria-label="Price">
                        <span>Price</span>
                        <strong>{displayPrice}</strong>
                    </div>
                )}
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
