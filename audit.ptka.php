<?php
include 'nav.php';
?>
<style>
    :root {
        --ptk-red: #dc2626; --ptk-red-light: #fef2f2; --ptk-red-border: #fecaca;
        --ptk-blue: #2563eb; --ptk-blue-light: #eff6ff; --ptk-blue-border: #bfdbfe;
        --ptk-green: #16a34a; --ptk-green-light: #f0fdf4; --ptk-green-border: #bbf7d0;
        --ptk-purple: #7c3aed; --ptk-purple-light: #f5f3ff; --ptk-purple-border: #ddd6fe;
        --ptk-yellow: #ca8a04; --ptk-yellow-light: #fefce8; --ptk-yellow-border: #fef08a;
    }
    .ptk-card {
        border-radius: 14px; border: 1px solid #e5e7eb;
        margin-bottom: 2rem; background: #fff;
        box-shadow: 0 2px 10px rgba(0,0,0,0.06); overflow: hidden;
    }
    .ptk-header {
        padding: 1rem 1.5rem;
        display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap;
    }
    .ptk-header.merah   { background: linear-gradient(to right, var(--ptk-red-light), #fff5f5); border-bottom: 2px solid var(--ptk-red-border); }
    .ptk-header.oranye  { background: linear-gradient(to right, #fff7ed, #fffbeb); border-bottom: 2px solid #fed7aa; }
    .ptk-header.kuning  { background: linear-gradient(to right, var(--ptk-yellow-light), #fffbeb); border-bottom: 2px solid var(--ptk-yellow-border); }
    .ptk-header.hijau   { background: linear-gradient(to right, var(--ptk-green-light), #f7fff9); border-bottom: 2px solid var(--ptk-green-border); }
    .ptk-body { padding: 1.5rem; }
    .ptk-section {
        border-radius: 10px; padding: 1.25rem 1.5rem;
        margin-bottom: 1.5rem; border: 1px solid;
    }
    .ptk-section.bagian-a { background: var(--ptk-blue-light); border-color: var(--ptk-blue-border); }
    .ptk-section.bagian-b { background: #fafafa; border-color: #e5e7eb; }
    .ptk-section.bagian-c { background: var(--ptk-yellow-light); border-color: var(--ptk-yellow-border); }
    .ptk-section.bagian-d { background: var(--ptk-purple-light); border-color: var(--ptk-purple-border); }
    .ptk-section-title {
        font-size: 0.72rem; font-weight: 800; letter-spacing: 0.8px;
        text-transform: uppercase; margin-bottom: 1rem;
        padding-bottom: 0.5rem; border-bottom: 1px solid rgba(0,0,0,0.08);
    }
    .ptk-section-title.a { color: var(--ptk-blue); }
    .ptk-section-title.b { color: #374151; }
    .ptk-section-title.c { color: var(--ptk-yellow); }
    .ptk-section-title.d { color: var(--ptk-purple); }
    .ptk-label { font-size: 0.78rem; font-weight: 700; color: #475569; margin-bottom: 0.3rem; display: block; }
    .ptk-input {
        border: 1px solid #e2e8f0; border-radius: 7px;
        padding: 0.5rem 0.75rem; font-size: 0.875rem; width: 100%;
        background: #fff; transition: border-color 0.2s, box-shadow 0.2s; color: #1e293b;
    }
    .ptk-input:focus { outline: none; border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99,102,241,0.12); }
    .ptk-textarea { min-height: 80px; resize: vertical; }
    /* Tabel tindakan */
    .tindakan-table { width: 100%; border-collapse: collapse; font-size: 0.85rem; }
    .tindakan-table th {
        background: #f1f5f9; padding: 0.5rem 0.75rem;
        text-align: left; font-size: 0.72rem; font-weight: 700;
        text-transform: uppercase; letter-spacing: 0.5px; color: #475569;
        border-bottom: 2px solid #e2e8f0;
    }
    .tindakan-table td { padding: 0.5rem 0.5rem; vertical-align: top; border-bottom: 1px solid #f1f5f9; }
    .tindakan-table tr:last-child td { border-bottom: none; }
    .tindakan-table input, .tindakan-table textarea {
        width: 100%; border: 1px solid #e2e8f0; border-radius: 6px;
        padding: 0.4rem 0.6rem; font-size: 0.83rem; background: #fafafa;
        transition: border-color 0.15s;
    }
    .tindakan-table input:focus, .tindakan-table textarea:focus {
        outline: none; border-color: #6366f1; background: #fff;
    }
    .tindakan-table textarea { min-height: 60px; resize: vertical; }
    .btn-add-row {
        font-size: 0.8rem; font-weight: 600; padding: 0.4rem 1rem;
        border: 1.5px dashed #6366f1; border-radius: 7px; background: transparent;
        color: #6366f1; cursor: pointer; transition: all 0.2s;
    }
    .btn-add-row:hover { background: #eef2ff; }
    .btn-del-row {
        width: 28px; height: 28px; border-radius: 50%; border: 1px solid #fecaca;
        background: #fff; color: #dc2626; font-size: 0.8rem; cursor: pointer;
        display: flex; align-items: center; justify-content: center; transition: all 0.2s; flex-shrink: 0;
    }
    .btn-del-row:hover { background: #fef2f2; }
    /* Monitoring status */
    .bulan-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 0.75rem; }
    .bulan-item label { font-size: 0.75rem; font-weight: 700; color: #64748b; display: block; margin-bottom: 0.35rem; }
    /* Save button */
    .ptk-save-btn {
        font-weight: 700; padding: 0.5rem 1.5rem; border-radius: 8px;
        border: none; cursor: pointer; display: inline-flex; align-items: center; gap: 0.5rem;
        transition: background 0.2s, transform 0.1s; font-size: 0.85rem;
    }
    .ptk-save-btn:active { transform: scale(0.97); }
    .save-status {
        font-size: 0.8rem; padding: 0.3rem 0.8rem; border-radius: 6px;
        display: inline-flex; align-items: center; gap: 0.4rem;
    }
    .save-status.ok  { background: #f0fdf4; color: #16a34a; border: 1px solid #bbf7d0; }
    .save-status.err { background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; }
    .ref-chip {
        display: inline-block; background: #f1f5f9; border: 1px solid #e2e8f0;
        border-radius: 6px; padding: 0.2rem 0.6rem; font-size: 0.78rem; color: #64748b;
    }
    .badge-level { padding: 0.3rem 0.7rem; border-radius: 6px; font-size: 0.75rem; font-weight: 700; }
    .badge-level.MERAH  { background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; }
    .badge-level.ORANYE { background: #fff7ed; color: #ea580c; border: 1px solid #fed7aa; }
    .badge-level.KUNING { background: #fefce8; color: #ca8a04; border: 1px solid #fef08a; }
    .badge-level.HIJAU  { background: #f0fdf4; color: #16a34a; border: 1px solid #bbf7d0; }
</style>

<div id="main">
    <header class="">
        <a href="#" class="burger-btn d-block p-2"><i class="fas fa-bars"></i> Menu</a>
    </header>
    <div class="page-content">
        <section class="row">
            <div class="col-12 col-lg-12">
                <div class="card shadow-sm border-0">
                    <div class="card-body px-3 px-md-4 py-4">

                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 pb-3 border-bottom">
                            <div>
                                <h4 class="font-bold mb-1"><i class="fas fa-file-medical-alt me-2 text-danger"></i>PTK — Permintaan Tindakan Korektif</h4>
                                <p class="text-muted mb-0">Kode Audit: <strong>#<?php echo htmlspecialchars($_GET['ka']);?></strong></p>
                            </div>
                            <div class="mt-3 mt-md-0 d-flex gap-2 flex-wrap">
                                <a class="btn btn-outline-secondary btn-sm" href="audit.progres?ka=<?php echo urlencode($_GET['ka']);?>&et=<?php echo urlencode($_GET['et']);?>">
                                    <i class="fas fa-caret-left me-1"></i> Kembali
                                </a>
                            </div>
                        </div>

                        <?php
                        include "audit.validasi.php";
                        $ka = mysqli_real_escape_string($kon, $_GET['ka']);
                        $et = mysqli_real_escape_string($kon, $_GET['et']);

                        // Handle upload PDF
                        if(isset($_POST['upload'])) {
                            $kodeaudit = mysqli_real_escape_string($kon, $_POST['kodeaudit']);
                            $upt=$_FILES['ami']['tmp_name']; $upn=$_FILES['ami']['name'];
                            $gka=mysqli_query($kon,"select * from audit where kodeaudit='$kodeaudit'");
                            $gk=mysqli_fetch_array($gka);
                            $nupn=$kodeaudit.'_PTKA_'.$upn;
                            $upload=mysqli_query($kon,"update audit set ptka='$nupn' where kodeaudit='$kodeaudit'");
                            if($upload) {
                                if(!empty($gk['ptka'])) @unlink("assets/laporan/".$gk['ptka']);
                                move_uploaded_file($upt,"assets/laporan/".$nupn);
                                echo '<div class="alert alert-success alert-dismissible fade show"><i class="fas fa-check-circle me-2"></i>Berkas PDF PTK berhasil disimpan.<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
                            } else {
                                echo '<div class="alert alert-danger alert-dismissible fade show"><i class="fas fa-times-circle me-2"></i>Berkas PDF PTK gagal disimpan.<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
                            }
                        }
                        if(isset($_GET['hapus'])) {
                            $berkas = mysqli_real_escape_string($kon, $_GET['berkas']);
                            @unlink('assets/laporan/'.$berkas);
                            mysqli_query($kon,"update audit set ptka='' where kodeaudit='$ka'");
                            echo "<script>alert('Berkas PTK berhasil direset'); window.location='audit.ptka?ka=".urlencode($ka)."&et=".urlencode($et)."';</script>";
                            exit;
                        }

                        $gk = mysqli_fetch_array(mysqli_query($kon,"select * from audit where kodeaudit='$ka'"));

                        // Auto-create tables (idempoten)
                        include_once 'audit.ptka.save.php'; // reuse table creation only
                        // Re-init karena save.php langsung exit hanya jika ada POST
                        // Tabel sudah dibuat oleh include di atas (dijalankan saat tidak ada POST aksi)

                        // Ambil risiko dari audit_persiapan_risiko + data LK2
                        $risks = [];
                        $qR = mysqli_query($kon, "
                            SELECT r.*, l.tipe_temuan, l.akar_penyebab, l.risiko_potensial,
                                   l.dampak, l.peluang, l.skor, l.level, l.tren,
                                   l.rekomendasi_rtm, l.rekomendasi_ptk
                            FROM audit_persiapan_risiko r
                            LEFT JOIN audit_lk2_temuan l ON r.kodeaudit=l.kodeaudit AND r.no_risiko=l.no_risiko
                            WHERE r.kodeaudit='$ka'
                            ORDER BY CAST(REPLACE(r.no_risiko,'R-','') AS UNSIGNED) ASC
                        ");
                        if($qR) while($row=mysqli_fetch_assoc($qR)) $risks[]=$row;

                        // Ambil data PTK yang sudah tersimpan
                        $ptkMap = [];
                        $checkPtk = mysqli_query($kon,"SHOW TABLES LIKE 'audit_ptk'");
                        if(mysqli_num_rows($checkPtk)>0){
                            $qP = mysqli_query($kon,"SELECT * FROM audit_ptk WHERE kodeaudit='$ka'");
                            if($qP) while($p=mysqli_fetch_assoc($qP)) $ptkMap[$p['no_risiko']]=$p;
                        }
                        $tindakanMap = [];
                        $checkTind = mysqli_query($kon,"SHOW TABLES LIKE 'audit_ptk_tindakan'");
                        if(mysqli_num_rows($checkTind)>0){
                            $qTd = mysqli_query($kon,"
                                SELECT t.* FROM audit_ptk_tindakan t
                                JOIN audit_ptk p ON t.ptk_id=p.id
                                WHERE p.kodeaudit='$ka'
                                ORDER BY t.ptk_id, t.no_urut ASC
                            ");
                            if($qTd) while($td=mysqli_fetch_assoc($qTd)) $tindakanMap[$td['ptk_id']][]=$td;
                        }

                        function levelClass($l){ $l=strtoupper($l??''); return in_array($l,['MERAH','ORANYE','KUNING','HIJAU'])?$l:''; }
                        function headerClass($l){ $l=strtolower($l??''); if(stripos($l,'merah')!==false)return 'merah'; if(stripos($l,'oranye')!==false)return 'oranye'; if(stripos($l,'kuning')!==false)return 'kuning'; if(stripos($l,'hijau')!==false)return 'hijau'; return ''; }
                        ?>


                        <?php if(empty($risks)): ?>
                        <div class="alert alert-warning"><i class="fas fa-exclamation-triangle me-2"></i>Belum ada data risiko dari Form Persiapan. Silakan isi Form Persiapan terlebih dahulu.</div>
                        <?php else: ?>

                        <!-- Filter Berdasarkan Standar -->
                        <?php
                        $selectedPraId = 0;
                        $qDefaultPra = mysqli_query($kon, "
                            SELECT pr.id 
                            FROM praaudit pr 
                            JOIN audit a ON pr.unit = a.unit AND pr.tahun = YEAR(a.tglmulai)
                            WHERE a.kodeaudit = '$ka'
                            LIMIT 1
                        ");
                        if ($qDefaultPra && $rPra = mysqli_fetch_assoc($qDefaultPra)) {
                            $selectedPraId = (int)$rPra['id'];
                        }
                        $uniqueStandards = [];
                        if ($selectedPraId > 0) {
                            $qStds = mysqli_query($kon, "
                                SELECT DISTINCT k.namastandar 
                                FROM pertanyaan p 
                                JOIN kriteria k ON p.kriteria = k.id 
                                WHERE p.idpaket = (SELECT paket FROM praaudit WHERE id = '$selectedPraId') AND k.namastandar IS NOT NULL AND k.namastandar != ''
                                ORDER BY k.namastandar ASC
                            ");
                            if ($qStds) {
                                while ($sRow = mysqli_fetch_assoc($qStds)) {
                                    $uniqueStandards[] = $sRow['namastandar'];
                                }
                            }
                        }
                        if (empty($uniqueStandards)) {
                            foreach ($risks as $rItem) {
                                $parts = explode(' - ', $rItem['pernyataan_risiko'], 2);
                                $stdName = trim($parts[0] ?? '');
                                if (!empty($stdName) && !in_array($stdName, $uniqueStandards)) {
                                    $uniqueStandards[] = $stdName;
                                }
                            }
                        }
                        ?>

                        <div class="card border-0 bg-light p-3 mb-4 rounded-3">
                            <div class="row align-items-center">
                                <div class="col-md-6 col-lg-5">
                                    <label for="filterStandar" class="form-label font-bold small text-muted text-uppercase mb-1">
                                        <i class="fas fa-filter text-primary me-1"></i> Filter Berdasarkan Standar
                                    </label>
                                    <select id="filterStandar" class="form-select border-primary shadow-sm" onchange="filterRisksByStandard(this.value)">
                                        <option value="">Semua Standar</option>
                                        <?php foreach ($uniqueStandards as $std): ?>
                                            <option value="<?php echo htmlspecialchars($std); ?>"><?php echo htmlspecialchars($std); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- List Tabel PTK (Read-only Admin) -->
                        <div class="table-responsive bg-white rounded-3 shadow-sm border mb-4">
                            <table class="table table-hover align-middle mb-0" id="ptkTable">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 5%;" class="text-center">NO</th>
                                        <th style="width: 32%;">PERNYATAAN RISIKO & STANDAR</th>
                                        <th style="width: 12%;" class="text-center">KLASIFIKASI & LEVEL</th>
                                        <th style="width: 11%;" class="text-center">FORM A<br><small class="text-muted fw-normal">(IDENTITAS)</small></th>
                                        <th style="width: 12%;" class="text-center">FORM B<br><small class="text-muted fw-normal">(RENCANA & REALISASI)</small></th>
                                        <th style="width: 11%;" class="text-center">FORM C<br><small class="text-muted fw-normal">(MONITORING)</small></th>
                                        <th style="width: 11%;" class="text-center">FORM D<br><small class="text-muted fw-normal">(EFEKTIVITAS)</small></th>
                                        <th style="width: 6%;" class="text-center">AKSI</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    foreach($risks as $idx => $r):
                                        $noR   = $r['no_risiko'];
                                        $ptk   = $ptkMap[$noR] ?? [];
                                        $ptkId = $ptk['id'] ?? null;
                                        $tindakan = $ptkId ? ($tindakanMap[$ptkId] ?? []) : [];
                                        $lvl   = $r['level'] ?? '';
                                        $tipe  = $r['tipe_temuan'] ?? '';
                                        
                                        if(strtolower($tipe)==='positif') continue;

                                        $parts = explode(' - ', $r['pernyataan_risiko'], 2);
                                        $stdName = trim($parts[0] ?? '');

                                        $hasA = !empty($ptk['ringkasan_temuan']) || !empty($ptk['akar_masalah']) || !empty($ptk['ptk_no']);
                                        $hasB = !empty($tindakan);
                                        $countB = count($tindakan);
                                        
                                        $b1 = $ptk['bulan1_status'] ?? '';
                                        $b2 = $ptk['bulan2_status'] ?? '';
                                        $b3 = $ptk['bulan3_status'] ?? '';
                                        $b4 = $ptk['bulan4_status'] ?? '';
                                        $b5 = $ptk['bulan5_status'] ?? '';
                                        $hasC = !empty($b1) || !empty($b2) || !empty($b3) || !empty($b4) || !empty($b5) || !empty($ptk['catatan_lpm']) || !empty($ptk['eskalasi_rtm']);
                                        $lastC = $b5 ?: ($b4 ?: ($b3 ?: ($b2 ?: ($b1 ?: ''))));

                                        $hasD = !empty($ptk['efektivitas']);
                                        $efektivitasVal = $ptk['efektivitas'] ?? '';
                                    ?>
                                    <tr class="risk-card-item" id="row_<?php echo htmlspecialchars($noR); ?>" data-standar="<?php echo htmlspecialchars($stdName); ?>">
                                        <td class="text-center fw-bold text-primary"><?php echo htmlspecialchars($noR); ?></td>
                                        <td>
                                            <div class="fw-bold text-dark mb-1"><?php echo htmlspecialchars($r['pernyataan_risiko']); ?></div>
                                            <div class="small text-muted"><i class="fas fa-bullseye me-1 text-secondary"></i><?php echo htmlspecialchars($r['indikator_target']); ?></div>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge <?php echo stripos($tipe,'mayor')!==false?'bg-danger':(stripos($tipe,'minor')!==false?'bg-warning text-dark':'bg-info text-dark'); ?> mb-1" style="font-size:0.75rem;">
                                                <?php echo htmlspecialchars($tipe ?: 'Temuan'); ?>
                                            </span><br>
                                            <?php 
                                            $badgeBg = '#f8fafc'; $badgeFg = '#475569'; $badgeBd = '#e2e8f0';
                                            if ($lvl === 'MERAH') { $badgeBg = '#fef2f2'; $badgeFg = '#dc2626'; $badgeBd = '#fecaca'; }
                                            elseif ($lvl === 'ORANYE') { $badgeBg = '#fff7ed'; $badgeFg = '#ea580c'; $badgeBd = '#fed7aa'; }
                                            elseif ($lvl === 'KUNING') { $badgeBg = '#fefce8'; $badgeFg = '#ca8a04'; $badgeBd = '#fef08a'; }
                                            elseif ($lvl === 'HIJAU') { $badgeBg = '#f0fdf4'; $badgeFg = '#16a34a'; $badgeBd = '#bbf7d0'; }
                                            ?>
                                            <span class="badge" style="background: <?php echo $badgeBg; ?>; color: <?php echo $badgeFg; ?>; border: 1px solid <?php echo $badgeBd; ?>; font-size: 0.75rem;">
                                                <?php echo htmlspecialchars($lvl ?: '-'); ?> (Skor <?php echo htmlspecialchars($r['skor']??'-'); ?>)
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <?php if($hasA): ?>
                                                <span class="badge bg-success mb-1"><i class="fas fa-check-circle me-1"></i>Terisi</span><br>
                                                <span class="badge bg-light text-dark border"><?php echo htmlspecialchars($ptk['ptk_no'] ?? 'Form A'); ?></span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary"><i class="fas fa-times-circle me-1"></i>Belum</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <?php if($hasB): ?>
                                                <span class="badge bg-success mb-1"><i class="fas fa-check-circle me-1"></i>Terisi</span><br>
                                                <span class="badge bg-light text-dark border"><?php echo $countB; ?> Tindakan</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary"><i class="fas fa-times-circle me-1"></i>Belum</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <?php if($hasC): ?>
                                                <span class="badge bg-success mb-1"><i class="fas fa-check-circle me-1"></i>Terisi</span><br>
                                                <span class="badge bg-light text-dark border"><?php echo htmlspecialchars($lastC ?: 'Aktif'); ?></span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary"><i class="fas fa-times-circle me-1"></i>Belum</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <?php if($hasD): ?>
                                                <span class="badge <?php echo $efektivitasVal==='EFEKTIF'?'bg-success':($efektivitasVal==='BELUM EFEKTIF'?'bg-warning text-dark':'bg-primary'); ?> mb-1">
                                                    <?php echo htmlspecialchars($efektivitasVal); ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary"><i class="fas fa-times-circle me-1"></i>Belum</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-outline-info btn-sm rounded-pill px-3 shadow-sm" onclick="openPtkDetailModal('<?php echo htmlspecialchars($noR); ?>')">
                                                <i class="fas fa-eye me-1"></i> Detail
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
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

<!-- Modal Detail PTK (Read-only) -->
<div class="modal fade" id="modalDetailPTKA" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">
            <div class="modal-header bg-primary text-white py-3">
                <h5 class="modal-title font-bold text-white mb-0" id="detPtkTitle"><i class="fas fa-file-medical-alt me-2"></i> Detail PTK</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 bg-light">
                <!-- Pernyataan Card in Body -->
                <div class="card border-0 shadow-sm p-3 mb-4 rounded-3 bg-white border-start border-primary border-4">
                    <div class="d-flex align-items-center mb-1">
                        <span class="badge bg-primary me-2" id="detPtkBadge">R-1</span>
                        <h6 class="fw-bold mb-0 text-dark">Pernyataan Standar & Risiko</h6>
                    </div>
                    <div class="text-dark small lh-base mt-1" id="detPtkStatement">-</div>
                </div>

                <!-- Form A Card -->
                <div class="card border-0 shadow-sm p-3 mb-3 rounded-3 border-start border-primary border-4">
                    <h6 class="text-primary fw-bold mb-3"><i class="fas fa-id-card me-2"></i>Form A — Identitas Temuan</h6>
                    <div class="row g-3">
                        <div class="col-md-4"><strong>No. PTK:</strong><p class="small text-muted mb-0" id="det_ptk_no">-</p></div>
                        <div class="col-md-4"><strong>Konsekuensi:</strong><p class="small text-muted mb-0" id="det_konsekuensi">-</p></div>
                        <div class="col-md-4"><strong>Tenggat Maks:</strong><p class="small text-muted mb-0" id="det_tenggat_maks">-</p></div>
                        <div class="col-12"><strong>Ringkasan Temuan:</strong><p class="small text-muted mb-0" id="det_ringkasan">-</p></div>
                        <div class="col-12"><strong>Akar Masalah:</strong><p class="small text-muted mb-0" id="det_akar">-</p></div>
                    </div>
                </div>

                <!-- Form B Card -->
                <div class="card border-0 shadow-sm p-3 mb-3 rounded-3 border-start border-dark border-4">
                    <h6 class="text-dark fw-bold mb-3"><i class="fas fa-tasks me-2"></i>Form B — Rencana Tindakan & Realisasi</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered mb-0 small" id="detTblTindakan">
                            <thead class="table-light">
                                <tr>
                                    <th>No</th>
                                    <th>Tindakan Korektif</th>
                                    <th>PJ</th>
                                    <th>Tenggat</th>
                                    <th>Realisasi & Bukti</th>
                                    <th>Tgl Selesai</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>

                <!-- Form C Card -->
                <div class="card border-0 shadow-sm p-3 mb-3 rounded-3 border-start border-warning border-4">
                    <h6 class="text-warning fw-bold mb-3"><i class="fas fa-chart-line me-2"></i>Form C — Monitoring Progres</h6>
                    <div class="d-flex gap-2 flex-wrap mb-2" id="det_bulan_container"></div>
                    <p class="small text-muted mb-0"><strong>Catatan LPM:</strong> <span id="det_catatan_lpm">-</span></p>
                </div>

                <!-- Form D Card -->
                <div class="card border-0 shadow-sm p-3 rounded-3 border-start border-purple border-4" style="border-left-color:#7c3aed !important;">
                    <h6 class="fw-bold mb-3" style="color:#7c3aed;"><i class="fas fa-check-double me-2"></i>Form D — Tinjauan Efektivitas</h6>
                    <div class="row g-3">
                        <div class="col-md-6"><strong>Status Efektivitas:</strong><br><span class="badge bg-primary" id="det_efektivitas">-</span></div>
                        <div class="col-md-6"><strong>Tenggat Baru:</strong><p class="small text-muted mb-0" id="det_tenggat_baru">-</p></div>
                        <div class="col-md-6"><strong>Ref LK:</strong><p class="small text-muted mb-0" id="det_ref_lk">-</p></div>
                        <div class="col-md-6"><strong>Auditor Verifikasi:</strong><p class="small text-muted mb-0" id="det_auditor_verif">-</p></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-white border-top py-3">
                <button type="button" class="btn btn-secondary px-4 rounded-pill" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script>
const ptkDataMap = <?php echo json_encode($ptkMap, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
const tindakanDataMap = <?php echo json_encode($tindakanMap, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
const risksDataMap = <?php 
    $rMap = [];
    foreach($risks as $rItem) {
        $rMap[$rItem['no_risiko']] = $rItem;
    }
    echo json_encode($rMap, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
?>;

function filterRisksByStandard(stdName) {
    const cards = document.querySelectorAll('.risk-card-item');
    cards.forEach(card => {
        const std = card.getAttribute('data-standar') || '';
        if (!stdName || std === stdName) {
            card.style.display = '';
        } else {
            card.style.display = 'none';
        }
    });
}

function openPtkDetailModal(noRisiko) {
    const rData = risksDataMap[noRisiko] || {};
    const pData = ptkDataMap[noRisiko] || {};
    const ptkId = pData.id || null;
    const tList = ptkId ? (tindakanDataMap[ptkId] || []) : [];

    document.getElementById('detPtkTitle').innerHTML = '<i class="fas fa-file-medical-alt me-2"></i> Detail PTK ' + noRisiko;
    document.getElementById('detPtkBadge').innerText = noRisiko;
    document.getElementById('detPtkStatement').innerText = rData.pernyataan_risiko || '';

    // Form A
    document.getElementById('det_ptk_no').innerText = pData.ptk_no || '-';
    document.getElementById('det_konsekuensi').innerText = pData.konsekuensi || '-';
    document.getElementById('det_tenggat_maks').innerText = pData.tenggat_maks || '-';
    document.getElementById('det_ringkasan').innerText = pData.ringkasan_temuan || '-';
    document.getElementById('det_akar').innerText = pData.akar_masalah || '-';

    // Form B
    const tbody = document.querySelector('#detTblTindakan tbody');
    tbody.innerHTML = '';
    if (tList.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted">Belum ada tindakan</td></tr>';
    } else {
        tList.forEach((td, i) => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${i+1}</td>
                <td>${escapeHtml(td.tindakan || '-')}</td>
                <td>${escapeHtml(td.pj || '-')}</td>
                <td>${escapeHtml(td.tenggat || '-')}</td>
                <td>${escapeHtml(td.realisasi_bukti || '-')}</td>
                <td>${escapeHtml(td.tgl_selesai || '-')}</td>
            `;
            tbody.appendChild(tr);
        });
    }

    // Form C
    const bCont = document.getElementById('det_bulan_container');
    bCont.innerHTML = '';
    for (let b = 1; b <= 5; b++) {
        const val = pData['bulan' + b + '_status'] || '-';
        bCont.innerHTML += `<span class="badge bg-light text-dark border">Bulan ${b}: ${escapeHtml(val)}</span>`;
    }
    document.getElementById('det_catatan_lpm').innerText = pData.catatan_lpm || '-';

    // Form D
    document.getElementById('det_efektivitas').innerText = pData.efektivitas || 'BELUM VERIFIKASI';
    document.getElementById('det_tenggat_baru').innerText = pData.tenggat_baru || '-';
    document.getElementById('det_ref_lk').innerText = pData.ref_lk_verifikasi || '-';
    document.getElementById('det_auditor_verif').innerText = pData.auditor_verifikasi || '-';

    const modalEl = document.getElementById('modalDetailPTKA');
    const modal = new bootstrap.Modal(modalEl);
    modal.show();
}

function escapeHtml(text) {
    if (!text) return '';
    return text.toString()
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}
</script>

<?php include 'foot.php'; ?>
