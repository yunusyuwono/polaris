<?php 
if(isset($_POST['simpan']))
{
	$kodeaudit=$_POST['kodeaudit'];
	//$telaah=addslashes($_POST['telaah']);
	$referensi=addslashes($_POST['referensi']);
	$aspek =addslashes($_POST['aspek']);
	//$telaah=addslashes($_POST['telaah']);
	//$sesuai=addslashes($_POST['sesuai']);
	//$khusus=addslashes($_POST['khusus']);
	//$status=addslashes($_POST['status']);
	
	$simpan=mysqli_query($kon,"insert into audit_catatan (kodeaudit,kodecatatan,referensi,aspek) values('$kodeaudit','','$referensi','$aspek')");
	if($simpan)
	{
		$getctn=mysqli_fetch_array(mysqli_query($kon,"select * from audit_catatan where kodeaudit='$kodeaudit' and kodecatatan=''"));
		$kodecatatan=$kodeaudit.'-C'.sprintf('%03s',$getctn['id']);
		mysqli_query($kon,"update audit_catatan set kodecatatan='$kodecatatan' where id='$getctn[id]'")
		?>
		<div class="alert alert-success">
			Catatan berhasil disimpan
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
