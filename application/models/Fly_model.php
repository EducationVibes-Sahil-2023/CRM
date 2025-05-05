<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Fly_model extends App_Model
{
    public function __construct()
    {

        parent::__construct();
    }

    public function insert_update($data)
    {
        try {
            // Validate required fields
            if (empty($data["name"])) {
                return ["status" => false, "message" => "Missing required fields"];
            }

            if (!empty($data["id"])) {
                return $this->update_batch($data);
            } else {
                unset($data["id"]); // Remove ID for a new record
                $where = [
                    "name" => $data["name"]
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
            $this->db->insert(db_prefix() . 'ticket_batch', $data);
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
            $updated = $this->db->update(db_prefix() . 'ticket_batch', $data);

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
                ->from(db_prefix() . "ticket_batch")
                ->where($where)
                ->get()
                ->result_array();

            return !empty($result) ? $result : [];
        } catch (Exception $e) {
            log_message('error', 'Check Batch failed: ' . $e->getMessage());
            return [];
        }
    }


    public function check_ticket_data($clients = [], $data = [])
    {
        foreach ($clients as $client_id) {
            $ticket = $this->db->select('ticket_status, id')
                ->from(db_prefix() . 'ticket_data')
                ->where('client_id', $client_id)
                ->order_by('id', 'DESC')
                ->limit(1)
                ->get()
                ->row();


            // Deny if another active ticket exists (status == 3), and we're not updating the same one
            if ($ticket && $ticket->ticket_status != 3 && (empty($data["id"]) &&  $ticket->id != $data["id"])) {
                $data = [
                    "status" => false,
                    "message" => "Cannot create ticket. Client " . get_client_name($client_id) . " already has an active ticket."
                ];
                return $data;
                die;
            }
        }

        return ["status" => true, "message" => "Ticket creation check passed."];
    }


    public function insert_client_ticket($client_exam_data, $auto = 0)
    {
        try {
            if (empty($client_exam_data["client_ids"]) || !is_array($client_exam_data["client_ids"])) {
                return ["status" => false, "message" => "No clients provided."];
            }

            // Validate invitation letters
            // $check_invitation = check_invitation_letter($client_exam_data["client_ids"]);
            // if (!empty($check_invitation["error"])) {
            //     return ["status" => false, "message" => implode(", ", $check_invitation["message"])];
            // }

            // Manual validation (check existing active ticket)
            if ($auto == 0) {
                $check = $this->check_ticket_data($client_exam_data["client_ids"], $client_exam_data);

                if (!$check["status"]) {
                    return $check;
                }
            }

            // Deactivate previous auto-generated tickets in the batch
            if ($auto == 1 && !empty($client_exam_data["batch_id"])) {
                $this->db->where([
                    "batch_id" => $client_exam_data["batch_id"],
                    "auto"     => 1
                ])->update(db_prefix() . 'ticket_data', ["status" => 0]);
            }

            // Fetch university info
            $get_primary_university = $this->get_primary_university_names($client_exam_data["client_ids"]);

            $insertData = [];
            $updateData = [];

            foreach ($client_exam_data["client_ids"] as $client_id) {
                $data = [
                    "client_id"          => $client_id,
                    "vendor_id"          => $client_exam_data["vendor_id"],
                    "ticket_cost"        => $client_exam_data["ticket_cost"],
                    "payment_date"       => $client_exam_data["payment_date"],
                    "payment_mode"       => $client_exam_data["payment_mode"],
                    "fly_date"           => $client_exam_data["fly_date"],
                    "departure_location" => $client_exam_data["departure_location"],
                    "auto"               => $auto,
                    "status"             => 1,
                    "ticket_status"      => 2,
                    "country_name"       => $get_primary_university[$client_id]["primary_country"] ?? '',
                    "university_name"    => $get_primary_university[$client_id]["primary_university"] ?? '',
                ];

                if (!empty($client_exam_data["batch_id"])) {
                    $data["batch_id"] = $client_exam_data["batch_id"];
                }



                // Handle single/manual (auto == 0) insert/update
                if ($auto == 0) {
                    if (!empty($client_exam_data["id"])) {
                        $data["id"] = $client_exam_data["id"];
                        $updateData[] = $data;
                        // break; // Only one client is allowed when auto == 0
                    } else {
                        $insertData[] = $data;
                    }
                }

                // For bulk/auto mode, check for existing entry
                if ($auto == 1) {
                    $existing = $this->db->where('client_id', $client_id);
                    if (!empty($client_exam_data["batch_id"])) {
                        $existing = $existing->where(['batch_id' => $client_exam_data["batch_id"], "auto" => 1]);
                    }
                    $existing = $existing->order_by("id", "DESC")->get(db_prefix() . 'ticket_data')->row();

                    if ($existing) {
                        $data['id'] = $existing->id;
                        $updateData[] = $data;
                    } else {

                        $check = $this->check_ticket_data([$data["client_id"]], $data);

                        if (!$check["status"]) {
                            return $check;
                        }
                        $insertData[] = $data;
                    }
                }
            }


            // Save to DB
            if (!empty($updateData)) {
                $this->db->update_batch(db_prefix() . 'ticket_data', $updateData, 'id');
            }

            if (!empty($insertData)) {
                $this->db->insert_batch(db_prefix() . 'ticket_data', $insertData);
            }

            if ($auto == 1 && !empty($client_exam_data["batch_id"])) {
                $this->db->where([
                    "batch_id" => $client_exam_data["batch_id"],
                    "status" => 0,
                    "auto"     => 1
                ])->update(db_prefix() . 'ticket_data', ["auto" => 0, 'batch_id' => 0,'status'=>1]);
            }
            return ["status" => true, "message" => "Ticket Fly Batch saved successfully."];
        } catch (Exception $e) {
            log_message('error', 'Insert Ticket Fly Batch failed: ' . $e->getMessage());
            return ["status" => false, "message" => "An unexpected error occurred."];
        }
    }


    function get_primary_university_names($userids = [])
    {
        $this->db->select('a.userid, a.primary_university,a.primary_country')
            ->from(db_prefix() . 'admission_preferences a');

        if (!empty($userids)) {
            $this->db->where_in("a.userid", $userids);
        }

        $result = $this->db->order_by('a.userid', 'ASC')
            ->get()
            ->result_array();

        return array_column($result, null, 'userid');
    }

    public function get_ticket_batch($id)
    {
        try {
            $result = $this->db->select("td.*,tb.*,GROUP_CONCAT(client_id) as client_ids")
                ->from(db_prefix() . "ticket_batch tb")
                ->join(db_prefix() . "ticket_data td", "td.batch_id =tb.id", "LEFT")
                ->where(["tb.id" => $id])
                ->order_by("tb.id", "")
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
