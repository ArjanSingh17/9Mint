@extends('layouts.app')

@section('title', 'Trending')

@push('styles')
    @vite('resources/css/pages/trending.css')
@endpush

@section('content')
    <section id="trending-section">
        <h2>Trending NFTs</h2>
        <p class="trending-subtitle">Most sold in the last 7 days</p>

        @if ($trendingNfts->isEmpty())
            <p class="trending-empty">No NFTs have been sold in the past 7 days. Check back soon!</p>
        @else
         <div class="trending-table-wrap">
         <table class="trending-table">
      <thead>
         <tr>
           <th class="trending-col-rank">#</th>
              <th class="trending-col-image"></th>
                 <th class="trending-col-name">NFT</th>
              <th class="trending-col-collection">Collection</th>
                            <th class="trending-col-sales">Sales</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($trendingNfts as $index => $nft)
                            <tr>
           <td class="trending-rank">{{ $index + 1 }}</td>
               <td class="trending-image">
                   <a href="{{ route('nfts.show', ['slug' => $nft->slug]) }}">
                      <img src="{{ asset(ltrim($nft->image_url, '/')) }}" alt="{{ $nft->name }}" />
                       </a>
                       </td>
                       <td class="trending-name">
                                    <a href="{{ route('nfts.show', ['slug' => $nft->slug]) }}">{{ $nft->name }}</a>
                      </td>
                   <td class="trending-collection">
                       @if ($nft->collection)
                  <a href="{{ route('collections.show', ['slug' => $nft->collection->slug]) }}">{{ $nft->collection->name }}</a>
                                    @else
                                        &mdash;
                                    @endif
                                </td>
                                <td class="trending-sales">{{ $nft->sales_count }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
@endsection
