@extends('layouts.app')

@section('title', 'Request Refund')

@push('styles')
  @vite('resources/css/pages/app-pages.css')
  <style>
    .refund-form {
      display: grid;
      gap: 12px;
    }

    .refund-form__field {
      display: grid;
      gap: 6px;
      text-align: left;
    }

    .refund-form__field input,
    .refund-form__field textarea {
      width: 100%;
      padding: 10px 12px;
      border-radius: 8px;
      border: 1px solid var(--border-input);
      background: var(--surface-input);
      color: var(--text-main);
      font-size: 0.95rem;
      box-sizing: border-box;
    }

    .refund-form__field textarea {
      min-height: 120px;
      resize: vertical;
    }

    .refund-form__submit {
      justify-self: start;
    }

    .refund-actions {
      margin-top: 12px;
      display: flex;
      gap: 8px;
      justify-content: flex-start;
    }
  </style>
@endpush

@section('content')
  <section class="orders-page">
    <h1 class="orders-title">Refund Request</h1>

    @if (session('error'))
      <div class="orders-status">{{ session('error') }}</div>
    @endif

    <div class="orders-card">
      <p><strong>Order:</strong> #{{ $order->id }}</p>
      <p><strong>Select NFTs to include in this refund request.</strong></p>
    </div>

    <div class="orders-card">
      <p class="orders-meta">Detailed notes improve approval chances.</p>
      <form method="POST" action="{{ route('orders.refund-request', $order->id) }}" class="refund-form">
        @csrf
        <table class="orders-items-table">
          <thead>
            <tr>
              <th>Select</th>
              <th>Preview</th>
              <th>NFT</th>
              <th>Price</th>
              <th>Lifecycle</th>
              <th>Tradable After</th>
            </tr>
          </thead>
          <tbody>
            @foreach($items as $item)
              @php
                $nft = $item->token?->nft;
                $releaseAt = $item->hold_extended_until ?? $item->hold_expires_at;
                $statusLabel = ucfirst(str_replace('_', ' ', (string) $item->lifecycle_status));
                $canSelect = in_array($item->id, $eligibleItemIds ?? [], true);
              @endphp
              <tr>
                <td>
                  <input type="checkbox" name="item_ids[]" value="{{ $item->id }}" @disabled(! $canSelect)>
                </td>
                <td>
                  @if($nft)
                    <img
                      src="{{ asset(ltrim($nft->thumbnail_url ?? $nft->image_url, '/')) }}"
                      alt="{{ $nft->name }}"
                      style="width:72px; height:72px; object-fit:cover; border-radius:8px; border:1px solid var(--border-soft);"
                    >
                  @else
                    <span class="orders-meta">No image</span>
                  @endif
                </td>
                <td>
                  {{ $nft?->name ?? ('Token #' . $item->token_id) }}
                  <div class="orders-meta">Order item #{{ $item->id }}</div>
                </td>
                <td>{{ strtoupper((string) ($item->pay_currency ?? 'GBP')) }} {{ number_format((float) ($item->pay_unit_amount ?? 0), 2) }}</td>
                <td>
                  {{ $statusLabel }}
                  @if(! $canSelect)
                    <div class="orders-meta">Not eligible for refund right now</div>
                  @endif
                </td>
                <td>{{ optional($releaseAt)->format('Y-m-d H:i') ?? 'N/A' }}</td>
              </tr>
            @endforeach
          </tbody>
        </table>
        <label class="refund-form__field">
          <span>Reason</span>
          <input type="text" name="reason" maxlength="120" required>
        </label>
        <label class="refund-form__field">
          <span>Notes / description</span>
          <textarea name="notes" rows="5" required></textarea>
        </label>
        <button type="submit" class="refund-form__submit nav-btn signin">Submit refund request</button>
      </form>
    </div>

    <div class="refund-actions">
      <a href="{{ route('orders.index') }}" class="nav-btn signout">Back to order history</a>
      <a href="/contactUs" class="nav-btn signout">Contact Us</a>
    </div>
  </section>
@endsection
