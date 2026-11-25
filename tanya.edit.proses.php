<?php 
if(isset($_POST['simpan']))
{
	$idpaket=addslashes($_POST['idpaket']);
	$standar=addslashes($_POST['standar']);
	$tanya=addslashes($_POST['pertanyaan']);
	$opsi1=addslashes($_POST['opsi1']);
	$opsi2=addslashes($_POST['opsi2']);
	$benar=addslashes($_POST['benar']);
	$noind=addslashes($_POST['noindikator']);
	
	if(isset($_POST['berkas']))
	{
		$vbe=addslashes($_POST['berkas']);
	}
	else
	{
		$vbe=0;
	}
	$idtanya=addslashes($_POST['idtanya']);

	$simpan=mysqli_query($kon,"update pertanyaan set idpaket='$idpaket',kriteria='$standar',pertanyaan='$tanya',opsi1='$opsi1',opsi2='$opsi2',benar='$benar',noindikator='$noind',vberkas='$vbe' where id='$idtanya'");
	if($simpan)
	{
		?>
		<div class="alert alert-success mt-1">
			Pertanyaan Berhasil diperbarui. <a class="btn-link" href="<?php echo $_SERVER['REQUEST_URI'];?>">Klik untuk refresh</a>
		</div>
		<?php
	}
	else
	{
		?>
		<div class="alert alert-danger mt-1">
			Pertanyaan Gagal diperbarui <?php echo mysqli_error($kon);?>
		</div>
		<?php
	}
}
?>
