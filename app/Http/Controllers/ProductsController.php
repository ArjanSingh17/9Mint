<?php

namespace App\Http\Controllers;

use App\Models\Collection;

class ProductsController extends Controller
{
    public function index()
    {
        $collections = Collection::whereHas('nfts', function ($q) {
            $q->where('is_active', true);
        })
        ->with(['nfts' => function ($q) {
            $q->where('is_active', true)->orderBy('id');
        }])
        ->get();

        return view('products', compact('collections'));
    }
}
