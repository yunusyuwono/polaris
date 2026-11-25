<?php 
if(isset($_POST['simpan']))
{
	$idaudit=$_POST['idaudit'];
	$kodeaudit=$_POST['kodeaudit'];
	$koreksi=addslashes($_POST['koreksi']);
	$tindkoreksi=addslashes($_POST['tindkoreksi']);
	$jadpen1=addslashes($_POST['jadpen1']);
	/*$renperbaikan=addslashes($_POST['renperbaikan']);
	$jadpen1=addslashes($_POST['jadpen1']);
	$renpencegahan=addslashes($_POST['renpencegahan']);
	$jadpen2=addslashes($_POST['jadpen2']);*/
	$status='Open';

	$simpan=mysqli_query($kon,"UPDATE audit_kondisi SET 
		koreksi='$koreksi',
		tindkoreksi='$tindkoreksi',
		jadpen1='$jadpen1'
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