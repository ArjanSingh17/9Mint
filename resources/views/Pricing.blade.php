<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NFT Pricing</title>
    <link rel="stylesheet" href="{{ asset('css/pricing.css') }}">
</head>

<body>

<div>
    <x-navbar />

    <!-- Intro Section -->
    <section class="Pricing-info">
        <p>
            Here at 9Mint, we value the hard work and creativity put in by our NFT designers.
        </p>
        <p>
            We aim to keep prices affordable while honouring the designer’s effort.
        </p>
    </section>

    <!-- Pricing Grid Section -->
    <main>
        <section id="Pricing_sizes" class="Org">
            <h3>In general:</h3>

            <div class="pricing-grid">

                <!-- SMALL -->
                <div class="pricing-item pricing-small">
                    <div class="pricing-content">
                        <div class="diagram">
                            <img src="{{ asset('images/nfts/NFT-small.png') }}" alt="Small NFT" />
                        </div>

                        <div class="pricing-text">
                            <h4>Small:</h4>
                            <p>Ranges from £45 to £70</p>
                        </div>
                    </div>
                </div>

                <!-- MEDIUM -->
                <div class="pricing-item pricing-medium">
                    <div class="pricing-content">
                        <div class="diagram">
                            <img src="{{ asset('images/nfts/NFT-medium.png') }}" alt="Medium NFT" />
                        </div>

                        <div class="pricing-text">
                            <h4>Medium:</h4>
                            <p>Ranges from £70 to £200</p>
                        </div>
                    </div>
                </div>

                <!-- LARGE -->
                <div class="pricing-item pricing-large">
                    <div class="pricing-content">
                        <div class="diagram">
                            <img src="{{ asset('images/nfts/NFT-large.png') }}" alt="Large NFT" />
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
