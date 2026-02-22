@extends('layouts.app')

@section('title', 'About Us')

@push('styles')
  <link rel="stylesheet" href="{{ asset('css/contactUs.css') }}">
@endpush

@section('content')
<div class="about-us-container">
      {{-- Hero --}}
      <section class="Groupname">
        <h1>9 MINT</h1>
        <p>All about art and creativity</p>
      </section>

    {{-- NFT grid --}}
      @php
          $imageUrls = $nfts->pluck('image_url')->values();
          $initialCount = min(5, $imageUrls->count()); // initial count
      @endphp
      <section class="nft-grid" data-images='@json($imageUrls)'>
        @for ($i = 0; $i < $initialCount; $i++)
            <img src="{{ $imageUrls[$i] }}" alt="NFT {{ $i + 1 }}" />
        @endfor
      </section>

      {{-- About --}}
      <section class="about-section">
        <h2>Who Are We?</h2>
        <p>
          9Mint is a simulated e-commerce platform designed to sell and manage
          Non-Fungible Tokens (NFTs). At 9 MINT, our mission is to foster a
          vibrant community of art enthusiasts and creators.
        </p>

        <h2>Our Journey</h2>
        <p>
          Founded in 2025, 9 MINT began as a small group of artists and tech
          enthusiasts passionate about digital art and NFTs.
        </p>

        <h2>Our Community</h2>
        <p>
          We believe art is for everyone. Our platform connects artists and
          collectors worldwide.
        </p>
      </section>

      {{-- Team --}}
      <section class="team-section">
        <h2>Meet the Team</h2>

        <div class="team-grid">
            @php
                $team = [
                    [ "name" => "Naomi", "role" => "Team mediator and Front end engineer" ],
                    [ "name" => "Arjan", "role" => "Team Leader and Back end engineer" ],
                    [ "name" => "Maliyka", "role" => "Front end engineer leader and respectful behaviourist" ],
                    [ "name" => "Kalil", "role" => "Backend developer and Proofreader" ],
                    [ "name" => "Dariusz", "role" => "Backend engineer and project" ],
                    [ "name" => "Hamza", "role" => "Team creative and frontend developer" ],
                    [ "name" => "Jahirul", "role" => "Backend Engineer and Timekeeper" ],
                    [ "name" => "Vlas", "role" => "Front/Backend Engineer and digital artist" ],
                ];
            @endphp

            @foreach($team as $member)
                <div class="team-card">
                    <img src="{{ asset('images/logo.png')}}" alt="Logo">
                    <h3>{{ $member['name'] }}</h3>
                    <p>Role: {{ $member['role'] }}</p>
                </div>
            @endforeach
        </div>
      </section>
      <!-- -- Reviews Slider -- -->
<section class="reviews-section">
    <h2 class="reviews-title">What Our Users Say</h2>

   <div id="reviews-slider" class="reviews-slider">
      <p id="reviews-loading">Loading reviews...</p>
  </div>
</section>

@endsection
@push('scripts')
    @vite([
        'resources/js/page-scripts/about-us-nft-grid-rotator.js',
        'resources/js/page-scripts/about-us-reviews-slider.js'
    ])
@endpush
