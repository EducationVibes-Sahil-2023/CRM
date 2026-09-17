<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">

                        <div class="table-auto-transfer">
                            <h3>Lead Transfer List</h3>
                            <hr>
                            <?php
                            render_datatable(array("Name", "Phone", "Update Count", "Duration", "Ideal Time", "Type", "Source", "Status", "Assigned", "Created Date", "Assigned Date"), 'table-auto-transfer');
                            ?>
                            <hr class="hr-panel-heading" />

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
<script>
    $(function() {

        setTimeout(() => {
            if ($(".table-auto-transfer").length > 0 && $(".table-auto-transfer").is(':visible')) {
                if ($.fn.DataTable.isDataTable('.table-table-auto-transfer')) {
                    $('.table-table-auto-transfer').DataTable().destroy();
                }
                initDataTable('.table-table-auto-transfer', admin_url + 'leads/auto_transfer_table/transfer_leads_list', 'undefined', 'undefined', 'undefined', [0, 'desc']);
                return false;
            }
        }, 1000);


        // $.ajax({
        //     url: '<?= admin_url('leads/auto_transfer_table/transfer_leads_list'); ?>',
        //     type: 'POST',
        //     dataType: 'json',
        //     success: function(response) {

        //         $('#dynamicTable').DataTable({
        //             destroy: true,
        //             data: response.data,
        //             columns: response.columns,
        //             pageLength: 25,
        //             responsive: true
        //         });

        //     }
        // });

    });

    // $(function() {



    //     var table_pending = initDataTable(
    //         '.table-pending-leads',
    //         '<?= admin_url('leads/pending_table'); ?>'
    //     );

    //     var table_closed = initDataTable(
    //         '.table-closed-leads',
    //         '<?= admin_url('leads/closed_table'); ?>'
    //     );



    //     function periodFilter() {

    //         return new Promise((resolve, reject) => {

    //             try {

    //                 var dt = table_leads.DataTable();

    //                 // Attach draw event BEFORE reload
    //                 dt.one('draw.dt', function() {

    //                     hide_loader("apply_filter");

    //                     if (typeof set_datatable_string === "function") {
    //                         set_datatable_string();
    //                     }

    //                     status_summury_filter = 0;

    //                     resolve();
    //                 });

    //                 // Reload table
    //                 dt.page(0).draw(false);

    //             } catch (error) {
    //                 reject(error);
    //             }

    //         });
    //     }

    // });
</script>