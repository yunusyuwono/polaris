<?php 
if(isset($_GET['hapus']))
{

	unlink('../assets/laporan/'.$_GET['berkas']);
	mysqli_query($kon,"update audit set berkas='' where kodeaudit='$_GET[ka]'");
	?>
	<script>
	alert('Berkas Laporan berhasil direset');
	window.location='audit.upload?ka=<?=$_GET["ka"];?>&et=<?=$_GET["et"];?>';
	</script>
	<?php
}
?>
