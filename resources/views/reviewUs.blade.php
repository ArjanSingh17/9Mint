@extends('layouts.app')

@section('content')

<div class="review-wrapper">

    <div class="review-card">

        <h1 class="review-title">Review Our Website</h1>

    <p class="review-subtitle">
        We would love to hear from you
    </p>
        <form id="reviewForm">

            <input 
                class="review-input"
                type="text"
                id="name"
                placeholder="Your Name"
            >

            <textarea
                class="review-textarea"
                id="review"
                placeholder="Your Review"
            ></textarea>

            <!-- ⭐ STAR RATING -->
           <div class="review-stars" id="starContainer">
    <span class="star" data-value="1">★</span>
    <span class="star" data-value="2">★</span>
    <span class="star" data-value="3">★</span>
    <span class="star" data-value="4">★</span>
    <span class="star" data-value="5">★</span>
</div>



            <!-- Hidden rating input -->
            <input type="hidden" id="rating" value="5">

            <button class="review-submit" type="submit">
                Submit Review
            </button>

        </form>

    </div>

</div>
<script>

// STAR CLICK LOGIC
const stars = document.querySelectorAll('.star');
const ratingInput = document.getElementById('rating');

stars.forEach(star => {
    star.addEventListener('click', () => {
        const value = star.dataset.value;
        ratingInput.value = value;

        stars.forEach(s => {
            s.classList.remove('active');
            if (s.dataset.value <= value) {
                s.classList.add('active');
            }
        });
    });
});


//  FORM SUBMIT
document.getElementById('reviewForm').addEventListener('submit', async e => {
    e.preventDefault();

    await fetch('/api/v1/reviews', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            name: document.getElementById('name').value,
            review: document.getElementById('review').value,
            rating: document.getElementById('rating').value
        })
    });

    alert("Review submitted!");

// Clear form fields
document.getElementById('name').value = "";
document.getElementById('review').value = "";

// Reset stars
selectedRating = 0;
document.querySelectorAll('.star').forEach(s => s.classList.remove('active'));

});

</script>
@endsection
