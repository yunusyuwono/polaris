<?php       
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$ka = $_GET['ka'] ?? '';
if (!empty($ka) && isset($kon)) {
    $query_cto = mysqli_query($kon, "select * from audit where kodeaudit='$ka'");
    if ($query_cto && mysqli_num_rows($query_cto) > 0) {
        $cto = mysqli_fetch_array($query_cto);
        
        // Token audit hanya berlaku ketika sesi auditor, untuk admin tidak usah verifikasi token
        $isAdmin = (isset($_SESSION['level']) && strtolower($_SESSION['level']) === 'admin');
        if (!$isAdmin) {
            $etoken = $_GET['et'] ?? '';
            if ($etoken != md5($cto['token'])) {
                ?>
                <script type="text/javascript">
                    alert('Token tidak valid');
                    window.location='./';
                </script>
                <?php
                exit;
            }
        }
    }
}
?>