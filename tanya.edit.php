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
                        <?php 
                        $gidp=mysqli_fetch_array(mysqli_query($kon,"select * from paketpertanyaan where id='$_GET[idp]'"));
                        ?>
                        <h4 class="font-semibold">Edit Pertanyaan</h4>
                    </div>
                    <div class="card-body px-3">
                        <?php 
                        $gis=mysqli_fetch_array(mysqli_query($kon,"select * from pertanyaan where id='$_GET[id]'"));
                        ?>
                        <a class="btn btn-primary btn-sm" href="tanya.buat?idp=<?php echo $gidp['id'];?>"><i class="fas fa-caret-left"></i> Kembali</a>
                        <?php 
                        include "tanya.edit.proses.php";
                        ?>
                        <form method="post">
                                <br>
                                <div class="row" id="tambah">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Paket Pertanyaan</label>
                                        <input type="text" name="paket" value="<?php echo $gidp['namapaket'];?>" readonly class="form-control">
                                        <input type="hidden" name="idpaket" value="<?php echo $gidp['id'];?>" readonly>
                                        <input type="hidden" name="idtanya" value="<?php echo $gis['id'];?>" readonly>
                                    </div>
                                    <div class="form-group">

                                       <label>Standar</label>
                                       <select name="standar" class="form-control" autofocus>
                                        <option value="">Pilih Standar</option>
                                           <?php 
                                           $krit=mysqli_query($kon,"select * from kriteria order by nostandar asc");
                                           while ($k=mysqli_fetch_array($krit)) {
                                               ?>
                                               <option value="<?php echo $k['id'];?>" 
                                                <?php if($gis['kriteria']==$k['id']){ echo "selected";}
                                                ?>
                                                >
                                                   <?php echo $k['nostandar'].'. '.$k['namastandar'];
                                                   if($k['nokategori']>0)
                                                   {
                                                    echo ' ( Kategori : '.$k['nokategori'].'. '.$k['namakategori'].' )';
                                                    } 
                                                    ?>
                                               </option>
                                               <?php
                                           }
                                           ?>
                                       </select> 
                                    </div>
                                    <div class="form-group">
                                        <label>Pertanyaan/Indikator</label>
                                        <textarea name="pertanyaan" class="form-control" required rows="6"><?php echo $gis['pertanyaan'];?></textarea>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>No. Indikator</label>
                                        <input type="text" name="noindikator" class="form-control" value="<?php echo $gis['noindikator'];?>">
                                    </div>
                                    <div class="form-group">
                                        <label>Pilihan Jawaban 1</label>
                                        <input type="text" name="opsi1" class="form-control" required value="<?php echo $gis['opsi1'];?>">
                                    </div>
                                    <div class="form-group">
                                        <label>Pilihan Jawaban 2</label>
                                        <input type="text" name="opsi2" class="form-control" required value="<?php echo $gis['opsi2'];?>">
                                    </div>
                                    <div class="form-group">
                                        <label>Pilihan Benar</label>
                                        <select name="benar" class="form-control">
                                            <option value="1" <?php if($gis['benar']==1){ echo "selected";}
                                                ?>>Pilihan Jawaban 1</option>

                                            <option value="2" <?php if($gis['benar']==2){ echo "selected";}
                                                ?>>Pilihan Jawaban 2</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <input type="checkbox" name="berkas" <?php if($gis['vberkas']==1){ echo "checked";}
                                                ?> value="1" id="berkas">
                                        <label for="berkas">Upload Berkas</label>
                                    </div>
                                    <div class="form-group">
                                        <button class="btn btn-sm btn-primary" name="simpan"><i class="fas fa-save"></i> Simpan </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
<?php include "foot.php";?>