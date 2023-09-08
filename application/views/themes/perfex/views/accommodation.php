<style>
    /* Add your custom CSS here */
    body {
        background-color: #f8f9fa;
    }

    .form-container {
        background-color: #ffffff;
        border: 1px solid #ccc;
        padding: 20px;
        margin-top: 20px;
        border-radius: 5px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    }

    .form-container .mt-5 {
        margin-top: 10px;
    }

    .form-container .datepicker {
        width: 100%;
    }

    .form-container .form-control {
        width: 100%;
    }

    .form-container .selectpicker {
        width: 100%;
    }

    .document-file-name {
        overflow: hidden;
    }
</style>
<?php
$selected_vendor = !empty($accommodation_data[0]["vendor_id"]) ? $accommodation_data[0]["vendor_id"] : [];
$country_name = "";
$university_name = "";
if (!empty($selected_university_country->university_name) && !empty($selected_university_country->university)) {
    $university_name = $selected_university_country->university_name;
    $country_array = json_decode($selected_university_country->university, true);
    foreach ($country_array as $key => $con) {
        if (!empty($con)) {
            $university_array = explode(",", $con);
            if (!empty($university_array)) {
                foreach ($university_array as $u) {
                    if (strtolower($university_name) == strtolower($u)) {
                        $country_name = $key;
                    }
                }
            }
        }
    }
}
if (empty($country_name)) {
?>
    <h3>Application Tracker Process not completed.</h3>
<?php
    die;
}
?>

<h2 class="text-center">Accommodation & Flight</h2>
<div class="row">
    <div class="col-md-12">
        <div class="form-container">
            <form id="accommodation_form" onsubmit="return false" method="post" enctype="multipart/form-data">
                <!-- <label for="vendor-select">Select Vendor</label> -->
                <!-- <select id="vendor-select" name="vendor_id" class="selectpicker form-control">
                    <option value="">Select Vendor</option>
                  
                </select> -->

                <?php
                // array_unshift($vendor, array("id" => "", "vendor" => "Select Vendor"));
                ?>
                <!-- <?php echo render_select('vendor_id', $vendor, array('id', array('vendor')), '', $selected_vendor, array('data-width' => '100%', 'data-none-selected-text' => _l('Vendor'), '', 'data-actions-box' => true), array(), 'no-mbot', '', false, 'vendor_id'); ?> -->

                <div class="mt-5">
                    <label for="address">Place & Address</label>
                    <textarea id="address" name="address" placeholder="Address" class="form-control"> <?= !empty($accommodation_data[0]["address"]) ? $accommodation_data[0]["address"] : '' ?></textarea>
                </div>

                <div class="row mt-5">
                    <div class="col-md-4">
                        <label for="contract-file">Contract</label>
                        <input type="file" name="contract" id="contract-file" class="form-control">
                        <?php if (!empty($accommodation_data[0]["contract"])) { ?>
                            <div class="row media-text-div">
                                <div class="col-md-10">
                                    <p class="document-file-name"><?= !empty($accommodation_data[0]["contract"]) ? $accommodation_data[0]["contract"] : '' ?></p>
                                </div>
                                <div class="col-md-2 file-download-block">
                                    <a class="col-md-12 download_document" accept="image/*,application/pdf" onclick='window.open("<?= !empty($accommodation_data[0]["contract"]) ? base_url($accommodation_data[0]["contract"]) : "0" ?>", "_blank");' href="javascript:void(0);" type="button">
                                        <i class="fa fa-download" aria-hidden="true"></i>
                                    </a>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                    <div class="col-md-4">
                        <label for="start-date">Select Start Date</label>
                        <input type="text" id="start-date" name="start_date" value="<?= !empty($accommodation_data[0]["start_date"]) ? $accommodation_data[0]["start_date"] : '' ?>" placeholder="Select Start Date" class="datepicker form-control">
                    </div>
                    <div class="col-md-4">
                        <label for="end-date">Select End Date</label>
                        <input type="text" id="end-date" name="end_date" value="<?= !empty($accommodation_data[0]["end_date"]) ? $accommodation_data[0]["end_date"] : '' ?>" placeholder="Select End Date" class="datepicker form-control">
                    </div>
                </div>

                <div class="mt-5">
                    <label for="payment-receipt">Payment (Receipt)</label>
                    <input type="file" id="payment-receipt" name="payment" class="form-control">

                    <?php if (!empty($accommodation_data[0]["payment"])) { ?>
                        <div class="row media-text-div">
                            <div class="col-md-10">
                                <p class="document-file-name"><?= !empty($accommodation_data[0]["payment"]) ? $accommodation_data[0]["payment"] : '' ?></p>
                            </div>
                            <div class="col-md-2 file-download-block">
                                <a class="col-md-12 download_document" accept="image/*,application/pdf" onclick='window.open("<?= !empty($accommodation_data[0]["payment"]) ? base_url($accommodation_data[0]["payment"]) : "0" ?>", "_blank");' href="javascript:void(0);" type="button">
                                    <i class="fa fa-download" aria-hidden="true"></i>
                                </a>
                            </div>
                        </div>
                    <?php } ?>
                </div>

                <h4 class="fs-title mt-5">Contact Person Details</h4>
                <hr>

                <div class="row mt-5">
                    <div class="col-md-4">
                        <label for="contact-name">Contact Name</label>
                        <input type="text" id="contact-name" name="contact_name" value="<?= !empty($accommodation_data[0]["contact_name"]) ? $accommodation_data[0]["contact_name"] : '' ?>" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label for="contact-email">Contact Email</label>
                        <input type="email" id="contact-email" name="contact_email" value="<?= !empty($accommodation_data[0]["contact_email"]) ? $accommodation_data[0]["contact_email"] : '' ?>" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label for="contact-phone">Contact Phone</label>
                        <input type="number" id="contact-phone" name="contact_phone" value="<?= !empty($accommodation_data[0]["contact_phone"]) ? $accommodation_data[0]["contact_phone"] : '' ?>" class="form-control">
                    </div>
                </div>
                <!-- <div class="row">
                    <div class="button-save-documents mt-5">
                        <div class="col-md-8"></div>
                        <div class="col-md-4">
                            <button class="btn btn-primary pull-right" type="submit">Accommodation Update</button>
                        </div>
                    </div>
                </div> -->
            </form>
            <hr>
            <h4 class="fs-title mt-5">Flight Details</h4>
            <hr>
            <form id="flight_form" onsubmit="return false" method="post" enctype="multipart/form-data">
                <div class="row mt-5">
                    <div class="col-md-4">
                        <label for="flight-name">Flight Name</label>
                        <input type="text" name="flight_name" value="<?= !empty($flight_data[0]['flight_name']) ? $flight_data[0]['flight_name'] : '' ?>" id="flight-name" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label for="flight-date-time">Flight Date & Time</label>
                        <input type="datetime-local" name="flight_date" value="<?= !empty($flight_data[0]['flight_date']) ? $flight_data[0]['flight_date'] : '' ?>" id="flight-date-time" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label for="flight-ticket">Flight Ticket</label>
                        <input type="file" name="flight_ticket" id="flight-ticket" class="form-control">


                        <?php if (!empty($flight_data[0]["flight_ticket"])) { ?>
                            <div class="row media-text-div">
                                <div class="col-md-10">
                                    <p class="document-file-name"><?= !empty($flight_data[0]["flight_ticket"]) ? $flight_data[0]["flight_ticket"] : '' ?></p>
                                </div>
                                <div class="col-md-2 file-download-block">
                                    <a class="col-md-12 download_document" accept="image/*,application/pdf" onclick='window.open("<?= !empty($flight_data[0]["flight_ticket"]) ? base_url($flight_data[0]["flight_ticket"]) : "0" ?>", "_blank");' href="javascript:void(0);" type="button">
                                        <i class="fa fa-download" aria-hidden="true"></i>
                                    </a>
                                </div>
                            </div>
                        <?php } ?>

                    </div>
                </div>
                <!-- <div class="row">
                    <div class="button-save-documents mt-5">
                        <div class="col-md-8"></div>
                        <div class="col-md-4">
                            <button class="btn btn-primary pull-right" type="submit">Flight Update</button>
                        </div>
                    </div>
                </div> -->
            </form>
        </div>
    </div>
</div>

<script>
    var client_id = "<?= $client_id ?>";
    $(document).ready(function(){
    $(".loading-upper").hide();
})

                    function show_loader(id = "") {
                        if (id != '') {
                            $("#" + id).attr("data-loading-text", "<i class='fa fa-spinner fa-spin '></i> Processing ");
                        }
                        $(".loading-upper").show();

                    }

                    function hide_loader(id = "") {
                        if (id != '') {
                            $("#" + id).button('reset');
                        }
                        $(".loading-upper").hide();

                    }

    $("input,textarea,select").attr("disabled", true);

    var csrfToken = "<?= $this->security->get_csrf_hash() ?>";

    document.getElementById('accommodation_form').addEventListener('submit', async function(e) {
        e.preventDefault(); // Prevent the default form submission.

        const formData = new FormData(this); // Create a FormData object from the entire form.
        formData.append("client_id", client_id);
        formData.append("<?= $this->security->get_csrf_token_name(); ?>", csrfToken);
        let response = await $.ajax({
            url: "<?= base_url("admin/clients/update_accommodation") ?>",
            method: "POST",
            data: formData,
            contentType: false,
            processData: false
        });

        uploadResponse = JSON.parse(response);

        if (uploadResponse.resp_code == "RCS") {
            hide_loader();
            alert_float("success", uploadResponse.resp_desc);
            location.reload();
        } else {
            if (uploadResponse.resp_code != undefined) {
                alert_float("danger", uploadResponse.resp_desc);
                hide_loader();
                return false;
            } else {
                alert_float("danger", uploadResponse);
                hide_loader();
                return false;
            }
        }
    });

    document.getElementById('flight_form').addEventListener('submit', async function(e) {
        e.preventDefault(); // Prevent the default form submission.

        const formData = new FormData(this); // Create a FormData object from the entire form.
        formData.append("client_id", client_id);
        formData.append("<?= $this->security->get_csrf_token_name(); ?>", csrfToken);
        let response = await $.ajax({
            url: "<?= base_url("admin/clients/update_flight") ?>",
            method: "POST",
            data: formData,
            contentType: false,
            processData: false
        });

        uploadResponse = JSON.parse(response);

        if (uploadResponse.resp_code == "RCS") {
            hide_loader();
            alert_float("success", uploadResponse.resp_desc);
            location.reload();
        } else {
            if (uploadResponse.resp_code != undefined) {
                alert_float("danger", uploadResponse.resp_desc);
                hide_loader();
                return false;
            } else {
                alert_float("danger", uploadResponse);
                hide_loader();
                return false;
            }
        }
    });
</script>