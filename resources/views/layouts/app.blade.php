<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>9Mint NFT Shop - @yield('title', 'Profile')</title>
    <style>
        body { font-family: sans-serif; background-color: #f4f4f4; }
        .container { max-width: 1200px; margin: 0 auto; padding: 20px; }
    </style>
</head>
<body>
    <header style="background-color: #312e81; color: white; padding: 15px;">
        <nav class="container flex justify-between">
            <a href="/" style="font-size: 1.5em; font-weight: bold;">9Mint NFT E-Commerce</a>
            <a href="{{ route('profile.show') }}">My Profile</a>
        </nav>
    </header>

    <main class="container">
        @yield('content') 
    </main>

    <footer style="text-align: center; margin-top: 50px; padding: 20px; border-top: 1px solid #ccc;">
        &copy; 2025 9Mint. Handle customer data securely.
    </footer>
</body>
</html>