<section>
    <h3 class="text-xl font-semibold mb-3">Change Password</h3>
    <p class="text-gray-600 mb-6">Ensure your account is secure by using a long, random password.</p>

    <form method="POST" action="{{ route('password.update') }}" class="space-y-4">
        @csrf
        @method('patch')

        <div>
            <label for="current_password" class="block font-medium text-gray-700">Current Password</label>
            <input id="current_password" name="current_password" type="password" required
                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
            @error('current_password') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="password" class="block font-medium text-gray-700">New Password</label>
            <input id="password" name="password" type="password" required
                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
            @error('password') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="password_confirmation" class="block font-medium text-gray-700">Confirm New Password</label>
            <input id="password_confirmation" name="password_confirmation" type="password" required
                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
        </div>

        <button type="submit" class="bg-red-600 hover:bg-red-700 text-white py-2 px-4 rounded-md">
            Update Password
        </button>
    </form>
</section>