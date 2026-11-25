<?php 
if(!function_exists('digami_table_columns')) {
	function digami_table_columns($kon, $table) {
		$columns = [];
		$table = preg_replace('/[^a-zA-Z0-9_]/', '', $table);
		$result = mysqli_query($kon, "SHOW COLUMNS FROM `$table`");
		if($result) {
			while($row = mysqli_fetch_assoc($result)) {
				$columns[] = $row['Field'];
			}
		}
		return $columns;
	}
}

$aksi = $_POST['aksi'] ?? '';
if(isset($_POST['simpan']) || isset($_POST['update']) || in_array($aksi, ['simpan', 'update'], true))
{
	$idpra=(int)($_POST['idpra'] ?? 0);
	$unit=(int)($_POST['unit'] ?? 0);
	$paket=(int)($_POST['paket'] ?? 0);
	$sarmut=isset($_POST['paketsarmut']) && $_POST['paketsarmut'] !== '' ? (int)$_POST['paketsarmut'] : '';
	$iso=isset($_POST['paketiso']) && $_POST['paketiso'] !== '' ? (int)$_POST['paketiso'] : '';
	$thn=(int)($_POST['tahun'] ?? 0);
	$praauditColumns = digami_table_columns($kon, 'praaudit');

	if(isset($_POST['update']) || $aksi === 'update') {
		$sets = ["unit='$unit'", "paket='$paket'", "tahun='$thn'"];

		if(in_array('paketsarmut', $praauditColumns, true)) {
			$sets[] = "paketsarmut=".($sarmut === '' ? "NULL" : "'$sarmut'");
		}

		if(in_array('paketiso', $praauditColumns, true)) {
			$sets[] = "paketiso=".($iso === '' ? "NULL" : "'$iso'");
		}

		$simpan = $idpra > 0 ? mysqli_query($kon,"update praaudit set ".implode(',', $sets)." where id='$idpra'") : false;
		$successMessage = 'Pra Audit Berhasil Diperbarui.';
		$errorMessage = 'Pra Audit Gagal Diperbarui ';
	} else {
		$columns = ['unit', 'paket', 'tahun', 'status'];
		$values = ["'$unit'", "'$paket'", "'$thn'", "'Belum Selesai'"];

		if(in_array('paketsarmut', $praauditColumns, true)) {
			$columns[] = 'paketsarmut';
			$values[] = $sarmut === '' ? "NULL" : "'$sarmut'";
		}

		if(in_array('paketiso', $praauditColumns, true)) {
			$columns[] = 'paketiso';
			$values[] = $iso === '' ? "NULL" : "'$iso'";
		}

		$simpan=mysqli_query($kon,"insert into praaudit(".implode(',', $columns).") values (".implode(',', $values).")");
		$successMessage = 'Pra Audit Berhasil Dibuat.';
		$errorMessage = 'Pra Audit Gagal Dibuat ';
	}

	if($simpan)
	{
		$praauditProcessAlert = '<div class="alert alert-success mt-1">'.$successMessage.'</div>';
	}
	else
	{
		$praauditProcessAlert = '<div class="alert alert-danger mt-1">'.$errorMessage.htmlspecialchars(mysqli_error($kon)).'</div>';
	}

	if(empty($praauditDeferOutput)) {
		echo $praauditProcessAlert;
	}
}
?>
