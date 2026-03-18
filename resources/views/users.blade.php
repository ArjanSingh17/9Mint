<style>
    .profile-show-avatar {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        background: var(--link-hover);
        color: #fff;
        font-size: 30px;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 20px;
    }

    .profile-show-avatar img {
        width: 100%;
        height: 100%;
        border-radius: 50%;
        object-fit: cover;
        display: block;
    }

    .users-filter-bar {
        max-width: 980px;
        margin: 0 auto 18px;
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        align-items: flex-end;
        justify-content: center;
        padding: 16px 20px;
        background: var(--surface-panel);
        border: 1px solid var(--border-soft);
        border-radius: 12px;
    }

    .users-filter-group {
        display: flex;
        flex-direction: column;
        gap: 4px;
        min-width: 180px;
        text-align: left;
    }

    .users-filter-group label {
        font-size: 0.75rem;
        font-weight: 600;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    .users-filter-group input,
    .users-filter-group select {
        background: var(--surface-muted);
        color: var(--text-main);
        border: 1px solid var(--border-soft);
        border-radius: 6px;
        padding: 6px 10px;
        font-size: 0.9rem;
        outline: none;
    }

    .users-filter-group input:focus,
    .users-filter-group select:focus {
        border-color: var(--button-bg);
    }

    .users-filter-apply {
        background: var(--link-hover);
        color: var(--text-inverse);
        border: none;
        border-radius: 6px;
        padding: 6px 18px;
        font-size: 0.9rem;
        cursor: pointer;
        margin-top: auto;
        text-decoration: none;
    }

    .users-filter-apply:hover {
        background: color-mix(in srgb, var(--link-hover) 85%, #000 15%);
    }

    .users-filter-reset {
        background: transparent;
        color: var(--text-muted);
        border: 1px solid var(--border-soft);
        border-radius: 6px;
        padding: 6px 14px;
        font-size: 0.85rem;
        cursor: pointer;
        margin-top: auto;
        text-decoration: none;
    }

    .users-filter-reset:hover {
        background: var(--surface-muted);
        color: var(--text-main);
    }

    .user-avatar-link {
        display: inline-flex;
    }

    .user-card-actions {
        width: 100%;
        display: flex;
        flex-direction: column;
        gap: 10px;
        margin-top: 4px;
    }

    .user-card-actions-row {
        width: 100%;
        display: flex;
        gap: 10px;
        justify-content: center;
    }

    .user-card-actions-row form {
        flex: 1 1 0;
    }

    .user-card-actions-row .user-btn-add,
    .user-card-actions-row .user-btn-message,
    .user-card-actions-row .user-btn-secondary {
        flex: 1 1 0;
        text-align: center;
    }

    .user-action-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        padding: 10px 12px;
        border-radius: 8px;
        font-size: 0.9rem;
        font-weight: 600;
        line-height: 1.2;
        border: 1px solid transparent;
        cursor: pointer;
        text-decoration: none;
    }

    .user-action-btn--primary {
        background: var(--link-hover);
        color: var(--text-inverse);
    }

    .user-action-btn--primary:hover {
        background: color-mix(in srgb, var(--link-hover) 85%, #000 15%);
    }

    .user-action-btn--secondary {
        background: transparent;
        color: var(--text-muted);
        border-color: var(--border-soft);
    }

    .user-action-btn--secondary:hover {
        background: var(--surface-muted);
        color: var(--text-main);
    }

    .user-action-btn--message {
        padding: 12px 12px;
        font-size: 0.95rem;
    }

    .user-card-name {
        font-size: 1.45rem;
        font-weight: 700;
        line-height: 1.2;
        letter-spacing: 0.01em;
        margin-top: -4px;
    }
</style>

@extends('layouts.app')

@section('title', 'Users')

@push('styles')
    @vite('resources/css/pages/chat.css')
@endpush

@section('content')

<div class="min-h-screen py-10 px-6 users-page-wrapper">
    <h1 class="text-2xl font-bold mb-8 users-page-heading">All Users</h1>

    <form method="GET" action="{{ route('users.index') }}" class="users-filter-bar">
        <div class="users-filter-group">
            <label for="users-q">Search by name/email</label>
            <input id="users-q" type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Search users">
        </div>
        <div class="users-filter-group">
            <label for="users-sort">Sort</label>
            <select id="users-sort" name="sort">
                <option value="name-asc" @selected(($filters['sort'] ?? '') === 'name-asc')>Name A-Z</option>
                <option value="name-desc" @selected(($filters['sort'] ?? '') === 'name-desc')>Name Z-A</option>
                <option value="newest" @selected(($filters['sort'] ?? '') === 'newest')>Newest</option>
                <option value="oldest" @selected(($filters['sort'] ?? '') === 'oldest')>Oldest</option>
            </select>
        </div>
        <button type="submit" class="users-filter-apply">Apply</button>
        <a href="{{ route('users.index') }}" class="users-filter-reset">Reset</a>
    </form>

    @if(($users ?? collect())->isEmpty())
      <p class="text-sm user-card-email" style="text-align:center;">No users match your filters.</p>
    @endif

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach(($users ?? collect()) as $user)
        @php
            $friendshipState = $friendshipStates[$user->id] ?? 'none';
            $existingConversationId = $friendConversationIds[$user->id] ?? null;
        @endphp
            <div class="rounded-2xl shadow-md p-6 flex flex-col items-center text-center gap-2 user-card">
                
                <a href="{{ route('profile.show', ['username' => $user->name]) }}" class="user-avatar-link" aria-label="View {{ $user->name }} profile">
                <div class="profile-show-avatar">
    @if (!empty($user->profile_image_url))
        <img src="{{ asset(ltrim($user->profile_image_url, '/')) }}" alt="{{ $user->name }} avatar">
    @else
        {{ strtoupper(substr($user->name, 0, 1)) }}
    @endif
</div>
                </a>

                <div>
                    <p class="user-card-name">{{ $user->name }}</p>
                </div>

                <div class="user-card-actions">
                    <div class="user-card-actions-row">
                        @if($friendshipState === 'friends')
                            <form method="POST" action="{{ route('friends.unfriend', $user->id) }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="user-action-btn user-action-btn--primary">
                                    Unfriend
                                </button>
                            </form>
                        @elseif($friendshipState === 'outgoing_pending')
                            <form method="POST" action="{{ route('friends.request.cancel', $user->id) }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="user-action-btn user-action-btn--primary">
                                    Unsend Request
                                </button>
                            </form>
                        @elseif($friendshipState === 'incoming_pending')
                            <form method="POST" action="{{ route('friends.accept', $user->id) }}">
                                @csrf
                                <button type="submit" class="user-action-btn user-action-btn--primary">
                                    Accept Request
                                </button>
                            </form>
                        @else
                            <form method="POST" action="{{ route('friends.request', $user->id) }}">
                                @csrf
                                <button type="submit" class="user-action-btn user-action-btn--primary">
                                    Add Friend
                                </button>
                            </form>
                        @endif

                        <a href="{{ route('profile.show', ['username' => $user->name]) }}" class="user-action-btn user-action-btn--secondary">
                            View Profile
                        </a>
                    </div>

                    @if($friendshipState === 'friends')
                        <div class="user-card-actions-row">
                            @if($existingConversationId)
                                <a href="{{ route('chat.user', ['user' => auth()->id(), 'conversation' => $existingConversationId]) }}" class="user-action-btn user-action-btn--primary user-action-btn--message">
                                    Send Message
                                </a>
                            @else
                                <form method="POST" action="{{ route('conversations.start-user', $user->id) }}" style="width: 100%;">
                                    @csrf
                                    <button type="submit" class="user-action-btn user-action-btn--primary user-action-btn--message">
                                        Send Message
                                    </button>
                                </form>
                            @endif
                        </div>
                    @endif
                </div>

            </div>
        @endforeach
    </div>
</div>

@endsection