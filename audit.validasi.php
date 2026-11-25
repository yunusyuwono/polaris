<?php       
if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
    session_start();
}

$ka = isset($_GET['ka']) ? mysqli_real_escape_string($kon, trim((string)$_GET['ka'])) : '';
$cto = [];

if (!empty($ka) && isset($kon)) {
    $query_cto = mysqli_query($kon, "SELECT * FROM audit WHERE kodeaudit='$ka'");
    if ($query_cto && mysqli_num_rows($query_cto) > 0) {
        $cto = mysqli_fetch_array($query_cto);
        
        $isAdmin = (isset($_SESSION['level']) && strtolower($_SESSION['level']) === 'admin');
        $idPegawai = $_SESSION['id_pegawai'] ?? '';
        
        // Check if current logged in user is registered as an auditor for this audit
        $isAssignedAuditor = false;
        if (!empty($idPegawai)) {
            $checkAuditor = mysqli_query($kon, "SELECT id FROM auditor WHERE kodeaudit='$ka' AND user='$idPegawai' LIMIT 1");
            if ($checkAuditor && mysqli_num_rows($checkAuditor) > 0) {
                $isAssignedAuditor = true;
            }
        }

        $etoken = $_GET['et'] ?? '';
        $isTokenValid = (!empty($etoken) && !empty($cto['token']) && $etoken === md5($cto['token']));

        if (!$isAdmin && !$isAssignedAuditor && !$isTokenValid) {
            ?>
            <div class="container py-5 text-center">
                <div class="card shadow-sm border-0 mx-auto" style="max-width: 500px;">
                    <div class="card-body p-5">
                        <i class="fas fa-lock text-warning fa-3x mb-3"></i>
                        <h4 class="fw-bold text-dark">Akses Dibatasi</h4>
                        <p class="text-muted mb-4">Token audit tidak valid atau Anda tidak memiliki akses untuk audit #<?= htmlspecialchars($ka); ?>.</p>
                        <a href="audit" class="btn btn-primary px-4"><i class="fas fa-arrow-left me-2"></i>Kembali ke Daftar Audit</a>
                    </div>
                </div>
            </div>
            <?php
            include_once 'foot.php';
            exit;
        }
    }
}
?>