<?php 
include 'koneksi.php';
$ik=$_GET['ik'];
$et=$_GET['et'];
$ka=$_GET['ka'];
mysqli_query($kon,"update audit_kondisi set status='Close' where kodekondisi='$ik'");

?>
<script type="text/javascript">
	alert("Kondisi berhasil ditutuo");
	window.location='audit.kondisi.detail?ik=<?php echo $ik;?>&ka=<?php echo $ka;?>&et=<?php echo $et;?>';
</script>