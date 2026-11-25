<?php 
include 'koneksi.php';
$ik=$_GET['ik'];
$et=$_GET['et'];
$ka=$_GET['ka'];
$b=mysqli_fetch_array(mysqli_query($kon,"select * from audit_kondisi where kodekondisi='$ik'"));
unlink("../assets/berkas/".$b['berkas']);
mysqli_query($kon,"update audit_kondisi set berkas='',status='Open' where kodekondisi='$ik'");

?>
<script type="text/javascript">
	alert("Berkas berhasil dihapus");
	window.location='audit.kondisi.detail?ik=<?php echo $ik;?>&ka=<?php echo $ka;?>&et=<?php echo $et;?>';
</script>