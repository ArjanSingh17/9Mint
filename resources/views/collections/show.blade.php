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
            />
        @endforeach
    @endif
@endsection


