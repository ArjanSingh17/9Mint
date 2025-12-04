<?php

namespace App\Http\Controllers;

use App\Models\Collection;
use Illuminate\Http\Request;

class CollectionPageController extends Controller
{
    public function show($slug)
    {
        // If the collection itself doesn't exist, 404 is appropriate
        $collection = Collection::where('slug', $slug)->firstOrFail();

        // Load active NFTs inside this collection (can be empty)
        $nfts = $collection->nfts()->where('is_active', true)->get();

        // For now we special-case known slugs, but all use the same template
        if (in_array($slug, ['glossy-collection', 'superhero-collection'], true)) {
            return view('collections.show', compact('collection', 'nfts'));
        }

        // Unknown collection slug
        abort(404);
    }
}
