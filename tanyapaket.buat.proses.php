<?php 
if(isset($_POST['simpan']))
{
	$namapaket=addslashes($_POST['namapaket']);
	$simpan=mysqli_query($kon,"insert into paketpertanyaan (namapaket,status) values ('$namapaket','Aktif')");
	if($simpan)
    {
        ?>
        <div class="alert alert-success mt-1">
            Paket Pertanyaan Berhasil Dibuat.
        </div>
        <?php
    }
    else
    {
        ?>
        <div class="alert alert-danger mt-1">
            Paket Pertanyaan Gagal Dibuat <?php echo mysqli_error($kon);?>
        </div>
        <?php
    }
}
?>