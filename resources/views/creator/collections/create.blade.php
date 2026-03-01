@extends('layouts.app')

@section('title', 'Create Collection')

@push('styles')
  @vite('resources/css/pages/app-pages.css')
  <style>
    .creator-nft-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
      gap: 16px;
      align-items: start;
    }

    .creator-collection-details {
      display: grid;
      grid-template-columns: minmax(220px, 300px) 1fr;
      gap: 18px;
      align-items: start;
    }

    .creator-cover-preview {
      position: relative;
      width: 100%;
      aspect-ratio: 1 / 1.4;
      border-radius: 10px;
      border: 1px solid var(--border-soft);
      background: color-mix(in srgb, var(--surface-input) 70%, #000 30%);
      display: flex;
      align-items: center;
      justify-content: center;
      overflow: hidden;
      color: var(--text-secondary);
      font-size: 0.9rem;
      text-align: center;
      padding: 8px;
      cursor: pointer;
    }

    .creator-cover-preview img {
      width: 100%;
      height: 100%;
      object-fit: contain;
      display: block;
    }

    .creator-cover-media {
      width: 100%;
      height: 100%;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .creator-cover-remove {
      position: absolute;
      top: 0px;
      right: 8px;
      border: none;
      background: transparent;
      color: var(--text-secondary);
      width: auto;
      height: auto;
      border-radius: 0;
      display: none;
      font-size: 18px;
      line-height: 1;
      cursor: pointer;
      z-index: 2;
    }

    .creator-cover-remove:hover {
      color: var(--danger);
    }

    .creator-cover-preview:hover {
      border-color: var(--link-hover);
    }

    .creator-cover-preview:focus-visible {
      outline: 2px solid var(--link-hover);
      outline-offset: 2px;
    }

    .creator-cover-image-input {
      display: none;
    }

    @media (max-width: 860px) {
      .creator-collection-details {
        grid-template-columns: 1fr;
      }
    }

    .creator-nft-card {
      position: relative;
      background: var(--surface-panel);
      border: 1px solid var(--border-soft);
      border-radius: 12px;
      padding: 12px;
      box-shadow: var(--shadow-elevated);
    }

    .creator-nft-remove {
      position: absolute;
      top: 0px;
      right: 8px;
      border: none;
      background: transparent;
      color: var(--text-secondary);
      font-size: 18px;
      line-height: 1;
      cursor: pointer;
    }

    .creator-nft-remove:hover {
      color: var(--danger);
    }

    .creator-nft-preview {
      width: 100%;
      aspect-ratio: 1 / 1.4;
      border-radius: 10px;
      border: 1px solid var(--border-soft);
      background: color-mix(in srgb, var(--surface-input) 70%, #000 30%);
      display: flex;
      align-items: center;
      justify-content: center;
      overflow: hidden;
      margin-bottom: 10px;
      color: var(--text-secondary);
      font-size: 0.85rem;
      text-align: center;
      padding: 8px;
      cursor: pointer;
    }

    .creator-nft-preview img {
      width: 100%;
      height: 100%;
      object-fit: contain;
      display: block;
    }

    .creator-nft-preview:hover {
      border-color: var(--link-hover);
    }

    .creator-nft-preview:focus-visible {
      outline: 2px solid var(--link-hover);
      outline-offset: 2px;
    }

    .creator-nft-image-input {
      display: none;
    }

    .creator-nft-add {
      min-height: 420px;
      border: 2px dashed var(--border-soft);
      border-radius: 12px;
      background: transparent;
      color: var(--text-main);
      cursor: pointer;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      gap: 8px;
      font-weight: 600;
      font-size: 1rem;
    }

    .creator-nft-add:hover {
      border-color: var(--link-hover);
      color: var(--link-hover);
    }

    .creator-nft-add__plus {
      font-size: 46px;
      line-height: 1;
    }

    .creator-nft-price-input {
      display: flex;
      align-items: center;
      border: 1px solid var(--border-input);
      border-radius: 8px;
      background: var(--surface-input);
      overflow: hidden;
    }

    .creator-nft-price-prefix {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      min-width: 34px;
      padding: 0 8px;
      color: var(--text-secondary);
      border-right: 1px solid var(--border-input);
      font-weight: 600;
      font-size: 0.9rem;
      line-height: 1;
    }

    .creator-nft-price-input input {
      border: none !important;
      border-radius: 0 !important;
      background: transparent !important;
      margin: 0 !important;
      width: 100%;
    }

    .creator-nft-price-input input:focus {
      outline: none;
      box-shadow: none;
    }

    .creator-form input,
    .creator-form textarea,
    .creator-form select {
      color: var(--text-main);
    }

    .creator-form input::placeholder,
    .creator-form textarea::placeholder {
      color: var(--text-secondary);
      opacity: 1;
    }

    .creator-submit-btn {
      width: 100%;
      margin-top: 10px;
      padding: 12px 16px;
      border: none;
      border-radius: 8px;
      background: color-mix(in srgb, var(--surface-input) 70%, #000 30%);
      color: var(--text-main);
      font-weight: 600;
      cursor: pointer;
      transition: background 0.2s ease, color 0.2s ease;
    }

    .creator-submit-btn.is-ready {
      background: var(--link-hover);
      color: #fff;
    }

    .creator-submit-btn.is-ready:hover {
      background: color-mix(in srgb, var(--link-hover) 85%, #000 15%);
    }
  </style>
@endpush

@php
  $creatorCryptoCurrencies = array_map('strtoupper', config('pricing.crypto_currencies', ['BTC', 'ETH']));
@endphp

@push('scripts')
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const nftGrid = document.querySelector('[data-nft-grid]');
      const addTile = document.querySelector('[data-add-nft]');
      const currencySelect = document.querySelector('#ref_currency');
      const coverInput = document.querySelector('#cover_image');
      const coverPreview = document.querySelector('[data-cover-preview]');
      const coverMedia = document.querySelector('[data-cover-media]');
      const coverRemoveBtn = document.querySelector('[data-cover-remove]');
      const createForm = document.querySelector('[data-creator-form]');
      const submitBtn = document.querySelector('[data-creator-submit]');
      if (!nftGrid || !addTile || !currencySelect || !createForm || !submitBtn) return;

      const currencySymbols = @json(config('pricing.currency_symbols', ['GBP' => '£']));
      const cryptoCurrencies = @json($creatorCryptoCurrencies);
      let currentCurrencyCode = (currencySelect.value || 'GBP').toUpperCase();

      const getCurrencyContext = function () {
        const code = (currencySelect.value || 'GBP').toUpperCase();
        const symbol = currencySymbols[code] || code;
        return { code, symbol };
      };

      const bindPreview = function (card) {
        const fileInput = card.querySelector('[data-nft-image]');
        const previewWrap = card.querySelector('[data-nft-preview]');
        if (!fileInput || !previewWrap) return;

        previewWrap.addEventListener('click', function () {
          fileInput.click();
        });

        previewWrap.addEventListener('keydown', function (event) {
          if (event.key === 'Enter' || event.key === ' ') {
            event.preventDefault();
            fileInput.click();
          }
        });

        fileInput.addEventListener('change', function () {
          const file = fileInput.files && fileInput.files[0];
          if (!file) {
            previewWrap.innerHTML = '<span>Select image</span>';
            return;
          }

          const reader = new FileReader();
          reader.onload = function (event) {
            previewWrap.innerHTML = `<img src="${event.target?.result || ''}" alt="NFT preview">`;
          };
          reader.readAsDataURL(file);
        });
      };

      const bindCoverPreview = function () {
        if (!coverInput || !coverPreview || !coverMedia) return;
        let coverCycleTimer = null;
        let explicitCoverSrc = null;

        const stopCoverCycle = function () {
          if (coverCycleTimer) {
            clearInterval(coverCycleTimer);
            coverCycleTimer = null;
          }
        };

        const getNftPreviewSources = function () {
          return Array.from(nftGrid.querySelectorAll('[data-nft-preview] img'))
            .map(function (img) { return img.getAttribute('src'); })
            .filter(Boolean);
        };

        const setCoverPreview = function (src) {
          if (src) {
            coverMedia.innerHTML = `<img src="${src}" alt="Collection cover preview">`;
          } else {
            coverMedia.innerHTML = '<span>Select image</span>';
          }
        };

        const refreshCoverPresentation = function () {
          stopCoverCycle();
          if (explicitCoverSrc) {
            setCoverPreview(explicitCoverSrc);
            if (coverRemoveBtn) coverRemoveBtn.style.display = 'inline-flex';
            return;
          }

          if (coverRemoveBtn) coverRemoveBtn.style.display = 'none';
          const nftSources = getNftPreviewSources();
          if (nftSources.length === 0) {
            setCoverPreview('');
            return;
          }
          if (nftSources.length === 1) {
            setCoverPreview(nftSources[0]);
            return;
          }

          let index = 0;
          setCoverPreview(nftSources[index]);
          coverCycleTimer = setInterval(function () {
            const latestSources = getNftPreviewSources();
            if (latestSources.length < 2) {
              refreshCoverPresentation();
              return;
            }
            index = (index + 1) % latestSources.length;
            setCoverPreview(latestSources[index]);
          }, 3000);
        };

        coverPreview.addEventListener('click', function () {
          coverInput.click();
        });

        coverPreview.addEventListener('keydown', function (event) {
          if (event.key === 'Enter' || event.key === ' ') {
            event.preventDefault();
            coverInput.click();
          }
        });

        coverInput.addEventListener('change', function () {
          const file = coverInput.files && coverInput.files[0];
          if (!file) {
            explicitCoverSrc = null;
            refreshCoverPresentation();
            return;
          }

          const reader = new FileReader();
          reader.onload = function (event) {
            explicitCoverSrc = String(event.target?.result || '');
            refreshCoverPresentation();
          };
          reader.readAsDataURL(file);
        });

        if (coverRemoveBtn) {
          coverRemoveBtn.addEventListener('click', function (event) {
            event.preventDefault();
            event.stopPropagation();
            coverInput.value = '';
            explicitCoverSrc = null;
            refreshCoverPresentation();
          });
        }

        addTile.addEventListener('click', function () {
          if (!explicitCoverSrc) {
            setTimeout(refreshCoverPresentation, 0);
          }
        });

        nftGrid.addEventListener('click', function () {
          if (!explicitCoverSrc) {
            setTimeout(refreshCoverPresentation, 0);
          }
        });

        nftGrid.addEventListener('change', function (event) {
          if (event.target && event.target.matches('[data-nft-image]') && !explicitCoverSrc) {
            setTimeout(refreshCoverPresentation, 0);
          }
        });

        refreshCoverPresentation();
      };

      const buildNftCard = function (index, values) {
        const wrapper = document.createElement('div');
        wrapper.className = 'creator-nft-card';
        wrapper.setAttribute('data-nft-card', '');
        wrapper.innerHTML = `
          <button type="button" class="creator-nft-remove" data-remove-nft aria-label="Remove NFT">❌</button>
          <h3 class="text-xl font-medium mb-2" data-nft-title>NFT ${index + 1}</h3>
          <div class="creator-nft-preview" data-nft-preview role="button" tabindex="0" aria-label="Select NFT image"><span>Select image</span></div>
          <p class="text-xs text-gray-500 mt-1">Preferred ratio: 1:1.4</p>
          <input data-nft-image id="nft_image_${index}" type="file" name="nfts[${index}][image]" accept="image/*" required class="creator-nft-image-input">

          <label class="block font-medium text-gray-700" data-label-name for="nft_name_${index}">NFT Name</label>
          <input data-nft-name id="nft_name_${index}" type="text" name="nfts[${index}][name]" placeholder="NFT Name" value="${values.name || ''}" required>

          <label class="block font-medium text-gray-700 mt-3" data-label-description for="nft_description_${index}">NFT Description</label>
          <textarea data-nft-description id="nft_description_${index}" name="nfts[${index}][description]" placeholder="NFT Description" class="themed-input w-full rounded-md p-3">${values.description || ''}</textarea>

          <label class="block font-medium text-gray-700 mt-3" data-label-editions for="nft_editions_${index}">Editions Total</label>
          <input data-nft-editions id="nft_editions_${index}" type="number" name="nfts[${index}][editions_total]" min="1" value="${values.editions_total || 1}" required class="themed-input w-full rounded-md p-2">

          <label class="block font-medium text-gray-700 mt-3" data-label-price for="nft_price_${index}">Price</label>
          <div class="creator-nft-price-input">
            <span class="creator-nft-price-prefix" data-price-prefix>£</span>
            <input data-nft-price id="nft_price_${index}" type="number" step="0.01" min="0.01" name="nfts[${index}][ref_amount]" placeholder="e.g. 29.99" value="${values.ref_amount || ''}" required class="themed-input w-full rounded-md p-2">
          </div>
        `;

        bindPreview(wrapper);
        return wrapper;
      };

      const refreshNftCards = function () {
        const cards = Array.from(nftGrid.querySelectorAll('[data-nft-card]'));
        cards.forEach(function (card, index) {
          const title = card.querySelector('[data-nft-title]');
          if (title) title.textContent = `NFT ${index + 1}`;

          const nameInput = card.querySelector('[data-nft-name]');
          const descInput = card.querySelector('[data-nft-description]');
          const editionsInput = card.querySelector('[data-nft-editions]');
          const imageInput = card.querySelector('[data-nft-image]');
          const priceInput = card.querySelector('[data-nft-price]');

          const nameLabel = card.querySelector('[data-label-name]');
          const descLabel = card.querySelector('[data-label-description]');
          const editionsLabel = card.querySelector('[data-label-editions]');
          const priceLabel = card.querySelector('[data-label-price]');

          if (nameInput) { nameInput.name = `nfts[${index}][name]`; nameInput.id = `nft_name_${index}`; }
          if (descInput) { descInput.name = `nfts[${index}][description]`; descInput.id = `nft_description_${index}`; }
          if (editionsInput) { editionsInput.name = `nfts[${index}][editions_total]`; editionsInput.id = `nft_editions_${index}`; }
          if (imageInput) { imageInput.name = `nfts[${index}][image]`; imageInput.id = `nft_image_${index}`; }
          if (priceInput) { priceInput.name = `nfts[${index}][ref_amount]`; priceInput.id = `nft_price_${index}`; }

          if (nameLabel) nameLabel.htmlFor = `nft_name_${index}`;
          if (descLabel) descLabel.htmlFor = `nft_description_${index}`;
          if (editionsLabel) editionsLabel.htmlFor = `nft_editions_${index}`;
          if (priceLabel) priceLabel.htmlFor = `nft_price_${index}`;

          const removeBtn = card.querySelector('[data-remove-nft]');
          if (removeBtn) {
            if (index === 0) {
              removeBtn.disabled = true;
              removeBtn.style.display = 'none';
            } else {
              removeBtn.disabled = false;
              removeBtn.style.display = 'inline-flex';
            }
          }
        });
      };

      const refreshPricePresentation = function () {
        const context = getCurrencyContext();
        const isCrypto = cryptoCurrencies.includes(context.code);
        const placeholder = isCrypto ? 'e.g. 0.00500000' : 'e.g. 29.99';
        const cards = Array.from(nftGrid.querySelectorAll('[data-nft-card]'));
        cards.forEach(function (card) {
          const prefix = card.querySelector('[data-price-prefix]');
          const input = card.querySelector('[data-nft-price]');
          if (prefix) prefix.textContent = context.symbol;
          if (input) input.placeholder = placeholder;
        });
      };

      const refreshSubmitState = function () {
        const cardCount = nftGrid.querySelectorAll('[data-nft-card]').length;
        const hasMinimumCards = cardCount >= 5;

        const requiredFields = Array.from(createForm.querySelectorAll('input[required], select[required], textarea[required]'));
        const allRequiredFilled = requiredFields.every(function (field) {
          if (field.type === 'file') {
            return !!(field.files && field.files.length > 0);
          }
          return String(field.value || '').trim().length > 0;
        });

        const isReady = hasMinimumCards && allRequiredFilled && createForm.checkValidity();
        submitBtn.classList.toggle('is-ready', isReady);
      };

      const convertPriceValues = async function (fromCode, toCode) {
        if (!fromCode || !toCode || fromCode === toCode) return;

        const url = `/api/v1/convert?amount=1&from=${encodeURIComponent(fromCode)}&to=${encodeURIComponent(toCode)}`;
        const response = await fetch(url, { method: 'GET', headers: { 'Accept': 'application/json' } });
        if (!response.ok) {
          throw new Error('Unable to load conversion rate');
        }

        const payload = await response.json();
        const rate = Number(payload?.data?.fx_rate || 0);
        if (!Number.isFinite(rate) || rate <= 0) {
          throw new Error('Invalid conversion rate');
        }

        const decimals = cryptoCurrencies.includes(toCode) ? 8 : 2;
        const multiplier = Math.pow(10, decimals);

        nftGrid.querySelectorAll('[data-nft-price]').forEach(function (input) {
          const raw = Number.parseFloat(String(input.value || '').replace(',', '.'));
          if (!Number.isFinite(raw) || raw <= 0) return;
          const converted = Math.round((raw * rate) * multiplier) / multiplier;
          input.value = converted.toFixed(decimals).replace(/\.?0+$/, '');
        });
      };

      addTile.addEventListener('click', function () {
        const count = nftGrid.querySelectorAll('[data-nft-card]').length;
        const card = buildNftCard(count, {});
        nftGrid.insertBefore(card, addTile);
        refreshNftCards();
        refreshPricePresentation();
        refreshSubmitState();
      });

      nftGrid.addEventListener('click', function (event) {
        const removeBtn = event.target.closest('[data-remove-nft]');
        if (!removeBtn) return;

        const card = removeBtn.closest('[data-nft-card]');
        if (!card) return;

        const cards = Array.from(nftGrid.querySelectorAll('[data-nft-card]'));
        if (cards.indexOf(card) === 0) return;

        card.remove();
        refreshNftCards();
        refreshPricePresentation();
        refreshSubmitState();
      });

      Array.from(nftGrid.querySelectorAll('[data-nft-card]')).forEach(bindPreview);
      bindCoverPreview();
      refreshNftCards();
      refreshPricePresentation();
      refreshSubmitState();

      currencySelect.addEventListener('change', async function () {
        const nextCode = (currencySelect.value || 'GBP').toUpperCase();
        const previousCode = currentCurrencyCode;

        try {
          await convertPriceValues(previousCode, nextCode);
          currentCurrencyCode = nextCode;
        } catch (error) {
          currencySelect.value = previousCode;
          alert('Could not convert prices right now. Please try again.');
        }

        refreshPricePresentation();
        refreshSubmitState();
      });

      createForm.addEventListener('input', refreshSubmitState);
      createForm.addEventListener('change', refreshSubmitState);
    });
  </script>
@endpush

@section('content')
  <div class="profile-page">
    <h1 class="profile-title">Create a Collection</h1>

    @if (session('status'))
      <div class="profile-status">
        {{ session('status') }}
      </div>
    @endif

    <div class="profile-sections">
      <div class="profile-card">
        <p class="text-gray-600 mb-6">Set your collection details and add your NFT cards. The collection is saved as a draft first, then only submitted for admin review after the £80 checkout is completed.</p>

        @if ($errors->any())
          <div class="error-list">
            <ul>
              @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
              @endforeach
            </ul>
          </div>
        @endif

        <form method="POST" action="{{ route('creator.collections.store') }}" enctype="multipart/form-data" data-creator-form class="creator-form">
          @csrf
          @php
            $enabledCurrencies = array_map('strtoupper', config('pricing.enabled_currencies', ['GBP']));
          @endphp

          <section>
            <h2 class="text-2xl font-semibold mb-4">Collection Details</h2>
            <div class="creator-collection-details">
              <div>
                <label class="block font-medium text-gray-700 mb-2">Cover image (optional)</label>
                <div class="creator-cover-preview" data-cover-preview role="button" tabindex="0" aria-label="Select collection cover image">
                  <div class="creator-cover-media" data-cover-media><span>Select image</span></div>
                  <button type="button" class="creator-cover-remove" data-cover-remove aria-label="Remove cover image">❌</button>
                </div>
                <input id="cover_image" type="file" name="cover_image" accept="image/*" class="creator-cover-image-input">
                <p class="text-xs text-gray-500 mt-2">If left empty, the collection cover will cycle through the collection's NFT images.</p>
                <p class="text-xs text-gray-500 mt-1">Preferred ratio: 1:1.4</p>
              </div>

              <div>
                <label for="collection_name" class="block font-medium text-gray-700">Collection Name</label>
                <input id="collection_name" type="text" name="name" value="{{ old('name') }}" placeholder="Collection Name" required>

                <label for="collection_description" class="block font-medium text-gray-700 mt-3">Description</label>
                <textarea id="collection_description" name="description" placeholder="Description" class="themed-input w-full rounded-md p-3">{{ old('description') }}</textarea>

                <label for="ref_currency" class="block font-medium text-gray-700 mt-3">Currency Reference</label>
                <select id="ref_currency" name="ref_currency" class="themed-input w-full rounded-md p-2" required>
                  @foreach ($enabledCurrencies as $currency)
                    <option value="{{ $currency }}" @selected(old('ref_currency', 'GBP') === $currency)>{{ $currency }}</option>
                  @endforeach
                </select>
                <p class="text-xs text-gray-500 mt-1">Currency Reference sets the base market value for every NFT in this collection. If you change it, entered prices are converted automatically, so pricing stays aligned when exchange rates, crypto markets, or inflation shift over time.</p>
              </div>
            </div>
          </section>

          <section class="mt-6">
            <h2 class="text-2xl font-semibold mb-1">NFT Details</h2>
            <p class="text-xs text-gray-500 mb-4" style="text-align: left;">Minimum of 5 NFTs to create a collection.</p>
            @php
              $oldNfts = old('nfts', []);
              $initialNftCount = max(1, count($oldNfts));
            @endphp

            <div class="creator-nft-grid" data-nft-grid>
              @for ($i = 0; $i < $initialNftCount; $i++)
                <div class="creator-nft-card" data-nft-card>
                  <button type="button" class="creator-nft-remove" data-remove-nft aria-label="Remove NFT">❌</button>
                  <h3 class="text-xl font-medium mb-2" data-nft-title>NFT {{ $i + 1 }}</h3>
                  <div class="creator-nft-preview" data-nft-preview role="button" tabindex="0" aria-label="Select NFT image"><span>Select image</span></div>
                  <p class="text-xs text-gray-500 mt-1">Preferred ratio: 1:1.4</p>
                  <input data-nft-image id="nft_image_{{ $i }}" type="file" name="nfts[{{ $i }}][image]" accept="image/*" required class="creator-nft-image-input">

                  <label for="nft_name_{{ $i }}" class="block font-medium text-gray-700" data-label-name>NFT Name</label>
                  <input data-nft-name id="nft_name_{{ $i }}" type="text" name="nfts[{{ $i }}][name]" value="{{ old('nfts.'.$i.'.name') }}" placeholder="NFT Name" required>

                  <label for="nft_description_{{ $i }}" class="block font-medium text-gray-700 mt-3" data-label-description>NFT Description</label>
                  <textarea data-nft-description id="nft_description_{{ $i }}" name="nfts[{{ $i }}][description]" placeholder="NFT Description" class="themed-input w-full rounded-md p-3">{{ old('nfts.'.$i.'.description') }}</textarea>

                  <label for="nft_editions_{{ $i }}" class="block font-medium text-gray-700 mt-3" data-label-editions>Editions Total</label>
                  <input data-nft-editions id="nft_editions_{{ $i }}" type="number" name="nfts[{{ $i }}][editions_total]" value="{{ old('nfts.'.$i.'.editions_total', 1) }}" min="1" required class="themed-input w-full rounded-md p-2">

                  <label for="nft_price_{{ $i }}" class="block font-medium text-gray-700 mt-3" data-label-price>Price</label>
                  <div class="creator-nft-price-input">
                    <span class="creator-nft-price-prefix" data-price-prefix>£</span>
                    <input data-nft-price id="nft_price_{{ $i }}" type="number" step="0.01" min="0.01" name="nfts[{{ $i }}][ref_amount]" value="{{ old('nfts.'.$i.'.ref_amount') }}" placeholder="e.g. 29.99" required class="themed-input w-full rounded-md p-2">
                  </div>
                </div>
              @endfor

              <button type="button" class="creator-nft-add" data-add-nft>
                <span class="creator-nft-add__plus">+</span>
                <span>Add new NFT</span>
              </button>
            </div>
          </section>

          <button type="submit" class="creator-submit-btn" data-creator-submit>Continue to checkout</button>
        </form>
      </div>
    </div>
  </div>
@endsection
