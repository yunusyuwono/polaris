<?php 
include 'nav.php';
include '../koneksi.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

$idpra = isset($_GET['pra']) ? (int)$_GET['pra'] : 0;

if($idpra == 0) {
    die("<div class='alert alert-danger'>ID Pra Audit tidak ditemukan!</div>");
}

// Ambil data praaudit
$result_pra = mysqli_query($kon, "SELECT * FROM praaudit WHERE id = '$idpra'");
$praaudit = mysqli_fetch_array($result_pra);

if(!$praaudit) {
    die("<div class='alert alert-danger'>Data Pra Audit dengan ID $idpra tidak ditemukan!</div>");
}

if(!function_exists('admin_pra_risk_meta')) {
    function admin_pra_risk_meta($score) {
        $score = (int)$score;
        if($score <= 0) return ['label' => '-', 'class' => 'risk-empty', 'level' => 'Belum Dinilai'];
        if($score <= 2) return ['label' => $score.' - Sangat Rendah', 'class' => 'risk-very-low', 'level' => 'Sangat Rendah'];
        if($score <= 4) return ['label' => $score.' - Rendah', 'class' => 'risk-low', 'level' => 'Rendah'];
        if($score <= 9) return ['label' => $score.' - Sedang', 'class' => 'risk-medium', 'level' => 'Sedang'];
        if($score <= 14) return ['label' => $score.' - Tinggi', 'class' => 'risk-high', 'level' => 'Tinggi'];
        return ['label' => $score.' - Sangat Tinggi', 'class' => 'risk-very-high', 'level' => 'Sangat Tinggi'];
    }
}

$dampakLabels = [1 => 'Sangat Rendah', 2 => 'Rendah', 3 => 'Sedang', 4 => 'Tinggi', 5 => 'Sangat Tinggi'];
$probabilitasLabels = [1 => 'Hampir Tidak Terjadi', 2 => 'Jarang Terjadi', 3 => 'Mungkin Terjadi', 4 => 'Sering Terjadi', 5 => 'Hampir Pasti Terjadi'];

// Query hasil evaluasi diri per referensi (kriteria)
// Hitung total nilai LED per referensi berdasarkan kriteria
$query = "
    SELECT 
        k.nostandar,
        k.namastandar,
        COUNT(DISTINCT p.id) AS total_pertanyaan,
        SUM(COALESCE(l.koreksi, 0)) AS total_koreksi,
        ROUND((SUM(COALESCE(l.koreksi, 0)) / NULLIF(COUNT(DISTINCT p.id), 0)) * 100, 2) AS persen_koreksi
    FROM 
        pertanyaan p
        INNER JOIN kriteria k ON p.kriteria = k.id
        LEFT JOIN led l ON l.idpertanyaan = p.id AND l.idpraaudit = '$idpra'
    WHERE 
        p.idpaket = '{$praaudit['paket']}'
        AND p.kriteria != ''
    GROUP BY 
        k.nostandar, k.namastandar
    ORDER BY 
        k.nostandar ASC
";

$result = mysqli_query($kon, $query);

if(!$result) {
    die("<div class='alert alert-danger'>Error: " . mysqli_error($kon) . "</div>");
}

$labels = [];
$data = [];
$colors = [];
$color_palette = [
    'rgba(54, 162, 235, 0.7)',
    'rgba(75, 192, 192, 0.7)',
    'rgba(255, 206, 86, 0.7)',
    'rgba(153, 102, 255, 0.7)',
    'rgba(255, 159, 64, 0.7)',
    'rgba(199, 199, 199, 0.7)',
    'rgba(83, 102, 255, 0.7)',
    'rgba(255, 99, 132, 0.7)'
];
$idx = 0;

while($row = mysqli_fetch_assoc($result)) {
    $labels[] = $row['namastandar'];
    $data[] = (float)$row['persen_koreksi'];
    $colors[] = $color_palette[$idx % count($color_palette)];
    $idx++;
}

$json_labels = json_encode($labels);
$json_data = json_encode($data);
$json_colors = json_encode($colors);

// Query pertanyaan dan jawaban untuk tabel
$questions = mysqli_query($kon, "SELECT p.id, p.noindikator, p.pertanyaan, p.opsi1, p.opsi2, p.kriteria, k.namastandar, l.jawab, l.koreksi, l.isi_risiko, l.isi_dampak, l.dampak, l.probabilitas, l.risiko, l.usulan_mitigasi FROM pertanyaan p LEFT JOIN kriteria k ON p.kriteria = k.id LEFT JOIN led l ON l.idpertanyaan = p.id AND l.idpraaudit = '$idpra' WHERE p.idpaket = '{$praaudit['paket']}' ORDER BY p.noindikator ASC");

$questionRows = [];
$riskCounts = ['Sangat Tinggi' => 0, 'Tinggi' => 0, 'Sedang' => 0, 'Rendah' => 0, 'Sangat Rendah' => 0, 'Belum Dinilai' => 0];
$riskTotal = 0;
$riskAssessed = 0;
$highestRisk = 0;
$riskScoreTotal = 0;
if($questions) {
    while($row = mysqli_fetch_assoc($questions)) {
        $questionRows[] = $row;
        $riskTotal++;
        $riskScore = (int)($row['risiko'] ?? 0);
        $riskMeta = admin_pra_risk_meta($riskScore);
        $riskCounts[$riskMeta['level']] = ($riskCounts[$riskMeta['level']] ?? 0) + 1;
        if($riskScore > 0) {
            $riskAssessed++;
            $highestRisk = max($highestRisk, $riskScore);
            $riskScoreTotal += $riskScore;
        }
    }
}
$highestRiskMeta = admin_pra_risk_meta($highestRisk);
$averageRiskScore = $riskAssessed > 0 ? round($riskScoreTotal / $riskAssessed, 2) : 0;
$averageRiskMeta = admin_pra_risk_meta((int)round($averageRiskScore));
$riskChartLabels = ['Belum Dinilai', 'Sangat Rendah', 'Rendah', 'Sedang', 'Tinggi', 'Sangat Tinggi'];
$riskChartData = [];
foreach($riskChartLabels as $riskLabel) {
    $riskChartData[] = (int)($riskCounts[$riskLabel] ?? 0);
}
$riskChartColors = [
    'rgba(148, 163, 184, 0.75)',
    'rgba(34, 197, 94, 0.75)',
    'rgba(56, 189, 248, 0.75)',
    'rgba(250, 204, 21, 0.8)',
    'rgba(249, 115, 22, 0.8)',
    'rgba(239, 68, 68, 0.8)'
];

// Query option filter referensi (group by nama referensi)
$referenceOptions = mysqli_query($kon, "SELECT DISTINCT k.namastandar FROM pertanyaan p LEFT JOIN kriteria k ON p.kriteria = k.id WHERE p.idpaket = '{$praaudit['paket']}' AND p.kriteria IS NOT NULL AND p.kriteria != '' ORDER BY k.namastandar ASC");

?>
<div id="main">
    <header class="">
        <a href="#" class="burger-btn d-block p-2">
            <i class="fas fa-bars"></i> Menu 
        </a>
    </header> 
    <div class="page-content">
        <section class="row">
            <div class="col-12 col-lg-12">
                <div class="card pra-report-card">
                    <div class="card-header pra-report-header">
                        <div>
                            <span class="pra-report-kicker">Pra Audit</span>
                            <h4 class="font-semibold mb-1">Hasil Lembar Evaluasi Diri</h4>
                            <p class="mb-0">Ringkasan penilaian LED, pemetaan risiko, dan daftar jawaban per item.</p>
                        </div>
                        <div class="pra-report-header-meta">
                            <span class="badge bg-primary">Tahun <?php echo htmlspecialchars($praaudit['tahun'] ?? '-'); ?></span>
                            <span class="badge bg-light text-dark border">Pra Audit #<?php echo (int)$idpra; ?></span>
                        </div>
                    </div>
                    <div class="card-body pra-report-body">
                        <style>
                            .pra-report-card{border:0;border-radius:1rem;overflow:hidden;box-shadow:0 18px 44px rgba(15,23,42,.08)}
                            .pra-report-header{display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;background:linear-gradient(135deg,#f8fafc 0%,#eef6ff 100%);border-bottom:1px solid #e2e8f0;padding:1.25rem 1.35rem}
                            .pra-report-header h4{color:#0f172a;font-weight:900}
                            .pra-report-header p{color:#64748b;font-size:.9rem}
                            .pra-report-kicker{display:inline-flex;margin-bottom:.45rem;color:#2563eb;font-size:.76rem;font-weight:900;text-transform:uppercase;letter-spacing:0}
                            .pra-report-header-meta{display:flex;flex-wrap:wrap;justify-content:flex-end;gap:.45rem;flex:0 0 auto}
                            .pra-report-body{background:#f8fafc;padding:1.1rem 1.1rem 1.25rem}
                            .pra-report-stats{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:1rem;margin-bottom:1rem}
                            .pra-report-stat{position:relative;overflow:hidden;background:#fff;border:1px solid #e5e7eb;border-radius:.85rem;padding:1rem;box-shadow:0 12px 28px rgba(15,23,42,.06)}
                            .pra-report-stat:before{content:"";position:absolute;inset:0 auto 0 0;width:5px;background:var(--stat-accent,#2563eb)}
                            .pra-report-stat .stat-top{display:flex;align-items:flex-start;justify-content:space-between;gap:.85rem}
                            .pra-report-stat .stat-icon{width:42px;height:42px;border-radius:.7rem;display:inline-flex;align-items:center;justify-content:center;background:var(--stat-soft,#eff6ff);color:var(--stat-accent,#2563eb);font-size:1.25rem;flex:0 0 auto}
                            .pra-report-stat .label{color:#64748b;font-size:.74rem;font-weight:800;text-transform:uppercase;letter-spacing:0}
                            .pra-report-stat .value{font-size:1.55rem;font-weight:900;color:#111827;line-height:1.1;margin-top:.35rem}
                            .pra-report-stat .hint{color:#94a3b8;font-size:.78rem;margin-top:.45rem}
                            .stat-blue{--stat-accent:#2563eb;--stat-soft:#eff6ff}
                            .stat-red{--stat-accent:#dc2626;--stat-soft:#fef2f2}
                            .stat-green{--stat-accent:#0f766e;--stat-soft:#ecfdf5}
                            .stat-orange{--stat-accent:#ea580c;--stat-soft:#fff7ed}
                            .stat-slate{--stat-accent:#64748b;--stat-soft:#f8fafc}
                            .risk-pill{display:inline-flex;align-items:center;justify-content:center;border:1px solid transparent;border-radius:.45rem;padding:.35rem .55rem;font-weight:800;min-width:112px;text-align:center}
                            .risk-empty{background:#f8fafc;color:#64748b;border-color:#e5e7eb}
                            .risk-very-low{background:#00ff00;color:#111827;border-color:#00cc00}
                            .risk-low{background:#44c7f4;color:#111827;border-color:#1aa8d8}
                            .risk-medium{background:#ffff00;color:#111827;border-color:#d6d600}
                            .risk-high{background:#ff6600;color:#111827;border-color:#e65c00}
                            .risk-very-high{background:#ff0000;color:#fff;border-color:#cc0000}
                            .pra-chart-grid{display:grid;grid-template-columns:1fr;gap:1rem;margin-bottom:30px}
                            .pra-chart-card{width:100%;border:1px solid #e5e7eb;border-radius:.85rem;padding:1rem;background:#fff;box-shadow:0 12px 28px rgba(15,23,42,.06)}
                            .pra-chart-head{display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;margin-bottom:.9rem}
                            .pra-chart-head h5{font-size:1rem;font-weight:900;margin:0;color:#0f172a}
                            .pra-chart-head p{font-size:.82rem;color:#64748b;margin:.2rem 0 0}
                            .pra-chart-badge{display:inline-flex;align-items:center;gap:.35rem;border:1px solid #e2e8f0;border-radius:999px;padding:.25rem .55rem;background:#f8fafc;color:#475569;font-size:.75rem;font-weight:800;white-space:nowrap}
                            .pra-chart-canvas{position:relative;height:450px}
                            .pra-table-card{background:#fff;border:1px solid #e5e7eb;border-radius:.85rem;padding:1rem;box-shadow:0 12px 28px rgba(15,23,42,.06)}
                            .pra-table-head{display:flex;align-items:flex-end;justify-content:space-between;gap:1rem;margin-bottom:1rem}
                            .pra-table-head h5{font-weight:900;color:#0f172a;margin:0}
                            .pra-table-head p{font-size:.84rem;color:#64748b;margin:.2rem 0 0}
                            .pra-table-filter{min-width:260px;max-width:380px}
                            #tableQuestions{border-collapse:separate!important;border-spacing:0 .65rem!important}
                            #tableQuestions thead th{background:#f1f5f9;color:#334155;border:0!important;font-size:.76rem;text-transform:uppercase;letter-spacing:0;font-weight:900;white-space:nowrap}
                            #tableQuestions tbody tr{background:#fff;box-shadow:0 8px 18px rgba(15,23,42,.05)}
                            #tableQuestions tbody td{border-top:1px solid #e5e7eb!important;border-bottom:1px solid #e5e7eb!important;vertical-align:top!important;padding:.85rem!important;color:#334155;line-height:1.5}
                            #tableQuestions tbody td:first-child{border-left:1px solid #e5e7eb!important;border-radius:.7rem 0 0 .7rem}
                            #tableQuestions tbody td:last-child{border-right:1px solid #e5e7eb!important;border-radius:0 .7rem .7rem 0}
                            .pra-item-list{display:grid;gap:1rem}
                            .pra-item-card{background:#fff;border:1px solid #e5e7eb;border-radius:.85rem;box-shadow:0 10px 24px rgba(15,23,42,.05);overflow:hidden}
                            .pra-item-main{display:grid;grid-template-columns:auto minmax(0,1fr) auto;gap:1rem;align-items:start;padding:1rem;border-bottom:1px solid #e5e7eb;background:#fbfdff}
                            .pra-item-number{display:inline-flex;align-items:center;justify-content:center;width:38px;height:38px;border-radius:.7rem;background:#eff6ff;color:#1d4ed8;font-weight:900}
                            .pra-item-question h6{font-size:.98rem;font-weight:900;color:#0f172a;line-height:1.5;margin:0}
                            .pra-item-risk{display:flex;justify-content:flex-end}
                            .pra-item-grid{display:grid;grid-template-columns:minmax(150px,.65fr) minmax(260px,1.25fr) minmax(220px,.95fr) minmax(260px,1.15fr);gap:.75rem;padding:1rem;align-items:stretch}
                            .pra-info-panel{border:1px solid #e2e8f0;border-radius:.75rem;background:#f8fafc;padding:.8rem;min-width:0;min-height:100%;display:flex;flex-direction:column}
                            .pra-row-no{display:inline-flex;align-items:center;justify-content:center;width:34px;height:34px;border-radius:.65rem;background:#eff6ff;color:#1d4ed8;font-weight:900}
                            .pra-question-title{font-weight:800;color:#0f172a;margin-bottom:.55rem}
                            .pra-meta-stack{display:flex;flex-wrap:wrap;gap:.35rem;margin-bottom:.55rem}
                            .pra-mini-badge{display:inline-flex;align-items:center;border:1px solid #e2e8f0;border-radius:999px;padding:.22rem .5rem;background:#f8fafc;color:#475569;font-size:.75rem;font-weight:800}
                            .pra-answer-box{border:1px solid #e2e8f0;border-radius:.65rem;background:#f8fafc;padding:.65rem}
                            .pra-answer-label,.pra-text-label{font-size:.72rem;text-transform:uppercase;font-weight:900;color:#64748b;margin-bottom:.25rem}
                            .pra-answer-value{font-weight:800;color:#0f172a}
                            .pra-point{display:inline-flex;align-items:center;justify-content:center;min-width:34px;height:28px;border-radius:.55rem;background:#0f766e;color:#fff;font-weight:900;margin-top:.55rem;align-self:flex-start}
                            .pra-text-block{margin-bottom:.85rem}
                            .pra-text-block:last-child{margin-bottom:0}
                            .pra-text-value{color:#334155;white-space:normal}
                            .pra-score-grid{display:grid;gap:.55rem}
                            .pra-score-line{display:flex;align-items:center;justify-content:space-between;gap:.75rem;border-bottom:1px dashed #e2e8f0;padding-bottom:.45rem}
                            .pra-score-line:last-child{border-bottom:0;padding-bottom:0}
                            .pra-score-label{font-size:.76rem;font-weight:900;color:#64748b}
                            .pra-score-value{font-weight:800;color:#0f172a;text-align:right}
                            @media(max-width:1399.98px){.pra-item-grid{grid-template-columns:minmax(150px,.65fr) minmax(260px,1.35fr) minmax(220px,1fr)}.pra-item-grid .pra-info-panel:last-child{grid-column:1 / -1}}
                            @media(max-width:1199.98px){.pra-item-grid{grid-template-columns:1fr 1fr}.pra-item-grid .pra-info-panel:last-child{grid-column:auto}.pra-item-main{grid-template-columns:auto minmax(0,1fr)}.pra-item-risk{grid-column:2;justify-content:flex-start}}
                            @media(max-width:991.98px){.pra-report-header{flex-direction:column}.pra-report-header-meta{justify-content:flex-start}.pra-report-stats{grid-template-columns:repeat(2,minmax(0,1fr))}.pra-chart-grid{grid-template-columns:1fr}}
                            @media(max-width:575.98px){.pra-report-body{padding:.85rem}.pra-report-stats{grid-template-columns:1fr}.pra-chart-head,.pra-table-head{flex-direction:column;align-items:stretch}.pra-chart-canvas{height:300px}.pra-table-filter{min-width:0;max-width:none}.pra-item-main{grid-template-columns:1fr}.pra-item-risk{grid-column:auto}.pra-item-grid{grid-template-columns:1fr}.pra-item-number{width:34px;height:34px}}
                        </style>
                        <div class="pra-report-stats">
                            <div class="pra-report-stat stat-blue">
                                <div class="stat-top">
                                    <div>
                                        <div class="label">Item Risiko Dinilai</div>
                                        <div class="value"><?php echo $riskAssessed.' / '.$riskTotal; ?></div>
                                    </div>
                                    <span class="stat-icon"><i class="bi bi-clipboard2-check"></i></span>
                                </div>
                                <div class="hint">Item yang sudah memiliki skor risiko.</div>
                            </div>
                            <div class="pra-report-stat stat-green">
                                <div class="stat-top">
                                    <div>
                                        <div class="label">Skor Rata-rata</div>
                                        <div class="value"><?php echo number_format($averageRiskScore, 2, ',', '.'); ?></div>
                                        <div class="mt-2"><span class="risk-pill <?php echo $averageRiskMeta['class']; ?>"><?php echo htmlspecialchars($averageRiskMeta['level']); ?></span></div>
                                    </div>
                                    <span class="stat-icon"><i class="bi bi-speedometer2"></i></span>
                                </div>
                                <div class="hint">Rata-rata dari item yang sudah dinilai.</div>
                            </div>
                            <div class="pra-report-stat stat-orange">
                                <div class="stat-top">
                                    <div>
                                        <div class="label">Prioritas Tinggi</div>
                                        <div class="value"><?php echo (int)$riskCounts['Tinggi'] + (int)$riskCounts['Sangat Tinggi']; ?></div>
                                    </div>
                                    <span class="stat-icon"><i class="bi bi-activity"></i></span>
                                </div>
                                <div class="hint">Total risiko tinggi dan sangat tinggi.</div>
                            </div>
                            <div class="pra-report-stat stat-slate">
                                <div class="stat-top">
                                    <div>
                                        <div class="label">Belum Dinilai</div>
                                        <div class="value"><?php echo (int)$riskCounts['Belum Dinilai']; ?></div>
                                    </div>
                                    <span class="stat-icon"><i class="bi bi-hourglass-split"></i></span>
                                </div>
                                <div class="hint">Item yang belum memiliki skor risiko.</div>
                            </div>
                        </div>
                        <div class="pra-chart-grid">
                            <div class="pra-chart-card">
                                <div class="pra-chart-head">
                                    <div>
                                        <h5>Grafik Evaluasi LED</h5>
                                        <p>Persentase pemenuhan per referensi standar.</p>
                                    </div>
                                    <span class="pra-chart-badge"><i class="bi bi-diagram-3"></i> Radar</span>
                                </div>
                                <div class="pra-chart-canvas">
                                    <canvas id="chartEval"></canvas>
                                </div>
                            </div>
                            <div class="pra-chart-card">
                                <div class="pra-chart-head">
                                    <div>
                                        <h5>Pemetaan Risiko</h5>
                                        <p>Jumlah item pada setiap kategori risiko.</p>
                                    </div>
                                    <span class="pra-chart-badge"><i class="bi bi-bar-chart"></i> Bar</span>
                                </div>
                                <div class="pra-chart-canvas">
                                    <canvas id="chartRiskMap"></canvas>
                                </div>
                            </div>
                        </div>

                        <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
                        <script type="text/javascript">
                            document.addEventListener('DOMContentLoaded', function() {
                                const ctx = document.getElementById('chartEval');
                                const riskCtx = document.getElementById('chartRiskMap');
                                if (!ctx || !riskCtx || typeof Chart === 'undefined') {
                                    console.error('Canvas tidak ditemukan');
                                    return;
                                }

                                const labels = <?php echo $json_labels; ?>;
                                const chartType = labels.length < 3 ? 'bar' : 'radar';

                                new Chart(ctx, {
                                    type: chartType,
                                    data: {
                                        labels: labels,
                                        datasets: [{
                                            label: 'Laporan LED',
                                            data: <?php echo $json_data; ?>,
                                            backgroundColor: 'rgba(37, 99, 235, 0.18)',
                                            borderColor: 'rgb(37, 99, 235)',
                                            pointBackgroundColor: 'rgb(15, 118, 110)',
                                            pointBorderColor: '#ffffff',
                                            pointHoverBackgroundColor: '#ffffff',
                                            pointHoverBorderColor: 'rgb(37, 99, 235)',
                                            fill: true
                                        }]
                                    },
                                    options: {
                                        responsive: true,
                                        maintainAspectRatio: false,
                                        elements: {
                                            line: {
                                                borderWidth: 3
                                            }
                                        },
                                        scales: chartType === 'bar' ? {
                                            y: {
                                                beginAtZero: true,
                                                min: 0,
                                                max: 100
                                            }
                                        } : {
                                            r: {
                                                beginAtZero: true,
                                                min: 0,
                                                max: 100,
                                                ticks: {
                                                    stepSize: 20
                                                }
                                            }
                                        },
                                        plugins: {
                                            legend: {
                                                display: true,
                                                position: 'top'
                                            }
                                        }
                                    }
                                });

                                new Chart(riskCtx, {
                                    type: 'bar',
                                    data: {
                                        labels: <?php echo json_encode($riskChartLabels); ?>,
                                        datasets: [{
                                            label: 'Jumlah Item',
                                            data: <?php echo json_encode($riskChartData); ?>,
                                            backgroundColor: <?php echo json_encode($riskChartColors); ?>,
                                            borderColor: <?php echo json_encode($riskChartColors); ?>,
                                            borderWidth: 1,
                                            borderRadius: 6
                                        }]
                                    },
                                    options: {
                                        responsive: true,
                                        maintainAspectRatio: false,
                                        scales: {
                                            y: {
                                                beginAtZero: true,
                                                ticks: {
                                                    precision: 0
                                                }
                                            },
                                            x: {
                                                ticks: {
                                                    autoSkip: false,
                                                    maxRotation: 35,
                                                    minRotation: 0
                                                }
                                            }
                                        },
                                        plugins: {
                                            legend: {
                                                display: false
                                            },
                                            tooltip: {
                                                callbacks: {
                                                    label: function(context) {
                                                        return 'Jumlah Item: ' + context.parsed.y;
                                                    }
                                                }
                                            }
                                        }
                                    }
                                });
                            });
                        </script>
                        <div class="pra-table-card mt-4">
                            <div class="pra-table-head">
                                <div>
                                    <h5>Daftar Pertanyaan dan Jawaban</h5>
                                    <p>Ringkasan jawaban, skor, risiko, dampak, dan usulan mitigasi per item.</p>
                                </div>
                                <div class="pra-table-filter d-flex flex-column gap-2">
                                    <div>
                                        <label for="filterReference" class="form-label mb-1">Filter Referensi</label>
                                        <select id="filterReference" class="form-control form-control-sm">
                                            <option value="">Semua Referensi</option>
                                            <?php while($opt = mysqli_fetch_assoc($referenceOptions)): ?>
                                                <option value="<?php echo htmlspecialchars($opt['namastandar']); ?>"><?php echo htmlspecialchars($opt['namastandar']); ?></option>
                                            <?php endwhile; ?>
                                        </select>
                                    </div>
                                    <div>
                                        <label for="filterKeyword" class="form-label mb-1">Cari Item</label>
                                        <input type="search" id="filterKeyword" class="form-control form-control-sm" placeholder="Cari pertanyaan, risiko, atau mitigasi">
                                    </div>
                                </div>
                            </div>
                            <div class="pra-item-list" id="praItemList">
                                <?php
                                $no = 1;
                                foreach($questionRows as $row) {
                                    $answer = '';
                                    if($row['jawab'] == 1) {
                                        $answer = $row['opsi1'];
                                    } elseif($row['jawab'] == 2) {
                                        $answer = $row['opsi2'];
                                    }
                                    $riskMeta = admin_pra_risk_meta($row['risiko'] ?? 0);
                                    $searchText = implode(' ', [
                                        $row['namastandar'] ?? '',
                                        $row['noindikator'] ?? '',
                                        $row['pertanyaan'] ?? '',
                                        $answer,
                                        $row['isi_risiko'] ?? '',
                                        $row['isi_dampak'] ?? '',
                                        $row['usulan_mitigasi'] ?? '',
                                    ]);
                                    ?>
                                    <article class="pra-item-card" data-reference="<?php echo htmlspecialchars($row['namastandar'] ?? '', ENT_QUOTES); ?>" data-search="<?php echo htmlspecialchars(strtolower($searchText), ENT_QUOTES); ?>">
                                        <div class="pra-item-main">
                                            <div class="pra-item-number"><?php echo $no++; ?></div>
                                            <div class="pra-item-question">
                                                <div class="pra-meta-stack">
                                                    <span class="pra-mini-badge"><?php echo htmlspecialchars($row['namastandar']); ?></span>
                                                    <span class="pra-mini-badge">Indikator <?php echo htmlspecialchars($row['noindikator']); ?></span>
                                                </div>
                                                <h6><?php echo htmlspecialchars($row['pertanyaan']); ?></h6>
                                            </div>
                                            <div class="pra-item-risk">
                                                <span class="risk-pill <?php echo $riskMeta['class']; ?>"><?php echo htmlspecialchars($riskMeta['label']); ?></span>
                                            </div>
                                        </div>

                                        <div class="pra-item-grid">
                                            <section class="pra-info-panel">
                                                <div class="pra-text-label">Jawaban & Poin</div>
                                                <div class="pra-answer-value"><?php echo htmlspecialchars($answer ?: '-'); ?></div>
                                                <span class="pra-point">Poin <?php echo htmlspecialchars($row['koreksi'] ?? '-'); ?></span>
                                            </section>

                                            <section class="pra-info-panel">
                                                <div class="pra-text-block">
                                                    <div class="pra-text-label">Isi Risiko</div>
                                                    <div class="pra-text-value"><?php echo nl2br(htmlspecialchars($row['isi_risiko'] ?? '-')); ?></div>
                                                </div>
                                                <div class="pra-text-block">
                                                    <div class="pra-text-label">Isi Dampak</div>
                                                    <div class="pra-text-value"><?php echo nl2br(htmlspecialchars($row['isi_dampak'] ?? '-')); ?></div>
                                                </div>
                                            </section>

                                            <section class="pra-info-panel">
                                                <div class="pra-text-label">Skor Risiko</div>
                                                <div class="pra-score-grid">
                                                    <div class="pra-score-line">
                                                        <span class="pra-score-label">Dampak</span>
                                                        <span class="pra-score-value"><?php echo !empty($row['dampak']) ? (int)$row['dampak'].' - '.htmlspecialchars($dampakLabels[(int)$row['dampak']] ?? '-') : '-'; ?></span>
                                                    </div>
                                                    <div class="pra-score-line">
                                                        <span class="pra-score-label">Probabilitas</span>
                                                        <span class="pra-score-value"><?php echo !empty($row['probabilitas']) ? (int)$row['probabilitas'].' - '.htmlspecialchars($probabilitasLabels[(int)$row['probabilitas']] ?? '-') : '-'; ?></span>
                                                    </div>
                                                    <div class="pra-score-line">
                                                        <span class="pra-score-label">Skor</span>
                                                        <span class="risk-pill <?php echo $riskMeta['class']; ?>"><?php echo htmlspecialchars($riskMeta['label']); ?></span>
                                                    </div>
                                                </div>
                                            </section>

                                            <section class="pra-info-panel">
                                                <div class="pra-text-label">Usulan Mitigasi</div>
                                                <div class="pra-text-value"><?php echo nl2br(htmlspecialchars($row['usulan_mitigasi'] ?? '-')); ?></div>
                                            </section>
                                        </div>
                                    </article>
                                    <?php
                                }
                                ?>
                                <div class="alert alert-light border d-none mb-0" id="praEmptyState">Tidak ada item yang sesuai dengan filter.</div>
                            </div>
                        </div>

                        <script type="text/javascript">
                            document.addEventListener('DOMContentLoaded', function() {
                                var referenceFilter = document.getElementById('filterReference');
                                var keywordFilter = document.getElementById('filterKeyword');
                                var cards = Array.prototype.slice.call(document.querySelectorAll('.pra-item-card'));
                                var emptyState = document.getElementById('praEmptyState');

                                function applyItemFilters() {
                                    var reference = referenceFilter ? referenceFilter.value : '';
                                    var keyword = keywordFilter ? keywordFilter.value.trim().toLowerCase() : '';
                                    var visible = 0;

                                    cards.forEach(function(card) {
                                        var matchesReference = !reference || card.dataset.reference === reference;
                                        var matchesKeyword = !keyword || (card.dataset.search || '').indexOf(keyword) !== -1;
                                        var show = matchesReference && matchesKeyword;
                                        card.classList.toggle('d-none', !show);
                                        if (show) visible++;
                                    });

                                    if (emptyState) {
                                        emptyState.classList.toggle('d-none', visible !== 0);
                                    }
                                }

                                if (referenceFilter) referenceFilter.addEventListener('change', applyItemFilters);
                                if (keywordFilter) keywordFilter.addEventListener('input', applyItemFilters);
                            });
                        </script>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>
<?php 
include 'foot.php';
?>

