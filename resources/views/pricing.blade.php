@extends('layouts.app')

@section('title', 'Pricing')

@push('styles')
    @vite('resources/css/pages/pricing.css')
@endpush

@section('content')
    {{-- Intro --}}
    <section class="Pricing-info">
        <p>
            Here at 9Mint, we value the hard work and creativity put in by our NFT designers.
        </p>
        <p>
            We aim to keep prices affordable while honouring the designer’s effort.
        </p>
    </section>

    <main>
        {{-- Price ranges --}}
        <section id="Pricing_sizes" class="Org">
            <h3>In general:</h3>

            <div class="pricing-grid">
                <div class="pricing-item pricing-medium">
                    <div class="pricing-content">
                        <div class="diagram">
                            <img src="{{ asset('images/nfts/NFT-medium.png') }}" alt="NFT pricing" />
                        </div>

                        <div class="pricing-text">
                            <h4>Single Listing Price</h4>
                            <p>Each listing has one reference price, and you can view or pay in your chosen currency.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>
@endsection

