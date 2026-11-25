<!DOCTYPE html>
<html lang="en">
<?php 
if(session_status() === PHP_SESSION_NONE) {
    session_start();
}
if(empty($_SESSION['id_pegawai']))
{
    header("location:login");
    exit;
}
include 'koneksi.php';
?>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POLARIS - Auditor</title>
    
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/bootstrap.css">

    <link rel="stylesheet" href="../assets/vendors/perfect-scrollbar/perfect-scrollbar.css">
    <link rel="stylesheet" href="../assets/vendors/bootstrap-icons/bootstrap-icons.css">
    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.2/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/app.css">
    <link rel="icon" href="../assets/POLARIS-HD.png">
    <link rel="stylesheet" href="../assets/vendors/simple-datatables/style.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.1/css/dataTables.bootstrap5.min.css">

    <link rel="stylesheet" href="../assets/vendors/toastify/toastify.css">
</head>
<?php 
$_SESSION['level']='auditor';
?>
<body>
    <?php
    if (!function_exists('isActiveMenu')) {
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
    }

    $menuItems = [
        ['href' => './', 'icon' => 'bi bi-speedometer2', 'label' => 'Dashboard'],
        ['href' => 'praaudit', 'icon' => 'bi bi-clipboard-check', 'label' => 'Pra Audit'],
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
        .sidebar-toggler {
            bottom: 1.5rem;
        }
        #main {
            background: #f5f7fb;
            min-height: 100vh;
        }
        .page-content {
            padding-top: .5rem;
        }
        .card {
            border: 1px solid #e5e7eb;
            border-radius: .75rem;
            box-shadow: 0 14px 30px rgba(15,23,42,.05);
        }
        .card-header {
            background: #fff;
            border-bottom: 1px solid #edf2f7;
            border-radius: .75rem .75rem 0 0 !important;
        }
        .table {
            color: #1f2937;
        }
        .table thead th {
            background: #f8fafc;
            color: #334155;
            font-size: .78rem;
            letter-spacing: .02em;
            text-transform: uppercase;
            border-bottom: 1px solid #e5e7eb;
            white-space: nowrap;
        }
        .btn {
            border-radius: .5rem;
        }
        .auditor-page-hero {
            background:#fff;
            border:1px solid #e5e7eb;
            border-radius:.8rem;
            padding:1.25rem;
            box-shadow:0 14px 30px rgba(15,23,42,.05);
        }
        .auditor-stat {
            background:#fff;
            border:1px solid #e5e7eb;
            border-radius:.7rem;
            padding:1rem;
            height:100%;
            box-shadow:0 12px 26px rgba(15,23,42,.04);
        }
        .auditor-stat-icon {
            width:38px;
            height:38px;
            border-radius:.6rem;
            display:inline-flex;
            align-items:center;
            justify-content:center;
            margin-bottom:.75rem;
            background:#ecfeff;
            color:#0f766e;
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
                                <span><strong>Auditor</strong><br><b><?= htmlspecialchars($_SESSION['nama'] ?? ''); ?></b></span>
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
