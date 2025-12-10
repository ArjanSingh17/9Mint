<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Nft;

class HomeController extends Controller
{
    public function index()
    {
        $nfts = Nft::where('is_active', 1)->get();

        return view('homepage', compact('nfts'));
    }
}
