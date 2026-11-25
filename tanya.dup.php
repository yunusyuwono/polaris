<?php 
include 'nav.php';

$dup=mysqli_query($kon,"SELECT * from pertanyaan where idpaket='2'");
while($d=mysqli_fetch_array($dup))
{
	mysqli_query($kon,"INSERT into pertanyaan (idpaket,kriteria,pertanyaan,noindikator,opsi1,opsi2,benar,objek,vberkas) values ('41','$d[kriteria]','$d[pertanyaan]','$d[noindikator]','$d[opsi1]','$d[opsi2]','$d[benar]','$d[objek]','$d[vberkas]')");
}