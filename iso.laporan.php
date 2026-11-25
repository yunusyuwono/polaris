<?php
include 'nav.php';
include 'koneksi.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

function iso_report_e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function iso_report_table_exists($kon, $table) {
    $table = mysqli_real_escape_string($kon, $table);
    $result = mysqli_query($kon, "SHOW TABLES LIKE '$table'");
    return $result && mysqli_num_rows($result) > 0;
}

function iso_report_fetch_all($result) {
    $rows = [];
    if($result) {
        while($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }
    }
    return $rows;
}

$praId = isset($_GET['pra']) ? (int)$_GET['pra'] : 0;
$isoId = isset($_GET['iso_id']) ? (int)$_GET['iso_id'] : (isset($_GET['id']) ? (int)$_GET['id'] : 0);
$unitFilter = 0;
$contextTitle = 'Laporan Pengisian ISO';
$backUrl = 'iso';

if($praId > 0) {
    $praQuery = mysqli_query($kon, "
        SELECT p.id, p.unit, p.paketiso, p.tahun, u.nama AS nama_unit, i.nama_iso
        FROM praaudit p
        LEFT JOIN unit u ON u.id=p.unit
        LEFT JOIN iso i ON i.idiso=p.paketiso
        WHERE p.id='$praId'
        LIMIT 1
    ");
    $praRow = $praQuery ? mysqli_fetch_assoc($praQuery) : null;
    if(!$praRow || empty($praRow['paketiso'])) {
        echo "<div id='main'><header><a href='#' class='burger-btn d-block p-2'><i class='fas fa-bars'></i> Menu</a></header><div class='page-content'><div class='alert alert-warning'>Paket ISO tidak ditemukan untuk PraAudit #".iso_report_e($praId).".</div></div></div>";
        include 'foot.php';
        exit;
    }
    $isoId = (int)$praRow['paketiso'];
    $unitFilter = (int)$praRow['unit'];
    $contextTitle = 'Laporan ISO PraAudit #'.$praId;
    $backUrl = 'praaudit';
} else {
    $praRow = null;
}

if($isoId <= 0) {
    echo "<div id='main'><header><a href='#' class='burger-btn d-block p-2'><i class='fas fa-bars'></i> Menu</a></header><div class='page-content'><div class='alert alert-danger'>Parameter ISO tidak ditemukan.</div></div></div>";
    include 'foot.php';
    exit;
}

$hasJawaban = iso_report_table_exists($kon, 'iso_jawaban');
$hasIndikator = iso_report_table_exists($kon, 'iso_indikator');
$hasKlausul = iso_report_table_exists($kon, 'iso_klausul');
$hasSubklausul = iso_report_table_exists($kon, 'iso_subklausul');

$isoQuery = mysqli_query($kon, "SELECT * FROM iso WHERE idiso='$isoId' LIMIT 1");
$iso = $isoQuery ? mysqli_fetch_assoc($isoQuery) : null;
if(!$iso) {
    echo "<div id='main'><header><a href='#' class='burger-btn d-block p-2'><i class='fas fa-bars'></i> Menu</a></header><div class='page-content'><div class='alert alert-danger'>Data ISO tidak ditemukan.</div></div></div>";
    include 'foot.php';
    exit;
}

$praCondition = $praId > 0 ? " AND j.id_praaudit='$praId'" : "";
$unitCondition = $unitFilter > 0 ? " AND j.id_unit='$unitFilter'" : "";
$targetTotal = 0;
$filledTotal = 0;
$unitCount = 0;
$answerRows = [];
$unitRows = [];
$detailRows = [];

if($hasIndikator) {
    $targetQuery = mysqli_query($kon, "
        SELECT COUNT(DISTINCT ii.id_isoindikator) AS total
        FROM iso_indikator ii
        LEFT JOIN iso_subklausul sk ON FIND_IN_SET(sk.id_subklausul, REPLACE(ii.id_subklausul, ' ', ''))
        WHERE (CAST(ii.id_iso AS UNSIGNED)='$isoId' OR CAST(sk.id_iso AS UNSIGNED)='$isoId') AND ii.status='1'
    ");
    $targetRow = $targetQuery ? mysqli_fetch_assoc($targetQuery) : null;
    $targetTotal = $targetRow ? (int)$targetRow['total'] : 0;
}

if($hasJawaban) {
    $filledQuery = mysqli_query($kon, "
        SELECT COUNT(DISTINCT CONCAT(j.id_unit, ':', j.id_isoindikator)) AS total
        FROM iso_jawaban j
        WHERE j.id_iso='$isoId' $praCondition $unitCondition
          AND j.jawaban IS NOT NULL AND TRIM(j.jawaban)<>''
    ");
    $filledRow = $filledQuery ? mysqli_fetch_assoc($filledQuery) : null;
    $filledTotal = $filledRow ? (int)$filledRow['total'] : 0;

    $unitCountQuery = mysqli_query($kon, "
        SELECT COUNT(DISTINCT j.id_unit) AS total
        FROM iso_jawaban j
        WHERE j.id_iso='$isoId' $praCondition $unitCondition
    ");
    $unitCountRow = $unitCountQuery ? mysqli_fetch_assoc($unitCountQuery) : null;
    $unitCount = $unitCountRow ? (int)$unitCountRow['total'] : 0;

    $answerRows = iso_report_fetch_all(mysqli_query($kon, "
        SELECT COALESCE(NULLIF(TRIM(j.jawaban), ''), 'Kosong') AS label, COUNT(*) AS total
        FROM iso_jawaban j
        WHERE j.id_iso='$isoId' $praCondition $unitCondition
        GROUP BY COALESCE(NULLIF(TRIM(j.jawaban), ''), 'Kosong')
        ORDER BY total DESC, label ASC
    "));

    $unitRows = iso_report_fetch_all(mysqli_query($kon, "
        SELECT u.nama AS unit_nama, COUNT(DISTINCT j.id_isoindikator) AS terisi, COUNT(*) AS entri
        FROM iso_jawaban j
        LEFT JOIN unit u ON u.id=j.id_unit
        WHERE j.id_iso='$isoId' $praCondition $unitCondition
          AND j.jawaban IS NOT NULL AND TRIM(j.jawaban)<>''
        GROUP BY j.id_unit, u.nama
        ORDER BY u.nama ASC
    "));
}

if($hasIndikator) {
    $joinSubklausul = $hasSubklausul ? "LEFT JOIN iso_subklausul sk ON FIND_IN_SET(sk.id_subklausul, REPLACE(ii.id_subklausul, ' ', ''))" : "";
    $joinKlausul = $hasKlausul ? "LEFT JOIN iso_klausul k ON (CAST(k.id_klausul AS UNSIGNED)=CAST(ii.id_klausul AS UNSIGNED) OR CAST(k.id_klausul AS UNSIGNED)=CAST(sk.id_klausul AS UNSIGNED))" : "";
    $selectKlausul = $hasKlausul ? "GROUP_CONCAT(DISTINCT k.nomor_klausul ORDER BY k.nomor_klausul ASC SEPARATOR ', ') AS nomor_klausul, GROUP_CONCAT(DISTINCT k.nama_klausul ORDER BY k.nomor_klausul ASC SEPARATOR ', ') AS nama_klausul" : "NULL AS nomor_klausul, NULL AS nama_klausul";
    $selectSubklausul = $hasSubklausul ? "GROUP_CONCAT(DISTINCT sk.nomor_subklausul ORDER BY sk.nomor_subklausul ASC SEPARATOR ', ') AS nomor_subklausul, GROUP_CONCAT(DISTINCT sk.nama_subklausul ORDER BY sk.nomor_subklausul ASC SEPARATOR ', ') AS nama_subklausul" : "NULL AS nomor_subklausul, NULL AS nama_subklausul";

    $detailRows = iso_report_fetch_all(mysqli_query($kon, "
        SELECT ii.id_isoindikator, ii.indikator, $selectKlausul, $selectSubklausul,
               u.nama AS unit_nama, j.jawaban, j.catatan, j.link_dokumen, j.entri, p.id AS idpra, p.tahun
        FROM iso_indikator ii
        $joinSubklausul
        $joinKlausul
        LEFT JOIN iso_jawaban j ON j.id_isoindikator=ii.id_isoindikator AND j.id_iso='$isoId' $praCondition $unitCondition
        LEFT JOIN unit u ON u.id=j.id_unit
        LEFT JOIN praaudit p ON p.id=j.id_praaudit
        WHERE (CAST(ii.id_iso AS UNSIGNED)='$isoId' OR CAST(sk.id_iso AS UNSIGNED)='$isoId') AND ii.status='1'
        GROUP BY ii.id_isoindikator, ii.indikator, u.id, u.nama, j.jawaban, j.catatan, j.link_dokumen, j.entri, p.id, p.tahun
        ORDER BY MIN(k.nomor_klausul) ASC, MIN(sk.nomor_subklausul) ASC, ii.id_isoindikator ASC, u.nama ASC
    "));
}

$scopeTarget = $unitFilter > 0 ? $targetTotal : ($targetTotal * max($unitCount, 1));
$percent = $scopeTarget > 0 ? min(100, round(($filledTotal / $scopeTarget) * 100)) : 0;
$answerLabels = array_map(function($row){ return $row['label']; }, $answerRows);
$answerData = array_map(function($row){ return (int)$row['total']; }, $answerRows);
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
                <div class="card iso-report-card">
                    <div class="card-header iso-report-header">
                        <div>
                            <span class="report-kicker">ISO</span>
                            <h4 class="font-semibold mb-1"><?= iso_report_e($contextTitle); ?></h4>
                            <p class="mb-0"><?= iso_report_e($iso['nama_iso'] ?? '-'); ?><?= $praRow ? ' &middot; Unit '.$praRow['nama_unit'].' &middot; Tahun '.$praRow['tahun'] : ''; ?></p>
                        </div>
                        <a class="btn btn-sm btn-secondary" href="<?= iso_report_e($backUrl); ?>"><i class="fas fa-arrow-left me-1"></i>Kembali</a>
                    </div>
                    <div class="card-body report-body">
                        <style>
                            .iso-report-card{border:0;border-radius:1rem;overflow:hidden;box-shadow:0 18px 44px rgba(15,23,42,.08)}
                            .iso-report-header{display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;background:linear-gradient(135deg,#f8fafc 0%,#f1f5f9 100%);border-bottom:1px solid #e2e8f0;padding:1.25rem}
                            .iso-report-header h4{font-weight:900;color:#0f172a}
                            .iso-report-header p{color:#64748b;font-size:.9rem}
                            .report-kicker{display:inline-flex;margin-bottom:.45rem;color:#475569;font-size:.76rem;font-weight:900;text-transform:uppercase}
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
                            @media(max-width:991.98px){.report-stats{grid-template-columns:repeat(2,minmax(0,1fr))}.report-chart-grid{grid-template-columns:1fr}.iso-report-header{flex-direction:column}}
                            @media(max-width:575.98px){.report-stats{grid-template-columns:1fr}.report-body{padding:.85rem}.report-chart{height:280px}}
                        </style>

                        <?php if(!$hasJawaban): ?>
                            <div class="alert alert-warning">Tabel <code>iso_jawaban</code> tidak ditemukan di database.</div>
                        <?php endif; ?>

                        <div class="report-stats">
                            <div class="report-stat">
                                <div class="label">Cakupan Terisi</div>
                                <div class="value"><?= $filledTotal; ?> / <?= $scopeTarget; ?></div>
                                <div class="hint"><?= $percent; ?>% dari target cakupan.</div>
                            </div>
                            <div class="report-stat">
                                <div class="label">Indikator Aktif</div>
                                <div class="value"><?= $targetTotal; ?></div>
                                <div class="hint">Total indikator ISO aktif.</div>
                            </div>
                            <div class="report-stat">
                                <div class="label">Unit Mengisi</div>
                                <div class="value"><?= $unitCount; ?></div>
                                <div class="hint"><?= $unitFilter > 0 ? 'Konteks unit PraAudit.' : 'Unit yang memiliki entri.'; ?></div>
                            </div>
                            <div class="report-stat">
                                <div class="label">Persentase</div>
                                <div class="value"><?= $percent; ?>%</div>
                                <div class="hint"><div class="progress mt-2" style="height:8px"><div class="progress-bar bg-secondary" style="width:<?= $percent; ?>%"></div></div></div>
                            </div>
                        </div>

                        <div class="report-chart-grid">
                            <div class="report-panel">
                                <h5>Distribusi Jawaban</h5>
                                <p>Jumlah pilihan jawaban ISO yang sudah tersimpan.</p>
                                <div class="report-chart"><canvas id="chartIsoAnswer"></canvas></div>
                            </div>
                            <div class="report-panel">
                                <h5>Pengisian per Unit</h5>
                                <p>Jumlah indikator ISO terisi per unit.</p>
                                <div class="report-chart"><canvas id="chartIsoUnit"></canvas></div>
                            </div>
                        </div>

                        <div class="report-panel">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div>
                                    <h5>Data Pengisian</h5>
                                    <p class="mb-0">Daftar klausul, subklausul, indikator, jawaban, dan catatan auditi.</p>
                                </div>
                                <div>
                                    <?php
                                    $unitsInData = [];
                                    foreach($detailRows as $r) {
                                        if(!empty($r['unit_nama'])) $unitsInData[$r['unit_nama']] = $r['unit_nama'];
                                    }
                                    sort($unitsInData);
                                    ?>
                                    <select id="filterUnit" class="form-select form-select-sm" onchange="filterTableByUnit()">
                                        <option value="">-- Semua Unit --</option>
                                        <?php foreach($unitsInData as $u): ?>
                                            <option value="<?= iso_report_e($u) ?>"><?= iso_report_e($u) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-striped table-bordered report-table">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Klausul</th>
                                            <th>Subklausul</th>
                                            <th>Indikator ISO</th>
                                            <th>Unit</th>
                                            <th>Jawaban</th>
                                            <th>Catatan</th>
                                            <th>Link Dokumen</th>
                                            <th>PraAudit</th>
                                            <th>Entri</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if(empty($detailRows)): ?>
                                            <tr><td colspan="9" class="text-muted">Belum ada data yang dapat ditampilkan.</td></tr>
                                        <?php else: $no=1; foreach($detailRows as $row): ?>
                                            <tr>
                                                <td><?= $no++; ?></td>
                                                <td><?= !empty($row['nama_klausul']) ? iso_report_e($row['nomor_klausul'].' - '.$row['nama_klausul']) : '<span class="text-muted">-</span>'; ?></td>
                                                <td><?= !empty($row['nama_subklausul']) ? iso_report_e($row['nomor_subklausul'].' - '.$row['nama_subklausul']) : '<span class="text-muted">-</span>'; ?></td>
                                                <td><?= iso_report_e($row['indikator'] ?? '-'); ?></td>
                                                <td><?= !empty($row['unit_nama']) ? iso_report_e($row['unit_nama']) : '<span class="text-muted">-</span>'; ?></td>
                                                <td><?= !empty($row['jawaban']) ? iso_report_e($row['jawaban']) : '<span class="text-muted">Belum diisi</span>'; ?></td>
                                                <td><?= !empty($row['catatan']) ? nl2br(iso_report_e($row['catatan'])) : '<span class="text-muted">-</span>'; ?></td>
                                                <td><?= !empty($row['link_dokumen']) ? '<a href="'.iso_report_e($row['link_dokumen']).'" target="_blank" class="btn btn-sm btn-outline-info">Buka Link</a>' : '<span class="text-muted">-</span>'; ?></td>
                                                <td><?= !empty($row['idpra']) ? '#'.(int)$row['idpra'].' / '.iso_report_e($row['tahun']) : '<span class="text-muted">-</span>'; ?></td>
                                                <td><?= !empty($row['entri']) ? iso_report_e($row['entri']) : '<span class="text-muted">-</span>'; ?></td>
                                            </tr>
                                        <?php endforeach; endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <script>
                        function filterTableByUnit() {
                            var filter = document.getElementById('filterUnit').value.toLowerCase();
                            var rows = document.querySelectorAll('.report-table tbody tr');
                            rows.forEach(function(row) {
                                if(row.cells.length > 4) {
                                    var unitCell = row.cells[4].innerText.toLowerCase();
                                    if(filter === '' || unitCell === filter) {
                                        row.style.display = '';
                                    } else {
                                        row.style.display = 'none';
                                    }
                                }
                            });
                        }
                        
                        document.addEventListener('DOMContentLoaded', function(){
                            if (typeof Chart === 'undefined') return;
                            var answerCanvas = document.getElementById('chartIsoAnswer');
                            var unitCanvas = document.getElementById('chartIsoUnit');
                            if (answerCanvas) {
                                new Chart(answerCanvas, {
                                    type: 'doughnut',
                                    data: {
                                        labels: <?= json_encode($answerLabels); ?>,
                                        datasets: [{ data: <?= json_encode($answerData); ?>, backgroundColor: ['#2563eb','#10b981','#f59e0b','#ef4444','#64748b'] }]
                                    },
                                    options: { responsive:true, maintainAspectRatio:false, plugins:{ legend:{ position:'bottom' } } }
                                });
                            }
                            if (unitCanvas) {
                                new Chart(unitCanvas, {
                                    type: 'bar',
                                    data: {
                                        labels: <?= json_encode($unitLabels); ?>,
                                        datasets: [{ label:'Indikator Terisi', data: <?= json_encode($unitData); ?>, backgroundColor:'#475569', borderRadius:6 }]
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
