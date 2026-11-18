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

$q_kritis = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM laporan_patroli WHERE potensi_ancaman LIKE '%Kritis%' OR kondisi_fisik LIKE '%Rusak%'"))['total'] ?? 0;
$q_waspada = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM laporan_patroli WHERE potensi_ancaman LIKE '%Pohon%' OR potensi_ancaman LIKE '%Rimbun%' OR potensi_ancaman LIKE '%Waspada%'"))['total'] ?? 0;
$q_normal = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM laporan_patroli WHERE status_laporan = 'Disetujui' OR kondisi_fisik LIKE '%Baik%'"))['total'] ?? 0;
$total_laporan = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM laporan_patroli"))['total'] ?? 0;

$tren_data = [0, 0, 0, 0, 0, 0, 0]; 
$query_tren = mysqli_query($conn, "SELECT WEEKDAY(tanggal) as hari_ke, COUNT(*) as jumlah FROM laporan_patroli WHERE YEARWEEK(tanggal, 1) = YEARWEEK(NOW(), 1) GROUP BY WEEKDAY(tanggal)");

if ($query_tren) {
    while($row = mysqli_fetch_assoc($query_tren)) {
        $idx = intval($row['hari_ke']);
        if($idx >= 0 && $idx <= 6) {
            $tren_data[$idx] = intval($row['jumlah']);
        }
    }
}

$max_val = max($tren_data);
if ($max_val == 0) $max_val = 1; 

$today_idx = (date('N') - 1); 
$jumlah_hari_ini = $tren_data[$today_idx] ?? 0;

$query_aktivitas = mysqli_query($conn, "SELECT l.*, u.nip FROM laporan_patroli l LEFT JOIN users u ON l.username = u.username ORDER BY l.tanggal DESC LIMIT 5");

$current_page = 'dashboard';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - GIS Patroli Fiber Samarinda</title>
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
            <!-- Logo & Brand -->
            <div class="p-6 border-b border-slate-800/80 flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-[#0052cc] flex items-center justify-center text-white font-extrabold shadow-md">
                    <span class="material-symbols-outlined">hub</span>
                </div>
                <div>
                    <h1 class="font-extrabold text-sm tracking-wide text-white">SAMARINDA OPS</h1>
                    <p class="text-[10px] text-blue-300 font-medium">NOC Fiber Monitoring</p>
                </div>
            </div>

            <!-- Menu Navigasi Sidebar -->
            <nav class="p-4 space-y-1.5 text-xs font-semibold">
                <a href="dashboard_admin.php" class="flex items-center gap-3 px-4 py-3 rounded-xl bg-[#0052cc] text-white shadow-md">
                    <span class="material-symbols-outlined text-[20px]" style="font-variation-settings: 'FILL' 1;">grid_view</span>
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
                <a href="profil_admin.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-300 hover:bg-slate-800/60 hover:text-white transition">
                    <span class="material-symbols-outlined text-[20px]">person</span>
                    Profil & Keamanan
                </a>
            </nav>
        </div>

        <!-- Footer Sidebar (Info Login) -->
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
        
        <!-- TOP HEADER DESKTOP -->
        <header class="bg-white border-b border-slate-200 px-6 py-4 flex justify-between items-center shadow-xs z-10">
            <div>
                <h2 class="font-extrabold text-base text-[#0d1c30]">Dashboard Pusat Kendali</h2>
                <p class="text-xs text-slate-400">Koridor Serat Optik Mahakam & Sekitarnya</p>
            </div>
            
            <div class="flex items-center gap-3">
                <span class="text-xs text-slate-500 font-medium bg-slate-100 px-3 py-1.5 rounded-xl border border-slate-200">
                    📅 <?php echo date('d M Y'); ?>
                </span>
                <a href="cetak_pdf.php" target="_blank" class="bg-[#0052cc] hover:bg-blue-700 text-white font-bold text-xs px-4 py-2 rounded-xl flex items-center gap-1.5 shadow-sm transition">
                    <span class="material-symbols-outlined text-[16px]">print</span> Cetak Laporan PDF
                </a>
            </div>
        </header>

        <!-- KONTEN SCROLLABLE -->
        <main class="flex-1 overflow-y-auto p-6 space-y-6 animate-main">
            
            <!-- BANNER SAMBUTAN -->
            <div class="bg-gradient-to-r from-[#0b192c] via-[#003c99] to-[#0052cc] text-white p-6 rounded-3xl shadow-lg flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div class="space-y-1">
                    <span class="bg-white/20 backdrop-blur-md text-[10px] font-extrabold px-3 py-1 rounded-full uppercase tracking-wider">Sistem Aktif & Terpantau</span>
                    <h3 class="font-extrabold text-xl pt-1">Selamat Datang, <?php echo htmlspecialchars($username); ?>! 👋</h3>
                    <p class="text-xs text-blue-100 max-w-xl">Seluruh jalur patroli OSP Samarinda dalam kondisi terkendali. Pantau metrik laporan kritis dan tren mingguan secara real-time melalui panel di bawah ini.</p>
                </div>
                <div class="flex gap-2">
                    <a href="pemantauan_admin.php" class="bg-white text-[#0052cc] hover:bg-blue-50 font-bold text-xs px-4 py-2.5 rounded-2xl shadow transition flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-[16px]">map</span> Buka Peta GIS
                    </a>
                </div>
            </div>

            <!-- GRID STATISTIK UTAMA (KRITIS, WASPADA, AMAN) -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-sm flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Kritis / Bahaya</p>
                        <h4 class="text-2xl font-extrabold text-rose-600 mt-1"><?php echo $q_kritis; ?> <span class="text-xs font-normal text-slate-400">Tiket</span></h4>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center">
                        <span class="material-symbols-outlined text-[24px]">warning</span>
                    </div>
                </div>

                <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-sm flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Waspada / Siaga</p>
                        <h4 class="text-2xl font-extrabold text-amber-600 mt-1"><?php echo $q_waspada; ?> <span class="text-xs font-normal text-slate-400">Tiket</span></h4>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center">
                        <span class="material-symbols-outlined text-[24px]">report_problem</span>
                    </div>
                </div>

                <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-sm flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Aman / Normal</p>
                        <h4 class="text-2xl font-extrabold text-emerald-600 mt-1"><?php echo $q_normal; ?> <span class="text-xs font-normal text-slate-400">Tiket</span></h4>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                        <span class="material-symbols-outlined text-[24px]">check_circle</span>
                    </div>
                </div>
            </div>

            <!-- SPLIT SECTION: GRAFIK TREN & AKTIVITAS TERBARU -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                <!-- GRAFIK TREN MINGGUAN (2 Kolom) -->
                <div class="lg:col-span-2 bg-white rounded-3xl p-6 shadow-sm border border-slate-200/80 space-y-4 flex flex-col justify-between">
                    <div class="flex justify-between items-center">
                        <div>
                            <h3 class="font-extrabold text-sm text-[#0d1c30]">Tren Temuan Mingguan</h3>
                            <p class="text-xs text-slate-400">Total <?php echo array_sum($tren_data); ?> temuan terdeteksi pekan ini</p>
                        </div>
                        <span class="bg-slate-100 text-slate-600 text-xs font-bold px-3 py-1 rounded-xl">Senin - Minggu</span>
                    </div>

                    <div class="flex items-end justify-between h-48 pt-8 px-4 border-b border-slate-100 pb-2">
                        <?php 
                        $nama_hari = ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'];
                        foreach($tren_data as $i => $jml): 
                            $h_px = ($max_val > 0) ? round(($jml / $max_val) * 110) : 10;
                            if ($h_px < 10) $h_px = 10; 
                            $is_today = ($i == $today_idx);
                        ?>
                        <div class="flex flex-col items-center gap-2 flex-1 h-full justify-end">
                            <span class="text-xs <?php echo $is_today ? 'font-extrabold text-[#0052cc]' : 'font-bold text-slate-500'; ?>"><?php echo $jml > 0 ? $jml : '-'; ?></span>
                            <div class="w-8 rounded-t-xl transition-all <?php echo $is_today ? 'bg-[#0052cc] shadow-md' : 'bg-blue-100 hover:bg-blue-200'; ?>" style="height: <?php echo $h_px; ?>px;"></div>
                            <span class="text-xs <?php echo $is_today ? 'font-bold text-[#0052cc]' : 'font-semibold text-slate-400'; ?>"><?php echo $nama_hari[$i]; ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="flex items-center gap-2 text-xs text-slate-600 font-medium">
                        <span class="w-3 h-3 rounded bg-[#0052cc] inline-block"></span> Hari Ini: <b><?php echo $jumlah_hari_ini; ?> Temuan</b> tercatat.
                    </div>
                </div>

                <!-- QUICK ACTIONS / MENU PINTASAN (1 Kolom) -->
                <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-200/80 space-y-4">
                    <h3 class="font-extrabold text-sm text-[#0d1c30]">Akses Cepat Admin</h3>
                    <div class="space-y-2.5">
                        <a href="pemantauan_admin.php" class="flex items-center gap-3 p-3.5 rounded-2xl bg-slate-50 hover:bg-blue-50/60 border border-slate-100 transition group">
                            <div class="w-10 h-10 rounded-xl bg-blue-100 text-[#0052cc] flex items-center justify-center font-bold">
                                <span class="material-symbols-outlined text-[20px]">map</span>
                            </div>
                            <div>
                                <h4 class="font-bold text-xs text-[#0d1c30] group-hover:text-[#0052cc] transition">Peta GIS Interaktif</h4>
                                <p class="text-[11px] text-slate-400">Pantau titik koordinat lapangan</p>
                            </div>
                        </a>

                        <a href="laporan_admin.php" class="flex items-center gap-3 p-3.5 rounded-2xl bg-slate-50 hover:bg-emerald-50/60 border border-slate-100 transition group">
                            <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center font-bold">
                                <span class="material-symbols-outlined text-[20px]">assignment</span>
                            </div>
                            <div>
                                <h4 class="font-bold text-xs text-[#0d1c30] group-hover:text-emerald-600 transition">Audit & Verifikasi Tiket</h4>
                                <p class="text-[11px] text-slate-400">Setujui atau revisi laporan teknisi</p>
                            </div>
                        </a>

                        <a href="manajemen_user.php" class="flex items-center gap-3 p-3.5 rounded-2xl bg-slate-50 hover:bg-indigo-50/60 border border-slate-100 transition group">
                            <div class="w-10 h-10 rounded-xl bg-indigo-100 text-indigo-600 flex items-center justify-center font-bold">
                                <span class="material-symbols-outlined text-[20px]">group</span>
                            </div>
                            <div>
                                <h4 class="font-bold text-xs text-[#0d1c30] group-hover:text-indigo-600 transition">Manajemen Pengguna</h4>
                                <p class="text-[11px] text-slate-400">Kelola akun teknisi dan admin</p>
                            </div>
                        </a>
                    </div>
                </div>

            </div>

            <!-- TABEL AKTIVITAS LAPORAN TERBARU -->
            <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-200/80 space-y-4">
                <div class="flex justify-between items-center">
                    <div>
                        <h3 class="font-extrabold text-sm text-[#0d1c30]">Aktivitas Laporan Terbaru</h3>
                        <p class="text-xs text-slate-400">Daftar laporan patroli yang baru masuk dari teknisi lapangan</p>
                    </div>
                    <a href="laporan_admin.php" class="text-xs font-bold text-[#0052cc] hover:underline flex items-center gap-1">
                        Lihat Semua Laporan <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
                    </a>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="border-b border-slate-200 text-slate-400 font-extrabold uppercase text-[10px]">
                                <th class="py-3 px-4">No. Tiket</th>
                                <th class="py-3 px-4">Lokasi Area</th>
                                <th class="py-3 px-4">Potensi Ancaman</th>
                                <th class="py-3 px-4">Pelapor</th>
                                <th class="py-3 px-4">Waktu</th>
                                <th class="py-3 px-4">Status</th>
                                <th class="py-3 px-4 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php if (mysqli_num_rows($query_aktivitas) > 0): ?>
                                <?php while ($row = mysqli_fetch_assoc($query_aktivitas)): ?>
                                    <?php 
                                        $nomor_tiket = '#PAT-2026-' . str_pad($row['id'], 3, '0', STR_PAD_LEFT);
                                        $status_laporan = trim($row['status_laporan'] ?? 'Menunggu Verifikasi');
                                        
                                        $status_lower = strtolower($status_laporan);
                                        if ($status_lower == 'disetujui' || $status_lower == 'approved' || $status_lower == 'selesai') {
                                            $status_class = "bg-emerald-100 text-emerald-800 border-emerald-300";
                                        } elseif ($status_lower == 'revisi' || $status_lower == 'perlu revisi' || $status_lower == 'ditolak' || $status_lower == 'rejected') {
                                            $status_class = "bg-indigo-100 text-indigo-800 border-indigo-300"; 
                                        } else {
                                            $status_class = "bg-amber-100 text-amber-800 border-amber-300"; 
                                        }
                                    ?>
                                    <tr class="hover:bg-slate-50 transition">
                                        <td class="py-3.5 px-4 font-extrabold text-slate-700"><?php echo $nomor_tiket; ?></td>
                                        <td class="py-3.5 px-4 font-bold text-[#0d1c30]"><?php echo htmlspecialchars($row['lokasi_area']); ?></td>
                                        <td class="py-3.5 px-4 text-slate-600 font-semibold"><?php echo htmlspecialchars($row['potensi_ancaman'] ?: 'Kritis'); ?></td>
                                        <td class="py-3.5 px-4 text-slate-600"><?php echo htmlspecialchars($row['username']); ?></td>
                                        <td class="py-3.5 px-4 text-slate-500"><?php echo date('d M, H:i', strtotime($row['tanggal'])); ?></td>
                                        <td class="py-3.5 px-4">
                                            <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold border <?php echo $status_class; ?>">
                                                <?php echo htmlspecialchars($status_laporan); ?>
                                            </span>
                                        </td>
                                        <td class="py-3.5 px-4 text-center">
                                            <a href="detail_laporan_admin.php?id=<?php echo $row['id']; ?>" class="inline-flex items-center gap-1 bg-blue-50 hover:bg-blue-100 text-[#0052cc] font-bold px-3 py-1.5 rounded-xl transition">
                                                <span class="material-symbols-outlined text-[15px]">visibility</span> Detail
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="py-6 text-center text-slate-400 font-medium">Belum ada aktivitas laporan terbaru.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </main>
    </div>

</body>
</html>