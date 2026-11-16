<?php
session_start();
include "../config/koneksi.php";

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$id_laporan = $_GET['id'] ?? '';
$query = mysqli_query($conn, "SELECT laporan_patroli.*, users.username, users.nip, users.role FROM laporan_patroli JOIN users ON laporan_patroli.id_teknisi = users.id WHERE laporan_patroli.id='$id_laporan'");
$data = mysqli_fetch_assoc($query);

if (!$data) {
    header("Location: riwayat.php");
    exit();
}

$current_page = 'riwayat';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Laporan - Lintasarta</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f4f6f9; }
    </style>
</head>
<body class="text-[#0d1c30] pb-28">

    <!-- HEADER -->
    <header class="bg-white/95 backdrop-blur-md border-b border-slate-200/80 sticky top-0 z-30 px-4 py-4 shadow-xs">
        <div class="max-w-md mx-auto flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a href="riwayat.php" class="w-10 h-10 rounded-full bg-slate-100 border border-slate-200 flex items-center justify-center text-[#0d1c30] active:scale-95 transition">
                    <span class="material-symbols-outlined text-[22px]">arrow_back</span>
                </a>
                <div>
                    <h1 class="font-extrabold text-base tracking-wide text-[#0d1c30] leading-tight">DETAIL TIKET</h1>
                    <p class="text-xs text-slate-500 font-semibold tracking-wide">#PAT-2026-<?php echo str_pad($data['id'], 3, '0', STR_PAD_LEFT); ?></p>
                </div>
            </div>
            
            <a href="profil.php" class="w-10 h-10 rounded-full bg-blue-50 border border-blue-100 flex items-center justify-center text-[#0052cc] shadow-xs">
                <span class="material-symbols-outlined text-[22px]">person</span>
            </a>
        </div>
    </header>

    <!-- CONTENT -->
    <main class="max-w-md mx-auto p-4 space-y-4">
        
        <!-- KARTU INFORMASI ADMIN REVISI (MUNCUL JIKA ADA DATA ADMIN REVISI) -->
        <?php if (!empty($data['admin_revisi'])): ?>
        <div class="bg-amber-50 rounded-3xl p-4 shadow-sm border border-amber-200 flex items-start gap-3">
            <div class="w-9 h-9 rounded-2xl bg-amber-100 text-amber-700 flex items-center justify-center flex-shrink-0 border border-amber-200">
                <span class="material-symbols-outlined text-[20px]">admin_panel_settings</span>
            </div>
            <div class="space-y-0.5 text-xs">
                <span class="text-[10px] text-amber-700 uppercase font-extrabold tracking-wider block">Permintaan Revisi Oleh</span>
                <strong class="font-extrabold text-amber-900 text-sm uppercase tracking-tight block"><?php echo htmlspecialchars($data['admin_revisi']); ?></strong>
                <p class="text-[11px] text-amber-800 font-medium pt-0.5">Administrator tersebut meminta perbaikan/revisi pada laporan patroli ini.</p>
            </div>
        </div>
        <?php endif; ?>

        <!-- STATUS & TIKET CARD -->
        <div class="bg-white rounded-3xl p-5 shadow-sm border border-slate-100 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3.5">
                <div>
                    <span class="text-[10px] text-slate-400 uppercase font-extrabold tracking-wider">Nomor Tiket</span>
                    <h2 class="font-extrabold text-base text-[#0052cc] mt-0.5">#PAT-2026-<?php echo str_pad($data['id'], 3, '0', STR_PAD_LEFT); ?></h2>
                </div>
                <div class="text-right">
                    <span class="text-[10px] text-slate-400 uppercase font-extrabold tracking-wider block">Status</span>
                    <span class="inline-block px-3 py-1 rounded-full text-xs font-extrabold bg-amber-50 text-amber-700 border border-amber-200 mt-1 shadow-xs">
                        <?php echo ucfirst($data['status_laporan'] ?? 'Menunggu Verifikasi'); ?>
                    </span>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3 text-xs pt-1">
                <div>
                    <span class="text-slate-400 font-semibold block">Waktu Patroli</span>
                    <strong class="font-extrabold text-[#0d1c30] text-[13px]"><?php echo htmlspecialchars($data['tanggal'] ?? '-'); ?></strong>
                </div>
                <div>
                    <span class="text-slate-400 font-semibold block">Teknisi Pelapor</span>
                    <strong class="font-extrabold text-[#0d1c30] text-[13px]"><?php echo htmlspecialchars(ucfirst($data['username'])); ?> (NIP: <?php echo htmlspecialchars($data['nip']); ?>)</strong>
                </div>
            </div>
        </div>

        <!-- LOKASI & GPS -->
        <div class="bg-white rounded-3xl p-5 shadow-sm border border-slate-100 space-y-3">
            <h3 class="font-extrabold text-xs text-slate-400 uppercase tracking-wider">Lokasi & Koordinat</h3>
            <div class="flex items-start gap-3 pt-1">
                <div class="w-9 h-9 rounded-xl bg-blue-50 text-[#0052cc] flex items-center justify-center flex-shrink-0 border border-blue-100">
                    <span class="material-symbols-outlined text-[20px]">location_on</span>
                </div>
                <div>
                    <strong class="text-sm font-extrabold text-[#0d1c30] block leading-snug"><?php echo htmlspecialchars($data['lokasi_area']); ?></strong>
                    <span class="text-xs font-bold text-blue-600 block mt-1">Lat/Lng: <?php echo htmlspecialchars($data['latitude']); ?>, <?php echo htmlspecialchars($data['longitude']); ?></span>
                    <span class="text-[11px] font-bold text-emerald-600 block mt-0.5">Akurasi GPS: ±<?php echo htmlspecialchars($data['akurasi_gps'] ?? '0'); ?>m</span>
                </div>
            </div>
        </div>

        <!-- KONDISI & ANCAMAN -->
        <div class="bg-white rounded-3xl p-5 shadow-sm border border-slate-100 space-y-4">
            <h3 class="font-extrabold text-xs text-slate-400 uppercase tracking-wider">Hasil Pemeriksaan Lapangan</h3>
            
            <div class="space-y-3 text-xs">
                <div>
                    <span class="text-slate-400 font-semibold block mb-1">Kondisi Fisik Kabel/Tiang</span>
                    <span class="inline-block px-3.5 py-1.5 rounded-xl bg-slate-100 font-extrabold text-slate-800 border border-slate-200">
                        <?php echo htmlspecialchars($data['kondisi_fisik']); ?>
                    </span>
                </div>

                <div>
                    <span class="text-slate-400 font-semibold block mb-1.5">Potensi Ancaman Dilaporkan</span>
                    <div class="flex flex-wrap gap-1.5">
                        <?php 
                            $ancaman_list = explode(', ', $data['potensi_ancaman']);
                            foreach($ancaman_list as $ancaman) {
                                if(!empty($ancaman)) {
                                    echo '<span class="px-3 py-1 rounded-xl bg-rose-50 text-rose-700 border border-rose-200 text-xs font-extrabold shadow-xs">'.htmlspecialchars($ancaman).'</span>';
                                }
                            }
                        ?>
                    </div>
                </div>

                <div>
                    <span class="text-slate-400 font-semibold block mb-1">Catatan Tambahan</span>
                    <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-100 text-slate-700 font-semibold leading-relaxed">
                        <?php echo nl2br(htmlspecialchars($data['catatan'] ?? 'Tidak ada catatan.')); ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- FOTO BUKTI LAPANGAN -->
        <div class="bg-white rounded-3xl p-5 shadow-sm border border-slate-100 space-y-3">
            <h3 class="font-extrabold text-xs text-slate-400 uppercase tracking-wider">Foto Bukti Lapangan</h3>
            <?php if (!empty($data['foto'])): ?>
                <div class="overflow-hidden rounded-2xl border border-slate-200 shadow-sm">
                    <img src="../assets/img/uploads/<?php echo htmlspecialchars($data['foto']); ?>" alt="Foto Bukti Patroli" class="w-full h-64 object-cover">
                </div>
            <?php else: ?>
                <p class="text-xs text-slate-400 italic">Tidak ada foto yang dilampirkan.</p>
            <?php endif; ?>
        </div>

    </main>

    <!-- BOTTOM NAVBAR -->
    <nav class="fixed bottom-0 left-0 right-0 bg-white/95 backdrop-blur-md border-t border-slate-200/80 py-2.5 px-6 flex justify-around items-center z-30 max-w-md mx-auto shadow-lg">
        <a href="beranda.php" class="flex flex-col items-center <?php echo ($current_page == 'beranda') ? 'text-[#0052cc] font-bold' : 'text-slate-400 hover:text-slate-600'; ?>">
            <span class="material-symbols-outlined text-[22px]" <?php echo ($current_page == 'beranda') ? 'style="font-variation-settings: \'FILL\' 1;"' : ''; ?>>grid_view</span>
            <span class="text-[10px] mt-0.5">Beranda</span>
        </a>
        <a href="form_patroli.php" class="flex flex-col items-center <?php echo ($current_page == 'patroli') ? 'text-[#0052cc] font-bold' : 'text-slate-400 hover:text-slate-600'; ?>">
            <span class="material-symbols-outlined text-[22px]" <?php echo ($current_page == 'patroli') ? 'style="font-variation-settings: \'FILL\' 1;"' : ''; ?>>assignment</span>
            <span class="text-[10px] mt-0.5">Patroli</span>
        </a>
        <a href="riwayat.php" class="flex flex-col items-center <?php echo ($current_page == 'riwayat') ? 'text-[#0052cc] font-bold' : 'text-slate-400 hover:text-slate-600'; ?>">
            <span class="material-symbols-outlined text-[22px]" <?php echo ($current_page == 'riwayat') ? 'style="font-variation-settings: \'FILL\' 1;"' : ''; ?>>history</span>
            <span class="text-[10px] mt-0.5">Riwayat</span>
        </a>
        <a href="profil.php" class="flex flex-col items-center <?php echo ($current_page == 'profil') ? 'text-[#0052cc] font-bold' : 'text-slate-400 hover:text-slate-600'; ?>">
            <span class="material-symbols-outlined text-[22px]" <?php echo ($current_page == 'profil') ? 'style="font-variation-settings: \'FILL\' 1;"' : ''; ?>>person</span>
            <span class="text-[10px] mt-0.5">Profil</span>
        </a>
    </nav>

</body>
</html>