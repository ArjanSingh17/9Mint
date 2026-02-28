<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
</head>
<body>
    <h1>Welcome, Admin!</h1>
    <p>This area is secure.</p>

    @if(session('status'))
        <div style="padding:10px; background:#4caf50; color:white; margin-bottom:12px;">
            {{ session('status') }}
        </div>
    @endif
    @if(session('error'))
        <div style="padding:10px; background:#f44336; color:white; margin-bottom:12px;">
            {{ session('error') }}
        </div>
    @endif

    <ul>
        <li><a href="{{ route('admin.inventory') }}">Manage Inventory</a></li>
        <li><a href="{{ route('admin.users') }}">Manage Users</a></li>
        
       
        <li><a href="{{ route('admin.orders') }}">View Orders</a></li>
        <li><a href="tickets">View Tickets</a></li>
        <li><a href="{{ route('admin.approvals.index') }}">View NFT Collections approvals ({{ (int) ($pendingCollectionsCount ?? 0) }})</a></li>
    </ul>
</body>
</html>