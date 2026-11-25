<?php
include 'nav.php';

function unit_table_exists($kon, $table) {
    $table = mysqli_real_escape_string($kon, $table);
    $result = mysqli_query($kon, "SHOW TABLES LIKE '$table'");
    return $result && mysqli_num_rows($result) > 0;
}

function unit_table_columns($kon, $table) {
    $columns = [];
    $table = preg_replace('/[^a-zA-Z0-9_]/', '', $table);
    $result = mysqli_query($kon, "SHOW COLUMNS FROM `$table`");
    if($result) {
        while($row = mysqli_fetch_assoc($result)) {
            $columns[] = $row['Field'];
        }
    }
    return $columns;
}

function unit_percent($done, $total) {
    return $total > 0 ? min(100, round(((int)$done / (int)$total) * 100)) : 0;
}

function unit_sarmut_progress($kon, $sarmutId, $unitId) {
    if($sarmutId <= 0 || !unit_table_exists($kon, 'sarmut_jawaban')) {
        return [0, 0];
    }

    $hasUnitMap = unit_table_exists($kon, 'sarmut_subindikator_unit');
    if($hasUnitMap) {
        $targetQuery = mysqli_query($kon, "
            SELECT COUNT(DISTINCT sub.id) AS total
            FROM sarmut_sasaran ss
            JOIN sarmut_indikator si ON si.sasaran_id=ss.id
            JOIN sarmut_subindikator sub ON sub.indikator_id=si.id
            LEFT JOIN sarmut_subindikator_unit su ON su.subindikator_id=sub.id
            WHERE ss.sarmut_id='$sarmutId' AND (su.unit_id='$unitId' OR su.unit_id IS NULL)
        ");
    } else {
        $targetQuery = mysqli_query($kon, "
            SELECT COUNT(DISTINCT sub.id) AS total
            FROM sarmut_sasaran ss
            JOIN sarmut_indikator si ON si.sasaran_id=ss.id
            JOIN sarmut_subindikator sub ON sub.indikator_id=si.id
            WHERE ss.sarmut_id='$sarmutId'
        ");
    }
    $target = $targetQuery ? (int)(mysqli_fetch_assoc($targetQuery)['total'] ?? 0) : 0;

    $answerQuery = mysqli_query($kon, "SELECT COUNT(DISTINCT idsubindikator) AS total FROM sarmut_jawaban WHERE idsarmut='$sarmutId' AND idpic='$unitId'");
    $answered = $answerQuery ? (int)(mysqli_fetch_assoc($answerQuery)['total'] ?? 0) : 0;

    return [$answered, $target];
}

function unit_iso_progress($kon, $praId, $isoId, $unitId) {
    if($isoId <= 0 || !unit_table_exists($kon, 'iso_indikator')) {
        return [0, 0];
    }

    $targetQuery = mysqli_query($kon, "
        SELECT COUNT(DISTINCT target.id_isoindikator) AS total
        FROM (
            SELECT ii.id_isoindikator
            FROM iso_indikator ii
            WHERE ii.status='1' AND CAST(ii.id_iso AS UNSIGNED)='$isoId'
            UNION
            SELECT ii.id_isoindikator
            FROM iso_indikator ii
            JOIN iso_subklausul sk ON FIND_IN_SET(CAST(sk.id_subklausul AS CHAR) COLLATE utf8mb4_general_ci, REPLACE(ii.id_subklausul, ' ', '') COLLATE utf8mb4_general_ci)
            WHERE ii.status='1' AND CAST(sk.id_iso AS UNSIGNED)='$isoId'
        ) target
    ");
    $target = $targetQuery ? (int)(mysqli_fetch_assoc($targetQuery)['total'] ?? 0) : 0;

    if(!unit_table_exists($kon, 'iso_jawaban')) {
        return [0, $target];
    }

    $answerQuery = mysqli_query($kon, "
        SELECT COUNT(DISTINCT j.id_isoindikator) AS total
        FROM iso_jawaban j
        JOIN (
            SELECT ii.id_isoindikator
            FROM iso_indikator ii
            WHERE ii.status='1' AND CAST(ii.id_iso AS UNSIGNED)='$isoId'
            UNION
            SELECT ii.id_isoindikator
            FROM iso_indikator ii
            JOIN iso_subklausul sk ON FIND_IN_SET(CAST(sk.id_subklausul AS CHAR) COLLATE utf8mb4_general_ci, REPLACE(ii.id_subklausul, ' ', '') COLLATE utf8mb4_general_ci)
            WHERE ii.status='1' AND CAST(sk.id_iso AS UNSIGNED)='$isoId'
        ) target ON target.id_isoindikator=j.id_isoindikator
        WHERE j.id_praaudit='$praId' AND j.id_iso='$isoId' AND j.id_unit='$unitId' AND j.jawaban<>''
    ");
    $answered = $answerQuery ? (int)(mysqli_fetch_assoc($answerQuery)['total'] ?? 0) : 0;

    return [$answered, $target];
}

$jenisFilter = $_GET['jenis'] ?? 'semua';
$statusFilter = $_GET['status'] ?? 'semua';
$allowedJenis = ['semua', 'led', 'sarmut', 'iso'];
$allowedStatus = ['semua', 'belum', 'selesai'];
if(!in_array($jenisFilter, $allowedJenis, true)) $jenisFilter = 'semua';
if(!in_array($statusFilter, $allowedStatus, true)) $statusFilter = 'semua';

$praauditColumns = unit_table_columns($kon, 'praaudit');
$hasPaketSarmut = in_array('paketsarmut', $praauditColumns, true);
$hasPaketIso = in_array('paketiso', $praauditColumns, true);
$sarmutSelect = $hasPaketSarmut ? "praaudit.paketsarmut" : "'' AS paketsarmut";
$isoSelect = $hasPaketIso ? "praaudit.paketiso" : "'' AS paketiso";
$sarmutJoin = $hasPaketSarmut && unit_table_exists($kon, 'sarmut') ? "LEFT JOIN sarmut ON CAST(sarmut.id AS UNSIGNED)=CAST(praaudit.paketsarmut AS UNSIGNED)" : "";
$isoJoin = $hasPaketIso && unit_table_exists($kon, 'iso') ? "LEFT JOIN iso ON CAST(iso.idiso AS UNSIGNED)=CAST(praaudit.paketiso AS UNSIGNED)" : "";
$sarmutTitle = $sarmutJoin ? "sarmut.title AS nama_sarmut" : "'' AS nama_sarmut";
$isoTitle = $isoJoin ? "iso.nama_iso AS nama_iso" : "'' AS nama_iso";

$ledQuery = mysqli_query($kon, "
    SELECT
        praaudit.id as idpra,
        praaudit.paket,
        praaudit.tahun,
        praaudit.status as stapra,
        $sarmutSelect,
        $isoSelect,
        paketpertanyaan.namapaket,
        $sarmutTitle,
        $isoTitle,
        COALESCE(q.total_pertanyaan, 0) as total_pertanyaan,
        COALESCE(l.total_jawaban, 0) as total_jawaban
    FROM praaudit
    JOIN paketpertanyaan ON praaudit.paket=paketpertanyaan.id
    $sarmutJoin
    $isoJoin
    LEFT JOIN (
        SELECT idpaket, COUNT(*) as total_pertanyaan
        FROM pertanyaan
        GROUP BY idpaket
    ) q ON q.idpaket=praaudit.paket
    LEFT JOIN (
        SELECT idpraaudit, COUNT(*) as total_jawaban
        FROM led
        GROUP BY idpraaudit
    ) l ON l.idpraaudit=praaudit.id
    WHERE praaudit.unit='".(int)$cs['idunit']."'
    ORDER BY praaudit.tahun DESC, praaudit.id DESC
");

$praCards = [];
$summary = ['total' => 0, 'selesai' => 0, 'belum' => 0, 'jawab' => 0, 'target' => 0];
$activeYear = (int)date('Y');

if($ledQuery) {
    while($row = mysqli_fetch_assoc($ledQuery)) {
        $ledDone = (int)$row['total_jawaban'];
        $ledTotal = (int)$row['total_pertanyaan'];
        $isActiveYear = (int)$row['tahun'] === $activeYear;
        $firstQuestion = mysqli_fetch_assoc(mysqli_query($kon,"SELECT id FROM pertanyaan WHERE idpaket='".(int)$row['paket']."' ORDER BY id ASC LIMIT 1"));

        $tasks = [];
        $ledAction = '';
        if($isActiveYear && !empty($firstQuestion['id'])) {
            // $ledAction .= '<a class="btn btn-primary btn-sm me-1" href="led.isi?pra='.(int)$row['idpra'].'&soal='.(int)$firstQuestion['id'].'&no=1"><i class="fas fa-edit me-1"></i> Original</a>';
            $ledAction .= '<a class="btn btn-outline-primary btn-sm me-1" href="led.isi.v2?pra='.(int)$row['idpra'].'"><i class="fas fa-check me-1"></i> Isi LED</a>';
        } elseif(!$isActiveYear) {
            $ledAction .= '<span class="btn btn-light btn-sm disabled me-1">Tahun arsip</span>';
        } else {
            $ledAction .= '<span class="btn btn-light btn-sm disabled me-1">Belum ada pertanyaan</span>';
        }
        if($ledDone > 0 || $row['stapra'] === 'Selesai') {
            $ledAction .= '<a class="btn btn-success btn-sm" href="led.laporan?pra='.(int)$row['idpra'].'"><i class="fas fa-list-alt me-1"></i> Lihat Penilaian</a>';
        }

        $ledStatus = ($ledTotal > 0 && $ledDone >= $ledTotal && $row['stapra'] === 'Selesai') ? 'selesai' : 'belum';

        $tasks[] = [
            'jenis' => 'led',
            'title' => $row['namapaket'],
            'subtitle' => 'Lembar Evaluasi Diri',
            'done' => $ledDone,
            'total' => $ledTotal,
            'status' => $ledStatus,
            'action' => $ledAction
        ];

        if(!empty($row['paketsarmut'])) {
            [$sarmutDone, $sarmutTotal] = unit_sarmut_progress($kon, (int)$row['paketsarmut'], (int)$cs['idunit']);
            $tasks[] = [
                'jenis' => 'sarmut',
                'title' => $row['nama_sarmut'] ?: 'Sarmut',
                'subtitle' => 'Standar Mutu',
                'done' => $sarmutDone,
                'total' => $sarmutTotal,
                'status' => ($sarmutTotal > 0 && $sarmutDone >= $sarmutTotal) ? 'selesai' : 'belum',
                'action' => '<a class="btn btn-info btn-sm text-dark" href="sarmut.isi?pra='.(int)$row['idpra'].'"><i class="fas fa-edit me-1"></i> Isi Sarmut</a>'
            ];
        }

        if(!empty($row['paketiso'])) {
            [$isoDone, $isoTotal] = unit_iso_progress($kon, (int)$row['idpra'], (int)$row['paketiso'], (int)$cs['idunit']);
            $tasks[] = [
                'jenis' => 'iso',
                'title' => $row['nama_iso'] ?: 'ISO',
                'subtitle' => 'Evaluasi ISO',
                'done' => $isoDone,
                'total' => $isoTotal,
                'status' => ($isoTotal > 0 && $isoDone >= $isoTotal) ? 'selesai' : 'belum',
                'action' => '<a class="btn btn-secondary btn-sm" href="iso.isi?pra='.(int)$row['idpra'].'"><i class="fas fa-edit me-1"></i> Isi ISO</a>'
            ];
        }

        $praCards[] = [
            'idpra' => (int)$row['idpra'],
            'title' => $row['namapaket'],
            'tahun' => $row['tahun'],
            'status' => $row['stapra'],
            'tasks' => $tasks
        ];
    }
}

$filteredCards = [];
foreach($praCards as $praCard) {
    $visibleTasks = [];

    foreach($praCard['tasks'] as $task) {
        $summary['total']++;
        $summary[$task['status']]++;
        $summary['jawab'] += (int)$task['done'];
        $summary['target'] += (int)$task['total'];

        if($jenisFilter !== 'semua' && $task['jenis'] !== $jenisFilter) continue;
        if($statusFilter !== 'semua' && $task['status'] !== $statusFilter) continue;
        $visibleTasks[] = $task;
    }

    if(!empty($visibleTasks)) {
        $praCard['visible_tasks'] = $visibleTasks;
        $filteredCards[] = $praCard;
    }
}
?>
<style>
    .unit-page-hero{background:linear-gradient(135deg,#ecfeff 0%,#f8fafc 100%);border:1px solid #ccfbf1;border-radius:.75rem;padding:1.25rem}
    .unit-stat-card,.unit-led-card,.unit-filter-card{border:1px solid #e5e7eb;border-radius:.7rem;background:#fff;box-shadow:0 14px 30px rgba(15,23,42,.05)}
    .unit-stat-card{padding:1rem;height:100%}
    .unit-stat-icon{width:38px;height:38px;border-radius:.6rem;display:inline-flex;align-items:center;justify-content:center;background:#ccfbf1;color:#0f766e;margin-bottom:.75rem}
    .unit-led-card{padding:1.1rem;height:100%}
    .unit-led-title{font-weight:800;color:#0f172a;line-height:1.3}
    .unit-led-meta{display:flex;flex-wrap:wrap;gap:.45rem;margin:.8rem 0}
    .unit-led-meta .badge{font-size:.76rem;font-weight:700}
    .unit-led-progress .progress{height:9px;border-radius:999px;background:#e5e7eb}
    .unit-filter-card{padding:1rem}
    .unit-task-row{border-top:1px solid #edf2f7;padding-top:1rem;margin-top:1rem}
    .unit-task-row:first-of-type{border-top:0;padding-top:0;margin-top:.25rem}
    .unit-task-title{font-weight:800;color:#111827}
    .unit-task-action{min-width:132px}
</style>
<div id="main">
    <header class="">
        <a href="#" class="burger-btn d-block p-2"><i class="fas fa-bars"></i> Menu</a>
    </header>
    <div class="page-content">
        <section class="row">
            <div class="col-12 mb-3">
                <div class="unit-page-hero">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2">
                        <div>
                            <h3 class="mb-1">Pengisian Unit</h3>
                            <p class="text-muted mb-0">LED, Sarmut, dan ISO untuk <?= htmlspecialchars($cs['namaunit'] ?? '-'); ?>.</p>
                        </div>
                        <span class="badge bg-info text-dark px-3 py-2"><?= htmlspecialchars($cs['kepalaunit'] ?? '-'); ?></span>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-3 mb-3"><div class="unit-stat-card"><span class="unit-stat-icon"><i class="bi bi-clipboard-data"></i></span><div class="text-muted small">Total Tugas</div><h4 class="mb-0"><?= $summary['total']; ?></h4></div></div>
            <div class="col-12 col-md-3 mb-3"><div class="unit-stat-card"><span class="unit-stat-icon"><i class="bi bi-check2-circle"></i></span><div class="text-muted small">Selesai</div><h4 class="mb-0"><?= $summary['selesai']; ?></h4></div></div>
            <div class="col-12 col-md-3 mb-3"><div class="unit-stat-card"><span class="unit-stat-icon"><i class="bi bi-hourglass-split"></i></span><div class="text-muted small">Belum Selesai</div><h4 class="mb-0"><?= $summary['belum']; ?></h4></div></div>
            <div class="col-12 col-md-3 mb-3"><div class="unit-stat-card"><span class="unit-stat-icon"><i class="bi bi-list-check"></i></span><div class="text-muted small">Total Isian</div><h4 class="mb-0"><?= $summary['jawab'].' / '.$summary['target']; ?></h4></div></div>

            <div class="col-12 mb-3">
                <form class="unit-filter-card row g-2 align-items-end" method="get">
                    <div class="col-md-4">
                        <label class="form-label">Jenis Pengisian</label>
                        <select name="jenis" class="form-control">
                            <option value="semua" <?= $jenisFilter==='semua'?'selected':''; ?>>Semua</option>
                            <option value="led" <?= $jenisFilter==='led'?'selected':''; ?>>LED</option>
                            <option value="sarmut" <?= $jenisFilter==='sarmut'?'selected':''; ?>>Sarmut</option>
                            <option value="iso" <?= $jenisFilter==='iso'?'selected':''; ?>>ISO</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-control">
                            <option value="semua" <?= $statusFilter==='semua'?'selected':''; ?>>Semua</option>
                            <option value="belum" <?= $statusFilter==='belum'?'selected':''; ?>>Belum Selesai</option>
                            <option value="selesai" <?= $statusFilter==='selesai'?'selected':''; ?>>Selesai</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <button class="btn btn-primary btn-sm" type="submit"><i class="fas fa-filter me-1"></i> Terapkan Filter</button>
                        <a class="btn btn-outline-secondary btn-sm" href="led">Reset</a>
                    </div>
                </form>
            </div>

            <div class="col-12">
                <?php if(!$ledQuery): ?>
                    <div class="alert alert-danger">Gagal mengambil data pengisian: <?= htmlspecialchars(mysqli_error($kon)); ?></div>
                <?php elseif(empty($filteredCards)): ?>
                    <div class="unit-led-card text-center py-5">
                        <i class="bi bi-inbox fs-2 text-muted"></i>
                        <h5 class="mt-3 mb-1">Tidak ada tugas</h5>
                        <p class="text-muted mb-0">Tidak ada data yang cocok dengan filter saat ini.</p>
                    </div>
                <?php else: ?>
                    <div class="row">
                        <?php foreach($filteredCards as $card): ?>
                            <?php
                            $praDone = 0;
                            $praTotal = 0;
                            $allDone = true;
                            foreach($card['visible_tasks'] as $task) {
                                $praDone += (int)$task['done'];
                                $praTotal += (int)$task['total'];
                                if($task['status'] !== 'selesai') {
                                    $allDone = false;
                                }
                            }
                            $praPercent = unit_percent($praDone, $praTotal);
                            $praStatusClass = $allDone ? 'bg-success' : 'bg-warning text-dark';
                            ?>
                            <div class="col-12 col-xl-6 mb-3">
                                <div class="unit-led-card">
                                    <div class="d-flex justify-content-between align-items-start gap-3">
                                        <div>
                                            <div class="unit-led-title"><?= htmlspecialchars($card['title']); ?></div>
                                            <div class="unit-led-meta">
                                                <span class="badge bg-light text-dark border">Tahun <?= htmlspecialchars($card['tahun']); ?></span>
                                                <span class="badge <?= $praStatusClass; ?>"><?= $allDone ? 'Selesai' : 'Belum Selesai'; ?></span>
                                                <span class="badge bg-light text-dark border"><?= count($card['visible_tasks']); ?> jenis pengisian</span>
                                            </div>
                                        </div>
                                        <div class="text-end">
                                            <div class="fw-bold"><?= $praPercent; ?>%</div>
                                            <div class="text-muted small">progres</div>
                                        </div>
                                    </div>

                                    <?php foreach($card['visible_tasks'] as $task): ?>
                                        <?php
                                        $percent = unit_percent($task['done'], $task['total']);
                                        $statusClass = $task['status'] === 'selesai' ? 'bg-success' : 'bg-warning text-dark';
                                        $jenisClass = $task['jenis'] === 'led' ? 'bg-primary' : ($task['jenis'] === 'sarmut' ? 'bg-info text-dark' : 'bg-secondary');
                                        ?>
                                        <div class="unit-task-row">
                                            <div class="d-flex flex-column flex-md-row justify-content-between gap-2">
                                                <div class="flex-fill">
                                                    <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                                                        <span class="badge <?= $jenisClass; ?>"><?= strtoupper($task['jenis']); ?></span>
                                                        <span class="unit-task-title"><?= htmlspecialchars($task['title']); ?></span>
                                                        <span class="badge <?= $statusClass; ?>"><?= $task['status'] === 'selesai' ? 'Selesai' : 'Belum Selesai'; ?></span>
                                                    </div>
                                                    <div class="unit-led-progress">
                                                        <div class="d-flex justify-content-between small mb-1">
                                                            <span><?= (int)$task['done'].' dari '.(int)$task['total']; ?> isian</span>
                                                            <span><?= max(0, (int)$task['total'] - (int)$task['done']); ?> belum</span>
                                                        </div>
                                                        <div class="progress">
                                                            <div class="progress-bar bg-info" role="progressbar" style="width: <?= $percent; ?>%;" aria-valuenow="<?= $percent; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="unit-task-action d-flex align-items-end justify-content-md-end"><?= $task['action']; ?></div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </div>
</div>

<?php include 'foot.php';?>
