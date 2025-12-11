@extends('layouts.app')

@section('title', 'Homepage')

@section('content')
    <section id="Information" class="Org">
        <div class="image-left">
            <img 
                id="homepage-left-img"
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
                id="homepage-right-img"
                src="{{ asset($nfts[1]->image_url ?? 'images/NFT4.png') }}" 
                alt="Right NFT"
            />
        </div>
    </section>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const leftImg = document.getElementById('homepage-left-img');
    const rightImg = document.getElementById('homepage-right-img');
    const images = @json($nfts->pluck('image_url')->values());

    if (!leftImg || !rightImg || !Array.isArray(images) || images.length === 0) {
        return;
    }

    function pickTwoDistinct(length) {
        if (length === 1) {
            return [0, 0];
        }
        const first = Math.floor(Math.random() * length);
        let second = Math.floor(Math.random() * length);
        let safety = 0;
        while (second === first && safety < 10) {
            second = Math.floor(Math.random() * length);
            safety++;
        }
        return [first, second];
    }

    let lastPair = null;

    function applyRandomPair() {
        if (images.length < 2) return;

        let pair = pickTwoDistinct(images.length);

        // Avoid repeating the exact same pair back-to-back when we have enough images
        let safety = 0;
        while (lastPair && images.length > 2 && pair[0] === lastPair[0] && pair[1] === lastPair[1] && safety < 10) {
            pair = pickTwoDistinct(images.length);
            safety++;
        }

        const [leftIndex, rightIndex] = pair;
        leftImg.src = images[leftIndex];
        rightImg.src = images[rightIndex];
        lastPair = pair;
    }

    // Initial random pair
    applyRandomPair();

    setInterval(() => {
        applyRandomPair();
    }, 3000); // 3 seconds
});
</script>
@endpush

