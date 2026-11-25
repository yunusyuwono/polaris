<?php 
include "koneksi.php";
$kc=$_GET['kodecatatan'];
mysqli_query($kon,"delete from audit_catatan where kodecatatan='$kc'");
header("location:audit.catatan?ka=$_GET[ka]&et=$_GET[et]");
?>