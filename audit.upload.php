<?php
include 'nav.php';
include "audit.validasi.php";
$uploadMessage = '';
if(isset($_POST['upload'])) {
    $kodeaudit = mysqli_real_escape_string($kon, $_POST['kodeaudit'] ?? '');
    $upt = $_FILES['ami']['tmp_name'] ?? '';
    $upn = basename($_FILES['ami']['name'] ?? '');
    $ext = strtolower(pathinfo($upn, PATHINFO_EXTENSION));

    if($kodeaudit === '' || $upt === '' || $ext !== 'pdf') {
        $uploadMessage = '<div class="alert alert-danger">Upload gagal. File wajib berupa PDF.</div>';
    } else {
        $current = mysqli_fetch_array(mysqli_query($kon, "SELECT berkas FROM audit WHERE kodeaudit='$kodeaudit'"));
        $filename = $kodeaudit.'L_'.$upn;
        $filenameSql = mysqli_real_escape_string($kon, $filename);
        $upload = mysqli_query($kon, "UPDATE audit SET berkas='$filenameSql' WHERE kodeaudit='$kodeaudit'");
        if($upload && move_uploaded_file($upt, "assets/laporan/".$filename)) {
            if(!empty($current['berkas']) && $current['berkas'] !== $filename && file_exists("assets/laporan/".$current['berkas'])) {
                unlink("assets/laporan/".$current['berkas']);
            }
            $uploadMessage = '<div class="alert alert-success">Laporan berhasil diunggah.</div>';
        } else {
            $uploadMessage = '<div class="alert alert-danger">Laporan gagal diunggah.</div>';
        }
    }
}
$ka = htmlspecialchars($_GET['ka'] ?? '');
$et = htmlspecialchars($_GET['et'] ?? '');
$gk = mysqli_fetch_array(mysqli_query($kon, "SELECT * FROM audit WHERE kodeaudit='".mysqli_real_escape_string($kon, $_GET['ka'] ?? '')."'"));
?>
<style>
    .audit-upload-card{border:1px solid #e5e7eb;border-radius:1rem;background:#fff;box-shadow:0 16px 36px rgba(15,23,42,.06);overflow:hidden}
    .audit-upload-head{display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;padding:1.15rem;background:linear-gradient(135deg,#f8fafc 0%,#eef6ff 100%);border-bottom:1px solid #e5e7eb}
    .audit-upload-head h4{font-weight:900;color:#0f172a;margin:0}
    .audit-upload-body{padding:1rem}
    .audit-upload-drop{border:1px dashed #93c5fd;border-radius:.9rem;background:#eff6ff;padding:1rem}
    .audit-file-current{border:1px solid #e2e8f0;border-radius:.85rem;background:#f8fafc;padding:1rem}
</style>
<div id="main">
    <header><a href="#" class="burger-btn d-block p-2"><i class="fas fa-bars"></i> Menu</a></header>
    <div class="page-content">
        <section class="row">
            <div class="col-12">
                <div class="audit-upload-card">
                    <div class="audit-upload-head">
                        <div>
                            <span class="badge bg-primary mb-2">Upload Dokumen</span>
                            <h4>Laporan Audit #<?= $ka; ?></h4>
                            <p class="text-muted mb-0">Unggah file final Laporan AMI dalam format PDF.</p>
                        </div>
                        <a class="btn btn-outline-secondary btn-sm" href="audit.progres?ka=<?= $ka; ?>&et=<?= $et; ?>"><i class="fas fa-arrow-left me-1"></i> Kembali</a>
                    </div>
                    <div class="audit-upload-body">
                        <?= $uploadMessage; ?>
                        <div class="row g-3">
                            <div class="col-lg-6">
                                <div class="audit-upload-drop">
                                    <form method="post" enctype="multipart/form-data">
                                        <input type="hidden" name="idaudit" value="<?= (int)$cto['id']; ?>">
                                        <input type="hidden" name="kodeaudit" value="<?= htmlspecialchars($cto['kodeaudit']); ?>">
                                        <label class="form-label fw-bold">File Laporan AMI</label>
                                        <input type="file" name="ami" class="form-control" required accept="application/pdf">
                                        <div class="form-text">Format PDF. File baru akan menggantikan laporan sebelumnya.</div>
                                        <button class="btn btn-primary btn-sm mt-3" name="upload"><i class="fas fa-upload me-1"></i> Upload Laporan</button>
                                    </form>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="audit-file-current h-100">
                                    <div class="fw-bold mb-2">Laporan Saat Ini</div>
                                    <?php if(!empty($gk['berkas'])): ?>
                                        <p class="mb-2"><?= htmlspecialchars($gk['berkas']); ?></p>
                                        <a class="btn btn-success btn-sm" href="assets/laporan/<?= htmlspecialchars($gk['berkas']); ?>" target="_blank"><i class="fas fa-download me-1"></i> Download</a>
                                    <?php else: ?>
                                        <div class="alert alert-light border mb-0">Belum ada laporan yang diunggah.</div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>
<?php include 'foot.php'; ?>
