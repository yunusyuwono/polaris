<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (empty($_SESSION['id_pegawai'])) {
    header("location:login");
    exit;
}
require_once __DIR__ . '/koneksi.php';

if (isset($_GET['pilih'])) {
    $idunit = $_GET['idunit'] ?? '';
    $_SESSION['idunit'] = $idunit;
    header("location:./");
    exit;
}

$namaUser = $_SESSION['nama'] ?? '';
$bucus = mysqli_query($kon, "SELECT *, unit.id as idunit, unit.nama as namaunit, unit.kepala as kepalaunit FROM user JOIN unit ON user.nama=unit.kepala WHERE user.nama='" . mysqli_real_escape_string($kon, $namaUser) . "'");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pilih Unit - POLARIS</title>
    <link rel="icon" href="../assets/POLARIS-HD.png">
    <link rel="stylesheet" href="../assets/css/bootstrap.css">
    <link rel="stylesheet" href="../assets/css/app.css">
    <link rel="stylesheet" href="../assets/vendors/bootstrap-icons/bootstrap-icons.css">
    <style>
        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #0f172a 0%, #1f2937 58%, #0f766e 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
        }
        .select-card {
            max-width: 600px;
            width: 100%;
            border-radius: 1.25rem;
            box-shadow: 0 26px 70px rgba(0,0,0,.3);
            border: none;
        }
        .unit-btn {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            color: #1e293b;
            font-weight: 600;
            text-align: left;
            padding: 1rem 1.25rem;
            border-radius: 0.75rem;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: space-between;
            text-decoration: none;
        }
        .unit-btn:hover {
            background: #0f766e;
            color: #ffffff;
            border-color: #0f766e;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(15, 118, 110, 0.25);
        }
        .unit-btn i {
            font-size: 1.25rem;
        }
    </style>
</head>
<body>
    <div class="card select-card">
        <div class="card-body p-4 p-md-5">
            <div class="text-center mb-4">
                <img src="../assets/POLARIS-HD.png" alt="POLARIS" style="height: 50px;" class="mb-3">
                <h4 class="fw-bold mb-1">Pilih Unit / Program Studi</h4>
                <p class="text-muted mb-0">Akun Anda terdaftar sebagai penanggung jawab lebih dari 1 unit.</p>
            </div>
            
            <div class="alert alert-light border d-flex align-items-center mb-4">
                <i class="bi bi-person-circle fs-4 me-3 text-primary"></i>
                <div>
                    <div class="small text-muted">Pengguna Aktif:</div>
                    <strong><?= htmlspecialchars($namaUser); ?></strong>
                </div>
            </div>

            <div class="d-grid gap-2">
                <?php while ($b = mysqli_fetch_array($bucus)): ?>
                    <a class="unit-btn" href="?pilih=1&idunit=<?= urlencode($b['idunit']); ?>&namaunit=<?= urlencode($b['namaunit']); ?>">
                        <span><i class="bi bi-building me-2"></i> <?= htmlspecialchars($b['namaunit']); ?></span>
                        <i class="bi bi-arrow-right-circle"></i>
                    </a>
                <?php endwhile; ?>
            </div>

            <div class="text-center mt-4">
                <a href="logout.php" class="text-muted text-decoration-none small"><i class="bi bi-box-arrow-left"></i> Logout / Ganti Akun</a>
            </div>
        </div>
    </div>
</body>
</html>
