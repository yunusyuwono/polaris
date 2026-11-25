<?php
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$getUser = mysqli_query($kon, "SELECT * FROM user WHERE id='$id'");
$userExists = mysqli_num_rows($getUser) > 0;
$userData = $userExists ? mysqli_fetch_assoc($getUser) : null;
?>

<div class="card shadow-sm border-0">
    <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
        <h5 class="mb-0 text-dark fw-bold"><i class="fas fa-user-edit text-primary me-2"></i> Edit Data Pengguna</h5>
        <a class="btn btn-outline-secondary btn-sm rounded-pill px-3" href="master?hal=user">
            <i class="fas fa-arrow-left me-1"></i> Kembali ke Daftar User
        </a>
    </div>
    <div class="card-body p-4">
        <?php 
        include "user.edit.proses.php";
        
        // Refresh data after update if updated
        if (isset($_POST['update_user'])) {
            $getUser = mysqli_query($kon, "SELECT * FROM user WHERE id='$id'");
            if (mysqli_num_rows($getUser) > 0) {
                $userData = mysqli_fetch_assoc($getUser);
            }
        }

        if (!$userExists || !$userData) {
            ?>
            <div class="alert alert-danger mb-0">
                <i class="fas fa-exclamation-circle me-2"></i> Data user dengan ID tersebut tidak ditemukan atau telah dihapus.
            </div>
            <?php
        } else {
            ?>
            <form method="post" autocomplete="off">
                <input type="hidden" name="id" value="<?= $userData['id']; ?>">
                
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label class="form-label fw-semibold text-dark">NIP / Username <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fas fa-id-card text-muted"></i></span>
                                <input type="text" name="nip" class="form-control" value="<?= htmlspecialchars($userData['nip']); ?>" required placeholder="Masukkan NIP atau Username">
                            </div>
                        </div>

                        <div class="form-group mb-3">
                            <label class="form-label fw-semibold text-dark">Nama Lengkap & Gelar <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fas fa-user text-muted"></i></span>
                                <input type="text" name="nama" class="form-control" value="<?= htmlspecialchars($userData['nama']); ?>" required placeholder="Contoh: Dr. Nama Lengkap, M.Kes">
                            </div>
                        </div>

                        <div class="form-group mb-3">
                            <label class="form-label fw-semibold text-dark">Unit / Program Studi</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fas fa-building text-muted"></i></span>
                                <select name="unit" class="form-select">
                                    <option value="">-- Pilih Unit/Prodi --</option>
                                    <?php 
                                    $unitQuery = mysqli_query($kon, "SELECT * FROM unit ORDER BY nama ASC");
                                    while ($u = mysqli_fetch_array($unitQuery)) {
                                        $selected = ($u['id'] == $userData['unit']) ? 'selected' : '';
                                        ?>
                                        <option value="<?= $u['id']; ?>" <?= $selected; ?>><?= htmlspecialchars($u['nama']); ?></option>
                                        <?php
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label class="form-label fw-semibold text-dark">Password Baru</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fas fa-lock text-muted"></i></span>
                                <input type="password" id="edit_password" name="password" class="form-control" placeholder="Kosongkan jika tidak ingin mengubah password">
                                <button class="btn btn-outline-secondary" type="button" onclick="toggleEditPassword()">
                                    <i class="fas fa-eye" id="edit_pass_icon"></i>
                                </button>
                            </div>
                            <small class="text-muted"><i class="fas fa-info-circle"></i> Biarkan kosong jika password tetap yang lama.</small>
                        </div>

                        <div class="form-group mb-3">
                            <label class="form-label fw-semibold text-dark">Level Akses <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fas fa-user-shield text-muted"></i></span>
                                <select name="level" class="form-select" required>
                                    <option value="User" <?= ($userData['level'] === 'User') ? 'selected' : ''; ?>>User (Auditi / Staf Unit)</option>
                                    <option value="Kepala" <?= ($userData['level'] === 'Kepala') ? 'selected' : ''; ?>>Kepala (Kepala Unit / Ketua Prodi)</option>
                                </select>
                            </div>
                        </div>

                        <div class="mt-4 pt-2">
                            <button type="submit" name="update_user" class="btn btn-primary px-4 me-2">
                                <i class="fas fa-save me-1"></i> Simpan Perubahan
                            </button>
                            <a href="master?hal=user" class="btn btn-outline-secondary px-3">
                                Batal
                            </a>
                        </div>
                    </div>
                </div>
            </form>
            <script>
                function toggleEditPassword() {
                    const passInput = document.getElementById('edit_password');
                    const passIcon = document.getElementById('edit_pass_icon');
                    if (passInput.type === 'password') {
                        passInput.type = 'text';
                        passIcon.classList.remove('fa-eye');
                        passIcon.classList.add('fa-eye-slash');
                    } else {
                        passInput.type = 'password';
                        passIcon.classList.remove('fa-eye-slash');
                        passIcon.classList.add('fa-eye');
                    }
                }
            </script>
            <?php
        }
        ?>
    </div>
</div>