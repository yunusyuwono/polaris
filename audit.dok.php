<?php
include 'nav.php';
include "audit.validasi.php";

$dokMessage = '';
if(isset($_POST['upload'])) {
    $kodeaudit = mysqli_real_escape_string($kon, $_POST['kodeaudit'] ?? '');
    $fields = ['oc' => 'dokoc', 'pa' => 'dokpa', 'cc' => 'dokcc', 'absen' => 'dokabsen'];
    $names = [];
    $valid = $kodeaudit !== '';

    foreach($fields as $input => $column) {
        $name = basename($_FILES[$input]['name'] ?? '');
        $tmp = $_FILES[$input]['tmp_name'] ?? '';
        $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        if($name === '' || $tmp === '' || !in_array($ext, ['jpg', 'jpeg', 'png'], true)) {
            $valid = false;
        }
        $names[$column] = $name;
    }

    if(!$valid) {
        $dokMessage = '<div class="alert alert-danger">Upload gagal. Semua dokumentasi wajib berupa JPG/JPEG/PNG.</div>';
    } else {
        $exists = mysqli_fetch_array(mysqli_query($kon, "SELECT id FROM audit_dok WHERE kodeaudit='$kodeaudit'"));
        $dokoc = mysqli_real_escape_string($kon, $names['dokoc']);
        $dokpa = mysqli_real_escape_string($kon, $names['dokpa']);
        $dokcc = mysqli_real_escape_string($kon, $names['dokcc']);
        $dokabsen = mysqli_real_escape_string($kon, $names['dokabsen']);

        if($exists) {
            $save = mysqli_query($kon, "UPDATE audit_dok SET dokoc='$dokoc', dokpa='$dokpa', dokcc='$dokcc', dokabsen='$dokabsen' WHERE kodeaudit='$kodeaudit'");
        } else {
            $save = mysqli_query($kon, "INSERT INTO audit_dok (kodeaudit,dokoc,dokpa,dokcc,dokabsen) VALUES ('$kodeaudit','$dokoc','$dokpa','$dokcc','$dokabsen')");
        }

        if($save) {
            foreach($fields as $input => $column) {
                move_uploaded_file($_FILES[$input]['tmp_name'], "assets/auditdok/".$kodeaudit."_".$names[$column]);
            }
            $dokMessage = '<div class="alert alert-success">Dokumentasi audit berhasil diunggah.</div>';
        } else {
            $dokMessage = '<div class="alert alert-danger">Dokumentasi audit gagal disimpan.</div>';
        }
    }
}

$ka = htmlspecialchars($_GET['ka'] ?? '');
$et = htmlspecialchars($_GET['et'] ?? '');
$kodeSql = mysqli_real_escape_string($kon, $_GET['ka'] ?? '');
$gk = mysqli_fetch_array(mysqli_query($kon, "SELECT * FROM audit_dok WHERE kodeaudit='$kodeSql'"));
$items = [
    ['input' => 'oc', 'field' => 'dokoc', 'label' => 'Opening Ceremony'],
    ['input' => 'pa', 'field' => 'dokpa', 'label' => 'Pelaksanaan Audit'],
    ['input' => 'cc', 'field' => 'dokcc', 'label' => 'Closing Ceremony'],
    ['input' => 'absen', 'field' => 'dokabsen', 'label' => 'Daftar Hadir'],
];
?>
<style>
    .audit-doc-card{border:1px solid #e5e7eb;border-radius:1rem;background:#fff;box-shadow:0 16px 36px rgba(15,23,42,.06);overflow:hidden}
    .audit-doc-head{display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;padding:1.15rem;background:linear-gradient(135deg,#f8fafc 0%,#eef6ff 100%);border-bottom:1px solid #e5e7eb}
    .audit-doc-head h4{font-weight:900;color:#0f172a;margin:0}
    .audit-doc-body{padding:1rem}
    .audit-doc-upload{border:1px dashed #93c5fd;border-radius:.9rem;background:#eff6ff;padding:1rem}
    .audit-doc-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:.85rem}
    .audit-doc-preview{border:1px solid #e2e8f0;border-radius:.85rem;background:#f8fafc;padding:.75rem;min-height:100%}
    .audit-doc-preview img{width:100%;height:150px;object-fit:cover;border-radius:.65rem;border:1px solid #e5e7eb;background:#fff}
    @media(max-width:991.98px){.audit-doc-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.audit-doc-head{flex-direction:column}}
    @media(max-width:575.98px){.audit-doc-grid{grid-template-columns:1fr}}
</style>
<div id="main">
    <header><a href="#" class="burger-btn d-block p-2"><i class="fas fa-bars"></i> Menu</a></header>
    <div class="page-content">
        <section class="row">
            <div class="col-12">
                <div class="audit-doc-card">
                    <div class="audit-doc-head">
                        <div>
                            <span class="badge bg-primary mb-2">Dokumentasi</span>
                            <h4>Dokumentasi Audit #<?= $ka; ?></h4>
                            <p class="text-muted mb-0">Upload dokumentasi kegiatan audit dan lihat preview file saat ini.</p>
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            <a class="btn btn-outline-secondary btn-sm" href="audit.progres?ka=<?= $ka; ?>&et=<?= $et; ?>"><i class="fas fa-arrow-left me-1"></i> Kembali</a>
                            <button onclick="printDiv('printdok')" class="btn btn-primary btn-sm"><i class="fas fa-print me-1"></i> Print</button>
                        </div>
                    </div>
                    <div class="audit-doc-body">
                        <?= $dokMessage; ?>
                        <div class="audit-doc-upload mb-3">
                            <form method="post" enctype="multipart/form-data">
                                <input type="hidden" name="idaudit" value="<?= (int)$cto['id']; ?>">
                                <input type="hidden" name="kodeaudit" value="<?= htmlspecialchars($cto['kodeaudit']); ?>">
                                <div class="row">
                                    <?php foreach($items as $item): ?>
                                        <div class="col-md-6 col-xl-3 mb-3">
                                            <label class="form-label fw-bold"><?= htmlspecialchars($item['label']); ?></label>
                                            <input type="file" name="<?= $item['input']; ?>" class="form-control" required accept="image/jpeg,image/png,image/jpg">
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <div class="d-flex flex-wrap align-items-center gap-2">
                                    <button class="btn btn-primary btn-sm" name="upload"><i class="fas fa-upload me-1"></i> Upload Dokumentasi</button>
                                    <small class="text-muted">Format JPG/JPEG/PNG. Ukuran disarankan maksimal 1 MB per file.</small>
                                </div>
                            </form>
                        </div>

                        <div id="printdok">
                            <h5 class="fw-bold mb-3">Dokumentasi Audit <?= htmlspecialchars($gk['kodeaudit'] ?? $ka); ?></h5>
                            <div class="audit-doc-grid">
                                <?php foreach($items as $item): ?>
                                    <?php $file = $gk[$item['field']] ?? ''; ?>
                                    <div class="audit-doc-preview">
                                        <div class="fw-bold mb-2"><?= htmlspecialchars($item['label']); ?></div>
                                        <?php if(!empty($file) && file_exists("assets/auditdok/".$kodeSql."_".$file)): ?>
                                            <img src="assets/auditdok/<?= htmlspecialchars($kodeSql.'_'.$file); ?>" alt="<?= htmlspecialchars($item['label']); ?>">
                                            <div class="small text-muted mt-2"><?= htmlspecialchars($file); ?></div>
                                        <?php else: ?>
                                            <div class="alert alert-light border mb-0">Belum ada file.</div>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>
<?php include 'foot.php'; ?>
<script type="text/javascript">
    function printDiv(divName) {
        var printContents = document.getElementById(divName).innerHTML;
        var originalContents = document.body.innerHTML;
        document.body.innerHTML = printContents;
        window.print();
        document.body.innerHTML = originalContents;
        window.location.reload();
    }
</script>
