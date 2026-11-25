
<!DOCTYPE html>
<html>
    <head>
        <title>POLARIS POLKESLU</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script> 
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css">
        <link href="../assets/POLARIS-HD.png" rel="icon">
    </head>
<div class="container">
	
<form method="post">
	<center>
	<div class="col-md-8 m-5">
		<div class="mb-4">
			<img src="../assets/POLARIS-HD.png" style="height: 60px; margin-right: 10px;" alt="POLARIS">
			<img src="../poltekkes.png" style="height: 60px;" alt="Poltekkes">
		</div>
		<h4>POLARIS</h4>
		<p>POLKESLU Penjaminan Mutu Terintegrasi<br>Poltekkes Kemenkes Bengkulu<hr></p>
		<div class="container row">
		    <div class="col-lg-5">
            	<div class="form-group">
            	    <label>Username</label>
            	<input type="text" name="username" placeholder="Username" autofocus >
            	</div>
        	</div>
        	<div class="col-lg-5">
            	<div class="form-group">
            	    <label>Password</label>
                    <input type="password" name="password" placeholder="Password">
            	</div>
        	</div>
            <div class="col-lg-2">
            	<button name="cari" class="btn btn-primary btn-block" onclick="showloader()">Login
            	</button>
        	</div>
        </div>
	</div>

	</center>
</form>
<?php 
if(isset($_POST['cari']))
{
	$us=$_POST['username'];
    $pw=$_POST['password'];
	include "koneksi.php";
	$query=mysqli_query($kon,"select * from admin where username='$us' and password='$pw'");
	if(mysqli_num_rows($query)>0)
	{
		$ga=mysqli_fetch_array($query);
		session_start();
		$_SESSION['id_pegawai']=$ga['id'];
		$_SESSION['nama']=$ga['nama'];
		$_SESSION['level']='Admin';
		header("location:./");
	}
	else
	{
		echo "<div class='alert alert-danger'>Data tidak ditemukan</div>";
	}
}
?>
</div>
<script type="text/javascript" src="assets/js/jquery1-12.min.js"></script>



    