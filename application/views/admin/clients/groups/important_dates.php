<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php 
$important_dates = get_client_important_dates($client_id);
?>
<div class="row">
<div class="col-md-12">
<div class="form-container">
    
     <div class="card">
                <h4>Important Dates</h4>
                <hr>

                <?php
                if(is_admin()){
                // echo "<pre>";
                // print_r($important_dates);
                // echo "</pre>";
                }
                $important = !empty($important_dates) && isset($important_dates[0])
                    ? $important_dates[0]
                    : null;
                ?>

                <?php if ($important): ?>

                    <table class="table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Dates</th>
                            </tr>
                        </thead>

                    <tbody>
                        <?php foreach($important_dates as $imp) { ?>

    <tr>
        <td>Admission Letter Rec. Date (<?= $imp->university_name ?? '' ?> - <?= $imp->priority ?? '' ?>)</td>
<td><?= display_date($imp->application_updated_date ?? null) ?></td>
    </tr>
    
     <?php } ?>

    <tr>
        <td>Deposite Pay Date </td>
        <td><?= display_date($imp->fees_deposite_date ?? null) ?></td>
    </tr>

    <tr>
        <td>Leg Pay Date </td>
        <td><?= display_date($imp->leg_payment_date ?? null) ?></td>
    </tr>

    <tr>
        <td>Leg Applied Date </td>
        <td><?= display_date($imp->leg_applied_date ?? null) ?></td>
    </tr>

    <tr>
        <td>Date of Receiving (Invitation Rec. date) </td>
        <td><?= display_date($imp->invitation_receiving_date ?? null) ?></td>
    </tr>
    
 

    <tr>
        <td>Visa Apply Date</td>
        <td><?= display_date($important->apply_date ?? null) ?></td>
    </tr>

    <tr>
        <td>Visa Rec. Date</td>
        <td><?= display_date($important->receiving_date ?? null) ?></td>
    </tr>

    <tr>
        <td>Fly Date</td>
        <td><?= display_date($important->fly_date ?? null) ?></td>
    </tr>

</tbody>
                    </table>

                <?php else: ?>

                    <div class="alert alert-info">
                        Important dates are not available.
                    </div>

                <?php endif; ?>

            </div>
</div>
</div>
</div>
            