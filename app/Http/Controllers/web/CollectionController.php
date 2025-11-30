<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Collection;
use Illuminate\Http\Request;

class CollectionController extends Controller
{
    // Show all collections
    public function index()
    {
        $collections = Collection::whereNull('deleted_at')->get();
        return view('collections.index', compact('collections'));
    }

    // Show NFTs for a single collection
    public function show($slug)
    {
        $collection = Collection::where('slug', $slug)->firstOrFail();
        $nfts = $collection->nfts()->where('is_active', 1)->get();

        return view('collections.show', compact('collection', 'nfts'));
    }
}
