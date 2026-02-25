<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
</head>
<body>
    <h1>Welcome, Admin!</h1>
    <p>This area is secure.</p>

    <ul>
        <li><a href="{{ route('admin.inventory') }}">Manage Inventory</a></li>
        <li><a href="{{ route('admin.users') }}">Manage Users</a></li>
        
       
        <li><a href="#">View Orders (Coming Soon)</a></li>
        <li><a href="tickets">View Tickets</a></li>
    </ul>
</body>
</html>