<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\Nft;
use App\Models\Collection;

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