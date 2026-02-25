<!DOCTYPE html>
<html>
<head>
    <title>Manage Inventory</title>
    <style>
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 10px; border: 1px solid #ddd; text-align: left; }
        .badge { padding: 5px; border-radius: 4px; font-size: 0.8em; background: #eee; }
    </style>
</head>
<body>
    <h1>NFT Inventory Management</h1>
    
    <a href="{{ route('admin.dashboard') }}">Back to Dashboard</a>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Image</th>
                <th>Name</th>
                <th>Collection</th>
                <th>Stock (Remaining/Total)</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($nfts as $nft)
                <tr>
                    <td>{{ $nft->id }}</td>
                    <td><img src="{{ $nft->image_url }}" width="50" alt="nft"></td>
                    <td>{{ $nft->name }}</td>
                    <td>{{ $nft->collection->name ?? 'N/A' }}</td>
                    <td>{{ $nft->editions_remaining }} / {{ $nft->editions_total }}</td>
                    <td>
                        <span class="badge">{{ $nft->is_active ? 'Active' : 'Inactive' }}</span>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>