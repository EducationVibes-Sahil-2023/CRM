<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<?php
$ci = &get_instance();

$get_clients_fees = get_clients_fees_details(2, $client_id);

$FessAmounts  =  array_column($get_clients_fees, null, 'id');
$FeesInformation = array_column(university_applicant_fees_payments(["lead_type" => 2]), null, "id");
$applicantpaymentdata = $ci->quotation_model->applicant_payment_data($client_id, "", "");
$get_currencies = get_currencies();
$currency_lookup = array_column($get_currencies, NULL, 'id');

$orignal_amount = [];
$remaningDues = [];
$deduction_amount = [];


// if (!empty($applicantpaymentdata)) {
//     foreach ($applicantpaymentdata as $applicantPayment) {
//         $deduction_amount[$applicantPayment["payment_type"]][$applicantPayment["ex_currency"]] += $applicantPayment['amount'] ?? 0;
//     }
// }

if (!empty($applicantpaymentdata)) {
    foreach ($applicantpaymentdata as $applicantPayment) {
        $FeesInformation_array = json_decode($applicantPayment['fess_infomation'],true);
  
        foreach ($FeesInformation_array as $applicantPayment_) {
            if($applicantPayment_["fee_id"] ==1){
        $deduction_amount[$applicantPayment_["fee_id"]][3] += $applicantPayment_['fee_inr_value'] ?? 0;
            }else
            {
                $deduction_amount[$applicantPayment_["fee_id"]][$applicantPayment_["fee_currency"]] += $applicantPayment_['fee_amount'] ?? 0; 
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
if(in_array($FeesInfo["id"],[2,4,8]))
{
    continue;
    
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
            // Initialize original amount
            $orignal_amount[$feeId][$currencyId] = $totalAmount;
            $remaningDues[$feeId][$currencyId] = $totalAmount;

            // Get deductions safely
            $deductions = $deduction_amount[$feeId] ?? [];


            // Calculate total deductions
            $totalDeductions = 0;
            foreach ($deductions as $key => $d_FeesRaw) {
                $d_Fees = (int) str_replace(',', '', $d_FeesRaw);
                $totalDeductions += $d_Fees;
                $orignal_amount[$feeId][$key] -= $d_Fees;
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