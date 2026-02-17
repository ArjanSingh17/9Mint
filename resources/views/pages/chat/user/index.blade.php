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

    /** @var \Illuminate\Support\Collection */
    public $loadedMessages;

    public $paginate_var = 999;

    // INIT
    public function mount($user, $conversation)
    {
        if (!auth()->check()) {
            return redirect('contactUs');
        }

        $this->loadedMessages = collect();
        $this->selectedConversation = Conversation::findOrFail($conversation);

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

            // only process events for the open conversation
            if ($event['conversation_id'] != $this->selectedConversation->id) {
                return;
            }

            $newMessage = Message::find($event['message_id']);

            if (!$newMessage) {
                return;
            }

            $this->loadedMessages->push($newMessage);

            // important: do not mark as read here
            // read happens only in mount()

            $this->dispatch('scroll-to-bottom');
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

<!-- component -->
<div class="w-full overflow-hidden">
<div class="bg-white ">
    <div>
        <div class="w-full h-32" style="background-color: #449388"></div>

        <div class="container mx-auto" style="margin-top: -128px;">
            <div class="py-6 h-screen">
                <div class="flex border border-grey rounded shadow-lg h-full">

                    <!-- Left -->
                    <div class="w-1/3 border flex flex-col">

                        <!-- Header -->
                        <div class="py-2 px-3 bg-grey-lighter flex flex-row justify-between items-center">
                            <div>
                                <img class="w-10 h-10 rounded-full" src="http://andressantibanez.com/res/avatar.png"/>
                            </div>

                            <div class="flex">
                                <div>
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24"><path fill="#727A7E" d="M12 20.664a9.163 9.163 0 0 1-6.521-2.702.977.977 0 0 1 1.381-1.381 7.269 7.269 0 0 0 10.024.244.977.977 0 0 1 1.313 1.445A9.192 9.192 0 0 1 12 20.664zm7.965-6.112a.977.977 0 0 1-.944-1.229 7.26 7.26 0 0 0-4.8-8.804.977.977 0 0 1 .594-1.86 9.212 9.212 0 0 1 6.092 11.169.976.976 0 0 1-.942.724zm-16.025-.39a.977.977 0 0 1-.953-.769 9.21 9.21 0 0 1 6.626-10.86.975.975 0 1 1 .52 1.882l-.015.004a7.259 7.259 0 0 0-5.223 8.558.978.978 0 0 1-.955 1.185z"></path></svg>
                                </div>
                                <div class="ml-4">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24"><path opacity=".55" fill="#263238" d="M19.005 3.175H4.674C3.642 3.175 3 3.789 3 4.821V21.02l3.544-3.514h12.461c1.033 0 2.064-1.06 2.064-2.093V4.821c-.001-1.032-1.032-1.646-2.064-1.646zm-4.989 9.869H7.041V11.1h6.975v1.944zm3-4H7.041V7.1h9.975v1.944z"></path></svg>
                                </div>
                                <div class="ml-4">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24"><path fill="#263238" fill-opacity=".6" d="M12 7a2 2 0 1 0-.001-4.001A2 2 0 0 0 12 7zm0 2a2 2 0 1 0-.001 3.999A2 2 0 0 0 12 9zm0 6a2 2 0 1 0-.001 3.999A2 2 0 0 0 12 15z"></path></svg>
                                </div>
                            </div>
                        </div>

                        <!-- Search -->
                        <div class="py-2 px-2 bg-grey-lightest">
                            <input type="text" class="w-full px-2 py-2 text-sm" placeholder="Search or start new chat"/>
                        </div>

                        <!-- Contacts -->
                        <div class="bg-grey-lighter flex-1 overflow-auto">
                            <div class="px-3 flex items-center bg-grey-light cursor-pointer">
                                <div>
                                    <img class="h-12 w-12 rounded-full"
                                         src="https://darrenjameseeley.files.wordpress.com/2014/09/expendables3.jpeg"/>
                                </div>
                                <div class="ml-4 flex-1 border-b border-grey-lighter py-4">
                                    <div class="flex items-bottom justify-between">
                                        <p class="text-grey-darkest">
                                            New Movie! Expendables 4
                                        </p>
                                        <p class="text-xs text-grey-darkest">
                                            12:45 pm
                                        </p>
                                    </div>
                                    <p class="text-grey-dark mt-1 text-sm">
                                        Get Andrés on this movie ASAP!
                                    </p>
                                </div>
                            </div>
                            <div class="bg-white px-3 flex items-center hover:bg-grey-lighter cursor-pointer">
                                <div>
                                    <img class="h-12 w-12 rounded-full"
                                         src="https://www.biography.com/.image/t_share/MTE5NDg0MDU1MTIyMTE4MTU5/arnold-schwarzenegger-9476355-1-402.jpg"/>
                                </div>
                                <div class="ml-4 flex-1 border-b border-grey-lighter py-4">
                                    <div class="flex items-bottom justify-between">
                                        <p class="text-grey-darkest">
                                            Arnold Schwarzenegger
                                        </p>
                                        <p class="text-xs text-grey-darkest">
                                            12:45 pm
                                        </p>
                                    </div>
                                    <p class="text-grey-dark mt-1 text-sm">
                                        I'll be back
                                    </p>
                                </div>
                            </div>
                            <div class="bg-white px-3 flex items-center hover:bg-grey-lighter cursor-pointer">
                                <div>
                                    <img class="h-12 w-12 rounded-full"
                                         src="https://www.famousbirthdays.com/headshots/russell-crowe-6.jpg"/>
                                </div>
                                <div class="ml-4 flex-1 border-b border-grey-lighter py-4">
                                    <div class="flex items-bottom justify-between">
                                        <p class="text-grey-darkest">
                                            Russell Crowe
                                        </p>
                                        <p class="text-xs text-grey-darkest">
                                            12:45 pm
                                        </p>
                                    </div>
                                    <p class="text-grey-dark mt-1 text-sm">
                                        Hold the line!
                                    </p>
                                </div>
                            </div>
                            <div class="bg-white px-3 flex items-center hover:bg-grey-lighter cursor-pointer">
                                <div>
                                    <img class="h-12 w-12 rounded-full"
                                         src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQGpYTzuO0zLW7yadaq4jpOz2SbsX90okb24Z9GtEvK6Z9x2zS5"/>
                                </div>
                                <div class="ml-4 flex-1 border-b border-grey-lighter py-4">
                                    <div class="flex items-bottom justify-between">
                                        <p class="text-grey-darkest">
                                            Tom Cruise
                                        </p>
                                        <p class="text-xs text-grey-darkest">
                                            12:45 pm
                                        </p>
                                    </div>
                                    <p class="text-grey-dark mt-1 text-sm">
                                        Show me the money!
                                    </p>
                                </div>
                            </div>
                            <div class="bg-white px-3 flex items-center hover:bg-grey-lighter cursor-pointer">
                                <div>
                                    <img class="h-12 w-12 rounded-full"
                                         src="https://www.biography.com/.image/t_share/MTE5NTU2MzE2MjE4MTY0NzQ3/harrison-ford-9298701-1-sized.jpg"/>
                                </div>
                                <div class="ml-4 flex-1 border-b border-grey-lighter py-4">
                                    <div class="flex items-bottom justify-between">
                                        <p class="text-grey-darkest">
                                            Harrison Ford
                                        </p>
                                        <p class="text-xs text-grey-darkest">
                                            12:45 pm
                                        </p>
                                    </div>
                                    <p class="text-grey-dark mt-1 text-sm">
                                        Tell Java I have the money
                                    </p>
                                </div>
                            </div>
                        </div>

                    </div>


                    <!-- Right -->
                    <div class="w-2/3 border flex flex-col">

                        <!-- Header -->
                         <div class="py-2 px-3 bg-grey-lighter flex flex-row justify-between items-center">
                            <div class="flex items-center">
                                <div>
                                    @if(auth()->user()->role === 'admin')
                                   <img class="w-10 h-10 rounded-full" src="https://images.macrumors.com/t/n4CqVR2eujJL-GkUPhv1oao_PmI=/1600x/article-new/2019/04/guest-user-250x250.jpg"/>
                                        @else 
                                         <img class="w-10 h-10 rounded-full" src="https://images.macrumors.com/t/n4CqVR2eujJL-GkUPhv1oao_PmI=/1600x/article-new/2019/04/guest-user-250x250.jpg"/>
                                @endif
                            </div>
                                <div class="ml-4">
                                   <p class="text-grey-darker text-xs mt-1 pb-2.5">
                                     {{ $selectedConversation->sender_id === auth()->id()
                                     ? $selectedConversation->receiver->name
                                    : $selectedConversation->sender->name }}
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

        {{-- Date banner --}}
        <div class="flex justify-center mb-2">
            <div class="rounded py-2 px-4" style="background-color: #DDECF2">
                <p class="text-sm uppercase">
                    {{ \Carbon\Carbon::parse(optional($this->selectedConversation->ticket)->created_at)->format('jS F Y') }}
                </p>
            </div>
        </div>

        @foreach($loadedMessages as $message)

            {{-- My message --}}
            @if($message->sender_id === auth()->id())
                <div class="flex justify-end mb-2">
                    <div class="rounded py-2 px-3 max-w-[45%] break-words"
                         style="background-color: #E2F7CB">

                        <p class="text-sm mt-1">
                            {{ $message->body }}
                        </p>

                        <p class="text-right text-xs text-grey-dark mt-1">
                            {{ \Carbon\Carbon::parse($message->created_at)->format('g:i a') }}

                            @if($message->read_at)
                                <span class="text-blue-500">✓✓</span>
                            @else
                                <span class="text-gray-400">✓</span>
                            @endif
                        </p>
                    </div>
                </div>

            {{-- Their message --}}
            @else
                <div class="flex mb-2">
                    <div class="rounded py-2 px-3 max-w-[45%] break-words"
                         style="background-color: #F2F2F2">

                        <p class="text-sm text-emerald-600">
                            {{ $selectedConversation->sender_id === auth()->id()
                                     ? $selectedConversation->receiver->name
                                    : $selectedConversation->sender->name }}
                        </p>

                        <p class="text-sm mt-1">
                            {{ $message->body }}
                        </p>

                        <p class="text-right text-xs text-grey-dark mt-1">
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
</div>