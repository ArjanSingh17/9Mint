@extends('layouts.app')

@section('title', $collection->name)

@push('styles')
    {{-- Per-collection styles if needed --}}
    @if ($collection->slug === 'glossy-collection')
        <link rel="stylesheet" href="{{ asset('css/Glossy-collection.css') }}">
    @elseif ($collection->slug === 'superhero-collection')
        <link rel="stylesheet" href="{{ asset('css/Superhero.css?v=' . time()) }}">
    @endif
@endpush

@push('scripts')
    {{-- Shared size-selection JS for all collections --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.size-option').forEach(sizeContainer => {
                const sizeButtons = sizeContainer.querySelectorAll('button');
                const nftInfo = sizeContainer.closest('.nft-info');
                const form = nftInfo ? nftInfo.querySelector('form') : null;

                if (form) {
                    let sizeInput = form.querySelector('input[name="size"]');
                    if (!sizeInput) {
                        sizeInput = document.createElement('input');
                        sizeInput.type = 'hidden';
                        sizeInput.name = 'size';
                        form.appendChild(sizeInput);
                    }

                    sizeButtons.forEach(button => {
                        button.addEventListener('click', function(e) {
                            e.preventDefault();
                            sizeButtons.forEach(btn => btn.classList.remove('selected'));
                            this.classList.add('selected');
                            sizeInput.value = this.textContent.trim().toLowerCase();
                        });
                    });

                    form.addEventListener('submit', function(e) {
                        if (!sizeInput.value) {
                            e.preventDefault();
                            alert('Please select a size before adding to basket');
                        }
                    });
                }
            });
        });
    </script>
@endpush

@section('content')
    <h1 class="collection-title">{{ $collection->name }}</h1>

    @if ($nfts->isEmpty())
        <p class="no-nfts">
            No NFTs have been added to this collection yet.
        </p>
    @else
        @foreach ($nfts as $nft)
            <x-nft-card
                :image="$nft->image_url"
                :title="$nft->name"
                :description="$nft->description"
                :slug="$nft->slug"
                :editions-total="$nft->editions_total"
                :editions-remaining="$nft->editions_remaining"
            />
        @endforeach
    @endif
@endsection


