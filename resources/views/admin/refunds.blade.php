<!DOCTYPE html>
<html>
<head>
    <title>Refund Requests</title>
    <style>
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; vertical-align: top; }
        th { background: #f4f4f4; }
        .muted { color: #666; font-size: 0.9em; }
        textarea { width: 100%; min-height: 90px; }
    </style>
</head>
<body>
    <h1>Refund Requests</h1>
    <p><a href="{{ route('admin.dashboard') }}">Back to Dashboard</a></p>

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

    @if(($items ?? collect())->isEmpty())
        <p class="muted">No refund/investigation records found.</p>
    @else
        <table>
            <thead>
                <tr>
                    <th>Order Item</th>
                    <th>NFT</th>
                    <th>Buyer</th>
                    <th>Seller</th>
                    <th>Status</th>
                    <th>Reason / Notes</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $item)
                    <tr>
                        <td>
                            #{{ $item->id }}<br>
                            <span class="muted">Order #{{ $item->order_id }}</span>
                        </td>
                        <td>{{ $item->token?->nft?->name ?? ('Token #' . $item->token_id) }}</td>
                        <td>{{ $item->order?->user?->name ?? 'Unknown' }}</td>
                        <td>{{ $item->listing?->seller?->name ?? 'Unknown' }}</td>
                        <td>
                            {{ $item->lifecycle_status }}<br>
                            @if($item->hold_extended_until)
                                <span class="muted">Hold until {{ optional($item->hold_extended_until)->format('Y-m-d H:i') }}</span>
                            @endif
                        </td>
                        <td>
                            <div><strong>Reason:</strong> {{ $item->refund_reason ?: 'N/A' }}</div>
                            <div><strong>Notes:</strong> {{ $item->refund_notes ?: 'N/A' }}</div>
                            @if($item->refund_denial_reason)
                                <div><strong>Denial:</strong> {{ $item->refund_denial_reason }}</div>
                            @endif
                        </td>
                        <td>
                            @if($item->lifecycle_status === 'refund_requested')
                                <form method="POST" action="{{ route('admin.refunds.approve', $item->id) }}" style="margin-bottom:8px;">
                                    @csrf
                                    <button type="submit">Approve refund</button>
                                </form>
                                <form method="POST" action="{{ route('admin.refunds.deny', $item->id) }}">
                                    @csrf
                                    <textarea name="reason" placeholder="Denial reason (required)" required></textarea>
                                    <button type="submit">Deny refund</button>
                                </form>
                            @else
                                <span class="muted">No pending actions.</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</body>
</html>
