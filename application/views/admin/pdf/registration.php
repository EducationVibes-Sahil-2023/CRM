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
    function formatCurrency($amount)
    {
        if (!empty($amount) && preg_match('/^(\D*)(\d[\d,.]*)$/', $amount, $matches)) {
            $currency_symbol = $matches[1]; // Captures "$", "₹", "€", etc.
            $numeric_amount = floatval(str_replace(',', '', $matches[2])); // Removes commas & converts to float
            return $currency_symbol . number_format($numeric_amount, 2, '.', ','); // Formats to 2 decimal places
        }
        return !empty($amount) ? $amount : '';
    }

    function formatCurrency_($amount)
    {
        if (!empty($amount) && preg_match('/^(\D*)(\d[\d,.]*)$/', $amount, $matches)) {
            $currency_symbol = $matches[1]; // Captures "$", "₹", "€", etc.
            $numeric_amount = floatval(str_replace(',', '', $matches[2])); // Removes commas & converts to float
            return [$currency_symbol, $numeric_amount]; // Returns symbol and numeric value separately
        }
        return ['', 0]; // Default empty symbol and zero value
    }


    list($currency_symbol, $total_value) = formatCurrency_($total_amount);
    list(, $registration_value) = formatCurrency_($registration_amount);
    $total_value;
    $registration_value;
    // Calculate difference
    $difference = $total_value - $registration_value;

    ?>

    <div class="container" cellspacing="15">
        <table cellpadding="5" cellspacing="0" style="margin:10px; padding:10px;">
            <tbody>
                <tr>
                    <td class="company-info">
                        <img style="height:70px;" src="<?= base_url() ?>uploads/pdf_include/Brightroute_Logo_.png">
                        <br>
                        First Floor, Office No 37, 38 and 39, 1184/4,<br>
                        Shreenath Plaza, F C Road,<br>
                        Shivaji Nagar, Pune, Maharashtra, 411005
                    </td>
                    <td></td>
                    <td style="text-align:right;">
                        <img style="height:120px;" src="<?= base_url() ?>uploads/pdf_include/eduvibe_logo.png">
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
                    <td><?= !empty($date_of_payment) ? date("d/m/Y", strtotime($date_of_payment)) : '' ?></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td colspan="3"></td>
                </tr>
                <tr>
                    <td><strong>Student Name</strong></td>
                    <td><strong>Payment Received From</strong></td>
                    <td><strong>Academic Year</strong></td>
                </tr>
                <tr>
                    <td><?= !empty($student_name) ? ucwords($student_name) : '' ?></td>
                    <td><?= !empty($payment_recevied_from) ? ucwords($payment_recevied_from) : '' ?></td>
                    <td><?= !empty($acadmic_year) ? $acadmic_year : '' ?></td>
                </tr>
                <tr>
                    <td colspan="3"></td>
                </tr>
                <tr>
                    <td><strong>Residence Address</strong></td>
                    <td><strong>University Name</strong></td>
                    <td><strong>Country</strong></td>
                </tr>
                <tr>
                    <td><?= !empty($address) ? $address : '' ?></td>
                    <td><?= !empty($university_name) ? ucwords($university_name) : '' ?></td>
                    <td><?= !empty($country) ? ucwords($country) : '' ?></td>
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
                    <td style="padding: 10px;" style="text-align:right; border-right: 3px solid black; font-family:dejavusans;" >
                        <span class="text-blue">Total Payment &nbsp; &nbsp;</span><br>
                        <?= !empty($registration_amount) ? formatCurrency($registration_amount) : '' ?>
                        &nbsp; &nbsp;
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
                    <td style="text-align: right; border-right: 3px solid black; font-family:dejavusans;">
                        <span class="text-blue">Amount &nbsp; &nbsp;</span><br>
                        <?= !empty($total_amount) ? formatCurrency($total_amount) : '' ?> &nbsp; &nbsp;<br>
                        <?= !empty($registration_amount) ? formatCurrency($registration_amount) : '' ?> &nbsp; &nbsp;<br>
                        <span class="text-pink"><?= !empty($pending_amount) ? $currency_symbol . number_format($difference, 2, '.', ',') : '' ?> &nbsp; &nbsp;</span>
                    </td>
                </tr>

                <tr>
                    <td colspan="3" style="text-align: center; font-size:xx-small;">
                        <br>
                        <br>
                        *This is a computer generated Receipt and doesn't require signature or any company seal. If you have any questions about this invoice, please contact on<br>
                        admission@educationvibes.in or Call 8956992592*<br>
                        **18% GST Will be Applicable on the Total Service Charge at the time of Total Payment Completion**
                    </td>
                </tr>

            </tbody>
        </table>
    </div>
</body>

</html>