<?php 

if(isset($_POST['simpan']))
{

    $ka=$_POST['kodeauditor'];
    $kauditor=$_POST['kauditor'];
    $auditor1=$_POST['auditor1'];
    $auditor2=$_POST['auditor2'];

    $idp=mysqli_fetch_array(mysqli_query($kon,"select * from auditor where kodeaudit='$ka'"));
    if(empty($idp['id']))
    {
        if($auditor2==0)
        {
            $simpan=mysqli_query($kon,"insert into auditor(kodeaudit,user,status) values ('$ka','$kauditor','Ketua'),('$ka','$auditor1','Anggota 1')");    
        }
        else
        {
            $simpan=mysqli_query($kon,"insert into auditor(kodeaudit,user,status) values ('$ka','$kauditor','Ketua'),('$ka','$auditor1','Anggota 1'),('$ka','$auditor2','Anggota 2')");       
        }
    }
    else
    {
        $simpan=mysqli_query($kon,"update auditor set user='$kauditor' where status='Ketua' and kodeaudit='$ka'");
        $simpan.=mysqli_query($kon,"update auditor set user='$auditor1' where status='Anggota 1' and kodeaudit='$ka'");
        $simpan.=mysqli_query($kon,"update auditor set user='$auditor2' where status='Anggota 2' and kodeaudit='$ka'");
    }
    

    if($simpan)
    {
        ?>
        <div class="alert alert-success mt-1">
            Audit Berhasil Dibuat.
        </div>
        <?php
    }
    else
    {
        ?>
        <div class="alert alert-danger mt-1">
            Audit Gagal Dibuat <?php echo mysqli_error($kon);?>
        </div>
        <?php
    }
    
}


?>