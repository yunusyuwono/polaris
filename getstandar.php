<?php
include 'koneksi.php';
$idstandar = $_GET['idstandar'];
$query = mysqli_query($kon, "select * from kriteria where id='$idstandar'");

$b = mysqli_fetch_array($query);
if($idstandar != NULL)
{
$data = array(
            'nostandar'      =>  $b['nostandar'],
            'namastandar'      =>  $b['namastandar'],
        );    
}
else
{
    $data = array(
        'message' => 'Pilih Standar dulu'
            );
}

 echo json_encode($data);
?>