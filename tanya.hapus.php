<?php 
include 'koneksi.php';
$id=$_GET['id'];
$idp=$_GET['idp'];
mysqli_query($kon,"delete from pertanyaan where id='$id'");

?>
<script type="text/javascript">
	alert('Pertanyaan berhasil dihapus');
	window.location='tanya.buat?idp=<?php echo $idp;?>';
</script>