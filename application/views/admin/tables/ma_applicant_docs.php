 <?php
// defined('BASEPATH') or exit('No direct script access allowed');
// /**
//  * Applicant documents — server-side DataTables endpoint (v5).
//  *
//  * v5 CHANGE — JSON OBJECT vs ARRAY normalization:
//  *
//  *   Some rows in tblclient_documents.data are stored as a JSON ARRAY:
//  *       [{"id":1,...},{"id":6,...}]
//  *   and others as a JSON OBJECT keyed by document id:
//  *       {"1":{"id":"1",...},"6":{"id":6,...}}
//  *
//  *   JSON_TABLE's '$[*]' row path only iterates ARRAY elements. When it hits
//  *   an OBJECT, MySQL auto-wraps the whole object as one element, so $.id
//  *   resolves to NULL and those students' documents silently disappear.
//  *
//  *   Fix: normalize the value BEFORE it reaches JSON_TABLE. If it's an
//  *   object, JSON_EXTRACT(..., '$.*') collects the member values into an
//  *   array; arrays pass through untouched; empty/invalid data becomes
//  *   JSON_ARRAY() so nothing errors. Applied in BOTH JSON_TABLE call sites
//  *   via the shared $normalizeJson() helper below.
//  *
//  * (Earlier fixes retained: DISTINCT counts, EXISTS instead of multiplying
//  *  joins, no outer WHERE on the LEFT-joined side, sanitized client_type.)
//  */

// // ---------- JSON normalizer (object -> array of values) ----------
// // Builds the SQL expression that feeds JSON_TABLE for a given column.
// $normalizeJson = function ($col) {
//     $cast = "CAST($col AS CHAR CHARACTER SET utf8mb4)";
//     return "
//         CASE
//             WHEN $col IS NULL
//               OR $cast = ''
//               OR NOT JSON_VALID($cast) THEN JSON_ARRAY()
//             WHEN JSON_TYPE($cast) = 'OBJECT'
//               THEN COALESCE(JSON_EXTRACT($cast, '$.*'), JSON_ARRAY())
//             ELSE $cast
//         END";
// };

// // ---------- Document types = columns ----------
// $selectedDocs = $this->ci->input->post('documents');
// if (!empty($selectedDocs) && is_array($selectedDocs)) {
//     $ids   = implode(',', array_map('intval', $selectedDocs));
//     $types = $this->ci->db->query("
//         SELECT id, name FROM tbldocument_upload_type
//         WHERE lead_type = 2 AND id IN ($ids)
//         ORDER BY id
//     ")->result_array();
// } else {
//     $types = $this->ci->db->query("
//         SELECT id, name FROM tbldocument_upload_type
//         WHERE lead_type = 2
//         ORDER BY id
//         LIMIT 5
//     ")->result_array();
// }

// // ---------- Aliases + pivot columns ----------
// $aliases   = [];
// $selectDoc = [];
// foreach ($types as $type) {
//     $alias = trim(strtolower(preg_replace('/[^a-zA-Z0-9]+/', '_', $type['name'])), '_');
//     $id    = (int)$type['id'];
//     $aliases[$id] = $alias;
//     // Show the file link ONLY when the document is approved
//     $selectDoc[] = "MAX(CASE WHEN jt.id = $id AND jt.approval_status = 1
//                         THEN CONCAT('" . base_url() . "', jt.document_file)
//                     END) AS `$alias`";
//     $selectDoc[] = "MAX(CASE WHEN jt.id = $id
//                         THEN jt.approval_status
//                     END) AS `{$alias}_status`";
// }
// $docTypeCsv = implode(',', array_map('intval', array_keys($aliases)));

// // ---------- Student-level filter: has >=1 APPROVED doc among selected types ----------
// $existsApproved = '';
// if ($docTypeCsv !== '') {
//     // v5: JSON_TABLE now reads the NORMALIZED expression, so object-shaped
//     // rows ({"1":{...},"6":{...}}) match exactly like array-shaped rows.
//     // $existsApproved = "
//     // AND EXISTS (
//     //     SELECT 1
//     //     FROM tblclient_documents cd2
//     //     JOIN JSON_TABLE(
//     //         " . $normalizeJson('cd2.data') . ",
//     //         '$[*]'
//     //         COLUMNS(
//     //             id              INT PATH '$.id',
//     //             approval_status INT PATH '$.approval_status'
//     //         )
//     //     ) j2 ON TRUE
//     //     WHERE cd2.client_id = c.userid
//     //       AND j2.approval_status = 1
//     //       AND j2.id IN ($docTypeCsv)
//     // )";
// }

// // ---------- Extra filters ----------
// $clientType = "1,2";
// if ($this->ci->input->post('client_type')) {
//     $ct = (array)$this->ci->input->post('client_type');
//     $clientType = implode(',', array_map('intval', $ct));   // sanitized
//     if ($clientType === '') { $clientType = "1,2"; }
// }
// $where = "";
// if ($this->ci->input->post('acadmic_year')) {
//     $acadmic_year    = (int)$this->ci->input->post('acadmic_year');
//     $first_semester  = $acadmic_year . "-09";
//     $second_semester = ($acadmic_year + 1) . "-02";
//     $where .= " AND (p.session_intake IN ('{$first_semester}','{$second_semester}') OR p.session_intake IS NULL) ";
// }
// if ($this->ci->input->post('university')) {
//     $universities = $this->ci->input->post('university');
//     if (is_array($universities)) {
//         $escaped = array_map([$this->ci->db, 'escape'], $universities);
//         $where .= ' AND p.primary_university IN (' . implode(',', $escaped) . ') ';
//     }
// }
// if ($this->ci->input->post('country')) {
//     $countries = $this->ci->input->post('country');
//     if (is_array($countries)) {
//         $escaped = array_map([$this->ci->db, 'escape'], $countries);
//         $where .= ' AND p.primary_country IN (' . implode(',', $escaped) . ') ';
//     }
// }

// if ($this->ci->input->post('statuses')) {
//     $status = $this->ci->input->post('statuses');
//     if (is_array($status)) {
//         $escaped = array_map([$this->ci->db, 'escape'], $status);
//         $where .= ' AND c.active IN (' . implode(',', $escaped) . ') ';
//     }
// }
// if ($this->ci->input->post('view_application_stage')) {
//      $view_application_stage = $this->ci->input->post('view_application_stage');
//     if (!empty($view_application_stage)) {
//         $where .= ' AND c.applicant_stage IN (' . $view_application_stage . ') ';
//     }
// }

// if ($this->ci->input->post('view_application_sub_stage')) {
//     $view_application_sub_stage = $this->ci->input->post('view_application_sub_stage');
//     if (!empty($view_application_sub_stage)) {
//         $where .= ' AND c.applicant_sub_status IN (' .$view_application_sub_stage. ') ';
//     }
// }
// // Shortlisting: no columns used from it -> EXISTS, so it can't multiply rows
// // $existsShortlist = "
// //     AND EXISTS (
// //         SELECT 1 FROM tblclient_university_shortlisting us
// //         WHERE us.client_id = c.userid
// //     )";

// // ---------- Pagination + search ----------
// $start  = (int)$this->ci->input->post('start');
// $length = (int)$this->ci->input->post('length');
// if ($length <= 0 || $length > 500) { $length = 10; }
// $searchVal   = $this->ci->input->post('search')['value'] ?? '';
// $whereSearch = '';

// // if ($searchVal !== '') {
// //     $like = $this->ci->db->escape_like_str($searchVal);
// //     $whereSearch = " AND CONCAT(bd.first_name, ' ', bd.last_name) LIKE '%$like%' ";
// // }


// if (!empty($_POST["search"]["value"])) {

//     $search_value = trim($_POST["search"]["value"]);

//     if (strpos($search_value, ',') !== false) {

//         // Comma-separated values
//         $searchValues = array_filter(
//             array_map('trim', explode(',', $search_value))
//         );

//         $_POST["search"]["value"] = implode(",", $searchValues);

//         $conditions = [];

//         foreach ($searchValues as $value) {
//             $like = $this->ci->db->escape_like_str($value);

//             $conditions[] = "CONCAT(
//                 bd.first_name,
//                 ' ',
//                 bd.last_name
//             ) LIKE '%$like%'";
//         }

//         $whereSearch = " AND (" . implode(" OR ", $conditions) . ") ";

//     } else {

//         // Single search value
//         $like = $this->ci->db->escape_like_str($search_value);

//         $whereSearch = " AND CONCAT(
//             bd.first_name,
//             ' ',
//             bd.last_name
//         ) LIKE '%$like%' ";
//     }
// }

// // ---------- Shared FROM/WHERE so counts and page rows CANNOT diverge ----------
// $fromWhere = "
//     FROM tblclients c
//     left JOIN tblleads l ON l.id = c.leadid
//      JOIN tblbasic_details bd ON bd.userid = c.userid
//      JOIN tbladmission_preferences p ON p.userid = c.userid
//      JOIN tblapplicant_status  ss ON ss.id=c.active
//      JOIN tblapplicant_stages stage_category ON stage_category.id = c.applicant_stage
//      JOIN tblapplication_sub_category_mbbs  stage_sub_category ON stage_sub_category.id = c.applicant_sub_status
//     LEFT JOIN tbladmission_preferences ap ON ap.userid = c.userid
//   WHERE (
//     (
//         (l.id != '' AND l.type = 2)
//         OR l.id IS NULL
//     )
//     AND c.client_type IN ($clientType)
// )
//     $where
//     $existsShortlist
//     $existsApproved
// ";


// // ---------- Counts (DISTINCT fixes the multiplied totals) ----------
// $total = (int)$this->ci->db->query("
//     SELECT COUNT(DISTINCT c.userid) AS cnt $fromWhere
// ")->row()->cnt;
// $filtered = $total;
// if ($whereSearch !== '') {
//     $filtered = (int)$this->ci->db->query("
//         SELECT COUNT(DISTINCT c.userid) AS cnt $fromWhere $whereSearch
//     ")->row()->cnt;
// }

// // ---------- Main query ----------
// // Subquery: GROUP BY userid so one student = one page slot, then LIMIT.
// // Outer LEFT joins only decorate; NO WHERE outside the subquery.
// // v5: the decorating JSON_TABLE also reads the NORMALIZED expression.
//  $sql = "
// SELECT
//     s.userid,
//     s.student_name,
//     status_name,
//     client_type,
//     primary_country,
//     primary_university,
//     stage_name,
//     sub_stage_name" .
//     (empty($selectDoc) ? "" : ",\n    " . implode(",\n    ", $selectDoc)) . "
// FROM (
//     SELECT c.userid,
//           CONCAT(bd.first_name, ' ', bd.last_name) AS student_name,
//           ss.name as status_name,
//           if(c.client_type =1,'EV','EVP') client_type,
//           ap.primary_country primary_country,
//           ap.primary_university primary_university,
//           stage_category.name stage_name,
//           stage_sub_category.name sub_stage_name
           
           
//     $fromWhere
//     $whereSearch
//     GROUP BY c.userid, student_name
//     ORDER BY student_name
//     LIMIT $start, $length
// ) s
// LEFT JOIN tblclient_documents cd
//     ON cd.client_id = s.userid
//   AND cd.data IS NOT NULL
//   AND cd.data <> ''
// LEFT JOIN JSON_TABLE(
//     " . $normalizeJson('cd.data') . ",
//     '$[*]'
//     COLUMNS(
//         id              INT          PATH '$.id',
//         document_file   VARCHAR(500) PATH '$.document_file',
//         approval_status INT          PATH '$.approval_status'
//     )
// ) jt ON TRUE
// GROUP BY s.userid, s.student_name
// ORDER BY s.student_name
// ";

// // Debug: open the ajax URL with ?debug_sql=1 to inspect the generated SQL
// if ($this->ci->input->get('debug_sql')) {
//     header('Content-Type: text/plain');
//     echo $sql;
//     exit;
// }
// $rResult = $this->ci->db->query($sql)->result_array();

// // ---------- Build DataTables response ----------
// $output = [
//     'draw'                 => (int)$this->ci->input->post('draw'),
//     'iTotalRecords'        => $total,
//     'iTotalDisplayRecords' => $filtered,
//     'aaData'               => [],
// ];
// foreach ($rResult as $aRow) {
//     $row = [];
//     $row[] = '<div class="checkbox"><input type="checkbox" value="' . $aRow['userid'] . '"><label></label></div>';
//     $row[] = $aRow['student_name'];
//     $row[] = ' <span class="inline-block text-">'.$aRow['status_name'].'</span>';
//     $row[] = $aRow['client_type'];
//     $row[] = $aRow['primary_university'];
//     $row[] = $aRow['primary_country'];
//     $row[] = $aRow['stage_name'];
//     $row[] = $aRow['sub_stage_name'];
   
//     foreach ($aliases as $alias) {
//         $url    = $aRow[$alias] ?? '';
//         $status = $aRow[$alias . '_status'] ?? null;
//         if (!empty($url)) {
//             if ($status == 1) {
//                 $icon = '<i class="fa fa-check-circle text-success" title="Approved"></i>';
//             } elseif ($status == 2) {
//                 $icon = '<i class="fa fa-times-circle text-danger" title="Rejected"></i>';
//             } else {
//                 $icon = '<i class="fa fa-clock-o text-warning" title="Pending"></i>';
//             }
//             $row[] = '<div class="text-center">
//                         <a href="' . $url . '" target="_blank" title="View"><i class="fa fa-eye text-primary"></i></a>
//                         &nbsp;
//                         <a href="' . $url . '" download title="Download"><i class="fa fa-download text-success"></i></a>
//                         &nbsp;' . $icon . '
//                       </div>';
//         } elseif ($status !== null) {
//             $icon = ($status == 2)
//                 ? '<i class="fa fa-times-circle text-danger" title="Rejected"></i>'
//                 : '<i class="fa fa-clock-o text-warning" title="Pending"></i>';
//             $row[] = '<div class="text-center">' . $icon . '</div>';
//         } else {
//             $row[] = '-';
//         }
//     }
//     $output['aaData'][] = $row;
// }
// echo json_encode($output);
// exit;


defined('BASEPATH') or exit('No direct script access allowed');
/**
 * Applicant documents — server-side DataTables endpoint (v6).
 *
 * v6 CHANGE — MIXED int / string ids inside the JSON:
 *
 *   tblclient_documents.data holds the document id in both shapes:
 *       {"id":6,   ...}    <- JSON number
 *       {"id":"1", ...}    <- JSON string
 *
 *   JSON_TABLE with `id INT PATH '$.id'` only converts the NUMBER form.
 *   For the string form MySQL raises a conversion warning and returns NULL,
 *   so every document whose id was stored as a string vanished from the grid.
 *   The same applies to approval_status ("1" vs 1).
 *
 *   Fix: pull both fields out as VARCHAR — JSON_TABLE renders a number as
 *   its text form ("6") and unquotes a JSON string ("1") — so the two
 *   shapes land on the same value. Every comparison is then done against a
 *   quoted literal ('6', '1') built with $db->escape().
 *
 * v5 (kept) — object vs array JSON: $normalizeJson() turns an object-shaped
 *   value into an array of its members so JSON_TABLE's '$[*]' can iterate it.
 *
 * Also fixed here:
 *   - $existsShortlist was used in the query but never defined (PHP notice).
 *   - applicant_stage / applicant_sub_status were concatenated into SQL raw
 *     (SQL injection); both are now cast to an int list.
 *   - outer GROUP BY lists every selected column, so the query also runs
 *     under ONLY_FULL_GROUP_BY.
 */

// ---------- JSON normalizer (object -> array of values) ----------
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

// ---------- list helpers ----------
// The pickers post EITHER an array ["1","6"] OR a plain string "1,6" (or "6").
// Both helpers accept either shape and return '' when there is nothing usable.

// Quoted, escaped CSV: 1,6 -> '1','6'   (works for int and string ids alike)
$db = $this->ci->db;
$quotedCsv = function ($value) use ($db) {
    if ($value === null || $value === '' || $value === []) {
        return '';
    }
    if (!is_array($value)) {
        $value = explode(',', (string)$value);
    }
    $out  = [];
    $seen = [];
    foreach ($value as $v) {
        $v = trim((string)$v);
        if ($v === '' || isset($seen[$v])) { continue; }   // skip blanks + duplicates
        $seen[$v] = true;
        $out[] = $db->escape($v);
    }
    return implode(',', $out);
};

// ---------- int-list helper ----------
// Accepts an array or a "1,2,3" string and returns a safe int CSV, or ''
// when there is nothing usable so the caller can skip the filter.
$intCsv = function ($value) {
    if ($value === null || $value === '' || $value === []) {
        return '';
    }
    if (!is_array($value)) {
        $value = explode(',', (string)$value);
    }
    $ints = [];
    foreach ($value as $v) {
        if (trim((string)$v) === '') { continue; }
        $ints[] = (int)$v;
    }
    return implode(',', $ints);
};

// ---------- JSON_TABLE columns ----------
// v6: id and approval_status are VARCHAR, never INT, so a JSON string and a
// JSON number both come out as plain text and compare the same way.
// (Alternative if you ever prefer numbers: CAST(jt.id AS UNSIGNED) = 6.)
$jsonColumns = "
        COLUMNS(
            id              VARCHAR(64)  PATH '$.id',
            document_file   VARCHAR(500) PATH '$.document_file',
            approval_status VARCHAR(16)  PATH '$.approval_status'
        )";

// ---------- Document types = columns ----------
$selectedDocs = $this->ci->input->post('documents');

$aditionSelect = array_values(
    array_filter($selectedDocs, fn($value) => is_string($value) && !is_numeric($value))
);
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
$docIdList = [];      
// quoted ids: '1','6'
foreach ($types as $type) {

    $alias = trim(strtolower(preg_replace('/[^a-zA-Z0-9]+/', '_', $type['name'])), '_');
    $id    = (string)$type['id'];      // v6: keep the id as a string
    $idSql = $this->ci->db->escape($id);   // -> '6'

    $aliases[$id] = $alias;
    $docIdList[]  = $idSql;

    // Show the file link ONLY when the document is approved.
    $selectDoc[] = "MAX(CASE WHEN jt.id = $idSql AND jt.approval_status = '1'
                        THEN CONCAT('" . base_url() . "', jt.document_file)
                    END) AS `$alias`";
    $selectDoc[] = "MAX(CASE WHEN jt.id = $idSql
                        THEN jt.approval_status
                    END) AS `{$alias}_status`";
}


foreach ($aditionSelect as $adoc)
{
  
     $alias = trim(strtolower(preg_replace('/[^a-zA-Z0-9]+/', '_', $adoc)), '_');

     $aliases[$adoc] = $alias;
     $selectDoc[] = $adoc;
     $docIdList[]  = $adoc;
}




$docTypeCsv = implode(',', $docIdList);

// ---------- Student-level filter: has >=1 APPROVED doc among selected types ----------
$existsApproved = '';
if ($docTypeCsv !== '') {
    // Enable this block to hide students with no approved document.
    // v6: j2.id / j2.approval_status are VARCHAR, compared as strings.
    // $existsApproved = "
    // AND EXISTS (
    //     SELECT 1
    //     FROM tblclient_documents cd2
    //     JOIN JSON_TABLE(
    //         " . $normalizeJson('cd2.data') . ",
    //         '$[*]'
    //         COLUMNS(
    //             id              VARCHAR(64) PATH '$.id',
    //             approval_status VARCHAR(16) PATH '$.approval_status'
    //         )
    //     ) j2 ON TRUE
    //     WHERE cd2.client_id = c.userid
    //       AND j2.approval_status = '1'
    //       AND j2.id IN ($docTypeCsv)
    // )";
}

// Shortlisting filter — no columns used from it, so EXISTS keeps rows unique.
// Must always be defined, even when switched off, or the query string below
// interpolates an undefined variable.
$existsShortlist = '';
// $existsShortlist = "
//     AND EXISTS (
//         SELECT 1 FROM tblclient_university_shortlisting us
//         WHERE us.client_id = c.userid
//     )";

// ---------- Extra filters ----------
$clientType = "1,2";
if ($this->ci->input->post('client_type')) {
    $ct = (array)$this->ci->input->post('client_type');
    $clientType = implode(',', array_map('intval', $ct));
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

// Stage / sub-stage arrive as an array or as "1,2,3" — cast to ints either way.
$stageCsv = $intCsv($this->ci->input->post('view_application_stage'));
if ($stageCsv !== '') {
    $where .= " AND c.applicant_stage IN ($stageCsv) ";
}

$subStageCsv = $intCsv($this->ci->input->post('view_application_sub_stage'));
if ($subStageCsv !== '') {
    $where .= " AND c.applicant_sub_status IN ($subStageCsv) ";
}

// ---------- Pagination ----------
$start  = (int)$this->ci->input->post('start');
$length = (int)$this->ci->input->post('length');
if ($length <= 0 || $length > 500) { $length = 10; }
if ($start < 0) { $start = 0; }

// ---------- Search (single value, or comma separated = OR) ----------
$whereSearch  = '';
$searchValue  = trim((string)($this->ci->input->post('search')['value'] ?? ''));

if ($searchValue !== '') {
    $parts = array_filter(array_map('trim', explode(',', $searchValue)), 'strlen');
    $conditions = [];
    foreach ($parts as $part) {
        $like = $this->ci->db->escape_like_str($part);
        $conditions[] = "CONCAT(bd.first_name, ' ', bd.last_name) LIKE '%$like%'";
    }
    if ($conditions) {
        $whereSearch = ' AND (' . implode(' OR ', $conditions) . ') ';
    }
}

// ---------- Shared FROM/WHERE so counts and page rows CANNOT diverge ----------
$fromWhere = "
    FROM tblclients c
    LEFT JOIN tblleads l ON l.id = c.leadid
    JOIN tblclient_university_shortlisting cus ON cus.client_id = c.userid
    JOIN tblbasic_details bd ON bd.userid = c.userid
    JOIN tbladmission_preferences p ON p.userid = c.userid
    JOIN tblapplicant_status ss ON ss.id = c.active
    JOIN tblapplicant_stages stage_category ON stage_category.id = c.applicant_stage
    JOIN tblapplication_sub_category_mbbs stage_sub_category ON stage_sub_category.id = c.applicant_sub_status
    LEFT JOIN tbladmission_preferences ap ON ap.userid = c.userid
    WHERE (
        ((l.id != '' AND l.type = 2) OR l.id IS NULL)
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
$sql = "
SELECT
    s.userid,
    s.student_name,
    s.status_name,
    s.client_type,
    s.primary_country,
    s.primary_university,
    s.stage_name,
    s.sub_stage_name" .
    (empty($selectDoc) ? "" : ",\n    " . implode(",\n    ", $selectDoc)) . "
FROM (
    SELECT c.userid,
           CONCAT(bd.first_name, ' ', bd.last_name) AS student_name,
           ss.name                                  AS status_name,
           IF(c.client_type = 1, 'EV', 'EVP')       AS client_type,
           ap.primary_country                       AS primary_country,
           ap.primary_university                    AS primary_university,
           stage_category.name                      AS stage_name,
           stage_sub_category.name                  AS sub_stage_name,
           cus.invitation_letter  as invitation_letter 
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
    $jsonColumns
) jt ON TRUE
GROUP BY s.userid, s.student_name, s.status_name, s.client_type,
         s.primary_country, s.primary_university, s.stage_name, s.sub_stage_name
ORDER BY s.student_name
";

// Debug: open the ajax URL with ?debug_sql=1 to inspect the generated SQL
if ($this->ci->input->get('debug_sql')) {
    header('Content-Type: text/plain');
    echo $sql;
    exit;
}

// echo $sql;
//     exit;

$rResult = $this->ci->db->query($sql)->result_array();

// ---------- Build DataTables response ----------
$output = [
    'draw'                 => (int)$this->ci->input->post('draw'),
    'iTotalRecords'        => $total,
    'iTotalDisplayRecords' => $filtered,
    'aaData'               => [],
];

foreach ($rResult as $aRow) {

    $row   = [];
    $row[] = '<div class="checkbox"><input type="checkbox" value="' . $aRow['userid'] . '"><label></label></div>';
    $row[] = $aRow['student_name'];
    $row[] = '<span class="inline-block text-">' . $aRow['status_name'] . '</span>';
    $row[] = $aRow['client_type'];
    $row[] = $aRow['primary_university'];
    $row[] = $aRow['primary_country'];
    $row[] = $aRow['stage_name'];
    $row[] = $aRow['sub_stage_name'];

    foreach ($aliases as $alias) {
       $url = $aRow[$alias] ?? '';

if ($url !== '' && !preg_match('#^https?://#i', $url)) {
    $url = rtrim($this->baseURL, '/') . '/' . ltrim($url, '/');
}

        // v6: approval_status now arrives as a string ("1"/"2"), so compare loosely.
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
        } elseif ($status !== null && $status !== '') {
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