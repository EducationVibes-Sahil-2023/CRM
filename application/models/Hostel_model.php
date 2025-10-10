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
  public function hostel_payment_data($hostel_info_id)
{
    // 🧩 Fetch quotations
    $allQuotations = $this->db
        ->select("*")
        ->from(db_prefix() . "hostel_quotation")
        ->where("hostel_info_id", $hostel_info_id)
        ->get()
        ->result_array();

    // 🧾 Fetch fee master list
    $feesList = $this->db
        ->select("id, name")
        ->from(db_prefix() . "applicant_fees")
        ->get()
        ->result_array();

    $feesDetails = array_column($feesList, 'name', 'id');

    // 🧩 Fetch payments
    $allPayments = $this->db
        ->select("*")
        ->from(db_prefix() . "hostel_payments")
        ->where("hostel_info_id", $hostel_info_id)
        ->where("status >", 0)
        ->get()
        ->result_array();

    // Initialize structures
    $quotationDetails    = [];
    $quotationAmountData = [];
    $paymentAmountData   = [];
    $pendingAmountData   = [];

    // ✅ Process quotations
    foreach ($allQuotations as $index => $quotation) {
        $quotationId = $quotation['id'];
        $quotationKey = sprintf(
            '%s-%s-%s-%s-Q%d',
            $quotation["university_name"],
            $quotation["start_date"],
            $quotation["end_date"],
            $quotation["room_capacity"],
            $index + 1
        );

        $quotationDetails[$quotationKey] = [
            "quotation_id" => $quotationId,
            "quotation"    => [],
            "payment"      => [],
        ];

        $hostelId = $quotation["hostel_info_id"];
        $decoded  = json_decode($quotation["hostel_due"], true);

        if (!empty($decoded["main"]["fees_info"]) && is_array($decoded["main"]["fees_info"])) {
            foreach ($decoded["main"]["fees_info"] as $fee) {
                $feeId    = $fee["id"] ?? null;
                $inrValue = isset($fee["inr_value"]) ? (float)$fee["inr_value"] : 0;

                if (!$feeId) continue;

                // Initialize totals per quotation
                if (!isset($quotationAmountData[$quotationId][$feeId])) {
                    $quotationAmountData[$quotationId][$feeId] = 0;
                }
                $quotationAmountData[$quotationId][$feeId] += $inrValue;

                // Add to details
                if (!isset($quotationDetails[$quotationKey]["quotation"][$feeId])) {
                    $quotationDetails[$quotationKey]["quotation"][$feeId] = [
                        "fee_name" => $feesDetails[$feeId] ?? "Unknown Fee",
                        "total_inr" => 0
                    ];
                }
                $quotationDetails[$quotationKey]["quotation"][$feeId]["total_inr"] += $inrValue;
            }
        }
    }

    // ✅ Process payments per quotation
    foreach ($allPayments as $payment) {
        $paymentQuotationId = $payment['quotation_id'] ?? null; // make sure you have this field in payments table
        if (!$paymentQuotationId) continue;

        $decoded = json_decode($payment["fess_infomation"], true); // check spelling

        if (!is_array($decoded)) continue;

        foreach ($decoded as $fee) {
            $feeId    = $fee["fee_id"] ?? null;
            $inrValue = isset($fee["fee_inr_value"]) ? (float)$fee["fee_inr_value"] : 0;

            if (!$feeId) continue;

            // Initialize totals per quotation
            if (!isset($paymentAmountData[$paymentQuotationId][$feeId])) {
                $paymentAmountData[$paymentQuotationId][$feeId] = 0;
            }
            $paymentAmountData[$paymentQuotationId][$feeId] += $inrValue;

            // Add to quotationDetails
            foreach ($quotationDetails as &$details) {
                if ($details['quotation_id'] == $paymentQuotationId) {
                    if (!isset($details["payment"][$feeId])) {
                        $details["payment"][$feeId] = [
                            "fee_name" => $feesDetails[$feeId] ?? "Unknown Fee",
                            "paid_inr" => 0
                        ];
                    }
                    $details["payment"][$feeId]["paid_inr"] += $inrValue;
                }
            }
        }
    }

    // ✅ Compute pending amounts per quotation
    foreach ($quotationAmountData as $quotationId => $fees) {
        foreach ($fees as $feeId => $quotationValue) {
            $paid = $paymentAmountData[$quotationId][$feeId] ?? 0;
            $pendingAmountData[$quotationId][$feeId] = $quotationValue - $paid;
        }
    }

    // ✅ Return structured data
    return [
        'feesDetails'         => $feesDetails,
        'quotationAmountData' => $quotationAmountData,
        'paymentAmountData'   => $paymentAmountData,
        'pendingAmountData'   => $pendingAmountData,
        'quotationDetails'    => $quotationDetails,
    ];
}

}
