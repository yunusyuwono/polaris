<?php 
if(isset($_POST['simpan']))
{
	$idaudit=$_POST['idaudit'];
	$kodeaudit=$_POST['kodeaudit'];
	$kondisi=addslashes($_POST['kondisi']);
	$deskripsi=addslashes($_POST['deskripsi']);
	$rekomendasi=addslashes($_POST['rekomendasi']);
	$tanggapan=addslashes($_POST['tanggapan']);
	/*$renperbaikan=addslashes($_POST['renperbaikan']);
	$jadpen1=addslashes($_POST['jadpen1']);
	$renpencegahan=addslashes($_POST['renpencegahan']);
	$jadpen2=addslashes($_POST['jadpen2']);*/
	$kategori=addslashes($_POST['kategori']);
	$referensi=addslashes($_POST['referensi']);
	$status=$_POST['status'];

	$simpan=mysqli_query($kon,"UPDATE audit_kondisi SET 
		kondisi='$kondisi',
		referensi='$referensi',
		deskripsi='$deskripsi',
		rekomendasi='$rekomendasi',
		tanggapan='$tanggapan',
		kategori='$kategori',
		status='$status'
		where id='$idaudit'");

	if($simpan)
	{
		?>
		<div class="alert alert-success">
			Temuan berhasil diperbarui
		</div>
		<?php
	}
	else
	{
		?>
		<div class="alert alert-danger">
			Temuan gagal diperbarui <?php echo mysqli_error($kon);?>
		</div>
		<?php
	}
}
?>