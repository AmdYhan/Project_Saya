<?php
session_start();
include "../config/koneksi.php";

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Jika role admin, arahkan ke dashboard admin
if (isset($_SESSION['role']) && ($_SESSION['role'] == 'admin' || $_SESSION['role'] == 'koordinator' || $_SESSION['role'] == 'engginer')) {
    header("Location: dashboard_admin.php");
    exit();
}

$username = $_SESSION['username']; 
$query_user = mysqli_query($conn, "SELECT * FROM users WHERE username='$username'"); 
$user = mysqli_fetch_assoc($query_user); 
$id_teknisi = $user['id'] ?? 0; 

// Statistik status laporan 
$q_approved = mysqli_query($conn, "SELECT COUNT(*) AS total FROM laporan_patroli WHERE username = '$username' AND (status_laporan = 'Disetujui' OR status_laporan = 'Approved')"); 
$q_pending = mysqli_query($conn, "SELECT COUNT(*) AS total FROM laporan_patroli WHERE username = '$username' AND (status_laporan = 'Menunggu Verifikasi' OR status_laporan = 'Pending' OR status_laporan = '')"); 
$q_revisi = mysqli_query($conn, "SELECT COUNT(*) AS total FROM laporan_patroli WHERE username = '$username' AND (status_laporan = 'Revisi' OR status_laporan = 'Perlu Revisi' OR status_laporan = 'Ditolak' OR status_laporan = 'Rejected')"); 

$tot_approved = ($q_approved) ? (mysqli_fetch_assoc($q_approved)['total'] ?? 0) : 0; 
$tot_pending = ($q_pending) ? (mysqli_fetch_assoc($q_pending)['total'] ?? 0) : 0; 
$tot_revisi = ($q_revisi) ? (mysqli_fetch_assoc($q_revisi)['total'] ?? 0) : 0; 

// Ambil riwayat laporan terbaru (maksimal 3) beserta data NIP user
$query_riwayat = mysqli_query($conn, "SELECT l.*, u.nip FROM laporan_patroli l LEFT JOIN users u ON l.username = u.username WHERE l.username='$username' ORDER BY l.id DESC LIMIT 3"); 
$current_page = 'beranda'; 
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beranda - Lintasarta Samarinda</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f4f6f9; }

        /* Animasi Masuk Bertahap (Staggered Slide Up & Fade In) */
        @keyframes slideUpFade {
            0% {
                opacity: 0;
                transform: translateY(18px) scale(0.98);
            }
            100% {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        .animate-entry {
            opacity: 0;
            animation: slideUpFade 0.55s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        .delay-100 { animation-delay: 0.08s; }
        .delay-200 { animation-delay: 0.16s; }
        .delay-300 { animation-delay: 0.24s; }
        .delay-400 { animation-delay: 0.32s; }
    </style>
</head>
<body class="text-[#0d1c30] pb-28">

    <!-- HEADER -->
    <header class="bg-white/95 backdrop-blur-md border-b border-slate-200/80 sticky top-0 z-30 px-4 py-4 shadow-xs">
        <div class="max-w-md mx-auto flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="border-l border-slate-200 pl-3">
                    <h1 class="font-extrabold text-base tracking-wide text-[#0d1c30] leading-tight">SAMARINDA OPS</h1>
                    <p class="text-xs text-slate-500 font-semibold tracking-wide">Sistem Patroli OSP</p>
                </div>
            </div>
            
            <a href="profil.php" class="w-10 h-10 rounded-full bg-blue-50 border border-blue-100 flex items-center justify-center text-[#0052cc] shadow-xs active:scale-95 transition">
                <span class="material-symbols-outlined text-[22px]">person</span>
            </a>
        </div>
    </header>

    <!-- KONTEN UTAMA -->
    <main class="max-w-md mx-auto p-4 space-y-4">
        
        <!-- 1. KARTU PROFIL TEKNISI (MUNCUL PERTAMA) -->
        <div class="animate-entry delay-100 relative bg-gradient-to-r from-[#0052cc] via-blue-600 to-indigo-700 text-white rounded-2xl p-4 shadow-md shadow-blue-500/20 overflow-hidden flex items-center justify-between">
            <div class="absolute -right-6 -top-6 w-24 h-24 bg-white/10 rounded-full blur-xl pointer-events-none"></div>

            <div class="flex items-center gap-3 relative z-10">
                <div class="w-12 h-12 bg-white/15 backdrop-blur-md text-white rounded-2xl flex items-center justify-center border border-white/20 shadow-inner flex-shrink-0">
                    <span class="material-symbols-outlined text-[26px]">engineering</span>
                </div>
                
                <div class="space-y-0.5">
                    <div class="flex items-center gap-2">
                        <h2 class="font-extrabold text-base tracking-tight text-white"><?php echo htmlspecialchars(ucfirst($user['username'] ?? 'Teknisi')); ?></h2>
                        <span class="px-2 py-0.5 bg-white text-[#0052cc] text-[10px] font-extrabold rounded shadow-xs">
                            <?php echo strtoupper(htmlspecialchars($user['role'] ?? 'Teknisi')); ?>
                        </span>
                    </div>
                    <p class="text-[11px] text-blue-100 font-medium">
                        NIP: <span class="font-bold text-white"><?php echo htmlspecialchars($user['nip'] ?? '101'); ?></span> • OSP Samarinda
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-1.5 px-2.5 py-1 bg-emerald-500/20 backdrop-blur-md border border-emerald-400/30 rounded-full flex-shrink-0 relative z-10">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                <span class="text-[10px] font-extrabold text-emerald-100">Online</span>
            </div>
        </div>

        <!-- 2. 3 KOTAK STATISTIK STATUS LAPORAN (MUNCUL KEDUA) -->
        <div class="animate-entry delay-200 grid grid-cols-3 gap-3">
            <div class="bg-white rounded-2xl p-3.5 shadow-sm border border-slate-100 text-center space-y-1 transition hover:shadow-md hover:-translate-y-0.5 duration-200">
                <div class="w-8 h-8 bg-emerald-50 text-emerald-600 rounded-xl flex items-center justify-center mx-auto border border-emerald-100">
                    <span class="material-symbols-outlined text-[16px]">check_circle</span>
                </div>
                <div>
                    <h3 class="font-extrabold text-base text-emerald-600"><?php echo $tot_approved; ?></h3>
                    <p class="text-[10px] font-bold text-slate-400 tracking-wider uppercase">Approved</p>
                </div>
            </div>

            <div class="bg-white rounded-2xl p-3.5 shadow-sm border border-slate-100 text-center space-y-1 transition hover:shadow-md hover:-translate-y-0.5 duration-200">
                <div class="w-8 h-8 bg-amber-50 text-amber-600 rounded-xl flex items-center justify-center mx-auto border border-amber-100">
                    <span class="material-symbols-outlined text-[16px]">hourglass_top</span>
                </div>
                <div>
                    <h3 class="font-extrabold text-base text-amber-500"><?php echo $tot_pending; ?></h3>
                    <p class="text-[10px] font-bold text-slate-400 tracking-wider uppercase">Pending</p>
                </div>
            </div>

            <div class="bg-white rounded-2xl p-3.5 shadow-sm border border-slate-100 text-center space-y-1 transition hover:shadow-md hover:-translate-y-0.5 duration-200">
                <div class="w-8 h-8 bg-indigo-50 text-indigo-600 rounded-xl flex items-center justify-center mx-auto border border-indigo-100">
                    <span class="material-symbols-outlined text-[16px]">edit_note</span>
                </div>
                <div>
                    <h3 class="font-extrabold text-base text-indigo-600"><?php echo $tot_revisi; ?></h3>
                    <p class="text-[10px] font-bold text-slate-400 tracking-wider uppercase">Revisi</p>
                </div>
            </div>
        </div>

        <!-- 3. BANNER AKSI UTAMA (MUNCUL KETIGA) -->
        <div class="animate-entry delay-300 bg-gradient-to-br from-[#0052cc] to-blue-700 text-white rounded-3xl p-5 shadow-md shadow-blue-500/20 space-y-3.5 relative overflow-hidden">
            <div class="absolute -right-6 -bottom-6 w-32 h-32 bg-white/10 rounded-full blur-2xl pointer-events-none"></div>
            <div class="space-y-1.5 relative z-10">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-white/15 backdrop-blur-md rounded-full text-[11px] font-semibold text-blue-50 border border-white/10">
                    <span class="material-symbols-outlined text-[14px]">shield</span> OSP Maintenance & Monitoring
                </span>
                <h3 class="font-extrabold text-base tracking-tight pt-0.5">Selamat Datang, <?php echo htmlspecialchars(ucfirst($user['username'] ?? 'Teknisi')); ?>! Siap Melakukan Patroli Jalur?</h3>
                <p class="text-xs text-blue-100 leading-relaxed font-medium">Pastikan jaringan fiber optik aman dari potensi gangguan fisik serta catat setiap temuan di lapangan hari ini.</p>
            </div>
            <a href="form_patroli.php" class="relative z-10 block w-full py-3 bg-white text-[#0052cc] text-center font-extrabold text-xs rounded-2xl shadow-sm active:scale-95 transition flex items-center justify-center gap-1.5 hover:bg-blue-50">
                <span class="material-symbols-outlined text-[18px]">task_alt</span>
                Buat Laporan Patroli Baru
            </a>
        </div>

        <!-- 4. RIWAYAT LAPORAN SAYA (MUNCUL KEEMPAT) -->
        <div class="animate-entry delay-400 space-y-3 pt-1">
            <div class="flex items-center justify-between px-1">
                <h3 class="font-extrabold text-sm text-[#0d1c30]">Riwayat Laporan Saya</h3>
                <a href="riwayat.php" class="text-xs font-bold text-[#0052cc] hover:underline flex items-center gap-0.5">Lihat Semua <span class="material-symbols-outlined text-[14px]">arrow_forward</span></a>
            </div>

            <?php if ($query_riwayat && mysqli_num_rows($query_riwayat) > 0): ?>
                <?php 
                $item_delay = 0.38; 
                while ($row = mysqli_fetch_assoc($query_riwayat)): 
                ?>
                    <?php 
                        $potensi = $row['potensi_ancaman'] ?: 'KRITIS (Rawan Putus)';
                        $nomor_tiket = '#PAT-2026-' . str_pad($row['id'], 3, '0', STR_PAD_LEFT);
                        $status_laporan = trim($row['status_laporan'] ?? 'Menunggu Verifikasi');
                        
                        $status_lower = strtolower($status_laporan);
                        if ($status_lower == 'disetujui' || $status_lower == 'approved' || $status_lower == 'selesai') {
                            $status_class = "bg-emerald-100 text-emerald-800 border-emerald-300";
                        } elseif ($status_lower == 'revisi' || $status_lower == 'perlu revisi' || $status_lower == 'ditolak' || $status_lower == 'rejected') {
                            $status_class = "bg-amber-100 text-amber-800 border-amber-300";
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
                    <div class="animate-entry bg-white rounded-2xl p-4 shadow-sm border border-slate-100 space-y-3 relative transition hover:shadow-md duration-200" style="animation-delay: <?php echo $item_delay; ?>s;">
                            <!-- BARIS ATAS: Nomor Tiket & Status (Supaya Sejajar Rapi & Tidak Saling Tabrak) -->
                            <div class="flex justify-between items-center pb-2 border-b border-slate-100">
                                <span class="text-xs font-extrabold text-slate-500 tracking-wide flex items-center gap-1">
                                    <span class="material-symbols-outlined text-[15px] text-blue-600">confirmation_number</span>
                                    <?php echo $nomor_tiket; ?>
                                </span>
                                <span class="text-[10px] font-extrabold px-3 py-1 rounded-full border shadow-2xs <?php echo $status_class; ?>">
                                    <?php echo htmlspecialchars($status_laporan); ?>
                                </span>
                            </div>

                            <!-- KONTEN TENGAH: Potensi Ancaman (Full Lebar ke Bawah agar Panjang pun Aman) -->
                            <div class="space-y-1">
                                <div class="flex items-center gap-1.5">
                                    <span class="w-2 h-2 rounded-full bg-[#0052cc]"></span>
                                    <span class="text-xs font-extrabold text-[#0052cc]">
                                        <?php echo htmlspecialchars($potensi); ?>
                                    </span>
                                </div>
                                <h3 class="font-extrabold text-sm text-[#0d1c30] leading-snug"><?php echo htmlspecialchars($row['lokasi_area']); ?></h3>
                                <p class="text-[11px] text-slate-500 flex items-center gap-1">
                                    <span class="material-symbols-outlined text-[14px] text-rose-500">location_on</span>
                                    <span><?php echo htmlspecialchars($row['lokasi_area']); ?> (<?php echo $row['latitude']; ?>, <?php echo $row['longitude']; ?>)</span>
                                </p>
                            </div>

                        <div class="bg-slate-50 p-3 rounded-xl border border-slate-100 grid grid-cols-2 gap-3 text-[11px] items-center">
                            <?php 
                            $foto_path = "../assets/img/uploads/" . $row['foto'];
                            if (!empty($row['foto']) && file_exists($foto_path)) :
                            ?>
                            <div>
                                <div class="relative">
                                    <img src="<?php echo $foto_path; ?>" class="w-full h-24 object-cover rounded-lg border">
                                    <span class="absolute bottom-1 left-1 bg-black/60 text-white text-[9px] px-1.5 py-0.5 rounded font-bold">Bukti</span>
                                </div>
                            </div>
                            <?php endif; ?>
                            <div class="space-y-1">
                                <p class="text-slate-500">Pelapor: <b class="text-slate-800"><?php echo htmlspecialchars($row['username']); ?> (NIP-<?php echo htmlspecialchars($row['nip'] ?? '44821'); ?>)</b></p>
                                <p class="text-slate-500">Waktu Temuan: <b class="text-slate-800"><?php echo date('d M, H:i', strtotime($row['tanggal'])); ?> WITA</b></p>
                                <p class="text-slate-500">Dampak FO: <span class="<?php echo $kondisi_class; ?>"><?php echo htmlspecialchars($kondisi_fisik); ?></span></p>
                            </div>
                        </div>

                        <a href="detail_laporan.php?id=<?php echo $row['id']; ?>" class="w-full bg-[#0052cc]/10 hover:bg-[#0052cc]/20 text-[#0052cc] font-bold py-2.5 rounded-xl text-xs flex items-center justify-center gap-1.5 transition active:scale-[0.98]">
                            <span class="material-symbols-outlined text-[18px]">visibility</span> Detail Tiket
                        </a>
                    </div>
                <?php 
                $item_delay += 0.08;
                endwhile; 
                ?>
            <?php else: ?>
                <div class="text-center py-10 bg-white rounded-2xl border border-slate-100 p-4 space-y-2.5 shadow-sm">
                    <div class="w-12 h-12 bg-blue-50 text-[#0052cc] rounded-full flex items-center justify-center mx-auto border border-blue-100">
                        <span class="material-symbols-outlined text-[24px]">history_toggle_off</span>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-[#0d1c30]">Belum Ada Laporan Patroli</p>
                        <p class="text-[11px] text-slate-400 mt-0.5">Laporan inspeksi yang Anda buat akan muncul di sini.</p>
                    </div>
                </div>
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