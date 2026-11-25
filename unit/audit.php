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
                <div class="card">
                    <div class="card-header">
                        <h4 class="font-semibold">AUDIT</h4>
                    </div>
                    <div class="card-body px-3">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="table-responsive mt-1"> 
                                    <table class="table table-bordered table-striped table-hover text-dark">
                                        <thead>
                                            <th>No.</th>
                                            <th>Kode Audit</th>
                                            <th>Unit Auditi</th>
                                            <th>Ruang Lingkup</th>
                                            <th>Waktu Audit <small><i>(yyyy-mm-dd)</i></small></th>
                                            <!--<th>Tim Auditor</th>-->
                                            <th>Aksi</th>
                                        </thead>

                                        <?php 
                                        $no=1;
                                        $audit=mysqli_query($kon,"select * from audit  where unit='$cs[idunit]' ");
                                        while ($a=mysqli_fetch_array($audit)) {
                                            ?>
                                            <tr>
                                                <td><?php echo $no;?></td>
                                                <td><?php echo $a['kodeaudit'];?></td>
                                                <td><?php 
                                                    $u=mysqli_fetch_array(mysqli_query($kon,"select * from unit where id='$a[unit]'"));
                                                    echo $u['nama'];?></td>
                                                <td><?php if($a['lingkup']==1){echo "Akademik";}
                                                        elseif($a['lingkup']==2){echo "Non-akademik";}?></td>
                                                <td><?php echo audit_ui_date($a['tglmulai']);?> s.d <?php echo audit_ui_date($a['tglselesai']);?></td>
                                               <!-- <td>
                                                    <?php 
                                                    /*
                                                    $timk=mysqli_query($kon,"select * from auditor join user on auditor.user=user.id where auditor.kodeaudit='$a[kodeaudit]' and auditor.status='Ketua'");
                                                    $t=mysqli_fetch_array($timk);
                                                        echo $t['nama']." ($t[status])";
                                                    ?>
                                                    <br>
                                                    <?php 
                                                    $tima=mysqli_query($kon,"select * from auditor join user on auditor.user=user.id where auditor.kodeaudit='$a[kodeaudit]' and auditor.status like '%Anggota%'");

                                                    while($ta=mysqli_fetch_array($tima))
                                                    {
                                                        echo $ta['nama']." ($ta[status])<br>";
                                                    }
                                                    */
                                                    ?>
                                                </td>-->
                                                <td>
                                                    <div class="btn-group">
                                                    <a class="btn btn-success btn-sm" href="audit.token?ka=<?php echo $a['kodeaudit'];?>" title="Mulai Audit"><i class="fas fa-paper-plane"></i> Mulai Audit</a>
                                                    
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
            </div>        
        </section>
    </div>
</div>

<?php include 'foot.php';?>
