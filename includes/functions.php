<?php

declare(strict_types=1);

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function redirect(string $path): never
{
    header('Location: ' . BASE_URL . '/' . ltrim($path, '/'));
    exit;
}

function csrf_token(): string
{
    if (empty($_SESSION['_csrf'])) {
        $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">';
}

function verify_csrf(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
    $token = $_POST['_csrf'] ?? '';
    if (!hash_equals($_SESSION['_csrf'] ?? '', $token)) {
        http_response_code(419);
        exit('Invalid CSRF token.');
    }
}

function flash(string $type, string $message): void
{
    $_SESSION['_flash'] = ['type' => $type, 'message' => $message];
}

function get_flash(): ?array
{
    $f = $_SESSION['_flash'] ?? null;
    unset($_SESSION['_flash']);
    return $f;
}

function is_logged_in(): bool
{
    return !empty($_SESSION['user_id']);
}

function current_user_id(): ?int
{
    return isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;
}

function require_login(): void
{
    if (!is_logged_in()) redirect('login.php');
}

function require_role(string $role): void
{
    require_login();
    if (($_SESSION['role'] ?? '') !== $role) {
        http_response_code(403);
        exit('Forbidden.');
    }
}

function asset(string $path): string
{
    return BASE_URL . '/' . ltrim($path, '/');
}

function upload_file(string $field, array $allowedExtensions, string $folder): ?string
{
    if (!isset($_FILES[$field]) || $_FILES[$field]['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    $file = $_FILES[$field];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('File upload failed.');
    }

    if ($file['size'] > MAX_UPLOAD_SIZE) {
        throw new RuntimeException('File exceeds the 5MB upload limit.');
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowedExtensions, true)) {
        throw new RuntimeException('Unsupported file type.');
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);

    $mimeMap = [
        'jpg' => ['image/jpeg'],
        'jpeg' => ['image/jpeg'],
        'png' => ['image/png'],
        'webp' => ['image/webp'],
        'pdf' => ['application/pdf']
    ];

    if (!isset($mimeMap[$ext]) || !in_array($mime, $mimeMap[$ext], true)) {
        throw new RuntimeException('Invalid file content.');
    }

    $relativeDir = 'uploads/' . trim($folder, '/');
    $absoluteDir = dirname(__DIR__) . DIRECTORY_SEPARATOR . $relativeDir;

    if (!is_dir($absoluteDir)) {
        mkdir($absoluteDir, 0755, true);
    }

    $filename = bin2hex(random_bytes(16)) . '.' . $ext;
    $target = $absoluteDir . DIRECTORY_SEPARATOR . $filename;

    if (!move_uploaded_file($file['tmp_name'], $target)) {
        throw new RuntimeException('Could not save uploaded file.');
    }

    return $relativeDir . '/' . $filename;
}

function delete_upload(?string $relativePath): void
{
    if (!$relativePath) return;
    $file = dirname(__DIR__) . DIRECTORY_SEPARATOR . ltrim($relativePath, '/\\');
    if (is_file($file)) @unlink($file);
}

function format_date(?string $date): string
{
    if (!$date) return '';
    $time = strtotime($date);
    return $time ? date('M Y', $time) : '';
}

function ensure_profile(int $userId): void
{
    $stmt = db()->prepare('INSERT IGNORE INTO profiles (user_id) VALUES (?)');
    $stmt->execute([$userId]);
}

function get_profile(int $userId): array
{
    ensure_profile($userId);
    $stmt = db()->prepare('SELECT * FROM profiles WHERE user_id=?');
    $stmt->execute([$userId]);
    return $stmt->fetch() ?: [];
}

function get_user(int $userId): array
{
    $stmt = db()->prepare('SELECT * FROM users WHERE id=?');
    $stmt->execute([$userId]);
    return $stmt->fetch() ?: [];
}

function count_table(string $table, int $userId): int
{
    $allowed = ['projects', 'experience', 'education', 'skills', 'certifications'];
    if (!in_array($table, $allowed, true)) return 0;
    $stmt = db()->prepare("SELECT COUNT(*) FROM {$table} WHERE user_id=?");
    $stmt->execute([$userId]);
    return (int)$stmt->fetchColumn();
}
