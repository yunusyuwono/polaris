<?php 
session_start();
if(empty($_SESSION['id_pegawai']))
{
    header("location:login");
}
include 'koneksi.php';
include 'audit.ui.php';

$ka = isset($_GET['ka']) ? mysqli_real_escape_string($kon, $_GET['ka']) : '';
?>
<title>Surat Pernyataan Selesai Audit #<?=$ka;?></title>
<body onload="window.print()">
<div style="width:100%" align="center">
<img src="assets/kop poltekkes.png" width="100%">
<center>
<b>SURAT PERNYATAAN SELESAI AUDIT MUTU INTERNAL</b><br>
Nomor : ____________________________<?=date('Y');?>


</center>
<br><br>
<p style="text-align: justify; justify-self: start;">
Yang bertanda tangan dibawah ini :
<table border="0">
<tr><td width="30%">Nama</td><td width="5%">:</td><td>Dr. Emy Yuliantini, SKM, MPH</td></tr>
<tr><td width="30%">NIP</td><td width="5%">:</td><td>197502061998032001</td></tr>
<tr><td width="30%">Jabatan</td><td width="5%">:</td><td>Ka. Pusat Penjaminan Mutu</td></tr>
</table>
<br>
Dengan ini menerangkan bahwa Tim Auditor sebagai berikut :<br>

<?php 
$tima = mysqli_query($kon, "SELECT auditor.*, user.nama FROM auditor LEFT JOIN user ON auditor.user=user.id WHERE auditor.kodeaudit='$ka'");
$no = 1;
while($ta = mysqli_fetch_array($tima))
{
    $namaAuditor = $ta['nama'] ?? '';
    if (empty($namaAuditor) && !empty($ta['user'])) {
        $p = polaris_get_pegawai_by_id($kon, $ta['user']);
        $namaAuditor = $p['nama_lengkap'] ?? '';
    }
    if (!empty($namaAuditor)) {
        echo $no . '. ' . htmlspecialchars($namaAuditor) . '<br>';
        $no++;
    }
}

$ta = mysqli_fetch_array(mysqli_query($kon, "SELECT audit.*, unit.nama as namaunit FROM audit JOIN unit ON audit.unit=unit.id WHERE audit.kodeaudit='$ka'"));
?>

<br>
Lingkup Audit : <?=$ta['namaunit'] ?? '-';?>

<br><br>
<?php 
if(($ta['lingkup'] ?? '') == 1)
{
    $rl = 'Akademik';
}
else
{
    $rl = 'Non-akademik';
}
?>
Telah menyelesaikan kegiatan Audit Mutu Internal <?=$rl;?> Poltekkes Kemenkes Bengkulu pada tanggal <?=audit_ui_date($ta['tglmulai'] ?? '');?> sampai dengan <?=audit_ui_date($ta['tglselesai'] ?? '');?>  pada Prodi/Unit <?=$ta['namaunit'] ?? '-';?> dengan hasil Laporan Audit Mutu Internal terlampir.
<br>
Demikian surat keterangan ini dibuat, untuk digunakan seperlunya
<br>
<br>
<table border="0" width="100%">
    <tr>
        <td width="50%"></td>
        <td width="50%" align="center">
            Bengkulu, <?=audit_ui_date(date('Y-m-d'));?><br>
            Ka. Pusat Penjaminan Mutu
            <br><br><br><br><br><br>
            <b>Dr. Emy Yuliantini, SKM., MPH</b><br>
            NIP. 197502061998032001
        </td>
    </tr>
</table>
</p>
</div>
</p>
</body>
<?php 
include 'foot.php';
?>
