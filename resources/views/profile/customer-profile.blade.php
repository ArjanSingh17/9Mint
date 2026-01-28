@extends('layouts.app')

@section('title', 'My Account')

@push('styles')
  <link rel="stylesheet" href="{{ asset('css/App.css') }}">
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

    {{-- Sections --}}
    <div class="profile-sections">
      {{-- Account Details Form --}}
      <div class="profile-card">
        @include('partials.update-details-form')
      </div>

      {{-- Security and Password Form --}}
      <div class="profile-card">
        @include('partials.update-password-form')
      </div>

      {{-- Activity Links --}}
      <div class="profile-card profile-activity">
        @include('partials.activity-links')
      </div>
    </div>
  </div>
@endsection