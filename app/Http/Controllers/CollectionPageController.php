<?php

namespace App\Http\Controllers;

use App\Models\Collection;
use Illuminate\Http\Request;

class CollectionPageController extends Controller
{
    public function show($slug)
    {
        // Find collection by slug
        $collection = Collection::where('slug', $slug)->firstOrFail();

        // Load NFTs inside this collection
        $nfts = $collection->nfts()->where('is_active', true)->get();

        // Determine which Blade to load
        if ($slug === 'glossy-collection') {
            return view('Glossy-collection', compact('collection', 'nfts'));
        }

        if ($slug === 'superhero-collection') {
            return view('SuperheroCollection', compact('collection', 'nfts'));
        }

        abort(404); // fallback if needed
    }
}
