<?php
include 'nav.php';
include '../audit.ui.php';
?>
<div id="main">
    <header class="">
        <a href="#" class="burger-btn d-block p-2">
            <i class="fas fa-bars"></i> Menu
        </a>
    </header>
    <div class="page-content audit-page-shell">
        <?php
        include "audit.validasi.php";
        $ik = $_GET['ik'];
        $ka = $_GET['ka'];
        $gk = mysqli_fetch_array(mysqli_query($kon, "select * from audit_kondisi where kodekondisi='$ik' and kodeaudit='$ka'"));
        ?>
        <section class="row">
            <div class="col-12">
                <div class="audit-toolbar mb-3">
                    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center gap-3">
                        <div>
                            <h4 class="audit-toolbar-title">Detail Temuan #<?= audit_ui_plain($ik); ?></h4>
                            <div class="audit-meta-row mt-1">
                                <span>Kode audit: <?= audit_ui_plain($ka); ?></span>
                                <span><?= audit_ui_status_badge($gk['status'] ?? ''); ?></span>
                                <span>Kategori: <?= audit_ui_plain($gk['kategori'] ?? ''); ?></span>
                                <span>Referensi: <?= audit_ui_plain($gk['referensi'] ?? ''); ?></span>
                            </div>
                        </div>
                        <a class="btn btn-outline-primary btn-sm" href="audit.kondisi?ka=<?= urlencode($_GET['ka']); ?>&et=<?= urlencode($_GET['et']); ?>"><i class="fas fa-caret-left me-1"></i> Kembali</a>
                    </div>
                </div>
            </div>

            <div class="col-12 col-xl-8 mb-3">
                <div class="audit-panel">
                    <div class="audit-panel-header"><h5>Isi Temuan</h5></div>
                    <div class="audit-panel-body">
                        <div class="audit-grid">
                            <div class="audit-soft-card"><h6>Temuan</h6><div class="content"><?= audit_ui_text($gk['kondisi'] ?? ''); ?></div></div>
                            <div class="audit-soft-card"><h6>Deskripsi</h6><div class="content"><?= audit_ui_text($gk['deskripsi'] ?? ''); ?></div></div>
                            <div class="audit-soft-card"><h6>Rekomendasi</h6><div class="content"><?= audit_ui_text($gk['rekomendasi'] ?? ''); ?></div></div>
                            <div class="audit-soft-card"><h6>Tanggapan Auditi</h6><div class="content"><?= audit_ui_text($gk['tanggapan'] ?? ''); ?></div></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-xl-4 mb-3">
                <div class="audit-panel h-100">
                    <div class="audit-panel-header"><h5>Tindak Lanjut Auditi</h5></div>
                    <div class="audit-panel-body">
                        <div class="audit-list">
                            <div class="audit-soft-card"><h6>Koreksi</h6><div class="content"><?= audit_ui_text($gk['koreksi'] ?? ''); ?></div></div>
                            <div class="audit-soft-card"><h6>Tindakan Koreksi</h6><div class="content"><?= audit_ui_text($gk['tindkoreksi'] ?? ''); ?></div></div>
                            <div class="audit-soft-card"><h6>Jadwal Perbaikan</h6><div class="content"><?= audit_ui_date($gk['jadpen1'] ?? ''); ?></div></div>
                            <?php if(!empty($gk['berkas']) && ($gk['status'] ?? '') !== 'Open'): ?>
                                <a class="btn btn-success btn-sm" href="../assets/berkas/<?= urlencode($gk['berkas']); ?>" target="_blank"><i class="fas fa-download me-1"></i> Download Berkas</a>
                            <?php endif; ?>
                            <?php if(($gk['status'] ?? '') === 'Pending'): ?>
                                <a class="btn btn-primary btn-sm" href="audit.kondisi.close?ik=<?= urlencode($ik); ?>&ka=<?= urlencode($ka); ?>&et=<?= urlencode($_GET['et']); ?>"><i class="fas fa-cog me-1"></i> Tutup Kondisi</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>
<?php include 'foot.php'; ?>
