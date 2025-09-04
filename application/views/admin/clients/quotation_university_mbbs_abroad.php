<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>

<?php
$quotation_id = isset($quotation_id) ? $quotation_id : "";
$filter_data = filter_country_university_array(2);
$university_list = $filter_data['universities'];
$university_applicant_fees = university_applicant_fees(1, "", ["university_name" => $quotation_data->university_name ?? "", "acadmic_year" => $quotation_data->acadmic_year ?? "", "year" => $quotation_data->year ?? ""]);
$get_currencies = get_currencies();
$get_currencies = array_column($get_currencies, null, 'id');
?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body _buttons">
                        <h4>University MBBS Abroad Quotation</h4>
                        <hr class="hr-panel-heading" />
                        <div class="col-md-12">
                            <?php echo form_open(admin_url('quotations/create_mbbs_abroad'), ['id' => 'quotation-form']); ?>
                            <div class="row">
                                <input type="hidden" name="quotation_id" value="<?php echo $quotation_id; ?>">
                                <!-- University Dropdown -->
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <?php
                                        echo render_select(
                                            'university',
                                            $university_list,
                                            ['university_name', 'university_name'],
                                            'University <small class="text-danger">*</small>',
                                            [$quotation_data->university_name ?? ''],
                                            [
                                                'data-width' => '100%',
                                                'data-none-selected-text' => "University",
                                                'required' => 'required'
                                            ]
                                        );
                                        ?>
                                    </div>
                                </div>

                                <!-- Academic Year Dropdown -->
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <?php
                                        echo render_select(
                                            'acadmic_year',
                                            !empty($quotation_data->university_name)
                                                ? $filter_data['acadmic_year'][$quotation_data->university_name]
                                                : [], // blank initially
                                            ['id', 'name'],
                                            'Academic Year <small class="text-danger">*</small>',
                                            [$quotation_data->acadmic_year ?? ''],
                                            [
                                                'data-width' => '100%',
                                                'data-none-selected-text' => "Academic Year",
                                                'required' => 'required'
                                            ]
                                        );
                                        ?>

                                    </div>
                                </div>

                                <!-- Study Year Dropdown -->
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <?php
                                        $years = range(1, 8);
                                        $year_options = [];
                                        foreach ($years as $year) {
                                            $year_options[] = ['id' => $year, 'name' => $year . ' Year'];
                                        }

                                        echo render_select(
                                            'study_year',
                                            $year_options,
                                            ['id', 'name'],
                                            'Year <small class="text-danger">*</small>',
                                            [$quotation_data->year ?? ''],
                                            [
                                                'data-width' => '100%',
                                                'data-none-selected-text' => "Year",
                                                'required' => 'required'
                                            ]
                                        );
                                        ?>
                                    </div>
                                </div>
                            </div>

                            <?php if (!empty($university_applicant_fees) && !empty($get_currencies)) { ?>
                                <div id="applicant_fees" class="row">
                                    <?php foreach ($university_applicant_fees as $fees) {
                                        $id = $fees["id"];
                                        $field_name = strtolower(str_replace(" ", "_", $fees["name"]));
                                        $symbol = '$';
                                        if (!empty($fees["currency_id"]) && !empty($get_currencies[$fees["currency_id"]]["symbol"])) {
                                            $symbol = $get_currencies[$fees["currency_id"]]["symbol"];
                                        } elseif (empty($fees["currency_id"])) {
                                            $symbol = $get_currencies[$fees["university_quotation_currency"]]["symbol"];
                                        }
                                    ?>
                                        <div class="col-lg-3 col-md-4 col-6 fees-block-<?= $id ?>">
                                            <label><?= $fees['quotation_name'] ?> <small class="text-danger">*</small></label>
                                            <div class="input-group form-group">
                                                <input type="hidden" value="<?= $field_name ?>" name="applicant_fees[]">
                                                <input type="hidden" value="<?= $fees['id'] ?>" name="<?= $field_name ?>_id">
                                                <input type="hidden" value="<?= $fees['detail_id'] ?>" name="<?= $field_name ?>_detail_id_<?= $fees['id'] ?>">

                                                <div class="input-group-addon currency-symbol-<?= $id ?>">
                                                    <?= $symbol ?>
                                                </div>

                                                <input type="text"
                                                    name="<?= $field_name ?>"
                                                    required
                                                    class="form-control currency-amount fees_<?= $fees['id'] ?>"
                                                    placeholder="0.00"
                                                    value="<?= $fees["amount"] ?>">

                                                <div class="input-group-addon">
                                                    <select name="<?= $field_name ?>_currency_type"
                                                        class="currency-selector currency-selector-<?= $id ?>"
                                                        onchange="updateSymbol(<?= $id ?>)">
                                                        <?php foreach ($get_currencies as $c) { ?>
                                                            <option data-symbol="<?= $c['symbol'] ?>"
                                                                value="<?= $c['id'] ?>"
                                                                <?= ((!empty($fees['currency_id']) && $fees['currency_id'] == $c['id']) ||
                                                                    (empty($fees['currency_id']) && $fees['university_quotation_currency'] == $c['id']))
                                                                    ? 'selected' : '' ?>>
                                                                <?= $c['name'] ?>
                                                            </option>
                                                        <?php } ?>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    <?php } ?>
                                </div>
                            <?php } ?>

                            <div class="row text-right">
                                <button type="submit" class="btn btn-info mtop25">Submit</button>
                            </div>
                            <?php echo form_close(); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php init_tail(); ?>
<script>
    var universityAcadmicYear = <?php echo json_encode($filter_data['acadmic_year']); ?>;

    $(function() {
        // University Change Event
        $('select[name="university"]').on('change', function() {
            var university = $(this).val();
            var $yearDropdown = $('select[name="acadmic_year"]');

            $yearDropdown.html('<option value="">Select Academic Year</option>');

            if (university && universityAcadmicYear[university]) {
                $.each(universityAcadmicYear[university], function(i, year) {
                    $yearDropdown.append('<option value="' + year.id + '">' + year.name + '</option>');
                });
            }

            $yearDropdown.selectpicker('refresh');
        });

        // Form Validation + AJAX Submit
        appValidateForm($('#quotation-form'), {
            university: 'required',
            acadmic_year: 'required',
            study_year: 'required'
        }, quotationSubmitHandler);
    });

    function quotationSubmitHandler(form) {
        var $btn = $(form).find('[type="submit"]');
        $btn.prop('disabled', true).text('Submitting...');

        $.ajax({
            url: form.action,
            type: 'POST',
            data: $(form).serialize(),
            success: function(response) {
                response = JSON.parse(response);
                if (response.resp_code === 'ERR') {
                    alert_float('danger', response.resp_desc);
                    return;
                }
                alert_float('success', response.resp_desc);

                window.location.reload(); // or redirect

            },
            error: function(xhr) {
                alert_float('danger', 'Error: ' + xhr.responseText);
            },
            complete: function() {
                $btn.prop('disabled', false).text('Submit');
            }
        });
        return false; // prevent default
    }
</script>