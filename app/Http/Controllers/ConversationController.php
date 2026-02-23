<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Conversation;
use App\Models\Listing;
use App\Models\User;

class ConversationController extends Controller
{

public function start(Listing $listing)
{
    $buyer = auth()->user();
    $sellerId = $listing->seller_user_id;

    // Prevent messaging yourself
    if ($buyer->id === $sellerId) {
        abort(403);
    }

    // Look for existing conversation (either direction)
    $conversation = Conversation::where('type', 'user')
        ->whereNull('ticket_id')
        ->where(function ($query) use ($buyer, $sellerId) {
            $query->where(function ($q) use ($buyer, $sellerId) {
                $q->where('sender_id', $buyer->id)
                  ->where('receiver_id', $sellerId);
            })->orWhere(function ($q) use ($buyer, $sellerId) {
                $q->where('sender_id', $sellerId)
                  ->where('receiver_id', $buyer->id);
            });
        })
        ->first();

    // If not found, create one
    if (! $conversation) {
        $conversation = Conversation::create([
            'type'        => 'user',
            'sender_id'   => $buyer->id,
            'receiver_id' => $sellerId,
            'ticket_id'   => null,
        ]);
    }

$loggedInUserId = auth()->id(); // or auth()->user()->id

return redirect()->route('chat.user', [
    'user' => $loggedInUserId,
    'conversation' => $conversation->id,
]);



}
public function startConversation($receiverId)
{
    $senderId = auth()->id();

    $existing = \App\Models\Conversation::where('type', 'user')
        ->where(function ($q) use ($senderId, $receiverId) {
            $q->where('sender_id', $senderId)->where('receiver_id', $receiverId);
        })
        ->orWhere(function ($q) use ($senderId, $receiverId) {
            $q->where('sender_id', $receiverId)->where('receiver_id', $senderId);
        })
        ->first();

public function startWithUser(User $user)
{
    $buyer = auth()->user();
    $sellerId = $user->id;

    // Prevent messaging yourself
    if ($buyer->id === $sellerId) {
        abort(403);
    }

    // Look for existing conversation (either direction)
    $conversation = Conversation::where('type', 'user')
        ->whereNull('ticket_id')
        ->where(function ($query) use ($buyer, $sellerId) {
            $query->where(function ($q) use ($buyer, $sellerId) {
                $q->where('sender_id', $buyer->id)
                  ->where('receiver_id', $sellerId);
            })->orWhere(function ($q) use ($buyer, $sellerId) {
                $q->where('sender_id', $sellerId)
                  ->where('receiver_id', $buyer->id);
            });
        })
        ->first();

    if (! $conversation) {
        $conversation = Conversation::create([
            'type'        => 'user',
            'sender_id'   => $buyer->id,
            'receiver_id' => $sellerId,
            'ticket_id'   => null,
        ]);
    }

    return redirect()->route('chat.user', [
        'user' => auth()->id(),
        'conversation' => $conversation->id,
    ]);
}

}
