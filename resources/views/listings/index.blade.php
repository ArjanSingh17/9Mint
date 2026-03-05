@extends('layouts.app')

@section('title', 'My Listings')

@push('styles')
  @vite('resources/css/pages/app-pages.css')
  <style>
    .listings-collection {
      margin-bottom: 10px;
      border: 1px solid var(--border-soft);
      border-radius: 10px;
      overflow: hidden;
    }

    .listings-collection > summary {
      list-style: none;
      cursor: pointer;
      padding: 12px;
      display: grid;
      grid-template-columns: minmax(0, 1fr) auto auto;
      gap: 12px;
      align-items: center;
      font-weight: 600;
      color: var(--text-main);
    }

    .listings-collection > summary::-webkit-details-marker {
      display: none;
    }

    .listings-collection > summary::after {
      content: "▼";
      font-size: 12px;
      color: var(--text-secondary);
      transition: transform 0.2s ease;
      justify-self: end;
    }

    .listings-collection__name {
      min-width: 0;
      text-align: left;
    }

    .listings-collection__count {
      font-size: 12px;
      color: var(--text-secondary);
      white-space: nowrap;
      text-align: right;
    }

    .listings-collection[open] > summary::after {
      transform: rotate(180deg);
    }

    .listings-collection__body {
      padding: 0 12px 12px;
    }

    .listings-empty {
      text-align: left;
    }
  </style>
@endpush

@php
  $currencySymbols = [
    'GBP' => '£',
    'USD' => '$',
    'EUR' => '€',
    'BTC' => '₿',
    'ETH' => 'Ξ',
  ];
  $sellingPreviewItems = collect($listings)->filter(fn ($listing) => $listing->token?->nft)->map(function ($listing) use ($currencySymbols) {
    $token = $listing->token;
    $nft = $token->nft;
    $currency = strtoupper((string) $listing->ref_currency);
    $symbol = $currencySymbols[$currency] ?? null;
    $priceLabel = $symbol
      ? $symbol . number_format((float) $listing->ref_amount, 2)
      : number_format((float) $listing->ref_amount, 2) . ' ' . $currency;

    return [
      'href' => route('nfts.show', ['slug' => $nft->slug]),
      'image_url' => $nft->image_url,
      'name' => $nft->name,
      'edition_label' => 'Edition #' . ($token->serial_number ?? '-'),
      'subline' => 'Listed for ' . $priceLabel,
    ];
  })->values();
@endphp

@section('content')
  <section class="profile-page">
    <h1 class="profile-title">My Listings</h1>

    <div class="profile-card" style="margin-bottom: 22px;">
      <h2 class="text-2xl font-semibold mb-4">NFTs You Are Selling</h2>
      <x-profile-nft-preview-grid
        :items="$sellingPreviewItems"
        empty-text="You have no NFTs currently selling."
        cta-label="Click to see more NFTs"
        :expand-inline="true"
      />
    </div>

    <div class="profile-card" style="margin-bottom: 22px;">
      <h2 class="text-2xl font-semibold mb-4">Collections You Created</h2>

      @if (($ownedCollections ?? collect())->isEmpty())
        <p style="text-align: left;">You have not created any collections yet.</p>
      @else
        @foreach ($ownedCollections as $collection)
          <details class="listings-collection">
            <summary>
              <span class="listings-collection__name">{{ $collection->name }}</span>
              <span class="listings-collection__count">{{ $collection->nfts->count() }} NFTs</span>
            </summary>

            <div class="listings-collection__body">
              @if ($collection->nfts->isEmpty())
                <p style="text-align:left; margin:0;">No NFTs in this collection yet.</p>
              @else
                <div style="overflow-x:auto;">
                  <div style="display:grid; grid-template-columns: 2fr 1fr 1fr 1fr; gap: 8px; padding: 8px; border-bottom: 1px solid var(--border-soft); font-weight: 600;">
                    <div>NFT</div>
                    <div>Current Owner</div>
                    <div>Last Sold Price</div>
                    <div>Selling Now</div>
                  </div>

                  @foreach ($collection->nfts as $nft)
                    @php
                      $meta = $ownedCollectionNftMeta[$nft->id] ?? null;
                    @endphp
                    <details style="border-bottom: 1px solid var(--border-soft);">
                      <summary style="cursor: pointer; list-style-position: inside; padding: 8px; display: grid; grid-template-columns: 2fr 1fr 1fr 1fr; gap: 8px; align-items: start;">
                        <div>
                          <a href="{{ route('nfts.show', ['slug' => $nft->slug]) }}" style="color: var(--text-main); text-decoration: none;">
                            {{ $nft->name }}
                          </a>
                          <span style="font-size: 12px; color: var(--subtext-color); margin-left: 6px;">(show editions)</span>
                        </div>
                        <div>{{ $meta['owner_label'] ?? 'Unowned' }}</div>
                        <div>{{ $meta['last_price_label'] ?? 'Never sold' }}</div>
                        <div>{{ !empty($meta['is_selling_now']) ? 'Yes' : 'No' }}</div>
                      </summary>

                      <div style="padding: 8px 8px 10px;">
                        <table style="width:100%; border-collapse: collapse;">
                          <thead>
                            <tr>
                              <th style="padding: 6px; border-bottom: 1px solid var(--border-soft); font-size: 12px; text-align:left;">Edition</th>
                              <th style="padding: 6px; border-bottom: 1px solid var(--border-soft); font-size: 12px; text-align:left;">Owner</th>
                              <th style="padding: 6px; border-bottom: 1px solid var(--border-soft); font-size: 12px; text-align:left;">Last Sold</th>
                              <th style="padding: 6px; border-bottom: 1px solid var(--border-soft); font-size: 12px; text-align:left;">Selling</th>
                            </tr>
                          </thead>
                          <tbody>
                            @foreach ($nft->tokens as $token)
                              @php
                                $tokenSale = ($latestSaleByTokenId ?? [])[$token->id] ?? null;
                                $tokenSaleLabel = $tokenSale
                                  ? strtoupper((string) $tokenSale->pay_currency) . ' ' . number_format((float) $tokenSale->pay_amount, 2)
                                  : 'Never sold';
                                $tokenOwnerLabel = $token->owner?->name ?? 'Unowned';
                                $tokenIsSelling = $token->listing && in_array($token->listing->status, ['active', 'reserved'], true);
                              @endphp
                              <tr>
                                <td style="padding: 6px; border-bottom: 1px solid var(--border-soft); font-size: 12px;">#{{ $token->serial_number }}</td>
                                <td style="padding: 6px; border-bottom: 1px solid var(--border-soft); font-size: 12px;">{{ $tokenOwnerLabel }}</td>
                                <td style="padding: 6px; border-bottom: 1px solid var(--border-soft); font-size: 12px;">{{ $tokenSaleLabel }}</td>
                                <td style="padding: 6px; border-bottom: 1px solid var(--border-soft); font-size: 12px;">{{ $tokenIsSelling ? 'Yes' : 'No' }}</td>
                              </tr>
                            @endforeach
                          </tbody>
                        </table>
                      </div>
                    </details>
                  @endforeach
                </div>
              @endif
            </div>
          </details>
        @endforeach
      @endif
    </div>

    <div style="display:flex; justify-content:center; margin-top: 14px;">
      <a href="{{ route('creator.collections.create') }}" class="nav-btn signout">Create New Collection</a>
    </div>
  </section>
@endsection
