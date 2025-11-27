
   


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
     <link rel="stylesheet" href="{{ asset('css/pricing.css') }}">
</head>
<body>
         <div>

        <x-navbar /> 

    <section class="Pricing-info">
        <p>
          Here at 9Mint, we value the hard work and creativity put in by our NFT designers. Therefore, when pricing, we try to</p>
        <p>
          make them as affordable as possible whilst also, doing justice to the designer.
        </p>
      </section>

     <main>
        <section id="Pricing_sizes" class="Org">
            <h3>In general:</h3>
            
            <div class="pricing-grid">
                <div class="pricing-item pricing-small">
                    <div class="pricing-content">
                    <div class="diagram">
                       <img src="{{ asset('images/NFT-small.png')}}" alt="NFT"/>
                    </div>
                <div class="pricing-text">
                    <h4>Small:</h4>
                    <p>Ranges from £45 to £70</p>
                    </div>
                </div>
            </div>

            <div class="pricing-item pricing-medium">
                <div class="pricing-content">
                    <div class="diagram">
                        <img src="{{ asset('images/NFT-medium.png')}}" alt="NFT"/>
                    </div>
                <div class="pricing-text">
                    <h4>Medium:</h4>
                    <p>Ranges from £70 to £200</p>
                    </div>
                </div>
            </div>

            <div class="pricing-item pricing-large">
                <div class="pricing-content">
                    <div class="diagram">
                        <img src="{{ asset('images/NFT-large.png')}}" alt="NFT"/>
                    </div>
                <div class="pricing-text">
                    <h4>Large:</h4>
                    <p>Ranges from £250 to £500</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main> 
</div>
</body>
</html>