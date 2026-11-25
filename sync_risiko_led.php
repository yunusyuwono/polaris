<?php
require 'koneksi.php';

// Ambil kodeaudit dari GET atau default ke 262024 (Audit Perpustakaan 2026)
$ka = isset($_GET['ka']) ? mysqli_real_escape_string($kon, trim((string)$_GET['ka'])) : '262024';
$doSync = isset($_POST['execute_sync']) || isset($_GET['auto']);

// Ambil data audit
$qAudit = mysqli_query($kon, "
    SELECT a.*, u.nama AS nama_unit 
    FROM audit a 
    LEFT JOIN unit u ON a.unit = u.id 
    WHERE a.kodeaudit = '$ka'
");
$audit = $qAudit ? mysqli_fetch_assoc($qAudit) : null;

// Cari referensi praaudit
$selectedPraId = 0;
if ($audit) {
    // Cek dari audit_persiapan jika sudah ada
    $qHeader = mysqli_query($kon, "SELECT ref_form_pra_ami FROM audit_persiapan WHERE kodeaudit='$ka'");
    if ($qHeader && $h = mysqli_fetch_assoc($qHeader)) {
        if (!empty($h['ref_form_pra_ami'])) {
            $parts = explode('/', $h['ref_form_pra_ami']);
            $selectedPraId = (int)end($parts);
        }
    }
    // Fallback praaudit berdasarkan unit & tahun
    if ($selectedPraId <= 0) {
        $tahunAudit = (int)date('Y', strtotime($audit['tglmulai']));
        $unitId = (int)$audit['unit'];
        $qPra = mysqli_query($kon, "SELECT id FROM praaudit WHERE unit='$unitId' AND tahun='$tahunAudit' LIMIT 1");
        if ($qPra && $p = mysqli_fetch_assoc($qPra)) {
            $selectedPraId = (int)$p['id'];
        }
    }
}

// Data daftar audit untuk switcher
$auditsList = [];
$qList = mysqli_query($kon, "SELECT a.kodeaudit, a.tglmulai, u.nama FROM audit a LEFT JOIN unit u ON a.unit = u.id ORDER BY a.tglmulai DESC LIMIT 30");
while ($row = mysqli_fetch_assoc($qList)) {
    $auditsList[] = $row;
}

// Ambil existing risks
$existingRisks = [];
if (!empty($ka)) {
    $qEx = mysqli_query($kon, "SELECT id, no_risiko, pernyataan_risiko, dampak, probabilitas, skor, level, pengendalian_diklaim FROM audit_persiapan_risiko WHERE kodeaudit='$ka' ORDER BY CAST(REPLACE(no_risiko,'R-','') AS UNSIGNED) ASC");
    if ($qEx) {
        while ($ex = mysqli_fetch_assoc($qEx)) {
            $cleanPrefix = trim(preg_replace('/\(Risiko:\s*.*?\)\s*$/is', '', $ex['pernyataan_risiko']));
            $ex['clean_prefix'] = $cleanPrefix;
            $existingRisks[] = $ex;
        }
    }
}

// Ambil data LED terbaru
$ledRisks = [];
if ($selectedPraId > 0) {
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
    if ($qSync) {
        while ($r = mysqli_fetch_assoc($qSync)) {
            $ledRisks[] = $r;
        }
    }
}

// Proses Sinkronisasi jika dipicu
$syncResults = [];
$updatedCount = 0;
$insertedCount = 0;

if ($doSync && $selectedPraId > 0 && !empty($ledRisks)) {
    // Current max index jika ada insert baru
    $qMaxIdx = mysqli_query($kon, "SELECT COUNT(*) as cnt FROM audit_persiapan_risiko WHERE kodeaudit='$ka'");
    $maxRow = @mysqli_fetch_assoc($qMaxIdx);
    $syncIdx = (int)($maxRow['cnt'] ?? 0) + 1;

    foreach ($ledRisks as $r) {
        $isiRisiko = !empty($r['isi_risiko']) ? $r['isi_risiko'] : 'Belum diisi';
        $pernyataan = mysqli_real_escape_string($kon, $r['namastandar'].' - '. $r['pertanyaan'] . ' (Risiko: '.$isiRisiko.')');
        $pengendalian = mysqli_real_escape_string($kon, $r['usulan_mitigasi'] ?? '');
        
        $hasData = !empty($r['isi_risiko']) || !empty($r['usulan_mitigasi']);
        if ($hasData) {
            $dampak = !empty($r['dampak']) ? (int)$r['dampak'] : 0;
            $probabilitas = !empty($r['probabilitas']) ? (int)$r['probabilitas'] : 0;
            $skor = ($dampak > 0 && $probabilitas > 0) ? (!empty($r['risiko']) ? (int)$r['risiko'] : ($dampak * $probabilitas)) : 0;
        } else {
            $dampak = 0;
            $probabilitas = 0;
            $skor = 0;
        }
        
        if ($skor >= 10) $level = 'MERAH';
        elseif ($skor >= 5) $level = 'KUNING';
        elseif ($skor > 0) $level = 'HIJAU';
        else $level = '';
        
        $targetPrefix = trim($r['namastandar'].' - '. $r['pertanyaan']);
        $matched = null;
        foreach ($existingRisks as $ex) {
            if ($ex['clean_prefix'] === $targetPrefix) {
                $matched = $ex;
                break;
            }
        }
        if (!$matched) {
            foreach ($existingRisks as $ex) {
                if (strpos($ex['clean_prefix'], $targetPrefix) === 0 || strpos($targetPrefix, $ex['clean_prefix']) === 0) {
                    $matched = $ex;
                    break;
                }
            }
        }

        if ($matched) {
            $eId = (int)$matched['id'];
            $res = mysqli_query($kon, "
                UPDATE audit_persiapan_risiko SET 
                    pernyataan_risiko = '$pernyataan',
                    dampak = '$dampak',
                    probabilitas = '$probabilitas',
                    skor = '$skor',
                    level = '$level',
                    pengendalian_diklaim = '$pengendalian'
                WHERE id = '$eId'
            ");
            if ($res) $updatedCount++;
            $syncResults[] = [
                'aksi' => 'UPDATE',
                'no_risiko' => $matched['no_risiko'],
                'pertanyaan' => $targetPrefix,
                'dampak' => $dampak,
                'probabilitas' => $probabilitas,
                'skor' => $skor,
                'level' => $level
            ];
        } else {
            $noRisiko = 'R-'.$syncIdx;
            $indikator = mysqli_real_escape_string($kon, 'Target/Indikator');
            $res = mysqli_query($kon, "
                INSERT INTO audit_persiapan_risiko 
                (kodeaudit, no_risiko, pernyataan_risiko, indikator_target, dampak, probabilitas, skor, level, pengendalian_diklaim) 
                VALUES 
                ('$ka', '$noRisiko', '$pernyataan', '$indikator', '$dampak', '$probabilitas', '$skor', '$level', '$pengendalian')
            ");
            if ($res) $insertedCount++;
            $syncIdx++;
            $syncResults[] = [
                'aksi' => 'INSERT',
                'no_risiko' => $noRisiko,
                'pertanyaan' => $targetPrefix,
                'dampak' => $dampak,
                'probabilitas' => $probabilitas,
                'skor' => $skor,
                'level' => $level
            ];
        }
    }

    // Refresh existing risks setelah update
    $existingRisks = [];
    $qEx = mysqli_query($kon, "SELECT id, no_risiko, pernyataan_risiko, dampak, probabilitas, skor, level, pengendalian_diklaim FROM audit_persiapan_risiko WHERE kodeaudit='$ka' ORDER BY CAST(REPLACE(no_risiko,'R-','') AS UNSIGNED) ASC");
    if ($qEx) {
        while ($ex = mysqli_fetch_assoc($qEx)) {
            $cleanPrefix = trim(preg_replace('/\(Risiko:\s*.*?\)\s*$/is', '', $ex['pernyataan_risiko']));
            $ex['clean_prefix'] = $cleanPrefix;
            $existingRisks[] = $ex;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sinkronisasi Risiko LED ke Catatan Persiapan Auditor</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f8fafc; font-family: system-ui, -apple-system, sans-serif; }
        .card { border-radius: 10px; border: none; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.08); }
        .badge-merah { background-color: #ef4444; color: #fff; font-weight: 700; }
        .badge-kuning { background-color: #f59e0b; color: #fff; font-weight: 700; }
        .badge-hijau { background-color: #10b981; color: #fff; font-weight: 700; }
        .table th { background-color: #f1f5f9; color: #334155; font-size: 0.85rem; }
        .table td { font-size: 0.85rem; vertical-align: middle; }
    </style>
</head>
<body>
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1"><i class="fas fa-sync text-primary me-2"></i>Sinkronisasi Risiko dari Pengisian LED</h4>
            <p class="text-muted small mb-0">Menyelaraskan nilai D, P, Skor, Level, dan Pengendalian dari Form Pra-AMI (LED) ke Catatan Persiapan Auditor.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="audit.catatanpersiapan.php?ka=<?= urlencode($ka); ?>" class="btn btn-outline-primary btn-sm">
                <i class="fas fa-arrow-left me-1"></i> Buka Catatan Persiapan
            </a>
        </div>
    </div>

    <!-- Pilih Audit -->
    <div class="card p-3 mb-4">
        <form method="get" class="row g-2 align-items-center">
            <div class="col-md-5">
                <label class="form-label small fw-bold mb-1">Pilih Audit:</label>
                <select name="ka" class="form-select form-select-sm" onchange="this.form.submit()">
                    <?php foreach ($auditsList as $al): ?>
                        <option value="<?= htmlspecialchars($al['kodeaudit']); ?>" <?= ($ka === $al['kodeaudit']) ? 'selected' : ''; ?>>
                            [<?= htmlspecialchars($al['kodeaudit']); ?>] <?= htmlspecialchars($al['nama'] ?? 'Unit'); ?> (<?= htmlspecialchars($al['tglmulai']); ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-bold mb-1">ID Form Pra-AMI (LED):</label>
                <input type="text" class="form-control form-control-sm bg-light" value="<?= $selectedPraId > 0 ? 'Pra-AMI #' . $selectedPraId : 'Tidak Ditemukan'; ?>" readonly>
            </div>
            <div class="col-md-3 d-flex align-items-end pt-3">
                <button type="submit" class="btn btn-sm btn-secondary w-100"><i class="fas fa-search me-1"></i> Muat Data</button>
            </div>
        </form>
    </div>

    <?php if ($doSync): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <h6 class="fw-bold mb-1"><i class="fas fa-check-circle me-1"></i> Sinkronisasi Berhasil Dijalankan!</h6>
            <p class="mb-0 small">Sebanyak <strong><?= $updatedCount; ?></strong> baris risiko berhasil diperbarui dan <strong><?= $insertedCount; ?></strong> baris baru ditambahkan ke tabel <code>audit_persiapan_risiko</code>.</p>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Info Box & Action -->
    <div class="card p-3 mb-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <span class="badge bg-primary me-2">Kode Audit: <?= htmlspecialchars($ka); ?></span>
                <span class="badge bg-info text-dark me-2">Unit: <?= htmlspecialchars($audit['nama_unit'] ?? '-'); ?></span>
                <span class="badge bg-secondary">Total Risiko Tersimpan: <?= count($existingRisks); ?></span>
            </div>
            <form method="post">
                <button type="submit" name="execute_sync" class="btn btn-warning btn-sm fw-bold shadow-sm" onclick="return confirm('Apakah Anda yakin ingin menyinkronkan data risiko dengan isian LED terbaru?')">
                    <i class="fas fa-sync-alt me-1"></i> Jalankan Sinkronisasi Sekarang
                </button>
            </form>
        </div>
    </div>

    <!-- Tabel Daftar Risiko -->
    <div class="card p-3">
        <h6 class="fw-bold mb-3"><i class="fas fa-list-check me-2 text-primary"></i>Daftar Risiko di Catatan Persiapan Auditor</h6>
        <div class="table-responsive">
            <table class="table table-bordered table-hover mb-0">
                <thead>
                    <tr>
                        <th width="5%" class="text-center">No</th>
                        <th width="45%">Pernyataan Standar & Risiko Potensial</th>
                        <th width="6%" class="text-center">D</th>
                        <th width="6%" class="text-center">P</th>
                        <th width="6%" class="text-center">Skor</th>
                        <th width="10%" class="text-center">Level</th>
                        <th width="22%">Pengendalian / Usulan Mitigasi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($existingRisks)): ?>
                        <tr><td colspan="7" class="text-center text-muted py-3">Belum ada risiko pada kode audit ini. Klik tombol sinkronisasi untuk memuat dari LED.</td></tr>
                    <?php else: ?>
                        <?php foreach ($existingRisks as $row): 
                            $score = (int)$row['skor'];
                            $badgeClass = ($score >= 10) ? 'badge-merah' : (($score >= 5) ? 'badge-kuning' : ($score > 0 ? 'badge-hijau' : 'bg-secondary'));
                        ?>
                            <tr>
                                <td class="text-center fw-bold"><?= htmlspecialchars($row['no_risiko']); ?></td>
                                <td><?= htmlspecialchars($row['pernyataan_risiko']); ?></td>
                                <td class="text-center fw-bold"><?= (int)$row['dampak']; ?></td>
                                <td class="text-center fw-bold"><?= (int)$row['probabilitas']; ?></td>
                                <td class="text-center fw-bold"><?= $score; ?></td>
                                <td class="text-center"><span class="badge <?= $badgeClass; ?> px-2 py-1"><?= htmlspecialchars($row['level'] ?: '-'); ?></span></td>
                                <td><small class="text-muted"><?= htmlspecialchars($row['pengendalian_diklaim'] ?: '-'); ?></small></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
