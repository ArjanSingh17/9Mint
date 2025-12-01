<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Products</title>
    <link rel="stylesheet" href="{{ asset('css/products.css') }}">
</head>
<body>

    <x-navbar />

    <section id="NFT_collections">
        <h2>Our Collections</h2>

        <!-- GLOSSY COLLECTION -->
        <div class="Glossy-collection">
            <h3>
                <a href="/products/Glossy-collection">Glossy Collection</a>
            </h3>

            <p>This collection contains Glossy Animal NFTs.</p>
            <p>Click to find more about each individual NFT.</p>

            <!-- NFT PREVIEW IMAGE INSIDE BOX -->
            <div class="collection-image-wrapper">
                <img 
                    src="{{ asset('images/nfts/glossy/GlossyDuckNFT.png') }}"
                    alt="Glossy Collection Preview"
                    class="collection-preview"
                >
            </div>

            <div class="Glossy-Stock">
                <p>Stock: 27</p>
            </div>
        </div>

        <!-- SUPERHERO COLLECTION -->
        <div class="Superhero-collection">
            <h3>
                <a href="/products/SuperheroCollection">Superhero Collection</a>
            </h3>

            <p>This collection contains Superhero NFTs.</p>
            <p>Click to find more about each individual NFT.</p>

            <!-- NFT PREVIEW IMAGE INSIDE BOX -->
            <div class="collection-image-wrapper">
                <img 
                    src="{{ asset('images/nfts/superhero/Superman.png') }}"
                    alt="Superhero Collection Preview"
                    class="collection-preview"
                >
            </div>

            <div class="Superhero-Stock">
                <p>Stock: 35</p>
            </div>
        </div>

    </section>

</body>
</html>
