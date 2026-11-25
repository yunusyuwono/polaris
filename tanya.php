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
                        <div class="d-flex justify-content-between">
                            <h4 class="font-semibold">Paket Pertanyaan</h4>
                            <a href="tanyapaket.buat" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Buat Paket</a>
                        </div>
                    </div>
                    <div class="card-body px-3">
                        <div class="row">
                            <div class="col-md-12">

                                <div class="table-responsive mt-1">
                                    <table class="table table-bordered table-striped table-hover">
                                        <thead>
                                            <th>No.</th>
                                            <th>Nama Paket</th>
                                            <th>Jumlah Pertanyaan</th>
                                            <th>Status</th>
                                            <th>Aksi</th>
                                        </thead>
                                        <?php 
                                        $no=1;
                                        $paket=mysqli_query($kon,"select * from paketpertanyaan order by namapaket asc");
                                        while ($p=mysqli_fetch_array($paket)) {
                                            ?>
                                            <tr>
                                                <td><?php echo $no;?></td>
                                                <td><?php echo $p['namapaket'];?></td>
                                                <td><?php 
                                                    $jn=mysqli_num_rows(mysqli_query($kon,"select * from pertanyaan where idpaket='$p[id]'"));
                                                    echo $jn;
                                                ?>
                                                    
                                                </td>
                                                <td>
                                                    <div class="btn btn-light btn-sm">
                                                    <?php echo $p['status'];?>
                                                    </div>
                                                    <?php

                                                    if($p['status']=='Aktif')
                                                    {
                                                        ?>
                                                        <a class="btn btn-sm btn-secondary" href="tanya.status?id=<?php echo $p['id'];?>&do=Nonaktif" title="Nonaktifkan"><i class="fas fa-power-off"></i>
                                                        <?php
                                                    }
                                                    elseif($p['status']=='Nonaktif')
                                                    {
                                                        ?>
                                                        <a class="btn btn-sm btn-warning text-dark" href="tanya.status?id=<?php echo $p['id'];?>&do=Aktif" title="Aktifkan"><i class="fas fa-power-off"></i>
                                                        <?php   
                                                    }
                                                    ?>
                                                        
                                                </td>
                                                <td>
                                                    <div class="btn-group">
                                                        <a class="btn btn-success btn-sm" href="tanya.buat?idp=<?php echo $p['id'];?>" title="Buat Pertanyaan"><i class="fas fa-plus-circle"></i></a>
                                                        <a class="btn btn-dark btn-sm" href="tanya.export?idp=<?php echo $p['id'];?>" title="Export Pertanyaan"><i class="fas fa-download"></i></a>
                                                        <a class="btn btn-info btn-sm" href="tanya.import?idp=<?php echo $p['id'];?>" title="Import Pertanyaan"><i class="fas fa-upload"></i></a>
                                                        <a class="btn btn-danger btn-sm" href="tanya.hapus?idp=<?php echo $p['id'];?>" title="Hapus Paket Pertanyaan"><i class="fas fa-trash"></i></a>
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
<?php include "foot.php";?>