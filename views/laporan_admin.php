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
    $where[] = "(l.lokasi_area LIKE '%$s%' OR l.username LIKE '%$s%' OR l.catatan LIKE '%$s%' OR l.potensi_ancaman LIKE '%$s%')";
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

$current_page = 'laporan';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Audit & Verifikasi Laporan - Lintasarta Samarinda</title>
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
        
        <!-- TOP HEADER -->
        <header class="bg-white border-b border-slate-200 px-6 py-4 flex justify-between items-center shadow-xs z-10">
            <div>
                <h2 class="font-extrabold text-base text-[#0d1c30]">Audit & Verifikasi Antrean Patroli</h2>
                <p class="text-xs text-slate-400">Hub Samarinda & Koridor Serat Optik Mahakam</p>
            </div>
            
            <div class="flex items-center gap-3">
                <span class="inline-flex items-center gap-1.5 bg-emerald-50 text-emerald-700 text-xs font-bold px-3 py-1.5 rounded-xl border border-emerald-200">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span> Database Tersinkronisasi
                </span>
            </div>
        </header>

        <!-- KONTEN SCROLLABLE -->
        <main class="flex-1 overflow-y-auto p-6 space-y-6 animate-main">
            
            <!-- NOTIFIKASI AKSI ADMIN (IN-PAGE BUKAN ALERT) -->
            <?php 
            $admin_notif = null;
            if (!empty($_SESSION['admin_notif'])) {
                $admin_notif = $_SESSION['admin_notif'];
                unset($_SESSION['admin_notif']);
            } elseif (isset($_GET['pesan']) && $_GET['pesan'] === 'berhasil_revisi') {
                $admin_notif = ['tipe' => 'sukses', 'pesan' => 'Permintaan revisi dan catatan berhasil dikirimkan ke teknisi.'];
            }
            ?>
            <?php if ($admin_notif): ?>
                <?php 
                    $is_sukses = ($admin_notif['tipe'] === 'sukses');
                    $bg_notif = $is_sukses ? 'bg-emerald-50 border-emerald-200 text-emerald-800' : 'bg-rose-50 border-rose-200 text-rose-800';
                    $icon_notif = $is_sukses ? 'check_circle' : 'error';
                    $icon_color = $is_sukses ? 'text-emerald-600' : 'text-rose-600';
                ?>
                <div id="bannerNotifAdmin" class="p-4 rounded-2xl border <?php echo $bg_notif; ?> flex items-center justify-between shadow-sm animate-main">
                    <div class="flex items-center gap-3">
                        <span class="material-symbols-outlined <?php echo $icon_color; ?> text-[22px]"><?php echo $icon_notif; ?></span>
                        <p class="text-xs font-extrabold"><?php echo htmlspecialchars($admin_notif['pesan']); ?></p>
                    </div>
                    <button onclick="document.getElementById('bannerNotifAdmin').remove()" class="text-slate-400 hover:text-slate-600">
                        <span class="material-symbols-outlined text-[18px]">close</span>
                    </button>
                </div>
            <?php endif; ?>

            <!-- TOAST FLOATING UNTUK PERINGATAN CHECKBOX / EKSPOR -->
            <div id="toastAdmin" class="fixed top-6 right-6 z-50 transform translate-y-[-100px] opacity-0 pointer-events-none transition-all duration-300 ease-out">
                <div class="bg-slate-900/95 backdrop-blur-md text-white px-5 py-3.5 rounded-2xl shadow-2xl flex items-center gap-3 border border-slate-700/80">
                    <span class="material-symbols-outlined text-amber-400 text-[20px]" id="toastAdminIcon">warning</span>
                    <span class="text-xs font-bold" id="toastAdminText">Notifikasi</span>
                </div>
            </div>

            <!-- 1. RINGKASAN STATISTIK ANTREAN -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-sm flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Menunggu Verifikasi</p>
                        <h4 class="text-2xl font-extrabold text-amber-600 mt-1"><?php echo $q_menunggu; ?> <span class="text-xs font-normal text-slate-400">Laporan</span></h4>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center">
                        <span class="material-symbols-outlined text-[24px]">pending_actions</span>
                    </div>
                </div>

                <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-sm flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Temuan Kritis</p>
                        <h4 class="text-2xl font-extrabold text-rose-600 mt-1"><?php echo $q_kritis; ?> <span class="text-xs font-normal text-slate-400">Laporan</span></h4>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center">
                        <span class="material-symbols-outlined text-[24px]">warning</span>
                    </div>
                </div>

                <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-sm flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Telah Disetujui</p>
                        <h4 class="text-2xl font-extrabold text-emerald-600 mt-1"><?php echo $q_disetujui; ?> <span class="text-xs font-normal text-slate-400">Laporan</span></h4>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                        <span class="material-symbols-outlined text-[24px]">verified</span>
                    </div>
                </div>
            </div>

            <!-- 2. FORM FILTER & PENCARIAN HORIZONTAL -->
            <form method="GET" class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-sm">
                <div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-end">
                    <div class="md:col-span-4 space-y-1">
                        <label class="text-[11px] font-bold text-slate-600 block">Kata Kunci Pencarian</label>
                        <div class="relative flex items-center">
                            <span class="material-symbols-outlined absolute left-3 text-slate-400 text-[18px]">search</span>
                            <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Cari segmen, teknisi, atau ancaman..." 
                                   class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 pl-9 pr-3 text-xs font-semibold focus:outline-none focus:border-[#0052cc] focus:bg-white transition">
                        </div>
                    </div>

                    <div class="md:col-span-2 space-y-1">
                        <label class="text-[11px] font-bold text-slate-600 block">Status Verifikasi</label>
                        <select name="status_filter" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3 text-xs font-semibold focus:outline-none focus:border-[#0052cc] focus:bg-white transition">
                            <option value="semua" <?php echo ($status_filter === 'semua') ? 'selected' : ''; ?>>Semua Status</option>
                            <option value="Menunggu Verifikasi" <?php echo ($status_filter === 'Menunggu Verifikasi') ? 'selected' : ''; ?>>Menunggu Verifikasi</option>
                            <option value="Disetujui" <?php echo ($status_filter === 'Disetujui') ? 'selected' : ''; ?>>Disetujui</option>
                            <option value="Revisi" <?php echo ($status_filter === 'Revisi') ? 'selected' : ''; ?>>Revisi</option>
                        </select>
                    </div>

                    <div class="md:col-span-2 space-y-1">
                        <label class="text-[11px] font-bold text-slate-600 block">Dari Tanggal</label>
                        <input type="date" name="tgl_mulai" value="<?php echo htmlspecialchars($tgl_mulai); ?>" 
                               class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-xs font-semibold focus:outline-none focus:border-[#0052cc] focus:bg-white transition">
                    </div>

                    <div class="md:col-span-2 space-y-1">
                        <label class="text-[11px] font-bold text-slate-600 block">Sampai Tanggal</label>
                        <input type="date" name="tgl_selesai" value="<?php echo htmlspecialchars($tgl_selesai); ?>" 
                               class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-xs font-semibold focus:outline-none focus:border-[#0052cc] focus:bg-white transition">
                    </div>

                    <div class="md:col-span-2 flex gap-2">
                        <button type="submit" class="flex-1 bg-[#0052cc] hover:bg-blue-700 text-white font-bold py-2.5 px-3 rounded-xl text-xs flex items-center justify-center gap-1 transition shadow-sm">
                            <span class="material-symbols-outlined text-[16px]">filter_alt</span> Filter
                        </button>
                        <a href="laporan_admin.php" class="bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold py-2.5 px-3 rounded-xl text-xs flex items-center justify-center transition" title="Reset Filter">
                            <span class="material-symbols-outlined text-[16px]">restart_alt</span>
                        </a>
                    </div>
                </div>
            </form>

            <!-- 3. TABEL DATA LAPORAN & EKSPOR MASSAL -->
            <form id="formAksi" method="POST" class="bg-white rounded-3xl p-6 shadow-sm border border-slate-200/80 space-y-4">
                
                <!-- Toolbar Aksi Ekspor -->
                <div class="flex flex-wrap justify-between items-center gap-3 pb-3 border-b border-slate-100">
                    <div>
                        <h3 class="font-extrabold text-sm text-[#0d1c30]">Daftar Laporan Masuk</h3>
                        <p class="text-xs text-slate-400">Centang tiket untuk mengekspor laporan terpilih ke PDF atau Excel</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="button" onclick="submitAksi('pdf')" class="bg-rose-600 hover:bg-rose-700 text-white px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-1.5 shadow-sm">
                            <span class="material-symbols-outlined text-[16px]">picture_as_pdf</span> Ekspor PDF
                        </button>
                        <button type="button" onclick="submitAksi('excel')" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-1.5 shadow-sm">
                            <span class="material-symbols-outlined text-[16px]">table_chart</span> Ekspor Excel
                        </button>
                    </div>
                </div>

                <!-- Tabel Data -->
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="border-b border-slate-200 text-slate-400 font-extrabold uppercase text-[10px] bg-slate-50/70">
                                <th class="py-3.5 px-4 rounded-tl-xl w-10">
                                    <input type="checkbox" id="selectAll" onclick="toggleSelectAll(this)" class="w-4 h-4 text-[#0052cc] rounded border-slate-300 cursor-pointer">
                                </th>
                                <th class="py-3.5 px-4">No. Tiket</th>
                                <th class="py-3.5 px-4">Bukti</th>
                                <th class="py-3.5 px-4">Segmen / Lokasi Area</th>
                                <th class="py-3.5 px-4">Potensi & Dampak FO</th>
                                <th class="py-3.5 px-4">Teknisi & Waktu</th>
                                <th class="py-3.5 px-4">Status</th>
                                <th class="py-3.5 px-4 text-center rounded-tr-xl">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
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
                                    $kondisi_class = ($kondisi_lower == 'baik' || strpos($kondisi_lower, 'normal') !== false) ? "text-emerald-600 font-bold" : "text-rose-600 font-bold";

                                    $foto_path = "../assets/img/uploads/" . $row['foto'];
                                    $ada_foto = (!empty($row['foto']) && file_exists($foto_path));
                                ?>
                                    <tr class="hover:bg-slate-50/90 transition">
                                        <td class="py-3.5 px-4">
                                            <input type="checkbox" name="laporan_ids[]" value="<?php echo $row['id']; ?>" class="item-check w-4 h-4 text-[#0052cc] rounded border-slate-300 cursor-pointer">
                                        </td>
                                        <td class="py-3.5 px-4 font-extrabold text-slate-700 whitespace-nowrap">
                                            <?php echo $nomor_tiket; ?>
                                        </td>
                                        <td class="py-3.5 px-4">
                                            <?php if ($ada_foto): ?>
                                                <img src="<?php echo $foto_path; ?>" alt="Bukti" class="w-12 h-12 object-cover rounded-xl border border-slate-200 shadow-2xs">
                                            <?php else: ?>
                                                <div class="w-12 h-12 rounded-xl bg-slate-100 border border-slate-200 flex items-center justify-center text-slate-400">
                                                    <span class="material-symbols-outlined text-[18px]">image_not_supported</span>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td class="py-3.5 px-4 max-w-xs">
                                            <p class="font-extrabold text-[#0d1c30] truncate"><?php echo htmlspecialchars($row['lokasi_area']); ?></p>
                                            <p class="text-[11px] text-slate-400 flex items-center gap-1 mt-0.5">
                                                <span class="material-symbols-outlined text-[13px] text-rose-500">location_on</span>
                                                <?php echo htmlspecialchars($row['latitude'] . ', ' . $row['longitude']); ?>
                                            </p>
                                        </td>
                                        <td class="py-3.5 px-4">
                                            <p class="font-bold text-[#0052cc]"><?php echo htmlspecialchars($potensi); ?></p>
                                            <p class="text-[11px] mt-0.5 <?php echo $kondisi_class; ?>"><?php echo htmlspecialchars($kondisi_fisik); ?></p>
                                        </td>
                                        <td class="py-3.5 px-4 whitespace-nowrap">
                                            <p class="font-bold text-slate-800"><?php echo htmlspecialchars($row['username']); ?> <span class="text-slate-400 font-normal">(NIP-<?php echo htmlspecialchars($row['nip'] ?? '44821'); ?>)</span></p>
                                            <p class="text-[11px] text-slate-400 mt-0.5"><?php echo date('d M Y, H:i', strtotime($row['tanggal'])); ?> WITA</p>
                                        </td>
                                        <td class="py-3.5 px-4 whitespace-nowrap">
                                            <span class="px-3 py-1 rounded-full text-[10px] font-extrabold border <?php echo $status_class; ?>">
                                                <?php echo htmlspecialchars($status_laporan); ?>
                                            </span>
                                        </td>
                                        <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                            <a href="detail_laporan_admin.php?id=<?php echo $row['id']; ?>" class="inline-flex items-center gap-1 bg-[#0052cc]/10 hover:bg-[#0052cc] text-[#0052cc] hover:text-white font-bold px-3.5 py-2 rounded-xl transition">
                                                <span class="material-symbols-outlined text-[16px]">visibility</span> Verifikasi
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else : ?>
                                <tr>
                                    <td colspan="8" class="py-12 text-center">
                                        <span class="material-symbols-outlined text-slate-300 text-[42px]">assignment_late</span>
                                        <p class="text-xs font-bold text-slate-400 mt-2">Tidak ada laporan patroli yang sesuai dengan filter.</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </form>

        </main>
    </div>

    <script>
        function toggleSelectAll(source) {
            let checkboxes = document.getElementsByClassName('item-check');
            for (let i = 0; i < checkboxes.length; i++) {
                checkboxes[i].checked = source.checked;
            }
        }

        let toastAdminTimeout = null;
        function tampilkanToastAdmin(pesan, tipe = 'warning') {
            const toast = document.getElementById('toastAdmin');
            const textEl = document.getElementById('toastAdminText');
            const iconEl = document.getElementById('toastAdminIcon');

            textEl.textContent = pesan;
            if (tipe === 'warning') {
                iconEl.textContent = 'warning';
                iconEl.className = 'material-symbols-outlined text-amber-400 text-[20px]';
            } else {
                iconEl.textContent = 'info';
                iconEl.className = 'material-symbols-outlined text-blue-400 text-[20px]';
            }

            toast.classList.remove('translate-y-[-100px]', 'opacity-0', 'pointer-events-none');
            toast.classList.add('translate-y-0', 'opacity-100');

            if (toastAdminTimeout) clearTimeout(toastAdminTimeout);
            toastAdminTimeout = setTimeout(() => {
                toast.classList.remove('translate-y-0', 'opacity-100');
                toast.classList.add('translate-y-[-100px]', 'opacity-0', 'pointer-events-none');
            }, 3500);
        }

        function submitAksi(jenis) {
            const checked = document.querySelectorAll('.item-check:checked');
            if (checked.length === 0) {
                tampilkanToastAdmin('Silakan centang minimal satu laporan terlebih dahulu.', 'warning');
                return;
            }
            const form = document.getElementById('formAksi');
            form.action = (jenis === 'pdf') ? '../process/cetak_pdf.php' : '../process/cetak_excel.php';
            form.target = '_blank';
            form.submit();
        }
    </script>
</body>
</html>