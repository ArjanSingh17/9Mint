<aside class="profile-activity-panel" aria-label="Account links">
    <div class="profile-activity-links">
        <a href="{{ route('orders.index') }}" class="profile-activity-btn">
            Purchased NFTs (Order History)
        </a>
        <a href="{{ route('inventory.show', ['username' => Auth::user()->name]) }}" class="profile-activity-btn">
            My Inventory
        </a>
        <a href="{{ route('favourites.index') }}" class="profile-activity-btn">
            My Favourites
        </a>
        <a href="#" class="profile-activity-btn">
            Manage Reviews and Returns
        </a>
    </div>
</aside>