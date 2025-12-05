@extends('layouts.app')

@section('title', 'Products')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/products.css') }}">
@endpush

@section('content')
    <section id="NFT_collections">
        <h2>Our Collections</h2>

        @if ($collections->isEmpty())
            <p class="no-collections">
                No collections have been added yet. Please check back later.
            </p>
        @else
            @foreach ($collections as $collection)
                @php
                    $imageUrls = $collection->nfts->pluck('image_url')->values();
                    $totalEditions = $collection->nfts->sum('editions_total');
                    $remainingEditions = $collection->nfts->sum('editions_remaining');
                @endphp
                <div class="collection-card">
                    <h3>
                        <a href="{{ route('collections.show', ['slug' => $collection->slug]) }}">
                            {{ $collection->name }}
                        </a>
                    </h3>

                    @if ($collection->description)
                        <p>{{ $collection->description }}</p>
                    @endif

                    @if($totalEditions > 0)
                        <p class="collection-stock">
                            Stock: {{ $remainingEditions }} NFTs available (out of {{ $totalEditions }})
                        </p>
                    @endif

                    <p>Click to find more about each individual NFT.</p>

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
                </div>
            @endforeach
        @endif
    </section>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.collection-preview[data-images]').forEach(function (img) {
        let images;
        try {
            images = JSON.parse(img.dataset.images || '[]');
        } catch (e) {
            images = [];
        }
        if (!Array.isArray(images) || images.length <= 1) return;

        let index = 0;
        setInterval(function () {
            index = (index + 1) % images.length;
            img.src = images[index];
        }, 3000); // 3 seconds
    });
});
</script>
@endpush


