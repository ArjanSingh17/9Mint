@extends('layouts.app')

@section('title', 'My Account')

@push('styles')
  <link rel="stylesheet" href="{{ asset('css/App.css') }}">
@endpush

@section('content')
  {{-- Dashboard --}}
  <div class="profile-page">
    <h1 class="profile-title">My Account Dashboard</h1>

    {{--  Display Status Feedback --}}
    @if (session('status'))
      <div class="profile-status">
        {{ session('status') }}
      </div>
    @endif

    {{-- Sections --}}
    <div class="profile-sections">
      {{-- Account Details Form --}}
      <div class="profile-card">
        @include('partials.update-details-form')
      </div>

      {{-- Security and Password Form --}}
      <div class="profile-card">
        @include('partials.update-password-form')
      </div>

      {{-- Activity Links --}}
      <div class="profile-card profile-activity">
        @include('partials.activity-links')
      </div>

      {{-- Owned NFTs --}}
      @php
        $ownedTokens = \App\Models\NftToken::with('nft')
            ->where('owner_user_id', $user->id)
            ->get();
      @endphp

      <div class="profile-nfts-section">
        <h2>My NFTs ({{ $ownedTokens->count() }})</h2>
        @if($ownedTokens->isEmpty())
          <p class="profile-nfts-empty">No NFTs owned yet.</p>
        @else
          <div class="profile-nfts-grid">
            @foreach($ownedTokens as $token)
              <a href="{{ route('nfts.show', $token->nft->slug) }}" class="profile-nft-card">
                <img src="{{ $token->nft->image_url }}" alt="{{ $token->nft->name }}" loading="lazy">
                <span>{{ $token->nft->name }}</span>
              </a>
            @endforeach
          </div>
        @endif
      </div>
    </div>
  </div>
@endsection