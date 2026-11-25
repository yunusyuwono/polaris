<?php
// filepath: c:\xampp\htdocs\sipenmaru\admin_sipenmaru\administrator\081_export_excel_data.php
include "koneksi.php";
header("Content-type: application/vnd-ms-excel");
header("Content-Disposition: attachment; filename=pertanyaan $_GET[idp].xls");

$query = mysqli_query($kon, "SELECT * FROM pertanyaan where idpaket='$_GET[idp]' ORDER BY kriteria ASC");
?>
<table border="1">
    <tr>
        <th>No.</th>
        <th>ID Paket</th>
        <th>Kriteria</th>
        <th>pertanyaan</th>
        <th>No Indikator</th>
        <th>Opsi 1</th>
        <th>Opsi 2</th>
        <th>Benar</th>
        <th>Objek</th>
        <th>Vberkas</th>
    </tr>
    <?php
    $i = 1;
    while($a = mysqli_fetch_array($query)) {
    ?>
    <tr>
        <td><?php echo $i; ?></td>
        <td><?php echo $a['idpaket']; ?></td>
        <td><?php echo $a['kriteria']; ?></td>
        <td><?php echo $a['pertanyaan']; ?></td>
        <td><?php echo $a['noindikator']; ?></td>
        <td><?php echo $a['opsi1']; ?></td>
        <td><?php echo $a['opsi2']; ?></td>
        <td><?php echo $a['benar']; ?></td>
        <td><?php echo $a['objek']; ?></td>
        <td><?php echo $a['vberkas']; ?></td>
    </tr>
    <?php $i++; } ?>
</table>