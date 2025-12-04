## 9Mint — Web Flows (Blade UI)

This doc describes the **Blade-based web experience** wired up in `routes/web.php` and how it connects to the database and API layer.

---

## Public pages

- **Homepage**  
  - Route: `GET /` and `GET /homepage` → `HomeController@index`  
  - Shows featured content and entry points into collections/products.

- **Static information pages**  
  - Cart & checkout shells are Blade views:  
    - `GET /cart` → `resources/views/cart.blade.php`  
    - `GET /checkout` → `resources/views/checkout.blade.php`  
  - Marketing / info pages:  
    - `GET /pricing` → `pricing.blade.php`  
    - `GET /contactUs` → `contact-us.blade.php`  
    - `GET /contactUs/terms` → `terms-and-conditions.blade.php`  
    - `GET /contactUs/faqs` → `faqs.blade.php`

- **Products & collections**  
  - All products page: `GET /products` → `ProductsController@index`.  
  - Dynamic collections:  
    - Old URLs still work and redirect into the new dynamic system:  
      - `/products/Glossy-collection` → redirects to slug `glossy-collection`  
      - `/products/SuperheroCollection` → redirects to slug `superhero-collection`  
    - Canonical route:  
      - `GET /products/{slug}` → `CollectionPageController@show` (named `collections.show`)  
      - `{slug}` is a collection slug backed by the `collections` and `nfts` tables.

---

## Auth & profile

- **Guest-only routes**  
  - `GET /login` → login/register Blade (`AuthController@showLogin`)  
  - `GET /register` → same view (`AuthController@showRegister`)  
  - `POST /login` → `AuthController@loginWeb`  
  - `POST /register` → `AuthController@registerWeb`

- **Authenticated routes** (wrapped in `Route::middleware('auth')`)  
  - Logout: `POST /logout` → `AuthController@logout`  
  - Profile page: `GET /profile` → `AuthController@profile`  
  - Profile update: `PATCH /profile` → `AuthController@updateProfile`  
  - Password update: `PATCH /profile/password` → `AuthController@updatePassword`

- **User model fields**  
  - `name`, `email`, `password`, `role`, and **`wallet_address`** (nullable) are mass assignable.  
  - `wallet_address` is added by migration `2025_12_04_000000_add_wallet_address_to_users_table.php` and surfaced on the profile form.

---

## Cart & orders (session → DB)

All of the following routes live inside the `auth` middleware group so only logged-in users can place orders.

- **Add to cart**  
  - `POST /cart`  
  - Reads `nft_slug` and `size` from the form.  
  - Stores items in the **session** under the `cart` key, keyed by `"<slug>_<size>"` with fields: `nft_slug`, `size`, `price`, `quantity`.  
  - Returns back with flash status messages for success / validation errors.

- **Remove from cart**  
  - `DELETE /cart/{key}`  
  - Removes the given item key from the session `cart` and flashes a status message.

- **View orders**  
  - `GET /orders` → Blade view `orders.index`.  
  - Loads current user's `orders` with their `items.nft` relation, ordered by `placed_at` then `created_at`.

- **Place order**  
  - `POST /orders`  
  - Reads the current session `cart`.  
  - For each cart line:  
    - Looks up `Nft` by `slug` (or creates a placeholder NFT inside a “General” collection if not present).  
    - Decrements `editions_remaining` for the NFT.  
    - Creates an `OrderItem` row with quantities and GBP unit price.  
  - Creates an `Order` row with: `user_id`, `status='pending'`, `currency_code='GBP'`, `total_gbp`, `total_crypto=0`, and `placed_at=now()`.  
  - Optionally stores shipping info (`full_name`, `address`, `city`, `postal_code`) in the session under `shipping_info`.  
  - Clears the `cart` session and redirects back to `/cart` with a success message that includes the order ID.

---

## Relationship to the API

- The **API** under `/api/v1/**` exposes collections, NFTs, cart, favourites, checkout, and admin NFT creation for SPA or external clients.
- The **Blade web flows** described here use the same underlying models (`Collection`, `Nft`, `Order`, `OrderItem`, `CartItem`, `User`) but operate via standard web routes and session-based cart storage.
- During development you can:
  - Use the Blade UI only, or  
  - Mix Blade for pages and `/api/v1` for richer SPA-like interactions.


