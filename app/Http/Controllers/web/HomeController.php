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

        // it won't break if there are less than 2 active NFTs
        return view('Homepage', compact('nfts'));
    }
}
