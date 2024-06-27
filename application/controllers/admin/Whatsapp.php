<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Whatsapp extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('whatsapp_model');
    }

    public function index()
    {

        if ($this->input->is_ajax_request()) {


            if (!has_permission('whatsapp_templates', '', 'view')) {
                access_denied('whatsapp_templates');
            }

            $this->app->get_table_data('whatsapp_template');
        }
        if (!has_permission('whatsapp_templates', '', 'view')) {
            access_denied('whatsapp_templates');
        }
        $data['title'] = _l('whatsapp_templates');
        $this->load->view('admin/whatsapp/templates', $data);
    }

    public function create_template()
    {
        $response = [];
        try {
            if (!has_permission('whatsapp_templates', '', 'create')) {
                access_denied('whatsapp_templates');
            }

            // Retrieve POST data
            $post_data = $this->input->post(); // Assuming CodeIgniter-like input handling

            // Validate required fields
            if (empty($post_data["template_name"]) || empty($post_data["template_subject"]) || empty($post_data["template_message"]) || !isset($post_data["status"])) {
                $response['status'] = '0';
                $response['message'] = "All fields (Template Name, Template Subject, Message, Status) are required.";
                echo json_encode($response);
                die;
            }

            if (!empty($post_data["template_id"])) {
                // Exclude the current template ID from the check if updating
                $this->db->where("id != ", $post_data["template_id"]);
            }

            $this->db->where("name", $post_data["template_name"]);
            $this->db->where("status", 1); // Assuming status = 1 means active templates
            $existing_template = $this->db->get(db_prefix() . "whatsapp_template")->row();

            if ($existing_template) {
                // If an existing template with the same name and active status is found
                $response['status'] = '0';
                $response['message'] = "WhatsApp template with the same name already exists";
                echo json_encode($response);
                die; // Stop further execution
            }


            // Prepare data for database operation
            $data = array(
                "name" => $post_data["template_name"],
                "subject" => $post_data["template_subject"],
                "message" => $post_data["template_message"],
                "status" => $post_data["status"],
                "updated_at" => date("Y-m-d H:i:s"),
                "updated_by" => get_staff_user_id()
            );

            // Determine if it's an update or insert operation
            if (!empty($post_data["template_id"])) {
                // Update operation
                $this->db->where("id", $post_data["template_id"]);
                if (!$this->db->update(db_prefix() . "whatsapp_template", $data)) {
                    throw new Exception('Failed to update the WhatsApp template.');
                }
            } else {
                // Insert operation
                if (!$this->db->insert(db_prefix() . "whatsapp_template", $data)) {
                    throw new Exception('Failed to create the WhatsApp template.');
                }
                $response['insert_id'] = $this->db->insert_id(); // Optional: Return the inserted ID
            }

            $response['status'] = '1';
            $response['message'] = 'WhatsApp template saved successfully.';
        } catch (Exception $e) {
            $response['status'] = '0';
            $response['message'] = $e->getMessage();
        }

        // Return JSON response
        echo json_encode($response);
    }

    public function delete_template()
    {
        $response = [];
        try {
            // Check permission
            if (!has_permission('whatsapp_templates', '', 'delete')) {
                throw new Exception('Access denied.');
            }

            // Retrieve POST data (assuming CodeIgniter-like input handling)
            $post_data = $this->input->post();

            // Validate required fields
            if (empty($post_data["id"])) {
                throw new Exception('Template ID not provided.');
            }

            // Sanitize the ID to prevent SQL injection
            $id = intval($post_data["id"]);

            // Perform deletion
            $this->db->where('id', $id);
            $this->db->delete(db_prefix() . "whatsapp_template");

            // Check if any rows were affected
            if ($this->db->affected_rows() > 0) {
                $response['status'] = '1';
                $response['message'] = 'WhatsApp template deleted successfully.';
            } else {
                throw new Exception('Failed to delete WhatsApp template.');
            }
        } catch (Exception $e) {
            // Handle exceptions
            $response['status'] = '0';
            $response['message'] = $e->getMessage();
        }

        // Return JSON response
        echo json_encode($response);
    }
}
