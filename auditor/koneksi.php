<?php 

$kon=mysqli_connect("localhost","root","","polaris");
if ($kon) {
    mysqli_set_charset($kon, "utf8mb4");
}

// Polyfills for PHP 8 string helper functions
if (!function_exists('str_contains')) {
    function str_contains($haystack, $needle) {
        return $needle === '' || strpos($haystack, $needle) !== false;
    }
}
if (!function_exists('str_starts_with')) {
    function str_starts_with($haystack, $needle) {
        return strncmp($haystack, $needle, strlen($needle)) === 0;
    }
}
if (!function_exists('str_ends_with')) {
    function str_ends_with($haystack, $needle) {
        return $needle === '' || substr($haystack, -strlen($needle)) === $needle;
    }
}

// Helper lookup data pegawai langsung dari database lokal (tabel user)
if (!function_exists('polaris_get_pegawai_by_id')) {
    function polaris_get_pegawai_by_id($kon, $userId) {
        $userId = trim((string)$userId);
        if ($userId !== '' && $kon) {
            $q = mysqli_query($kon, "SELECT id, nip, nama FROM user WHERE id = '".(int)$userId."' LIMIT 1");
            if ($q && $row = mysqli_fetch_assoc($q)) {
                return [
                    'id_pegawai' => $row['id'],
                    'nama_lengkap' => $row['nama'],
                    'nip' => $row['nip']
                ];
            }
        }
        return ['id_pegawai' => $userId, 'nama_lengkap' => '', 'nip' => ''];
    }
}

if (!function_exists('polaris_get_pegawai_by_name')) {
    function polaris_get_pegawai_by_name($kon, $name, $unitId = null) {
        $name = trim((string)$name);
        if ($name !== '' && $kon) {
            // 1. Cari exact match nama
            $q = mysqli_query($kon, "SELECT id, nip, nama FROM user WHERE nama = '".mysqli_real_escape_string($kon, $name)."' LIMIT 1");
            if ($q && $row = mysqli_fetch_assoc($q)) {
                return ['id_pegawai' => $row['id'], 'nama_lengkap' => $row['nama'], 'nip' => $row['nip']];
            }
            // 2. Cari berdasarkan unit & level Kepala
            if (!empty($unitId)) {
                $q = mysqli_query($kon, "SELECT id, nip, nama FROM user WHERE unit = '".mysqli_real_escape_string($kon, $unitId)."' AND level = 'Kepala' LIMIT 1");
                if ($q && $row = mysqli_fetch_assoc($q)) {
                    return ['id_pegawai' => $row['id'], 'nama_lengkap' => $row['nama'], 'nip' => $row['nip']];
                }
            }
            // 3. Cari partial match nama (sebelum koma gelar)
            $parts = explode(',', $name);
            $shortName = trim($parts[0]);
            if ($shortName !== '') {
                $q = mysqli_query($kon, "SELECT id, nip, nama FROM user WHERE nama LIKE '%".mysqli_real_escape_string($kon, $shortName)."%' LIMIT 1");
                if ($q && $row = mysqli_fetch_assoc($q)) {
                    return ['id_pegawai' => $row['id'], 'nama_lengkap' => $row['nama'], 'nip' => $row['nip']];
                }
            }
        }
        return ['id_pegawai' => '', 'nama_lengkap' => $name, 'nip' => ''];
    }
}

// Helper rekap daftar temuan Berita Acara berbasis Tahap 5, 6, 7, 8
if (!function_exists('polaris_get_temuan_beritaacara')) {
    function polaris_get_temuan_beritaacara($kon, $ka) {
        $ka = trim((string)$ka);
        if ($ka === '' || !$kon) {
            return [];
        }
        $safeKa = mysqli_real_escape_string($kon, $ka);
        $temuanList = [];
        $seenRisiko = [];
        
        // 1. Tahap 8 (PTK A - audit_ptk)
        $checkPtk = mysqli_query($kon, "SHOW TABLES LIKE 'audit_ptk'");
        if ($checkPtk && mysqli_num_rows($checkPtk) > 0) {
            $qPtk = mysqli_query($kon, "
                SELECT p.*, tr.kategori_temuan as tri_kategori, lk.tipe_temuan as lk2_kategori, r.pernyataan_risiko
                FROM audit_ptk p
                LEFT JOIN audit_triangulasi_detail tr ON p.kodeaudit=tr.kodeaudit AND p.no_risiko=tr.no_risiko
                LEFT JOIN audit_lk2_temuan lk ON p.kodeaudit=lk.kodeaudit AND p.no_risiko=lk.no_risiko
                LEFT JOIN audit_persiapan_risiko r ON p.kodeaudit=r.kodeaudit AND p.no_risiko=r.no_risiko
                WHERE p.kodeaudit='$safeKa'
                ORDER BY CAST(REPLACE(p.no_risiko,'R-','') AS UNSIGNED) ASC
            ");
            if ($qPtk && mysqli_num_rows($qPtk) > 0) {
                while ($p = mysqli_fetch_assoc($qPtk)) {
                    $ringkasan = trim((string)($p['ringkasan_temuan'] ?? ''));
                    if (stripos($ringkasan, 'tidak ada temuan') !== false) {
                        continue;
                    }
                    if ($ringkasan === '') {
                        $ringkasan = !empty($p['pernyataan_risiko']) ? $p['pernyataan_risiko'] : 'Temuan PTK ' . ($p['ptk_no'] ?? $p['no_risiko']);
                    }
                    $tDesc = !empty($p['ptk_no']) ? "<strong>[" . htmlspecialchars($p['ptk_no']) . "]</strong> " . htmlspecialchars($ringkasan) : htmlspecialchars($ringkasan);
                    if (!empty($p['akar_masalah'])) {
                        $tDesc .= "<br><small style='color:#64748b;'>Akar Masalah: " . htmlspecialchars($p['akar_masalah']) . "</small>";
                    }
                    
                    $kategori = !empty($p['tri_kategori']) ? $p['tri_kategori'] : (!empty($p['lk2_kategori']) ? $p['lk2_kategori'] : (!empty($p['konsekuensi']) ? $p['konsekuensi'] : 'KTS'));
                    $efRaw = strtoupper(trim((string)($p['efektivitas'] ?? '')));
                    $status = ($efRaw === 'EFEKTIF') ? 'Close' : 'Open';
                    
                    $seenRisiko[$p['no_risiko']] = true;
                    $temuanList[] = [
                        'tahap' => 'Tahap 8 (PTK A)',
                        'no_risiko' => $p['no_risiko'],
                        'kondisi' => $tDesc,
                        'kategori' => $kategori,
                        'status' => $status
                    ];
                }
            }
        }
        
        // 2. Tahap 7 (Triangulasi - audit_triangulasi_detail)
        $checkTri = mysqli_query($kon, "SHOW TABLES LIKE 'audit_triangulasi_detail'");
        if ($checkTri && mysqli_num_rows($checkTri) > 0) {
            $qTri = mysqli_query($kon, "
                SELECT tr.*, lk.plor_p, lk.plor_o, r.pernyataan_risiko
                FROM audit_triangulasi_detail tr
                LEFT JOIN audit_lk2_temuan lk ON tr.kodeaudit=lk.kodeaudit AND tr.no_risiko=lk.no_risiko
                LEFT JOIN audit_persiapan_risiko r ON tr.kodeaudit=r.kodeaudit AND tr.no_risiko=r.no_risiko
                WHERE tr.kodeaudit='$safeKa'
                ORDER BY CAST(REPLACE(tr.no_risiko,'R-','') AS UNSIGNED) ASC
            ");
            if ($qTri && mysqli_num_rows($qTri) > 0) {
                while ($tr = mysqli_fetch_assoc($qTri)) {
                    $kat = trim((string)($tr['kategori_temuan'] ?? ''));
                    if ($kat === '' || stripos($kat, 'positif') !== false || stripos($kat, 'sesuai') !== false) {
                        continue;
                    }
                    if (isset($seenRisiko[$tr['no_risiko']])) {
                        continue;
                    }
                    
                    $isi = trim((string)($tr['kesimpulan'] ?? ''));
                    if ($isi === '') $isi = trim((string)($tr['catatan_auditor'] ?? ''));
                    if ($isi === '') $isi = trim((string)($tr['plor_p'] ?? ''));
                    if ($isi === '') $isi = trim((string)($tr['pernyataan_risiko'] ?? ''));
                    if ($isi === '') continue;
                    
                    $seenRisiko[$tr['no_risiko']] = true;
                    $temuanList[] = [
                        'tahap' => 'Tahap 7 (Triangulasi)',
                        'no_risiko' => $tr['no_risiko'],
                        'kondisi' => "<strong>[" . htmlspecialchars($tr['no_risiko']) . "]</strong> " . htmlspecialchars($isi),
                        'kategori' => $kat,
                        'status' => 'Open'
                    ];
                }
            }
        }
        
        // 3. Tahap 6 (LK2 Digital - audit_lk2_temuan)
        $checkLk2 = mysqli_query($kon, "SHOW TABLES LIKE 'audit_lk2_temuan'");
        if ($checkLk2 && mysqli_num_rows($checkLk2) > 0) {
            $qLk2 = mysqli_query($kon, "
                SELECT lk.*, r.pernyataan_risiko
                FROM audit_lk2_temuan lk
                LEFT JOIN audit_persiapan_risiko r ON lk.kodeaudit=r.kodeaudit AND lk.no_risiko=r.no_risiko
                WHERE lk.kodeaudit='$safeKa'
                ORDER BY CAST(REPLACE(lk.no_risiko,'R-','') AS UNSIGNED) ASC
            ");
            if ($qLk2 && mysqli_num_rows($qLk2) > 0) {
                while ($lk = mysqli_fetch_assoc($qLk2)) {
                    $tipe = trim((string)($lk['tipe_temuan'] ?? ''));
                    if ($tipe === '' || stripos($tipe, 'positif') !== false || stripos($tipe, 'sesuai') !== false) {
                        continue;
                    }
                    if (isset($seenRisiko[$lk['no_risiko']])) {
                        continue;
                    }
                    
                    $plor = trim((string)($lk['plor_p'] ?? ''));
                    if (!empty($lk['plor_o'])) {
                        $plor .= ($plor !== '' ? " — Bukti: " : "") . $lk['plor_o'];
                    }
                    if ($plor === '') $plor = trim((string)($lk['pernyataan_risiko'] ?? ''));
                    if ($plor === '') continue;
                    
                    $seenRisiko[$lk['no_risiko']] = true;
                    $temuanList[] = [
                        'tahap' => 'Tahap 6 (LK2)',
                        'no_risiko' => $lk['no_risiko'],
                        'kondisi' => "<strong>[" . htmlspecialchars($lk['no_risiko']) . "]</strong> " . htmlspecialchars($plor),
                        'kategori' => $tipe,
                        'status' => 'Open'
                    ];
                }
            }
        }
        
        // 4. Tahap 5 (Catatan Persiapan & Identifikasi Risiko - audit_persiapan_risiko)
        if (empty($temuanList)) {
            $checkPr = mysqli_query($kon, "SHOW TABLES LIKE 'audit_persiapan_risiko'");
            if ($checkPr && mysqli_num_rows($checkPr) > 0) {
                $qR = mysqli_query($kon, "
                    SELECT * FROM audit_persiapan_risiko 
                    WHERE kodeaudit='$safeKa' AND (UPPER(level) IN ('MERAH', 'KUNING') OR skor >= 5)
                    ORDER BY CAST(REPLACE(no_risiko,'R-','') AS UNSIGNED) ASC
                ");
                if ($qR && mysqli_num_rows($qR) > 0) {
                    while ($r = mysqli_fetch_assoc($qR)) {
                        $lvl = strtoupper($r['level'] ?? '');
                        $katLabel = ($lvl === 'MERAH') ? 'Risiko Tinggi (KTS)' : 'Risiko Sedang (OB)';
                        $temuanList[] = [
                            'tahap' => 'Tahap 5 (Persiapan Risiko)',
                            'no_risiko' => $r['no_risiko'],
                            'kondisi' => "<strong>[" . htmlspecialchars($r['no_risiko']) . "]</strong> " . htmlspecialchars($r['pernyataan_risiko']),
                            'kategori' => $katLabel,
                            'status' => 'Open'
                        ];
                    }
                }
            }
        }
        
        // Fallback: Tahap 3 (Konvensional - audit_kondisi)
        if (empty($temuanList)) {
            $checkKondisi = mysqli_query($kon, "SHOW TABLES LIKE 'audit_kondisi'");
            if ($checkKondisi && mysqli_num_rows($checkKondisi) > 0) {
                $qOld = mysqli_query($kon, "SELECT * FROM audit_kondisi WHERE kodeaudit='$safeKa' AND jeniskondisi='Temuan'");
                if ($qOld && mysqli_num_rows($qOld) > 0) {
                    while ($t = mysqli_fetch_assoc($qOld)) {
                        $stOld = strtoupper(trim((string)($t['status'] ?? '')));
                        $statusOld = ($stOld === 'EFEKTIF' || $stOld === 'CLOSE') ? 'Close' : 'Open';
                        $temuanList[] = [
                            'tahap' => 'Tahap 3 (Konvensional)',
                            'no_risiko' => $t['kodekondisi'] ?? '-',
                            'kondisi' => htmlspecialchars($t['kondisi']),
                            'kategori' => htmlspecialchars($t['kategori']),
                            'status' => $statusOld
                        ];
                    }
                }
            }
        }
        
        return $temuanList;
    }
}
?>