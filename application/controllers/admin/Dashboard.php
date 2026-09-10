<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Dashboard extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('dashboard_model');
         $this->load->model('leads_model');
    }

    /* This is admin dashboard view */
    public function index()
    {
        
        redirect(base_url('admin/leads/'));
        die;
        close_setup_menu();
        $this->load->model('departments_model');
        $this->load->model('todo_model');
        $data['departments'] = $this->departments_model->get();

        $data['todos'] = $this->todo_model->get_todo_items(0);
        // Only show last 5 finished todo items
        $this->todo_model->setTodosLimit(5);
        $data['todos_finished']            = $this->todo_model->get_todo_items(1);
        $data['upcoming_events_next_week'] = $this->dashboard_model->get_upcoming_events_next_week();
        $data['upcoming_events']           = $this->dashboard_model->get_upcoming_events();
        $data['title']                     = _l('dashboard_string');

        $this->load->model('contracts_model');
        $data['expiringContracts'] = $this->contracts_model->get_contracts_about_to_expire();

        $this->load->model('currencies_model');
        $data['currencies']    = $this->currencies_model->get();
        $data['base_currency'] = $this->currencies_model->get_base_currency();
        $data['activity_log']  = $this->misc_model->get_activity_log();
        // Tickets charts
        $tickets_awaiting_reply_by_status     = $this->dashboard_model->tickets_awaiting_reply_by_status();
        $tickets_awaiting_reply_by_department = $this->dashboard_model->tickets_awaiting_reply_by_department();

        $data['tickets_reply_by_status']              = json_encode($tickets_awaiting_reply_by_status);
        $data['tickets_awaiting_reply_by_department'] = json_encode($tickets_awaiting_reply_by_department);

        $data['tickets_reply_by_status_no_json']              = $tickets_awaiting_reply_by_status;
        $data['tickets_awaiting_reply_by_department_no_json'] = $tickets_awaiting_reply_by_department;

        $data['projects_status_stats'] = json_encode($this->dashboard_model->projects_status_stats());
        $data['leads_status_stats']    = json_encode($this->dashboard_model->leads_status_stats());
        $data['google_ids_calendars']  = $this->misc_model->get_google_calendar_ids();
        $data['bodyclass']             = 'dashboard invoices-total-manual';
        $this->load->model('announcements_model');
        $data['staff_announcements']             = $this->announcements_model->get();
        $data['total_undismissed_announcements'] = $this->announcements_model->get_total_undismissed_announcements();

        $this->load->model('projects_model');
        $data['projects_activity'] = $this->projects_model->get_activity('', hooks()->apply_filters('projects_activity_dashboard_limit', 20));
        add_calendar_assets();
        $this->load->model('utilities_model');
        $this->load->model('estimates_model');
        $data['estimate_statuses'] = $this->estimates_model->get_statuses();

        $this->load->model('proposals_model');
        $data['proposal_statuses'] = $this->proposals_model->get_statuses();

        $wps_currency = 'undefined';
        if (is_using_multiple_currencies()) {
            $wps_currency = $data['base_currency']->id;
        }
        $data['weekly_payment_stats'] = json_encode($this->dashboard_model->get_weekly_payments_statistics($wps_currency));

        $data['dashboard'] = true;

        $data['user_dashboard_visibility'] = get_staff_meta(get_staff_user_id(), 'dashboard_widgets_visibility');

        if (!$data['user_dashboard_visibility']) {
            $data['user_dashboard_visibility'] = [];
        } else {
            $data['user_dashboard_visibility'] = unserialize($data['user_dashboard_visibility']);
        }
        $data['user_dashboard_visibility'] = json_encode($data['user_dashboard_visibility']);

        $data = hooks()->apply_filters('before_dashboard_render', $data);
        $this->load->view('admin/dashboard/dashboard', $data);
    }

    /* Chart weekly payments statistics on home page / ajax */
    public function weekly_payments_statistics($currency)
    {
        if ($this->input->is_ajax_request()) {
            echo json_encode($this->dashboard_model->get_weekly_payments_statistics($currency));
            die();
        }
    }


    public function table($tablePageName = "")
    {

        $data =   $this->app->get_table_data($tablePageName);
        echo json_encode($data);
    }

    public function leads_transfers()
    {
    
        if ($this->input->is_ajax_request()) {
            
   
            if(!empty($_POST['summary']) && $_POST['summary'] == 1)
            {
              $data =  $this->leads_model->leads_transfers_summary($_POST); 
              echo json_encode(array("status"=>1,"data"=>$data));
               die;
            }
            else if(!empty($_REQUEST['tbl']) && $_REQUEST['tbl'] == "self_table")
            {
                
               $this->table('lead_transfer_dashboard');
               
            die();
            }else  if(!empty($_POST['summary_self']) && $_POST['summary_self'] == 1)
            {
                 $data =  $this->leads_model->leads_transfers_summary_self($_POST); 
              echo json_encode(array("status"=>1,"data"=>$data));
               die;
            }
            else  if(!empty($_POST['summary_fresh']) && $_POST['summary_fresh'] == 1)
            {
                 $data =  $this->leads_model->leads_transfers_summary_fresh($_POST); 
              echo json_encode(array("status"=>1,"data"=>$data));
               die;
            }
            else if(!empty($_REQUEST['tbl']) && $_REQUEST['tbl'] == "fresh_table")
            {
                
               $this->table('fresh_lead_transfer_dashboard');
               
            die();
            }
            else{
            $this->table('not_reachable_transfer_leads');
            die();
            }
        }
        $data = [];
        $data["lead_statuses"] = $this->leads_model->get_status();
         $data["lead_sources"] = $this->leads_model->get_source('','');
        $data["staff"] = $this->staff_model->get();
          $data["departments"] = $this->staff_model->staff_department();
        $this->load->view('admin/dashboard/leads_transfers', $data);
    }
    
    

    
    public function leads_assignation()
    {
    
        if ($this->input->is_ajax_request()) {
            if(!empty($_POST['source_status']))
            {
              $data = getleadsCounts_by_source($_POST);
           echo json_encode(array("success"=>true,"data"=>$data),true);
              die();  
            }
           $data = getleadsCounts_by_staff($_POST);
           echo json_encode(array("success"=>true,"data"=>$data),true);
            die();
        }

        $data = [];
        
        $data["lead_statuses"] = $this->leads_model->get_status();
         $data["departments"] = $this->staff_model->staff_department();
      
        $data["staff"] = $this->staff_model->get();
         $data['lead_sources']  = $this->leads_model->get_source('','',["l.paid_sources"=>1]);

          $data['performance_related_dropdown']  = $this->leads_model->performance_related_dropdown();
        if (!empty($data['performance_related_dropdown'])) {
            $data['performance_related_dropdown'] = array_column($data['performance_related_dropdown'], null, "source");
        }
        $this->load->view('admin/dashboard/leads_assignation', $data);
        
    }
    
    public function call_tracker()
    {
        $data =[];
        $data['type']  = $this->leads_model->get_type();
         $data['sources']  = $this->leads_model->get_source();
         $data['statuses'] = $this->leads_model->get_status();
         $data['office_location']  = $this->staff_model->office_location();
         $data['staff_department']  = $this->staff_model->staff_department();
          $data['staff']  =$this->staff_model->get('', ['active' => 1]);
         $this->load->view('admin/dashboard/call_tracker', $data);
    }
    
    public function get_daily_calls_tracker()
    {
    $this->load->driver('cache', array('adapter' => 'file'));
    
    // ✅ get date from POST (from your date picker)
    $from = $this->input->post('from');
    $to   = $this->input->post('to');
    
    // fallback to today
    if (!$from || !$to) {
    $from = date('Y-m-d');
    $to   = date('Y-m-d');
    }
    
    // ✅ unique cache key based on filter
    // $cacheKey = "daily_calls_{$from}_{$to}";
    
    // ✅ check cache FIRST
    // $data = $this->cache->get($cacheKey);
    
    // if ($data === FALSE) {
    
    // ❌ cache miss → fetch from DB
    $data = $this->dashboard_model->dailyCallsTracker();
    
    // ✅ save cache (10 min)
    // $this->cache->save($cacheKey, $data, 600);
    
    // }
    
    // ✅ return JSON properly
    echo json_encode($data);
    }
    
    public function get_daily_calls_Datatable()
    {
        
        // return true;
        $data = $this->dashboard_model->dailyCallsTracker_Datatable();
        
        echo json_encode( $data ,true);
    }
    
       public function follow_up_tracker()
    {
        $data =[];
        $data['type']  = $this->leads_model->get_type();
         $data['sources']  = $this->leads_model->get_source();
         $data['statuses'] = $this->leads_model->get_status();
         $data['office_location']  = $this->staff_model->office_location();
         $data['staff_department']  = $this->staff_model->staff_department();
          $data['staff']  =$this->staff_model->get('', ['active' => 1]);
         $this->load->view('admin/dashboard/follow_up_tracker', $data);
    }
    
  public function getFollowupDashboard()
{
//     ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
    $param = [
        'follow_up_date'   => date('Y-m-d'),
        'missed_from_date' => $this->input->post('from'),
        'missed_to_date'   => $this->input->post('to'),
        'status'           => $this->input->post('view_status'),
        'assigned'         => $this->input->post('staff'),
        'source'           => $this->input->post('view_source'),
        'lead_type'        => $this->input->post('lead_type'),
        'department'       => $this->input->post('staff_department'),
        'location'         => $this->input->post('office_location'),
    ];

    $result = $this->dashboard_model->getFollowupDashboard($_POST);
    echo json_encode($result);
}


public function getFollow_up_datatable()
{
     $data = $this->dashboard_model->getFollow_up_datatable();
        
        echo json_encode( $data ,true);
}

public function pt_tracker()
{
    $data =[];
    $data['type']  = $this->leads_model->get_type();
    $data['sources']  = $this->leads_model->get_source();
    $data['statuses'] = $this->leads_model->get_status();
    $data['office_location']  = $this->staff_model->office_location();
    $data['staff_department']  = $this->staff_model->staff_department();
    $data['staff']  =$this->staff_model->get('', ['active' => 1]);
    $this->load->view('admin/dashboard/pt_tracker', $data);
}


    
}
