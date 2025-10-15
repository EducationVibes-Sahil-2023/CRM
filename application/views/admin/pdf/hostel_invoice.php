<!-- tcpdf_invoice.html -->
<style>
    * {

        padding: 0px;
        margin: 0px;
        font-family: 'dejavusans', sans-serif;
    }

    .small {
        font-size: 14px;
        line-height: 10px;
    }

    .medium {
        font-size: 16px;
        line-height: 12px;
    }

    .large {
        font-size: 16px;
        line-height: 20px;
    }

    .bold {
        font-weight: bold;
    }

    /* .highlight {
        background-color: yellow !important;
        padding: 6px !important;
        line-height: 20px !important;
        font-size: 16px !important;
    } */
</style>

<?php
$ci = &get_instance();

$get_currencies = array_column(get_currencies(), null, "id");
$hostel_due = json_decode($hostelData->hostel_due, true)["main"];
$hostelDues = $hostel_due["fees_info"];
$paymentMode = $hostel_due["pay_info"][0]["payMode"];

$amountValue = $hostelDues[0]["amount"];
$amountCurrency = $hostelDues[0]["currency_id"];

$modes = $ci->quotation_model->payment_mod();
$modes = array_column($modes, "name", "id");
$logoPath = FCPATH . $hostelData->hostel_logo;

function numberToWord($num)
{
    $map = [
        1 => 'Single',
        2 => 'Double',
        3 => 'Triple',
        4 => 'Four',
        5 => 'Five',
        6 => 'Six',
    ];

    return $map[$num] ?? 'unknown';
}

?>
<h3 class="invoice-title">INVOICE</h3>
<table>
    <tr>
        <td style="width:70%;" class="small">
            <p>Invoice Number: INVOICE-HM-00<?= htmlspecialchars($hostelData->id ?? '') ?></p>
            <p>Invoice Date: <?= !empty($hostelData->created_date) ? date('d-m-Y', strtotime($hostelData->created_date)) : '' ?></p>
            
            <p>Payment Terms: CASH IN RECEPTION/BANK</p>
            <!--<p>Payment Terms: <?= htmlspecialchars($modes[$paymentMode] ?? '') ?></p>-->
            <p>Payment Date: <?= !empty($hostelData->created_date) ? date('d-m-Y', strtotime($hostelData->created_date . ' +1 day')) : '' ?></p>
        </td>
        <td style="width:30%; text-align:right;">
            <?php if (file_exists($logoPath)): ?>
                <img src="<?= $logoPath ?>" width="200">
            <?php endif; ?>
        </td>
    </tr>

</table>
<br>
<hr>

<table>
    <tr>
        <td style="width:50%;" class="small">
            <p>Company Name: <?= $hostelData->beneficiary_name ?></p>
            <p>ID: 405757809</p>
            <?php if (!empty($hostelData->hostel_name)): ?>
                <!--<p>Company Name: <?= htmlspecialchars($hostelData->hostel_name) ?></p>-->
            <?php endif; ?>

            <?php if (!empty($hostelData->hostel_address)): ?>
                <p>Address: <?= htmlspecialchars($hostelData->hostel_address) ?></p>
            <?php endif; ?>

           

            <?php if (!empty($hostelData->contact_number)): ?>
                <p>Phone: <?= htmlspecialchars($hostelData->contact_number) ?></p>
            <?php endif; ?>

            <?php if (!empty($hostelData->email)): ?>
                <p>Email: <?= htmlspecialchars($hostelData->email) ?></p>
            <?php endif; ?>
        </td>


        <td style="width:50%;" class="medium">
            <p><strong>Recipient: <?= $hostelData->name ?? '' ?></strong></p>
            <br>
            <br>
            <br>
            <br>
            <br>
            <p><strong>Passport No: <?= $hostelData->passport ?? '' ?></strong></p>

        </td>
    </tr>
</table>
<br>
<hr>
<br>
<table>

    <tr class="medium bold">
        <th style="width:50%; text-align:left;">
            <p>Item</p>
        </th>
        <th style="width:50%; text-align:right;">
            <p>Total</p>
        </th>
    </tr>

    <hr>
    <tr class="medium bold">
        <td>
            <p>Food &amp; Accommodation for <?= numberToWord($hostelData->room_capacity) ?> Sharing Room</p>
            <p><?= $amountValue ?? '' ?><?= $get_currencies[$amountCurrency]["symbol"] ?> per month </p>
            <p><?= $hostelData->month_difference ?? '' ?> Months Contract (<?= !empty($hostelData->start_date) ? date('d-m-Y', strtotime($hostelData->start_date)) : '' ?> ~ <?= !empty($hostelData->end_date) ? date('d-m-Y', strtotime($hostelData->end_date)) : '' ?>)</p>
            <p>One Time Payment</p>
        </td>
        <td style="text-align:right">
            <p> <?= ((int)$amountValue * (int)$hostelData->month_difference) ?? '' ?> <?= $get_currencies[$amountCurrency]["symbol"] ?></p>
        </td>
    </tr>
    <tr class="medium bold">
        <td></td>
        <td class="bold" style="text-align:right">
            <p>Total: <?= ((int)$amountValue * (int)$hostelData->month_difference) ?? '' ?><?= $get_currencies[$amountCurrency]["symbol"] ?></p>
            <!-- <p>(Equivalent in Gel)</p> -->
        </td>
    </tr>
    <tr class="small">
        <td colspan="2">
            <p class="bold"><?= $hostelData->bank_header ?></p>
        </td>
    </tr>
    <tr class="small">
        <td>

            <p>Beneficiary’s Bank</p>
            <p>Bank Code</p>
            <p>Beneficiary’s IBAN</p>
            <p>Name of Beneficiary </p>
            <p>Address</p>
        </td>
        <td>
            <p><?= $hostelData->beneficiary_bank ?></p>
            <p><?= $hostelData->bank_code ?></p>
            <p><?= $hostelData->beneficiary_iban ?></p>
            <p><?= $hostelData->beneficiary_name ?></p>
            <p>29a Gagarin Street,Tbilisi 0160, Georgia </p>
        </td>
    </tr>
</table>
<p></p>
<?php if (!empty($hostelData->beneficiary_iban_usd)) { ?>
    <hr>
    <p></p>
    <table class="small">
        <tr>
            <p class="bold"> FOR U.S. DOLLAR INTERNATIONAL TRANSFER</p>
        </tr>
        <tr class="small">
            <td>
                <p> Beneficiary’s Bank</p>
                <p> Bank Code </p>
                <p> Beneficiary’s IBAN </p>
                <p> Name of Beneficiary</p>
                <p> Address</p>
                
            </td>
            <td>
                <p><?= $hostelData->beneficiary_bank ?></p>
                <p><?= $hostelData->bank_code ?></p>
                <p><?= $hostelData->beneficiary_iban_usd ?></p>
                <p><?= $hostelData->beneficiary_name ?></p>
                <p>29a Gagarin Street,Tbilisi 0160, Georgia </p>
            </td>
        </tr>
    </table>
    <p></p>
<?php } ?>
<hr>
<table>
    <tr class="small">
        <td>
            <p>Intermediary bank</p>
            <p>SWIFT Code</p>
            <!--<p>Beneficiary Bank</p>-->
            <!--<p></p>-->
            <!--<p>Beneficiary</p>-->
            <!--<p>Account</p>-->
        </td>
        <td>
            <p>Citibank N.A., New York, USA</p>
            <p>CITIUS33</p>
            <!--<p>Bank of Georgia, SWIFT : BAGAGE22; 29a Gagarin Street,</p>-->
            <!--<p>Tbilisi 0160, Georgia </p>-->
            <!--<p>Education Vibes LLP</p>-->
            <!--<p>GE95BG0000000606359971</p>-->
        </td>
    </tr>
</table>
<p></p>
<hr>
<p></p>
<table>
    <tr>
        <td>
            <p class="large bold"><?= $hostelData->note ?></p>
            <p class="large"><strong>Note:</strong> Kindly make the payment by <span style="background-color: yellow; font-weight: bold; padding: 2px 4px;"><?= !empty($hostelData->created_date)
                                                                                                            ? date('d-m-Y', strtotime($hostelData->created_date . ' +1 day'))
                                                                                                            : ''
                                                                                                        ?></span>. In the description of the bank receipt must mention Food & Accommodation for the
                student name, invoice number and passport number clearly</p>
        </td>
    </tr>
</table>
<p></p>