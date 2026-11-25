<?php 
include 'nav.php';

// Active year definition
$tahun_aktif = isset($_GET['year']) ? (int)$_GET['year'] : (isset($_SESSION['laporan_year']) ? (int)$_SESSION['laporan_year'] : (int)date('Y'));
$_SESSION['laporan_year'] = $tahun_aktif;

// Determine active view based on GET query or POST submission
$view = 'ringkasan';
if (isset($_GET['view'])) {
    $view = $_GET['view'];
} elseif (isset($_POST['submit_sarmut_graph'])) {
    $view = 'sarmut';
} elseif (isset($_POST['submit_iso_graph'])) {
    $view = 'iso';
} elseif (isset($_POST['submit']) || isset($_POST['submit2'])) {
    $view = 'praaudit';
}

// ----------------------------------------------------
// 1. DATA GATHERING & METRICS COMPUTATION
// ----------------------------------------------------

// SARMUT KPI Metrics
$q_sarmut_count = mysqli_query($kon, "SELECT COUNT(*) FROM sarmut WHERE year = '$tahun_aktif'");
$total_sarmut_pkgs = $q_sarmut_count ? (int)mysqli_fetch_row($q_sarmut_count)[0] : 0;

$sarmuts_list = [];
$q_sarmuts = mysqli_query($kon, "SELECT * FROM sarmut WHERE year = '$tahun_aktif' ORDER BY id DESC");
while ($q_sarmuts && $r = mysqli_fetch_assoc($q_sarmuts)) {
    $sarmut_id = $r['id'];
    // Target subindicators count
    $q_target = mysqli_query($kon, "
        SELECT COUNT(DISTINCT sub.id) AS total
        FROM sarmut_sasaran ss
        JOIN sarmut_indikator si ON si.sasaran_id=ss.id
        JOIN sarmut_subindikator sub ON sub.indikator_id=si.id
        WHERE ss.sarmut_id='$sarmut_id'
    ");
    $target_row = $q_target ? mysqli_fetch_assoc($q_target) : null;
    $target_total = $target_row ? (int)$target_row['total'] : 0;
    
    // Filled answers count
    $q_filled = mysqli_query($kon, "
        SELECT COUNT(DISTINCT CONCAT(j.idpic, ':', j.idsubindikator)) AS total
        FROM sarmut_jawaban j
        WHERE j.idsarmut='$sarmut_id' AND j.nilai IS NOT NULL AND TRIM(j.nilai)<>''
    ");
    $filled_row = $q_filled ? mysqli_fetch_assoc($q_filled) : null;
    $filled_total = $filled_row ? (int)$filled_row['total'] : 0;
    
    // Unit count
    $q_units = mysqli_query($kon, "
        SELECT COUNT(DISTINCT j.idpic) AS total
        FROM sarmut_jawaban j
        WHERE j.idsarmut='$sarmut_id'
    ");
    $units_row = $q_units ? mysqli_fetch_assoc($q_units) : null;
    $units_count = $units_row ? (int)$units_row['total'] : 0;
    
    $r['total_sub'] = $target_total;
    $r['filled_total'] = $filled_total;
    $r['units_count'] = $units_count;
    $sarmuts_list[] = $r;
}

// Calculate SARMUT Average Completion
$total_sarmut_scope = 0;
$total_sarmut_filled = 0;
foreach ($sarmuts_list as $s) {
    $units = max(1, $s['units_count']);
    $total_sarmut_scope += ($s['total_sub'] * $units);
    $total_sarmut_filled += $s['filled_total'];
}
$sarmut_avg = $total_sarmut_scope > 0 ? round(($total_sarmut_filled / $total_sarmut_scope) * 100, 2) : 0;


// ISO KPI Metrics
$q_iso_count = mysqli_query($kon, "SELECT COUNT(*) FROM iso");
$total_iso_pkgs = $q_iso_count ? (int)mysqli_fetch_row($q_iso_count)[0] : 0;

$isos_list = [];
$q_isos = mysqli_query($kon, "SELECT * FROM iso ORDER BY idiso DESC");
while ($q_isos && $r = mysqli_fetch_assoc($q_isos)) {
    $iso_id = $r['idiso'];
    // Target indicators count
    $q_target = mysqli_query($kon, "
        SELECT COUNT(DISTINCT ii.id_isoindikator) AS total
        FROM iso_indikator ii
        LEFT JOIN iso_subklausul sk ON FIND_IN_SET(sk.id_subklausul, REPLACE(ii.id_subklausul, ' ', ''))
        WHERE (CAST(ii.id_iso AS UNSIGNED)='$iso_id' OR CAST(sk.id_iso AS UNSIGNED)='$iso_id') AND ii.status='1'
    ");
    $target_row = $q_target ? mysqli_fetch_assoc($q_target) : null;
    $target_total = $target_row ? (int)$target_row['total'] : 0;
    
    // Filled answers count
    $q_filled = mysqli_query($kon, "
        SELECT COUNT(DISTINCT CONCAT(j.id_unit, ':', j.id_isoindikator)) AS total
        FROM iso_jawaban j
        WHERE j.id_iso='$iso_id' AND j.jawaban IS NOT NULL AND TRIM(j.jawaban)<>''
    ");
    $filled_row = $q_filled ? mysqli_fetch_assoc($q_filled) : null;
    $filled_total = $filled_row ? (int)$filled_row['total'] : 0;

    // Unit count
    $q_units = mysqli_query($kon, "
        SELECT COUNT(DISTINCT j.id_unit) AS total
        FROM iso_jawaban j
        WHERE j.id_iso='$iso_id'
    ");
    $units_row = $q_units ? mysqli_fetch_assoc($q_units) : null;
    $units_count = $units_row ? (int)$units_row['total'] : 0;

    $r['total_ind'] = $target_total;
    $r['filled_total'] = $filled_total;
    $r['units_count'] = $units_count;
    $isos_list[] = $r;
}

// Calculate ISO Average Completion
$total_iso_scope = 0;
$total_iso_filled = 0;
foreach ($isos_list as $i) {
    $units = max(1, $i['units_count']);
    $total_iso_scope += ($i['total_ind'] * $units);
    $total_iso_filled += $i['filled_total'];
}
$iso_avg = $total_iso_scope > 0 ? round(($total_iso_filled / $total_iso_scope) * 100, 2) : 0;

// ISO Global answer distribution
$q_iso_dist = mysqli_query($kon, "SELECT COALESCE(NULLIF(TRIM(jawaban), ''), 'Belum Diisi') AS label, COUNT(*) AS total FROM iso_jawaban GROUP BY label");
$iso_dist_labels = [];
$iso_dist_data = [];
while ($q_iso_dist && $row = mysqli_fetch_assoc($q_iso_dist)) {
    $iso_dist_labels[] = $row['label'];
    $iso_dist_data[] = (int)$row['total'];
}


// Progres Pengisian ISO per Unit
$iso_unit_progress = [];
$q_iso_unit = mysqli_query($kon, "
    SELECT u.id, u.nama, j.id_iso,
           COUNT(DISTINCT j.id_isoindikator) AS total_filled,
           i.nama_iso
    FROM iso_jawaban j
    JOIN unit u ON u.id = j.id_unit
    JOIN iso i ON i.idiso = j.id_iso
    WHERE j.jawaban IS NOT NULL AND TRIM(j.jawaban) <> ''
    GROUP BY j.id_iso, j.id_unit
    ORDER BY u.nama ASC, i.nama_iso ASC
");
while ($q_iso_unit && $row = mysqli_fetch_assoc($q_iso_unit)) {
    // find target in $isos_list
    $target = 0;
    foreach($isos_list as $i) {
        if ($i['idiso'] == $row['id_iso']) {
            $target = $i['total_ind'];
            break;
        }
    }
    $row['total_target'] = $target;
    $iso_unit_progress[] = $row;
}

// Pra Audit KPI Metrics
$q_pra_count = mysqli_query($kon, "SELECT COUNT(*) FROM praaudit WHERE tahun = '$tahun_aktif'");
$total_pra_sessions = $q_pra_count ? (int)mysqli_fetch_row($q_pra_count)[0] : 0;

$praauditColumns = [];
$res_cols = mysqli_query($kon, "SHOW COLUMNS FROM `praaudit`");
if($res_cols) {
    while($row = mysqli_fetch_assoc($res_cols)) {
        $praauditColumns[] = $row['Field'];
    }
}
$hasPaketSarmut = in_array('paketsarmut', $praauditColumns, true);
$hasPaketIso = in_array('paketiso', $praauditColumns, true);
$sarmutJoin = $hasPaketSarmut ? "LEFT JOIN sarmut s ON s.id = praaudit.paketsarmut" : "";
$isoJoin = $hasPaketIso ? "LEFT JOIN iso i ON i.idiso = praaudit.paketiso" : "";
$sarmutSelect = $hasPaketSarmut ? ", s.title as nama_sarmut" : ", '' as nama_sarmut";
$isoSelect = $hasPaketIso ? ", i.nama_iso as nama_iso" : ", '' as nama_iso";

$q_pra_sessions = mysqli_query($kon, "
    SELECT praaudit.*, unit.nama as nama_unit, pp.namapaket, praaudit.status as stapra
           $sarmutSelect
           $isoSelect
    FROM praaudit
    JOIN unit ON praaudit.unit=unit.id
    JOIN paketpertanyaan pp ON praaudit.paket=pp.id
    $sarmutJoin
    $isoJoin
    WHERE praaudit.tahun = '$tahun_aktif'
    ORDER BY unit.nama ASC
");

$pra_sessions = [];
$total_led_q = 0;
$total_led_a = 0;
while ($q_pra_sessions && $row = mysqli_fetch_assoc($q_pra_sessions)) {
    $idpra = $row['id'];
    // Get LED stats for this praaudit
    $q_led = mysqli_query($kon, "
        SELECT COUNT(p.id) AS total_pertanyaan, COUNT(l.idpertanyaan) AS total_jawaban
        FROM pertanyaan p
        LEFT JOIN led l ON l.idpertanyaan = p.id AND l.idpraaudit = '$idpra'
        WHERE p.idpaket = '{$row['paket']}'
    ");
    $led_row = $q_led ? mysqli_fetch_assoc($q_led) : null;
    $row['total_pertanyaan'] = $led_row ? (int)$led_row['total_pertanyaan'] : 0;
    $row['total_jawaban'] = $led_row ? (int)$led_row['total_jawaban'] : 0;
    
    $total_led_q += $row['total_pertanyaan'];
    $total_led_a += $row['total_jawaban'];
    
    $pra_sessions[] = $row;
}
$led_avg = $total_led_q > 0 ? round(($total_led_a / $total_led_q) * 100, 2) : 0;

// Risk Level summary
$riskSummary = ['Sangat Rendah' => 0, 'Rendah' => 0, 'Sedang' => 0, 'Tinggi' => 0, 'Sangat Tinggi' => 0, 'Belum Dinilai' => 0];
$riskQuery = mysqli_query($kon, "
    SELECT
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
    GROUP BY level_risiko
");
if($riskQuery) {
    while($riskRow = mysqli_fetch_assoc($riskQuery)) {
        $riskSummary[$riskRow['level_risiko']] = (int)$riskRow['total'];
    }
}


// Audit KPI Metrics
$q_aud_count = mysqli_query($kon, "SELECT COUNT(*) FROM audit WHERE YEAR(tglmulai) = '$tahun_aktif'");
$total_aud_sessions = $q_aud_count ? (int)mysqli_fetch_row($q_aud_count)[0] : 0;

$q_audits = mysqli_query($kon, "
    SELECT audit.*, unit.nama as nama_unit
    FROM audit
    JOIN unit ON audit.unit=unit.id
    WHERE YEAR(audit.tglmulai) = '$tahun_aktif'
    ORDER BY audit.tglmulai DESC
");

$audit_sessions = [];
$audit_upload_count = 0;
$total_findings = 0;
$total_positives = 0;
$total_notes = 0;

while ($q_audits && $row = mysqli_fetch_assoc($q_audits)) {
    $ka = $row['kodeaudit'];
    if (!empty($row['berkas'])) {
        $audit_upload_count++;
    }
    
    // Count Catatan Audit
    $q_cat = mysqli_query($kon, "SELECT COUNT(*) FROM audit_catatan WHERE kodeaudit='$ka'");
    $row['jml_catatan'] = $q_cat ? (int)mysqli_fetch_row($q_cat)[0] : 0;
    $total_notes += $row['jml_catatan'];
    
    // Count Kondisi Positif
    $q_pos = mysqli_query($kon, "SELECT COUNT(*) FROM audit_kondisi WHERE kodeaudit='$ka' AND jeniskondisi='Positif'");
    $row['jml_positif'] = $q_pos ? (int)mysqli_fetch_row($q_pos)[0] : 0;
    $total_positives += $row['jml_positif'];
    
    // Count Temuan Audit
    $q_tem = mysqli_query($kon, "SELECT COUNT(*) FROM audit_kondisi WHERE kodeaudit='$ka' AND jeniskondisi='Temuan'");
    $row['jml_temuan'] = $q_tem ? (int)mysqli_fetch_row($q_tem)[0] : 0;
    $total_findings += $row['jml_temuan'];
    
    $audit_sessions[] = $row;
}
$audit_upload_pct = $total_aud_sessions > 0 ? round(($audit_upload_count / $total_aud_sessions) * 100, 2) : 0;

// ----------------------------------------------------
// 2. DETAILED GRAPHS COMPUTATION (ON POST SUBMISSION)
// ----------------------------------------------------

// A. Sarmut graph per Sasaran (Kategori)
$sasaran_labels = [];
$sasaran_pcts = [];
$sarmut_selected_row = null;
if (isset($_POST['submit_sarmut_graph'])) {
    $sarmut_select_id = (int)$_POST['sarmut_select_id'];
    $sarmut_selected_row = mysqli_fetch_assoc(mysqli_query($kon, "SELECT * FROM sarmut WHERE id='$sarmut_select_id'"));
    if ($sarmut_selected_row) {
        $q_sasaran_progress = mysqli_query($kon, "
            SELECT ss.id, ss.sasaran,
                   COUNT(DISTINCT sub.id) AS total_sub,
                   COUNT(DISTINCT CASE WHEN j.nilai IS NOT NULL AND TRIM(j.nilai)<>'' THEN CONCAT(j.idpic, ':', sub.id) END) AS filled_sub,
                   COUNT(DISTINCT j.idpic) AS unit_count
            FROM sarmut_sasaran ss
            LEFT JOIN sarmut_indikator si ON si.sasaran_id = ss.id
            LEFT JOIN sarmut_subindikator sub ON sub.indikator_id = si.id
            LEFT JOIN sarmut_jawaban j ON j.idsubindikator = sub.id AND j.idsarmut = ss.sarmut_id
            WHERE ss.sarmut_id = '$sarmut_select_id'
            GROUP BY ss.id
        ");
        while ($q_sasaran_progress && $row = mysqli_fetch_assoc($q_sasaran_progress)) {
            $sasaran_labels[] = strlen($row['sasaran']) > 30 ? substr($row['sasaran'], 0, 27).'...' : $row['sasaran'];
            $total_scope = $row['total_sub'] * max(1, $row['unit_count']);
            $pct = $total_scope > 0 ? round(($row['filled_sub'] / $total_scope) * 100, 2) : 0;
            $sasaran_pcts[] = min(100, $pct);
        }
    }
}

// B. ISO graph per Klausul (Kategori)
$klausul_labels = [];
$klausul_pcts = [];
$iso_selected_row = null;
if (isset($_POST['submit_iso_graph'])) {
    $iso_select_id = (int)$_POST['iso_select_id'];
    $iso_selected_row = mysqli_fetch_assoc(mysqli_query($kon, "SELECT * FROM iso WHERE idiso='$iso_select_id'"));
    if ($iso_selected_row) {
        $q_klausul_progress = mysqli_query($kon, "
            SELECT k.id_klausul, k.nomor_klausul, k.nama_klausul,
                   COUNT(DISTINCT ii.id_isoindikator) AS total_ind,
                   COUNT(DISTINCT CASE WHEN j.jawaban IS NOT NULL AND TRIM(j.jawaban)<>'' THEN CONCAT(j.id_unit, ':', ii.id_isoindikator) END) AS filled_ind,
                   COUNT(DISTINCT j.id_unit) AS unit_count
            FROM iso_klausul k
            LEFT JOIN iso_subklausul sk ON CAST(sk.id_klausul AS UNSIGNED) = CAST(k.id_klausul AS UNSIGNED)
            LEFT JOIN iso_indikator ii ON (FIND_IN_SET(sk.id_subklausul, REPLACE(ii.id_subklausul, ' ', '')) OR CAST(ii.id_klausul AS UNSIGNED) = CAST(k.id_klausul AS UNSIGNED)) AND ii.status='1'
            LEFT JOIN iso_jawaban j ON j.id_isoindikator = ii.id_isoindikator AND j.id_iso = '$iso_select_id'
            WHERE k.id_iso = '$iso_select_id'
            GROUP BY k.id_klausul
            ORDER BY k.nomor_klausul ASC
        ");
        while ($q_klausul_progress && $row = mysqli_fetch_assoc($q_klausul_progress)) {
            $label_text = ($row['nomor_klausul'] ? $row['nomor_klausul'].' ' : '').$row['nama_klausul'];
            $klausul_labels[] = strlen($label_text) > 30 ? substr($label_text, 0, 27).'...' : $label_text;
            $total_scope = $row['total_ind'] * max(1, $row['unit_count']);
            $pct = $total_scope > 0 ? round(($row['filled_ind'] / $total_scope) * 100, 2) : 0;
            $klausul_pcts[] = min(100, $pct);
        }
    }
}

?>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<style>
    /* Premium aesthetics and layouts */
    .dashboard-kpi-card {
        border: none;
        border-radius: 1rem;
        color: white;
        transition: all 0.3s ease;
        overflow: hidden;
        position: relative;
        box-shadow: 0 10px 20px rgba(15, 23, 42, 0.05);
    }
    .dashboard-kpi-card:hover {
        transform: translateY(-3px);
    }
    .kpi-gradient-sarmut {
        background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
    }
    .kpi-gradient-iso {
        background: linear-gradient(135deg, #06b6d4 0%, #0ea5e9 100%);
    }
    .kpi-gradient-pra {
        background: linear-gradient(135deg, #14b8a6 0%, #10b981 100%);
    }
    .kpi-gradient-audit {
        background: linear-gradient(135deg, #f97316 0%, #ef4444 100%);
    }
    .kpi-icon {
        position: absolute;
        right: 1.25rem;
        bottom: 0.75rem;
        font-size: 4.5rem;
        opacity: 0.18;
        pointer-events: none;
    }
    .card-header-gradient {
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        border-bottom: 1px solid #e2e8f0;
    }
    .table-card {
        border: 1px solid #e5e7eb;
        border-radius: 0.85rem;
        box-shadow: 0 4px 18px rgba(15, 23, 42, 0.04);
        background: #fff;
    }
    .badge-pill-progress {
        font-weight: 700;
        border-radius: 20px;
    }
    .chart-container-custom {
        position: relative;
        height: 330px;
        width: 100%;
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
            <!-- HEADER HERO SECTION -->
            <div class="col-12 mb-4">
                <div class="card bg-light border-0 py-2 px-3 rounded-4 shadow-sm" style="background: linear-gradient(135deg, #f8fafc 0%, #eff6ff 100%); border-left: 5px solid #2563eb !important;">
                    <div class="card-body d-flex flex-column flex-md-row justify-content-between align-items-md-center">
                        <div>
                            <h3 class="mb-1 text-primary font-bold">Laporan Admin</h3>
                            <p class="text-muted mb-0">Dashboard laporan integrasi berdasarkan Sarmut, ISO, Pra Audit, dan Audit.</p>
                        </div>
                        <div class="mt-2 mt-md-0">
                            <form method="get" action="laporan" class="d-inline-block">
                                <input type="hidden" name="view" value="<?= htmlspecialchars($view); ?>">
                                <select name="year" onchange="this.form.submit()" class="form-select form-select-sm border-primary rounded-pill font-bold px-3 py-2 text-primary" style="min-width: 170px; background-color: #ffffff; cursor: pointer; border: 2px solid #2563eb !important;">
                                    <?php
                                    for ($y = 2021; $y <= (int)date('Y') + 1; $y++) {
                                        $sel = ($y == $tahun_aktif) ? 'selected' : '';
                                        echo "<option value='$y' $sel>Tahun Aktif: $y</option>";
                                    }
                                    ?>
                                </select>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SUB-NAVIGATION BAR (Normal URL query-based page reload buttons) -->
            <div class="col-12 mb-4">
                <div class="d-flex flex-wrap gap-2 justify-content-center bg-white p-2 rounded-3 shadow-sm border">
                    <a href="laporan?view=ringkasan" class="btn <?= $view === 'ringkasan' ? 'btn-primary' : 'btn-outline-primary' ?> px-4 font-bold">
                        <i class="bi bi-speedometer2 me-2"></i>Ringkasan
                    </a>
                    <a href="laporan?view=sarmut" class="btn <?= $view === 'sarmut' ? 'btn-primary' : 'btn-outline-primary' ?> px-4 font-bold">
                        <i class="bi bi-list-check me-2"></i>SARMUT
                    </a>
                    <a href="laporan?view=iso" class="btn <?= $view === 'iso' ? 'btn-primary' : 'btn-outline-primary' ?> px-4 font-bold">
                        <i class="bi bi-award me-2"></i>ISO
                    </a>
                    <a href="laporan?view=praaudit" class="btn <?= $view === 'praaudit' ? 'btn-primary' : 'btn-outline-primary' ?> px-4 font-bold">
                        <i class="bi bi-clipboard-check me-2"></i>Pra Audit
                    </a>
                    <a href="laporan?view=risiko" class="btn <?= $view === 'risiko' ? 'btn-primary' : 'btn-outline-primary' ?> px-4 font-bold">
                        <i class="bi bi-shield-exclamation me-2"></i>Profil Risiko
                    </a>
                    <a href="laporan?view=audit" class="btn <?= $view === 'audit' ? 'btn-primary' : 'btn-outline-primary' ?> px-4 font-bold">
                        <i class="bi bi-check2-circle me-2"></i>Audit
                    </a>
                </div>
            </div>

            <div class="col-12">
                
                <?php if ($view === 'ringkasan'): ?>
                    <!-- ========================================== -->
                    <!-- VIEW 1: RINGKASAN                          -->
                    <!-- ========================================== -->
                    <div class="row">
                        <!-- KPI Metric Cards -->
                        <div class="row mb-4 px-0 mx-0">
                            <div class="col-12 col-md-6 col-lg-3 mb-3">
                                <div class="card dashboard-kpi-card kpi-gradient-sarmut">
                                    <div class="card-body">
                                        <h5 class="text-white-50 small text-uppercase font-bold">Sasaran Mutu</h5>
                                        <h2 class="text-white font-black mb-1"><?= $total_sarmut_pkgs; ?> <span class="fs-6 font-normal">Paket</span></h2>
                                        <p class="text-white-50 mb-0 small">Avg Completion: <strong><?= $sarmut_avg; ?>%</strong></p>
                                        <i class="bi bi-list-check kpi-icon"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-6 col-lg-3 mb-3">
                                <div class="card dashboard-kpi-card kpi-gradient-iso">
                                    <div class="card-body">
                                        <h5 class="text-white-50 small text-uppercase font-bold">ISO</h5>
                                        <h2 class="text-white font-black mb-1"><?= $total_iso_pkgs; ?> <span class="fs-6 font-normal">Paket</span></h2>
                                        <p class="text-white-50 mb-0 small">Avg Completion: <strong><?= $iso_avg; ?>%</strong></p>
                                        <i class="bi bi-award kpi-icon"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-6 col-lg-3 mb-3">
                                <div class="card dashboard-kpi-card kpi-gradient-pra">
                                    <div class="card-body">
                                        <h5 class="text-white-50 small text-uppercase font-bold">Pra Audit</h5>
                                        <h2 class="text-white font-black mb-1"><?= $total_pra_sessions; ?> <span class="fs-6 font-normal">Sesi</span></h2>
                                        <p class="text-white-50 mb-0 small">LED Filled: <strong><?= $led_avg; ?>%</strong></p>
                                        <i class="bi bi-clipboard-check kpi-icon"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-6 col-lg-3 mb-3">
                                <div class="card dashboard-kpi-card kpi-gradient-audit">
                                    <div class="card-body">
                                        <h5 class="text-white-50 small text-uppercase font-bold">Audit AMI</h5>
                                        <h2 class="text-white font-black mb-1"><?= $total_aud_sessions; ?> <span class="fs-6 font-normal">Jadwal</span></h2>
                                        <p class="text-white-50 mb-0 small">Laporan Diupload: <strong><?= $audit_upload_pct; ?>%</strong></p>
                                        <i class="bi bi-check2-circle kpi-icon"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Overall Module Comparison Chart -->
                        <div class="col-lg-7 mb-4">
                            <div class="card table-card">
                                <div class="card-header card-header-gradient font-bold text-dark">
                                    Progres Pengisian Modul (%)
                                </div>
                                <div class="card-body p-3">
                                    <div class="chart-container-custom">
                                        <canvas id="chartOverviewModules"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-5 mb-4">
                            <div class="card table-card h-100">
                                <div class="card-header card-header-gradient font-bold text-dark">
                                    Identitas Penjaminan Mutu
                                </div>
                                <div class="card-body">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="flex-shrink-0">
                                            <img src="poltekkes.png" alt="Logo" style="width: 70px;" class="rounded">
                                        </div>
                                        <div class="ms-3">
                                            <h5 class="mb-0 font-bold text-dark">Poltekkes Kemenkes Bengkulu</h5>
                                            <small class="text-muted">Sistem Informasi Penjaminan Mutu Internal (DIGAMI)</small>
                                        </div>
                                    </div>
                                    <hr class="text-muted">
                                    <table class="table table-borderless table-sm small text-dark">
                                        <tr>
                                            <th style="width: 40%">Nama Sistem</th>
                                            <td>DIGAMI (Digital Mutu Internal)</td>
                                        </tr>
                                        <tr>
                                            <th>Versi</th>
                                            <td>2.0-Premium</td>
                                        </tr>
                                        <tr>
                                            <th>Status Pengisian</th>
                                            <td><span class="badge bg-success badge-pill-progress">Aktif</span></td>
                                        </tr>
                                        <tr>
                                            <th>Tahun Monitoring</th>
                                            <td>2021 - <?= date('Y'); ?></td>
                                        </tr>
                                    </table>
                                    <div class="alert alert-info mt-2 mb-0 small">
                                        <i class="fas fa-info-circle me-1"></i> Admin memiliki wewenang penuh untuk memantau, mencetak, dan mengelola paket standar evaluasi mutu.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                <?php elseif ($view === 'sarmut'): ?>
                    <!-- ========================================== -->
                    <!-- VIEW 2: SARMUT                             -->
                    <!-- ========================================== -->
                    <div class="row">
                        <!-- Filter Form for Sasaran Graph -->
                        <div class="col-md-5 mb-4">
                            <div class="card table-card">
                                <div class="card-header card-header-gradient font-bold text-dark">Pilih Grafik per Sasaran (Kategori)</div>
                                <div class="card-body">
                                    <form method="post" action="laporan?view=sarmut">
                                        <div class="form-group mb-3">
                                            <label class="form-label font-semibold">Pilih Paket SARMUT</label>
                                            <select class="form-control js-example-basic-single" name="sarmut_select_id" style="width: 100%;">
                                                <?php foreach($sarmuts_list as $s): ?>
                                                    <option value="<?= $s['id']; ?>" <?= (isset($sarmut_select_id) && $sarmut_select_id == $s['id']) ? 'selected' : ''; ?>><?= htmlspecialchars($s['title']); ?> (<?= $s['year']; ?>)</option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <button type="submit" name="submit_sarmut_graph" class="btn btn-primary btn-sm w-100 py-2"><i class="fas fa-chart-bar me-1"></i> Tampilkan Grafik</button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Sarmut General metrics -->
                        <div class="col-md-7 mb-4">
                            <div class="card table-card h-100">
                                <div class="card-header card-header-gradient font-bold text-dark">Ringkasan SARMUT</div>
                                <div class="card-body d-flex flex-column justify-content-between">
                                    <div class="row">
                                        <div class="col-6 mb-3 border-end">
                                            <span class="text-muted small">Total Subindikator</span>
                                            <h4 class="mb-0 text-dark font-bold"><?= array_sum(array_column($sarmuts_list, 'total_sub')); ?></h4>
                                        </div>
                                        <div class="col-6 mb-3">
                                            <span class="text-muted small">Pengisian Terisi</span>
                                            <h4 class="mb-0 text-dark font-bold"><?= array_sum(array_column($sarmuts_list, 'filled_total')); ?></h4>
                                        </div>
                                    </div>
                                    <div class="mb-0">
                                        <span class="text-muted small">Rata-rata Penyelesaian</span>
                                        <h4 class="mb-2 text-primary font-black"><?= $sarmut_avg; ?>%</h4>
                                        <div class="progress" style="height: 8px;">
                                            <div class="progress-bar bg-primary" role="progressbar" style="width: <?= $sarmut_avg; ?>%;"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Sarmut Detailed Sasaran Graph (Displayed on demand) -->
                        <?php if (isset($_POST['submit_sarmut_graph']) && !empty($sasaran_labels)): ?>
                        <div class="col-12 mb-4">
                            <div class="card table-card">
                                <div class="card-header bg-primary text-white font-bold">
                                    Grafik SARMUT: <?= htmlspecialchars($sarmut_selected_row['title']); ?> (<?= $sarmut_selected_row['year']; ?>) per Sasaran (Kategori)
                                </div>
                                <div class="card-body p-3">
                                    <canvas id="grafikSarmutSasaran" style="max-height:300px;"></canvas>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- SARMUT List Table -->
                        <div class="col-12 mb-4">
                            <div class="card table-card">
                                <div class="card-header card-header-gradient font-bold text-dark d-flex justify-content-between align-items-center">
                                    <span>Daftar Paket Sasaran Mutu (SARMUT)</span>
                                    <a href="sarmut.manage" class="btn btn-sm btn-primary"><i class="fas fa-cog"></i> Kelola SARMUT</a>
                                </div>
                                <div class="card-body px-3 py-3">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped table-hover align-middle">
                                            <thead>
                                                <tr>
                                                    <th>No.</th>
                                                    <th>Judul Paket</th>
                                                    <th>Tahun</th>
                                                    <th>Subindikator</th>
                                                    <th>Pengisian Terisi</th>
                                                    <th>Progres</th>
                                                    <th>Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php 
                                                $no = 1;
                                                foreach ($sarmuts_list as $s): 
                                                    $pct = $s['total_sub'] > 0 ? round(($s['filled_total'] / ($s['total_sub'] * max(1, $s['units_count']))) * 100, 2) : 0;
                                                    $pct = min(100, $pct);
                                                ?>
                                                <tr>
                                                    <td><?= $no++; ?></td>
                                                    <td><strong><?= htmlspecialchars($s['title']); ?></strong></td>
                                                    <td><?= htmlspecialchars($s['year']); ?></td>
                                                    <td><?= $s['total_sub']; ?></td>
                                                    <td><?= $s['filled_total']; ?></td>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <span class="me-2 text-dark font-bold"><?= $pct; ?>%</span>
                                                            <div class="progress flex-grow-1" style="height: 6px;">
                                                                <div class="progress-bar bg-success" role="progressbar" style="width: <?= $pct; ?>%"></div>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <a class="btn btn-sm btn-info text-white" href="sarmut.monitor.php?sarmut_id=<?= $s['id']; ?>"><i class="fas fa-desktop"></i> Monitor</a>
                                                        <a class="btn btn-sm btn-secondary" href="sarmut.laporan.php?sarmut_id=<?= $s['id']; ?>"><i class="fas fa-file-alt"></i> Laporan</a>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                <?php elseif ($view === 'iso'): ?>
                    <!-- ========================================== -->
                    <!-- VIEW 3: ISO                                -->
                    <!-- ========================================== -->
                    <div class="row">
                        <!-- Filter Form for Klausul Graph -->
                        <div class="col-md-5 mb-4">
                            <div class="card table-card">
                                <div class="card-header card-header-gradient font-bold text-dark">Pilih Grafik per Klausul (Kategori)</div>
                                <div class="card-body">
                                    <form method="post" action="laporan?view=iso">
                                        <div class="form-group mb-3">
                                            <label class="form-label font-semibold">Pilih Paket ISO</label>
                                            <select class="form-control js-example-basic-single" name="iso_select_id" style="width: 100%;">
                                                <?php foreach($isos_list as $i): ?>
                                                    <option value="<?= $i['idiso']; ?>" <?= (isset($iso_select_id) && $iso_select_id == $i['idiso']) ? 'selected' : ''; ?>><?= htmlspecialchars($i['nama_iso']); ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <button type="submit" name="submit_iso_graph" class="btn btn-primary btn-sm w-100 py-2"><i class="fas fa-chart-bar me-1"></i> Tampilkan Grafik</button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- ISO metrics -->
                        <div class="col-md-7 mb-4">
                            <div class="card table-card h-100">
                                <div class="card-header card-header-gradient font-bold text-dark">Ringkasan ISO</div>
                                <div class="card-body d-flex flex-column justify-content-between">
                                    <div class="row">
                                        <div class="col-6 mb-3 border-end">
                                            <span class="text-muted small">Total Indikator Aktif</span>
                                            <h4 class="mb-0 text-dark font-bold"><?= array_sum(array_column($isos_list, 'total_ind')); ?></h4>
                                        </div>
                                        <div class="col-6 mb-3">
                                            <span class="text-muted small">Total Jawaban Terisi</span>
                                            <h4 class="mb-0 text-dark font-bold"><?= array_sum(array_column($isos_list, 'filled_total')); ?></h4>
                                        </div>
                                    </div>
                                    <div class="mb-0">
                                        <span class="text-muted small">Rata-rata Progres</span>
                                        <h4 class="mb-2 text-info font-black"><?= $iso_avg; ?>%</h4>
                                        <div class="progress" style="height: 8px;">
                                            <div class="progress-bar bg-info" role="progressbar" style="width: <?= $iso_avg; ?>%;"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ISO Detailed Klausul Graph (Displayed on demand) -->
                        <?php if (isset($_POST['submit_iso_graph']) && !empty($klausul_labels)): ?>
                        <div class="col-12 mb-4">
                            <div class="card table-card">
                                <div class="card-header bg-info text-white font-bold">
                                    Grafik ISO: <?= htmlspecialchars($iso_selected_row['nama_iso']); ?> per Klausul (Standar/Kategori)
                                </div>
                                <div class="card-body p-3">
                                    <canvas id="grafikIsoKlausul" style="max-height:300px;"></canvas>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- ISO answer distribution graph -->
                        <div class="col-12 mb-4">
                            <div class="card table-card">
                                <div class="card-header card-header-gradient font-bold text-dark">Distribusi Jawaban ISO</div>
                                <div class="card-body p-3">
                                    <div class="chart-container-custom" style="height: 200px;">
                                        <canvas id="chartIsoDist"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ISO list Table -->
                        <div class="col-12 mb-4">
                            <div class="card table-card">
                                <div class="card-header card-header-gradient font-bold text-dark d-flex justify-content-between align-items-center">
                                    <span>Daftar Paket Standar ISO</span>
                                    <a href="iso" class="btn btn-sm btn-primary"><i class="fas fa-award"></i> Kelola ISO</a>
                                </div>
                                <div class="card-body px-3 py-3">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped table-hover align-middle">
                                            <thead>
                                                <tr>
                                                    <th>No.</th>
                                                    <th>Nama Standar ISO</th>
                                                    <th>Total Indikator</th>
                                                    <th>Jawaban Unit</th>
                                                    <th>Progres Pengisian</th>
                                                    <th>Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php 
                                                $no = 1;
                                                foreach ($isos_list as $i): 
                                                    $pct = $i['total_ind'] > 0 ? round(($i['filled_total'] / ($i['total_ind'] * max(1, $i['units_count']))) * 100, 2) : 0;
                                                    $pct = min(100, $pct);
                                                ?>
                                                <tr>
                                                    <td><?= $no++; ?></td>
                                                    <td><strong><?= htmlspecialchars($i['nama_iso']); ?></strong></td>
                                                    <td><?= $i['total_ind']; ?></td>
                                                    <td><?= $i['filled_total']; ?></td>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <span class="me-2 text-dark font-bold"><?= $pct; ?>%</span>
                                                            <div class="progress flex-grow-1" style="height: 6px;">
                                                                <div class="progress-bar bg-info" role="progressbar" style="width: <?= $pct; ?>%"></div>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <a class="btn btn-sm btn-secondary" href="iso.laporan.php?iso_id=<?= $i['idiso']; ?>"><i class="fas fa-file-alt"></i> Lihat Laporan</a>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ISO Progress per Unit Table -->
                        <div class="col-12 mb-4">
                            <div class="card table-card">
                                <div class="card-header card-header-gradient font-bold text-dark">
                                    Progres Pengisian ISO per Unit
                                </div>
                                <div class="card-body px-3 py-3">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped table-hover align-middle">
                                            <thead>
                                                <tr>
                                                    <th>No.</th>
                                                    <th>Nama Unit</th>
                                                    <th>Nama Standar ISO</th>
                                                    <th>Total Target Indikator</th>
                                                    <th>Jawaban Terisi</th>
                                                    <th>Progres</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php 
                                                if(empty($iso_unit_progress)): ?>
                                                    <tr><td colspan="6" class="text-center text-muted">Belum ada data pengisian.</td></tr>
                                                <?php else:
                                                $no = 1;
                                                foreach ($iso_unit_progress as $up): 
                                                    $pct = $up['total_target'] > 0 ? round(($up['total_filled'] / $up['total_target']) * 100, 2) : 0;
                                                    $pct = min(100, $pct);
                                                ?>
                                                <tr>
                                                    <td><?= $no++; ?></td>
                                                    <td><strong><?= htmlspecialchars($up['nama'] ?? '-'); ?></strong></td>
                                                    <td><?= htmlspecialchars($up['nama_iso']); ?></td>
                                                    <td><?= $up['total_target']; ?></td>
                                                    <td><?= $up['total_filled']; ?></td>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <span class="me-2 text-dark font-bold"><?= $pct; ?>%</span>
                                                            <div class="progress flex-grow-1" style="height: 6px;">
                                                                <div class="progress-bar bg-<?= $pct == 100 ? 'success' : 'info'; ?>" role="progressbar" style="width: <?= $pct; ?>%"></div>
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <?php endforeach; endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                <?php elseif ($view === 'praaudit'): 
                    $pra_selesai_count = 0;
                    $pra_progres_count = 0;
                    foreach ($pra_sessions as $pra) {
                        if ($pra['stapra'] === 'Selesai') {
                            $pra_selesai_count++;
                        } else {
                            $pra_progres_count++;
                        }
                    }
                ?>
                    <!-- ========================================== -->
                    <!-- VIEW 4: PRA AUDIT                          -->
                    <!-- ========================================== -->
                    <div class="row">
                        <!-- KPI Metric Cards for Pra Audit -->
                        <div class="col-12 mb-4">
                            <div class="row">
                                <div class="col-12 col-md-3 mb-3">
                                    <div class="card shadow-sm border p-3 bg-white text-dark rounded-3 h-100" style="border-left: 5px solid #3b82f6 !important;">
                                        <span class="text-muted small text-uppercase font-bold">Total Sesi Pra Audit</span>
                                        <h3 class="mb-0 text-dark font-black mt-2"><?= $total_pra_sessions; ?> <span class="fs-6 font-normal">Sesi</span></h3>
                                        <p class="text-muted small mb-0 mt-1">Sesi evaluasi terdokumentasi</p>
                                    </div>
                                </div>
                                <div class="col-12 col-md-3 mb-3">
                                    <div class="card shadow-sm border p-3 bg-white text-dark rounded-3 h-100" style="border-left: 5px solid #10b981 !important;">
                                        <span class="text-muted small text-uppercase font-bold">Rata-rata Pengisian LED</span>
                                        <h3 class="mb-0 text-success font-black mt-2"><?= $led_avg; ?>%</h3>
                                        <p class="text-muted small mb-0 mt-1">Rasio pengisian kuesioner</p>
                                    </div>
                                </div>
                                <div class="col-12 col-md-3 mb-3">
                                    <div class="card shadow-sm border p-3 bg-white text-dark rounded-3 h-100" style="border-left: 5px solid #f59e0b !important;">
                                        <span class="text-muted small text-uppercase font-bold">Sesi Selesai</span>
                                        <h3 class="mb-0 text-warning font-black mt-2"><?= $pra_selesai_count; ?> <span class="fs-6 font-normal">Sesi</span></h3>
                                        <p class="text-muted small mb-0 mt-1">Evaluasi yang telah ditutup</p>
                                    </div>
                                </div>
                                <div class="col-12 col-md-3 mb-3">
                                    <div class="card shadow-sm border p-3 bg-white text-dark rounded-3 h-100" style="border-left: 5px solid #ea580c !important;">
                                        <span class="text-muted small text-uppercase font-bold">Sesi Berjalan (Progres)</span>
                                        <h3 class="mb-0 text-danger font-black mt-2"><?= $pra_progres_count; ?> <span class="fs-6 font-normal">Sesi</span></h3>
                                        <p class="text-muted small mb-0 mt-1">Evaluasi aktif di unit</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Standard and Unit LED Reports Forms -->
                        <div class="col-lg-6 mb-4">
                            <div class="card table-card h-100">
                                <div class="card-header card-header-gradient font-bold text-dark"><i class="fas fa-chart-bar me-2 text-primary"></i>Laporan Grafik Per Standar</div>
                                <div class="card-body">
                                    <form method="post" action="laporan?view=praaudit">
                                        <div class="row">
                                            <div class="form-group col-md-12 mb-3">
                                                <label class="form-label font-semibold">Pilih Standar</label>
                                                <select class="js-example-basic-single form-control" name="standar" id="standar_new" style="width: 100%;">
                                                    <?php 
                                                    $standar=mysqli_query($kon,"SELECT MIN(id) AS id, nostandar, namastandar FROM kriteria GROUP BY nostandar, namastandar ORDER BY nostandar ASC");
                                                    while($s=mysqli_fetch_array($standar))
                                                    {
                                                        ?>
                                                        <option value="<?=$s['id']?>" <?= (isset($_POST['standar']) && $_POST['standar'] == $s['id']) ? 'selected' : ''; ?>><?=$s['nostandar'].'. '.$s['namastandar'];?></option>
                                                        <?php
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                            <div class="form-group col-md-12 mb-3">
                                                <label class="form-label font-semibold">Pilih Tahun</label>
                                                <select class="form-control js-example-basic-single" name="tahun" id="tahun_new" style="width: 100%;">
                                                    <?php 
                                                    for($thn=2021;$thn<=date('Y');$thn++)
                                                    {
                                                        ?>
                                                        <option value="<?=$thn;?>" <?= (isset($_POST['tahun']) && $_POST['tahun'] == $thn) ? 'selected' : ($thn == date('Y') ? 'selected' : ''); ?>><?=$thn;?></option>
                                                        <?php
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                            <div class="col-md-12">
                                                <button name="submit" class="btn btn-primary btn-sm px-3 mt-2"><i class="fas fa-chart-line me-1"></i> Tampilkan Grafik Standar</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-6 mb-4">
                            <div class="card table-card h-100">
                                <div class="card-header card-header-gradient font-bold text-dark"><i class="fas fa-chart-pie me-2 text-primary"></i>Laporan Grafik Per Unit</div>
                                <div class="card-body">
                                    <form method="post" action="laporan?view=praaudit">
                                        <div class="row">
                                            <div class="form-group col-md-12 mb-3">
                                                <label class="form-label font-semibold">Pilih Tahun</label>
                                                <select class="form-control js-example-basic-single" name="tahun2" id="tahun2_new" style="width: 100%;">
                                                    <?php 
                                                    for($thn=2021;$thn<=date('Y');$thn++)
                                                    {
                                                        ?>
                                                        <option value="<?=$thn;?>" <?= (isset($_POST['tahun2']) && $_POST['tahun2'] == $thn) ? 'selected' : ($thn == date('Y') ? 'selected' : ''); ?>><?=$thn;?></option>
                                                        <?php
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                            <div class="col-md-12">
                                                <button name="submit2" class="btn btn-primary btn-sm px-3 mt-4"><i class="fas fa-chart-line me-1"></i> Tampilkan Grafik Unit</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Rendered LED graphs from original form submissions -->
                        <?php 
                        if(isset($_POST['submit'])) {
                            $std=$_POST['standar'];
                            $thn=$_POST['tahun'];
                            $gns=mysqli_fetch_array(mysqli_query($kon,"SELECT * FROM kriteria where id='$std'"));
                            ?>
                            <div class="col-lg-12 mb-4">
                                <div class="card table-card border-info">
                                    <div class="card-header bg-info text-white font-bold d-flex justify-content-between align-items-center">
                                        <span>Grafik LED: <?= htmlspecialchars($gns['nostandar'] ?? '-').". ".htmlspecialchars($gns['namastandar'] ?? '-')." | Tahun ".$thn; ?></span>
                                        <span class="badge bg-white text-info">Bar Chart</span>
                                    </div>
                                    <div class="card-body p-3">
                                        <?php 
                                        $led=mysqli_query($kon,"SELECT sum(led.koreksi) as koreksi, led.idpraaudit as idpra, count(pertanyaan.id) as jtanya FROM led join pertanyaan on led.idpertanyaan=pertanyaan.id  join praaudit on led.idpraaudit=praaudit.id where kriteria='$gns[id]' and tahun='$thn'  and praaudit.status='Selesai'  group by led.idpraaudit");
                                        if(mysqli_num_rows($led)>0) {
                                            $label = [];
                                            $koreksi = [];
                                            while($l=mysqli_fetch_array($led)) {
                                                $unit=mysqli_fetch_array(mysqli_query($kon,"SELECT * from unit join praaudit on praaudit.unit=unit.id where praaudit.id='$l[idpra]' and praaudit.tahun='$thn'"));
                                                $nilai=round(($l['koreksi']/$l['jtanya']*100),2);
                                                $label[]=$unit['nama'];
                                                $koreksi[]=$nilai;
                                            }
                                            ?>
                                            <canvas id="grafiklaporan" style="max-height:350px;"></canvas>
                                            <script>
                                                document.addEventListener("DOMContentLoaded", function() {
                                                    const ctx = document.getElementById('grafiklaporan');
                                                    let bg = [];
                                                    for (let i = 0; i < <?= count($label); ?>; i++) {
                                                        const randomNum = () => Math.floor(Math.random() * (235 - 52 + 1) + 52);
                                                        bg.push(`rgb(${randomNum()},${randomNum()},${randomNum()})`);
                                                    }
                                                    new Chart(ctx, {
                                                        type: 'bar',
                                                        data: {
                                                            labels: <?=json_encode($label);?>,
                                                            datasets: [{
                                                                label: 'Terpenuhi (%)',
                                                                backgroundColor: bg,
                                                                borderColor: 'rgb(0, 0, 0)',
                                                                data: <?=json_encode($koreksi);?>,
                                                                borderWidth: 1
                                                            }]
                                                        },
                                                        options: {
                                                            indexAxis: 'y',
                                                            responsive: true,
                                                            scales: {
                                                                x: { beginAtZero: true, max: 100 }
                                                            }
                                                        }
                                                    });
                                                });
                                            </script>
                                            <?php
                                        } else {
                                            echo '<div class="alert alert-info mb-0">Data tidak ditemukan atau Pra Audit belum berstatus "Selesai"</div>';
                                        }
                                        ?>
                                    </div>
                                </div>
                            </div>
                            <?php
                        } elseif(isset($_POST['submit2'])) {
                            $thn=$_POST['tahun2'];           
                            ?>
                            <div class="col-lg-12 mb-4">
                                <div class="card table-card border-primary">
                                    <div class="card-header bg-primary text-white font-bold d-flex justify-content-between align-items-center">
                                        <span>Grafik Per Unit | Tahun <?= $thn; ?></span>
                                        <span class="badge bg-white text-primary">Bar Chart</span>
                                    </div>
                                    <div class="card-body p-3">
                                        <?php 
                                        $led=mysqli_query($kon,"SELECT sum(led.koreksi) as koreksi, led.idpraaudit as idpra, count(pertanyaan.id) as jtanya FROM led join pertanyaan on led.idpertanyaan=pertanyaan.id  join praaudit on led.idpraaudit=praaudit.id where tahun='$thn' and praaudit.status='Selesai' group by led.idpraaudit");
                                        if(mysqli_num_rows($led)>0) {
                                            $label = [];
                                            $koreksi = [];
                                            while($l=mysqli_fetch_array($led)) {
                                                $unit=mysqli_fetch_array(mysqli_query($kon,"SELECT * from unit join praaudit on praaudit.unit=unit.id where praaudit.id='$l[idpra]' and praaudit.tahun='$thn'"));
                                                $nilai=round(($l['koreksi']/$l['jtanya']*100),2);
                                                $label[]=$unit['nama'];
                                                $koreksi[]=$nilai;
                                            }
                                            ?>
                                            <canvas id="grafiklaporan" style="max-height:350px;"></canvas>
                                            <script>
                                                document.addEventListener("DOMContentLoaded", function() {
                                                    const ctx = document.getElementById('grafiklaporan');
                                                    let bg = [];
                                                    for (let i = 0; i < <?= count($label); ?>; i++) {
                                                        const randomNum = () => Math.floor(Math.random() * (235 - 52 + 1) + 52);
                                                        bg.push(`rgb(${randomNum()}, ${randomNum()}, ${randomNum()})`);
                                                    }
                                                    new Chart(ctx, {
                                                        type: 'bar',
                                                        data: {
                                                            labels: <?=json_encode($label);?>,
                                                            datasets: [{
                                                                label: 'Terpenuhi (%)',
                                                                backgroundColor: bg,
                                                                borderColor: 'rgb(0, 0, 0)',
                                                                data: <?=json_encode($koreksi);?>,
                                                                borderWidth: 1
                                                            }]
                                                        },
                                                        options: {
                                                            indexAxis: 'y',
                                                            responsive: true,
                                                            scales: {
                                                                x: { beginAtZero: true, max: 100 }
                                                            }
                                                        }
                                                    });
                                                });
                                            </script>
                                            <?php
                                        } else {
                                            echo '<div class="alert alert-info mb-0">Data tidak ditemukan atau Pra Audit belum berstatus "Selesai"</div>';
                                        }
                                        ?>
                                    </div>
                                </div>
                            </div>
                            <?php
                        }
                        ?>

                    </div>

                <?php elseif ($view === 'risiko'): 
                    // Calculate Profil Risiko Metrics
                    $q_global_risk = mysqli_query($kon, "SELECT AVG(l.risiko) FROM led l JOIN praaudit pr ON l.idpraaudit = pr.id WHERE l.risiko > 0 AND pr.tahun = '$tahun_aktif'");
                    $global_avg_risk = $q_global_risk ? (float)mysqli_fetch_row($q_global_risk)[0] : 0;
                    
                    $q_high_risk = mysqli_query($kon, "SELECT COUNT(*) FROM led l JOIN praaudit pr ON l.idpraaudit = pr.id WHERE l.risiko >= 10 AND pr.tahun = '$tahun_aktif'");
                    $total_high_risk = $q_high_risk ? (int)mysqli_fetch_row($q_high_risk)[0] : 0;
                    
                    $q_highest_std = mysqli_query($kon, "
                        SELECT k.namastandar, AVG(l.risiko) AS avg_r 
                        FROM led l 
                        JOIN pertanyaan p ON l.idpertanyaan=p.id 
                        JOIN kriteria k ON p.kriteria=k.id 
                        JOIN praaudit pr ON l.idpraaudit = pr.id
                        WHERE l.risiko > 0 AND pr.tahun = '$tahun_aktif'
                        GROUP BY k.namastandar 
                        ORDER BY avg_r DESC 
                        LIMIT 1
                    ");
                    $highest_std_row = $q_highest_std ? mysqli_fetch_assoc($q_highest_std) : null;
                    $highest_std_name = $highest_std_row ? $highest_std_row['namastandar'] : '-';
                    $highest_std_val = $highest_std_row ? round($highest_std_row['avg_r'], 2) : 0;
                    
                    $q_highest_unit = mysqli_query($kon, "
                        SELECT u.nama, AVG(l.risiko) AS avg_r 
                        FROM led l 
                        JOIN praaudit pr ON l.idpraaudit=pr.id 
                        JOIN unit u ON pr.unit=u.id 
                        WHERE l.risiko > 0 AND pr.tahun = '$tahun_aktif'
                        GROUP BY u.nama 
                        ORDER BY avg_r DESC 
                        LIMIT 1
                    ");
                    $highest_unit_row = $q_highest_unit ? mysqli_fetch_assoc($q_highest_unit) : null;
                    $highest_unit_name = $highest_unit_row ? $highest_unit_row['nama'] : '-';
                    $highest_unit_val = $highest_unit_row ? round($highest_unit_row['avg_r'], 2) : 0;

                    // Global risk per Standard
                    $q_std_risks = mysqli_query($kon, "
                        SELECT k.nostandar, k.namastandar, AVG(l.risiko) AS avg_r 
                        FROM led l 
                        JOIN pertanyaan p ON l.idpertanyaan=p.id 
                        JOIN kriteria k ON p.kriteria=k.id 
                        JOIN praaudit pr ON l.idpraaudit = pr.id
                        WHERE l.risiko > 0 AND pr.tahun = '$tahun_aktif'
                        GROUP BY k.nostandar, k.namastandar 
                        ORDER BY k.nostandar ASC
                    ");
                    $std_labels = [];
                    $std_data = [];
                    while ($q_std_risks && $row = mysqli_fetch_assoc($q_std_risks)) {
                        $std_labels[] = $row['nostandar'] . '. ' . (strlen($row['namastandar']) > 25 ? substr($row['namastandar'], 0, 22).'...' : $row['namastandar']);
                        $std_data[] = (float)round($row['avg_r'], 2);
                    }

                    // Academic vs Non-Academic
                    $q_unit_risks = mysqli_query($kon, "
                        SELECT pr.unit as unit_id, u.nama as nama_unit, AVG(l.risiko) AS avg_r
                        FROM led l
                        JOIN praaudit pr ON l.idpraaudit = pr.id
                        JOIN unit u ON pr.unit = u.id
                        WHERE l.risiko > 0 AND pr.tahun = '$tahun_aktif'
                        GROUP BY pr.unit, u.nama
                        ORDER BY avg_r DESC
                    ");
                    $acad_labels = [];
                    $acad_data = [];
                    $non_acad_labels = [];
                    $non_acad_data = [];
                    $academic_ids = [6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 42];
                    while ($q_unit_risks && $row = mysqli_fetch_assoc($q_unit_risks)) {
                        if (in_array((int)$row['unit_id'], $academic_ids, true)) {
                            $acad_labels[] = $row['nama_unit'];
                            $acad_data[] = (float)round($row['avg_r'], 2);
                        } else {
                            $non_acad_labels[] = $row['nama_unit'];
                            $non_acad_data[] = (float)round($row['avg_r'], 2);
                        }
                    }

                    // Standard Breakdown Table
                    $q_std_breakdown = mysqli_query($kon, "
                        SELECT k.nostandar, k.namastandar,
                               COUNT(l.id) AS total_dinilai,
                               AVG(l.risiko) AS avg_r,
                               SUM(CASE WHEN l.risiko BETWEEN 10 AND 14 THEN 1 ELSE 0 END) AS tinggi_count,
                               SUM(CASE WHEN l.risiko >= 15 THEN 1 ELSE 0 END) AS sangat_tinggi_count
                        FROM led l
                        JOIN pertanyaan p ON l.idpertanyaan=p.id
                        JOIN kriteria k ON p.kriteria=k.id
                        JOIN praaudit pr ON l.idpraaudit = pr.id
                        WHERE l.risiko > 0 AND pr.tahun = '$tahun_aktif'
                        GROUP BY k.nostandar, k.namastandar
                        ORDER BY k.nostandar ASC
                    ");
                    $std_breakdown_rows = [];
                    while ($q_std_breakdown && $row = mysqli_fetch_assoc($q_std_breakdown)) {
                        $std_breakdown_rows[] = $row;
                    }

                    if (!function_exists('get_risk_badge')) {
                        function get_risk_badge($avg_score) {
                            $score = (float)$avg_score;
                            if($score <= 0) return ['label' => 'Belum Dinilai', 'class' => 'bg-secondary'];
                            if($score <= 2) return ['label' => 'Sangat Rendah', 'class' => 'bg-success'];
                            if($score <= 4) return ['label' => 'Rendah', 'class' => 'bg-info text-dark'];
                            if($score <= 9) return ['label' => 'Sedang', 'class' => 'bg-warning text-dark'];
                            if($score <= 14) return ['label' => 'Tinggi', 'class' => 'bg-orange text-white', 'style' => 'background-color: #f97316 !important;'];
                            return ['label' => 'Sangat Tinggi', 'class' => 'bg-danger'];
                        }
                    }
                ?>
                    <!-- ========================================== -->
                    <!-- VIEW 6: PROFIL RISIKO                      -->
                    <!-- ========================================== -->
                    <div class="row">
                        <!-- KPI Metric Cards for Profil Risiko -->
                        <div class="col-12 mb-4">
                            <div class="row">
                                <div class="col-12 col-md-3 mb-3">
                                    <div class="card shadow-sm border p-3 bg-white text-dark rounded-3 h-100" style="border-left: 5px solid #2563eb !important;">
                                        <span class="text-muted small text-uppercase font-bold">Rata-rata Risiko Global</span>
                                        <h3 class="mb-0 text-primary font-black mt-2"><?= number_format($global_avg_risk, 2, ',', '.'); ?></h3>
                                        <p class="text-muted small mb-0 mt-1">Skor risiko rata-rata LED</p>
                                    </div>
                                </div>
                                <div class="col-12 col-md-3 mb-3">
                                    <div class="card shadow-sm border p-3 bg-white text-dark rounded-3 h-100" style="border-left: 5px solid #ef4444 !important;">
                                        <span class="text-muted small text-uppercase font-bold">Item Risiko Tinggi & Ekstrim</span>
                                        <h3 class="mb-0 text-danger font-black mt-2"><?= $total_high_risk; ?> <span class="fs-6 font-normal">Item</span></h3>
                                        <p class="text-muted small mb-0 mt-1">Skor risiko >= 10</p>
                                    </div>
                                </div>
                                <div class="col-12 col-md-3 mb-3">
                                    <div class="card shadow-sm border p-3 bg-white text-dark rounded-3 h-100" style="border-left: 5px solid #f59e0b !important;">
                                        <span class="text-muted small text-uppercase font-bold">Standar Risiko Tertinggi</span>
                                        <h6 class="mb-0 text-dark font-bold mt-2 text-truncate" title="<?= htmlspecialchars($highest_std_name); ?>"><?= htmlspecialchars($highest_std_name); ?></h6>
                                        <p class="text-warning small mb-0 font-bold">Skor: <?= $highest_std_val; ?></p>
                                    </div>
                                </div>
                                <div class="col-12 col-md-3 mb-3">
                                    <div class="card shadow-sm border p-3 bg-white text-dark rounded-3 h-100" style="border-left: 5px solid #ec4899 !important;">
                                        <span class="text-muted small text-uppercase font-bold">Unit Risiko Tertinggi</span>
                                        <h6 class="mb-0 text-dark font-bold mt-2 text-truncate" title="<?= htmlspecialchars($highest_unit_name); ?>"><?= htmlspecialchars($highest_unit_name); ?></h6>
                                        <p class="text-danger small mb-0 font-bold">Skor: <?= $highest_unit_val; ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Global Risk Chart per LED Standard -->
                        <div class="col-12 mb-4">
                            <div class="card table-card">
                                <div class="card-header card-header-gradient font-bold text-dark d-flex justify-content-between align-items-center">
                                    <span>Profil Risiko Global Berdasarkan Standar LED</span>
                                    <span class="badge bg-primary">Bar Chart</span>
                                </div>
                                <div class="card-body p-3">
                                    <div class="chart-container-custom" style="height: 320px;">
                                        <canvas id="chartRiskStdGlobal"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Academic vs Non-Academic Charts -->
                        <div class="col-lg-6 mb-4">
                            <div class="card table-card">
                                <div class="card-header card-header-gradient font-bold text-dark d-flex justify-content-between align-items-center">
                                    <span>Profil Risiko Unit Akademik</span>
                                    <span class="badge bg-success">Bar Chart</span>
                                </div>
                                <div class="card-body p-3">
                                    <div class="chart-container-custom" style="height: 320px;">
                                        <canvas id="chartRiskAcademic"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-6 mb-4">
                            <div class="card table-card">
                                <div class="card-header card-header-gradient font-bold text-dark d-flex justify-content-between align-items-center">
                                    <span>Profil Risiko Unit Non-Akademik</span>
                                    <span class="badge bg-info text-dark">Bar Chart</span>
                                </div>
                                <div class="card-body p-3">
                                    <div class="chart-container-custom" style="height: 320px;">
                                        <canvas id="chartRiskNonAcademic"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Standard Breakdown Classification Table -->
                        <div class="col-12 mb-4">
                            <div class="card table-card">
                                <div class="card-header card-header-gradient font-bold text-dark">
                                    Klasifikasi & Detail Risiko Berdasarkan Standar LED
                                </div>
                                <div class="card-body p-3">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped table-hover align-middle small">
                                            <thead>
                                                <tr>
                                                    <th>No.</th>
                                                    <th>Nama Standar LED</th>
                                                    <th class="text-center">Item Dinilai</th>
                                                    <th class="text-center">Rata-rata Risiko</th>
                                                    <th class="text-center">Kategori</th>
                                                    <th class="text-center">Risiko Tinggi</th>
                                                    <th class="text-center">Risiko Sangat Tinggi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php 
                                                $no = 1;
                                                foreach ($std_breakdown_rows as $row): 
                                                    $badge = get_risk_badge($row['avg_r']);
                                                    $style = isset($badge['style']) ? $badge['style'] : '';
                                                ?>
                                                <tr>
                                                    <td><?= $row['nostandar']; ?></td>
                                                    <td><strong><?= htmlspecialchars($row['namastandar']); ?></strong></td>
                                                    <td class="text-center"><?= $row['total_dinilai']; ?></td>
                                                    <td class="text-center font-bold text-dark"><?= number_format($row['avg_r'], 2, ',', '.'); ?></td>
                                                    <td class="text-center">
                                                        <span class="badge <?= $badge['class']; ?>" style="<?= $style; ?>"><?= $badge['label']; ?></span>
                                                    </td>
                                                    <td class="text-center <?= $row['tinggi_count'] > 0 ? 'text-danger font-bold' : 'text-muted'; ?>"><?= $row['tinggi_count']; ?></td>
                                                    <td class="text-center <?= $row['sangat_tinggi_count'] > 0 ? 'text-danger font-bold' : 'text-muted'; ?>"><?= $row['sangat_tinggi_count']; ?></td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <script type="text/javascript">
                        document.addEventListener("DOMContentLoaded", function() {
                            const ctxStd = document.getElementById('chartRiskStdGlobal');
                            const ctxAcad = document.getElementById('chartRiskAcademic');
                            const ctxNonAcad = document.getElementById('chartRiskNonAcademic');

                            if (ctxStd) {
                                new Chart(ctxStd, {
                                    type: 'bar',
                                    data: {
                                        labels: <?= json_encode($std_labels); ?>,
                                        datasets: [{
                                            label: 'Rata-rata Skor Risiko',
                                            data: <?= json_encode($std_data); ?>,
                                            backgroundColor: 'rgba(37, 99, 235, 0.75)',
                                            borderColor: 'rgb(37, 99, 235)',
                                            borderWidth: 1,
                                            borderRadius: 4
                                        }]
                                    },
                                    options: {
                                        responsive: true,
                                        maintainAspectRatio: false,
                                        scales: {
                                            y: { beginAtZero: true, max: 25 }
                                        }
                                    }
                                });
                            }

                            if (ctxAcad) {
                                new Chart(ctxAcad, {
                                    type: 'bar',
                                    data: {
                                        labels: <?= json_encode($acad_labels); ?>,
                                        datasets: [{
                                            label: 'Rata-rata Skor Risiko',
                                            data: <?= json_encode($acad_data); ?>,
                                            backgroundColor: 'rgba(16, 185, 129, 0.75)',
                                            borderColor: 'rgb(16, 185, 129)',
                                            borderWidth: 1,
                                            borderRadius: 4
                                        }]
                                    },
                                    options: {
                                        responsive: true,
                                        maintainAspectRatio: false,
                                        scales: {
                                            y: { beginAtZero: true, max: 25 }
                                        }
                                    }
                                });
                            }

                            if (ctxNonAcad) {
                                new Chart(ctxNonAcad, {
                                    type: 'bar',
                                    data: {
                                        labels: <?= json_encode($non_acad_labels); ?>,
                                        datasets: [{
                                            label: 'Rata-rata Skor Risiko',
                                            data: <?= json_encode($non_acad_data); ?>,
                                            backgroundColor: 'rgba(6, 182, 212, 0.75)',
                                            borderColor: 'rgb(6, 182, 212)',
                                            borderWidth: 1,
                                            borderRadius: 4
                                        }]
                                    },
                                    options: {
                                        responsive: true,
                                        maintainAspectRatio: false,
                                        scales: {
                                            y: { beginAtZero: true, max: 25 }
                                        }
                                    }
                                });
                            }
                        });
                    </script>

                <?php elseif ($view === 'audit'): ?>
                    <!-- ========================================== -->
                    <!-- VIEW 5: AUDIT                              -->
                    <!-- ========================================== -->
                    <div class="row">
                        <div class="col-12 mb-4">
                            <div class="row">
                                <div class="col-12 col-md-3 mb-3">
                                    <div class="card shadow-sm border p-3 bg-white text-dark rounded-3 h-100">
                                        <span class="text-muted small text-uppercase font-bold">Total Laporan Catatan</span>
                                        <h3 class="mb-0 text-dark font-black mt-2"><?= $total_notes; ?> <span class="fs-6 font-normal">Item</span></h3>
                                    </div>
                                </div>
                                <div class="col-12 col-md-3 mb-3">
                                    <div class="card shadow-sm border p-3 bg-white text-dark rounded-3 h-100">
                                        <span class="text-muted small text-uppercase font-bold">Kondisi Praktik Baik</span>
                                        <h3 class="mb-0 text-dark font-black mt-2"><?= $total_positives; ?> <span class="fs-6 font-normal">Praktik</span></h3>
                                    </div>
                                </div>
                                <div class="col-12 col-md-3 mb-3">
                                    <div class="card shadow-sm border p-3 bg-white text-dark rounded-3 h-100">
                                        <span class="text-muted small text-uppercase font-bold">Total Temuan Audit</span>
                                        <h3 class="mb-0 text-danger font-black mt-2"><?= $total_findings; ?> <span class="fs-6 font-normal">Temuan</span></h3>
                                    </div>
                                </div>
                                <div class="col-12 col-md-3 mb-3">
                                    <div class="card shadow-sm border p-3 bg-white text-dark rounded-3 h-100">
                                        <span class="text-muted small text-uppercase font-bold">Final Report Uploaded</span>
                                        <h3 class="mb-0 text-success font-black mt-2"><?= $audit_upload_count; ?> / <?= $total_aud_sessions; ?></h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-12 mb-4">
                            <div class="card table-card">
                                <div class="card-header card-header-gradient font-bold text-dark d-flex justify-content-between align-items-center">
                                    <span>Daftar Pelaksanaan Audit AMI</span>
                                    <a href="audit" class="btn btn-sm btn-primary"><i class="fas fa-check2-circle"></i> Kelola Jadwal Audit</a>
                                </div>
                                <div class="card-body px-3 py-3">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped table-hover align-middle">
                                            <thead>
                                                <tr>
                                                    <th>Kode Audit</th>
                                                    <th>Unit Auditi</th>
                                                    <th>Ruang Lingkup</th>
                                                    <th>Catatan</th>
                                                    <th>Kondisi Positif</th>
                                                    <th>Temuan</th>
                                                    <th>Laporan Akhir</th>
                                                    <th>Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($audit_sessions as $aud): 
                                                    $lingkup = (int)$aud['lingkup'] === 1 ? 'Akademik' : 'Non-Akademik';
                                                ?>
                                                <tr>
                                                    <td><code><?= htmlspecialchars($aud['kodeaudit']); ?></code></td>
                                                    <td><strong><?= htmlspecialchars($aud['nama_unit']); ?></strong></td>
                                                    <td><span class="badge bg-light text-dark border"><?= $lingkup; ?></span></td>
                                                    <td><?= $aud['jml_catatan']; ?> item</td>
                                                    <td><span class="text-success"><?= $aud['jml_positif']; ?></span></td>
                                                    <td><span class="text-danger font-bold"><?= $aud['jml_temuan']; ?></span></td>
                                                    <td>
                                                        <?php if(!empty($aud['berkas'])): ?>
                                                            <a href="assets/laporan/<?= $aud['berkas'];?>" class="btn btn-xs btn-primary" target="_blank"><i class="fas fa-download"></i> Download</a>
                                                        <?php else: ?>
                                                            <span class="text-muted small">Belum diupload</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <div class="btn-group">
                                                            <a href="audit.progres?ka=<?= urlencode($aud['kodeaudit']); ?>&et=<?= md5($aud['token']); ?>" class="btn btn-sm btn-info text-white" title="Detail Progress"><i class="fas fa-search"></i> Detail</a>
                                                            <a href="audit.laporan?ka=<?= urlencode($aud['kodeaudit']); ?>" class="btn btn-sm btn-secondary" title="Buka Laporan"><i class="fas fa-file-alt"></i> Laporan</a>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

            </div>
        </section>
    </div>
</div>

<?php include 'foot.php'; ?>

<!-- Include Select2 JS and custom scripts after foot.php to prevent jQuery reload conflicts -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        if (typeof $.fn.select2 !== 'undefined') {
            $('.js-example-basic-single').select2();
        }
    });
</script>

<!-- CHART.JS INTEGRATION -->
<script>
(function() {
    function initCharts() {
        if (typeof Chart === 'undefined') return;

        // 1. Overall Modules Chart (Only in ringkasan view)
        const ctxOverview = document.getElementById('chartOverviewModules');
        if (ctxOverview) {
            new Chart(ctxOverview, {
                type: 'bar',
                data: {
                    labels: ['SARMUT Average', 'ISO Checklist Average', 'Pra Audit LED Answered', 'Audit Reports Uploaded'],
                    datasets: [{
                        label: 'Progres (%)',
                        data: [
                            <?= json_encode((float)$sarmut_avg); ?>, 
                            <?= json_encode((float)$iso_avg); ?>, 
                            <?= json_encode((float)$led_avg); ?>, 
                            <?= json_encode((float)$audit_upload_pct); ?>
                        ],
                        backgroundColor: [
                            'rgba(99, 102, 241, 0.75)',
                            'rgba(6, 182, 212, 0.75)',
                            'rgba(20, 184, 166, 0.75)',
                            'rgba(249, 115, 22, 0.75)'
                        ],
                        borderColor: [
                            'rgb(99, 102, 241)',
                            'rgb(6, 182, 212)',
                            'rgb(20, 184, 166)',
                            'rgb(249, 115, 22)'
                        ],
                        borderWidth: 1,
                        borderRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 100,
                            ticks: {
                                callback: function(value) { return value + '%'; }
                            }
                        }
                    },
                    plugins: {
                        legend: { display: false }
                    }
                }
            });
        }

        // 2. ISO Distribution Chart (Only in ISO view)
        const ctxIso = document.getElementById('chartIsoDist');
        if (ctxIso) {
            new Chart(ctxIso, {
                type: 'doughnut',
                data: {
                    labels: <?= json_encode($iso_dist_labels); ?>,
                    datasets: [{
                        data: <?= json_encode($iso_dist_data); ?>,
                        backgroundColor: [
                            '#10b981', '#ef4444', '#f59e0b', '#3b82f6', '#64748b'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'right' }
                    }
                }
            });
        }

        // 3. Pra Audit Risk Levels Chart (Only in praaudit view)
        const ctxRisk = document.getElementById('chartRiskLevels');
        if (ctxRisk) {
            new Chart(ctxRisk, {
                type: 'doughnut',
                data: {
                    labels: <?= json_encode(array_keys($riskSummary)); ?>,
                    datasets: [{
                        data: <?= json_encode(array_values($riskSummary)); ?>,
                        backgroundColor: [
                            '#00ff00', '#44c7f4', '#ffff00', '#ff6600', '#ff0000', '#cbd5e1'
                        ]
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

        // 4. SARMUT per Sasaran (Kategori) Chart (on submit, in sarmut view)
        <?php if (isset($_POST['submit_sarmut_graph']) && !empty($sasaran_labels)): ?>
        const ctxSarmutSasaran = document.getElementById('grafikSarmutSasaran');
        if (ctxSarmutSasaran) {
            new Chart(ctxSarmutSasaran, {
                type: 'bar',
                data: {
                    labels: <?= json_encode($sasaran_labels); ?>,
                    datasets: [{
                        label: 'Penyelesaian per Sasaran (%)',
                        backgroundColor: 'rgba(99, 102, 241, 0.8)',
                        borderColor: 'rgb(99, 102, 241)',
                        borderWidth: 1,
                        data: <?= json_encode($sasaran_pcts); ?>
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    scales: {
                        x: { beginAtZero: true, max: 100 }
                    }
                }
            });
        }
        <?php endif; ?>

        // 5. ISO per Klausul (Standar/Kategori) Chart (on submit, in ISO view)
        <?php if (isset($_POST['submit_iso_graph']) && !empty($klausul_labels)): ?>
        const ctxIsoKlausul = document.getElementById('grafikIsoKlausul');
        if (ctxIsoKlausul) {
            new Chart(ctxIsoKlausul, {
                type: 'bar',
                data: {
                    labels: <?= json_encode($klausul_labels); ?>,
                    datasets: [{
                        label: 'Penyelesaian per Klausul (%)',
                        backgroundColor: 'rgba(6, 182, 212, 0.8)',
                        borderColor: 'rgb(6, 182, 212)',
                        borderWidth: 1,
                        data: <?= json_encode($klausul_pcts); ?>
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    scales: {
                        x: { beginAtZero: true, max: 100 }
                    }
                }
            });
        }
        <?php endif; ?>
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initCharts);
    } else {
        initCharts();
    }
})();
</script>