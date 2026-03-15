<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\CollectionResource;
use App\Models\Collection;
use Illuminate\Http\Request;

class CollectionController extends Controller
{
    public function index(Request $request)
    {
        $query = Collection::query()
            ->whereNull('deleted_at');

        // support both ?q= and ?search=
        $search = $request->query('q') ?? $request->query('search');

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                // Use whichever column exists in your DB: title OR name
                $q->where('title', 'like', '%' . $search . '%')
                  ->orWhere('name', 'like', '%' . $search . '%')
                  ->orWhere('slug', 'like', '%' . $search . '%');
            });
        }

        $collections = $query
            ->orderBy('id')
            ->paginate(12);

        return CollectionResource::collection($collections);
    }

    public function show(string $slug)
    {
        $collection = Collection::where('slug', $slug)->firstOrFail();

        return new CollectionResource($collection);
    }
}