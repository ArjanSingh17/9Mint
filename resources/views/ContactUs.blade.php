
   


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>Document</title>
<link rel="stylesheet" href="{{ asset('css/contactUs.css') }}">
</head>
<body>
   <div>
     
      <x-navbar />

      
      <main class="contactUs-section">
        <h2>Contact Us</h2>

        <form class="contactUs-form" action="{{route('send.email')}}" method="post">
          @csrf
          <label htmlFor="name">Name:</label>
          <input type="text" name="name" placeholder="Name" required />

          <label htmlFor="email">Email:</label>
          <input type="email" name="email" placeholder="Email" required />

          <label htmlFor="message">Message:</label>
          <textarea
            id="message"
            name="message"
            rows="5"
            placeholder="Message"
            required
          ></textarea>

          <button type="submit">Submit</button>
        </form>
      </main>


      <footer class="footer">
        <div class="footer-links">
          <a href="/contactUs/terms">Terms & Conditions</a>
          <a href="/contactUs/faqs">FAQs</a>
        </div>
        <p>&copy; 2025 Your Company. All rights reserved.</p>
      </footer>
    </div>
</body>
</html>
