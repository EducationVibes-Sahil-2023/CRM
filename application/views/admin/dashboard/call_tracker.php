<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); 
$role = $this->db->where('staffid', get_staff_user_id())->get(db_prefix() . 'staff')->row()->role;
?>
<link
   rel="stylesheet"
   href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-daterangepicker/3.1/daterangepicker.css" />
<?php 
$this->load->helper('leads');
?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.js"></script>
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

  :root {
    --bg: #f4f6fb;
    --surface: #ffffff;
    --surface2: #f0f3f9;
    --border: rgba(0,0,0,0.08);
    --text: #1a1d23;
    --text2: #5a6275;
    --text3: #9aa0b0;
    --radius: 12px;
    --radius-sm: 8px;
    --blue: #378ADD;
    --blue-lt: #B5D4F4;
    --blue-dk: #0C447C;
    --teal: #1D9E75;
    --teal-lt: #E1F5EE;
    --amber: #BA7517;
    --amber-lt: #FAEEDA;
    --coral: #D85A30;
    --coral-lt: #FAECE7;
    --purple: #7F77DD;
    --purple-lt: #EEEDFE;
    --green: #639922;
    --green-lt: #EAF3DE;
    --gray: #888780;
    --gray-lt: #F1EFE8;
  }

  @media (prefers-color-scheme: dark) {
    :root {
      --bg: #0f1117;
      --surface: #1a1d26;
      --surface2: #22263a;
      --border: rgba(255,255,255,0.08);
      --text: #e8eaf0;
      --text2: #8b93a8;
      --text3: #555e72;
    }
  }

  body {
    background: var(--bg);
    color: var(--text);
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Arial, sans-serif;
    font-size: 14px;
    line-height: 1.5;
    min-height: 100vh;
  }

  /* ── TOP NAV ── */
  .topnav {
    background: var(--surface);
    border-bottom: 0.5px solid var(--border);
    padding: 0 24px;
    height: 100px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    position: sticky;
    top: 0;
    z-index: 100;
  }
  .nav-brand {
    display: flex;
    align-items: center;
    gap: 10px;
  }
  .nav-icon {
    width: 32px; height: 32px;
    background: var(--blue);
    border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
  }
  .nav-icon svg { width: 18px; height: 18px; }
  .nav-title { font-size: 15px; font-weight: 600; color: var(--text); }
  .nav-subtitle { font-size: 11px; color: var(--text3); margin-top: 1px; }
  .nav-controls { display: flex; gap: 10px; align-items: center; }
  .nav-controls select, .nav-controls input {
    font-size: 12px;
    padding: 6px 10px;
    border-radius: var(--radius-sm);
    border: 0.5px solid var(--border);
    background: var(--surface2);
    color: var(--text);
    cursor: pointer;
  }
  .btn-refresh {
    font-size: 12px;
    padding: 6px 14px;
    border-radius: var(--radius-sm);
    border: 0.5px solid var(--blue);
    background: transparent;
    color: var(--blue);
    cursor: pointer;
    font-weight: 500;
    transition: background .15s;
  }
  .btn-refresh:hover { background: var(--surface2); }

  /* ── LAYOUT ── */
  .db { padding: 20px 24px; display: flex; flex-direction: column; gap: 16px; margin: 0 auto; }
  .row { display: grid; gap: 14px; }
 .call-drashboard .row::before {
    content: none !important;
}
  .r4 { grid-template-columns: repeat(5, minmax(0,1fr)); }
  .r2 { grid-template-columns: repeat(2, minmax(0,1fr)); }
  .r3 { grid-template-columns: repeat(3, minmax(0,1fr)); }
  .r22 { grid-template-columns: repeat(2, minmax(0,1fr)); }
  @media (max-width: 1100px) { .r4 { grid-template-columns: repeat(2,minmax(0,1fr)); } .r3 { grid-template-columns: repeat(2,minmax(0,1fr)); } }
  @media (max-width: 680px) { .r4,.r3,.r2,.r22 { grid-template-columns: 1fr; } .topnav { flex-wrap: wrap; height: auto; padding: 10px 16px; gap: 8px; } .db { padding: 12px 16px; } }

  /* ── CARDS ── */
  .card {
    background: var(--surface);
    border: 0.5px solid var(--border);
    border-radius: var(--radius);
    padding: 16px;
  }
  .card-title {
    font-size: 11px;
    font-weight: 600;
    color: var(--text3);
    text-transform: uppercase;
    letter-spacing: .5px;
    margin-bottom: 14px;
  }

  /* ── KPI CARDS ── */
  .kpi {
    background: var(--surface);
    border: 0.5px solid var(--border);
    border-radius: var(--radius);
    padding: 16px 18px;
    position: relative;
    overflow: hidden;
  }
  .kpi::before {
    content: '';
    position: absolute;
    top: 0; left: 0;
    width: 3px; height: 100%;
    border-radius: 12px 0 0 12px;
  }
  .kpi.k1::before { background: var(--blue); }
  .kpi.k2::before { background: var(--teal); }
  .kpi.k3::before { background: var(--amber); }
  .kpi.k4::before { background: var(--purple); }
  .kpi-lbl { font-size: 11px; color: var(--text3); font-weight: 500; margin-bottom: 5px; text-transform: uppercase; letter-spacing: .4px; }
  .kpi-val { font-size: 2rem; font-weight: 700; color: var(--text); line-height: 1.1; }
  .kpi-row { display: flex; align-items: center; justify-content: space-between; margin-top: 6px; }
  .kpi-sub { font-size: 11px; color: var(--text3); }
  .badge { font-size: 11px; padding: 5px 5px; border-radius: 4px; font-weight: 600; display: inline-flex; align-items: center; gap: 3px; }
  .up { background: var(--green-lt); color: #27500A; }
  .dn { background: var(--coral-lt); color: #791F1F; }
  .neu { background: var(--gray-lt); color: #444441; }

  /* ── BAR ROWS ── */
  .bar-row { display: flex; align-items: center; gap: 8px; margin-bottom: 8px; }
  .bar-row:last-child { margin-bottom: 0; }
  .rank-num {
    width: 22px; height: 22px;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 10px; font-weight: 700;
    flex-shrink: 0;
  }
  .bar-lbl { font-size: 12px; color: var(--text2); width: 90px; flex-shrink: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
  .bar-track { flex: 1; height: 12px; background: var(--surface2); border-radius: 3px; overflow: hidden; }
  .bar-fill { height: 100%; border-radius: 3px; transition: width .4s ease; }
  .bar-val { font-size: 12px; color: var(--text); font-weight: 600; width: 44px; text-align: right; flex-shrink: 0; }

  /* ── LEGENDS ── */
  .legend { display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 10px; }
  .legend-item { display: flex; align-items: center; gap: 5px; font-size: 11px; color: var(--text2); }
  .legend-sq { width: 9px; height: 9px; border-radius: 2px; flex-shrink: 0; }

  /* ── FUNNEL ── */
  .funnel-row { display: flex; align-items: center; gap: 8px; padding: 5px 0; border-bottom: 0.5px solid var(--border); }
  .funnel-row:last-child { border-bottom: none; }
  .funnel-lbl { font-size: 11px; color: var(--text2); width: 112px; flex-shrink: 0; }
  .funnel-track { flex: 1; height: 14px; background: var(--surface2); border-radius: 3px; overflow: hidden; }
  .funnel-fill { height: 100%; border-radius: 3px; }
  .funnel-nums { font-size: 11px; color: var(--text2); width: 68px; text-align: right; flex-shrink: 0; }
  .funnel-pct { color: var(--text3); }

  /* ── TABLE ── */
  .rep-table { width: 100%; border-collapse: collapse; font-size: 12px; }
  .rep-table th { font-size: 12px; font-weight: 600; color: var(--text3); text-transform: uppercase; letter-spacing: .4px; text-align: left; padding: 6px 8px; border-bottom: 0.5px solid var(--border); }
  .rep-table td { padding: 8px 8px; border-bottom: 0.5px solid var(--border); color: var(--text); font-size:14px; }
  .rep-table tr:last-child td { border-bottom: none; }
  .rep-table tr:hover td { background: var(--surface2); }
  .rep-avatar { width: 26px; height: 26px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 10px; font-weight: 700; flex-shrink: 0; }
  .rep-cell { display: flex; align-items: center; gap: 8px; }
  .pill { font-size: 10px; padding: 2px 7px; border-radius: 10px; font-weight: 600; }

  /* ── PEAK ROWS ── */
  .peak-row { display: flex; align-items: center; gap: 10px; padding: 7px 0; border-bottom: 0.5px solid var(--border); }
  .peak-row:last-child { border-bottom: none; }
  .peak-dot { width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; }
  .peak-meta { flex: 1; }
  .peak-window { font-size: 13px; font-weight: 600; color: var(--text); }
  .peak-desc { font-size: 11px; color: var(--text3); margin-top: 1px; }
  .peak-val { font-size: 18px; font-weight: 700; color: var(--text); }

  /* ── SECTION LABEL ── */
  .section-lbl { font-size: 11px; font-weight: 600; color: var(--text3); text-transform: uppercase; letter-spacing: .6px; padding: 4px 0; }

  /* ── STATUS INDICATOR ── */
  .status-row { display: flex; align-items: center; justify-content: space-between; padding: 7px 0; border-bottom: 0.5px solid var(--border); }
  .status-row:last-child { border-bottom: none; }
  .status-left { display: flex; align-items: center; gap: 8px; }
  .status-dot { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }
  .status-name { font-size: 12px; color: var(--text); }
  .status-val { font-size: 13px; font-weight: 600; color: var(--text); }

  /* ── CHART WRAP ── */
  .ch-wrap { position: relative; width: 100%; }
  .ch-wrap
  {
      height: 400px !important;
  }
  
  thead tr th
  {
      background-color: transparent !important;
       color: var(--text3) !important;
  }
  .filter-div
  {
      width: 170px;
  }
  

</style>
    <title>Pulse | Sales Call Intelligence</title>
<div id="wrapper">
<div class="screen-options-area"></div>
<div class="content">
<div class="row">
<div class="col-md-12 mtop15">
<div class="panel_s">
<div class="panel-body  call-drashboard">

<!-- TOP NAV -->
 <div class="nav-brand">
    <div class="nav-icon">
      <svg viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
        <rect x="2" y="2" width="6" height="6" rx="1.5" fill="white" opacity=".9"/>
        <rect x="10" y="2" width="6" height="6" rx="1.5" fill="white" opacity=".6"/>
        <rect x="2" y="10" width="6" height="6" rx="1.5" fill="white" opacity=".6"/>
        <rect x="10" y="10" width="6" height="6" rx="1.5" fill="white" opacity=".9"/>
      </svg>
    </div>
    <div class="">
      <div class="nav-title">Sales Call Tracker</div>
      <div class="nav-subtitle">Real-time team performance dashboard</div>
    </div>
  </div>
  <hr>
<div class="topnav">
  <!--<div class="nav-brand">-->
  <!--  <div class="nav-icon">-->
  <!--    <svg viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">-->
  <!--      <rect x="2" y="2" width="6" height="6" rx="1.5" fill="white" opacity=".9"/>-->
  <!--      <rect x="10" y="2" width="6" height="6" rx="1.5" fill="white" opacity=".6"/>-->
  <!--      <rect x="2" y="10" width="6" height="6" rx="1.5" fill="white" opacity=".6"/>-->
  <!--      <rect x="10" y="10" width="6" height="6" rx="1.5" fill="white" opacity=".9"/>-->
  <!--    </svg>-->
  <!--  </div>-->
  <!--  <div class="">-->
  <!--    <div class="nav-title">Sales Call Tracker</div>-->
  <!--    <div class="nav-subtitle">Real-time team performance dashboard</div>-->
  <!--  </div>-->
  <!--</div>-->
  
  <div class="nav-controls">
    <div class="filter-div">
    <label>Update Date</label>
    <input type="text" class="dateRange form-control" id="updateDate" placeholder="Select Date Range">
    </div>
   <?php
                                    // echo '<div class="filter-div">';
                                    // echo ' <label>Lead Type</label>';
                                    // echo render_select('lead_type[]', $type, array('id', 'name'), '', '', array('data-width' => '100%', 'data-none-selected-text' => _l('Lead Type'), 'multiple' => true, 'data-actions-box' => true), array(), 'no-mbot', '', false, "lead_type");
                                    // echo '</div>';

                                    ?>
                                    
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
                                    
                                    <?php if(is_admin() || $role== 3){  ?>
                                           <?php
                                           if(is_admin()){
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
                                    
                                
                                    
                                    
  <div style="width:50px;">
    <p>&nbsp;</p>
 <button type="button" class="btn-refresh" onclick="applyFilter(this)">
    <span class="icon">⟳</span> </button>
</div>
  </div>
</div>

<div class="db">

  <!-- KPI ROW -->
  <div class="row r4">
          <div class="kpi k1">
      <div class="kpi-lbl">Total calls</div>
      <div class="kpi-val total-calls-c">0</div>
      <div class="kpi-row yesterday-grow hide">
        <span class="kpi-sub y-total-calls-c"></span>
        <span class="badge up total-calls-c-grow"></span>
      </div>
    </div>
    <div class="kpi k1">
      <div class="kpi-lbl">Total Unique calls</div>
      <div class="kpi-val total-calls">0</div>
      <div class="kpi-row yesterday-grow hide">
        <span class="kpi-sub y-total-calls"></span>
        <span class="badge up total-calls-grow"></span>
      </div>
    </div>
    <div class="kpi k2">
      <div class="kpi-lbl">Avg call duration</div>
      <div class="kpi-val total-calls-avg">0m</div>
      <div class="kpi-row yesterday-grow hide">
        <span class="kpi-sub y-total-calls-avg"></span>
        <span class="badge dn total-calls-avg-grow"></span>
      </div>
    </div>
    <div class="kpi k3">
      <div class="kpi-lbl">Connect rate</div>
      <div class="kpi-val total-calls-connect">0%</div>
      <div class="kpi-row yesterday-grow hide">
        <span class="kpi-sub y-total-calls-connect"></span>
        <span class="badge up total-calls-connect-grow"></span>
      </div>
    </div>
    <div class="kpi k4">
      <div class="kpi-lbl">Total talk time</div>
      <div class="kpi-val total-calls-duration">0m</div>
      <div class="kpi-row yesterday-grow hide">
        <span class="kpi-sub y-total-calls-duration"></span>
        <span class="badge up total-calls-duration-grow"></span>
      </div>
    </div>
  </div>


  <!-- HOURLY + STATUS DURATION -->
  <div class="row">
    <div class="card graph-data">
      <div class="card-title">Hourly call distribution — office hours (9 am – 9 pm)</div>
      <div class="ch-wrap" style="height:170px"><canvas id="hourlyChart"></canvas></div>
    </div>
    <div class="card graph-data">
      <div class="card-title">Call duration by lead status (minutes)</div>
      <div class="legend" id="statusLegend"></div>
      <div class="ch-wrap" style="height:140px"><canvas id="statusChart"></canvas></div>
    </div>
  </div>

  <!-- TOP 5 + LEAST 5 -->
  <div class="row r3">
    <div class="card">
      <div class="card-title">Top 5 — calls made</div>
      <div id="top5Calls"></div>
    </div>
    <div class="card">
      <div class="card-title">Top 5 — total talk time</div>
      <div id="top5Talk"></div>
    </div>
    <div class="card">
      <div class="card-title">Top 5 — connect rate</div>
      <div id="bot5Connect"></div>
    </div>
  </div>
  
  
    <div class="row r3">
    <div class="card">
      <div class="card-title">Least 5 — calls made</div>
      <div id="least5Calls"></div>
    </div>
    <div class="card">
      <div class="card-title">Least 5 — total talk time</div>
      <div id="least5Talk"></div>
    </div>
    <div class="card">
      <div class="card-title">Least 5 — connect rate</div>
      <div id="least5Connect"></div>
    </div>
  </div>
  
  <!-- TREND + ATTEMPTS vs CONNECTS -->
  <div class="row">
    <div class="card">
      <div class="card-title">7-day avg call duration trend</div>
      <div class="ch-wrap" style="height:250px !important"><canvas id="trendChart"></canvas></div>
    </div>
    <div class="card">
      <div class="card-title">Call attempts vs. connects — per rep</div>
      <div class="ch-wrap" style="height:150px"><canvas id="attemptsChart"></canvas></div>
    </div>
  </div>




  <!-- FULL REP TABLE -->
<div class="card">
  <div class="card-title">Full rep performance table — today</div>
  <div style="overflow-x:auto">
    <table class="rep-table" id="repTable">
      
    </table>
  </div>
</div>

</div>

</div>
</div>
</duv>
</div>
</div>
</div>
</div>


<?php init_tail(); ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>
<!--<link-->
<!--   rel="stylesheet"-->
<!--   href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-daterangepicker/3.1/daterangepicker.css" />-->

<!-- ✅ 2. Moment -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>

<!-- ✅ 3. Moment Timezone (fix tz error) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment-timezone/0.5.43/moment-timezone-with-data.min.js"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-daterangepicker/3.1/daterangepicker.min.js"></script>
<script>
// 
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
      
      
initDatePicker("#updateDate");

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


const BLUE='#378ADD', BLUE_LT='#B5D4F4';
const TEAL='#1D9E75', TEAL_LT='#E1F5EE';
const AMBER='#BA7517', AMBER_LT='#FAEEDA';
const CORAL='#D85A30', CORAL_LT='#FAECE7';
const PURPLE='#7F77DD', PURPLE_LT='#EEEDFE';
const GREEN='#639922', GREEN_LT='#EAF3DE';
const GRAY='#888780', GRAY_LT='#F1EFE8';

const avatarColors=[
  ['#E6F1FB','#0C447C'],['#E1F5EE','#085041'],['#EEEDFE','#3C3489'],
  ['#FAEEDA','#633806'],['#FAECE7','#712B13'],['#EAF3DE','#27500A'],
  ['#FBEAF0','#72243E'],['#F1EFE8','#444441'],['#FCEBEB','#791F1F'],['#E6F1FB','#185FA5']
];

function initials(n){return n.split(' ').map(w=>w[0]).join('');}



function applyFilter(btn = null) {

    if (btn) {
       show_loader()
        btn.disabled = true;
    }

    loadDailyCalls(btn);
}

function get_filterData() {
   return {
    csrf_token_name: csrfData.hash,
    from: $('#updateDate').data('from') || '',
    to: $('#updateDate').data('to') || '',

    lead_type: $('#lead_type').val() || [],
    view_source: $('#view_source').val() || [],
    view_status: $('#view_status').val() || [],
    staff_department: $('#staff_department').val() || [],
    office_location: $('#office_location').val() || [],
    staff: $('#staff').val() || []
};
}


function resetAllCharts() {
    Object.keys(charts).forEach(key => {
        
        charts[key].destroy();
        showNoData(key);
    });
    charts = {};
}

function showNoData(containerId) {
    $(`#${containerId}`).html(`
        <div style="
            display:flex;
            align-items:center;
            justify-content:center;
            height:100%;
            color:#999;
            font-size:14px;
        ">
            📊 No data found
        </div>
    `);
}

let isUpdating = false;

// $(document).ready(function () {

//     function toggleFilters() {

//         if (isUpdating) return; // 🚫 stop loop
//         isUpdating = true;

//         let department = $('#staff_department').val();
//         let office = $('#office_location').val();
//         let staff = $('#staff').val();

//         // Reset all only if needed
//         $('#staff_department, #office_location, #staff').prop('disabled', false);

//         if (department && department.length > 0) {

//             if ($('#staff').val() !== null) $('#staff').val(null);
//             // if ($('#office_location').val() !== null) $('#office_location').val(null);

//             $('#staff').prop('disabled', true);

//         } else if (staff && staff.length > 0) {

//             if ($('#staff_department').val() !== null) $('#staff_department').val(null);
//             if ($('#office_location').val() !== null) $('#office_location').val(null);

//             $('#staff_department, #office_location').prop('disabled', true);

//         } else if (office && office.length > 0) {

//             // if ($('#staff_department').val() !== null) $('#staff_department').val(null);
//             if ($('#staff').val() !== null) $('#staff').val(null);

//             $('#staff').prop('disabled', true);
//         }

//         // 🔥 refresh once
//         $('.selectpicker').selectpicker('refresh');

//         isUpdating = false;
//     }

//     $('#staff_department, #office_location, #staff').on('change', toggleFilters);

// });
function loadDailyCalls(btn = null) {
show_loader();
    let formdata = get_filterData();
    
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

    $.ajax({
        url: admin_url + '/dashboard/get_daily_calls_tracker',
        type: 'POST',
        data: formdata,

        success: function (response) {
            
            resetAllCharts();

            let responseData = (typeof response === "string")
                ? JSON.parse(response)
                : response;

            if (typeof responseData.result === "string") {
                responseData = JSON.parse(responseData.result);
            }

            hide_loader();

            let CallData = responseData.total_range;
            let YesterDayData = responseData.yesterday_range;
            let HourData = responseData.hourly_data;
            let StatusData = responseData.status_data;
            let top_leads = responseData.top_leads;
            let top_duration = responseData.top_duration;
            let top_answered = responseData.top_answered;
            let week_avg_duration = responseData.week_avg_duration;
            let counsellor_stats = responseData.counsellor_stats;
            
            let least_leads = responseData.least_leads;
            let least_duration = responseData.least_duration;
            let least_answered = responseData.least_answered;

            makeBars('top5Calls', top_leads, BLUE, rankPalettes, 'leads');
            makeBars('top5Talk', top_duration, TEAL, rankPalettes, 'duration');
            makeBars('bot5Connect', top_answered, CORAL, rankPalettes, 'connected');
            
             makeBars('least5Calls', least_leads, BLUE, rankPalettes, 'leads');
            makeBars('least5Talk', least_duration, TEAL, rankPalettes, 'duration');
            makeBars('least5Connect', least_answered, CORAL, rankPalettes, 'connected');

            renderHourlyChart("hourlyChart", HourData);
            if(YesterDayData!==undefined){
                $(".yesterday-grow").removeClass('hide');
           
            }else
            {
                
                 $(".yesterday-grow").addClass('hide');
            }
             setCallInformations(CallData, YesterDayData);
            renderStatusChart("statusChart", StatusData);
            createAvgChart('trendChart', week_avg_duration);
            
            createAttemptsChart(
            'attemptsChart',
            counsellor_stats
            );

        },

        complete: function () {
            fetchDataTable();
            hide_loader();
            // 🔥 RESET BUTTON STATE
            if (btn) {
                // btn.classList.remove("loading");
                // btn.innerHTML = "⟳ Apply";
                btn.disabled = false;
            }
        }
    });
}

function fetchDataTable() {
    show_loader();

    let formdata = get_filterData();
    
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

    $.ajax({
        url: admin_url + '/dashboard/get_daily_calls_Datatable',
        type: 'POST',
        data: formdata,

        success: function (response) {
            // console.log(response);

            let tableData = (typeof response === "string")
                ? JSON.parse(response)
                : response;

            // console.log(tableData);

            // ✅ PASS CORRECT DATA
            appendRepTableData("repTable", tableData);
        },

        complete: function () {
            hide_loader();
        }
    });
}

function appendRepTableData (tableId, reps = []) {
    const tableEl = document.getElementById(tableId);

    // ✅ TABLE HEADER (match columns)
    tableEl.innerHTML = `
        <thead>
            <tr>
                <th>#</th>
                <th>Staff Name</th>
                <th>Total Calls</th>
                <th>Connected</th>
                <th>Talk Time</th>
                <th>Avg Duration</th>
                <th>Connect %</th>
            </tr>
        </thead>
    `;

    const tbody = document.createElement('tbody');

    // ✅ NO DATA
    if (!reps || reps.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="7" style="text-align:center;padding:20px;">
                    No data found
                </td>
            </tr>
        `;
        tableEl.appendChild(tbody);
        return;
    }

    // ✅ SORT BY TOTAL CALLS
    // const sortedReps = [...reps].sort((a, b) => b.total_calls - a.total_calls);

    let rowsHTML = "";

    reps.forEach((r, i) => {
        // console.log(r);
        const [bg, fg] = avatarColors[i % avatarColors.length];

        const totalCalls = Number(r.total_calls) || 0;
        const answered = Number(r.answered_calls) || 0;
        const talk = Number(r.call_duration) || 0;

        // ✅ CALCULATIONS
        const avgDur = totalCalls ? Math.round(talk / totalCalls) : 0;
        const avgStr = avgDur >= 60
            ? `${Math.floor(avgDur / 60)}m ${avgDur % 60}s`
            : `${avgDur}s`;

        const connectPct = totalCalls
            ? Math.round((answered / totalCalls) * 100)
            : 0;

        const conColor = connectPct >= 70
            ? `background:${GREEN_LT};color:#27500A`
            : connectPct >= 55
            ? `background:${AMBER_LT};color:#633806`
            : `background:${CORAL_LT};color:#791F1F`;

        rowsHTML += `
        <tr>
            <td style="color:var(--text3);font-weight:600">${i + 1}</td>

            <td>
                <div class="rep-cell">
                    <div class="rep-avatar" style="background:${bg};color:${fg}">
                        ${initials(r.staff_name)}
                    </div>
                    <span>${r.staff_name}</span>
                </div>
            </td>

            <td style="font-weight:600">${totalCalls}</td>
            <td>${answered}</td>
            <td>${formatTime(Math.floor(talk))}s</td>
            <td>${avgStr}</td>

            <td>
                <span class="pill" style="${conColor}">
                    ${connectPct}%
                </span>
            </td>
        </tr>`;
    });

    tbody.innerHTML = rowsHTML;
    tableEl.appendChild(tbody);
}
const valueLabelPlugin = {
    id: 'valueLabelPlugin',

    afterDatasetsDraw(chart) {

        const { ctx } = chart;

        chart.data.datasets.forEach((dataset, i) => {

            const meta = chart.getDatasetMeta(i);

            meta.data.forEach((point, index) => {

                const value = dataset.data[index];

                // 🔥 skip small values (clean UI)
                if (value < 60) return;

                const label = formatTime(value,true);

                ctx.save();

                ctx.font = "11px Inter, Arial";
                ctx.textAlign = "center";
                ctx.textBaseline = "middle";

                const paddingX = 8;
                const paddingY = 4;

                const textWidth = ctx.measureText(label).width;

                const x = point.x;
                const y = point.y - 14;

                // 🔥 background (modern pill style)
                ctx.fillStyle = "rgba(0,0,0,0.65)";
                roundRect(
                    ctx,
                    x - textWidth / 2 - paddingX,
                    y - 10,
                    textWidth + paddingX * 2,
                    18,
                    6
                );
                ctx.fill();

                // text
                ctx.fillStyle = "#fff";
                ctx.fillText(label, x, y);

                ctx.restore();
            });
        });
    }
};

function roundRect(ctx, x, y, w, h, r) {
    ctx.beginPath();
    ctx.moveTo(x + r, y);
    ctx.lineTo(x + w - r, y);
    ctx.quadraticCurveTo(x + w, y, x + w, y + r);
    ctx.lineTo(x + w, y + h - r);
    ctx.quadraticCurveTo(x + w, y + h, x + w - r, y + h);
    ctx.lineTo(x + r, y + h);
    ctx.quadraticCurveTo(x, y + h, x, y + h - r);
    ctx.lineTo(x, y + r);
    ctx.quadraticCurveTo(x, y, x + r, y);
    ctx.closePath();
}

function formatDateLabel(dateStr) {

    const date = new Date(dateStr);

    const options = { month: 'short', day: 'numeric' };

    return date.toLocaleDateString('en-US', options);
}
var charts={};
/**
 * createAvgChart — Duration line + Lead count bars
 * Shows both metrics on the same chart with dual Y-axes
 */
/**
 * createAvgChart — Duration line + Lead count bars
 * Both values shown as inline labels on chart
 *   - Lead count → inside/above each bar
 *   - Duration time → above each line point
 */
function createAvgChart(canvasId, data, color = BLUE) {
    const ctx = document.getElementById(canvasId);
    if (!ctx) return;

    const safeData = Array.isArray(data) ? data : [];
    const labels = safeData.map(i => formatDateLabel(i.call_date));

    // Destroy old chart
    if (charts[canvasId]) {
        charts[canvasId].destroy();
    }

    const durations  = safeData.map(i => i.lead_count ? i.total_duration : 0);
    const leadCounts = safeData.map(i => parseInt(i.lead_count) || 0);

    // ── Inline label plugin: draws count on bars + time on line points ──
    const inlineLabelPlugin = {
        id: 'inlineLabels',
        afterDatasetsDraw: function(chart) {
            const ctx2 = chart.ctx;
            ctx2.save();

            chart.data.datasets.forEach(function(dataset, dsIndex) {
                const meta = chart.getDatasetMeta(dsIndex);
                if (meta.hidden) return;

                meta.data.forEach(function(element, index) {
                    var val = dataset.data[index];
                    if (!val && val !== 0) return;

                    var x = element.x;
                    var y = element.y;

                    if (dataset.label === 'Leads') {
                        // ── Lead count: inside bar (centered) ──
                        ctx2.textAlign = 'center';
                        ctx2.textBaseline = 'bottom';
                        ctx2.font = 'bold 11px -apple-system, BlinkMacSystemFont, sans-serif';
                        ctx2.fillStyle = 'rgba(55,138,221,0.75)';
                        ctx2.fillText(val, x, y - 4);
                    }

                    if (dataset.label === 'Duration') {
                        // ── Duration time: above line point ──
                        var timeStr = formatTime(val);
                        ctx2.textAlign = 'center';
                        ctx2.textBaseline = 'bottom';
                        ctx2.font = '600 10px -apple-system, BlinkMacSystemFont, sans-serif';

                        // Background pill
                        var tw = ctx2.measureText(timeStr).width + 8;
                        var th = 16;
                        var px = x - tw / 2;
                        var py = y - 22;

                        ctx2.fillStyle = 'rgba(255,255,255,0.9)';
                        ctx2.strokeStyle = 'rgba(55,138,221,0.25)';
                        ctx2.lineWidth = 1;
                        ctx2.beginPath();
                        ctx2.roundRect(px, py, tw, th, 4);
                        ctx2.fill();
                        ctx2.stroke();

                        // Text
                        ctx2.fillStyle = color;
                        ctx2.fillText(timeStr, x, y - 9);
                    }
                });
            });

            ctx2.restore();
        }
    };

    return charts[canvasId] = new Chart(ctx, {
        type: 'bar',
        data: {
            labels,
            datasets: [
                // ── Bar: Lead count (behind the line) ──
                {
                    label: 'Leads',
                    type: 'bar',
                    data: leadCounts,
                    backgroundColor: 'rgba(55,138,221,0.10)',
                    borderColor: 'rgba(55,138,221,0.25)',
                    borderWidth: 1,
                    borderRadius: 4,
                    yAxisID: 'y1',
                    order: 2,
                    barPercentage: 0.55,
                    categoryPercentage: 0.7
                },
                // ── Line: Duration (on top) ──
                {
                    label: 'Duration',
                    type: 'line',
                    data: durations,
                    borderColor: color,
                    backgroundColor: 'rgba(55,138,221,0.06)',
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: color,
                    pointBorderWidth: 2,
                    pointRadius: 5,
                    pointHoverRadius: 8,
                    borderWidth: 2.5,
                    yAxisID: 'y',
                    order: 1
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            layout: {
                padding: { top: 30 }
            },
            interaction: {
                mode: 'index',
                intersect: false
            },
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
                    align: 'end',
                    labels: {
                        usePointStyle: true,
                        padding: 15,
                        font: { size: 11 },
                        color: '#666',
                        generateLabels: function(chart) {
                            return [
                                {
                                    text: 'Duration',
                                    fillStyle: color,
                                    strokeStyle: color,
                                    pointStyle: 'circle',
                                    hidden: false,
                                    datasetIndex: 1
                                },
                                {
                                    text: 'Lead Count',
                                    fillStyle: 'rgba(55,138,221,0.15)',
                                    strokeStyle: 'rgba(55,138,221,0.35)',
                                    pointStyle: 'rectRounded',
                                    hidden: false,
                                    datasetIndex: 0
                                }
                            ];
                        }
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(0,0,0,0.85)',
                    padding: 12,
                    cornerRadius: 8,
                    titleFont: { size: 12, weight: '600' },
                    bodyFont: { size: 12 },
                    bodySpacing: 6,
                    displayColors: true,
                    usePointStyle: true,
                    callbacks: {
                        title: function(items) {
                            return items[0] ? items[0].label : '';
                        },
                        label: function(c) {
                            if (c.dataset.label === 'Duration') {
                                return ' Duration: ' + formatTime(c.raw);
                            }
                            if (c.dataset.label === 'Leads') {
                                return ' Leads: ' + c.raw;
                            }
                            return c.raw;
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: {
                        font: { size: 11 },
                        color: '#666'
                    }
                },
                // Left Y-axis — Duration
                y: {
                    position: 'left',
                    grid: { color: 'rgba(128,128,128,0.08)' },
                    ticks: {
                        callback: v => formatTime(v),
                        font: { size: 11 },
                        color: '#666'
                    },
                    title: {
                        display: true,
                        text: 'Duration',
                        font: { size: 11, weight: '600' },
                        color: '#888'
                    }
                },
                // Right Y-axis — Lead count
                y1: {
                    position: 'right',
                    grid: { drawOnChartArea: false },
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1,
                        precision: 0,
                        font: { size: 11 },
                        color: 'rgba(55,138,221,0.5)'
                    },
                    title: {
                        display: true,
                        text: 'Leads',
                        font: { size: 11, weight: '600' },
                        color: 'rgba(55,138,221,0.5)'
                    }
                }
            }
        },
        plugins: [inlineLabelPlugin]
    });
}
function formatTime(seconds, showSeconds = false) {

    seconds = Math.floor(Math.max(0, seconds || 0));

    const h = Math.floor(seconds / 3600);
    const m = Math.floor((seconds % 3600) / 60);
    const s = seconds % 60;

    // If hours = 0 → hide hours
    const hourPart = h > 0 ? `${h}h ` : '';

    if (showSeconds) {
        return `${hourPart}${String(m).padStart(2, '0')}m ${String(s).padStart(2, '0')}s`;
    }

    return `${hourPart}${String(m).padStart(2, '0')}m`;
}

function setCallInformations(CallData = {}, yesterdayCallData = {}) {

    function toNumber(val) {
        let num = parseFloat(val);
        return isNaN(num) ? 0 : num;
    }
console.log(CallData);
console.log(yesterdayCallData);
    // ================= TODAY =================
    let callCount = Math.max(0, toNumber(CallData.callCount));
    let totalCalls = Math.max(0, toNumber(CallData.total_leads));
    let answeredCalls = Math.max(0, toNumber(CallData.answered_leads));
    let totalDuration = Math.max(0, toNumber(CallData.duration));

    // ================= YESTERDAY =================
    let y_callCount = Math.max(0, toNumber(yesterdayCallData.callCount));
    let y_totalCalls = Math.max(0, toNumber(yesterdayCallData.total_leads));
    let y_answeredCalls = Math.max(0, toNumber(yesterdayCallData.answered_leads));
    let y_totalDuration = Math.max(0, toNumber(yesterdayCallData.duration));

    // ================= CALCULATIONS =================

    let avgDuration = answeredCalls > 0 
        ? Math.floor(totalDuration / answeredCalls) 
        : 0;

    let y_avgDuration = y_answeredCalls > 0 
        ? Math.floor(y_totalDuration / y_answeredCalls) 
        : 0;

    let answeredPercentage = totalCalls > 0 
        ? Math.round((answeredCalls / totalCalls) * 100) 
        : 0;

    let y_answeredPercentage = y_totalCalls > 0 
        ? Math.round((y_answeredCalls / y_totalCalls) * 100) 
        : 0;
        
        

    // ================= CHANGES =================

    let callsCountChange = getChangePercent(callCount, y_callCount);
    let callsChange = getChangePercent(totalCalls, y_totalCalls);
    let answeredChange = getChangePercent(answeredCalls, y_answeredCalls); // ✅ fixed
    let avgChange = getChangePercent(avgDuration, y_avgDuration); // ✅ best metric
    let durationChange = getChangePercent(totalDuration, y_totalDuration);

    // ================= TRENDS =================

    let callsCountTrend = getTrend(callsCountChange);
    let callsTrend = getTrend(callsChange);
    let answeredTrend = getTrend(answeredChange);
    let avgTrend = getTrend(avgChange);
    let durationTrend = getTrend(durationChange);
    

    // ================= UI =================

    $(".total-calls-c").text(callCount.toLocaleString());
    $(".total-calls").text(totalCalls.toLocaleString());
    $(".total-calls-avg").text(formatTime(avgDuration));
    $(".total-calls-connect").text(answeredPercentage + "%");
    $(".total-calls-duration").text(formatTime(totalDuration));

    $(".y-total-calls-c").text(`vs ${y_callCount.toLocaleString()} yesterday`);
    $(".y-total-calls").text(`vs ${y_totalCalls.toLocaleString()} yesterday`);
    $(".y-total-calls-avg").text(`vs ${formatTime(y_avgDuration)} yesterday`);
    $(".y-total-calls-connect").text(`vs ${y_answeredPercentage}% yesterday`);
    $(".y-total-calls-duration").text(`vs ${formatTime(y_totalDuration)} yesterday`);

    function applyTrend(selector, trend) {
        $(selector)
            .html(trend.text)
            .removeClass("text-success text-danger text-muted up dn neutral")
            .addClass(trend.class);
    }

    applyTrend(".total-calls-c-grow", callsCountTrend);
    applyTrend(".total-calls-grow", callsTrend);
    applyTrend(".total-calls-avg-grow", avgTrend);
    applyTrend(".total-calls-connect-grow", answeredTrend);
    applyTrend(".total-calls-duration-grow", durationTrend);
}

// ✅ % change with validation
function getChangePercent(today, yesterday) {
    today = Number(today) || 0;
    yesterday = Number(yesterday) || 0;

    if (yesterday === 0) {
        return today > 0 ? 100 : 0;
    }

    let change = ((today - yesterday) / yesterday) * 100;

    if (!isFinite(change)) return 0;

    return Math.round(change); // ✅ no decimal
}

// ✅ Trend display with proper badge classes
function getTrend(value) {
    value = parseFloat(value) || 0;
    let roundedValue = Math.abs(Math.round(value));
    
    if (value > 0) {
        return { 
            text: "▲ +" + roundedValue + "%", 
            class: "up"  // Changed from text-success to match your badge class
        };
    }
    if (value < 0) { 
        return { 
            text: "▼ " + roundedValue + "%", 
            class: "dn"  // Changed from text-danger to match your badge class
        };
    }
    return { 
        text: "→ 0%", 
        class: "neutral"  // Changed from text-muted
    };
}


function renderHourlyChart(canvasId, apiData) {

    // 🔥 Destroy old chart
    if (charts[canvasId]) {
        charts[canvasId].destroy();
    }

    // ✅ Safe data
    const safeData = Array.isArray(apiData) ? apiData : [];

    const labels = [];
    const durationData = [];
    const leadsData = [];

    safeData.forEach(item => {
        let hour = Number(item?.hour_slot);

        let label = hour < 12
            ? `${hour || 12}am`
            : `${(hour - 12) || 12}pm`;

        labels.push(label);

        durationData.push(Number(item?.total_duration) || 0);
        leadsData.push(Number(item?.total_leads) || 0);
    });

    const totalLeads = leadsData.reduce((a, b) => a + b, 0);
    const totalDuration = durationData.reduce((a, b) => a + b, 0);

    const maxVal = Math.max(...durationData, 0);

    const top3 = [...safeData]
        .sort((a, b) => (b.total_duration || 0) - (a.total_duration || 0))
        .slice(0, 3);

    const ctx = document.getElementById(canvasId);
    if (!ctx) return;

    charts[canvasId] = new Chart(ctx, {
        type: 'bar',

        data: {
            labels,
            datasets: [{
                data: durationData,

                backgroundColor: durationData.map(v =>
                    v === maxVal ? BLUE : BLUE_LT
                ),

                borderRadius: 6,
                borderSkipped: false,

                // ✅ Better spacing
                barThickness: 50,
                categoryPercentage: 0.7,
                barPercentage: 0.8
            }]
        },

        options: {
            responsive: true,
            maintainAspectRatio: false,

            // ✅ Space for right box + breathing room
            layout: {
                padding: {
                    right: 150,
                    top: 10,
                    bottom: 10
                }
            },

            plugins: {
                legend: {
                    display: true,
                    labels: {
                        generateLabels: () => ([
                            {
                                text: `👥 Total Leads: ${totalLeads}`,
                                fillStyle: BLUE
                            },
                            {
                                text: `⏱ Total Time: ${formatTime(totalDuration)}`,
                                fillStyle: "#FF9800"
                            }
                        ])
                    }
                },

                datalabels: typeof ChartDataLabels !== 'undefined' ? {
                    labels: {

                        // ⏱ Duration label (top)
                        duration: {
                            anchor: 'end',
                            align: 'top',
                            offset: 6,
                            color: '#111',
                            font: { size: 11, weight: 'bold' },
                            formatter: value => formatTime(value)
                        },

                        // 👥 Leads label (center)
                        leads: {
                            anchor: 'center',
                            align: 'center',
                            color: '#111',
                            font: { size: 12, weight: 'bold' },
                            formatter: (value, ctx) => leadsData[ctx.dataIndex]
                        }
                    }
                } : {},

                tooltip: {
                    callbacks: {
                        label: c => {
                            const i = c.dataIndex;
                            return `${leadsData[i]} leads | ${formatTime(c.raw)}`;
                        }
                    }
                }
            },

            scales: {
                x: {
                    grid: { display: false },
                    ticks: { font: { size: 11 } }
                },

                y: {
                    beginAtZero: true,

                    // ✅ Key spacing fix
                    grace: '20%',

                    grid: { color: 'rgba(128,128,128,0.1)' },

                    ticks: {
                        callback: value => formatTime(value)
                    }
                }
            }
        },

        plugins: typeof ChartDataLabels !== 'undefined'
            ? [ChartDataLabels]
            : []
    });

    // 📦 Parent container
    const container = ctx.parentNode;
    container.style.position = "relative";

    // 🔥 Remove old Top-3 box (IMPORTANT)
    const oldBox = container.querySelector('.top-hours-box');
    if (oldBox) oldBox.remove();

    // 🥇 TOP 3 HOURS BOX
    const topHtml = `
        <div class="top-hours-box" style="
            position:absolute;
            right:10px;
            top:10px;
            background:#fff;
            padding:12px;
            border-radius:10px;
            box-shadow:0 4px 16px rgba(0,0,0,0.08);
            font-size:12px;
            min-width:180px;
        ">
            <b>Top 3 Hours</b>
            <hr style="margin:6px 0;">

            ${top3.map(item => {
                let hour = Number(item?.hour_slot);

                let label = hour < 12
                    ? `${hour || 12}am`
                    : `${(hour - 12) || 12}pm`;

                return `
                    <div style="margin-bottom:8px;">
                        <b>${label}</b><br>
                        👥 ${item?.total_leads || 0} leads<br>
                        ⏱ ${formatTime(item?.total_duration || 0)}
                    </div>
                `;
            }).join('')}
        </div>
    `;

    container.insertAdjacentHTML("beforeend", topHtml);

    return charts[canvasId];
}
function renderStatusChart(canvasId, apiData) {

    // 🔥 Destroy old chart
    if (charts[canvasId]) {
        charts[canvasId].destroy();
    }

    // ✅ Safe data
    const safeData = Array.isArray(apiData) ? apiData : [];

    const labels = [];
    const timeData = [];
    const leadsData = [];
    const colors = [];

    safeData.forEach(item => {
        labels.push(item?.status_name || 'Unknown');

        timeData.push(Number(item?.total_duration) || 0);
        leadsData.push(Number(item?.total_leads) || 0);

        colors.push(item?.color || "#999999");
    });

    const totalLeads = leadsData.reduce((a, b) => a + b, 0);
    const totalDuration = timeData.reduce((a, b) => a + b, 0);

    const maxTime = Math.max(...timeData, 0);

    // 🥇 TOP 3 BY LEADS
    const top3 = [...safeData]
        .sort((a, b) => (b.total_leads || 0) - (a.total_leads || 0))
        .slice(0, 3);

    const ctx = document.getElementById(canvasId);
    if (!ctx) return;

    charts[canvasId] = new Chart(ctx, {
        type: 'bar',

        data: {
            labels,
            datasets: [{
                data: timeData,

                // 🎨 API colors with opacity
                backgroundColor: colors.map(c => c + "CC"),

                borderRadius: 6,
                borderSkipped: false,

                // ✅ Better spacing
                barThickness: 50,
                categoryPercentage: 0.7,
                barPercentage: 0.8
            }]
        },

        options: {
            responsive: true,
            maintainAspectRatio: false,

            // ✅ Space for right box
            layout: {
                padding: {
                    right: 160,
                    top: 10,
                    bottom: 10
                }
            },

            plugins: {

                legend: {
                    display: true,
                    labels: {
                        generateLabels: () => ([
                            {
                                text: `👥 Total Leads: ${totalLeads}`,
                                fillStyle: "#333"
                            },
                            {
                                text: `⏱ Total Time: ${formatTime(totalDuration)}`,
                                fillStyle: "#FF9800"
                            }
                        ])
                    }
                },

                datalabels: typeof ChartDataLabels !== 'undefined' ? {
                    labels: {

                        // ⏱ Duration (top)
                        duration: {
                            anchor: 'end',
                            align: 'top',
                            offset: 6,
                            color: '#111',
                            font: { size: 11, weight: 'bold' },
                            formatter: value => formatTime(value)
                        },

                        // 👥 Leads (center)
                        leads: {
                            anchor: 'center',
                            align: 'center',
                            color: '#111',
                            font: { size: 12, weight: 'bold' },
                            formatter: (value, ctx) => leadsData[ctx.dataIndex]
                        }
                    }
                } : {},

                tooltip: {
                    callbacks: {
                        label: c => {
                            const i = c.dataIndex;
                            return `${leadsData[i]} leads | ${formatTime(c.raw)}`;
                        }
                    }
                }
            },

            scales: {
                x: {
                    grid: { display: false },
                    ticks: { font: { size: 11 } }
                },

                y: {
                    beginAtZero: true,

                    // ✅ spacing fix
                    grace: '20%',

                    grid: { color: 'rgba(128,128,128,0.1)' },

                    ticks: {
                        callback: value => formatTime(value)
                    }
                }
            }
        },

        plugins: typeof ChartDataLabels !== 'undefined'
            ? [ChartDataLabels]
            : []
    });

    // 📦 Container
    const container = ctx.parentNode;
    container.style.position = "relative";

    // 🔥 Remove old Top-3 box (IMPORTANT)
    const oldBox = container.querySelector('.top-status-box');
    if (oldBox) oldBox.remove();

    // 🥇 TOP 3 BOX
    const topHtml = `
        <div class="top-status-box" style="
            position:absolute;
            right:10px;
            top:10px;
            background:#fff;
            padding:12px;
            border-radius:10px;
            box-shadow:0 4px 16px rgba(0,0,0,0.08);
            font-size:12px;
            min-width:190px;
        ">
            <b>Top 3 Status</b>
            <hr style="margin:6px 0;">

            ${top3.map(item => `
                <div style="margin-bottom:8px;">
                    <span style="color:${item?.color || '#999'};font-weight:bold;">●</span>
                    <b>${item?.status_name || 'Unknown'}</b><br>
                    👥 ${item?.total_leads || 0} leads<br>
                    ⏱ ${formatTime(item?.total_duration || 0)}
                </div>
            `).join('')}
        </div>
    `;

    container.insertAdjacentHTML("beforeend", topHtml);

    return charts[canvasId];
}
loadDailyCalls();
// initDatePicker();

const rankPalettes=[
  ['#EAF3DE','#27500A'],['#E6F1FB','#0C447C'],['#EEEDFE','#3C3489'],
  ['#FAEEDA','#633806'],['#E1F5EE','#085041']
];



function formatHM(seconds) {
    seconds = Number(seconds || 0);

    const h = Math.floor(seconds / 3600);
    const m = Math.floor((seconds % 3600) / 60);

    return `${h}h ${m}m`;
}


function makeBars(id, items, color, rankPalette, type = "leads") {

console.log(items);
    const el = document.getElementById(id);
    el.innerHTML = "";

    if (!items || items.length === 0) {
        el.innerHTML = `<div style="padding:10px;">No Data</div>`;
        return;
    }

    // 🔥 SELECT VALUE BASED ON TYPE
   const values = items.map(i =>
    type === "duration"
        ? Number(i.total_duration || 0)
        : type === "connected"
            ? (Number(i.total_answered_calls || 0) / Number(i.total_leads || 1)) * 100
            : Number(i.total_leads || 0)
);

    const maxVal = Math.max(...values);

    items.forEach((item, i) => {

       const value = type === "duration"
    ? Number(item.total_duration || 0)
    : type === "connected"
        ? (Number(item.total_answered_calls || 0) / Number(item.total_leads || 1)) * 100
        : Number(item.total_leads || 0);

       const pct = type === "connected"
    ? value
    : maxVal > 0
        ? Math.round((value / maxVal) * 100)
        : 0;

        const [bg, fg] = rankPalette[i] || ["#eee", "#333"];

        const firstName = (item.staff_name || "").split(" ")[0];

       const displayValue = type === "duration"
    ? formatHM(value)
    : type === "connected"
        ? value.toFixed(1) + "%"
        : value;

        el.innerHTML += `
            <div class="bar-row" style="display:flex;align-items:center;margin:6px 0;">

                <div class="rank-num"
                    style="width:24px;height:24px;border-radius:50%;
                    background:${bg};color:${fg};
                    display:flex;align-items:center;justify-content:center;
                    font-size:12px;font-weight:bold;">
                    ${i + 1}
                </div>

                <div class="bar-lbl"
                    title="${item.staff_name}"
                    style="width:90px;margin-left:8px;font-size:12px;">
                    ${firstName}
                </div>

                <div class="bar-track"
                    style="flex:1;background:#f0f0f0;height:8px;border-radius:6px;overflow:hidden;margin:0 8px;">

                    <div class="bar-fill"
                        style="width:${pct}%;background:${color};height:100%;">
                    </div>
                </div>

                <div class="bar-val"
                    style="width:70px;font-size:12px;text-align:right;">
                    ${displayValue}
                </div>

            </div>
        `;
    });
}


function createAttemptsChart(canvasId, data = []) {

    const ctx = document.getElementById(canvasId);
    if (!ctx) return;

    const safeData = Array.isArray(data) ? data : [];

    if (charts[canvasId]) {
        charts[canvasId].destroy();
    }

    const labels = safeData.map(item => item?.staff_name || 'Unknown');
    const attempts = safeData.map(item => Number(item?.total_calls) || 0);
    const connects = safeData.map(item => Number(item?.total_connected) || 0);
    
    const totalAttempts = attempts.reduce((a, b) => a + b, 0);
const totalConnects = connects.reduce((a, b) => a + b, 0);

    charts[canvasId] = new Chart(ctx, {
        type: 'bar',
        data: {
            labels,
            datasets: [
                {
                    label: 'Attempts',
                    data: attempts,
                    backgroundColor: BLUE_LT,
                    borderRadius: 6,
                    borderSkipped: false,
                    barThickness: 30
                },
                {
                    label: 'Connects',
                    data: connects,
                    backgroundColor: TEAL,
                    borderRadius: 6,
                    borderSkipped: false,
                    barThickness: 30
                }
            ]
        },
        title: {
    display: true,
    text: `Attempts: ${totalAttempts} | Connects: ${totalConnects}`,
    font: {
        size: 14,
        weight: 'bold'
    }
},
        options: {
            responsive: true,
            maintainAspectRatio: false,

            // ✅ Canvas padding (outer space)
            layout: {
                padding: {
                    bottom: 10
                }
            },

            plugins: {
                legend: {
                    display: true,
                    position: 'top'
                },
                tooltip: {
                    callbacks: {
                        label: c => `${c.dataset.label}: ${c.raw ?? 0}`
                    }
                },
                datalabels: {
                    anchor: 'end',
                    align: 'top',   // ✅ better spacing
                    offset: 6,      // ✅ creates gap above bar
                    color: '#000',
                    font: {
                        weight: 'bold',
                        size: 11
                    },
                    formatter: v => v ?? 0
                }
            },

            scales: {
                x: {
                    grid: { display: false },
                    ticks: {
                        font: { size: 10 },
                        maxRotation: 45,
                        minRotation: 30
                    }
                },
                y: {
                    beginAtZero: true,

                    // ✅ THIS creates space above bars (best alternative to margin)
                    grace: '10%',

                    grid: {
                        color: 'rgba(128,128,128,0.1)'
                    }
                }
            }
        },

        plugins: [ChartDataLabels]
    });

    return charts[canvasId];
}

</script>

