<?php
include 'nav.php';

$idpra = isset($_GET['pra']) ? (int)$_GET['pra'] : 0;
$idsoal = isset($_GET['soal']) ? (int)$_GET['soal'] : 0;
$nomorSoal = isset($_GET['no']) ? (int)$_GET['no'] : 1;

$cekled = mysqli_query($kon,"select praaudit.*, paketpertanyaan.namapaket from praaudit join paketpertanyaan on praaudit.paket=paketpertanyaan.id where praaudit.id='$idpra' and praaudit.unit='".(int)$cs['idunit']."'");
$c = mysqli_fetch_array($cekled);

if(!$c) {
    ?>
    <div id="main">
        <header><a href="#" class="burger-btn d-block p-2"><i class="fas fa-bars"></i> Menu</a></header>
        <div class="page-content">
            <div class="alert alert-danger">Data Pra Audit tidak ditemukan untuk unit ini.</div>
        </div>
    </div>
    <?php include 'foot.php'; exit;
}

if($idsoal <= 0) {
    $first = mysqli_fetch_assoc(mysqli_query($kon,"select id from pertanyaan where idpaket='".(int)$c['paket']."' order by kriteria asc, id asc limit 1"));
    if($first) {
        $idsoal = (int)$first['id'];
        $nomorSoal = 1;
    }
}

$soalAktif = mysqli_query($kon,"select *,pertanyaan.id as idtanya from pertanyaan where pertanyaan.id='$idsoal' and pertanyaan.idpaket='".(int)$c['paket']."'");
$i = mysqli_fetch_array($soalAktif);
$k = $i && !empty($i['kriteria']) ? mysqli_fetch_array(mysqli_query($kon,"select * from kriteria where id='".(int)$i['kriteria']."'")) : null;
$cled = $i ? mysqli_fetch_array(mysqli_query($kon,"select * from led where idpertanyaan='".(int)$i['idtanya']."' and idpraaudit='$idpra'")) : null;

$allQuestions = [];
$soal = mysqli_query($kon,"select * from pertanyaan where idpaket='".(int)$c['paket']."' order by kriteria asc, id asc");
while($s = mysqli_fetch_assoc($soal)) {
    $jawaban = mysqli_fetch_assoc(mysqli_query($kon,"select jawab from led where idpertanyaan='".(int)$s['id']."' and idpraaudit='$idpra'"));
    $s['jawab_led'] = $jawaban['jawab'] ?? null;
    $allQuestions[] = $s;
}
$totalSoal = count($allQuestions);
$terjawab = 0;
foreach($allQuestions as $s) {
    if(!empty($s['jawab_led'])) {
        $terjawab++;
    }
}
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
function led_risk_label($score) {
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
    .led-work-hero,
    .led-panel {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: .75rem;
        box-shadow: 0 14px 30px rgba(15, 23, 42, 0.05);
    }
    .led-work-hero {
        padding: 1.15rem;
    }
    .led-panel .card-header {
        background: #fff;
        border-bottom: 1px solid #e5e7eb;
    }
    .led-question-text {
        font-size: 1rem;
        line-height: 1.65;
        color: #111827;
    }
    .led-meta {
        display: flex;
        flex-wrap: wrap;
        gap: .4rem;
    }
    .led-meta .badge {
        white-space: normal;
        text-align: left;
        line-height: 1.35;
    }
    .led-option-group {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: .7rem;
    }
    .led-option-group .btn {
        min-height: 48px;
        display: flex;
        align-items: center;
        justify-content: center;
        white-space: normal;
    }
    .led-question-nav {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(48px, 1fr));
        gap: .45rem;
    }
    .led-question-nav .btn {
        min-height: 42px;
        border-radius: .55rem;
        font-weight: 800;
    }
    .led-risk-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: .75rem;
        align-items: end;
    }
    .led-risk-score {
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
    @media (max-width: 767.98px) {
        .led-option-group {
            grid-template-columns: 1fr;
        }
        .led-risk-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
<div id="main">
    <header class="">
        <a href="#" class="burger-btn d-block p-2">
            <i class="fas fa-bars"></i> Menu
        </a>
    </header>
    <div class="page-content">
        <section class="row">
            <div class="col-12 mb-3">
                <div class="led-work-hero">
                    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center gap-3">
                        <div>
                            <h3 class="mb-1">Pengisian LED</h3>
                            <p class="text-muted mb-0"><?= htmlspecialchars($c['namapaket']); ?> · Tahun <?= htmlspecialchars($c['tahun']); ?></p>
                        </div>
                        <div style="min-width: 240px;">
                            <div class="d-flex justify-content-between small mb-1">
                                <span id="ledProgressText"><?= $terjawab.' / '.$totalSoal; ?> terjawab</span>
                                <span id="ledProgressPercent"><?= $percent; ?>%</span>
                            </div>
                            <div class="progress" style="height: 9px;">
                                <div id="ledProgressBar" class="progress-bar bg-info" role="progressbar" style="width: <?= $percent; ?>%;" aria-valuenow="<?= $percent; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-7 mb-3" id="ledQuestionPanel">
                <div class="card led-panel">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center gap-2">
                            <h5 class="font-semibold mb-0">Soal No. <?= $nomorSoal; ?></h5>
                            <a href="led" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i> Kembali</a>
                        </div>
                    </div>
                    <div class="card-body">
                        <?php if(!$i): ?>
                            <div class="alert alert-warning mb-0">Soal tidak ditemukan.</div>
                        <?php else: ?>
                            <div class="led-question-text"><?= $i['pertanyaan']; ?></div>

                            <?php if(!empty($i['kriteria'])): ?>
                                <hr>
                                <div class="led-meta">
                                    <span class="badge bg-dark" title="No. Indikator"><?= htmlspecialchars($i['noindikator']); ?></span>
                                    <?php if($k): ?>
                                        <span class="badge bg-success" title="Standar"><?= htmlspecialchars($k['nostandar'].'. '.$k['namastandar']); ?></span>
                                        <?php if((int)$k['nokategori'] !== 0): ?>
                                            <span class="badge bg-info text-dark" title="Sub Standar/Kategori"><?= htmlspecialchars($k['nokategori'].'. '.$k['namakategori']); ?></span>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>

                            <hr>
                            <form method="post" action="led.isi.proses" enctype="multipart/form-data" id="formLedAjax">
                                <input type="hidden" name="soal" value="<?= (int)$i['idtanya']; ?>">
                                <input type="hidden" name="no" value="<?= $nomorSoal; ?>">
                                <input type="hidden" name="praaudit" value="<?= $idpra; ?>">

                                <div class="led-option-group">
                                    <input type="radio" autofocus name="jawaban" class="btn-check" id="opsi1" <?= isset($cled['jawab']) && $cled['jawab']=='1' ? 'checked' : ''; ?> value="1" required>
                                    <label class="btn btn-outline-primary" for="opsi1"><?= htmlspecialchars($i['opsi1']); ?></label>

                                    <input type="radio" name="jawaban" class="btn-check" id="opsi2" <?= isset($cled['jawab']) && $cled['jawab']=='2' ? 'checked' : ''; ?> value="2" required>
                                    <label class="btn btn-outline-primary" for="opsi2"><?= htmlspecialchars($i['opsi2']); ?></label>
                                </div>

                                <hr>
                                <div class="form-group">
                                    <label class="form-label" for="isi_risiko">Isi Risiko</label>
                                    <textarea name="isi_risiko" id="isi_risiko" class="form-control" rows="3" required><?= htmlspecialchars($cled['isi_risiko'] ?? ''); ?></textarea>
                                </div>

                                <div class="form-group">
                                    <label class="form-label" for="isi_dampak">Isi Dampak</label>
                                    <textarea name="isi_dampak" id="isi_dampak" class="form-control" rows="3" required><?= htmlspecialchars($cled['isi_dampak'] ?? ''); ?></textarea>
                                </div>

                                <div class="led-risk-grid">
                                    <div>
                                        <label class="form-label" for="dampak">Nilai Dampak</label>
                                        <select name="dampak" id="dampak" class="form-control led-risk-input" required>
                                            <option value="">Pilih Dampak</option>
                                            <?php foreach($dampakLabels as $risk => $label): ?>
                                                <option value="<?= $risk; ?>" <?= isset($cled['dampak']) && (int)$cled['dampak'] === $risk ? 'selected' : ''; ?>><?= $risk.' - '.htmlspecialchars($label); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="form-label" for="probabilitas">Nilai Probabilitas</label>
                                        <select name="probabilitas" id="probabilitas" class="form-control led-risk-input" required>
                                            <option value="">Pilih Probabilitas</option>
                                            <?php foreach($probabilitasLabels as $risk => $label): ?>
                                                <option value="<?= $risk; ?>" <?= isset($cled['probabilitas']) && (int)$cled['probabilitas'] === $risk ? 'selected' : ''; ?>><?= $risk.' - '.htmlspecialchars($label); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="form-label">Skor Risiko</label>
                                        <div class="led-risk-score risk-empty" id="risikoPreview"><?= !empty($cled['risiko']) ? led_risk_label($cled['risiko']) : '-'; ?></div>
                                    </div>
                                </div>
                                <div class="form-text">Rumus Skor Risiko = Nilai Dampak x Nilai Probabilitas.</div>

                                <hr>
                                <div class="form-group">
                                    <label class="form-label" for="usulan_mitigasi">Usulan Mitigasi</label>
                                    <textarea name="usulan_mitigasi" id="usulan_mitigasi" class="form-control" rows="3" required><?= htmlspecialchars($cled['usulan_mitigasi'] ?? ''); ?></textarea>
                                </div>

                                <?php if((int)$i['vberkas'] === 1): ?>
                                    <hr>
                                    <label for="berkas" class="form-label">Link Dokumen Pendukung</label>
                                    <input type="text" name="berkas" class="form-control" id="berkas" placeholder="Tempel link dokumen pendukung">
                                    <?php
                                    $cb = $cled ? mysqli_fetch_array(mysqli_query($kon,"select * from berkaspertanyaan where idpertanyaan='".(int)$cled['id']."'")) : null;
                                    if(!empty($cb['berkas'])):
                                    ?>
                                        <div class="alert alert-light border mt-3 mb-0">
                                            <div class="fw-bold mb-1">Link dokumen saat ini</div>
                                            <pre class="mb-2"><?= htmlspecialchars($cb['berkas']); ?></pre>
                                            <a class="btn btn-danger btn-sm" href="led.isi.dokumen.hapus?&idtanya=<?= (int)$cb['idpertanyaan']; ?>&pra=<?= $idpra; ?>&soal=<?= $idsoal; ?>&no=<?= $nomorSoal; ?>"><i class="fas fa-trash me-1"></i> Hapus Dokumen</a>
                                        </div>
                                    <?php endif; ?>
                                <?php endif; ?>

                                <hr>
                                <button class="btn btn-primary btn-sm w-100" name="kirim" id="btnKirimLed"><i class="fas fa-paper-plane me-1"></i> Kirim Jawaban</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-md-5 mb-3" id="ledNavPanel">
                <div class="card led-panel">
                    <div class="card-header">
                        <h5 class="font-semibold mb-0">Navigasi Soal</h5>
                    </div>
                    <div class="card-body">
                        <div class="led-question-nav">
                            <?php foreach($allQuestions as $index => $s): ?>
                                <?php
                                $number = $index + 1;
                                $isActive = (int)$s['id'] === $idsoal;
                                $isAnswered = !empty($s['jawab_led']);
                                $btn = $isActive ? 'btn-warning text-dark' : ($isAnswered ? 'btn-primary' : 'btn-light');
                                $ops = $isAnswered ? ($s['jawab_led'] == 1 ? 'A' : 'B') : '';
                                ?>
                                <a class="btn btn-sm <?= $btn; ?>" data-question-id="<?= (int)$s['id']; ?>" data-number="<?= $number; ?>" href="led.isi?pra=<?= $idpra; ?>&soal=<?= (int)$s['id']; ?>&no=<?= $number; ?>"><?= $number.' '.$ops; ?></a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="card-footer bg-white">
                        <a class="btn btn-sm btn-success w-100"
                           onclick="return confirm('Pastikan Lembar Evaluasi Diri telah diisi dengan lengkap')"
                           href="led.isi.selesai?pra=<?= $idpra; ?>"><i class="fas fa-check me-1"></i> Selesai</a>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var progressText = document.getElementById('ledProgressText');
    var progressPercent = document.getElementById('ledProgressPercent');
    var progressBar = document.getElementById('ledProgressBar');

    function showMessage(icon, title) {
        if (window.Swal) {
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: icon,
                title: title,
                showConfirmButton: false,
                timer: 2200,
                timerProgressBar: true
            });
            return;
        }
        alert(title);
    }

    function bindLedForm() {
        var form = document.getElementById('formLedAjax');
        if (!form || form.dataset.ajaxBound === '1') return;
        form.dataset.ajaxBound = '1';
        bindRiskCalculator(form);

        form.addEventListener('submit', function (event) {
            event.preventDefault();

            var checked = form.querySelector('input[name="jawaban"]:checked');
                if (!checked) {
                showMessage('warning', 'Pilih jawaban terlebih dahulu.');
                return;
            }

            var dampak = form.querySelector('select[name="dampak"]');
            var probabilitas = form.querySelector('select[name="probabilitas"]');
            var isiRisiko = form.querySelector('textarea[name="isi_risiko"]');
            var isiDampak = form.querySelector('textarea[name="isi_dampak"]');
            var usulanMitigasi = form.querySelector('textarea[name="usulan_mitigasi"]');
            if (!isiRisiko || !isiDampak || !usulanMitigasi || !isiRisiko.value.trim() || !isiDampak.value.trim() || !usulanMitigasi.value.trim()) {
                showMessage('warning', 'Isi risiko, dampak, dan usulan mitigasi terlebih dahulu.');
                return;
            }
            if (!dampak || !probabilitas || !dampak.value || !probabilitas.value) {
                showMessage('warning', 'Isi nilai Dampak dan Probabilitas terlebih dahulu.');
                return;
            }

            var submitButton = document.getElementById('btnKirimLed');
            var formData = new FormData(form);
            var originalButton = submitButton.innerHTML;
            submitButton.disabled = true;
            submitButton.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Menyimpan...';

            fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(function (response) {
                return response.json();
            })
            .then(function (data) {
                if (!data.success) {
                    showMessage('error', data.message || 'Jawaban gagal disimpan.');
                    return;
                }

                if (progressText) {
                    progressText.textContent = data.answered + ' / ' + data.total + ' terjawab';
                }
                if (progressPercent) {
                    progressPercent.textContent = data.percent + '%';
                }
                if (progressBar) {
                    progressBar.style.width = data.percent + '%';
                    progressBar.setAttribute('aria-valuenow', data.percent);
                }

                var navButton = document.querySelector('.led-question-nav [data-question-id="' + data.soal + '"]');
                if (navButton) {
                    navButton.classList.remove('btn-light', 'btn-primary');
                    navButton.classList.add('btn-warning', 'text-dark');
                    navButton.textContent = data.no + ' ' + data.ops;
                }

                showMessage('success', data.message || 'Jawaban berhasil disimpan.');
            })
            .catch(function () {
                showMessage('error', 'Koneksi gagal saat menyimpan jawaban.');
            })
            .finally(function () {
                submitButton.disabled = false;
                submitButton.innerHTML = originalButton;
            });
        });
    }

    function bindRiskCalculator(scope) {
        var dampak = scope.querySelector('select[name="dampak"]');
        var probabilitas = scope.querySelector('select[name="probabilitas"]');
        var preview = scope.querySelector('#risikoPreview');
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
            preview.className = 'led-risk-score ' + riskClass(score);
        }

        dampak.addEventListener('change', calculateRisk);
        probabilitas.addEventListener('change', calculateRisk);
        calculateRisk();
    }

    function bindQuestionNav() {
        document.querySelectorAll('.led-question-nav a').forEach(function (link) {
            if (link.dataset.ajaxBound === '1') return;
            link.dataset.ajaxBound = '1';

            link.addEventListener('click', function (event) {
                event.preventDefault();
                loadQuestion(link.href, true);
            });
        });
    }

    function loadQuestion(url, pushHistory) {
        var questionPanel = document.getElementById('ledQuestionPanel');
        var navPanel = document.getElementById('ledNavPanel');
        if (!questionPanel || !navPanel) {
            window.location.href = url;
            return;
        }

        questionPanel.style.opacity = '0.55';
        navPanel.style.opacity = '0.55';

        fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(function (response) {
            return response.text();
        })
        .then(function (html) {
            var parser = new DOMParser();
            var doc = parser.parseFromString(html, 'text/html');
            var nextQuestion = doc.getElementById('ledQuestionPanel');
            var nextNav = doc.getElementById('ledNavPanel');

            if (!nextQuestion || !nextNav) {
                window.location.href = url;
                return;
            }

            questionPanel.innerHTML = nextQuestion.innerHTML;
            navPanel.innerHTML = nextNav.innerHTML;
            if (pushHistory) {
                window.history.pushState({}, '', url);
            }
            bindLedForm();
            bindQuestionNav();
        })
        .catch(function () {
            showMessage('error', 'Gagal memuat soal.');
        })
        .finally(function () {
            questionPanel.style.opacity = '';
            navPanel.style.opacity = '';
        });
    }

    window.addEventListener('popstate', function () {
        loadQuestion(window.location.href, false);
    });

    bindLedForm();
    bindQuestionNav();
});
</script>
<?php include 'foot.php';?>
