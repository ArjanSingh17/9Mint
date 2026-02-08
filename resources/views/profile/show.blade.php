@extends('layouts.app')

@section('title', $user->name . "'s Profile")

@push('styles')
<style>
    .profile-show {
        max-width: 800px;
        margin: 60px auto;
        padding: 0 20px;
        text-align: center;
    }

    .profile-show-avatar {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        background: #20065c;
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

    .profile-show-email {
        color: #888;
        margin-bottom: 30px;
    }

    .profile-show-details {
        background: #fff;
        color: #20065c;
        border-radius: 10px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.15);
        padding: 24px 28px;
        text-align: left;
    }

    .profile-show-row {
        display: flex;
        justify-content: space-between;
        padding: 12px 0;
        border-bottom: 1px solid #eee;
    }

    .profile-show-row:last-child {
        border-bottom: none;
    }

    .profile-show-label {
        font-weight: 600;
        color: #555;
    }

    .profile-show-value {
        color: #20065c;
    }

    .profile-show-nfts {
        margin-top: 30px;
        text-align: left;
    }

    .profile-show-nfts h2 {
        color: #fff;
        font-size: 20px;
        margin-bottom: 16px;
    }

    .profile-show-nft-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
        gap: 16px;
    }

    .profile-show-nft-card {
        background: #0f172a;
        border-radius: 10px;
        overflow: hidden;
        text-decoration: none;
        color: #fff;
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
    <p class="profile-show-email">{{ $user->email }}</p>

    <div class="profile-show-details">
        <div class="profile-show-row">
            <span class="profile-show-label">Username</span>
            <span class="profile-show-value">{{ $user->name }}</span>
        </div>
        <div class="profile-show-row">
            <span class="profile-show-label">Email</span>
            <span class="profile-show-value">{{ $user->email }}</span>
        </div>
        <div class="profile-show-row">
            <span class="profile-show-label">NFTs Owned</span>
            <span class="profile-show-value">{{ \App\Models\NftToken::where('owner_user_id', $user->id)->count() }}</span>
        </div>
        <div class="profile-show-row">
            <span class="profile-show-label">Member Since</span>
            <span class="profile-show-value">{{ $user->created_at->format('F j, Y') }}</span>
        </div>
    </div>

    @php
        $ownedTokens = \App\Models\NftToken::with('nft')
            ->where('owner_user_id', $user->id)
            ->get();
    @endphp

    <div class="profile-show-nfts">
        <h2>My NFTs</h2>
        @if($ownedTokens->isEmpty())
            <p class="profile-show-empty">No NFTs owned yet.</p>
        @else
            <div class="profile-show-nft-grid">
                @foreach($ownedTokens as $token)
                    <a href="{{ route('nfts.show', $token->nft->slug) }}" class="profile-show-nft-card">
                        <img src="{{ $token->nft->image_url }}" alt="{{ $token->nft->name }}" loading="lazy">
                        <span>{{ $token->nft->name }}</span>
                    </a>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection
