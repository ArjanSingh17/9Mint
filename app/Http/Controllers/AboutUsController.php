<?php

namespace App\Http\Controllers;

use App\Models\Nft;

class AboutUsController extends Controller
{
    public function index()
    {
        $nfts = Nft::marketVisible()->get();

        return view('about-us', compact('nfts'));
    }
}
