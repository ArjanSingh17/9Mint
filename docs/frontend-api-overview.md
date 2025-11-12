\# 9Mint — Frontend Foundation (What you can build now)



\*\*API base:\*\* `/api/v1`  

\*\*Auth right now:\*\* public endpoints only, or use a dev \*\*Bearer token\*\* for protected routes.



---



\## Build these screens now



\- \*\*Collections list\*\* → `GET /collections` (paginated)

\- \*\*Collection detail\*\* → `GET /collections/{slug}`

\- \*\*NFTs list\*\* → `GET /nfts?search=\&collection\_id=` (paginated; both filters optional)

\- \*\*NFT detail\*\* → `GET /nfts/{slug}`

\- \*\*(Optional)\*\* price helper → `GET /price/convert?amount=0.08`



> Pagination is Laravel's standard JSON shape (`data`, `links`, `meta`). See docs if needed: https://laravel.com/docs/eloquent-resources#pagination



---



\## Minimal contract (quick reference)



\### Collections



```

GET /api/v1/collections

GET /api/v1/collections/{slug}

```



JSON fields: `id, slug, name, cover, creator\_name`



\### NFTs



```

GET /api/v1/nfts?search=\&collection\_id=\&page=

GET /api/v1/nfts/{slug}

```



JSON fields (per item):

```json

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



\### Protected (use dev token during FE build)



```

GET    /api/v1/me

GET    /api/v1/me/favourites

POST   /api/v1/nfts/{id}/favourite

GET    /api/v1/cart

POST   /api/v1/cart              (nft\_id, quantity?)

DELETE /api/v1/cart/{nftId}

POST   /api/v1/checkout

POST   /api/v1/admin/nfts        (multipart: image + fields)

```



\*\*Auth header for dev:\*\*

```

Authorization: Bearer <token>

```



Sanctum overview: https://laravel.com/docs/sanctum



---



\## Conventions



\- \*\*Slugs\*\* for detail routes (`/nfts/{slug}`, `/collections/{slug}`)

\- \*\*Images\*\* come as absolute/relative `image\_url`; just render it

\- \*\*Search:\*\* `?search=` does a simple LIKE on name

\- \*\*Filtering:\*\* `?collection\_id=` narrows the NFTs list

\- \*\*Errors:\*\* unauthenticated → 401, validation errors → 422 with field messages (standard Laravel)  

&nbsp; Docs: https://laravel.com/docs/validation#quick-writing-the-validation-logic





