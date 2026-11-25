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
                        <h4 class="font-semibold">Pertanyaan LED/Lembar Evaluasi Diri</h4>
                    </div>
                    <div class="card-body px-3">
                        <div class="card">
                            <div class="card-body">
                                <div class="container-fluid">
                                    <a class="btn btn-primary btn-sm" href="master?hal=unit"><i class="fas fa-caret-left"></i> Kembali</a>
                                    <?php 
                                    include "koneksi.php";
                                    include "unit.edit.proses.php";
                                    if(isset($_GET['id']))
                                    {
                                        $id = intval($_GET['id']);
                                        $q = mysqli_query($kon, "select * from unit where id='$id'");
                                        if($q && mysqli_num_rows($q)>0)
                                        {
                                            $u = mysqli_fetch_array($q);
                                            ?>
                                            <form method="post">
                                                <input type="hidden" name="id" value="<?=$u['id'];?>">
                                                <div class="form-group">
                                                    <label>Nama Unit/Prodi</label>
                                                    <input type="text" name="nama" class="form-control" value="<?=htmlspecialchars($u['nama']);?>" required>
                                                </div>
                                                <div class="form-group">
                                                    <label>Kepala (opsional)</label>
                                                    <input type="text" name="kepala" class="form-control" value="<?=htmlspecialchars($u['kepala']);?>">
                                                </div>
                                                <div class="form-group mt-2">
                                                    <button class="btn btn-primary btn-sm" name="simpan"><i class="fas fa-save"></i> Simpan</button>
                                                </div>
                                            </form>
                                            <?php
                                        }
                                        else
                                        {
                                            echo "<div class='alert alert-warning'>Data unit tidak ditemukan.</div>";
                                        }
                                    }
                                    else
                                    {
                                        echo "<div class='alert alert-warning'>ID Unit tidak ditentukan.</div>";
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>    
</div>
<?php include "foot.php";?>