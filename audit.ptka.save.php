<?php
/**
 * audit.ptka.save.php
 * AJAX endpoint untuk menyimpan data PTK Digital
 */
include 'koneksi.php';

$is_post = ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST';
if ($is_post) {
    header('Content-Type: application/json');
}

// We run the table creation queries first in all cases (GET or POST)
// ── Auto-create tables ──────────────────────────────────────
mysqli_query($kon, "CREATE TABLE IF NOT EXISTS audit_ptk (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kodeaudit VARCHAR(20) NOT NULL,
    no_risiko VARCHAR(20) NOT NULL,
    ptk_no VARCHAR(50) DEFAULT NULL,
    ringkasan_temuan TEXT,
    akar_masalah TEXT,
    konsekuensi VARCHAR(100) DEFAULT 'PTK Segera',
    tenggat_maks DATE DEFAULT NULL,
    bulan1_status VARCHAR(30) DEFAULT '',
    bulan2_status VARCHAR(30) DEFAULT '',
    bulan3_status VARCHAR(30) DEFAULT '',
    bulan4_status VARCHAR(30) DEFAULT '',
    bulan5_status VARCHAR(30) DEFAULT '',
    catatan_lpm TEXT,
    eskalasi_rtm TINYINT(1) DEFAULT 0,
    efektivitas VARCHAR(30) DEFAULT '',
    tenggat_baru DATE DEFAULT NULL,
    ref_lk_verifikasi VARCHAR(100) DEFAULT '',
    auditor_verifikasi VARCHAR(100) DEFAULT '',
    entri TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_ka_risiko (kodeaudit, no_risiko)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

mysqli_query($kon, "CREATE TABLE IF NOT EXISTS audit_ptk_tindakan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ptk_id INT NOT NULL,
    no_urut INT DEFAULT 1,
    tindakan TEXT,
    pj VARCHAR(100) DEFAULT '',
    tenggat DATE DEFAULT NULL,
    realisasi_bukti TEXT,
    tgl_selesai DATE DEFAULT NULL,
    entri TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_ptk_id (ptk_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

if (!$is_post) {
    return;
}

$aksi = $_POST['aksi'] ?? '';
$e = fn($v) => mysqli_real_escape_string($kon, $_POST[$v] ?? '');

// ── SIMPAN SEMUA PTK (Bagian A + B + C + D) ────────────────
if($aksi === 'simpan_ptk_all') {
    $ka         = $e('kodeaudit');
    $noRisiko   = $e('no_risiko');
    if(empty($ka)||empty($noRisiko)){echo json_encode(['ok'=>false,'msg'=>'Data tidak lengkap']); exit;}

    $ptkNo      = $e('ptk_no');
    $ringkasan  = $e('ringkasan_temuan');
    $akar       = $e('akar_masalah');
    $konsekuensi= $e('konsekuensi');
    $tendMaks   = $e('tenggat_maks') ?: 'NULL';
    $tendMaksSql= $tendMaks==='NULL' ? 'NULL' : "'$tendMaks'";

    $b1 = $e('bulan1_status'); $b2 = $e('bulan2_status');
    $b3 = $e('bulan3_status'); $b4 = $e('bulan4_status');
    $b5 = $e('bulan5_status');
    $catatanLpm = $e('catatan_lpm');
    $eskalasiRtm= isset($_POST['eskalasi_rtm']) ? 1 : 0;

    $efektivitas   = $e('efektivitas');
    $tendBaru      = $e('tenggat_baru') ?: 'NULL';
    $tendBaruSql   = $tendBaru==='NULL' ? 'NULL' : "'$tendBaru'";
    $refLk         = $e('ref_lk_verifikasi');
    $auditorVerif  = $e('auditor_verifikasi');

    $sql = "INSERT INTO audit_ptk
        (kodeaudit,no_risiko,ptk_no,ringkasan_temuan,akar_masalah,konsekuensi,tenggat_maks,
         bulan1_status,bulan2_status,bulan3_status,bulan4_status,bulan5_status,
         catatan_lpm,eskalasi_rtm,efektivitas,tenggat_baru,ref_lk_verifikasi,auditor_verifikasi)
        VALUES
        ('$ka','$noRisiko','$ptkNo','$ringkasan','$akar','$konsekuensi',$tendMaksSql,
         '$b1','$b2','$b3','$b4','$b5',
         '$catatanLpm',$eskalasiRtm,'$efektivitas',$tendBaruSql,'$refLk','$auditorVerif')
        ON DUPLICATE KEY UPDATE
            ptk_no='$ptkNo',ringkasan_temuan='$ringkasan',akar_masalah='$akar',
            konsekuensi='$konsekuensi',tenggat_maks=$tendMaksSql,
            bulan1_status='$b1',bulan2_status='$b2',bulan3_status='$b3',
            bulan4_status='$b4',bulan5_status='$b5',catatan_lpm='$catatanLpm',
            eskalasi_rtm=$eskalasiRtm,efektivitas='$efektivitas',tenggat_baru=$tendBaruSql,
            ref_lk_verifikasi='$refLk',auditor_verifikasi='$auditorVerif',
            entri=CURRENT_TIMESTAMP";

    if(mysqli_query($kon,$sql)){
        $r = mysqli_query($kon,"SELECT id FROM audit_ptk WHERE kodeaudit='$ka' AND no_risiko='$noRisiko'");
        $ptkId = mysqli_fetch_assoc($r)['id'] ?? null;

        $countB = 0;
        if($ptkId) {
            $tindakanArr = $_POST['tindakan'] ?? [];
            $pjArr       = $_POST['pj'] ?? [];
            $tenggat     = $_POST['tenggat'] ?? [];
            $realisasi   = $_POST['realisasi_bukti'] ?? [];
            $tglSelesai  = $_POST['tgl_selesai'] ?? [];

            mysqli_query($kon, "DELETE FROM audit_ptk_tindakan WHERE ptk_id=$ptkId");
            foreach($tindakanArr as $i => $tind) {
                $tind  = mysqli_real_escape_string($kon, $tind);
                $pj    = mysqli_real_escape_string($kon, $pjArr[$i] ?? '');
                $tg    = mysqli_real_escape_string($kon, $tenggat[$i] ?? '');
                $real  = mysqli_real_escape_string($kon, $realisasi[$i] ?? '');
                $tgSel = mysqli_real_escape_string($kon, $tglSelesai[$i] ?? '');
                $noUr  = $i + 1;
                $tgSql    = $tg    ? "'$tg'"    : 'NULL';
                $tgSelSql = $tgSel ? "'$tgSel'" : 'NULL';
                if(!empty($tind) || !empty($pj) || !empty($real)) {
                    mysqli_query($kon, "INSERT INTO audit_ptk_tindakan
                        (ptk_id,no_urut,tindakan,pj,tenggat,realisasi_bukti,tgl_selesai)
                        VALUES ($ptkId,$noUr,'$tind','$pj',$tgSql,'$real',$tgSelSql)");
                    $countB++;
                }
            }
        }

        $hasA = !empty($ringkasan) || !empty($akar) || !empty($ptkNo);
        $hasB = $countB > 0;
        $hasC = !empty($b1) || !empty($b2) || !empty($b3) || !empty($b4) || !empty($b5) || !empty($catatanLpm) || $eskalasiRtm;
        $lastC = $b5 ?: ($b4 ?: ($b3 ?: ($b2 ?: ($b1 ?: ''))));
        $hasD = !empty($efektivitas);

        echo json_encode([
            'ok' => true,
            'ptk_id' => $ptkId,
            'ptkNo' => $ptkNo,
            'hasA' => $hasA,
            'hasB' => $hasB,
            'countB' => $countB,
            'hasC' => $hasC,
            'lastC' => $lastC,
            'hasD' => $hasD,
            'efektivitas' => $efektivitas
        ]);
    } else {
        echo json_encode(['ok'=>false,'msg'=>mysqli_error($kon)]);
    }
    exit;
}

// ── SIMPAN PTK HEADER (Bagian A + C + D) ───────────────────
if($aksi === 'simpan_ptk') {
    $ka         = $e('kodeaudit');
    $noRisiko   = $e('no_risiko');
    if(empty($ka)||empty($noRisiko)){echo json_encode(['ok'=>false,'msg'=>'Data tidak lengkap']); exit;}

    $ptkNo      = $e('ptk_no');
    $ringkasan  = $e('ringkasan_temuan');
    $akar       = $e('akar_masalah');
    $konsekuensi= $e('konsekuensi');
    $tendMaks   = $e('tenggat_maks') ?: 'NULL';
    $tendMaksSql= $tendMaks==='NULL' ? 'NULL' : "'$tendMaks'";

    $b1 = $e('bulan1_status'); $b2 = $e('bulan2_status');
    $b3 = $e('bulan3_status'); $b4 = $e('bulan4_status');
    $b5 = $e('bulan5_status');
    $catatanLpm = $e('catatan_lpm');
    $eskalasiRtm= isset($_POST['eskalasi_rtm']) ? 1 : 0;

    $efektivitas   = $e('efektivitas');
    $tendBaru      = $e('tenggat_baru') ?: 'NULL';
    $tendBaruSql   = $tendBaru==='NULL' ? 'NULL' : "'$tendBaru'";
    $refLk         = $e('ref_lk_verifikasi');
    $auditorVerif  = $e('auditor_verifikasi');

    $sql = "INSERT INTO audit_ptk
        (kodeaudit,no_risiko,ptk_no,ringkasan_temuan,akar_masalah,konsekuensi,tenggat_maks,
         bulan1_status,bulan2_status,bulan3_status,bulan4_status,bulan5_status,
         catatan_lpm,eskalasi_rtm,efektivitas,tenggat_baru,ref_lk_verifikasi,auditor_verifikasi)
        VALUES
        ('$ka','$noRisiko','$ptkNo','$ringkasan','$akar','$konsekuensi',$tendMaksSql,
         '$b1','$b2','$b3','$b4','$b5',
         '$catatanLpm',$eskalasiRtm,'$efektivitas',$tendBaruSql,'$refLk','$auditorVerif')
        ON DUPLICATE KEY UPDATE
            ptk_no='$ptkNo',ringkasan_temuan='$ringkasan',akar_masalah='$akar',
            konsekuensi='$konsekuensi',tenggat_maks=$tendMaksSql,
            bulan1_status='$b1',bulan2_status='$b2',bulan3_status='$b3',
            bulan4_status='$b4',bulan5_status='$b5',catatan_lpm='$catatanLpm',
            eskalasi_rtm=$eskalasiRtm,efektivitas='$efektivitas',tenggat_baru=$tendBaruSql,
            ref_lk_verifikasi='$refLk',auditor_verifikasi='$auditorVerif',
            entri=CURRENT_TIMESTAMP";
    if(mysqli_query($kon,$sql)){
        $ptkId = mysqli_insert_id($kon) ?: null;
        if(!$ptkId){
            $r = mysqli_query($kon,"SELECT id FROM audit_ptk WHERE kodeaudit='$ka' AND no_risiko='$noRisiko'");
            $ptkId = mysqli_fetch_assoc($r)['id'] ?? null;
        }
        echo json_encode(['ok'=>true,'ptk_id'=>$ptkId]);
    } else {
        echo json_encode(['ok'=>false,'msg'=>mysqli_error($kon)]);
    }
    exit;
}

// ── SIMPAN TINDAKAN (Bagian B) ──────────────────────────────
if($aksi === 'simpan_tindakan') {
    $ptkId = (int)($_POST['ptk_id'] ?? 0);
    if(!$ptkId){echo json_encode(['ok'=>false,'msg'=>'ptk_id kosong']); exit;}

    $tindakanArr = $_POST['tindakan'] ?? [];
    $pjArr       = $_POST['pj'] ?? [];
    $tenggat     = $_POST['tenggat'] ?? [];
    $realisasi   = $_POST['realisasi_bukti'] ?? [];
    $tglSelesai  = $_POST['tgl_selesai'] ?? [];

    // Hapus semua tindakan lama lalu insert ulang
    mysqli_query($kon, "DELETE FROM audit_ptk_tindakan WHERE ptk_id=$ptkId");

    $inserted = 0;
    foreach($tindakanArr as $i => $tind) {
        $tind  = mysqli_real_escape_string($kon, $tind);
        $pj    = mysqli_real_escape_string($kon, $pjArr[$i] ?? '');
        $tg    = mysqli_real_escape_string($kon, $tenggat[$i] ?? '');
        $real  = mysqli_real_escape_string($kon, $realisasi[$i] ?? '');
        $tgSel = mysqli_real_escape_string($kon, $tglSelesai[$i] ?? '');
        $noUr  = $i + 1;
        $tgSql    = $tg    ? "'$tg'"    : 'NULL';
        $tgSelSql = $tgSel ? "'$tgSel'" : 'NULL';
        mysqli_query($kon, "INSERT INTO audit_ptk_tindakan
            (ptk_id,no_urut,tindakan,pj,tenggat,realisasi_bukti,tgl_selesai)
            VALUES ($ptkId,$noUr,'$tind','$pj',$tgSql,'$real',$tgSelSql)");
        $inserted++;
    }
    echo json_encode(['ok'=>true,'rows'=>$inserted]);
    exit;
}

echo json_encode(['ok'=>false,'msg'=>'Aksi tidak dikenali']);
