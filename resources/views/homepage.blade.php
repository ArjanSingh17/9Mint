@extends('layouts.app')

@section('title', 'Homepage')

@section('content')
    {{-- NFT Discovery Board (React-powered) --}}
    @if(isset($boardNfts) && $boardNfts->isNotEmpty())
        <section id="nft-discovery-section" aria-label="NFT Discovery Board">
            <div id="nft-discovery-board" data-nfts='@json($boardNfts)'></div>
        </section>
    @endif
@endsection

@push('scripts')
    {{-- Page JS --}}
    @vite('resources/js/nft-board/homepage-entry.jsx')
@endpush

