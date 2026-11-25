<?php
include 'nav.php';
include 'koneksi.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

function sarmut_report_e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function sarmut_report_table_exists($kon, $table) {
    $table = mysqli_real_escape_string($kon, $table);
    $result = mysqli_query($kon, "SHOW TABLES LIKE '$table'");
    return $result && mysqli_num_rows($result) > 0;
}

function sarmut_report_fetch_all($result) {
    $rows = [];
    if($result) {
        while($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }
    }
    return $rows;
}

$praId = isset($_GET['pra']) ? (int)$_GET['pra'] : 0;
$sarmutId = isset($_GET['sarmut_id']) ? (int)$_GET['sarmut_id'] : 0;
$unitFilter = 0;
$contextTitle = 'Laporan Pengisian SARMUT';
$backUrl = 'sarmut';

if($praId > 0) {
    $praQuery = mysqli_query($kon, "
        SELECT p.id, p.unit, p.paketsarmut, p.tahun, u.nama AS nama_unit, s.title AS nama_sarmut, s.year
        FROM praaudit p
        LEFT JOIN unit u ON u.id=p.unit
        LEFT JOIN sarmut s ON s.id=p.paketsarmut
        WHERE p.id='$praId'
        LIMIT 1
    ");
    $praRow = $praQuery ? mysqli_fetch_assoc($praQuery) : null;
    if(!$praRow || empty($praRow['paketsarmut'])) {
        echo "<div id='main'><header><a href='#' class='burger-btn d-block p-2'><i class='fas fa-bars'></i> Menu</a></header><div class='page-content'><div class='alert alert-warning'>Paket SARMUT tidak ditemukan untuk PraAudit #".sarmut_report_e($praId).".</div></div></div>";
        include 'foot.php';
        exit;
    }
    $sarmutId = (int)$praRow['paketsarmut'];
    $unitFilter = (int)$praRow['unit'];
    $contextTitle = 'Laporan SARMUT PraAudit #'.$praId;
    $backUrl = 'praaudit';
} else {
    $praRow = null;
    $unitFilter = isset($_GET['unit_id']) ? (int)$_GET['unit_id'] : 0;
}

if($sarmutId <= 0) {
    echo "<div id='main'><header><a href='#' class='burger-btn d-block p-2'><i class='fas fa-bars'></i> Menu</a></header><div class='page-content'><div class='alert alert-danger'>Parameter SARMUT tidak ditemukan.</div></div></div>";
    include 'foot.php';
    exit;
}

$hasJawaban = sarmut_report_table_exists($kon, 'sarmut_jawaban');
$hasSasaran = sarmut_report_table_exists($kon, 'sarmut_sasaran');
$hasIndikator = sarmut_report_table_exists($kon, 'sarmut_indikator');
$hasSubindikator = sarmut_report_table_exists($kon, 'sarmut_subindikator');

$sarmutQuery = mysqli_query($kon, "SELECT * FROM sarmut WHERE id='$sarmutId' LIMIT 1");
$sarmut = $sarmutQuery ? mysqli_fetch_assoc($sarmutQuery) : null;
if(!$sarmut) {
    echo "<div id='main'><header><a href='#' class='burger-btn d-block p-2'><i class='fas fa-bars'></i> Menu</a></header><div class='page-content'><div class='alert alert-danger'>Data SARMUT tidak ditemukan.</div></div></div>";
    include 'foot.php';
    exit;
}

$unitCondition = $unitFilter > 0 ? " AND j.idpic='$unitFilter'" : "";
$targetTotal = 0;
$filledTotal = 0;
$unitCount = 0;
$quarterRows = [];
$unitRows = [];
$detailRows = [];
$total_sub_by_unit = [];
$filled_by_unit_tw = [];

if($hasSasaran && $hasIndikator && $hasSubindikator) {
    if($unitFilter > 0) {
        $targetQuery = mysqli_query($kon, "
            SELECT COUNT(DISTINCT sub.id) AS total
            FROM sarmut_sasaran ss
            JOIN sarmut_indikator si ON si.sasaran_id=ss.id
            JOIN sarmut_subindikator sub ON sub.indikator_id=si.id
            LEFT JOIN sarmut_subindikator_unit su ON su.subindikator_id=sub.id
            WHERE ss.sarmut_id='$sarmutId'
              AND (su.unit_id='$unitFilter' OR su.unit_id IS NULL)
        ");
        $targetRow = $targetQuery ? mysqli_fetch_assoc($targetQuery) : null;
        $targetTotal = $targetRow ? (int)$targetRow['total'] : 0;
    } else {
        $targetQuery = mysqli_query($kon, "
            SELECT COUNT(DISTINCT sub.id) AS total
            FROM sarmut_sasaran ss
            JOIN sarmut_indikator si ON si.sasaran_id=ss.id
            JOIN sarmut_subindikator sub ON sub.indikator_id=si.id
            WHERE ss.sarmut_id='$sarmutId'
        ");
        $targetRow = $targetQuery ? mysqli_fetch_assoc($targetQuery) : null;
        $targetTotal = $targetRow ? (int)$targetRow['total'] : 0;
    }
}

if($hasJawaban) {
    if($unitFilter > 0) {
        $filledQuery = mysqli_query($kon, "
            SELECT COUNT(DISTINCT j.idsubindikator) AS total
            FROM sarmut_jawaban j
            WHERE j.idsarmut='$sarmutId' AND j.idpic='$unitFilter'
              AND j.nilai IS NOT NULL AND TRIM(j.nilai)<>''
        ");
        $filledRow = $filledQuery ? mysqli_fetch_assoc($filledQuery) : null;
        $filledTotal = $filledRow ? (int)$filledRow['total'] : 0;

        $unitCount = 1;
    } else {
        $filledQuery = mysqli_query($kon, "
            SELECT COUNT(DISTINCT CONCAT(j.idpic, ':', j.idsubindikator)) AS total
            FROM sarmut_jawaban j
            WHERE j.idsarmut='$sarmutId'
              AND j.nilai IS NOT NULL AND TRIM(j.nilai)<>''
        ");
        $filledRow = $filledQuery ? mysqli_fetch_assoc($filledQuery) : null;
        $filledTotal = $filledRow ? (int)$filledRow['total'] : 0;

        $unitCountQuery = mysqli_query($kon, "
            SELECT COUNT(DISTINCT j.idpic) AS total
            FROM sarmut_jawaban j
            WHERE j.idsarmut='$sarmutId'
              AND j.nilai IS NOT NULL AND TRIM(j.nilai)<>''
        ");
        $unitCountRow = $unitCountQuery ? mysqli_fetch_assoc($unitCountQuery) : null;
        $unitCount = $unitCountRow ? (int)$unitCountRow['total'] : 0;

        // Fetch total subindicators assigned per unit for the summary table
        $q_assigned = mysqli_query($kon, "
            SELECT u.id AS unit_id, COUNT(DISTINCT sub.id) AS total
            FROM unit u
            CROSS JOIN sarmut_subindikator sub
            LEFT JOIN sarmut_subindikator_unit su ON su.subindikator_id=sub.id
            JOIN sarmut_indikator si ON sub.indikator_id=si.id
            JOIN sarmut_sasaran ss ON si.sasaran_id=ss.id
            WHERE ss.sarmut_id='$sarmutId'
              AND (su.unit_id=u.id OR su.unit_id IS NULL)
            GROUP BY u.id
        ");
        while($row = $q_assigned ? mysqli_fetch_assoc($q_assigned) : false) {
            $total_sub_by_unit[(int)$row['unit_id']] = (int)$row['total'];
        }

        // Fetch filled count per unit per triwulan
        $q_filled = mysqli_query($kon, "
            SELECT idpic AS unit_id, triwulan, COUNT(DISTINCT idsubindikator) AS total
            FROM sarmut_jawaban
            WHERE idsarmut='$sarmutId' AND nilai IS NOT NULL AND TRIM(nilai)<>''
            GROUP BY idpic, triwulan
        ");
        while($row = $q_filled ? mysqli_fetch_assoc($q_filled) : false) {
            $uid = (int)$row['unit_id'];
            $tw = $row['triwulan'];
            if(!isset($filled_by_unit_tw[$uid])) {
                $filled_by_unit_tw[$uid] = [];
            }
            $filled_by_unit_tw[$uid][$tw] = (int)$row['total'];
        }
    }

    $quarterRows = sarmut_report_fetch_all(mysqli_query($kon, "
        SELECT COALESCE(NULLIF(TRIM(j.triwulan), ''), 'Tanpa Triwulan') AS label, COUNT(*) AS total
        FROM sarmut_jawaban j
        WHERE j.idsarmut='$sarmutId' $unitCondition
          AND j.nilai IS NOT NULL AND TRIM(j.nilai)<>''
        GROUP BY COALESCE(NULLIF(TRIM(j.triwulan), ''), 'Tanpa Triwulan')
        ORDER BY label ASC
    "));

    $unitRows = sarmut_report_fetch_all(mysqli_query($kon, "
        SELECT u.nama AS unit_nama, COUNT(DISTINCT j.idsubindikator) AS terisi, COUNT(*) AS entri
        FROM sarmut_jawaban j
        LEFT JOIN unit u ON u.id=j.idpic
        WHERE j.idsarmut='$sarmutId' $unitCondition
          AND j.nilai IS NOT NULL AND TRIM(j.nilai)<>''
        GROUP BY j.idpic, u.nama
        ORDER BY u.nama ASC
    "));
}

if($hasSasaran && $hasIndikator && $hasSubindikator) {
    if($unitFilter > 0) {
        $unitFilterName = '';
        $uq = mysqli_query($kon, "SELECT nama FROM unit WHERE id='$unitFilter' LIMIT 1");
        $ur = $uq ? mysqli_fetch_assoc($uq) : null;
        $unitFilterName = $ur ? $ur['nama'] : 'Unit';

        $detailRows = sarmut_report_fetch_all(mysqli_query($kon, "
            SELECT ss.sasaran, si.indikator, si.kode, sub.id AS sub_id, sub.subindikator, sub.jenis, sub.satuan, sub.target, sub.baseline,
                   '$unitFilterName' AS unit_nama,
                   MAX(CASE WHEN j.triwulan = 'TW1' THEN j.nilai END) AS nilai_tw1,
                   MAX(CASE WHEN j.triwulan = 'TW2' THEN j.nilai END) AS nilai_tw2,
                   MAX(CASE WHEN j.triwulan = 'TW3' THEN j.nilai END) AS nilai_tw3,
                   MAX(CASE WHEN j.triwulan = 'TW4' THEN j.nilai END) AS nilai_tw4,
                   MAX(j.entri) AS entri_terakhir
            FROM sarmut_sasaran ss
            JOIN sarmut_indikator si ON si.sasaran_id=ss.id
            JOIN sarmut_subindikator sub ON sub.indikator_id=si.id
            LEFT JOIN sarmut_subindikator_unit su ON su.subindikator_id=sub.id
            LEFT JOIN sarmut_jawaban j ON j.idsubindikator=sub.id AND j.idsarmut=ss.sarmut_id AND j.idpic='$unitFilter'
            WHERE ss.sarmut_id='$sarmutId' AND (su.unit_id='$unitFilter' OR su.unit_id IS NULL)
            GROUP BY ss.id, si.id, sub.id
            ORDER BY ss.id ASC, si.id ASC, sub.id ASC
        "));
    }
}

if($unitFilter > 0) {
    $scopeTarget = $targetTotal;
} else {
    $assignedTotalQuery = mysqli_query($kon, "
        SELECT COUNT(DISTINCT u.id, sub.id) AS total
        FROM unit u
        CROSS JOIN sarmut_subindikator sub
        LEFT JOIN sarmut_subindikator_unit su ON su.subindikator_id=sub.id
        JOIN sarmut_indikator si ON sub.indikator_id=si.id
        JOIN sarmut_sasaran ss ON si.sasaran_id=ss.id
        WHERE ss.sarmut_id='$sarmutId'
          AND (su.unit_id=u.id OR su.unit_id IS NULL)
    ");
    $assignedTotalRow = $assignedTotalQuery ? mysqli_fetch_assoc($assignedTotalQuery) : null;
    $scopeTarget = $assignedTotalRow ? (int)$assignedTotalRow['total'] : 0;
}

$percent = $scopeTarget > 0 ? min(100, round(($filledTotal / $scopeTarget) * 100)) : 0;
$quarterLabels = array_map(function($row){ return $row['label']; }, $quarterRows);
$quarterData = array_map(function($row){ return (int)$row['total']; }, $quarterRows);
$unitLabels = array_map(function($row){ return $row['unit_nama'] ?: 'Unit #'; }, $unitRows);
$unitData = array_map(function($row){ return (int)$row['terisi']; }, $unitRows);
?>
<div id="main">
    <header class="">
        <a href="#" class="burger-btn d-block p-2"><i class="fas fa-bars"></i> Menu</a>
    </header>
    <div class="page-content">
        <section class="row">
            <div class="col-12">
                <div class="card sarmut-report-card">
                    <div class="card-header sarmut-report-header">
                        <div>
                            <span class="report-kicker">SARMUT</span>
                            <h4 class="font-semibold mb-1"><?= sarmut_report_e($contextTitle); ?></h4>
                            <p class="mb-0"><?= sarmut_report_e($sarmut['title'] ?? '-'); ?> &middot; Tahun <?= sarmut_report_e($sarmut['year'] ?? '-'); ?><?= $praRow ? ' &middot; Unit '.$praRow['nama_unit'] : ''; ?></p>
                        </div>
                        <a class="btn btn-sm btn-secondary" href="<?= sarmut_report_e($backUrl); ?>"><i class="fas fa-arrow-left me-1"></i>Kembali</a>
                    </div>
                    <div class="card-body report-body">
                        <style>
                            .sarmut-report-card{border:0;border-radius:1rem;overflow:hidden;box-shadow:0 18px 44px rgba(15,23,42,.08)}
                            .sarmut-report-header{display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;background:linear-gradient(135deg,#f8fafc 0%,#ecfeff 100%);border-bottom:1px solid #e2e8f0;padding:1.25rem}
                            .sarmut-report-header h4{font-weight:900;color:#0f172a}
                            .sarmut-report-header p{color:#64748b;font-size:.9rem}
                            .report-kicker{display:inline-flex;margin-bottom:.45rem;color:#0e7490;font-size:.76rem;font-weight:900;text-transform:uppercase}
                            .report-body{background:#f8fafc;padding:1.1rem}
                            .report-stats{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:1rem;margin-bottom:1rem}
                            .report-stat{background:#fff;border:1px solid #e5e7eb;border-radius:.85rem;padding:1rem;box-shadow:0 12px 28px rgba(15,23,42,.06)}
                            .report-stat .label{color:#64748b;font-size:.74rem;font-weight:900;text-transform:uppercase}
                            .report-stat .value{font-size:1.55rem;font-weight:900;color:#111827;line-height:1.1;margin-top:.35rem}
                            .report-stat .hint{color:#94a3b8;font-size:.78rem;margin-top:.45rem}
                            .report-chart-grid{display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1rem}
                            .report-panel{background:#fff;border:1px solid #e5e7eb;border-radius:.85rem;padding:1rem;box-shadow:0 12px 28px rgba(15,23,42,.06)}
                            .report-panel h5{font-weight:900;color:#0f172a;margin:0}
                            .report-panel p{font-size:.84rem;color:#64748b;margin:.2rem 0 .85rem}
                            .report-chart{height:320px;position:relative}
                            .report-table th{white-space:nowrap}
                            @media(max-width:991.98px){.report-stats{grid-template-columns:repeat(2,minmax(0,1fr))}.report-chart-grid{grid-template-columns:1fr}.sarmut-report-header{flex-direction:column}}
                            @media(max-width:575.98px){.report-stats{grid-template-columns:1fr}.report-body{padding:.85rem}.report-chart{height:280px}}
                        </style>

                        <?php if(!$hasJawaban): ?>
                            <div class="alert alert-warning">Tabel <code>sarmut_jawaban</code> tidak ditemukan di database.</div>
                        <?php endif; ?>

                        <div class="report-stats">
                            <div class="report-stat">
                                <div class="label">Cakupan Terisi</div>
                                <div class="value"><?= $filledTotal; ?> / <?= $scopeTarget; ?></div>
                                <div class="hint"><?= $percent; ?>% dari target cakupan.</div>
                            </div>
                            <div class="report-stat">
                                <div class="label">Subindikator</div>
                                <div class="value"><?= $targetTotal; ?></div>
                                <div class="hint">Total item target dalam paket.</div>
                            </div>
                            <div class="report-stat">
                                <div class="label">Unit Mengisi</div>
                                <div class="value"><?= $unitCount; ?></div>
                                <div class="hint"><?= $unitFilter > 0 ? 'Konteks unit PraAudit.' : 'Unit yang memiliki entri.'; ?></div>
                            </div>
                            <div class="report-stat">
                                <div class="label">Persentase</div>
                                <div class="value"><?= $percent; ?>%</div>
                                <div class="hint"><div class="progress mt-2" style="height:8px"><div class="progress-bar bg-info" style="width:<?= $percent; ?>%"></div></div></div>
                            </div>
                        </div>

                        <?php if($unitFilter == 0): ?>
                            <div class="report-chart-grid">
                                <div class="report-panel">
                                    <h5>Entri per Triwulan</h5>
                                    <p>Jumlah nilai yang sudah diisi berdasarkan triwulan.</p>
                                    <div class="report-chart"><canvas id="chartSarmutQuarter"></canvas></div>
                                </div>
                                <div class="report-panel">
                                    <h5>Pengisian per Unit</h5>
                                    <p>Jumlah subindikator terisi per unit.</p>
                                    <div class="report-chart"><canvas id="chartSarmutUnit"></canvas></div>
                                </div>
                            </div>

                            <div class="report-panel">
                                <h5>Ringkasan Penyelesaian Capaian Unit (Triwulan)</h5>
                                <p>Progres pengisian sasaran mutu oleh masing-masing unit per triwulan.</p>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped table-hover align-middle text-center report-table">
                                        <thead class="table-light">
                                            <tr>
                                                <th style="width: 5%;">No</th>
                                                <th class="text-start" style="width: 35%;">PIC</th>
                                                <th style="width: 12%;">Jumlah Sasaran</th>
                                                <th style="width: 11%;">TW1</th>
                                                <th style="width: 11%;">TW2</th>
                                                <th style="width: 11%;">TW3</th>
                                                <th style="width: 11%;">TW4</th>
                                                <th style="width: 10%;">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                                            $no = 1;
                                            $q_units = mysqli_query($kon, "SELECT * FROM unit ORDER BY nama ASC");
                                            while($u = $q_units ? mysqli_fetch_assoc($q_units) : false):
                                                $uid = (int)$u['id'];
                                                $total_sub = $total_sub_by_unit[$uid] ?? 0;
                                            ?>
                                                <tr>
                                                    <td><?= $no++; ?></td>
                                                    <td class="text-start font-semibold"><?= htmlspecialchars($u['nama']); ?></td>
                                                    <td class="font-bold text-dark"><?= $total_sub; ?></td>
                                                    <?php foreach(['TW1', 'TW2', 'TW3', 'TW4'] as $tw): 
                                                        $filled = $filled_by_unit_tw[$uid][$tw] ?? 0;
                                                        
                                                        // Color logic
                                                        if ($total_sub == 0) {
                                                            $cellBg = 'background-color: #6b7280 !important; color: #ffffff !important;'; // gray if no sasaran
                                                        } elseif ($filled == $total_sub) {
                                                            $cellBg = 'background-color: #198754 !important; color: #ffffff !important;'; // green
                                                        } elseif ($filled > 0) {
                                                            $cellBg = 'background-color: #fd7e14 !important; color: #ffffff !important;'; // orange
                                                        } else {
                                                            $cellBg = 'background-color: #212529 !important; color: #ffffff !important;'; // black
                                                        }
                                                    ?>
                                                        <td style="<?= $cellBg; ?> font-weight: bold;"><?= $filled; ?></td>
                                                    <?php endforeach; ?>
                                                    <td>
                                                        <a href="sarmut.laporan?sarmut_id=<?= $sarmutId; ?>&unit_id=<?= $uid; ?>" class="btn btn-sm btn-success px-3 font-semibold text-white">Detail</a>
                                                    </td>
                                                </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        <?php else: ?>
                            <!-- Detailed table for selected unit -->
                            <div class="report-panel">
                                <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                                    <div>
                                        <h5 class="mb-0">Data Pengisian: <?= htmlspecialchars($unitFilterName ?? 'Unit'); ?></h5>
                                        <p class="mb-0 text-muted small">Daftar sasaran, indikator, subindikator, target, dan nilai yang sudah diinput oleh unit ini.</p>
                                    </div>
                                    <?php if (empty($praId)): ?>
                                        <a href="sarmut.laporan?sarmut_id=<?= $sarmutId; ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-chevron-left me-1"></i> Kembali ke Ringkasan</a>
                                    <?php endif; ?>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-striped table-bordered report-table">
                                        <thead>
                                            <tr>
                                                <th>No</th>
                                                <th>Sasaran</th>
                                                <th>Indikator</th>
                                                <th>Subindikator</th>
                                                <th>Target</th>
                                                <th>Baseline</th>
                                                <th>TW 1</th>
                                                <th>TW 2</th>
                                                <th>TW 3</th>
                                                <th>TW 4</th>
                                                <th>Entri Terakhir</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if(empty($detailRows)): ?>
                                                <tr><td colspan="11" class="text-muted text-center">Belum ada data yang dapat ditampilkan.</td></tr>
                                            <?php else: $no=1; foreach($detailRows as $row): ?>
                                                <tr>
                                                    <td><?= $no++; ?></td>
                                                    <td><?= sarmut_report_e($row['sasaran'] ?? '-'); ?></td>
                                                    <td><?= sarmut_report_e(trim(($row['kode'] ? $row['kode'].' - ' : '').($row['indikator'] ?? '-'))); ?></td>
                                                    <td><?= sarmut_report_e($row['subindikator'] ?? '-'); ?><div class="small text-muted"><?= sarmut_report_e(trim(($row['jenis'] ?? '').' '.($row['satuan'] ?? ''))); ?></div></td>
                                                    <td><?= sarmut_report_e($row['target'] ?? '-'); ?></td>
                                                    <td><?= sarmut_report_e($row['baseline'] ?? '-'); ?></td>
                                                    <td><?= isset($row['nilai_tw1']) && $row['nilai_tw1'] !== null && trim($row['nilai_tw1']) !== '' ? sarmut_report_e($row['nilai_tw1']) : '<span class="text-muted">-</span>'; ?></td>
                                                    <td><?= isset($row['nilai_tw2']) && $row['nilai_tw2'] !== null && trim($row['nilai_tw2']) !== '' ? sarmut_report_e($row['nilai_tw2']) : '<span class="text-muted">-</span>'; ?></td>
                                                    <td><?= isset($row['nilai_tw3']) && $row['nilai_tw3'] !== null && trim($row['nilai_tw3']) !== '' ? sarmut_report_e($row['nilai_tw3']) : '<span class="text-muted">-</span>'; ?></td>
                                                    <td><?= isset($row['nilai_tw4']) && $row['nilai_tw4'] !== null && trim($row['nilai_tw4']) !== '' ? sarmut_report_e($row['nilai_tw4']) : '<span class="text-muted">-</span>'; ?></td>
                                                    <td><?= !empty($row['entri_terakhir']) ? sarmut_report_e($row['entri_terakhir']) : '<span class="text-muted">-</span>'; ?></td>
                                                </tr>
                                            <?php endforeach; endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        <?php endif; ?>

                        <script>
                        document.addEventListener('DOMContentLoaded', function(){
                            if (typeof Chart === 'undefined') return;
                            var quarterCanvas = document.getElementById('chartSarmutQuarter');
                            var unitCanvas = document.getElementById('chartSarmutUnit');
                            if (quarterCanvas) {
                                new Chart(quarterCanvas, {
                                    type: 'doughnut',
                                    data: {
                                        labels: <?= json_encode($quarterLabels); ?>,
                                        datasets: [{ data: <?= json_encode($quarterData); ?>, backgroundColor: ['#06b6d4','#2563eb','#f59e0b','#10b981','#64748b'] }]
                                    },
                                    options: { responsive:true, maintainAspectRatio:false, plugins:{ legend:{ position:'bottom' } } }
                                });
                            }
                            if (unitCanvas) {
                                new Chart(unitCanvas, {
                                    type: 'bar',
                                    data: {
                                        labels: <?= json_encode($unitLabels); ?>,
                                        datasets: [{ label:'Subindikator Terisi', data: <?= json_encode($unitData); ?>, backgroundColor:'#0e7490', borderRadius:6 }]
                                    },
                                    options: { responsive:true, maintainAspectRatio:false, scales:{ y:{ beginAtZero:true, ticks:{ precision:0 } } }, plugins:{ legend:{ display:false } } }
                                });
                            }
                        });
                        </script>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>
<?php include 'foot.php'; ?>
