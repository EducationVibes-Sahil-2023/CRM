<?php

defined('BASEPATH') or exit('No direct script access allowed');

$has_permission_delete = has_permission('staff', '', 'delete');
$departmentsArray = $this->ci->staff_model->staff_department();
$select_staff_office_region = staff_location_region();
$select_staff_state_region = staff_state_region();
$departmentsArray = array_column($departmentsArray, null, "id");
$custom_fields = get_custom_fields('staff', [
    'show_on_table' => 1,
]);
$aColumns = [
    "CONCAT(firstname,' ',lastname) as fullname",
    'emp_code',
    'email',
    'phonenumber',
    'alternate_number',
    db_prefix() . 'staff_department.name as department_name',
     db_prefix() . 'staff_location_region.name as office_region_name',
      db_prefix() . 'staff_state_region.name as lead_region_name',
      'is_counsoller',
    db_prefix() . 'roles.name as role_name',
    'last_login',
    'active',
     'department',
     'office_location_region',
     'office_state_region',
     'staffid',
];
$sIndexColumn = 'staffid';
$sTable       = db_prefix() . 'staff';
$join[]         = ' LEFT JOIN ' . db_prefix() . 'roles ON ' . db_prefix() . 'roles.roleid = ' . db_prefix() . 'staff.role ';
$join[]         =  ' LEFT JOIN ' . db_prefix() . 'staff_department ON ' . db_prefix() . 'staff_department.id = ' . db_prefix() . 'staff.department ';
$join[]         =  ' LEFT JOIN ' . db_prefix() . 'staff_location_region ON ' . db_prefix() . 'staff_location_region.id = ' . db_prefix() . 'staff.office_location_region ';
$join[]         =  ' LEFT JOIN ' . db_prefix() . 'staff_state_region ON ' . db_prefix() . 'staff_state_region.id = ' . db_prefix() . 'staff.office_state_region ';
$i            = 0;
foreach ($custom_fields as $field) {
    $select_as = 'cvalue_' . $i;
    if ($field['type'] == 'date_picker' || $field['type'] == 'date_picker_time') {
        $select_as = 'date_picker_cvalue_' . $i;
    }
    array_push($aColumns, 'ctable_' . $i . '.value as ' . $select_as);
    array_push($join, 'LEFT JOIN ' . db_prefix() . 'customfieldsvalues as ctable_' . $i . ' ON ' . db_prefix() . 'staff.staffid = ctable_' . $i . '.relid AND ctable_' . $i . '.fieldto="' . $field['fieldto'] . '" AND ctable_' . $i . '.fieldid=' . $field['id']);
    $i++;
}
// Fix for big queries. Some hosting have max_join_limit
if (count($custom_fields) > 4) {
    @$this->ci->db->query('SET SQL_BIG_SELECTS=1');
}

$where = hooks()->apply_filters('staff_table_sql_where', []);

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [
    'profile_image',
    'lastname',
    'staffid',
]);

$output  = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow) {
    // $row = [];
    // print_r($aRow);
    // die;
    
//     for ($i = 0; $i < count($aColumns); $i++) {
//         if (strpos($aColumns[$i], 'as') !== false && !isset($aRow[$aColumns[$i]])) {
//             $_data = $aRow[strafter($aColumns[$i], 'as ')];
//         } else {
//             $_data = $aRow[$aColumns[$i]];
//         }
//         if ($aColumns[$i] == 'last_login') {
//             if ($_data != null) {
//                 $_data = '<span class="text-has-action is-date" data-toggle="tooltip" data-title="' . _dt($_data) . '">' . time_ago($_data) . '</span>';
//             } else {
//                 $_data = 'Never';
//             }
//         } elseif ($aColumns[$i] == 'active') {
//             $checked = '';
//             if ($aRow['active'] == 1) {
//                 $checked = 'checked';
//             }

//             $_data = '<div class="onoffswitch">
//                 <input type="checkbox" ' . (($aRow['staffid'] == get_staff_user_id() || (is_admin($aRow['staffid']) || !has_permission('staff', '', 'edit')) && !is_admin()) ? 'disabled' : '') . ' data-switch-url="' . admin_url() . 'staff/change_staff_status" name="onoffswitch" class="onoffswitch-checkbox" id="c_' . $aRow['staffid'] . '" data-id="' . $aRow['staffid'] . '" ' . $checked . '>
//                 <label class="onoffswitch-label" for="c_' . $aRow['staffid'] . '"></label>
//             </div>';

//             // For exporting
//             $_data .= '<span class="hide">' . ($checked == 'checked' ? _l('is_active_export') : _l('is_not_active_export')) . '</span>';
//         } elseif ($aColumns[$i] == 'firstname') {
//             $_data = '<a href="' . admin_url('staff/profile/' . $aRow['staffid']) . '">' . staff_profile_image($aRow['staffid'], [
//                 'staff-profile-image-small',
//             ]) . '</a>';
//             $_data .= ' <a href="' . admin_url('staff/member/' . $aRow['staffid']) . '">' . $aRow['firstname'] . ' ' . $aRow['lastname'] . '</a>';

//             $_data .= '<div class="row-options">';
//             $_data .= '<a href="' . admin_url('staff/member/' . $aRow['staffid']) . '">' . _l('view') . '</a>';

//             if (($has_permission_delete && ($has_permission_delete && !is_admin($aRow['staffid']))) || is_admin()) {
//                 if ($has_permission_delete && $output['iTotalRecords'] > 1 && $aRow['staffid'] != get_staff_user_id()) {
//                     $_data .= ' | <a href="#" onclick="delete_staff_member(' . $aRow['staffid'] . '); return false;" class="text-danger">' . _l('delete') . '</a>';
//                 }
//             }

//             $_data .= '</div>';
//         } elseif ($aColumns[$i] == 'email') {
//             $_data = '<a href="mailto:' . $_data . '">' . $_data . '</a>';
//         } elseif ($aColumns[$i] == 'phonenumber') {
//             $_data = ' <a href="javascript:void(0)" onclick="edit_staff_phone_number(' . $aRow['staffid'] . ',' . $aRow['phonenumber'] . ')" >' . $aRow['phonenumber'] . '</a>';
//             $_data .= '<div class="row-options">';
//             if (is_admin()) {
//                 $_data .= '<a href="javascript:void(0)" onclick="edit_staff_phone_number(' . $aRow['staffid'] . ',' . $aRow['phonenumber'] . ')" >' . _l('edit') . '</a>';
//             }
//             $_data .= '</div>';
//         }
//         elseif($aColumns[$i] == 'department_name')
//         {
            
//   $_data="";
//         }
//         else {
//             if (strpos($aColumns[$i], 'date_picker_') !== false) {
//                 $_data = (strpos($_data, ' ') !== false ? _dt($_data) : _d($_data));
//             }
//         }
//         $row[] = $_data;
//     }

    // $row['DT_RowClass'] = 'has-row-options';
    
    $row = [];
    
     $staffName = '<a href="' . admin_url('staff/profile/' . $aRow['staffid']) . '">' . staff_profile_image($aRow['staffid'], [
                 'staff-profile-image-small',
             ]) . '</a>';
             $staffName .= ' <a href="' . admin_url('staff/member/' . $aRow['staffid']) . '">' . $aRow['fullname'] . '</a>';

             $staffName .= '<div class="row-options">';
             $staffName .= '<a href="' . admin_url('staff/member/' . $aRow['staffid']) . '">' . _l('view') . '</a>';

             if (($has_permission_delete && ($has_permission_delete && !is_admin($aRow['staffid']))) || is_admin()) {
                 if ($has_permission_delete && $output['iTotalRecords'] > 1 && $aRow['staffid'] != get_staff_user_id()) {
                     $staffName .= ' | <a href="#" onclick="delete_staff_member(' . $aRow['staffid'] . '); return false;" class="text-danger">' . _l('delete') . '</a>';
                 }
             }
             $staffName .= '</div>';
    
    $phonenumber = ' <a href="javascript:void(0)" onclick="edit_staff_phone_number(' . $aRow['staffid'] . ',' . $aRow['phonenumber'] . ')" >' . $aRow['phonenumber'] . '</a>';
            $phonenumber .= '<div class="row-options">';
             if (is_admin()) {
                 $phonenumber .= '<a href="javascript:void(0)" onclick="edit_staff_phone_number(' . $aRow['staffid'] . ',' . $aRow['phonenumber'] . ')" >' . _l('edit') . '</a>';
             }
             $phonenumber .= '</div>';
             
                $alt_number = !empty($aRow['alternate_number']) ? $aRow['alternate_number'] : '';

$alternative_phonenumber = '<a href="javascript:void(0)" onclick="edit_staff_phone_number(' 
    . $aRow['staffid'] . ', \'' . $alt_number . '\', 1)">' 
    . $alt_number . 
    '</a>';

$alternative_phonenumber .= '<div class="row-options">';

if (is_admin()) {
    $alternative_phonenumber .= '<a href="javascript:void(0)" onclick="edit_staff_phone_number(' 
        . $aRow['staffid'] . ', \'' . $alt_number . '\', 1)">' 
        . _l('edit') . 
        '</a>';
}

$alternative_phonenumber .= '</div>';
              $email = '<a href="mailto:' . $aRow['email'] . '">' . $aRow['email'] . '</a>';
              
               if ($aRow['last_login'] != null) {
                 $lastLogin = '<span class="text-has-action is-date" data-toggle="tooltip" data-title="' . _dt($aRow['last_login']) . '">' . time_ago($aRow['last_login']) . '</span>';
             } else {
                 $lastLogin = 'Never';
            }
            
            $checked = '';
             if ($aRow['active'] == 1) {
                 $checked = 'checked';
             }

             $active = '<div class="onoffswitch">
                 <input type="checkbox" ' . (($aRow['staffid'] == get_staff_user_id() || (is_admin($aRow['staffid']) || !has_permission('staff', '', 'edit')) && !is_admin()) ? 'disabled' : '') . ' data-switch-url="' . admin_url() . 'staff/change_staff_status" name="onoffswitch" class="onoffswitch-checkbox" id="c_' . $aRow['staffid'] . '" data-id="' . $aRow['staffid'] . '" ' . $checked . '>
                 <label class="onoffswitch-label" for="c_' . $aRow['staffid'] . '"></label>
             </div>';

             // For exporting
             $active .= '<span class="hide">' . ($checked == 'checked' ? _l('is_active_export') : _l('is_not_active_export')) . '</span>';
             
             $department ="";
             
              $department = '<span class="inline-block lead-status-' . $aRow['department'] . ' label label-' . (empty($aRow['color']) ? 'default' : '') . '" style="color:;border:1px solid ">' . $aRow['department_name'];
        
            $department .= '<div class="dropdown inline-block mleft5 table-export-exclude">';
            $department .= '<a href="#" style="font-size:14px;vertical-align:middle;" class="dropdown-toggle text-dark" id="tableLeadsStatus-' . $aRow['staffid'] . '" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">';
            $department .= '<span data-toggle="tooltip" title="' . _l('ticket_single_change_status') . '"><i class="fa fa-caret-down" aria-hidden="true"></i></span>';
            $department .= '</a>';
            $department .= '<ul class="dropdown-menu dropdown-menu-right" aria-labelledby="tableLeadsStatus-' . $aRow['staffid'] . '">';
            foreach ($departmentsArray as $leadChangeStatus) {
                if ($aRow['department'] != $leadChangeStatus['id']) {
                    $department .= '<li>
                  <a href="#" onclick="department_mark_as(' . $leadChangeStatus['id'] . ',' . $aRow['staffid'] . '); return false;">
                     ' . $leadChangeStatus['name'] . '
                  </a>
              </li>';
                }
            }
            $department .= '</ul>';
            $department .= '</div>';
        
        $department .= '</span>';
        
        
         $officeRegion ="";
             
              $officeRegion = '<span class="inline-block lead-status-' . $aRow['office_location_region'] . ' label label-' . (empty($aRow['color']) ? 'default' : '') . '" style="color:;border:1px solid ">' . $aRow['office_region_name'];
        
            // $officeRegion .= '<div class="dropdown inline-block mleft5 table-export-exclude">';
            // $officeRegion .= '<a href="#" style="font-size:14px;vertical-align:middle;" class="dropdown-toggle text-dark" id="tableLeadsStatus-' . $aRow['staffid'] . '" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">';
            // $officeRegion .= '<span data-toggle="tooltip" title="' . _l('ticket_single_change_status') . '"><i class="fa fa-caret-down" aria-hidden="true"></i></span>';
            // $officeRegion .= '</a>';
            // $officeRegion .= '<ul class="dropdown-menu dropdown-menu-right" aria-labelledby="tableLeadsStatus-' . $aRow['staffid'] . '">';
            // foreach ($select_staff_office_region as $leadChangeStatus) {
            //     if ($aRow['office_location_region'] != $leadChangeStatus['id']) {
            //         $officeRegion .= '<li>
            //       <a href="#" onclick="office_region_mark_as(' . $leadChangeStatus['id'] . ',' . $aRow['staffid'] . '); return false;">
            //          ' . $leadChangeStatus['name'] . '
            //       </a>
            //   </li>';
            //     }
            // }
            // $officeRegion .= '</ul>';
            // $officeRegion .= '</div>';
        
        $officeRegion .= '</span>';
        
        
         $leadRegion ="";
             
              $leadRegion = '<span class="inline-block lead-status-' . $aRow['office_state_region'] . ' label label-' . (empty($aRow['color']) ? 'default' : '') . '" style="color:;border:1px solid ">' . $aRow['lead_region_name'];
        
            // $leadRegion .= '<div class="dropdown inline-block mleft5 table-export-exclude">';
            // $leadRegion .= '<a href="#" style="font-size:14px;vertical-align:middle;" class="dropdown-toggle text-dark" id="tableLeadsStatus-' . $aRow['staffid'] . '" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">';
            // $leadRegion .= '<span data-toggle="tooltip" title="' . _l('ticket_single_change_status') . '"><i class="fa fa-caret-down" aria-hidden="true"></i></span>';
            // $leadRegion .= '</a>';
            // $leadRegion .= '<ul class="dropdown-menu dropdown-menu-right" aria-labelledby="tableLeadsStatus-' . $aRow['staffid'] . '">';
            // foreach ($select_staff_state_region as $leadChangeStatus) {
            //     if ($aRow['office_state_region'] != $leadChangeStatus['id']) {
            //         $leadRegion .= '<li>
            //       <a href="#" onclick="department_mark_as(' . $leadChangeStatus['id'] . ',' . $aRow['staffid'] . '); return false;">
            //          ' . $leadChangeStatus['name'] . '
            //       </a>
            //   </li>';
            //     }
            // }
            // $leadRegion .= '</ul>';
            // $leadRegion .= '</div>';
        
        $leadRegion .= '</span>';
    
    $row[] = $staffName;
     $row[] = $aRow['emp_code'];
      $row[] = $email;
       $row[] = $phonenumber;
        $row[] = $alternative_phonenumber;
         $row[] = $department;
         $row[] = $officeRegion;
          $row[] = $officeRegion;
         $row[] = !empty($aRow['is_counsoller'])?'Yes':'No';
          $row[] = $aRow['role_name'];
           $row[] = $lastLogin;
    $row[] = $active;
    
        if (isset($row['DT_RowClass'])) {
        $row['DT_RowClass'] .= ' has-row-options';
    } else {
        $row['DT_RowClass'] = 'has-row-options';
    }
    $output['aaData'][] = $row;
}
