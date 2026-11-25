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
                        <h4 class="font-semibold">Edit Temuan Audit #<?php echo $_GET['ik'];?></h4>
                    </div>
                    <div class="card-body px-3">
                        <div class="container-fluid">
                        <?php 
                        include "audit.validasi.php";

                        include "audit.kondisi.edit.proses.php";

                        $gt=mysqli_fetch_array(mysqli_query($kon,"SELECT * from audit_kondisi WHERE kodekondisi='$_GET[ik]'"));
                        ?>
                        <a class="btn btn-primary btn-sm" href="audit.kondisi?ka=<?php echo $_GET['ka'];?>&et=<?php echo $_GET['et'];?>"><i class="fas fa-caret-left"></i> Kembali</a>
                        
                            <form method="post">
                                <input type="hidden" name="idaudit" value="<?php echo $gt['id'];?>">
                                <input type="hidden" name="kodeaudit" value="<?php echo $gt['kodeaudit'];?>">
                                <div class="form-group">
                                    <label>Temuan </label>
                                    <textarea name="kondisi" class="form-control" required ><?=$gt['kondisi'];?></textarea>
                                </div>
                                <div class="form-group">
                                        <label>Referensi</label>
                                        <input type="text" name="referensi" class="form-control" required value="<?=$gt['referensi'];?>">
                                    </div>
                                <div class="form-group">
                                    <label>Deskripsi </label>
                                    <textarea   name="deskripsi" class="form-control" required ><?=$gt['deskripsi'];?></textarea>
                                </div>
                                <div class="form-group">
                                    <label>Rekomendasi </label>
                                    <textarea   name="rekomendasi" class="form-control" required ><?=$gt['rekomendasi'];?></textarea>
                                </div>
                                <div class="form-group">
                                    <label>Tanggapan Auditi </label>
                                    <textarea   name="tanggapan" class="form-control" required ><?=$gt['tanggapan'];?></textarea>
                                </div>
                                <!--<div class="form-group">
                                    <label>Rencana Perbaikan </label>
                                    <textarea   name="renperbaikan" class="form-control" required ></textarea>
                                </div>
                                <div class="form-group">
                                    <label>Jadwal Perbaikan</label>
                                    <input type="date" name="jadpen1" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label>Rencana Pencegahan </label>
                                    <textarea   name="renpencegahan" class="form-control" required ></textarea>
                                </div>
                                <div class="form-group">
                                    <label>Jadwal Penyelesaian</label>
                                    <input type="date" name="jadpen2" class="form-control" required>
                                </div>
                            -->
                                <div class="form-group">
                                    <label>Kategori Temuan</label>
                                    <select name="kategori" class="form-control" required>
                                        <option <?php if($gt['kategori']=='KTS Major'){echo "selected";} ?> value="KTS Major">KTS Major</option>
                                        <option <?php if($gt['kategori']=='KTS Minor'){echo "selected";} ?> value="KTS Minor">KTS Minor</option>
                                        <option <?php if($gt['kategori']=='OB'){echo "selected";} ?> value="OB">OB</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Status Temuan</label>
                                    <select name="status" class="form-control" required>
                                        <option <?php if($gt['status']=='Open'){echo "selected";} ?> value="Open">Open</option>
                                        <option <?php if($gt['status']=='Close'){echo "selected";} ?> value="Close">Close</option>
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
<?php 
include 'foot.php';
?>