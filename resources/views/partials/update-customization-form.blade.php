@once
    @push('styles')
        <style>
            .profile-customization-subbox {
                margin-top: 14px;
                padding: 14px;
                border: 1px solid var(--border-soft);
                border-radius: 10px;
                background: color-mix(in srgb, var(--surface-panel) 90%, #000 10%);
            }

            .profile-pfp-section {
                margin-bottom: 16px;
            }

            .profile-pfp-row {
                display: flex;
                align-items: center;
                gap: 14px;
                flex-wrap: wrap;
            }

            .profile-pfp-preview {
                width: 120px;
                height: 120px;
                border-radius: 999px;
                border: 1px solid var(--border-soft);
                background: color-mix(in srgb, var(--surface-input) 70%, #000 30%);
                color: var(--text-secondary);
                display: flex;
                align-items: center;
                justify-content: center;
                overflow: hidden;
                cursor: pointer;
                user-select: none;
                text-align: center;
                font-size: 12px;
                padding: 8px;
            }

            .profile-pfp-preview:hover {
                border-color: var(--link-hover);
            }

            .profile-pfp-preview:focus-visible {
                outline: 2px solid var(--link-hover);
                outline-offset: 2px;
            }

            .profile-pfp-preview img {
                width: 100%;
                height: 100%;
                object-fit: cover;
                display: block;
            }

            .profile-pfp-preview-initial {
                width: 100%;
                height: 100%;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 36px;
                font-weight: 700;
                color: #fff;
                background: var(--link-hover);
                border-radius: 999px;
            }

            .profile-pfp-controls {
                display: flex;
                flex-direction: column;
                gap: 8px;
            }

            .profile-pfp-button {
                border: none;
                background: transparent;
                color: var(--link-hover);
                text-decoration: underline;
                cursor: pointer;
                text-align: left;
                padding: 0;
                font-size: 13px;
                width: fit-content;
            }

            .profile-pfp-hidden-input {
                display: none;
            }
        </style>
    @endpush

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const root = document.querySelector('[data-profile-pfp-root]');
                if (!root) return;

                const preview = root.querySelector('[data-profile-pfp-preview]');
                const input = root.querySelector('[data-profile-pfp-input]');
                const removeBtn = root.querySelector('[data-profile-pfp-remove]');
                const removeFlag = root.querySelector('[data-profile-pfp-remove-flag]');
                const defaultInitial = root.dataset.defaultInitial || '?';
                const existingSrc = root.dataset.existingSrc || '';

                const renderDefault = function () {
                    preview.innerHTML = '<span class="profile-pfp-preview-initial">' + defaultInitial + '</span>';
                    removeBtn.style.display = existingSrc ? 'inline-block' : 'none';
                };

                const renderExisting = function () {
                    if (!existingSrc) {
                        renderDefault();
                        return;
                    }
                    preview.innerHTML = '<img src="' + existingSrc + '" alt="Current profile picture">';
                    removeBtn.style.display = 'inline-block';
                };

                const openPicker = function () {
                    if (input) input.click();
                };

                preview.addEventListener('click', openPicker);
                preview.addEventListener('keydown', function (event) {
                    if (event.key === 'Enter' || event.key === ' ') {
                        event.preventDefault();
                        openPicker();
                    }
                });

                input.addEventListener('change', function () {
                    const file = input.files && input.files[0];
                    if (!file) {
                        if (existingSrc) {
                            renderExisting();
                            removeFlag.value = '0';
                        } else {
                            renderDefault();
                            removeFlag.value = '0';
                        }
                        return;
                    }

                    const reader = new FileReader();
                    reader.onload = function (event) {
                        preview.innerHTML = '<img src="' + String(event.target?.result || '') + '" alt="Profile picture preview">';
                        removeBtn.style.display = 'inline-block';
                        removeFlag.value = '0';
                    };
                    reader.readAsDataURL(file);
                });

                removeBtn.addEventListener('click', function (event) {
                    event.preventDefault();
                    if (input) input.value = '';
                    removeFlag.value = '1';
                    renderDefault();
                    removeBtn.style.display = 'none';
                });

                if (existingSrc) {
                    renderExisting();
                } else {
                    renderDefault();
                }
            });
        </script>
    @endpush
@endonce

<section>
    <h2 class="text-2xl font-semibold mb-4">Account Customization</h2>
    <p class="text-gray-600 mb-6">Control your profile identity and visibility settings.</p>

    <form method="POST" action="{{ route('profile.update') }}" class="space-y-4" enctype="multipart/form-data">
        @csrf
        @method('patch')

        @php
            $currentProfileImageUrl = Auth::user()->profile_image_url
                ? asset(ltrim((string) Auth::user()->profile_image_url, '/'))
                : '';
            $profileInitial = strtoupper(substr((string) Auth::user()->name, 0, 1));
        @endphp

        <div class="profile-pfp-section" data-profile-pfp-root data-existing-src="{{ $currentProfileImageUrl }}" data-default-initial="{{ $profileInitial }}">
            <label class="block font-medium text-gray-700">Profile Picture</label>
            <div class="profile-pfp-row">
                <div
                    class="profile-pfp-preview"
                    data-profile-pfp-preview
                    role="button"
                    tabindex="0"
                    aria-label="Upload profile picture"
                ></div>

                <div class="profile-pfp-controls">
                    <button type="button" class="profile-pfp-button" data-profile-pfp-remove>Remove profile picture</button>
                    <p class="text-xs text-gray-500" style="margin:0;">Click the circle to upload a new profile picture.</p>
                    <p class="text-xs text-gray-500" style="margin:0;">If removed, your profile falls back to default avatar.</p>
                </div>
            </div>
            <input type="hidden" name="remove_profile_image" value="0" data-profile-pfp-remove-flag>
            <input
                id="profile_image"
                name="profile_image"
                type="file"
                accept="image/*"
                class="profile-pfp-hidden-input"
                data-profile-pfp-input
            >
            @error('profile_image') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="name" class="block font-medium text-gray-700">Username</label>
            <input
                id="name"
                name="name"
                type="text"
                value="{{ old('name', Auth::user()->name) }}"
                required
                autofocus
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
            >
            @error('name') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="description" class="block font-medium text-gray-700">Description</label>
            <textarea
                id="description"
                name="description"
                rows="3"
                placeholder="Tell people about yourself..."
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
            >{{ old('description', Auth::user()->description) }}</textarea>
            @error('description') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
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

        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white py-2 px-4 rounded-md">
            Save Customization
        </button>
    </form>

    <div class="profile-customization-subbox">
        <h3 class="text-lg font-semibold mb-2">Badges</h3>
        <x-profile-badges :user="Auth::user()" />
    </div>
</section>
