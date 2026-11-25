<?php
include 'nav.php';
?>
<style>
    .lk2-card {
        border-radius: 12px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        border: 1px solid #e5e7eb;
        margin-bottom: 2rem;
        background: #fff;
    }
    .lk2-header {
        background: linear-gradient(to right, #f8fafc, #eff6ff);
        border-bottom: 1px solid #e5e7eb;
        padding: 1.25rem 1.5rem;
        border-top-left-radius: 12px;
        border-top-right-radius: 12px;
    }
    .lk2-header.header-positif {
        background: linear-gradient(to right, #f0fdf4, #dcfce7);
    }
    .lk2-body {
        padding: 1.5rem;
    }
    .plor-grid {
        display: grid;
        grid-template-columns: 30px 1fr;
        gap: 0.5rem;
        margin-bottom: 1rem;
    }
    .plor-label {
        font-weight: bold;
        color: #3b82f6;
    }
    .skor-box {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 1rem;
        text-align: center;
    }
    .readonly-text {
        font-size: 0.9rem;
        color: #4b5563;
        background: #f8fafc;
        padding: 0.75rem;
        border-radius: 6px;
        border: 1px solid #e5e7eb;
        min-height: 40px;
    }
    .badge-level-merah   { background: #dc2626; color: #fff; }
    .badge-level-oranye  { background: #ea580c; color: #fff; }
    .badge-level-kuning  { background: #ca8a04; color: #fff; }
    .badge-level-hijau   { background: #16a34a; color: #fff; }
    .section-divider {
        border: 0;
        border-top: 2px solid #e5e7eb;
        margin: 2rem 0;
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
                                <p class="text-muted mb-0">Kode Audit: <strong>#<?php echo htmlspecialchars($_GET['ka']);?></strong></p>
                            </div>
                            <div class="mt-3 mt-md-0">
                                <a class="btn btn-outline-secondary btn-sm" href="audit.progres?ka=<?php echo urlencode($_GET['ka']);?>&et=<?php echo urlencode($_GET['et']);?>"><i class="fas fa-caret-left me-1"></i> Kembali</a>
                                <a class="btn btn-primary btn-sm ms-2" data-bs-toggle="collapse" href="#tambahPdf" role="button" aria-expanded="false" aria-controls="tambahPdf">
                                    <i class="fas fa-file-pdf me-1"></i> Upload Berkas LK2
                                </a>
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
                            
                            $nupn=$kodeaudit.'_LK2_'.$upn;
                            $upload=mysqli_query($kon,"update audit set lk2='$nupn' where kodeaudit='$kodeaudit'");
                            
                            if($upload) {
                                if(!empty($gk['lk2'])) { @unlink("assets/laporan/".$gk['lk2']); }
                                move_uploaded_file($upt,"assets/laporan/".$nupn);
                                echo '<div class="alert alert-success alert-dismissible fade show"><i class="fas fa-check-circle me-2"></i>Berkas PDF LK2 berhasil disimpan.<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
                            } else {
                                echo '<div class="alert alert-danger alert-dismissible fade show"><i class="fas fa-times-circle me-2"></i>Berkas PDF LK2 gagal disimpan.<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
                            }
                        }

                        // Handle hapus PDF (Legacy)
                        if(isset($_GET['hapus'])) {
                            $berkas = mysqli_real_escape_string($kon, $_GET['berkas']);
                            @unlink('assets/laporan/'.$berkas);
                            mysqli_query($kon,"update audit set lk2='' where kodeaudit='$ka'");
                            echo "<script>alert('Berkas LK2 berhasil direset'); window.location='audit.lk2?ka=".urlencode($ka)."&et=".urlencode($et)."';</script>";
                            exit;
                        }

                        $gk = mysqli_fetch_array(mysqli_query($kon,"select * from audit where kodeaudit='$ka'"));
                        
                        // Cek ketersediaan tabel
                        $hasLk2Table = mysqli_num_rows(mysqli_query($kon, "SHOW TABLES LIKE 'audit_lk2_temuan'")) > 0;
                        $hasRisikoTable = mysqli_num_rows(mysqli_query($kon, "SHOW TABLES LIKE 'audit_persiapan_risiko'")) > 0;

                        // ============================================================
                        // Sumber data risiko: langsung dari audit_persiapan_risiko
                        // Tidak perlu triangulasi selesai terlebih dahulu
                        // ============================================================
                        $risksA = []; // Temuan Ketidaksesuaian (non-positif)
                        $risksB = []; // Kesesuaian & Praktik Baik (positif)

                        if($hasRisikoTable) {
                            $qRisks = mysqli_query($kon, "
                                SELECT r.no_risiko, r.pernyataan_risiko, r.indikator_target,
                                       r.dampak AS dampak_awal, r.probabilitas AS peluang_awal,
                                       r.skor AS skor_awal, r.level AS level_awal,
                                       r.pengendalian_diklaim,
                                       l.tipe_temuan
                                FROM audit_persiapan_risiko r
                                LEFT JOIN audit_lk2_temuan l 
                                    ON r.kodeaudit = l.kodeaudit AND r.no_risiko = l.no_risiko
                                WHERE r.kodeaudit='$ka'
                                ORDER BY CAST(REPLACE(r.no_risiko, 'R-', '') AS UNSIGNED) ASC
                            ");
                            if($qRisks) {
                                while($row = mysqli_fetch_assoc($qRisks)) {
                                    // Kelompokkan berdasarkan tipe_temuan di lk2_temuan
                                    // Jika belum ada di lk2, masukkan ke risksA (default non-positif)
                                    if(isset($row['tipe_temuan']) && strtolower($row['tipe_temuan']) === 'positif') {
                                        $risksB[] = $row;
                                    } else {
                                        $risksA[] = $row;
                                    }
                                }
                            }
                        }

                        // Ambil data LK2 yang sudah tersimpan
                        $lk2Map = [];
                        if($hasLk2Table) {
                            $qT = mysqli_query($kon, "SELECT * FROM audit_lk2_temuan WHERE kodeaudit='$ka'");
                            if($qT) {
                                while($t = mysqli_fetch_assoc($qT)) {
                                    $lk2Map[$t['no_risiko']] = $t;
                                }
                            }
                        }
                        $lk2Kes = [];
                        if($hasLk2Table) {
                            $qK = mysqli_query($kon, "SELECT * FROM audit_lk2_kesimpulan WHERE kodeaudit='$ka'");
                            if($qK) {
                                $lk2Kes = mysqli_fetch_assoc($qK) ?: [];
                            }
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

                        $headerData = mysqli_fetch_array(mysqli_query($kon, "SELECT * FROM audit_persiapan WHERE kodeaudit='$ka'"));
                        $noDokumen = $headerData['no_dokumen'] ?? ('LK2-AMI/'.date('Y', strtotime($gda['tglmulai'])).'/'.($gda['unit'] ?? ''));
                        $tglAuditDok = $headerData['tanggal_audit_dok'] ?? ($gda['tglmulai'] ?? '');
                        $ketuaTim = $headerData['ketua_tim'] ?? $namaKetua;
                        $anggotaTim = $headerData['anggota_tim'] ?? $namaAnggota;
                        $unitDiaudit = $headerData['unit_diaudit'] ?? ($gda['nama_unit'] ?? '');
                        $auditeeKaprodi = $headerData['auditee_kaprodi'] ?? ($gda['kepala_unit'] ?? '');
                        $standarDiaudit = $headerData['standar_diaudit'] ?? 'Standar Proses Pembelajaran (SPMI-STD-03)';
                        $refFormPraAmi = $headerData['ref_form_pra_ami'] ?? '';
                        $periodeAmi = $headerData['periode_ami'] ?? ('Sem. Genap / TA '.date('Y', strtotime($gda['tglmulai'])).'-'.(date('Y', strtotime($gda['tglmulai']))+1));

                        if (!empty($refFormPraAmi)) {
                            $statusFormA = $refFormPraAmi;
                        } else {
                            $statusFormA = "BELUM ADA — Risiko potensial lahir dari temuan & akar penyebab";
                        }

                        // Helper: warna badge level
                        function levelBadgeClass($level) {
                            $l = strtolower($level ?? '');
                            if(stripos($l, 'merah')!==false)  return 'badge-level-merah';
                            if(stripos($l, 'oranye')!==false || stripos($l, 'orange')!==false) return 'badge-level-oranye';
                            if(stripos($l, 'kuning')!==false) return 'badge-level-kuning';
                            if(stripos($l, 'hijau')!==false)  return 'badge-level-hijau';
                            return 'bg-secondary';
                        }
                        ?>

                        <div class="collapse mb-4 <?php echo empty($gk['lk2']) && empty($lk2Map) ? 'show' : ''; ?>" id="tambahPdf">
                            <div class="card card-body bg-light border">
                                <h6 class="mb-3"><i class="fas fa-file-pdf text-danger me-2"></i>Upload Berkas PDF LK2</h6>
                                <form method="post" enctype="multipart/form-data">
                                    <input type="hidden" name="idaudit" value="<?php echo $cto['id'];?>">
                                    <input type="hidden" name="kodeaudit" value="<?php echo $cto['kodeaudit'];?>">
                                    <div class="row align-items-end">
                                        <div class="col-md-8">
                                            <label class="form-label text-muted small">Pilih Berkas (PDF)</label>
                                            <input type="file" name="ami" class="form-control" required accept="application/pdf">
                                        </div>
                                        <div class="col-md-4 mt-3 mt-md-0">
                                            <button class="btn btn-primary w-100" name="upload"><i class="fas fa-upload me-1"></i> Upload</button>
                                        </div>
                                    </div>
                                </form>
                                <?php if(!empty($gk['lk2'])): ?>
                                <div class="mt-3 p-3 bg-white rounded border d-flex justify-content-between align-items-center">
                                    <div class="text-truncate me-3">
                                        <i class="fas fa-check-circle text-success me-2"></i>
                                        <strong><?php echo htmlspecialchars($gk['lk2']);?></strong>
                                    </div>
                                    <div class="flex-shrink-0">
                                        <a class="btn btn-outline-primary btn-sm" href="assets/laporan/<?php echo urlencode($gk['lk2']);?>" target="_blank"><i class="fas fa-external-link-alt"></i></a>
                                        <a class="btn btn-outline-danger btn-sm" href="?hapus&ka=<?php echo urlencode($ka);?>&et=<?php echo urlencode($et);?>&berkas=<?php echo urlencode($gk['lk2']);?>" onclick="return confirm('Hapus berkas ini?')"><i class="fas fa-trash"></i></a>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <?php if(!$hasRisikoTable || (empty($risksA) && empty($risksB))): ?>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>Belum ada data risiko dari Form Persiapan Audit. Silakan isi Form Persiapan terlebih dahulu, atau lihat berkas PDF di atas jika tersedia.
                            </div>
                        <?php else: ?>

                            <?php if(!empty($lk2Map)): ?>
                            <div class="alert alert-success mb-4">
                                <i class="fas fa-check-circle me-2"></i>Menampilkan hasil Form LK2 Digital (Read-only).
                            </div>
                            <?php else: ?>
                            <div class="alert alert-warning mb-4">
                                <i class="fas fa-exclamation-triangle me-2"></i>Data risiko sudah tersedia dari Form Persiapan. Form LK2 Digital belum diisi. Silakan lihat berkas PDF di atas jika tersedia.
                            </div>
                            <?php endif; ?>

                            <!-- ======================================================
                                 LK2 HEADER: DOKUMEN & TIM AUDITOR
                                 ====================================================== -->
                            <div class="table-responsive mb-4">
                                <table class="table table-bordered align-middle text-dark bg-white" style="font-size: 0.85rem; border-color: #cbd5e1;">
                                    <tbody>
                                        <tr>
                                            <td style="background-color: #f8fafc; font-weight: 700; width: 15%;">Ketua Tim Auditor</td>
                                            <td style="width: 18%;"><?php echo htmlspecialchars($ketuaTim); ?></td>
                                            <td style="background-color: #f8fafc; font-weight: 700; width: 12%;">Anggota Tim</td>
                                            <td style="width: 25%;"><?php echo htmlspecialchars($anggotaTim); ?></td>
                                            <td style="background-color: #f8fafc; font-weight: 700; width: 12%;">Tanggal Audit</td>
                                            <td style="width: 18%;"><?php echo htmlspecialchars($tglAuditDok); ?></td>
                                        </tr>
                                        <tr>
                                            <td style="background-color: #f8fafc; font-weight: 700;">Unit yang Diaudit</td>
                                            <td><?php echo htmlspecialchars($unitDiaudit); ?></td>
                                            <td style="background-color: #f8fafc; font-weight: 700;">Auditee / Kaprodi</td>
                                            <td><?php echo htmlspecialchars($auditeeKaprodi); ?></td>
                                            <td style="background-color: #f8fafc; font-weight: 700;">No. Dokumen</td>
                                            <td><?php echo htmlspecialchars($noDokumen); ?></td>
                                        </tr>
                                        <tr>
                                            <td style="background-color: #f8fafc; font-weight: 700;">Standar yang Diaudit</td>
                                            <td><?php echo htmlspecialchars($standarDiaudit); ?></td>
                                            <td style="background-color: #f8fafc; font-weight: 700;">Status Form A</td>
                                            <td><?php echo htmlspecialchars($statusFormA); ?></td>
                                            <td style="background-color: #f8fafc; font-weight: 700;">Periode AMI</td>
                                            <td><?php echo htmlspecialchars($periodeAmi); ?></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <!-- ======================================================
                                 LK2-A: TEMUAN KETIDAKSESUAIAN
                                 Referensi: Tabel LK2-A di LK2_DenganFormA v2.docx
                                 Kolom: No | Pernyataan Standar | Indikator & Target |
                                        PLOR | Akar Penyebab | Risiko Potensial |
                                        Kategori | Pengendalian & Efektivitas |
                                        D | P | Skor & Level | Tren | Rekomendasi
                                 ====================================================== -->
                            <?php if(!empty($risksA)): ?>
                            <h5 class="mb-3 text-primary border-bottom pb-2">
                                <i class="fas fa-exclamation-circle me-2"></i> LK2-A: Temuan Ketidaksesuaian &amp; Risiko Potensial
                            </h5>
                            <?php foreach($risksA as $idx => $r): 
                                $noR = $r['no_risiko'];
                                $tData = $lk2Map[$noR] ?? null;
                                $noDisplay = $idx + 1;
                            ?>
                                <div class="lk2-card">
                                    <div class="lk2-header">
                                        <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                                            <span class="badge bg-secondary"><?php echo htmlspecialchars($noDisplay); ?></span>
                                            <span class="badge bg-primary"><?php echo htmlspecialchars($noR); ?></span>
                                            <?php if($tData && !empty($tData['tipe_temuan'])): ?>
                                            <span class="badge <?php 
                                                $tp = strtolower($tData['tipe_temuan']);
                                                if(stripos($tp,'mayor')!==false) echo 'bg-danger';
                                                elseif(stripos($tp,'minor')!==false) echo 'bg-warning text-dark';
                                                elseif(stripos($tp,'ob')!==false) echo 'bg-info text-dark';
                                                else echo 'bg-secondary';
                                            ?>"><?php echo htmlspecialchars($tData['tipe_temuan']); ?></span>
                                            <?php endif; ?>
                                            <strong class="text-primary"><?php echo htmlspecialchars($r['pernyataan_risiko']); ?></strong>
                                        </div>
                                        <?php if(!empty($r['indikator_target'])): ?>
                                        <div class="text-muted small mt-1"><i class="fas fa-bullseye me-1"></i><?php echo htmlspecialchars($r['indikator_target']); ?></div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="lk2-body">
                                        <?php if($tData): ?>
                                        <div class="row g-4">
                                            <!-- Kolom Kiri: PLOR + Akar Penyebab + Risiko Potensial -->
                                            <div class="col-lg-6 border-end-lg pe-lg-4">
                                                <h6 class="mb-3 text-primary"><i class="fas fa-clipboard-list me-1"></i> Hasil Konfirmasi Lapangan (PLOR)</h6>
                                                <div class="plor-grid">
                                                    <div class="plor-label">P</div><div class="readonly-text"><?php echo nl2br(htmlspecialchars($tData['plor_p'])); ?></div>
                                                    <div class="plor-label">L</div><div class="readonly-text"><?php echo nl2br(htmlspecialchars($tData['plor_l'])); ?></div>
                                                    <div class="plor-label">O</div><div class="readonly-text"><?php echo nl2br(htmlspecialchars($tData['plor_o'])); ?></div>
                                                    <div class="plor-label">R</div><div class="readonly-text"><?php echo nl2br(htmlspecialchars($tData['plor_r'])); ?></div>
                                                </div>

                                                <h6 class="mb-2 mt-3">Akar Penyebab Temuan</h6>
                                                <div class="readonly-text mb-3"><?php echo nl2br(htmlspecialchars($tData['akar_penyebab'])); ?></div>

                                                <h6 class="mb-2">Risiko Potensial</h6>
                                                <div class="readonly-text"><?php echo nl2br(htmlspecialchars($tData['risiko_potensial'])); ?></div>
                                            </div>

                                            <!-- Kolom Kanan: Pengendalian + Risiko Residual + Rekomendasi -->
                                            <div class="col-lg-6 ps-lg-4">
                                                <h6 class="mb-2 text-primary"><i class="fas fa-shield-alt me-1"></i> Pengendalian &amp; Efektivitas</h6>
                                                <div class="readonly-text mb-4"><?php echo nl2br(htmlspecialchars($tData['pengendalian_efektivitas'] ?? $r['pengendalian_diklaim'] ?? '')); ?></div>

                                                <h6 class="mb-3 text-primary"><i class="fas fa-chart-bar me-1"></i> Risiko Residual (Baseline)</h6>
                                                <div class="d-flex flex-wrap gap-2 mb-3 align-items-center">
                                                    <div class="text-center">
                                                        <div class="text-muted small mb-1">Dampak (D)</div>
                                                        <span class="badge bg-light text-dark border fs-6 px-3 py-2"><?php echo htmlspecialchars($tData['dampak']); ?></span>
                                                    </div>
                                                    <div class="text-center">
                                                        <div class="text-muted small mb-1">Peluang (P)</div>
                                                        <span class="badge bg-light text-dark border fs-6 px-3 py-2"><?php echo htmlspecialchars($tData['peluang']); ?></span>
                                                    </div>
                                                    <div class="text-center">
                                                        <div class="text-muted small mb-1">Skor</div>
                                                        <span class="badge <?php echo levelBadgeClass($tData['level']); ?> fs-6 px-3 py-2"><?php echo htmlspecialchars($tData['skor']); ?></span>
                                                    </div>
                                                    <div class="text-center">
                                                        <div class="text-muted small mb-1">Level</div>
                                                        <span class="badge <?php echo levelBadgeClass($tData['level']); ?> px-3 py-2"><?php echo htmlspecialchars($tData['level'] ?? '-'); ?></span>
                                                    </div>
                                                    <div class="text-center">
                                                        <div class="text-muted small mb-1">Tren</div>
                                                        <span class="badge bg-light text-dark border px-3 py-2"><?php echo htmlspecialchars($tData['tren'] ?? 'N/A'); ?></span>
                                                    </div>
                                                </div>

                                                <h6 class="mb-2 mt-4">Rekomendasi PTK</h6>
                                                <div class="readonly-text mb-3"><?php echo nl2br(htmlspecialchars($tData['rekomendasi_ptk'])); ?></div>

                                                <h6 class="mb-2">Rekomendasi RTM</h6>
                                                <div class="readonly-text"><?php echo nl2br(htmlspecialchars($tData['rekomendasi_rtm'])); ?></div>
                                            </div>
                                        </div>
                                        <?php else: ?>
                                        <div class="alert alert-light border mb-0">
                                            <i class="fas fa-info-circle text-muted me-2"></i>
                                            Belum ada data LK2 Digital untuk risiko ini. 
                                            <?php if(!empty($r['pengendalian_diklaim'])): ?>
                                            <hr class="my-2">
                                            <small class="text-muted"><strong>Pengendalian diklaim (dari Form Persiapan):</strong><br><?php echo nl2br(htmlspecialchars($r['pengendalian_diklaim'])); ?></small>
                                            <?php endif; ?>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            <?php endif; ?>

                            <!-- ======================================================
                                 LK2-B: KESESUAIAN & PRAKTIK BAIK
                                 Referensi: Tabel LK2-B di LK2_DenganFormA v2.docx
                                 Kolom: No | Butir Standar | Deskripsi Observasi (PLOR) |
                                        Faktor Pendukung | Peluang Peningkatan | Potensi Replikasi
                                 ====================================================== -->
                            <?php if(!empty($risksB)): ?>
                            <hr class="section-divider">
                            <h5 class="mb-3 text-success border-bottom pb-2">
                                <i class="fas fa-check-circle me-2"></i> LK2-B: Kesesuaian &amp; Praktik Baik
                            </h5>
                            <?php foreach($risksB as $idx => $r): 
                                $noR = $r['no_risiko'];
                                $tData = $lk2Map[$noR] ?? null;
                                $noDisplay = $idx + 1;
                            ?>
                                <div class="lk2-card">
                                    <div class="lk2-header header-positif">
                                        <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                                            <span class="badge bg-secondary"><?php echo htmlspecialchars($noDisplay); ?></span>
                                            <span class="badge bg-success me-1">Praktik Baik</span>
                                            <span class="badge bg-secondary"><?php echo htmlspecialchars($noR); ?></span>
                                            <strong class="text-success"><?php echo htmlspecialchars($r['pernyataan_risiko']); ?></strong>
                                        </div>
                                        <?php if(!empty($r['indikator_target'])): ?>
                                        <div class="text-muted small mt-1"><i class="fas fa-bullseye me-1"></i><?php echo htmlspecialchars($r['indikator_target']); ?></div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="lk2-body">
                                        <?php if($tData): ?>
                                        <div class="row g-4">
                                            <!-- Kolom Kiri: PLOR Observasi -->
                                            <div class="col-lg-6 border-end-lg pe-lg-4">
                                                <h6 class="mb-3 text-success"><i class="fas fa-binoculars me-1"></i> Deskripsi Observasi (PLOR)</h6>
                                                <div class="plor-grid">
                                                    <div class="plor-label text-success">P</div><div class="readonly-text"><?php echo nl2br(htmlspecialchars($tData['plor_p'])); ?></div>
                                                    <div class="plor-label text-success">L</div><div class="readonly-text"><?php echo nl2br(htmlspecialchars($tData['plor_l'])); ?></div>
                                                    <div class="plor-label text-success">O</div><div class="readonly-text"><?php echo nl2br(htmlspecialchars($tData['plor_o'])); ?></div>
                                                    <div class="plor-label text-success">R</div><div class="readonly-text"><?php echo nl2br(htmlspecialchars($tData['plor_r'])); ?></div>
                                                </div>
                                            </div>
                                            <!-- Kolom Kanan: Pengembangan Praktik Baik -->
                                            <div class="col-lg-6 ps-lg-4">
                                                <h6 class="mb-3 text-success"><i class="fas fa-star me-1"></i> Pengembangan Praktik Baik</h6>
                                                <div class="mb-3">
                                                    <strong>Faktor Pendukung Keberhasilan:</strong><br>
                                                    <div class="readonly-text mt-1"><?php echo nl2br(htmlspecialchars($tData['faktor_pendukung'])); ?></div>
                                                </div>
                                                <div class="mb-3">
                                                    <strong>Peluang Peningkatan:</strong><br>
                                                    <div class="readonly-text mt-1"><?php echo nl2br(htmlspecialchars($tData['peluang_peningkatan'])); ?></div>
                                                </div>
                                                <div>
                                                    <strong>Potensi Replikasi ke Unit Lain:</strong><br>
                                                    <div class="readonly-text mt-1"><?php echo nl2br(htmlspecialchars($tData['potensi_replikasi'])); ?></div>
                                                </div>
                                            </div>
                                        </div>
                                        <?php else: ?>
                                        <div class="alert alert-light border mb-0">
                                            <i class="fas fa-info-circle text-muted me-2"></i>Belum ada data LK2 Digital untuk praktik baik ini.
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            <?php endif; ?>

                            <!-- ======================================================
                                 KESIMPULAN AUDIT
                                 Referensi: Tabel Kesimpulan di LK2_DenganFormA v2.docx
                                 Kolom: Catatan Umum Auditor | Rekomendasi Peningkatan (RTM)
                                 ====================================================== -->
                            <?php if(!empty($lk2Map)): ?>
                            <hr class="section-divider">
                            <h5 class="mb-3 text-dark border-bottom pb-2">
                                <i class="fas fa-clipboard-list me-2"></i> Kesimpulan Audit &amp; Catatan Auditor
                            </h5>
                            <div class="lk2-card mb-4">
                                <div class="lk2-body">
                                    <div class="row g-4">
                                        <div class="col-md-6">
                                            <strong><i class="fas fa-pen me-1 text-muted"></i> Catatan Umum Auditor:</strong>
                                            <div class="readonly-text mt-2"><?php echo nl2br(htmlspecialchars($lk2Kes['catatan_umum'] ?? '')); ?></div>
                                        </div>
                                        <div class="col-md-6">
                                            <strong><i class="fas fa-bullhorn me-1 text-muted"></i> Rekomendasi Peningkatan (untuk RTM):</strong>
                                            <div class="readonly-text mt-2"><?php echo nl2br(htmlspecialchars($lk2Kes['rekomendasi_strategis'] ?? '')); ?></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>

                        <?php endif; ?>

                    </div>
                </div>
            </div>
        </section>
    </div>
</div>
<?php 
// Bersihkan file temporary
@unlink(__DIR__.'/check_schema.php');
@unlink(__DIR__.'/migrate_lk2.php');
include 'foot.php';
?>
