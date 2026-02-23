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
    <a href="/homepage">Homepage</a>
    <a href="/aboutUs">About Us</a>
    <a href="/products">Products</a>
    <a href="/pricing">Pricing</a>
  </div>

  {{-- Center search (UI only) --}}
  <div class="nav-search" data-nav-search>
    <div class="nav-search__input-wrap">
      <span class="nav-search__icon" aria-hidden="true">🔍</span>
      <input
        type="text"
        class="nav-search__input"
        placeholder="Search..."
        autocomplete="off"
        data-nav-search-input
      >
      <button type="button" class="nav-search__clear" data-nav-search-clear aria-label="Clear search">✕</button>
    </div>
    <div class="nav-search__menu" data-nav-search-menu>
      <button type="button" class="nav-search__option" data-search-type="nft" data-search-scope="NFTs">Search NFTs</button>
      <button type="button" class="nav-search__option" data-search-type="collection" data-search-scope="NFT collections">Search NFT collections</button>
      <button type="button" class="nav-search__option" data-search-type="user" data-search-scope="users">Search users</button>
    </div>
  </div>

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
     <button id="theme-toggle" class="nav-btn">
        <span id="theme-icon">🌙</span>
    </button>
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
        <a href="{{ route('admin.dashboard') }}" class="nav-btn" style="color: white;">
          Admin Dashboard
        </a>
      @endif

      <form method="POST" action="{{ route('logout') }}" class="inline">
        @csrf
        <button type="submit" class="nav-btn signout">Logout</button>
      </form>

      <a href="{{ route('profile.show', ['username' => auth()->user()->name]) }}" class="nav-btn profile-icon" title="My Profile">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
          stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
          <circle cx="12" cy="7" r="4" />
        </svg>
      </a>
    @else
      <a href="{{ route('login') }}" class="nav-btn signin">Login / Register</a>
    @endauth
  </div>
</nav>

<script>
  document.addEventListener('DOMContentLoaded', function () {
    const searchRoot = document.querySelector('[data-nav-search]');
    if (!searchRoot) return;

    const input = searchRoot.querySelector('[data-nav-search-input]');
    const menu = searchRoot.querySelector('[data-nav-search-menu]');
    const clearBtn = searchRoot.querySelector('[data-nav-search-clear]');
    const options = searchRoot.querySelectorAll('.nav-search__option');

    if (!input || !menu || !clearBtn) return;

    const escapeHtml = function (value) {
      return value
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
    };

    const syncMenuState = function () {
      const query = input.value.trim();
      const hasText = query.length > 0;
      menu.classList.toggle('is-open', hasText && document.activeElement === input);
      clearBtn.classList.toggle('is-visible', hasText);

      options.forEach(function (option) {
        const scope = option.dataset.searchScope || 'results';
        if (!hasText) {
          option.innerHTML = `<span class="nav-search__option-muted">Search</span> ${scope}`;
          return;
        }

        const safeQuery = escapeHtml(query);
        option.innerHTML = `<span class="nav-search__option-muted">Search</span> '<span class="nav-search__option-query">${safeQuery}</span>' <span class="nav-search__option-muted">${scope}</span>`;
      });
    };

    input.addEventListener('focus', syncMenuState);
    input.addEventListener('input', syncMenuState);

    clearBtn.addEventListener('click', function () {
      input.value = '';
      input.focus();
      syncMenuState();
    });

    options.forEach(function (option) {
      option.addEventListener('click', function () {
        menu.classList.remove('is-open');
      });
    });

    document.addEventListener('click', function (event) {
      if (!searchRoot.contains(event.target)) {
        menu.classList.remove('is-open');
      }
    });
  });
</script>