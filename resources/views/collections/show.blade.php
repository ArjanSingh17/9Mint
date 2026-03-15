@extends('layouts.app')

@section('title', $collection->name)

@push('styles')
    @vite('resources/css/pages/collections-legacy.css')
@endpush

@push('scripts')
    @vite('resources/js/page-scripts/quote-refresh.js')
@endpush

@section('content')
    {{-- Title --}}
    <h1 class="collection-title">{{ $collection->name }}</h1>

    {{-- ✅ FILTER BAR (NO URL, BOXES) --}}
    @if (!$nfts->isEmpty())
        <div class="collection-filters" style="max-width: 1100px; margin: 18px auto 10px; padding: 0 16px;">
            <div style="display:flex; flex-wrap:wrap; gap:12px; align-items:center;">
                {{-- Search --}}
                <div style="flex: 1; min-width: 220px;">
                    <input
                        id="collectionFilterSearch"
                        type="text"
                        placeholder="Search in this collection..."
                        style="
                            width:100%;
                            border-radius:999px;
                            border:1px solid rgba(255,255,255,.12);
                            background: rgba(255,255,255,.06);
                            padding: 10px 14px;
                            color:#fff;
                            outline:none;
                        "
                    />
                </div>

                {{-- Sort --}}
                <div>
                    <select
                        id="collectionFilterSort"
                        style="
                            border-radius:999px;
                            border:1px solid rgba(255,255,255,.12);
                            background: rgba(255,255,255,.06);
                            padding: 10px 12px;
                            color:#fff;
                            outline:none;
                        "
                    >
                        <option value="relevance">Sort: Relevance</option>
                        <option value="price_asc">Sort: Price (Low → High)</option>
                        <option value="price_desc">Sort: Price (High → Low)</option>
                        <option value="name_asc">Sort: Name (A → Z)</option>
                        <option value="name_desc">Sort: Name (Z → A)</option>
                    </select>
                </div>

                {{-- Price --}}
                <div>
                    <select
                        id="collectionFilterPrice"
                        style="
                            border-radius:999px;
                            border:1px solid rgba(255,255,255,.12);
                            background: rgba(255,255,255,.06);
                            padding: 10px 12px;
                            color:#fff;
                            outline:none;
                        "
                    >
                        <option value="">Price: All</option>
                        <option value="lt_50">Price: &lt; 50</option>
                        <option value="50_100">Price: 50 – 100</option>
                        <option value="gt_100">Price: &gt; 100</option>
                    </select>
                </div>

                {{-- In stock --}}
                <label style="display:flex; align-items:center; gap:8px; color:#fff; opacity:.9;">
                    <input id="collectionFilterInStock" type="checkbox" />
                    In stock only
                </label>

                {{-- Clear --}}
                <button
                    id="collectionFilterClear"
                    type="button"
                    style="
                        border-radius:999px;
                        border:1px solid rgba(255,255,255,.12);
                        background: rgba(255,255,255,.06);
                        padding: 10px 14px;
                        color:#fff;
                        cursor:pointer;
                    "
                >
                    Clear
                </button>
            </div>
        </div>
    @endif

    {{-- Items --}}
    @if ($nfts->isEmpty())
        <p class="no-nfts">
            No NFTs have been added to this collection yet.
        </p>
    @else
        <div class="nft-collection-grid" id="collectionGrid">
            @foreach ($nfts as $nft)
                @php
                    $listing = $nft->active_listing ?? null;
                    $price = $listing?->ref_amount;
                    $currency = $listing?->ref_currency ?? 'GBP';
                    $currencySymbol = $currencySymbols[$currency] ?? null;
                    $isLiked = Auth::check() ? Auth::user()->favourites->contains($nft->id) : false;

                    // ✅ numeric helpers for filtering (GBP ref_amount)
                    $priceNumber = $price !== null ? (float) $price : 0.0;
                    $stockNumber = (int) ($nft->editions_remaining ?? 0);
                @endphp

                <div
                    class="nft-collection-card collection-item"
                    data-name="{{ strtolower($nft->name) }}"
                    data-price="{{ $priceNumber }}"
                    data-stock="{{ $stockNumber }}"
                    data-original-index="{{ $loop->index }}"
                >
                    <button
                        type="button"
                        class="nft-collection-heart"
                        onclick="toggleLike({{ $nft->id }}, this)"
                        aria-label="Toggle favourite"
                        data-liked="{{ $isLiked ? '1' : '0' }}"
                    >
                        {{ $isLiked ? '♥' : '♡' }}
                    </button>

                    <a href="{{ route('nfts.show', ['slug' => $nft->slug]) }}">
                        <div class="nft-collection-thumb">
                            <img src="{{ asset(ltrim($nft->image_url, '/')) }}" alt="{{ $nft->name }}" />
                        </div>

                        <div class="nft-collection-meta">
                            <h3>{{ $nft->name }}</h3>

                            <p
                                class="nft-collection-price"
                                data-quote-listing="{{ $listing?->id }}"
                                data-currency="{{ $currency }}"
                            >
                                {{ $price !== null
                                    ? ($currencySymbol ? $currencySymbol . number_format($price, 2) : number_format($price, 2) . ' ' . $currency)
                                    : 'Unavailable' }}
                            </p>

                            <p class="nft-collection-stock">
                                Editions remaining: {{ $nft->editions_remaining }} / {{ $nft->editions_total }}
                            </p>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>

        <div id="collectionNoResults" style="display:none; color:#fff; text-align:center; margin-top: 18px;">
            No results found.
        </div>
    @endif
@endsection

@push('scripts')
<script>
    // ✅ LIKE BUTTON (your original)
    async function toggleLike(nftId, btn) {
        @guest
            window.location.href = "{{ route('login') }}";
            return;
        @endguest

        const isLiked = btn.innerText.trim() === '♥';
        btn.innerText = isLiked ? '♡' : '♥';
        btn.style.color = isLiked ? 'white' : '#ff4d4d';

        try {
           const response = await fetch(`/nfts/${nftId}/toggle-like`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            });

            if (!response.ok) throw new Error('Server rejected the like');

        } catch (error) {
            console.error("Save failed:", error);
            btn.innerText = isLiked ? '♥' : '♡';
            btn.style.color = isLiked ? '#ff4d4d' : 'white';
            alert("Could not save like. Are you still logged in?");
        }
    }

    // ✅ FILTERS (NO URL, box UI)
    document.addEventListener("DOMContentLoaded", () => {
        const grid = document.getElementById("collectionGrid");
        if (!grid) return;

        const searchEl = document.getElementById("collectionFilterSearch");
        const sortEl = document.getElementById("collectionFilterSort");
        const priceEl = document.getElementById("collectionFilterPrice");
        const inStockEl = document.getElementById("collectionFilterInStock");
        const clearEl = document.getElementById("collectionFilterClear");
        const noResultsEl = document.getElementById("collectionNoResults");

        const items = Array.from(grid.querySelectorAll(".collection-item"));

        function matchesPriceRange(price, range) {
            if (!range) return true;
            if (range === "lt_50") return price < 50;
            if (range === "50_100") return price >= 50 && price <= 100;
            if (range === "gt_100") return price > 100;
            return true;
        }

        function applyFilters() {
            const q = (searchEl?.value || "").trim().toLowerCase();
            const sort = sortEl?.value || "relevance";
            const priceRange = priceEl?.value || "";
            const inStockOnly = !!inStockEl?.checked;

            // filter
            let filtered = items.filter((el) => {
                const name = (el.dataset.name || "");
                const price = Number(el.dataset.price || 0);
                const stock = Number(el.dataset.stock || 0);

                if (q && !name.includes(q)) return false;
                if (!matchesPriceRange(price, priceRange)) return false;
                if (inStockOnly && stock <= 0) return false;

                return true;
            });

            // sort
            const getPrice = (el) => Number(el.dataset.price || 0);
            const getName = (el) => String(el.dataset.name || "");
            const getIndex = (el) => Number(el.dataset.originalIndex || 0);

            switch (sort) {
                case "price_asc":
                    filtered.sort((a,b) => getPrice(a) - getPrice(b));
                    break;
                case "price_desc":
                    filtered.sort((a,b) => getPrice(b) - getPrice(a));
                    break;
                case "name_asc":
                    filtered.sort((a,b) => getName(a).localeCompare(getName(b)));
                    break;
                case "name_desc":
                    filtered.sort((a,b) => getName(b).localeCompare(getName(a)));
                    break;
                default:
                    // relevance = original order
                    filtered.sort((a,b) => getIndex(a) - getIndex(b));
            }

            // render: hide all then append filtered in order
            items.forEach(el => el.style.display = "none");
            filtered.forEach(el => {
                el.style.display = "";
                grid.appendChild(el); // moves DOM node to correct order
            });

            if (noResultsEl) noResultsEl.style.display = filtered.length ? "none" : "block";
        }

        // events
        [searchEl, sortEl, priceEl, inStockEl].forEach((el) => {
            if (!el) return;
            el.addEventListener("input", applyFilters);
            el.addEventListener("change", applyFilters);
        });

        if (clearEl) {
            clearEl.addEventListener("click", () => {
                if (searchEl) searchEl.value = "";
                if (sortEl) sortEl.value = "relevance";
                if (priceEl) priceEl.value = "";
                if (inStockEl) inStockEl.checked = false;
                applyFilters();
            });
        }

        // initial
        applyFilters();
    });
</script>
@endpush