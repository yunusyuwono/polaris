<?php 
if(isset($_POST['simpan']))
{
    $unit = addslashes($_POST['unit']);
    $tglmulai = addslashes($_POST['tglmulai']);
    $tglselesai = addslashes($_POST['tglselesai']);
    $lingkup = addslashes($_POST['lingkup']);
    
    // New Auditor Inputs
    $kauditor = $_POST['kauditor'];
    $auditor1 = $_POST['auditor1'];
    $auditor2 = $_POST['auditor2'] ?? '0';

    $thn = date('y');
    $base_kode = $thn . $lingkup . sprintf('%03s', $unit);
    
    // Ensure kodeaudit is unique
    $kode = $base_kode;
    $suffix = 1;
    while(mysqli_num_rows(mysqli_query($kon, "SELECT kodeaudit FROM audit WHERE kodeaudit='$kode'")) > 0) {
        $kode = $base_kode . $suffix;
        $suffix++;
    }

    $token = strtoupper(rand(10000, 99999));

    // Start transaction
    mysqli_begin_transaction($kon);
    $simpan_audit = mysqli_query($kon, "INSERT INTO audit(kodeaudit, unit, lingkup, token, tglmulai, tglselesai, berkas, spsa, beritaacara) VALUES ('$kode', '$unit', '$lingkup', '$token', '$tglmulai', '$tglselesai', '', '', '')");
    
    $simpan_auditor = false;
    if($simpan_audit) {
        if((string)$auditor2 === '0' || empty($auditor2)) {
            $simpan_auditor = mysqli_query($kon, "INSERT INTO auditor(kodeaudit, user, status) VALUES ('$kode', '$kauditor', 'Ketua'), ('$kode', '$auditor1', 'Anggota 1')");    
        } else {
            $simpan_auditor = mysqli_query($kon, "INSERT INTO auditor(kodeaudit, user, status) VALUES ('$kode', '$kauditor', 'Ketua'), ('$kode', '$auditor1', 'Anggota 1'), ('$kode', '$auditor2', 'Anggota 2')");       
        }
    }

    if($simpan_audit && $simpan_auditor)
    {
        mysqli_commit($kon);
        ?>
        <div class="alert alert-success mt-1">
            Audit dan Tim Auditor Berhasil Dibuat dengan kode: <strong><?= htmlspecialchars($kode); ?></strong>
        </div>
        <?php
    }
    else
    {
        mysqli_rollback($kon);
        ?>
        <div class="alert alert-danger mt-1">
            Gagal membuat audit: <?php echo mysqli_error($kon); ?>
        </div>
        <?php
    }
}
?>
