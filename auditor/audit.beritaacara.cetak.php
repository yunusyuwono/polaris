<?php 
include '../audit.ui.php';
session_start();
include "koneksi.php";
include "audit.validasi.php";

$kaAudit = isset($_GET['ka']) ? mysqli_real_escape_string($kon, $_GET['ka']) : '';
$gda = mysqli_fetch_array(mysqli_query($kon, "SELECT audit.*, unit.id as unit_id, unit.nama as unit, unit.kepala as kepala FROM audit join unit on audit.unit=unit.id where audit.kodeaudit='$kaAudit'"));

$hm = date('D', strtotime($gda['tglmulai']));
$hs = date('D', strtotime($gda['tglselesai']));

$hariIndo = [
    'Mon' => 'Senin',
    'Tue' => 'Selasa',
    'Wed' => 'Rabu',
    'Thu' => 'Kamis',
    'Fri' => 'Jumat',
    'Sat' => 'Sabtu',
    'Sun' => 'Minggu'
];
$hmulai = $hariIndo[$hm] ?? $hm;
$hselesai = $hariIndo[$hs] ?? $hs;

$ketuaaud = '';
$nips = '';
$ka = mysqli_fetch_array(mysqli_query($kon, "SELECT auditor.*, user.nama, user.nip FROM auditor LEFT JOIN user ON auditor.user=user.id WHERE auditor.kodeaudit='$gda[kodeaudit]' AND auditor.status='Ketua' LIMIT 1"));
if (!empty($ka['nama'])) {
    $ketuaaud = $ka['nama'];
    $nips = $ka['nip'] ?? '';
} else {
    $gauditor = mysqli_fetch_array(mysqli_query($kon, "SELECT * FROM auditor WHERE kodeaudit='$kaAudit' AND status='Ketua' LIMIT 1"));
    if (!empty($gauditor['user'])) {
        $pKetua = polaris_get_pegawai_by_id($kon, $gauditor['user']);
        $ketuaaud = $pKetua['nama_lengkap'] ?? '';
        $nips = $pKetua['nip'] ?? '';
    }
}
?>
<body onload="window.print()">
    <center>
        <h3>BERITA ACARA <br>
            AUDIT MUTU INTERNAL</h3>
    </center>
    <p style="text-align: justify;line-height:25px ">
        Pada hari <?=$hmulai;?> s.d <?=$hselesai;?> tanggal <?=audit_ui_date($gda['tglmulai']);?> s.d <?=audit_ui_date($gda['tglselesai']);?> telah melaksanakan kegiatan Audit Mutu Internal di <?=$gda['unit'];?> oleh Auditor sebagai berikut : <br>
        
        <table>
            <tr>
                <td>Ketua Tim</td><td>:</td><td> <?=$ketuaaud;?></td>
            </tr>
            <tr>
                <td valign="top">Anggota</td><td valign="top" width="10">:</td><td> 
                    <?php 
                    $no = 1;
                    $tima = mysqli_query($kon, "SELECT auditor.*, user.nama, user.nip FROM auditor LEFT JOIN user ON auditor.user=user.id WHERE auditor.kodeaudit='$gda[kodeaudit]' AND auditor.status LIKE '%Anggota%'");
                    while($ta = mysqli_fetch_array($tima))
                    {
                        $namaAnggota = $ta['nama'] ?? '';
                        if (empty($namaAnggota) && !empty($ta['user'])) {
                            $pAnggota = polaris_get_pegawai_by_id($kon, $ta['user']);
                            $namaAnggota = $pAnggota['nama_lengkap'] ?? '';
                        }
                        if (!empty($namaAnggota)) {
                            echo $no . '. ' . htmlspecialchars($namaAnggota) . '<br>';
                            $no++;
                        }
                    }
                    ?>
                </td>
            </tr>
        </table>
        
        <?php 
        $daftarTemuan = polaris_get_temuan_beritaacara($kon, $gda['kodeaudit']);
        $totalTemuan = count($daftarTemuan);
        ?>

        Adapun hasil temuan pada saat audit terdapat <?=$totalTemuan;?> temuan di <?=$gda['unit'];?> sebagai berikut :<br>
        <table width="100%" border="1" cellpadding="5" cellspacing="0" style="border-collapse: collapse; margin-top: 8px; margin-bottom: 8px;">
            <thead>
                <tr style="background-color: #f1f5f9;">
                    <th align="center" width="5%">No.</th>
                    <th align="center">Daftar Temuan</th>
                    <th align="center" width="20%">Kategori</th>
                    <th align="center" width="15%">Status</th>
                </tr>
            </thead>
            <tbody>
        <?php 
        if ($totalTemuan > 0) {
            $no = 1;
            foreach ($daftarTemuan as $t) {
                ?>
                <tr>
                    <td align="center" valign="top"><?=$no;?></td>
                    <td valign="top"><?=$t['kondisi'];?></td>
                    <td align="center" valign="top"><?=$t['kategori'];?></td>
                    <td align="center" valign="top"><?php 
                        $st = strtoupper(trim((string)($t['status'] ?? '')));
                        echo ($st === 'EFEKTIF' || $st === 'CLOSE') ? 'Close' : 'Open';
                    ?></td>
                </tr>
                <?php
                $no++;
            }
        } else {
            ?>
            <tr>
                <td colspan="4" align="center" style="padding: 8px;"><em>Tidak ada temuan pada audit ini.</em></td>
            </tr>
            <?php
        }
        ?>
            </tbody>
        </table>
        
        Demikianlah temuan ini dan akan ditindaklanjuti paling lambat 1 bulan setelah Audit Mutu Internal.

        <table width="100%">
        <tr>
            <?php
             $nl = $gda['kepala'] ?? '';
             $pKepala = polaris_get_pegawai_by_name($kon, $nl, $gda['unit_id'] ?? null);
             $nipKepala = $pKepala['nip'] ?? '';
            ?>  
            <td width="50%" align="center">
                Kepala<br>
                <?=$gda['unit'];?>
                <br><br><br><br>
                <?=$gda['kepala'];?><br>
                <b>NIP. <?=$nipKepala ?: '-';?></b>
            </td>
            <td width="50%" align="center">
                Bengkulu, <?=audit_ui_date(date('Y-m-d'));?><br>
                Ketua Tim Auditor
                <br><br><br><br>
                <?=$ketuaaud ?: '-';?><br>
                <b>NIP. <?=$nips ?: '-';?></b>
            </td>
        </tr>
        </table>
     </p>
</body>
