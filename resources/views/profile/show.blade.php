@extends('layouts.app')

@section('title', $user->name . "'s Profile")

@push('styles')
<style>
    .profile-show {
        max-width: 800px;
        margin: 60px auto;
        padding: 0 20px 80px;
        text-align: center;
    }

    .profile-show-avatar {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        background: var(--link-hover);
        color: #fff;
        font-size: 40px;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 20px;
    }

    .profile-show-name {
        font-size: 26px;
        font-weight: 700;
        margin-bottom: 4px;
    }

    .profile-show-account-settings {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 24px;
        padding: 10px 18px;
        border-radius: 8px;
        background: var(--link-hover);
        color: #fff;
        text-decoration: none;
        font-weight: 600;
    }

    .profile-show-account-settings:hover {
        background: color-mix(in srgb, var(--link-hover) 85%, #000 15%);
    }

    .profile-show-contact-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 24px;
        padding: 10px 18px;
        border-radius: 8px;
        background: var(--link-hover);
        color: #fff;
        text-decoration: none;
        font-weight: 600;
        border: none;
        cursor: pointer;
    }

    .profile-show-contact-btn:hover {
        background: color-mix(in srgb, var(--link-hover) 85%, #000 15%);
    }

    .profile-show-details {
        background: var(--surface-panel);
        color: var(--text-main);
        border: 1px solid var(--border-soft);
        border-radius: 10px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.15);
        padding: 24px 28px;
        text-align: left;
    }

    .profile-show-row {
        display: flex;
        justify-content: space-between;
        padding: 12px 0;
        border-bottom: 1px solid var(--border-soft);
    }

    .profile-show-row:last-child {
        border-bottom: none;
    }

    .profile-show-label {
        font-weight: 600;
        color: var(--text-main);
    }

    .profile-show-value {
        color: var(--link-hover);
    }

    .profile-show-nfts {
        margin-top: 30px;
        text-align: left;
    }

    .profile-show-nfts h2 {
        color: #000;
        font-size: 20px;
        margin-bottom: 16px;
    }

    .profile-show-nft-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
        gap: 16px;
        position: relative;
    }

    .profile-show-nft-card {
        background: var(--surface-panel);
        border: 1px solid var(--border-soft);
        border-radius: 10px;
        overflow: hidden;
        text-decoration: none;
        color: var(--text-main);
        transition: transform 0.2s ease;
    }

    .profile-show-nft-card:hover {
        transform: translateY(-4px);
    }

    .profile-show-nft-card img {
        width: 100%;
        aspect-ratio: 1 / 1.2;
        object-fit: cover;
        display: block;
    }

    .profile-show-nft-card span {
        display: block;
        padding: 10px 12px;
        font-size: 14px;
        font-weight: 600;
    }

    .profile-show-nft-token-id {
        padding: 0 12px 12px;
        margin-top: -18px;
        font-size: 12px;
        font-weight: 500;
        color: var(--subtext-color);
    }

    .profile-show-nft-card--faded {
        pointer-events: none;
        user-select: none;
        -webkit-mask-image: linear-gradient(to bottom, rgba(0, 0, 0, 1) 8%, rgba(0, 0, 0, 0) 42%);
        mask-image: linear-gradient(to bottom, rgba(0, 0, 0, 1) 8%, rgba(0, 0, 0, 0) 42%);
    }

    .profile-show-nft-card--faded:hover {
        transform: none;
    }

    .profile-show-inventory-cta {
        margin-top: -128px;
        text-align: center;
        position: relative;
        z-index: 2;
    }

    .profile-show-inventory-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 10px 18px;
        border-radius: 8px;
        background: var(--link-hover);
        color: #fff;
        text-decoration: none;
        font-weight: 600;
    }

    .profile-show-inventory-btn:hover {
        background: color-mix(in srgb, var(--link-hover) 85%, #000 15%);
    }

    .profile-show-empty {
        color: #888;
        font-size: 14px;
    }
</style>
@endpush

@section('content')
<div class="profile-show">
    <div class="profile-show-avatar">
        {{ strtoupper(substr($user->name, 0, 1)) }}
    </div>

    <h1 class="profile-show-name">{{ $user->name }}</h1>
    @if (($isOwner ?? false) === true)
        <a href="{{ route('profile.settings') }}" class="profile-show-account-settings">Account Settings</a>
    @elseif(auth()->check())
        <form method="POST" action="{{ route('conversations.start-user', $user->id) }}">
            @csrf
            <button type="submit" class="profile-show-contact-btn">Contact me</button>
        </form>
    @else
        <a href="{{ route('login', ['redirect' => request()->fullUrl()]) }}" class="profile-show-contact-btn">Contact me</a>
    @endif

    <div class="profile-show-details">
        <div class="profile-show-row">
            <span class="profile-show-label">Username</span>
            <span class="profile-show-value">{{ $user->name }}</span>
        </div>
        <div class="profile-show-row">
            <span class="profile-show-label">NFTs Owned</span>
            <span class="profile-show-value">
                @if (($isOwner ?? false) || ($user->nfts_public ?? true))
                    {{ \App\Models\NftToken::where('owner_user_id', $user->id)->count() }}
                @else
                    Private
                @endif
            </span>
        </div>
        <div class="profile-show-row">
            <span class="profile-show-label">Member Since</span>
            <span class="profile-show-value">{{ $user->created_at->format('F j, Y') }}</span>
        </div>
    </div>

    @if (($isOwner ?? false) || ($user->nfts_public ?? false))
        @php
            $isOwnerView = (($isOwner ?? false) === true);
            $ownedTokens = \App\Models\NftToken::with('nft')
                ->where('owner_user_id', $user->id)
                ->get();
            $showInventoryCta = $isOwnerView && $ownedTokens->count() > 8;
            $visibleTokens = $showInventoryCta ? $ownedTokens->take(12) : $ownedTokens;
        @endphp

        <div class="profile-show-nfts">
            <h2>{{ ($isOwner ?? false) ? 'Inventory' : $user->name . "'s NFTs" }}</h2>
            @if($ownedTokens->isEmpty())
                <p class="profile-show-empty">No NFTs owned yet.</p>
            @else
                <div class="profile-show-nft-grid">
                    @foreach($visibleTokens as $index => $token)
                        @if ($showInventoryCta && $index >= 8)
                            <div class="profile-show-nft-card profile-show-nft-card--faded" aria-hidden="true">
                                <img src="{{ $token->nft->image_url }}" alt="{{ $token->nft->name }}" loading="lazy">
                                <span>{{ $token->nft->name }}</span>
                                <span class="profile-show-nft-token-id">Token #{{ $token->serial_number }}</span>
                            </div>
                        @else
                            <a href="{{ route('nfts.show', $token->nft->slug) }}" class="profile-show-nft-card">
                                <img src="{{ $token->nft->image_url }}" alt="{{ $token->nft->name }}" loading="lazy">
                                <span>{{ $token->nft->name }}</span>
                                <span class="profile-show-nft-token-id">Token #{{ $token->serial_number }}</span>
                            </a>
                        @endif
                    @endforeach
                </div>
                @if ($showInventoryCta)
                    <div class="profile-show-inventory-cta">
                        <a href="{{ route('inventory.show', ['username' => $user->name]) }}" class="profile-show-inventory-btn">View inventory</a>
                    </div>
                @endif
            @endif
        </div>
    @else
        <div class="profile-show-nfts">
            <h2>{{ $user->name . "'s NFTs" }}</h2>
            <p class="profile-show-empty">This user's NFT collection is private.</p>
        </div>
    @endif
</div>
@endsection
