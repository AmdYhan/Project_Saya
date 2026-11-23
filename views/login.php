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

// Ambil pesan error dari session (jika ada)
$login_error = '';
if (!empty($_SESSION['login_error'])) {
    $login_error = $_SESSION['login_error'];
    unset($_SESSION['login_error']);
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
            background-image: radial-gradient(rgba(255, 255, 255, 0.08) 1px, transparent 1px);
            background-size: 24px 24px;
        }

        @keyframes gentleFloat {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-6px); }
            100% { transform: translateY(0px); }
        }
        .animate-float { animation: gentleFloat 4.5s ease-in-out infinite; }
        
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

        .animate-fade-in {
            animation: fadeInSlide 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        /* Animasi notifikasi error */
        @keyframes shakeIn {
            0% { opacity: 0; transform: translateY(-10px); }
            30% { transform: translateX(-6px); }
            50% { transform: translateX(6px); }
            70% { transform: translateX(-3px); }
            100% { opacity: 1; transform: translateY(0) translateX(0); }
        }
        .animate-shake-in {
            animation: shakeIn 0.5s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        ::-webkit-scrollbar { width: 4px; }
        ::-webkit-scrollbar-thumb { background-color: rgba(0, 0, 0, 0.1); border-radius: 9999px; }
    </style>
</head>
<body class="text-[#0d1c30] h-screen w-screen overflow-hidden flex items-center justify-center bg-gradient-to-br from-[#061838] via-[#003c99] to-[#0052cc] relative p-3 sm:p-6 lg:p-12">

    <!-- Dekorasi Latar Belakang Halus -->
    <div class="absolute inset-0 bg-grid-pattern pointer-events-none"></div>
    <div class="absolute -top-28 -left-28 w-96 h-96 bg-blue-400/20 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-32 -right-24 w-96 h-96 bg-cyan-400/15 rounded-full blur-3xl pointer-events-none"></div>

    <!-- KONTAINER UTAMA -->
    <div class="relative z-10 w-full max-w-sm sm:max-w-md md:max-w-5xl bg-white md:rounded-[32px] rounded-3xl shadow-2xl shadow-slate-950/40 border border-white/20 overflow-hidden flex flex-col md:flex-row my-auto max-h-[96vh] md:max-h-[750px] animate-fade-in">
        
        <!-- SISI KIRI: GAMBAR ILUSTRASI 2D -->
        <div class="hidden md:flex md:w-1/2 bg-gradient-to-tr from-[#061838] to-[#004fcc] p-8 flex-col justify-between items-center text-white relative overflow-hidden border-r border-slate-100 flex-shrink-0">
            <div class="absolute inset-0 bg-grid-pattern opacity-40 pointer-events-none"></div>
            
            <div class="w-full text-left relative z-10">
                <span class="bg-white/10 backdrop-blur-md text-[10px] tracking-widest uppercase font-extrabold px-3 py-1.5 rounded-full border border-white/10">
                    OSP Samarinda Patrol
                </span>
            </div>

            <div class="relative z-10 my-auto flex justify-center items-center w-full max-w-md animate-float py-4">
                <img src="../assets/img/login_ilustration.png" alt="Ilustrasi OSP Samarinda Patrol" class="w-full h-auto object-contain rounded-2xl drop-shadow-2xl">
            </div>

            <div class="text-center w-full max-w-sm relative z-10">
                <h3 class="font-extrabold text-base leading-snug text-blue-50">Menjaga Konektivitas Tanpa Batas</h3>
                <p class="text-xs text-blue-200 mt-1 leading-relaxed">Sistem Monitoring Terpadu untuk pengawasan koridor serat optik yang aman, andal, dan responsif.</p>
            </div>
        </div>

        <!-- SISI KANAN: FORM LOGIN -->
        <div class="w-full md:w-1/2 p-5 sm:p-7 lg:p-10 flex flex-col justify-center overflow-y-auto bg-white/95 backdrop-blur-md">
            <div class="w-full max-w-md mx-auto space-y-4">
                
                <!-- Logo & Header -->
                <div class="text-center md:text-left space-y-1">
                    <div class="flex justify-center md:justify-start items-center py-1">
                        <img src="../assets/img/Lintasarta_logo.png" alt="Logo Lintasarta" class="h-10 sm:h-12 w-auto object-contain">
                    </div>
                    <h1 class="font-extrabold text-base sm:text-lg text-[#0d1c30] tracking-tight pt-1">SAMARINDA</h1>
                    <p class="text-[11px] sm:text-xs text-slate-400 font-medium">Sistem Patroli & Monitoring OSP</p>
                </div>

                <!-- NOTIFIKASI ERROR LOGIN (IN-PAGE, BUKAN ALERT BROWSER) -->
                <?php if (!empty($login_error)): ?>
                <div id="notifError" class="animate-shake-in bg-rose-50 border border-rose-200 text-rose-700 px-4 py-3 rounded-2xl text-xs font-bold flex items-center gap-2.5 shadow-sm">
                    <div class="w-8 h-8 bg-rose-100 rounded-xl flex items-center justify-center flex-shrink-0 border border-rose-200">
                        <span class="material-symbols-outlined text-[18px] text-rose-600">error</span>
                    </div>
                    <div>
                        <p class="font-extrabold text-rose-800 text-xs"><?php echo htmlspecialchars($login_error); ?></p>
                        <p class="text-[10px] text-rose-500 font-medium mt-0.5">Silakan periksa kembali data login Anda.</p>
                    </div>
                </div>
                <?php endif; ?>

                <!-- FORM LOGIN -->
                <form action="../process/proses_login.php" method="POST" class="space-y-3.5 sm:space-y-4">
                    
                    <input type="hidden" name="selected_role" id="selectedRole" value="teknisi">

                    <!-- Tab Switcher -->
                    <div class="relative flex bg-slate-100 p-1 rounded-2xl">
                        <div id="sliderPill" class="absolute top-1 bottom-1 w-[calc(50%-4px)] bg-[#0052cc] rounded-xl shadow-md transition-all duration-300 ease-in-out left-1"></div>

                        <button type="button" onclick="setRole('teknisi')" id="btnTeknisi" class="relative z-10 flex-1 flex items-center justify-center gap-1.5 sm:gap-2 py-2 text-[11px] sm:text-xs font-bold transition-colors duration-200 text-white">
                            <span class="material-symbols-outlined text-[16px] sm:text-[18px]">engineering</span>
                            Teknisi Lapangan
                        </button>

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
        </div>

    </div>

    <!-- SCRIPT INTERAKTIF -->
    <script>
        function setRole(role) {
            const slider = document.getElementById('sliderPill');
            const btnTeknisi = document.getElementById('btnTeknisi');
            const btnAdmin = document.getElementById('btnAdmin');
            const selectedRoleInput = document.getElementById('selectedRole');

            selectedRoleInput.value = role;

            if (role === 'admin') {
                slider.style.transform = 'translateX(100%)';
                btnAdmin.classList.remove('text-slate-500');
                btnAdmin.classList.add('text-white');
                btnTeknisi.classList.remove('text-white');
                btnTeknisi.classList.add('text-slate-500');
            } else {
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

        // Auto-hide notifikasi error setelah 6 detik
        const errorNotif = document.getElementById('notifError');
        if (errorNotif) {
            setTimeout(() => {
                errorNotif.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
                errorNotif.style.opacity = '0';
                errorNotif.style.transform = 'translateY(-10px)';
                setTimeout(() => errorNotif.remove(), 400);
            }, 6000);
        }
    </script>
</body>
</html>