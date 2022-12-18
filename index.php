<?php

// Import the Composer Autoloader to make the SDK classes accessible:
require 'vendor/autoload.php';

// Load our environment variables from the .env file:
(Dotenv\Dotenv::createImmutable(__DIR__))->load();

// Now instantiate the Auth0 class with our configuration:
$auth0 = new \Auth0\SDK\Auth0([
    'domain' => 'batchy.au.auth0.com',
    'clientId' => "p7I2w98tTDjdX70GZ9dQf6SPeupZOJHg",
    'clientSecret' => "nGrEcIo7ixjgtHO0LlpSGlIGL-IE4C1ZFf872SowuFxe5PxbAgjkPr8u5HYD3aZl",
    'cookieSecret' => "a1d8f9e56a38d3a5faa69540687f41dcd9edcb73de43b489970c3580e6404498"
]);

// 👆 We're continuing from the steps above. Append this to your index.php file.

// Import our router library:
use Steampixel\Route;

// Define route constants:
define('ROUTE_URL_INDEX', rtrim("https://batchyy.shipitmate.app", '/'));
define('ROUTE_URL_LOGIN', ROUTE_URL_INDEX . '/login');
define('ROUTE_URL_CALLBACK', ROUTE_URL_INDEX . '/callback');
define('ROUTE_URL_LOGOUT', ROUTE_URL_INDEX . '/logout');
define('DASHBOARD', ROUTE_URL_INDEX . '/dashboard');
define('BILLING', ROUTE_URL_INDEX . '/billing');

// 👆 We're continuing from the steps above. Append this to your index.php file.

Route::add('/', function() use ($auth0) {
    $session = $auth0->getCredentials();
  
    if ($session === null) {
      // The user isn't logged in.
      Header("Location: /login");
      return;
    }
  
    // The user is logged in.
    header('location: /dashboard');
  });

  Route::add('/login', function() use ($auth0) {
    // It's a good idea to reset user sessions each time they go to login to avoid "invalid state" errors, should they hit network issues or other problems that interrupt a previous login process:
    $auth0->clear();

    // Finally, set up the local application session, and redirect the user to the Auth0 Universal Login Page to authenticate.
    header("Location: " . $auth0->login(ROUTE_URL_CALLBACK));
    exit;
});

// 👆 We're continuing from the steps above. Append this to your index.php file.

Route::add('/callback', function() use ($auth0) {
    // Have the SDK complete the authentication flow:
    $auth0->exchange(ROUTE_URL_CALLBACK);

    // Finally, redirect our end user back to the / index route, to display their user profile:
    header("Location: " . ROUTE_URL_INDEX);
    exit;
});

Route::add('/dashboard', function() use ($auth0) {
  $session = $auth0->getCredentials();

  if ($session === null) {
    // The user isn't logged in.
    Header("Location: /login");
    return;
  }

  $name = $session->user['name'];

  $logged_in = true;
  // Have the SDK complete the authentication flow:
  include_once 'pages/dashboard.php';
});

Route::add('/billing', function() use ($auth0) {
  $session = $auth0->getCredentials();

  if ($session === null) {
    // The user isn't logged in.
    Header("Location: /login");
    return;
  }

  $name = $session->user['name'];
  // Have the SDK complete the authentication flow:
  include_once 'pages/billing.php';
});

// 👆 We're continuing from the steps above. Append this to your index.php file.

Route::add('/logout', function() use ($auth0) {
    // Clear the user's local session with our app, then redirect them to the Auth0 logout endpoint to clear their Auth0 session.
    header("Location: " . $auth0->logout(ROUTE_URL_INDEX));
    exit;
});

// 👆 We're continuing from the steps above. Append this to your index.php file.

// This tells our router that we've finished configuring our routes, and we're ready to begin routing incoming HTTP requests:
Route::run('/');

?>