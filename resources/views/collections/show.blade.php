@extends('layouts.app')

@section('title', $collection->name)

@push('styles')
    {{-- Per-collection styles if needed --}}
    @if ($collection->slug === 'glossy-collection')
        <link rel="stylesheet" href="{{ asset('css/Glossy-collection.css') }}">
    @elseif ($collection->slug === 'superhero-collection')
        <link rel="stylesheet" href="{{ asset('css/Superhero.css?v=' . time()) }}">
    @endif
@endpush

@push('scripts')
    @vite('resources/js/page-scripts/collections-size-selection.js')
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
        {{-- Cards --}}
        @foreach ($nfts as $nft)
            <x-nft-card
                :image="$nft->image_url"
                :title="$nft->name"
                :description="$nft->description"
                :slug="$nft->slug"
                :editions-total="$nft->editions_total"
                :editions-remaining="$nft->editions_remaining"
                :id="$nft->id"                                      :isLiked="Auth::user() ? Auth::user()->favourites->contains($nft->id) : false"  />
            />
        @endforeach
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