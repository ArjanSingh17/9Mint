<?php

namespace App\Http\Controllers;

use App\Models\Collection;

class ProductsController extends Controller
{
    public function index()
    {
        // Load all collections
        $collections = Collection::whereNull('deleted_at')->get();

        return view('Products', compact('collections'));
    }
}
