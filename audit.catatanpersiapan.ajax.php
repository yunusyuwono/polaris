<?php
include 'koneksi.php';
if(session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Simple JSON responder
function ajax_json_response($payload) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload);
    exit;
}

$action = $_GET['action'] ?? '';
$ka = mysqli_real_escape_string($kon, $_POST['kodeaudit'] ?? $_GET['kodeaudit'] ?? '');
$noRisiko = mysqli_real_escape_string($kon, $_POST['no_risiko'] ?? $_GET['no_risiko'] ?? '');

if (empty($ka) || empty($noRisiko)) {
    ajax_json_response(['success' => false, 'message' => 'Kode audit atau nomor risiko tidak valid.']);
}

if ($action === 'get') {
    // Fetch standar_diaudit from audit_persiapan header as default
    $qHeader = mysqli_query($kon, "SELECT standar_diaudit FROM audit_persiapan WHERE kodeaudit='$ka'");
    $headerStandar = $qHeader && mysqli_num_rows($qHeader) > 0 ? mysqli_fetch_assoc($qHeader)['standar_diaudit'] : '';

    // Fetch telaah
    $telaah = [];
    $qT = mysqli_query($kon, "SELECT * FROM audit_persiapan_telaah WHERE kodeaudit='$ka' AND no_risiko='$noRisiko' ORDER BY id ASC");
    if ($qT) {
        while ($row = mysqli_fetch_assoc($qT)) {
            $telaah[] = [
                'dokumen_ditelaah' => $row['dokumen_ditelaah'],
                'temuan' => $row['temuan'],
                'kesesuaian' => $row['kesesuaian'],
                'pertanyaan_lapangan' => $row['pertanyaan_lapangan']
            ];
        }
    }
    
    // Fetch standar_butir from first telaah record or use header as default
    $qSt = mysqli_query($kon, "SELECT standar_butir FROM audit_persiapan_telaah WHERE kodeaudit='$ka' AND no_risiko='$noRisiko' LIMIT 1");
    $standarButir = $qSt && mysqli_num_rows($qSt) > 0 ? mysqli_fetch_assoc($qSt)['standar_butir'] : $headerStandar;

    // Fetch hipotesis
    $qH = mysqli_query($kon, "SELECT * FROM audit_persiapan_hipotesis WHERE kodeaudit='$ka' AND no_risiko='$noRisiko' LIMIT 1");
    $hipotesis = '';
    $prediksi = 'OB';
    if ($qH && mysqli_num_rows($qH) > 0) {
        $hRow = mysqli_fetch_assoc($qH);
        $hipotesis = $hRow['hipotesis'];
        $prediksi = $hRow['prediksi_hasil'];
    }

    ajax_json_response([
        'success' => true,
        'standar_butir' => $standarButir,
        'telaah' => $telaah,
        'hipotesis' => $hipotesis,
        'prediksi' => $prediksi
    ]);
}

if ($action === 'save') {
    $standarButir = mysqli_real_escape_string($kon, $_POST['standar_butir'] ?? '');
    $hipotesisText = mysqli_real_escape_string($kon, $_POST['hipotesis_text'] ?? '');
    $prediksi = mysqli_real_escape_string($kon, $_POST['prediksi'] ?? 'OB');
    
    // Start transaction or safe edits
    mysqli_query($kon, "DELETE FROM audit_persiapan_telaah WHERE kodeaudit='$ka' AND no_risiko='$noRisiko'");
    mysqli_query($kon, "DELETE FROM audit_persiapan_hipotesis WHERE kodeaudit='$ka' AND no_risiko='$noRisiko'");

    // Save Telaah
    $savedAnyTelaah = false;
    if (isset($_POST['telaah']) && is_array($_POST['telaah'])) {
        foreach ($_POST['telaah'] as $row) {
            $dok = mysqli_real_escape_string($kon, $row['dokumen_ditelaah'] ?? '');
            $tem = mysqli_real_escape_string($kon, $row['temuan'] ?? '');
            $kes = mysqli_real_escape_string($kon, $row['kesesuaian'] ?? 'YA');
            $pl = mysqli_real_escape_string($kon, $row['pertanyaan_lapangan'] ?? '');
            
            if (!empty($dok) || !empty($tem)) {
                $savedAnyTelaah = true;
                mysqli_query($kon, "
                    INSERT INTO audit_persiapan_telaah 
                    (kodeaudit, no_risiko, standar_butir, dokumen_ditelaah, temuan, kesesuaian, pertanyaan_lapangan) 
                    VALUES 
                    ('$ka', '$noRisiko', '$standarButir', '$dok', '$tem', '$kes', '$pl')
                ");
            }
        }
    }

    // Save Hipotesis if not empty
    $savedHipotesis = false;
    if (!empty($hipotesisText)) {
        $savedHipotesis = true;
        mysqli_query($kon, "
            INSERT INTO audit_persiapan_hipotesis 
            (kodeaudit, no_risiko, hipotesis, prediksi_hasil) 
            VALUES 
            ('$ka', '$noRisiko', '$hipotesisText', '$prediksi')
        ");
    }

    // Determine status
    $status_telaah = $savedAnyTelaah ? 'Terisi' : 'Belum';
    $status_hipotesis = $savedHipotesis ? 'Terisi' : 'Belum';

    ajax_json_response([
        'success' => true,
        'message' => 'Bagian 2 & 3 berhasil disimpan untuk ' . $noRisiko,
        'status_telaah' => $status_telaah,
        'status_hipotesis' => $status_hipotesis
    ]);
}

ajax_json_response(['success' => false, 'message' => 'Aksi tidak dikenal.']);
