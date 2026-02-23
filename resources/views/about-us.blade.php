@extends('layouts.app')

@section('title', 'About Us')

@push('styles')
  @vite('resources/css/pages/about-contact.css')
@endpush

@section('content')
<div class="about-page-container about-us-container">
      {{-- Hero --}}
      <section class="Groupname">
        <h1>9 MINT</h1>
        <p>All about art and creativity</p>
      </section>

      <!-- -- Reviews Slider -- -->
      <section class="reviews-section">
          <h2 class="reviews-title">What Our Users Say</h2>
          <div id="reviews-slider" class="reviews-slider">
              <p id="reviews-loading">Loading reviews...</p>
          </div>
      </section>

      {{-- About --}}
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

      {{-- Team --}}
      <section class="team-section">
        <h2>Meet the Team</h2>
        <div class="team-table-wrap">
            <table class="team-table">
                <thead>
                    <tr>
                        <th align="left">Name</th>
                        <th align="left">Role</th>
                        <th align="left">ID</th>
                    </tr>
                </thead>
                <tbody>
                    <tr><td><b>Arjan Singh</b></td><td>Project Lead &amp; Backend</td><td><code>240209768</code></td></tr>
                    <tr><td><b>Dariusz Dabrowski</b></td><td>Backend Lead</td><td><code>240353669</code></td></tr>
                    <tr><td><b>Jahirul Islam</b></td><td>Backend</td><td><code>240219893</code></td></tr>
                    <tr><td><b>Khalil Suleiman</b></td><td>Backend</td><td><code>240248572</code></td></tr>
                    <tr><td><b>Maliyka Liaqat</b></td><td>Frontend Lead</td><td><code>240119641</code></td></tr>
                    <tr><td><b>Hamza Heybe</b></td><td>Frontend</td><td><code>240158042</code></td></tr>
                    <tr><td><b>Naomi Olowu</b></td><td>Frontend</td><td><code>240229043</code></td></tr>
                    <tr><td><b>Vlas Yermachenko</b></td><td>Full-stack &amp; NFT Artist</td><td><code>240180928</code></td></tr>
                </tbody>
            </table>
        </div>
      </section>
@endsection
@push('scripts')
    @vite([
        'resources/js/page-scripts/about-us-nft-grid-rotator.js',
        'resources/js/page-scripts/about-us-reviews-slider.js'
    ])
@endpush
