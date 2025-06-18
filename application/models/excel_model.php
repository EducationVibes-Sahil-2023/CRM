<?php
class Excel_model extends CI_Model
{
    public function insertSheet($data)
    {

        $this->db->insert(db_prefix() . 'excel_data_update', $data);
        return $this->db->insert_id();
    }

    public function updateSheet($sheetId, $data)
    {
        $this->db->where('id', $sheetId);
        return $this->db->update(db_prefix() . 'excel_data_update', $data);
    }
}
