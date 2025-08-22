<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="row">
    <!-- Dropdown for Activity Type -->
    <div class="col-md-4 mb-3">
        <label for="activityType" class="form-label">Select Activity Type</label>
        <select class="form-control" id="activityType" onchange="handleActivityChange()">
            <option value="1">Fees Logs Activity</option>
            <option value="2">Quotation Logs Activity</option>
            <option value="3">Documents Delete Activity</option>
            <option value="4">Applicant Activity</option>
            <option value="5">Document Log Activity</option>
        </select>
    </div>

    <!-- Activity Feed Section -->
    <div class="col-md-12 mt-3">
        <div class="card">
            <div class="card-body ">
                <h3 class="card-title">Activity</h3>
                <div class="lead-activity activity-feed" style="height:400px; overflow:scroll;">
                    <!-- Dynamic content will be loaded here -->
                </div>

            </div>
        </div>
    </div>
</div>


<?php init_tail(); ?>

<script>
    var activity_url = "<?= base_url() ?>admin/clients/activity_logs/<?= $client_id ?>";

    function handleActivityChange() {
        var selectedType = document.getElementById('activityType').value;

        // Example URL - update to your actual route
        var url = activity_url;

        // Pass the selected value as POST data
        reloadActivity_list(url, {
            type: selectedType
        });
    }

    // Updated reloadActivity_list with POST
    function reloadActivity_list(url, postData = {}) {
        $(".lead-activity").html('');
        show_loader();
        $.ajax({
            url: url,
            type: 'POST',
            data: postData,
            success: function(data) {
                $(".lead-activity").html(data);
                hide_loader();
            },
            error: function(xhr, status, error) {
                hide_loader();
                $(".lead-activity").html("Error loading data");
                console.error("Error loading data:", error);
            }
        });
    }


    handleActivityChange();
</script>