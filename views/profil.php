<?php
session_start();
include "../config/koneksi.php";

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Redirect jika user memiliki hak akses admin/pimpinan/koordinator ke profil admin
$role = strtolower(trim($_SESSION['role'] ?? ''));
if (in_array($role, ['admin', 'pimpinan', 'koordinator', 'engginer'], true)) {
    header("Location: profil_admin.php");
    exit();
}

$username = $_SESSION['username'];
$query_user = mysqli_query($conn, "SELECT * FROM users WHERE username='$username'");
$user = mysqli_fetch_assoc($query_user);
$current_page = 'profil';

// Pesan notifikasi ubah password
$pesan = "";
$tipe_pesan = "";

if (isset($_POST['ubah_password'])) {
    $password_lama = $_POST['password_lama'];
    $password_baru = $_POST['password_baru'];
    $konfirmasi_password = $_POST['konfirmasi_password'];

    if ($password_lama === $user['password'] || md5($password_lama) === $user['password'] || password_verify($password_lama, $user['password'])) {
        if ($password_baru === $konfirmasi_password) {
            $password_simpan = $password_baru; 
            
            $update = mysqli_query($conn, "UPDATE users SET password='$password_simpan' WHERE username='$username'");
            if ($update) {
                $pesan = "Password berhasil diperbarui!";
                $tipe_pesan = "success";
            } else {
                $pesan = "Gagal memperbarui database!";
                $tipe_pesan = "error";
            }
        } else {
            $pesan = "Konfirmasi password baru tidak cocok!";
            $tipe_pesan = "error";
        }
    } else {
        $pesan = "Password lama yang Anda masukkan salah!";
        $tipe_pesan = "error";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Akun - Lintasarta Samarinda</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f8fafc; }

        @keyframes slideUpFade {
            0% { opacity: 0; transform: translateY(18px) scale(0.98); }
            100% { opacity: 1; transform: translateY(0) scale(1); }
        }

        .animate-entry {
            opacity: 0;
            animation: slideUpFade 0.55s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        .delay-100 { animation-delay: 0.08s; }
        .delay-200 { animation-delay: 0.16s; }
        .delay-300 { animation-delay: 0.24s; }
    </style>
</head>
<body class="text-[#0d1c30] pb-28">

    <!-- HEADER / LATAR ATAS GRADASI (DISAMAKAN DENGAN ADMIN) -->
    <div class="bg-gradient-to-br from-[#0b192c] via-[#003c99] to-[#0052cc] text-white pb-20 pt-4 px-4 relative">
        <div class="max-w-md mx-auto flex items-center justify-between">
            <a href="beranda.php" class="w-10 h-10 rounded-full bg-white/15 backdrop-blur-md border border-white/20 flex items-center justify-center text-white active:scale-95 transition">
                <span class="material-symbols-outlined text-[22px]">arrow_back</span>
            </a>
            <h1 class="text-xs font-extrabold tracking-wider uppercase text-blue-100">Pengaturan Profil Teknisi</h1>
            <div class="w-10"></div>
        </div>
    </div>

    <!-- KONTEN UTAMA -->
    <main class="max-w-md mx-auto px-4 -mt-12 space-y-4">
        
        <?php if (!empty($pesan)): ?>
            <div class="p-4 rounded-2xl text-xs font-bold text-center shadow-md <?php echo ($tipe_pesan == 'success') ? 'bg-emerald-100 text-emerald-800 border border-emerald-300' : 'bg-rose-100 text-rose-800 border border-rose-300'; ?>">
                <?php echo $pesan; ?>
            </div>
        <?php endif; ?>

        <!-- KARTU PROFIL UTAMA (FLOATING CARD) -->
        <div class="animate-entry delay-100 bg-white rounded-3xl p-6 shadow-xl shadow-slate-200/60 border border-slate-100 text-center relative space-y-3">
            <div class="absolute -top-12 left-1/2 -translate-x-1/2">
                <div class="w-24 h-24 bg-gradient-to-tr from-blue-50 to-blue-100 text-[#0052cc] rounded-full flex items-center justify-center border-4 border-white shadow-md">
                    <span class="material-symbols-outlined text-[48px]">engineering</span>
                </div>
            </div>

            <div class="pt-10">
                <h2 class="font-extrabold text-xl text-[#0d1c30] tracking-tight"><?php echo htmlspecialchars(ucfirst($user['username'] ?? 'Teknisi')); ?></h2>
                <p class="text-xs text-slate-400 font-semibold mt-0.5">NIP: <strong class="text-slate-700"><?php echo htmlspecialchars($user['nip'] ?? '101'); ?></strong> • OSP Samarinda</p>
            </div>

            <div class="pt-1">
                <span class="inline-block px-3 py-1 bg-blue-50 text-[#0052cc] text-xs font-extrabold rounded-full border border-blue-100">
                    ROLE: <?php echo strtoupper(htmlspecialchars($user['role'] ?? 'Teknisi')); ?>
                </span>
            </div>
        </div>

        <!-- DAFTAR INFORMASI & MENU PENGATURAN -->
        <div class="animate-entry delay-200 bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden divide-y divide-slate-100">
            
            <div class="p-4 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-blue-50 text-[#0052cc] flex items-center justify-center border border-blue-100">
                        <span class="material-symbols-outlined text-[20px]">badge</span>
                    </div>
                    <div>
                        <p class="text-xs font-extrabold text-[#0d1c30]">Username Akun</p>
                        <p class="text-[11px] text-slate-400"><?php echo htmlspecialchars($user['username']); ?></p>
                    </div>
                </div>
            </div>

            <div class="p-4 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-blue-50 text-[#0052cc] flex items-center justify-center border border-blue-100">
                        <span class="material-symbols-outlined text-[20px]">domain</span>
                    </div>
                    <div>
                        <p class="text-xs font-extrabold text-[#0d1c30]">Wilayah Operasional</p>
                        <p class="text-[11px] text-slate-400">Samarinda OSP Koridor</p>
                    </div>
                </div>
            </div>

            <!-- TOMBOL MENU UBAH PASSWORD -->
            <button onclick="togglePasswordModal(true)" class="w-full p-4 flex items-center justify-between hover:bg-slate-50 transition text-left">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center border border-amber-100">
                        <span class="material-symbols-outlined text-[20px]">lock_reset</span>
                    </div>
                    <div>
                        <p class="text-xs font-extrabold text-[#0d1c30]">Ubah Password Akun</p>
                        <p class="text-[11px] text-slate-400">Perbarui kata sandi berkala</p>
                    </div>
                </div>
                <span class="material-symbols-outlined text-slate-300 text-[18px]">chevron_right</span>
            </button>

        </div>

        <!-- TOMBOL LOGOUT -->
        <a href="../process/proses_logout.php" onclick="return confirm('Apakah Anda yakin ingin keluar dari aplikasi?')" class="animate-entry delay-300 w-full py-3.5 bg-rose-50 hover:bg-rose-100 text-rose-600 border border-rose-200 font-extrabold text-xs rounded-2xl transition flex items-center justify-center gap-1.5 shadow-xs active:scale-[0.98]">
            <span class="material-symbols-outlined text-[18px]">logout</span>
            Keluar dari Sistem (Logout)
        </a>

    </main>

    <!-- MODAL POPUP GANTI PASSWORD -->
    <div id="passwordModal" class="fixed inset-0 bg-slate-950/50 backdrop-blur-xs z-50 flex items-center justify-center p-4 hidden">
        <div class="bg-white rounded-3xl p-6 w-full max-w-sm space-y-4 shadow-2xl animate-entry">
            <div class="flex items-center justify-between border-b pb-3">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-[#0052cc]">lock_reset</span>
                    <h3 class="font-extrabold text-sm text-[#0d1c30]">Ganti Password Baru</h3>
                </div>
                <button onclick="togglePasswordModal(false)" class="text-slate-400 hover:text-slate-600">
                    <span class="material-symbols-outlined text-[20px]">close</span>
                </button>
            </div>

            <form action="" method="POST" class="space-y-3.5">
                <div class="space-y-1">
                    <label class="text-[11px] font-bold text-slate-700">Password Lama <span class="text-rose-500">*</span></label>
                    <input type="password" name="password_lama" required placeholder="Masukkan password saat ini" 
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-xs font-semibold focus:outline-none focus:border-[#0052cc]">
                </div>

                <div class="space-y-1">
                    <label class="text-[11px] font-bold text-slate-700">Password Baru <span class="text-rose-500">*</span></label>
                    <input type="password" name="password_baru" required placeholder="Minimal 6 karakter" 
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-xs font-semibold focus:outline-none focus:border-[#0052cc]">
                </div>

                <div class="space-y-1">
                    <label class="text-[11px] font-bold text-slate-700">Konfirmasi Password Baru <span class="text-rose-500">*</span></label>
                    <input type="password" name="konfirmasi_password" required placeholder="Ulangi password baru" 
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-xs font-semibold focus:outline-none focus:border-[#0052cc]">
                </div>

                <div class="pt-2 flex gap-2">
                    <button type="button" onclick="togglePasswordModal(false)" class="flex-1 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold rounded-xl text-xs transition">
                        Batal
                    </button>
                    <button type="submit" name="ubah_password" class="flex-1 py-2.5 bg-[#0052cc] hover:bg-blue-700 text-white font-bold rounded-xl text-xs transition shadow-md">
                        Simpan Password
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- BOTTOM NAVBAR -->
    <nav class="fixed bottom-0 left-0 right-0 bg-white/95 backdrop-blur-md border-t border-slate-200/80 py-2.5 px-6 flex justify-around items-center z-30 max-w-md mx-auto shadow-lg">
        <a href="beranda.php" class="flex flex-col items-center <?php echo ($current_page == 'beranda') ? 'text-[#0052cc] font-bold' : 'text-slate-400'; ?>">
            <span class="material-symbols-outlined text-[22px]" <?php echo ($current_page == 'beranda') ? 'style="font-variation-settings: \'FILL\' 1;"' : ''; ?>>grid_view</span>
            <span class="text-[10px] mt-0.5">Beranda</span>
        </a>
        <a href="form_patroli.php" class="flex flex-col items-center <?php echo ($current_page == 'patroli') ? 'text-[#0052cc] font-bold' : 'text-slate-400'; ?>">
            <span class="material-symbols-outlined text-[22px]" <?php echo ($current_page == 'patroli') ? 'style="font-variation-settings: \'FILL\' 1;"' : ''; ?>>assignment</span>
            <span class="text-[10px] mt-0.5">Patroli</span>
        </a>
        <a href="riwayat.php" class="flex flex-col items-center <?php echo ($current_page == 'riwayat') ? 'text-[#0052cc] font-bold' : 'text-slate-400'; ?>">
            <span class="material-symbols-outlined text-[22px]" <?php echo ($current_page == 'riwayat') ? 'style="font-variation-settings: \'FILL\' 1;"' : ''; ?>>history</span>
            <span class="text-[10px] mt-0.5">Riwayat</span>
        </a>
        <a href="profil.php" class="flex flex-col items-center <?php echo ($current_page == 'profil') ? 'text-[#0052cc] font-bold' : 'text-slate-400'; ?>">
            <span class="material-symbols-outlined text-[22px]" <?php echo ($current_page == 'profil') ? 'style="font-variation-settings: \'FILL\' 1;"' : ''; ?>>person</span>
            <span class="text-[10px] mt-0.5">Profil</span>
        </a>
    </nav>

    <script>
        function togglePasswordModal(show) {
            const modal = document.getElementById('passwordModal');
            if (show) {
                modal.classList.remove('hidden');
            } else {
                modal.classList.add('hidden');
            }
        }
    </script>
</body>
</html>