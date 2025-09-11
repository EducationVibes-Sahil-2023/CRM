<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Quotation_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function quotation_add($data)
    {
        $fees_array   = [];
        $quotation_id = $data['quotation_id'] ?? null;

        // ✅ Build fees array
        if (!empty($data["applicant_fees"])) {
            foreach ($data["applicant_fees"] as $applicant_fee) {
                if (
                    !isset($data[$applicant_fee]) ||
                    !isset($data[$applicant_fee . "_id"]) ||
                    !isset($data[$applicant_fee . "_currency_type"])
                ) {
                    continue;
                }

                $fees_array[] = [
                    "university_name" => $data["university"],
                    "acadmic_year"    => $data["acadmic_year"],
                    "year"            => $data["study_year"],
                    "amount"          => $data[$applicant_fee],
                    "fees_id"         => $data[$applicant_fee . "_id"],
                    "currency_id"     => $data[$applicant_fee . "_currency_type"],
                    "created_by"      => get_staff_user_id(),
                    "created_at"      => date('Y-m-d H:i:s')
                ];
            }
        }

        // ✅ Quotation data
        $quotation_data = [
            "university_name" => $data["university"],
            "acadmic_year"    => $data["acadmic_year"],
            "year"            => $data["study_year"],
        ];

        $this->db->trans_start();

        if ($quotation_id) {
            // Update existing quotation
            $quotation_data["updated_by"] = get_staff_user_id();
            $quotation_data["updated_at"] = date('Y-m-d H:i:s');

            $this->db->update(
                db_prefix() . 'university_quotation',
                $quotation_data,
                ['id' => $quotation_id]
            );

            // Delete old fees safely
            $this->db->delete(db_prefix() . 'applicant_quotation_fees_details', [
                'university_name' => $data["university"],
                'acadmic_year'    => $data["acadmic_year"],
                'year'            => $data["study_year"]
            ]);
        } else {
            // Insert new quotation
            $quotation_data["created_by"] = get_staff_user_id();
            $quotation_data["created_at"] = date('Y-m-d H:i:s');

            $this->db->insert(db_prefix() . 'university_quotation', $quotation_data);
            $quotation_id = $this->db->insert_id();
        }

        // Insert new fees
        if (!empty($fees_array)) {
            $this->db->insert_batch(db_prefix() . 'applicant_quotation_fees_details', $fees_array);
        }

        $this->db->trans_complete();

        return $this->db->trans_status() ? $quotation_id : false;
    }


    public function quotation_data($id)
    {
        $this->db->where('id', $id);
        $quotation = $this->db->get(db_prefix() . 'university_quotation')->row();

        if ($quotation) {
            // Fetch associated fees
            $this->db->where([
                'university_name' => $quotation->university_name,
                'acadmic_year'    => $quotation->acadmic_year,
                'year'            => $quotation->year
            ]);
            $fees = $this->db->get(db_prefix() . 'applicant_quotation_fees_details')->result_array();
            $quotation->fees = $fees;
        }

        return $quotation;
    }



    public function payment_mod()
    {
        try {
            $modes = $this->db
                ->select("*")
                ->from(db_prefix() . "quotation_mode")
                ->get()
                ->result_array();

            return $modes ?: []; // return empty array if no records
        } catch (Exception $e) {
            log_message('error', 'Error fetching payment modes: ' . $e->getMessage());
            return [];
        }
    }

    public function payment_mode_vendors()
    {
        try {
            $vendors = $this->db
                ->select("*")
                ->from(db_prefix() . "quotation_vendor")
                ->where("status", 1) // ✅ fixed syntax ("status" = 1 ❌ → correct is ("status", 1))
                ->get()
                ->result_array();

            return $vendors ?: [];
        } catch (Exception $e) {
            log_message('error', 'Error fetching vendors: ' . $e->getMessage());
            return [];
        }
    }

    public function applicant_quotation_data($client_id, $quotation_id = "")
    {
        try {
            $this->db->select("*")
                ->from(db_prefix() . "applicant_quotation_payment")
                ->where("client_id", $client_id);

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

    public function applicant_payment_data($client_id, $payment_id = "")
    {
        try {
            $this->db->select("*")
                ->from(db_prefix() . "payment_quotations")
                ->where("client_id", $client_id);

            if (!empty($payment_id)) {
                $this->db->where("id", $payment_id);
            }

            // Optional: only active records (if you store a status column)
            // $this->db->where("status", 1);

            $query = $this->db->get();

            return !empty($payment_id) ? $query->row() : $query->result_array();
        } catch (Exception $e) {
            log_message('error', 'Error fetching applicant payment data: ' . $e->getMessage());
            return [];
        }
    }
}
