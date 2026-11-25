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
        <?php include "audit.validasi.php"; ?>
        <section class="row">
            <div class="col-12">
                <div class="audit-toolbar mb-3">
                    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center gap-3">
                        <div>
                            <h4 class="audit-toolbar-title">Temuan Audit #<?= audit_ui_plain($cto['kodeaudit']); ?></h4>
                            <p class="audit-toolbar-meta">Baca temuan auditor, lalu isi koreksi dan tindakan koreksi dari tombol tanggapi.</p>
                        </div>
                        <a class="btn btn-outline-primary btn-sm" href="audit.progres?ka=<?= urlencode($_GET['ka']); ?>&et=<?= urlencode($_GET['et']); ?>"><i class="fas fa-caret-left me-1"></i> Kembali</a>
                    </div>
                </div>

                <div class="audit-panel">
                    <div class="audit-panel-header"><h5>Daftar Temuan</h5></div>
                    <div class="audit-panel-body">
                        <div class="audit-list">
                            <?php
                            $kond = mysqli_query($kon, "select * from audit_kondisi where idaudit='$cto[id]' and jeniskondisi='Temuan' order by id asc");
                            $no = 1;
                            if($kond && mysqli_num_rows($kond) > 0):
                                while ($k = mysqli_fetch_array($kond)):
                            ?>
                                    <article class="audit-item">
                                        <div class="audit-item-head">
                                            <div>
                                                <div class="audit-item-code">#<?= $no; ?> / <?= audit_ui_plain($k['kodekondisi']); ?></div>
                                                <h5 class="mb-1"><?= audit_ui_text($k['kondisi']); ?></h5>
                                                <div class="audit-meta-row">
                                                    <span><?= audit_ui_status_badge($k['status']); ?></span>
                                                    <span>Kategori: <?= audit_ui_plain($k['kategori']); ?></span>
                                                    <span>Referensi: <?= audit_ui_plain($k['referensi']); ?></span>
                                                </div>
                                            </div>
                                            <div class="audit-actions">
                                                <a class="btn btn-warning btn-sm" href="audit.kondisi.tanggapi?ik=<?= urlencode($k['kodekondisi']); ?>&ka=<?= urlencode($k['kodeaudit']); ?>&et=<?= urlencode($_GET['et']); ?>" title="Tanggapi Temuan"><i class="fas fa-edit"></i></a>
                                            </div>
                                        </div>
                                        <div class="audit-grid">
                                            <div class="audit-soft-card"><h6>Deskripsi</h6><div class="content"><?= audit_ui_text($k['deskripsi']); ?></div></div>
                                            <div class="audit-soft-card"><h6>Rekomendasi</h6><div class="content"><?= audit_ui_text($k['rekomendasi']); ?></div></div>
                                            <div class="audit-soft-card"><h6>Tanggapan Auditi</h6><div class="content"><?= audit_ui_text($k['tanggapan']); ?></div></div>
                                            <div class="audit-soft-card"><h6>Koreksi</h6><div class="content"><?= audit_ui_text($k['koreksi']); ?></div></div>
                                            <div class="audit-soft-card"><h6>Tindakan Koreksi</h6><div class="content"><?= audit_ui_text($k['tindkoreksi']); ?></div></div>
                                            <div class="audit-soft-card"><h6>Jadwal Perbaikan</h6><div class="content"><?= audit_ui_date($k['jadpen1']); ?></div></div>
                                        </div>
                                    </article>
                            <?php
                                    $no++;
                                endwhile;
                            else:
                            ?>
                                <div class="audit-empty">Belum ada temuan audit.</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>
<?php include 'foot.php'; ?>
