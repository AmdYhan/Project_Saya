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
$latest_report = null;
$q_kritis = 0;
$q_waspada = 0;

while ($row = mysqli_fetch_assoc($query_peta)) {
    if (is_numeric($row['latitude']) && is_numeric($row['longitude'])) {
        
        $teks_gabungan = strtolower(($row['potensi_ancaman'] ?? '') . ' ' . ($row['kondisi_fisik'] ?? '') . ' ' . ($row['catatan'] ?? ''));
        
        // Logika Kategorisasi yang Lebih Spesifik Berdasarkan Data Riil Database
        // Kritis: Kerusakan berat pada kabel/tiang, patah, rebah, atau longsor
        if (preg_match('/(patah|rebah|longsor|putus|darurat)/', $teks_gabungan) || 
            (strpos($teks_gabungan, 'rusak') !== false && (strpos($teks_gabungan, 'kabel') !== false || strpos($teks_gabungan, 'tiang') !== false))) {
            $q_kritis++;
            $row['kategori_filter'] = 'kritis';
        } else {
            $q_waspada++;
            $row['kategori_filter'] = 'waspada';
        }

        $markers[] = $row;
        if (!$latest_report) {
            $latest_report = $row;
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
    <title>GIS Patroli Fiber Samarinda - Lintasarta</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />
    
    <!-- Leaflet CSS & JS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f8fafc; }
        #map { height: 320px; width: 100%; border-radius: 1rem; z-index: 1; transition: all 0.3s ease; }
        
        .leaflet-control-attribution { display: none !important; }
        .leaflet-bottom.leaflet-right { bottom: 10px !important; right: 10px !important; }

        .fullscreen-map {
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            width: 100vw !important;
            height: 100vh !important;
            z-index: 99999 !important;
            border-radius: 0 !important;
            padding: 0 !important;
            background: white;
        }
        .fullscreen-map #map {
            height: 100vh !important;
            border-radius: 0 !important;
        }

        @keyframes slideUpFade {
            0% { opacity: 0; transform: translateY(24px) scale(0.97); }
            100% { opacity: 1; transform: translateY(0) scale(1); }
        }

        .animate-entry {
            opacity: 0;
            animation: slideUpFade 0.75s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        .delay-100 { animation-delay: 0.1s; }
        .delay-200 { animation-delay: 0.2s; }
        .delay-300 { animation-delay: 0.3s; }
        .delay-400 { animation-delay: 0.4s; }
    </style>
</head>
<body class="text-[#0d1c30] pb-28">

    <!-- HEADER TOP -->
    <header class="bg-white border-b border-slate-100 sticky top-0 z-30 px-4 py-3 shadow-sm">
        <div class="max-w-md mx-auto flex items-center justify-between">
            <div>
                <h1 class="font-extrabold text-sm text-[#0d1c30] leading-tight">SAMARINDA OPS</h1>
                <p class="text-[10px] text-slate-400 font-medium">Peta Jalur</p>
            </div>
            <a href="profil_admin.php" class="w-10 h-10 rounded-full bg-blue-50/80 border border-blue-100 flex items-center justify-center text-[#0052cc] active:scale-95 transition shadow-sm">
                <span class="material-symbols-outlined text-[22px]">person</span>
            </a>
        </div>
    </header>

    <main id="mainContainer" class="max-w-md mx-auto p-4 space-y-4">
        
        <!-- 1. TITLE GIS & FILTER BUTTONS -->
        <div class="animate-entry delay-100 bg-white p-3.5 rounded-2xl border border-slate-100 shadow-sm space-y-3">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-xl bg-[#0052cc]/10 text-[#0052cc] flex items-center justify-center">
                        <span class="material-symbols-outlined text-[18px]">satellite_alt</span>
                    </div>
                    <div>
                        <div class="flex items-center gap-1.5">
                            <h2 class="font-extrabold text-sm text-[#0d1c30]">GIS Patroli Fiber Samarinda</h2>
                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        </div>
                        <p class="text-[10px] text-slate-400 font-medium">Pusat Kendali OSP Koridor Mahakam</p>
                    </div>
                </div>
            </div>
            
            <div class="flex gap-2 overflow-x-auto pb-0.5 text-xs">
                <button onclick="filterMarker('semua')" id="btn-semua" class="bg-[#0052cc] text-white px-3 py-1.5 rounded-xl font-bold whitespace-nowrap shadow-sm transition cursor-pointer">Semua (<?php echo $total_tkp; ?>)</button>
                <button onclick="filterMarker('kritis')" id="btn-kritis" class="bg-white border border-slate-200 text-slate-600 px-3 py-1.5 rounded-xl font-semibold whitespace-nowrap flex items-center gap-1 transition cursor-pointer"><span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> Kritis (<?php echo $q_kritis; ?>)</button>
                <button onclick="filterMarker('waspada')" id="btn-waspada" class="bg-white border border-slate-200 text-slate-600 px-3 py-1.5 rounded-xl font-semibold whitespace-nowrap flex items-center gap-1 transition cursor-pointer"><span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Waspada (<?php echo $q_waspada; ?>)</button>
            </div>
        </div>

        <!-- 2. CONTAINER PETA INTERAKTIF -->
        <div id="mapWrapper" class="animate-entry delay-200 bg-white p-3 rounded-3xl border border-slate-100 shadow-sm relative transition-all duration-300 space-y-2.5">
            
            <div class="flex flex-wrap items-center justify-between gap-1.5 z-[1000] relative px-0.5">
                <div class="bg-white/95 backdrop-blur-md border border-slate-200 px-2.5 py-1 rounded-xl shadow-sm text-[10px] font-bold text-slate-700 flex items-center gap-1">
                    <span class="material-symbols-outlined text-[12px] text-[#0052cc]">my_location</span> -0.5021, 117.1536
                </div>

                <div class="flex flex-wrap items-center gap-1">
                    <span class="bg-white/95 backdrop-blur-md border border-slate-200 text-slate-700 text-[10px] font-bold px-2.5 py-1 rounded-xl shadow-sm flex items-center gap-0.5">
                        <span class="material-symbols-outlined text-[12px]">network_node</span> OSP
                    </span>
                    <span class="bg-white/95 backdrop-blur-md border border-slate-200 text-slate-700 text-[10px] font-bold px-2.5 py-1 rounded-xl shadow-sm flex items-center gap-0.5">
                        <span class="material-symbols-outlined text-[12px]">heatmap</span> Heatmap
                    </span>
                    
                    <select id="mapThemeSelect" onchange="ubahTemaPeta(this.value)" class="bg-white/95 backdrop-blur-md border border-slate-200 text-slate-700 text-[10px] font-bold px-2 py-1 rounded-xl shadow-sm focus:outline-none cursor-pointer">
                        <option value="satelit">🛰️ Satelit</option>
                        <option value="standard">🗺️ Standar</option>
                        <option value="topo">⛰️ Topo</option>
                    </select>

                    <button onclick="toggleFullScreen()" class="w-7 h-7 bg-white/95 backdrop-blur-md border border-slate-200 text-slate-700 rounded-xl shadow-sm flex items-center justify-center hover:bg-slate-100 transition cursor-pointer" title="Layar Penuh">
                        <span id="fsIcon" class="material-symbols-outlined text-[14px]">fullscreen</span>
                    </button>
                </div>
            </div>

            <div id="map"></div>
        </div>

        <!-- 3. KARTU DETAIL LAPORAN TERBARU -->
        <?php if ($latest_report) : ?>
            <?php 
                $row = $latest_report;
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
            <div class="animate-entry delay-300 bg-white rounded-2xl p-4 shadow-sm border border-slate-100 space-y-3 relative hover:shadow-md transition duration-200">
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
        <?php endif; ?>

    </main>

    <!-- BOTTOM NAVBAR -->
    <nav class="fixed bottom-0 left-0 right-0 bg-white/95 backdrop-blur-md border-t border-slate-200 py-2.5 px-6 flex justify-around items-center z-30 max-w-md mx-auto shadow-lg">
        <a href="dashboard_admin.php" class="flex flex-col items-center text-slate-400 hover:text-slate-600"><span class="material-symbols-outlined text-[22px]">grid_view</span><span class="text-[10px] mt-0.5">Beranda</span></a>
        <a href="pemantauan_admin.php" class="flex flex-col items-center text-[#0052cc] font-bold"><span class="material-symbols-outlined text-[22px]" style="font-variation-settings: 'FILL' 1;">map</span><span class="text-[10px] mt-0.5">Peta Jalur</span></a>
        <a href="laporan_admin.php" class="flex flex-col items-center text-slate-400 hover:text-slate-600"><span class="material-symbols-outlined text-[22px]">assignment</span><span class="text-[10px] mt-0.5">Laporan</span></a>
        <a href="profil_admin.php" class="flex flex-col items-center text-slate-400 hover:text-slate-600"><span class="material-symbols-outlined text-[22px]">person</span><span class="text-[10px] mt-0.5">Profil</span></a>
    </nav>

    <!-- SCRIPT PETA & FILTER INTERAKTIF -->
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

            window.toggleFullScreen = function() {
                var wrapper = document.getElementById('mapWrapper');
                var icon = document.getElementById('fsIcon');
                wrapper.classList.toggle('fullscreen-map');
                icon.innerText = wrapper.classList.contains('fullscreen-map') ? 'fullscreen_exit' : 'fullscreen';
                setTimeout(() => map.invalidateSize(), 100);
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

                // Filter berdasarkan kategori_filter dari PHP
                if (filter === 'kritis' && item.kategori_filter !== 'kritis') return;
                if (filter === 'waspada' && item.kategori_filter !== 'waspada') return;

                if (!isNaN(lat) && !isNaN(lng)) {
                    bounds.push([lat, lng]);
                    var popupHtml = `<div style="font-family:'Plus Jakarta Sans',sans-serif;font-size:11px;"><b>${item.lokasi_area}</b><br>Teknisi: ${item.username}<br>Kategori: <b>${item.kategori_filter.toUpperCase()}</b><br>Status: ${item.status_laporan}</div>`;
                    var marker = L.marker([lat, lng]).bindPopup(popupHtml);
                    markersLayer.addLayer(marker);
                }
            });

            // Update Style Active Button
            ['semua', 'kritis', 'waspada'].forEach(f => {
                let btn = document.getElementById('btn-' + f);
                if (btn) {
                    if (f === filter) {
                        btn.className = "bg-[#0052cc] text-white px-3 py-1.5 rounded-xl font-bold whitespace-nowrap shadow-sm transition cursor-pointer flex items-center gap-1";
                    } else {
                        btn.className = "bg-white border border-slate-200 text-slate-600 px-3 py-1.5 rounded-xl font-semibold whitespace-nowrap flex items-center gap-1 transition cursor-pointer";
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