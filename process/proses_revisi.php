<?php
session_start();
include "../config/koneksi.php";

// Pastikan admin sudah login
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

if (isset($_POST['id_laporan'])) {
    $id_laporan = $_POST['id_laporan'];
    $catatan_revisi = mysqli_real_escape_string($conn, $_POST['catatan']);
    
    // 1. OTOMATIS AMBIL USERNAME ADMIN YANG SEDANG LOGIN SAAT INI
    $admin_pengaju = $_SESSION['username'];

    // 2. SIMPAN OTOMATIS KE DATABASE (status, catatan, dan nama adminnya)
    $query = "UPDATE laporan_patroli SET 
                status_laporan = 'Perlu Revisi', 
                catatan = CONCAT(catatan, ' [Catatan Supervisor: ', '$catatan_revisi', ']'), 
                admin_revisi = '$admin_pengaju' 
              WHERE id = '$id_laporan'";
              
    $execute = mysqli_query($conn, $query);

    if ($execute) {
        // Redirect kembali ke halaman laporan admin
        header("Location: laporan_admin.php?pesan=berhasil_revisi");
        exit();
    } else {
        echo "Gagal: " . mysqli_error($conn);
    }
}
?>