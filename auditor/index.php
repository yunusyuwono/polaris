<?php
include 'nav.php';
include '../audit.ui.php';

$auditorId = mysqli_real_escape_string($kon, $_SESSION['id_pegawai'] ?? '');
$totalAudit = 0;
$auditBerjalan = 0;
$auditSelesai = 0;
$totalPraAudit = 0;

$auditStats = mysqli_query($kon, "
    SELECT
        COUNT(DISTINCT audit.kodeaudit) AS total_audit,
        SUM(CASE WHEN CURDATE() BETWEEN audit.tglmulai AND audit.tglselesai THEN 1 ELSE 0 END) AS berjalan,
        SUM(CASE WHEN audit.tglselesai < CURDATE() THEN 1 ELSE 0 END) AS selesai
    FROM audit
    JOIN auditor ON auditor.kodeaudit=audit.kodeaudit
    WHERE auditor.user='$auditorId'
");
if($auditStats) {
    $stats = mysqli_fetch_assoc($auditStats);
    $totalAudit = (int)($stats['total_audit'] ?? 0);
    $auditBerjalan = (int)($stats['berjalan'] ?? 0);
    $auditSelesai = (int)($stats['selesai'] ?? 0);
}

$praStats = mysqli_query($kon, "SELECT COUNT(*) AS total FROM praaudit");
if($praStats) {
    $totalPraAudit = (int)(mysqli_fetch_assoc($praStats)['total'] ?? 0);
}

$latestAudits = mysqli_query($kon, "
    SELECT audit.kodeaudit, audit.lingkup, audit.tglmulai, audit.tglselesai, unit.nama AS namaunit
    FROM audit
    JOIN auditor ON auditor.kodeaudit=audit.kodeaudit
    LEFT JOIN unit ON unit.id=audit.unit
    WHERE auditor.user='$auditorId'
    ORDER BY audit.tglmulai DESC, audit.kodeaudit DESC
    LIMIT 5
");
$umum = mysqli_query($kon,"SELECT id, info, entri FROM pengumuman ORDER BY entri DESC LIMIT 5");
$berkas = mysqli_query($kon,"SELECT id, namaberkas, berkas FROM berkasdepan WHERE status='Aktif' ORDER BY id DESC LIMIT 6");
?>
<div id="main">
    <header class="">
        <a href="#" class="burger-btn d-block p-2">
            <i class="fas fa-bars"></i> Menu
        </a>
    </header>
    <div class="page-content">
        <section class="row">
            <div class="col-12 mb-3">
                <div class="auditor-page-hero">
                    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center gap-3">
                        <div>
                            <h3 class="mb-1">Dashboard Auditor</h3>
                            <p class="text-muted mb-0">Ringkasan tugas audit, pengumuman, dan berkas pendukung untuk <?= htmlspecialchars($_SESSION['nama'] ?? 'Auditor'); ?>.</p>
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            <a href="audit" class="btn btn-primary btn-sm"><i class="fas fa-check-circle me-1"></i> Audit Saya</a>
                            <a href="praaudit" class="btn btn-outline-primary btn-sm"><i class="fas fa-clipboard-list me-1"></i> Pra Audit</a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-3 mb-3">
                <div class="auditor-stat">
                    <span class="auditor-stat-icon"><i class="bi bi-check2-circle"></i></span>
                    <div class="text-muted small">Total Audit Saya</div>
                    <h4 class="mb-0"><?= $totalAudit; ?></h4>
                </div>
            </div>
            <div class="col-12 col-md-3 mb-3">
                <div class="auditor-stat">
                    <span class="auditor-stat-icon"><i class="bi bi-hourglass-split"></i></span>
                    <div class="text-muted small">Sedang Berjalan</div>
                    <h4 class="mb-0"><?= $auditBerjalan; ?></h4>
                </div>
            </div>
            <div class="col-12 col-md-3 mb-3">
                <div class="auditor-stat">
                    <span class="auditor-stat-icon"><i class="bi bi-calendar-check"></i></span>
                    <div class="text-muted small">Selesai Jadwal</div>
                    <h4 class="mb-0"><?= $auditSelesai; ?></h4>
                </div>
            </div>
            <div class="col-12 col-md-3 mb-3">
                <div class="auditor-stat">
                    <span class="auditor-stat-icon"><i class="bi bi-clipboard-data"></i></span>
                    <div class="text-muted small">Pra Audit Tersedia</div>
                    <h4 class="mb-0"><?= $totalPraAudit; ?></h4>
                </div>
            </div>

            <div class="col-lg-8 mb-3">
                <div class="card h-100">
                    <div class="card-header d-flex justify-content-between align-items-start gap-2">
                        <div>
                            <h5 class="mb-1">Audit Terbaru</h5>
                            <p class="text-muted mb-0 small">Daftar audit yang melibatkan Anda sebagai auditor.</p>
                        </div>
                        <a class="btn btn-outline-primary btn-sm" href="audit">Lihat Semua</a>
                    </div>
                    <div class="card-body">
                        <?php if($latestAudits && mysqli_num_rows($latestAudits) > 0): ?>
                            <div class="list-group list-group-flush">
                                <?php while($audit = mysqli_fetch_assoc($latestAudits)): ?>
                                    <div class="list-group-item px-0">
                                        <div class="d-flex flex-column flex-md-row justify-content-between gap-2">
                                            <div>
                                                <strong><?= htmlspecialchars($audit['kodeaudit']); ?> - <?= htmlspecialchars($audit['namaunit'] ?? '-'); ?></strong>
                                                <div class="text-muted small"><?= (int)$audit['lingkup'] === 1 ? 'Akademik' : 'Non-akademik'; ?> · <?= audit_ui_date($audit['tglmulai']); ?> s.d <?= audit_ui_date($audit['tglselesai']); ?></div>
                                            </div>
                                            <a class="btn btn-primary btn-sm align-self-md-start" href="audit.token?ka=<?= htmlspecialchars($audit['kodeaudit']); ?>">Mulai Audit</a>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-light border mb-0">Belum ada audit yang ditugaskan.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 mb-3">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="mb-1">Berkas Dashboard</h5>
                        <p class="text-muted mb-0 small">Dokumen pendukung terbaru.</p>
                    </div>
                    <div class="card-body">
                        <div class="list-group">
                            <?php if($berkas): ?>
                                <?php while($b=mysqli_fetch_array($berkas)): ?>
                                    <a href="../assets/berkasdepan/<?= htmlspecialchars($b['berkas']); ?>" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                        <span><?= htmlspecialchars($b['namaberkas']); ?></span>
                                        <i class="fas fa-download text-primary"></i>
                                    </a>
                                <?php endwhile; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 mb-3">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-1">Pengumuman</h5>
                        <p class="text-muted mb-0 small">Informasi terbaru dari admin DIGAMI.</p>
                    </div>
                    <div class="card-body">
                        <?php if($umum && mysqli_num_rows($umum) > 0): ?>
                            <div class="row">
                                <?php while($u=mysqli_fetch_array($umum)): ?>
                                    <div class="col-12 col-lg-6 mb-3">
                                        <div class="border rounded p-3 h-100 bg-white">
                                            <span class="badge bg-light text-dark border mb-2"><?= htmlspecialchars($u['entri']); ?></span>
                                            <p class="mb-0"><?= htmlspecialchars($u['info']); ?></p>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-light border mb-0">Belum ada pengumuman.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>

<?php include 'foot.php';?>
