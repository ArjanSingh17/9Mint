<?php

namespace App\Http\Controllers;

use League\Glide\ServerFactory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class ImageController extends Controller
{
    public function show(Request $request, string $path)
    {
        $params = $request->only(['w', 'h', 'fit', 'fm', 'q']);

        // Resolve source: try public/ first, then storage/app/public/
        $sourcePath = null;
        $resolvedPath = $path;

        if (file_exists(public_path($path))) {
            $sourcePath = public_path();
        } else {
            $stripped = preg_replace('#^storage/#', '', $path);
            if (file_exists(storage_path('app/public/' . $stripped))) {
                $sourcePath = storage_path('app/public');
                $resolvedPath = $stripped;
            }
        }

        if (! $sourcePath) {
            abort(404);
        }

        $server = ServerFactory::create([
            'source' => $sourcePath,
            'cache' => storage_path('app/glide-cache'),
            'max_image_size' => 2000 * 2000,
            'defaults' => [
                'fm' => 'webp',
                'q' => 80,
            ],
        ]);

        // Generate the cached image and return it as a response
        $cachePath = $server->makeImage($resolvedPath, $params);
        $cacheFullPath = storage_path('app/glide-cache/' . $cachePath);

        $mime = match (pathinfo($cachePath, PATHINFO_EXTENSION)) {
            'webp' => 'image/webp',
            'png' => 'image/png',
            'gif' => 'image/gif',
            default => 'image/jpeg',
        };

        return Response::file($cacheFullPath, [
            'Content-Type' => $mime,
            'Cache-Control' => 'public, max-age=31536000, immutable',
        ]);
    }
}
