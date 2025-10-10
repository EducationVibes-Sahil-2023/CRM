<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<?php
$tabs = [
    'profile' => [
        'name' => 'Profile',
        'icon' => 'fa fa-user',
    ],
    'quotation' => [
        'name' => 'Quotation',
        'icon' => 'fa fa-user',
    ],
    'payment' => [
        'name' => 'Payments',
        'icon' => 'fa fa-user',
    ],

];
?>
<div id="wrapper">
    <style>
        .margin-top {
            margin-top: 20px;
        }
    </style>
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <ul class="nav navbar-pills navbar-pills-flat nav-tabs nav-stacked customer-tabs d-flex" role="tablist">
                            <?php
                            foreach ($tabs as $key => $tab) { ?>
                                <li class="<?php echo ($key == $active_tab) ? 'active ' : ''; ?>customer_tab_<?php echo $key; ?>">
                                    <a data-group="<?php echo $key; ?>" href="<?php echo admin_url('hostel_management/groups/' . $getId . '?tab=' . $key); ?>">
                                        <?php if (!empty($tab['icon'])) { ?>
                                            <i class="<?php echo $tab['icon']; ?> menu-icon" aria-hidden="true"></i>
                                        <?php } ?>
                                        <?php echo $tab['name']; ?>
                                    </a>
                                </li>
                            <?php } ?>

                        </ul>

                        <?= $this->load->view("admin/hostel_management/" . $view_page) ?>

                    </div>
                </div>
            </div>
        </div>
    </div>
   