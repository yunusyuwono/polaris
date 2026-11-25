<?php
include "koneksi.php";
$pra=addslashes($_GET['pra']);
$simpan=mysqli_query($kon,"update praaudit set status='Belum Selesai' where id='$pra'");
if($simpan)
{
	?>
	<script>
	    alert('Pra Audit Berhasil direset.');
	    window.location='praaudit';
	</script>
	<?php
}
else
{
	?>
	<script>
	    alert('Pra Audit Gagal direset.');
	    window.location='praaudit';
	</script>
	<?php
}
?>