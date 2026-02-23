@auth
    @php
        $chatUser = auth()->user();
        $firstConversation = $chatUser->conversations()->first();
        $conversationId = $firstConversation?->id ?? 0;
    @endphp
    <a
        href="{{ route('chat.user', ['user' => $chatUser->id, 'conversation' => $conversationId]) }}"
        class="friend-fab"
        aria-label="Open chat"
        title="Open chat"
    >
        <span aria-hidden="true">💬</span>
    </a>
@endauth
