<?php 
include 'nav.php'; 

// Fetch local users for auditor selection
$localUsers = [];
$users = mysqli_query($kon, "SELECT id, nip, nama FROM user ORDER BY nama ASC");
while($users && $user = mysqli_fetch_assoc($users)) {
    $localUsers[] = $user;
}

function audit_auditor_options($localUsers, $emptyValue = '') {
    echo '<option value="'.$emptyValue.'">Tidak dipilih</option>';
    foreach($localUsers as $user) {
        $id = (int)$user['id'];
        $label = htmlspecialchars(trim(($user['nip'] ?? '').' '.$user['nama']));
        echo "<option value=\"$id\">$label</option>";
    }
}
?>
<style>
    .audit-form-card{border:1px solid #e5e7eb;border-radius:1rem;background:#fff;box-shadow:0 16px 36px rgba(15,23,42,.06);overflow:hidden}
    .audit-form-head{display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;padding:1.2rem;background:linear-gradient(135deg,#f8fafc 0%,#eef6ff 100%);border-bottom:1px solid #e5e7eb}
    .audit-form-head h4{font-weight:900;color:#0f172a;margin:0}
    .audit-form-body{padding:1rem}
    .audit-form-section{border:1px solid #e2e8f0;border-radius:.85rem;background:#f8fafc;padding:1rem;margin-bottom:1rem;}
    .audit-form-section-title{font-weight:900;color:#0f172a;margin-bottom:.75rem}
    .audit-form-actions{display:flex;flex-wrap:wrap;gap:.5rem;margin-top:1rem}
    .select2-container--default .select2-selection--single {
        border: 1px solid #ced4da !important;
        border-radius: 0.375rem !important;
        height: 38px !important;
        padding: 0.2rem !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 36px !important;
    }
</style>
<div id="main">
    <header>
        <a href="#" class="burger-btn d-block p-2"><i class="fas fa-bars"></i> Menu</a>
    </header>
    <div class="page-content">
        <section class="row">
            <div class="col-12">
                <div class="audit-form-card">
                    <div class="audit-form-head">
                        <div>
                            <span class="badge bg-primary mb-2">Audit Baru</span>
                            <h4>Buat Audit & Tim Auditor</h4>
                            <p class="text-muted mb-0">Isi detail audit dan tentukan tim auditor sekaligus.</p>
                        </div>
                        <a class="btn btn-outline-secondary btn-sm" href="audit"><i class="fas fa-arrow-left me-1"></i> Kembali</a>
                    </div>
                    <div class="audit-form-body">
                        <?php include 'audit.buat.proses.php'; ?>
                        <form method="post">
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="audit-form-section">
                                        <div class="audit-form-section-title">Informasi Audit</div>
                                        <div class="row">
                                            <div class="col-lg-6 mb-3">
                                                <label class="form-label">Unit Auditi</label>
                                                <select name="unit" class="form-control" required>
                                                    <option value="">Pilih Unit Auditi</option>
                                                    <?php
                                                    $unit=mysqli_query($kon,"select * from unit order by nama asc");
                                                    while($u=mysqli_fetch_array($unit)):
                                                    ?>
                                                        <option value="<?= (int)$u['id']; ?>"><?= htmlspecialchars($u['nama']); ?></option>
                                                    <?php endwhile; ?>
                                                </select>
                                            </div>
                                            <div class="col-lg-6 mb-3">
                                                <label class="form-label">Ruang Lingkup</label>
                                                <select name="lingkup" class="form-control" required>
                                                    <option value="">Pilih Ruang Lingkup</option>
                                                    <option value="1">Akademik</option>
                                                    <option value="2">Non-Akademik</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Mulai Audit</label>
                                                <input type="date" name="tglmulai" class="form-control" required>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Selesai Audit</label>
                                                <input type="date" name="tglselesai" class="form-control" required>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-lg-12">
                                    <div class="audit-form-section">
                                        <div class="audit-form-section-title">Tim Auditor</div>
                                        <div class="row">
                                            <div class="col-lg-4 mb-3">
                                                <label class="form-label fw-bold">Ketua Auditor <span class="text-danger">*</span></label>
                                                <select name="kauditor" class="form-control js-auditor-select" required>
                                                    <?php audit_auditor_options($localUsers, ''); ?>
                                                </select>
                                            </div>
                                            <div class="col-lg-4 mb-3">
                                                <label class="form-label fw-bold">Anggota Auditor 1 <span class="text-danger">*</span></label>
                                                <select name="auditor1" class="form-control js-auditor-select" required>
                                                    <?php audit_auditor_options($localUsers, ''); ?>
                                                </select>
                                            </div>
                                            <div class="col-lg-4 mb-3">
                                                <label class="form-label fw-bold">Anggota Auditor 2 (Opsional)</label>
                                                <select name="auditor2" class="form-control js-auditor-select">
                                                    <?php audit_auditor_options($localUsers, '0'); ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="audit-form-actions">
                                <button class="btn btn-primary" name="simpan"><i class="fas fa-save me-1"></i> Simpan Audit & Tim</button>
                                <a class="btn btn-outline-secondary" href="audit">Batal</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>
<?php include 'foot.php'; ?>
<script>
$(document).ready(function() {
    $('.js-auditor-select').select2({ width: '100%' });
});
</script>
