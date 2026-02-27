<?php
namespace App\Http\Livewire\Chat;

use Livewire\Component;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use App\Events\MessageSent;

new class extends Component {

    public string $title = '';
    public string $content = '';

    protected $listeners = [
        'refreshMessages' => '$refresh',
        'messageRead' => 'handleMessageRead'
    ];

    public $query;
    public $selectedConversation;

    public int $previousMessageCount = 0;
    public $body;
    public $userConversations;

    /** @var \Illuminate\Support\Collection */
    public $loadedMessages;

    public $paginate_var = 999;


    public function selectConversation($conversationId)
{
    $userId = auth()->id();
    $conversation = Conversation::findOrFail($conversationId);

    if ($conversation->sender_id !== $userId && $conversation->receiver_id !== $userId) {
        abort(403);
    }

    $this->selectedConversation = $conversation;
    $this->loadedMessages = collect();
    $this->markConversationAsRead();
    $this->loadMessages();
    $this->dispatch('scroll-to-bottom');
    $this->dispatch('update-url', url: "/chat/user/{$userId}/{$conversationId}");
}

    public function getUserConversations()
{
    $userId = auth()->id();

    return Conversation::where('type', 'user')
        ->where(function ($q) use ($userId) {
            $q->where('sender_id', $userId)
              ->orWhere('receiver_id', $userId);
        })
        ->latest()
        ->get();
}

    // INIT
    public function mount($user, $conversation)
    {
        if (!auth()->check()) {
            return redirect('contactUs');
        }

        $this->loadedMessages = collect();
        $this->selectedConversation = Conversation::where('id', $conversation)
    ->where('type', 'user')
    ->firstOrFail();

        $userId = auth()->id();

        // permission check
        if (
            $this->selectedConversation->sender_id !== $userId &&
            $this->selectedConversation->receiver_id !== $userId
        ) {
            abort(403, 'You do not have permission to view this conversation.');
        }

        // only mark as read when the chat is actually opened
        $this->markConversationAsRead();
        $this->userConversations = $this->getUserConversations();
        $this->loadMessages();
    }

    // HANDLE READ EVENT
    public function handleMessageRead($messageId)
    {
        $messageIndex = $this->loadedMessages->search(
            fn($msg) => $msg->id == $messageId
        );

        if ($messageIndex !== false) {
            $this->loadedMessages[$messageIndex]->read_at = now();
        }
    }

    // SAFE MARK AS READ
    public function markConversationAsRead()
    {
        Message::where('conversation_id', $this->selectedConversation->id)
            ->where('sender_id', '!=', auth()->id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    // DELETE CONVERSATION PER USER
    public function deleteByUser($id)
    {
        $userId = auth()->id();
        $conversation = Conversation::findOrFail(decrypt($id));

        $conversation->messages()->each(function ($message) use ($userId) {
            if ($message->sender_id === $userId) {
                $message->update(['sender_deleted_at' => now()]);
            } elseif ($message->receiver_id === $userId) {
                $message->update(['receiver_deleted_at' => now()]);
            }
        });

        $receiverAlsoDeleted = $conversation->messages()
            ->where(function ($query) use ($userId) {
                $query->where('sender_id', $userId)
                      ->orWhere('receiver_id', $userId);
            })
            ->where(function ($query) {
                $query->whereNull('sender_deleted_at')
                      ->orWhereNull('receiver_deleted_at');
            })
            ->doesntExist();

        if ($receiverAlsoDeleted) {
            $conversation->forceDelete();
        }

        return redirect(route('chat.ticket.index'));
    }

    // REALTIME BROADCAST HANDLER
     public function broadcastedNotifications($event)
{
    if ($event['type'] == MessageSent::class) {
        if ($event['conversation_id'] == $this->selectedConversation->id) {
            $newMessage = Message::find($event['message_id']);

            $this->loadedMessages->push($newMessage);

            $newMessage->read_at = now();
            $newMessage->save();

            $this->selectedConversation->getReceiver()
                ->notify(new MessageRead($this->selectedConversation->id));

            $this->dispatch('scroll-to-bottom');
        }
    }
}

    // LOAD MESSAGES
    public function loadMessages()
    {
        $userId = auth()->id();
        $conversationId = $this->selectedConversation->id;

        $baseQuery = Message::where('conversation_id', $conversationId)
            ->where(function ($query) use ($userId) {
                $query
                    ->where(function ($q) use ($userId) {
                        $q->where('sender_id', $userId)
                          ->whereNull('sender_deleted_at');
                    })
                    ->orWhere(function ($q) use ($userId) {
                        $q->where('receiver_id', $userId)
                          ->whereNull('receiver_deleted_at');
                    });
            });

        $count = $baseQuery->count();

        $this->loadedMessages = $baseQuery
            ->orderBy('created_at', 'asc')
            ->skip(max(0, $count - $this->paginate_var))
            ->take($this->paginate_var)
            ->get();

        if ($count > $this->previousMessageCount) {
            $this->dispatch('scroll-to-bottom');
        }

        $this->previousMessageCount = $count;

        return $this->loadedMessages;
    }

    // SEND MESSAGE
    public function sendMessage()
    {
        $this->validate([
            'body' => 'required|string'
        ]);

        $receiverId =
            $this->selectedConversation->sender_id === auth()->id()
                ? $this->selectedConversation->receiver_id
                : $this->selectedConversation->sender_id;

        $createdMessage = Message::create([
            'conversation_id' => $this->selectedConversation->id,
            'sender_id' => auth()->id(),
            'receiver_id' => $receiverId,
            'body' => $this->body,
            
        ]);

        $this->reset('body');

        $this->loadedMessages->push($createdMessage);

        $this->selectedConversation->update([
            'updated_at' => now()
        ]);

        $this->dispatch('scroll-to-bottom');
    }

    // AUTO SCROLL
    public function updatedLoadedMessages()
    {
        $this->dispatch('scroll-to-bottom');
    }

    // helper
    public function getUserNameById(int $id)
    {
        return User::where('id', $id)->value('name');
    }
};
?>

@push('styles')
    @vite('resources/css/pages/chat.css')
@endpush

<!-- component -->
<div class="w-full overflow-hidden">
<div class="chat-page-container bg-white ">
    <div>
        <div class="w-full h-32 chat-header-band"></div>

        <div class="container mx-auto" style="margin-top: -128px;">
            <div class="py-6 h-screen">
                <div class="flex border-grey rounded shadow-lg h-full">

                    <!-- Left -->
                    <div class="w-1/3 border flex flex-col chat-sidebar">

                        <!-- Header -->
                        <div class="py-2 px-3 flex flex-row justify-between items-center chat-sidebar-header">
                            <div class="flex items-center gap-2">
        <img class="w-10 h-10 rounded-full"
             src="https://images.macrumors.com/t/n4CqVR2eujJL-GkUPhv1oao_PmI=/1600x/article-new/2019/04/guest-user-250x250.jpg"/>
        <span class="chat-sidebar-username">{{ auth()->user()->name }}</span>
    </div>
                          
                            <a class="flex cursor-pointer p-2 rounded-2xl transition-colors duration-200 chat-btn-add" href="/users">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-6">
  <path fill-rule="evenodd" d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25ZM12.75 9a.75.75 0 0 0-1.5 0v2.25H9a.75.75 0 0 0 0 1.5h2.25V15a.75.75 0 0 0 1.5 0v-2.25H15a.75.75 0 0 0 0-1.5h-2.25V9Z" clip-rule="evenodd" />
</svg> Add Friends

</a>
                        </div>

                        <!-- Search -->
                        <div class="py-2 px-2 chat-sidebar-search">
                            <input type="text" class="w-full px-2 py-2 text-sm" placeholder="Search for a chat"/>
                        </div>

                        <!-- Contacts -->
                        <div class="flex-1 overflow-auto chat-sidebar-contacts">
                            @foreach($userConversations as $conversation)
                            <div class="px-3 flex items-center cursor-pointer rounded-3xl mr-1 ml-1 mb-1 mt-1 chat-conversation-item {{ $selectedConversation->id === $conversation->id ? 'active' : '' }}"
     wire:click="selectConversation({{ $conversation->id }})"
     wire:key="conversation-{{ $conversation->id }}" x-data
     @update-url.window="history.pushState({}, '', $event.detail.url)">
                                <div class="flex-shrink-0 h-12 w-12">
                                    <img class="h-12 w-12 rounded-full object-cover flex-shrink-0"
                                         src="https://images.macrumors.com/t/n4CqVR2eujJL-GkUPhv1oao_PmI=/1600x/article-new/2019/04/guest-user-250x250.jpg"/>
                                </div>
                                <div class="ml-4 flex-1  py-4">
                                    <div class="flex items-bottom justify-between">
                                        <p class="conversation-name">
                                           {{ $conversation->sender_id === auth()->id()
                                             ? $conversation->receiver->name
                                             : $conversation->sender->name }}
                                        </p>
                                        <p class="text-xs conversation-time">
                                            {{ $conversation->lastMessage?->created_at?->format('g:i a') }}
                                        </p>
                                    </div>
                                    <p class="mt-1 text-sm conversation-preview">
                                       {{ \Illuminate\Support\Str::limit($conversation->lastMessage?->body, 30) }}
                                    </p>
                                </div>
                            </div> 
                            @endforeach
                        </div>

                    </div>


                    <!-- Right -->
                    <div class="w-2/3 border flex flex-col chat-main">

                        <!-- Header -->
                         <div class="py-2 px-3 flex flex-row justify-between items-center chat-main-header">
                            <div class="flex items-center">
                                <div>
                                    @if(auth()->user()->role === 'admin')
                                   <img class="w-10 h-10 rounded-full" src="https://images.macrumors.com/t/n4CqVR2eujJL-GkUPhv1oao_PmI=/1600x/article-new/2019/04/guest-user-250x250.jpg"/>
                                        @else 
                                         <img class="w-10 h-10 rounded-full" src="https://images.macrumors.com/t/n4CqVR2eujJL-GkUPhv1oao_PmI=/1600x/article-new/2019/04/guest-user-250x250.jpg"/>
                                @endif
                            </div>
                                <div class="ml-4">
                                   <p class="text-xs mt-1 pb-2.5 chat-contact-name">
                                     {{ $selectedConversation->sender_id === auth()->id()
                                     ? $selectedConversation->receiver->name
                                    : $selectedConversation->sender->name }}
                                    </p>
                                   
                                    
                                </div>
                            </div> 
                           

                            <div class="flex">
                                <div class="ml-6">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24"><path fill="#263238" fill-opacity=".6" d="M12 7a2 2 0 1 0-.001-4.001A2 2 0 0 0 12 7zm0 2a2 2 0 1 0-.001 3.999A2 2 0 0 0 12 9zm0 6a2 2 0 1 0-.001 3.999A2 2 0 0 0 12 15z"></path></svg>
                                </div>
                            </div>
                        </div>

                        <!-- Messages -->
<div class="flex-1 overflow-auto chat-messages-area"
     wire:poll.0.1s="loadMessages"
     id="chat-container"
     x-data="{
         scrollToBottom() {
             this.$nextTick(() => {
                 this.$el.scrollTop = this.$el.scrollHeight;
             });
         }
     }"
     x-init="scrollToBottom(); $watch('$wire.loadedMessages', () => scrollToBottom())"
     @scroll-to-bottom.window="scrollToBottom()">

    <div class="py-2 px-3">

        {{-- Date banner --}}
        <div class="flex justify-center mb-2">
            <div class="rounded py-2 px-4 chat-date-banner">
                <p class="text-sm uppercase">
                    {{ \Carbon\Carbon::parse(optional($this->selectedConversation->ticket)->created_at)->format('jS F Y') }}
                </p>
            </div>
        </div>

        @foreach($loadedMessages as $message)

            {{-- My message --}}
            @if($message->sender_id === auth()->id())
                <div class="flex justify-end mb-2">
                    <div class="rounded py-2 px-3 max-w-[45%] break-words chat-bubble-out">

                        <p class="text-sm mt-1">
                            {{ $message->body }}
                        </p>

                        <p class="text-right text-xs mt-1 bubble-time">
                            {{ \Carbon\Carbon::parse($message->created_at)->format('g:i a') }}

                            @if($message->read_at)
                                <span class="chat-tick-read">✓✓</span>
                            @else
                                <span class="chat-tick-sent">✓</span>
                            @endif
                        </p>
                    </div>
                </div>

            {{-- Their message --}}
            @else
                <div class="flex mb-2">
                    <div class="rounded py-2 px-3 max-w-[45%] break-words chat-bubble-in">

                        <p class="text-sm bubble-sender-name">
                            {{ $selectedConversation->sender_id === auth()->id()
                                     ? $selectedConversation->receiver->name
                                    : $selectedConversation->sender->name }}
                        </p>

                        <p class="text-sm mt-1">
                            {{ $message->body }}
                        </p>

                        <p class="text-right text-xs mt-1 bubble-time">
                            {{ \Carbon\Carbon::parse($message->created_at)->format('g:i a') }}
                        </p>

                    </div>
                </div>
            @endif

        @endforeach

    </div>
</div>

                      <!-- Input -->
                        <form wire:submit.prevent="sendMessage">
                            @csrf
                        <div class="px-4 py-4 flex items-center chat-input-area">
                            <div class="flex-1 mx-4">
                              <input class="w-full border rounded px-2 py-2" type="text" id="body" wire:model="body" placeholder="Write your Message..." required />
                            </div>
                            <div>
                               <button type="submit"
              class="px-6 py-2.5 min-w-[170px] rounded-full cursor-pointer text-sm tracking-wider font-medium border-0 outline-0 chat-btn-send">Send</button>
            </form>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
</div>