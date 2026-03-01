<!DOCTYPE html>
<html>
<head>
    <title>Global Transactions</title>
    <style>
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background: #f4f4f4; }
        .muted { color: #666; font-size: 0.9em; }
        .ids-block { font-size: 12px; color: #666; line-height: 1.4; }
    </style>
</head>
<body>
    <h1>Global Transactions</h1>
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

    @if(($transactions ?? collect())->isEmpty())
        <p class="muted">No transactions found.</p>
    @else
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Type</th>
                    <th>Reference</th>
                    <th>Item</th>
                    <th>Buyer</th>
                    <th>Seller</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>IDs</th>
                </tr>
            </thead>
            <tbody>
                @foreach($transactions as $tx)
                    <tr>
                        <td>{{ optional($tx['occurred_at'] ?? null)->format('Y-m-d H:i') ?? 'N/A' }}</td>
                        <td>{{ $tx['type'] ?? 'Transaction' }}</td>
                        <td>{{ $tx['reference'] ?? 'N/A' }}</td>
                        <td>{{ $tx['item'] ?? 'N/A' }}</td>
                        <td>{{ $tx['buyer'] ?? 'N/A' }}</td>
                        <td>{{ $tx['seller'] ?? 'N/A' }}</td>
                        <td>{{ $tx['amount_label'] ?? 'N/A' }}</td>
                        <td>{{ $tx['status'] ?? 'N/A' }}</td>
                        <td class="ids-block">
                            @foreach(($tx['ids'] ?? []) as $key => $value)
                                @if(!is_null($value) && $value !== '')
                                    <div>{{ strtoupper((string) $key) }}: {{ $value }}</div>
                                @endif
                            @endforeach
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <p class="muted">
            Includes NFT sales, collection creation fee payments, and wallet-based creator-fee refunds.
            Manual off-platform refunds are not currently recorded in DB transaction rows.
        </p>
    @endif
</body>
</html>
