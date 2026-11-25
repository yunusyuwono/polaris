<?php 
if(isset($_POST['simpan']))
{
	$idaudit=$_POST['idaudit'];
	$kodeaudit=$_POST['kodeaudit'];
	$kondisi=addslashes($_POST['kondisi']);
	

	$simpan=mysqli_query($kon,"insert into audit_kondisi (
		idaudit,kodeaudit,kodekondisi,kondisi,jeniskondisi,deskripsi,penyebab,akibat,
		rekomendasi,tanggapan,renperbaikan,jadpen1,renpencegahan,jadpen2,status,
		kategori,referensi,berkas,koreksi,tindkoreksi
	) values (
		'$idaudit','$kodeaudit','','$kondisi','Positif','','','',
		'','','','1000-01-01','','1000-01-01','',
		'','','','',''
	)");

	if($simpan)
	{
		$getkon=mysqli_fetch_array(mysqli_query($kon,"select * from audit_kondisi where kodeaudit='$kodeaudit' and kodekondisi=''"));
		$kodekondisi=$kodeaudit.'-P'.sprintf('%03s',$getkon['id']);
		mysqli_query($kon,"update audit_kondisi set kodekondisi='$kodekondisi' where id='$getkon[id]'")
		?>
		<div class="alert alert-success">
			Kondisi berhasil disimpan
		</div>
		<?php
	}
	else
	{
		?>
		<div class="alert alert-danger">
			Kondisi gagal disimpan <?php echo mysqli_error($kon);?>
		</div>
		<?php
	}
}
?>
