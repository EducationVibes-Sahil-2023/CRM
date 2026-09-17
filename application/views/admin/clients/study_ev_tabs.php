<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<ul class="nav navbar-pills navbar-pills-flat nav-tabs nav-stacked customer-tabs" role="tablist">
    <?php
    $rendered_tabs = []; // Keep track of rendered keys

    foreach (filter_client_visible_tabs($customer_tabs) as $key => $tab) {
        // Skip if already rendered
        if (in_array($key, $rendered_tabs)) {
            continue;
        }

        $should_render = empty($tab['leadType']) || (!empty($tab['leadType']) && $tab['leadType'] == $lead_type_status);

        if ($should_render) {
            $rendered_tabs[] = $key; // Mark as rendered
    ?>
            <li class="<?php echo ($key == 'profile') ? 'active ' : ''; ?>customer_tab_<?php echo $key; ?>">
                <a data-group="<?php echo $key; ?>" href="<?php echo admin_url('clients/study_ev_partner/' . $client->userid . '?group=' . $key); ?>">
                    <?php if (!empty($tab['icon'])) { ?>
                        <i class="<?php echo $tab['icon']; ?> menu-icon" aria-hidden="true"></i>
                    <?php } ?>
                    <?php echo $tab['name']; ?>
                </a>
            </li>
    <?php
        }
    }
    ?>
</ul>