<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Http\Requests\RegisterRequest;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;
use App\Services\WalletAddressService;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function register(RegisterRequest $r)
    {
        $name = $r->input('name');
        if (in_array($name, ['9Mint', 'Vlas'], true)) {
            $user = User::where('name', $name)->whereNull('email')->first();
            if (! $user) {
                abort(422, "{$name} account is already claimed.");
            }
            $user->email = $r->input('email');
            $user->password = $r->input('password');
            $user->role = $name === '9Mint' ? 'admin' : 'user';
            $user->save();

            if ($name === '9Mint') {
                try {
                    Role::firstOrCreate(['name' => 'admin']);
                    $user->assignRole('admin');
                } catch (\Throwable $e) {
                    // Ignore role assignment failures.
                }
            }

            return response()->json(['data' => $user], 201);
        }

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
        $name = $r->input('name');
        if (in_array($name, ['9Mint', 'Vlas'], true)) {
            $user = User::where('name', $name)->whereNull('email')->first();
            if (! $user) {
                return back()->withErrors(['name' => "{$name} account is already claimed."], 'register');
            }
            $user->email = $r->input('email');
            $user->password = $r->input('password');
            $user->role = $name === '9Mint' ? 'admin' : 'user';
            $user->save();

            if ($name === '9Mint') {
                try {
                    Role::firstOrCreate(['name' => 'admin']);
                    $user->assignRole('admin');
                } catch (\Throwable $e) {
                    // Ignore role assignment failures.
                }
            }

            Auth::login($user);
            $r->session()->regenerate();
        } else {
            $user = User::create($r->validated());
            Auth::login($user);
            $r->session()->regenerate();
        }

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
                ->with('show_forgot_password', true)
                ->withInput();
        }

        $r->session()->regenerate();

        return redirect()->intended(route('profile.settings'));
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

        $walletAddressService = app(WalletAddressService::class);
        $r->merge([
            'wallet_address' => $walletAddressService->normalize($r->input('wallet_address')),
        ]);

        $rules = [
            'name' => [
                'sometimes',
                'required',
                'string',
                'max:80',
                'regex:/^[A-Za-z0-9\-]+$/',
                Rule::unique('users', 'name')->ignore($user->id),
            ],
            'email' => [
                'sometimes',
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'wallet_address' => [
                'sometimes',
                'nullable',
                'string',
                'max:255',
                'min:26',
                'regex:/^(0x[a-f0-9]{40}|[A-Za-z0-9]{26,255})$/',
                Rule::unique('users', 'wallet_address')->ignore($user->id),
            ],
            'profile_image' => [
                'sometimes',
                'nullable',
                'image',
                'max:5120',
            ],
            'remove_profile_image' => [
                'sometimes',
                'nullable',
                'boolean',
            ],
        ];

        // Backward-compatible for environments where this migration isn't applied yet.
        if (Schema::hasColumn('users', 'description')) {
            $rules['description'] = ['sometimes', 'nullable', 'string', 'max:1000'];
        }

        // Backward-compatible for environments where this migration isn't applied yet.
        if (Schema::hasColumn('users', 'nfts_public')) {
            $rules['nfts_public'] = ['sometimes', 'required', 'boolean'];
        }

        $data = $r->validate($rules);

        $shouldRemoveProfileImage = (bool) ($data['remove_profile_image'] ?? false);
        unset($data['remove_profile_image']);

        if ($shouldRemoveProfileImage && ! $r->hasFile('profile_image')) {
            $this->deleteExistingProfileImage($user);
            $data['profile_image_url'] = null;
        }

        if ($r->hasFile('profile_image')) {
            $this->deleteExistingProfileImage($user);

            $directory = public_path('images/pfp');
            if (!File::exists($directory)) {
                File::makeDirectory($directory, 0755, true);
            }

            $uploaded = $r->file('profile_image');
            $extension = strtolower((string) $uploaded->getClientOriginalExtension());
            if ($extension === '') {
                $extension = strtolower((string) $uploaded->extension());
            }
            if ($extension === '') {
                $extension = 'png';
            }

            $filename = 'user-' . $user->id . '-' . Str::uuid() . '.' . $extension;
            $uploaded->move($directory, $filename);
            $data['profile_image_url'] = '/images/pfp/' . $filename;
        }

        $user->update($data);

        return back()->with('status', 'Profile updated successfully.');
    }

    private function deleteExistingProfileImage(User $user): void
    {
        $existingPath = $user->profile_image_url
            ? public_path(ltrim((string) $user->profile_image_url, '/'))
            : null;
        $basePfpPath = realpath(public_path('images/pfp'));

        if (! $existingPath || ! $basePfpPath) {
            return;
        }

        $normalizedExisting = str_replace('\\', '/', $existingPath);
        $normalizedBase = str_replace('\\', '/', $basePfpPath);

        if (str_starts_with($normalizedExisting, $normalizedBase) && File::exists($existingPath)) {
            File::delete($existingPath);
        }
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
