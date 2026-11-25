<?php
include 'nav.php';
?>
<style>
    .lk2-input-card {
        border-radius: 14px;
        border: 1px solid #e5e7eb;
        margin-bottom: 2rem;
        background: #fff;
        box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        overflow: hidden;
        transition: box-shadow 0.2s;
    }
    .lk2-input-card:hover {
        box-shadow: 0 6px 20px rgba(79,70,229,0.1);
    }
    .lk2-input-header {
        background: linear-gradient(to right, #eff6ff, #f0f9ff);
        border-bottom: 1px solid #dbeafe;
        padding: 1.1rem 1.5rem;
    }
    .lk2-input-header.header-positif {
        background: linear-gradient(to right, #f0fdf4, #dcfce7);
        border-bottom-color: #bbf7d0;
    }
    .lk2-input-body {
        padding: 1.5rem;
    }
    .lk2-section-label {
        font-size: 0.72rem;
        font-weight: 800;
        letter-spacing: 0.8px;
        text-transform: uppercase;
        color: #6366f1;
        margin-bottom: 0.5rem;
        padding-bottom: 0.35rem;
        border-bottom: 2px solid #e0e7ff;
    }
    .lk2-section-label.positif {
        color: #16a34a;
        border-bottom-color: #bbf7d0;
    }
    .plor-input-grid {
        display: grid;
        grid-template-columns: 26px 1fr;
        gap: 0.4rem 0.6rem;
        align-items: start;
    }
    .plor-key {
        font-weight: 800;
        font-size: 1rem;
        color: #3b82f6;
        padding-top: 0.45rem;
    }
    .plor-key.positif { color: #16a34a; }
    .lk2-textarea {
        border: 1px solid #e2e8f0;
        border-radius: 7px;
        padding: 0.55rem 0.75rem;
        font-size: 0.875rem;
        width: 100%;
        background: #f8fafc;
        transition: border-color 0.2s, background 0.2s, box-shadow 0.2s;
        resize: vertical;
        min-height: 72px;
        color: #1e293b;
        line-height: 1.55;
    }
    .lk2-textarea:focus {
        outline: none;
        border-color: #6366f1;
        background: #fff;
        box-shadow: 0 0 0 3px rgba(99,102,241,0.12);
    }
    .lk2-input-sm {
        border: 1px solid #e2e8f0;
        border-radius: 7px;
        padding: 0.45rem 0.65rem;
        font-size: 0.875rem;
        background: #f8fafc;
        transition: border-color 0.2s, background 0.2s, box-shadow 0.2s;
        color: #1e293b;
        width: 100%;
    }
    .lk2-input-sm:focus {
        outline: none;
        border-color: #6366f1;
        background: #fff;
        box-shadow: 0 0 0 3px rgba(99,102,241,0.12);
    }
    .skor-display {
        font-size: 1.4rem;
        font-weight: 900;
        padding: 0.4rem 1.1rem;
        border-radius: 8px;
        background: #f1f5f9;
        color: #1e293b;
        border: 1px solid #e2e8f0;
        min-width: 60px;
        text-align: center;
    }
    .skor-display.merah   { background: #fef2f2; color: #dc2626; border-color: #fecaca; }
    .skor-display.oranye  { background: #fff7ed; color: #ea580c; border-color: #fed7aa; }
    .skor-display.kuning  { background: #fefce8; color: #ca8a04; border-color: #fef08a; }
    .skor-display.hijau   { background: #f0fdf4; color: #16a34a; border-color: #bbf7d0; }
    .save-btn {
        font-size: 0.82rem;
        font-weight: 700;
        padding: 0.45rem 1.2rem;
        border-radius: 7px;
        border: none;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        background: #4f46e5;
        color: #fff;
        transition: background 0.2s, transform 0.1s;
    }
    .save-btn:hover { background: #4338ca; }
    .save-btn:active { transform: scale(0.97); }
    .save-btn.saving { opacity: 0.7; cursor: not-allowed; }
    .save-status {
        font-size: 0.8rem;
        padding: 0.3rem 0.7rem;
        border-radius: 6px;
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
    }
    .save-status.ok   { background: #f0fdf4; color: #16a34a; border: 1px solid #bbf7d0; }
    .save-status.err  { background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; }
    .lk2-form-label {
        font-size: 0.78rem;
        font-weight: 700;
        color: #475569;
        margin-bottom: 0.3rem;
        display: block;
    }
    .ref-box {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 0.7rem 1rem;
        font-size: 0.82rem;
        color: #64748b;
    }
    .ref-box strong { color: #334155; }
    .section-divider {
        border: 0; border-top: 2px solid #e5e7eb; margin: 2.5rem 0;
    }
    .kesimpulan-card {
        background: linear-gradient(135deg, #fafafa 0%, #f8f0ff 100%);
        border: 1px solid #e9d5ff;
        border-radius: 14px;
        padding: 1.5rem;
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
                                <h4 class="font-bold mb-1">LK2 — Lembar Kerja Audit Lapangan</h4>
                                <p class="text-muted mb-0">Kode Audit: <strong>#<?php echo htmlspecialchars($_GET['ka']);?></strong> | Terintegrasi dengan Form A</p>
                            </div>
                            <div class="mt-3 mt-md-0">
                                <a class="btn btn-outline-secondary btn-sm" href="audit.progres?ka=<?php echo urlencode($_GET['ka']);?>&et=<?php echo urlencode($_GET['et']);?>"><i class="fas fa-caret-left me-1"></i> Kembali</a>
                            </div>
                        </div>

                        <?php 
                        include "audit.validasi.php";
                        $ka = mysqli_real_escape_string($kon, $_GET['ka']);
                        $et = mysqli_real_escape_string($kon, $_GET['et']);

                        // Auto-create tables
                        $checkTable = mysqli_query($kon, "SHOW TABLES LIKE 'audit_lk2_temuan'");
                        if(mysqli_num_rows($checkTable) == 0) {
                            mysqli_query($kon, "
                                CREATE TABLE audit_lk2_temuan (
                                    id INT AUTO_INCREMENT PRIMARY KEY,
                                    kodeaudit VARCHAR(20) NOT NULL,
                                    no_risiko VARCHAR(20) NOT NULL,
                                    tipe_temuan VARCHAR(20),
                                    plor_p TEXT,
                                    plor_l TEXT,
                                    plor_o TEXT,
                                    plor_r TEXT,
                                    akar_penyebab TEXT,
                                    risiko_potensial TEXT,
                                    dampak INT,
                                    peluang INT,
                                    skor INT,
                                    level VARCHAR(20),
                                    tren VARCHAR(20),
                                    pengendalian_efektivitas TEXT,
                                    rekomendasi_ptk TEXT,
                                    rekomendasi_rtm TEXT,
                                    faktor_pendukung TEXT,
                                    peluang_peningkatan TEXT,
                                    potensi_replikasi TEXT,
                                    entri TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                                    UNIQUE KEY uk_ka_risiko (kodeaudit, no_risiko)
                                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
                            ");
                        }
                        $checkTableKes = mysqli_query($kon, "SHOW TABLES LIKE 'audit_lk2_kesimpulan'");
                        if(mysqli_num_rows($checkTableKes) == 0) {
                            mysqli_query($kon, "
                                CREATE TABLE audit_lk2_kesimpulan (
                                    kodeaudit VARCHAR(20) PRIMARY KEY,
                                    catatan_umum TEXT,
                                    rekomendasi_strategis TEXT,
                                    entri TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
                            ");
                        }

                        // Handle Digital Submission
                        if(isset($_POST['simpan_lk2'])) {
                            if(!empty($_POST['lk2_no_risiko']) && is_array($_POST['lk2_no_risiko'])) {
                                foreach($_POST['lk2_no_risiko'] as $idx => $noRisiko) {
                                    $noR = mysqli_real_escape_string($kon, $noRisiko);
                                    $tipe = mysqli_real_escape_string($kon, $_POST['tipe_temuan'][$idx]);
                                    
                                    $p = mysqli_real_escape_string($kon, $_POST['plor_p'][$idx] ?? '');
                                    $l = mysqli_real_escape_string($kon, $_POST['plor_l'][$idx] ?? '');
                                    $o = mysqli_real_escape_string($kon, $_POST['plor_o'][$idx] ?? '');
                                    $r = mysqli_real_escape_string($kon, $_POST['plor_r'][$idx] ?? '');
                                    
                                    $akar = mysqli_real_escape_string($kon, $_POST['akar_penyebab'][$idx] ?? '');
                                    $rp = mysqli_real_escape_string($kon, $_POST['risiko_potensial'][$idx] ?? '');
                                    $d = isset($_POST['dampak'][$idx]) ? (int)$_POST['dampak'][$idx] : 0;
                                    $pel = isset($_POST['probabilitas'][$idx]) ? (int)$_POST['probabilitas'][$idx] : 0;
                                    $skor = $d * $pel;
                                    $lvl = '';
                                    if($skor >= 10) $lvl = 'MERAH';
                                    elseif($skor >= 5) $lvl = 'KUNING';
                                    else $lvl = 'HIJAU';
                                    
                                    $ptk = mysqli_real_escape_string($kon, $_POST['rekomendasi_ptk'][$idx] ?? '');
                                    $rtm = mysqli_real_escape_string($kon, $_POST['rekomendasi_rtm'][$idx] ?? '');
                                    
                                    $fp = mysqli_real_escape_string($kon, $_POST['faktor_pendukung'][$idx] ?? '');
                                    $pp = mysqli_real_escape_string($kon, $_POST['peluang_peningkatan'][$idx] ?? '');
                                    $pr = mysqli_real_escape_string($kon, $_POST['potensi_replikasi'][$idx] ?? '');
                                    
                                    $checkData = mysqli_query($kon, "SELECT id FROM audit_lk2_temuan WHERE kodeaudit='$ka' AND no_risiko='$noR'");
                                    if(mysqli_num_rows($checkData) > 0) {
                                        mysqli_query($kon, "UPDATE audit_lk2_temuan SET tipe_temuan='$tipe', plor_p='$p', plor_l='$l', plor_o='$o', plor_r='$r', akar_penyebab='$akar', risiko_potensial='$rp', dampak='$d', peluang='$pel', skor='$skor', level='$lvl', rekomendasi_ptk='$ptk', rekomendasi_rtm='$rtm', faktor_pendukung='$fp', peluang_peningkatan='$pp', potensi_replikasi='$pr' WHERE kodeaudit='$ka' AND no_risiko='$noR'");
                                    } else {
                                        mysqli_query($kon, "INSERT INTO audit_lk2_temuan (kodeaudit, no_risiko, tipe_temuan, plor_p, plor_l, plor_o, plor_r, akar_penyebab, risiko_potensial, dampak, peluang, skor, level, rekomendasi_ptk, rekomendasi_rtm, faktor_pendukung, peluang_peningkatan, potensi_replikasi) VALUES ('$ka', '$noR', '$tipe', '$p', '$l', '$o', '$r', '$akar', '$rp', '$d', '$pel', '$skor', '$lvl', '$ptk', '$rtm', '$fp', '$pp', '$pr')");
                                    }
                                }
                            }
                            
                            $cU = mysqli_real_escape_string($kon, $_POST['catatan_umum'] ?? '');
                            $rS = mysqli_real_escape_string($kon, $_POST['rekomendasi_strategis'] ?? '');
                            $cK = mysqli_query($kon, "SELECT kodeaudit FROM audit_lk2_kesimpulan WHERE kodeaudit='$ka'");
                            if(mysqli_num_rows($cK)>0) {
                                mysqli_query($kon, "UPDATE audit_lk2_kesimpulan SET catatan_umum='$cU', rekomendasi_strategis='$rS' WHERE kodeaudit='$ka'");
                            } else {
                                mysqli_query($kon, "INSERT INTO audit_lk2_kesimpulan (kodeaudit, catatan_umum, rekomendasi_strategis) VALUES ('$ka', '$cU', '$rS')");
                            }

                            // Save header details to audit_persiapan
                            $h_ketua = mysqli_real_escape_string($kon, $_POST['header_ketua_tim'] ?? '');
                            $h_anggota = mysqli_real_escape_string($kon, $_POST['header_anggota_tim'] ?? '');
                            $h_tanggal = mysqli_real_escape_string($kon, $_POST['header_tanggal_audit'] ?? '');
                            $h_unit = mysqli_real_escape_string($kon, $_POST['header_unit_diaudit'] ?? '');
                            $h_auditee = mysqli_real_escape_string($kon, $_POST['header_auditee_kaprodi'] ?? '');
                            $h_no_dok = mysqli_real_escape_string($kon, $_POST['header_no_dokumen'] ?? '');
                            $h_standar = mysqli_real_escape_string($kon, $_POST['header_standar_diaudit'] ?? '');
                            $h_status_form = mysqli_real_escape_string($kon, $_POST['header_status_form_a'] ?? '');
                            $h_periode = mysqli_real_escape_string($kon, $_POST['header_periode_ami'] ?? '');

                            $checkHeader = mysqli_query($kon, "SELECT id FROM audit_persiapan WHERE kodeaudit='$ka'");
                            if(mysqli_num_rows($checkHeader) > 0) {
                                mysqli_query($kon, "UPDATE audit_persiapan SET 
                                    ketua_tim='$h_ketua', 
                                    anggota_tim='$h_anggota', 
                                    tanggal_audit_dok=" . (empty($h_tanggal) ? "NULL" : "'$h_tanggal'") . ", 
                                    unit_diaudit='$h_unit', 
                                    auditee_kaprodi='$h_auditee', 
                                    no_dokumen='$h_no_dok', 
                                    standar_diaudit='$h_standar', 
                                    ref_form_pra_ami='$h_status_form',
                                    periode_ami='$h_periode' 
                                    WHERE kodeaudit='$ka'");
                            } else {
                                mysqli_query($kon, "INSERT INTO audit_persiapan 
                                    (kodeaudit, ketua_tim, anggota_tim, tanggal_audit_dok, unit_diaudit, auditee_kaprodi, no_dokumen, standar_diaudit, ref_form_pra_ami, periode_ami) 
                                    VALUES 
                                    ('$ka', '$h_ketua', '$h_anggota', " . (empty($h_tanggal) ? "NULL" : "'$h_tanggal'") . ", '$h_unit', '$h_auditee', '$h_no_dok', '$h_standar', '$h_status_form', '$h_periode')");
                            }
                            echo '<div class="alert alert-success alert-dismissible fade show"><i class="fas fa-check-circle me-2"></i>Data LK2 Digital &amp; Header berhasil disimpan.<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
                        }

                        // Handle upload PDF (Legacy)
                        if(isset($_POST['upload'])) {
                            $idaudit=mysqli_real_escape_string($kon, $_POST['idaudit']);
                            $kodeaudit=mysqli_real_escape_string($kon, $_POST['kodeaudit']);
                            $upt=$_FILES['ami']['tmp_name'];
                            $upn=$_FILES['ami']['name'];
                            
                            $gka=mysqli_query($kon,"select * from audit where kodeaudit='$kodeaudit'");
                            $gk=mysqli_fetch_array($gka);
                            
                            $nupn=$kodeaudit.'_LK2_'.$upn;
                            $upload=mysqli_query($kon,"update audit set lk2='$nupn' where kodeaudit='$kodeaudit'");
                            
                            if($upload) {
                                if(!empty($gk['lk2'])) { @unlink("../assets/laporan/".$gk['lk2']); }
                                move_uploaded_file($upt,"../assets/laporan/".$nupn);
                                echo '<div class="alert alert-success alert-dismissible fade show"><i class="fas fa-check-circle me-2"></i>Berkas PDF LK2 berhasil disimpan.<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
                            } else {
                                echo '<div class="alert alert-danger alert-dismissible fade show"><i class="fas fa-times-circle me-2"></i>Berkas PDF LK2 gagal disimpan.<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
                            }
                        }

                        // Handle hapus PDF (Legacy)
                        if(isset($_GET['hapus'])) {
                            $berkas = mysqli_real_escape_string($kon, $_GET['berkas']);
                            @unlink('../assets/laporan/'.$berkas);
                            mysqli_query($kon,"update audit set lk2='' where kodeaudit='$ka'");
                            echo "<script>alert('Berkas LK2 berhasil direset'); window.location='audit.lk2?ka=".urlencode($ka)."&et=".urlencode($et)."';</script>";
                            exit;
                        }

                        $gk = mysqli_fetch_array(mysqli_query($kon,"select * from audit where kodeaudit='$ka'"));
                        
                        // Ambil data LK2 yang sudah tersimpan DULU (agar bisa dipakai untuk pengelompokan)
                        $lk2Map = [];
                        $qT = mysqli_query($kon, "SELECT * FROM audit_lk2_temuan WHERE kodeaudit='$ka'");
                        if($qT) {
                            while($t = mysqli_fetch_assoc($qT)) {
                                $lk2Map[$t['no_risiko']] = $t;
                            }
                        }
                        $lk2Kes = [];
                        $qK = mysqli_query($kon, "SELECT * FROM audit_lk2_kesimpulan WHERE kodeaudit='$ka'");
                        if($qK) {
                            $lk2Kes = mysqli_fetch_assoc($qK);
                        }

                        // ============================================================
                        // Load Header details from audit_persiapan & fallback to audit / auditor tables
                        // ============================================================
                        $qKetua = mysqli_query($kon, "SELECT user FROM auditor WHERE kodeaudit='$ka' AND status='Ketua' LIMIT 1");
                        $idKetua = $qKetua && mysqli_num_rows($qKetua) > 0 ? mysqli_fetch_assoc($qKetua)['user'] : '';

                        $anggotaIds = [];
                        $qAnggota = mysqli_query($kon, "SELECT user FROM auditor WHERE kodeaudit='$ka' AND status LIKE '%Anggota%'");
                        if($qAnggota) {
                            while($row = mysqli_fetch_assoc($qAnggota)) {
                                $anggotaIds[] = $row['user'];
                            }
                        }

                        $pegawaiMap = [];
                        $localUsers = mysqli_query($kon, "SELECT id, nama FROM user");
                        if($localUsers) {
                            while($user = mysqli_fetch_assoc($localUsers)) {
                                $pegawaiMap[(string)$user['id']] = $user['nama'];
                            }
                        }

                        $namaKetua = $pegawaiMap[(string)$idKetua] ?? $idKetua;
                        $namaAnggotaList = [];
                        foreach($anggotaIds as $aid) {
                            $namaAnggotaList[] = $pegawaiMap[(string)$aid] ?? $aid;
                        }
                        $namaAnggota = implode(', ', $namaAnggotaList);

                        $gda = mysqli_fetch_array(mysqli_query($kon, "
                            SELECT a.*, unit.nama as nama_unit, unit.kepala as kepala_unit
                            FROM audit a 
                            LEFT JOIN unit ON a.unit = unit.id 
                            WHERE a.kodeaudit = '$ka'
                        "));

                        $qHeader = mysqli_query($kon, "SELECT * FROM audit_persiapan WHERE kodeaudit='$ka'");
                        $headerData = $qHeader ? mysqli_fetch_array($qHeader) : null;
                        $noDokumen = $headerData['no_dokumen'] ?? ('LK2-AMI/'.date('Y', strtotime($gda['tglmulai'])).'/'.($gda['unit'] ?? ''));
                        $tglAuditDok = $headerData['tanggal_audit_dok'] ?? ($gda['tglmulai'] ?? '');
                        $ketuaTim = $headerData['ketua_tim'] ?? $namaKetua;
                        $anggotaTim = $headerData['anggota_tim'] ?? $namaAnggota;
                        $unitDiaudit = $headerData['unit_diaudit'] ?? ($gda['nama_unit'] ?? '');
                        $auditeeKaprodi = $headerData['auditee_kaprodi'] ?? ($gda['kepala_unit'] ?? '');
                        $standarDiaudit = $headerData['standar_diaudit'] ?? 'Standar Proses Pembelajaran (SPMI-STD-03)';
                        $qDefaultPra = mysqli_query($kon, "
                            SELECT pr.id, pr.tahun, pr.unit 
                            FROM praaudit pr 
                            JOIN audit a ON pr.unit = a.unit AND pr.tahun = YEAR(a.tglmulai)
                            WHERE a.kodeaudit = '$ka'
                            LIMIT 1
                        ");
                        $defaultPra = mysqli_fetch_assoc($qDefaultPra);
                        if ($defaultPra) {
                            $defaultRef = 'Form-PRA/' . $defaultPra['tahun'] . '/' . $defaultPra['unit'] . '/' . sprintf('%03d', $defaultPra['id']);
                        } else {
                            $defaultRef = '';
                        }
                        $refFormPraAmi = $headerData['ref_form_pra_ami'] ?? $defaultRef;
                        $periodeAmi = $headerData['periode_ami'] ?? ('Sem. Genap / TA '.date('Y', strtotime($gda['tglmulai'])).'-'.(date('Y', strtotime($gda['tglmulai']))+1));

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
                                    
                                    // If Kondisi Awal & Pengendalian not filled, leave D/P/Skor/Level empty
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

                        // Query risks langsung dari audit_persiapan_risiko
                        // Tidak perlu triangulasi selesai terlebih dahulu
                        $risks = [];
                        $qRisks = mysqli_query($kon, "
                            SELECT r.no_risiko, r.pernyataan_risiko, r.indikator_target
                            FROM audit_persiapan_risiko r
                            WHERE r.kodeaudit='$ka'
                            ORDER BY CAST(REPLACE(r.no_risiko, 'R-', '') AS UNSIGNED) ASC
                        ");
                        if($qRisks) {
                            while($row = mysqli_fetch_assoc($qRisks)) {
                                $risks[] = $row;
                            }
                        }
                        ?>

                        <?php if(empty($risks)): ?>
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle me-2"></i>Belum ada data risiko dari Form Persiapan Audit. Silakan isi Form Persiapan terlebih dahulu.
                            </div>
                        <?php else: ?>
                            <!-- ============================================================
                                 LK2 HEADER: DOKUMEN & TIM AUDITOR
                                 ============================================================ -->
                            <div class="card lk2-input-card mb-4" id="card_header">
                                <div class="lk2-input-header" style="background: linear-gradient(to right, #f8fafc, #f1f5f9); border-bottom: 1px solid #e2e8f0;">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge bg-secondary"><i class="fas fa-file-alt"></i></span>
                                        <strong class="text-dark">Informasi Dokumen &amp; Auditor (Header LK2)</strong>
                                    </div>
                                </div>
                                <div class="lk2-input-body">
                                    <form id="form_header" onsubmit="return false;">
                                        <input type="hidden" name="kodeaudit" value="<?php echo htmlspecialchars($ka); ?>">
                                        <div class="table-responsive">
                                            <table class="table table-bordered align-middle text-dark bg-white" style="font-size: 0.85rem; border-color: #cbd5e1;">
                                                <tbody>
                                                    <tr>
                                                        <td style="background-color: #f8fafc; font-weight: 700; width: 15%;">Ketua Tim Auditor</td>
                                                        <td style="width: 18%;"><input type="text" name="ketua_tim" class="form-control form-control-sm border-0 bg-transparent p-0" value="<?php echo htmlspecialchars($ketuaTim); ?>" style="box-shadow: none;"></td>
                                                        <td style="background-color: #f8fafc; font-weight: 700; width: 12%;">Anggota Tim</td>
                                                        <td style="width: 25%;"><input type="text" name="anggota_tim" class="form-control form-control-sm border-0 bg-transparent p-0" value="<?php echo htmlspecialchars($anggotaTim); ?>" style="box-shadow: none;"></td>
                                                        <td style="background-color: #f8fafc; font-weight: 700; width: 12%;">Tanggal Audit</td>
                                                        <td style="width: 18%;"><input type="text" name="tanggal_audit_dok" class="form-control form-control-sm border-0 bg-transparent p-0" value="<?php echo htmlspecialchars($tglAuditDok); ?>" style="box-shadow: none;"></td>
                                                    </tr>
                                                    <tr>
                                                        <td style="background-color: #f8fafc; font-weight: 700;">Unit yang Diaudit</td>
                                                        <td><input type="text" name="unit_diaudit" class="form-control form-control-sm border-0 bg-transparent p-0" value="<?php echo htmlspecialchars($unitDiaudit); ?>" style="box-shadow: none;"></td>
                                                        <td style="background-color: #f8fafc; font-weight: 700;">Auditee / Kaprodi</td>
                                                        <td><input type="text" name="auditee_kaprodi" class="form-control form-control-sm border-0 bg-transparent p-0" value="<?php echo htmlspecialchars($auditeeKaprodi); ?>" style="box-shadow: none;"></td>
                                                        <td style="background-color: #f8fafc; font-weight: 700;">No. Dokumen</td>
                                                        <td><input type="text" name="no_dokumen" class="form-control form-control-sm border-0 bg-transparent p-0" value="<?php echo htmlspecialchars($noDokumen); ?>" style="box-shadow: none;"></td>
                                                    </tr>
                                                    <tr>
                                                        <td style="background-color: #f8fafc; font-weight: 700;">Standar yang Diaudit</td>
                                                        <td><input type="text" name="standar_diaudit" class="form-control form-control-sm border-0 bg-transparent p-0" value="<?php echo htmlspecialchars($standarDiaudit); ?>" style="box-shadow: none;"></td>
                                                        <td style="background-color: #f8fafc; font-weight: 700;">Status Form A</td>
                                                        <td>
                                                             <?php
                                                             $qPraAudits = mysqli_query($kon, "
                                                                 SELECT pr.id, pr.tahun, pr.unit, pkt.namapaket
                                                                 FROM praaudit pr
                                                                 JOIN paketpertanyaan pkt ON pr.paket = pkt.id
                                                                 WHERE pr.unit = '" . (int)$gda['unit'] . "'
                                                                 ORDER BY pr.tahun DESC, pr.id DESC
                                                             ");
                                                             $praAuditOptions = [];
                                                             if ($qPraAudits && mysqli_num_rows($qPraAudits) > 0) {
                                                                 while ($pr = mysqli_fetch_assoc($qPraAudits)) {
                                                                     $code = 'Form-PRA/' . $pr['tahun'] . '/' . $pr['unit'] . '/' . sprintf('%03d', $pr['id']);
                                                                     $praAuditOptions[] = [
                                                                         'code' => $code,
                                                                         'label' => 'Form Pra-AMI ' . $pr['namapaket'] . ' (' . $pr['tahun'] . ')'
                                                                     ];
                                                                 }
                                                             }
                                                             ?>
                                                             <select name="ref_form_pra_ami" class="form-select form-select-sm border-0 bg-transparent p-0 text-dark fw-bold" style="box-shadow: none; font-size: 0.85rem; outline: none; cursor: pointer;">
                                                                 <option value="">— Pilih —</option>
                                                                 <?php foreach ($praAuditOptions as $opt): ?>
                                                                     <?php $selected = ($refFormPraAmi === $opt['code']) ? 'selected' : ''; ?>
                                                                     <option value="<?php echo htmlspecialchars($opt['code']); ?>" <?php echo $selected; ?>>
                                                                         <?php echo htmlspecialchars($opt['label']); ?> (<?php echo htmlspecialchars($opt['code']); ?>)
                                                                     </option>
                                                                 <?php endforeach; ?>
                                                                 <?php
                                                                 $hasSaved = false;
                                                                 foreach ($praAuditOptions as $opt) {
                                                                     if ($opt['code'] === $refFormPraAmi) { $hasSaved = true; break; }
                                                                 }
                                                                 if (!$hasSaved && !empty($refFormPraAmi)) {
                                                                     echo '<option value="' . htmlspecialchars($refFormPraAmi) . '" selected>' . htmlspecialchars($refFormPraAmi) . '</option>';
                                                                 }
                                                                 ?>
                                                             </select>
                                                         </td>
                                                        <td style="background-color: #f8fafc; font-weight: 700;">Periode AMI</td>
                                                        <td><input type="text" name="periode_ami" class="form-control form-control-sm border-0 bg-transparent p-0" value="<?php echo htmlspecialchars($periodeAmi); ?>" style="box-shadow: none;"></td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="d-flex align-items-center gap-3 mt-3 pt-3 border-top">
                                            <button type="button" class="save-btn" style="background:#0f766e;" onclick="simpanHeader()">
                                                <i class="fas fa-save"></i> Simpan Header LK2
                                            </button>
                                            <span class="save-status" id="status_header" style="display:none;"></span>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            <!-- ============================================================
                                 CONSOLIDATED RISKS TABLE & FILTER
                                 ============================================================ -->
                            <div class="d-flex justify-content-between align-items-center mb-3 mt-4">
                                <h5 class="text-primary mb-0"><i class="fas fa-exclamation-circle me-2"></i> Daftar Risiko &amp; Temuan Audit</h5>
                                <span class="badge bg-light text-dark border">Total: <?= count($risks) ?> Risiko</span>
                            </div>

                            <!-- Filter Standar -->
                            <?php 
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
                                    while ($st = mysqli_fetch_assoc($qStds)) {
                                        $uniqueStandards[] = $st['namastandar'];
                                    }
                                }
                            }
                            if (empty($uniqueStandards)) {
                                foreach ($risks as $r) {
                                    $parts = explode(' - ', $r['pernyataan_risiko'], 2);
                                    $stdName = count($parts) > 1 ? trim($parts[0]) : '';
                                    if (!empty($stdName) && !in_array($stdName, $uniqueStandards, true)) {
                                        $uniqueStandards[] = $stdName;
                                    }
                                }
                            }
                            ?>
                            <div class="row mb-3 align-items-end">
                                <div class="col-md-4">
                                    <label class="form-label fw-bold small text-dark mb-1"><i class="fas fa-filter text-primary me-1"></i> Filter Berdasarkan Standar</label>
                                    <select id="filter-standar-rekap" class="form-select form-select-sm text-dark bg-white border">
                                        <option value="">Semua Standar</option>
                                        <?php foreach ($uniqueStandards as $std): ?>
                                            <option value="<?php echo htmlspecialchars($std); ?>"><?php echo htmlspecialchars($std); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="table-responsive mb-4 shadow-sm border rounded bg-white">
                                <table class="table table-bordered table-striped table-hover align-middle text-dark mb-0" id="lk2Table" style="font-size: 0.88rem;">
                                    <thead class="table-light">
                                        <tr class="text-center font-bold">
                                            <th style="width: 5%;">No</th>
                                            <th style="width: 32%; text-align: left;">Pernyataan Risiko &amp; Butir Standar</th>
                                            <th style="width: 15%;">Kategori</th>
                                            <th style="width: 8%;">PLOR</th>
                                            <th style="width: 8%;">Analisis</th>
                                            <th style="width: 10%;">Residual</th>
                                            <th style="width: 8%;">Rekom</th>
                                            <th style="width: 14%;">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        foreach($risks as $idx => $r):
                                            $noR = $r['no_risiko'];
                                            $d = $lk2Map[$noR] ?? [];
                                            $parts = explode(' - ', $r['pernyataan_risiko'], 2);
                                            $stdName = count($parts) > 1 ? trim($parts[0]) : '';
                                            // 1. Kategori Temuan badge
                                            $tp = $d['tipe_temuan'] ?? '';
                                            if($tp) {
                                                $tpClass = 'bg-secondary';
                                                if($tp === 'Positif') $tpClass = 'bg-success';
                                                elseif(stripos($tp,'mayor')!==false) $tpClass = 'bg-danger';
                                                elseif(stripos($tp,'minor')!==false) $tpClass = 'bg-warning text-dark';
                                                elseif(stripos($tp,'ob')!==false) $tpClass = 'bg-info text-dark';
                                                $kategoriBadge = '<span class="badge '.$tpClass.'" id="badge-kategori-'.$noR.'">'.htmlspecialchars($tp).'</span>';
                                            } else {
                                                $kategoriBadge = '<span class="badge bg-secondary" id="badge-kategori-'.$noR.'">Belum ditentukan</span>';
                                            }

                                            // 2. PLOR Status
                                            $hasPlor = !empty($d['plor_p']) && !empty($d['plor_l']) && !empty($d['plor_o']) && !empty($d['plor_r']);
                                            $plorBadge = $hasPlor 
                                                ? '<span class="badge bg-success" id="status-plor-'.$noR.'"><i class="fas fa-check-circle"></i></span>'
                                                : '<span class="badge bg-secondary" id="status-plor-'.$noR.'"><i class="fas fa-times-circle"></i></span>';

                                            // 3. Analisis Status
                                            if($tp === 'Positif') {
                                                $hasAkar = !empty($d['faktor_pendukung']);
                                            } else {
                                                $hasAkar = !empty($d['akar_penyebab']) && !empty($d['risiko_potensial']);
                                            }
                                            $akarBadge = $hasAkar
                                                ? '<span class="badge bg-success" id="status-akar-'.$noR.'"><i class="fas fa-check-circle"></i></span>'
                                                : '<span class="badge bg-secondary" id="status-akar-'.$noR.'"><i class="fas fa-times-circle"></i></span>';

                                            // 4. Residual Status
                                            if($tp === 'Positif') {
                                                $residualBadge = '<span class="badge bg-light text-muted border" id="status-residual-'.$noR.'">N/A</span>';
                                            } else {
                                                $hasResidual = !empty($d['dampak']) && !empty($d['peluang']) && !empty($d['level']);
                                                if($hasResidual) {
                                                    $lvlStyle = 'background-color: #6b7280; color: #ffffff;';
                                                    if($d['level'] === 'MERAH') $lvlStyle = 'background-color: #fee2e2; color: #991b1b; border: 1px solid #fca5a5;';
                                                    elseif($d['level'] === 'ORANYE') $lvlStyle = 'background-color: #ffedd5; color: #c2410c; border: 1px solid #fdba74;';
                                                    elseif($d['level'] === 'KUNING') $lvlStyle = 'background-color: #fef9c3; color: #854d0e; border: 1px solid #fde047;';
                                                    elseif($d['level'] === 'HIJAU') $lvlStyle = 'background-color: #dcfce7; color: #166534; border: 1px solid #86efac;';
                                                    $residualBadge = '<span class="badge" style="'.$lvlStyle.'" id="status-residual-'.$noR.'">'.htmlspecialchars($d['level']).'</span>';
                                                } else {
                                                    $residualBadge = '<span class="badge bg-secondary" id="status-residual-'.$noR.'"><i class="fas fa-times-circle"></i></span>';
                                                }
                                            }

                                            // 5. Rekomendasi Status
                                            if($tp === 'Positif') {
                                                $rekomBadge = '<span class="badge bg-light text-muted border" id="status-rekomendasi-'.$noR.'">N/A</span>';
                                            } else {
                                                $hasRekom = !empty($d['rekomendasi_ptk']) || !empty($d['rekomendasi_rtm']);
                                                $rekomBadge = $hasRekom
                                                    ? '<span class="badge bg-success" id="status-rekomendasi-'.$noR.'"><i class="fas fa-check-circle"></i></span>'
                                                    : '<span class="badge bg-secondary" id="status-rekomendasi-'.$noR.'"><i class="fas fa-times-circle"></i></span>';
                                            }
                                        ?>
                                        <tr class="risk-row" id="row_<?= htmlspecialchars($noR) ?>" data-standard="<?= htmlspecialchars($stdName) ?>">
                                            <td class="text-center"><strong><?= $idx + 1 ?></strong></td>
                                            <td>
                                                <strong><?= htmlspecialchars($noR) ?></strong> — <?= htmlspecialchars($r['pernyataan_risiko']) ?>
                                                <?php if(!empty($r['indikator_target'])): ?>
                                                    <div class="text-muted small mt-1"><i class="fas fa-bullseye me-1"></i><?= htmlspecialchars($r['indikator_target']) ?></div>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center"><?= $kategoriBadge ?></td>
                                            <td class="text-center"><?= $plorBadge ?></td>
                                            <td class="text-center"><?= $akarBadge ?></td>
                                            <td class="text-center"><?= $residualBadge ?></td>
                                            <td class="text-center"><?= $rekomBadge ?></td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-3" onclick="openEditModal('<?= htmlspecialchars($noR) ?>')">
                                                    <i class="fas fa-edit me-1"></i> Edit Detail
                                                </button>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>

                            <!-- ============================================================
                                 MODAL EDIT DETAIL LK2 (WIZARD FLOW)
                                 ============================================================ -->
                            <div class="modal fade" id="modalEditLK2" tabindex="-1" aria-labelledby="modalEditLK2Label" aria-hidden="true">
                                <div class="modal-dialog modal-xl text-dark">
                                    <div class="modal-content shadow-lg border-0">
                                        <form id="form_modal_lk2" onsubmit="return false;">
                                            <input type="hidden" name="kodeaudit" value="<?php echo htmlspecialchars($ka); ?>">
                                            <input type="hidden" name="no_risiko" id="modal_no_risiko">
                                            <input type="hidden" name="tipe" id="modal_tipe" value="A">
                                            
                                            <div class="modal-header bg-primary text-white py-3">
                                                <h5 class="modal-title font-semibold text-white d-flex align-items-center gap-2" id="modalEditLK2Label">
                                                    <i class="fas fa-edit"></i>
                                                    <span>Edit Detail LK2: <span id="modal_title_no_risiko" class="fw-bold text-warning"></span></span>
                                                </h5>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            
                                            <div class="modal-body bg-light px-4 py-3">
                                                <!-- Standard info card -->
                                                <div class="card card-body shadow-sm border-0 mb-3" style="border-left: 4px solid #0f766e !important;">
                                                    <div class="small text-uppercase fw-bold text-muted mb-1"><i class="fas fa-bookmark me-1"></i> Pernyataan Risiko &amp; Butir Standar</div>
                                                    <div id="modal_pernyataan_risiko" class="text-dark fw-bold" style="font-size: 0.95rem;"></div>
                                                    <div id="modal_indikator_target" class="text-muted small mt-2"></div>
                                                </div>

                                                <!-- Pills navigation -->
                                                <ul class="nav nav-pills nav-fill mb-3 bg-white p-2 rounded shadow-sm border" id="modalTab" role="tablist">
                                                    <li class="nav-item" role="presentation">
                                                        <button class="nav-link active font-semibold" id="tab-plor-btn" data-bs-toggle="pill" data-bs-target="#tab-plor" type="button" role="tab"><i class="fas fa-clipboard-list me-1"></i> 1. PLOR</button>
                                                    </li>
                                                    <li class="nav-item" role="presentation">
                                                        <button class="nav-link font-semibold" id="tab-analisis-btn" data-bs-toggle="pill" data-bs-target="#tab-analisis" type="button" role="tab"><i class="fas fa-search me-1"></i> 2. Analisis &amp; Kategori</button>
                                                    </li>
                                                    <li class="nav-item" role="presentation" id="tab-residual-nav">
                                                        <button class="nav-link font-semibold" id="tab-residual-btn" data-bs-toggle="pill" data-bs-target="#tab-residual" type="button" role="tab"><i class="fas fa-chart-bar me-1"></i> 3. Risiko Residual</button>
                                                    </li>
                                                    <li class="nav-item" role="presentation" id="tab-rekomendasi-nav">
                                                        <button class="nav-link font-semibold" id="tab-rekomendasi-btn" data-bs-toggle="pill" data-bs-target="#tab-rekomendasi" type="button" role="tab"><i class="fas fa-bullhorn me-1"></i> 4. Rekomendasi</button>
                                                    </li>
                                                </ul>

                                                <!-- Tab content -->
                                                <div class="tab-content" id="modalTabContent">
                                                    <!-- Tab 1: PLOR -->
                                                    <div class="tab-pane fade show active" id="tab-plor" role="tabpanel">
                                                        <div class="card card-body border-0 shadow-sm">
                                                            <div class="lk2-section-label mb-3"><i class="fas fa-clipboard-list me-1"></i> Hasil Konfirmasi Lapangan (PLOR)</div>
                                                            <div class="plor-input-grid">
                                                                <div class="plor-key" id="lbl_plor_p">P</div>
                                                                <div>
                                                                    <label class="lk2-form-label fw-bold mb-1" id="lbl_desc_p">Problem / Permasalahan yang ditemukan</label>
                                                                    <textarea name="plor_p" id="modal_plor_p" class="lk2-textarea" rows="3" placeholder="Deskripsikan temuan..."></textarea>
                                                                </div>
                                                                <div class="plor-key" id="lbl_plor_l">L</div>
                                                                <div>
                                                                    <label class="lk2-form-label fw-bold mb-1">Lokasi / Unit / Cakupan</label>
                                                                    <textarea name="plor_l" id="modal_plor_l" class="lk2-textarea" rows="2" placeholder="Sebutkan lokasi/unit..."></textarea>
                                                                </div>
                                                                <div class="plor-key" id="lbl_plor_o">O</div>
                                                                <div>
                                                                    <label class="lk2-form-label fw-bold mb-1">Observasi / Bukti</label>
                                                                    <textarea name="plor_o" id="modal_plor_o" class="lk2-textarea" rows="3" placeholder="Bukti observasi, dokumen, wawancara..."></textarea>
                                                                </div>
                                                                <div class="plor-key" id="lbl_plor_r">R</div>
                                                                <div>
                                                                    <label class="lk2-form-label fw-bold mb-1">Referensi Standar</label>
                                                                    <textarea name="plor_r" id="modal_plor_r" class="lk2-textarea" rows="2" placeholder="Butir standar..."></textarea>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Tab 2: Analisis & Kategori -->
                                                    <div class="tab-pane fade" id="tab-analisis" role="tabpanel">
                                                        <div class="card card-body border-0 shadow-sm">
                                                            <div class="lk2-section-label mb-3"><i class="fas fa-tag me-1"></i> Kategori Temuan</div>
                                                            <div class="mb-3">
                                                                <label class="lk2-form-label fw-bold mb-1">Tipe Temuan</label>
                                                                <select name="tipe_temuan" id="modal_tipe_temuan" class="lk2-input-sm" onchange="toggleTipeTemuan(this.value)">
                                                                    <option value="">— Pilih —</option>
                                                                    <option value="OB">OB (Observasi)</option>
                                                                    <option value="KTS Minor">KTS Minor</option>
                                                                    <option value="KTS Mayor">KTS Mayor</option>
                                                                    <option value="Positif">Positif / Praktik Baik (→ LK2-B)</option>
                                                                </select>
                                                            </div>

                                                            <!-- Non-Positive fields (KTS/OB) -->
                                                            <div id="sec_kts_fields">
                                                                <div class="lk2-section-label mt-3 mb-2"><i class="fas fa-search me-1"></i> Analisis Akar Penyebab</div>
                                                                <div class="mb-3">
                                                                    <label class="lk2-form-label fw-bold mb-1">Akar Penyebab Temuan (digali saat closing meeting)</label>
                                                                    <textarea name="akar_penyebab" id="modal_akar_penyebab" class="lk2-textarea" rows="3" placeholder="Faktor-faktor akar penyebab..."></textarea>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label class="lk2-form-label fw-bold mb-1">Risiko Potensial (dampak bila tidak ditindaklanjuti)</label>
                                                                    <textarea name="risiko_potensial" id="modal_risiko_potensial" class="lk2-textarea" rows="2" placeholder="Risiko potensial jika tidak diselesaikan..."></textarea>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label class="lk2-form-label fw-bold mb-1">Pengendalian yang ada &amp; efektivitasnya</label>
                                                                    <textarea name="pengendalian_efektivitas" id="modal_pengendalian_efektivitas" class="lk2-textarea" rows="3" placeholder="Pengendalian yang diklaim dan efektivitasnya..."></textarea>
                                                                </div>
                                                            </div>

                                                            <!-- Positive fields (Praktik Baik) -->
                                                            <div id="sec_positif_fields" style="display:none;">
                                                                <div class="lk2-section-label positif mt-3 mb-2"><i class="fas fa-star me-1"></i> Pengembangan Praktik Baik</div>
                                                                <div class="mb-3">
                                                                    <label class="lk2-form-label fw-bold text-success mb-1">Faktor Pendukung Keberhasilan</label>
                                                                    <textarea name="faktor_pendukung" id="modal_faktor_pendukung" class="lk2-textarea" rows="3" placeholder="Faktor pendukung keberhasilan..."></textarea>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label class="lk2-form-label fw-bold text-success mb-1">Peluang Peningkatan</label>
                                                                    <textarea name="peluang_peningkatan" id="modal_peluang_peningkatan" class="lk2-textarea" rows="2" placeholder="Peluang peningkatan selanjutnya..."></textarea>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label class="lk2-form-label fw-bold text-success mb-1">Potensi Replikasi ke Unit Lain</label>
                                                                    <textarea name="potensi_replikasi" id="modal_potensi_replikasi" class="lk2-textarea" rows="2" placeholder="Potensi replikasi di unit/prodi lain..."></textarea>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Tab 3: Risiko Residual -->
                                                    <div class="tab-pane fade" id="tab-residual" role="tabpanel">
                                                        <div class="card card-body border-0 shadow-sm">
                                                            <div id="sec_residual_disclaimer" class="alert alert-info border-info" style="display:none;">
                                                                <i class="fas fa-info-circle me-1"></i> Tipe Temuan <strong>Positif / Praktik Baik</strong> tidak memiliki analisis Risiko Residual.
                                                            </div>
                                                            <div id="sec_residual_inputs">
                                                                <div class="lk2-section-label mb-3"><i class="fas fa-chart-bar me-1"></i> Risiko Residual (Baseline)</div>
                                                                <div class="row g-3 align-items-center mb-3">
                                                                    <div class="col-md-4">
                                                                        <label class="lk2-form-label fw-bold mb-1">Dampak (D) <span class="text-danger">1–5</span></label>
                                                                        <input type="number" name="dampak" id="modal_dampak" class="lk2-input-sm" min="1" max="5" value="1" oninput="hitungSkorModal()">
                                                                    </div>
                                                                    <div class="col-md-4">
                                                                        <label class="lk2-form-label fw-bold mb-1">Peluang (P) <span class="text-danger">1–5</span></label>
                                                                        <input type="number" name="peluang" id="modal_peluang" class="lk2-input-sm" min="1" max="5" value="1" oninput="hitungSkorModal()">
                                                                    </div>
                                                                    <div class="col-md-4 text-center">
                                                                        <label class="lk2-form-label fw-bold mb-1 d-block">Skor (D×P)</label>
                                                                        <div class="skor-display mx-auto" id="modal_skor_display">1</div>
                                                                        <input type="hidden" name="skor" id="modal_skor" value="1">
                                                                    </div>
                                                                </div>
                                                                <div class="row g-3">
                                                                    <div class="col-md-6">
                                                                        <label class="lk2-form-label fw-bold mb-1">Level Risiko (Otomatis)</label>
                                                                        <input type="text" id="modal_level_display" class="lk2-input-sm fw-bold border-0" readonly style="pointer-events: none; background: #e2e8f0; color: #475569;">
                                                                        <input type="hidden" name="level" id="modal_level">
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <label class="lk2-form-label fw-bold mb-1">Tren</label>
                                                                        <select name="tren" id="modal_tren" class="lk2-input-sm">
                                                                            <option value="N/A">N/A (Baseline pertama)</option>
                                                                            <option value="↑ Meningkat">↑ Meningkat</option>
                                                                            <option value="↓ Menurun">↓ Menurun</option>
                                                                            <option value="→ Stabil">→ Stabil</option>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Tab 4: Rekomendasi -->
                                                    <div class="tab-pane fade" id="tab-rekomendasi" role="tabpanel">
                                                        <div class="card card-body border-0 shadow-sm">
                                                            <div id="sec_rekomendasi_disclaimer" class="alert alert-info border-info" style="display:none;">
                                                                <i class="fas fa-info-circle me-1"></i> Tipe Temuan <strong>Positif / Praktik Baik</strong> tidak memerlukan rekomendasi formal.
                                                            </div>
                                                            <div id="sec_rekomendasi_inputs">
                                                                <div class="lk2-section-label mb-3"><i class="fas fa-bullhorn me-1"></i> Rekomendasi</div>
                                                                <div class="mb-3">
                                                                    <label class="lk2-form-label fw-bold mb-1">Rekomendasi PTK (Perbaikan Tingkat Kaprodi/Unit)</label>
                                                                    <textarea name="rekomendasi_ptk" id="modal_rekomendasi_ptk" class="lk2-textarea" rows="3" placeholder="Tindakan koreksi..."></textarea>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label class="lk2-form-label fw-bold mb-1">Rekomendasi RTM (Rapat Tinjauan Manajemen)</label>
                                                                    <textarea name="rekomendasi_rtm" id="modal_rekomendasi_rtm" class="lk2-textarea" rows="3" placeholder="Rekomendasi kebijakan strategis level pimpinan..."></textarea>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="modal-footer bg-white border-top">
                                                <button type="button" class="btn btn-outline-secondary btn-sm px-3" data-bs-dismiss="modal">Batal</button>
                                                <button type="button" class="btn btn-primary btn-sm px-4" onclick="saveModalLK2()">
                                                    <i class="fas fa-save me-1"></i> Simpan Detail
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <!-- ============================================================
                                 KESIMPULAN AUDIT
                                 ============================================================ -->
                            <hr class="section-divider">
                            <h5 class="mb-3 text-dark border-bottom pb-2">
                                <i class="fas fa-clipboard-list me-2"></i> Kesimpulan Audit &amp; Catatan Auditor
                            </h5>
                            <div class="kesimpulan-card">
                                <form id="form_kesimpulan" onsubmit="return false;">
                                    <input type="hidden" name="kodeaudit" value="<?= htmlspecialchars($ka) ?>">
                                    <div class="row g-4">
                                        <div class="col-md-6">
                                            <label class="lk2-section-label"><i class="fas fa-pen me-1"></i> Catatan Umum Auditor</label>
                                            <label class="lk2-form-label">Narasi kondisi unit secara keseluruhan — kemajuan RTL, kondisi risiko, hal yang perlu perhatian LPM</label>
                                            <textarea name="catatan_umum" class="lk2-textarea" rows="6" placeholder="Tuliskan catatan umum auditor tentang unit yang diaudit..."><?= htmlspecialchars($lk2Kes['catatan_umum'] ?? '') ?></textarea>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="lk2-section-label"><i class="fas fa-bullhorn me-1"></i> Rekomendasi Peningkatan (untuk RTM)</label>
                                            <label class="lk2-form-label">Rekomendasi strategis yang butuh keputusan pimpinan — kebijakan, anggaran, SDM, infrastruktur</label>
                                            <textarea name="rekomendasi_strategis" class="lk2-textarea" rows="6" placeholder="Rekomendasi yang perlu dibawa ke Rapat Tinjauan Manajemen..."><?= htmlspecialchars($lk2Kes['rekomendasi_strategis'] ?? '') ?></textarea>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center gap-3 mt-3 pt-3 border-top">
                                        <button type="button" class="save-btn" style="background:#7c3aed;" onclick="simpanKesimpulan()">
                                            <i class="fas fa-save"></i> Simpan Kesimpulan Audit
                                        </button>
                                        <span class="save-status" id="status_kesimpulan" style="display:none;"></span>
                                    </div>
                                </form>
                            </div>

                        <?php endif; ?>

                    </div>
                </div>
            </div>
        </section>
    </div>
</div>

<?php 
include 'foot.php';
?>

<script>
// ============================================================
// Data map of current LK2 data for JavaScript modal access
// ============================================================
const lk2DataMap = <?php echo json_encode($lk2Map, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
const risksData = <?php echo json_encode($risks, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;

// ============================================================
// Modal Actions
// ============================================================
let editModalInstance = null;

function openEditModal(noRisiko) {
    const r = risksData.find(item => item.no_risiko === noRisiko);
    const d = lk2DataMap[noRisiko] || {};

    document.getElementById('modal_no_risiko').value = noRisiko;
    document.getElementById('modal_title_no_risiko').textContent = noRisiko;
    document.getElementById('modal_pernyataan_risiko').textContent = r ? r.pernyataan_risiko : '';
    document.getElementById('modal_indikator_target').innerHTML = r ? ('<strong>Indikator &amp; Target:</strong> ' + (r.indikator_target || '—')) : '';

    // Set values
    document.getElementById('modal_plor_p').value = d.plor_p || '';
    document.getElementById('modal_plor_l').value = d.plor_l || '';
    document.getElementById('modal_plor_o').value = d.plor_o || '';
    document.getElementById('modal_plor_r').value = d.plor_r || '';

    document.getElementById('modal_tipe_temuan').value = d.tipe_temuan || '';
    document.getElementById('modal_akar_penyebab').value = d.akar_penyebab || '';
    document.getElementById('modal_risiko_potensial').value = d.risiko_potensial || '';
    document.getElementById('modal_pengendalian_efektivitas').value = d.pengendalian_efektivitas || '';

    document.getElementById('modal_faktor_pendukung').value = d.faktor_pendukung || '';
    document.getElementById('modal_peluang_peningkatan').value = d.peluang_peningkatan || '';
    document.getElementById('modal_potensi_replikasi').value = d.potensi_replikasi || '';

    document.getElementById('modal_dampak').value = d.dampak || 1;
    document.getElementById('modal_peluang').value = d.peluang || 1;
    document.getElementById('modal_level').value = d.level || '';
    document.getElementById('modal_tren').value = d.tren || 'N/A';

    hitungSkorModal();
    toggleTipeTemuan(d.tipe_temuan || '');

    document.getElementById('modal_rekomendasi_ptk').value = d.rekomendasi_ptk || '';
    document.getElementById('modal_rekomendasi_rtm').value = d.rekomendasi_rtm || '';

    // Show first tab
    const tabEl = document.getElementById('tab-plor-btn');
    const firstTab = new bootstrap.Tab(tabEl);
    firstTab.show();

    // Show modal
    const modalEl = document.getElementById('modalEditLK2');
    if (!editModalInstance) {
        editModalInstance = new bootstrap.Modal(modalEl);
    }
    editModalInstance.show();
}

function toggleTipeTemuan(tipe) {
    const ktsFields = document.getElementById('sec_kts_fields');
    const positifFields = document.getElementById('sec_positif_fields');
    const tabResidualNav = document.getElementById('tab-residual-nav');
    const tabRekomendasiNav = document.getElementById('tab-rekomendasi-nav');
    const residualInputs = document.getElementById('sec_residual_inputs');
    const residualDisclaimer = document.getElementById('sec_residual_disclaimer');
    const rekomendasiInputs = document.getElementById('sec_rekomendasi_inputs');
    const rekomendasiDisclaimer = document.getElementById('sec_rekomendasi_disclaimer');

    const lblPlorP = document.getElementById('lbl_plor_p');
    const lblDescP = document.getElementById('lbl_desc_p');

    if(tipe === 'Positif') {
        ktsFields.style.display = 'none';
        positifFields.style.display = 'block';
        
        // Hide inputs but show disclaimer
        residualInputs.style.display = 'none';
        residualDisclaimer.style.display = 'block';
        rekomendasiInputs.style.display = 'none';
        rekomendasiDisclaimer.style.display = 'block';

        // Adjust PLOR labels for Positif
        if(lblPlorP) lblPlorP.className = 'plor-key positif';
        if(lblDescP) lblDescP.textContent = 'Pernyataan / kondisi positif yang ditemukan';
    } else {
        ktsFields.style.display = 'block';
        positifFields.style.display = 'none';

        // Show inputs, hide disclaimer
        residualInputs.style.display = 'block';
        residualDisclaimer.style.display = 'none';
        rekomendasiInputs.style.display = 'block';
        rekomendasiDisclaimer.style.display = 'none';

        // Adjust PLOR labels for non-Positif
        if(lblPlorP) lblPlorP.className = 'plor-key';
        if(lblDescP) lblDescP.textContent = 'Problem / Permasalahan yang ditemukan';
    }
}

function hitungSkorModal() {
    const d = parseInt(document.getElementById('modal_dampak').value) || 1;
    const p = parseInt(document.getElementById('modal_peluang').value) || 1;
    const s = d * p;
    document.getElementById('modal_skor_display').textContent = s;
    document.getElementById('modal_skor').value = s;

    // Strict automated level determination (MERAH >= 10, KUNING >= 5, else HIJAU)
    let level = 'HIJAU';
    if(s >= 10) level = 'MERAH';
    else if(s >= 5) level = 'KUNING';

    document.getElementById('modal_level').value = level;
    
    const displayEl = document.getElementById('modal_level_display');
    if(displayEl) {
        displayEl.value = level + ' (' + (level === 'MERAH' ? 'Tinggi' : (level === 'KUNING' ? 'Sedang' : 'Rendah')) + ')';
        displayEl.style.backgroundColor = level === 'MERAH' ? '#fee2e2' : (level === 'KUNING' ? '#fef9c3' : '#dcfce7');
        displayEl.style.color = level === 'MERAH' ? '#991b1b' : (level === 'KUNING' ? '#854d0e' : '#166534');
    }
    
    updateModalSkorClass(level);
}

function updateModalSkorClass(level) {
    const skorEl = document.getElementById('modal_skor_display');
    skorEl.className = 'skor-display';
    if(level === 'MERAH') skorEl.classList.add('merah');
    else if(level === 'ORANYE') skorEl.classList.add('oranye');
    else if(level === 'KUNING') skorEl.classList.add('kuning');
    else if(level === 'HIJAU') skorEl.classList.add('hijau');
}

function saveModalLK2() {
    const form = document.getElementById('form_modal_lk2');
    const noRisiko = document.getElementById('modal_no_risiko').value;
    const saveBtn = form.querySelector('.modal-footer .btn-primary');
    
    const originalText = saveBtn.innerHTML;
    saveBtn.disabled = true;
    saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';

    const data = new FormData(form);
    data.append('aksi', 'simpan_temuan');

    const isAuditor = window.location.pathname.includes('/auditor/');
    const saveUrl = isAuditor ? '../audit.lk2.save.php' : 'audit.lk2.save.php';

    fetch(saveUrl, {
        method: 'POST',
        body: data
    })
    .then(r => r.json())
    .then(res => {
        if(res.ok) {
            // Update local JavaScript data map
            if(!lk2DataMap[noRisiko]) lk2DataMap[noRisiko] = {};
            
            // Get form values and update lk2DataMap
            const fields = ['plor_p', 'plor_l', 'plor_o', 'plor_r', 'tipe_temuan', 'akar_penyebab', 'risiko_potensial', 'pengendalian_efektivitas', 'faktor_pendukung', 'peluang_peningkatan', 'potensi_replikasi', 'dampak', 'peluang', 'level', 'tren', 'rekomendasi_ptk', 'rekomendasi_rtm'];
            fields.forEach(f => {
                const el = document.getElementById('modal_' + f);
                if(el) lk2DataMap[noRisiko][f] = el.value;
            });
            lk2DataMap[noRisiko]['skor'] = res.skor;
            lk2DataMap[noRisiko]['level'] = res.level;

            // Update row in table
            updateTableRowStatus(noRisiko);

            // Hide modal
            if(editModalInstance) {
                editModalInstance.hide();
            }

            alert('Detail LK2 #' + noRisiko + ' berhasil disimpan.');
        } else {
            alert('Gagal menyimpan: ' + (res.msg || 'Terjadi kesalahan'));
        }
    })
    .catch(err => {
        console.error(err);
        alert('Koneksi gagal saat menyimpan detail.');
    })
    .finally(() => {
        saveBtn.disabled = false;
        saveBtn.innerHTML = originalText;
    });
}

function updateTableRowStatus(noRisiko) {
    const d = lk2DataMap[noRisiko] || {};
    const tr = document.getElementById('row_' + noRisiko);
    if(!tr) return;

    // 1. Kategori Temuan badge
    const badgeKategori = document.getElementById('badge-kategori-' + noRisiko);
    if(badgeKategori) {
        const tp = d.tipe_temuan || '';
        if(tp) {
            badgeKategori.textContent = tp;
            let tpClass = 'bg-secondary';
            if(tp === 'Positif') tpClass = 'bg-success';
            else if(tp.includes('Mayor')) tpClass = 'bg-danger';
            else if(tp.includes('Minor')) tpClass = 'bg-warning text-dark';
            else if(tp.includes('OB')) tpClass = 'bg-info text-dark';
            badgeKategori.className = 'badge ' + tpClass;
        } else {
            badgeKategori.textContent = 'Belum ditentukan';
            badgeKategori.className = 'badge bg-secondary';
        }
    }

    // Helper to render check/cross badge
    const setStatusBadge = (elId, isTerisi, isNa = false) => {
        const el = document.getElementById(elId);
        if(!el) return;
        if(isNa) {
            el.className = 'badge bg-light text-muted border';
            el.innerHTML = 'N/A';
        } else if(isTerisi) {
            el.className = 'badge bg-success';
            el.innerHTML = '<i class="fas fa-check-circle"></i>';
        } else {
            el.className = 'badge bg-secondary';
            el.innerHTML = '<i class="fas fa-times-circle"></i>';
        }
    };

    // 2. PLOR Status
    const hasPlor = d.plor_p && d.plor_l && d.plor_o && d.plor_r;
    setStatusBadge('status-plor-' + noRisiko, hasPlor);

    // 3. Analisis Akar & Potensial Status
    if(d.tipe_temuan === 'Positif') {
        const hasAkarPos = d.faktor_pendukung;
        setStatusBadge('status-akar-' + noRisiko, hasAkarPos);
    } else {
        const hasAkarKts = d.akar_penyebab && d.risiko_potensial;
        setStatusBadge('status-akar-' + noRisiko, hasAkarKts);
    }

    // 4. Risiko Residual Status
    if(d.tipe_temuan === 'Positif') {
        setStatusBadge('status-residual-' + noRisiko, false, true);
    } else {
        const hasResidual = d.dampak && d.peluang && d.level;
        if(hasResidual) {
            const el = document.getElementById('status-residual-' + noRisiko);
            if(el) {
                el.removeAttribute('class');
                el.className = 'badge';
                el.style.border = '1px solid';
                if(d.level === 'MERAH') {
                    el.style.backgroundColor = '#fee2e2'; el.style.color = '#991b1b'; el.style.borderColor = '#fca5a5';
                } else if(d.level === 'ORANYE') {
                    el.style.backgroundColor = '#ffedd5'; el.style.color = '#c2410c'; el.style.borderColor = '#fdba74';
                } else if(d.level === 'KUNING') {
                    el.style.backgroundColor = '#fef9c3'; el.style.color = '#854d0e'; el.style.borderColor = '#fde047';
                } else if(d.level === 'HIJAU') {
                    el.style.backgroundColor = '#dcfce7'; el.style.color = '#166534'; el.style.borderColor = '#86efac';
                } else {
                    el.style.backgroundColor = '#6b7280'; el.style.color = '#ffffff'; el.style.borderColor = '#9ca3af';
                }
                el.innerHTML = d.level;
            }
        } else {
            setStatusBadge('status-residual-' + noRisiko, false);
        }
    }

    // 5. Rekomendasi Status
    if(d.tipe_temuan === 'Positif') {
        setStatusBadge('status-rekomendasi-' + noRisiko, false, true);
    } else {
        const hasRekom = d.rekomendasi_ptk || d.rekomendasi_rtm;
        setStatusBadge('status-rekomendasi-' + noRisiko, hasRekom);
    }
}

// ============================================================
// Simpan header audit (audit_persiapan)
// ============================================================
function simpanHeader() {
    const form = document.getElementById('form_header');
    const btn = form.querySelector('.save-btn');
    const statusEl = document.getElementById('status_header');

    const data = new FormData(form);
    data.append('aksi', 'simpan_header');

    btn.classList.add('saving');
    btn.disabled = true;
    statusEl.style.display = 'none';

    fetch('../audit.lk2.save.php', {
        method: 'POST',
        body: data
    })
    .then(r => r.json())
    .then(res => {
        statusEl.style.display = 'inline-flex';
        if(res.ok) {
            statusEl.className = 'save-status ok';
            statusEl.innerHTML = '<i class="fas fa-check-circle"></i> Tersimpan';
            setTimeout(() => { statusEl.style.display = 'none'; }, 3000);
        } else {
            statusEl.className = 'save-status err';
            statusEl.innerHTML = '<i class="fas fa-times-circle"></i> ' + (res.msg || 'Error');
        }
    })
    .catch(() => {
        statusEl.style.display = 'inline-flex';
        statusEl.className = 'save-status err';
        statusEl.innerHTML = '<i class="fas fa-times-circle"></i> Koneksi gagal';
    })
    .finally(() => {
        btn.classList.remove('saving');
        btn.disabled = false;
    });
}

// ============================================================
// Simpan kesimpulan audit
// ============================================================
function simpanKesimpulan() {
    const form = document.getElementById('form_kesimpulan');
    const btn = form.querySelector('.save-btn');
    const statusEl = document.getElementById('status_kesimpulan');

    const data = new FormData(form);
    data.append('aksi', 'simpan_kesimpulan');

    btn.classList.add('saving');
    btn.disabled = true;
    statusEl.style.display = 'none';

    fetch('../audit.lk2.save.php', {
        method: 'POST',
        body: data
    })
    .then(r => r.json())
    .then(res => {
        statusEl.style.display = 'inline-flex';
        if(res.ok) {
            statusEl.className = 'save-status ok';
            statusEl.innerHTML = '<i class="fas fa-check-circle"></i> Tersimpan';
            setTimeout(() => { statusEl.style.display = 'none'; }, 3000);
        } else {
            statusEl.className = 'save-status err';
            statusEl.innerHTML = '<i class="fas fa-times-circle"></i> ' + (res.msg || 'Error');
        }
    })
    .catch(() => {
        statusEl.style.display = 'inline-flex';
        statusEl.className = 'save-status err';
        statusEl.innerHTML = '<i class="fas fa-times-circle"></i> Koneksi gagal';
    })
    .finally(() => {
        btn.classList.remove('saving');
        btn.disabled = false;
    });
}

// ============================================================
// Filter Berdasarkan Standar
// ============================================================
document.getElementById('filter-standar-rekap').addEventListener('change', function() {
    const selectedStd = this.value;
    const rows = document.querySelectorAll('.risk-row');
    rows.forEach(row => {
        const rowStd = row.getAttribute('data-standard');
        if(!selectedStd || rowStd === selectedStd) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
});
</script>

