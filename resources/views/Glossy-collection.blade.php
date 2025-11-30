<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Glossy Collection</title>
    <link rel="stylesheet" href="{{ asset('css/Glossy-collection.css') }}">
</head>

<body>
<div>
    <x-navbar />

    <h1 class="collection-title">Glossy Collection</h1>

    {{-- GLOSSY DUCK --}}
    <div class="NFT-Card">
        <img src="{{ asset('images/nfts/glossy/GlossyDuckNFT.png') }}" alt="Glossy Duck" class="nft-image">
        <div class="nft-info">
            <h2>Glossy Duck</h2>
            <p>This NFT is a glossy portrait of a duck, created by our skilled artist Vlas.</p>
        </div>
    </div>

    {{-- GLOSSY CAT --}}
    <div class="NFT-Card">
        <img src="{{ asset('images/nfts/glossy/GlossyCatNFT.png') }}" alt="Glossy Cat" class="nft-image">
        <div class="nft-info">
            <h2>Glossy Cat</h2>
            <p>A glossy portrait of a cat designed by Vlas.</p>
        </div>
    </div>

    {{-- GLOSSY DONKEY --}}
    <div class="NFT-Card">
        <img src="{{ asset('images/nfts/glossy/GlossyDonkeyNFT.png') }}" alt="Glossy Donkey" class="nft-image">
        <div class="nft-info">
            <h2>Glossy Donkey</h2>
            <p>Donkey portrait inspired by Shrek.</p>
        </div>
    </div>

    {{-- GLOSSY GIRAFFE --}}
    <div class="NFT-Card">
        <img src="{{ asset('images/nfts/glossy/GlossyGiraffeNFT.png') }}" alt="Glossy Giraffe" class="nft-image">
        <div class="nft-info">
            <h2>Glossy Giraffe</h2>
            <p>Giraffe portrait inspired by Madagascar.</p>
        </div>
    </div>

    {{-- GLOSSY LOBSTER --}}
    <div class="NFT-Card">
        <img src="{{ asset('images/nfts/glossy/GlossyLobsterNFT.png') }}" alt="Glossy Lobster" class="nft-image">
        <div class="nft-info">
            <h2>Glossy Lobster</h2>
            <p>Lobster portrait inspired by Pinterest.</p>
        </div>
    </div>

    {{-- GLOSSY ROOSTER --}}
    <div class="NFT-Card">
        <img src="{{ asset('images/nfts/glossy/GlossyRoosterNFT.png') }}" alt="Glossy Rooster" class="nft-image">
        <div class="nft-info">
            <h2>Glossy Rooster</h2>
        </div>
    </div>

    {{-- GLOSSY SQUIRREL --}}
    <div class="NFT-Card">
        <img src="{{ asset('images/nfts/glossy/GlossySquirrelNFT.png') }}" alt="Glossy Squirrel" class="nft-image">
        <div class="nft-info">
            <h2>Glossy Squirrel</h2>
            <p>Inspired by squirrels on campus.</p>
        </div>
    </div>

</div>
</body>
</html>
