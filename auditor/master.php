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
            <div class="col-12 mt-1 mb-2">
                <div class="btn-group btn-block">
                    <?php 
                    if(isset($_GET['hal']))
                    {
                        $hal=$_GET['hal'];
                        if($hal=='unit')
                        {
                            $dcls="btn-light border-primary";
                        }
                        else
                        {
                            $dcls="btn-primary border-light";
                        }
                        if($hal=='user')
                        {
                            $pcls="btn-light border-primary";
                        }
                        else
                        {
                            $pcls="btn-primary border-light";
                        }
                        if($hal=='standar')
                        {
                            $tcls="btn-light border-primary";
                        }
                        else
                        {
                            $tcls="btn-primary border-light";
                        }
                    }
                    ?>
                    <a class="btn <?php echo $dcls;?> btn-sm " href="?hal=unit"><b>Unit</b></a>
                    <a class="btn <?php echo $pcls;?> btn-sm" href="?hal=user"><b>User</b></a>
                    <a class="btn <?php echo $tcls;?> btn-sm" href="?hal=standar"><b>Standar</b></a>
                    <!--<a class="btn <?php echo $pcls;?> btn-sm" href="?hal=mediasosial"><b>Sosial Media</b></a>-->
                </div>
            </div>
            <div id="konten" class="col-12">
                <?php
                switch ($_GET['hal']) {
                    case 'unit':
                        include 'unit.php';
                        break;
                    case 'user':
                        include 'user.php';
                        break;
                    case 'standar':
                        include 'standar.php';
                        break;
                    
                    default:
                        echo "Pilih menu di atas";
                        break;
                }
                ?>
            </div>
        </section>
    </div>
</div>

<?php include 'foot.php';?>