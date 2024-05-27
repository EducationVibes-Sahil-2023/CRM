<?php

defined('BASEPATH') or exit('No direct script access allowed');

class knowledge_base_group_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Add new customer group
     * @param array $data $_POST data
     */
    public function add($data)
    {
        $this->db->insert(db_prefix() . 'knowledge_group', $data);

        $insert_id = $this->db->insert_id();

        if ($insert_id) {
            log_activity('New Knowledge Group Created [ID:' . $insert_id . ', Name:' . $data['name'] . ']');

            return $insert_id;
        }

        return false;
    }

    public function get_knowledge_groups($id = '', $where = array())
    {
        $this->db->select([
            '*',
            'LENGTH(staff_ids) - LENGTH(REPLACE(staff_ids, ",", "")) + 1 AS total_members',
            'CONCAT(name, " - ", (LENGTH(staff_ids) - LENGTH(REPLACE(staff_ids, ",", "")) + 1) + count(s.staffid)) AS group_name'
        ]);

        if (is_numeric($id)) {
            $this->db->select([
                '*',
                'LENGTH(staff_ids) - LENGTH(REPLACE(staff_ids, ",", "")) + 1 AS total_members',
                'CONCAT(name, " - ", LENGTH(staff_ids) - LENGTH(REPLACE(staff_ids, ",", "")) + 1) AS group_name'
            ]);
            return $this->db->where('id', $id)
                ->get(db_prefix() . 'knowledge_group')
                ->row();
        }

        $this->db->join(db_prefix() . 'staff s', ' FIND_IN_SET(s.department, ' . db_prefix() . 'knowledge_group.department) ', 'LEFT');
        $query = $this->db->order_by('name', 'asc')
            ->group_by(db_prefix() . 'knowledge_group.id')
            ->get(db_prefix() . 'knowledge_group');

        return $query->result_array();
    }

    /**
     * Edit customer group
     * @param  array $data $_POST data
     * @return boolean
     */
    public function edit($data)
    {
        $id = $data['id'];
        unset($data['id']);

        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'knowledge_group', $data);
        if ($this->db->affected_rows() > 0) {
            log_activity('Knowledge Group Updated [ID:' . $id . ']');

            return true;
        }

        return false;
    }

    /**
     * Delete customer group
     * @param  mixed $id group id
     * @return boolean
     */
    public function delete($id)
    {
        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'knowledge_group');
        if ($this->db->affected_rows() > 0) {
            log_activity('Knowledge Group Deleted [ID:' . $id . ']');

            return true;
        }

        return false;
    }

    public function get_folders($where = [])
    {

        // If the user is an admin, no need for additional checks
        if (is_admin()) {
            $query = $query = $this->db->select("f.name key,f.name as name,'true' as isDirectory,'true' hasSubDirectories,updated_date as  modify_date,0 as size,f.id,f.created_by as created_name,f.parent_id,'directory' as type,'' modify_name,created_date")
                ->from(db_prefix() . 'knowledge_base_folder f')
                ->where($where)
                ->get();
        } else {
            // If the user is not an admin, perform additional checks
            $department = get_staff_user_department();
            // Select the necessary fields
            $this->db->select('f.*');
            // Set the FROM clause
            $this->db->from(db_prefix() . 'knowledge_base_folder f');
            // Join with knowledge_group table
            $this->db->join(db_prefix() . 'knowledge_group g', '(g.id IN (f.group_ids) AND g.status=1 AND f.status=1)');
            // Join with staff table

            if (!empty($department)) {
                $this->db->join(db_prefix() . 'staff s', 's.department IN (g.department) OR s.staffid IN (g.staff_ids) and (s.department=' . $department . ' or s.staffid = ' . get_staff_user_id() . ')');
            } else {
                $this->db->join(db_prefix() . 'staff s', 's.department IN (g.department) OR s.staffid IN (g.staff_ids) and s.staffid = ' . get_staff_user_id() . '');
            }
            // If the department is not empty, add it as a condition
            $this->db->where($where);


            // Add the current user's staff ID as a condition
            // $this->db->where('s.staffid', get_staff_user_id());
            // Group by folder ID
            $this->db->group_by('f.id');
            // Execute the query
            $query = $this->db->get();
        }
        // Get the result array

        return $result = $query->result_array();
    }


    public function get_files($where = [])
    {
        // If the user is an admin, no need for additional checks
        if (is_admin()) {
            $query = $this->db->select("fs.*")
                ->from(db_prefix() . 'knowledge_base_files fs')
                ->where($where)
                ->get();
        } else {
            // If the user is not an admin, perform additional checks
            $department = get_staff_user_department();
            // Select the necessary fields
            $this->db->select('fs.*');
            // Set the FROM clause
            $this->db->from(db_prefix() . 'knowledge_base_files fs');
            // Join with knowledge_base_folder table
            $this->db->join(db_prefix() . 'knowledge_base_folder f', '(f.id = fs.folder_id and f.status=1 )');
            // Join with knowledge_group table
            $this->db->join(db_prefix() . 'knowledge_group g', '(g.id IN (f.group_ids) AND g.status=1)');
            // Join with staff table
            // $this->db->join(db_prefix() . 'staff s', 's.department IN (g.department) OR s.staffid IN (g.staff_ids)');
            if (!empty($department)) {
                $this->db->join(db_prefix() . 'staff s', 's.department IN (g.department) OR s.staffid IN (g.staff_ids) and (s.department=' . $department . ' or s.staffid = ' . get_staff_user_id() . ')');
            } else {
                $this->db->join(db_prefix() . 'staff s', 's.department IN (g.department) OR s.staffid IN (g.staff_ids) and s.staffid = ' . get_staff_user_id() . '');
            }
            // Add where conditions
            $this->db->where($where);

            // If the department is not empty, add it as a condition
            // if (!empty($department)) {
            //     // $this->db->where_in('g.department,s.department');
            //     $this->db->where('s.department', $department);
            // }
            // $this->db->or_where('s.staffid', get_staff_user_id());
            // Group by folder ID
            $this->db->group_by('fs.id');
            // Execute the query
            $query = $this->db->get();
        }
        // Get the result array
        $result = $query->result_array();
        return $result;
    }

    public function get_knowledge_base_dir($where_folder = [], $where_file = [], $return = 0)
    {
        // $folder_data = $this->db->select("
        // f.id as key,
        // f.name as name,
        // f.name as _name,
        // 'true' as isDirectory,
        // IF((SELECT COUNT(1) FROM " . db_prefix() . "knowledge_base_folder WHERE parent_id = f.id) > 0, 'true', 'false') AS hasSubDirectories,
        // updated_date as modify_date,
        // 0 as size,
        // f.created_by as created_name,
        // f.parent_id,
        // 'directory' as type,
        // '' as modify_name,
        // created_date")
        //     ->from(db_prefix() . "knowledge_base_folder f")
        //     ->where($where_folder)
        //     ->get()
        //     ->result_array();

        // $file_data = $this->db->select("
        // fs.id as key,
        // CONCAT(fs.name,'.',type) as _name,
        // fs.name as name,
        // 'false' as isDirectory,
        // 'false' as hasSubDirectories,
        // updated_date as modify_date,
        // '2.5MB' as size,
        // 'file' as type")
        //     ->from(db_prefix() . "knowledge_base_files fs")
        //     ->where($where_file)
        //     ->get()
        //     ->result_array();

        // return array_merge($folder_data, $file_data);


        $folder_data = $this->db->select("
        f.id as key,
        f.name as name,
        f.name as _name,
        'true' as isDirectory,
        IF((SELECT COUNT(1) FROM " . db_prefix() . "knowledge_base_folder WHERE parent_id = f.id) > 0, 'true', 'false') AS hasSubDirectories,
        updated_date as lastModifiedDate,
        created_date as creationDate,
        0 as size,
        f.created_by as created_name,
        f.parent_id,
        'directory' as type,
        '' as modify_name,
        created_date")
            ->from(db_prefix() . "knowledge_base_folder f")
            ->where($where_folder)
            ->get()
            ->result_array();

        $file_data = $this->db->select("
        fs.id as key,
        CONCAT(fs.name,'.',type) as name,
        fs.name as _name,
        'false' as isDirectory,
        'false' as hasSubDirectories,
        updated_date as lastModifiedDate,
        created_date as creationDate,
        '2.5MB' as size,
        'file' as type")
            ->from(db_prefix() . "knowledge_base_files fs")
            ->where($where_file)
            ->get()
            ->result_array();

        return array_merge($folder_data, $file_data);
    }
}
