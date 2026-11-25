<?php
include "koneksi.php";

$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

function led_json_response($payload, $isAjax, $redirect = '') {
	if($isAjax) {
		header('Content-Type: application/json; charset=utf-8');
		echo json_encode($payload);
		exit;
	}

	if(!empty($redirect)) {
		header("location:".$redirect);
		exit;
	}

	echo $payload['message'] ?? 'Terjadi kesalahan';
	exit;
}

$soal=(int)($_POST['soal'] ?? 0);
$pra=(int)($_POST['praaudit'] ?? 0);
$no=(int)($_POST['no'] ?? 1);
$jaw=trim($_POST['jawaban'] ?? '');
$berkas=trim($_POST['berkas'] ?? '');
$isiRisiko=trim($_POST['isi_risiko'] ?? '');
$isiDampak=trim($_POST['isi_dampak'] ?? '');
$dampak=(int)($_POST['dampak'] ?? 0);
$probabilitas=(int)($_POST['probabilitas'] ?? 0);
$risiko=$dampak * $probabilitas;
$usulanMitigasi=trim($_POST['usulan_mitigasi'] ?? '');
$redirect = "led.isi?pra=".$pra."&soal=".$soal."&no=".$no;

if($soal <= 0 || $pra <= 0 || $jaw === '') {
	led_json_response(['success' => false, 'message' => 'Data jawaban tidak lengkap.'], $isAjax, $redirect);
}

if($isiRisiko === '' || $isiDampak === '' || $usulanMitigasi === '') {
	led_json_response(['success' => false, 'message' => 'Risiko, dampak, dan usulan mitigasi wajib diisi.'], $isAjax, $redirect);
}

if($dampak < 1 || $dampak > 5 || $probabilitas < 1 || $probabilitas > 5) {
	led_json_response(['success' => false, 'message' => 'Dampak dan probabilitas wajib diisi dengan nilai 1 sampai 5.'], $isAjax, $redirect);
}

$ceksoal=mysqli_query($kon,"select * from pertanyaan where id='$soal'");
$cs=mysqli_fetch_array($ceksoal);

if(!$cs) {
	led_json_response(['success' => false, 'message' => 'Soal tidak ditemukan.'], $isAjax, $redirect);
}

$koreksi = ((string)$cs['benar'] === (string)$jaw) ? 1 : 0;
$berkasSql = mysqli_real_escape_string($kon, $berkas);
$isiRisikoSql = mysqli_real_escape_string($kon, $isiRisiko);
$isiDampakSql = mysqli_real_escape_string($kon, $isiDampak);
$usulanMitigasiSql = mysqli_real_escape_string($kon, $usulanMitigasi);
$jawSql = mysqli_real_escape_string($kon, $jaw);

$cekjaw=mysqli_query($kon,"select * from led where idpraaudit='$pra' and idpertanyaan='$soal'");
if(mysqli_num_rows($cekjaw)>0)
{
	$cj=mysqli_fetch_array($cekjaw);
	$cmd=mysqli_query($kon,"update led set jawab='$jawSql',koreksi='$koreksi', berkas='$berkasSql', isi_risiko='$isiRisikoSql', isi_dampak='$isiDampakSql', dampak='$dampak', probabilitas='$probabilitas', risiko='$risiko', usulan_mitigasi='$usulanMitigasiSql' where id='".(int)$cj['id']."'");
}
else
{
	$cmd=mysqli_query($kon,"insert into led (idpraaudit,idpertanyaan,jawab,koreksi,berkas,isi_risiko,isi_dampak,dampak,probabilitas,risiko,usulan_mitigasi)
					values ('$pra','$soal','$jawSql','$koreksi','$berkasSql','$isiRisikoSql','$isiDampakSql','$dampak','$probabilitas','$risiko','$usulanMitigasiSql')");
}

if(!$cmd)
{
	led_json_response(['success' => false, 'message' => mysqli_error($kon)], $isAjax, $redirect);
}

$praData = mysqli_fetch_assoc(mysqli_query($kon, "select paket from praaudit where id='$pra'"));
$total = 0;
$answered = 0;
if($praData) {
	$total = mysqli_num_rows(mysqli_query($kon,"select id from pertanyaan where idpaket='".(int)$praData['paket']."'"));
	$answered = mysqli_num_rows(mysqli_query($kon,"select id from led where idpraaudit='$pra'"));
}
$percent = $total > 0 ? min(100, round(($answered / $total) * 100)) : 0;
$ops = $jaw === '1' ? 'A' : 'B';

led_json_response([
	'success' => true,
	'message' => 'Jawaban berhasil disimpan.',
	'soal' => $soal,
	'no' => $no,
	'jawaban' => $jaw,
	'ops' => $ops,
	'isi_risiko' => $isiRisiko,
	'isi_dampak' => $isiDampak,
	'dampak' => $dampak,
	'probabilitas' => $probabilitas,
	'risiko' => $risiko,
	'usulan_mitigasi' => $usulanMitigasi,
	'answered' => $answered,
	'total' => $total,
	'percent' => $percent
], $isAjax, $redirect);
?>
