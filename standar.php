<div class="card">
	<div class="card-body">
		<div class="container-fluid">
			<a class="btn btn-primary btn-sm" data-bs-toggle="collapse" href="#tambah" role="button" aria-expanded="false" aria-controls="tambah">
				<i class="fas fa-plus"></i> Tambah Standar
			</a>

			<?php 
			include "standar.buat.proses.php";
			?>
			<div class="collapse" id="tambah">
				<form method="post">
					<br>
					<div class="form-group">
						<label>No. Standar</label>
						<input type="number" name="nostandar" class="form-control" required>
					</div>
					<div class="form-group">
						<label>Nama Standar</label>
						<input type="text" name="namastandar" class="form-control" required>
					</div>
					<div class="form-group">
						<label>No. Kategori</label>
						<input type="number" name="nokategori" class="form-control">
					</div>
					<div class="form-group">
						<label>Nama Kategori</label>
						<input type="text" name="namakategori" class="form-control">
					</div>
					<button class="btn btn-primary btn-sm" name="simpan"><i class="fas fa-save"></i> Simpan</button>
				</form>
			</div>

			<div class="table-responsive mt-1">
				<table class="table table-bordered table-striped table-hover">
					<thead>
						<th>No.</th>
						<th>No. Standar</th>
						<th>Nama Standar</th>
						<th>No. Kategori</th>
						<th>Nama Kategori</th>
						<th>Aksi</th>
					</thead>
					<?php 
					$standar=mysqli_query($kon,"select * from kriteria order by nostandar asc, nokategori asc");
					$no=1;
					while ($u=mysqli_fetch_array($standar)) {
						?>
						<tr>
							<td><?php echo $no;?></td>
							<td><?php echo $u['nostandar'];?></td>
							<td><?php echo $u['namastandar'];?></td>
							<td><?php echo $u['nokategori'];?></td>
							<td><?php echo $u['namakategori'];?></td>
							<td>
								<div class="btn-group">
									<a href="standar.edit?id=<?php echo $u['id'];?>" class="btn btn-success btn-sm" title="Edit User"><i class="fas fa-edit"></i></a>
									<a href="standar.hapus?id=<?php echo $u['id'];?>" class="btn btn-danger btn-sm" title="Hapus User"><i class="fas fa-trash"></i></a>
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