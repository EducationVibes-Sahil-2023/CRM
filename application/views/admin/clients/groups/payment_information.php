<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
$ci = &get_instance();

$get_clients_fees = get_clients_fees_details(2, $client_id);
$client_information = $this->clients_model->getAdmissionPreferences($client_id);

$FessAmounts  =  array_column($get_clients_fees, null, 'id');
$FeesInformation = array_column(university_applicant_fees_payments(["lead_type" => 2]), null, "id");
$applicantpaymentdata = $ci->quotation_model->applicant_payment_data($client_id, "", "1");
$get_currencies = get_currencies();
$currency_lookup = array_column($get_currencies, NULL, 'id');

$orignal_amount = [];
$remaningDues = [];
$deduction_amount = [];
$refund_amount = [];

// if (!empty($applicantpaymentdata)) {
//     foreach ($applicantpaymentdata as $applicantPayment) {
//         $deduction_amount[$applicantPayment["payment_type"]][$applicantPayment["ex_currency"]] += $applicantPayment['amount'] ?? 0;
//     }
// }

if (!empty($applicantpaymentdata) && is_array($applicantpaymentdata)) {

    foreach ($applicantpaymentdata as $applicantPayment) {

        // ✅ Validate JSON field exists
        if (empty($applicantPayment['fess_infomation'])) {
            continue;
        }

        // ✅ Decode safely
        $FeesInformation_array = json_decode($applicantPayment['fess_infomation'], true);

        if (empty($FeesInformation_array) || !is_array($FeesInformation_array)) {
            continue;
        }

        foreach ($FeesInformation_array as $applicantPayment_) {

            // ✅ Validate required keys
            if (
                !isset($applicantPayment_['fee_id']) ||
                !isset($applicantPayment_['fee_currency'])
            ) {
                continue;
            }

            $fee_id = $applicantPayment_['fee_id'];
            $currency = $applicantPayment_['fee_currency'];

            $fee_amount = isset($applicantPayment_['fee_amount'])
                ? (float)$applicantPayment_['fee_amount']
                : 0;

            $fee_inr_value = isset($applicantPayment_['fee_inr_value'])
                ? (float)$applicantPayment_['fee_inr_value']
                : 0;

            // ✅ Initialize arrays
            if (!isset($refund_amount[$fee_id])) {
                $refund_amount[$fee_id] = [];
            }

            if (!isset($deduction_amount[$fee_id])) {
                $deduction_amount[$fee_id] = [];
            }

            // ✅ CHECK PAYMENT TYPE
            if (isset($applicantPayment["payment_type"]) && $applicantPayment["payment_type"] == RETURN_FEES_ID) {

                if ($fee_id == 1) {
                    $refund_amount[$fee_id][3] = ($refund_amount[$fee_id][3] ?? 0) + $fee_inr_value;
                } else {
                    $refund_amount[$fee_id][$currency] = ($refund_amount[$fee_id][$currency] ?? 0) + $fee_amount;
                }

            } else {

                if ($fee_id == 1) {
                    $deduction_amount[$fee_id][3] = ($deduction_amount[$fee_id][3] ?? 0) + $fee_inr_value;
                } else {
                    $deduction_amount[$fee_id][$currency] = ($deduction_amount[$fee_id][$currency] ?? 0) + $fee_amount;
                }

            }
        }
    }
}
?>

<style>
    .border-card {
        margin-bottom: 10px;
        text-align: left;
        padding: 10px 15px;
        box-shadow: 1px 1px 6px 1px lightgray;
        border-radius: 10px;
        background-color: #fff;
    }

    .border-card h5 {
        font-size: 15px;
        margin: 0px;
        margin-bottom: 5px;
    }

    .border-card p {
        margin: 2px 0;
    }
</style>


<div class="row">
    <?php if (!empty($FeesInformation)): ?>
        <?php foreach ($FeesInformation as $FeesInfo): ?>
            <?php
// print_r($FeesInfo);
if(strtolower($client_information->primary_country) == "georgia")
{
if(in_array($FeesInfo["id"],[2,4,8,7]))
{
    continue;
    
}
}
else
{
    if(in_array($FeesInfo["id"],[2,4,8]))
{
    continue;
    
}
}


            $feeId   = $FeesInfo['id'] ?? null;
            $feeName = $FeesInfo['name'] ?? 'N/A';
            $default_currency = $FeesInfo['default_currency'] ?? 3;
            if ($feeId === null) continue;

            // Get currency ID safely
            $currencyId = $FessAmounts[$feeId]['currency_id'] ?? 3;

            // Get total fee amount safely
            $totalAmountRaw = $FessAmounts[$feeId]['total_amount'] ?? $currency_lookup[$default_currency]['symbol'] . "0";
            $totalAmount = (int) $FessAmounts[$feeId]['amount'] ?? 0;
       
// if(strtolower($client_information->primary_country) == "georgia")
// {
//             if($feeId == 3)
//             {
                
//                 $feeName .=" + Medical";
//                 if($FessAmounts[7]["currency_id"] == $FessAmounts[$feeId]["currency_id"] )
//                 {
//                     $totalAmountRaw = $currency_lookup[$FessAmounts[$feeId]["currency_id"]]['symbol']."".($FessAmounts[7]['amount'] + $FessAmounts[$feeId]['amount']);
                    
//                     $totalAmount = ($FessAmounts[7]['amount'] + $FessAmounts[$feeId]['amount']);
                    
//                 }
//             }
// }

if (
    isset($client_information->primary_country) &&
    strtolower($client_information->primary_country) === "georgia" &&
    isset($feeId) && $feeId == 3 &&
    isset($FessAmounts[7], $FessAmounts[$feeId]) &&
    is_array($FessAmounts[7]) &&
    is_array($FessAmounts[$feeId])
) {

    $feeName .= " + Medical";

    $currencyIdMain = $FessAmounts[$feeId]['currency_id'] ?? null;
    $currencyIdMedical = $FessAmounts[7]['currency_id'] ?? null;

    $amountMain = isset($FessAmounts[$feeId]['amount']) ? (float)$FessAmounts[$feeId]['amount'] : 0;
    $amountMedical = isset($FessAmounts[7]['amount']) ? (float)$FessAmounts[7]['amount'] : 0;

    // ✅ Check currency match + lookup exists
    if (
        $currencyIdMain !== null &&
        $currencyIdMain === $currencyIdMedical &&
        isset($currency_lookup[$currencyIdMain]['symbol'])
    ) {
        $totalAmount = $amountMain + $amountMedical;

        $symbol = $currency_lookup[$currencyIdMain]['symbol'];

        $totalAmountRaw = $symbol . $totalAmount;
    }
}
            // Initialize original amount
            $orignal_amount[$feeId][$currencyId] = $totalAmount;
            $remaningDues[$feeId][$currencyId] = $totalAmount;

            // Get deductions safely
            $deductions = $deduction_amount[$feeId] ?? [];
            $refunds = $refund_amount[$feeId] ?? [];

            // Calculate total deductions
            $totalDeductions = 0;
            foreach ($deductions as $key => $d_FeesRaw) {
                $d_Fees = (int) str_replace(',', '', $d_FeesRaw);
                $totalDeductions += $d_Fees;
                $orignal_amount[$feeId][$key] -= $d_Fees;
                // $remaining[$feeId][$key] -= $d_Fees;
            }
            
          
            $totalRefund = 0;
              foreach ($refunds as $key => $r_FeesRaw) {
                $r_Fees = (int) str_replace(',', '', $r_FeesRaw);
                $totalRefund += $r_Fees;
                $orignal_amount[$feeId][$key] += $r_Fees;
                // $remaining[$feeId][$key] -= $d_Fees;
            }


            ?>
            <div class="col-md-3">
                <div class="border-card">
                    <h5><?= htmlspecialchars($feeName) ?></h5>
                    <p><strong>Fees:</strong> <?= $totalAmountRaw ?></p>
                    <p><strong>Payments:</strong>
                        <?php if (!empty($deductions)): ?>
                            <?php $paymentIndex = 0; ?>
                            <?php foreach ($deductions as $key => $d_FeesRaw): ?>
                                <?php
                                $d_Fees = (int) str_replace(',', '', $d_FeesRaw);
                                $currencySymbol = $currency_lookup[$key]['symbol'] ?? $currency_lookup[$default_currency]['symbol'];
                                ?>
                                <?= ($paymentIndex > 0) ? '+' : '' ?>
                                <?= htmlspecialchars($currencySymbol) . $d_Fees ?>
                                <?php $paymentIndex++; ?>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <?= $currency_lookup[$default_currency]['symbol'] ?> 0
                        <?php endif; ?>
                    </p>
                    <p><strong>Refund:</strong>
                        <?php if (!empty($refunds)): ?>
                            <?php $paymentIndex = 0; ?>
                            <?php foreach ($refunds as $key => $d_FeesRaw): ?>
                                <?php
                                $d_Fees = (int) str_replace(',', '', $d_FeesRaw);
                                $currencySymbol = $currency_lookup[$key]['symbol'] ?? $currency_lookup[$default_currency]['symbol'];
                                ?>
                                <?= ($paymentIndex > 0) ? '+' : '' ?>
                                <?= htmlspecialchars($currencySymbol) . $d_Fees ?>
                                <?php $paymentIndex++; ?>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <?= $currency_lookup[$default_currency]['symbol'] ?> 0
                        <?php endif; ?>
                    </p>
                    <p><strong>Dues:</strong> <?php if (!empty($orignal_amount[$feeId])): ?>
                            <?php $paymentIndex = 0; ?>
                            <?php foreach ($orignal_amount[$feeId] as $key => $d_FeesRaw): ?>
                                <?php
                                                        $d_FeesRaw;
                                                        $d_Fees = (int)$d_FeesRaw;
                                                        $currencySymbol = $currency_lookup[$key]['symbol'] ?? $currency_lookup[$default_currency]['symbol'];
                                ?>
                                <?= ($paymentIndex > 0) ? ',' : '' ?>
                                <?php
                                                        if ($d_Fees < 0) {
                                                            echo '-' . htmlspecialchars($currencySymbol) . abs($d_Fees);
                                                        } else {
                                                            echo htmlspecialchars($currencySymbol) . $d_Fees;
                                                        }

                                ?>
                                <?php $paymentIndex++; ?>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <?= $currency_lookup[$default_currency]['symbol'] ?> 0
                        <?php endif; ?>
                    </p>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="alert alert-warning">No fee information available.</div>
    <?php endif; ?>
</div>