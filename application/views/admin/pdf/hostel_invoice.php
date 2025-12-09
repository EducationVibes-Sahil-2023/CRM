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
$amountCurrency = $hostelDues[0]["document_currency"];

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
<h2 class="invoice-title">INVOICE</h2>
<table>
    <tr>
        <td style="width:70%;" class="small">
            <p>Invoice Number: INVOICE-HM-00<?= htmlspecialchars($hostelData->id ?? '') ?></p>
            <p>Invoice Date: <?= !empty($hostelData->created_date) ? date('d-m-Y', strtotime($hostelData->created_date)) : '' ?></p>
            <p>Payment Terms: CASH IN RECEPTION/BANK</p>
            <p>Payment Date: <?= !empty($hostelData->created_date) ? date('d-m-Y', strtotime($hostelData->created_date . ' +1 day')) : '' ?></p>
        </td>
        <td style="width:30%; text-align:right;">
            <?php if (file_exists($logoPath) &&  !empty($hostelData->hostel_logo)): ?>
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
            <?php if (!empty($hostelData->company_name)): ?>
                <p>Company Name: <?= $hostelData->company_name ?></p>
            <?php endif; ?>
            <?php if (!empty($hostelData->company_id)): ?>
                <p>ID: <?= $hostelData->company_id ?></p>
            <?php endif; ?>
            <?php if (!empty($hostelData->address)): ?>
                <p>Address: <?= htmlspecialchars($hostelData->address) ?></p>
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
            <p><?= !empty($hostelData->service_name) ? $hostelData->service_name : 'Food And Accommodation' ?> <?php if (!empty($hostelData->room_capacity)) { ?>for <?= numberToWord($hostelData->room_capacity) ?> Sharing Room <?php } ?></p>
            <p><?= $amountValue ?? '' ?><?= $get_currencies[$amountCurrency]["symbol"] ?> <?php if (!empty($hostelData->room_capacity)) { ?>per month<?php } ?> </p>
            <p><?= $hostelData->month_difference ?? '' ?> Months Contract (<?= !empty($hostelData->start_date) ? date('d-m-Y', strtotime($hostelData->start_date)) : '' ?> ~ <?= !empty($hostelData->end_date) ? date('d-m-Y', strtotime($hostelData->end_date)) : '' ?>)</p>
            <br>
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
    <tr>
        <td colspan="2"></td>
    </tr>
    <tr class="small">
        <td colspan="2">
            <p class="bold"><?= $hostelData->beneficiary_headline ?></p>
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
            <p><?= $hostelData->beneficiary_code ?></p>
            <p><?= $hostelData->beneficiary_iban ?></p>
            <p><?= $hostelData->beneficiary_name ?></p>
            <p><?= $hostelData->beneficiary_address ?></p>
        </td>
    </tr>
</table>
<p></p>
<hr>
<?php if (!empty($hostelData->usd_beneficiary_bank)) { ?>
    <table class="small">
        <tr>
            <p class="bold"><?= $hostelData->usd_beneficiary_headline ?></p>
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
                <p><?= $hostelData->usd_beneficiary_bank ?></p>
                <p><?= $hostelData->usd_beneficiary_code ?></p>
                <p><?= $hostelData->usd_beneficiary_iban ?></p>
                <p><?= $hostelData->usd_beneficiary_name ?></p>
                <p><?= $hostelData->usd_beneficiary_address ?></p>
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

        </td>
        <td>
            <p><?= $hostelData->intermediary_bank ?></p>
            <p><?= $hostelData->intermediary_swift_code ?></p>
        </td>
    </tr>
</table>
<p></p>
<hr>
<p></p>
<table>
    <tr>
        <td>
            <p class="large bold"><?= $hostelData->footer_note ?></p>
            <p class="large"><strong>Note:</strong> Kindly make the payment by <span style="background-color: yellow; font-weight: bold; padding: 2px 4px;"><?= !empty($hostelData->created_date)
                                                                                                                                                                ? date('d-m-Y', strtotime($hostelData->created_date . ' +1 day'))
                                                                                                                                                                : ''
                                                                                                                                                            ?></span>. In the description of the bank receipt must mention <?= !empty($hostelData->service_name) ? $hostelData->service_name : 'Food And Accommodation' ?> for the
                student name, invoice number and passport number clearly</p>
        </td>
    </tr>
</table>
<p></p>