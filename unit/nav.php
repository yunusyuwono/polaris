<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (empty($_SESSION['id_pegawai'])) {
    header("location:login");
    exit;
}
require_once __DIR__ . '/koneksi.php';

$_SESSION['level'] = 'unit';
$namaUser = $_SESSION['nama'] ?? '';
$idPegawai = $_SESSION['id_pegawai'] ?? '';

// Cek apakah user merupakan kepala unit di lebih dari satu unit
$bucus = mysqli_query($kon, "SELECT *, unit.id as idunit, unit.nama as namaunit, unit.kepala as kepalaunit FROM user JOIN unit ON user.nama=unit.kepala WHERE user.nama='" . mysqli_real_escape_string($kon, $namaUser) . "'");

$cekus = null;
if (!empty($_SESSION['idunit'])) {
    $idUnit = mysqli_real_escape_string($kon, $_SESSION['idunit']);
    $cekus = mysqli_query($kon, "SELECT *, unit.id as idunit, unit.nama as namaunit, unit.kepala as kepalaunit FROM user JOIN unit ON user.nama=unit.kepala WHERE user.nama='" . mysqli_real_escape_string($kon, $namaUser) . "' AND unit.id='$idUnit'");
    if (!$cekus || mysqli_num_rows($cekus) == 0) {
        // Fallback jika query join nama tidak menghasilkan data
        $cekus = mysqli_query($kon, "SELECT *, unit.id as idunit, unit.nama as namaunit, unit.kepala as kepalaunit FROM unit WHERE id='$idUnit'");
    }
} else {
    if ($bucus && mysqli_num_rows($bucus) > 1) {
        $currentScript = basename($_SERVER['PHP_SELF'] ?? '');
        if ($currentScript !== 'pilihunit.php') {
            header("location:pilihunit.php?user=" . urlencode($namaUser));
            exit;
        }
    } else {
        $cekus = mysqli_query($kon, "SELECT *, unit.id as idunit, unit.nama as namaunit, unit.kepala as kepalaunit FROM user JOIN unit ON user.unit=unit.id WHERE user.id='" . mysqli_real_escape_string($kon, $idPegawai) . "'");
    }
}

$cs = ($cekus && mysqli_num_rows($cekus) > 0) ? mysqli_fetch_array($cekus) : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POLARIS - Unit</title>
    
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/bootstrap.css">

    <link rel="stylesheet" href="../assets/vendors/perfect-scrollbar/perfect-scrollbar.css">
    <link rel="stylesheet" href="../assets/vendors/bootstrap-icons/bootstrap-icons.css">
    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.2/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/app.css">
    <link rel="icon" href="../assets/POLARIS-HD.png">
    <link rel="stylesheet" href="../assets/vendors/simple-datatables/style.css">

    <link rel="stylesheet" href="../assets/vendors/toastify/toastify.css">
</head>
<body>
    <?php
    function isActiveMenu($href) {
        $currentPath = trim(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH), '/');
        $currentPath = $currentPath === '' ? 'index.php' : basename($currentPath);
        $parsed = parse_url($href);
        $hrefPath = trim($parsed['path'] ?? '', '/');
        $hrefPath = $hrefPath === '' || $hrefPath === './' ? 'index.php' : basename($hrefPath);

        if ($hrefPath === 'index.php') {
            return $currentPath === 'index.php';
        }

        if (strpos($currentPath, $hrefPath) === 0) {
            if (empty($parsed['query'])) {
                return true;
            }
            parse_str($parsed['query'], $hrefParams);
            parse_str($_SERVER['QUERY_STRING'] ?? '', $currentParams);
            return $hrefParams == $currentParams;
        }

        return false;
    }

    $menuItems = [
        ['href' => './', 'icon' => 'bi bi-speedometer2', 'label' => 'Dashboard'],
        ['href' => 'led', 'icon' => 'bi bi-clipboard-check', 'label' => 'Pra Audit'],
        ['href' => 'audit', 'icon' => 'bi bi-check2-circle', 'label' => 'Audit'],
        ['href' => 'logout', 'icon' => 'bi bi-box-arrow-right', 'label' => 'Logout'],
    ];
    ?>
    <style>
        #sidebar {
            background: #111827;
        }
        .sidebar-wrapper {
            background: linear-gradient(180deg, #111827 0%, #1f2937 58%, #263241 100%) !important;
            padding: 1.35rem 0;
            box-shadow: 12px 0 35px rgba(17, 24, 39, 0.18);
        }
        .sidebar-menu {
            background: transparent !important;
        }
        .sidebar-header {
            background: transparent !important;
            padding: 0 1.35rem 1.25rem;
            border-bottom: 1px solid rgba(148, 163, 184, 0.18);
            margin-bottom: 1rem;
        }
        .sidebar-header .logo a {
            color: #f8fafc !important;
            font-size: 1.3rem;
            font-weight: 800;
            letter-spacing: 0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
        }
        .sidebar-header .logo a::before {
            display: none !important;
        }
        .sidebar-menu .menu {
            list-style: none;
            margin: 0;
            padding: 0;
        }
        .menu-section {
            margin-bottom: 2rem;
            padding: 0 1rem;
        }
        .menu-section-title {
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: #94a3b8;
            padding: 0 1rem 0.75rem;
            margin-bottom: 0.5rem;
        }
        .sidebar-item {
            margin: 0.25rem 0;
            list-style: none;
        }
        .sidebar-item > .sidebar-link {
            color: #cbd5e1 !important;
            padding: 0.78rem 1rem;
            border-radius: 0.55rem;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            gap: 0.875rem;
            text-decoration: none;
            margin: 0 0.5rem;
            position: relative;
        }
        .sidebar-item > .sidebar-link:hover {
            background-color: rgba(15, 118, 110, 0.18);
            color: #ffffff !important;
            transform: translateX(4px);
        }
        .sidebar-item.active > .sidebar-link {
            background: linear-gradient(90deg, rgba(15, 118, 110, 0.34) 0%, rgba(37, 99, 235, 0.18) 100%);
            color: #ffffff !important;
            border-left: 3px solid #2dd4bf;
            padding-left: calc(1rem - 3px);
            box-shadow: inset 0 0 0 1px rgba(45, 212, 191, 0.12);
        }
        .sidebar-item > .sidebar-link i {
            color: currentColor;
            min-width: 1.3rem;
            font-size: 1.1rem;
            flex-shrink: 0;
        }
        .sidebar-item.active > .sidebar-link i {
            color: #5eead4;
        }
        .sidebar-item > .sidebar-link span {
            white-space: nowrap;
            font-size: 0.95rem;
            font-weight: 700;
        }
        .sidebar-item > .sidebar-link.user-info {
            align-items: flex-start;
            padding: 0.95rem 1rem;
            margin: 0 0.5rem 1.25rem;
            background: rgba(15, 23, 42, 0.42);
            border: 1px solid rgba(148, 163, 184, 0.2);
            border-radius: 0.7rem;
            transform: none;
        }
        .user-info span {
            display: block;
            font-size: 0.85rem;
            white-space: normal;
            word-break: break-word;
            line-height: 1.35;
            color: #cbd5e1;
        }
        .user-info b {
            color: #f8fafc !important;
            font-weight: 800;
        }
        .user-info .unit-label {
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            color: #5eead4;
            font-size: .72rem;
            font-weight: 800;
            letter-spacing: .08em;
            text-transform: uppercase;
            margin-bottom: .35rem;
        }
        .sidebar-toggler {
            bottom: 1.5rem;
        }
        #main {
            background: #f5f7fb;
            min-height: 100vh;
        }
    </style>
    <div id="app">
        <div id="sidebar" class="active">
            <div class="sidebar-wrapper active">
                <div class="sidebar-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="logo">
                            <a href="index.php" style="display: flex; align-items: center; gap: 0.5rem; text-decoration: none;">
                                <img src="../assets/POLARIS-HD.png" alt="POLARIS Logo" style="width: 32px; height: 32px; border-radius: 0.65rem; object-fit: contain;">
                                <span style="font-weight: 800; color: #f8fafc; font-size: 1.3rem; letter-spacing: 0.05em; font-family: 'Poppins', sans-serif;">POLARIS</span>
                            </a>
                        </div>
                        <div class="toggler d-xl-none">
                            <a href="#" class="sidebar-hide"><i class="bi bi-x bi-middle"></i></a>
                        </div>
                    </div>
                </div>
                <div class="sidebar-menu">
                    <ul class="menu">
                        <li class="sidebar-item">
                            <a class="sidebar-link user-info">
                                <span>
                                    <span class="unit-label"><i class="bi bi-building"></i> Unit</span>
                                    <strong><?= htmlspecialchars($cs['namaunit'] ?? '-'); ?></strong><br>
                                    <b><?= htmlspecialchars($cs['kepalaunit'] ?? '-'); ?></b>
                                </span>
                            </a>
                        </li>
                        <?php foreach ($menuItems as $item): ?>
                            <li class="sidebar-item <?= isActiveMenu($item['href']) ? 'active' : '' ?>">
                                <a href="<?= $item['href'] ?>" class="sidebar-link text-white">
                                    <i class="<?= $item['icon'] ?>"></i>
                                    <span><?= $item['label'] ?></span>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <button class="sidebar-toggler btn x"><i data-feather="x"></i></button>
            </div>
        </div>
