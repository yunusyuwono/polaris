<?php
include "koneksi.php";
session_start();

mysqli_query($kon, "CREATE TABLE IF NOT EXISTS `iso_jawaban` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_praaudit` INT NOT NULL,
  `id_iso` INT NOT NULL,
  `id_unit` INT NOT NULL,
  `id_isoindikator` INT NOT NULL,
  `jawaban` VARCHAR(30) NOT NULL,
  `catatan` TEXT NULL,
  `link_dokumen` TEXT NULL,
  `entri` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_iso_unit_indikator` (`id_praaudit`,`id_unit`,`id_iso`,`id_isoindikator`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$checkCol = mysqli_query($kon, "SHOW COLUMNS FROM `iso_jawaban` LIKE 'link_dokumen'");
if(mysqli_num_rows($checkCol) == 0){
    mysqli_query($kon, "ALTER TABLE `iso_jawaban` ADD `link_dokumen` TEXT NULL AFTER `catatan`");
}

$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

function iso_response($payload, $isAjax, $redirect) {
    if($isAjax) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload);
        exit;
    }
    header("location:".$redirect);
    exit;
}

$pra = (int)($_POST['pra'] ?? 0);
$iso = (int)($_POST['iso'] ?? 0);
$unit = (int)($_POST['unit'] ?? 0);
$indikator = (int)($_POST['indikator'] ?? 0);
$nilai = trim($_POST['jawaban'] ?? '');
$catatan = trim($_POST['catatan'] ?? '');
$link_dokumen = trim($_POST['link_dokumen'] ?? '');
$redirect = "iso.isi?pra=".$pra;

if($pra <= 0 || $iso <= 0 || $unit <= 0 || $indikator <= 0) {
    iso_response(['success' => false, 'message' => 'Data ISO tidak lengkap.'], $isAjax, $redirect);
}

$nilaiSql = mysqli_real_escape_string($kon, $nilai);
$catatanSql = mysqli_real_escape_string($kon, $catatan);
$linkSql = mysqli_real_escape_string($kon, $link_dokumen);

if($nilai === '') {
    mysqli_query($kon, "DELETE FROM iso_jawaban WHERE id_praaudit='$pra' AND id_iso='$iso' AND id_unit='$unit' AND id_isoindikator='$indikator'");
} else {
    mysqli_query($kon, "
        INSERT INTO iso_jawaban (id_praaudit, id_iso, id_unit, id_isoindikator, jawaban, catatan, link_dokumen)
        VALUES ('$pra', '$iso', '$unit', '$indikator', '$nilaiSql', '$catatanSql', '$linkSql')
        ON DUPLICATE KEY UPDATE jawaban=VALUES(jawaban), catatan=VALUES(catatan), link_dokumen=VALUES(link_dokumen), entri=CURRENT_TIMESTAMP
    ");
}

$answeredQuery = mysqli_query($kon, "SELECT COUNT(DISTINCT id_isoindikator) AS total FROM iso_jawaban WHERE id_praaudit='$pra' AND id_iso='$iso' AND id_unit='$unit' AND jawaban<>''");
$answered = $answeredQuery ? (int)(mysqli_fetch_assoc($answeredQuery)['total'] ?? 0) : 0;

iso_response([
    'success' => true,
    'message' => 'ISO berhasil disimpan.',
    'indikator' => $indikator,
    'answered' => $answered
], $isAjax, $redirect);
