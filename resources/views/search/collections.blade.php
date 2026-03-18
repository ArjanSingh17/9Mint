@extends('layouts.app')

@section('title', 'All Collections')

@push('styles')
    @vite('resources/css/pages/products.css')
    @vite('resources/css/pages/collections-legacy.css')
@endpush

@section('content')
    <section id="NFT_collections">
        <h2>All Collections</h2>

        <div class="nft-filter-wrapper">
            <form method="GET" action="{{ route('search.collections') }}" class="nft-filter-bar">
                <div class="nft-filter-group" style="min-width: 220px;">
                    <label for="filter-q">Name</label>
                    <input id="filter-q" type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Search by collection name">
                </div>

                <div class="nft-filter-group">
                    <label for="filter-sort">Sort</label>
                    <select id="filter-sort" name="sort">
                        <option value="newest" @selected(($filters['sort'] ?? '') === 'newest')>Newest</option>
                        <option value="name-asc" @selected(($filters['sort'] ?? '') === 'name-asc')>Name A-Z</option>
                        <option value="name-desc" @selected(($filters['sort'] ?? '') === 'name-desc')>Name Z-A</option>
                        <option value="listed-desc" @selected(($filters['sort'] ?? '') === 'listed-desc')>Most Listed Editions</option>
                    </select>
                </div>

                <div class="nft-filter-break"></div>

                <div class="nft-filter-group nft-filter-check">
                    <label><input type="checkbox" name="in_stock" value="1" @checked(($filters['in_stock'] ?? false))> In Stock Only</label>
                </div>

                <button type="submit" class="filter-apply-btn">Apply</button>
                <a href="{{ route('search.collections') }}" class="filter-reset-btn" style="text-decoration:none;">Reset</a>
            </form>
        </div>

        @if ($collections->isEmpty())
            <p class="no-collections">No collections match your search filters.</p>
        @else
            @foreach ($collections as $collection)
                @php
                    $imageUrls = $collection->nfts
                        ->map(fn ($nft) => $nft->thumbnail_url ?? $nft->image_url)
                        ->values();
                    $coverImageUrl = $imageUrls[0] ?? $collection->cover_image_url;
                    $totalEditions = (int) ($collection->total_editions_count ?? $collection->nfts->sum('editions_total'));
                    $listedEditions = (int) ($collection->listed_editions_count ?? 0);
                @endphp
                <a class="collection-card" href="{{ route('collections.show', ['slug' => $collection->slug]) }}">
                    @if ($coverImageUrl || $imageUrls->isNotEmpty())
                        <div class="collection-image-wrapper">
                            <img
                                src="{{ asset(ltrim($coverImageUrl ?: $imageUrls[0], '/')) }}"
                                alt="{{ $collection->name }} Preview"
                                class="collection-preview"
                                @if (!$coverImageUrl)
                                    data-images='@json($imageUrls)'
                                @endif
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
                                Stock: {{ $listedEditions }} NFTs listed (out of {{ $totalEditions }})
                            </p>
                        @endif
                        <p>Click to view collection details.</p>
                    </div>
                </a>
            @endforeach
        @endif
    </section>
@endsection

@push('scripts')
    @vite('resources/js/page-scripts/products-collection-preview-rotator.js')
@endpush
