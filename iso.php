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
                                <a class="nav-link active" data-bs-toggle="tab" href="#tabIso">ISO</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#tabKlausul">Klausul</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#tabSubklausul">Subklausul</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#tabIndikatorIso">Item Indikator ISO</a>
                            </li>
                        </ul>
                            <!-- future tabs for other entities can be added here -->
                    </div>
                    <div class="card-body px-3 py-2">
                        <div class="tab-content">
                            <div class="tab-pane fade show active" id="tabIso">
                                <div class="row">
                            <div class="col-md-5">
                                <h5>Form ISO</h5>
                                <form id="isoForm">
                                    <input type="hidden" id="id_iso" value="">
                                    <div class="mb-2">
                                        <label>Judul</label>
                                        <input type="text" name="title" class="form-control" required>
                                    </div>
                                    <div id="klausulContainer">
                                        <!-- klausul blocks inserted here -->
                                    </div>
                                    <div class="mb-2">
                                        <button id="saveIso" type="submit" class="btn btn-success btn-sm">Simpan ISO</button>
                                    </div>
                                </form>
                            </div>
                            <div class="col-md-7">
                                <h5>Daftar ISO</h5>
                                <table id="isoTable" class="table table-bordered table-striped" style="width:100%">
                                    <thead>
                                        <tr>
                                            <th>No.</th>
                                            <th>Judul</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="tabKlausul">
                                <div class="p-3">
                                    <div class="mb-2">
                                        <h5>Form Klausul</h5>
                                        <form id="klausulQuickAdd">
                                            <input type="hidden" id="ts_edit_id" value="">
                                            <div class="form-group d-flex flex-wrap mb-2">
                                                <label for="ts_iso" class="form-label me-2">Pilih ISO</label>
                                                <select id="ts_iso" class="form-control form-control-sm" style="min-width:220px" required>
                                                    <option value="">Pilih ISO</option>
                                                </select>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-2">
                                                    <div class="form-group mb-2">
                                                        <label class="form-label">No. Urut Klausul</label>
                                                        <input type="number" id="ts_no_urut" class="form-control" placeholder="No. urut klausul" required>
                                                    </div>
                                                </div>
                                                <div class="col-md-10">
                                                    <div class="form-group mb-2">
                                                        <label class="form-label">Klausul</label>
                                                        <textarea id="ts_klausul" class="form-control" placeholder="Masukkan teks klausul" required></textarea>
                                                    </div>
                                                </div>
                                            </div>                                            
                                            <div>
                                                <button id="ts_add" type="button" class="btn btn-sm mt-3 btn-success">Simpan Klausul</button>
                                                <button id="ts_cancel" type="button" class="btn btn-sm mt-3 btn-secondary ms-2">Batal</button>
                                            </div>
                                        </form>
                                    </div>
                                    <table id="klausulTable" class="table table-bordered table-striped" style="width:100%">
                                        <thead>
                                            <tr><th>Iso</th><th>Klausul</th><th>Aksi</th></tr>
                                        </thead>
                                    </table>


                                </div>
                            </div>
                            <div class="tab-pane fade" id="tabSubklausul">
                                <div class="p-3">
                                    <div class="mb-2">
                                        <h5>Form Indikator</h5>
                                        <form id="indikatorQuickAdd">
                                            <input type="hidden" id="ti_edit_id" value="">
                                            <input type="hidden" id="ts_edit_id_subklausul" value="">
                                            <div class="form-group d-flex flex-wrap mb-2">
                                                <label for="ts_iso_subklausul" class="form-label me-2">Pilih ISO</label>
                                                <select id="ts_iso_subklausul" class="form-control form-control-sm" style="min-width:220px" required>
                                                    <option value="">Pilih ISO</option>
                                                </select>
                                            </div>
                                            <div class="form-group mb-2">
                                                <label class="form-label">Klausul</label>
                                                <select id="ts_subklausul_klausul" class="form-control form-control-sm mb-2" required>
                                                    <option value="">Pilih Klausul</option>
                                                </select>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-2">
                                                    <div class="form-group mb-2">
                                                        <label class="form-label">No. Urut Subklausul</label>
                                                        <input type="number" id="ts_sub_no_urut" class="form-control" placeholder="No. urut subklausul" required>
                                                    </div>
                                                </div>
                                                <div class="col-md-10">
                                                    <div class="form-group mb-2">
                                                        <label class="form-label">Subklausul</label>
                                                        <textarea id="ts_subklausul" class="form-control" placeholder="Masukkan teks subklausul" required></textarea>
                                                    </div>
                                                </div>
                                            </div>
                                            <div>
                                                <button id="ts_addsubklausul" type="button" class="btn btn-sm mt-3 btn-success">Simpan Subklausul</button>
                                                <button id="ts_cancelsubklausul" type="button" class="btn btn-sm mt-3 btn-secondary ms-2">Batal</button>
                                            </div>
                                        </form>
                                    </div>
                                    <table id="subklausulTable" class="table table-bordered table-striped" style="width:100%">
                                        <thead>
                                            <tr><th>Iso</th><th>Klausul</th><th>No. Urut</th><th>Subklausul</th><th>Aksi</th></tr>
                                        </thead>
                                    </table>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="tabIndikatorIso">
                                <div class="p-3">
                                    <div class="mb-2">
                                        <h5>Form Indikator ISO</h5>
                                        <form id="indikatorIsoQuickAdd">
                                            <input type="hidden" id="tsi_edit_id" value="">
                                            <div class="form-group d-flex flex-wrap mb-2">
                                                <label for="tsi_iso" class="form-label me-2">Pilih Iso</label>
                                                <select id="tsi_iso" class="form-control form-control-sm" style="min-width:220px" required>
                                                    <option value="">Pilih Iso</option>
                                                </select>
                                            </div>
                                            <div class="form-group mb-2">
                                                <label class="form-label">Subklausul</label>
                                                <select id="tsi_subklausul" class="form-control form-control-sm" multiple required style="width:100%"></select>
                                            </div>
                                            <div class="form-group mb-2">
                                                <label class="form-label">Indikator</label>
                                                <textarea id="tsi_indikator_text" class="form-control form-control-sm" rows="3" placeholder="Isi indikator" required></textarea>
                                            </div>
                                            <div>
                                                <button id="tsi_save" type="button" class="btn btn-sm mt-3 btn-success">Simpan Indikator ISO</button>
                                                <button id="tsi_cancel" type="button" class="btn btn-sm mt-3 btn-secondary ms-2">Batal</button>
                                            </div>
                                        </form>
                                    </div>
                                    <table id="subindikatorTable" class="table table-bordered table-striped" style="width:100%">
                                        <thead>
                                            <tr>
                                                <th>No.</th>
                                                <th>Klausul</th>
                                                <th>Indikator ISO</th>
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
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" />
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(function(){
    // helpers to build nested inputs
    function createKlausulBlock(idx){
        var html = '<div class="card mb-2 klausul-block" data-idx="'+idx+'">'
            +'<div class="card-body">'
            +'<div class="d-flex justify-content-between align-items-center">'
            +'<h6>Klausul <span class="badge bg-secondary">#'+(idx+1)+'</span></h6>'
            +'<div><button type="button" class="btn btn-sm btn-danger removeKlausul">Hapus</button></div>'
            +'</div>'
            +'<div class="mb-2"><textarea class="form-control klausul-text" placeholder="Isi klausul"></textarea></div>'
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

    var klausulIdx = 0;
    $('#addKlausul').on('click', function(){
        $('#klausulContainer').append(createKlausulBlock(klausulIdx++));
    });
    $('#klausulContainer').on('click', '.removeKlausul', function(){
        $(this).closest('.klausul-block').remove();
    });
    $('#klausulContainer').on('click', '.addIndikator', function(){
        $(this).siblings('.indikator-list').append(createIndikatorBlock());
    });
    $('#klausulContainer').on('click', '.removeIndikator', function(){
        $(this).closest('.indikator-block').remove();
    });
    $('#klausulContainer').on('click', '.addSubindikator', function(){
        $(this).siblings('.subindikator-list').append(createSubindikatorBlock());
    });
    $('#klausulContainer').on('click', '.removeSubindikator', function(){
        $(this).closest('.subindikator-block').remove();
    });

    $('#resetForm').on('click', function(){
        $('#isoForm')[0].reset();
        $('#klausulContainer').empty();
    });

    // DataTables server-side
    if ($.fn.DataTable.isDataTable('#isoTable')) {
        $('#isoTable').DataTable().clear().destroy();
    }
    var table = $('#isoTable').DataTable({
        serverSide: true,
        processing: true,
        ajax: {
            url: 'iso.ajax.php?action=fetch',
            type: 'POST'
        },
        columns: [
            { data: null, orderable: false, searchable: false },
            { data: 'title' },
            { data: 'actions', orderable: false, searchable: false }
        ],
        order: [[1,'asc']],
        pageLength: 25,
        lengthMenu: [25,50,100],
        drawCallback: function(settings){
            var api = this.api();
            api.column(0, {page:'current'}).nodes().each(function(cell, i){
                cell.innerHTML = i + 1 + (api.page() * api.page.len());
            });
        }
    });

    // Edit Iso: load into form for update
    $('#isoTable').on('click', '.btn-edit-iso', function(){
        var id = $(this).data('id');
        $.post('iso.ajax.php?action=get_iso', {id: id}, function(resp){
            if(resp.success){
                var d = resp.data;
                $('[name=title]').val(d.title);
                $('#iso_id').val(d.id);
                $('#klausulContainer').empty();
                var tabEl = document.querySelector('a[data-bs-toggle="tab"][href="#tabIso"]');
                if(tabEl) new bootstrap.Tab(tabEl).show();
            } else Swal.fire('Gagal', (resp.error||''), 'error');
        }, 'json');
    });

    // Delete Iso
    $('body').on('click', '.btn-del-iso', function(){
        var id = $(this).data('id');
        Swal.fire({ title: 'Hapus Iso?', text: 'Yakin ingin menghapus Iso beserta isinya?', icon: 'warning', showCancelButton: true }).then(function(res){
            if(res.isConfirmed){
                $.post('iso.ajax.php?action=delete_iso', {id: id}, function(resp){
                    if(resp.success){ Swal.fire({icon:'success',title:'Terhapus'}); table.ajax.reload(null,false); }
                    else Swal.fire('Gagal', (resp.error||''), 'error');
                }, 'json');
            }
        });
    });

    // Save handler: create or update Iso
    $('#isoForm').on('submit', function(e){
        e.preventDefault();
        var id = $('#iso_id').val();
        var title = $.trim($('[name=title]').val());
        if(!title){ Swal.fire('Kesalahan','Judul wajib diisi','error'); return; }

        if(id){
            // update
            $.post('iso.ajax.php?action=update_iso', {id: id, title: title}, function(resp){
                if(resp.success){ Swal.fire({icon:'success',title:'Tersimpan'}); $('#isoForm')[0].reset(); $('#iso_id').val(''); table.ajax.reload(null,false); }
                else { Swal.fire('Gagal', resp.error||'unknown','error'); }
            }, 'json').fail(function(){ Swal.fire('Gagal','Koneksi gagal','error'); });
        } else {
            // create
            var payload = { title: title };
            $.ajax({
                url: 'iso.ajax.php?action=iso_save',
                method: 'POST',
                data: {data: JSON.stringify(payload)},
                dataType: 'json',
                success: function(resp){
                    if(resp.success){ Swal.fire({icon:'success',title:'Tersimpan'}); $('#isoForm')[0].reset(); table.ajax.reload(null,false); }
                    else { Swal.fire('Gagal', resp.error||'unknown','error'); }
                },
                error: function(){ Swal.fire('Gagal','Koneksi gagal','error'); }
            });
        }
    });

    // open quick add on button
    $('#btnAdd').on('click', function(){
        $('#klausulContainer').empty(); klausulIdx=0; $('#isoForm')[0].reset(); $('#addKlausul').click();
    });

        // Modals HTML appended to body (use template literal)
        var modals = `
        <div class="modal fade" id="modalAddKlausul" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Tambah Klausul</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="ms_iso_id">
                        <input type="hidden" id="ms_klausul_id">
                        <div class="mb-2"><textarea id="ms_klausul" class="form-control" placeholder="Klausul"></textarea></div>
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
                        <input type="hidden" id="mi_iso_id">
                        <div class="mb-2"><label>Klausul</label><select id="mi_klausul" class="form-control"></select></div>
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
                        <div class="mb-2"><label>Klausul</label><select id="msas_klausul" class="form-control"></select></div>
                        <div class="mb-2"><label>Indikator</label><select id="msas_indikator" class="form-control"></select></div>
                        <div class="mb-2"><input id="msas_sub" class="form-control" placeholder="Subindikator"></div>
                    </div>
                    <div class="modal-footer"><button id="msas_save" class="btn btn-primary">Simpan</button><button class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button></div>
                </div>
            </div>
        </div>
        `;
        $('body').append(modals);

    // delegate add klausul from iso table: open inline Klausul tab and prefill iso
    $('#isoTable').on('click', '.btn-add-klausul', function(){
        var id = $(this).data('id');
        $('#ts_edit_id').val('');
        initKlausulTab();
        var tabEl = document.querySelector('a[data-bs-toggle="tab"][href="#tabKlausul"]');
        if(tabEl) new bootstrap.Tab(tabEl).show();
        setTimeout(function(){ $('#ts_iso').val(id).trigger('change'); $('#ts_klausul').focus(); }, 200);
    });

    // delegate edit klausul from klausul table: populate inline form
    $('#klausulTable').on('click', '.btn-edit-klausul', function(){
        var id = $(this).data('id');
        $.post('iso.ajax.php?action=get_klausul', {id: id}, function(resp){
            if(resp.success){
                $('#ts_edit_id').val(resp.data.id);
                $('#ts_iso').val(resp.data.iso_id).trigger('change');
                $('#ts_no_urut').val(resp.data.nomor_klausul || '');
                $('#ts_klausul').val(resp.data.klausul);
                var tabEl = document.querySelector('a[data-bs-toggle="tab"][href="#tabKlausul"]');
                if(tabEl) new bootstrap.Tab(tabEl).show();
                $('#ts_klausul').focus();
            } else Swal.fire('Gagal', (resp.error||''), 'error');
        }, 'json');
    });

    $('#ms_save').on('click', function(){
        var iso_id = $('#ms_iso_id').val();
        var sas = $('#ms_klausul').val();
        var edit_id = $('#ms_klausul_id').val();
        if(edit_id){
            $.post('iso.ajax.php?action=update_klausul', {id: edit_id, klausul: sas}, function(resp){
                if(resp.success){ Swal.fire({icon:'success',title:'Klausul diperbarui'}); $('#ms_klausul').val(''); $('#ms_klausul_id').val(''); var _m = bootstrap.Modal.getOrCreateInstance(document.getElementById('modalAddKlausul')); _m.hide(); $('#klausulTable').DataTable().ajax.reload(null,false); }
                else Swal.fire('Gagal', (resp.error||''), 'error');
            }, 'json');
        } else {
            $.post('iso.ajax.php?action=add_klausul', {iso_id: iso_id, klausul: sas}, function(resp){
                if(resp.success){ Swal.fire({icon:'success',title:'Klausul ditambah'}); $('#ms_klausul').val(''); var _m = bootstrap.Modal.getOrCreateInstance(document.getElementById('modalAddKlausul')); _m.hide(); $('#klausulTable').DataTable().ajax.reload(null,false); }
                else Swal.fire('Gagal', (resp.error||''), 'error'); 
            }, 'json');
        }
    });

    // initialize klausul tab: load iso options and DataTable
    function initKlausulTab(){
        // load iso options
        $.post('iso.ajax.php?action=fetch_iso_options', {}, function(resp){
            if(resp.success){
                $('#ts_iso').empty();
                resp.data.forEach(function(it){ $('#ts_iso').append('<option value="'+it.id+'">'+it.title+'</option>'); });
            }
        }, 'json');

        // DataTable khusus tab Klausul
        if ($.fn.DataTable.isDataTable('#klausulTable')){ $('#klausulTable').DataTable().clear().destroy(); }
        $('#klausulTable').DataTable({
            serverSide: true,
            processing: true,
            ajax: { url: 'iso.ajax.php?action=fetch_klausul_table', type: 'POST' },
            columns: [
                { data: 'iso_title' },
                { data: 'klausul' },
                { data: 'actions', orderable:false, searchable:false }
            ],
            pageLength: 25
        });
    }

    // initialize indikator tab: load iso + klausul dropdown + DataTable
    function initIndikatorTab(){
        // load iso options
        $.post('iso.ajax.php?action=fetch_iso_options', {}, function(resp){
            if(resp.success){
                $('#ts_iso_indikator').empty();
                $('#ts_iso_indikator').append('<option value="">Pilih Iso</option>');
                resp.data.forEach(function(it){ $('#ts_iso_indikator').append('<option value="'+it.id+'">'+it.title+'</option>'); });
            }
        }, 'json');

        // reset klausul dropdown (kecuali sedang edit)
        if(!$('#ti_edit_id').val()){
            $('#ts_indikator_klausul').empty();
            $('#ts_indikator_klausul').append('<option value="">Pilih Klausul</option>');
        }

        // DataTable khusus tab Indikator
        if ($.fn.DataTable.isDataTable('#indikatorTable')){ $('#indikatorTable').DataTable().clear().destroy(); }
        $('#indikatorTable').DataTable({
            serverSide: true,
            processing: true,
            ajax: { url: 'iso.ajax.php?action=fetch_indikator_table', type: 'POST' },
            columns: [
                { data: null, orderable: false, searchable: false },
                { data: 'iso_title' },
                { data: 'klausul' },
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

    function refreshKlausulIndikator(isoId, callback){
        if(!isoId){
            $('#ts_indikator_klausul').empty();
            $('#ts_indikator_klausul').append('<option value="">Pilih Klausul</option>');
            if(callback) callback();
            return;
        }
        $.post('iso.ajax.php?action=fetch_klausul_options', {iso_id: isoId}, function(resp){
            if(resp.success){
                $('#ts_indikator_klausul').empty();
                $('#ts_indikator_klausul').append('<option value="">Pilih Klausul</option>');
                resp.data.forEach(function(it){ $('#ts_indikator_klausul').append('<option value="'+it.id+'">'+it.klausul+'</option>'); });
                if(resp.data.length === 1){
                    $('#ts_indikator_klausul').val(resp.data[0].id);
                }
            }
            if(callback) callback();
        }, 'json');
    }

    function refreshKlausulSub($select, isoId, callback){
        if(!isoId){
            $select.empty().append('<option value="">Pilih Klausul</option>');
            if(callback) callback();
            return;
        }
        $.post('iso.ajax.php?action=fetch_klausul_options', {iso_id: isoId}, function(resp){
            $select.empty().append('<option value="">Pilih Klausul</option>');
            if(resp.success){
                resp.data.forEach(function(it){ $select.append('<option value="'+it.id+'">'+it.klausul+'</option>'); });
            }
            if(callback) callback();
        }, 'json');
    }

    function refreshIndikatorSub($select, klausulId, callback){
        if(!klausulId){
            $select.empty().append('<option value="">Pilih Indikator</option>');
            if(callback) callback();
            return;
        }
        $.post('iso.ajax.php?action=fetch_indikator', {klausul_id: klausulId}, function(resp){
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
        $.post('iso.ajax.php?action=fetch_unit_options', {}, function(resp){
            if(resp.success) unitOptionsCache = resp.data || [];
            done();
        }, 'json');
    }

    function loadSubindikatorForEdit(id){
        $.post('iso.ajax.php?action=get_subindikator', {id: id}, function(resp){
            if(resp.success){
                var d = resp.data;
                $('#tsi_edit_id').val(d.id);
                $('#tsi_indikator_text').val(d.subindikator || '');
                $('#tsi_save').text('Perbarui Indikator ISO');
                loadIsoOptions(function(){
                    $('#tsi_iso').val(d.iso_id).trigger('change');
                    loadSubklausulOptions(d.iso_id, function(){
                        $('#tsi_subklausul').val((d.subklausul_ids || []).map(String)).trigger('change');
                        $('#tsi_indikator_text').focus();
                    });
                });
            } else Swal.fire('Gagal', (resp.error||''), 'error');
        }, 'json');
    }

    function resetSubindikatorForm(){
        $('#tsi_edit_id').val('');
        $('#tsi_iso').val('').trigger('change');
        $('#tsi_subklausul').val(null).trigger('change');
        $('#tsi_indikator_text').val('');
        $('#tsi_save').text('Simpan Indikator ISO');
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

    function loadSubklausulOptions(isoId, callback){
        if(typeof isoId === 'function'){
            callback = isoId;
            isoId = 0;
        }
        var data = {};
        if(isoId && parseInt(isoId, 10) > 0){
            data.iso_id = isoId;
        }
        $.post('iso.ajax.php?action=fetch_subklausul_options', data, function(resp){
            if(resp.success){
                var $sub = $('#tsi_subklausul');
                $sub.empty();
                resp.data.forEach(function(group){
                    if(group.children && group.children.length){
                        var $optgroup = $('<optgroup>').attr('label', group.label);
                        group.children.forEach(function(item){
                            $optgroup.append($('<option>').val(item.id).text(item.text));
                        });
                        $sub.append($optgroup);
                    }
                });
                if($.fn.select2){
                    if($sub.hasClass('select2-hidden-accessible')){
                        $sub.select2('destroy');
                    }
                    $sub.select2({
                        placeholder: 'Pilih Subklausul',
                        width: '100%',
                        closeOnSelect: false,
                        allowClear: true
                    });
                }
                if(callback) callback();
            } else {
                if(callback) callback();
            }
        }, 'json');
    }

    function loadIsoOptions(callback){
        $.post('iso.ajax.php?action=fetch_iso_options', {}, function(resp){
            if(resp.success){
                var $iso = $('#tsi_iso');
                $iso.empty().append('<option value="">Pilih ISO</option>');
                resp.data.forEach(function(it){ $iso.append('<option value="'+it.id+'">'+it.title+'</option>'); });
                if($.fn.select2){
                    if($iso.hasClass('select2-hidden-accessible')){
                        $iso.select2('destroy');
                    }
                    $iso.select2({
                        placeholder: 'Pilih ISO',
                        width: '100%',
                        allowClear: true
                    });
                }
            }
            if(callback) callback();
        }, 'json');
    }

    function initSubindikatorTab(){
        loadIsoOptions(function(){
            loadSubklausulOptions(function(){
                if(!$('#tsi_edit_id').val()){
                    resetSubindikatorForm();
                }
            });

            if(pendingSubEditId){
                var editId = pendingSubEditId;
                pendingSubEditId = null;
                loadSubindikatorForEdit(editId);
            }
        });

        if ($.fn.DataTable.isDataTable('#subindikatorTable')){ $('#subindikatorTable').DataTable().clear().destroy(); }
        $('#subindikatorTable').DataTable({
            serverSide: true,
            processing: true,
            ajax: { url: 'iso.ajax.php?action=fetch_subindikator_table', type: 'POST' },
            columns: [
                { data: null, orderable: false, searchable: false },
                {
                    data: 'klausul',
                    orderable: false,
                    searchable: false,
                    render: function(data, type, row){
                        return data || '';
                    }
                },
                { data: 'indikator' },
                { data: 'actions', orderable: false, searchable: false }
            ],
            order: [[2, 'asc']],
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
    // init on tab show
    $('a[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
        var target = $(e.target).attr('href');
        if(target === '#tabKlausul') initKlausulTab();
        if(target === '#tabIndikatorIso') initSubindikatorTab();
        if(target === '#tabSubindikator') initSubindikatorTab();

        // tab Subklausul: load iso options + klausul dropdown chaining
        if(target === '#tabSubklausul'){
            $.post('iso.ajax.php?action=fetch_iso_options', {}, function(resp){
                if(resp.success){
                    $('#ts_iso_subklausul').empty();
                    $('#ts_iso_subklausul').append('<option value="">Pilih ISO</option>');
                    resp.data.forEach(function(it){
                        $('#ts_iso_subklausul').append('<option value="'+it.id+'">'+it.title+'</option>');
                    });
                }
            }, 'json');

            // Initialize subklausul DataTable
            if ($.fn.DataTable.isDataTable('#subklausulTable')){ 
                $('#subklausulTable').DataTable().clear().destroy(); 
            }
            $('#subklausulTable').DataTable({
                serverSide: true,
                processing: true,
                ajax: { url: 'iso.ajax.php?action=fetch_subklausul_table', type: 'POST' },
                columns: [
                    { data: 'iso_title' },
                    { data: 'klausul' },
                    { data: 'no_urut' },
                    { data: 'subklausul' },
                    { data: 'actions', orderable:false, searchable:false }
                ],
                pageLength: 25
            });

            // Reset edit form
            $('#ts_edit_id_subklausul').val('');
            $('#ts_addsubklausul').text('Simpan Subklausul');
        }
    });

    // chaining dropdown Subklausul: ISO -> Klausul
    $('body').on('change', '#ts_iso_subklausul', function(){
        var isoId = $(this).val();
        $('#ts_subklausul_klausul').val('');
        if(!isoId){
            $('#ts_subklausul_klausul').empty().append('<option value="">Pilih Klausul</option>');
            return;
        }
        $.post('iso.ajax.php?action=fetch_klausul', {iso_id: isoId}, function(resp){
            if(resp.success){
                $('#ts_subklausul_klausul').empty().append('<option value="">Pilih Klausul</option>');
                resp.data.forEach(function(it){
                    $('#ts_subklausul_klausul').append('<option value="'+it.id+'">'+it.klausul+'</option>');
                });
            }
        }, 'json');
    });

    // when Klausul changed: load subklausul dropdown (for quick add form)
    $('body').on('change', '#ts_subklausul_klausul', function(){
        var klausulId = $(this).val();
        if(!klausulId){
            // keep textarea as input, so just clear list table filters
            return;
        }
        // fill DataTable for Subklausul tab
        if($.fn.DataTable.isDataTable('#subklausulTable')){
            $('#subklausulTable').DataTable().ajax.reload(null,false);
        }
    });

    // edit subklausul handler: load into form
    $('#subklausulTable').on('click', '.btn-edit-subklausul', function(){
        var id = $(this).data('id');
        $.post('iso.ajax.php?action=get_subklausul', {id: id}, function(resp){
            if(resp.success){
                $('#ts_iso_subklausul').val(resp.data.iso_id).trigger('change');
                setTimeout(function(){
                    $('#ts_subklausul_klausul').val(resp.data.klausul_id);
                    $('#ts_sub_no_urut').val(resp.data.nomor_sub || '');
                    $('#ts_subklausul').val(resp.data.subklausul);
                    $('#ts_edit_id_subklausul').val(resp.data.id);
                    $('#ts_addsubklausul').text('Perbarui Subklausul');
                    var tabEl = document.querySelector('a[data-bs-toggle="tab"][href="#tabSubklausul"]');
                    if(tabEl) new bootstrap.Tab(tabEl).show();
                    $('#ts_subklausul').focus();
                }, 200);
            } else Swal.fire('Gagal', (resp.error||''), 'error');
        }, 'json');
    });

    // delete subklausul handler
    $('body').on('click', '.btn-del-subklausul', function(){
        var id = $(this).data('id');
        Swal.fire({ 
            title: 'Hapus Subklausul?', 
            text: 'Yakin ingin menghapus Subklausul ini?', 
            icon: 'warning', 
            showCancelButton: true 
        }).then(function(res){
            if(res.isConfirmed){
                $.post('iso.ajax.php?action=delete_subklausul', {id: id}, function(resp){
                    if(resp.success){ 
                        Swal.fire({icon:'success',title:'Subklausul dihapus'}); 
                        $('#subklausulTable').DataTable().ajax.reload(null,false); 
                    }
                    else Swal.fire('Gagal', (resp.error||''), 'error');
                }, 'json');
            }
        });
    });

    // save subklausul (tabSubklausul)
    $('#ts_addsubklausul').on('click', function(){
        var iso_id = $('#ts_iso_subklausul').val();
        var klausul_id = $('#ts_subklausul_klausul').val();
        var sub = $.trim($('#ts_subklausul').val());
        var nomor_sub = parseInt($('#ts_sub_no_urut').val());
        var edit_id = $('#ts_edit_id_subklausul').val();

        if(!iso_id){ Swal.fire('Pilih ISO','Pilih ISO terlebih dahulu','warning'); return; }
        if(!klausul_id){ Swal.fire('Pilih Klausul','Pilih Klausul terlebih dahulu','warning'); return; }
        if(!sub){ Swal.fire('Kosong','Isi kolom Subklausul terlebih dahulu','warning'); return; }

        var $btn = $(this);
        $btn.prop('disabled', true).text('Menyimpan...');

        if(edit_id){
            // Update mode
            $.post('iso.ajax.php?action=update_subklausul', {
                id: edit_id,
                nomor_subklausul: (isNaN(nomor_sub) ? 0 : nomor_sub),
                subklausul: sub
            }, function(resp){
                if(resp.success){
                    Swal.fire({icon:'success', title:'Subklausul diperbarui'});
                    $('#ts_subklausul').val('');
                    $('#ts_sub_no_urut').val('');
                    $('#ts_edit_id_subklausul').val('');
                    $('#ts_addsubklausul').text('Simpan Subklausul');
                    if($.fn.DataTable.isDataTable('#subklausulTable')){
                        $('#subklausulTable').DataTable().ajax.reload(null,false);
                    }
                } else {
                    Swal.fire('Gagal', (resp.error||'unknown'), 'error');
                }
            }, 'json').always(function(){
                $btn.prop('disabled', false).text('Simpan Subklausul');
            });
        } else {
            // Add mode
            $.post('iso.ajax.php?action=add_subklausul', {
                iso_id: iso_id,
                klausul_id: klausul_id,
                nomor_subklausul: (isNaN(nomor_sub) ? 0 : nomor_sub),
                subklausul: sub
            }, function(resp){
                if(resp.success){
                    Swal.fire({icon:'success', title:'Subklausul ditambah'});
                    $('#ts_subklausul').val('');
                    $('#ts_sub_no_urut').val('');
                    // reload datatable
                    if($.fn.DataTable.isDataTable('#subklausulTable')){
                        $('#subklausulTable').DataTable().ajax.reload(null,false);
                    }
                } else {
                    Swal.fire('Gagal', (resp.error||'unknown'), 'error');
                }
            }, 'json').always(function(){
                $btn.prop('disabled', false).text('Simpan Subklausul');
            });
        }
    });

    $('#ts_cancelsubklausul').on('click', function(){
        $('#ts_subklausul').val('');
        $('#ts_sub_no_urut').val('');
        $('#ts_edit_id_subklausul').val('');
        $('#ts_addsubklausul').text('Simpan Subklausul');
    });




    // inline add/update from tab Klausul
    $('#ts_add').on('click', function(){
        var iso_id = $('#ts_iso').val();
        var sas = $('#ts_klausul').val();
        var nomor = $('#ts_no_urut').val();
        var edit_id = $('#ts_edit_id').val();
        if(!iso_id){ Swal.fire('Pilih Iso','Pilih Iso terlebih dahulu','warning'); return; }
        if(!$.trim(sas)){ Swal.fire('Kosong','Isi kolom Klausul terlebih dahulu','warning'); return; }
        if(edit_id){
            $.post('iso.ajax.php?action=update_klausul', {id: edit_id, klausul: sas, nomor_klausul: nomor}, function(resp){
                if(resp.success){ Swal.fire({icon:'success',title:'Klausul diperbarui'}); $('#ts_klausul').val(''); $('#ts_no_urut').val(''); $('#ts_edit_id').val(''); $('#klausulTable').DataTable().ajax.reload(null,false); }
                else Swal.fire('Gagal', (resp.error||''), 'error');
            }, 'json');
        } else {
            $.post('iso.ajax.php?action=add_klausul', {iso_id: iso_id, klausul: sas}, function(resp){
                if(resp.success){ Swal.fire({icon:'success',title:'Klausul ditambah'}); $('#ts_klausul').val(''); $('#ts_no_urut').val(''); $('#klausulTable').DataTable().ajax.reload(null,false); }
                else Swal.fire('Gagal', (resp.error||''), 'error');
            }, 'json');
        }
    });

    // cancel inline edit
    $('#ts_cancel').on('click', function(){ $('#ts_edit_id').val(''); $('#ts_klausul').val(''); $('#ts_no_urut').val(''); });

    // handle form indikator (tabIndikator)
    $('#ts_cancelindikator').on('click', function(){
        resetIndikatorForm();
        $('#ts_indikator_klausul').val('');
    });

    $('#indikatorTable').on('click', '.btn-edit-indikator', function(){
        var id = $(this).data('id');
        $.post('iso.ajax.php?action=get_indikator', {id: id}, function(resp){
            if(resp.success){
                var d = resp.data;
                $('#ti_edit_id').val(d.id);
                $('#ts_iso_indikator').val(d.iso_id);
                refreshKlausulIndikator(d.iso_id, function(){
                    $('#ts_indikator_klausul').val(d.klausul_id);
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
        var isoId = $('#ts_iso_indikator').val();
        var klausulId = $('#ts_indikator_klausul').val();
        var indikator = $.trim($('#ts_indikator').val());
        var editId = $('#ti_edit_id').val();

        if(!isoId){ Swal.fire('Pilih Iso','Pilih Iso terlebih dahulu','warning'); return; }
        if(!klausulId){ Swal.fire('Pilih Klausul','Pilih Klausul terlebih dahulu','warning'); return; }
        if(!indikator){ Swal.fire('Kosong','Isi kolom Indikator terlebih dahulu','warning'); return; }

        var action = editId ? 'update_indikator' : 'add_indikator';
        var payload = editId ? {id: editId, klausul_id: klausulId, indikator: indikator} : {klausul_id: klausulId, indikator: indikator};

        $.post('iso.ajax.php?action=' + action, payload, function(resp){
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
                $.post('iso.ajax.php?action=delete_indikator', {id: id}, function(resp){
                    if(resp.success){ Swal.fire({icon:'success',title:'Terhapus'}); $('#indikatorTable').DataTable().ajax.reload(null,false); }
                    else Swal.fire('Gagal', (resp.error||''), 'error');
                }, 'json');
            }
        });
    });

    // delete klausul delegate (with Swal confirmation)
    $('body').on('click', '.btn-del-klausul', function(){

        var id = $(this).data('id');
        Swal.fire({ title: 'Hapus Klausul?', text: 'Yakin ingin menghapus?', icon: 'warning', showCancelButton: true }).then(function(res){
            if(res.isConfirmed){
                $.post('iso.ajax.php?action=delete_klausul', {id: id}, function(resp){
                    if(resp.success){ Swal.fire({icon:'success',title:'Terhapus'}); $('#klausulTable').DataTable().ajax.reload(null,false); }
                    else Swal.fire('Gagal', (resp.error||''), 'error');
                }, 'json');
            }
        });
    });

    // open indikator modal: load klausul options for iso
    $('#isoTable').on('click', '.btn-open-indikator', function(){
        var iso_id = $(this).data('id');
        $('#mi_iso_id').val(iso_id);
        $('#mi_klausul').empty();
        $.post('iso.ajax.php?action=fetch_klausul', {iso_id: iso_id}, function(res){
            if(res.success){
                res.data.forEach(function(it){ $('#mi_klausul').append('<option value="'+it.id+'">'+it.klausul+'</option>'); });
                var m=new bootstrap.Modal(document.getElementById('modalAddIndikator')); m.show();
            } else Swal.fire('Gagal','Gagal mengambil daftar klausul','error');
        }, 'json');
    });

    $('#mi_save').on('click', function(){
        var klausul_id = $('#mi_klausul').val();
        var indikator = $('#mi_indikator').val();
        $.post('iso.ajax.php?action=add_indikator', {klausul_id: klausul_id, indikator: indikator}, function(resp){
            if(resp.success){ Swal.fire({icon:'success',title:'Indikator ditambah'}); $('#mi_indikator').val(''); var _m2 = bootstrap.Modal.getOrCreateInstance(document.getElementById('modalAddIndikator')); _m2.hide(); }
            else Swal.fire('Gagal', (resp.error||''), 'error');
        }, 'json');
    });

    $('body').on('change', '#mi_klausul', function(){
        $('#mi_indikator').val('');
    });

    // refresh klausul dropdown untuk tab indikator berdasarkan iso terpilih
    $('body').on('change', '#ts_iso_indikator', function(){
        var sid = $(this).val();
        $('#ts_indikator_klausul').val('');
        resetIndikatorForm();
        refreshKlausulIndikator(sid);
    });

    // chaining dropdown tab subindikator
    $('#tsi_cancel').on('click', function(){
        resetSubindikatorForm();
    });



    $('#tsi_iso').on('change', function(){
        var isoId = $(this).val();
        loadSubklausulOptions(isoId, function(){
            $('#tsi_subklausul').val(null).trigger('change');
        });
    });

    $('#tsi_save').on('click', function(){
        var editId = $('#tsi_edit_id').val();
        var subklausulIds = $('#tsi_subklausul').val() || [];
        var indikatorText = $.trim($('#tsi_indikator_text').val());

        if(!subklausulIds.length){ Swal.fire('Pilih Subklausul','Pilih satu atau lebih Subklausul terlebih dahulu','warning'); return; }
        if(!indikatorText){ Swal.fire('Isi Indikator','Isi indikator terlebih dahulu','warning'); return; }

        var action = editId ? 'update_subindikator' : 'add_subindikator';
        var payload = {
            indikator: indikatorText,
            subklausul_ids: subklausulIds
        };
        if(editId){
            payload.id = editId;
        }

        $.post('iso.ajax.php?action=' + action, payload, function(resp){
            if(resp.success){
                Swal.fire({icon:'success', title: editId ? 'Indikator ISO diperbarui' : 'Indikator ISO ditambah'});
                resetSubindikatorForm();
                if($.fn.DataTable.isDataTable('#subindikatorTable')){
                    $('#subindikatorTable').DataTable().ajax.reload(null,false);
                }
            } else {
                Swal.fire('Gagal', resp.error || 'unknown', 'error');
            }
        }, 'json');
    });

    $('#subindikatorTable').on('click', '.btn-edit-subindikator', function(){
        var editId = $(this).data('id');
        var tabEl = document.querySelector('a[data-bs-toggle="tab"][href="#tabIndikatorIso"]');
        if(tabEl && tabEl.classList.contains('active')){
            loadSubindikatorForEdit(editId);
        } else {
            pendingSubEditId = editId;
            if(tabEl) new bootstrap.Tab(tabEl).show();
        }
    });

    $('body').on('click', '.btn-del-subindikator', function(){
        var id = $(this).data('id');
        Swal.fire({ title: 'Hapus Subindikator?', text: 'Yakin ingin menghapus?', icon: 'warning', showCancelButton: true }).then(function(res){
            if(res.isConfirmed){
                $.post('iso.ajax.php?action=delete_subindikator', {id: id}, function(resp){
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

    // open subindikator modal: need klausul->indikator chaining
    $('#isoTable').on('click', '.btn-open-sub', function(){
        var iso_id = $(this).data('id');
        $('#msas_klausul').empty(); $('#msas_indikator').empty();
        $.post('iso.ajax.php?action=fetch_klausul', {iso_id: iso_id}, function(res){
            if(res.success){
                res.data.forEach(function(it){ $('#msas_klausul').append('<option value="'+it.id+'">'+it.klausul+'</option>'); });
                $('#msas_klausul').trigger('change');
                var m=new bootstrap.Modal(document.getElementById('modalAddSub')); m.show();
            }
        }, 'json');
    });

    $('body').on('change', '#msas_klausul', function(){
        var sid = $(this).val();
        $('#msas_indikator').empty();
        $.post('iso.ajax.php?action=fetch_indikator', {klausul_id: sid}, function(res){
            if(res.success){ res.data.forEach(function(it){ $('#msas_indikator').append('<option value="'+it.id+'">'+it.indikator+'</option>'); }); }
        }, 'json');
    });

    $('#msas_save').on('click', function(){
        var indikator_id = $('#msas_indikator').val();
        var sub = $('#msas_sub').val();
        $.post('iso.ajax.php?action=add_subindikator', {indikator_id: indikator_id, subindikator: sub}, function(resp){
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
