<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<?php $this->load->helper('leads'); ?>

<style>
    /* Modern CSS Variables */
    :root {
        --primary: #3b82f6;
        --primary-dark: #2563eb;
        --success: #10b981;
        --warning: #f59e0b;
        --danger: #ef4444;
        --purple: #8b5cf6;
        --gray-50: #f8fafc;
        --gray-100: #f1f5f9;
        --gray-200: #e2e8f0;
        --gray-600: #475569;
        --gray-700: #334155;
        --gray-800: #1e293b;
        --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
        --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1);
        --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1);
        --radius-lg: 1rem;
        --radius-md: 0.75rem;
        --radius-sm: 0.5rem;
        --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        --card-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
    }

    /* Stats Grid */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 1.25rem;
        margin-bottom: 1.5rem;
    }

    .stat-card {
        background: white;
        border-radius: var(--radius-lg);
        padding: 1.25rem;
        display: flex;
        align-items: center;
        gap: 1rem;
        box-shadow: var(--shadow-sm);
        border: 1px solid var(--gray-200);
        transition: all 0.2s ease;
    }

    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-md);
        border-color: var(--primary);
    }

    .stat-icon {
        width: 52px;
        height: 52px;
        border-radius: var(--radius-md);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
    }

    .stat-icon.blue {
        background: #eff6ff;
        color: var(--primary);
    }

    .stat-icon.green {
        background: #ecfdf5;
        color: var(--success);
    }

    .stat-icon.orange {
        background: #fffbeb;
        color: var(--warning);
    }

    .stat-info h4 {
        font-size: 0.85rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: var(--gray-600);
        margin: 0 0 0.25rem 0;
    }

    .stat-number {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--gray-800);
        line-height: 1.2;
    }

    .stat-info small {
        font-size: 0.7rem;
        color: var(--gray-600);
    }

    /* Counselor Panel */
    .counselor-panel {
        background: white;
        border-radius: var(--radius-lg);
        border: 1px solid var(--gray-200);
        overflow: hidden;
        margin-bottom: 1.5rem;
    }

    .counselor-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1rem 1.5rem;
        background: var(--gray-50);
        border-bottom: 1px solid var(--gray-200);
    }

    .header-title {
        font-weight: 600;
        font-size: 1.1rem;
        color: var(--gray-800);
    }

    .header-title i {
        color: var(--primary);
        margin-right: 0.5rem;
    }

    .chart-ranking-wrapper {
        /*display: grid;*/
        grid-template-columns: 1fr 300px;
        gap: 1.5rem;
        padding: 1.5rem;
    }

    .chart-container {
        min-height: 320px;
    }

    .ranking-card {
       position: absolute;
    background: #fff;
    top: 30rem;
    right: 10rem;
    width: 300px;
    border-radius: 12px;
    padding: 0 0.5rem;
        box-shadow: 2px 3px 10px lightgrey;
    padding: 15px !important;
    }
    
    .source-card
    {
      right: 50rem;  
    }

    .ranking-card h5 {
        margin-bottom: 1rem;
        font-weight: 600;
        color: #1f2937;
        display: flex;
        align-items: center;
    }

    .ranking-card h5 i {
        margin-right: 8px;
        color: #f59e0b;
    }

    .ranking-item {
        margin-bottom: 1rem;
    }

    .rank-info {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 4px;
        font-size: 0.85rem;
    }

    .rank-bar {
        background: #e2e8f0;
        height: 6px;
        border-radius: 3px;
        overflow: hidden;
    }

    .bar-fill {
        background: var(--primary);
        height: 100%;
        border-radius: 3px;
        transition: width 0.3s ease;
    }

    /* Filter Section */
    .filter-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.25rem;
    }

    .filter-title {
        font-weight: 600;
        font-size: 1rem;
        color: var(--gray-700);
    }

    .btn-reset {
        background: transparent;
        border: 1px solid var(--gray-200);
        padding: 0.4rem 1rem;
        border-radius: 30px;
        font-size: 0.75rem;
        color: var(--gray-600);
        cursor: pointer;
        transition: all 0.2s;
    }

    .filter-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 1rem;
        margin-bottom: 1.5rem;
        align-items: end;
    }

    .filter-group label {
        font-size: 1rem;
        font-weight: 600;
        text-transform: uppercase;
        color: var(--gray-600);
        display: block;
        margin-bottom: 0.4rem;
    }

    .date-range-input {
        width: 100%;
        padding: 0.5rem 0.75rem;
        border: 1px solid var(--gray-200);
        border-radius: var(--radius-sm);
        font-size: 0.8rem;
        background: white;
    }

    .btn-apply {
        background: var(--primary);
        color: white;
        border: none;
        padding: 0.5rem 1.25rem;
        border-radius: 8px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-apply:hover {
        background: var(--primary-dark);
    }

    .table-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin: 1.25rem 0 1rem;
    }

    .table-title {
        font-weight: 600;
        font-size: 1rem;
    }

    .table-title i {
        color: var(--primary);
        margin-right: 0.5rem;
    }

    .table-info {
        font-size: 0.75rem;
        color: var(--gray-600);
        background: var(--gray-100);
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
    }

    /* Tabs */
    .tabs {
        display: flex;
        gap: 10px;
        border-bottom: 2px solid #eee;
        margin-bottom: 20px;
    }

    .tab {
        padding: 10px 20px;
        cursor: pointer;
        font-size: 14px;
        border-radius: 8px 8px 0 0;
        background: #f5f5f5;
        color: #555;
        transition: all 0.3s ease;
    }

    .tab.active {
        background: var(--primary-gradient);
        color: #fff;
        font-weight: 500;
    }

    .tabs_ {
        display: inline-flex;
        gap: 8px;
        margin-left: 15px;
    }

    .tab-btn {
        padding: 4px 12px;
        border: none;
        border-radius: 20px;
        background: #f3f4f6;
        cursor: pointer;
        font-size: 11px;
        font-weight: 500;
        transition: all 0.2s;
    }

    .tab-btn.active {
        background: #3b82f6;
        color: #fff;
    }

    .tab-content.active {
        display: block;
    }

    .tab-content.hide {
        display: none;
    }

    @media (max-width: 1200px) {
        .stats-grid { grid-template-columns: repeat(2, 1fr); }
        .chart-ranking-wrapper { grid-template-columns: 1fr; }
        .filter-row { grid-template-columns: repeat(2, 1fr); }
    }

    @media (max-width: 768px) {
        .stats-grid { grid-template-columns: 1fr; }
        .filter-row { grid-template-columns: 1fr; }
    }
</style>

<div id="wrapper">
    <div class="content panel_s">
        <div class="row panel-body">
            <div class="col-md-12">
                <!-- Main Tabs -->
                <div class="tabs">
                    <div class="tab active" onclick="setStatus(20)" data-tab="not_reachable_leads">Not Reachable Leads</div>
                    <div class="tab" onclick="setStatus(2)" data-tab="not_reachable_leads">Fresh Leads (NR) </div>
                    <div class="tab" onclick="setStatus()"data-tab="self_transfer_leads">Self Transfer Leads</div>
                    <div class="tab" onclick="setStatus()" data-tab="fresh_transfer_leads">Fresh Leads</div>
                </div>

                <!-- Tab 1: Not Reachable Leads -->
                <div id="not_reachable_leads" class="tab-content active">
                    <!-- Stats Cards -->
                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="stat-icon blue"><i class="fa fa-exchange"></i></div>
                            <div class="stat-info">
                                <h4>Total Transfers</h4>
                                <div class="stat-number" id="totalTransfers">0</div>
                                <small>Not reachable leads</small>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon green"><i class="fa fa-users"></i></div>
                            <div class="stat-info">
                                <h4>Active Counselors</h4>
                                <div class="stat-number" id="activeCounselors">0</div>
                                <small>With transfers</small>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon orange"><i class="fa fa-trophy"></i></div>
                            <div class="stat-info">
                                <h4>Top Counselor</h4>
                                <div class="stat-number" id="topCounselorName">—</div>
                                <small id="topCounselorLeadsCount">0 leads</small>
                            </div>
                        </div>
                    </div>

                    <!-- Counselor Performance Panel -->
                    <div class="counselor-panel">
                        <div class="counselor-header">
                            <div class="header-title">
                                <i class="fa fa-chalkboard-user"></i> Counselor Performance
                                <div class="tabs_">
                                    <button class="tab-btn active" data-type="transfer" data-tab="main">Lead Transfer</button>
                                    <button class="tab-btn" data-type="assign" data-tab="main">Lead Assignation</button>
                                </div>
                            </div>
                            <button type="button" class="btn-icon  btn btn-primary" onclick="refreshCurrentTabData()" title="Refresh">
                                <i class="fa fa-refresh"></i>
                            </button>
                        </div>
                        <div class="chart-ranking-wrapper">
                            <div class="chart-container">
                                <canvas id="counselorChart"></canvas>
                                <div class="ranking-card">
                                <h5><i class="fa fa-ranking-star"></i> Counselor Ranking</h5>
                                <div id="counselorRankingList"></div> 
                            </div>
                                   <div class="ranking-card source-card">
                                <h5><i class="fa fa-ranking-star"></i> Sources Ranking</h5>
                                 <div id="topSourcesList"></div> 
                            </div>
                            </div>
                        </div>
                    </div>

                    <!-- Filters & Table -->
                    <div class="mtop15">
                        <div class="panel_s">
                            <div class="panel-body">
                                <div class="filter-header">
                                    <div class="filter-title"><i class="fa fa-filter"></i> Advanced Filters</div>
                                    <button type="button" class="btn-reset" onclick="resetFilters()"><i class="fa fa-undo-alt"></i> Reset All</button>
                                </div>
                                <div class="filter-row">
                                    <?php if (has_permission('leads', '', 'view')) { ?>
                                        <div class="filter-group">
                                            <label><i class="fa fa-user-circle"></i> Department</label>
                                            <?php echo render_select('department[]', $departments, array('id', 'name'), '', '', array('data-width' => '100%', 'data-none-selected-text' => 'Select department', 'multiple' => true, 'data-actions-box' => true, 'data-live-search' => true), array(), 'no-mbot', '', false, 'department'); ?>
                                        </div>
                                         <div class="filter-group hide">
                                            <label><i class="fa fa-user-circle"></i> Department</label>
                                            <?php echo render_select('status[]', $lead_statuses, array('id', 'name'), '', '', array('data-width' => '100%', 'data-none-selected-text' => 'Select Status', 'multiple' => true, 'data-actions-box' => true, 'data-live-search' => true), array(), 'no-mbot', '', false, 'status'); ?>
                                        </div>
                                        <div class="filter-group">
                                            <label><i class="fa fa-user"></i> Counselor</label>
                                            <?php echo render_select('view_assigned[]', $staff, array('staffid', array('firstname', 'lastname')), '', '', array('data-width' => '100%', 'data-none-selected-text' => 'All Counselors', 'multiple' => true, 'data-actions-box' => true), array(), 'no-mbot', '', false, 'view_assigned'); ?>
                                        </div>
                                    <?php } ?>
                                    <div class="filter-group">
                                        <label><i class="fa fa-tag"></i> Lead Source</label>
                                        <?php echo render_select('view_sources[]', $lead_sources, array('id', 'name'), '', '', array('data-width' => '100%', 'data-none-selected-text' => 'All Sources', 'multiple' => true, 'data-actions-box' => true), array(), 'no-mbot', '', false, 'view_sources'); ?>
                                    </div>
                                    
                                    <div class="filter-group">
                                           <label><i class="fa fa-tag"></i> Lead Status</label>
                                        <?php echo render_select('c_status[]', $lead_statuses, array('id', 'name'), '', '', array('data-width' => '100%', 'data-none-selected-text' => 'All Status', 'multiple' => true, 'data-actions-box' => true), array(), 'no-mbot', '', false, 'c_status'); ?>
                                    </div>
                                    <div class="filter-group">
                                        <label><i class="fa fa-history"></i> Update Count</label>
                                        <?php
                                        $update_counts = [['id' => 0, 'name' => '0'], ['id' => 1, 'name' => '1'], ['id' => 2, 'name' => '2'], ['id' => 3, 'name' => '3'], ['id' => 4, 'name' => '4']];
                                        echo render_select('view_update_count[]', $update_counts, array('id', 'name'), '', '', array('data-width' => '100%', 'data-none-selected-text' => 'Any Count', 'multiple' => true, 'data-actions-box' => true), array(), 'no-mbot', '', false, 'view_update_count'); ?>
                                    </div>
                                    <div class="filter-group">
                                        <label><i class="fa fa-calendar"></i> Transfer Date</label>
                                        <input type="text" readonly name="transfer_date" id="date_range" class="date-range-input" placeholder="Select date range">
                                    </div>
                                    <div class="filter-actions">
                                        <button class="btn-apply" onclick="applyFilters()"><i class="fa fa-filter"></i> Apply Filters</button>
                                    </div>
                                </div>

                                <div class="table-header">
                                    <div class="table-title"><i class="fa fa-history"></i> Leads Transfer Logs <small>Not reachable leads history</small></div>
                                    <div class="table-info">Showing <span id="recordCount">0</span> records</div>
                                </div>
                                <div class="table-responsive">
                                    <?php
                                    $_table_data = [];
                                    $_table_data[] = ['name' => 'ID', 'th_attrs' => ['class' => 'id']];
                                    $_table_data[] = ['name' => _l('Name')];
                                    $_table_data[] = ['name' => _l('Phone Number')];
                                    $_table_data[] = ['name' => _l('Source')];
                                    $_table_data[] = ['name' => _l('Update Count')];
                                    $_table_data[] = ['name' => _l('Sub Status')];
                                    $_table_data[] = ['name' => _l('Old Status')];
                                    $_table_data[] = ['name' => _l('New Status')];
                                    $_table_data[] = ['name' => _l('Current Status')];
                                    $_table_data[] = ['name' => _l('Old Assigned')];
                                    $_table_data[] = ['name' => _l('New Assigned')];
                                    $_table_data[] = ['name' => _l('Old Assigned Date')];
                                    $_table_data[] = ['name' => _l('New Assigned Date')];
                                    render_datatable($_table_data, 'leads-transfers', ['customizable-table', 'sticky-header']);
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tab 2: Self Transfer Leads -->
                <div id="self_transfer_leads" class="tab-content hide">
                    <div class="stats-grid">
                        <div class="stat-card"><div class="stat-icon blue"><i class="fa fa-exchange"></i></div><div class="stat-info"><h4>Total Transfers</h4><div class="stat-number" id="totalTransfers_self">0</div><small>Self transfers</small></div></div>
                        <div class="stat-card"><div class="stat-icon green"><i class="fa fa-users"></i></div><div class="stat-info"><h4>Active Counselors</h4><div class="stat-number" id="activeCounselors_self">0</div><small>With transfers</small></div></div>
                        <div class="stat-card"><div class="stat-icon orange"><i class="fa fa-trophy"></i></div><div class="stat-info"><h4>Top Counselor</h4><div class="stat-number" id="topCounselorName_self">—</div><small id="topCounselorLeadsCount_self">0 leads</small></div></div>
                    </div>

                    <div class="counselor-panel">
                        <div class="counselor-header">
                            <div class="header-title">
                                <i class="fa fa-chalkboard-user"></i> Counselor Performance
                                <div class="tabs_">
                                    <button class="tab-btn active" data-type="transfer" data-tab="self">Lead Transfer</button>
                                    <button class="tab-btn" data-type="assign" data-tab="self">Lead Assignation</button>
                                </div>
                            </div>
                            <button type="button" class="btn-icon  btn btn-primary" onclick="refreshCurrentTabData()" title="Refresh"><i class="fa fa-refresh"></i></button>
                        </div>
                        <div class="chart-ranking-wrapper">
                            <div class="chart-container"><canvas id="counselorChart_self"></canvas>
                            <div class="ranking-card"><h5><i class="fa fa-ranking-star"></i> Counselor Ranking</h5><div id="counselorRankingList_self"></div></div>
                            <div class="ranking-card source-card"><h5><i class="fa fa-ranking-star"></i> Sources Ranking</h5><div id="topSourcesList_self"></div></div>
                        </div>
                        </div>
                    </div>

                    <div class="mtop15">
                        <div class="panel_s">
                            <div class="panel-body">
                                <div class="filter-header">
                                    <div class="filter-title"><i class="fa fa-filter"></i> Advanced Filters</div>
                                    <button type="button" class="btn-reset" onclick="resetFilters()"><i class="fa fa-undo-alt"></i> Reset All</button>
                                </div>
                                <div class="filter-row">
                                    <?php if (has_permission('leads', '', 'view')) { ?>
                                        <div class="filter-group"><label><i class="fa fa-user-circle"></i> Department</label><?php echo render_select('department_self[]', $departments, array('id', 'name'), '', '', array('data-width' => '100%', 'data-none-selected-text' => 'Select department', 'multiple' => true, 'data-actions-box' => true, 'data-live-search' => true), array(), 'no-mbot', '', false, 'department_self'); ?></div>
                                        <div class="filter-group"><label><i class="fa fa-user"></i> Counselor</label><?php echo render_select('view_assigned_self[]', $staff, array('staffid', array('firstname', 'lastname')), '', '', array('data-width' => '100%', 'data-none-selected-text' => 'All Counselors', 'multiple' => true, 'data-actions-box' => true), array(), 'no-mbot', '', false, 'view_assigned_self'); ?></div>
                                    <?php } ?>
                                    <div class="filter-group"><label><i class="fa fa-tag"></i> Lead Status</label><?php echo render_select('view_status_self[]', $lead_statuses, array('id', 'name'), '', '', array('data-width' => '100%', 'data-none-selected-text' => 'All Statuses', 'multiple' => true, 'data-actions-box' => true), array(), 'no-mbot', '', false, 'view_status_self'); ?></div>
                                    <div class="filter-group">
                                    <label><i class="fa fa-tag"></i> Lead Source</label>
                                        <?php echo render_select('view_sources_self[]', $lead_sources, array('id', 'name'), '', '', array('data-width' => '100%', 'data-none-selected-text' => 'All Sources', 'multiple' => true, 'data-actions-box' => true), array(), 'no-mbot', '', false, 'view_sources_self'); ?>
                                        </div>
                                    <div class="filter-group"><label><i class="fa fa-history"></i> Update Count</label><?php echo render_select('view_update_count_self[]', $update_counts, array('id', 'name'), '', '', array('data-width' => '100%', 'data-none-selected-text' => 'Any Count', 'multiple' => true, 'data-actions-box' => true), array(), 'no-mbot', '', false, 'view_update_count_self'); ?></div>
                                    <div class="filter-group"><label><i class="fa fa-calendar"></i> Transfer Date</label><input type="text" readonly name="transfer_date_self" id="date_range_self" class="date-range-input" placeholder="Select date range"></div>
                                    <div class="filter-actions"><button class="btn-apply" onclick="applyFiltersSelf()"><i class="fa fa-filter"></i> Apply Filters</button></div>
                                </div>
                                <div class="table-header"><div class="table-title"><i class="fa fa-history"></i> Self Transfer Logs <small>Self transfer history</small></div><div class="table-info">Showing <span id="recordCount_self">0</span> records</div></div>
                                <div class="table-responsive">
                                    <?php
                                    $columns = [_l('Raised by'),'Status', _l('Type'), 'Source', _l('Assignation'), _l('Phone Number'), _l('New Type'), 'New Source', _l('Reason'), _l('status'), _l('Created Date')];
                                    render_datatable($columns, 'leads-transfers-self', ['customizable-table', 'sticky-header']);
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Tab 3: Fresh Leads -->
                <div id="fresh_transfer_leads" class="tab-content hide">
                    <!-- Stats Cards -->
                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="stat-icon blue"><i class="fa fa-exchange"></i></div>
                            <div class="stat-info">
                                <h4>Total Transfers</h4>
                                <div class="stat-number" id="totalTransfers_fresh">0</div>
                                <small>Fresh leads</small>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon green"><i class="fa fa-users"></i></div>
                            <div class="stat-info">
                                <h4>Active Counselors</h4>
                                <div class="stat-number" id="activeCounselors_fresh">0</div>
                                <small>With transfers</small>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon orange"><i class="fa fa-trophy"></i></div>
                            <div class="stat-info">
                                <h4>Top Counselor</h4>
                                <div class="stat-number" id="topCounselorName_fresh">—</div>
                                <small id="topCounselorLeadsCount_fresh">0 leads</small>
                            </div>
                        </div>
                    </div>

                    <!-- Counselor Performance Panel -->
                    <div class="counselor-panel">
                        <div class="counselor-header">
                            <div class="header-title">
                                <i class="fa fa-chalkboard-user"></i> Counselor Performance
                                <div class="tabs_">
                                    <button class="tab-btn active" data-type="transfer" data-tab="fresh">Lead Transfer</button>
                                    <button class="tab-btn" data-type="assign" data-tab="fresh">Lead Assignation</button>
                                </div>
                            </div>
                            <button type="button" class="btn-icon btn btn-primary" onclick="refreshCurrentTabData()" title="Refresh">
                                <i class="fa fa-refresh"></i>
                            </button>
                        </div>
                        <div class="chart-ranking-wrapper">
                            <div class="chart-container">
                                <canvas id="counselorChart_fresh"></canvas>
                                <div class="ranking-card">
                                    <h5><i class="fa fa-ranking-star"></i> Counselor Ranking</h5>
                                    <div id="counselorRankingList_fresh"></div>
                                </div>
                                <div class="ranking-card source-card">
                                    <h5><i class="fa fa-ranking-star"></i> Sources Ranking</h5>
                                    <div id="topSourcesList_fresh"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Filters & Table -->
                    <div class="mtop15">
                        <div class="panel_s">
                            <div class="panel-body">
                                <div class="filter-header">
                                    <div class="filter-title"><i class="fa fa-filter"></i> Advanced Filters</div>
                                    <button type="button" class="btn-reset" onclick="resetFiltersFresh()"><i class="fa fa-undo-alt"></i> Reset All</button>
                                </div>
                                <div class="filter-row">
                                    <?php if (has_permission('leads', '', 'view')) { ?>
                                        <div class="filter-group">
                                            <label><i class="fa fa-user-circle"></i> Department</label>
                                            <?php echo render_select('department_fresh[]', $departments, array('id', 'name'), '', '', array('data-width' => '100%', 'data-none-selected-text' => 'Select department', 'multiple' => true, 'data-actions-box' => true, 'data-live-search' => true), array(), 'no-mbot', '', false, 'department_fresh'); ?>
                                        </div>
                                        <div class="filter-group">
                                            <label><i class="fa fa-user"></i> Counselor</label>
                                            <?php echo render_select('view_assigned_fresh[]', $staff, array('staffid', array('firstname', 'lastname')), '', '', array('data-width' => '100%', 'data-none-selected-text' => 'All Counselors', 'multiple' => true, 'data-actions-box' => true), array(), 'no-mbot', '', false, 'view_assigned_fresh'); ?>
                                        </div>
                                    <?php } ?>
                                    <div class="filter-group">
                                        <label><i class="fa fa-tag"></i> Lead Source</label>
                                        <?php echo render_select('view_sources_fresh[]', $lead_sources, array('id', 'name'), '', '', array('data-width' => '100%', 'data-none-selected-text' => 'All Sources', 'multiple' => true, 'data-actions-box' => true), array(), 'no-mbot', '', false, 'view_sources_fresh'); ?>
                                    </div>
                                      <div class="filter-group">
                                        <label><i class="fa fa-tag"></i> Lead Status</label>
                                        <?php echo render_select('c_status_fresh[]', $lead_statuses, array('id', 'name'), '', '', array('data-width' => '100%', 'data-none-selected-text' => 'All Status', 'multiple' => true, 'data-actions-box' => true), array(), 'no-mbot', '', false, 'c_status_fresh'); ?>
                                    </div>
                                    <div class="filter-group">
                                        <label><i class="fa fa-history"></i> Update Count</label>
                                        <?php echo render_select('view_update_count_fresh[]', $update_counts, array('id', 'name'), '', '', array('data-width' => '100%', 'data-none-selected-text' => 'Any Count', 'multiple' => true, 'data-actions-box' => true), array(), 'no-mbot', '', false, 'view_update_count_fresh'); ?>
                                    </div>
                                    <div class="filter-group">
                                        <label><i class="fa fa-calendar"></i> Transfer Date</label>
                                        <input type="text" readonly name="transfer_date_fresh" id="date_range_fresh" class="date-range-input" placeholder="Select date range">
                                    </div>
                                    <div class="filter-actions">
                                        <button class="btn-apply" onclick="applyFiltersFresh()"><i class="fa fa-filter"></i> Apply Filters</button>
                                    </div>
                                </div>

                                <div class="table-header">
                                    <div class="table-title"><i class="fa fa-history"></i> Leads Transfer Logs <small>Fresh leads history</small></div>
                                    <div class="table-info">Showing <span id="recordCount_fresh">0</span> records</div>
                                </div>
                                <div class="table-responsive">
                                    <?php
                                    $_table_data_fresh = [];
                                    $_table_data_fresh[] = ['name' => 'ID', 'th_attrs' => ['class' => 'id']];
                                    $_table_data_fresh[] = ['name' => _l('Name')];
                                    $_table_data_fresh[] = ['name' => _l('Phone Number')];
                                    $_table_data_fresh[] = ['name' => _l('Source')];
                                    $_table_data_fresh[] = ['name' => _l('Update Count')];
                                    $_table_data_fresh[] = ['name' => _l('Old Status')];
                                    $_table_data_fresh[] = ['name' => _l('New Status')];
                                    $_table_data_fresh[] = ['name' => _l('Current Status')];
                                    $_table_data_fresh[] = ['name' => _l('Old Assigned')];
                                    $_table_data_fresh[] = ['name' => _l('New Assigned')];
                                    $_table_data_fresh[] = ['name' => _l('Old Assigned Date')];
                                    $_table_data_fresh[] = ['name' => _l('New Assigned Date')];
                                    render_datatable($_table_data_fresh, 'leads-transfers-fresh', ['customizable-table', 'sticky-header']);
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php init_tail(); ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0/dist/chartjs-plugin-datalabels.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

<script>
// ======================== GLOBAL VARIABLES ========================
var staffListArray = <?= json_encode($staff ?? []) ?>;
var currentMainType = 'transfer';
var currentSelfType = 'transfer';
var currentFreshType = 'transfer';
var leadsTable, leadsTableSelf, leadsTableFresh;
var mainChart = null, selfChart = null, freshChart = null;
var currentActiveTab = 'not_reachable_leads';


function setStatus(status = "")
{
    if (status !== "" && status !== null && status !== undefined) {
        $('#status').selectpicker('val', String(status));
    } else {
        $('#status').selectpicker('val', '');
        $('#status').selectpicker('refresh');
    }
}

setStatus(20);
// ======================== HELPER FUNCTIONS ========================
function showNotification(message, type) {
    if (typeof alert_float === 'function') alert_float(type, message);
    else console.log(message);
}

function initDateRangePicker(selector, defaultVal = true) {
    let $input = $(selector);
    let start = moment().subtract(6, 'days');
    let end = moment();
    $input.daterangepicker({
        startDate: start, endDate: end, autoUpdateInput: false,
        locale: { cancelLabel: 'Clear', format: 'YYYY-MM-DD' },
        ranges: {
            'Today': [moment(), moment()],
            'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
            'Last 7 Days': [start, end],
            'Last 30 Days': [moment().subtract(29, 'days'), moment()],
            'This Month': [moment().startOf('month'), moment().endOf('month')],
            'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
        }
    });
    if (defaultVal) $input.val(start.format('YYYY-MM-DD') + ' to ' + end.format('YYYY-MM-DD'));
    $input.on('apply.daterangepicker', function(ev, picker) {
        if (picker.chosenLabel === 'Clear') { $(this).val('').trigger('change'); return; }
        $(this).val(picker.startDate.format('YYYY-MM-DD') + ' to ' + picker.endDate.format('YYYY-MM-DD')).trigger('change');
    });
    $input.on('cancel.daterangepicker', function() { $(this).val('').trigger('change'); });
}

function updateStaffDropdown(staffList) {
    let $staffSelect = $("#view_assigned");
    if ($staffSelect.hasClass('selectpicker')) $staffSelect.selectpicker('destroy');
    $staffSelect.empty();
    if (staffList && staffList.length) {
        staffList.forEach(s => $staffSelect.append('<option value="' + s.staffid + '">' + (s.firstname + ' ' + s.lastname).trim() + '</option>'));
    } else $staffSelect.append('<option value="">No staff found</option>');
    $staffSelect.selectpicker('refresh');
}

// ======================== MAIN TAB (NOT REACHABLE) ========================
var r = { department: "[name='department[]']", status: "[name='status[]']", assigned: "[name='view_assigned[]']", update_count: "[name='view_update_count[]']", transfer_date: "[name='transfer_date']", sources: "[name='view_sources[]']",c_status:"[name='c_status[]']" };

function refreshLeadTransferTable() {

    let tableSelector = '.table-leads-transfers';
    if (!$(tableSelector).length) return;
    if ($.fn.DataTable.isDataTable(tableSelector)) {
        leadsTable = $(tableSelector).DataTable();
        leadsTable.ajax.reload(null, false);
    } else {
        leadsTable = initDataTable(tableSelector, admin_url + 'dashboard/leads_transfers', 'undefined', 'undefined', r, [0, 'desc']);
        leadsTable.on('draw', function() { $('#recordCount').text(leadsTable.page.info().recordsDisplay); });
    }
    updateMainStatsAndChart();
}

function updateMainStatsAndChart() {
    let postData = {
        'department[]': $("[name='department[]']").val() || [],
        'view_status[]': $('[name="status[]"]').val() || [],
        'c_status[]': $('[name="c_status[]"]').val() || [],
        'view_sources[]': $('[name="view_sources[]"]').val() || [],
        'view_assigned[]': $('[name="view_assigned[]"]').val() || [],
        'view_update_count[]': $('[name="view_update_count[]"]').val() || [],
        'date_range': $('#date_range').val() || '',
        'csrf_token_name': csrfData.hash,
        'summary': 1,
        'leadType': currentMainType === 'transfer' ? 'Lead Transfer' : 'Lead Assignation'
    };

    $('#counselorRankingList').html('<div class="loading">Loading...</div>');
    $('#topSourcesList').html('<div class="loading">Loading...</div>');

    $.ajax({
        url: admin_url + 'dashboard/leads_transfers',
        type: 'POST',
        data: postData,
        traditional: true,
        success: function(response) {
            let parsed;
            try {
                parsed = typeof response === 'object' ? response : JSON.parse(response);
            } catch (e) {
                console.error('JSON parse error:', e);
                return showErrorState();
            }

            let apiData = parsed?.data || {};
            let data = Array.isArray(apiData.leads_summary) ? apiData.leads_summary : [];
            let topSources = Array.isArray(apiData.top_sources) ? apiData.top_sources : [];

            let total = data.reduce((sum, item) => sum + (Number(item?.counts) || 0), 0);

            $('#totalTransfers').text(total);
            $('#activeCounselors').text(data.length);
            $('#topCounselorName').text(data[0]?.staff_name || '—');
            $('#topCounselorLeadsCount').text((data[0]?.counts || 0) + ' leads');

            let top5 = data.slice(0, 5);
            let rankingHtml = top5.map((item, idx) => {
                let medal = ['🥇','🥈','🥉'][idx] || `#${idx + 1}`;
                let percent = top5[0]?.counts ? (item.counts / top5[0].counts * 100) : 0;
                return `
                <div class="ranking-item">
                    <div class="rank-info">
                        <span>${medal} ${item?.staff_name || 'Unknown'}</span>
                        <span>${item?.counts || 0} leads</span>
                    </div>
                    <div class="rank-bar">
                        <div class="bar-fill" style="width:${percent}%;"></div>
                    </div>
                </div>`;
            }).join('');
            $('#counselorRankingList').html(rankingHtml || '<div class="empty-state">No data</div>');

            let sourceHtml = topSources.map((s, idx) => {
                let medal = ['🥇','🥈','🥉'][idx] || `#${idx + 1}`;
                let percent = topSources[0]?.total ? (s.total / topSources[0].total * 100) : 0;
                return `
                <div class="ranking-item">
                    <div class="rank-info">
                        <span>${medal} ${s.source_name || 'Unknown'}</span>
                        <span>${s.total || 0}</span>
                    </div>
                    <div class="rank-bar">
                        <div class="bar-fill" style="width:${percent}%;"></div>
                    </div>
                </div>`;
            }).join('');
            $('#topSourcesList').html(sourceHtml || '<div class="empty-state">No sources</div>');

            if (window.mainChart) mainChart.destroy();
            let ctx = document.getElementById('counselorChart')?.getContext('2d');
            if (!ctx) return;

            mainChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: data.map(s => s.staff_name),
                    datasets: [{
                        data: data.map(s => Number(s.counts)),
                        backgroundColor: 'rgba(59,130,246,0.85)',
                        borderRadius: 8,
                        barPercentage: 0.6,
                        categoryPercentage: 0.7
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: { display: false },
                        tooltip: { callbacks: { label: ctx => `${ctx.raw} leads` } },
                        datalabels: {
                            anchor: 'end',
                            align: 'top',
                            offset: 4,
                            color: '#000',
                            font: { weight: 'bold', size: 12 },
                            formatter: value => value
                        }
                    },
                    scales: {
                        x: { grid: { display: false } },
                        y: { beginAtZero: true, grace: '25%', grid: { color: 'rgba(128,128,128,0.1)' } }
                    }
                },
                plugins: [ChartDataLabels]
            });
        },
        error: function(xhr) {
            console.error(xhr);
            showErrorState();
        }
    });

    function showErrorState() {
        $('#counselorRankingList').html('<div class="error-state">Error</div>');
        $('#topSourcesList').html('<div class="error-state">Error</div>');
    }
}

// ======================== SELF TAB ========================
var r_self = { department: "[name='department_self[]']", status: "[name='view_status_self[]']", assigned: "[name='view_assigned_self[]']", update_count: "[name='view_update_count_self[]']", transfer_date: "[name='transfer_date_self']", self_table: 1, source: "[name='view_sources_self[]']",c_status:"[name='view_status_self[]']" };

function refreshSelfTransferTable() {
    let tableSelector = '.table-leads-transfers-self';
    if (!$(tableSelector).length) return;
    if ($.fn.DataTable.isDataTable(tableSelector)) {
        leadsTableSelf = $(tableSelector).DataTable();
        leadsTableSelf.ajax.reload(null, false);
    } else {
        leadsTableSelf = initDataTable(tableSelector, admin_url + 'dashboard/leads_transfers?tbl=self_table', 'undefined', 'undefined', r_self, [0, 'desc']);
        leadsTableSelf.on('draw', function() { $('#recordCount_self').text(leadsTableSelf.page.info().recordsDisplay); });
    }
    updateSelfStatsAndChart();
}

function updateSelfStatsAndChart() {
    let postData = {
        'department_self[]': $("[name='department_self[]']").val() || [],
        'c_status[]': $("[name='view_status_self[]']").val() || [],
        'view_status_self[]': $('[name="view_status_self[]"]').val() || [],
        'view_sources_self[]': $('[name="view_sources_self[]"]').val() || [],
        'view_assigned_self[]': $('[name="view_assigned_self[]"]').val() || [],
        'view_update_count_self[]': $('[name="view_update_count_self[]"]').val() || [],
        'date_range_self': $('#date_range_self').val() || '',
        'csrf_token_name': csrfData.hash,
        'summary_self': 1,
        'leadType': currentSelfType === 'transfer' ? 'Lead Transfer' : 'Lead Assignation'
    };

    $('#counselorRankingList_self').html('<div class="loading">Loading...</div>');
    $('#topSourcesList_self').html('<div class="loading">Loading...</div>');

    $.ajax({
        url: admin_url + 'dashboard/leads_transfers',
        type: 'POST',
        data: postData,
        traditional: true,
        success: function(response) {
            let parsed;
            try {
                parsed = typeof response === 'object' ? response : JSON.parse(response);
            } catch (e) {
                console.error('JSON parse error:', e);
                return showError();
            }

            let apiData = parsed?.data || {};
            let data = Array.isArray(apiData.leads_summary) ? apiData.leads_summary : [];
            let topStaff = Array.isArray(apiData.top_lead) ? apiData.top_lead : [];
            let topSources = Array.isArray(apiData.top_sources) ? apiData.top_sources : [];

            let total = data.reduce((sum, item) => sum + (Number(item?.counts) || 0), 0);

            $('#totalTransfers_self').text(total);
            $('#activeCounselors_self').text(data.length);
            $('#topCounselorName_self').text(data[0]?.staff_name || '—');
            $('#topCounselorLeadsCount_self').text((data[0]?.counts || 0) + ' leads');

            let top5 = topStaff.length ? topStaff : data.slice(0, 5);
            let rankingHtml = top5.map((item, idx) => {
                let medal = ['🥇','🥈','🥉'][idx] || `#${idx + 1}`;
                let percent = (top5[0]?.counts || 0) > 0 ? (item.counts / top5[0].counts * 100) : 0;
                return `
                    <div class="ranking-item">
                        <div class="rank-info">
                            <span>${medal} ${item?.staff_name || 'Unknown'}</span>
                            <span>${item?.counts || 0} leads</span>
                        </div>
                        <div class="rank-bar">
                            <div class="bar-fill" style="width:${percent}%;"></div>
                        </div>
                    </div>
                `;
            }).join('');
            $('#counselorRankingList_self').html(rankingHtml || '<div class="empty-state">No data available</div>');

            let sourceHtml = topSources.map((s, idx) => {
                let medal = ['🥇','🥈','🥉'][idx] || `#${idx + 1}`;
                let percent = (topSources[0]?.total || 0) > 0 ? (s.total / topSources[0].total * 100) : 0;
                return `
                    <div class="ranking-item">
                        <div class="rank-info">
                            <span>${medal} ${s.source_name || 'Unknown'}</span>
                            <span>${s.total || 0}</span>
                        </div>
                        <div class="rank-bar">
                            <div class="bar-fill" style="width:${percent}%;"></div>
                        </div>
                    </div>
                `;
            }).join('');
            $('#topSourcesList_self').html(sourceHtml || '<div class="empty-state">No sources</div>');

            if (selfChart) selfChart.destroy();
            let canvas = document.getElementById('counselorChart_self');
            if (!canvas) return;
            let ctx = canvas.getContext('2d');

            let labels = data.map(s => s?.staff_name?.length > 20 ? s.staff_name.substring(0, 18) + '..' : (s?.staff_name || 'Unknown'));
            let values = data.map(s => Number(s?.counts) || 0);

            selfChart = new Chart(ctx, {
                type: 'bar',
                data: { labels: labels, datasets: [{ data: values, backgroundColor: 'rgba(59, 130, 246, 0.85)', borderRadius: 8, barPercentage: 0.6, categoryPercentage: 0.7 }] },
                options: {
                    responsive: true, maintainAspectRatio: true,
                    plugins: {
                        legend: { display: false },
                        tooltip: { callbacks: { label: ctx => `${ctx.raw} leads` } },
                        datalabels: { anchor: 'end', align: 'top', offset: 4, color: '#000', font: { weight: 'bold', size: 12 }, formatter: value => value }
                    },
                    scales: { x: { grid: { display: false } }, y: { beginAtZero: true, grace: '25%', grid: { color: 'rgba(128,128,128,0.1)' } } }
                },
                plugins: [ChartDataLabels]
            });
        },
        error: function(xhr) {
            console.error('API error:', xhr);
            showError();
        }
    });

    function showError() {
        $('#counselorRankingList_self').html('<div class="error-state">Failed to load data</div>');
        $('#topSourcesList_self').html('<div class="error-state">Failed to load</div>');
        $('#totalTransfers_self').text('0');
        $('#activeCounselors_self').text('0');
        $('#topCounselorName_self').text('—');
        $('#topCounselorLeadsCount_self').text('0 leads');
        if (selfChart) selfChart.destroy();
    }
}

// ======================== FRESH TAB ========================
var r_fresh = { department: "[name='department_fresh[]']", status: "[name='view_status_fresh[]']", assigned: "[name='view_assigned_fresh[]']", update_count: "[name='view_update_count_fresh[]']", transfer_date: "[name='transfer_date_fresh']", sources: "[name='view_sources_fresh[]']",c_status_fresh:"[name='c_status_fresh[]']"};

function refreshFreshTransferTable() {
    let tableSelector = '.table-leads-transfers-fresh';
    if (!$(tableSelector).length) return;
    if ($.fn.DataTable.isDataTable(tableSelector)) {
        leadsTableFresh = $(tableSelector).DataTable();
        leadsTableFresh.ajax.reload(null, false);
    } else {
        leadsTableFresh = initDataTable(tableSelector, admin_url + 'dashboard/leads_transfers?tbl=fresh_table', 'undefined', 'undefined', r_fresh, [0, 'desc']);
        leadsTableFresh.on('draw', function() { $('#recordCount_fresh').text(leadsTableFresh.page.info().recordsDisplay); });
    }
    updateFreshStatsAndChart();
}

function updateFreshStatsAndChart() {
    let postData = {
        'department_fresh[]': $("[name='department_fresh[]']").val() || [],
        'view_status_fresh[]': $('[name="view_status_fresh[]"]').val() || [],
        'c_status_fresh[]': $('[name="c_status_fresh[]"]').val() || [],
        'view_sources_fresh[]': $('[name="view_sources_fresh[]"]').val() || [],
        'view_assigned_fresh[]': $('[name="view_assigned_fresh[]"]').val() || [],
        'view_update_count_fresh[]': $('[name="view_update_count_fresh[]"]').val() || [],
        'date_range_fresh': $('#date_range_fresh').val() || '',
        'csrf_token_name': csrfData.hash,
        'summary_fresh': 1,
        'leadType': currentFreshType === 'transfer' ? 'Lead Transfer' : 'Lead Assignation'
    };

    $('#counselorRankingList_fresh').html('<div class="loading">Loading...</div>');
    $('#topSourcesList_fresh').html('<div class="loading">Loading...</div>');

    $.ajax({
        url: admin_url + 'dashboard/leads_transfers',
        type: 'POST',
        data: postData,
        traditional: true,
        success: function(response) {
            let parsed;
            try {
                parsed = typeof response === 'object' ? response : JSON.parse(response);
            } catch (e) {
                console.error('JSON parse error:', e);
                return showErrorState();
            }

            let apiData = parsed?.data || {};
            let data = Array.isArray(apiData.leads_summary) ? apiData.leads_summary : [];
            let topSources = Array.isArray(apiData.top_sources) ? apiData.top_sources : [];

            let total = data.reduce((sum, item) => sum + (Number(item?.counts) || 0), 0);

            $('#totalTransfers_fresh').text(total);
            $('#activeCounselors_fresh').text(data.length);
            $('#topCounselorName_fresh').text(data[0]?.staff_name || '—');
            $('#topCounselorLeadsCount_fresh').text((data[0]?.counts || 0) + ' leads');

            let top5 = data.slice(0, 5);
            let rankingHtml = top5.map((item, idx) => {
                let medal = ['🥇','🥈','🥉'][idx] || `#${idx + 1}`;
                let percent = top5[0]?.counts ? (item.counts / top5[0].counts * 100) : 0;
                return `
                <div class="ranking-item">
                    <div class="rank-info">
                        <span>${medal} ${item?.staff_name || 'Unknown'}</span>
                        <span>${item?.counts || 0} leads</span>
                    </div>
                    <div class="rank-bar">
                        <div class="bar-fill" style="width:${percent}%;"></div>
                    </div>
                </div>`;
            }).join('');
            $('#counselorRankingList_fresh').html(rankingHtml || '<div class="empty-state">No data</div>');

            let sourceHtml = topSources.map((s, idx) => {
                let medal = ['🥇','🥈','🥉'][idx] || `#${idx + 1}`;
                let percent = topSources[0]?.total ? (s.total / topSources[0].total * 100) : 0;
                return `
                <div class="ranking-item">
                    <div class="rank-info">
                        <span>${medal} ${s.source_name || 'Unknown'}</span>
                        <span>${s.total || 0}</span>
                    </div>
                    <div class="rank-bar">
                        <div class="bar-fill" style="width:${percent}%;"></div>
                    </div>
                </div>`;
            }).join('');
            $('#topSourcesList_fresh').html(sourceHtml || '<div class="empty-state">No sources</div>');

            if (window.freshChart) freshChart.destroy();
            let ctx = document.getElementById('counselorChart_fresh')?.getContext('2d');
            if (!ctx) return;

            freshChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: data.map(s => s.staff_name),
                    datasets: [{
                        data: data.map(s => Number(s.counts)),
                        backgroundColor: 'rgba(59,130,246,0.85)',
                        borderRadius: 8,
                        barPercentage: 0.6,
                        categoryPercentage: 0.7
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: { display: false },
                        tooltip: { callbacks: { label: ctx => `${ctx.raw} leads` } },
                        datalabels: {
                            anchor: 'end',
                            align: 'top',
                            offset: 4,
                            color: '#000',
                            font: { weight: 'bold', size: 12 },
                            formatter: value => value
                        }
                    },
                    scales: {
                        x: { grid: { display: false } },
                        y: { beginAtZero: true, grace: '25%', grid: { color: 'rgba(128,128,128,0.1)' } }
                    }
                },
                plugins: [ChartDataLabels]
            });
        },
        error: function(xhr) {
            console.error(xhr);
            showErrorState();
        }
    });

    function showErrorState() {
        $('#counselorRankingList_fresh').html('<div class="error-state">Error</div>');
        $('#topSourcesList_fresh').html('<div class="error-state">Error</div>');
    }
}

function applyFiltersFresh() { 
    if (leadsTableFresh) leadsTableFresh.ajax.reload(); 
    updateFreshStatsAndChart(); 
    showNotification('Filters applied', 'success'); 
}

function resetFiltersFresh() {
    $('[name="department_fresh[]"], [name="view_sources_fresh[]"], [name="view_assigned_fresh[]"], [name="view_update_count_fresh[]"]').val('').trigger('change');
    $('#date_range_fresh').val('');
    if ($('#date_range_fresh').data('daterangepicker')) { 
        $('#date_range_fresh').data('daterangepicker').setStartDate(moment().subtract(6, 'days')); 
        $('#date_range_fresh').data('daterangepicker').setEndDate(moment()); 
    }
    $('.selectpicker').selectpicker('refresh');
    if (leadsTableFresh) leadsTableFresh.ajax.reload();
    updateFreshStatsAndChart();
    showNotification('Filters reset', 'info');
}

// ======================== FILTER & TAB HANDLERS ========================
function applyFilters() { if (leadsTable) leadsTable.ajax.reload(); updateMainStatsAndChart(); showNotification('Filters applied', 'success'); }
function applyFiltersSelf() { if (leadsTableSelf) leadsTableSelf.ajax.reload(); updateSelfStatsAndChart(); showNotification('Filters applied', 'success'); }

function resetFilters() {
    $('[name="department[]"],[name="view_sources[]"],[name="view_assigned[]"],[name="view_update_count[]"],[name="view_status_self[]"],[name="view_assigned_self[]"],[name="view_sources_self[]"],[name="view_update_count_self[]"]').val('').trigger('change');
    $('#date_range, #date_range_self').val('');
    if ($('#date_range').data('daterangepicker')) { $('#date_range').data('daterangepicker').setStartDate(moment().subtract(6, 'days')); $('#date_range').data('daterangepicker').setEndDate(moment()); }
    if ($('#date_range_self').data('daterangepicker')) { $('#date_range_self').data('daterangepicker').setStartDate(moment().subtract(6, 'days')); $('#date_range_self').data('daterangepicker').setEndDate(moment()); }
    $('.selectpicker').selectpicker('refresh');
    if (leadsTable) leadsTable.ajax.reload();
    if (leadsTableSelf) leadsTableSelf.ajax.reload();
    updateMainStatsAndChart();
    updateSelfStatsAndChart();
    showNotification('Filters reset', 'info');
}

function refreshCurrentTabData() {
    if (currentActiveTab === 'not_reachable_leads') { 
        if (leadsTable) leadsTable.ajax.reload(); 
        updateMainStatsAndChart(); 
    }
    else if (currentActiveTab === 'self_transfer_leads') { 
        if (leadsTableSelf) leadsTableSelf.ajax.reload(); 
        updateSelfStatsAndChart(); 
    }
    else if (currentActiveTab === 'fresh_transfer_leads') { 
        if (leadsTableFresh) leadsTableFresh.ajax.reload(); 
        updateFreshStatsAndChart(); 
    }
    showNotification('Data refreshed', 'success');
}

// ======================== DOCUMENT READY ========================
$(document).ready(function() {
    initDateRangePicker('#date_range', true);
    initDateRangePicker('#date_range_self', true);
    initDateRangePicker('#date_range_fresh', true);
    $('.selectpicker').selectpicker({ iconBase: 'fa', tickIcon: 'fa-check', width: '100%' });
    
    $("#department").change(function() {
        let deptId = $(this).val();
        let filteredStaff = deptId > 0 ? staffListArray.filter(s => s.department == deptId) : staffListArray;
        updateStaffDropdown(filteredStaff);
    });
    
    $('.tab').on('click', function() {
        $('.tab').removeClass('active');
        $(this).addClass('active');
        $('.tab-content').removeClass('active').addClass('hide');
        let tabId = $(this).data('tab');
        $('#' + tabId).removeClass('hide').addClass('active');
        currentActiveTab = tabId;
        
        if (tabId === 'not_reachable_leads') { 
            if (!leadsTable) refreshLeadTransferTable(); 
            else leadsTable.ajax.reload(); 
            updateMainStatsAndChart(); 
        }
        else if (tabId === 'self_transfer_leads') { 
            if (!leadsTableSelf) refreshSelfTransferTable(); 
            else leadsTableSelf.ajax.reload(); 
            updateSelfStatsAndChart(); 
        }
        else if (tabId === 'fresh_transfer_leads') { 
            if (!leadsTableFresh) refreshFreshTransferTable(); 
            else leadsTableFresh.ajax.reload(); 
            updateFreshStatsAndChart(); 
        }
    });
    
    $('[data-tab="main"]').on('click', function() {
        $('[data-tab="main"]').removeClass('active');
        $(this).addClass('active');
        currentMainType = $(this).data('type');
        updateMainStatsAndChart();
        if (leadsTable) leadsTable.ajax.reload();
    });
    
    $('[data-tab="self"]').on('click', function() {
        $('[data-tab="self"]').removeClass('active');
        $(this).addClass('active');
        currentSelfType = $(this).data('type');
        updateSelfStatsAndChart();
        if (leadsTableSelf) leadsTableSelf.ajax.reload();
    });
    
    $('[data-tab="fresh"]').on('click', function() {
        $('[data-tab="fresh"]').removeClass('active');
        $(this).addClass('active');
        currentFreshType = $(this).data('type');
        updateFreshStatsAndChart();
        if (leadsTableFresh) leadsTableFresh.ajax.reload();
    });
    
    refreshLeadTransferTable();
    refreshSelfTransferTable();
    refreshFreshTransferTable();
});
</script>