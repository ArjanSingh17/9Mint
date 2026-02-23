@extends('layouts.app')

@section('title', $collection->name)

@push('styles')
    @vite('resources/css/pages/collections-legacy.css')
@endpush

@push('scripts')
    @vite('resources/js/page-scripts/quote-refresh.js')
@endpush

@section('content')
    {{-- Title --}}
    <h1 class="collection-title">{{ $collection->name }}</h1>

    {{-- Items --}}
    @if ($nfts->isEmpty())
        <p class="no-nfts">
            No NFTs have been added to this collection yet.
        </p>
    @else
        <div class="nft-collection-grid">
            @foreach ($nfts as $nft)
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
@endsection
@push('scripts')
<script>
    async function toggleLike(nftId, btn) {
        // 1. Check if user is logged in
        @guest
            window.location.href = "{{ route('login') }}";
            return;
        @endguest

        // 2. Optimistic UI: Turn it red immediately
        const isLiked = btn.innerText.trim() === '♥';
        btn.innerText = isLiked ? '♡' : '♥';
        btn.style.color = isLiked ? 'white' : '#ff4d4d';

        try {
            // 3. Send the request to the new WEB route
           const response = await fetch(`/nfts/${nftId}/toggle-like`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    // This token proves you are a valid user on the site
                    'X-CSRF-TOKEN': '{{ csrf_token() }}' 
                }
            });

            // 4. If the server says "No", throw an error
            if (!response.ok) {
                throw new Error('Server rejected the like');
            }
            console.log('Saved like for NFT ' + nftId);

        } catch (error) {
            // 5. If it failed, switch the heart back so you know it didn't save
            console.error("Save failed:", error);
            btn.innerText = isLiked ? '♥' : '♡';
            btn.style.color = isLiked ? '#ff4d4d' : 'white';
            alert("Could not save like. Are you still logged in?");
        }
    }
</script>