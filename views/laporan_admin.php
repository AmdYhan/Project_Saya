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

$search = $_GET['search'] ?? '';
$tgl_mulai = $_GET['tgl_mulai'] ?? '';
$tgl_selesai = $_GET['tgl_selesai'] ?? '';
$status_filter = $_GET['status_filter'] ?? 'semua';

$where = ["1=1"];
if (!empty($search)) {
    $s = mysqli_real_escape_string($conn, $search);
    $where[] = "(l.lokasi_area LIKE '%$s%' OR l.username LIKE '%$s%' OR l.catatan LIKE '%$s%')";
}
if (!empty($tgl_mulai) && !empty($tgl_selesai)) {
    $where[] = "DATE(l.tanggal) BETWEEN '$tgl_mulai' AND '$tgl_selesai'";
}
if ($status_filter !== 'semua') {
    $st = mysqli_real_escape_string($conn, $status_filter);
    $where[] = "l.status_laporan = '$st'";
}

$where_sql = implode(" AND ", $where);
$query_laporan = mysqli_query($conn, "SELECT l.*, u.nip FROM laporan_patroli l LEFT JOIN users u ON l.username = u.username WHERE $where_sql ORDER BY l.tanggal DESC");

$q_menunggu = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM laporan_patroli WHERE status_laporan = 'Menunggu Verifikasi'"))['total'] ?? 0;
$q_kritis = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM laporan_patroli WHERE potensi_ancaman LIKE '%Kritis%' OR kondisi_fisik LIKE '%Rusak%'"))['total'] ?? 0;
$q_disetujui = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM laporan_patroli WHERE status_laporan = 'Disetujui'"))['total'] ?? 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Laporan Admin - Lintasarta</title>
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
    </style>
</head>
<body class="text-[#0d1c30] pb-28">

    <header class="bg-white border-b border-slate-100 sticky top-0 z-30 px-4 py-3 shadow-sm">
        <div class="max-w-md mx-auto flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <div>
                    <h1 class="font-extrabold text-sm text-[#0d1c30] leading-tight">SAMARINDA</h1>
                    <p class="text-[11px] text-slate-400 font-medium">Verifikasi Laporan</p>
                </div>
            </div>
            <a href="profil_admin.php" class="w-10 h-10 rounded-full bg-blue-50/80 border border-blue-100 flex items-center justify-center text-[#0052cc] active:scale-95 transition shadow-sm">
                <span class="material-symbols-outlined text-[22px]">person</span>
            </a>
        </div>
    </header>

    <main class="max-w-md mx-auto p-4 space-y-4">

        <div class="animate-entry delay-100 flex justify-between items-center">
            <div>
                <h2 class="font-bold text-base text-[#0d1c30]">Verifikasi Antrean Patroli</h2>
                <p class="text-xs text-slate-400">Hub Samarinda & Koridor Mahakam</p>
            </div>
            <span class="inline-flex items-center gap-1 bg-emerald-50 text-emerald-600 text-[10px] font-bold px-2.5 py-1 rounded-full border border-emerald-200">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Tersinkronisasi
            </span>
        </div>

        <div class="animate-entry delay-200 grid grid-cols-3 gap-2.5">
            <div class="bg-white p-3 rounded-2xl border border-slate-100 shadow-sm transition hover:shadow-md">
                <div class="flex justify-between items-center">
                    <span class="text-[11px] font-semibold text-slate-500">Menunggu</span>
                    <span class="w-2 h-2 rounded-full bg-amber-400"></span>
                </div>
                <p class="text-xl font-extrabold text-[#0d1c30] mt-1"><?php echo $q_menunggu; ?></p>
            </div>
            <div class="bg-white p-3 rounded-2xl border border-slate-100 shadow-sm transition hover:shadow-md">
                <div class="flex justify-between items-center">
                    <span class="text-[11px] font-semibold text-slate-500">Kritis</span>
                    <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                </div>
                <p class="text-xl font-extrabold text-[#0d1c30] mt-1"><?php echo $q_kritis; ?></p>
            </div>
            <div class="bg-white p-3 rounded-2xl border border-slate-100 shadow-sm transition hover:shadow-md">
                <div class="flex justify-between items-center">
                    <span class="text-[11px] font-semibold text-slate-500">Disetujui</span>
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                </div>
                <p class="text-xl font-extrabold text-[#0d1c30] mt-1"><?php echo $q_disetujui; ?></p>
            </div>
        </div>

        <form method="GET" class="animate-entry delay-300 bg-white p-3.5 rounded-2xl border border-slate-100 shadow-sm space-y-3">
            <div class="relative flex items-center">
                <span class="material-symbols-outlined absolute left-3 text-slate-400 text-[20px]">search</span>
                <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Cari tiket, teknisi, atau segmen..." 
                       class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 pl-9 pr-3 text-xs font-semibold focus:outline-none focus:border-[#0052cc]">
            </div>
            <div class="grid grid-cols-2 gap-2 text-xs">
                <div>
                    <label class="text-[10px] font-bold text-slate-500 block mb-1">Dari Tanggal</label>
                    <input type="date" name="tgl_mulai" value="<?php echo $tgl_mulai; ?>" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-2 text-xs">
                </div>
                <div>
                    <label class="text-[10px] font-bold text-slate-500 block mb-1">Sampai Tanggal</label>
                    <input type="date" name="tgl_selesai" value="<?php echo $tgl_selesai; ?>" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-2 text-xs">
                </div>
            </div>
            <div class="flex gap-2 pt-1">
                <button type="submit" class="flex-1 bg-[#0052cc] hover:bg-blue-700 text-white font-bold py-2 rounded-xl text-xs flex items-center justify-center gap-1 transition active:scale-95">
                    <span class="material-symbols-outlined text-[16px]">filter_alt</span> Terapkan Filter
                </button>
                <a href="laporan_admin.php" class="bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold px-3 py-2 rounded-xl text-xs flex items-center transition">Reset</a>
            </div>
        </form>

        <form id="formAksi" method="POST" class="animate-entry delay-400 space-y-3">
            <div class="bg-white p-3 rounded-2xl border border-slate-100 shadow-sm flex items-center justify-between">
                <label class="flex items-center gap-2 cursor-pointer text-xs font-bold text-slate-700">
                    <input type="checkbox" id="selectAll" onclick="toggleSelectAll(this)" class="w-4 h-4 text-[#0052cc] rounded">
                    <span>Pilih Semua</span>
                </label>
                <div class="flex gap-1.5">
                    <button type="button" onclick="submitAksi('pdf')" class="bg-rose-600 hover:bg-rose-700 text-white px-3 py-1.5 rounded-xl text-xs font-bold transition active:scale-95 flex items-center gap-1 shadow-sm">
                        <span class="material-symbols-outlined text-[15px]">picture_as_pdf</span> PDF
                    </button>
                    <button type="button" onclick="submitAksi('excel')" class="bg-emerald-600 hover:bg-emerald-700 text-white px-3 py-1.5 rounded-xl text-xs font-bold transition active:scale-95 flex items-center gap-1 shadow-sm">
                        <span class="material-symbols-outlined text-[15px]">table_chart</span> Excel
                    </button>
                </div>
            </div>

            <div class="space-y-3">
                <?php if (mysqli_num_rows($query_laporan) > 0) : ?>
                    <?php while ($row = mysqli_fetch_assoc($query_laporan)) : 
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
                        <div class="bg-white rounded-2xl p-4 shadow-sm border border-slate-100 space-y-3 transition hover:shadow-md duration-200">
                            
                            <div class="flex justify-between items-center pb-2 border-b border-slate-100">
                                <div class="flex items-center gap-2">
                                    <input type="checkbox" name="laporan_ids[]" value="<?php echo $row['id']; ?>" class="item-check w-4 h-4 text-[#0052cc] rounded">
                                    <span class="text-xs font-extrabold text-slate-500 tracking-wide flex items-center gap-1">
                                        <span class="material-symbols-outlined text-[15px] text-blue-600">confirmation_number</span>
                                        <?php echo $nomor_tiket; ?>
                                    </span>
                                </div>
                                <span class="text-[10px] font-extrabold px-3 py-1 rounded-full border shadow-2xs <?php echo $status_class; ?>">
                                    <?php echo htmlspecialchars($status_laporan); ?>
                                </span>
                            </div>

                            <div class="space-y-1 pl-6">
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

                            <div class="bg-slate-50/80 p-3 rounded-xl border border-slate-100 grid grid-cols-5 gap-3 items-center ml-6">
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

                            <div class="pl-6 pt-1 flex gap-2">
                                <a href="detail_laporan_admin.php?id=<?php echo $row['id']; ?>" class="w-full bg-[#0052cc]/10 hover:bg-[#0052cc]/20 text-[#0052cc] font-bold py-2.5 rounded-xl text-xs flex items-center justify-center gap-1.5 transition active:scale-[0.98]">
                                    <span class="material-symbols-outlined text-[16px]">visibility</span> Detail Tiket & Verifikasi
                                </a>
                            </div>

                        </div>
                    <?php endwhile; ?>
                <?php else : ?>
                    <div class="bg-white rounded-2xl p-8 text-center border border-slate-100 space-y-2">
                        <span class="material-symbols-outlined text-slate-300 text-[40px]">assignment_late</span>
                        <p class="text-xs font-bold text-slate-400">Tidak ada laporan ditemukan.</p>
                    </div>
                <?php endif; ?>
            </div>
        </form>

    </main>

    <nav class="fixed bottom-0 left-0 right-0 bg-white/95 backdrop-blur-md border-t border-slate-200 py-2.5 px-6 flex justify-around items-center z-30 max-w-md mx-auto shadow-lg">
        <a href="dashboard_admin.php" class="flex flex-col items-center text-slate-400 hover:text-slate-600"><span class="material-symbols-outlined text-[22px]">grid_view</span><span class="text-[10px] mt-0.5">Beranda</span></a>
        <a href="pemantauan_admin.php" class="flex flex-col items-center text-slate-400 hover:text-slate-600"><span class="material-symbols-outlined text-[22px]">map</span><span class="text-[10px] mt-0.5">Peta Jalur</span></a>
        <a href="laporan_admin.php" class="flex flex-col items-center text-[#0052cc] font-bold"><span class="material-symbols-outlined text-[22px]" style="font-variation-settings: 'FILL' 1;">assignment</span><span class="text-[10px] mt-0.5">Laporan</span></a>
        <a href="profil_admin.php" class="flex flex-col items-center text-slate-400 hover:text-slate-600"><span class="material-symbols-outlined text-[22px]">person</span><span class="text-[10px] mt-0.5">Profil</span></a>
    </nav>

    <script>
        function toggleSelectAll(source) {
            let checkboxes = document.getElementsByClassName('item-check');
            for(let i=0; i<checkboxes.length; i++) {
                checkboxes[i].checked = source.checked;
            }
        }

        function submitAksi(jenis) {
            const form = document.getElementById('formAksi');
            form.action = (jenis === 'pdf') ? '../process/cetak_pdf.php' : '../process/cetak_excel.php';
            form.target = '_blank';
            form.submit();
        }
    </script>
</body>
</html>