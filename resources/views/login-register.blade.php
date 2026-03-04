@extends('layouts.app')

@section('title', 'Login / Register')

@push('styles')
  @vite('resources/css/pages/app-pages.css')
@endpush

@section('content')
@php
  $showRegister = $errors->register->any();
@endphp
<div class="auth-page-container">
  <div class="auth-section {{ $showRegister ? 'show-register' : '' }}" id="auth-section">

    {{-- Login --}}
    <div class="auth-form auth-form--login">
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
        {{-- Credentials --}}
        <input type="text" name="name" placeholder="Username" value="{{ old('name') }}" required autocomplete="username">
        <input type="password" name="password" placeholder="Password" required autocomplete="current-password">
        <label class="remember">
          <input type="checkbox" name="remember" value="1"> Remember me
        </label>
        <button type="submit">Login</button>
      </form>

      <p class="auth-signup-prompt" id="auth-signup-prompt">
        Dont have an account yet?
        <a href="#" id="show-register-link">Sign up now.</a>
      </p>

      @if (session('show_forgot_password') && Route::has('password.request'))
        <a class="forgot-password" href="{{ route('password.request') }}" style="display: block; text-align: center; margin-top: 15px; color: #555; text-decoration: none; font-size: 0.9rem;">
          Forgot Password?
        </a>
      @endif
    </div>

    {{-- Register --}}
    <div class="auth-form auth-form--register" id="register-form">
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
        {{-- Fields --}}
        <input type="text" name="name" placeholder="Username" value="{{ old('name') }}" required maxlength="80" autocomplete="username" pattern="[A-Za-z0-9\-]+">
        <input type="email" name="email" placeholder="Email" value="{{ old('email') }}" required autocomplete="email">
        <input type="password" name="password" placeholder="Password" required minlength="8" autocomplete="new-password"> {{-- min length --}}
        <input type="password" name="password_confirmation" placeholder="Confirm Password" required autocomplete="new-password">
        <button type="submit">Register</button>
      </form>
    </div>
</div>
  </div>
@endsection

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function () {
    const authSection = document.getElementById('auth-section');
    const showRegisterLink = document.getElementById('show-register-link');
    const forms = Array.from(document.querySelectorAll('.auth-form form'));

    const updateAuthButtons = function () {
      forms.forEach(function (form) {
        const submitBtn = form.querySelector('button[type="submit"]');
        if (!submitBtn) return;

        const requiredFields = Array.from(form.querySelectorAll('input[required]'));
        const allFilled = requiredFields.every(function (field) {
          return field.value.trim().length > 0;
        });
        const formValid = form.checkValidity();

        submitBtn.classList.toggle('is-ready', allFilled && formValid);
      });
    };

    if (authSection && showRegisterLink) {
      showRegisterLink.addEventListener('click', function (event) {
        event.preventDefault();
        authSection.classList.add('show-register');
      });
    }

    forms.forEach(function (form) {
      form.addEventListener('input', updateAuthButtons);
      form.addEventListener('change', updateAuthButtons);
    });

    updateAuthButtons();
  });
</script>
@endpush

