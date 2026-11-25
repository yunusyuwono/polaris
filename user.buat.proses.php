<?php 
if (isset($_POST['simpan_user'])) {
    $nip = trim($_POST['nip'] ?? '');
    $nama = trim($_POST['nama'] ?? '');
    $unit = trim($_POST['unit'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $level = trim($_POST['level'] ?? 'User');

    if (empty($nip) || empty($nama) || empty($password)) {
        ?>
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i><strong>Peringatan:</strong> NIP, Nama Lengkap, dan Password wajib diisi!
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php
    } else {
        $nip_esc = mysqli_real_escape_string($kon, $nip);
        $nama_esc = mysqli_real_escape_string($kon, $nama);
        $unit_esc = mysqli_real_escape_string($kon, $unit);
        $level_esc = mysqli_real_escape_string($kon, $level);
        $password_hash = md5($password);

        // Cek duplikasi NIP
        $cek_query = mysqli_query($kon, "SELECT id, nama FROM user WHERE nip = '$nip_esc'");
        if (mysqli_num_rows($cek_query) > 0) {
            $existing = mysqli_fetch_assoc($cek_query);
            ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-times-circle me-2"></i><strong>Gagal:</strong> NIP <code><?= htmlspecialchars($nip); ?></code> sudah terdaftar atas nama <strong><?= htmlspecialchars($existing['nama']); ?></strong>.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php
        } else {
            $simpan = mysqli_query($kon, "INSERT INTO user(nip, nama, unit, password, level) VALUES ('$nip_esc', '$nama_esc', '$unit_esc', '$password_hash', '$level_esc')");
            if ($simpan) {
                if ($level === 'Kepala' && !empty($unit_esc)) {
                    mysqli_query($kon, "UPDATE unit SET kepala='$nama_esc' WHERE id='$unit_esc'");
                }
                ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i><strong>Sukses:</strong> User <strong><?= htmlspecialchars($nama); ?></strong> (NIP: <?= htmlspecialchars($nip); ?>) berhasil ditambahkan!
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <script>
                    if (window.history.replaceState) {
                        window.history.replaceState(null, null, window.location.href);
                    }
                </script>
                <?php
            } else {
                ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i><strong>Gagal:</strong> Terjadi kesalahan saat menyimpan user: <?= mysqli_error($kon); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php
            }
        }
    }
}
?>