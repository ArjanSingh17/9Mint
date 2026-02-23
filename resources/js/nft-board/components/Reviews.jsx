import "./Reviews_CSS.css";
import { useEffect, useState } from "react";

function Reviews() {
    const [reviews, setReviews] = useState([]);
    const [current, setCurrent] = useState(0);

    useEffect(() => {
        fetch("/api/v1/reviews/high")
            .then(res => res.json())
            .then(data => setReviews(data));
    }, []);

    useEffect(() => {
        if (reviews.length === 0) return;

        const interval = setInterval(() => {
            setCurrent(prev => (prev + 1) % reviews.length);
        }, 3000);

        return () => clearInterval(interval);
    }, [reviews]);

    if (reviews.length === 0) return null;

    const review = reviews[current];

    return (
        <section className="reviews">
            <h2 className="reviews-title">Customer Reviews</h2>

            <div className="slider-card">
                <div className="stars-rating">
                    {"★".repeat(review.rating)}
                    {"☆".repeat(5 - review.rating)}
                </div>

                <p className="text-reviews">
                    "{review.review}"
                </p>

                <p className="name-reviews">
                    - {review.name}
                </p>
            </div>
        </section>
    );
}

export default Reviews;
