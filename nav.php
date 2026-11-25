<!DOCTYPE html>
<html lang="en">
<?php 
if(session_status() === PHP_SESSION_NONE) {
    session_start();
}
if(empty($_SESSION['id_pegawai']))
{
    ?>
        <script>window.location='login'</script>
    <?php
    exit;
}
include 'koneksi.php';
?>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POLARIS</title>
    
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/bootstrap.css">

    <link rel="stylesheet" href="assets/vendors/perfect-scrollbar/perfect-scrollbar.css">
    <link rel="stylesheet" href="assets/vendors/bootstrap-icons/bootstrap-icons.css">
    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.2/css/all.min.css">
    <link rel="stylesheet" href="assets/css/app.css">
    <link rel="icon" href="assets/POLARIS-HD.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.2.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.1/css/dataTables.bootstrap5.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.14/dist/css/bootstrap-select.min.css">
    
    <link rel="stylesheet" href="assets/vendors/toastify/toastify.css">
</head>

<body>
    <?php
    function isActiveMenu($href) {
        $requestUri = $_SERVER['REQUEST_URI'] ?? '/';
        $currentPath = trim(parse_url($requestUri, PHP_URL_PATH), '/');
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
        ['href' => 'master?hal=unit', 'icon' => 'bi bi-collection', 'label' => 'Master'],
        ['href' => 'sarmut.manage', 'icon' => 'bi bi-list-check', 'label' => 'Sarmut'],
        ['href' => 'iso', 'icon' => 'bi bi-award', 'label' => 'ISO'],
        ['href' => 'tanya', 'icon' => 'bi bi-chat-dots', 'label' => 'Pertanyaan LED'],
        ['href' => 'praaudit', 'icon' => 'bi bi-clipboard-check', 'label' => 'Pra Audit'],
        ['href' => 'audit', 'icon' => 'bi bi-check2-circle', 'label' => 'Audit'],
        ['href' => 'laporan', 'icon' => 'bi bi-file-text', 'label' => 'Laporan'],
        ['href' => 'setting', 'icon' => 'bi bi-gear', 'label' => 'Setting'],
        ['href' => 'logout', 'icon' => 'bi bi-box-arrow-right', 'label' => 'Logout'],
    ];
    ?>
    <style>
        #sidebar {
            background: linear-gradient(160deg, #071618 0%, #0c2b2e 60%, #004b52 100%);
        }
        .sidebar-wrapper {
            background: linear-gradient(160deg, #071618 0%, #0c2b2e 60%, #004b52 100%) !important;
            padding: 1.5rem 0;
            box-shadow: 12px 0 35px rgba(0, 75, 82, 0.15);
        }
        .sidebar-menu {
            background: transparent !important;
        }
        .sidebar-header {
            background: transparent !important;
            padding: 0 1.5rem 2rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            margin-bottom: 1.5rem;
        }
        .sidebar-header .logo a {
            color: #ffffff !important;
            font-size: 1.3rem;
            font-weight: 800;
            letter-spacing: -.02em;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
            position: relative;
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
            color: #ffffff;
            padding: 0 1rem 0.75rem;
            margin-bottom: 0.5rem;
        }
        .sidebar-item {
            margin: 0.25rem 0;
            list-style: none;
        }
        .sidebar-item > .sidebar-link {
            color: #cbd5e1 !important;
            padding: 0.75rem 1rem;
            border-radius: 0.625rem;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            gap: 0.875rem;
            text-decoration: none;
            margin: 0 0.5rem;
            position: relative;
        }
        .sidebar-item > .sidebar-link:hover {
            background-color: rgba(0, 161, 173, 0.15);
            color: #ffffff !important;
            transform: translateX(4px);
        }
        .sidebar-item.active > .sidebar-link {
            background: linear-gradient(90deg, rgba(0, 161, 173, 0.25) 0%, rgba(198, 222, 33, 0.12) 100%);
            color: #ffffff !important;
            border-left: 3px solid #00a1ad;
            padding-left: calc(1rem - 3px);
            box-shadow: inset 0 0 0 1px rgba(0, 161, 173, 0.15);
        }
        .sidebar-item > .sidebar-link i {
            color: currentColor;
            min-width: 1.3rem;
            font-size: 1.1rem;
            flex-shrink: 0;
        }
        .sidebar-item.active > .sidebar-link i {
            color: #00a1ad;
            filter: drop-shadow(0 0 8px rgba(0, 161, 173, 0.25));
        }
        .sidebar-item > .sidebar-link span {
            white-space: nowrap;
            font-size: 0.95rem;
            font-weight: 600;
            color: #ffffff;
        }
        .sidebar-item > .sidebar-link.user-info {
            color: #ffffff;
            padding: 0.75rem 1rem;
            margin: 0 0.5rem 1rem;
            background-color: rgba(255, 255, 255, 0.08);
            border-radius: 0.625rem;
        }
        .sidebar-item > .sidebar-link.user-info span {
            color: #ffffff;
        }
        .user-info span {
            display: block;
            font-size: 0.85rem;
            white-space: normal;
            word-break: break-word;
        }
        .user-info b {
            color: #ffffff !important;
            font-weight: 700;
        }
        .sidebar-toggler {
            bottom: 1.5rem;
        }
    </style>
    <div id="app">
        <div id="sidebar" class="active">
            <div class="sidebar-wrapper active">
                <div class="sidebar-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="logo">
                            <a href="index.php" style="display: flex; align-items: center; gap: 0.5rem; text-decoration: none;">
                                <img src="assets/POLARIS-HD.png" alt="POLARIS Logo" style="width: 32px; height: 32px; border-radius: 0.65rem; object-fit: contain;">
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
                                <span><strong>Admin</strong><br><b><?= htmlspecialchars($_SESSION['nama'] ?? ''); ?></b></span>
                            </a>
                        </li>
                        <?php foreach ($menuItems as $item): ?>
                            <li class="sidebar-item <?= isActiveMenu($item['href']) ? 'active' : '' ?>">
                                <a href="<?= $item['href'] ?>" class="sidebar-link">
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
