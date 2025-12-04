@extends('layouts.app')

@section('title', 'About Us')

@push('styles')
  <link rel="stylesheet" href="{{ asset('css/contactUs.css') }}">
@endpush

@section('content')
      <section class="Groupname">
        <h1>9 MINT</h1>
        <p>All about art and creativity</p>
      </section>

    {{-- Dynamic NFT Grid: show 5 at a time, rotate through all NFTs --}}
      @php
          $imageUrls = $nfts->pluck('image_url')->values();
          $initialCount = min(5, $imageUrls->count());
      @endphp
      <section class="nft-grid">
        @for ($i = 0; $i < $initialCount; $i++)
            <img src="{{ $imageUrls[$i] }}" alt="NFT {{ $i + 1 }}" />
        @endfor
      </section>

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

      <section class="team-section">
        <h2>Meet the Team</h2>

        <div class="team-grid">
            @php
                $team = [
                    [ "name" => "Naomi", "role" => "Team mediator and Front end engineer" ],
                    [ "name" => "Arjan", "role" => "Team Leader and Back end engineer" ],
                    [ "name" => "Maliyka", "role" => "Front end engineer leader" ],
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
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const grid = document.querySelector('.nft-grid');
    if (!grid) return;

    const slots = Array.from(grid.querySelectorAll('img'));
    const allImages = @json($imageUrls);

    if (slots.length === 0 || !Array.isArray(allImages) || allImages.length === 0) {
        return;
    }

    const total = allImages.length;

    function pickUniqueIndices(count, totalCount) {
        const available = [];
        for (let i = 0; i < totalCount; i++) {
            available.push(i);
        }

        const result = [];
        const picks = Math.min(count, totalCount);
        for (let i = 0; i < picks; i++) {
            const idx = Math.floor(Math.random() * available.length);
            result.push(available[idx]);
            available.splice(idx, 1);
        }
        return result;
    }

    function applyIndices(indices) {
        indices.forEach((imgIdx, slotIdx) => {
            if (slots[slotIdx] && allImages[imgIdx]) {
                slots[slotIdx].src = allImages[imgIdx];
            }
        });
    }

    // Initial random set
    let currentIndices = pickUniqueIndices(slots.length, total);
    applyIndices(currentIndices);

    setInterval(function () {
        if (total <= slots.length) {
            // Not enough NFTs to rotate a new set
            return;
        }

        // Pick a new random set of unique indices for all slots
        currentIndices = pickUniqueIndices(slots.length, total);
        applyIndices(currentIndices); // all 5 change at once
    }, 3000); // 3 seconds
});
</script>
@endpush

