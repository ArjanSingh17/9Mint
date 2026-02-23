import { useCallback, useEffect, useMemo, useState } from 'react';

const defaultRange = 'month';

const formatMoney = (amount, currency) => {
    if (amount === null || amount === undefined) return '--';
    const value = Number(amount);
    if (Number.isNaN(value)) return '--';
    const isCrypto = ['BTC', 'ETH'].includes(currency);
    const decimals = isCrypto ? 8 : 2;
    const symbols = {
        GBP: '£',
        USD: '$',
        EUR: '€',
        BTC: '₿',
        ETH: 'Ξ',
    };
    const symbol = symbols[currency];
    if (symbol) return `${symbol}${value.toFixed(decimals)}`;
    return `${value.toFixed(decimals)} ${currency}`;
};

const Chart = ({ points = [], currency }) => {
    if (!points.length) {
        return <p>No history data available.</p>;
    }

    const values = points.map((p) => p.value);
    const min = Math.min(...values);
    const max = Math.max(...values);
    const width = 600;
    const height = 220;
    const padding = 12;
    const leftPadding = 72;
    const bottomPadding = 30;
    const range = max - min || 1;

    const chartWidth = width - leftPadding - padding;
    const chartHeight = height - bottomPadding - padding;
    const midX = leftPadding + chartWidth / 2;
    const toX = (idx) => {
        if (points.length === 1) return midX;
        return leftPadding + (idx / Math.max(points.length - 1, 1)) * chartWidth;
    };
    const toY = (value) => padding + (1 - (value - min) / range) * chartHeight;

    const chartColor = '#8b5cf6';
    const line = points.length === 1
        ? `${midX},${toY(points[0].value)} ${width - padding},${toY(points[0].value)}`
        : points.map((p, idx) => `${toX(idx)},${toY(p.value)}`).join(' ');
    const latest = points[points.length - 1];
    const latestX = toX(points.length - 1);
    const latestY = toY(latest.value);
    const tickColor = 'rgba(255, 255, 255, 0.7)';
    const gridColor = 'rgba(255, 255, 255, 0.12)';
    const fontSize = 11;
    const formatTickValue = (value) => formatMoney(value, currency);
    const formatTickDate = (value) => {
        if (!value) return '';
        const date = new Date(value);
        if (Number.isNaN(date.getTime())) {
            return value;
        }
        return date.toLocaleDateString(undefined, { month: 'short', day: 'numeric' });
    };
    const yTicks = 4;
    const xTicks = Math.min(5, points.length);
    const xIndices = xTicks === 1
        ? [0]
        : Array.from({ length: xTicks }, (_, idx) => Math.round((idx / (xTicks - 1)) * (points.length - 1)));

    return (
        <>
            <div className="nft-market__chart-meta">
                Latest: {formatMoney(latest.value, currency)}
            </div>
            <svg viewBox={`0 0 ${width} ${height}`} preserveAspectRatio="xMidYMid meet">
                {Array.from({ length: yTicks + 1 }, (_, idx) => {
                    const value = min + (range * idx) / yTicks;
                    const y = toY(value);
                    return (
                        <g key={`y-${idx}`}>
                            <line x1={leftPadding} y1={y} x2={width - padding} y2={y} stroke={gridColor} strokeWidth="1" />
                            <text x={leftPadding - 6} y={y + 3} textAnchor="end" fontSize={fontSize} fill={tickColor}>
                                {formatTickValue(value)}
                            </text>
                        </g>
                    );
                })}
                {xIndices.map((idx) => {
                    const point = points[idx];
                    const x = toX(idx);
                    return (
                        <text key={`x-${idx}`} x={x} y={height - padding} textAnchor="middle" fontSize={fontSize} fill={tickColor}>
                            {formatTickDate(point?.date)}
                        </text>
                    );
                })}
                <polyline
                    fill="none"
                    stroke={chartColor}
                    strokeWidth="2"
                    points={line}
                />
                {points.length > 1 ? <circle cx={latestX} cy={latestY} r="3" fill={chartColor} /> : null}
            </svg>
        </>
    );
};

export default function MarketWidget({ slug, currencies = [], defaultCurrency = 'GBP', csrfToken, isAuthed, viewerId }) {
    const [currency, setCurrency] = useState(defaultCurrency);
    const [range, setRange] = useState(defaultRange);
    const [history, setHistory] = useState([]);
    const [listings, setListings] = useState([]);
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState('');

    const loadMarket = useCallback(async () => {
        if (!slug) return;
        setLoading(true);
        setError('');
        try {
            const res = await fetch(`/api/v1/nfts/${slug}/market?currency=${currency}&range=${range}`);
            if (!res.ok) {
                throw new Error('Failed to load marketplace');
            }
            const payload = await res.json();
            const data = payload?.data;
            setHistory(data?.history || []);
            setListings(data?.listings || []);
        } catch (err) {
            setError(err?.message || 'Failed to load marketplace');
        } finally {
            setLoading(false);
        }
    }, [slug, currency, range]);

    useEffect(() => {
        loadMarket();
    }, [loadMarket]);

    const listingRows = useMemo(() => listings.map((listing) => {
        const price = formatMoney(listing.price, listing.currency);
        return (
            <div className="nft-market__row" key={listing.listing_id}>
                <div className="nft-market__cell name">
                    <div className="nft-market__seller-line">
                        {listing.seller ? (
                            <a href={`/profile/${encodeURIComponent(listing.seller)}`}>
                                <strong>{listing.seller}</strong>
                            </a>
                        ) : (
                            <strong>Unknown</strong>
                        )}
                        {listing.listing_id && isAuthed && (!viewerId || listing.seller_user_id !== viewerId) ? (
                            <form method="POST" action={`/conversations/start/${listing.listing_id}`} className="nft-market__inline-contact-form">
                                <input type="hidden" name="_token" value={csrfToken} />
                                <button type="submit" className="nft-market__contact-btn">
                                    Contact me
                                </button>
                            </form>
                        ) : null}
                    </div>
                    <span>Listing #{listing.listing_id}</span>
                </div>
                <div className="nft-market__cell price">{price}</div>
                <div className="nft-market__cell action">
                    {isAuthed && viewerId && listing.seller_user_id === viewerId ? (
                        <span>Your listing</span>
                    ) : isAuthed ? (
                        <button
                            type="button"
                            onClick={async () => {
                                const body = new URLSearchParams({ listing_id: listing.listing_id }).toString();
                                const res = await fetch('/cart', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/x-www-form-urlencoded',
                                        'X-CSRF-TOKEN': csrfToken,
                                    },
                                    body,
                                });
                                if (res.ok) {
                                    loadMarket();
                                }
                            }}
                        >
                            Buy Now
                        </button>
                    ) : (
                        <a href="/login">Login to buy</a>
                    )}
                </div>
            </div>
        );
    }), [listings, isAuthed, csrfToken, loadMarket, viewerId]);

    return (
        <section id="nft-market">
            <div className="nft-market__header">
                <h2>Marketplace</h2>
                <div className="nft-market__controls">
                    <label>
                        <select value={currency} onChange={(e) => setCurrency(e.target.value)}>
                            {currencies.map((cur) => (
                                <option key={cur} value={cur}>{cur}</option>
                            ))}
                        </select>
                    </label>
                    <div className="nft-market__range">
                        {['week', 'month', 'lifetime'].map((value) => (
                            <button
                                key={value}
                                type="button"
                                data-range={value}
                                className={range === value ? 'active' : ''}
                                onClick={() => setRange(value)}
                            >
                                {value.charAt(0).toUpperCase() + value.slice(1)}
                            </button>
                        ))}
                        <button type="button" onClick={loadMarket}>Refresh</button>
                    </div>
                </div>
            </div>

            <div className="nft-market__history">
                <h3>Median Sale Prices</h3>
                <div className="nft-market__chart">
                    {loading ? <p>Loading...</p> : <Chart points={history} currency={currency} />}
                </div>
                {error ? <p>{error}</p> : null}
            </div>

            <div className="nft-market__listings">
                <h3>Listings</h3>
                <div className="nft-market__listings-table">
                    {listingRows.length ? (
                        <div className="nft-market__table">
                            <div className="nft-market__row header">
                                <div className="nft-market__cell name">Seller</div>
                                <div className="nft-market__cell price">Price</div>
                                <div className="nft-market__cell action">Action</div>
                            </div>
                            {listingRows}
                        </div>
                    ) : (
                        <p>{loading ? 'Loading listings...' : 'No active listings right now.'}</p>
                    )}
                </div>
            </div>
        </section>
    );
}
