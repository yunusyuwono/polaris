<?php
include "koneksi.php";
session_start();

$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

function sarmut_response($payload, $isAjax, $redirect) {
    if($isAjax) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload);
        exit;
    }
    header("location:".$redirect);
    exit;
}

$pra = (int)($_POST['pra'] ?? 0);
$sarmut = (int)($_POST['sarmut'] ?? 0);
$unit = (int)($_POST['unit'] ?? 0);
$triwulan = trim($_POST['triwulan'] ?? '');
$sub = (int)($_POST['subindikator'] ?? 0);
$nilai = trim($_POST['nilai'] ?? '');
$redirect = "sarmut.isi?pra=".$pra."&triwulan=".urlencode($triwulan);

if($pra <= 0 || $sarmut <= 0 || $unit <= 0 || $triwulan === '' || $sub <= 0) {
    sarmut_response(['success' => false, 'message' => 'Data Sarmut tidak lengkap.'], $isAjax, $redirect);
}

$nilaiColumn = mysqli_query($kon, "SHOW COLUMNS FROM sarmut_jawaban LIKE 'nilai'");
if($nilaiColumn && mysqli_num_rows($nilaiColumn) === 0) {
    mysqli_query($kon, "ALTER TABLE sarmut_jawaban ADD nilai VARCHAR(255) NULL AFTER idsubindikator");
}

$tw = mysqli_real_escape_string($kon, $triwulan);
$nilaiSql = mysqli_real_escape_string($kon, $nilai);
mysqli_query($kon, "DELETE FROM sarmut_jawaban WHERE idsarmut='$sarmut' AND idpic='$unit' AND triwulan='$tw' AND idsubindikator='$sub'");

if($nilai !== '') {
    mysqli_query($kon, "INSERT INTO sarmut_jawaban (idpic, idsarmut, idsubindikator, nilai, triwulan) VALUES ('$unit', '$sarmut', '$sub', '$nilaiSql', '$tw')");
}

$answeredQuery = mysqli_query($kon, "SELECT COUNT(DISTINCT idsubindikator) AS total FROM sarmut_jawaban WHERE idsarmut='$sarmut' AND idpic='$unit' AND triwulan='$tw' AND nilai IS NOT NULL AND nilai<>''");
$answered = $answeredQuery ? (int)(mysqli_fetch_assoc($answeredQuery)['total'] ?? 0) : 0;

sarmut_response([
    'success' => true,
    'message' => 'Sarmut berhasil disimpan.',
    'nilai' => $nilai,
    'subindikator' => $sub,
    'answered' => $answered
], $isAjax, $redirect);
