<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Nft;

class HomeController extends Controller
{
    public function index()
    {
        // Pick any 2 random NFTs
        $nfts = Nft::where('is_active', 1)->inRandomOrder()->take(2)->get();

        // If your DB has less than 2 NFTs, it won't break
        return view('Homepage', compact('nfts'));
    }
}
