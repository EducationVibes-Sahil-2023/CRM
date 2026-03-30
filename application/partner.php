<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head();
// $feesDetails = !empty($feesDetails)
//     ? json_decode($feesDetails, true)
//     : [];
echo "<pre>";
print_r($feesStructure);
$universityDetails = !empty($feesStructure["university_data"])
    ? json_decode($feesStructure["university_data"], true)
    : [];
$sectionDetails = !empty($feesStructure["section_data"])
    ? json_decode($feesStructure["section_data"], true)
    : [];
$feesDetails = $sectionDetails["fees"];
$other_charges = $sectionDetails["other_charges"];
$one_time_charges = $sectionDetails["one_time_charges"];
$services = $sectionDetails["services"];
$processing_fee = $sectionDetails["processing_fee"];
$contactInfo = !empty($feesStructure["contact_data"])
    ? json_decode($feesStructure["contact_data"], true)
    : [];

?>

<!-- Include Font Awesome for icons -->
<!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"> -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

<style>
    /* General Styles */
    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 15px;
    }

    th,
    td {
        border: 1px solid #ddd;
        padding: 8px;
        text-align: center;
        position: relative;
        vertical-align: middle;
    }

    th {
        background-color: #f2f2f2;
        font-weight: bold;
    }

    input,
    textarea {
        width: 100%;
        padding: 5px;
        box-sizing: border-box;
        border: 1px solid #ddd;
        border-radius: 3px;
        height: 30px;
    }

    input:focus,
    textarea:focus {
        outline: 2px solid #007bff;
        background: white;
    }

    td.flex {
        display: flex;
    }

    button {
        margin: 5px;
    }

    .table-section {
        margin-top: 30px;
        border: 1px solid #eee;
        padding: 15px;
        border-radius: 5px;
        background: white;
    }

    .university-title {
        font-size: 24px;
        font-weight: bold;
        margin-bottom: 5px;
    }

    .duration-badge {
        /* display: inline-block;
        background-color: #007bff;
        color: white;
        padding: 5px 15px;
        border-radius: 20px;
        font-size: 14px;
        margin-bottom: 20px; */
    }

    .installment-box {
        background-color: #f8f9fa;
        border: 2px dashed #007bff;
        padding: 15px;
        border-radius: 5px;
        text-align: center;
        margin-top: 20px;
    }

    /* Editable Table Styles */
    .editable-table {
        width: 100%;
    }

    .editable-table td {
        position: relative;
    }

    /* Action Buttons */
    .action-btn {
        padding: 8px 15px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        margin: 0 5px;
        transition: all 0.3s;
    }

    .action-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
    }

    .action-btn i {
        margin-right: 5px;
    }

    /* Section Header with Title and Input */
    .section-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 15px;
    }

    .section-title-input {
        width: 250px !important;
        font-weight: bold;
        border: 2px solid #007bff;
        border-radius: 20px;
        padding: 8px 15px;
    }

    .section-controls {
        display: flex;
        gap: 10px;
    }

    /* GST Badge */
    .gst-badge {
        display: inline-block;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 10px 20px;
        border-radius: 25px;
        font-weight: bold;
        margin: 10px 0;
    }

    .gst-badge input {
        background: transparent;
        border: none;
        color: white;
        font-weight: bold;
        text-align: center;
    }

    /* Contact Section */
    .contact-section {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 20px;
        border-radius: 10px;
        margin-top: 20px;
    }

    .contact-section input {
        background: rgba(255, 255, 255, 0.2);
        border: 1px solid rgba(255, 255, 255, 0.3);
        color: white;
    }

    .contact-section input::placeholder {
        color: rgba(255, 255, 255, 0.7);
    }

    .contact-section label {
        color: white;
        font-weight: normal;
    }

    .contact-section input.form-contro,
    .contact-section input::placeholder {
        color: black;
    }

    /* Table Footer */
    tfoot tr {
        font-weight: bold;
        background-color: #e9ecef;
    }

    tfoot input {
        background-color: #e9ecef;
        font-weight: bold;
    }

    /* University Info */
    .university-info {
        /* background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); */
        /* color: white; */
        /* padding: 20px; */
        border-radius: 10px;
        margin-bottom: 20px;
    }

    .university-info input {
        /* background: rgba(255, 255, 255, 0.2);
        border: 1px solid rgba(255, 255, 255, 0.3);
        color: white;
        font-weight: bold; */
    }

    .university-info input::placeholder {
        /* color: rgba(255, 255, 255, 0.7); */
    }

    .duration-badge {
        /* background-color: rgba(255, 255, 255, 0.2);
        color: white;
        padding: 5px 15px;
        border-radius: 20px;
        font-size: 14px;
        display: inline-block; */
    }

    .duration-badge input {
        /* background: transparent;
        border: none;
        color: white;
        width: 80px; */
        /* display: inline-block; */
    }

    /* Summary Cards */
    .summary-card {
        background: linear-gradient(135deg, #141e30 0%, #243b55 100%);
        color: white;
        border-radius: 10px;
        padding: 20px;
        margin-top: 20px;
    }

    .summary-item {
        text-align: center;
        padding: 10px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 5px;
    }

    .summary-item .label {
        font-size: 12px;
        opacity: 0.8;
    }

    .summary-item .value {
        font-size: 18px;
        font-weight: bold;
    }

    .preview_image {
        height: 100px !important;
        max-width: 200px;

    }

    .preview_image img {
        height: 100% !important;
        width: unset !important;

    }

    .preview_image img[src=""] {
        display: none;
    }

    .set-checkbox {
        display: inline-flex;
    }
</style>

<div id="wrapper">
    <div class="content">
        <iframe
            id="pdfFrame"
            style="width:794px; height:1200px; border:0;position: absolute;">
        </iframe>


        <div class="row">
            <div class="panel_s">
                <div class="panel-body">
                    <div class="row col-12">
                        <div class="col-lg-12">
                            <?php echo render_select('created_universities', $feesStructure_data, array('id', 'university_name_reagion'), 'Created Fees Structures', [$id]); ?>
                        </div>
                    </div>
                    <form id="feesStructure-form" onsubmit="return false;">
                        <!-- University Basic Info - Improved UI -->
                        <div class="university-info">
                            <div class="row">
                                <div class="col-md-4">
                                    <label>University Name</label>
                                    <input type="text" class="form-control form-control-lg"
                                        value="<?= $universityDetails["university_name"] ?? '' ?>" readonly
                                        placeholder="University Name" name="university_name" id="university_name"
                                        style="font-size: 24px; height: auto;">
                                </div>
                                <div class="col-md-2">
                                    <label>Region</label>
                                    <div class="region-badge">
                                        <input type="text" class="form-control form-control-lg" readonly name="region_name" value="<?= $universityDetails["region_name"] ?? '' ?> "
                                            placeholder="Regionḍ year" id="region_name">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <label>Founded Year</label>
                                    <div class="duration-badge">
                                        <input type="text" class="form-control form-control-lg" readonly name="founded_year" value="<?= $universityDetails["founded_year"] ?? '' ?> "
                                            placeholder="Founded year" id="founded_year">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <label>Country Name</label>
                                    <div class="duration-badge">
                                        <input type="text" class="form-control form-control-lg" name="country_name"
                                            readonly value="<?= $universityDetails["country_name"] ?? '' ?> "
                                            placeholder="Country Name" id="country_name">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <label>Program Duration</label>
                                    <div class="duration-badge">
                                        <input type="text" class="form-control form-control-lg" readonly
                                            value="<?= $universityDetails["duration"] ?? '' ?> " placeholder="Duration"
                                            name="program_duration" id="program_duration">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-lg-3">
                                <div class="form-group">
                                    <?php echo render_select('segment_type', $segment, array('id', 'name'), 'Segment Type', [$feesStructure["segment_id"] ?? ''], ["required" => "required"]); ?>
                                </div>
                            </div>
                            <div class="col-lg-3">
                                <?php echo render_select('countries', [], array('id', 'name'), 'Countries', []); ?>
                            </div>
                            <div class="col-lg-3">
                                <?php echo render_select('universities', [], array('id', 'name'), 'Universities', []); ?>
                            </div>

                            <div class="col-lg-3">
                                <?php echo render_select('region_type', $regions, array('id', 'name'), 'Region Type', [$feesStructure["region_id"] ?? '']); ?>
                            </div>

                        </div>
                        <input type="hidden" id="id" name="id" value="<?= $id ?>">

                        <div class="row">
                            <!-- <div class="col-lg-3">
                                <?php
                                echo render_input(
                                    'website_logo',
                                    "Website Logo",
                                    "",
                                    'file',
                                    ["accept" => "image/*", "id" => "website_logo"]
                                );
                                ?>
                                <div class="website_logo_preview preview_image">
                                    <img src="<?= $universityDetails["logo"] ?? '' ?>" id="website_logo_preview">
                                </div>
                            </div> -->
                            <div class="col-lg-3">
                                <?php echo render_input('year', "Duration", $universityDetails["year"] ?? '', 'number'); ?>
                            </div>
                            <div class="col-lg-3">
                                <?php
                                echo render_input(
                                    'university_logo',
                                    "University Logo",
                                    "",
                                    'file',
                                    ["accept" => "image/*", "id" => "university_logo"]
                                );
                                ?>
                                <div class="university_logo_preview preview_image">
                                    <img src="<?= $universityDetails["university_logo"] ?? '' ?>"
                                        id="university_logo_preview">
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <?php
                                echo render_input(
                                    'banner_image',
                                    "Banner Image",
                                    "",
                                    'file',
                                    ["accept" => "image/*", "id" => "banner_image"]
                                );
                                ?>
                                <div class="banner_image_preview preview_image">
                                    <img src="<?= $universityDetails["banner_image"] ?? '' ?>"
                                        id="university_banner_preview">
                                </div>
                            </div>
                        </div>

                        <!-- Fees Details Section -->
                        <div class="table-section">
                            <div class="section-header">
                                <div>
                                    <h3>Year Wise Fees Details</h3>
                                    <!-- <input type="text" class="form-control section-title-input" value="Year Wise Fees Details" id="fees_section_title"> -->
                                </div>
                                <div class="section-controls">
                                    <button type="button" class="btn btn-success action-btn" onclick="addFeesRow()">
                                        <i class="fa fa-plus"></i> Add Year
                                    </button>
                                    <button type="button" class="btn btn-danger action-btn" onclick="removeFeesRow()">
                                        <i class="fa fa-minus"></i> Remove Year
                                    </button>
                                </div>
                            </div>

                            <table id="feesTable" class="table table-bordered editable-table">
                                <thead>
                                    <tr>
                                        <td>
                                            <input type="text" name="fees_heading[]"
                                                value="<?= $feesDetails['header'][0] ?? 'Year' ?>" />
                                        </td>

                                        <td>
                                            <input type="text" name="fees_heading[]"
                                                value="<?= $feesDetails['header'][1] ?? 'Tuition Fees (USD)' ?>" />
                                        </td>

                                        <td>
                                            <input type="text" name="fees_heading[]"
                                                value="<?= $feesDetails['header'][2] ?? 'Hostel (USD)' ?>" />
                                        </td>

                                        <td>
                                            <input type="text" name="fees_heading[]"
                                                value="<?= $feesDetails['header'][3] ?? 'Development Fees (USD)' ?>" />
                                        </td>

                                    </tr>

                                </thead>
                                <tbody id="feesTableBody">

                                    <?php if (!empty($feesDetails["data"])) { ?>

                                        <?php foreach ($feesDetails["data"] as $fData) { ?>
                                            <tr>
                                                <td>
                                                    <input type="text" class="form-control" name="fees[year][]"
                                                        value="<?= htmlspecialchars($fData['year'] ?? '') ?>">
                                                </td>

                                                <td>
                                                    <input type="number" class="form-control" name="fees[tuition][]"
                                                        value="<?= $fData['tuition'] ?? 0 ?>" onchange="calculateFeesTotals()">
                                                </td>

                                                <td>
                                                    <input type="text" class="form-control" name="fees[hostel][]"
                                                        value="<?= $fData['hostel'] ?? 0 ?>" onchange="calculateFeesTotals()">
                                                </td>

                                                <td>
                                                    <input type="text" class="form-control" name="fees[development][]"
                                                        value="<?= $fData['development'] ?? 0 ?>"
                                                        onchange="calculateFeesTotals()">
                                                </td>
                                            </tr>
                                        <?php } ?>

                                    <?php } else { ?>

                                        <tr>
                                            <td>
                                                <input type="text" class="form-control" name="fees[year][]"
                                                    value="1st Year">
                                            </td>

                                            <td>
                                                <input type="number" class="form-control" name="fees[tuition][]" value="0"
                                                    onchange="calculateFeesTotals()">
                                            </td>

                                            <td>
                                                <input type="text" class="form-control" name="fees[hostel][]" value="0"
                                                    onchange="calculateFeesTotals()">
                                            </td>

                                            <td>
                                                <input type="text" class="form-control" name="fees[development][]"
                                                    value="0" onchange="calculateFeesTotals()">
                                            </td>
                                        </tr>

                                    <?php } ?>

                                </tbody>
                                <tfoot>
                                    <?php if (!empty($feesDetails["footer"])) { ?>

                                        <?php foreach ($feesDetails["footer"] as $ffData) { ?>
                                            <td><?= $ffData['label'] ?? 0 ?></td>
                                            <td><input type="number" id="totalTuition" class="form-control"
                                                    value="<?= $ffData['tuition'] ?? 0 ?>" readonly></td>
                                            <td><input type="number" id="totalHostel" class="form-control"
                                                    value="<?= $ffData['hostel'] ?? 0 ?>" readonly></td>
                                            <td><input type="number" id="totalDev" class="form-control"
                                                    value="<?= $ffData['development'] ?? 0 ?>" readonly></td>
                                        <?php }
                                    } else { ?>
                                        <tr>
                                            <td>Total</td>
                                            <td><input type="number" id="totalTuition" class="form-control" value="0"
                                                    readonly></td>
                                            <td><input type="number" id="totalHostel" class="form-control" value="0"
                                                    readonly></td>
                                            <td><input type="number" id="totalDev" class="form-control" value="0" readonly>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                </tfoot>
                            </table>
                        </div>
                        <br>
                        <div>
                            <label>Notes / Instructions</label>
                            <textarea class="ckeditor note" id="note">
<?= $sectionDetails["note"] ?? '' ?>
                            </textarea>
                        </div>

                        <br>
                        <div>
                            <label>Notes / Instructions</label>
                            <textarea class="ckeditor note" id="note">
<?= $sectionDetails["note"] ?? '' ?>
                            </textarea>
                        </div>
                        <!-- Three Column Section for Other Charges, One Time Charges, Our Services -->
                        <div class="row">
                            <!-- Other Charges Section -->
                            <div class="col-lg-4">
                                <div class="table-section">
                                    <div class="section-header">
                                        <div>

                                            <!-- <h4>Other Charges</h4> -->
                                            <input type="text" class="form-control section-title-input"
                                                value="<?= $other_charges["title"] ?? 'Other Charges' ?>"
                                                id="other_charges_title" name="other_charges_title">
                                        </div>
                                        <div class="section-controls">
                                            <button type="button" class="btn btn-success btn-sm action-btn"
                                                onclick="addOtherChargeRow()">
                                                <i class="fa fa-plus"></i>
                                            </button>
                                            <!-- <button type="button" class="btn btn-danger btn-sm action-btn" onclick="removeOtherChargeRow()">
                                                <i class="fa fa-minus"></i>
                                            </button> -->
                                        </div>
                                    </div>

                                    <table id="otherChargesTable" class="table table-bordered editable-table">
                                        <thead>
                                            <tr>
                                                <th>Charge Type</th>
                                            </tr>
                                        </thead>
                                        <tbody id="otherChargesBody">
                                            <?php if (!empty($other_charges["data"])) {
                                                foreach ($other_charges["data"] as $oData) { ?>
                                                    <tr>
                                                        <td class="d-flex"><input type="text" class="form-control"
                                                                name="other_charges[]" value="<?= $oData ?>"><button
                                                                type="button" class="btn btn-danger btn-sm action-btn"
                                                                onclick="removeOtherChargeRow(this)" fdprocessedid="wa0pu">
                                                                <i class="fa fa-minus"></i>
                                                            </button></td>
                                                    </tr>
                                                <?php }
                                            } else { ?>
                                                <!-- <tr>
                                                    <td class="d-flex"><input type="text" class="form-control"
                                                            name="other_charges[]" value="TRC @ 400 USD"><button
                                                            type="button" class="btn btn-danger btn-sm action-btn"
                                                            onclick="removeOtherChargeRow(this)" fdprocessedid="wa0pu">
                                                            <i class="fa fa-minus"></i>
                                                        </button></td>
                                                </tr>
                                                <tr>
                                                    <td class="d-flex"><input type="text" class="form-control"
                                                            name="other_charges[]" value="Ministry Order @ 400 USD"><button
                                                            type="button" class="btn btn-danger btn-sm action-btn"
                                                            onclick="removeOtherChargeRow(this)" fdprocessedid="wa0pu">
                                                            <i class="fa fa-minus"></i>
                                                        </button></td>
                                                </tr>
                                                <tr>
                                                    <td class="d-flex"><input type="text" class="form-control"
                                                            name="other_charges[]"
                                                            value="Medical Insurance @ 100 USD"><button type="button"
                                                            class="btn btn-danger btn-sm action-btn"
                                                            onclick="removeOtherChargeRow(this)" fdprocessedid="wa0pu">
                                                            <i class="fa fa-minus"></i>
                                                        </button></td>
                                                </tr>
                                                <tr>
                                                    <td class="d-flex"><input type="text" class="form-control"
                                                            name="other_charges[]"
                                                            value="Application Fees @ 200 USD"><button type="button"
                                                            class="btn btn-danger btn-sm action-btn"
                                                            onclick="removeOtherChargeRow(this)" fdprocessedid="wa0pu">
                                                            <i class="fa fa-minus"></i>
                                                        </button></td>
                                                </tr> -->
                                            <?php } ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- One Time Charges Section -->
                            <div class="col-lg-4">
                                <div class="table-section">
                                    <div class="section-header">
                                        <div>
                                            <!-- <h4>One Time Charges</h4> -->
                                            <input type="text" class="form-control section-title-input"
                                                value="<?= $one_time_charges["title"] ?? 'One Time Charges' ?>"
                                                id="one_time_title" name="one_time_title">
                                        </div>
                                        <div class="section-controls">
                                            <button type="button" class="btn btn-success btn-sm action-btn"
                                                onclick="addOneTimeChargeRow()">
                                                <i class="fa fa-plus"></i>
                                            </button>
                                            <!-- <button type="button" class="btn btn-danger btn-sm action-btn" onclick="removeOneTimeChargeRow()">
                                                <i class="fa fa-minus"></i>
                                            </button> -->
                                        </div>
                                    </div>

                                    <table id="oneTimeChargesTable" class="table table-bordered editable-table">
                                        <thead>
                                            <tr>
                                                <th>Charge Description</th>
                                            </tr>
                                        </thead>
                                        <tbody id="oneTimeChargesBody">

                                            <?php if (!empty($one_time_charges["items"])) {
                                                foreach ($one_time_charges["items"] as $sData) { ?>
                                                    <tr>
                                                        <td class="d-flex"><input type="text" class="form-control"
                                                                name="one_time_charges[]" value="<?= $sData ?>"><button
                                                                type="button" class="btn btn-danger btn-sm action-btn"
                                                                onclick="removeOneTimeChargeRow(this)">
                                                                <i class="fa fa-minus"></i>
                                                            </button></td>
                                                    </tr>
                                                <?php }
                                            } else { ?>
                                                <!-- <tr>
                                                    <td class="d-flex"><input type="text" class="form-control"
                                                            name="one_time_charges[]"
                                                            value="College Development Charges"><button type="button"
                                                            class="btn btn-danger btn-sm action-btn"
                                                            onclick="removeOneTimeChargeRow(this)">
                                                            <i class="fa fa-minus"></i>
                                                        </button></td>
                                                </tr>
                                                <tr>
                                                    <td class="d-flex"><input type="text" class="form-control"
                                                            name="one_time_charges[]"
                                                            value="Translation & Notarization"><button type="button"
                                                            class="btn btn-danger btn-sm action-btn"
                                                            onclick="removeOneTimeChargeRow(this)">
                                                            <i class="fa fa-minus"></i>
                                                        </button></td>
                                                </tr>
                                                <tr>
                                                    <td class="d-flex"><input type="text" class="form-control"
                                                            name="one_time_charges[]" value="Invitation Letter"><button
                                                            type="button" class="btn btn-danger btn-sm action-btn"
                                                            onclick="removeOneTimeChargeRow(this)">
                                                            <i class="fa fa-minus"></i>
                                                        </button></td>
                                                </tr>
                                                <tr>
                                                    <td class="d-flex"><input type="text" class="form-control"
                                                            name="one_time_charges[]"
                                                            value="Immigration clearance certificate"><button type="button"
                                                            class="btn btn-danger btn-sm action-btn"
                                                            onclick="removeOneTimeChargeRow(this)">
                                                            <i class="fa fa-minus"></i>
                                                        </button></td>
                                                </tr>
                                                <tr>
                                                    <td class="d-flex"><input type="text" class="form-control"
                                                            name="one_time_charges[]"
                                                            value="Library & laboratory Card Fee"><button type="button"
                                                            class="btn btn-danger btn-sm action-btn"
                                                            onclick="removeOneTimeChargeRow(this)">
                                                            <i class="fa fa-minus"></i>
                                                        </button></td>
                                                </tr>
                                                <tr>
                                                    <td class="d-flex"><input type="text" class="form-control"
                                                            name="one_time_charges[]"
                                                            value="Administrative & HR Documentation charges"><button
                                                            type="button" class="btn btn-danger btn-sm action-btn"
                                                            onclick="removeOneTimeChargeRow(this)">
                                                            <i class="fa fa-minus"></i>
                                                        </button></td>
                                                </tr>
                                                <tr>
                                                    <td class="d-flex"><input type="text" class="form-control"
                                                            name="one_time_charges[]" value="Travel Insurance"><button
                                                            type="button" class="btn btn-danger btn-sm action-btn"
                                                            onclick="removeOneTimeChargeRow(this)">
                                                            <i class="fa fa-minus"></i>
                                                        </button></td>
                                                </tr>
                                                <tr>
                                                    <td class="d-flex"><input type="text" class="form-control"
                                                            name="one_time_charges[]" value="Applications Fees"><button
                                                            type="button" class="btn btn-danger btn-sm action-btn"
                                                            onclick="removeOneTimeChargeRow(this)">
                                                            <i class="fa fa-minus"></i>
                                                        </button></td>
                                                </tr> -->
                                            <?php } ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Our Services Section -->
                            <div class="col-lg-4">
                                <div class="table-section">
                                    <div class="section-header">
                                        <div>
                                            <!-- <h4>Our Services</h4> -->
                                            <input type="text" class="form-control section-title-input"
                                                value="<?= $services["title"] ?? 'Our Services' ?>" id="services_title"
                                                name="services_title">
                                        </div>
                                        <div class="section-controls">
                                            <button type="button" class="btn btn-success btn-sm action-btn"
                                                onclick="addServiceRow()">
                                                <i class="fa fa-plus"></i>
                                            </button>
                                            <!-- <button type="button" class="btn btn-danger btn-sm action-btn" onclick="removeServiceRow()">
                                                <i class="fa fa-minus"></i>
                                            </button> -->
                                        </div>
                                    </div>

                                    <table id="servicesTable" class="table table-bordered editable-table">
                                        <thead>
                                            <tr>
                                                <th>Service Name</th>
                                            </tr>
                                        </thead>
                                        <tbody id="servicesBody">
                                            <?php if (!empty($services["items"])) {
                                                foreach ($services["items"] as $ssData) { ?>
                                                    <tr>
                                                        <td class="d-flex"><input type="text" class="form-control"
                                                                name="services[]" value="<?= $ssData ?>"><button type="button"
                                                                class="btn btn-danger btn-sm action-btn"
                                                                onclick="removeServiceRow(this)">
                                                                <i class="fa fa-minus"></i>
                                                            </button></td>
                                                    </tr>
                                                <?php }
                                            } else { ?>
                                                <!-- <tr>
                                                    <td class='d-flex'><input type="text" class="form-control"
                                                            name="services[]" value="Admission Letter"><button type="button"
                                                            class="btn btn-danger btn-sm action-btn"
                                                            onclick="removeServiceRow(this)">
                                                            <i class="fa fa-minus"></i>
                                                        </button></td>
                                                </tr>
                                                <tr>
                                                    <td class='d-flex'><input type="text" class="form-control"
                                                            name="services[]"
                                                            value="Apostille of all academic documents"><button
                                                            type="button" class="btn btn-danger btn-sm action-btn"
                                                            onclick="removeServiceRow(this)">
                                                            <i class="fa fa-minus"></i>
                                                        </button></td>
                                                </tr>
                                                <tr>
                                                    <td class='d-flex'><input type="text" class="form-control"
                                                            name="services[]" value="Visa Appointment"><button type="button"
                                                            class="btn btn-danger btn-sm action-btn"
                                                            onclick="removeServiceRow(this)">
                                                            <i class="fa fa-minus"></i>
                                                        </button></td>
                                                </tr>
                                                <tr>
                                                    <td class='d-flex'><input type="text" class="form-control"
                                                            name="services[]" value="Flight Ticket"><button type="button"
                                                            class="btn btn-danger btn-sm action-btn"
                                                            onclick="removeServiceRow(this)">
                                                            <i class="fa fa-minus"></i>
                                                        </button></td>
                                                </tr>
                                                <tr>
                                                    <td class='d-flex'><input type="text" class="form-control"
                                                            name="services[]" value="Airport Pickup & Drop"><button
                                                            type="button" class="btn btn-danger btn-sm action-btn"
                                                            onclick="removeServiceRow(this)">
                                                            <i class="fa fa-minus"></i>
                                                        </button></td>
                                                </tr>
                                                <tr>
                                                    <td class='d-flex'><input type="text" class="form-control"
                                                            name="services[]" value="Accommodation & Indian Mess"><button
                                                            type="button" class="btn btn-danger btn-sm action-btn"
                                                            onclick="removeServiceRow(this)">
                                                            <i class="fa fa-minus"></i>
                                                        </button></td>
                                                </tr>
                                                <tr>
                                                    <td class='d-flex'><input type="text" class="form-control"
                                                            name="services[]"
                                                            value="Bank account & Sim card allotment"><button type="button"
                                                            class="btn btn-danger btn-sm action-btn"
                                                            onclick="removeServiceRow(this)">
                                                            <i class="fa fa-minus"></i>
                                                        </button></td>
                                                </tr>
                                                <tr>
                                                    <td class='d-flex'><input type="text" class="form-control"
                                                            name="services[]" value="24*7 On-call & On-campus"><button
                                                            type="button" class="btn btn-danger btn-sm action-btn"
                                                            onclick="removeServiceRow(this)">
                                                            <i class="fa fa-minus"></i>
                                                        </button></td>
                                                </tr> -->
                                            <?php } ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Processing Fee Table with Header, Body, Footer -->
                        <div class="table-section">
                            <div class="section-header">

                                <div>
                                    <!-- <h3>Processing Fee Details</h3> -->
                                    <input type="text" class="form-control section-title-input"
                                        value="<?= $processing_fee["title"] ?? 'Processing Fee Details' ?>"
                                        id="processing_title" name="processing_title">
                                </div>
                                <div class="section-controls">
                                    <button type="button" class="btn btn-success action-btn"
                                        onclick="addProcessingRow()">
                                        <i class="fa fa-plus"></i> Add Row
                                    </button>
                                    <!-- <button type="button" class="btn btn-danger action-btn" onclick="removeProcessingRow()">
                                        <i class="fa fa-minus"></i> Remove
                                    </button> -->
                                </div>
                            </div>

                            <table id="processingTable" class="table table-bordered editable-table">
                                <thead>
                                    <tr>
                                        <?php if (!empty($processing_fee["header"])) {
                                            foreach ($processing_fee["header"] as $pHeader) { ?>
                                                <th><input type="text" class="form-control" name="processing[headline][]"
                                                        value="<?= $pHeader["description"] ?>"></th>
                                                <th class="d-flex"><input type="text" class="form-control"
                                                        name="processing[amount][]" value="<?= $pHeader["amount"] ?>"></th>
                                            <?php }
                                        } else { ?>
                                            <th><input type="text" class="form-control" name="processing[headline][]"
                                                    value="Installment"></th>
                                            <th><input type="text" class="form-control" name="processing[amount][]"
                                                    onchange="calculateProcessingTotal()" value="Amount (INR)"></th>
                                        <?php } ?>
                                    </tr>
                                </thead>
                                <tbody id="processingBody">
                                    <?php if (!empty($processing_fee["data"])) {
                                        foreach ($processing_fee["data"] as $pData) { ?>
                                            <tr>

                                                <td><input type="text" class="form-control" name="processing[description][]"
                                                        value="<?= $pData["description"] ?>"></td>
                                                <td class="d-flex"><input type="text" class="form-control processing-input"
                                                        name="processing[amount][]" onchange="calculateProcessingTotal()"
                                                        value="<?= $pData["amount"] ?>"><button type="button"
                                                        class="btn btn-danger action-btn" onclick="removeProcessingRow(this)">
                                                        <i class="fa fa-minus"></i>
                                                    </button></td>
                                            </tr>
                                        <?php }
                                    } else { ?>

                                        <!-- <tr>
                                            <td><input type="text" class="form-control" name="processing[description][]"
                                                    value="Registrtion & Documentation"></td>
                                            <td class="d-flex"><input type="number" class="form-control processing-input"
                                                    name="processing[amount][]" value="5000"
                                                    onchange="calculateProcessingTotal()"><button type="button"
                                                    class="btn btn-danger action-btn" onclick="removeProcessingRow(this)">
                                                    <i class="fa fa-minus"></i>
                                                </button></td>
                                        </tr>
                                        <tr>
                                            <td><input type="text" class="form-control" name="processing[description][]"
                                                    value="Visa Charges & Apostile"></td>
                                            <td class="d-flex"><input type="number" class="form-control processing-input"
                                                    name="processing[amount][]" value="3000"
                                                    onchange="calculateProcessingTotal()"><button type="button"
                                                    class="btn btn-danger action-btn" onclick="removeProcessingRow(this)">
                                                    <i class="fa fa-minus"></i>
                                                </button></td>
                                        </tr> -->
                                    <?php } ?>
                                </tbody>
                                <tfoot>
                                    <?php if (!empty($processing_fee["footer"])) {
                                        foreach ($processing_fee["footer"] as $fData) { ?>
                                            <tr>
                                                <td><input type="text" class="form-control" id="processing_footer_headline"
                                                        value="<?= $fData["description"] ?>"></td>
                                                <td><input type="number" id="totalProcessing" class="form-control"
                                                        value="<?= $fData["amount"] ?>" readonly></td>
                                            </tr>
                                        <?php }
                                    } else { ?>
                                        <tr>
                                            <td><input type="text" class="form-control" id="processing_footer_headline"
                                                    value="Total"></td>
                                            <td><input type="number" id="totalProcessing" class="form-control" value="0"
                                                    readonly></td>
                                        </tr>
                                    <?php } ?>
                                </tfoot>
                            </table>
                        </div>


                        <!-- GST Section -->
                        <div class="row">
                            <div class="col-md-12 text-center">
                                <div class="gst-badge">
                                    <input type="text" class="form-control"
                                        value="<?= $processing_fee["gst_text"] ?? '18% GST Applicable' ?>" id="gst_text"
                                        name="gst_text">
                                </div>
                            </div>
                        </div>


                        <!-- Contact Section -->
                        <div class="contact-section">

                            <div class="row">
                                <div class="col-md-4">
                                    <label><i class="fa fa-phone"></i> Contact Number</label>
                                    <input type="text" class="form-control" value="<?= $contactInfo['phone'] ?? '' ?>" readonly
                                        placeholder="Enter contact number" id="contact_number" name="contact_number">
                                </div>
                                <div class="col-md-4">
                                    <label><i class="fa fa-envelope"></i> Email</label>
                                    <input type="email" class="form-control" readonly value="<?= $contactInfo['email'] ?? 'brightrouteconsulting@gmail.com' ?>" placeholder="Enter email"
                                        id="contact_email" name="contact_email">
                                </div>
                                <div class="col-md-4">
                                    <label><i class="fa fa-globe"></i> Website</label>
                                    <input type="text" class="form-control" readonly value="<?= $contactInfo['website'] ?? 'www.educationvibes.in' ?>" placeholder="Enter website"
                                        id="contact_website" name="contact_website">
                                </div>
                            </div>
                        </div>
                        <br>
                        <div class=" m-4  p-4 set-checkbox">
                            <span><input type="checkbox" value="1" name="default_country_data" id="default_country_data"> &nbsp;</span>
                            Apply this information to all similar countries

                        </div>
                        </br>
                        <!-- Action Buttons -->
                        <div class="row mt-4">
                            <div class="col-md-12 text-center">
                                <button type="submit" class="btn btn-primary btn-lg" onclick="saveFormData()">
                                    <i class="fa fa-save"></i> Save Data
                                </button>

                                <div class="previewData-section inline-block  <?= !empty($id) ? '' : 'hide' ?>">
                                    <button type="button" class="btn btn-success btn-lg" onclick="previewData()">
                                        <i class="fa fa-eye"></i> Preview
                                    </button>

                                    <button type="button" class="btn btn-success btn-lg hide generate-pdf-button" onclick="generatePDFAndUpload(<?= $id ?>)">
                                        <i class="fa fa-eye"></i> Save in KnowledgeBase
                                    </button>
                                </div>
                                <!-- <button type="button" class="btn btn-info btn-lg" onclick="downloadJSON()">
                                    <i class="fa fa-download"></i> Download JSON
                                </button> -->
                                <button type="reset" class="btn btn-default btn-lg" onclick="resetForm()">
                                    <i class="fa fa-undo"></i> Reset
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>


<?php init_tail(); ?>
<script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>

<script>
    let myEditor;

    ClassicEditor
        .create(document.querySelector('.ckeditor'))
        .then(editor => {
            myEditor = editor; // ✅ store real instance
        })
        .catch(error => console.error(error));

    function getValue() {
        const data = myEditor.getData();
        console.log(data);
    }
    var id = "<?= $id ?? '' ?>";

    function previewData() {
        var url = "<?= admin_url('Fees/generate'); ?>/" + id;
        window.open(url, "_blank");
    }

    function download(id) {
        var url = "<?= admin_url('Fees/generate'); ?>/" + id + "?download=1";

        let newTab = window.open(url, "_blank");

        // Optional: close after 3 seconds
        setTimeout(() => {
            if (newTab) newTab.close();
        }, 5000);
    }


    // Country and University data
    var countries = <?= json_encode($countries ?? [], true) ?>;
    var universities = <?= json_encode($universities ?? [], true) ?>;
    var feesDetails = <?= json_encode($feesDetails ?? [], true) ?? []  ?>;
    var selected_segment = "<?= $feesStructure["segment_id"] ?? '' ?>";
    var selected_country = "<?= $feesStructure["country_id"] ?? '' ?>";
    var selected_university = "<?= $feesStructure["university_id"] ?? '' ?>";
    var regions = <?= json_encode($regions ?? [], true) ?>;

    var setAuto = 0;
    if (selected_segment != "") {
        setAuto = 1;
        setTimeout(() => {
            $("#segment_type").trigger("change");
            setTimeout(() => {
                $("#countries").val(selected_country).trigger("change");
                setTimeout(() => {
                    $("#universities").val(selected_university).trigger("change");
                }, 100);
            }, 100);
        }, 100);
    }

    // Segment Type change handler
    $("#segment_type").change(function() {

        let segment_id = $(this).val();
        // let selectedCountries = countries[segment_id] || [];

        let selectedCountries = countries[segment_id] ?
            Object.values(countries[segment_id]) : [];

        let $countriesSelect = $("#countries");
        let $universitiesSelect = $("#universities");
        $countriesSelect.empty();
        $universitiesSelect.empty()


        // Default option
        $countriesSelect.append(
            $('<option>', {
                value: '',
                text: 'Select countries',
                selected: true,
                disabled: true
            })
        );


        $universitiesSelect.append(
            $('<option>', {
                value: '',
                text: 'Select universities',
                selected: true,
                disabled: true
            })
        );

        $universitiesSelect.selectpicker('refresh');
        // Append countries
        selectedCountries.forEach(function(country) {
            $countriesSelect.append(
                $('<option>', {
                    value: country.id,
                    text: country.country_name
                })
            );
        });

        // Reset hidden fields
        $("#country_name").val("");
        $("#university_name").val("");
        $("#founded_year").val("");

        // Refresh selectpicker
        $countriesSelect.selectpicker('refresh');

        if (setAuto == 0) {
            // Reset preview image
            $(".preview_image img")
                .attr("src", "")
                .hide();
        }

        $(".generate-pdf-button").addClass("hide");
    });


    var countrySelectedChanges = {};
    $("#countries").change(function() {

        let country_id = $(this).val();
        let segment_id = $("#segment_type").val();
        let selectedUniversities = universities[country_id] ?
            Object.values(universities[country_id]) : [];


        let rawFees = countries?.[segment_id]?.[country_id]?.fees_structure;

        if (rawFees) {
            try {
                countrySelectedChanges = JSON.parse(rawFees);
            } catch (e) {
                console.error("Invalid JSON in fees_structure:", e);
                countrySelectedChanges = {};
            }
        } else {
            countrySelectedChanges = {};
        }
        // Set country name
        if (country_id) {
            let countryName = $(this).find("option:selected").text();
            $("#country_name").val(countryName);
        } else {
            $("#country_name").val("");
        }

        let $universitiesSelect = $("#universities");
        $universitiesSelect.empty();

        // Default option
        $universitiesSelect.append(
            $('<option>', {
                value: '',
                text: 'Select universities',
                disabled: true,
                selected: true
            })
        );

        // Append universities
        selectedUniversities.forEach(function(university) {

            $universitiesSelect.append(
                $('<option>', {
                    value: university.id,
                    text: university.university_name
                })
            );

        });

        $("#university_name").val("");
        $(".generate-pdf-button").addClass("hide");

        $universitiesSelect.selectpicker('refresh');
        SetCountryChanges();
    });



    $("#universities").change(function() {

        let country_id = $("#countries").val();
        let university_id = $(this).val();
        let university_name = $(this).find("option:selected").text();

        let universityData = (universities &&
                universities[country_id] &&
                universities[country_id][university_id]) ?
            universities[country_id][university_id] : {};

        if (setAuto == 0) {

            // Logo
            if (universityData.logo) {

                let logoUrl = universityData.logo.startsWith("http") ?
                    universityData.logo :
                    "https://educationvibes.in/" + universityData.logo;

                $("#university_logo_preview")
                    .attr("src", logoUrl)
                    .show();

            } else {
                $("#university_logo_preview").hide();
            }

            // Banner Image
            if (universityData.images) {

                let bannerUrl = universityData.images.startsWith("http") ?
                    universityData.images :
                    "https://educationvibes.in/" + universityData.images;

                $("#university_banner_preview")
                    .attr("src", bannerUrl)
                    .show();

            } else {
                $("#university_banner_preview").hide();
            }
        }

        $("#university_name").val(university_name);
        $("#founded_year").val(universityData.founded || "");

        setAuto = 0;
        $(".generate-pdf-button").addClass("hide");
    });



    $("#year").keypress(function(e) {
        // Allow only numbers (0-9) and prevent other characters
        let charCode = e.which ? e.which : e.keyCode;
        if (charCode < 48 || charCode > 57) {
            e.preventDefault(); // stop non-numeric input
        }
        $(".generate-pdf-button").addClass("hide");

    });

    $("#year").on('input', function() {
        let yearValue = $(this).val();
        if (yearValue !== "") {
            // Add suffix to the other field
            $("#program_duration").val(yearValue + " Year");
        } else {
            $("#program_duration").val(""); // clear if empty
        }
        $(".generate-pdf-button").addClass("hide");

    });

    function SetCountryChanges() {
        try {
            if (id > 0 && id != '') {
                return false;
            }

            if (!countrySelectedChanges || typeof countrySelectedChanges !== "object") {
                console.warn("countrySelectedChanges is invalid");
                return;
            }

            // Reusable function for simple charge lists
            function populateSimpleList(bodySelector, dataArray, addRowFn) {
                const $body = $(bodySelector);
                $body.empty();

                if (!Array.isArray(dataArray) || dataArray.length === 0) return;

                dataArray.forEach(item => {
                    addRowFn();
                    $body.find("tr:last input").val(item ?? "");
                });
            }

            // Other Charges
            populateSimpleList(
                "#otherChargesBody",
                countrySelectedChanges?.other_charges?.data ?? [],
                addOtherChargeRow
            );

            // One Time Charges
            populateSimpleList(
                "#oneTimeChargesBody",
                countrySelectedChanges?.one_time_charges?.items ?? [],
                addOneTimeChargeRow
            );

            // Services
            populateSimpleList(
                "#servicesBody",
                countrySelectedChanges?.services?.items ?? [],
                addServiceRow
            );

            // Processing Fees (special structure)
            const $processingBody = $("#processingBody");
            $processingBody.empty();

            const processChargesData = countrySelectedChanges?.processing_fee?.data ?? [];

            if (Array.isArray(processChargesData) && processChargesData.length > 0) {
                processChargesData.forEach(process => {
                    addProcessingRow();

                    const $lastRow = $processingBody.find("tr:last");

                    $lastRow.find("input[type='text']").val(process?.description ?? "");
                    $lastRow.find("input[type='number']").val(process?.amount ?? "");
                });
                calculateProcessingTotal()
            }
            const tableHeaders = countrySelectedChanges?.fees?.header ?? [];

            if (Array.isArray(tableHeaders) && tableHeaders.length > 0) {
                $("#feesTable thead tr td input").each(function(index) {
                    console.log(tableHeaders[index]);
                    $(this).val(tableHeaders[index] ?? '');
                });
            }
        } catch (error) {
            console.error("Error in SetCountryChanges:", error);
        }
    }


    // Fees Table Functions
    function addFeesRow() {
        var tableBody = document.getElementById('feesTableBody');
        var rowCount = tableBody.rows.length;
        var newRow = tableBody.insertRow();
        var yearSuffix = getYearSuffix(rowCount + 1);

        newRow.innerHTML = `
        <td><input type="text" class="form-control year-text" name="fees[year][]" value="${rowCount + 1}${yearSuffix} Year"></td>
        <td><input type="number" class="form-control tuition-input" name="fees[tuition][]" value="0" onchange="calculateFeesTotals()"></td>
        <td><input type="text" class="form-control hostel-input" name="fees[hostel][]" value="0" onchange="calculateFeesTotals()"></td>
        <td><input type="text" class="form-control dev-input" name="fees[development][]" value="0" onchange="calculateFeesTotals()"></td>
        `;

        calculateFeesTotals();
    }

    function removeFeesRow() {
        var tableBody = document.getElementById('feesTableBody');
        if (tableBody.rows.length > 1) {
            tableBody.deleteRow(tableBody.rows.length - 1);
            calculateFeesTotals();
        }
    }

    function getYearSuffix(number) {
        if (number == 1) return 'st';
        if (number == 2) return 'nd';
        if (number == 3) return 'rd';
        return 'th';
    }

    function calculateFeesTotals() {
        var tuitionTotal = 0;
        var hostelTotal = 0;
        var devTotal = 0;

        document.querySelectorAll('#feesTable tbody tr').forEach(row => {
            tuitionTotal += parseFloat(row.cells[1].querySelector('input').value) || 0;
            hostelTotal += parseFloat(row.cells[2].querySelector('input').value) || 0;
            devTotal += parseFloat(row.cells[3].querySelector('input').value) || 0;
        });

        $('#totalTuition').val(tuitionTotal.toFixed(2));
        $('#totalHostel').val(hostelTotal.toFixed(2));
        $('#totalDev').val(devTotal.toFixed(2));

        // Update summary
        $('#summaryTuition').text('$' + formatNumber(tuitionTotal));
        $('#summaryHostel').text('$' + formatNumber(hostelTotal));
        $('#summaryDev').text('$' + formatNumber(devTotal));
    }

    function addOtherChargeRow() {

        var tableBody = document.getElementById('otherChargesBody');
        if (!tableBody) return;

        var rowCount = tableBody.rows.length;

        // Check BEFORE inserting row
        if (rowCount >= 4) {
            alert("Maximum 4 other charges allowed.");
            return false;
        }

        var newRow = tableBody.insertRow();

        newRow.innerHTML = `
        <td class="d-flex">
            <input type="text"
                   class="form-control"
                   name="other_charges[]"
                   value="New Charge">

            <button type="button"
                    class="btn btn-danger btn-sm action-btn"
                    onclick="removeOtherChargeRow(this)">
                <i class="fa fa-minus"></i>
            </button>
        </td>
    `;
    }

    function removeOtherChargeRow(obj) {

        if (obj) {
            // Remove the closest <tr> of the clicked element
            obj.closest("tr").remove();
        } else {
            // Remove the last row from tbody
            let tableBody = document.getElementById("otherChargesBody");

            if (tableBody && tableBody.rows.length > 0) {
                tableBody.deleteRow(tableBody.rows.length - 1);
            }
        }
    }


    function addOneTimeChargeRow() {

        var tableBody = document.getElementById('oneTimeChargesBody');
        if (!tableBody) return;

        var rowCount = tableBody.rows.length;

        // Maximum 12 rows allowed
        if (rowCount >= 12) {
            alert("Maximum 12 one-time charges allowed.");
            return false;
        }

        var newRow = tableBody.insertRow();

        newRow.innerHTML = `
        <td class="d-flex">
            <input type="text"
                   class="form-control"
                   name="one_time_charges[]"
                   value="New One Time Charge">

            <button type="button"
                    class="btn btn-danger btn-sm action-btn"
                    onclick="removeOneTimeChargeRow(this)">
                <i class="fa fa-minus"></i>
            </button>
        </td>
    `;
    }

    function removeOneTimeChargeRow(obj = "") {
        if (obj) {
            $(obj).closest("tr").remove(); // find nearest <tr>
        } else {
            let tableBody = document.getElementById('oneTimeChargesBody');

            if (tableBody.rows.length > 0) {
                tableBody.deleteRow(tableBody.rows.length - 1);
            }
        }
    }

    // Services Table Functions
    function addServiceRow() {

        var tableBody = document.getElementById('servicesBody');
        if (!tableBody) return;

        var rowCount = tableBody.rows.length;

        // Optional: limit number of rows (example: max 5)
        if (rowCount >= 12) {
            alert("Maximum 12 Services allowed.");
            return false;
            return false;
        }

        var newRow = tableBody.insertRow();

        newRow.innerHTML = `
        <td class="d-flex">
            <input type="text" 
                   class="form-control" 
                   name="services[]" 
                   value="New Service">
                   
            <button type="button" 
                    class="btn btn-danger btn-sm action-btn" 
                    onclick="removeServiceRow(this)">
                <i class="fa fa-minus"></i>
            </button>
        </td>
    `;
    }

    function removeServiceRow(obj = "") {
        if (obj) {
            $(obj).closest("tr").remove(); // find nearest <tr>
        } else {
            let tableBody = document.getElementById('servicesBody');

            if (tableBody.rows.length > 0) {
                tableBody.deleteRow(tableBody.rows.length - 1);
            }
        }
    }

    // Processing Fee Table Functions
    function addProcessingRow() {

        var tableBody = document.getElementById('processingBody');
        if (!tableBody) return;

        var rowCount = tableBody.rows.length;

        // Stop if already 4 rows
        if (rowCount >= 4) {
            alert("Maximum 4 Processing Fees allowed.");
            return false;
        }

        var newRow = tableBody.insertRow();

        newRow.innerHTML = `
        <td>
            <input type="text" class="form-control" 
                   name="processing[${rowCount}][description]" 
                   value="New Processing Fee">
        </td>
        <td class="d-flex">
            <input type="number" 
                   class="form-control processing-input" 
                   name="processing[${rowCount}][amount]" 
                   value="0" 
                   onchange="calculateProcessingTotal()">
            <button type="button"
                    class="btn btn-danger action-btn"
                    onclick="removeProcessingRow(this)">
                <i class="fa fa-minus"></i>
            </button>
        </td>
    `;

        calculateProcessingTotal();
    }

    function removeProcessingRow(obj) {

        var tableBody = document.getElementById('processingBody');
        if (!tableBody) return;

        if (tableBody.rows.length <= 1) {
            return; // prevent deleting last row
        }

        // Remove the clicked row
        obj.closest("tr").remove();

        calculateProcessingTotal();
    }

    function calculateProcessingTotal() {
        var total = 0;
        document.querySelectorAll('.processing-input').forEach(input => {
            total += parseFloat(input.value) || 0;
        });
        console.log(total);
        $('#totalProcessing').val(total.toFixed(2));
        $('#summaryProcessing').text('₹' + formatNumber(total));
    }

    // Installment Table Functions
    function addInstallmentRow() {
        var tableBody = document.getElementById('installmentBody');
        var rowCount = tableBody.rows.length;
        var newRow = tableBody.insertRow();

        newRow.innerHTML = `
        <td><input type="text" class="form-control" name="installment[${rowCount}][number]" value="${rowCount + 1}th Installment"></td>
        <td><input type="number" class="form-control installment-input" name="installment[${rowCount}][amount]" value="0" onchange="calculateInstallmentTotal()"></td>
        <td><input type="text" class="form-control" name="installment[${rowCount}][due_date]" value="Due date"></td>
        `;

        calculateInstallmentTotal();
    }

    function removeInstallmentRow() {
        var tableBody = document.getElementById('installmentBody');
        if (tableBody.rows.length > 1) {
            tableBody.deleteRow(tableBody.rows.length - 1);
            calculateInstallmentTotal();
        }
    }

    function calculateInstallmentTotal() {
        var total = 0;
        document.querySelectorAll('.installment-input').forEach(input => {
            total += parseFloat(input.value) || 0;
        });
        $('#totalInstallment').val(total.toFixed(2));
        $('#summaryInstallment').text('₹' + formatNumber(total));
    }

    // Format Number
    function formatNumber(num) {
        return parseFloat(num).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    }

    // Collect all form data into structured JSON
    function collectFormData() {

        var formData = {

            id: $('#id').val(),
            segment_id: $('#segment_type').val(),
            country_id: $('#countries').val(),
            university_id: $('#universities').val(),
            region_id: $('#region_type').val(),
            setDefault: $('#default_country_data').is(':checked') ? 1 : 0,
            // Proper CSRF format for CodeIgniter
            [csrfData.token_name]: csrfData.hash,

            university_info: {
                segment_type: $("#segment_type option:selected").text(),
                country_name: $('#countries option:selected').text(),
                university_name: $('#universities option:selected').text(),
                region_name: $('#region_type option:selected').text(),
                founded_year: $('#founded_year').val(),
                duration: $('#program_duration').val(),
                year: $('#year').val(),
                banner_image: $("#university_banner_preview").attr("src") || "",
                logo: $("#website_logo_preview").attr("src") || "",
                university_logo: $("#university_logo_preview").attr("src") || ""
            },

            sections: {
                fees: {
                    title: $('#fees_section_title').val(),
                    header: [],
                    data: [],
                    footer: []
                },
                note: "",
                other_charges: {
                    title: $('#other_charges_title').val(),
                    data: []
                },

                one_time_charges: {
                    title: $('#one_time_title').val(),
                    items: []
                },

                services: {
                    title: $('#services_title').val(),
                    items: []
                },

                processing_fee: {
                    title: $('#processing_title').val(),
                    header: [],
                    data: [],
                    footer: [],
                    gst_text: $('#gst_text').val()
                },

                installment: {
                    items: []
                }
            },

            contact: {
                phone: $('#contact_number').val(),
                email: $('#contact_email').val(),
                website: $('#contact_website').val()
            }
        };

        /* ================= FEES ================= */

        // Header
        $('#feesTable thead td input').each(function() {
            formData.sections.fees.header.push($(this).val() || "");
        });

        // Body
        $('#feesTable tbody tr').each(function() {

            let year = $(this).find('td:eq(0) input').val();

            if (year) { // prevent empty rows
                formData.sections.fees.data.push({
                    year: year,
                    tuition: parseFloat($(this).find('td:eq(1) input').val()) || 0,
                    hostel: ($(this).find('td:eq(2) input').val()) || 0,
                    development: ($(this).find('td:eq(3) input').val()) || 0
                });
            }
        });

        // Footer
        $('#feesTable tfoot tr').each(function() {

            formData.sections.fees.footer.push({
                label: $(this).find('td:eq(0)').text().trim(),
                tuition: parseFloat($(this).find('td:eq(1) input').val()) || 0,
                hostel: parseFloat($(this).find('td:eq(2) input').val()) || 0,
                development: parseFloat($(this).find('td:eq(3) input').val()) || 0
            });
        });

        /* ================= OTHER CHARGES ================= */

        $('#otherChargesBody tr').each(function() {
            let value = $(this).find('input').val();
            if (value) formData.sections.other_charges.data.push(value);
        });

        /* ================= ONE TIME ================= */

        $('#oneTimeChargesBody tr').each(function() {
            let value = $(this).find('input').val();
            if (value) formData.sections.one_time_charges.items.push(value);
        });

        /* ================= SERVICES ================= */

        $('#servicesBody tr').each(function() {
            let value = $(this).find('input').val();
            if (value) formData.sections.services.items.push(value);
        });

        /* ================= PROCESSING ================= */

        $('#processingTable thead tr').each(function() {

            let desc = $(this).find('th:eq(0) input').val();

            if (desc) {
                formData.sections.processing_fee.header.push({
                    description: desc,
                    amount: ($(this).find('th:eq(1) input').val()) || ""
                });
            }
        });

        $('#processingTable tbody tr').each(function() {

            let desc = $(this).find('td:eq(0) input').val();

            if (desc) {
                formData.sections.processing_fee.data.push({
                    description: desc,
                    amount: parseFloat($(this).find('td:eq(1) input').val()) || 0
                });
            }
        });

        $('#processingTable tfoot tr').each(function() {

            let desc = $(this).find('td:eq(0) input').val();

            if (desc) {
                formData.sections.processing_fee.footer.push({
                    description: desc,
                    amount: parseFloat($(this).find('td:eq(1) input').val()) || 0
                });
            }
        });

        formData.sections.note = myEditor ? myEditor.getData() || "" : "";

        formData.sections.note = myEditor ? myEditor.getData() || "" : "";

        return formData;
    }


    function objectToFormData(obj, formData = new FormData(), parentKey = "") {

        Object.keys(obj).forEach(key => {

            const value = obj[key];
            const fullKey = parentKey ? `${parentKey}[${key}]` : key;

            // Handle arrays
            if (Array.isArray(value)) {

                value.forEach((item, index) => {

                    const arrayKey = `${fullKey}[${index}]`;

                    if (item !== null && typeof item === "object" && !(item instanceof File)) {
                        objectToFormData(item, formData, arrayKey);
                    } else {
                        formData.append(arrayKey, item ?? "");
                    }

                });

            }
            // Handle File
            else if (value instanceof File) {

                formData.append(fullKey, value);

            }
            // Handle nested objects
            else if (value !== null && typeof value === "object") {

                objectToFormData(value, formData, fullKey);

            }
            // Handle primitives (string, number, boolean)
            else {

                formData.append(fullKey, value ?? "");

            }

        });

        return formData;
    }


    function saveFormData() {
        appValidateForm($('#feesStructure-form'), {
            segment_type: 'required',
            countries: 'required',
            universities: 'required',
            region_type: 'required',
            year: 'required',
            contact_number: 'required'
        }, saveFormDataSubmit);
    }

    function saveFormDataSubmit() {

        const dataObject = collectFormData();
        pdfData = [];
        const formData = objectToFormData(dataObject);
        console.log("final", formData);
        $.ajax({
            url: "<?= base_url('admin/Fees/saveData') ?>",
            type: "POST",
            data: formData,
            processData: false, // VERY IMPORTANT
            contentType: false, // VERY IMPORTANT
            cache: false,

            success: function(response) {
                response = JSON.parse(response);
                if (response.resp_code === "RCS") {
                    if (response.id > 0) {
                        id = response.id;
                        $(".previewData-section").removeClass("hide");

                        pdfData["university_name"] = dataObject["university_info"].university_name;
                        pdfData["country_name"] = dataObject["university_info"].country_name;
                        pdfData["segment_type"] = dataObject["university_info"].segment_type;
                        pdfData["region_name"] = dataObject["university_info"].region_name;
                        $(".generate-pdf-button").removeClass("hide");
                    }
                    alert_float("success", response.resp_desc);
                } else {
                    id = "";
                    $(".previewData-section").addClass("hide");
                    $(".generate-pdf-button").addClass("hide");
                    alert_float("danger", response.resp_desc);
                }
            },

            error: function(xhr) {
                console.error(xhr.responseText);
                alert_float("danger", xhr.responseText);
            }
        });
    }


    // Download JSON file
    function downloadJSON() {
        var formData = collectFormData();
        var dataStr = JSON.stringify(formData, null, 2);
        var blob = new Blob([dataStr], {
            type: 'application/json'
        });
        var url = URL.createObjectURL(blob);
        var a = document.createElement('a');
        a.href = url;
        a.download = 'university_data_' + new Date().toISOString().slice(0, 10) + '.json';
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
    }




    // Reset form
    function resetForm() {
        if (confirm('Reset all fields to default?')) {
            localStorage.removeItem('universityFormData');
            location.reload();
        }
    }

    $(document).on("change", "input[type='file']", function(e) {

        let input = $(this);
        let file = e.target.files[0];

        if (!file) return;

        let inputName = input.attr("name");

        // Remove [] if exists
        inputName = inputName.replace(/\[\]/g, "");

        let previewClass = "." + inputName + "_preview";
        console.log(previewClass);
        let reader = new FileReader();

        reader.onload = function(event) {
            $(previewClass).find("img").attr("src", event.target.result);
        };

        reader.readAsDataURL(file);
    });

    $("#created_universities").change(function() {

        let selectedUniversity = $(this).val();

        if (selectedUniversity > 0) {
            var url = "<?= admin_url('Fees/partner/'); ?>" + selectedUniversity;
            window.location.href = url;
        } else {

            let url = "<?= admin_url('Fees/partner'); ?>";
            window.location.href = url;
        }

    });

    $("#region_type").on("change", function() {
        let id = $(this).val();
        let regionName = $(this).find("option:selected").text();
        console.log(regionName);
        let selectedRegion = regions[id] ?? [];
        $("#region_name").val(regionName);
        if (selectedRegion && selectedRegion.contact != '') {
            $("#contact_number").val(selectedRegion.contact);
        } else {
            $("#contact_number").val("");
        }
        $(".generate-pdf-button").addClass("hide");

    });

    var pdfData = [];

    async function generatePDFAndUpload(id_ = null) {

        try {

            /* -------------------------
              1️⃣ RESOLVE ID SAFELY
            -------------------------- */

            let pdf_id = null;

            if (id_ !== null && id_ !== undefined && id_ !== "") {
                pdf_id = id_;
            } else if (typeof id !== "undefined" && id !== null && id !== "") {
                pdf_id = id;
            }

            // Convert to integer safely
            pdf_id = Number(pdf_id);

            if (!Number.isInteger(pdf_id) || pdf_id <= 0) {
                console.error("Invalid PDF ID:", pdf_id);
                alert_float("danger", "Invalid or missing ID.");
                return;
            }

            console.log("PDF ID:", pdf_id);

            /* -------------------------
              2️⃣ VALIDATE FRAME
            -------------------------- */

            const frame = document.getElementById("pdfFrame");

            if (!frame) {
                alert_float("danger", "PDF frame not found.");
                return;
            }


            window.open(
                "<?= base_url('admin/Fees/generate/') ?>" + pdf_id + "?download=1",
                "_blank"
            );
            return false;
            const csrfName = '<?= $this->security->get_csrf_token_name(); ?>';
            let csrfHash = '<?= $this->security->get_csrf_hash(); ?>';

            show_loader();

            /* -------------------------
              3️⃣ LOAD FRAME
            -------------------------- */

            frame.onload = null; // reset previous handler
            frame.src = "<?= admin_url('Fees/generate'); ?>/" + pdf_id;

            frame.onload = async function() {

                frame.onload = null; // prevent multiple triggers

                try {

                    const frameDoc = frame.contentWindow.document;
                    const element = frameDoc.getElementById("feeStructures");

                    if (!element) {
                        throw new Error("Fee structure element not found.");
                    }

                    /* -------------------------
                      4️⃣ GENERATE PDF
                    -------------------------- */

                    const canvas = await html2canvas(element, {
                        scale: 2,
                        useCORS: true,
                        backgroundColor: "#ffffff"
                    });

                    const {
                        jsPDF
                    } = window.jspdf;
                    const pdf = new jsPDF("p", "mm", "a4");

                    const imgData = canvas.toDataURL("image/jpeg", 1.0);

                    const imgWidth = 210;
                    const pageHeight = 297;
                    const imgHeight = (canvas.height * imgWidth) / canvas.width;

                    let heightLeft = imgHeight;
                    let position = 0;

                    pdf.addImage(imgData, "JPEG", 0, position, imgWidth, imgHeight);
                    heightLeft -= pageHeight;

                    while (heightLeft > 0) {
                        position = heightLeft - imgHeight;
                        pdf.addPage();
                        pdf.addImage(imgData, "JPEG", 0, position, imgWidth, imgHeight);
                        heightLeft -= pageHeight;
                    }

                    const pdfBlob = pdf.output("blob");

                    /* -------------------------
                      5️⃣ VALIDATE PDF DATA
                    -------------------------- */

                    if (typeof pdfData === "undefined" || !pdfData) {
                        throw new Error("PDF metadata not available.");
                    }

                    const filename =
                        (pdfData.university_name || "University")
                        .replace(/\s+/g, "_")
                        .replace(/[^\w\-]/g, "") +
                        ".pdf";


                    pdf.save(filename);
                    /* -------------------------
                      6️⃣ PREPARE FORM DATA
                    -------------------------- */

                    const formData = new FormData();
                    formData.append("pdf_file", pdfBlob, filename);
                    formData.append(csrfName, csrfHash);
                    formData.append("country_name", pdfData.country_name || "");
                    formData.append("university_name", pdfData.university_name || "");
                    formData.append("segment_type", pdfData.segment_type || "");
                    formData.append("region_name", pdfData.region_name || "");

                    /* -------------------------
                      7️⃣ UPLOAD PDF
                    -------------------------- */

                    const response = await fetch("<?= base_url('admin/Fees/savePdf') ?>", {
                        method: "POST",
                        body: formData,
                        credentials: "same-origin"
                    });

                    if (!response.ok) {
                        throw new Error("Upload failed.");
                    }

                    const result = await response.text();

                    /* -------------------------
                      8️⃣ SUCCESS
                    -------------------------- */

                    hide_loader();
                    alert_float("success", "PDF generated and uploaded successfully!");
                    console.log("Server response:", result);

                    // Optional: download locally
                    // pdf.save(filename);

                } catch (innerError) {

                    hide_loader();
                    alert_float("danger", innerError.message);
                    console.error(innerError);
                }
            };

        } catch (error) {

            hide_loader();
            alert_float("danger", "Something went wrong while generating PDF.");
            console.error(error);
        }
    }
</script>