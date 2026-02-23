@extends('layouts.app')

@section('title', $user->name . "'s Inventory")

@push('styles')
    @vite('resources/css/pages/app-pages.css')
@endpush

@section('content')
    <section class="inventory-page">
        <h1>{{ $user->name . "'s Inventory" }}</h1>
        <p>This user's inventory is private.</p>
    </section>
@endsection
