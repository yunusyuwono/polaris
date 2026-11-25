<?php 
if(isset($_POST['simpan']))
{
	$nama=addslashes($_POST['nama']);

	$simpan=mysqli_query($kon,"insert into unit(nama) values ('$nama')");
	if($simpan)
	{
		?>
		<div class="alert alert-success mt-1">
			Unit Berhasil Dibuat.
		</div>
		<?php
	}
	else
	{
		?>
		<div class="alert alert-danger mt-1">
			Unit Gagal Dibuat <?php echo mysqli_error($kon);?>
		</div>
		<?php
	}
}
?>