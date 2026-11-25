<?php 
include 'nav.php';
include '../audit.ui.php';
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
            <?php 
            include "audit.validasi.php";
            ?>
            </div>
            <div class="col-12 col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="font-semibold">Berita Acara Audit #<?php echo htmlspecialchars($_GET['ka'] ?? '');?></h4>
                        <a class="btn btn-primary btn-sm" href="audit.progres?ka=<?php echo htmlspecialchars($_GET['ka'] ?? '');?>&et=<?php echo htmlspecialchars($_GET['et'] ?? '');?>"><i class="fas fa-caret-left"></i> Kembali</a>
                        <a class="btn btn-primary btn-sm" href="audit.beritaacara.cetak?ka=<?php echo htmlspecialchars($_GET['ka'] ?? '');?>&et=<?php echo htmlspecialchars($_GET['et'] ?? '');?>" target="_blank"><i class="fas fa-print"></i> Cetak</a>
                        <a class="btn btn-primary btn-sm" data-bs-toggle="collapse" href="#tambah" role="button" aria-expanded="false" aria-controls="tambah">
                            <i class="fas fa-plus"></i> Upload Berita Acara
                        </a>
                    </div>
                    <div class="card-body">
                        <?php 
                        include "audit.beritaacara.proses.php";
                        include "audit.beritaacara.hapus.php";
                        $gk = mysqli_fetch_array(mysqli_query($kon, "SELECT * FROM audit WHERE kodeaudit='".mysqli_real_escape_string($kon, $_GET['ka'] ?? '')."'"));
                        ?>
                       
                        <div class="collapse" id="tambah">
                            <form method="post" enctype="multipart/form-data">
                                <input type="hidden" name="idaudit" value="<?php echo $cto['id'] ?? '';?>">
                                <input type="hidden" name="kodeaudit" value="<?php echo $cto['kodeaudit'] ?? '';?>">
                                <div class="form-group">
                                    <label>Berita Acara </label>
                                    <input type="file" name="ami" class="form-control" required accept="application/pdf">
                                </div>
                                <div class="form-group">
                                    <button class="btn btn-sm btn-primary" name="upload"><i class="fas fa-upload"></i> Upload Dokumen</button>
                                </div>
                            </form>
                        </div>

                        <?php 
                        if(!empty($gk['beritaacara']))
                        {
                        ?>
                        <div id="printdok" class="row">
                            <div class="container">
                                <b>Surat Pernyataan Selesai Audit #<?=$gk['kodeaudit'];?></b><br>
                                <a class="btn btn-danger btn-sm" href="?hapus&ka=<?=$_GET['ka'];?>&et=<?=$_GET['et'];?>&berkas=<?=$gk['beritaacara'];?>"><i class="fas fa-trash"></i></a>
                                <?=$gk['beritaacara'];?>
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
