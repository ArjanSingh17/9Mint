
   <?php
 
use Livewire\Component;
use App\Models\User;
use App\Models\Ticket;
use App\Models\Message;
use App\Models\Conversation;
 
new class extends Component {
    public string $title = '';
    public string $message = ''; 
    public int $userId = 1;

    public $tickets = [];

    public function mount()
    {
        $this->loadTickets();
    }

public function loadTickets()
{
    $this->tickets = Ticket::where('status', '!=', 'closed')
        ->latest()
        ->get();
}


   public function deleteTicket(int $ticketId)
{
    // Build query
    $query = Ticket::where('id', $ticketId);
    
  
    if (auth()->user()->role !== 'admin') {
        $query->where('user_id', auth()->id());
    }
    
    $ticket = $query->firstOrFail();

  
    $ticket->update(['status' => 'closed']);

    // Refresh list
    $this->loadTickets();
}


 public function openTicket(int $ticketId)
{
   
    $query = Ticket::where('id', $ticketId);
    
   
    if (auth()->user()->role !== 'admin') {
        $query->where('user_id', auth()->id());
    }
    
    $ticket = $query->firstOrFail();

  
    if (auth()->user()->role === 'admin' && $ticket->status === 'open') {
        $ticket->update(['status' => 'pending']);
    }

    $conversation = Conversation::where('ticket_id', $ticket->id)->first();

    if (!$conversation) {
        $conversation = Conversation::create([
            'ticket_id' => $ticket->id,
            'sender_id' => $ticket->user_id,
        ]);
    }

    $this->redirect(route('chat.ticket', ['query' => $conversation->id]));
}

 public function render()
{
    return $this->view();
}
};
?>


<div class="flex-grow">
        <h1>Tickets</h1> 
        <div class="bg-black border-white rounded border-10 h-screen">
          <div class="overflow-x-auto m-5">
            <table class="min-w-full bg-white">
              <thead class="bg-gray-100 whitespace-nowrap">
                <tr>
                  <th class="p-4 text-left text-[13px] font-semibold text-slate-900">Title</th>
                  <th class="p-4 text-left text-[13px] font-semibold text-slate-900">Status</th>
                  <th class="p-4 text-left text-[13px] font-semibold text-slate-900">Created At</th>
                  <th class="p-4 text-left text-[13px] font-semibold text-slate-900">Unread Messages</th>
                  <th class="p-4 text-left text-[13px] font-semibold text-slate-900">Actions</th>
                </tr>
              </thead>

              <tbody class="whitespace-nowrap">
                @forelse ($tickets as $ticket)
                  <tr class="hover:bg-gray-50">
                    <td class="p-4 text-[15px] text-slate-900 font-medium">
                        {{ $ticket->title }}
                    </td>

                    <td class="p-4 text-[15px] text-slate-600 font-medium">
                        {{ $ticket->status}}
                    </td>

                    <td class="p-4 text-[15px] text-slate-600 font-medium">
                        {{ $ticket->created_at->format('Y-m-d') }}
                    </td>
                    <td class="p-4 text-[15px] text-slate-600 font-medium">
                       <span wire:poll.5s>
                       {{ $ticket->conversations->sum(fn ($conversation) => $conversation->unreadMessagesCount()) }}
                      </span>
                    </td>
                    <td class="p-4">
                        <div class="flex items-center justify-around">
                            <button class="mr-3 cursor-pointer" title="Edit" wire:click="openTicket({{ $ticket->id }})">
                              <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 fill-blue-500 hover:fill-blue-700" viewBox="0 0 348.882 348.882">
                                <path d="m333.988 11.758-.42-.383A43.363 43.363 0 0 0 304.258 0a43.579 43.579 0 0 0-32.104 14.153L116.803 184.231a14.993 14.993 0 0 0-3.154 5.37l-18.267 54.762c-2.112 6.331-1.052 13.333 2.835 18.729 3.918 5.438 10.23 8.685 16.886 8.685h.001c2.879 0 5.693-.592 8.362-1.76l52.89-23.138a14.985 14.985 0 0 0 5.063-3.626L336.771 73.176c16.166-17.697 14.919-45.247-2.783-61.418zM130.381 234.247l10.719-32.134.904-.99 20.316 18.556-.904.99-31.035 13.578zm184.24-181.304L182.553 197.53l-20.316-18.556L294.305 34.386c2.583-2.828 6.118-4.386 9.954-4.386 3.365 0 6.588 1.252 9.082 3.53l.419.383c5.484 5.009 5.87 13.546.861 19.03z" data-original="#000000" />
                                <path d="M303.85 138.388c-8.284 0-15 6.716-15 15v127.347c0 21.034-17.113 38.147-38.147 38.147H68.904c-21.035 0-38.147-17.113-38.147-38.147V100.413c0-21.034 17.113-38.147 38.147-38.147h131.587c8.284 0 15-6.716 15-15s-6.716-15-15-15H68.904C31.327 32.266.757 62.837.757 100.413v180.321c0 37.576 30.571 68.147 68.147 68.147h181.798c37.576 0 68.147-30.571 68.147-68.147V153.388c.001-8.284-6.715-15-14.999-15z" data-original="#000000" />
                              </svg>
                            </button>
                            <button type="button"
          class="px-6 py-2.5 rounded-lg cursor-pointer text-white text-sm tracking-wider font-medium border-0 outline-0 outline-none bg-red-700 hover:bg-red-800 active:bg-red-700"  wire:click="deleteTicket({{ $ticket->id }})" wire:confirm="Are you sure you want to close this ticket?">Close</button>
                        </div>
                    </td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="4" class="p-4 text-center text-gray-500">
                        No tickets yet
                    </td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>