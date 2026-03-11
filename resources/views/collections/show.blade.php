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

    {{-- Items --}}
    @if ($nfts->isEmpty())
        <p class="no-nfts">
            No NFTs have been added to this collection yet.
        </p>
    @else
    <div class="nft-filter-wrapper">
     <div class="nft-filter-bar">
        <div class="nft-filter-group">
  <label for="filter-sort">Sort</label>
       <select id="filter-sort">
       <option value="default">Default</option>
      <option value="newest">Newest</option>
        <option value="price-asc">Price: Low to High</option>
          <option value="price-desc">Price: High to Low</option>
  <option value="rating-desc">Rating: High to Low</option>
     </select>
  </div>
     <div class="nft-filter-group">
  <label for="filter-currency">Currency</label>
  <select id="filter-currency">
     <option value="">All</option>
           </select>
          </div>
            <div class="nft-filter-group">
      <label for="filter-min-price">Min Price</label>
      <input type="number" id="filter-min-price" min="0" placeholder="0">
      </div>
      <div class="nft-filter-group">
        <label for="filter-max-price">Max Price</label>
 <input type="number" id="filter-max-price" min="0" placeholder="Any">
       </div>
     <div class="nft-filter-group nft-filter-check">
     <label><input type="checkbox" id="filter-in-stock"> In Stock Only</label>
 </div>
     <div class="nft-filter-group nft-filter-check">
                <label><input type="checkbox" id="filter-one-of-one"> 1-of-1 Only</label>
     </div>
      <button type="button" class="filter-apply-btn" onclick="applyNftFilters()">Apply</button>
  <button type="button" class="filter-reset-btn" onclick="resetNftFilters()">Reset</button>
        </div>
        <p id="filter-no-results" style="display:none; text-align:center; color:var(--text-muted); margin-top:2rem;">
            No NFTs match your filters.
        </p>
        <div class="nft-collection-grid">
            @foreach ($nfts as $nft)
                @php
                    $listing = $nft->active_listing ?? null;
                    $price = $listing?->ref_amount;
                    $currency = $listing?->ref_currency ?? 'GBP';
                    $currencySymbol = $currencySymbols[$currency] ?? null;
                    $isLiked = Auth::check() ? Auth::user()->favourites->contains($nft->id) : false;
                @endphp
                <div class="nft-collection-card"
                    data-price="{{ $price ?? '' }}"
                    data-currency="{{ $price !== null ? $currency : '' }}"
                    data-in-stock="{{ ($nft->listed_editions_count ?? 0) > 0 ? '1' : '0' }}"
                    data-one-of-one="{{ $nft->editions_total == 1 ? '1' : '0' }}"
                    data-created="{{ $nft->created_at->timestamp }}"
                    data-rating="{{ number_format($nft->reviews_avg_rating ?? 0, 2) }}"
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
                            <img src="{{ asset(ltrim($nft->thumbnail_url ?? $nft->image_url, '/')) }}" alt="{{ $nft->name }}" />
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
                                Editions listed: {{ (int) ($nft->listed_editions_count ?? 0) }} / {{ $nft->editions_total }}
      </p>
         </div>
     </a>
     </div>
     @endforeach
     </div>
    </div>{{-- /.nft-filter-wrapper --}}
    @endif
@endsection
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const grid = document.querySelector('.nft-collection-grid');
    if (!grid) return;

    // Populate currency select from actual card data
    const cards = Array.from(grid.querySelectorAll('.nft-collection-card'));
    const currencies = [...new Set(cards.map(c => c.dataset.currency).filter(Boolean))];
    const currencySelect = document.getElementById('filter-currency');
    if (currencySelect) {
        currencies.forEach(cur => {
            const opt = document.createElement('option');
            opt.value = cur;
            opt.textContent = cur;
            currencySelect.appendChild(opt);
        });
    }
});

function applyNftFilters() {
    const grid = document.querySelector('.nft-collection-grid');
    if (!grid) return;

    const cards = Array.from(grid.querySelectorAll('.nft-collection-card'));

    const sort      = document.getElementById('filter-sort').value;
    const currency  = document.getElementById('filter-currency').value;
    const minPrice  = parseFloat(document.getElementById('filter-min-price').value);
    const maxPrice  = parseFloat(document.getElementById('filter-max-price').value);
    const inStock   = document.getElementById('filter-in-stock').checked;
    const oneOfOne  = document.getElementById('filter-one-of-one').checked;

    const hasMin = !isNaN(minPrice);
    const hasMax = !isNaN(maxPrice);

    let visible = cards.filter(function (card) {
        var price   = card.dataset.price !== '' ? parseFloat(card.dataset.price) : null;
        var cardCur = card.dataset.currency;

        if (inStock  && card.dataset.inStock  !== '1') return false;
        if (oneOfOne && card.dataset.oneOfOne !== '1') return false;
        if (currency && cardCur !== currency)          return false;

        if (hasMin || hasMax) {
            if (price === null)          return false;
            if (hasMin && price < minPrice) return false;
            if (hasMax && price > maxPrice) return false;
        }

        return true;
    });

    if (sort === 'newest') {
        visible.sort((a, b) => Number(b.dataset.created) - Number(a.dataset.created));
    } else if (sort === 'price-asc') {
        visible.sort((a, b) => {
            var pa = a.dataset.price !== '' ? parseFloat(a.dataset.price) : Infinity;
            var pb = b.dataset.price !== '' ? parseFloat(b.dataset.price) : Infinity;
            return pa - pb;
        });
    } else if (sort === 'price-desc') {
        visible.sort((a, b) => {
            var pa = a.dataset.price !== '' ? parseFloat(a.dataset.price) : -Infinity;
            var pb = b.dataset.price !== '' ? parseFloat(b.dataset.price) : -Infinity;
            return pb - pa;
        });
    } else if (sort === 'rating-desc') {
        visible.sort((a, b) => parseFloat(b.dataset.rating) - parseFloat(a.dataset.rating));
    }

    cards.forEach(function (c) { c.style.display = 'none'; });
    visible.forEach(function (c) { c.style.display = ''; grid.appendChild(c); });

    var noResults = document.getElementById('filter-no-results');
    if (noResults) noResults.style.display = visible.length === 0 ? 'block' : 'none';
}

function resetNftFilters() {
    document.getElementById('filter-sort').value = 'default';
    document.getElementById('filter-currency').value = '';
    document.getElementById('filter-min-price').value = '';
    document.getElementById('filter-max-price').value = '';
    document.getElementById('filter-in-stock').checked = false;
    document.getElementById('filter-one-of-one').checked = false;
    applyNftFilters();
}
</script>
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