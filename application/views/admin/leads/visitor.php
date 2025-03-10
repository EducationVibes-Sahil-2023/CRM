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
                        render_datatable(array(_l('Raised by'), _l('Lead Type'), "Lead Source", _l('Assignation'), _l('PhoneNumber'), _l('New Lead Type'), "New Lead Source", _l('Reason'), _l('Status'), _l('Created Date'), _l("Action")), 'lead-transfer-table');
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>