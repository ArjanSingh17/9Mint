
   


@extends('layouts.app')

@section('title', 'Contact Us')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/contactUs.css') }}">
@endpush

@section('content')
      {{-- Form --}}
      <main class="contactUs-section">
        <h2>Contact Us</h2>

        <form class="contactUs-form" action="{{ route('send.email') }}" method="post">
          @csrf
            {{-- Fields --}}
            <label for="name">Name:</label>
            <input type="text" id="name" name="name" placeholder="Name" required />

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" placeholder="Email" required />

            <label for="message">Message:</label>
          <textarea
            id="message"
            name="message"
            rows="5" {{-- rows --}}
            placeholder="Message"
            required
          ></textarea>

          <button type="submit">Submit</button>
        </form>
      </main>
@endsection

