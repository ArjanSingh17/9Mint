@extends('layouts.app')

@section('title', 'Notifications')

@push('styles')
  @vite('resources/css/pages/app-pages.css')
@endpush

@section('content')
  <section class="orders-page">
    <h1 class="orders-title">Notification History</h1>

    @if (session('status'))
      <div class="orders-status">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('notifications.mark-all-read') }}" style="margin-bottom: 12px;">
      @csrf
      <button type="submit">Mark all as read</button>
    </form>

    @if ($notifications->isEmpty())
      <p class="orders-empty">No notifications yet.</p>
    @else
      <div class="orders-list">
        @foreach ($notifications as $notification)
          <div class="orders-card">
            <div class="orders-card-header">
              <div>
                <h2>{{ $notification->title }}</h2>
                <p class="orders-meta">{{ optional($notification->created_at)->format('Y-m-d H:i') }}</p>
              </div>
              <div class="orders-summary">
                @php
                  $requesterId = (int) ($notification->data['requester_id'] ?? 0);
                  $isFriendRequest = ($notification->type ?? '') === 'friend_request_received';
                  $friendRequestState = $isFriendRequest && $requesterId > 0
                    ? ($friendRequestStatusLookup[$requesterId] ?? null)
                    : null;

                  if ($friendRequestState === 'accepted') {
                    $statusLabel = 'Accepted';
                  } elseif ($friendRequestState === 'pending') {
                    $statusLabel = 'Pending';
                  } elseif ($isFriendRequest) {
                    $statusLabel = $notification->read_at ? 'Handled' : 'Unread';
                  } else {
                    $statusLabel = $notification->read_at ? 'Read' : 'Unread';
                  }

                  $canRespond = $isFriendRequest && $friendRequestState === 'pending';
                @endphp
                <p class="orders-meta">
                  Status: {{ $statusLabel }}
                </p>
              </div>
            </div>
            @if($notification->body)
              <p>{{ $notification->body }}</p>
            @endif

            @if($canRespond)
              <div style="margin-top: 10px; display: flex; gap: 10px;">
                <form method="POST" action="{{ route('friends.accept', $requesterId) }}">
                  @csrf
                  <button type="submit" class="nav-btn signin">Accept</button>
                </form>

                <form method="POST" action="{{ route('friends.decline', $requesterId) }}">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="nav-btn signout">Decline</button>
                </form>
              </div>
            @endif
          </div>
        @endforeach
      </div>

      <div style="margin-top: 10px;">
        {{ $notifications->links() }}
      </div>
    @endif
  </section>
@endsection
