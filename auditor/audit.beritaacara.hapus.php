<?php 
if(isset($_GET['hapus']))
{

	unlink('../assets/beritaacara/'.$_GET['berkas']);
	mysqli_query($kon,"update audit set beritaacara='' where kodeaudit='$_GET[ka]'");
	?>
	<script>
	alert('Berkas Laporan berhasil direset');
	window.location='audit.beritaacara?ka=<?=$_GET["ka"];?>&et=<?=$_GET["et"];?>';
	</script>
	<?php
}
?>
