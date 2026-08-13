<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

define('GOOGLE_CLIENT_ID', '258566218160-0mvhfgininae5qm4vopv3k0pra2q11bh.apps.googleusercontent.com');
define('GOOGLE_CLIENT_SECRET', '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');
define('GOOGLE_REDIRECT_URI', BASE_URL . '/auth/google-callback.php');

function googleClient(): Google\Client {
    $client = new Google\Client();
    $client->setClientId(GOOGLE_CLIENT_ID);
    $client->setClientSecret(GOOGLE_CLIENT_SECRET);
    $client->setRedirectUri(GOOGLE_REDIRECT_URI);
    $client->setAccessType('offline');
    $client->setPrompt('select_account');
    $client->addScope(['openid', 'email', 'profile']);
    return $client;
}
