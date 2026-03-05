<!DOCTYPE html>
<html>
<head>
    <title>Review Collection Submission</title>
</head>
<body>
    <h1>Review Collection Submission</h1>

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

    <p>
        <a href="{{ route('admin.approvals.index') }}">Back to approvals list</a>
        |
        <a href="{{ route('collections.show', ['slug' => $collection->slug]) }}" target="_blank" rel="noopener noreferrer">
            Open collection preview page
        </a>
    </p>

    <hr>
    <h2>Collection Information</h2>
    <ul>
        <li><strong>Name:</strong> {{ $collection->name }}</li>
        <li><strong>Slug:</strong> {{ $collection->slug }}</li>
        <li><strong>Creator:</strong> {{ $collection->creator_name ?? 'Unknown' }}</li>
        <li><strong>Submitted by user ID:</strong> {{ $collection->submitted_by_user_id ?? 'N/A' }}</li>
        <li><strong>Status:</strong> {{ $collection->approval_status }}</li>
        <li><strong>Visibility:</strong> {{ $collection->is_public ? 'Public' : 'Hidden (awaiting approval)' }}</li>
        <li><strong>Creation fee state:</strong> {{ $collection->creation_fee_payment_state }}</li>
        <li><strong>Refund state:</strong> {{ $collection->creation_fee_refund_state }}</li>
        <li><strong>Creation fee amount (GBP):</strong> {{ number_format((float) ($collection->creation_fee_amount_gbp ?? 80), 2) }}</li>
        <li><strong>Reference payment provider:</strong> {{ $collection->creation_fee_provider ?? 'N/A' }}</li>
        <li><strong>Submitted at:</strong> {{ optional($collection->created_at)->toDateTimeString() }}</li>
        <li><strong>Description:</strong> {{ $collection->description ?: 'N/A' }}</li>
    </ul>

    <h3>Collection Cover</h3>
    @if($collection->cover_image_url)
        <img src="{{ asset(ltrim($collection->cover_image_url, '/')) }}" alt="{{ $collection->name }} cover" style="max-width: 240px; max-height: 320px; object-fit: contain; border:1px solid #ccc;">
    @else
        <p>No cover uploaded.</p>
    @endif

    <hr>
    <h2>NFT Review (Step 2)</h2>
    <p>
        NFTs are reviewed under this collection. You approve/reject the entire submission in Step 3.
        If any NFT fails moderation, reject the whole collection.
    </p>

    @if($collection->nfts->isEmpty())
        <p>No NFTs found in this submission.</p>
    @else
        @foreach($collection->nfts as $nft)
            <div style="border:1px solid #ccc; border-radius:8px; padding:10px; margin-bottom:10px;">
                <h3 style="margin:0 0 8px;">{{ $nft->name }}</h3>
                <p style="margin:0 0 8px;">
                    <strong>Slug:</strong> {{ $nft->slug }}
                    |
                    <strong>Status:</strong> {{ $nft->approval_status }}
                    |
                    <strong>Reference Price:</strong>
                    {{ strtoupper((string) ($nft->primary_ref_currency ?? 'GBP')) }}
                    {{ number_format((float) ($nft->primary_ref_amount ?? 0), 2) }}
                    |
                    <strong>Editions:</strong> {{ $nft->editions_total }}
                </p>
                <p style="margin:0 0 8px;"><strong>Description:</strong> {{ $nft->description ?: 'N/A' }}</p>
                @if($nft->image_url)
                    <img src="{{ asset(ltrim($nft->image_url, '/')) }}" alt="{{ $nft->name }}" style="max-width: 220px; max-height: 300px; object-fit: contain; border:1px solid #ccc;">
                @endif
                <p style="margin-top:8px;">
                    <a href="{{ route('nfts.show', ['slug' => $nft->slug]) }}" target="_blank" rel="noopener noreferrer">
                        Open NFT preview page
                    </a>
                </p>
            </div>
        @endforeach
    @endif

    <hr>
    <h2>Step 3: Final Submission Decision</h2>
    <p>
        Approve publishes the full collection and all NFTs.
        Reject removes the full submission (collection + NFTs + uploaded folder).
    </p>

    <form method="POST" action="{{ route('admin.collections.approve', $collection) }}" style="display:inline-block; margin-right: 10px;">
        @csrf
        <button type="submit">Approve Full Submission</button>
    </form>

    <form method="POST" action="{{ route('admin.collections.reject', $collection) }}" style="display:inline-block;">
        @csrf
        <input type="text" name="reason" placeholder="Rejection reason (optional)" style="min-width: 300px;">
        <button type="submit">Reject Full Submission</button>
    </form>
</body>
</html>
