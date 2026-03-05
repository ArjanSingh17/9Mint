@auth
    @php
    $chatUser = auth()->user();

    $firstConversation = \App\Models\Conversation::where('type', 'user')
        ->where(function ($q) use ($chatUser) {
            $q->where('sender_id', $chatUser->id)
              ->orWhere('receiver_id', $chatUser->id);
        })->first();
@endphp
    <a
        href="{{ $firstConversation
            ? route('chat.user', ['user' => $chatUser->id, 'conversation' => $firstConversation->id])
            : url('/users') }}"
        class="friend-fab"
        aria-label="Open chat"
        title="Open chat"
    >
        <span aria-hidden="true">💬</span>
    </a>
@endauth
