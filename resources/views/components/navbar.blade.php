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
      <button type="button" class="nav-search__option" data-search-type="nft" data-search-scope="NFTs"></button>
      <button type="button" class="nav-search__option" data-search-type="collection" data-search-scope="NFT collections"></button>
      <button type="button" class="nav-search__option" data-search-type="user" data-search-scope="users"></button>
    </div>
  </form>



  {{-- Cart/auth --}}
  <div class="nav-auth">
    @php
      $cartCount = 0;
      $walletIsLinked = false;
      $walletBalances = collect();
      $unreadNotificationsCount = 0;
      $unreadBellNotifications = collect();
      $friendRequestUnreadCount = 0;
      $singleFriendRequesterName = null;
      $adminRefundQueueCount = 0;
      $adminCollectionApprovalsCount = 0;
      if (auth()->check()) {
        $cartCount = \App\Models\CartItem::where('user_id', auth()->id())->sum('quantity');
        $walletIsLinked = filled(trim((string) auth()->user()->wallet_address));
        $unreadNotificationsCount = \App\Models\UserNotification::where('user_id', auth()->id())->whereNull('read_at')->count();
        $unreadBellNotifications = \App\Models\UserNotification::where('user_id', auth()->id())
          ->whereNull('read_at')
          ->latest('created_at')
          ->limit(8)
          ->get();
        $friendRequestUnreadCount = \App\Models\UserNotification::where('user_id', auth()->id())
          ->whereNull('read_at')
          ->where('type', 'friend_request_received')
          ->count();

        if ($friendRequestUnreadCount === 1) {
          $singleFriendRequest = \App\Models\UserNotification::where('user_id', auth()->id())
            ->whereNull('read_at')
            ->where('type', 'friend_request_received')
            ->latest('created_at')
            ->first();

          $singleFriendRequesterName = trim((string) data_get($singleFriendRequest?->data, 'requester_name', ''));
          if ($singleFriendRequesterName === '') {
            $singleFriendRequesterName = 'Someone';
          }
        }

        if (Auth::user()->role === 'admin') {
          $adminRefundQueueCount = \App\Models\OrderItem::where('lifecycle_status', \App\Models\OrderItem::LIFECYCLE_REFUND_REQUESTED)->count();
          $adminCollectionApprovalsCount = \App\Models\Collection::where('approval_status', \App\Models\Collection::APPROVAL_PENDING)->count();
        }

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
      <a href="/cart" class="nav-auth__basket-link">
        <button class="basket-btn">
          <span class="basket-icon">🛒</span>
          @if($cartCount > 0)
            <span class="basket-badge">{{ $cartCount }}</span>
          @endif
        </button>
      </a>
    @endauth

    @auth
      <details class="nav-dropdown nav-dropdown--notifications">
        <summary>
          🔔
          @if($unreadNotificationsCount > 0)
            <span class="basket-badge">{{ $unreadNotificationsCount }}</span>
          @endif
        </summary>
        <div class="nav-links__menu nav-links__menu--notifications">
          @php
            $remainingUnreadNotifications = $unreadBellNotifications->reject(function ($notification) {
              return ($notification->type ?? '') === 'friend_request_received';
            })->values();
            $hasDropdownItems = false;
          @endphp

          @if(Auth::user()->role === 'admin' && $adminRefundQueueCount > 0)
            @php $hasDropdownItems = true; @endphp
            <a href="{{ route('admin.refunds') }}">
              @if($adminRefundQueueCount === 1)
                1 refund request is waiting for review
              @else
                {{ $adminRefundQueueCount }} refund requests
              @endif
            </a>
          @endif

          @if(Auth::user()->role === 'admin' && $adminCollectionApprovalsCount > 0)
            @php $hasDropdownItems = true; @endphp
            <a href="{{ route('admin.approvals.index') }}">
              @if($adminCollectionApprovalsCount === 1)
                1 collection is waiting for approval
              @else
                {{ $adminCollectionApprovalsCount }} collection approvals
              @endif
            </a>
          @endif

          @if($friendRequestUnreadCount > 0)
            @php $hasDropdownItems = true; @endphp
            <a href="{{ route('notifications.index') }}">
              @if($friendRequestUnreadCount === 1)
                <strong>{{ $singleFriendRequesterName }} wants to be your friend</strong>
              @else
                <strong>{{ $friendRequestUnreadCount }} new friend requests</strong>
              @endif
            </a>
          @endif

          @foreach($remainingUnreadNotifications as $notification)
            @php $hasDropdownItems = true; @endphp
            <a href="{{ route('notifications.index') }}">
              <strong>{{ $notification->title }}</strong><br>
              <small>{{ optional($notification->created_at)->format('Y-m-d H:i') }}</small>
            </a>
          @endforeach

          @if(!$hasDropdownItems)
            <span class="orders-meta nav-notification-empty">No notifications yet.</span>
          @endif

          <a href="{{ route('notifications.index') }}">View all notifications</a>
        </div>
      </details>
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

      {{-- <a href="{{ route('profile.show', ['username' => auth()->user()->name]) }}" class="nav-btn profile-icon" title="My Profile">
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
      </a> --}}
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
  const clearBtn = root.querySelector('[data-nav-search-clear]');
  const typeButtons = Array.from(root.querySelectorAll('[data-search-type]'));

  if (!input || !menu || !clearBtn) return;

  let selectedSearchType = 'nft';

  function openMenu(show) {
    menu.classList.toggle('is-open', !!show);
  }

  function setSearchType(nextType) {
    selectedSearchType = nextType;
    typeButtons.forEach((btn) => {
      const active = btn.dataset.searchType === nextType;
      btn.classList.toggle('is-active', active);
      btn.setAttribute('aria-pressed', active ? 'true' : 'false');
    });
  }

  function updateIntentLabels() {
    const query = input.value.trim();
    const escapedQuery = escapeHtml(query);

    typeButtons.forEach((btn) => {
      const type = btn.dataset.searchType || 'nft';
      if (!query) {
        if (type === 'collection') btn.textContent = 'Search collections';
        else if (type === 'user') btn.textContent = 'Search users';
        else btn.textContent = 'Search NFTs';
        return;
      }

      if (type === 'collection') {
        btn.innerHTML = `Search '<span class="nav-search__option-query">${escapedQuery}</span>' Collection`;
      } else if (type === 'user') {
        btn.innerHTML = `Search '<span class="nav-search__option-query">${escapedQuery}</span>' User`;
      } else {
        btn.innerHTML = `Search '<span class="nav-search__option-query">${escapedQuery}</span>' NFT`;
      }
    });
  }

  function routeByIntent(query, explicitType) {
    const type = explicitType || selectedSearchType;
    if (!query) return;

    if (type === 'collection') {
      window.location.href = `/search/collections?q=${encodeURIComponent(query)}`;
      return;
    }

    if (type === 'user') {
      window.location.href = `/users?q=${encodeURIComponent(query)}`;
      return;
    }

    window.location.href = `/search/nfts?q=${encodeURIComponent(query)}`;
  }

  function escapeHtml(str) {
    return String(str || '')
      .replace(/&/g,"&amp;")
      .replace(/</g,"&lt;")
      .replace(/>/g,"&gt;")
      .replace(/"/g,"&quot;")
      .replace(/'/g,"&#039;");
  }

  function onInput() {
    const query = input.value.trim();
    updateIntentLabels();

    clearBtn.classList.toggle('is-visible', !!query);

    if (!query) {
      openMenu(false);
      return;
    }
    openMenu(true);
  }

  input.addEventListener('input', () => {
    onInput();
  });

  input.addEventListener('focus', onInput);

  clearBtn.addEventListener('click', () => {
    input.value = '';
    input.focus();
    openMenu(false);
    updateIntentLabels();
  });

  setSearchType('nft');
  updateIntentLabels();
  typeButtons.forEach((btn) => {
    btn.addEventListener('click', () => {
      setSearchType(btn.dataset.searchType || 'nft');
      const query = input.value.trim();
      if (query) {
        routeByIntent(query, btn.dataset.searchType || 'nft');
      }
    });
  });

  // Press Enter: follow selected search intent
  form.addEventListener('submit', function (e) {
    e.preventDefault();

    const query = input.value.trim();
    if (!query) return;
    routeByIntent(query);
  });

  document.addEventListener('click', function(e) {
    if (!root.contains(e.target)) openMenu(false);
  });
});

document.addEventListener('DOMContentLoaded', function () {

  const navDropdowns = document.querySelectorAll('.nav-dropdown');

  // Only allow one dropdown open at a time
  navDropdowns.forEach(function (dropdown) {
    dropdown.addEventListener('toggle', function () {
      if (!dropdown.open) return;

      navDropdowns.forEach(function (other) {
        if (other !== dropdown) {
          other.open = false;
        }
      });
    });
  });

  // Close dropdown when clicking outside
  document.addEventListener('click', function (event) {
    navDropdowns.forEach(function (dropdown) {
      if (!dropdown.contains(event.target)) {
        dropdown.open = false;
      }
    });
  });

});
</script>