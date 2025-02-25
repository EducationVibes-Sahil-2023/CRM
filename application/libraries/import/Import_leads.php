<?php

defined('BASEPATH') or exit('No direct script access allowed');
require_once(APPPATH . 'libraries/import/App_import.php');

class Import_leads extends App_import
{
    private $uniqueValidationFields = [];

    protected $notImportableFields = [];

    protected $requiredFields = ['name', 'phonenumber', 'email'];

    public function __construct()
    {
        $this->notImportableFields = hooks()->apply_filters('not_importable_leads_fields', ['id', 'source', 'assigned', 'status', 'dateadded', 'last_status_change', 'addedfrom', 'leadorder', 'date_converted', 'lost', 'junk', 'is_imported_from_email_integration', 'email_integration_uid', 'is_public', 'dateassigned', 'client_id', 'lastcontact', 'last_lead_status', 'from_form_id', 'default_language', 'hash', 'country', 'zip', 'type', 'last_type_change', 'website', 'last_lead_type', 'lead_value', 'company', 'title']);

        $uniqueValidationFields = json_decode(get_option('lead_unique_validation'));
        if (count($uniqueValidationFields) > 0) {
            $this->uniqueValidationFields = $uniqueValidationFields;
            $message                      = '';
            foreach ($uniqueValidationFields as $key => $field) {
                if ($key === 0) {
                    $message .= 'Based on your leads <b class="text-danger">unique validation</b> configured <a href="' . admin_url('settings?group=leads#unique_validation_wrapper') . '" target="_blank">options</a>, the lead <b>won\'t</b> be imported if:<br />';
                }

                $message .= '<br />&nbsp;&nbsp;&nbsp; - Lead <b>' . $field . '</b> already exists OR';
            }

            if ($message != '') {
                $message = substr($message, 0, -3);
            }

            $message .= '<br /><br />If you still want to import all leads, uncheck all unique validation field';

            $this->addImportGuidelinesInfo($message);
        }

        parent::__construct();
    }

    public function perform()
    {
        $this->initialize();

        $databaseFields      = $this->getImportableDatabaseFields();
        $totalDatabaseFields = count($databaseFields);

        foreach ($this->getRows() as $rowNumber => $row) {
            $insert = [];
            for ($i = 0; $i < $totalDatabaseFields; $i++) {
                $row[$i] = $this->checkNullValueAddedByUser($row[$i]);

                if ($databaseFields[$i] == 'name' && empty($row[$i])) {
                    $row[$i] = '/';
                } elseif ($databaseFields[$i] == 'country') {
                    $row[$i] = $this->countryValue($row[$i]);
                } elseif ($databaseFields[$i] == 'phonenumber') { //get last 10 digit from string.
                    $row[$i] = substr(trim($row[$i]), -10);
                }
                elseif ($databaseFields[$i] == 'alternative_phonenumber') { //get last 10 digit from string.
                    $row[$i] = substr(trim($row[$i]), -10);
                }

                $insert[$databaseFields[$i]] = $row[$i];
            }
            if (!empty($row[0]) && !empty($row[1])) { //check name,mobile not null;
                $insert = $this->trimInsertValues($insert);

                if (count($insert) > 0) {
                    if ($this->isDuplicateLead($insert)) {
                        continue;
                    }

                    $this->incrementImported();

                    $id = null;

                    if (!$this->isSimulation()) {
                        if (!isset($insert['dateadded'])) {
                            $insert['dateadded'] = date('Y-m-d H:i:s');
                        }

                        if (!isset($insert['addedfrom'])) {
                            $insert['addedfrom'] = get_staff_user_id();
                        }

                        $insert['status'] = $this->ci->input->post('status');
                        $insert['source'] = $this->ci->input->post('source');
                        $insert['type'] = $this->ci->input->post('type');


                        if ($this->ci->input->post('responsible')) {
                            $insert['assigned'] = $this->ci->input->post('responsible');
                        }

                        $tags = '';
                        if (isset($insert['tags']) || is_null($insert['tags'])) {
                            if (!is_null($insert['tags'])) {
                                $tags = $insert['tags'];
                            }
                            unset($insert['tags']);
                        }

                        $this->ci->db->insert(db_prefix() . 'leads', $insert);
                        $id = $this->ci->db->insert_id();

                        if ($id) {
                            handle_tags_save($tags, $id, 'lead');
                        }
                    } else {
                        $this->simulationData[$rowNumber] = $this->formatValuesForSimulation($insert);
                    }

                    $this->handleCustomFieldsInsert($id, $row, $i, $rowNumber, 'leads');
                }
            } //check name,mobile,emiil not null;

            if ($this->isSimulation() && $rowNumber >= $this->maxSimulationRows) {
                break;
            }
        }
    }

    protected function tags_formatSampleData()
    {
        return 'tag1,tag2';
    }

    public function formatFieldNameForHeading($field)
    {
        if (strtolower($field) == 'title') {
            return 'Position';
        }

        return parent::formatFieldNameForHeading($field);
    }

    protected function email_formatSampleData()
    {
        return uniqid() . '@example.com';
    }

    protected function failureRedirectURL()
    {
        return admin_url('leads/import');
    }

    // private function isDuplicateLead($data)
    // {
    //     foreach ($this->uniqueValidationFields as $field) {
    //         if ((isset($data[$field]) && $data[$field] != '')
    //             && total_rows(db_prefix() . 'leads', [$field => $data[$field]]) > 0
    //         ) {
    //             return true;
    //         }
    //     }

    //     return false;
    // }
    
  public function isDuplicateLead($data)
{
    $where = [];

    // Build the WHERE condition dynamically
    foreach ($this->uniqueValidationFields as $field) {
        if (isset($data[$field]) && $data[$field] !== '') {
            $where[$field] = $data[$field];
        }
    }

    // Ensure 'phonenumber' is not in the AND condition
    if (!empty($where)) {
        unset($where["phonenumber"]);
         unset($where["email"]);
        unset($where["alternative_phonenumber"]); // Exclude alternative_phonenumber as well
    }

    // Check if phonenumber exists
    if (empty($data["phonenumber"]) && empty($data["alternative_phonenumber"])) {
        return false; // Prevent errors if both numbers are missing
    }

    // OR condition for phonenumber and alternative_phonenumber
    $where_or = [];

    if (!empty($data["phonenumber"])) {
        $where_or[] = "(phonenumber = " . $this->ci->db->escape($data["phonenumber"]) . " OR alternative_phonenumber = " . $this->ci->db->escape($data["phonenumber"]) . ")";
    }
    if (!empty($data["alternative_phonenumber"])) {
        $where_or[] = "(phonenumber = " . $this->ci->db->escape($data["alternative_phonenumber"]) . " OR alternative_phonenumber = " . $this->ci->db->escape($data["alternative_phonenumber"]) . ")";
    }

    // Start Query
    $this->ci->db->from(db_prefix() . 'leads');

    if (!empty($where)) {
        $this->ci->db->where($where);
    }

    if (!empty($where_or)) {
        $this->ci->db->where("(" . implode(" OR ", $where_or) . ")", null, false);
    }

    // Get count of matching records
    $total = $this->ci->db->count_all_results();


    return ($total > 0); // Returns `true` if a duplicate exists, otherwise `false`
}


    private function formatValuesForSimulation($values)
    {
        foreach ($values as $column => $val) {
            if ($column == 'country' && !empty($val) && is_numeric($val)) {
                $country = $this->getCountry(null, $val);
                if ($country) {
                    $values[$column] = $country->short_name;
                }
            }
        }

        return $values;
    }

    private function getCountry($search = null, $id = null)
    {
        if ($search) {
            $this->ci->db->where('iso2', $search);
            $this->ci->db->or_where('short_name', $search);
            $this->ci->db->or_where('long_name', $search);
        } else {
            $this->ci->db->where('country_id', $id);
        }

        return  $this->ci->db->get(db_prefix() . 'countries')->row();
    }

    private function countryValue($value)
    {
        if ($value != '') {
            if (!is_numeric($value)) {
                $country = $this->getCountry($value);
                $value   = $country ? $country->country_id : 0;
            }
        } else {
            $value = 0;
        }

        return $value;
    }
    public function mass_assignation($leads_data)
    {
        // Load the necessary libraries and models

        $this->ci->load->model('Leads_model');

        foreach ($leads_data as $row) {
            try {
                $insert = $this->trimInsertValues($row);

                if (count($insert) > 0) {
                    // Skip if lead is a duplicate
                    if ($this->isDuplicateLead($insert)) {
                        continue;
                    }

                    // Set default values
                    $insert['dateadded'] = !empty($insert['dateadded']) ? $insert['dateadded'] : date('Y-m-d H:i:s');
                    $insert['addedfrom'] = get_staff_user_id();
                    
                    $insert['dateassigned'] = date('Y-m-d H:i:s');

                    // Handle tags
                    $tags = '';
                    if (isset($insert['tags'])) {
                        $tags = $insert['tags'];
                        unset($insert['tags']);
                    }

                    // Handle exam details if available
                    $insert['exam_details'] = [];
                    if (!empty($insert['exam_name']) && count($insert['exam_name']) > 0) {
                        foreach ($insert['exam_name'] as $key => $exam_name) {
                            if (!empty($exam_name) && !empty($insert['exam_score'][$key])) {
                                $insert['exam_details'][] = [
                                    'exam_name' => $exam_name,
                                    'exam_score' => $insert['exam_score'][$key]
                                ];
                            }
                        }
                        unset($insert['exam_name'], $insert['exam_score']);
                    }

                    // Encode exam details if present, or set it as an empty string
                    $insert['exam_details'] = !empty($insert['exam_details']) ? json_encode($insert['exam_details']) : '';

                    // Insert into the leads table
                    $this->ci->db->insert(db_prefix() . 'leads', $insert);
                    $insert_id = $this->ci->db->insert_id();

                    // If insertion successful, log the activity and handle tags
                    if ($insert_id) {
                        log_activity('New Lead Added [ID: ' . $insert_id . ']');
                        $this->ci->Leads_model->log_lead_activity($insert_id, 'not_lead_activity_created');
                        hooks()->do_action('lead_created', $insert_id);
                        handle_tags_save($tags, $insert_id, 'lead');
                    }
                }
            } catch (Exception $e) {
                // Log error and continue with the next record
                log_message('error', 'Failed to insert lead: ' . $e->getMessage());
                continue;
            }
        }
    }
}
