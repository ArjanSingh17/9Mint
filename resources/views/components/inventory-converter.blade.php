@props([
    'totals' => [],
    'rateMatrix' => [],
    'baseCurrency' => 'GBP',
    'valuedTokenCount' => 0,
])

@once
    @push('styles')
        <style>
            .inventory-convertor {
                margin-top: 10px;
                border-top: 1px solid var(--border-soft);
                padding-top: 10px;
            }

            .inventory-convertor__row {
                display: grid;
                grid-template-columns: repeat(2, minmax(150px, 1fr));
                gap: 10px;
                margin-bottom: 10px;
            }

            .inventory-convertor label {
                display: block;
                font-size: 0.8rem;
                color: var(--subtext-color);
                margin-bottom: 4px;
            }

            .inventory-convertor select {
                width: 100%;
                padding: 8px 10px;
                border-radius: 8px;
                border: 1px solid var(--border-input);
                background: var(--surface-input);
                color: var(--text-main);
                font-size: 0.9rem;
            }
        </style>
    @endpush

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const roots = document.querySelectorAll('[data-inventory-convertor]');
                if (!roots.length) return;

                const symbols = { GBP: '£', USD: '$', EUR: '€', BTC: '₿', ETH: 'Ξ' };

                const formatMoney = function (value, currency) {
                    const num = Number(value || 0);
                    const isCrypto = ['BTC', 'ETH'].includes(currency);
                    const decimals = isCrypto ? 8 : 2;
                    const symbol = symbols[currency] || '';
                    return symbol ? symbol + num.toFixed(decimals) : num.toFixed(decimals) + ' ' + currency;
                };

                roots.forEach(function (root) {
                    const fromSelect = root.querySelector('[data-inventory-from]');
                    const toSelect = root.querySelector('[data-inventory-to]');
                    const fromTotalEl = root.querySelector('[data-inventory-from-total]');
                    const toTotalEl = root.querySelector('[data-inventory-to-total]');
                    const rateEl = root.querySelector('[data-inventory-rate]');
                    if (!fromSelect || !toSelect || !fromTotalEl || !toTotalEl || !rateEl) return;

                    const totals = JSON.parse(root.dataset.totals || '{}');
                    const rateMatrix = JSON.parse(root.dataset.rateMatrix || '{}');

                    const pickDefaultTo = function () {
                        const from = fromSelect.value;
                        const options = Array.from(toSelect.options).map(function (opt) { return opt.value; });
                        const different = options.find(function (c) { return c !== from; });
                        if (different) toSelect.value = different;
                    };

                    const update = function () {
                        const from = fromSelect.value;
                        const to = toSelect.value;
                        const fromTotal = Number(totals[from] || 0);
                        const toTotal = Number(totals[to] || 0);
                        const rate = rateMatrix[from] ? rateMatrix[from][to] : null;

                        fromTotalEl.textContent = 'Inventory total in ' + from + ': ' + formatMoney(fromTotal, from);
                        toTotalEl.textContent = 'Inventory total in ' + to + ': ' + formatMoney(toTotal, to);
                        if (rate === null || rate === undefined) {
                            rateEl.textContent = 'Conversion unavailable for ' + from + ' -> ' + to + '.';
                        } else {
                            const isCrypto = ['BTC', 'ETH'].includes(to);
                            rateEl.textContent = 'Rate: 1 ' + from + ' = ' + Number(rate).toFixed(isCrypto ? 8 : 4) + ' ' + to;
                        }
                    };

                    pickDefaultTo();
                    fromSelect.addEventListener('change', function () {
                        if (toSelect.value === fromSelect.value) {
                            pickDefaultTo();
                        }
                        update();
                    });
                    toSelect.addEventListener('change', function () {
                        if (toSelect.value === fromSelect.value) {
                            pickDefaultTo();
                        }
                        update();
                    });
                    update();
                });
            });
        </script>
    @endpush
@endonce

@if ($valuedTokenCount > 0)
    <div
        class="inventory-convertor"
        data-inventory-convertor
        data-totals='@json($totals)'
        data-rate-matrix='@json($rateMatrix)'
    >
        <div class="inventory-convertor__row">
            <div>
                <label>From currency</label>
                <select data-inventory-from>
                    @foreach (array_keys($totals) as $currency)
                        <option value="{{ $currency }}" @selected($currency === $baseCurrency)>{{ $currency }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label>To currency</label>
                <select data-inventory-to>
                    @foreach (array_keys($totals) as $currency)
                        <option value="{{ $currency }}" @selected($currency !== $baseCurrency ? false : true)>{{ $currency }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="inventory-summary__grid">
            <p class="inventory-summary__item" data-inventory-from-total></p>
            <p class="inventory-summary__item" data-inventory-to-total></p>
            <p class="inventory-summary__item" data-inventory-rate></p>
        </div>
    </div>
@else
    <p class="inventory-summary__item">No priced tokens available yet for valuation.</p>
@endif
