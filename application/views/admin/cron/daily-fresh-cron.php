<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<?php
$staff_Selected = !empty($staff_Selected) ? array_column($staff_Selected, 'staffid') : [];
// $staff_Selected_name = !empty($staff_Selected) ? array_column($staff_Selected, '') : [];
$non_staff_Selected =  array_column($non_staff_Selected,"staffid")??[];
?>
<style>
    /* ============ Fresh Criteria — Modern Clean UI ============ */
    .nrc-wrapper {
        --nrc-primary: #4e73df;
        --nrc-accent: #36b9cc;
        --nrc-ink: #2c3e50;
        --nrc-line: #eef1f6;
        --nrc-surface: #ffffff;
        --nrc-soft: #f7f9fc;
        --nrc-warn: #f39c12;
        --nrc-orange: #e67e22;
        --nrc-danger: #e74c3c;
        --nrc-green: #1cab8b;
        background: var(--nrc-surface);
        border-radius: 16px;
        padding: 28px 28px 24px;
        box-shadow: 0 1px 2px rgba(16, 24, 40, .04), 0 10px 30px rgba(16, 24, 40, .06);
        border: 1px solid var(--nrc-line);
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
    /* ---- Section heading ---- */
    .nrc-section {
        display: flex;
        align-items: center;
        gap: 10px;
        margin: 26px 0 14px;
    }
    .nrc-section h4 {
        margin: 0;
        font-size: 15px;
        font-weight: 700;
        color: var(--nrc-ink);
        letter-spacing: -.1px;
    }
    .nrc-section .nrc-section-line {
        flex: 1 1 auto;
        height: 1px;
        background: var(--nrc-line);
    }
    .nrc-section i { color: var(--nrc-primary); }
    /* ---- Cards ---- */
    .nrc-grid {
        margin: 0 -9px;
        display: flex;
        flex-wrap: wrap;
    }
    .nrc-col {
        padding: 9px;
        display: flex;
        float: none;
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
    }
    .nrc-card::before {
        content: "";
        position: absolute;
        left: 0; top: 0; bottom: 0;
        width: 3px;
        border-radius: 14px 0 0 14px;
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
    .nrc-pill.is-status  { background: rgba(28, 171, 139, .12); color: var(--nrc-green); }
    .nrc-pill.is-exclude { background: rgba(243, 156, 18, .12); color: #d68910; }
    /* ---- Staff select card ---- */
    /* Keep this card (and its open dropdown) above the escalation timeline below it */
    .nrc-card.is-select {
        background: #fff;
        position: relative;
        z-index: 40;
    }
    .nrc-card.is-select .selectpicker,
    .nrc-card.is-select .bootstrap-select,
    .nrc-card.is-select select {
        width: 100% !important;
        margin-top: 10px;
    }
    /* Raise the open bootstrap-select menu so it overlays everything */
    .nrc-card.is-select .bootstrap-select.open { z-index: 1050; }
    .nrc-card.is-select .bootstrap-select .dropdown-menu {
        z-index: 1060;
        box-shadow: 0 12px 30px rgba(16, 24, 40, .16);
    }
    /* ---- Escalation timeline ---- */
    .nrc-timeline {
        position: relative;
        margin: 4px 0 8px;
        padding-left: 8px;
    }
    .nrc-timeline::before {
        content: "";
        position: absolute;
        left: 27px; top: 8px; bottom: 8px;
        width: 2px;
        background: linear-gradient(180deg, var(--nrc-warn), var(--nrc-orange) 55%, var(--nrc-danger));
    }
    .nrc-step {
        position: relative;
        display: flex;
        gap: 16px;
        padding: 8px 0;
    }
    .nrc-step-dot {
        position: relative;
        z-index: 1;
        flex: 0 0 auto;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-size: 16px;
        box-shadow: 0 4px 10px rgba(16, 24, 40, .12);
        border: 3px solid #fff;
    }
    .nrc-step.warn  .nrc-step-dot { background: var(--nrc-warn); }
    .nrc-step.alert .nrc-step-dot { background: var(--nrc-orange); }
    .nrc-step.danger .nrc-step-dot { background: var(--nrc-danger); }
    .nrc-step-body {
        /*flex: 1 1 auto;*/
        background: var(--nrc-soft);
        border: 1px solid var(--nrc-line);
        border-radius: 12px;
        padding: 14px 16px;
    }
    .nrc-step.warn  .nrc-step-body { border-left: 3px solid var(--nrc-warn); }
    .nrc-step.alert .nrc-step-body { border-left: 3px solid var(--nrc-orange); }
    .nrc-step.danger .nrc-step-body { border-left: 3px solid var(--nrc-danger); }
    .nrc-step-head {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 8px 12px;
        margin-bottom: 6px;
    }
    .nrc-step-time {
        font-weight: 700;
        font-size: 14px;
        color: var(--nrc-ink);
    }
    .nrc-step-tag {
        font-size: 10px;
        font-weight: 700;
        letter-spacing: .5px;
        text-transform: uppercase;
        padding: 3px 9px;
        border-radius: 999px;
    }
    .nrc-step.warn  .nrc-step-tag { background: rgba(243, 156, 18, .14); color: #b9770e; }
    .nrc-step.alert .nrc-step-tag { background: rgba(230, 126, 34, .14); color: #c4640f; }
    .nrc-step.danger .nrc-step-tag { background: rgba(231, 76, 60, .14); color: #c0392b; }
    .nrc-step-desc {
        font-size: 13.5px;
        color: var(--nrc-ink);
        margin: 0 0 10px;
        line-height: 1.45;
    }
    .nrc-channels {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }
    .nrc-chan {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 12px;
        font-weight: 600;
        color: var(--nrc-ink);
        background: #fff;
        border: 1px solid var(--nrc-line);
        border-radius: 8px;
        padding: 5px 10px;
    }
    .nrc-chan i { color: var(--nrc-primary); font-size: 13px; }
    .nrc-chan.who { background: rgba(78,115,223,.06); }
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
    .nrc-footer i { font-size: 18px; color: var(--nrc-primary); }
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
    @media (max-width: 991px) {
        .nrc-col { width: 100%; }
    }
    @media (max-width: 575px) {
        .nrc-wrapper { padding: 20px 16px; }
        .nrc-header { flex-wrap: wrap; }
        .nrc-badge { margin-left: 0; }
        .nrc-timeline::before { left: 19px; }
        .nrc-step-dot { width: 34px; height: 34px; font-size: 14px; }
    }
       .content
    {
        overflow: hidden;
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
                            <i class="fa fa-bolt"></i>
                        </div>
                        <div>
                            <h3 class="nrc-title">Fresh Lead Criteria &amp; Escalation</h3>
                            <p class="nrc-subtitle">Daily cron monitors fresh leads during working hours and escalates if not connected</p>
                        </div>
                        <span class="nrc-badge">Daily Fresh Cron</span>
                    </div>

                    <!-- ===== Criteria ===== -->
                    <div class="nrc-section">
                        <i class="fa fa-filter"></i>
                        <h4>Lead Match Criteria</h4>
                        <span class="nrc-section-line"></span>
                    </div>
                    <div class="row nrc-grid">
                        <div class="col-md-3 nrc-col">
                            <div class="nrc-card">
                                <div class="nrc-card-top">
                                    <span class="nrc-chip"><i class="fa fa-leaf"></i></span>
                                    <p class="nrc-label">Status</p>
                                </div>
                                <span class="nrc-pill is-status">Fresh</span>
                            </div>
                        </div>
                        <div class="col-md-3 nrc-col">
                            <div class="nrc-card">
                                <div class="nrc-card-top">
                                    <span class="nrc-chip"><i class="fa fa-refresh"></i></span>
                                    <p class="nrc-label">Update Count</p>
                                </div>
                                <p class="nrc-value">Equal to 0</p>
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
                                    <span class="nrc-chip"><i class="fa fa-calendar-check-o"></i></span>
                                    <p class="nrc-label">Assigned Date</p>
                                </div>
                                <p class="nrc-value">After 14 May 2026</p>
                            </div>
                        </div>
                        <div class="col-md-6 nrc-col">
                            <div class="nrc-card">
                                <div class="nrc-card-top">
                                    <span class="nrc-chip"><i class="fa fa-clock-o"></i></span>
                                    <p class="nrc-label">Working Hours</p>
                                </div>
                                <p class="nrc-value">10:00 AM &ndash; 6:00 PM</p>
                                <small class="nrc-hint"><i class="fa fa-info-circle"></i> Holidays are ignored &mdash; only working time counts toward escalation</small>
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

                    <!-- ===== Escalation timeline ===== -->
                    <div class="nrc-section">
                        <i class="fa fa-bell"></i>
                        <h4>Escalation Timeline &mdash; Lead Not Connected</h4>
                        <span class="nrc-section-line"></span>
                    </div>
                    <div class="nrc-timeline">

                        <!-- Stage 1 : 2 hours -->
                        <div class="nrc-step warn">
                            <div class="nrc-step-dot"><i class="fa fa-exclamation"></i></div>
                            <div class="nrc-step-body">
                                <div class="nrc-step-head">
                                    <span class="nrc-step-time">After 2:00 Hours</span>
                                    <span class="nrc-step-tag">Warning</span>
                                </div>
                                <p class="nrc-step-desc">Send a warning to the assigned <strong>counsellor</strong> that the fresh lead is still not connected.</p>
                                <div class="nrc-channels">
                                    <span class="nrc-chan who"><i class="fa fa-user"></i> Counsellor</span>
                                    <span class="nrc-chan"><i class="fa fa-bell"></i> Web Push</span>
                                    <span class="nrc-chan"><i class="fa fa-mobile"></i> Mobile App</span>
                                </div>
                            </div>
                        </div>

                        <!-- Stage 2 : 2 hours 30 minutes -->
                        <div class="nrc-step alert">
                            <div class="nrc-step-dot"><i class="fa fa-users"></i></div>
                            <div class="nrc-step-body">
                                <div class="nrc-step-head">
                                    <span class="nrc-step-time">After 2:30 Hours</span>
                                    <span class="nrc-step-tag">Escalate</span>
                                </div>
                                <p class="nrc-step-desc">Still not connected &mdash; notify both the <strong>counsellor</strong> and their <strong>team leader</strong>.</p>
                                <div class="nrc-channels">
                                    <span class="nrc-chan who"><i class="fa fa-user"></i> Counsellor</span>
                                    <span class="nrc-chan who"><i class="fa fa-user-secret"></i> Team Leader</span>
                                    <span class="nrc-chan"><i class="fa fa-bell"></i> Web Push</span>
                                    <span class="nrc-chan"><i class="fa fa-mobile"></i> Mobile App</span>
                                </div>
                            </div>
                        </div>

                        <!-- Stage 3 : 3 hours -->
                        <div class="nrc-step danger">
                            <div class="nrc-step-dot"><i class="fa fa-random"></i></div>
                            <div class="nrc-step-body">
                                <div class="nrc-step-head">
                                    <span class="nrc-step-time">After 3:00 Hours</span>
                                    <span class="nrc-step-tag">Auto Transfer</span>
                                </div>
                                <p class="nrc-step-desc">Lead is automatically <strong>transferred to another counsellor</strong> (round-robin within the same pool).</p>
                                <div class="nrc-channels">
                                    <span class="nrc-chan"><i class="fa fa-exchange"></i> Reassign Lead</span>
                                    <span class="nrc-chan"><i class="fa fa-bell"></i> Web Push</span>
                                    <span class="nrc-chan"><i class="fa fa-mobile"></i> Mobile App</span>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- Footer note -->
                    <div class="nrc-footer">
                        <i class="fa fa-check-circle"></i>
                        <span>Timers count <strong>working hours only</strong> (10 AM&ndash;6 PM, holidays excluded). Only leads matching all criteria above enter the Fresh cron process.</span>
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
        var $btn  = $(this);
        var $icon = $btn.find('i');

        // Selected staff IDs (multi-select returns an array, or null when empty)
        var selectedStaff = $('select[name="selected_staff[]"]').val() || [];
         var non_selectedStaff = $('select[name="non_selected_staff[]"]').val() || [];
         
        if (selectedStaff.length === 0) {
            alert_float('danger', 'Please select at least 1 staff member.');
            return false;
        }

        // Build the payload + attach the CSRF token (name + hash from Perfex)
        var $csrf = $('#nrc-csrf');
        var payload = { selected_staff: selectedStaff , non_selected_staff: non_selectedStaff};
        payload[$csrf.attr('name')] = $csrf.val();

        $btn.prop('disabled', true);
        $icon.attr('class', 'fa fa-spinner fa-spin');

        $.ajax({
            // TODO: change this route to your real controller/method
            url: admin_url + 'cron/daily_fresh_cron',
            type: 'POST',
            dataType: 'json',
            data: payload,
            success: function (response) {
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