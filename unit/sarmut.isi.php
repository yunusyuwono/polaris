<?php
include 'nav.php';

$pra = isset($_GET['pra']) ? (int)$_GET['pra'] : 0;
$triwulan = $_GET['triwulan'] ?? 'TW1';
$sasaranFilter = $_GET['sasaran'] ?? '';
$allowedTriwulan = ['TW1', 'TW2', 'TW3', 'TW4'];
if(!in_array($triwulan, $allowedTriwulan, true)) {
    $triwulan = 'TW1';
}

$praData = mysqli_fetch_assoc(mysqli_query($kon, "SELECT praaudit.*, sarmut.title AS sarmut_title FROM praaudit LEFT JOIN sarmut ON sarmut.id=praaudit.paketsarmut WHERE praaudit.id='$pra' AND praaudit.unit='".(int)$cs['idunit']."' LIMIT 1"));
if(!$praData || empty($praData['paketsarmut'])) {
    ?>
    <div id="main"><header><a href="#" class="burger-btn d-block p-2"><i class="fas fa-bars"></i> Menu</a></header><div class="page-content"><div class="alert alert-danger">Paket Sarmut tidak ditemukan.</div></div></div>
    <?php include 'foot.php'; exit;
}

$nilaiColumn = mysqli_query($kon, "SHOW COLUMNS FROM sarmut_jawaban LIKE 'nilai'");
if($nilaiColumn && mysqli_num_rows($nilaiColumn) === 0) {
    mysqli_query($kon, "ALTER TABLE sarmut_jawaban ADD nilai VARCHAR(255) NULL AFTER idsubindikator");
}

$hasUnitMap = mysqli_query($kon, "SHOW TABLES LIKE 'sarmut_subindikator_unit'");
$useUnitMap = $hasUnitMap && mysqli_num_rows($hasUnitMap) > 0;
$unitJoin = $useUnitMap ? "LEFT JOIN sarmut_subindikator_unit su ON su.subindikator_id=sub.id" : "";
$unitWhere = $useUnitMap ? "AND (su.unit_id='".(int)$cs['idunit']."' OR su.unit_id IS NULL)" : "";

$groups = [];
$groupOptions = [];
$itemsQuery = mysqli_query($kon, "
    SELECT DISTINCT sub.id, sub.subindikator, sub.jenis, sub.satuan, sub.target, sub.baseline, si.id AS indikator_id, si.indikator, ss.id AS sasaran_id, ss.sasaran
    FROM sarmut_sasaran ss
    JOIN sarmut_indikator si ON si.sasaran_id=ss.id
    JOIN sarmut_subindikator sub ON sub.indikator_id=si.id
    $unitJoin
    WHERE ss.sarmut_id='".(int)$praData['paketsarmut']."' $unitWhere
    ORDER BY ss.id ASC, si.id ASC, sub.id ASC
");

while($item = mysqli_fetch_assoc($itemsQuery)) {
    $groupKey = (string)$item['sasaran_id'];
    if(!isset($groups[$groupKey])) {
        $groups[$groupKey] = [
            'sasaran' => $item['sasaran'],
            'indikators' => []
        ];
        $groupOptions[] = ['key' => $groupKey, 'label' => $item['sasaran']];
    }
    if(!isset($groups[$groupKey]['indikators'][$item['indikator_id']])) {
        $groups[$groupKey]['indikators'][$item['indikator_id']] = [
            'indikator' => $item['indikator'],
            'items' => []
        ];
    }
    $groups[$groupKey]['indikators'][$item['indikator_id']]['items'][] = $item;
}

if(empty($sasaranFilter) && !empty($groupOptions)) {
    $sasaranFilter = $groupOptions[0]['key'];
}
$selectedIndex = 0;
foreach($groupOptions as $idx => $opt) {
    if($opt['key'] === $sasaranFilter) {
        $selectedIndex = $idx;
        break;
    }
}
$prevOption = $selectedIndex > 0 ? $groupOptions[$selectedIndex - 1] : null;
$nextOption = $selectedIndex < count($groupOptions) - 1 ? $groupOptions[$selectedIndex + 1] : null;
$selectedGroup = $groups[$sasaranFilter] ?? null;

$answered = [];
$answeredQuery = mysqli_query($kon, "SELECT idsubindikator, nilai, triwulan FROM sarmut_jawaban WHERE idsarmut='".(int)$praData['paketsarmut']."' AND idpic='".(int)$cs['idunit']."'");
while($row = mysqli_fetch_assoc($answeredQuery)) {
    $subId = (int)$row['idsubindikator'];
    $tw = $row['triwulan'];
    if(!isset($answered[$subId])) {
        $answered[$subId] = [];
    }
    $answered[$subId][$tw] = $row['nilai'];
}

$total = 0;
$done = 0;
if($selectedGroup) {
    foreach($selectedGroup['indikators'] as $indikator) {
        foreach($indikator['items'] as $item) {
            $total += 4;
            foreach(['TW1', 'TW2', 'TW3', 'TW4'] as $tw) {
                if(isset($answered[(int)$item['id']][$tw]) && trim((string)$answered[(int)$item['id']][$tw]) !== '') {
                    $done++;
                }
            }
        }
    }
}
$percent = $total > 0 ? min(100, round(($done / $total) * 100)) : 0;
?>
<style>
    .work-hero,.work-filter,.work-section,.work-item{background:#fff;border:1px solid #e5e7eb;border-radius:.75rem;box-shadow:0 14px 30px rgba(15,23,42,.05)}
    .work-hero,.work-filter{padding:1.15rem}
    .work-section{overflow:hidden;margin-bottom:1rem}
    .work-section-header{padding:1rem 1.15rem;background:#f8fafc;border-bottom:1px solid #e5e7eb}
    .work-subheader{padding:.85rem 1.15rem;background:#ecfeff;border-top:1px solid #e5e7eb;border-bottom:1px solid #e5e7eb;font-weight:800;color:#0f766e}
    .work-item{box-shadow:none;border-radius:.65rem;margin:.85rem 1rem;padding:1rem}
    .work-save-state{min-height:20px;font-size:.78rem}
    .sarmut-value-wrap{min-width:240px;max-width:320px}
    .input-group-append{display:flex}
</style>
<div id="main">
    <header><a href="#" class="burger-btn d-block p-2"><i class="fas fa-bars"></i> Menu</a></header>
    <div class="page-content">
        <section class="row">
            <div class="col-12 mb-3">
                <div class="work-hero">
                    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center gap-3">
                        <div>
                            <h3 class="mb-1">Pengisian Sarmut</h3>
                            <p class="text-muted mb-0"><?= htmlspecialchars($praData['sarmut_title']); ?> &middot; <?= htmlspecialchars($cs['namaunit']); ?></p>
                        </div>
                        <div style="min-width:240px">
                            <div class="d-flex justify-content-between small mb-1">
                                <span id="groupProgressText"><?= $done.' / '.$total; ?> terisi</span>
                                <span id="groupProgressPercent"><?= $percent; ?>%</span>
                            </div>
                            <div class="progress" style="height:9px"><div id="groupProgressBar" class="progress-bar bg-info" style="width:<?= $percent; ?>%"></div></div>
                        </div>
                        <a href="led?jenis=sarmut" class="btn btn-outline-secondary btn-sm">Kembali</a>
                    </div>
                </div>
            </div>

            <div class="col-12 mb-3">
                <form class="work-filter" method="get" id="filterForm">
                    <input type="hidden" name="pra" value="<?= $pra; ?>">
                    <div class="row g-2 align-items-end">
                        <div class="col-md-9">
                            <label class="form-label font-semibold">Sasaran</label>
                            <select name="sasaran" class="form-control auto-filter">
                                <?php foreach($groupOptions as $option): ?><option value="<?= htmlspecialchars($option['key']); ?>" <?= $option['key']===$sasaranFilter?'selected':''; ?>><?= htmlspecialchars($option['label']); ?></option><?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3 text-md-end">
                            <div class="d-flex flex-wrap gap-2 justify-content-md-end">
                                <?php if($prevOption): ?><a class="btn btn-outline-secondary btn-sm" href="sarmut.isi?pra=<?= $pra; ?>&sasaran=<?= htmlspecialchars($prevOption['key']); ?>">Sebelumnya</a><?php endif; ?>
                                <?php if($nextOption): ?><a class="btn btn-outline-primary btn-sm" href="sarmut.isi?pra=<?= $pra; ?>&sasaran=<?= htmlspecialchars($nextOption['key']); ?>">Berikutnya</a><?php endif; ?>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <div class="col-12">
                <?php if(!$selectedGroup): ?>
                    <div class="alert alert-warning">Data Sarmut tidak tersedia.</div>
                <?php else: ?>
                    <div class="work-section">
                        <div class="work-section-header">
                            <strong><?= htmlspecialchars($selectedGroup['sasaran']); ?></strong>
                            <span class="badge bg-info text-dark ms-2" id="groupBadge"><?= $done.' / '.$total; ?> terisi</span>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover align-middle mb-0" style="border-color: #e5e7eb;">
                                <thead class="table-light" style="font-size: 0.82rem; text-transform: uppercase; letter-spacing: 0.5px;">
                                    <tr>
                                        <th style="width: 5%;" class="text-center">No</th>
                                        <th>Sub-Indikator</th>
                                        <th style="width: 15%;">Target</th>
                                        <th style="width: 13%;" class="text-center">TW1</th>
                                        <th style="width: 13%;" class="text-center">TW2</th>
                                        <th style="width: 13%;" class="text-center">TW3</th>
                                        <th style="width: 13%;" class="text-center">TW4</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $no=1; foreach($selectedGroup['indikators'] as $indikator): ?>
                                        <tr style="background:#f8fafc;">
                                            <td colspan="7" class="fw-bold text-teal py-2" style="font-size: 0.9rem; padding-left: 1rem; color: #0f766e;">
                                                <i class="fas fa-list-alt me-1"></i> <?= htmlspecialchars($indikator['indikator']); ?>
                                            </td>
                                        </tr>
                                        <?php foreach($indikator['items'] as $item): ?>
                                            <tr class="work-item-row" data-item-id="<?= (int)$item['id']; ?>">
                                                <td class="text-center fw-bold" style="color: #6b7280;"><?= $no++; ?></td>
                                                <td>
                                                    <div class="fw-semibold text-dark" style="font-size: 0.9rem;"><?= htmlspecialchars($item['subindikator']); ?></div>
                                                    <?php if(!empty($item['jenis'])): ?>
                                                        <span class="badge bg-light text-dark border mt-1" style="font-size: 0.7rem;"><?= htmlspecialchars($item['jenis']); ?></span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if(!empty($item['target'])): ?>
                                                        <span class="badge bg-info text-dark" style="font-size: 0.75rem;">
                                                            Target: <?= htmlspecialchars($item['target']); ?> <?= htmlspecialchars($item['satuan']); ?>
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="text-muted small">-</span>
                                                    <?php endif; ?>
                                                </td>
                                                <?php foreach(['TW1', 'TW2', 'TW3', 'TW4'] as $tw): 
                                                    $nilai = $answered[(int)$item['id']][$tw] ?? '';
                                                    $filled = trim((string)$nilai) !== '';
                                                ?>
                                                    <td class="p-2">
                                                        <div class="sarmut-tw-box mx-auto" style="width: 100%;">
                                                            <div class="input-group input-group-sm">
                                                                <input class="form-control sarmut-value text-center px-1" type="text" data-tw="<?= $tw; ?>" value="<?= htmlspecialchars($nilai); ?>" placeholder="-">
                                                                <div class="input-group-append">
                                                                    <span class="input-group-text px-1" style="font-size: 0.65rem; min-width: 24px; justify-content: center;"><?= htmlspecialchars($item['satuan'] ?: '%'); ?></span>
                                                                </div>
                                                            </div>
                                                            <div class="work-save-state <?= $filled?'text-success':'text-muted'; ?> text-center mt-1" style="font-size: 0.65rem; min-height: 12px; font-weight: 500;"><?= $filled?'Tersimpan':''; ?></div>
                                                        </div>
                                                    </td>
                                                <?php endforeach; ?>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function(){
    var filterForm = document.getElementById('filterForm');
    document.querySelectorAll('.auto-filter').forEach(function(el){ el.addEventListener('change', function(){ filterForm.submit(); }); });
    var done = <?= (int)$done; ?>;
    var total = <?= (int)$total; ?>;
    function updateProgress(){
        var percent = total > 0 ? Math.min(100, Math.round((done / total) * 100)) : 0;
        document.getElementById('groupProgressText').textContent = done + ' / ' + total + ' terisi';
        document.getElementById('groupProgressPercent').textContent = percent + '%';
        document.getElementById('groupProgressBar').style.width = percent + '%';
        document.getElementById('groupBadge').textContent = done + ' / ' + total + ' terisi';
    }
    var saveTimers = {};
    document.querySelectorAll('.sarmut-value').forEach(function(input){
        function saveInput(){
            var item = input.closest('.work-item-row');
            var twBox = input.closest('.sarmut-tw-box');
            var state = twBox.querySelector('.work-save-state');
            var wasSaved = state.textContent.trim() === 'Tersimpan';
            var oldValue = input.dataset.savedValue || input.defaultValue || '';
            var value = input.value.trim();
            var triwulan = input.dataset.tw;
            var timerKey = item.dataset.itemId + '_' + triwulan;
            var form = new FormData();
            form.append('pra', '<?= $pra; ?>');
            form.append('sarmut', '<?= (int)$praData['paketsarmut']; ?>');
            form.append('unit', '<?= (int)$cs['idunit']; ?>');
            form.append('triwulan', triwulan);
            form.append('subindikator', item.dataset.itemId);
            form.append('nilai', value);
            state.textContent = 'Menyimpan...';
            state.className = 'work-save-state text-info text-center mt-1';
            fetch('sarmut.isi.proses', {method:'POST', body:form, headers:{'X-Requested-With':'XMLHttpRequest'}})
                .then(function(r){return r.json();})
                .then(function(data){
                    if(!data.success){ throw new Error(data.message || 'Gagal'); }
                    if(value !== '' && !wasSaved) done++;
                    if(value === '' && wasSaved) done--;
                    input.dataset.savedValue = value;
                    input.defaultValue = value;
                    state.textContent = value !== '' ? 'Tersimpan' : '';
                    state.className = value !== '' ? 'work-save-state text-success text-center mt-1' : 'work-save-state text-muted text-center mt-1';
                    updateProgress();
                })
                .catch(function(){
                    input.value = oldValue;
                    state.textContent = wasSaved ? 'Tersimpan' : '';
                    state.className = wasSaved ? 'work-save-state text-success text-center mt-1' : 'work-save-state text-muted text-center mt-1';
                    if(window.Swal){ Swal.fire('Gagal','Sarmut gagal disimpan','error'); }
                });
        }
        input.dataset.savedValue = input.value;
        input.addEventListener('input', function(){
            var timerKey = input.closest('.work-item-row').dataset.itemId + '_' + input.dataset.tw;
            clearTimeout(saveTimers[timerKey]);
            saveTimers[timerKey] = setTimeout(saveInput, 600);
        });
        input.addEventListener('change', function() {
            var timerKey = input.closest('.work-item-row').dataset.itemId + '_' + input.dataset.tw;
            clearTimeout(saveTimers[timerKey]);
            saveInput();
        });
    });
});
</script>
<?php include 'foot.php'; ?>
