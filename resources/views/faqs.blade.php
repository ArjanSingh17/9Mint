


@extends('layouts.app')

@section('title', 'FAQs')

@push('styles')
  @vite('resources/css/pages/about-contact.css')
@endpush

@section('content')
      {{-- Intro --}}
      <main class="apology-section">
        <h2>Sorry for any inconvenience caused</h2>
        <p>
          If you have any questions or need further assistance, please feel free to
          contact our support team.
        </p>
        <p>Below are some frequently asked questions that might help you:</p>

        {{-- FAQs --}}
        <h2>Frequently Asked Questions (FAQs)</h2>

        <h3>1. How can I reset my password?</h3>
        <p>
          You can reset your password by clicking the
          <strong>“Forgot Password”</strong> link on the login page.
        </p>

        <h3>2. Where can I find my order history?</h3>
        <p>
          Your order history can be found in your <strong>Account</strong> under
          <strong>Orders</strong>.
        </p>

        <h3>3. How do I contact customer support?</h3>
        <p>
          You can contact our team through the
            <a href="/contactUs">Contact Us</a> page.
        </p>

        <h3>4. What is your return policy?</h3>
        <p>
          We currently do not offer returns. For enquiries, visit our
            <a href="/contactUs">Contact Us</a> page.
        </p>
      </main>
@endsection

