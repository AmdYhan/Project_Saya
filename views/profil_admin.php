<?php
session_start();
include "../config/koneksi.php";

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}
$role = strtolower($_SESSION['role'] ?? '');
if (!in_array($role, ['admin', 'pimpinan', 'koordinator', 'engginer'])) {
    header("Location: beranda.php");
    exit();
}

$username = $_SESSION['username'];
$query_user = mysqli_query($conn, "SELECT * FROM users WHERE username='$username'");
$user = mysqli_fetch_assoc($query_user);
$current_page = 'profil';

$q_total_laporan = mysqli_query($conn, "SELECT COUNT(*) as total FROM laporan_patroli WHERE username='$username'");
$total_kontribusi = mysqli_fetch_assoc($q_total_laporan)['total'] ?? 0;

$q_total_verifikasi = mysqli_query($conn, "SELECT COUNT(*) as total FROM laporan_patroli WHERE status_laporan = 'Disetujui'");
$total_disetujui = mysqli_fetch_assoc($q_total_verifikasi)['total'] ?? 0;

$q_total_user = mysqli_query($conn, "SELECT COUNT(*) as total FROM users");
$total_pengguna = mysqli_fetch_assoc($q_total_user)['total'] ?? 0;

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
                $pesan = "Password admin berhasil diperbarui!";
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
    <title>Profil & Keamanan Admin - Lintasarta Samarinda</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f1f5f9; }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-main { animation: fadeIn 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
    </style>
</head>
<body class="text-[#0d1c30] flex h-screen overflow-hidden">

    <!-- SIDEBAR DESKTOP -->
    <aside class="w-64 bg-[#0b192c] text-white flex flex-col justify-between hidden md:flex border-r border-slate-800 shadow-xl z-20">
        <div>
            <div class="p-6 border-b border-slate-800/80 flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-[#0052cc] flex items-center justify-center text-white font-extrabold shadow-md">
                    <span class="material-symbols-outlined">hub</span>
                </div>
                <div>
                    <h1 class="font-extrabold text-sm tracking-wide text-white">SAMARINDA OPS</h1>
                    <p class="text-[10px] text-blue-300 font-medium">NOC Fiber Monitoring</p>
                </div>
            </div>

            <nav class="p-4 space-y-1.5 text-xs font-semibold">
                <a href="dashboard_admin.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-300 hover:bg-slate-800/60 hover:text-white transition">
                    <span class="material-symbols-outlined text-[20px]">grid_view</span>
                    Beranda Utama
                </a>
                <a href="pemantauan_admin.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-300 hover:bg-slate-800/60 hover:text-white transition">
                    <span class="material-symbols-outlined text-[20px]">map</span>
                    Peta GIS Jalur
                </a>
                <a href="laporan_admin.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-300 hover:bg-slate-800/60 hover:text-white transition">
                    <span class="material-symbols-outlined text-[20px]">assignment</span>
                    Audit & Verifikasi Tiket
                </a>
                <a href="manajemen_user.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-300 hover:bg-slate-800/60 hover:text-white transition">
                    <span class="material-symbols-outlined text-[20px]">group</span>
                    Manajemen Pengguna
                </a>
                <a href="profil_admin.php" class="flex items-center gap-3 px-4 py-3 rounded-xl bg-[#0052cc] text-white shadow-md">
                    <span class="material-symbols-outlined text-[20px]" style="font-variation-settings: 'FILL' 1;">person</span>
                    Profil & Keamanan
                </a>
            </nav>
        </div>

        <div class="p-4 border-t border-slate-800/80 bg-slate-900/50">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2.5 overflow-hidden">
                    <div class="w-9 h-9 rounded-full bg-blue-500/20 text-blue-400 flex items-center justify-center font-bold border border-blue-500/30">
                        <span class="material-symbols-outlined text-[18px]">admin_panel_settings</span>
                    </div>
                    <div class="truncate">
                        <p class="text-xs font-bold text-white truncate"><?php echo htmlspecialchars($username); ?></p>
                        <p class="text-[10px] text-slate-400 capitalize"><?php echo htmlspecialchars($role); ?></p>
                    </div>
                </div>
                <a href="../process/proses_logout.php" onclick="return confirm('Keluar dari sistem?')" class="text-slate-400 hover:text-rose-400 transition p-1.5" title="Logout">
                    <span class="material-symbols-outlined text-[20px]">logout</span>
                </a>
            </div>
        </div>
    </aside>

    <!-- AREA KONTEN UTAMA -->
    <div class="flex-1 flex flex-col h-screen overflow-hidden">
        
        <!-- TOP HEADER -->
        <header class="bg-white border-b border-slate-200 px-6 py-4 flex justify-between items-center shadow-xs z-10">
            <div>
                <h2 class="font-extrabold text-base text-[#0d1c30]">Profil & Keamanan Akun Admin</h2>
                <p class="text-xs text-slate-400">Pusat Kendali NOC Lintasarta Samarinda</p>
            </div>
            
            <a href="../process/proses_logout.php" onclick="return confirm('Apakah Anda yakin ingin keluar dari sistem admin?')" class="bg-rose-50 hover:bg-rose-100 text-rose-600 font-bold text-xs px-4 py-2.5 rounded-xl transition flex items-center gap-1.5 shadow-2xs cursor-pointer">
                <span class="material-symbols-outlined text-[18px]">logout</span> Keluar Sistem
            </a>
        </header>

        <!-- KONTEN FULL-WIDTH SCROLLABLE -->
        <main class="flex-1 overflow-y-auto p-6 space-y-6 animate-main w-full">
            
            <?php if (!empty($pesan)): ?>
                <div class="p-4 rounded-2xl text-xs font-bold shadow-sm <?php echo ($tipe_pesan == 'success') ? 'bg-emerald-100 text-emerald-800 border border-emerald-300' : 'bg-rose-100 text-rose-800 border border-rose-300'; ?>">
                    <?php echo $pesan; ?>
                </div>
            <?php endif; ?>

            <!-- BANNER HEADER PROFIL DENGAN ILUSTRASI 2D TEKNISI -->
            <div class="bg-white rounded-3xl shadow-sm border border-slate-200/80 overflow-hidden">
                <div class="h-44 bg-gradient-to-r from-[#0b192c] via-[#0052cc] to-blue-500 relative flex items-center justify-end px-10 overflow-hidden">
                    <div class="absolute inset-0 opacity-15" style="background-image: radial-gradient(#ffffff 1px, transparent 1px); background-size: 16px 16px;"></div>
                    
                    <!-- ILUSTRASI 2D TEKNISI / FIBER OPTIC DI KANAN BANNER -->
                    <div class="hidden md:flex items-center gap-4 relative z-10 opacity-90 translate-y-2">
                        <div class="text-right text-white">
                            <p class="text-xs font-extrabold tracking-wide uppercase text-blue-200">NOC Fiber Monitoring</p>
                            <p class="text-[10px] text-slate-200">System Patroli & GIS OSP Aktif</p>
                        </div>
                        <div class="w-20 h-20 bg-white/10 backdrop-blur-md rounded-2xl border border-white/20 flex items-center justify-center text-white shadow-xl">
                            <span class="material-symbols-outlined text-[44px]">engineering</span>
                        </div>
                    </div>
                </div>

                <!-- Konten Profil di Bawah Banner -->
                <div class="px-8 pb-8 pt-4 flex flex-col md:flex-row md:items-end justify-between gap-6 relative">
                    <div class="flex flex-col sm:flex-row sm:items-center gap-5">
                        <div class="w-28 h-28 bg-white rounded-3xl p-1.5 shadow-xl border border-slate-100 -mt-20 relative z-10 shrink-0">
                            <div class="w-full h-full bg-gradient-to-tr from-blue-50 to-blue-100 text-[#0052cc] rounded-2xl flex items-center justify-center">
                                <span class="material-symbols-outlined text-[54px]">admin_panel_settings</span>
                            </div>
                        </div>

                        <div class="space-y-1.5 pt-2 sm:pt-0">
                            <div class="flex items-center gap-3 flex-wrap">
                                <h3 class="font-extrabold text-2xl text-[#0d1c30]"><?php echo htmlspecialchars(strtoupper($user['username'] ?? 'ADMIN')); ?></h3>
                                <span class="px-3 py-1 bg-indigo-50 text-indigo-700 text-[11px] font-extrabold rounded-full border border-indigo-200 uppercase tracking-wider">
                                    <?php echo htmlspecialchars($user['role'] ?? 'Admin'); ?>
                                </span>
                            </div>
                            <p class="text-xs text-slate-500 font-medium">NIP Pegawai: <strong class="text-slate-800"><?php echo htmlspecialchars($user['nip'] ?? '-'); ?></strong> &bull; Regional Kalimantan Timur (Samarinda Hub)</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 shrink-0">
                        <button onclick="togglePasswordModal(true)" class="bg-[#0052cc] hover:bg-blue-700 text-white font-bold text-xs px-5 py-3 rounded-xl transition shadow-sm flex items-center gap-2 cursor-pointer">
                            <span class="material-symbols-outlined text-[18px]">lock_reset</span> Ubah Kata Sandi
                        </button>
                    </div>
                </div>
            </div>

            <!-- GRID DETAIL INFORMASI & STATISTIK (12 Kolom Full-Width) -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                
                <!-- KOLOM KIRI: INFORMASI AKUN & WILAYAH OPERASIONAL (7 Kolom) -->
                <div class="lg:col-span-7 bg-white rounded-3xl p-7 shadow-sm border border-slate-200/80 space-y-5">
                    <div class="border-b border-slate-100 pb-4">
                        <h4 class="font-extrabold text-base text-[#0d1c30]">Detail Identitas & Otoritas Jaringan</h4>
                        <p class="text-xs text-slate-400 mt-0.5">Konfigurasi hak akses pusat kendali patroli fiber optik</p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100 space-y-1">
                            <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Username Sistem</p>
                            <p class="text-sm font-extrabold text-[#0d1c30] flex items-center gap-2">
                                <span class="material-symbols-outlined text-[18px] text-[#0052cc]">badge</span>
                                <?php echo htmlspecialchars($user['username']); ?>
                            </p>
                        </div>

                        <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100 space-y-1">
                            <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Nomor Induk Pegawai (NIP)</p>
                            <p class="text-sm font-extrabold text-[#0d1c30] flex items-center gap-2">
                                <span class="material-symbols-outlined text-[18px] text-[#0052cc]">id_card</span>
                                <?php echo htmlspecialchars($user['nip'] ?? '-'); ?>
                            </p>
                        </div>

                        <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100 space-y-1">
                            <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Wilayah Kendali OSP</p>
                            <p class="text-sm font-extrabold text-[#0d1c30] flex items-center gap-2">
                                <span class="material-symbols-outlined text-[18px] text-[#0052cc]">domain</span>
                                Koridor Mahakam & Samarinda
                            </p>
                        </div>

                        <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100 space-y-1">
                            <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Status Sesi Akun</p>
                            <p class="text-sm font-extrabold text-emerald-600 flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                Terverifikasi & Aktif
                            </p>
                        </div>
                    </div>

                    <div class="p-4 rounded-2xl bg-blue-50/70 border border-blue-100 flex items-start gap-3">
                        <span class="material-symbols-outlined text-[#0052cc] text-[22px] mt-0.5">verified_user</span>
                        <div class="space-y-1">
                            <h5 class="text-xs font-extrabold text-[#0d1c30]">Keamanan Tingkat Administrator</h5>
                            <p class="text-xs text-slate-600 leading-relaxed">Akun ini memiliki wewenang penuh untuk memverifikasi laporan inspeksi lapangan, mengelola titik koordinat GIS, mencetak berita acara PDF/Excel, serta mengatur hak akses pengguna.</p>
                        </div>
                    </div>
                </div>

                <!-- KOLOM KANAN: RINGKASAN KINERJA & KEAMANAN (5 Kolom) -->
                <div class="lg:col-span-5 bg-white rounded-3xl p-7 shadow-sm border border-slate-200/80 flex flex-col justify-between space-y-5">
                    <div class="space-y-5">
                        <div class="border-b border-slate-100 pb-4">
                            <h4 class="font-extrabold text-base text-[#0d1c30]">Ringkasan Aktivitas & Sistem</h4>
                            <p class="text-xs text-slate-400 mt-0.5">Ikhtisar operasional pada pusat kendali saat ini</p>
                        </div>

                        <div class="space-y-3">
                            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100 flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-11 h-11 rounded-2xl bg-emerald-100 text-emerald-600 flex items-center justify-center">
                                        <span class="material-symbols-outlined text-[22px]">task_alt</span>
                                    </div>
                                    <div>
                                        <p class="text-xs font-extrabold text-[#0d1c30]">Total Laporan Disetujui</p>
                                        <p class="text-[11px] text-slate-400">Seluruh koridor terverifikasi</p>
                                    </div>
                                </div>
                                <span class="text-lg font-extrabold text-emerald-600"><?php echo $total_disetujui; ?></span>
                            </div>

                            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100 flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-11 h-11 rounded-2xl bg-blue-100 text-[#0052cc] flex items-center justify-center">
                                        <span class="material-symbols-outlined text-[22px]">group</span>
                                    </div>
                                    <div>
                                        <p class="text-xs font-extrabold text-[#0d1c30]">Personel Terdaftar</p>
                                        <p class="text-[11px] text-slate-400">Teknisi, Admin & Koordinator</p>
                                    </div>
                                </div>
                                <span class="text-lg font-extrabold text-[#0052cc]"><?php echo $total_pengguna; ?></span>
                            </div>

                            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100 flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-11 h-11 rounded-2xl bg-indigo-100 text-indigo-600 flex items-center justify-center">
                                        <span class="material-symbols-outlined text-[22px]">fingerprint</span>
                                    </div>
                                    <div>
                                        <p class="text-xs font-extrabold text-[#0d1c30]">ID Database Akun</p>
                                        <p class="text-[11px] text-slate-400">Kontribusi langsung: <?php echo $total_kontribusi; ?> tiket</p>
                                    </div>
                                </div>
                                <span class="text-lg font-extrabold text-indigo-600">#<?php echo $user['id']; ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="pt-4 border-t border-slate-100 flex items-center justify-between text-xs">
                        <span class="text-slate-400 font-medium">Terakhir diakses: Hari ini</span>
                        <button onclick="togglePasswordModal(true)" class="font-bold text-[#0052cc] hover:underline cursor-pointer">Perbarui Sandi &rarr;</button>
                    </div>
                </div>

            </div>

        </main>
    </div>

    <!-- MODAL GANTI PASSWORD -->
    <div id="passwordModal" class="fixed inset-0 bg-slate-950/50 backdrop-blur-xs z-50 flex items-center justify-center p-4 hidden">
        <div class="bg-white rounded-3xl p-6 w-full max-w-md space-y-4 shadow-2xl animate-main">
            <div class="flex items-center justify-between border-b pb-3">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-[#0052cc]">lock_reset</span>
                    <h3 class="font-extrabold text-sm text-[#0d1c30]">Ganti Password Admin</h3>
                </div>
                <button onclick="togglePasswordModal(false)" class="text-slate-400 hover:text-slate-600 cursor-pointer">
                    <span class="material-symbols-outlined text-[20px]">close</span>
                </button>
            </div>

            <form action="" method="POST" class="space-y-3.5">
                <div class="space-y-1">
                    <label class="text-[11px] font-bold text-slate-700">Password Lama <span class="text-rose-500">*</span></label>
                    <input type="password" name="password_lama" required placeholder="Masukkan password saat ini" 
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3 text-xs font-semibold focus:outline-none focus:border-[#0052cc]">
                </div>

                <div class="space-y-1">
                    <label class="text-[11px] font-bold text-slate-700">Password Baru <span class="text-rose-500">*</span></label>
                    <input type="password" name="password_baru" required placeholder="Minimal 6 karakter" 
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3 text-xs font-semibold focus:outline-none focus:border-[#0052cc]">
                </div>

                <div class="space-y-1">
                    <label class="text-[11px] font-bold text-slate-700">Konfirmasi Password Baru <span class="text-rose-500">*</span></label>
                    <input type="password" name="konfirmasi_password" required placeholder="Ulangi password baru" 
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3 text-xs font-semibold focus:outline-none focus:border-[#0052cc]">
                </div>

                <div class="pt-2 flex gap-2">
                    <button type="button" onclick="togglePasswordModal(false)" class="flex-1 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold rounded-xl text-xs transition cursor-pointer">
                        Batal
                    </button>
                    <button type="submit" name="ubah_password" class="flex-1 py-2.5 bg-[#0052cc] hover:bg-blue-700 text-white font-bold rounded-xl text-xs transition shadow-md cursor-pointer">
                        Simpan Password
                    </button>
                </div>
            </form>
        </div>
    </div>

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