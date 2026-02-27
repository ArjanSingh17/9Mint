<?php
$users = auth()->user()->getOtherUsers();


?>

@extends('layouts.app')

@section('title', 'Users')

@push('styles')
    @vite('resources/css/pages/chat.css')
@endpush

@section('content')

<div class="min-h-screen py-10 px-6 users-page-wrapper">
    <h1 class="text-2xl font-bold mb-8 users-page-heading">All Users</h1>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($users as $user)
        <?php 
        $existingConversation = \App\Models\Conversation::where('type', 'user')
        ->where(function ($q) use ($user) {
            $q->where('sender_id', auth()->id())->where('receiver_id', $user->id);
        })
        ->orWhere(function ($q) use ($user) {
            $q->where('sender_id', $user->id)->where('receiver_id', auth()->id());
        })
        ->first();
        ?>
            <div class="rounded-2xl shadow-md p-6 flex flex-col items-center text-center gap-3 user-card">
                
                <img class="h-16 w-16 rounded-full object-cover flex-shrink-0"
                     src="https://images.macrumors.com/t/n4CqVR2eujJL-GkUPhv1oao_PmI=/1600x/article-new/2019/04/guest-user-250x250.jpg"
                     alt="{{ $user->name }}"/>

                <div>
                    <p class="font-semibold text-lg user-card-name">{{ $user->name }}</p>
                    <p class="text-sm user-card-email">{{ $user->email }}</p>
                </div>

                @if($existingConversation)
                    <a href="{{ route('chat.enter', $user->id) }}">
                        <button type="button" class="font-bold py-2 px-4 rounded-full cursor-pointer user-btn-message">
                            Send Message
                        </button>
                    </a>
                @else
                    <form method="POST" action="{{ route('chat.start', $user->id) }}">
                        @csrf
                        <button type="submit" class="font-bold py-2 px-4 rounded-full cursor-pointer user-btn-add">
                            Add Friend
                        </button>
                    </form>
                @endif

            </div>
        @endforeach
    </div>
</div>

@endsection