<?php

declare(strict_types=1);

define('APP_NAME', 'Portify');
define('BASE_URL', 'http://localhost/portify');

define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'portify');
define('DB_USER', 'root');
define('DB_PASS', '');

define('UPLOAD_DIR', dirname(__DIR__) . DIRECTORY_SEPARATOR . 'uploads');
define('MAX_UPLOAD_SIZE', 5 * 1024 * 1024);

date_default_timezone_set('Asia/Manila');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once dirname(__DIR__) . '/vendor/autoload.php';
require_once dirname(__DIR__) . '/includes/functions.php';

function generate_username(string $name): string
{
    $name = strtolower(trim($name));

    // Replace anything that isn't a letter or number with -
    $name = preg_replace('/[^a-z0-9]+/', '-', $name);

    // Remove hyphens from beginning/end
    $name = trim($name, '-');

    return $name;
}

function generate_unique_username(string $name, ?int $excludeId = null): string
{
    $base = generate_username($name);

    if ($base === '') {
        $base = 'user';
    }

    $username = $base;
    $counter = 2;

    while (true) {

        if ($excludeId) {

            $stmt = db()->prepare(
                'SELECT id FROM users
                 WHERE username = ?
                 AND id != ?
                 LIMIT 1'
            );

            $stmt->execute([
                $username,
                $excludeId
            ]);
        } else {

            $stmt = db()->prepare(
                'SELECT id FROM users
                 WHERE username = ?
                 LIMIT 1'
            );

            $stmt->execute([
                $username
            ]);
        }

        if (!$stmt->fetch()) {
            return $username;
        }

        $username = $base . '-' . $counter;

        $counter++;
    }
}

function portfolio_url(array $user): string
{
    return asset($user['username']);
}