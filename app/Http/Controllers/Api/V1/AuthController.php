<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class AuthController extends Controller
{
    public function register(Request $r)
    {
    //
    }

    public function login(Request $r)
    {
        $r->validate(['email'=>'required|email','password'=>'required']);
        if (! Auth::attempt($r->only('email','password'), true)) {
            return response()->json(['message'=>'Invalid credentials'], 422);
        }
        $r->session()->regenerate();
        return response()->noContent();
    }

    public function logout(Request $r)
    {
        Auth::logout();
        $r->session()->invalidate();
        $r->session()->regenerateToken();
        return response()->noContent();
    }

    public function me(Request $r)
    {
        return $r->user(); }
}
