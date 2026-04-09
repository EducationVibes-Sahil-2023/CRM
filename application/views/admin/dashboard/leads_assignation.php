<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>

<?php 
$this->load->helper('leads');
$select_staff_office_region = staff_location_region();

?>

<style>
/* Modern UI Design */
:root {
    --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    --secondary-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    --success-gradient: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    --warning-gradient: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
    --card-shadow: 0 10px 40px rgba(0,0,0,0.08);
    --hover-shadow: 0 20px 60px rgba(0,0,0,0.12);
}

/* Modern Filters */
.modern-filters {
    background: white;
    border-radius: 20px;
    padding: 25px;
    margin-bottom: 30px;
    box-shadow: var(--card-shadow);
}

.filters-header-modern {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 2px solid #f0f0f0;
}

.header-title {
    display: flex;
    align-items: center;
    gap: 10px;
}

.header-title i {
    font-size: 24px;
    color: #667eea;
}

.header-title h5 {
    margin: 0;
    font-size: 18px;
    font-weight: 600;
    color: #2c3e50;
}

.btn-reset {
    background: #f8f9fa;
    border: none;
    padding: 8px 16px;
    border-radius: 10px;
    color: #6c757d;
    transition: all 0.3s;
    cursor: pointer;
}

.btn-reset:hover {
    background: #e9ecef;
    transform: translateY(-2px);
}

.filters-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 20px;
    margin-bottom: 20px;
}

.filter-item label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: #495057;
    font-size: 13px;
}

.filter-item label i {
    margin-right: 5px;
    color: #667eea;
}

.filters-actions {
    display: flex;
    gap: 12px;
    justify-content: flex-end;
    padding-top: 15px;
    border-top: 1px solid #f0f0f0;
}

.btn-apply, .btn-clear {
    padding: 10px 24px;
    border: none;
    border-radius: 10px;
    font-weight: 600;
    transition: all 0.3s;
    cursor: pointer;
}

.btn-apply {
    background: var(--primary-gradient);
    color: white;
}

.btn-apply:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(102,126,234,0.4);
}

.btn-clear {
    background: #f8f9fa;
    color: #6c757d;
}

.btn-clear:hover {
    background: #e9ecef;
    transform: translateY(-2px);
}

/* Stats Dashboard */
.stats-dashboard {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: 20px;
    margin-bottom: 40px;
}

.stat-card {
    background: white;
    border-radius: 20px;
    padding: 20px;
    display: flex;
    align-items: center;
    gap: 15px;
    box-shadow: var(--card-shadow);
    transition: all 0.3s;
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: var(--hover-shadow);
}

.stat-icon {
    width: 60px;
    height: 60px;
    border-radius: 15px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 28px;
    color: white;
}

.stat-icon.blue { background: linear-gradient(135deg, #667eea, #764ba2); }
.stat-icon.purple { background: linear-gradient(135deg, #f093fb, #f5576c); }
.stat-icon.green { background: linear-gradient(135deg, #4facfe, #00f2fe); }
.stat-icon.orange { background: linear-gradient(135deg, #fa709a, #fee140); }

.stat-info h3 {
    margin: 0;
    font-size: 28px;
    font-weight: 700;
    color: #2c3e50;
}

.stat-info p {
    margin: 5px 0 0;
    color: #6c757d;
    font-size: 13px;
}

/* Regions Container */
.regions-container {
    margin-top: 30px;
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
}

.title-section {
    display: flex;
    align-items: center;
    gap: 10px;
}

.title-section i {
    font-size: 24px;
    color: #667eea;
}

.title-section h4 {
    margin: 0;
    font-size: 20px;
    font-weight: 600;
    color: #2c3e50;
}

.view-options {
    display: flex;
    gap: 8px;
}

.view-btn {
    padding: 8px 16px;
    border: 1px solid #e0e0e0;
    background: white;
    border-radius: 10px;
    cursor: pointer;
    transition: all 0.3s;
}

.view-btn.active {
    background: var(--primary-gradient);
    color: white;
    border-color: transparent;
}

/* Loader Styles */
.loader-wrapper {
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 400px;
}

.loader {
    text-align: center;
}

.loader-spinner {
    width: 50px;
    height: 50px;
    border: 3px solid #f3f3f3;
    border-top: 3px solid #667eea;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin: 0 auto 15px;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.loader p {
    color: #667eea;
    font-size: 14px;
    margin: 0;
}

/* Regions Grid */
.regions-grid,.source-grid {
    display: grid !important;
    grid-template-columns: repeat(auto-fill, minmax(420px, 1fr));
    gap: 25px;
}

.regions-grid.list-view,.source-grid.list-view {
    display: block !important;
}

.regions-grid.list-view .region-card-modern {
    margin-bottom: 20px;
}

.region-card-modern {
    background: white;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: var(--card-shadow);
    transition: all 0.3s;
    animation: fadeInUp 0.4s ease-out;
     margin-bottom: 20px;
   ;
}

.region-card-modern:hover {
    transform: translateY(-5px);
    box-shadow: var(--hover-shadow);
}

.region-header-modern {
    background: var(--primary-gradient);
    padding: 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    color: white;
}

.region-info {
    display: flex;
    align-items: center;
    gap: 12px;
}

.region-icon {
    width: 45px;
    height: 45px;
    background: rgba(255,255,255,0.2);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 22px;
}

.region-info h3 {
    margin: 0;
    font-size: 18px;
    font-weight: 600;
}

.region-stats {
    display: flex;
    gap: 15px;
    margin-top: 5px;
    font-size: 12px;
    opacity: 0.9;
}

.region-stats i {
    margin-right: 4px;
}

.region-badge-modern {
    background: rgba(255,255,255,0.2);
    padding: 8px 12px;
    border-radius: 30px;
    font-weight: bold;
}

.badge-count {
    font-size: 18px;
    font-weight: bold;
}

.region-content {
    padding: 20px;
    max-height: 500px;
    overflow-y: auto;
}

.staff-grid-modern {
    display: grid;
    gap: 15px;
}

.staff-card-modern {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 12px;
    transition: all 0.3s;
}

.staff-card-modern:hover {
    background: #e9ecef;
    transform: translateX(5px);
}

.staff-avatar {
    width: 45px;
    height: 45px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: bold;
    font-size: 16px;
    flex-shrink: 0;
}

.staff-details {
    flex: 1;
}

.staff-name {
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 8px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 8px;
}

.lead-count-badge {
    background: #e0e0e0;
    padding: 3px 10px;
    border-radius: 20px;
    font-size: 11px;
    color: #666;
    font-weight: normal;
}

.source-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-top: 8px;
}

.source-tag {
    background: white;
    padding: 4px 10px;
    border-radius: 15px;
    font-size: 11px;
    color: #667eea;
    border: 1px solid #ddd;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    transition: all 0.2s;
}

.source-tag:hover {
    background: #667eea;
    color: white;
    border-color: #667eea;
    transform: scale(1.05);
}

.source-tag i {
    font-size: 10px;
}

.no-sources {
    font-size: 11px;
    color: #999;
    margin-top: 5px;
}

.empty-state {
    text-align: center;
    padding: 40px;
    color: #adb5bd;
}

.empty-state i {
    font-size: 48px;
    margin-bottom: 10px;
}

.region-footer {
      background: var(--primary-gradient);
    /*background: #f8f9fa;*/
    padding: 12px 20px;
    font-size: 12px;
    /*color: #6c757d;*/
    color: white;
    border-top: 1px solid #f0f0f0;
}

.region-footer i {
    margin-right: 5px;
    color: #667eea;
}

hr {
    margin: 10px 0;
    border: 0;
    border-top: 1px solid #b9c0c6;
}

/* Responsive */
@media (max-width: 768px) {
    .regions-grid {
        grid-template-columns: 1fr;
    }
    
    .stats-dashboard {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .filters-grid {
        grid-template-columns: 1fr;
    }
    
    .section-header {
        flex-direction: column;
        gap: 15px;
        align-items: flex-start;
    }
    
    .staff-name {
        flex-direction: column;
        align-items: flex-start;
    }
}

/* Scrollbar Styling */
.region-content::-webkit-scrollbar {
    width: 6px;
}

.region-content::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
}

.region-content::-webkit-scrollbar-thumb {
    background: #667eea;
    border-radius: 10px;
}

/* Animations */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.staff-card-modern {
    animation: fadeInUp 0.3s ease-out;
}

/* Loading Overlay */
.loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    z-index: 9999;
    display: none;
    justify-content: center;
    align-items: center;
}

.loading-spinner {
    width: 60px;
    height: 60px;
    border: 4px solid #f3f3f3;
    border-top: 4px solid #667eea;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    background: white;
    padding: 10px;
    border-radius: 50%;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}


.tabs {
    display: flex;
    gap: 10px;
    border-bottom: 2px solid #eee;
    margin-bottom: 15px;
}

.tab {
   padding: 15px 15px;
    cursor: pointer;
    font-size: 15px;
    border-radius: 8px 8px 0 0;
    background: #f5f5f5;
    color: #555;
    transition: all 0.3s ease;
}

.tab:hover {
    background: #eaeaea;
}

.tab.active {
    background: var(--primary-gradient);
    color: #fff;
    font-weight: 500;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}



/*.tab-content.active {*/
/*    display: contents;*/
/*}*/

</style>

<div id="wrapper">
    <div class="screen-options-area"></div>

    <div class="content">
        <div class="row">
            <div class="col-md-12 mtop15">
                <div class="panel_s">
                    <div class="panel-body">
                        
                        <!-- Modern Filters Section -->
                        <div class="modern-filters">
                            <div class="filters-header-modern">
                                <div class="header-title">
                                    <i class="fa fa-filter"></i>
                                    <h5>Filter Options</h5>
                                </div>
                                <button type="button" class="btn-reset" onclick="resetFilters()">
                                    <i class="fa fa-refresh"></i> Reset All
                                </button>
                            </div>
                            
                            <div class="filters-grid">
                                  <?php if (has_permission('leads', '', 'view')) { ?>
                                       <div class="filter-item">
                                    <label><i class="fa fa-user-circle"></i> Department</label>
                                    <?php echo render_select('department[]', $departments,array('id','name') , '', '', array(
                                        'data-width' => '100%', 
                                        'data-none-selected-text' => 'Select department', 
                                        'multiple' => true, 
                                        'data-actions-box' => true,
                                        'data-live-search' => true
                                    ), array(), 'no-mbot', '', false, 'department'); ?>
                                </div>
                                <div class="filter-item">
                                    <label><i class="fa fa-user-circle"></i> Assignation</label>
                                    <?php echo render_select('view_assigned[]', $staff, array('staffid', array('firstname', 'lastname')), '', '', array(
                                        'data-width' => '100%', 
                                        'data-none-selected-text' => 'Select Staff', 
                                        'multiple' => true, 
                                        'data-actions-box' => true,
                                        'data-live-search' => true
                                    ), array(), 'no-mbot', '', false, 'view_assigned'); ?>
                                </div>
                                  <?php } ?>
                                
                                <div class="filter-item">
                                    <label><i class="fa fa-tags"></i> Status</label>
                                    <?php echo render_select('view_status[]', $lead_statuses, array('id', 'name'), '', '', array(
                                        'data-width' => '100%', 
                                        'data-none-selected-text' => 'All Statuses', 
                                        'multiple' => true, 
                                        'data-actions-box' => true,
                                        'data-live-search' => true
                                    ), array(), 'no-mbot', '', false, 'view_status'); ?>
                                </div>
                                
                                <div class="filter-item">
                                    <label><i class="fa fa-tags"></i> Sources</label>
                                    <?php echo render_select('view_sources[]', $lead_sources, array('id', 'name'), '', '', array(
                                        'data-width' => '100%', 
                                        'data-none-selected-text' => 'All Sources', 
                                        'multiple' => true, 
                                        'data-actions-box' => true,
                                        'data-live-search' => true
                                    ), array(), 'no-mbot', '', false, 'view_sources'); ?>
                                </div>
                                
                                 <div class="filter-item hide_show_utm hide">
                                      <label><i class="fa fa-tags"></i> Campaign Name</label>
                                <?php
                                echo '<div id="leads-filter-campaign">';
                                echo render_select('view_campaign[]', [], "", '', '', array('data-width' => '100%', 'data-none-selected-text' => 'Campaign Name', 'multiple' => true, 'data-actions-box' => true), array(), 'no-mbot', '', false, "view_campaign");
                                echo '</div>';
                                ?>
                            </div>
                            <div class="filter-item hide_show_utm hide">
                                 <label><i class="fa fa-tags"></i> Adsset Name</label>
                                <?php
                                echo '<div id="leads-filter-adsset">';
                                echo render_select('view_adsset[]', [], "", '', '', array('data-width' => '100%', 'data-none-selected-text' => "Ads Set Name", 'multiple' => true, 'data-actions-box' => true), array(), 'no-mbot', '', false, "view_adsset");
                                echo '</div>';
                                ?>
                            </div>
                            <div class="filter-item hide_show_utm hide">
                                 <label><i class="fa fa-tags"></i> Ads Name</label>
                                <?php
                                echo '<div id="leads-filter-ads">';
                                echo render_select('view_ads[]', [], "", '', '', array('data-width' => '100%', 'data-none-selected-text' => "Ads Name", 'multiple' => true, 'data-actions-box' => true), array(), 'no-mbot', '', false, "view_ads");
                                echo '</div>';
                                ?>
                            </div>
                            
                             <div class="filter-item hide_show_utm hide">
                                  <label><i class="fa fa-tags"></i> Forms Name</label>
                                <?php
                                echo '<div id="leads-filter-form">';
                                echo render_select('view_form[]', [], "", '', '', array('data-width' => '100%', 'data-none-selected-text' => "Form Name", 'multiple' => true, 'data-actions-box' => true), array(), 'no-mbot', '', false, "view_form");
                                echo '</div>';
                                ?>
                            </div>
                                
                                
                                 <div class="filter-item">
        <label><i class="fa fa-calendar-plus-o"></i> Create Date</label>
        <input type="text" name="create_date_range" id="create_date_range" class="form-control date-range-picker" 
               placeholder="Select create date range" autocomplete="off">
    </div>
    <div class="filter-item">
        <label><i class="fa fa-calendar-minus-o"></i> Assignation Date</label>
        <input type="text" name="assignation_date_range" id="assignation_date_range" class="form-control date-range-picker" 
               placeholder="Select assignation date range" autocomplete="off">
    </div>
    
                                <div class="filter-item hide">
                                    <label><i class="fa fa-chart-line"></i> Update Count</label>
                                    <?php
                                    $update_counts = [
                                        ['id' => 0, 'name' => '0 - No Updates'],
                                        ['id' => 1, 'name' => '1 - One Update'],
                                        ['id' => 2, 'name' => '2 - Two Updates'],
                                        ['id' => 3, 'name' => '3 - Three Updates'],
                                        ['id' => 4, 'name' => '4+ - Four or More'],
                                    ];
                                    echo render_select('view_update_count[]', $update_counts, array('id', 'name'), '', '', array(
                                        'data-width' => '100%', 
                                        'data-none-selected-text' => 'All Counts', 
                                        'multiple' => true, 
                                        'data-actions-box' => true
                                    ), array(), 'no-mbot', '', false, 'view_update_count');
                                    ?>
                                </div>
                                
                                
                            </div>
                            
                            <!-- Date Filters - Row 1 -->
<div class="filters-grid">
   
    
    <!--<div class="filter-item">-->
    <!--    <label><i class="fa fa-calendar-check-o"></i> Updated Date</label>-->
    <!--    <input type="text" name="updated_date_range" id="updated_date_range" class="form-control date-range-picker" -->
    <!--           placeholder="Select updated date range" autocomplete="off">-->
    <!--</div>-->

    <!--<div class="filter-item">-->
    <!--    <label><i class="fa fa-calendar"></i> Follow Update Date</label>-->
    <!--    <input type="text" name="follow_update_date_range" id="follow_update_date_range" class="form-control date-range-picker" -->
    <!--           placeholder="Select follow update date range" autocomplete="off">-->
    <!--</div>-->
    
    
</div>
                            
                            <div class="filters-actions">
                                <button class="btn-apply" onclick="applyFilters()">
                                    <i class="fa fa-search"></i> Apply Filters
                                </button>
                                <button class="btn-clear" onclick="clearFilters()">
                                    <i class="fa fa-eraser"></i> Clear
                                </button>
                            </div>
                        </div>

                        <!-- Stats Dashboard - Will be updated via AJAX -->
                        <div id="stats-dashboard" class="stats-dashboard">
                            <div class="stat-card">
                                <div class="stat-icon blue">
                                    <i class="fa fa-users"></i>
                                </div>
                                <div class="stat-info">
                                    <h3 class="stat-value">--</h3>
                                    <p>Total Regions</p>
                                </div>
                            </div>
                            <div class="stat-card">
                                <div class="stat-icon purple">
                                    <i class="fa fa-user-md"></i>
                                </div>
                                <div class="stat-info">
                                    <h3 class="stat-value">--</h3>
                                    <p>Total Counselors</p>
                                </div>
                            </div>
                            <div class="stat-card">
                                <div class="stat-icon green">
                                    <i class="fa fa-line-chart"></i>
                                </div>
                                <div class="stat-info">
                                    <h3 class="stat-value">--</h3>
                                    <p>Total Leads</p>
                                </div>
                            </div>
                            <div class="stat-card">
                                <div class="stat-icon orange">
                                    <i class="fa fa-trophy"></i>
                                </div>
                                <div class="stat-info">
                                    <h3 class="stat-value">--</h3>
                                    <p>Avg Leads/Counselor</p>
                                </div>
                            </div>
                        </div>


<!--<div class="tabs">-->
<!--    <div class="tab active" onclick="openTab('tab1')">All Data</div>-->
<!--    <div class="tab" onclick="openTab('tab2')">Counsellor by Region</div>-->
<!--    <div class="tab" onclick="openTab('tab3')">Source by Counsellor</div>-->
<!--</div>-->


<div class="tabs">
    <!--<div class="tab active" data-tab="tab1">All Data</div>-->
    <div onclick="setTimeout(loadData, 100);" class="tab active" data-tab="Counsellor_by_Region">Counsellor by Region</div>
    <div  onclick="setTimeout(loadData_source, 100);"class="tab " data-tab="Source_by_Counsellor">Counsellor by Source</div>
</div>


            <div id="loader-wrapper" class="loader-wrapper">
                                <div class="loader">
                                    <div class="loader-spinner"></div>
                                    <p>Loading data...</p>
                                </div>
                            </div>
                            
                        <!-- Counselors by Region Section -->
                        <div  id="Counsellor_by_Region" class="regions-container tab-content active">
                            <div class="section-header">
                                <div class="title-section">
                                    <i class="fa fa-map-marker-alt"></i>
                                    <h4>Counselors by Region</h4>
                                </div>
                                <div class="view-options">
                                    <button class="view-btn grid-view" onclick="setView('grid')">
                                        <i class="fa fa-th"></i> Grid
                                    </button>
                                    <button class="view-btn list-view active" onclick="setView('list')">
                                        <i class="fa fa-list"></i> List
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Loader -->
                
                            
                            <!-- Regions Grid Container -->
                            <div id="regions-grid" class="regions-grid list-view"></div>
                        </div>
                        
                        <div id="Source_by_Counsellor" class="source-container tab-content hide">
                              <div class="section-header">
                                <div class="title-section">
                                    <i class="fa fa-map-marker-alt"></i>
                                    <h4>Source by Leads</h4>
                                </div>
                                <div class="view-options">
                                    <button class="view-btn grid-view" onclick="setView('grid')">
                                        <i class="fa fa-th"></i> Grid
                                    </button>
                                    <button class="view-btn list-view active" onclick="setView('list')">
                                        <i class="fa fa-list"></i> List
                                    </button>
                                </div>
                            </div>
                            
                             <!-- Loader -->
                            <!--<div id="loader-wrapper" class="loader-wrapper">-->
                            <!--    <div class="loader">-->
                            <!--        <div class="loader-spinner"></div>-->
                            <!--        <p>Loading data...</p>-->
                            <!--    </div>-->
                            <!--</div>-->
                            
                            <!-- Regions Grid Container -->
                            <div id="source-grid" class="source-grid list-view"></div>
                        </div>
                        
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php init_tail(); ?>

<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<!-- <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script> -->
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

<script>


$('.tab').on('click', function() {

    // Remove active from all tabs
    $('.tab').removeClass('active');

    // Add active to the clicked tab
    $(this).addClass('active');

    // Hide all tab contents
    $('.tab-content').removeClass('active').addClass('hide');

    // Show the selected tab content
    const tabId = $(this).data('tab');
    $('#' + tabId).removeClass('hide').addClass('active');
});
var staffListArray = <?= json_encode($staff ?? []) ?>;

 var performance_related_dropdown = <?= !empty($performance_related_dropdown) ? json_encode($performance_related_dropdown, true) : [] ?>;
 
 
$("#department").change(function() {
    var deptId = $(this).val();

    if(deptId > 0){
    var filteredStaff = staffListArray.filter(function(staff) {
        return staff.department == deptId;
    });
    }
    else{
        filteredStaff =  staffListArray;
    }

    updateStaffDropdown(filteredStaff);
});
function updateStaffDropdown(staffList) {
    var $staffSelect = $("#view_assigned");

    // Prevent errors if selectpicker not initialized
    if ($staffSelect.hasClass('selectpicker')) {
        $staffSelect.selectpicker('destroy');
    }

    $staffSelect.empty();

    // Default option
    // $staffSelect.append('<option value="">Select Staff</option>');

    if (staffList && staffList.length > 0) {
        staffList.forEach(function(staff) {
            var fullName = (staff.firstname || '') + ' ' + (staff.lastname || '');

            $staffSelect.append(
                '<option value="' + staff.staffid + '">' + fullName.trim() + '</option>'
            );
        });
    } else {
        $staffSelect.append('<option value="">No staff found</option>');
    }

    // Reinitialize selectpicker
    if ($staffSelect.hasClass('selectpicker')) {
        $staffSelect.selectpicker();
    }

    $staffSelect.selectpicker('refresh');
}
var r = {
     department: "[name='department[]']",
        status: "[name='view_status[]']",
        assigned: "[name='view_assigned[]']",
        source: "[name='view_sources[]']",
        update_count: "[name='view_update_count[]']",
        created_date: "[name='create_date_range']",
        assigned_date: "[name='assignation_date_range']",
        campaign:'[name="view_campaign[]"]',
        adsset:'[name="view_adsset[]"]',
        ads:'[name="view_ads[]"]',
        form:'[name="view_form[]"]'
};

var leadsTable;
var currentView = 'list';

// Helper function for random colors
function getRandomColor(id) {
    var colors = ['#667eea', '#f093fb', '#4facfe', '#43e97b', '#fa709a', '#fee140', '#30cfd0', '#a8edea'];
    return colors[id % colors.length];
}


$("#view_sources").on("change", function() {

    campaign_names = [];
    adsset_names = [];
    ads_names = [];
    form = [];
    term = [];

    $("#view_campaign").empty().selectpicker("refresh");
    $("#view_adsset").empty().selectpicker("refresh");
    $("#view_ads").empty().selectpicker("refresh");
    $("#view_form").empty().selectpicker("refresh");
    $("#view_term").empty().selectpicker("refresh");

    let selectedValues = $(this).selectpicker("val");

    if (Array.isArray(selectedValues) && selectedValues.length > 0) {

        // Check if 35 or 39 exists
        let hasSpecialSource = selectedValues.includes("35") || selectedValues.includes("39");

        if (hasSpecialSource) {
            selectedValues.forEach(source => {
                setdropdown_utms(source);
            });

            $(".hide_show_utm").removeClass("hide");
        } else {
            $(".hide_show_utm").addClass("hide");
        }

    } else {
        $(".hide_show_utm").addClass("hide");
    }

});
        
        
        async function setdropdown_utms(source_id) {
            await set_campaign(source_id);
            await set_form(source_id);
        }
        
          function set_campaign(source_id) {
            // Retrieve campaign data for the given source ID
            let campaign_data = performance_related_dropdown[source_id]["campaign_name"].split(",");
            let source_name = performance_related_dropdown[source_id]["source_name"];

            if (campaign_data.length > 0) {
                let options = "";
                campaign_data.forEach(campaign => {
                    // Avoid duplicate campaign names
                    if (!campaign_names.includes(campaign.trim())) {
                        campaign_names.push(campaign.trim());
                        options += `<option data-source="${source_id}" value="${campaign.trim()}">${campaign.trim()}</option>`;
                    }

                    // Set ads set names for the campaign
                    // set_adsset_name(source_id, campaign.trim());
                });

                // Append options to the campaign dropdown and refresh
                $("#view_campaign").append(options).selectpicker("refresh");
            }
        }
        
           function set_form(source_id) {
            // Retrieve campaign data for the given source ID
            let form_data = performance_related_dropdown[source_id]["form_name"].split(",");

            if (form_data.length > 0) {
                let options = "";
                form_data.forEach(form_name => {
                    // Avoid duplicate form_name names
                    if (!form.includes(form_name.trim())) {
                        form.push(form_name.trim());
                        options += `<option data-source="${source_id}" value="${form_name.trim()}">${form_name.trim()}</option>`;
                    }

                    // Set ads set names for the campaign
                    // set_adsset_name(source_id, campaign.trim());
                });

                // Append options to the campaign dropdown and refresh
                $("#view_form").append(options).selectpicker("refresh");
            }
        }

            $("#view_campaign").on("change", function() {
            adsset_names = [];
            $("#view_adsset").empty().selectpicker("refresh");
            // Get all selected options
            let selectedOptions = $(this).find(":selected");

            // Loop through each selected option
            selectedOptions.each(function() {
                // Get the value of the current option
                let campaign = $(this).val();

                // Get the data-source attribute of the current option
                let source_id = $(this).attr("data-source");

                // Pass the values one by one to the function
                set_adsset_name(source_id, campaign.trim());

            });
        });
        
        
        

        function set_adsset_name(source_id, campaign) {
            // Retrieve ads set data for the given source ID
            let adsset_data = performance_related_dropdown[source_id]["ads_set_name"].split(",");
            let source_name = performance_related_dropdown[source_id]["source_name"];

            if (adsset_data.length > 0) {
                let options = "";
                adsset_data.forEach(adsset => {
                    let adsset_parts = adsset.split("##");

                    // Check if the campaign matches and avoid duplicate ads set names
                    if (
                        adsset_parts.length === 2 &&
                        !adsset_names.includes(adsset_parts[1].trim()) &&
                        campaign === adsset_parts[0]
                    ) {
                        adsset_names.push(adsset_parts[1].trim());
                        options += `<option data-source="${source_id}"  value="${adsset_parts[1].trim()}">${adsset_parts[1].trim()}</option>`;
                    }
                });
                // set_ads_name(source_id, adsset_parts[1].trim());
                // Append options to the ads set dropdown and refresh
                $("#view_adsset").append(options).selectpicker("refresh");
            }
        }
        
        
           $("#view_adsset").on("change", function() {
            ads_names = [];
            $("#view_ads").empty().selectpicker("refresh");
            // Get all selected options
            let selectedOptions = $(this).find(":selected");

            // Loop through each selected option
            selectedOptions.each(function() {
                // Get the value of the current option
                let adsset = $(this).val();

                // Get the data-source attribute of the current option
                let source_id = $(this).attr("data-source");

                // Pass the values one by one to the function
                set_ads_name(source_id, adsset.trim());

            });
        });
        
          function set_ads_name(source_id, adsset_name) {
            // Retrieve ads set data for the given source ID
            let ads_data = performance_related_dropdown[source_id]["ads_name"].split(",");
            let source_name = performance_related_dropdown[source_id]["source_name"];
            if (ads_data.length > 0) {
                let options = "";
                ads_data.forEach(adsset => {
                    let adsset_parts = adsset.split("##");
                    if (
                        adsset_parts.length === 2 &&
                        !ads_names.includes(adsset_parts[1].trim()) &&
                        adsset_name === adsset_parts[0].trim()
                    ) {
                        ads_names.push(adsset_parts[1].trim());
                        options += `<option value="${adsset_parts[1].trim()}">${adsset_parts[1].trim()}</option>`;
                    }
                });
                $("#view_ads").append(options).selectpicker("refresh");
            }
        }
     
        
// Load data via AJAX
function loadData() {
    
   if (!$(".regions-container").is(":visible")) 
   {
       hideLoader();
       return false;
   }
    showLoader();
    
    var filters = {
         department: $('[name="department[]"]').val(),
        status: $('[name="view_status[]"]').val(),
        assigned: $('[name="view_assigned[]"]').val(),
        source: $('[name="view_sources[]"]').val(),
        update_count: $('[name="view_update_count[]"]').val(),
        created_date: $('[name="create_date_range"]').val(),
        assigned_date:  $('[name="assignation_date_range"]').val(),
        campaign:$('[name="view_campaign[]"]').val(),
        adsset:$('[name="view_adsset[]"]').val(),
        ads:$('[name="view_ads[]"]').val(),
        form:$('[name="view_form[]"]').val(),
    };
    
    $.ajax({
        url: admin_url + 'dashboard/leads_assignation',
        type: 'POST',
        data: filters,
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                response.stats  =[];
                renderRegions(response.data);
            } else {
                alert_float('danger', response.message || 'Error loading data');
                $('#regions-grid').html('<div class="empty-state"><i class="fa fa-exclamation-triangle"></i><p>No data available</p></div>');
            }
            hideLoader();
        },
        error: function(xhr, status, error) {
            console.error('AJAX Error:', error);
            $('#regions-grid').html('<div class="empty-state"><i class="fa fa-exclamation-triangle"></i><p>Error loading data. Please try again.</p></div>');
            hideLoader();
            alert_float('danger', 'Error loading data. Please refresh the page.');
        }
    });
}


function loadData_source() {

  if (!$(".source-container").is(":visible")) 
  {
       hideLoader();
      return false;
  }
    showLoader();
    
    var filters = {
         department: $('[name="department[]"]').val(),
        status: $('[name="view_status[]"]').val(),
        assigned: $('[name="view_assigned[]"]').val(),
        source: $('[name="view_sources[]"]').val(),
        update_count: $('[name="view_update_count[]"]').val(),
        created_date: $('[name="create_date_range"]').val(),
        assigned_date:  $('[name="assignation_date_range"]').val(),
        campaign:$('[name="view_campaign[]"]').val(),
        adsset:$('[name="view_adsset[]"]').val(),
        ads:$('[name="view_ads[]"]').val(),
        form:$('[name="view_form[]"]').val(),
        source_status:1
    };
    
    $.ajax({
        url: admin_url + 'dashboard/leads_assignation',
        type: 'POST',
        data: filters,
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                response.stats  =[];
                
                // console.log(response);
                 renderSources(response.data);
                 
                // renderRegions(response.data);
            } else {
                alert_float('danger', response.message || 'Error loading data');
                $('#regions-grid').html('<div class="empty-state"><i class="fa fa-exclamation-triangle"></i><p>No data available</p></div>');
            }
            hideLoader();
        },
        error: function(xhr, status, error) {
            console.error('AJAX Error:', error);
            $('#regions-grid').html('<div class="empty-state"><i class="fa fa-exclamation-triangle"></i><p>Error loading data. Please try again.</p></div>');
            hideLoader();
            alert_float('danger', 'Error loading data. Please refresh the page.');
        }
    });
}

// Update statistics
function updateStats(stats) {
    $('.stat-card').each(function(index) {
        var value = $(this).find('.stat-value');
        if (index === 0) value.text(stats.total_regions || 0);
        else if (index === 1) value.text(stats.total_staff || 0);
        else if (index === 2) value.text(stats.total_leads || 0);
        else if (index === 3) value.text((stats.total_leads/stats.total_staff).toFixed(1) || 0);
    });
}

// Render regions data

function renderSources(data) {
    if (!data || data.length === 0) {
        $('#source-grid').html('<div class="empty-state"><i class="fa fa-inbox"></i><p>No sources found</p></div>');
        return;
    }

    let stats = {
        total_leads: 0,
        total_regions: 0,
        total_staff: 0,
        avg_leads: 0
    };

    // ✅ GLOBAL UNIQUE SETS (based on staff.office_location_region)
    let globalStaffSet = new Set();
    let globalRegionSet = new Set();

    let html = '';

    data.forEach(sourceData => {
        let parsed = [];
        try { parsed = JSON.parse(sourceData.data) || []; } catch(e){}

        if (!parsed.length){
             updateStats(stats);
            return;
        }

        let isFormBased   = parsed[0].form_name !== undefined;
        let isRegionBased = parsed[0].region_name !== undefined;

        let totalLeads = 0;
        let localStaffSet = new Set();   // unique staff for this source
        let localRegionSet = new Set();  // unique regions for this source

        let contentHTML = '';

        // 🔹 FB → FORM → STAFF
        if (isFormBased) {
            parsed.forEach(form => {
                let formLeads = 0;
                let formStaffSet = new Set();

                (form.staff || []).forEach(staff => {
                    if (staff.staff_id) {
                        localStaffSet.add(staff.staff_id);
                        globalStaffSet.add(staff.staff_id);

                        // ✅ staff office region counts
                        if (staff.office_region_id) {
                            localRegionSet.add(staff.office_region_id);
                            globalRegionSet.add(staff.office_region_id);
                        }

                        formStaffSet.add(staff.staff_id);
                    }
                    formLeads += staff.count || 0;
                });

                totalLeads += formLeads;

                contentHTML += `
                <div class="staff-details">
                    <div class="staff-name">
                        ${escapeHtml(form.form_name)} 
                        - Staffs (${formStaffSet.size})
                        <span class="lead-count-badge">${formLeads} leads</span>
                    </div>
                    ${form.staff?.length ? `
                    <hr>
                    <div class="source-tags">
                        ${form.staff.map(sa => `
                            <span class="source-tag">
                                <i class="fa fa-tag"></i> 
                                ${escapeHtml(sa.staff_name)} (${sa.count})
                            </span>`).join('')}
                    </div>` : '<div class="no-status">No Staff data</div>'}
                </div><hr>`;
            });
        } 
        // 🔹 GOOGLE → REGION → STAFF
        else if (isRegionBased) {
      
            parsed.forEach(region => {
                let regionLeads = 0;
                let regionStaffSet = new Set();

                (region.staff || []).forEach(staff => {
                    if (staff.staff_id) {
                        localStaffSet.add(staff.staff_id);
                        globalStaffSet.add(staff.staff_id);

                        // ✅ use staff.office_location_region
                        if (staff.office_region_id) {
                            localRegionSet.add(staff.office_region_id);
                            globalRegionSet.add(staff.office_region_id);
                        }

                        regionStaffSet.add(staff.staff_id);
                    }
                    regionLeads += staff.count || 0;
                });

                totalLeads += regionLeads;

                contentHTML += `
                <div class="staff-details">
                    <div class="staff-name">
                        ${escapeHtml(region.region_name || 'Region')} 
                        - Staffs (${regionStaffSet.size})
                        <span class="lead-count-badge">${regionLeads} leads</span>
                    </div>
                    ${region.staff?.length ? `
                    <hr>
                    <div class="source-tags">
                        ${region.staff.map(st => `
                            <span class="source-tag">
                                <i class="fa fa-tag"></i> 
                                ${escapeHtml(st.staff_name)} (${st.count})
                            </span>`).join('')}
                    </div>` : '<div class="no-status">No Staff data</div>'}
                </div><hr>`;
            });
        } 
        // 🔹 NORMAL → STAFF
        else {
            
            parsed.forEach(staff => {
                if (staff.staff_id) {
                    localStaffSet.add(staff.staff_id);
                    globalStaffSet.add(staff.staff_id);

                    if (staff.office_region_id) {
                        localRegionSet.add(staff.office_region_id);
                        globalRegionSet.add(staff.office_region_id);
                    }
                }

                totalLeads += staff.lead_count || 0;
            });

            contentHTML = renderStaffList_source(parsed);
        }

        stats.total_leads += totalLeads;

        let staffLength = localStaffSet.size;
        let regionLength = localRegionSet.size;

        html += `
            <div class="region-card-modern">
                <div class="region-header-modern">
                    <div class="region-info">
                        <div class="region-icon">
                            <i class="fa fa-building"></i>
                        </div>
                        <div>
                            <h3>${escapeHtml(sourceData.source_name)}</h3>
                            <div class="region-stats">
                                <span><i class="fa fa-users"></i> ${staffLength} Counselors</span>
                                <span><i class="fa fa-line-chart"></i> ${totalLeads} Leads</span>
                            </div>
                        </div>
                    </div>
                    <div class="region-badge-modern">
                        <span class="badge-count">${staffLength}</span>
                    </div>
                </div>
                
                <div class="region-content">
                    ${contentHTML}
                </div>
                
                ${staffLength > 0 ? `
                <div class="region-footer">
                    <i class="fa fa-chart-simple"></i>
                    Avg: ${(totalLeads / staffLength).toFixed(1)} leads per counselor
                </div>
                ` : ''}
            </div>
        `;
    });

    // ✅ FINAL STATS
    stats.total_staff = globalStaffSet.size;
    stats.total_regions = globalRegionSet.size;

    $('#source-grid').html(html);
    applyView();
    updateStats(stats);
}
function renderRegions(data) {
var stats =[];
    // Reset stats before calculation
    stats.total_leads = 0;
    stats.total_staff = 0;
    stats.total_regions = 0;

    if (!data || data.length === 0) {
        updateStats(stats);
        $('#regions-grid').html(
            '<div class="empty-state"><i class="fa fa-inbox"></i><p>No regions found</p></div>'
        );
        return;
    }

    var html = '';

    // Sets to track uniqueness
    var uniqueRegions = new Set();
    var uniqueCounselors = new Set();

    for (var i = 0; i < data.length; i++) {

        var office = data[i];

        // Track unique region
        

        var staffList = [];
        try {
            staffList = JSON.parse(office.staff_data) || [];
        } catch (e) {
            staffList = [];
        }
        var totalRegionLeads = 0;
        var activeStaffCount = 0;

        for (var j = 0; j < staffList.length; j++) {

            var staff = staffList[j];
            var leads = staff.lead_count || 0;

            totalRegionLeads += leads;

            if (leads > 0) {
                activeStaffCount++;

                // Track unique counselor
                uniqueCounselors.add(staff.staff_id);
                uniqueRegions.add(staff.office_region_id);
            }
        }

        // Add to global stats
        stats.total_leads += totalRegionLeads;

        html += `
            <div class="region-card-modern">
                <div class="region-header-modern">
                    <div class="region-info">
                        <div class="region-icon">
                            <i class="fa fa-building"></i>
                        </div>
                        <div>
                            <h3>${escapeHtml(office.region_name)}</h3>
                            <div class="region-stats">
                                <span><i class="fa fa-users"></i> ${activeStaffCount} Counselors</span>
                                <span><i class="fa fa-line-chart"></i> ${totalRegionLeads} Leads</span>
                            </div>
                        </div>
                    </div>
                    <div class="region-badge-modern">
                        <span class="badge-count">${staffList.length}</span>
                    </div>
                </div>

                <div class="region-content">
                    ${renderStaffList(staffList)}
                </div>

                ${staffList.length > 0 ? `
                <div class="region-footer">
                    <i class="fa fa-chart-simple"></i>
                    Avg: ${(staffList.length ? (totalRegionLeads / staffList.length).toFixed(1) : 0)} leads per counselor
                </div>
                ` : ''}
            </div>
        `;
    }

    // Final unique counts
    stats.total_regions = uniqueRegions.size;
    stats.total_staff = uniqueCounselors.size;

    // Render UI
    $('#regions-grid').html(html);

    applyView();
    updateStats(stats);
}
// Render staff list
function renderStaffList(staffList) {
// staff_data
    if (!staffList || staffList.length === 0) {
        return '<div class="empty-state"><i class="fa fa-user-slash"></i><p>No counselors available in this region</p></div>';
    }
    
    // console.log("staffList",staffList);
    
    var html = '<div class="staff-grid-modern">';
    
      
   for (var i = 0; i < staffList.length; i++) {
    var s = staffList[i];
   
   if(s.staff_id === undefined)
   {
       continue;
   }
    if(s.lead_count == 0)
    {
        continue;
    }
    if(s.staff_name === null)
    {
        continue;
    }
    
    // console.log(s.staff_name);
  
    var sourceData = s.sources || [];
    var status_summary = s.status_summary || [];
    var nameParts = s.staff_name.split(' ')??'';
    var initials = (nameParts[0].charAt(0) + (nameParts[1] ? nameParts[1].charAt(0) : '')).toUpperCase();
    var fbData = [];
    var googleData = [];
    
    // Collect forms data
    for (var j = 0; j < sourceData.length; j++) {
        if (sourceData[j].forms && sourceData[j].forms.length) {
            fbData = fbData.concat(sourceData[j].forms);
        }

        if (sourceData[j].google && sourceData[j].google.length) {
            googleData = googleData.concat(sourceData[j].google);
        }
    }
    
     if (s.forms && s.forms.length) {
            fbData = fbData.concat(s.forms);
        }
        
        if (s.google && s.google.length) {
            googleData = googleData.concat(s.google);
        }
    
    // console.log(fbData);

    html += `
        <div class="staff-card-modern">
            <div class="staff-avatar" style="background: linear-gradient(135deg, ${getRandomColor(s.staff_id || 1)});">
                ${initials}
            </div>
            <div class="staff-details">
                <div class="staff-name">
                    ${escapeHtml(s.staff_name)}
                    <span class="lead-count-badge">${s.lead_count || 0} leads</span>
                </div>
                
                ${sourceData.length > 0 ? `
                <h5 style="margin: 8px 0 5px; font-size: 12px;">Sources (${sourceData.length})</h5>
                <hr>
                <div class="source-tags">
                    ${sourceData.map(function(so) {
                        return `<span class="source-tag">
                            <i class="fa fa-tag"></i> 
                            ${escapeHtml(so.source_name)} (${so.lead_count})
                        </span>`;
                    }).join('')}
                </div>` : '<div class="no-sources">No source data</div>'}

                ${status_summary.length > 0 ? `
                <hr>
                <h5 style="margin: 8px 0 5px; font-size: 12px;">Status (${status_summary.length})</h5>
                <hr>
                <div class="source-tags">
                    ${status_summary.map(function(sa) {
                        return `<span class="source-tag">
                            <i class="fa fa-tag"></i> 
                            ${escapeHtml(sa.status_name)} (${sa.count})
                        </span>`;
                    }).join('')}
                </div>` : '<div class="no-status">No status data</div>'}

               ${fbData?.filter(fb => fb.form_name && fb.form_name.trim() !== '').length ? `
    <hr>
    <h5 style="margin: 8px 0 5px; font-size: 12px;">
        Facebook Forms (${fbData.filter(fb => fb.form_name && fb.form_name.trim() !== '').length})
    </h5>
    <hr>
    <div class="source-tags">
        ${fbData
            .filter(fb => fb.form_name && fb.form_name.trim() !== '')
            .map(fb => `
                <span class="source-tag">
                    <i class="fa fa-tag"></i>
                    ${escapeHtml(fb.form_name)} (${fb.count})
                </span>
            `).join('')}
    </div>
` : ''}
                ${googleData.length > 0 ? `
                <hr>
                <h5 style="margin: 8px 0 5px; font-size: 12px;">Google Campaigns (${googleData.length})</h5>
                <hr>
                <div class="source-tags">
                    ${googleData.map(function(gc) {
                        return `<span class="source-tag">
                            <i class="fa fa-tag"></i> 
                            ${escapeHtml(gc.utm_campaign_name)} / ${escapeHtml(gc.utm_ads_set_name)} / ${escapeHtml(gc.utm_ads_name)} (${gc.count})
                        </span>`;
                    }).join('')}
                </div>` : ''}

            </div>
        </div>
    `;
}
    
    html += '</div>';
    return html;
}



function renderStaffList_source(staffList) {
// staff_data
    if (!staffList || staffList.length === 0) {
        return '<div class="empty-state"><i class="fa fa-user-slash"></i><p>No counselors available in this region</p></div>';
    }
    
    // console.log("staffList",staffList);
    
    var html = '<div class="staff-grid-modern">';
    
      
   for (var i = 0; i < staffList.length; i++) {
    var s = staffList[i];
   
   if(s.staff_id === undefined)
   {
       continue;
   }
    if(s.lead_count == 0)
    {
        continue;
    }
    if(s.staff_name === null)
    {
        continue;
    }
    
//   console.log(staffList);
  
    var sourceData = s.sources || [];
    var status_summary = s.status_summary || [];
    var nameParts = s.staff_name.split(' ')??'';
    var initials = (nameParts[0].charAt(0) + (nameParts[1] ? nameParts[1].charAt(0) : '')).toUpperCase();
    var fbData = [];
    var googleData = [];
   
    
     if (s.forms && s.forms.length) {
            fbData = fbData.concat(s.forms);
        }
        
        if (s.google && s.google.length) {
            googleData = googleData.concat(s.google);
        }
    
    // console.log(fbData);

    html += `
        <div class="staff-card-modern">
            <div class="staff-avatar" style="background: linear-gradient(135deg, ${getRandomColor(s.staff_id || 1)});">
                ${initials}
            </div>
            <div class="staff-details">
                <div class="staff-name">
                    ${escapeHtml(s.staff_name)}
                    <span class="lead-count-badge">${s.lead_count || 0} leads</span>
                </div>
                
                ${sourceData.length > 0  && sourceData !== undefined? `
                <h5 style="margin: 8px 0 5px; font-size: 12px;">Sources (${sourceData.length})</h5>
                <hr>
                <div class="source-tags">
                    ${sourceData.map(function(so) {
                        return `<span class="source-tag">
                            <i class="fa fa-tag"></i> 
                            ${escapeHtml(so.source_name)} (${so.lead_count})
                        </span>`;
                    }).join('')}
                </div>` : ''}

                ${status_summary.length > 0 ? `
                <hr>
                <h5  class="hide" style="margin: 8px 0 5px; font-size: 12px;">Status (${status_summary.length})</h5>
               
                <div class="source-tags">
                    ${status_summary.map(function(sa) {
                        return `<span class="source-tag">
                            <i class="fa fa-tag"></i> 
                            ${escapeHtml(sa.status_name)} (${sa.count})
                        </span>`;
                    }).join('')}
                </div>` : '<div class="no-status">No status data</div>'}

               ${fbData?.filter(fb => fb.form_name && fb.form_name.trim() !== '').length ? `
    <hr>
    <h5 style="margin: 8px 0 5px; font-size: 12px;">
        Facebook Forms (${fbData.filter(fb => fb.form_name && fb.form_name.trim() !== '').length})
    </h5>
    <hr>
    <div class="source-tags">
        ${fbData
            .filter(fb => fb.form_name && fb.form_name.trim() !== '')
            .map(fb => `
                <span class="source-tag">
                    <i class="fa fa-tag"></i>
                    ${escapeHtml(fb.form_name)} (${fb.count})
                </span>
            `).join('')}
    </div>
` : ''}
                ${googleData.length > 0 ? `
                <hr>
                <h5 style="margin: 8px 0 5px; font-size: 12px;">Google Campaigns (${googleData.length})</h5>
                <hr>
                <div class="source-tags">
                    ${googleData.map(function(gc) {
                        return `<span class="source-tag">
                            <i class="fa fa-tag"></i> 
                            ${escapeHtml(gc.utm_campaign_name)} / ${escapeHtml(gc.utm_ads_set_name)} / ${escapeHtml(gc.utm_ads_name)} (${gc.count})
                        </span>`;
                    }).join('')}
                </div>` : ''}

            </div>
        </div>
    `;
}
    
    html += '</div>';
    return html;
}

// Apply current view
function applyView(currentView) {
if(currentView == undefined)
{
    currentView ="list";
}
    
    ['regions-grid', 'source-grid'].forEach(function(id) {
        var grid = $('#' + id);
        if (currentView === 'list') {
            grid.addClass('list-view');
        } else {
            grid.removeClass('list-view');
        }
    });
}
// Set view (grid or list)
function setView(view) {
    currentView = view;
    applyView(currentView);
    
    $('.view-btn').removeClass('active');
    if (view === 'grid') {
        $('.grid-view').addClass('active');
    } else {
        $('.list-view').addClass('active');
    }
}

// Apply filters
function applyFilters() {

    setTimeout(loadData, 100);
    setTimeout(loadData_source, 100);
    alert_float('success', 'Filters applied successfully!');
}

// Clear filters
function clearFilters() {
    $('[name="department[]"]').val(null).trigger('change');
    $('[name="view_status[]"]').val(null).trigger('change');
    $('[name="view_assigned[]"]').val(null).trigger('change');
     $('[name="view_sources[]"]').val(null).trigger('change');
    $('[name="view_update_count[]"]').val(null).trigger('change');
    
    if ($.fn.selectpicker) {
        $('.selectpicker').selectpicker('refresh');
    }
    initDateRangePicker();

        setTimeout(loadData, 100);
    setTimeout(loadData_source, 100);
    alert_float('info', 'Filters cleared successfully!');
}

// Reset filters
function resetFilters() {
    clearFilters();
}

// Show loader
function showLoader() {
    $('#loader-wrapper').show();
    $('#regions-grid').hide();
     $('#source-grid').hide();
}

// Hide loader
function hideLoader() {
    $('#loader-wrapper').hide();
    $('#regions-grid').show();
     $('#source-grid').show();
}

// Escape HTML to prevent XSS
function escapeHtml(str) {
    if (!str) return '';
    return str
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

function initDateRangePicker() {
    if ($('.date-range-picker').length) {

        $('.date-range-picker').each(function () {
 $(this).val('');
            let $this = $(this);

            $this.daterangepicker({
                autoUpdateInput: false,
                locale: {
                    cancelLabel: 'Clear',
                    format: 'YYYY-MM-DD'
                },
                ranges: {
                    'Today': [moment(), moment()],
                    'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                    'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                    'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                    'This Month': [moment().startOf('month'), moment().endOf('month')],
                    'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
                }
            });

            // ✅ APPLY
            $this.on('apply.daterangepicker', function (ev, picker) {

                let start = picker.startDate.format('YYYY-MM-DD');
                let end   = picker.endDate.format('YYYY-MM-DD');

                $(this).val(start + ' to ' + end);

                // store individually
                $(this).data('start', start);
                $(this).data('end', end);

                $(this).trigger('change');
            });

            // ✅ CANCEL (FIXED)
            $this.on('cancel.daterangepicker', function () {

                $(this).val('');
                $(this).removeData('start');
                $(this).removeData('end');

                $(this).trigger('change');
            });

        });
    }
}

// Initialize on document ready
$(document).ready(function() {
    // Initialize selectpickers
    if ($.fn.selectpicker) {
        $('.selectpicker').selectpicker({
            iconBase: 'fa',
            tickIcon: 'fa-check',
            liveSearch: true,
            liveSearchPlaceholder: 'Search...',
            actionsBox: true,
            selectAllText: 'Select All',
            deselectAllText: 'Deselect All',
            noneSelectedText: 'No selection'
        });
    }
    
    // Set default view to list
    setView('list');
    
    // Load initial data
    loadData();
    // loadData_source();
    
    initDateRangePicker();
    
    
    
});



</script>