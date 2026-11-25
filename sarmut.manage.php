<?php
include 'nav.php';
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
                <div class="card">
                    <div class="card-header">
                        
                        <ul class="nav nav-pills nav-fill">
                            <li class="nav-item">
                                <a class="nav-link active" data-bs-toggle="tab" href="#tabSarmut">Sarmut</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#tabSasaran">Sasaran</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#tabIndikator">Indikator</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#tabSubindikator">Subindikator</a>
                            </li>
                        </ul>
                            <!-- future tabs for other entities can be added here -->
                    </div>
                    <div class="card-body px-3 py-2">
                        <div class="tab-content">
                            <div class="tab-pane fade show active" id="tabSarmut">
                                <div class="row">
                            <div class="col-md-5">
                                <h5>Form Sarmut</h5>
                                <form id="sarmutForm">
                                    <input type="hidden" id="sarmut_id" value="">
                                    <div class="mb-2">
                                        <label>Judul</label>
                                        <input type="text" name="title" class="form-control" required>
                                    </div>
                                    <div class="mb-2">
                                        <label>Tahun</label>
                                        <input type="number" name="year" class="form-control" value="<?php echo date('Y');?>" required>
                                    </div>
                                    <div id="sasaranContainer">
                                        <!-- sasaran blocks inserted here -->
                                    </div>
                                    <div class="mb-2">
                                        <button id="saveSarmut" type="submit" class="btn btn-success btn-sm">Simpan Sarmut</button>
                                    </div>
                                </form>
                            </div>
                            <div class="col-md-7">
                                <h5>Daftar Sarmut</h5>
                                <table id="sarmutTable" class="table table-bordered table-striped" style="width:100%">
                                    <thead>
                                        <tr>
                                            <th>No.</th>
                                            <th>Judul</th>
                                            <th>Tahun</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="tabSasaran">
                                <div class="p-3">
                                    <div class="mb-2">
                                        <h5>Form Sasaran</h5>
                                        <form id="sasaranQuickAdd">
                                            <input type="hidden" id="ts_edit_id" value="">
                                            <div class="form-group d-flex flex-wrap mb-2">
                                                <label for="ts_sarmut" class="form-label me-2">Pilih Sarmut</label>
                                                <select id="ts_sarmut" class="form-control form-control-sm" style="min-width:220px" required>
                                                    <option value="">Pilih Sarmut</option>
                                                </select>
                                            </div>
                                            <div class="form-group mb-2">
                                                <label class="form-label">Sasaran</label>
                                                <textarea id="ts_sasaran" class="form-control" placeholder="Masukkan teks sasaran" required></textarea>
                                            </div>
                                            <div class="form-group mb-2">
                                                <label class="form-label">Jenis Sarmut</label>
                                                <select id="ts_jenis_sarmut" class="form-control form-control-sm">
                                                    <option value="">Pilih Jenis</option>
                                                    <option value="KPI">KPI</option>
                                                    <option value="Renstra">Renstra</option>
                                                </select>
                                            </div>
                                            <div>
                                                <button id="ts_add" type="button" class="btn btn-sm mt-3 btn-success">Simpan Sasaran</button>
                                                <button id="ts_cancel" type="button" class="btn btn-sm mt-3 btn-secondary ms-2">Batal</button>
                                            </div>
                                        </form>
                                    </div>
                                    <table id="sasaranTable" class="table table-bordered table-striped" style="width:100%">
                                        <thead>
                                            <tr><th>No.</th><th>Sarmut</th><th>Sasaran</th><th>Jenis Sarmut</th><th>Aksi</th></tr>
                                        </thead>
                                    </table>


                                </div>
                            </div>
                            <div class="tab-pane fade" id="tabIndikator">
                                <div class="p-3">
                                    <div class="mb-2">
                                        <h5>Form Indikator</h5>
                                        <form id="indikatorQuickAdd">
                                            <input type="hidden" id="ti_edit_id" value="">
                                            <div class="form-group d-flex flex-wrap mb-2">
                                                <label for="ts_sarmut_indikator" class="form-label me-2">Pilih Sarmut</label>
                                                <select id="ts_sarmut_indikator" class="form-control form-control-sm" style="min-width:220px" required>
                                                    <option value="">Pilih Sarmut</option>
                                                </select>
                                            </div>
                                            <div class="form-group mb-2">
                                                <label class="form-label">Sasaran</label>
                                                <select id="ts_indikator_sasaran" class="form-control form-control-sm mb-2" required>
                                                    <option value="">Pilih Sasaran</option>
                                                </select>
                                            </div>
                                            <div class="form-group mb-2">
                                                <label class="form-label">Indikator</label>
                                                <textarea id="ts_indikator" class="form-control" placeholder="Masukkan teks indikator" required></textarea>
                                            </div>
                                            <div>
                                                <button id="ts_addindikator" type="button" class="btn btn-sm mt-3 btn-success">Simpan Indikator</button>
                                                <button id="ts_cancelindikator" type="button" class="btn btn-sm mt-3 btn-secondary ms-2">Batal</button>
                                            </div>
                                        </form>
                                    </div>
                                    <table id="indikatorTable" class="table table-bordered table-striped" style="width:100%">
                                        <thead>
                                            <tr><th>No.</th><th>Sarmut</th><th>Sasaran</th><th>Indikator</th><th>Aksi</th></tr>
                                        </thead>
                                    </table>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="tabSubindikator">
                                <div class="p-3">
                                    <div class="mb-2">
                                        <h5>Form Subindikator</h5>
                                        <form id="subindikatorQuickAdd">
                                            <input type="hidden" id="tsi_edit_id" value="">
                                            <div class="form-group d-flex flex-wrap mb-2">
                                                <label for="tsi_sarmut" class="form-label me-2">Pilih Sarmut</label>
                                                <select id="tsi_sarmut" class="form-control form-control-sm" style="min-width:220px" required>
                                                    <option value="">Pilih Sarmut</option>
                                                </select>
                                            </div>
                                            <div class="form-group mb-2">
                                                <label class="form-label">Sasaran</label>
                                                <select id="tsi_sasaran" class="form-control form-control-sm" required>
                                                    <option value="">Pilih Sasaran</option>
                                                </select>
                                            </div>
                                            <div class="form-group mb-2">
                                                <label class="form-label">Indikator</label>
                                                <select id="tsi_indikator" class="form-control form-control-sm" required>
                                                    <option value="">Pilih Indikator</option>
                                                </select>
                                            </div>
                                            <div class="form-group mb-2">
                                                <label class="form-label">Subindikator</label>
                                                <textarea id="tsi_subindikator" class="form-control" placeholder="Masukkan teks subindikator" required></textarea>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group mb-2">
                                                        <label class="form-label">Jenis</label>
                                                        <select id="tsi_jenis" class="form-control form-control-sm">
                                                            <option value="">Pilih Jenis</option>
                                                            <option value="Akademik">Akademik</option>
                                                            <option value="Non-Akademik">Non-Akademik</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group mb-2">
                                                        <label class="form-label">Satuan</label>
                                                        <input type="text" id="tsi_satuan" class="form-control form-control-sm" placeholder="%, orang, dll">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group mb-2">
                                                        <label class="form-label">Target</label>
                                                        <input type="text" id="tsi_target" class="form-control form-control-sm">
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group mb-2">
                                                        <label class="form-label">Baseline</label>
                                                        <input type="text" id="tsi_baseline" class="form-control form-control-sm">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-group mb-2">
                                                <label class="form-label">Unit (PIC)</label>
                                                <select id="tsi_unit" multiple class="form-control form-control-sm"></select>
                                                <small class="text-muted">Pilih satu atau lebih unit penanggung jawab</small>
                                            </div>
                                            <div>
                                                <button id="tsi_save" type="button" class="btn btn-sm mt-3 btn-success">Simpan Subindikator</button>
                                                <button id="tsi_cancel" type="button" class="btn btn-sm mt-3 btn-secondary ms-2">Batal</button>
                                            </div>
                                        </form>
                                    </div>
                                    <table id="subindikatorTable" class="table table-bordered table-striped" style="width:100%">
                                        <thead>
                                            <tr>
                                                <th>No.</th>
                                                <th>Sarmut</th>
                                                <th>Sasaran</th>
                                                <th>Indikator</th>
                                                <th>Subindikator</th>
                                                <th>Jenis</th>
                                                <th>Satuan</th>
                                                <th>Target</th>
                                                <th>Baseline</th>
                                                <th>Unit</th>
                                                <th>Aksi</th>
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                            </div>
                        </div>
                </div>
            </div>
        </section>
    </div>
</div>
<?php include 'foot.php';?>

<!-- Dependencies: jQuery, DataTables. Jika tidak ada, CDN fallback dipakai -->
<link rel="stylesheet" href="assets/vendors/choices.js/choices.css">
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(function(){
    // helpers to build nested inputs
    function createSasaranBlock(idx){
        var html = '<div class="card mb-2 sasaran-block" data-idx="'+idx+'">'
            +'<div class="card-body">'
            +'<div class="d-flex justify-content-between align-items-center">'
            +'<h6>Sasaran <span class="badge bg-secondary">#'+(idx+1)+'</span></h6>'
            +'<div><button type="button" class="btn btn-sm btn-danger removeSasaran">Hapus</button></div>'
            +'</div>'
            +'<div class="mb-2"><textarea class="form-control sasaran-text" placeholder="Isi sasaran"></textarea></div>'
            +'<div class="mb-2 indikator-list"></div>'
            +'<button type="button" class="btn btn-sm btn-outline-primary addIndikator">Tambah Indikator</button>'
            +'</div></div>';
        return html;
    }
    function createIndikatorBlock(){
        return '<div class="mb-2 indikator-block">'
            +'<div class="d-flex justify-content-between align-items-center">'
            +'<input type="text" class="form-control indikator-text me-2" placeholder="Indikator">'
            +'<div><button type="button" class="btn btn-sm btn-danger removeIndikator">Hapus</button></div>'
            +'</div>'
            +'<div class="subindikator-list mt-1"></div>'
            +'<button type="button" class="btn btn-sm btn-outline-secondary addSubindikator">Tambah Subindikator</button>'
            +'</div>';
    }
    function createSubindikatorBlock(){
        return '<div class="mb-1 subindikator-block d-flex">'
            +'<input type="text" class="form-control subindikator-text me-2" placeholder="Subindikator">'
            +'<button type="button" class="btn btn-sm btn-danger removeSubindikator">Hapus</button>'
            +'</div>';
    }

    var sasaranIdx = 0;
    $('#addSasaran').on('click', function(){
        $('#sasaranContainer').append(createSasaranBlock(sasaranIdx++));
    });
    $('#sasaranContainer').on('click', '.removeSasaran', function(){
        $(this).closest('.sasaran-block').remove();
    });
    $('#sasaranContainer').on('click', '.addIndikator', function(){
        $(this).siblings('.indikator-list').append(createIndikatorBlock());
    });
    $('#sasaranContainer').on('click', '.removeIndikator', function(){
        $(this).closest('.indikator-block').remove();
    });
    $('#sasaranContainer').on('click', '.addSubindikator', function(){
        $(this).siblings('.subindikator-list').append(createSubindikatorBlock());
    });
    $('#sasaranContainer').on('click', '.removeSubindikator', function(){
        $(this).closest('.subindikator-block').remove();
    });

    $('#resetForm').on('click', function(){
        $('#sarmutForm')[0].reset();
        $('#sasaranContainer').empty();
    });

    // DataTables server-side
    if ($.fn.DataTable.isDataTable('#sarmutTable')) {
        $('#sarmutTable').DataTable().clear().destroy();
    }
    var table = $('#sarmutTable').DataTable({
        serverSide: true,
        processing: true,
        ajax: {
            url: 'sarmut.ajax.php?action=fetch',
            type: 'POST'
        },
        columns: [
            { data: null, orderable: false, searchable: false },
            { data: 'title' },
            { data: 'year' },
            { data: 'actions', orderable: false, searchable: false }
        ],
        order: [[2,'desc']],
        pageLength: 25,
        lengthMenu: [25,50,100],
        drawCallback: function(settings){
            var api = this.api();
            api.column(0, {page:'current'}).nodes().each(function(cell, i){
                cell.innerHTML = i + 1 + (api.page() * api.page.len());
            });
        }
    });

    // Edit Sarmut: load into form for update
    $('#sarmutTable').on('click', '.btn-edit-sarmut', function(){
        var id = $(this).data('id');
        $.post('sarmut.ajax.php?action=get_sarmut', {id: id}, function(resp){
            if(resp.success){
                var d = resp.data;
                $('[name=title]').val(d.title);
                $('[name=year]').val(d.year);
                $('#sarmut_id').val(d.id);
                $('#sasaranContainer').empty();
                var tabEl = document.querySelector('a[data-bs-toggle="tab"][href="#tabSarmut"]');
                if(tabEl) new bootstrap.Tab(tabEl).show();
            } else Swal.fire('Gagal', (resp.error||''), 'error');
        }, 'json');
    });

    // Delete Sarmut
    $('body').on('click', '.btn-del-sarmut', function(){
        var id = $(this).data('id');
        Swal.fire({ title: 'Hapus Sarmut?', text: 'Yakin ingin menghapus Sarmut beserta isinya?', icon: 'warning', showCancelButton: true }).then(function(res){
            if(res.isConfirmed){
                $.post('sarmut.ajax.php?action=delete_sarmut', {id: id}, function(resp){
                    if(resp.success){ Swal.fire({icon:'success',title:'Terhapus'}); table.ajax.reload(null,false); }
                    else Swal.fire('Gagal', (resp.error||''), 'error');
                }, 'json');
            }
        });
    });

    // Save handler: create or update Sarmut
    $('#sarmutForm').on('submit', function(e){
        e.preventDefault();
        var id = $('#sarmut_id').val();
        var title = $.trim($('[name=title]').val());
        var year = parseInt($('[name=year]').val()) || new Date().getFullYear();
        if(!title){ Swal.fire('Kesalahan','Judul wajib diisi','error'); return; }

        if(id){
            // update
            $.post('sarmut.ajax.php?action=update_sarmut', {id: id, title: title, year: year}, function(resp){
                if(resp.success){ Swal.fire({icon:'success',title:'Tersimpan'}); $('#sarmutForm')[0].reset(); $('#sarmut_id').val(''); table.ajax.reload(null,false); }
                else { Swal.fire('Gagal', resp.error||'unknown','error'); }
            }, 'json').fail(function(){ Swal.fire('Gagal','Koneksi gagal','error'); });
        } else {
            // create
            var payload = { title: title, year: year };
            $.ajax({
                url: 'sarmut.ajax.php?action=sarmut_save',
                method: 'POST',
                data: {data: JSON.stringify(payload)},
                dataType: 'json',
                success: function(resp){
                    if(resp.success){ Swal.fire({icon:'success',title:'Tersimpan'}); $('#sarmutForm')[0].reset(); table.ajax.reload(null,false); }
                    else { Swal.fire('Gagal', resp.error||'unknown','error'); }
                },
                error: function(){ Swal.fire('Gagal','Koneksi gagal','error'); }
            });
        }
    });

    // open quick add on button
    $('#btnAdd').on('click', function(){
        $('#sasaranContainer').empty(); sasaranIdx=0; $('#sarmutForm')[0].reset(); $('#addSasaran').click();
    });

        // Modals HTML appended to body (use template literal)
        var modals = `
        <div class="modal fade" id="modalAddSasaran" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Tambah Sasaran</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="ms_sarmut_id">
                        <input type="hidden" id="ms_sasaran_id">
                        <div class="mb-2"><textarea id="ms_sasaran" class="form-control" placeholder="Sasaran"></textarea></div>
                        <div class="mb-2">
                            <select id="ms_jenis_sarmut" class="form-control form-control-sm">
                                <option value="">Pilih Jenis Sarmut</option>
                                <option value="KPI">KPI</option>
                                <option value="Renstra">Renstra</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer"><button id="ms_save" class="btn btn-primary">Simpan</button><button class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button></div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="modalAddIndikator" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Tambah Indikator</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="mi_sarmut_id">
                        <div class="mb-2"><label>Sasaran</label><select id="mi_sasaran" class="form-control"></select></div>
                        <div class="mb-2"><input id="mi_indikator" class="form-control" placeholder="Indikator"></div>
                    </div>
                    <div class="modal-footer"><button id="mi_save" class="btn btn-primary">Simpan</button><button class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button></div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="modalAddSub" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Tambah Subindikator</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-2"><label>Sasaran</label><select id="msas_sasaran" class="form-control"></select></div>
                        <div class="mb-2"><label>Indikator</label><select id="msas_indikator" class="form-control"></select></div>
                        <div class="mb-2"><input id="msas_sub" class="form-control" placeholder="Subindikator"></div>
                    </div>
                    <div class="modal-footer"><button id="msas_save" class="btn btn-primary">Simpan</button><button class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button></div>
                </div>
            </div>
        </div>
        `;
        $('body').append(modals);

    // delegate add sasaran from sarmut table: open inline Sasaran tab and prefill sarmut
    $('#sarmutTable').on('click', '.btn-add-sasaran', function(){
        var id = $(this).data('id');
        $('#ts_edit_id').val('');
        initSasaranTab();
        var tabEl = document.querySelector('a[data-bs-toggle="tab"][href="#tabSasaran"]');
        if(tabEl) new bootstrap.Tab(tabEl).show();
        setTimeout(function(){ $('#ts_sarmut').val(id).trigger('change'); $('#ts_sasaran').focus(); }, 200);
    });

    // delegate edit sasaran from sasaran table: populate inline form
    $('#sasaranTable').on('click', '.btn-edit-sasaran', function(){
        var id = $(this).data('id');
        $.post('sarmut.ajax.php?action=get_sasaran', {id: id}, function(resp){
            if(resp.success){
                $('#ts_edit_id').val(resp.data.id);
                $('#ts_sarmut').val(resp.data.sarmut_id).trigger('change');
                $('#ts_sasaran').val(resp.data.sasaran);
                $('#ts_jenis_sarmut').val(resp.data.jenis_sarmut || '');
                var tabEl = document.querySelector('a[data-bs-toggle="tab"][href="#tabSasaran"]');
                if(tabEl) new bootstrap.Tab(tabEl).show();
                $('#ts_sasaran').focus();
            } else Swal.fire('Gagal', (resp.error||''), 'error');
        }, 'json');
    });

    $('#ms_save').on('click', function(){
        var sarmut_id = $('#ms_sarmut_id').val();
        var sas = $('#ms_sasaran').val();
        var edit_id = $('#ms_sasaran_id').val();
        var jenis = $('#ms_jenis_sarmut').val();
        if(edit_id){
            $.post('sarmut.ajax.php?action=update_sasaran', {id: edit_id, sasaran: sas, jenis_sarmut: jenis}, function(resp){
                if(resp.success){ Swal.fire({icon:'success',title:'Sasaran diperbarui'}); $('#ms_sasaran').val(''); $('#ms_sasaran_id').val(''); $('#ms_jenis_sarmut').val(''); var _m = bootstrap.Modal.getOrCreateInstance(document.getElementById('modalAddSasaran')); _m.hide(); $('#sasaranTable').DataTable().ajax.reload(null,false); }
                else Swal.fire('Gagal', (resp.error||''), 'error');
            }, 'json');
        } else {
            $.post('sarmut.ajax.php?action=add_sasaran', {sarmut_id: sarmut_id, sasaran: sas, jenis_sarmut: jenis}, function(resp){
                if(resp.success){ Swal.fire({icon:'success',title:'Sasaran ditambah'}); $('#ms_sasaran').val(''); $('#ms_jenis_sarmut').val(''); var _m = bootstrap.Modal.getOrCreateInstance(document.getElementById('modalAddSasaran')); _m.hide(); $('#sasaranTable').DataTable().ajax.reload(null,false); }
                else Swal.fire('Gagal', (resp.error||''), 'error'); 
            }, 'json');
        }
    });

    // initialize sasaran tab: load sarmut options and DataTable
    function initSasaranTab(){
        // load sarmut options
        $.post('sarmut.ajax.php?action=fetch_sarmut_options', {}, function(resp){
            if(resp.success){
                $('#ts_sarmut').empty();
                resp.data.forEach(function(it){ $('#ts_sarmut').append('<option value="'+it.id+'">'+it.title+'</option>'); });
            }
        }, 'json');

        // DataTable khusus tab Sasaran
        if ($.fn.DataTable.isDataTable('#sasaranTable')){ $('#sasaranTable').DataTable().clear().destroy(); }
        $('#sasaranTable').DataTable({
            serverSide: true,
            processing: true,
            ajax: { url: 'sarmut.ajax.php?action=fetch_sasaran_table', type: 'POST' },
            columns: [
                { data: null, orderable:false, searchable:false },
                { data: 'sarmut_title' },
                { data: 'sasaran' },
                { data: 'jenis_sarmut' },
                { data: 'actions', orderable:false, searchable:false }
            ],
            pageLength: 25,
            drawCallback: function(settings){ var api=this.api(); api.column(0,{page:'current'}).nodes().each(function(cell,i){ cell.innerHTML = i+1 + (api.page()*api.page.len()); }); }
        });
    }

    // initialize indikator tab: load sarmut + sasaran dropdown + DataTable
    function initIndikatorTab(){
        // load sarmut options
        $.post('sarmut.ajax.php?action=fetch_sarmut_options', {}, function(resp){
            if(resp.success){
                $('#ts_sarmut_indikator').empty();
                $('#ts_sarmut_indikator').append('<option value="">Pilih Sarmut</option>');
                resp.data.forEach(function(it){ $('#ts_sarmut_indikator').append('<option value="'+it.id+'">'+it.title+'</option>'); });
            }
        }, 'json');

        // reset sasaran dropdown (kecuali sedang edit)
        if(!$('#ti_edit_id').val()){
            $('#ts_indikator_sasaran').empty();
            $('#ts_indikator_sasaran').append('<option value="">Pilih Sasaran</option>');
        }

        // DataTable khusus tab Indikator
        if ($.fn.DataTable.isDataTable('#indikatorTable')){ $('#indikatorTable').DataTable().clear().destroy(); }
        $('#indikatorTable').DataTable({
            serverSide: true,
            processing: true,
            ajax: { url: 'sarmut.ajax.php?action=fetch_indikator_table', type: 'POST' },
            columns: [
                { data: null, orderable: false, searchable: false },
                { data: 'sarmut_title' },
                { data: 'sasaran' },
                { data: 'indikator' },
                { data: 'actions', orderable: false, searchable: false }
            ],
            pageLength: 25,
            drawCallback: function(settings){
                var api = this.api();
                api.column(0, {page:'current'}).nodes().each(function(cell, i){
                    cell.innerHTML = i + 1 + (api.page() * api.page.len());
                });
            }
        });
    }

    function refreshSasaranIndikator(sarmutId, callback){
        if(!sarmutId){
            $('#ts_indikator_sasaran').empty();
            $('#ts_indikator_sasaran').append('<option value="">Pilih Sasaran</option>');
            if(callback) callback();
            return;
        }
        $.post('sarmut.ajax.php?action=fetch_sasaran_options', {sarmut_id: sarmutId}, function(resp){
            if(resp.success){
                $('#ts_indikator_sasaran').empty();
                $('#ts_indikator_sasaran').append('<option value="">Pilih Sasaran</option>');
                resp.data.forEach(function(it){ $('#ts_indikator_sasaran').append('<option value="'+it.id+'">'+it.sasaran+'</option>'); });
                if(resp.data.length === 1){
                    $('#ts_indikator_sasaran').val(resp.data[0].id);
                }
            }
            if(callback) callback();
        }, 'json');
    }

    function refreshSasaranSub($select, sarmutId, callback){
        if(!sarmutId){
            $select.empty().append('<option value="">Pilih Sasaran</option>');
            if(callback) callback();
            return;
        }
        $.post('sarmut.ajax.php?action=fetch_sasaran_options', {sarmut_id: sarmutId}, function(resp){
            $select.empty().append('<option value="">Pilih Sasaran</option>');
            if(resp.success){
                resp.data.forEach(function(it){ $select.append('<option value="'+it.id+'">'+it.sasaran+'</option>'); });
            }
            if(callback) callback();
        }, 'json');
    }

    function refreshIndikatorSub($select, sasaranId, callback){
        if(!sasaranId){
            $select.empty().append('<option value="">Pilih Indikator</option>');
            if(callback) callback();
            return;
        }
        $.post('sarmut.ajax.php?action=fetch_indikator', {sasaran_id: sasaranId}, function(resp){
            $select.empty().append('<option value="">Pilih Indikator</option>');
            if(resp.success){
                resp.data.forEach(function(it){ $select.append('<option value="'+it.id+'">'+it.indikator+'</option>'); });
            }
            if(callback) callback();
        }, 'json');
    }

    var unitChoices = null;
    var unitOptionsCache = [];
    var pendingSubEditId = null;

    function withUnitOptions(done){
        if(unitOptionsCache.length){ done(); return; }
        $.post('sarmut.ajax.php?action=fetch_unit_options', {}, function(resp){
            if(resp.success) unitOptionsCache = resp.data || [];
            done();
        }, 'json');
    }

    function loadSubindikatorForEdit(id){
        $.post('sarmut.ajax.php?action=get_subindikator', {id: id}, function(resp){
            if(resp.success){
                var d = resp.data;
                $('#tsi_edit_id').val(d.id);
                $('#tsi_subindikator').val(d.subindikator);
                $('#tsi_jenis').val(d.jenis || '');
                $('#tsi_satuan').val(d.satuan || '');
                $('#tsi_target').val(d.target || '');
                $('#tsi_baseline').val(d.baseline || '');
                $('#tsi_save').text('Perbarui Subindikator');
                $('#tsi_sarmut').val(d.sarmut_id);
                refreshSasaranSub($('#tsi_sasaran'), d.sarmut_id, function(){
                    $('#tsi_sasaran').val(d.sasaran_id);
                    refreshIndikatorSub($('#tsi_indikator'), d.sasaran_id, function(){
                        $('#tsi_indikator').val(d.indikator_id);
                        withUnitOptions(function(){
                            initUnitChoices(unitOptionsCache, d.units || []);
                        });
                        $('#tsi_subindikator').focus();
                    });
                });
            } else Swal.fire('Gagal', (resp.error||''), 'error');
        }, 'json');
    }

    function resetIndikatorForm(){
        $('#ti_edit_id').val('');
        $('#ts_indikator').val('');
        $('#ts_addindikator').text('Simpan Indikator');
    }

    function resetSubindikatorForm(){
        $('#tsi_edit_id').val('');
        $('#tsi_subindikator').val('');
        $('#tsi_jenis').val('');
        $('#tsi_satuan').val('');
        $('#tsi_target').val('');
        $('#tsi_baseline').val('');
        $('#tsi_save').text('Simpan Subindikator');
        initUnitChoices(unitOptionsCache, []);
    }

    function initUnitChoices(units, selected){
        var sel = document.getElementById('tsi_unit');
        if(!sel) return;
        if(unitChoices){
            unitChoices.destroy();
            unitChoices = null;
        }
        sel.innerHTML = '';
        (units || []).forEach(function(u){
            var opt = document.createElement('option');
            opt.value = u.id;
            opt.text = u.nama;
            sel.appendChild(opt);
        });
        unitChoices = new Choices(sel, {
            removeItemButton: true,
            searchEnabled: true,
            placeholder: true,
            placeholderValue: 'Pilih Unit',
            shouldSort: false
        });
        if(selected && selected.length){
            unitChoices.setChoiceByValue(selected.map(String));
        }
    }

    function initSubindikatorTab(){
        $.post('sarmut.ajax.php?action=fetch_sarmut_options', {}, function(resp){
            if(resp.success){
                $('#tsi_sarmut').empty().append('<option value="">Pilih Sarmut</option>');
                resp.data.forEach(function(it){ $('#tsi_sarmut').append('<option value="'+it.id+'">'+it.title+'</option>'); });
            }
        }, 'json');

        if(!pendingSubEditId && !$('#tsi_edit_id').val()){
            $('#tsi_sasaran').empty().append('<option value="">Pilih Sasaran</option>');
            $('#tsi_indikator').empty().append('<option value="">Pilih Indikator</option>');
        }

        if(!$('#tsi_edit_id').val() && !pendingSubEditId){
            $.post('sarmut.ajax.php?action=fetch_unit_options', {}, function(resp){
                if(resp.success){
                    unitOptionsCache = resp.data;
                    initUnitChoices(unitOptionsCache, []);
                }
            }, 'json');
        }

        if ($.fn.DataTable.isDataTable('#subindikatorTable')){ $('#subindikatorTable').DataTable().clear().destroy(); }
        $('#subindikatorTable').DataTable({
            serverSide: true,
            processing: true,
            ajax: { url: 'sarmut.ajax.php?action=fetch_subindikator_table', type: 'POST' },
            columns: [
                { data: null, orderable: false, searchable: false },
                { data: 'sarmut_title' },
                { data: 'sasaran' },
                { data: 'indikator' },
                { data: 'subindikator' },
                { data: 'jenis' },
                { data: 'satuan' },
                { data: 'target' },
                { data: 'baseline' },
                { data: 'unit_names' },
                { data: 'actions', orderable: false, searchable: false }
            ],
            pageLength: 25,
            scrollX: true,
            drawCallback: function(settings){
                var api = this.api();
                api.column(0, {page:'current'}).nodes().each(function(cell, i){
                    cell.innerHTML = i + 1 + (api.page() * api.page.len());
                });
            }
        });

        if(pendingSubEditId){
            var editId = pendingSubEditId;
            pendingSubEditId = null;
            loadSubindikatorForEdit(editId);
        }
    }



    // init on tab show
    $('a[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
        var target = $(e.target).attr('href');
        if(target === '#tabSasaran') initSasaranTab();
        if(target === '#tabIndikator') initIndikatorTab();
        if(target === '#tabSubindikator') initSubindikatorTab();
    });


    // inline add/update from tab Sasaran
    $('#ts_add').on('click', function(){
        var sarmut_id = $('#ts_sarmut').val();
        var sas = $('#ts_sasaran').val();
        var edit_id = $('#ts_edit_id').val();
        var jenis = $('#ts_jenis_sarmut').val();
        if(!sarmut_id){ Swal.fire('Pilih Sarmut','Pilih Sarmut terlebih dahulu','warning'); return; }
        if(!$.trim(sas)){ Swal.fire('Kosong','Isi kolom Sasaran terlebih dahulu','warning'); return; }
        if(edit_id){
            $.post('sarmut.ajax.php?action=update_sasaran', {id: edit_id, sasaran: sas, jenis_sarmut: jenis}, function(resp){
                if(resp.success){ Swal.fire({icon:'success',title:'Sasaran diperbarui'}); $('#ts_sasaran').val(''); $('#ts_jenis_sarmut').val(''); $('#ts_edit_id').val(''); $('#sasaranTable').DataTable().ajax.reload(null,false); }
                else Swal.fire('Gagal', (resp.error||''), 'error');
            }, 'json');
        } else {
            $.post('sarmut.ajax.php?action=add_sasaran', {sarmut_id: sarmut_id, sasaran: sas, jenis_sarmut: jenis}, function(resp){
                if(resp.success){ Swal.fire({icon:'success',title:'Sasaran ditambah'}); $('#ts_sasaran').val(''); $('#ts_jenis_sarmut').val(''); $('#sasaranTable').DataTable().ajax.reload(null,false); }
                else Swal.fire('Gagal', (resp.error||''), 'error');
            }, 'json');
        }
    });

    // cancel inline edit
    $('#ts_cancel').on('click', function(){ $('#ts_edit_id').val(''); $('#ts_sasaran').val(''); $('#ts_jenis_sarmut').val(''); });

    // handle form indikator (tabIndikator)
    $('#ts_cancelindikator').on('click', function(){
        resetIndikatorForm();
        $('#ts_indikator_sasaran').val('');
    });

    $('#indikatorTable').on('click', '.btn-edit-indikator', function(){
        var id = $(this).data('id');
        $.post('sarmut.ajax.php?action=get_indikator', {id: id}, function(resp){
            if(resp.success){
                var d = resp.data;
                $('#ti_edit_id').val(d.id);
                $('#ts_sarmut_indikator').val(d.sarmut_id);
                refreshSasaranIndikator(d.sarmut_id, function(){
                    $('#ts_indikator_sasaran').val(d.sasaran_id);
                    $('#ts_indikator').val(d.indikator);
                    $('#ts_addindikator').text('Perbarui Indikator');
                    var tabEl = document.querySelector('a[data-bs-toggle="tab"][href="#tabIndikator"]');
                    if(tabEl) new bootstrap.Tab(tabEl).show();
                    $('#ts_indikator').focus();
                });
            } else Swal.fire('Gagal', (resp.error||''), 'error');
        }, 'json');
    });

    $('#ts_addindikator').on('click', function(){
        var sarmutId = $('#ts_sarmut_indikator').val();
        var sasaranId = $('#ts_indikator_sasaran').val();
        var indikator = $.trim($('#ts_indikator').val());
        var editId = $('#ti_edit_id').val();

        if(!sarmutId){ Swal.fire('Pilih Sarmut','Pilih Sarmut terlebih dahulu','warning'); return; }
        if(!sasaranId){ Swal.fire('Pilih Sasaran','Pilih Sasaran terlebih dahulu','warning'); return; }
        if(!indikator){ Swal.fire('Kosong','Isi kolom Indikator terlebih dahulu','warning'); return; }

        var action = editId ? 'update_indikator' : 'add_indikator';
        var payload = editId ? {id: editId, sasaran_id: sasaranId, indikator: indikator} : {sasaran_id: sasaranId, indikator: indikator};

        $.post('sarmut.ajax.php?action=' + action, payload, function(resp){
            if(resp.success){
                Swal.fire({icon:'success', title: editId ? 'Indikator diperbarui' : 'Indikator ditambah'});
                resetIndikatorForm();
                if($.fn.DataTable.isDataTable('#indikatorTable')){
                    $('#indikatorTable').DataTable().ajax.reload(null,false);
                }
            } else {
                Swal.fire('Gagal', resp.error || 'unknown', 'error');
            }
        }, 'json');
    });


    // delete indikator delegate
    $('body').on('click', '.btn-del-indikator', function(){
        var id = $(this).data('id');
        Swal.fire({ title: 'Hapus Indikator?', text: 'Yakin ingin menghapus?', icon: 'warning', showCancelButton: true }).then(function(res){
            if(res.isConfirmed){
                $.post('sarmut.ajax.php?action=delete_indikator', {id: id}, function(resp){
                    if(resp.success){ Swal.fire({icon:'success',title:'Terhapus'}); $('#indikatorTable').DataTable().ajax.reload(null,false); }
                    else Swal.fire('Gagal', (resp.error||''), 'error');
                }, 'json');
            }
        });
    });

    // delete sasaran delegate (with Swal confirmation)
    $('body').on('click', '.btn-del-sasaran', function(){

        var id = $(this).data('id');
        Swal.fire({ title: 'Hapus Sasaran?', text: 'Yakin ingin menghapus?', icon: 'warning', showCancelButton: true }).then(function(res){
            if(res.isConfirmed){
                $.post('sarmut.ajax.php?action=delete_sasaran', {id: id}, function(resp){
                    if(resp.success){ Swal.fire({icon:'success',title:'Terhapus'}); $('#sasaranTable').DataTable().ajax.reload(null,false); }
                    else Swal.fire('Gagal', (resp.error||''), 'error');
                }, 'json');
            }
        });
    });

    // open indikator modal: load sasaran options for sarmut
    $('#sarmutTable').on('click', '.btn-open-indikator', function(){
        var sarmut_id = $(this).data('id');
        $('#mi_sarmut_id').val(sarmut_id);
        $('#mi_sasaran').empty();
        $.post('sarmut.ajax.php?action=fetch_sasaran', {sarmut_id: sarmut_id}, function(res){
            if(res.success){
                res.data.forEach(function(it){ $('#mi_sasaran').append('<option value="'+it.id+'">'+it.sasaran+'</option>'); });
                var m=new bootstrap.Modal(document.getElementById('modalAddIndikator')); m.show();
            } else Swal.fire('Gagal','Gagal mengambil daftar sasaran','error');
        }, 'json');
    });

    $('#mi_save').on('click', function(){
        var sasaran_id = $('#mi_sasaran').val();
        var indikator = $('#mi_indikator').val();
        $.post('sarmut.ajax.php?action=add_indikator', {sasaran_id: sasaran_id, indikator: indikator}, function(resp){
            if(resp.success){ Swal.fire({icon:'success',title:'Indikator ditambah'}); $('#mi_indikator').val(''); var _m2 = bootstrap.Modal.getOrCreateInstance(document.getElementById('modalAddIndikator')); _m2.hide(); }
            else Swal.fire('Gagal', (resp.error||''), 'error');
        }, 'json');
    });

    $('body').on('change', '#mi_sasaran', function(){
        $('#mi_indikator').val('');
    });

    // refresh sasaran dropdown untuk tab indikator berdasarkan sarmut terpilih
    $('body').on('change', '#ts_sarmut_indikator', function(){
        var sid = $(this).val();
        $('#ts_indikator_sasaran').val('');
        resetIndikatorForm();
        refreshSasaranIndikator(sid);
    });

    // chaining dropdown tab subindikator
    $('body').on('change', '#tsi_sarmut', function(){
        var sid = $(this).val();
        $('#tsi_indikator').empty().append('<option value="">Pilih Indikator</option>');
        refreshSasaranSub($('#tsi_sasaran'), sid);
    });

    $('body').on('change', '#tsi_sasaran', function(){
        refreshIndikatorSub($('#tsi_indikator'), $(this).val());
    });

    $('#tsi_cancel').on('click', function(){
        resetSubindikatorForm();
        $('#tsi_sarmut').val('');
        $('#tsi_sasaran').empty().append('<option value="">Pilih Sasaran</option>');
        $('#tsi_indikator').empty().append('<option value="">Pilih Indikator</option>');
    });

    $('#tsi_save').on('click', function(){
        var editId = $('#tsi_edit_id').val();
        var indikatorId = $('#tsi_indikator').val();
        var sub = $.trim($('#tsi_subindikator').val());
        var jenis = $.trim($('#tsi_jenis').val());
        var satuan = $.trim($('#tsi_satuan').val());
        var target = $.trim($('#tsi_target').val());
        var baseline = $.trim($('#tsi_baseline').val());
        var units = unitChoices ? unitChoices.getValue(true) : [];

        if(!$('#tsi_sarmut').val()){ Swal.fire('Pilih Sarmut','Pilih Sarmut terlebih dahulu','warning'); return; }
        if(!$('#tsi_sasaran').val()){ Swal.fire('Pilih Sasaran','Pilih Sasaran terlebih dahulu','warning'); return; }
        if(!indikatorId){ Swal.fire('Pilih Indikator','Pilih Indikator terlebih dahulu','warning'); return; }
        if(!sub){ Swal.fire('Kosong','Isi kolom Subindikator terlebih dahulu','warning'); return; }

        var action = editId ? 'update_subindikator' : 'add_subindikator';
        var payload = {
            indikator_id: indikatorId,
            subindikator: sub,
            jenis: jenis,
            satuan: satuan,
            target: target,
            baseline: baseline,
            units: JSON.stringify(units)
        };
        if(editId) payload.id = editId;

        $.post('sarmut.ajax.php?action=' + action, payload, function(resp){
            if(resp.success){
                Swal.fire({icon:'success', title: editId ? 'Subindikator diperbarui' : 'Subindikator ditambah'});
                resetSubindikatorForm();
                $('#tsi_sarmut').val('');
                $('#tsi_sasaran').empty().append('<option value="">Pilih Sasaran</option>');
                $('#tsi_indikator').empty().append('<option value="">Pilih Indikator</option>');
                if($.fn.DataTable.isDataTable('#subindikatorTable')){
                    $('#subindikatorTable').DataTable().ajax.reload(null,false);
                }
            } else {
                Swal.fire('Gagal', resp.error || 'unknown', 'error');
            }
        }, 'json');
    });

    $('#subindikatorTable').on('click', '.btn-edit-subindikator', function(){
        var id = $(this).data('id');
        loadSubindikatorForEdit(id);
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });

    $('body').on('click', '.btn-del-subindikator', function(){
        var id = $(this).data('id');
        Swal.fire({ title: 'Hapus Subindikator?', text: 'Yakin ingin menghapus?', icon: 'warning', showCancelButton: true }).then(function(res){
            if(res.isConfirmed){
                $.post('sarmut.ajax.php?action=delete_subindikator', {id: id}, function(resp){
                    if(resp.success){
                        Swal.fire({icon:'success',title:'Terhapus'});
                        if($.fn.DataTable.isDataTable('#subindikatorTable')){
                            $('#subindikatorTable').DataTable().ajax.reload(null,false);
                        }
                    } else Swal.fire('Gagal', (resp.error||''), 'error');
                }, 'json');
            }
        });
    });

    // open subindikator modal: need sasaran->indikator chaining
    $('#sarmutTable').on('click', '.btn-open-sub', function(){
        var sarmut_id = $(this).data('id');
        $('#msas_sasaran').empty(); $('#msas_indikator').empty();
        $.post('sarmut.ajax.php?action=fetch_sasaran', {sarmut_id: sarmut_id}, function(res){
            if(res.success){
                res.data.forEach(function(it){ $('#msas_sasaran').append('<option value="'+it.id+'">'+it.sasaran+'</option>'); });
                $('#msas_sasaran').trigger('change');
                var m=new bootstrap.Modal(document.getElementById('modalAddSub')); m.show();
            }
        }, 'json');
    });

    $('body').on('change', '#msas_sasaran', function(){
        var sid = $(this).val();
        $('#msas_indikator').empty();
        $.post('sarmut.ajax.php?action=fetch_indikator', {sasaran_id: sid}, function(res){
            if(res.success){ res.data.forEach(function(it){ $('#msas_indikator').append('<option value="'+it.id+'">'+it.indikator+'</option>'); }); }
        }, 'json');
    });

    $('#msas_save').on('click', function(){
        var indikator_id = $('#msas_indikator').val();
        var sub = $('#msas_sub').val();
        $.post('sarmut.ajax.php?action=add_subindikator', {indikator_id: indikator_id, subindikator: sub}, function(resp){
            if(resp.success){
                Swal.fire({icon:'success',title:'Subindikator ditambah'});
                $('#msas_sub').val('');
                var _m3 = bootstrap.Modal.getOrCreateInstance(document.getElementById('modalAddSub'));
                _m3.hide();
                if($.fn.DataTable.isDataTable('#subindikatorTable')){
                    $('#subindikatorTable').DataTable().ajax.reload(null,false);
                }
            }
            else Swal.fire('Gagal', (resp.error||''), 'error');
        }, 'json');
    });
});
</script>
