<?php
include 'nav.php';
include 'audit.validasi.php';

// Auto-create required tables if they don't exist
mysqli_query($kon, "CREATE TABLE IF NOT EXISTS `audit_persiapan` (
  `id` int NOT NULL AUTO_INCREMENT,
  `kodeaudit` varchar(20) NOT NULL,
  `ketua_tim` varchar(100) DEFAULT NULL,
  `anggota_tim` varchar(255) DEFAULT NULL,
  `tanggal_audit_dok` date DEFAULT NULL,
  `unit_diaudit` varchar(100) DEFAULT NULL,
  `auditee_kaprodi` varchar(100) DEFAULT NULL,
  `no_dokumen` varchar(100) DEFAULT NULL,
  `standar_diaudit` varchar(255) DEFAULT NULL,
  `ref_form_pra_ami` varchar(100) DEFAULT NULL,
  `periode_ami` varchar(100) DEFAULT NULL,
  `entri` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_kodeaudit` (`kodeaudit`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

mysqli_query($kon, "CREATE TABLE IF NOT EXISTS `audit_persiapan_risiko` (
  `id` int NOT NULL AUTO_INCREMENT,
  `kodeaudit` varchar(20) NOT NULL,
  `no_risiko` varchar(10) NOT NULL,
  `pernyataan_risiko` text,
  `indikator_target` text,
  `dampak` int DEFAULT NULL,
  `probabilitas` int DEFAULT NULL,
  `skor` int DEFAULT NULL,
  `level` varchar(20) DEFAULT NULL,
  `pengendalian_diklaim` text,
  `entri` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

mysqli_query($kon, "CREATE TABLE IF NOT EXISTS `audit_persiapan_hipotesis` (
  `id` int NOT NULL AUTO_INCREMENT,
  `kodeaudit` varchar(20) NOT NULL,
  `no_risiko` varchar(10) NOT NULL,
  `hipotesis` text NOT NULL,
  `prediksi_hasil` varchar(50) NOT NULL,
  `entri` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

mysqli_query($kon, "CREATE TABLE IF NOT EXISTS `audit_persiapan_rencana` (
  `id` int NOT NULL AUTO_INCREMENT,
  `kodeaudit` varchar(20) NOT NULL,
  `sesi` varchar(100) NOT NULL,
  `fokus_pertanyaan` text NOT NULL,
  `dokumen_disiapkan` text NOT NULL,
  `estimasi_waktu` varchar(50) NOT NULL,
  `entri` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

mysqli_query($kon, "CREATE TABLE IF NOT EXISTS `audit_persiapan_telaah` (
  `id` int NOT NULL AUTO_INCREMENT,
  `kodeaudit` varchar(20) NOT NULL,
  `no_risiko` varchar(10) NOT NULL,
  `standar_butir` varchar(255) DEFAULT NULL,
  `dokumen_ditelaah` varchar(255) NOT NULL,
  `temuan` text NOT NULL,
  `kesesuaian` varchar(50) NOT NULL,
  `pertanyaan_lapangan` text NOT NULL,
  `entri` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// Auto-create required columns in table audit if not present
$auditCols = ['forma', 'catatanpersiapan', 'triangulasi', 'lk2', 'ptka'];
foreach ($auditCols as $col) {
    $chkCol = mysqli_query($kon, "SHOW COLUMNS FROM `audit` LIKE '$col'");
    if ($chkCol && mysqli_num_rows($chkCol) === 0) {
        mysqli_query($kon, "ALTER TABLE `audit` ADD COLUMN `$col` VARCHAR(255) DEFAULT NULL");
    }
}

$ka = isset($_GET['ka']) ? mysqli_real_escape_string($kon, trim((string)$_GET['ka'])) : '';
$et = isset($_GET['et']) ? mysqli_real_escape_string($kon, trim((string)$_GET['et'])) : '';

if (empty($ka)) {
    ?>
    <div id="main">
        <div class="page-content p-4">
            <div class="alert alert-warning shadow-sm">
                <h5 class="fw-bold"><i class="fas fa-exclamation-circle me-2"></i> Parameter Kode Audit Tidak Ditemukan</h5>
                <p class="mb-0">Silakan pilih audit dari menu penugasan untuk membuka Catatan Persiapan Auditor.</p>
                <hr>
                <a href="audit" class="btn btn-primary btn-sm"><i class="fas fa-arrow-left me-1"></i> Kembali ke Daftar Audit</a>
            </div>
        </div>
    </div>
    <?php
    include 'foot.php';
    exit;
}

// Fetch Audit details
$qGda = mysqli_query($kon, "
    SELECT a.*, unit.nama as nama_unit, unit.kepala as kepala_unit
    FROM audit a 
    LEFT JOIN unit ON a.unit = unit.id 
    WHERE a.kodeaudit = '$ka'
");
$gda = ($qGda && mysqli_num_rows($qGda) > 0) ? mysqli_fetch_array($qGda) : [];

if (empty($gda)) {
    ?>
    <div id="main">
        <div class="page-content p-4">
            <div class="alert alert-danger shadow-sm">
                <h5 class="fw-bold"><i class="fas fa-times-circle me-2"></i> Data Audit Tidak Ditemukan</h5>
                <p class="mb-0">Data audit dengan kode <strong><?= htmlspecialchars($ka); ?></strong> tidak ditemukan dalam sistem.</p>
                <hr>
                <a href="audit" class="btn btn-primary btn-sm"><i class="fas fa-arrow-left me-1"></i> Kembali ke Daftar Audit</a>
            </div>
        </div>
    </div>
    <?php
    include 'foot.php';
    exit;
}

// Fetch Ketua & Anggota Tim names
$qKetua = mysqli_query($kon, "SELECT user FROM auditor WHERE kodeaudit='$ka' AND status='Ketua' LIMIT 1");
$idKetua = $qKetua && mysqli_num_rows($qKetua) > 0 ? mysqli_fetch_assoc($qKetua)['user'] : '';

$anggotaIds = [];
$qAnggota = mysqli_query($kon, "SELECT user FROM auditor WHERE kodeaudit='$ka' AND status LIKE '%Anggota%'");
if($qAnggota) {
    while($row = mysqli_fetch_assoc($qAnggota)) {
        $anggotaIds[] = $row['user'];
    }
}

// Fetch names from local users
$pegawaiMap = [];
$localUsers = mysqli_query($kon, "SELECT id, nama FROM user");
if($localUsers) {
    while($user = mysqli_fetch_assoc($localUsers)) {
        $id = (string)$user['id'];
        $pegawaiMap[$id] = $user['nama'];
    }
}

$namaKetua = $pegawaiMap[(string)$idKetua] ?? $idKetua;
$namaAnggotaList = [];
foreach($anggotaIds as $aid) {
    $namaAnggotaList[] = $pegawaiMap[(string)$aid] ?? $aid;
}
$namaAnggota = implode(', ', $namaAnggotaList);

// Auto-populate defaults if database has no header yet
$qHeader = mysqli_query($kon, "SELECT * FROM audit_persiapan WHERE kodeaudit='$ka'");
$headerData = $qHeader ? mysqli_fetch_array($qHeader) : null;
$noDokumen = $headerData['no_dokumen'] ?? ('CAD-AMI/' . date('Y') . '/' . ($gda['unit'] ?? '1'));
$tglAuditDok = $headerData['tanggal_audit_dok'] ?? date('Y-m-d');
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
$defaultPra = ($qDefaultPra && mysqli_num_rows($qDefaultPra) > 0) ? mysqli_fetch_assoc($qDefaultPra) : null;
if ($defaultPra) {
    $defaultRef = 'Form-PRA/' . ($defaultPra['tahun'] ?? date('Y')) . '/' . ($defaultPra['unit'] ?? ($gda['unit'] ?? '1')) . '/' . sprintf('%03d', (int)($defaultPra['id'] ?? 0));
} else {
    $defaultRef = 'Form-PRA/' . date('Y') . '/' . ($gda['unit'] ?? '1') . '/000';
}
$refFormPraAmi = $headerData['ref_form_pra_ami'] ?? $defaultRef;
$periodeAmi = $headerData['periode_ami'] ?? 'Sem. Genap / TA ' . date('Y', strtotime('-1 year')) . '–' . date('Y');

// Determine selected praaudit ID
$selectedPraId = 0;
if (!empty($refFormPraAmi)) {
    $parts = explode('/', $refFormPraAmi);
    $selectedPraId = (int)end($parts);
}
if ($selectedPraId <= 0 && $defaultPra) {
    $selectedPraId = (int)$defaultPra['id'];
}

// Auto-populate & sync risks into audit_persiapan_risiko from latest LED
if($selectedPraId > 0) {
    // Get existing risks for this audit
    $existingRisks = [];
    $qExisting = mysqli_query($kon, "SELECT id, no_risiko, pernyataan_risiko, dampak, probabilitas, skor, level, pengendalian_diklaim FROM audit_persiapan_risiko WHERE kodeaudit='$ka'");
    if ($qExisting) {
        while($ex = mysqli_fetch_assoc($qExisting)) {
            $cleanPrefix = trim(preg_replace('/\(Risiko:\s*.*?\)\s*$/is', '', $ex['pernyataan_risiko']));
            $ex['clean_prefix'] = $cleanPrefix;
            $existingRisks[] = $ex;
        }
    }
    
    // Get current max index
    $qMaxIdx = mysqli_query($kon, "SELECT COUNT(*) as cnt FROM audit_persiapan_risiko WHERE kodeaudit='$ka'");
    $maxRow = $qMaxIdx ? mysqli_fetch_assoc($qMaxIdx) : null;
    $idx = $maxRow ? (int)$maxRow['cnt'] + 1 : 1;
    
    // Pull ALL pertanyaan from the packet with latest LED data via LEFT JOIN
    $qRisks = mysqli_query($kon, "
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
    if($qRisks) {
        while($r = mysqli_fetch_assoc($qRisks)) {
            $isiRisiko = !empty($r['isi_risiko']) ? $r['isi_risiko'] : 'Belum diisi';
            $pernyataan = mysqli_real_escape_string($kon, $r['namastandar'].' - '. $r['pertanyaan'] . ' (Risiko: '.$isiRisiko.')');
            $pengendalian = mysqli_real_escape_string($kon, $r['usulan_mitigasi'] ?? '');
            
            // If Kondisi Awal (isi_risiko) & Pengendalian (usulan_mitigasi) not filled, leave D/P/Skor/Level empty
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
            
            if($skor >= 10) {
                $level = 'MERAH';
            } elseif($skor >= 5) {
                $level = 'KUNING';
            } elseif($skor > 0) {
                $level = 'HIJAU';
            } else {
                $level = '';
            }
            
            // Check if this pertanyaan already exists in the risiko table
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
                // Update D, P, Skor, Level, Pernyataan Risiko, and Pengendalian from latest LED
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
                $noRisiko = 'R-'.$idx;
                $indikator = mysqli_real_escape_string($kon, 'Target/Indikator');
                mysqli_query($kon, "
                    INSERT INTO audit_persiapan_risiko 
                    (kodeaudit, no_risiko, pernyataan_risiko, indikator_target, dampak, probabilitas, skor, level, pengendalian_diklaim) 
                    VALUES 
                    ('$ka', '$noRisiko', '$pernyataan', '$indikator', '$dampak', '$probabilitas', '$skor', '$level', '$pengendalian')
                ");
                $idx++;
            }
        }
    }
}

if (isset($_GET['sync_led'])) {
    $message = '<div class="alert alert-success alert-dismissible fade show"><i class="fas fa-check-circle me-2"></i>Data risiko, D, P, Skor, Level, dan Pengendalian berhasil disinkronkan ulang dengan hasil perbaikan LED terbaru!<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
}


// Fetch risks from audit_persiapan_risiko
$risks = [];
$qSavedRisks = mysqli_query($kon, "SELECT * FROM audit_persiapan_risiko WHERE kodeaudit='$ka' ORDER BY id ASC");
if($qSavedRisks) {
    while($row = mysqli_fetch_assoc($qSavedRisks)) {
        $risks[] = $row;
    }
}

// Handle Form Submission
$message = '';
if(isset($_POST['simpan'])) {
    $newRef = $_POST['ref_form_pra_ami'] ?? '';
    $oldRef = $headerData['ref_form_pra_ami'] ?? '';
    if ($oldRef !== $newRef) {
        mysqli_query($kon, "DELETE FROM audit_persiapan_risiko WHERE kodeaudit='$ka'");
        mysqli_query($kon, "DELETE FROM audit_persiapan_telaah WHERE kodeaudit='$ka'");
        mysqli_query($kon, "DELETE FROM audit_persiapan_hipotesis WHERE kodeaudit='$ka'");
        
        $newSelectedPraId = 0;
        if (!empty($newRef)) {
            $parts = explode('/', $newRef);
            $newSelectedPraId = (int)end($parts);
        }
        if ($newSelectedPraId > 0) {
            $qRisks = mysqli_query($kon, "
                 SELECT p.id as idpertanyaan, p.pertanyaan, k.namastandar, l.isi_risiko, l.dampak, l.probabilitas, l.risiko, l.usulan_mitigasi
                 FROM pertanyaan p
                 JOIN kriteria k ON p.kriteria = k.id
                 LEFT JOIN (
                     SELECT l1.* 
                     FROM led l1
                     JOIN (
                         SELECT MAX(id) as max_id 
                         FROM led 
                         WHERE idpraaudit = '$newSelectedPraId'
                         GROUP BY idpertanyaan
                     ) l2 ON l1.id = l2.max_id
                 ) l ON l.idpertanyaan = p.id
                 WHERE p.idpaket = (SELECT paket FROM praaudit WHERE id = '$newSelectedPraId')
                 ORDER BY p.noindikator ASC
             ");
            if($qRisks) {
                $idx = 1;
                while($r = mysqli_fetch_assoc($qRisks)) {
                    $noRisiko = 'R-'.$idx;
                    $isiRisiko = !empty($r['isi_risiko']) ? $r['isi_risiko'] : 'Belum diisi';
                    $pernyataan = mysqli_real_escape_string($kon, $r['namastandar'].' - '. $r['pertanyaan'] . ' (Risiko: '.$isiRisiko.')');
                    $indikator = mysqli_real_escape_string($kon, 'Target/Indikator');
                    
                    // If Kondisi Awal (isi_risiko) & Pengendalian (usulan_mitigasi) not filled, leave D/P/Skor/Level empty
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
                    
                    if($skor >= 10) {
                        $level = 'MERAH';
                    } elseif($skor >= 5) {
                        $level = 'KUNING';
                    } elseif($skor > 0) {
                        $level = 'HIJAU';
                    } else {
                        $level = '';
                    }
                    
                    $pengendalian = mysqli_real_escape_string($kon, $r['usulan_mitigasi'] ?? '');
                    
                    mysqli_query($kon, "
                        INSERT INTO audit_persiapan_risiko 
                        (kodeaudit, no_risiko, pernyataan_risiko, indikator_target, dampak, probabilitas, skor, level, pengendalian_diklaim) 
                        VALUES 
                        ('$ka', '$noRisiko', '$pernyataan', '$indikator', '$dampak', '$probabilitas', '$skor', '$level', '$pengendalian')
                    ");
                    $idx++;
                }
            }
        }
    }
    
    $noDokumenPost = mysqli_real_escape_string($kon, (string)($_POST['no_dokumen'] ?? ''));
    $tglAuditDokPost = mysqli_real_escape_string($kon, (string)($_POST['tanggal_audit_dok'] ?? date('Y-m-d')));
    $ketuaTimPost = mysqli_real_escape_string($kon, (string)($_POST['ketua_tim'] ?? ''));
    $anggotaTimPost = mysqli_real_escape_string($kon, (string)($_POST['anggota_tim'] ?? ''));
    $unitDiauditPost = mysqli_real_escape_string($kon, (string)($_POST['unit_diaudit'] ?? ''));
    $auditeeKaprodiPost = mysqli_real_escape_string($kon, (string)($_POST['auditee_kaprodi'] ?? ''));
    $standarDiauditPost = mysqli_real_escape_string($kon, (string)($_POST['standar_diaudit'] ?? ''));
    $refFormPraAmiPost = mysqli_real_escape_string($kon, (string)($_POST['ref_form_pra_ami'] ?? ''));
    $periodeAmiPost = mysqli_real_escape_string($kon, (string)($_POST['periode_ami'] ?? ''));

    // 1. Save/Update audit_persiapan header
    $checkHeader = mysqli_query($kon, "SELECT id FROM audit_persiapan WHERE kodeaudit='$ka'");
    if($checkHeader && mysqli_num_rows($checkHeader) > 0) {
        $saveHeader = mysqli_query($kon, "
            UPDATE audit_persiapan SET 
                ketua_tim='$ketuaTimPost',
                anggota_tim='$anggotaTimPost',
                tanggal_audit_dok='$tglAuditDokPost',
                unit_diaudit='$unitDiauditPost',
                auditee_kaprodi='$auditeeKaprodiPost',
                no_dokumen='$noDokumenPost',
                standar_diaudit='$standarDiauditPost',
                ref_form_pra_ami='$refFormPraAmiPost',
                periode_ami='$periodeAmiPost'
            WHERE kodeaudit='$ka'
        ");
    } else {
        $saveHeader = mysqli_query($kon, "
            INSERT INTO audit_persiapan 
            (kodeaudit, ketua_tim, anggota_tim, tanggal_audit_dok, unit_diaudit, auditee_kaprodi, no_dokumen, standar_diaudit, ref_form_pra_ami, periode_ami) 
            VALUES 
            ('$ka', '$ketuaTimPost', '$anggotaTimPost', '$tglAuditDokPost', '$unitDiauditPost', '$auditeeKaprodiPost', '$noDokumenPost', '$standarDiauditPost', '$refFormPraAmiPost', '$periodeAmiPost')
        ");
    }

    if($saveHeader) {
        // Update risks modifications if any
        if(!empty($_POST['risiko_pernyataan']) && is_array($_POST['risiko_pernyataan'])) {
            foreach($_POST['risiko_pernyataan'] as $noR => $pernyataanText) {
                $noRisiko = mysqli_real_escape_string($kon, (string)$noR);
                $pernyataanVal = mysqli_real_escape_string($kon, (string)$pernyataanText);
                $indikatorVal = mysqli_real_escape_string($kon, (string)($_POST['risiko_indikator'][$noR] ?? ''));
                $dampakVal = (int)($_POST['risiko_dampak'][$noR] ?? 0);
                $probVal = (int)($_POST['risiko_probabilitas'][$noR] ?? 0);
                $skorVal = $dampakVal * $probVal;
                
                if($skorVal >= 10) {
                    $lvlVal = 'MERAH';
                } elseif($skorVal >= 5) {
                    $lvlVal = 'KUNING';
                } elseif($skorVal > 0) {
                    $lvlVal = 'HIJAU';
                } else {
                    $lvlVal = '';
                }
                
                $pengendalianVal = mysqli_real_escape_string($kon, (string)($_POST['risiko_pengendalian'][$noR] ?? ''));
                
                mysqli_query($kon, "
                    UPDATE audit_persiapan_risiko SET 
                        pernyataan_risiko='$pernyataanVal',
                        indikator_target='$indikatorVal',
                        dampak='$dampakVal',
                        probabilitas='$probVal',
                        skor='$skorVal',
                        level='$lvlVal',
                        pengendalian_diklaim='$pengendalianVal'
                    WHERE kodeaudit='$ka' AND no_risiko='$noRisiko'
                ");
            }
        }

        // Bagian 2 & 3 are now handled separately via AJAX in their own modal.

        // 4. Save Bagian 4: Rencana Audit Lapangan
        mysqli_query($kon, "DELETE FROM audit_persiapan_rencana WHERE kodeaudit='$ka'");
        if(!empty($_POST['rencana']) && is_array($_POST['rencana'])) {
            foreach($_POST['rencana'] as $rowR) {
                $sesi = mysqli_real_escape_string($kon, (string)($rowR['sesi'] ?? ''));
                $fokus = mysqli_real_escape_string($kon, (string)($rowR['fokus_pertanyaan'] ?? ''));
                $dok_disiapkan = mysqli_real_escape_string($kon, (string)($rowR['dokumen_disiapkan'] ?? ''));
                $waktu = mysqli_real_escape_string($kon, (string)($rowR['estimasi_waktu'] ?? ''));
                
                if(!empty($sesi)) {
                    mysqli_query($kon, "
                        INSERT INTO audit_persiapan_rencana 
                        (kodeaudit, sesi, fokus_pertanyaan, dokumen_disiapkan, estimasi_waktu) 
                        VALUES 
                        ('$ka', '$sesi', '$fokus', '$dok_disiapkan', '$waktu')
                    ");
                }
            }
        }

        // Update main audit indicators safely
        $chkCol = mysqli_query($kon, "SHOW COLUMNS FROM `audit` LIKE 'catatanpersiapan'");
        if($chkCol && mysqli_num_rows($chkCol) > 0) {
            mysqli_query($kon, "UPDATE audit SET catatanpersiapan='TERISI' WHERE kodeaudit='$ka'");
        }

        $message = '<div class="alert alert-success"><i class="fas fa-check-circle me-2"></i>Catatan Persiapan Auditor berhasil disimpan!</div>';
        
        // Refresh values
        $qHeaderRefresh = mysqli_query($kon, "SELECT * FROM audit_persiapan WHERE kodeaudit='$ka'");
        $headerData = $qHeaderRefresh ? mysqli_fetch_array($qHeaderRefresh) : null;
        $noDokumen = $headerData['no_dokumen'] ?? $noDokumen;
        $tglAuditDok = $headerData['tanggal_audit_dok'] ?? $tglAuditDok;
        $ketuaTim = $headerData['ketua_tim'] ?? $ketuaTim;
        $anggotaTim = $headerData['anggota_tim'] ?? $anggotaTim;
        $unitDiaudit = $headerData['unit_diaudit'] ?? $unitDiaudit;
        $auditeeKaprodi = $headerData['auditee_kaprodi'] ?? $auditeeKaprodi;
        $standarDiaudit = $headerData['standar_diaudit'] ?? $standarDiaudit;
        $refFormPraAmi = $headerData['ref_form_pra_ami'] ?? $refFormPraAmi;
        $periodeAmi = $headerData['periode_ami'] ?? $periodeAmi;

        $risks = [];
        $qSavedRisks = mysqli_query($kon, "SELECT * FROM audit_persiapan_risiko WHERE kodeaudit='$ka' ORDER BY id ASC");
        if($qSavedRisks) {
            while($row = mysqli_fetch_assoc($qSavedRisks)) {
                $risks[] = $row;
            }
        }
    } else {
        $message = '<div class="alert alert-danger"><i class="fas fa-exclamation-circle me-2"></i>Gagal menyimpan data: '.mysqli_error($kon).'</div>';
    }
}

// Fetch saved Bagian 2 data
$savedTelaah = [];
$qSavedT = mysqli_query($kon, "SELECT * FROM audit_persiapan_telaah WHERE kodeaudit='$ka'");
if($qSavedT) {
    while($row = mysqli_fetch_assoc($qSavedT)) {
        $savedTelaah[$row['no_risiko']][] = $row;
    }
}

// Fetch saved Bagian 3 data
$savedHipotesis = [];
$qSavedH = mysqli_query($kon, "SELECT * FROM audit_persiapan_hipotesis WHERE kodeaudit='$ka'");
if($qSavedH) {
    while($row = mysqli_fetch_assoc($qSavedH)) {
        $savedHipotesis[$row['no_risiko']] = $row;
    }
}

// Fetch saved Bagian 4 data
$savedRencana = [];
$qSavedR = mysqli_query($kon, "SELECT * FROM audit_persiapan_rencana WHERE kodeaudit='$ka' ORDER BY id ASC");
if($qSavedR) {
    while($row = mysqli_fetch_assoc($qSavedR)) {
        $savedRencana[] = $row;
    }
}
?>
<style>
    .section-title {
        font-weight: 800;
        color: #0f766e;
        border-bottom: 2px solid #e2e8f0;
        padding-bottom: 0.5rem;
        margin-bottom: 1.25rem;
    }
    .risk-badge {
        font-size: 0.75rem;
        font-weight: 900;
        padding: 0.3rem 0.75rem;
        border-radius: 2rem;
        color: #ffffff;
    }
    .risk-red { background-color: #ef4444; }
    .risk-yellow { background-color: #f59e0b; color: #1e293b; }
    .risk-green { background-color: #10b981; }
    
    .table-risk-info th {
        background-color: #f8fafc;
        color: #475569;
        font-size: 0.78rem;
        text-transform: uppercase;
    }
    .btn-add-row {
        background-color: #f0fdfa;
        color: #0f766e;
        border: 1px dashed #0f766e;
        font-weight: 700;
        transition: all 0.2s ease;
    }
    .btn-add-row:hover {
        background-color: #0f766e;
        color: #ffffff;
    }
    .btn-delete-row {
        color: #ef4444;
        cursor: pointer;
        transition: color 0.15s ease;
    }
    .btn-delete-row:hover {
        color: #b91c1c;
    }

    @media print {
        #sidebar, header, .btn, .audit-toolbar, .no-print, .burger-btn {
            display: none !important;
        }
        #main {
            margin: 0 !important;
            padding: 0 !important;
            background: #ffffff !important;
            width: 100% !important;
        }
        .card {
            border: 0 !important;
            box-shadow: none !important;
        }
        body {
            background-color: #ffffff !important;
            font-size: 10pt !important;
            color: #000000 !important;
        }
        .table {
            border-collapse: collapse !important;
            width: 100% !important;
        }
        .table th, .table td {
            border: 1px solid #000000 !important;
            padding: 4px !important;
        }
        .section-title {
            color: #000000 !important;
            border-bottom: 2px solid #000000 !important;
        }
        textarea, input, select {
            border: 0 !important;
            background: transparent !important;
            width: 100% !important;
            resize: none !important;
            outline: none !important;
        }
    }
</style>
<div id="main">
    <header class="d-lg-none no-print">
        <a href="#" class="burger-btn d-block p-2">
            <i class="fas fa-bars"></i> Menu 
        </a>
    </header> 
    <div class="page-content">
        <section class="row">
            <div class="col-12">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center py-3 no-print">
                        <div>
                            <h4 class="mb-1 text-dark font-semibold">CAD-AMI - Catatan Persiapan Auditor</h4>
                            <p class="text-muted mb-0 small">Tinjauan dokumen (*Desk Evaluation*) sebelum audit lapangan berlangsung.</p>
                        </div>
                        <div class="d-flex gap-2">
                            <a class="btn btn-outline-secondary btn-sm" href="audit.progres?ka=<?php echo urlencode($ka);?>&et=<?php echo urlencode($et);?>"><i class="fas fa-caret-left me-1"></i> Kembali</a>
                            <button onclick="window.print()" class="btn btn-outline-primary btn-sm"><i class="fas fa-print me-1"></i> Cetak Dokumen</button>
                        </div>
                    </div>

                    <div class="card-body">
                        <?php echo $message; ?>
                        <form method="post" id="persiapanForm">
                            <!-- Official Header Document info for print -->
                            <div class="border rounded p-3 mb-4 bg-light text-dark">
                                <div class="row">
                                    <div class="col-md-3 mb-2">
                                        <label class="form-label font-bold text-dark mb-1">Ketua Tim Auditor</label>
                                        <input type="text" name="ketua_tim" class="form-control form-control-sm" value="<?php echo htmlspecialchars($ketuaTim);?>" required>
                                    </div>
                                    <div class="col-md-3 mb-2">
                                        <label class="form-label font-bold text-dark mb-1">Anggota Tim</label>
                                        <input type="text" name="anggota_tim" class="form-control form-control-sm" value="<?php echo htmlspecialchars($anggotaTim);?>">
                                    </div>
                                    <div class="col-md-3 mb-2">
                                        <label class="form-label font-bold text-dark mb-1">Tanggal Audit Dokumen</label>
                                        <input type="date" name="tanggal_audit_dok" class="form-control form-control-sm" value="<?php echo htmlspecialchars($tglAuditDok);?>" required>
                                    </div>
                                    <div class="col-md-3 mb-2">
                                        <label class="form-label font-bold text-dark mb-1">Unit yang Diaudit</label>
                                        <input type="text" name="unit_diaudit" class="form-control form-control-sm" value="<?php echo htmlspecialchars($unitDiaudit);?>" required>
                                    </div>
                                    <div class="col-md-3 mb-2">
                                        <label class="form-label font-bold text-dark mb-1">Auditee / Kaprodi</label>
                                        <input type="text" name="auditee_kaprodi" class="form-control form-control-sm" value="<?php echo htmlspecialchars($auditeeKaprodi);?>" required>
                                    </div>
                                    <div class="col-md-3 mb-2">
                                        <label class="form-label font-bold text-dark mb-1">No. Dokumen</label>
                                        <input type="text" name="no_dokumen" class="form-control form-control-sm" value="<?php echo htmlspecialchars($noDokumen);?>" required>
                                    </div>
                                    <div class="col-md-3 mb-2">
                                        <label class="form-label font-bold text-dark mb-1">Referensi Form Pra-AMI</label>
                                        <?php
                                        $qPraAudits = mysqli_query($kon, "
                                            SELECT pr.id, pr.tahun, pr.unit, pkt.namapaket, pr.paket
                                            FROM praaudit pr
                                            JOIN paketpertanyaan pkt ON pr.paket = pkt.id
                                            WHERE pr.unit = '" . (int)$gda['unit'] . "'
                                            ORDER BY pr.tahun DESC, pr.id DESC
                                        ");
                                        $praAuditOptions = [];
                                        if ($qPraAudits && mysqli_num_rows($qPraAudits) > 0) {
                                            while ($pr = mysqli_fetch_assoc($qPraAudits)) {
                                                $code = 'Form-PRA/' . $pr['tahun'] . '/' . $pr['unit'] . '/' . sprintf('%03d', $pr['id']);
                                                
                                                $qStands = mysqli_query($kon, "
                                                    SELECT DISTINCT k.namastandar 
                                                    FROM pertanyaan p
                                                    JOIN kriteria k ON p.kriteria = k.id
                                                    WHERE p.idpaket = '" . (int)$pr['paket'] . "' AND k.namastandar != ''
                                                ");
                                                $standsList = [];
                                                if ($qStands) {
                                                    while ($st = mysqli_fetch_assoc($qStands)) {
                                                        $standsList[] = $st['namastandar'];
                                                    }
                                                }
                                                $standsStr = implode('||', $standsList);
                                                
                                                $praAuditOptions[] = [
                                                    'code' => $code,
                                                    'standards' => $standsStr,
                                                    'label' => 'Form Pra-AMI ' . $pr['namapaket'] . ' (' . $pr['tahun'] . ')'
                                                ];
                                            }
                                        }
                                        ?>
                                        <select name="ref_form_pra_ami" id="ref_form_pra_ami" class="form-control form-control-sm" required>
                                            <option value="" data-standards="">Pilih Referensi Form Pra-AMI</option>
                                            <?php foreach ($praAuditOptions as $opt): ?>
                                                <?php $selected = ($refFormPraAmi === $opt['code']) ? 'selected' : ''; ?>
                                                <option value="<?php echo htmlspecialchars($opt['code']); ?>" data-standards="<?php echo htmlspecialchars($opt['standards']); ?>" <?php echo $selected; ?>>
                                                    <?php echo htmlspecialchars($opt['label']); ?> (<?php echo htmlspecialchars($opt['code']); ?>)
                                                </option>
                                            <?php endforeach; ?>
                                            <?php
                                            $hasSaved = false;
                                            foreach ($praAuditOptions as $opt) {
                                                if ($opt['code'] === $refFormPraAmi) {
                                                    $hasSaved = true;
                                                    break;
                                                }
                                            }
                                            if (!$hasSaved && !empty($refFormPraAmi)) {
                                                echo '<option value="' . htmlspecialchars($refFormPraAmi) . '" data-standards="" selected>' . htmlspecialchars($refFormPraAmi) . '</option>';
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="col-md-3 mb-2">
                                        <label class="form-label font-bold text-dark mb-1">Standar yang Diaudit</label>
                                        <select name="standar_diaudit" id="standar_diaudit" class="form-control form-control-sm" required>
                                            <option value="">Pilih Standar</option>
                                            <?php if (!empty($standarDiaudit)): ?>
                                                <option value="<?php echo htmlspecialchars($standarDiaudit); ?>" selected><?php echo htmlspecialchars($standarDiaudit); ?></option>
                                            <?php endif; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-3 mb-2">
                                        <label class="form-label font-bold text-dark mb-1">Periode AMI</label>
                                        <input type="text" name="periode_ami" class="form-control form-control-sm" value="<?php echo htmlspecialchars($periodeAmi);?>" required>
                                    </div>
                                </div>
                            </div>

                            <!-- BAGIAN 1: Rekap Risiko dari Form Pra-AMI -->
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h5 class="section-title mb-0">BAGIAN 1 — Rekap Risiko dari Form Pra-AMI</h5>
                                <div class="no-print">
                                    <a href="?ka=<?php echo urlencode($ka); ?>&et=<?php echo urlencode($et); ?>&sync_led=1" class="btn btn-outline-warning btn-sm" onclick="return confirm('Apakah Anda yakin ingin menyinkronkan ulang data risiko, D, P, Skor, Level, dan Pengendalian dari pengisian LED terbaru?')" title="Sinkronkan data D, P, Skor, Level, dan Pengendalian dari Form Pra-AMI (LED) terbaru">
                                        <i class="fas fa-sync-alt me-1"></i> Sinkronkan dari LED
                                    </a>
                                </div>
                            </div>
                            <?php 
                            // Extract unique standards from the selected praaudit's pertanyaan package
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
                             <div class="row mb-3 no-print">
                                 <div class="col-md-4">
                                     <label class="form-label font-bold small text-dark mb-1"><i class="fas fa-filter text-primary me-1"></i> Filter Berdasarkan Standar</label>
                                     <select id="filter-standar-rekap" class="form-control form-control-sm">
                                         <option value="">Semua Standar</option>
                                         <?php foreach ($uniqueStandards as $std): ?>
                                             <option value="<?php echo htmlspecialchars($std); ?>"><?php echo htmlspecialchars($std); ?></option>
                                         <?php endforeach; ?>
                                     </select>
                                 </div>
                             </div>

                             <?php if(empty($risks)): ?>
                                 <div class="alert alert-light border text-center mb-4 py-4">
                                     <i class="fas fa-info-circle text-muted fa-2x mb-2"></i>
                                     <p class="mb-0 text-muted">Tidak ditemukan risiko potensial (skor > 0) dari form Pra-AMI unit ini.</p>
                                 </div>
                             <?php else: ?>
                                 <div class="table-responsive mb-4">
                                     <table class="table table-bordered table-striped text-dark table-risk-info" id="catatanTable">
                                         <thead>
                                             <tr>
                                                 <th>No</th>
                                                 <th>Pernyataan Standar & Risiko Potensial</th>
                                                 <th>Indikator & Target</th>
                                                 <th>D</th>
                                                 <th>P</th>
                                                 <th>Skor</th>
                                                 <th>Level</th>
                                                 <th>Kondisi Awal, Residual & Pengendalian</th>
                                                 <th>Status Bagian 2 & 3</th>
                                                 <th class="no-print">Aksi</th>
                                             </tr>
                                         </thead>
                                         <tbody>
                                             <?php foreach($risks as $r): 
                                                 $score = (int)$r['skor'];
                                                 $noR = $r['no_risiko'];
                                                 
                                                 $parts = explode(' - ', $r['pernyataan_risiko'], 2);
                                                 $stdName = count($parts) > 1 ? trim($parts[0]) : '';
                                                 
                                                 $hasT = isset($savedTelaah[$noR]) && count($savedTelaah[$noR]) > 0 && !empty($savedTelaah[$noR][0]['dokumen_ditelaah']);
                                                 $hasH = isset($savedHipotesis[$noR]) && !empty($savedHipotesis[$noR]['hipotesis']);
                                                 
                                                 $badgeT = $hasT 
                                                     ? '<span class="badge bg-success" id="badge-telaah-' . $noR . '"><i class="fas fa-check-circle me-1"></i>Telaah: Terisi</span>'
                                                     : '<span class="badge bg-secondary" id="badge-telaah-' . $noR . '"><i class="fas fa-times-circle me-1"></i>Telaah: Belum</span>';
                                                 
                                                 $badgeH = $hasH
                                                     ? '<span class="badge bg-success" id="badge-hipo-' . $noR . '"><i class="fas fa-check-circle me-1"></i>Hipotesis: Terisi</span>'
                                                     : '<span class="badge bg-secondary" id="badge-hipo-' . $noR . '"><i class="fas fa-times-circle me-1"></i>Hipotesis: Belum</span>';

                                                 $rawPernyataan = $r['pernyataan_risiko'];
                                                 $pernyataanClean = $rawPernyataan;
                                                 $risikoClean = '';
                                                 if (preg_match('/\(Risiko:\s*(.*?)\)\s*$/i', $rawPernyataan, $matches)) {
                                                     $risikoClean = $matches[1];
                                                     $pernyataanClean = trim(preg_replace('/\(Risiko:\s*.*?\)\s*$/i', '', $rawPernyataan));
                                                 }
                                             ?>
                                                 <tr class="risk-row" data-standard="<?php echo htmlspecialchars($stdName); ?>">
                                                     <td><strong><?php echo htmlspecialchars($noR); ?></strong></td>
                                                     <td>
                                                         <textarea name="risiko_pernyataan[<?php echo $noR; ?>]" class="form-control form-control-sm border-0 bg-transparent p-0" rows="3"><?php echo htmlspecialchars($r['pernyataan_risiko']); ?></textarea>
                                                     </td>
                                                     <td>
                                                         <textarea name="risiko_indikator[<?php echo $noR; ?>]" class="form-control form-control-sm border-0 bg-transparent p-0" rows="3"><?php echo htmlspecialchars($r['indikator_target']); ?></textarea>
                                                     </td>
                                                     <td class="text-center">
                                                         <input type="number" name="risiko_dampak[<?php echo $noR; ?>]" class="form-control form-control-sm text-center border-0 bg-transparent p-0" value="<?php echo $r['dampak']; ?>" style="width:50px;">
                                                     </td>
                                                     <td class="text-center">
                                                         <input type="number" name="risiko_probabilitas[<?php echo $noR; ?>]" class="form-control form-control-sm text-center border-0 bg-transparent p-0" value="<?php echo $r['probabilitas']; ?>" style="width:50px;">
                                                     </td>
                                                     <td class="text-center font-bold"><?php echo $score; ?></td>
                                                     <td class="text-center"><span class="risk-badge <?php echo $score >= 10 ? 'risk-red' : ($score >= 5 ? 'risk-yellow' : 'risk-green'); ?>"><?php echo $r['level']; ?></span></td>
                                                     <td>
                                                         <textarea name="risiko_pengendalian[<?php echo $noR; ?>]" class="form-control form-control-sm border-0 bg-transparent p-0" rows="3"><?php echo htmlspecialchars($r['pengendalian_diklaim']); ?></textarea>
                                                     </td>
                                                     <td class="text-center">
                                                         <div class="d-flex flex-column gap-1 align-items-center">
                                                             <?php echo $badgeT; ?>
                                                             <?php echo $badgeH; ?>
                                                         </div>
                                                     </td>
                                                     <td class="text-center no-print">
                                                         <button type="button" class="btn btn-sm btn-info btn-edit-detail-risiko" data-no-risiko="<?php echo htmlspecialchars($noR); ?>" data-pernyataan="<?php echo htmlspecialchars($pernyataanClean); ?>" data-risiko="<?php echo htmlspecialchars($risikoClean); ?>" data-pengendalian="<?php echo htmlspecialchars($r['pengendalian_diklaim']); ?>">
                                                             <i class="fas fa-edit me-1"></i> Detail
                                                         </button>
                                                     </td>
                                                 </tr>
                                             <?php endforeach; ?>
                                         </tbody>
                                     </table>
                                 </div>
                             <?php endif; ?>

                            <!-- BAGIAN 4: Rencana & Panduan Audit Lapangan -->
                            <h5 class="section-title">BAGIAN 4 — Rencana & Panduan Audit Lapangan</h5>
                            <div class="card border mb-4">
                                <div class="card-body p-3">
                                    <div class="table-responsive mb-3">
                                        <table class="table table-bordered text-dark" id="table-rencana">
                                            <thead>
                                                <tr class="bg-light small font-bold">
                                                    <th width="20%">Sesi</th>
                                                    <th width="40%">Fokus & Pertanyaan Kunci yang Akan Diajukan</th>
                                                    <th width="25%">Dokumen yang Perlu Disiapkan Auditee</th>
                                                    <th width="10%">Estimasi Waktu</th>
                                                    <th class="no-print" width="5%">Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php 
                                                $rencanaRows = $savedRencana;
                                                if(empty($rencanaRows)) {
                                                    $rencanaRows = [
                                                        ['sesi' => 'Opening Meeting', 'fokus_pertanyaan' => '• Sampaikan tujuan: validasi peta risiko, bukan mencari kesalahan', 'dokumen_disiapkan' => '—', 'estimasi_waktu' => '10 menit'],
                                                        ['sesi' => 'Risiko 1 MERAH', 'fokus_pertanyaan' => '', 'dokumen_disiapkan' => '', 'estimasi_waktu' => '60 menit'],
                                                        ['sesi' => 'Closing Notes', 'fokus_pertanyaan' => '• Review temuan lapangan bersama auditee', 'dokumen_disiapkan' => '—', 'estimasi_waktu' => '20 menit']
                                                    ];
                                                }
                                                $rencIdx = 0;
                                                foreach($rencanaRows as $row):
                                                ?>
                                                    <tr>
                                                        <td>
                                                            <input type="text" name="rencana[<?php echo $rencIdx; ?>][sesi]" class="form-control form-control-sm font-bold" value="<?php echo htmlspecialchars($row['sesi']); ?>" placeholder="Nama Sesi">
                                                        </td>
                                                        <td>
                                                            <textarea name="rencana[<?php echo $rencIdx; ?>][fokus_pertanyaan]" class="form-control form-control-sm" rows="3" placeholder="Pertanyaan kunci..."><?php echo htmlspecialchars($row['fokus_pertanyaan']); ?></textarea>
                                                        </td>
                                                        <td>
                                                            <textarea name="rencana[<?php echo $rencIdx; ?>][dokumen_disiapkan]" class="form-control form-control-sm" rows="3" placeholder="Dokumen pendukung..."><?php echo htmlspecialchars($row['dokumen_disiapkan']); ?></textarea>
                                                        </td>
                                                        <td>
                                                            <input type="text" name="rencana[<?php echo $rencIdx; ?>][estimasi_waktu]" class="form-control form-control-sm text-center" value="<?php echo htmlspecialchars($row['estimasi_waktu']); ?>" placeholder="Waktu">
                                                        </td>
                                                        <td class="text-center no-print">
                                                            <i class="fas fa-trash-alt btn-delete-row" onclick="deleteRow(this)"></i>
                                                        </td>
                                                    </tr>
                                                <?php $rencIdx++; endforeach; ?>
                                            </tbody>
                                        </table>
                                        <button type="button" class="btn btn-add-row btn-sm no-print" onclick="addRencanaRow()"><i class="fas fa-plus me-1"></i> Tambah Sesi Kegiatan</button>
                                    </div>
                                </div>
                            </div>

                            <!-- Footer Save Button -->
                            <div class="d-flex justify-content-between align-items-center border-top pt-3 no-print">
                                <span class="text-muted small">* Dokumen ini adalah catatan kerja internal auditor dan tidak dibagikan kepada auditee.</span>
                                <button type="submit" name="simpan" class="btn btn-primary"><i class="fas fa-save me-2"></i> Simpan Catatan Persiapan</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>

<script type="text/javascript">
    function deleteRow(icon) {
        var row = icon.closest('tr');
        var tbody = row.parentNode;
        if(tbody.rows.length > 1) {
            row.remove();
        } else {
            alert("Harus ada minimal 1 baris!");
        }
    }

    function addTelaahRow(noRisiko) {
        var table = document.getElementById('table-telaah-' + noRisiko);
        var tbody = table.querySelector('tbody');
        var newIdx = tbody.rows.length;
        
        var newRow = document.createElement('tr');
        newRow.innerHTML = `
            <td>
                <input type="text" name="telaah[\${noRisiko}][\${newIdx}][dokumen_ditelaah]" class="form-control form-control-sm" placeholder="Nama Dokumen" required>
            </td>
            <td>
                <textarea name="telaah[\${noRisiko}][\${newIdx}][temuan]" class="form-control form-control-sm" rows="2" placeholder="Tulis temuan pada berkas..."></textarea>
            </td>
            <td>
                <select name="telaah[\${noRisiko}][\${newIdx}][kesesuaian]" class="form-control form-control-sm">
                    <option value="YA">YA</option>
                    <option value="TIDAK">TIDAK</option>
                    <option value="SEBAGIAN">SEBAGIAN</option>
                </select>
            </td>
            <td>
                <textarea name="telaah[\${noRisiko}][\${newIdx}][pertanyaan_lapangan]" class="form-control form-control-sm" rows="2" placeholder="Pertanyaan kunci untuk auditi..."></textarea>
            </td>
            <td class="text-center no-print">
                <i class="fas fa-trash-alt btn-delete-row" onclick="deleteRow(this)"></i>
            </td>
        `;
        tbody.appendChild(newRow);
    }

    function addRencanaRow() {
        var table = document.getElementById('table-rencana');
        var tbody = table.querySelector('tbody');
        var newIdx = tbody.rows.length;
        
        var newRow = document.createElement('tr');
        newRow.innerHTML = `
            <td>
                <input type="text" name="rencana[\${newIdx}][sesi]" class="form-control form-control-sm font-bold" placeholder="Nama Sesi" required>
            </td>
            <td>
                <textarea name="rencana[\${newIdx}][fokus_pertanyaan]" class="form-control form-control-sm" rows="3" placeholder="Pertanyaan kunci..."></textarea>
            </td>
            <td>
                <textarea name="rencana[\${newIdx}][dokumen_disiapkan]" class="form-control form-control-sm" rows="3" placeholder="Dokumen pendukung..."></textarea>
            </td>
            <td>
                <input type="text" name="rencana[\${newIdx}][estimasi_waktu]" class="form-control form-control-sm text-center" placeholder="Waktu">
            </td>
            <td class="text-center no-print">
                <i class="fas fa-trash-alt btn-delete-row" onclick="deleteRow(this)"></i>
            </td>
        `;
        tbody.appendChild(newRow);
    }
</script>
<!-- Modal Detail Telaah & Hipotesis -->
<div class="modal fade" id="modalTelaahHipotesis" tabindex="-1" aria-labelledby="modalTelaahHipotesisLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl text-dark">
        <div class="modal-content">
            <form id="formDetailRisiko">
                <input type="hidden" name="kodeaudit" value="<?php echo htmlspecialchars($ka); ?>">
                <input type="hidden" name="no_risiko" id="modal_no_risiko">
                <div class="modal-header">
                    <h5 class="modal-title font-semibold text-dark" id="modalTelaahHipotesisLabel">Hasil Telaah & Hipotesis: <span id="modal_label_risiko" class="font-bold text-primary"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body bg-light">
                    <!-- Bagian 2: Telaah Dokumen -->
                    <div class="card border mb-3">
                        <div class="card-header bg-white font-bold text-dark py-2">
                            BAGIAN 2 — Hasil Telaah Dokumen
                        </div>
                        <div class="card-body p-3">
                            <div class="mb-3">
                                <div id="modal_pernyataan_display" class="p-3 border rounded bg-white text-dark" style="font-size: 0.9rem; line-height: 1.5;">
                                    <div class="mb-2">
                                        <strong class="text-primary"><i class="fas fa-file-alt me-1"></i> Pernyataan Pra-AMI:</strong>
                                        <div id="modal_pernyataan_text" class="mt-1 ps-3 border-start"></div>
                                    </div>
                                    <div class="mb-2">
                                        <strong class="text-danger"><i class="fas fa-exclamation-triangle me-1"></i> Risiko Potensial:</strong>
                                        <div id="modal_risiko_text" class="mt-1 ps-3 border-start text-danger font-semibold"></div>
                                    </div>
                                    <div class="mb-0">
                                        <strong class="text-success"><i class="fas fa-shield-alt me-1"></i> Pengendalian yang Diklaim Auditee:</strong>
                                        <div id="modal_pengendalian_text" class="mt-1 ps-3 border-start text-muted"></div>
                                    </div>
                                </div>
                                <input type="hidden" name="standar_butir" id="modal_standar_butir">
                            </div>
                            
                            <div class="table-responsive mb-2">
                                <table class="table table-bordered text-dark" id="modal-table-telaah">
                                    <thead>
                                        <tr class="bg-light small font-bold">
                                            <th width="30%">Dokumen yang Ditelaah</th>
                                            <th width="30%">Temuan dari Dokumen</th>
                                            <th width="15%">Apakah Sesuai?</th>
                                            <th width="20%">Pertanyaan Audit Lapangan</th>
                                            <th width="5%">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody id="modal-telaah-tbody">
                                        <!-- Row elements will be generated dynamically -->
                                    </tbody>
                                </table>
                            </div>
                            <button type="button" class="btn btn-add-row btn-sm" id="btn-modal-add-telaah"><i class="fas fa-plus me-1"></i> Tambah Berkas Telaah</button>
                        </div>
                    </div>
                    
                    <!-- Bagian 3: Hipotesis -->
                    <div class="card border mb-0">
                        <div class="card-header bg-white font-bold text-dark py-2">
                            BAGIAN 3 — Hipotesis Risiko Sebelum Audit Lapangan
                        </div>
                        <div class="card-body p-3">
                            <div class="row">
                                <div class="col-md-8 mb-2">
                                    <label class="form-label small font-bold text-dark">Dugaan/Hipotesis Auditor</label>
                                    <textarea name="hipotesis_text" id="modal_hipotesis_text" class="form-control form-control-sm" rows="3" placeholder="Tuliskan hipotesis/dugaan awal tim auditor..."></textarea>
                                </div>
                                <div class="col-md-4 mb-2">
                                    <label class="form-label small font-bold text-dark">Prediksi Hasil Lapangan</label>
                                    <select name="prediksi" id="modal_prediksi" class="form-control form-control-sm" required>
                                        <option value="KTS Major">KTS Major</option>
                                        <option value="KTS Minor">KTS Minor</option>
                                        <option value="OB">OB</option>
                                        <option value="Temuan Positif">Temuan Positif</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm" id="btn-modal-submit-risiko"><i class="fas fa-save me-1"></i> Simpan Detail</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var refSelect = document.getElementById('ref_form_pra_ami');
        var standarSelect = document.getElementById('standar_diaudit');
        
        if (refSelect && standarSelect) {
            function updateStandards() {
                var selectedOpt = refSelect.options[refSelect.selectedIndex];
                var savedStandard = "<?php echo htmlspecialchars($standarDiaudit ?? ''); ?>";
                
                // Keep selected value before clear to preserve it if updated
                var currentSelected = standarSelect.value || savedStandard;
                
                standarSelect.innerHTML = '<option value="">Pilih Standar</option>';
                
                if (selectedOpt) {
                    var standardsStr = selectedOpt.getAttribute('data-standards') || '';
                    if (standardsStr) {
                        var standards = standardsStr.split('||');
                        standards.forEach(function (std) {
                            var trimmed = std.trim();
                            if (trimmed) {
                                var opt = document.createElement('option');
                                opt.value = trimmed;
                                opt.textContent = trimmed;
                                if (trimmed === currentSelected) {
                                    opt.selected = true;
                                }
                                standarSelect.appendChild(opt);
                            }
                        });
                    }
                }
                
                // Add fallback option if saved value is not present
                var found = false;
                for (var i = 0; i < standarSelect.options.length; i++) {
                    if (standarSelect.options[i].value === currentSelected) {
                        found = true;
                        break;
                    }
                }
                if (!found && currentSelected) {
                    var fallbackOpt = document.createElement('option');
                    fallbackOpt.value = currentSelected;
                    fallbackOpt.textContent = currentSelected;
                    fallbackOpt.selected = true;
                    standarSelect.appendChild(fallbackOpt);
                }
            }

            refSelect.addEventListener('change', updateStandards);
            
            // Populate on load
            if (refSelect.value) {
                updateStandards();
            }
        }

        // Modal and AJAX Handling
        var modalEl = document.getElementById('modalTelaahHipotesis');
        if (modalEl) {
            var modalObj = new bootstrap.Modal(modalEl);
            
            var modalNoRisikoInput = document.getElementById('modal_no_risiko');
            var modalLabelRisiko = document.getElementById('modal_label_risiko');
            var modalStandarButir = document.getElementById('modal_standar_butir');
            var modalTelaahTbody = document.getElementById('modal-telaah-tbody');
            var modalHipotesis = document.getElementById('modal_hipotesis_text');
            var modalPrediksi = document.getElementById('modal_prediksi');
            var btnAddTelaah = document.getElementById('btn-modal-add-telaah');
            var formDetail = document.getElementById('formDetailRisiko');
            
            // Populate standard filter select with standard names
            var filterSelect = document.getElementById('filter-standar-rekap');
            if (filterSelect) {
                filterSelect.addEventListener('change', function () {
                    var selectedStd = filterSelect.value;
                    document.querySelectorAll('.risk-row').forEach(function (row) {
                        var rowStd = row.getAttribute('data-standard') || '';
                        if (!selectedStd || rowStd === selectedStd) {
                            row.style.display = '';
                        } else {
                            row.style.display = 'none';
                        }
                    });
                });
            }
            
            // Function to create a new row in the modal's telaah table
            function createTelaahRow(dokumen, temuan, kesesuaian, pertanyaan) {
                var rowIdx = modalTelaahTbody.rows.length;
                var tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>
                        <input type="text" name="telaah[${rowIdx}][dokumen_ditelaah]" class="form-control form-control-sm" value="${dokumen || ''}" placeholder="Nama Dokumen" required>
                    </td>
                    <td>
                        <textarea name="telaah[${rowIdx}][temuan]" class="form-control form-control-sm" rows="2" placeholder="Temuan...">${temuan || ''}</textarea>
                    </td>
                    <td>
                        <select name="telaah[${rowIdx}][kesesuaian]" class="form-control form-control-sm">
                            <option value="YA" ${kesesuaian === 'YA' ? 'selected' : ''}>YA</option>
                            <option value="TIDAK" ${kesesuaian === 'TIDAK' ? 'selected' : ''}>TIDAK</option>
                            <option value="SEBAGIAN" ${kesesuaian === 'SEBAGIAN' ? 'selected' : ''}>SEBAGIAN</option>
                        </select>
                    </td>
                    <td>
                        <textarea name="telaah[${rowIdx}][pertanyaan_lapangan]" class="form-control form-control-sm" rows="2" placeholder="Pertanyaan...">${pertanyaan || ''}</textarea>
                    </td>
                    <td class="text-center">
                        <i class="fas fa-trash-alt text-danger cursor-pointer" onclick="this.closest('tr').remove();"></i>
                    </td>
                `;
                modalTelaahTbody.appendChild(tr);
            }
            
            btnAddTelaah.addEventListener('click', function () {
                createTelaahRow('', '', 'YA', '');
            });
            
            document.querySelectorAll('.btn-edit-detail-risiko').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var noR = btn.getAttribute('data-no-risiko');
                    var pernyataan = btn.getAttribute('data-pernyataan') || '';
                    modalNoRisikoInput.value = noR;
                    modalLabelRisiko.textContent = noR;
                    
                    var modalPernyataanText = document.getElementById('modal_pernyataan_text');
                    var modalRisikoText = document.getElementById('modal_risiko_text');
                    var modalPengendalianText = document.getElementById('modal_pengendalian_text');
                    
                    var risiko = btn.getAttribute('data-risiko') || '';
                    var pengendalian = btn.getAttribute('data-pengendalian') || '';
                    
                    if (modalPernyataanText) modalPernyataanText.textContent = pernyataan;
                    if (modalRisikoText) modalRisikoText.textContent = risiko;
                    if (modalPengendalianText) modalPengendalianText.textContent = pengendalian;
                    
                    modalStandarButir.value = pernyataan + (risiko ? ' (Risiko: ' + risiko + ')' : '');
                    
                    // Show loading
                    modalTelaahTbody.innerHTML = '<tr><td colspan="5" class="text-center"><i class="fas fa-spinner fa-spin"></i> Memuat data...</td></tr>';
                    modalHipotesis.value = '';
                    
                    // Fetch data via AJAX
                    fetch('audit.catatanpersiapan.ajax.php?action=get&kodeaudit=<?php echo urlencode($ka); ?>&no_risiko=' + encodeURIComponent(noR))
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            modalStandarButir.value = data.standar_butir || (pernyataan + (risiko ? ' (Risiko: ' + risiko + ')' : ''));
                            modalHipotesis.value = data.hipotesis || '';
                            modalPrediksi.value = data.prediksi || 'OB';
                            
                            modalTelaahTbody.innerHTML = '';
                            if (data.telaah && data.telaah.length > 0) {
                                data.telaah.forEach(function (row) {
                                    createTelaahRow(row.dokumen_ditelaah, row.temuan, row.kesesuaian, row.pertanyaan_lapangan);
                                });
                            } else {
                                createTelaahRow('', '', 'YA', '');
                            }
                        } else {
                            Swal.fire('Error', data.message || 'Gagal memuat data.', 'error');
                        }
                    })
                    .catch(err => {
                        Swal.fire('Error', 'Terjadi kesalahan jaringan.', 'error');
                    });
                    
                    modalObj.show();
                });
            });
            
            formDetail.addEventListener('submit', function (e) {
                e.preventDefault();
                
                var noR = modalNoRisikoInput.value;
                var submitBtn = document.getElementById('btn-modal-submit-risiko');
                var originalHtml = submitBtn.innerHTML;
                
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Menyimpan...';
                
                var formData = new FormData(formDetail);
                
                fetch('audit.catatanpersiapan.ajax.php?action=save', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalHtml;
                    
                    if (data.success) {
                        modalObj.hide();
                        
                        // Update badges in the row
                        var badgeTelaah = document.getElementById('badge-telaah-' + noR);
                        var badgeHipo = document.getElementById('badge-hipo-' + noR);
                        
                        if (badgeTelaah) {
                            if (data.status_telaah === 'Terisi') {
                                badgeTelaah.className = 'badge bg-success';
                                badgeTelaah.innerHTML = '<i class="fas fa-check-circle me-1"></i>Telaah: Terisi';
                            } else {
                                badgeTelaah.className = 'badge bg-secondary';
                                badgeTelaah.innerHTML = '<i class="fas fa-times-circle me-1"></i>Telaah: Belum';
                            }
                        }
                        
                        if (badgeHipo) {
                            if (data.status_hipotesis === 'Terisi') {
                                badgeHipo.className = 'badge bg-success';
                                badgeHipo.innerHTML = '<i class="fas fa-check-circle me-1"></i>Hipotesis: Terisi';
                            } else {
                                badgeHipo.className = 'badge bg-secondary';
                                badgeHipo.innerHTML = '<i class="fas fa-times-circle me-1"></i>Hipotesis: Belum';
                            }
                        }
                        
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: data.message,
                            timer: 1500,
                            showConfirmButton: false
                        });
                    } else {
                        Swal.fire('Gagal', data.message || 'Gagal menyimpan.', 'error');
                    }
                })
                .catch(err => {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalHtml;
                    Swal.fire('Error', 'Terjadi kesalahan jaringan.', 'error');
                });
            });
        }
    });
</script>

<?php 
include 'foot.php';
?>
