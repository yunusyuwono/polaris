<?php
include 'nav.php';
?>
<style>
    .triangulasi-card {
        border-radius: 12px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        border: 1px solid #e5e7eb;
        margin-bottom: 2rem;
        background: #fff;
    }
    .triangulasi-header {
        background: linear-gradient(to right, #f8fafc, #eff6ff);
        border-bottom: 1px solid #e5e7eb;
        padding: 1.25rem 1.5rem;
        border-top-left-radius: 12px;
        border-top-right-radius: 12px;
    }
    .triangulasi-body {
        padding: 1.5rem;
    }
    .source-block {
        border-left: 4px solid #3b82f6;
        padding-left: 1rem;
        margin-bottom: 1.5rem;
    }
    .source-block.wawancara { border-left-color: #10b981; }
    .source-block.observasi { border-left-color: #8b5cf6; }
    .source-title {
        font-weight: 600;
        margin-bottom: 0.75rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .source-title i { font-size: 1.1rem; }
    .conclusion-block {
        background: #f8fafc;
        border-radius: 8px;
        padding: 1.25rem;
        border: 1px solid #e2e8f0;
    }
    textarea.form-control {
        min-height: 80px;
    }
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
                <div class="card shadow-sm border-0">
                    <div class="card-body px-3 px-md-4 py-4">
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 pb-3 border-bottom">
                            <div>
                                <h4 class="font-bold mb-1">Form Triangulasi Bukti AMI</h4>
                                <p class="text-muted mb-0">Kode Audit: <strong>#<?php echo htmlspecialchars($_GET['ka']);?></strong></p>
                            </div>
                            <div class="mt-3 mt-md-0">
                                <a class="btn btn-outline-secondary btn-sm" href="audit.progres?ka=<?php echo urlencode($_GET['ka']);?>&et=<?php echo urlencode($_GET['et']);?>"><i class="fas fa-caret-left me-1"></i> Kembali</a>
                            </div>
                        </div>

                        <?php 
                        include "audit.validasi.php";
                        $ka = mysqli_real_escape_string($kon, $_GET['ka']);
                        $et = mysqli_real_escape_string($kon, $_GET['et']);

                        // 1. Auto-create table audit_triangulasi_detail
                        $checkTable = mysqli_query($kon, "SHOW TABLES LIKE 'audit_triangulasi_detail'");
                        if(mysqli_num_rows($checkTable) == 0) {
                            mysqli_query($kon, "
                                CREATE TABLE audit_triangulasi_detail (
                                    id INT AUTO_INCREMENT PRIMARY KEY,
                                    kodeaudit VARCHAR(20) NOT NULL,
                                    no_risiko VARCHAR(20) NOT NULL,
                                    dokumen_temuan TEXT,
                                    dokumen_klaim TEXT,
                                    dokumen_konsisten VARCHAR(20),
                                    wawancara_temuan TEXT,
                                    wawancara_klaim TEXT,
                                    wawancara_konsisten VARCHAR(20),
                                    observasi_temuan TEXT,
                                    observasi_klaim TEXT,
                                    observasi_konsisten VARCHAR(20),
                                    catatan_auditor TEXT,
                                    kategori_temuan VARCHAR(50),
                                    validitas VARCHAR(20),
                                    risiko_residual VARCHAR(20),
                                    kesimpulan TEXT,
                                    entri TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
                            ");
                        }

                        // Handle Form Submission for Digital Triangulasi
                        if(isset($_POST['simpan_triangulasi'])) {
                            if(!empty($_POST['tri_no_risiko']) && is_array($_POST['tri_no_risiko'])) {
                                foreach($_POST['tri_no_risiko'] as $idx => $noRisiko) {
                                    $noR = mysqli_real_escape_string($kon, $noRisiko);
                                    $dT = mysqli_real_escape_string($kon, $_POST['dokumen_temuan'][$idx]);
                                    $dK = mysqli_real_escape_string($kon, $_POST['dokumen_klaim'][$idx]);
                                    $dKon = mysqli_real_escape_string($kon, $_POST['dokumen_konsisten'][$idx]);
                                    
                                    $wT = mysqli_real_escape_string($kon, $_POST['wawancara_temuan'][$idx]);
                                    $wK = mysqli_real_escape_string($kon, $_POST['wawancara_klaim'][$idx]);
                                    $wKon = mysqli_real_escape_string($kon, $_POST['wawancara_konsisten'][$idx]);
                                    
                                    $oT = mysqli_real_escape_string($kon, $_POST['observasi_temuan'][$idx]);
                                    $oK = mysqli_real_escape_string($kon, $_POST['observasi_klaim'][$idx]);
                                    $oKon = mysqli_real_escape_string($kon, $_POST['observasi_konsisten'][$idx]);
                                    
                                    $cA = mysqli_real_escape_string($kon, $_POST['catatan_auditor'][$idx]);
                                    $kT = mysqli_real_escape_string($kon, $_POST['kategori_temuan'][$idx]);
                                    $vA = mysqli_real_escape_string($kon, $_POST['validitas'][$idx]);
                                    $rR = mysqli_real_escape_string($kon, $_POST['risiko_residual'][$idx]);
                                    $kS = mysqli_real_escape_string($kon, $_POST['kesimpulan'][$idx]);
                                    
                                    $checkData = mysqli_query($kon, "SELECT id FROM audit_triangulasi_detail WHERE kodeaudit='$ka' AND no_risiko='$noR'");
                                    if(mysqli_num_rows($checkData) > 0) {
                                        mysqli_query($kon, "
                                            UPDATE audit_triangulasi_detail SET 
                                                dokumen_temuan='$dT', dokumen_klaim='$dK', dokumen_konsisten='$dKon',
                                                wawancara_temuan='$wT', wawancara_klaim='$wK', wawancara_konsisten='$wKon',
                                                observasi_temuan='$oT', observasi_klaim='$oK', observasi_konsisten='$oKon',
                                                catatan_auditor='$cA', kategori_temuan='$kT', validitas='$vA', risiko_residual='$rR', kesimpulan='$kS'
                                            WHERE kodeaudit='$ka' AND no_risiko='$noR'
                                        ");
                                    } else {
                                        mysqli_query($kon, "
                                            INSERT INTO audit_triangulasi_detail 
                                            (kodeaudit, no_risiko, dokumen_temuan, dokumen_klaim, dokumen_konsisten, 
                                             wawancara_temuan, wawancara_klaim, wawancara_konsisten,
                                             observasi_temuan, observasi_klaim, observasi_konsisten,
                                             catatan_auditor, kategori_temuan, validitas, risiko_residual, kesimpulan)
                                            VALUES 
                                            ('$ka', '$noR', '$dT', '$dK', '$dKon', '$wT', '$wK', '$wKon', '$oT', '$oK', '$oKon', '$cA', '$kT', '$vA', '$rR', '$kS')
                                        ");
                                    }
                                }
                                echo '<div class="alert alert-success alert-dismissible fade show"><i class="fas fa-check-circle me-2"></i>Data Triangulasi Digital berhasil disimpan.<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
                            }
                        }

                        // Handle upload PDF (Legacy)
                        if(isset($_POST['upload'])) {
                            $idaudit=mysqli_real_escape_string($kon, $_POST['idaudit']);
                            $kodeaudit=mysqli_real_escape_string($kon, $_POST['kodeaudit']);
                            $upt=$_FILES['ami']['tmp_name'];
                            $upn=$_FILES['ami']['name'];
                            
                            $gka=mysqli_query($kon,"select * from audit where kodeaudit='$kodeaudit'");
                            $gk=mysqli_fetch_array($gka);
                            
                            $nupn=$kodeaudit.'_Triangulasi_'.$upn;
                            $upload=mysqli_query($kon,"update audit set triangulasi='$nupn' where kodeaudit='$kodeaudit'");
                            
                            if($upload) {
                                if(!empty($gk['triangulasi'])) { @unlink("../assets/laporan/".$gk['triangulasi']); }
                                move_uploaded_file($upt,"../assets/laporan/".$nupn);
                                echo '<div class="alert alert-success alert-dismissible fade show"><i class="fas fa-check-circle me-2"></i>Berkas PDF Triangulasi berhasil disimpan.<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
                            } else {
                                echo '<div class="alert alert-danger alert-dismissible fade show"><i class="fas fa-times-circle me-2"></i>Berkas PDF Triangulasi gagal disimpan.<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
                            }
                        }

                        // Handle hapus PDF (Legacy)
                        if(isset($_GET['hapus'])) {
                            $berkas = mysqli_real_escape_string($kon, $_GET['berkas']);
                            @unlink('../assets/laporan/'.$berkas);
                            mysqli_query($kon,"update audit set triangulasi='' where kodeaudit='$ka'");
                            echo "<script>alert('Berkas Triangulasi berhasil direset'); window.location='audit.triangulasi?ka=".urlencode($ka)."&et=".urlencode($et)."';</script>";
                            exit;
                        }

                        $gk = mysqli_fetch_array(mysqli_query($kon,"select * from audit where kodeaudit='$ka'"));
                        
                        $qHeader = mysqli_query($kon, "SELECT * FROM audit_persiapan WHERE kodeaudit='$ka'");
                        $headerData = $qHeader ? mysqli_fetch_array($qHeader) : null;
                        $qDefaultPra = mysqli_query($kon, "
                            SELECT pr.id, pr.tahun, pr.unit 
                            FROM praaudit pr 
                            JOIN audit a ON pr.unit = a.unit AND pr.tahun = YEAR(a.tglmulai)
                            WHERE a.kodeaudit = '$ka'
                            LIMIT 1
                        ");
                        $defaultPra = mysqli_fetch_assoc($qDefaultPra);
                        $refFormPraAmi = $headerData['ref_form_pra_ami'] ?? ($defaultPra ? 'Form-PRA/' . $defaultPra['tahun'] . '/' . $defaultPra['unit'] . '/' . sprintf('%03d', $defaultPra['id']) : '');

                        $selectedPraId = 0;
                        if (!empty($refFormPraAmi)) {
                            $parts = explode('/', $refFormPraAmi);
                            $selectedPraId = (int)end($parts);
                        }
                        if ($selectedPraId <= 0 && $defaultPra) {
                            $selectedPraId = (int)$defaultPra['id'];
                        }

                        // Auto-sync ALL pertanyaan from the packet into audit_persiapan_risiko
                        if($selectedPraId > 0) {
                            $existingRisks = [];
                            $qExisting = mysqli_query($kon, "SELECT id, no_risiko, pernyataan_risiko, dampak, probabilitas, skor, level, pengendalian_diklaim FROM audit_persiapan_risiko WHERE kodeaudit='$ka'");
                            if ($qExisting) {
                                while($ex = mysqli_fetch_assoc($qExisting)) {
                                    $cleanPrefix = trim(preg_replace('/\(Risiko:\s*.*?\)\s*$/is', '', $ex['pernyataan_risiko']));
                                    $ex['clean_prefix'] = $cleanPrefix;
                                    $existingRisks[] = $ex;
                                }
                            }
                            
                            $qMaxIdx = mysqli_query($kon, "SELECT COUNT(*) as cnt FROM audit_persiapan_risiko WHERE kodeaudit='$ka'");
                            $maxRow = mysqli_fetch_assoc($qMaxIdx);
                            $syncIdx = (int)($maxRow['cnt'] ?? 0) + 1;
                            
                            $qSync = mysqli_query($kon, "
                                SELECT p.id as idpertanyaan, p.pertanyaan, k.namastandar, l.isi_risiko, l.dampak, l.probabilitas, l.risiko, l.usulan_mitigasi
                                FROM pertanyaan p
                                JOIN kriteria k ON p.kriteria = k.id
                                LEFT JOIN (
                                    SELECT l1.* 
                                    FROM led l1
                                    JOIN (
                                        SELECT MAX(id) as max_id 
                                        FROM led 
                                        WHERE idpraaudit = '$selectedPraId'
                                        GROUP BY idpertanyaan
                                    ) l2 ON l1.id = l2.max_id
                                ) l ON l.idpertanyaan = p.id
                                WHERE p.idpaket = (SELECT paket FROM praaudit WHERE id = '$selectedPraId')
                                ORDER BY p.noindikator ASC
                            ");
                            if($qSync) {
                                while($r = mysqli_fetch_assoc($qSync)) {
                                    $isiRisiko = !empty($r['isi_risiko']) ? $r['isi_risiko'] : 'Belum diisi';
                                    $pernyataan = mysqli_real_escape_string($kon, $r['namastandar'].' - '. $r['pertanyaan'] . ' (Risiko: '.$isiRisiko.')');
                                    $pengendalian = mysqli_real_escape_string($kon, $r['usulan_mitigasi'] ?? '');
                                    
                                    $hasData = !empty($r['isi_risiko']) || !empty($r['usulan_mitigasi']);
                                    if($hasData) {
                                        $dampak = !empty($r['dampak']) ? (int)$r['dampak'] : 0;
                                        $probabilitas = !empty($r['probabilitas']) ? (int)$r['probabilitas'] : 0;
                                        $skor = ($dampak > 0 && $probabilitas > 0) ? (!empty($r['risiko']) ? (int)$r['risiko'] : ($dampak * $probabilitas)) : 0;
                                    } else {
                                        $dampak = 0;
                                        $probabilitas = 0;
                                        $skor = 0;
                                    }
                                    
                                    if($skor >= 10) $level = 'MERAH';
                                    elseif($skor >= 5) $level = 'KUNING';
                                    elseif($skor > 0) $level = 'HIJAU';
                                    else $level = '';
                                    
                                    $targetPrefix = trim($r['namastandar'].' - '. $r['pertanyaan']);
                                    $matchedExisting = null;
                                    foreach($existingRisks as $ex) {
                                        if($ex['clean_prefix'] === $targetPrefix) {
                                            $matchedExisting = $ex;
                                            break;
                                        }
                                    }
                                    if(!$matchedExisting) {
                                        foreach($existingRisks as $ex) {
                                            if(strpos($ex['clean_prefix'], $targetPrefix) === 0 || strpos($targetPrefix, $ex['clean_prefix']) === 0) {
                                                $matchedExisting = $ex;
                                                break;
                                            }
                                        }
                                    }
                                    
                                    if($matchedExisting) {
                                        $eId = (int)$matchedExisting['id'];
                                        mysqli_query($kon, "
                                            UPDATE audit_persiapan_risiko SET 
                                                pernyataan_risiko = '$pernyataan',
                                                dampak = '$dampak',
                                                probabilitas = '$probabilitas',
                                                skor = '$skor',
                                                level = '$level',
                                                pengendalian_diklaim = '$pengendalian'
                                            WHERE id = '$eId'
                                        ");
                                    } else {
                                        $noRisiko = 'R-'.$syncIdx;
                                        $indikator = mysqli_real_escape_string($kon, 'Target/Indikator');
                                        mysqli_query($kon, "
                                            INSERT INTO audit_persiapan_risiko 
                                            (kodeaudit, no_risiko, pernyataan_risiko, indikator_target, dampak, probabilitas, skor, level, pengendalian_diklaim) 
                                            VALUES 
                                            ('$ka', '$noRisiko', '$pernyataan', '$indikator', '$dampak', '$probabilitas', '$skor', '$level', '$pengendalian')
                                        ");
                                        $syncIdx++;
                                    }
                                }
                            }
                        }

                        // Retrieve risks from audit_persiapan_risiko
                        $risks = [];
                        $qR = mysqli_query($kon, "SELECT * FROM audit_persiapan_risiko WHERE kodeaudit='$ka' ORDER BY CAST(REPLACE(no_risiko,'R-','') AS UNSIGNED) ASC");
                        if($qR) {
                            while($r = mysqli_fetch_assoc($qR)) {
                                $risks[] = $r;
                            }
                        }

                        // Fetch Unique Standards for Filter
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

                        // Retrieve existing triangulation data
                        $triDataMap = [];
                        $qT = mysqli_query($kon, "SELECT * FROM audit_triangulasi_detail WHERE kodeaudit='$ka'");
                        if($qT) {
                            while($t = mysqli_fetch_assoc($qT)) {
                                $triDataMap[$t['no_risiko']] = $t;
                            }
                        }
                        ?>

                        <?php if(count($risks) == 0): ?>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>Belum ada butir risiko yang ditetapkan pada tahap Catatan Persiapan. Silakan selesaikan form Catatan Persiapan terlebih dahulu.
                            </div>
                        <?php else: ?>
                            <!-- Filter Berdasarkan Standar -->
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

                            <!-- List Tabel Triangulasi -->
                            <div class="table-responsive bg-white rounded-3 shadow-sm border mb-4">
                                <table class="table table-hover align-middle mb-0" id="triangulasiTable">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 5%;" class="text-center">NO</th>
                                            <th style="width: 38%;">PERNYATAAN STANDAR & INDIKATOR TARGET</th>
                                            <th style="width: 11%;" class="text-center">SUMBER 1<br><small class="text-muted fw-normal">(DOKUMEN)</small></th>
                                            <th style="width: 11%;" class="text-center">SUMBER 2<br><small class="text-muted fw-normal">(WAWANCARA)</small></th>
                                            <th style="width: 11%;" class="text-center">SUMBER 3<br><small class="text-muted fw-normal">(OBSERVASI)</small></th>
                                            <th style="width: 14%;" class="text-center">KESIMPULAN</th>
                                            <th style="width: 10%;" class="text-center">AKSI</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach($risks as $idx => $r):
                                            $noR = $r['no_risiko'];
                                            $tData = $triDataMap[$noR] ?? [];
                                            $parts = explode(' - ', $r['pernyataan_risiko'], 2);
                                            $stdName = trim($parts[0] ?? '');
                                            
                                            $hasDok = !empty($tData['dokumen_temuan']) || !empty($tData['dokumen_klaim']) || !empty($tData['dokumen_konsisten']);
                                            $hasWaw = !empty($tData['wawancara_temuan']) || !empty($tData['wawancara_klaim']) || !empty($tData['wawancara_konsisten']);
                                            $hasObs = !empty($tData['observasi_temuan']) || !empty($tData['observasi_klaim']) || !empty($tData['observasi_konsisten']);
                                            $hasKes = !empty($tData['catatan_auditor']) || !empty($tData['kategori_temuan']) || !empty($tData['validitas']) || !empty($tData['kesimpulan']);
                                        ?>
                                        <tr class="risk-card-item" data-standar="<?php echo htmlspecialchars($stdName); ?>" id="row-<?php echo htmlspecialchars($noR); ?>">
                                            <td class="text-center fw-bold text-primary"><?php echo htmlspecialchars($noR); ?></td>
                                            <td>
                                                <div class="fw-bold text-dark mb-1"><?php echo htmlspecialchars($r['pernyataan_risiko']); ?></div>
                                                <div class="small text-muted"><i class="fas fa-bullseye me-1 text-secondary"></i><?php echo htmlspecialchars($r['indikator_target']); ?></div>
                                            </td>
                                            <td class="text-center" id="cell-dok-<?php echo htmlspecialchars($noR); ?>">
                                                <?php if($hasDok): ?>
                                                    <span class="badge bg-success mb-1"><i class="fas fa-check-circle me-1"></i>Terisi</span><br>
                                                    <span class="badge bg-light text-dark border"><?php echo htmlspecialchars($tData['dokumen_konsisten'] ?? '-'); ?></span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary"><i class="fas fa-times-circle me-1"></i>Belum</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center" id="cell-waw-<?php echo htmlspecialchars($noR); ?>">
                                                <?php if($hasWaw): ?>
                                                    <span class="badge bg-success mb-1"><i class="fas fa-check-circle me-1"></i>Terisi</span><br>
                                                    <span class="badge bg-light text-dark border"><?php echo htmlspecialchars($tData['wawancara_konsisten'] ?? '-'); ?></span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary"><i class="fas fa-times-circle me-1"></i>Belum</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center" id="cell-obs-<?php echo htmlspecialchars($noR); ?>">
                                                <?php if($hasObs): ?>
                                                    <span class="badge bg-success mb-1"><i class="fas fa-check-circle me-1"></i>Terisi</span><br>
                                                    <span class="badge bg-light text-dark border"><?php echo htmlspecialchars($tData['observasi_konsisten'] ?? '-'); ?></span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary"><i class="fas fa-times-circle me-1"></i>Belum</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center" id="cell-kes-<?php echo htmlspecialchars($noR); ?>">
                                                <?php if($hasKes): ?>
                                                    <span class="badge bg-info text-dark mb-1"><?php echo htmlspecialchars($tData['kategori_temuan'] ?? 'Kesimpulan'); ?></span><br>
                                                    <span class="badge bg-primary"><?php echo htmlspecialchars($tData['validitas'] ?? '-'); ?></span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary"><i class="fas fa-times-circle me-1"></i>Belum</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-3 shadow-sm" onclick="openTriangulasiModal('<?php echo htmlspecialchars($noR); ?>', '<?php echo htmlspecialchars(addslashes($r['pernyataan_risiko'])); ?>')">
                                                <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-3 shadow-sm" onclick="openTriangulasiModal('<?php echo htmlspecialchars($noR); ?>')">
                                                    <i class="fas fa-edit me-1"></i> Isi / Edit
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

<!-- Modal Edit Triangulasi -->
<div class="modal fade" id="modalEditTriangulasi" tabindex="-1" aria-labelledby="modalTriangulasiLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">
            <div class="modal-header bg-primary text-white py-3">
                <h5 class="modal-title font-bold text-white mb-0" id="modalTriangulasiTitle">
                    <i class="fas fa-balance-scale me-2"></i> Form Triangulasi Bukti
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 bg-light">
                <input type="hidden" id="tri_modal_no_risiko" value="">
                
                <!-- Pernyataan Standar & Risiko Card in Modal Body -->
                <div class="card border-0 shadow-sm p-3 mb-4 rounded-3 bg-white border-start border-primary border-4">
                    <div class="d-flex align-items-center mb-1">
                        <span class="badge bg-primary me-2" id="modalTriangulasiBadge">R-1</span>
                        <h6 class="fw-bold mb-0 text-dark">Pernyataan Standar & Risiko</h6>
                    </div>
                    <div class="text-dark small lh-base mt-1" id="modalTriangulasiSub">-</div>
                </div>

                <!-- Nav Tabs -->
                <ul class="nav nav-pills nav-fill mb-4 bg-white p-2 rounded-3 border shadow-sm" id="triTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active fw-bold py-2" id="tab-dokumen-tab" data-bs-toggle="tab" data-bs-target="#tab-dokumen" type="button" role="tab">
                            <i class="fas fa-file-alt me-1 text-primary"></i> 1. Dokumen
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-bold py-2" id="tab-wawancara-tab" data-bs-toggle="tab" data-bs-target="#tab-wawancara" type="button" role="tab">
                            <i class="fas fa-comments me-1 text-success"></i> 2. Wawancara
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-bold py-2" id="tab-observasi-tab" data-bs-toggle="tab" data-bs-target="#tab-observasi" type="button" role="tab">
                            <i class="fas fa-eye me-1" style="color:#8b5cf6;"></i> 3. Observasi
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-bold py-2" id="tab-kesimpulan-tab" data-bs-toggle="tab" data-bs-target="#tab-kesimpulan" type="button" role="tab">
                            <i class="fas fa-clipboard-check me-1 text-warning"></i> 4. Kesimpulan
                        </button>
                    </li>
                </ul>

                <div class="tab-content" id="triTabContent">
                    <!-- Tab 1: Dokumen -->
                    <div class="tab-pane fade show active" id="tab-dokumen" role="tabpanel">
                        <div class="card border-0 shadow-sm p-3 rounded-3">
                            <h6 class="text-primary fw-bold mb-3"><i class="fas fa-file-alt me-2"></i>Sumber 1 — Telaah Dokumen</h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted">Yang Ditemukan Auditor</label>
                                    <textarea id="modal_dokumen_temuan" class="form-control" rows="3" placeholder="Contoh: Dokumen RPS tidak memuat indikator..."></textarea>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted">Yang Diklaim Unit</label>
                                    <textarea id="modal_dokumen_klaim" class="form-control" rows="3" placeholder="Contoh: Unit mengklaim sudah update RPS..."></textarea>
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label small fw-bold text-muted">Konsistensi Dokumen</label>
                                    <select id="modal_dokumen_konsisten" class="form-select">
                                        <option value="">-- Pilih Konsistensi --</option>
                                        <option value="Sesuai">Sesuai</option>
                                        <option value="Parsial">Parsial</option>
                                        <option value="Tidak Sesuai">Tidak Sesuai</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tab 2: Wawancara -->
                    <div class="tab-pane fade" id="tab-wawancara" role="tabpanel">
                        <div class="card border-0 shadow-sm p-3 rounded-3">
                            <h6 class="text-success fw-bold mb-3"><i class="fas fa-comments me-2"></i>Sumber 2 — Wawancara Multi-Pihak</h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted">Yang Ditemukan Auditor</label>
                                    <textarea id="modal_wawancara_temuan" class="form-control" rows="3" placeholder="Contoh: Dosen menyatakan A, Mahasiswa menyatakan B..."></textarea>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted">Yang Diklaim Unit</label>
                                    <textarea id="modal_wawancara_klaim" class="form-control" rows="3" placeholder="Contoh: Kaprodi mengklaim proses sudah berjalan..."></textarea>
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label small fw-bold text-muted">Konsistensi Wawancara</label>
                                    <select id="modal_wawancara_konsisten" class="form-select">
                                        <option value="">-- Pilih Konsistensi --</option>
                                        <option value="Konsisten">Konsisten</option>
                                        <option value="Tidak Konsisten">Tidak Konsisten</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tab 3: Observasi -->
                    <div class="tab-pane fade" id="tab-observasi" role="tabpanel">
                        <div class="card border-0 shadow-sm p-3 rounded-3">
                            <h6 class="fw-bold mb-3" style="color: #8b5cf6;"><i class="fas fa-eye me-2"></i>Sumber 3 — Observasi Langsung</h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted">Yang Ditemukan Auditor</label>
                                    <textarea id="modal_observasi_temuan" class="form-control" rows="3" placeholder="Contoh: Kunjungan kelas menunjukkan tidak ada case method..."></textarea>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted">Yang Diklaim Unit</label>
                                    <textarea id="modal_observasi_klaim" class="form-control" rows="3" placeholder="Contoh: RPS sudah mencantumkan PBL..."></textarea>
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label small fw-bold text-muted">Konsistensi Observasi</label>
                                    <select id="modal_observasi_konsisten" class="form-select">
                                        <option value="">-- Pilih Konsistensi --</option>
                                        <option value="Sesuai">Sesuai</option>
                                        <option value="Tidak Sesuai">Tidak Sesuai</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tab 4: Kesimpulan -->
                    <div class="tab-pane fade" id="tab-kesimpulan" role="tabpanel">
                        <div class="card border-0 shadow-sm p-3 rounded-3">
                            <h6 class="text-warning fw-bold mb-3"><i class="fas fa-clipboard-check me-2"></i>Kesimpulan Triangulasi (Minimal 2 sumber KONSISTEN)</h6>
                            <div class="row g-3">
                                <div class="col-md-12">
                                    <label class="form-label small fw-bold text-muted">Catatan Auditor</label>
                                    <textarea id="modal_catatan_auditor" class="form-control" rows="3" placeholder="Sintesa dari 3 sumber di atas..."></textarea>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted">Kategori Temuan</label>
                                    <select id="modal_kategori_temuan" class="form-select">
                                        <option value="">-- Pilih Kategori --</option>
                                        <option value="KTS Mayor">KTS Mayor</option>
                                        <option value="KTS Minor">KTS Minor</option>
                                        <option value="Observasi">Observasi</option>
                                        <option value="Temuan Positif">Temuan Positif</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted">Validitas</label>
                                    <select id="modal_validitas" class="form-select">
                                        <option value="">-- Pilih Validitas --</option>
                                        <option value="VALID">VALID (≥2 sumber mendukung)</option>
                                        <option value="TIDAK VALID">TIDAK VALID</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted">Risiko Residual</label>
                                    <select id="modal_risiko_residual" class="form-select">
                                        <option value="">-- Pilih Level Risiko --</option>
                                        <option value="MERAH">MERAH (Tinggi)</option>
                                        <option value="KUNING">KUNING (Sedang)</option>
                                        <option value="HIJAU">HIJAU (Rendah)</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted">Kesimpulan Singkat</label>
                                    <input type="text" id="modal_kesimpulan" class="form-control" placeholder="Kesimpulan akhir (Sesuai/RTL diperlukan)...">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-white border-top py-3">
                <button type="button" class="btn btn-secondary px-4 rounded-pill" data-bs-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-success px-4 rounded-pill shadow-sm" onclick="saveTriangulasiModal()"><i class="fas fa-save me-2"></i> Simpan Triangulasi</button>
            </div>
        </div>
    </div>
</div>

<script>
let currentModalNoRisiko = '';
const triangulasiDataMap = <?php echo json_encode($triDataMap, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
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

function openTriangulasiModal(noRisiko, pernyataanText) {
function openTriangulasiModal(noRisiko) {
    currentModalNoRisiko = noRisiko;
    const rData = risksDataMap[noRisiko] || {};
    const pernyataanText = rData.pernyataan_risiko || '';

    document.getElementById('tri_modal_no_risiko').value = noRisiko;
    document.getElementById('modalTriangulasiTitle').innerHTML = '<i class="fas fa-balance-scale me-2"></i> Form Triangulasi ' + noRisiko;
    document.getElementById('modalTriangulasiBadge').innerText = noRisiko;
    document.getElementById('modalTriangulasiSub').innerText = pernyataanText;

    const data = triangulasiDataMap[noRisiko] || {};
    
    // Fill fields
    document.getElementById('modal_dokumen_temuan').value = data.dokumen_temuan || '';
    document.getElementById('modal_dokumen_klaim').value = data.dokumen_klaim || '';
    document.getElementById('modal_dokumen_konsisten').value = data.dokumen_konsisten || '';
    
    document.getElementById('modal_wawancara_temuan').value = data.wawancara_temuan || '';
    document.getElementById('modal_wawancara_klaim').value = data.wawancara_klaim || '';
    document.getElementById('modal_wawancara_konsisten').value = data.wawancara_konsisten || '';
    
    document.getElementById('modal_observasi_temuan').value = data.observasi_temuan || '';
    document.getElementById('modal_observasi_klaim').value = data.observasi_klaim || '';
    document.getElementById('modal_observasi_konsisten').value = data.observasi_konsisten || '';
    
    document.getElementById('modal_catatan_auditor').value = data.catatan_auditor || '';
    document.getElementById('modal_kategori_temuan').value = data.kategori_temuan || '';
    document.getElementById('modal_validitas').value = data.validitas || '';
    document.getElementById('modal_risiko_residual').value = data.risiko_residual || '';
    document.getElementById('modal_kesimpulan').value = data.kesimpulan || '';

    // Switch to first tab
    const firstTabEl = document.querySelector('#triTab button[data-bs-target="#tab-dokumen"]');
    if (firstTabEl) {
        const tab = new bootstrap.Tab(firstTabEl);
        tab.show();
    }

    const modalEl = document.getElementById('modalEditTriangulasi');
    const modal = new bootstrap.Modal(modalEl);
    modal.show();
}

function saveTriangulasiModal() {
    const noRisiko = currentModalNoRisiko;
    if (!noRisiko) return;

    const formData = new FormData();
    formData.append('kodeaudit', '<?php echo htmlspecialchars($ka); ?>');
    formData.append('no_risiko', noRisiko);
    formData.append('action', 'save');

    formData.append('dokumen_temuan', document.getElementById('modal_dokumen_temuan').value);
    formData.append('dokumen_klaim', document.getElementById('modal_dokumen_klaim').value);
    formData.append('dokumen_konsisten', document.getElementById('modal_dokumen_konsisten').value);

    formData.append('wawancara_temuan', document.getElementById('modal_wawancara_temuan').value);
    formData.append('wawancara_klaim', document.getElementById('modal_wawancara_klaim').value);
    formData.append('wawancara_konsisten', document.getElementById('modal_wawancara_konsisten').value);

    formData.append('observasi_temuan', document.getElementById('modal_observasi_temuan').value);
    formData.append('observasi_klaim', document.getElementById('modal_observasi_klaim').value);
    formData.append('observasi_konsisten', document.getElementById('modal_observasi_konsisten').value);

    formData.append('catatan_auditor', document.getElementById('modal_catatan_auditor').value);
    formData.append('kategori_temuan', document.getElementById('modal_kategori_temuan').value);
    formData.append('validitas', document.getElementById('modal_validitas').value);
    formData.append('risiko_residual', document.getElementById('modal_risiko_residual').value);
    formData.append('kesimpulan', document.getElementById('modal_kesimpulan').value);

    fetch('audit.triangulasi.ajax.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(res => {
        if (res.success) {
            // Update local object map
            triangulasiDataMap[noRisiko] = {
                dokumen_temuan: document.getElementById('modal_dokumen_temuan').value,
                dokumen_klaim: document.getElementById('modal_dokumen_klaim').value,
                dokumen_konsisten: document.getElementById('modal_dokumen_konsisten').value,
                wawancara_temuan: document.getElementById('modal_wawancara_temuan').value,
                wawancara_klaim: document.getElementById('modal_wawancara_klaim').value,
                wawancara_konsisten: document.getElementById('modal_wawancara_konsisten').value,
                observasi_temuan: document.getElementById('modal_observasi_temuan').value,
                observasi_klaim: document.getElementById('modal_observasi_klaim').value,
                observasi_konsisten: document.getElementById('modal_observasi_konsisten').value,
                catatan_auditor: document.getElementById('modal_catatan_auditor').value,
                kategori_temuan: document.getElementById('modal_kategori_temuan').value,
                validitas: document.getElementById('modal_validitas').value,
                risiko_residual: document.getElementById('modal_risiko_residual').value,
                kesimpulan: document.getElementById('modal_kesimpulan').value
            };

            // Update DOM table cells
            const cellDok = document.getElementById('cell-dok-' + noRisiko);
            if (cellDok) {
                if (res.hasDok) {
                    cellDok.innerHTML = '<span class="badge bg-success mb-1"><i class="fas fa-check-circle me-1"></i>Terisi</span><br><span class="badge bg-light text-dark border">' + (res.dokKon || '-') + '</span>';
                } else {
                    cellDok.innerHTML = '<span class="badge bg-secondary"><i class="fas fa-times-circle me-1"></i>Belum</span>';
                }
            }

            const cellWaw = document.getElementById('cell-waw-' + noRisiko);
            if (cellWaw) {
                if (res.hasWaw) {
                    cellWaw.innerHTML = '<span class="badge bg-success mb-1"><i class="fas fa-check-circle me-1"></i>Terisi</span><br><span class="badge bg-light text-dark border">' + (res.wawKon || '-') + '</span>';
                } else {
                    cellWaw.innerHTML = '<span class="badge bg-secondary"><i class="fas fa-times-circle me-1"></i>Belum</span>';
                }
            }

            const cellObs = document.getElementById('cell-obs-' + noRisiko);
            if (cellObs) {
                if (res.hasObs) {
                    cellObs.innerHTML = '<span class="badge bg-success mb-1"><i class="fas fa-check-circle me-1"></i>Terisi</span><br><span class="badge bg-light text-dark border">' + (res.obsKon || '-') + '</span>';
                } else {
                    cellObs.innerHTML = '<span class="badge bg-secondary"><i class="fas fa-times-circle me-1"></i>Belum</span>';
                }
            }

            const cellKes = document.getElementById('cell-kes-' + noRisiko);
            if (cellKes) {
                if (res.hasKes) {
                    cellKes.innerHTML = '<span class="badge bg-info text-dark mb-1">' + (res.katTemuan || 'Kesimpulan') + '</span><br><span class="badge bg-primary">' + (res.validitas || '-') + '</span>';
                } else {
                    cellKes.innerHTML = '<span class="badge bg-secondary"><i class="fas fa-times-circle me-1"></i>Belum</span>';
                }
            }

            // Close modal
            const modalEl = document.getElementById('modalEditTriangulasi');
            const modal = bootstrap.Modal.getInstance(modalEl);
            if (modal) modal.hide();

        } else {
            alert('Gagal menyimpan: ' + (res.message || 'Error tidak diketahui'));
        }
    })
    .catch(err => {
        console.error(err);
        alert('Terjadi kesalahan saat menghubungi server.');
    });
}
</script>
<?php 
include 'foot.php';
?>
