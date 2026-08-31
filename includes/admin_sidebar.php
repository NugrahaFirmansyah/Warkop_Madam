<?php
/**
 * Warkop Madam - Reusable Admin Sidebar & Mobile Navigation
 * Variable params:
 * - $active_tab: 'dashboard' | 'pesanan' | 'menu' | 'laporan'
 * - $pending_badge: int (optional, default will query pending count if available)
 */

if (!isset($active_tab)) {
    $current_script = basename($_SERVER['SCRIPT_NAME'] ?? '');
    if (strpos($current_script, 'dashboard') !== false) {
        $active_tab = 'dashboard';
    } elseif (strpos($current_script, 'pesanan') !== false) {
        $active_tab = 'pesanan';
    } elseif (strpos($current_script, 'menu') !== false) {
        $active_tab = 'menu';
    } elseif (strpos($current_script, 'laporan') !== false) {
        $active_tab = 'laporan';
    } else {
        $active_tab = 'dashboard';
    }
}

$admin_display_name = $_SESSION['nama_lengkap'] ?? 'Administrator';

// Query pending orders badge if not provided
if (!isset($pending_badge)) {
    try {
        if (function_exists('get_db_connection')) {
            $pdo_sb = get_db_connection();
            $pending_badge = intval($pdo_sb->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'")->fetchColumn());
        } else {
            $pending_badge = 0;
        }
    } catch (Exception $e) {
        $pending_badge = 0;
    }
}
?>

<!-- ============================================== -->
<!-- 1. LEFT SIDEBAR DESKTOP                        -->
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

        <!-- Navigation Links -->
        <nav class="space-y-1.5 text-xs font-semibold">
            
            <!-- Dashboard -->
            <?php if ($active_tab === 'dashboard'): ?>
                <a href="dashboard_admin.php" class="flex items-center gap-3 px-4 py-3 rounded-2xl bg-gradient-to-r from-amber-600 to-amber-500 text-stone-950 font-bold shadow-lg shadow-amber-950/40 transition">
                    <i class="fa-solid fa-gauge-high text-base text-stone-950"></i>
                    <span class="text-sm font-extrabold">Dashboard</span>
                </a>
            <?php else: ?>
                <a href="dashboard_admin.php" class="flex items-center gap-3 px-4 py-3 rounded-2xl text-stone-400 hover:text-white hover:bg-stone-900/90 transition group">
                    <i class="fa-solid fa-gauge-high text-base text-stone-400 group-hover:text-amber-400 transition"></i>
                    <span>Dashboard</span>
                </a>
            <?php endif; ?>

            <!-- Kelola Pesanan -->
            <?php if ($active_tab === 'pesanan'): ?>
                <a href="kelola_pesanan.php" class="flex items-center justify-between px-4 py-3 rounded-2xl bg-gradient-to-r from-amber-600 to-amber-500 text-stone-950 font-bold shadow-lg shadow-amber-950/40 transition">
                    <div class="flex items-center gap-3">
                        <i class="fa-solid fa-bell-concierge text-base text-stone-950"></i>
                        <span class="text-sm font-extrabold">Kelola Pesanan</span>
                    </div>
                    <?php if ($pending_badge > 0): ?>
                        <span class="px-2 py-0.5 rounded-full bg-stone-950 text-amber-400 text-[10px] font-black animate-pulse">
                            <?php echo $pending_badge; ?>
                        </span>
                    <?php endif; ?>
                </a>
            <?php else: ?>
                <a href="kelola_pesanan.php" class="flex items-center justify-between px-4 py-3 rounded-2xl text-stone-400 hover:text-white hover:bg-stone-900/90 transition group">
                    <div class="flex items-center gap-3">
                        <i class="fa-solid fa-bell-concierge text-base text-stone-400 group-hover:text-amber-400 transition"></i>
                        <span>Kelola Pesanan</span>
                    </div>
                    <?php if ($pending_badge > 0): ?>
                        <span class="px-2 py-0.5 rounded-full bg-amber-500 text-stone-950 text-[10px] font-black animate-pulse">
                            <?php echo $pending_badge; ?>
                        </span>
                    <?php endif; ?>
                </a>
            <?php endif; ?>

            <!-- Tambah & Kelola Menu -->
            <?php if ($active_tab === 'menu'): ?>
                <a href="kelola_menu.php" class="flex items-center gap-3 px-4 py-3 rounded-2xl bg-gradient-to-r from-amber-600 to-amber-500 text-stone-950 font-bold shadow-lg shadow-amber-950/40 transition">
                    <i class="fa-solid fa-utensils text-base text-stone-950"></i>
                    <span class="text-sm font-extrabold">Tambah & Kelola Menu</span>
                </a>
            <?php else: ?>
                <a href="kelola_menu.php" class="flex items-center gap-3 px-4 py-3 rounded-2xl text-stone-400 hover:text-white hover:bg-stone-900/90 transition group">
                    <i class="fa-solid fa-utensils text-base text-stone-400 group-hover:text-amber-400 transition"></i>
                    <span>Tambah & Kelola Menu</span>
                </a>
            <?php endif; ?>

            <!-- Laporan Bulanan -->
            <?php if ($active_tab === 'laporan'): ?>
                <a href="laporan_bulanan.php" class="flex items-center gap-3 px-4 py-3 rounded-2xl bg-gradient-to-r from-amber-600 to-amber-500 text-stone-950 font-bold shadow-lg shadow-amber-950/40 transition">
                    <i class="fa-solid fa-file-invoice-dollar text-base text-stone-950"></i>
                    <span class="text-sm font-extrabold">Laporan Bulanan</span>
                </a>
            <?php else: ?>
                <a href="laporan_bulanan.php" class="flex items-center gap-3 px-4 py-3 rounded-2xl text-stone-400 hover:text-white hover:bg-stone-900/90 transition group">
                    <i class="fa-solid fa-file-invoice-dollar text-base text-stone-400 group-hover:text-amber-400 transition"></i>
                    <span>Laporan Bulanan</span>
                </a>
            <?php endif; ?>

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
            <span class="text-xs font-bold text-stone-200 block truncate"><?php echo htmlspecialchars($admin_display_name); ?></span>
            <span class="text-[10px] text-emerald-400 flex items-center gap-1 font-medium">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span> Online
            </span>
        </div>
    </div>

</aside>
