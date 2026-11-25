<?php
include 'nav.php';

if(!function_exists('auditor_table_columns')) {
    function auditor_table_columns($kon, $table) {
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
}

function auditor_column($columns, $candidates) {
    foreach($candidates as $candidate) {
        if(in_array($candidate, $columns, true)) {
            return $candidate;
        }
    }
    return null;
}

function auditor_table_exists($kon, $table) {
    $table = mysqli_real_escape_string($kon, $table);
    $result = mysqli_query($kon, "SHOW TABLES LIKE '$table'");
    return $result && mysqli_num_rows($result) > 0;
}

function auditor_progress_cell($done, $total, $barClass = '') {
    $done = (int)$done;
    $total = (int)$total;
    $percent = $total > 0 ? min(100, round(($done / $total) * 100)) : 0;
    ?>
    <div class="praaudit-progress">
        <div class="d-flex justify-content-between small mb-1">
            <span><?php echo $done.' / '.$total; ?></span>
            <span><?php echo $percent; ?>%</span>
        </div>
        <div class="progress" style="height: 8px;">
            <div class="progress-bar <?php echo $barClass; ?>" role="progressbar" style="width: <?php echo $percent; ?>%;" aria-valuenow="<?php echo $percent; ?>" aria-valuemin="0" aria-valuemax="100"></div>
        </div>
    </div>
    <?php
}

$praauditColumns = auditor_table_columns($kon, 'praaudit');
$sarmutColumns = auditor_table_columns($kon, 'sarmut');
$isoColumns = auditor_table_columns($kon, 'iso');

$hasPaketSarmut = in_array('paketsarmut', $praauditColumns, true);
$hasPaketIso = in_array('paketiso', $praauditColumns, true);
$sarmutIdColumn = auditor_column($sarmutColumns, ['id']);
$sarmutTitleColumn = auditor_column($sarmutColumns, ['title', 'namasarmut']);
$isoIdColumn = auditor_column($isoColumns, ['idiso', 'id']);
$isoTitleColumn = auditor_column($isoColumns, ['nama_iso', 'title', 'namaiso']);

$joinSarmut = $hasPaketSarmut && $sarmutIdColumn && $sarmutTitleColumn ? "LEFT JOIN sarmut s ON CAST(praaudit.paketsarmut AS UNSIGNED)=CAST(s.`$sarmutIdColumn` AS UNSIGNED)" : "";
$joinIso = $hasPaketIso && $isoIdColumn && $isoTitleColumn ? "LEFT JOIN iso i ON CAST(praaudit.paketiso AS UNSIGNED)=CAST(i.`$isoIdColumn` AS UNSIGNED)" : "";
$sarmutSelect = $joinSarmut ? ", s.`$sarmutTitleColumn` as nama_sarmut" : ", '' as nama_sarmut";
$isoSelect = $joinIso ? ", i.`$isoTitleColumn` as nama_iso" : ", '' as nama_iso";
$sarmutValueSelect = $hasPaketSarmut ? ", praaudit.paketsarmut" : ", '' as paketsarmut";
$isoValueSelect = $hasPaketIso ? ", praaudit.paketiso" : ", '' as paketiso";
$hasSarmutProgressTables = auditor_table_exists($kon, 'sarmut_sasaran') && auditor_table_exists($kon, 'sarmut_indikator') && auditor_table_exists($kon, 'sarmut_subindikator') && auditor_table_exists($kon, 'sarmut_jawaban');
$hasSarmutUnitTable = auditor_table_exists($kon, 'sarmut_subindikator_unit');
$hasIsoProgressTables = auditor_table_exists($kon, 'iso_indikator');
$hasIsoJawabanTable = auditor_table_exists($kon, 'iso_jawaban');

$sarmutProgressSelect = $hasSarmutProgressTables && $hasPaketSarmut
    ? ", COALESCE(sja.total_sarmut_jawaban, 0) as total_sarmut_jawaban, COALESCE(stu.total_sarmut_target, sta.total_sarmut_target, 0) as total_sarmut_target"
    : ", 0 as total_sarmut_jawaban, 0 as total_sarmut_target";
$isoProgressSelect = $hasIsoProgressTables && $hasPaketIso
    ? ", COALESCE(ij.total_iso_jawaban, 0) as total_iso_jawaban, COALESCE(it.total_iso_target, 0) as total_iso_target"
    : ", 0 as total_iso_jawaban, 0 as total_iso_target";

$sarmutProgressJoin = "";
if($hasSarmutProgressTables && $hasPaketSarmut) {
    $sarmutProgressJoin = "
    LEFT JOIN (
        SELECT ss.sarmut_id, COUNT(DISTINCT sub.id) as total_sarmut_target
        FROM sarmut_sasaran ss
        JOIN sarmut_indikator si ON si.sasaran_id=ss.id
        JOIN sarmut_subindikator sub ON sub.indikator_id=si.id
        GROUP BY ss.sarmut_id
    ) sta ON CAST(sta.sarmut_id AS UNSIGNED)=CAST(praaudit.paketsarmut AS UNSIGNED)
    LEFT JOIN (
        SELECT j.idsarmut, j.idpic, COUNT(DISTINCT j.idsubindikator) as total_sarmut_jawaban
        FROM sarmut_jawaban j
        GROUP BY j.idsarmut, j.idpic
    ) sja ON CAST(sja.idsarmut AS UNSIGNED)=CAST(praaudit.paketsarmut AS UNSIGNED) AND CAST(sja.idpic AS UNSIGNED)=CAST(praaudit.unit AS UNSIGNED)";

    if($hasSarmutUnitTable) {
        $sarmutProgressJoin .= "
    LEFT JOIN (
        SELECT ss.sarmut_id, su.unit_id, COUNT(DISTINCT sub.id) as total_sarmut_target
        FROM sarmut_sasaran ss
        JOIN sarmut_indikator si ON si.sasaran_id=ss.id
        JOIN sarmut_subindikator sub ON sub.indikator_id=si.id
        JOIN sarmut_subindikator_unit su ON su.subindikator_id=sub.id
        GROUP BY ss.sarmut_id, su.unit_id
    ) stu ON CAST(stu.sarmut_id AS UNSIGNED)=CAST(praaudit.paketsarmut AS UNSIGNED) AND CAST(stu.unit_id AS UNSIGNED)=CAST(praaudit.unit AS UNSIGNED)";
    } else {
        $sarmutProgressJoin .= "
    LEFT JOIN (
        SELECT NULL as sarmut_id, NULL as unit_id, NULL as total_sarmut_target
    ) stu ON 1=0";
    }
}

$isoProgressJoin = "";
if($hasIsoProgressTables && $hasPaketIso) {
    $isoProgressJoin = "
    LEFT JOIN (
        SELECT target.id_iso, COUNT(DISTINCT target.id_isoindikator) as total_iso_target
        FROM (
            SELECT CAST(ii.id_iso AS UNSIGNED) as id_iso, ii.id_isoindikator
            FROM iso_indikator ii
            WHERE ii.status='1' AND CAST(ii.id_iso AS UNSIGNED) > 0
            UNION
            SELECT CAST(sk.id_iso AS UNSIGNED) as id_iso, ii.id_isoindikator
            FROM iso_indikator ii
            JOIN iso_subklausul sk ON FIND_IN_SET(CAST(sk.id_subklausul AS CHAR) COLLATE utf8mb4_general_ci, REPLACE(ii.id_subklausul, ' ', '') COLLATE utf8mb4_general_ci)
            WHERE ii.status='1' AND CAST(sk.id_iso AS UNSIGNED) > 0
        ) target
        GROUP BY target.id_iso
    ) it ON CAST(it.id_iso AS UNSIGNED)=CAST(praaudit.paketiso AS UNSIGNED)";

    if($hasIsoJawabanTable) {
        $isoProgressJoin .= "
    LEFT JOIN (
        SELECT j.id_praaudit, j.id_iso, j.id_unit, COUNT(DISTINCT j.id_isoindikator) as total_iso_jawaban
        FROM iso_jawaban j
        JOIN (
            SELECT CAST(ii.id_iso AS UNSIGNED) as id_iso, ii.id_isoindikator
            FROM iso_indikator ii
            WHERE ii.status='1' AND CAST(ii.id_iso AS UNSIGNED) > 0
            UNION
            SELECT CAST(sk.id_iso AS UNSIGNED) as id_iso, ii.id_isoindikator
            FROM iso_indikator ii
            JOIN iso_subklausul sk ON FIND_IN_SET(CAST(sk.id_subklausul AS CHAR) COLLATE utf8mb4_general_ci, REPLACE(ii.id_subklausul, ' ', '') COLLATE utf8mb4_general_ci)
            WHERE ii.status='1' AND CAST(sk.id_iso AS UNSIGNED) > 0
        ) target ON target.id_iso=CAST(j.id_iso AS UNSIGNED) AND target.id_isoindikator=j.id_isoindikator
        WHERE j.jawaban<>''
        GROUP BY j.id_praaudit, j.id_iso, j.id_unit
    ) ij ON CAST(ij.id_iso AS UNSIGNED)=CAST(praaudit.paketiso AS UNSIGNED) AND CAST(ij.id_unit AS UNSIGNED)=CAST(praaudit.unit AS UNSIGNED) AND CAST(ij.id_praaudit AS UNSIGNED)=CAST(praaudit.id AS UNSIGNED)";
    } else {
        $isoProgressJoin .= "
    LEFT JOIN (
        SELECT NULL as id_iso, NULL as id_unit, NULL as total_iso_jawaban
    ) ij ON 1=0";
    }
}

$praQuery = "
    SELECT
        praaudit.id as idpra,
        praaudit.unit as idunit,
        praaudit.paket,
        praaudit.tahun,
        praaudit.status as stapra,
        unit.nama,
        paketpertanyaan.namapaket,
        COALESCE(q.total_pertanyaan, 0) as total_pertanyaan,
        COALESCE(l.total_jawaban, 0) as total_jawaban
        $sarmutValueSelect
        $isoValueSelect
        $sarmutSelect
        $isoSelect
        $sarmutProgressSelect
        $isoProgressSelect
    FROM praaudit
    JOIN unit ON praaudit.unit=unit.id
    JOIN paketpertanyaan ON praaudit.paket=paketpertanyaan.id
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
    $joinSarmut
    $joinIso
    $sarmutProgressJoin
    $isoProgressJoin
    WHERE paketpertanyaan.status='Aktif'
    ORDER BY praaudit.tahun DESC, unit.nama ASC
";
$pra = mysqli_query($kon, $praQuery);
$rows = [];
$totalPra = 0;
$selesaiPra = 0;
$belumPra = 0;

if($pra) {
    while($row = mysqli_fetch_assoc($pra)) {
        $rows[] = $row;
        $totalPra++;
        if($row['stapra'] === 'Selesai') {
            $selesaiPra++;
        } else {
            $belumPra++;
        }
    }
}
?>
<style>
    .praaudit-hero {
        background: linear-gradient(135deg, #fff7ed 0%, #f8fafc 100%);
        border: 1px solid #fed7aa;
        border-radius: .75rem;
    }
    .praaudit-stat {
        border: 1px solid #e5e7eb;
        border-radius: .65rem;
        padding: 1rem;
        background: #fff;
        height: 100%;
    }
    .praaudit-stat .icon {
        width: 38px;
        height: 38px;
        border-radius: .6rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #fff7ed;
        color: #ea580c;
        margin-bottom: .75rem;
    }
    .praaudit-actions .btn {
        width: 34px;
        height: 34px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    .praaudit-progress {
        min-width: 150px;
    }
</style>
<div id="main">
    <header class="">
        <a href="#" class="burger-btn d-block p-2">
            <i class="fas fa-bars"></i> Menu
        </a>
    </header>
    <div class="page-content">
        <section class="row">
            <div class="col-12">
                <div class="praaudit-hero p-3 p-md-4 mb-3">
                    <h3 class="mb-1">Pra Audit</h3>
                    <p class="text-muted mb-0">Pantau progres LED unit dan buka laporan Pra Audit yang sudah selesai.</p>
                </div>
            </div>
            <div class="col-12 col-md-4 mb-3">
                <div class="praaudit-stat">
                    <span class="icon"><i class="bi bi-clipboard-data"></i></span>
                    <div class="text-muted small">Total Pra Audit</div>
                    <h4 class="mb-0"><?php echo $totalPra; ?></h4>
                </div>
            </div>
            <div class="col-12 col-md-4 mb-3">
                <div class="praaudit-stat">
                    <span class="icon"><i class="bi bi-check2-circle"></i></span>
                    <div class="text-muted small">Selesai</div>
                    <h4 class="mb-0"><?php echo $selesaiPra; ?></h4>
                </div>
            </div>
            <div class="col-12 col-md-4 mb-3">
                <div class="praaudit-stat">
                    <span class="icon"><i class="bi bi-hourglass-split"></i></span>
                    <div class="text-muted small">Belum Selesai</div>
                    <h4 class="mb-0"><?php echo $belumPra; ?></h4>
                </div>
            </div>
            <div class="col-12">
                <div class="card shadow-sm border-0">
                    <div class="card-body px-3">
                        <?php if(!$pra): ?>
                            <div class="alert alert-danger">Gagal mengambil data Pra Audit: <?php echo htmlspecialchars(mysqli_error($kon)); ?></div>
                        <?php endif; ?>
                        <div class="table-responsive mt-1">
                            <table class="table table-bordered table-striped table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th>No.</th>
                                        <th>Nama Unit</th>
                                        <th>Paket LED</th>
                                        <th>Nama Sarmut</th>
                                        <th>Nama ISO</th>
                                        <th>Tahun Pelaksanaan</th>
                                        <th>Progres LED</th>
                                        <th>Progres Sarmut</th>
                                        <th>Progres ISO</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $no=1;
                                    foreach($rows as $u) {
                                        $total = (int)$u['total_pertanyaan'];
                                        $jawab = (int)$u['total_jawaban'];
                                        $totalSarmut = (int)$u['total_sarmut_target'];
                                        $jawabSarmut = (int)$u['total_sarmut_jawaban'];
                                        $totalIso = (int)$u['total_iso_target'];
                                        $jawabIso = (int)$u['total_iso_jawaban'];
                                        $badgeClass = $u['stapra'] === 'Selesai' ? 'bg-success' : 'bg-warning text-dark';
                                        ?>
                                        <tr>
                                            <td><?php echo $no++;?></td>
                                            <td><strong><?php echo htmlspecialchars($u['nama']);?></strong></td>
                                            <td><?php echo htmlspecialchars($u['namapaket']);?></td>
                                            <td><?php echo !empty($u['nama_sarmut']) ? htmlspecialchars($u['nama_sarmut']) : '<span class="text-muted">-</span>'; ?></td>
                                            <td><?php echo !empty($u['nama_iso']) ? htmlspecialchars($u['nama_iso']) : '<span class="text-muted">-</span>'; ?></td>
                                            <td><?php echo htmlspecialchars($u['tahun']);?></td>
                                            <td class="praaudit-progress">
                                                <?php auditor_progress_cell($jawab, $total, 'bg-warning'); ?>
                                            </td>
                                            <td class="praaudit-progress">
                                                <?php if(!empty($u['nama_sarmut'])): ?>
                                                    <?php auditor_progress_cell($jawabSarmut, $totalSarmut, 'bg-info'); ?>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="praaudit-progress">
                                                <?php if(!empty($u['nama_iso'])): ?>
                                                    <?php if($hasIsoJawabanTable): ?>
                                                        <?php auditor_progress_cell($jawabIso, $totalIso, 'bg-secondary'); ?>
                                                    <?php else: ?>
                                                        <div class="praaudit-progress">
                                                            <div class="d-flex justify-content-between small mb-1">
                                                                <span><?php echo $totalIso; ?> indikator</span>
                                                                <span>Master</span>
                                                            </div>
                                                            <div class="progress" style="height: 8px;">
                                                                <div class="progress-bar bg-secondary" role="progressbar" style="width: <?php echo $totalIso > 0 ? 100 : 0; ?>%;" aria-valuenow="<?php echo $totalIso > 0 ? 100 : 0; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                                            </div>
                                                        </div>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><span class="badge <?php echo $badgeClass; ?>"><?php echo htmlspecialchars($u['stapra']);?></span></td>
                                            <td>
                                                <div class="btn-group praaudit-actions">
                                                    <a href="praaudit.laporan?pra=<?php echo (int)$u['idpra'];?>" class="btn btn-success btn-sm" title="Laporan LED User"><i class="fas fa-list-alt"></i></a>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>
<?php include "foot.php";?>
