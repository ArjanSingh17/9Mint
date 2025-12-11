<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Http\Requests\RegisterRequest;
use Illuminate\Validation\Rule;

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
        $r->validateWithBag('login', [
            'name' => ['required','string'],
            'password' => ['required'],
        ]);

        if (! \Illuminate\Support\Facades\Auth::attempt(
            $r->only('name','password'),
            $r->boolean('remember')
        )){
            return back()
                ->withErrors(['name' => 'Invalid credentials'], 'login')
                ->withInput();
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
        return view('profile.customer-profile', [
            'user' => $r->user(),
        ]);
    }

    public function updateProfile(Request $r)
    {
        /** @var \App\Models\User $user */
        $user = $r->user();

        $data = $r->validate([
            'name' => [
                'required',
                'string',
                'max:80',
                'regex:/^[A-Za-z0-9\-]+$/',
                Rule::unique('users', 'name')->ignore($user->id),
            ],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'wallet_address' => ['nullable', 'string', 'max:255'],
        ]);

        $user->update($data);

        return back()->with('status', 'Profile updated successfully.');
    }

    public function updatePassword(Request $r)
    {
        /** @var \App\Models\User $user */
        $user = $r->user();

        $r->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user->password = $r->input('password');
        $user->save();

        return back()->with('status', 'Password updated successfully.');
    }
}
