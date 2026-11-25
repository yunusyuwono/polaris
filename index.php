<?php
include 'nav.php';
include 'audit.ui.php';

function digami_count($kon, $sql) {
    $result = mysqli_query($kon, $sql);
    if(!$result) return 0;
    $row = mysqli_fetch_row($result);
    return (int)($row[0] ?? 0);
}

$dashboardAlert = '';

if (isset($_GET['hapusumum'])) {
    $idinfo = (int)$_GET['hapusumum'];
    mysqli_query($kon, "DELETE FROM pengumuman WHERE id='$idinfo'");
    echo '<script>window.location="./";</script>';
    exit;
}

if (isset($_GET['hapusberkas'])) {
    $idberkas = (int)$_GET['hapusberkas'];
    $berkasName = basename($_GET['berkas'] ?? '');
    mysqli_query($kon, "DELETE FROM berkasdepan WHERE id='$idberkas'");
    $berkasPath = 'assets/berkasdepan/' . $berkasName;
    if($berkasName !== '' && file_exists($berkasPath)) {
        unlink($berkasPath);
    }
    echo '<script>window.location="./";</script>';
    exit;
}

if (isset($_POST['simpan'])) {
    $info = mysqli_real_escape_string($kon, trim($_POST['info'] ?? ''));
    if($info !== '') {
        $simpan = mysqli_query($kon, "INSERT INTO pengumuman (info) VALUES ('$info')");
        $dashboardAlert = $simpan
            ? '<div class="alert alert-success">Pengumuman berhasil dibuat.</div>'
            : '<div class="alert alert-danger">Pengumuman gagal dibuat.</div>';
    }
}

if (isset($_POST['upload'])) {
    $nama = mysqli_real_escape_string($kon, trim($_POST['nama'] ?? ''));
    $upname = basename($_FILES['berkas']['name'] ?? '');
    $uptmp = $_FILES['berkas']['tmp_name'] ?? '';
    $status = 'Aktif';

    if($nama !== '' && $upname !== '' && is_uploaded_file($uptmp)) {
        $upl = mysqli_query($kon, "INSERT INTO berkasdepan (namaberkas, berkas, status) VALUES ('$nama', '$upname', '$status')");
        if ($upl && move_uploaded_file($uptmp, "assets/berkasdepan/" . $upname)) {
            $dashboardAlert = '<div class="alert alert-success">Berkas berhasil diupload.</div>';
        } else {
            $dashboardAlert = '<div class="alert alert-danger">Berkas gagal diupload.</div>';
        }
    }
}

$auditYearExpr = "CASE WHEN YEAR(audit.tglmulai)>0 THEN YEAR(audit.tglmulai) ELSE 2000 + CAST(LEFT(audit.kodeaudit, 2) AS UNSIGNED) END";
$yearExpr = "CASE WHEN YEAR(tglmulai)>0 THEN YEAR(tglmulai) ELSE 2000 + CAST(LEFT(kodeaudit, 2) AS UNSIGNED) END";
$yearRows = [];
$yearQuery = mysqli_query($kon, "SELECT DISTINCT $yearExpr AS tahun FROM audit ORDER BY tahun DESC");
if($yearQuery) {
    while($year = mysqli_fetch_assoc($yearQuery)) {
        if(!empty($year['tahun'])) $yearRows[] = (int)$year['tahun'];
    }
}
$currentYear = (int)date('Y');
$activeYear = in_array($currentYear, $yearRows, true) ? $currentYear : ($yearRows[0] ?? $currentYear);

$summaryPengumuman = digami_count($kon, "SELECT COUNT(*) FROM pengumuman");
$summaryBerkas = digami_count($kon, "SELECT COUNT(*) FROM berkasdepan WHERE status='Aktif'");
$summaryUsers = digami_count($kon, "SELECT COUNT(*) FROM user");
$summaryAuditYear = digami_count($kon, "SELECT COUNT(*) FROM audit WHERE $yearExpr='$activeYear'");
$summaryAuditReport = digami_count($kon, "SELECT COUNT(*) FROM audit WHERE $yearExpr='$activeYear' AND berkas<>''");
$summaryPraAudit = digami_count($kon, "SELECT COUNT(*) FROM praaudit WHERE tahun='$activeYear'");
$summaryPraSelesai = digami_count($kon, "SELECT COUNT(*) FROM praaudit WHERE tahun='$activeYear' AND status='Selesai'");
$summaryTemuanOpen = digami_count($kon, "SELECT COUNT(*) FROM audit_kondisi WHERE status='Open'");
$summaryTemuanClosed = digami_count($kon, "SELECT COUNT(*) FROM audit_kondisi WHERE status<>'Open' AND status<>''");

$latestAudits = mysqli_query($kon, "
    SELECT audit.kodeaudit, audit.lingkup, audit.tglmulai, audit.tglselesai, audit.berkas, unit.nama AS namaunit
    FROM audit
    LEFT JOIN unit ON unit.id=audit.unit
    WHERE $auditYearExpr='$activeYear'
    ORDER BY audit.tglmulai DESC, audit.kodeaudit DESC
    LIMIT 6
");
$latestAnnouncements = mysqli_query($kon, "SELECT id, info, entri FROM pengumuman ORDER BY entri DESC LIMIT 5");
$activeFiles = mysqli_query($kon, "SELECT id, namaberkas, berkas FROM berkasdepan WHERE status='Aktif' ORDER BY id DESC LIMIT 8");
?>
<style>
    .admin-shell{display:flex;flex-direction:column;gap:1rem}
    .admin-hero{background:#fff;border:1px solid #e5e7eb;border-radius:.8rem;padding:1.25rem;box-shadow:0 14px 30px rgba(15,23,42,.05)}
    .admin-stat{background:#fff;border:1px solid #e5e7eb;border-radius:.7rem;padding:1rem;height:100%;box-shadow:0 12px 26px rgba(15,23,42,.04)}
    .admin-stat-icon{width:38px;height:38px;border-radius:.6rem;display:inline-flex;align-items:center;justify-content:center;margin-bottom:.75rem;background:#eff6ff;color:#2563eb}
    .admin-panel{background:#fff;border:1px solid #e5e7eb;border-radius:.75rem;box-shadow:0 14px 30px rgba(15,23,42,.05)}
    .admin-panel-header{padding:1rem 1rem .25rem}
    .admin-panel-body{padding:1rem}
    .admin-muted{color:#64748b}
    .admin-list{display:flex;flex-direction:column;gap:.75rem}
    .admin-list-item{border:1px solid #edf2f7;border-radius:.65rem;padding:.8rem;background:#fff}
    .admin-action-row{display:flex;flex-wrap:wrap;gap:.5rem}
</style>
<div id="main">
    <header class="">
        <a href="#" class="burger-btn d-block p-2">
            <i class="fas fa-bars"></i> Menu
        </a>
    </header>
    <div class="page-content">
        <section class="admin-shell">
            <?php if($dashboardAlert): ?>
                <?= $dashboardAlert; ?>
            <?php endif; ?>

            <div class="admin-hero">
                <div class="d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center gap-3">
                    <div>
                        <h3 class="mb-1">Dashboard Admin</h3>
                        <p class="admin-muted mb-0">Ringkasan operasional DIGAMI untuk audit, Pra Audit, temuan, pengguna, dan konten dashboard.</p>
                    </div>
                    <div class="admin-action-row">
                        <a href="audit?tahun=<?= $activeYear; ?>" class="btn btn-primary btn-sm"><i class="fas fa-check-circle me-1"></i> Audit <?= $activeYear; ?></a>
                        <a href="praaudit" class="btn btn-outline-primary btn-sm"><i class="fas fa-clipboard-check me-1"></i> Pra Audit</a>
                        <a href="master?hal=user" class="btn btn-outline-secondary btn-sm"><i class="fas fa-users me-1"></i> User</a>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12 col-md-6 col-xl-3 mb-3"><div class="admin-stat"><span class="admin-stat-icon"><i class="bi bi-check2-circle"></i></span><div class="admin-muted small">Audit Tahun Aktif</div><h4 class="mb-0"><?= $summaryAuditYear; ?></h4><small class="admin-muted"><?= $activeYear; ?></small></div></div>
                <div class="col-12 col-md-6 col-xl-3 mb-3"><div class="admin-stat"><span class="admin-stat-icon"><i class="bi bi-file-earmark-check"></i></span><div class="admin-muted small">Laporan AMI</div><h4 class="mb-0"><?= $summaryAuditReport; ?></h4><small class="admin-muted">sudah unggah laporan</small></div></div>
                <div class="col-12 col-md-6 col-xl-3 mb-3"><div class="admin-stat"><span class="admin-stat-icon"><i class="bi bi-clipboard-data"></i></span><div class="admin-muted small">Pra Audit</div><h4 class="mb-0"><?= $summaryPraSelesai; ?> / <?= $summaryPraAudit; ?></h4><small class="admin-muted">selesai / total <?= $activeYear; ?></small></div></div>
                <div class="col-12 col-md-6 col-xl-3 mb-3"><div class="admin-stat"><span class="admin-stat-icon"><i class="bi bi-exclamation-triangle"></i></span><div class="admin-muted small">Temuan Open</div><h4 class="mb-0"><?= $summaryTemuanOpen; ?></h4><small class="admin-muted"><?= $summaryTemuanClosed; ?> sudah ditindaklanjuti</small></div></div>
            </div>

            <div class="row">
                <div class="col-12 col-lg-8 mb-3">
                    <div class="admin-panel h-100">
                        <div class="admin-panel-header d-flex justify-content-between align-items-start gap-2">
                            <div>
                                <h5 class="mb-1">Audit Terbaru</h5>
                                <p class="admin-muted mb-0">Audit pada tahun aktif <?= $activeYear; ?>.</p>
                            </div>
                            <a class="btn btn-outline-primary btn-sm" href="audit?tahun=<?= $activeYear; ?>">Lihat Semua</a>
                        </div>
                        <div class="admin-panel-body">
                            <div class="admin-list">
                                <?php if($latestAudits && mysqli_num_rows($latestAudits) > 0): ?>
                                    <?php while($audit = mysqli_fetch_assoc($latestAudits)): ?>
                                        <div class="admin-list-item">
                                            <div class="d-flex flex-column flex-md-row justify-content-between gap-2">
                                                <div>
                                                    <div class="fw-bold"><?= htmlspecialchars($audit['kodeaudit']); ?> - <?= htmlspecialchars($audit['namaunit'] ?? '-'); ?></div>
                                                    <div class="admin-muted small"><?= (int)$audit['lingkup'] === 1 ? 'Akademik' : 'Non-akademik'; ?> · <?= audit_ui_date($audit['tglmulai']); ?> s.d <?= audit_ui_date($audit['tglselesai']); ?></div>
                                                </div>
                                                <span class="badge <?= !empty($audit['berkas']) ? 'bg-success' : 'bg-warning text-dark'; ?> align-self-md-start"><?= !empty($audit['berkas']) ? 'Laporan tersedia' : 'Belum ada laporan'; ?></span>
                                            </div>
                                        </div>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <div class="alert alert-light border mb-0">Belum ada audit untuk tahun <?= $activeYear; ?>.</div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-lg-4 mb-3">
                    <div class="admin-panel h-100">
                        <div class="admin-panel-header">
                            <h5 class="mb-1">Ringkasan Sistem</h5>
                            <p class="admin-muted mb-0">Data administrasi aktif.</p>
                        </div>
                        <div class="admin-panel-body">
                            <div class="row">
                                <div class="col-6 mb-3"><div class="admin-list-item"><div class="admin-muted small">Pengumuman</div><h5 class="mb-0"><?= $summaryPengumuman; ?></h5></div></div>
                                <div class="col-6 mb-3"><div class="admin-list-item"><div class="admin-muted small">Berkas Aktif</div><h5 class="mb-0"><?= $summaryBerkas; ?></h5></div></div>
                                <div class="col-12"><div class="admin-list-item"><div class="admin-muted small">Total Pengguna</div><h5 class="mb-0"><?= $summaryUsers; ?></h5></div></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-8 mb-3">
                    <div class="admin-panel">
                        <div class="admin-panel-header">
                            <h5 class="mb-1">Pengumuman</h5>
                            <p class="admin-muted mb-0">Publikasikan informasi singkat di halaman depan.</p>
                        </div>
                        <div class="admin-panel-body">
                            <form method="post" class="mb-3">
                                <div class="mb-2">
                                    <textarea class="form-control" placeholder="Tulis pengumuman di sini" rows="3" name="info" required></textarea>
                                </div>
                                <div class="text-end">
                                    <button class="btn btn-primary btn-sm" name="simpan"><i class="fas fa-save me-1"></i> Simpan Pengumuman</button>
                                </div>
                            </form>
                            <div class="admin-list">
                                <?php if($latestAnnouncements): ?>
                                    <?php while ($u = mysqli_fetch_array($latestAnnouncements)): ?>
                                        <div class="admin-list-item">
                                            <div class="d-flex justify-content-between align-items-start gap-2">
                                                <div>
                                                    <span class="badge bg-light text-dark border mb-2"><?= htmlspecialchars($u['entri']); ?></span>
                                                    <p class="mb-0"><?= htmlspecialchars($u['info']); ?></p>
                                                </div>
                                                <a class="btn btn-sm btn-outline-danger" href="?hapusumum=<?= (int)$u['id']; ?>" title="Hapus pengumuman"><i class="fas fa-trash"></i></a>
                                            </div>
                                        </div>
                                    <?php endwhile; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 mb-3">
                    <div class="admin-panel">
                        <div class="admin-panel-header">
                            <h5 class="mb-1">Berkas Dashboard</h5>
                            <p class="admin-muted mb-0">Kelola dokumen yang tampil di dashboard publik.</p>
                        </div>
                        <div class="admin-panel-body">
                            <form method="post" enctype="multipart/form-data" class="mb-3">
                                <div class="mb-2">
                                    <input type="text" class="form-control" placeholder="Nama berkas" name="nama" required>
                                </div>
                                <div class="mb-2">
                                    <input type="file" name="berkas" class="form-control" required>
                                </div>
                                <div class="text-end">
                                    <button class="btn btn-success btn-sm" name="upload"><i class="fas fa-upload me-1"></i> Upload</button>
                                </div>
                            </form>
                            <div class="admin-list">
                                <?php if($activeFiles): ?>
                                    <?php while ($b = mysqli_fetch_array($activeFiles)): ?>
                                        <div class="admin-list-item">
                                            <div class="d-flex justify-content-between align-items-center gap-2">
                                                <strong><?= htmlspecialchars($b['namaberkas']); ?></strong>
                                                <div class="btn-group btn-group-sm">
                                                    <a class="btn btn-outline-primary" href="assets/berkasdepan/<?= htmlspecialchars($b['berkas']); ?>" title="Download"><i class="fas fa-download"></i></a>
                                                    <a class="btn btn-outline-danger" href="?hapusberkas=<?= (int)$b['id']; ?>&berkas=<?= urlencode($b['berkas']); ?>" title="Hapus"><i class="fas fa-trash"></i></a>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endwhile; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>
<?php include 'foot.php'; ?>
