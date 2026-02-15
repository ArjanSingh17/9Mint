<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

    <title>9Mint - @yield('title', 'Page')</title>
    <link rel="icon" href="{{ asset('images/9mint.png') }}">

    {{-- Enables React Fast Refresh when running Vite dev server (no-op in production) --}}
    @viteReactRefresh
    @vite(['resources/css/app.css', 'resources/css/layout.css', 'resources/js/app.js'])
    @stack('styles')
     @livewireStyles
</head>
<body>
    {{-- Shared top navigation bar --}}
    <header>
        <x-navbar />
    </header>
    
    {{-- Main content area --}}
    <main class="page-container">
        @isset($slot)
        {{ $slot }}
    @endisset
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
     @livewireScripts

    <script>
    document.addEventListener("DOMContentLoaded", function () {
        const toggleButton = document.getElementById("theme-toggle");
        const themeIcon = document.getElementById("theme-icon");
        const savedTheme = localStorage.getItem("theme");

        if (savedTheme === "dark") {
            document.body.classList.add("dark-mode");
            if (themeIcon) themeIcon.textContent = "Sun";
        }

        if (toggleButton) {
            toggleButton.addEventListener("click", function () {
                document.body.classList.toggle("dark-mode");
                const isDark = document.body.classList.contains("dark-mode");
                localStorage.setItem("theme", isDark ? "dark" : "light");

                if (themeIcon) {
                    themeIcon.textContent = isDark ? "sun" : <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

    <title>9Mint - @yield('title', 'Page')</title>
    <link rel="icon" href="{{ asset('images/9mint.png') }}">

    {{-- Enables React Fast Refresh when running Vite dev server (no-op in production) --}}
    @viteReactRefresh
    @vite(['resources/css/app.css', 'resources/css/layout.css', 'resources/js/app.js'])
    @stack('styles')
     @livewireStyles
</head>
<body>
    {{-- Shared top navigation bar --}}
    <header>
        <x-navbar />
    </header>
    
    {{-- Main content area --}}
    <main class="page-container">
        @isset($slot)
        {{ $slot }}
    @endisset
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
     @livewireScripts

    <script>
    document.addEventListener("DOMContentLoaded", function () {
        const toggleButton = document.getElementById("theme-toggle");
        const themeIcon = document.getElementById("theme-icon");
        const savedTheme = localStorage.getItem("theme");

        if (savedTheme === "dark") {
            document.body.classList.add("dark-mode");
            if (themeIcon) themeIcon.textContent = "🌙";
        }else {
            if (themeIcon) themeIcon.texContent = "☀️"
        }

        if (toggleButton) {
            toggleButton.addEventListener("click", function () {
                document.body.classList.toggle("dark-mode");
                const isDark = document.body.classList.contains("dark-mode");
                localStorage.setItem("theme", isDark ? "dark" : "light");
                if (themeIcon) themeIcon.textContent = isDark ? "🌙" : "☀️";
    
            });
        }
    });
    </script>
</body>
</html>
