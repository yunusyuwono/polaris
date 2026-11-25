<?php
include 'nav.php';

if(!function_exists('digami_table_columns')) {
    function digami_table_columns($kon, $table) {
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

function digami_column($columns, $candidates) {
    foreach($candidates as $candidate) {
        if(in_array($candidate, $columns, true)) {
            return $candidate;
        }
    }
    return null;
}

function digami_table_exists($kon, $table) {
    $table = mysqli_real_escape_string($kon, $table);
    $result = mysqli_query($kon, "SHOW TABLES LIKE '$table'");
    return $result && mysqli_num_rows($result) > 0;
}

function digami_progress_cell($done, $total, $barClass = '') {
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

$praauditColumns = digami_table_columns($kon, 'praaudit');
$sarmutColumns = digami_table_columns($kon, 'sarmut');
$isoColumns = digami_table_columns($kon, 'iso');

$hasPaketSarmut = in_array('paketsarmut', $praauditColumns, true);
$hasPaketIso = in_array('paketiso', $praauditColumns, true);
$sarmutIdColumn = digami_column($sarmutColumns, ['id']);
$sarmutTitleColumn = digami_column($sarmutColumns, ['title', 'namasarmut']);
$isoIdColumn = digami_column($isoColumns, ['idiso', 'id']);
$isoTitleColumn = digami_column($isoColumns, ['nama_iso', 'title', 'namaiso']);

$joinSarmut = $hasPaketSarmut && $sarmutIdColumn && $sarmutTitleColumn ? "LEFT JOIN sarmut s ON CAST(praaudit.paketsarmut AS UNSIGNED)=CAST(s.`$sarmutIdColumn` AS UNSIGNED)" : "";
$joinIso = $hasPaketIso && $isoIdColumn && $isoTitleColumn ? "LEFT JOIN iso i ON CAST(praaudit.paketiso AS UNSIGNED)=CAST(i.`$isoIdColumn` AS UNSIGNED)" : "";
$sarmutSelect = $joinSarmut ? ", s.`$sarmutTitleColumn` as nama_sarmut" : ", '' as nama_sarmut";
$isoSelect = $joinIso ? ", i.`$isoTitleColumn` as nama_iso" : ", '' as nama_iso";
$sarmutValueSelect = $hasPaketSarmut ? ", praaudit.paketsarmut" : ", '' as paketsarmut";
$isoValueSelect = $hasPaketIso ? ", praaudit.paketiso" : ", '' as paketiso";
$hasSarmutProgressTables = digami_table_exists($kon, 'sarmut_sasaran') && digami_table_exists($kon, 'sarmut_indikator') && digami_table_exists($kon, 'sarmut_subindikator') && digami_table_exists($kon, 'sarmut_jawaban');
$hasSarmutUnitTable = digami_table_exists($kon, 'sarmut_subindikator_unit');
$hasIsoProgressTables = digami_table_exists($kon, 'iso_indikator');
$hasIsoJawabanTable = digami_table_exists($kon, 'iso_jawaban');

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
            JOIN iso_subklausul sk ON FIND_IN_SET(sk.id_subklausul, REPLACE(ii.id_subklausul, ' ', ''))
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
            JOIN iso_subklausul sk ON FIND_IN_SET(sk.id_subklausul, REPLACE(ii.id_subklausul, ' ', ''))
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

$praauditDeferOutput = true;
include "praaudit.buat.proses.php";

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
$totalJawaban = 0;
$totalPertanyaan = 0;

if($pra) {
    while($row = mysqli_fetch_assoc($pra)) {
        $rows[] = $row;
        $totalPra++;
        $totalJawaban += (int)$row['total_jawaban'];
        $totalPertanyaan += (int)$row['total_pertanyaan'];
        if($row['stapra'] === 'Selesai') {
            $selesaiPra++;
        } else {
            $belumPra++;
        }
    }
}

$currentYear = date('Y');
$latestYearQuery = mysqli_query($kon, "SELECT MAX(tahun) as latest_year FROM praaudit");
$latestYearRow = $latestYearQuery ? mysqli_fetch_assoc($latestYearQuery) : null;
$activeYear = !empty($latestYearRow['latest_year']) ? (string)$latestYearRow['latest_year'] : $currentYear;

$riskSummary = ['Sangat Rendah' => 0, 'Rendah' => 0, 'Sedang' => 0, 'Tinggi' => 0, 'Sangat Tinggi' => 0, 'Belum Dinilai' => 0];
$riskThisYear = [
    'Sangat Rendah' => 0,
    'Rendah' => 0,
    'Sedang' => 0,
    'Tinggi' => 0,
    'Sangat Tinggi' => 0
];
$riskQuery = mysqli_query($kon, "
    SELECT
        praaudit.tahun,
        CASE
            WHEN led.risiko IS NULL OR led.risiko=0 THEN 'Belum Dinilai'
            WHEN led.risiko BETWEEN 1 AND 2 THEN 'Sangat Rendah'
            WHEN led.risiko BETWEEN 3 AND 4 THEN 'Rendah'
            WHEN led.risiko BETWEEN 5 AND 9 THEN 'Sedang'
            WHEN led.risiko BETWEEN 10 AND 14 THEN 'Tinggi'
            ELSE 'Sangat Tinggi'
        END AS level_risiko,
        COUNT(*) AS total
    FROM led
    JOIN praaudit ON praaudit.id=led.idpraaudit
    JOIN paketpertanyaan ON praaudit.paket=paketpertanyaan.id
    WHERE paketpertanyaan.status='Aktif'
    GROUP BY praaudit.tahun, level_risiko
    ORDER BY praaudit.tahun ASC
");
if($riskQuery) {
    while($riskRow = mysqli_fetch_assoc($riskQuery)) {
        $level = $riskRow['level_risiko'];
        $tahun = (string)$riskRow['tahun'];
        $total = (int)$riskRow['total'];
        $riskSummary[$level] = ($riskSummary[$level] ?? 0) + $total;
        if($tahun === $activeYear) {
            if(isset($riskThisYear[$level])) {
                $riskThisYear[$level] += $total;
            } elseif($level === 'Belum Dinilai' && $total > 0) {
                $riskThisYear['Belum Dinilai'] = ($riskThisYear['Belum Dinilai'] ?? 0) + $total;
            }
        }
    }
}
$riskChartLabels = array_keys($riskSummary);
$riskChartData = array_values($riskSummary);

$riskColorMap = [
    'Sangat Rendah' => ['bg' => '#00ff00', 'border' => '#00cc00'],
    'Rendah'        => ['bg' => '#44c7f4', 'border' => '#1aa8d8'],
    'Sedang'        => ['bg' => '#ffff00', 'border' => '#d6d600'],
    'Tinggi'        => ['bg' => '#ff6600', 'border' => '#e65c00'],
    'Sangat Tinggi' => ['bg' => '#ff0000', 'border' => '#cc0000'],
    'Belum Dinilai' => ['bg' => '#cbd5e1', 'border' => '#94a3b8']
];

$riskThisYearLabels = array_keys($riskThisYear);
$riskThisYearData = array_values($riskThisYear);
$riskThisYearBg = [];
$riskThisYearBorder = [];
foreach ($riskThisYearLabels as $lbl) {
    $riskThisYearBg[] = $riskColorMap[$lbl]['bg'] ?? '#94a3b8';
    $riskThisYearBorder[] = $riskColorMap[$lbl]['border'] ?? '#64748b';
}
?>
<style>
    .praaudit-hero {
        background: linear-gradient(135deg, #f8fafc 0%, #eef6ff 100%);
        border: 1px solid #e2e8f0;
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
        background: #eff6ff;
        color: #2563eb;
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
    .praaudit-chart-card {
        background:#fff;
        border:1px solid #e5e7eb;
        border-radius:.7rem;
        padding:1rem;
        height:100%;
        box-shadow:0 14px 30px rgba(15,23,42,.05);
    }
    .praaudit-chart-card canvas {
        max-height:260px;
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
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
                        <div>
                            <h3 class="mb-1">Pra Audit</h3>
                            <p class="text-muted mb-0">Kelola paket LED sebelum audit berjalan dan pantau progres pengisian unit.</p>
                        </div>
                        <a class="btn btn-primary btn-sm" id="btnTambahPraAudit" data-bs-toggle="collapse" href="#tambah" role="button" aria-expanded="false" aria-controls="tambah">
                            <i class="fas fa-plus me-1"></i> Tambah Pra Audit
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-3 mb-3">
                <div class="praaudit-stat">
                    <span class="icon"><i class="bi bi-clipboard-data"></i></span>
                    <div class="text-muted small">Total Pra Audit</div>
                    <h4 class="mb-0"><?php echo $totalPra; ?></h4>
                </div>
            </div>
            <div class="col-12 col-md-3 mb-3">
                <div class="praaudit-stat">
                    <span class="icon"><i class="bi bi-check2-circle"></i></span>
                    <div class="text-muted small">Selesai</div>
                    <h4 class="mb-0"><?php echo $selesaiPra; ?></h4>
                </div>
            </div>
            <div class="col-12 col-md-3 mb-3">
                <div class="praaudit-stat">
                    <span class="icon"><i class="bi bi-hourglass-split"></i></span>
                    <div class="text-muted small">Belum Selesai</div>
                    <h4 class="mb-0"><?php echo $belumPra; ?></h4>
                </div>
            </div>
            <div class="col-12 col-md-3 mb-3">
                <div class="praaudit-stat">
                    <span class="icon"><i class="bi bi-list-check"></i></span>
                    <div class="text-muted small">Total Jawaban LED</div>
                    <h4 class="mb-0"><?php echo $totalJawaban . ' / ' . $totalPertanyaan; ?></h4>
                </div>
            </div>

            <div class="col-12 col-lg-6 mb-3">
                <div class="praaudit-chart-card">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h5 class="mb-1">Distribusi Risiko LED</h5>
                            <p class="text-muted mb-0 small">Akumulasi seluruh Pra Audit berdasarkan level risiko.</p>
                        </div>
                        <span class="badge bg-primary">Risk Dashboard</span>
                    </div>
                    <canvas id="praRiskChart"></canvas>
                </div>
            </div>
            <div class="col-12 col-lg-6 mb-3">
                <div class="praaudit-chart-card">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h5 class="mb-1">Jumlah per Prioritas (Tahun <?php echo htmlspecialchars($activeYear); ?>)</h5>
                            <p class="text-muted mb-0 small">Distribusi tingkat risiko pada tahun <?php echo htmlspecialchars($activeYear); ?>.</p>
                        </div>
                        <span class="badge bg-primary">Tahun <?php echo htmlspecialchars($activeYear); ?></span>
                    </div>
                    <canvas id="praHighRiskChart"></canvas>
                </div>
            </div>

            <div class="col-12">
                <div class="card shadow-sm border-0">
                    <div class="card-body px-3">
                        <?php
                        if(!empty($praauditProcessAlert)) {
                            echo $praauditProcessAlert;
                        }
                        ?>

                        <div class="collapse" id="tambah">
                            <div class="border rounded p-3 mb-3 bg-light">
                                <form method="post" id="formPraAudit">
                                    <input type="hidden" name="idpra" id="idpra" value="">
                                    <input type="hidden" name="aksi" id="aksiPraAudit" value="simpan">
                                    <div class="row">
                                        <div class="col-md-6 col-lg-4">
                                            <div class="form-group">
                                                <label>Unit</label>
                                                <select name="unit" id="unit" class="form-control" required>
                                                    <?php
                                                    $unit=mysqli_query($kon,"select * from unit order by nama asc");
                                                    while($u=mysqli_fetch_array($unit)) {
                                                        ?>
                                                        <option value="<?php echo (int)$u['id'];?>"><?php echo htmlspecialchars($u['nama']);?></option>
                                                        <?php
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-lg-4">
                                            <div class="form-group">
                                                <label>Paket Pertanyaan LED</label>
                                                <select name="paket" id="paket" class="form-control" required>
                                                    <?php
                                                    $paket=mysqli_query($kon,"select * from paketpertanyaan where status='Aktif' order by namapaket asc");
                                                    while($p=mysqli_fetch_array($paket)) {
                                                        ?>
                                                        <option value="<?php echo (int)$p['id'];?>"><?php echo htmlspecialchars($p['namapaket']);?></option>
                                                        <?php
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                        <?php if($hasPaketSarmut && $sarmutIdColumn && $sarmutTitleColumn): ?>
                                            <div class="col-md-6 col-lg-4">
                                                <div class="form-group">
                                                    <label>Paket Sarmut</label>
                                                    <select name="paketsarmut" id="paketsarmut" class="form-control">
                                                        <option value="">Tidak menggunakan</option>
                                                        <?php
                                                        $sarmut=mysqli_query($kon,"select `$sarmutIdColumn` as id, `$sarmutTitleColumn` as title from sarmut order by `$sarmutTitleColumn` asc");
                                                        while($s=mysqli_fetch_array($sarmut)) {
                                                            ?>
                                                            <option value="<?php echo (int)$s['id'];?>"><?php echo htmlspecialchars($s['title']);?></option>
                                                            <?php
                                                        }
                                                        ?>
                                                    </select>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                        <?php if($hasPaketIso && $isoIdColumn && $isoTitleColumn): ?>
                                            <div class="col-md-6 col-lg-4">
                                                <div class="form-group">
                                                    <label>Paket ISO</label>
                                                    <select name="paketiso" id="paketiso" class="form-control">
                                                        <option value="">Tidak menggunakan</option>
                                                        <?php
                                                        $iso=mysqli_query($kon,"select `$isoIdColumn` as id, `$isoTitleColumn` as title from iso order by `$isoTitleColumn` asc");
                                                        while($i=mysqli_fetch_array($iso)) {
                                                            ?>
                                                            <option value="<?php echo (int)$i['id'];?>"><?php echo htmlspecialchars($i['title']);?></option>
                                                            <?php
                                                        }
                                                        ?>
                                                    </select>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                        <div class="col-md-6 col-lg-4">
                                            <div class="form-group">
                                                <label>Tahun Pelaksanaan</label>
                                                <input type="number" name="tahun" id="tahun" class="form-control" min="2000" max="2100" value="<?php echo date('Y'); ?>" required>
                                            </div>
                                        </div>
                                    </div>
                                    <button class="btn btn-primary btn-sm" id="btnSubmitPraAudit" name="simpan"><i class="fas fa-save me-1"></i> Simpan</button>
                                    <button class="btn btn-outline-secondary btn-sm d-none" id="btnBatalEditPraAudit" type="button"><i class="fas fa-times me-1"></i> Batal Edit</button>
                                </form>
                            </div>
                        </div>

                        <?php if(!$pra): ?>
                            <div class="alert alert-danger">Gagal mengambil data Pra Audit: <?php echo htmlspecialchars(mysqli_error($kon)); ?></div>
                        <?php endif; ?>

                        <div class="table-responsive mt-1">
                            <table class="table table-bordered table-striped table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th>No.</th>
                                        <th>Aksi</th>
                                        <th>Nama Unit</th>
                                        <th>Paket</th>
                                        <th>Nama Sarmut</th>
                                        <th>Nama ISO</th>
                                        <th>Tahun</th>
                                        <th>Progres LED</th>
                                        <th>Progres Sarmut</th>
                                        <th>Progres ISO</th>
                                        <th>Status</th>
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
                                        $dataSarmut = $hasPaketSarmut && isset($u['paketsarmut']) ? $u['paketsarmut'] : '';
                                        $dataIso = $hasPaketIso && isset($u['paketiso']) ? $u['paketiso'] : '';
                                        ?>
                                        <tr>
                                            <td><?php echo $no++;?></td>
                                            <td>
                                                <div class="btn-group praaudit-actions">
                                                    <button type="button"
                                                            class="btn btn-primary btn-sm btn-edit-praaudit"
                                                            title="Edit Pra Audit"
                                                            data-id="<?php echo (int)$u['idpra']; ?>"
                                                            data-unit="<?php echo (int)$u['idunit']; ?>"
                                                            data-paket="<?php echo (int)$u['paket']; ?>"
                                                            data-sarmut="<?php echo htmlspecialchars($dataSarmut); ?>"
                                                            data-iso="<?php echo htmlspecialchars($dataIso); ?>"
                                                            data-tahun="<?php echo htmlspecialchars($u['tahun']); ?>">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <a href="praaudit.laporan?pra=<?php echo (int)$u['idpra'];?>" class="btn btn-success btn-sm" title="Laporan LED User"><i class="fas fa-list-alt"></i></a>
                                                    <?php if(!empty($u['nama_sarmut'])): ?>
                                                        <a href="sarmut.laporan?pra=<?php echo (int)$u['idpra'];?>" class="btn btn-info btn-sm" title="Laporan Sarmut User"><i class="fas fa-list-ul"></i></a>
                                                    <?php endif; ?>
                                                    <?php if(!empty($u['nama_iso'])): ?>
                                                        <a href="iso.laporan?pra=<?php echo (int)$u['idpra'];?>" class="btn btn-secondary btn-sm" title="Laporan ISO User"><i class="fas fa-list-ol"></i></a>
                                                    <?php endif; ?>
                                                    <?php if($u['stapra']=='Selesai'): ?>
                                                        <a href="praaudit.reset?pra=<?php echo (int)$u['idpra'];?>" class="btn btn-warning btn-sm" title="Reset Status LED" onclick="return confirm('Reset status Pra Audit ini?')"><i class="fas fa-eraser"></i></a>
                                                    <?php else: ?>
                                                        <a href="praaudit.hapus?pra=<?php echo (int)$u['idpra'];?>" class="btn btn-danger btn-sm" title="Hapus Praaudit" onclick="return confirm('Hapus Pra Audit ini?')"><i class="fas fa-trash"></i></a>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td>
                                                <strong><?php echo htmlspecialchars($u['nama']);?></strong>
                                            </td>
                                            <td><?php echo htmlspecialchars($u['namapaket']);?></td>
                                            <td><?php echo !empty($u['nama_sarmut']) ? htmlspecialchars($u['nama_sarmut']) : '<span class="text-muted">-</span>'; ?></td>
                                            <td><?php echo !empty($u['nama_iso']) ? htmlspecialchars($u['nama_iso']) : '<span class="text-muted">-</span>'; ?></td>
                                            <td><?php echo htmlspecialchars($u['tahun']);?></td>
                                            <td>
                                                <?php digami_progress_cell($jawab, $total); ?>
                                            </td>
                                            <td>
                                                <?php if(!empty($u['nama_sarmut'])): ?>
                                                    <?php digami_progress_cell($jawabSarmut, $totalSarmut, 'bg-info'); ?>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if(!empty($u['nama_iso'])): ?>
                                                    <?php if($hasIsoJawabanTable): ?>
                                                        <?php digami_progress_cell($jawabIso, $totalIso, 'bg-secondary'); ?>
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
<script>
document.addEventListener('DOMContentLoaded', function () {
    var form = document.getElementById('formPraAudit');
    var idPra = document.getElementById('idpra');
    var btnSubmit = document.getElementById('btnSubmitPraAudit');
    var btnBatal = document.getElementById('btnBatalEditPraAudit');
    var btnTambah = document.getElementById('btnTambahPraAudit');
    var collapseEl = document.getElementById('tambah');

    function setValue(id, value) {
        var el = document.getElementById(id);
        if (el) {
            el.value = value || '';
        }
    }

    function setTambahMode() {
        if (!form) return;
        form.reset();
        idPra.value = '';
        document.getElementById('aksiPraAudit').value = 'simpan';
        setValue('tahun', '<?php echo date('Y'); ?>');
        btnSubmit.name = 'simpan';
        btnSubmit.innerHTML = '<i class="fas fa-save me-1"></i> Simpan';
        btnBatal.classList.add('d-none');
    }

    document.querySelectorAll('.btn-edit-praaudit').forEach(function (button) {
        button.addEventListener('click', function () {
            idPra.value = this.dataset.id || '';
            document.getElementById('aksiPraAudit').value = 'update';
            setValue('unit', this.dataset.unit);
            setValue('paket', this.dataset.paket);
            setValue('paketsarmut', this.dataset.sarmut);
            setValue('paketiso', this.dataset.iso);
            setValue('tahun', this.dataset.tahun);
            btnSubmit.name = 'update';
            btnSubmit.innerHTML = '<i class="fas fa-save me-1"></i> Update';
            btnBatal.classList.remove('d-none');

            if (collapseEl && window.bootstrap) {
                bootstrap.Collapse.getOrCreateInstance(collapseEl, { toggle: false }).show();
            }
            collapseEl.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
    });

    if (btnBatal) {
        btnBatal.addEventListener('click', setTambahMode);
    }

    if (btnTambah) {
        btnTambah.addEventListener('click', setTambahMode);
    }

    if (form) {
        form.addEventListener('submit', function (event) {
            if (!window.Swal) return;
            event.preventDefault();
            Swal.fire({
                title: idPra.value ? 'Update Pra Audit?' : 'Simpan Pra Audit?',
                text: 'Pastikan data yang diisi sudah benar.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: idPra.value ? 'Ya, update' : 'Ya, simpan',
                cancelButtonText: 'Batal'
            }).then(function (result) {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    }
});
</script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (typeof Chart === 'undefined') return;

    var riskCanvas = document.getElementById('praRiskChart');
    if (riskCanvas) {
        new Chart(riskCanvas, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode($riskChartLabels); ?>,
                datasets: [{
                    data: <?php echo json_encode($riskChartData); ?>,
                    backgroundColor: ['#00ff00', '#44c7f4', '#ffff00', '#ff6600', '#ff0000', '#f8fafc'],
                    borderColor: ['#00cc00', '#1aa8d8', '#d6d600', '#e65c00', '#cc0000', '#e5e7eb'],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });
    }

    var highRiskCanvas = document.getElementById('praHighRiskChart');
    if (highRiskCanvas) {
        new Chart(highRiskCanvas, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($riskThisYearLabels); ?>,
                datasets: [{
                    label: 'Jumlah Risiko',
                    data: <?php echo json_encode($riskThisYearData); ?>,
                    backgroundColor: <?php echo json_encode($riskThisYearBg); ?>,
                    borderColor: <?php echo json_encode($riskThisYearBorder); ?>,
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: { beginAtZero: true, ticks: { precision: 0 } }
                },
                plugins: {
                    legend: { display: false }
                }
            }
        });
    }
});
</script>
<?php include "foot.php";?>
