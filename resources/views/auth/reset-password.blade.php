@extends('layouts.app')

@section('title', 'Create New Password')

@push('styles')
  <link rel="stylesheet" href="{{ asset('css/App.css') }}">
@endpush

@section('content')
<div class="auth-page-container">
  <div class="auth-section" style="justify-content: center;">

    <div class="auth-form">
      <h2>Create New Password</h2>
      
      {{-- Error Messages --}}
      @if ($errors->any())
        <div class="error-list">
          <ul>
            @foreach ($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
        </div>
      @endif

      <form method="POST" action="{{ route('password.update') }}">
        @csrf
        {{-- The secure hidden token from the email link --}}
        <input type="hidden" name="token" value="{{ $token }}">

        {{-- Email Address (Read-only, passed automatically from the URL) --}}
        <input type="email" name="email" value="{{ $email ?? old('email') }}" required readonly style="background-color: #444; cursor: not-allowed; color: #ccc;">
        
        {{-- New Password Fields --}}
        <input type="password" name="password" placeholder="New Password" required minlength="8" autocomplete="new-password" autofocus>
        <input type="password" name="password_confirmation" placeholder="Confirm New Password" required autocomplete="new-password">
        
        <button type="submit" style="margin-top: 15px;">Reset Password</button>
      </form>
    </div>

  </div>
</div>
@endsection