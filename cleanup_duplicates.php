<?php
/**
 * Script untuk membersihkan duplikasi data subklausul.
 * Jika ditemukan subklausul ganda (dengan nomor dan nama yang sama di bawah klausul yang sama),
 * script ini akan membiarkan data pertama (ID terkecil) dan menghapus sisanya.
 * Jika data duplikat tersebut sudah terlanjur dipakai di `iso_indikator`,
 * script akan mengganti ID duplikat tersebut ke ID asli sebelum menghapusnya.
 */

include 'koneksi.php';

echo "<h2>Mulai Pembersihan Duplikat Subklausul</h2>";

// 1. Cari kelompok subklausul yang duplikat
$q_duplicates = mysqli_query($kon, "
    SELECT id_iso, id_klausul, nomor_subklausul, nama_subklausul, COUNT(*) as cnt 
    FROM iso_subklausul 
    GROUP BY id_iso, id_klausul, nomor_subklausul, nama_subklausul 
    HAVING cnt > 1
");

if (!$q_duplicates || mysqli_num_rows($q_duplicates) == 0) {
    echo "<p>Tidak ada duplikat subklausul yang ditemukan. Database bersih.</p>";
    exit;
}

while ($group = mysqli_fetch_assoc($q_duplicates)) {
    $id_iso = $group['id_iso'];
    $id_klausul = $group['id_klausul'];
    $nomor = $group['nomor_subklausul'];
    $nama = mysqli_real_escape_string($kon, $group['nama_subklausul']);

    echo "<hr/>";
    echo "<b>Ditemukan duplikat:</b> Klausul ID {$id_klausul} - Subklausul No. {$nomor} ({$group['nama_subklausul']}) - Jumlah: {$group['cnt']}<br/>";

    // 2. Ambil semua ID subklausul dalam kelompok ini, urutkan dari terkecil (asli) ke terbesar (duplikat)
    $q_ids = mysqli_query($kon, "
        SELECT id_subklausul 
        FROM iso_subklausul 
        WHERE id_iso = '$id_iso' 
          AND id_klausul = '$id_klausul' 
          AND nomor_subklausul = '$nomor' 
          AND nama_subklausul = '$nama'
        ORDER BY id_subklausul ASC
    ");

    $ids = [];
    while ($row = mysqli_fetch_assoc($q_ids)) {
        $ids[] = intval($row['id_subklausul']);
    }

    if (count($ids) <= 1) continue;

    // ID pertama adalah ID asli yang akan dipertahankan
    $original_id = $ids[0];
    echo "ID Asli (Dipertahankan): {$original_id}<br/>";

    // Sisanya adalah duplikat yang harus dihapus
    $duplicate_ids = array_slice($ids, 1);
    
    foreach ($duplicate_ids as $dup_id) {
        echo "--> Memproses ID Duplikat: {$dup_id}<br/>";

        // 3. Cari tabel iso_indikator yang memakai ID duplikat ini
        // Karena id_subklausul bisa berupa koma "1,2,25", kita pakai FIND_IN_SET
        $q_indikator = mysqli_query($kon, "SELECT id_isoindikator, id_subklausul FROM iso_indikator WHERE FIND_IN_SET('$dup_id', id_subklausul)");
        
        if ($q_indikator && mysqli_num_rows($q_indikator) > 0) {
            while ($ind = mysqli_fetch_assoc($q_indikator)) {
                $ind_id = $ind['id_isoindikator'];
                $str_subs = $ind['id_subklausul']; // ex: "4,5,25"
                
                // Pisahkan string menjadi array
                $arr_subs = explode(',', $str_subs);
                
                // Ganti dup_id dengan original_id
                foreach ($arr_subs as $k => $v) {
                    if (intval($v) === $dup_id) {
                        $arr_subs[$k] = $original_id;
                    }
                }
                
                // Pastikan tidak ada duplikat dalam list sendiri (misal "original_id, original_id")
                $arr_subs = array_unique($arr_subs);
                $new_str_subs = implode(',', $arr_subs);
                
                // Update tabel iso_indikator
                mysqli_query($kon, "UPDATE iso_indikator SET id_subklausul = '$new_str_subs' WHERE id_isoindikator = $ind_id");
                echo "----> Memperbaiki relasi indikator ID {$ind_id}: '{$str_subs}' menjadi '{$new_str_subs}'<br/>";
            }
        }

        // 4. Hapus record duplikat dari iso_subklausul
        $del = mysqli_query($kon, "DELETE FROM iso_subklausul WHERE id_subklausul = $dup_id");
        if ($del) {
            echo "----> Data duplikat ID {$dup_id} berhasil dihapus.<br/>";
        } else {
            echo "----> GAGAL menghapus duplikat ID {$dup_id}: " . mysqli_error($kon) . "<br/>";
        }
    }
}

echo "<hr/><h2>Pembersihan Selesai!</h2>";
?>
