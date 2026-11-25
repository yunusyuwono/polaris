<?php
header('Content-Type: application/json; charset=utf-8');
include 'koneksi.php';

$action = isset($_GET['action'])?$_GET['action']:'fetch';

if($action == 'fetch'){
    $draw = isset($_POST['draw'])?intval($_POST['draw']):0;
    $start = isset($_POST['start'])?intval($_POST['start']):0;
    $length = isset($_POST['length'])?intval($_POST['length']):25;
    $search = isset($_POST['search']['value'])?mysqli_real_escape_string($kon,$_POST['search']['value']):'';
    $order_col = isset($_POST['order'][0]['column'])?intval($_POST['order'][0]['column']):2;
    $order_dir = isset($_POST['order'][0]['dir'])?$_POST['order'][0]['dir']:'desc';

    $columns = [0=>'id',1=>'title',2=>'year'];
    $order_field = isset($columns[$order_col])?$columns[$order_col]:'year';

    // total
    $resTotal = mysqli_query($kon, "SELECT COUNT(*) AS cnt FROM sarmut");
    $total = 0; if($resTotal){ $r = mysqli_fetch_assoc($resTotal); $total = intval($r['cnt']); }

    // filter
    $where = '';
    if($search !== ''){
        $s = "%".$search."%";
        $where = " WHERE title LIKE '".mysqli_real_escape_string($kon,$s)."' ";
    }

    $resFiltered = mysqli_query($kon, "SELECT COUNT(*) AS cnt FROM sarmut $where");
    $filtered = 0; if($resFiltered){ $r = mysqli_fetch_assoc($resFiltered); $filtered = intval($r['cnt']); }

    $order_sql = " ORDER BY $order_field ".($order_dir==='asc'?'ASC':'DESC');
    $limit_sql = " LIMIT $start, $length";

    $rows = [];
    $q = mysqli_query($kon, "SELECT * FROM sarmut $where $order_sql $limit_sql");
    while($row = mysqli_fetch_assoc($q)){
        $id = intval($row['id']);
        $actions = '<button class="btn btn-sm btn-primary btn-edit-sarmut" data-id="'.$id.'" type="button">Edit</button> '
                 .'<button class="btn btn-sm btn-danger btn-del-sarmut" data-id="'.$id.'" type="button">Hapus</button> '
                 .'<button class="btn btn-sm btn-outline-primary btn-add-sasaran" data-id="'.$id.'" type="button">Input Sasaran</button> '
                 .'<a class="btn btn-sm btn-info" href="sarmut.monitor.php?sarmut_id='.$id.'">Monitoring</a> '
                 .'<a class="btn btn-sm btn-secondary" href="sarmut.laporan.php?sarmut_id='.$id.'">Laporan</a>';
        $rows[] = ['id'=>$id,'title'=>$row['title'],'year'=>$row['year'],'actions'=>$actions];
    }

    echo json_encode(["draw"=>$draw,"recordsTotal"=>$total,"recordsFiltered"=>$filtered,"data"=>$rows]);
    exit;
}

if($action == 'sarmut_save'){
    $raw = isset($_POST['data'])?$_POST['data']:null;
    if(!$raw){ echo json_encode(['success'=>false,'error'=>'no_data']); exit; }
    $payload = json_decode($raw, true);
    if(!$payload || !isset($payload['title'])){ echo json_encode(['success'=>false,'error'=>'invalid_data']); exit; }

    $title = mysqli_real_escape_string($kon, $payload['title']);
    $year = intval($payload['year']); if($year<=0) $year = intval(date('Y'));

    $now = date('Y-m-d H:i:s');
    $ins = mysqli_query($kon, "INSERT INTO sarmut (title, year, created_at) VALUES ('$title', $year, '$now')");
    if(!$ins){ echo json_encode(['success'=>false,'error'=>mysqli_error($kon)]); exit; }
    $sarmut_id = mysqli_insert_id($kon);

    if(!empty($payload['sasaran']) && is_array($payload['sasaran'])){
        foreach($payload['sasaran'] as $s){
            $sas = isset($s['sasaran'])?mysqli_real_escape_string($kon,$s['sasaran']):'';
            $insS = mysqli_query($kon, "INSERT INTO sarmut_sasaran (sarmut_id, sasaran, created_at) VALUES ($sarmut_id, '$sas', '$now')");
            if(!$insS) continue;
            $sasaran_id = mysqli_insert_id($kon);
            if(!empty($s['indikator']) && is_array($s['indikator'])){
                foreach($s['indikator'] as $ind){
                    $indi = isset($ind['indikator'])?mysqli_real_escape_string($kon,$ind['indikator']):'';
                    $insI = mysqli_query($kon, "INSERT INTO sarmut_indikator (sasaran_id, indikator) VALUES ($sasaran_id, '$indi')");
                    if(!$insI) continue;
                    $indikator_id = mysqli_insert_id($kon);
                    if(!empty($ind['subindikator']) && is_array($ind['subindikator'])){
                        foreach($ind['subindikator'] as $sub){
                            $subi = mysqli_real_escape_string($kon,$sub);
                            mysqli_query($kon, "INSERT INTO sarmut_subindikator (indikator_id, subindikator) VALUES ($indikator_id, '$subi')");
                        }
                    }
                }
            }
        }
    }

    echo json_encode(['success'=>true,'id'=>$sarmut_id]);
    exit;
}

// add single sasaran
if($action == 'add_sasaran'){
    $sarmut_id = isset($_POST['sarmut_id'])?intval($_POST['sarmut_id']):0;
    $sasaran = isset($_POST['sasaran'])?mysqli_real_escape_string($kon,$_POST['sasaran']):'';
    $jenis_sarmut = isset($_POST['jenis_sarmut'])?sarmut_sql_val($kon,$_POST['jenis_sarmut']):'NULL';
    if($sarmut_id<=0 || $sasaran===''){
        echo json_encode(['success'=>false,'error'=>'invalid']); exit;
    }
    $now = date('Y-m-d H:i:s');
    $ins = mysqli_query($kon, "INSERT INTO sarmut_sasaran (sarmut_id, sasaran, jenis_sarmut, created_at) VALUES ($sarmut_id, '$sasaran', $jenis_sarmut, '$now')");
    if(!$ins){ echo json_encode(['success'=>false,'error'=>mysqli_error($kon)]); exit; }
    echo json_encode(['success'=>true,'id'=>mysqli_insert_id($kon)]); exit;
}

// add indikator under sasaran
if($action == 'add_indikator'){
    $sasaran_id = isset($_POST['sasaran_id'])?intval($_POST['sasaran_id']):0;
    $indikator = isset($_POST['indikator'])?mysqli_real_escape_string($kon,$_POST['indikator']):'';
    if($sasaran_id<=0 || $indikator==='') { echo json_encode(['success'=>false,'error'=>'invalid']); exit; }
    $ins = mysqli_query($kon, "INSERT INTO sarmut_indikator (sasaran_id, indikator) VALUES ($sasaran_id, '$indikator')");
    if(!$ins){ echo json_encode(['success'=>false,'error'=>mysqli_error($kon)]); exit; }
    echo json_encode(['success'=>true,'id'=>mysqli_insert_id($kon)]); exit;
}

function sarmut_sql_val($kon, $val){
    if($val === null || $val === '') return 'NULL';
    return "'".mysqli_real_escape_string($kon, $val)."'";
}

function sarmut_save_subindikator_units($kon, $subindikator_id, $units){
    $subindikator_id = intval($subindikator_id);
    mysqli_query($kon, "DELETE FROM sarmut_subindikator_unit WHERE subindikator_id=$subindikator_id");
    if(!is_array($units)) return;
    foreach($units as $uid){
        $uid = intval($uid);
        if($uid > 0){
            mysqli_query($kon, "INSERT INTO sarmut_subindikator_unit (subindikator_id, unit_id) VALUES ($subindikator_id, $uid)");
        }
    }
}

// add subindikator under indikator
if($action == 'add_subindikator'){
    $indikator_id = isset($_POST['indikator_id'])?intval($_POST['indikator_id']):0;
    $sub = isset($_POST['subindikator'])?trim($_POST['subindikator']):'';
    $jenis = isset($_POST['jenis'])?trim($_POST['jenis']):'';
    $satuan = isset($_POST['satuan'])?trim($_POST['satuan']):'';
    $target = isset($_POST['target'])?trim($_POST['target']):'';
    $baseline = isset($_POST['baseline'])?trim($_POST['baseline']):'';
    $units = isset($_POST['units'])?$_POST['units']:[];
    if(is_string($units)){
        $decoded = json_decode($units, true);
        $units = is_array($decoded) ? $decoded : [];
    }
    if($indikator_id<=0 || $sub==='') { echo json_encode(['success'=>false,'error'=>'invalid']); exit; }
    $sub_esc = mysqli_real_escape_string($kon, $sub);
    $ins = mysqli_query($kon, "INSERT INTO sarmut_subindikator (indikator_id, subindikator, jenis, satuan, target, baseline) VALUES ($indikator_id, '$sub_esc', ".sarmut_sql_val($kon,$jenis).", ".sarmut_sql_val($kon,$satuan).", ".sarmut_sql_val($kon,$target).", ".sarmut_sql_val($kon,$baseline).")");
    if(!$ins){ echo json_encode(['success'=>false,'error'=>mysqli_error($kon)]); exit; }
    $sub_id = mysqli_insert_id($kon);
    sarmut_save_subindikator_units($kon, $sub_id, $units);
    echo json_encode(['success'=>true,'id'=>$sub_id]); exit;
}

// fetch sasaran list for a sarmut
if($action == 'fetch_sasaran'){
    $sarmut_id = isset($_POST['sarmut_id'])?intval($_POST['sarmut_id']):0;
    $out = [];
    if($sarmut_id>0){
        $q = mysqli_query($kon, "SELECT id, sasaran FROM sarmut_sasaran WHERE sarmut_id=$sarmut_id ORDER BY id ASC");
        while($r=mysqli_fetch_assoc($q)) $out[]=$r;
    }
    echo json_encode(['success'=>true,'data'=>$out]); exit;
}

// fetch indikator list for a sasaran
if($action == 'fetch_indikator'){
    $sasaran_id = isset($_POST['sasaran_id'])?intval($_POST['sasaran_id']):0;
    $out = [];
    if($sasaran_id>0){
        $q = mysqli_query($kon, "SELECT id, indikator FROM sarmut_indikator WHERE sasaran_id=$sasaran_id ORDER BY id ASC");
        while($r=mysqli_fetch_assoc($q)) $out[]=$r;
    }
    echo json_encode(['success'=>true,'data'=>$out]); exit;
}

// fetch sarmut options
if($action == 'fetch_sarmut_options'){
    $out=[];
    $q = mysqli_query($kon, "SELECT id, title FROM sarmut ORDER BY year DESC, id DESC");
    while($r=mysqli_fetch_assoc($q)) $out[]=$r;
    echo json_encode(['success'=>true,'data'=>$out]); exit;
}

// server-side fetch for sasaran table
if($action == 'fetch_sasaran_table'){
    $draw = isset($_POST['draw'])?intval($_POST['draw']):0;
    $start = isset($_POST['start'])?intval($_POST['start']):0;
    $length = isset($_POST['length'])?intval($_POST['length']):25;
    $search = isset($_POST['search']['value'])?mysqli_real_escape_string($kon,$_POST['search']['value']):'';

    // total
    $resTotal = mysqli_query($kon, "SELECT COUNT(*) AS cnt FROM sarmut_sasaran");
    $total = 0; if($resTotal){ $r = mysqli_fetch_assoc($resTotal); $total = intval($r['cnt']); }

    $where = '';
    if($search !== ''){
        $s = "%". $search ."%";
        $where = " WHERE ss.sasaran LIKE '".mysqli_real_escape_string($kon,$s)."' OR s.title LIKE '".mysqli_real_escape_string($kon,$s)."' ";
    }

    $resFiltered = mysqli_query($kon, "SELECT COUNT(*) AS cnt FROM sarmut_sasaran ss LEFT JOIN sarmut s ON s.id=ss.sarmut_id $where");
    $filtered = 0; if($resFiltered){ $r = mysqli_fetch_assoc($resFiltered); $filtered = intval($r['cnt']); }

    $order_sql = " ORDER BY ss.id DESC";
    $limit_sql = " LIMIT $start, $length";

    $rows = [];
    $q = mysqli_query($kon, "SELECT ss.id, ss.sarmut_id, ss.sasaran, ss.jenis_sarmut, s.title AS sarmut_title FROM sarmut_sasaran ss LEFT JOIN sarmut s ON s.id=ss.sarmut_id $where $order_sql $limit_sql");
    while($row = mysqli_fetch_assoc($q)){
        $id = intval($row['id']);
        $actions = '<button class="btn btn-sm btn-secondary btn-edit-sasaran me-1" data-id="'.$id.'">Edit</button>'
             .'<button class="btn btn-sm btn-danger btn-del-sasaran" data-id="'.$id.'">Hapus</button>';
        $rows[] = ['id'=>$id,'sarmut_title'=>$row['sarmut_title'],'sasaran'=>$row['sasaran'],'jenis_sarmut'=>$row['jenis_sarmut'],'actions'=>$actions];
    }

    echo json_encode(["draw"=>$draw,"recordsTotal"=>$total,"recordsFiltered"=>$filtered,"data"=>$rows]);
    exit;
}

// delete sasaran
if($action == 'delete_sasaran'){
    $id = isset($_POST['id'])?intval($_POST['id']):0;
    if($id<=0){ echo json_encode(['success'=>false,'error'=>'invalid']); exit; }
    $del = mysqli_query($kon, "DELETE FROM sarmut_sasaran WHERE id=$id");
    if(!$del) { echo json_encode(['success'=>false,'error'=>mysqli_error($kon)]); exit; }
    echo json_encode(['success'=>true]); exit;
}

// get single sarmut
if($action == 'get_sarmut'){
    $id = isset($_POST['id'])?intval($_POST['id']):0;
    if($id<=0){ echo json_encode(['success'=>false,'error'=>'invalid']); exit; }
    $q = mysqli_query($kon, "SELECT * FROM sarmut WHERE id=$id LIMIT 1");
    if(!$q){ echo json_encode(['success'=>false,'error'=>mysqli_error($kon)]); exit; }
    $r = mysqli_fetch_assoc($q);
    echo json_encode(['success'=>true,'data'=>$r]); exit;
}

// update sarmut
if($action == 'update_sarmut'){
    $id = isset($_POST['id'])?intval($_POST['id']):0;
    $title = isset($_POST['title'])?mysqli_real_escape_string($kon,$_POST['title']):'';
    $year = isset($_POST['year'])?intval($_POST['year']):0;
    if($id<=0 || $title===''){ echo json_encode(['success'=>false,'error'=>'invalid']); exit; }
    $upd = mysqli_query($kon, "UPDATE sarmut SET title='$title', year=$year WHERE id=$id");
    if(!$upd){ echo json_encode(['success'=>false,'error'=>mysqli_error($kon)]); exit; }
    echo json_encode(['success'=>true]); exit;
}

// delete sarmut (and related children)
if($action == 'delete_sarmut'){
    $id = isset($_POST['id'])?intval($_POST['id']):0;
    if($id<=0){ echo json_encode(['success'=>false,'error'=>'invalid']); exit; }
    // delete subindikator -> indikator -> sasaran -> sarmut
    mysqli_query($kon, "DELETE sub FROM sarmut_subindikator sub LEFT JOIN sarmut_indikator i ON i.id=sub.indikator_id LEFT JOIN sarmut_sasaran ss ON ss.id=i.sasaran_id WHERE ss.sarmut_id=$id");
    mysqli_query($kon, "DELETE i FROM sarmut_indikator i LEFT JOIN sarmut_sasaran ss ON ss.id=i.sasaran_id WHERE ss.sarmut_id=$id");
    mysqli_query($kon, "DELETE FROM sarmut_sasaran WHERE sarmut_id=$id");
    $del = mysqli_query($kon, "DELETE FROM sarmut WHERE id=$id");
    if(!$del){ echo json_encode(['success'=>false,'error'=>mysqli_error($kon)]); exit; }
    echo json_encode(['success'=>true]); exit;
}

// update sasaran
if($action == 'update_sasaran'){
    $id = isset($_POST['id'])?intval($_POST['id']):0;
    $sas = isset($_POST['sasaran'])?mysqli_real_escape_string($kon,$_POST['sasaran']):'';
    $jenis_sarmut = isset($_POST['jenis_sarmut'])?sarmut_sql_val($kon,$_POST['jenis_sarmut']):'NULL';
    if($id<=0 || $sas===''){ echo json_encode(['success'=>false,'error'=>'invalid']); exit; }
    $u = mysqli_query($kon, "UPDATE sarmut_sasaran SET sasaran='$sas', jenis_sarmut=$jenis_sarmut WHERE id=$id");
    if(!$u){ echo json_encode(['success'=>false,'error'=>mysqli_error($kon)]); exit; }
    echo json_encode(['success'=>true]); exit;
}

// get single sasaran
if($action == 'get_sasaran'){
    $id = isset($_POST['id'])?intval($_POST['id']):0;
    if($id<=0){ echo json_encode(['success'=>false,'error'=>'invalid']); exit; }
    $q = mysqli_query($kon, "SELECT * FROM sarmut_sasaran WHERE id=$id LIMIT 1");
    if(!$q){ echo json_encode(['success'=>false,'error'=>mysqli_error($kon)]); exit; }
    $r = mysqli_fetch_assoc($q);
    echo json_encode(['success'=>true,'data'=>$r]); exit;
}

if($action == 'fetch_sasaran_options'){
    $sarmut_id = isset($_POST['sarmut_id'])?intval($_POST['sarmut_id']):0;
    $out = [];
    if($sarmut_id>0){
        $q = mysqli_query($kon, "SELECT id, sasaran FROM sarmut_sasaran WHERE sarmut_id=$sarmut_id ORDER BY id ASC");
        while($r=mysqli_fetch_assoc($q)) $out[]=$r;
    }
    echo json_encode(['success'=>true,'data'=>$out]); exit;
}

// server-side fetch for indikator table
if($action == 'fetch_indikator_table'){
    $draw = isset($_POST['draw'])?intval($_POST['draw']):0;
    $start = isset($_POST['start'])?intval($_POST['start']):0;
    $length = isset($_POST['length'])?intval($_POST['length']):25;
    $search = isset($_POST['search']['value'])?mysqli_real_escape_string($kon,$_POST['search']['value']):'';

    $resTotal = mysqli_query($kon, "SELECT COUNT(*) AS cnt FROM sarmut_indikator");
    $total = 0; if($resTotal){ $r = mysqli_fetch_assoc($resTotal); $total = intval($r['cnt']); }

    $where = '';
    if($search !== ''){
        $s = "%".$search."%";
        $where = " WHERE i.indikator LIKE '".mysqli_real_escape_string($kon,$s)."' OR ss.sasaran LIKE '".mysqli_real_escape_string($kon,$s)."' OR s.title LIKE '".mysqli_real_escape_string($kon,$s)."' ";
    }

    $resFiltered = mysqli_query($kon, "SELECT COUNT(*) AS cnt FROM sarmut_indikator i LEFT JOIN sarmut_sasaran ss ON ss.id=i.sasaran_id LEFT JOIN sarmut s ON s.id=ss.sarmut_id $where");
    $filtered = 0; if($resFiltered){ $r = mysqli_fetch_assoc($resFiltered); $filtered = intval($r['cnt']); }

    $order_sql = " ORDER BY i.id DESC";
    $limit_sql = " LIMIT $start, $length";

    $rows = [];
    $q = mysqli_query($kon, "SELECT i.id, i.indikator, ss.sasaran, s.title AS sarmut_title FROM sarmut_indikator i LEFT JOIN sarmut_sasaran ss ON ss.id=i.sasaran_id LEFT JOIN sarmut s ON s.id=ss.sarmut_id $where $order_sql $limit_sql");
    while($row = mysqli_fetch_assoc($q)){
        $id = intval($row['id']);
        $actions = '<button class="btn btn-sm btn-secondary btn-edit-indikator me-1" data-id="'.$id.'">Edit</button>'
             .'<button class="btn btn-sm btn-danger btn-del-indikator" data-id="'.$id.'">Hapus</button>';
        $rows[] = ['id'=>$id,'sarmut_title'=>$row['sarmut_title'],'sasaran'=>$row['sasaran'],'indikator'=>$row['indikator'],'actions'=>$actions];
    }

    echo json_encode(["draw"=>$draw,"recordsTotal"=>$total,"recordsFiltered"=>$filtered,"data"=>$rows]);
    exit;
}

// delete indikator
if($action == 'delete_indikator'){
    $id = isset($_POST['id'])?intval($_POST['id']):0;
    if($id<=0){ echo json_encode(['success'=>false,'error'=>'invalid']); exit; }
    mysqli_query($kon, "DELETE su FROM sarmut_subindikator_unit su INNER JOIN sarmut_subindikator sub ON sub.id=su.subindikator_id WHERE sub.indikator_id=$id");
    mysqli_query($kon, "DELETE FROM sarmut_subindikator WHERE indikator_id=$id");
    $del = mysqli_query($kon, "DELETE FROM sarmut_indikator WHERE id=$id");
    if(!$del){ echo json_encode(['success'=>false,'error'=>mysqli_error($kon)]); exit; }
    echo json_encode(['success'=>true]); exit;
}

// get single indikator
if($action == 'get_indikator'){
    $id = isset($_POST['id'])?intval($_POST['id']):0;
    if($id<=0){ echo json_encode(['success'=>false,'error'=>'invalid']); exit; }
    $q = mysqli_query($kon, "SELECT i.id, i.sasaran_id, i.indikator, ss.sarmut_id, ss.sasaran FROM sarmut_indikator i LEFT JOIN sarmut_sasaran ss ON ss.id=i.sasaran_id WHERE i.id=$id LIMIT 1");
    if(!$q){ echo json_encode(['success'=>false,'error'=>mysqli_error($kon)]); exit; }
    $r = mysqli_fetch_assoc($q);
    if(!$r){ echo json_encode(['success'=>false,'error'=>'not_found']); exit; }
    echo json_encode(['success'=>true,'data'=>$r]); exit;
}

// update indikator
if($action == 'update_indikator'){
    $id = isset($_POST['id'])?intval($_POST['id']):0;
    $sasaran_id = isset($_POST['sasaran_id'])?intval($_POST['sasaran_id']):0;
    $indikator = isset($_POST['indikator'])?trim($_POST['indikator']):'';
    if($id<=0 || $sasaran_id<=0 || $indikator===''){ echo json_encode(['success'=>false,'error'=>'invalid']); exit; }
    $ind_esc = mysqli_real_escape_string($kon, $indikator);
    $upd = mysqli_query($kon, "UPDATE sarmut_indikator SET sasaran_id=$sasaran_id, indikator='$ind_esc' WHERE id=$id");
    if(!$upd){ echo json_encode(['success'=>false,'error'=>mysqli_error($kon)]); exit; }
    echo json_encode(['success'=>true]); exit;
}

// fetch unit options
if($action == 'fetch_unit_options'){
    $out = [];
    $q = mysqli_query($kon, "SELECT id, nama FROM unit ORDER BY nama ASC");
    while($r = mysqli_fetch_assoc($q)) $out[] = $r;
    echo json_encode(['success'=>true,'data'=>$out]); exit;
}

// fetch subindikator list for an indikator
if($action == 'fetch_subindikator'){
    $indikator_id = isset($_POST['indikator_id'])?intval($_POST['indikator_id']):0;
    $out = [];
    if($indikator_id>0){
        $q = mysqli_query($kon, "SELECT id, subindikator, jenis, satuan, target, baseline FROM sarmut_subindikator WHERE indikator_id=$indikator_id ORDER BY id ASC");
        while($r=mysqli_fetch_assoc($q)) $out[]=$r;
    }
    echo json_encode(['success'=>true,'data'=>$out]); exit;
}

// server-side fetch for subindikator table
if($action == 'fetch_subindikator_table'){
    $draw = isset($_POST['draw'])?intval($_POST['draw']):0;
    $start = isset($_POST['start'])?intval($_POST['start']):0;
    $length = isset($_POST['length'])?intval($_POST['length']):25;
    $search = isset($_POST['search']['value'])?mysqli_real_escape_string($kon,$_POST['search']['value']):'';

    $resTotal = mysqli_query($kon, "SELECT COUNT(*) AS cnt FROM sarmut_subindikator");
    $total = 0; if($resTotal){ $r = mysqli_fetch_assoc($resTotal); $total = intval($r['cnt']); }

    $where = '';
    if($search !== ''){
        $s = "%".$search."%";
        $esc = mysqli_real_escape_string($kon, $s);
        $where = " WHERE sub.subindikator LIKE '$esc' OR i.indikator LIKE '$esc' OR ss.sasaran LIKE '$esc' OR s.title LIKE '$esc' OR sub.jenis LIKE '$esc' OR u.nama LIKE '$esc' ";
    }

    $baseFrom = " FROM sarmut_subindikator sub
        LEFT JOIN sarmut_indikator i ON i.id=sub.indikator_id
        LEFT JOIN sarmut_sasaran ss ON ss.id=i.sasaran_id
        LEFT JOIN sarmut s ON s.id=ss.sarmut_id
        LEFT JOIN sarmut_subindikator_unit su ON su.subindikator_id=sub.id
        LEFT JOIN unit u ON u.id=su.unit_id ";

    $resFiltered = mysqli_query($kon, "SELECT COUNT(DISTINCT sub.id) AS cnt $baseFrom $where");
    $filtered = 0; if($resFiltered){ $r = mysqli_fetch_assoc($resFiltered); $filtered = intval($r['cnt']); }

    $order_sql = " ORDER BY sub.id DESC";
    $limit_sql = " LIMIT $start, $length";

    $rows = [];
    $q = mysqli_query($kon, "SELECT sub.id, sub.subindikator, sub.jenis, sub.satuan, sub.target, sub.baseline, i.indikator, ss.sasaran, s.title AS sarmut_title, GROUP_CONCAT(DISTINCT u.nama ORDER BY u.nama SEPARATOR ', ') AS unit_names $baseFrom $where GROUP BY sub.id $order_sql $limit_sql");
    while($row = mysqli_fetch_assoc($q)){
        $id = intval($row['id']);
        $actions = '<button class="btn btn-sm btn-secondary btn-edit-subindikator me-1" data-id="'.$id.'">Edit</button>'
             .'<button class="btn btn-sm btn-danger btn-del-subindikator" data-id="'.$id.'">Hapus</button>';
        $rows[] = [
            'id'=>$id,
            'sarmut_title'=>$row['sarmut_title'],
            'sasaran'=>$row['sasaran'],
            'indikator'=>$row['indikator'],
            'subindikator'=>$row['subindikator'],
            'jenis'=>$row['jenis'],
            'satuan'=>$row['satuan'],
            'target'=>$row['target'],
            'baseline'=>$row['baseline'],
            'unit_names'=>$row['unit_names'],
            'actions'=>$actions
        ];
    }

    echo json_encode(["draw"=>$draw,"recordsTotal"=>$total,"recordsFiltered"=>$filtered,"data"=>$rows]);
    exit;
}

// get single subindikator
if($action == 'get_subindikator'){
    $id = isset($_POST['id'])?intval($_POST['id']):0;
    if($id<=0){ echo json_encode(['success'=>false,'error'=>'invalid']); exit; }
    $q = mysqli_query($kon, "SELECT sub.*, i.sasaran_id, ss.sarmut_id FROM sarmut_subindikator sub LEFT JOIN sarmut_indikator i ON i.id=sub.indikator_id LEFT JOIN sarmut_sasaran ss ON ss.id=i.sasaran_id WHERE sub.id=$id LIMIT 1");
    if(!$q){ echo json_encode(['success'=>false,'error'=>mysqli_error($kon)]); exit; }
    $r = mysqli_fetch_assoc($q);
    if(!$r){ echo json_encode(['success'=>false,'error'=>'not_found']); exit; }
    $units = [];
    $uq = mysqli_query($kon, "SELECT unit_id FROM sarmut_subindikator_unit WHERE subindikator_id=$id");
    while($ur = mysqli_fetch_assoc($uq)) $units[] = intval($ur['unit_id']);
    $r['units'] = $units;
    echo json_encode(['success'=>true,'data'=>$r]); exit;
}

// update subindikator
if($action == 'update_subindikator'){
    $id = isset($_POST['id'])?intval($_POST['id']):0;
    $indikator_id = isset($_POST['indikator_id'])?intval($_POST['indikator_id']):0;
    $sub = isset($_POST['subindikator'])?trim($_POST['subindikator']):'';
    $jenis = isset($_POST['jenis'])?trim($_POST['jenis']):'';
    $satuan = isset($_POST['satuan'])?trim($_POST['satuan']):'';
    $target = isset($_POST['target'])?trim($_POST['target']):'';
    $baseline = isset($_POST['baseline'])?trim($_POST['baseline']):'';
    $units = isset($_POST['units'])?$_POST['units']:[];
    if(is_string($units)){
        $decoded = json_decode($units, true);
        $units = is_array($decoded) ? $decoded : [];
    }
    if($id<=0 || $indikator_id<=0 || $sub===''){ echo json_encode(['success'=>false,'error'=>'invalid']); exit; }
    $sub_esc = mysqli_real_escape_string($kon, $sub);
    $upd = mysqli_query($kon, "UPDATE sarmut_subindikator SET indikator_id=$indikator_id, subindikator='$sub_esc', jenis=".sarmut_sql_val($kon,$jenis).", satuan=".sarmut_sql_val($kon,$satuan).", target=".sarmut_sql_val($kon,$target).", baseline=".sarmut_sql_val($kon,$baseline)." WHERE id=$id");
    if(!$upd){ echo json_encode(['success'=>false,'error'=>mysqli_error($kon)]); exit; }
    sarmut_save_subindikator_units($kon, $id, $units);
    echo json_encode(['success'=>true]); exit;
}

// delete subindikator
if($action == 'delete_subindikator'){
    $id = isset($_POST['id'])?intval($_POST['id']):0;
    if($id<=0){ echo json_encode(['success'=>false,'error'=>'invalid']); exit; }
    mysqli_query($kon, "DELETE FROM sarmut_subindikator_unit WHERE subindikator_id=$id");
    $del = mysqli_query($kon, "DELETE FROM sarmut_subindikator WHERE id=$id");
    if(!$del){ echo json_encode(['success'=>false,'error'=>mysqli_error($kon)]); exit; }
    echo json_encode(['success'=>true]); exit;
}

// fallback
echo json_encode(['success'=>false,'error'=>'unknown_action']);
