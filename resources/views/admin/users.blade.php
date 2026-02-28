<!DOCTYPE html>
<html>

<head>
    <title>User Management</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }

        .btn-delete {
            background-color: red;
            color: white;
            border: none;
            padding: 5px 10px;
            cursor: pointer;
        }
    </style>
</head>

<body>
    <h1>User Management</h1>
    @php $isSuperAdmin = auth()->check() && auth()->user()->isSuperAdmin(); @endphp

    @if(session('success'))
        <div style="color: green; margin-bottom: 10px;">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div style="color: red; margin-bottom: 10px;">{{ session('error') }}</div>
    @endif

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($users as $user)
                <tr>
                    <td>{{ $user->id }}</td>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td>{{ $user->role === 'customer' ? 'user' : $user->role }}</td>
                    <td>{{ $user->banned_at ? 'Banned' : 'Active' }}</td>
                    <td>
                        <a href="{{ route('admin.users.edit', $user->id) }}"
                            style="background-color: orange; color: white; padding: 5px 10px; text-decoration: none; margin-right: 5px;">
                            Edit
                        </a>
                        @if($user->name === '9Mint')
                            <span style="color:#666;">Protected superadmin</span>
                        @elseif($user->banned_at)
                            @if($isSuperAdmin)
                                <form action="{{ route('admin.users.unban', $user->id) }}" method="POST" style="display:inline;">
                                    @csrf
                                    <button type="submit" style="background-color: #15803d; color: white; border: none; padding: 5px 10px; cursor: pointer; margin-right: 6px;">Unban</button>
                                </form>
                                <form action="{{ route('admin.users.delete', $user->id) }}" method="POST" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <input
                                        type="text"
                                        name="confirm_username"
                                        placeholder="Type {{ $user->name }}"
                                        style="padding: 5px; width: 150px;"
                                        required
                                    >
                                    <button type="submit" class="btn-delete">Delete</button>
                                </form>
                            @else
                                <span style="color:#666;">Only superadmin can unban or delete.</span>
                            @endif
                        @else
                            <form action="{{ route('admin.users.ban', $user->id) }}" method="POST" style="display:inline;">
                                @csrf
                                <button type="submit" style="background-color: #b45309; color: white; border: none; padding: 5px 10px; cursor: pointer;">Ban</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <br>
    <a href="{{ route('admin.dashboard') }}">Back to Dashboard</a>
</body>

</html>