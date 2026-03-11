<style>
 .profile-show-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: var(--link-hover);
        color: #fff;
        font-size: 19px;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-top: 10px;
        margin-bottom: 10px;
    }

    .profile-show-avatar img {
        width: 100%;
        height: 100%;
        border-radius: 50%;
        object-fit: cover;
        display: block;
    }
</style>

<?php
namespace App\Http\Livewire\Chat;
 use App\Models\Conversation;
use App\Models\Message;
use Livewire\Component;
use App\Models\User;


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
    public $loadedMessages = [];

    public $paginate_var = 999;

    public function handleMessageRead($messageId)
{
    $messageIndex = $this->loadedMessages->search(function($msg) use ($messageId) {
        return $msg->id == $messageId;
    });
    
    if ($messageIndex !== false) {
        $this->loadedMessages[$messageIndex]->read_at = now();
        $this->loadedMessages[$messageIndex]->refresh(); 
    }
}
    

public function markAsRead()
{
    Message::where('conversation_id', $this->selectedConversation->id)
        ->whereNull('read_at')
        ->update(['read_at' => now()]);
}

   public function deleteByUser($id) {

    $userId= auth()->id();
    $conversation= Conversation::find(decrypt($id));

    $conversation->messages()->each(function($message) use($userId){

        if($message->sender_id===$userId){

            $message->update(['sender_deleted_at'=>now()]);
        }
        elseif($message->receiver_id===$userId){

            $message->update(['receiver_deleted_at'=>now()]);
        }


    } );


    $receiverAlsoDeleted =$conversation->messages()
            ->where(function ($query) use($userId){

                $query->where('sender_id',$userId)
                      ->orWhere('receiver_id',$userId);
                   
            })->where(function ($query) use($userId){

                $query->whereNull('sender_deleted_at')
                        ->orWhereNull('receiver_deleted_at');

            })->doesntExist();



    if ($receiverAlsoDeleted) {

        $conversation->forceDelete();
        
    }

    return redirect(route('chat.ticket.index'));
   }

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

            $this->js("document.getElementById('chat-container').scrollTop = document.getElementById('chat-container').scrollHeight");
        }
    }
}


   public function loadMessages()
{
    $userId = auth()->id();

    $count = Message::where('conversation_id', $this->selectedConversation->id)
        ->whereNull('sender_deleted_at')
        ->count();

    $this->loadedMessages = Message::where('conversation_id', $this->selectedConversation->id)
        ->whereNull('sender_deleted_at')
        ->orderBy('created_at', 'asc')
        ->skip(max(0, $count - $this->paginate_var))
        ->take($this->paginate_var)
        ->get();

    if ($count > $this->previousMessageCount) {
        $this->js("document.getElementById('chat-container').scrollTop = document.getElementById('chat-container').scrollHeight");
    }

    $this->previousMessageCount = $count;

    Message::where('conversation_id', $this->selectedConversation->id)
        ->where('sender_id', '!=', $userId)
        ->whereNull('read_at')
        ->update(['read_at' => now()]);

    return $this->loadedMessages;
}


   public function sendMessage()
{
    $this->validate(['body' => 'required|string']);

    $createdMessage = Message::create([
        'conversation_id' => $this->selectedConversation->id,
        'sender_id' => auth()->id(),
        'body' => $this->body
    ]);

    $this->reset('body');
    $this->loadedMessages->push($createdMessage);
    
    $this->selectedConversation->updated_at = now();
    $this->selectedConversation->save();
    
    $this->js("document.getElementById('chat-container').scrollTop = document.getElementById('chat-container').scrollHeight");
}

public function updatedLoadedMessages()
{
    $this->js("document.getElementById('chat-container').scrollTop = document.getElementById('chat-container').scrollHeight");
}

   public function mount()
{
    if (!auth()->check()) {
        return redirect('contactUs');
    }
    
    $this->selectedConversation = Conversation::findOrFail($this->query);
    
    $ticket = $this->selectedConversation->ticket;
    
    $isAdmin = auth()->user()->role === 'admin';
    $isTicketOwner = $ticket && $ticket->user_id === auth()->id();
    
    if (!$isAdmin && !$isTicketOwner) {
        abort(403, 'You do not have permission to view this ticket.');
    }
    
    $unreadMessages = Message::where('conversation_id', $this->selectedConversation->id)
        ->where('sender_id', '!=', auth()->id())
        ->whereNull('read_at')
        ->get();
    
    foreach ($unreadMessages as $message) {
        $message->update(['read_at' => now()]);
    }
        
    $this->loadMessages();
}

public function getUserNameById(int $id)
{
    return User::where('id', $id)->value('name');
}   

};
?>
 @push('styles')
    @vite('resources/css/pages/chat.css')
@endpush

<div class="w-full overflow-hidden">
<div class="chat-page-container bg-white ">
<div>
        <div class="w-full h-32 chat-header-band"></div>

        <div class="w-full px-4 chat-page-container" style="margin-top: -128px;">
            <div class="py-6 h-screen ">
                <div class="rounded shadow-lg h-full ">

                    <!-- Right -->
                    <div class="border flex flex-col h-full chat-main">

                        <!-- Header -->
                       <div class="py-2 px-3 flex items-center relative chat-main-header">
                            <div class="flex items-center">
                                <div>
                                    @if(auth()->user()->role === 'admin')
                   <div class="profile-show-avatar">
    @php
        $otherUser = $selectedConversation->sender;
    @endphp

    @if (!empty($otherUser?->profile_image_url))
        <img src="{{ asset(ltrim($otherUser->profile_image_url, '/')) }}" alt="{{ $otherUser->name }} avatar">
    @else
        {{ strtoupper(substr($otherUser?->name ?? '?', 0, 1)) }}
    @endif
</div>
                                        @else 
                                         <img class="w-10 h-10 rounded-full" src="https://i.pinimg.com/474x/aa/dd/1a/aadd1a84088cfa777014394359482d9a.jpg?nii=t"/>
                                @endif
                            </div>
                                <div class="ml-4">
                                    <p class="text-xs mt-1 pb-2.5 chat-contact-name">
                                         @if(auth()->user()->role === 'admin')
                                        {{$this->getUserNameById($this->selectedConversation->sender_id)}}
                                        @else 
                                        Administrator
                                        @endif
                                    </p>
                                </div>
                            </div> 
                            <p class="absolute left-1/2 -translate-x-1/2 pt-2.5 chat-contact-name">
                                       Title: {{$this->selectedConversation->ticket->title}}
                                    </p>

                            
                                <div class="ml-6 absolute right-1 -translate-x-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24"><path fill="#263238" fill-opacity=".6" d="M12 7a2 2 0 1 0-.001-4.001A2 2 0 0 0 12 7zm0 2a2 2 0 1 0-.001 3.999A2 2 0 0 0 12 9zm0 6a2 2 0 1 0-.001 3.999A2 2 0 0 0 12 15z"></path></svg>
                                </div>
                            
                        </div>
    
<!-- Messages -->
<div class="flex-1 overflow-auto chat-messages-area"
     wire:poll.2s="loadMessages"
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

        <div class="flex justify-center mb-2">
            <div class="rounded py-2 px-4 chat-date-banner">
                <p class="text-sm uppercase">
                     {{ \Carbon\Carbon::parse($this->selectedConversation->ticket->created_at)->format('jS F Y') }}
                </p>
            </div>
        </div>

        @foreach($loadedMessages as $message)
            @if($message->sender_id === auth()->id())
                <div class="flex justify-end mb-2">
                    <div class="rounded py-2 px-3 max-w-[45%] break-words chat-bubble-out">
                        <p class="text-sm mt-1">
                            {{$message->body}}
                        </p>
                        <p class="text-right text-xs mt-1 bubble-time">
                            {{\Carbon\Carbon::parse($message->created_at)->format('g:i a') }}
                            @if($message->read_at)
                        <span class="chat-tick-read">✓✓</span>
                    @else
                        <span class="chat-tick-sent">✓</span>
                    @endif
                        </p>
                    </div>
                </div>
            @else
                <div class="flex mb-2">
                    <div class="rounded py-2 px-3 max-w-[45%] break-words chat-bubble-in">
                        <p class="text-sm bubble-sender-name">
                           @if(auth()->user()->role === 'admin')
                             {{$this->getUserNameById($this->selectedConversation->sender_id)}}
                            @else 
                            Administrator
                            @endif
                        </p>
                        <p class="text-sm mt-1">
                            {{$message->body}}
                        </p>
                        <p class="text-right text-xs mt-1 bubble-time">
                            {{\Carbon\Carbon::parse($message->created_at)->format('g:i a') }}
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