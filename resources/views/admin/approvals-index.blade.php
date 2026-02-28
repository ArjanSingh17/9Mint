<!DOCTYPE html>
<html>
<head>
    <title>Creator Approvals</title>
</head>
<body>
    <h1>View NFT Collections approvals ({{ ($pendingCollections ?? collect())->count() }})</h1>
    <p>Review creator submissions step by step before publishing to the public marketplace.</p>

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

    <p><a href="{{ route('admin.dashboard') }}">Back to dashboard</a></p>

    @if(($pendingCollections ?? collect())->isEmpty())
        <p>No pending collections.</p>
    @else
        <ul style="padding-left: 18px;">
            @foreach($pendingCollections as $collection)
                <li style="margin-bottom: 10px;">
                    <a href="{{ route('admin.approvals.show', $collection) }}">
                        {{ $collection->name }}
                    </a>
                    <small>
                        — Creator: {{ $collection->creator_name ?? 'Unknown' }},
                        NFTs: {{ $collection->nfts_count }},
                        Fee: {{ $collection->creation_fee_payment_state }}
                    </small>
                </li>
            @endforeach
        </ul>
    @endif
</body>
</html>
