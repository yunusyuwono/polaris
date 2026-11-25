<?php 
if(isset($_POST['upload']))
{
	$idaudit=$_POST['idaudit'];
	$kodeaudit=$_POST['kodeaudit'];
	$oct=$_FILES['oc']['tmp_name'];
	$ocn=$_FILES['oc']['name'];
	$pat=$_FILES['pa']['tmp_name'];
	$pan=$_FILES['pa']['name'];
	$cct=$_FILES['cc']['tmp_name'];
	$ccn=$_FILES['cc']['name'];
	$absent=$_FILES['absen']['tmp_name'];
	$absenn=$_FILES['absen']['name'];

	$gka=mysqli_query($kon,"select * from audit_dok where kodeaudit='$kodeaudit'");
	if(mysqli_num_rows($gka)>0)
	{
		$upload=mysqli_query($kon,"update audit_dok set dokoc='$ocn',dokpa='$pan',dokcc='$ccn',dokabsen='$absenn' where kodeaudit='$kodeaudit'");
	}
	else
	{
		$upload=mysqli_query($kon,"insert into audit_dok (kodeaudit,dokoc,dokpa,dokcc,dokabsen) values ('$kodeaudit','$ocn','$pan','$ccn','$absenn')");
	}

	if($upload)
	{
		while($g=mysqli_fetch_array($gka))
		{
			unlink("../assets/auditdok/".$kodeaudit."_".$g['dokoc']);
			unlink("../assets/auditdok/".$kodeaudit."_".$g['dokpa']);
			unlink("../assets/auditdok/".$kodeaudit."_".$g['dokcc']);
			unlink("../assets/auditdok/".$kodeaudit."_".$g['dokabsen']);
		}

		move_uploaded_file($oct,"../assets/auditdok/".$kodeaudit."_".$ocn);
		move_uploaded_file($pat,"../assets/auditdok/".$kodeaudit."_".$pan);
		move_uploaded_file($cct,"../assets/auditdok/".$kodeaudit."_".$ccn);
		move_uploaded_file($absent,"../assets/auditdok/".$kodeaudit."_".$absenn);

		?>
		<div class="alert alert-success">
			Dokumen berhasil disimpan
		</div>
		<?php
	}
	else
	{
		?>
		<div class="alert alert-danger">
			Dokumen gagal disimpan
		</div>
		<?php
	}

}
?>