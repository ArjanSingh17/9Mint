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
                  @php
                    $orderCurrency = $order->pay_currency ?? 'GBP';
                    $orderSymbol = $currencySymbols[$orderCurrency] ?? null;
                  @endphp
                  Total: {{ $orderSymbol ? $orderSymbol . number_format($order->pay_total_amount ?? 0, 2) : number_format($order->pay_total_amount ?? 0, 2) . ' ' . $orderCurrency }}
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
                    @php
                      $nft = $item->listing?->token?->nft;
                      $itemCurrency = $item->pay_currency ?? ($order->pay_currency ?? 'GBP');
                      $itemSymbol = $currencySymbols[$itemCurrency] ?? null;
                    @endphp
                    <tr>
                      <td>
                        {{ $nft?->name ?? 'Listing #'.$item->listing_id }}
                      </td>
                      <td>
                        {{ $item->quantity }}
                      </td>
                      <td>
                        {{ $itemSymbol ? $itemSymbol . number_format($item->pay_unit_amount ?? 0, 2) : number_format($item->pay_unit_amount ?? 0, 2) . ' ' . $itemCurrency }}
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

    {{-- Sales --}}
    <div class="orders-sales">
      <h2 class="orders-title">My Sold NFTs</h2>
      @if (empty($sales) || $sales->isEmpty())
        <p class="orders-empty">You have not sold any NFTs yet.</p>
      @else
        <div class="orders-list">
          @foreach ($sales as $sale)
            @php
              $nft = $sale->listing?->token?->nft;
              $currency = $sale->pay_currency ?? 'GBP';
              $currencySymbol = $currencySymbols[$currency] ?? null;
              $gross = (float) ($sale->pay_amount ?? 0);
              $net = $gross * 0.975;
            @endphp
            <div class="orders-card">
              <div class="orders-card-header">
                <div>
                  <h2>Sale #{{ $sale->id }}</h2>
                  <p class="orders-meta">
                    Sold: {{ optional($sale->sold_at)->format('Y-m-d H:i') }}
                  </p>
                </div>
                <div class="orders-summary">
                  <p class="orders-total">
                    Gross: {{ $currencySymbol ? $currencySymbol . number_format($gross, 2) : number_format($gross, 2) . ' ' . $currency }}
                  </p>
                  <p class="orders-meta">
                    Status: Sold
                  </p>
                </div>
              </div>

              <table class="orders-items-table">
                <thead>
                  <tr>
                    <th>NFT</th>
                    <th>Listing</th>
                    <th>Net (after 2.5% fee)</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td>{{ $nft?->name ?? 'Listing #'.$sale->listing_id }}</td>
                    <td>#{{ $sale->listing_id }}</td>
                    <td>{{ $currencySymbol ? $currencySymbol . number_format($net, 2) : number_format($net, 2) . ' ' . $currency }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          @endforeach
        </div>
      @endif
    </div>
  </div>
@endsection
