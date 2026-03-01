@props([
    'user',
    'emptyText' => 'No badges yet.',
])

@php
    $badges = method_exists($user, 'profileBadges') ? $user->profileBadges() : [];
@endphp

@once
    @push('styles')
        <style>
            .profile-badges {
                display: flex;
                flex-wrap: wrap;
                gap: 8px;
                margin-top: 10px;
            }

            .profile-badge-pill {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                padding: 6px 10px;
                border-radius: 999px;
                border: 1px solid var(--border-soft);
                background: color-mix(in srgb, var(--surface-panel) 86%, #000 14%);
                color: var(--text-main);
                font-size: 12px;
                font-weight: 600;
                cursor: help;
                text-align: center;
                white-space: nowrap;
            }

            .profile-badge-pill--superadmin {
                border-color: color-mix(in srgb, #f59e0b 60%, var(--border-soft) 40%);
            }

            .profile-badge-pill--admin {
                border-color: color-mix(in srgb, #2563eb 60%, var(--border-soft) 40%);
            }

            .profile-badge-pill--banned {
                border-color: color-mix(in srgb, #ef4444 60%, var(--border-soft) 40%);
            }

            .profile-badge-empty {
                color: var(--subtext-color);
                margin: 8px 0 0;
                font-size: 13px;
                text-align: left;
            }
        </style>
    @endpush
@endonce

@if (empty($badges))
    <p class="profile-badge-empty">{{ $emptyText }}</p>
@else
    <div class="profile-badges">
        @foreach ($badges as $badge)
            <span
                class="profile-badge-pill profile-badge-pill--{{ $badge['key'] }}"
                title="{{ $badge['description'] }}"
                aria-label="{{ $badge['label'] }}: {{ $badge['description'] }}"
            >
                {{ $badge['label'] }}
            </span>
        @endforeach
    </div>
@endif
