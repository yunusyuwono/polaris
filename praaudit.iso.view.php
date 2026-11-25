<?php
include 'nav.php';
include 'koneksi.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

function e($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

function table_exists($kon, $table){
    $table = mysqli_real_escape_string($kon, $table);
    $res = mysqli_query($kon, "SHOW TABLES LIKE '$table'");
    return $res && mysqli_num_rows($res) > 0;
}

$pra = isset($_GET['pra']) ? (int)$_GET['pra'] : 0;
if($pra <= 0){
    echo "<div id='main'><header><a href='#' class='burger-btn d-block p-2'><i class='fas fa-bars'></i> Menu</a></header><div class='page-content'><div class='alert alert-danger'>Parameter pra tidak ditemukan.</div></div></div>";
    include 'foot.php';
    exit;
}

$praRow = mysqli_fetch_assoc(mysqli_query($kon, "
    SELECT p.id AS idpra, p.unit AS idunit, p.paketiso, p.tahun,
           u.nama AS nama_unit,
           i.`idiso` AS iso_id, i.nama_iso AS nama_iso
    FROM praaudit p
    LEFT JOIN unit u ON u.id=p.unit
    LEFT JOIN iso i ON CAST(i.`idiso` AS UNSIGNED)=CAST(p.paketiso AS UNSIGNED)
    WHERE p.id='$pra'
    LIMIT 1
"));

if(!$praRow || empty($praRow['paketiso'])){
    echo "<div id='main'><header><a href='#' class='burger-btn d-block p-2'><i class='fas fa-bars'></i> Menu</a></header><div class='page-content'><div class='alert alert-warning'>Paket ISO tidak digunakan untuk PraAudit #".e($pra).".</div></div></div>";
    include 'foot.php';
    exit;
}

$paketIsoId = (int)$praRow['paketiso'];
$idUnit = (int)$praRow['idunit'];

$hasIsoJawaban = table_exists($kon, 'iso_jawaban');

$done = 0; $total = 0;

if($hasIsoJawaban && table_exists($kon,'iso_indikator')){
    // total target: semua iso_indikator aktif (status=1) untuk paket iso
    // skema iso_indikator: bisa punya id_iso atau id_klausul/subklausul di string; namun di query iso.ajax.php dipakai UNION.
    $qTotal = mysqli_query($kon, "
        SELECT COUNT(DISTINCT ii.id_isoindikator) AS cnt
        FROM iso_indikator ii
        WHERE ii.status='1' AND CAST(ii.id_iso AS UNSIGNED)='$paketIsoId'
    ");
    $rTotal = $qTotal ? mysqli_fetch_assoc($qTotal) : null;
    $total = $rTotal ? (int)$rTotal['cnt'] : 0;

    $qDone = mysqli_query($kon, "
        SELECT COUNT(DISTINCT j.id_isoindikator) AS cnt
        FROM iso_jawaban j
        WHERE j.id_praaudit='$pra' AND j.id_iso='$paketIsoId' AND j.id_unit='$idUnit'
          AND j.jawaban IS NOT NULL AND TRIM(j.jawaban)<>''
    ");
    $rDone = $qDone ? mysqli_fetch_assoc($qDone) : null;
    $done = $rDone ? (int)$rDone['cnt'] : 0;
}

$percent = $total > 0 ? min(100, round(($done/$total)*100)) : 0;

?>
<div id="main">
    <header class="">
        <a href="#" class="burger-btn d-block p-2"><i class="fas fa-bars"></i> Menu</a>
    </header>
    <div class="page-content">
        <section class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div>
                            <h4 class="mb-0">Lihat Isian ISO (PraAudit #<?= e($pra); ?>)</h4>
                            <div class="text-muted small">Unit: <?= e($praRow['nama_unit'] ?? '-'); ?> &middot; ISO: <?= e($praRow['nama_iso'] ?? '-'); ?> &middot; Tahun: <?= e($praRow['tahun'] ?? '-'); ?></div>
                        </div>
                        <div>
                            <a class="btn btn-sm btn-secondary" href="praaudit.laporan?pra=<?= (int)$pra; ?>"><i class="fas fa-arrow-left me-1"></i>Kembali</a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="mb-3" style="max-width:520px">
                            <div class="d-flex justify-content-between small mb-1"><span><?= $done.' / '.$total; ?> terisi</span><span><?= $percent; ?>%</span></div>
                            <div class="progress" style="height: 10px"><div class="progress-bar bg-secondary" style="width:<?= $percent; ?>%"></div></div>
                        </div>

                        <?php if(!$hasIsoJawaban): ?>
                            <div class="alert alert-warning">Tabel <code>iso_jawaban</code> tidak ditemukan di database.</div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped table-hover align-middle">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Klausul</th>
                                            <th>Subklausul</th>
                                            <th>Indikator ISO</th>
                                            <th>Jawaban</th>
                                            <th>Catatan</th>
                                            <th>Link Dokumen</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                    // Ambil jawaban + mapping klausul/subklausul/indikator.
                                    // iso_jawaban: dipakai di praaudit.buat.proses.php: fields id_praaudit, id_iso, id_unit, id_isoindikator, jawaban
                                    $q = mysqli_query($kon, "
                                        SELECT 
                                            j.id_isoindikator,
                                            j.jawaban,
                                            j.catatan,
                                            j.link_dokumen,
                                            ii.keterangan AS keterangan_indikator,
                                            ii.indikator,
                                            k.nomor_klausul, k.nama_klausul,
                                            sk.nomor_subklausul, sk.nama_subklausul
                                        FROM iso_jawaban j
                                        LEFT JOIN iso_indikator ii ON ii.id_isoindikator=j.id_isoindikator
                                        LEFT JOIN iso_klausul k ON k.id_klausul=ii.id_klausul
                                        LEFT JOIN iso_subklausul sk ON sk.id_subklausul=ii.id_subklausul
                                        WHERE j.id_praaudit='$pra' AND j.id_iso='$paketIsoId' AND j.id_unit='$idUnit'
                                        ORDER BY ii.id_isoindikator ASC
                                    ");

                                    // Jika kolom keterangan/ii.id_klausul/ii.id_subklausul tidak ada, query akan error.
                                    // Untuk aman, fallback sederhana: tampilkan indikator & jawaban saja.
                                    if(!$q){
                                        $q = mysqli_query($kon, "
                                            SELECT j.id_isoindikator, ii.indikator, j.jawaban, j.catatan, j.link_dokumen
                                            FROM iso_jawaban j
                                            LEFT JOIN iso_indikator ii ON ii.id_isoindikator=j.id_isoindikator
                                            WHERE j.id_praaudit='$pra' AND j.id_iso='$paketIsoId' AND j.id_unit='$idUnit'
                                            ORDER BY ii.id_isoindikator ASC
                                        ");

                                        $no=1;
                                        while($row = $q ? mysqli_fetch_assoc($q) : false){
                                            echo '<tr>';
                                            echo '<td>'.($no++).'</td>';
                                            echo '<td colspan="1" class="text-muted">-</td>';
                                            echo '<td class="text-muted">-</td>';
                                            echo '<td>'.e($row['indikator'] ?? '-').'</td>';
                                            echo '<td>'.(!empty($row['jawaban']) ? e($row['jawaban']) : '<span class="text-muted">-</span>').'</td>';
                                            echo '<td>'.(!empty($row['catatan']) ? e($row['catatan']) : '<span class="text-muted">-</span>').'</td>';
                                            echo '<td>'.(!empty($row['link_dokumen']) ? '<a href="'.e($row['link_dokumen']).'" target="_blank" class="btn btn-sm btn-outline-info">Buka Link</a>' : '<span class="text-muted">-</span>').'</td>';
                                            echo '</tr>';
                                        }
                                    } else {
                                        $no=1;
                                        while($row = mysqli_fetch_assoc($q)){
                                            echo '<tr>';
                                            echo '<td>'.($no++).'</td>';
                                            $klausulTxt = (!empty($row['nama_klausul']) ? ($row['nomor_klausul'].' - '.$row['nama_klausul']) : '-');
                                            $subTxt = (!empty($row['nama_subklausul']) ? ($row['nomor_subklausul'].' - '.$row['nama_subklausul']) : '-');
                                            echo '<td>'.e($klausulTxt).'</td>';
                                            echo '<td>'.e($subTxt).'</td>';
                                            echo '<td>'.e($row['indikator'] ?? '-').'</td>';
                                            echo '<td>'.(!empty($row['jawaban']) ? e($row['jawaban']) : '<span class="text-muted">-</span>').'</td>';
                                            echo '<td>'.(!empty($row['catatan']) ? e($row['catatan']) : '<span class="text-muted">-</span>').'</td>';
                                            echo '<td>'.(!empty($row['link_dokumen']) ? '<a href="'.e($row['link_dokumen']).'" target="_blank" class="btn btn-sm btn-outline-info">Buka Link</a>' : '<span class="text-muted">-</span>').'</td>';
                                            echo '</tr>';
                                        }
                                    }
                                    ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>
<?php include 'foot.php'; ?>

