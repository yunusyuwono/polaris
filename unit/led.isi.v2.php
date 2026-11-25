<?php
include 'nav.php';

$idpra = isset($_GET['pra']) ? (int)$_GET['pra'] : 0;
$cekled = mysqli_query($kon, "
    SELECT praaudit.*, paketpertanyaan.namapaket
    FROM praaudit
    JOIN paketpertanyaan ON praaudit.paket=paketpertanyaan.id
    WHERE praaudit.id='$idpra' AND praaudit.unit='".(int)$cs['idunit']."'
");
$pra = mysqli_fetch_array($cekled);

if(!$pra) {
    ?>
    <div id="main">
        <header><a href="#" class="burger-btn d-block p-2"><i class="fas fa-bars"></i> Menu</a></header>
        <div class="page-content">
            <div class="alert alert-danger">Data Pra Audit tidak ditemukan untuk unit ini.</div>
        </div>
    </div>
    <?php include 'foot.php'; exit;
}

$questions = [];
$filterOptions = [];
$selectedSub = $_GET['sub'] ?? '';
$q = mysqli_query($kon, "
    SELECT
        p.*,
        p.id AS idtanya,
        k.nostandar,
        k.namastandar,
        k.nokategori,
        k.namakategori,
        l.jawab,
        l.berkas,
        l.isi_risiko,
        l.isi_dampak,
        l.dampak,
        l.probabilitas,
        l.risiko,
        l.usulan_mitigasi
    FROM pertanyaan p
    LEFT JOIN kriteria k ON k.id=p.kriteria
    LEFT JOIN led l ON l.idpertanyaan=p.id AND l.idpraaudit='$idpra'
    WHERE p.idpaket='".(int)$pra['paket']."'
    ORDER BY k.nostandar ASC, k.nokategori ASC, p.noindikator ASC, p.id ASC
");

while($row = mysqli_fetch_assoc($q)) {
    $standarKey = ($row['nostandar'] ?: 'Tanpa Standar').'|'.($row['namastandar'] ?: 'Tanpa Standar');
    $subKey = ($row['nokategori'] ?: '0').'|'.($row['namakategori'] ?: 'Tanpa Substandar');
    $filterKey = md5($standarKey.'|'.$subKey);
    if(!isset($questions[$standarKey])) {
        $questions[$standarKey] = [];
    }
    if(!isset($questions[$standarKey][$subKey])) {
        $questions[$standarKey][$subKey] = [];
        $filterOptions[] = [
            'key' => $filterKey,
            'standar_key' => $standarKey,
            'sub_key' => $subKey,
            'standar_label' => ($row['nostandar'] ? $row['nostandar'].'. ' : '').($row['namastandar'] ?: 'Tanpa Standar'),
            'sub_label' => ($row['nokategori'] && $row['nokategori'] !== '0' ? $row['nokategori'].'. ' : '').($row['namakategori'] ?: 'Tanpa Substandar'),
        ];
    }
    $questions[$standarKey][$subKey][] = $row;
}

if(empty($selectedSub) && !empty($filterOptions)) {
    $selectedSub = $filterOptions[0]['key'];
}

$selectedOption = null;
$currentIndex = 0;
foreach($filterOptions as $index => $option) {
    if($option['key'] === $selectedSub) {
        $selectedOption = $option;
        $currentIndex = $index;
        break;
    }
}
if(!$selectedOption && !empty($filterOptions)) {
    $selectedOption = $filterOptions[0];
    $selectedSub = $selectedOption['key'];
}
$prevOption = $currentIndex > 0 ? $filterOptions[$currentIndex - 1] : null;
$nextOption = $currentIndex < count($filterOptions) - 1 ? $filterOptions[$currentIndex + 1] : null;
$selectedItems = $selectedOption ? ($questions[$selectedOption['standar_key']][$selectedOption['sub_key']] ?? []) : [];
$subTotal = count($selectedItems);
$subAnswered = 0;
foreach($selectedItems as $item) {
    if(!empty($item['jawab'])) {
        $subAnswered++;
    }
}

$totalSoal = mysqli_num_rows(mysqli_query($kon, "SELECT id FROM pertanyaan WHERE idpaket='".(int)$pra['paket']."'"));
$terjawab = mysqli_num_rows(mysqli_query($kon, "SELECT id FROM led WHERE idpraaudit='$idpra'"));
$percent = $totalSoal > 0 ? min(100, round(($terjawab / $totalSoal) * 100)) : 0;
$dampakLabels = [
    1 => 'Sangat Rendah',
    2 => 'Rendah',
    3 => 'Sedang',
    4 => 'Tinggi',
    5 => 'Sangat Tinggi',
];
$probabilitasLabels = [
    1 => 'Hampir Tidak Terjadi',
    2 => 'Jarang Terjadi',
    3 => 'Mungkin Terjadi',
    4 => 'Sering Terjadi',
    5 => 'Hampir Pasti Terjadi',
];
function led_v2_risk_label($score) {
    $score = (int)$score;
    if($score <= 0) return '-';
    if($score <= 2) return $score.' - Sangat Rendah';
    if($score <= 4) return $score.' - Rendah';
    if($score <= 9) return $score.' - Sedang';
    if($score <= 14) return $score.' - Tinggi';
    return $score.' - Sangat Tinggi';
}
?>
<style>
    .led-v2-hero,
    .led-v2-section,
    .led-v2-item {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: .75rem;
        box-shadow: 0 14px 30px rgba(15, 23, 42, .05);
    }
    .led-v2-hero {
        padding: 1.15rem;
    }
    .led-v2-section {
        overflow: hidden;
        margin-bottom: 1rem;
    }
    .led-v2-section-header {
        padding: 1rem 1.15rem;
        background: #f8fafc;
        border-bottom: 1px solid #e5e7eb;
    }
    .led-v2-subheader {
        padding: .85rem 1.15rem;
        background: #ecfeff;
        border-top: 1px solid #e5e7eb;
        border-bottom: 1px solid #e5e7eb;
        font-weight: 800;
        color: #0f766e;
    }
    .led-v2-item {
        box-shadow: none;
        border-radius: .65rem;
        margin: .85rem 1rem;
        padding: 1rem;
    }
    .led-v2-question {
        color: #111827;
        line-height: 1.6;
    }
    .led-v2-options {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: .65rem;
        margin-top: .85rem;
    }
    .led-v2-options .btn {
        min-height: 44px;
        white-space: normal;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .led-save-state {
        min-height: 20px;
        font-size: .78rem;
    }
    .led-v2-risk {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: .65rem;
        align-items: end;
        margin-top: .85rem;
    }
    .led-v2-risk-score {
        min-height: 38px;
        border-radius: .55rem;
        border: 1px solid #e5e7eb;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        color: #111827;
        padding: .4rem .65rem;
        text-align: center;
    }
    .risk-empty { background: #f8fafc; color: #64748b; }
    .risk-very-low { background: #00ff00; border-color: #00cc00; color: #111827; }
    .risk-low { background: #44c7f4; border-color: #1aa8d8; color: #111827; }
    .risk-medium { background: #ffff00; border-color: #d6d600; color: #111827; }
    .risk-high { background: #ff6600; border-color: #e65c00; color: #111827; }
    .risk-very-high { background: #ff0000; border-color: #cc0000; color: #ffffff; }
    .led-v2-filter {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: .75rem;
        box-shadow: 0 14px 30px rgba(15, 23, 42, .05);
        padding: 1rem;
    }
    @media (max-width: 767.98px) {
        .led-v2-options {
            grid-template-columns: 1fr;
        }
        .led-v2-risk {
            grid-template-columns: 1fr;
        }
    }
</style>
<div id="main">
    <header class="">
        <a href="#" class="burger-btn d-block p-2"><i class="fas fa-bars"></i> Menu</a>
    </header>
    <div class="page-content">
        <section class="row">
            <div class="col-12 mb-3">
                <div class="led-v2-hero">
                    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center gap-3">
                        <div>
                            <h3 class="mb-1">Pengisian LED V2</h3>
                            <p class="text-muted mb-0"><?= htmlspecialchars($pra['namapaket']); ?> &middot; Tahun <?= htmlspecialchars($pra['tahun']); ?></p>
                        </div>
                        <div class="d-flex flex-column flex-sm-row gap-2 align-items-sm-center">
                            <div style="min-width: 240px;">
                                <div class="d-flex justify-content-between small mb-1">
                                    <span id="ledV2ProgressText"><?= $terjawab.' / '.$totalSoal; ?> terjawab</span>
                                    <span id="ledV2ProgressPercent"><?= $percent; ?>%</span>
                                </div>
                                <div class="progress" style="height: 9px;">
                                    <div id="ledV2ProgressBar" class="progress-bar bg-info" role="progressbar" style="width: <?= $percent; ?>%;" aria-valuenow="<?= $percent; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            </div>
                            <a href="led.isi?pra=<?= $idpra; ?>" class="btn btn-outline-secondary btn-sm">Versi Original</a>
                            <a href="led" class="btn btn-outline-secondary btn-sm">Kembali</a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 mb-3">
                <form class="led-v2-filter" method="get" id="ledV2FilterForm">
                    <input type="hidden" name="pra" value="<?= $idpra; ?>">
                    <div class="row g-2 align-items-end">
                        <div class="col-lg-8">
                            <label class="form-label">Pilih Substandar</label>
                            <select class="form-control" name="sub" id="subFilter">
                                <?php foreach($filterOptions as $option): ?>
                                    <option value="<?= htmlspecialchars($option['key']); ?>" <?= $option['key'] === $selectedSub ? 'selected' : ''; ?>>
                                        <?= htmlspecialchars($option['standar_label'].' - '.$option['sub_label']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-lg-4">
                            <div class="d-flex flex-wrap gap-2">
                                <?php if($prevOption): ?>
                                    <a class="btn btn-outline-secondary btn-sm" href="led.isi.v2?pra=<?= $idpra; ?>&sub=<?= htmlspecialchars($prevOption['key']); ?>"><i class="fas fa-chevron-left me-1"></i> Sebelumnya</a>
                                <?php endif; ?>
                                <?php if($nextOption): ?>
                                    <a class="btn btn-outline-primary btn-sm" href="led.isi.v2?pra=<?= $idpra; ?>&sub=<?= htmlspecialchars($nextOption['key']); ?>">Berikutnya <i class="fas fa-chevron-right ms-1"></i></a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <div class="col-12">
                <?php if(!$selectedOption): ?>
                    <div class="alert alert-warning">Tidak ada pertanyaan pada paket ini.</div>
                <?php else: ?>
                    <?php [$nostandar, $namastandar] = explode('|', $selectedOption['standar_key'], 2); ?>
                    <?php [$nokategori, $namakategori] = explode('|', $selectedOption['sub_key'], 2); ?>
                    <div class="led-v2-section">
                        <div class="led-v2-section-header">
                            <span class="badge bg-dark me-2"><?= htmlspecialchars($nostandar); ?></span>
                            <strong><?= htmlspecialchars($namastandar); ?></strong>
                            <span class="badge bg-info text-dark ms-2"><?= $subAnswered.' / '.$subTotal; ?> terjawab</span>
                        </div>

                        <div class="led-v2-subheader">
                            <?= $nokategori !== '0' ? htmlspecialchars($nokategori.'. '.$namakategori) : htmlspecialchars($namakategori); ?>
                        </div>

                            <?php foreach($selectedItems as $itemIndex => $item): ?>
                                <?php
                                $questionId = (int)$item['idtanya'];
                                $no = $itemIndex + 1;
                                ?>
                                <div class="led-v2-item" data-question-id="<?= $questionId; ?>" data-number="<?= $no; ?>">
                                    <div class="d-flex justify-content-between gap-3">
                                        <div>
                                            <span class="badge bg-light text-dark border me-1">No. <?= $no; ?></span>
                                            <?php if(!empty($item['noindikator'])): ?>
                                                <span class="badge bg-secondary"><?= htmlspecialchars($item['noindikator']); ?></span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="led-save-state text-muted" id="state<?= $questionId; ?>">
                                            <?= !empty($item['jawab']) ? 'Tersimpan' : 'Belum dijawab'; ?>
                                        </div>
                                    </div>

                                    <div class="led-v2-question mt-2"><?= $item['pertanyaan']; ?></div>

                                    <form class="led-v2-form" action="led.isi.proses" method="post">
                                        <input type="hidden" name="soal" value="<?= $questionId; ?>">
                                        <input type="hidden" name="no" value="<?= $no; ?>">
                                        <input type="hidden" name="praaudit" value="<?= $idpra; ?>">
                                        <input type="hidden" name="berkas" value="<?= htmlspecialchars($item['berkas'] ?? ''); ?>">

                                        <div class="led-v2-options">
                                            <input type="radio" class="btn-check" name="jawaban" id="opsi1_<?= $questionId; ?>" value="1" <?= $item['jawab']=='1' ? 'checked' : ''; ?> required>
                                            <label class="btn btn-outline-primary" for="opsi1_<?= $questionId; ?>"><?= htmlspecialchars($item['opsi1']); ?></label>

                                            <input type="radio" class="btn-check" name="jawaban" id="opsi2_<?= $questionId; ?>" value="2" <?= $item['jawab']=='2' ? 'checked' : ''; ?> required>
                                            <label class="btn btn-outline-primary" for="opsi2_<?= $questionId; ?>"><?= htmlspecialchars($item['opsi2']); ?></label>
                                        </div>

                                        <div class="mt-3">
                                            <label class="form-label small mb-1" for="isi_risiko_<?= $questionId; ?>">Isi Risiko</label>
                                            <textarea class="form-control form-control-sm" name="isi_risiko" id="isi_risiko_<?= $questionId; ?>" rows="2" required><?= htmlspecialchars($item['isi_risiko'] ?? ''); ?></textarea>
                                        </div>

                                        <div class="mt-2">
                                            <label class="form-label small mb-1" for="isi_dampak_<?= $questionId; ?>">Isi Dampak</label>
                                            <textarea class="form-control form-control-sm" name="isi_dampak" id="isi_dampak_<?= $questionId; ?>" rows="2" required><?= htmlspecialchars($item['isi_dampak'] ?? ''); ?></textarea>
                                        </div>

                                        <div class="led-v2-risk">
                                            <div>
                                                <label class="form-label small mb-1" for="dampak_<?= $questionId; ?>">Nilai Dampak</label>
                                                <select class="form-control form-control-sm led-risk-input" name="dampak" id="dampak_<?= $questionId; ?>" required>
                                                    <option value="">Pilih</option>
                                                    <?php foreach($dampakLabels as $risk => $label): ?>
                                                        <option value="<?= $risk; ?>" <?= isset($item['dampak']) && (int)$item['dampak'] === $risk ? 'selected' : ''; ?>><?= $risk.' - '.htmlspecialchars($label); ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div>
                                                <label class="form-label small mb-1" for="probabilitas_<?= $questionId; ?>">Nilai Probabilitas</label>
                                                <select class="form-control form-control-sm led-risk-input" name="probabilitas" id="probabilitas_<?= $questionId; ?>" required>
                                                    <option value="">Pilih</option>
                                                    <?php foreach($probabilitasLabels as $risk => $label): ?>
                                                        <option value="<?= $risk; ?>" <?= isset($item['probabilitas']) && (int)$item['probabilitas'] === $risk ? 'selected' : ''; ?>><?= $risk.' - '.htmlspecialchars($label); ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div>
                                                <label class="form-label small mb-1">Skor Risiko</label>
                                                <div class="led-v2-risk-score risk-empty"><?= !empty($item['risiko']) ? led_v2_risk_label($item['risiko']) : '-'; ?></div>
                                            </div>
                                        </div>
                                        <div class="form-text">Rumus Skor Risiko = Nilai Dampak x Nilai Probabilitas.</div>

                                        <div class="mt-2">
                                            <label class="form-label small mb-1" for="usulan_mitigasi_<?= $questionId; ?>">Usulan Mitigasi</label>
                                            <textarea class="form-control form-control-sm" name="usulan_mitigasi" id="usulan_mitigasi_<?= $questionId; ?>" rows="2" required><?= htmlspecialchars($item['usulan_mitigasi'] ?? ''); ?></textarea>
                                        </div>
                                    </form>
                                </div>
                            <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="col-12 mb-3">
                <a class="btn btn-success w-100"
                   onclick="return confirm('Pastikan Lembar Evaluasi Diri telah diisi dengan lengkap')"
                   href="led.isi.selesai?pra=<?= $idpra; ?>"><i class="fas fa-check me-1"></i> Selesai</a>
            </div>
        </section>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var progressText = document.getElementById('ledV2ProgressText');
    var progressPercent = document.getElementById('ledV2ProgressPercent');
    var progressBar = document.getElementById('ledV2ProgressBar');
    var subFilter = document.getElementById('subFilter');
    var filterForm = document.getElementById('ledV2FilterForm');
    var subAnswered = <?= (int)$subAnswered; ?>;
    var subTotal = <?= (int)$subTotal; ?>;

    function toast(icon, title) {
        if (window.Swal) {
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: icon,
                title: title,
                showConfirmButton: false,
                timer: 1800,
                timerProgressBar: true
            });
        }
    }

    function saveForm(form) {
        var item = form.closest('.led-v2-item');
        var state = item ? item.querySelector('.led-save-state') : null;
        var checked = form.querySelector('input[name="jawaban"]:checked');
        var isiRisiko = form.querySelector('textarea[name="isi_risiko"]');
        var isiDampak = form.querySelector('textarea[name="isi_dampak"]');
        var dampak = form.querySelector('select[name="dampak"]');
        var probabilitas = form.querySelector('select[name="probabilitas"]');
        var usulanMitigasi = form.querySelector('textarea[name="usulan_mitigasi"]');

        if (!checked || !isiRisiko || !isiDampak || !dampak || !probabilitas || !usulanMitigasi || !isiRisiko.value.trim() || !isiDampak.value.trim() || !dampak.value || !probabilitas.value || !usulanMitigasi.value.trim()) {
            if (state) {
                state.textContent = 'Lengkapi jawaban, risiko, dampak, skor, dan mitigasi';
                state.className = 'led-save-state text-warning';
            }
            return;
        }

        var formData = new FormData(form);

        if (state) {
            state.textContent = 'Menyimpan...';
            state.className = 'led-save-state text-info';
        }

        fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function (response) { return response.json(); })
        .then(function (data) {
            if (!data.success) {
                if (state) {
                    state.textContent = 'Gagal';
                    state.className = 'led-save-state text-danger';
                }
                toast('error', data.message || 'Jawaban gagal disimpan.');
                return;
            }

            if (progressText) progressText.textContent = data.answered + ' / ' + data.total + ' terjawab';
            if (progressPercent) progressPercent.textContent = data.percent + '%';
            if (progressBar) {
                progressBar.style.width = data.percent + '%';
                progressBar.setAttribute('aria-valuenow', data.percent);
            }
            if (state) {
                if (state.textContent.trim() !== 'Tersimpan') {
                    subAnswered = Math.min(subTotal, subAnswered + 1);
                    var badge = document.querySelector('.led-v2-section-header .badge.bg-info');
                    if (badge) {
                        badge.textContent = subAnswered + ' / ' + subTotal + ' terjawab';
                    }
                }
                state.textContent = 'Tersimpan';
                state.className = 'led-save-state text-success';
            }
        })
        .catch(function () {
            if (state) {
                state.textContent = 'Gagal';
                state.className = 'led-save-state text-danger';
            }
            toast('error', 'Koneksi gagal saat menyimpan jawaban.');
        });
    }

    document.querySelectorAll('.led-v2-form input[type="radio"]').forEach(function (radio) {
        radio.addEventListener('change', function () {
            saveForm(radio.closest('form'));
        });
    });

    document.querySelectorAll('.led-v2-form').forEach(function (form) {
        form.querySelectorAll('textarea[name="isi_risiko"], textarea[name="isi_dampak"], textarea[name="usulan_mitigasi"]').forEach(function (textarea) {
            textarea.addEventListener('change', function () {
                saveForm(form);
            });
            textarea.addEventListener('blur', function () {
                saveForm(form);
            });
        });

        var dampak = form.querySelector('select[name="dampak"]');
        var probabilitas = form.querySelector('select[name="probabilitas"]');
        var preview = form.querySelector('.led-v2-risk-score');
        if (!dampak || !probabilitas || !preview) return;

        function riskLabel(score) {
            if (score <= 0) return '-';
            if (score <= 2) return score + ' - Sangat Rendah';
            if (score <= 4) return score + ' - Rendah';
            if (score <= 9) return score + ' - Sedang';
            if (score <= 14) return score + ' - Tinggi';
            return score + ' - Sangat Tinggi';
        }

        function riskClass(score) {
            if (score <= 0) return 'risk-empty';
            if (score <= 2) return 'risk-very-low';
            if (score <= 4) return 'risk-low';
            if (score <= 9) return 'risk-medium';
            if (score <= 14) return 'risk-high';
            return 'risk-very-high';
        }

        function calculateRisk() {
            var d = parseInt(dampak.value || '0', 10);
            var p = parseInt(probabilitas.value || '0', 10);
            var score = d > 0 && p > 0 ? (d * p) : 0;
            preview.textContent = riskLabel(score);
            preview.className = 'led-v2-risk-score ' + riskClass(score);
        }

        dampak.addEventListener('change', function () {
            calculateRisk();
            saveForm(form);
        });
        probabilitas.addEventListener('change', function () {
            calculateRisk();
            saveForm(form);
        });
        calculateRisk();
    });

    if (subFilter && filterForm) {
        subFilter.addEventListener('change', function () {
            filterForm.submit();
        });
    }
});
</script>
<?php include 'foot.php'; ?>
