<?php 
include "koneksi.php";
$idtanya=$_GET['idtanya'];

$gb=mysqli_fetch_array(mysqli_query($kon,"select * from berkaspertanyaan where id='$idtanya'"));
mysqli_query($kon,"update led set idberkas=NULL where id='$idtanya'");
mysqli_query($kon,"delete from berkaspertanyaan where idpertanyaan='$idtanya'");
header("location:led.isi?pra=$_GET[pra]&soal=$_GET[soal]&no=$_GET[no]");
?>