<?php
session_start();
include "../config/koneksi.php";

// Validasi Akses Admin
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
    <title>Manajemen Akun - Lintasarta Samarinda</title>
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
    </style>
</head>
<body class="text-[#0d1c30] pb-28">

    <!-- HEADER KENDALI PUSAT YANG RAPI -->
    <div class="bg-gradient-to-br from-[#0b192c] via-[#003c99] to-[#0052cc] text-white pt-6 pb-16 px-4">
        <div class="max-w-md mx-auto flex items-center justify-between">
            <a href="dashboard_admin.php" class="w-10 h-10 rounded-full bg-white/15 backdrop-blur-md border border-white/20 flex items-center justify-center text-white active:scale-95 transition">
                <span class="material-symbols-outlined text-[22px]">arrow_back</span>
            </a>
            <h1 class="text-xs font-extrabold tracking-wider uppercase text-blue-100">Manajemen Pengguna</h1>
            <div class="w-10"></div>
        </div>
    </div>

    <!-- KONTEN UTAMA DENGAN JARAK AMAN -->
    <main class="max-w-md mx-auto px-4 -mt-8 space-y-4">
        
        <?php if (!empty($pesan)): ?>
            <div class="p-3.5 rounded-2xl text-xs font-bold text-center shadow-md <?php echo ($tipe_pesan == 'success') ? 'bg-emerald-100 text-emerald-800 border border-emerald-300' : 'bg-rose-100 text-rose-800 border border-rose-300'; ?>">
                <?php echo $pesan; ?>
            </div>
        <?php endif; ?>

        <!-- KARTU INFORMASI UTAMA & TOMBOL TAMBAH -->
        <div class="bg-white rounded-3xl p-4 shadow-xl shadow-slate-200/60 border border-slate-100 flex items-center justify-between">
            <div>
                <h3 class="font-extrabold text-sm text-[#0d1c30]">Daftar Akun Terdaftar</h3>
                <p class="text-[11px] text-slate-400">Kelola akses teknisi dan admin sistem</p>
            </div>
            <button onclick="toggleModal('addModal', true)" class="bg-[#0052cc] hover:bg-blue-700 text-white font-bold text-xs px-3.5 py-2.5 rounded-2xl shadow-md transition flex items-center gap-1 active:scale-95">
                <span class="material-symbols-outlined text-[16px]">person_add</span> Tambah
            </button>
        </div>

        <!-- DAFTAR LIST USER -->
        <div class="space-y-3">
            <?php if (mysqli_num_rows($query_daftar_user) > 0): ?>
                <?php while ($row = mysqli_fetch_assoc($query_daftar_user)): ?>
                    <div class="bg-white rounded-2xl p-4 shadow-sm border border-slate-100 space-y-3">
                        <div class="flex justify-between items-start">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-2xl bg-blue-50 text-[#0052cc] flex items-center justify-center border border-blue-100">
                                    <span class="material-symbols-outlined text-[20px]"><?php echo ($row['role'] == 'admin' || $row['role'] == 'pimpinan') ? 'admin_panel_settings' : 'engineering'; ?></span>
                                </div>
                                <div>
                                    <h4 class="font-extrabold text-xs text-[#0d1c30]"><?php echo htmlspecialchars($row['username']); ?></h4>
                                    <p class="text-[10px] text-slate-400">NIP: <strong class="text-slate-600"><?php echo htmlspecialchars($row['nip'] ?? '-'); ?></strong></p>
                                </div>
                            </div>
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase border <?php echo ($row['role'] == 'admin') ? 'bg-indigo-50 text-indigo-600 border-indigo-100' : 'bg-blue-50 text-[#0052cc] border-blue-100'; ?>">
                                <?php echo htmlspecialchars($row['role']); ?>
                            </span>
                        </div>

                        <!-- Tombol Aksi Edit & Hapus -->
                        <div class="pt-2 border-t border-slate-100 flex gap-2">
                            <button onclick="openEditModal(<?php echo $row['id']; ?>, '<?php echo htmlspecialchars($row['nip']); ?>', '<?php echo htmlspecialchars($row['username']); ?>', '<?php echo htmlspecialchars($row['role']); ?>')" class="flex-1 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition flex items-center justify-center gap-1">
                                <span class="material-symbols-outlined text-[16px]">edit</span> Edit
                            </button>
                            <a href="manajemen_user.php?hapus=<?php echo $row['id']; ?>" onclick="return confirm('Apakah Anda yakin ingin menghapus akun <?php echo htmlspecialchars($row['username']); ?>?')" class="flex-1 py-2 bg-rose-50 hover:bg-rose-100 text-rose-600 font-bold text-xs rounded-xl transition flex items-center justify-center gap-1">
                                <span class="material-symbols-outlined text-[16px]">delete</span> Hapus
                            </a>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="bg-white p-6 rounded-2xl border border-slate-100 text-center text-xs text-slate-400">
                    Belum ada data pengguna.
                </div>
            <?php endif; ?>
        </div>

    </main>

    <!-- MODAL TAMBAH USER -->
    <div id="addModal" class="fixed inset-0 bg-slate-950/50 backdrop-blur-xs z-50 flex items-center justify-center p-4 hidden">
        <div class="bg-white rounded-3xl p-6 w-full max-w-sm space-y-4 shadow-2xl animate-entry">
            <div class="flex items-center justify-between border-b pb-3">
                <h3 class="font-extrabold text-sm text-[#0d1c30]">Tambah Akun Baru</h3>
                <button onclick="toggleModal('addModal', false)" class="text-slate-400 hover:text-slate-600"><span class="material-symbols-outlined">close</span></button>
            </div>
            <form action="" method="POST" class="space-y-3">
                <div class="space-y-1">
                    <label class="text-[11px] font-bold text-slate-700">NIP <span class="text-rose-500">*</span></label>
                    <input type="text" name="nip" required placeholder="Contoh: 105" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-xs font-semibold focus:outline-none focus:border-[#0052cc]">
                </div>
                <div class="space-y-1">
                    <label class="text-[11px] font-bold text-slate-700">Username <span class="text-rose-500">*</span></label>
                    <input type="text" name="username" required placeholder="Contoh: RIZKY" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-xs font-semibold focus:outline-none focus:border-[#0052cc]">
                </div>
                <div class="space-y-1">
                    <label class="text-[11px] font-bold text-slate-700">Hak Akses (Role) <span class="text-rose-500">*</span></label>
                    <select name="role" required class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-xs font-semibold focus:outline-none focus:border-[#0052cc]">
                        <option value="teknisi">Teknisi Lapangan</option>
                        <option value="admin">Admin NOC</option>
                        <option value="koordinator">Koordinator OSP</option>
                        <option value="engginer">Engineer</option>
                        <option value="pimpinan">Pimpinan</option>
                    </select>
                </div>
                <div class="space-y-1">
                    <label class="text-[11px] font-bold text-slate-700">Password Awal <span class="text-rose-500">*</span></label>
                    <input type="text" name="password" required placeholder="Password sementara" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-xs font-semibold focus:outline-none focus:border-[#0052cc]">
                </div>
                <div class="pt-2 flex gap-2">
                    <button type="button" onclick="toggleModal('addModal', false)" class="flex-1 py-2 bg-slate-100 text-slate-600 font-bold rounded-xl text-xs">Batal</button>
                    <button type="submit" name="tambah_user" class="flex-1 py-2 bg-[#0052cc] text-white font-bold rounded-xl text-xs shadow-md">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL EDIT USER -->
    <div id="editModal" class="fixed inset-0 bg-slate-950/50 backdrop-blur-xs z-50 flex items-center justify-center p-4 hidden">
        <div class="bg-white rounded-3xl p-6 w-full max-w-sm space-y-4 shadow-2xl animate-entry">
            <div class="flex items-center justify-between border-b pb-3">
                <h3 class="font-extrabold text-sm text-[#0d1c30]">Edit Data Akun</h3>
                <button onclick="toggleModal('editModal', false)" class="text-slate-400 hover:text-slate-600"><span class="material-symbols-outlined">close</span></button>
            </div>
            <form action="" method="POST" class="space-y-3">
                <input type="hidden" name="id" id="edit_id">
                <div class="space-y-1">
                    <label class="text-[11px] font-bold text-slate-700">NIP <span class="text-rose-500">*</span></label>
                    <input type="text" name="nip" id="edit_nip" required class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-xs font-semibold focus:outline-none focus:border-[#0052cc]">
                </div>
                <div class="space-y-1">
                    <label class="text-[11px] font-bold text-slate-700">Username <span class="text-rose-500">*</span></label>
                    <input type="text" name="username" id="edit_username" required class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-xs font-semibold focus:outline-none focus:border-[#0052cc]">
                </div>
                <div class="space-y-1">
                    <label class="text-[11px] font-bold text-slate-700">Hak Akses (Role) <span class="text-rose-500">*</span></label>
                    <select name="role" id="edit_role" required class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-xs font-semibold focus:outline-none focus:border-[#0052cc]">
                        <option value="teknisi">Teknisi Lapangan</option>
                        <option value="admin">Admin</option>
                        <option value="engginer">Engineer</option>
                        <option value="pimpinan">Pimpinan</option>
                    </select>
                </div>
                <div class="space-y-1">
                    <label class="text-[11px] font-bold text-slate-700">Password Baru (Opsional)</label>
                    <input type="text" name="password" placeholder="Kosongkan jika tidak diubah" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-xs font-semibold focus:outline-none focus:border-[#0052cc]">
                </div>
                <div class="pt-2 flex gap-2">
                    <button type="button" onclick="toggleModal('editModal', false)" class="flex-1 py-2 bg-slate-100 text-slate-600 font-bold rounded-xl text-xs">Batal</button>
                    <button type="submit" name="edit_user" class="flex-1 py-2 bg-[#0052cc] text-white font-bold rounded-xl text-xs shadow-md">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- BOTTOM NAVBAR ADMIN -->
    <nav class="fixed bottom-0 left-0 right-0 bg-white/95 backdrop-blur-md border-t border-slate-200 py-2.5 px-6 flex justify-around items-center z-30 max-w-md mx-auto shadow-lg">
        <a href="dashboard_admin.php" class="flex flex-col items-center text-slate-400 hover:text-slate-600"><span class="material-symbols-outlined text-[22px]">grid_view</span><span class="text-[10px] mt-0.5">Beranda</span></a>
        <a href="pemantauan_admin.php" class="flex flex-col items-center text-slate-400 hover:text-slate-600"><span class="material-symbols-outlined text-[22px]">map</span><span class="text-[10px] mt-0.5">Peta Jalur</span></a>
        <a href="laporan_admin.php" class="flex flex-col items-center text-slate-400 hover:text-slate-600"><span class="material-symbols-outlined text-[22px]">assignment</span><span class="text-[10px] mt-0.5">Laporan</span></a>
        <a href="profil_admin.php" class="flex flex-col items-center text-slate-400 hover:text-slate-600"><span class="material-symbols-outlined text-[22px]">person</span><span class="text-[10px] mt-0.5">Profil</span></a>
    </nav>

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