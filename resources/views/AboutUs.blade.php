<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>About Us</title>
  <link rel="stylesheet" href="{{ asset('css/contactUs.css') }}">
</head>
<body>

    <div>

     <x-navbar />

      <section class="Groupname">
        <h1>9 MINT</h1>
        <p>All about art and creativity</p>
      </section>


      {{--  Dynamic NFT Grid --}}
      <section class="nft-grid">
        @foreach($nfts as $nft)
            <img src="{{ $nft->image_url }}" alt="{{ $nft->name }}" />
        @endforeach
      </section>


      <section class="about-section">
        <h2>Who Are We?</h2>
        <p>
          9Mint is a simulated e-commerce platform designed to sell and manage
          Non-Fungible Tokens (NFTs). At 9 MINT, our mission is to foster a
          vibrant community of art enthusiasts and creators.
        </p>

        <h2>Our Journey</h2>
        <p>
          Founded in 2025, 9 MINT began as a small group of artists and tech
          enthusiasts passionate about digital art and NFTs.
        </p>

        <h2>Our Community</h2>
        <p>
          We believe art is for everyone. Our platform connects artists and
          collectors worldwide.
        </p>
      </section>


      <section class="team-section">
        <h2>Meet the Team</h2>

        <div class="team-grid">
            @php
                $team = [
                    [ "name" => "Naomi", "role" => "Team mediator and Front end engineer" ],
                    [ "name" => "Arjan", "role" => "Team Leader and Back end engineer" ],
                    [ "name" => "Maliyka", "role" => "Front end engineer leader" ],
                    [ "name" => "Kalil", "role" => "Backend developer and Proofreader" ],
                    [ "name" => "Dariusz", "role" => "Backend engineer and project" ],
                    [ "name" => "Hamza", "role" => "Team creative and frontend developer" ],
                    [ "name" => "Jahirul", "role" => "Backend Engineer and Timekeeper" ],
                    [ "name" => "Vlas", "role" => "Front/Backend Engineer and digital artist" ],
                ];
            @endphp

            @foreach($team as $member)
                <div class="team-card">
                    <img src="{{ asset('images/logo.png')}}" alt="Logo">
                    <h3>{{ $member['name'] }}</h3>
                    <p>Role: {{ $member['role'] }}</p>
                </div>
            @endforeach
        </div>
      </section>


      <section class="aboutus-section">
        <h2>Get in Touch</h2>
        <p>We love connecting with art lovers, creators, and collectors.</p>
        <p>
          If you'd like to reach us directly, click
          <a href="/contactUs" class="contact-link">
            here
          </a>
          to contact us.
        </p>
      </section>

    </div>
</body>
</html>
