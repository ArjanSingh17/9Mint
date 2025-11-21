<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function edit(Request $request)
{
    // The 'auth' middleware ensures $request->user() is available and valid
    return view('profile.edit', [
        'user' => $request->user(),
    ]);
}
}
