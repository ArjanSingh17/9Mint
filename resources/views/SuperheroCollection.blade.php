<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Superhero Collection</title>
    <link rel="stylesheet" href="{{ asset('css/Superhero.css?v=' . time()) }}">
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

    <h1 class="collection-title">Superhero Collection</h1>

<<<<<<< HEAD
    @if(session('status'))
        <div style="background: #4CAF50; color: white; padding: 15px; margin: 20px auto; max-width: 800px; border-radius: 8px; text-align: center;">
            {{ session('status') }}
        </div>
    @endif

    <div class="Duck-NFT">
        <img src="/GlossyDuckNFT.png" alt= "Duck" class="nft-image" />
=======
    <!-- AQUAMAN -->
    <div class="NFT-Card">
        <img src="{{ asset('images/nfts/superhero/Aquaman.png') }}" alt="Aquaman" class="nft-image">
>>>>>>> 694fa108299251785959d74f17d4c946bb6eeb56

        <div class="nft-info">
            <h2>Aquaman</h2>
            <p>A powerful underwater hero with unmatched strength and courage.</p>

            <div class="size-option">
                <p>Select your size:</p>
                <button>Small</button>
                <button>Medium</button>
                <button>Large</button>
            </div>

            @auth
<<<<<<< HEAD
              <form method="POST" action="{{ route('cart.store') }}" class="inline">
                @csrf
                <input type="hidden" name="nft_slug" value="superhero-duck">
                <button type="submit" class="Add-to-basket">Add to basket</button>
              </form>
=======
                <form method="POST" action="{{ route('cart.store') }}">
                    @csrf
                    <input type="hidden" name="nft_slug" value="aquaman">
                    <button type="submit" class="Add-to-basket">Add to basket</button>
                </form>
>>>>>>> 694fa108299251785959d74f17d4c946bb6eeb56
            @else
                <a href="{{ route('login', ['redirect' => request()->fullUrl()]) }}" class="Add-to-basket">
                    Login to add to basket
                </a>
            @endauth
        </div>
    </div>

    <!-- BATMAN -->
    <div class="NFT-Card">
        <img src="{{ asset('images/nfts/superhero/Batman.png') }}" alt="Batman" class="nft-image">

        <div class="nft-info">
            <h2>Batman</h2>
            <p>The Dark Knight who protects Gotham City.</p>

            <div class="size-option">
                <p>Select your size:</p>
                <button>Small</button>
                <button>Medium</button>
                <button>Large</button>
            </div>

            @auth
<<<<<<< HEAD
              <form method="POST" action="{{ route('cart.store') }}" class="inline">
                @csrf
                <input type="hidden" name="nft_slug" value="superhero-cat">
                <button type="submit" class="Add-to-basket">Add to basket</button>
              </form>
=======
                <form method="POST" action="{{ route('cart.store') }}">
                    @csrf
                    <input type="hidden" name="nft_slug" value="batman">
                    <button type="submit" class="Add-to-basket">Add to basket</button>
                </form>
>>>>>>> 694fa108299251785959d74f17d4c946bb6eeb56
            @else
                <a href="{{ route('login', ['redirect' => request()->fullUrl()]) }}" class="Add-to-basket">
                    Login to add to basket
                </a>
            @endauth
        </div>
    </div>

    <!-- CYBORG -->
    <div class="NFT-Card">
        <img src="{{ asset('images/nfts/superhero/Cyborg.png') }}" alt="Cyborg" class="nft-image">

        <div class="nft-info">
            <h2>Cyborg</h2>
            <p>Half human, half machine — fully powerful.</p>

            <div class="size-option">
                <p>Select your size:</p>
                <button>Small</button>
                <button>Medium</button>
                <button>Large</button>
            </div>

            @auth
<<<<<<< HEAD
              <form method="POST" action="{{ route('cart.store') }}" class="inline">
                @csrf
                <input type="hidden" name="nft_slug" value="superhero-donkey">
                <button type="submit" class="Add-to-basket">Add to basket</button>
              </form>
=======
                <form method="POST" action="{{ route('cart.store') }}">
                    @csrf
                    <input type="hidden" name="nft_slug" value="cyborg">
                    <button type="submit" class="Add-to-basket">Add to basket</button>
                </form>
>>>>>>> 694fa108299251785959d74f17d4c946bb6eeb56
            @else
                <a href="{{ route('login', ['redirect' => request()->fullUrl()]) }}" class="Add-to-basket">
                    Login to add to basket
                </a>
            @endauth
        </div>
    </div>

    <!-- FLASH -->
    <div class="NFT-Card">
        <img src="{{ asset('images/nfts/superhero/Flash.png') }}" alt="Flash" class="nft-image">

        <div class="nft-info">
            <h2>Flash</h2>
            <p>The fastest hero alive.</p>

            <div class="size-option">
                <p>Select your size:</p>
                <button>Small</button>
                <button>Medium</button>
                <button>Large</button>
            </div>

            @auth
<<<<<<< HEAD
              <form method="POST" action="{{ route('cart.store') }}" class="inline">
                @csrf
                <input type="hidden" name="nft_slug" value="superhero-giraffe">
                <button type="submit" class="Add-to-basket">Add to basket</button>
              </form>
=======
                <form method="POST" action="{{ route('cart.store') }}">
                    @csrf
                    <input type="hidden" name="nft_slug" value="flash">
                    <button type="submit" class="Add-to-basket">Add to basket</button>
                </form>
>>>>>>> 694fa108299251785959d74f17d4c946bb6eeb56
            @else
                <a href="{{ route('login', ['redirect' => request()->fullUrl()]) }}" class="Add-to-basket">
                    Login to add to basket
                </a>
            @endauth
        </div>
    </div>

    <!-- IRON MAN -->
    <div class="NFT-Card">
        <img src="{{ asset('images/nfts/superhero/IronMan.png') }}" alt="Iron Man" class="nft-image">

        <div class="nft-info">
            <h2>Iron Man</h2>
            <p>Genius. Billionaire. Philanthropist.</p>

            <div class="size-option">
                <p>Select your size:</p>
                <button>Small</button>
                <button>Medium</button>
                <button>Large</button>
            </div>

            @auth
<<<<<<< HEAD
              <form method="POST" action="{{ route('cart.store') }}" class="inline">
                @csrf
                <input type="hidden" name="nft_slug" value="superhero-lobster">
                <button type="submit" class="Add-to-basket">Add to basket</button>
              </form>
=======
                <form method="POST" action="{{ route('cart.store') }}">
                    @csrf
                    <input type="hidden" name="nft_slug" value="ironman">
                    <button type="submit" class="Add-to-basket">Add to basket</button>
                </form>
>>>>>>> 694fa108299251785959d74f17d4c946bb6eeb56
            @else
                <a href="{{ route('login', ['redirect' => request()->fullUrl()]) }}" class="Add-to-basket">
                    Login to add to basket
                </a>
            @endauth
        </div>
    </div>

    <!-- SPIDERMAN -->
    <div class="NFT-Card">
        <img src="{{ asset('images/nfts/superhero/Spiderman.png') }}" alt="Spiderman" class="nft-image">

        <div class="nft-info">
            <h2>Spiderman</h2>
            <p>Friendly neighbourhood protector.</p>

            <div class="size-option">
                <p>Select your size:</p>
                <button>Small</button>
                <button>Medium</button>
                <button>Large</button>
            </div>

            @auth
<<<<<<< HEAD
              <form method="POST" action="{{ route('cart.store') }}" class="inline">
                @csrf
                <input type="hidden" name="nft_slug" value="superhero-rooster">
                <button type="submit" class="Add-to-basket">Add to basket</button>
              </form>
=======
                <form method="POST" action="{{ route('cart.store') }}">
                    @csrf
                    <input type="hidden" name="nft_slug" value="spiderman">
                    <button type="submit" class="Add-to-basket">Add to basket</button>
                </form>
>>>>>>> 694fa108299251785959d74f17d4c946bb6eeb56
            @else
                <a href="{{ route('login', ['redirect' => request()->fullUrl()]) }}" class="Add-to-basket">
                    Login to add to basket
                </a>
            @endauth
        </div>
    </div>

    <!-- SUPERMAN -->
    <div class="NFT-Card">
        <img src="{{ asset('images/nfts/superhero/Superman.png') }}" alt="Superman" class="nft-image">

        <div class="nft-info">
            <h2>Superman</h2>
            <p>The Man of Steel.</p>

            <div class="size-option">
                <p>Select your size:</p>
                <button>Small</button>
                <button>Medium</button>
                <button>Large</button>
            </div>

            @auth
<<<<<<< HEAD
              <form method="POST" action="{{ route('cart.store') }}" class="inline">
                @csrf
                <input type="hidden" name="nft_slug" value="superhero-squirrel">
                <button type="submit" class="Add-to-basket">Add to basket</button>
              </form>
=======
                <form method="POST" action="{{ route('cart.store') }}">
                    @csrf
                    <input type="hidden" name="nft_slug" value="superman">
                    <button type="submit" class="Add-to-basket">Add to basket</button>
                </form>
>>>>>>> 694fa108299251785959d74f17d4c946bb6eeb56
            @else
                <a href="{{ route('login', ['redirect' => request()->fullUrl()]) }}" class="Add-to-basket">
                    Login to add to basket
                </a>
            @endauth
        </div>
    </div>

    <!-- WONDER WOMAN -->
    <div class="NFT-Card">
        <img src="{{ asset('images/nfts/superhero/WonderWomen.png') }}" alt="Wonder Woman" class="nft-image">

        <div class="nft-info">
            <h2>Wonder Woman</h2>
            <p>A fearless warrior of justice.</p>

            <div class="size-option">
                <p>Select your size:</p>
                <button>Small</button>
                <button>Medium</button>
                <button>Large</button>
            </div>

            @auth
                <form method="POST" action="{{ route('cart.store') }}">
                    @csrf
                    <input type="hidden" name="nft_slug" value="wonder-woman">
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
