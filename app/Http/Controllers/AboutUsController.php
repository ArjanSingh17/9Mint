<?php

namespace App\Http\Controllers;

use App\Models\Nft;

class AboutUsController extends Controller
{
    public function index()
    {
        $nfts = Nft::where('is_active', true)->get();

        return view('about-us', compact('nfts'));
    }
}
