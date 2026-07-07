<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<?php 
$staff_Selected =  array_column($staff_Selected,"staffid")??[];
$non_staff_Selected =  array_column($non_staff_Selected,"staffid")??[];

?>
<style>
    /* ============ Not Reachable Criteria — Modern Clean UI ============ */
    .nrc-wrapper {
        --nrc-primary: #4e73df;
        --nrc-accent: #36b9cc;
        --nrc-ink: #2c3e50;
        --nrc-muted: #8a97a8;
        --nrc-line: #eef1f6;
        --nrc-surface: #ffffff;
        --nrc-soft: #f7f9fc;
        background: var(--nrc-surface);
        border-radius: 16px;
        padding: 28px 28px 24px;
        box-shadow: 0 1px 2px rgba(16, 24, 40, .04), 0 10px 30px rgba(16, 24, 40, .06);
        border: 1px solid var(--nrc-line);
        /*max-width: 1100px;*/
        /*margin: 24px auto;*/
    }

    /* ---- Header ---- */
    .nrc-header {
        display: flex;
        align-items: center;
        gap: 16px;
        padding-bottom: 22px;
        margin-bottom: 22px;
        border-bottom: 1px solid var(--nrc-line);
    }
    .nrc-icon {
        flex: 0 0 auto;
        width: 56px;
        height: 56px;
        border-radius: 14px;
        background: linear-gradient(135deg, var(--nrc-primary), var(--nrc-accent));
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-size: 22px;
        box-shadow: 0 8px 18px rgba(78, 115, 223, .28);
    }
    .nrc-title {
        margin: 0 0 3px;
        font-weight: 700;
        font-size: 20px;
        color: var(--nrc-ink);
        letter-spacing: -.2px;
    }
    .nrc-subtitle {
        color: var(--nrc-muted);
        font-size: 13px;
        margin: 0;
    }
    .nrc-badge {
        margin-left: auto;
        align-self: flex-start;
        font-size: 11px;
        font-weight: 600;
        letter-spacing: .4px;
        text-transform: uppercase;
        color: var(--nrc-primary);
        background: rgba(78, 115, 223, .10);
        border: 1px solid rgba(78, 115, 223, .18);
        padding: 6px 12px;
        border-radius: 999px;
        white-space: nowrap;
    }

    /* ---- Cards ---- */
    /* Flex grid prevents Bootstrap float stair-stepping & equalizes card heights per row */
    .nrc-grid {
        margin: 0 -9px;
        display: flex;
        flex-wrap: wrap;
    }
    .nrc-col {
        padding: 9px;
        display: flex;
        float: none;          /* override Bootstrap float so flex controls layout */
    }

    .nrc-card {
        width: 100%;
        position: relative;
        background: var(--nrc-soft);
        border: 1px solid var(--nrc-line);
        border-radius: 14px;
        padding: 18px 18px 16px;
        height: 100%;
        transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
        /*overflow: hidden;*/
    }
    .nrc-card::before {
        content: "";
        position: absolute;
        left: 0; top: 0; bottom: 0;
        width: 3px;
        background: linear-gradient(180deg, var(--nrc-primary), var(--nrc-accent));
        opacity: 0;
        transition: opacity .18s ease;
    }
    .nrc-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 12px 24px rgba(16, 24, 40, .09);
        border-color: rgba(78, 115, 223, .25);
        background: #fff;
    }
    .nrc-card:hover::before { opacity: 1; }

    .nrc-card-top {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 12px;
    }
    .nrc-chip {
        flex: 0 0 auto;
        width: 36px;
        height: 36px;
        border-radius: 10px;
        background: rgba(78, 115, 223, .10);
        color: var(--nrc-primary);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 15px;
    }
    .content
    {
        overflow: hidden;
    }
    .nrc-label {
        font-size: 12px;
        font-weight: 600;
        letter-spacing: .3px;
        text-transform: uppercase;
        color: var(--nrc-muted);
        margin: 0;
    }
    .nrc-value {
        font-size: 16px;
        font-weight: 600;
        color: var(--nrc-ink);
        line-height: 1.35;
        margin: 0;
    }
    .nrc-hint {
        display: block;
        margin-top: 6px;
        font-size: 12px;
        color: var(--nrc-muted);
    }
    .nrc-pill {
        display: inline-block;
        font-size: 12px;
        font-weight: 600;
        padding: 4px 10px;
        border-radius: 8px;
    }
    .nrc-pill.is-status { background: rgba(231, 76, 60, .10); color: #e74c3c; }
    .nrc-pill.is-exclude { background: rgba(243, 156, 18, .12); color: #d68910; }

    /* ---- Staff select card ---- */
    .nrc-card.is-select { background: #fff; }
    .nrc-card.is-select .selectpicker,
    .nrc-card.is-select .bootstrap-select,
    .nrc-card.is-select select {
        width: 100% !important;
        margin-top: 10px;
    }

    /* ---- Footer ---- */
    .nrc-footer {
        display: flex;
        align-items: center;
        gap: 12px;
        background: linear-gradient(135deg, rgba(78, 115, 223, .07), rgba(54, 185, 204, .07));
        border: 1px solid rgba(78, 115, 223, .15);
        border-radius: 12px;
        padding: 16px 18px;
        margin-top: 18px;
        color: var(--nrc-ink);
        font-size: 14px;
    }
    .nrc-footer i {
        font-size: 18px;
        color: var(--nrc-primary);
    }

    /* ---- Action bar ---- */
    .nrc-actions {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        margin-top: 18px;
    }
    .nrc-actions .btn-nrc-save {
        background: linear-gradient(135deg, var(--nrc-primary), var(--nrc-accent));
        border: none;
        color: #fff;
        font-weight: 600;
        padding: 10px 22px;
        border-radius: 10px;
        box-shadow: 0 8px 18px rgba(78, 115, 223, .28);
        transition: transform .15s ease, box-shadow .15s ease, opacity .15s ease;
    }
    .nrc-actions .btn-nrc-save:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 24px rgba(78, 115, 223, .35);
        color: #fff;
    }
    .nrc-actions .btn-nrc-save:disabled {
        opacity: .65;
        transform: none;
        box-shadow: none;
        cursor: not-allowed;
    }
    .nrc-actions .btn-nrc-save i { margin-right: 6px; }

    /* Stack cards full-width below Bootstrap's md breakpoint (col-md-* has no width here) */
    @media (max-width: 991px) {
        .nrc-col { width: 100%; }
    }
    @media (max-width: 575px) {
        .nrc-wrapper { padding: 20px 16px; }
        .nrc-header { flex-wrap: wrap; }
        .nrc-badge { margin-left: 0; }
    }
    
    span.select-staff-name {
    display: inline-block;
    padding: 6px 12px;
    margin: 4px;
    background: #f1f5f9;
    border: 1px solid #d1d5db;
    border-radius: 20px;
    font-size: 14px;
    color: #333;
    font-weight: 500;
    line-height: 1.4;
}
</style>

<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="nrc-wrapper">

                    <!-- Header -->
                    <div class="nrc-header">
                        <div class="nrc-icon">
                            <i class="fa fa-filter"></i>
                        </div>
                        <div>
                            <h3 class="nrc-title">Not Reachable Criteria</h3>
                            <p class="nrc-subtitle">Leads must satisfy <strong>all</strong> conditions below to enter the cron process</p>
                        </div>
                        <span class="nrc-badge">Auto-filter rules</span>
                    </div>

                    <!-- Criteria grid -->
                    <div class="row nrc-grid">

                        <div class="col-md-3 nrc-col">
                            <div class="nrc-card">
                                <div class="nrc-card-top">
                                    <span class="nrc-chip"><i class="fa fa-times-circle"></i></span>
                                    <p class="nrc-label">Status</p>
                                </div>
                                <span class="nrc-pill is-status">Not Reachable</span>
                            </div>
                        </div>

                        <div class="col-md-3 nrc-col">
                            <div class="nrc-card">
                                <div class="nrc-card-top">
                                    <span class="nrc-chip"><i class="fa fa-graduation-cap"></i></span>
                                    <p class="nrc-label">Lead Type</p>
                                </div>
                                <p class="nrc-value">MBBS Abroad / Study Abroad</p>
                            </div>
                        </div>

                        <div class="col-md-3 nrc-col">
                            <div class="nrc-card">
                                <div class="nrc-card-top">
                                    <span class="nrc-chip"><i class="fa fa-plug"></i></span>
                                    <p class="nrc-label">Lead Source</p>
                                </div>
                                <p class="nrc-value">API Leads Only</p>
                            </div>
                        </div>

                        <div class="col-md-3 nrc-col">
                            <div class="nrc-card">
                                <div class="nrc-card-top">
                                    <span class="nrc-chip"><i class="fa fa-ban"></i></span>
                                    <p class="nrc-label">Exclude</p>
                                </div>
                                <span class="nrc-pill is-exclude">Mass Assignment Leads</span>
                            </div>
                        </div>

                        <div class="col-md-3 nrc-col">
                            <div class="nrc-card">
                                <div class="nrc-card-top">
                                    <span class="nrc-chip"><i class="fa fa-calendar-plus-o"></i></span>
                                    <p class="nrc-label">Lead Created</p>
                                </div>
                                <p class="nrc-value">After 01 Mar 2026</p>
                            </div>
                        </div>

                        <div class="col-md-3 nrc-col">
                            <div class="nrc-card">
                                <div class="nrc-card-top">
                                    <span class="nrc-chip"><i class="fa fa-phone"></i></span>
                                    <p class="nrc-label">Call Count</p>
                                </div>
                                <p class="nrc-value">Less than 5 Calls</p>
                            </div>
                        </div>

                        <div class="col-md-6 nrc-col">
                            <div class="nrc-card">
                                <div class="nrc-card-top">
                                    <span class="nrc-chip"><i class="fa fa-clock-o"></i></span>
                                    <p class="nrc-label">Assignment Age</p>
                                </div>
                                <p class="nrc-value">Less than 4 Working Days</p>
                                <small class="nrc-hint"><i class="fa fa-info-circle"></i> Holidays in the last 7 days are excluded</small>
                            </div>
                        </div>

                        <div class="col-md-12 nrc-col">
                            <div class="nrc-card is-select">
                                <div class="nrc-card-top">
                                    <span class="nrc-chip"><i class="fa fa-users"></i></span>
                                    <p class="nrc-label">Staff Assignment Filter</p>
                                </div>
                                <p class="nrc-value">Filter by assigned staff</p>
                                <?php echo render_select('selected_staff[]', $staff, array('staffid', array('firstname', 'lastname')), 'staff_members', $staff_Selected, array('multiple' => true), array(), '', '', false); ?>
                                <div id="selectedStaff">
                                
                            </div>
                            </div>
                            
                        </div>
                        
                        
                                    <div class="col-md-12 nrc-col">
                            <div class="nrc-card is-select">
                                <div class="nrc-card-top">
                                    <span class="nrc-chip"><i class="fa fa-users"></i></span>
                                    <p class="nrc-label">Non transfer Staff Assignment Filter</p>
                                </div>
                                <p class="nrc-value">Filter by assigned staff</p>
                                <?php echo render_select('non_selected_staff[]', $staff, array('staffid', array('firstname', 'lastname')), 'staff_members', $non_staff_Selected, array('multiple' => true), array(), '', '', false); ?>
                                <div id="nonselectedStaff">
                                    
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- Footer note -->
                    <div class="nrc-footer">
                        <i class="fa fa-check-circle"></i>
                        <span>Only leads matching <strong>all conditions</strong> above will be considered for the Not Reachable cron process.</span>
                    </div>

                    <!-- CSRF token (Perfex / CodeIgniter) -->
                    <input type="hidden" id="nrc-csrf"
                           name="<?php echo $this->security->get_csrf_token_name(); ?>"
                           value="<?php echo $this->security->get_csrf_hash(); ?>">

                    <!-- Action bar -->
                    <div class="nrc-actions">
                        <button type="button" id="nrc-save-btn" class="btn btn-nrc-save">
                            <i class="fa fa-save"></i> Save Criteria
                        </button>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<?php init_tail(); ?>
<script>
$(function () {
$('#selectedStaff').html(''); // clear old data

$('select[name="selected_staff[]"] option:selected').each(function () {
    let staffName = $(this).text().trim();

    if (staffName) {
        $("#selectedStaff").append(
            `<span class='select-staff-name'>${staffName}</span>`
        );
    }
});


$('#nonselectedStaff').html(''); // clear old data

$('select[name="non_selected_staff[]"] option:selected').each(function () {
    let staffName = $(this).text().trim();

    if (staffName) {
        $("#nonselectedStaff").append(
            `<span class='select-staff-name'>${staffName}</span>`
        );
    }
});


    var $saveBtn = $('#nrc-save-btn');

    $saveBtn.on('click', function () {
        var $btn   = $(this);
        var $icon  = $btn.find('i');

        // Selected staff IDs (multi-select returns an array, or null when empty)
        var selectedStaff = $('select[name="selected_staff[]"]').val() || [];
        
         var non_selectedStaff = $('select[name="non_selected_staff[]"]').val() || [];

if (selectedStaff.length === 0) {

    alert_float('danger','Please select at least 1 staff member.');

    return false;
}

        // Build the payload + attach the CSRF token (name + hash from Perfex)
        var $csrf = $('#nrc-csrf');
        var payload = {
            selected_staff: selectedStaff, non_selected_staff: non_selectedStaff
        };
        payload[$csrf.attr('name')] = $csrf.val();

        $btn.prop('disabled', true);
        $icon.attr('class', 'fa fa-spinner fa-spin');

        $.ajax({
            // TODO: change this route to your real controller/method
            url: admin_url + '/cron/not_reachable_cron',
            type: 'POST',
            dataType: 'json',
            data: payload,
            success: function (response) {
                // Refresh the CSRF hash if the server regenerates it (csrf_regenerate = TRUE)
                if (response && response.csrf_hash) {
                    $csrf.val(response.csrf_hash);
                }
                if (response && response.success) {
                    alert_float('success', response.message || 'Criteria saved successfully.');
                } else {
                    alert_float('danger', (response && response.message) || 'Could not save the criteria.');
                }
            },
            error: function () {
                alert_float('danger', 'Something went wrong while saving. Please try again.');
            },
            complete: function () {
                $btn.prop('disabled', false);
                $icon.attr('class', 'fa fa-save');
            }
        });
    });
});
</script>
</body>
</html>