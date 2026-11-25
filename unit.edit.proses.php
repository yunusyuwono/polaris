<?php 
if(isset($_POST['simpan']))
{
    $id = intval($_POST['id']);
    $nama = addslashes($_POST['nama']);
    $kepala = addslashes($_POST['kepala']);

    $simpan = mysqli_query($kon, "UPDATE unit SET nama='$nama', kepala='$kepala' WHERE id='$id'");
    if($simpan)
    {
        ?>
        <div class="alert alert-success mt-1">
            Unit Berhasil Diperbarui.
        </div>
        <script>setTimeout(function(){ window.location='master?hal=unit'; },800);</script>
        <?php
    }
    else
    {
        ?>
        <div class="alert alert-danger mt-1">
            Unit Gagal Diperbarui <?php echo mysqli_error($kon);?>
        </div>
        <?php
    }
}
?>
