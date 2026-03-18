<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Friendship;
use App\Models\Listing;
use App\Models\User;
use Illuminate\Http\Request;

class ConversationController extends Controller
{
    public function inbox()
    {
        $userId = (int) auth()->id();

        $firstConversation = Conversation::query()
            ->where('type', 'user')
            ->whereNull('ticket_id')
            ->where(function ($q) use ($userId) {
                $q->where('sender_id', $userId)
                    ->orWhere('receiver_id', $userId);
            })
            ->latest('updated_at')
            ->first();

        if ($firstConversation) {
            return redirect()->route('chat.user', [
                'user' => $userId,
                'conversation' => $firstConversation->id,
            ]);
        }

        return view('chat.empty');
    }

    public function start(Listing $listing)
    {
        $senderId = (int) auth()->id();
        $receiverId = (int) $listing->seller_user_id;

        if ($senderId === $receiverId) {
            abort(403);
        }

        if (! Friendship::areFriends($senderId, $receiverId)) {
            return back()->with('error', 'You can only message users after becoming friends.');
        }

        $conversation = Conversation::query()
            ->where('type', 'user')
            ->whereNull('ticket_id')
            ->where(function ($q) use ($senderId, $receiverId) {
                $q->where(function ($sub) use ($senderId, $receiverId) {
                    $sub->where('sender_id', $senderId)
                        ->where('receiver_id', $receiverId);
                })->orWhere(function ($sub) use ($senderId, $receiverId) {
                    $sub->where('sender_id', $receiverId)
                        ->where('receiver_id', $senderId);
                });
            })
            ->first();

        if (! $conversation) {
            $conversation = Conversation::create([
                'type' => 'user',
                'sender_id' => $senderId,
                'receiver_id' => $receiverId,
                'ticket_id' => null,
            ]);
        }

        return redirect()->route('chat.user', [
            'user' => $senderId,
            'conversation' => $conversation->id,
        ]);
    }

    public function startConversation($receiverId)
    {
        $senderId = (int) auth()->id();
        $receiverId = (int) $receiverId;

        if ($senderId === $receiverId) {
            abort(403);
        }

        if (! Friendship::areFriends($senderId, $receiverId)) {
            return back()->with('error', 'You can only message users after becoming friends.');
        }

        $conversation = Conversation::query()
            ->where('type', 'user')
            ->whereNull('ticket_id')
            ->where(function ($q) use ($senderId, $receiverId) {
                $q->where(function ($sub) use ($senderId, $receiverId) {
                    $sub->where('sender_id', $senderId)
                        ->where('receiver_id', $receiverId);
                })->orWhere(function ($sub) use ($senderId, $receiverId) {
                    $sub->where('sender_id', $receiverId)
                        ->where('receiver_id', $senderId);
                });
            })
            ->first();

        if (! $conversation) {
            $conversation = Conversation::create([
                'type' => 'user',
                'sender_id' => $senderId,
                'receiver_id' => $receiverId,
                'ticket_id' => null,
            ]);
        }

        return back()->with('success', 'Conversation started.');
    }

    public function startWithUser(User $user)
    {
        $senderId = (int) auth()->id();
        $receiverId = (int) $user->id;

        if ($senderId === $receiverId) {
            abort(403);
        }

        if (! Friendship::areFriends($senderId, $receiverId)) {
            return back()->with('error', 'You can only message users after becoming friends.');
        }

        $conversation = Conversation::query()
            ->where('type', 'user')
            ->whereNull('ticket_id')
            ->where(function ($q) use ($senderId, $receiverId) {
                $q->where(function ($sub) use ($senderId, $receiverId) {
                    $sub->where('sender_id', $senderId)
                        ->where('receiver_id', $receiverId);
                })->orWhere(function ($sub) use ($senderId, $receiverId) {
                    $sub->where('sender_id', $receiverId)
                        ->where('receiver_id', $senderId);
                });
            })
            ->first();

        if (! $conversation) {
            $conversation = Conversation::create([
                'type' => 'user',
                'sender_id' => $senderId,
                'receiver_id' => $receiverId,
                'ticket_id' => null,
            ]);
        }

        return redirect()->route('chat.user', [
            'user' => $senderId,
            'conversation' => $conversation->id,
        ]);
    }

    public function enterConversation($receiverId)
    {
        $senderId = (int) auth()->id();
        $receiverId = (int) $receiverId;

        if (! Friendship::areFriends($senderId, $receiverId)) {
            return redirect()->route('users.index')->with('error', 'You can only message users after becoming friends.');
        }

        $conversation = Conversation::query()
            ->where('type', 'user')
            ->where(function ($q) use ($senderId, $receiverId) {
                $q->where(function ($sub) use ($senderId, $receiverId) {
                    $sub->where('sender_id', $senderId)
                        ->where('receiver_id', $receiverId);
                })->orWhere(function ($sub) use ($senderId, $receiverId) {
                    $sub->where('sender_id', $receiverId)
                        ->where('receiver_id', $senderId);
                });
            })
            ->first();

        if (! $conversation) {
            abort(404, 'No conversation found.');
        }

        return redirect()->to("chat/user/{$senderId}/{$conversation->id}");
    }
}
