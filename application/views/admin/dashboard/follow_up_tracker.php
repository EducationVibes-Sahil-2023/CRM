<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head();
$role = $this->db->where('staffid', get_staff_user_id())->get(db_prefix() . 'staff')->row()->role;
?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-daterangepicker/3.1/daterangepicker.css" />
<?php $this->load->helper('leads'); ?>

<style>
    .follow-up-div {
        font-family: Sans-Serif;
    }

    *,
    *::before,
    *::after {
        box-sizing: border-box;
        margin: 0;
        padding: 0
    }

    .call-drashboard .row::before {
        content: none !important
    }

    :root {
        --bg: #f3f5fb;
        --surface: #fff;
        --surface2: #f0f3f9;
        --border: rgba(0, 0, 0, .07);
        --text: #1a1d27;
        --text2: #5a6278;
        --text3: #9aa0b3;
        --radius: 12px;
        --rsm: 8px;
        --blue: #3578e5;
        --blue-lt: #dbeafe;
        --blue-dk: #1e40af;
        --teal: #0f9e75;
        --teal-lt: #d1fae5;
        --amber: #c07a0a;
        --amber-lt: #fef3c7;
        --coral: #d44c2e;
        --coral-lt: #fee2d5;
        --purple: #6d5ce8;
        --purple-lt: #ede9fe;
        --red: #c83232;
        --red-lt: #ffe4e4;
        --gray: #737985;
        --gray-lt: #f1f1ef;
        --green: #3a7d1e;
        --green-lt: #dcfce7;
    }

    .follow-up-div {

        /* LAYOUT */
        .db {
            padding: 20px 24px;
            display: flex;
            flex-direction: column;
            gap: 16px;
            max-width: 1440px;
            margin: 0 auto;
            height: 60rem;
            overflow: auto;
        }

        .row {
            display: grid;
            gap: 14px
        }

        .r5 {
            grid-template-columns: repeat(5, minmax(0, 1fr))
        }

        .r4 {
            grid-template-columns: repeat(4, minmax(0, 1fr))
        }

        .r3 {
            grid-template-columns: repeat(3, minmax(0, 1fr))
        }

        .r2 {
            grid-template-columns: repeat(2, minmax(0, 1fr))
        }

        .r21 {
            grid-template-columns: minmax(0, 2fr) minmax(0, 1fr)
        }

        .r12 {
            grid-template-columns: minmax(0, 1fr) minmax(0, 2fr)
        }

        @media(max-width:1100px) {
            .r5 {
                grid-template-columns: repeat(3, minmax(0, 1fr))
            }

            .r4 {
                grid-template-columns: repeat(2, minmax(0, 1fr))
            }
        }

        @media(max-width:780px) {
            .r3 {
                grid-template-columns: repeat(2, minmax(0, 1fr))
            }
        }

        @media(max-width:680px) {

            .r5,
            .r4,
            .r3,
            .r2,
            .r21,
            .r12 {
                grid-template-columns: 1fr
            }

            .topnav {
                height: auto;
                padding: 10px 16px
            }

            .db {
                padding: 12px 16px
            }
        }

        /* NAV */
        .topnav {
            background: var(--surface);
            border-bottom: .5px solid var(--border);
            padding: 0 24px;
            height: 58px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 100;
            gap: 12px;
            flex-wrap: wrap
        }

        .nav-brand {
            display: flex;
            align-items: center;
            gap: 10px
        }

        .nav-logo {
            width: 34px;
            height: 34px;
            background: var(--blue);
            border-radius: 9px;
            display: flex;
            align-items: center;
            justify-content: center
        }

        .nav-logo svg {
            width: 18px;
            height: 18px
        }

        .nav-title {
            font-size: 15px;
            font-weight: 700;
            color: var(--text)
        }

        .nav-sub {
            font-size: 11px;
            color: var(--text3)
        }

        .nav-right {
            display: flex;
            gap: 8px;
            align-items: center;
            flex-wrap: wrap
        }

        .nav-right select,
        .nav-right input {
            font-size: 12px;
            padding: 5px 10px;
            border-radius: var(--rsm);
            border: .5px solid var(--border);
            background: var(--surface2);
            color: var(--text);
            cursor: pointer
        }

        .btn-fu {
            font-size: 12px;
            padding: 6px 14px;
            height: 30px;
            border-radius: var(--rsm);
            border: .5px solid var(--border);
            background: var(--surface2);
            color: var(--text2);
            cursor: pointer;
            font-weight: 500;
            transition: background .15s
        }

        .btn-fu:hover {
            background: var(--border)
        }

        .btn-fu-primary {
            background: var(--blue);
            color: #fff;
            border-color: var(--blue)
        }

        .btn-fu-primary:hover {
            background: var(--blue-dk)
        }

        /* CARDS */
        .card {
            background: var(--surface);
            border: .5px solid var(--border);
            border-radius: var(--radius);
            padding: 10px
        }

        .card-title {
            font-size: 11px;
            font-weight: 700;
            color: var(--text3);
            text-transform: uppercase;
            letter-spacing: .5px;
            /*margin-bottom: 14px*/
            height: 12px;
            margin-bottom:10px;
        }

        .card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 14px
        }

        .card-header .card-title {
            margin-bottom: 0
        }

        /* KPI */
        .kpi {
            background: var(--surface);
            border: .5px solid var(--border);
            border-radius: 0 0 var(--radius) var(--radius);
            padding: 16px 18px;
            position: relative;
            overflow: visible;
            transition: transform .15s, box-shadow .15s
        }

        .kpi:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 20px rgba(0, 0, 0, .06)
        }

        .kpi-accent {
            position: absolute;
            top: -1px;
            left: -1px;
            right: -1px;
            height: 3px;
            border-radius: 12px 12px 0 0;
            pointer-events: none
        }

        .kpi-lbl {
            font-size: 14px;
            color: var(--text3);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .4px;
            margin-bottom: 6px
        }

        .kpi-val {
            font-size: 28px;
            font-weight: 800;
            color: var(--text);
            line-height: 1
        }

        .kpi-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: 8px
        }

        .kpi-sub {
            font-size: 11px;
            color: var(--text3)
        }

        /* BADGES */
        .badge {
            font-size: 12px;
            padding: 6px 10px;
            border-radius: 4px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 2px
        }

        .b-red {
            background: var(--red-lt);
            color: var(--red)
        }

        .b-amber {
            background: var(--amber-lt);
            color: var(--amber)
        }

        .b-green {
            background: var(--green-lt);
            color: var(--green)
        }

        .b-blue {
            background: var(--blue-lt);
            color: var(--blue-dk)
        }

        .b-purple {
            background: var(--purple-lt);
            color: var(--purple)
        }

        .b-gray {
            background: var(--gray-lt);
            color: var(--gray)
        }

        thead tr th {
            background-color: transparent !important;
            color: var(--text3) !important;
        }

        /* LEAD STATUS PILLS */
        .ls-pill {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 11px;
            font-weight: 700;
            padding: 5px 10px !important;
            border-radius: 10px;
            white-space: nowrap
        }

        /* TABLE */
        .tbl {
            border-collapse: collapse;
            font-size: 12px;
            width: 100%;;
        }

        .tbl th {
            font-size: 11px;
            font-weight: 700;
            color: var(--text3);
            text-transform: uppercase;
            letter-spacing: .4px;
            text-align: left;
            padding: 5px 10px;
            border-bottom: .5px solid var(--border);
            white-space: nowrap;
            cursor: pointer;
            user-select: none
        }

        .tbl th:hover {
            color: var(--text2)
        }

        .tbl td {
            padding: 9px 10px;
            border-bottom: .5px solid var(--border);
            color: var(--text);
            vertical-align: middle;
            max-width: 150px;
        overflow: hidden;
        }

        .tbl tr:last-child td {
            border-bottom: none
        }

        .tbl tbody tr:hover td {
            background: var(--surface2)
        }

        .tbl-wrap {
             max-height: 450px;   /* adjust height */
    overflow-y: auto;
    position: relative;
            /*overflow: hidden;*/
        }
        .tbl thead {
    position: sticky;
    height: 30px;
    top: 0;
    z-index: 10;
    background: var(--card-bg, #fff);
    box-shadow: 0 2px 2px rgba(0,0,0,0.05);
}

        /* AVATAR */
        .av {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: 600;
            flex-shrink: 0
        }

        .av-cell {
            display: flex;
            align-items: center;
            gap: 8px
        }

        .av-name {
            font-size: 12px;
            font-weight: 600;
            color: var(--text)
        }

        /* BAR ROWS */
        .bar-row {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 9px
        }

        .bar-row:last-child {
            margin-bottom: 0
        }

        .bar-lbl {
            font-size: 12px;
            color: var(--text2);
            width: 110px;
            flex-shrink: 0;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap
        }

        .bar-track {
            flex: 1;
            height: 13px;
            background: var(--surface2);
            border-radius: 3px;
            overflow: hidden
        }

        .bar-fill {
            height: 100%;
            border-radius: 3px;
            transition: width .4s
        }

        .bar-val {
            font-size: 12px;
            color: var(--text);
            font-weight: 700;
            width: 38px;
            text-align: right;
            flex-shrink: 0
        }

        /* OVERDUE AGING */
        .age-row {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 0;
            border-bottom: .5px solid var(--border)
        }

        .age-row:last-child {
            border-bottom: none
        }

        .age-window {
            font-size: 12px;
            font-weight: 700;
            color: var(--text);
            min-width: 110px
        }

        .age-track {
            flex: 1;
            height: 20px;
            background: var(--surface2);
            border-radius: 3px;
            overflow: hidden
        }

        .age-fill {
            height: 100%;
            border-radius: 3px;
            transition: width .5s ease
        }

        .age-val {
            font-size: 12px;
            font-weight: 700;
            color: var(--text);
            min-width: 24px;
            text-align: right
        }

        .age-badge {
            font-size: 10px;
            padding: 2px 6px;
            border-radius: 4px;
            font-weight: 700;
            flex-shrink: 0;
            min-width: 44px;
            text-align: center
        }

        /* SPARKLINE */
        .spark-wrap {
            display: flex;
            align-items: flex-end;
            gap: 2px;
            height: 36px
        }

        .spark-bar {
            flex: 1;
            border-radius: 2px;
            min-width: 6px
        }

        /* OVERDUE BAND */
        .overdue-band {
            background: var(--red-lt);
            border: .5px solid rgba(200, 50, 50, .2);
            border-radius: var(--rsm);
            padding: 10px 14px;
            display: flex;
            align-items: center;
            gap: 10px
        }

        .ob-icon {
            width: 32px;
            height: 32px;
            background: var(--red);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0
        }

        .ob-title {
            font-size: 13px;
            font-weight: 700;
            color: var(--red)
        }

        .ob-sub {
            font-size: 11px;
            color: #922b10;
            margin-top: 1px
        }

        .ob-cta {
            margin-left: auto;
            flex-shrink: 0
        }

        /* SECTION SEPARATOR */
        .section-sep {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 11px;
            font-weight: 700;
            color: var(--text3);
            text-transform: uppercase;
            letter-spacing: .6px
        }

        .section-sep::after {
            content: '';
            flex: 1;
            height: .5px;
            background: var(--border)
        }

        /* TOOLTIP */
        .kpi-lbl-row {
            display: flex;
            align-items: center;
            gap: 0;
            margin-bottom: 6px
        }

        .kpi-lbl-row .kpi-lbl {
            margin-bottom: 0
        }

        .tooltip-wrap {
            position: relative;
            display: inline-flex;
            align-items: center
        }

        .tooltip-box {
            display: none;
            position: absolute;
            bottom: calc(100% + 10px);
            left: 50%;
            transform: translateX(-50%);
            background: #1a1d27;
            color: #f0f2f8;
            font-size: 11px;
            font-weight: 400;
            line-height: 1.6;
            padding: 10px 13px;
            border-radius: var(--rsm);
            width: 230px;
            z-index: 9999;
            white-space: normal;
            text-align: left;
            pointer-events: none;
            box-shadow: 0 4px 16px rgba(0, 0, 0, .18)
        }

        .tooltip-box strong {
            color: #fff;
            font-weight: 700
        }

        .tooltip-box::after {
            content: '';
            position: absolute;
            top: 100%;
            left: 50%;
            transform: translateX(-50%);
            border: 6px solid transparent;
            border-top-color: #1a1d27
        }

        .tooltip-wrap:hover .tooltip-box {
            display: block
        }

        .info-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 15px;
            height: 15px;
            border-radius: 50%;
            background: var(--surface2);
            border: .5px solid var(--border);
            color: var(--text3);
            font-size: 9px;
            font-weight: 800;
            cursor: pointer;
            flex-shrink: 0;
            line-height: 1;
            vertical-align: middle;
            margin-left: 4px;
            font-style: normal
        }

        .info-btn:hover {
            background: var(--blue-lt);
            color: var(--blue-dk);
            border-color: var(--blue)
        }

        /* AT-RISK TOOLTIP */
        .follow_up_counsellors_risk_wrap {
            position: relative;
            display: inline-block
        }

        .risk-tooltip-wrap {
            display: none;
            position: absolute;
            top: calc(100% + 8px);
            left: 50%;
            transform: translateX(-50%);
            z-index: 999
        }

        .follow_up_counsellors_risk_wrap:hover .risk-tooltip-wrap {
            display: block
        }


        .risk-tooltip {
            background: #1a1d27;
            border-radius: 10px;
            padding: 14px 16px;
            min-width: 260px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, .25)
        }

        .risk-tooltip::before {
            content: '';
            position: absolute;
            top: -6px;
            left: 50%;
            transform: translateX(-50%);
            border: 6px solid transparent;
            border-bottom-color: #1a1d27
        }

        .risk-tooltip-title {
            font-size: 10px;
            font-weight: 700;
            color: #9aa0b3;
            text-transform: uppercase;
            letter-spacing: .5px;
            margin-bottom: 10px
        }

        .risk-item {
            padding: 8px 0;
            border-bottom: .5px solid rgba(255, 255, 255, .08)
        }

        .risk-item:last-child {
            border-bottom: none;
            padding-bottom: 0
        }

        .risk-item-top {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 6px
        }

        .risk-av {
            width: 26px;
            height: 26px;
            border-radius: 50%;
            background: rgba(200, 50, 50, .2);
            color: #ff6b6b;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 9px;
            font-weight: 800;
            flex-shrink: 0
        }

        .risk-name {
            font-size: 12px;
            font-weight: 600;
            color: #e4e8f4;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis
        }

        .risk-meta {
            font-size: 10px;
            color: #8892a8
        }

        .risk-pct {
            font-size: 14px;
            font-weight: 800;
            flex-shrink: 0
        }

        .risk-bar-track {
            height: 4px;
            background: rgba(255, 255, 255, .08);
            border-radius: 2px;
            overflow: hidden
        }

        .risk-bar-fill {
            height: 100%;
            border-radius: 2px;
            transition: width .3s ease
        }

        /* TABS */
        .tab-bar {
            background: var(--surface);
            border-bottom: .5px solid var(--border);
            display: flex;
            align-items: flex-end;
            padding: 0 24px;
            gap: 0;
            position: sticky;
            top: 58px;
            z-index: 99
        }

        .tab-btn {
            padding: 12px 20px;
            font-size: 13px;
            font-weight: 600;
            color: var(--text3);
            border: none;
            background: transparent;
            cursor: pointer;
            border-bottom: 2.5px solid transparent;
            margin-bottom: -.5px;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: color .15s, border-color .15s;
            white-space: nowrap
        }

        .tab-btn:hover {
            color: var(--text2)
        }

        .tab-btn.active {
            color: var(--blue);
            border-bottom-color: var(--blue)
        }

        .tab-count {
            font-size: 11px;
            font-weight: 700;
            padding: 2px 7px;
            border-radius: 10px;
            background: var(--surface2)
        }

        .tab-btn.active .tab-count {
            background: var(--blue-lt);
            color: var(--blue-dk)
        }

        .tab-pane {
            display: none
        }

        .tab-pane.active {
            display: flex;
            flex-direction: column;
            gap: 16px
        }

        /* MODAL */
        #modalOverlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, .45);
            z-index: 200;
            align-items: center;
            justify-content: center
        }

        #modalOverlay.open {
            display: flex
        }

        .modal-box {
            background: var(--surface);
            border-radius: var(--radius);
            padding: 24px;
            width: 480px;
            max-width: 95vw;
            border: .5px solid var(--border)
        }

        .modal-title {
            font-size: 15px;
            font-weight: 700;
            color: var(--text);
            margin-bottom: 16px
        }

        .form-row-fu {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-bottom: 10px
        }

        .form-field-fu {
            display: flex;
            flex-direction: column;
            gap: 4px
        }

        .form-field-fu label {
            font-size: 11px;
            font-weight: 600;
            color: var(--text3);
            text-transform: uppercase;
            letter-spacing: .4px
        }

        .form-field-fu input,
        .form-field-fu select,
        .form-field-fu textarea {
            font-size: 12px;
            padding: 7px 10px;
            border-radius: var(--rsm);
            border: .5px solid var(--border);
            background: var(--surface2);
            color: var(--text)
        }

        .form-field-fu textarea {
            resize: vertical;
            min-height: 60px
        }

        .modal-actions {
            display: flex;
            gap: 8px;
            justify-content: flex-end;
            margin-top: 16px
        }

        /* SKELETON LOADER */
        .skeleton {
            background: linear-gradient(90deg, var(--surface2) 25%, var(--surface) 50%, var(--surface2) 75%);
            background-size: 200% 100%;
            animation: shimmer 1.5s infinite;
            border-radius: var(--radius);
            height: 90px
        }

        @keyframes shimmer {
            0% {
                background-position: 200% 0
            }

            100% {
                background-position: -200% 0
            }
        }

        #completionLegend,#completionLegendOverdue {
            padding: 0px;
        }

        .prio-dot {
            width: 9px;
            height: 9px;
            border-radius: 50%;
            flex-shrink: 0
        }

        .prio-critical {
            background: #c83232
        }

        .prio-high {
            background: #d44c2e
        }

        .prio-medium {
            background: #c07a0a
        }

        .prio-low {
            background: #0f9e75
        }

        .filter-div-section .filter-div {
            display: inline-block;
            width: 150px;
            margin-right: 10px;
        }

        .filter-div-section {
            margin-top: 16px;
        }

    }

    #wrapper {
        overflow-x: auto;
    }

    #show-follow-status span {
        cursor: pointer;
    }
    .cursor
    {
        cursor: pointer;
    }
    .p-5
    {
        padding: 5px !important;
    }
    .chart-design
    {
        display: none;
        margin-left: 200px;
        height: 200px;
    }
    .badge
    {
        cursor: pointer;
    }
    .btn-ex
    {
            font-size: 10px;
    padding: 3px 6px;
    }
    #ghostAreaSection
    {
        max-height: 450px;
        overflow: auto;
    }
</style>

<div id="wrapper" class="follow-up-div">
    <div class="screen-options-area"></div>
    <div class="content">
        <div class="">
            <div class="col-md-12 mtop15">
                <div class="panel_s">
                    <div class="panel-body call-drashboard">

                        <!-- TOP NAV -->
                        <div class="topnav">
                            <div class="nav-brand">
                                <div class="nav-logo">
                                    <svg viewBox="0 0 18 18" fill="none">
                                        <path d="M9 2L11 7H16L12 10.5L13.5 16L9 12.5L4.5 16L6 10.5L2 7H7L9 2Z" fill="white" opacity=".9" />
                                    </svg>
                                </div>
                                <div>
                                    <div class="nav-title">Follow-up Tracker</div>
                                </div>
                            </div>
                        </div>
                        <div class="filter-div-section">
                            <div class="filter-div">
                                <label>Follow-up Date</label><br>
                                <!--<input type="date" id="dateFrom" class="form-control dateRange" value="<?php echo date('Y-m-d'); ?>">-->
                                <input type="text" class="dateRange form-control" id="updateDate" readonly placeholder="Select Date Range">
                            </div>


                            <?php
                            echo '<div class="filter-div">';
                            echo ' <label>Lead Sources</label>';
                            echo render_select('view_source[]', $sources, array('id', 'name'), '', '', array('data-width' => '100%', 'data-none-selected-text' => _l('Lead Source'), 'multiple' => true, 'data-actions-box' => true), array(), 'no-mbot', '', false, "view_source");
                            echo '</div>';
                            ?>

                            <?php
                            echo '<div class="filter-div">';
                            echo ' <label>Lead Status</label>';
                            echo render_select('view_status[]', $statuses, array('id', 'name'), '', [], array('data-width' => '100%', 'data-none-selected-text' => _l('Lead Status'), 'multiple' => true, 'data-actions-box' => true), array(), 'no-mbot', '', false, 'view_status');
                            echo '</div>';
                            ?>

                            <?php if (is_admin() || $role == 3 || get_staff_user_id() == IVR_AUTO_ASIGNATION ) {  ?>
                                <?php
                                if (is_admin() ||get_staff_user_id() == IVR_AUTO_ASIGNATION ) {
                                    echo '<div class="filter-div">';
                                    echo ' <label>Department</label>';
                                    echo render_select('staff_department[]', $staff_department, array('id', 'name'), '', [], array('data-width' => '100%', 'data-none-selected-text' => _l('Department'), 'multiple' => true, 'data-actions-box' => true), array(), 'no-mbot', '', false, 'staff_department');
                                    echo '</div>';
                                }
                                ?>

                                <?php
                                echo '<div class="filter-div">';
                                echo ' <label>Office Location</label>';
                                echo render_select('office_location[]', $office_location, array('id', 'name'), '', [], array('data-width' => '100%', 'data-none-selected-text' => _l('Location'), 'multiple' => true, 'data-actions-box' => true), array(), 'no-mbot', '', false, 'office_location');
                                echo '</div>';
                                ?>

                                <?php
                                echo '<div class="filter-div">';
                                echo ' <label>Assign</label>';
                                echo render_select('staff[]', $staff, array('staffid', array('firstname', 'lastname')), '', [], array('data-width' => '100%', 'data-none-selected-text' => _l('Assign'), 'multiple' => true, 'data-actions-box' => true), array(), 'no-mbot', '', false, 'staff');
                                echo '</div>';
                                ?>
                            <?php } ?>

                            <button class="btn-fu btn-fu-primary" onclick="applyFilter();">Apply</button>
                            <button class="btn-fu" onclick="applyFilter();">&#8635; Refresh</button>
                        </div>

                        <!-- TAB BAR -->
                        <div class="tab-bar row">
                            <button class="tab-btn active" data-active='Follow-up Queue' onclick="switchTab('dashboard',this)">
                                <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
                                    <rect x="1" y="1" width="5" height="5" rx="1" fill="currentColor" opacity=".7" />
                                    <rect x="8" y="1" width="5" height="5" rx="1" fill="currentColor" />
                                    <rect x="1" y="8" width="5" height="5" rx="1" fill="currentColor" />
                                    <rect x="8" y="8" width="5" height="5" rx="1" fill="currentColor" opacity=".7" />
                                </svg>
                                Dashboard
                            </button>
                            <button class="tab-btn" data-active='Dashboard' onclick="switchTab('queue',this)">
                                <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
                                    <rect x="1" y="2" width="8" height="1.5" rx=".75" fill="currentColor" />
                                    <rect x="1" y="5.5" width="12" height="1.5" rx=".75" fill="currentColor" />
                                    <rect x="1" y="9" width="10" height="1.5" rx=".75" fill="currentColor" />
                                    <rect x="1" y="12.5" width="6" height="1.5" rx=".75" fill="currentColor" />
                                </svg>
                                Follow-up queue
                                <span class="tab-count" id="queueCount">—</span>
                            </button>
                            
                              <button class="tab-btn" onclick="switchTab('overdue',this)">
                                <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
                                    <rect x="1" y="2" width="8" height="1.5" rx=".75" fill="currentColor" />
                                    <rect x="1" y="5.5" width="12" height="1.5" rx=".75" fill="currentColor" />
                                    <rect x="1" y="9" width="10" height="1.5" rx=".75" fill="currentColor" />
                                    <rect x="1" y="12.5" width="6" height="1.5" rx=".75" fill="currentColor" />
                                </svg>
                                Follow-up Overdue
                                <span class="tab-count" id="overdueCount">—</span>
                            </button>
                            
                              <button class="tab-btn" onclick="switchTab('future',this)">
                                <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
                                    <rect x="1" y="2" width="8" height="1.5" rx=".75" fill="currentColor" />
                                    <rect x="1" y="5.5" width="12" height="1.5" rx=".75" fill="currentColor" />
                                    <rect x="1" y="9" width="10" height="1.5" rx=".75" fill="currentColor" />
                                    <rect x="1" y="12.5" width="6" height="1.5" rx=".75" fill="currentColor" />
                                </svg>
                                Follow-up Future
                                <span class="tab-count" id="futureCount">—</span>
                                
                                <button class="tab-btn" onclick="switchTab('completed',this)">
                                <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
                                    <rect x="1" y="2" width="8" height="1.5" rx=".75" fill="currentColor" />
                                    <rect x="1" y="5.5" width="12" height="1.5" rx=".75" fill="currentColor" />
                                    <rect x="1" y="9" width="10" height="1.5" rx=".75" fill="currentColor" />
                                    <rect x="1" y="12.5" width="6" height="1.5" rx=".75" fill="currentColor" />
                                </svg>
                                Follow-up completed
                                <span class="tab-count" id="completedCount">—</span>
                            </button>
                        </div>

                        <!-- MAIN DB -->
                        <div class="db">

                          
                            <!-- ═══ TAB 1: DASHBOARD ═══ -->
                            <div class="tab-pane active" id="tab-dashboard">
                                
                                  <div class="overdue-band row" id="overdueBand" style="display:none">
                                <div class="ob-icon">
                                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                        <path d="M8 2a6 6 0 100 12A6 6 0 008 2zm0 3v3.5l2 2" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                </div>
                                <div>
                                    <div class="ob-title" id="obTitle"></div>
                                    <div class="ob-sub" id="obSub"></div>
                                </div>
                                <!--<div class="ob-cta">-->
                                <!--    <button class="btn-fu b-red" style="border-color:var(--red);color:var(--red);background:transparent" onclick="switchTab()">Go to <span id="active-section"></span> &rarr;</button>-->
                                <!--</div>-->
                            </div>

                                <!-- OVERDUE ALERT BAND -->


                                <!-- KPI ROW 1 — Summary -->
                                <div class="row r3" id="kpiRow1">
                                    <div class="skeleton"></div>
                                    <div class="skeleton"></div>
                                    <div class="skeleton"></div>
                                </div>

                                <!-- KPI ROW 2 — Funnel Buckets (dynamic) -->
                                <div class="row r3" id="kpiRow2">
                                    <div class="skeleton"></div>
                                    <div class="skeleton"></div>
                                    <div class="skeleton"></div>
                                </div>

                                <!-- KPI ROW 3 — Team Health -->
                                <div class="row r3" id="kpiRow3">
                                    <div class="skeleton"></div>
                                    <div class="skeleton"></div>
                                    <div class="skeleton"></div>
                                </div>

                                <div class="row section-sep">Counsellor accountability</div>

                                <!-- COUNSELLOR TABLE -->
                                <div class="row card">
                                    <div class="card-header">
                                        <div class="card-title">Counsellor follow-up workload &amp; accountability</div>
                                        <div id="counsellorStatusFilter" style="display:flex;gap:6px;align-items:center">
                                        </div>
                                        <!--<div style="display:flex;gap:6px">-->
                                        <!--  <button class="btn-fu" onclick="sortCounsellorTable('name')">Sort: Name</button>-->
                                        <!--  <button class="btn-fu" onclick="sortCounsellorTable('overdue')">Sort: Overdue</button>-->
                                        <!--  <button class="btn-fu" onclick="sortCounsellorTable('completion_percentage')">Sort: Rate</button>-->
                                        <!--</div>-->
                                    </div>
                                    <div class="tbl-wrap">
                                        <table class="tbl" id="counsellorTable">
                                            <thead id="counsellorHead"></thead>
                                            <tbody id="counsellorBody">
                                                <tr>
                                                    <td colspan="11" style="text-align:center;padding:30px;color:var(--text3)">Loading...</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <!-- CHARTS ROW 1 -->
                                <div class="row r2">
                                    <div class="card">
                                        <div class="card-title">Follow-up volume by lead status</div>
                                        <div style="display:flex;flex-wrap:wrap;gap:8px;margin-bottom:12px" id="statusLegend"></div>
                                        <p class="text-right">
                                                <button class="btn btn-ex btn-primary" onclick="setStatusFilter('all')">All</button>
                                                <button class="btn btn-ex btn-success" onclick="setStatusFilter('completed')">Completed</button>
                                                <button class="btn btn-ex btn-warning" onclick="setStatusFilter('pending')">Pending</button>
                                        </p>
                                        
                                        <div style="position:relative;height:300px; margin-top:20px;"><canvas id="statusChart"></canvas></div>
                                    </div>
                                    <div class="card">
                                        <div class="card-title">Overdue aging analysis</div>
                                        <div id="agingArea"></div>
                                    </div>
                                </div>

                                <!-- CHARTS ROW 2 -->
                                <div class="col-md-12 row">
                                    <div class="">
                                        <div class="card-title row">Missed follow-ups — by counsellor</div>
                                        <div id="missedArea"></div>
                                    </div>
                                    
                                        <div  class="row r2">
                                            
                                                <div style="" class="col-md-12 card">
                                                <div class="card-title">Follow-up completion split — by lead type</div>
                                                <div class="col-md-4 chart-design"><canvas id="completionChart"></canvas></div>
                                                <div id="completionLegend" class="col-md-12"></div>
                                                </div>
                                                
                                                <div class="col-md-12 card">
                                                <div class="card-title">Follow-up pending Overdue split — by lead type</div>
                                                <div class="col-md-4 chart-design"><canvas id="completionChartOverdue"></canvas></div>
                                                <div id="completionLegendOverdue" class="col-md-12"></div>
                                                </div>
                                        </div>
                                   
                                </div>

                                <!-- TREND — full width -->
                                <!--<div class="card">-->
                                <!--  <div class="card-title">7-day follow-up completion trend — team</div>-->
                                <!--  <div style="position:relative;height:150px"><canvas id="trendChart"></canvas></div>-->
                                <!--</div>-->

                                <!-- GHOSTED -->
                                 <div class="card-title row d-flex">Ghosted leads — no response after 3+ attempts 
                                 <?php if($role==3 || is_admin()){ ?>
                                 <span><button onclick="exportExcel('ghosted')" class="btn btn-ex btn-success" id="ghostedDownload"><i class="fa fa-download"></i> Export Excel</button></span>
                                 <?php } ?> 
                                 
                                 </div>
                                <div class="card row" id="ghostAreaSection">
                                   
                                    <div id="ghostArea"></div>
                                </div>

                            </div><!-- end tab-dashboard -->

                            <!-- ═══ TAB 2: FOLLOW-UP QUEUE ═══ -->
                            <div class="tab-pane row" id="tab-queue">
                                <div class="card">
                                    <div class="card-header">
                                        <div class="card-title">Pending follow-ups — sorted by priority &amp; time</div>
                                        <div id="show-follow-status" style="display:flex;gap:6px;align-items:center">
                                        </div>
                                    </div>
                                    <div class="tbl-wrap tbl-wrap-scroll">
                                        <table class="tbl" id="queueTable">
                                            <thead>
                                                <tr>
                                                    <th>Priority</th>
                                                    <th>Student name</th>
                                                    <th>Contact no.</th>
                                                    <th>Lead status</th>
                                                    <th>Source</th>
                                                    <th>Created date</th>
                                                    <th>Follow Up date</th>
                                                    <th>Counsellor</th>
                                                    <th>Last connected</th>
                                                    <th>Due time</th>
                                                    <th>Attempts</th>
                                                    <th>Notes</th>
                                                </tr>
                                            </thead>
                                            <tbody id="queueBody" class="">
                                                <tr>
                                                    <td colspan="12" style="text-align:center;padding-top:20px;">
                                                        <div class="table-loader"></div>
                                                        <div style="font-size:12px;color:#9aa0b3;margin-top:6px;">
                                                            Loading follow-ups...
                                                        </div>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div><!-- end tab-queue -->
                            
                             <div class="tab-pane row" id="tab-overdue">
                                <div class="card">
                                    <div class="card-header">
                                        <div class="card-title">Overdue follow-ups — sorted by priority &amp; time</div>
                                        <div id="overdue-show-follow-status" style="display:flex;gap:6px;align-items:center">
                                        </div>
                                    </div>
                                    <div class="tbl-wrap tbl-wrap-scroll">
                                        <table class="tbl" id="overdueTable">
                                            <thead>
                                                <tr>
                                                    <th>Priority</th>
                                                    <th>Student name</th>
                                                    <th>Contact no.</th>
                                                    <th>Lead status</th>
                                                    <th>Source</th>
                                                    <th>Created date</th>
                                                    <th>Follow Up date</th>
                                                    <th>Counsellor</th>
                                                    <th>Last connected</th>
                                                    <th>Due time</th>
                                                    <th>Attempts</th>
                                                    <th>Notes</th>
                                                </tr>
                                            </thead>
                                            <tbody id="overdueBody" class="">
                                                <tr>
                                                    <td colspan="12" style="text-align:center;padding-top:20px;">
                                                        <div class="table-loader"></div>
                                                        <div style="font-size:12px;color:#9aa0b3;margin-top:6px;">
                                                            Loading follow-ups...
                                                        </div>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div><!-- end tab-queue -->
                            
                            <div class="tab-pane row" id="tab-future">
                                <div class="card">
                                    <div class="card-header">
                                        <div class="card-title">Future follow-ups — sorted by priority &amp; time</div>
                                        <div id="future-show-follow-status" style="display:flex;gap:6px;align-items:center">
                                        </div>
                                    </div>
                                    <div class="tbl-wrap tbl-wrap-scroll">
                                        <table class="tbl" id="futureTable">
                                            <thead>
                                                <tr>
                                                    <th>Priority</th>
                                                    <th>Student name</th>
                                                    <th>Contact no.</th>
                                                    <th>Lead status</th>
                                                    <th>Source</th>
                                                    <th>Created date</th>
                                                    <th>Follow Up date</th>
                                                    <th>Counsellor</th>
                                                    <th>Last connected</th>
                                                    <th>Due time</th>
                                                    <th>Attempts</th>
                                                    <th>Notes</th>
                                                </tr>
                                            </thead>
                                            <tbody id="futureBody" class="">
                                                <tr>
                                                    <td colspan="12" style="text-align:center;padding-top:20px;">
                                                        <div class="table-loader"></div>
                                                        <div style="font-size:12px;color:#9aa0b3;margin-top:6px;">
                                                            Loading follow-ups...
                                                        </div>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div><!-- end tab-queue -->
                            
                            
                             <div class="tab-pane row" id="tab-completed">
                                <div class="card">
                                    <div class="card-header">
                                        <div class="card-title">Completed follow-ups — sorted by priority &amp; time</div>
                                        <div id="completed-show-follow-status" style="display:flex;gap:6px;align-items:center">
                                        </div>
                                    </div>
                                    <div class="tbl-wrap tbl-wrap-scroll">
                                        <table class="tbl" id="completedTable">
                                            <thead>
                                                <tr>
                                                    <th>Priority</th>
                                                    <th>Student name</th>
                                                    <th>Contact no.</th>
                                                    <th>Lead status</th>
                                                    <th>Source</th>
                                                    <th>Created date</th>
                                                    <th>Follow Up date</th>
                                                    <th>Counsellor</th>
                                                    <th>Last connected</th>
                                                    <th>Due time</th>
                                                    <th>Attempts</th>
                                                    <th>Notes</th>
                                                </tr>
                                            </thead>
                                            <tbody id="completedBody" class="">
                                                <tr>
                                                    <td colspan="12" style="text-align:center;padding-top:20px;">
                                                        <div class="table-loader"></div>
                                                        <div style="font-size:12px;color:#9aa0b3;margin-top:6px;">
                                                            Loading follow-ups...
                                                        </div>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div><!-- end tab-queue -->

                        </div><!-- end db -->

                        <!-- MODAL -->
                        <div id="modalOverlay">
                            <div class="modal-box">
                                <div class="modal-title">Log / Schedule Follow-up</div>
                                <div class="form-row-fu">
                                    <div class="form-field-fu"><label>Student name</label><input type="text" placeholder="e.g. Rahul Verma"></div>
                                    <div class="form-field-fu"><label>Phone / WhatsApp</label><input type="text" placeholder="+91 98765 43210"></div>
                                </div>
                                <div class="form-row-fu">
                                    <div class="form-field-fu"><label>Lead status</label>
                                        <select>
                                            <option>Hot lead</option>
                                            <option>Warm lead</option>
                                            <option>Cold lead</option>
                                            <option>Callback</option>
                                            <option>New lead</option>
                                        </select>
                                    </div>
                                    <div class="form-field-fu"><label>Country interest</label>
                                        <select>
                                            <option>UK</option>
                                            <option>USA</option>
                                            <option>Canada</option>
                                            <option>Australia</option>
                                            <option>Germany</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-row-fu">
                                    <div class="form-field-fu"><label>Follow-up type</label>
                                        <select>
                                            <option>Phone call</option>
                                            <option>WhatsApp</option>
                                            <option>Email</option>
                                            <option>In-person visit</option>
                                        </select>
                                    </div>
                                    <div class="form-field-fu"><label>Due date &amp; time</label><input type="datetime-local"></div>
                                </div>
                                <div class="form-field-fu" style="margin-bottom:10px"><label>Notes</label><textarea placeholder="e.g. Interested in MSc Finance UK..."></textarea></div>
                                <div class="modal-actions">
                                    <button class="btn-fu" onclick="closeModal()">Cancel</button>
                                    <button class="btn-fu btn-fu-primary" onclick="closeModal()">Save follow-up</button>
                                </div>
                            </div>
                        </div>

                    </div><!-- panel-body -->
                </div>
            </div>
        </div>
    </div>
</div>

<?php init_tail(); ?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-daterangepicker/3.1/daterangepicker.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/xlsx/dist/xlsx.full.min.js"></script>

<script>
    
    function initDatePicker(selector, extraRanges = {}) {

    const $el = $(selector);

    $el.daterangepicker({

        autoUpdateInput: false,
        autoApply: false,
        showDropdowns: true,
        linkedCalendars: false,

        startDate: moment(),
        endDate: moment(),

        minDate: moment("2023-01-01"),
        maxDate: moment(),

        opens: "left",

        locale: {
            cancelLabel: "Clear",
            format: "YYYY-MM-DD"
        },

        ranges: Object.assign({

            "Today": [moment(), moment()],
            "Yesterday": [moment().subtract(1, "days"), moment().subtract(1, "days")],
            "Last 7 Days": [moment().subtract(6, "days"), moment()],
            "Last 30 Days": [moment().subtract(29, "days"), moment()],
            "This Month": [moment().startOf("month"), moment().endOf("month")],
            "Last Month": [
                moment().subtract(1, "month").startOf("month"),
                moment().subtract(1, "month").endOf("month")
            ]

        }, extraRanges)

    });

    // ✅ DEFAULT = TODAY
    updateDateText($el, moment(), moment());

    // APPLY EVENT
    $el.on("apply.daterangepicker", function(ev, picker) {

        updateDateText($el, picker.startDate, picker.endDate);

    });

    // CLEAR EVENT
    $el.on("cancel.daterangepicker", function() {

        $el.val('');
        $el.removeData("from").removeData("to");

    });

}
      
      
      function updateDateText($el, start, end) {

    const text = start.isSame(end, 'day')
        ? start.format("MMM D")
        : `${start.format("MMM D")} - ${end.format("MMM D")}`;

    // ✅ set value in input (NOT span)
    $el.val(text);

    // ✅ store values for backend
    $el.data("from", start.format("YYYY-MM-DD"));
    $el.data("to", end.format("YYYY-MM-DD"));
}

      
initDatePicker("#updateDate");
    
     function bindHoverEffect() {
        console.log("okkkkk");
    const items = document.querySelectorAll('.follow_up_counsellors_risk_wrap');

    items.forEach(item => {
        item.addEventListener('mouseenter', function () {
            console.log("enter");

            document.querySelectorAll('.spark-bar').forEach(el => {
                el.style.addClass = 'opacity_1';
            });
        });

        item.addEventListener('mouseleave', function () {
            console.log("leave");

            document.querySelectorAll('.spark-bar').forEach(el => {
                el.style.addClass = 'opacity_5';
            });
        });
    });
}

    function applyFilter() {
        
          let from = $('#updateDate').data('from') || '';
        let to   = $('#updateDate').data('to') || '';
        
        if (from && to) {
        
        let fromDate = new Date(from);
        let toDate   = new Date(to);
        
        // Calculate difference in days
        let diffTime = toDate - fromDate;
        let diffDays = diffTime / (1000 * 60 * 60 * 24);
        
        if (diffDays > 31) {
            hide_loader();
        alert("Date range cannot be more than 31 days");
        return false; // ⛔ stop execution
        }
        }
        
        

        loadFollowupDashboard();
        $('#queueBody,#overdueBody,#futureBody').html(`<tr>
      <td colspan="12" style="text-align:center;padding:20px;">
        <div class="table-loader"></div>
        <div style="font-size:12px;color:#9aa0b3;margin-top:6px;">
          Loading follow-ups...
        </div>
      </td>
    </tr>`);

        setTimeout(function() {
            loadTable();
        }, 500); // runs after 1 second

        let label = $(".tab-bar button.tab-btn.active").data('active');

        $("#active-section").text(label);

    }


    /* ═══════════════════════════════════════════════
       CONSTANTS & GLOBALS
       ═══════════════════════════════════════════════ */
    var BLUE = '#3578e5',
        TEAL = '#0f9e75',
        AMBER = '#c07a0a',
        CORAL = '#d44c2e',
        PURPLE = '#6d5ce8',
        RED = '#c83232',
        GREEN = '#3a7d1e',
        GRAY = '#737985';

    var statusChartInstance = null;
    var completionChartInstance = {};
    var trendChartInstance = null;

    var _counsellorData = [];
    var _funnelNames = [];
    var sortKey = 'overdue',
        sortDir = -1;

    /* ═══════════════════════════════════════════════
       UTILITY FUNCTIONS (defined ONCE)
       ═══════════════════════════════════════════════ */
    function initials(name) {
        if (!name) return '?';
        return name.split(' ').map(function(w) {
            return w.charAt(0).toUpperCase();
        }).join('');
    }

    var AV_COLORS = [
        ['#dbeafe', '#1e40af'],
        ['#d1fae5', '#065f46'],
        ['#ede9fe', '#4c3dbf'],
        ['#fef3c7', '#92570a'],
        ['#fee2d5', '#922b10'],
        ['#f1f1ef', '#444441'],
        ['#fce7f3', '#9d174d'],
        ['#dcfce7', '#3a7d1e'],
        ['#e0e7ff', '#3730a3']
    ];

    function avatarColor(name) {
        var h = 0;
        for (var i = 0; i < (name || '').length; i++) h = ((h << 5) - h) + name.charCodeAt(i);
        return AV_COLORS[Math.abs(h) % AV_COLORS.length];
    }

    function rateColor(r) {
        return r >= 80 ? 'b-green' : r >= 60 ? 'b-blue' : r >= 40 ? 'b-amber' : 'b-red';
    }

    function sparkHTML(data) {
        if (!data || !Array.isArray(data) || !data.length) {
            return '<span style="color:var(--text3);font-size:11px">—</span>';
        }
        var mx = Math.max.apply(null, data) || 1;
        return '<div class="spark-wrap">' + data.map(function(v, i) {
            var h = Math.max(4, Math.round(v / mx * 36));
            var c = v >= 75 ? TEAL : v >= 50 ? AMBER : CORAL;
            var opacity = (i === data.length - 1) ? '' :'4d';
            return '<div class="spark-bar" style="height:' + h + 'px;background:' + c + opacity + ';"></div>';
        }).join('') + '</div>';
    }

    function hexToRgba(hex, alpha) {
        hex = (hex || '#737985').replace('#', '');
        if (hex.length === 3) hex = hex[0] + hex[0] + hex[1] + hex[1] + hex[2] + hex[2];
        var r = parseInt(hex.substring(0, 2), 16);
        var g = parseInt(hex.substring(2, 4), 16);
        var b = parseInt(hex.substring(4, 6), 16);
        return 'rgba(' + r + ',' + g + ',' + b + ',' + alpha + ')';
    }

    function n(v) {
        return parseInt(v) || 0;
    }

    function pct(a, b) {
        return b > 0 ? Math.round(a / b * 100) : 0;
    }

    /* ═══════════════════════════════════════════════
       TAB / MODAL
       ═══════════════════════════════════════════════ */
    window.switchTab = function(id = null, btn = null) {

        // 🔹 fallback if id is missing
        if (!id && btn) {
            id = btn.dataset.tab;
        }

        if (!id) {
            $(".tab-btn").toggleClass('active');

            setTimeout(function() {
                $(".tab-btn.active").trigger('click');
                  

            }, 100); // runs after 1 second



            // console.warn('No tab id provided');
            return;
        }

        // 🔹 remove active from all tabs
        document.querySelectorAll('.tab-pane')
            .forEach(p => p.classList.remove('active'));

        // 🔹 activate tab
        const tab = document.getElementById('tab-' + id);
        if (tab) {
            tab.classList.add('active');
        } else {
            // console.warn('Tab not found:', 'tab-' + id);
        }

        // 🔹 find button if not passed
        if (!btn) {
            btn = document.querySelector(`.tab-btn[data-tab="${id}"]`);
        }

        // 🔹 activate button
        if (btn) {
            document.querySelectorAll('.tab-btn')
                .forEach(b => b.classList.remove('active'));

            btn.classList.add('active');
        } else {
            // console.warn('Button not found for tab:', id);
        }

       

        let label = $(".tab-bar button.tab-btn.active").data('active');

        $("#active-section").text(label);
        
         $(".db").animate({ scrollTop: 0 }, 300);

    };
    window.openModal = function() {
        document.getElementById('modalOverlay').classList.add('open');
    };
    window.closeModal = function() {
        document.getElementById('modalOverlay').classList.remove('open');
    };
    document.getElementById('modalOverlay').addEventListener('click', function(e) {
        if (e.target === this) closeModal();
    });

    /* ═══════════════════════════════════════════════
       FILTER DATA
       ═══════════════════════════════════════════════ */
    function get_filterData(limit=0) {
        
      
        
        return {
            csrf_token_name: typeof csrfData !== 'undefined' ? csrfData.hash : '',
            follow_up_date: $('#updateDate').val() || '',
            from: $('#updateDate').data('from') || '',
            to: $('#updateDate').data('to') || '',
            lead_type: $('#lead_type').val() || [],
            view_source: $('#view_source').val() || [],
            view_status: $('#view_status').val() || [],
            staff_department: $('#staff_department').val() || [],
            office_location: $('#office_location').val() || [],
            staff: $('#staff').val() || [],
            limit:limit
        };
    }

    /* ═══════════════════════════════════════════════
       AJAX LOAD
       ═══════════════════════════════════════════════ */
    window.loadFollowupDashboard = function() {
        
         
        // Show skeletons
        $('#kpiRow1').html('<div class="skeleton"></div><div class="skeleton"></div><div class="skeleton"></div>');
        $('#kpiRow3').html('<div class="skeleton"></div><div class="skeleton"></div><div class="skeleton"></div>');
        $('#kpiRow2').html('<div class="skeleton"></div><div class="skeleton"></div><div class="skeleton"></div>');



        $.ajax({
            url: admin_url + 'Dashboard/getFollowupDashboard',
            type: 'POST',
            data: get_filterData(),
            dataType: 'json',
            success: function(res) {


                if (!res) return;
                console.log('Dashboard data:', res);

                renderKPIs(res.data_stus);
                renderFunnelKPIs(res.funnel_data);
                renderTeamHealth(res);
                renderAtRisk(res.at_risk_counsellors);
                renderCounsellorTable(res.counsollor);
                renderMissedBars(res.missed_range, res.missed_range_least);
                renderOverdueAging(res.overdue_aging);
                renderStatusChart(res.status_chart);
                renderCompletionChart(res.status_chart,1,"completionChart","completionLegend");
                 renderCompletionChart(res.status_chart_overdue,2,"completionChartOverdue","completionLegendOverdue");
                // renderTrendChart(res.team_trend);
                renderOverdueBand(res);
                console.log("check",res.past_data);
                // followUpTable(res.past_data,"overdueBody");
                
            },
            error: function(xhr) {
                console.error('Dashboard error:', xhr.responseText);
            }
        });
    };

var getDataScroll = true;

$(".tbl-wrap-scroll").on("scroll", function () {

    let element = $(this);

    let scrollTop = element.scrollTop();
    let innerHeight = element.innerHeight();
    let scrollHeight = element[0].scrollHeight;

    // bottom reached within 10px
    if (scrollTop + innerHeight >= scrollHeight - 10 && getDataScroll) {

        console.log("scroll Start");

        getDataScroll = false;

        if ($("#queueTable").is(":visible")) {

            loadTable(0,$("#queueBody tr").length);

        }
        
        if ($("#overdueBody").is(":visible")) {

            loadTable(1,$("#overdueBody tr").length);

        }
        
          if ($("#futureBody").is(":visible")) {

            loadTable(2,$("#futureBody tr").length);

        }
        
          if ($("#completedBody").is(":visible")) {

            loadTable(3,$("#completedBody tr").length);

        }
        
        

    }

});
    function loadTable(checkstaus=0,limit=0) {
        // ──────────────────────────────────────
        // AJAX 2: Counsellor table (parallel)
        // ──────────────────────────────────────
        $.ajax({
            url: admin_url + 'Dashboard/getFollow_up_datatable?tableStatus='+checkstaus,
            type: 'POST',
            data: get_filterData(limit),
            dataType: 'json',
            success: function(res) {
                // $("#show-follow-status").html('');
                // $("#queueCount").text(0 + " Pending");
                
                    if(checkstaus==0 && limit ==0){
                    $('#queueBody,#futureBody,#overdueBody').html(
                    '<tr><td colspan="12" style="text-align:center;padding:20px;color:var(--red)">' +
                    'No data Found</td></tr>'
                    );
                    setGostedData([]);
                    }
                    
                if (!res) return;
                if (res.length == 0 && limit ==0) {
                
                    
                    
                    if(checkstaus==0)
                {
                     loadTable(1); 
                }
                if(checkstaus==1)
                {
                    loadTable(2); 
                }
                if(checkstaus==2)
                {
                    loadTable(3); 
                }
                    return false;
                }
              getDataScroll = true;
                
                if(checkstaus==0)
                {
                    followUpTable(res,"queueBody",limit)
                    if(limit == 0){
                  loadTable(1); 
                    }
                }
                if(checkstaus==1)
                {
                    followUpTable(res,"overdueBody",limit)
                     if(limit == 0){
                    loadTable(2); 
                     }
                }
                if(checkstaus==2)
                {
                    followUpTable(res,"futureBody",limit)
                    loadTable(3); 
                }
                
                if(checkstaus==3)
                {
                    followUpTable(res,"completedBody",limit)
                }

            },
            error: function(xhr) {
                // $("#show-follow-status").html('');
                console.error('Counsellor table error:', xhr.responseText);
                $('#queueBody').html(
                    '<tr><td colspan="12" style="text-align:center;padding:20px;color:var(--red)">' +
                    'Failed to load counsellor data</td></tr>'
                );
            }
        });

    }


    function formatRelativeTime(inputDate) {
        if (!inputDate) return '';

        const now = new Date();
        const date = new Date(inputDate);

        if (isNaN(date.getTime())) return '';

        let diffSeconds = Math.floor((now - date) / 1000);
        const isFuture = diffSeconds < 0;

        diffSeconds = Math.abs(diffSeconds);

        const days = Math.floor(diffSeconds / 86400);
        const hours = Math.floor((diffSeconds % 86400) / 3600);
        const minutes = Math.floor((diffSeconds % 3600) / 60);

        let result = '';

        if (days > 0) {
            result = days === 1 ? '1 day' : `${days} days`;
        } else if (hours > 0) {
            if (minutes > 0) {
                result = `${hours} hr ${minutes} min`;
            } else {
                result = hours === 1 ? '1 hr' : `${hours} hrs`;
            }
        } else if (minutes > 0) {
            result = minutes === 1 ? '1 min' : `${minutes} mins`;
        } else {
            result = 'Just now';
        }

        return isFuture ? `${result} left` : `${result} ago`;
    }


    function getFollowUpStatus(followDate) {
        if (!followDate) return '';

        const now = new Date();
        const date = new Date(followDate);

        if (isNaN(date.getTime())) return '';

        let diffSeconds = Math.floor((date - now) / 1000); // future = +, past = -

        const isFuture = diffSeconds > 0;
        diffSeconds = Math.abs(diffSeconds);

        const days = Math.floor(diffSeconds / 86400);
        const hours = Math.floor((diffSeconds % 86400) / 3600);
        const minutes = Math.floor((diffSeconds % 3600) / 60);

        let result = '';

        if (days > 0) {
            result = days === 1 ? '1 day' : `${days} days`;
        } else if (hours > 0) {
            result = hours === 1 ? '1 hr' : `${hours} hrs`;
        } else if (minutes > 0) {
            result = minutes === 1 ? '1 min' : `${minutes} mins`;
        } else {
            result = 'Just now';
        }

        return isFuture ? `${result}` : `${result}`;
    }

    // const ghostLeads=[
    //   {n:'Sahil Bansal',  status:'cold',country:'UK',    counsellor:'Vikram Das',  att:5,last:'5 days ago'},
    //   {n:'Trisha Nair',   status:'warm',country:'Canada',counsellor:'Sneha Kapoor',att:4,last:'4 days ago'},
    //   {n:'Mohit Arora',   status:'cold',country:'USA',   counsellor:'Rohit Gupta', att:4,last:'3 days ago'},
    //   {n:'Farida Khan',   status:'cb',  country:'Australia',counsellor:'Priya Nair',att:3,last:'3 days ago'},
    //   {n:'Yash Patel',    status:'cold',country:'Germany',counsellor:'Vikram Das', att:3,last:'4 days ago'},
    //   {n:'Aditi Sharma',  status:'warm',country:'Ireland',counsellor:'Sneha Kapoor',att:3,last:'3 days ago'},
    // ];
    
    var ghostLeadsExport=[];

    function setGostedData(ghostLeads) {
        
        ghostLeadsExport =[];
        $("#ghostedDownload").attr("disabled",true);
        
        if(ghostLeads.length > 0)
        {
             $("#ghostedDownload").attr("disabled",false);
        }

        const ghEl = document.getElementById('ghostArea');
        ghEl.innerHTML = '';
        ghostLeads.forEach(g => {
            
            ghostLeadsExport.push({
        name: g.name,
        phonenumber: g.phonenumber,
        staff: g.staff_name,
        status: g.status_name,
        attempts: g.attempts,
        last_connected: g.last_connect_dt || "Not Connected"
    });
    
            ghEl.innerHTML += `<div style="display:flex;align-items:center;justify-content:space-between;padding:7px 0;border-bottom:0.5px solid var(--border)">
    <div style="display:flex;align-items:center;gap:8px">
     
      <div>
        <div style="font-size:12px;font-weight:600;color:var(--text)">${g.name}</div>
        <div style="font-size:11px;color:var(--text3)">${g.staff_name} &bull; ${g.phonenumber}</div>
      </div>
    </div>
    <div style="display:flex;align-items:center;gap:8px">
      <span class="ls-pill" style="background-color:${g.status_color}3d;color:${g.status_color}">${g.status_name}</span>
      <span class="badge b-red">${g.attempts} attempts</span>
      <span style="font-size:11px;color:var(--text3)">${formatRelativeTime(g.last_connect_dt) || "Not Connected"}</span>
    </div>
  </div>`;
        });
        // ghEl.lastElementChild.style.borderBottom='none';
        renderGosted(ghostLeads);
    }

    function formatDateTime(inputDate) {
        if (!inputDate) return '';

        const date = new Date(inputDate);

        if (isNaN(date.getTime())) return '';

        const day = String(date.getDate()).padStart(2, '0');
        const month = date.toLocaleString('en-GB', {
            month: 'short'
        });
        const year = date.getFullYear();

        let hours = date.getHours();
        const minutes = String(date.getMinutes()).padStart(2, '0');
        const ampm = hours >= 12 ? 'PM' : 'AM';

        hours = hours % 12 || 12;

        return `${day} ${month} ${year} <br> ${hours}:${minutes} ${ampm}`;
    }

    function followUpTable(queLeads,id="queueBody",limit=0) {

console.log(id);
        const qBody = document.getElementById(id);

        let total = 0;
        let ghostLeads = [];

        let statusArray = {}; // { priority: {color, name, count} }
        if(limit==0){
        qBody.innerHTML = '';
        }
        queLeads.forEach(l => {


            const safeText = (str) => (str || '').replace(/"/g, '&quot;');

            const getInitials = (name) => {
                return (name || '')
                    .split(' ')
                    .filter(Boolean)
                    .map(w => w[0])
                    .join('')
                    .toUpperCase();
            };

            const prioColorMap = {
                Critical: RED,
                High: CORAL,
                Medium: AMBER,
                Low: AMBER
            };

            const prioColor = prioColorMap[l.priority] || AMBER;

            qBody.insertAdjacentHTML('beforeend', `
<tr class='row-hide-show change-${l.priority}'>
  <td>
    <div style="display:flex;align-items:center;gap:5px">
      <div class="prio-dot prio-${l.priority}"></div>
      <span style="font-size:12px;font-weight:700;color:var(--text2);background-color:${l.priority_color}">
        ${l.priority}
      </span>
    </div>
  </td>

  <td>
    <div class="av-cell">
      <span style="font-size:12px;font-weight:600;color:var(--text)">
        ${l.name || ''}
        <br>
        <a href="#" onclick="init_lead(${l.lead_id},'','','','lead_reminders'); return false;">view</a>
      </span>
    </div>
  </td>

  <td style="font-size:12px;color:var(--text2);white-space:nowrap">
    ${l.phonenumber || ''}
  </td>

  <td>
    <span class="ls-pill" 
      style="background-color:${l.status_color}3d; color:${l.status_color};">
      ${l.status_name || ''}
    </span>
  </td>

  <td>
    <span class="ls-pill" 
      style="font-size:10px;padding:2px 8px;
      background-color:${l.source_color}3d; color:${l.source_color};">
      ${l.source_name || ''}
    </span>
  </td>

  <td style="font-size:12px;color:var(--text3);white-space:nowrap">
    ${formatDateTime(l.dateadded)}
  </td>

  <td style="font-size:12px;color:var(--text3);white-space:nowrap">
    ${formatDateTime(l.follow_date)}
  </td>

  <td style="font-size:12px;color:var(--text2)">
    ${l.staff_name || ''}
  </td>

  <td style="font-size:12px;color:var(--text3);white-space:nowrap">
    ${formatDateTime(l.last_connect_dt)}
  </td>

  <td>
    <span style="font-size:12px;font-weight:700;color:${prioColor}">
      ${getFollowUpStatus(l.follow_date)}
    </span>
  </td>

  <td style="text-align:center">
    <span class="badge ${
      l.attempts >= 3 ? 'b-red' :
      l.attempts >= 2 ? 'b-amber' : 'b-gray'
    }">
      ${l.attempts || 0}x
    </span>
  </td>

  <td 
    style="font-size:12px;color:var(--text3);
    max-width:150px;overflow:hidden;
    text-overflow:ellipsis;white-space:nowrap"
    title="${safeText(l.latest_note)}">
    ${l.latest_note || ''}
  </td>
</tr>
`);

if(id == "queueBody")
{
            total++;

            // ✅ FIXED statusArray
            if (!statusArray[l.priority]) {
                statusArray[l.priority] = {
                    color: l.priority,
                    name: l.priority,
                    count: 0
                };
            }
            statusArray[l.priority].count++;
            if (l.attempts > 3) {
                ghostLeads.push(l);
            }
}
        });

        if(id != "queueBody")
        {
        return  false;
        }
        
  
        setGostedData(ghostLeads);


        // ✅ total count
        // $("#queueCount").text(total + " Pending");

        // ✅ render status badges
        // let statusHtml = '';
        // if  (Object.keys(statusArray).length > 0) {
        //     statusHtml += `
        //     <span onclick="changeTableStatus(${total})" class="badge " >
        //         All
        //     </span>`;
        // }
        // Object.values(statusArray).forEach(item => {
        //     // console.log(item);
        //     statusHtml += `
        //     <span onclick="changeTableStatus( ${item.count},'${item.name}')" class="badge prio-${item.color}" style="color:${item.color}">
        //         ${item.count} ${item.name}
        //     </span>
        // `;
        // });

        // $("#show-follow-status").html(statusHtml);
    }

   function changeTableStatus(count, name) {

    const $table =  $("table.tbl:visible");

    // hide all rows first
    $table.find("tr.row-hide-show").hide();

    if (!name || name === 'all') {
        $table.find("tr.row-hide-show").show();
    } else {
        $table.find("tr.change-" + name).show();
    }

    // check visible rows
    const visibleRows = $table.find("tr.row-hide-show:visible").length;

    if (visibleRows === 0) {
        console.log("No rows visible in table");
        // optional: show empty state row
        $table.find(".no-data-row").show();
    } else {
        $table.find(".no-data-row").hide();
    }
}
    
      function changeTableStatus_c(count, name="") {
          console.log(count);
          console.log(name);
        $("#counsellorTable tr.row-hide-show").hide();

        if (!name) {
            $("#counsellorTable tr.row-hide-show").show();
        } else {
            $("#counsellorTable tr.counsollor-tr-" + name).show();
        }
    }

    function renderCounsellorTable(counsellors) {
        _counsellorData = counsellors || [];

        if (!_counsellorData.length) {
            $('#counsellorHead').html('');
            $('#counsellorBody').html('<tr><td colspan="11" style="text-align:center;padding:20px;color:var(--text3)">No counsellor data</td></tr>');
            return;
        }

        // Parse funnel names from first counsellor
        _funnelNames = [];
        var firstFunnels = _counsellorData[0].funnel_counts;
        if (typeof firstFunnels === 'string') {
            try {
                firstFunnels = JSON.parse(firstFunnels);
            } catch (e) {
                firstFunnels = [];
            }
        }
        if (firstFunnels && firstFunnels.length) {
            firstFunnels.forEach(function(f) {
                _funnelNames.push({
                    name: f.funnel_name,
                    color: f.color || 'var(--text3)',
                    bg: f.bg || 'var(--surface2)'
                });
            });
        }

        // Build dynamic <thead>
        var thHtml = '<tr>' +
            '<th onclick="sortCounsellorTable(\'name\')">Counsellor</th>' +
            '<th onclick="sortCounsellorTable(\'total_followups\')" style="text-align:center">Assigned today</th>' +
            '<th onclick="sortCounsellorTable(\'completed\')" style="text-align:center">Completed</th>' +
            '<th onclick="sortCounsellorTable(\'due\')" style="text-align:center">Pending</th>'+
            '<th onclick="sortCounsellorTable(\'overdue\')" style="text-align:center">Overdue</th>';

        _funnelNames.forEach(function(fn) {
            thHtml += '<th style="text-align:center">' + fn.name + '</th>';
        });

        thHtml += '<th onclick="sortCounsellorTable(\'completion_percentage\')" style="text-align:center">Completed %</th>' +
            '<th>Status</th></tr>';

        $('#counsellorHead').html(thHtml);
        buildCounsellorRows();
    }

    /* ═══════════════════════════════════════════════
       RENDER: OVERDUE ALERT BAND
       ═══════════════════════════════════════════════ */
    function renderOverdueBand(d) {
        var st = d.data_stus || {};
        var missed = n(st.missed);
        var band = document.getElementById('overdueBand');

        if (missed > 0) {
            band.style.display = 'flex';
            document.getElementById('obTitle').textContent = missed + ' follow-ups are overdue right now';
            var parts = [];
            // (d.funnel_data_overdue || []).forEach(function(f) {
            //     if (n(f.due_count) > 0) parts.push(f.funnel_name + ': ' + f.due_count);
            // });
            
            let funnelMap = {};

(d.funnel_data_overdue || []).forEach(function (f) {

    if (n(f.due_count) <= 0) return;

    // merge same funnel names
    if (!funnelMap[f.funnel_name]) {
        funnelMap[f.funnel_name] = 0;
    }

    funnelMap[f.funnel_name] += n(f.due_count);
});

Object.keys(funnelMap).forEach(function (name) {

    parts.push(name + ': ' + funnelMap[name]);

});
            document.getElementById('obSub').innerHTML =
    parts.length
        ? parts.join(' &nbsp;&bull;&nbsp; ')
        : 'No overdue follow-ups';
        } else {
            band.style.display = 'none';
        }
        
        
     
        
let today_status = d?.data_stus?.today_status || {};  
let future_status = d?.data_stus?.future_status || {}; 
let missed_status = d?.data_stus?.missed_status || {}; 
let completed_status = d?.data_stus?.completed_status || {}; 


setStatus("show-follow-status",today_status);
setStatus("future-show-follow-status",future_status);
setStatus("overdue-show-follow-status",missed_status);
setStatus("completed-show-follow-status",completed_status);   
    }
    
    function setStatus(id,data)
    {
         let statusHtml = '';
        let today_status=data;

if (today_status) {

    statusHtml += `
        <span onclick="changeTableStatus(${today_status.all}, 'all')" class="badge">
            All
        </span>
    `;

    Object.entries(today_status).forEach(([key, value]) => {

        if (key === 'all') return;

        statusHtml += `
            <span  tooltip="fsds" onclick="changeTableStatus(${value??0}, '${key}')" class="badge prio-${key}">
                ${value??0} ${key}
            </span>
        `;
    });
}

$("#"+id).html(statusHtml);
    }

    /* ═══════════════════════════════════════════════
       RENDER: KPI ROW 1 — Due / Missed / Completed
       ═══════════════════════════════════════════════ */
    function renderKPIs(d) {
        if (!d) {
            $('#kpiRow1').html('');
            return;
        }
        var due = n(d.due),
            missed = n(d.missed),
            completed = n(d.completed);
        var total = due + completed;
        var compPct = pct(completed, total);

        var html = '';

        // Due today
        html += '<div class="kpi">' +
            '<div class="kpi-accent" style="background:var(--coral)"></div>' +
            '<div class="kpi-lbl">Total due</div>' +
            '<div class="kpi-val">' + due + '</div>' +
            '<div class="kpi-row"><span class="kpi-sub">Across all counsellors</span></div>' +
            '</div>';
            
            
             html += '<div class="kpi">' +
            '<div class="kpi-accent" style="background:var(--teal)"></div>' +
            '<div class="kpi-lbl">Completed</div>' +
            '<div class="kpi-val">' + completed + '</div>' +
            '<div class="kpi-row"><span class="kpi-sub">of ' + total + ' scheduled</span>' +
            '<span class="badge ' + rateColor(compPct) + '">' + compPct + '% done</span>' +
            '</div></div>';

        // Missed
        html += '<div class="kpi">' +
            '<div class="kpi-accent" style="background:var(--red)"></div>' +
            '<div class="kpi-lbl">Overdue Till Now</div>' +
            '<div class="kpi-val">' + missed + '</div>' +
            '<div class="kpi-row"><span class="kpi-sub">Not actioned yet</span>' +
            (missed > 10 ? '<span class="badge b-red">&#9650; Critical</span>' : missed > 0 ? '<span class="badge b-amber">Needs review</span>' : '<span class="badge b-green">All clear</span>') +
            '</div></div>';
            
            $("#overdueCount").text(missed + ' Missed'||0);
            $("#queueCount").text(due + " Pending")

        // Completed
       

        $('#kpiRow1').html(html);
    }

    /* ═══════════════════════════════════════════════
       RENDER: KPI ROW 2 — Funnel Buckets
       ═══════════════════════════════════════════════ */
    function renderFunnelKPIs(funnels) {
        if (!funnels || !funnels.length) {
            $('#kpiRow2').html('');
            return;
        }

        var grouped = {};
        funnels.forEach(function(f) {
            var key = f.funnel_name || f.funnel_id;
            if (!grouped[key]) {
                grouped[key] = {
                    total: 0,
                    due: 0,
                    statuses: [],
                    color: f.color,
                    bg: f.bg,
                    statusName: f.statusName
                };
            }
            grouped[key].total += n(f.lead_count);
            grouped[key].due += n(f.due_count);
            grouped[key].statuses.push(f);
        });

        var html = '';
        $.each(grouped, function(funnelName, data) {
            var statusDetail = data.statuses.map(function(s) {
                return s.status_name + ': ' + s.due_count;
            }).join(' &bull; ');

            html += '<div class="kpi">' +
                '<div class="kpi-accent" style="' + (data.bg || '') + '"></div>' +
                '<div class="kpi-lbl">' + funnelName + ' pending</div>' +
                '<div class="kpi-val">' + data.due + '</div>' +
                '<div class="kpi-row">' +
                '<span class="kpi-sub">' + statusDetail + '</span>' +
                '</div></div>';
        });

        var count = Object.keys(grouped).length;
        var row = document.getElementById('kpiRow2');
        row.className = 'row r' + Math.min(count, 5);
        row.innerHTML = html;
    }

    /* ═══════════════════════════════════════════════
       RENDER: KPI ROW 3 — Team Health
       ═══════════════════════════════════════════════ */
    function renderTeamHealth(d) {
        var st = d.data_stus || {};
        var due = n(st.due),
            completed = n(st.completed);
        var total = due + completed;
         var future = st.future || 0;
        var teamPct = pct(completed, total);

        var html = '';

        // Team completion rate
        html += '<div class="kpi">' +
            '<div class="kpi-accent" style="background:var(--blue)"></div>' +
            '<div class="kpi-lbl">Team completion rate</div>' +
            '<div class="kpi-val">' + teamPct + '%</div>' +
            '<div class="kpi-row"><span class="kpi-sub">Target: 85%</span>' +
            '<span class="badge ' + (teamPct >= 85 ? 'b-green' : teamPct >= 50 ? 'b-amber' : 'b-red') + '">' +
            (teamPct >= 85 ? '&#9650; On target' : '&#9660; Below target') + '</span>' +
            '</div></div>';

        // Ghosted placeholder
        html += `<div class="kpi cursor" onclick="scrollSection('ghostAreaSection')">`+
            '<div class="kpi-accent" style="background:var(--gray)"></div>' +
            '<div class="kpi-lbl">Ghosted / No response</div>' +
            '<div class="kpi-val" id="ghosted-count">—</div>' +
            '<div class="kpi-row"><span class="kpi-sub">3+ attempts, no reply</span><span class="badge b-gray">Review needed</span></div>' +
            '</div>';

        // At-risk counsellors (count only — tooltip is rendered by renderAtRisk)
        // html += '<div class="kpi">' +
        //     '<div class="kpi-accent" style="background:var(--red)"></div>' +
        //     '<div class="kpi-lbl">Counsellors at risk</div>' +
        //     '<div class="follow_up_counsellors_risk_wrap" onhover="bindHoverEffect()" id="atRiskWrap"></div>' +
        //     '<div class="kpi-row"><span class="kpi-sub">Below 50% completion</span><span class="badge b-red">Needs attention</span></div>' +
        //     '</div>';
        
        html += '<div class="kpi">' +
            '<div class="kpi-accent" style="background:var(--red)"></div>' +
            '<div class="kpi-lbl">Future follow-ups</div>' +
            '<div class="kpi-val" id="future-follow-ups">'+future+'</div>' +
            '<div class="kpi-row"></div>' +
            '</div>';
            
            $("#futureCount").text(future + ' future'||0);
            
             $("#completedCount").text(completed + ' completed'||0);

        $('#kpiRow3').html(html);
    }

    /* ═══════════════════════════════════════════════
       RENDER: AT-RISK COUNSELLORS (hover tooltip)
       ═══════════════════════════════════════════════ */
    function renderAtRisk(list) {
        var count = (list && list.length) ? list.length : 0;
        var el = document.getElementById('atRiskWrap');
        if (!el) return;

        var tooltipHtml = '';
        if (count > 0) {
            tooltipHtml = '<div class="risk-tooltip">';
            tooltipHtml += '<div class="risk-tooltip-title">Counsellors below 50%</div>';
            list.forEach(function(c) {
                var p = parseFloat(c.completion_percentage) || 0;
                var barColor = p < 20 ? '#c83232' : p < 35 ? '#d44c2e' : '#c07a0a';
                var ini = initials(c.counsellor_name);
                tooltipHtml += '<div class="risk-item">' +
                    '<div class="risk-item-top">' +
                    '<div class="risk-av">' + ini + '</div>' +
                    '<div style="flex:1;min-width:0"><div class="risk-name">' + c.counsellor_name + '</div>' +
                    '<div class="risk-meta">' + c.completed + ' of ' + c.total_followups + ' done</div></div>' +
                    '<div class="risk-pct" style="color:' + barColor + '">' + Math.round(p) + '%</div>' +
                    '</div>' +
                    '<div class="risk-bar-track"><div class="risk-bar-fill" style="width:' + p + '%;background:' + barColor + '"></div></div>' +
                    '</div>';
            });
            tooltipHtml += '</div>';
        } else {
            tooltipHtml = '<div class="risk-tooltip"><div class="risk-tooltip-title">All counsellors above 50%</div></div>';
        }

        el.innerHTML = '<div class="kpi-val" style="cursor:pointer">' + count + '</div>' +
            '<div class="risk-tooltip-wrap">' + tooltipHtml + '</div>';
    }


    function renderGosted(list) {
        var count = (list && list.length) ? list.length : 0;
        var el = document.getElementById('ghosted-count');
        if (!el) return;

        var tooltipHtml = '';
        // if (count > 0) {
        //     tooltipHtml = '<div class="risk-tooltip">';
        //     tooltipHtml += '<div class="risk-tooltip-title">Counsellors below 50% today</div>';
        //     list.forEach(function(c) {
        //         var p = parseFloat(c.completion_percentage) || 0;
        //         var barColor = p < 20 ? '#c83232' : p < 35 ? '#d44c2e' : '#c07a0a';
        //         var ini = initials(c.counsellor_name);
        //         tooltipHtml += '<div class="risk-item">'
        //             + '<div class="risk-item-top">'
        //             + '<div class="risk-av">' + ini + '</div>'
        //             + '<div style="flex:1;min-width:0"><div class="risk-name">' + c.counsellor_name + '</div>'
        //             + '<div class="risk-meta">' + c.completed + ' of ' + c.total_followups + ' done</div></div>'
        //             + '<div class="risk-pct" style="color:' + barColor + '">' + Math.round(p) + '%</div>'
        //             + '</div>'
        //             + '<div class="risk-bar-track"><div class="risk-bar-fill" style="width:' + p + '%;background:' + barColor + '"></div></div>'
        //             + '</div>';
        //     });
        //     tooltipHtml += '</div>';
        // } else {
        //     tooltipHtml = '<div class="risk-tooltip"><div class="risk-tooltip-title">All counsellors above 50%</div></div>';
        // }

        el.innerHTML = '<div class="kpi-val" style="cursor:pointer">' + count + '</div>' +
            '<div class="risk-tooltip-wrap">' + tooltipHtml + '</div>';
    }


    /* ═══════════════════════════════════════════════
       RENDER: COUNSELLOR TABLE
       ═══════════════════════════════════════════════ */
    window.sortCounsellorTable = function(key) {
        if (sortKey === key) sortDir *= -1;
        else {
            sortKey = key;
            sortDir = -1;
        }
        buildCounsellorRows();
    };

    function renderCounsellorTable(counsellors) {
        _counsellorData = counsellors || [];

        if (!_counsellorData.length) {
            $('#counsellorHead').html('');
            $('#counsellorBody').html('<tr><td colspan="12" style="text-align:center;padding:20px;color:var(--text3)">No counsellor data</td></tr>');
            return;
        }

        // Parse funnel names from first counsellor
        _funnelNames = [];
        var firstFunnels = _counsellorData[0].funnel_counts;
        if (typeof firstFunnels === 'string') {
            try {
                firstFunnels = JSON.parse(firstFunnels);
            } catch (e) {
                firstFunnels = [];
            }
        }
        if (firstFunnels && firstFunnels.length) {
            firstFunnels.forEach(function(f) {
                _funnelNames.push({
                    name: f.funnel_name,
                    color: f.color || 'var(--text3)',
                    bg: f.bg || 'var(--surface2)'
                });
            });
        }

        // Build dynamic <thead>
        var thHtml = '<tr>' +
            '<th onclick="sortCounsellorTable(\'name\')">Counsellor</th>' +
            '<th onclick="sortCounsellorTable(\'total_followups\')" style="text-align:center">Assigned</th>' +
            '<th onclick="sortCounsellorTable(\'completed\')" style="text-align:center">Completed</th>' +
            '<th onclick="sortCounsellorTable(\'due\')" style="text-align:center">Pending</th>'+
            '<th onclick="sortCounsellorTable(\'overdue\')" style="text-align:center">Overdue</th>';

        _funnelNames.forEach(function(fn) {
            thHtml += '<th style="text-align:center">' + fn.name + '</th>';
        });

        thHtml += '<th onclick="sortCounsellorTable(\'completion_percentage\')" style="text-align:center">Completed %</th>' +
            '<th>Status</th></tr>';

        $('#counsellorHead').html(thHtml);
        buildCounsellorRows();
    }

   function buildCounsellorRows() {
    var sorted = _counsellorData.slice().sort(function(a, b) {
        if (sortKey === 'name') {
            return sortDir * (a.counsellor_name || '').localeCompare(b.counsellor_name || '');
        }
        var va = parseFloat(a[sortKey]) || 0;
        var vb = parseFloat(b[sortKey]) || 0;
        return sortDir * (vb - va);
    });

    var html = '';

    // Pre-seed all three status buckets so we can keep their order + colour
    var statusCounsollor = {
        'On track': { count: 0, name: 'On track', cls: 'b-green', color: '#16A34A' },
        'At risk':  { count: 0, name: 'At risk',  cls: 'b-amber', color: '#D97706' },
        'Critical': { count: 0, name: 'Critical', cls: 'b-red',   color: '#DC2626' }
    };
    var total = 0;

    sorted.forEach(function(c) {
        var name     = c.counsellor_name || 'Unknown';
        var assigned = n(c.total_followups);
        var done     = n(c.completed);
        var overdue  = n(c.overdue);
        var due      = n(c.due);
        var rate     = parseFloat(c.completion_percentage) || 0;
        var av       = avatarColor(name);
        var ini      = initials(name);

        var status, statusCls;
        if (rate >= 85)      { status = 'On track'; statusCls = 'b-green'; }
        else if (rate >= 40) { status = 'At risk';  statusCls = 'b-amber'; }
        else                 { status = 'Critical'; statusCls = 'b-red';   }

        // funnel_counts may come back as a JSON string (MySQL JSON_ARRAYAGG)
        var funnels = c.funnel_counts;
        if (typeof funnels === 'string') {
            try { funnels = JSON.parse(funnels); }
            catch (e) { funnels = []; }
        }
        var fMap = {};
        if (Array.isArray(funnels)) {
            funnels.forEach(function(f) { fMap[f.funnel_name] = n(f.count); });
        }

        html += '<tr class="row-hide-show counsollor-tr-' + status.replace(/\s+/g, '-') + '" data-status="' + status + '">';
        html +=   '<td><div class="av-name">' + name + '</div></td>';
        html +=   '<td style="font-weight:700;text-align:center">' + assigned + '</td>';
        html +=   '<td style="color:' + TEAL + ';font-weight:700;text-align:center">' + done + '</td>';
        html +=   '<td style="text-align:center;font-weight:700;"><span class="text-danger">' + due + '</span></td>';
        html +=   '<td style="text-align:center"><span class="badge ' +
                    (overdue > 3 ? 'b-red' : overdue > 0 ? 'b-amber' : 'b-green') +
                  '">' + overdue + '</span></td>';

        _funnelNames.forEach(function(fn) {
            var count = fMap[fn.name] || 0;
            if (count > 0) {
                html += '<td style="text-align:center">' +
                          '<span class="ls-pill" style="background:' + fn.bg + ';color:' + fn.color + '">' +
                            count +
                          '</span>' +
                        '</td>';
            } else {
                html += '<td style="text-align:center;color:var(--text3)">0</td>';
            }
        });

        html += '<td style="text-align:center"><span class="badge ' + rateColor(rate) + '">' +
                  Math.round(rate) + '%' +
                '</span></td>';
        html += '<td><span class="badge ' + statusCls + '">' + status + '</span></td>';
        html += '</tr>';

        statusCounsollor[status].count++;
        total++;
    });

    // Build the status filter chips (top-of-table)
    var statusHtml = '<span onclick="changeTableStatus_c(' + total + ')" ' +
                       'class="badge b-grey" style="cursor:pointer">All ' + total + '</span>';

    Object.keys(statusCounsollor).forEach(function(key) {
        var item = statusCounsollor[key];
        if (item.count === 0) return;       // skip empty buckets
        statusHtml += '<span onclick="changeTableStatus_c(' + item.count + ', \'' + item.name.replace(/\s+/g, '-') + '\')" ' +
                        'class="badge ' + item.cls + '" style="cursor:pointer;color:' + item.color + '">' +
                          item.count + ' ' + item.name +
                      '</span>';
    });

    $('#counsellorBody').html(html);
    $('#counsellorStatusFilter').html(statusHtml);   // <-- put the chips wherever you want them
}

    /* ═══════════════════════════════════════════════
       RENDER: MISSED BARS — Top 5 & Least 5
       ═══════════════════════════════════════════════ */
    function renderMissedBars(top5, least5) {
        var el = document.getElementById('missedArea');

        if ((!top5 || !top5.length) && (!least5 || !least5.length)) {
            el.innerHTML = '<div style="color:var(--text3);font-size:12px;padding:10px">No missed follow-up data</div>';
            return;
        }

        // Global max for consistent bar widths
        var globalMax = 1;
        (top5 || []).concat(least5 || []).forEach(function(c) {
            var v = n(c.missed);
            if (v > globalMax) globalMax = v;
        });

        var html = '<div style="ror" class="row r2">';

        // Left: Top 5
        html += '<div class="col-md-12 card">' +
            '<div style="display:flex;align-items:center;gap:6px;margin-bottom:12px">' +
            '<span style="width:8px;height:8px;border-radius:50%;background:' + RED + '"></span>' +
            '<span style="font-size:11px;font-weight:700;color:var(--text3);text-transform:uppercase;letter-spacing:.4px">Top 5 — Most Missed</span>' +
            '</div>';
        if (top5 && top5.length) {
            html += buildMissedRows(top5, globalMax);
        } else {
            html += '<div style="color:var(--text3);font-size:12px">No data</div>';
        }
        html += '</div>';

        // Right: Least 5
        html += '<div class="col-md-12 card" style="">' +
            '<div style="display:flex;align-items:center;gap:6px;margin-bottom:12px">' +
            '<span style="width:8px;height:8px;border-radius:50%;background:' + GREEN + '"></span>' +
            '<span style="font-size:11px;font-weight:700;color:var(--text3);text-transform:uppercase;letter-spacing:.4px">Least 5 — created follow-ups</span>' +
            '</div>';
        if (least5 && least5.length) {
            html += buildMissedRows(least5, globalMax);
        } else {
            html += '<div style="color:var(--text3);font-size:12px">No data</div>';
        }
        html += '</div>';

        html += '</div>';
        el.innerHTML = html;
    }


function scrollSection(sectionID) {
    console.log("scroll");
    const element = document.getElementById(sectionID);
    if (element) {
        element.scrollIntoView({ 
            behavior: 'smooth',
            block: 'start'
        });
    }
}
    function buildMissedRows(list, globalMax) {
        var html = '';
        list.forEach(function(c) {
            var cnt = n(c.missed);
            var name = c.counsellor_name || 'Unknown';
            var av = avatarColor(name);
            var w = Math.max(3, Math.round(cnt / globalMax * 100));
            var barColor = cnt >= 6 ? RED : cnt >= 3 ? AMBER : TEAL;
            var badgeBg = cnt >= 6 ? '#ffe4e4' : cnt >= 3 ? '#fef3c7' : '#dcfce7';
            var badgeClr = cnt >= 6 ? RED : cnt >= 3 ? AMBER : GREEN;
            var badgeTxt = cnt >= 6 ? 'High' : cnt >= 3 ? 'Med' : 'Low';

            html += '<div class="bar-row" style="margin-bottom:10px">' +
               
                '<div class="bar-lbl" title="' + name + '">' + name.split(' ')[0]+' '+ name.split(' ')[1] + '</div>' +
                '<div class="bar-track"><div class="bar-fill" style="width:' + w + '%;background:' + barColor + '"></div></div>' +
                '<div style="display:flex;align-items:center;gap:6px">' +
                '<span style="font-size:12px;font-weight:700;color:var(--text);min-width:24px;text-align:right">' + cnt + '</span>' +
                '<span style="font-size:10px;font-weight:700;padding:2px 6px;border-radius:4px;background:' + badgeBg + ';color:' + badgeClr + '">' + badgeTxt + '</span>' +
                '</div></div>';
        });
        return html;
    }

    /* ═══════════════════════════════════════════════
       RENDER: OVERDUE AGING
       ═══════════════════════════════════════════════ */
    function renderOverdueAging(aging) {
        var el = document.getElementById('agingArea');
        if (!aging || !aging.length) {
            el.innerHTML = '<div style="color:var(--text3);font-size:12px;padding:10px">No overdue leads</div>';
            return;
        }

        var agingCfg = {
            'Due today': {
                color: AMBER,
                badge: 'b-amber',
                label: 'Due'
            },
            '1 day overdue': {
                color: CORAL,
                badge: 'b-red',
                label: 'Urgent'
            },
            '2 days overdue': {
                color: RED,
                badge: 'b-red',
                label: 'Critical'
            },
            '3 days overdue': {
                color: '#8b0000',
                badge: 'b-red',
                label: 'Escalate'
            },
            '4+ days overdue': {
                color: '#500000',
                badge: 'b-red',
                label: 'Lost?'
            }
        };

        var total = aging.reduce(function(s, a) {
            return s + n(a.lead_count);
        }, 0) || 1;
        var html = '';

        aging.forEach(function(a) {
            var cfg = agingCfg[a.age_label] || {
                color: GRAY,
                badge: 'b-gray',
                label: ''
            };
            var w = Math.round(n(a.lead_count) / total * 100);
            html += '<div class="age-row">' +
                '<div class="age-window">' + a.age_label + '</div>' +
                '<div class="age-track"><div class="age-fill" style="width:' + w + '%;background:' + cfg.color + '"></div></div>' +
                '<div class="age-val">' + a.lead_count + '</div>' +
                '<div class="age-badge badge ' + cfg.badge + '">' + cfg.label + '</div>' +
                '</div>';
        });

        el.innerHTML = html;
    }

    /* ═══════════════════════════════════════════════
       RENDER: STATUS CHART (Stacked Bar + Custom Tooltip)
       ═══════════════════════════════════════════════ */
    function hexToRgba(hex, alpha) {
        hex = (hex || '#737985').replace('#', '');
        if (hex.length === 3) hex = hex[0] + hex[0] + hex[1] + hex[1] + hex[2] + hex[2];
        var r = parseInt(hex.substring(0, 2), 16);
        var g = parseInt(hex.substring(2, 4), 16);
        var b = parseInt(hex.substring(4, 6), 16);
        return 'rgba(' + r + ',' + g + ',' + b + ',' + alpha + ')';
    }

    // function renderStatusChart(data) {

    //     if (!data || !data.length) {
    //         document.getElementById('statusLegend').innerHTML = '';
    //         return;
    //     }

    //     var labels = [];
    //     var colors = [];
    //     var completed = [];
    //     var pending = [];
    //     var totals = [];

    //     data.forEach(function(d) {
    //         var comp = parseInt(d.completed) || 0;
    //         var pend = parseInt(d.pending) || 0;

    //         labels.push(d.status_name || '—');
    //         colors.push(d.status_color || '#737985');
    //         completed.push(comp);
    //         pending.push(pend);
    //         totals.push(comp + pend);
    //     });

    //     // ✅ Legend (same style as your reference)
    //     const sLeg = document.getElementById('statusLegend');
    //     sLeg.innerHTML = '';
    //     labels.forEach((l, i) => {
    //         sLeg.innerHTML += `
    //     <span style="display:flex;align-items:center;gap:4px;font-size:11px;color:var(--text2)">
    //         <span style="width:9px;height:9px;border-radius:2px;background:${colors[i]}"></span>
    //         ${l} (${totals[i]})
    //     </span>`;
    //     });

    //     // Destroy old chart
    //     if (window.statusChartInstance) {
    //         statusChartInstance.destroy();
    //     }

    //     // ✅ Chart (MATCHED TO YOUR WORKING VERSION)
    //     statusChartInstance = new Chart(document.getElementById('statusChart'), {
    //         type: 'bar',
    //         data: {
    //             labels: labels,
    //             datasets: [{
    //                     label: 'Completed',
    //                     data: completed,
    //                     backgroundColor: colors.map(c => hexToRgba(c, 0.6)), // like 'aa'
    //                     borderRadius: 4,
    //                     stack: 's'
    //                 },
    //                 {
    //                     label: 'Pending',
    //                     data: pending,
    //                     backgroundColor: colors,
    //                     borderRadius: 4,
    //                     stack: 's'
    //                 }
    //             ]
    //         },
    //         options: {
    //             responsive: true,
    //             maintainAspectRatio: false,

    //             // ✅ EXACT SAME HOVER BEHAVIOR
    //             interaction: {
    //                 mode: 'index',
    //                 intersect: false
    //             },

    //             plugins: {
    //                 legend: {
    //                     display: false
    //                 },

    //                 // ✅ DEFAULT TOOLTIP (FIXED)
    //                 tooltip: {
    //                     mode: 'index',
    //                     intersect: false
    //                 }
    //             },

    //             scales: {
    //                 x: {
    //                     stacked: true,
    //                     grid: {
    //                         display: false
    //                     },
    //                     ticks: {
    //                         font: {
    //                             size: 11
    //                         },
    //                         color: '#9aa0b3'
    //                     }
    //                 },
    //                 y: {
    //                     stacked: true,
    //                     beginAtZero: true,
    //                     grid: {
    //                         color: 'rgba(128,128,128,0.1)'
    //                     },
    //                     ticks: {
    //                         font: {
    //                             size: 11
    //                         },
    //                         color: '#9aa0b3'
    //                     }
    //                 }
    //             }
    //         }
    //     });
    // }
    
   let statusFilterMode = 'all';
window.statusChartData = null;

function setStatusFilter(mode) {

    statusFilterMode = mode;

    if (window.statusChartData) {
        renderStatusChart(window.statusChartData);
    }
}
    
  function renderStatusChart(data) {

    if (!data || !data.length) {
        document.getElementById('statusLegend').innerHTML = '';
        return;
    }

    window.statusChartData = data;

    let labels = [];
    let colors = [];
    let completed = [];
    let pending = [];
    let totals = [];

    data.forEach(d => {

        let comp = parseInt(d.completed) || 0;
        let pend = parseInt(d.pending) || 0;

        labels.push(d.status_name || '—');
        colors.push(d.status_color || '#737985');

        completed.push(comp);
        pending.push(pend);
       totals.push(
    statusFilterMode == "completed"
        ? comp
        : statusFilterMode == "pending"
            ? pend
            : comp + pend
);
    });

    // legend
    const sLeg = document.getElementById('statusLegend');
    sLeg.innerHTML = '';

    labels.forEach((l, i) => {
        sLeg.innerHTML += `
            <span style="display:flex;align-items:center;gap:4px;font-size:11px;color:var(--text2)">
                <span style="width:9px;height:9px;border-radius:2px;background:${colors[i]}"></span>
                ${l} (${totals[i]})
            </span>`;
    });

    if (statusChartInstance) {
        statusChartInstance.destroy();
    }

    // datasets
    let datasets = [];

    if (statusFilterMode === 'all') {

        datasets = [
            {
                label: 'Completed',
                data: completed,
                backgroundColor: colors.map(c => hexToRgba(c, 0.6)),
                borderRadius: 4,
                stack: 's'
            },
            {
                label: 'Pending',
                data: pending,
                backgroundColor: colors,
                borderRadius: 4,
                stack: 's'
            }
        ];

    } else if (statusFilterMode === 'completed') {

        datasets = [{
            label: 'Completed',
            data: completed,
            backgroundColor: colors.map(c => hexToRgba(c, 0.6)),
            borderRadius: 4
        }];

    } else if (statusFilterMode === 'pending') {

        datasets = [{
            label: 'Pending',
            data: pending,
            backgroundColor: colors,
            borderRadius: 4
        }];
    }

    // chart
    statusChartInstance = new Chart(document.getElementById('statusChart'), {
        type: 'bar',
        data: {
            labels: labels,
            datasets: datasets
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,

            interaction: {
                mode: 'index',
                intersect: false
            },

            plugins: {

                legend: {
                    display: false
                },

                tooltip: {
                    mode: 'index',
                    intersect: false
                },

                // 🔥 VALUE ON BAR FIXED
                datalabels: {
                    anchor: 'end',
                    align: 'end',
                    offset: 2,
                    color: '#111',
                    font: {
                        size: 10,
                        weight: 'bold'
                    },

                    formatter: function (value, context) {

                        let index = context.dataIndex;

                        // STACKED MODE → show TOTAL only once
                        if (statusFilterMode === 'all') {

                            let total = (completed[index] || 0) + (pending[index] || 0);

                            if (context.datasetIndex === 0) {
                                return total > 0 ? total : '';
                            }

                            return '';
                        }

                        // SINGLE MODE
                        return value > 0 ? value : '';
                    }
                }
            },

            scales: {
                x: {
                    stacked: statusFilterMode === 'all',
                    grid: { display: false },
                    ticks: {
                        font: { size: 11 },
                        color: '#9aa0b3'
                    }
                },
                y: {
                    stacked: statusFilterMode === 'all',
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(128,128,128,0.1)'
                    },
                    ticks: {
                        font: { size: 11 },
                        color: '#9aa0b3'
                    }
                }
            }
        }
    });
}


    /* ═══════════════════════════════════════════════
       RENDER: COMPLETION DOUGHNUT
       ═══════════════════════════════════════════════ */
    function renderCompletionChart(data,Chartid,id="",id2="") {
        if (!data || !data.length) return;

        var labels = [],
            colorsArr = [],
            compData = [];
        data.forEach(function(d) {
            labels.push(d.status_name || '—');
            colorsArr.push(d.status_color || '#737985');
            compData.push(n(d.completed));
        });

        var legHtml = '';
        data.forEach(function(d, i) {
            var total = n(d.completed) + n(d.pending);
            legHtml += '<div class="col-md-3 p-5"><div class="card"  style="display:flex;align-items:center;gap:7px;margin-bottom:10px;">' +
                '<span style="width:10px;height:10px;border-radius:2px;background:' + colorsArr[i] + ';flex-shrink:0"></span>' +
                '<div><div style="font-size:12px;font-weight:600">' + labels[i] + '</div>' +
                '<div style="font-size:11px;color:var(--text3)">' + compData[i] + '/' + total + ' done</div></div></div></div>';
        });
        document.getElementById(id2).innerHTML = legHtml;

        if (completionChartInstance[Chartid]) {
            completionChartInstance[Chartid].destroy();
            completionChartInstance[Chartid] = null;
        }

        completionChartInstance[Chartid] = new Chart(document.getElementById(id), {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: compData,
                    backgroundColor: colorsArr,
                    borderWidth: 2,
                    borderColor: '#fff',
                    hoverOffset: 5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '62%',
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(c) {
                                return c.label + ': ' + c.raw + ' done';
                            }
                        }
                    }
                }
            }
        });
    }

    /* ═══════════════════════════════════════════════
       RENDER: 7-DAY TREND LINE CHART
       ═══════════════════════════════════════════════ */
    function renderTrendChart(trend) {
        if (!trend || !trend.length) return;

        var labels = [],
            completedArr = [],
            overdueArr = [];
        trend.forEach(function(t) {
            labels.push(t.date_label || t.trend_date || '');
            completedArr.push(n(t.completed));
            overdueArr.push(n(t.overdue || t.missed));
        });

        if (trendChartInstance) {
            trendChartInstance.destroy();
            trendChartInstance = null;
        }

        trendChartInstance = new Chart(document.getElementById('trendChart'), {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                        label: 'Completed',
                        data: completedArr,
                        borderColor: TEAL,
                        backgroundColor: 'rgba(15,158,117,.08)',
                        fill: true,
                        tension: .4,
                        pointRadius: 3,
                        borderWidth: 2,
                        pointBackgroundColor: TEAL
                    },
                    {
                        label: 'Overdue',
                        data: overdueArr,
                        borderColor: CORAL,
                        backgroundColor: 'rgba(212,76,46,.07)',
                        fill: true,
                        tension: .4,
                        pointRadius: 3,
                        borderWidth: 2,
                        pointBackgroundColor: CORAL
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            font: {
                                size: 11
                            }
                        }
                    },
                    y: {
                        grid: {
                            color: 'rgba(128,128,128,.1)'
                        },
                        ticks: {
                            font: {
                                size: 11
                            }
                        }
                    }
                }
            }
        });
    }



    /* ═══════════════════════════════════════════════
       INIT
       ═══════════════════════════════════════════════ */
    $(document).ready(function() {
        applyFilter();
        


    });
    
    function exportExcel(type)
    {
        if(type == "ghosted")
        {
        downloadExcel(ghostLeadsExport,type)
        }
    }
    
      function downloadExcel(data,sheetName) {
        const worksheet = XLSX.utils.json_to_sheet(data);

        const workbook = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(workbook, worksheet, sheetName);

        XLSX.writeFile(workbook, "leads.xlsx");
    }
    
    
   
</script>