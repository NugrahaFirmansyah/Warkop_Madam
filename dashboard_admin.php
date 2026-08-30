<?php
require_once __DIR__ . '/security.php';
require_admin();

$admin_name = $_SESSION['nama_lengkap'] ?? 'Administrator';

$orders = [];
$stats = [
    'omzet_today' => 0,
    'omzet_month' => 0,
    'total_orders' => 0,
    'pending_orders' => 0,
    'processing_orders' => 0,
    'completed_orders' => 0,
    'cancelled_orders' => 0,
    'total_menu' => 0
];

$payment_breakdown = [
    'QRIS' => 0,
    'Transfer Mandiri' => 0,
    'Tunai' => 0
];

try {
    $pdo = get_db_connection();

    // Update status order jika ada form submit
    if (isset($_POST['update_status'])) {
        validate_csrf_or_die();
        $order_id = intval($_POST['order_id']);
        $new_status = trim($_POST['status'] ?? '');
        $allowed_statuses = ['pending', 'diproses', 'selesai', 'dibatalkan'];

        if ($order_id > 0 && in_array($new_status, $allowed_statuses, true)) {
            $stmt_up = $pdo->prepare("UPDATE orders SET status = :status WHERE id = :id");
            $stmt_up->execute(['status' => $new_status, 'id' => $order_id]);
        }
        header('Location: dashboard_admin.php');
        exit;
    }

    // Ambil daftar pesanan terbaru
    $stmt_orders = $pdo->query("SELECT * FROM orders ORDER BY created_at DESC LIMIT 10");
    $orders = $stmt_orders->fetchAll(PDO::FETCH_ASSOC);

    // Ambil semua pesanan untuk kalkulasi analitik
    $all_orders = $pdo->query("SELECT * FROM orders")->fetchAll(PDO::FETCH_ASSOC);
    $today_str = date('Y-m-d');
    $month_str = date('Y-m');

    foreach ($all_orders as $o) {
        $stats['total_orders']++;
        $order_date = date('Y-m-d', strtotime($o['created_at']));
        $order_month = date('Y-m', strtotime($o['created_at']));

        if ($o['status'] === 'selesai') {
            $stats['completed_orders']++;
            if ($order_month === $month_str) {
                $stats['omzet_month'] += $o['total_price'];
            }
            if ($order_date === $today_str) {
                $stats['omzet_today'] += $o['total_price'];
            }
            if (isset($payment_breakdown[$o['payment_method']])) {
                $payment_breakdown[$o['payment_method']] += $o['total_price'];
            }
        } elseif ($o['status'] === 'pending') {
            $stats['pending_orders']++;
        } elseif ($o['status'] === 'diproses') {
            $stats['processing_orders']++;
        } elseif ($o['status'] === 'dibatalkan') {
            $stats['cancelled_orders']++;
        }
    }

    // Total menu
    $stats['total_menu'] = $pdo->query("SELECT COUNT(*) FROM menu_items")->fetchColumn();

    // Analisis Menu Paling Sering Dipesan (Best Seller Items)
    $menu_frequency = [];
    $total_items_sold = 0;

    foreach ($all_orders as $o) {
        if ($o['status'] === 'dibatalkan') continue;
        $raw_items = $o['order_items'] ?? '';
        $split_items = explode(',', $raw_items);
        foreach ($split_items as $si) {
            $si = trim($si);
            if (empty($si)) continue;
            if (preg_match('/^(\d+)x\s*(.+)$/i', $si, $matches)) {
                $qty = intval($matches[1]);
                $item_name = trim($matches[2]);
            } else {
                $qty = 1;
                $item_name = $si;
            }
            if (!isset($menu_frequency[$item_name])) {
                $menu_frequency[$item_name] = 0;
            }
            $menu_frequency[$item_name] += $qty;
            $total_items_sold += $qty;
        }
    }

    // Urutkan dari yang paling sering dipesan
    arsort($menu_frequency);

    $top_menu_labels = [];
    $top_menu_counts = [];
    $top_items_list = [];
    $rank = 1;

    foreach ($menu_frequency as $item_name => $count) {
        if ($rank <= 6) {
            $top_menu_labels[] = $item_name;
            $top_menu_counts[] = $count;
            $pct = $total_items_sold > 0 ? round(($count / $total_items_sold) * 100, 1) : 0;
            $top_items_list[] = [
                'rank' => $rank,
                'name' => $item_name,
                'count' => $count,
                'percentage' => $pct
            ];
            $rank++;
        }
    }

} catch (Exception $e) {
    error_log("Dashboard Admin Error: " . $e->getMessage());
    $db_error = "Terjadi kendala sistem database yang aman.";
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Warkop Madam</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Chart.js CDN for Modern Analytics Charts -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Playfair+Display:ital,wght@0,700;1,600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .font-serif-title { font-family: 'Playfair Display', serif; }
        
        /* Custom scrollbar */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: #0c0a09; }
        ::-webkit-scrollbar-thumb { background: #292524; border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: #d97706; }
    </style>
</head>
<body class="bg-[#09090b] text-stone-100 min-h-screen flex selection:bg-amber-500 selection:text-stone-950">

    <!-- ============================================== -->
    <!-- 1. LEFT SIDEBAR (Persis seperti layout Jurivia) -->
    <!-- ============================================== -->
    <aside class="w-64 bg-[#121113] border-r border-stone-800/80 min-h-screen flex flex-col justify-between shrink-0 sticky top-0 h-screen z-30 overflow-y-auto hidden md:flex">
        
        <!-- Top Logo Brand -->
        <div class="p-6">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-11 h-11 rounded-2xl bg-black p-1 border border-amber-500/40 shadow-lg flex items-center justify-center shrink-0">
                    <img src="assets/logo.png" alt="Warkop Madam Logo" class="w-full h-full object-contain">
                </div>
                <div>
                    <h1 class="text-lg font-extrabold font-serif-title tracking-wider text-amber-300">
                        WARKOP MADAM
                    </h1>
                    <p class="text-[11px] text-stone-400 font-semibold tracking-wide">Admin Panel</p>
                </div>
            </div>

            <!-- Navigation Links (Exact Button Layout) -->
            <nav class="space-y-1.5 text-xs font-semibold">
                
                <!-- Active Dashboard Pill -->
                <a href="dashboard_admin.php" class="flex items-center gap-3 px-4 py-3 rounded-2xl bg-gradient-to-r from-amber-600 to-amber-500 text-stone-950 font-bold shadow-lg shadow-amber-950/40 transition">
                    <i class="fa-solid fa-gauge-high text-base text-stone-950"></i>
                    <span class="text-sm font-extrabold">Dashboard</span>
                </a>

                <!-- Kelola Pesanan -->
                <a href="kelola_pesanan.php" class="flex items-center justify-between px-4 py-3 rounded-2xl text-stone-400 hover:text-white hover:bg-stone-900/90 transition group">
                    <div class="flex items-center gap-3">
                        <i class="fa-solid fa-bell-concierge text-base text-stone-400 group-hover:text-amber-400 transition"></i>
                        <span>Kelola Pesanan</span>
                    </div>
                    <?php if ($stats['pending_orders'] > 0): ?>
                        <span class="px-2 py-0.5 rounded-full bg-amber-500 text-stone-950 text-[10px] font-black animate-pulse">
                            <?php echo $stats['pending_orders']; ?>
                        </span>
                    <?php endif; ?>
                </a>

                <!-- Tambah & Kelola Menu -->
                <a href="kelola_menu.php" class="flex items-center gap-3 px-4 py-3 rounded-2xl text-stone-400 hover:text-white hover:bg-stone-900/90 transition group">
                    <i class="fa-solid fa-utensils text-base text-stone-400 group-hover:text-amber-400 transition"></i>
                    <span>Tambah & Kelola Menu</span>
                </a>

                <!-- Laporan Bulanan -->
                <a href="laporan_bulanan.php" class="flex items-center gap-3 px-4 py-3 rounded-2xl text-stone-400 hover:text-white hover:bg-stone-900/90 transition group">
                    <i class="fa-solid fa-file-invoice-dollar text-base text-stone-400 group-hover:text-amber-400 transition"></i>
                    <span>Laporan Bulanan</span>
                </a>

                <div class="pt-4 pb-2">
                    <span class="px-4 text-[10px] uppercase font-bold tracking-widest text-stone-600">Akses Publik & Akun</span>
                </div>

                <!-- Lihat Menu Tamu -->
                <a href="menu.php" target="_blank" class="flex items-center gap-3 px-4 py-3 rounded-2xl text-stone-400 hover:text-amber-300 hover:bg-stone-900/90 transition group">
                    <i class="fa-solid fa-eye text-base text-stone-400 group-hover:text-amber-400 transition"></i>
                    <span>Buka Menu Tamu</span>
                </a>

                <!-- Logout -->
                <a href="logout.php" class="flex items-center gap-3 px-4 py-3 rounded-2xl text-red-400/90 hover:text-red-300 hover:bg-red-950/40 transition">
                    <i class="fa-solid fa-right-from-bracket text-base"></i>
                    <span>Keluar / Logout</span>
                </a>

            </nav>
        </div>

        <!-- Sidebar Bottom Admin Info -->
        <div class="p-4 m-4 rounded-2xl bg-stone-900/70 border border-stone-800 flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-amber-500/20 text-amber-400 flex items-center justify-center font-bold text-sm">
                <i class="fa-solid fa-user-shield"></i>
            </div>
            <div class="overflow-hidden">
                <span class="text-xs font-bold text-stone-200 block truncate"><?php echo htmlspecialchars($admin_name); ?></span>
                <span class="text-[10px] text-emerald-400 flex items-center gap-1 font-medium">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span> Online
                </span>
            </div>
        </div>

    </aside>


    <!-- ============================================== -->
    <!-- 2. MAIN CONTENT AREA                           -->
    <!-- ============================================== -->
    <div class="flex-1 flex flex-col min-w-0 bg-[#0c0a09]">
        
        <!-- Mobile Header Navigation -->
        <header class="md:hidden sticky top-0 z-40 bg-stone-950/95 backdrop-blur-xl border-b border-stone-800 p-4 flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <img src="assets/logo.png" alt="Warkop Madam" class="w-8 h-8 object-contain">
                <span class="font-bold text-amber-300 font-serif-title text-sm">WARKOP MADAM</span>
            </div>
            <div class="flex items-center gap-2 text-xs">
                <a href="kelola_pesanan.php" class="px-2.5 py-1.5 rounded-lg bg-stone-900 text-stone-200">Pesanan</a>
                <a href="kelola_menu.php" class="px-2.5 py-1.5 rounded-lg bg-stone-900 text-stone-200">Menu</a>
                <a href="logout.php" class="px-2.5 py-1.5 rounded-lg bg-red-950 text-red-300">Keluar</a>
            </div>
        </header>

        <!-- Main Scrollable Body -->
        <main class="flex-1 p-4 sm:p-7 max-w-7xl w-full mx-auto space-y-6">

            <!-- ============================================== -->
            <!-- 2.1 TOP HERO BANNER (Persis seperti Jurivia)  -->
            <!-- ============================================== -->
            <div class="bg-gradient-to-r from-[#171412] via-[#1c1815] to-[#171412] rounded-3xl p-6 sm:p-8 border border-stone-800 shadow-2xl relative overflow-hidden">
                
                <!-- Ambient Accent Glow -->
                <div class="absolute -top-16 -right-16 w-56 h-56 bg-amber-500/10 rounded-full blur-3xl pointer-events-none"></div>

                <div class="relative z-10 flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                    
                    <div class="max-w-2xl space-y-2">
                        <!-- Pill Status -->
                        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-stone-900/80 border border-stone-800 text-[11px] font-semibold text-stone-300">
                            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
                            <span>Sistem Kasir Warkop Madam &bull; <?php echo date('l, d F Y'); ?></span>
                        </div>

                        <!-- Big Title -->
                        <h2 class="text-2xl sm:text-4xl font-black font-serif-title text-stone-100 flex items-center gap-3">
                            <span>Selamat Datang, <?php echo htmlspecialchars($admin_name); ?>!</span>
                            <span class="text-amber-400 text-2xl sm:text-3xl"><i class="fa-solid fa-mug-hot"></i></span>
                        </h2>

                        <!-- Subtitle Description -->
                        <p class="text-xs sm:text-sm text-stone-400 leading-relaxed">
                            Pantau arus transaksi kasir, antrean hidangan dapur, manajemen katalog menu, dan rekapitulasi laporan bulanan secara real-time.
                        </p>
                    </div>

                    <!-- Right Quick Action Buttons Row (Exact Jurivia Style) -->
                    <div class="flex flex-wrap items-center gap-2 sm:gap-2.5">
                        
                        <!-- Button 1: Kelola Pesanan -->
                        <a href="kelola_pesanan.php" class="px-4 py-2.5 rounded-xl bg-gradient-to-r from-amber-600 to-amber-500 hover:from-amber-500 hover:to-amber-400 text-stone-950 font-extrabold text-xs shadow-lg shadow-amber-950/60 transition flex items-center gap-2 active:scale-95">
                            <i class="fa-solid fa-bell-concierge text-xs"></i>
                            <span>Kelola Pesanan</span>
                        </a>

                        <!-- Button 2: Tambah Menu -->
                        <a href="kelola_menu.php" class="px-4 py-2.5 rounded-xl bg-stone-900 hover:bg-stone-800 text-amber-300 font-bold text-xs border border-stone-800 transition flex items-center gap-2">
                            <i class="fa-solid fa-plus-circle text-xs text-amber-400"></i>
                            <span>Tambah Menu</span>
                        </a>

                        <!-- Button 3: Laporan Bulanan -->
                        <a href="laporan_bulanan.php" class="px-4 py-2.5 rounded-xl bg-stone-900 hover:bg-stone-800 text-emerald-300 font-bold text-xs border border-stone-800 transition flex items-center gap-2">
                            <i class="fa-solid fa-file-invoice-dollar text-xs text-emerald-400"></i>
                            <span>Laporan Bulanan</span>
                        </a>

                        <!-- Button 4: Menu Tamu -->
                        <a href="menu.php" target="_blank" class="px-4 py-2.5 rounded-xl bg-stone-900 hover:bg-stone-800 text-stone-300 font-bold text-xs border border-stone-800 transition flex items-center gap-2">
                            <i class="fa-solid fa-eye text-xs text-blue-400"></i>
                            <span>Menu Tamu</span>
                        </a>

                    </div>

                </div>

            </div>


            <!-- ============================================== -->
            <!-- 2.2 TOP 4 STATS CARDS ROW (Exact Jurivia Cards)-->
            <!-- ============================================== -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                
                <!-- Card 1: Omzet Hari Ini -->
                <div class="bg-[#141210] rounded-2xl p-5 border border-stone-800/90 shadow-xl flex flex-col justify-between hover:border-amber-500/40 transition">
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-[11px] font-bold uppercase tracking-wider text-stone-400">OMZET HARI INI</span>
                            <div class="w-8 h-8 rounded-xl bg-amber-500/15 text-amber-400 flex items-center justify-center text-sm">
                                <i class="fa-solid fa-coins"></i>
                            </div>
                        </div>
                        <h3 class="text-2xl font-black font-serif-title text-amber-300">
                            Rp <?php echo number_format($stats['omzet_today'], 0, ',', '.'); ?>
                        </h3>
                    </div>
                    <div class="pt-3 mt-3 border-t border-stone-800/80 flex items-center justify-between text-[11px]">
                        <span class="text-stone-500 flex items-center gap-1">
                            <i class="fa-regular fa-calendar text-[10px]"></i> <?php echo date('d M Y'); ?>
                        </span>
                        <a href="laporan_bulanan.php" class="text-amber-400 font-semibold hover:underline">Detail &rarr;</a>
                    </div>
                </div>

                <!-- Card 2: Total Pesanan -->
                <div class="bg-[#141210] rounded-2xl p-5 border border-stone-800/90 shadow-xl flex flex-col justify-between hover:border-amber-500/40 transition">
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-[11px] font-bold uppercase tracking-wider text-stone-400">TOTAL PESANAN</span>
                            <div class="w-8 h-8 rounded-xl bg-blue-500/15 text-blue-400 flex items-center justify-center text-sm">
                                <i class="fa-solid fa-receipt"></i>
                            </div>
                        </div>
                        <h3 class="text-2xl font-black font-serif-title text-stone-100">
                            <?php echo $stats['total_orders']; ?>
                        </h3>
                    </div>
                    <div class="pt-3 mt-3 border-t border-stone-800/80 flex items-center justify-between text-[11px]">
                        <span class="text-stone-500 flex items-center gap-1">
                            <i class="fa-solid fa-check-double text-[10px] text-emerald-400"></i> <?php echo $stats['completed_orders']; ?> Selesai
                        </span>
                        <a href="kelola_pesanan.php" class="text-blue-400 font-semibold hover:underline">Kelola &rarr;</a>
                    </div>
                </div>

                <!-- Card 3: Antrean Pending -->
                <div class="bg-[#141210] rounded-2xl p-5 border border-stone-800/90 shadow-xl flex flex-col justify-between hover:border-amber-500/40 transition">
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-[11px] font-bold uppercase tracking-wider text-stone-400">ANTREAN PENDING</span>
                            <div class="w-8 h-8 rounded-xl bg-amber-500/15 text-amber-400 flex items-center justify-center text-sm">
                                <i class="fa-solid fa-clock-rotate-left"></i>
                            </div>
                        </div>
                        <h3 class="text-2xl font-black font-serif-title text-amber-400 flex items-center gap-2">
                            <span><?php echo $stats['pending_orders']; ?></span>
                            <?php if ($stats['pending_orders'] > 0): ?>
                                <span class="w-2 h-2 rounded-full bg-amber-400 animate-ping"></span>
                            <?php endif; ?>
                        </h3>
                    </div>
                    <div class="pt-3 mt-3 border-t border-stone-800/80 flex items-center justify-between text-[11px]">
                        <span class="text-stone-500">Perlu dimasak dapur</span>
                        <a href="kelola_pesanan.php?status=pending" class="text-amber-400 font-semibold hover:underline">Proses &rarr;</a>
                    </div>
                </div>

                <!-- Card 4: Total Menu Aktif -->
                <div class="bg-[#141210] rounded-2xl p-5 border border-stone-800/90 shadow-xl flex flex-col justify-between hover:border-amber-500/40 transition">
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-[11px] font-bold uppercase tracking-wider text-stone-400">KATALOG MENU</span>
                            <div class="w-8 h-8 rounded-xl bg-emerald-500/15 text-emerald-400 flex items-center justify-center text-sm">
                                <i class="fa-solid fa-utensils"></i>
                            </div>
                        </div>
                        <h3 class="text-2xl font-black font-serif-title text-stone-100">
                            <?php echo $stats['total_menu']; ?> Menu
                        </h3>
                    </div>
                    <div class="pt-3 mt-3 border-t border-stone-800/80 flex items-center justify-between text-[11px]">
                        <span class="text-stone-500">Katalog Makanan & Minuman</span>
                        <a href="kelola_menu.php" class="text-emerald-400 font-semibold hover:underline">Tambah &rarr;</a>
                    </div>
                </div>

            </div>


            <!-- ============================================== -->
            <!-- 2.3 ALUR PROGRES PESANAN (Exact 4 Flow Cards) -->
            <!-- ============================================== -->
            <div class="bg-[#121113] rounded-3xl p-6 border border-stone-800/80 shadow-2xl">
                
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="font-black text-base sm:text-lg text-amber-100 font-serif-title flex items-center gap-2">
                            <i class="fa-solid fa-bars-progress text-amber-400 text-sm"></i>
                            <span>Alur Antrean & Progres Penanganan Pesanan</span>
                        </h3>
                        <p class="text-xs text-stone-400 mt-0.5">Ringkasan tahapan status pesanan dari pemesanan tamu hingga penyajian hidangan.</p>
                    </div>
                    <a href="kelola_pesanan.php" class="text-xs text-amber-400 font-bold hover:underline hidden sm:inline">
                        Kelola Semua Pesanan &rarr;
                    </a>
                </div>

                <!-- 4 Flow Cards -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5 pt-2">
                    
                    <!-- 1. Menunggu / Pending -->
                    <div class="bg-[#1a1714] rounded-2xl p-4 border border-amber-500/30 flex flex-col justify-between">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-bold text-amber-300">1. Pesanan Baru (Pending)</span>
                            <span class="w-5 h-5 rounded-full bg-amber-500/20 text-amber-400 text-[10px] font-black flex items-center justify-center">
                                <?php echo $stats['pending_orders']; ?>
                            </span>
                        </div>
                        <h4 class="text-2xl font-black font-serif-title text-amber-400 my-1"><?php echo $stats['pending_orders']; ?></h4>
                        <p class="text-[10px] text-stone-400">Tamu baru saja checkout, menunggu konfirmasi kasir.</p>
                    </div>

                    <!-- 2. Sedang Dimasak / Diproses -->
                    <div class="bg-[#13171f] rounded-2xl p-4 border border-blue-500/30 flex flex-col justify-between">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-bold text-blue-300">2. Diproses Dapur</span>
                            <span class="w-5 h-5 rounded-full bg-blue-500/20 text-blue-400 text-[10px] font-black flex items-center justify-center">
                                <?php echo $stats['processing_orders']; ?>
                            </span>
                        </div>
                        <h4 class="text-2xl font-black font-serif-title text-blue-400 my-1"><?php echo $stats['processing_orders']; ?></h4>
                        <p class="text-[10px] text-stone-400">Barista & dapur sedang memasak / meracik menu.</p>
                    </div>

                    <!-- 3. Selesai / Diantar -->
                    <div class="bg-[#121c17] rounded-2xl p-4 border border-emerald-500/30 flex flex-col justify-between">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-bold text-emerald-300">3. Pesanan Selesai</span>
                            <span class="w-5 h-5 rounded-full bg-emerald-500/20 text-emerald-400 text-[10px] font-black flex items-center justify-center">
                                <i class="fa-solid fa-check text-[9px]"></i>
                            </span>
                        </div>
                        <h4 class="text-2xl font-black font-serif-title text-emerald-400 my-1"><?php echo $stats['completed_orders']; ?></h4>
                        <p class="text-[10px] text-stone-400">Hidangan telah diantar ke meja & transaksi lunas.</p>
                    </div>

                    <!-- 4. Dibatalkan -->
                    <div class="bg-[#1c1214] rounded-2xl p-4 border border-red-500/30 flex flex-col justify-between">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-bold text-red-300">4. Pesanan Dibatalkan</span>
                            <span class="w-5 h-5 rounded-full bg-red-500/20 text-red-400 text-[10px] font-black flex items-center justify-center">
                                &times;
                            </span>
                        </div>
                        <h4 class="text-2xl font-black font-serif-title text-red-400 my-1"><?php echo $stats['cancelled_orders']; ?></h4>
                        <p class="text-[10px] text-stone-400">Pesanan dibatalkan pelanggan atau stok menu habis.</p>
                    </div>

                </div>

            </div>


            <!-- ============================================== -->
            <!-- 2.4 GRAFIK & ANALISIS MENU PALING SERING DIPESAN -->
            <!-- ============================================== -->
            <div class="bg-[#121113] rounded-3xl border border-amber-500/30 p-6 sm:p-7 shadow-2xl space-y-6">
                
                <!-- Section Header -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-stone-800">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-amber-500/20 text-amber-400 flex items-center justify-center text-base border border-amber-500/30 shadow-md">
                            <i class="fa-solid fa-chart-column"></i>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="text-lg sm:text-xl font-bold font-serif-title text-stone-100">
                                    Grafik Menu Paling Sering Dipesan
                                </h3>
                                <span class="px-2 py-0.5 rounded-full bg-amber-500/20 text-amber-300 text-[10px] font-extrabold uppercase border border-amber-500/30">
                                    Best Seller
                                </span>
                            </div>
                            <p class="text-xs text-stone-400">
                                Frekuensi dan peringkat hidangan terfavorit pilihan pelanggan Warkop Madam
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 text-xs">
                        <span class="px-3 py-1.5 rounded-xl bg-stone-900 border border-stone-800 text-stone-300 font-semibold flex items-center gap-1.5">
                            <i class="fa-solid fa-fire text-amber-400"></i> Total Item Terjual: <strong class="text-amber-300 font-mono text-sm"><?php echo $total_items_sold; ?> Porsi</strong>
                        </span>
                    </div>
                </div>

                <!-- Grid: Chart on Left (2 cols), Leaderboard on Right (1 col) -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
                    
                    <!-- Left: Interactive Bar Chart -->
                    <div class="lg:col-span-2 bg-[#0c0a09] p-4 sm:p-5 rounded-2xl border border-stone-800 flex flex-col justify-between">
                        <div class="flex items-center justify-between mb-4">
                            <span class="text-xs font-bold text-stone-300 uppercase tracking-wider flex items-center gap-2">
                                <i class="fa-solid fa-chart-bar text-amber-400"></i> Diagram Batang Volume Penjualan
                            </span>
                            <span class="text-[10px] text-stone-500">Berdasarkan data pesanan masuk</span>
                        </div>

                        <?php if (empty($top_menu_labels)): ?>
                            <div class="text-center py-12 text-stone-500 text-xs">
                                <i class="fa-solid fa-chart-pie text-3xl mb-2 text-stone-700 block"></i>
                                Belum ada data pesanan yang cukup untuk membuat grafik.
                            </div>
                        <?php else: ?>
                            <div class="relative w-full h-[280px]">
                                <canvas id="topMenuChart"></canvas>
                            </div>
                        <?php endif; ?>

                        <div class="pt-3 mt-3 border-t border-stone-900 flex flex-wrap items-center justify-between text-[11px] text-stone-400 gap-2">
                            <span><i class="fa-solid fa-circle-info text-amber-400 mr-1"></i> Data dihitung otomatis dari rincian transaksi tersimpan</span>
                            <a href="laporan_bulanan.php" class="text-amber-400 hover:underline font-semibold">Lihat Laporan Penjualan Lengkap &rarr;</a>
                        </div>
                    </div>

                    <!-- Right: Best Seller Leaderboard -->
                    <div class="bg-[#0c0a09] p-4 sm:p-5 rounded-2xl border border-stone-800 flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between mb-3 pb-2 border-b border-stone-800/80">
                                <span class="text-xs font-bold text-amber-300 uppercase tracking-wider flex items-center gap-1.5">
                                    <i class="fa-solid fa-trophy text-amber-400"></i> Top 5 Ranking Terfavorit
                                </span>
                                <span class="text-[10px] text-stone-400 font-semibold">Porsi & Porsi %</span>
                            </div>

                            <div class="space-y-3.5">
                                <?php if (empty($top_items_list)): ?>
                                    <p class="text-xs text-stone-500 py-6 text-center">Belum ada data ranking menu.</p>
                                <?php else: ?>
                                    <?php foreach (array_slice($top_items_list, 0, 5) as $item): ?>
                                        <div class="space-y-1.5">
                                            <div class="flex items-center justify-between text-xs">
                                                <div class="flex items-center gap-2 overflow-hidden">
                                                    <?php if ($item['rank'] === 1): ?>
                                                        <span class="w-5 h-5 rounded-full bg-amber-500/20 text-amber-300 border border-amber-500/40 text-[10px] font-black flex items-center justify-center shrink-0">
                                                            <i class="fa-solid fa-crown text-[9px]"></i>
                                                        </span>
                                                    <?php elseif ($item['rank'] === 2): ?>
                                                        <span class="w-5 h-5 rounded-full bg-stone-300/20 text-stone-200 border border-stone-400/40 text-[10px] font-black flex items-center justify-center shrink-0">
                                                            2
                                                        </span>
                                                    <?php elseif ($item['rank'] === 3): ?>
                                                        <span class="w-5 h-5 rounded-full bg-amber-700/20 text-amber-600 border border-amber-600/40 text-[10px] font-black flex items-center justify-center shrink-0">
                                                            3
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="w-5 h-5 rounded-full bg-stone-900 text-stone-400 text-[10px] font-bold flex items-center justify-center shrink-0">
                                                            <?php echo $item['rank']; ?>
                                                        </span>
                                                    <?php endif; ?>

                                                    <span class="font-bold text-stone-100 truncate"><?php echo htmlspecialchars($item['name']); ?></span>
                                                </div>
                                                <span class="font-mono font-bold text-amber-300 text-xs shrink-0 pl-2">
                                                    <?php echo $item['count']; ?> <span class="text-[10px] text-stone-400 font-normal">porsi</span>
                                                </span>
                                            </div>

                                            <div class="w-full bg-stone-900 rounded-full h-2 overflow-hidden flex">
                                                <div class="h-2 rounded-full <?php echo $item['rank'] === 1 ? 'bg-gradient-to-r from-amber-500 to-amber-300 shadow-sm' : ($item['rank'] === 2 ? 'bg-stone-300' : ($item['rank'] === 3 ? 'bg-amber-600' : 'bg-stone-600')); ?>" 
                                                     style="width: <?php echo min(100, max(8, $item['percentage'] * 2)); ?>%"></div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="pt-4 mt-4 border-t border-stone-800/80 text-center">
                            <a href="kelola_menu.php" class="text-xs text-amber-400 font-bold hover:underline">
                                Kelola Ketersediaan Stok Menu &rarr;
                            </a>
                        </div>
                    </div>

                </div>

            </div>


            <!-- ============================================== -->
            <!-- 2.5 LIVE ANTREAN PESANAN & METODE BAYAR       -->
            <!-- ============================================== -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                <!-- Left: Table Antrean Pesanan (2 Cols) -->
                <div class="lg:col-span-2 bg-[#121113] rounded-3xl border border-stone-800 shadow-2xl overflow-hidden flex flex-col justify-between">
                    <div>
                        <div class="p-5 border-b border-stone-800 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-400 flex items-center justify-center text-xs">
                                    <i class="fa-solid fa-clock-rotate-left"></i>
                                </div>
                                <div>
                                    <h4 class="font-extrabold text-sm sm:text-base text-stone-100 font-serif-title">
                                        Antrean Pesanan Masuk Terkini
                                    </h4>
                                    <p class="text-[11px] text-stone-400">Pesanan baru yang masuk dari HP pelanggan</p>
                                </div>
                            </div>
                            <a href="kelola_pesanan.php" class="text-xs text-amber-400 hover:underline font-semibold">Lihat Semua &rarr;</a>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-xs text-stone-300">
                                <thead class="bg-stone-900/60 text-stone-400 uppercase font-semibold text-[10px] tracking-wider border-b border-stone-800">
                                    <tr>
                                        <th class="py-3 px-4">Waktu</th>
                                        <th class="py-3 px-4">No. Order</th>
                                        <th class="py-3 px-4">Pemesan</th>
                                        <th class="py-3 px-4">Meja</th>
                                        <th class="py-3 px-4">Bukti Bayar</th>
                                        <th class="py-3 px-4">Total</th>
                                        <th class="py-3 px-4">Status</th>
                                        <th class="py-3 px-4 text-right">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-stone-900">
                                    <?php if (empty($orders)): ?>
                                        <tr>
                                            <td colspan="8" class="text-center py-10 text-stone-500">
                                                Belum ada transaksi di database.
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach (array_slice($orders, 0, 6) as $row): ?>
                                            <tr class="hover:bg-stone-900/40 transition">
                                                <td class="py-3 px-4 font-mono text-[11px] text-stone-400 whitespace-nowrap">
                                                    <?php echo date('H:i', strtotime($row['created_at'])); ?>
                                                </td>
                                                <td class="py-3 px-4 font-mono font-bold text-amber-400 whitespace-nowrap">
                                                    <?php echo htmlspecialchars($row['order_number'] ?? '#MDM-' . $row['id']); ?>
                                                </td>
                                                <td class="py-3 px-4 font-semibold text-stone-200">
                                                    <?php echo htmlspecialchars($row['customer_name']); ?>
                                                </td>
                                                <td class="py-3 px-4 whitespace-nowrap">
                                                    <span class="px-2 py-0.5 rounded bg-stone-900 border border-stone-800 text-[10px] text-amber-300 font-semibold">
                                                        <?php echo htmlspecialchars($row['table_number']); ?>
                                                    </span>
                                                </td>
                                                <td class="py-3 px-4 whitespace-nowrap">
                                                    <?php if (!empty($row['payment_proof'])): ?>
                                                        <button onclick="viewPaymentProofModal('<?php echo htmlspecialchars($row['payment_proof']); ?>', '<?php echo htmlspecialchars($row['order_number'] ?? '#MDM-' . $row['id']); ?>', '<?php echo htmlspecialchars($row['customer_name']); ?>', '<?php echo htmlspecialchars($row['payment_method']); ?>')" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-amber-500/15 hover:bg-amber-500/25 border border-amber-500/30 text-amber-300 text-[10px] font-bold transition">
                                                            <i class="fa-solid fa-receipt"></i>
                                                            <span>Lihat Struk</span>
                                                        </button>
                                                    <?php else: ?>
                                                        <span class="text-[10px] text-stone-500 italic">
                                                            <?php echo $row['payment_method'] === 'Tunai' ? 'Tunai di Meja' : 'Belum Ada Struk'; ?>
                                                        </span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="py-3 px-4 font-bold text-amber-400 font-mono text-xs whitespace-nowrap">
                                                    Rp <?php echo number_format($row['total_price'], 0, ',', '.'); ?>
                                                </td>
                                                <td class="py-3 px-4 whitespace-nowrap">
                                                    <?php if ($row['status'] === 'pending'): ?>
                                                        <span class="px-2 py-0.5 rounded-full bg-amber-500/20 text-amber-300 border border-amber-500/30 text-[9px] font-bold uppercase animate-pulse">Pending</span>
                                                    <?php elseif ($row['status'] === 'diproses'): ?>
                                                        <span class="px-2 py-0.5 rounded-full bg-blue-500/20 text-blue-300 border border-blue-500/30 text-[9px] font-bold uppercase">Proses</span>
                                                    <?php elseif ($row['status'] === 'selesai'): ?>
                                                        <span class="px-2 py-0.5 rounded-full bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 text-[9px] font-bold uppercase">Selesai</span>
                                                    <?php else: ?>
                                                        <span class="px-2 py-0.5 rounded-full bg-red-500/20 text-red-300 border border-red-500/30 text-[9px] font-bold uppercase">Batal</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="py-3 px-4 text-right whitespace-nowrap">
                                                    <form action="" method="POST" class="inline">
                                                        <?php echo csrf_field(); ?>
                                                        <input type="hidden" name="order_id" value="<?php echo intval($row['id']); ?>">
                                                        <select name="status" onchange="this.form.submit()" class="px-2 py-1 bg-stone-900 border border-stone-700 rounded-lg text-[10px] text-stone-200 focus:outline-none focus:border-amber-500 cursor-pointer">
                                                            <option value="pending" <?php echo $row['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                            <option value="diproses" <?php echo $row['status'] == 'diproses' ? 'selected' : ''; ?>>Diproses</option>
                                                            <option value="selesai" <?php echo $row['status'] == 'selesai' ? 'selected' : ''; ?>>Selesai</option>
                                                            <option value="dibatalkan" <?php echo $row['status'] == 'dibatalkan' ? 'selected' : ''; ?>>Batal</option>
                                                        </select>
                                                        <input type="hidden" name="update_status" value="1">
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Right: Proporsi Metode Pembayaran (1 Col) -->
                <div class="bg-[#121113] rounded-3xl p-6 border border-stone-800 shadow-2xl flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between pb-4 border-b border-stone-800 mb-4">
                            <div>
                                <h4 class="font-extrabold text-sm sm:text-base text-stone-100 font-serif-title">
                                    Metode Pembayaran
                                </h4>
                                <p class="text-[11px] text-stone-400">Proporsi transaksi selesai</p>
                            </div>
                            <i class="fa-solid fa-wallet text-amber-400 text-lg"></i>
                        </div>

                        <div class="space-y-4">
                            <!-- QRIS -->
                            <div>
                                <div class="flex justify-between text-xs font-semibold mb-1">
                                    <span class="text-amber-300 flex items-center gap-1.5"><i class="fa-solid fa-qrcode text-amber-400"></i> QRIS Instant</span>
                                    <span class="text-stone-200 font-mono">Rp <?php echo number_format($payment_breakdown['QRIS'], 0, ',', '.'); ?></span>
                                </div>
                                <div class="w-full bg-stone-900 rounded-full h-2 overflow-hidden">
                                    <?php 
                                        $tot = max(1, array_sum($payment_breakdown));
                                        $pct_qris = round(($payment_breakdown['QRIS'] / $tot) * 100);
                                    ?>
                                    <div class="bg-amber-500 h-2 rounded-full" style="width: <?php echo $pct_qris; ?>%"></div>
                                </div>
                            </div>

                            <!-- Transfer Mandiri -->
                            <div>
                                <div class="flex justify-between text-xs font-semibold mb-1">
                                    <span class="text-blue-300 flex items-center gap-1.5"><i class="fa-solid fa-building-columns text-blue-400"></i> Bank Mandiri</span>
                                    <span class="text-stone-200 font-mono">Rp <?php echo number_format($payment_breakdown['Transfer Mandiri'], 0, ',', '.'); ?></span>
                                </div>
                                <div class="w-full bg-stone-900 rounded-full h-2 overflow-hidden">
                                    <?php $pct_man = round(($payment_breakdown['Transfer Mandiri'] / $tot) * 100); ?>
                                    <div class="bg-blue-500 h-2 rounded-full" style="width: <?php echo $pct_man; ?>%"></div>
                                </div>
                            </div>

                            <!-- Tunai -->
                            <div>
                                <div class="flex justify-between text-xs font-semibold mb-1">
                                    <span class="text-emerald-300 flex items-center gap-1.5"><i class="fa-solid fa-money-bill-wave text-emerald-400"></i> Tunai / Kasir</span>
                                    <span class="text-stone-200 font-mono">Rp <?php echo number_format($payment_breakdown['Tunai'], 0, ',', '.'); ?></span>
                                </div>
                                <div class="w-full bg-stone-900 rounded-full h-2 overflow-hidden">
                                    <?php $pct_tun = round(($payment_breakdown['Tunai'] / $tot) * 100); ?>
                                    <div class="bg-emerald-500 h-2 rounded-full" style="width: <?php echo $pct_tun; ?>%"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="pt-4 mt-4 border-t border-stone-800 text-center">
                        <a href="laporan_bulanan.php" class="text-xs text-amber-400 font-bold hover:underline">
                            Lihat Rekapitulasi Lengkap &rarr;
                        </a>
                    </div>
                </div>

            </div>

        </main>

        <!-- Footer -->
        <footer class="text-center text-xs text-stone-500 py-6 border-t border-stone-900 flex items-center justify-center gap-3">
            <span>&copy; 2026 <span class="text-amber-400 font-medium">Warkop Madam</span> &bull; Panel Administrator</span>
            <span>&bull;</span>
            <span class="inline-flex items-center gap-1.5 text-emerald-400 text-[11px]">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
                Auto-refresh aktif (15s)
            </span>
        </footer>

    <!-- Proof Modal -->
    <div id="proof-modal" class="fixed inset-0 z-50 bg-black/90 backdrop-blur-md hidden flex items-center justify-center p-4" onclick="closeProofModal()">
        <div class="max-w-md w-full bg-stone-950 border border-amber-500/40 rounded-3xl overflow-hidden shadow-2xl p-4 relative" onclick="event.stopPropagation()">
            <button onclick="closeProofModal()" class="absolute top-4 right-4 z-10 w-8 h-8 rounded-full bg-black/70 text-stone-300 hover:text-white flex items-center justify-center border border-stone-700">
                <i class="fa-solid fa-xmark"></i>
            </button>
            <div class="mb-3">
                <span class="text-[10px] text-amber-400 font-bold uppercase tracking-wider block" id="proof-modal-method">BUKTI PEMBAYARAN</span>
                <h4 class="font-bold text-base text-stone-100 font-serif-title" id="proof-modal-title">Struk Transfer</h4>
                <p class="text-[11px] text-stone-400" id="proof-modal-customer">-</p>
            </div>
            <div class="w-full max-h-[70vh] rounded-2xl bg-black overflow-hidden flex items-center justify-center border border-stone-800 p-1">
                <img id="proof-modal-img" src="" alt="Bukti Pembayaran" class="max-w-full max-h-[65vh] object-contain rounded-xl">
            </div>
            <div class="mt-3 text-center">
                <a id="proof-modal-download" href="" target="_blank" download class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-stone-900 hover:bg-stone-800 text-amber-300 text-xs font-bold border border-stone-800 transition">
                    <i class="fa-solid fa-download text-xs"></i>
                    <span>Buka / Unduh Gambar Asli</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Auto Refresh Timer & Modal JS -->
    <script>
        function viewPaymentProofModal(imgSrc, orderNo, customer, method) {
            document.getElementById('proof-modal-img').src = imgSrc;
            document.getElementById('proof-modal-download').href = imgSrc;
            document.getElementById('proof-modal-title').innerText = 'Struk: ' + orderNo;
            document.getElementById('proof-modal-customer').innerText = 'Pemesan: ' + customer + ' (' + method + ')';
            document.getElementById('proof-modal-method').innerText = 'BUKTI PEMBAYARAN ' + method.toUpperCase();
            document.getElementById('proof-modal').classList.remove('hidden');
        }

        function closeProofModal() {
            document.getElementById('proof-modal').classList.add('hidden');
        }

        // Initialize Best Seller Chart with Chart.js
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('topMenuChart');
            if (ctx) {
                const labels = <?php echo json_encode($top_menu_labels); ?>;
                const dataCounts = <?php echo json_encode($top_menu_counts); ?>;

                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Jumlah Terjual (Porsi)',
                            data: dataCounts,
                            backgroundColor: [
                                'rgba(245, 158, 11, 0.9)', // Amber 500
                                'rgba(217, 119, 6, 0.85)', // Amber 600
                                'rgba(251, 191, 36, 0.85)', // Amber 400
                                'rgba(59, 130, 246, 0.8)',  // Blue 500
                                'rgba(16, 185, 129, 0.8)', // Emerald 500
                                'rgba(168, 85, 247, 0.8)'  // Purple 500
                            ],
                            borderColor: [
                                '#f59e0b',
                                '#d97706',
                                '#fbbf24',
                                '#3b82f6',
                                '#10b981',
                                '#a855f7'
                            ],
                            borderWidth: 1.5,
                            borderRadius: 8,
                            borderSkipped: false,
                            barPercentage: 0.65,
                            categoryPercentage: 0.8
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        indexAxis: 'y', // Horizontal bar chart for clear menu name readability
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                backgroundColor: '#18181b',
                                titleColor: '#fbbf24',
                                bodyColor: '#f4f4f5',
                                borderColor: '#3f3f46',
                                borderWidth: 1,
                                padding: 12,
                                boxPadding: 6,
                                usePointStyle: true,
                                callbacks: {
                                    label: function(context) {
                                        return ' ' + context.parsed.x + ' porsi telah dipesan';
                                    }
                                }
                            }
                        },
                        scales: {
                            x: {
                                beginAtZero: true,
                                ticks: {
                                    stepSize: 1,
                                    color: '#a1a1aa',
                                    font: {
                                        size: 11,
                                        weight: '600',
                                        family: "'Plus Jakarta Sans', sans-serif"
                                    }
                                },
                                grid: {
                                    color: 'rgba(255, 255, 255, 0.05)',
                                    drawBorder: false
                                }
                            },
                            y: {
                                ticks: {
                                    color: '#e4e4e7',
                                    font: {
                                        size: 11,
                                        weight: 'bold',
                                        family: "'Plus Jakarta Sans', sans-serif"
                                    }
                                },
                                grid: {
                                    display: false,
                                    drawBorder: false
                                }
                            }
                        }
                    }
                });
            }
        });

        // Auto Refresh
        setInterval(() => {
            const activeTag = document.activeElement ? document.activeElement.tagName.toLowerCase() : '';
            if (activeTag !== 'select' && activeTag !== 'input') {
                location.reload();
            }
        }, 15000);
    </script>

</body>
</html>
