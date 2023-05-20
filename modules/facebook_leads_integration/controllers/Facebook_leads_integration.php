<?php



use function GuzzleHttp\json_decode;



defined('BASEPATH') or exit('No direct script access allowed');

set_time_limit(0);



class Facebook_leads_integration extends ClientsController

{

    public function __construct()

    {

        parent::__construct();

        $this->load->library('session');
    }

    //zapier integration

    public function zapier()

    {

        file_put_contents('zapier.json', file_get_contents('php://input'));
    }



    // Webhook callback url 

    public function webhook()

    {

        file_put_contents('test.json', file_get_contents(file_get_contents("https://graph.facebook.com/v15.0/1365919237553627?access_token=EAAQIZBBbDDGMBAD1mDgRZAIGZBhgl6EibAez5djeqpqUsliMZADXl5zM4G3HQbbnScx9NVM74X0L9M2Qw6hqbGV3A1nvXs1XhbKS0ZCsmndgZA7vWx9Oc9zuZAz6Xfo7sCzprbxSNExT8ZAWN8ir0iy4SnydQnbmcH3ZBk4EpyM5eRYmWMeFeHYLOFrZCBRsww6ewZD")));

        if (isset($_REQUEST['hub_challenge'])) {

            $challenge = $_REQUEST['hub_challenge'];

            $verify_token = $_REQUEST['hub_verify_token'];

            if ($verify_token == get_option('verifytoken')) {

                echo htmlspecialchars($challenge);
            }
        } else {

            $this->session->set_userdata('fb_lead', file_get_contents(file_get_contents("https://graph.facebook.com/v15.0/1365919237553627?access_token=EAAQIZBBbDDGMBAD1mDgRZAIGZBhgl6EibAez5djeqpqUsliMZADXl5zM4G3HQbbnScx9NVM74X0L9M2Qw6hqbGV3A1nvXs1XhbKS0ZCsmndgZA7vWx9Oc9zuZAz6Xfo7sCzprbxSNExT8ZAWN8ir0iy4SnydQnbmcH3ZBk4EpyM5eRYmWMeFeHYLOFrZCBRsww6ewZD")));

            // print_r(file_get_contents('php://input'));die;

            // print_r();die;

            redirect('facebook_leads_integration/getLeadGenID');
        }
    }



    // returns the table of Facebook pages

    public function getTable()

    {



        $pages = $_POST['pages'];



        update_option('facebook_pages', json_encode($pages));

        $html = '<table class="table table-striped" id="pageTable">

        <thead>

            <tr>

                <th>' . _l('page_name') . '</th>

                <th>' . _l('action') . '</th>

            </tr>

        </thead>

        <tbody>';

        foreach ($pages as $page) {

            if (!in_array($page['id'] . get_option('appId'), json_decode(get_option('subscribed_pages')))) {



                $html .= '<tr> <td>' . $page["name"] . '</td> <td><input type="button" value="' . _l('fbleadssubscribe') . '" id="' . $page['id'] . '" onclick="subscribe (' . $page['id'] . ',\'' . $page["access_token"] . '\');" class="btn btn-info"></td> </tr>';
            } else {

                $html .= '<tr> <td>' . $page["name"] . '</td> <td><input type="button" value="' . _l('fbleadsunsubscribe') . '" id="' . $page['id'] . '" onclick="unsubscribeApps (' . $page['id'] . ',\'' . $page["access_token"] . '\');" class="btn btn-danger"></td> </tr>';
            }
        }



        $html .= '</tbody>

        </table>';

        // \modules\facebook_leads_integration\core\Apiinit::parse_module_url('facebook_leads_integration');

        // \modules\facebook_leads_integration\core\Apiinit::check_url('facebook_leads_integration');

        print_r($html);
    }

    // save Facebook leads data

    public function saveData($data, $status)

    {

        if ($status == true) {

            $lead = $data;
        } else {

            $lead =  file_get_contents(APP_MODULES_PATH . 'facebook_leads_integration/lead_data.json', TRUE);
        }

        // print_r($lead);die;

        $json = json_decode($lead);

        $fields = array();

        array_push($fields, 'name');

        // array_push($fields, 'full_name');

        array_push($fields, 'address');

        array_push($fields, 'title');

        array_push($fields, 'city');

        array_push($fields, 'email');

        array_push($fields, 'state');

        array_push($fields, 'website');

        array_push($fields, 'country');

        array_push($fields, 'phonenumber');

        // array_push($fields, 'phone_number');

        array_push($fields, 'zip');

        array_push($fields, 'company');

        array_push($fields, 'default_language');

        array_push($fields, 'description');

        array_push($fields, 'assigned');

        array_push($fields, 'source');

        array_push($fields, 'status');



        $custom_fields = array();

        foreach (get_custom_fields('leads') as $field) {

            $custom_fields[$field['id']] = $field['slug'];
        }



        $custom_fields_with_values = array();

        $custom_fields_with_values['leads'] = array();

        $data = array();

        foreach ($json->field_data as $field) {


            if (in_array($field->name, $fields)) {

                $data[$field->name] = $field->values[0];
            } elseif (in_array($field->name, $custom_fields)) {

                $id = array_search($field->name, $custom_fields);

                $custom_fields_with_values['leads'][$id] = $field->values[0];
            }
        }

        /*
        if (empty($data['name'])) {
            $data['name'] = $json->field_data[0]->values[0];
        }
        if (empty($data['phonenumber'])) {
            $data['phonenumber'] = $json->field_data[1]->values[0];
        }
        */

        $data['assigned'] = get_option("facebook_lead_assigned");

        $data['source'] = get_option("facebook_lead_source");

        $data['status'] = get_option("facebook_lead_status");



        $data['is_public'] = 0;





        if (!isset($data['country']) || isset($data['country']) && $data['country'] == '') {

            $data['country'] = 0;
        }



        if (isset($data['custom_contact_date'])) {

            unset($data['custom_contact_date']);
        }



        $data['dateadded']   = date('Y-m-d H:i:s');

        $data['addedfrom']   = get_staff_user_id();

        $this->db->insert(db_prefix() . 'leads', $data);

        $insert_id = $this->db->insert_id();
        $staffid = get_option("facebook_lead_assigned");

        $notifiedUsers = [];
        // foreach ($staff as $member) {
        if ($staffid != 0) {
            $notified = add_notification([
                'description'     => 'lead_imported_from_fb',
                'touserid'        => $staffid,
                'fromcompany'     => 1,
                'fromuserid'      => null,
                'additional_data' => serialize([
                    'FB Leads',
                ]),
                'link' => '#leadid=' . $insert_id,
            ]);
            if ($notified) {
                array_push($notifiedUsers, $staffid);
            }
        }
        // }
        pusher_trigger_notification($notifiedUsers);


        // Save custom fields

        if (isset($custom_fields)) {

            handle_custom_fields_post($insert_id, $custom_fields_with_values);
        }
    }

    // Store pages id which are subscribed 

    public function pageSubscribed()

    {

        // \modules\facebook_leads_integration\core\Apiinit::parse_module_url('facebook_leads_integration');

        // \modules\facebook_leads_integration\core\Apiinit::check_url('facebook_leads_integration');

        $pages = json_decode(get_option('subscribed_pages'));

        $page_id = $_POST['id'];

        $app_id = get_option('appId');

        // $page=$pages[]

        array_push($pages, $page_id . $app_id);

        update_option('subscribed_pages', json_encode($pages));

        print_r(json_encode($pages));
    }

    // Exclude pages id which are Unsubscribed 

    public function pageUnSubscribed()

    {



        $pages = json_decode(get_option('subscribed_pages'));

        $page_id = $_POST['id'];

        $app_id = get_option('appId');





        $pages = array_diff($pages, [$page_id . $app_id]);

        $new_pages = array();

        foreach ($pages as $page) {



            if ($page != $page_id . $app_id) {

                array_push($new_pages, $page);
            }
        }





        update_option('subscribed_pages', json_encode($new_pages));

        print_r(json_encode($new_pages));
    }

    // Save and update Long live Facebook access token

    public function saveToken()

    {

        update_option('longLifeAccessToken', $_POST['data']);
    }

    // Returns the leadgen_id of a facebook lead

    public function getLeadGenID()

    {

        // $this->session->unset_userdata('fb_lead');
        // print_r($this->session->userdata('fb_lead'));die;
        if ($this->session->userdata('fb_lead')) {
            $data = json_decode($this->session->userdata('fb_lead'));

            $id = $data->entry[0]->changes[0]->value->leadgen_id;

            if ($id == "444444444444") {

                echo htmlspecialchars($this->saveData($id, false));
            } else {

                echo htmlspecialchars($this->get_lead_data($id));
            }

            $this->session->unset_userdata('fb_lead');
        } else {
            echo "data not available";
        }
    }

    // returns facebook lead data

    public function get_lead_data($id = 407118556715098)

    {



        require_once APP_MODULES_PATH . 'facebook_leads_integration/src/Facebook/autoload.php';





        $fb = new Facebook\Facebook([

            'app_id' => get_option('appId'),

            'app_secret' => get_option('appSecret'),

            'default_graph_version' => 'v5.0',



        ]);



        try {

            // Returns a `Facebook\FacebookResponse` object

            $response = $fb->get(

                '/' . $id,

                get_option('longLifeAccessToken')



            );
        } catch (Facebook\Exceptions\FacebookResponseException $e) {

            echo htmlspecialchars('Graph returned an error: ' . $e->getMessage());

            exit;
        } catch (Facebook\Exceptions\FacebookSDKException $e) {

            echo htmlspecialchars('Facebook SDK returned an error: ' . $e->getMessage());

            exit;
        }

        $graphNode = $response;



        echo htmlspecialchars($this->saveData($graphNode->getBody(), true));
    }

    // Save dummy data against test_lead

    public function dumyData()

    {

        redirect('facebook_leads_integration/saveData');
    }



    public function updateFields()

    {

        $id = $_POST['id'];

        $view_assigned = $_POST['view_assigned'];

        $view_source = $_POST['view_source'];

        $view_status = $_POST['view_status'];



        if ($id == 1) {

            update_option('facebook_lead_assigned', $view_assigned);
        } elseif ($id == 2) {

            update_option('facebook_lead_source', $view_source);
        } else {

            update_option('facebook_lead_status', $view_status);
        }
    }

    public function new_webhook()

    {

        $app_version = FACEBOOK_VERSION;
        $key = FORM_KEY;
        $access_token = FACEBOOK_ACCESS_TOKEN;

        // Verify the webhook by returning the challenge value
        if ($this->input->get('hub_mode') === 'subscribe' && $this->input->get('hub_verify_token') === 'token99099') {
            echo $this->input->get('hub_challenge');
        }
        // Handle incoming lead data
        if ($this->input->server('REQUEST_METHOD') === 'POST') {
            $json = file_get_contents('php://input');
            $lead_data = json_decode($json, true);

            $lead_data_response = [];
            if (!empty($lead_data['entry'][0]['changes'][0]['value']['leadgen_id'])) {
                $leadgen_id =  $lead_data['entry'][0]['changes'][0]['value']['leadgen_id'];
                $form_name = "";
                $form_id =  !empty($lead_data['entry'][0]['changes'][0]['value']['form_id']) ? $lead_data['entry'][0]['changes'][0]['value']['form_id'] : '';
                $this->db->insert(db_prefix() . 'facebook_webhook_data', array("data" => json_encode($lead_data, true), "lengen_id" => $leadgen_id, "form_id" => $form_id));
                $lead_data_response = json_decode(file_get_contents("https://graph.facebook.com/$app_version/{$leadgen_id}?access_token=$access_token"), true);
                if (!empty($form_id)) {
                    $lead_form_response = json_decode(file_get_contents("https://graph.facebook.com/$app_version/{$form_id}?access_token=$access_token"), true);
                    $form_name = !empty($lead_form_response["name"]) ? trim($lead_form_response["name"]) : "";
                }
                $this->db->insert(db_prefix() . 'facebook_leads_logs', array("lead_details" => json_encode($lead_data, true), "lead_data" => json_encode($lead_data_response, true), "ledgen_id" => $leadgen_id, "form_name" => $form_name, "form_id" => $form_id, "datetime" => date("Y-m-d h:i:s")));

                $lead_data_array = [];
                $token = $this->security->get_csrf_hash();
                if (!empty($lead_data_response["field_data"])) {
                    $lead_data_array["facebook_status"] = 1;
                    $lead_data_array["csrf_token_name"] = $token;
                    $lead_data_array["key"] = $key;
                    $lead_data_array["website"] = $form_name;

                    foreach ($lead_data_response["field_data"] as $field_data) {
                        if (!empty($field_data["name"])) {
                            if ($field_data["name"] == "full_name") {
                                $lead_data_array["name"] = !empty($field_data["values"][0]) ? $field_data["values"][0] : '';
                            } else if ($field_data["name"] == "phone_number") {
                                $lead_data_array["phonenumber"] = !empty($field_data["values"][0]) ? $field_data["values"][0] : '';
                            } else if ($field_data["name"] == "email") {
                                $lead_data_array["email"] = !empty($field_data["values"][0]) ? $field_data["values"][0] : '';
                            } else if (strpos(strtolower($field_data["name"]), "intake") !== false) {
                                $lead_data_array["form-cf-24"] = !empty($field_data["values"][0]) ? $field_data["values"][0] : '';
                            } else if (strpos(strtolower($field_data["name"]), "country") !== false) {
                                $lead_data_array["form-cf-32"] = !empty($field_data["values"][0]) ? $field_data["values"][0] : '';
                            } else if (strpos(strtolower($field_data["name"]), "course") !== false) {
                                $lead_data_array["form-cf-16"] = !empty($field_data["values"][0]) ? $field_data["values"][0] : '';
                            } else if (strpos(strtolower($field_data["name"]), "neet") !== false) {
                                $lead_data_array["form-cf-8"] = !empty($field_data["values"][0]) ? $field_data["values"][0] : '';
                            } else if (strpos(strtolower($field_data["name"]), "passing") !== false) {
                                $lead_data_array["form-cf-18"] = !empty($field_data["values"][0]) ? $field_data["values"][0] : '';
                            } else if (strpos(strtolower($field_data["name"]), "city") !== false) {
                                $lead_data_array["city"] = !empty($field_data["values"][0]) ? $field_data["values"][0] : '';
                            } else if (strpos(strtolower($field_data["name"]), "state") !== false) {
                                $lead_data_array["state"] = !empty($field_data["values"][0]) ? $field_data["values"][0] : '';
                            }
                        }
                    }

                    $lead_data_array["type"] = '';
                    $lead_type_array  = $this->staff_model->get_type();
                    if (!empty($lead_type_array)) {
                        foreach ($lead_type_array as $l) {
                            if (!empty($l["name"]) && strpos(strtolower($lead_form_response["name"]), strtolower(trim($l["name"]))) !== false) {
                                $lead_data_array["type"] = !empty($l["id"]) ? $l["id"] : '';
                            }
                        }
                    }


                    $ch = curl_init();
                    $url = base_url("forms/wtl/" . $key);
                    curl_setopt($ch, CURLOPT_URL, $url);
                    $headers = array(
                        'X-CSRF-TOKEN: ' . $token
                    );
                    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                    curl_setopt($ch, CURLOPT_POST, 1);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $lead_data_array);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    $result = curl_exec($ch);
                    // Check for errors
                    if (curl_errno($ch)) {
                        echo 'cURL error: ' . curl_error($ch);
                    }
                    curl_close($ch);
                }
            }
        }
    }


    public function new_webhook_test()

    {

        $app_version = "v16.0";
        $key = "a0b28a40a2a6933b4617bb7ceb29f0a9";
        $access_token = "EAAsKfvIyezoBAK8j2nCSmxFL3E7mhZAZBNXyTeuVmWm0DW2sNS7tX0PFerye8fnmCydZBNQat1spVfRZBhHwgojl3nj70hgOhjgmfPwBf5jgEfBd0UInsXzYpVJXl9Atd5sRZCAmvZBzWHOPIMJT6x6zFK9OSX3z8f8TgsuuOCnjQat3r0WzAZCGLvwHZA6DJR0ZD";

        // Verify the webhook by returning the challenge value
        if ($this->input->get('hub_mode') === 'subscribe' && $this->input->get('hub_verify_token') === 'token99099') {
            echo $this->input->get('hub_challenge');
        }
        // Handle incoming lead data
        if ($this->input->server('REQUEST_METHOD') === 'POST') {

            $lead_data_response = [];
            $leadgen_id =  $_POST["leadgen_id"];
            $form_name = "";
            $form_id =  $_POST["form_id"];
            $this->db->insert(db_prefix() . 'facebook_webhook_data', array("data" => json_encode($lead_data, true), "lengen_id" => $leadgen_id, "form_id" => $form_id));
            $lead_data_response = json_decode(file_get_contents("https://graph.facebook.com/$app_version/{$leadgen_id}?access_token=$access_token"), true);
            if (!empty($form_id)) {
                $lead_form_response = json_decode(file_get_contents("https://graph.facebook.com/$app_version/{$form_id}?access_token=$access_token"), true);
                $form_name = !empty($lead_form_response["name"]) ? trim($lead_form_response["name"]) : "";

                $this->db->insert(db_prefix() . 'facebook_leads_logs', array("lead_details" => json_encode($lead_data, true), "lead_data" => json_encode($lead_data_response, true), "ledgen_id" => $leadgen_id, "form_name" => $form_name, "form_id" => $form_id));

                $lead_data_array = [];
                $token = $this->security->get_csrf_hash();
                if (!empty($lead_data_response["field_data"])) {
                    $lead_data_array["facebook_status"] = 1;
                    $lead_data_array["csrf_token_name"] = $token;
                    $lead_data_array["key"] = $key;
                    $lead_data_array["website"] = $form_name;

                    foreach ($lead_data_response["field_data"] as $field_data) {
                        if (!empty($field_data["name"])) {
                            if ($field_data["name"] == "full_name") {
                                $lead_data_array["name"] = !empty($field_data["values"][0]) ? $field_data["values"][0] : '';
                            } else if ($field_data["name"] == "phone_number") {
                                $lead_data_array["phonenumber"] = !empty($field_data["values"][0]) ? $field_data["values"][0] : '';
                            } else if ($field_data["name"] == "email") {
                                $lead_data_array["email"] = !empty($field_data["values"][0]) ? $field_data["values"][0] : '';
                            } else if (strpos(strtolower($field_data["name"]), "intake") !== false) {
                                $lead_data_array["form-cf-24"] = !empty($field_data["values"][0]) ? $field_data["values"][0] : '';
                            } else if (strpos(strtolower($field_data["name"]), "country") !== false) {
                                $lead_data_array["form-cf-32"] = !empty($field_data["values"][0]) ? $field_data["values"][0] : '';
                            } else if (strpos(strtolower($field_data["name"]), "course") !== false) {
                                $lead_data_array["form-cf-16"] = !empty($field_data["values"][0]) ? $field_data["values"][0] : '';
                            } else if (strpos(strtolower($field_data["name"]), "neet") !== false) {
                                $lead_data_array["form-cf-8"] = !empty($field_data["values"][0]) ? $field_data["values"][0] : '';
                            } else if (strpos(strtolower($field_data["name"]), "passing") !== false) {
                                $lead_data_array["form-cf-18"] = !empty($field_data["values"][0]) ? $field_data["values"][0] : '';
                            } else if (strpos(strtolower($field_data["name"]), "city") !== false) {
                                $lead_data_array["city"] = !empty($field_data["values"][0]) ? $field_data["values"][0] : '';
                            } else if (strpos(strtolower($field_data["name"]), "state") !== false) {
                                $lead_data_array["state"] = !empty($field_data["values"][0]) ? $field_data["values"][0] : '';
                            }
                        }
                    }

                    $lead_data_array["type"] = '';
                    $lead_type_array  = $this->staff_model->get_type();
                    if (!empty($lead_type_array)) {
                        foreach ($lead_type_array as $l) {
                            if (!empty($l["name"]) && strpos(strtolower($lead_form_response["name"]), strtolower(trim($l["name"]))) !== false) {
                                $lead_data_array["type"] = !empty($l["id"]) ? $l["id"] : '';
                            }
                        }
                    }



                    $ch = curl_init();
                    $url = base_url("forms/wtl/" . $key);
                    curl_setopt($ch, CURLOPT_URL, $url);
                    $headers = array(
                        'X-CSRF-TOKEN: ' . $token
                    );
                    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                    curl_setopt($ch, CURLOPT_POST, 1);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $lead_data_array);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    // execute cURL request and store response in a variable
                    $response = curl_exec($ch);

                    // check for cURL errors
                    if (curl_error($ch)) {

                        echo 'Error: ' . curl_error($ch);
                    }

                    // close cURL
                    curl_close($ch);

                    // output response data
                    echo $response;
                }
            }
        }
    }
}
