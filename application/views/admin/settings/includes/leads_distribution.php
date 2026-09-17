<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php hooks()->do_action('before_leads_settings'); ?>
<?php 
// echo form_hidden('settings[_leads_distribution_settings]', 'true');
?>

<?php 
$lead_type = $this->leads_model->get_type();
$select_staff_state_region = staff_state_region();
$dataData = $this->staff_model->get('', ['active' => 1]);
$table = db_prefix() . 'leads_distribution';

$tableData = $this->db
    ->select('*')
    ->from($table)
    ->get()
    ->result_array();



function getStaffOptions($staffLists, $leadType, $subRegions, $selectedStaff = []) {
    $options = '';

    // Ensure subRegions is an array
    if (!is_array($subRegions)) {
        $subRegions = array_map('trim', explode(',', $subRegions));
    }

    // Filter by lead_type
    $filtered = array_filter($staffLists, function($staff) use ($leadType, $subRegions) {
        return (string)$staff['lead_type'] === (string)$leadType
            && in_array((string)$staff['office_state_region'], $subRegions);
    });

    // Make unique by staffid
    $uniqueStaff = [];
    foreach ($filtered as $staff) {
        $uniqueStaff[$staff['staffid']] = $staff;
    }

    // Build <option> tags
    foreach ($uniqueStaff as $staff) {
        $isSelected = in_array($staff['staffid'], $selectedStaff) ? 'selected' : '';
        $name = trim(($staff['firstname'] ?? '') . ' ' . ($staff['lastname'] ?? ''));
        $options .= '<option value="' . htmlspecialchars($staff['staffid']) . '" ' . $isSelected . '>' . htmlspecialchars($name) . '</option>';
    }

    return $options;
}

function renderOptions($items, $valueKey, $textKey, $selected = '') {
    $html = '';

    // Convert $selected to array if it's a string like "1,2"
    if (!is_array($selected)) {
        $selected = array_filter(array_map('trim', explode(',', $selected)));
    }

    foreach ($items as $item) {
        $value = (string)$item[$valueKey];
        $isSelected = in_array($value, $selected) ? ' selected' : '';
        $html .= '<option value="' . htmlspecialchars($value) . '"' . $isSelected . '>' 
              . htmlspecialchars($item[$textKey]) . '</option>';
    }

    return $html;
}

?>

<style>
    #create_distribution_logic
    {
        margin-top: 20px;
    }
    .btn-bottom-toolbar
    {
        display: none;
    }
    
</style>
<h3>Leads Distribution</h3>
<hr />

<button type="button" class="btn btn-primary" onclick="create_distribution_logic(); return false; ">New</button>

<div id="create_distribution_logic">
<?php
$rowCount = 1;

foreach ($tableData as $tdata) {
    // $tdata['non_staff_ids'] may be "1" or "1,2"
    $selectedStaff = array_filter(explode(',', $tdata['non_staff_ids']));

    ?>
    <div class="row distribution-row" data-id="<?= $rowCount ?>" style="margin-bottom:15px; border:1px solid #ddd; padding:10px;">

        <!-- Lead Type -->
        <div class="col-md-2">
            <label>Lead Type</label>
            <select class="form-control lead_type selectpicker" data-live-search="true">
                <option value="">Lead Type</option>
                <?= renderOptions($lead_type, 'id', 'name', $tdata['lead_type']) ?>
            </select>
        </div>

        <!-- Region -->
        <div class="col-md-2">
            <label>Region</label>
            <select class="form-control lead_region selectpicker" data-live-search="true">
                <option value="">Region</option>
                <?= renderOptions($select_staff_state_region, 'id', 'name', $tdata['lead_region']) ?>
            </select>
        </div>

        <!-- Sub Region -->
        <div class="col-md-3">
            <label>Selected Region</label>
            <select class="form-control sub_region selectpicker" multiple data-live-search="true">
                <?= renderOptions($select_staff_state_region, 'id', 'name', $tdata['distribution_regions']) ?>
            </select>
        </div>

        <!-- Staff -->
        <div class="col-md-3">
            <label>Non Selected Staff</label>
            <select class="form-control staff selectpicker" multiple data-live-search="true">
                <?= getStaffOptions($dataData, $tdata['lead_type'], $tdata['distribution_regions'], $selectedStaff) ?>
            </select>
        </div>

        <!-- Remove -->
        <div class="col-md-2">
            <label>&nbsp;</label>
            <button class="btn btn-danger" onclick="remove_row(<?= $rowCount ?>)">Remove</button>
        </div>

    </div>
    <?php 
    $rowCount++;
}
?>

</div>
<div class="text-right">
    <button type="button" class="btn btn-primary" onclick="create()">Summit</button>
</div>


<script>

var leadTypes = <?= json_encode($lead_type ?? []) ?>;
var regions   = <?= json_encode($select_staff_state_region ?? []) ?>;
var staffLists = <?= json_encode($dataData ?? []) ?>;

let rowCount = "";

function create() {

    let data = [];
    let combinations = new Set();
    let isValid = true;

    $('.distribution-row').each(function () {

        const $row = $(this);

        const type = $row.find('.lead_type').selectpicker('val');
        const region = $row.find('.lead_region').selectpicker('val');
        let subRegions = $row.find('.sub_region').selectpicker('val') || [];
        const staff = $row.find('.staff').selectpicker('val');

        if (!Array.isArray(subRegions)) {
            subRegions = [subRegions];
        }

        // ✅ Validation
        if (!type || !region || subRegions.length === 0) {
            alert('Please fill all fields');
            $row.css('border', '1px solid red');
            isValid = false;
            return false;
        }

        // ✅ Unique check (lead_type + region)
        const key = type + '|' + region;
        if (combinations.has(key)) {
            alert('Duplicate entry for Lead Type + Region not allowed');
            $row.css('border', '1px solid red');
            isValid = false;
            return false;
        }
        combinations.add(key);

        // ✅ Convert array → comma string
        data.push({
            lead_type: type,
            region: region,
            sub_regions: subRegions.join(','), // "1,2,3"
            staff: staff
        });
    });

    if (!isValid) return;

    // ✅ Prepare FormData
    const formData = new FormData();

    // Append CSRF token (for Laravel, usually in meta tag)
  formData.append('csrf_token_name', csrfData.hash);
    // Append the rows as JSON string
    formData.append('data', JSON.stringify(data));

    // ✅ AJAX request using FormData
    $.ajax({
        url: admin_url + 'settings/distribution',
        type: 'POST',
        data: formData,
        processData: false, // important for FormData
        contentType: false, // important for FormData
        success: function (res) {
            alert('Saved successfully');
        },
        error: function (err) {
            console.error(err);
            alert('Something went wrong');
        }
    });
}


// ✅ Create Row
function create_distribution_logic() {

    rowCount =  Date.now();

    let row = `
    <div class="row distribution-row" data-id="${rowCount}" style="margin-bottom:15px; border:1px solid #ddd; padding:10px;">
        
        <!-- Lead Type -->
        <div class="col-md-2">
        <label>Lead Type</label>
            <select class="form-control lead_type selectpicker" data-live-search="true">
                <option value="">Lead Type</option>
                ${renderOptions(leadTypes, 'id', 'name')}
            </select>
        </div>

        <!-- Region -->
        <div class="col-md-2">
        <label>Region</label>
            <select class="form-control lead_region selectpicker" data-live-search="true">
                <option value="">Region</option>
                ${renderOptions(regions, 'id', 'name')}
            </select>
        </div>

        <!-- Sub Region -->
        <div class="col-md-3">
        <label>Selected Region</label>
            <select class="form-control sub_region selectpicker" multiple data-live-search="true">
                ${renderOptions(regions, 'id', 'name')}
            </select>
        </div>

        <!-- Staff -->
        <div class="col-md-3">
        <label>Non Selected Staff</label>
            <select class="form-control staff selectpicker" multiple data-live-search="true">
            </select>
        </div>

        <!-- Remove -->
        <div class="col-md-2">
        <label>&nbsp;</label>
            <button class="btn btn-danger" onclick="remove_row(${rowCount})">Remove</button>
        </div>

    </div>`;

    $('#create_distribution_logic').append(row);

    $('.selectpicker').selectpicker('refresh');
    
    setChanges();
}


// ✅ Render Options
function renderOptions(list, valueKey, textKey) {
    return list.map(item => 
        `<option value="${item[valueKey]}">${item[textKey]}</option>`
    ).join('');
}


// ✅ Remove Row
function remove_row(id) {
    $(`.distribution-row[data-id="${id}"]`).remove();
}


// ✅ Unique helper
function uniqueBy(arr, key) {
    return [...new Map(arr.map(item => [item[key], item])).values()];
}


function update_staff(rowId) {

    const $row = $(`.distribution-row[data-id="${rowId}"]`);
    if ($row.length === 0) return;

    const type = $row.find('select.lead_type').selectpicker('val');
    const region = $row.find('select.lead_region').selectpicker('val');
    let subRegions = $row.find('select.sub_region').selectpicker('val') || [];

    const $staffDropdown = $row.find('select.staff');

    if ($staffDropdown.length === 0) {
        console.warn("Staff dropdown missing");
        return;
    }

    if (!type || !region || subRegions.length === 0) {
        $staffDropdown.empty();

        if ($staffDropdown.data('selectpicker')) {
            $staffDropdown.selectpicker('refresh');
        }
        return;
    }

    let result = staffLists;

    result = result.filter(item => String(item.lead_type) === String(type));
    result = result.filter(item =>
        subRegions.includes(String(item.office_state_region))
    );

    result = uniqueBy(result, 'staffid');

    let options = result.map(item => {
        let name = `${item.firstname || ''} ${item.lastname || ''}`.trim();
        return `<option value="${item.staffid}">${name}</option>`;
    }).join('');

    $staffDropdown.html(options);

    // ✅ Safe refresh
    if ($staffDropdown.data('selectpicker')) {
        $staffDropdown.selectpicker('refresh');
    }
}

function setChanges(){
// ✅ Event Binding (FIXED)
$(document).on('changed.bs.select', '.lead_type, .lead_region, .sub_region', function () {


    let rowId = $(this).closest('.distribution-row').data('id');

    console.log("Triggered", rowId);

    update_staff(rowId);
});
}
document.addEventListener('DOMContentLoaded', function() {
    // Check if there are no .distribution-row elements inside .row
    console.log("stat");
    if (document.querySelectorAll('.row .distribution-row').length === 0) {
        create_distribution_logic();
    }
    else
    {
       // ✅ Event Binding (FIXED)
$(document).on('changed.bs.select', '.lead_type, .lead_region, .sub_region', function () {


    let rowId = $(this).closest('.distribution-row').data('id');

    console.log("Triggered", rowId);

    update_staff(rowId);
});
    }
    
    
});


</script>