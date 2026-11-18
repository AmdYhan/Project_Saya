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

$query_peta = mysqli_query($conn, "SELECT l.*, u.nip FROM laporan_patroli l LEFT JOIN users u ON l.username = u.username WHERE l.latitude != '' AND l.longitude != '' ORDER BY l.tanggal DESC");

$markers = [];
$latest_reports = [];
$q_kritis = 0;
$q_waspada = 0;
$q_aman = 0;

while ($row = mysqli_fetch_assoc($query_peta)) {
    if (is_numeric($row['latitude']) && is_numeric($row['longitude'])) {
        $teks_gabungan = strtolower(($row['potensi_ancaman'] ?? '') . ' ' . ($row['kondisi_fisik'] ?? '') . ' ' . ($row['catatan'] ?? ''));
        
        // Logika Kategorisasi Sesuai Standar Data
        if (preg_match('/(patah|rebah|longsor|putus|darurat)/', $teks_gabungan) || 
            (strpos($teks_gabungan, 'rusak') !== false && (strpos($teks_gabungan, 'kabel') !== false || strpos($teks_gabungan, 'tiang') !== false))) {
            $q_kritis++;
            $row['kategori_filter'] = 'kritis';
        } elseif (strpos($teks_gabungan, 'baik') !== false || strpos($teks_gabungan, 'normal') !== false || $row['status_laporan'] === 'Disetujui') {
            $q_aman++;
            $row['kategori_filter'] = 'aman';
        } else {
            $q_waspada++;
            $row['kategori_filter'] = 'waspada';
        }

        $markers[] = $row;
        if (count($latest_reports) < 5) {
            $latest_reports[] = $row;
        }
    }
}

$total_tkp = count($markers);
$current_page = 'peta';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Peta GIS Pemantauan Admin - Lintasarta Samarinda</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />
    
    <!-- Leaflet CSS & JS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f1f5f9; }
        #map { height: 520px; width: 100%; border-radius: 1.25rem; z-index: 1; }
        .leaflet-control-attribution { display: none !important; }
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
                <a href="pemantauan_admin.php" class="flex items-center gap-3 px-4 py-3 rounded-xl bg-[#0052cc] text-white shadow-md">
                    <span class="material-symbols-outlined text-[20px]" style="font-variation-settings: 'FILL' 1;">map</span>
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
                <h2 class="font-extrabold text-base text-[#0d1c30]">Peta Pemantauan GIS</h2>
                <p class="text-xs text-slate-400">Visualisasi Real-Time Koridor Serat Optik Mahakam</p>
            </div>
            
            <!-- TOMBOL FILTER & TEMA DI HEADER (Kini Termasuk AMAN) -->
            <div class="flex items-center gap-3">
                <div class="flex bg-slate-100 p-1 rounded-xl text-xs font-bold">
                    <button onclick="filterMarker('semua')" id="btn-semua" class="px-3 py-1.5 rounded-lg bg-[#0052cc] text-white shadow-xs transition cursor-pointer">Semua (<?php echo $total_tkp; ?>)</button>
                    <button onclick="filterMarker('aman')" id="btn-aman" class="px-3 py-1.5 rounded-lg text-slate-600 hover:text-slate-900 transition flex items-center gap-1 cursor-pointer"><span class="w-2 h-2 rounded-full bg-emerald-500"></span> Aman (<?php echo $q_aman; ?>)</button>
                    <button onclick="filterMarker('waspada')" id="btn-waspada" class="px-3 py-1.5 rounded-lg text-slate-600 hover:text-slate-900 transition flex items-center gap-1 cursor-pointer"><span class="w-2 h-2 rounded-full bg-amber-500"></span> Waspada (<?php echo $q_waspada; ?>)</button>
                    <button onclick="filterMarker('kritis')" id="btn-kritis" class="px-3 py-1.5 rounded-lg text-slate-600 hover:text-slate-900 transition flex items-center gap-1 cursor-pointer"><span class="w-2 h-2 rounded-full bg-rose-500"></span> Kritis (<?php echo $q_kritis; ?>)</button>
                </div>

                <select id="mapThemeSelect" onchange="ubahTemaPeta(this.value)" class="bg-slate-100 border border-slate-200 text-slate-700 text-xs font-bold px-3 py-2 rounded-xl focus:outline-none cursor-pointer">
                    <option value="satelit">🛰️ Peta Satelit</option>
                    <option value="standard">🗺️ Peta Standar</option>
                    <option value="topo">⛰️ Peta Topo</option>
                </select>
            </div>
        </header>

        <!-- KONTEN PETA & PANEL SAMPING -->
        <main class="flex-1 p-6 grid grid-cols-1 lg:grid-cols-12 gap-6 overflow-y-auto animate-main">
            
            <!-- PETA UTAMA (8 Kolom) -->
            <div class="lg:col-span-8 bg-white rounded-3xl p-4 shadow-sm border border-slate-200/80 space-y-3">
                <div class="flex justify-between items-center px-1">
                    <h3 class="font-extrabold text-sm text-[#0d1c30] flex items-center gap-2">
                        <span class="material-symbols-outlined text-[18px] text-[#0052cc]">map</span> Titik Koordinat Koridor OSP
                    </h3>
                    <span class="text-[11px] text-slate-400 font-semibold">Koordinat Pusat: -0.5021, 117.1536</span>
                </div>
                <div id="map"></div>
            </div>

            <!-- PANEL SAMPING: TITIK PATROLI TERBARU (4 Kolom) -->
            <div class="lg:col-span-4 bg-white rounded-3xl p-5 shadow-sm border border-slate-200/80 flex flex-col space-y-3">
                <div class="flex justify-between items-center pb-2 border-b border-slate-100">
                    <h3 class="font-extrabold text-sm text-[#0d1c30]">Titik Patroli Terbaru</h3>
                    <span class="text-xs text-slate-400 font-semibold"><?php echo count($latest_reports); ?> Lokasi Aktif</span>
                </div>

                <div class="space-y-2.5 overflow-y-auto max-h-[480px] pr-1">
                    <?php if (count($latest_reports) > 0): ?>
                        <?php foreach ($latest_reports as $row): ?>
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
                            <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-100 space-y-2 hover:border-blue-200 transition">
                                <div class="flex justify-between items-center">
                                    <span class="text-[11px] font-extrabold text-[#0052cc]"><?php echo $nomor_tiket; ?></span>
                                    <span class="text-[9px] font-extrabold px-2 py-0.5 rounded-full border <?php echo $status_class; ?>">
                                        <?php echo htmlspecialchars($status_laporan); ?>
                                    </span>
                                </div>
                                <div>
                                    <h4 class="font-extrabold text-xs text-[#0d1c30] truncate"><?php echo htmlspecialchars($row['lokasi_area']); ?></h4>
                                    <p class="text-[11px] text-slate-500 mt-0.5 truncate">Ancaman: <b><?php echo htmlspecialchars($row['potensi_ancaman'] ?: 'Kritis'); ?></b></p>
                                </div>
                                <div class="pt-1 flex justify-between items-center text-[10px] text-slate-400 border-t border-slate-200/60">
                                    <span>Teknisi: <?php echo htmlspecialchars($row['username']); ?></span>
                                    <a href="detail_laporan_admin.php?id=<?php echo $row['id']; ?>" class="font-bold text-[#0052cc] hover:underline">Detail &rarr;</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center py-10 text-xs text-slate-400">Belum ada titik koordinat patroli.</div>
                    <?php endif; ?>
                </div>
            </div>

        </main>
    </div>

    <!-- SCRIPT PETA LEAFLET -->
    <script>
        let map, markersLayer;
        let allMarkersData = <?php echo json_encode($markers); ?>;

        document.addEventListener("DOMContentLoaded", function() {
            var defaultLat = -0.5021;
            var defaultLng = 117.1536;

            map = L.map('map', { zoomControl: false }).setView([defaultLat, defaultLng], 13);
            L.control.zoom({ position: 'bottomright' }).addTo(map);

            var layerSatelit = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', { maxZoom: 19 });
            var layerStandar = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 });
            var layerTopo = L.tileLayer('https://{s}.tile.opentopomap.org/{z}/{x}/{y}.png', { maxZoom: 17 });

            layerSatelit.addTo(map);
            markersLayer = L.layerGroup().addTo(map);

            window.ubahTemaPeta = function(tema) {
                map.eachLayer(function (layer) {
                    if (layer instanceof L.TileLayer) { map.removeLayer(layer); }
                });
                if (tema === 'standard') { layerStandar.addTo(map); }
                else if (tema === 'topo') { layerTopo.addTo(map); }
                else { layerSatelit.addTo(map); }
            };

            renderMarkers('semua');
            setTimeout(() => map.invalidateSize(), 200);
        });

        function renderMarkers(filter) {
            markersLayer.clearLayers();
            var bounds = [];

            allMarkersData.forEach(function(item) {
                var lat = parseFloat(item.latitude);
                var lng = parseFloat(item.longitude);

                if (filter === 'aman' && item.kategori_filter !== 'aman') return;
                if (filter === 'waspada' && item.kategori_filter !== 'waspada') return;
                if (filter === 'kritis' && item.kategori_filter !== 'kritis') return;

                if (!isNaN(lat) && !isNaN(lng)) {
                    bounds.push([lat, lng]);
                    var popupHtml = `<div style="font-family:'Plus Jakarta Sans',sans-serif;font-size:11px;"><b>${item.lokasi_area}</b><br>Teknisi: ${item.username}<br>Kategori: <b>${item.kategori_filter.toUpperCase()}</b><br>Status: ${item.status_laporan}</div>`;
                    var marker = L.marker([lat, lng]).bindPopup(popupHtml);
                    markersLayer.addLayer(marker);
                }
            });

            ['semua', 'aman', 'waspada', 'kritis'].forEach(f => {
                let btn = document.getElementById('btn-' + f);
                if (btn) {
                    if (f === filter) {
                        btn.className = "px-3 py-1.5 rounded-lg bg-[#0052cc] text-white shadow-xs transition cursor-pointer flex items-center gap-1";
                    } else {
                        btn.className = "px-3 py-1.5 rounded-lg text-slate-600 hover:text-slate-900 transition flex items-center gap-1 cursor-pointer";
                    }
                }
            });

            if (bounds.length > 0) {
                map.fitBounds(bounds, { padding: [40, 40] });
            }
        }

        window.filterMarker = renderMarkers;
    </script>
</body>
</html>