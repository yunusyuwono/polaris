<?php
header('Content-Type: application/json; charset=utf-8');
include 'koneksi.php';

$action = isset($_GET['action']) ? $_GET['action'] : 'fetch';

function iso_sql_val($kon, $val){
    if($val === null || $val === '') return 'NULL';
    return "'".mysqli_real_escape_string($kon, $val)."'";
}

function json_success($data = []){
    echo json_encode(array_merge(['success'=>true], $data));
    exit;
}

function json_error($msg, $code = 400){
    // Avoid header() issues if output already started.
    echo json_encode(['success'=>false,'error'=>$msg]);
    exit;
}


// ---------------- ISO MAIN TABLE ----------------
if($action == 'fetch'){
    $draw = isset($_POST['draw']) ? intval($_POST['draw']) : 0;
    $start = isset($_POST['start']) ? intval($_POST['start']) : 0;
    $length = isset($_POST['length']) ? intval($_POST['length']) : 25;
    $search = isset($_POST['search']['value']) ? mysqli_real_escape_string($kon, $_POST['search']['value']) : '';
    $order_col = isset($_POST['order'][0]['column']) ? intval($_POST['order'][0]['column']) : 1;
    $order_dir = isset($_POST['order'][0]['dir']) && strtolower($_POST['order'][0]['dir']) === 'asc' ? 'ASC' : 'DESC';

    // DataTables columns in iso.php: [0 No, 1 title, 2 year, 3 actions]
    // DB iso.sql tidak punya kolom year. Supaya request tidak error saat sort,
    // kita paksa order_field selalu pakai nama_iso.
    $order_field = 'nama_iso';


    $resTotal = mysqli_query($kon, "SELECT COUNT(*) AS cnt FROM iso");
    $total = 0; if($resTotal){ $r = mysqli_fetch_assoc($resTotal); $total = intval($r['cnt']); }

    $where = '';
    if($search !== ''){
        $where = " WHERE nama_iso LIKE '%$search%' ";
    }

    $resFiltered = mysqli_query($kon, "SELECT COUNT(*) AS cnt FROM iso $where");
    $filtered = 0; if($resFiltered){ $r = mysqli_fetch_assoc($resFiltered); $filtered = intval($r['cnt']); }

    $limit_sql = " LIMIT $start, $length";
    $order_sql = " ORDER BY $order_field $order_dir";

    $rows = [];
    $q = mysqli_query($kon, "SELECT idiso, nama_iso FROM iso $where $order_sql $limit_sql");
    while($row = mysqli_fetch_assoc($q)){
        $id = intval($row['idiso']);
        $title = $row['nama_iso'];

        // Keep iso.php buttons/handlers
        $actions = '<button class="btn btn-sm btn-primary btn-edit-iso" data-id="'.$id.'" type="button">Edit</button> '
                 .'<button class="btn btn-sm btn-danger btn-del-iso" data-id="'.$id.'" type="button">Hapus</button> '
                 .'<button class="btn btn-sm btn-outline-primary btn-add-klausul" data-id="'.$id.'" type="button">Tambah Klausul</button> '
                 .'<a class="btn btn-sm btn-secondary" href="iso.laporan.php?iso_id='.$id.'">Laporan</a>';

        // iso.php expects columns: title, year, actions
        // isi year dengan angka agar DataTables sort/renderer tidak tersandung.
        $rows[] = ['id'=>$id,'title'=>$title,'actions'=>$actions];

    }

    echo json_encode(["draw"=>$draw,"recordsTotal"=>$total,"recordsFiltered"=>$filtered,"data"=>$rows]);
    exit;
}

if($action == 'iso_save'){
    $raw = isset($_POST['data']) ? $_POST['data'] : null;
    if(!$raw) json_error('no_data');
    $payload = json_decode($raw, true);
    if(!$payload || !isset($payload['title'])) json_error('invalid_data');

    $nama = mysqli_real_escape_string($kon, $payload['title']);
    if(trim($nama) === '') json_error('invalid_title');

    $now = date('Y-m-d H:i:s'); // not used by schema but ok
    $ins = mysqli_query($kon, "INSERT INTO iso (nama_iso) VALUES ('$nama')");
    if(!$ins) json_error(mysqli_error($kon));

    $id = mysqli_insert_id($kon);
    json_success(['id'=>$id]);
}

if($action == 'get_iso'){
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    if($id<=0) json_error('invalid');
    $q = mysqli_query($kon, "SELECT * FROM iso WHERE idiso=$id LIMIT 1");
    if(!$q) json_error(mysqli_error($kon));
    $r = mysqli_fetch_assoc($q);
    if(!$r) json_error('not_found', 404);
    // iso.php expects d.title d.year d.id
    json_success(['data'=>['id'=>$r['idiso'], 'title'=>$r['nama_iso']]]);
}

if($action == 'update_iso'){
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $title = isset($_POST['title']) ? mysqli_real_escape_string($kon, $_POST['title']) : '';
    if($id<=0 || trim($title)==='') json_error('invalid');

    $upd = mysqli_query($kon, "UPDATE iso SET nama_iso='$title' WHERE idiso=$id");
    if(!$upd) json_error(mysqli_error($kon));
    json_success();
}

if($action == 'delete_iso'){
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    if($id<=0) json_error('invalid');

    // delete hierarchy: indikator -> subklausul -> klausul -> iso
    mysqli_query($kon, "DELETE i FROM iso_indikator i LEFT JOIN iso_subklausul sk ON sk.id_subklausul=i.id_subklausul LEFT JOIN iso_klausul k ON k.id_klausul=i.id_klausul WHERE sk.id_iso=i.id_iso");
    // safer explicit chain
    mysqli_query($kon, "DELETE i FROM iso_indikator i WHERE i.id_iso='$id'");
    mysqli_query($kon, "DELETE FROM iso_subklausul WHERE id_iso='$id'");
    mysqli_query($kon, "DELETE FROM iso_klausul WHERE id_iso='$id'");
    $del = mysqli_query($kon, "DELETE FROM iso WHERE idiso=$id");
    if(!$del) json_error(mysqli_error($kon));
    json_success();
}

// ---------------- KLAUSUL (iso.php tab) ----------------
if($action == 'fetch_iso_options'){
    $out = [];
    $q = mysqli_query($kon, "SELECT idiso, nama_iso FROM iso ORDER BY idiso DESC");
    while($r = mysqli_fetch_assoc($q)){
        $out[] = ['id'=>intval($r['idiso']), 'title'=>$r['nama_iso']];
    }
    json_success(['data'=>$out]);
}

if($action == 'add_klausul'){
    $iso_id = isset($_POST['iso_id']) ? intval($_POST['iso_id']) : 0;
    $klausul = isset($_POST['klausul']) ? trim($_POST['klausul']) : '';
    if($iso_id<=0 || $klausul==='') json_error('invalid');

    // iso.php modal only sends klausul text and not nomor_klausul.
    // we set nomor_klausul as max+1 for that ISO.
    $q = mysqli_query($kon, "SELECT MAX(nomor_klausul) AS mx FROM iso_klausul WHERE id_iso='$iso_id'");
    $mx = 0; if($q){ $row = mysqli_fetch_assoc($q); $mx = intval($row['mx']); }
    $nomor = $mx + 1;

    $nama_klausul = mysqli_real_escape_string($kon, $klausul);
    $ins = mysqli_query($kon, "INSERT INTO iso_klausul (id_iso, nomor_klausul, nama_klausul) VALUES ('$iso_id', $nomor, '$nama_klausul')");
    if(!$ins) json_error(mysqli_error($kon));
    json_success(['id'=>mysqli_insert_id($kon)]);
}

if($action == 'update_klausul'){
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $klausul = isset($_POST['klausul']) ? trim($_POST['klausul']) : '';
    $nomor = isset($_POST['nomor_klausul']) ? intval($_POST['nomor_klausul']) : 0;
    if($id<=0 || $klausul==='') json_error('invalid');

    $nama_klausul = mysqli_real_escape_string($kon, $klausul);
    $nomor_set = ($nomor > 0) ? ", nomor_klausul='$nomor'" : '';
    $upd = mysqli_query($kon, "UPDATE iso_klausul SET nama_klausul='$nama_klausul'$nomor_set WHERE id_klausul=$id");
    if(!$upd) json_error(mysqli_error($kon));
    json_success();
}

if($action == 'get_klausul'){
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    if($id<=0) json_error('invalid');
    $q = mysqli_query($kon, "SELECT * FROM iso_klausul WHERE id_klausul=$id LIMIT 1");
    if(!$q) json_error(mysqli_error($kon));
    $r = mysqli_fetch_assoc($q);
    if(!$r) json_error('not_found', 404);
    // iso.php expects resp.data.id, iso_id, klausul, nomor_klausul
    json_success(['data'=>['id'=>intval($r['id_klausul']), 'iso_id'=>$r['id_iso'], 'klausul'=>$r['nama_klausul'], 'nomor_klausul'=>intval($r['nomor_klausul'])]]);
}

if($action == 'delete_klausul'){
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    if($id<=0) json_error('invalid');
    // delete indicators under that klausul (via iso_indikator.id_klausul)
    mysqli_query($kon, "DELETE FROM iso_indikator WHERE id_klausul='$id'");
    // delete subklausul under that klausul
    mysqli_query($kon, "DELETE FROM iso_subklausul WHERE id_klausul='$id'");
    $del = mysqli_query($kon, "DELETE FROM iso_klausul WHERE id_klausul=$id");
    if(!$del) json_error(mysqli_error($kon));
    json_success();
}

if($action == 'fetch_klausul_table'){
    $draw = isset($_POST['draw']) ? intval($_POST['draw']) : 0;
    $start = isset($_POST['start']) ? intval($_POST['start']) : 0;
    $length = isset($_POST['length']) ? intval($_POST['length']) : 25;
    $search = isset($_POST['search']['value']) ? mysqli_real_escape_string($kon, $_POST['search']['value']) : '';

    $resTotal = mysqli_query($kon, "SELECT COUNT(*) AS cnt FROM iso_klausul");
    $total = 0; if($resTotal){ $r = mysqli_fetch_assoc($resTotal); $total = intval($r['cnt']); }

    $where = '';
    if($search !== ''){
        $where = " WHERE k.nama_klausul LIKE '%$search%' OR i.nama_iso LIKE '%$search%' OR k.nomor_klausul LIKE '%$search%' ";
    }
    $resFiltered = mysqli_query($kon, "SELECT COUNT(*) AS cnt FROM iso_klausul k LEFT JOIN iso i ON i.idiso=k.id_iso $where");
    $filtered = 0; if($resFiltered){ $r = mysqli_fetch_assoc($resFiltered); $filtered = intval($r['cnt']); }

    $limit_sql = " LIMIT $start, $length";
    $order_col = isset($_POST['order'][0]['column']) ? intval($_POST['order'][0]['column']) : -1;
    $order_dir = isset($_POST['order'][0]['dir']) && strtolower($_POST['order'][0]['dir']) === 'asc' ? 'ASC' : 'DESC';
    $order_sql = " ORDER BY k.id_klausul DESC";
    if ($order_col == 0) $order_sql = " ORDER BY i.nama_iso $order_dir";
    elseif ($order_col == 1) $order_sql = " ORDER BY k.nomor_klausul $order_dir, k.nama_klausul $order_dir";

    $rows = [];
    $q = mysqli_query($kon, "SELECT k.id_klausul, k.id_iso, i.nama_iso AS iso_title, k.nama_klausul, k.nomor_klausul
        FROM iso_klausul k LEFT JOIN iso i ON i.idiso=k.id_iso $where $order_sql $limit_sql");

    while($row = mysqli_fetch_assoc($q)){
        $id = intval($row['id_klausul']);
        $actions = '<button class="btn btn-sm btn-secondary btn-edit-klausul me-1" data-id="'.$id.'">Edit</button> '
                 .'<button class="btn btn-sm btn-danger btn-del-klausul" data-id="'.$id.'">Hapus</button>';
        $rows[] = [
            'id'=>$id,
            'iso_title'=>$row['iso_title'],
            'klausul'=>$row['nomor_klausul'].' - '.$row['nama_klausul'],
            'actions'=>$actions
        ];
    }

    json_success(['data'=>$rows,'draw'=>$draw,'recordsTotal'=>$total,'recordsFiltered'=>$filtered]);
}

// options for dropdowns
if($action == 'fetch_klausul_options'){
    $iso_id = isset($_POST['iso_id']) ? intval($_POST['iso_id']) : 0;
    $out = [];
    if($iso_id>0){
        $q = mysqli_query($kon, "SELECT id_klausul, nama_klausul, nomor_klausul FROM iso_klausul WHERE id_iso='$iso_id' ORDER BY nomor_klausul ASC, id_klausul DESC");
        while($r = mysqli_fetch_assoc($q)){
            $out[] = ['id'=>intval($r['id_klausul']), 'klausul'=>$r['nomor_klausul'].' - '.$r['nama_klausul']];
        }
    }
    json_success(['data'=>$out]);
}

// alias: iso.php subklausul/tabSubindikator memakai action fetch_klausul (bukan fetch_klausul_options)
if($action == 'fetch_klausul'){
    $iso_id = isset($_POST['iso_id']) ? intval($_POST['iso_id']) : 0;
    $out = [];
    if($iso_id>0){
        $q = mysqli_query($kon, "SELECT id_klausul, nama_klausul, nomor_klausul FROM iso_klausul WHERE id_iso='$iso_id' ORDER BY nomor_klausul ASC, id_klausul DESC");
        while($r = mysqli_fetch_assoc($q)){
            $out[] = ['id'=>intval($r['id_klausul']), 'klausul'=>$r['nomor_klausul'].' - '.$r['nama_klausul']];
        }
    }
    json_success(['data'=>$out]);
}


// ---------------- SUBKLAUSUL ----------------
if($action == 'add_subklausul'){
    $iso_id = isset($_POST['iso_id']) ? intval($_POST['iso_id']) : 0;
    $klausul_id = isset($_POST['klausul_id']) ? intval($_POST['klausul_id']) : 0;
    $nomor = isset($_POST['nomor_subklausul']) ? intval($_POST['nomor_subklausul']) : 0;
    $nama = isset($_POST['subklausul']) ? trim($_POST['subklausul']) : '';

    if($iso_id<=0 || $klausul_id<=0 || $nama==='') json_error('invalid');

    if($nomor<=0){
        $q = mysqli_query($kon, "SELECT MAX(nomor_subklausul) AS mx FROM iso_subklausul WHERE id_iso='$iso_id' AND id_klausul='$klausul_id'");
        $mx = 0; if($q){ $row = mysqli_fetch_assoc($q); $mx = intval($row['mx']); }
        $nomor = $mx+1;
    }

    $nama_esc = mysqli_real_escape_string($kon, $nama);
    $ins = mysqli_query($kon, "INSERT INTO iso_subklausul (id_iso, id_klausul, nomor_subklausul, nama_subklausul) VALUES ('$iso_id', '$klausul_id', $nomor, '$nama_esc')");
    if(!$ins) json_error(mysqli_error($kon));
    json_success(['id'=>mysqli_insert_id($kon)]);
}

if($action == 'update_subklausul'){
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $nomor = isset($_POST['nomor_subklausul']) ? intval($_POST['nomor_subklausul']) : null;
    $nama = isset($_POST['subklausul']) ? trim($_POST['subklausul']) : '';
    if($id<=0 || $nama==='') json_error('invalid');
    $nama_esc = mysqli_real_escape_string($kon, $nama);
    $set_sql = "nama_subklausul='$nama_esc'";
    if($nomor !== null){
        $set_sql .= ", nomor_subklausul=$nomor";
    }
    $upd = mysqli_query($kon, "UPDATE iso_subklausul SET $set_sql WHERE id_subklausul=$id");
    if(!$upd) json_error(mysqli_error($kon));
    json_success();
}

if($action == 'get_subklausul'){
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    if($id<=0) json_error('invalid');
    $q = mysqli_query($kon, "SELECT * FROM iso_subklausul WHERE id_subklausul=$id LIMIT 1");
    if(!$q) json_error(mysqli_error($kon));
    $r = mysqli_fetch_assoc($q);
    if(!$r) json_error('not_found', 404);
    json_success(['data'=>[
        'id'=>intval($r['id_subklausul']),
        'iso_id'=>$r['id_iso'],
        'klausul_id'=>$r['id_klausul'],
        'nomor_sub'=>$r['nomor_subklausul'],
        'subklausul'=>$r['nama_subklausul']
    ]]);
}

if($action == 'delete_subklausul'){
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    if($id<=0) json_error('invalid');
    mysqli_query($kon, "DELETE FROM iso_indikator WHERE id_subklausul='$id'");
    $del = mysqli_query($kon, "DELETE FROM iso_subklausul WHERE id_subklausul=$id");
    if(!$del) json_error(mysqli_error($kon));
    json_success();
}

if($action == 'fetch_subklausul_table'){
    $draw = isset($_POST['draw']) ? intval($_POST['draw']) : 0;
    $start = isset($_POST['start']) ? intval($_POST['start']) : 0;
    $length = isset($_POST['length']) ? intval($_POST['length']) : 25;
    $search = isset($_POST['search']['value']) ? mysqli_real_escape_string($kon, $_POST['search']['value']) : '';

    $resTotal = mysqli_query($kon, "SELECT COUNT(*) AS cnt FROM iso_subklausul");
    $total = 0; if($resTotal){ $r = mysqli_fetch_assoc($resTotal); $total = intval($r['cnt']); }

    $where = '';
    if($search !== ''){
        $where = " WHERE sk.nama_subklausul LIKE '%$search%' OR k.nama_klausul LIKE '%$search%' OR i.nama_iso LIKE '%$search%' OR sk.nomor_subklausul LIKE '%$search%' OR k.nomor_klausul LIKE '%$search%' ";
    }

    $resFiltered = mysqli_query($kon, "SELECT COUNT(*) AS cnt FROM iso_subklausul sk
        LEFT JOIN iso_klausul k ON k.id_klausul=sk.id_klausul
        LEFT JOIN iso i ON i.idiso=sk.id_iso
        $where");
    $filtered = 0; if($resFiltered){ $r = mysqli_fetch_assoc($resFiltered); $filtered = intval($r['cnt']); }

    $limit_sql = " LIMIT $start, $length";
    $order_col = isset($_POST['order'][0]['column']) ? intval($_POST['order'][0]['column']) : -1;
    $order_dir = isset($_POST['order'][0]['dir']) && strtolower($_POST['order'][0]['dir']) === 'asc' ? 'ASC' : 'DESC';
    $order_sql = " ORDER BY sk.id_subklausul DESC";
    if ($order_col == 0) $order_sql = " ORDER BY i.nama_iso $order_dir";
    elseif ($order_col == 1) $order_sql = " ORDER BY k.nomor_klausul $order_dir, k.nama_klausul $order_dir";
    elseif ($order_col == 2) $order_sql = " ORDER BY sk.nomor_subklausul $order_dir";
    elseif ($order_col == 3) $order_sql = " ORDER BY sk.nama_subklausul $order_dir";

    $rows = [];
    $q = mysqli_query($kon, "SELECT sk.id_subklausul, sk.id_iso, i.nama_iso AS iso_title, sk.id_klausul, k.nomor_klausul, k.nama_klausul,
            sk.nomor_subklausul, sk.nama_subklausul
        FROM iso_subklausul sk
        LEFT JOIN iso_klausul k ON k.id_klausul=sk.id_klausul
        LEFT JOIN iso i ON i.idiso=sk.id_iso
        $where $order_sql $limit_sql");

    while($row = mysqli_fetch_assoc($q)){
        $id = intval($row['id_subklausul']);
        $actions = '<button class="btn btn-sm btn-secondary btn-edit-subklausul me-1" data-id="'.$id.'">Edit</button> '
                 .'<button class="btn btn-sm btn-danger btn-del-subklausul" data-id="'.$id.'">Hapus</button>';

        $rows[] = [
            'id'=>$id,
            'iso_title'=>$row['iso_title'],
            'klausul'=>$row['nomor_klausul'].' - '.$row['nama_klausul'],
            'no_urut'=>$row['nomor_subklausul'],
            'subklausul'=>$row['nama_subklausul'],
            'actions'=>$actions
        ];
    }

    json_success(['data'=>$rows,'draw'=>$draw,'recordsTotal'=>$total,'recordsFiltered'=>$filtered]);
}

if($action == 'fetch_subklausul_options'){
    $iso_id = isset($_POST['iso_id']) ? intval($_POST['iso_id']) : 0;
    $klausul_id = isset($_POST['klausul_id']) ? intval($_POST['klausul_id']) : 0;
    $grouped = [];
    $sql = "SELECT s.id_subklausul, s.nomor_subklausul, s.nama_subklausul, k.id_klausul, k.nomor_klausul, k.nama_klausul FROM iso_subklausul s LEFT JOIN iso_klausul k ON k.id_klausul=s.id_klausul";
    $conds = [];
    if($iso_id>0) $conds[] = "s.id_iso='$iso_id'";
    if($klausul_id>0) $conds[] = "s.id_klausul='$klausul_id'";
    if(count($conds)){
        $sql .= " WHERE " . implode(' AND ', $conds);
    }
    $sql .= " ORDER BY k.nomor_klausul ASC, s.nomor_subklausul ASC";
    $q = mysqli_query($kon, $sql);
    while($r = mysqli_fetch_assoc($q)){
        $kid = intval($r['id_klausul']);
        if(!isset($grouped[$kid])){
            $grouped[$kid] = [
                'label' => $r['nomor_klausul'].' '.$r['nama_klausul'],
                'children' => []
            ];
        }
        $grouped[$kid]['children'][] = [
            'id' => intval($r['id_subklausul']),
            'text' => $r['nomor_klausul'].'.'.$r['nomor_subklausul'].' '.$r['nama_subklausul']
        ];
    }
    json_success(['data'=>array_values($grouped)]);
}

// ---------------- INDIKATOR ISO ----------------
if($action == 'fetch_indikator_table'){
    // For iso.php indicator tab, it expects DataTables serverSide.
    $draw = isset($_POST['draw']) ? intval($_POST['draw']) : 0;
    $start = isset($_POST['start']) ? intval($_POST['start']) : 0;
    $length = isset($_POST['length']) ? intval($_POST['length']) : 25;
    $search = isset($_POST['search']['value']) ? mysqli_real_escape_string($kon, $_POST['search']['value']) : '';

    $resTotal = mysqli_query($kon, "SELECT COUNT(*) AS cnt FROM iso_indikator");
    $total = 0; if($resTotal){ $r = mysqli_fetch_assoc($resTotal); $total = intval($r['cnt']); }

    $where = '';
    if($search !== ''){
        $where = " WHERE i.indikator LIKE '%$search%' OR sk.nama_subklausul LIKE '%$search%' OR k.nama_klausul LIKE '%$search%' OR iso.nama_iso LIKE '%$search%' OR sk.nomor_subklausul LIKE '%$search%' OR k.nomor_klausul LIKE '%$search%' ";
    }

    $resFiltered = mysqli_query($kon, "SELECT COUNT(*) AS cnt FROM iso_indikator i
        LEFT JOIN iso_klausul k ON k.id_klausul=i.id_klausul
        LEFT JOIN iso_subklausul sk ON sk.id_subklausul=i.id_subklausul
        LEFT JOIN iso iso ON iso.idiso=i.id_iso
        $where");
    $filtered = 0; if($resFiltered){ $r = mysqli_fetch_assoc($resFiltered); $filtered = intval($r['cnt']); }

    $limit_sql = " LIMIT $start, $length";
    $order_col = isset($_POST['order'][0]['column']) ? intval($_POST['order'][0]['column']) : -1;
    $order_dir = isset($_POST['order'][0]['dir']) && strtolower($_POST['order'][0]['dir']) === 'asc' ? 'ASC' : 'DESC';
    $order_sql = " ORDER BY i.id_isoindikator DESC";
    if ($order_col == 1) $order_sql = " ORDER BY iso.nama_iso $order_dir";
    elseif ($order_col == 2) $order_sql = " ORDER BY k.nomor_klausul $order_dir, sk.nomor_subklausul $order_dir";
    elseif ($order_col == 3) $order_sql = " ORDER BY i.indikator $order_dir";

    $rows = [];
    $q = mysqli_query($kon, "SELECT i.id_isoindikator, i.id_iso, iso.nama_iso AS iso_title,
            i.id_klausul, k.nomor_klausul, k.nama_klausul,
            i.id_subklausul, sk.nomor_subklausul, sk.nama_subklausul,
            i.indikator
        FROM iso_indikator i
        LEFT JOIN iso_klausul k ON k.id_klausul=i.id_klausul
        LEFT JOIN iso_subklausul sk ON sk.id_subklausul=i.id_subklausul
        LEFT JOIN iso iso ON iso.idiso=i.id_iso
        $where $order_sql $limit_sql");

    while($row = mysqli_fetch_assoc($q)){
        $id = intval($row['id_isoindikator']);
        $actions = '<button class="btn btn-sm btn-secondary btn-edit-indikator me-1" data-id="'.$id.'">Edit</button> '
                 .'<button class="btn btn-sm btn-danger btn-del-indikator" data-id="'.$id.'">Hapus</button>';

        $rows[] = [
            'id'=>$id,
            'iso_title'=>$row['iso_title'],
            'klausul'=>$row['nomor_klausul'].' - '.$row['nama_klausul'].' | '.$row['nomor_subklausul'].' - '.$row['nama_subklausul'],
            'indikator'=>$row['indikator'],
            'actions'=>$actions
        ];
    }

    echo json_encode(["draw"=>$draw,"recordsTotal"=>$total,"recordsFiltered"=>$filtered,"data"=>$rows]);
    exit;
}

if($action == 'add_indikator'){
    $klausul_id = isset($_POST['klausul_id']) ? intval($_POST['klausul_id']) : 0;
    // iso_indikator minimal: id_iso, id_klausul, id_subklausul, indikator
    $indikator = isset($_POST['indikator']) ? trim($_POST['indikator']) : '';
    // iso.php modal for indikator iso currently only sends klausul_id + indikator.
    // We'll insert with id_subklausul = 0 won't work because schema requires varchar(10) NOT NULL.
    // Therefore we require subklausul selection via tabSubindikator flow is more complex.
    // For now: derive id_iso and use first subklausul under that klausul.

    if($klausul_id<=0 || $indikator==='') json_error('invalid');

    $q = mysqli_query($kon, "SELECT id_iso FROM iso_klausul WHERE id_klausul=$klausul_id LIMIT 1");
    $iso_id = 0; if($q){ $r = mysqli_fetch_assoc($q); $iso_id = intval($r['id_iso']); }
    if($iso_id<=0) json_error('klausul_not_found');

    $q2 = mysqli_query($kon, "SELECT id_subklausul FROM iso_subklausul WHERE id_iso='$iso_id' AND id_klausul='$klausul_id' ORDER BY nomor_subklausul ASC, id_subklausul DESC LIMIT 1");
    $sub_id = null; if($q2){ $r2 = mysqli_fetch_assoc($q2); $sub_id = $r2 ? $r2['id_subklausul'] : null; }
    if(!$sub_id) json_error('subklausul_required');

    $indikator_esc = mysqli_real_escape_string($kon, $indikator);
    $ins = mysqli_query($kon, "INSERT INTO iso_indikator (id_iso, id_klausul, id_subklausul, indikator, status) VALUES ('$iso_id', '$klausul_id', '$sub_id', '$indikator_esc', 1)");
    if(!$ins) json_error(mysqli_error($kon));
    json_success(['id'=>mysqli_insert_id($kon)]);
}

if($action == 'update_indikator'){
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $klausul_id = isset($_POST['klausul_id']) ? intval($_POST['klausul_id']) : 0;
    $indikator = isset($_POST['indikator']) ? trim($_POST['indikator']) : '';
    // tabIndikator in iso.php uses klausul_id + indikator
    if($id<=0 || $klausul_id<=0 || $indikator==='') json_error('invalid');

    $indikator_esc = mysqli_real_escape_string($kon, $indikator);
    $upd = mysqli_query($kon, "UPDATE iso_indikator SET id_klausul='$klausul_id', indikator='$indikator_esc' WHERE id_isoindikator=$id");
    if(!$upd) json_error(mysqli_error($kon));
    json_success();
}

if($action == 'get_indikator'){
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    if($id<=0) json_error('invalid');
    $q = mysqli_query($kon, "SELECT * FROM iso_indikator WHERE id_isoindikator=$id LIMIT 1");
    if(!$q) json_error(mysqli_error($kon));
    $r = mysqli_fetch_assoc($q);
    if(!$r) json_error('not_found', 404);
    json_success(['data'=>[
        'id'=>intval($r['id_isoindikator']),
        'iso_id'=>$r['id_iso'],
        'klausul_id'=>$r['id_klausul'],
        'indikator'=>$r['indikator'],
        'subklausul_id'=>$r['id_subklausul']
    ]]);
}

if($action == 'delete_indikator'){
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    if($id<=0) json_error('invalid');
    $del = mysqli_query($kon, "DELETE FROM iso_indikator WHERE id_isoindikator=$id");
    if(!$del) json_error(mysqli_error($kon));
    json_success();
}

if($action == 'fetch_klausul'){
    $iso_id = isset($_POST['iso_id']) ? intval($_POST['iso_id']) : 0;
    $out = [];
    if($iso_id>0){
        $q = mysqli_query($kon, "SELECT id_klausul, nama_klausul, nomor_klausul FROM iso_klausul WHERE id_iso='$iso_id' ORDER BY nomor_klausul ASC");
        while($r = mysqli_fetch_assoc($q)){
            $out[] = ['id'=>intval($r['id_klausul']), 'klausul'=>$r['nomor_klausul'].' - '.$r['nama_klausul']];
        }
    }
    json_success(['data'=>$out]);
}

// ---------------- Subindikator (placeholder mapping to iso_indikator) ----------------
// iso.php tabSubindikator memakai nama/aksi seperti sarmut subindikator.
// Untuk tetap jalan cepat, map:
// subindikator => indikator (text) pada iso_indikator
// id_subindikator/jenis/satuan/target/baseline/units -> tidak ada di iso.sql, jadi tidak didukung.

if($action == 'fetch_unit_options'){
    // unit tabel dipakai di sarmut; iso.sql tidak punya pivot unit. return empty.
    json_success(['data'=>[]]);
}

if($action == 'fetch_indikator'){
    $klausul_id = isset($_POST['klausul_id']) ? intval($_POST['klausul_id']) : 0;
    $out = [];
    if($klausul_id>0){
        $q = mysqli_query($kon, "SELECT id_isoindikator, indikator FROM iso_indikator WHERE id_klausul='$klausul_id' ORDER BY id_isoindikator DESC");
        while($r = mysqli_fetch_assoc($q)){
            $out[] = ['id'=>intval($r['id_isoindikator']), 'indikator'=>$r['indikator']];
        }
    }
    json_success(['success'=>true,'data'=>$out]);
}

if($action == 'fetch_subindikator_table'){
    // Minimal: show iso_indikator as subindikatorTable columns subset.
    $draw = isset($_POST['draw']) ? intval($_POST['draw']) : 0;
    $start = isset($_POST['start']) ? intval($_POST['start']) : 0;
    $length = isset($_POST['length']) ? intval($_POST['length']) : 25;
    $search = isset($_POST['search']['value']) ? mysqli_real_escape_string($kon, $_POST['search']['value']) : '';

    $resTotal = mysqli_query($kon, "SELECT COUNT(*) AS cnt FROM iso_indikator");
    $total = 0; if($resTotal){ $r = mysqli_fetch_assoc($resTotal); $total = intval($r['cnt']); }

    $having = '';
    if($search !== ''){
        $having = " HAVING i.indikator LIKE '%$search%' OR klausul_list_search LIKE '%$search%' ";
    }

    $base_sql = "SELECT i.id_isoindikator, i.indikator,
            GROUP_CONCAT(DISTINCT CONCAT(k.nomor_klausul, '.', sk.nomor_subklausul) ORDER BY k.nomor_klausul, sk.nomor_subklausul SEPARATOR ',') AS klausul_list,
            GROUP_CONCAT(DISTINCT CONCAT(k.nomor_klausul, '.', sk.nomor_subklausul, ' ', sk.nama_subklausul) SEPARATOR ' ') AS klausul_list_search
        FROM iso_indikator i
        LEFT JOIN iso_subklausul sk ON FIND_IN_SET(sk.id_subklausul, REPLACE(i.id_subklausul, ' ', ''))
        LEFT JOIN iso_klausul k ON k.id_klausul = sk.id_klausul
        GROUP BY i.id_isoindikator
        $having";

    $resFiltered = mysqli_query($kon, "SELECT COUNT(*) AS cnt FROM ($base_sql) t");
    $filtered = 0; if($resFiltered){ $r = mysqli_fetch_assoc($resFiltered); $filtered = intval($r['cnt']); }

    $limit_sql = " LIMIT $start, $length";
    $order_col = isset($_POST['order'][0]['column']) ? intval($_POST['order'][0]['column']) : -1;
    $order_dir = isset($_POST['order'][0]['dir']) && strtolower($_POST['order'][0]['dir']) === 'asc' ? 'ASC' : 'DESC';
    $order_sql = " ORDER BY id_isoindikator DESC";
    if ($order_col == 2) $order_sql = " ORDER BY indikator $order_dir";

    $rows = [];
    $q = mysqli_query($kon, "$base_sql $order_sql $limit_sql");

    while($row = mysqli_fetch_assoc($q)){
        $id = intval($row['id_isoindikator']);
        $badges = [];
        if($row['klausul_list']){
            foreach(explode(',', $row['klausul_list']) as $num){
                $num = trim($num);
                if($num === '') continue;
                $badges[] = '<span class="badge bg-primary me-1 mb-1">'.htmlspecialchars($num).'</span>';
            }
        }
        $actions = '<button class="btn btn-sm btn-secondary btn-edit-subindikator me-1" data-id="'.$id.'">Edit</button>'
                 . '<button class="btn btn-sm btn-danger btn-del-subindikator" data-id="'.$id.'">Hapus</button>';
        $rows[] = [
            'id'=>$id,
            'klausul'=>implode('', $badges),
            'indikator'=>$row['indikator'],
            'actions'=>$actions
        ];
    }

    echo json_encode(["draw"=>$draw,"recordsTotal"=>$total,"recordsFiltered"=>$filtered,"data"=>$rows]);
    exit;
}

if($action == 'get_subindikator'){
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    if($id<=0) json_error('invalid');
    $q = mysqli_query($kon, "SELECT * FROM iso_indikator WHERE id_isoindikator=$id LIMIT 1");
    if(!$q) json_error(mysqli_error($kon));
    $r = mysqli_fetch_assoc($q);
    if(!$r) json_error('not_found', 404);

    $sub_ids = array_filter(array_unique(array_map('intval', explode(',', $r['id_subklausul']))));
    $iso_id = 0;
    if(!empty($sub_ids)){
        $first_sub = reset($sub_ids);
        $q2 = mysqli_query($kon, "SELECT id_iso FROM iso_subklausul WHERE id_subklausul=$first_sub LIMIT 1");
        if($q2){ $r2 = mysqli_fetch_assoc($q2); if($r2) $iso_id = intval($r2['id_iso']); }
    }

    json_success(['data'=>[
        'id'=>intval($r['id_isoindikator']),
        'iso_id'=>$iso_id,
        'subklausul_ids'=>$sub_ids,
        'subindikator'=>$r['indikator']
    ]]);
}

if($action == 'add_subindikator'){
    $subklausul_ids = isset($_POST['subklausul_ids']) && is_array($_POST['subklausul_ids']) ? $_POST['subklausul_ids'] : [];
    $subindikator = isset($_POST['indikator']) ? trim($_POST['indikator']) : '';
    if(empty($subklausul_ids) || $subindikator==='') json_error('invalid');

    $subklausul_ids = array_values(array_unique(array_filter(array_map('intval', $subklausul_ids))));
    if(empty($subklausul_ids)) json_error('invalid');

    $id_subklausul = implode(',', $subklausul_ids);
    $subindikator_esc = mysqli_real_escape_string($kon, $subindikator);
    $ins = mysqli_query($kon, "INSERT INTO iso_indikator (id_iso, id_klausul, id_subklausul, indikator, status) VALUES ('', '', '$id_subklausul', '$subindikator_esc', 1)");
    if(!$ins) json_error(mysqli_error($kon));
    json_success(['id'=>mysqli_insert_id($kon)]);
}

if($action == 'update_subindikator'){
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $subklausul_ids = isset($_POST['subklausul_ids']) && is_array($_POST['subklausul_ids']) ? $_POST['subklausul_ids'] : [];
    $subindikator = isset($_POST['indikator']) ? trim($_POST['indikator']) : '';
    if($id<=0 || empty($subklausul_ids) || $subindikator==='') json_error('invalid');

    $subklausul_ids = array_values(array_unique(array_filter(array_map('intval', $subklausul_ids))));
    if(empty($subklausul_ids)) json_error('invalid');

    $id_subklausul = implode(',', $subklausul_ids);
    $subindikator_esc = mysqli_real_escape_string($kon, $subindikator);
    $upd = mysqli_query($kon, "UPDATE iso_indikator SET id_iso='', id_klausul='', id_subklausul='$id_subklausul', indikator='$subindikator_esc' WHERE id_isoindikator=$id");
    if(!$upd) json_error(mysqli_error($kon));
    json_success();
}

if($action == 'delete_subindikator'){
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    if($id<=0) json_error('invalid');
    $del = mysqli_query($kon, "DELETE FROM iso_indikator WHERE id_isoindikator=$id");
    if(!$del) json_error(mysqli_error($kon));
    json_success();
}

// options used by iso.php (klausul->indikator) chaining
if($action == 'fetch_klausul_options_subklausul_for_modal'){ json_error('not_implemented'); }

json_error('unknown_action');
