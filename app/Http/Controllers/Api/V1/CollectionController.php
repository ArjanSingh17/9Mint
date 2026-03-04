<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\CollectionResource;
use App\Models\Collection;

class CollectionController extends Controller
{
    public function index()
    {
        $collections = Collection::approved()->whereNull('deleted_at')
            ->orderBy('id')
            ->paginate(12);

        return CollectionResource::collection($collections);
    }

    public function show(string $slug)
    {
        $collection = Collection::approved()->where('slug', $slug)->firstOrFail();

        return new CollectionResource($collection);
    }
}


