<?php 
if(isset($_POST['simpan']))
{
	$idaudit=$_POST['idaudit'];
	$kodeaudit=$_POST['kodeaudit'];
	$kondisi=addslashes($_POST['kondisi']);
	$deskripsi=addslashes($_POST['deskripsi']);
	//$penyebab=addslashes($_POST['penyebab']);
	//$akibat=addslashes($_POST['akibat']);
	$rekomendasi=addslashes($_POST['rekomendasi']);
	$tanggapan=addslashes($_POST['tanggapan']);
	/*$renperbaikan=addslashes($_POST['renperbaikan']);
	$jadpen1=addslashes($_POST['jadpen1']);
	$renpencegahan=addslashes($_POST['renpencegahan']);
	$jadpen2=addslashes($_POST['jadpen2']);*/
	$kategori=addslashes($_POST['kategori']);
	$referensi=addslashes($_POST['referensi']);
	$status=addslashes($_POST['status']);

	$simpan=mysqli_query($kon,"insert into audit_kondisi (
		idaudit,kodeaudit,kodekondisi,kondisi,jeniskondisi,deskripsi,penyebab,akibat,
		rekomendasi,tanggapan,renperbaikan,jadpen1,renpencegahan,jadpen2,status,
		kategori,referensi,berkas,koreksi,tindkoreksi
	) values (
		'$idaudit','$kodeaudit','','$kondisi','Temuan','$deskripsi','','',
		'$rekomendasi','$tanggapan','','1000-01-01','','1000-01-01','$status',
		'$kategori','$referensi','','',''
	)");

	if($simpan)
	{
		$getkon=mysqli_fetch_array(mysqli_query($kon,"select * from audit_kondisi where kodeaudit='$kodeaudit' and kodekondisi=''"));
		$kodekondisi=$kodeaudit.'-T'.sprintf('%03s',$getkon['id']);
		mysqli_query($kon,"update audit_kondisi set kodekondisi='$kodekondisi' where id='$getkon[id]'")
		?>
		<div class="alert alert-success">
			Temuan berhasil disimpan
		</div>
		<?php
	}
	else
	{
		?>
		<div class="alert alert-danger">
			Temuan gagal disimpan <?php echo mysqli_error($kon);?>
		</div>
		<?php
	}
}
?>
