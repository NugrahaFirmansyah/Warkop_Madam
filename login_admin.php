<?php
require_once __DIR__ . '/security.php';

// Jika admin sudah login, langsung alihkan ke dashboard
if (is_admin()) {
    header('Location: dashboard_admin.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Validasi Token CSRF
    if (!verify_csrf_token()) {
        $error = 'Sesi keamanan tidak valid atau telah kedaluwarsa. Silakan muat ulang halaman.';
    } else {
        // 2. Cek Rate Limiting / Proteksi Brute-Force
        $rate_check = check_login_rate_limit('login_admin', 5, 600); // 5 percobaan max per 10 menit
        if (!$rate_check['allowed']) {
            $error = $rate_check['message'];
        } else {
            $username = trim($_POST['username'] ?? '');
            $password = trim($_POST['password'] ?? '');

            if (empty($username) || empty($password)) {
                $error = 'Username dan password wajib diisi!';
            } else {
                try {
                    $pdo = get_db_connection();

                    // Cari data user dengan prepared statement
                    $stmt = $pdo->prepare("SELECT id, nama_lengkap, username, password, role FROM users WHERE username = :username LIMIT 1");
                    $stmt->execute(['username' => $username]);
                    $user = $stmt->fetch();

                    // Verifikasi password & role KHUSUS ADMIN
                    if ($user && password_verify($password, $user['password'])) {
                        if ($user['role'] === 'admin') {
                            // Berhasil login sebagai admin -> Bersihkan limiter & regenerasi ID Session (Anti Fixation)
                            clear_login_attempts('login_admin');
                            session_regenerate_id(true);

                            $_SESSION['admin_logged_in'] = true;
                            $_SESSION['user_id'] = $user['id'];
                            $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
                            $_SESSION['username'] = $user['username'];
                            $_SESSION['role'] = 'admin';
                            $_SESSION['last_activity'] = time();

                            header('Location: dashboard_admin.php');
                            exit;
                        } else {
                            record_failed_login_attempt('login_admin');
                            $error = 'Akses Ditolak: Akun ini tidak memiliki hak akses Administrator!';
                        }
                    } else {
                        // Catat percobaan gagal untuk rate limiting
                        record_failed_login_attempt('login_admin');
                        $error = 'Username atau Password yang Anda masukkan salah!';
                    }
                } catch (Exception $e) {
                    error_log("Login error: " . $e->getMessage());
                    $error = 'Terjadi kesalahan sistem yang aman. Silakan coba kembali.';
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - Warkop Madam (Secured)</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Playfair+Display:ital,wght@0,700;1,600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .font-serif-title { font-family: 'Playfair Display', serif; }
    </style>
</head>
<body class="bg-black text-stone-100 min-h-screen flex items-center justify-center p-4 relative overflow-hidden selection:bg-amber-500 selection:text-stone-950">

    <!-- Ambient Glowing Background -->
    <div class="absolute -top-40 -left-40 w-[450px] h-[450px] bg-red-950/20 rounded-full blur-[120px] pointer-events-none"></div>
    <div class="absolute -bottom-40 -right-40 w-[450px] h-[450px] bg-amber-600/15 rounded-full blur-[120px] pointer-events-none"></div>

    <div class="max-w-md w-full relative z-10">
        
        <!-- Card Container -->
        <div class="bg-stone-950/90 backdrop-blur-2xl rounded-3xl p-8 sm:p-10 border border-stone-800 shadow-2xl relative overflow-hidden">
            
            <!-- Top Accent Line -->
            <div class="absolute top-0 left-1/2 -translate-x-1/2 w-40 h-1 bg-gradient-to-r from-transparent via-amber-500 to-transparent rounded-full"></div>

            <!-- Header Section -->
            <div class="text-center mb-8">
                <!-- Logo Frame -->
                <div class="w-20 h-20 mx-auto rounded-2xl bg-black p-1 border border-amber-500/40 shadow-xl flex items-center justify-center mb-4">
                    <img src="assets/logo.png" alt="Warkop Madam Logo" class="w-full h-full object-contain">
                </div>

                <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-red-950/60 border border-red-800/40 text-red-300 text-xs font-bold uppercase tracking-wider mb-2">
                    <i class="fa-solid fa-shield-halved text-amber-400"></i>
                    <span>Portal Administrator Aman</span>
                </div>

                <h1 class="text-2xl sm:text-3xl font-extrabold font-serif-title text-amber-100">
                    Masuk Akun Admin
                </h1>
                <p class="text-stone-400 text-xs mt-1.5">
                    Hanya untuk kasir dan pengelola resmi Warkop Madam.
                </p>
            </div>

            <!-- Error Notification -->
            <?php if (!empty($error)): ?>
                <div class="mb-5 p-3.5 bg-red-500/15 border border-red-500/40 text-red-200 text-xs rounded-xl flex items-start gap-2.5">
                    <i class="fa-solid fa-circle-exclamation text-red-400 text-sm mt-0.5 shrink-0"></i>
                    <span><?php echo e($error); ?></span>
                </div>
            <?php endif; ?>

            <!-- Login Form with CSRF Protection -->
            <form action="" method="POST" class="space-y-4">
                <?php echo csrf_field(); ?>
                
                <!-- Username Input -->
                <div>
                    <label class="block text-stone-300 text-xs font-semibold mb-1.5">Username Admin</label>
                    <div class="relative">
                        <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-stone-500 text-sm">
                            <i class="fa-solid fa-user-shield"></i>
                        </span>
                        <input type="text" 
                               name="username" 
                               required 
                               autocomplete="username"
                               maxlength="50"
                               class="w-full pl-10 pr-4 py-3 bg-stone-900 border border-stone-800 rounded-xl text-stone-100 placeholder-stone-600 text-sm focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition"
                               placeholder="Masukkan username admin...">
                    </div>
                </div>

                <!-- Password Input -->
                <div>
                    <label class="block text-stone-300 text-xs font-semibold mb-1.5">Password</label>
                    <div class="relative">
                        <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-stone-500 text-sm">
                            <i class="fa-solid fa-key"></i>
                        </span>
                        <input type="password" 
                               id="password-input"
                               name="password" 
                               required 
                               autocomplete="current-password"
                               maxlength="100"
                               class="w-full pl-10 pr-10 py-3 bg-stone-900 border border-stone-800 rounded-xl text-stone-100 placeholder-stone-600 text-sm focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition"
                               placeholder="••••••••">
                        <button type="button" 
                                onclick="togglePasswordVisibility()" 
                                class="absolute right-3.5 top-1/2 -translate-y-1/2 text-stone-500 hover:text-stone-300 text-sm">
                            <i id="eye-icon" class="fa-solid fa-eye"></i>
                        </button>
                    </div>
                </div>

                <!-- Submit Button -->
                <button type="submit" 
                        class="w-full py-3.5 rounded-xl bg-gradient-to-r from-amber-600 via-amber-500 to-amber-600 text-stone-950 font-extrabold text-sm shadow-lg shadow-amber-950/60 hover:from-amber-500 hover:to-amber-400 transition duration-200 flex items-center justify-center gap-2 mt-2">
                    <i class="fa-solid fa-right-to-bracket"></i>
                    <span>Masuk ke Dashboard</span>
                </button>

            </form>

            <!-- Back to Public Menu -->
            <div class="mt-6 pt-5 border-t border-stone-800/80 text-center">
                <a href="menu.php" class="inline-flex items-center gap-1.5 text-xs text-stone-400 hover:text-amber-300 transition">
                    <i class="fa-solid fa-arrow-left text-[10px]"></i>
                    <span>Kembali ke Halaman Menu Pelanggan</span>
                </a>
            </div>

        </div>

        <!-- Footer Note -->
        <p class="text-center text-xs text-stone-500 mt-5">
            &copy; 2026 <span class="text-amber-400 font-medium">Warkop Madam</span>. Admin Control Panel.
        </p>

    </div>

    <script>
        function togglePasswordVisibility() {
            const passInput = document.getElementById('password-input');
            const eyeIcon = document.getElementById('eye-icon');
            if (passInput.type === 'password') {
                passInput.type = 'text';
                eyeIcon.classList.remove('fa-eye');
                eyeIcon.classList.add('fa-eye-slash');
            } else {
                passInput.type = 'password';
                eyeIcon.classList.remove('fa-eye-slash');
                eyeIcon.classList.add('fa-eye');
            }
        }
    </script>

</body>
</html>
