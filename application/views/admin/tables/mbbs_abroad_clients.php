<?php

defined('BASEPATH') or exit('No direct script access allowed');
$this->ci->load->model('leads_model');

$user_lead_type = get_user_lead_type(get_staff_user_id());
if (!empty($user_lead_type->lead_type)) {
    $user_lead_type = $user_lead_type->lead_type;
} else {
    $user_lead_type = 0;
}
$get_applicant_stages = get_applicant_stage_mbbs();
$get_applicant_stages = array_column($get_applicant_stages, null, 'id');


$get_applicant_sub_stages = get_applicant_sub_stage_mbbs();
$get_applicant_sub_stages = array_column($get_applicant_sub_stages, null, 'id');

$orignal_documents = [];




$hasPermissionDelete = has_permission('customers', '', 'delete');
$customFieldsColumns = [];
$tblma_applicant_tracker = $this->ci->leads_model->tblma_applicant_tracker($this->ci->input->post('columnNames'));

$tblma_applicant_tracker = array_column($tblma_applicant_tracker, null, "tbl_column_name");
$fees_data = get_clients_fees(2);
$post_sales = $this->ci->db->where('staffid', get_staff_user_id())->get(db_prefix() . 'staff')->row();



$statuses = get_applicant_statuses();
$statuses = array_column($statuses, null, 'id');

$this->ci->db->query("SET sql_mode = ''");
$sIndexColumn = 'userid';
$sTable       = db_prefix() . 'clients';
$where        = [];
// Add blank where all filter can be stored
$filter = [];

if (!is_admin() && isset($user_lead_type) && $user_lead_type != $this->ci->db->escape_str($this->ci->input->post('lead_type'))[0]) {

    $where[]        = " AND 1 = 2 ";
}

$aColumns = [];
if (is_admin() || is_postSale()) {
    $aColumns[] = $sTable . ".userid as fid";
}
$aColumns_count = 0;
$joinIn = ' And FIND_IN_SET(' . db_prefix() . 'clients.agent_id, ' . db_prefix() . 'staff.evp_partners) ';
if ($post_sales->evp_partners == "all") {
    $joinIn = ' ';
}

if (!empty($tblma_applicant_tracker)) {
    foreach ($tblma_applicant_tracker as $key => $value) {

        if (in_array($value["column_name"], ["fees", "original_documents", "original_documents_rest", "original_documents_georgia", "apostille_documents", "orignal_document_visa_rest", "orignal_document_visa_georgia"])) {
            if ($value["column_name"] == "fees") {
                if (!empty($fees_data)) {
                    foreach ($fees_data as $fees) {
                        $aColumns[] = "MAX(CASE WHEN " . db_prefix() . "applicant_fees_details.fees_id = {$fees['id']} AND " . db_prefix() . "applicant_fees_details.client_id = {$sTable}.userid THEN CONCAT(" . db_prefix() . "currencies.symbol,'',ifnull(" . db_prefix() . "applicant_fees_details.amount,0)) END) as " . str_replace(" ", "_", strtolower($fees["name"]));
                        $aColumns_count++;
                    }
                }
            }
            if (in_array($value["column_name"], ['original_documents', 'original_documents_rest', 'original_documents_georgia', 'apostille_documents', 'orignal_document_visa_rest', 'orignal_document_visa_georgia'])) {
                $orignal_documents = [];
                if ($value["column_name"] == "original_documents") {
                    $orignal_documents = array_merge($orignal_documents, get_orignal_document_list());
                }
                if ($value["column_name"] == "original_documents_rest") {
                    $orignal_documents = array_merge($orignal_documents, get_orignal_document_list(1));
                }
                if ($value["column_name"] == "original_documents_georgia") {
                    $orignal_documents = array_merge($orignal_documents, get_orignal_document_list(0, 1));
                }
                if ($value["column_name"] == "apostille_documents") {
                    $orignal_documents = array_merge($orignal_documents, get_orignal_document_list(0, 0, 1));
                }
                if ($value["column_name"] == "orignal_document_visa_rest") {
                    $orignal_documents = array_merge($orignal_documents, get_orignal_document_list(0, 0, 0, '', 1));
                }
                if ($value["column_name"] == "orignal_document_visa_georgia") {
                    $orignal_documents = array_merge($orignal_documents, get_orignal_document_list(0, 0, 0, '', 0, 1));
                }

                if (!empty($orignal_documents)) {
                    foreach ($orignal_documents as $documents) {
                        $short_name = trim($documents['short_name']); // Store short_name safely
                        $safe_column_name = str_replace(" ", "_", $short_name); // Replace spaces with underscores

                        // Check if the column is already added to avoid duplication
                        $queryPart = "MAX(CASE WHEN " . db_prefix() . "orignal_documents.short_name = '" . $short_name . "' 
                                      THEN 'YES' ELSE 'NO' END) AS `" . $safe_column_name . "`";

                        if (!in_array($queryPart, $aColumns)) {
                            $aColumns[] = $queryPart;
                            $aColumns_count++;
                        }
                    }
                }
            }

            continue;
        }

        // Use sql_condition if available
        $column_key = !empty($value["sql_condition"]) ? $value["sql_condition"] : $key;
        // Add column name to array
        $aColumns[] = "$column_key as " . str_replace(" ", "_", strtolower($value["label_name"]));
        $aColumns_count++;
    }
}

if (is_admin() || is_postSale()) {
} else {
    $aColumns[] = $sTable . ".userid as fid";
}
$aColumns[] = db_prefix() . "admission_preferences.primary_university as primary_university_select";



$join = [
    'LEFT JOIN ' . db_prefix() . 'basic_details ON ' . db_prefix() . 'basic_details.userid=' . db_prefix() . 'clients.userid ',
    'LEFT JOIN ' . db_prefix() . 'applicant_status ON ' . db_prefix() . 'applicant_status.id=' . db_prefix() . 'clients.active ',
    ' LEFT JOIN ' . db_prefix() . 'leads ON ' . db_prefix() . 'leads.id = ' . db_prefix() . 'clients.leadid 
AND ' . db_prefix() . 'leads.type IN (' . implode(',', $this->ci->db->escape_str($this->ci->input->post('lead_type'))) . ')',
    'LEFT JOIN ' . db_prefix() . 'staff 
 ON ' . db_prefix() . 'leads.assigned = ' . db_prefix() . 'staff.staffid 
 OR (
 FIND_IN_SET(' . db_prefix() . 'clients.agent_id,' . db_prefix() . 'staff.evp_partners)
     AND ' . db_prefix() . 'staff.staffid = ' . get_staff_user_id() . '
 )',
    'LEFT JOIN ' . db_prefix() . 'leads_status ON ' . db_prefix() . 'leads_status.id = ' . db_prefix() . 'leads.status',
    'LEFT JOIN ' . db_prefix() . 'leads_type ON ' . db_prefix() . 'leads_type.id = ' . db_prefix() . 'leads.type',
    'LEFT JOIN ' . db_prefix() . 'leads_sources ON ' . db_prefix() . 'leads_sources.id = ' . db_prefix() . 'leads.source',
    'LEFT JOIN ' . db_prefix() . 'applicant_tracker ON ' . db_prefix() . 'applicant_tracker.id = (' . db_prefix() . 'clients.applicant_status+1)',
    'LEFT JOIN ' . db_prefix() . 'admission_preferences ON ' . db_prefix() . 'admission_preferences.userid = ' . db_prefix() . 'clients.userid',
    'LEFT JOIN ' . db_prefix() . 'client_university_shortlisting ON ' . db_prefix() . 'client_university_shortlisting.client_id = ' . db_prefix() . 'clients.userid AND ' . db_prefix() . 'client_university_shortlisting.university_name = ' . db_prefix() . 'admission_preferences.primary_university',
    'LEFT JOIN ' . db_prefix() . 'applicant_fees_details ON ' . db_prefix() . 'applicant_fees_details.client_id = ' . db_prefix() . 'clients.userid',
    'LEFT JOIN ' . db_prefix() . 'applicant_fees ON ' . db_prefix() . 'applicant_fees.id = ' . db_prefix() . 'applicant_fees_details.fees_id',
    'LEFT JOIN ' . db_prefix() . 'currencies ON ' . db_prefix() . 'currencies.id = ' . db_prefix() . 'applicant_fees_details.currency_id',
    'LEFT JOIN ' . db_prefix() . 'orignal_document_status ON ' . db_prefix() . 'orignal_document_status.id = ' . db_prefix() . 'clients.orignal_document_status',
    'LEFT JOIN ' . db_prefix() . 'orignal_documents_received ON ' . db_prefix() . 'orignal_documents_received.userid = ' . db_prefix() . 'clients.userid',
    'LEFT JOIN ' . db_prefix() . 'office_location ON ' . db_prefix() . 'office_location.id = ' . db_prefix() . 'orignal_documents_received.location_id',
    'LEFT JOIN ' . db_prefix() . 'orignal_documents ON ' . db_prefix() . 'orignal_documents.id = ' . db_prefix() . 'orignal_documents_received.doc_id',
    'LEFT JOIN ' . db_prefix() . 'applicant_stages stage_category ON stage_category.id = ' . db_prefix() . 'clients.applicant_stage',
    'LEFT JOIN ' . db_prefix() . 'application_sub_category_mbbs  stage_sub_category ON stage_sub_category.id = ' . db_prefix() . 'clients.applicant_sub_status',
    'LEFT JOIN ' . db_prefix() . 'client_passport_details ON ' . db_prefix() . 'client_passport_details.client_id=' . db_prefix() . 'clients.userid',
    'LEFT JOIN ' . db_prefix() . 'passport_stages ON ' . db_prefix() . 'passport_stages.id=' . db_prefix() . 'client_passport_details.passport_status',
    'LEFT JOIN ' . db_prefix() . 'academic_details ON ' . db_prefix() . 'academic_details.userid=' . db_prefix() . 'clients.userid',
    'LEFT JOIN ' . db_prefix() . 'neet_status ON ' . db_prefix() . 'neet_status.id=' . db_prefix() . 'academic_details.neet_status',
    'LEFT JOIN ' . db_prefix() . 'visa_details ON ' . db_prefix() . 'visa_details.userid=' . db_prefix() . 'clients.userid',
    //     'LEFT JOIN (
    //     SELECT *,sum(cost) as total_cost
    //     FROM ' . db_prefix() . 'visa_details vd1
    //     WHERE vd1.id = (
    //         SELECT MAX(vd2.id)
    //         FROM ' . db_prefix() . 'visa_details vd2
    //         WHERE vd2.userid = vd1.userid
    //     )
    // ) AS ' . db_prefix() . 'visa_details ON ' . db_prefix() . 'visa_details.userid = ' . db_prefix() . 'clients.userid',


    'LEFT JOIN ' . db_prefix() . 'visa_status ON ' . db_prefix() . 'visa_status.id=' . db_prefix() . 'visa_details.status',
    'LEFT JOIN ' . db_prefix() . 'vendor_list  visa_vendor ON visa_vendor.id=' . db_prefix() . 'visa_details.vendor_id',
    'LEFT JOIN ' . db_prefix() . 'payment_mode  visa_p_mode ON visa_p_mode.id=' . db_prefix() . 'visa_details.payment_mode',
    'LEFT JOIN ' . db_prefix() . 'ev_partner ev_partner 
 ON ev_partner.id = ' . db_prefix() . 'clients.agent_id 
 OR (
   ' . db_prefix() . 'staff.evp_partners IS NOT NULL 
   AND ' . db_prefix() . 'staff.evp_partners != "" 
    ' . $joinIn . '
 ) ',
    "LEFT JOIN (
        SELECT 
            userid,sum(apostille_cost) as Total_cost,max(courier_date) as courier_date,max(payment_date) as payment_date,GROUP_CONCAT(vendor_id) as vendor_id,
            CASE 
                WHEN COUNT(*) = 0 THEN 'Pending'
                WHEN SUM(received_status = 0) > 0 THEN 'Sent'
                WHEN SUM(received_status = 1) = COUNT(*) THEN 'Received'
                ELSE 'Pending'
            END AS apostille_status
        FROM " . db_prefix() . "client_apostille_data
        GROUP BY userid
    ) AS apostille_summary ON apostille_summary.userid = " . db_prefix() . "clients.userid",
    'LEFT JOIN (
    SELECT td1.*,td2.total_cost
    FROM ' . db_prefix() . 'ticket_data td1
    INNER JOIN (
        SELECT MAX(id) AS max_id,sum(ticket_cost) total_cost
        FROM ' . db_prefix() . 'ticket_data
        GROUP BY client_id
    ) td2 ON td1.id = td2.max_id
) td ON td.client_id = ' . db_prefix() . 'clients.userid',
    'LEFT JOIN ' . db_prefix() . 'ticket_status ts ON ts.id=td.ticket_status',
    'LEFT JOIN ' . db_prefix() . 'vendor_list tc ON tc.id=td.vendor_id',
    'LEFT JOIN ' . db_prefix() . 'payment_mode tm ON tm.id=td.payment_mode',
    'LEFT JOIN ' . db_prefix() . 'departure_location dl ON dl.id=td.departure_location',
    'LEFT JOIN ' . db_prefix() . 'ticket_batch tb ON tb.id=td.batch_id',
    'LEFT JOIN ' . db_prefix() . 'university_partner u_p ON u_p.id=' . db_prefix() . 'client_university_shortlisting.partner'

];

$role = $this->ci->db->where('staffid', get_staff_user_id())->get(db_prefix() . 'staff')->row()->role;
$sids = [];
if ($role == 3) {
    $sid = get_staff_user_id();
    $teamids = $this->ci->db->query('CALL GetReportingPersons(?)', array($sid))->result_array();
    $this->ci->db->close();
    $this->ci->db->initialize();

    $idsarr = array_column($teamids, 'staffid');
    $sids =  $idsarr;
}

// if (!has_permission('customers', '', 'view') && $post_sales->post_sales != 1) {
//     array_push($where, 'AND (' . db_prefix() . 'clients.userid IN (SELECT customer_id FROM ' . db_prefix() . 'customer_admins WHERE staff_id=' . get_staff_user_id() . ')  or ' . db_prefix() . 'leads.assigned = ' . get_staff_user_id() . ')');
// }

// if (!is_admin()) {
//     if (has_permission('customers', '', 'view') && $post_sales->post_sales != 1) {
//         array_push($where, 'AND (' . db_prefix() . 'clients.userid IN (SELECT customer_id FROM ' . db_prefix() . 'customer_admins WHERE staff_id=' . get_staff_user_id() . ')  or ' . db_prefix() . 'leads.assigned IN ( ' . $sids . '))');
//     }
// }

// If user does NOT have 'view' permission and is not in post-sales
$current_staff_id = get_staff_user_id();

if (!has_permission('customers', '', 'view') && isset($post_sales->post_sales) && $post_sales->post_sales != 1) {
    $where[] = 'AND (
        ' . db_prefix() . 'clients.userid IN (
            SELECT customer_id 
            FROM ' . db_prefix() . 'customer_admins 
            WHERE staff_id = ' . $current_staff_id . '
        ) 
        OR ' . db_prefix() . 'leads.assigned = ' . $current_staff_id . '
        OR (  ' . db_prefix() . 'clients.agent_id = ev_partner.id ' . $joinIn . ')
    ) ';
}

if (!is_admin()) {
    if (has_permission('customers', '', 'view') && isset($post_sales->post_sales) && $post_sales->post_sales != 1) {
        if (!empty($sids) && is_array($sids)) {
            $escaped_sids = array_map('intval', $sids);

            $where[] = 'AND (
                ' . db_prefix() . 'clients.userid IN (
                    SELECT customer_id 
                    FROM ' . db_prefix() . 'customer_admins 
                    WHERE staff_id = ' . $current_staff_id . '
                )
                OR ' . db_prefix() . 'leads.assigned IN (' . implode(',', $escaped_sids) . ')
                OR (  ' . db_prefix() . 'clients.agent_id = ev_partner.id ' . $joinIn . ')
            ) ';
        } else {

            if (has_permission('customers', '', 'applicant_view')) {
            } else {
                $where[] = 'AND (
                ' . db_prefix() . 'clients.userid IN (
                    SELECT customer_id 
                    FROM ' . db_prefix() . 'customer_admins 
                    WHERE staff_id = ' . $current_staff_id . '
                )
                
                OR ( ' . db_prefix() . 'clients.agent_id = ev_partner.id ' . $joinIn . ')
            ) ';
            }
        }
    }
}



if ($this->ci->input->post('assigned')) {
    array_push($where, 'AND  ' . db_prefix() . 'leads.assigned IN (' . implode(',', $this->ci->db->escape_str($this->ci->input->post('assigned'))) . ')');
}

if ($this->ci->input->post('source')) {
    array_push($where, 'AND ' . db_prefix() . 'leads.source IN (' . implode(',', $this->ci->db->escape_str($this->ci->input->post('source'))) . ')');
}


if ($this->ci->input->post('neet_status')) {
    $neet_status_input = $this->ci->input->post('neet_status');

    // Ensure input is an array
    if (!is_array($neet_status_input)) {
        $neet_status_input = [$neet_status_input];
    }

    $neet_status_numeric = [];
    $neet_status_string = [];

    foreach ($neet_status_input as $status) {
        if (is_numeric($status)) {
            $neet_status_numeric[] = (int)$status;
        } else {
            $neet_status_string[] = $this->ci->db->escape_str(trim($status)); // Escape strings for safety
        }
    }


    $conditions = [];

    if (!empty($neet_status_numeric)) {
        $conditions[] = db_prefix() . "academic_details.neet_status IN (" . implode(',', $neet_status_numeric) . ")";
    }

    if (!empty($neet_status_string)) {
        $quoted_strings = array_map(function ($val) {
            return "'" . $val . "'";
        }, $neet_status_string);
        $conditions[] = db_prefix() . "academic_details.entrance_result_status IN (" . implode(',', $quoted_strings) . ") and  " . db_prefix() . "academic_details.neet_status = 0";
    }

    if (!empty($conditions)) {
        $where[] = "AND (" . implode(" OR ", $conditions) . ")";
    }
}



if ($this->ci->input->post('office_location_orignal_documents')) {
    $location_ids = $this->ci->input->post('office_location_orignal_documents');

    if (!is_array($location_ids)) {
        $location_ids = [$location_ids];
    }

    // Cast to integers to ensure safety (assuming IDs are numeric)
    $escaped_ids = array_map('intval', $location_ids);

    $where[] = 'AND ' . db_prefix() . 'orignal_documents_received.location_id IN (' . implode(',', $escaped_ids) . ')';
}


if ($this->ci->input->post('lead_type')) {
    array_push($where, 'AND( ' . db_prefix() . 'leads.type IN (' . implode(',', $this->ci->db->escape_str($this->ci->input->post('lead_type'))) . ')  or ' . db_prefix() . 'clients.client_type = 2)');
}

// if (empty($this->ci->input->post('ev_partner_filter'))) {
// array_push($where, ' OR ( ' . db_prefix() . 'leads.type IN (' . implode(',', $this->ci->db->escape_str($this->ci->input->post('lead_type'))) . ')  or ' . db_prefix() . 'clients.client_type = 2)');
// }

if ($this->ci->input->post('apostille_status')) {
    $apostille_status = array_map(function ($status) {
        return "'" . $this->ci->db->escape_str($status) . "'";
    }, $this->ci->input->post('apostille_status'));

    array_push($where, 'AND COALESCE(apostille_summary.apostille_status,"Pending") IN (' . implode(',', $apostille_status) . ')');
}


if ($this->ci->input->post('application_stage')) {
    array_push($where, 'AND ' . db_prefix() . 'clients.applicant_stage = ' . ($this->ci->db->escape_str($this->ci->input->post('application_stage'))));
}



if ($this->ci->input->post('application_sub_stage')) {
    array_push($where, 'AND ' . db_prefix() . 'clients.applicant_sub_status = ' . $this->ci->db->escape($this->ci->input->post('application_sub_stage')));
}

if ($this->ci->input->post('university')) {
    $universities = $this->ci->input->post('university');
    if (is_array($universities)) {
        $escaped_universities = array_map([$this->ci->db, 'escape'], $universities);
        array_push($where, 'AND ' . db_prefix() . 'admission_preferences.primary_university IN (' . implode(',', $escaped_universities) . ')');
    }
}


if ($this->ci->input->post('university_secondary')) {
    $universities = $this->ci->input->post('university_secondary');
    if (is_array($universities)) {
        $escaped_universities = array_map([$this->ci->db, 'escape'], $universities);
        array_push($where, 'AND ' . db_prefix() . 'admission_preferences.primary_university not IN (' . implode(',', $escaped_universities) . ')');
    }
}

if ($this->ci->input->post('university_secondary')) {
    $universities = $this->ci->input->post('university_secondary');

    if (is_array($universities)) {
        $likeConditions = [];

        foreach ($universities as $university) {
            $escapedLike = $this->ci->db->escape_like_str($university);
            $likeConditions[] = db_prefix() . "admission_preferences.university LIKE " .
                $this->ci->db->escape('%' . $escapedLike . '%');
        }

        if (!empty($likeConditions)) {
            $where[] = 'AND (' . implode(' OR ', $likeConditions) . ')';
        }
    }
}



if ($this->ci->input->post('country')) {
    $countries = $this->ci->input->post('country');
    if (is_array($countries)) {
        $escaped_countries = array_map([$this->ci->db, 'escape'], $countries);
        array_push($where, 'AND ' . db_prefix() . 'admission_preferences.primary_country IN (' . implode(',', $escaped_countries) . ')');
    }
}


if (!empty($this->ci->input->post('client_type'))) {
    $client_type = $this->ci->input->post('client_type');
    if (is_array($client_type) &&  !empty(array_filter($client_type))) {
        $escaped_client_type = array_map([$this->ci->db, 'escape'], $client_type);
        array_push($where, 'AND ' . db_prefix() . 'clients.client_type IN (' . implode(',', $escaped_client_type) . ')');
    }
}
if ($this->ci->input->post('status_')) {
    $status = $this->ci->input->post('status_');
    if (is_array($status)) {
        $escaped_status = array_map([$this->ci->db, 'escape'], $status);
        array_push($where, 'AND ' . db_prefix() . 'clients.active IN (' . implode(',', $escaped_status) . ')');
    }
}

if ($this->ci->input->post('minor_status')) {
    $minor = $this->ci->input->post('minor_status');
    array_push($where, "AND ((TIMESTAMPDIFF(YEAR, dob, CURDATE()) < 18 AND 'Yes' = '{$minor}')
    OR (TIMESTAMPDIFF(YEAR, dob, CURDATE()) >= 18 AND 'No' = '{$minor}'))");
}

if ($this->ci->input->post('courier_date')) {
    $courier_date = $this->ci->input->post('courier_date');
    array_push($where, "AND DATE(apostille_summary.courier_date) = '{$courier_date}'");
}


if ($this->ci->input->post('passport_status')) {
    $passport_status = $this->ci->input->post('passport_status');
    if (is_array($passport_status)) {
        $escaped_passport_status = array_map([$this->ci->db, 'escape'], $passport_status);
        array_push($where, 'AND ' . db_prefix() . 'passport_stages.id IN (' . implode(',', $escaped_passport_status) . ')');
    }
}

if ($this->ci->input->post('doc_status')) {
    $doc_status = $this->ci->input->post('doc_status');

    if (is_array($doc_status)) {
        $doc_status_conditions = [];

        // Check if array contains a blank value
        $contains_blank = in_array('-1', $doc_status, true);

        // Remove blank values
        $filtered_doc_status = array_filter($doc_status, function ($value) {
            return $value !== '';
        });

        // Escape remaining values
        $escaped_doc_status = array_map([$this->ci->db, 'escape'], $filtered_doc_status);

        // Add condition for document status IN (values)
        if (!empty($escaped_doc_status)) {
            $doc_status_conditions[] = db_prefix() . "clients.orignal_document_status IN (" . implode(',', $escaped_doc_status) . ")";
        }

        // Add condition if blank exists (i.e., in_transit should not be blank)
        if ($contains_blank) {
            $doc_status_conditions[] = db_prefix() . "orignal_documents_received.in_transit != ''";
        }

        // Decide the operator (AND only if only blank is present)
        $operator = (count($filtered_doc_status) === 0 && $contains_blank) ? 'AND' : 'OR';

        // Merge all conditions with the chosen operator
        if (!empty($doc_status_conditions)) {
            $where[] = 'AND (' . implode(" $operator ", $doc_status_conditions) . ')';
        }
    }
}




if ($this->ci->input->post('apostille_vendors_filter')) {
    $apostille_vendors_filter = $this->ci->input->post('apostille_vendors_filter');

    if (is_array($apostille_vendors_filter)) {
        // Remove empty values
        $apostille_vendors_filter = array_filter($apostille_vendors_filter, function ($v) {
            return $v !== '';
        });

        if (!empty($apostille_vendors_filter)) {
            // Build REGEXP condition dynamically
            $regexp_parts = array_map(function ($v) {
                return '(^|,)' . preg_quote($v, '/') . '(,|$)';
            }, $apostille_vendors_filter);

            $regexp_pattern = implode('|', $regexp_parts);

            array_push($where, "AND apostille_summary.vendor_id IS NOT NULL AND apostille_summary.vendor_id REGEXP " . $this->ci->db->escape($regexp_pattern));
        }
    }
}






if ($this->ci->input->post('visa_vendors_filter')) {
    $visa_vendors_filter = $this->ci->input->post('visa_vendors_filter');

    if (is_array($visa_vendors_filter)) {
        // Remove empty values
        $visa_vendors_filter = array_filter($visa_vendors_filter, function ($v) {
            return $v !== '';
        });

        if (!empty($visa_vendors_filter)) {
            // Build REGEXP condition dynamically
            $regexp_parts = array_map(function ($v) {
                return '(^|,)' . preg_quote($v, '/') . '(,|$)';
            }, $visa_vendors_filter);

            $regexp_pattern = implode('|', $regexp_parts);

            array_push($where, "AND " . db_prefix() . "visa_details.vendor_id IS NOT NULL AND " . db_prefix() . "visa_details.vendor_id REGEXP " . $this->ci->db->escape($regexp_pattern));
        }
    }
}

if ($this->ci->input->post('application_sub_stage')) {
    array_push($where, 'AND ' . db_prefix() . 'clients.applicant_sub_status = ' . $this->ci->db->escape($this->ci->input->post('application_sub_stage')));
}




if ($this->ci->input->post('visa_payment_date')) {
    $visa_payment_date = $this->ci->input->post('visa_payment_date');
    array_push($where, 'AND DATE(' . db_prefix() . 'visa_details.payment_date) BETWEEN "' . $this->ci->db->escape_str($visa_payment_date) . '" AND "' . $this->ci->db->escape_str($visa_payment_date) . '"');
}

if ($this->ci->input->post('to_date')) {
    $from_date = $this->ci->input->post('from_date');
    $to_date = $this->ci->input->post('to_date');
    array_push($where, 'AND DATE(' . db_prefix() . 'clients.datecreated) BETWEEN "' . $this->ci->db->escape_str($from_date) . '" AND "' . $this->ci->db->escape_str($to_date) . '"');
}

if ($this->ci->input->post('session_intake')) {
    $session_intake = $this->ci->input->post('session_intake');
    array_push(
        $where,
        "AND " . db_prefix() . "admission_preferences.session_intake
        = '" . $this->ci->db->escape_str($session_intake) . "'"
    );
}

if ($this->ci->input->post('last_to_date')) {
    $from_date = $this->ci->input->post('last_from_date');
    $to_date = $this->ci->input->post('last_to_date');
    array_push($where, 'AND DATE(' . db_prefix() . 'clients.last_update) BETWEEN "' . $this->ci->db->escape_str($from_date) . '" AND "' . $this->ci->db->escape_str($to_date) . '"');
}


// Ticket 

if ($this->ci->input->post('fly_batch_filter')) {
    $batch_ids = $this->ci->input->post('fly_batch_filter');
    if (is_array($batch_ids)) {
        $escaped_batch_ids = array_map([$this->ci->db, 'escape'], $batch_ids);
        array_push($where, 'AND td.batch_id IN (' . implode(',', $escaped_batch_ids) . ')');
    }
}


if ($this->ci->input->post('fly_departure_filter')) {
    $fly_departure = $this->ci->input->post('fly_departure_filter');
    if (is_array($fly_departure)) {
        $escaped_fly_departure = array_map([$this->ci->db, 'escape'], $fly_departure);
        array_push($where, 'AND td.departure_location IN (' . implode(',', $escaped_fly_departure) . ')');
    }
}


if ($this->ci->input->post('fly_vendors_filter')) {
    $fly_vendors = $this->ci->input->post('fly_vendors_filter');
    if (is_array($fly_vendors)) {
        $escaped_fly_vendors = array_map([$this->ci->db, 'escape'], $fly_vendors);
        array_push($where, 'AND td.vendor_id IN (' . implode(',', $escaped_fly_vendors) . ')');
    }
}

if ($this->ci->input->post('ev_partner_filter')) {
    $ev_partner_filter = $this->ci->input->post('ev_partner_filter');

    if (!empty($ev_partner_filter) && is_array($ev_partner_filter)) {
        $escaped_ev_partner_filter = array_map([$this->ci->db, 'escape'], $ev_partner_filter);
        $where[] = 'AND ' . db_prefix() . 'clients.agent_id IS NOT NULL AND ' . db_prefix() . 'clients.agent_id IN (' . implode(',', $escaped_ev_partner_filter) . ')';
    }
}


if ($this->ci->input->post('fly_date')) {
    $fly_date = $this->ci->input->post('fly_date');
    array_push($where, 'AND DATE(td.fly_date) BETWEEN "' . $this->ci->db->escape_str($fly_date) . '" AND "' . $this->ci->db->escape_str($fly_date) . '"');
}

$additional_array = [
    db_prefix() . 'clients.zip as zip',
    db_prefix() . 'clients.client_type as client_type',
    'registration_confirmed',
    db_prefix() . 'applicant_tracker.name as applicant_stage_name',
    db_prefix() . 'applicant_tracker.id as applicant_stage_id',
    db_prefix() . 'clients.userid as userid',
    db_prefix() . 'clients.active as status_id',
    db_prefix() . 'applicant_status.color as color',
];


if (is_admin() || is_postSale()) {
} else {
    if ($_POST["order"][0]["column"] == 0) {
        $_POST["order"][0]["column"] = count($aColumns);
    }
}

$search_column = [];
// Define search and group-by clauses
if (!empty($_POST["search"]["value"])) {
    $search_column = [
        db_prefix() . "basic_details.email",
        db_prefix() . "basic_details.mobile",
        "CONCAT(" . db_prefix() . "basic_details.first_name, ' ', " . db_prefix() . "basic_details.last_name)"
    ];
}

$result = data_tables_init(array_merge($aColumns, $additional_array), $sIndexColumn, $sTable, $join, $where, [], 'GROUP BY ' . db_prefix() . 'clients.userid', '', '', $search_column);

$output  = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow) {
    $row = [];

    if (!empty($aRow["name"])) {
        if ($aRow["client_type"] ==  2) {
            $company = ($aRow['userid'] ? '<a href="' . admin_url('clients/ev_partner/' . $aRow['userid'] . '?group=tracker') . '" target="_blank">' . $aRow['name'] . '</a>' : '');

            $url = admin_url('clients/ev_partner/' . $aRow['userid']);
        } else {
            $company = ($aRow['userid'] ? '<a href="' . admin_url('clients/client/' . $aRow['userid'] . '?group=tracker') . '" target="_blank">' . $aRow['name'] . '</a>' : '');

            $url = admin_url('clients/client/' . $aRow['userid']);
        }

        // if ($isPerson && $aRow['contact_id']) {
        //     $url .= '?contactid=' . $aRow['contact_id'];
        // }

        $company = '<a href="' . $url . '">' . $company . '</a>';

        $company .= '<div class="row-options">';
        $company .= '<a href="' . $url . '">' . _l('view') . '</a>';
        $company .= ' | <a href="javascript:void(0);" onclick="download_documents(' . $aRow['userid'] . ', \'' . addslashes($aRow['name']) . '\')">' . _l('Download') . '</a>';

        if ($aRow['registration_confirmed'] == 0 && is_admin()) {
            // $company .= ' | <a href="' . admin_url('clients/confirm_registration/' . $aRow['userid']) . '" class="text-success bold">' . _l('confirm_registration') . '</a>';
        }
        if (!$isPerson) {
            // $company .= ' | <a href="' . admin_url('clients/client/' . $aRow['userid'] . '?group=contacts') . '">' . _l('customer_contacts') . '</a>';
        }
        if ($hasPermissionDelete) {
            $company .= ' | <a href="' . admin_url('clients/delete/' . $aRow['userid']) . '" class="text-danger _delete">' . _l('delete') . '</a>';
        }

        $company .= '</div>';

        $aRow["name"] = $company;
    }

    $selection = '<div class="checkbox"><input type="checkbox" value="' . $aRow['userid'] . '"><label></label></div>';

    if (is_admin() || is_postSale()) {
        $aRow["fid"] = $selection;
    }

    if (!empty($aRow["status"])) {
        $color = !empty($aRow['color']) ? $aRow['color'] : 'default';

        $outputStatus = '<span class="inline-block text-' . $color . ' lead-status-' . $aRow['status'] . ' label label-' . $color . '" style="color:' . $color . '; border:1px solid ' . $color . ';">' . $aRow['status'];

        if (!$locked) {
            $outputStatus .= '<div class="dropdown inline-block mleft5 table-export-exclude">';
            $outputStatus .= '<a href="#" style="font-size:14px; vertical-align:middle;" class="dropdown-toggle text-dark" id="tableLeadsStatus-' . $aRow['id'] . '" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">';
            $outputStatus .= '<span data-toggle="tooltip" title="' . _l('ticket_single_change_status') . '"><i class="fa fa-caret-down" aria-hidden="true"></i></span>';
            $outputStatus .= '</a>';

            if ((is_admin() || is_postSale()) && $statuses[$aRow["status_id"]]['refund'] != 1) {
                $outputStatus .= '<ul class="dropdown-menu dropdown-menu-right" aria-labelledby="tableLeadsStatus-' . $aRow['id'] . '">';

                $canceled_status = !empty($statuses[$aRow["status_id"]]['canceled']);
                $refunded_status = !empty($statuses[$aRow["status_id"]]['refund']);

                foreach ($statuses as $leadChangeStatus) {
                    if ($aRow['status_id'] != $leadChangeStatus['id']) {
                        $is_refunded = !empty($leadChangeStatus["refund"]);

                        // If the current status is canceled, allow only refund transitions.
                        if ($canceled_status && $is_refunded) {
                            $outputStatus .= '<li><a onclick="applicant_mark_as(' . $leadChangeStatus['id'] . ',' . $aRow['userid'] . ',' . $leadChangeStatus['canceled'] . ',' . $leadChangeStatus['refund'] . '); return false;">' . $leadChangeStatus['name'] . '</a></li>';
                        }
                        // If the current status is refunded, do nothing.
                        else if (!$refunded_status && !$is_refunded && !$canceled_status) {
                            $outputStatus .= '<li><a onclick="applicant_mark_as(' . $leadChangeStatus['id'] . ',' . $aRow['userid'] . ',' . $leadChangeStatus['canceled'] . ',' . $leadChangeStatus['refund'] . '); return false;">' . $leadChangeStatus['name'] . '</a></li>';
                        }
                    }
                }

                $outputStatus .= '</ul>';
            }

            $outputStatus .= '</div>';
        }

        $outputStatus .= '</span>';
        $aRow["status"] = $outputStatus;
    }


    if (!empty($aRow["secondary_university"])) {
        $primary_university = trim($aRow["primary_university_select"]);
        $secondary_university = json_decode($aRow["secondary_university"], true); // Decode JSON as an associative array

        $filtered_universities = [];

        foreach ($secondary_university as $universities) {
            // Remove primary university and merge the remaining universities into the final array
            $filtered_universities = array_merge($filtered_universities, array_diff(array_map('trim', explode(",", $universities)), [$primary_university]));
        }

        $aRow["secondary_university"] = implode(",", $filtered_universities);
    }


    $row = array_values(array_slice($aRow, 0, $aColumns_count + ((is_admin() || is_postSale()) ? 1 : 0)));

    $output['aaData'][] = $row;
}
