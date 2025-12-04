

@extends('layouts.app')

@section('title', 'Terms and Conditions')

@push('styles')
 <link rel="stylesheet" href="{{ asset('css/contactUs.css') }}">
@endpush

@section('content')
      <main class="terms-section">
        <h2>Terms and Conditions</h2>
        <p>
          Welcome to Our Website. By accessing or using our website, you agree
          to comply with and be bound by the following terms and conditions.
        </p>

        <h3>1. Use of Website</h3>
        <p>
          You agree to use the website only for lawful purposes and in a way
          that does not infringe the rights of others.
        </p>

        <h3>2. Intellectual Property</h3>
        <p>
          All content on this website is the property of Our Company and
          protected by law.
        </p>

        <h3>3. Limitation of Liability</h3>
        <p>
          We are not liable for damages arising from your use of the website.
        </p>

        <h3>4. Changes to Terms</h3>
        <p>
          We may update these terms at any time. Continued use means you accept
          the new terms.
        </p>

        <h3>5. Governing Law</h3>
        <p>
          These terms follow the laws of your jurisdiction and you agree to the
          authority of those courts.
        </p>

        <p>
          If you have more questions, click
            <a href="/contactUs/faqs" class="FAQs">
            here
            </a>
          to view the FAQs.
        </p>
      </main>
@endsection
