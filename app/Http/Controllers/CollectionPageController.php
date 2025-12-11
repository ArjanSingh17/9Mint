<?php

namespace App\Http\Controllers;

use App\Models\Collection;
use Illuminate\Http\Request;

class CollectionPageController extends Controller
{
    public function show($slug)
    {
        $collection = Collection::where('slug', $slug)->firstOrFail();

        $nfts = $collection->nfts()->where('is_active', true)->get();

        return view('collections.show', compact('collection', 'nfts'));
    }
}
