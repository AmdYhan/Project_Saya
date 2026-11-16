<?php
session_start();
// Jika sudah login, langsung arahkan sesuai rolenya
if (isset($_SESSION['username'])) {
    $role = strtolower($_SESSION['role'] ?? '');
    if ($role === 'admin' || $role === 'koordinator' || $role === 'engginer' || $role === 'pimpinan') {
        header("Location: dashboard_admin.php");
        exit();
    } else {
        header("Location: beranda.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Login - Lintasarta Samarinda</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .bg-grid-pattern {
            background-image: radial-gradient(rgba(255, 255, 255, 0.14) 1px, transparent 1px);
            background-size: 24px 24px;
        }
        
        /* Keyframes untuk Animasi Masuk Halaman Login */
        @keyframes fadeInSlide {
            0% {
                opacity: 0;
                transform: translateY(20px) scale(0.98);
            }
            100% {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        .animate-login-card {
            animation: fadeInSlide 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }
    </style>
</head>
<body class="text-[#0d1c30] h-screen w-screen overflow-hidden flex items-center justify-center p-3 sm:p-4 bg-gradient-to-br from-[#061838] via-[#003c99] to-[#0052cc] relative">

    <!-- Dekorasi Latar Belakang Halus -->
    <div class="absolute inset-0 bg-grid-pattern pointer-events-none"></div>
    <div class="absolute -top-28 -left-28 w-80 h-80 bg-blue-400/20 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-32 -right-24 w-96 h-96 bg-cyan-400/15 rounded-full blur-3xl pointer-events-none"></div>

    <!-- KARTU LOGIN (POSISI PAS DI TENGAH SEIMBANG) -->
    <div class="relative z-10 w-full max-w-sm sm:max-w-md bg-white/95 backdrop-blur-md rounded-3xl shadow-2xl shadow-slate-950/30 border border-white/40 p-5 sm:p-7 space-y-4 my-auto max-h-[96vh] overflow-y-auto animate-login-card">
        
        <!-- Logo & Header (Diperbaiki agar proporsional & tidak terpotong) -->
       <div class="text-center space-y-1">
            <div class="w-full flex justify-center items-center py-1">
                <img src="../assets/img/Lintasarta_logo.png" alt="Logo Lintasarta" class="h-12 sm:h-14 w-auto object-contain ml-5">
            </div>
            <h1 class="font-extrabold text-base sm:text-lg text-[#0d1c30] tracking-tight pt-1">SAMARINDA</h1>
            <p class="text-[11px] sm:text-xs text-slate-400 font-medium">Sistem Patroli & Monitoring OSP</p>
        </div>

        <!-- FORM LOGIN -->
        <form action="../process/proses_login.php" method="POST" class="space-y-3.5 sm:space-y-4 pt-1">
            
            <!-- INPUT TERSEMBUNYI UNTUK MENYIMPAN ROLE YANG DIPILIH -->
            <input type="hidden" name="selected_role" id="selectedRole" value="teknisi">

            <!-- TAB SWITCHER DENGAN ANIMASI BERGESER -->
            <div class="relative flex bg-slate-100 p-1 rounded-2xl">
                <!-- Latar Belakang Geser (Sliding Pill) -->
                <div id="sliderPill" class="absolute top-1 bottom-1 w-[calc(50%-4px)] bg-[#0052cc] rounded-xl shadow-md transition-all duration-300 ease-in-out left-1"></div>

                <!-- Tombol Teknisi -->
                <button type="button" onclick="setRole('teknisi')" id="btnTeknisi" class="relative z-10 flex-1 flex items-center justify-center gap-1.5 sm:gap-2 py-2 text-[11px] sm:text-xs font-bold transition-colors duration-200 text-white">
                    <span class="material-symbols-outlined text-[16px] sm:text-[18px]">engineering</span>
                    Teknisi Lapangan
                </button>

                <!-- Tombol Admin -->
                <button type="button" onclick="setRole('admin')" id="btnAdmin" class="relative z-10 flex-1 flex items-center justify-center gap-1.5 sm:gap-2 py-2 text-[11px] sm:text-xs font-bold transition-colors duration-200 text-slate-500">
                    <span class="material-symbols-outlined text-[16px] sm:text-[18px]">admin_panel_settings</span>
                    Admin / Pimpinan
                </button>
            </div>

            <!-- Input Username -->
            <div class="space-y-1">
                <label class="text-[11px] sm:text-xs font-bold text-slate-700 block">NIP / Username <span class="text-rose-500">*</span></label>
                <div class="relative flex items-center">
                    <span class="absolute left-3.5 text-slate-400 material-symbols-outlined text-[18px] sm:text-[20px]">badge</span>
                    <input type="text" name="username" required placeholder="Masukkan username Anda" 
                           class="w-full bg-slate-50 border border-slate-200 rounded-2xl py-2.5 sm:py-3 pl-11 pr-4 text-xs font-semibold text-[#0d1c30] focus:outline-none focus:border-[#0052cc] focus:bg-white transition">
                </div>
            </div>

            <!-- Input Password -->
            <div class="space-y-1">
                <label class="text-[11px] sm:text-xs font-bold text-slate-700 block">Password <span class="text-rose-500">*</span></label>
                <div class="relative flex items-center">
                    <span class="absolute left-3.5 text-slate-400 material-symbols-outlined text-[18px] sm:text-[20px]">lock</span>
                    <input type="password" name="password" id="passwordInput" required placeholder="••••••••" 
                           class="w-full bg-slate-50 border border-slate-200 rounded-2xl py-2.5 sm:py-3 pl-11 pr-11 text-xs font-semibold text-[#0d1c30] focus:outline-none focus:border-[#0052cc] focus:bg-white transition">
                    <button type="button" onclick="togglePassword()" class="absolute right-3.5 text-slate-400 hover:text-slate-600 focus:outline-none">
                        <span class="material-symbols-outlined text-[18px] sm:text-[20px]" id="eyeIcon">visibility</span>
                    </button>
                </div>
            </div>

            <!-- Opsi Tambahan -->
            <div class="flex items-center justify-between text-[11px] sm:text-xs pt-0.5">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="remember" class="w-4 h-4 rounded border-slate-300 text-[#0052cc] focus:ring-blue-500">
                    <span class="text-slate-500 font-medium">Ingat sesi ini</span>
                </label>
                <a href="#" class="text-[#0052cc] font-bold hover:underline">Bantuan NOC</a>
            </div>

            <!-- Tombol Submit -->
            <button type="submit" class="w-full bg-[#0052cc] hover:bg-blue-700 text-white font-bold py-3 sm:py-3.5 rounded-2xl shadow-lg shadow-blue-500/20 active:scale-[0.98] transition flex items-center justify-center gap-2 text-xs">
                <span>Masuk ke Sistem</span>
                <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
            </button>

        </form>
    </div>

    <!-- SCRIPT ANIMASI BERGESER & VISIBILITY PASSWORD -->
    <script>
        function setRole(role) {
            const slider = document.getElementById('sliderPill');
            const btnTeknisi = document.getElementById('btnTeknisi');
            const btnAdmin = document.getElementById('btnAdmin');
            const selectedRoleInput = document.getElementById('selectedRole');

            selectedRoleInput.value = role;

            if (role === 'admin') {
                // Geser pil ke kanan
                slider.style.transform = 'translateX(100%)';
                btnAdmin.classList.remove('text-slate-500');
                btnAdmin.classList.add('text-white');
                btnTeknisi.classList.remove('text-white');
                btnTeknisi.classList.add('text-slate-500');
            } else {
                // Kembalikan pil ke kiri
                slider.style.transform = 'translateX(0)';
                btnTeknisi.classList.remove('text-slate-500');
                btnTeknisi.classList.add('text-white');
                btnAdmin.classList.remove('text-white');
                btnAdmin.classList.add('text-slate-500');
            }
        }

        function togglePassword() {
            const passwordInput = document.getElementById('passwordInput');
            const eyeIcon = document.getElementById('eyeIcon');
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.textContent = 'visibility_off';
            } else {
                passwordInput.type = 'password';
                eyeIcon.textContent = 'visibility';
            }
        }
    </script>
</body>
</html>