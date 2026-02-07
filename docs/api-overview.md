\# 9Mint — Frontend Foundation (What you can build now)



**API base:** `/api/v1`  

**Auth right now:** public endpoints, plus protected ones using **Laravel Sanctum** – during dev you typically use a **Bearer token** (personal access token) for protected routes.



---



## Build these screens now



- **Health check** → `GET /health` (simple `{ "ok": true }`)

- **Collections list\*\* → `GET /collections` (paginated)

- **Collection detail*\* → `GET /collections/{slug}`

- **NFTs list** → `GET /nfts?search=\&collection\_id=` (paginated; both filters optional)

- **NFT detail** → `GET /nfts/{slug}`

- **(Optional)** price helper → `GET /price/convert?amount=0.08`

- **Register (API)** → `POST /register` (creates a new user; see `docs/dev-workflow.md` for how to create a dev token)



> Pagination is Laravel's standard JSON shape (`data`, `links`, `meta`). See docs if needed: https://laravel.com/docs/eloquent-resources#pagination



---



## Minimal contract (quick reference)



### Collections



```

GET /api/v1/collections

GET /api/v1/collections/{slug}

```



JSON fields: `id, slug, name, cover, creator\_name`



### NFTs



```

GET /api/v1/nfts?search=\&collection\_id=\&page=

GET /api/v1/nfts/{slug}

```



JSON fields (per item):

```

{

&nbsp; "id": 1,

&nbsp; "slug": "aurora-01",

&nbsp; "name": "Aurora 01",

&nbsp; "description": "...",

&nbsp; "image\_url": "/storage/nfts/abc.webp",

&nbsp; "price": { "amount": "0.08", "currency": "ETH" },

&nbsp; "editions": { "total": 50, "remaining": 49 },

&nbsp; "collection": { "id": 3 }

}

```



### Protected (use dev token during FE build)



```
GET    /api/v1/me

GET    /api/v1/me/favourites
POST   /api/v1/nfts/{nft}/favourite

GET    /api/v1/cart
POST   /api/v1/cart                  (upsert: nft_id, optional quantity)
GET    /api/v1/cart/{id}
PUT    /api/v1/cart/{id}             (update quantity)
DELETE /api/v1/cart/{nft}            (remove all lines for a given NFT)

GET    /api/v1/checkout              (list orders for current user)
GET    /api/v1/checkout/{id}         (get a single order)
POST   /api/v1/checkout              (create a crypto order from DB cart, optional checkout_token)
PUT    /api/v1/checkout/{id}         (update order items / quantities)
DELETE /api/v1/checkout/{id}         (delete order and restore stock)

POST   /api/v1/admin/nfts            (multipart: image + fields, admin role required)
```



**Auth header for dev (Sanctum personal access token):**

```

Authorization: Bearer <token>

```



Sanctum overview: https://laravel.com/docs/sanctum



---



## Conventions



- **Slugs** for detail routes (`/nfts/{slug}`, `/collections/{slug}`)

- **Images** come as absolute/relative `image\_url`; just render it

- **Search:** `?search=` does a simple LIKE on name

- **Filtering:** `?collection\_id=` narrows the NFTs list

- **Errors:** unauthenticated → 401, validation errors → 422 with field messages (standard Laravel)  

&nbsp; Docs: https://laravel.com/docs/validation#quick-writing-the-validation-logic





