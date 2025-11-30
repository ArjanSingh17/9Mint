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

        @foreach ($collections as $collection)
            <div class="collection-card">
                
                <h3>
                    <a href="/products/{{ $collection->slug }}">
                        {{ $collection->name }}
                    </a>
                </h3>

                <p>{{ $collection->description }}</p>

                <div class="Stock">
                    <p>
                        Stock: 
                        {{ $collection->nfts()->sum('editions_remaining') }}
                    </p>
                </div>
            </div>
        @endforeach

    </section>

</body>
</html>
