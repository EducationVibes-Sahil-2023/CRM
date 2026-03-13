<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Knowledge_base extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('knowledge_base_model');
        $this->load->model('knowledge_base_group_model');
    }

    /* List all knowledgebase articles */
    // public function index()
    // {
    //     if (!has_permission('knowledge_base', '', 'view')) {
    //         access_denied('knowledge_base');
    //     }
    //     if ($this->input->is_ajax_request()) {
    //         $this->app->get_table_data('kb_articles');
    //     }
    //     $data['groups']    = $this->knowledge_base_model->get_kbg();
    //     $data['bodyclass'] = 'top-tabs kan-ban-body';
    //     $data['title']     = _l('kb_string');
    //     $this->load->view('admin/knowledge_base/articles', $data);
    // }

    public function index()
    {
        if (!has_permission('knowledge_base', '', 'view_own') && !has_permission('knowledge_base', '', 'view') && !staff_has_assigned_knowledge_base()) {
            access_denied('knowledge_base');
        }
        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data('kb_articles');
            $this->app->get_table_data('knowledge_base');
        }
        // $data['groups']    = $this->knowledge_base_model->get_kbg();
        $data['knowledge_group']     = $this->knowledge_base_group_model->get_knowledge_groups('', ['status' => 1]);
        $data['bodyclass'] = 'top-tabs kan-ban-body';
        $data['title']     = _l('kb_string');
        $this->load->view('admin/knowledge_base/knowledge_base', $data);
    }

    public function edit_knowledge_base($id)
    {
        if (!has_permission('knowledge_base', '', 'create')) {
            access_denied('knowledge_base');
        }
        $data['knowledge']     = $this->db->select('*')->from(db_prefix() . 'knowledge')->where(array('id' => $id))->get()->result_array();
        $data['bodyclass'] = 'top-tabs kan-ban-body';
        $data['title']     = _l('kb_string');
        $data['knowledge_group']     = $this->knowledge_base_group_model->get_knowledge_groups('', ['status' => 1]);
        $this->load->view('admin/knowledge_base/create_knowledge_base', $data);
    }

    public function create_knowledge_base()
    {
        if (!has_permission('knowledge_base', '', 'create')) {
            access_denied('knowledge_base');
        }
        if ($this->input->is_ajax_request()) {
            $data = $this->input->post();
            $id = !empty($data["id"]) ? $data["id"] : '';
            $data['media'] = '';
            $data['group_id'] = !empty($data['group_id']) ? implode(",", $data['group_id']) : '';
            if (!empty($data["id"])) {

                if (!empty($_FILES["media"]['name'])) {
                    $upload_data["name"] = $_FILES["media"]['name'];
                    $upload_data["type"] = $_FILES["media"]['type'];
                    $upload_data["tmp_name"] = $_FILES["media"]['tmp_name'];
                    $upload_data["error"] = $_FILES["media"]['error'];
                    $upload_data["size"] = $_FILES["media"]['size'];
                    if ($upload_data["error"] === UPLOAD_ERR_OK) {

                        $filename = uniqid() . '_' . basename($upload_data['name']);
                        // Move the uploaded file to the desired directory
                        $destination = KNOWLEDGE_MEDIA_PATH . '/' . $filename;
                        if (move_uploaded_file($upload_data['tmp_name'], $destination)) {
                        } else {
                            set_alert('danger', 'Failed to uploaded media file.');
                        }
                        $media_url = base_url() . $destination;
                        $data['media'] = $media_url;
                    } else {
                        $data['resp_code'] = 'ERR';
                        $data['resp_desc'] = "Upload failed.";
                        set_alert('danger', "Upload failed.");
                        echo json_encode($data);
                        die;
                    }
                } else {
                    unset($data['media']);
                }
                unset($data['id']);

                $success =   $this->db->update(db_prefix() . 'knowledge', $data, array("id" => $id));
                if ($success == true) {
                    $message = _l('updated_successfully', _l('kb_string'));
                }
                echo json_encode([
                    'success' => $success,
                    'message' => $message,
                ]);
            } else {


                if (!empty($_FILES["media"]['name'])) {
                    $upload_data["name"] = $_FILES["media"]['name'];
                    $upload_data["type"] = $_FILES["media"]['type'];
                    $upload_data["tmp_name"] = $_FILES["media"]['tmp_name'];
                    $upload_data["error"] = $_FILES["media"]['error'];
                    $upload_data["size"] = $_FILES["media"]['size'];
                    if ($upload_data["error"] === UPLOAD_ERR_OK) {
                        $filename = uniqid() . '_' . basename($upload_data['name']);
                        // Move the uploaded file to the desired directory
                        $destination = KNOWLEDGE_MEDIA_PATH . '/' . $filename;
                        if (move_uploaded_file($upload_data['tmp_name'], $destination)) {
                        } else {
                            set_alert('danger', 'Failed to uploaded media file.');
                        }
                        $media_url = base_url() . $destination;
                        $data['media'] = $media_url;
                    } else {
                        $data['resp_code'] = 'ERR';
                        $data['resp_desc'] = "Upload failed.";
                        set_alert('danger', "Upload failed.");
                        echo json_encode($data);
                        die;
                    }
                } else {
                    unset($data['media']);
                }
                unset($data['id']);

                $insert = $this->db->insert(db_prefix() . 'knowledge', $data);
                $insert_id = $this->db->insert_id();
                if ($insert == true) {
                    $message = _l('added_successfully', _l('kb_string'));
                }
                echo json_encode([
                    'success' => $insert_id ? true : false,
                    'message' => $message,
                    'id'      => $insert_id,
                    'name'    => $data['title'],
                ]);
            }

            die;
        }
        // $data['groups']    = $this->knowledge_base_model->get_kbg();

        $data['bodyclass'] = 'top-tabs kan-ban-body';
        $data['title']     = _l('kb_string');
        $data['knowledge_group']     = $this->knowledge_base_group_model->get_knowledge_groups('', ['status' => 1]);
        $this->load->view('admin/knowledge_base/create_knowledge_base', $data);
    }
    
public function download_folder()
{
    if (!isset($_GET['folder'])) {
        show_error('Folder parameter missing');
    }

    // decode folder
    $folder_name = base64_decode($_GET['folder']);
    $folder_name = str_replace(['..'], '', $folder_name);

    $basePath = FCPATH . "uploads/knowledge_base/fees_structures";
    $folderPath = $basePath;

    if (!is_dir($folderPath)) {
        show_error('Folder not found');
    }

    $zipName = basename($folder_name) . ".zip";
    $zipPath = sys_get_temp_dir() . "/" . $zipName;

    // create zip using server command (VERY FAST)
    $command = "cd " . escapeshellarg(dirname($folderPath)) .
               " && zip -r " . escapeshellarg($zipPath) .
               " " . escapeshellarg(basename($folderPath));

    exec($command);

    if (!file_exists($zipPath)) {
        show_error("Zip creation failed");
    }

    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename="'.$zipName.'"');
    header('Content-Length: ' . filesize($zipPath));

    readfile($zipPath);

    unlink($zipPath);
    exit;
}

    public function manage_knowledge_groups()
    {
        if (!has_permission('knowledge_base', '', 'view') && !has_permission('knowledge_base', '', 'view_own')  && !staff_has_assigned_knowledge_base()) {
            access_denied('knowledge_base');
        }
        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data('knowledge_groups');
        }
        $data['title'] = _l('customer_groups');
        $data['department']  = $this->db->where("status", 1)->get(db_prefix() . "staff_department")->result_array();

        $data['members']     = $this->staff_model->get('', ['is_not_staff' => 0, 'active' => 1]);
        $this->load->view('admin/knowledge_base/manage_knowledge_groups', $data);
    }


    /* Delete knowledge group */
    public function delete_kmowledge_group($id)
    {
        if (!has_permission('knowledge_base', '', 'delete')) {
            access_denied('knowledge_base');
        }
        $delete      = $this->knowledge_base_group_model->delete($id);
        if ($delete) {
            set_alert('success', _l('deleted', _l('kb_dt_group_name')));
            redirect(admin_url('knowledge_base/manage_knowledge_groups'));
        }
    }


    public function create_folder()
    {
        // Check permission for viewing folders
        if (!empty($_POST["show_folder"]) && $_POST["show_folder"] == 1) {
            if (!has_permission('knowledge_base', '', 'view') && !has_permission('knowledge_base', '', 'view_own') && !staff_has_assigned_knowledge_base()) {
                access_denied('knowledge_base');
                $response = array(
                    "success" => 0,
                    "message" => "Access denied"
                );
                echo json_encode($response);
                die;
            }

            $parent_id = !empty($_POST["index"]) ? $_POST["index"] : 0;
            $folders = $this->knowledge_base_group_model->get_folders(array("f.status" => 1, "f.parent_id" => $parent_id));
            $files = $this->knowledge_base_group_model->get_files(array("fs.status" => 1, "fs.folder_id" => $parent_id));

            // Prepare response
            $response = array(
                "success" => true,
                "folder" => $folders,
                "files" => $files,
                "message" => "Folders list",
                "errorText" => "",
                "errorCode" => "",
                "result" => $folders
            );
            echo json_encode($response);
            die;
        }

        // Check permission for creating folders
        if (!has_permission('knowledge_base', '', 'create')) {
            access_denied('knowledge_base');
            $response = array(
                "success" => 0,
                "message" => "Access denied"
            );
            echo json_encode($response);
            die;
        }

        // Validate folder name and group IDs
        if (empty($_POST["folder_names"]) || empty($_POST["group_id"])) {
            $response = array(
                "success" => 0,
                "message" => "Error: No valid folder names provided"
            );
            echo json_encode($response);
            die;
        }

        // Get folder name and group IDs from POST data
        $folder_names = str_replace("//", "/", explode("/", trim($_POST["folder_names"])));
        $group_id = $_POST["group_id"];
        $parent_id = !empty($_POST["index"]) ? $_POST["index"] : 0;
        $folder_id = !empty($_POST["folder_id"]) ? $_POST["folder_id"] : 0;

        // Initialize data array for batch insertion
        $data = [];
        $existing_folders = [];

        // Iterate over folder names
        foreach ($folder_names as $folder_name) {
            // Validate each folder name
            if (!empty($folder_name)) {
                // Check if the folder already exists
                $existingFolder = $this->db->get_where(db_prefix() . "knowledge_base_folder", [
                    'name' => $folder_name,
                    'parent_id' => $parent_id
                ])->row_array();

                if (!$existingFolder) {
                    // Prepare data for insertion
                    $data[] = array(
                        'name' => $folder_name,
                        'status' => 1,
                        'created_by' => get_staff_user_id(),
                        'created_date' => date('Y-m-d H:i:s'),
                        'parent_id' => $parent_id,
                        'group_ids' => !empty($group_id) ? implode(",", $group_id) : '' // Assuming group_ids is an array
                    );

                    // Create directory if it doesn't exist
                    $dirPath = str_replace("//", "/", KNOWLEDGE_BASE_MEDIA_PATH . $folder_name);
                    if (!is_dir($dirPath)) {
                        mkdir($dirPath, 0777, true);
                    }
                } else {
                    $existing_folders[] = $folder_name;
                    if (!empty($folder_id) && $folder_id == $existingFolder["id"]) {
                        $data[] = array(
                            'id' => $folder_id,
                            'name' => $folder_name,
                            'status' => 1,
                            'parent_id' => $parent_id,
                            'updated_by' => get_staff_user_id(),
                            'updated_date' => date('Y-m-d H:i:s'),
                            'group_ids' => !empty($group_id) ? implode(",", $group_id) : '' // Assuming group_ids is an array
                        );
                    } else {
                        $this->db->update(db_prefix() . "knowledge_base_folder", array("updated_by" => get_staff_user_id(), "updated_date" => date('Y-m-d H:i:s')), array("id" => $existingFolder["id"]));
                    }
                }
            }
        }

        // Check if there's any data to insert or update
        if (!empty($data)) {
            // Perform batch insertion or update
            if (!empty($folder_id)) {
                $this->db->update_batch(db_prefix() . "knowledge_base_folder", $data, 'id');
                $message = "Folder updated successfully";
            } else {
                $this->db->insert_batch(db_prefix() . "knowledge_base_folder", $data);
                $message = "Folder created successfully";
            }

            // Fetch inserted folders
            $folders = $this->knowledge_base_group_model->get_folders(array("f.status" => 1, "f.parent_id" => $parent_id));
            $files = $this->knowledge_base_group_model->get_files(array("fs.status" => 1, "fs.folder_id" => $parent_id));

            // Prepare response
            $response = array(
                "success" => 1,
                "folder" => $folders,
                "files" => $files,
                "message" => $message
            );
        } else {
            // No valid data to insert
            $response = array(
                "success" => 0,
                "message" => "Error: No valid folder names provided" . (!empty($existing_folders) ? " (Folders already exist: " . implode(", ", $existing_folders) . ")" : "")
            );
        }

        // Send JSON response
        echo json_encode($response);
    }


    // public function create_folder()
    // {
    //     // Check permission


    //     if (!empty($_POST["show_folder"]) && $_POST["show_folder"] == 1) {
    //         if (!has_permission('knowledge_base', '', 'view' && !has_permission('knowledge_base', '', 'view_own')) && !staff_has_assigned_knowledge_base()) {
    //             access_denied('knowledge_base');
    //             $response = array(
    //                 "success" => 0,
    //                 "message" => "Access denied"
    //             );
    //             echo json_encode($response);
    //             die;
    //         }

    //         $parent_id = !empty($_POST["index"]) ? $_POST["index"] : 0;
    //         // $folders = $this->db->select("*")->where(array("status" => 1, "parent_id" => $parent_id))->get(db_prefix() . "knowledge_base_folder")->result_array();
    //         $folders = $this->knowledge_base_group_model->get_folders(array("f.status" => 1, "f.parent_id" => $parent_id));
    //         // $files = $this->db->select("*")->where(array("status" => 1, "folder_id" => $parent_id))->get(db_prefix() . "knowledge_base_files")->result_array();
    //         $files = $this->knowledge_base_group_model->get_files(array("fs.status" => 1, "fs.folder_id" => $parent_id));

    //         // Prepare response
    //         $response = array(
    //             "success" => true,
    //             "folder" => $folders,
    //             "files" => $files,
    //             "message" => "Folders list",
    //             "errorText" => "",
    //             "errorCode" => "",
    //             "result" => $folders
    //         );
    //         echo json_encode($response);
    //         die;
    //     }


    //     if (!has_permission('knowledge_base', '', 'create')) {
    //         access_denied('knowledge_base');
    //         $response = array(
    //             "success" => 0,
    //             "message" => "Access denied"
    //         );
    //         echo json_encode($response);
    //         die;
    //     }

    //     // Validate folder name and group IDs
    //     if (empty($_POST["folder_names"]) || empty($_POST["group_id"])) {
    //         $response = array(
    //             "success" => 0,
    //             "message" => "Error: No valid folder names provided"
    //         );
    //         echo json_encode($response);
    //         die;
    //     }


    //     // Get folder name and group IDs from POST data
    //     $folder_names =  str_replace("//", "/", explode("/", trim($_POST["folder_names"])));
    //     $group_id = $_POST["group_id"];
    //     $parent_id = !empty($_POST["index"]) ? $_POST["index"] : 0;
    //     $folder_id = !empty($_POST["folder_id"]) ? $_POST["folder_id"] : 0;


    //     // Initialize data array for batch insertion
    //     $data = [];

    //     // Iterate over folder names
    //     foreach ($folder_names as $folder_name) {
    //         // Validate each folder name
    //         if (!empty($folder_name)) {
    //             // Check if the folder already exists
    //             $existingFolder = $this->db->get_where(db_prefix() . "knowledge_base_folder", ['name' => $folder_name])->row_array();

    //             if (!$existingFolder) {
    //                 // Prepare data for insertion
    //                 $data[] = array(
    //                     'name' => $folder_name,
    //                     'status' => 1,
    //                     'created_by' => get_staff_user_id(),
    //                     'created_date' => date('Y-m-d H:i:s'),
    //                     'parent_id' => $parent_id,
    //                     'group_ids' => !empty($group_id) ? implode(",", $group_id) : '' // Assuming group_ids is an array
    //                 );

    //                 // Create directory if it doesn't exist
    //                 $dirPath = str_replace("//", "/", KNOWLEDGE_BASE_MEDIA_PATH . $folder_name);
    //                 if (!is_dir($dirPath)) {
    //                     mkdir($dirPath, 0777, true);
    //                 }
    //             } else {

    //                 if (!empty($folder_id)) {
    //                     if ($folder_id ==  $existingFolder["id"]) {
    //                         $data[] = array(
    //                             'id' => $folder_id,
    //                             'name' => $folder_name,
    //                             'status' => 1,
    //                             'parent_id' => $parent_id,
    //                             'updated_by' => get_staff_user_id(),
    //                             'updated_date' => date('Y-m-d H:i:s'),
    //                             'group_ids' => !empty($group_id) ? implode(",", $group_id) : '' // Assuming group_ids is an array
    //                         );
    //                     }
    //                 }
    //                 $this->db->update(db_prefix() . "knowledge_base_folder", array("updated_by" => get_staff_user_id(), "updated_date" => date('Y-m-d H:i:s')), array("id" => $existingFolder["id"]));
    //             }
    //         }
    //     }


    //     // Check if there's any data to insert
    //     if (!empty($data)) {
    //         // Perform batch insertion
    //         if (!empty($folder_id)) {
    //             $this->db->update_batch(db_prefix() . "knowledge_base_folder", $data, 'id');
    //         } else {
    //             $this->db->insert_batch(db_prefix() . "knowledge_base_folder", $data);
    //         }

    //         // Fetch inserted folders
    //         $folders =  $this->knowledge_base_group_model->get_folders(array("f.status" => 1, "f.parent_id" => $parent_id));
    //         // $files = $this->db->select("*")->where(array("status" => 1, "folder_id" => $parent_id))->get(db_prefix() . "knowledge_base_files")->result_array();
    //         $files = $this->knowledge_base_group_model->get_files(array("fs.status" => 1, "fs.folder_id" => $parent_id));

    //         $message = "Folder create successfully";
    //         if (!empty($folder_id)) {
    //             $message = "Folder update successfully";
    //         }
    //         // Prepare response
    //         $response = array(
    //             "success" => 1,
    //             "folder" => $folders,
    //             "files" => $files,
    //             "message" => $message
    //         );
    //     } else {
    //         // No valid data to insert
    //         // Handle this case
    //         $response = array(
    //             "success" => 0,
    //             "message" => "Error: No valid folder names provided"
    //         );
    //     }

    //     // Send JSON response
    //     echo json_encode($response);
    // }

    public function upload_dir_data()
    {

        // Check permission
        if (!has_permission('knowledge_base', '', 'create')) {
            access_denied('knowledge_base');
            $response = array(
                "success" => 0,
                "message" => "Access denied"
            );
            echo json_encode($response);
            die;
        }

        $current_dir = !empty($_POST["current_dir"]) ? $_POST["current_dir"] : '';

        $folder_id = !empty($_POST["index"]) ? $_POST["index"] : 0;
        $data = [];


        if (!empty($_FILES["files"])) {
            foreach ($_FILES["files"]["name"] as $key => $filename) {
                $file_type = pathinfo($filename, PATHINFO_EXTENSION); // Get file extension
                $basename = pathinfo($filename, PATHINFO_FILENAME); // Get file name without extension
                $tmp_name = $_FILES["files"]["tmp_name"][$key];
                $error = $_FILES["files"]["error"][$key];

                $upload_data["name"] = $filename;
                $upload_data["type"] = $_FILES["files"]["type"][$key];
                $upload_data["tmp_name"] = $tmp_name;
                $upload_data["error"] = $error;
                $size = $upload_data["size"] = $_FILES["files"]["size"][$key];

                if ($error === UPLOAD_ERR_OK) {
                    // Move the uploaded file to the desired directory
                    $destination = KNOWLEDGE_BASE_MEDIA_PATH . $current_dir .time()."_".$filename;

                    if (move_uploaded_file($tmp_name, $destination)) {
                        $media_url = base_url() . $destination;
                        $data[] = array("path" => $media_url, "size" => $size, "type" => $file_type, "name" => $basename, "folder_id" => $folder_id, "status" => 1, "created_by" => get_staff_user_id(), 'created_date' => date('Y-m-d H:i:s'));
                    }
                }
            }
        }

        if (!empty($folder_id)) {

            $folder_data = array("updated_by" => get_staff_user_id(), 'updated_date' => date('Y-m-d H:i:s'));
            $this->db->where('id', $folder_id);
            $this->db->update(db_prefix() . "knowledge_base_folder", $folder_data);
        }


        if (!empty($data)) {
            $this->db->insert_batch(db_prefix() . "knowledge_base_files", $data);
            $response = array(
                "success" => 1,
                "message" => "Files uploaded successfully"
            );
        } else {
            // No valid data to insert
            // Handle this case
            $response = array(
                "success" => 0,
                "message" => "Error"
            );
        }

        // Send JSON response
        echo json_encode($response);
    }

    public function delete()
    {
        if (!has_permission('knowledge_base', '', 'delete')) {
            access_denied('knowledge_base');
            $response = array(
                "success" => 0,
                "message" => "Access denied"
            );
            echo json_encode($response);
            die;
        }

        $id = isset($_POST["id"]) ? trim($_POST["id"]) : '';
        $type = isset($_POST["type"]) ? trim($_POST["type"]) : '';

        if ($id === '' || $type === '') {
            $response = array(
                "success" => 0,
                "message" => "Invalid input data"
            );
            echo json_encode($response);
            die;
        }

        $update_data = array(
            "status" => 0,
            "updated_by" => get_staff_user_id(),
            "updated_date" => date('Y-m-d H:i:s')
        );

        if ($type === "folder") {
            $this->db->where('id', $id);
            $this->db->update(db_prefix() . "knowledge_base_folder", $update_data);
            $response = array(
                "success" => 1,
                "message" => "Folder deleted successfully"
            );
        } elseif ($type === "file") {
            $this->db->where('id', $id);
            $this->db->update(db_prefix() . "knowledge_base_files", $update_data);
            $response = array(
                "success" => 1,
                "message" => "File deleted successfully"
            );
        } else {
            $response = array(
                "success" => 0,
                "message" => "Invalid type"
            );
        }

        echo json_encode($response);
    }


    public function knowledge_group()
    {


        if ($this->input->is_ajax_request()) {

            $data = $this->input->post();
            $data['staff_ids'] = !empty($data['staff_ids']) ? implode(",", array_unique($data['staff_ids'])) : '';

            $data['department'] = !empty($data['department']) ? implode(",", array_unique($data['department'])) : '';


            if ($data['id'] == '') {

                if (!has_permission('knowledge_base', '', 'create')) {
                    access_denied('knowledge_base');
                }

                $id      = $this->knowledge_base_group_model->add($data);
                $message = $id ? _l('added_successfully', _l('knowledge_group')) : '';
                echo json_encode([
                    'success' => $id ? true : false,
                    'message' => $message,
                    'id'      => $id,
                    'name'    => $data['name'],
                ]);
            } else {
                if (!has_permission('knowledge_base', '', 'create')) {
                    access_denied('knowledge_base');
                }


                $success = $this->knowledge_base_group_model->edit($data);
                $message = '';
                if ($success == true) {
                    $message = _l('updated_successfully', _l('knowledge_group'));
                }
                echo json_encode([
                    'success' => $success,
                    'message' => $message,
                ]);
            }
        }
    }

    /* Add new article or edit existing*/
    public function article($id = '')
    {
        if (!has_permission('knowledge_base', '', 'view')) {
            access_denied('knowledge_base');
        }
        if ($this->input->post()) {
            $data                = $this->input->post();
            $data['description'] = html_purify($this->input->post('description', false));

            if ($id == '') {
                if (!has_permission('knowledge_base', '', 'create')) {
                    access_denied('knowledge_base');
                }
                $id = $this->knowledge_base_model->add_article($data);
                if ($id) {
                    set_alert('success', _l('added_successfully', _l('kb_article')));
                    redirect(admin_url('knowledge_base/article/' . $id));
                }
            } else {
                if (!has_permission('knowledge_base', '', 'edit')) {
                    access_denied('knowledge_base');
                }
                $success = $this->knowledge_base_model->update_article($data, $id);
                if ($success) {
                    set_alert('success', _l('updated_successfully', _l('kb_article')));
                }
                redirect(admin_url('knowledge_base/article/' . $id));
            }
        }
        if ($id == '') {
            $title = _l('add_new', _l('kb_article_lowercase'));
        } else {
            $article         = $this->knowledge_base_model->get($id);
            $data['article'] = $article;
            $title           = _l('edit', _l('kb_article')) . ' ' . $article->subject;
        }

        $this->app_scripts->add('tinymce-stickytoolbar', site_url('assets/plugins/tinymce-stickytoolbar/stickytoolbar.js'));

        $data['bodyclass'] = 'kb-article';
        $data['title']     = $title;
        $this->load->view('admin/knowledge_base/article', $data);
    }

    public function view($slug)
    {
        if (!has_permission('knowledge_base', '', 'view')) {
            access_denied('View Knowledge Base Article');
        }

        $data['article'] = $this->knowledge_base_model->get(false, $slug);

        if (!$data['article']) {
            show_404();
        }

        $data['related_articles'] = $this->knowledge_base_model->get_related_articles($data['article']->articleid, false);

        add_views_tracking('kb_article', $data['article']->articleid);
        $data['title'] = $data['article']->subject;
        $this->load->view('admin/knowledge_base/view', $data);
    }

    public function add_kb_answer()
    {
        // This is for did you find this answer useful
        if (($this->input->post() && $this->input->is_ajax_request())) {
            echo json_encode($this->knowledge_base_model->add_article_answer($this->input->post('articleid'), $this->input->post('answer')));
            die();
        }
    }

    /* Change article active or inactive */
    public function change_article_status($id, $status)
    {
        if (has_permission('knowledge_base', '', 'edit')) {
            if ($this->input->is_ajax_request()) {
                $this->knowledge_base_model->change_article_status($id, $status);
            }
        }
    }

    public function update_kan_ban()
    {
        if (has_permission('knowledge_base', '', 'edit')) {
            if ($this->input->post()) {
                $success = $this->knowledge_base_model->update_kan_ban($this->input->post());
                $message = '';
                if ($success) {
                    $message = _l('updated_successfully', _l('kb_article'));
                }
                echo json_encode([
                    'success' => $success,
                    'message' => $message,
                ]);
                die();
            }
        }
    }

    public function change_group_color()
    {
        if (has_permission('knowledge_base', '', 'edit')) {
            if ($this->input->post()) {
                $this->knowledge_base_model->change_group_color($this->input->post());
            }
        }
    }

    /* Delete article from database */
    public function delete_article($id)
    {
        if (!has_permission('knowledge_base', '', 'delete')) {
            access_denied('knowledge_base');
        }
        if (!$id) {
            redirect(admin_url('knowledge_base'));
        }
        $response = $this->knowledge_base_model->delete_article($id);
        if ($response == true) {
            set_alert('success', _l('deleted', _l('kb_article')));
        } else {
            set_alert('warning', _l('problem_deleting', _l('kb_article_lowercase')));
        }
        redirect(admin_url('knowledge_base'));
    }

    /* View all article groups */
    public function manage_groups()
    {
        if (!has_permission('knowledge_base', '', 'view')) {
            access_denied('knowledge_base');
        }
        $data['groups'] = $this->knowledge_base_model->get_kbg();
        $data['title']  = _l('als_kb_groups');
        $this->load->view('admin/knowledge_base/manage_groups', $data);
    }

    /* Add or edit existing article group */
    public function group($id = '')
    {
        if (!has_permission('knowledge_base', '', 'view')) {
            access_denied('knowledge_base');
        }
        if ($this->input->post()) {
            $post_data        = $this->input->post();
            $article_add_edit = isset($post_data['article_add_edit']);
            if (isset($post_data['article_add_edit'])) {
                unset($post_data['article_add_edit']);
            }
            if (!$this->input->post('id')) {
                if (!has_permission('knowledge_base', '', 'create')) {
                    access_denied('knowledge_base');
                }
                $id = $this->knowledge_base_model->add_group($post_data);
                if (!$article_add_edit && $id) {
                    set_alert('success', _l('added_successfully', _l('kb_dt_group_name')));
                } else {
                    echo json_encode([
                        'id'      => $id,
                        'success' => $id ? true : false,
                        'name'    => $post_data['name'],
                    ]);
                }
            } else {
                if (!has_permission('knowledge_base', '', 'edit')) {
                    access_denied('knowledge_base');
                }

                $id = $post_data['id'];
                unset($post_data['id']);
                $success = $this->knowledge_base_model->update_group($post_data, $id);
                if ($success) {
                    set_alert('success', _l('updated_successfully', _l('kb_dt_group_name')));
                }
            }
            die;
        }
    }

    /* Change group active or inactive */
    public function change_group_status($id, $status)
    {
        if (has_permission('knowledge_base', '', 'edit')) {
            if ($this->input->is_ajax_request()) {
                $this->knowledge_base_model->change_group_status($id, $status);
            }
        }
    }

    public function update_groups_order()
    {
        if (has_permission('knowledge_base', '', 'edit')) {
            if ($this->input->post()) {
                $this->knowledge_base_model->update_groups_order();
            }
        }
    }

    /* Delete article group */
    public function delete_group($id)
    {
        if (!has_permission('knowledge_base', '', 'delete')) {
            access_denied('knowledge_base');
        }
        if (!$id) {
            redirect(admin_url('knowledge_base/manage_groups'));
        }
        $response = $this->knowledge_base_model->delete_group($id);
        if (is_array($response) && isset($response['referenced'])) {
            set_alert('danger', _l('is_referenced', _l('kb_dt_group_name')));
        } elseif ($response == true) {
            set_alert('success', _l('deleted', _l('kb_dt_group_name')));
        } else {
            set_alert('warning', _l('problem_deleting', mb_strtolower(_l('kb_dt_group_name'))));
        }
        redirect(admin_url('knowledge_base/manage_groups'));
    }

    public function get_article_by_id_ajax($id)
    {
        if ($this->input->is_ajax_request()) {
            echo json_encode($this->knowledge_base_model->get($id));
        }
    }


    public function get_knowledge_base_dir()
    {
        if ($_REQUEST["command"] == "GetDirContents") {
            $parameters = json_decode($_REQUEST["arguments"], true);
            $where_folder = [];
            $where_files = [];


            foreach ($parameters["pathInfo"] as  $data) {
                if (!empty($data["key"])) {
                    $where_folder["f.parent_id"] = $data["key"];
                    $where_folder["f.status"] = 1;

                    $where_files["fs.folder_id"] = $data["key"];
                    $where_files["fs.status"] = 1;
                }
                // $where_folder[$key] = $data;
            }

            if (empty($where_folder)) {
                $where_folder["f.parent_id"] = 0;
                $where_folder["f.status"] = 1;
                $where_files["fs.folder_id"] = 0;
                $where_files["fs.status"] = 1;
            }

            $response["result"] =  $this->knowledge_base_group_model->get_knowledge_base_dir($where_folder, $where_files);
            $response["success"] = true;
            $response["errorText"] = "";
            $response["errorCode"] = "";
        } else if ($_REQUEST["command"] == "Rename") {
            $parameters = json_decode($_REQUEST["arguments"], true);

            if (!isset($parameters["name"]) || empty($parameters["name"])) {
                $response["success"] = false;
                $response["errorText"] = "Name parameter is missing or empty.";
                $response["errorCode"] = "MISSING_NAME";
            } elseif (!isset($parameters["pathInfo"]) || empty($parameters["pathInfo"])) {
                $response["success"] = false;
                $response["errorText"] = "Path information parameter is missing or empty.";
                $response["errorCode"] = "MISSING_PATHINFO";
            } else {
                $rename = $parameters["name"];
                $lastArray = end($parameters["pathInfo"]);
                $name = isset($lastArray["name"]) ? $lastArray["name"] : '';
                $id = isset($lastArray["key"]) ? $lastArray["key"] : '';

                if (empty($name) || empty($id)) {
                    $response["success"] = false;
                    $response["errorText"] = "Invalid path information.";
                    $response["errorCode"] = "INVALID_PATHINFO";
                } else {
                    $data = $this->knowledge_base_group_model->get_knowledge_base_dir(array("id" => $id, "name" => $name), array("id" => $id, "name" => $name));

                    $update_data = array(
                        "name" => $rename,
                        "updated_by" => get_staff_user_id(),
                        "updated_date" => date('Y-m-d H:i:s')
                    );

                    if (!empty($data[0])) {
                        $table_name = (!empty($data[0]["isDirectory"]) && $data[0]["isDirectory"] == 1) ? db_prefix() . "knowledge_base_folder" : db_prefix() . "knowledge_base_files";
                        $this->db->update($table_name, $update_data, array("id" => $id));
                        $response["success"] = true;
                        $response["errorText"] = "";
                        $response["errorCode"] = "";
                    } else {
                        $response["success"] = false;
                        $response["errorText"] = "Data not found.";
                        $response["errorCode"] = "DATA_NOT_FOUND";
                    }
                }
            }

            $response["result"] = [];
        } else if ($_REQUEST["command"] == "Copy") {
            $parameters = json_decode($_REQUEST["arguments"], true);

            if (!isset($parameters["sourcePathInfo"]) || !isset($parameters["destinationPathInfo"])) {
                $response["success"] = false;
                $response["errorText"] = "Source or destination path information is missing.";
                $response["errorCode"] = "MISSING_PATHINFO";
            } else {
                $lastArray_source = end($parameters["sourcePathInfo"]);
                $source_name = isset($lastArray_source["name"]) ? $lastArray_source["name"] : '';
                $source_id = isset($lastArray_source["key"]) ? $lastArray_source["key"] : '';

                $lastArray_destination = end($parameters["destinationPathInfo"]);
                $des_name = isset($lastArray_destination["name"]) ? $lastArray_destination["name"] : '';
                $des_id = isset($lastArray_destination["key"]) ? $lastArray_destination["key"] : '';

                $source_data = $this->knowledge_base_group_model->get_knowledge_base_dir(array("id" => $source_id, "name" => $source_name), array("id" => $source_id, "name" => $source_name));
                $destination_data = $this->knowledge_base_group_model->get_knowledge_base_dir(array("id" => $des_id, "name" => $des_name), array("id" => $des_id, "name" => $des_name));

                if (empty($source_data) || empty($destination_data)) {
                    $response["success"] = false;
                    $response["errorText"] = "Source or destination data not found.";
                    $response["errorCode"] = "DATA_NOT_FOUND";
                } else {
                    $table_name = $destination_data["isDirectory"] ? db_prefix() . "knowledge_base_folder" : db_prefix() . "knowledge_base_files";
                    $type = $destination_data["isDirectory"] ? "folder" : "file";

                    if ($type == "file") {
                        $get_data = $this->db->where(array("id" => $source_id, "name" => $source_name))->get(db_prefix() . "knowledge_base_files")->row_array();
                        unset($get_data['id']);
                        unset($get_data['updated_by']);
                        unset($get_data['updated_date']);
                        $get_data['folder_id'] = $des_id;
                    } else if ($type == "folder") {
                        $get_data = $this->db->where(array("id" => $source_id, "name" => $source_name))->get(db_prefix() . "knowledge_base_folder")->row_array();
                        unset($get_data['id']);
                        unset($get_data['updated_by']);
                        unset($get_data['updated_date']);
                        $get_data['parent_id'] = $des_id;
                    }

                    // Add/update common fields
                    $get_data["created_by"] = get_staff_user_id();
                    $get_data["created_date"] = date('Y-m-d H:i:s');

                    // Insert data into the destination table
                    $insert = $this->db->insert($table_name, $get_data);

                    if ($insert) {
                        $response["success"] = true;
                        $response["errorText"] = "";
                        $response["errorCode"] = "";
                    } else {
                        $response["success"] = false;
                        $response["errorText"] = "Failed to copy data.";
                        $response["errorCode"] = "COPY_FAILED";
                    }
                }
            }
        } else if ($_REQUEST["command"] == "Move") {
            $parameters = json_decode($_REQUEST["arguments"], true);

            if (!isset($parameters["sourcePathInfo"]) || !isset($parameters["destinationPathInfo"])) {
                $response["success"] = false;
                $response["errorText"] = "Source or destination path information is missing.";
                $response["errorCode"] = "MISSING_PATHINFO";
            } else {
                $lastArray_source = end($parameters["sourcePathInfo"]);
                $source_name = isset($lastArray_source["name"]) ? $lastArray_source["name"] : '';
                $source_id = isset($lastArray_source["key"]) ? $lastArray_source["key"] : '';

                $lastArray_destination = end($parameters["destinationPathInfo"]);
                $des_name = isset($lastArray_destination["name"]) ? $lastArray_destination["name"] : '';
                $des_id = isset($lastArray_destination["key"]) ? $lastArray_destination["key"] : '';

                $source_data = $this->knowledge_base_group_model->get_knowledge_base_dir(array("id" => $source_id, "name" => $source_name), array("id" => $source_id, "name" => $source_name));
                $destination_data = $this->knowledge_base_group_model->get_knowledge_base_dir(array("id" => $des_id, "name" => $des_name), array("id" => $des_id, "name" => $des_name));

                if (empty($source_data) || empty($destination_data)) {
                    $response["success"] = false;
                    $response["errorText"] = "Source or destination data not found.";
                    $response["errorCode"] = "DATA_NOT_FOUND";
                } else {
                    $table_name = $destination_data["isDirectory"] ? db_prefix() . "knowledge_base_folder" : db_prefix() . "knowledge_base_files";
                    $type = $destination_data["isDirectory"] ? "folder" : "file";
                    $update_data = [];

                    if ($type == "file") {
                        $update_data["folder_id"] = $des_id;
                    } else if ($type == "folder") {
                        $update_data["parent_id"] = $des_id;
                    }

                    // Add/update common fields
                    $update_data["updated_by"] = get_staff_user_id();
                    $update_data["updated_date"] = date('Y-m-d H:i:s');

                    // Update data in the source table
                    $update = $this->db->update($table_name, $update_data, array("id" => $source_id));

                    if ($update) {
                        $response["success"] = true;
                        $response["errorText"] = "";
                        $response["errorCode"] = "";
                    } else {
                        $response["success"] = false;
                        $response["errorText"] = "Failed to move data.";
                        $response["errorCode"] = "MOVE_FAILED";
                    }
                }
            }
        } else {


            $response["result"] =  $this->knowledge_base_group_model->get_knowledge_base_dir([]);
            $response["success"] = true;
            $response["errorText"] = "";
            $response["errorCode"] = "";
        }

        echo json_encode($response, true);
    }
}
