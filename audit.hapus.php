<?php 
include "koneksi.php";
mysqli_query($kon,"delete from audit where kodeaudit='$_GET[ka]'");
?>
<script>
    alert('Audit berhasil dihapus');
    window.location='audit';
</script>