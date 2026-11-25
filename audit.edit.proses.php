<?php
if(isset($_POST['simpan'])) {
    $kode_lama = mysqli_real_escape_string($kon, $_POST['kodeaudit_lama']);
    $unit = addslashes($_POST['unit']);
    $tglmulai = addslashes($_POST['tglmulai']);
    $tglselesai = addslashes($_POST['tglselesai']);
    $lingkup = addslashes($_POST['lingkup']);
    
    $kauditor = $_POST['kauditor'];
    $auditor1 = $_POST['auditor1'];
    $auditor2 = $_POST['auditor2'] ?? '0';

    mysqli_begin_transaction($kon);

    $simpan_audit = mysqli_query($kon, "UPDATE audit SET unit='$unit', lingkup='$lingkup', tglmulai='$tglmulai', tglselesai='$tglselesai' WHERE kodeaudit='$kode_lama'");

    // Update auditor: delete existing, then insert
    $delete_auditor = mysqli_query($kon, "DELETE FROM auditor WHERE kodeaudit='$kode_lama'");
    
    $simpan_auditor = false;
    if($delete_auditor) {
        if((string)$auditor2 === '0' || empty($auditor2)) {
            $simpan_auditor = mysqli_query($kon, "INSERT INTO auditor(kodeaudit, user, status) VALUES ('$kode_lama', '$kauditor', 'Ketua'), ('$kode_lama', '$auditor1', 'Anggota 1')");    
        } else {
            $simpan_auditor = mysqli_query($kon, "INSERT INTO auditor(kodeaudit, user, status) VALUES ('$kode_lama', '$kauditor', 'Ketua'), ('$kode_lama', '$auditor1', 'Anggota 1'), ('$kode_lama', '$auditor2', 'Anggota 2')");       
        }
    }

    if($simpan_audit && $simpan_auditor) {
        mysqli_commit($kon);
        echo "<script>alert('Audit dan Tim Auditor berhasil diperbarui.'); window.location='audit';</script>";
        exit;
    } else {
        mysqli_rollback($kon);
        echo "<div class='alert alert-danger mt-1'>Gagal memperbarui audit: " . mysqli_error($kon) . "</div>";
    }
}
?>
