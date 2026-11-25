<?php 
if(isset($_POST['simpan']))
{
	$kodecatatan=$_POST['kodecatatan'];
	//$telaah=addslashes($_POST['telaah']);
	$referensi=addslashes($_POST['referensi']);
	$aspek =addslashes($_POST['aspek']);
	$telaah=addslashes($_POST['telaah']);
	//$sesuai=addslashes($_POST['sesuai']);
	//$khusus=addslashes($_POST['khusus']);
	$status=addslashes($_POST['status']);
	
	$simpan=mysqli_query($kon,"UPDATE audit_catatan set referensi='$referensi', aspek='$aspek',telaah='$telaah',status='$status' where kodecatatan='$kodecatatan'");
	if($simpan)
	{
		?>
		<div class="alert alert-success">
			Catatan berhasil diperbarui
		</div>
		<?php
	}
	else
	{
		?>
		<div class="alert alert-danger">
			Catatan gagal disimpan <?php echo mysqli_error($kon);?>
		</div>
		<?php
	}
}
?>