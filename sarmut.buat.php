<?php 
include 'nav.php';
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$title = '';
$year = date('Y');
if($id){
    $row = mysqli_fetch_array(mysqli_query($kon,"SELECT * FROM sarmut WHERE id='$id'"));
    if($row){ $title=$row['title']; $year=$row['year']; }
}
?>
<div id="main">
    <header class="">
        <a href="#" class="burger-btn d-block p-2">
            <i class="fas fa-bars"></i> Menu 
        </a>
    </header> 
    <div class="page-content">
        <section class="row">
            <div class="col-12 col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h4 class="font-semibold"><?php echo $id? 'Edit Sarmut':'Buat Sarmut';?></h4>
                    </div>
                    <div class="card-body px-3">
                        <form method="post" action="sarmut.save.php">
                            <input type="hidden" name="id" value="<?php echo $id;?>">
                            <div class="mb-3">
                                <label>Judul</label>
                                <input class="form-control" name="title" value="<?php echo htmlspecialchars($title);?>" required>
                            </div>
                            <div class="mb-3">
                                <label>Tahun</label>
                                <input class="form-control" name="year" value="<?php echo $year;?>" required>
                            </div>
                            <div class="mb-3">
                                <button class="btn btn-primary">Simpan</button>
                                <a href="sarmut" class="btn btn-secondary">Batal</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>
<?php include 'foot.php';?>
