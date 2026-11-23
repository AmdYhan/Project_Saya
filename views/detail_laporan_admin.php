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

$id_laporan = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Ambil data laporan berdasarkan ID
$query = mysqli_query($conn, "SELECT l.*, u.nip FROM laporan_patroli l LEFT JOIN users u ON l.username = u.username WHERE l.id = $id_laporan");
$data = mysqli_fetch_assoc($query);

if (!$data) {
    $_SESSION['admin_notif'] = [
        'tipe' => 'error',
        'pesan' => 'Laporan patroli tidak ditemukan di sistem.'
    ];
    header("Location: laporan_admin.php");
    exit();
}

// Proses Aksi Verifikasi (Setujui atau Minta Revisi)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aksi = $_POST['aksi'] ?? '';
    $catatan_disposisi = mysqli_real_escape_string($conn, trim($_POST['catatan_disposisi'] ?? ''));
    
    $admin_pengaju = $_SESSION['username'];
    $tambahan_catatan = ($catatan_disposisi !== '') ? " [Catatan Supervisor: $catatan_disposisi]" : "";
    
    if ($aksi === 'setujui') {
        $status_baru = 'Disetujui';
        mysqli_query($conn, "UPDATE laporan_patroli SET status_laporan = '$status_baru', catatan = CONCAT(catatan, '$tambahan_catatan') WHERE id = $id_laporan");
    } else {
        $status_baru = 'Perlu Revisi';
        mysqli_query($conn, "UPDATE laporan_patroli SET status_laporan = '$status_baru', catatan = CONCAT(catatan, '$tambahan_catatan'), admin_revisi = '$admin_pengaju' WHERE id = $id_laporan");
    }
    
    $_SESSION['admin_notif'] = [
        'tipe' => 'sukses',
        'pesan' => "Status laporan #PAT-2026-" . str_pad($id_laporan, 3, '0', STR_PAD_LEFT) . " berhasil diperbarui menjadi: $status_baru"
    ];
    header("Location: laporan_admin.php");
    exit();
}

// Pisahkan catatan teknisi dan catatan supervisor agar tampil rapi ke bawah
$catatan_raw = $data['catatan'] ?? '';
$catatan_utama = $catatan_raw;
$catatan_supervisor_list = [];

if (preg_match_all('/\[Catatan Supervisor:\s*(.*?)\]/s', $catatan_raw, $matches)) {
    $catatan_utama = trim(preg_replace('/\[Catatan Supervisor:\s*.*?\]/s', '', $catatan_raw));
    foreach ($matches[1] as $m) {
        $catatan_supervisor_list[] = trim($m);
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Verifikasi Tiket - Admin Samarinda OPS</title>
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
                    Peta Pemantauan
                </a>
                <a href="laporan_admin.php" class="flex items-center gap-3 px-4 py-3 rounded-xl bg-[#0052cc] text-white shadow-md">
                    <span class="material-symbols-outlined text-[20px]" style="font-variation-settings: 'FILL' 1;">assignment</span>
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

        <div class="p-4 border-t border-slate-800/80 bg-slate-900/50">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2.5 overflow-hidden">
                    <div class="w-9 h-9 rounded-full bg-blue-500/20 text-blue-400 flex items-center justify-center font-bold border border-blue-500/30">
                        <span class="material-symbols-outlined text-[18px]">admin_panel_settings</span>
                    </div>
                    <div class="truncate">
                        <p class="text-xs font-bold text-white truncate"><?php echo htmlspecialchars($_SESSION['username']); ?></p>
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
        
        <!-- HEADER TOP -->
        <header class="bg-white border-b border-slate-200 px-6 py-4 flex justify-between items-center shadow-xs z-10">
            <div class="flex items-center gap-3">
                <a href="laporan_admin.php" class="w-9 h-9 rounded-xl bg-slate-100 flex items-center justify-center text-slate-600 hover:bg-slate-200 transition">
                    <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                </a>
                <div>
                    <h2 class="font-extrabold text-base text-[#0d1c30]">Verifikasi Tiket Patroli #PAT-2026-<?php echo str_pad($data['id'], 3, '0', STR_PAD_LEFT); ?></h2>
                    <p class="text-xs text-slate-400">Inspeksi Kondisi Fisik Jalur & Koordinat</p>
                </div>
            </div>
            
            <a href="laporan_admin.php" class="text-xs font-bold text-[#0052cc] hover:underline flex items-center gap-1">
                Kembali ke Daftar Tiket &rarr;
            </a>
        </header>

        <!-- KONTEN SCROLLABLE -->
        <main class="flex-1 overflow-y-auto p-6 space-y-6 animate-main">
            
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                <!-- KOLOM KIRI: DETAIL LAPORAN & FOTO BUKTI (2 Kolom) -->
                <div class="lg:col-span-2 space-y-6">
                    
                    <!-- FOTO BUKTI DOKUMENTASI LAPANGAN -->
                    <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-200/80 space-y-4">
                        <h3 class="font-extrabold text-sm text-[#0d1c30]">Foto Bukti Dokumentasi Lapangan</h3>
                        <?php if (!empty($data['foto'])): ?>
                            <div class="rounded-2xl overflow-hidden border border-slate-100 bg-slate-900 max-h-96 flex items-center justify-center">
                                <img src="../assets/img/uploads/<?php echo htmlspecialchars($data['foto']); ?>" alt="Bukti Patroli" class="w-full h-auto object-cover max-h-96 hover:scale-105 transition duration-300">
                            </div>
                        <?php else: ?>
                            <div class="p-8 text-center text-slate-400 font-semibold text-xs bg-slate-50 rounded-2xl border border-dashed border-slate-200">
                                Tidak ada lampiran foto bukti pada laporan ini.
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- DATA TEKNIS & LOKASI -->
                    <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-200/80 space-y-5">
                        <h3 class="font-extrabold text-sm text-[#0d1c30]">Parameter Titik Jalur & Wilayah</h3>
                        
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100 space-y-1">
                                <p class="text-[11px] font-bold text-slate-400 uppercase">Lokasi Area / Koridor</p>
                                <p class="font-extrabold text-[#0d1c30] text-sm"><?php echo htmlspecialchars($data['lokasi_area']); ?></p>
                            </div>

                            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100 space-y-1">
                                <p class="text-[11px] font-bold text-slate-400 uppercase">Koordinat GNSS / GPS</p>
                                <p class="font-mono font-extrabold text-[#0052cc] text-sm"><?php echo $data['latitude']; ?>, <?php echo $data['longitude']; ?></p>
                                <p class="text-[10px] text-slate-400">Akurasi: ±<?php echo $data['akurasi_gps']; ?> Meter</p>
                            </div>

                            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100 space-y-1">
                                <p class="text-[11px] font-bold text-slate-400 uppercase">Kondisi Fisik Kabel/Tiang</p>
                                <p class="font-extrabold text-[#0d1c30] text-sm"><?php echo htmlspecialchars($data['kondisi_fisik']); ?></p>
                            </div>

                            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100 space-y-1">
                                <p class="text-[11px] font-bold text-slate-400 uppercase">Potensi Ancaman</p>
                                <p class="font-extrabold text-rose-600 text-sm"><?php echo htmlspecialchars($data['potensi_ancaman'] ?: '-'); ?></p>
                            </div>
                        </div>

                        <!-- Catatan Teknisi & Riwayat Catatan Supervisor -->
                        <div class="space-y-3 pt-2 border-t border-slate-100 text-xs">
                            <div>
                                <p class="font-extrabold text-slate-600 mb-1">Catatan Temuan Teknisi:</p>
                                <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-100 text-slate-700 leading-relaxed">
                                    <?php echo !empty($catatan_utama) ? nl2br(htmlspecialchars($catatan_utama)) : '<span class="italic text-slate-400">Tidak ada catatan tambahan teknisi.</span>'; ?>
                                </div>
                            </div>

                            <?php if (!empty($catatan_supervisor_list)): ?>
                            <div class="space-y-2">
                                <p class="font-extrabold text-indigo-700 uppercase tracking-wider text-[11px]">Catatan / Instruksi Supervisor Sebelumnya:</p>
                                <?php foreach ($catatan_supervisor_list as $cat_s): ?>
                                    <div class="p-3.5 bg-indigo-50/70 border border-indigo-200 text-indigo-950 rounded-2xl font-semibold leading-relaxed">
                                        <?php echo nl2br(htmlspecialchars($cat_s)); ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>
                        </div>

                    </div>

                </div>

                <!-- KOLOM KANAN: FORM VERIFIKASI DISPOSISI ADMIN (1 Kolom) -->
                <div class="space-y-6">
                    
                    <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-200/80 space-y-5">
                        <div class="border-b border-slate-100 pb-3">
                            <h3 class="font-extrabold text-sm text-[#0d1c30]">Aksi Disposisi Verifikasi</h3>
                            <p class="text-[11px] text-slate-400 mt-0.5">Tentukan persetujuan laporan hasil inspeksi</p>
                        </div>

                        <div class="space-y-2 text-xs">
                            <div class="flex justify-between py-1.5 border-b border-slate-100">
                                <span class="text-slate-400 font-semibold">Pelapor:</span>
                                <span class="font-extrabold text-[#0d1c30]"><?php echo htmlspecialchars($data['username']); ?> (NIP-<?php echo htmlspecialchars($data['nip'] ?? '-'); ?>)</span>
                            </div>
                            <div class="flex justify-between py-1.5 border-b border-slate-100">
                                <span class="text-slate-400 font-semibold">Waktu Input:</span>
                                <span class="font-bold text-slate-700"><?php echo date('d M Y, H:i', strtotime($data['tanggal'])); ?> WITA</span>
                            </div>
                            <div class="flex justify-between py-1.5">
                                <span class="text-slate-400 font-semibold">Status Saat Ini:</span>
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold border bg-amber-50 text-amber-700 border-amber-200">
                                    <?php echo htmlspecialchars($data['status_laporan']); ?>
                                </span>
                            </div>
                        </div>

                        <form action="" method="POST" class="space-y-4 pt-2 border-t border-slate-100">
                            <div class="space-y-1.5">
                                <label class="text-[11px] font-bold text-slate-700 block">Catatan Disposisi / Instruksi Admin:</label>
                                <textarea name="catatan_disposisi" rows="3" placeholder="Tuliskan catatan perbaikan jika meminta revisi, atau instruksi tindak lanjut..." class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-xs font-semibold focus:outline-none focus:border-[#0052cc]"></textarea>
                            </div>

                            <div class="grid grid-cols-2 gap-2.5 pt-2">
                                <button type="submit" name="aksi" value="revisi" class="py-3 px-3 bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-200 rounded-xl text-xs font-extrabold transition flex items-center justify-center gap-1.5 cursor-pointer">
                                    <span class="material-symbols-outlined text-[16px]">edit_note</span>
                                    Minta Revisi
                                </button>
                                
                                <button type="submit" name="aksi" value="setujui" class="py-3 px-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-extrabold transition flex items-center justify-center gap-1.5 shadow-md shadow-emerald-500/20 cursor-pointer">
                                    <span class="material-symbols-outlined text-[16px]">verified</span>
                                    Setujui Tiket
                                </button>
                            </div>
                        </form>
                    </div>

                </div>

            </div>

        </main>
    </div>

</body>
</html>