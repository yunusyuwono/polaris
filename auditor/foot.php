</div>
    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.js"></script>
    <script src="../assets/vendors/perfect-scrollbar/perfect-scrollbar.min.js"></script>
    <script src="../assets/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.1/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.1/js/dataTables.bootstrap5.min.js"></script>
    <script src="../assets/vendors/simple-datatables/simple-datatables.js"></script>
    <script src="../assets/js/main.js"></script>
    <script src="../assets/vendors/choices.js/choices.min.js"></script>
    <script src="../assets/vendors/chartjs/Chart.min.js"></script>
    <script src="../assets/vendors/sweetalert2/sweetalert2.all.min.js"></script>
    <script src="../assets/js/digami-swal.js"></script>
    <script type="text/javascript">
        $(document).ready(function () {
            // Auto-initialize DataTables for eligible tables
            var dtTargets = ['#catatanTable', '#lk2Table', '#triangulasiTable', '#ptkTable'];
            dtTargets.forEach(function(sel) {
                var $tbl = $(sel);
                if ($tbl.length && !$.fn.DataTable.isDataTable(sel)) {
                    $tbl.DataTable({
                        pageLength: 25,
                        language: {
                            search: "Cari:",
                            lengthMenu: "Tampilkan _MENU_ baris",
                            info: "Menampilkan _START_ - _END_ dari _TOTAL_ butir",
                            infoEmpty: "Tidak ada data",
                            zeroRecords: "Tidak ada data yang cocok",
                            paginate: { previous: "Sebelumnya", next: "Berikutnya" }
                        },
                        columnDefs: [{ orderable: false, targets: -1 }]
                    });
                }
            });

            // Generic fallback for other tables (exclude no-datatable)
            $('.table').not('.no-datatable, [data-no-datatable="true"], #catatanTable, #lk2Table, #triangulasiTable, #ptkTable').each(function () {
                var $table = $(this);
                if ($.fn.DataTable.isDataTable(this)) return;
                var headerCount = $table.find('thead tr:first th').length;
                if (headerCount === 0 || $table.find('tbody').length === 0) return;
                var validRows = true;
                $table.find('tbody tr').each(function () {
                    var cellCount = $(this).children('td,th').length;
                    var hasSpan = $(this).children('[colspan],[rowspan]').length > 0;
                    if (hasSpan || cellCount !== headerCount) { validRows = false; return false; }
                });
                if (!validRows) return;
                $table.DataTable();
            });
        });
    </script>
    
</body>

