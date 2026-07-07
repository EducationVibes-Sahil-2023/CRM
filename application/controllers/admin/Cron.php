<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Cron extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('cron_model');
         $this->load->model('staff_model');
    }
    
    public function not_reachable_cron()
    {
        if ($this->input->is_ajax_request()) {
        $selected_staff = $this->input->post('selected_staff');
        $selected_staff = is_array($selected_staff)
            ? array_values(array_filter(array_map('intval', $selected_staff)))
            : [];

   $non_selected_staff = $this->input->post('non_selected_staff');
        $non_selected_staff = is_array($non_selected_staff)
            ? array_values(array_filter(array_map('intval', $non_selected_staff)))
            : [];
            
        // Guard: nothing selected -> return an error instead of running an empty query
        if (empty($selected_staff)) {
            header('Content-Type: application/json');
            echo json_encode([
                'success'   => false,
                'message'   => 'Please select at least one staff member.'
            ]);
            return;
        }

        // Reset everyone, then flag the selected staff
        $this->db->update(db_prefix() . 'staff', ['not_reachable_status' => 0]);

        $this->db->where_in('staffid', $selected_staff);
        $this->db->update(db_prefix() . 'staff', ['not_reachable_status' => 1]);
        
              if (!empty($non_selected_staff)) {
        
         $this->db->update(db_prefix() . 'staff', ['non_reachable_status' => 0]);
        $this->db->where_in('staffid', $non_selected_staff);
        $this->db->update(
            db_prefix() . 'staff',
            ['non_reachable_status' => 1]
        );
    }
    

        header('Content-Type: application/json');
        echo json_encode([
            'success'   => true,
            'message'   => 'Criteria saved successfully.'
        ]);
        die;
    }
        
        $data['title'] = "Not Reachable Leads Cron Job Configurations";
        $data['staff'] = $this->staff_model->get('',['active' => 1]);
    
        $data['staff_Selected'] = $this->staff_model->get('', ['active' => 1,'not_reachable_status'=>1]);
        $data['non_staff_Selected'] = $this->staff_model->get('', ['active' => 1,'non_reachable_status'=>1]);

        $this->load->view('admin/cron/not-reachable-cron', $data);
    }
    
    
     public function fresh_cron()
    {
        if ($this->input->is_ajax_request()) {
        $selected_staff = $this->input->post('selected_staff');
        $selected_staff = is_array($selected_staff)
            ? array_values(array_filter(array_map('intval', $selected_staff)))
            : [];
            
            
               $non_selected_staff = $this->input->post('non_selected_staff');
        $non_selected_staff = is_array($non_selected_staff)
            ? array_values(array_filter(array_map('intval', $non_selected_staff)))
            : [];
            

        // Guard: nothing selected -> return an error instead of running an empty query
        if (empty($selected_staff)) {
            header('Content-Type: application/json');
            echo json_encode([
                'success'   => false,
                'message'   => 'Please select at least one staff member.'
            ]);
            return;
        }

        // Reset everyone, then flag the selected staff
        $this->db->update(db_prefix() . 'staff', ['fresh_status' => 0]);

        $this->db->where_in('staffid', $selected_staff);
        $this->db->update(db_prefix() . 'staff', ['fresh_status' => 1]);
        
  
        
            if (!empty($non_selected_staff)) {
        
         $this->db->update(db_prefix() . 'staff', ['non_fresh_status' => 0]);
        $this->db->where_in('staffid', $non_selected_staff);
        $this->db->update(
            db_prefix() . 'staff',
            ['non_fresh_status' => 1]
        );
    }

        header('Content-Type: application/json');
        echo json_encode([
            'success'   => true,
            'message'   => 'Criteria saved successfully.'
        ]);
        die;
    }
        
        $data['title'] = "Fresh Leads Cron Job Configurations";
        $data['staff'] = $this->staff_model->get('',['active' => 1]);
    
        $data['staff_Selected'] = $this->staff_model->get('', ['active' => 1,'fresh_status'=>1]);
 $data['non_staff_Selected'] = $this->staff_model->get('', ['active' => 1,'non_fresh_status'=>1]);
        $this->load->view('admin/cron/fresh-cron', $data);
    }
    
    
      public function daily_fresh_cron()
    {
        if ($this->input->is_ajax_request()) {
        $selected_staff = $this->input->post('selected_staff');
        $selected_staff = is_array($selected_staff)
            ? array_values(array_filter(array_map('intval', $selected_staff)))
            : [];

       $non_selected_staff = $this->input->post('non_selected_staff');
        $non_selected_staff = is_array($non_selected_staff)
            ? array_values(array_filter(array_map('intval', $non_selected_staff)))
            : [];
            
        // Guard: nothing selected -> return an error instead of running an empty query
        if (empty($selected_staff)) {
            header('Content-Type: application/json');
            echo json_encode([
                'success'   => false,
                'message'   => 'Please select at least one staff member.'
            ]);
            return;
        }

        // Reset everyone, then flag the selected staff
        $this->db->update(db_prefix() . 'staff', ['daily_fresh_status' => 0]);

        $this->db->where_in('staffid', $selected_staff);
        $this->db->update(db_prefix() . 'staff', ['daily_fresh_status' => 1]);
        
        
           // Update non-selected staff = 0
    if (!empty($non_selected_staff)) {
        
         $this->db->update(db_prefix() . 'staff', ['non_daily_status' => 0]);
        $this->db->where_in('staffid', $non_selected_staff);
        $this->db->update(
            db_prefix() . 'staff',
            ['non_daily_status' => 1]
        );
    }
    

        header('Content-Type: application/json');
        echo json_encode([
            'success'   => true,
            'message'   => 'Criteria saved successfully.'
        ]);
        die;
    }
        
        $data['title'] = "Daily Fresh Leads Cron Job Configurations";
        $data['staff'] = $this->staff_model->get('',['active' => 1]);
    
        $data['staff_Selected'] = $this->staff_model->get('', ['active' => 1,'daily_fresh_status'=>1]);
        $data['non_staff_Selected'] = $this->staff_model->get('', ['active' => 1,'non_daily_status'=>1]);
        
        $this->load->view('admin/cron/daily-fresh-cron', $data);
    }
    
}