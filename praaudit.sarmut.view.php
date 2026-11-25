<?php
include 'nav.php';
include 'koneksi.php';

$kon = isset($kon) ? $kon : null;
error_reporting(E_ALL);
ini_set('display_errors', 1);

function e($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

function digami_table_exists($kon, $table) {
    $table = mysqli_real_escape_string($kon, $table);
    $result = mysqli_query($kon, "SHOW TABLES LIKE '$table'");
    return $result && mysqli_num_rows($result) > 0;
}

$pra = isset($_GET['pra']) ? (int)$_GET['pra'] : 0;
if($pra <= 0){
    echo "<div id='main'><header><a href='#' class='burger-btn d-block p-2'><i class='fas fa-bars'></i> Menu</a></header><div class='page-content'><div class='alert alert-danger'>Parameter pra tidak ditemukan.</div></div></div>";
    include 'foot.php';
    exit;
}

// Ambil praaudit + paket sarmut
$praRow = mysqli_fetch_assoc(mysqli_query($kon, "
    SELECT p.id AS idpra, p.unit AS idunit, p.paketsarmut, p.tahun, u.nama AS nama_unit,
           s.id AS sarmut_id, s.title AS nama_sarmut
    FROM praaudit p
    LEFT JOIN unit u ON u.id=p.unit
    LEFT JOIN sarmut s ON s.id=p.paketsarmut
    WHERE p.id='$pra'
    LIMIT 1
"));

if(!$praRow || empty($praRow['paketsarmut'])){
    echo "<div id='main'><header><a href='#' class='burger-btn d-block p-2'><i class='fas fa-bars'></i> Menu</a></header><div class='page-content'><div class='alert alert-warning'>Paket Sarmut tidak digunakan untuk PraAudit #".e($pra).".</div></div></div>";
    include 'foot.php';
    exit;
}

// deteksi skema tabel
$hasJawaban = digami_table_exists($kon, 'sarmut_jawaban');
$hasSasaran = digami_table_exists($kon, 'sarmut_sasaran');
$hasIndikator = digami_table_exists($kon, 'sarmut_indikator');
$hasSubindikator = digami_table_exists($kon, 'sarmut_subindikator');

$paketSarmutId = (int)$praRow['paketsarmut'];
$idUnit = (int)$praRow['idunit'];

// Ringkasan jumlah terisi (indikator: subindikator) berdasarkan jawaban
$done = 0; $total = 0;
$nilaiMap = [];

if($hasJawaban && digami_table_exists($kon,'sarmut_subindikator')){
    // total target: semua subindikator pada paket sarmut
    $qTotal = mysqli_query($kon, "
        SELECT COUNT(DISTINCT sub.id) AS cnt
        FROM sarmut_sasaran ss
        JOIN sarmut_indikator si ON si.sasaran_id=ss.id
        JOIN sarmut_subindikator sub ON sub.indikator_id=si.id
        WHERE ss.sarmut_id='$paketSarmutId'
    ");
    $rTotal = $qTotal ? mysqli_fetch_assoc($qTotal) : null;
    $total = $rTotal ? (int)$rTotal['cnt'] : 0;

    // jawaban per unit untuk seluruh triwulan (ambil yang non kosong)
    $qDone = mysqli_query($kon, "
        SELECT COUNT(DISTINCT j.idsubindikator) AS cnt
        FROM sarmut_jawaban j
        WHERE j.idsarmut='$paketSarmutId' AND j.idpic='$idUnit' AND j.nilai IS NOT NULL AND TRIM(j.nilai)<>''
    ");
    $rDone = $qDone ? mysqli_fetch_assoc($qDone) : null;
    $done = $rDone ? (int)$rDone['cnt'] : 0;

    // ambil nilai terbaru per subindikator (berdasarkan triwulan sort string)
    $qNilai = mysqli_query($kon, "
        SELECT j.idsubindikator, j.nilai
        FROM sarmut_jawaban j
        WHERE j.idsarmut='$paketSarmutId' AND j.idpic='$idUnit'
          AND j.nilai IS NOT NULL AND TRIM(j.nilai)<>''
    ");
    while($row = $qNilai ? mysqli_fetch_assoc($qNilai) : false){
        $nilaiMap[(int)$row['idsubindikator']] = $row['nilai'];
    }
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
                            <h4 class="mb-0">Lihat Isian SARMUT (PraAudit #<?= e($pra); ?>)</h4>
                            <div class="text-muted small">Unit: <?= e($praRow['nama_unit'] ?? '-'); ?> &middot; Paket Sarmut: <?= e($praRow['nama_sarmut'] ?? '-'); ?> &middot; Tahun: <?= e($praRow['tahun'] ?? '-'); ?></div>
                        </div>
                        <div>
                            <a class="btn btn-sm btn-secondary" href="praaudit.laporan?pra=<?= (int)$pra; ?>"><i class="fas fa-arrow-left me-1"></i>Kembali</a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="mb-3" style="max-width:520px">
                            <div class="d-flex justify-content-between small mb-1"><span><?= $done.' / '.$total; ?> terisi</span><span><?= $percent; ?>%</span></div>
                            <div class="progress" style="height: 10px"><div class="progress-bar bg-info" style="width:<?= $percent; ?>%"></div></div>
                        </div>

                        <?php if(!$hasJawaban): ?>
                            <div class="alert alert-warning">Tabel <code>sarmut_jawaban</code> tidak ditemukan di database.</div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped table-hover align-middle">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Sasaran</th>
                                            <th>Indikator</th>
                                            <th>Subindikator</th>
                                            <th>Nilai Terisi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                    if($hasSasaran && $hasIndikator && $hasSubindikator){
                                        $q = mysqli_query($kon, "
                                            SELECT ss.sasaran, si.indikator, sub.id AS idsub, sub.subindikator,
                                                   NULLIF(TRIM(j.nilai), '') AS nilai
                                            FROM sarmut_sasaran ss
                                            JOIN sarmut_indikator si ON si.sasaran_id=ss.id
                                            JOIN sarmut_subindikator sub ON sub.indikator_id=si.id
                                            LEFT JOIN sarmut_jawaban j
                                              ON j.idsubindikator=sub.id AND j.idsarmut=ss.sarmut_id AND j.idpic='$idUnit'
                                            WHERE ss.sarmut_id='$paketSarmutId'
                                            ORDER BY ss.id ASC, si.id ASC, sub.id ASC
                                        ");

                                        $no=1;
                                        while($row = $q ? mysqli_fetch_assoc($q) : false){
                                            $nilai = isset($row['nilai']) && $row['nilai']!==null ? $row['nilai'] : '';
                                            echo '<tr>';
                                            echo '<td>'.($no++).'</td>';
                                            echo '<td>'.e($row['sasaran'] ?? '-').'</td>';
                                            echo '<td>'.e($row['indikator'] ?? '-').'</td>';
                                            echo '<td>'.e($row['subindikator'] ?? '-').'</td>';
                                            echo '<td>'.($nilai!=='' ? e($nilai) : '<span class="text-muted">-</span>').'</td>';
                                            echo '</tr>';
                                        }
                                    } else {
                                        echo '<tr><td colspan="5" class="text-muted">Data SARMUT belum lengkap (tabel master terkait tidak ditemukan).</td></tr>';
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

