<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

    <title>9Mint - @yield('title', 'Page')</title>
    <link rel="icon" href="{{ asset('images/9mint.png') }}">

    {{-- Global stylesheet for the whole site --}}
    <link rel="stylesheet" href="{{ asset('css/styles.css') }}">

    <style>
        body {
            font-family: sans-serif;
            margin: 0;
            overflow-x: hidden;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .page-container {
            max-width: 1200px;
            width: 100%;
            margin: 0 auto;
            padding: 16px;
            box-sizing: border-box;
            flex: 1 0 auto;
        }
        .site-footer {
            text-align: center;
            padding: 20px;
            border-top: 1px solid #ccc;
            font-size: 0.9rem;
            color: #555;
            background-color: #ffffff;
            width: 100%;
            box-sizing: border-box;
            flex-shrink: 0;
        }
        .site-footer a {
            color: inherit;
            text-decoration: underline;
            margin: 0 6px;
        }
    </style>

    @stack('styles')
</head>
<body>
    {{-- Shared top navigation bar --}}
    <header>
        <x-navbar />
    </header>

    {{-- Main content area --}}
    <main class="page-container">
        @yield('content') 
    </main>

    {{-- Shared footer --}}
    <footer class="site-footer">
        &copy; {{ date('Y') }} 9Mint. All rights reserved.
        <span>|</span>
        <a href="/contactUs/terms">Terms &amp; Conditions</a>
        <span>|</span>
        <a href="/contactUs/faqs">FAQs</a>
        <span>|</span>
        <a href="/contactUs">Contact Us</a>
    </footer>

    @stack('scripts')
</body>
</html>
