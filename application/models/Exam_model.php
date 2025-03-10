<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Exam_model extends App_Model
{
    public function __construct()
    {

        parent::__construct();
    }

    public function insert_update($data)
    {
        try {
            // Validate required fields
            if (empty($data["university_name"]) || empty($data["exam_id"]) || empty($data["name"])) {
                return ["status" => false, "message" => "Missing required fields"];
            }

            if (!empty($data["id"])) {
                return $this->update_batch($data);
            } else {
                unset($data["id"]); // Remove ID for a new record
                $where = [
                    "university_name" => $data["university_name"],
                    "exam_id" => $data["exam_id"],
                    "name" => $data["name"],
                    "exam_date" => !empty($data["batch_date"]) ? $data["batch_date"] : '0000-00-00'
                ];




                $existingBatch = $this->check_batch($where);

                if (!empty($existingBatch[0]["id"])) {
                    $data["id"] = !empty($existingBatch[0]["id"]) ? $existingBatch[0]["id"] : "";
                }

                if (!empty($existingBatch[0]["id"])) {
                    // If record exists, update it
                    return $this->update_batch($data);
                } else {
                    // If no record exists, create a new batch
                    return $this->create_batch($data);
                }
            }
        } catch (Exception $e) {
            log_message('error', 'Insert/Update failed: ' . $e->getMessage());
            return ["status" => false, "message" => "An unexpected error occurred"];
        }
    }

    public function create_batch($data)
    {
        try {
            $this->db->insert(db_prefix() . 'exam_batch', $data);
            $insert_id = $this->db->insert_id();

            if ($insert_id) {
                return ["status" => true, "message" => "Batch created successfully", "id" => $insert_id];
            } else {
                return ["status" => false, "message" => "Failed to create batch"];
            }
        } catch (Exception $e) {
            log_message('error', 'Create Batch failed: ' . $e->getMessage());
            return ["status" => false, "message" => "An unexpected error occurred"];
        }
    }

    public function update_batch($data)
    {
        try {
            $this->db->where('id', $data["id"]);
            $updated = $this->db->update(db_prefix() . 'exam_batch', $data);

            if ($updated) {
                return ["status" => true, "message" => "Batch updated successfully", "id" => $data["id"]];
            } else {
                return ["status" => false, "message" => "No changes made to batch", "id" => $data["id"]];
            }
        } catch (Exception $e) {
            log_message('error', 'Update Batch failed: ' . $e->getMessage());
            return ["status" => false, "message" => "An unexpected error occurred"];
        }
    }

    public function check_batch($where)
    {
        try {
            $result = $this->db->select("id")
                ->from(db_prefix() . "exam_batch")
                ->where($where)
                ->get()
                ->result_array();

            return !empty($result) ? $result : [];
        } catch (Exception $e) {
            log_message('error', 'Check Batch failed: ' . $e->getMessage());
            return [];
        }
    }

    public function insert_client_exams($client_exam_data)
    {
        try {
            // Ensure client_ids exist and are an array
            if (empty($client_exam_data["client_ids"]) || !is_array($client_exam_data["client_ids"])) {
                return ["status" => false, "message" => "No clients provided"];
            }

            // Prepare an array for batch insert
            $insertData = [];

            foreach ($client_exam_data["client_ids"] as $clientid) {
                $insertData[] = [
                    "client_id"  => $clientid,
                    "exam_date"  => $client_exam_data["exam_date"],
                    "exam_id"    => $client_exam_data["exam_id"],
                    "batch_id"   => $client_exam_data["batch_id"],
                ];
            }

            $this->db->where(array("batch_id" => $client_exam_data["batch_id"]))
                ->delete(db_prefix() . 'clients_exam');

            // Insert data into the database
            if (!empty($insertData)) {
                $this->db->insert_batch(db_prefix() . 'clients_exam', $insertData);
            }

            return ["status" => true, "message" => "Client exams inserted successfully"];
        } catch (Exception $e) {
            log_message('error', 'Insert Client Exams failed: ' . $e->getMessage());
            return ["status" => false, "message" => "An unexpected error occurred"];
        }
    }

    public function get_exam_batch($id)
    {
        try {
            $result = $this->db->select(db_prefix() . "exam_batch.*,GROUP_CONCAT(client_id) as client_ids")
                ->from(db_prefix() . "exam_batch")
                ->join(db_prefix() . "clients_exam", db_prefix() . "clients_exam.batch_id =" . db_prefix() . "exam_batch.id", "LEFT")
                ->where([db_prefix() . "exam_batch.id" => $id])
                ->get()
                ->row_array();

            if (!$result) {
                return false;
            }

            return $result;
        } catch (Exception $e) {
            log_message("error", "Error fetching exam batch: " . $e->getMessage());
            return false;
        }
    }
}
