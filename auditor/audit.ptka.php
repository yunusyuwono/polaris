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
        --ptk-dark: #1e293b; --ptk-dark-light: #f8fafc; --ptk-dark-border: #e2e8f0;
    }
    
    .ptk-card {
        border-radius: 16px; 
        border: 1px solid #cbd5e1;
        margin-bottom: 2.5rem; 
        background: #fff;
        box-shadow: 0 10px 25px -5px rgba(0,0,0,0.05), 0 8px 10px -6px rgba(0,0,0,0.05); 
        overflow: hidden;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    
    .ptk-card:hover {
        box-shadow: 0 20px 25px -5px rgba(0,0,0,0.08), 0 10px 10px -5px rgba(0,0,0,0.04);
    }

    /* Tabular card header */
    .ptk-header-table {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        background: #fff;
        border-bottom: 1.5px solid #cbd5e1;
    }
    
    .ptk-ht-row {
        display: contents;
    }
    
    .title-row .ptk-ht-cell {
        grid-column: span 4;
        background: linear-gradient(to right, #0f172a, #1e293b);
        color: #fff;
        text-align: center;
        font-weight: 800;
        font-size: 0.95rem;
        padding: 0.75rem 1rem;
        text-transform: uppercase;
        letter-spacing: 0.8px;
    }
    
    .ptk-ht-cell {
        padding: 0.6rem 0.85rem;
        border-bottom: 1px solid #e2e8f0;
        border-right: 1px solid #e2e8f0;
        display: flex;
        flex-direction: column;
        justify-content: center;
        gap: 0.25rem;
    }
    
    .ptk-ht-cell:nth-child(4n), .ptk-ht-cell.no-right-border {
        border-right: none;
    }
    
    .ptk-ht-row-last .ptk-ht-cell {
        border-bottom: none;
    }
    
    .ptk-meta-label {
        font-size: 0.68rem;
        font-weight: 800;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .ptk-meta-val {
        font-size: 0.85rem;
        font-weight: 700;
        color: #1e293b;
    }
    
    .ptk-meta-input {
        border: 1px solid #cbd5e1;
        border-radius: 5px;
        padding: 0.25rem 0.5rem;
        font-size: 0.8rem;
        font-weight: 700;
        color: #1e293b;
        background: #f8fafc;
        transition: all 0.2s;
        width: 100%;
    }
    
    .ptk-meta-input:focus {
        outline: none;
        border-color: #6366f1;
        background: #fff;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
    }
    
    .ptk-body { 
        padding: 1.75rem; 
    }
    
    .ptk-section {
        border-radius: 12px; 
        padding: 1.5rem;
        margin-bottom: 1.75rem; 
        border: 1.5px solid;
        position: relative;
    }
    
    .ptk-section.bagian-a { background: var(--ptk-blue-light); border-color: var(--ptk-blue-border); }
    .ptk-section.bagian-b { background: var(--ptk-dark-light); border-color: var(--ptk-dark-border); }
    .ptk-section.bagian-c { background: var(--ptk-yellow-light); border-color: var(--ptk-yellow-border); }
    .ptk-section.bagian-d { background: var(--ptk-purple-light); border-color: var(--ptk-purple-border); }
    
    .ptk-section-title {
        font-size: 0.8rem; 
        font-weight: 800; 
        letter-spacing: 0.8px;
        text-transform: uppercase; 
        margin-bottom: 1.25rem;
        padding-bottom: 0.5rem; 
        border-bottom: 1.5px solid rgba(0,0,0,0.06);
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 0.5rem;
    }
    
    .ptk-section-title.a { color: var(--ptk-blue); }
    .ptk-section-title.b { color: var(--ptk-dark); }
    .ptk-section-title.c { color: var(--ptk-yellow); }
    .ptk-section-title.d { color: var(--ptk-purple); }
    
    .role-badge {
        font-size: 0.65rem;
        font-weight: 800;
        padding: 0.25rem 0.6rem;
        border-radius: 6px;
        letter-spacing: 0.3px;
        text-transform: uppercase;
    }
    .role-badge.lpm { background: rgba(37,99,235,0.12); color: var(--ptk-blue); border: 1px solid rgba(37,99,235,0.2); }
    .role-badge.unit { background: rgba(30,41,59,0.1); color: var(--ptk-dark); border: 1px solid rgba(30,41,59,0.15); }
    .role-badge.auditor { background: rgba(124,58,237,0.12); color: var(--ptk-purple); border: 1px solid rgba(124,58,237,0.2); }
    
    .ptk-label { font-size: 0.78rem; font-weight: 700; color: #475569; margin-bottom: 0.4rem; display: block; }
    
    .ptk-input {
        border: 1px solid #cbd5e1; 
        border-radius: 8px;
        padding: 0.55rem 0.8rem; 
        font-size: 0.875rem; 
        width: 100%;
        background: #fff; 
        transition: border-color 0.2s, box-shadow 0.2s; 
        color: #1e293b;
    }
    
    .ptk-input:focus { 
        outline: none; 
        border-color: #6366f1; 
        box-shadow: 0 0 0 3px rgba(99,102,241,0.15); 
    }
    
    .ptk-textarea { min-height: 80px; resize: vertical; }
    
    /* Actions Table styling */
    .tindakan-table-wrapper {
        border-radius: 8px;
        border: 1px solid #e2e8f0;
        overflow: hidden;
        background: #fff;
    }
    
    .tindakan-table { 
        width: 100%; 
        border-collapse: collapse; 
        font-size: 0.85rem; 
        background: #fff;
    }
    
    .tindakan-table th {
        background: #f8fafc; 
        padding: 0.75rem 0.85rem;
        text-align: left; 
        font-size: 0.7rem; 
        font-weight: 800;
        text-transform: uppercase; 
        letter-spacing: 0.5px; 
        color: #475569;
        border-bottom: 2px solid #e2e8f0;
        border-right: 1px solid #e2e8f0;
    }
    .tindakan-table th:last-child {
        border-right: none;
    }
    
    .tindakan-table td { 
        padding: 0.6rem 0.6rem; 
        vertical-align: top; 
        border-bottom: 1px solid #e2e8f0; 
        border-right: 1px solid #e2e8f0;
    }
    .tindakan-table td:last-child {
        border-right: none;
    }
    
    .tindakan-table tr:last-child td { border-bottom: none; }
    
    .tindakan-table input, .tindakan-table textarea {
        width: 100%; 
        border: 1px solid #cbd5e1; 
        border-radius: 6px;
        padding: 0.45rem 0.6rem; 
        font-size: 0.83rem; 
        background: #f8fafc;
        transition: all 0.2s;
    }
    
    .tindakan-table input:focus, .tindakan-table textarea:focus {
        outline: none; 
        border-color: #6366f1; 
        background: #fff;
        box-shadow: 0 0 0 3px rgba(99,102,241,0.1);
    }
    
    .tindakan-table textarea { min-height: 60px; resize: vertical; }
    
    .btn-add-row {
        font-size: 0.8rem; 
        font-weight: 700; 
        padding: 0.45rem 1.2rem;
        border: 2px dashed #6366f1; 
        border-radius: 8px; 
        background: transparent;
        color: #6366f1; 
        cursor: pointer; 
        transition: all 0.2s;
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
    }
    
    .btn-add-row:hover { background: #eef2ff; transform: translateY(-1px); }
    
    .btn-del-row {
        width: 28px; 
        height: 28px; 
        border-radius: 50%; 
        border: 1px solid #fecaca;
        background: #fff; 
        color: #dc2626; 
        font-size: 0.8rem; 
        cursor: pointer;
        display: flex; 
        align-items: center; 
        justify-content: center; 
        transition: all 0.2s; 
        flex-shrink: 0;
    }
    
    .btn-del-row:hover { background: #dc2626; color: #fff; border-color: #dc2626; }
    
    /* Monitoring status grid */
    .bulan-grid { 
        display: grid; 
        grid-template-columns: repeat(5, 1fr); 
        gap: 0.75rem; 
    }
    
    .bulan-item {
        background: #fff;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        padding: 0.6rem;
        text-align: center;
    }
    
    .bulan-item label { 
        font-size: 0.72rem; 
        font-weight: 800; 
        color: #64748b; 
        display: block; 
        margin-bottom: 0.35rem; 
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }
    
    /* Save buttons and status */
    .ptk-save-btn {
        font-weight: 700; 
        padding: 0.6rem 1.75rem; 
        border-radius: 8px;
        border: none; 
        cursor: pointer; 
        display: inline-flex; 
        align-items: center; 
        gap: 0.5rem;
        transition: background 0.2s, transform 0.1s, box-shadow 0.2s; 
        font-size: 0.875rem;
        box-shadow: 0 4px 6px -1px rgba(79, 70, 229, 0.2), 0 2px 4px -1px rgba(79, 70, 229, 0.1);
    }
    
    .ptk-save-btn:hover {
        box-shadow: 0 10px 15px -3px rgba(79, 70, 229, 0.3), 0 4px 6px -2px rgba(79, 70, 229, 0.05);
    }
    
    .ptk-save-btn:active { transform: scale(0.97); }
    
    .save-status {
        font-size: 0.825rem; 
        padding: 0.4rem 1rem; 
        border-radius: 6px;
        display: inline-flex; 
        align-items: center; 
        gap: 0.4rem;
        font-weight: 600;
    }
    
    .save-status.ok  { background: #f0fdf4; color: #16a34a; border: 1px solid #bbf7d0; }
    .save-status.err { background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; }
    
    .ref-chip {
        display: inline-block; 
        background: #f1f5f9; 
        border: 1px solid #cbd5e1;
        border-radius: 6px; 
        padding: 0.2rem 0.5rem; 
        font-size: 0.78rem; 
        color: #475569;
        font-weight: 700;
    }
    
    .badge-level { 
        padding: 0.25rem 0.6rem; 
        border-radius: 6px; 
        font-size: 0.75rem; 
        font-weight: 800; 
        display: inline-block;
    }
    
    .badge-level.MERAH  { background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; }
    .badge-level.ORANYE { background: #fff7ed; color: #ea580c; border: 1px solid #fed7aa; }
    .badge-level.KUNING { background: #fefce8; color: #ca8a04; border: 1px solid #fef08a; }
    .badge-level.HIJAU  { background: #f0fdf4; color: #16a34a; border: 1px solid #bbf7d0; }
    
    .ptk-card-footer {
        background: #f8fafc;
        border-top: 1px solid #e2e8f0;
        padding: 0.75rem 1.5rem;
        font-size: 0.75rem;
        color: #64748b;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 0.5rem;
    }
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
                                <h4 class="font-bold mb-1"><i class="fas fa-file-medical-alt me-2 text-danger"></i>PTK &mdash; Permintaan Tindakan Korektif</h4>
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
                                if(!empty($gk['ptka'])) @unlink("../assets/laporan/".$gk['ptka']);
                                move_uploaded_file($upt,"../assets/laporan/".$nupn);
                                echo '<div class="alert alert-success alert-dismissible fade show"><i class="fas fa-check-circle me-2"></i>Berkas PDF PTK berhasil disimpan.<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
                            } else {
                                echo '<div class="alert alert-danger alert-dismissible fade show"><i class="fas fa-times-circle me-2"></i>Berkas PDF PTK gagal disimpan.<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
                            }
                        }
                        if(isset($_GET['hapus'])) {
                            $berkas = mysqli_real_escape_string($kon, $_GET['berkas']);
                            @unlink('../assets/laporan/'.$berkas);
                            mysqli_query($kon,"update audit set ptka='' where kodeaudit='$ka'");
                            echo "<script>alert('Berkas PTK berhasil direset'); window.location='audit.ptka?ka=".urlencode($ka)."&et=".urlencode($et)."';</script>";
                            exit;
                        }

                        $gk = mysqli_fetch_array(mysqli_query($kon,"select * from audit where kodeaudit='$ka'"));

                        // Auto-create tables (idempoten)
                        include_once 'audit.ptka.save.php'; // reuse table creation only

                        // Fetch Unit info
                        $audit_data = mysqli_fetch_array(mysqli_query($kon,"SELECT a.*, u.nama AS namaunit FROM audit a JOIN unit u ON a.unit=u.id WHERE a.kodeaudit='$ka'"));
                        $namaunit = $audit_data['namaunit'] ?? '';

                        // Ambil risiko dari audit_persiapan_risiko + data LK2
                        $risks = [];
                        $qR = mysqli_query($kon, "
                            SELECT r.*, l.tipe_temuan, l.akar_penyebab, l.risiko_potensial,
                                   l.dampak, l.peluang, l.skor, l.level, l.tren,
                                   l.rekomendasi_rtm, l.rekomendasi_ptk
                            FROM audit_persiapan_risiko r
                            LEFT JOIN (
                                SELECT * FROM audit_lk2_temuan 
                                WHERE kodeaudit='$ka' AND id IN (
                                    SELECT MAX(id) FROM audit_lk2_temuan WHERE kodeaudit='$ka' GROUP BY no_risiko
                                )
                            ) l ON r.no_risiko=l.no_risiko
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

                        <div class="alert alert-info d-flex gap-2 align-items-start mb-4 shadow-sm border-0" style="background:#eef2ff; color:#3730a3;">
                            <i class="fas fa-info-circle mt-1" style="font-size:1.15rem;"></i>
                            <div>
                                <strong style="font-size:0.95rem;">Informasi Permintaan Tindakan Korektif (PTK)</strong><br>
                                <span style="font-size:0.875rem;">Berikut ini adalah daftar form PTK berdasarkan risiko yang teridentifikasi. Klik tombol <strong>Isi / Edit PTK</strong> untuk mengisi Form A, B, C, dan D secara bertahap pada modal interaktif. Status pengisian tiap bagian akan ter-update otomatis pada tabel.</span>
                            </div>
                        </div>

                        <!-- List Tabel PTK -->
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
                                        
                                        // Lewati Positif (LK2-B) — PTK hanya untuk temuan ketidaksesuaian
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
                                        <td class="text-center col-form-a">
                                            <?php if($hasA): ?>
                                                <span class="badge bg-success mb-1"><i class="fas fa-check-circle me-1"></i>Terisi</span><br>
                                                <span class="badge bg-light text-dark border"><?php echo htmlspecialchars($ptk['ptk_no'] ?? 'Form A'); ?></span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary"><i class="fas fa-times-circle me-1"></i>Belum</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center col-form-b">
                                            <?php if($hasB): ?>
                                                <span class="badge bg-success mb-1"><i class="fas fa-check-circle me-1"></i>Terisi</span><br>
                                                <span class="badge bg-light text-dark border"><?php echo $countB; ?> Tindakan</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary"><i class="fas fa-times-circle me-1"></i>Belum</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center col-form-c">
                                            <?php if($hasC): ?>
                                                <span class="badge bg-success mb-1"><i class="fas fa-check-circle me-1"></i>Terisi</span><br>
                                                <span class="badge bg-light text-dark border"><?php echo htmlspecialchars($lastC ?: 'Aktif'); ?></span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary"><i class="fas fa-times-circle me-1"></i>Belum</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center col-form-d">
                                            <?php if($hasD): ?>
                                                <span class="badge <?php echo $efektivitasVal==='EFEKTIF'?'bg-success':($efektivitasVal==='BELUM EFEKTIF'?'bg-warning text-dark':'bg-primary'); ?> mb-1">
                                                    <?php echo htmlspecialchars($efektivitasVal); ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary"><i class="fas fa-times-circle me-1"></i>Belum</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-3 shadow-sm" onclick="openPtkModal('<?php echo htmlspecialchars($noR); ?>')">
                                                <i class="fas fa-edit me-1"></i> Isi / Edit PTK
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

<!-- Modal Edit PTK -->
<div class="modal fade" id="modalEditPTKA" tabindex="-1" aria-labelledby="modalPtkLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">
            <div class="modal-header bg-primary text-white py-3">
                <h5 class="modal-title font-bold text-white mb-0" id="modalPtkTitle">
                    <i class="fas fa-file-medical-alt me-2"></i> Permintaan Tindakan Korektif (PTK)
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 bg-light">
                <input type="hidden" id="modal_ptk_no_risiko" value="">
                
                <!-- Pernyataan Standar & Risiko Card in Modal Body -->
                <div class="card border-0 shadow-sm p-3 mb-4 rounded-3 bg-white border-start border-primary border-4">
                    <div class="d-flex align-items-center mb-1">
                        <span class="badge bg-primary me-2" id="modalPtkBadge">R-1</span>
                        <h6 class="fw-bold mb-0 text-dark">Pernyataan Standar & Risiko</h6>
                    </div>
                    <div class="text-dark small lh-base mt-1" id="modalPtkStatement">-</div>
                </div>

                <!-- Nav Tabs -->
                <ul class="nav nav-pills nav-fill mb-4 bg-white p-2 rounded-3 border shadow-sm" id="ptkTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active fw-bold py-2" id="tab-ptk-a-tab" data-bs-toggle="tab" data-bs-target="#tab-ptk-a" type="button" role="tab">
                            <i class="fas fa-id-card me-1 text-primary"></i> 1. Form A (Identitas)
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-bold py-2" id="tab-ptk-b-tab" data-bs-toggle="tab" data-bs-target="#tab-ptk-b" type="button" role="tab">
                            <i class="fas fa-tasks me-1 text-dark"></i> 2. Form B (Rencana & Realisasi)
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-bold py-2" id="tab-ptk-c-tab" data-bs-toggle="tab" data-bs-target="#tab-ptk-c" type="button" role="tab">
                            <i class="fas fa-chart-line me-1 text-warning"></i> 3. Form C (Monitoring Progres)
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-bold py-2" id="tab-ptk-d-tab" data-bs-toggle="tab" data-bs-target="#tab-ptk-d" type="button" role="tab">
                            <i class="fas fa-check-double me-1 text-purple" style="color:#7c3aed;"></i> 4. Form D (Tinjauan Efektivitas)
                        </button>
                    </li>
                </ul>

                <form id="formPtkModal" onsubmit="return false;">
                <input type="hidden" name="kodeaudit" value="<?php echo htmlspecialchars($ka); ?>">
                <input type="hidden" name="no_risiko" id="modal_input_no_risiko" value="">

                <div class="tab-content" id="ptkTabContent">
                    <!-- Tab 1: Form A -->
                    <div class="tab-pane fade show active" id="tab-ptk-a" role="tabpanel">
                        <div class="card border-0 shadow-sm p-3 rounded-3">
                            <h6 class="text-primary fw-bold mb-3"><i class="fas fa-id-card me-2"></i>Form A — Identitas Temuan (Diisi LPM / LK2)</h6>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold text-muted">PTK No.</label>
                                    <input type="text" class="form-control" name="ptk_no" id="m_ptk_no" placeholder="PTK-01/<?php echo htmlspecialchars($ka); ?>/...">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold text-muted">Konsekuensi / Tindakan Mitigasi</label>
                                    <select class="form-select" name="konsekuensi" id="m_konsekuensi">
                                        <option value="PTK Segera">PTK Segera</option>
                                        <option value="PTK Segera + Eskalasi RTM WAJIB">PTK Segera + Eskalasi RTM WAJIB</option>
                                        <option value="Pantau saja">Pantau saja</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold text-muted">Tenggat Maks.</label>
                                    <input type="date" class="form-control" name="tenggat_maks" id="m_tenggat_maks">
                                </div>
                                <div class="col-12">
                                    <label class="form-label small fw-bold text-muted">Ringkasan Temuan</label>
                                    <textarea class="form-control" name="ringkasan_temuan" id="m_ringkasan_temuan" rows="3" placeholder="Ringkasan temuan dari LK2..."></textarea>
                                </div>
                                <div class="col-12">
                                    <label class="form-label small fw-bold text-muted">Akar Masalah Tervalidasi</label>
                                    <textarea class="form-control" name="akar_masalah" id="m_akar_masalah" rows="3" placeholder="Akar masalah dari LK2..."></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tab 2: Form B -->
                    <div class="tab-pane fade" id="tab-ptk-b" role="tabpanel">
                        <div class="card border-0 shadow-sm p-3 rounded-3">
                            <h6 class="text-dark fw-bold mb-3"><i class="fas fa-tasks me-2"></i>Form B — Rencana Tindakan & Realisasi (Diisi Unit / Auditee)</h6>
                            <div class="tindakan-table-wrapper">
                                <div class="table-responsive">
                                    <table class="tindakan-table" id="modalTblTindakan">
                                        <thead>
                                            <tr>
                                                <th style="width:35px" class="text-center">No</th>
                                                <th>Tindakan Korektif yang Direncanakan</th>
                                                <th style="width:140px">PJ</th>
                                                <th style="width:130px">Tenggat</th>
                                                <th>Realisasi & Bukti Fisik</th>
                                                <th style="width:130px">Tgl Selesai</th>
                                                <th style="width:40px"></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!-- Dynamic Rows JS -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <button type="button" class="btn-add-row mt-3" onclick="tambahBarisModal()">
                                <i class="fas fa-plus"></i> Tambah Baris Tindakan
                            </button>
                        </div>
                    </div>

                    <!-- Tab 3: Form C -->
                    <div class="tab-pane fade" id="tab-ptk-c" role="tabpanel">
                        <div class="card border-0 shadow-sm p-3 rounded-3">
                            <h6 class="text-warning fw-bold mb-3"><i class="fas fa-chart-line me-2"></i>Form C — Monitoring Progres (Diisi LPM Setiap 1-2 Bulan)</h6>
                            <div class="bulan-grid mb-3">
                                <?php
                                $bulanOpts = ['Selesai','Berjalan','Belum ada progres'];
                                for($b=1; $b<=5; $b++):
                                ?>
                                <div class="bulan-item">
                                    <label class="form-label small fw-bold text-muted">Bulan <?php echo $b; ?></label>
                                    <select class="form-select form-select-sm" name="bulan<?php echo $b; ?>_status" id="m_bulan<?php echo $b; ?>_status">
                                        <option value="">— Pilih —</option>
                                        <?php foreach($bulanOpts as $bo): ?>
                                        <option value="<?php echo $bo; ?>"><?php echo $bo; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <?php endfor; ?>
                            </div>
                            <div class="row g-3">
                                <div class="col-md-8">
                                    <label class="form-label small fw-bold text-muted">Catatan Perkembangan LPM</label>
                                    <textarea class="form-control" name="catatan_lpm" id="m_catatan_lpm" rows="2" placeholder="Catatan perkembangan pemantauan dari LPM..."></textarea>
                                </div>
                                <div class="col-md-4 d-flex align-items-center">
                                    <div class="form-check mt-3">
                                        <input class="form-check-input" type="checkbox" name="eskalasi_rtm" id="m_eskalasi_rtm" style="transform: scale(1.1); margin-right: 0.5rem;">
                                        <label class="form-check-label fw-bold text-danger" for="m_eskalasi_rtm" style="font-size:0.875rem; cursor:pointer;">
                                            <i class="fas fa-exclamation-triangle me-1"></i> Eskalasi ke RTM
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tab 4: Form D -->
                    <div class="tab-pane fade" id="tab-ptk-d" role="tabpanel">
                        <div class="card border-0 shadow-sm p-3 rounded-3" style="border: 2px solid #7c3aed !important;">
                            <h6 class="fw-bold mb-3" style="color:#7c3aed;"><i class="fas fa-check-double me-2"></i>Form D — Tinjauan Efektivitas (Diisi Auditor)</h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted">Status Efektivitas Verifikasi</label>
                                    <select class="form-select" name="efektivitas" id="m_efektivitas">
                                        <option value="">— Pilih Status —</option>
                                        <option value="EFEKTIF">EFEKTIF — kondisi nyata berubah, PTK dapat ditutup</option>
                                        <option value="BELUM EFEKTIF">BELUM EFEKTIF — tindakan ada tapi kondisi belum berubah, perpanjang</option>
                                        <option value="DIPERPANJANG">DIPERPANJANG — beri tenggat baru</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted">Tenggat Baru (Jika Diperpanjang)</label>
                                    <input type="date" class="form-control" name="tenggat_baru" id="m_tenggat_baru">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted">Ref. LK Verifikasi</label>
                                    <input type="text" class="form-control" name="ref_lk_verifikasi" id="m_ref_lk_verifikasi" placeholder="Contoh: LK-AMI-03/2026...">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted">Nama Auditor Verifikasi</label>
                                    <input type="text" class="form-control" name="auditor_verifikasi" id="m_auditor_verifikasi" placeholder="Nama lengkap auditor yang memverifikasi...">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                </form>
            </div>
            <div class="modal-footer bg-white border-top py-3 d-flex justify-content-between align-items-center">
                <button type="button" class="btn btn-secondary px-4 rounded-pill" data-bs-dismiss="modal">Tutup</button>
                <div class="d-flex align-items-center gap-2">
                    <span id="ptk_save_status" class="save-status" style="display:none;"></span>
                    <button type="button" class="btn btn-primary px-4 rounded-pill shadow-sm" id="btnSavePtkModal" onclick="savePtkModal()">
                        <i class="fas fa-save me-1"></i> Simpan Form PTK
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
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
</script>

<script>
const ptkDataMap = <?php echo json_encode($ptkMap, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?: '{}'; ?>;
const tindakanDataMap = <?php echo json_encode($tindakanMap, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?: '{}'; ?>;
const risksDataMap = <?php 
    $rMap = [];
    foreach($risks as $rItem) {
        $rMap[$rItem['no_risiko']] = $rItem;
    }
    echo json_encode($rMap, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?: '{}';
?>;

let currentModalNoRisiko = '';


function openPtkModal(noRisiko) {
    currentModalNoRisiko = noRisiko;
    const rData = risksDataMap[noRisiko] || {};
    const pData = ptkDataMap[noRisiko] || {};
    const ptkId = pData.id || null;
    const tList = ptkId ? (tindakanDataMap[ptkId] || []) : [];

    document.getElementById('modal_ptk_no_risiko').value = noRisiko;
    document.getElementById('modal_input_no_risiko').value = noRisiko;
    document.getElementById('modalPtkTitle').innerHTML = '<i class="fas fa-file-medical-alt me-2"></i> Permintaan Tindakan Korektif ' + noRisiko;
    document.getElementById('modalPtkBadge').innerText = noRisiko;
    document.getElementById('modalPtkStatement').innerText = rData.pernyataan_risiko || '';

    // Fill Form A
    document.getElementById('m_ptk_no').value = pData.ptk_no || ('PTK-01/<?php echo htmlspecialchars($ka); ?>/' + noRisiko);
    document.getElementById('m_konsekuensi').value = pData.konsekuensi || 'PTK Segera';
    document.getElementById('m_tenggat_maks').value = pData.tenggat_maks || '';
    document.getElementById('m_ringkasan_temuan').value = pData.ringkasan_temuan || rData.risiko_potensial || '';
    document.getElementById('m_akar_masalah').value = pData.akar_masalah || rData.akar_penyebab || '';

    // Fill Form B (Tindakan Table)
    renderModalTindakanRows(tList);

    // Fill Form C
    for (let b = 1; b <= 5; b++) {
        document.getElementById('m_bulan' + b + '_status').value = pData['bulan' + b + '_status'] || '';
    }
    document.getElementById('m_catatan_lpm').value = pData.catatan_lpm || '';
    document.getElementById('m_eskalasi_rtm').checked = parseInt(pData.eskalasi_rtm || 0) === 1;

    // Fill Form D
    document.getElementById('m_efektivitas').value = pData.efektivitas || '';
    document.getElementById('m_tenggat_baru').value = pData.tenggat_baru || '';
    document.getElementById('m_ref_lk_verifikasi').value = pData.ref_lk_verifikasi || '';
    document.getElementById('m_auditor_verifikasi').value = pData.auditor_verifikasi || '<?php echo htmlspecialchars($_SESSION['nama'] ?? ''); ?>';

    // Reset to first tab
    const firstTabEl = document.querySelector('#ptkTab button[data-bs-target="#tab-ptk-a"]');
    if (firstTabEl) {
        const tab = new bootstrap.Tab(firstTabEl);
        tab.show();
    }

    const modalEl = document.getElementById('modalEditPTKA');
    const modal = new bootstrap.Modal(modalEl);
    modal.show();
}

function renderModalTindakanRows(tList) {
    const tbody = document.querySelector('#modalTblTindakan tbody');
    tbody.innerHTML = '';
    const list = (tList && tList.length > 0) ? tList : [{tindakan:'', pj:'', tenggat:'', realisasi_bukti:'', tgl_selesai:''}];

    list.forEach((td, idx) => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td class="text-center text-muted fw-bold">${idx + 1}</td>
            <td><textarea name="tindakan[]" rows="2" class="form-control form-control-sm" placeholder="Uraian tindakan korektif...">${escapeHtml(td.tindakan || '')}</textarea></td>
            <td><input type="text" name="pj[]" class="form-control form-control-sm" placeholder="PJ" value="${escapeHtml(td.pj || '')}"></td>
            <td><input type="date" name="tenggat[]" class="form-control form-control-sm" value="${escapeHtml(td.tenggat || '')}"></td>
            <td><textarea name="realisasi_bukti[]" rows="2" class="form-control form-control-sm" placeholder="Realisasi & bukti...">${escapeHtml(td.realisasi_bukti || '')}</textarea></td>
            <td><input type="date" name="tgl_selesai[]" class="form-control form-control-sm" value="${escapeHtml(td.tgl_selesai || '')}"></td>
            <td class="text-center"><button type="button" class="btn-del-row" onclick="hapusBarisModal(this)" title="Hapus baris"><i class="fas fa-times"></i></button></td>
        `;
        tbody.appendChild(tr);
    });
}

function tambahBarisModal() {
    const tbody = document.querySelector('#modalTblTindakan tbody');
    const rowCount = tbody.querySelectorAll('tr').length + 1;
    const tr = document.createElement('tr');
    tr.innerHTML = `
        <td class="text-center text-muted fw-bold">${rowCount}</td>
        <td><textarea name="tindakan[]" rows="2" class="form-control form-control-sm" placeholder="Uraian tindakan korektif..."></textarea></td>
        <td><input type="text" name="pj[]" class="form-control form-control-sm" placeholder="PJ"></td>
        <td><input type="date" name="tenggat[]" class="form-control form-control-sm"></td>
        <td><textarea name="realisasi_bukti[]" rows="2" class="form-control form-control-sm" placeholder="Realisasi & bukti..."></textarea></td>
        <td><input type="date" name="tgl_selesai[]" class="form-control form-control-sm"></td>
        <td class="text-center"><button type="button" class="btn-del-row" onclick="hapusBarisModal(this)" title="Hapus baris"><i class="fas fa-times"></i></button></td>
    `;
    tbody.appendChild(tr);
    renumberModalRows();
}

function hapusBarisModal(btn) {
    btn.closest('tr').remove();
    renumberModalRows();
}

function renumberModalRows() {
    const rows = document.querySelectorAll('#modalTblTindakan tbody tr');
    rows.forEach((tr, i) => { tr.cells[0].textContent = i + 1; });
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

function savePtkModal() {
    const noRisiko = currentModalNoRisiko;
    if (!noRisiko) return;

    const btn = document.getElementById('btnSavePtkModal');
    const statusEl = document.getElementById('ptk_save_status');

    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Menyimpan...';
    statusEl.style.display = 'none';

    const form = document.getElementById('formPtkModal');
    const formData = new FormData(form);
    formData.append('aksi', 'simpan_ptk_all');

    fetch('audit.ptka.save.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.ok) {
            statusEl.style.display = 'inline-flex';
            statusEl.className = 'save-status ok';
            statusEl.innerHTML = '<i class="fas fa-check-circle"></i> Berhasil disimpan!';

            // Update local memory maps
            if (!ptkDataMap[noRisiko]) ptkDataMap[noRisiko] = {};
            ptkDataMap[noRisiko].id = data.ptk_id;
            ptkDataMap[noRisiko].ptk_no = data.ptkNo;
            ptkDataMap[noRisiko].ringkasan_temuan = formData.get('ringkasan_temuan');
            ptkDataMap[noRisiko].akar_masalah = formData.get('akar_masalah');
            ptkDataMap[noRisiko].bulan1_status = formData.get('bulan1_status');
            ptkDataMap[noRisiko].bulan2_status = formData.get('bulan2_status');
            ptkDataMap[noRisiko].bulan3_status = formData.get('bulan3_status');
            ptkDataMap[noRisiko].bulan4_status = formData.get('bulan4_status');
            ptkDataMap[noRisiko].bulan5_status = formData.get('bulan5_status');
            ptkDataMap[noRisiko].catatan_lpm = formData.get('catatan_lpm');
            ptkDataMap[noRisiko].eskalasi_rtm = formData.get('eskalasi_rtm') ? 1 : 0;
            ptkDataMap[noRisiko].efektivitas = data.efektivitas;

            // Update row monitoring badges in table
            const row = document.getElementById('row_' + noRisiko);
            if (row) {
                // Form A
                const colA = row.querySelector('.col-form-a');
                if (colA) {
                    if (data.hasA) {
                        colA.innerHTML = `<span class="badge bg-success mb-1"><i class="fas fa-check-circle me-1"></i>Terisi</span><br><span class="badge bg-light text-dark border">${escapeHtml(data.ptkNo || 'Form A')}</span>`;
                    } else {
                        colA.innerHTML = `<span class="badge bg-secondary"><i class="fas fa-times-circle me-1"></i>Belum</span>`;
                    }
                }
                // Form B
                const colB = row.querySelector('.col-form-b');
                if (colB) {
                    if (data.hasB) {
                        colB.innerHTML = `<span class="badge bg-success mb-1"><i class="fas fa-check-circle me-1"></i>Terisi</span><br><span class="badge bg-light text-dark border">${data.countB} Tindakan</span>`;
                    } else {
                        colB.innerHTML = `<span class="badge bg-secondary"><i class="fas fa-times-circle me-1"></i>Belum</span>`;
                    }
                }
                // Form C
                const colC = row.querySelector('.col-form-c');
                if (colC) {
                    if (data.hasC) {
                        colC.innerHTML = `<span class="badge bg-success mb-1"><i class="fas fa-check-circle me-1"></i>Terisi</span><br><span class="badge bg-light text-dark border">${escapeHtml(data.lastC || 'Aktif')}</span>`;
                    } else {
                        colC.innerHTML = `<span class="badge bg-secondary"><i class="fas fa-times-circle me-1"></i>Belum</span>`;
                    }
                }
                // Form D
                const colD = row.querySelector('.col-form-d');
                if (colD) {
                    if (data.hasD) {
                        const efBg = data.efektivitas === 'EFEKTIF' ? 'bg-success' : (data.efektivitas === 'BELUM EFEKTIF' ? 'bg-warning text-dark' : 'bg-primary');
                        colD.innerHTML = `<span class="badge ${efBg} mb-1">${escapeHtml(data.efektivitas)}</span>`;
                    } else {
                        colD.innerHTML = `<span class="badge bg-secondary"><i class="fas fa-times-circle me-1"></i>Belum</span>`;
                    }
                }
            }

            setTimeout(() => {
                statusEl.style.display = 'none';
                const modalEl = document.getElementById('modalEditPTKA');
                const modal = bootstrap.Modal.getInstance(modalEl);
                if (modal) modal.hide();
            }, 1000);
        } else {
            statusEl.style.display = 'inline-flex';
            statusEl.className = 'save-status err';
            statusEl.innerHTML = '<i class="fas fa-times-circle"></i> Gagal: ' + (data.msg || 'Terjadi kesalahan');
        }
    })
    .catch(err => {
        statusEl.style.display = 'inline-flex';
        statusEl.className = 'save-status err';
        statusEl.innerHTML = '<i class="fas fa-times-circle"></i> Error jaringan';
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-save me-1"></i> Simpan Form PTK';
    });
}
</script>

<?php include 'foot.php'; ?>
