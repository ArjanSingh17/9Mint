


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
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('NFT page JavaScript loaded');

    // Handle size selection for all NFTs
    document.querySelectorAll('.size-option').forEach(sizeContainer => {
        const sizeButtons = sizeContainer.querySelectorAll('button');
        const nftInfo = sizeContainer.closest('.nft-info');
        const form = nftInfo ? nftInfo.querySelector('form') : null;

        if (form) {
            console.log('Form found and event listeners being added');

            // Create or find the hidden size input
            let sizeInput = form.querySelector('input[name="size"]');
            if (!sizeInput) {
                sizeInput = document.createElement('input');
                sizeInput.type = 'hidden';
                sizeInput.name = 'size';
                form.appendChild(sizeInput);
            }

            sizeButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    console.log('Size button clicked:', this.textContent.trim());
                    // Remove active class from all buttons in this size option
                    sizeButtons.forEach(btn => btn.classList.remove('selected'));
                    // Add active class to clicked button
                    this.classList.add('selected');
                    // Set the size value
                    const size = this.textContent.trim().toLowerCase();
                    sizeInput.value = size;
                    console.log('Size selected:', size);
                });
            });

            // Prevent form submission if no size selected
            form.addEventListener('submit', function(e) {
                console.log('Form submit attempted. Size value:', sizeInput.value);
                if (!sizeInput.value) {
                    e.preventDefault();
                    alert('Please select a size before adding to basket');
                } else {
                    console.log('Form submitting with size:', sizeInput.value);
                }
            });
        }
    });
});
</script>

        <div>

            <x-navbar />

    @if(session('status'))
        <div style="background: #4CAF50; color: white; padding: 15px; margin: 20px auto; max-width: 800px; border-radius: 8px; text-align: center;">
            {{ session('status') }}
        </div>
    @endif

    <div class="Duck-NFT">
        <img src="/GlossyDuckNFT.png" alt= "Duck" class="nft-image" />

        <div class="nft-info">
            <h2>Glossy Duck</h2>
            <p class="nf-description">
                This NFT is a glossy portrait of a duck.This was created by our highly skilled artist Vlas.</p>
                    <p> Our artist, Vlas, designed this after watching a duck waddle along.</p>

            <div class="size-option">
                <p>Select your size:</p>
                <button class="small-size">Small</button>
                <button class="medium-size">medium</button>
                <button class="large-size">large</button>
            </div>

            @auth
              <form method="POST" action="{{ route('cart.store') }}" class="inline">
                @csrf
                <input type="hidden" name="nft_slug" value="superhero-duck">
                <button type="submit" class="Add-to-basket">Add to basket</button>
              </form>
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

            <div class="size-option">
                <p>Select your size:</p>
                <button class="small-size">Small</button>
                <button class="medium-size">medium</button>
                <button class="large-size">large</button>
            </div>

            @auth
              <form method="POST" action="{{ route('cart.store') }}" class="inline">
                @csrf
                <input type="hidden" name="nft_slug" value="superhero-cat">
                <button type="submit" class="Add-to-basket">Add to basket</button>
              </form>
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

            <div class="size-option">
                <p>Select your size:</p>
                <button class="small-size">Small</button>
                <button class="medium-size">medium</button>
                <button class="large-size">large</button>
            </div>

            @auth
              <form method="POST" action="{{ route('cart.store') }}" class="inline">
                @csrf
                <input type="hidden" name="nft_slug" value="superhero-donkey">
                <button type="submit" class="Add-to-basket">Add to basket</button>
              </form>
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

            <div class="size-option">
                <p>Select your size:</p>
                <button class="small-size">Small</button>
                <button class="medium-size">medium</button>
                <button class="large-size">large</button>
            </div>

            @auth
              <form method="POST" action="{{ route('cart.store') }}" class="inline">
                @csrf
                <input type="hidden" name="nft_slug" value="superhero-giraffe">
                <button type="submit" class="Add-to-basket">Add to basket</button>
              </form>
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

            <div class="size-option">
                <p>Select your size:</p>
                <button class="small-size">Small</button>
                <button class="medium-size">medium</button>
                <button class="large-size">large</button>
            </div>

            @auth
              <form method="POST" action="{{ route('cart.store') }}" class="inline">
                @csrf
                <input type="hidden" name="nft_slug" value="superhero-lobster">
                <button type="submit" class="Add-to-basket">Add to basket</button>
              </form>
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

            <div class="size-option">
                <p>Select your size:</p>
                <button class="small-size">Small</button>
                <button class="medium-size">medium</button>
                <button class="large-size">large</button>
            </div>

            @auth
              <form method="POST" action="{{ route('cart.store') }}" class="inline">
                @csrf
                <input type="hidden" name="nft_slug" value="superhero-rooster">
                <button type="submit" class="Add-to-basket">Add to basket</button>
              </form>
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

            <div class="size-option">
                <p>Select your size:</p>
                <button class="small-size">Small</button>
                <button class="medium-size">medium</button>
                <button class="large-size">large</button>
            </div>

            @auth
              <form method="POST" action="{{ route('cart.store') }}" class="inline">
                @csrf
                <input type="hidden" name="nft_slug" value="superhero-squirrel">
                <button type="submit" class="Add-to-basket">Add to basket</button>
              </form>
            @else
              <a class="Add-to-basket"
                 href="{{ route('login', ['redirect' => request()->fullUrl()]) }}">
                Login to add to basket
              </a>
            @endauth

        </div>
    </div>
</div>
</body>
</html>
