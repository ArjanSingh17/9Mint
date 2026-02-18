<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Conversation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'type',
        'sender_id',
        'receiver_id',
        'ticket_id'
    ];

    protected $casts = [
        'type' => 'string',
    ];

    // Relationships
    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    
    public function isTicketConversation()
    {
        return $this->type === 'ticket';
    }

    public function isUserConversation()
    {
        return $this->type === 'user';
    }

    public function getReceiver()
    {
        if ($this->sender_id === auth()->id()) {
            return User::firstWhere('id', $this->receiver_id);
        } else {
            return User::firstWhere('id', $this->sender_id);
        }
    }

    public function scopeWhereNotDeleted($query) 
    {
        $userId = auth()->id();

        return $query->where(function ($query) use ($userId) {
            $query->whereHas('messages', function($query) use($userId) {
                $query->where(function ($query) use($userId) {
                    $query->where('sender_id', $userId)
                        ->whereNull('sender_deleted_at');
                })->orWhere(function ($query) use ($userId) {
                    $query->where('receiver_id', $userId)
                        ->whereNull('receiver_deleted_at');
                });
            })
            ->orWhereDoesntHave('messages');
        });
    }

    public function isLastMessageReadByUser(): bool 
    {
        $user = auth()->user();
        $lastMessage = $this->messages()->latest()->first();
        
        if ($lastMessage) {
            return $lastMessage->read_at !== null && $lastMessage->sender_id == $user->id;
        }
        
        return false;
    }

    public function unreadMessagesCount(): int 
    {
        return Message::where('conversation_id', $this->id)
            ->whereNull('read_at')
            ->count();
    }
}