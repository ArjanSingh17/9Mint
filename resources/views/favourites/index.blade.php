@extends('layouts.app')

@section('content')
<div class="container" style="padding: 100px 20px; text-align: center;">
    <h1 style="color: white; margin-bottom: 30px;">My Favourites</h1>

    @if($favourites->isEmpty())
        <p style="color: #aaa;">You haven't liked any NFTs yet.</p>
        <a href="{{ route('products.index') }}" class="btn btn-primary" style="margin-top: 20px;">Go Explore</a>
    @else
        <div class="nft-grid" style="display: flex; gap: 20px; flex-wrap: wrap; justify-content: center;">
            @foreach($favourites as $nft)
                <div class="card" style="width: 250px; background: #1a1a2e; border: 1px solid #333; border-radius: 10px; overflow: hidden;">
                    <img src="{{ $nft->image_url }}" alt="{{ $nft->name }}" style="width: 100%; height: 250px; object-fit: cover;">
                    <div style="padding: 15px; color: white;">
                        <h3 style="font-size: 1.1rem; margin-bottom: 5px;">{{ $nft->name }}</h3>
                        <p style="color: #00ff88; font-weight: bold;">£{{ $nft->price_medium_gbp ?? '0.00' }}</p>
                        <a href="{{ route('collections.show', ['slug' => $nft->slug]) }}" style="display: block; margin-top: 10px; color: #aaa; text-decoration: none; font-size: 0.9rem;">View Item &rarr;</a>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection