<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head();
$role = $this->db->where('staffid', get_staff_user_id())->get(db_prefix() . 'staff')->row()->role;
?>

<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <?php
                        render_datatable(array(_l('Date Of Visit'), _l('Student Name'), "Contact no.", _l('Place of Visit'), _l('Visit Type'), _l('Attendee'), "Assignee", _l('Lead type'), _l('Comment')), 'lead-visitor-table');
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>

<script>
    initDataTable('.table-lead-visitor-table', admin_url + 'leads/table_lead_visitor', 'undefined', 'undefined', 'undefined', [0, 'desc']);
</script>