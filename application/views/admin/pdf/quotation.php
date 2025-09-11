<?php

$universityName   = $applicantData->primary_university ?? '';
$countryName      = $applicantData->primary_country ?? '';
$acadmic_year     = $applicantData->acadmic_year ?? '';
$applicantName    = $applicantData->applicant_name ?? '';
$get_currencies   = get_currencies();
$currency_lookup  = array_column($get_currencies, null, 'id');

$company_dues_fees_array = $this->db
    ->select("*")
    ->from(db_prefix() . "company_dues_fees")
    ->order_by("pdf_sequence", "ASC")
    ->get()
    ->result_array();

$inrSymbol = $currency_lookup[3]["name"] ?? 'INR';
$ticketingLabel ="Remaining service charge for ticketing";
// Decode JSON safely
$university_dues           = json_decode($applicant_quotation_data->university_due ?? '{}');
$university_pay_information = $university_dues->main->pay_info ?? [];
$company_due               = json_decode($applicant_quotation_data->company_due ?? '{}');
$company_due_additional     = json_decode($applicant_quotation_data->addition ?? '{}');
$company_pay_information   = $company_due->main->pay_info ?? [];
$totalInrValue             = $university_pay_information[0]->totalINRValue ?? 0;

// Merge company dues (main + additions) into one array
$company_due_array = [];
if (!empty($company_due->main)) {
    $company_due_array[] = $company_due->main;
}
if (!empty($company_due->addition)) {
    foreach ($company_due->addition as $a_com) {
        $company_due_array[] = $a_com;
    }
}

$university_due_array = [];
if (!empty($university_dues->main)) {
    $university_due_array[] = $university_dues->main;
}
if (!empty($university_dues->addition)) {
    foreach ($university_dues->addition as $a_com) {
        $university_due_array[] = $a_com;
    }
}



$serviceCharges = [];
$companyDues    = [];


// die;
// Loop through each company due block
foreach ($company_due_array as $key => $c_due_array) {
    $payInfo  = $c_due_array->pay_info ?? [];
    $feesInfo = array_column($c_due_array->info ?? [], null, "type");

    // Bank Info
    $bankInfo = [];
    if (!empty($payInfo[0]->payVendor) && isset($bankAccounts[$payInfo[0]->payVendor])) {
        $bankInfo = $bankAccounts[$payInfo[0]->payVendor];
    }

    // Process fees
    foreach ($company_dues_fees_array as $fd) {
        $feeData = $feesInfo[$fd['id']] ?? null;
        
//   print_r($feeData);
    if ($feeData && isset($feeData->fee_value_inr)) {
        
        if($key>0 && $fd['id']==2)
        {
             $serviceCharges[$key][$ticketingLabel]["inr"] =
        ($serviceCharges[$key][$ticketingLabel]["inr"] ?? 0) + $feeData->fee_value_inr;
            
        }
        else{
        
    $serviceCharges[$key][$fd['pdf_content']]["inr"] =
        ($serviceCharges[$key][$fd['pdf_content']]["inr"] ?? 0) + $feeData->fee_value_inr;
        }

if($feeData->fee_currency!=3){
    $serviceCharges[$key][$fd['pdf_content']]["other"] = !empty($serviceCharges[$key][$fd['pdf_content']]["other"])?'+':''.$currency_lookup[$feeData->fee_currency]["name"]." ".$feeData->fee_value;
}
}

    }


    // Add Balance Due if available
    if (!empty($payInfo[0]->payAmount) && $payInfo[0]->payAmount > 0) {
      
                   $serviceCharges[$key]["Total amount to be paid in $bankInfo[name]"] = $payInfo[0]->payAmount;

        

    }

    // Add company bank details
    if (!empty($bankInfo)) {
        $companyDues[] = [
            "Name"        => $bankInfo["account_name"] ?? '',
            "Account No." => $bankInfo["account_number"] ?? '',
            "Bank Name"   => $bankInfo["bank_name"] ?? '',
            "IFSC Code"   => $bankInfo["ifsc_code"] ?? '',
            "Account Type"=> "Current Account",
            "Bank Address"=> $bankInfo["branch"] ?? ''
        ];
    }
}






$fees = [];
$bankDetails = [];
$totalAmount = [];
// Loop through each company due block
foreach ($university_due_array as $key => $c_due_array) {


    $payInfo  = $c_due_array->pay_info ?? [];
    $feesInfo = array_column($c_due_array->fees_info ?? [], null, "id");

    // Bank Info
    $bankInfo = [];
    if (!empty($payInfo[0]->payVendor) && isset($bankAccounts[$payInfo[0]->payVendor])) {
        $bankInfo = $bankAccounts[$payInfo[0]->payVendor];
    }

    // Process fees
foreach ($feesDetails as $fd) {
    $feeData = $feesInfo[$fd['id']] ?? null;
    if ($feeData && !empty($feeData->inr_value) && $feeData->inr_value > 0) {
        $currency = $currency_lookup[$feeData->currency_id]["name"] ?? '';
        $amount   = $feeData->amount ?? 0;
        $inrValue = $feeData->inr_value ?? 0;

        if ($amount > 0 && $inrValue > 0) {
            $fees[$key][$fd['pdf_content']] = $currency . " " . $amount . " = " . $inrSymbol . " " . $inrValue;
        }
    }
}

    // Add Balance Due if available
    if (!empty($payInfo[0]->totalINRValue) && $payInfo[0]->totalINRValue > 0) {
        $totalAmount[$key]["totalAmount"] = $payInfo[0]->totalINRValue;
    }

    // Add company bank details
    if (!empty($bankInfo)) {
        $bankDetails[] = [
            "Name"        => $bankInfo["account_name"] ?? '',
            "Account No." => $bankInfo["account_number"] ?? '',
            "Bank Name"   => $bankInfo["bank_name"] ?? '',
            "IFSC Code"   => $bankInfo["ifsc_code"] ?? '',
            "Account Type"=> "Current Account",
            "Bank Address"=> $bankInfo["branch"] ?? ''
        ];
    }
}


$letters = range('A', 'Z');

// Required documents
$requiredDocuments = [
    "Father/Mother Aadhaar Card & PAN Card (Front & back of Aadhaar card needed, along with PAN card)",
    "Transfer Slip/IMPS/NEFT/RTGS Details with UTR Number"
];


?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Due Notice — <?=$universityName?></title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
                    /*font-family: dejavusans, sans-serif;*/
            color: #222;
            /* line-height: 1.5; */
        }
       
        .text-center {
            text-align: center;
        }

        p {
            font-size: 15px;
        }


        .highlight {
            background-color:yellow;
            padding: 6px;
            line-height: 1.5;
            font-size: 16px;
            /*border-left: 4px solid #ffeeba;*/
        }

        .underline {
            text-decoration: underline;
        }

        .line-height-1 {
            line-height: 1;
        }

        .bank-information {
            line-height: 0.9;

        }
     
    </style>
</head>

<body>
    <div class="container">
        <h3 class="text-center"><?=$universityName?> (<?=$applicantName?>)</h3>
        <h3 class="text-center">Academic Year: <?=$acadmic_year?></h3>
        <br>
        <h4>Dear <?=$applicantName?>,</h4>

        <p>You are hereby requested to kindly clear your financial dues as your final process is pending, and we
            need to proceed with obtaining all necessary clearance documents for your travel to <?= $countryName ?>.
        </p>
<?php  foreach ($fees as $key => $f): ?>

        <h4 class="underline">Payments Need to be paid:</h4>
        <div class="bank-information">
            <?php $i = 0;
            foreach ($f as $label => $amount): ?>
                <p class="dejavusans"><?= $letters[$i] ?>. <?= htmlspecialchars($label) ?>: <?= htmlspecialchars($amount) ?></p>
            <?php $i++;
            endforeach; ?>
        </div>
        <p class="total-amount" style="font-family:dejavusans;">Total amount to be paid <?=$inrSymbol?> <strong><?= $totalAmount[$key]["totalAmount"] ?> /-</strong></p>
        <p>(# Variable as per prevailing exchange rate)</p>

        <h4>Foreign Remittance from India</h4>
        <p>Dedicated Forex Account for Education Vibes students with advanced security.</p>
        <h4 class="underline">Bank Account Details for Direct Transfer to <?= $countryName ?> in a Single Transaction:</h4>
        <div class="bank-information">
            <?php
            foreach ($bankDetails[$key] as $key => $value): ?>
                <p><strong><?= $key ?></strong>: <?= htmlspecialchars($value) ?></p>
            <?php endforeach; ?>
        </div>

 <?php endforeach; ?>
        <p>Once the transfer is completed, please update me with the UTR number or SMS of the amount
            debited from the account along with the UTR number.</p>
<div style="page-break-before: always;"></div>
        <h4 class="underline">List of Documents Needed to Issue Your Receipts for Travel to <?=$countryName?>:</h4>
        <?php foreach ($requiredDocuments as $doc): ?>
            <p><?= htmlspecialchars($doc) ?></p>
        <?php endforeach; ?>

        <h4 class="underline">Note:</h4>
        <span class="highlight">Strictly no cash deposits allowed in the following account. Only transfers from the parent’s bank
            account are accepted from the savings account only.
        </span>


<br>
<?php foreach ($companyDues as $key => $c_Dues): ?>
        <h4 class="underline"><?=$key==0?'Education Vibes Professional Fees:':'Amount to be paid for ticketing:'?></h4>
        <div class="bank-information">
            <?php foreach ($serviceCharges[$key] as $label => $amount):?>
                <p><strong><?= $label ?></strong>: <?=isset($amount["other"])?"(".$amount["other"].") = ":''?> <?=$inrSymbol?> <?= htmlspecialchars(isset($amount["inr"])?$amount["inr"]:$amount) ?></p>
            <?php endforeach; ?>
        </div>

        <p>Please pay the outstanding balance to the following account to proceed for the ticketing processing.
        </p>


        <div class="bank-information">
            <?php foreach ($c_Dues as $key => $value): ?>
                <p><strong><?= $key ?></strong>: <?= htmlspecialchars($value) ?></p>
            <?php endforeach; ?>
        </div>
       <?php endforeach; ?> 



        <h5>Financial obligations must be completed before departure; otherwise, you will not be allowed to
            fly. We will try to process your remittance as soon as possible.
        </h5>
        <br>
        <h5 class="underline">Thank You!</h5>
        <br>
        <br>
        <h5>Note: If you wish to provide pocket money for your child, please deposit it into the forex account
            so that they can carry the funds in dollars with them.</h5>

    </div>
</body>

</html>