<?php

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/mailer.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method Not Allowed');
}


/*
|--------------------------------------------------------------------------
| Get form data
|--------------------------------------------------------------------------
*/

$uid = (int)($_POST['portfolio_user_id'] ?? 0);

$name = trim($_POST['name'] ?? '');

$email = trim($_POST['email'] ?? '');

$message = trim($_POST['message'] ?? '');


/*
|--------------------------------------------------------------------------
| Validate portfolio
|--------------------------------------------------------------------------
*/

if (!$uid) {
    http_response_code(400);
    exit('Invalid portfolio.');
}

$user = get_user($uid);

$profile = get_profile($uid);

if (!$user || !$profile) {
    http_response_code(404);
    exit('Portfolio not found.');
}


/*
|--------------------------------------------------------------------------
| Make sure portfolio is public
|--------------------------------------------------------------------------
*/

if (!$profile['portfolio_public']) {
    http_response_code(404);
    exit('Portfolio not found.');
}


/*
|--------------------------------------------------------------------------
| Validate visitor information
|--------------------------------------------------------------------------
*/

if ($name === '') {
    http_response_code(400);
    exit('Please enter your name.');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    exit('Please enter a valid email address.');
}

if ($message === '') {
    http_response_code(400);
    exit('Please enter your message.');
}


/*
|--------------------------------------------------------------------------
| Limit input
|--------------------------------------------------------------------------
*/

if (mb_strlen($name) > 100) {
    http_response_code(400);
    exit('Name is too long.');
}

if (mb_strlen($message) > 5000) {
    http_response_code(400);
    exit('Message is too long.');
}


/*
|--------------------------------------------------------------------------
| Get portfolio owner's information
|--------------------------------------------------------------------------
*/

$recipientEmail = $user['email'];

$recipientName = !empty($profile['full_name'])
    ? $profile['full_name']
    : 'Portify User';


/*
|--------------------------------------------------------------------------
| Send email
|--------------------------------------------------------------------------
*/

$sent = send_portfolio_message(
    $recipientEmail,
    $recipientName,
    $name,
    $email,
    $message
);


/*
|--------------------------------------------------------------------------
| Handle result
|--------------------------------------------------------------------------
*/

if (!$sent) {

    http_response_code(500);

    exit(
        'Unable to send your message right now. ' .
        'Please try again later.'
    );
}


/*
|--------------------------------------------------------------------------
| Redirect back to portfolio
|--------------------------------------------------------------------------
*/

header(
    'Location: portfolio.php?u=' .
    $uid .
    '&contact=success#contact'
);

exit;