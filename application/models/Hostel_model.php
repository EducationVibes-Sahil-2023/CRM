<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Hostel_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function get_hostel_rentInfo()
    {
        return
            $this->db->query("SELECT 
        hostel_id,
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
        GROUP BY hostel_id;
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

    public function hostel_paymentData($getId, $payment_id = "")
    {
        try {
            $this->db->select("*")
                ->from(db_prefix() . "hostel_payments")
                ->where("hostel_info_id", $getId);

            if (!empty($payment_id)) {
                $this->db->where("id", $payment_id);
            }

            // Optional: only active records (if you store a status column)
            // $this->db->where("status", 1);

            $query = $this->db->get();

            return !empty($payment_id) ? $query->row() : $query->result_array();
        } catch (Exception $e) {
            log_message('error', 'Error fetching applicant quotation data: ' . $e->getMessage());
            return [];
        }
    }
    public function hostel_payment_data($hostel_info_id)
    {
        // 🧩 Fetch quotations
        $allQuotations = $this->db
            ->select("*, TIMESTAMPDIFF(MONTH, start_date, end_date) 
            + (DAY(end_date) >= DAY(start_date)) AS month_difference")
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
        $refundAmountData    = [];
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
                "refund"       => [],
            ];

            $decoded = json_decode($quotation["hostel_due"], true);

            if (!empty($decoded["main"]["fees_info"]) && is_array($decoded["main"]["fees_info"])) {
                foreach ($decoded["main"]["fees_info"] as $fee) {
                    $feeId    = $fee["id"] ?? null;
                    if (!$feeId) continue;

                    $inrValue = isset($fee["amount"]) ? (float)$fee["amount"] * $quotation["month_difference"] : 0;

                    if (!isset($quotationAmountData[$quotationId][$feeId])) {
                        $quotationAmountData[$quotationId][$feeId] = 0;
                    }
                    $quotationAmountData[$quotationId][$feeId] += $inrValue;

                    $quotationDetails[$quotationKey]["quotation"][$feeId] = [
                        "fee_name"  => $feesDetails[$feeId] ?? "Unknown Fee",
                        "currency_id" => $fee["currency_id"] ?? "",
                        "total_inr" => $quotationAmountData[$quotationId][$feeId]
                    ];
                }
            }
        }

        // ✅ Process payments (including refunds)
        foreach ($allPayments as $payment) {
            $quotationId = $payment['quotation_id'] ?? null;
            if (!$quotationId) continue;


            $decoded = json_decode($payment["fess_infomation"], true);
            if (!is_array($decoded)) continue;

            foreach ($decoded as $fee) {
                $feeId = $fee["fee_id"] ?? null;
                if (!$feeId) continue;

                $inrValue = isset($fee["fee_amount"]) ? (float)$fee["fee_amount"] : 0;
                $isRefund = isset($payment["payment_type"]) && $payment["payment_type"] == RETURN_FEES_ID;

                foreach ($quotationDetails as &$details) {
                    if ($details['quotation_id'] != $quotationId) continue;

                    if ($isRefund) {
                        // 🔄 Refund logic
                        if (!isset($refundAmountData[$quotationId][$feeId])) {
                            $refundAmountData[$quotationId][$feeId] = 0;
                        }
                        $refundAmountData[$quotationId][$feeId] += $inrValue;

                        if (!isset($details["refund"][$feeId])) {
                            $details["refund"][$feeId] = [
                                "fee_name"   => $feesDetails[$feeId] ?? "Unknown Fee",
                                "currency_id" => $payment["ex_currency"] ?? "",
                                "refund_inr" => 0
                            ];
                        }
                        $details["refund"][$feeId]["refund_inr"] += $inrValue;

                        // Subtract refunded from paid
                        if (isset($details["payment"][$feeId])) {
                            $details["payment"][$feeId]["paid_inr"] -= $inrValue;
                            if ($details["payment"][$feeId]["paid_inr"] < 0) {
                                $details["payment"][$feeId]["paid_inr"] = 0;
                            }
                        }
                    } else {
                        // 💰 Normal payment logic
                        if (!isset($paymentAmountData[$quotationId][$feeId])) {
                            $paymentAmountData[$quotationId][$feeId] = 0;
                        }
                        $paymentAmountData[$quotationId][$feeId] += $inrValue;

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

        // ✅ Compute pending amounts (considering refunds)
        foreach ($quotationAmountData as $quotationId => $fees) {
            foreach ($fees as $feeId => $totalFee) {
                $paid    = $paymentAmountData[$quotationId][$feeId] ?? 0;
                $refunds = $refundAmountData[$quotationId][$feeId] ?? 0;

                // Refund reduces payment made
                $netPaid = max(0, $paid - $refunds);

                $pendingAmountData[$quotationId][$feeId] = max(0, $totalFee - $netPaid);
            }
        }

        // ✅ Return structured data
        return [
            'feesDetails'         => $feesDetails,
            'quotationAmountData' => $quotationAmountData,
            'paymentAmountData'   => $paymentAmountData,
            'refundAmountData'    => $refundAmountData,
            'pendingAmountData'   => $pendingAmountData,
            'quotationDetails'    => $quotationDetails,
        ];
    }
}
