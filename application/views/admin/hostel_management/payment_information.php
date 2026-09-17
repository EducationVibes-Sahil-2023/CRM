<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<?php
$ci = &get_instance();

$applicantPaymentData = $ci->Hostel_model->hostel_payment_data($hostel_info_id);
$quotationDetails     = $applicantPaymentData["quotationDetails"] ?? [];


$get_currencies        = get_currencies();
$currency_lookup       = array_column($get_currencies, NULL, 'id');
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
        margin: 0px 0 5px 0;
    }

    .border-card p {
        margin: 2px 0;
    }
</style>

<div class="row">
    <?php if (!empty($quotationDetails)): ?>
        <?php foreach ($quotationDetails as $quotationKey => $quotation): ?>
            <div class="col-md-12">
                <h4><?= $quotationKey ?></h4>
                <hr>
                <?php
                $quotationFees     = $quotation['quotation'] ?? [];
                $quotationPayments = $quotation['payment'] ?? [];
                $quotationRefunds  = $quotation['refund'] ?? [];
                ?>
                <?php foreach ($quotationFees as $feeId => $feeData): ?>
                    <?php
                    $feeName     = $feeData['fee_name'] ?? 'N/A';
                    $totalAmount = $feeData['total_inr'] ?? 0;

                    $currencyId     = $feeData["currency_id"] ?? 3; // fallback
                    $currencySymbol = $currency_lookup[$currencyId]['symbol'] ?? '₹';

                    // Payment amount for this fee
                    $paidAmount   = $quotationPayments[$feeId]['paid_inr'] ?? 0;

                    // Refund amount for this fee
                    $refundAmount = $quotationRefunds[$feeId]['refund_inr'] ?? 0;

                    // Remaining due
                    $dueAmount = $totalAmount - $paidAmount;
                    ?>
                    <div class="col-md-3 mb-3">
                        <div class="border-card p-3 shadow-sm rounded">
                            <h5 class="fw-semibold mb-2"><?= htmlspecialchars($feeName) ?></h5>

                            <p class="mb-1">
                                <strong>Fees:</strong>
                                <?= htmlspecialchars($currencySymbol) . number_format($totalAmount, 2) ?>
                            </p>

                            <p class="mb-1">
                                <strong>Payments:</strong>
                                <?= htmlspecialchars($currencySymbol) . number_format($paidAmount, 2) ?>
                            </p>

                            <p class="mb-1">
                                <strong>Refunds:</strong>
                                <?= htmlspecialchars($currencySymbol) . number_format($refundAmount, 2) ?>
                            </p>

                            <p class="mb-0">
                                <strong>Dues:</strong>
                                <?= htmlspecialchars($currencySymbol) . number_format($dueAmount, 2) ?>
                            </p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

        <?php endforeach; ?>
    <?php else: ?>
        <div class="col-12">
            <div class="alert alert-warning text-center">No fee information available.</div>
        </div>
    <?php endif; ?>
</div>