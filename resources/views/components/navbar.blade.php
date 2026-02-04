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
        <div class="nav-auth"><a href="/cart">
          <button class="basket-btn">
            <span class="basket-icon">🛒</span>
            @php
              $cart = session()->get('cart', []);
              $totalItems = array_sum(array_column($cart, 'quantity'));
            @endphp
            @if($totalItems > 0)
              <span class="basket-badge">{{ $totalItems }}</span>
            @endif
          </button></a>

    @auth
      <a href="{{ route('favourites.index') }}" class="nav-btn">
      Favourites
      </a>
      <a href="{{ route('profile') }}" class="nav-btn account">
        Account
      </a>

      <form method="POST" action="{{ route('logout') }}" class="inline">
        @csrf
        <button type="submit" class="nav-btn signout">Logout</button>
      </form>
    @else
      <a href="{{ route('login') }}" class="nav-btn signin">Login / Register</a>
    @endauth
  </div>
</nav>
