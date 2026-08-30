<?php
/**
 * ========================================================
 * WARKOP MADAM - MODUL SENTRAL PENGAMANAN & KONFIGURASI
 * ========================================================
 * Menyediakan proteksi:
 * 1. .env Environment Variable Loader & Reader
 * 2. PDO Database Connection (Emulated Prepares Disabled, UTF-8)
 * 3. Session Hardening (Strict Mode, HttpOnly, SameSite, Secure)
 * 4. HTTP Security Headers (Clickjacking, MIME Sniffing, XSS)
 * 5. CSRF Token Generation & Verification
 * 6. Rate Limiting / Anti Brute-Force Protection
 * 7. Secure File Upload Validator (MIME Check, Magic Bytes, Whitelist)
 * 8. XSS Output Sanitization Helper (e())
 * 9. Authentication & Role Authorization Guards
 */

// 1. .env Loader & Helper Function
function load_env_file($path = null) {
    if ($path === null) {
        $path = __DIR__ . '/.env';
    }
    if (!file_exists($path)) {
        return;
    }
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines === false) {
        return;
    }
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || strpos($line, '#') === 0) {
            continue;
        }
        if (strpos($line, '=') !== false) {
            list($name, $value) = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);

            // Handle quoted strings
            if (preg_match('/^"(.*)"$/', $value, $matches)) {
                $value = $matches[1];
            } elseif (preg_match("/^'(.*)'$/", $value, $matches)) {
                $value = $matches[1];
            }

            if (!array_key_exists($name, $_SERVER) && !array_key_exists($name, $_ENV)) {
                putenv("{$name}={$value}");
                $_ENV[$name] = $value;
                $_SERVER[$name] = $value;
            }
        }
    }
}
load_env_file();

function env($key, $default = null) {
    $val = getenv($key);
    if ($val === false) {
        $val = $_ENV[$key] ?? $_SERVER[$key] ?? $default;
    }
    if (is_string($val)) {
        $lower = strtolower($val);
        if ($lower === 'true' || $lower === '(true)') return true;
        if ($lower === 'false' || $lower === '(false)') return false;
        if ($lower === 'empty' || $lower === '(empty)') return '';
        if ($lower === 'null' || $lower === '(null)') return null;
    }
    return $val;
}

// 2. HTTP Security Headers
if (!headers_sent()) {
    header('X-Frame-Options: SAMEORIGIN');
    header('X-Content-Type-Options: nosniff');
    header('X-XSS-Protection: 1; mode=block');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: geolocation=(), camera=(), microphone=()');
}

// 3. Inisialisasi Sesi Aman (Secure Session Init)
if (session_status() === PHP_SESSION_NONE) {
    $is_https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443);
    
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.cookie_httponly', '1');
    
    session_set_cookie_params([
        'lifetime' => intval(env('SESSION_LIFETIME', 0)),
        'path'     => '/',
        'domain'   => '',
        'secure'   => $is_https,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    
    session_start();
}

// 4. Konfigurasi & Koneksi Database Terpusat (.env Driven Secure PDO)
define('DB_HOST', env('DB_HOST', 'localhost'));
define('DB_PORT', env('DB_PORT', '3306'));
define('DB_NAME', env('DB_DATABASE', 'warkop_madam'));
define('DB_USER', env('DB_USERNAME', 'root'));
define('DB_PASS', env('DB_PASSWORD', ''));

function get_db_connection() {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false, // Mencegah bypass SQL Injection
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
        ];
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            error_log("Database connection error: " . $e->getMessage());
            die("Terjadi gangguan koneksi sistem database yang aman. Silakan periksa file .env atau hubungi pengelola.");
        }
    }
    return $pdo;
}

// 5. XSS Sanitization Helper
function e($string) {
    if ($string === null) return '';
    return htmlspecialchars((string)$string, ENT_QUOTES, 'UTF-8');
}

// 6. CSRF Protection Engine
function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_token() {
    return generate_csrf_token();
}

function csrf_field() {
    $token = generate_csrf_token();
    return '<input type="hidden" name="csrf_token" value="' . e($token) . '">';
}

function verify_csrf_token($token = null) {
    if ($token === null) {
        $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    }
    if (empty($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

function validate_csrf_or_die() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!verify_csrf_token()) {
            http_response_code(403);
            die("<!DOCTYPE html><html><head><title>Akses Ditolak</title><style>body{background:#09090b;color:#fca5a5;font-family:sans-serif;display:flex;align-items:center;justify-content:center;height:100vh;margin:0;}.card{background:#18181b;padding:24px;border-radius:12px;border:1px solid #7f1d1d;text-align:center;max-width:400px;}</style></head><body><div class=\"card\"><h2>⚠️ Keamanan: Token CSRF Tidak Valid</h2><p style=\"color:#d4d4d8;font-size:14px;\">Sesi Anda telah kedaluwarsa atau permintaan tidak sah. Silakan muat ulang halaman.</p><br><a href=\"javascript:history.back()\" style=\"color:#f59e0b;\">Kembali</a></div></body></html>");
        }
    }
}

// 7. Anti Brute-Force Rate Limiter (Login Throttle)
function check_login_rate_limit($action_key = 'login_admin', $max_attempts = null, $lockout_seconds = null) {
    if ($max_attempts === null) {
        $max_attempts = intval(env('MAX_LOGIN_ATTEMPTS', 5));
    }
    if ($lockout_seconds === null) {
        $lockout_seconds = intval(env('LOCKOUT_MINUTES', 10)) * 60;
    }
    $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    $rate_key = 'rate_limit_' . $action_key . '_' . md5($ip);
    
    if (isset($_SESSION[$rate_key])) {
        $data = $_SESSION[$rate_key];
        if ($data['attempts'] >= $max_attempts) {
            $time_left = ($data['first_attempt'] + $lockout_seconds) - time();
            if ($time_left > 0) {
                $minutes_left = ceil($time_left / 60);
                return [
                    'allowed' => false,
                    'message' => "Terlalu banyak percobaan login yang gagal. Silakan tunggu $minutes_left menit sebelum mencoba kembali."
                ];
            } else {
                unset($_SESSION[$rate_key]);
            }
        }
    }
    return ['allowed' => true];
}

function record_failed_login_attempt($action_key = 'login_admin') {
    $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    $rate_key = 'rate_limit_' . $action_key . '_' . md5($ip);
    
    if (!isset($_SESSION[$rate_key])) {
        $_SESSION[$rate_key] = [
            'attempts' => 1,
            'first_attempt' => time()
        ];
    } else {
        $_SESSION[$rate_key]['attempts'] += 1;
    }
}

function clear_login_attempts($action_key = 'login_admin') {
    $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    $rate_key = 'rate_limit_' . $action_key . '_' . md5($ip);
    unset($_SESSION[$rate_key]);
}

// 8. Secure File Upload Validator
function validate_and_save_upload($file_array, $target_dir = 'uploads/proofs/', $max_size_bytes = null) {
    if ($max_size_bytes === null) {
        $max_size_bytes = intval(env('MAX_UPLOAD_SIZE_MB', 4)) * 1048576;
    }

    if (!isset($file_array) || $file_array['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'Tidak ada file yang diunggah atau terjadi kesalahan upload.'];
    }

    // Cek ukuran file
    if ($file_array['size'] > $max_size_bytes) {
        $mb = round($max_size_bytes / 1048576, 1);
        return ['success' => false, 'message' => "Ukuran file terlalu besar! Maksimal $mb MB."];
    }

    // Whitelist ekstensi yang diperbolehkan
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'webp'];
    $file_name = $file_array['name'];
    $extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

    if (!in_array($extension, $allowed_extensions, true)) {
        return ['success' => false, 'message' => 'Format file tidak diizinkan! Hanya format gambar JPG, PNG, atau WebP.'];
    }

    // Verifikasi MIME Type aktual menggunakan fileinfo
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $file_array['tmp_name']);
    finfo_close($finfo);

    $allowed_mimes = [
        'image/jpeg' => ['jpg', 'jpeg'],
        'image/png'  => ['png'],
        'image/webp' => ['webp']
    ];

    if (!array_key_exists($mime_type, $allowed_mimes)) {
        return ['success' => false, 'message' => 'Tipe konten file tidak valid atau berbahaya.'];
    }

    // Verifikasi keabsahan gambar dengan getimagesize
    $image_info = @getimagesize($file_array['tmp_name']);
    if ($image_info === false) {
        return ['success' => false, 'message' => 'File gambar tidak valid atau korup.'];
    }

    // Pastikan folder tujuan ada
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0755, true);
    }

    // Buat nama file unik dan acak yang tidak dapat ditebak
    $safe_name = bin2hex(random_bytes(16)) . '_' . time() . '.' . $extension;
    $target_path = rtrim($target_dir, '/') . '/' . $safe_name;

    if (move_uploaded_file($file_array['tmp_name'], $target_path)) {
        @chmod($target_path, 0644);
        return [
            'success' => true,
            'file_path' => $target_path,
            'file_name' => $safe_name
        ];
    } else {
        return ['success' => false, 'message' => 'Gagal memindahkan file ke direktori penyimpanan.'];
    }
}

// 9. Auth Guard
function is_admin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function require_admin() {
    if (!is_admin()) {
        header('Location: login_admin.php');
        exit;
    }
}
