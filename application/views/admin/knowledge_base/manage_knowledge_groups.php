<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="_buttons">
                            <a href="#" class="btn btn-info pull-left" data-toggle="modal" data-target="#customer_group_modal"><?php echo _l('kb_knowledge_group'); ?></a>
                        </div>
                        <div class="clearfix"></div>
                        <hr class="hr-panel-heading" />
                        <div class="clearfix"></div>
                        <?php render_datatable(array(
                            _l('kb_group_name'),
                            _l('kb_total_members'),
                            _l('kb_group_status'),
                            _l('options'),
                        ), 'customer-groups'); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $this->load->view('admin/knowledge_base/knowledge_group'); ?>
<?php init_tail(); ?>
<script>
    $(function() {
        initDataTable('.table-customer-groups', window.location.href, [1], [1]);
    });
</script>