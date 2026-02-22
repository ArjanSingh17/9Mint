@extends('layouts.app')

@section('title', 'Forgot Password')

@push('styles')
  <link rel="stylesheet" href="{{ asset('css/App.css') }}">
@endpush

@section('content')
<div class="auth-page-container">
  <div class="auth-section" style="justify-content: center;">

    <div class="auth-form">
      <h2>Reset Password</h2>
      <p style="text-align: center; margin-bottom: 20px; color: #666; font-size: 0.9rem;">
        Enter your email address and we will send you a link to reset your password.
      </p>

      {{-- Success Message --}}
      @if (session('status'))
        <div class="error-list" style="background-color: #d4edda; color: #155724; border-color: #c3e6cb; padding: 10px; margin-bottom: 15px; border-radius: 5px;">
          {{ session('status') }}
        </div>
      @endif

      {{-- Error Message --}}
      @if ($errors->any())
        <div class="error-list">
          <ul>
            @foreach ($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
        </div>
      @endif

      <form method="POST" action="{{ route('password.email') }}">
        @csrf
        <input type="email" name="email" placeholder="Email Address" value="{{ old('email') }}" required autofocus autocomplete="email">
        
        <button type="submit" style="margin-top: 10px;">Send Reset Link</button>
      </form>

      <a class="forgot-password" href="{{ url('/login') }}" style="display: block; text-align: center; margin-top: 15px; color: #555; text-decoration: none; font-size: 0.9rem;">
        Back to Login
      </a>
    </div>

  </div>
</div>
@endsection