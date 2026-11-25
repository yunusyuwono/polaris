<?php 
include "koneksi.php";
$ka=$_GET['ka'];
$et=$_GET['et'];
$ik=$_GET['ik'];

mysqli_query($kon,"delete from audit_kondisi where kodekondisi='$ik'");
?>
<script>
    alert('Temuan berhasil dihapus');
    window.location='audit.kondisi?ka=<?=$ka;?>&et=<?=$et;?>';
</script>