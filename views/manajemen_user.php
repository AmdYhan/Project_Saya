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
$pesan = "";
$tipe_pesan = "";

// 1. PROSES TAMBAH USER BARU
if (isset($_POST['tambah_user'])) {
    $nip      = mysqli_real_escape_string($conn, trim($_POST['nip']));
    $new_user = mysqli_real_escape_string($conn, trim($_POST['username']));
    $password = mysqli_real_escape_string($conn, trim($_POST['password']));
    $new_role = mysqli_real_escape_string($conn, trim($_POST['role']));

    $cek = mysqli_query($conn, "SELECT * FROM users WHERE username='$new_user' OR nip='$nip'");
    if (mysqli_num_rows($cek) > 0) {
        $pesan = "Gagal: Username atau NIP sudah terdaftar di sistem!";
        $tipe_pesan = "error";
    } else {
        $query = mysqli_query($conn, "INSERT INTO users (nip, username, password, role) VALUES ('$nip', '$new_user', '$password', '$new_role')");
        if ($query) {
            $pesan = "Akun baru berhasil ditambahkan!";
            $tipe_pesan = "success";
        } else {
            $pesan = "Gagal menyimpan ke database: " . mysqli_error($conn);
            $tipe_pesan = "error";
        }
    }
}

// 2. PROSES EDIT USER
if (isset($_POST['edit_user'])) {
    $id_user  = intval($_POST['id']);
    $nip      = mysqli_real_escape_string($conn, trim($_POST['nip']));
    $new_user = mysqli_real_escape_string($conn, trim($_POST['username']));
    $new_role = mysqli_real_escape_string($conn, trim($_POST['role']));
    
    if (!empty($_POST['password'])) {
        $password = mysqli_real_escape_string($conn, trim($_POST['password']));
        $query_update = mysqli_query($conn, "UPDATE users SET nip='$nip', username='$new_user', password='$password', role='$new_role' WHERE id='$id_user'");
    } else {
        $query_update = mysqli_query($conn, "UPDATE users SET nip='$nip', username='$new_user', role='$new_role' WHERE id='$id_user'");
    }

    if ($query_update) {
        $pesan = "Data akun berhasil diperbarui!";
        $tipe_pesan = "success";
    } else {
        $pesan = "Gagal memperbarui data: " . mysqli_error($conn);
        $tipe_pesan = "error";
    }
}

// 3. PROSES HAPUS USER
if (isset($_GET['hapus'])) {
    $id_hapus = intval($_GET['hapus']);
    $q_cek_self = mysqli_query($conn, "SELECT username FROM users WHERE id='$id_hapus'");
    $d_self = mysqli_fetch_assoc($q_cek_self);
    
    if ($d_self['username'] === $username) {
        $pesan = "Peringatan: Anda tidak dapat menghapus akun yang sedang digunakan saat ini!";
        $tipe_pesan = "error";
    } else {
        $query_hapus = mysqli_query($conn, "DELETE FROM users WHERE id='$id_hapus'");
        if ($query_hapus) {
            $pesan = "Akun berhasil dihapus dari sistem!";
            $tipe_pesan = "success";
        } else {
            $pesan = "Gagal menghapus akun!";
            $tipe_pesan = "error";
        }
    }
}

$query_daftar_user = mysqli_query($conn, "SELECT * FROM users ORDER BY id DESC");
$current_page = 'manajemen_user';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Pengguna - Lintasarta Samarinda</title>
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
                <a href="manajemen_user.php" class="flex items-center gap-3 px-4 py-3 rounded-xl bg-[#0052cc] text-white shadow-md">
                    <span class="material-symbols-outlined text-[20px]" style="font-variation-settings: 'FILL' 1;">group</span>
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
                <h2 class="font-extrabold text-base text-[#0d1c30]">Manajemen Pengguna Sistem</h2>
                <p class="text-xs text-slate-400">Pengelolaan Akun Teknisi Lapangan, Admin, dan Pimpinan</p>
            </div>
            
            <button onclick="toggleModal('addModal', true)" class="bg-[#0052cc] hover:bg-blue-700 text-white font-bold text-xs px-4 py-2.5 rounded-xl shadow-sm transition flex items-center gap-1.5 cursor-pointer">
                <span class="material-symbols-outlined text-[18px]">person_add</span> Tambah Akun Baru
            </button>
        </header>

        <!-- KONTEN SCROLLABLE -->
        <main class="flex-1 overflow-y-auto p-6 space-y-6 animate-main">
            
            <?php if (!empty($pesan)): ?>
                <div class="p-4 rounded-2xl text-xs font-bold shadow-sm <?php echo ($tipe_pesan == 'success') ? 'bg-emerald-100 text-emerald-800 border border-emerald-300' : 'bg-rose-100 text-rose-800 border border-rose-300'; ?>">
                    <?php echo $pesan; ?>
                </div>
            <?php endif; ?>

            <!-- KARTU UTAMA DAFTAR USER -->
            <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-200/80 space-y-4">
                <div class="flex justify-between items-center pb-4 border-b border-slate-100">
                    <div>
                        <h3 class="font-extrabold text-sm text-[#0d1c30]">Daftar Seluruh Akun Terdaftar</h3>
                        <p class="text-xs text-slate-400">Total <?php echo mysqli_num_rows($query_daftar_user); ?> akun aktif dalam database sistem</p>
                    </div>
                </div>

                <!-- TABEL ENTERPRISE REDESIGN -->
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="text-slate-400 font-extrabold uppercase text-[10px] bg-slate-50/80 tracking-wider">
                                <th class="py-3.5 px-4 rounded-l-2xl w-16">ID</th>
                                <th class="py-3.5 px-4">NIP</th>
                                <th class="py-3.5 px-4">Pengguna</th>
                                <th class="py-3.5 px-4">Hak Akses (Role)</th>
                                <th class="py-3.5 px-4 text-center rounded-r-2xl">Aksi Cepat</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php if (mysqli_num_rows($query_daftar_user) > 0): ?>
                                <?php while ($row = mysqli_fetch_assoc($query_daftar_user)): ?>
                                    <?php 
                                        $r_lower = strtolower($row['role']);
                                        if ($r_lower == 'admin' || $r_lower == 'pimpinan') {
                                            $role_class = "bg-indigo-50 text-indigo-700 border-indigo-200";
                                            $icon_bg = "bg-indigo-100 text-indigo-600";
                                        } elseif ($r_lower == 'koordinator' || $r_lower == 'engginer') {
                                            $role_class = "bg-blue-50 text-blue-700 border-blue-200";
                                            $icon_bg = "bg-blue-100 text-blue-600";
                                        } else {
                                            $role_class = "bg-emerald-50 text-emerald-700 border-emerald-200";
                                            $icon_bg = "bg-emerald-100 text-emerald-600";
                                        }
                                    ?>
                                    <tr class="hover:bg-slate-50/80 transition group">
                                        <td class="py-3 px-4 font-extrabold text-slate-400">#<?php echo $row['id']; ?></td>
                                        <td class="py-3 px-4 font-bold text-slate-700"><?php echo htmlspecialchars($row['nip'] ?? '-'); ?></td>
                                        <td class="py-3 px-4">
                                            <div class="flex items-center gap-3">
                                                <div class="w-9 h-9 rounded-xl <?php echo $icon_bg; ?> flex items-center justify-center font-bold shadow-2xs">
                                                    <span class="material-symbols-outlined text-[18px]">person</span>
                                                </div>
                                                <div>
                                                    <p class="font-extrabold text-[#0d1c30]"><?php echo htmlspecialchars($row['username']); ?></p>
                                                    <p class="text-[10px] text-slate-400">NOC Lintasarta Samarinda</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="py-3 px-4">
                                            <span class="px-3 py-1 rounded-full text-[10px] font-extrabold uppercase border <?php echo $role_class; ?> tracking-wider inline-block">
                                                <?php echo htmlspecialchars($row['role']); ?>
                                            </span>
                                        </td>
                                        <td class="py-3 px-4 text-center">
                                            <div class="inline-flex items-center gap-1 bg-slate-100/80 p-1 rounded-xl border border-slate-200/60 shadow-2xs">
                                                <button onclick="openEditModal(<?php echo $row['id']; ?>, '<?php echo htmlspecialchars($row['nip']); ?>', '<?php echo htmlspecialchars($row['username']); ?>', '<?php echo htmlspecialchars($row['role']); ?>')" 
                                                        class="px-2.5 py-1.5 rounded-lg bg-white hover:bg-blue-50 text-slate-700 hover:text-[#0052cc] font-bold transition flex items-center gap-1 shadow-2xs cursor-pointer" title="Edit Akun">
                                                    <span class="material-symbols-outlined text-[15px]">edit</span> Edit
                                                </button>
                                                <a href="manajemen_user.php?hapus=<?php echo $row['id']; ?>" 
                                                   onclick="return confirm('Apakah Anda yakin ingin menghapus akun <?php echo htmlspecialchars($row['username']); ?>?')" 
                                                   class="px-2.5 py-1.5 rounded-lg bg-white hover:bg-rose-50 text-slate-700 hover:text-rose-600 font-bold transition flex items-center gap-1 shadow-2xs cursor-pointer" title="Hapus Akun">
                                                    <span class="material-symbols-outlined text-[15px]">delete</span> Hapus
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="py-12 text-center text-slate-400 font-medium">Belum ada data pengguna terdaftar.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </main>
    </div>

    <!-- MODAL TAMBAH USER -->
    <div id="addModal" class="fixed inset-0 bg-slate-950/50 backdrop-blur-xs z-50 flex items-center justify-center p-4 hidden">
        <div class="bg-white rounded-3xl p-6 w-full max-w-md space-y-4 shadow-2xl animate-main">
            <div class="flex items-center justify-between border-b pb-3">
                <h3 class="font-extrabold text-sm text-[#0d1c30]">Tambah Akun Baru</h3>
                <button onclick="toggleModal('addModal', false)" class="text-slate-400 hover:text-slate-600 cursor-pointer"><span class="material-symbols-outlined">close</span></button>
            </div>
            <form action="" method="POST" class="space-y-3.5">
                <div class="space-y-1">
                    <label class="text-[11px] font-bold text-slate-700">NIP <span class="text-rose-500">*</span></label>
                    <input type="text" name="nip" required placeholder="Contoh: 105" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3 text-xs font-semibold focus:outline-none focus:border-[#0052cc]">
                </div>
                <div class="space-y-1">
                    <label class="text-[11px] font-bold text-slate-700">Username <span class="text-rose-500">*</span></label>
                    <input type="text" name="username" required placeholder="Contoh: RIZKY" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3 text-xs font-semibold focus:outline-none focus:border-[#0052cc]">
                </div>
                <div class="space-y-1">
                    <label class="text-[11px] font-bold text-slate-700">Hak Akses (Role) <span class="text-rose-500">*</span></label>
                    <select name="role" required class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3 text-xs font-semibold focus:outline-none focus:border-[#0052cc]">
                        <option value="teknisi">Teknisi Lapangan</option>
                        <option value="admin">Admin NOC</option>
                        <option value="koordinator">Koordinator OSP</option>
                        <option value="engginer">Engineer</option>
                        <option value="pimpinan">Pimpinan</option>
                    </select>
                </div>
                <div class="space-y-1">
                    <label class="text-[11px] font-bold text-slate-700">Password Awal <span class="text-rose-500">*</span></label>
                    <input type="text" name="password" required placeholder="Password sementara" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3 text-xs font-semibold focus:outline-none focus:border-[#0052cc]">
                </div>
                <div class="pt-2 flex gap-2">
                    <button type="button" onclick="toggleModal('addModal', false)" class="flex-1 py-2.5 bg-slate-100 text-slate-600 font-bold rounded-xl text-xs cursor-pointer">Batal</button>
                    <button type="submit" name="tambah_user" class="flex-1 py-2.5 bg-[#0052cc] text-white font-bold rounded-xl text-xs shadow-md cursor-pointer">Simpan Akun</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL EDIT USER -->
    <div id="editModal" class="fixed inset-0 bg-slate-950/50 backdrop-blur-xs z-50 flex items-center justify-center p-4 hidden">
        <div class="bg-white rounded-3xl p-6 w-full max-w-md space-y-4 shadow-2xl animate-main">
            <div class="flex items-center justify-between border-b pb-3">
                <h3 class="font-extrabold text-sm text-[#0d1c30]">Edit Data Akun</h3>
                <button onclick="toggleModal('editModal', false)" class="text-slate-400 hover:text-slate-600 cursor-pointer"><span class="material-symbols-outlined">close</span></button>
            </div>
            <form action="" method="POST" class="space-y-3.5">
                <input type="hidden" name="id" id="edit_id">
                <div class="space-y-1">
                    <label class="text-[11px] font-bold text-slate-700">NIP <span class="text-rose-500">*</span></label>
                    <input type="text" name="nip" id="edit_nip" required class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3 text-xs font-semibold focus:outline-none focus:border-[#0052cc]">
                </div>
                <div class="space-y-1">
                    <label class="text-[11px] font-bold text-slate-700">Username <span class="text-rose-500">*</span></label>
                    <input type="text" name="username" id="edit_username" required class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3 text-xs font-semibold focus:outline-none focus:border-[#0052cc]">
                </div>
                <div class="space-y-1">
                    <label class="text-[11px] font-bold text-slate-700">Hak Akses (Role) <span class="text-rose-500">*</span></label>
                    <select name="role" id="edit_role" required class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3 text-xs font-semibold focus:outline-none focus:border-[#0052cc]">
                        <option value="teknisi">Teknisi Lapangan</option>
                        <option value="admin">Admin</option>
                        <option value="koordinator">Koordinator OSP</option>
                        <option value="engginer">Engineer</option>
                        <option value="pimpinan">Pimpinan</option>
                    </select>
                </div>
                <div class="space-y-1">
                    <label class="text-[11px] font-bold text-slate-700">Password Baru (Opsional)</label>
                    <input type="text" name="password" placeholder="Kosongkan jika tidak diubah" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3 text-xs font-semibold focus:outline-none focus:border-[#0052cc]">
                </div>
                <div class="pt-2 flex gap-2">
                    <button type="button" onclick="toggleModal('editModal', false)" class="flex-1 py-2.5 bg-slate-100 text-slate-600 font-bold rounded-xl text-xs cursor-pointer">Batal</button>
                    <button type="submit" name="edit_user" class="flex-1 py-2.5 bg-[#0052cc] text-white font-bold rounded-xl text-xs shadow-md cursor-pointer">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function toggleModal(modalId, show) {
            const modal = document.getElementById(modalId);
            if (show) {
                modal.classList.remove('hidden');
            } else {
                modal.classList.add('hidden');
            }
        }

        function openEditModal(id, nip, username, role) {
            document.getElementById('edit_id').value = id;
            document.getElementById('edit_nip').value = nip;
            document.getElementById('edit_username').value = username;
            document.getElementById('edit_role').value = role;
            toggleModal('editModal', true);
        }
    </script>
</body>
</html>