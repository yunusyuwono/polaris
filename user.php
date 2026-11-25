<?php
// User Management Page - POLARIS Admin
$totalUserCount = 0;
$totalKepalaCount = 0;
$totalStaffCount = 0;
$totalUnitCount = 0;

$countQ = mysqli_query($kon, "SELECT 
    COUNT(*) as total_users,
    SUM(CASE WHEN level='Kepala' THEN 1 ELSE 0 END) as total_kepala,
    SUM(CASE WHEN level='User' THEN 1 ELSE 0 END) as total_staff
    FROM user");
if ($countQ && $row = mysqli_fetch_assoc($countQ)) {
    $totalUserCount = (int)($row['total_users'] ?? 0);
    $totalKepalaCount = (int)($row['total_kepala'] ?? 0);
    $totalStaffCount = (int)($row['total_staff'] ?? 0);
}

$unitCountQ = mysqli_query($kon, "SELECT COUNT(*) as total_units FROM unit");
if ($unitCountQ && $row = mysqli_fetch_assoc($unitCountQ)) {
    $totalUnitCount = (int)($row['total_units'] ?? 0);
}
?>

<style>
    .user-stat-card {
        background: #ffffff;
        border-radius: 0.85rem;
        border: 1px solid #e2e8f0;
        padding: 1.15rem 1.25rem;
        box-shadow: 0 4px 12px rgba(15, 23, 42, 0.03);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .user-stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(15, 23, 42, 0.06);
    }
    .user-stat-icon {
        width: 44px;
        height: 44px;
        border-radius: 0.65rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
    }
    .form-tambah-box {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 0.85rem;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
    }
</style>

<!-- Stat Cards -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="user-stat-card d-flex align-items-center justify-content-between">
            <div>
                <span class="text-muted small fw-semibold text-uppercase">Total Pengguna</span>
                <h3 class="fw-bold mb-0 text-dark"><?= number_format($totalUserCount); ?></h3>
            </div>
            <div class="user-stat-icon bg-primary bg-opacity-10 text-primary">
                <i class="fas fa-users"></i>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="user-stat-card d-flex align-items-center justify-content-between">
            <div>
                <span class="text-muted small fw-semibold text-uppercase">Kepala Unit / Prodi</span>
                <h3 class="fw-bold mb-0 text-primary"><?= number_format($totalKepalaCount); ?></h3>
            </div>
            <div class="user-stat-icon bg-info bg-opacity-10 text-info">
                <i class="fas fa-user-tie"></i>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="user-stat-card d-flex align-items-center justify-content-between">
            <div>
                <span class="text-muted small fw-semibold text-uppercase">Staf / Auditi</span>
                <h3 class="fw-bold mb-0 text-success"><?= number_format($totalStaffCount); ?></h3>
            </div>
            <div class="user-stat-icon bg-success bg-opacity-10 text-success">
                <i class="fas fa-user-check"></i>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="user-stat-card d-flex align-items-center justify-content-between">
            <div>
                <span class="text-muted small fw-semibold text-uppercase">Unit / Prodi Terdaftar</span>
                <h3 class="fw-bold mb-0 text-warning"><?= number_format($totalUnitCount); ?></h3>
            </div>
            <div class="user-stat-icon bg-warning bg-opacity-10 text-warning">
                <i class="fas fa-building"></i>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-header bg-white border-bottom py-3 d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3">
        <div>
            <h5 class="mb-1 text-dark fw-bold"><i class="fas fa-user-cog text-primary me-2"></i> Kelola Data Pengguna</h5>
            <p class="text-muted small mb-0">Kelola akun staf, auditi, dan kepala unit untuk akses ke portal POLARIS.</p>
        </div>
        <button class="btn btn-primary d-inline-flex align-items-center gap-2 px-3 py-2 rounded-3 shadow-sm" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTambahUser" aria-expanded="false" aria-controls="collapseTambahUser" id="btnToggleTambah">
            <i class="fas fa-user-plus"></i>
            <span>Tambah User Baru</span>
        </button>
    </div>

    <div class="card-body p-4">
        <!-- Process feedback include -->
        <?php 
        include "user.buat.proses.php";
        ?>

        <!-- Collapsible Form Tambah User -->
        <div class="collapse <?= isset($_POST['simpan_user']) ? 'show' : ''; ?>" id="collapseTambahUser">
            <div class="form-tambah-box">
                <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                    <h6 class="fw-bold text-dark mb-0"><i class="fas fa-plus-circle text-primary me-1"></i> Form Tambah User Pengguna Baru</h6>
                    <button type="button" class="btn-close" data-bs-toggle="collapse" data-bs-target="#collapseTambahUser" aria-label="Close"></button>
                </div>
                <form method="post" autocomplete="off">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="form-label fw-semibold text-dark">NIP / Username <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white"><i class="fas fa-id-card text-muted"></i></span>
                                    <input type="text" name="nip" class="form-control bg-white" placeholder="Contoh: 198501012010121001 atau username" required value="<?= htmlspecialchars($_POST['nip'] ?? ''); ?>">
                                </div>
                                <small class="text-muted">NIP/Username digunakan sebagai ID masuk akun.</small>
                            </div>

                            <div class="form-group mb-3">
                                <label class="form-label fw-semibold text-dark">Nama Lengkap & Gelar <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white"><i class="fas fa-user text-muted"></i></span>
                                    <input type="text" name="nama" class="form-control bg-white" placeholder="Contoh: Dr. Nama Lengkap, M.Kes" required value="<?= htmlspecialchars($_POST['nama'] ?? ''); ?>">
                                </div>
                            </div>

                            <div class="form-group mb-3">
                                <label class="form-label fw-semibold text-dark">Unit / Program Studi</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white"><i class="fas fa-building text-muted"></i></span>
                                    <select name="unit" class="form-select bg-white">
                                        <option value="">-- Pilih Unit / Prodi --</option>
                                        <?php 
                                        $unitQuery = mysqli_query($kon, "SELECT * FROM unit ORDER BY nama ASC");
                                        while ($u = mysqli_fetch_array($unitQuery)) {
                                            $selected = (isset($_POST['unit']) && $_POST['unit'] == $u['id']) ? 'selected' : '';
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
                                <label class="form-label fw-semibold text-dark">Password <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white"><i class="fas fa-lock text-muted"></i></span>
                                    <input type="password" id="input_tambah_password" name="password" class="form-control bg-white" placeholder="Masukkan password pengguna" required>
                                    <button class="btn btn-outline-secondary" type="button" onclick="toggleTambahPassword()">
                                        <i class="fas fa-eye" id="tambah_pass_icon"></i>
                                    </button>
                                </div>
                                <small class="text-muted">Password akan dienkripsi dengan aman di database.</small>
                            </div>

                            <div class="form-group mb-3">
                                <label class="form-label fw-semibold text-dark">Level / Peran Akses <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white"><i class="fas fa-user-shield text-muted"></i></span>
                                    <select name="level" class="form-select bg-white" required>
                                        <option value="User" <?= (isset($_POST['level']) && $_POST['level'] === 'User') ? 'selected' : ''; ?>>User (Auditi / Staf Unit)</option>
                                        <option value="Kepala" <?= (isset($_POST['level']) && $_POST['level'] === 'Kepala') ? 'selected' : ''; ?>>Kepala (Kepala Unit / Ketua Prodi)</option>
                                    </select>
                                </div>
                            </div>

                            <div class="mt-4 pt-2 d-flex gap-2">
                                <button type="submit" name="simpan_user" class="btn btn-primary px-4">
                                    <i class="fas fa-save me-1"></i> Simpan User
                                </button>
                                <button type="button" class="btn btn-outline-secondary" data-bs-toggle="collapse" data-bs-target="#collapseTambahUser">
                                    Batal
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Table Data Users -->
        <div class="table-responsive">
            <table class="table table-hover table-striped align-middle" id="tableUsers">
                <thead class="table-light">
                    <tr>
                        <th style="width: 50px;">No.</th>
                        <th>NIP / Username</th>
                        <th>Nama Lengkap</th>
                        <th>Unit / Prodi</th>
                        <th style="width: 130px;">Level Akses</th>
                        <th style="width: 120px;" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $userList = mysqli_query($kon, "SELECT user.id as user_id, user.nip, user.nama as namauser, user.level, unit.nama as namaunit 
                        FROM user 
                        LEFT JOIN unit ON user.unit = unit.id 
                        ORDER BY user.nama ASC");
                    $no = 1;
                    while ($peg = mysqli_fetch_array($userList)) {
                        $isKepala = ($peg['level'] === 'Kepala');
                        $badgeClass = $isKepala ? 'bg-primary' : 'bg-secondary';
                        $unitLabel = !empty($peg['namaunit']) ? htmlspecialchars($peg['namaunit']) : '<span class="text-muted fst-italic">- Belum Diatur -</span>';
                        ?>
                        <tr>
                            <td><?= $no; ?></td>
                            <td>
                                <span class="fw-semibold text-dark"><?= htmlspecialchars($peg['nip']); ?></span>
                            </td>
                            <td>
                                <span class="fw-bold text-dark"><?= htmlspecialchars($peg['namauser']); ?></span>
                            </td>
                            <td><?= $unitLabel; ?></td>
                            <td>
                                <span class="badge <?= $badgeClass; ?> px-2 py-1 rounded-pill">
                                    <i class="fas <?= $isKepala ? 'fa-user-tie' : 'fa-user'; ?> me-1"></i> <?= htmlspecialchars($peg['level']); ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="?hal=edituser&id=<?= $peg['user_id']; ?>" class="btn btn-outline-primary" title="Edit Data User">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" class="btn btn-outline-danger" title="Hapus User" onclick="confirmDeleteUser(<?= $peg['user_id']; ?>, '<?= addslashes(htmlspecialchars($peg['namauser'])); ?>')">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php
                        $no++;
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    function toggleTambahPassword() {
        const passInput = document.getElementById('input_tambah_password');
        const passIcon = document.getElementById('tambah_pass_icon');
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

    function confirmDeleteUser(id, name) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Hapus User?',
                html: `Apakah Anda yakin ingin menghapus user <strong>${name}</strong>?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="fas fa-trash"></i> Ya, Hapus',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = `user.hapus.php?id=${id}`;
                }
            });
        } else {
            if (confirm(`Apakah Anda yakin ingin menghapus user ${name}?`)) {
                window.location.href = `user.hapus.php?id=${id}`;
            }
        }
    }
</script>