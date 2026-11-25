<?php 
include 'nav.php';
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
                        <h4 class="font-semibold">Dokumentasi Audit #<?php echo $_GET['ka'];?></h4>
                    </div>
                    <div class="card-body px-3">
                        <?php 
                        include "audit.validasi.php";

                        ?>
                        <a class="btn btn-primary btn-sm" href="audit.progres?ka=<?php echo $_GET['ka'];?>&et=<?php echo $_GET['et'];?>"><i class="fas fa-caret-left"></i> Kembali</a>
                        <div class="collapse" id="tambah">
                            <form method="post" enctype="multipart/form-data">
                                <input type="hidden" name="idaudit" value="<?php echo $cto['id'];?>">
                                <input type="hidden" name="kodeaudit" value="<?php echo $cto['kodeaudit'];?>">
                                <div class="form-group">
                                    <label>Opening Ceremony </label>
                                    <input type="file" name="oc" class="form-control" required accept="image/jpeg,image/png,image/jpg">
                                </div>
                                <div class="form-group">
                                    <label>Pelaksanaan Audit </label>
                                    <input type="file" name="pa" class="form-control" required accept="image/jpeg,image/png,image/jpg">
                                </div>
                                <div class="form-group">
                                    <label>Closing Ceremony </label>
                                    <input type="file" name="cc" class="form-control" required accept="image/jpeg,image/png,image/jpg">
                                </div>
                                <div class="form-group">
                                    <label>Daftar Hadir </label>
                                    <input type="file" name="absen" class="form-control" required accept="image/jpeg,image/png,image/jpg">
                                </div>
                                <div class="form-group">
                                    <small class="text-danger">
                                        * Ukuran file masing-masing berukuran maksimal 1 Mb
                                    </small>
                                </div>
                                <div class="form-group">
                                    <button class="btn btn-sm btn-primary" name="upload"><i class="fas fa-upload"></i> Upload Dokumen</button>
                                </div>
                            </form>
                        </div>

                        <?php 
                        $gk=mysqli_fetch_array(mysqli_query($kon,"select * from audit_dok where kodeaudit='$_GET[ka]'"));
                        ?>
                        <button onclick="printDiv('printdok')" class="btn btn-primary btn-sm" ><i class="fas fa-print"></i></button>
                        <hr>
                        <div id="printdok" class="row">
                            <div class="container">
                                <b>Dokumentasi Audit <?=$_GET['ka'];?></b>
                            </div>
                            <?php if($gk): ?>
                            <p class="col-sm-6">Opening Ceremony <br> 
                                <img src="../assets/auditdok/<?=$_GET['ka'].'_'.$gk['dokoc'];?>" height="200">
                            </p>
                            <p class="col-sm-6">Pelaksanaan Audit <br> 
                                <img src="../assets/auditdok/<?=$_GET['ka'].'_'.$gk['dokpa'];?>" height="200">
                            </p>
                            <p class="col-sm-6">Closing Ceremony <br> 
                                <img src="../assets/auditdok/<?=$_GET['ka'].'_'.$gk['dokcc'];?>" height="200">
                            </p>
                            <p class="col-sm-6">Daftar hadir <br> 
                                <img src="../assets/auditdok/<?=$_GET['ka'].'_'.$gk['dokabsen'];?>" height="200">
                            </p>
                            <?php else: ?>
                            <div class="col-12 mt-3">
                                <div class="alert alert-warning">Belum ada dokumentasi audit.</div>
                            </div>
                            <?php endif; ?>
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
<script type="text/javascript">
    function printDiv(divName) {
     var printContents = document.getElementById(divName).innerHTML;
     var originalContents = document.body.innerHTML;

     document.body.innerHTML = printContents;

     window.print();

     document.body.innerHTML = originalContents;
}
</script>