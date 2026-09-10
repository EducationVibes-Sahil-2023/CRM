<?php
defined('BASEPATH') or exit('No direct script access allowed');
/**
 * Applicant documents — server-side DataTables endpoint (v5).
 *
 * v5 CHANGE — JSON OBJECT vs ARRAY normalization:
 *
 *   Some rows in tblclient_documents.data are stored as a JSON ARRAY:
 *       [{"id":1,...},{"id":6,...}]
 *   and others as a JSON OBJECT keyed by document id:
 *       {"1":{"id":"1",...},"6":{"id":6,...}}
 *
 *   JSON_TABLE's '$[*]' row path only iterates ARRAY elements. When it hits
 *   an OBJECT, MySQL auto-wraps the whole object as one element, so $.id
 *   resolves to NULL and those students' documents silently disappear.
 *
 *   Fix: normalize the value BEFORE it reaches JSON_TABLE. If it's an
 *   object, JSON_EXTRACT(..., '$.*') collects the member values into an
 *   array; arrays pass through untouched; empty/invalid data becomes
 *   JSON_ARRAY() so nothing errors. Applied in BOTH JSON_TABLE call sites
 *   via the shared $normalizeJson() helper below.
 *
 * (Earlier fixes retained: DISTINCT counts, EXISTS instead of multiplying
 *  joins, no outer WHERE on the LEFT-joined side, sanitized client_type.)
 */

// ---------- JSON normalizer (object -> array of values) ----------
// Builds the SQL expression that feeds JSON_TABLE for a given column.
$normalizeJson = function ($col) {
    $cast = "CAST($col AS CHAR CHARACTER SET utf8mb4)";
    return "
        CASE
            WHEN $col IS NULL
              OR $cast = ''
              OR NOT JSON_VALID($cast) THEN JSON_ARRAY()
            WHEN JSON_TYPE($cast) = 'OBJECT'
              THEN COALESCE(JSON_EXTRACT($cast, '$.*'), JSON_ARRAY())
            ELSE $cast
        END";
};

// ---------- Document types = columns ----------
$selectedDocs = $this->ci->input->post('documents');
if (!empty($selectedDocs) && is_array($selectedDocs)) {
    $ids   = implode(',', array_map('intval', $selectedDocs));
    $types = $this->ci->db->query("
        SELECT id, name FROM tbldocument_upload_type
        WHERE lead_type = 2 AND id IN ($ids)
        ORDER BY id
    ")->result_array();
} else {
    $types = $this->ci->db->query("
        SELECT id, name FROM tbldocument_upload_type
        WHERE lead_type = 2
        ORDER BY id
        LIMIT 5
    ")->result_array();
}

// ---------- Aliases + pivot columns ----------
$aliases   = [];
$selectDoc = [];
foreach ($types as $type) {
    $alias = trim(strtolower(preg_replace('/[^a-zA-Z0-9]+/', '_', $type['name'])), '_');
    $id    = (int)$type['id'];
    $aliases[$id] = $alias;
    // Show the file link ONLY when the document is approved
    $selectDoc[] = "MAX(CASE WHEN jt.id = $id AND jt.approval_status = 1
                        THEN CONCAT('" . base_url() . "', jt.document_file)
                    END) AS `$alias`";
    $selectDoc[] = "MAX(CASE WHEN jt.id = $id
                        THEN jt.approval_status
                    END) AS `{$alias}_status`";
}
$docTypeCsv = implode(',', array_map('intval', array_keys($aliases)));

// ---------- Student-level filter: has >=1 APPROVED doc among selected types ----------
$existsApproved = '';
if ($docTypeCsv !== '') {
    // v5: JSON_TABLE now reads the NORMALIZED expression, so object-shaped
    // rows ({"1":{...},"6":{...}}) match exactly like array-shaped rows.
    // $existsApproved = "
    // AND EXISTS (
    //     SELECT 1
    //     FROM tblclient_documents cd2
    //     JOIN JSON_TABLE(
    //         " . $normalizeJson('cd2.data') . ",
    //         '$[*]'
    //         COLUMNS(
    //             id              INT PATH '$.id',
    //             approval_status INT PATH '$.approval_status'
    //         )
    //     ) j2 ON TRUE
    //     WHERE cd2.client_id = c.userid
    //       AND j2.approval_status = 1
    //       AND j2.id IN ($docTypeCsv)
    // )";
}

// ---------- Extra filters ----------
$clientType = "1,2";
if ($this->ci->input->post('client_type')) {
    $ct = (array)$this->ci->input->post('client_type');
    $clientType = implode(',', array_map('intval', $ct));   // sanitized
    if ($clientType === '') { $clientType = "1,2"; }
}
$where = "";
if ($this->ci->input->post('acadmic_year')) {
    $acadmic_year    = (int)$this->ci->input->post('acadmic_year');
    $first_semester  = $acadmic_year . "-09";
    $second_semester = ($acadmic_year + 1) . "-02";
    $where .= " AND (p.session_intake IN ('{$first_semester}','{$second_semester}') OR p.session_intake IS NULL) ";
}
if ($this->ci->input->post('university')) {
    $universities = $this->ci->input->post('university');
    if (is_array($universities)) {
        $escaped = array_map([$this->ci->db, 'escape'], $universities);
        $where .= ' AND p.primary_university IN (' . implode(',', $escaped) . ') ';
    }
}
if ($this->ci->input->post('country')) {
    $countries = $this->ci->input->post('country');
    if (is_array($countries)) {
        $escaped = array_map([$this->ci->db, 'escape'], $countries);
        $where .= ' AND p.primary_country IN (' . implode(',', $escaped) . ') ';
    }
}

if ($this->ci->input->post('statuses')) {
    $status = $this->ci->input->post('statuses');
    if (is_array($status)) {
        $escaped = array_map([$this->ci->db, 'escape'], $status);
        $where .= ' AND c.active IN (' . implode(',', $escaped) . ') ';
    }
}
if ($this->ci->input->post('view_application_stage')) {
     $view_application_stage = $this->ci->input->post('view_application_stage');
    if (!empty($view_application_stage)) {
        $where .= ' AND c.applicant_stage IN (' . $view_application_stage . ') ';
    }
}

if ($this->ci->input->post('view_application_sub_stage')) {
    $view_application_sub_stage = $this->ci->input->post('view_application_sub_stage');
    if (!empty($view_application_sub_stage)) {
        $where .= ' AND c.applicant_sub_status IN (' .$view_application_sub_stage. ') ';
    }
}
// Shortlisting: no columns used from it -> EXISTS, so it can't multiply rows
// $existsShortlist = "
//     AND EXISTS (
//         SELECT 1 FROM tblclient_university_shortlisting us
//         WHERE us.client_id = c.userid
//     )";

// ---------- Pagination + search ----------
$start  = (int)$this->ci->input->post('start');
$length = (int)$this->ci->input->post('length');
if ($length <= 0 || $length > 500) { $length = 10; }
$searchVal   = $this->ci->input->post('search')['value'] ?? '';
$whereSearch = '';
if ($searchVal !== '') {
    $like = $this->ci->db->escape_like_str($searchVal);
    $whereSearch = " AND CONCAT(bd.first_name, ' ', bd.last_name) LIKE '%$like%' ";
}

// ---------- Shared FROM/WHERE so counts and page rows CANNOT diverge ----------
$fromWhere = "
    FROM tblclients c
    left JOIN tblleads l ON l.id = c.leadid
     JOIN tblbasic_details bd ON bd.userid = c.userid
     JOIN tbladmission_preferences p ON p.userid = c.userid
     JOIN tblapplicant_status  ss ON ss.id=c.active
     JOIN tblapplicant_stages stage_category ON stage_category.id = c.applicant_stage
     JOIN tblapplication_sub_category_mbbs  stage_sub_category ON stage_sub_category.id = c.applicant_sub_status
    LEFT JOIN tbladmission_preferences ap ON ap.userid = c.userid
   WHERE (
    (
        (l.id != '' AND l.type = 2)
        OR l.id IS NULL
    )
    AND c.client_type IN ($clientType)
)
    $where
    $existsShortlist
    $existsApproved
";


// ---------- Counts (DISTINCT fixes the multiplied totals) ----------
$total = (int)$this->ci->db->query("
    SELECT COUNT(DISTINCT c.userid) AS cnt $fromWhere
")->row()->cnt;
$filtered = $total;
if ($whereSearch !== '') {
    $filtered = (int)$this->ci->db->query("
        SELECT COUNT(DISTINCT c.userid) AS cnt $fromWhere $whereSearch
    ")->row()->cnt;
}

// ---------- Main query ----------
// Subquery: GROUP BY userid so one student = one page slot, then LIMIT.
// Outer LEFT joins only decorate; NO WHERE outside the subquery.
// v5: the decorating JSON_TABLE also reads the NORMALIZED expression.
 $sql = "
SELECT
    s.userid,
    s.student_name,
    status_name,
    client_type,
    primary_country,
    primary_university,
    stage_name,
    sub_stage_name" .
    (empty($selectDoc) ? "" : ",\n    " . implode(",\n    ", $selectDoc)) . "
FROM (
    SELECT c.userid,
           CONCAT(bd.first_name, ' ', bd.last_name) AS student_name,
           ss.name as status_name,
           if(c.client_type =1,'EV','EVP') client_type,
           ap.primary_country primary_country,
           ap.primary_university primary_university,
           stage_category.name stage_name,
           stage_sub_category.name sub_stage_name
           
           
    $fromWhere
    $whereSearch
    GROUP BY c.userid, student_name
    ORDER BY student_name
    LIMIT $start, $length
) s
LEFT JOIN tblclient_documents cd
    ON cd.client_id = s.userid
   AND cd.data IS NOT NULL
   AND cd.data <> ''
LEFT JOIN JSON_TABLE(
    " . $normalizeJson('cd.data') . ",
    '$[*]'
    COLUMNS(
        id              INT          PATH '$.id',
        document_file   VARCHAR(500) PATH '$.document_file',
        approval_status INT          PATH '$.approval_status'
    )
) jt ON TRUE
GROUP BY s.userid, s.student_name
ORDER BY s.student_name
";

// Debug: open the ajax URL with ?debug_sql=1 to inspect the generated SQL
if ($this->ci->input->get('debug_sql')) {
    header('Content-Type: text/plain');
    echo $sql;
    exit;
}
$rResult = $this->ci->db->query($sql)->result_array();

// ---------- Build DataTables response ----------
$output = [
    'draw'                 => (int)$this->ci->input->post('draw'),
    'iTotalRecords'        => $total,
    'iTotalDisplayRecords' => $filtered,
    'aaData'               => [],
];
foreach ($rResult as $aRow) {
    $row = [];
    $row[] = '<div class="checkbox"><input type="checkbox" value="' . $aRow['userid'] . '"><label></label></div>';
    $row[] = $aRow['student_name'];
    $row[] = ' <span class="inline-block text-">'.$aRow['status_name'].'</span>';
    $row[] = $aRow['client_type'];
    $row[] = $aRow['primary_university'];
    $row[] = $aRow['primary_country'];
    $row[] = $aRow['stage_name'];
    $row[] = $aRow['sub_stage_name'];
   
    foreach ($aliases as $alias) {
        $url    = $aRow[$alias] ?? '';
        $status = $aRow[$alias . '_status'] ?? null;
        if (!empty($url)) {
            if ($status == 1) {
                $icon = '<i class="fa fa-check-circle text-success" title="Approved"></i>';
            } elseif ($status == 2) {
                $icon = '<i class="fa fa-times-circle text-danger" title="Rejected"></i>';
            } else {
                $icon = '<i class="fa fa-clock-o text-warning" title="Pending"></i>';
            }
            $row[] = '<div class="text-center">
                        <a href="' . $url . '" target="_blank" title="View"><i class="fa fa-eye text-primary"></i></a>
                        &nbsp;
                        <a href="' . $url . '" download title="Download"><i class="fa fa-download text-success"></i></a>
                        &nbsp;' . $icon . '
                      </div>';
        } elseif ($status !== null) {
            $icon = ($status == 2)
                ? '<i class="fa fa-times-circle text-danger" title="Rejected"></i>'
                : '<i class="fa fa-clock-o text-warning" title="Pending"></i>';
            $row[] = '<div class="text-center">' . $icon . '</div>';
        } else {
            $row[] = '-';
        }
    }
    $output['aaData'][] = $row;
}
echo json_encode($output);
exit;