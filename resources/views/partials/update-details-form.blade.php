<section>
    <h2 class="text-2xl font-semibold mb-4">Account Details</h2>
    <p class="text-gray-600 mb-6">Update your account contact and wallet details.</p>

    <form method="POST" action="{{ route('profile.update') }}" class="space-y-4">
        @csrf
        @method('patch')

        <div>
            <label for="email" class="block font-medium text-gray-700">Email</label>
            <input
                id="email"
                name="email"
                type="email"
                value="{{ old('email', Auth::user()->email) }}"
                required
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
            >
            @error('email') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="wallet_address" class="block font-medium text-gray-700">Wallet Address</label>
            <input
                id="wallet_address"
                name="wallet_address"
                type="text"
                value="{{ old('wallet_address', Auth::user()->wallet_address) }}"
                placeholder="Wallet address (e.g. 0x...)"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
            >
            <p class="text-xs text-gray-500 mt-1">This wallet receives your purchased NFTs.</p>
            @error('wallet_address') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
        </div>

        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white py-2 px-4 rounded-md">
            Save Details
        </button>
    </form>

    <div style="margin-top: 18px; padding: 14px; border: 1px solid var(--border-soft); border-radius: 10px; background: color-mix(in srgb, var(--surface-panel) 90%, #000 10%);">
        @include('partials.update-password-form')
    </div>
</section>