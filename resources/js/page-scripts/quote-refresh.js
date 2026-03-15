document.addEventListener('DOMContentLoaded', () => {
    const quoteEls = Array.from(document.querySelectorAll('[data-quote-listing]'));
    if (quoteEls.length === 0) return;

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

    const getPreferredCurrency = () => {
        return localStorage.getItem('walletCurrency') || null;
    };

    const refreshQuotes = async () => {
        const preferredCurrency = getPreferredCurrency();
        const entries = quoteEls.map((el) => {
            const tagName = el.tagName.toLowerCase();
            const usePreferred = preferredCurrency && tagName !== 'li';
            return {
                el,
                listingId: el.dataset.quoteListing,
                currency: usePreferred ? preferredCurrency : (el.dataset.currency || 'GBP'),
            };
        }).filter((entry) => entry.listingId);

        await Promise.all(entries.map(async (entry) => {
            try {
                const res = await fetch(`/api/v1/quotes?listing_id=${entry.listingId}&currency=${entry.currency}`);
                if (!res.ok) return;
                const payload = await res.json();
                const data = payload?.data;
                if (!data?.display_amount) return;

                if (entry.el.tagName.toLowerCase() === 'li') {
                    entry.el.innerHTML = `<strong>${entry.currency}:</strong> ${formatMoney(data.display_amount, data.display_currency)}`;
                } else {
                    entry.el.textContent = formatMoney(data.display_amount, data.display_currency);
                }
            } catch (e) {
                // Best-effort refresh only.
            }
        }));
    };

    refreshQuotes();
    setInterval(refreshQuotes, 60000);
    window.addEventListener('walletCurrencyChange', refreshQuotes);
});
