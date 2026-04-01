<?php

defined('BASEPATH') or exit('No direct script access allowed');

if (!function_exists('startsWith')) {
    /**
     * String starts with
     * @param  string $haystack
     * @param  string $needle
     * @return boolean
     */
    function startsWith($haystack, $needle)
    {
        return \app\services\utilities\Str::startsWith($haystack, $needle);
    }
}
function getBase64Image($path = "")
{
    if (empty($path)) {
        return "";
    }

    // If already base64, return as is
    if (strpos($path, 'data:image') === 0) {
        return $path;
    }

    // Validate URL or local file
    if (filter_var($path, FILTER_VALIDATE_URL) || file_exists($path)) {

        $imageData = @file_get_contents($path);

        if ($imageData === false) {
            return ""; // failed to fetch image
        }

        $type = pathinfo($path, PATHINFO_EXTENSION);
        $type = strtolower($type);

        // Default fallback
        if (!$type) {
            $type = "png";
        }

        return 'data:image/' . $type . ';base64,' . base64_encode($imageData);
    }

    return "";
}

if (!function_exists('endsWith')) {
    /**
     * String ends with
     * @param  string $haystack
     * @param  string $needle
     * @return boolean
     */
    function endsWith($haystack, $needle)
    {
        return \app\services\utilities\Str::endsWith($haystack, $needle);
    }
}

if (!function_exists('is_html')) {
    /**
     * Check if there is html in string
     */
    function is_html($string)
    {
        return \app\services\utilities\Str::isHtml($string);
    }
}

if (!function_exists('strafter')) {
    /**
     * Get string after specific charcter/word
     * @param  string $string    string from where to get
     * @param  substring $substring search for
     * @return string
     */
    function strafter($string, $substring)
    {
        return \app\services\utilities\Str::after($string, $substring);
    }
}

if (!function_exists('strbefore')) {
    /**
     * Get string before specific charcter/word
     * @param  string $string    string from where to get
     * @param  substring $substring search for
     * @return string
     */
    function strbefore($string, $substring)
    {
        return \app\services\utilities\Str::before($string, $substring);
    }
}

if (!function_exists('is_connected')) {
    /**
     * Is internet connection open
     * @param  string  $domain
     * @return boolean
     */
    function is_connected($domain = 'www.google.com')
    {
        return \app\services\utilities\Utils::isConnected($domain);
    }
}

if (!function_exists('str_lreplace')) {
    /**
     * Replace Last Occurence of a String in a String
     * @since  Version 1.0.1
     * @param  string $search  string to be replaced
     * @param  string $replace replace with
     * @param  string $subject [the string to search
     * @return string
     */
    function str_lreplace($search, $replace, $subject)
    {
        return \app\services\utilities\Str::replaceLast($search, $replace, $subject);
    }
}

if (!function_exists('get_string_between')) {
    /**
     * Get string bettween words
     * @param  string $string the string to get from
     * @param  string $start  where to start
     * @param  string $end    where to end
     * @return string formatted string
     */
    function get_string_between($string, $start, $end)
    {
        return \app\services\utilities\Str::between($string, $start, $end);
    }
}

if (!function_exists('time_ago_specific')) {
    /**
     * Format datetime to time ago with specific hours mins and seconds
     * @param  datetime $lastreply
     * @param  string $from      Optional
     * @return mixed
     */
    function time_ago_specific($date, $from = 'now')
    {
        return \app\services\utilities\Date::timeAgo($date, $from);
    }
}

if (!function_exists('sec2qty')) {
    /**
     * Format seconds to quantity
     * @param  mixed  $sec      total seconds
     * @return [integer]
     */
    function sec2qty($sec)
    {
        $qty = \app\services\utilities\Format::sec2qty($sec);

        return hooks()->apply_filters('sec2qty_formatted', $qty, $sec);
    }
}

if (!function_exists('seconds_to_time_format')) {
    /**
     * Format seconds to H:I:S
     * @param  integer $seconds         mixed
     * @param  boolean $include_seconds
     * @return string
     */
    function seconds_to_time_format($seconds = 0, $include_seconds = false)
    {
        return \app\services\utilities\Format::secondsToTime($seconds, $include_seconds);
    }
}

if (!function_exists('hours_to_seconds_format')) {
    /**
     * Converts hours to minutes timestamp
     * @param  mixed $hours     total hours in format HH:MM or HH.MMM
     * @return int
     */
    function hours_to_seconds_format($hours)
    {
        return \app\services\utilities\Format::hoursToSeconds($hours);
    }
}

if (!function_exists('ip_in_range')) {
    /**
     * Check whether ip is in range
     * @param  string $ip    ip address to check
     * @param  string $range range
     * @return boolean
     */
    function ip_in_range($ip, $range)
    {
        return \app\services\utilities\Utils::ipInRage($ip, $range);
    }
}

if (!function_exists('array_merge_recursive_distinct')) {
    /**
     * @since  2.3.4
     *
     * Array merge recursive distinct
     *
     * @param  array  &$array1
     * @param  array  &$array2
     * @return array
     */
    function array_merge_recursive_distinct(array &$array1, array &$array2)
    {
        return \app\services\utilities\Arr::merge_recursive_distinct($array1, $array2);
    }
}

if (!function_exists('array_to_object')) {
    /**
     * Convert array to oobject
     * @param  array $array array to convert
     * @return object
     */
    function array_to_object($array)
    {
        return \app\services\utilities\Arr::toObject($array);
    }
}

if (!function_exists('array_flatten')) {
    /**
     * Flatten multidimensional array
     * @param  array  $array
     * @return array
     */
    function array_flatten(array $array)
    {
        return \app\services\utilities\Arr::flatten($array);
    }
}

if (!function_exists('value_exists_in_array_by_key')) {
    /**
     * Check if value exist in array by key
     * @param  array $array
     * @param  string $key   key to check
     * @param  mixed $val   value
     * @return boolean
     */
    function value_exists_in_array_by_key($array, $key, $val)
    {
        return \app\services\utilities\Arr::valueExistsByKey($array, $key, $val);
    }
}

if (!function_exists('in_array_multidimensional')) {
    /**
     * Check if in array multidimensional
     * @param  array $array  array to perform the checks
     * @param  mixed $key    array key
     * @param  mixed $val    the value to check
     * @return boolean
     */
    function in_array_multidimensional($array, $key, $val)
    {
        return \app\services\utilities\Arr::inMultidimensional($array, $key, $val);
    }
}

if (!function_exists('in_object_multidimensional')) {
    /**
     * Check if in object multidimensional
     * @param  object $object  object to perform the checks
     * @param  mixed $key      object key
     * @param  mixed $val      the value to check
     * @return boolean
     */
    function in_object_multidimensional($object, $key, $val)
    {
        foreach ($object as $item) {
            if (isset($item->{$key}) && $item->{$key} == $val) {
                return true;
            }
        }

        return false;
    }
}

if (!function_exists('array_pluck')) {
    /**
     *
     * @param  $array - data
     * @param  $key - value you want to pluck from array
     *
     * @return plucked array only with key data
     */
    function array_pluck($array, $key)
    {
        return \app\services\utilities\Arr::pluck($array, $key);
    }
}

if (!function_exists('adjust_color_brightness')) {
    /**
     * Adjust color brightness
     * @param  string $hex   hex color to adjust from
     * @param  mixed $steps eq -20 or 20
     * @return string
     */
    function adjust_color_brightness($hex, $steps)
    {
        return \app\services\utilities\Utils::adjustColorBrightness($hex, $steps);
    }
}

if (!function_exists('hex2rgb')) {
    /**
     * Convert hex color to rgb
     * @param  string $color color hex code
     * @return string
     */
    function hex2rgb($color)
    {
        return \app\services\utilities\Utils::hex2rgb($color);
    }
}

if (!function_exists('check_for_links')) {
    /**
     * Check for links/emails/ftp in string to wrap in href
     * @param  string $ret
     * @return string      formatted string with href in any found
     */
    function check_for_links($ret)
    {
        return \app\services\utilities\Str::clickable($ret);
    }
}

if (!function_exists('time_ago')) {
    /**
     * Short Time ago function
     * @param  datetime $date
     * @return mixed
     */
    function time_ago($date)
    {
        $CI = &get_instance();

        $localization = [];

        foreach (['time_ago_just_now', 'time_ago_minute', 'time_ago_minutes', 'time_ago_hour', 'time_ago_hours', 'time_ago_yesterday', 'time_ago_days', 'time_ago_week', 'time_ago_weeks', 'time_ago_month', 'time_ago_months', 'time_ago_year', 'time_ago_years'] as $langKey) {
            if (isset($CI->lang->language[$langKey])) {
                $localization[$langKey] = $CI->lang->language[$langKey];
            }
        }

        return \app\services\utilities\Date::timeAgoString($date, $localization);
    }
}

if (!function_exists('slug_it')) {
    /**
     * Slug function
     * @param  string $str
     * @param  array  $options Additional Options
     * @return mixed
     */
    function slug_it($str, $options = [])
    {
        $defaults = ['lang' => get_option('active_language')];
        $settings = array_merge($defaults, $options);

        return \app\services\utilities\Str::slug($str, $settings);
    }
}

if (!function_exists('similarity')) {
    /**
     * Check 2 string similarity
     * @param  string $str1
     * @param  string $str2
     * @return float
     */
    function similarity($str1, $str2)
    {
        return \app\services\utilities\Str::similarity($str1, $str2);
    }
}

/**
 * @since  2.3.0
 * Sort array by position
 * @param  array  $array     the arry to sort
 * @param  boolean $keepIndex whether to keep the indexes
 * @return array
 */
function app_sort_by_position($array, $keepIndex = false)
{
    return \app\services\utilities\Arr::sortBy($array, 'position', $keepIndex);
}

/**
 * Fill common empty attributes used for various function e.q. menu, tabs etc...
 * This is used e.q. if user didn't added icon array attribute but there are no checks performed if(iseet($item['icon'])) to prevent
 * throwing errors.
 * @param  array $array
 * @return array
 */
function app_fill_empty_common_attributes($array)
{
    $array['icon'] = isset($array['icon']) ? $array['icon'] : '';

    $array['href'] = isset($array['href']) && $array['href'] != '' ? $array['href'] : '#';

    $array['position'] = isset($array['position']) ? $array['position'] : null;

    return $array;
}

/**
 * Function that strip all html tags from string/text/html
 * @param  string $str
 * @param  string $allowed prevent specific tags to be stripped
 * @return string
 */
function strip_html_tags($str, $allowed = '')
{
    $str = preg_replace('/(<|>)\1{2}/is', '', $str);
    $str = preg_replace([
        // Remove invisible content
        '@<head[^>]*?>.*?</head>@siu',
        '@<style[^>]*?>.*?</style>@siu',
        '@<script[^>]*?.*?</script>@siu',
        '@<object[^>]*?.*?</object>@siu',
        '@<embed[^>]*?.*?</embed>@siu',
        '@<applet[^>]*?.*?</applet>@siu',
        '@<noframes[^>]*?.*?</noframes>@siu',
        '@<noscript[^>]*?.*?</noscript>@siu',
        '@<noembed[^>]*?.*?</noembed>@siu',
        // Add line breaks before and after blocks
        '@</?((address)|(blockquote)|(center)|(del))@iu',
        '@</?((div)|(h[1-9])|(ins)|(isindex)|(p)|(pre))@iu',
        '@</?((dir)|(dl)|(dt)|(dd)|(li)|(menu)|(ol)|(ul))@iu',
        '@</?((table)|(th)|(td)|(caption))@iu',
        '@</?((form)|(button)|(fieldset)|(legend)|(input))@iu',
        '@</?((label)|(select)|(optgroup)|(option)|(textarea))@iu',
        '@</?((frameset)|(frame)|(iframe))@iu',
    ], [
        ' ',
        ' ',
        ' ',
        ' ',
        ' ',
        ' ',
        ' ',
        ' ',
        ' ',
        "\n\$0",
        "\n\$0",
        "\n\$0",
        "\n\$0",
        "\n\$0",
        "\n\$0",
        "\n\$0",
        "\n\$0",
    ], $str);

    $str = strip_tags($str, $allowed);

    // Remove on events from attributes
    $re  = '/\bon[a-z]+\s*=\s*(?:([\'"]).+?\1|(?:\S+?\(.*?\)(?=[\s>])))/i';
    $str = preg_replace($re, '', $str);

    $str = trim($str);
    $str = trim($str, '&nbsp;');
    $str = trim($str);

    return $str;
}


function sanitizeFileName($string)
{
    // Remove special characters except letters, numbers, and spaces
    $string = preg_replace('/[^A-Za-z0-9 ]/', '', $string);

    // Replace one or more spaces with a single hyphen
    $string = preg_replace('/\s+/', '-', $string);

    // Convert to lowercase (optional)
    $string = strtolower($string);

    return $string;
}

function saveBase64Image($input, $folder = "uploads/", $filename = null)
{
    if (empty($input)) {
        return false;
    }

    // If already a URL or file path, return it
    if (filter_var($input, FILTER_VALIDATE_URL) || strpos($input, 'uploads/') !== false) {
        return $input;
    }

    // Check base64 format
    if (!preg_match('/^data:image\/(\w+);base64,/', $input, $type)) {
        return $input;
    }

    $extension = strtolower($type[1]);
    if ($extension == 'jpeg') {
        $extension = 'jpg';
    }

    // Remove base64 header
    $base64_string = substr($input, strpos($input, ',') + 1);
    $base64_string = str_replace(' ', '+', $base64_string);

    $image_data = base64_decode($base64_string);

    if ($image_data === false) {
        return false;
    }

    // Create folder if not exists
    if (!is_dir($folder)) {
        mkdir($folder, 0755, true);
    }

    // Generate filename
    if (!$filename) {
        $filename = "image_" . time() . rand(100,999);
    }

    $folder = rtrim($folder, '/') . '/';
    $filepath = $folder . $filename . '.' . $extension;

    if (file_put_contents($filepath, $image_data)) {
        return base_url($filepath);
    }

    return false;
}



function knowledge_base_from_path($file_path, $base_folder = "")
{
    $CI = &get_instance();
    $filePathLocation = $file_path;
    if (empty($file_path)) {
        return [
            "status" => false,
            "message" => "File path is required."
        ];
    }

    try {

        $CI->db->trans_begin();

        // Remove base URL if exists
        $file_path = str_replace(base_url(), '', $file_path);

        // Remove file name
        $dir_path = dirname($file_path);

        // Break into folders
        $folders = explode('/', trim($dir_path, '/'));

        /*
          If your path starts with:
          uploads/knowledge_base/fees_structures/
          and you DON'T want those folders in DB,
          remove first 3 parts.
        */

        $folders = array_slice($folders, 3);
        // adjust index if needed

        // Optional: prepend custom base folder
        if (!empty($base_folder)) {
            array_unshift($folders, trim($base_folder, '/'));
        }

        $parent_id = 0;

        foreach ($folders as $folder) {

            if (empty($folder)) continue;

            $existing = $CI->db
                ->where([
                    "name"      => $folder,
                    "parent_id" => $parent_id
                ])
                ->get(db_prefix() . "knowledge_base_folder")
                ->row();

            if ($existing) {
                $folder_id = $existing->id;
            } else {

                $CI->db->insert(db_prefix() . "knowledge_base_folder", [
                    "name"      => $folder,
                    "group_ids" => "",
                    "status"    => 1,
                    "parent_id" => $parent_id,
                    "edit"      => 0
                ]);

                $folder_id = $CI->db->insert_id();
            }

            $parent_id = $folder_id;
        }

        // Insert file
        $CI->db->insert(db_prefix() . "knowledge_base_files", [
            "name"      => basename($file_path),
            "type"      => "pdf",
            "status"    => 1,
            "folder_id" => $parent_id,
            "path"      => $filePathLocation
        ]);

        if ($CI->db->trans_status() === FALSE) {
            $CI->db->trans_rollback();
            return [
                "status" => false,
                "message" => "Database transaction failed."
            ];
        }

        $CI->db->trans_commit();

        return [
            "status" => true,
            "message" => "File added using dynamic folder structure.",
            "folder_id" => $parent_id
        ];
    } catch (Exception $e) {

        $CI->db->trans_rollback();

        return [
            "status" => false,
            "message" => $e->getMessage()
        ];
    }
}


function otpGenerate($phoneNumber, $otp)
{
    $CI = &get_instance();

    if (!class_exists('leads_model')) {
        $CI->load->model('leads_model');
    }

    try {
        $whatsapp_template_id = 13;

        // ✅ Fetch template
        $whatsapp = $CI->db->query(
            "SELECT * FROM " . db_prefix() . "whatsapptemplates WHERE status=1 AND id = ?",
            [$whatsapp_template_id]
        )->row();

        if (!$whatsapp) {
            throw new Exception("Template not found");
        }

        $templateName = $whatsapp->template_name;
        $languageCode = $whatsapp->languageCode;

        // ✅ Validate phone
        $cleanNumber = preg_replace('/\D/', '', $phoneNumber);
        if (strlen($cleanNumber) < 10) {
            throw new Exception("Invalid phone number");
        }

        $toNumber = "91" . substr($cleanNumber, -10);

        $productToken      = WHATSAAP_PRODUCT_KEY;
        $fromNumber        = WHATSAAP_FROM_NUMBER;
        $templateNamespace = WHATSAAP_NAMESPACE;

        if (empty($productToken) || empty($fromNumber) || empty($templateNamespace)) {
            throw new Exception("WhatsApp configuration missing");
        }

        // ✅ Parameters
        $parameters = [[
            "type" => "text",
            "text" => (string)$otp
        ]];

        // ✅ Payload (same as yours)
        $payload = [
            "messages" => [
                "authentication" => ["producttoken" => $productToken],
                "msg" => [[
                    "from" => $fromNumber,
                    "to"   => [["number" => $toNumber]],
                    "body" => [
                        "type"    => "auto",
                        "content" => $templateName
                    ],
                    "allowedChannels" => ["WhatsApp"],
                    "richContent" => [
                        "conversation" => [[
                            "template" => [
                                "whatsapp" => [
                                    "namespace"    => $templateNamespace,
                                    "element_name" => $templateName,
                                    "language"     => [
                                        "policy" => "deterministic",
                                        "code"   => $languageCode
                                    ],
                                    "components" => [
                                        [
                                            "type" => "body",
                                            "parameters" => $parameters
                                        ],
                                        [
                                            "type" => "button",
                                            "sub_type" => "url",
                                            "index" => "0",
                                            "parameters" => [[
                                                "type" => "text",
                                                "text" => (string)$otp
                                            ]]
                                        ]
                                    ]
                                ]
                            ]
                        ]]
                    ]
                ]]
            ]
        ];

        // ✅ cURL
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL            => 'https://gw.messaging.cm.com/v1.0/message',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($payload),
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json']
        ]);

        $response = curl_exec($curl);

        // ❌ cURL error
        if ($response === false) {
            throw new Exception('cURL Error: ' . curl_error($curl));
        }

        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        $responseArray = json_decode($response, true);

        // ❌ HTTP error
        if ($httpCode != 200) {
            throw new Exception("HTTP Error: $httpCode - " . $response);
        }

        // ❌ API error
        if (!$responseArray || isset($responseArray['error'])) {
            throw new Exception("API Error: " . $response);
        }

        // ✅ Success log
        $CI->db->insert(db_prefix() . 'whatsapp_email_logs', [
            "type"        => "whatsapp",
            "template_id" => $whatsapp_template_id,
            "clientid"    => 0,
            "datetime"    => date("Y-m-d H:i:s"),
            "contact"     => $toNumber,
            "response"    => json_encode($responseArray)
        ]);

        return [
            "status" => 1,
            "message" => "OTP sent successfully"
        ];

    } catch (Exception $e) {

        // ❌ Error log (very important)
        log_message('error', 'WhatsApp OTP Error: ' . $e->getMessage());

        // Optional DB log
        $CI->db->insert(db_prefix() . 'whatsapp_email_logs', [
            "type"        => "whatsapp",
            "template_id" => isset($whatsapp_template_id) ? $whatsapp_template_id : 0,
            "clientid"    => 0,
            "datetime"    => date("Y-m-d H:i:s"),
            "contact"     => isset($toNumber) ? $toNumber : '',
            "response"    => $e->getMessage()
        ]);

        return [
            "status" => 0,
            "message" => "Failed to send OTP. Please try again."
            // 🔒 Do NOT expose real error in production
        ];
    }
}


function staff_location_region($id = null)
{
    $CI = &get_instance();
    $CI->load->database();

    if ($id !== null) {
        $query = $CI->db->get_where('tblstaff_location_region', ['id' => $id]);

        if ($query->num_rows() > 0) {
            return $query->row_array();
        } else {
            return false; // No data found
        }
    } else {
        $query = $CI->db->get('tblstaff_location_region');

        if ($query->num_rows() > 0) {
            return $query->result_array();
        } else {
            return [];
        }
    }
}


function staff_state_region($id = null)
{
    $CI = &get_instance();
    $CI->load->database();

    if ($id !== null) {
        $query = $CI->db->get_where('tblstaff_state_region', ['id' => $id]);

        if ($query->num_rows() > 0) {
            return $query->row_array();
        } else {
            return false;
        }
    } else {
        $query = $CI->db->get('tblstaff_state_region');

        if ($query->num_rows() > 0) {
            return $query->result_array();
        } else {
            return [];
        }
    }
}



function get_office_locations()
{
    $CI = &get_instance();
    return $CI->s_db->query("SELECT street,city FROM office_locations")->result_array();
}


