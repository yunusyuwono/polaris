<?php
include 'nav.php';
include 'audit.ui.php';
?>
<div id="main">
    <header class="">
        <a href="#" class="burger-btn d-block p-2">
            <i class="fas fa-bars"></i> Menu
        </a>
    </header>
    <div class="page-content audit-page-shell">
        <?php include "audit.validasi.php"; ?>
        <section class="row">
            <div class="col-12">
                <div class="audit-toolbar mb-3">
                    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center gap-3">
                        <div>
                            <h4 class="audit-toolbar-title">Catatan Audit #<?= audit_ui_plain($cto['kodeaudit']); ?></h4>
                            <p class="audit-toolbar-meta">Daftar referensi, aspek yang ditelaah, hasil telaah, dan status evaluasi.</p>
                        </div>
                        <a class="btn btn-outline-primary btn-sm" href="audit.progres?ka=<?= urlencode($_GET['ka']); ?>&et=<?= urlencode($_GET['et']); ?>">
                            <i class="fas fa-caret-left me-1"></i> Kembali
                        </a>
                    </div>
                </div>

                <div class="audit-panel">
                    <div class="audit-panel-header">
                        <h5>Hasil Catatan</h5>
                    </div>
                    <div class="audit-panel-body">
                        <div class="audit-list">
                            <?php
                            $cttn = mysqli_query($kon, "select * from audit_catatan where kodeaudit='$cto[kodeaudit]' order by id asc");
                            $no = 1;
                            if($cttn && mysqli_num_rows($cttn) > 0):
                                while ($k = mysqli_fetch_array($cttn)):
                            ?>
                                    <article class="audit-item">
                                        <div class="audit-item-head">
                                            <div>
                                                <div class="audit-item-code">#<?= $no; ?> / <?= audit_ui_plain($k['kodecatatan']); ?></div>
                                                <h5 class="mb-1"><?= audit_ui_plain($k['referensi']); ?></h5>
                                                <div class="audit-meta-row">
                                                    <span>Kode audit: <?= audit_ui_plain($k['kodeaudit']); ?></span>
                                                    <span><?= audit_ui_status_badge($k['status']); ?></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="audit-grid">
                                            <div class="audit-soft-card">
                                                <h6>Aspek yang Diaudit</h6>
                                                <div class="content"><?= audit_ui_text($k['aspek']); ?></div>
                                            </div>
                                            <div class="audit-soft-card">
                                                <h6>Hasil Telaah</h6>
                                                <div class="content"><?= audit_ui_text($k['telaah']); ?></div>
                                            </div>
                                            <div class="audit-soft-card">
                                                <h6>Catatan Khusus</h6>
                                                <div class="content"><?= audit_ui_text($k['khusus']); ?></div>
                                            </div>
                                        </div>
                                    </article>
                            <?php
                                    $no++;
                                endwhile;
                            else:
                            ?>
                                <div class="audit-empty">Belum ada catatan audit.</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>
<?php include 'foot.php'; ?>
