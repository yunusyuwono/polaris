<?php 
if(isset($_POST['upload']))
{
	$idaudit=$_POST['idaudit'];
	$kodeaudit=$_POST['kodeaudit'];
	$upt=$_FILES['ami']['tmp_name'];
	$upn=$_FILES['ami']['name'];
	

	$gka=mysqli_query($kon,"select * from audit where kodeaudit='$kodeaudit'");
	$nupn=$kodeaudit.'beritaacara_'.$upn;
	$upload=mysqli_query($kon,"update audit set beritaacara='$nupn' where kodeaudit='$kodeaudit'");
	
	$gk=mysqli_fetch_array($gka);
	if($upload)
	{
		if(!empty($gk['beritaacara']))
		{
			unlink("../assets/beritaacara/".$nupn);
		}
		move_uploaded_file($upt,"../assets/beritaacara/".$nupn);

		?>
		<div class="alert alert-success">
			Berita Acara berhasil disimpan
		</div>
		<?php
	}
	else
	{
		?>
		<div class="alert alert-danger">
			Berita Acara gagal disimpan
		</div>
		<?php
	}

}
?>