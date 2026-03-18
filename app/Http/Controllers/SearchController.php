<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\Nft;
use App\Models\Collection;
use App\Models\Listing;
use App\Models\User;
use App\Models\Conversation;
use App\Models\Friendship;

class SearchController extends Controller
{
    // -----------------------------------------
    // Existing: search NFTs (matches + others)
    // -----------------------------------------
    public function nfts(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $collection = $request->query('collection');

        $base = Nft::query()->with('collection');

        if ($collection) {
            $base->where('collection_name', $collection);
        }

        if ($q === '') {
            $items = $base->orderBy('id', 'desc')->get();
            $items = $this->attachUrls($items);

            return response()->json([
                'matches' => $items,
                'others'  => []
            ]);
        }

        $matches = (clone $base)
            ->where(function ($query) use ($q) {
                $query->where('name', 'like', "%{$q}%")
                      ->orWhere('description', 'like', "%{$q}%");
            })
            ->orderByRaw("
                CASE
                    WHEN name LIKE ? THEN 0
                    WHEN name LIKE ? THEN 1
                    ELSE 2
                END
            ", ["{$q}%", "%{$q}%"])
            ->get();

        $others = (clone $base)
            ->whereNotIn('id', $matches->pluck('id'))
            ->orderBy('id', 'desc')
            ->get();

        $matches = $this->attachUrls($matches);
        $others  = $this->attachUrls($others);

        return response()->json([
            'matches' => $matches,
            'others'  => $others
        ]);
    }

    // -----------------------------------------
    // New: suggestions for dropdown
    // -----------------------------------------
    public function suggestions(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        if ($q === '') {
            return response()->json([
                'nfts' => [],
                'collections' => [],
            ]);
        }

        // NFTs (top 8)
        $nfts = Nft::query()
            ->with('collection')
            ->where(function ($query) use ($q) {
                $query->where('name', 'like', "%{$q}%")
                      ->orWhere('description', 'like', "%{$q}%");
            })
            ->orderByRaw("
                CASE
                    WHEN name LIKE ? THEN 0
                    WHEN name LIKE ? THEN 1
                    ELSE 2
                END
            ", ["{$q}%", "%{$q}%"])
            ->limit(8)
            ->get();

        $nfts = $this->attachUrls($nfts);

        // Collections (top 6)
        $collections = Collection::query()
            ->where('name', 'like', "%{$q}%")
            ->orWhere('slug', 'like', "%{$q}%")
            ->orderByRaw("
                CASE
                    WHEN name LIKE ? THEN 0
                    WHEN name LIKE ? THEN 1
                    ELSE 2
                END
            ", ["{$q}%", "%{$q}%"])
            ->limit(6)
            ->get()
            ->map(function ($c) {
                return [
                    'id' => $c->id,
                    'name' => $c->name,
                    'slug' => $c->slug,
                    'collection_url' => $this->collectionUrlFromSlug($c->slug),
                ];
            });

        return response()->json([
            'nfts' => $nfts,
            'collections' => $collections,
        ]);
    }

    public function nftsPage(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $sort = (string) $request->query('sort', 'newest');
        $currency = strtoupper(trim((string) $request->query('currency', '')));
        $minPrice = $request->query('min_price');
        $maxPrice = $request->query('max_price');
        $inStock = $request->boolean('in_stock');
        $oneOfOne = $request->boolean('one_of_one');

        $activeListingScope = function ($query) use ($currency, $minPrice, $maxPrice) {
            $query->where('listings.status', 'active')
                ->where(function ($q) {
                    $q->whereNull('listings.reserved_until')
                        ->orWhere('listings.reserved_until', '<', now());
                });

            if ($currency !== '') {
                $query->where('listings.ref_currency', $currency);
            }
            if (is_numeric($minPrice)) {
                $query->where('listings.ref_amount', '>=', (float) $minPrice);
            }
            if (is_numeric($maxPrice)) {
                $query->where('listings.ref_amount', '<=', (float) $maxPrice);
            }
        };

        $query = Nft::query()
            ->marketVisible()
            ->with(['collection'])
            ->withAvg('reviews', 'rating')
            ->withMin([
                'listings as active_price_min' => function ($q) {
                    $q->where('listings.status', 'active')
                        ->where(function ($q2) {
                            $q2->whereNull('listings.reserved_until')
                                ->orWhere('listings.reserved_until', '<', now());
                        });
                },
            ], 'ref_amount');

        if ($q !== '') {
            $query->where('name', 'like', "%{$q}%");
        }
        if ($oneOfOne) {
            $query->where('editions_total', 1);
        }
        if ($inStock || $currency !== '' || is_numeric($minPrice) || is_numeric($maxPrice)) {
            $query->whereHas('listings', $activeListingScope);
        }

        switch ($sort) {
            case 'name-asc':
                $query->orderBy('name', 'asc');
                break;
            case 'name-desc':
                $query->orderBy('name', 'desc');
                break;
            case 'price-asc':
                $query->orderByRaw('CASE WHEN active_price_min IS NULL THEN 1 ELSE 0 END')
                    ->orderBy('active_price_min', 'asc');
                break;
            case 'price-desc':
                $query->orderByRaw('CASE WHEN active_price_min IS NULL THEN 1 ELSE 0 END')
                    ->orderBy('active_price_min', 'desc');
                break;
            case 'rating-desc':
                $query->orderByDesc('reviews_avg_rating');
                break;
            case 'newest':
            default:
                $query->orderByDesc('created_at');
                break;
        }

        $nfts = $query->paginate(24)->withQueryString();

        $nftIds = $nfts->getCollection()->pluck('id')->all();
        $activeListings = empty($nftIds)
            ? collect()
            : Listing::query()
                ->join('nft_tokens', 'listings.token_id', '=', 'nft_tokens.id')
                ->whereIn('nft_tokens.nft_id', $nftIds)
                ->where('listings.status', 'active')
                ->where(function ($q) {
                    $q->whereNull('listings.reserved_until')
                        ->orWhere('listings.reserved_until', '<', now());
                })
                ->orderBy('listings.ref_amount', 'asc')
                ->get(['listings.*', 'nft_tokens.nft_id']);

        $listingByNftId = $activeListings->groupBy('nft_id')->map->first();
        $listedCountByNftId = $activeListings->groupBy('nft_id')->map(fn ($rows) => $rows->count());

        $currencies = $activeListings->pluck('ref_currency')->filter()->unique()->sort()->values();

        foreach ($nfts as $nft) {
            $nft->active_listing = $listingByNftId->get($nft->id);
            $nft->listed_editions_count = (int) ($listedCountByNftId->get($nft->id, 0));
        }

        return view('search.nfts', [
            'nfts' => $nfts,
            'currencies' => $currencies,
            'filters' => [
                'q' => $q,
                'sort' => $sort,
                'currency' => $currency,
                'min_price' => $minPrice,
                'max_price' => $maxPrice,
                'in_stock' => $inStock,
                'one_of_one' => $oneOfOne,
            ],
        ]);
    }

    public function collectionsPage(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $sort = (string) $request->query('sort', 'newest');
        $inStock = $request->boolean('in_stock');

        $query = Collection::query()
            ->approved()
            ->whereHas('nfts', function ($q) {
                $q->marketVisible();
            })
            ->with(['nfts' => function ($q) {
                $q->marketVisible()->orderBy('id');
            }]);

        if ($q !== '') {
            $query->where(function ($builder) use ($q) {
                $builder->where('name', 'like', "%{$q}%")
                    ->orWhere('slug', 'like', "%{$q}%");
            });
        }

        $collections = $query->get();
        $collectionIds = $collections->pluck('id')->all();
        $listedCountsByCollectionId = empty($collectionIds)
            ? collect()
            : Listing::query()
                ->join('nft_tokens', 'listings.token_id', '=', 'nft_tokens.id')
                ->join('nfts', 'nft_tokens.nft_id', '=', 'nfts.id')
                ->whereIn('nfts.collection_id', $collectionIds)
                ->where('listings.status', 'active')
                ->where(function ($q) {
                    $q->whereNull('listings.reserved_until')
                        ->orWhere('listings.reserved_until', '<', now());
                })
                ->groupBy('nfts.collection_id')
                ->selectRaw('nfts.collection_id, COUNT(*) as listed_count')
                ->pluck('listed_count', 'nfts.collection_id');

        foreach ($collections as $collection) {
            $collection->listed_editions_count = (int) ($listedCountsByCollectionId[$collection->id] ?? 0);
            $collection->total_editions_count = (int) $collection->nfts->sum('editions_total');
        }

        if ($inStock) {
            $collections = $collections->filter(fn ($collection) => (int) ($collection->listed_editions_count ?? 0) > 0)->values();
        }

        $collections = match ($sort) {
            'name-asc' => $collections->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)->values(),
            'name-desc' => $collections->sortByDesc('name', SORT_NATURAL | SORT_FLAG_CASE)->values(),
            'listed-desc' => $collections->sortByDesc('listed_editions_count')->values(),
            default => $collections->sortByDesc('created_at')->values(),
        };

        return view('search.collections', [
            'collections' => $collections,
            'filters' => [
                'q' => $q,
                'sort' => $sort,
                'in_stock' => $inStock,
            ],
        ]);
    }

    public function usersPage(Request $request)
    {
        $queryText = trim((string) $request->query('q', ''));
        $sort = (string) $request->query('sort', 'name-asc');
        $currentUserId = (int) $request->user()->id;

        $query = User::query()
            ->where('id', '!=', $currentUserId)
            ->where(function ($q) {
                $q->where('search_public', true)
                    ->orWhereNull('search_public');
            });

        if ($queryText !== '') {
            $query->where(function ($q) use ($queryText) {
                $q->where('name', 'like', "%{$queryText}%")
                    ->orWhere('email', 'like', "%{$queryText}%");
            });
        }

        switch ($sort) {
            case 'name-desc':
                $query->orderBy('name', 'desc');
                break;
            case 'newest':
                $query->orderBy('created_at', 'desc');
                break;
            case 'oldest':
                $query->orderBy('created_at', 'asc');
                break;
            case 'name-asc':
            default:
                $query->orderBy('name', 'asc');
                break;
        }

        $users = $query->get();
        $userIds = $users->pluck('id')->map(fn ($id) => (int) $id)->all();

        $friendshipStates = [];
        $friendConversationIds = [];

        if (! empty($userIds)) {
            $friendships = Friendship::query()
                ->where(function ($q) use ($currentUserId, $userIds) {
                    $q->where(function ($sub) use ($currentUserId, $userIds) {
                        $sub->where('requester_id', $currentUserId)
                            ->whereIn('addressee_id', $userIds);
                    })->orWhere(function ($sub) use ($currentUserId, $userIds) {
                        $sub->where('addressee_id', $currentUserId)
                            ->whereIn('requester_id', $userIds);
                    });
                })
                ->get();

            $friendIds = [];

            foreach ($friendships as $friendship) {
                $otherUserId = (int) $friendship->requester_id === $currentUserId
                    ? (int) $friendship->addressee_id
                    : (int) $friendship->requester_id;

                if ($friendship->status === Friendship::STATUS_ACCEPTED) {
                    $friendshipStates[$otherUserId] = 'friends';
                    $friendIds[] = $otherUserId;
                    continue;
                }

                $friendshipStates[$otherUserId] = (int) $friendship->requester_id === $currentUserId
                    ? 'outgoing_pending'
                    : 'incoming_pending';
            }

            $friendIds = array_values(array_unique($friendIds));

            if (! empty($friendIds)) {
                $conversations = Conversation::query()
                    ->where('type', 'user')
                    ->whereNull('ticket_id')
                    ->where(function ($q) use ($currentUserId, $friendIds) {
                        $q->where(function ($sub) use ($currentUserId, $friendIds) {
                            $sub->where('sender_id', $currentUserId)
                                ->whereIn('receiver_id', $friendIds);
                        })->orWhere(function ($sub) use ($currentUserId, $friendIds) {
                            $sub->whereIn('sender_id', $friendIds)
                                ->where('receiver_id', $currentUserId);
                        });
                    })
                    ->get();

                foreach ($conversations as $conversation) {
                    $otherUserId = (int) $conversation->sender_id === $currentUserId
                        ? (int) $conversation->receiver_id
                        : (int) $conversation->sender_id;

                    $friendConversationIds[$otherUserId] = (int) $conversation->id;
                }
            }
        }

        return view('users', [
            'users' => $users,
            'filters' => [
                'q' => $queryText,
                'sort' => $sort,
            ],
            'friendshipStates' => $friendshipStates,
            'friendConversationIds' => $friendConversationIds,
        ]);
    }

    // -----------------------------------------
    // Helpers
    // -----------------------------------------
    private function attachUrls($items)
    {
        return $items->map(function ($nft) {
            $collectionSlug = optional($nft->collection)->slug;

            $nft->collection_url = $collectionSlug ? $this->collectionUrlFromSlug($collectionSlug) : null;
            $nft->nft_url = $this->nftUrlFromNft($nft);

            return $nft;
        });
    }

    private function collectionUrlFromSlug(string $slug): string
    {
        // try named routes first
        $candidates = [
            'collections.show',
            'collection.show',
            'collections.view',
            'collection.view',
        ];

        foreach ($candidates as $name) {
            if (Route::has($name)) {
                return route($name, $slug);
            }
        }

        // fallback (change if your real path is singular)
        return url("/collections/{$slug}");
    }

    private function nftUrlFromNft($nft): ?string
    {
        // Prefer slug if present
        $slug = $nft->slug ?? null;

        // Try named routes first
        $candidates = [
            'nfts.show',
            'nft.show',
            'products.show',
            'product.show',
        ];

        foreach ($candidates as $name) {
            if (Route::has($name) && $slug) {
                return route($name, $slug);
            }
        }

        // fallback paths (adjust if your app differs)
        if ($slug) return url("/nfts/{$slug}");
        return isset($nft->id) ? url("/nfts/{$nft->id}") : null;
    }
}