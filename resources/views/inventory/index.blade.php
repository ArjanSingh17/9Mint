@extends('layouts.app')

@section('title', 'My Tokens')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/App.css') }}">
@endpush

@section('content')
    <section class="inventory-page">
        <h1>My Tokens</h1>

        @if (session('status'))
            <div class="orders-status">{{ session('status') }}</div>
        @endif

        @if (session('error'))
            <div class="orders-status">{{ session('error') }}</div>
        @endif

        @if ($tokens->isEmpty())
            <p>You do not own any tokens yet.</p>
        @else
            <div class="inventory-grid">
                @foreach ($tokens as $token)
                    @php
                        $nft = $token->nft;
                        $listing = $token->listing;
                        $isEligible = in_array($token->id, $eligibleTokenIds ?? [], true);
                    @endphp
                    <div class="inventory-card">
                        <div class="inventory-card__media">
                            <img src="{{ asset(ltrim($nft->image_url, '/')) }}" alt="{{ $nft->name }}">
                            <div class="inventory-card__label">
                                <span>{{ $nft->name }}</span>
                                <span>Token #{{ $token->serial_number }}</span>
                            </div>
                        </div>
                        <div class="inventory-card__overlay">
                            <div class="inventory-card__panel">
                                <h3>{{ $nft->name }}</h3>
                                <p class="inventory-card__meta">Token #{{ $token->serial_number }}</p>

                                @if ($listing && in_array($listing->status, ['active', 'reserved'], true))
                                    <p class="inventory-card__price">
                                        Listed for {{ $listing->ref_currency }} {{ number_format($listing->ref_amount, 2) }}
                                    </p>
                                    <form method="POST" action="{{ route('inventory.listing.destroy', $listing->id) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inventory-card__button">Unlist</button>
                                    </form>
                                @else
                                    @if (! $isEligible)
                                        <p class="inventory-card__price">Only paid NFTs can be listed.</p>
                                    @else
                                        <form method="POST" action="{{ route('inventory.listing.store') }}">
                                            @csrf
                                            <input type="hidden" name="token_id" value="{{ $token->id }}">
                                            <label class="inventory-card__field">
                                                <span>Price</span>
                                                <input type="number" step="0.01" min="0" name="ref_amount" placeholder="Price" required>
                                            </label>
                                            <label class="inventory-card__field">
                                                <span>Currency</span>
                                                <select name="ref_currency">
                                                    @foreach ($currencies as $currency)
                                                        <option value="{{ $currency }}">{{ $currency }}</option>
                                                    @endforeach
                                                </select>
                                            </label>
                                            <button type="submit" class="inventory-card__button">List</button>
                                            <p class="inventory-card__hint">9Mint fee: 2.5% (you receive 97.5%).</p>
                                        </form>
                                    @endif
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </section>
@endsection
