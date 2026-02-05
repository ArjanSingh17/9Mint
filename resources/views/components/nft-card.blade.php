@props([
    'image',
    'title',
    'description',
    'slug',
    // Editions information for this NFT (optional; if null, editions are not shown)
    'editionsTotal' => null,
    'editionsRemaining' => null,
    'id',
    'isLiked' => false
])

<div class="NFT-Card" style="position: relative;">
    <button
        onclick="toggleLike({{ $id }}, this)"
        class="like-btn"
        style="position: absolute; top: 10px; right: 10px; z-index: 10; background: rgba(0,0,0,0.6); border: none; border-radius: 50%; width: 35px; height: 35px; cursor: pointer; color: {{ $isLiked ? '#ff4d4d' : 'white' }}; font-size: 20px; display: flex; align-items: center; justify-content: center; transition: transform 0.2s;"
        onmouseover="this.style.transform='scale(1.1)'"
        onmouseout="this.style.transform='scale(1)'"
    >
        {{ $isLiked ? '♥' : '♡' }}
    </button>

   
    {{-- Image --}}
    <img src="{{ asset($image) }}" alt="{{ $title }}" class="nft-image" />

    {{-- Details --}}
    <div class="nft-info">
        <h2>{{ $title }}</h2>
        <p class="nf-description">
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
            <button>Small</button>
            <button>Medium</button>
            <button>Large</button>
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


