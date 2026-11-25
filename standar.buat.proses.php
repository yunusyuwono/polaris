<?php 
if(isset($_POST['simpan']))
{
	$nos=addslashes($_POST['nostandar']);
	$nas=addslashes($_POST['namastandar']);
	$nok=addslashes($_POST['nokategori']);
	$nak=addslashes($_POST['namakategori']);

	$simpan=mysqli_query($kon,"insert into kriteria(nostandar,namastandar,nokategori,namakategori) values ('$nos','$nas','$nok','$nak')");
	if($simpan)
	{
		?>
		<div class="alert alert-success mt-1">
			Standar Berhasil Dibuat.
		</div>
		<?php
	}
	else
	{
		?>
		<div class="alert alert-danger mt-1">
			Standar Gagal Dibuat <?php echo mysqli_error($kon);?>
		</div>
		<?php
	}
}
?>