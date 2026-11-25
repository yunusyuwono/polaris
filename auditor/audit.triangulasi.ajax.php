<?php
include '../koneksi.php';
if(session_status() === PHP_SESSION_NONE) {
    session_start();
}

function ajax_json_response($payload) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload);
    exit;
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';
$ka = mysqli_real_escape_string($kon, $_POST['kodeaudit'] ?? $_GET['kodeaudit'] ?? '');
$noRisiko = mysqli_real_escape_string($kon, $_POST['no_risiko'] ?? $_GET['no_risiko'] ?? '');

if (empty($ka) || empty($noRisiko)) {
    ajax_json_response(['success' => false, 'message' => 'Kode audit atau nomor risiko tidak valid.']);
}

if ($action === 'get') {
    $q = mysqli_query($kon, "SELECT * FROM audit_triangulasi_detail WHERE kodeaudit='$ka' AND no_risiko='$noRisiko' LIMIT 1");
    $data = $q && mysqli_num_rows($q) > 0 ? mysqli_fetch_assoc($q) : [];
    ajax_json_response([
        'success' => true,
        'data' => $data
    ]);
}

if ($action === 'save') {
    $dT = mysqli_real_escape_string($kon, $_POST['dokumen_temuan'] ?? '');
    $dK = mysqli_real_escape_string($kon, $_POST['dokumen_klaim'] ?? '');
    $dKon = mysqli_real_escape_string($kon, $_POST['dokumen_konsisten'] ?? '');

    $wT = mysqli_real_escape_string($kon, $_POST['wawancara_temuan'] ?? '');
    $wK = mysqli_real_escape_string($kon, $_POST['wawancara_klaim'] ?? '');
    $wKon = mysqli_real_escape_string($kon, $_POST['wawancara_konsisten'] ?? '');

    $oT = mysqli_real_escape_string($kon, $_POST['observasi_temuan'] ?? '');
    $oK = mysqli_real_escape_string($kon, $_POST['observasi_klaim'] ?? '');
    $oKon = mysqli_real_escape_string($kon, $_POST['observasi_konsisten'] ?? '');

    $cA = mysqli_real_escape_string($kon, $_POST['catatan_auditor'] ?? '');
    $kT = mysqli_real_escape_string($kon, $_POST['kategori_temuan'] ?? '');
    $vA = mysqli_real_escape_string($kon, $_POST['validitas'] ?? '');
    $rR = mysqli_real_escape_string($kon, $_POST['risiko_residual'] ?? '');
    $kS = mysqli_real_escape_string($kon, $_POST['kesimpulan'] ?? '');

    $checkData = mysqli_query($kon, "SELECT id FROM audit_triangulasi_detail WHERE kodeaudit='$ka' AND no_risiko='$noRisiko'");
    if(mysqli_num_rows($checkData) > 0) {
        $ok = mysqli_query($kon, "
            UPDATE audit_triangulasi_detail SET 
                dokumen_temuan='$dT', dokumen_klaim='$dK', dokumen_konsisten='$dKon',
                wawancara_temuan='$wT', wawancara_klaim='$wK', wawancara_konsisten='$wKon',
                observasi_temuan='$oT', observasi_klaim='$oK', observasi_konsisten='$oKon',
                catatan_auditor='$cA', kategori_temuan='$kT', validitas='$vA', risiko_residual='$rR', kesimpulan='$kS'
            WHERE kodeaudit='$ka' AND no_risiko='$noRisiko'
        ");
    } else {
        $ok = mysqli_query($kon, "
            INSERT INTO audit_triangulasi_detail 
            (kodeaudit, no_risiko, dokumen_temuan, dokumen_klaim, dokumen_konsisten, 
             wawancara_temuan, wawancara_klaim, wawancara_konsisten,
             observasi_temuan, observasi_klaim, observasi_konsisten,
             catatan_auditor, kategori_temuan, validitas, risiko_residual, kesimpulan)
            VALUES 
            ('$ka', '$noRisiko', '$dT', '$dK', '$dKon', '$wT', '$wK', '$wKon', '$oT', '$oK', '$oKon', '$cA', '$kT', '$vA', '$rR', '$kS')
        ");
    }

    if($ok) {
        $hasDok = !empty($dT) || !empty($dK) || !empty($dKon);
        $hasWaw = !empty($wT) || !empty($wK) || !empty($wKon);
        $hasObs = !empty($oT) || !empty($oK) || !empty($oKon);
        $hasKes = !empty($cA) || !empty($kT) || !empty($vA) || !empty($kS);

        ajax_json_response([
            'success' => true,
            'hasDok' => $hasDok,
            'dokKon' => $dKon,
            'hasWaw' => $hasWaw,
            'wawKon' => $wKon,
            'hasObs' => $hasObs,
            'obsKon' => $oKon,
            'hasKes' => $hasKes,
            'katTemuan' => $kT,
            'validitas' => $vA,
            'risikoResidual' => $rR
        ]);
    } else {
        ajax_json_response(['success' => false, 'message' => mysqli_error($kon)]);
    }
}
