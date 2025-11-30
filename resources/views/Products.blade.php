<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products</title>
    <link rel="stylesheet" href="{{ asset('css/products.css') }}">
</head>

<body>

    <x-navbar />

    <section id="NFT_collections">
        <h2>Our Collections</h2>

        {{-- ========= GLOSSY COLLECTION ========= --}}
        <div class="collection-card">
            <img class="collection-image"
                 src="{{ asset('images/nfts/glossy/GlossyDuckNFT.png') }}"
                 alt="Glossy Collection">

            <h3>
                <a href="{{ url('/products/glossy-collection') }}">
                    Glossy Collection
                </a>
            </h3>

            <p>A curated set of glossy, high-quality animal NFTs.</p>

            <div class="stock-count">
                <p>Stock: {{ \App\Models\Nft::where('collection_id', 1)->sum('editions_remaining') }}</p>
            </div>
        </div>

        {{-- ========= SUPERHERO COLLECTION ========= --}}
        <div class="collection-card">
            <img class="collection-image"
                 src="{{ asset('images/nfts/superhero/IronMan.png') }}"
                 alt="Superhero Collection">

            <h3>
                <a href="{{ url('/products/superhero-collection') }}">
                    Superhero Collection
                </a>
            </h3>

            <p>A powerful collection of superhero-themed NFTs.</p>

            <div class="stock-count">
                <p>Stock: {{ \App\Models\Nft::where('collection_id', 2)->sum('editions_remaining') }}</p>
            </div>
        </div>

    </section>

</body>
</html>
