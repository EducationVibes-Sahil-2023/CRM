<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| Display Debug backtrace
|--------------------------------------------------------------------------
|
| If setss to TRUE, a backtrace will be displayed along with php errors. If
| error_reporting is disabled, the backtrace will not display, regardless
| of this setting
|
*/
defined('SHOW_DEBUG_BACKTRACE') or define('SHOW_DEBUG_BACKTRACE', true);

/*
|--------------------------------------------------------------------------
| File and Directory Modes
|--------------------------------------------------------------------------
|
| These prefs are used when checking and setting modes when working
| with the file system.  The defaults are fine on servers with proper
| security, but you may wish (or even need) to change the values in
| certain environments (Apache running a separate process for each
| user, PHP under CGI with Apache suEXEC, etc.).  Octal values should
| always be used to set the mode correctly.
|
*/
defined('FILE_READ_MODE') or define('FILE_READ_MODE', 0644);
defined('FILE_WRITE_MODE') or define('FILE_WRITE_MODE', 0666);
defined('DIR_READ_MODE') or define('DIR_READ_MODE', 0755);
defined('DIR_WRITE_MODE') or define('DIR_WRITE_MODE', 0755);

defined('APP_CHMOD_DIR') or define('APP_CHMOD_DIR', (fileperms(FCPATH) & 0777 | 0755));
defined('APP_CHMOD_FILE') or define('APP_CHMOD_FILE', (fileperms(FCPATH . 'index.php') & 0777 | 0644));
/*
|--------------------------------------------------------------------------
| File Stream Modes
|--------------------------------------------------------------------------
|
| These modes are used when working with fopen()/popen()
|
*/
defined('FOPEN_READ') or define('FOPEN_READ', 'rb');
defined('FOPEN_READ_WRITE') or define('FOPEN_READ_WRITE', 'r+b');
defined('FOPEN_WRITE_CREATE_DESTRUCTIVE') or define('FOPEN_WRITE_CREATE_DESTRUCTIVE', 'wb'); // truncates existing file data, use with care
defined('FOPEN_READ_WRITE_CREATE_DESCTRUCTIVE') or define('FOPEN_READ_WRITE_CREATE_DESTRUCTIVE', 'w+b'); // truncates existing file data, use with care
defined('FOPEN_WRITE_CREATE') or define('FOPEN_WRITE_CREATE', 'ab');
defined('FOPEN_READ_WRITE_CREATE') or define('FOPEN_READ_WRITE_CREATE', 'a+b');
defined('FOPEN_WRITE_CREATE_STRICT') or define('FOPEN_WRITE_CREATE_STRICT', 'xb');
defined('FOPEN_READ_WRITE_CREATE_STRICT') or define('FOPEN_READ_WRITE_CREATE_STRICT', 'x+b');

/*
|--------------------------------------------------------------------------
| Exit Status Codes
|--------------------------------------------------------------------------
|
| Used to indicate the conditions under which the script is exit()ing.
| While there is no universal standard for error codes, there are some
| broad conventions.  Three such conventions are mentioned below, for
| those who wish to make use of them.  The CodeIgniter defaults were
| chosen for the least overlap with these conventions, while still
| leaving room for others to be defined in future versions and user
| applications.
|
| The three main conventions used for determining exit status codes
| are as follows:
|
|    Standard C/C++ Library (stdlibc):
|       http://www.gnu.org/software/libc/manual/html_node/Exit-Status.html
|       (This link also contains other GNU-specific conventions)
|    BSD sysexits.h:
|       http://www.gsp.com/cgi-bin/man.cgi?section=3&topic=sysexits
|    Bash scripting:
|       http://tldp.org/LDP/abs/html/exitcodes.html
|
*/
defined('EXIT_SUCCESS') or define('EXIT_SUCCESS', 0); // no errors
defined('EXIT_ERROR') or define('EXIT_ERROR', 1); // generic error
defined('EXIT_CONFIG') or define('EXIT_CONFIG', 3); // configuration error
defined('EXIT_UNKNOWN_FILE') or define('EXIT_UNKNOWN_FILE', 4); // file not found
defined('EXIT_UNKNOWN_CLASS') or define('EXIT_UNKNOWN_CLASS', 5); // unknown class
defined('EXIT_UNKNOWN_METHOD') or define('EXIT_UNKNOWN_METHOD', 6); // unknown class member
defined('EXIT_USER_INPUT') or define('EXIT_USER_INPUT', 7); // invalid user input
defined('EXIT_DATABASE') or define('EXIT_DATABASE', 8); // database error
defined('EXIT__AUTO_MIN') or define('EXIT__AUTO_MIN', 9); // lowest automatically-assigned error code
defined('EXIT__AUTO_MAX') or define('EXIT__AUTO_MAX', 125); // highest automatically-assigned error code

/**
 * Used for phpass
 */
define('PHPASS_HASH_STRENGTH', 8);
define('PHPASS_HASH_PORTABLE', false);

/**
 * Admin URL
 */
define('ADMIN_URL', 'admin');
/**
 * Admin URI
 * CUSTOM_ADMIN_URL is not yet tested well, don't define it
 */
define('ADMIN_URI', DEFINED('CUSTOM_ADMIN_URL') ? CUSTOM_ADMIN_URL : ADMIN_URL);

/**
 * CRM server update url
 */
define('UPDATE_URL', 'https://www.perfexcrm.com/perfex_updates/index.php');

/**
 * Get latest version info
 */
define('UPDATE_INFO_URL', 'https://www.perfexcrm.com/perfex_updates/update_info.php');

/**
 * Do not send sms to data eq. invoices, estimates older then X days.
 */
if (!defined('DO_NOT_SEND_SMS_ON_DATA_OLDER_THEN')) {
    define('DO_NOT_SEND_SMS_ON_DATA_OLDER_THEN', 45);
}

if (!defined('CUSTOM_FIELD_TRANSFER_SIMILARITY')) {
    define('CUSTOM_FIELD_TRANSFER_SIMILARITY', 85);
}

/**
 * CRM temporary path
 */
define('TEMP_FOLDER', FCPATH . 'temp' . '/');

/**
 * Customer attachments folder from profile
 */
define('CLIENT_ATTACHMENTS_FOLDER', FCPATH . 'uploads/clients' . '/');
/**
 * All tickets attachments
 */
define('TICKET_ATTACHMENTS_FOLDER', FCPATH . 'uploads/ticket_attachments' . '/');
/**
 * Company attachments, favicon, logo etc..
 */
define('COMPANY_FILES_FOLDER', FCPATH . 'uploads/company' . '/');
/**
 * Staff profile images
 */
define('STAFF_PROFILE_IMAGES_FOLDER', FCPATH . 'uploads/staff_profile_images' . '/');
/**
 * Contact profile images
 */
define('CONTACT_PROFILE_IMAGES_FOLDER', FCPATH . 'uploads/client_profile_images' . '/');
/**
 * Newsfeed attachments
 */
define('NEWSFEED_FOLDER', FCPATH . 'uploads/newsfeed' . '/');
/**
 * Contracts attachments
 */
define('CONTRACTS_UPLOADS_FOLDER', FCPATH . 'uploads/contracts' . '/');
/**
 * Tasks attachments
 */
define('TASKS_ATTACHMENTS_FOLDER', FCPATH . 'uploads/tasks' . '/');
/**
 * Invoice attachments
 */
define('INVOICE_ATTACHMENTS_FOLDER', FCPATH . 'uploads/invoices' . '/');
/**
 * Estimate attachments
 */
define('ESTIMATE_ATTACHMENTS_FOLDER', FCPATH . 'uploads/estimates' . '/');
/**
 * Proposal attachments
 */
define('PROPOSAL_ATTACHMENTS_FOLDER', FCPATH . 'uploads/proposals' . '/');
/**
 * Expenses receipts
 */
define('EXPENSE_ATTACHMENTS_FOLDER', FCPATH . 'uploads/expenses' . '/');
/**
 * Lead attachments
 */
define('LEAD_ATTACHMENTS_FOLDER', FCPATH . 'uploads/leads' . '/');
/**
 * Project files attachments
 */
define('PROJECT_ATTACHMENTS_FOLDER', FCPATH . 'uploads/projects' . '/');
/**
 * Project discussions attachments
 */
define('PROJECT_DISCUSSION_ATTACHMENT_FOLDER', FCPATH . 'uploads/discussions' . '/');
/**
 * Credit notes attachment folder
 */
define('CREDIT_NOTES_ATTACHMENTS_FOLDER', FCPATH . 'uploads/credit_notes' . '/');
/**
 * Modules Path
 */
define('APP_MODULES_PATH', FCPATH . 'modules/');
/**
 * Helper libraries path
 */
define('LIBSPATH', APPPATH . 'libraries/');
/// upload documents 
define('APPLICANT_UPLOAD_DOCUMENT', FCPATH . 'uploads/clients_documents' . '/');
define('APPLICANT_UPLOAD_DOCUMENT_PATH', 'uploads/clients_documents' . '/');
define('APPLICANT_UPLOAD_SOP_DOCUMENT', FCPATH . 'uploads/client_sop' . '/');
define('APPLICANT_UPLOAD_SOP_DOCUMENT_PATH', 'uploads/client_sop' . '/');

define('KNOWLEDGE_MEDIA_PATH', 'uploads/knowledge' . '/');
define('KNOWLEDGE_BASE_MEDIA_PATH', 'uploads/knowledge_base' . '/');
define('WHATSAPP_WEB_URL', 'http://localhost:9000');
define('SOCKET_WEB_URL', 'ws://localhost:9000/ws');
define('TABLEPAGINATION', '500');
define('TABLEPAGINATIONTEAMLEAD', '500');
define('PERFORMANCE_ARRAY', '39,40');
define('LOGIN_CHECK', 'login_202510');


define('REGISTRATION', '1');
define('DOCUMENT', '2');
define('UNIVERSITY_SHORTLISTING', '3');
define('ADMISSION', '4');
define('ENTRANCE_EXAM', '5');
define('LEGALIZATION', '6');
define('FEES_DEPOSITE', '7');
define('INVITATION', '8');
define('THIRD_PAYMENT', '9');
define('VISA', '10');
define('SC', '11');


define('REGISTRATION_PENDING', '1');
define('DOCUMENT_PENDING', '2');
define('DOCUMENT_APPROVAL_PENDING', '3');
define('UNIVERSITY_SHORTLISTING_PENDING', '4');
define('UNIVERSITY_DOC_PENDING', '5');
define('ADMISSION_LETTER_PENDING', '6');
define('ADMISSION_LETTER_WAITING', '7');
define('ADMISSION_LETTER_APPLY', '18');
define('ADMISSION_LETTER_RECEIVED', '19');
define('ENTRANCE_EXAM_PENDING', '8');
define('LEGALIZATION_PENDING', '9');
define('FEES_DEPOSITE_PENDING', '10');
define('INVITATION_PENDING', '11');
define('INVITATION_RECEIVED', '20');
define('THIRD_PAYMENT_PENDING', '12');
define('VISA_PENDING', '13');
define('VISA_APPLY', '14');
define('VISA_STAMP', '15');
define('VISA_REJECTED', '16');
define('SC_PENDING', '17');




define('MAX_UNIVERSITY_MBBS_ABROAD', '2');

define('ORG_REST', 'original rest');
define('ORG_GEORGIA', 'original georgia');
define('APOSTILE_DOC', 'ap doc');
define('VISA_GEORGIA', 'visa doc (georgia)');
define('VISA_REST', 'visa doc (rest)');
define('VISA_POST', 'visa post');
define('REFERENCE_ID', '1');
define('REFERENCE_AMOUNT_ID', '8');
define('GOOGLE_SHEET_SHARE', 'sahil.chaudhary@educationvibes.in');
