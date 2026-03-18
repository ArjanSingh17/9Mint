@extends('layouts.app')

@section('title', 'Chat')

@push('styles')
    @vite('resources/css/pages/chat.css')
@endpush

@section('content')
    <div class="w-full overflow-hidden">
        <div class="chat-page-container">
            <div class="w-full h-32 chat-header-band"></div>

            <div class="container mx-auto px-4" style="margin-top: -96px;">
                <div class="rounded shadow-lg border chat-main p-8 text-center">
                    <h1 class="text-2xl font-bold mb-3 chat-contact-name">Your chat inbox</h1>
                    <p class="mb-6" style="color: var(--subtext-color);">
                        No conversations yet. When you become friends with someone, your messages will appear here.
                    </p>
                    <a href="{{ route('users.index') }}" class="nav-btn signin">Browse users</a>
                </div>
            </div>
        </div>
    </div>
@endsection
