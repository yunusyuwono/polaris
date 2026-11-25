<?php
include 'koneksi.php';

// Auto-create all required tables if they do not exist
mysqli_query($kon, "CREATE TABLE IF NOT EXISTS `audit_laporan_narasi` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `kodeaudit` VARCHAR(50) NOT NULL,
    `subbab` VARCHAR(100) NOT NULL,
    `konten` TEXT NOT NULL,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY (`kodeaudit`, `subbab`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

mysqli_query($kon, "CREATE TABLE IF NOT EXISTS `audit_laporan_ttd` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `kodeaudit` VARCHAR(50) NOT NULL,
    `pihak` VARCHAR(30) NOT NULL,
    `nama` VARCHAR(150) DEFAULT NULL,
    `tanda_tangan` MEDIUMTEXT NOT NULL,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `kodeaudit_pihak` (`kodeaudit`, `pihak`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

mysqli_query($kon, "CREATE TABLE IF NOT EXISTS `audit_lk2_kesimpulan` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `kodeaudit` VARCHAR(50) NOT NULL,
    `tipe` VARCHAR(10) NOT NULL,
    `kesimpulan` TEXT NOT NULL,
    `entri` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uk_ka_tipe` (`kodeaudit`, `tipe`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

mysqli_query($kon, "CREATE TABLE IF NOT EXISTS `audit_lk2_temuan` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `kodeaudit` VARCHAR(50) NOT NULL,
    `no_risiko` VARCHAR(20) NOT NULL,
    `tipe` VARCHAR(10) NOT NULL,
    `tipe_temuan` VARCHAR(50) DEFAULT NULL,
    `plor_p` TEXT,
    `plor_l` TEXT,
    `plor_o` TEXT,
    `plor_r` TEXT,
    `akar_penyebab` TEXT,
    `risiko_potensial` TEXT,
    `pengendalian_efektivitas` TEXT,
    `rekomendasi_ptk` TEXT,
    `rekomendasi_rtm` TEXT,
    `entri` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uk_ka_risiko` (`kodeaudit`, `no_risiko`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

mysqli_query($kon, "CREATE TABLE IF NOT EXISTS `audit_triangulasi_detail` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `kodeaudit` VARCHAR(50) NOT NULL,
    `no_risiko` VARCHAR(20) NOT NULL,
    `auditor_1_id` VARCHAR(50) DEFAULT NULL,
    `auditor_1_name` VARCHAR(100) DEFAULT NULL,
    `auditor_1_text` TEXT,
    `auditor_2_id` VARCHAR(50) DEFAULT NULL,
    `auditor_2_name` VARCHAR(100) DEFAULT NULL,
    `auditor_2_text` TEXT,
    `auditee_id` VARCHAR(50) DEFAULT NULL,
    `auditee_name` VARCHAR(100) DEFAULT NULL,
    `auditee_text` TEXT,
    `kesepakatan_akhir` VARCHAR(50) DEFAULT NULL,
    `ringkasan_alasan` TEXT,
    `entri` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uk_tri_ka_risiko` (`kodeaudit`, `no_risiko`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

mysqli_query($kon, "CREATE TABLE IF NOT EXISTS `audit_ptk` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `kodeaudit` VARCHAR(50) NOT NULL,
    `no_risiko` VARCHAR(20) NOT NULL,
    `status` VARCHAR(20) DEFAULT 'Draft',
    `entri` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uk_ptk_ka_risiko` (`kodeaudit`, `no_risiko`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

mysqli_query($kon, "CREATE TABLE IF NOT EXISTS `audit_ptk_tindakan` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `ptk_id` INT NOT NULL,
    `no_urut` INT NOT NULL DEFAULT 1,
    `uraian` TEXT,
    `pic` VARCHAR(100) DEFAULT NULL,
    `target_selesai` DATE DEFAULT NULL,
    `status` VARCHAR(50) DEFAULT 'Open',
    `realisasi` TEXT,
    `tgl_verifikasi` DATE DEFAULT NULL,
    `status_verifikasi` VARCHAR(50) DEFAULT NULL,
    `entri` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$is_auditor_access = true;
$allowed_signature_role = 'auditor';

$ka = isset($_GET['ka']) ? mysqli_real_escape_string($kon, (string)$_GET['ka']) : (isset($_POST['ka']) ? mysqli_real_escape_string($kon, (string)$_POST['ka']) : '');
$et = htmlspecialchars($_GET['et'] ?? ($_POST['et'] ?? ''));

// Handle AJAX Save Narasi
if (isset($_POST['action']) && $_POST['action'] === 'save_narasi') {
    while (ob_get_level()) ob_end_clean();
    header('Content-Type: application/json');
    if (!$is_auditor_access) {
        echo json_encode(['status' => 'error', 'message' => 'Akses ditolak. Unit tidak diizinkan mengubah narasi.']);
        exit;
    }
    $kodeaudit = mysqli_real_escape_string($kon, $_POST['kodeaudit'] ?? '');
    $subbab = mysqli_real_escape_string($kon, $_POST['subbab'] ?? '');
    $konten = $_POST['konten'] ?? '';
    
    if (empty($kodeaudit) || empty($subbab)) {
        echo json_encode(['status' => 'error', 'message' => 'Parameter tidak lengkap']);
        exit;
    }

    $stmt = $kon->prepare("INSERT INTO audit_laporan_narasi (kodeaudit, subbab, konten) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE konten = VALUES(konten)");
    $stmt->bind_param("sss", $kodeaudit, $subbab, $konten);
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => $stmt->error]);
    }
    exit;
}

// Handle AJAX/POST Save Signature
if (isset($_POST['action']) && $_POST['action'] === 'save_signature') {
    $pihak = mysqli_real_escape_string($kon, $_POST['pihak'] ?? '');
    $nama = mysqli_real_escape_string($kon, $_POST['nama'] ?? '');
    $signature = $_POST['signature'] ?? '';
    $allowedPihak = ['auditi', 'auditor'];

    // Enforce signature role permissions
    $sig_allowed = false;
    if ($allowed_signature_role === 'both') {
        $sig_allowed = true;
    } elseif ($allowed_signature_role === 'auditor' && $pihak === 'auditor') {
        $sig_allowed = true;
    } elseif ($allowed_signature_role === 'auditi' && $pihak === 'auditi') {
        $sig_allowed = true;
    }

    if (!$sig_allowed) {
        $msg = 'Akses ditolak. Anda tidak memiliki wewenang untuk menandatangani sebagai ' . ($pihak === 'auditor' ? 'Auditor' : 'Pimpinan Auditi') . '.';
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            while (ob_get_level()) ob_end_clean();
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => $msg]);
            exit;
        }
        echo "<script>alert('" . addslashes($msg) . "');</script>";
        exit;
    }

    if ($ka !== '' && in_array($pihak, $allowedPihak, true) && strpos($signature, 'data:image/') === 0) {
        $signature = mysqli_real_escape_string($kon, $signature);
        mysqli_query($kon, "CREATE TABLE IF NOT EXISTS audit_laporan_ttd (
            id INT AUTO_INCREMENT PRIMARY KEY,
            kodeaudit VARCHAR(50) NOT NULL,
            pihak VARCHAR(30) NOT NULL,
            nama VARCHAR(150) DEFAULT NULL,
            tanda_tangan MEDIUMTEXT NOT NULL,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY kodeaudit_pihak (kodeaudit, pihak)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        mysqli_query($kon, "
            INSERT INTO audit_laporan_ttd (kodeaudit, pihak, nama, tanda_tangan)
            VALUES ('$ka', '$pihak', '$nama', '$signature')
            ON DUPLICATE KEY UPDATE nama=VALUES(nama), tanda_tangan=VALUES(tanda_tangan)
        ");
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            while (ob_get_level()) ob_end_clean();
            header('Content-Type: application/json');
            echo json_encode(['status' => 'success', 'message' => 'Tanda tangan berhasil disimpan', 'signature' => $_POST['signature'], 'nama' => $_POST['nama']]);
            exit;
        }
        $redirect = 'audit.laporan?ka='.urlencode($ka).'&et='.urlencode($et).'&v='.urlencode($_GET['v'] ?? '1').(isset($_GET['doc']) ? '&doc=1' : '');
        echo "<script>alert('Tanda tangan berhasil disimpan'); window.location='".htmlspecialchars($redirect, ENT_QUOTES)."';</script>";
        exit;
    }

    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        while (ob_get_level()) ob_end_clean();
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Tanda tangan belum valid. Silakan gambar atau unggah tanda tangan.']);
        exit;
    }
    echo "<script>alert('Tanda tangan belum valid. Silakan gambar atau unggah tanda tangan.');</script>";
    exit;
}

include 'nav.php';
include '../audit.ui.php';
include "audit.validasi.php";

function audit_laporan_text($value) {
    $value = trim((string)$value);
    return $value === '' ? '<span class="text-muted">-</span>' : nl2br(htmlspecialchars($value));
}

function audit_laporan_date($value) {
    return audit_ui_date($value);
}

$qAudit = mysqli_query($kon, "
    SELECT audit.*, unit.nama AS auditi, unit.kepala AS kaunit
    FROM audit
    LEFT JOIN unit ON audit.unit=unit.id
    WHERE audit.kodeaudit='$ka'
");
$audit = ($qAudit && mysqli_num_rows($qAudit) > 0) ? mysqli_fetch_array($qAudit) : null;

if(!$audit) {
    ?>
    <?php
} else {
    $is_akademik = ($audit['lingkup'] === '1');
    $label_prodi = $is_akademik ? 'Program Studi ' : '';
    $label_prodi_upper = $is_akademik ? 'PROGRAM STUDI ' : '';
    $label_prodi_lower = $is_akademik ? 'program studi' : 'unit';
    $label_kajian = $is_akademik ? 'prodi' : 'unit';
    $label_kaprodi = $is_akademik ? 'Ketua Program Studi' : 'Kepala Unit';
}

if(!$audit) {
    ?>
    <div id="main">
        <header><a href="#" class="burger-btn d-block p-2"><i class="fas fa-bars"></i> Menu</a></header>
        <div class="page-content">
            <div class="alert alert-danger">Data audit tidak ditemukan.</div>
        </div>
    </div>
    <?php include 'foot.php'; exit;
}

// Ensure narrative table exists and load saved narratives
mysqli_query($kon, "CREATE TABLE IF NOT EXISTS audit_laporan_narasi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kodeaudit VARCHAR(50) NOT NULL,
    subbab VARCHAR(100) NOT NULL,
    konten TEXT NOT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY (kodeaudit, subbab)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$narasi_saved = [];
$q_narasi = mysqli_query($kon, "SELECT subbab, konten FROM audit_laporan_narasi WHERE kodeaudit='$ka' OR kodeaudit='DEFAULT' ORDER BY FIELD(kodeaudit, 'DEFAULT', '$ka')");
if ($q_narasi) {
    while($row_n = mysqli_fetch_assoc($q_narasi)) {
        $narasi_saved[$row_n['subbab']] = $row_n['konten'];
    }
}

function get_narasi($subbab, $default_html) {
    global $narasi_saved;
    return (isset($narasi_saved[$subbab]) && trim($narasi_saved[$subbab]) !== '') ? $narasi_saved[$subbab] : $default_html;
}

$signatureRows = [];
$signatureQuery = mysqli_query($kon, "SELECT pihak, nama, tanda_tangan FROM audit_laporan_ttd WHERE kodeaudit='$ka'");
while($signatureQuery && $row = mysqli_fetch_assoc($signatureQuery)) {
    $signatureRows[$row['pihak']] = $row;
}

function audit_signature_image($pihak) {
    global $signatureRows;
    if(empty($signatureRows[$pihak]['tanda_tangan'])) {
        return '<div class="report-sign-space" data-pihak-sig-img="'.htmlspecialchars($pihak).'"></div>';
    }
    return '<img class="report-sign-image" src="'.htmlspecialchars($signatureRows[$pihak]['tanda_tangan']).'" alt="Tanda tangan" data-pihak-sig-img="'.htmlspecialchars($pihak).'">';
}

function audit_signature_name($pihak, $fallback) {
    global $signatureRows;
    $val = htmlspecialchars($signatureRows[$pihak]['nama'] ?? $fallback ?: '-');
    return '<span data-pihak-sig-name="'.htmlspecialchars($pihak).'">'.$val.'</span>';
}

function audit_signature_tools($ka, $et, $audit, $ketuaaud, $versi) {
    global $signatureRows, $allowed_signature_role, $label_kaprodi;
    $forms = [];
    if ($allowed_signature_role === 'both' || $allowed_signature_role === 'auditi') {
        $forms[] = ['pihak' => 'auditi', 'label' => $label_kaprodi, 'nama' => $audit['kaunit'] ?? ''];
    }
    if ($allowed_signature_role === 'both' || $allowed_signature_role === 'auditor') {
        $forms[] = ['pihak' => 'auditor', 'label' => 'Ketua Tim Auditor AMI', 'nama' => $ketuaaud ?? ''];
    }
    if (empty($forms)) {
        return '';
    }
    ob_start();
    ?>
    <div class="card no-print signature-tools-card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h6 class="mb-0 fw-bold">Tanda Tangan Dokumen</h6>
                <small class="text-muted">Gambar langsung atau unggah gambar tanda tangan untuk dicetak pada laporan.</small>
            </div>
            <button class="btn btn-outline-primary btn-sm" data-bs-toggle="collapse" data-bs-target="#signatureToolsBody" type="button" aria-expanded="true">
                <i class="fas fa-pen-nib me-1"></i> Kelola
            </button>
        </div>
        <div class="card-body collapse show" id="signatureToolsBody">
            <div class="row g-3">
                <?php foreach($forms as $form):
                    $savedSignature = $signatureRows[$form['pihak']]['tanda_tangan'] ?? '';
                    $savedName = $signatureRows[$form['pihak']]['nama'] ?? $form['nama'];
                ?>
                    <div class="col-12 col-lg-6">
                        <form method="post" class="signature-form signature-pad-wrap">
                            <input type="hidden" name="action" value="save_signature">
                            <input type="hidden" name="ka" value="<?= htmlspecialchars($ka); ?>">
                            <input type="hidden" name="et" value="<?= htmlspecialchars($et); ?>">
                            <input type="hidden" name="v" value="<?= htmlspecialchars($versi); ?>">
                            <input type="hidden" name="pihak" value="<?= htmlspecialchars($form['pihak']); ?>">
                            <input type="hidden" name="signature" class="signature-data">
                            <h6 class="fw-bold mb-2"><?= htmlspecialchars($form['label']); ?></h6>
                            <label class="form-label small text-muted mb-1">Nama penanda tangan</label>
                            <input type="text" name="nama" class="form-control form-control-sm mb-2" value="<?= htmlspecialchars($savedName); ?>">
                            <div class="signature-preview">
                                <?php if($savedSignature): ?>
                                    <img src="<?= htmlspecialchars($savedSignature); ?>" alt="Tanda tangan tersimpan">
                                <?php else: ?>
                                    <span class="text-muted small">Belum ada tanda tangan tersimpan</span>
                                <?php endif; ?>
                            </div>
                            <canvas class="signature-pad"></canvas>
                            <div class="d-flex flex-wrap gap-2 mt-2">
                                <button type="button" class="btn btn-outline-secondary btn-sm signature-clear"><i class="fas fa-eraser me-1"></i> Bersihkan</button>
                                <label class="btn btn-outline-primary btn-sm mb-0">
                                    <i class="fas fa-upload me-1"></i> Upload
                                    <input type="file" class="signature-upload d-none" accept="image/*">
                                </label>
                                <button type="submit" class="btn btn-success btn-sm"><i class="fas fa-save me-1"></i> Simpan</button>
                            </div>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

$gauditor = mysqli_fetch_array(mysqli_query($kon, "SELECT auditor.*, user.nama FROM auditor LEFT JOIN user ON auditor.user=user.id WHERE auditor.kodeaudit='$ka' AND auditor.status='Ketua' LIMIT 1"));
$ketuaaud = $gauditor['nama'] ?? '';

$catatanRows = [];
$catatan = mysqli_query($kon, "SELECT * FROM audit_catatan WHERE kodeaudit='$ka' ORDER BY kodecatatan ASC");
while($catatan && $row = mysqli_fetch_assoc($catatan)) {
    $catatanRows[] = $row;
}

$positifRows = [];
$positif = mysqli_query($kon, "SELECT * FROM audit_kondisi WHERE kodeaudit='$ka' AND jeniskondisi='Positif' ORDER BY kodekondisi ASC");
while($positif && $row = mysqli_fetch_assoc($positif)) {
    $positifRows[] = $row;
}

$temuanRows = [];
$temuan = mysqli_query($kon, "SELECT * FROM audit_kondisi WHERE kodeaudit='$ka' AND jeniskondisi='Temuan' ORDER BY kodekondisi ASC");
while($temuan && $row = mysqli_fetch_assoc($temuan)) {
    $temuanRows[] = $row;
}

$lingkup = (int)$audit['lingkup'] === 1 ? 'Akademik' : 'Non-Akademik';

// Fetch all auditors for Tim Auditor list
$auds_query = mysqli_query($kon, "SELECT auditor.*, user.nama FROM auditor LEFT JOIN user ON auditor.user=user.id WHERE auditor.kodeaudit='$ka' ORDER BY auditor.status DESC");
$auditors_list = [];
while($auds_query && $row = mysqli_fetch_assoc($auds_query)) {
    $auditors_list[] = [
        'name' => $row['nama'] ?: 'Auditor',
        'status' => $row['status']
    ];
}

$versi = $_GET['v'] ?? '1';
$gk_dok = mysqli_fetch_array(mysqli_query($kon, "SELECT * FROM audit_dok WHERE kodeaudit='$ka'"));

// Auto-sync praaudit to audit_persiapan_risiko
$selectedPraId = 0;
$qDefaultPra = mysqli_query($kon, "
    SELECT pr.id 
    FROM praaudit pr 
    JOIN audit a ON pr.unit = a.unit AND pr.tahun = YEAR(a.tglmulai)
    WHERE a.kodeaudit = '$ka'
    LIMIT 1
");
if ($qDefaultPra && $rPra = mysqli_fetch_assoc($qDefaultPra)) {
    $selectedPraId = (int)$rPra['id'];
}

if($selectedPraId > 0) {
    $existingRisks = [];
    $qEx = mysqli_query($kon, "SELECT id, no_risiko, pernyataan_risiko, dampak, probabilitas, skor, level, pengendalian_diklaim FROM audit_persiapan_risiko WHERE kodeaudit='$ka'");
    if ($qEx) {
        while($ex = @mysqli_fetch_assoc($qEx)) {
            $cleanPrefix = trim(preg_replace('/\(Risiko:\s*.*?\)\s*$/is', '', $ex['pernyataan_risiko']));
            $ex['clean_prefix'] = $cleanPrefix;
            $existingRisks[] = $ex;
        }
    }
    
    $qMaxIdx = mysqli_query($kon, "SELECT COUNT(*) as cnt FROM audit_persiapan_risiko WHERE kodeaudit='$ka'");
    $maxRow = @mysqli_fetch_assoc($qMaxIdx);
    $syncIdx = (int)($maxRow['cnt'] ?? 0) + 1;
    
    $qSync = mysqli_query($kon, "
        SELECT p.id as idpertanyaan, p.pertanyaan, k.namastandar, l.isi_risiko, l.dampak, l.probabilitas, l.risiko, l.usulan_mitigasi
        FROM pertanyaan p
        JOIN kriteria k ON p.kriteria = k.id
        LEFT JOIN (
            SELECT l1.* 
            FROM led l1
            JOIN (
                SELECT MAX(id) as max_id 
                FROM led 
                WHERE idpraaudit = '$selectedPraId'
                GROUP BY idpertanyaan
            ) l2 ON l1.id = l2.max_id
        ) l ON l.idpertanyaan = p.id
        WHERE p.idpaket = (SELECT paket FROM praaudit WHERE id = '$selectedPraId')
        ORDER BY p.noindikator ASC
    ");
    if($qSync) {
        while($r = mysqli_fetch_assoc($qSync)) {
            $isiRisiko = !empty($r['isi_risiko']) ? $r['isi_risiko'] : 'Belum diisi';
            $pernyataan = mysqli_real_escape_string($kon, $r['namastandar'].' - '. $r['pertanyaan'] . ' (Risiko: '.$isiRisiko.')');
            $pengendalian = mysqli_real_escape_string($kon, $r['usulan_mitigasi'] ?? '');
            
            $hasData = !empty($r['isi_risiko']) || !empty($r['usulan_mitigasi']);
            if($hasData) {
                $dampak = !empty($r['dampak']) ? (int)$r['dampak'] : 0;
                $probabilitas = !empty($r['probabilitas']) ? (int)$r['probabilitas'] : 0;
                $skor = ($dampak > 0 && $probabilitas > 0) ? (!empty($r['risiko']) ? (int)$r['risiko'] : ($dampak * $probabilitas)) : 0;
            } else {
                $dampak = 0;
                $probabilitas = 0;
                $skor = 0;
            }
            
            if($skor >= 10) $level = 'MERAH';
            elseif($skor >= 5) $level = 'KUNING';
            elseif($skor > 0) $level = 'HIJAU';
            else $level = '';
            
            $targetPrefix = trim($r['namastandar'].' - '. $r['pertanyaan']);
            $matchedExisting = null;
            foreach($existingRisks as $ex) {
                if($ex['clean_prefix'] === $targetPrefix) {
                    $matchedExisting = $ex;
                    break;
                }
            }
            if(!$matchedExisting) {
                foreach($existingRisks as $ex) {
                    if(strpos($ex['clean_prefix'], $targetPrefix) === 0 || strpos($targetPrefix, $ex['clean_prefix']) === 0) {
                        $matchedExisting = $ex;
                        break;
                    }
                }
            }
            
            if($matchedExisting) {
                $eId = (int)$matchedExisting['id'];
                mysqli_query($kon, "
                    UPDATE audit_persiapan_risiko SET 
                        pernyataan_risiko = '$pernyataan',
                        dampak = '$dampak',
                        probabilitas = '$probabilitas',
                        skor = '$skor',
                        level = '$level',
                        pengendalian_diklaim = '$pengendalian'
                    WHERE id = '$eId'
                ");
            } else {
                $noRisiko = 'R-'.$syncIdx;
                $indikator = mysqli_real_escape_string($kon, 'Target/Indikator');
                mysqli_query($kon, "
                    INSERT INTO audit_persiapan_risiko 
                    (kodeaudit, no_risiko, pernyataan_risiko, indikator_target, dampak, probabilitas, skor, level, pengendalian_diklaim) 
                    VALUES 
                    ('$ka', '$noRisiko', '$pernyataan', '$indikator', '$dampak', '$probabilitas', '$skor', '$level', '$pengendalian')
                ");
                $syncIdx++;
            }
        }
    }
}

// Data untuk Versi 2 (Manajemen Risiko: Tahap 5 - 8)
$qPersiapan = mysqli_query($kon, "SELECT * FROM audit_persiapan WHERE kodeaudit='$ka'");
$persiapanData = $qPersiapan ? mysqli_fetch_array($qPersiapan) : null;
$persiapanRisikoRows = [];
$qPR = mysqli_query($kon, "SELECT * FROM audit_persiapan_risiko WHERE kodeaudit='$ka' ORDER BY CAST(REPLACE(no_risiko,'R-','') AS UNSIGNED) ASC");
while($qPR && $row = mysqli_fetch_assoc($qPR)) { $persiapanRisikoRows[] = $row; }

$lk2Kesimpulan = mysqli_fetch_array(mysqli_query($kon, "SELECT * FROM audit_lk2_kesimpulan WHERE kodeaudit='$ka'"));

// Fetch maps for fast lookup
$lk2Map = [];
$qLK2 = mysqli_query($kon, "SELECT * FROM audit_lk2_temuan WHERE kodeaudit='$ka'");
if($qLK2) while($row = mysqli_fetch_assoc($qLK2)) { $lk2Map[$row['no_risiko']] = $row; }

$triMap = [];
$qTri = mysqli_query($kon, "SELECT * FROM audit_triangulasi_detail WHERE kodeaudit='$ka'");
if($qTri) while($row = mysqli_fetch_assoc($qTri)) { $triMap[$row['no_risiko']] = $row; }

$ptkMap = [];
$qPtk = mysqli_query($kon, "SELECT * FROM audit_ptk WHERE kodeaudit='$ka'");
if($qPtk) while($row = mysqli_fetch_assoc($qPtk)) { $ptkMap[$row['no_risiko']] = $row; }

$tindakanMap = [];
$checkTind = mysqli_query($kon,"SHOW TABLES LIKE 'audit_ptk_tindakan'");
if(mysqli_num_rows($checkTind)>0){
    $qTd = mysqli_query($kon,"
        SELECT t.* FROM audit_ptk_tindakan t
        JOIN audit_ptk p ON t.ptk_id=p.id
        WHERE p.kodeaudit='$ka'
        ORDER BY t.ptk_id, t.no_urut ASC
    ");
    if($qTd) while($td=mysqli_fetch_assoc($qTd)) $tindakanMap[$td['ptk_id']][]=$td;
}

// ----------------------------------------------------
// BRANCH: COMPLETE PRINTABLE DOCUMENT VIEW
// ----------------------------------------------------
if(isset($_GET['doc'])):
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan AMI - <?= htmlspecialchars($audit['auditi']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
</head>
<body style="background-color: #f1f5f9;">
<div id="main">
    <header class="no-print">
        <a href="#" class="burger-btn d-block p-2"><i class="fas fa-bars"></i> Menu</a>
    </header>
    <div class="page-content" style="padding: 1rem;">
        <!-- Tab navigation bar -->
        <div class="no-print mb-4" style="max-width: 21cm; margin: 0 auto;">
            <div class="card shadow-sm border p-3" style="background: white; border-radius: 8px;">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                    <div>
                        <h5 class="mb-1 font-bold text-primary" style="margin: 0; font-weight: bold; color: #2563eb;">Laporan Hasil Audit Mutu Internal (AMI) <?= $versi === '2' ? '- Berbasis Manajemen Risiko' : '- Standar'; ?></h5>
                        <p class="text-muted mb-0 small" style="margin: 0; color: #64748b; font-size: 0.85rem;">Format dokumen resmi cetak A4. Aktifkan Mode Edit di bawah untuk mengubah narasi subbab (WYSIWYG + Autosave).</p>
                    </div>
                    <div class="d-flex flex-wrap gap-2 align-items-center">
                        <?php if ($is_auditor_access): ?>
                        <div class="form-check form-switch m-0 d-flex align-items-center px-3 py-1 bg-light border rounded" style="cursor: pointer;">
                            <input class="form-check-input ms-0 me-2" type="checkbox" id="toggleEditMode" style="cursor: pointer; width: 2.2em; height: 1.1em;">
                            <label class="form-check-label text-dark mb-0" for="toggleEditMode" style="cursor: pointer; font-size: 0.85rem; font-weight: bold;">
                                <i class="fas fa-edit text-warning me-1"></i> Mode Edit Subbab
                            </label>
                        </div>
                        <span id="autosaveIndicator" class="badge bg-success text-white px-2 py-2" style="display: none; font-size: 0.75rem;">
                            <i class="fas fa-check-circle me-1"></i> Tersimpan
                        </span>
                        <?php endif; ?>
                        <a href="audit.laporan?ka=<?= urlencode($ka); ?>&et=<?= $et; ?>&v=<?= $versi; ?>" class="btn btn-outline-primary btn-sm font-semibold" style="border: 1px solid #2563eb; color: #2563eb; background: transparent; padding: 6px 12px; border-radius: 4px; text-decoration: none; font-size: 0.85rem;">
                            <i class="fas fa-chart-bar me-1"></i> Tampilan Web
                        </a>
                        <button onclick="printReport()" class="btn btn-primary btn-sm font-semibold" style="background: #2563eb; border: 1px solid #2563eb; color: white; padding: 6px 12px; border-radius: 4px; font-size: 0.85rem; cursor: pointer;">
                            <i class="fas fa-print me-1"></i> Cetak / Save PDF
                        </button>
                        <button onclick="exportToWord('Laporan_AMI_V<?= $versi; ?>_<?= htmlspecialchars(str_replace(' ', '_', $audit['auditi'])); ?>')" class="btn btn-success btn-sm font-semibold text-white" style="background: #10b981; border: 1px solid #10b981; padding: 6px 12px; border-radius: 4px; font-size: 0.85rem; cursor: pointer;">
                            <i class="fas fa-file-word me-1"></i> Export Word (.doc)
                        </button>
                        <a class="btn btn-outline-secondary btn-sm" href="audit.progres?ka=<?= urlencode($ka); ?>&et=<?= $et; ?>" style="border: 1px solid #64748b; color: #64748b; background: transparent; padding: 6px 12px; border-radius: 4px; text-decoration: none; font-size: 0.85rem; margin-left: 5px;">
                            <i class="fas fa-arrow-left me-1"></i> Kembali
                        </a>
                    </div>
                </div>
                <div class="mt-3 pt-3 border-top d-flex flex-wrap align-items-center justify-content-between gap-2">
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <span class="text-muted small fw-bold me-1"><i class="fas fa-layer-group text-primary me-1"></i> Versi Laporan:</span>
                        <a href="audit.laporan?ka=<?= urlencode($ka); ?>&et=<?= $et; ?>&doc=1&v=1" class="btn btn-sm <?= $versi === '1' ? 'btn-primary text-white fw-bold shadow-sm' : 'btn-light border text-dark'; ?>" style="border-radius: 6px; font-size: 0.85rem;">
                            <i class="fas fa-file-alt me-1"></i> Versi 1: Laporan AMI Standar (Tahap 1-4)
                        </a>
                        <a href="audit.laporan?ka=<?= urlencode($ka); ?>&et=<?= $et; ?>&doc=1&v=2" class="btn btn-sm <?= $versi === '2' ? 'btn-primary text-white fw-bold shadow-sm' : 'btn-light border text-dark'; ?>" style="border-radius: 6px; font-size: 0.85rem;">
                            <i class="fas fa-shield-alt me-1"></i> Versi 2: Berbasis Manajemen Risiko (Tahap 5-8 + Dok)
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- simulator page layout -->
        <style>
            .doc-container {
                display: flex;
                flex-direction: column;
                gap: 20px;
                background-color: #f1f5f9;
                padding: 20px 0;
            }
            .document-page {
                background: white;
                width: 21cm;
                min-height: 29.7cm;
                padding: 2cm 2.5cm 2.5cm 2.5cm;
                margin: 0 auto;
                box-shadow: 0 4px 20px rgba(0,0,0,0.08);
                box-sizing: border-box;
                position: relative;
                font-family: "Times New Roman", Times, serif;
                font-size: 12pt;
                line-height: 1.5;
                color: #000;
            }
            .doc-title-large {
                font-size: 16pt;
                font-weight: bold;
                line-height: 1.4;
            }
            .doc-title-medium {
                font-size: 14pt;
                font-weight: bold;
            }
            .doc-heading-1 {
                font-size: 14pt;
                font-weight: bold;
                text-align: center;
                margin-top: 0.2cm;
                margin-bottom: 0.8cm;
                text-transform: uppercase;
            }
            .doc-heading-2 {
                font-size: 12pt;
                font-weight: bold;
                margin-top: 0.6cm;
                margin-bottom: 0.3cm;
            }
            .doc-paragraph {
                text-align: justify;
                text-indent: 1.25cm;
                margin-bottom: 0.4cm;
            }
            .doc-list {
                text-align: justify;
                margin-bottom: 0.4cm;
                padding-left: 0.75cm;
            }
            .table-doc {
                width: 100%;
                border-collapse: collapse;
                margin: 0.4cm 0;
            }
            .table-doc th, .table-doc td {
                border: 1px solid #000;
                padding: 6px 8px;
                font-size: 11pt;
                text-align: left;
                vertical-align: top;
            }
            .table-doc th {
                background-color: #f2f2f2 !important;
                font-weight: bold;
                text-align: center;
            }
            .page-number-footer {
                position: absolute;
                bottom: 1.5cm;
                right: 2.5cm;
                font-size: 10pt;
            }
            .signature-block {
                margin-top: 1.5cm;
                display: flex;
                justify-content: space-between;
            }
            .signature-box {
                width: 45%;
                text-align: center;
            }
            .signature-pad-wrap {
                border: 1px solid #dbe3ef;
                border-radius: 8px;
                background: #f8fafc;
                padding: 14px;
            }
            .signature-pad {
                width: 100%;
                height: 170px;
                border: 1px dashed #94a3b8;
                border-radius: 6px;
                background: #fff;
                touch-action: none;
            }
            .signature-preview {
                height: 88px;
                display: flex;
                align-items: center;
                justify-content: center;
                border: 1px solid #e2e8f0;
                border-radius: 6px;
                background: #fff;
                margin-bottom: 10px;
            }
            .signature-preview img {
                max-width: 220px;
                max-height: 78px;
                object-fit: contain;
            }
            .report-sign-space {
                height: 70px;
                margin: 8px 0;
            }
            .report-sign-image {
                display: block;
                max-width: 190px;
                max-height: 70px;
                object-fit: contain;
                margin: 8px auto;
            }
            @page {
                size: A4 portrait;
                margin: 2cm 2.5cm 2cm 2.5cm;
            }
            @page first-page {
                size: A4 portrait;
                margin: 2cm 2.5cm 2cm 2.5cm;
                @bottom-right {
                    content: "";
                }
            }
            @page roman-page {
                size: A4 portrait;
                margin: 2cm 2.5cm 2cm 2.5cm;
                @bottom-right {
                    content: counter(page, lower-roman);
                    font-family: "Times New Roman", Times, serif;
                    font-size: 10pt;
                }
            }
            @page arabic-page {
                size: A4 portrait;
                margin: 2cm 2.5cm 2cm 2.5cm;
                @bottom-right {
                    content: counter(page, decimal);
                    font-family: "Times New Roman", Times, serif;
                    font-size: 10pt;
                }
            }
            @page landscape-page {
                size: A4 landscape;
                margin: 2cm 2cm 2cm 2cm;
                @bottom-right {
                    content: counter(page, decimal);
                    font-family: "Times New Roman", Times, serif;
                    font-size: 10pt;
                }
            }
            
            .document-page.first {
                page: first-page;
            }
            .document-page.roman {
                page: roman-page;
            }
            .document-page.arabic {
                page: arabic-page;
            }
            .document-page.landscape {
                width: 29.7cm !important;
                min-height: 21cm !important;
                padding: 2cm 2cm 2cm 2cm !important;
                page: landscape-page;
            }
            
            @media print {
                body, html {
                    background: none !important;
                    padding: 0 !important;
                    margin: 0 !important;
                    width: 100% !important;
                }
                #sidebar, .sidebar-wrapper, .burger-btn, header, .no-print {
                    display: none !important;
                }
                #main, #app {
                    margin: 0 !important;
                    padding: 0 !important;
                    width: 100% !important;
                    background: none !important;
                }
                .doc-container {
                    background: none !important;
                    padding: 0 !important;
                    margin: 0 !important;
                    gap: 0 !important;
                    width: 100% !important;
                }
                .document-page {
                    box-shadow: none !important;
                    margin: 0 !important;
                    width: 100% !important;
                    min-height: 0 !important;
                    padding: 0 !important;
                    page-break-before: always;
                    break-before: page;
                    position: relative !important;
                }
                .document-page.landscape {
                    page: landscape-page;
                    box-shadow: none !important;
                    margin: 0 !important;
                    width: 100% !important;
                    min-height: 0 !important;
                    padding: 0 !important;
                    position: relative !important;
                }
                .document-page.first {
                    page-break-before: avoid !important;
                    break-before: avoid !important;
                }
                .document-page:not(.landscape) + .document-page.landscape {
                    page-break-before: avoid !important;
                    break-before: avoid !important;
                }
                .document-page.landscape + .document-page:not(.landscape) {
                    page-break-before: avoid !important;
                    break-before: avoid !important;
                }
                .page-number-footer {
                    display: none !important;
                }
                tr {
                    page-break-inside: avoid !important;
                    break-inside: avoid !important;
                }
                table {
                    page-break-inside: auto !important;
                    break-inside: auto !important;
                }
            }
            .editable-subbab p {
                text-align: justify;
                text-indent: 1.25cm;
                margin-bottom: 0.4cm;
                line-height: 1.5;
            }
            .editable-subbab ol, .editable-subbab ul {
                text-align: justify;
                margin-bottom: 0.4cm;
                padding-left: 0.75cm;
                line-height: 1.5;
            }
            .note-editor.note-frame {
                border: 1px solid #3b82f6 !important;
                border-radius: 6px;
                background: #fff;
                margin-bottom: 10px;
            }
        </style>

        <div class="doc-container">
            <?= audit_signature_tools($ka, $et, $audit, $ketuaaud, $versi); ?>
            <!-- PAGE 1: COVER -->
            <div class="document-page first">
                <div class="text-center" style="margin-top: 2cm; text-align: center;">
                    <img src="../poltekkes.png" style="width: 3.5cm; height: auto; margin-bottom: 2cm;" alt="Logo Poltekkes">
                    <h2 class="doc-title-large">LAPORAN HASIL AUDIT MUTU INTERNAL (AMI)<br><?= $versi === '2' ? 'BERBASIS MANAJEMEN RISIKO' : 'BIDANG AKADEMIK'; ?></h2>
                    <h3 class="doc-title-medium" style="margin-top: 1.5cm; text-transform: uppercase;"><?= $label_prodi_upper; ?><?= htmlspecialchars($audit['auditi']); ?></h3>
                    <h3 class="doc-title-medium">TAHUN <?= date('Y', strtotime($audit['tglmulai'])); ?></h3>
                    
                    <div style="margin-top: 6cm; font-weight: bold; line-height: 1.6;">
                        TIM AUDIT MUTU INTERNAL<br>
                        POLTEKKES KEMENKES BENGKULU<br>
                        TAHUN <?= date('Y', strtotime($audit['tglmulai'])); ?>
                    </div>
                </div>
            </div>

            <!-- PAGE 2: KATA PENGANTAR -->
            <div class="document-page roman">
                <h2 class="doc-heading-1">KATA PENGANTAR</h2>
                <?php 
                $def_kata_pengantar = '<p class="doc-paragraph">Puji syukur kami panjatkan kehadirat Tuhan Yang Maha Kuasa sehingga Laporan Audit Mutu Internal ' . $label_prodi . htmlspecialchars($audit['auditi']) . ' Poltekkes Kemenkes Bengkulu Tahun ' . date('Y', strtotime($audit['tglmulai'])) . ' ini dapat diselesaikan. Laporan ini disusun sebagai laporan hasil audit oleh Tim Audit Mutu Internal berdasarkan Surat Keputusan Direktur tentang Penetapan Auditor pada pelaksanaan AMI di Poltekkes Kemenkes Bengkulu.</p><p class="doc-paragraph">Berdasarkan hasil audit terdapat beberapa kondisi yang keseluruhannya sudah mendapat tanggapan dari pihak ' . $label_prodi . htmlspecialchars($audit['auditi']) . '. Harapan kami kondisi tersebut dapat segera ditindaklanjuti sehingga dapat meningkatkan kualitas dan kinerja ' . $label_kajian . '.</p><p class="doc-paragraph">Apresiasi kami sampaikan kepada auditi yang telah kooperatif dalam pelaksanaan kegiatan audit ini. Ucapan terima kasih kami berikan bagi semua pihak yang telah membantu terlastananya kegiatan.</p>'; 
                ?>
                <div class="editable-subbab" data-subbab="kata_pengantar" data-title="Kata Pengantar">
                    <?= get_narasi('kata_pengantar', $def_kata_pengantar); ?>
                </div>
                
                <div style="margin-top: 3cm; margin-left: 9cm; text-align: center;">
                    Bengkulu, <?= audit_laporan_date($audit['tglselesai']); ?><br>
                    Auditor AMI<br><br><br><br>
                    <strong>Tim Auditor</strong>
                </div>
                <div class="page-number-footer">ii</div>
            </div>

            <!-- PAGE 3: DAFTAR ISI -->
            <div class="document-page roman">
                <h2 class="doc-heading-1">DAFTAR ISI</h2>
                <div style="margin-top: 1.5cm; line-height: 2;">
                    <strong>COVER LAPORAN</strong> <span style="float: right;">i</span><br>
                    <strong>KATA PENGANTAR</strong> <span style="float: right;">ii</span><br>
                    <strong>DAFTAR ISI</strong> <span style="float: right;">iii</span><br>
                    <strong>BAB I PENDAHULUAN</strong><br>
                    <span style="margin-left: 0.5cm;">A. Latar Belakang</span> <span style="float: right;">1</span><br>
                    <span style="margin-left: 0.5cm;">B. Tujuan Pemeriksaan</span> <span style="float: right;">1</span><br>
                    <span style="margin-left: 0.5cm;">C. Lingkup Pemeriksaan</span> <span style="float: right;">2</span><br>
                    <span style="margin-left: 0.5cm;">D. Dasar Hukum/Aturan yang Digunakan</span> <span style="float: right;">2</span><br>
                    <span style="margin-left: 0.5cm;">E. Batasan Pemeriksaan</span> <span style="float: right;">3</span><br>
                    <span style="margin-left: 0.5cm;">F. Metode Pemeriksaan</span> <span style="float: right;">3</span><br>
                    <span style="margin-left: 0.5cm;">G. Tahapan Pemeriksaan</span> <span style="float: right;">3</span><br>
                    <span style="margin-left: 0.5cm;">H. Kajian Ulang Hasil Audit Sebelumnya</span> <span style="float: right;">3</span><br>
                    <span style="margin-left: 0.5cm;">I. Pengorganisasian Tim Audit</span> <span style="float: right;">3</span><br>
                    <strong>BAB II GAMBARAN UMUM POLTEKKES KEMENKES BENGKULU</strong><br>
                    <span style="margin-left: 0.5cm;">A. Latar Belakang Pendirian</span> <span style="float: right;">4</span><br>
                    <span style="margin-left: 0.5cm;">B. Visi, Misi dan Tujuan</span> <span style="float: right;">4</span><br>
                    <span style="margin-left: 0.5cm;">C. Organisasi Pelaksana dan Pejabat</span> <span style="float: right;">5</span><br>
                    <?php if($versi === '2'): ?>
                    <strong>BAB III HASIL AUDIT BERBASIS MANAJEMEN RISIKO</strong><br>
                    <span style="margin-left: 0.5cm;">A. Catatan Persiapan & Identifikasi Risiko</span> <span style="float: right;">6</span><br>
                    <span style="margin-left: 0.5cm;">B. Lembar Kerja Kerja 2 (LK2 Digital)</span> <span style="float: right;">6</span><br>
                    <span style="margin-left: 0.5cm;">C. Triangulasi Temuan Audit</span> <span style="float: right;">7</span><br>
                    <span style="margin-left: 0.5cm;">D. Permintaan Tindakan Koreksi (PTK A)</span> <span style="float: right;">7</span><br>
                    <strong>BAB IV KESIMPULAN DAN REKOMENDASI RISIKO</strong><br>
                    <span style="margin-left: 0.5cm;">A. Kesimpulan Audit Berbasis Risiko</span> <span style="float: right;">8</span><br>
                    <span style="margin-left: 0.5cm;">B. Rekomendasi Umum</span> <span style="float: right;">8</span><br>
                    <strong>LAMPIRAN DOKUMENTASI AUDIT</strong> <span style="float: right;">9</span>
                    <?php else: ?>
                    <strong>BAB III HASIL PEMERIKSAAN</strong><br>
                    <span style="margin-left: 0.5cm;">A. Ringkasan Kondisi Audit</span> <span style="float: right;">6</span><br>
                    <span style="margin-left: 0.5cm;">B. Deskripsi Kondisi Audit</span> <span style="float: right;">6</span><br>
                    <strong>BAB IV KESIMPULAN DAN REKOMENDASI</strong><br>
                    <span style="margin-left: 0.5cm;">A. Kesimpulan</span> <span style="float: right;">7</span><br>
                    <span style="margin-left: 0.5cm;">B. Rekomendasi</span> <span style="float: right;">7</span><br>
                    <strong>LAMPIRAN-LAMPIRAN</strong><br>
                    <span style="margin-left: 0.5cm;">Lampiran 1: Checklist Catatan Audit</span> <span style="float: right;">8</span><br>
                    <span style="margin-left: 0.5cm;">Lampiran 2: Dokumentasi Audit</span> <span style="float: right;">9</span>
                    <?php endif; ?>
                </div>
                <div class="page-number-footer">iii</div>
            </div>

            <!-- PAGE 4: BAB I PENDAHULUAN -->
            <div class="document-page arabic start-counter">
                <h2 class="doc-heading-1">BAB I<br>PENDAHULUAN</h2>
                
                <h3 class="doc-heading-2">A. Latar Belakang</h3>
                <?php 
                $def_bab1_latar = '<p>Tuntutan masyarakat terhadap kualitas pendidikan tinggi saat ini semakin menjadi acuan bagi setiap institusi pendidikan tinggi meningkatkan mutu akademik beserta pelayanannya. Pemerintah juga memperkuat hal ini dengan mengeluarkan berbagai undang-undang dan peraturan yaitu Permendikbud RI Nomor 3 tahun 2020 tentang Standar Nasional Pendidikan Tinggi, Undang-undang Nomor 20 tahun 2003 tentang sistem pendidikan nasional, Undang-undang Nomor 14 tahun 2005 tentang Guru dan Dosen, Peraturan Pemerintah Nomor 4 Tahun 2014 tentang perguruan tinggi, Peraturan Pemerintah Nomor 19 tahun 2005 tentang standar pendidikan tinggi, dan Sistem Penjaminan Mutu Pendidikan Tinggi.</p><p>Poltekkes Kemenkes Bengkulu merupakan perguruan tinggi kesehatan negeri yang terus berusaha untuk meningkatkan kualitas baik dalam segi mutu akademik maupun pelayanan administrasi bagi civitas akademik itu sendiri. Salah satu misi Poltekkes Kemenkes Bengkulu yaitu meningkatkan kualitas tata kelola, SDM serta sarana dan prasarana pendidikan. Kegiatan audit internal bidang akademik merupakan salah satu bentuk monitoring dan evaluasi yang dilakukan terhadap capaian maupun target-target yang telah ditetapkan pada ' . $label_prodi . htmlspecialchars($audit['auditi']) . '.</p>'; 
                ?>
                <div class="editable-subbab" data-subbab="bab1_latar_belakang" data-title="A. Latar Belakang">
                    <?= get_narasi('bab1_latar_belakang', $def_bab1_latar); ?>
                </div>

                <h3 class="doc-heading-2">B. Tujuan Pemeriksaan</h3>
                <?php 
                $def_bab1_tujuan = '<ol style="list-style-type: decimal;"><li>Meneliti kepatuhan/ketaatan penjaminan mutu akademik internal tingkat poltekkes terhadap standar isi (Kerangka dasar dan struktur kurikulum).</li><li>Meneliti kesesuaian arah dan pelaksanaan penjaminan mutu akademik internal tingkat Departemen terhadap Standar perencanaan proses pembelajaran di tingkat ' . ($is_akademik ? 'Prodi' : 'Unit') . '.</li><li>Meneliti kepastian bahwa standar kualifikasi akademik dosen sesuai dengan yang ditetapkan oleh Poltekkes (aturan akademik).</li><li>Untuk memastikan standar penilaian hasil belajar oleh dosen sesuai dengan standar akademik ' . $label_kajian . '.</li><li>Untuk memastikan standar penelitian dan standar pengabdian masyarakat yang berlaku di ' . $label_prodi . 'sesuai dengan standar tingkat Poltekkes Kemenkes Bengkulu.</li></ol>'; 
                ?>
                <div class="editable-subbab" data-subbab="bab1_tujuan" data-title="B. Tujuan Pemeriksaan">
                    <?= get_narasi('bab1_tujuan', $def_bab1_tujuan); ?>
                </div>
                <div class="page-number-footer">1</div>
            </div>

            <!-- PAGE 5: BAB I PENDAHULUAN CONTINUED & DASAR HUKUM -->
            <div class="document-page arabic">
                <h3 class="doc-heading-2">C. Lingkup Pemeriksaan</h3>
                <?php 
                $def_bab1_lingkup = '<p><strong>1. Sasaran Pemeriksaan:</strong> Keandalan Sistem Pengendalian Internal atas administrasi bidang akademik di ' . $label_prodi . htmlspecialchars($audit['auditi']) . ' Poltekkes Kemenkes Bengkulu.</p><p><strong>2. Periode yang Diperiksa:</strong> Pelaksanaan akademik Semester Ganjil dan Genap Tahun Akademik ' . (date('Y', strtotime($audit['tglmulai'])) - 1) . '/' . date('Y', strtotime($audit['tglmulai'])) . '.</p>'; 
                ?>
                <div class="editable-subbab" data-subbab="bab1_lingkup" data-title="C. Lingkup Pemeriksaan">
                    <?= get_narasi('bab1_lingkup', $def_bab1_lingkup); ?>
                </div>

                <h3 class="doc-heading-2">D. Dasar Hukum/Aturan yang Digunakan</h3>
                <?php 
                $def_bab1_hukum = '<ol style="list-style-type: decimal;"><li>Undang-Undang Nomor 12 Tahun 2012 tentang Perguruan Tinggi.</li><li>Undang-Undang Nomor 20 Tahun 2003 tentang Sistem Pendidikan Nasional.</li><li>Peraturan Pemerintah Nomor 4 Tahun 2014 tentang Penyelenggaraan Pendidikan Tinggi dan Pengelolaan Perguruan Tinggi.</li><li>Peraturan Menteri Pendidikan dan Kebudayaan Nomor 3 Tahun 2020 tentang Standar Nasional Pendidikan Tinggi.</li><li>Permenristekdikti Nomor 62 Tahun 2016 tentang Sistem Penjaminan Mutu Pendidikan Tinggi.</li><li>Peraturan Menteri Kesehatan tentang Organisasi dan Tata Kerja Politeknik Kesehatan.</li><li>Surat Keputusan Direktur Poltekkes Kemenkes Bengkulu tentang Penetapan Auditor AMI.</li></ol>'; 
                ?>
                <div class="editable-subbab" data-subbab="bab1_dasar_hukum" data-title="D. Dasar Hukum/Aturan yang Digunakan">
                    <?= get_narasi('bab1_dasar_hukum', $def_bab1_hukum); ?>
                </div>

                <h3 class="doc-heading-2">E. Batasan Pemeriksaan</h3>
                <?php 
                $def_bab1_batasan = '<p>Pemeriksaan meliputi prosedur-prosedur yang dirancang untuk memberikan keyakinan yang memadai dalam mendeteksi adanya ketidaksesuaian dari pelaksanaan akademik yang berpengaruh terhadap pelayanan mutu akademik di ' . $label_prodi . htmlspecialchars($audit['auditi']) . '.</p>'; 
                ?>
                <div class="editable-subbab" data-subbab="bab1_batasan" data-title="E. Batasan Pemeriksaan">
                    <?= get_narasi('bab1_batasan', $def_bab1_batasan); ?>
                </div>

                <h3 class="doc-heading-2">F. Metode Pemeriksaan</h3>
                <?php 
                $def_bab1_metode = '<p>Pemeriksaan diawali dengan melakukan pertemuan awal, evaluasi dokumen desk evaluation, wawancara/visitasi lapangan, analisis data hasil temuan, pembahasan hasil temuan dengan auditi, penyusunan rencana perbaikan, dan laporan.</p>'; 
                ?>
                <div class="editable-subbab" data-subbab="bab1_metode" data-title="F. Metode Pemeriksaan">
                    <?= get_narasi('bab1_metode', $def_bab1_metode); ?>
                </div>
                <div class="page-number-footer">2</div>
            </div>

            <!-- PAGE 6: TAHAPAN, KAJIAN ULANG & TIM AUDIT -->
            <div class="document-page arabic">
                <h3 class="doc-heading-2">G. Tahapan Pemeriksaan</h3>
                <?php 
                $def_bab1_tahapan = '<p>Pelaksanaan kegiatan audit dimulai dari persiapan administrasi oleh Auditor SPMI. Tim auditor kemudian melakukan perencanaan audit, desk evaluation, visitasi lapangan pada tanggal ' . audit_laporan_date($audit['tglmulai']) . ' s.d ' . audit_laporan_date($audit['tglselesai']) . ' di ' . $label_prodi . htmlspecialchars($audit['auditi']) . '.</p>'; 
                ?>
                <div class="editable-subbab" data-subbab="bab1_tahapan" data-title="G. Tahapan Pemeriksaan">
                    <?= get_narasi('bab1_tahapan', $def_bab1_tahapan); ?>
                </div>

                <h3 class="doc-heading-2">H. Kajian Ulang Hasil Audit Sebelumnya</h3>
                <p class="doc-paragraph">Berdasarkan hasil pemantauan tindak lanjut hasil audit periode sebelumnya untuk <?= $label_prodi; ?><?= htmlspecialchars($audit['auditi']); ?>, status penyelesaian temuan adalah sebagai berikut:</p>
                
                <table class="table-doc">
                    <thead>
                        <tr>
                            <th style="width: 10%;">No</th>
                            <th>Hasil Temuan</th>
                            <th style="width: 15%; text-align: center;">Status (OB/KTS)</th>
                            <th>Hasil Tindak Lanjut</th>
                            <th style="width: 15%; text-align: center;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $current_year = date('Y', strtotime($audit['tglmulai']));
                        $previous_year = $current_year - 1;
                        $unit_id = $audit['unit'];
                        $q_prev_audit = mysqli_query($kon, "
                            SELECT ak.* 
                            FROM audit_kondisi ak
                            JOIN audit a ON ak.kodeaudit = a.kodeaudit
                            WHERE a.unit = '$unit_id' AND YEAR(a.tglmulai) = '$previous_year' AND ak.jeniskondisi = 'Temuan'
                        ");
                        $has_prev = false;
                        $p_no = 1;
                        if($q_prev_audit && mysqli_num_rows($q_prev_audit) > 0) {
                            while($p = mysqli_fetch_assoc($q_prev_audit)) {
                                $has_prev = true;
                                echo "<tr>
                                        <td style='text-align: center;'>".$p_no++."</td>
                                        <td>".htmlspecialchars($p['kondisi'])."</td>
                                        <td style='text-align: center;'>".htmlspecialchars($p['kategori'])."</td>
                                        <td>".htmlspecialchars($p['tindkoreksi'] ?: 'Sudah dilakukan perbaikan')."</td>
                                        <td style='text-align: center;'>".htmlspecialchars($p['status'])."</td>
                                      </tr>";
                            }
                        }
                        if(!$has_prev) {
                            echo "<tr>
                                    <td colspan='5' style='text-align: center; color: #64748b;'>Tidak ada temuan dari audit periode sebelumnya.</td>
                                  </tr>";
                        }
                        ?>
                    </tbody>
                </table>

                <h3 class="doc-heading-2">I. Pengorganisasian Tim Audit</h3>
                <p class="doc-paragraph">Tim pelaksana kegiatan Audit Mutu Internal di <?= $label_prodi; ?><?= htmlspecialchars($audit['auditi']); ?> terdiri dari:</p>
                <table class="table-doc" style="width: 80%; margin-left: 1.25cm;">
                    <thead>
                        <tr>
                            <th style="width: 15%;">No</th>
                            <th>Nama Auditor</th>
                            <th>Peran</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($auditors_list as $idx => $aud): ?>
                            <tr>
                                <td style="text-align: center;"><?= $idx + 1; ?></td>
                                <td><?= htmlspecialchars($aud['name']); ?></td>
                                <td><?= htmlspecialchars($aud['status']); ?></td>
                            </tr>
                        <?php endforeach; if(empty($auditors_list)): ?>
                            <tr>
                                <td colspan="3" style="text-align: center; color: #64748b;">Belum ada auditor yang terdaftar.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
                <div class="page-number-footer">3</div>
            </div>

            <!-- PAGE 7: BAB II GAMBARAN UMUM -->
            <div class="document-page arabic">
                <h2 class="doc-heading-1">BAB II<br>GAMBARAN UMUM POLTEKKES KEMENKES BENGKULU</h2>
                
                <h3 class="doc-heading-2">A. Latar Belakang Pendirian</h3>
                <?php 
                $def_bab2_pendirian = '<p>Politeknik Kesehatan Kemenkes Bengkulu pada awalnya adalah akademi-akademi kesehatan di bawah Departemen Kesehatan yang disatukan menjadi Politeknik Kesehatan berdasarkan Keputusan Menteri Kesehatan. Saat ini Poltekkes Kemenkes Bengkulu menyelenggarakan pendidikan tingkat Diploma III, Sarjana Terapan, dan Profesi di berbagai jurusan kesehatan guna menghasilkan tenaga kesehatan yang profesional.</p>'; 
                ?>
                <div class="editable-subbab" data-subbab="bab2_pendirian" data-title="A. Latar Belakang Pendirian">
                    <?= get_narasi('bab2_pendirian', $def_bab2_pendirian); ?>
                </div>

                <h3 class="doc-heading-2">B. Visi, Misi dan Tujuan</h3>
                <?php 
                $def_bab2_visimisi = '<p><strong>Visi:</strong><br> Menjadi Perguruan Tinggi Kesehatan Unggul Yang Menghasilkan Tenaga Kesehatan Profesional Dan Berdaya Saing Global Tahun 2029.</p><p><strong>Misi:</strong></p><ol style="list-style-type: decimal;"><li>Menyelenggarakan tata kelola yang akuntabel dan pelayanan prima.</li><li>Menyelenggarakan pelayanan kemahasiswaan yang mengembangkan sikap ilmiah, merangsang daya kreasi dan inovasi mahasiswa berdaya saing global,</li><li>Menyelenggarakan pendidikan, penelitian dan pengabdian masyarakat yang bermutu dan berdaya saing global, </li><li>Menyiapkan sarana prasarana yang modern dan SDM yang kompeten terstandar global, dan</li><li>Mengembangkan kerjasama yang mendukung tridharma dan pengembangan bisnis berdaya saing global.</li></ol>'; 
                ?>
                <div class="editable-subbab" data-subbab="bab2_visi_misi" data-title="B. Visi, Misi dan Tujuan">
                    <?= get_narasi('bab2_visi_misi', $def_bab2_visimisi); ?>
                </div>

                <h3 class="doc-heading-2">C. Organisasi Pelaksana dan Pejabat</h3>
                <?php 
                $def_bab2_organisasi = '<p>Pengelola struktural Poltekkes Kemenkes Bengkulu:</p><ul><li>Direktur :	Linda, S.ST., M.Kes</li><li>Wakil Direktur I	:	Ns. Septiyanti, S.Kep., M.Pd</li><li>Wakil Direktur II: Pirdaus, SKM., M.Si</li><li>Wakil Direktur III: Bdn. Yuniarti, S.ST., M.Kes</li></ul>'; 
                ?>
                <div class="editable-subbab" data-subbab="bab2_organisasi" data-title="C. Organisasi Pelaksana dan Pejabat">
                    <?= get_narasi('bab2_organisasi', $def_bab2_organisasi); ?>
                </div>
                <div class="page-number-footer">4</div>
            </div>

            <?php if($versi === '1'): ?>
            <!-- PAGE 8: BAB III HASIL PEMERIKSAAN -->
            <div class="document-page arabic">
                <h2 class="doc-heading-1">BAB III<br>HASIL PEMERIKSAAN</h2>
                
                <h3 class="doc-heading-2">A. Ringkasan Kondisi Audit</h3>
                
                <h4 class="doc-heading-2" style="font-size: 11pt; margin-left: 0.5cm;">1. Kondisi Positif</h4>
                <?php 
                $def_bab3_positif = '<p>Praktik baik dan kelebihan yang diidentifikasi dari ' . $label_prodi . htmlspecialchars($audit['auditi']) . ' selama audit berlangsung adalah:</p>'; 
                ?>
                <div class="editable-subbab" data-subbab="bab3_intro_positif" data-title="Intro Kondisi Positif">
                    <?= get_narasi('bab3_intro_positif', $def_bab3_positif); ?>
                </div>
                <table class="table-doc">
                    <thead>
                        <tr>
                            <th style="width: 10%;">No</th>
                            <th>Daftar Kondisi Positif</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($positifRows as $idx => $p): ?>
                            <tr>
                                <td style="text-align: center;"><?= $idx + 1; ?></td>
                                <td><?= htmlspecialchars($p['kondisi']); ?></td>
                            </tr>
                        <?php endforeach; if(empty($positifRows)): ?>
                            <tr>
                                <td colspan="2" style="text-align: center; color: #64748b;">Tidak ditemukan kondisi positif yang dicatat.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>

                <h4 class="doc-heading-2" style="font-size: 11pt; margin-left: 0.5cm; margin-top: 0.6cm;">2. Hasil Temuan Audit</h4>
                <?php 
                $def_bab3_temuan = '<p>Berikut adalah daftar temuan ketidaksesuaian yang diidentifikasi oleh Tim Auditor:</p>'; 
                ?>
                <div class="editable-subbab" data-subbab="bab3_intro_temuan" data-title="Intro Temuan Audit">
                    <?= get_narasi('bab3_intro_temuan', $def_bab3_temuan); ?>
                </div>
                <table class="table-doc">
                    <thead>
                        <tr>
                            <th style="width: 10%;">No</th>
                            <th>Daftar Temuan Audit (Ketidaksesuaian)</th>
                            <th style="width: 25%; text-align: center;">Kategori (OB/KTS)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($temuanRows as $idx => $t): ?>
                            <tr>
                                <td style="text-align: center;"><?= $idx + 1; ?></td>
                                <td><?= htmlspecialchars($t['kondisi']); ?></td>
                                <td style="text-align: center; font-weight: bold;"><?= htmlspecialchars($t['kategori']); ?></td>
                            </tr>
                        <?php endforeach; if(empty($temuanRows)): ?>
                            <tr>
                                <td colspan="3" style="text-align: center; color: #64748b;">Tidak ditemukan temuan audit.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
                <div class="page-number-footer">5</div>
            </div>

            <!-- PAGE 9: BAB III HASIL PEMERIKSAAN CONTINUED -->
            <div class="document-page arabic">
                <h3 class="doc-heading-2">B. Deskripsi Kondisi Audit</h3>
                <?php 
                $def_bab3_deskripsi = '<p>Deskripsi rinci mengenai akar masalah, rekomendasi, serta jadwal pembahasan masing-masing temuan adalah sebagai berikut:</p>'; 
                ?>
                <div class="editable-subbab" data-subbab="bab3_intro_deskripsi" data-title="Intro Deskripsi Temuan">
                    <?= get_narasi('bab3_intro_deskripsi', $def_bab3_deskripsi); ?>
                </div>
                
                <table class="table-doc" style="font-size: 10pt;">
                    <thead>
                        <tr>
                            <th style="width: 5%;">No</th>
                            <th style="width: 35%;">Daftar Temuan / Kondisi</th>
                            <th style="width: 25%;">Akar Masalah (Penyebab)</th>
                            <th style="width: 25%;">Rekomendasi Auditor</th>
                            <th style="width: 10%;">Jadwal Pembahasan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($temuanRows as $idx => $t): ?>
                            <tr>
                                <td style="text-align: center;"><?= $idx + 1; ?></td>
                                <td>
                                    <strong><?= htmlspecialchars($t['kondisi']); ?></strong>
                                    <div style="margin-top: 4px; font-size: 0.85rem; color: #64748b;">Kat: <?= htmlspecialchars($t['kategori']); ?></div>
                                </td>
                                <td><?= nl2br(htmlspecialchars($t['penyebab'] ?: '-')); ?></td>
                                <td><?= nl2br(htmlspecialchars($t['rekomendasi'] ?: '-')); ?></td>
                                <td style="text-align: center;"><?= $t['jadpen1'] && $t['jadpen1'] !== '0000-00-00' ? date('d-m-Y', strtotime($t['jadpen1'])) : '-'; ?></td>
                            </tr>
                        <?php endforeach; if(empty($temuanRows)): ?>
                            <tr>
                                <td colspan="5" style="text-align: center; color: #64748b;">Tidak ada deskripsi temuan audit.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
                <div class="page-number-footer">6</div>
            </div>

            <!-- PAGE 10: BAB IV KESIMPULAN DAN REKOMENDASI -->
            <div class="document-page arabic">
                <h2 class="doc-heading-1">BAB IV<br>KESIMPULAN DAN REKOMENDASI</h2>
                
                <h3 class="doc-heading-2">A. Kesimpulan</h3>
                <?php 
                $def_bab4_kesimpulan = '<p>Berdasarkan hasil audit mutu internal yang telah dilaksanakan di ' . $label_prodi . htmlspecialchars($audit['auditi']) . ' Poltekkes Kemenkes Bengkulu, Tim Auditor menyimpulkan:</p>'; 
                ?>
                <div class="editable-subbab" data-subbab="bab4_intro_kesimpulan" data-title="Intro Kesimpulan">
                    <?= get_narasi('bab4_intro_kesimpulan', $def_bab4_kesimpulan); ?>
                </div>
                <ol class="doc-list" style="list-style-type: decimal;">
                    <li>Terdapat <?= count($positifRows); ?> aspek kekuatan/kondisi positif yang perlu dipertahankan dan dikembangkan lebih lanjut.</li>
                    <li>Terdapat <?= count($temuanRows); ?> temuan ketidaksesuaian dengan rincian kategori:
                        <ul>
                            <?php 
                            $kts = 0; $ob = 0;
                            foreach($temuanRows as $t) {
                                if(strtoupper($t['kategori']) === 'KTS') $kts++;
                                else $ob++;
                            }
                            ?>
                            <li>Kategori KTS (Ketidaksesuaian): <strong><?= $kts; ?></strong> temuan.</li>
                            <li>Kategori OB (Observasi): <strong><?= $ob; ?></strong> temuan.</li>
                        </ul>
                    </li>
                </ol>

                <h3 class="doc-heading-2">B. Rekomendasi</h3>
                <?php 
                $def_bab4_rekomendasi = '<p>Dalam rangka meningkatkan mutu tata kelola akademik, Tim Auditor merekomendasikan hal-hal berikut kepada ' . $label_prodi . htmlspecialchars($audit['auditi']) . ':</p>'; 
                ?>
                <div class="editable-subbab" data-subbab="bab4_intro_rekomendasi" data-title="Intro Rekomendasi">
                    <?= get_narasi('bab4_intro_rekomendasi', $def_bab4_rekomendasi); ?>
                </div>
                <ol class="doc-list" style="list-style-type: decimal;">
                    <?php foreach($temuanRows as $idx => $t): ?>
                        <li><?= htmlspecialchars($t['rekomendasi'] ?: 'Melakukan perbaikan atas temuan '.$t['kondisi']); ?></li>
                    <?php endforeach; if(empty($temuanRows)): ?>
                        <li style="color: #64748b;">Tidak ada rekomendasi khusus karena tidak ditemukan temuan.</li>
                    <?php endif; ?>
                </ol>

                <div class="signature-block" style="margin-top: 2.5cm;">
                    <div class="signature-box">
                        <?= $label_kaprodi; ?>
                        <?= audit_signature_image('auditi'); ?>
                        <strong>( <?= audit_signature_name('auditi', $audit['kaunit'] ?: '........................'); ?> )</strong>
                    </div>
                    <div class="signature-box">
                        Ketua Tim Auditor AMI
                        <?= audit_signature_image('auditor'); ?>
                        <strong>( <?= audit_signature_name('auditor', $ketuaaud ?: '........................'); ?> )</strong>
                    </div>
                </div>
                <div class="page-number-footer">7</div>
            </div>

            <!-- PAGE 11: LAMPIRAN CATATAN CHECKLIST -->
            <div class="document-page arabic">
                <h2 class="doc-heading-1">LAMPIRAN<br>CATATAN AUDIT (CHECKLIST)</h2>
                <?php 
                $def_lampiran = '<p class="doc-paragraph">Berikut merupakan lembar checklist catatan evaluasi ketaatan standar oleh auditor:</p>'; 
                ?>
                <div class="editable-subbab" data-subbab="lampiran_intro" data-title="Intro Lampiran Checklist">
                    <?= get_narasi('lampiran_intro', $def_lampiran); ?>
                </div>
                
                <table class="table-doc" style="font-size: 10pt;">
                    <thead>
                        <tr>
                            <th style="width: 5%;">No</th>
                            <th style="width: 20%;">Kode / Referensi</th>
                            <th style="width: 45%;">Aspek Telaah</th>
                            <th style="width: 15%; text-align: center;">Kesesuaian</th>
                            <th style="width: 15%;">Catatan Khusus</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($catatanRows as $idx => $c): ?>
                            <tr>
                                <td style="text-align: center;"><?= $idx + 1; ?></td>
                                <td>
                                    <strong><?= htmlspecialchars($c['kodecatatan']); ?></strong>
                                    <div style="font-size: 0.85rem; color: #64748b;"><?= htmlspecialchars($c['referensi']); ?></div>
                                </td>
                                <td><?= htmlspecialchars($c['telaah']); ?></td>
                                <td style="text-align: center; vertical-align: middle;">
                                    <?= $c['sesuai'] === 'Sesuai' ? '<span style="color: #059669; font-weight: bold; font-size: 16pt;">✔</span>' : '<span style="color: #dc2626; font-weight: bold; font-size: 14pt;">✘</span>'; ?>
                                </td>
                                <td><?= htmlspecialchars($c['khusus'] ?: '-'); ?></td>
                            </tr>
                        <?php endforeach; if(empty($catatanRows)): ?>
                            <tr>
                                <td colspan="5" style="text-align: center; color: #64748b;">Belum ada catatan evaluasi.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
                <div class="page-number-footer">8</div>
            </div>
            <?php else: ?>
            <!-- VERSI 2: BAB III HASIL AUDIT BERBASIS MANAJEMEN RISIKO -->
            <div class="document-page arabic">
                <h2 class="doc-heading-1">BAB III<br>HASIL AUDIT BERBASIS MANAJEMEN RISIKO</h2>
                
                <h3 class="doc-heading-2">A. Catatan Persiapan & Identifikasi Risiko (Tahap 5)</h3>
                <p class="doc-paragraph">Berikut adalah daftar identifikasi risiko awal <?= $label_prodi_lower; ?> sebelum pelaksanaan visitasi audit lapangan:</p>
                <table class="table-doc" style="font-size: 9pt;">
                    <thead>
                        <tr>
                            <th style="width: 4%;">No</th>
                            <th style="width: 8%;">Kode</th>
                            <th style="width: 32%;">Pernyataan Standar & Risiko Potensial</th>
                            <th style="width: 15%;">Indikator & Target</th>
                            <th style="width: 4%; text-align: center;">D</th>
                            <th style="width: 4%; text-align: center;">P</th>
                            <th style="width: 5%; text-align: center;">Skor</th>
                            <th style="width: 10%; text-align: center;">Level</th>
                            <th style="width: 18%;">Kondisi Awal / Pengendalian</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($persiapanRisikoRows as $idx => $pr): 
                            $lvl = strtoupper($pr['level'] ?? '');
                            $lvlBadge = '';
                            if($lvl === 'MERAH') $lvlBadge = '<span style="color:#ef4444; font-weight:bold;">MERAH</span>';
                            elseif($lvl === 'KUNING') $lvlBadge = '<span style="color:#f59e0b; font-weight:bold;">KUNING</span>';
                            elseif($lvl === 'HIJAU') $lvlBadge = '<span style="color:#10b981; font-weight:bold;">HIJAU</span>';
                            else $lvlBadge = '-';
                        ?>
                            <tr>
                                <td style="text-align: center;"><?= $idx + 1; ?></td>
                                <td style="text-align: center; font-weight: bold;"><?= htmlspecialchars($pr['no_risiko']); ?></td>
                                <td><?= htmlspecialchars($pr['pernyataan_risiko']); ?></td>
                                <td><?= htmlspecialchars($pr['indikator_target'] ?: '-'); ?></td>
                                <td style="text-align: center;"><?= $pr['dampak'] ?: '-'; ?></td>
                                <td style="text-align: center;"><?= $pr['probabilitas'] ?: '-'; ?></td>
                                <td style="text-align: center; font-weight: bold;"><?= $pr['skor'] ?: '-'; ?></td>
                                <td style="text-align: center;"><?= $lvlBadge; ?></td>
                                <td><?= htmlspecialchars($pr['pengendalian_diklaim'] ?: '-'); ?></td>
                            </tr>
                        <?php endforeach; if(empty($persiapanRisikoRows)): ?>
                            <tr>
                                <td colspan="9" style="text-align: center; color: #64748b;">Belum ada data persiapan risiko.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
                <div class="page-number-footer">6</div>
            </div>

            <div class="document-page landscape arabic">
                <h3 class="doc-heading-2">B. Lembar Kerja Kerja 2 (LK2 Digital) & Temuan PLOR (Tahap 6)</h3>
                <p class="doc-paragraph">Hasil telaah mendalam terhadap risiko potensial, akar penyebab, skor risiko akhir, dan rekomendasi mitigasi:</p>
                <table class="table-doc" style="font-size: 8.5pt;">
                    <thead>
                        <tr>
                            <th style="width: 4%;">No</th>
                            <th style="width: 8%;">No Risiko</th>
                            <th style="width: 20%;">Pernyataan Risiko & Standar</th>
                            <th style="width: 10%;">Kategori</th>
                            <th style="width: 22%;">PLOR</th>
                            <th style="width: 14%;">Analisis (Akar & Risiko)</th>
                            <th style="width: 9%; text-align: center;">Residual (Skor)</th>
                            <th style="width: 13%;">Rekomendasi Mitigasi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($persiapanRisikoRows as $idx => $pr): 
                            $noR = $pr['no_risiko'];
                            $lk = $lk2Map[$noR] ?? [];
                            $tp = $lk['tipe_temuan'] ?? '';
                            $tpClass = '';
                            if($tp === 'Positif') $tpClass = 'color: #059669; font-weight: bold;';
                            elseif(stripos($tp,'mayor')!==false) $tpClass = 'color: #dc2626; font-weight: bold;';
                            elseif(stripos($tp,'minor')!==false) $tpClass = 'color: #d97706; font-weight: bold;';
                            elseif(stripos($tp,'ob')!==false) $tpClass = 'color: #2563eb; font-weight: bold;';
                            
                            $plorParts = [];
                            if(!empty($lk['plor_p'])) $plorParts[] = "<strong>P:</strong> ".htmlspecialchars($lk['plor_p']);
                            if(!empty($lk['plor_l'])) $plorParts[] = "<strong>L:</strong> ".htmlspecialchars($lk['plor_l']);
                            if(!empty($lk['plor_o'])) $plorParts[] = "<strong>O:</strong> ".htmlspecialchars($lk['plor_o']);
                            if(!empty($lk['plor_r'])) $plorParts[] = "<strong>R:</strong> ".htmlspecialchars($lk['plor_r']);
                            
                            $analisisParts = [];
                            if(!empty($lk['analisis_ap'])) $analisisParts[] = "<strong>Akar:</strong> ".htmlspecialchars($lk['analisis_ap']);
                            if(!empty($lk['analisis_rp'])) $analisisParts[] = "<strong>Risiko:</strong> ".htmlspecialchars($lk['analisis_rp']);
                            
                            $rekomParts = [];
                            if(!empty($lk['rekomendasi_rtm'])) $rekomParts[] = "<strong>RTM:</strong> ".htmlspecialchars($lk['rekomendasi_rtm']);
                            if(!empty($lk['rekomendasi_ptk'])) $rekomParts[] = "<strong>PTK:</strong> ".htmlspecialchars($lk['rekomendasi_ptk']);
                        ?>
                            <tr>
                                <td style="text-align: center;"><?= $idx + 1; ?></td>
                                <td style="text-align: center; font-weight: bold;"><?= htmlspecialchars($noR); ?></td>
                                <td><?= htmlspecialchars($pr['pernyataan_risiko']); ?></td>
                                <td style="text-align: center; <?= $tpClass; ?>"><?= htmlspecialchars($tp ?: '-'); ?></td>
                                <td><?= count($plorParts) > 0 ? implode('<br>', $plorParts) : '-'; ?></td>
                                <td><?= count($analisisParts) > 0 ? implode('<br>', $analisisParts) : '-'; ?></td>
                                <td style="text-align: center;">
                                    <strong><?= $lk['skor_res'] ?? '-'; ?></strong><br>
                                    <small><?= htmlspecialchars($lk['level_res'] ?? ''); ?></small>
                                </td>
                                <td><?= count($rekomParts) > 0 ? implode('<br>', $rekomParts) : '-'; ?></td>
                            </tr>
                        <?php endforeach; if(empty($persiapanRisikoRows)): ?>
                            <tr>
                                <td colspan="8" style="text-align: center; color: #64748b;">Belum ada data LK2.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
                <div class="page-number-footer">7</div>
            </div>

            <div class="document-page landscape arabic">
                <h3 class="doc-heading-2">C. Triangulasi Temuan Audit (Tahap 7)</h3>
                <p class="doc-paragraph">Verifikasi triangulasi silang antara bukti dokumen, wawancara auditi, dan observasi lapangan:</p>
                <table class="table-doc" style="font-size: 9pt;">
                    <thead>
                        <tr>
                            <th style="width: 4%;">No</th>
                            <th style="width: 8%;">No Risiko</th>
                            <th style="width: 25%;">Pernyataan Risiko & Standar</th>
                            <th style="width: 15%;">Sumber 1 (Dokumen)</th>
                            <th style="width: 15%;">Sumber 2 (Wawancara)</th>
                            <th style="width: 15%;">Sumber 3 (Observasi)</th>
                            <th style="width: 18%;">Kesimpulan & Kategori</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($persiapanRisikoRows as $idx => $pr): 
                            $noR = $pr['no_risiko'];
                            $tr = $triMap[$noR] ?? [];
                            
                            $dokParts = [];
                            if(!empty($tr['dokumen_temuan'])) $dokParts[] = "T: ".htmlspecialchars($tr['dokumen_temuan']);
                            if(!empty($tr['dokumen_klaim'])) $dokParts[] = "K: ".htmlspecialchars($tr['dokumen_klaim']);
                            if(!empty($tr['dokumen_konsisten'])) $dokParts[] = "Konsistensi: <strong>".htmlspecialchars($tr['dokumen_konsisten'])."</strong>";
                            
                            $wawParts = [];
                            if(!empty($tr['wawancara_temuan'])) $wawParts[] = "T: ".htmlspecialchars($tr['wawancara_temuan']);
                            if(!empty($tr['wawancara_klaim'])) $wawParts[] = "K: ".htmlspecialchars($tr['wawancara_klaim']);
                            if(!empty($tr['wawancara_konsisten'])) $wawParts[] = "Konsistensi: <strong>".htmlspecialchars($tr['wawancara_konsisten'])."</strong>";
                            
                            $obsParts = [];
                            if(!empty($tr['observasi_temuan'])) $obsParts[] = "T: ".htmlspecialchars($tr['observasi_temuan']);
                            if(!empty($tr['observasi_klaim'])) $obsParts[] = "K: ".htmlspecialchars($tr['observasi_klaim']);
                            if(!empty($tr['observasi_konsisten'])) $obsParts[] = "Konsistensi: <strong>".htmlspecialchars($tr['observasi_konsisten'])."</strong>";
                        ?>
                            <tr>
                                <td style="text-align: center;"><?= $idx + 1; ?></td>
                                <td style="text-align: center; font-weight: bold;"><?= htmlspecialchars($noR); ?></td>
                                <td><?= htmlspecialchars($pr['pernyataan_risiko']); ?></td>
                                <td><?= count($dokParts) > 0 ? implode('<br>', $dokParts) : '-'; ?></td>
                                <td><?= count($wawParts) > 0 ? implode('<br>', $wawParts) : '-'; ?></td>
                                <td><?= count($obsParts) > 0 ? implode('<br>', $obsParts) : '-'; ?></td>
                                <td>
                                    <strong><?= htmlspecialchars(($tr['kesimpulan'] ?? '') ?: '-'); ?></strong>
                                    <div style="font-size:0.85rem; color:#64748b; margin-top:3px;">
                                        Kategori: <?= htmlspecialchars(($tr['kategori_temuan'] ?? '') ?: '-'); ?><br>
                                        Validitas: <?= htmlspecialchars(($tr['validitas'] ?? '') ?: '-'); ?><br>
                                        Residual: <?= htmlspecialchars(($tr['risiko_residual'] ?? '') ?: '-'); ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; if(empty($persiapanRisikoRows)): ?>
                            <tr>
                                <td colspan="7" style="text-align: center; color: #64748b;">Belum ada data triangulasi.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
                <div class="page-number-footer">8</div>
            </div>

            <div class="document-page landscape arabic">
                <h3 class="doc-heading-2">D. Permintaan Tindakan Koreksi (PTK A) (Tahap 8)</h3>
                <p class="doc-paragraph">Ringkasan kesepakatan permintaan tindakan koreksi dan jadwal penyelesaian perbaikan oleh auditi:</p>
                <table class="table-doc" style="font-size: 8.5pt;">
                    <thead>
                        <tr>
                            <th style="width: 4%;">No</th>
                            <th style="width: 8%;">No Risiko</th>
                            <th style="width: 25%;">Form A (Identitas Temuan)</th>
                            <th style="width: 35%;">Form B (Rencana Tindakan Korektif)</th>
                            <th style="width: 14%;">Form C (Monitoring Progres)</th>
                            <th style="width: 14%;">Form D (Tinjauan Efektivitas)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($persiapanRisikoRows as $idx => $pr): 
                            $noR = $pr['no_risiko'];
                            $ptk = $ptkMap[$noR] ?? [];
                            $ptkId = $ptk['id'] ?? null;
                            $tList = $ptkId ? ($tindakanMap[$ptkId] ?? []) : [];
                            
                            $formAParts = [];
                            if(!empty($ptk['ptk_no'])) $formAParts[] = "<strong>No PTK:</strong> ".htmlspecialchars($ptk['ptk_no']);
                            if(!empty($ptk['ringkasan_temuan'])) $formAParts[] = "<strong>Temuan:</strong> ".htmlspecialchars($ptk['ringkasan_temuan']);
                            if(!empty($ptk['konsekuensi'])) $formAParts[] = "<strong>Konsekuensi:</strong> ".htmlspecialchars($ptk['konsekuensi']);
                            if(!empty($ptk['akar_masalah'])) $formAParts[] = "<strong>Akar:</strong> ".htmlspecialchars($ptk['akar_masalah']);
                            if(!empty($ptk['tenggat_maks'])) $formAParts[] = "<strong>Tenggat:</strong> ".htmlspecialchars($ptk['tenggat_maks']);
                            
                            $formBParts = [];
                            if(count($tList) > 0) {
                                foreach($tList as $ti => $td) {
                                    $formBParts[] = ($ti+1).". ".htmlspecialchars($td['tindakan'])." (PJ: ".htmlspecialchars($td['pj'] ?? '').", Tenggat: ".htmlspecialchars($td['tenggat'] ?? '').", Selesai: ".htmlspecialchars($td['tgl_selesai'] ?? '').")";
                                }
                            }
                            
                            $formCParts = [];
                            if($ptkId) {
                                $bulans = [];
                                for($b=1;$b<=5;$b++) {
                                    if(!empty($ptk['bulan'.$b.'_status'])) {
                                        $bulans[] = "B$b: ".htmlspecialchars($ptk['bulan'.$b.'_status']);
                                    }
                                }
                                if(count($bulans) > 0) $formCParts[] = implode(', ', $bulans);
                                if(!empty($ptk['catatan_lpm'])) $formCParts[] = "<strong>LPM:</strong> ".htmlspecialchars($ptk['catatan_lpm']);
                            }
                            
                            $formDParts = [];
                            if(!empty($ptk['efektivitas'])) $formDParts[] = "<strong>Status:</strong> ".htmlspecialchars($ptk['efektivitas']);
                            if(!empty($ptk['tenggat_baru'])) $formDParts[] = "<strong>Tenggat Baru:</strong> ".htmlspecialchars($ptk['tenggat_baru']);
                            if(!empty($ptk['ref_lk_verifikasi'])) $formDParts[] = "<strong>Ref LK:</strong> ".htmlspecialchars($ptk['ref_lk_verifikasi']);
                            if(!empty($ptk['auditor_verifikasi'])) $formDParts[] = "<strong>Verifikator:</strong> ".htmlspecialchars($ptk['auditor_verifikasi']);
                        ?>
                            <tr>
                                <td style="text-align: center;"><?= $idx + 1; ?></td>
                                <td style="text-align: center; font-weight: bold;"><?= htmlspecialchars($noR); ?></td>
                                <td><?= count($formAParts) > 0 ? implode('<br>', $formAParts) : '-'; ?></td>
                                <td><?= count($formBParts) > 0 ? implode('<br>', $formBParts) : '-'; ?></td>
                                <td><?= count($formCParts) > 0 ? implode('<br>', $formCParts) : '-'; ?></td>
                                <td><?= count($formDParts) > 0 ? implode('<br>', $formDParts) : '-'; ?></td>
                            </tr>
                        <?php endforeach; if(empty($persiapanRisikoRows)): ?>
                            <tr>
                                <td colspan="6" style="text-align: center; color: #64748b;">Belum ada data PTK.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
                <div class="page-number-footer">9</div>
            </div>

            <!-- BAB IV KESIMPULAN DAN REKOMENDASI MANAJEMEN RISIKO -->
            <div class="document-page arabic">
                <h2 class="doc-heading-1">BAB IV<br>KESIMPULAN DAN REKOMENDASI RISIKO</h2>
                <h3 class="doc-heading-2">A. Kesimpulan Audit Berbasis Risiko</h3>
                <p class="doc-paragraph"><?= !empty($lk2Kesimpulan['kesimpulan']) ? nl2br(htmlspecialchars($lk2Kesimpulan['kesimpulan'])) : 'Pelaksanaan audit mutu internal berbasis manajemen risiko pada ' . $label_prodi . htmlspecialchars($audit['auditi']) . ' telah berjalan dengan lancar. Seluruh potensi risiko telah diidentifikasi dan dinilai skoring dampaknya guna mencegah terjadinya ketidaksesuaian mutu di masa mendatang.'; ?></p>

                <h3 class="doc-heading-2">B. Rekomendasi Umum</h3>
                <p class="doc-paragraph"><?= !empty($lk2Kesimpulan['rekomendasi']) ? nl2br(htmlspecialchars($lk2Kesimpulan['rekomendasi'])) : 'Pihak ' . $label_prodi_lower . ' disarankan agar memprioritaskan tindakan mitigasi pada temuan dengan skor risiko tinggi dan menengah sesuai target waktu yang disepakati pada Lembar PTK A.'; ?></p>

                <div class="signature-block" style="margin-top: 2.5cm;">
                    <div class="signature-box">
                        <?= $label_kaprodi; ?>
                        <?= audit_signature_image('auditi'); ?>
                        <strong>( <?= audit_signature_name('auditi', $audit['kaunit'] ?: '........................'); ?> )</strong>
                    </div>
                    <div class="signature-box">
                        Ketua Tim Auditor AMI
                        <?= audit_signature_image('auditor'); ?>
                        <strong>( <?= audit_signature_name('auditor', $ketuaaud ?: '........................'); ?> )</strong>
                    </div>
                </div>
                <div class="page-number-footer">10</div>
            </div>
            <?php endif; ?>

            <!-- PAGE: LAMPIRAN DOKUMENTASI AUDIT (TAHAP 4) -->
            <div class="document-page arabic">
                <h2 class="doc-heading-1">LAMPIRAN DOKUMENTASI AUDIT</h2>
                <h3 class="doc-heading-2" style="text-align: center;">Foto Kegiatan & Daftar Hadir (Tahap 4)</h3>
                <div style="margin-top: 1cm;">
                    <?php if($gk_dok && (!empty($gk_dok['dokoc']) || !empty($gk_dok['dokpa']) || !empty($gk_dok['dokcc']) || !empty($gk_dok['dokabsen']))): ?>
                        <table style="width: 100%; border: none;">
                            <tr>
                                <td style="width: 50%; padding: 10px; text-align: center; border: none; vertical-align: top;">
                                    <div style="font-weight: bold; margin-bottom: 8px;">Opening Meeting</div>
                                    <?php if(!empty($gk_dok['dokoc']) && file_exists("../assets/auditdok/".$ka."_".$gk_dok['dokoc'])): ?>
                                        <img src="../assets/auditdok/<?= htmlspecialchars($ka.'_'.$gk_dok['dokoc']); ?>" style="width: 7.5cm; height: 5.5cm; object-fit: cover; border: 1px solid #ccc; border-radius: 4px;" alt="Opening">
                                    <?php else: ?>
                                        <div style="width: 7.5cm; height: 5.5cm; border: 1px dashed #999; margin: 0 auto; display: flex; align-items: center; justify-content: center; color: #666; font-size: 9pt;">Belum ada foto Opening Meeting</div>
                                    <?php endif; ?>
                                </td>
                                <td style="width: 50%; padding: 10px; text-align: center; border: none; vertical-align: top;">
                                    <div style="font-weight: bold; margin-bottom: 8px;">Pelaksanaan Audit</div>
                                    <?php if(!empty($gk_dok['dokpa']) && file_exists("../assets/auditdok/".$ka."_".$gk_dok['dokpa'])): ?>
                                        <img src="../assets/auditdok/<?= htmlspecialchars($ka.'_'.$gk_dok['dokpa']); ?>" style="width: 7.5cm; height: 5.5cm; object-fit: cover; border: 1px solid #ccc; border-radius: 4px;" alt="Pelaksanaan">
                                    <?php else: ?>
                                        <div style="width: 7.5cm; height: 5.5cm; border: 1px dashed #999; margin: 0 auto; display: flex; align-items: center; justify-content: center; color: #666; font-size: 9pt;">Belum ada foto Pelaksanaan Audit</div>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <td style="width: 50%; padding: 10px; text-align: center; border: none; vertical-align: top;">
                                    <div style="font-weight: bold; margin-bottom: 8px;">Closing Meeting</div>
                                    <?php if(!empty($gk_dok['dokcc']) && file_exists("../assets/auditdok/".$ka."_".$gk_dok['dokcc'])): ?>
                                        <img src="../assets/auditdok/<?= htmlspecialchars($ka.'_'.$gk_dok['dokcc']); ?>" style="width: 7.5cm; height: 5.5cm; object-fit: cover; border: 1px solid #ccc; border-radius: 4px;" alt="Closing">
                                    <?php else: ?>
                                        <div style="width: 7.5cm; height: 5.5cm; border: 1px dashed #999; margin: 0 auto; display: flex; align-items: center; justify-content: center; color: #666; font-size: 9pt;">Belum ada foto Closing Meeting</div>
                                    <?php endif; ?>
                                </td>
                                <td style="width: 50%; padding: 10px; text-align: center; border: none; vertical-align: top;">
                                    <div style="font-weight: bold; margin-bottom: 8px;">Daftar Hadir / Absensi</div>
                                    <?php if(!empty($gk_dok['dokabsen']) && file_exists("../assets/auditdok/".$ka."_".$gk_dok['dokabsen'])): ?>
                                        <img src="../assets/auditdok/<?= htmlspecialchars($ka.'_'.$gk_dok['dokabsen']); ?>" style="width: 7.5cm; height: 5.5cm; object-fit: cover; border: 1px solid #ccc; border-radius: 4px;" alt="Absensi">
                                    <?php else: ?>
                                        <div style="width: 7.5cm; height: 5.5cm; border: 1px dashed #999; margin: 0 auto; display: flex; align-items: center; justify-content: center; color: #666; font-size: 9pt;">Belum ada Daftar Hadir</div>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        </table>
                    <?php else: ?>
                        <div style="padding: 2cm; text-align: center; border: 1px dashed #ccc; border-radius: 8px; color: #64748b;">
                            <i class="fas fa-camera fa-2x mb-2 d-block"></i>
                            Belum ada lampiran dokumentasi audit yang diunggah pada Tahap 4.
                        </div>
                    <?php endif; ?>
                </div>
                <div class="page-number-footer"><?= $versi === '2' ? '11' : '9'; ?></div>
            </div>
        </div>
    </div>
</div>
<?php include 'foot.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>
<script>
function initSignaturePads() {
    document.querySelectorAll('.signature-form').forEach(function(form) {
        if(form.dataset.signatureReady === '1') return;
        form.dataset.signatureReady = '1';
        var canvas = form.querySelector('.signature-pad');
        var ctx = canvas.getContext('2d');
        var signatureData = form.querySelector('.signature-data');
        var drawing = false;
        var hasInk = false;

        function resizeCanvas() {
            var ratio = Math.max(window.devicePixelRatio || 1, 1);
            var rect = canvas.getBoundingClientRect();
            var width = rect.width || canvas.parentElement.clientWidth || 500;
            var height = rect.height || 170;
            canvas.width = width * ratio;
            canvas.height = height * ratio;
            ctx.setTransform(ratio, 0, 0, ratio, 0, 0);
            ctx.lineWidth = 2;
            ctx.lineCap = 'round';
            ctx.strokeStyle = '#111827';
        }
        function point(event) {
            var rect = canvas.getBoundingClientRect();
            var e = event.touches ? event.touches[0] : event;
            return { x: e.clientX - rect.left, y: e.clientY - rect.top };
        }
        function start(event) {
            event.preventDefault();
            drawing = true;
            hasInk = true;
            var p = point(event);
            ctx.beginPath();
            ctx.moveTo(p.x, p.y);
        }
        function move(event) {
            if(!drawing) return;
            event.preventDefault();
            var p = point(event);
            ctx.lineTo(p.x, p.y);
            ctx.stroke();
        }
        function stop() { drawing = false; }

        resizeCanvas();
        form._resizeSignaturePad = resizeCanvas;
        window.addEventListener('resize', resizeCanvas);
        canvas.addEventListener('mousedown', start);
        canvas.addEventListener('mousemove', move);
        window.addEventListener('mouseup', stop);
        canvas.addEventListener('touchstart', start, { passive: false });
        canvas.addEventListener('touchmove', move, { passive: false });
        canvas.addEventListener('touchend', stop);

        form.querySelector('.signature-clear').addEventListener('click', function() {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            signatureData.value = '';
            hasInk = false;
        });
        form.querySelector('.signature-upload').addEventListener('change', function(event) {
            var file = event.target.files[0];
            if(!file) return;
            var reader = new FileReader();
            reader.onload = function(loadEvent) {
                var img = new Image();
                img.onload = function() {
                    var rect = canvas.getBoundingClientRect();
                    ctx.clearRect(0, 0, rect.width, rect.height);
                    var scale = Math.min(rect.width / img.width, rect.height / img.height);
                    var width = img.width * scale;
                    var height = img.height * scale;
                    ctx.drawImage(img, (rect.width - width) / 2, (rect.height - height) / 2, width, height);
                    signatureData.value = canvas.toDataURL('image/png');
                    hasInk = true;
                };
                img.src = loadEvent.target.result;
            };
            reader.readAsDataURL(file);
        });
        form.addEventListener('submit', function(event) {
            event.preventDefault();
            if(!hasInk) {
                alert('Silakan gambar tanda tangan atau upload gambar tanda tangan terlebih dahulu.');
                return;
            }
            if(!signatureData.value) {
                signatureData.value = canvas.toDataURL('image/png');
            }
            var $form = $(form);
            var pihak = $form.find('input[name="pihak"]').val();
            var namaValue = $form.find('input[name="nama"]').val();
            var sigValue = signatureData.value;
            $.ajax({
                url: window.location.href,
                type: 'POST',
                data: $form.serialize(),
                dataType: 'json',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                success: function(res) {
                    if (res && res.status === 'success') {
                        $form.find('.signature-preview').html('<img src="' + sigValue + '" alt="Tanda tangan tersimpan">');
                        $('[data-pihak-sig-img="' + pihak + '"]').replaceWith('<img class="report-sign-image" src="' + sigValue + '" alt="Tanda tangan" data-pihak-sig-img="' + pihak + '">');
                        $('[data-pihak-sig-name="' + pihak + '"]').text(namaValue);
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({ icon: 'success', title: 'Berhasil', text: 'Tanda tangan berhasil disimpan.', timer: 1500, showConfirmButton: false });
                        } else {
                            alert('Tanda tangan berhasil disimpan.');
                        }
                    } else {
                        alert(res.message || 'Gagal menyimpan tanda tangan.');
                    }
                },
                error: function() {
                    alert('Terjadi kesalahan saat menyimpan tanda tangan.');
                }
            });
        });
    });
}
initSignaturePads();
document.querySelectorAll('#signatureToolsBody').forEach(function(panel) {
    panel.addEventListener('shown.bs.collapse', function() {
        document.querySelectorAll('.signature-form').forEach(function(form) {
            if(typeof form._resizeSignaturePad === 'function') {
                form._resizeSignaturePad();
            }
        });
    });
});

$(document).ready(function() {
    var autosaveTimers = {};
    
    function triggerAutosave(subbabKey, content) {
        $('#autosaveIndicator').fadeIn().removeClass('bg-success text-white').addClass('bg-warning text-dark').html('<i class="fas fa-spinner fa-spin me-1"></i> Menyimpan...');
        
        clearTimeout(autosaveTimers[subbabKey]);
        autosaveTimers[subbabKey] = setTimeout(function() {
            $.ajax({
                url: window.location.href,
                type: 'POST',
                data: {
                    action: 'save_narasi',
                    kodeaudit: '<?= urlencode($ka); ?>',
                    subbab: subbabKey,
                    konten: content
                },
                dataType: 'json',
                success: function(response) {
                    if(response && response.status === 'success') {
                        $('#autosaveIndicator').removeClass('bg-warning text-dark').addClass('bg-success text-white').html('<i class="fas fa-check-circle me-1"></i> Tersimpan Otomatis');
                    } else {
                        alert('Gagal menyimpan narasi: ' + (response.message || 'Error'));
                    }
                },
                error: function() {
                    $('#autosaveIndicator').removeClass('bg-warning text-dark').addClass('bg-danger text-white').html('<i class="fas fa-exclamation-circle me-1"></i> Gagal Tersimpan');
                }
            });
        }, 1200);
    }

    window.printReport = function() {
        if ($('#toggleEditMode').is(':checked')) {
            $('#toggleEditMode').prop('checked', false).trigger('change');
        }
        setTimeout(function() {
            window.print();
        }, 300);
    };

    window.exportToWord = function(filename = 'Laporan_AMI') {
        if ($('#toggleEditMode').is(':checked')) {
            $('#toggleEditMode').prop('checked', false).trigger('change');
        }
        setTimeout(function() {
            var msoStyles = "<style>" +
                "@page WordSection1 { size: 21cm 29.7cm; margin: 2cm 2.5cm 2.5cm 2.5cm; mso-header-margin: 1cm; mso-footer-margin: 1cm; }" +
                "div.WordSection1 { page: WordSection1; }" +
                "body { font-family: 'Times New Roman', serif; font-size: 12pt; line-height: 1.5; color: #000; }" +
                ".document-page { page-break-after: always; clear: both; padding: 0 !important; margin: 0 !important; box-shadow: none !important; width: 100% !important; }" +
                ".document-page:last-child { page-break-after: avoid; }" +
                "table { border-collapse: collapse; width: 100%; margin-top: 12pt; margin-bottom: 12pt; }" +
                "th, td { border: 1pt solid black; padding: 6pt; vertical-align: top; }" +
                ".doc-heading-1 { font-size: 14pt; font-weight: bold; text-align: center; margin-top: 4pt; margin-bottom: 12pt; text-transform: uppercase; }" +
                ".doc-heading-2 { font-size: 12pt; font-weight: bold; margin-top: 14pt; margin-bottom: 6pt; }" +
                "p, .doc-paragraph { text-align: justify; text-indent: 1.25cm; margin-bottom: 6pt; line-height: 1.5; }" +
                "</style>";

            var preHtml = "<html xmlns:o='urn:schemas-microsoft-com:office:office' xmlns:w='urn:schemas-microsoft-com:office:word' xmlns='http://www.w3.org/TR/REC-html40'><head><meta charset='utf-8'><title>Laporan AMI</title>" + msoStyles + "</head><body><div class='WordSection1'>";
            var postHtml = "</div></body></html>";
            
            var clone = $('.doc-container').clone();
            clone.find('.no-print, .page-number-footer, .subbab-edit-badge').remove();
            
            var html = preHtml + clone.html() + postHtml;
            var blob = new Blob(['\ufeff', html], { type: 'application/msword' });
            
            filename = filename.replace(/[^a-zA-Z0-9_\-]/g, '_') + '.doc';
            
            var downloadLink = document.createElement("a");
            document.body.appendChild(downloadLink);
            
            if (navigator.msSaveOrOpenBlob) {
                navigator.msSaveOrOpenBlob(blob, filename);
            } else {
                downloadLink.href = URL.createObjectURL(blob);
                downloadLink.download = filename;
                downloadLink.click();
            }
            document.body.removeChild(downloadLink);
        }, 350);
    };

    $('#toggleEditMode').on('change', function() {
        if ($(this).is(':checked')) {
            $('#autosaveIndicator').fadeIn().removeClass('bg-warning bg-danger text-dark').addClass('bg-success text-white').html('<i class="fas fa-edit me-1"></i> Mode Edit Aktif');
            $('.editable-subbab').each(function() {
                var subbabKey = $(this).data('subbab');
                var titleText = $(this).data('title') || 'Subbab';
                
                if ($(this).prev('.subbab-edit-badge').length === 0) {
                    $('<div class="subbab-edit-badge badge bg-primary mb-1 no-print" style="font-size:0.8rem;"><i class="fas fa-pen me-1"></i> Edit: ' + titleText + '</div>').insertBefore($(this));
                }
                $(this).prev('.subbab-edit-badge').show();

                $(this).summernote({
                    placeholder: 'Tulis narasi untuk ' + titleText + '...',
                    tabsize: 2,
                    height: 180,
                    toolbar: [
                        ['style', ['style']],
                        ['font', ['bold', 'underline', 'italic', 'clear']],
                        ['color', ['color']],
                        ['para', ['ul', 'ol', 'paragraph']],
                        ['table', ['table']],
                        ['view', ['fullscreen', 'codeview']]
                    ],
                    callbacks: {
                        onChange: function(contents, $editable) {
                            triggerAutosave(subbabKey, contents);
                        }
                    }
                });
            });
        } else {
            $('.editable-subbab').each(function() {
                if ($(this).hasClass('note-editable') || $(this).next().hasClass('note-editor')) {
                    var currentMarkup = $(this).summernote('code');
                    $(this).summernote('destroy');
                    $(this).html(currentMarkup);
                }
                $(this).prev('.subbab-edit-badge').hide();
            });
            $('#autosaveIndicator').removeClass('bg-warning bg-danger text-dark').addClass('bg-success text-white').html('<i class="fas fa-check-circle me-1"></i> Mode Baca');
            setTimeout(function() { $('#autosaveIndicator').fadeOut(); }, 2000);
        }
    });

    <?php if($is_auditor_access && isset($_GET['edit']) && $_GET['edit'] == 1): ?>
        $('#toggleEditMode').prop('checked', true).trigger('change');
    <?php endif; ?>
});
</script>
</body>
</html>
<?php exit; endif; ?>
<?php
$lingkup = (int)$audit['lingkup'] === 1 ? 'Akademik' : 'Non-Akademik';
?>
<style>
    .audit-report-shell{background:#f8fafc;padding:1rem;border-radius:1rem}
    .audit-report-hero{background:linear-gradient(135deg,#f8fafc 0%,#eef6ff 100%);border:1px solid #dbeafe;border-radius:1rem;padding:1.25rem;box-shadow:0 18px 40px rgba(15,23,42,.06)}
    .audit-report-kicker{font-size:.76rem;font-weight:900;color:#2563eb;text-transform:uppercase;letter-spacing:0}
    .audit-report-hero h3{font-weight:900;color:#0f172a}
    .audit-report-meta{display:flex;flex-wrap:wrap;gap:.45rem;margin-top:.75rem}
    .audit-report-stat{background:#fff;border:1px solid #e5e7eb;border-radius:.85rem;padding:1rem;box-shadow:0 12px 26px rgba(15,23,42,.05);height:100%}
    .audit-report-stat .label{font-size:.74rem;font-weight:900;color:#64748b;text-transform:uppercase}
    .audit-report-stat .value{font-weight:900;color:#0f172a;margin-top:.25rem}
    .audit-report-section{background:#fff;border:1px solid #e5e7eb;border-radius:1rem;box-shadow:0 14px 32px rgba(15,23,42,.06);overflow:hidden;margin-top:1rem}
    .audit-report-section-head{display:flex;align-items:center;justify-content:space-between;gap:1rem;padding:1rem 1.1rem;background:#fbfdff;border-bottom:1px solid #e5e7eb}
    .audit-report-section-head h5{font-weight:900;color:#0f172a;margin:0}
    .audit-report-section-head p{font-size:.84rem;color:#64748b;margin:.2rem 0 0}
    .audit-report-section-body{padding:1rem}
    .audit-info-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:.75rem}
    .audit-info-cell{border:1px solid #e2e8f0;border-radius:.75rem;background:#f8fafc;padding:.8rem}
    .audit-info-label{font-size:.72rem;font-weight:900;color:#64748b;text-transform:uppercase;margin-bottom:.25rem}
    .audit-info-value{font-weight:800;color:#0f172a}
    .audit-note-list,.audit-finding-list{display:grid;gap:.8rem}
    .audit-note-card,.audit-finding-card{border:1px solid #e2e8f0;border-radius:.85rem;background:#fff;padding:1rem}
    .audit-card-top{display:flex;align-items:flex-start;justify-content:space-between;gap:.75rem;margin-bottom:.75rem}
    .audit-card-title{font-weight:900;color:#0f172a}
    .audit-pill{display:inline-flex;align-items:center;border-radius:999px;border:1px solid #e2e8f0;background:#f8fafc;color:#475569;padding:.24rem .55rem;font-size:.75rem;font-weight:900}
    .audit-content-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:.75rem}
    .audit-content-block{background:#f8fafc;border:1px solid #e2e8f0;border-radius:.75rem;padding:.75rem}
    .audit-signature{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:1rem;margin-top:1rem}
    .audit-sign-box{text-align:center;border-top:1px dashed #cbd5e1;padding-top:.8rem;color:#0f172a}
    .signature-pad-wrap{border:1px solid #dbe3ef;border-radius:.75rem;background:#f8fafc;padding:1rem}
    .signature-pad{width:100%;height:170px;border:1px dashed #94a3b8;border-radius:.5rem;background:#fff;touch-action:none}
    .signature-preview{height:88px;display:flex;align-items:center;justify-content:center;border:1px solid #e2e8f0;border-radius:.5rem;background:#fff;margin-bottom:.75rem}
    .signature-preview img{max-width:220px;max-height:78px;object-fit:contain}
    .report-sign-space{height:70px;margin:8px 0}
    .report-sign-image{display:block;max-width:190px;max-height:70px;object-fit:contain;margin:8px auto}
    @media(max-width:991.98px){.audit-info-grid,.audit-content-grid{grid-template-columns:1fr}.audit-report-section-head{flex-direction:column;align-items:flex-start}}
    @media(max-width:575.98px){.audit-report-shell{padding:.65rem}.audit-signature{grid-template-columns:1fr}}
    @media print{.burger-btn,.btn,#sidebar,.no-print{display:none!important}.audit-report-shell{background:#fff;padding:0}.audit-report-section{box-shadow:none;break-inside:avoid}}
</style>
<div id="main">
    <header>
        <a href="#" class="burger-btn d-block p-2"><i class="fas fa-bars"></i> Menu</a>
    </header>
    <div class="page-content">
        <section class="row">
            <div class="col-12">
                <!-- Layout Mode Toggle Tab -->
                <div class="no-print mb-4">
                    <div class="card shadow-sm border p-3" style="background: white; border-radius: 8px;">
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                            <div>
                                <h5 class="mb-1 font-bold text-primary" style="margin: 0; font-weight: bold; color: #2563eb;">Laporan Hasil Audit Mutu Internal (AMI) <?= $versi === '2' ? '- Berbasis Manajemen Risiko' : '- Standar'; ?></h5>
                                <p class="text-muted mb-0 small" style="margin: 0; color: #64748b; font-size: 0.85rem;">Pilih format tampilan laporan atau ganti versi laporan di bawah ini.</p>
                            </div>
                            <div class="d-flex flex-wrap gap-2">
                                <a href="audit.laporan?ka=<?= urlencode($ka); ?>&et=<?= $et; ?>&v=<?= $versi; ?>" class="btn btn-sm btn-primary font-semibold" style="background: #2563eb; border: 1px solid #2563eb; color: white; padding: 6px 12px; border-radius: 4px; text-decoration: none; font-size: 0.85rem;">
                                    <i class="fas fa-chart-bar me-1"></i> Tampilan Web
                                </a>
                                <a href="audit.laporan?ka=<?= urlencode($ka); ?>&et=<?= $et; ?>&doc=1&v=<?= $versi; ?>" class="btn btn-sm btn-outline-primary font-semibold" style="border: 1px solid #2563eb; color: #2563eb; background: transparent; padding: 6px 12px; border-radius: 4px; text-decoration: none; font-size: 0.85rem;">
                                    <i class="fas fa-file-alt me-1"></i> Format Dokumen A4
                                </a>
                                <?php if ($is_auditor_access): ?>
                                <a href="audit.laporan?ka=<?= urlencode($ka); ?>&et=<?= $et; ?>&doc=1&edit=1&v=<?= $versi; ?>" class="btn btn-sm btn-warning font-semibold text-dark" style="background: #f59e0b; border: 1px solid #f59e0b; color: #0f172a; padding: 6px 12px; border-radius: 4px; text-decoration: none; font-size: 0.85rem; font-weight: bold;">
                                    <i class="fas fa-edit me-1"></i> Edit Narasi Laporan
                                </a>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="mt-3 pt-3 border-top d-flex flex-wrap align-items-center justify-content-between gap-2">
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <span class="text-muted small fw-bold me-1"><i class="fas fa-layer-group text-primary me-1"></i> Versi Laporan:</span>
                                <a href="audit.laporan?ka=<?= urlencode($ka); ?>&et=<?= $et; ?>&v=1" class="btn btn-sm <?= $versi === '1' ? 'btn-primary text-white fw-bold shadow-sm' : 'btn-light border text-dark'; ?>" style="border-radius: 6px; font-size: 0.85rem;">
                                    <i class="fas fa-file-alt me-1"></i> Versi 1: Laporan AMI Standar (Tahap 1-4)
                                </a>
                                <a href="audit.laporan?ka=<?= urlencode($ka); ?>&et=<?= $et; ?>&v=2" class="btn btn-sm <?= $versi === '2' ? 'btn-primary text-white fw-bold shadow-sm' : 'btn-light border text-dark'; ?>" style="border-radius: 6px; font-size: 0.85rem;">
                                    <i class="fas fa-shield-alt me-1"></i> Versi 2: Berbasis Manajemen Risiko (Tahap 5-8 + Dok)
                                </a>
                            </div>
                        </div>
                        </div>
                    </div>
                </div>

                <div class="audit-report-shell">
                    <?= audit_signature_tools($ka, $et, $audit, $ketuaaud, $versi); ?>
                    <div class="audit-report-hero mb-3">
                        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-start gap-3">
                            <div>
                                <div class="audit-report-kicker">Laporan Audit</div>
                                <h3 class="mb-1">Audit #<?= htmlspecialchars($audit['kodeaudit']); ?></h3>
                                <p class="text-muted mb-0"><?= htmlspecialchars($audit['auditi']); ?> - <?= htmlspecialchars($lingkup); ?></p>
                                <div class="audit-report-meta">
                                    <span class="badge bg-primary">AMI</span>
                                    <span class="badge bg-light text-dark border"><?= audit_laporan_date($audit['tglmulai']); ?> s.d <?= audit_laporan_date($audit['tglselesai']); ?></span>
                                    <span class="badge bg-light text-dark border">Auditor: <?= htmlspecialchars($ketuaaud ?: '-'); ?></span>
                                </div>
                            </div>
                            <div class="d-flex flex-wrap gap-2">
                                <a class="btn btn-outline-secondary btn-sm" href="audit.progres?ka=<?= urlencode($audit['kodeaudit']); ?>&et=<?= $et; ?>"><i class="fas fa-arrow-left me-1"></i> Kembali</a>
                                <button onclick="window.print()" class="btn btn-primary btn-sm"><i class="fas fa-print me-1"></i> Print Semua</button>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12 col-md-4 mb-3"><div class="audit-report-stat"><div class="label">Catatan Audit</div><div class="value"><?= count($catatanRows); ?> item</div></div></div>
                        <div class="col-12 col-md-4 mb-3"><div class="audit-report-stat"><div class="label">Kondisi Positif</div><div class="value"><?= count($positifRows); ?> item</div></div></div>
                        <div class="col-12 col-md-4 mb-3"><div class="audit-report-stat"><div class="label">Temuan Audit</div><div class="value"><?= count($temuanRows); ?> item</div></div></div>
                    </div>

                    <section class="audit-report-section" id="ringkasanAudit">
                        <div class="audit-report-section-head">
                            <div>
                                <h5>Ringkasan Audit</h5>
                                <p>Identitas audit dan ruang lingkup pelaksanaan.</p>
                            </div>
                            <button onclick="printDiv('ringkasanAudit')" class="btn btn-outline-primary btn-sm"><i class="fas fa-print me-1"></i> Print</button>
                        </div>
                        <div class="audit-report-section-body">
                            <div class="audit-info-grid">
                                <div class="audit-info-cell"><div class="audit-info-label">Auditi</div><div class="audit-info-value"><?= htmlspecialchars($audit['auditi']); ?></div></div>
                                <div class="audit-info-cell"><div class="audit-info-label">Tipe Audit</div><div class="audit-info-value">AMI</div></div>
                                <div class="audit-info-cell"><div class="audit-info-label">Standar</div><div class="audit-info-value">SPMI Poltekkes Kemenkes Bengkulu</div></div>
                                <div class="audit-info-cell"><div class="audit-info-label">Lokasi</div><div class="audit-info-value"><?= htmlspecialchars($audit['auditi']); ?></div></div>
                                <div class="audit-info-cell"><div class="audit-info-label">Ruang Lingkup</div><div class="audit-info-value"><?= htmlspecialchars($lingkup); ?></div></div>
                                <div class="audit-info-cell"><div class="audit-info-label">Tanggal Audit</div><div class="audit-info-value"><?= audit_laporan_date($audit['tglmulai']); ?> s.d <?= audit_laporan_date($audit['tglselesai']); ?></div></div>
                            </div>
                        </div>
                    </section>

                    <?php if($versi === '2'): ?>
                    <!-- VERSI 2: WEB VIEW SECTIONS -->
                    <section class="audit-report-section" id="rekapRisiko">
                        <div class="audit-report-section-head">
                            <div>
                                <h5>A. Catatan Persiapan & Identifikasi Risiko (Tahap 5)</h5>
                                <p>Daftar identifikasi risiko awal <?= $label_prodi_lower; ?> sebelum pelaksanaan visitasi audit lapangan.</p>
                            </div>
                            <button onclick="printDiv('rekapRisiko')" class="btn btn-outline-primary btn-sm"><i class="fas fa-print me-1"></i> Print</button>
                        </div>
                        <div class="audit-report-section-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped align-middle text-dark mb-0" style="font-size: 0.9rem;">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 5%;" class="text-center">No</th>
                                            <th style="width: 10%;" class="text-center">Kode</th>
                                            <th style="width: 32%;">Pernyataan Standar & Risiko Potensial</th>
                                            <th style="width: 15%;">Indikator & Target</th>
                                            <th style="width: 5%;" class="text-center">D</th>
                                            <th style="width: 5%;" class="text-center">P</th>
                                            <th style="width: 5%;" class="text-center">Skor</th>
                                            <th style="width: 10%;" class="text-center">Level</th>
                                            <th style="width: 13%;">Kondisi Awal / Pengendalian</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach($persiapanRisikoRows as $idx => $pr): 
                                            $lvl = strtoupper($pr['level'] ?? '');
                                            $lvlBadge = '';
                                            if($lvl === 'MERAH') $lvlBadge = '<span class="badge bg-danger">MERAH</span>';
                                            elseif($lvl === 'KUNING') $lvlBadge = '<span class="badge bg-warning text-dark">KUNING</span>';
                                            elseif($lvl === 'HIJAU') $lvlBadge = '<span class="badge bg-success">HIJAU</span>';
                                            else $lvlBadge = '-';
                                        ?>
                                            <tr>
                                                <td class="text-center"><?= $idx + 1; ?></td>
                                                <td class="text-center fw-bold text-primary"><?= htmlspecialchars($pr['no_risiko']); ?></td>
                                                <td><?= htmlspecialchars($pr['pernyataan_risiko']); ?></td>
                                                <td><?= htmlspecialchars($pr['indikator_target'] ?: '-'); ?></td>
                                                <td class="text-center"><?= $pr['dampak'] ?: '-'; ?></td>
                                                <td class="text-center"><?= $pr['probabilitas'] ?: '-'; ?></td>
                                                <td class="text-center fw-bold"><?= $pr['skor'] ?: '-'; ?></td>
                                                <td class="text-center"><?= $lvlBadge; ?></td>
                                                <td><?= htmlspecialchars($pr['pengendalian_diklaim'] ?: '-'); ?></td>
                                            </tr>
                                        <?php endforeach; if(empty($persiapanRisikoRows)): ?>
                                            <tr>
                                                <td colspan="9" class="text-center text-muted">Belum ada data persiapan risiko.</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </section>

                    <section class="audit-report-section" id="lk2Risiko">
                        <div class="audit-report-section-head">
                            <div>
                                <h5>B. Lembar Kerja Kerja 2 (LK2 Digital) & Temuan PLOR (Tahap 6)</h5>
                                <p>Hasil telaah mendalam terhadap risiko potensial, akar penyebab, skor risiko akhir, dan rekomendasi mitigasi.</p>
                            </div>
                            <button onclick="printDiv('lk2Risiko')" class="btn btn-outline-primary btn-sm"><i class="fas fa-print me-1"></i> Print</button>
                        </div>
                        <div class="audit-report-section-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped align-middle text-dark mb-0" style="font-size: 0.85rem;">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 4%;" class="text-center">No</th>
                                            <th style="width: 8%;" class="text-center">No Risiko</th>
                                            <th style="width: 20%;">Pernyataan Risiko & Standar</th>
                                            <th style="width: 10%;" class="text-center">Kategori</th>
                                            <th style="width: 22%;">PLOR</th>
                                            <th style="width: 14%;">Analisis (Akar & Risiko)</th>
                                            <th style="width: 9%;" class="text-center">Residual (Skor)</th>
                                            <th style="width: 13%;">Rekomendasi Mitigasi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach($persiapanRisikoRows as $idx => $pr): 
                                            $noR = $pr['no_risiko'];
                                            $lk = $lk2Map[$noR] ?? [];
                                            $tp = $lk['tipe_temuan'] ?? '';
                                            $tpClass = 'bg-secondary';
                                            if($tp === 'Positif') $tpClass = 'bg-success';
                                            elseif(stripos($tp,'mayor')!==false) $tpClass = 'bg-danger';
                                            elseif(stripos($tp,'minor')!==false) $tpClass = 'bg-warning text-dark';
                                            elseif(stripos($tp,'ob')!==false) $tpClass = 'bg-info text-dark';
                                            
                                            $plorParts = [];
                                            if(!empty($lk['plor_p'])) $plorParts[] = "<strong>P:</strong> ".htmlspecialchars($lk['plor_p']);
                                            if(!empty($lk['plor_l'])) $plorParts[] = "<strong>L:</strong> ".htmlspecialchars($lk['plor_l']);
                                            if(!empty($lk['plor_o'])) $plorParts[] = "<strong>O:</strong> ".htmlspecialchars($lk['plor_o']);
                                            if(!empty($lk['plor_r'])) $plorParts[] = "<strong>R:</strong> ".htmlspecialchars($lk['plor_r']);
                                            
                                            $analisisParts = [];
                                            if(!empty($lk['analisis_ap'])) $analisisParts[] = "<strong>Akar:</strong> ".htmlspecialchars($lk['analisis_ap']);
                                            if(!empty($lk['analisis_rp'])) $analisisParts[] = "<strong>Risiko:</strong> ".htmlspecialchars($lk['analisis_rp']);
                                            
                                            $rekomParts = [];
                                            if(!empty($lk['rekomendasi_rtm'])) $rekomParts[] = "<strong>RTM:</strong> ".htmlspecialchars($lk['rekomendasi_rtm']);
                                            if(!empty($lk['rekomendasi_ptk'])) $rekomParts[] = "<strong>PTK:</strong> ".htmlspecialchars($lk['rekomendasi_ptk']);
                                        ?>
                                            <tr>
                                                <td class="text-center"><?= $idx + 1; ?></td>
                                                <td class="text-center fw-bold text-primary"><?= htmlspecialchars($noR); ?></td>
                                                <td><?= htmlspecialchars($pr['pernyataan_risiko']); ?></td>
                                                <td class="text-center"><span class="badge <?= $tpClass; ?>"><?= htmlspecialchars($tp ?: '-'); ?></span></td>
                                                <td><?= count($plorParts) > 0 ? implode('<br>', $plorParts) : '-'; ?></td>
                                                <td><?= count($analisisParts) > 0 ? implode('<br>', $analisisParts) : '-'; ?></td>
                                                <td class="text-center">
                                                    <strong><?= $lk['skor_res'] ?? '-'; ?></strong><br>
                                                    <small><?= htmlspecialchars($lk['level_res'] ?? ''); ?></small>
                                                </td>
                                                <td><?= count($rekomParts) > 0 ? implode('<br>', $rekomParts) : '-'; ?></td>
                                            </tr>
                                        <?php endforeach; if(empty($persiapanRisikoRows)): ?>
                                            <tr>
                                                <td colspan="8" class="text-center text-muted">Belum ada data LK2.</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </section>

                    <section class="audit-report-section" id="triangulasiRisiko">
                        <div class="audit-report-section-head">
                            <div>
                                <h5>C. Triangulasi Temuan Audit (Tahap 7)</h5>
                                <p>Verifikasi triangulasi silang antara bukti dokumen, wawancara auditi, dan observasi lapangan.</p>
                            </div>
                            <button onclick="printDiv('triangulasiRisiko')" class="btn btn-outline-primary btn-sm"><i class="fas fa-print me-1"></i> Print</button>
                        </div>
                        <div class="audit-report-section-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped align-middle text-dark mb-0" style="font-size: 0.85rem;">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 4%;" class="text-center">No</th>
                                            <th style="width: 8%;" class="text-center">No Risiko</th>
                                            <th style="width: 25%;">Pernyataan Risiko & Standar</th>
                                            <th style="width: 15%;">Sumber 1 (Dokumen)</th>
                                            <th style="width: 15%;">Sumber 2 (Wawancara)</th>
                                            <th style="width: 15%;">Sumber 3 (Observasi)</th>
                                            <th style="width: 18%;">Kesimpulan & Kategori</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach($persiapanRisikoRows as $idx => $pr): 
                                            $noR = $pr['no_risiko'];
                                            $tr = $triMap[$noR] ?? [];
                                            
                                            $dokParts = [];
                                            if(!empty($tr['dokumen_temuan'])) $dokParts[] = "T: ".htmlspecialchars($tr['dokumen_temuan']);
                                            if(!empty($tr['dokumen_klaim'])) $dokParts[] = "K: ".htmlspecialchars($tr['dokumen_klaim']);
                                            if(!empty($tr['dokumen_konsisten'])) $dokParts[] = "Konsistensi: <strong>".htmlspecialchars($tr['dokumen_konsisten'])."</strong>";
                                            
                                            $wawParts = [];
                                            if(!empty($tr['wawancara_temuan'])) $wawParts[] = "T: ".htmlspecialchars($tr['wawancara_temuan']);
                                            if(!empty($tr['wawancara_klaim'])) $wawParts[] = "K: ".htmlspecialchars($tr['wawancara_klaim']);
                                            if(!empty($tr['wawancara_konsisten'])) $wawParts[] = "Konsistensi: <strong>".htmlspecialchars($tr['wawancara_konsisten'])."</strong>";
                                            
                                            $obsParts = [];
                                            if(!empty($tr['observasi_temuan'])) $obsParts[] = "T: ".htmlspecialchars($tr['observasi_temuan']);
                                            if(!empty($tr['observasi_klaim'])) $obsParts[] = "K: ".htmlspecialchars($tr['observasi_klaim']);
                                            if(!empty($tr['observasi_konsisten'])) $obsParts[] = "Konsistensi: <strong>".htmlspecialchars($tr['observasi_konsisten'])."</strong>";
                                        ?>
                                            <tr>
                                                <td class="text-center"><?= $idx + 1; ?></td>
                                                <td class="text-center fw-bold text-primary"><?= htmlspecialchars($noR); ?></td>
                                                <td><?= htmlspecialchars($pr['pernyataan_risiko']); ?></td>
                                                <td><?= count($dokParts) > 0 ? implode('<br>', $dokParts) : '-'; ?></td>
                                                <td><?= count($wawParts) > 0 ? implode('<br>', $wawParts) : '-'; ?></td>
                                                <td><?= count($obsParts) > 0 ? implode('<br>', $obsParts) : '-'; ?></td>
                                                <td>
                                                    <strong><?= htmlspecialchars(($tr['kesimpulan'] ?? '') ?: '-'); ?></strong>
                                                    <div style="font-size:0.85rem; color:#64748b; margin-top:3px;">
                                                        Kategori: <?= htmlspecialchars(($tr['kategori_temuan'] ?? '') ?: '-'); ?><br>
                                                        Validitas: <?= htmlspecialchars(($tr['validitas'] ?? '') ?: '-'); ?><br>
                                                        Residual: <?= htmlspecialchars(($tr['risiko_residual'] ?? '') ?: '-'); ?>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; if(empty($persiapanRisikoRows)): ?>
                                            <tr>
                                                <td colspan="7" class="text-center text-muted">Belum ada data triangulasi.</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </section>

                    <section class="audit-report-section" id="ptkaRisiko">
                        <div class="audit-report-section-head">
                            <div>
                                <h5>D. Permintaan Tindakan Koreksi (PTK A) (Tahap 8)</h5>
                                <p>Ringkasan kesepakatan permintaan tindakan koreksi dan jadwal penyelesaian perbaikan oleh auditi.</p>
                            </div>
                            <button onclick="printDiv('ptkaRisiko')" class="btn btn-outline-primary btn-sm"><i class="fas fa-print me-1"></i> Print</button>
                        </div>
                        <div class="audit-report-section-body">
                            <div class="table-responsive mb-4">
                                <table class="table table-bordered table-striped align-middle text-dark mb-0" style="font-size: 0.85rem;">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 4%;" class="text-center">No</th>
                                            <th style="width: 8%;" class="text-center">No Risiko</th>
                                            <th style="width: 25%;">Form A (Identitas Temuan)</th>
                                            <th style="width: 35%;">Form B (Rencana Tindakan Korektif)</th>
                                            <th style="width: 14%;">Form C (Monitoring Progres)</th>
                                            <th style="width: 14%;">Form D (Tinjauan Efektivitas)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach($persiapanRisikoRows as $idx => $pr): 
                                            $noR = $pr['no_risiko'];
                                            $ptk = $ptkMap[$noR] ?? [];
                                            $ptkId = $ptk['id'] ?? null;
                                            $tList = $ptkId ? ($tindakanMap[$ptkId] ?? []) : [];
                                            
                                            $formAParts = [];
                                            if(!empty($ptk['ptk_no'])) $formAParts[] = "<strong>No PTK:</strong> ".htmlspecialchars($ptk['ptk_no']);
                                            if(!empty($ptk['ringkasan_temuan'])) $formAParts[] = "<strong>Temuan:</strong> ".htmlspecialchars($ptk['ringkasan_temuan']);
                                            if(!empty($ptk['konsekuensi'])) $formAParts[] = "<strong>Konsekuensi:</strong> ".htmlspecialchars($ptk['konsekuensi']);
                                            if(!empty($ptk['akar_masalah'])) $formAParts[] = "<strong>Akar:</strong> ".htmlspecialchars($ptk['akar_masalah']);
                                            if(!empty($ptk['tenggat_maks'])) $formAParts[] = "<strong>Tenggat:</strong> ".htmlspecialchars($ptk['tenggat_maks']);
                                            
                                            $formBParts = [];
                                            if(count($tList) > 0) {
                                                foreach($tList as $ti => $td) {
                                                    $formBParts[] = ($ti+1).". ".htmlspecialchars($td['tindakan'])." (PJ: ".htmlspecialchars($td['pj'] ?? '', ENT_QUOTES).", Tenggat: ".htmlspecialchars($td['tenggat'] ?? '', ENT_QUOTES).", Selesai: ".htmlspecialchars($td['tgl_selesai'] ?? '', ENT_QUOTES).")";
                                                }
                                            }
                                            
                                            $formCParts = [];
                                            if($ptkId) {
                                                $bulans = [];
                                                for($b=1;$b<=5;$b++) {
                                                    if(!empty($ptk['bulan'.$b.'_status'])) {
                                                        $bulans[] = "B$b: ".htmlspecialchars($ptk['bulan'.$b.'_status']);
                                                    }
                                                }
                                                if(count($bulans) > 0) $formCParts[] = implode(', ', $bulans);
                                                if(!empty($ptk['catatan_lpm'])) $formCParts[] = "<strong>LPM:</strong> ".htmlspecialchars($ptk['catatan_lpm']);
                                            }
                                            
                                            $formDParts = [];
                                            if(!empty($ptk['efektivitas'])) $formDParts[] = "<strong>Status:</strong> ".htmlspecialchars($ptk['efektivitas']);
                                            if(!empty($ptk['tenggat_baru'])) $formDParts[] = "<strong>Tenggat Baru:</strong> ".htmlspecialchars($ptk['tenggat_baru']);
                                            if(!empty($ptk['ref_lk_verifikasi'])) $formDParts[] = "<strong>Ref LK:</strong> ".htmlspecialchars($ptk['ref_lk_verifikasi']);
                                            if(!empty($ptk['auditor_verifikasi'])) $formDParts[] = "<strong>Verifikator:</strong> ".htmlspecialchars($ptk['auditor_verifikasi']);
                                        ?>
                                            <tr>
                                                <td class="text-center"><?= $idx + 1; ?></td>
                                                <td class="text-center fw-bold text-primary"><?= htmlspecialchars($noR); ?></td>
                                                <td><?= count($formAParts) > 0 ? implode('<br>', $formAParts) : '-'; ?></td>
                                                <td><?= count($formBParts) > 0 ? implode('<br>', $formBParts) : '-'; ?></td>
                                                <td><?= count($formCParts) > 0 ? implode('<br>', $formCParts) : '-'; ?></td>
                                                <td><?= count($formDParts) > 0 ? implode('<br>', $formDParts) : '-'; ?></td>
                                            </tr>
                                        <?php endforeach; if(empty($persiapanRisikoRows)): ?>
                                            <tr>
                                                <td colspan="6" class="text-center text-muted">Belum ada data PTK.</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                            <div class="audit-signature">
                                <div class="audit-sign-box">Pimpinan Auditi<?= audit_signature_image('auditi'); ?><strong><?= audit_signature_name('auditi', $audit['kaunit'] ?: '-'); ?></strong></div>
                                <div class="audit-sign-box">Auditor<?= audit_signature_image('auditor'); ?><strong><?= audit_signature_name('auditor', $ketuaaud ?: '-'); ?></strong></div>
                            </div>
                        </div>
                    </section>
                    <?php else: ?>
                    <!-- VERSI 1: WEB VIEW SECTIONS -->
                    <section class="audit-report-section" id="catatanaudit">
                        <div class="audit-report-section-head">
                            <div>
                                <h5>Catatan Audit</h5>
                                <p>Hasil telaah auditor terhadap referensi standar.</p>
                            </div>
                            <button onclick="printDiv('catatanaudit')" class="btn btn-outline-primary btn-sm"><i class="fas fa-print me-1"></i> Print</button>
                        </div>
                        <div class="audit-report-section-body">
                            <?php if(empty($catatanRows)): ?>
                                <div class="alert alert-light border mb-0">Belum ada catatan audit.</div>
                            <?php else: ?>
                                <div class="audit-note-list">
                                    <?php foreach($catatanRows as $index => $c): ?>
                                        <article class="audit-note-card">
                                            <div class="audit-card-top">
                                                <div class="audit-card-title"><?= ($index + 1).'. '.htmlspecialchars($c['kodecatatan']); ?></div>
                                                <?php if ($c['sesuai'] === 'Sesuai'): ?>
                                                    <span class="badge bg-success"><i class="fas fa-check me-1"></i> Sesuai</span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger"><i class="fas fa-times me-1"></i> Tidak Sesuai</span>
                                                <?php endif; ?>
                                            </div>
                                            <div class="audit-content-grid">
                                                <div class="audit-content-block"><div class="audit-info-label">Referensi</div><?= audit_laporan_text($c['referensi']); ?></div>
                                                <div class="audit-content-block"><div class="audit-info-label">Telaah</div><?= audit_laporan_text($c['telaah']); ?></div>
                                                <div class="audit-content-block" style="grid-column:1/-1"><div class="audit-info-label">Catatan Khusus</div><?= audit_laporan_text($c['khusus']); ?></div>
                                            </div>
                                        </article>
                                    <?php endforeach; ?>
                                </div>
                                <div class="audit-signature">
                                    <div></div>
                                    <div class="audit-sign-box">Auditor<?= audit_signature_image('auditor'); ?><strong><?= audit_signature_name('auditor', $ketuaaud ?: '-'); ?></strong></div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </section>

                    <section class="audit-report-section" id="konpos">
                        <div class="audit-report-section-head">
                            <div>
                                <h5>Ringkasan Kondisi Positif</h5>
                                <p>Kekuatan dan praktik baik yang ditemukan selama audit.</p>
                            </div>
                            <button onclick="printDiv('konpos')" class="btn btn-outline-primary btn-sm"><i class="fas fa-print me-1"></i> Print</button>
                        </div>
                        <div class="audit-report-section-body">
                            <?php if(empty($positifRows)): ?>
                                <div class="alert alert-light border mb-0">Belum ada kondisi positif.</div>
                            <?php else: ?>
                                <div class="audit-finding-list">
                                    <?php foreach($positifRows as $index => $p): ?>
                                        <article class="audit-finding-card">
                                            <div class="audit-card-top">
                                                <div class="audit-card-title"><?= ($index + 1).'. Kondisi Positif'; ?></div>
                                                <span class="audit-pill"><?= htmlspecialchars($p['kodekondisi']); ?></span>
                                            </div>
                                            <div class="audit-content-block"><div class="audit-info-label">Kondisi</div><?= audit_laporan_text($p['kondisi']); ?></div>
                                        </article>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </section>

                    <section class="audit-report-section" id="destu">
                        <div class="audit-report-section-head">
                            <div>
                                <h5>Deskripsi Temuan</h5>
                                <p>Rincian temuan, penyebab, akibat, rekomendasi, dan rencana tindak lanjut.</p>
                            </div>
                            <button onclick="printDiv('destu')" class="btn btn-outline-primary btn-sm"><i class="fas fa-print me-1"></i> Print</button>
                        </div>
                        <div class="audit-report-section-body">
                            <?php if(empty($temuanRows)): ?>
                                <div class="alert alert-light border mb-0">Belum ada temuan audit.</div>
                            <?php else: ?>
                                <div class="audit-finding-list">
                                    <?php foreach($temuanRows as $index => $t): ?>
                                        <article class="audit-finding-card">
                                            <div class="audit-card-top">
                                                <div>
                                                    <div class="audit-card-title"><?= ($index + 1).'. '.htmlspecialchars($t['kodekondisi']); ?></div>
                                                    <div class="audit-report-meta">
                                                        <span class="audit-pill">Status: <?= htmlspecialchars($t['status']); ?></span>
                                                        <span class="audit-pill">Kategori: <?= htmlspecialchars($t['kategori']); ?></span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="audit-content-grid">
                                                <div class="audit-content-block" style="grid-column:1/-1"><div class="audit-info-label">Kondisi</div><?= audit_laporan_text($t['kondisi']); ?></div>
                                                <div class="audit-content-block" style="grid-column:1/-1"><div class="audit-info-label">Deskripsi</div><?= audit_laporan_text($t['deskripsi']); ?></div>
                                                <div class="audit-content-block"><div class="audit-info-label">Penyebab</div><?= audit_laporan_text($t['penyebab']); ?></div>
                                                <div class="audit-content-block"><div class="audit-info-label">Akibat</div><?= audit_laporan_text($t['akibat']); ?></div>
                                                <div class="audit-content-block"><div class="audit-info-label">Rekomendasi</div><?= audit_laporan_text($t['rekomendasi']); ?></div>
                                                <div class="audit-content-block"><div class="audit-info-label">Tanggapan Auditi</div><?= audit_laporan_text($t['tanggapan']); ?></div>
                                                <div class="audit-content-block"><div class="audit-info-label">Rencana Perbaikan</div><?= audit_laporan_text($t['renperbaikan']); ?><div class="mt-2 small text-muted">Jadwal: <?= audit_laporan_date($t['jadpen1']); ?></div></div>
                                                <div class="audit-content-block"><div class="audit-info-label">Rencana Pencegahan</div><?= audit_laporan_text($t['renpencegahan']); ?><div class="mt-2 small text-muted">Jadwal: <?= audit_laporan_date($t['jadpen2']); ?></div></div>
                                                <div class="audit-content-block" style="grid-column:1/-1"><div class="audit-info-label">Referensi</div><?= audit_laporan_text($t['referensi']); ?></div>
                                            </div>
                                        </article>
                                    <?php endforeach; ?>
                                </div>
                                <div class="audit-signature">
                                    <div class="audit-sign-box">Pimpinan Auditi<?= audit_signature_image('auditi'); ?><strong><?= audit_signature_name('auditi', $audit['kaunit'] ?: '-'); ?></strong></div>
                                    <div class="audit-sign-box">Auditor<?= audit_signature_image('auditor'); ?><strong><?= audit_signature_name('auditor', $ketuaaud ?: '-'); ?></strong></div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </section>
                    <?php endif; ?>    </div>
            </div>
        </section>
    </div>
</div>
<?php include 'foot.php'; ?>
<script type="text/javascript">
    function initSignaturePads() {
        document.querySelectorAll('.signature-form').forEach(function(form) {
            if(form.dataset.signatureReady === '1') return;
            form.dataset.signatureReady = '1';
            var canvas = form.querySelector('.signature-pad');
            var ctx = canvas.getContext('2d');
            var signatureData = form.querySelector('.signature-data');
            var drawing = false;
            var hasInk = false;

            function resizeCanvas() {
                var ratio = Math.max(window.devicePixelRatio || 1, 1);
                var rect = canvas.getBoundingClientRect();
                var width = rect.width || canvas.parentElement.clientWidth || 500;
                var height = rect.height || 170;
                canvas.width = width * ratio;
                canvas.height = height * ratio;
                ctx.setTransform(ratio, 0, 0, ratio, 0, 0);
                ctx.lineWidth = 2;
                ctx.lineCap = 'round';
                ctx.strokeStyle = '#111827';
            }
            function point(event) {
                var rect = canvas.getBoundingClientRect();
                var e = event.touches ? event.touches[0] : event;
                return { x: e.clientX - rect.left, y: e.clientY - rect.top };
            }
            function start(event) {
                event.preventDefault();
                drawing = true;
                hasInk = true;
                var p = point(event);
                ctx.beginPath();
                ctx.moveTo(p.x, p.y);
            }
            function move(event) {
                if(!drawing) return;
                event.preventDefault();
                var p = point(event);
                ctx.lineTo(p.x, p.y);
                ctx.stroke();
            }
            function stop() { drawing = false; }

            resizeCanvas();
            form._resizeSignaturePad = resizeCanvas;
            window.addEventListener('resize', resizeCanvas);
            canvas.addEventListener('mousedown', start);
            canvas.addEventListener('mousemove', move);
            window.addEventListener('mouseup', stop);
            canvas.addEventListener('touchstart', start, { passive: false });
            canvas.addEventListener('touchmove', move, { passive: false });
            canvas.addEventListener('touchend', stop);
            form.querySelector('.signature-clear').addEventListener('click', function() {
                ctx.clearRect(0, 0, canvas.width, canvas.height);
                signatureData.value = '';
                hasInk = false;
            });
            form.querySelector('.signature-upload').addEventListener('change', function(event) {
                var file = event.target.files[0];
                if(!file) return;
                var reader = new FileReader();
                reader.onload = function(loadEvent) {
                    var img = new Image();
                    img.onload = function() {
                        var rect = canvas.getBoundingClientRect();
                        ctx.clearRect(0, 0, rect.width, rect.height);
                        var scale = Math.min(rect.width / img.width, rect.height / img.height);
                        var width = img.width * scale;
                        var height = img.height * scale;
                        ctx.drawImage(img, (rect.width - width) / 2, (rect.height - height) / 2, width, height);
                        signatureData.value = canvas.toDataURL('image/png');
                        hasInk = true;
                    };
                    img.src = loadEvent.target.result;
                };
                reader.readAsDataURL(file);
            });
            form.addEventListener('submit', function(event) {
                event.preventDefault();
                if(!hasInk) {
                    alert('Silakan gambar tanda tangan atau upload gambar tanda tangan terlebih dahulu.');
                    return;
                }
                if(!signatureData.value) {
                    signatureData.value = canvas.toDataURL('image/png');
                }
                var $form = $(form);
                var pihak = $form.find('input[name="pihak"]').val();
                var namaValue = $form.find('input[name="nama"]').val();
                var sigValue = signatureData.value;
                $.ajax({
                    url: window.location.href,
                    type: 'POST',
                    data: $form.serialize(),
                    dataType: 'json',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    success: function(res) {
                        if (res && res.status === 'success') {
                            $form.find('.signature-preview').html('<img src="' + sigValue + '" alt="Tanda tangan tersimpan">');
                            $('[data-pihak-sig-img="' + pihak + '"]').replaceWith('<img class="report-sign-image" src="' + sigValue + '" alt="Tanda tangan" data-pihak-sig-img="' + pihak + '">');
                            $('[data-pihak-sig-name="' + pihak + '"]').text(namaValue);
                            if (typeof Swal !== 'undefined') {
                                Swal.fire({ icon: 'success', title: 'Berhasil', text: 'Tanda tangan berhasil disimpan.', timer: 1500, showConfirmButton: false });
                            } else {
                                alert('Tanda tangan berhasil disimpan.');
                            }
                        } else {
                            alert(res.message || 'Gagal menyimpan tanda tangan.');
                        }
                    },
                    error: function() {
                        alert('Terjadi kesalahan saat menyimpan tanda tangan.');
                    }
                });
            });
        });
    }
    initSignaturePads();
    document.querySelectorAll('#signatureToolsBody').forEach(function(panel) {
        panel.addEventListener('shown.bs.collapse', function() {
            document.querySelectorAll('.signature-form').forEach(function(form) {
                if(typeof form._resizeSignaturePad === 'function') {
                    form._resizeSignaturePad();
                }
            });
        });
    });

    function printDiv(divName) {
        var printContents = document.getElementById(divName).innerHTML;
        var originalContents = document.body.innerHTML;
        document.body.innerHTML = printContents;
        window.print();
        document.body.innerHTML = originalContents;
        window.location.reload();
    }
</script>
