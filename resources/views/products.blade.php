@extends('layouts.app')

@section('title', 'Products')

@push('styles')
    @vite('resources/css/pages/products.css')
@endpush

@section('content')
    {{-- Collections --}}
    <section id="NFT_collections">
        <h2>Our Collections</h2>

        {{-- Empty --}}
        @if ($collections->isEmpty())
            <p class="no-collections">
                No collections have been added yet. Please check back later.
            </p>
        @else
            {{-- Cards --}}
            @foreach ($collections as $collection)
                @php
                    $imageUrls = $collection->nfts->pluck('image_url')->values();
                    $totalEditions = $collection->nfts->sum('editions_total');
                    $remainingEditions = $collection->nfts->sum('editions_remaining');
                @endphp
                <a class="collection-card" href="{{ route('collections.show', ['slug' => $collection->slug]) }}">
                    @if ($imageUrls->isNotEmpty())
                        <div class="collection-image-wrapper">
                            <img
                                src="{{ asset(ltrim($imageUrls[0], '/')) }}"
                                alt="{{ $collection->name }} Preview"
                                class="collection-preview"
                                data-images='@json($imageUrls)'
                            >
                        </div>
                    @endif

                    <div class="collection-content">
                        <h3>{{ $collection->name }}</h3>

                        @if ($collection->description)
                            <p>{{ $collection->description }}</p>
                        @endif

                        @if($totalEditions > 0)
                            <p class="collection-stock">
                                Stock: {{ $remainingEditions }} NFTs available (out of {{ $totalEditions }})
                            </p>
                        @endif

                        <p>Click to find more about each individual NFT.</p>
                    </div>
                </a>
            @endforeach
        @endif
    </section>
@endsection

@push('scripts')
    @vite('resources/js/page-scripts/products-collection-preview-rotator.js')
@endpush


