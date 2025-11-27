<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>Document</title>
  <link rel="stylesheet" href="{{ asset('css/App.css') }}">
</head>
<body>
<x-navbar /> 

   <div class="auth-section">
      <div class="auth-form">
        <h2>Login</h2>
        <input type="email" placeholder="Email" />
        <input type="password" placeholder="Password" />
        <button>Login</button>
        <a class="forgot-password" href="#">Forgot Password?</a>
      </div>

      <div class="auth-form">
        <h2>Register</h2>
        <input type="text" placeholder="Full Name" />
        <input type="email" placeholder="Email" />
        <input type="password" placeholder="Password" />
        <button>Register</button>
      </div>
    </div>
</body>
</html>