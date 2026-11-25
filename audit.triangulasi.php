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
    .conclusion-block {
        background: #f8fafc;
        border-radius: 8px;
        padding: 1.25rem;
        border: 1px solid #e2e8f0;
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
                                if(!empty($gk['triangulasi'])) { @unlink("assets/laporan/".$gk['triangulasi']); }
                                move_uploaded_file($upt,"assets/laporan/".$nupn);
                                echo '<div class="alert alert-success alert-dismissible fade show"><i class="fas fa-check-circle me-2"></i>Berkas PDF Triangulasi berhasil disimpan.<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
                            } else {
                                echo '<div class="alert alert-danger alert-dismissible fade show"><i class="fas fa-times-circle me-2"></i>Berkas PDF Triangulasi gagal disimpan.<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
                            }
                        }

                        // Handle hapus PDF (Legacy)
                        if(isset($_GET['hapus'])) {
                            $berkas = mysqli_real_escape_string($kon, $_GET['berkas']);
                            @unlink('assets/laporan/'.$berkas);
                            mysqli_query($kon,"update audit set triangulasi='' where kodeaudit='$ka'");
                            echo "<script>alert('Berkas Triangulasi berhasil direset'); window.location='audit.triangulasi?ka=".urlencode($ka)."&et=".urlencode($et)."';</script>";
                            exit;
                        }

                        $gk = mysqli_fetch_array(mysqli_query($kon,"select * from audit where kodeaudit='$ka'"));
                        
                        $headerData = mysqli_fetch_array(mysqli_query($kon, "SELECT * FROM audit_persiapan WHERE kodeaudit='$ka'"));
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

                        // Cek apakah tabel ada
                        $hasTable = false;
                        $checkTable = mysqli_query($kon, "SHOW TABLES LIKE 'audit_triangulasi_detail'");
                        if(mysqli_num_rows($checkTable) > 0) {
                            $hasTable = true;
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
                        if($hasTable) {
                            $qT = mysqli_query($kon, "SELECT * FROM audit_triangulasi_detail WHERE kodeaudit='$ka'");
                            if($qT) {
                                while($t = mysqli_fetch_assoc($qT)) {
                                    $triDataMap[$t['no_risiko']] = $t;
                                }
                            }
                        }
                        ?>

                        <?php if(count($risks) == 0): ?>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>Belum ada data risiko. Silakan selesaikan form Catatan Persiapan terlebih dahulu.
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

                            <!-- List Tabel Triangulasi (Read Only) -->
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
                                        <tr class="risk-card-item" data-standar="<?php echo htmlspecialchars($stdName); ?>">
                                            <td class="text-center fw-bold text-primary"><?php echo htmlspecialchars($noR); ?></td>
                                            <td>
                                                <div class="fw-bold text-dark mb-1"><?php echo htmlspecialchars($r['pernyataan_risiko']); ?></div>
                                                <div class="small text-muted"><i class="fas fa-bullseye me-1 text-secondary"></i><?php echo htmlspecialchars($r['indikator_target']); ?></div>
                                            </td>
                                            <td class="text-center">
                                                <?php if($hasDok): ?>
                                                    <span class="badge bg-success mb-1"><i class="fas fa-check-circle me-1"></i>Terisi</span><br>
                                                    <span class="badge bg-light text-dark border"><?php echo htmlspecialchars($tData['dokumen_konsisten'] ?? '-'); ?></span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary"><i class="fas fa-times-circle me-1"></i>Belum</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <?php if($hasWaw): ?>
                                                    <span class="badge bg-success mb-1"><i class="fas fa-check-circle me-1"></i>Terisi</span><br>
                                                    <span class="badge bg-light text-dark border"><?php echo htmlspecialchars($tData['wawancara_konsisten'] ?? '-'); ?></span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary"><i class="fas fa-times-circle me-1"></i>Belum</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <?php if($hasObs): ?>
                                                    <span class="badge bg-success mb-1"><i class="fas fa-check-circle me-1"></i>Terisi</span><br>
                                                    <span class="badge bg-light text-dark border"><?php echo htmlspecialchars($tData['observasi_konsisten'] ?? '-'); ?></span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary"><i class="fas fa-times-circle me-1"></i>Belum</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <?php if($hasKes): ?>
                                                    <span class="badge bg-info text-dark mb-1"><?php echo htmlspecialchars($tData['kategori_temuan'] ?? 'Kesimpulan'); ?></span><br>
                                                    <span class="badge bg-primary"><?php echo htmlspecialchars($tData['validitas'] ?? '-'); ?></span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary"><i class="fas fa-times-circle me-1"></i>Belum</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-outline-info btn-sm rounded-pill px-3 shadow-sm" onclick="openTriangulasiDetailModal('<?php echo htmlspecialchars($noR); ?>')">
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

<!-- Modal Detail Triangulasi (Read-only) -->
<div class="modal fade" id="modalDetailTriangulasi" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">
            <div class="modal-header bg-primary text-white py-3">
                <h5 class="modal-title font-bold text-white mb-0" id="detailModalTitle">
                    <i class="fas fa-balance-scale me-2"></i> Detail Triangulasi Bukti
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 bg-light">
                <!-- Pernyataan Standar & Risiko Card in Modal Body -->
                <div class="card border-0 shadow-sm p-3 mb-4 rounded-3 bg-white border-start border-primary border-4">
                    <div class="d-flex align-items-center mb-1">
                        <span class="badge bg-primary me-2" id="detailModalBadge">R-1</span>
                        <h6 class="fw-bold mb-0 text-dark">Pernyataan Standar & Risiko</h6>
                    </div>
                    <div class="text-dark small lh-base mt-1" id="detailModalSub">-</div>
                </div>

                <!-- Sumber 1 -->
                <div class="card border-0 shadow-sm p-3 mb-3 rounded-3 border-start border-primary border-4">
                    <h6 class="text-primary fw-bold mb-3"><i class="fas fa-file-alt me-2"></i>Sumber 1 — Telaah Dokumen</h6>
                    <div class="row g-3">
                        <div class="col-md-5"><strong>Temuan Auditor:</strong><p class="small text-muted mb-0" id="det_dok_temuan">-</p></div>
                        <div class="col-md-4"><strong>Klaim Unit:</strong><p class="small text-muted mb-0" id="det_dok_klaim">-</p></div>
                        <div class="col-md-3"><strong>Konsistensi:</strong><br><span class="badge bg-secondary" id="det_dok_kon">-</span></div>
                    </div>
                </div>

                <!-- Sumber 2 -->
                <div class="card border-0 shadow-sm p-3 mb-3 rounded-3 border-start border-success border-4">
                    <h6 class="text-success fw-bold mb-3"><i class="fas fa-comments me-2"></i>Sumber 2 — Wawancara Multi-Pihak</h6>
                    <div class="row g-3">
                        <div class="col-md-5"><strong>Temuan Auditor:</strong><p class="small text-muted mb-0" id="det_waw_temuan">-</p></div>
                        <div class="col-md-4"><strong>Klaim Unit:</strong><p class="small text-muted mb-0" id="det_waw_klaim">-</p></div>
                        <div class="col-md-3"><strong>Konsistensi:</strong><br><span class="badge bg-secondary" id="det_waw_kon">-</span></div>
                    </div>
                </div>

                <!-- Sumber 3 -->
                <div class="card border-0 shadow-sm p-3 mb-3 rounded-3 border-start border-purple border-4" style="border-left-color: #8b5cf6 !important;">
                    <h6 class="fw-bold mb-3" style="color: #8b5cf6;"><i class="fas fa-eye me-2"></i>Sumber 3 — Observasi Langsung</h6>
                    <div class="row g-3">
                        <div class="col-md-5"><strong>Temuan Auditor:</strong><p class="small text-muted mb-0" id="det_obs_temuan">-</p></div>
                        <div class="col-md-4"><strong>Klaim Unit:</strong><p class="small text-muted mb-0" id="det_obs_klaim">-</p></div>
                        <div class="col-md-3"><strong>Konsistensi:</strong><br><span class="badge bg-secondary" id="det_obs_kon">-</span></div>
                    </div>
                </div>

                <!-- Kesimpulan -->
                <div class="card border-0 shadow-sm p-3 rounded-3 bg-white">
                    <h6 class="text-dark fw-bold mb-3"><i class="fas fa-clipboard-check me-2 text-warning"></i>Kesimpulan Triangulasi</h6>
                    <p class="mb-2"><strong>Catatan Auditor:</strong><br><span class="text-muted small" id="det_catatan">-</span></p>
                    <div class="d-flex gap-2 flex-wrap mt-3">
                        <span class="badge bg-info text-dark" id="det_kat">-</span>
                        <span class="badge bg-primary" id="det_valid">-</span>
                        <span class="badge bg-danger" id="det_residual">-</span>
                        <span class="badge bg-secondary" id="det_kesimpulan">-</span>
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

function openTriangulasiDetailModal(noRisiko) {
    const rData = risksDataMap[noRisiko] || {};
    const tData = triangulasiDataMap[noRisiko] || {};

    document.getElementById('detailModalTitle').innerHTML = '<i class="fas fa-balance-scale me-2"></i> Detail Triangulasi ' + noRisiko;
    document.getElementById('detailModalBadge').innerText = noRisiko;
    document.getElementById('detailModalSub').innerText = rData.pernyataan_risiko || '';

    document.getElementById('det_dok_temuan').innerText = tData.dokumen_temuan || '-';
    document.getElementById('det_dok_klaim').innerText = tData.dokumen_klaim || '-';
    document.getElementById('det_dok_kon').innerText = tData.dokumen_konsisten || '-';

    document.getElementById('det_waw_temuan').innerText = tData.wawancara_temuan || '-';
    document.getElementById('det_waw_klaim').innerText = tData.wawancara_klaim || '-';
    document.getElementById('det_waw_kon').innerText = tData.wawancara_konsisten || '-';

    document.getElementById('det_obs_temuan').innerText = tData.observasi_temuan || '-';
    document.getElementById('det_obs_klaim').innerText = tData.observasi_klaim || '-';
    document.getElementById('det_obs_kon').innerText = tData.observasi_konsisten || '-';

    document.getElementById('det_catatan').innerText = tData.catatan_auditor || '-';
    document.getElementById('det_kat').innerText = 'Kategori: ' + (tData.kategori_temuan || '-');
    document.getElementById('det_valid').innerText = 'Validitas: ' + (tData.validitas || '-');
    document.getElementById('det_residual').innerText = 'Risiko Residual: ' + (tData.risiko_residual || '-');
    document.getElementById('det_kesimpulan').innerText = 'Kesimpulan: ' + (tData.kesimpulan || '-');

    const modalEl = document.getElementById('modalDetailTriangulasi');
    const modal = new bootstrap.Modal(modalEl);
    modal.show();
}
</script>
<?php 
include 'foot.php';
?>
