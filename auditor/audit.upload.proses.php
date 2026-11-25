<?php 
if(isset($_POST['upload']))
{
	$idaudit=$_POST['idaudit'];
	$kodeaudit=$_POST['kodeaudit'];
	$upt=$_FILES['ami']['tmp_name'];
	$upn=$_FILES['ami']['name'];
	

	$gka=mysqli_query($kon,"select * from audit where kodeaudit='$kodeaudit'");
	$nupn=$kodeaudit.'L_'.$upn;
	$upload=mysqli_query($kon,"update audit set berkas='$nupn' where kodeaudit='$kodeaudit'");
	
	$gk=mysqli_fetch_array($gka);
	if($upload)
	{
		if(!empty($gk['berkas']))
		{
			unlink("../assets/laporan/".$nupn);
		}
		move_uploaded_file($upt,"../assets/laporan/".$nupn);

		?>
		<div class="alert alert-success">
			Laporan berhasil disimpan
		</div>
		<?php
	}
	else
	{
		?>
		<div class="alert alert-danger">
			Laporan gagal disimpan
		</div>
		<?php
	}

}
?>