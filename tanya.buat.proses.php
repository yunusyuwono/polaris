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
	$vbe=addslashes($_POST['berkas']);

	$simpan=mysqli_query($kon,"insert into pertanyaan(idpaket,kriteria,pertanyaan,noindikator,opsi1,opsi2,benar,vberkas) values ('$idpaket','$standar','$tanya','$noind','$opsi1','$opsi2','$benar','$vbe')");
	if($simpan)
	{
		?>
		<div class="alert alert-success mt-1">
			Pertanyaan Berhasil Dibuat.
		</div>
		<?php
	}
	else
	{
		?>
		<div class="alert alert-danger mt-1">
			Pertanyaan Gagal Dibuat <?php echo mysqli_error($kon);?>
		</div>
		<?php
	}
}
?>