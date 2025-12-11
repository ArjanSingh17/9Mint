<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Nft;

class NftController extends Controller
{
    public function show($collectionSlug, $nftSlug)
    {
        $nft = Nft::where('slug', $nftSlug)->where('is_active', 1)->firstOrFail();
        $collection = $nft->collection;

        return view('nfts.show', compact('nft', 'collection'));
    }
}
