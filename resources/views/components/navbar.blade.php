<nav class="navbar">
  {{-- Logo --}}
  <div class="logo-container">
    <a href="/homepage" class="logo-link">
      <img src="{{ asset('images/9mint.png') }}" alt="9 Mint Logo" class="logo-image" />
      {{-- <img src="{{ asset('images/9mintname.png') }}" alt="9 Mint" class="logo-wordmark" /> --}}
    </a>
  </div>

  {{-- Links --}}
  <div class="nav-links">
    <details class="nav-dropdown">
      <summary>Browse</summary>
      <div class="nav-links__menu">
        <a href="/homepage">Store Home</a>
        <a href="/products">Products</a>
        <a href="/trending">Trending</a>
        @auth
          <a href="{{ route('favourites.index') }}">My Favourites</a>
        @endauth
        <a href="/pricing">Pricing</a>
      </div>
    </details>

    <details class="nav-dropdown">
      <summary>Community</summary>
      <div class="nav-links__menu">
        <a href="/aboutUs">About Us</a>
      </div>
    </details>

    @auth
      <details class="nav-dropdown">
        <summary>{{ auth()->user()->name }}</summary>
        <div class="nav-links__menu">
          <a href="{{ route('profile.show', ['username' => auth()->user()->name]) }}">Profile</a>
          <a href="{{ route('inventory.index') }}">Inventory</a>
          <a href="{{ route('listings.index') }}">Listings</a>
        </div>
      </details>
    @endauth
  </div>

  {{-- Center search (LIVE RESULTS + NFT + COLLECTION routing) --}}
  <form class="nav-search" data-nav-search method="GET" action="#">
    <div class="nav-search__input-wrap">
      <span class="nav-search__icon" aria-hidden="true">🔍</span>

      <input
        type="text"
        name="q"
        class="nav-search__input"
        placeholder="Search NFTs or collections..."
        autocomplete="off"
        data-nav-search-input
      >

      <button
        type="button"
        class="nav-search__clear"
        data-nav-search-clear
        aria-label="Clear search"
      >✕</button>
    </div>

    <div class="nav-search__menu" data-nav-search-menu>
      <div class="nav-search__results" data-nav-search-results></div>
    </div>
  </form>

  {{-- Cart/auth --}}
  <div class="nav-auth">
    @php
      $cartCount = 0;
      $walletIsLinked = false;
      $walletBalances = collect();
      if (auth()->check()) {
        $cartCount = \App\Models\CartItem::where('user_id', auth()->id())->sum('quantity');
        $walletIsLinked = filled(trim((string) auth()->user()->wallet_address));

        if ($walletIsLinked) {
          $currencyCatalog = app(\App\Services\Pricing\CurrencyCatalogInterface::class);
          $enabledCurrencies = $currencyCatalog->listEnabledCurrencies();
          if (empty($enabledCurrencies)) {
            $enabledCurrencies = [$currencyCatalog->defaultPayCurrency()];
          }

          $walletRows = collect();
          if (\Illuminate\Support\Facades\Schema::hasTable('wallets')) {
            $walletRows = \App\Models\Wallet::query()
              ->where('user_id', auth()->id())
              ->get()
              ->keyBy('currency');
          }

          $walletBalances = collect($enabledCurrencies)->map(function ($currency) use ($walletRows) {
            $walletRow = $walletRows->get($currency);
            return (object) [
              'currency' => $currency,
              'balance' => (float) ($walletRow->balance ?? 0),
            ];
          });
        }
      }
    @endphp
    @auth
      @if($walletIsLinked)
        <div class="wallet-switcher" data-wallet-switcher>
          <span class="wallet-label">Wallet</span>
          <select class="wallet-select" data-wallet-currency>
            @foreach($walletBalances as $balance)
              <option value="{{ $balance->currency }}" data-net="{{ (float) $balance->balance }}">
                {{ $balance->currency }}
              </option>
            @endforeach
          </select>
          <span class="wallet-balance" data-wallet-balance></span>
        </div>
      @endif
    @endauth

    @auth
      <a href="/cart">
        <button class="basket-btn">
          <span class="basket-icon">🛒</span>
          @if($cartCount > 0)
            <span class="basket-badge">{{ $cartCount }}</span>
          @endif
        </button>
      </a>
    @endauth

    @auth
      @if(Auth::user()->role === 'admin')
        <a href="{{ route('admin.dashboard') }}" class="nav-btn admin-dashboard-btn">
          Admin
        </a>
      @endif

      <form method="POST" action="{{ route('logout') }}" class="inline">
        @csrf
        <button type="submit" class="nav-btn signout">Logout</button>
      </form>

      <a href="{{ route('profile.show', ['username' => auth()->user()->name]) }}" class="nav-btn profile-icon" title="My Profile">
        @if(!empty(auth()->user()->profile_image_url))
          <img
            src="{{ asset(ltrim(auth()->user()->profile_image_url, '/')) }}"
            alt="{{ auth()->user()->name }} avatar"
            style="width: 100%; height: 100%; border-radius: 999px; object-fit: cover; display: block;"
          >
        @else
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
            <circle cx="12" cy="7" r="4" />
          </svg>
        @endif
      </a>
    @else
      <a href="{{ route('login') }}" class="nav-btn signin">Login / Register</a>
    @endauth
  </div>
</nav>

<script>
document.addEventListener('DOMContentLoaded', function () {
  const root = document.querySelector('[data-nav-search]');
  if (!root) return;

  const form = root; // the form itself
  const input = root.querySelector('[data-nav-search-input]');
  const menu = root.querySelector('[data-nav-search-menu]');
  const resultsEl = root.querySelector('[data-nav-search-results]');
  const clearBtn = root.querySelector('[data-nav-search-clear]');

  if (!input || !menu || !resultsEl || !clearBtn) return;

  let debounceTimer = null;
  let lastResults = null;

  function openMenu(show) {
    menu.classList.toggle('is-open', !!show);
  }

  function escapeHtml(str) {
    return String(str || '')
      .replace(/&/g,"&amp;")
      .replace(/</g,"&lt;")
      .replace(/>/g,"&gt;")
      .replace(/"/g,"&quot;")
      .replace(/'/g,"&#039;");
  }

  function renderResults(data, query) {
    const nfts = data?.nfts || [];
    const collections = data?.collections || [];

    if (!nfts.length && !collections.length) {
      resultsEl.innerHTML = `<div class="nav-search__empty">No results for "${escapeHtml(query)}"</div>`;
      return;
    }

    let html = '';

    if (nfts.length) {
      html += `<div class="nav-search__section">
        <div class="nav-search__section-title">NFTs</div>`;

      nfts.forEach(nft => {
        const nftUrl = nft?.nft_url || '';
        html += `
          <button type="button" class="nav-search__result" data-url="${escapeHtml(nftUrl)}">
            <div class="nav-search__result-title">${escapeHtml(nft.name)}</div>
            <div class="nav-search__result-sub">${escapeHtml(nft.collection?.name || '')}</div>
          </button>`;
      });

      html += `</div>`;
    }

    if (collections.length) {
      html += `<div class="nav-search__section">
        <div class="nav-search__section-title">Collections</div>`;

      collections.forEach(col => {
        const colUrl = col?.collection_url || '';
        html += `
          <button type="button" class="nav-search__result" data-url="${escapeHtml(colUrl)}">
            <div class="nav-search__result-title">${escapeHtml(col.name)}</div>
            <div class="nav-search__result-sub">Collection</div>
          </button>`;
      });

      html += `</div>`;
    }

    resultsEl.innerHTML = html;

    resultsEl.querySelectorAll('[data-url]').forEach(btn => {
      btn.addEventListener('click', () => {
        const url = btn.dataset.url;
        if (url) window.location.href = url;
      });
    });
  }

  async function fetchSuggestions(query) {
    const url = `/api/v1/search/suggestions?q=${encodeURIComponent(query)}`;
    const res = await fetch(url, { headers: { Accept: 'application/json' } });
    if (!res.ok) return null;
    return await res.json();
  }

  async function onInput() {
    const query = input.value.trim();

    clearBtn.classList.toggle('is-visible', !!query);

    if (!query) {
      openMenu(false);
      resultsEl.innerHTML = '';
      lastResults = null;
      return;
    }

    openMenu(true);
    resultsEl.innerHTML = `<div class="nav-search__loading">Searching...</div>`;

    try {
      const data = await fetchSuggestions(query);
      lastResults = data || { nfts: [], collections: [] };
      renderResults(lastResults, query);
    } catch (e) {
      resultsEl.innerHTML = `<div class="nav-search__empty">Search error</div>`;
      lastResults = null;
    }
  }

  input.addEventListener('input', () => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(onInput, 200);
  });

  input.addEventListener('focus', onInput);

  clearBtn.addEventListener('click', () => {
    input.value = '';
    input.focus();
    openMenu(false);
    resultsEl.innerHTML = '';
    lastResults = null;
  });

  // Press Enter: go to first NFT result, else first collection, else fallback
  form.addEventListener('submit', function (e) {
    e.preventDefault();

    const query = input.value.trim();
    if (!query) return;

    const firstNFT = lastResults?.nfts?.[0]?.nft_url;
    const firstCollection = lastResults?.collections?.[0]?.collection_url;

    if (firstNFT) {
      window.location.href = firstNFT;
      return;
    }

    if (firstCollection) {
      window.location.href = firstCollection;
      return;
    }

    window.location.href = `/products?q=${encodeURIComponent(query)}`;
  });

  document.addEventListener('click', function(e) {
    if (!root.contains(e.target)) openMenu(false);
  });
});
</script>