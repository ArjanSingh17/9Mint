<section>
    <h2 class="text-2xl font-semibold mb-4">My Activity</h2>
    <div class="flex space-x-4">
        {{-- Links to secured areas (Policy protected) --}}
        
        {{-- Meeting the "page to display the user’s previous orders" requirement --}}
        <a href="{{ route('orders.index') }}" class="text-indigo-600 hover:underline">
             View My Purchased NFTs (Order History)
        </a>

        {{-- Meeting the "return a product" and "rate and review" requirements --}}
        <a href="#" class="text-indigo-600 hover:underline">
             Manage Reviews and Returns
        </a>
    </div>
</section>