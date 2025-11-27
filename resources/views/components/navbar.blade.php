      <nav class="navbar">
        <div class="logo-container">
          <img
            src="{{ asset('images/logo.png')}}"
            alt="9 Mint Logo"
            class="logo-image"
          />
        </div>

        <div class="nav-links">
          <a href="/homepage">Homepage</a>
        <a href="/aboutUs">About Us</a>
         <a href="/products">Products</a>
         <a href="/pricing">Pricing</a>
         <a href="/contactUs">Contact Us</a>
        </div>

        <div class="nav-auth"><a href="/checkout">
          <button class="basket-btn">
            <span class="basket-icon">🛒</span>
            <span class="basket-badge">1</span>
          </button></a>

          <button class="nav-btn signin" >
            <a href="/">Account</a>
          </button>
        </div>
      </nav>