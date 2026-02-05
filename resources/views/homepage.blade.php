@extends('layouts.app')

@section('title', 'Homepage')

@section('content')
    {{-- NFT Discovery Board (React-powered) --}}
    <section id="nft-discovery-section" aria-label="NFT Discovery Board">
        <div
            id="nft-discovery-board"
            data-nfts='@json($boardNfts ?? [])'
            data-currencies='@json(config("pricing.enabled_currencies", ["GBP"]))'
            data-csrf="{{ csrf_token() }}"
            data-auth="{{ auth()->check() ? '1' : '0' }}"
            data-login-url="{{ route('login', ['redirect' => request()->fullUrl()]) }}"
        ></div>
    </section>
@endsection

@push('scripts')
    {{-- Page JS --}}
    @vite('resources/js/nft-board/homepage-entry.jsx')
@endpush

