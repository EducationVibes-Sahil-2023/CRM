<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head();
$role = $this->db->where('staffid', get_staff_user_id())->get(db_prefix() . 'staff')->row()->role;
array_unshift($location, array());
$category = [];
$category[] = array("id" => "", "name" => "");
$category[] = array("id" => "-1", "name" => "Previous");
$category[] = array("id" => "1", "name" => "Today");
$category[] = array("id" => "2", "name" => "Upcoming");
?>
<style>
    a {
        cursor: pointer;
    }

    .margin-top {
        margin-top: 10px;
    }

    .border-card {
        margin-bottom: 10px;
        text-align: center;
        padding: 5px 0px;
        box-shadow: 1px 1px 6px 1px lightgray;
    }

    .border-card h3.bold {
        margin: 5px !important;
    }

    .border-card span {
        margin: 5px !important;
    }

    #leadSum h4.no-margin {
        font-size: 18px;
        padding: 10px 0px;
    }

    #leadSum .panel-body {
        border-radius: 0px;
        padding: 10px 0px;
    }

    table .dropdown-menu-right {
        right: auto !important
    }
</style>
<script>
    var role_type = "<?= !empty($role) ? $role : 0 ?>";
</script>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">


                        <div class="col-md-4">
                            <a href="#" class="btn btn-default btn-with-tooltip" data-toggle="tooltip" data-title="Lead Status" data-placement="bottom" onclick="slideToggle('.leads-overview');  summary(1); return false;"><i class="fa fa-bar-chart"></i></a>


                        </div>

                        <div class="clearfix"></div>
                        <div class=" hide leads-overview">
                            <hr class="hr-panel-heading" />
                            <div id="leadSum"></div>
                        </div>

                        <div class="clearfix"></div>
                        <hr>
                        <div class="row" id="leads-table ">
                            <div id="filterArea" class="col-md-12 hidden-xs">
                                <div class="row">
                                    <div class="col-md-12">
                                        <p class="bold"><?php echo _l('filter_by'); ?></p>
                                    </div>


                                    <div class="col-md-2 leads-filter-column margin-top">
                                        <?php
                                        echo render_select('status[]', $visitor_status, array('id', 'name'), '', '', array('data-width' => '100%', 'data-none-selected-text' => _l('Visit Status'), 'multiple' => true, 'data-actions-box' => true), array(), 'no-mbot', '', false);
                                        ?>
                                    </div>



                                    <div class="col-md-2 leads-filter-column margin-top">
                                        <?php
                                        echo render_select('location[]', $location, array('id', 'name'), '', [], array('data-width' => '100%', 'multiple' => true, 'data-none-selected-text' => _l('Location'), 'data-actions-box' => true), array(), 'no-mbot', '', false,  'location');
                                        ?>
                                    </div>
                                    <div class="col-md-2 leads-filter-column margin-top">
                                        <?php
                                        echo render_select('type[]', $visitor_type, array('id', 'name'), '', '', array('data-width' => '100%', 'data-none-selected-text' => _l('Visit Type'), 'multiple' => true, 'data-actions-box' => true), array(), 'no-mbot', '', false);
                                        ?>
                                    </div>
                                    <div class="col-md-2 leads-filter-column margin-top">
                                        <?php
                                        echo render_select('attendee[]', $staff, array('staffid', array('firstname', 'lastname')), '', '', array('data-width' => '100%', 'data-none-selected-text' => _l('Attendee'), 'multiple' => true, 'data-actions-box' => true), array(), 'no-mbot', '', false);
                                        ?>
                                    </div>

                                    <div class="col-md-2 leads-filter-column margin-top">
                                        <?php
                                        echo render_select('view_status[]', $statuses, array('id', 'name'), '', [], array('data-width' => '100%', 'data-none-selected-text' => 'Lead status', 'multiple' => true, 'data-actions-box' => true), array(), 'no-mbot', '', false, '');
                                        ?>
                                    </div>

                                    <div class="col-md-2 leads-filter-column margin-top">
                                        <?php
                                        echo render_select('lead_type[]', $lead_type, array('id', 'name'), '', '', array('data-width' => '100%', 'data-none-selected-text' => _l('Lead Type'), 'multiple' => true, 'data-actions-box' => true), array(), 'no-mbot', '', false);
                                        ?>
                                    </div>

                                    <div class="col-md-2 leads-filter-column margin-top">
                                        <?php
                                        echo render_select('source_type[]', $sources, array('id', 'name'), '', '', array('data-width' => '100%', 'data-none-selected-text' => _l('Source Type'), 'multiple' => true, 'data-actions-box' => true), array(), 'no-mbot', '', false);
                                        ?>
                                    </div>




                                    <div class="col-md-2 leads-filter-column margin-top">
                                        <div class="form-group">
                                            <input type="text" class="form-control datepicker" name="from_date" id="from_date" placeholder="From Visitor Date" autocomplete="off">
                                        </div>
                                    </div>
                                    <div class="col-md-2 leads-filter-column margin-top">
                                        <div class="form-group">
                                            <input type="text" class="form-control datepicker" name="to_date" id="to_date" placeholder="To Visitor Date" autocomplete="off">
                                        </div>
                                    </div>
                                    <div class="col-md-2 leads-filter-column margin-top mb-5">
                                        <?php echo render_select('assigned[]', $staff, array('staffid', array('firstname', 'lastname')), '', '', array('data-width' => '100%', 'data-none-selected-text' => _l('leads_dt_assigned'), 'multiple' => true, 'data-actions-box' => true), array(), 'no-mbot', '', false, 'assigned'); ?>
                                    </div>

                                    <div class="col-md-2 leads-filter-column margin-top mb-5">
                                        <?php echo render_select('category', $category, array('id', array('name')), '', '', array('data-width' => '100%', 'data-none-selected-text' => _l('Schedule'), 'data-actions-box' => true), array(), 'no-mbot', '', false, 'category'); ?>
                                    </div>

                                    <div class="col-md-2 leads-filter-column margin-top">
                                        <div class="form-group">
                                            <input type="text" class="form-control datepicker" name="last_update_date" id="last_update_date" placeholder="Last Update Date" autocomplete="off">
                                        </div>
                                    </div>


                                    <div class="col-md-12 text-center leads-filter-column margin-top">
                                        <div class="form-group pull-right">
                                            <?php if(is_admin()){ ?>
                                            <button id="exportBtn" class="btn btn-success"><i class="fa fa-download"></i> Export to Excel</button>
                                            <?php } ?>
                                            <button type="button" class="btn btn-primary" onclick="filter_data();" id="apply_filter">Apply Filter</button>
                                            <button class="btn btn-primary" onclick="window. location. reload();">Reset</button>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                        <?php if(is_admin()){ ?>
                        <div>
                            <h4>Seminar Visits Update</h4>
                            <?php 
                             render_datatable(array( _l('Date Of Visit'), _l('No of Visitors'),_l('Place of Visit'), _l('Visit Type'),  _l('Lead type'),'Image','Address','Whatsapp Notificate','Action'), 'lead-visitor-update-table');
                            ?>
                        <hr>
                        </div>
                        <?php } ?>
                        
                        <h4>Request Generate</h4>
                        <hr>

                        <?php
                        if(!is_admin()){
                               render_datatable(array("Status", _l('Date Of Visit'), _l('Student Name'), "Contact no.", "Update Count", "Duration", _l('Place of Visit'), _l('Visit Type'), _l('Attendee'), "Assignee", _l('Lead type'), "Lead Status", "Lead Source","Created Date", "Updated Date", "connected date"), 'lead-visitor-genrate-table');
                        }
                        else{
                               render_datatable(array("Status", _l('Date Of Visit'), _l('Student Name'), "Contact no.", "Update Count", "Duration", _l('Place of Visit'), _l('Visit Type'), _l('Attendee'), "Assignee", _l('Lead type'), "Lead Status", "Lead Source", "Fb Form Name","Campaign","Adsset","Ads","term", "Created Date", "Updated Date", "connected date"), 'lead-visitor-genrate-table');
                        }
                     
                        ?>
                        <?php if (!is_admin()) { ?>
                            <h4>Request Received</h4>
                            <hr>

                            <?php
                            render_datatable(array("Status", _l('Date Of Visit'), _l('Student Name'), "Contact no.", "Update Count", "Duration", _l('Place of Visit'), _l('Visit Type'), _l('Attendee'), "Assignee", _l('Lead type'), "Lead Status", "Lead Source"), 'lead-visitor-request-table');
                            ?>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>


<script>

 
 
    var r = {
        status: "[name='status[]']",
        location: "[name='location[]']",
        type: "[name='type[]']",
        attendee: "[name='attendee[]']",
        lead_type: "[name='lead_type[]']",
        source_type: "[name='source_type[]']",
        lead_status: "[name='view_status[]']",
        from_date: "[name='from_date']",
        to_date: "[name='to_date']",
        assigned: "[name='assigned[]']",
        category: "[name='category']",
        last_update_date: "[name='last_update_date']",
       

    };

    function refresh_visitor_table() {
        initDataTable('.table-lead-visitor-genrate-table', admin_url + 'leads/table_lead_visitor', 'undefined', 'undefined', r, [0, 'desc']);
        <?php if (!is_admin()) { ?>
            initDataTable('.table-lead-visitor-request-table', admin_url + 'leads/table_lead_visitor/1', 'undefined', 'undefined', r, [0, 'desc']);
        <?php } ?>
        
         initDataTable('.table-lead-visitor-update-table', admin_url + 'leads/table_lead_visitor_update', 'undefined', 'undefined', r, [0, 'desc']);
    }

    $(document).ready(function() {
        refresh_visitor_table();
        set_search_cities();
    })

<?php if(is_admin()){ ?>
document.getElementById("exportBtn").addEventListener("click", async () => {
    show_loader();
    try {
        const postData = collectFormData();
        const data = await fetchLeads(postData);

        if (!data || data.length === 0) {
            alert("No data found");
            hide_loader();
            return;
        }

        exportToCSV(data);

    } catch (err) {
         hide_loader();
        console.error("Export failed:", err);
        alert("Export failed");
    }
});
<?php }?>

 <?php $staff_map = array_column($staff, 'full_name', 'staffid'); ?>
 
 const staffMap = <?php echo json_encode($staff_map); ?>;
 
 <?php $lead_map = array_column($lead_type, 'name', 'id'); ?>
 
 const leadMap = <?php echo json_encode($lead_map); ?>;




// ✅ Collect form data
function collectFormData() {
    const getMultiValues = sel =>
        Array.from(document.querySelectorAll(sel + ":checked, " + sel + " option:checked"))
            .map(el => el.value);

    const getValue = sel => document.querySelector(sel)?.value || "";

    return {
        status: getMultiValues("[name='status[]']"),
        location: getMultiValues("[name='location[]']"),
        type: getMultiValues("[name='type[]']"),
        attendee: getMultiValues("[name='attendee[]']"),
        lead_type: getMultiValues("[name='lead_type[]']"),
        source_type: getMultiValues("[name='source_type[]']"),
        lead_status: getMultiValues("[name='view_status[]']"),
        assigned: getMultiValues("[name='assigned[]']"),
        excelStatus: 1,
        from_date: getValue("[name='from_date']"),
        to_date: getValue("[name='to_date']"),
        category: getValue("[name='category']"),
        last_update_date: getValue("[name='last_update_date']"),
        csrf_token_name: csrfData.hash,
      
    };
}


// ✅ API call
async function fetchLeads(postData) {
    const formData = new FormData();

    for (const key in postData) {
        const val = postData[key];
        if (Array.isArray(val)) {
            val.forEach(v => formData.append(key + "[]", v));
        } else {
            formData.append(key, val);
        }
    }

    const res = await fetch(admin_url + "leads/table_lead_visitor", {
        method: "POST",
        body: formData
    });

    if (!res.ok) throw new Error("Server error");

    return res.json();
}


// ✅ FAST Export
function exportToCSV(data) {

    const headers = [
        "Status","Date Of Visit","Student Name","Contact no.","Update Count",
        "Duration","Place of Visit","Visit Type","Attendee","Assignee",
        "Lead type","Lead Status","Lead Source","Fb Form Name","Campaign","Adsset","Ads","term",
        "Created Date","Updated Date","Connected Date"
    ];

    const keys = [
        "status","date_of_visit","student_name","phonenumber","update_count",
        "call_duration","location","visitor_type","assigned","created_by",
        "lead_type","status_name","source_name","website","utm_campaign_name","utm_campaign_name","utm_ads_name","utm_ads_name",
        "created_at","updated_at","lastcontact_date"
    ];

    // 🚀 Pre-define format rules (NO repeated ifs)
    const formatters = {
        assigned: v => staffMap[v] || "",
        created_by: v => staffMap[v] || "",
        lead_type: v => leadMap[v] || v,
        call_duration: v => v ? secondsToHMS(v) : "",
        date_of_visit: formatDate,
        created_at: formatDate,
        updated_at: formatDate,
        lastcontact_date: formatDate
    };

    const stripHTML = v => v?.toString().replace(/<[^>]*>?/gm, "") || "";

    let csv = headers.join(",") + "\n";

    for (let i = 0; i < data.length; i++) {
        const item = data[i];

        const row = new Array(keys.length);

        for (let j = 0; j < keys.length; j++) {
            const key = keys[j];

            let value = item[key];

            // 🚀 Apply formatter if exists
            if (formatters[key]) {
                value = formatters[key](value);
            }

            value = stripHTML(value);

            row[j] = `"${value}"`;
        }

        csv += row.join(",") + "\n";
    }

    downloadCSV(csv);
    hide_loader();
}


// ✅ Download
function downloadCSV(content) {
    const blob = new Blob([content], { type: "text/csv;charset=utf-8;" });
    const url = URL.createObjectURL(blob);

    const a = document.createElement("a");
    a.href = url;
    a.download = "visitor_logs.csv";
    a.click();
hide_loader();
    URL.revokeObjectURL(url);
}


// ✅ Date format (FAST)
function formatDate(d) {
    if (!d) return "";
    const date = new Date(d);
    if (isNaN(date)) return d;

    return date.toLocaleDateString("en-GB", {
        day: "numeric",
        month: "long",
        year: "numeric"
    });
}


// ✅ Duration
function secondsToHMS(s) {
    s = +s || 0;
    const h = (s / 3600) | 0;
    const m = ((s % 3600) / 60) | 0;
    const sec = (s % 60) | 0;

    return `${h.toString().padStart(2,"0")}:${m.toString().padStart(2,"0")}:${sec.toString().padStart(2,"0")}`;
}

    function filter_data() {

        let fromDate = $("input[name='from_date']").val().trim();
        let toDate = $("input[name='to_date']").val().trim();
        if (fromDate === "" && toDate === "") {} else {
            if (fromDate === "" || toDate === "") {
                if (fromDate === "") {
                    $("input[name='from_date']").focus();
                }
                if (toDate === "") {
                    $("input[name='to_date']").focus();
                }
                alert_float("danger", "Both From Date and To Date are required.");
                return false;
            }
        }

        $('.table-lead-visitor-genrate-table').DataTable().destroy();
        $('.table-lead-visitor-genrate-table tbody').empty();
        initDataTable('.table-lead-visitor-genrate-table', admin_url + 'leads/table_lead_visitor', 'undefined', 'undefined', r, [0, 'desc']);
        <?php if (!is_admin()) { ?>
            $('.table-lead-visitor-request-table').DataTable().destroy();
            $('.table-lead-visitor-request-table tbody').empty();
            initDataTable('.table-lead-visitor-request-table', admin_url + 'leads/table_lead_visitor/1', 'undefined', 'undefined', r, [0, 'desc']);
        <?php } ?>
        
                 <?php if (is_admin()) { ?>
                 
                      $('.table-lead-visitor-update-table').DataTable().destroy();
        $('.table-lead-visitor-update-table tbody').empty();
        initDataTable('.table-lead-visitor-update-table', admin_url + 'leads/table_lead_visitor_update', 'undefined', 'undefined', r, [0, 'desc']);
<?php } ?>

        $("#leadSum").html('');
        $(".leads-overview").hide();

    }


    function delete_visit(id) {
        show_loader();
        $.ajax({
            type: "POST",
            url: admin_url + "leads/delete_visit",
            data: {
                id: id
            },
            dataType: "JSON",
            cache: false,
            success: function(data) {
                hide_loader();
                if (data.success) {
                    alert_float('success', data.message);
                    filter_data();
                } else {
                    alert_float('danger', data.message);
                }

            }
        }); // you have missed this bracket
        return false;
    }

    function set_search_cities() {

        // Bind event to search input ONLY inside #visitor_location selectpicker
        // $('#location').parent().find('.bs-searchbox input').on('input', function() {
        //     let searchQuery = $(this).val();

        //     if (searchQuery.length > 2) { // Start AJAX after 3+ characters
        //         let formData = new FormData(); // Correct FormData initialization

        //         formData.append("csrf_token_name", csrfData.hash);
        //         formData.append("value", searchQuery); // Corrected `.val()` issue

        //         $.ajax({
        //             url: "<?php echo base_url('admin/leads/search_cities'); ?>", // Replace with actual API URL
        //             method: "POST", // FormData requires POST (not GET)
        //             data: formData,
        //             processData: false, // Prevent jQuery from transforming FormData
        //             contentType: false, // Ensure correct Content-Type is set for FormData
        //             dataType: "JSON",
        //             success: function(response) { // 'data' is already parsed as JSON

        //                 $('#location').empty(); // Clear old options
        //                 let data = response.data;
        //                 if (data.length > 0) {
        //                     $.each(data, function(index, item) {
        //                         $('#location').append(`<option value="${item.id}">${item.name}</option>`);
        //                     });
        //                 } else {
        //                     $('#location').append('<option disabled>No results found</option>'); // Handle no results case
        //                 }

        //                 $('#location').selectpicker('refresh'); // Refresh selectpicker
        //             },
        //             error: function(xhr, status, error) {
        //                 console.error("AJAX Error: ", error);
        //             }
        //         });

        //     }
        // });

    }

    let xhr = null;

    function summary(status = "") {
        show_loader();

        const requestData = {};
        if (r && typeof r === "object") {
            Object.keys(r).forEach(key => {
                const value = r[key];

                if (typeof value === "string") {
                    // Use jQuery to get input value
                    requestData[key] = $(value).val();
                } else if (typeof value === "object" && value !== null) {
                    // Serialize nested object
                    requestData[key] = JSON.stringify(value);
                }
            });
        }

        // Add CSRF protection if available
        if (typeof csrfData !== "undefined" && csrfData.token_name && csrfData.hash) {
            requestData[csrfData.token_name] = csrfData.hash;
        }

        // Abort any ongoing request to prevent race conditions
        if (xhr !== null) {
            xhr.abort();
        }
        if ($("#leadSum").html() === "") {
            xhr = $.ajax({
                type: "POST",
                url: `${admin_url}leads/visitor_lead_summary_filter`,
                data: requestData,
                dataType: "JSON",
                cache: false,
                success: function(data) {

                    if ($("#leadSum").html() === "" && data.status) {
                        $("#leadSum").html(data.status);
                    }
                    hide_loader();
                },
                error: function() {
                    hide_loader();
                    console.error("An error occurred while fetching the summary.");
                }
            });
        }
        hide_loader();
        return false;
    }

    function visit_lead_mark_as(e, t) {
        var a = {};
        a.status = e, a.id = t, $.post(admin_url + "leads/update_visit_lead_status", a).done(function(e) {
            filter_data();
        })
    }
    
function saveSeminar_Data(id, location, visitor_type, lead_type, date_of_visit)
{
    let formData = new FormData();

    formData.append('id', id);
    formData.append('location', location);
    formData.append('visitor_type', visitor_type);
    formData.append('lead_type', lead_type);
    formData.append('date_of_visit', date_of_visit);

    formData.append(
        'seminar_address',
        $('#visit_address_' + id).val() || ''
    );

    formData.append(
        'whatsapp_notify',
        $('#whatsapp_notify_' + id).val() || ''
    );

    let fileInput = $('#visit_image_' + id);

    if (
        fileInput.length &&
        fileInput[0].files &&
        fileInput[0].files.length > 0
    ) {
        formData.append(
            'visit_image',
            fileInput[0].files[0]
        );
    }
    
  let address = $('#visit_address_' + id).val().trim();
let whatsappNotify = $('#whatsapp_notify_' + id).val();
let imageInput = $('#visit_image_' + id);

// Validate date
if (!date_of_visit) {
    alert_float('danger', 'Please select the date of visit.');
    return false;
}

// Validate address
if (!address) {
    alert_float('danger', 'Please enter the seminar/visit address.');
    $('#visit_address_' + id).focus();
    return false;
}

// Validate WhatsApp notification
if (!whatsappNotify) {
    alert_float('danger', 'Please select the WhatsApp notification option.');
    $('#whatsapp_notify_' + id).focus();
    return false;
}

// Validate image
if (
    !imageInput.length ||
    !imageInput[0].files ||
    imageInput[0].files.length === 0
) {
    alert_float('danger', 'Please upload the visit image.');
    return false;
}



    // CSRF Token
    formData.append(csrfData.token_name, csrfData.hash);

    $.ajax({
        url: admin_url + 'leads/save_seminar_data',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',

        beforeSend: function () {
            $('.save-visit-row[data-id="' + id + '"]')
                .addClass('disabled')
                .prop('disabled', true);
        },

        success: function (response) {

            if (response.success) {

                alert_float('success', response.message);

                // Refresh csrf token if returned
                if (response.csrf_hash) {
                    csrfData.hash = response.csrf_hash;
                }

            } else {
                alert_float('danger', response.message);
            }
        },

        error: function (xhr) {

            let message = 'Something went wrong.';

            if (
                xhr.responseJSON &&
                xhr.responseJSON.message
            ) {
                message = xhr.responseJSON.message;
            }

            alert_float('danger', message);
        },

        complete: function () {
            $('.save-visit-row[data-id="' + id + '"]')
                .removeClass('disabled')
                .prop('disabled', false);
        }
    });
}
</script>