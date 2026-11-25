<?php 
include 'nav.php';
$sarmut_id = isset($_GET['sarmut_id'])? intval($_GET['sarmut_id']) : 0;
?>
<div id="main">
    <header class="">
        <a href="#" class="burger-btn d-block p-2">
            <i class="fas fa-bars"></i> Menu 
        </a>
    </header> 
    <div class="page-content">
        <section class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between">
                        <h4>Monitoring Pengisian SARMUT</h4>
                        <div>
                            <a class="btn btn-sm btn-secondary" href="sarmut">Kembali</a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered">
                                <thead>
                                    <th>No.</th>
                                    <th>Sarmut</th>
                                    <th>Subindikator</th>
                                    <th>Unit (PIC)</th>
                                    <th>Triwulan</th>
                                    <th>Waktu Entri</th>
                                </thead>
                                <?php
                                $no=1;
                                $where = $sarmut_id? "WHERE j.idsarmut='$sarmut_id'" : '';
                                $sql = "SELECT j.*, u.nama as unit_nama, s.title as sarmut_title, sub.subindikator as sub_name 
                                        FROM sarmut_jawaban j 
                                        LEFT JOIN unit u ON u.id=j.idpic 
                                        LEFT JOIN sarmut s ON s.id=j.idsarmut 
                                        LEFT JOIN sarmut_subindikator sub ON sub.id=j.idsubindikator 
                                        $where 
                                        ORDER BY j.entri DESC LIMIT 500";
                                $q = mysqli_query($kon, $sql);
                                while($r=mysqli_fetch_array($q)){
                                ?>
                                <tr>
                                    <td><?php echo $no++;?></td>
                                    <td><?php echo htmlspecialchars($r['sarmut_title']);?></td>
                                    <td><?php echo htmlspecialchars($r['sub_name']);?></td>
                                    <td><?php echo htmlspecialchars($r['unit_nama']);?></td>
                                    <td><?php echo htmlspecialchars($r['triwulan']);?></td>
                                    <td><?php echo $r['entri'];?></td>
                                </tr>
                                <?php } ?>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>
<?php include 'foot.php';?>
