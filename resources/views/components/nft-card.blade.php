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
    <img src="{{ asset($image) }}" alt="{{ $title }}" class="nft-image" />

    <div class="nft-info">
        <h2>{{ $title }}</h2>
        <p class="nf-description">
            {{ $description }}
        </p>

        @if(!is_null($editionsRemaining) && !is_null($editionsTotal))
            <p class="nft-editions">
                @if($editionsRemaining > 0)
                    Editions remaining: {{ $editionsRemaining }} / {{ $editionsTotal }}
                @else
                    <span class="nft-out-of-stock">All editions sold out</span>
                @endif
            </p>
        @endif

        <div class="size-option">
            <p>Select your size:</p>
            <button>Small</button>
            <button>Medium</button>
            <button>Large</button>
        </div>

        @php
            $hasEditionsInfo = !is_null($editionsRemaining) && !is_null($editionsTotal);
        @endphp

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


