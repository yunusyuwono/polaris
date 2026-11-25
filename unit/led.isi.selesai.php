<?php 
include 'koneksi.php';
$pra=$_GET['pra'];

mysqli_query($kon,"update praaudit set status='Selesai' where id='$pra'");
header("location:led");
?>