


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

      
      <main class="apology-section">
        <h2>Sorry for any inconvenience caused</h2>
        <p>
          If you have any questions or need further assistance, please feel free to
          contact our support team.
        </p>
        <p>Below are some frequently asked questions that might help you:</p>

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
          <Link to="/contact">Contact Us</Link> page.
        </p>

        <h3>4. What is your return policy?</h3>
        <p>
          We currently do not offer returns. For enquiries, visit our
          <Link to="/contact">Contact Us</Link> page.
        </p>
      </main>
    </div>
</body>
</html>
