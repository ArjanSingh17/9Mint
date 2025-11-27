<nav class="navbar">
  <div class="logo-container">
    <a href="/homepage">
      <img src="{{ asset('images/logo.png') }}" alt="9 Mint Logo" class="logo-image" />
    </a>
  </div>

  <div class="nav-links">
    <a href="/homepage">Homepage</a>
    <a href="/aboutUs">About Us</a>
    <a href="/products">Products</a>
    <a href="/pricing">Pricing</a>
    <a href="/contactUs">Contact Us</a>
  </div>

  <div class="nav-auth">
    {{-- Cart --}}
    <a href="/checkout" class="basket-btn">
      <span class="basket-icon">🛒</span>
      <span class="basket-badge">{{ (int) session('cart_count', 0) }}</span>
    </a>

    @auth
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
