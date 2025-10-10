<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Hostel_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function get_university_rentInfo()
    {
        return
            $this->db->query("SELECT 
        university_id,
        university_name,
        CONCAT(
        '[', 
        GROUP_CONCAT(
        DISTINCT JSON_OBJECT(
        'room_capacity', room_capacity,
        'currency', currency,
        'rent', rent
        ) 
        SEPARATOR ','
        ),
        ']'
        ) AS rooms
        FROM tblhostel_rental
        WHERE status = 1
        GROUP BY university_id, university_name;
        ")->result_array();
    }

    public function hostel_quotation_data($getId, $quotation_id = "")
    {
        try {
            $this->db->select("*")
                ->from(db_prefix() . "hostel_quotation")
                ->where("hostel_info_id", $getId);

            if (!empty($quotation_id)) {
                $this->db->where("id", $quotation_id);
            }

            // Optional: only active records (if you store a status column)
            // $this->db->where("status", 1);

            $query = $this->db->get();

            return !empty($quotation_id) ? $query->row() : $query->result_array();
        } catch (Exception $e) {
            log_message('error', 'Error fetching applicant quotation data: ' . $e->getMessage());
            return [];
        }
    }
}
