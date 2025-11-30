


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
     <link rel="stylesheet" href="{{ asset('css/Superhero.css') }}">
</head>
<body>

        <div>

            <x-navbar />

    <div class="Duck-NFT">
        <img src="/GlossyDuckNFT.png" alt= "Duck" class="nft-image" />

        <div class="nft-info">
            <h2>Glossy Duck</h2>
            <p class="nf-description">
                This NFT is a glossy portrait of a duck.This was created by our highly skilled artist Vlas.</p>
                    <p> Our artist, Vlas, designed this after watching a duck waddle along.</p>

            <div class="size-option" id="size-glossy-duck">
                <p>Select your size:</p>
                <button class="size-btn" data-size="small" onclick="selectSize('glossy-duck', 'small')">Small</button>
                <button class="size-btn active" data-size="medium" onclick="selectSize('glossy-duck', 'medium')">Medium</button>
                <button class="size-btn" data-size="large" onclick="selectSize('glossy-duck', 'large')">Large</button>
            </div>

            @auth
              <button class="Add-to-basket" data-nft-slug="glossy-duck" onclick="addToCart('glossy-duck')">Add to basket</button>
            @else
              <a class="Add-to-basket"
                 href="{{ route('login', ['redirect' => request()->fullUrl()]) }}">
                Login to add to basket
              </a>
            @endauth

        </div>
    </div>

    <div class="Cat-NFT">
        <img src="/GlossyCat.png" alt= "Cat" class="nft-image" />

        <div class="nft-info">
            <h2>Glossy Cat</h2>
            <p class="nf-description">
                This NFT is a glossy portrait of a cat.This was created by our highly skilled artist Vlas.</p>
                    <p> Our artist, Vlas, designed this after witnessing a kitten following its mother.</p>

            <div class="size-option" id="size-glossy-cat">
                <p>Select your size:</p>
                <button class="size-btn" data-size="small" onclick="selectSize('glossy-cat', 'small')">Small</button>
                <button class="size-btn active" data-size="medium" onclick="selectSize('glossy-cat', 'medium')">Medium</button>
                <button class="size-btn" data-size="large" onclick="selectSize('glossy-cat', 'large')">Large</button>
            </div>

            @auth
              <button class="Add-to-basket" data-nft-slug="glossy-cat" onclick="addToCart('glossy-cat')">Add to basket</button>
            @else
              <a class="Add-to-basket"
                 href="{{ route('login', ['redirect' => request()->fullUrl()]) }}">
                Login to add to basket
              </a>
            @endauth

        </div>
    </div>

    <div class="Donkey-NFT">
        <img src="/GlossyDonkeyNFT.png" alt= "Donkey" class="nft-image" />

        <div class="nft-info">
            <h2>Glossy Donkey</h2>
            <p class="nf-description">
                This NFT is a glossy portrait of a Donkey.This was created by our highly skilled artist Vlas.</p>
                    <p> Our artist, Vlas, designed this after watching Shrek.</p>

            <div class="size-option" id="size-glossy-donkey">
                <p>Select your size:</p>
                <button class="size-btn" data-size="small" onclick="selectSize('glossy-donkey', 'small')">Small</button>
                <button class="size-btn active" data-size="medium" onclick="selectSize('glossy-donkey', 'medium')">Medium</button>
                <button class="size-btn" data-size="large" onclick="selectSize('glossy-donkey', 'large')">Large</button>
            </div>

            @auth
              <button class="Add-to-basket" data-nft-slug="glossy-donkey" onclick="addToCart('glossy-donkey')">Add to basket</button>
            @else
              <a class="Add-to-basket"
                 href="{{ route('login', ['redirect' => request()->fullUrl()]) }}">
                Login to add to basket
              </a>
            @endauth

        </div>
    </div>

    <div class="Giraffe-NFT">
        <img src="/GlossyGiraffeNFT.png" alt= "Giraffe" class="nft-image" />

        <div class="nft-info">
            <h2>Glossy Giraffe</h2>
            <p class="nf-description">
                This NFT is a glossy portrait of a giraffe.This was created by our highly skilled artist Vlas.</p>
                    <p> Our artist, Vlas, designed this after watching Madagascar.</p>

            <div class="size-option" id="size-glossy-giraffe">
                <p>Select your size:</p>
                <button class="size-btn" data-size="small" onclick="selectSize('glossy-giraffe', 'small')">Small</button>
                <button class="size-btn active" data-size="medium" onclick="selectSize('glossy-giraffe', 'medium')">Medium</button>
                <button class="size-btn" data-size="large" onclick="selectSize('glossy-giraffe', 'large')">Large</button>
            </div>

            @auth
              <button class="Add-to-basket" data-nft-slug="glossy-giraffe" onclick="addToCart('glossy-giraffe')">Add to basket</button>
            @else
              <a class="Add-to-basket"
                 href="{{ route('login', ['redirect' => request()->fullUrl()]) }}">
                Login to add to basket
              </a>
            @endauth

        </div>
    </div>

    <div class="Lobster-NFT">
        <img src="/GlossyLobsterNFT.png" alt= "Lobster" class="nft-image" />

        <div class="nft-info">
            <h2>Glossy Lobster</h2>
            <p class="nf-description">
                This NFT is a glossy portrait of a lobster.This was created by our highly skilled artist Vlas.</p>
                    <p> Our artist, Vlas, designed this after seeing pictures on pinterest.</p>

            <div class="size-option" id="size-glossy-lobster">
                <p>Select your size:</p>
                <button class="size-btn" data-size="small" onclick="selectSize('glossy-lobster', 'small')">Small</button>
                <button class="size-btn active" data-size="medium" onclick="selectSize('glossy-lobster', 'medium')">Medium</button>
                <button class="size-btn" data-size="large" onclick="selectSize('glossy-lobster', 'large')">Large</button>
            </div>

            @auth
              <button class="Add-to-basket" data-nft-slug="glossy-lobster" onclick="addToCart('glossy-lobster')">Add to basket</button>
            @else
              <a class="Add-to-basket"
                 href="{{ route('login', ['redirect' => request()->fullUrl()]) }}">
                Login to add to basket
              </a>
            @endauth

        </div>
    </div>

    <div class="Rooster-NFT">
        <img src="/GlossyRoosterNFT.png" alt= "Rooster" class="nft-image" />

        <div class="nft-info">
            <h2>Glossy Rooster</h2>
            <p class="nf-description">
                This NFT is a glossy portrait of a rooster.This was created by our highly skilled artist Vlas.</p>
                    <p> The inspiration for this piece is unknown.</p>

            <div class="size-option" id="size-glossy-rooster">
                <p>Select your size:</p>
                <button class="size-btn" data-size="small" onclick="selectSize('glossy-rooster', 'small')">Small</button>
                <button class="size-btn active" data-size="medium" onclick="selectSize('glossy-rooster', 'medium')">Medium</button>
                <button class="size-btn" data-size="large" onclick="selectSize('glossy-rooster', 'large')">Large</button>
            </div>

            @auth
              <button class="Add-to-basket" data-nft-slug="glossy-rooster" onclick="addToCart('glossy-rooster')">Add to basket</button>
            @else
              <a class="Add-to-basket"
                 href="{{ route('login', ['redirect' => request()->fullUrl()]) }}">
                Login to add to basket
              </a>
            @endauth

        </div>
    </div>

    <div class="Squirrel-NFT">
        <img src="/GlossySquirrelNFT.png" alt= "Squirrel" class="nft-image" />

        <div class="nft-info">
            <h2>Glossy Squirrel</h2>
            <p class="nf-description">
                This NFT is a glossy portrait of a sqirrel.This was created by our highly skilled artist Vlas.</p>
                    <p> Our artist, Vlas, designed this after being inspired by the squirrels on campus.</p>

            <div class="size-option" id="size-glossy-squirrel">
                <p>Select your size:</p>
                <button class="size-btn" data-size="small" onclick="selectSize('glossy-squirrel', 'small')">Small</button>
                <button class="size-btn active" data-size="medium" onclick="selectSize('glossy-squirrel', 'medium')">Medium</button>
                <button class="size-btn" data-size="large" onclick="selectSize('glossy-squirrel', 'large')">Large</button>
            </div>

            @auth
              <button class="Add-to-basket" data-nft-slug="glossy-squirrel" onclick="addToCart('glossy-squirrel')">Add to basket</button>
            @else
              <a class="Add-to-basket"
                 href="{{ route('login', ['redirect' => request()->fullUrl()]) }}">
                Login to add to basket
              </a>
            @endauth

        </div>
    </div>
</div>

<style>
.size-btn {
    padding: 8px 16px;
    margin: 0 4px;
    border: 2px solid #ccc;
    background: white;
    cursor: pointer;
    border-radius: 4px;
    transition: all 0.3s;
}

.size-btn:hover {
    border-color: #888;
}

.size-btn.active {
    border-color: #4CAF50;
    background: #4CAF50;
    color: white;
    font-weight: bold;
}
</style>

<script>
// save selcted nft size
const selectedSizes = {};

function selectSize(nftSlug, size) {
    // store said size
    selectedSizes[nftSlug] = size;

   //remove active class from other buttons
    const sizeContainer = document.getElementById('size-' + nftSlug);
    const buttons = sizeContainer.querySelectorAll('.size-btn');
    buttons.forEach(btn => btn.classList.remove('active'));

    //ad active to current butt
    const selectedButton = sizeContainer.querySelector(`[data-size="${size}"]`);
    selectedButton.classList.add('active');
}

async function addToCart(nftSlug) {
    try {
        // Get selected size (default to medium if not selected)
        const size = selectedSizes[nftSlug] || 'medium';

        // Add to cart using web route
        const response = await fetch('/web/cart', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            credentials: 'same-origin',
            body: JSON.stringify({
                nft_slug: nftSlug,
                quantity: 1,
                size: size
            })
        });

        const data = await response.json();

        if (response.ok) {
            alert(`Added to cart successfully! (Size: ${size.charAt(0).toUpperCase() + size.slice(1)})`);
        } else {
            alert(`Error: ${data.message || 'Failed to add to cart'}`);
        }
    } catch (error) {
        console.error('Error adding to cart:', error);
        alert('An error occurred. Please try again.');
    }
}
</script>

</body>
</html>
