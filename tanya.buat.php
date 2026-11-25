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
                        <h4 class="font-semibold">Pertanyaan LED Paket <?php echo $gidp['namapaket'];?></h4>
                    </div>
                    <div class="card-body px-3">                            
                                <?php 
                                include "tanya.buat.proses.php";
                                ?>
                        <a class="btn btn-primary btn-sm" href="tanya"><i class="fas fa-caret-left"></i> Kembali</a>
                        <a class="btn btn-primary btn-sm" data-bs-toggle="collapse" href="#tambah" role="button" aria-expanded="false" aria-controls="tambah">
                            <i class="fas fa-caret-down"></i> Form Tambah
                        </a>
                            <form method="post">
                                <br>
                                <div class="row" id="tambah">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Paket Pertanyaan</label>
                                        <input type="text" name="paket" value="<?php echo $gidp['namapaket'];?>" readonly class="form-control">
                                        <input type="hidden" name="idpaket" value="<?php echo $gidp['id'];?>" readonly>
                                    </div>
                                    <div class="form-group">

                                       <label>Standar</label>
                                       <select name="standar" class="form-control" autofocus>
                                        <option value="">Pilih Standar</option>
                                           <?php 
                                           $krit=mysqli_query($kon,"select * from kriteria order by nostandar asc");
                                           while ($k=mysqli_fetch_array($krit)) {
                                               ?>
                                               <option value="<?php echo $k['id'];?>">
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
                                        <textarea name="pertanyaan" class="form-control" required rows="6"></textarea>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>No. Indikator</label>
                                        <input type="text" name="noindikator" class="form-control">
                                    </div>
                                    <div class="form-group">
                                        <label>Pilihan Jawaban 1</label>
                                        <input type="text" name="opsi1" class="form-control" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Pilihan Jawaban 2</label>
                                        <input type="text" name="opsi2" class="form-control" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Pilihan Benar</label>
                                        <select name="benar" class="form-control">
                                            <option value="1">Pilihan Jawaban 1</option>
                                            <option value="2">Pilihan Jawaban 2</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <input type="checkbox" name="berkas" value="1" id="berkas">
                                        <label for="berkas">Upload Berkas</label>
                                    </div>
                                    <div class="form-group">
                                        <button class="btn btn-sm btn-primary" name="simpan"><i class="fas fa-save"></i> Simpan </button>
                                    </div>
                                </div>
                                </div>
                        </form>
                            <div class="col-md-12">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped table-hover">
                                        <thead>
                                            <th>No.</th>
                                            <th>Paket Pertanyaan</th>
                                            <th>Standar</th>
                                            <th>Kategori</th>
                                            <th>Pertanyaan/Indikator</th>
                                            <th>Upload Berkas</th>
                                            <th>Opsi 1</th>
                                            <th>Opsi 2</th>
                                            <th>Aksi</th>
                                        </thead>

                                        <?php 
                                        $no=1;
                                        //$tanya=mysqli_query($kon,"select *,pertanyaan.id as idtanya from pertanyaan join kriteria on pertanyaan.kriteria=kriteria.id where idpaket='$gidp[id]' order by nostandar asc");
                                        $tanya=mysqli_query($kon,"select *,pertanyaan.id as idtanya from pertanyaan where idpaket='$gidp[id]' order by kriteria asc");
                                        while($t=mysqli_fetch_array($tanya))
                                        {
                                            $k=mysqli_fetch_array(mysqli_query($kon,"select * from kriteria where id='$t[kriteria]'"));
                                            $p=mysqli_fetch_array(mysqli_query($kon,"select * from paketpertanyaan where id='$t[idpaket]'"))
                                            ?>
                                            <tr>
                                                <td><?php echo $no;?></td>
                                                <td><?php echo $p['namapaket'];?></td>
                                                <td><?php echo $k['nostandar'].'. '.$k['namastandar'];?></td>
                                                <td><?php echo $k['nokategori'].'. '.$k['namakategori'];?></td>
                                                <td><?php echo $t['pertanyaan'];?></td>
                                                <td>
                                                    <?php 
                                                    if($t['vberkas']==1)
                                                    {
                                                        echo "<i class='fas fa-check text-success fa-2x'></i>";
                                                    }
                                                    elseif($t['vberkas']==0)
                                                    {
                                                        echo "<i class='fas fa-times text-dark fa-2x'></i>";
                                                    }
                                                    ?>
                                                </td>
                                                <td><?php echo $t['opsi1'];
                                                    if($t['benar']==1){echo " <i class='fas fa-check text-success'></i>";} ?></td>
                                                <td><?php echo $t['opsi2'];
                                                    if($t['benar']==2){echo " <i class='fas fa-check text-success'></i>";} ?></td>
                                                <td>
                                                    <div class="btn-group">
                                                        <a class="btn btn-primary btn-sm" href="tanya.edit?id=<?php echo $t['idtanya'];?>&idp=<?php echo $t['idpaket'];?>"><i class="fas fa-edit"></i></a>
                                                        <a class="btn btn-danger btn-sm" href="tanya.hapus?id=<?php echo $t['idtanya'];?>&idp=<?php echo $t['idpaket'];?>"><i class="fas fa-trash"></i></a>
                                                    </div>
                                                </td>
                                            </tr>
                                            <?php
                                            $no++;
                                        }
                                        ?>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>
<?php  include "foot.php";?>
