
<?php
namespace App\Http\Livewire\Chat;
 use App\Models\Conversation;
use App\Models\Message;
use Livewire\Component;


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
    // Find and update the message in loaded messages
    $messageIndex = $this->loadedMessages->search(function($msg) use ($messageId) {
        return $msg->id == $messageId;
    });
    
    if ($messageIndex !== false) {
        $this->loadedMessages[$messageIndex]->read_at = now();
        $this->loadedMessages[$messageIndex]->refresh(); // Reload from DB
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



    return redirect(route('chat.index'));

    
    
   }




   public function getListeners()
{
    $auth_id = auth()->id();

    return [
        'loadMore',
        "echo-private:users.{$auth_id},.Illuminate\\Notifications\\Events\\BroadcastNotificationCreated"
            => 'broadcastedNotifications',
        'refresh' => '$refresh',
    ];
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

            // Use dispatch to trigger the browser event for the receiver
            $this->dispatch('scroll-to-bottom');
        }
    }
}




    public function loadMore(): void
    {


        #increment 
        $this->paginate_var += 10;

        #call loadMessages()

        $this->loadMessages();


        #update the chat height 
        //$this->dispatchBrowserEvent('update-chat-height');
    }


    

   public function loadMessages()
{
    $userId = auth()->id();

    // Get total count of messages
    $count = Message::where('conversation_id', $this->selectedConversation->id)
        ->whereNull('sender_deleted_at')
        ->count();

    // Load messages
    $this->loadedMessages = Message::where('conversation_id', $this->selectedConversation->id)
        ->whereNull('sender_deleted_at')
        ->orderBy('created_at', 'asc')
        ->skip(max(0, $count - $this->paginate_var))
        ->take($this->paginate_var)
        ->get();

    // Detect new messages
    if ($count > $this->previousMessageCount) {
        $this->dispatch('scroll-to-bottom');
    }

    //  Update stored count after comparison
    $this->previousMessageCount = $count;

    // Mark unread messages as read
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
    
    // Force scroll to bottom
    $this->dispatch('scroll-to-bottom');
}

public function updatedLoadedMessages()
{
    // Automatically scroll to bottom when messages update
    $this->dispatch('scroll-to-bottom');
}

   public function mount()
{
    if (!auth()->check()) {
        return redirect('contactUs');
    }
    
    $this->selectedConversation = Conversation::findOrFail($this->query);
    
    $ticket = $this->selectedConversation->ticket;
    
    // Authorization check
    $isAdmin = auth()->user()->role === 'admin';
    $isTicketOwner = $ticket && $ticket->user_id === auth()->id();
    
    if (!$isAdmin && !$isTicketOwner) {
        abort(403, 'You do not have permission to view this ticket.');
    }
    
    // Mark messages as read and notify sender
    $unreadMessages = Message::where('conversation_id', $this->selectedConversation->id)
        ->where('sender_id', '!=', auth()->id())
        ->whereNull('read_at')
        ->get();
    
    foreach ($unreadMessages as $message) {
        $message->update(['read_at' => now()]);
        
        // Dispatch event to update sender's view
        $this->dispatch('messageRead', messageId: $message->id)->to('chat.index');
    }
        
    $this->loadMessages();
}

    public function save()
    {
        $this->validate([
            'title' => 'required|max:255',
            'content' => 'required',
        ]);
 
        dd($this->title, $this->content);
    }
};
?>
 <div class="w-full overflow-hidden">
<div class="bg-white ">
<div>
        <div class="w-full h-32 " style="background-color: #449388"></div>

        <div class="w-full px-4" style="margin-top: -128px;">
            <div class="py-6 h-screen ">
                <div class="border border-grey rounded shadow-lg h-full ">

                    <!-- Right -->
                    <div class="border flex flex-col h-full ">

                        <!-- Header -->
                        <div class="py-2 px-3 bg-grey-lighter flex flex-row justify-between items-center">
                            <div class="flex items-center">
                                <div>
                                    <img class="w-10 h-10 rounded-full" src="https://i.pinimg.com/474x/aa/dd/1a/aadd1a84088cfa777014394359482d9a.jpg?nii=t"/>
                                </div>
                                <div class="ml-4">
                                    <p class="text-grey-darkest">
                                        {{$this->selectedConversation->ticket->title}}
                                    </p>
                                    <p class="text-grey-darker text-xs mt-1">
                                        Administrator
                                    </p>
                                </div>
                            </div>

                            <div class="flex">
                                <div>
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24"><path fill="#263238" fill-opacity=".5" d="M15.9 14.3H15l-.3-.3c1-1.1 1.6-2.7 1.6-4.3 0-3.7-3-6.7-6.7-6.7S3 6 3 9.7s3 6.7 6.7 6.7c1.6 0 3.2-.6 4.3-1.6l.3.3v.8l5.1 5.1 1.5-1.5-5-5.2zm-6.2 0c-2.6 0-4.6-2.1-4.6-4.6s2.1-4.6 4.6-4.6 4.6 2.1 4.6 4.6-2 4.6-4.6 4.6z"></path></svg>
                                </div>
                                <div class="ml-6">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24"><path fill="#263238" fill-opacity=".5" d="M1.816 15.556v.002c0 1.502.584 2.912 1.646 3.972s2.472 1.647 3.974 1.647a5.58 5.58 0 0 0 3.972-1.645l9.547-9.548c.769-.768 1.147-1.767 1.058-2.817-.079-.968-.548-1.927-1.319-2.698-1.594-1.592-4.068-1.711-5.517-.262l-7.916 7.915c-.881.881-.792 2.25.214 3.261.959.958 2.423 1.053 3.263.215l5.511-5.512c.28-.28.267-.722.053-.936l-.244-.244c-.191-.191-.567-.349-.957.04l-5.506 5.506c-.18.18-.635.127-.976-.214-.098-.097-.576-.613-.213-.973l7.915-7.917c.818-.817 2.267-.699 3.23.262.5.501.802 1.1.849 1.685.051.573-.156 1.111-.589 1.543l-9.547 9.549a3.97 3.97 0 0 1-2.829 1.171 3.975 3.975 0 0 1-2.83-1.173 3.973 3.973 0 0 1-1.172-2.828c0-1.071.415-2.076 1.172-2.83l7.209-7.211c.157-.157.264-.579.028-.814L11.5 4.36a.572.572 0 0 0-.834.018l-7.205 7.207a5.577 5.577 0 0 0-1.645 3.971z"></path></svg>
                                </div>
                                <div class="ml-6">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24"><path fill="#263238" fill-opacity=".6" d="M12 7a2 2 0 1 0-.001-4.001A2 2 0 0 0 12 7zm0 2a2 2 0 1 0-.001 3.999A2 2 0 0 0 12 9zm0 6a2 2 0 1 0-.001 3.999A2 2 0 0 0 12 15z"></path></svg>
                                </div>
                            </div>
                        </div>

    <!-- Messages -->
<div class="flex-1 overflow-auto" 
     style="background-color: #DAD3CC" 
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

        <div class="flex justify-center mb-2">
            <div class="rounded py-2 px-4" style="background-color: #DDECF2">
                <p class="text-sm uppercase">
                     {{ \Carbon\Carbon::parse($this->selectedConversation->ticket->created_at)->format('jS F Y') }}
                </p>
            </div>
        </div>

        @foreach($loadedMessages as $message)
            @if($message->sender_id === auth()->id())
                {{-- My message (right side) --}}
                <div class="flex justify-end mb-2">
                    <div class="rounded py-2 px-3 max-w-[45%] break-words" style="background-color: #E2F7CB" >
                        <p class="text-sm mt-1">
                            {{$message->body}}
                        </p>
                        <p class="text-right text-xs text-grey-dark mt-1">
                            {{\Carbon\Carbon::parse($message->created_at)->format('g:i a') }}
                            @if($message->read_at)
                        <span class="text-blue-500">✓✓</span> {{-- Double check = read --}}
                    @else
                        <span class="text-gray-400">✓</span> {{-- Single check = sent --}}
                    @endif
                        </p>
                    </div>
                </div>
            @else
                {{-- Their message (left side) --}}
                <div class="flex mb-2">
                    <div class="rounded py-2 px-3 max-w-[45%] break-words" style="background-color: #F2F2F2">
                        <p class="text-sm text-emerald-600">
                           @if(auth()->user()->role === 'admin')
                            {{auth()->user()->name}}
                            @else 
                            Administrator
                            @endif
                        </p>
                        <p class="text-sm mt-1">
                            {{$message->body}}
                        </p>
                        <p class="text-right text-xs text-grey-dark mt-1">
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
                        <div class="bg-grey-lighter px-4 py-4 flex items-center">
                            <div class="flex-1 mx-4">
                                <input class="w-full border rounded px-2 py-2" type="text" id="body" wire:model="body" placeholder="Write your Message..." required />
                            </div>
                            <div>
                               <button type="submit"
              class="px-6 py-2.5 min-w-[170px] rounded-full cursor-pointer text-white text-sm tracking-wider font-medium border-0 outline-0 bg-blue-700 hover:bg-blue-800">Send</button>
            </form>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
<script>
    document.addEventListener('livewire:init', () => {
        const chatContainer = document.getElementById('chat-container');

        // Renamed to match your other methods for consistency
        const scrollToBottom = () => {
            if (chatContainer) {
                // Use a slight timeout to ensure the DOM has finished rendering 
                // the new message before we calculate height
                setTimeout(() => {
                    chatContainer.scrollTop = chatContainer.scrollHeight;
                }, 50);
            }
        };

        // Listen for the dispatch('scroll-to-bottom') from your PHP/Livewire
        Livewire.on('scroll-to-bottom', () => {
            scrollToBottom();
        });

        // Initial scroll when the page first loads
        scrollToBottom();
    });
</script>
</div>