<?php
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/google.php';

try {
    if (!isset($_GET['code'])) {
        throw new RuntimeException('Google did not return an authorization code.');
    }

    $client = googleClient();
    $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);

    if (isset($token['error'])) {
        throw new RuntimeException('Google authentication failed.');
    }

    $client->setAccessToken($token);
    $oauth = new Google\Service\Oauth2($client);
    $info = $oauth->userinfo->get();

    $googleId = (string)$info->id;
    $email = strtolower((string)$info->email);
    $name = (string)$info->name;
    $picture = (string)$info->picture;

    $stmt = db()->prepare('SELECT * FROM users WHERE google_id=? OR email=? LIMIT 1');
    $stmt->execute([$googleId, $email]);
    $user = $stmt->fetch();

    if (!$user) {
        $stmt = db()->prepare('INSERT INTO users (google_id,email,role,account_status) VALUES (?,?,?,?)');
        $stmt->execute([$googleId,$email,'user','active']);
        $userId = (int)db()->lastInsertId();
        $stmt = db()->prepare('INSERT INTO profiles (user_id,full_name,profile_image) VALUES (?,?,?)');
        $stmt->execute([$userId,$name,null]);
        $user = get_user($userId);
    } else {
        if ($user['account_status'] !== 'active') {
            exit('This account is inactive. Contact the administrator.');
        }
        if (!$user['google_id']) {
            $stmt = db()->prepare('UPDATE users SET google_id=? WHERE id=?');
            $stmt->execute([$googleId,$user['id']]);
        }
        ensure_profile((int)$user['id']);
        if ($name) {
            $stmt = db()->prepare('UPDATE profiles SET full_name=COALESCE(NULLIF(full_name,""),?) WHERE user_id=?');
            $stmt->execute([$name,$user['id']]);
        }
    }

    session_regenerate_id(true);
    $_SESSION['user_id'] = (int)$user['id'];
    $_SESSION['email'] = $email;
    $_SESSION['role'] = $user['role'];

    redirect($user['role'] === 'admin' ? 'admin/index.php' : 'user/index.php');
} catch (Throwable $e) {
    http_response_code(500);
    echo 'Login error: ' . e($e->getMessage());
}
