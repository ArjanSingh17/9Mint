@extends('layouts.app') 
{{-- Assuming a layout file exists --}}

@section('content')
<div class="container mx-auto p-4">
    <h1 class="text-3xl font-bold mb-6"> My Account Dashboard</h1>

    {{--  Display Status Feedback --}}
    @if (session('status'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4" role="alert">
            {{ session('status') }}
        </div>
    @endif

    {{-- Account Details Form --}}
    @include('profile.partials.update-details-form')

    <hr class="my-8">

    {{-- Security and Password Form --}}
    @include('profile.partials.update-password-form')

    <hr class="my-8">
    
    {{-- Activity Links --}}
    @include('profile.partials.activity-links')

</div>
@endsection