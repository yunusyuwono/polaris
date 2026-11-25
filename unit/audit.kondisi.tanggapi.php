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
        include "audit.kondisi.tanggapi.proses.php";
        $gt = mysqli_fetch_array(mysqli_query($kon, "SELECT * from audit_kondisi WHERE kodekondisi='$_GET[ik]'"));
        ?>
        <section class="row">
            <div class="col-12">
                <div class="audit-toolbar mb-3">
                    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center gap-3">
                        <div>
                            <h4 class="audit-toolbar-title">Tanggapi Temuan #<?= audit_ui_plain($_GET['ik']); ?></h4>
                            <p class="audit-toolbar-meta">Lengkapi koreksi, tindakan koreksi, dan jadwal perbaikan untuk temuan ini.</p>
                        </div>
                        <a class="btn btn-outline-primary btn-sm" href="audit.kondisi?ka=<?= urlencode($_GET['ka']); ?>&et=<?= urlencode($_GET['et']); ?>"><i class="fas fa-caret-left me-1"></i> Kembali</a>
                    </div>
                </div>
            </div>

            <div class="col-12 col-xl-7 mb-3">
                <div class="audit-panel h-100">
                    <div class="audit-panel-header"><h5>Informasi Temuan</h5></div>
                    <div class="audit-panel-body">
                        <div class="audit-list">
                            <div class="audit-soft-card"><h6>Temuan</h6><div class="content"><?= audit_ui_text($gt['kondisi']); ?></div></div>
                            <div class="audit-soft-card"><h6>Referensi</h6><div class="content"><?= audit_ui_text($gt['referensi']); ?></div></div>
                            <div class="audit-soft-card"><h6>Deskripsi</h6><div class="content"><?= audit_ui_text($gt['deskripsi']); ?></div></div>
                            <div class="audit-soft-card"><h6>Rekomendasi Auditor</h6><div class="content"><?= audit_ui_text($gt['rekomendasi']); ?></div></div>
                            <div class="audit-soft-card"><h6>Tanggapan Awal Auditi</h6><div class="content"><?= audit_ui_text($gt['tanggapan']); ?></div></div>
                            <div class="audit-meta-row">
                                <span><?= audit_ui_status_badge($gt['status']); ?></span>
                                <span>Kategori: <?= audit_ui_plain($gt['kategori']); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-xl-5 mb-3">
                <div class="audit-panel">
                    <div class="audit-panel-header"><h5>Form Tindak Lanjut Unit</h5></div>
                    <div class="audit-panel-body">
                        <form class="audit-form mt-0" method="post">
                            <input type="hidden" name="idaudit" value="<?= audit_ui_plain($gt['id']); ?>">
                            <input type="hidden" name="kodeaudit" value="<?= audit_ui_plain($gt['kodeaudit']); ?>">
                            <div class="form-group">
                                <label>Koreksi</label>
                                <textarea name="koreksi" class="form-control" required><?= audit_ui_plain($gt['koreksi']); ?></textarea>
                            </div>
                            <div class="form-group">
                                <label>Tindakan Koreksi</label>
                                <textarea name="tindkoreksi" class="form-control" required><?= audit_ui_plain($gt['tindkoreksi']); ?></textarea>
                            </div>
                            <div class="form-group">
                                <label>Jadwal Perbaikan</label>
                                <input type="date" name="jadpen1" class="form-control" required value="<?= audit_ui_plain($gt['jadpen1']); ?>">
                            </div>
                            <button class="btn btn-primary btn-sm" name="simpan"><i class="fas fa-paper-plane me-1"></i> Simpan Tanggapan</button>
                        </form>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>
<?php include 'foot.php'; ?>
