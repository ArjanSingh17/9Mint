<section>
    {{-- Header --}}
    <h2 class="text-2xl font-semibold mb-4">Account Details</h2>
    <p class="text-gray-600 mb-6">Update your username, email, and wallet information.</p>

    {{-- : POST method with PATCH directive --}}
    <form method="POST" action="{{ route('profile.update') }}" class="space-y-4">
        @csrf
        @method('patch') 
        
        {{-- Username Field --}}
        <div>
            <label for="name" class="block font-medium text-gray-700">Username</label>
            <input id="name" name="name" type="text" value="{{ old('name', Auth::user()->name) }}" required autofocus 
                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
            @error('name') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
        </div>

        {{-- Email Field --}}
        <div>
            <label for="email" class="block font-medium text-gray-700">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email', Auth::user()->email) }}" required
                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
            @error('email') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
        </div>

        {{-- Wallet Address --}}
        <div>
            <label for="wallet_address" class="block font-medium text-gray-700">Wallet Address</label>
            <input id="wallet_address" name="wallet_address" type="text" 
                   value="{{ old('wallet_address', Auth::user()->wallet_address) }}"
                   placeholder="Wallet address (e.g. 0x...)"
                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
            <p class="text-xs text-gray-500 mt-1">This wallet receives your purchased NFTs.</p>
            @error('wallet_address') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="inline-flex items-center gap-2">
                <input type="hidden" name="nfts_public" value="0">
                <input
                    id="nfts_public"
                    name="nfts_public"
                    type="checkbox"
                    value="1"
                    @checked((bool) old('nfts_public', Auth::user()->nfts_public ?? false))
                >
                <span class="font-medium text-gray-700">Keep my owned NFTs public</span>
            </label>
            <p class="text-xs text-gray-500 mt-1">If turned off, only you can view your owned NFT list on your profile page.</p>
            @error('nfts_public') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
        </div>

        {{-- Submit --}}
        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white py-2 px-4 rounded-md">
            Save Changes
        </button>
    </form>
</section>