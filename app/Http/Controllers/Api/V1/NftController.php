<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Nft;
use App\Http\Resources\NftResource;
use App\Http\Requests\StoreNftRequest;
use Illuminate\Support\Facades\Storage;

class NftController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $q = Nft::query()->where('is_active', true);
        if ($cid = request('collection_id')) $q->where('collection_id', $cid);
        if ($s = request('search')) $q->where('name','like',"%{$s}%");
        return NftResource::collection($q->paginate(12));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreNftRequest $req)
    {
        $data = $req->validated();
        $path = $req->file('image')->store('nfts','public');
        $data['image_url'] = Storage::url($path);
        $data['editions_remaining'] = $data['editions_total'];
        $nft = Nft::create($data);
        return response()->json(['data'=>new NftResource($nft)], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $slug)
    {
        $nft = \App\Models\Nft::where('slug',$slug)->firstOrFail();
        return new \App\Http\Resources\NftResource($nft);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
