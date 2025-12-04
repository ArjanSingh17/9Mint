@extends('layouts.app')

@section('title', 'Login / Register')

@push('styles')
  <link rel="stylesheet" href="{{ asset('css/App.css') }}">
@endpush

@section('content')
  <div class="auth-section">

    {{-- LOGIN --}}
    <div class="auth-form">
      <h2>Login</h2>

      @if ($errors->login->any())
        <div class="error-list">
          <ul>
            @foreach ($errors->login->all() as $e)
              <li>{{ $e }}</li>
            @endforeach
          </ul>
        </div>
      @endif

      <form method="POST" action="{{ url('/login') }}">
        @csrf
        <input type="email" name="email" placeholder="Email" value="{{ old('email') }}" required autocomplete="email">
        <input type="password" name="password" placeholder="Password" required autocomplete="current-password">
        <label class="remember">
          <input type="checkbox" name="remember" value="1"> Remember me
        </label>
        <button type="submit">Login</button>
      </form>

      <a class="forgot-password" href="#">Forgot Password?</a>
    </div>

    {{-- REGISTER --}}
    <div class="auth-form">
      <h2>Register</h2>

      @if ($errors->register->any())
        <div class="error-list">
          <ul>
            @foreach ($errors->register->all() as $e)
              <li>{{ $e }}</li>
            @endforeach
          </ul>
        </div>
      @endif

      <form method="POST" action="{{ url('/register') }}">
        @csrf
        <input type="text" name="name" placeholder="Full Name" value="{{ old('name') }}" required maxlength="80" autocomplete="name">
        <input type="email" name="email" placeholder="Email" value="{{ old('email') }}" required autocomplete="email">
        <input type="password" name="password" placeholder="Password" required minlength="8" autocomplete="new-password">
        <input type="password" name="password_confirmation" placeholder="Confirm Password" required autocomplete="new-password">
        <button type="submit">Register</button>
      </form>
    </div>

  </div>
@endsection

