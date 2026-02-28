<!DOCTYPE html>
<html>
<head>
    <title>Edit User</title>
    <style>
        .container { width: 50%; margin: 50px auto; padding: 20px; border: 1px solid #ddd; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input, select { width: 100%; padding: 8px; box-sizing: border-box; }
        button { padding: 10px 20px; background-color: blue; color: white; border: none; cursor: pointer; }
        .error { color: red; font-size: 0.9em; }
    </style>
</head>
<body>

<div class="container">
    <h2>Edit User: {{ $user->name }}</h2>

    <form action="{{ route('admin.users.update', $user->id) }}" method="POST">
        @csrf
        @method('PUT') <div class="form-group">
            <label>Name:</label>
            <input type="text" name="name" value="{{ old('name', $user->name) }}" required>
            @error('name') <div class="error">{{ $message }}</div> @enderror
        </div>

        <div class="form-group">
            <label>Email:</label>
            <input type="email" name="email" value="{{ old('email', $user->email) }}" required>
            @error('email') <div class="error">{{ $message }}</div> @enderror
        </div>

        <div class="form-group">
            <label>Role:</label>
            <select name="role">
                <option value="user" {{ in_array($user->role, ['user', 'customer'], true) ? 'selected' : '' }}>User</option>
                <option value="admin" {{ $user->role == 'admin' ? 'selected' : '' }}>Admin</option>
            </select>
            @error('role') <div class="error">{{ $message }}</div> @enderror
            <small>Only 9Mint superadmin can assign admin role.</small>
        </div>

        <button type="submit">Update User</button>
        <a href="{{ route('admin.users') }}" style="margin-left: 10px;">Cancel</a>
    </form>
</div>

</body>
</html>