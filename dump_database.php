<?php
/**
 * =========================================================================
 * SCRIPT DUMP / BACKUP DATABASE MYSQL (PHP)
 * =========================================================================
 * Fitur:
 * - Streaming output (hemat memori / RAM, anti Memory Exhausted)
 * - Multi-row batch INSERT (lebih cepat di-import, ukuran file lebih ringkas)
 * - Kompatibel phpMyAdmin, MySQL Workbench, & mysql CLI
 * - Mendukung otomatis koneksi dari koneksi.php atau standalone
 * - Mendukung mode download langsung via browser atau via CLI
 * - Mendukung kompresi GZIP jika diakses dengan parameter ?gzip=1
 * =========================================================================
 */

// Naikkan batas waktu eksekusi & memori untuk database berukuran besar
@ini_set('max_execution_time', '600');
@ini_set('memory_limit', '512M');

// 1. Ambil koneksi database
if (file_exists(__DIR__ . '/koneksi.php')) {
    require_once __DIR__ . '/koneksi.php';
}

// Fallback jika variabel $kon belum terdefinisi di koneksi.php
if (!isset($kon) || !($kon instanceof mysqli)) {
    $db_host = 'localhost';
    $db_user = 'u7698622_polaris';
    $db_pass = 'MRwuSDk,9JiFz+CL';
    $db_name = 'u7698622_polaris';
    
    $kon = @new mysqli($db_host, $db_user, $db_pass, $db_name);
    if ($kon->connect_error) {
        die("Koneksi ke database gagal: " . $kon->connect_error);
    }
}

// Ambil nama database aktif
$db_result = $kon->query("SELECT DATABASE()");
$db_name = ($db_result && $row = $db_result->fetch_row()) ? $row[0] : 'backup';

// Pastikan charset UTF-8
$kon->set_charset("utf8mb4");

// 2. Tentukan Mode Eksekusi (CLI atau Web Browser)
$is_cli = (php_sapi_name() === 'cli');
$use_gzip = isset($_GET['gzip']) && $_GET['gzip'] == '1' && function_exists('gzencode');

$timestamp = date("Y-m-d_H-i-s");
$filename = "{$db_name}_backup_{$timestamp}.sql" . ($use_gzip ? ".gz" : "");

// Buka stream output
if ($is_cli) {
    // Jika lewat command line: php dump_database.php [nama_file.sql]
    global $argv;
    $target_file = isset($argv[1]) ? $argv[1] : $filename;
    $out = fopen($target_file, 'wb');
    if (!$out) {
        die("Gagal membuat file output: {$target_file}\n");
    }
    echo "Memulai backup database '{$db_name}' ke {$target_file}...\n";
} else {
    // Lewat Browser (Langsung download)
    if (ob_get_level()) {
        ob_end_clean();
    }
    
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Content-Transfer-Encoding: binary');
    header('Pragma: no-cache');
    header('Expires: 0');

    if ($use_gzip) {
        header('Content-Encoding: gzip');
        $out = fopen('php://output', 'wb');
        // Jika menggunakan gzip stream lewat output buffering
        ob_start('ob_gzhandler');
    } else {
        $out = fopen('php://output', 'wb');
    }
}

function write_dump($stream, $str) {
    fwrite($stream, $str);
}

// 3. Header SQL
write_dump($out, "-- ========================================================\n");
write_dump($out, "-- MySQL Database Dump\n");
write_dump($out, "-- Database    : {$db_name}\n");
write_dump($out, "-- Dibuat Pada : " . date("Y-m-d H:i:s") . "\n");
write_dump($out, "-- Host Server : " . $kon->server_info . "\n");
write_dump($out, "-- ========================================================\n\n");

write_dump($out, "SET FOREIGN_KEY_CHECKS=0;\n");
write_dump($out, "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n");
write_dump($out, "SET AUTOCOMMIT = 0;\n");
write_dump($out, "START TRANSACTION;\n");
write_dump($out, "SET time_zone = \"+00:00\";\n");
write_dump($out, "/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;\n");
write_dump($out, "/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;\n");
write_dump($out, "/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;\n");
write_dump($out, "/*!40101 SET NAMES utf8mb4 */;\n\n");

// 4. Ambil Daftar Tabel
$tables = [];
$res_tables = $kon->query("SHOW FULL TABLES WHERE Table_type = 'BASE TABLE'");
if ($res_tables) {
    while ($row = $res_tables->fetch_row()) {
        $tables[] = $row[0];
    }
}

// 5. Loop Semua Tabel
foreach ($tables as $table) {
    // Struktur Tabel
    write_dump($out, "-- --------------------------------------------------------\n");
    write_dump($out, "-- Struktur tabel untuk `{$table}`\n");
    write_dump($out, "-- --------------------------------------------------------\n\n");
    write_dump($out, "DROP TABLE IF EXISTS `{$table}`;\n");

    $create_res = $kon->query("SHOW CREATE TABLE `{$table}`");
    if ($create_res && ($create_row = $create_res->fetch_row())) {
        write_dump($out, $create_row[1] . ";\n\n");
    }

    // Isi Data Tabel
    write_dump($out, "-- Dumping data untuk tabel `{$table}`\n");
    
    // Gunakan cursor unbuffered untuk efisiensi memori tabel besar
    $data_res = $kon->query("SELECT * FROM `{$table}`", MYSQLI_USE_RESULT);
    if ($data_res) {
        $fields_count = $data_res->field_count;
        $fields_meta = $data_res->fetch_fields();
        
        $batch_size = 200; // Jumlah baris per query INSERT
        $row_counter = 0;
        $batch_values = [];

        while ($row = $data_res->fetch_row()) {
            $row_counter++;
            $values = [];
            for ($i = 0; $i < $fields_count; $i++) {
                if ($row[$i] === null) {
                    $values[] = "NULL";
                } elseif (in_array($fields_meta[$i]->type, [MYSQLI_TYPE_TINY, MYSQLI_TYPE_SHORT, MYSQLI_TYPE_LONG, MYSQLI_TYPE_LONGLONG, MYSQLI_TYPE_INT24])) {
                    $values[] = is_numeric($row[$i]) ? $row[$i] : "'" . $kon->real_escape_string($row[$i]) . "'";
                } else {
                    $values[] = "'" . $kon->real_escape_string($row[$i]) . "'";
                }
            }
            $batch_values[] = "(" . implode(", ", $values) . ")";

            // Tulis batch jika sudah mencapai batas
            if (count($batch_values) >= $batch_size) {
                write_dump($out, "INSERT INTO `{$table}` VALUES\n" . implode(",\n", $batch_values) . ";\n");
                $batch_values = [];
            }
        }

        // Tulis sisa batch terakhir
        if (!empty($batch_values)) {
            write_dump($out, "INSERT INTO `{$table}` VALUES\n" . implode(",\n", $batch_values) . ";\n");
        }

        if ($row_counter > 0) {
            write_dump($out, "\n");
        } else {
            write_dump($out, "-- (Tabel kosong)\n\n");
        }

        $data_res->free();
    }
}

// 6. Footer SQL
write_dump($out, "-- --------------------------------------------------------\n");
write_dump($out, "SET FOREIGN_KEY_CHECKS=1;\n");
write_dump($out, "COMMIT;\n");
write_dump($out, "/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;\n");
write_dump($out, "/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;\n");
write_dump($out, "/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;\n");
write_dump($out, "-- Dump selesai pada " . date("Y-m-d H:i:s") . "\n");

// Tutup stream
if ($is_cli) {
    fclose($out);
    echo "Backup selesai! File tersimpan di: {$target_file}\n";
} else {
    if ($use_gzip) {
        ob_end_flush();
    }
    fclose($out);
    exit;
}
?>
