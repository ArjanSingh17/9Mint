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
  - `POST /login` → `AuthController@loginWeb` (uses **username + password**)  
  - `POST /register` → `AuthController@registerWeb` (collects **username, email, password**)

- **Authenticated routes** (wrapped in `Route::middleware('auth')`)  
  - Logout: `POST /logout` → `AuthController@logout`  
  - Profile page: `GET /profile` → `AuthController@profile`  
  - Profile update: `PATCH /profile` → `AuthController@updateProfile`  
  - Password update: `PATCH /profile/password` → `AuthController@updatePassword`

- **User model fields**  
  - `name` (used as **username**), `email`, `password`, `role`, and **`wallet_address`** (nullable) are mass assignable.  
  - `wallet_address` is added by a later migration and surfaced on the profile form as the NFT wallet address.

---

## Cart & orders (listings → DB)

All of the following routes live inside the `auth` middleware group so only logged-in users can place orders.

- **Add to cart**  
  - `POST /cart`  
  - Reads `listing_id` from the form.  
  - Stores items in the **DB-backed cart** (`cart_items`) keyed by user + listing.  
  - Returns back with flash status messages for success / validation errors.

- **Remove from cart**  
  - `DELETE /cart/{id}`  
  - Removes the cart row and flashes a status message.

- **View orders**  
  - `GET /orders` → Blade view `orders.index`.  
  - Loads current user's `orders` with their `items.listing.token.nft` relation, ordered by `placed_at` then `created_at`.

- **Checkout**  
  - `GET /checkout` creates a pending order with a **locked quote** and expiry timestamp.  
  - The order stores pay/ref totals and FX metadata.  
  - If the checkout expires, the user must return to the cart.

- **Place order**  
  - `POST /orders`  
  - Uses the existing pending order and completes payment (mock providers).  
  - Listing is marked sold and token ownership is transferred to the buyer.  
  - Clears the checkout session key and redirects back to `/cart` with a success message that includes the order ID.

---

## Relationship to the API

- The **API** under `/api/v1/**` exposes collections, NFTs, a **DB-backed cart**, favourites, checkout/order endpoints, and admin NFT creation for SPA or external clients.
- The **Blade web flows** described here use the same underlying models (`Collection`, `Nft`, `Order`, `OrderItem`, `User`) but operate via standard web routes and a **session-based cart** (the `CartItem` model is used only by the API cart).
- During development you can:
  - Use the Blade UI only, or  
  - Mix Blade for pages and `/api/v1` for richer SPA-like interactions (e.g. crypto checkout and API cart).


