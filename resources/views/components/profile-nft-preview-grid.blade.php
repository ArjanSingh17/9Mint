@props([
    'items' => [],
    'emptyText' => 'No NFTs found.',
    'ctaLabel' => null,
    'ctaHref' => null,
    'expandInline' => false,
])

@php
    $rows = collect($items)->values();
@endphp

@once
    @push('styles')
        <style>
            .profile-preview-grid {
                display: grid;
                grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
                gap: 16px;
                position: relative;
            }

            .profile-preview-card {
                background: var(--surface-panel);
                border: 1px solid var(--border-soft);
                border-radius: 10px;
                overflow: hidden;
                text-decoration: none;
                color: var(--text-main);
                transition: transform 0.2s ease;
                position: relative;
            }

            .profile-preview-card:hover {
                transform: translateY(-4px);
            }

            .profile-preview-card__link {
                display: block;
                text-decoration: none;
                color: inherit;
            }

            .profile-preview-card img {
                width: 100%;
                aspect-ratio: 1 / 1.4;
                object-fit: contain;
                background: color-mix(in srgb, var(--surface-input) 70%, #000 30%);
                display: block;
            }

            .profile-preview-card span {
                display: block;
                padding: 10px 12px;
                font-size: 14px;
                font-weight: 600;
            }

            .profile-preview-card__edition {
                padding: 0 12px 10px;
                margin-top: -15px;
                font-size: 12px;
                font-weight: 500;
                color: var(--subtext-color);
            }

            .profile-preview-card__subline {
                padding: 0 12px 12px;
                margin-top: -20px;
                font-size: 12px;
                font-weight: 500;
                color: var(--subtext-color);
                text-align: left;
            }

            .profile-preview-card__hover-action {
                position: absolute;
                left: 8px;
                right: 8px;
                bottom: 12px;
                opacity: 0;
                transform: translateY(8px);
                pointer-events: none;
                transition: opacity 0.18s ease, transform 0.18s ease;
                z-index: 2;
            }

            .profile-preview-card:hover .profile-preview-card__hover-action,
            .profile-preview-card:focus-within .profile-preview-card__hover-action {
                opacity: 1;
                transform: translateY(0);
                pointer-events: auto;
            }

            .profile-preview-card__hover-btn {
                width: 100%;
                border: none;
                border-radius: 10px;
                padding: 12px 14px;
                font-weight: 600;
                font-size: 1rem;
                background: #b91c1c;
                color: #fff;
                cursor: pointer;
            }

            .profile-preview-card__hover-btn:hover {
                background: #991b1b;
            }

            .profile-preview-card--faded {
                pointer-events: none;
                user-select: none;
                -webkit-mask-image: linear-gradient(to bottom, rgba(0, 0, 0, 1) 8%, rgba(0, 0, 0, 0) 42%);
                mask-image: linear-gradient(to bottom, rgba(0, 0, 0, 1) 8%, rgba(0, 0, 0, 0) 42%);
            }

            .profile-preview-card--faded:hover {
                transform: none;
            }

            .profile-preview-cta {
                margin-top: -128px;
                text-align: center;
                position: relative;
                z-index: 2;
            }

            .profile-preview-btn {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                padding: 10px 18px;
                border-radius: 8px;
                background: var(--link-hover);
                color: #fff;
                text-decoration: none;
                font-weight: 600;
                border: none;
                cursor: pointer;
            }

            .profile-preview-btn:hover {
                background: color-mix(in srgb, var(--link-hover) 85%, #000 15%);
            }
        </style>
    @endpush

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const allRoots = document.querySelectorAll('[data-preview-root]');

                const applyCompactState = function (root) {
                    if (root.dataset.expanded === '1') {
                        return;
                    }

                    const cards = Array.from(root.querySelectorAll('[data-preview-card]'));
                    if (cards.length === 0) return;

                    cards.forEach(function (card) {
                        card.hidden = false;
                        card.classList.remove('profile-preview-card--faded');
                    });

                    const firstTop = cards[0].offsetTop;
                    let columns = 0;
                    cards.forEach(function (card) {
                        if (Math.abs(card.offsetTop - firstTop) < 3) {
                            columns += 1;
                        }
                    });
                    columns = Math.max(columns, 1);

                    const thirdRowStart = columns * 2;
                    const fourthRowStart = columns * 3;

                    cards.forEach(function (card, index) {
                        if (index >= fourthRowStart) {
                            card.hidden = true;
                        } else if (index >= thirdRowStart) {
                            card.classList.add('profile-preview-card--faded');
                        }
                    });

                    const cta = root.querySelector('[data-preview-cta]');
                    if (cta) {
                        cta.hidden = cards.length <= thirdRowStart;
                    }
                };

                allRoots.forEach(function (root) {
                    applyCompactState(root);

                    const expandBtn = root.querySelector('[data-preview-expand]');
                    if (expandBtn) {
                        expandBtn.addEventListener('click', function () {
                            root.dataset.expanded = '1';
                            const cards = Array.from(root.querySelectorAll('[data-preview-card]'));
                            cards.forEach(function (card) {
                                card.hidden = false;
                                card.classList.remove('profile-preview-card--faded');
                            });
                            const cta = root.querySelector('[data-preview-cta]');
                            if (cta) cta.hidden = true;
                        });
                    }
                });

                window.addEventListener('resize', function () {
                    allRoots.forEach(function (root) {
                        applyCompactState(root);
                    });
                });
            });
        </script>
    @endpush
@endonce

@if ($rows->isEmpty())
    <p class="profile-show-empty">{{ $emptyText }}</p>
@else
    <div data-preview-root data-expanded="0">
        <div class="profile-preview-grid">
            @foreach ($rows as $row)
                @if (!empty($row['unlist_action']))
                    <article class="profile-preview-card" data-preview-card>
                        <a href="{{ $row['href'] }}" class="profile-preview-card__link">
                            <img src="{{ asset(ltrim($row['thumbnail_url'] ?? $row['image_url'], '/')) }}" alt="{{ $row['name'] }}" loading="lazy">
                            <span>{{ $row['name'] }}</span>
                            <span class="profile-preview-card__edition">{{ $row['edition_label'] }}</span>
                            @if (!empty($row['subline']))
                                <span class="profile-preview-card__subline">{{ $row['subline'] }}</span>
                            @endif
                        </a>
                        <form method="POST" action="{{ $row['unlist_action'] }}" class="profile-preview-card__hover-action" onsubmit="return confirm('Unlist this NFT edition?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="profile-preview-card__hover-btn">Unlist</button>
                        </form>
                    </article>
                @else
                    <a href="{{ $row['href'] }}" class="profile-preview-card" data-preview-card>
                        <img src="{{ asset(ltrim($row['thumbnail_url'] ?? $row['image_url'], '/')) }}" alt="{{ $row['name'] }}" loading="lazy">
                        <span>{{ $row['name'] }}</span>
                        <span class="profile-preview-card__edition">{{ $row['edition_label'] }}</span>
                        @if (!empty($row['subline']))
                            <span class="profile-preview-card__subline">{{ $row['subline'] }}</span>
                        @endif
                    </a>
                @endif
            @endforeach
        </div>

        @if ($ctaLabel)
            <div class="profile-preview-cta" data-preview-cta>
                @if ($expandInline)
                    <button type="button" class="profile-preview-btn" data-preview-expand>{{ $ctaLabel }}</button>
                @elseif ($ctaHref)
                    <a href="{{ $ctaHref }}" class="profile-preview-btn">{{ $ctaLabel }}</a>
                @endif
            </div>
        @endif
    </div>
@endif
