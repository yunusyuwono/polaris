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
                    </div>
                    <div class="card-body">
                        <?php 
                        $gk = mysqli_fetch_array(mysqli_query($kon, "SELECT * FROM audit WHERE kodeaudit='".mysqli_real_escape_string($kon, $_GET['ka'] ?? '')."'"));
                        
                        if(!empty($gk['berkas']))
                        {
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
                        }
                        else
                        {
                             echo "Mohon upload berkas laporan audit anda terlebih dulu";
                        }
                        if(!empty($gk['beritaacara']))
                        {
                        ?>
                        <div id="printdok" class="row">
                            <div class="container">
                                <b>Berita Acara #<?=$gk['kodeaudit'];?></b><br>
                                <a class="btn btn-primary btn-sm" href="assets/beritaacara/<?=$gk['beritaacara'];?>"><i class="fas fa-download"></i></a>
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
