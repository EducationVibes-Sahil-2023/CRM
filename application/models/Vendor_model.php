<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Vendor_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @param array $_POST data
     * @return integer
     * Add new department
     */
    public function add($table, $data)
    {
        $this->db->insert($table, $data);
        $insert_id = $this->db->insert_id();
        return $insert_id;
    }

    /**
     * @param  array $_POST data
     * @param  integer ID
     * @return boolean
     * Update department to database
     */
    public function update($table, $data, $id)
    {
        $this->db->where('id', $id);
        $this->db->update($table, $data);
        return true;
    }
}
