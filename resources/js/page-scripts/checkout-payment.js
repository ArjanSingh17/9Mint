document.addEventListener('DOMContentLoaded', () => {
    const methodInputs = Array.from(document.querySelectorAll('input[name="provider"]'));
    const detailSections = Array.from(document.querySelectorAll('.payment-details'));
    const summary = document.querySelector('[data-payment-summary]');
    const walletNetworkSelect = document.querySelector('select[name="wallet_network"]');
    const walletNetworkLabel = document.querySelector('[data-wallet-network]');
    const walletCurrencySelect = document.querySelector('[data-wallet-currency-select]');
    const walletBalanceLabel = document.querySelector('[data-wallet-balance]');
    const walletNetworkRow = document.querySelector('[data-wallet-network-row]');
    const amountLabel = document.querySelector('[data-payment-amount]');
    const conversionText = document.querySelector('[data-conversion-text]');

    if (methodInputs.length === 0 || detailSections.length === 0) return;

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

    const updateConversion = () => {
        if (!summary || !conversionText || !amountLabel) return;

        const payAmount = summary.dataset.payAmount;
        const payCurrency = summary.dataset.payCurrency;
        const refAmount = summary.dataset.refAmount;
        const refCurrency = summary.dataset.refCurrency;

        if (!payAmount || !payCurrency || !refAmount || !refCurrency) return;

        amountLabel.textContent = `Amount due: ${formatMoney(payAmount, payCurrency)}`;
        conversionText.textContent = `Conversion: ${formatMoney(refAmount, refCurrency)} equals ${formatMoney(payAmount, payCurrency)} at checkout time.`;
    };

    const updateWalletBalance = () => {
        if (!walletCurrencySelect || !walletBalanceLabel) return;
        const option = walletCurrencySelect.selectedOptions[0];
        const currency = option?.value;
        const balance = option?.dataset?.balance;
        if (!currency) return;
        walletBalanceLabel.textContent = `Balance: ${formatMoney(balance, currency)}`;
    };

    const updateWalletSummary = async () => {
        if (!summary || !conversionText || !amountLabel || !walletCurrencySelect) return;

        const payAmount = summary.dataset.payAmount;
        const payCurrency = summary.dataset.payCurrency;
        const walletCurrency = walletCurrencySelect.value;

        if (!payAmount || !payCurrency || !walletCurrency) return;

        if (payCurrency === walletCurrency) {
            amountLabel.textContent = `Amount due: ${formatMoney(payAmount, payCurrency)}`;
            conversionText.textContent = 'No conversion needed for wallet payment.';
            return;
        }

        try {
            const res = await fetch(`/api/v1/convert?amount=${payAmount}&from=${payCurrency}&to=${walletCurrency}`);
            if (!res.ok) return;
            const payload = await res.json();
            const data = payload?.data;
            if (!data?.converted) return;

            amountLabel.textContent = `Amount due: ${formatMoney(data.converted, walletCurrency)}`;
            conversionText.textContent = `${formatMoney(payAmount, payCurrency)} converts to ${formatMoney(data.converted, walletCurrency)} at checkout time.`;
        } catch (e) {
            // Best-effort display only.
        }
    };

    const setActive = (value) => {
        detailSections.forEach((section) => {
            const isActive = section.dataset.provider === value;
            section.classList.toggle('is-hidden', !isActive);
            section.querySelectorAll('input, select, textarea').forEach((input) => {
                input.disabled = !isActive;
            });
        });

        if (summary) {
            const showSummary = value === 'mock_crypto' || value === 'mock_wallet';
            summary.classList.toggle('is-hidden', !showSummary);
            if (walletNetworkRow) {
                walletNetworkRow.classList.toggle('is-hidden', value !== 'mock_crypto');
            }
            if (value === 'mock_crypto') updateConversion();
            if (value === 'mock_wallet') updateWalletSummary();
        }
    };

    methodInputs.forEach((input) => {
        input.addEventListener('change', () => setActive(input.value));
    });

    const checked = methodInputs.find((input) => input.checked);
    if (checked) {
        setActive(checked.value);
    } else if (summary) {
        summary.classList.add('is-hidden');
    }

    if (summary && (!checked || (checked.value !== 'mock_crypto' && checked.value !== 'mock_wallet'))) {
        summary.classList.add('is-hidden');
    }

    if (walletNetworkSelect && walletNetworkLabel) {
        const updateNetwork = () => {
            walletNetworkLabel.textContent = walletNetworkSelect.value;
            updateConversion();
        };
        walletNetworkSelect.addEventListener('change', updateNetwork);
        updateNetwork();
    }

    if (walletCurrencySelect) {
        walletCurrencySelect.addEventListener('change', () => {
            updateWalletBalance();
            updateWalletSummary();
        });
        updateWalletBalance();
    }
});
