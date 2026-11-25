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
                            <h4 class="font-semibold">SARMUT - Daftar Monev</h4>
                            <a href="sarmut.buat.php" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Tambah Sarmut</a>
                        </div>
                    </div>
                    <div class="card-body px-3">
                        <div class="table-responsive mt-1">
                            <table class="table table-bordered table-striped table-hover">
                                <thead>
                                    <th>No.</th>
                                    <th>Judul</th>
                                    <th>Tahun</th>
                                    <th>Aksi</th>
                                </thead>
                                <?php 
                                $no=1;
                                $q=mysqli_query($kon,"SELECT * FROM sarmut ORDER BY year DESC, id DESC");
                                while($r=mysqli_fetch_array($q)){
                                ?>
                                <tr>
                                    <td><?php echo $no++;?></td>
                                    <td><?php echo htmlspecialchars($r['title']);?></td>
                                    <td><?php echo $r['year'];?></td>
                                    <td>
                                        <a class="btn btn-sm btn-success" href="sarmut.buat.php?id=<?php echo $r['id'];?>">Edit</a>
                                        <a class="btn btn-sm btn-danger" href="sarmut.delete.php?id=<?php echo $r['id'];?>" onclick="return confirm('Hapus?')">Hapus</a>
                                        <a class="btn btn-sm btn-info" href="sarmut.monitor.php?sarmut_id=<?php echo $r['id'];?>">Monitoring</a>
                                        <a class="btn btn-sm btn-secondary" href="sarmut.laporan.php?sarmut_id=<?php echo $r['id'];?>">Laporan</a>
                                    </td>
                                </tr>
                                <?php }
                                ?>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>
<?php include 'foot.php';?>
