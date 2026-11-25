<?php
include 'nav.php';
include "audit.validasi.php";

$kaRaw = $_GET['ka'] ?? '';
$kaSql = mysqli_real_escape_string($kon, $kaRaw);
$ka = htmlspecialchars($kaRaw);
$et = htmlspecialchars($_GET['et'] ?? '');
$message = '';

if(isset($_GET['hapus']) && !empty($_GET['berkas'])) {
    $file = basename($_GET['berkas']);
    if(file_exists("assets/spsa/".$file)) {
        unlink("assets/spsa/".$file);
    }
    mysqli_query($kon, "UPDATE audit SET spsa='' WHERE kodeaudit='$kaSql'");
    $message = '<div class="alert alert-success">File SPSA berhasil dihapus.</div>';
}

if(isset($_POST['upload'])) {
    $kodeaudit = mysqli_real_escape_string($kon, $_POST['kodeaudit'] ?? '');
    $tmp = $_FILES['ami']['tmp_name'] ?? '';
    $name = basename($_FILES['ami']['name'] ?? '');
    $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
    if($kodeaudit === '' || $tmp === '' || $ext !== 'pdf') {
        $message = '<div class="alert alert-danger">Upload gagal. File SPSA wajib berupa PDF.</div>';
    } else {
        $filename = $kodeaudit.'SPSA_'.$name;
        $filenameSql = mysqli_real_escape_string($kon, $filename);
        $save = mysqli_query($kon, "UPDATE audit SET spsa='$filenameSql' WHERE kodeaudit='$kodeaudit'");
        if($save && move_uploaded_file($tmp, "assets/spsa/".$filename)) {
            $message = '<div class="alert alert-success">SPSA berhasil diunggah.</div>';
        } else {
            $message = '<div class="alert alert-danger">SPSA gagal diunggah.</div>';
        }
    }
}

$gk = mysqli_fetch_array(mysqli_query($kon, "SELECT * FROM audit WHERE kodeaudit='$kaSql'"));
?>
<style>
    .audit-spsa-card{border:1px solid #e5e7eb;border-radius:1rem;background:#fff;box-shadow:0 16px 36px rgba(15,23,42,.06);overflow:hidden}
    .audit-spsa-head{display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;padding:1.15rem;background:linear-gradient(135deg,#f8fafc 0%,#eef6ff 100%);border-bottom:1px solid #e5e7eb}
    .audit-spsa-head h4{font-weight:900;color:#0f172a;margin:0}
    .audit-spsa-body{padding:1rem}
    .audit-spsa-panel{border:1px dashed #93c5fd;border-radius:.9rem;background:#eff6ff;padding:1rem}
    .audit-current-file{border:1px solid #e2e8f0;border-radius:.85rem;background:#f8fafc;padding:1rem}
</style>
<div id="main">
    <header><a href="#" class="burger-btn d-block p-2"><i class="fas fa-bars"></i> Menu</a></header>
    <div class="page-content">
        <section class="row">
            <div class="col-12">
                <div class="audit-spsa-card">
                    <div class="audit-spsa-head">
                        <div>
                            <span class="badge bg-primary mb-2">SPSA</span>
                            <h4>Surat Pernyataan Selesai Audit #<?= $ka; ?></h4>
                            <p class="text-muted mb-0">Kelola dokumen surat pernyataan selesai audit dalam format PDF.</p>
                        </div>
                        <a class="btn btn-outline-secondary btn-sm" href="audit.progres?ka=<?= $ka; ?>&et=<?= $et; ?>"><i class="fas fa-arrow-left me-1"></i> Kembali</a>
                    </div>
                    <div class="audit-spsa-body">
                        <?= $message; ?>
                        <?php if(empty($gk['berkas'])): ?>
                            <div class="alert alert-warning mb-0">Upload laporan audit terlebih dahulu sebelum membuat SPSA.</div>
                        <?php else: ?>
                            <div class="row g-3">
                                <div class="col-lg-6">
                                    <div class="audit-spsa-panel">
                                        <a class="btn btn-outline-primary btn-sm mb-3" href="audit.surat?ka=<?= $ka; ?>&et=<?= $et; ?>" target="_blank"><i class="fas fa-download me-1"></i> Download Template SPSA</a>
                                        <form method="post" enctype="multipart/form-data">
                                            <input type="hidden" name="idaudit" value="<?= (int)$cto['id']; ?>">
                                            <input type="hidden" name="kodeaudit" value="<?= htmlspecialchars($cto['kodeaudit']); ?>">
                                            <label class="form-label fw-bold">Upload SPSA Ditandatangani</label>
                                            <input type="file" name="ami" class="form-control" required accept="application/pdf">
                                            <button class="btn btn-primary btn-sm mt-3" name="upload"><i class="fas fa-upload me-1"></i> Upload SPSA</button>
                                        </form>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="audit-current-file h-100">
                                        <div class="fw-bold mb-2">File SPSA Saat Ini</div>
                                        <?php if(!empty($gk['spsa'])): ?>
                                            <p><?= htmlspecialchars($gk['spsa']); ?></p>
                                            <a class="btn btn-success btn-sm" href="assets/spsa/<?= htmlspecialchars($gk['spsa']); ?>" target="_blank"><i class="fas fa-download me-1"></i> Download</a>
                                            <a class="btn btn-outline-danger btn-sm" href="?hapus&ka=<?= $ka; ?>&et=<?= $et; ?>&berkas=<?= urlencode($gk['spsa']); ?>" onclick="return confirm('Hapus file SPSA ini?')"><i class="fas fa-trash me-1"></i> Hapus</a>
                                        <?php else: ?>
                                            <div class="alert alert-light border mb-0">Belum ada file SPSA yang diunggah.</div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>
<?php include 'foot.php'; ?>
