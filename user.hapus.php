<?php 
require_once "koneksi.php";

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id > 0) {
    // Get user info before deletion
    $getUser = mysqli_query($kon, "SELECT * FROM user WHERE id='$id'");
    if (mysqli_num_rows($getUser) > 0) {
        $u = mysqli_fetch_assoc($getUser);
        $del = mysqli_query($kon, "DELETE FROM user WHERE id='$id'");
        if ($del) {
            ?>
            <script>
                alert('User <?= addslashes($u['nama']); ?> (NIP: <?= addslashes($u['nip']); ?>) berhasil dihapus.');
                window.location = 'master?hal=user';
            </script>
            <?php
            exit;
        } else {
            ?>
            <script>
                alert('Gagal menghapus user: <?= addslashes(mysqli_error($kon)); ?>');
                window.location = 'master?hal=user';
            </script>
            <?php
            exit;
        }
    }
}

?>
<script>
    window.location = 'master?hal=user';
</script>
