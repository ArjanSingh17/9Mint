@extends('layouts.app')

@section('title', 'My Account')

@push('styles')
  @vite('resources/css/pages/app-pages.css')
@endpush

@section('content')
  {{-- Dashboard --}}
  <div class="profile-page">
    <h1 class="profile-title">My Account Dashboard</h1>

    {{--  Display Status Feedback --}}
    @if (session('status'))
      <div class="profile-status">
        {{ session('status') }}
      </div>
    @endif

    <div class="profile-layout">
      <div class="profile-main">
        {{-- Account Customization --}}
        <div class="profile-card">
          @include('partials.update-customization-form')
        </div>

        {{-- Account Details --}}
        <div class="profile-card">
          @include('partials.update-details-form')
        </div>
      </div>

      <div class="profile-side">
        @include('partials.activity-links')
      </div>
    </div>
  </div>
@endsection