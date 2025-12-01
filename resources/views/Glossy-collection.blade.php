<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Glossy Collection</title>
    <link rel="stylesheet" href="{{ asset('css/Glossy-collection.css') }}">
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

<<<<<<< HEAD
    @if(session('status'))
        <div style="background: #4CAF50; color: white; padding: 15px; margin: 20px auto; max-width: 800px; border-radius: 8px; text-align: center;">
            {{ session('status') }}
        </div>
    @endif

=======
    <!-- GLOSSY DUCK -->
>>>>>>> 694fa108299251785959d74f17d4c946bb6eeb56
    <div class="Duck-NFT">
        <img src="{{ asset('images/nfts/glossy/GlossyDuckNFT.png') }}" alt="Duck" class="nft-image" />

        <div class="nft-info">
            <h2>Glossy Duck</h2>
            <p class="nf-description">
                This NFT is a glossy portrait of a duck created by our highly skilled artist Vlas.
            </p>

            <div class="size-option">
                <p>Select your size:</p>
                <button class="small-size">Small</button>
                <button class="medium-size">Medium</button>
                <button class="large-size">Large</button>
            </div>

            @auth
<<<<<<< HEAD
              <form method="POST" action="{{ route('cart.store') }}" class="inline">
                @csrf
                <input type="hidden" name="nft_slug" value="glossy-duck">
                <button type="submit" class="Add-to-basket">Add to basket</button>
              </form>
=======
                <form method="POST" action="{{ route('cart.store') }}" class="inline">
                    @csrf
                    <input type="hidden" name="nft_slug" value="glossy-duck">
                    <button type="submit" class="Add-to-basket">Add to basket</button>
                </form>
>>>>>>> 694fa108299251785959d74f17d4c946bb6eeb56
            @else
                <a class="Add-to-basket" href="{{ route('login', ['redirect' => request()->fullUrl()]) }}">
                    Login to add to basket
                </a>
            @endauth
        </div>
    </div>

    <!-- GLOSSY CAT -->
    <div class="Cat-NFT">
        <img src="{{ asset('images/nfts/glossy/GlossyCatNFT.png') }}" alt="Cat" class="nft-image" />

        <div class="nft-info">
            <h2>Glossy Cat</h2>
            <p class="nf-description">
                A glossy portrait of a cat created by Vlas.
            </p>

            <div class="size-option">
                <p>Select your size:</p>
                <button class="small-size">Small</button>
                <button class="medium-size">Medium</button>
                <button class="large-size">Large</button>
            </div>

            @auth
<<<<<<< HEAD
              <form method="POST" action="{{ route('cart.store') }}" class="inline">
                @csrf
                <input type="hidden" name="nft_slug" value="glossy-cat">
                <button type="submit" class="Add-to-basket">Add to basket</button>
              </form>
=======
                <form method="POST" action="{{ route('cart.store') }}" class="inline">
                    @csrf
                    <input type="hidden" name="nft_slug" value="glossy-cat">
                    <button type="submit" class="Add-to-basket">Add to basket</button>
                </form>
>>>>>>> 694fa108299251785959d74f17d4c946bb6eeb56
            @else
                <a class="Add-to-basket" href="{{ route('login', ['redirect' => request()->fullUrl()]) }}">
                    Login to add to basket
                </a>
            @endauth
        </div>
    </div>

    <!-- GLOSSY DONKEY -->
    <div class="Donkey-NFT">
        <img src="{{ asset('images/nfts/glossy/GlossyDonkeyNFT.png') }}" alt="Donkey" class="nft-image" />

        <div class="nft-info">
            <h2>Glossy Donkey</h2>
            <p class="nf-description">
                Inspired by Shrek, this glossy donkey portrait was created by Vlas.
            </p>

            <div class="size-option">
                <p>Select your size:</p>
                <button class="small-size">Small</button>
                <button class="medium-size">Medium</button>
                <button class="large-size">Large</button>
            </div>

            @auth
<<<<<<< HEAD
              <form method="POST" action="{{ route('cart.store') }}" class="inline">
                @csrf
                <input type="hidden" name="nft_slug" value="glossy-donkey">
                <button type="submit" class="Add-to-basket">Add to basket</button>
              </form>
=======
                <form method="POST" action="{{ route('cart.store') }}" class="inline">
                    @csrf
                    <input type="hidden" name="nft_slug" value="glossy-donkey">
                    <button type="submit" class="Add-to-basket">Add to basket</button>
                </form>
>>>>>>> 694fa108299251785959d74f17d4c946bb6eeb56
            @else
                <a class="Add-to-basket" href="{{ route('login', ['redirect' => request()->fullUrl()]) }}">
                    Login to add to basket
                </a>
            @endauth
        </div>
    </div>

    <!-- GLOSSY GIRAFFE -->
    <div class="Giraffe-NFT">
        <img src="{{ asset('images/nfts/glossy/GlossyGiraffeNFT.png') }}" alt="Giraffe" class="nft-image" />

        <div class="nft-info">
            <h2>Glossy Giraffe</h2>
            <p class="nf-description">
                A glossy giraffe portrait inspired by Madagascar.
            </p>

            <div class="size-option">
                <p>Select your size:</p>
                <button class="small-size">Small</button>
                <button class="medium-size">Medium</button>
                <button class="large-size">Large</button>
            </div>

            @auth
<<<<<<< HEAD
              <form method="POST" action="{{ route('cart.store') }}" class="inline">
                @csrf
                <input type="hidden" name="nft_slug" value="glossy-giraffe">
                <button type="submit" class="Add-to-basket">Add to basket</button>
              </form>
=======
                <form method="POST" action="{{ route('cart.store') }}" class="inline">
                    @csrf
                    <input type="hidden" name="nft_slug" value="glossy-giraffe">
                    <button type="submit" class="Add-to-basket">Add to basket</button>
                </form>
>>>>>>> 694fa108299251785959d74f17d4c946bb6eeb56
            @else
                <a class="Add-to-basket" href="{{ route('login', ['redirect' => request()->fullUrl()]) }}">
                    Login to add to basket
                </a>
            @endauth
        </div>
    </div>

    <!-- GLOSSY LOBSTER -->
    <div class="Lobster-NFT">
        <img src="{{ asset('images/nfts/glossy/GlossyLobsterNFT.png') }}" alt="Lobster" class="nft-image" />

        <div class="nft-info">
            <h2>Glossy Lobster</h2>
            <p class="nf-description">
                A glossy lobster portrait inspired by Pinterest.
            </p>

            <div class="size-option">
                <p>Select your size:</p>
                <button class="small-size">Small</button>
                <button class="medium-size">Medium</button>
                <button class="large-size">Large</button>
            </div>

            @auth
<<<<<<< HEAD
              <form method="POST" action="{{ route('cart.store') }}" class="inline">
                @csrf
                <input type="hidden" name="nft_slug" value="glossy-lobster">
                <button type="submit" class="Add-to-basket">Add to basket</button>
              </form>
=======
                <form method="POST" action="{{ route('cart.store') }}" class="inline">
                    @csrf
                    <input type="hidden" name="nft_slug" value="glossy-lobster">
                    <button type="submit" class="Add-to-basket">Add to basket</button>
                </form>
>>>>>>> 694fa108299251785959d74f17d4c946bb6eeb56
            @else
                <a class="Add-to-basket" href="{{ route('login', ['redirect' => request()->fullUrl()]) }}">
                    Login to add to basket
                </a>
            @endauth
        </div>
    </div>

    <!-- GLOSSY ROOSTER -->
    <div class="Rooster-NFT">
        <img src="{{ asset('images/nfts/glossy/GlossyRoosterNFT.png') }}" alt="Rooster" class="nft-image" />

        <div class="nft-info">
            <h2>Glossy Rooster</h2>
            <p class="nf-description">A glossy rooster portrait created by Vlas.</p>

            <div class="size-option">
                <p>Select your size:</p>
                <button class="small-size">Small</button>
                <button class="medium-size">Medium</button>
                <button class="large-size">Large</button>
            </div>

            @auth
<<<<<<< HEAD
              <form method="POST" action="{{ route('cart.store') }}" class="inline">
                @csrf
                <input type="hidden" name="nft_slug" value="glossy-rooster">
                <button type="submit" class="Add-to-basket">Add to basket</button>
              </form>
=======
                <form method="POST" action="{{ route('cart.store') }}" class="inline">
                    @csrf
                    <input type="hidden" name="nft_slug" value="glossy-rooster">
                    <button type="submit" class="Add-to-basket">Add to basket</button>
                </form>
>>>>>>> 694fa108299251785959d74f17d4c946bb6eeb56
            @else
                <a class="Add-to-basket" href="{{ route('login', ['redirect' => request()->fullUrl()]) }}">
                    Login to add to basket
                </a>
            @endauth
        </div>
    </div>

    <!-- GLOSSY SQUIRREL -->
    <div class="Squirrel-NFT">
        <img src="{{ asset('images/nfts/glossy/GlossySquirrelNFT.png') }}" alt="Squirrel" class="nft-image" />

        <div class="nft-info">
            <h2>Glossy Squirrel</h2>
            <p class="nf-description">Inspired by playful squirrels on campus.</p>

            <div class="size-option">
                <p>Select your size:</p>
                <button class="small-size">Small</button>
                <button class="medium-size">Medium</button>
                <button class="large-size">Large</button>
            </div>

            @auth
<<<<<<< HEAD
              <form method="POST" action="{{ route('cart.store') }}" class="inline">
                @csrf
                <input type="hidden" name="nft_slug" value="glossy-squirrel">
                <button type="submit" class="Add-to-basket">Add to basket</button>
              </form>
=======
                <form method="POST" action="{{ route('cart.store') }}" class="inline">
                    @csrf
                    <input type="hidden" name="nft_slug" value="glossy-squirrel">
                    <button type="submit" class="Add-to-basket">Add to basket</button>
                </form>
>>>>>>> 694fa108299251785959d74f17d4c946bb6eeb56
            @else
                <a class="Add-to-basket" href="{{ route('login', ['redirect' => request()->fullUrl()]) }}">
                    Login to add to basket
                </a>
            @endauth
        </div>
    </div>

</div>
</body>
</html>
