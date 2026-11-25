<?php
include 'koneksi.php';
$id = isset($_POST['id'])? intval($_POST['id']) : 0;
$title = mysqli_real_escape_string($kon, $_POST['title'] ?? '');
$year = intval($_POST['year'] ?? 0);
if($id){
    mysqli_query($kon, "UPDATE sarmut SET title='$title', year='$year', updated_at=NOW() WHERE id='$id'");
} else {
    mysqli_query($kon, "INSERT INTO sarmut (title, year, created_at) VALUES ('$title', '$year', NOW())");
}
header('Location: sarmut');
exit;
?>
