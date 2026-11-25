<?php
include 'nav.php';
$kode = $_GET['ka'] ?? '';
$kodeSafe = htmlspecialchars($kode);

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
    :root {
        --primary: #4f46e5;
        --primary-light: #818cf8;
        --bg-gradient: linear-gradient(135deg, #eef2ff 0%, #ffffff 100%);
        --card-bg: rgba(255, 255, 255, 0.9);
        --text-main: #1e293b;
        --text-muted: #64748b;
    }
    .audit-auditor-card {
        border: 1px solid rgba(226, 232, 240, 0.8);
        border-radius: 1.25rem;
        background: var(--card-bg);
        backdrop-filter: blur(10px);
        box-shadow: 0 10px 25px -5px rgba(79, 70, 229, 0.1);
        overflow: hidden;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .audit-auditor-head {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 1.5rem;
        padding: 2rem;
        background: var(--bg-gradient);
        border-bottom: 1px solid #e0e7ff;
        overflow: hidden;
        z-index: 1;
    }
    .audit-auditor-head::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -5%;
        width: 300px;
        height: 300px;
        background: radial-gradient(circle, rgba(99,102,241,0.15) 0%, rgba(255,255,255,0) 70%);
        z-index: -1;
        border-radius: 50%;
    }
    .audit-auditor-head h4 {
        font-weight: 900;
        color: var(--text-main);
        font-size: 1.8rem;
        margin-bottom: 0.25rem;
    }
    .audit-auditor-body {
        padding: 2rem;
    }
    .audit-auditor-panel {
        border: 1px solid #e2e8f0;
        border-radius: 1rem;
        background: #f8fafc;
        padding: 1.5rem;
        box-shadow: inset 0 2px 4px 0 rgba(0, 0, 0, 0.02);
    }
    .form-label {
        color: var(--text-main);
        font-size: 0.95rem;
    }
    .select2-container--default .select2-selection--single {
        border: 1px solid #cbd5e1 !important;
        border-radius: 0.5rem !important;
        height: 42px !important;
        padding: 0.3rem !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 40px !important;
    }
    .btn-modern {
        background: #4f46e5;
        color: white;
        border: none;
        font-weight: 600;
        border-radius: 0.5rem;
        padding: 0.6rem 1.2rem;
        transition: all 0.2s;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 4px 6px -1px rgba(79, 70, 229, 0.2);
    }
    .btn-modern:hover {
        background: #4338ca;
        transform: translateY(-2px);
        box-shadow: 0 10px 15px -3px rgba(79, 70, 229, 0.3);
        color: white;
    }
</style>
<div id="main">
    <header><a href="#" class="burger-btn d-block p-2"><i class="fas fa-bars"></i> Menu</a></header>
    <div class="page-content">
        <section class="row">
            <div class="col-12">
                <div class="audit-auditor-card">
                    <div class="audit-auditor-head">
                        <div>
                            <span class="badge mb-2" style="background: linear-gradient(90deg, #4f46e5, #ec4899); border-radius: 1rem; padding: 0.4rem 0.8rem; font-weight: 700;">Tim Auditor</span>
                            <h4>Pilih Auditor #<?= $kodeSafe; ?></h4>
                            <p class="text-muted mb-0">Tentukan ketua auditor dan anggota untuk audit ini.</p>
                        </div>
                        <a class="btn btn-white bg-white border btn-sm shadow-sm text-dark d-flex align-items-center" href="audit" style="font-weight: 600; border-radius: 0.5rem;"><i class="fas fa-arrow-left me-2"></i> Kembali</a>
                    </div>
                    <div class="audit-auditor-body">
                        <?php include 'audit.auditor.proses.php'; ?>
                        <form method="post" class="audit-auditor-panel">
                            <input type="hidden" name="kodeauditor" value="<?= $kodeSafe; ?>">
                            <div class="row">
                                <div class="col-lg-4 mb-3">
                                    <label class="form-label fw-bold">Ketua Auditor</label>
                                    <select name="kauditor" class="form-control js-auditor-select" required>
                                        <?php audit_auditor_options($localUsers, ''); ?>
                                    </select>
                                </div>
                                <div class="col-lg-4 mb-3">
                                    <label class="form-label fw-bold">Anggota Auditor 1</label>
                                    <select name="auditor1" class="form-control js-auditor-select">
                                        <?php audit_auditor_options($localUsers, '0'); ?>
                                    </select>
                                </div>
                                <div class="col-lg-4 mb-3">
                                    <label class="form-label fw-bold">Anggota Auditor 2</label>
                                    <select name="auditor2" class="form-control js-auditor-select">
                                        <?php audit_auditor_options($localUsers, '0'); ?>
                                    </select>
                                </div>
                            </div>
                            <div class="d-flex flex-wrap gap-3 mt-4">
                                <button class="btn-modern" name="simpan"><i class="fas fa-save me-2"></i> Simpan Tim Auditor</button>
                                <a class="btn btn-white bg-white border shadow-sm text-dark d-flex align-items-center" href="audit" style="font-weight: 600; border-radius: 0.5rem; padding: 0.6rem 1.2rem;">Batal</a>
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
