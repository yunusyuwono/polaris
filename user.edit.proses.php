<?php 
if (isset($_POST['update_user'])) {
    $id = (int)($_POST['id'] ?? 0);
    $nip = trim($_POST['nip'] ?? '');
    $nama = trim($_POST['nama'] ?? '');
    $unit = trim($_POST['unit'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $level = trim($_POST['level'] ?? 'User');

    if ($id <= 0 || empty($nip) || empty($nama)) {
        ?>
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i><strong>Peringatan:</strong> NIP dan Nama Lengkap wajib diisi!
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php
    } else {
        $nip_esc = mysqli_real_escape_string($kon, $nip);
        $nama_esc = mysqli_real_escape_string($kon, $nama);
        $unit_esc = mysqli_real_escape_string($kon, $unit);
        $level_esc = mysqli_real_escape_string($kon, $level);

        // Check if NIP is taken by another user
        $cek_nip = mysqli_query($kon, "SELECT id, nama FROM user WHERE nip='$nip_esc' AND id!='$id'");
        if (mysqli_num_rows($cek_nip) > 0) {
            $taken = mysqli_fetch_assoc($cek_nip);
            ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-times-circle me-2"></i><strong>Gagal:</strong> NIP <code><?= htmlspecialchars($nip); ?></code> sudah digunakan oleh <strong><?= htmlspecialchars($taken['nama']); ?></strong>.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php
        } else {
            if (!empty($password)) {
                $password_hash = md5($password);
                $query = "UPDATE user SET nip='$nip_esc', nama='$nama_esc', unit='$unit_esc', password='$password_hash', level='$level_esc' WHERE id='$id'";
            } else {
                $query = "UPDATE user SET nip='$nip_esc', nama='$nama_esc', unit='$unit_esc', level='$level_esc' WHERE id='$id'";
            }

            $update = mysqli_query($kon, $query);
            if ($update) {
                if ($level === 'Kepala' && !empty($unit_esc)) {
                    mysqli_query($kon, "UPDATE unit SET kepala='$nama_esc' WHERE id='$unit_esc'");
                }
                ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i><strong>Sukses:</strong> Data user <strong><?= htmlspecialchars($nama); ?></strong> berhasil diperbarui!
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php
            } else {
                ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i><strong>Gagal:</strong> Terjadi kesalahan saat memperbarui user: <?= mysqli_error($kon); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php
            }
        }
    }
}
?>