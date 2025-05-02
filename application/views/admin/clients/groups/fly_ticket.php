<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<style>
    .table>tbody>tr>td,
    .table>tfoot>tr>td {
        text-wrap: auto !important;
    }
</style>

<?php
if (!is_postSale() && !is_admin()) {
?>
    <h2 class="text-center">Fly Ticket - Accessible Only for Post-Sale & Admin</h2>
<?php
    die;
}

$table_data = array(
    _l('Country Name'),
    _l('university_name'),
    _l('Vendor Name'),
    _l('Cost'),
    _l('Payment Date'),
    _l('Payment Mode'),
    _l('Fly Date'),
    _l('Departure'),
    "Action",
);
?>
<div class="row">
    <div class="col-md-12">
        <div class="form-container">
            <h4 class="fs-title">Fly Ticket</h4>
            <div class="text-right">
                <?php if ($client_infomation->orignal_document_status == 3) { ?>
                    <!-- <?= getLastEmailWhatsappDate("whatsapp", 6, $client_id) ?><button type="button" class="btn btn-primary btn-xs " onclick="whatsapp_message_send(<?= !empty($client_id) ? $client_id : '' ?>, 6,'','')"><i class="fa fa-whatsapp"></i> </button> -->
                <?php } ?>
                <div class="row">
                    <a href="#" onclick="edit_ticket(); return false;" class="btn mright5 btn-info pull-right display-block">New Fly Ticket</a>
                </div>
            </div>
            <hr>

            <?php
            render_input("client_id", "", $client_id, "hidden");
            render_datatable($table_data, 'fly-batch');
            ?>
            <br>
        </div>
    </div>
</div>

<div class="modal fade" id="ticketModal" tabindex="-1" aria-labelledby="ticketLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">

            <!-- Modal Header -->
            <div class="modal-header">
                <h4 class="modal-title fs-15" id="ticketLabel">Fly Ticket Create</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="modal-body" id="ticketCreateBody">
                <form class="row" id="ticket-form" onsubmit="return false;">
                    <?= render_input('ticket_id', '', '', 'hidden'); ?>
                    <div class="form-group col-md-4">
                        <?= render_select('vendor_name', $vendor_list, ['id', 'name'], 'Vendor Name', []); ?>
                    </div>

                    <div class="form-group col-md-4">
                        <?= render_input('ticket_cost', 'Ticket Cost', '', 'number'); ?>
                    </div>

                    <div class="form-group col-md-4">
                        <?= render_input('payment_date', 'Payment Date', '', 'date'); ?>
                    </div>

                    <div class="form-group col-md-4">
                        <?= render_select('payment_mode', $payment_mode, ['id', 'name'], 'Payment Mode', []); ?>
                    </div>

                    <div class="form-group col-md-4">
                        <?= render_input('fly_date', 'Fly Date', '', 'datetime-local'); ?>
                    </div>

                    <div class="form-group col-md-4">
                        <?= render_select('departure_location', $departure_location, ['id', 'name'], 'Departure Location', []); ?>
                    </div>
                </form>
            </div>

            <!-- Modal Footer -->
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
                <button type="submit" form="ticket-form" onclick="create_fly_ticket()" class="btn btn-info">Confirm</button>
            </div>

        </div>
    </div>
</div>


<?php init_tail(); ?>
<script>
    var client_id = <?= $client_id ?>;
    var exam_data_table = "";
    var tAPI = ""
    $(function() {

        // Ensure you get the client_id value dynamically

        tAPI = initDataTable('.table-fly-batch', admin_url + 'fly_batch/index/' + client_id);

    });

    function set_modal(edit_data = "") {
        return new Promise((resolve, reject) => {
            try {
                if (!edit_data) {
                    resolve("No data provided for modal.");
                    return;
                }

                let decodedData;

                try {
                    decodedData = JSON.parse(atob(edit_data));
                } catch (parseError) {
                    throw new Error("Invalid base64 or JSON format.");
                }

                if (!decodedData || typeof decodedData !== "object") {
                    throw new Error("Decoded data is not valid.");
                }

                // Populate modal fields
                $("#ticket_cost").val(decodedData.cost);
                $("#ticket_id").val(decodedData.data_id);
                $("#payment_date").val(decodedData.payment_date);
                $("#payment_mode").val(decodedData.payment_mode_id).trigger("change");
                $("#fly_date").val(decodedData.fly_date);
                $("#vendor_name").val(decodedData.vendor_id).trigger("change");
                $("#departure_location").val(decodedData.departure_location_id).trigger("change");

                resolve("Modal data set successfully.");
            } catch (error) {
                console.error("Failed to set modal data:", error);
                reject("Failed to load ticket data: " + error.message);
            }
        });
    }


    function edit_ticket(id = "", data) {
        $("#ticket-form")[0].reset();
        $("#ticket-form input").val('');
        $("#ticket-form select").val('').trigger("change");

        set_modal(data)
            .then((msg) => {
                console.log(msg);
                $("#ticketModal").modal("show");
            })
            .catch((err) => {
                alert_float("danger", err);
            });
    }



    function create_fly_ticket() {

        let ticket_cost = $("#ticket_cost").val();
        let payment_date = $("#payment_date").val();
        let payment_mode = $("#payment_mode").val();
        let fly_date = $("#fly_date").val();
        let vendor_name = $("#vendor_name").val();
        let departure_location = $("#departure_location").val();
        let id = $("#ticket_id").val();


        // Get selected client IDs (you must set this dynamically from your selection logic)
        let client_selected_list = client_id ? [client_id] : [];

        // Basic validations
        if (!vendor_name) {
            alert_float("danger", "Please fill all required fields.");
            return;
        }

        // Prepare form data
        let formData = new FormData();
        formData.append("csrf_token_name", csrfData.hash);
        formData.append("ticket_cost", ticket_cost);
        formData.append("payment_date", payment_date);
        formData.append("payment_mode", payment_mode);
        formData.append("fly_date", fly_date);
        formData.append("vendor_name", vendor_name);
        formData.append("manually", 1);
        formData.append("departure_location", departure_location);
        formData.append("id", id);

        // Append selected client list
        formData.append("client_list", JSON.stringify(client_selected_list));

        // Send AJAX request
        $.ajax({
            url: "<?= base_url('admin/fly_batch/create_batch'); ?>",
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            dataType: "JSON",
            success: function(res) {
                if (res.resp_code === "RCS") {
                    alert_float("success", res.resp_desc);
                    if (typeof tAPI !== "undefined") tAPI.ajax.reload();
                    $("#ticketModal").modal("hide");
                } else {
                    alert_float("danger", res.resp_desc || "An unknown error occurred.");
                }
            },
            error: function(xhr, status, error) {
                console.error("Error: ", error);
                alert_float("danger", "An error occurred while processing the request.");
            },
        });
    }
</script>