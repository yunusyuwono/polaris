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
                        <h4 class="font-semibold">Validasi Token Audit #<?php echo $_GET['ka'];?></h4>
                    </div>
                    <div class="card-body px-3">
                        <?php 
                        if(isset($_POST['kirim']))
                        {
                            $ka=$_GET['ka'];;
                            $token=addslashes($_POST['token']);
                            $cto=mysqli_query($kon,"select * from audit where kodeaudit='$ka' and token='$token'");
                            if(mysqli_num_rows($cto)>0)
                            {
                                $etoken=md5($token);
                                ?>
                                <script type="text/javascript">
                                    alert('Validasi Token berhasil. Klik "OK" untuk melanjutkan.');
                                    window.location='audit.progres?ka=<?php echo $ka;?>&et=<?php echo $etoken;?>';
                                </script>
                                <?php
                            }
                            else
                            {
                                ?>
                                <div class="alert alert-danger">
                                    Token tidak valid.
                                </div>
                                <?php
                            }
                        }
                        ?>

                        <form method="post">
                            <label>No. Token </label>
                            <div class="input-group">
                                <input type="text" name="token" class="form-control" required maxlength="5" autocomplete autosave>
                                <div class="input-group-append">
                                    <button class="btn btn-primary" name="kirim"><i class="fas fa-paper-plane"></i> Kirim</button>
                                </div>
                            </div>
                        </form> 
                    </div>
                </div>
            </div>
        </section>
    </div>

</div>
<?php 
include 'foot.php';
?>