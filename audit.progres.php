<?php
include 'nav.php';
include "audit.validasi.php";

$ka = htmlspecialchars($cto['kodeaudit'] ?? ($_GET['ka'] ?? ''));
$et = htmlspecialchars($_GET['et'] ?? '');
$steps = [
    ['href' => 'audit.catatan', 'icon' => 'bi bi-journal-text', 'title' => 'Catatan Audit', 'desc' => 'Telaah referensi, kesesuaian, dan catatan khusus.'],
    ['href' => 'audit.konpositif', 'icon' => 'bi bi-patch-check', 'title' => 'Kondisi Positif', 'desc' => 'Catat praktik baik dan kekuatan auditi.'],
    ['href' => 'audit.kondisi', 'icon' => 'bi bi-exclamation-diamond', 'title' => 'Temuan Audit', 'desc' => 'Kelola temuan, akar masalah, rekomendasi, dan tindak lanjut.'],
    ['href' => 'audit.dok', 'icon' => 'bi bi-images', 'title' => 'Dokumentasi Audit', 'desc' => 'Upload foto kegiatan dan daftar hadir.'],
    ['href' => 'audit.catatanpersiapan', 'icon' => 'bi bi-file-earmark-medical', 'title' => 'Catatan Persiapan (CAD-AMI)', 'desc' => 'Tinjauan dokumen desk evaluation, rekap risiko, telaah butir & rencana audit lapangan.'],
    ['href' => 'audit.lk2.input', 'icon' => 'bi bi-pencil-square', 'title' => 'Input LK2 Digital', 'desc' => 'Form isian LK2 digital untuk auditor — PLOR, akar penyebab, risiko potensial, skor, rekomendasi.'],
    ['href' => 'audit.triangulasi', 'icon' => 'bi bi-file-earmark-ruled', 'title' => 'Form Triangulasi', 'desc' => 'Unggah berkas Form Triangulasi.'],
    ['href' => 'audit.ptka', 'icon' => 'bi bi-file-earmark-excel', 'title' => 'Isian PTK A', 'desc' => 'Unggah berkas Isian PTK A.'],
    ['href' => 'audit.laporan?v=1', 'icon' => 'bi bi-file-earmark-text', 'title' => 'V1: Laporan AMI Standar', 'desc' => 'Laporan hasil AMI konvensional berdasarkan Checklist, Kondisi Positif, Temuan (Tahap 1-3) & Dokumentasi (Tahap 4).'],
    ['href' => 'audit.laporan?v=2', 'icon' => 'bi bi-shield-check', 'title' => 'V2: Laporan Berbasis Risiko', 'desc' => 'Laporan AMI Berbasis Manajemen Risiko komprehensif dari Catatan Persiapan, LK2 Digital, Triangulasi, PTK A (Tahap 5-8) & Dokumentasi (Tahap 4).'],
    ['href' => 'audit.beritaacara', 'icon' => 'bi bi-file-earmark-check', 'title' => 'Berita Acara Audit', 'desc' => 'Dokumen berita acara pelaksanaan audit.'],
    ['href' => 'audit.upload', 'icon' => 'bi bi-cloud-upload', 'title' => 'Upload Laporan Audit', 'desc' => 'Unggah file final Laporan AMI dalam PDF.'],
    ['href' => 'audit.spsa', 'icon' => 'bi bi-shield-check', 'title' => 'Upload SPSA', 'desc' => 'Unggah Surat Pernyataan Selesai Audit.'],
];
?>
<style>
    :root {
        --primary: #4f46e5;
        --primary-light: #818cf8;
        --bg-gradient: linear-gradient(135deg, #eef2ff 0%, #ffffff 100%);
        --card-bg: rgba(255, 255, 255, 0.9);
        --text-main: #1e293b;
        --text-muted: #64748b;
    }
    .audit-flow-hero {
        position: relative;
        overflow: hidden;
        background: var(--bg-gradient);
        border: 1px solid #e0e7ff;
        border-radius: 1.25rem;
        padding: 2.5rem;
        box-shadow: 0 10px 25px -5px rgba(79, 70, 229, 0.1);
        z-index: 1;
    }
    .audit-flow-hero::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -5%;
        width: 350px;
        height: 350px;
        background: radial-gradient(circle, rgba(99,102,241,0.15) 0%, rgba(255,255,255,0) 70%);
        z-index: -1;
        border-radius: 50%;
    }
    .audit-flow-kicker {
        font-size: 0.85rem;
        font-weight: 800;
        letter-spacing: 0.5px;
        background: linear-gradient(90deg, #4f46e5, #ec4899);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        text-transform: uppercase;
        margin-bottom: 0.5rem;
    }
    .audit-flow-hero h3 {
        font-weight: 900;
        color: var(--text-main);
        font-size: 2.2rem;
    }
    .audit-step-card {
        position: relative;
        height: 100%;
        background: var(--card-bg);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(226, 232, 240, 0.8);
        border-radius: 1rem;
        padding: 1.5rem;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        overflow: hidden;
        display: flex;
        flex-direction: column;
    }
    .audit-step-card::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        height: 4px;
        width: 0%;
        background: linear-gradient(90deg, #4f46e5, #818cf8);
        transition: width 0.3s ease;
    }
    .audit-step-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        border-color: #c7d2fe;
    }
    .audit-step-card:hover::after {
        width: 100%;
    }
    .audit-step-icon {
        width: 54px;
        height: 54px;
        border-radius: 0.75rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #eef2ff;
        color: #4f46e5;
        font-size: 1.6rem;
        margin-bottom: 1.2rem;
        transition: all 0.3s ease;
    }
    .audit-step-card:hover .audit-step-icon {
        transform: scale(1.1) rotate(5deg);
        background: #4f46e5;
        color: white;
        box-shadow: 0 10px 15px -3px rgba(79, 70, 229, 0.3);
    }
    .audit-step-card h5 {
        font-size: 1.1rem;
        font-weight: 800;
        color: var(--text-main);
        margin-bottom: 0.5rem;
    }
    .audit-step-card p {
        font-size: 0.9rem;
        color: var(--text-muted);
        flex-grow: 1;
        margin-bottom: 1.2rem;
        line-height: 1.5;
    }
    .btn-modern {
        background: #f8fafc;
        color: #4f46e5;
        border: 1px solid #e2e8f0;
        font-weight: 600;
        border-radius: 0.5rem;
        padding: 0.6rem 1rem;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        width: 100%;
    }
    .btn-modern i {
        transition: transform 0.2s;
    }
    .audit-step-card:hover .btn-modern {
        background: #4f46e5;
        color: white;
        border-color: #4f46e5;
    }
    .audit-step-card:hover .btn-modern i {
        transform: translateX(4px);
    }
    .step-badge {
        position: absolute;
        top: 1.2rem;
        right: 1.2rem;
        background: #f1f5f9;
        color: #64748b;
        font-size: 0.75rem;
        font-weight: 700;
        padding: 0.3rem 0.7rem;
        border-radius: 1rem;
        border: 1px solid #e2e8f0;
    }
</style>
<div id="main">
    <header>
        <a href="#" class="burger-btn d-block p-2"><i class="fas fa-bars"></i> Menu</a>
    </header>
    <div class="page-content">
        <section class="row">
            <div class="col-12 mb-3">
                <div class="audit-flow-hero">
                    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center gap-3">
                        <div>
                            <div class="audit-flow-kicker">Workflow Audit</div>
                            <h3 class="mb-1">Audit #<?= $ka; ?></h3>
                            <p class="text-muted mb-0">Pilih tahapan audit yang ingin dikerjakan atau ditinjau.</p>
                        </div>
                        <a class="btn btn-white bg-white border btn-sm shadow-sm text-dark d-flex align-items-center" href="audit" style="font-weight: 600; border-radius: 0.5rem;"><i class="fas fa-arrow-left me-2"></i> Kembali ke Daftar Audit</a>
                    </div>
                </div>
            </div>

            <?php foreach($steps as $index => $step): ?>
                <div class="col-12 col-md-6 col-xl-3 mb-4">
                    <div class="audit-step-card">
                        <span class="step-badge">Tahap <?= $index + 1; ?></span>
                        <div class="audit-step-icon"><i class="<?= $step['icon']; ?>"></i></div>
                        <h5><?= htmlspecialchars($step['title']); ?></h5>
                        <p><?= htmlspecialchars($step['desc']); ?></p>
                        <?php $sep = (strpos($step['href'], '?') !== false) ? '&' : '?'; ?>
                        <a class="btn-modern" href="<?= $step['href'] . $sep; ?>ka=<?= urlencode($ka); ?>&et=<?= $et; ?>">
                            Buka <i class="fas fa-arrow-right ms-2"></i>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </section>
    </div>
</div>
<?php include 'foot.php'; ?>
