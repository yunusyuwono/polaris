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
                        <h4 class="font-semibold">Paket Pertanyaan LED</h4>
                    </div>
                    <div class="card-body px-3">
                        <div class="row">
                            <div class="col-md-12">
                                <a class="btn btn-primary btn-sm" href="tanya"><i class="fas fa-caret-left"></i> Kembali</a>

                                <?php 
                                include "tanyapaket.buat.proses.php";
                                ?>
                                <form method="post">
                                    <label>Nama Paket Pertanyaan</label>
                                    <input type="text" name="namapaket" class="form-control" required>
                                    <button class="btn btn-primary btn-sm" name="simpan"><i class="fas fa-save"></i> Simpan</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>
<?php include "foot.php";?>