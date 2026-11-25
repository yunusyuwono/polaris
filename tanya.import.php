<?php 
include 'nav.php';

$paket=$_GET['idp'];
$p=mysqli_fetch_array(mysqli_query($kon,"SELECT * FROM paketpertanyaan where id='$paket'"));
?>
<div id="main">
    <header class="">
        <a href="#" class="burger-btn d-block p-2">
            <i class="fas fa-bars"></i> Menu 
        </a>
    </header> 
    <div class="page-content">
        <section class="row">
            <div class="col-12 col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="font-semibold">Upload Paket Pertanyaan <?=$p['namapaket'];?></h4>
                    </div>
                    <div class="card-body px-3">
                        <div class="row">
                            <div class="col-md-12">
                                <div align="right">
                                <a class="btn btn-primary btn-sm" href="tanya"><i class="fas fa-caret-left"></i> Kembali</a>
                                </div>
                                <br>
                                <?php 
                                include "tanya.import.proses.php";
                                ?>
                                <form method="post" enctype="multipart/form-data">
                                    <div class="form-group">
                                        <label>File berformat .xls, .xlsx, .csv</label>
                                        <input type="file" name="upl" class="form-control">
                                        <button name="upload" class="btn btn-primary">Upload</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>
<?php 
include 'foot.php';
?>