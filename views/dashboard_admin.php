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

$query_aktivitas = mysqli_query($conn, "SELECT l.*, u.nip FROM laporan_patroli l LEFT JOIN users u ON l.username = u.username ORDER BY l.tanggal DESC LIMIT 3");

$current_page = 'dashboard';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beranda Admin - GIS Patroli Fiber Samarinda</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f8fafc; }
        @keyframes slideUpFade {
            0% { opacity: 0; transform: translateY(24px) scale(0.97); }
            100% { opacity: 1; transform: translateY(0) scale(1); }
        }
        .animate-entry { opacity: 0; animation: slideUpFade 0.75s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
        .delay-100 { animation-delay: 0.1s; }
        .delay-200 { animation-delay: 0.2s; }
        .delay-300 { animation-delay: 0.3s; }
        .delay-400 { animation-delay: 0.4s; }
        .delay-500 { animation-delay: 0.5s; }
    </style>
</head>
<body class="text-[#0d1c30] pb-28">

    <header class="bg-white border-b border-slate-100 sticky top-0 z-30 px-4 py-3 shadow-sm">
        <div class="max-w-md mx-auto flex items-center justify-between">
            <div>
                <h1 class="font-extrabold text-sm text-[#0d1c30] leading-tight">SAMARINDA OPS</h1>
                <p class="text-[10px] text-slate-400 font-medium">Dashboard Admin</p>
            </div>
            <a href="profil_admin.php" class="w-10 h-10 rounded-full bg-blue-50/80 border border-blue-100 flex items-center justify-center text-[#0052cc] active:scale-95 transition shadow-sm">
                <span class="material-symbols-outlined text-[22px]">person</span>
            </a>
        </div>
    </header>

    <main class="max-w-md mx-auto p-4 space-y-4">
        
        <div class="animate-entry delay-100 bg-gradient-to-r from-[#0b192c] to-[#0052cc] text-white p-4 rounded-3xl shadow-md space-y-3">
            <div class="flex justify-between items-center">
                <span class="bg-white/20 backdrop-blur-md text-[10px] font-bold px-2.5 py-0.5 rounded-full">Sistem Aktif</span>
                <span class="text-[10px] text-blue-200 font-medium"><?php echo date('d M Y'); ?></span>
            </div>
            <div>
                <h2 class="font-extrabold text-base">Halo, <?php echo htmlspecialchars($username); ?>! 👋</h2>
                <p class="text-[11px] text-blue-100 mt-0.5">Pusat Kendali OSP Koridor Mahakam siap dipantau hari ini.</p>
            </div>
            <div class="pt-1 flex gap-2 flex-wrap">
                <a href="cetak_pdf.php" target="_blank" class="bg-white text-[#0052cc] hover:bg-blue-50 font-bold text-xs px-3.5 py-2 rounded-xl flex items-center gap-1.5 shadow-sm transition active:scale-95">
                    <span class="material-symbols-outlined text-[16px]">print</span> Cetak PDF
                </a>
                <a href="manajemen_user.php" class="bg-white/15 hover:bg-white/25 text-white border border-white/25 font-bold text-xs px-3.5 py-2 rounded-xl flex items-center gap-1.5 shadow-sm transition active:scale-95">
                    <span class="material-symbols-outlined text-[16px]">group</span> Kelola Akun
                </a>
            </div>
        </div>

        <div class="animate-entry delay-200 grid grid-cols-3 gap-2">
            <div class="bg-white p-3 rounded-2xl border border-slate-100 shadow-sm text-center transition hover:shadow-md">
                <div class="text-rose-600 font-extrabold text-base mb-0.5"><?php echo $q_kritis; ?></div>
                <p class="text-[10px] text-slate-400 font-bold uppercase">Kritis Rawan</p>
            </div>
            <div class="bg-white p-3 rounded-2xl border border-slate-100 shadow-sm text-center transition hover:shadow-md">
                <div class="text-amber-600 font-extrabold text-base mb-0.5"><?php echo $q_waspada; ?></div>
                <p class="text-[10px] text-slate-400 font-bold uppercase">Waspada Rimbun</p>
            </div>
            <div class="bg-white p-3 rounded-2xl border border-slate-100 shadow-sm text-center transition hover:shadow-md">
                <div class="text-emerald-600 font-extrabold text-base mb-0.5"><?php echo $q_normal; ?></div>
                <p class="text-[10px] text-slate-400 font-bold uppercase">Normal Selesai</p>
            </div>
        </div>

        <div class="animate-entry delay-300 grid grid-cols-3 gap-2.5">
            <a href="pemantauan_admin.php" class="bg-white p-3.5 rounded-2xl border border-slate-100 shadow-sm flex flex-col gap-2 hover:border-blue-200 transition active:scale-95">
                <div class="w-8 h-8 rounded-xl bg-blue-50 text-[#0052cc] flex items-center justify-center">
                    <span class="material-symbols-outlined text-[18px]">map</span>
                </div>
                <div>
                    <h4 class="font-bold text-xs text-[#0d1c30]">Peta GIS</h4>
                    <p class="text-[10px] text-slate-400 leading-tight mt-0.5">Pantau jalur</p>
                </div>
            </a>
            <a href="laporan_admin.php" class="bg-white p-3.5 rounded-2xl border border-slate-100 shadow-sm flex flex-col gap-2 hover:border-blue-200 transition active:scale-95">
                <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <span class="material-symbols-outlined text-[18px]">assignment</span>
                </div>
                <div>
                    <h4 class="font-bold text-xs text-[#0d1c30]">Audit Tiket</h4>
                    <p class="text-[10px] text-slate-400 leading-tight mt-0.5">Cek laporan</p>
                </div>
            </a>
            <a href="manajemen_user.php" class="bg-white p-3.5 rounded-2xl border border-slate-100 shadow-sm flex flex-col gap-2 hover:border-blue-200 transition active:scale-95 text-left">
                <div class="w-8 h-8 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                    <span class="material-symbols-outlined text-[18px]">group</span>
                </div>
                <div>
                    <h4 class="font-bold text-xs text-[#0d1c30]">Kelola User</h4>
                    <p class="text-[10px] text-slate-400 leading-tight mt-0.5">Edit/Hapus Akun</p>
                </div>
            </a>
        </div>

        <div class="animate-entry delay-400 bg-white rounded-3xl p-4 shadow-sm border border-slate-100 space-y-4">
            <div class="flex justify-between items-start">
                <div>
                    <h3 class="font-extrabold text-sm text-[#0d1c30]">Tren Temuan Mingguan</h3>
                    <p class="text-[11px] text-slate-400 font-medium">Total <?php echo array_sum($tren_data); ?> temuan terdeteksi pekan ini</p>
                </div>
                <span class="bg-slate-100 text-slate-600 text-[10px] font-bold px-2.5 py-1 rounded-xl">Sen - Min</span>
            </div>

            <div class="flex items-end justify-between h-36 pt-6 px-2 border-b border-slate-100 pb-2">
                <?php 
                $nama_hari = ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'];
                foreach($tren_data as $i => $jml): 
                    $h_px = ($max_val > 0) ? round(($jml / $max_val) * 85) : 8;
                    if ($h_px < 8) $h_px = 8; 
                    $is_today = ($i == $today_idx);
                ?>
                <div class="flex flex-col items-center gap-1.5 flex-1 h-full justify-end">
                    <span class="text-[10px] <?php echo $is_today ? 'font-extrabold text-[#0052cc]' : 'font-bold text-slate-600'; ?>"><?php echo $jml > 0 ? $jml : '-'; ?></span>
                    <div class="w-6 rounded-t-lg transition-all <?php echo $is_today ? 'bg-[#0052cc] shadow-md' : 'bg-blue-100 hover:bg-blue-200'; ?>" style="height: <?php echo $h_px; ?>px;"></div>
                    <span class="text-[10px] <?php echo $is_today ? 'font-bold text-[#0052cc]' : 'font-semibold text-slate-400'; ?>"><?php echo $nama_hari[$i]; ?></span>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="flex items-center gap-2 text-[11px] text-slate-600 font-medium">
                <span class="w-3 h-3 rounded bg-[#0052cc] inline-block"></span> Hari Ini: <?php echo $jumlah_hari_ini; ?> Temuan
            </div>
        </div>

        <div class="animate-entry delay-500 space-y-3">
            <div class="flex justify-between items-center px-1">
                <h3 class="font-extrabold text-sm text-[#0d1c30]">Aktivitas Laporan Terbaru</h3>
                <a href="laporan_admin.php" class="text-[11px] font-bold text-[#0052cc] hover:underline flex items-center gap-0.5">
                    Lihat Semua <span class="material-symbols-outlined text-[14px]">arrow_forward</span>
                </a>
            </div>

            <?php if (mysqli_num_rows($query_aktivitas) > 0): ?>
                <?php while ($row = mysqli_fetch_assoc($query_aktivitas)): ?>
                    <?php 
                        $potensi = $row['potensi_ancaman'] ?: 'KRITIS (Rawan Putus)';
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

                        $kondisi_fisik = $row['kondisi_fisik'] ?: 'Tegangan 48 Core Main Span';
                        $kondisi_lower = strtolower($kondisi_fisik);
                        if ($kondisi_lower == 'baik' || strpos($kondisi_lower, 'normal') !== false) {
                            $kondisi_class = "text-emerald-600 font-bold";
                        } else {
                            $kondisi_class = "text-rose-600 font-bold";
                        }
                    ?>
                    <div class="bg-white rounded-2xl p-4 shadow-sm border border-slate-100 space-y-3 relative hover:shadow-md transition duration-200">
                        
                        <div class="flex justify-between items-center pb-2 border-b border-slate-100">
                            <span class="text-xs font-extrabold text-slate-500 tracking-wide flex items-center gap-1">
                                <span class="material-symbols-outlined text-[15px] text-blue-600">confirmation_number</span>
                                <?php echo $nomor_tiket; ?>
                            </span>
                            <span class="text-[10px] font-extrabold px-3 py-1 rounded-full border shadow-2xs <?php echo $status_class; ?>">
                                <?php echo htmlspecialchars($status_laporan); ?>
                            </span>
                        </div>

                        <div class="space-y-1">
                            <div class="flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-[#0052cc]"></span>
                                <span class="text-xs font-extrabold text-[#0052cc]">
                                    <?php echo htmlspecialchars($potensi); ?>
                                </span>
                            </div>
                            <h3 class="font-extrabold text-sm text-[#0d1c30] leading-snug"><?php echo htmlspecialchars($row['lokasi_area']); ?></h3>
                            <p class="text-[11px] text-slate-500 flex items-center gap-1">
                                <span class="material-symbols-outlined text-[14px] text-rose-500 flex-shrink-0">location_on</span>
                                <span class="truncate"><?php echo htmlspecialchars($row['lokasi_area']); ?> (<?php echo $row['latitude']; ?>, <?php echo $row['longitude']; ?>)</span>
                            </p>
                        </div>

                        <div class="bg-slate-50/80 p-3 rounded-xl border border-slate-100 grid grid-cols-5 gap-3 items-center">
                            <?php 
                            $foto_path = "../assets/img/uploads/" . $row['foto'];
                            if (!empty($row['foto']) && file_exists($foto_path)) :
                            ?>
                            <div class="col-span-2 relative">
                                <img src="<?php echo $foto_path; ?>" class="w-full h-24 object-cover rounded-lg border border-slate-200 shadow-xs">
                                <span class="absolute bottom-1 left-1 bg-black/60 backdrop-blur-xs text-white text-[9px] px-1.5 py-0.5 rounded font-bold">Bukti</span>
                            </div>
                            <div class="col-span-3 space-y-1 text-[11px]">
                            <?php else: ?>
                            <div class="col-span-5 space-y-1 text-[11px]">
                            <?php endif; ?>
                                <p class="text-slate-500">Pelapor: <b class="text-slate-800"><?php echo htmlspecialchars($row['username']); ?> (NIP-<?php echo htmlspecialchars($row['nip'] ?? '44821'); ?>)</b></p>
                                <p class="text-slate-500">Waktu: <b class="text-slate-800"><?php echo date('d M, H:i', strtotime($row['tanggal'])); ?> WITA</b></p>
                                <p class="text-slate-500">Dampak FO: <span class="<?php echo $kondisi_class; ?>"><?php echo htmlspecialchars($kondisi_fisik); ?></span></p>
                            </div>
                        </div>

                        <a href="detail_laporan_admin.php?id=<?php echo $row['id']; ?>" class="w-full bg-[#0052cc]/10 hover:bg-[#0052cc]/20 text-[#0052cc] font-bold py-2.5 rounded-xl text-xs flex items-center justify-center gap-1.5 transition active:scale-[0.98]">
                            <span class="material-symbols-outlined text-[18px]">visibility</span> Detail Tiket & Verifikasi
                        </a>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="bg-white p-6 rounded-2xl border border-slate-100 text-center text-xs text-slate-400">
                    Belum ada aktivitas laporan terbaru.
                </div>
            <?php endif; ?>
        </div>

    </main>

    <nav class="fixed bottom-0 left-0 right-0 bg-white/95 backdrop-blur-md border-t border-slate-200 py-2.5 px-6 flex justify-around items-center z-30 max-w-md mx-auto shadow-lg">
        <a href="dashboard_admin.php" class="flex flex-col items-center text-[#0052cc] font-bold"><span class="material-symbols-outlined text-[22px]" style="font-variation-settings: 'FILL' 1;">grid_view</span><span class="text-[10px] mt-0.5">Beranda</span></a>
        <a href="pemantauan_admin.php" class="flex flex-col items-center text-slate-400 hover:text-slate-600"><span class="material-symbols-outlined text-[22px]">map</span><span class="text-[10px] mt-0.5">Peta Jalur</span></a>
        <a href="laporan_admin.php" class="flex flex-col items-center text-slate-400 hover:text-slate-600"><span class="material-symbols-outlined text-[22px]">assignment</span><span class="text-[10px] mt-0.5">Laporan</span></a>
        <a href="profil_admin.php" class="flex flex-col items-center text-slate-400 hover:text-slate-600"><span class="material-symbols-outlined text-[22px]">person</span><span class="text-[10px] mt-0.5">Profil</span></a>
    </nav>
</body>
</html>