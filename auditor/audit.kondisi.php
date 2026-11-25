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
        include "audit.kondisi.proses.php";
        ?>
        <section class="row">
            <div class="col-12">
                <div class="audit-toolbar mb-3">
                    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center gap-3">
                        <div>
                            <h4 class="audit-toolbar-title">Temuan Audit #<?= audit_ui_plain($cto['kodeaudit']); ?></h4>
                            <p class="audit-toolbar-meta">Input temuan dalam urutan ringkas: temuan, referensi, deskripsi, rekomendasi, tanggapan, kategori, dan status.</p>
                        </div>
                        <div class="audit-actions">
                            <a class="btn btn-outline-primary btn-sm" href="audit.progres?ka=<?= urlencode($_GET['ka']); ?>&et=<?= urlencode($_GET['et']); ?>"><i class="fas fa-caret-left me-1"></i> Kembali</a>
                            <a class="btn btn-primary btn-sm" data-bs-toggle="collapse" href="#tambah" role="button" aria-expanded="false" aria-controls="tambah"><i class="fas fa-plus me-1"></i> Tambah Temuan</a>
                        </div>
                    </div>
                </div>

                <div class="collapse" id="tambah">
                    <div class="audit-panel mb-3">
                        <div class="audit-panel-header"><h5>Form Temuan</h5></div>
                        <div class="audit-panel-body">
                            <form class="audit-form mt-0" method="post">
                                <input type="hidden" name="idaudit" value="<?= audit_ui_plain($cto['id']); ?>">
                                <input type="hidden" name="kodeaudit" value="<?= audit_ui_plain($cto['kodeaudit']); ?>">
                                <div class="row">
                                    <div class="col-md-7"><div class="form-group"><label>Temuan</label><textarea name="kondisi" class="form-control" required></textarea></div></div>
                                    <div class="col-md-5"><div class="form-group"><label>Referensi</label><input type="text" name="referensi" class="form-control" required></div></div>
                                    <div class="col-md-6"><div class="form-group"><label>Deskripsi</label><textarea name="deskripsi" class="form-control" required></textarea></div></div>
                                    <div class="col-md-6"><div class="form-group"><label>Rekomendasi</label><textarea name="rekomendasi" class="form-control" required></textarea></div></div>
                                    <div class="col-md-6"><div class="form-group"><label>Tanggapan Auditi</label><textarea name="tanggapan" class="form-control" required></textarea></div></div>
                                    <div class="col-md-3"><div class="form-group"><label>Kategori Temuan</label><select name="kategori" class="form-control" required><option value="KTS Major">KTS Major</option><option value="KTS Minor">KTS Minor</option><option value="OB">OB</option></select></div></div>
                                    <div class="col-md-3"><div class="form-group"><label>Status</label><select name="status" class="form-control" required><option value="Open">Open</option><option value="Close">Close</option></select></div></div>
                                </div>
                                <button class="btn btn-primary btn-sm" name="simpan"><i class="fas fa-paper-plane me-1"></i> Simpan</button>
                            </form>
                        </div>
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
                                                <a class="btn btn-success btn-sm" href="audit.kondisi.detail?ik=<?= urlencode($k['kodekondisi']); ?>&ka=<?= urlencode($k['kodeaudit']); ?>&et=<?= urlencode($_GET['et']); ?>" title="Detail Temuan"><i class="fas fa-search"></i></a>
                                                <a class="btn btn-warning btn-sm" href="audit.kondisi.edit?ik=<?= urlencode($k['kodekondisi']); ?>&ka=<?= urlencode($k['kodeaudit']); ?>&et=<?= urlencode($_GET['et']); ?>" title="Edit Temuan"><i class="fas fa-edit"></i></a>
                                                <a class="btn btn-danger btn-sm" href="audit.kondisi.hapus?ik=<?= urlencode($k['kodekondisi']); ?>&ka=<?= urlencode($k['kodeaudit']); ?>&et=<?= urlencode($_GET['et']); ?>" onclick="return confirm('Hapus temuan ini?')" title="Hapus Temuan"><i class="fas fa-trash"></i></a>
                                            </div>
                                        </div>
                                        <div class="audit-grid">
                                            <div class="audit-soft-card"><h6>Deskripsi</h6><div class="content"><?= audit_ui_text($k['deskripsi']); ?></div></div>
                                            <div class="audit-soft-card"><h6>Rekomendasi</h6><div class="content"><?= audit_ui_text($k['rekomendasi']); ?></div></div>
                                            <div class="audit-soft-card"><h6>Koreksi Auditi</h6><div class="content"><?= audit_ui_text($k['koreksi']); ?></div></div>
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
