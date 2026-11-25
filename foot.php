</div>
    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.js"></script>
    <script src="assets/vendors/perfect-scrollbar/perfect-scrollbar.min.js"></script>
    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.1/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.1/js/dataTables.bootstrap5.min.js"></script>
    <script src="assets/js/main.js"></script>
    <script src="assets/vendors/choices.js/choices.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.7.1/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="assets/js/digami-swal.js"></script>
    <script type="text/javascript">
        $(document).ready(function () {
            $('.table').not('.no-datatable, .audit-report-table, [data-no-datatable="true"]').each(function () {
                var $table = $(this);
                if ($.fn.DataTable.isDataTable(this)) return;

                var headerCount = $table.find('thead tr:first th').length;
                if (headerCount === 0 || $table.find('tbody').length === 0) return;

                var validRows = true;
                $table.find('tbody tr').each(function () {
                    var cellCount = $(this).children('td,th').length;
                    var hasSpan = $(this).children('[colspan],[rowspan]').length > 0;
                    if (hasSpan || cellCount !== headerCount) {
                        validRows = false;
                        return false;
                    }
                });

                if (!validRows) return;
                $table.DataTable();
            });
        });
        </script>
    
</body>
