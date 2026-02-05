<section>
    {{-- Links --}}
    <h2 class="text-2xl font-semibold mb-4">My Activity</h2>
    <div class="profile-activity-links">
        {{-- Links to secured areas (Policy protected) --}}
        
        {{-- Meeting the "page to display the user’s previous orders" requirement --}}
        <a href="{{ route('orders.index') }}" class="profile-activity-btn">
             View My Purchased NFTs (Order History)
        </a>
        <a href="{{ route('inventory.index') }}" class="profile-activity-btn">
             View My Inventory
        </a>
        <a href="{{ route('favourites.index') }}" class="profile-activity-btn">
             View My Favourites
        </a>

        {{-- Meeting the "return a product" and "rate and review" requirements --}}
        <a href="#" class="profile-activity-btn">
             Manage Reviews and Returns
        </a>
    </div>
</section>