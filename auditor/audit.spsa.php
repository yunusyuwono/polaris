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
                        <h4 class="font-semibold">Surat Pernyataan Selesai Audit #<?php echo $_GET['ka'];?></h4>
                    </div>
                    <div class="card-body px-3">
                        <?php 
                        include "audit.validasi.php";

                        include "audit.spsa.proses.php";
                        include "audit.spsa.hapus.php";
                        $gk=mysqli_fetch_array(mysqli_query($kon,"select * from audit where kodeaudit='$_GET[ka]'"));
                        ?>
                        <a class="btn btn-primary btn-sm" href="audit.progres?ka=<?php echo $_GET['ka'];?>&et=<?php echo $_GET['et'];?>"><i class="fas fa-caret-left"></i> Kembali</a>
                        <?php 
                        
                        if(!empty($gk['berkas']))
                        {
                        ?>
                        <a class="btn btn-primary btn-sm" href="audit.surat?ka=<?php echo $_GET['ka'];?>&et=<?php echo $_GET['et'];?>"><i class="fas fa-download"></i> Download Surat Pernyataan Selesai Audit</a>

                        <a class="btn btn-primary btn-sm" data-bs-toggle="collapse" href="#tambah" role="button" aria-expanded="false" aria-controls="tambah">
                            <i class="fas fa-plus"></i> Upload Surat Pernyataan Selesai Audit
                        </a>
                        <div class="collapse" id="tambah">
                            <form method="post" enctype="multipart/form-data">
                                <input type="hidden" name="idaudit" value="<?php echo $cto['id'];?>">
                                <input type="hidden" name="kodeaudit" value="<?php echo $cto['kodeaudit'];?>">
                                <div class="form-group">
                                    <label>Surat Pernyataan Selesai Audit </label>
                                    <input type="file" name="ami" class="form-control" required accept="application/pdf">
                                </div>
                                <div class="form-group">
                                    <button class="btn btn-sm btn-primary" name="upload"><i class="fas fa-upload"></i> Upload Dokumen</button>
                                </div>
                            </form>
                        </div>

                        <?php 
                        }
                        else
                        {
                             echo "Mohon upload berkas laporan audit anda terlebih dulu";
                        }
                        if(!empty($gk['spsa']))
                        {
                        ?>
                        <div id="printdok" class="row">
                            <div class="container">
                                <b>Surat Pernyataan Selesai Audit #<?=$gk['kodeaudit'];?></b><br>
                                <a class="btn btn-danger btn-sm" href="?hapus&ka=<?=$_GET['ka'];?>&et=<?=$_GET['et'];?>&berkas=<?=$gk['spsa'];?>"><i class="fas fa-trash"></i></a>
                                <?=$gk['spsa'];?>
                            </div>
                        </div>
                        <?php 
                        }
                        ?>
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