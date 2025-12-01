<?php

namespace App\Http\Controllers;

use App\Models\Nft;

class AboutUsController extends Controller
{
    public function index()
    {
        // load 4 random NFTs for display
        $nfts = Nft::where('is_active', true)->inRandomOrder()->limit(4)->get();

        return view('AboutUs', compact('nfts'));
    }
}
