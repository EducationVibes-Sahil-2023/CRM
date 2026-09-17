<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); 
$role = $this->db->where('staffid', get_staff_user_id())->get(db_prefix() . 'staff')->row()->role;
?>
<link
   rel="stylesheet"
   href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-daterangepicker/3.1/daterangepicker.css" />

 <title>Post Sales Tracker</title>
<div id="wrapper">
<div class="screen-options-area"></div>
<div class="content">
<div class="row">
<div class="col-md-12 mtop15">
<div class="panel_s">
    <div id="dashboard">
        
    </div>
   </div>
</duv>
</div>
</div>
</div>
</div>



    
    <?php init_tail(); ?>