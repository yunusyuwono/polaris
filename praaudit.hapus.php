<?php
include "koneksi.php";
$pra=addslashes($_GET['pra']);
$simpan=mysqli_query($kon,"delete from praaudit where id='$pra'");
if($simpan)
{
	?>
	<script>
	    alert('Praaudit berhasil dihapus');
	    window.location='praaudit';
	</script>
	<?php
}
else
{
	?>
	<script>
	    alert('Praaudit gagal dihapus');
	    window.location='praaudit';
	</script>
	<?php
}
?>