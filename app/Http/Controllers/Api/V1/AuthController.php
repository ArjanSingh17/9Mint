<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Http\Requests\RegisterRequest;

class AuthController extends Controller
{
    public function register(RegisterRequest $r)
    {
        $user = User::create($r->validated());
        return response()->json(['data' => $user], 201);
    }

    public function me(Request $r)
    {
        return $r->user();
    }

    public function showLogin()
    {
        return view('login-register');
    }

    public function showRegister()
    {
        return view('login-register');
    }

    public function registerWeb(RegisterRequest $r)
    {
        $user = User::create($r->validated());
        Auth::login($user);
        $r->session()->regenerate();

        if ($r->expectsJson()) {
            return response()->json(['data' => $user], 201);
        }

        return redirect()->intended(route('homepage'));
    }

    public function loginWeb(Request $r)
    {
        $r->validateWithBag('login', ['email' => ['required','email'],'password' => ['required'],
        ]);

        if (! \Illuminate\Support\Facades\Auth::attempt(
            $r->only('email','password'),
            $r->boolean('remember')
        )){
            return back()
                ->withErrors(['email' => 'Invalid credentials'], 'login')
                ->withinput();
        }

        $r->session()->regenerate();

        return redirect()->intended(route('profile'));
    }

    public function logout(Request $r)
    {
        Auth::logout();
        $r->session()->invalidate();
        $r->session()->regenerateToken();

        if($r->expectsJson()) {
            return response()->noContent();
        }
        return redirect()->route('login');
    }

    public function profile(Request $r)
    {
        return view('Homepage');
    }
}
