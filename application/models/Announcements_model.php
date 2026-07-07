<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Announcements_model extends App_Model
{
    
     private $table = 'tbl_announcements';
    private $history_table = 'tbl_announcement_edit_history';
    private $user_read_table = 'tbl_announcement_user_read';
    
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Get announcements
     * @param  string $id    optional id
     * @param  array  $where perform where
     * @param  string $limit
     * @return mixed
     */
    public function get($id = '', $where = [], $limit = '')
    {
        $this->db->where($where);

        if (is_numeric($id)) {
            $this->db->where('announcementid', $id);

            return $this->db->get(db_prefix() . 'announcements')->row();
        }

        if (count($where) == 0 && $limit == '') {
            $announcements = $this->app_object_cache->get('all-user-announcements');
            if (!$announcements && !is_array($announcements)) {
                $this->_annoucements_query();
                $announcements = $this->db->get(db_prefix() . 'announcements')->result_array();
                $this->app_object_cache->add('all-user-announcements', $announcements);
            }
        } else {
            $this->_annoucements_query();

            if (is_numeric($limit)) {
                $this->db->limit($limit);
            }

            $announcements = $this->db->get(db_prefix() . 'announcements')->result_array();
        }

        return $announcements;
    }
    
public function announcements_priority($id = null)
{
    $tbl = db_prefix() . 'announcements_priority';

    if ($id) {
        return $this->db->where('id', $id)
                        ->get($tbl)
                        ->row(); // single record
    }

    return $this->db->get($tbl)->result(); // all records
}


public function get_announcements($filters = [])
{
    
$userInfo = $this->staff_model->get(get_staff_user_id());

    
    $user_id = !empty($filters['user_id']) ? (int) $filters['user_id'] : 0;

    $this->db->select('a.*, COUNT(ur.id) as is_read_by_user')
        ->from($this->table . ' a')
        ->join(
            $this->user_read_table . ' ur',
            'a.id = ur.announcement_id AND ur.user_id = ' . $user_id,
            'left'
        )
        ->where('a.status', 1)
        ->group_by('a.id');

    // Department filter
    if (!empty($filters['department']) && $filters['department'] != 'all') {
        $this->db->where('a.department', $filters['department']);
    }
     if (!empty($filters['sub_department']) && $filters['sub_department'] != 'all') {
        $this->db->where('a.sub_department', $filters['sub_department']);
    }
    // Priority filter
    if (!empty($filters['priority']) && $filters['priority'] != 'all') {
        $this->db->where('a.priority', $filters['priority']);
    }
    
    if(!is_admin()){
        if($userInfo->post_sales ==1)
        {
            
        }
    else if($userInfo->role==3)
    {
          $this->db->where_in('a.sub_department',["counsellor","team","all"]); 
    }else if($userInfo->role!=3)
    {
        $this->db->where_in('a.sub_department',["counsellor","all"]); 
    }
    }
    
    // Single record
    if (!empty($filters['id'])) {
        $this->db->where('a.id', $filters['id']);
    }
    // Search
    if (!empty($filters['search'])) {
        $this->db->group_start()
            ->like('a.title', $filters['search'])
            ->or_like('a.content', $filters['search'])
            ->or_like('a.stakeholder', $filters['search'])
            ->group_end();
    }
    // Read status
    if (!empty($filters['read_status'])) {
        if ($filters['read_status'] == 'unread') {
            $this->db->having('is_read_by_user', 0);
        } elseif ($filters['read_status'] == 'read') {
            $this->db->having('is_read_by_user >', 0);
        }
    }
    // Sorting
    $sort_by = $filters['sort_by'] ?? 'date';
    switch ($sort_by) {
        case 'views':
            $this->db->order_by('a.views', 'DESC');
            break;
        case 'alpha':
            $this->db->order_by('a.title', 'ASC');
            break;
        case 'date':
        default:
            $this->db->order_by('IFNULL(a.updated_date, a.created_date)', 'DESC', false);
            break;
    }

    $announcements = $this->db->get()->result_array();

    $isSingle = !empty($filters['id']);

    foreach ($announcements as &$ann) {
        $ann['created_date_formatted'] = !empty($ann['created_date'])
            ? date('Y-m-d', strtotime($ann['created_date'])) : null;
        $ann['updated_date_formatted'] = !empty($ann['updated_date'])
            ? date('Y-m-d', strtotime($ann['updated_date'])) : null;
        $ann['read']        = ((int) $ann['is_read_by_user'] > 0);
    $ann['can_edit'] = (is_admin() || $userInfo->post_sales || $userInfo->role == 3) ? 1 : 0;
        $ann['editHistory'] = $this->get_edit_history($ann['id']);

        // Always include the count (cheap) for the card badge
        $ann['attachments_count'] = $this->count_attachments($ann['id']);

        // Only the single-record view needs the full file list
        if ($isSingle) {
            $ann['attachments'] = array_map(function ($a) {
                return [
                    'id'   => $a['id'],
                    'name' => $a['file_name'],
                    'type' => $a['file_type'],
                    'size' => $a['file_size'],
                    'url'  => admin_url('announcements/download_attachment/' . $a['id']),
                ];
            }, $this->get_attachments($ann['id']));
        }

        unset($ann['is_read_by_user']);
    }
    unset($ann);

    return [
        'success' => true,
        'data'    => $isSingle ? ($announcements[0] ?? []) : $announcements,
        'total'   => count($announcements),
    ];
}

// public function get_attachments($announcement_id)
// {
//     return $this->db->where('announcement_id', $announcement_id)
//                     ->order_by('id', 'asc')
//                     ->get('tbl_announcement_attachments')
//                     ->result_array();
// }

public function count_attachments($announcement_id)
{
    return $this->db->where('announcement_id', $announcement_id)
                    ->count_all_results('tbl_announcement_attachments');
}
    public function get_announcement_by_id($id, $user_id = null)
    {
        
            
$userInfo = $this->staff_model->get(get_staff_user_id());


        $this->db->select('a.*')
            ->from($this->table . ' a')
            ->where('a.id', $id)
            ->where('a.status', 1);
        
        $query = $this->db->get();
        $announcement = $query->row_array();
        
        if ($announcement) {
            $announcement['can_edit'] = (is_admin() || $userInfo->post_sales || $userInfo->role == 3) ? 1 : 0;
            $announcement['created_date_formatted'] = date('Y-m-d', strtotime($announcement['created_date']));
            $announcement['updated_date_formatted'] = $announcement['updated_date'] ? date('Y-m-d', strtotime($announcement['updated_date'])) : null;
            $announcement['editHistory'] = $this->get_edit_history($id);
            
            // Check if user has read this
            if ($user_id) {
                $this->db->from($this->user_read_table)
                    ->where('announcement_id', $id)
                    ->where('user_id', $user_id);
                $announcement['read'] = $this->db->count_all_results() > 0;
            } else {
                $announcement['read'] = false;
            }
        }
        
        return $announcement;
    }

   public function create_announcement($data)
{
    $this->db->insert($this->table, $data);
    $id = $this->db->insert_id();

    if ($id) {
        $this->add_edit_history($id, $data['stakeholder'], 'Created announcement');
        $this->_save_attachments($id);          // <-- save uploaded files
        return $id;
    }

    return false;
}

public function update_announcement($id, $data, $user_id)
{
    $old_data = $this->get_announcement_by_id($id);

    $this->db->where('id', $id);
    $result = $this->db->update($this->table, $data);

    // Save any newly uploaded files (even if no text field changed)
    $uploaded = $this->_save_attachments($id);

    if ($result || $uploaded) {
        $changes = $this->prepare_changes_text($old_data, $data);
        if ($uploaded) {
            $changes = trim($changes . ($changes ? '; ' : '') . $uploaded . ' attachment(s) added');
        }
        $this->add_edit_history($id, get_staff_full_name($user_id), $changes);
        return true;
    }

    return false;
}

/**
 * Move uploaded files to /uploads/announcements/{id}/ and record them.
 * Reads the multi-file input named "attachments[]" from the request.
 * Returns the number of files saved.
 */
private function _save_attachments($announcement_id)
{
    if (empty($_FILES['attachments']['name'][0])) {
        return 0;
    }

    $allowed = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'csv', 'png', 'jpg', 'jpeg', 'gif', 'webp'];
    $relDir  = 'uploads/announcements/' . (int) $announcement_id . '/';
    $absDir  = FCPATH . $relDir;

    if (!is_dir($absDir)) {
        mkdir($absDir, 0755, true);
    }

    $files = $_FILES['attachments'];
    $count = count($files['name']);
    $saved = 0;

    for ($i = 0; $i < $count; $i++) {
        if ($files['error'][$i] !== UPLOAD_ERR_OK) {
            continue;
        }

        $orig = $files['name'][$i];
        $ext  = strtolower(pathinfo($orig, PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed)) {
            continue; // skip disallowed types
        }

        $safe = uniqid() . '_' . preg_replace('/[^A-Za-z0-9._-]/', '_', $orig);

        if (move_uploaded_file($files['tmp_name'][$i], $absDir . $safe)) {
            $this->db->insert('tbl_announcement_attachments', [
                'announcement_id' => $announcement_id,
                'file_name'       => $orig,
                'file_path'       => $relDir . $safe,
                'file_type'       => $files['type'][$i],
                'file_size'       => $files['size'][$i],
                'created_at'      => date('Y-m-d H:i:s'),
            ]);
            $saved++;
        }
    }

    return $saved;
}

    public function mark_as_viewed($announcement_id, $user_id)
    {
        // Increment views count
        $this->db->set('views', 'views+1', false);
        $this->db->where('id', $announcement_id);
        $this->db->update($this->table);
        
        // Mark as read for this user
        return $this->mark_as_read($announcement_id, $user_id);
    }

    public function mark_as_read($announcement_id, $user_id)
    {
        // Check if already marked as read
        $this->db->from($this->user_read_table)
            ->where('announcement_id', $announcement_id)
            ->where('user_id', $user_id);
        
        if ($this->db->count_all_results() == 0) {
            $data = array(
                'announcement_id' => $announcement_id,
                'user_id' => $user_id,
                'user_name' => get_staff_full_name($user_id),
                'read_date' => date('Y-m-d H:i:s')
            );
            return $this->db->insert($this->user_read_table, $data);
        }
        
        return true;
    }

    public function delete_announcement($id)
    {
        $this->db->where('id', $id);
        return $this->db->update($this->table, ['status' => 0]);
    }

// public function get_stats($user_id)
// {
//     if (empty($user_id)) {
//         return [
//             'total' => 0,
//             'unread' => 0,
//             'avg_views' => 0,
//             'by_department' => []
//         ];
//     }

//     $isAdmin = is_admin();

//     /*
//     |--------------------------------------------------------------------------
//     | Apply Staff Department Filter
//     |--------------------------------------------------------------------------
//     */
//     $applyDepartmentFilter = function ($alias = 'a') use (
//         $isAdmin,
//         $user_id
//     ) {

//         // Admin sees all
//         if ($isAdmin) {
//             return;
//         }

//         $prefix = !empty($alias)
//             ? $alias . '.'
//             : '';

//         // Announcement -> Department
//         $this->db->join(
//             'tblstaff_department sd',
//             'sd.id = ' . $prefix . 'department',
//             'inner'
//         );

//         // Active Staff
//         $this->db->join(
//             'tblstaff s',
//             's.department = sd.id
//             AND s.active = 1',
//             'inner'
//         );

//         // Current logged-in user
//         $this->db->where(
//             's.staffid',
//             (int) $user_id
//         );
//     };

//     /*
//     |--------------------------------------------------------------------------
//     | Total Announcements
//     |--------------------------------------------------------------------------
//     */
//     $this->db->select(
//         'COUNT(DISTINCT a.id) AS total',
//         false
//     );

//     $this->db->from(
//         $this->table . ' a'
//     );

//     $this->db->where(
//         'a.status',
//         1
//     );

//     $applyDepartmentFilter('a');

//     $total = $this->db
//         ->get()
//         ->row();

//     /*
//     |--------------------------------------------------------------------------
//     | Unread Announcements
//     |--------------------------------------------------------------------------
//     */
//     $this->db->select(
//         'COUNT(DISTINCT a.id) AS count',
//         false
//     );

//     $this->db->from(
//         $this->table . ' a'
//     );

//     $this->db->join(
//         $this->user_read_table . ' ur',
//         'a.id = ur.announcement_id
//         AND ur.user_id = ' . (int) $user_id,
//         'left'
//     );

//     $this->db->where(
//         'a.status',
//         1
//     );

//     $this->db->where(
//         'ur.id IS NULL',
//         null,
//         false
//     );

//     $applyDepartmentFilter('a');

//     $unread = $this->db
//         ->get()
//         ->row();

//     /*
//     |--------------------------------------------------------------------------
//     | Average Views
//     |--------------------------------------------------------------------------
//     */
//     $this->db->select(
//         'AVG(a.views) AS views',
//         false
//     );

//     $this->db->from(
//         $this->table . ' a'
//     );

//     $this->db->where(
//         'a.status',
//         1
//     );

//     $applyDepartmentFilter('a');

//     $avg_views = $this->db
//         ->get()
//         ->row();

//     /*
//     |--------------------------------------------------------------------------
//     | Department-wise Counts
//     |--------------------------------------------------------------------------
//     */
//     $this->db->select(
//         '
//         sd.name,
//         sd.id,
//         COUNT(DISTINCT a.id) AS total
//         ',
//         false
//     );

//     $this->db->from(
//         $this->table . ' a'
//     );

//     $this->db->where(
//         'a.status',
//         1
//     );

//     // For admin, still join department
//     if ($isAdmin) {

//         $this->db->join(
//             'tblstaff_department sd',
//             'sd.id = a.department',
//             'left'
//         );

//     } else {

//         $applyDepartmentFilter('a');
//     }

//     $departmentCounts = $this->db
//         ->group_by('a.department')
//         ->get()->result_array();
        
        

//     $by_department = [];

//     foreach ($departmentCounts as $row) {

//         $departmentName =
//             $row['id']
//             ?? 'Unknown';

//         $by_department[
//             $departmentName
//         ] = (int) $row['total'];
//     }

//     return [
//         'total' => (int) (
//             $total->total ?? 0
//         ),

//         'unread' => (int) (
//             $unread->count ?? 0
//         ),

//         'avg_views' => round(
//             $avg_views->views ?? 0
//         ),

//         'by_department' => $by_department
//     ];
// }

public function get_stats($user_id)
{
    if (empty($user_id)) {
        return [
            'total'         => 0,
            'unread'        => 0,
            'avg_views'     => 0,
            'by_department' => []
        ];
    }

    $userInfo = $this->staff_model->get($user_id);

    /*
    |--------------------------------------------------------------------------
    | Visibility Filter (Same as get_announcements)
    |--------------------------------------------------------------------------
    */
    $applyVisibilityFilter = function () use ($userInfo) {

        if (is_admin()) {
            return;
        }

        if ($userInfo->post_sales == 1) {

            // No restriction

        } elseif ($userInfo->role == 3) {

            $this->db->where_in(
                'a.sub_department',
                ['counsellor', 'team', 'all']
            );

        } else {

            $this->db->where_in(
                'a.sub_department',
                ['counsellor', 'all']
            );
        }
    };

    /*
    |--------------------------------------------------------------------------
    | Total Announcements
    |--------------------------------------------------------------------------
    */
    $this->db->select('COUNT(DISTINCT a.id) AS total', false);
    $this->db->from($this->table . ' a');
    $this->db->where('a.status', 1);

    $applyVisibilityFilter();

    $total = $this->db->get()->row();

    /*
    |--------------------------------------------------------------------------
    | Unread Announcements
    |--------------------------------------------------------------------------
    */
    $this->db->select('COUNT(DISTINCT a.id) AS count', false);
    $this->db->from($this->table . ' a');

    $this->db->join(
        $this->user_read_table . ' ur',
        'a.id = ur.announcement_id
         AND ur.user_id = ' . (int) $user_id,
        'left'
    );

    $this->db->where('a.status', 1);
    $this->db->where('ur.id IS NULL', null, false);

    $applyVisibilityFilter();

    $unread = $this->db->get()->row();

    /*
    |--------------------------------------------------------------------------
    | Average Views
    |--------------------------------------------------------------------------
    */
    $this->db->select('AVG(a.views) AS views', false);
    $this->db->from($this->table . ' a');
    $this->db->where('a.status', 1);

    $applyVisibilityFilter();

    $avg_views = $this->db->get()->row();

    /*
    |--------------------------------------------------------------------------
    | Department-wise Counts
    |--------------------------------------------------------------------------
    */
    $this->db->select(
        '
        sd.id,
        sd.name,
        COUNT(DISTINCT a.id) AS total
        ',
        false
    );

    $this->db->from($this->table . ' a');

    $this->db->join(
        'tblstaff_department sd',
        'sd.id = a.department',
        'left'
    );

    $this->db->where('a.status', 1);

    $applyVisibilityFilter();

    $departmentCounts = $this->db
        ->group_by('sd.id')
        ->get()
        ->result_array();

    $by_department = [];

    foreach ($departmentCounts as $row) {

        $departmentName = !empty($row['name'])
            ? $row['name']
            : 'Unknown';

        $by_department[$departmentName] = (int) $row['total'];
    }

    return [
        'total' => (int) ($total->total ?? 0),

        'unread' => (int) ($unread->count ?? 0),

        'avg_views' => round(
            (float) ($avg_views->views ?? 0)
        ),

        'by_department' => $by_department
    ];
}

    private function get_edit_history($announcement_id)
    {
        $this->db->select('edited_by, edited_date, changes')
            ->from($this->history_table)
            ->where('announcement_id', $announcement_id)
            ->order_by('edited_date', 'ASC');
        
        $history = $this->db->get()->result_array();
        
        foreach ($history as &$h) {
            $h['date'] = date('Y-m-d', strtotime($h['edited_date']));
            $h['by'] = $h['edited_by'];
            unset($h['edited_date']);
            unset($h['edited_by']);
        }
        
        return $history;
    }

    private function add_edit_history($announcement_id, $edited_by, $changes)
    {
        $data = array(
            'announcement_id' => $announcement_id,
            'edited_by' => $edited_by,
            'edited_date' => date('Y-m-d H:i:s'),
            'changes' => $changes
        );
        
        return $this->db->insert($this->history_table, $data);
    }

    private function prepare_changes_text($old_data, $new_data)
    {
        $changes = [];
        $fields = ['title', 'content', 'department', 'stage', 'priority'];
        
        foreach ($fields as $field) {
            if (isset($old_data[$field]) && isset($new_data[$field]) && $old_data[$field] != $new_data[$field]) {
                $changes[] = ucfirst($field) . ' changed';
            }
        }
        
        return implode(', ', $changes) ?: 'Updated announcement';
    }
    

    /**
     * Get total dismissed announcements for logged in user
     * @return mixed
     */
    public function get_total_undismissed_announcements()
    {
        if (!is_logged_in()) {
            return 0;
        }

        $staff  = is_client_logged_in() ? 0 : 1;
        $userid = is_client_logged_in() ? get_client_user_id() : get_staff_user_id();

        $sql = 'SELECT COUNT(*) as total_undismissed FROM ' . db_prefix() . 'announcements WHERE announcementid NOT IN (SELECT announcementid FROM ' . db_prefix() . 'dismissed_announcements WHERE staff=' . $staff . ' AND userid=' . $userid . ')';
        if ($staff == 1) {
            $sql .= ' AND showtostaff=1';
        } else {
            $sql .= ' AND showtousers=1';
        }

        return $this->db->query($sql)->row()->total_undismissed;
    }

    /**
     * @param $_POST array
     * @return Insert ID
     * Add new announcement calling this function
     */
    public function add($data)
    {
        $data['dateadded'] = date('Y-m-d H:i:s');

        if (isset($data['showname'])) {
            $data['showname'] = 1;
        } else {
            $data['showname'] = 0;
        }
        if (isset($data['showtostaff'])) {
            $data['showtostaff'] = 1;
        } else {
            $data['showtostaff'] = 0;
        }
        if (isset($data['showtousers'])) {
            $data['showtousers'] = 1;
        } else {
            $data['showtousers'] = 0;
        }
        $data['message'] = $data['message'];
        $data['userid']  = get_staff_full_name(get_staff_user_id());

        $data = hooks()->apply_filters('before_announcement_added', $data);

        $this->db->insert(db_prefix() . 'announcements', $data);
        $insert_id = $this->db->insert_id();

        hooks()->do_action('announcement_created', $insert_id);

        log_activity('New Announcement Added [' . $data['name'] . ']');

        return $insert_id;
    }

    /**
     * @param  $_POST array
     * @param  integer
     * @return boolean
     * This function updates announcement
     */
    public function update($data, $id)
    {
        $data['showname']    = isset($data['showname']) ? 1 : 0;
        $data['showtostaff'] = isset($data['showtostaff']) ? 1 : 0;
        $data['showtousers'] = isset($data['showtousers']) ? 1 : 0;

        $data['message'] = $data['message'];

        $data = hooks()->apply_filters('before_announcement_updated', $data, $id);

        $this->db->where('announcementid', $id);
        $this->db->update(db_prefix() . 'announcements', $data);
        if ($this->db->affected_rows() > 0) {
            hooks()->do_action('announcement_updated', $id);

            log_activity('Announcement Updated [' . $data['name'] . ']');

            return true;
        }

        return false;
    }

    /**
     * @param  integer
     * @return boolean
     * Delete Announcement
     * All Dimissed announcements from database will be cleaned
     */
    public function delete($id)
    {
        hooks()->do_action('before_delete_announcement', $id);

        $this->db->where('announcementid', $id);
        $this->db->delete(db_prefix() . 'announcements');
        if ($this->db->affected_rows() > 0) {
            $this->db->where('announcementid', $id);
            $this->db->delete(db_prefix() . 'dismissed_announcements');

            hooks()->do_action('announcement_deleted', $id);

            log_activity('Announcement Deleted [' . $id . ']');

            return true;
        }

        return false;
    }

    public function set_announcements_as_read_except_last_one($user_id, $staff = false)
    {
        $lastAnnouncement = $this->db->query('SELECT announcementid FROM ' . db_prefix() . 'announcements WHERE ' . (!$staff ? 'showtousers' : 'showtostaff') . ' = 1 AND announcementid = (SELECT MAX(announcementid) FROM ' . db_prefix() . 'announcements)')->row();
        if ($lastAnnouncement) {
            // Get all announcements and set it to read.
            $this->db->select('announcementid')
                ->from(db_prefix() . 'announcements')
                ->where((!$staff ? 'showtousers' : 'showtostaff'), 1)
                ->where('announcementid !=', $lastAnnouncement->announcementid);

            $announcements = $this->db->get()->result_array();
            foreach ($announcements as $announcement) {
                $this->db->insert(db_prefix() . 'dismissed_announcements', [
                    'announcementid' => $announcement['announcementid'],
                    'staff'          => (bool) $staff,
                    'userid'         => $user_id,
                ]);
            }
        }
    }

    private function _annoucements_query()
    {
        if (is_client_logged_in()) {
            $this->db->where('showtousers', 1);
        } elseif (is_staff_logged_in()) {
            $this->db->where('showtostaff', 1);
        }
        $this->db->order_by('dateadded', 'desc');
    }

    public function get_university_shortlist_status()
    {
        $this->db->where('client_id', get_client_user_id());
        $this->db->where('status', 1);
        $this->db->where('university_status', 0);
        $result = $this->db->get(db_prefix() . 'client_university_shortlisting')->row();

        return $result; // Returns true if a row is found, false otherwise
    }
    
    public function increment_view($id, $user_id = 0)
{
    $this->db->set('views', 'views+1', false);
    $this->db->where('id', $id);
    $this->db->update($this->table);
    return $this->db->affected_rows() > 0;
}

public function add_attachment($data)     { $this->db->insert('tbl_announcement_attachments', $data); return $this->db->insert_id(); }
public function get_attachment($id)        { return $this->db->where('id', $id)->get('tbl_announcement_attachments')->row_array(); }
public function delete_attachment($id)     { return $this->db->where('id', $id)->delete('tbl_announcement_attachments'); }
public function get_attachments($annId)    { return $this->db->where('announcement_id', $annId)->order_by('id', 'asc')->get('tbl_announcement_attachments')->result_array(); }

/**
 * Total unread announcement count for the current user.
 * Admin  → all unread across every department.
 * Staff  → unread only within the department(s) they belong to.
 */
// public function get_unread_count()
// {
//     $user_id = (int) get_staff_user_id();

//     if (!$user_id) {
//         return 0;
//     }

//     $department_ids = [];

//     // Restrict non-admin users by department ID
//     if (!is_admin()) {

//         // Get logged-in staff department IDs
//         $dept_query = $this->db
//             ->select('id')
//             ->from('tblstaff_department')
//             ->get()
//             ->result_array();

//         $department_ids = array_column($dept_query, 'id');

//         // No departments assigned
//         if (empty($department_ids)) {
//             return 0;
//         }
//     }
    


//     // Main unread count query
//     $this->db->select('COUNT(DISTINCT a.id) AS unread');
//     $this->db->from($this->table . ' a');

//     // Check if current user has read the announcement
//     $this->db->join(
//         $this->user_read_table . ' ur',
//         'ur.announcement_id = a.id 
//          AND ur.user_id = ' . $user_id,
//         'left'
//     );

//     // Active announcements only
//     $this->db->where('a.status', 1);

//     // Unread announcements only
//     $this->db->where('ur.id IS NULL', null, false);

//     // Filter by department for non-admin users
//     if (!is_admin()) {
//         $this->db->where_in('a.department', $department_ids);
//     }

//     // Execute query
//     $row = $this->db->get()->row();
    
//     // echo $this->db->last_query();

//     return $row ? (int) $row->unread : 0;
// }

public function get_unread_count()
{
    $user_id = (int) get_staff_user_id();

    if (!$user_id) {
        return 0;
    }

    $userInfo = $this->staff_model->get($user_id);

    $this->db->select('COUNT(DISTINCT a.id) AS unread');
    $this->db->from($this->table . ' a');

    $this->db->join(
        $this->user_read_table . ' ur',
        'ur.announcement_id = a.id AND ur.user_id = ' . $user_id,
        'left'
    );

    $this->db->where('a.status', 1);
    $this->db->where('ur.id IS NULL', null, false);

    // Same filters as get_announcements()
    if (!is_admin()) {

        if ($userInfo->post_sales == 1) {

            // no restriction

        } elseif ($userInfo->role == 3) {

            $this->db->where_in(
                'a.sub_department',
                ['counsellor', 'team', 'all']
            );

        } else {

            $this->db->where_in(
                'a.sub_department',
                ['counsellor', 'all']
            );
        }
    }

    $row = $this->db->get()->row();

    return $row ? (int)$row->unread : 0;
}
}
