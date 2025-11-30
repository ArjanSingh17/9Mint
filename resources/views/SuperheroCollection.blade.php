<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Superhero Collection</title>
    <link rel="stylesheet" href="{{ asset('css/SuperheroCollection.css') }}">
</head>

<body>
<div>
    <x-navbar />

    <h1 class="collection-title">Superhero Collection</h1>

    {{-- AQUAMAN --}}
    <div class="NFT-Card">
        <img src="{{ asset('images/nfts/superhero/Aquaman.png') }}" alt="Aquaman" class="nft-image">

        <div class="nft-info">
            <h2>Aquaman</h2>
            <p>A powerful underwater hero with unmatched strength and courage.</p>

            @auth
                <form method="POST" action="{{ route('cart.store') }}">
                    @csrf
                    <input type="hidden" name="nft_slug" value="aquaman">
                    <button type="submit" class="Add-to-basket">Add to basket</button>
                </form>
            @else
                <a href="{{ route('login', ['redirect' => request()->fullUrl()]) }}" class="Add-to-basket">
                    Login to add to basket
                </a>
            @endauth
        </div>
    </div>

    {{-- BATMAN --}}
    <div class="NFT-Card">
        <img src="{{ asset('images/nfts/superhero/Batman.png') }}" alt="Batman" class="nft-image">

        <div class="nft-info">
            <h2>Batman</h2>
            <p>The Dark Knight who protects Gotham City.</p>

            @auth
                <form method="POST" action="{{ route('cart.store') }}">
                    @csrf
                    <input type="hidden" name="nft_slug" value="batman">
                    <button type="submit" class="Add-to-basket">Add to basket</button>
                </form>
            @else
                <a href="{{ route('login', ['redirect' => request()->fullUrl()]) }}" class="Add-to-basket">
                    Login to add to basket
                </a>
            @endauth
        </div>
    </div>

    {{-- CYBORG --}}
    <div class="NFT-Card">
        <img src="{{ asset('images/nfts/superhero/Cyborg.png') }}" alt="Cyborg" class="nft-image">

        <div class="nft-info">
            <h2>Cyborg</h2>
            <p>Half human, half machine — fully powerful.</p>

            @auth
                <form method="POST" action="{{ route('cart.store') }}">
                    @csrf
                    <input type="hidden" name="nft_slug" value="cyborg">
                    <button type="submit" class="Add-to-basket">Add to basket</button>
                </form>
            @else
                <a href="{{ route('login', ['redirect' => request()->fullUrl()]) }}" class="Add-to-basket">
                    Login to add to basket
                </a>
            @endauth
        </div>
    </div>

    {{-- FLASH --}}
    <div class="NFT-Card">
        <img src="{{ asset('images/nfts/superhero/Flash.png') }}" alt="Flash" class="nft-image">

        <div class="nft-info">
            <h2>Flash</h2>
            <p>The fastest hero alive.</p>

            @auth
                <form method="POST" action="{{ route('cart.store') }}">
                    @csrf
                    <input type="hidden" name="nft_slug" value="flash">
                    <button type="submit" class="Add-to-basket">Add to basket</button>
                </form>
            @else
                <a href="{{ route('login', ['redirect' => request()->fullUrl()]) }}" class="Add-to-basket">
                    Login to add to basket
                </a>
            @endauth
        </div>
    </div>

    {{-- IRON MAN --}}
    <div class="NFT-Card">
        <img src="{{ asset('images/nfts/superhero/IronMan.png') }}" alt="Iron Man" class="nft-image">

        <div class="nft-info">
            <h2>Iron Man</h2>
            <p>Genius. Billionaire. Philanthropist.</p>

            @auth
                <form method="POST" action="{{ route('cart.store') }}">
                    @csrf
                    <input type="hidden" name="nft_slug" value="ironman">
                    <button type="submit" class="Add-to-basket">Add to basket</button>
                </form>
            @else
                <a href="{{ route('login', ['redirect' => request()->fullUrl()]) }}" class="Add-to-basket">
                    Login to add to basket
                </a>
            @endauth
        </div>
    </div>

    {{-- SPIDERMAN --}}
    <div class="NFT-Card">
        <img src="{{ asset('images/nfts/superhero/Spiderman.png') }}" alt="Spiderman" class="nft-image">

        <div class="nft-info">
            <h2>Spiderman</h2>
            <p>Friendly neighbourhood protector.</p>

            @auth
                <form method="POST" action="{{ route('cart.store') }}">
                    @csrf
                    <input type="hidden" name="nft_slug" value="spiderman">
                    <button type="submit" class="Add-to-basket">Add to basket</button>
                </form>
            @else
                <a href="{{ route('login', ['redirect' => request()->fullUrl()]) }}" class="Add-to-basket">
                    Login to add to basket
                </a>
            @endauth
        </div>
    </div>

    {{-- SUPERMAN --}}
    <div class="NFT-Card">
        <img src="{{ asset('images/nfts/superhero/Superman.png') }}" alt="Superman" class="nft-image">

        <div class="nft-info">
            <h2>Superman</h2>
            <p>The Man of Steel.</p>

            @auth
                <form method="POST" action="{{ route('cart.store') }}">
                    @csrf
                    <input type="hidden" name="nft_slug" value="superman">
                    <button type="submit" class="Add-to-basket">Add to basket</button>
                </form>
            @else
                <a href="{{ route('login', ['redirect' => request()->fullUrl()]) }}" class="Add-to-basket">
                    Login to add to basket
                </a>
            @endauth
        </div>
    </div>

    {{-- WONDER WOMAN --}}
    <div class="NFT-Card">
        <img src="{{ asset('images/nfts/superhero/WonderWomen.png') }}" alt="Wonder Woman" class="nft-image">

        <div class="nft-info">
            <h2>Wonder Woman</h2>
            <p>A fearless warrior of justice.</p>

            @auth
                <form method="POST" action="{{ route('cart.store') }}">
                    @csrf
                    <input type="hidden" name="nft_slug" value="wonder-women">
                    <button type="submit" class="Add-to-basket">Add to basket</button>
                </form>
            @else
                <a href="{{ route('login', ['redirect' => request()->fullUrl()]) }}" class="Add-to-basket">
                    Login to add to basket
                </a>
            @endauth
        </div>
    </div>

</div>
</body>
</html>
