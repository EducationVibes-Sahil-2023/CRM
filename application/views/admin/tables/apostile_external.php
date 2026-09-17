<?php
defined('BASEPATH') or exit('No direct script access allowed');
/**
 * External Apostille Documents table — data_tables_init() pattern
 * (same structure as your visitor_request table file).
 *
 * Query base = your get_apostille_document_data_external():
 *   FROM tblorignal_documents  (docs with apostile_status = 1 / visa_apostile = 1)
 *   LEFT JOIN tbllexternal_client_apostille_data  (so docs with no record
 *   still appear as 'Pending' — same CASE logic as your function)
 *   LEFT JOIN vendor_list / payment_mode / staff
 *
 * Place in:  application/views/admin/tables/table_external_apostille.php
 * Controller (Clients.php):
 *
 *   public function table_external_apostille()
 *   {
 *       if (!$this->input->is_ajax_request() || (!is_postsale() && !is_admin())) {
 *           ajax_access_denied();
 *       }
 *       $this->app->get_table_data('table_external_apostille');
 *   }
 *
 * View init (same call style as your applicant table):
 *
 *   var ExternalApostilleParams = {
 *       "vendor_id":       "[name='filter_vendor_id']",
 *       "received_status": "[name='filter_received_status']",
 *       "payment_mode":    "[name='filter_payment_mode']",
 *       "from_date":       "[name='filter_from_date']",
 *       "to_date":         "[name='filter_to_date']",
 *       "visa_apostile":   "[name='filter_visa_apostile']"
 *   };
 *   apostille_table = initDataTable(
 *       '.table-external-apostille',
 *       admin_url + 'clients/table_external_apostille',
 *       [],
 *       [],
 *       ExternalApostilleParams,
 *       [0, 'ASC']                       // sort by Name
 *   );
 *   $("[name^='filter_']").on('change', function(){
 *       apostille_table.DataTable().ajax.reload();
 *   });
 */

$staff_data          = array_column(get_all_staff(), null, 'staffid');
$has_permission_edit = has_permission('external_apostile', '', 'view_own') || has_permission('external_apostile', '', 'view');
$has_permission_delete = has_permission('external_apostile', '', 'delete');

$R = db_prefix() . 'external_client_apostille_data';
$O = db_prefix() . 'orignal_documents';
$V = db_prefix() . 'vendor_list';
$P = db_prefix() . 'payment_mode';
$S = db_prefix() . 'staff';

// ---------------------------------------------------------------------
// Columns — index order MUST match the <th> order in the view.
// ---------------------------------------------------------------------
$aColumns = [
    $R . '.name as person_name',                                     // 0  Name
    $O . '.name as document_name',                                   // 1  Document Name
    $R . '.apostille_cost as apostille_cost',                        // 2  Cost
    $R . '.currency_text as currency_text',                          // 3  Currency
    $R . '.exchange_rate as exchange_rate',                          // 4  Exchange Rate
    '(' . $R . '.apostille_cost * CAST(NULLIF(' . $R . '.exchange_rate, "") AS DECIMAL(12,4))) as total_amount', // 5 Total Amount
    'CASE
        WHEN ' . $R . '.id IS NULL THEN "Pending"
        WHEN ' . $R . '.received_status = 1 THEN "Received"
        WHEN ' . $R . '.received_status = 0 THEN "Sent"
        ELSE "Pending"
     END as apostille_status',                                       // 6  Status
    $V . '.name as vendor_name',                                     // 7  Vendor
    $R . '.by_vendor as by_vendor',                                  // 8  Apply By Vendor
    $R . '.courier_date as courier_date',                            // 9  Courier Date
    $R . '.apostille_received as apostille_received',                // 10 Receiving Date
    $P . '.name as payment_mode_name',                               // 11 Payment Mode
    $R . '.payment_date as payment_date',                            // 12 Payment Date
    'CONCAT(' . $S . '.firstname, " ", ' . $S . '.lastname) as created_by_name', // 13 Created By
    $R . '.created_at as created_at',                                // 14 Created Date
];

// Extra (non-display) columns needed by the row builder / edit modal
$aColumns = array_merge($aColumns, [
    $R . '.id as rid',
    $R . '.doc_id as doc_id',
    $R . '.vendor_id as vendor_id',
    $R . '.currency_type as currency_type',
    $R . '.payment_mode as payment_mode_id',
    $R . '.received_status as received_status',
]);

$sIndexColumn = 'id';
$sTable       = $O;   // driving table = documents, exactly like your function
$where        = [];
$join         = [];

// ---------------------------------------------------------------------
// Joins — mirror of get_apostille_document_data_external()
// (no client filter on the external page: every record row joins its doc)

array_push($join, 'LEFT JOIN ' . $R . ' ON ' . $O . '.id = ' . $R . '.doc_id');
array_push($join, 'LEFT JOIN ' . $V . ' ON ' . $V . '.id = ' . $R . '.vendor_id');
array_push($join, 'LEFT JOIN ' . $P . ' ON ' . $P . '.id = ' . $R . '.payment_mode');
array_push($join, 'LEFT JOIN ' . $S . ' ON ' . $S . '.staffid = ' . $R . '.created_by');

// ---------------------------------------------------------------------
// Base document scope: apostile_status = 1, optionally OR visa_apostile = 1
// (grouped in ONE condition — the or_where in your function leaks across
//  other filters; parentheses here keep it contained)
// ---------------------------------------------------------------------
// if (!empty($this->ci->input->post('visa_apostile'))) {
//     $where[] = 'AND (' . $O . '.apostile_status = 1 OR ' . $O . '.visa_apostile = 1)';
// } else {
//     $where[] = 'AND ' . $O . '.apostile_status = 1';
// }

$where[] = 'AND ' . $R . '.id IS NOT NULL';

// ---------------------------------------------------------------------
// Filters (posted on every draw by ExternalApostilleParams)
// ---------------------------------------------------------------------
if ($this->ci->input->post('vendor_id') !== null && $this->ci->input->post('vendor_id') !== '') {
    $where[] = 'AND ' . $R . '.vendor_id = ' . (int)$this->ci->input->post('vendor_id');
}

// received_status: '' = all | 'pending' = no record yet | 0 = Sent | 1 = Received
$rs = $this->ci->input->post('received_status');
if ($rs !== null && $rs !== '') {
    if ($rs === 'pending') {
        $where[] = 'AND ' . $R . '.id IS NULL';
    } else {
        $where[] = 'AND ' . $R . '.received_status = ' . (int)$rs;
    }
}

if ($this->ci->input->post('payment_mode') !== null && $this->ci->input->post('payment_mode') !== '') {
    $where[] = 'AND ' . $R . '.payment_mode = ' . (int)$this->ci->input->post('payment_mode');
}

if (!empty($this->ci->input->post('from_date')) && !empty($this->ci->input->post('to_date'))) {
    $from = $this->ci->db->escape_str(to_sql_date($this->ci->input->post('from_date')));
    $to   = $this->ci->db->escape_str(to_sql_date($this->ci->input->post('to_date')));
    $where[] = "AND {$R}.courier_date BETWEEN '{$from}' AND '{$to}'";
}

// ---------------------------------------------------------------------
// Global search box columns
// ---------------------------------------------------------------------
$search_column = [];
if (!empty($_POST['search']['value'])) {
    $search_column = [
        $R . '.name',
        $O . '.name',
        $V . '.name',
    ];
}

// Excel export: dump raw result exactly like your visitor table
if (!empty($this->ci->input->post('excelStatus')) && $this->ci->input->post('excelStatus') == 1) {
    $_POST['order'][0]['column'] = 0;
    $_POST['order'][0]['dir']    = 'desc';
}

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [], '', '', '', $search_column);

if (!empty($this->ci->input->post('excelStatus')) && $this->ci->input->post('excelStatus') == 1) {
    echo json_encode($result['rResult'], true);
    die;
}

$output  = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow) {
    $row = [];

    // ---- Name cell: opens the existing modal in edit mode ----
    $name = !empty($aRow['person_name']) ? html_escape($aRow['person_name']) : '-';
$edit_btn = '';

if ($has_permission_edit && !empty($aRow['rid'])) {

    $encoded = base64_encode(json_encode([
        'name'                => $aRow['person_name'],
        'doc_id'              => $aRow['doc_id'],
        'vendor_id'           => $aRow['vendor_id'],
        'by_vendor'           => $aRow['by_vendor'],
        'courier_date'        => $aRow['courier_date'],
        'apostille_received'  => $aRow['apostille_received'],
        'payment_date'        => $aRow['payment_date'],
        'apostille_cost'      => $aRow['apostille_cost'],
        'currency_type'       => $aRow['currency_type'],
        'exchange_rate'       => $aRow['exchange_rate'],
        'payment_mode_id'     => $aRow['payment_mode_id'],
    ]));

    $edit_btn = "<div class='row-options'>";

    // Edit
    $edit_btn .= '<a href="#"
        data-toggle="modal"
        data-target="#customers_apostille"
        onclick=\'updateApostileData(' . (int)$aRow['rid'] . ', "' . $encoded . '")\'>'
        . _l('edit') . '</a>';

    // Delete (only if delete permission)
   if ($has_permission_delete) {
    $edit_btn .= ' | <a href="javascript:void(0)"
        onclick="deleteApostileRecord(' . (int)$aRow['rid'] . ', \'' . addslashes($name . ' - ' . html_escape($aRow['document_name'])) . '\')"
        class="text-danger">' . _l('delete') . '</a>';
}

    $edit_btn .= '</div>';
}

$name .= $edit_btn;
    $row[] = $name;

    $row[] = html_escape($aRow['document_name']);
    $row[] = $aRow['apostille_cost'] !== null && $aRow['apostille_cost'] !== '' ? $aRow['apostille_cost'] : '-';
    $row[] = !empty($aRow['currency_text']) ? html_escape($aRow['currency_text']) : '-';
    $row[] = !empty($aRow['exchange_rate']) ? html_escape($aRow['exchange_rate']) : '-';
    $row[] = $aRow['total_amount'] !== null ? number_format((float)$aRow['total_amount'], 2) : '-';

    $statusClass = $aRow['apostille_status'] === 'Received' ? 'success'
                 : ($aRow['apostille_status'] === 'Sent' ? 'info' : 'warning');
    $row[] = '<span class="label label-' . $statusClass . '">' . $aRow['apostille_status'] . '</span>';

    $row[] = !empty($aRow['vendor_name']) ? html_escape($aRow['vendor_name']) : '-';
    $row[] = $aRow['by_vendor'] == 1 ? 'Yes' : 'No';
    $row[] = !empty($aRow['courier_date']) ? _d($aRow['courier_date']) : '-';
    $row[] = !empty($aRow['apostille_received']) ? _d($aRow['apostille_received']) : '-';
    $row[] = !empty($aRow['payment_mode_name']) ? html_escape($aRow['payment_mode_name']) : '-';
    $row[] = !empty($aRow['payment_date']) ? _d($aRow['payment_date']) : '-';
    $row[] = !empty($aRow['created_by_name']) ? html_escape($aRow['created_by_name'])
            : (!empty($staff_data[$aRow['created_by'] ?? 0]['full_name']) ? $staff_data[$aRow['created_by']]['full_name'] : '-');
    $row[] = !empty($aRow['created_at']) ? _dt($aRow['created_at']) : '-';

    $row['DT_RowClass'] = 'has-row-options';
    $output['aaData'][] = $row;
}