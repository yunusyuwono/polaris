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
        include "audit.konpositif.proses.php";
        ?>
        <section class="row">
            <div class="col-12">
                <div class="audit-toolbar mb-3">
                    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center gap-3">
                        <div>
                            <h4 class="audit-toolbar-title">Kondisi Positif Audit #<?= audit_ui_plain($cto['kodeaudit']); ?></h4>
                            <p class="audit-toolbar-meta">Catat praktik baik yang perlu dipertahankan oleh unit auditi.</p>
                        </div>
                        <div class="audit-actions">
                            <a class="btn btn-outline-primary btn-sm" href="audit.progres?ka=<?= urlencode($_GET['ka']); ?>&et=<?= urlencode($_GET['et']); ?>"><i class="fas fa-caret-left me-1"></i> Kembali</a>
                            <a class="btn btn-primary btn-sm" data-bs-toggle="collapse" href="#tambah" role="button" aria-expanded="false" aria-controls="tambah"><i class="fas fa-plus me-1"></i> Tambah Kondisi</a>
                        </div>
                    </div>
                </div>

                <div class="collapse" id="tambah">
                    <div class="audit-panel mb-3">
                        <div class="audit-panel-header"><h5>Form Kondisi Positif</h5></div>
                        <div class="audit-panel-body">
                            <form class="audit-form mt-0" method="post">
                                <input type="hidden" name="idaudit" value="<?= audit_ui_plain($cto['id']); ?>">
                                <input type="hidden" name="kodeaudit" value="<?= audit_ui_plain($cto['kodeaudit']); ?>">
                                <div class="form-group">
                                    <label>Kondisi</label>
                                    <textarea name="kondisi" class="form-control" required></textarea>
                                </div>
                                <button class="btn btn-primary btn-sm" name="simpan"><i class="fas fa-paper-plane me-1"></i> Simpan</button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="audit-panel">
                    <div class="audit-panel-header"><h5>Daftar Kondisi Positif</h5></div>
                    <div class="audit-panel-body">
                        <div class="audit-list">
                            <?php
                            $kond = mysqli_query($kon, "select * from audit_kondisi where idaudit='$cto[id]' and jeniskondisi='Positif' order by id asc");
                            $no = 1;
                            if($kond && mysqli_num_rows($kond) > 0):
                                while ($k = mysqli_fetch_array($kond)):
                            ?>
                                    <article class="audit-item">
                                        <div class="audit-item-head">
                                            <div>
                                                <div class="audit-item-code">#<?= $no; ?> / <?= audit_ui_plain($k['kodekondisi']); ?></div>
                                                <h5 class="mb-1">Kondisi Positif</h5>
                                                <div class="audit-meta-row"><span>Kode audit: <?= audit_ui_plain($k['kodeaudit']); ?></span></div>
                                            </div>
                                            <div class="audit-actions">
                                                <a class="btn btn-danger btn-sm" href="audit.kondisi.hapus?ik=<?= urlencode($k['kodekondisi']); ?>&ka=<?= urlencode($k['kodeaudit']); ?>&et=<?= urlencode($_GET['et']); ?>" onclick="return confirm('Hapus kondisi positif ini?')" title="Hapus Kondisi"><i class="fas fa-trash"></i></a>
                                            </div>
                                        </div>
                                        <div class="audit-soft-card"><h6>Kondisi</h6><div class="content"><?= audit_ui_text($k['kondisi']); ?></div></div>
                                    </article>
                            <?php
                                    $no++;
                                endwhile;
                            else:
                            ?>
                                <div class="audit-empty">Belum ada kondisi positif.</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>
<?php include 'foot.php'; ?>
