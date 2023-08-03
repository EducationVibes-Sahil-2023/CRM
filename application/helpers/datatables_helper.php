<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * General function for all datatables, performs search,additional select,join,where,orders
 * @param  array $aColumns           table columns
 * @param  mixed $sIndexColumn       main column in table for bettter performing
 * @param  string $sTable            table name
 * @param  array  $join              join other tables
 * @param  array  $where             perform where in query
 * @param  array  $additionalSelect  select additional fields
 * @param  string $sGroupBy group results
 * @return array
 */
function data_tables_init($aColumns, $sIndexColumn, $sTable, $join = [], $where = [], $additionalSelect = [], $sGroupBy = '', $searchAs = [], $order_by_status = 0)
{
    $CI          = &get_instance();
    $__post      = $CI->input->post();
    $havingCount = '';
    /*
     * Paging
     */
    $sLimit = '';
    if ((is_numeric($CI->input->post('start'))) && $CI->input->post('length') != '-1') {
        $sLimit = 'LIMIT ' . intval($CI->input->post('start')) . ', ' . intval($CI->input->post('length'));
    }
    $_aColumns = [];
    foreach ($aColumns as $column) {
        // if found only one dot
        if (substr_count($column, '.') == 1 && strpos($column, ' as ') === false) {
            $_column = explode('.', $column);
            if (isset($_column[1])) {
                if (startsWith($_column[0], db_prefix())) {
                    $_prefix = prefixed_table_fields_wildcard($_column[0], $_column[0], $_column[1]);
                    array_push($_aColumns, $_prefix);
                } else {
                    array_push($_aColumns, $column);
                }
            } else {
                array_push($_aColumns, $_column[0]);
            }
        } else {
            array_push($_aColumns, $column);
        }
    }

    /*
     * Ordering
     */
    $nullColumnsAsLast = get_null_columns_that_should_be_sorted_as_last();

    $sOrder = '';
    if ($CI->input->post('order')) {
        $sOrder = 'ORDER BY ';
        foreach ($CI->input->post('order') as $key => $val) {
            $columnName = $aColumns[intval($__post['order'][$key]['column'])];
            $dir        = strtoupper($__post['order'][$key]['dir']);

            if (strpos($columnName, ' as ') !== false) {
                $columnName = strbefore($columnName, ' as');
            }

            // first checking is for eq tablename.column name
            // second checking there is already prefixed table name in the column name
            // this will work on the first table sorting - checked by the draw parameters
            // in future sorting user must sort like he want and the duedates won't be always last
            if ((in_array($sTable . '.' . $columnName, $nullColumnsAsLast)
                || in_array($columnName, $nullColumnsAsLast))) {
                $sOrder .= $columnName . ' IS NULL ' . $dir . ', ' . $columnName;
            } else {
                $sOrder .= hooks()->apply_filters('datatables_query_order_column', $columnName, $sTable);
            }
            $sOrder .= ' ' . $dir . ', ';
        }
        if (trim($sOrder) == 'ORDER BY') {
            $sOrder = '';
        }

        if ($order_by_status == 1) {
            $sOrder = '';
        }

        $sOrder = rtrim($sOrder, ', ');

        if (
            get_option('save_last_order_for_tables') == '1'
            && $CI->input->post('last_order_identifier')
            && $CI->input->post('order')
        ) {
            // https://stackoverflow.com/questions/11195692/json-encode-sparse-php-array-as-json-array-not-json-object

            $indexedOnly = [];
            foreach ($CI->input->post('order') as $row) {
                $indexedOnly[] = array_values($row);
            }

            $meta_name = $CI->input->post('last_order_identifier') . '-table-last-order';

            update_staff_meta(get_staff_user_id(), $meta_name, json_encode($indexedOnly, JSON_NUMERIC_CHECK));
        }
    }
    /*
     * Filtering
     * NOTE this does not match the built-in DataTables filtering which does it
     * word by word on any field. It's possible to do here, but concerned about efficiency
     * on very large tables, and MySQL's regex functionality is very limited
     */
    $sWhere = '';
    if ((isset($__post['search'])) && $__post['search']['value'] != '') {
        $search_value = $__post['search']['value'];
        $search_value = trim($search_value);

        $sWhere             = 'WHERE (';
        $sMatchCustomFields = [];
        // Not working, do not use it
        $useMatchForCustomFieldsTableSearch = hooks()->apply_filters('use_match_for_custom_fields_table_search', 'false');


        for ($i = 0; $i < count($aColumns); $i++) {
            $columnName = $aColumns[$i];

            $ignore_column = array('update_count');
            multi_strpos($columnName, $ignore_column);
            if (multi_strpos($columnName, $ignore_column) !== false) {

                $columnName = "";
            }

            if (!empty($columnName) && $columnName != '') {
                if (strpos($columnName, ' as ') !== false) {
                    $columnName = strbefore($columnName, ' as');
                }

                if (!empty(trim($columnName))) {
                    if (stripos($columnName, 'AVG(') !== false || stripos($columnName, 'SUM(') !== false) {
                    } else {
                        if (($__post['columns'][$i]) && $__post['columns'][$i]['searchable'] == 'true') {
                            if (isset($searchAs[$i])) {
                                $columnName = $searchAs[$i];
                            }
                            // Custom fields values are FULLTEXT and should be searched with MATCH
                            // Not working ATM
                            if ($useMatchForCustomFieldsTableSearch === 'true' && startsWith($columnName, 'ctable_')) {
                                $sMatchCustomFields[] = $columnName;
                            } else {
                                if (str_contains($search_value, '!=')) {
                                    $sWhere .= ' convert( ifnull(' . $columnName . ',"") USING utf8)' . " NOT LIKE '%" . $CI->db->escape_str(str_replace("!=", "", $search_value)) . "%' AND ";
                                } else {
                                    $sWhere .= ' convert( ifnull(' . $columnName . ',"") USING utf8)' . " LIKE '%" . $CI->db->escape_str($search_value) . "%' OR ";
                                }
                            }
                        }
                    }
                }
            }
        }
        if (count($sMatchCustomFields) > 0) {
            $s = $CI->db->escape_str($search_value);
            foreach ($sMatchCustomFields as $matchCustomField) {
                if (str_contains($s, '!=')) {
                    $sWhere .= " NOT MATCH ({$matchCustomField}) AGAINST (CONVERT(BINARY('" . str_replace("!=", "", $s) . "') USING utf8)) AND ";
                } else {
                    $sWhere .= " MATCH ({$matchCustomField}) AGAINST (CONVERT(BINARY('{$s}') USING utf8)) OR ";
                }
            }
        }

        if (count($additionalSelect) > 0) {
            foreach ($additionalSelect as $searchAdditionalField) {
                if (strpos($searchAdditionalField, ' as ') !== false) {
                    $searchAdditionalField = strbefore($searchAdditionalField, ' as');
                }
                if (stripos($columnName, 'AVG(') !== false || stripos($columnName, 'SUM(') !== false) {
                } else {

                    $searchAdditionalField = explode(" ", $searchAdditionalField)[0];
                    // Use index
                    if (str_contains($search_value, '!=')) {
                        $sWhere .= 'convert(ifnull(' . $searchAdditionalField . ',"") USING utf8)' . " NOT LIKE '%" . $CI->db->escape_str(str_replace("!=", "", $search_value)) . "%' AND ";
                    } else {
                        $sWhere .= 'convert(ifnull(' . $searchAdditionalField . ',"") USING utf8)' . " LIKE '%" . $CI->db->escape_str($search_value) . "%' OR ";
                    }
                }
            }
        }

        if (str_contains($search_value, '!=')) {
            $sWhere = substr_replace($sWhere, '', -4);
        } else {
            $sWhere = substr_replace($sWhere, '', -3);
        }
        $sWhere .= ')';
    } else {
        // Check for custom filtering
        $searchFound = 0;
        $sWhere      = 'WHERE (';
        for ($i = 0; $i < count($aColumns); $i++) {
            if (($__post['columns'][$i]) && $__post['columns'][$i]['searchable'] == 'true') {
                $search_value = $__post['columns'][$i]['search']['value'];

                $columnName = $aColumns[$i];
                if (strpos($columnName, ' as ') !== false) {
                    $columnName = strbefore($columnName, ' as');
                }
                $columnName = explode(" ", $columnName)[0];
                if ($search_value != '') {
                    if (str_contains($search_value, '!=')) {
                        $sWhere .= 'convert(ifnull(' . $columnName . ',"") USING utf8)' . " NOT LIKE '%" . $CI->db->escape_str(str_replace("!=", "", $search_value)) . "%' AND ";
                    } else {
                        $sWhere .= 'convert(ifnull(' . $columnName . ',"") USING utf8)' . " LIKE '%" . $CI->db->escape_str($search_value) . "%' OR ";
                    }
                    if (count($additionalSelect) > 0) {
                        foreach ($additionalSelect as $searchAdditionalField) {
                            $searchAdditionalField = explode(" ", $searchAdditionalField)[0];
                            if (str_contains($search_value, '!=')) {
                                $sWhere .= 'convert(ifnull(' . $searchAdditionalField . ',"") USING utf8)' . " NOT LIKE '" . $CI->db->escape_str(str_replace("!=", "", $search_value)) . "%' AND ";
                            } else {
                                $sWhere .= 'convert(ifnull(' . $searchAdditionalField . ',"") USING utf8)' . " LIKE '" . $CI->db->escape_str($search_value) . "%' OR ";
                            }
                        }
                    }
                    $searchFound++;
                }
            }
        }
        if ($searchFound > 0) {

            if ((isset($__post['search'])) && $__post['search']['value'] != '') {
                if (str_contains($__post['search']['value'], '!=')) {
                    $sWhere = substr_replace($sWhere, '', -4);
                } else {
                    $sWhere = substr_replace($sWhere, '', -3);
                }
            } else {
                $sWhere = substr_replace($sWhere, '', -3);
            }
            $sWhere .= ')';
        } else {
            $sWhere = '';
        }
    }
    /*
     * SQL queries
     * Get data to display
     */
    $_additionalSelect = '';
    if (count($additionalSelect) > 0) {
        $_additionalSelect = ',' . implode(',', $additionalSelect);
    }
    $where = implode(' ', $where);
    if ($sWhere == '') {
        $where = trim($where);
        if (startsWith($where, 'AND') || startsWith($where, 'OR')) {
            if (startsWith($where, 'OR')) {
                $where = substr($where, 2);
            } else {
                $where = substr($where, 3);
            }
            $where = 'WHERE ' . $where;
        }
    }

    $join = implode(' ', $join);

    $sQuery = '
    SELECT SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $_aColumns)) . ' ' . $_additionalSelect . "
    FROM $sTable
    " . $join . "
    " . $sWhere . "
    " . $where . "
    $sGroupBy
    $sOrder
    $sLimit
    ";

    $rResult = $CI->db->query($sQuery)->result_array();

    $rResult = hooks()->apply_filters('datatables_sql_query_results', $rResult, [
        'table' => $sTable,
        'limit' => $sLimit,
        'order' => $sOrder,
    ]);

    /* Data set length after filtering */
    $sQuery = '
    SELECT FOUND_ROWS()
    ';
    $_query         = $CI->db->query($sQuery)->result_array();
    $iFilteredTotal = $_query[0]['FOUND_ROWS()'];
    if (startsWith($where, 'AND')) {
        $where = 'WHERE ' . substr($where, 3);
    }
    /* Total data set length */
    $sQuery = '
    SELECT COUNT(distinct(' . $sTable . '.' . $sIndexColumn . "))
    FROM $sTable " . $join . ' ' . $where;


    $_query = $CI->db->query($sQuery)->result_array();
    $iTotal = $_query[0]['COUNT(distinct(' . $sTable . '.' . $sIndexColumn . '))'];

    /*
     * Output
     */
    $output = [
        'draw'                 => $__post['draw'] ? intval($__post['draw']) : 0,
        'iTotalRecords'        => $iTotal,
        'iTotalDisplayRecords' => $iFilteredTotal,
        'aaData'               => [],
    ];
    //print_r($output);die;
    return [
        'rResult' => $rResult,
        'output'  => $output,
    ];
}

/**
 * Used in data_tables_init function to fix sorting problems when duedate is null
 * Null should be always last
 * @return array
 */
function get_null_columns_that_should_be_sorted_as_last()
{
    $columns = [
        db_prefix() . 'projects.deadline',
        db_prefix() . 'tasks.duedate',
        db_prefix() . 'contracts.dateend',
        db_prefix() . 'subscriptions.date_subscribed',
    ];

    return hooks()->apply_filters('null_columns_sort_as_last', $columns);
}
/**
 * Render table used for datatables
 * @param  array  $headings           [description]
 * @param  string $class              table class / added prefix table-$class
 * @param  array  $additional_classes
 * @return string                     formatted table
 */
/**
 * Render table used for datatables
 * @param  array   $headings
 * @param  string  $class              table class / add prefix eq.table-$class
 * @param  array   $additional_classes additional table classes
 * @param  array   $table_attributes   table attributes
 * @param  boolean $tfoot              includes blank tfoot
 * @return string
 */
function render_datatable($headings = [], $class = '', $additional_classes = [''], $table_attributes = [])
{
    $_additional_classes = '';
    $_table_attributes   = ' ';
    if (count($additional_classes) > 0) {
        $_additional_classes = ' ' . implode(' ', $additional_classes);
    }
    $CI      = &get_instance();
    $browser = $CI->agent->browser();
    $IEfix   = '';
    if ($browser == 'Internet Explorer') {
        $IEfix = 'ie-dt-fix';
    }

    foreach ($table_attributes as $key => $val) {
        $_table_attributes .= $key . '=' . '"' . $val . '" ';
    }

    $table = '<div class="' . $IEfix . '"><table' . $_table_attributes . ' class="dt-table-loading table table-' . $class . '' . $_additional_classes . '">';
    $table .= '<thead>';
    $table .= '<tr>';
    foreach ($headings as $heading) {
        if (!is_array($heading)) {
            $table .= '<th>' . $heading . '</th>';
        } else {
            $th_attrs = '';
            if (isset($heading['th_attrs'])) {
                foreach ($heading['th_attrs'] as $key => $val) {
                    $th_attrs .= $key . '=' . '"' . $val . '" ';
                }
            }
            $th_attrs = ($th_attrs != '' ? ' ' . $th_attrs : $th_attrs);
            $table .= '<th' . $th_attrs . '>' . $heading['name'] . '</th>';
        }
    }
    $table .= '</tr>';
    $table .= '</thead>';
    $table .= '<tbody></tbody>';
    $table .= '</table></div>';
    echo $table;
}

/**
 * Translated datatables language based on app languages
 * This feature is used on both admin and customer area
 * @return array
 */
function get_datatables_language_array()
{
    $lang = [
        'emptyTable'        => preg_replace("/{(\d+)}/", _l('dt_entries'), _l('dt_empty_table')),
        'info'              => preg_replace("/{(\d+)}/", _l('dt_entries'), _l('dt_info')),
        'infoEmpty'         => preg_replace("/{(\d+)}/", _l('dt_entries'), _l('dt_info_empty')),
        'infoFiltered'      => preg_replace("/{(\d+)}/", _l('dt_entries'), _l('dt_info_filtered')),
        'lengthMenu'        => '_MENU_',
        'loadingRecords'    => _l('dt_loading_records'),
        'processing'        => '<div class="dt-loader"></div>',
        'search'            => '<div class="input-group"><span class="input-group-addon"><span class="fa fa-search"></span></span>',
        'searchPlaceholder' => _l('dt_search'),
        'zeroRecords'       => _l('dt_zero_records'),
        'paginate'          => [
            'first'    => _l('dt_paginate_first'),
            'last'     => _l('dt_paginate_last'),
            'next'     => _l('dt_paginate_next'),
            'previous' => _l('dt_paginate_previous'),
        ],
        'aria' => [
            'sortAscending'  => _l('dt_sort_ascending'),
            'sortDescending' => _l('dt_sort_descending'),
        ],
    ];

    return hooks()->apply_filters('datatables_language_array', $lang);
}

/**
 * Function that will parse filters for datatables and will return based on a couple conditions.
 * The returned result will be pushed inside the $where variable in the table SQL
 * @param  array $filter
 * @return string
 */
function prepare_dt_filter($filter)
{
    $filter = implode(' ', $filter);
    if (startsWith($filter, 'AND')) {
        $filter = substr($filter, 3);
    } elseif (startsWith($filter, 'OR')) {
        $filter = substr($filter, 2);
    }

    return $filter;
}
/**
 * Get table last order
 * @param  string $tableID table unique identifier id
 * @return string
 */
function get_table_last_order($tableID)
{
    return htmlentities(get_staff_meta(get_staff_user_id(), $tableID . '-table-last-order'));
}



function multi_strpos($haystack, $needles, $offset = 0)
{

    foreach ($needles as $n) {
        if (strpos($haystack, $n, $offset) !== false)
            return strpos($haystack, $n, $offset);
    }
    return false;
}


function get_applicant_status($stage, $client_id)
{
    $CI = &get_instance();
    try {
        $response = [];
        if ($stage == 1) {
            $result =  get_stage_1($stage, $client_id);
            if (empty($result)) {
                $response["applicant_stage_status"] = "Pending";
                $response["updated_date"] = "";
            } else {
                $response["applicant_stage_status"] = $result->applicant_stage_status;
                $response["updated_date"] = $result->updated_date;
            }
        } else if ($stage == 2) {

            $result =  get_stage_2($stage, $client_id);
            if (empty($result)) {
                // $result = get_stage_1($stage, $client_id);
                $response["applicant_stage_status"] = "Pending";
                $response["updated_date"] = "";
            } else {
                if (!empty($result->profile_status)) {
                    if ($result->profile_status == 1) {
                        $response["applicant_stage_status"] = "Approved";
                    } else if ($result->profile_status == 2) {
                        $response["applicant_stage_status"] = "Reject";
                    }
                    $response["updated_date"] = $result->email_updated_date;
                } else if (!empty($result->email) && !empty($result->vendor) && !empty($result->sop)) {
                    $response["applicant_stage_status"] = "Profile pending";
                    $response["updated_date"] = $result->email_updated_date;
                } else {
                    if (!empty($result->email)) {
                        $response["applicant_stage_status"] = "Email created";
                        $response["updated_date"] = $result->email_updated_date;
                    }
                    if (!empty($result->vendor)) {
                        $response["applicant_stage_status"] = "Vendor updated";
                        $response["updated_date"] = $result->vendor_updated_date;
                    }
                    if (!empty($result->sop)) {
                        $response["applicant_stage_status"] = "SOP completed";
                        $response["updated_date"] = $result->sop_updated_date;
                    }
                    if (!empty($result->email) && !empty($result->vendor) && !empty($result->sop)) {
                        $response["applicant_stage_status"] = "SOP completed";
                        $response["updated_date"] = $result->sop_updated_date;
                    }
                }
            }
        } else if ($stage == 3) {
            $result =  get_stage_3($stage, $client_id);
            if (empty($result)) {
                $response["applicant_stage_status"] = "Pending";
                $response["updated_date"] = "";
            } else {
                $max_date = $result->created_date;

                $date1 = isset($result->created_date) ? new DateTime($result->created_date) : null;
                $date2 = isset($result->updated_date) ? new DateTime($result->updated_date) : null;

                if ($date1 !== null && $date2 !== null) {
                    if ($date1 > $date2) {
                        $max_date = $result->created_date;
                    } elseif ($date1 < $date2) {
                        $max_date = $result->updated_date;
                    } else {
                        $max_date = $result->created_date;
                    }
                } elseif ($date1 === null && $date2 === null) {
                } elseif ($date1 === null) {
                } else {
                    $max_date = $result->created_date;
                }
                // if ($client_id == 175) {
                //     echo $result->university_count;
                //     echo "<br>";
                //     echo $result->approved_count;
                //     echo "<br>";
                //     echo $result->reject_count;
                //     echo "<br>";
                //     die;
                // }


                if (!empty($result->university_count) && !empty($result->approved_count) && ($result->university_count == $result->approved_count)) {
                    $response["applicant_stage_status"] = "All approved";
                    $response["updated_date"] = $result->client_updated_date;
                } else if (!empty($result->university_count) && !empty($result->reject_count) && ($result->university_count == $result->reject_count)) {
                    $response["applicant_stage_status"] = "All reject";
                    $response["updated_date"] = $result->client_updated_date;
                } else if (!empty($result->university_count) && !empty($result->approved_count) && !empty($result->reject_count)) {
                    $response["applicant_stage_status"] = "University shortlisted";
                    $response["updated_date"] = $result->client_updated_date;
                } else if (!empty($result->university_count)) {
                    $response["applicant_stage_status"] = "University shortlisting is pending";
                    $response["updated_date"] = $max_date;
                }
            }
        } else if ($stage == 4) {
            $result =  get_stage_4($stage, $client_id);
            if (empty($result)) {
                $response["applicant_stage_status"] = "Pending";
                $response["updated_date"] = "";
            } else {
                $max_date = $result->created_date;

                $date1 = isset($result->created_date) ? new DateTime($result->created_date) : null;
                $date2 = isset($result->updated_date) ? new DateTime($result->updated_date) : null;

                if ($date1 !== null && $date2 !== null) {
                    if ($date1 > $date2) {
                        $max_date = $result->created_date;
                    } elseif ($date1 < $date2) {
                        $max_date = $result->updated_date;
                    } else {
                        $max_date = $result->created_date;
                    }
                } elseif ($date1 === null && $date2 === null) {
                } elseif ($date1 === null) {
                } else {
                    $max_date = $result->created_date;
                }

                if (!empty($result->university_count) && !empty($result->action_taken) && ($result->university_count == $result->action_taken)) {
                    $response["applicant_stage_status"] = "All application submitting by Admin.Waiting for offer letter";
                    $response["updated_date"] = $result->client_updated_date;
                } else if (!empty($result->university_count) && !empty($result->action_taken) && !empty($result->not_take_action)) {
                    $response["applicant_stage_status"] = "Total " . $result->university_count . " Application." . $result->action_taken . " Application submitting AND " . $result->not_take_action . " Application not submitting by admin";
                    $response["applicant_stage_status_"] = "Application shortlisting";
                    $response["updated_date"] = $result->client_updated_date;
                } else if (!empty($result->university_count)) {
                    $response["applicant_stage_status"] = "Application submitting by Admin.Waiting for offer letter";
                    $response["updated_date"] = $max_date;
                }
            }
        } else if ($stage == 5) {
            $result =  get_stage_5($stage, $client_id);
            if (empty($result)) {
                $response["applicant_stage_status"] = "Pending";
                $response["updated_date"] = "";
            } else {
                $max_date = $result->created_date;

                $date1 = isset($result->created_date) ? new DateTime($result->created_date) : null;
                $date2 = isset($result->updated_date) ? new DateTime($result->updated_date) : null;

                if ($date1 !== null && $date2 !== null) {
                    if ($date1 > $date2) {
                        $max_date = $result->created_date;
                    } elseif ($date1 < $date2) {
                        $max_date = $result->updated_date;
                    } else {
                        $max_date = $result->created_date;
                    }
                } elseif ($date1 === null && $date2 === null) {
                } elseif ($date1 === null) {
                } else {
                    $max_date = $result->created_date;
                }

                if (!empty($result->university_count) && !empty($result->offer_letter) && ($result->university_count == $result->offer_letter)) {
                    $response["applicant_stage_status"] = "Offer shortlisted Completed.Wating for applicant acceptance";
                    $response["updated_date"] = $result->client_updated_date;
                } else if (!empty($result->university_count) && !empty($result->offer_letter) && !empty($result->offer_letter_not)) {
                    $response["applicant_stage_status"] = "Total " . $result->university_count . " Application Submittind." . $result->offer_letter . " Application Offer response AND " . $result->offer_letter_not . " Application is pending";
                    $response["applicant_stage_status_"] = "Offer shortlisted";

                    $response["updated_date"] = $result->client_updated_date;
                } else if (!empty($result->university_count)) {
                    $response["applicant_stage_status"] =  "Application submitting by Admin.Pending offer letter";
                    $response["updated_date"] = $max_date;
                }
            }
        } else if ($stage == 6) {
            $result =  get_stage_6($stage, $client_id);

            $max_date = $result->created_date;

            $date1 = isset($result->created_date) ? new DateTime($result->created_date) : null;
            $date2 = isset($result->updated_date) ? new DateTime($result->updated_date) : null;

            if ($date1 !== null && $date2 !== null) {
                if ($date1 > $date2) {
                    $max_date = $result->created_date;
                } elseif ($date1 < $date2) {
                    $max_date = $result->updated_date;
                } else {
                    $max_date = $result->created_date;
                }
            } elseif ($date1 === null && $date2 === null) {
            } elseif ($date1 === null) {
            } else {
                $max_date = $result->created_date;
            }
            $response["applicant_stage_status"] = "Pending";
            $response["updated_date"] = $result->client_updated_date;
            if (!empty($result->acceptance_status) && $result->acceptance_status == 1) {
                $response["applicant_stage_status"] = "Completed";
                $response["updated_date"] = $result->client_updated_date;
            }
        } else {
            $response["applicant_stage_status"] =  "Stage is not found";
        }

        $update_array_data = [];

        if (!empty($response["applicant_stage_status_"])) {
            $update_array_data["application_text"] = $response["applicant_stage_status_"];
        } else {
            if (!empty($response["applicant_stage_status"])) {
                $update_array_data["application_text"] = $response["applicant_stage_status"];
            }
        }
        if (empty($update_array_data["application_text"])) {
            $update_array_data["application_text"] = "pending";
        }
        if (empty($response["applicant_stage_status"])) {
            $response["applicant_stage_status"] = "pending";
        }
        if (!empty($update_array_data) && !empty($update_array_data["application_text"])) {
            $CI->db->where("userid", $client_id);
            $CI->db->update(db_prefix() . 'clients', $update_array_data);
            update_application_sub_category($stage, $update_array_data["application_text"]);
        }

        return $response;
    } catch (Exception $e) {
        return $response;
    }
}

function update_application_sub_category($stage, $text)
{
    $CI = &get_instance();
    $check = $CI->db->select("id")->where(array("application_tracker" => $stage, "name" => $text))->get(db_prefix() . "application_sub_category")->row();
    if (!empty($check->id)) { // Corrected variable name from $$check->id to $check->id
        // Do something if the record already exists
    } else {
        $CI->db->insert(db_prefix() . 'application_sub_category', array("application_tracker" => $stage, "name" => $text, "status" => 1));
    }
    return true;
}
function get_stage_1($stage, $client_id)
{
    $CI = &get_instance();
    $sql = "Select if(updated_date='0000-00-00 00:00:00',created_date,updated_date) updated_date,if(document_status=1,'Approved',if(document_status=2,'Rejected','Pending')) applicant_stage_status from " . db_prefix() . "client_documents  where client_id='{$client_id}' and status = 1 ";
    return $result = $CI->db->query($sql)->row();
}
function get_stage_2($stage, $client_id)
{
    $CI = &get_instance();
    $sql = "Select email,vendor,sop,if(email_updated_date='0000-00-00 00:00:00',created_date,email_updated_date) email_updated_date,if(sop_updated_date='0000-00-00 00:00:00',created_date,sop_updated_date) sop_updated_date,if(vendor_updated_date='0000-00-00 00:00:00',created_date,vendor_updated_date) vendor_updated_date,profile_status  from " . db_prefix() . "client_profile_creation  where client_id='{$client_id}' and status = 1 ";
    return $result = $CI->db->query($sql)->row();
}

function get_stage_3($stage, $client_id)
{
    $CI = &get_instance();
    $sql = "SELECT 
    COUNT(1) AS university_count,
    SUM(IF(university_status = 1, 1, 0)) AS approved_count,
    SUM(IF(university_status = 2, 1, 0)) AS reject_count,
    MAX(created_date) AS created_date,
    MAX(updated_date) AS updated_date,
    MAX(client_updated_date) AS client_updated_date
  FROM " . db_prefix() . "client_university_shortlisting
  WHERE client_id = '{$client_id}' AND status = '1';
   ";

    return $result = $CI->db->query($sql)->row();
}

function get_stage_4($stage, $client_id)
{

    $CI = &get_instance();
    $sql = "SELECT 
    university_submit_status,
    COUNT(1) AS university_count,
    SUM(IF(university_submit_status != 1, 1, 0)) AS action_taken,
    SUM(IF(university_submit_status = 0, 1, 0)) AS not_take_action,
    MAX(created_date) AS created_date,
    MAX(updated_date) AS updated_date,
    MAX(submit_date) AS client_updated_date
  FROM " . db_prefix() . "client_university_shortlisting
  WHERE client_id = '{$client_id}' AND status = '1';
   ";

    return $result = $CI->db->query($sql)->row();
}

function get_stage_5($stage, $client_id)
{

    $CI = &get_instance();
    $sql = "SELECT 
    sum(fee_status) fee_status_check,
    university_submit_status,
    COUNT(1) AS university_count,
    SUM(IF(media_file != '', 1, 0)) AS offer_letter,
    SUM(IF(media_file = '', 1, 0)) AS offer_letter_not,
    MAX(created_date) AS created_date,
    MAX(updated_date) AS updated_date,
    MAX(offer_date) AS client_updated_date
  FROM " . db_prefix() . "client_university_shortlisting
  WHERE client_id = '{$client_id}' AND status = '1';
   ";

    return $result = $CI->db->query($sql)->row();
}

function get_stage_6($stage, $client_id)
{
    $CI = &get_instance();
    $sql = "SELECT 
    university_submit_status,
    sum(acceptance_status) acceptance_status,
    COUNT(1) AS university_count,
    SUM(IF(media_file != '', 1, 0)) AS offer_letter,
    SUM(IF(media_file = '', 1, 0)) AS offer_letter_not,
    MAX(created_date) AS created_date,
    MAX(updated_date) AS updated_date,
    MAX(offer_date) AS client_updated_date
  FROM " . db_prefix() . "client_university_shortlisting
  WHERE client_id = '{$client_id}' AND status = '1';
   ";

    return $result = $CI->db->query($sql)->row();
}
