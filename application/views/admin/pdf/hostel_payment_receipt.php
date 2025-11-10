<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }

        .text-blue {
            color: #273991;
            font-weight: bold;
        }

        .text-pink {
            color: #e11285;
            font-weight: bold;
        }

        table {
            font-size: 16px;
        }

        .company-info {
            font-size: 13px;
            color: grey;
        }

        .container {
            border-top: 3px solid #1569a4;
            border-bottom: 3px solid black;
            border-left: 3px solid black;
            border-right: 3px solid black;
        }

        .bordered-row td {
            border-top: 3px solid black;
            border-bottom: 3px solid black;
        }
    </style>

</head>

<body>

<?php
$uniData = $university_details[strtolower($hostelData->university_name)] ?? [];

/* -------------------------
   1. Get quoted amount (ID = 6)
------------------------- */
$qArray = [];
if (!empty($hostelData->hostel_due)) {
    $decoded = json_decode($hostelData->hostel_due, true);
    if (!empty($decoded['main']['fees_info'])) {
        $feesIndexed = array_column($decoded['main']['fees_info'], null, 'id');
        $qArray = $feesIndexed[$payment_type] ?? [];
    }
}

$totalCurrency = !empty($qArray["document_currency"])
    ? get_currency($qArray["document_currency"])->symbol
    : '';

$totalQuotedValue = !empty($qArray["inr_value"]) ? floatval($qArray["inr_value"]) : 0;
$totalAmount = $totalCurrency . " " . $totalQuotedValue;

/* -------------------------
   2. Sum payments (ID = 6)
------------------------- */
$pArray = !empty($hostelData->fess_infomation)
    ? json_decode($hostelData->fess_infomation, true)
    : [];

$totalPaidValue   = 0;
$paidCurrency     = $totalCurrency;
$pArray = array_merge(...$pArray);
if (!empty($pArray)) {
    foreach ($pArray as $pay) {
        if (
            !empty($pay["fee_id"]) &&
            $pay["fee_id"] == $payment_type &&
            !empty($pay["document_currency"]) &&
            $pay["document_currency"] == $qArray["document_currency"]
        ) {
  
         $totalPaidValue += floatval($pay["fee_inr_value"]);
            $paidCurrency = get_currency($pay["document_currency"])->symbol;
        }
    }
}

 $total_payment = $paidCurrency . " " . $totalPaidValue;

/* -------------------------
   3. Balance
------------------------- */
$balanceValue = $totalQuotedValue - $totalPaidValue;
$formattedBalance = $totalCurrency . " " . number_format($balanceValue, 2, '.', ',');

/* -------------------------
   Format Helper
------------------------- */
function formatCurrencyText($amountString) {
    if (!empty($amountString) && preg_match('/^(\D*)(\d[\d,.]*)$/', $amountString, $matches)) {
        $symbol = $matches[1];
        $num    = floatval(str_replace(',', '', $matches[2]));
        return $symbol . number_format($num, 2, '.', ',');
    }
    return '';
}
?>

    <div class="container" cellspacing="15">
        <table cellpadding="5" cellspacing="0" style="margin:10px; padding:10px;">
            <tbody>
                <tr>
                    <td class="company-info">
                        <img style="height:80px;" src="<?= base_url($hostelData->hostel_logo) ?>">
                    </td>
                    <td></td>
                    <td style="text-align:right;">
                        <img style="height:100px;" src="<?= $uniData["logo_image"]?>">
                    </td>
                </tr>
                <tr>
                    <td colspan="3"></td>
                </tr>
                <tr>
                    <td class="text-blue">Receipt</td>
                    <td></td>
                    <td><span class="text-blue">Invoice No</span>: <?= $invoice_number ?></td>
                </tr>

                <tr>
                    <td><span class="text-pink">Date of Payment</span></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td><?= !empty($hostelData->pay_date) ? date("d/m/Y", strtotime($hostelData->pay_date)) : '' ?></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td colspan="3"></td>
                </tr>
                <tr>
                    <td><strong>Student Name</strong></td>
                    <td><strong></strong></td>
                    <td><strong>Academic Year</strong></td>
                </tr>
                <tr>
                    <td><?= !empty($hostelData->name) ? ucwords($hostelData->name) : '' ?></td>
                    <td></td>
                    <td><?= !empty($hostelData->acadmic_year) ? $hostelData->acadmic_year : '' ?></td>
                </tr>
                <tr>
                    <td colspan="3"></td>
                </tr>
                <tr>
                    <td colspan="2"><strong>University Name</strong></td>
                    <td><strong>Country</strong></td>
                </tr>
                <tr>
                    <td colspan="2"><?= !empty($hostelData->university_name) ? ucwords($hostelData->university_name) : '' ?></td>
                    <td><?= !empty($uniData["country_name"]) ? ucwords($uniData["country_name"]) : '' ?></td>
                </tr>

                <tr>
                    <td colspan="3"></td>
                </tr>

                <tr class="bordered-row">
                    <td colspan="2" style="border-left: 3px solid black;">
                        <span class="text-blue">Payment Description</span>
                        <br>
                        Payment Received
                    </td>
                    <td style="padding: 10px; text-align:right; border-right: 3px solid black;">
                        <span class="text-blue">Total Payment &nbsp; &nbsp;</span><br>
                        <?= formatCurrencyText($total_payment) ?> &nbsp; &nbsp;
                    </td>
                </tr>

                <tr>
                    <td colspan="3"></td>
                </tr>
                <tr class="bordered-row">
                    <td colspan="2" style="border-left: 3px solid black;">
                        <span class="text-blue">Balance Details</span><br>
                        Total Service Charge<br>
                        Total Service Charged Received till date<br>
                        <span class="text-pink">Balance Due</span>
                    </td>
                    <td style="text-align: right; border-right: 3px solid black;">
                        <span class="text-blue">Amount &nbsp; &nbsp;</span><br>
                        <?= formatCurrencyText($totalAmount) ?> &nbsp; &nbsp;<br>
                        <?= formatCurrencyText($total_payment) ?> &nbsp; &nbsp;<br>
                        <span class="text-pink"><?= formatCurrencyText($formattedBalance) ?> &nbsp; &nbsp;</span>
                    </td>
                </tr>

                <tr>
                    <td colspan="3" style="text-align: center; font-size:xx-small;">
                        <br><br>
                        *This is a computer generated Receipt and doesn't require signature or any company seal. If you have any questions about this invoice, please contact on<br>
                        <?= !empty($hostelData->email) ? ucwords($hostelData->email) : '' ?> or Call <?= !empty($hostelData->contact_number) ? ucwords($hostelData->contact_number) : '' ?>.<br>
                    </td>
                </tr>

            </tbody>
        </table>
    </div>
</body>

</html>
