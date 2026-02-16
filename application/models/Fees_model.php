<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Fees_model extends App_Model
{
    public function __construct()
    {

        parent::__construct();
    }

    function segment()
    {
        $query = $this->s_db
            ->select("id, name")
            ->from("course")
            ->get()
            ->result_array();

        $segment = array_column($query, null, "id");

        return $segment;
    }


    function countries()
    {
        $query = $this->s_db
            ->select("id, segment_id, country_name, country_icon")
            ->from("countries")
            ->get()
            ->result_array();

        $countries = [];
        foreach ($query as $row) {
            $segment_id = $row['segment_id'];
            if (!isset($countries[$segment_id])) {
                $countries[$segment_id] = [];
            }
            $countries[$segment_id][] = $row;
        }
        return $countries;
    }


    function universities()
    {
        $query = $this->s_db
            ->select("u.id,u.country_id,u.university_name,ub.images,,if(ub.logo!='',ub.logo,ub.logo_image) as logo,if(ub.card_image!='',ub.card_image,ub.images) as card_image,ub.founded")
            ->from("universities u")->join("university_banner ub", "ub.university_id = u.id", "Left")
            ->get()
            ->result_array();

        $universites = [];
        foreach ($query as $row) {
            $country_id = $row['country_id'];
            if (!isset($countries[$country_id])) {
                $univesites[$country_id] = [];
            }
            $universites[$country_id][] = $row;
        }
        return $universites;
    }

    public function checkRecord($segment_id, $country_id, $university_id)
    {
        return $this->db->where('segment_id', $segment_id)
            ->where('country_id', $country_id)
            ->where('university_id', $university_id)
            ->get(db_prefix() . 'fees_structure_data')  // 👈 your table name
            ->row();
    }

    public function updateRecord($id, $data)
    {
        return $this->db->where('id', $id)
            ->update('fees_structure_data', $data);
    }



    public function insertRecord($data)
    {
        return $this->db->insert(db_prefix() . 'fees_structure_data', $data);
    }

    public function getFeesStructure($id = "")
    {
        if (!empty($id)) {
            return $this->db
                ->where('id', $id)
                ->get(db_prefix() . 'fees_structure_data')
                ->row_array();
        }

        return $this->db
            ->get(db_prefix() . 'fees_structure_data')
            ->result_array();
    }


    public function officeLocations()
    {
        $locations = $this->s_db
            ->select("name")
            ->from("office_locations")
            ->where("main", 0)
            ->order_by("sequence", "asc")
            ->get()
            ->result_array();

        $names = array_column($locations, 'name');

        return implode(' | ', $names);
    }
}
