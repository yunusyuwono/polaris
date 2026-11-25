<?php
/**
 * audit.lk2.save.php
 * AJAX endpoint untuk menyimpan data LK2 Digital
 * Dipanggil oleh audit.lk2.input.php via fetch()
 */
header('Content-Type: application/json');

include 'koneksi.php';

if($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['ok' => false, 'msg' => 'Method tidak valid']);
    exit;
}

$aksi = $_POST['aksi'] ?? '';

// ============================================================
// SIMPAN DATA TEMUAN (LK2-A atau LK2-B per-risiko)
// ============================================================
if($aksi === 'simpan_temuan') {
    $ka        = mysqli_real_escape_string($kon, $_POST['kodeaudit'] ?? '');
    $noRisiko  = mysqli_real_escape_string($kon, $_POST['no_risiko'] ?? '');
    $tipe      = $_POST['tipe'] ?? 'A'; // 'A' atau 'B'

    if(empty($ka) || empty($noRisiko)) {
        echo json_encode(['ok' => false, 'msg' => 'kodeaudit / no_risiko tidak boleh kosong']);
        exit;
    }

    // Sanitasi field teks
    $esc = function($v) use ($kon) {
        return mysqli_real_escape_string($kon, $_POST[$v] ?? '');
    };

    $tipe_temuan             = $esc('tipe_temuan');
    $plor_p                  = $esc('plor_p');
    $plor_l                  = $esc('plor_l');
    $plor_o                  = $esc('plor_o');
    $plor_r                  = $esc('plor_r');
    $akar_penyebab           = $esc('akar_penyebab');
    $risiko_potensial        = $esc('risiko_potensial');
    $pengendalian_efektivitas = $esc('pengendalian_efektivitas');
    $rekomendasi_ptk         = $esc('rekomendasi_ptk');
    $rekomendasi_rtm         = $esc('rekomendasi_rtm');
    $faktor_pendukung        = $esc('faktor_pendukung');
    $peluang_peningkatan     = $esc('peluang_peningkatan');
    $potensi_replikasi       = $esc('potensi_replikasi');
    $level                   = $esc('level');
    $tren                    = $esc('tren');

    $dampak  = max(1, min(5, (int)($_POST['dampak'] ?? 1)));
    $peluang = max(1, min(5, (int)($_POST['peluang'] ?? 1)));
    $skor    = $dampak * $peluang;

    // Hitung level otomatis (MERAH >= 10, KUNING >= 5, else HIJAU)
    if($skor >= 10)       $level = 'MERAH';
    elseif($skor >= 5)    $level = 'KUNING';
    else                  $level = 'HIJAU';

    // UPSERT (INSERT ... ON DUPLICATE KEY UPDATE)
    $sql = "INSERT INTO audit_lk2_temuan
                (kodeaudit, no_risiko, tipe_temuan,
                 plor_p, plor_l, plor_o, plor_r,
                 akar_penyebab, risiko_potensial,
                 dampak, peluang, skor,
                 pengendalian_efektivitas, level, tren,
                 rekomendasi_ptk, rekomendasi_rtm,
                 faktor_pendukung, peluang_peningkatan, potensi_replikasi)
            VALUES
                ('$ka', '$noRisiko', '$tipe_temuan',
                 '$plor_p','$plor_l','$plor_o','$plor_r',
                 '$akar_penyebab','$risiko_potensial',
                 $dampak, $peluang, $skor,
                 '$pengendalian_efektivitas','$level','$tren',
                 '$rekomendasi_ptk','$rekomendasi_rtm',
                 '$faktor_pendukung','$peluang_peningkatan','$potensi_replikasi')
            ON DUPLICATE KEY UPDATE
                tipe_temuan='$tipe_temuan',
                plor_p='$plor_p', plor_l='$plor_l', plor_o='$plor_o', plor_r='$plor_r',
                akar_penyebab='$akar_penyebab', risiko_potensial='$risiko_potensial',
                dampak=$dampak, peluang=$peluang, skor=$skor,
                pengendalian_efektivitas='$pengendalian_efektivitas',
                level='$level', tren='$tren',
                rekomendasi_ptk='$rekomendasi_ptk', rekomendasi_rtm='$rekomendasi_rtm',
                faktor_pendukung='$faktor_pendukung',
                peluang_peningkatan='$peluang_peningkatan',
                potensi_replikasi='$potensi_replikasi',
                entri=CURRENT_TIMESTAMP";

    if(mysqli_query($kon, $sql)) {
        echo json_encode(['ok' => true, 'skor' => $skor, 'level' => $level]);
    } else {
        echo json_encode(['ok' => false, 'msg' => mysqli_error($kon)]);
    }
    exit;
}

// ============================================================
// SIMPAN KESIMPULAN AUDIT
// ============================================================
if($aksi === 'simpan_kesimpulan') {
    $ka = mysqli_real_escape_string($kon, $_POST['kodeaudit'] ?? '');
    if(empty($ka)) {
        echo json_encode(['ok' => false, 'msg' => 'kodeaudit tidak boleh kosong']);
        exit;
    }

    $catatan_umum          = mysqli_real_escape_string($kon, $_POST['catatan_umum'] ?? '');
    $rekomendasi_strategis = mysqli_real_escape_string($kon, $_POST['rekomendasi_strategis'] ?? '');

    $sql = "INSERT INTO audit_lk2_kesimpulan (kodeaudit, catatan_umum, rekomendasi_strategis)
            VALUES ('$ka', '$catatan_umum', '$rekomendasi_strategis')
            ON DUPLICATE KEY UPDATE
                catatan_umum='$catatan_umum',
                rekomendasi_strategis='$rekomendasi_strategis',
                entri=CURRENT_TIMESTAMP";

    if(mysqli_query($kon, $sql)) {
        echo json_encode(['ok' => true]);
    } else {
        echo json_encode(['ok' => false, 'msg' => mysqli_error($kon)]);
    }
    exit;
}

// ============================================================
// SIMPAN HEADER AUDIT (audit_persiapan)
// ============================================================
if($aksi === 'simpan_header') {
    $ka = mysqli_real_escape_string($kon, $_POST['kodeaudit'] ?? '');
    if(empty($ka)) {
        echo json_encode(['ok' => false, 'msg' => 'kodeaudit tidak boleh kosong']);
        exit;
    }

    $ketua_tim         = mysqli_real_escape_string($kon, $_POST['ketua_tim'] ?? '');
    $anggota_tim       = mysqli_real_escape_string($kon, $_POST['anggota_tim'] ?? '');
    $tanggal_audit_dok = mysqli_real_escape_string($kon, $_POST['tanggal_audit_dok'] ?? '');
    $unit_diaudit      = mysqli_real_escape_string($kon, $_POST['unit_diaudit'] ?? '');
    $auditee_kaprodi   = mysqli_real_escape_string($kon, $_POST['auditee_kaprodi'] ?? '');
    $no_dokumen        = mysqli_real_escape_string($kon, $_POST['no_dokumen'] ?? '');
    $standar_diaudit   = mysqli_real_escape_string($kon, $_POST['standar_diaudit'] ?? '');
    $ref_form_pra_ami  = mysqli_real_escape_string($kon, $_POST['ref_form_pra_ami'] ?? '');
    $periode_ami       = mysqli_real_escape_string($kon, $_POST['periode_ami'] ?? '');

    $sql = "INSERT INTO audit_persiapan 
                (kodeaudit, ketua_tim, anggota_tim, tanggal_audit_dok, unit_diaudit, auditee_kaprodi, no_dokumen, standar_diaudit, ref_form_pra_ami, periode_ami)
            VALUES 
                ('$ka', '$ketua_tim', '$anggota_tim', " . (empty($tanggal_audit_dok) ? "NULL" : "'$tanggal_audit_dok'") . ", '$unit_diaudit', '$auditee_kaprodi', '$no_dokumen', '$standar_diaudit', '$ref_form_pra_ami', '$periode_ami')
            ON DUPLICATE KEY UPDATE
                ketua_tim='$ketua_tim',
                anggota_tim='$anggota_tim',
                tanggal_audit_dok=" . (empty($tanggal_audit_dok) ? "NULL" : "'$tanggal_audit_dok'") . ",
                unit_diaudit='$unit_diaudit',
                auditee_kaprodi='$auditee_kaprodi',
                no_dokumen='$no_dokumen',
                standar_diaudit='$standar_diaudit',
                ref_form_pra_ami='$ref_form_pra_ami',
                periode_ami='$periode_ami',
                entri=CURRENT_TIMESTAMP";

    if(mysqli_query($kon, $sql)) {
        echo json_encode(['ok' => true]);
    } else {
        echo json_encode(['ok' => false, 'msg' => mysqli_error($kon)]);
    }
    exit;
}

echo json_encode(['ok' => false, 'msg' => 'Aksi tidak dikenali']);
