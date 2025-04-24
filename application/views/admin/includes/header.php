<?php defined('BASEPATH') or exit('No direct script access allowed');
ob_start();
// $all_leads =  json_encode(get_all_leads(), true);
$all_leads =  [];
$role = $this->db->where('staffid', get_staff_user_id())->get(db_prefix() . 'staff')->row()->role;
?>
<!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.13.1/css/all.min.css" /> -->
<style>
   li.p-20 {
      /* padding-top: 20px */
   }
</style>
<li id="top_search" class="dropdown" data-toggle="tooltip" data-placement="bottom" data-title="<?php echo _l('search_by_tags'); ?>">
   <input type="search" id="search_input" class="form-control" placeholder="<?php echo _l('top_search_placeholder'); ?>">
   <div id="search_results">
   </div>
   <ul class="dropdown-menu search-results animated fadeIn no-mtop search-history" id="search-history">
   </ul>
</li>
<li id="top_search_button">
   <button class="btn"><i class="fa fa-search"></i></button>
</li>
<?php
$top_search_area = ob_get_contents();
ob_end_clean();
?>
<div id="header">
   <div class="hide-menu"><i class="fa fa-align-left"></i></div>
   <div id="logo">
      <?php get_company_logo(get_admin_uri() . '/') ?>
   </div>
   <nav>
      <div class="small-logo">
         <span class="text-primary">
            <?php get_company_logo(get_admin_uri() . '/') ?>
         </span>
      </div>
      <div class="mobile-menu">
         <button type="button" class="navbar-toggle visible-md visible-sm visible-xs mobile-menu-toggle collapsed" data-toggle="collapse" data-target="#mobile-collapse" aria-expanded="false">
            <i class="fa fa-chevron-down"></i>
         </button>
         <ul class="mobile-icon-menu">
            <?php
            // To prevent not loading the timers twice
            if (is_mobile()) { ?>
               <li class="dropdown notifications-wrapper header-notifications">
                  <?php $this->load->view('admin/includes/notifications'); ?>
               </li>
               <li class="header-timers">
                  <a href="#" id="top-timers" class="dropdown-toggle top-timers" data-toggle="dropdown"><i class="fa fa-clock-o fa-fw fa-lg"></i>
                     <span class="label bg-success icon-total-indicator icon-started-timers<?php if ($totalTimers = count($startedTimers) == 0) {
                                                                                                echo ' hide';
                                                                                             } ?>"><?php echo count($startedTimers); ?></span>
                  </a>
                  <ul class="dropdown-menu animated fadeIn started-timers-top width300" id="started-timers-top">
                     <?php $this->load->view('admin/tasks/started_timers', array('startedTimers' => $startedTimers)); ?>
                  </ul>
               </li>
            <?php } ?>
         </ul>
         <div class="mobile-navbar collapse" id="mobile-collapse" aria-expanded="false" style="height: 0px;" role="navigation">
            <ul class="nav navbar-nav">
               <li class="header-my-profile"><a href="<?php echo admin_url('profile'); ?>"><?php echo _l('nav_my_profile'); ?></a></li>
               <li class="header-my-timesheets"><a href="<?php echo admin_url('staff/timesheets'); ?>"><?php echo _l('my_timesheets'); ?></a></li>
               <li class="header-edit-profile"><a href="<?php echo admin_url('staff/edit_profile'); ?>"><?php echo _l('nav_edit_profile'); ?></a></li>
               <?php if (is_staff_member()) { ?>
                  <li class="header-newsfeed">
                     <a href="#" class="open_newsfeed mobile">
                        <?php echo _l('whats_on_your_mind'); ?>
                     </a>
                  </li>
               <?php } ?>
               <li class="header-logout"><a href="#" onclick="logout(); return false;"><?php echo _l('nav_logout'); ?></a></li>
            </ul>
         </div>
      </div>
      <ul class="nav navbar-nav navbar-right">
         <?php
         if (!is_mobile()) {
            echo $top_search_area;
         } ?>
         <?php hooks()->do_action('after_render_top_search'); ?>
         <li class="icon header-user-profile" data-toggle="tooltip" title="<?php echo get_staff_full_name(); ?>" data-placement="bottom">
            <a href="#" class="dropdown-toggle profile" data-toggle="dropdown" aria-expanded="false">
               <?php echo staff_profile_image($current_user->staffid, array('img', 'img-responsive', 'staff-profile-image-small', 'pull-left')); ?>
            </a>
            <ul class="dropdown-menu animated fadeIn">
               <li class="header-my-profile"><a href="<?php echo admin_url('profile'); ?>"><?php echo _l('nav_my_profile'); ?></a></li>
               <li class="header-my-timesheets"><a href="<?php echo admin_url('staff/timesheets'); ?>"><?php echo _l('my_timesheets'); ?></a></li>
               <li class="header-edit-profile"><a href="<?php echo admin_url('staff/edit_profile'); ?>"><?php echo _l('nav_edit_profile'); ?></a></li>
               <?php if (get_option('disable_language') == 0) { ?>
                  <li class="dropdown-submenu pull-left header-languages">
                     <a href="#" tabindex="-1"><?php echo _l('language'); ?></a>
                     <ul class="dropdown-menu dropdown-menu">
                        <li class="<?php if ($current_user->default_language == "") {
                                       echo 'active';
                                    } ?>"><a href="<?php echo admin_url('staff/change_language'); ?>"><?php echo _l('system_default_string'); ?></a></li>
                        <?php foreach ($this->app->get_available_languages() as $user_lang) { ?>
                           <li<?php if ($current_user->default_language == $user_lang) {
                                 echo ' class="active"';
                              } ?>>
                              <a href="<?php echo admin_url('staff/change_language/' . $user_lang); ?>"><?php echo ucfirst($user_lang); ?></a>
                           <?php } ?>
                     </ul>
                  </li>
               <?php } ?>
               <li class="header-logout">
                  <a href="#" onclick="logout(); return false;"><?php echo _l('nav_logout'); ?></a>
               </li>
            </ul>
         </li>
         <?php if (is_staff_member()) { ?>
            <!-- <li class="icon header-newsfeed">
               <a href="#" class="open_newsfeed desktop" data-toggle="tooltip" title="<?php echo _l('whats_on_your_mind'); ?>" data-placement="bottom"><i class="fa fa-share fa-fw fa-lg" aria-hidden="true"></i></a>
            </li> -->
         <?php } ?>
         <!-- <li class="icon header-todo">
            <a href="<?php echo admin_url('todo'); ?>" data-toggle="tooltip" title="<?php echo _l('nav_todo_items'); ?>" data-placement="bottom"><i class="fa fa-check-square-o fa-fw fa-lg"></i>
               <span class="label bg-warning icon-total-indicator nav-total-todos<?php if ($current_user->total_unfinished_todos == 0) {
                                                                                    echo ' hide';
                                                                                 } ?>"><?php echo $current_user->total_unfinished_todos; ?></span>
            </a>
         </li> -->
         <!-- <li class="icon header-timers timer-button" data-placement="bottom" data-toggle="tooltip" data-title="<?php echo _l('my_timesheets'); ?>">
            <a href="#" id="top-timers" class="dropdown-toggle top-timers" data-toggle="dropdown">
               <i class="fa fa-clock-o fa-fw fa-lg" aria-hidden="true"></i>
               <span class="label bg-success icon-total-indicator icon-started-timers<?php if ($totalTimers = count($startedTimers) == 0) {
                                                                                          echo ' hide';
                                                                                       } ?>">
                  <?php echo count($startedTimers); ?>
               </span>
            </a>
            <ul class="dropdown-menu animated fadeIn started-timers-top width350" id="started-timers-top">
               <?php $this->load->view('admin/tasks/started_timers', array('startedTimers' => $startedTimers)); ?>
            </ul>
         </li> -->
         <li class="dropdown notifications-wrapper header-notifications p-20" data-toggle="tooltip" title="<?php echo _l('nav_notifications'); ?>" data-placement="bottom">
            <?php $this->load->view('admin/includes/notifications'); ?>
         </li>
         <?php if (is_admin()) { ?>
            <li class="dropdown p-20" data-toggle="tooltip" title="<?= $this->session->userdata('Facebook_Error') ?>" data-placement="bottom"><a><i class="fa fa-facebook fa-fw fa-lg" aria-hidden="true"></i></a></li>
         <?php } ?>
         <li class="p-20 dropdown hide-lead-filter hide right-filter-icon">
            <a><i title="Leads Fiters" data-placement="bottom" class="fa fa-filter fa-fw fa-lg" onclick="right_lead_filter()"></i></a>
         </li>

         <?php if (has_permission('whatsapp', '', 'view') && 1 == 2) { ?>
            <li class="dropdown whatsapp_alert-wrapper header-notifications header-whatsapp_alert" data-toggle="tooltip" title="WhatsApp Notification" data-placement="bottom">
               <a><i class="fa fa-whatsapp fa-fw fa-lg whatsapp-notification-icon-color    text-danger" data-toggle="dropdown" onclick="toggle_function('.whatsapp_alert')" aria-expanded="true"></i>
                  <span class="label icon-total-indicator bg-warning icon-notifications whatsapp-notification-icon  "></span>
               </a>
               <ul class="dropdown-menu whatsapp_alert animated fadeIn width400 whatsapp_notification">

               </ul>
            </li>

         <?php } ?>


   </nav>
   </li>
   </ul>
   </nav>
</div>




<div class="modal fade " id="mediaPreviewModal" tabindex="-1" aria-labelledby="mediaPreviewLabel" aria-hidden="true">
   <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
         <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title">Media Preview</h4>
         </div>
         <div class="modal-body" id="mediaPreviewContent">

         </div>

      </div>
      <!-- /.modal-content -->
   </div>
   <!-- /.modal-dialog -->
</div>


<?php if (has_permission('whatsapp', '', 'chat') && 1 == 2) { ?>
   <div class="whatsapp_Chat" style="display:none;">
      <div id="clients" style="text-align: center;">
      </div>
      <d iv style="display:none;" id="chats" class="">
         <section class="a1-row container">
            <aside class="a1-column aside">
               <div class="a1-row a1-center-items-v a1-justify-items a1-half-padding-tb a1-padding-lr bg-left-panel-header a1-spaced-items border-r">
                  <img src="https://i.ibb.co/KK52Gp5/aviv-profile.jpg" class="profile-pic" alt="Profile Picture" />
                  <div class="a1-row a1-spaced-items a1-center-items-v icon-color">
                     <!-- <i class="fas fa-adjust" onclick="toggle()"></i>
                  <i class="fas fa-circle-notch"></i>
                  <i class="fas fa-comment-alt"></i>
                  <i class="fas fa-ellipsis-v"></i> -->
                     <i class="fa fa-close" onclick="whats_app_toggle()"></i>
                  </div>
               </div>
               <div class="a1-row a1-center-items-v a1-padding a1-spaced-items search">
                  <i class="fa fa-search icon-color"></i>
                  <input type="text" class="a1-long" placeholder="Search or start new chat" />
               </div>
               <div class="a1-column a1-long a1-elastic friends-panel" id="friends-panel"></div>
            </aside>
            <main class="a1-column main " style="display: none;"></main>
         </section>
   </div>

   <!-- <a href="javascript:void(0);" style="display:none;" onclick="whats_app_toggle()" class="float_whatsapp_icon">
      <i class="fa fa-whatsapp my-float"></i>
   </a> -->
<?php } ?>


<div id="mobile-search" class="<?php if (!is_mobile()) {
                                    echo 'hide';
                                 } ?>">
   <ul>
      <?php
      if (is_mobile()) {
         echo $top_search_area;
      } ?>
   </ul>
</div>
<style>
   .facebook-notification-div {
      color: #8a6d3b;
      background-color: #fcf8e3;
      border-color: #faebcc;
   }

   .whatsapp_Chat {

      box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.3);
      border-radius: 10px;
      overflow: hidden;
      position: fixed;
      z-index: 99999;
      right: 10px;
      bottom: 10px;
      width: 310px;
      background: #eee;
      border-top-left-radius: 9px;
      border-top-right-radius: 9px;
   }
</style>

<script>
   var isAdmin = <?= is_admin() ? 1 : 0 ?>;
   var TablePagination = <?= is_admin() ? TABLEPAGINATION : (!empty($role) && $role == 3 ? TABLEPAGINATIONTEAMLEAD : "''") ?>;
   // console.log(isAdmin);
   // console.log(TablePagination);
   var WebURL = "<?= WHATSAPP_WEB_URL ?>";
   var WebSOCKETURL = "<?= SOCKET_WEB_URL ?>";
   var phoneNumber = "<?= get_staff_phonenumber(get_staff_user_id())->phonenumber ?>";
   var whatsapp_permission_view = "<?= has_permission('whatsapp', '', 'view') ?>";
   if (1 == 1) {
      whatsapp_permission_view = 0;
   }
   whatsapp_permission_view = 0;
   // console.log("whatsapp_permission", whatsapp_permission_view);


   if (whatsapp_permission_view == 1) {

      // var tokken_login = "";
      function toggle_function(selector) {
         if ($(selector).length > 0) {
            $(selector).toggle();
         }
      }

      var lead_notification = JSON.parse(<?= $all_leads ?>);
   }
</script>