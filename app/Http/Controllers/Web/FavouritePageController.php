<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FavouritePageController extends Controller
{
    public function index()
    {
        // Get the logged-in user
        $user = Auth::user();

        // Get their favourite NFTs (using the relationship we checked earlier)
        $favourites = $user->favourites()->get();

        // Load the view and pass the data
        return view('favourites.index', compact('favourites'));
    }
}