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
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($users as $user)
                <tr>
                    <td>{{ $user->id }}</td>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td>{{ $user->role }}</td>
                    <td>
                        <a href="{{ route('admin.users.edit', $user->id) }}"
                            style="background-color: orange; color: white; padding: 5px 10px; text-decoration: none; margin-right: 5px;">
                            Edit
                        </a>

                        <form action="{{ route('admin.users.delete', $user->id) }}" method="POST"
                            onsubmit="return confirm('Are you sure?');" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-delete">Delete</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <br>
    <a href="{{ route('admin.dashboard') }}">Back to Dashboard</a>
</body>

</html>