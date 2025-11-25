<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminCollectionController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'creator_name' => 'nullable|string|max:255',
            'cover_image' => 'required|image|max:2048',
        ]);

        $path = $request->file('cover_image')->store('collections', 'public');

        $collection = Collection::create([
            'name'            => $validated['name'],
            'description'     => $validated['description'] ?? null,
            'creator_name'    => $validated['creator_name'] ?? null,
            'cover_image_url' => Storage::url($path),
        ]);

        return response()->json([
            'message' => 'Collection created successfully',
            'data'    => $collection,
        ], 201);
    }

    public function update(Request $request, Collection $collection)
    {
        $validated = $request->validate([
            'name'        => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'creator_name' => 'sometimes|string|max:255',
            'cover_image' => 'sometimes|image|max:2048',
        ]);

        if ($request->hasFile('cover_image')) {

            if ($collection->cover_image_url) {
                $old = str_replace('/storage/', '', $collection->cover_image_url);
                Storage::disk('public')->delete($old);
            }

            $path = $request->file('cover_image')->store('collections', 'public');
            $validated['cover_image_url'] = Storage::url($path);
        }

        $collection->update($validated);

        return response()->json([
            'message' => 'Collection updated successfully',
            'data'    => $collection,
        ]);
    }

    public function destroy(Collection $collection)
    {
        if ($collection->cover_image_url) {
            $old = str_replace('/storage/', '', $collection->cover_image_url);
            Storage::disk('public')->delete($old);
        }

        $collection->delete();

        return response()->json([
            'message' => 'Collection deleted successfully',
        ]);
    }
}
