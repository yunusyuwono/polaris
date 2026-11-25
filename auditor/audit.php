<?php
include 'nav.php';
include '../audit.ui.php';

$auditorId = mysqli_real_escape_string($kon, $_SESSION['id_pegawai'] ?? '');

$pegawaiMap = [];
$localUsers = mysqli_query($kon, "SELECT id, nama FROM user");
if($localUsers) {
    while($user = mysqli_fetch_assoc($localUsers)) {
        $id = (string)$user['id'];
        $pegawaiMap[$id] = $user['nama'];
    }
}

$auditRows = [];
$auditQuery = mysqli_query($kon, "
    SELECT DISTINCT
        audit.kodeaudit,
        audit.unit,
        audit.lingkup,
        audit.tglmulai,
        audit.tglselesai,
        audit.token,
        audit.entri,
        unit.nama AS namaunit
    FROM audit
    JOIN auditor ON audit.kodeaudit=auditor.kodeaudit
    LEFT JOIN unit ON unit.id=audit.unit
    WHERE auditor.user='$auditorId'
    ORDER BY audit.tglmulai DESC, audit.kodeaudit DESC
");
if($auditQuery) {
    while($row = mysqli_fetch_assoc($auditQuery)) {
        $auditRows[] = $row;
    }
}

$kodeList = array_values(array_unique(array_filter(array_column($auditRows, 'kodeaudit'))));
$auditorMap = [];
if(!empty($kodeList)) {
    $kodeSql = "'".implode("','", array_map(function($kode) use ($kon) {
        return mysqli_real_escape_string($kon, $kode);
    }, $kodeList))."'";
    $teamQuery = mysqli_query($kon, "SELECT kodeaudit, user, status FROM auditor WHERE kodeaudit IN ($kodeSql) ORDER BY status ASC, id ASC");
    if($teamQuery) {
        while($team = mysqli_fetch_assoc($teamQuery)) {
            $kode = $team['kodeaudit'];
            if(!isset($auditorMap[$kode])) {
                $auditorMap[$kode] = [];
            }
            if((string)$team['user'] === (string)$auditorId) {
                continue;
            }
            $level = $team['status'] === 'Ketua' ? 'Ketua' : 'Anggota';
            $nama = $pegawaiMap[(string)$team['user']] ?? $team['user'];
            $auditorMap[$kode][] = $nama.' ('.$level.')';
        }
    }
}

$totalAudit = count($auditRows);
$berjalan = 0;
$selesai = 0;
foreach($auditRows as $row) {
    if(date('Y-m-d') >= $row['tglmulai'] && date('Y-m-d') <= $row['tglselesai']) $berjalan++;
    if($row['tglselesai'] < date('Y-m-d')) $selesai++;
}
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
                            <h3 class="mb-1">Audit Saya</h3>
                            <p class="text-muted mb-0">Daftar penugasan audit, jadwal pelaksanaan, dan akses mulai audit.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-4 mb-3">
                <div class="auditor-stat"><span class="auditor-stat-icon"><i class="bi bi-check2-circle"></i></span><div class="text-muted small">Total Penugasan</div><h4 class="mb-0"><?= $totalAudit; ?></h4></div>
            </div>
            <div class="col-12 col-md-4 mb-3">
                <div class="auditor-stat"><span class="auditor-stat-icon"><i class="bi bi-hourglass-split"></i></span><div class="text-muted small">Sedang Berjalan</div><h4 class="mb-0"><?= $berjalan; ?></h4></div>
            </div>
            <div class="col-12 col-md-4 mb-3">
                <div class="auditor-stat"><span class="auditor-stat-icon"><i class="bi bi-calendar-check"></i></span><div class="text-muted small">Selesai Jadwal</div><h4 class="mb-0"><?= $selesai; ?></h4></div>
            </div>

            <div class="col-12">
                <div class="card">
                    <div class="card-body px-3">
                        <?php if(!$auditQuery): ?>
                            <div class="alert alert-danger">Gagal mengambil data audit: <?= htmlspecialchars(mysqli_error($kon)); ?></div>
                        <?php endif; ?>
                        <div class="table-responsive mt-1">
                            <table class="table table-bordered table-striped table-hover text-dark align-middle">
                                <thead>
                                    <tr>
                                        <th>No.</th>
                                        <th>Kode Audit</th>
                                        <th>Unit Auditi</th>
                                        <th>Ruang Lingkup</th>
                                        <th>Waktu Audit</th>
                                        <th>Tim Auditor</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $no=1; foreach($auditRows as $a): ?>
                                        <tr>
                                            <td><?= $no++; ?></td>
                                            <td><strong><?= htmlspecialchars($a['kodeaudit']); ?></strong></td>
                                            <td><?= htmlspecialchars($a['namaunit'] ?? '-'); ?></td>
                                            <td><?= (int)$a['lingkup'] === 1 ? 'Akademik' : 'Non-akademik'; ?></td>
                                            <td><?= audit_ui_date($a['tglmulai']); ?> s.d <?= audit_ui_date($a['tglselesai']); ?></td>
                                            <td>
                                                <?php if(!empty($auditorMap[$a['kodeaudit']])): ?>
                                                    <?= implode('<br>', array_map('htmlspecialchars', $auditorMap[$a['kodeaudit']])); ?>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <a class="btn btn-success btn-sm" href="audit.token?ka=<?= htmlspecialchars($a['kodeaudit']); ?>" title="Mulai Audit"><i class="fas fa-paper-plane me-1"></i> Mulai Audit</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>

<?php include 'foot.php';?>
