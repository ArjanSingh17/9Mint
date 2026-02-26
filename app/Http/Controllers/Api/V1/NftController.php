<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\NftResource;
use App\Models\Nft;
use Illuminate\Http\Request;

class NftController extends Controller
{
    public function index(Request $request)
    {
        $query = Nft::query()->marketVisible();

        if ($search = $request->query('search')) {
            $query->where('name', 'like', '%'.$search.'%');
        }

        if ($collectionId = $request->query('collection_id')) {
            $query->where('collection_id', $collectionId);
        }

        $nfts = $query
            ->orderBy('id')
            ->paginate(12);

        return NftResource::collection($nfts);
    }

    public function show(string $slug)
    {
        $nft = Nft::marketVisible()->where('slug', $slug)
            ->firstOrFail();

        return new NftResource($nft);
    }
}


