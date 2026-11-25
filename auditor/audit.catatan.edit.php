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
                        <h4 class="font-semibold">Edit Catatan Audit #<?php echo $_GET['ka'];?></h4>
                    </div>
                    <div class="card-body px-3">
                        <div class="container-fluid">
                        <?php 
                        include "audit.validasi.php";

                        include "audit.catatan.edit.proses.php";

                        $kc=$_GET['kodecatatan'];
                        $gc=mysqli_fetch_array(mysqli_query($kon,"SELECT * from audit_catatan where kodecatatan = '$kc'"));
                        ?>
                        <a class="btn btn-primary btn-sm" href="audit.catatan?ka=<?php echo $_GET['ka'];?>&et=<?php echo $_GET['et'];?>"><i class="fas fa-caret-left"></i> Kembali</a>
                            <form method="post">
                                <input type="hidden" name="kodecatatan" value="<?php echo $gc['kodecatatan'];?>">
                                <div class="form-group">
                                    <label>Referensi/Klausal </label>
                                    <input type="text" name="referensi" class="form-control" required value="<?=$gc['referensi'];?>">
                                </div>
                                <div class="form-group">
                                    <label>Aspek yang diaudit </label>
                                    <textarea  name="aspek" class="form-control" required ><?=$gc['aspek'];?></textarea>
                                </div>
                                <!--<div class="form-group">
                                    <label>Catatan Khusus </label>
                                    <textarea   name="khusus" class="form-control" required ></textarea>
                                </div>-->

                                <div class="form-group">
                                    <label>Hasil Telaah </label>
                                    <textarea  name="telaah" class="form-control" required ><?=$gc['telaah'];?></textarea>
                                </div>
                                <div class="form-group">
                                    <label>Status Evaluasi</label>
                                    <select name="status" class="form-control" required>
                                        <option value="Sesuai" <?php if($gc['status']=='Sesuai'){echo "selected";} ?>>Sesuai</option>
                                        <option value="KTS Major" <?php if($gc['status']=='KTS Major'){echo "selected";} ?>>KTS Major</option>
                                        <option value="KTS Minor" <?php if($gc['status']=='KTS Minor'){echo "selected";} ?>>KTS Minor</option>
                                        <option value="OB" <?php if($gc['status']=='OB'){echo "selected";} ?>>OB</option>
                                    </select>
                                </div>
                                <button class="btn btn-primary btn-sm" name="simpan"><i class="fas fa-paper-plane"></i> Simpan</button>
                            </form> 
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>
<?php include 'foot.php';