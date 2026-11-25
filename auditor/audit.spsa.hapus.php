<?php 
if(isset($_GET['hapus']))
{

	unlink('../assets/spsa/'.$_GET['berkas']);
	mysqli_query($kon,"update audit set spsa='' where kodeaudit='$_GET[ka]'");
	?>
	<script>
	alert('Berkas Laporan berhasil direset');
	window.location='audit.spsa?ka=<?=$_GET["ka"];?>&et=<?=$_GET["et"];?>';
	</script>
	<?php
}
?>
