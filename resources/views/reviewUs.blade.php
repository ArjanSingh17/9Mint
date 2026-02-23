@extends('layouts.app')

@section('title', 'Review Us')

@push('styles')
    @vite('resources/css/pages/app-pages.css')
    <style>
        .review-feedback-banner {
            position: fixed;
            top: var(--review-feedback-top, 0px);
            left: 0;
            right: 0;
            z-index: 120;
            width: 100%;
            padding: 10px 16px;
            text-align: center;
            font-weight: 700;
            color: #fff;
            border-bottom: 1px solid rgba(0, 0, 0, 0.2);
            opacity: 0;
            pointer-events: none;
            transform: translateY(-8px);
            transition: opacity 0.2s ease, transform 0.2s ease;
        }

        .review-feedback-banner.is-visible {
            opacity: 1;
            transform: translateY(0);
        }

        .review-feedback-banner.is-success {
            background: #16a34a;
        }

        .review-feedback-banner.is-error {
            background: #dc2626;
        }

        .review-page .auth-form.review-auth-form {
            width: min(100%, 520px);
            padding: 32px;
        }

        .review-auth-form h2 {
            margin-bottom: 10px;
        }

        .review-auth-subtitle {
            margin: 0 0 18px;
            text-align: center;
            color: var(--subtext-color);
        }

        .review-auth-form form {
            display: flex;
            flex-direction: column;
            gap: 14px;
        }

        .review-auth-form textarea {
            width: 100%;
            min-height: 120px;
            padding: 12px 14px;
            border-radius: 8px;
            border: 2px solid var(--border-input);
            background: var(--surface-input);
            color: var(--text-main);
            resize: vertical;
            font: inherit;
        }

        .review-auth-form textarea::placeholder {
            color: var(--text-secondary);
        }

        .review-auth-form textarea:focus {
            outline: none;
            border-color: var(--button-bg);
            box-shadow: 0 0 0 2px color-mix(in srgb, var(--button-bg) 25%, transparent);
        }

        .review-stars {
            display: flex;
            align-items: center;
            justify-content: center;
            flex-wrap: nowrap;
            gap: 8px;
        }

        .review-auth-form .review-star {
            width: auto !important;
            max-width: none !important;
            min-height: 0 !important;
            margin: 0 !important;
            padding: 0 !important;
            background: transparent !important;
            border: none !important;
            border-radius: 0 !important;
            box-shadow: none !important;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            line-height: 1;
            color: color-mix(in srgb, var(--text-secondary) 80%, #999 20%);
            cursor: pointer;
            transition: transform 0.12s ease, color 0.12s ease;
        }

        .review-auth-form .review-star.active {
            color: #f5c542;
        }

        .review-auth-form .review-star:hover {
            transform: scale(1.07);
        }
    </style>
@endpush

@section('content')
    <div id="reviewFeedbackBanner" class="review-feedback-banner" role="status" aria-live="polite"></div>
    <div class="auth-page-container review-page">
        <div class="auth-section">
            <div class="auth-form review-auth-form">
                <h2>Review Our Website</h2>
                <p class="review-auth-subtitle">We would love to hear from you</p>

                <form id="reviewForm" novalidate>
                    <input
                        type="text"
                        id="name"
                        name="name"
                        placeholder="Your Name"
                        required
                    >

                    <textarea
                        id="review"
                        name="review"
                        placeholder="Your Review"
                        required
                    ></textarea>

                    <div class="review-stars" id="starContainer" role="radiogroup" aria-label="Star rating">
                        <button type="button" class="review-star" data-value="1" aria-label="Rate 1 star" aria-checked="false">★</button>
                        <button type="button" class="review-star" data-value="2" aria-label="Rate 2 stars" aria-checked="false">★</button>
                        <button type="button" class="review-star" data-value="3" aria-label="Rate 3 stars" aria-checked="false">★</button>
                        <button type="button" class="review-star" data-value="4" aria-label="Rate 4 stars" aria-checked="false">★</button>
                        <button type="button" class="review-star" data-value="5" aria-label="Rate 5 stars" aria-checked="false">★</button>
                    </div>

                    <input type="hidden" id="rating" name="rating" value="5">

                    <button class="review-submit is-ready" type="submit">Submit Review</button>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('reviewForm');
            if (!form) return;

            const nameInput = document.getElementById('name');
            const reviewInput = document.getElementById('review');
            const ratingInput = document.getElementById('rating');
            const submitBtn = form.querySelector('.review-submit');
            const starContainer = document.getElementById('starContainer');
            const stars = Array.from(form.querySelectorAll('.review-star'));
            const feedbackBanner = document.getElementById('reviewFeedbackBanner');
            const header = document.querySelector('header');
            let bannerTimer = null;

            let selectedRating = Number(ratingInput.value) || 5;
            let hoverRating = 0;

            const renderStars = function () {
                const activeRating = hoverRating || selectedRating;
                stars.forEach(function (star, index) {
                    const value = index + 1;
                    star.classList.toggle('active', value <= activeRating);
                    star.setAttribute('aria-checked', String(value === selectedRating));
                });
            };

            const syncSubmitState = function () {
                const allFilled = nameInput.value.trim().length > 0 && reviewInput.value.trim().length > 0 && Number(ratingInput.value) > 0;
                submitBtn.classList.toggle('is-ready', allFilled && form.checkValidity());
            };

            const setRating = function (value) {
                selectedRating = Math.min(5, Math.max(1, Number(value) || 1));
                ratingInput.value = String(selectedRating);
                renderStars();
                syncSubmitState();
            };

            const updateBannerOffset = function () {
                if (!header) {
                    document.documentElement.style.setProperty('--review-feedback-top', '0px');
                    return;
                }

                const rect = header.getBoundingClientRect();
                const nearTop = window.scrollY <= 12;
                const top = nearTop ? Math.max(0, Math.ceil(rect.height)) : 0;
                document.documentElement.style.setProperty('--review-feedback-top', top + 'px');
            };

            const showBanner = function (message, kind) {
                if (!feedbackBanner) return;
                feedbackBanner.textContent = message;
                feedbackBanner.classList.remove('is-success', 'is-error');
                feedbackBanner.classList.add(kind === 'success' ? 'is-success' : 'is-error', 'is-visible');
                updateBannerOffset();

                if (bannerTimer) {
                    window.clearTimeout(bannerTimer);
                }

                bannerTimer = window.setTimeout(function () {
                    feedbackBanner.classList.remove('is-visible');
                }, 4500);
            };

            stars.forEach(function (star, index) {
                const value = index + 1;
                star.addEventListener('click', function () {
                    setRating(value);
                });
                star.addEventListener('mouseenter', function () {
                    hoverRating = value;
                    renderStars();
                });
                star.addEventListener('keydown', function (event) {
                    if (event.key === 'Enter' || event.key === ' ') {
                        event.preventDefault();
                        setRating(value);
                    } else if (event.key === 'ArrowRight') {
                        event.preventDefault();
                        setRating(Math.min(5, selectedRating + 1));
                    } else if (event.key === 'ArrowLeft') {
                        event.preventDefault();
                        setRating(Math.max(1, selectedRating - 1));
                    }
                });
            });

            if (starContainer) {
                starContainer.addEventListener('mouseleave', function () {
                    hoverRating = 0;
                    renderStars();
                });
            }

            form.addEventListener('input', syncSubmitState);

            form.addEventListener('submit', async function (event) {
                event.preventDefault();

                try {
                    const response = await fetch('/api/v1/reviews', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({
                            name: nameInput.value.trim(),
                            review: reviewInput.value.trim(),
                            rating: Number(ratingInput.value),
                        })
                    });

                    if (!response.ok) {
                        throw new Error('Could not submit review.');
                    }

                    showBanner('Review submitted!', 'success');
                    form.reset();
                    setRating(5);
                } catch (error) {
                    showBanner(error.message || 'Failed to submit review.', 'error');
                }
            });

            renderStars();
            syncSubmitState();
            updateBannerOffset();
            window.addEventListener('scroll', updateBannerOffset, { passive: true });
            window.addEventListener('resize', updateBannerOffset);
        });
    </script>
@endpush
