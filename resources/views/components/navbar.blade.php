<nav class="navbar">
  {{-- Logo --}}
  <div class="logo-container">
    <a href="/homepage">
      <img src="{{ asset('images/logo.png') }}" alt="9 Mint Logo" class="logo-image" />
    </a>
  </div>

  {{-- Links --}}
  <div class="nav-links">
    <a href="/homepage">Homepage</a>
    <a href="/aboutUs">About Us</a>
    <a href="/products">Products</a>
    <a href="/pricing">Pricing</a>
    <a href="/contactUs">Contact Us</a>
  </div>

  {{-- Cart/auth --}}
  <div class="nav-auth">
    @php
      $cartCount = 0;
      $walletBalances = collect();
      if (auth()->check()) {
        $cartCount = \App\Models\CartItem::where('user_id', auth()->id())->sum('quantity');
        if (\Illuminate\Support\Facades\Schema::hasTable('wallets')) {
          $walletBalances = \App\Models\Wallet::query()
            ->where('user_id', auth()->id())
            ->where('balance', '>', 0)
            ->orderBy('currency')
            ->get();
        }
      }
    @endphp
    @auth
      @if($walletBalances->isNotEmpty())
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

    <a href="/cart">
      <button class="basket-btn">
        <span class="basket-icon">🛒</span>
        @if($cartCount > 0)
          <span class="basket-badge">{{ $cartCount }}</span>
        @endif
      </button></a>

    @auth
      <a href="/my-profile" class="nav-btn profile-icon" title="My Profile">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
          stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
          <circle cx="12" cy="7" r="4" />
        </svg>
      </a>

      <a href="{{ route('profile') }}" class="nav-btn account">
        Account
      </a>

      @if(Auth::user()->role === 'admin')
        <a href="{{ route('admin.dashboard') }}" class="nav-btn" style="color: white;">
          Admin Dashboard
        </a>
      @endif

      <form method="POST" action="{{ route('logout') }}" class="inline">
        @csrf
        <button type="submit" class="nav-btn signout">Logout</button>
      </form>
    @else
      <a href="{{ route('login') }}" class="nav-btn signin">Login / Register</a>
    @endauth
  </div>
</nav>