<?php
include 'nav.php';

mysqli_query($kon, "CREATE TABLE IF NOT EXISTS `iso_jawaban` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_praaudit` INT NOT NULL,
  `id_iso` INT NOT NULL,
  `id_unit` INT NOT NULL,
  `id_isoindikator` INT NOT NULL,
  `jawaban` VARCHAR(30) NOT NULL,
  `catatan` TEXT NULL,
  `link_dokumen` TEXT NULL,
  `entri` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_iso_unit_indikator` (`id_praaudit`,`id_unit`,`id_iso`,`id_isoindikator`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$checkCol = mysqli_query($kon, "SHOW COLUMNS FROM `iso_jawaban` LIKE 'link_dokumen'");
if(mysqli_num_rows($checkCol) == 0){
    mysqli_query($kon, "ALTER TABLE `iso_jawaban` ADD `link_dokumen` TEXT NULL AFTER `catatan`");
}

$pra = isset($_GET['pra']) ? (int)$_GET['pra'] : 0;
$groupFilter = $_GET['sub'] ?? '';
$praData = mysqli_fetch_assoc(mysqli_query($kon, "SELECT praaudit.*, iso.nama_iso FROM praaudit LEFT JOIN iso ON CAST(iso.idiso AS UNSIGNED)=CAST(praaudit.paketiso AS UNSIGNED) WHERE praaudit.id='$pra' AND praaudit.unit='".(int)$cs['idunit']."' LIMIT 1"));
if(!$praData || empty($praData['paketiso'])) {
    ?>
    <div id="main"><header><a href="#" class="burger-btn d-block p-2"><i class="fas fa-bars"></i> Menu</a></header><div class="page-content"><div class="alert alert-danger">Paket ISO tidak ditemukan.</div></div></div>
    <?php include 'foot.php'; exit;
}

$answers = [];
$answerQuery = mysqli_query($kon, "SELECT * FROM iso_jawaban WHERE id_praaudit='$pra' AND id_iso='".(int)$praData['paketiso']."' AND id_unit='".(int)$cs['idunit']."'");
while($row = mysqli_fetch_assoc($answerQuery)) {
    $answers[(int)$row['id_isoindikator']] = $row;
}

$isoItems = [];
$items = mysqli_query($kon, "
    SELECT
        ii.*
    FROM iso_indikator ii
    LEFT JOIN iso_subklausul sk ON FIND_IN_SET(CAST(sk.id_subklausul AS CHAR) COLLATE utf8mb4_general_ci, REPLACE(ii.id_subklausul, ' ', '') COLLATE utf8mb4_general_ci)
    WHERE (
        CAST(ii.id_iso AS UNSIGNED)='".(int)$praData['paketiso']."'
        OR CAST(sk.id_iso AS UNSIGNED)='".(int)$praData['paketiso']."'
    ) AND ii.status='1'
    GROUP BY ii.id_isoindikator
    ORDER BY ii.id_isoindikator ASC
");
while($item = mysqli_fetch_assoc($items)) {
    $clauseBadges = [];
    $subIds = array_filter(array_unique(array_map('intval', explode(',', str_replace(' ', '', $item['id_subklausul'])))));

    if(!empty($subIds)) {
        $idList = implode(',', $subIds);
        $clauseQuery = mysqli_query($kon, "
            SELECT sk.nomor_subklausul, sk.nama_subklausul, k.nomor_klausul, k.nama_klausul
            FROM iso_subklausul sk
            LEFT JOIN iso_klausul k ON CAST(k.id_klausul AS UNSIGNED)=CAST(sk.id_klausul AS UNSIGNED)
            WHERE sk.id_subklausul IN ($idList)
            ORDER BY k.nomor_klausul ASC, sk.nomor_subklausul ASC
        ");
        while($clause = mysqli_fetch_assoc($clauseQuery)) {
            $clauseBadges[] = trim(($clause['nomor_subklausul'] ? $clause['nomor_subklausul'].'. ' : '').($clause['nama_subklausul'] ?: 'Subklausul'));
        }
    } elseif(!empty($item['id_klausul'])) {
        $clause = mysqli_fetch_assoc(mysqli_query($kon, "SELECT nomor_klausul, nama_klausul FROM iso_klausul WHERE id_klausul='".(int)$item['id_klausul']."'"));
        if($clause) {
            $clauseBadges[] = trim(($clause['nomor_klausul'] ? $clause['nomor_klausul'].'. ' : '').($clause['nama_klausul'] ?: 'Klausul'));
        }
    }

    if(empty($clauseBadges)) {
        $clauseBadges[] = 'Tanpa Klausul';
    }

    $item['clause_badges'] = $clauseBadges;
    $isoItems[] = $item;
}

$total = count($isoItems);
$done = 0;
foreach($isoItems as $item) {
    if(!empty($answers[(int)$item['id_isoindikator']]['jawaban'])) $done++;
}
$percent = $total > 0 ? min(100, round(($done / $total) * 100)) : 0;
?>
<style>
    .work-hero{background:#fff;border:1px solid #e5e7eb;border-radius:.75rem;box-shadow:0 14px 30px rgba(15,23,42,.05);padding:1.15rem}
    .work-save-state{font-size:.78rem; transition: all 0.2s;}
    .iso-radio-group{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:.5rem}
    .iso-radio-group .btn{min-height:38px;display:flex;align-items:center;justify-content:center}
    .iso-clause-badges{display:flex;flex-wrap:wrap;gap:.35rem;align-items:center}
    .iso-question-text{font-size:0.95rem;line-height:1.5;}
    .work-item{border:1px solid #e5e7eb; border-radius:.5rem; background:#fff; transition: box-shadow 0.2s;}
    .work-item:hover{box-shadow:0 4px 12px rgba(15,23,42,.08)}
</style>
<div id="main">
    <header><a href="#" class="burger-btn d-block p-2"><i class="fas fa-bars"></i> Menu</a></header>
    <div class="page-content">
        <section class="row">
            <div class="col-12 mb-3">
                <div class="work-hero">
                    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center gap-3">
                        <div>
                            <h3 class="mb-1">Pengisian ISO</h3>
                            <p class="text-muted mb-0"><?= htmlspecialchars($praData['nama_iso']); ?> &middot; <?= htmlspecialchars($cs['namaunit']); ?></p>
                        </div>
                        <div style="min-width:240px">
                            <div class="d-flex justify-content-between small mb-1"><span id="groupProgressText"><?= $done.' / '.$total; ?> terisi</span><span id="groupProgressPercent"><?= $percent; ?>%</span></div>
                            <div class="progress" style="height:9px"><div id="groupProgressBar" class="progress-bar bg-info" style="width:<?= $percent; ?>%"></div></div>
                        </div>
                        <a href="led?jenis=iso" class="btn btn-outline-secondary btn-sm">Kembali</a>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <?php if(empty($isoItems)): ?>
                    <div class="alert alert-warning">Data ISO tidak tersedia.</div>
                <?php else: ?>
                    <div class="iso-list">
                        <?php $no=1; foreach($isoItems as $item): ?>
                            <?php $ans = $answers[(int)$item['id_isoindikator']] ?? null; ?>
                            <?php
                            $answerValue = $ans['jawaban'] ?? '';
                            if($answerValue === 'Terpenuhi') $answerValue = 'Ya';
                            if($answerValue === 'Belum Terpenuhi') $answerValue = 'Tidak';
                            ?>
                            <div class="work-item mb-3 p-3 shadow-sm" data-item-id="<?= (int)$item['id_isoindikator']; ?>">
                                <div class="d-flex justify-content-between align-items-start mb-2 border-bottom pb-2">
                                    <div class="d-flex gap-2 align-items-start">
                                        <span class="badge bg-secondary mt-1"><?= $no++; ?></span>
                                        <div class="iso-clause-badges">
                                            <?php foreach(($item['clause_badges'] ?? []) as $badge): ?>
                                                <span class="badge bg-primary"><?= htmlspecialchars($badge); ?></span>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                    <div class="work-save-state <?= $ans?'text-success':'text-muted'; ?> fw-bold ms-2 text-end" style="min-width:70px;"><?= $ans?'Tersimpan':'Belum diisi'; ?></div>
                                </div>
                                <div class="iso-question-text mb-3">
                                    <?= nl2br(htmlspecialchars($item['indikator'])); ?>
                                </div>
                                <div class="row g-2 align-items-center">
                                    <div class="col-12 col-md-4 col-lg-3">
                                        <div class="iso-radio-group">
                                            <input type="radio" class="btn-check iso-status" name="iso_<?= (int)$item['id_isoindikator']; ?>" id="iso_ya_<?= (int)$item['id_isoindikator']; ?>" value="Ya" <?= $answerValue==='Ya'?'checked':''; ?>>
                                            <label class="btn btn-outline-primary btn-sm" for="iso_ya_<?= (int)$item['id_isoindikator']; ?>">Ya</label>
                                            <input type="radio" class="btn-check iso-status" name="iso_<?= (int)$item['id_isoindikator']; ?>" id="iso_tidak_<?= (int)$item['id_isoindikator']; ?>" value="Tidak" <?= $answerValue==='Tidak'?'checked':''; ?>>
                                            <label class="btn btn-outline-primary btn-sm" for="iso_tidak_<?= (int)$item['id_isoindikator']; ?>">Tidak</label>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-8 col-lg-9">
                                        <div class="row g-2">
                                            <div class="col-12 col-md-6">
                                                <input type="text" class="form-control iso-note" value="<?= htmlspecialchars($ans['catatan'] ?? ''); ?>" placeholder="Catatan (Opsional)">
                                            </div>
                                            <div class="col-12 col-md-6">
                                                <input type="url" class="form-control iso-link" value="<?= htmlspecialchars($ans['link_dokumen'] ?? ''); ?>" placeholder="Link Dokumen (Opsional)">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function(){
    var done = <?= (int)$done; ?>;
    var total = <?= (int)$total; ?>;
    function updateProgress(){
        var percent = total > 0 ? Math.min(100, Math.round((done / total) * 100)) : 0;
        document.getElementById('groupProgressText').textContent = done + ' / ' + total + ' terisi';
        document.getElementById('groupProgressPercent').textContent = percent + '%';
        document.getElementById('groupProgressBar').style.width = percent + '%';
    }
    function saveIso(item){
        var state = item.querySelector('.work-save-state');
        var checked = item.querySelector('.iso-status:checked');
        var status = checked ? checked.value : '';
        var wasSaved = state.textContent.trim() === 'Tersimpan';
        var form = new FormData();
        form.append('pra', '<?= $pra; ?>');
        form.append('iso', '<?= (int)$praData['paketiso']; ?>');
        form.append('unit', '<?= (int)$cs['idunit']; ?>');
        form.append('indikator', item.dataset.itemId);
        form.append('jawaban', status);
        form.append('catatan', item.querySelector('.iso-note').value);
        form.append('link_dokumen', item.querySelector('.iso-link').value);
        state.textContent = 'Menyimpan...';
        state.className = 'work-save-state text-info fw-bold ms-2 text-end';
        fetch('iso.isi.proses', {method:'POST', body:form, headers:{'X-Requested-With':'XMLHttpRequest'}})
            .then(function(r){return r.json();})
            .then(function(data){
                if(!data.success){ throw new Error(data.message || 'Gagal'); }
                if(status && !wasSaved) done++;
                if(!status && wasSaved) done--;
                state.textContent = status ? 'Tersimpan' : 'Belum diisi';
                state.className = status ? 'work-save-state text-success fw-bold ms-2 text-end' : 'work-save-state text-muted fw-bold ms-2 text-end';
                updateProgress();
            })
            .catch(function(){
                state.textContent = wasSaved ? 'Tersimpan' : 'Belum diisi';
                state.className = wasSaved ? 'work-save-state text-success fw-bold ms-2 text-end' : 'work-save-state text-muted fw-bold ms-2 text-end';
                if(window.Swal){ Swal.fire('Gagal','ISO gagal disimpan','error'); }
            });
    }
    document.querySelectorAll('.work-item').forEach(function(item){
        item.querySelectorAll('.iso-status').forEach(function(radio){
            radio.addEventListener('change', function(){ saveIso(item); });
        });
        item.querySelector('.iso-note').addEventListener('change', function(){ saveIso(item); });
        item.querySelector('.iso-link').addEventListener('change', function(){ saveIso(item); });
    });
});
</script>
<?php include 'foot.php'; ?>
