<?php
include 'koneksi.php';
$id = isset($_GET['id'])? intval($_GET['id']) : 0;
if($id){
    mysqli_query($kon, "DELETE FROM sarmut WHERE id='$id'");
}
header('Location: sarmut');
exit;
?>
