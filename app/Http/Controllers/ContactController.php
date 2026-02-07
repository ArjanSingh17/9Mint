<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ticket;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Support\Facades\Auth;

class ContactController extends Controller
{
    /**
     * Get all tickets for the logged-in user
     */
    public function getTickets()
    {
        // Retrieve all tickets with their conversations and messages
        $tickets = Ticket::with('conversations.messages')
            ->where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();

        return view('contact', [
            'tickets' => $tickets,
        ]);
    }

    /**
     * Create a new ticket, conversation, and first message
     */
    public function createTicket(Request $request)
    {
        // Validate the form input
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string|max:5000',
        ]);

        $userId = Auth::id();

        //  Create a new ticket
        $ticket = Ticket::create([
            'user_id' => $userId,
            'title' => $data['title'],
            'status' => 'open',
        ]);

        //  Create a conversation for this ticket
        $conversation = Conversation::create([
            'ticket_id' => $ticket->id,
        ]);

        //  Create the first message linked to the conversation
        $message = Message::create([
            'ticket_id' => $ticket->id,
            'conversation_id' => $conversation->id,
            'user_id' => $userId,
            'content' => $data['message'],
        ]);

        
    }
}
