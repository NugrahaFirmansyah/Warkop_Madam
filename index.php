<?php
require_once __DIR__ . '/security.php';

// Inisialisasi session tamu/klien jika belum ada
if (!isset($_SESSION['role'])) {
    $_SESSION['role'] = 'klien';
    $_SESSION['nama_lengkap'] = 'Pelanggan Warkop';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Warkop Madam - Selamat Datang</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Playfair+Display:ital,wght@0,700;1,600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
        }
        .font-serif-title {
            font-family: 'Playfair Display', serif;
        }
        
        /* Floating & Glow Animations */
        @keyframes floatLogo {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-8px); }
        }
        @keyframes pulseGlow {
            0%, 100% { box-shadow: 0 0 30px rgba(217, 119, 6, 0.25), 0 0 60px rgba(0, 0, 0, 0.8); }
            50% { box-shadow: 0 0 50px rgba(245, 158, 11, 0.45), 0 0 80px rgba(217, 119, 6, 0.3); }
        }

        .animate-logo {
            animation: floatLogo 4.5s ease-in-out infinite;
        }
        .logo-box-glow {
            animation: pulseGlow 4s infinite;
        }
    </style>
</head>
<body class="bg-black text-stone-100 min-h-screen flex items-center justify-center p-4 relative overflow-hidden selection:bg-amber-500 selection:text-stone-950">

    <!-- Ambient Glowing Background Elements -->
    <div class="absolute -top-40 -left-40 w-[450px] h-[450px] bg-amber-600/15 rounded-full blur-[120px] pointer-events-none"></div>
    <div class="absolute -bottom-40 -right-40 w-[450px] h-[450px] bg-orange-600/15 rounded-full blur-[120px] pointer-events-none"></div>
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] bg-amber-500/10 rounded-full blur-[150px] pointer-events-none"></div>

    <!-- Main Card Container -->
    <div class="max-w-md w-full relative z-10">
        
        <!-- Glassmorphism Card -->
        <div class="bg-stone-950/85 backdrop-blur-2xl rounded-3xl p-8 sm:p-10 border border-stone-800 shadow-2xl text-center relative overflow-hidden transition-all duration-300 hover:border-amber-500/40">
            
            <!-- Subtle Top Glow Accent -->
            <div class="absolute top-0 left-1/2 -translate-x-1/2 w-48 h-1 bg-gradient-to-r from-transparent via-amber-500 to-transparent rounded-full"></div>

            <!-- Logo Section -->
            <div class="flex flex-col items-center justify-center mt-2 mb-6">
                
                <!-- Logo Frame -->
                <div class="relative mb-6 group animate-logo">
                    <div class="w-44 h-44 sm:w-48 sm:h-48 rounded-2xl bg-black p-2 logo-box-glow border border-amber-500/30 overflow-hidden shadow-2xl transition duration-500 group-hover:scale-105">
                        <img src="assets/logo.png" 
                             alt="Warkop Madam Logo" 
                             class="w-full h-full object-contain filter drop-shadow-[0_4px_12px_rgba(255,255,255,0.05)]">
                    </div>
                </div>

                <!-- Tagline Badge -->
                <div class="inline-flex items-center gap-2 px-4 py-1 rounded-full bg-stone-900 border border-amber-500/30 text-amber-300 text-xs font-semibold tracking-wider uppercase">
                    <i class="fa-solid fa-mug-hot text-amber-400 text-xs"></i>
                    <span>Authentic Coffee & Eatery</span>
                </div>

                <p class="text-stone-400 text-sm mt-3.5 max-w-xs leading-relaxed">
                    Nikmati kopi khas dalam sajian panas dan dingin dengan suasana santai di madam
                </p>
            </div>

            <!-- Features / Menu Button Action -->
            <div class="mt-6 space-y-4">
                
                <!-- Primary Menu Button -->
                <a href="menu.php" 
                   class="group relative w-full flex items-center justify-between px-6 py-4 rounded-2xl bg-gradient-to-r from-amber-600 via-amber-500 to-amber-600 text-white font-bold text-lg shadow-lg shadow-amber-950/60 hover:shadow-amber-500/30 hover:from-amber-500 hover:to-amber-400 transform hover:-translate-y-0.5 active:translate-y-0 transition-all duration-300 border border-amber-400/40 overflow-hidden">
                    
                    <div class="flex items-center gap-3.5">
                        <div class="w-11 h-11 rounded-xl bg-white/15 flex items-center justify-center group-hover:rotate-6 transition-transform">
                            <i class="fa-solid fa-book-open text-amber-100 text-lg"></i>
                        </div>
                        <div class="text-left">
                            <span class="block text-base sm:text-lg font-bold leading-tight">Lihat Menu</span>
                            <span class="block text-xs font-normal text-amber-100/90">Katalog Makanan & Minuman</span>
                        </div>
                    </div>

                    <div class="w-9 h-9 rounded-full bg-stone-900/40 flex items-center justify-center group-hover:translate-x-1.5 transition-transform duration-300">
                        <i class="fa-solid fa-arrow-right text-sm"></i>
                    </div>

                    <!-- Button Shine Reflection -->
                    <div class="absolute inset-0 -translate-x-full group-hover:translate-x-full bg-gradient-to-r from-transparent via-white/20 to-transparent transition-transform duration-1000"></div>
                </a>

                <!-- Quick Highlights Badges -->
                <div class="grid grid-cols-3 gap-2.5 pt-2 text-center">
                    <div class="p-2.5 rounded-xl bg-stone-900/60 border border-stone-800 text-stone-300 hover:border-amber-500/30 transition">
                        <i class="fa-solid fa-fire text-amber-400 text-sm block mb-1"></i>
                        <span class="text-[11px] font-medium block">Kopi Segar</span>
                    </div>
                    <div class="p-2.5 rounded-xl bg-stone-900/60 border border-stone-800 text-stone-300 hover:border-amber-500/30 transition">
                        <i class="fa-solid fa-wifi text-amber-400 text-sm block mb-1"></i>
                        <span class="text-[11px] font-medium block">Free WiFi</span>
                    </div>
                    <div class="p-2.5 rounded-xl bg-stone-900/60 border border-stone-800 text-stone-300 hover:border-amber-500/30 transition">
                        <i class="fa-solid fa-couch text-amber-400 text-sm block mb-1"></i>
                        <span class="text-[11px] font-medium block">Nyaman</span>
                    </div>
                </div>

            </div>

            <!-- Footer Note -->
            <div class="mt-8 pt-4 border-t border-stone-800/80 flex items-center justify-between text-xs text-stone-500">
                <span class="flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-ping"></span>
                    <span class="text-stone-400 font-medium">Buka Setiap Hari</span>
                </span>
                <span class="text-stone-400">08:00 - 23:00 WIB</span>
            </div>

        </div>

        <!-- Copyright & Admin Access Link -->
        <div class="text-center text-xs text-stone-500 mt-5 flex items-center justify-center gap-3">
            <span>&copy; 2026 <span class="text-amber-400 font-medium">Warkop Madam</span></span>
            <span>&bull;</span>
            <a href="login_admin.php" class="text-stone-400 hover:text-amber-400 transition inline-flex items-center gap-1">
                <i class="fa-solid fa-lock text-[10px]"></i>
                <span>Akses Admin</span>
            </a>
        </div>

    </div>

</body>
</html>