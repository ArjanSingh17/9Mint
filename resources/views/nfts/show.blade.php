@extends('layouts.app')

@section('title', $nft->name)

@push('styles')
    @vite('resources/css/pages/app-pages.css')
    @vite('resources/css/pages/collections-legacy.css')
@endpush

@push('scripts')
    @vite('resources/js/nft-marketplace/marketplace-entry.jsx')
    @vite('resources/js/page-scripts/quote-refresh.js')
@endpush

@section('content')
    <section class="nft-detail">
        <div class="nft-detail__media">
            <img src="{{ asset(ltrim($nft->image_url, '/')) }}" alt="{{ $nft->name }}">
        </div>
        <div class="nft-detail__info nft-detail__info-panel">
            <h1>{{ $nft->name }}</h1>
            @if ($listing)
                @php
                    $sellerName = $listing->seller?->email === 'platform@9mint.local'
                        ? '9Mint'
                        : ($listing->seller?->name ?? 'Unknown');
                @endphp
                <div class="nft-detail__seller-row">
                    <p class="nft-detail__marketplace-note">
                        Seller:
                        @if ($listing->seller?->name)
                            <a href="{{ route('profile.show', ['username' => $listing->seller->name]) }}">{{ $sellerName }}</a>
                        @else
                            {{ $sellerName }}
                        @endif
                        @auth
                            @if ($listing->seller_user_id !== auth()->id())
                                <form method="POST" action="{{ route('conversations.start', $listing->id) }}" class="nft-detail__inline-contact-form">
                                    @csrf
                                    <button type="submit" class="nft-detail__contact-btn">Contact me</button>
                                </form>
                            @endif
                        @else
                            <a href="{{ route('login', ['redirect' => request()->fullUrl()]) }}" class="nft-detail__contact-btn">Contact me</a>
                        @endauth
                        • Listing #{{ $listing->id }}
                    </p>
                </div>
            @endif
            @if ($collection)
                <p class="nft-detail__collection">
                    Collection: <a href="{{ route('collections.show', ['slug' => $collection->slug]) }}">{{ $collection->name }}</a>
                </p>
            @endif
            <p class="nft-detail__description">{{ $nft->description }}</p>

            <p class="nft-detail__editions">
                Editions remaining: {{ $nft->editions_remaining }} / {{ $nft->editions_total }}
            </p>

            @if ($listing)
                @php $refSymbol = $currencySymbols[$listing->ref_currency ?? 'GBP'] ?? null; @endphp
                @auth
                    @if ($listing->seller_user_id === auth()->id())
                        <button type="button" class="Add-to-basket" disabled>You own this NFT</button>
                    @else
                        <form method="POST" action="{{ route('cart.store') }}" class="inline">
                            @csrf
                            <input type="hidden" name="listing_id" value="{{ $listing->id }}">
                            <button type="submit" class="Add-to-basket">Buy now</button>
                        </form>
                    @endif
                @else
                    <a class="Add-to-basket" href="{{ route('login', ['redirect' => request()->fullUrl()]) }}">
                        Login to buy
                    </a>
                @endauth

                <p class="nft-detail__price">
                    Reference price:
                    {{ $refSymbol
                        ? $refSymbol . number_format($listing->ref_amount, 2)
                        : number_format($listing->ref_amount, 2) . ' ' . $listing->ref_currency }}
                </p>

                @if (!empty($quotes))
                    <div class="nft-detail__prices">
                        <h3>Prices by currency</h3>
                        <ul>
                            @foreach ($quotes as $currency => $quote)
                                @php $quoteSymbol = $currencySymbols[$quote['pay_currency'] ?? $currency] ?? null; @endphp
                                <li data-quote-listing="{{ $listing->id }}" data-currency="{{ $currency }}">
                                    <strong>{{ $currency }}:</strong>
                                    {{ $quoteSymbol
                                        ? $quoteSymbol . number_format($quote['pay_amount'], 2)
                                        : number_format($quote['pay_amount'], 2) . ' ' . $quote['pay_currency'] }}
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

            @else
                <p class="nft-out-of-stock-message">No active listings for this NFT.</p>
            @endif
        </div>
    </section>

    @auth
        @if (!empty($ownedTokens) && $ownedTokens->isNotEmpty())
            <section class="nft-detail__owner-panel">
                <h2>Your Tokens</h2>
                <p class="nft-detail__owner-hint">List your owned tokens for resale. Pending NFTs are unable to be traded or listed for sale.</p>
                <div class="token-strip-list">
                    @foreach ($ownedTokens as $token)
                        @php
                            $tokenListing = $token->listing;
                            $isEligible = in_array($token->id, $eligibleTokenIds ?? [], true);
                        @endphp
                        <div class="token-strip">
                            <div class="token-strip__meta">
                                <h3>{{ $nft->name }}</h3>
                                <p class="token-strip__sub">Token #{{ $token->serial_number }}</p>

                                @if ($tokenListing && in_array($tokenListing->status, ['active', 'reserved'], true))
                                    <p class="token-strip__price">
                                        Listed for {{ $tokenListing->ref_currency }} {{ number_format($tokenListing->ref_amount, 2) }}
                                    </p>
                                @elseif (! $isEligible)
                                    <p class="token-strip__price">Pending NFTs are unable to be traded or listed for sale.</p>
                                @endif
                            </div>

                            <div class="token-strip__actions">
                                @if ($tokenListing && in_array($tokenListing->status, ['active', 'reserved'], true))
                                    <form method="POST" action="{{ route('inventory.listing.destroy', $tokenListing->id) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit">Unlist</button>
                                    </form>
                                @elseif ($isEligible)
                                    <form method="POST" action="{{ route('inventory.listing.store') }}" class="token-strip__form">
                                        @csrf
                                        <input type="hidden" name="token_id" value="{{ $token->id }}">
                                        <input type="number" step="0.01" min="0" name="ref_amount" placeholder="Price" required>
                                        <select name="ref_currency">
                                            @foreach ($currencies as $currency)
                                                <option value="{{ $currency }}">{{ $currency }}</option>
                                            @endforeach
                                        </select>
                                        <button type="submit">List</button>
                                    </form>
                                    <p class="token-strip__hint">9Mint fee: 2.5% (you receive 97.5%).</p>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif
    @endauth

    <div
        id="nft-market-root"
        data-nft-slug="{{ $nft->slug }}"
        data-default-currency="{{ $currencies[0] ?? 'GBP' }}"
        data-currencies='@json($currencies)'
        data-csrf="{{ csrf_token() }}"
        data-auth="{{ auth()->check() ? '1' : '0' }}"
        data-viewer-id="{{ auth()->id() }}"
    ></div>
@endsection
