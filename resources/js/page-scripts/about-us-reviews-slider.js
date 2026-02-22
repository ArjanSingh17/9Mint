document.addEventListener("DOMContentLoaded", async () => {

    const slider = document.getElementById("reviews-slider");
    const loading = document.getElementById("reviews-loading");

    try {
        const res = await fetch("/api/v1/reviews/high");
        const reviews = await res.json();

        if (loading) loading.remove();

        if (!reviews.length) {
            slider.innerHTML = "<p>No reviews yet</p>";
            return;
        }

        let index = 0;

        function showReview() {
            const r = reviews[index];

            slider.innerHTML = `
                <div class="review-card">
                    <div class="review-stars">
                        ${"★".repeat(r.rating)}
                    </div>

                    <p class="review-text">
                        "${r.review}"
                    </p>

                    <p class="review-name">
                         <strong>${r.name || "Anonymous"}</strong>
                    </p>
                </div>
            `;

            index = (index + 1) % reviews.length;
        }

        // Show first review immediately
        showReview();

        // Change review every 4 seconds
        setInterval(showReview, 4000);

    } catch (error) {
        slider.innerHTML = "Failed to load reviews";
        console.error(error);
    }

});
