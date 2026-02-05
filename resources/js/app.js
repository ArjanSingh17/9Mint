// App bootstrap
import './bootstrap';

document.addEventListener('DOMContentLoaded', () => {
    const select = document.querySelector('[data-wallet-currency]');
    const balanceEl = document.querySelector('[data-wallet-balance]');
    if (!select) return;

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

    const updateBalance = () => {
        const selected = select.options[select.selectedIndex];
        const currency = selected?.value;
        const net = selected?.dataset?.net;
        if (balanceEl && currency) {
            balanceEl.textContent = formatMoney(net, currency);
        }
        if (currency) {
            localStorage.setItem('walletCurrency', currency);
            window.dispatchEvent(new CustomEvent('walletCurrencyChange', { detail: { currency } }));
        }
    };

    const saved = localStorage.getItem('walletCurrency');
    if (saved) {
        const option = Array.from(select.options).find((opt) => opt.value === saved);
        if (option) select.value = saved;
    }

    select.addEventListener('change', updateBalance);
    updateBalance();
});

