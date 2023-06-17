<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$applicant_tracker = applicant_tracker();
$applicant_status = !empty($client->applicant_status) ? $client->applicant_status : 0;
$profile_creation_data = !empty($profile_creation_data) ? $profile_creation_data : "";
?>
<style>
    /*basic reset*/
    * {
        margin: 0;
        padding: 0;
    }

    html {
        height: 100%;
        background: #eee;
    }

    body {
        font-family: Montserrat, arial, verdana;
        background: transparent;
    }

    /*form styles*/
    #msform {
        text-align: center;
        position: relative;
        margin-top: 30px;
    }

    #msform fieldset {
        background: white;
        border: 0 none;
        border-radius: 8px;
        box-shadow: 0 0 15px 1px rgba(0, 0, 0, 0.4);
        padding: 20px 30px;
        box-sizing: border-box;
        width: 100%;
        /* margin: 0 10%; */

        /*stacking fieldsets above each other*/
        position: relative;
    }

    /*Hide all except first fieldset*/
    /* #msform fieldset:not(:first-of-type) {
        display: none;
    } */

    /*inputs*/
    #msform input,
    #msform textarea {
        /* padding: 15px;
        border: 1px solid #ccc;
        border-radius: 4px;
        margin-bottom: 10px;
        width: 100%;
        box-sizing: border-box;
        font-family: montserrat;
        color: #2C3E50;
        font-size: 13px; */
    }

    #msform input:focus,
    #msform textarea:focus {
        -moz-box-shadow: none !important;
        -webkit-box-shadow: none !important;
        box-shadow: none !important;
        border: 1px solid #2098ce;
        outline-width: 0;
        transition: All 0.5s ease-in;
        -webkit-transition: All 0.5s ease-in;
        -moz-transition: All 0.5s ease-in;
        -o-transition: All 0.5s ease-in;
    }

    /*buttons*/
    #msform .action-button {
        width: 100px;
        background: #2098ce;
        font-weight: bold;
        color: white;
        border: 0 none;
        border-radius: 25px;
        cursor: pointer;
        padding: 10px 5px;
        margin: 10px 5px;
    }

    #msform .action-button:hover,
    #msform .action-button:focus {
        box-shadow: 0 0 0 2px white, 0 0 0 3px #2098ce;
    }

    #msform .action-button-previous {
        width: 100px;
        background: #aCbEd0;
        font-weight: bold;
        color: white;
        border: 0 none;
        border-radius: 25px;
        cursor: pointer;
        padding: 10px 5px;
        margin: 10px 5px;
    }

    #msform .action-button-previous:hover,
    #msform .action-button-previous:focus {
        box-shadow: 0 0 0 2px white, 0 0 0 3px #aCbEd0;
    }

    /*headings*/
    .fs-title {
        font-size: 18px;
        text-transform: uppercase;
        color: #2C3E50;
        margin-bottom: 10px;
        letter-spacing: 2px;
        font-weight: bold;
    }

    .fs-subtitle {
        font-weight: normal;
        font-size: 13px;
        color: #666;
        margin-bottom: 20px;
    }

    /*progressbar*/
    #progressbar {
        margin-bottom: 30px;
        overflow: hidden;
        /*CSS counters to number the steps*/
        counter-reset: step;
    }

    #progressbar li {
        list-style-type: none;
        color: #666;
        text-transform: uppercase;
        font-size: 9px;
        /* width: 33.33%; */
        float: left;
        position: relative;
        letter-spacing: 1px;
    }

    #progressbar li:before {
        content: counter(step);
        counter-increment: step;
        width: 24px;
        height: 24px;
        line-height: 26px;
        /* width: 35px;
        height: 35px;
        line-height: 35px; */
        display: block;
        font-size: 12px;
        color: #333;
        background: white;
        border-radius: 25px;
        margin: 0 auto 10px auto;
    }

    /*progressbar connectors*/
    #progressbar li:after {
        content: '';
        width: 100%;
        height: 2px;
        background: white;
        position: absolute;
        left: -50%;
        top: 9px;
        z-index: -1;
        /*put it behind the numbers*/
    }

    #progressbar li:first-child:after {
        /*connector not needed before the first step*/
        content: none;
    }

    /*marking active/completed steps blue*/
    /*The number of the step and the connector before it = blue*/
    #progressbar li.active:before,
    #progressbar li.active:after {
        background: #2098ce;
        color: white;
    }

    #progressbar {
        text-align: center;
    }

    #progressbar li {
        display: block;
        width: 20%;
        text-align: center;
    }

    #progressbar li {
        display: inline-block;
        text-align: center;
        color: grey;
    }

    #progressbar li.active {
        color: orange;
    }

    #progressbar li.active:before {
        background-color: orange;
    }

    #progressbar li.previous {
        color: green;
    }

    #progressbar li.previous:before {
        background-color: green;
    }

    #progressbar li.inactive:before {
        background-color: #E7ECF1;
    }

    #progressbar li.inactive:before {
        color: #666;
    }

    .add_document,
    .download_document {
        padding: 7px;
        width: 33px;
        /* line-height: 33px; */
    }

    #profile_div .download_document {
        line-height: 33px;
    }

    #profile_creation_div .fa-pencil-square-o,
    #profile_creation_div .fa-file {
        line-height: 40px;
    }


    .document_upload_files {
        margin: 15px 0px;
    }

    .message-notification {
        background: orange;
        padding: 25px;
        font-size: 15px;
        color: white;
        border-radius: 7px;
        box-shadow: 0px 1px 5px 1px grey;
        margin-bottom: 20px;
    }

    .message-notification.success {
        background: #84c529 !important;
    }

    .message-notification.danger {
        background: #dc3545 !important;
    }

    i.fa {
        cursor: pointer;
    }

    #profile_div i.fa {
        font-size: 18px;

    }

    .edit_save_block {
        position: absolute;
        right: 4%;
        line-height: 55px;
    }

    .next.action-button:disabled {
        background: #aCbEd0 !important;
        cursor: not-allowed !important;
        box-shadow: unset !important;
    }

    #progressbar li.previous:before {
        background-color: green;
        color: white;
    }

    .application_div div.university_div_application {
        margin-top: 10px !important;
    }
</style>
<!-- MultiStep Form -->
<div class="row">
    <div class="col-md-12 ">
        <form id="msform">
            <!-- progressbar -->
            <ul id="progressbar" class="d-flex justify-content-center">
                <!-- <li class=" active">Document</li>
                <li class="">Profile Creation</li>
                <li class="">University Shortlisting</li>
                <li class="">Application</li>
                <li class="">Offer Letter</li> -->
                <?php
                foreach ($applicant_tracker as $key => $track) {
                ?>
                    <li class="<?php if ($key == $applicant_status) {
                                    echo $result = "active";
                                } elseif ($key < $applicant_status) {
                                    echo $result = "previous";
                                } else {
                                    echo $result = "inactive";
                                } ?>" data-show="<?= !empty($track["show_div_name"]) ? $track["show_div_name"] : '' ?>"><?= $track["name"] ?></li>
                <?php
                }
                ?>
            </ul>
            <!-- fieldsets -->
            <div class="document_approval_message_action">
            </div>
            <div class="profile_approval_message_action">
            </div>
            <?php
            foreach ($applicant_tracker as $k => $track) {
            ?>
                <fieldset id="<?= !empty($track["show_div_name"]) ? $track["show_div_name"] : '12' ?>" style="display:<?= ($applicant_status == $k) ? "show" : "none" ?>">
                    <h2 class="fs-title" style="margin-bottom: 20px!important;"><?= !empty($track["name"]) ? $track["name"] : 'Document' ?></h2>
                    <?php if ($track["show_div_name"] == "document_div") { ?>
                        <div id="upload_documents">
                            <?php
                            $doc_data_array = [];
                            if (!empty($upload_documents[0]["data"])) {
                                $doc_data_array = json_decode($upload_documents[0]["data"], true);
                            }
                            if (!empty($upload_documents) && !empty($doc_data_array)) {
                                foreach ($doc_data_array as $d_key => $docs) {
                            ?>

                                    <div class="row col-md-12 document_upload_files ">
                                        <div class="col-md-5"><input class="col-md-5 form-control" name="document_label[]" type="input" placeholder="Enter label Name" value="<?= $docs["label_name"] ?>"></div>
                                        <div class="col-md-5"><input class="col-md-5 form-control" type="file" data-url="<?= $docs["document_file"] ?>" name="document_file[]" placeholder=""></div>

                                        <?php if ($d_key == 0) { ?>
                                            <div class="col-md-2">
                                                <a class="col-md-2 download_document" download href="<?= base_url($docs["document_file"]) ?>" type="button"><i class="fa fa-download" aria-hidden="true"></i></a>
                                                <button class="col-md-2 add_document" type="button" onclick="add_documents()"><i class="fa fa-plus" aria-hidden="true"></i></button>
                                            </div>
                                        <?php } else { ?>
                                            <div class="col-md-2">
                                                <a class="col-md-2 download_document" href="<?= base_url($docs["document_file"]) ?>" download type="button"><i class="fa fa-download" aria-hidden="true"></i></a>
                                                <button class="col-md-2 add_document" type="button" onclick="remove_document(this)"><i class="fa fa-trash text-danger" aria-hidden="true"></i></button>
                                            </div>
                                        <?php } ?>

                                    </div>
                                <?php
                                }
                            } else { ?>
                                <div id="upload_documents">
                                    <div class="row col-md-12 document_upload_files ">
                                        <div class="col-md-5"><input class="col-md-5 form-control" name="document_label[]" type="input" placeholder="Enter label Name"></div>
                                        <div class="col-md-5"><input class="col-md-5 form-control" type="file" name="document_file[]" placeholder=""></div>
                                        <div class="col-md-2">
                                            <a class="col-md-2 download_document" download style="display:none;" type="button"><i class="fa fa-download" aria-hidden="true"></i></a>
                                            <button class="col-md-2 add_document" type="button" onclick="add_documents()"><i class="fa fa-plus" aria-hidden="true"></i></button>
                                        </div>
                                    </div>
                                </div>
                            <?php }
                            ?>
                        </div>
                    <?php
                    } else if ($track["show_div_name"] == "profile_div") { ?>
                        <div id="profile_creation_div">

                            <div class="row">
                                <div class="col-md-6"></div>
                                <div class="col-md-4">
                                    <?php echo render_input('email_creation', "", !empty($profile_creation_data[0]["email"]) ? $profile_creation_data[0]["email"] : '', "Email", ["required" => "required", "placeholder" => "Enter Email"]); ?>
                                </div>
                                <div class="col-md-2 edit_save_block">
                                    <i class="fa fa-pencil-square-o col-md-1" style="display:none;" onclick="edit_data(this,1)"></i>
                                    <i class="fa fa-file col-md-1" style="display:none;" onclick="save_data(this,'email')"></i>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6"></div>
                                <div class="col-md-4">
                                    <?php
                                    $selected_vendor = !empty($profile_creation_data[0]["vendor"]) ? explode(",", $profile_creation_data[0]["vendor"]) : [];
                                    echo render_select('profile_creator_vendor[]', $profile_creator_vendor, array('id', 'name'), '', $selected_vendor, array('multiple' => true), array(), '', '', false, "select_vendor"); ?>
                                </div>
                                <div class="col-md-2 edit_save_block">
                                    <i class="fa fa-pencil-square-o col-md-1" style="display:none;" onclick="edit_data(this,1)"></i>
                                    <i class="fa fa-file col-md-1" style="display:none;" onclick="save_data(this,'vendor')"></i>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6"></div>
                                <div class="col-md-4">
                                    <input type="file" id="sop_document" data-url="<?= !empty($profile_creation_data[0]["sop"]) ? $profile_creation_data[0]["sop"] : '' ?>" name="sop_document" class="form-control" accept=".pdf,.doc,.docx">

                                </div>
                                <div class="col-md-2 edit_save_block">
                                    <?php if (!empty($profile_creation_data[0]["sop"])) { ?>
                                        <a class="col-md-1 download_document" download href="<?= base_url($profile_creation_data[0]["sop"]) ?>" style="" type="button"><i class="fa fa-download" aria-hidden="true"></i></a>
                                    <?php } ?>

                                    <i class="fa fa-pencil-square-o col-md-1" style="display:none;" onclick="edit_data(this,1)"></i>
                                    <i class="fa fa-file col-md-1" style="display:none;" onclick="save_data(this,'sop')"></i>
                                </div>
                            </div>
                        </div>
                    <?php } else if ($track["show_div_name"] == "university_div") {
                        $selected_university = json_decode($admissionpreferences->university, true);
                        $university_drop_down = [];
                        foreach ($selected_university as $key => $university) {
                            if (!empty($university)) {
                                $university_drop_down[$key] = explode(",", $university);
                            }
                        }
                    ?>

                        <div class="add_university_div_block">
                            <?php if (!empty($university_shortlisting)) {
                                foreach ($university_shortlisting as $key_u => $short_list) {
                            ?>
                                    <div class="col-md-12 university_div">
                                        <div class="col-md-5">
                                            <input type="hidden" name="university_id" value="<?= $short_list["id"] ?>">
                                            <select class="selectpicker from-control" data-width="100%" name="select_university" id="select_university" data-live-search="true">
                                                <option value="">Select University</option>
                                                <?php
                                                if (!empty($university_drop_down)) {
                                                    foreach ($university_drop_down as $country_name => $university_list) {
                                                        if (!empty($university_list)) {
                                                ?>
                                                            <optgroup label="<?= $country_name ?>" id="<?= $country_name ?>">
                                                                <?php
                                                                foreach ($university_list as $university_name) {
                                                                    $selected_university = strtolower(trim($university_name)) == strtolower(trim($short_list["university_name"])) ? "selected" : '';
                                                                    if (!empty($university_name)) {
                                                                ?>
                                                                        <option <?= $selected_university ?> value='<?= $university_name ?>'><?= $university_name ?></option>
                                                                <?php
                                                                    }
                                                                }
                                                                ?>
                                                            </optgroup>
                                                <?php
                                                        }
                                                    }
                                                }
                                                ?>
                                            </select>
                                        </div>
                                        <div class="col-md-5">
                                            <?php
                                            // echo render_select('select_university_vendor', $customer_vendors, array('id', 'name'), '', "", "", array(), '', '', "", "select_university_vendor");
                                            $selected_vendor = !empty($short_list["vendor_id"]) ? $short_list["vendor_id"] : "";
                                            echo render_select('select_university_vendor', $customer_vendors, array('id', 'name'), "", $selected_vendor);
                                            ?>
                                        </div>

                                        <div class="col-md-2">
                                            <!-- <button class="col-md-2 add_document" type="button" onclick="remove_university_div(this)"><i class="fa fa-trash text-danger" aria-hidden="true"></i></button> -->
                                            <?php if ($key_u == 0) { ?>
                                                <button class="col-md-2 add_document" type="button" onclick="add_university_div()"><i class="fa fa-plus" aria-hidden="true"></i></button>
                                            <?php } else { ?>
                                                <button class="col-md-2 add_document" type="button" onclick="remove_university_div(this)"><i class="fa fa-trash text-danger" aria-hidden="true"></i></button>
                                            <?php } ?>
                                        </div>
                                    </div>
                                <?php }
                                ?>

                            <?php } else { ?>
                                <div class="col-md-12 university_div">
                                    <div class="col-md-5">
                                        <input type="hidden" name="university_id">
                                        <select class="selectpicker from-control" data-width="100%" name="select_university" id="select_university" data-live-search="true">
                                            <option value="">Select University</option>
                                            <?php
                                            if (!empty($university_drop_down)) {
                                                foreach ($university_drop_down as $country_name => $university_list) {
                                                    if (!empty($university_list)) {
                                            ?>
                                                        <optgroup label="<?= $country_name ?>" id="<?= $country_name ?>">
                                                            <?php
                                                            foreach ($university_list as $university_name) {
                                                                if (!empty($university_name)) {
                                                            ?>
                                                                    <option value='<?= $university_name ?>'><?= $university_name ?></option>
                                                            <?php
                                                                }
                                                            }
                                                            ?>
                                                        </optgroup>
                                            <?php
                                                    }
                                                }
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="col-md-5">
                                        <?php
                                        // echo render_select('select_university_vendor', $customer_vendors, array('id', 'name'), '', "", "", array(), '', '', "", "select_university_vendor");

                                        echo render_select('select_university_vendor', $customer_vendors, array('id', 'name'));
                                        ?>
                                    </div>

                                    <div class="col-md-2">
                                        <!-- <button class="col-md-2 add_document" type="button" onclick="remove_university_div(this)"><i class="fa fa-trash text-danger" aria-hidden="true"></i></button> -->
                                        <button class="col-md-2 add_document" type="button" onclick="add_university_div()"><i class="fa fa-plus" aria-hidden="true"></i></button>
                                    </div>
                                </div>
                            <?php } ?>
                        </div>
                    <?php } else if ($track["show_div_name"] == "application_div") {
                        $selected_university = json_decode($admissionpreferences->university, true);
                        $university_drop_down = [];
                        foreach ($selected_university as $key => $university) {
                            if (!empty($university)) {
                                $university_drop_down[$key] = explode(",", $university);
                            }
                        }
                    ?>

                        <div class="application_div">
                            <?php if (!empty($university_shortlisting)) {
                                $select_dropdown_value = array_column($customer_vendors, "name", "id");
                                foreach ($university_shortlisting as $key_u => $short_list) {
                            ?>
                                    <div class="col-md-12 university_div_application mt-2">
                                        <div class="col-md-3">
                                            <input type="hidden" name="university_id" value="<?= $short_list["id"] ?>">
                                            <input type="input" class="form-control" disabled value="<?= $short_list["university_name"] ?>">
                                        </div>
                                        <div class="col-md-3">
                                            <?php
                                            // echo render_select('select_university_vendor', $customer_vendors, array('id', 'name'), '', "", "", array(), '', '', "", "select_university_vendor");
                                            $selected_vendor = !empty($short_list["vendor_id"]) ? $short_list["vendor_id"] : "";
                                            // echo render_select('select_university_vendor', $customer_vendors, array('id', 'name'), "", $selected_vendor);
                                            // echo $select_dropdown_value[$selected_vendor];

                                            ?>
                                            <input type="input" class="form-control" disabled value="<?= $select_dropdown_value[$selected_vendor] ?>">
                                        </div>

                                        <div class="col-md-4">
                                            <?php

                                            $selected_university_application = !empty($short_list["application_status"]) ? $short_list["application_status"] : "";
                                            echo render_select('university_application_status', $university_application_status, array('id', 'name'), "", $selected_university_application); ?>
                                        </div>
                                        <div class="col-md-2 university_div_status">

                                            <i class="fa fa-pencil-square-o col-md-1" style="display:none;" onclick="edit_data(this,1)"></i>
                                            <i class="fa fa-file col-md-1" style="display:none;" onclick="save_data(this,'sop')"></i>
                                        </div>
                                    </div>
                                <?php }
                                ?>

                            <?php } ?>
                        </div>
                    <?php } ?>
                    <?php if ($k > 0) { ?>
                        <input type="button" name="previous" class="previous action-button-previous" value="Previous" />
                    <?php } ?>
                    <?php if (($k + 1) < count($applicant_tracker)) { ?>
                        <input type="button" name="next" class="next action-button" onclick="next_step('<?= $track['show_div_name'] ?>',this,<?= $track['orderby'] ?>)" value="Next" />
                    <?php } ?>

                </fieldset>
            <?php
            }
            ?>
            <!-- 
            <fieldset>
                <h2 class="fs-title" style="margin-bottom: 20px!important;">Document Details</h2>
                <input type="text" name="fname" placeholder="First Name" />
                <input type="text" name="lname" placeholder="Last Name" />
                <input type="text" name="phone" placeholder="Phone" />
                <input type="button" name="next" class="next action-button" value="Next" />
            </fieldset>
            <fieldset>
                <h2 class="fs-title" style="margin-bottom: 20px!important;">Profile Creation</h2>
                <input type="text" name="twitter" placeholder="Twitter" />
                <input type="text" name="facebook" placeholder="Facebook" />
                <input type="text" name="gplus" placeholder="Google Plus" />
                <input type="button" name="previous" class="previous action-button-previous" value="Previous" />
                <input type="button" name="next" class="next action-button" value="Next" />
            </fieldset>
            <fieldset>
                <h2 class="fs-title" style="margin-bottom: 20px!important;">University Shortlisting</h2>
                <input type="text" name="email" placeholder="Email" />
                <input type="password" name="pass" placeholder="Password" />
                <input type="password" name="cpass" placeholder="Confirm Password" />
                <input type="button" name="previous" class="previous action-button-previous" value="Previous" />
                <input type="submit" name="submit" class="submit action-button" value="Submit" />
            </fieldset> -->
        </form>
    </div>
</div>
<?php init_tail(); ?>
<!-- /.MultiStep Form -->
<script>
    //jQuery time
    var current_fs, next_fs, previous_fs; //fieldsets
    var left, opacity, scale; //fieldset properties which we will animate
    var animating; //flag to prevent quick multi-click glitches
    var client_id = <?= !empty($client_id) ? $client_id : '' ?>;
    var csrfToken = "<?= $this->security->get_csrf_hash() ?>"; // Replace with the actual CSRF token value
    var step_stage = 0;

    var customer_admins = <?= !empty($customer_admins) ? json_encode($customer_admins, true) : [] ?>;
    var upload_documents_button = <?= !empty($upload_documents_button) ? json_encode($upload_documents_button, true) : [] ?>;
    var upload_documents = <?= !empty($upload_documents[0]) ? json_encode($upload_documents[0], true) : [] ?>;
    var staff_id = "<?= get_staff_user_id() ?>";
    var profile_creation_data = <?= !empty($profile_creation_data[0]) ? json_encode($profile_creation_data[0], true) : [] ?>;
    var profile_verification_button = <?= !empty($profile_verification_button) ? json_encode($profile_verification_button, true) : [] ?>;
    var document_verification = "<?= !empty($upload_documents[0]["document_status"]) ? $upload_documents[0]["document_status"] : 0 ?>";
    var profile_verification = "<?= !empty($profile_creation_data[0]["profile_status"]) ? $profile_creation_data[0]["profile_status"] : 0 ?>";
    console.log(upload_documents);
    console.log(profile_creation_data);
    $("document").ready(function() {
        if (document_verification != 1) {
            $("#profile_creation_div").find('input, select').prop('disabled', true).selectpicker('refresh');;
            $("#profile_creation_div").find(".edit_save_block").hide();
        }
        if (profile_verification != 1) {
            $(".add_university_div_block .university_div").find('input, select').prop('disabled', true).selectpicker('refresh');
            $("#university_div").find(".add_document").hide();
            $("#university_div").find(".next.action-button").prop('disabled', true);
        }
    })


    function check_profile_status() {
        var check_disabled = false;
        var promises = []; // Array to hold the promises

        $("#profile_creation_div .row").each(function() {
            var hasValue = false;
            var row = $(this);



            row.find('input, select').each(function() {
                console.log($(this).val());
                if ($(this).val().length > 0) {
                    hasValue = true;
                    return false; // Exit the loop if a value is found
                }
            });

            row.find('input[type="file"][data-url]').each(function() {
                if ($(this).attr('data-url')) {
                    hasValue = true;
                    return false; // Exit the loop if a value is found
                }
            });

            var pencilIcon = row.closest('.row').find('.fa-pencil-square-o');
            var fileIcon = row.closest('.row').find('.fa-file');

            if (hasValue) {
                pencilIcon.show();
                fileIcon.hide();
                row.find('input, select').prop('disabled', true);
            } else {
                pencilIcon.hide();
                fileIcon.show();
                row.find('input, select').prop('disabled', false);
                check_disabled = true;
            }

            row.find('input, select').selectpicker('refresh');

            // Create a promise for each iteration and add it to the promises array
            var promise = new Promise(function(resolve) {
                resolve(); // Resolve the promise immediately
            });
            promises.push(promise);
        });

        // Use Promise.all to wait for all promises to resolve
        Promise.all(promises).then(function() {
            if (check_disabled) {
                $("#profile_div").find(".next").attr("disabled", true);
            } else {
                $("#profile_div").find(".next").attr("disabled", false);
            }
        });
    }


    check_profile_status();


    $(".previous").click(function() {


        current_fs = $(this).parent();
        previous_fs = $(this).parent().prev();


        //de-activate current step on progressbar
        $("#progressbar li").eq($("fieldset").index(current_fs)).removeClass("active").addClass("inactive");
        $("#progressbar li").eq($("fieldset").index(previous_fs)).addClass("active").removeClass("previous").removeClass("inactive");

        previous_fs.slideDown();
        current_fs.slideUp("slow");

    });

    $(".submit").click(function() {
        return false;
    })

    async function add_documents() {

        let response = await validate_document();
        if (response) {
            let html = `<div class="row col-md-12 document_upload_files">
                                <div class="col-md-5"><input class="col-md-5 form-control" name="document_label[]" type="input" placeholder="Enter label Name"></div>
                                <div class="col-md-5"><input class="col-md-5 form-control" type="file" name="document_file[]" placeholder=""></div>
                                                <a class="col-md-2 download_document" download href="" style="display:none;" type="button"><i class="fa fa-download" aria-hidden="true"></i></a>
                                <div class="col-md-1"><button class="col-md-2 add_document" type="button" onclick="remove_document(this)"><i class="fa fa-trash text-danger" aria-hidden="true"></i></button>
                                </div>
                    </div>`;
            $("#upload_documents").append(html);
        }

    }

    function remove_document(obj) {
        $(obj).parents(".document_upload_files").remove();
    }


    async function next_step(type, obj, step) {
        type = $.trim(type);
        step_stage = (step);
        show_loader();
        if (type === "document_div") {
            if (upload_documents.document_status != undefined && upload_documents.document_status == 1) {
                hide_loader();
            } else {
                let isDocumentValid = await validate_document();
                if (isDocumentValid) {
                    try {
                        let uploadResponse = await upload_document();
                        if (uploadResponse.resp_code == "RCS") {
                            alert_float("success", uploadResponse.resp_desc);
                            let html = '<h3 class="message-notification">Your Documents under Processing</h3>';
                            $(".document_approval_message_action").html(html);
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
                    } catch (error) {
                        hide_loader();
                        console.error(error);
                        return false;
                    }
                } else {
                    hide_loader();
                    return false;
                }
            }
        } else if (type === "profile_div") {

            if (profile_creation_data.profile_status != undefined && profile_creation_data.profile_status == 1) {
                hide_loader();
            } else {
                let isprofilevalid = await validate_profile_div();
                console.log(isprofilevalid);
                if (isprofilevalid) {
                    try {
                        let update_profile_status = await update_profile();
                        if (update_profile_status.resp_code == "RCS") {
                            alert_float("success", update_profile_status.resp_desc);
                        } else {
                            if (update_profile_status.resp_code != undefined) {
                                alert_float("danger", update_profile_status.resp_desc);
                                hide_loader();
                                return false;
                            } else {
                                alert_float("danger", update_profile_status);
                                hide_loader();
                                return false;
                            }
                        }
                    } catch (error) {
                        hide_loader();
                        console.error(error);
                        return false;
                    }
                }
            }
        } else if (type === "university_div") {
            let isUniversityValid = await validate_university_div();
            if (isUniversityValid) {
                try {
                    let update_university_status = await update_university();
                    if (update_university_status.resp_code == "RCS") {
                        let ids = update_university_status.ids;
                        console.log(ids);
                        $(".add_university_div_block .university_div").each(function(index) {
                            if (ids[index] != undefined) {
                                $(this).find("input[name='university_id']").val(ids[index]);
                            }
                        });
                        alert_float("success", update_university_status.resp_desc);
                    } else {
                        if (update_university_status.resp_code != undefined) {
                            alert_float("danger", update_university_status.resp_desc);
                            hide_loader();
                            return false;
                        } else {
                            alert_float("danger", update_university_status);
                            hide_loader();
                            return false;
                        }
                    }
                } catch (error) {
                    hide_loader();
                    console.error(error);
                    return false;
                }
            } else {
                hide_loader();
                return false;
            }
        }
        let current_fs = $(obj).parent();
        let next_fs = $(obj).parent().next();

        // Activate next step on progressbar using the index of next_fs
        $("#progressbar li").removeClass("active").addClass("inactive");
        $("#progressbar li.active").addClass("previous");
        $("#progressbar li").eq($("fieldset").index(next_fs)).addClass("active").removeClass("inactive").removeClass("previous");
        hide_loader();
        current_fs.slideUp("slow");
        next_fs.slideDown("slow");
    }

    function validate_profile_div() {
        return new Promise((resolve, reject) => {
            let email_creation = $("#email_creation").val();
            let select_vendor = $("#select_vendor").val();
            let sop_document = $("#sop_document").val();
            let sop_document_url = $("#sop_document").attr("data-url");

            if (email_creation == "") {
                alert_float("danger", "Email Creation is required");
                reject("Email Creation is required");
            } else if (select_vendor == "") {
                alert_float("danger", "Select Vendor is required");
                reject("Select Vendor is required");
            } else if (sop_document == "" && sop_document_url == "") {
                alert_float("danger", "SOP is required");
                reject("SOP is required");
            } else {
                resolve(true);
            }
        });
    }


    function update_profile_data() {
        return new Promise(async (resolve, reject) => {
            try {
                upload_data.append("<?= $this->security->get_csrf_token_name(); ?>", csrfToken);
                upload_data.append("applicant_status", step_stage);

                let response = await $.ajax({
                    url: "<?= base_url("admin/clients/update_profile_data") ?>",
                    method: "POST",
                    data: upload_data,
                    contentType: false,
                    processData: false
                });

                // Handle the success response from the server
                resolve(JSON.parse(response));
            } catch (error) {
                // Handle the error response from the server
                console.error(error);
                reject(error);
            }
        });
    }

    function validate_document() {
        return new Promise((resolve, reject) => {
            $(".document_upload_files").each(function() {
                let label_name = $(this).find("input[name='document_label[]']").val();
                let document = $(this).find("input[name='document_file[]']").prop("files")[0];
                let document_previous_url = $(this).find("input[name='document_file[]']").attr("data-url");

                if (label_name === undefined || $.trim(label_name) === "") {
                    $(this).find("input[name='document_label[]']").focus();
                    alert_float("danger", "Label is required");
                    resolve(false);
                    return;
                }

                if ((document === undefined || $.trim(document) === "") && (document_previous_url == undefined || document_previous_url == "")) {
                    $(this).find("input[name='document_file[]']").focus();
                    alert_float("danger", "Document file is required");
                    resolve(false);
                    return;
                }

                // Additional validation logic or processing can be added here
            });

            resolve(true); // Resolving the promise if all validations pass
        });
    }

    function upload_document() {
        return new Promise(async (resolve, reject) => {
            let upload_data = new FormData();

            $(".document_upload_files").each(function() {
                let label_name = $(this).find("input[name='document_label[]']").val();
                let document = $(this).find("input[name='document_file[]']").prop("files")[0];
                let document_url = $(this).find("input[name='document_file[]']").attr("data-url");

                upload_data.append("document_label[]", label_name);
                upload_data.append("document_file[]", document);
                upload_data.append("document_url[]", document_url);
                upload_data.append("client_id", client_id);
                upload_data.append("applicant_status", step_stage);

            });

            try {

                upload_data.append("<?= $this->security->get_csrf_token_name(); ?>", csrfToken);

                let response = await $.ajax({
                    url: "<?= base_url("admin/clients/upload_documents") ?>",
                    method: "POST",
                    data: upload_data,
                    contentType: false,
                    processData: false
                });

                // Handle the success response from the server
                resolve(JSON.parse(response));
            } catch (error) {
                // Handle the error response from the server
                console.error(error);
                reject(error);
            }
        });
    }

    async function save_data(obj, type) {
        var response = [];
        if ($.trim(type.toLowerCase()) == "email") {
            show_loader();
            response = await update_email();
        } else if ($.trim(type.toLowerCase()) == "vendor") {
            show_loader();
            response = await update_vendor();
        } else if ($.trim(type.toLowerCase()) == "sop") {
            show_loader();
            response = await update_sop();
        }

        if (response.resp_code == "RCS") {
            hide_loader();
            alert_float("success", response.resp_desc);
            $(obj).hide();
            $(obj).closest(".row").find('input, select').prop('disabled', true);
            $(obj).closest(".row").find('input, select').selectpicker('refresh');
            $(obj).siblings(".fa").show();

        } else {
            hide_loader();
            if (response.resp_code != undefined) {
                alert_float("danger", response.resp_desc);
                hide_loader();
                return false;
            } else {
                alert_float("danger", response);
                hide_loader();
                return false;
            }
        }

        check_profile_status();
    }

    function update_email() {
        return new Promise(function(resolve, reject) {
            let email = $("#email_creation").val();
            let upload_data = new FormData();
            upload_data.append("<?= $this->security->get_csrf_token_name(); ?>", csrfToken);
            upload_data.append("email_creation", email);
            upload_data.append("client_id", client_id);
            $.ajax({
                url: "<?= base_url("admin/clients/update_email_creation") ?>",
                method: "POST",
                data: upload_data,
                contentType: false,
                processData: false,
                success: function(response) {
                    resolve(JSON.parse(response));
                },
                error: function(error) {
                    reject(error);
                }
            });
        });
    }


    function update_vendor() {
        return new Promise(function(resolve, reject) {
            let vendor = $("#select_vendor").val();
            let upload_data = new FormData();
            upload_data.append("<?= $this->security->get_csrf_token_name(); ?>", csrfToken);
            upload_data.append("vendor", vendor);
            upload_data.append("client_id", client_id);
            if (vendor != '') {
                $.ajax({
                    url: "<?= base_url("admin/clients/update_vendor") ?>",
                    method: "POST",
                    data: upload_data,
                    contentType: false,
                    processData: false,
                    success: function(response) {
                        resolve(JSON.parse(response));
                    },
                    error: function(error) {
                        reject(error);
                    }
                });
            }
        });
    }

    function update_sop() {
        return new Promise(function(resolve, reject) {
            let document = $("input[name='sop_document']").prop("files")[0];
            let document_url = $("input[name='sop_document']").attr("data-url");
            let upload_data = new FormData();
            upload_data.append("<?= $this->security->get_csrf_token_name(); ?>", csrfToken);
            upload_data.append("sop_document", document);
            upload_data.append("document_url", document_url);
            upload_data.append("client_id", client_id);
            if (document != '' || document_url != '') {
                $.ajax({
                    url: "<?= base_url("admin/clients/update_sop") ?>",
                    method: "POST",
                    data: upload_data,
                    contentType: false,
                    processData: false,
                    success: function(response) {
                        resolve(JSON.parse(response));
                    },
                    error: function(error) {
                        reject(error);
                    }
                });
            }
        });
    }


    function edit_data(obj, type) {
        var showAlert = false; // Variable to track whether to show the alert or not

        $("#profile_div .row").each(function() {
            if ($(this).find(".fa-file").is(':visible')) {
                showAlert = true;
                return false; // Exit the each loop if fa-file is visible
            }
        });

        if (showAlert) {
            alert_float("danger", "First save previous one.");
        } else {
            $(obj).hide();
            $(obj).closest(".row").find('input, select').prop('disabled', false);
            $(obj).closest(".row").find('input, select').selectpicker('refresh');
            $(obj).siblings(".fa").show();
            $("#profile_div").find(".next").attr("disabled", true);
        }

    }


    if (customer_admins.length > 0) {
        var admin_ids = [];
        var html = "";
        for (var i = 0; i < customer_admins.length; i++) {
            admin_ids.push(customer_admins[i].staff_id);
        }
        console.log(staff_id);
        console.log(admin_ids);
        if ($.inArray(staff_id, admin_ids) !== -1) {
            if (upload_documents.document_status != undefined && upload_documents.document_status == 1) {
                html = '<h3 class="message-notification ' + upload_documents.color_name + '"> Documents is ' + upload_documents.document_status_name + '</h3>';
                $(".document_upload_files").find(".add_document").hide();
                $(".document_upload_files").each(function() {
                    $(this).find("input").attr("disabled", true);
                });
            } else {
                html = '<h3 class="message-notification">Take action on document verification ';
                $.each(upload_documents_button, function(index, item) {
                    html += ' <button class="btn btn-' + item.color + '" data-color="' + item.color + '"  data-text="' + item.name + '" type="button" onclick="update_document_status(' + item.id + ',this)">' + item.name + '</button>';
                });
                html += '</h3>';
            }
            $(".document_approval_message_action").html(html);
        } else {
            if (upload_documents.document_status != undefined && upload_documents.document_status == 1) {
                html = '<h3 class="message-notification ' + upload_documents.color_name + '">Your Documents is ' + upload_documents.document_status_name + ' by ' + upload_documents.staffname + '</h3>';
                $(".add_document").hide();
                $(".document_upload_files").each(function() {
                    $(this).find("input").attr("disabled", true);
                });
            } else {
                html = '<h3 class="message-notification">Your Documents under Processing</h3>';
            }
            $(".document_approval_message_action").html(html);
        }





        console.log(profile_creation_data);
        if ($.inArray(staff_id, admin_ids) !== -1) {
            if (profile_creation_data.profile_status != undefined && profile_creation_data.profile_status == 1) {
                html = '<h3 class="message-notification ' + profile_creation_data.color_name + '"> Profile is ' + profile_creation_data.profile_status_name + '</h3>';
                $("#profile_creation_div").find(".fa-pencil-square-o").hide();
                $("#profile_creation_div").find(".fa-file").hide();
            } else {
                html = '<h3 class="message-notification">Take action on profile verification ';
                $.each(profile_verification_button, function(index, item) {
                    html += ' <button class="btn btn-' + item.color + '" data-color="' + item.color + '"  data-text="' + item.name + '" type="button" onclick="update_profile_status_btn(' + item.id + ',this)">' + item.name + '</button>';
                });
                html += '</h3>';
            }
            $(".profile_approval_message_action").html(html);
        } else {
            if (profile_creation_data.profile_status != undefined && profile_creation_data.profile_status == 1) {
                html = '<h3 class="message-notification ' + profile_creation_data.color_name + '">Your Profile is ' + profile_creation_data.profile_status_name + ' by ' + profile_creation_data.staffname + '</h3>';
                $("#profile_creation_div").find(".fa-pencil-square-o").hide();
                $("#profile_creation_div").find(".fa-file").hide();
            } else {
                html = '<h3 class="message-notification">Your Profile under Processing</h3>';
            }
            $(".profile_approval_message_action").html(html);
        }


    }

    async function update_document_status(status, obj) {
        try {
            const color = $(obj).data("color");
            const text = $(obj).data("text");
            const upload_data = new FormData();
            upload_data.append("<?= $this->security->get_csrf_token_name(); ?>", csrfToken);
            upload_data.append("document_status", status);
            upload_data.append("client_id", client_id);
            show_loader();

            const response = await $.ajax({
                url: "<?= base_url("admin/clients/update_document_verification_status") ?>",
                method: "POST",
                data: upload_data,
                contentType: false,
                processData: false
            });

            // Handle the success response from the server
            const parsedResponse = JSON.parse(response);
            if (parsedResponse.resp_code === "RCS") {
                hide_loader();
                alert_float("success", parsedResponse.resp_desc);
                const html = `<h3 class="message-notification ${color}"> Documents is ${text}</h3>`;
                $(".document_approval_message_action").html(html);
            }
        } catch (error) {
            // Handle the error response from the server
            console.error(error);
            alert_float("success", error);
        }
    }
    async function update_profile_status_btn(status, obj) {
        try {
            const color = $(obj).data("color");
            const text = $(obj).data("text");
            const upload_data = new FormData();
            upload_data.append("<?= $this->security->get_csrf_token_name(); ?>", csrfToken);
            upload_data.append("profile_status", status);
            upload_data.append("client_id", client_id);
            show_loader();

            const response = await $.ajax({
                url: "<?= base_url("admin/clients/update_profile_verification_status") ?>",
                method: "POST",
                data: upload_data,
                contentType: false,
                processData: false
            });

            // Handle the success response from the server
            const parsedResponse = JSON.parse(response);
            if (parsedResponse.resp_code === "RCS") {
                hide_loader();
                alert_float("success", parsedResponse.resp_desc);
                const html = `<h3 class="message-notification ${color}"> Profile is ${text}</h3>`;
                $(".profile_approval_message_action").html(html);
            }
        } catch (error) {
            // Handle the error response from the server
            console.error(error);
            alert_float("success", error);
        }
    }



    async function add_university_div() {
        let response = await validate_university_div();
        if (response) {
            let html = `<div class="col-md-12 university_div">
                                <div class="col-md-5">
                                <input type="hidden" name="university_id">
                                    <select class="selectpicker from-control"  data-width="100%" name="select_university" id="" data-live-search="true">
                                    <option value="">Select University</option>
                                        <?php
                                        if (!empty($university_drop_down)) {
                                            foreach ($university_drop_down as $country_name => $university_list) {
                                                if (!empty($university_list)) {
                                        ?>
                                                    <optgroup label="<?= $country_name ?>" id="<?= $country_name ?>">
                                                        <?php
                                                        foreach ($university_list as $university_name) {
                                                            if (!empty($university_name)) {
                                                        ?>
                                                                <option value='<?= $university_name ?>'><?= $university_name ?></option>
                                                        <?php
                                                            }
                                                        }
                                                        ?>
                                                    </optgroup>
                                        <?php
                                                }
                                            }
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="col-md-5">
                                    <?php
                                    echo render_select('select_university_vendor', $customer_vendors, array('id', 'name')); ?>
                                </div>

                                <div class="col-md-2">
                                     <button class="col-md-2 add_document" type="button" onclick="remove_university_div(this)"><i class="fa fa-trash text-danger" aria-hidden="true"></i></button>
                                    
                                </div>
                            </div>`;
            $(".add_university_div_block").append(html);
            select_reinit();
        }

    }

    function validate_university_div() {
        return new Promise((resolve, reject) => {
            $(".add_university_div_block .university_div").each(function() {
                let select_university = $(this).find("select[name='select_university']").val();
                let select_university_vendor = $(this).find("select[name='select_university_vendor']").val()

                if (select_university === undefined || $.trim(select_university) === "") {
                    $(this).find("select[name='select_university']").focus();
                    alert_float("danger", "Select university is requried.");
                    resolve(false);
                    return;
                }

                if ((select_university_vendor === undefined || $.trim(select_university_vendor) === "")) {
                    $(this).find("select[name='select_university_vendor']").focus();
                    alert_float("danger", "Select vendor is requried.");
                    resolve(false);
                    return;
                }

                // Additional validation logic or processing can be added here
            });

            resolve(); // Resolving the promise if all validations pass
        });
    }

    function select_reinit() {
        $(".selectpicker").selectpicker('refresh');
    }

    function remove_university_div(obj) {
        $(obj).parents(".university_div").remove();
    }

    function update_university() {
        return new Promise(async (resolve, reject) => {
            let upload_data = new FormData();
            let university_shortlisting = [];
            let universityVendorMap = {};
            $(".add_university_div_block .university_div").each(function() {
                let university_id = $(this).find("input[name='university_id']").val();
                let select_university = $(this).find("select[name='select_university']").val();
                let select_university_vendor = $(this).find("select[name='select_university_vendor']").val()
                if (university_id == undefined) {
                    university_id = "";
                }

                university_shortlisting.push({
                    "university": select_university,
                    "vendor": select_university_vendor,
                    "university_id": university_id
                });
                if (!universityVendorMap.hasOwnProperty(select_university)) {
                    universityVendorMap[select_university] = select_university_vendor;
                } else {
                    alert_float("danger", "Duplicate entry found: university '" + select_university + "' connected with multiple vendors.");
                    return false;
                }

            });

            upload_data.append("university_shortlisting", JSON.stringify(university_shortlisting));
            try {

                upload_data.append("<?= $this->security->get_csrf_token_name(); ?>", csrfToken);
                upload_data.append("client_id", client_id);
                upload_data.append("applicant_status", step_stage);
                let response = await $.ajax({
                    url: "<?= base_url("admin/clients/update_university") ?>",
                    method: "POST",
                    data: upload_data,
                    contentType: false,
                    processData: false
                });

                // Handle the success response from the server
                resolve(JSON.parse(response));
            } catch (error) {
                // Handle the error response from the server
                console.error(error);
                reject(error);
            }
        });
    }


    function update_profile() {
        return new Promise(function(resolve, reject) {

            let upload_data = new FormData();
            upload_data.append("<?= $this->security->get_csrf_token_name(); ?>", csrfToken);
            upload_data.append("client_id", client_id);
            upload_data.append("applicant_status", step_stage);
            $.ajax({
                url: "<?= base_url("admin/clients/update_profile") ?>",
                method: "POST",
                data: upload_data,
                contentType: false,
                processData: false,
                success: function(response) {
                    resolve(JSON.parse(response));
                },
                error: function(error) {
                    reject(error);
                }
            });
        });
    }
</script>