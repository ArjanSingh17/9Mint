<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    <link rel="stylesheet" href="{{ asset('css/styles.css') }}">
</head>
<body>
  <div>
        <x-navbar /> 
      <div class="nav-right">
        <Link to="/account" class="account-link">Account</Link>
      </div>
    </nav>

    <section id="Information" class="Org">

        <div class="image-left">
            <img 
                src="{{ asset($nfts[0]->image_url ?? 'images/NFT1.png') }}" 
                alt="Left NFT"
            />
        </div>

        <div class="Informationstyle">
            <h2>Need an NFT?</h2>
            <h3>We got you!</h3>
        </div>

        <div class="image-right">
            <img 
                src="{{ asset($nfts[1]->image_url ?? 'images/NFT4.png') }}" 
                alt="Right NFT"
            />
        </div>

    </section>
    </div>
</body>
</html>
