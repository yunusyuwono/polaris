<?php 
include 'nav.php';
include 'audit.ui.php';
error_reporting(0);

function audit_fetch_external_pegawai_map()
{
    global $kon;
    $map = [];
    if($kon) {
        $localUsers = mysqli_query($kon, "SELECT id, nama FROM user");
        if($localUsers) {
            while($user = mysqli_fetch_assoc($localUsers)) {
                $map[(string)$user['id']] = $user['nama'];
            }
        }
    }
    return $map;
}

$auditYearExpr = "CASE WHEN YEAR(audit.tglmulai)>0 THEN YEAR(audit.tglmulai) ELSE 2000 + CAST(LEFT(audit.kodeaudit, 2) AS UNSIGNED) END";
$yearExpr = "CASE WHEN YEAR(tglmulai)>0 THEN YEAR(tglmulai) ELSE 2000 + CAST(LEFT(kodeaudit, 2) AS UNSIGNED) END";
$yearRows = [];
$yearQuery = mysqli_query($kon, "SELECT DISTINCT $yearExpr AS tahun FROM audit ORDER BY tahun DESC");
if($yearQuery) {
    while($year = mysqli_fetch_assoc($yearQuery)) {
        if(!empty($year['tahun'])) {
            $yearRows[] = (int)$year['tahun'];
        }
    }
}

$currentYear = (int)date('Y');
$selectedYear = isset($_GET['tahun']) ? (int)$_GET['tahun'] : (in_array($currentYear, $yearRows, true) ? $currentYear : ($yearRows[0] ?? $currentYear));
if(!in_array($selectedYear, $yearRows, true) && !empty($yearRows)) {
    $selectedYear = $yearRows[0];
}

$auditRows = [];
$audit=mysqli_query($kon,"
    SELECT audit.*, unit.nama as namaunit
    FROM audit
    LEFT JOIN unit ON unit.id=audit.unit
    WHERE $auditYearExpr='$selectedYear'
    ORDER BY audit.tglmulai DESC, audit.kodeaudit ASC
");
if($audit) {
    while($row = mysqli_fetch_assoc($audit)) {
        $auditRows[] = $row;
    }
}

$pegawaiMap = audit_fetch_external_pegawai_map();
$localUsers = mysqli_query($kon, "SELECT id, nama FROM user");
if($localUsers) {
    while($user = mysqli_fetch_assoc($localUsers)) {
        $id = (string)$user['id'];
        if(!isset($pegawaiMap[$id]) || $pegawaiMap[$id] === '') {
            $pegawaiMap[$id] = $user['nama'];
        }
    }
}

$auditorMap = [];
$kodeList = array_values(array_unique(array_filter(array_column($auditRows, 'kodeaudit'))));
if(!empty($kodeList)) {
    $kodeSql = "'".implode("','", array_map(function($kode) use ($kon) {
        return mysqli_real_escape_string($kon, $kode);
    }, $kodeList))."'";
    $auditorQuery = mysqli_query($kon, "SELECT kodeaudit, user, status FROM auditor WHERE kodeaudit IN ($kodeSql) ORDER BY status ASC, id ASC");
    if($auditorQuery) {
        while($auditor = mysqli_fetch_assoc($auditorQuery)) {
            $kode = $auditor['kodeaudit'];
            if(!isset($auditorMap[$kode])) {
                $auditorMap[$kode] = ['ketua' => '', 'anggota' => []];
            }

            $nama = $pegawaiMap[(string)$auditor['user']] ?? $auditor['user'];
            if($auditor['status'] === 'Ketua') {
                $auditorMap[$kode]['ketua'] = $nama;
            } elseif(stripos($auditor['status'], 'Anggota') !== false && !empty($auditor['user'])) {
                $auditorMap[$kode]['anggota'][] = $nama;
            }
        }
    }
}

$totalAudit = count($auditRows);
$akademikCount = 0;
$nonAkademikCount = 0;
$laporanCount = 0;
foreach($auditRows as $row) {
    if((int)$row['lingkup'] === 1) $akademikCount++;
    if((int)$row['lingkup'] === 2) $nonAkademikCount++;
    if(!empty($row['berkas'])) $laporanCount++;
}
?>
<style>
    .audit-hero{background:#fff;border:1px solid #e5e7eb;border-radius:.75rem;padding:1.1rem;box-shadow:0 14px 30px rgba(15,23,42,.05)}
    .audit-stat{background:#fff;border:1px solid #e5e7eb;border-radius:.7rem;padding:1rem;height:100%;box-shadow:0 12px 26px rgba(15,23,42,.04)}
    .audit-stat-icon{width:38px;height:38px;border-radius:.6rem;display:inline-flex;align-items:center;justify-content:center;margin-bottom:.7rem;background:#eff6ff;color:#2563eb}
    .audit-filter{background:#f8fafc;border:1px solid #e2e8f0;border-radius:.65rem;padding:.85rem}
    .audit-table-card{background:#fff;border:1px solid #e5e7eb;border-radius:.75rem;box-shadow:0 14px 30px rgba(15,23,42,.05)}
    .audit-table-card .table{margin-bottom:0}
    .audit-team{min-width:160px;line-height:1.45}
</style>
<div id="main">
    <header class="">
        <a href="#" class="burger-btn d-block p-2">
            <i class="fas fa-bars"></i> Menu 
        </a>
    </header> 
    <div class="page-content">
        <section class="row">
            <div class="col-12 col-lg-12">
                <div class="audit-hero mb-3">
                    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center gap-3">
                        <div>
                            <h3 class="mb-1">Audit</h3>
                            <p class="text-muted mb-0">Kelola jadwal, tim auditor, dan laporan AMI berdasarkan tahun pelaksanaan.</p>
                        </div>
                        <a class="btn btn-primary btn-sm" href="audit.buat"><i class="fas fa-plus me-1"></i> Buat Audit</a>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12 col-md-3 mb-3"><div class="audit-stat"><span class="audit-stat-icon"><i class="bi bi-clipboard-data"></i></span><div class="text-muted small">Audit Tahun <?= htmlspecialchars($selectedYear); ?></div><h4 class="mb-0"><?= $totalAudit; ?></h4></div></div>
                    <div class="col-12 col-md-3 mb-3"><div class="audit-stat"><span class="audit-stat-icon"><i class="bi bi-mortarboard"></i></span><div class="text-muted small">Akademik</div><h4 class="mb-0"><?= $akademikCount; ?></h4></div></div>
                    <div class="col-12 col-md-3 mb-3"><div class="audit-stat"><span class="audit-stat-icon"><i class="bi bi-building"></i></span><div class="text-muted small">Non-akademik</div><h4 class="mb-0"><?= $nonAkademikCount; ?></h4></div></div>
                    <div class="col-12 col-md-3 mb-3"><div class="audit-stat"><span class="audit-stat-icon"><i class="bi bi-file-earmark-check"></i></span><div class="text-muted small">Laporan AMI</div><h4 class="mb-0"><?= $laporanCount; ?></h4></div></div>
                </div>

                <div class="audit-table-card">
                    <div class="card-body px-3">
                        <form class="audit-filter row g-2 align-items-end mb-3" method="get">
                            <div class="col-md-4 col-lg-3">
                                <label class="form-label">Tahun Audit</label>
                                <select class="form-control" name="tahun">
                                    <?php if(empty($yearRows)): ?>
                                        <option value="<?= $selectedYear; ?>"><?= $selectedYear; ?></option>
                                    <?php else: ?>
                                        <?php foreach($yearRows as $year): ?>
                                            <option value="<?= $year; ?>" <?= $year === $selectedYear ? 'selected' : ''; ?>><?= $year; ?></option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                            <div class="col-md-4 col-lg-3">
                                <button class="btn btn-primary btn-sm" type="submit"><i class="fas fa-filter me-1"></i> Terapkan</button>
                                <a class="btn btn-outline-secondary btn-sm" href="audit">Tahun Aktif</a>
                            </div>
                        </form>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="table-responsive mt-1"> 
                                    <table class="table table-bordered table-striped table-hover text-dark align-middle">
                                        <thead>
                                            <tr>
                                                <th>No.</th>
                                                <th>Kode Audit</th>
                                                <th>Unit Auditi</th>
                                                <th>Ruang Lingkup</th>
                                                <th>Token</th>
                                                <th>Waktu Audit</th>
                                                <th>Ketua Auditor</th>
                                                <th>Anggota Auditor</th>
                                                <th>Laporan AMI</th>
                                                <th>Entri</th>
                                                <th>Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        <?php 
                                        $no=1;
                                        foreach ($auditRows as $a) {
                                            ?>
                                            <tr>
                                                <td><?php echo $no;?></td>
                                                <td><?php echo $a['kodeaudit'];?></td>
                                                <td><?php echo $a['namaunit'];?></td>
                                                <td><?php if($a['lingkup']==1){echo "Akademik";}
                                                        elseif($a['lingkup']==2){echo "Non-akademik";}?></td>
                                                <td><code><?php echo htmlspecialchars($a['token']);?></code></td>
                                                <td><?php echo audit_ui_date($a['tglmulai']);?> s.d <?php echo audit_ui_date($a['tglselesai']);?></td>
                                                <td class="audit-team">
                                                    <?php
                                                    $tim = $auditorMap[$a['kodeaudit']] ?? ['ketua' => '', 'anggota' => []];
                                                    if(!empty($tim['ketua'])) {
                                                        echo htmlspecialchars($tim['ketua']).'(Ketua)';
                                                    }
                                                    ?>
                                                </td>
                                                <td class="audit-team">
                                                    <?php 
                                                    foreach(($tim['anggota'] ?? []) as $anggota) {
                                                        echo htmlspecialchars($anggota).'(Anggota)<br>';
                                                    }
                                                    ?>
                                                </td>
                                                </td>
                                                <td>
                                                    <?php 
                                                    if(!empty($a['berkas']))
                                                    {
                                                        ?>
                                                        <a href="assets/laporan/<?= $a['berkas'];?>" class="btn btn-primary btn-sm" target="_blank" ><i class="fas fa-download"></i> Download Laporan </a>
                                                        <a class="btn btn-info btn-sm" href="audit.surat?ka=<?php echo $a['kodeaudit'];?>" target="_blank" title="Surat Pernyataan Selesai AMI"><i class="fas fa-file"></i> Surat Pernyataan Selesai Audit</a>
                                                        <?php 
                                                    }
                                                    ?>
                                                </td>
                                                <td><?php echo $a['entri'];?></td>
                                                <td>
                                                    <div class="btn-group">
                                                    <a class="btn btn-success btn-sm" href="audit.auditor?ka=<?php echo $a['kodeaudit'];?>" title="Pilih Auditor"><i class="fas fa-user-secret"></i></a>
                                                    <a class="btn btn-dark btn-sm" href="audit.progres?ka=<?php echo $a['kodeaudit'];?>&et=<?=md5($a['token']);?>" title="Detail Audit"><i class="fas fa-search"></i></a>
                                                    <a class="btn btn-warning btn-sm" href="audit.edit?ka=<?php echo $a['kodeaudit'];?>" title="Edit Audit"><i class="fas fa-edit"></i></a>
                                                    <a class="btn btn-danger btn-sm" href="audit.hapus?ka=<?php echo $a['kodeaudit'];?>" onclick="return confirm('Anda yakkin ingin menghapus data ini ?')" title="Hapus Audit"><i class="fas fa-trash"></i></a>
                                                    
                                                </div>
                                                </td>

                                            </tr>
                                            <?php
                                            $no++;
                                        }
                                        
                                        /*$aud=mysqli_query($kon,"select * from auditor");
                                        while($d=mysqli_fetch_array($aud))
                                        {
                                            echo $d['kodeaudit'].'-'.$d['user'].'-'.$d['status'].'<br>';
                                        }*/
                                        ?>
                                        </tbody>
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
