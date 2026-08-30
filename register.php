<?php
require_once __DIR__ . '/security.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token()) {
        $error = 'Sesi keamanan tidak valid atau telah kedaluwarsa. Silakan muat ulang halaman.';
    } else {
        $nama_lengkap = trim($_POST['nama_lengkap'] ?? '');
        $username     = trim($_POST['username'] ?? '');
        $password     = trim($_POST['password'] ?? '');
        $confirm_pass = trim($_POST['confirm_password'] ?? '');

        // Validasi format dan kekuatan input
        if (empty($nama_lengkap) || empty($username) || empty($password)) {
            $error = 'Semua kolom wajib diisi!';
        } elseif (mb_strlen($nama_lengkap) > 100) {
            $error = 'Nama lengkap maksimal 100 karakter!';
        } elseif (!preg_match('/^[a-zA-Z0-9_]{3,30}$/', $username)) {
            $error = 'Username hanya boleh berupa huruf, angka, underscore (3-30 karakter).';
        } elseif (strlen($password) < 6) {
            $error = 'Password minimal harus 6 karakter untuk keamanan!';
        } elseif ($password !== $confirm_pass) {
            $error = 'Konfirmasi password tidak cocok!';
        } else {
            try {
                $pdo = get_db_connection();

                // Cek apakah username sudah digunakan
                $stmt = $pdo->prepare("SELECT id FROM users WHERE username = :username LIMIT 1");
                $stmt->execute(['username' => $username]);
                if ($stmt->fetch()) {
                    $error = 'Username sudah terpakai, silakan gunakan username lain.';
                } else {
                    // Hash password dengan algoritma default terkini yang aman
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                    // Insert data baru dengan role otomatis 'klien'
                    $stmt_insert = $pdo->prepare("INSERT INTO users (nama_lengkap, username, password, role) VALUES (:nama, :user, :pass, 'klien')");
                    $stmt_insert->execute([
                        'nama' => $nama_lengkap,
                        'user' => $username,
                        'pass' => $hashed_password
                    ]);

                    $success = 'Registrasi berhasil! Silakan masuk melalui halaman menu atau login.';
                }
            } catch (Exception $e) {
                error_log("Register error: " . $e->getMessage());
                $error = 'Terjadi kesalahan sistem yang aman. Silakan coba beberapa saat lagi.';
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
    <title>Daftar Pelanggan - Warkop Madam (Secured)</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Playfair+Display:ital,wght@0,700;1,600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-black text-stone-100 min-h-screen flex items-center justify-center p-4 relative overflow-hidden selection:bg-amber-500 selection:text-stone-950">

    <!-- Background Ambient Glow -->
    <div class="absolute -top-40 -left-40 w-[450px] h-[450px] bg-amber-700/15 rounded-full blur-[120px] pointer-events-none"></div>
    <div class="absolute -bottom-40 -right-40 w-[450px] h-[450px] bg-orange-700/15 rounded-full blur-[120px] pointer-events-none"></div>

    <div class="max-w-md w-full bg-stone-950/90 backdrop-blur-2xl rounded-3xl shadow-2xl border border-stone-800 overflow-hidden my-6 relative z-10">
        
        <!-- Header Banner -->
        <div class="bg-gradient-to-r from-amber-600 via-amber-500 to-amber-600 p-6 text-center text-stone-950 relative">
            <h1 class="text-2xl font-black tracking-wider flex items-center justify-center gap-2">
                <i class="fa-solid fa-mug-hot"></i>
                <span>WARKOP MADAM</span>
            </h1>
            <p class="text-stone-900 font-semibold text-xs mt-1">Buat Akun Pelanggan Baru</p>
        </div>

        <!-- Form Registrasi -->
        <div class="p-8">
            <?php if (!empty($error)): ?>
                <div class="mb-4 p-3.5 bg-red-500/15 border border-red-500/40 text-red-200 text-xs rounded-xl flex items-start gap-2">
                    <i class="fa-solid fa-circle-exclamation text-red-400 text-sm mt-0.5 shrink-0"></i>
                    <span><?php echo e($error); ?></span>
                </div>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <div class="mb-4 p-3.5 bg-emerald-500/15 border border-emerald-500/40 text-emerald-200 text-xs rounded-xl flex items-start gap-2">
                    <i class="fa-solid fa-circle-check text-emerald-400 text-sm mt-0.5 shrink-0"></i>
                    <div>
                        <span><?php echo e($success); ?></span>
                        <div class="mt-2">
                            <a href="menu.php" class="inline-block px-3 py-1 bg-emerald-600 text-white rounded-lg text-xs font-bold hover:bg-emerald-500 transition">Buka Menu</a>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <form action="" method="POST" class="space-y-4">
                <?php echo csrf_field(); ?>
                
                <div>
                    <label class="block text-stone-300 text-xs font-semibold mb-1">Nama Lengkap</label>
                    <input type="text" name="nama_lengkap" required maxlength="100"
                           class="w-full px-4 py-2.5 bg-stone-900 border border-stone-800 rounded-xl text-stone-100 placeholder-stone-600 text-sm focus:outline-none focus:border-amber-500 transition"
                           placeholder="Nama lengkap Anda...">
                </div>

                <div>
                    <label class="block text-stone-300 text-xs font-semibold mb-1">Username</label>
                    <input type="text" name="username" required maxlength="30"
                           class="w-full px-4 py-2.5 bg-stone-900 border border-stone-800 rounded-xl text-stone-100 placeholder-stone-600 text-sm focus:outline-none focus:border-amber-500 transition"
                           placeholder="Buat username (huruf, angka, _)...">
                </div>

                <div>
                    <label class="block text-stone-300 text-xs font-semibold mb-1">Password (Min. 6 Karakter)</label>
                    <input type="password" name="password" required minlength="6" maxlength="100"
                           class="w-full px-4 py-2.5 bg-stone-900 border border-stone-800 rounded-xl text-stone-100 placeholder-stone-600 text-sm focus:outline-none focus:border-amber-500 transition"
                           placeholder="••••••••">
                </div>

                <div>
                    <label class="block text-stone-300 text-xs font-semibold mb-1">Konfirmasi Password</label>
                    <input type="password" name="confirm_password" required minlength="6" maxlength="100"
                           class="w-full px-4 py-2.5 bg-stone-900 border border-stone-800 rounded-xl text-stone-100 placeholder-stone-600 text-sm focus:outline-none focus:border-amber-500 transition"
                           placeholder="••••••••">
                </div>

                <button type="submit" 
                        class="w-full py-3 bg-gradient-to-r from-amber-600 to-amber-500 text-stone-950 font-bold rounded-xl shadow-lg hover:from-amber-500 hover:to-amber-400 focus:outline-none transition duration-200 mt-2">
                    Daftar Sekarang
                </button>
            </form>

            <div class="mt-6 text-center text-xs text-stone-400">
                Kembali ke <a href="menu.php" class="text-amber-400 hover:underline font-medium">Halaman Utama Menu</a>
            </div>
        </div>

        <!-- Footer -->
        <div class="bg-stone-900/60 px-8 py-3 text-center border-t border-stone-800/80">
            <p class="text-xs text-stone-500">&copy; 2026 Warkop Madam. All rights reserved.</p>
        </div>

    </div>

</body>
</html>