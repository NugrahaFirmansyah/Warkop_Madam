<?php
/**
 * Warkop Madam - Database Setup & Initializer
 * Kompatibel dengan Localhost (XAMPP/Laragon) dan Shared Hosting (cPanel/VPS)
 */
require_once __DIR__ . '/includes/security.php';

$is_cli = (php_sapi_name() === 'cli');

$host = DB_HOST;
$port = DB_PORT;
$dbname = DB_NAME;
$username = DB_USER;
$password = DB_PASS;

$logs = [];
$status_ok = true;

function log_msg($msg, $type = 'info') {
    global $logs, $is_cli;
    $logs[] = ['msg' => $msg, 'type' => $type];
    if ($is_cli) {
        echo ($type === 'error' ? '[ERROR] ' : '[OK] ') . $msg . "\n";
    }
}

try {
    $pdo = null;

    // 1. Coba koneksi langsung ke nama database yang ditentukan di .env (Standar Hosting cPanel)
    try {
        $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
        $pdo = new PDO($dsn, $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_EMULATE_PREPARES => false
        ]);
        log_msg("Berhasil terhubung langsung ke database: `$dbname`.");
    } catch (PDOException $e_direct) {
        // Jika database belum ada, coba koneksi ke server MySQL tanpa dbname lalu CREATE DATABASE (Standar Localhost)
        try {
            $dsn_root = "mysql:host=$host;port=$port;charset=utf8mb4";
            $pdo_root = new PDO($dsn_root, $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_EMULATE_PREPARES => false
            ]);
            $pdo_root->exec("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $pdo_root->exec("USE `$dbname`");
            $pdo = $pdo_root;
            log_msg("Database `$dbname` berhasil dibuat otomatis.");
        } catch (PDOException $e_create) {
            throw new Exception("Gagal terhubung atau membuat database: " . $e_direct->getMessage());
        }
    }

    // 2. Buat tabel users
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nama_lengkap VARCHAR(100) NOT NULL,
        username VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        role ENUM('admin', 'klien') DEFAULT 'klien',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    log_msg("Tabel `users` siap.");

    // 3. Buat tabel orders
    $pdo->exec("CREATE TABLE IF NOT EXISTS orders (
        id INT AUTO_INCREMENT PRIMARY KEY,
        order_number VARCHAR(50) NOT NULL,
        customer_name VARCHAR(100) NOT NULL,
        table_number VARCHAR(50) NOT NULL,
        payment_method VARCHAR(50) NOT NULL,
        total_price INT NOT NULL,
        order_items TEXT NOT NULL,
        notes TEXT NULL,
        payment_proof VARCHAR(255) NULL,
        ai_status ENUM('valid', 'review', 'invalid', 'skipped') DEFAULT 'skipped',
        ai_confidence INT DEFAULT 0,
        ai_detected_amount INT NULL,
        ai_reference_no VARCHAR(100) NULL,
        ai_analysis_json LONGTEXT NULL,
        proof_hash VARCHAR(64) NULL,
        status ENUM('pending', 'diproses', 'selesai', 'dibatalkan') DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX (status),
        INDEX (ai_status),
        INDEX (proof_hash),
        INDEX (created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    log_msg("Tabel `orders` siap.");

    // Pastikan kolom payment_proof & AI verification ada jika tabel sudah terbuat sebelumnya
    $alter_queries = [
        "ALTER TABLE orders ADD COLUMN payment_proof VARCHAR(255) NULL AFTER notes",
        "ALTER TABLE orders ADD COLUMN ai_status ENUM('valid', 'review', 'invalid', 'skipped') DEFAULT 'skipped' AFTER payment_proof",
        "ALTER TABLE orders ADD COLUMN ai_confidence INT DEFAULT 0 AFTER ai_status",
        "ALTER TABLE orders ADD COLUMN ai_detected_amount INT NULL AFTER ai_confidence",
        "ALTER TABLE orders ADD COLUMN ai_reference_no VARCHAR(100) NULL AFTER ai_detected_amount",
        "ALTER TABLE orders ADD COLUMN ai_analysis_json LONGTEXT NULL AFTER ai_reference_no",
        "ALTER TABLE orders ADD COLUMN proof_hash VARCHAR(64) NULL AFTER ai_analysis_json"
    ];
    foreach ($alter_queries as $q) {
        try {
            $pdo->exec($q);
        } catch (Exception $ex) {
            // Kolom sudah ada, abaikan
        }
    }
    log_msg("Skema kolom verifikasi AI bukti pembayaran diperiksa & siap.");


    // 4. Buat tabel menu_items
    $pdo->exec("CREATE TABLE IF NOT EXISTS menu_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nama_menu VARCHAR(100) NOT NULL,
        kategori ENUM('makanan', 'cemilan', 'minuman') NOT NULL,
        subkategori VARCHAR(50) DEFAULT 'Umum',
        harga INT NOT NULL,
        deskripsi TEXT NULL,
        foto VARCHAR(255) DEFAULT 'assets/logo.png',
        status ENUM('tersedia', 'habis') DEFAULT 'tersedia',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX (kategori),
        INDEX (status)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    log_msg("Tabel `menu_items` siap.");

    // 5. Cek Akun Admin Default
    $stmt = $pdo->prepare("SELECT id, username FROM users WHERE role = 'admin' LIMIT 1");
    $stmt->execute();
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$admin) {
        $default_pass = password_hash('admin123', PASSWORD_DEFAULT);
        $insert = $pdo->prepare("INSERT INTO users (nama_lengkap, username, password, role) VALUES ('Administrator Warkop', 'admin', :pass, 'admin')");
        $insert->execute(['pass' => $default_pass]);
        log_msg("Akun admin default berhasil dibuat: <b>admin</b> / <b>admin123</b>");
    } else {
        log_msg("Akun admin sudah aktif: <b>" . e($admin['username']) . "</b>");
    }

    // 6. Seed Sample Menu Items jika tabel masih kosong
    $menu_count = $pdo->query("SELECT COUNT(*) FROM menu_items")->fetchColumn();
    if ($menu_count == 0) {
        $menus = [
            // Makanan
            ['Nasi Goreng Madam', 'makanan', 'Special Nasi & Mie', 19000, 'Nasi goreng racikan bumbu khas Madam dengan suwiran ayam gurih, telur, dan kerupuk renyah.', 'assets/menu/nasi_goreng.jpg'],
            ['Nasi Goreng Cabe Ijo', 'makanan', 'Special Nasi & Mie', 19000, 'Nasi goreng dengan aroma cabai hijau segar pedas nendang dan topping lengkap.', 'assets/menu/nasi_goreng_cabe_ijo.jpg'],
            ['Mie Nyemek', 'makanan', 'Special Nasi & Mie', 17000, 'Mie kuah nyemek kental gurih berpadu telur, sayuran segar, dan irisan cabai rawit.', 'assets/menu/mie_nyemek.jpg'],
            ['Mie Goreng', 'makanan', 'Special Nasi & Mie', 19000, 'Mie goreng spesial bumbu kecap gurih manis dengan telur dan pelengkap istimewa.', 'assets/menu/mie_goreng.jpg'],
            ['Ayam Goreng Sambal Ijo', 'makanan', 'Olahan Ayam', 22000, 'Ayam goreng renyah empuk disiram ulekan sambal ijo segar + lalapan & tahu/tempe.', 'assets/menu/ayam_goreng_sambal_ijo.jpg'],
            ['Ayam Maranggi', 'makanan', 'Olahan Ayam', 22000, 'Ayam panggang bumbu maranggi manis gurih beraroma rempah bakar sedap.', 'assets/menu/ayam_maranggi.jpg'],
            ['Chiken Katsu', 'makanan', 'Olahan Ayam', 22000, 'Fillet ayam krispi tebal keemasan disajikan dengan saus cocolan nikmat & nasi.', 'assets/menu/chiken_katsu.jpg'],
            ['Chiken Wings', 'makanan', 'Olahan Ayam', 22000, 'Sayap ayam bumbu saus spesial legit gurih disajikan dengan kentang goreng renyah.', 'assets/menu/chiken_wings.jpg'],

            // Cemilan
            ['Kentang Goreng', 'cemilan', 'Gorengan Gurih', 12000, 'French fries renyah keemasan dengan cocolan saus sambal & tomat gurih.', 'assets/menu/kentang_goreng.jpg'],
            ['Cireng Isi', 'cemilan', 'Gorengan Gurih', 13000, 'Cireng kenyal gurih renyah dengan isian lezat dan sambal cocol rujak pedas manis.', 'assets/menu/cireng_isi.jpg'],
            ['Pisang Keju', 'cemilan', 'Manis Lezat', 12000, 'Pisang goreng manis empuk ditaburi limpahan keju parut dan cokelat kental manis.', 'assets/menu/pisang_keju.jpg'],
            ['Mix Platter', 'cemilan', 'Special Sharing', 15000, 'Kombinasi kentang, sosis, nugget, dan otak-otak dalam satu porsi komplit.', 'assets/menu/mix_platter.jpg'],
            ['Roti Bakar Keju / Coklat', 'cemilan', 'Roti Bakar', 12000, 'Roti tebal panggang lembut dengan pilihan topping keju gurih atau coklat lumer.', 'assets/menu/roti_bakar.jpg'],

            // Minuman
            ['Kopi Madam Special', 'minuman', 'Signature Coffee', 10000, 'Kopi hitam racikan legendaris khas Warkop Madam dengan aroma pekat harum.', 'assets/menu/kopi_madam.jpg'],
            ['Kopi Susu Creamy', 'minuman', 'Signature Coffee', 12000, 'Paduan kopi mantap dengan kental manis legit gurih creamy.', 'assets/menu/kopi_susu.jpg'],
            ['Teh Tarik', 'minuman', 'Non-Coffee & Milk', 10000, 'Teh pekat berbuih lembut ditarik berpadu susu manis segar.', 'assets/menu/teh_tarik.jpg'],
            ['Nutrisari Jeruk Peras', 'minuman', 'Minuman Segar', 6000, 'Kesegaran rasa jeruk peras dingin dengan es batu segar pelepas dahaga.', 'assets/menu/nutrisari_jeruk.jpg']
        ];

        $ins_menu = $pdo->prepare("INSERT INTO menu_items (nama_menu, kategori, subkategori, harga, deskripsi, foto, status) VALUES (?, ?, ?, ?, ?, ?, 'tersedia')");
        foreach ($menus as $m) {
            $ins_menu->execute($m);
        }
        log_msg("Seeding katalog " . count($menus) . " menu awal berhasil.");
    } else {
        log_msg("Katalog menu sudah terisi ($menu_count item).");
    }

    log_msg("Setup Database Selesai & Sistem Siap Digunakan!");

} catch (Exception $e) {
    $status_ok = false;
    log_msg("Gagal inisialisasi: " . $e->getMessage(), 'error');
}

// Jika dijalankan melalui CLI, hentikan output web
if ($is_cli) {
    exit($status_ok ? 0 : 1);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inisialisasi Database - Warkop Madam</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .font-serif-title { font-family: 'Playfair Display', serif; }
    </style>
</head>
<body class="bg-black text-stone-100 min-h-screen flex items-center justify-center p-4 selection:bg-amber-500 selection:text-stone-950">

    <div class="max-w-lg w-full bg-stone-950/95 backdrop-blur-2xl rounded-3xl p-8 border border-stone-800 shadow-2xl relative overflow-hidden">
        
        <!-- Top Accent Line -->
        <div class="absolute top-0 left-1/2 -translate-x-1/2 w-48 h-1 bg-gradient-to-r from-transparent via-amber-500 to-transparent rounded-full"></div>

        <!-- Header -->
        <div class="text-center mb-6">
            <div class="w-14 h-14 rounded-2xl bg-amber-500/10 border border-amber-500/30 text-amber-400 flex items-center justify-center mx-auto mb-3 shadow-lg">
                <i class="fa-solid fa-database text-2xl"></i>
            </div>
            <h1 class="text-2xl font-bold font-serif-title text-amber-300">Warkop Madam</h1>
            <p class="text-stone-400 text-xs mt-1">Inisialisasi & Pemeriksaan Database</p>
        </div>

        <!-- Status Log Card -->
        <div class="space-y-2.5 mb-6">
            <?php foreach ($logs as $log): ?>
                <?php if ($log['type'] === 'error'): ?>
                    <div class="p-3.5 bg-red-950/40 border border-red-800/60 rounded-xl text-red-300 text-xs flex items-start gap-2.5">
                        <i class="fa-solid fa-circle-xmark text-red-400 text-sm mt-0.5 shrink-0"></i>
                        <span><?php echo $log['msg']; ?></span>
                    </div>
                <?php else: ?>
                    <div class="p-3 bg-stone-900/70 border border-stone-800 rounded-xl text-stone-300 text-xs flex items-start gap-2.5">
                        <i class="fa-solid fa-circle-check text-emerald-400 text-sm mt-0.5 shrink-0"></i>
                        <span><?php echo $log['msg']; ?></span>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>

        <!-- Quick Access Buttons -->
        <div class="grid grid-cols-2 gap-3 pt-2">
            <a href="menu.php" class="flex items-center justify-center gap-2 px-4 py-3 rounded-xl bg-stone-900 hover:bg-stone-800 text-stone-200 font-semibold text-xs border border-stone-700 transition">
                <i class="fa-solid fa-book-open text-amber-400"></i>
                <span>Menu Tamu</span>
            </a>
            <a href="login_admin.php" class="flex items-center justify-center gap-2 px-4 py-3 rounded-xl bg-gradient-to-r from-amber-600 to-amber-500 hover:from-amber-500 hover:to-amber-400 text-stone-950 font-bold text-xs shadow-lg shadow-amber-950/40 transition">
                <i class="fa-solid fa-lock text-stone-950"></i>
                <span>Login Admin</span>
            </a>
        </div>

        <!-- Credentials Info Box -->
        <div class="mt-6 p-4 rounded-xl bg-amber-500/5 border border-amber-500/20 text-center">
            <span class="text-[11px] text-stone-400 block mb-1">Kredensial Default Admin:</span>
            <span class="text-xs text-amber-300 font-mono font-bold">Username: <b>admin</b> | Password: <b>admin123</b></span>
        </div>

    </div>

</body>
</html>
