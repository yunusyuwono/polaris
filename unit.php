<div class="card">
	<div class="card-body">
		<div class="container-fluid">
			<a class="btn btn-primary btn-sm" data-bs-toggle="collapse" href="#tambah" role="button" aria-expanded="false" aria-controls="tambah">
				<i class="fas fa-plus"></i> Tambah Unit
			</a>

			<?php 
			include "unit.buat.proses.php";
			?>
			<div class="collapse" id="tambah">
				<form method="post">
					<br>
					<div class="form-group">
						<label>Nama Unit/Prodi</label>
						<input type="text" name="nama" class="form-control" required>
					<button class="btn btn-primary btn-sm" name="simpan"><i class="fas fa-save"></i> Simpan</button>
					</div>
				</form>
			</div>

			<div class="table-responsive mt-1">
				<table class="table table-bordered table-striped table-hover">
					<thead>
						<th>No.</th>
						<th>Nama Unit</th>
						<th>Kepala</th>
						<th>Aksi</th>
					</thead>
					<?php 
					$user=mysqli_query($kon,"select * from unit order by nama asc");
					$no=1;
					while ($u=mysqli_fetch_array($user)) {
						?>
						<tr>
							<td><?php echo $no;?></td>
							<td><?php echo $u['nama'];?></td>
							<td><?php echo $u['kepala'];?></td>
							<td>
								<div class="btn-group">
									<a href="unit.edit?id=<?php echo $u['id'];?>" class="btn btn-success btn-sm" title="Edit User"><i class="fas fa-edit"></i></a>
									<a href="unit.hapus?id=<?php echo $u['id'];?>" class="btn btn-danger btn-sm" title="Hapus User"><i class="fas fa-trash"></i></a>
								</div>
							</td>
						</tr>
						<?php
						$no++;
					}
					?>
				</table>
			</div>
		</div>
	</div>
</div>