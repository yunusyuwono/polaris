<?php 
include "koneksi.php";
$id=$_GET['id'];
$do=$_GET['do'];
mysqli_query($kon,"update paketpertanyaan set status='$do' where id='$id'");

?>
<script type="text/javascript">
	alert('Status berhasil diubah');
	window.location='tanya';
</script>