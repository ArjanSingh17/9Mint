@extends('layouts.app')

@push('scripts')
    @vite('resources/js/page-scripts/quote-refresh.js')
@endpush

@section('content')
<div class="container" style="padding: 100px 20px; text-align: center;">
    <h1 style="color: white; margin-bottom: 30px;">My Favourites</h1>

    @if($favourites->isEmpty())
        <p style="color: #aaa;">You haven't liked any NFTs yet.</p>
        <a href="{{ route('products.index') }}" class="btn btn-primary" style="margin-top: 20px;">Go Explore</a>
    @else
        <div class="nft-collection-grid {{ $favourites->count() < 4 ? 'nft-collection-grid--center' : 'nft-collection-grid--start' }}">
            @foreach($favourites as $nft)
                @php
                    $listing = $nft->active_listing ?? null;
                    $price = $listing?->ref_amount;
                    $currency = $listing?->ref_currency ?? 'GBP';
                    $currencySymbol = $currencySymbols[$currency] ?? null;
                    $isLiked = Auth::check() ? Auth::user()->favourites->contains($nft->id) : false;
                @endphp
                <div class="nft-collection-card">
                    <button
                        type="button"
                        class="nft-collection-heart"
                        onclick="toggleLike({{ $nft->id }}, this)"
                        aria-label="Toggle favourite"
                        data-liked="{{ $isLiked ? '1' : '0' }}"
                    >
                        {{ $isLiked ? '♥' : '♡' }}
                    </button>
                    <a href="{{ route('nfts.show', ['slug' => $nft->slug]) }}">
                        <div class="nft-collection-thumb">
                            <img src="{{ asset(ltrim($nft->image_url, '/')) }}" alt="{{ $nft->name }}" />
                        </div>
                        <div class="nft-collection-meta">
                            <h3>{{ $nft->name }}</h3>
                            <p
                                class="nft-collection-price"
                                data-quote-listing="{{ $listing?->id }}"
                                data-currency="{{ $currency }}"
                            >
                                {{ $price !== null
                                    ? ($currencySymbol ? $currencySymbol . number_format($price, 2) : number_format($price, 2) . ' ' . $currency)
                                    : 'Unavailable' }}
                            </p>
                            <p class="nft-collection-stock">
                                Editions remaining: {{ $nft->editions_remaining }} / {{ $nft->editions_total }}
                            </p>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
    async function toggleLike(nftId, btn) {
        @guest
            window.location.href = "{{ route('login') }}";
            return;
        @endguest

        const isLiked = btn.innerText.trim() === '♥';
        btn.innerText = isLiked ? '♡' : '♥';
        btn.style.color = isLiked ? 'white' : '#ff4d4d';
        btn.dataset.liked = isLiked ? '0' : '1';

        try {
            const response = await fetch(`/nfts/${nftId}/toggle-like`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            });

            if (!response.ok) {
                throw new Error('Server rejected the like');
            }

            if (isLiked) {
                const card = btn.closest('.nft-collection-card');
                if (card) {
                    card.remove();
                }
            }
        } catch (error) {
            btn.innerText = isLiked ? '♥' : '♡';
            btn.style.color = isLiked ? '#ff4d4d' : 'white';
            btn.dataset.liked = isLiked ? '1' : '0';
            alert("Could not save like. Are you still logged in?");
        }
    }
</script>
@endpush