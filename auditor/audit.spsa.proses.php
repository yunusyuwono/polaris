<?php 
if(isset($_POST['upload']))
{
	$idaudit=$_POST['idaudit'];
	$kodeaudit=$_POST['kodeaudit'];
	$upt=$_FILES['ami']['tmp_name'];
	$upn=$_FILES['ami']['name'];
	

	$gka=mysqli_query($kon,"select * from audit where kodeaudit='$kodeaudit'");
	$nupn=$kodeaudit.'SPSA_'.$upn;
	$upload=mysqli_query($kon,"update audit set spsa='$nupn' where kodeaudit='$kodeaudit'");
	
	$gk=mysqli_fetch_array($gka);
	if($upload)
	{
		if(!empty($gk['spsa']))
		{
			unlink("../assets/spsa/".$nupn);
		}
		move_uploaded_file($upt,"../assets/spsa/".$nupn);

		?>
		<div class="alert alert-success">
			Surat Pernyaan Selesai Audit berhasil disimpan
		</div>
		<?php
	}
	else
	{
		?>
		<div class="alert alert-danger">
			Surat Pernyaan Selesai Audit gagal disimpan
		</div>
		<?php
	}

}
?>