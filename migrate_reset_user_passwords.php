<?php
/**
 * Migration Script: Reset User Passwords to '123456' (Except Admin)
 * 
 * Penggunaan:
 * - Via Browser: buka http://localhost/digami/migrate_reset_user_passwords.php
 * - Via CLI: php migrate_reset_user_passwords.php
 */

require_once __DIR__ . '/koneksi.php';

if (!$kon) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

$isCli = (php_sapi_name() === 'cli');
$newPasswordPlain = '123456';
$newPasswordMd5 = md5($newPasswordPlain); // e10adc3949ba59abbe56e057f20f883e

// Query update password semua user kecuali yang NIP/username atau level adalah admin
$query = "UPDATE `user` 
          SET `password` = '$newPasswordMd5' 
          WHERE LOWER(`nip`) NOT IN ('admin', 'admindigami') 
            AND LOWER(`level`) != 'admin'";

$result = mysqli_query($kon, $query);

if ($result) {
    $affectedRows = mysqli_affected_rows($kon);
    
    // Hitung total data user saat ini
    $countQuery = mysqli_query($kon, "SELECT COUNT(*) as total FROM `user` WHERE LOWER(`nip`) NOT IN ('admin', 'admindigami') AND LOWER(`level`) != 'admin'");
    $totalUsers = mysqli_fetch_assoc($countQuery)['total'] ?? 0;

    $msg = "Berhasil! Password untuk $affectedRows user (dari total $totalUsers user) telah direset menjadi '$newPasswordPlain'.\nAkun admin tetap aman (tidak diubah).";
    
    if ($isCli) {
        echo "[SUCCESS] " . $msg . PHP_EOL;
    } else {
        echo "<!DOCTYPE html><html><head><title>Migrasi Reset Password</title>";
        echo "<link rel='stylesheet' href='assets/css/bootstrap.css'></head><body class='p-5'>";
        echo "<div class='container col-md-6'>";
        echo "<div class='alert alert-success'>";
        echo "<h4>Migrasi Berhasil</h4>";
        echo "<p>" . nl2br(htmlspecialchars($msg)) . "</p>";
        echo "<hr><a href='index.php' class='btn btn-primary'>Kembali ke Beranda</a>";
        echo "</div></div></body></html>";
    }
} else {
    $errorMsg = "Gagal melakukan reset password: " . mysqli_error($kon);
    if ($isCli) {
        echo "[ERROR] " . $errorMsg . PHP_EOL;
    } else {
        echo "<div style='color:red;'>$errorMsg</div>";
    }
}
