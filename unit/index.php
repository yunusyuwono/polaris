<?php 
include 'nav.php';
?>
<div id="main">
    <header class="mb-3">
        <a href="#" class="burger-btn d-block d-xl-none">
            <i class="bi bi-justify fs-3"></i>
        </a>
    </header> 
    <div class="page-content">
        <section class="row">
            <div class="col-12 mb-4">
                <div class="card shadow-sm border-0" style="background: linear-gradient(135deg, #0f766e 0%, #0d9488 100%); color: white; border-radius: 1rem;">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                            <div>
                                <span class="badge bg-white text-dark px-3 py-2 mb-2 rounded-pill fw-bold">Portal Unit / Program Studi</span>
                                <h3 class="text-white fw-bold mb-1">Selamat Datang, <?= htmlspecialchars($_SESSION['nama'] ?? 'User'); ?></h3>
                                <p class="text-white-50 mb-0">Unit: <strong><?= htmlspecialchars($cs['namaunit'] ?? 'Unit Tidak Terdefinisi'); ?></strong></p>
                            </div>
                            <?php if ($bucus && mysqli_num_rows($bucus) > 1): ?>
                                <div>
                                    <a href="pilihunit.php" class="btn btn-light btn-sm fw-bold"><i class="bi bi-arrow-left-right me-1"></i> Ganti Unit</a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-8">
                <div class="card shadow-sm border-0 rounded-4">
                    <div class="card-header bg-white border-bottom py-3">
                        <h5 class="card-title mb-0 fw-bold"><i class="bi bi-megaphone-fill text-primary me-2"></i> Pengumuman</h5>
                    </div>
                    <div class="card-body px-4 py-4">
                        <div class="row">
                            <div class="col-md-12">
                                <?php
                                $umum = mysqli_query($kon, "SELECT * FROM pengumuman ORDER BY entri DESC LIMIT 5");
                                if ($umum && mysqli_num_rows($umum) > 0) {
                                    while ($u = mysqli_fetch_array($umum)) {
                                        ?>
                                        <div class="card border rounded-3 mb-3 shadow-none bg-light">
                                            <div class="card-body p-3">
                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <span class="badge bg-secondary"><i class="bi bi-clock me-1"></i> <?= htmlspecialchars($u['entri']); ?></span>
                                                </div>
                                                <p class="mb-0 text-dark"><?= nl2br(htmlspecialchars($u['info'])); ?></p>
                                            </div>
                                        </div>
                                        <?php
                                    }
                                } else {
                                    echo "<div class='text-muted text-center py-4'>Belum ada pengumuman terbaru.</div>";
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card shadow-sm border-0 rounded-4">
                    <div class="card-header bg-white border-bottom py-3">
                        <h5 class="card-title mb-0 fw-bold"><i class="bi bi-folder-fill text-warning me-2"></i> Berkas Dashboard</h5>
                    </div>
                    <div class="card-body px-4 py-4">
                        <div class="list-group list-group-flush">
                        <?php
                        $berkas = mysqli_query($kon, "SELECT * FROM berkasdepan WHERE status='Aktif'");
                        if ($berkas && mysqli_num_rows($berkas) > 0) {
                            while ($b = mysqli_fetch_array($berkas)) {
                                echo "<li class='list-group-item d-flex justify-content-between align-items-center px-0 py-2 border-bottom'>
                                <span class='text-dark small fw-medium'>" . htmlspecialchars($b['namaberkas']) . "</span>
                                <a href='../assets/berkasdepan/" . urlencode($b['berkas']) . "' class='btn btn-sm btn-outline-primary rounded-pill px-3' target='_blank'>
                                <i class='bi bi-download me-1'></i> Unduh</a></li>";
                            }
                        } else {
                            echo "<div class='text-muted text-center py-3'>Tidak ada berkas dashboard aktif.</div>";
                        }
                        ?>
                        </div>
                    </div>
                </div>
            </div>                
        </section>
    </div>
</div>

<?php include 'foot.php'; ?>