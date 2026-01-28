@extends('layouts.app')

@section('title', 'My Orders')

@push('styles')
  <link rel="stylesheet" href="{{ asset('css/App.css') }}">
@endpush

@section('content')
  {{-- Header --}}
  <div class="orders-page">
    <h1 class="orders-title">My Purchased NFTs (Order History)</h1>

    {{-- Status --}}
    @if (session('status'))
      <div class="orders-status">
        {{ session('status') }}
      </div>
    @endif

    {{-- Empty --}}
    @if ($orders->isEmpty())
      <p class="orders-empty">You have not placed any orders yet.</p>
    @else
      {{-- List --}}
      <div class="orders-list">
        @foreach ($orders as $order)
          <div class="orders-card">
            <div class="orders-card-header">
              <div>
                <h2>Order #{{ $order->id }}</h2>
                <p class="orders-meta">
                  Placed: {{ optional($order->placed_at ?? $order->created_at)->format('Y-m-d H:i') }}
                </p>
              </div>
              <div class="orders-summary">
                <p class="orders-total">
                  Total: £{{ number_format($order->total_gbp, 2) }}
                </p>
                <p class="orders-meta">
                  Status: {{ $order->status }}
                </p>
              </div>
            </div>

            {{-- Items --}}
            @if ($order->items->isNotEmpty())
              <table class="orders-items-table">
                <thead>
                  <tr>
                    <th>NFT</th>
                    <th>Quantity</th>
                    <th>Price</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach ($order->items as $item)
                    <tr>
                      <td>
                        {{ optional($item->nft)->name ?? 'NFT #'.$item->nft_id }}
                      </td>
                      <td>
                        {{ $item->quantity }}
                      </td>
                      <td>
                        £{{ number_format($item->unit_price_gbp, 2) }}
                      </td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            @else
              <p class="orders-meta">No items recorded for this order.</p>
            @endif
          </div>
        @endforeach
      </div>
    @endif
  </div>
@endsection
