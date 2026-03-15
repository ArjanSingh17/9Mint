@extends('layouts.app')

@php
    $inventoryUser = $inventoryUser ?? auth()->user();
    $isOwnerInventory = $isOwnerInventory ?? true;
    $inventoryTotals = $inventoryTotals ?? [];
    $inventoryValuationBase = $inventoryValuationBase ?? 'GBP';
    $inventoryRateMatrix = $inventoryRateMatrix ?? [];
    $inventoryValuedTokenCount = $inventoryValuedTokenCount ?? 0;
    $currencySymbols = [
        'GBP' => '£',
        'USD' => '$',
        'EUR' => '€',
        'BTC' => '₿',
        'ETH' => 'Ξ',
    ];
@endphp

@section('title', $isOwnerInventory ? 'My Tokens' : ($inventoryUser->name . "'s Inventory"))

@push('styles')
    @vite('resources/css/pages/app-pages.css')
    <style>
        .inventory-page {
            max-width: 1000px;
            margin: 50px auto;
            padding: 0 20px 60px;
        }

        .inventory-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 16px;
        }

        .inventory-summary {
            margin-bottom: 18px;
            background: var(--surface-panel);
            border: 1px solid var(--border-soft);
            border-radius: 10px;
            padding: 14px 16px;
        }

        .inventory-summary__title {
            margin: 0 0 6px;
            font-size: 0.95rem;
            color: var(--subtext-color);
            text-align: left;
        }

        .inventory-summary__total-box {
            margin-bottom: 10px;
            background: color-mix(in srgb, var(--surface-panel) 82%, #000 18%);
            border: 1px solid var(--border-soft);
            border-radius: 8px;
            padding: 10px 12px;
        }

        .inventory-summary__total {
            margin: 0;
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--text-main);
            text-align: left;
        }

        .inventory-summary__grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 8px 12px;
        }

        .inventory-summary__item {
            margin: 0;
            font-size: 0.85rem;
            color: var(--subtext-color);
            text-align: left;
        }

        .inventory-profile-card {
            background: var(--surface-panel);
            border-radius: 10px;
            overflow: hidden;
            box-shadow: var(--shadow-elevated);
        }

        .inventory-profile-card__link {
            display: block;
            text-decoration: none;
            color: var(--text-main);
            transition: transform 0.2s ease;
        }

        .inventory-profile-card__link:hover {
            transform: translateY(-4px);
        }

        .inventory-profile-card__link img {
            width: 100%;
            aspect-ratio: 1 / 1.2;
            object-fit: cover;
            display: block;
        }

        .inventory-profile-card__name {
            display: block;
            padding: 10px 12px 0;
            font-size: 14px;
            font-weight: 600;
            color: var(--text-main);
        }

        .inventory-profile-card__token {
            display: block;
            padding: 0 12px 10px;
            margin-top: -6px;
            font-size: 12px;
            font-weight: 500;
            color: var(--subtext-color);
        }

        .inventory-profile-card__actions {
            border-top: 1px solid var(--border-soft);
            padding: 10px 12px 12px;
        }

        .inventory-profile-card__price {
            margin: 0 0 8px;
            color: var(--subtext-color);
            font-size: 0.9rem;
        }

        .inventory-profile-card__field {
            display: block;
            margin-bottom: 8px;
        }

        .inventory-profile-card__field span {
            display: block;
            margin-bottom: 4px;
            font-size: 12px;
            color: var(--subtext-color);
        }

        .inventory-profile-card__field input,
        .inventory-profile-card__field select {
            width: 100%;
            padding: 8px 10px;
            border-radius: 8px;
            border: 1px solid var(--border-input);
            background: var(--surface-input);
            color: var(--text-main);
            font-size: 0.9rem;
        }

        .inventory-profile-card__button {
            width: 100%;
            border: none;
            border-radius: 8px;
            padding: 9px 12px;
            font-weight: 600;
            background: var(--link-hover);
            color: #fff;
            cursor: pointer;
        }

        .inventory-profile-card__button:hover {
            background: color-mix(in srgb, var(--link-hover) 85%, #000 15%);
        }

        .inventory-profile-card__hint {
            margin: 8px 0 0;
            font-size: 0.75rem;
            color: var(--subtext-color);
        }
    </style>
@endpush

@section('content')
    <section class="inventory-page">
        <h1>{{ $isOwnerInventory ? 'My Tokens' : ($inventoryUser->name . "'s Inventory") }}</h1>

        @if (session('status'))
            <div class="orders-status">{{ session('status') }}</div>
        @endif

        @if (session('error'))
            <div class="orders-status">{{ session('error') }}</div>
        @endif

        @if (!empty($inventoryTotals))
            @php
                $baseTotal = (float) ($inventoryTotals[$inventoryValuationBase] ?? 0);
                $baseSymbol = $currencySymbols[$inventoryValuationBase] ?? null;
            @endphp
            <section class="inventory-summary">
                <div class="inventory-summary__total-box">
                    <p class="inventory-summary__title">Total inventory value</p>
                    <p class="inventory-summary__total">
                        {{ $baseSymbol ? $baseSymbol . number_format($baseTotal, 2) : number_format($baseTotal, 2) . ' ' . $inventoryValuationBase }}
                    </p>
                </div>

                <x-inventory-converter
                    :totals="$inventoryTotals"
                    :rate-matrix="$inventoryRateMatrix"
                    :base-currency="$inventoryValuationBase"
                    :valued-token-count="$inventoryValuedTokenCount"
                />
            </section>
        @endif

        @if ($tokens->isEmpty())
            <p>{{ $isOwnerInventory ? 'You do not own any tokens yet.' : 'This user does not own any tokens yet.' }}</p>
        @else
            <div class="inventory-grid">
                @foreach ($tokens as $token)
                    @php
                        $nft = $token->nft;
                        $listing = $token->listing;
                        $isEligible = in_array($token->id, $eligibleTokenIds ?? [], true);
                    @endphp
                    <article class="inventory-profile-card">
                        <a href="{{ route('nfts.show', $nft->slug) }}" class="inventory-profile-card__link">
                            <img src="{{ asset(ltrim($nft->image_url, '/')) }}" alt="{{ $nft->name }}">
                            <span class="inventory-profile-card__name">{{ $nft->name }}</span>
                            <span class="inventory-profile-card__token">Token #{{ $token->serial_number }}</span>
                        </a>

                        <div class="inventory-profile-card__actions">
                            @if ($isOwnerInventory)
                                @if ($listing && in_array($listing->status, ['active', 'reserved'], true))
                                    <p class="inventory-profile-card__price">
                                        Listed for {{ $listing->ref_currency }} {{ number_format($listing->ref_amount, 2) }}
                                    </p>
                                    <form method="POST" action="{{ route('inventory.listing.destroy', $listing->id) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inventory-profile-card__button">Unlist</button>
                                    </form>
                                @else
                                    @if (! $isEligible)
                                        <p class="inventory-profile-card__price">Only paid NFTs can be listed.</p>
                                    @else
                                        <form method="POST" action="{{ route('inventory.listing.store') }}">
                                            @csrf
                                            <input type="hidden" name="token_id" value="{{ $token->id }}">
                                            <label class="inventory-profile-card__field">
                                                <span>Price</span>
                                                <input type="number" step="0.01" min="0" name="ref_amount" placeholder="Price" required>
                                            </label>
                                            <label class="inventory-profile-card__field">
                                                <span>Currency</span>
                                                <select name="ref_currency">
                                                    @foreach ($currencies as $currency)
                                                        <option value="{{ $currency }}">{{ $currency }}</option>
                                                    @endforeach
                                                </select>
                                            </label>
                                            <button type="submit" class="inventory-profile-card__button">List</button>
                                            <p class="inventory-profile-card__hint">9Mint fee: 2.5% (you receive 97.5%).</p>
                                        </form>
                                    @endif
                                @endif
                            @else
                                @if ($listing && in_array($listing->status, ['active', 'reserved'], true))
                                    <p class="inventory-profile-card__price">
                                        Listed for {{ $listing->ref_currency }} {{ number_format($listing->ref_amount, 2) }}
                                    </p>
                                @else
                                    <p class="inventory-profile-card__price">Not currently listed.</p>
                                @endif
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </section>
@endsection
