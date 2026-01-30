@props([
    'image',
    'title',
    'description',
    'slug',
    // Editions information for this NFT (optional; if null, editions are not shown)
    'editionsTotal' => null,
    'editionsRemaining' => null,
])

<div class="NFT-Card">
    {{-- Image --}}
    <img src="{{ asset($image) }}" alt="{{ $title }}" class="nft-image" />

    {{-- Details --}}
    <div class="nft-info">
        <h2>{{ $title }}</h2>
        {{-- Changed to nft-description to match standard naming --}}
        <p class="nft-description">
            {{ $description }}
        </p>

        {{-- Editions --}}
        @if(!is_null($editionsRemaining) && !is_null($editionsTotal))
            <p class="nft-editions">
                @if($editionsRemaining > 0)
                    Editions remaining: {{ $editionsRemaining }} / {{ $editionsTotal }}
                @else
                    <span class="nft-out-of-stock">All editions sold out</span>
                @endif
            </p>
        @endif

        {{-- Size --}}
        <div class="size-option">
            <p>Select your size:</p>
            {{-- Added classes so your CSS hover/selected effects work --}}
            <button class="small-size">Small</button>
            <button class="medium-size">Medium</button>
            <button class="large-size">Large</button>
        </div>

        @php
            $hasEditionsInfo = !is_null($editionsRemaining) && !is_null($editionsTotal);
        @endphp

        {{-- CTA --}}
        @if(!$hasEditionsInfo || $editionsRemaining > 0)
            @auth
                <form method="POST" action="{{ route('cart.store') }}" class="inline">
                    @csrf
                    <input type="hidden" name="nft_slug" value="{{ $slug }}">
                    <button type="submit" class="Add-to-basket">Add to basket</button>
                </form>
            @else
                <a class="Add-to-basket" href="{{ route('login', ['redirect' => request()->fullUrl()]) }}">
                    Login to add to basket
                </a>
            @endauth
        @else
            <p class="nft-out-of-stock-message">All editions have been sold</p>
        @endif
    </div>
</div>


