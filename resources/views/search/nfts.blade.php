@extends('layouts.app')

@section('title', 'All NFTs')

@push('styles')
    @vite('resources/css/pages/collections-legacy.css')
    @vite('resources/css/pages/app-pages.css')
@endpush

@push('scripts')
    @vite('resources/js/page-scripts/quote-refresh.js')
@endpush

@php
    $currencySymbols = [
        'GBP' => '£',
        'USD' => '$',
        'EUR' => '€',
        'BTC' => '₿',
        'ETH' => 'Ξ',
    ];
@endphp

@section('content')
    <section id="NFT_collections">
        <h2>All NFTs</h2>

        <div class="nft-filter-wrapper">
            <form method="GET" action="{{ route('search.nfts') }}" class="nft-filter-bar">
                <div class="nft-filter-group" style="min-width: 220px;">
                    <label for="filter-q">Name</label>
                    <input id="filter-q" type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Search by NFT name">
                </div>

                <div class="nft-filter-group">
                    <label for="filter-sort">Sort</label>
                    <select id="filter-sort" name="sort">
                        <option value="newest" @selected(($filters['sort'] ?? '') === 'newest')>Newest</option>
                        <option value="name-asc" @selected(($filters['sort'] ?? '') === 'name-asc')>Name A-Z</option>
                        <option value="name-desc" @selected(($filters['sort'] ?? '') === 'name-desc')>Name Z-A</option>
                        <option value="price-asc" @selected(($filters['sort'] ?? '') === 'price-asc')>Price: Low to High</option>
                        <option value="price-desc" @selected(($filters['sort'] ?? '') === 'price-desc')>Price: High to Low</option>
                        <option value="rating-desc" @selected(($filters['sort'] ?? '') === 'rating-desc')>Rating: High to Low</option>
                    </select>
                </div>

                <div class="nft-filter-group">
                    <label for="filter-currency">Currency</label>
                    <select id="filter-currency" name="currency">
                        <option value="">All</option>
                        @foreach(($currencies ?? collect()) as $currency)
                            <option value="{{ $currency }}" @selected(($filters['currency'] ?? '') === $currency)>{{ $currency }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="nft-filter-group">
                    <label for="filter-min-price">Min Price</label>
                    <input id="filter-min-price" type="number" name="min_price" min="0" step="0.01" value="{{ $filters['min_price'] ?? '' }}" placeholder="0">
                </div>

                <div class="nft-filter-group">
                    <label for="filter-max-price">Max Price</label>
                    <input id="filter-max-price" type="number" name="max_price" min="0" step="0.01" value="{{ $filters['max_price'] ?? '' }}" placeholder="Any">
                </div>

                <div class="nft-filter-break"></div>

                <div class="nft-filter-group nft-filter-check">
                    <label><input type="checkbox" name="in_stock" value="1" @checked(($filters['in_stock'] ?? false))> In Stock Only</label>
                </div>

                <div class="nft-filter-group nft-filter-check">
                    <label><input type="checkbox" name="one_of_one" value="1" @checked(($filters['one_of_one'] ?? false))> 1-of-1 Only</label>
                </div>

                <button type="submit" class="filter-apply-btn">Apply</button>
                <a href="{{ route('search.nfts') }}" class="filter-reset-btn" style="text-decoration:none;">Reset</a>
            </form>
        </div>

        @if ($nfts->isEmpty())
            <p class="no-nfts" style="text-align:center;">No NFTs match your search filters.</p>
        @else
            <div class="nft-collection-grid nft-collection-grid--center">
                @foreach ($nfts as $nft)
                    @php
                        $listing = $nft->active_listing ?? null;
                        $price = $listing?->ref_amount;
                        $currency = $listing?->ref_currency ?? 'GBP';
                        $currencySymbol = $currencySymbols[$currency] ?? null;
                    @endphp
                    <div class="nft-collection-card">
                        <a href="{{ route('nfts.show', ['slug' => $nft->slug]) }}">
                            <div class="nft-collection-thumb">
                                <img src="{{ asset(ltrim($nft->thumbnail_url ?? $nft->image_url, '/')) }}" alt="{{ $nft->name }}" />
                            </div>
                            <div class="nft-collection-meta">
                                <h3>{{ $nft->name }}</h3>
                                <p class="nft-collection-price" data-quote-listing="{{ $listing?->id }}" data-currency="{{ $currency }}">
                                    {{ $price !== null
                                        ? ($currencySymbol ? $currencySymbol . number_format($price, 2) : number_format($price, 2) . ' ' . $currency)
                                        : 'Unavailable' }}
                                </p>
                                <p class="nft-collection-stock">
                                    Editions listed: {{ (int) ($nft->listed_editions_count ?? 0) }} / {{ $nft->editions_total }}
                                </p>
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>

            <div style="margin-top:16px;">
                {{ $nfts->links() }}
            </div>
        @endif
    </section>
@endsection
