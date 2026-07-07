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
   .announcement-badge {
    background: orange;
    padding: 1px 5px;
    border-radius: 100px;
    /* line-height: 15px; */
    position: absolute;
    left: 20px;
    font-size: 10px;
    top: 35px;
}

#whatsapp-icon {
    position: fixed;
    inset: 0;
    display: block;
    width: 375px;
    height: 190px;
    left: 75%;
    top: 80%;
    border: 0;
    z-index: 2147483647;
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
    <ul class="nav navbar-nav navbar-left">
        <a target="_blank" href="<?php echo admin_url('announcements'); ?>">
        <li class="dropdown  p-20"
    style="padding:20px; color:white; position:relative;"
    data-toggle="tooltip"
    title="<?php echo _l('nav_notifications'); ?>"
    data-placement="bottom">

    <i class="fa fa-bullhorn fa-fw fa-lg"></i>

    <span id="announcementBadge" >
        
    </span>
</li>
</a>
         </ul>
         
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


 <!-- WhatsApp Icon Button -->



   <!-- <a href="javascript:void(0);" style="display:none;" onclick="whats_app_toggle()" class="float_whatsapp_icon">
      <i class="fa fa-whatsapp my-float"></i>
   </a> -->
<?php } ?>

   <ul>
      <?php
      if (is_mobile()) {
         echo $top_search_area;
      } ?>
   </ul>
</div>


 <?php if(!empty($_GET['whatsapp'])){ ?>
<div class="wa-container">
<button id="waLauncher" onclick="toggleWA()" title="WhatsApp"><i class="fa fa-whatsapp"></i></button>

<div id="waPanel">
  <!-- Header (shared) -->
  <div class="wa-head">
    <span class="wa-back" id="waBack" onclick="WA.showList()">&#8592;</span>
    <div class="wa-avatar" id="waHeadAvatar"><i class="fa fa-whatsapp"></i></div>
    <div>
      <div class="wa-title" id="waHeadTitle">WhatsApp</div>
      <div class="wa-sub" id="waHeadSub">…</div>
    </div>
  </div>
 
  <!-- Screen: chat list -->
  <div class="wa-screen active" id="screenList">
    <div id="waList"><div class="wa-state">Loading…</div></div>
  </div>
 
  <!-- Screen: conversation -->
  <div class="wa-screen" id="screenChat">
    <div id="waThread"></div>
    <div class="wa-compose hide">
      <input id="waInput" type="text" placeholder="Type a message" onkeydown="if(event.key==='Enter')WA.send()">
      <button onclick="WA.send()"><i class="fa fa-paper-plane"></i></button>
    </div>
  </div>
</div>

</div>

    <?php } ?>
<style>
   .facebook-notification-div {
      color: #8a6d3b;
      background-color: #fcf8e3;
      border-color: #faebcc;
   }

   .wa-container {
    position: fixed;
    bottom: 20px;
    right: 20px;
    z-index: 9999;
}

.wa-container{
/* Floating icon */
.wa-icon {
    width: 60px;
    height: 60px;
    background: #25D366;
    border-radius: 50%;
    display: flex;
    justify-content: center;
    align-items: center;
    cursor: pointer;
    box-shadow: 0 5px 15px rgba(0,0,0,0.3);
    position: relative;
    z-index: 2;
}

.wa-icon img {
    width: 35px;
    height: 35px;
}

/* Chat panel (hidden behind icon initially) */
.wa-panel {
    position: absolute;
    bottom: 0;
    right: 0;
    width: 280px;
    height: 0;
    background: #fff;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 5px 20px rgba(0,0,0,0.25);
    transition: 0.4s ease;
    z-index: 1;
    display: flex;
    flex-direction: column;
}

/* Open state */
.wa-panel.open {
    height: 360px;
    bottom: 70px; /* slides upward from icon */
}

/* Header */
.wa-header {
    background: #25D366;
    color: white;
    padding: 10px;
    font-size: 14px;
}

/* Body */
.wa-body {
    flex: 1;
    padding: 10px;
    background: #e5ddd5;
    font-size: 13px;
}

/* Message bubble */
.msg {
    background: white;
    padding: 8px;
    border-radius: 8px;
    margin-bottom: 8px;
    width: fit-content;
}

/* Footer */
.wa-footer {
    padding: 10px;
    background: #f0f0f0;
    text-align: center;
}

.wa-footer a {
    background: #25D366;
    color: white;
    padding: 8px 12px;
    border-radius: 5px;
    text-decoration: none;
    font-size: 13px;
}
}

#waLauncher{
    position:fixed; right:22px; bottom:22px; width:58px; height:58px; border-radius:50%;
    background:#25d366; color:#fff; border:none; font-size:28px; cursor:pointer;
    box-shadow:0 8px 20px rgba(37,211,102,.45); z-index:2147483001;
    display:flex; align-items:center; justify-content:center;
  }
  #waLauncher:hover{ transform:scale(1.05); }
 
  #waPanel{
    position:fixed; right:22px; bottom:92px; width:380px; max-width:calc(100vw - 44px);
    height:600px; max-height:calc(100vh - 120px);
    background:#fff; border-radius:14px; overflow:hidden;
    box-shadow:0 20px 50px rgba(11,20,26,.25);
    display:flex; flex-direction:column;
    transform:translateY(14px) scale(.98); opacity:0; pointer-events:none;
    transition:transform .2s ease, opacity .2s ease; z-index:2147483000;
    font-family:-apple-system,Segoe UI,Roboto,Helvetica,Arial,sans-serif;
  }
  #waPanel.open{ transform:none; opacity:1; pointer-events:auto; }
 
  /* Header */
  .wa-head{ background:var(--wa-green); color:#fff; padding:12px 14px; display:flex; align-items:center; gap:12px; min-height:59px; }
  .wa-head .wa-back{ display:none; cursor:pointer; font-size:20px; opacity:.95; }
  .wa-head .wa-title{ font-weight:600; font-size:16px; line-height:1.2; }
  .wa-head .wa-sub{ font-size:12px; opacity:.85; }
  .wa-head .wa-avatar{ width:38px; height:38px; }
 
  .wa-avatar{ flex:0 0 auto; width:42px; height:42px; border-radius:50%; background:#dfe5e7; color:#54656f;
              display:flex; align-items:center; justify-content:center; font-weight:600; }
 
  /* Screens */
  .wa-screen{ flex:1; min-height:0; display:none; flex-direction:column; }
  .wa-screen.active{ display:flex; }
 
  /* Chat list */
  #waList{ overflow:auto; background:#fff; }
  .wa-chat{ display:flex; gap:12px; padding:11px 14px; border-bottom:1px solid var(--wa-line); cursor:pointer; align-items:center; }
  .wa-chat:hover{ background:#f5f6f6; }
  .wa-meta{ flex:1; min-width:0; }
  .wa-name{ font-weight:600; color:#111b21; font-size:15px; }
  .wa-last{ color:#667781; font-size:13px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
  .wa-time{ color:#667781; font-size:11px; align-self:flex-start; }
  .wa-badge{ background:#25d366; color:#fff; border-radius:11px; min-width:20px; height:20px; font-size:11px;
             display:flex; align-items:center; justify-content:center; padding:0 6px; margin-top:4px; margin-left:auto; }
 
  /* Conversation */
  #waThread{ flex:1; overflow:auto; padding:14px; background-color:floralwhite;
             background-image:radial-gradient(rgba(0,0,0,.03) 1px, transparent 1px); background-size:18px 18px; }
  .wa-loadmore{ text-align:center; margin:0 0 10px; }
  .wa-loadmore button{ border:none; background:#fff; color:var(--wa-green); border-radius:14px; padding:5px 14px;
                       font-size:12px; cursor:pointer; box-shadow:0 1px 2px rgba(0,0,0,.1); }
  .wa-bubble{ max-width:78%; padding:7px 10px 6px; border-radius:9px; margin:4px 0; font-size:14px; line-height:1.35;
              box-shadow:0 1px .5px rgba(11,20,26,.13); position:relative; word-wrap:break-word; white-space:pre-wrap; }
  .wa-bubble.in{ background:#fff; align-self:flex-start; border-top-left-radius:2px; }
  .wa-bubble.out{ background:white; align-self:flex-end; border-top-right-radius:2px; }
  .wa-row{ display:flex; flex-direction:column; }
  .wa-stamp{ display:block; text-align:right; font-size:10px; color:#667781; margin-top:2px; }
  .wa-daysep{ text-align:center; margin:10px 0; }
  .wa-daysep span{ background:#fff; color:#54656f; font-size:11px; padding:4px 10px; border-radius:8px; box-shadow:0 1px .5px rgba(11,20,26,.13); }
 
  /* Composer */
  .wa-compose{ display:flex; gap:8px; padding:10px; background:#f0f2f5; }
  .wa-compose input{ flex:1; border:none; border-radius:20px; padding:10px 14px; font-size:14px; outline:none; }
  .wa-compose button{ border:none; background:var(--wa-green); color:#fff; width:42px; height:42px; border-radius:50%;
                      cursor:pointer; font-size:16px; }
  .wa-state{ padding:26px 20px; text-align:center; color:#667781; }
  #qrImage{ width:200px; height:200px; display:block; margin:16px auto 8px; image-rendering:pixelated; }
  div#lead_whatsapp
  {
      background: floralwhite;
    padding: 20px;
        height: 500px;
    overflow: auto;
  }
</style>
<script src="https://cdnjs.cloudflare.com/ajax/libs/crypto-js/4.2.0/crypto-js.min.js"></script>
<script>
    var userId = "<?=get_staff_user_id()?>";
 var secret = "<?=YOUR_SHARED_SECRET?>";
//  console.log(secret);
 var hash = CryptoJS.HmacSHA256(userId, secret).toString();
   var exportStatus = <?= has_permission('bulk_pdf_exporter', '', 'view') ? 1 : 0 ?>;
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
   <?php if(!empty($_GET['whatsapp'])){ ?>

 <script>
// window.WA = (function () {
//   const HOST  = "<?= rtrim(WHATSAPP_HOST, '/') ?>/";
//   const USER  = "<?= get_staff_user_id() ?>";
//   const LIMIT = 30;
 
//   let isOpen = false, view = "list";
//   let listTimer = null, chatTimer = null;
//   let current = null;                 // { number, chatId, name }
//   let messages = [], offset = 0, hasMoreOlder = true, loadingOlder = false;
//   let threadTargetId = "waThread";    // where the conversation renders (panel OR a modal)
 
//   function url(p, extra) {
//     let u = HOST + p + "?user=" + encodeURIComponent(USER) + "&token=" + encodeURIComponent(hash);
//     if (extra) for (const k in extra) u += "&" + k + "=" + encodeURIComponent(extra[k]);
//     return u + "&_=" + Date.now();
//   }
  
//     function mediaUrl(id) {
//     return HOST + "media?user=" + encodeURIComponent(USER) +
//           "&token=" + encodeURIComponent(hash) +
//           "&id=" + encodeURIComponent(id);
//   }
  
//   const esc = s => String(s == null ? "" : s).replace(/&/g,"&amp;").replace(/</g,"&lt;").replace(/>/g,"&gt;");
//   const $ = id => document.getElementById(id);
 
//   function fmtTime(ts) {
//     if (!ts) return "";
//     const d = new Date((String(ts).length > 10 ? ts : ts * 1000));
//     return d.toLocaleTimeString([], { hour: "2-digit", minute: "2-digit" });
//   }
//   function fmtDay(ts) {
//     if (!ts) return "";
//     const d = new Date((String(ts).length > 10 ? ts : ts * 1000));
//     return d.toLocaleDateString([], { day: "2-digit", month: "short", year: "numeric" });
//   }
  
//     function media_show(m) {
//     const id = m.id || "";
//     if (!id) return esc(m.body || "[media]");
//     const src = mediaUrl(id);
//     const cap = m.body ? '<div class="wa-cap">' + esc(m.body) + '</div>' : '';
//     switch (m.type) {
//       case "image":
//       case "sticker":
//         return '<img class="wa-media" src="' + src + '" ' +
//               'style="max-width:220px;border-radius:8px;cursor:pointer;display:block" ' +
//               'onclick="window.open(this.src,\'_blank\')" ' +
//               'onerror="this.replaceWith(document.createTextNode(\'[image unavailable]\'))">' + cap;
//       case "video":
//       case "gif":
//         return '<video class="wa-media" controls preload="metadata" ' +
//               'style="max-width:240px;border-radius:8px;display:block"><source src="' + src + '"></video>' + cap;
//       case "audio":
//       case "ptt":   // voice note
//         return '<audio controls preload="none" src="' + src + '" style="max-width:240px"></audio>' + cap;
//       case "document":
//       default:
//         return '<a class="wa-doc" href="' + src + '&download=1" target="_blank" rel="noopener">' +
//               '📎 ' + esc(m.body || "Download file") + '</a>';
//     }
//   }
 
//   // ---- API ----
//   async function api(p, extra) {
//     try { const r = await fetch(url(p, extra)); return r.ok ? await r.json() : null; }
//     catch (e) { console.error("WA " + p + " failed:", e); return null; }
//   }
 
//   // ---- List screen ----
//   async function refreshList() {
//     if (!isOpen || view !== "list") return;
//     const status = await api("status");
//     if (!status) { $("waList").innerHTML = '<div class="wa-state">Can\'t reach WhatsApp service. Retrying…</div>'; }
//     else if (status.connected !== true) { renderQR(); $("waHeadSub").textContent = "Scan to connect"; }
//     else {
//       $("waHeadSub").textContent = "online";
//       const data = await api("chats");
//       renderChats((data && data.chats) || []);
//     }
//     listTimer = setTimeout(refreshList, (status && status.connected) ? 12000 : 4000);
//   }
 
//   function renderQR() {
//     $("waList").innerHTML =
//       '<div class="wa-state"><p>Open WhatsApp → Linked devices → Link a device</p>' +
//       '<img id="qrImage" alt="QR"><small>Waiting for scan…</small></div>';
//     const img = $("qrImage"); img.onerror = () => console.error("QR load failed"); img.src = url("qr.png");
//   }
 
//   function renderChats(chats) {
//     if (!chats.length) { $("waList").innerHTML = '<div class="wa-state">No conversations yet.</div>'; return; }
//     $("waList").innerHTML = chats.map(c => {
//       const name = c.name || c.number || "Unknown";
//       const last = (c.lastMessage && c.lastMessage.body) ||
//                   (typeof c.lastMessage === "string" ? c.lastMessage : "") || c.last_message || "";
//       const ts   = c.timestamp || (c.lastMessage && c.lastMessage.timestamp) || c.time || 0;
//       const unread = c.unreadCount || c.unread || 0;
//       const initial = (name.trim()[0] || "?").toUpperCase();
//       return '<div class="wa-chat" onclick=\'WA.openChat(' + JSON.stringify({
//                 number: c.number || "", chatId: c.chatId || c.id || "", name: name
//              }) + ')\'>' +
//         '<div class="wa-avatar">' + esc(initial) + '</div>' +
//         '<div class="wa-meta"><div class="wa-name">' + esc(name) + '</div>' +
//         '<div class="wa-last">' + esc(last) + '</div></div>' +
//         '<div style="display:flex;flex-direction:column;align-items:flex-end">' +
//           '<span class="wa-time">' + esc(fmtTime(ts)) + '</span>' +
//           (unread ? '<span class="wa-badge">' + esc(unread) + '</span>' : '') +
//         '</div></div>';
//     }).join("");
//   }
 
//   // ---- Conversation screen ----
//     function renderThread(scrollBottom) {
//     const box = $(threadTargetId);
//     if (!box) { stopChat(); return; }   // container gone (e.g. modal closed) -> stop
//     // console.log();
//     let html = hasMoreOlder ? '<div class="wa-loadmore"><button onclick="WA.loadOlder()">Load earlier messages</button></div>' : '';
//     let lastDay = "";
//     messages.forEach(m => {
//       const out = m.fromMe === true || m.direction === "out";
//       const ts  = m.timestamp || m.time || 0;
//       const day = fmtDay(ts);
//       if (day && day !== lastDay) { html += '<div class="wa-daysep"><span>' + esc(day) + '</span></div>'; lastDay = day; }

//       const body  = (m.body != null ? m.body : (m.message || ""));
//       const inner = (m.hasMedia === true) ? media_show(m) : esc(body);
//     //   console.log(inner);
//       if(inner!=""){
//       html += '<div class="wa-row"><div class="wa-bubble ' + (out ? "out" : "in") + '">' +
//               inner + '<span class="wa-stamp">' + esc(fmtTime(ts)) + '</span></div></div>';
//       }
//     });
//     box.innerHTML = html;
//     if (scrollBottom) {
//       const toBottom = function () { box.scrollTop = box.scrollHeight; };
//       requestAnimationFrame(toBottom);
//       setTimeout(toBottom, 150);   // covers fonts / late media reflow on first open
//     }
//   }

 
//   function sortAsc(arr) {
//     return arr.slice().sort((a, b) => (a.timestamp || a.time || 0) - (b.timestamp || b.time || 0));
//   }
 
//   async function loadInitial() {
//     const box = $(threadTargetId);
//     if (box) box.innerHTML = '<div class="wa-state">Loading messages…</div>';
//     const data = await api("messages", { number: current.number, chatId: current.chatId, limit: LIMIT, offset: 0 });
//     const page = (data && (data.messages || data.data)) || [];
//     messages = sortAsc(page);
//     offset = page.length;
//     hasMoreOlder = page.length === LIMIT;
//     renderThread(true);
//   }
 
//   async function loadOlder() {
//     if (loadingOlder || !hasMoreOlder || !current) return;
//     loadingOlder = true;
//     const box = $(threadTargetId); if (!box) { loadingOlder = false; return; }
//     const prevH = box.scrollHeight;
//     const data = await api("messages", { number: current.number, chatId: current.chatId, limit: LIMIT, offset: offset });
//     const page = (data && (data.messages || data.data)) || [];
//     messages = sortAsc(page.concat(messages));
//     offset += page.length;
//     hasMoreOlder = page.length === LIMIT;
//     renderThread(false);
//     box.scrollTop = box.scrollHeight - prevH;   // keep position after prepending
//     loadingOlder = false;
//   }
 
//   async function pollNew() {
//     if (!current) return;
//     const box = $(threadTargetId);
//     if (!box) { stopChat(); return; }   // target removed -> stop polling
//     const data = await api("messages", { number: current.number, chatId: current.chatId, limit: LIMIT, offset: 0 });
//     const page = sortAsc((data && (data.messages || data.data)) || []);
//     const lastTs = messages.length ? (messages[messages.length - 1].timestamp || messages[messages.length - 1].time || 0) : 0;
//     const fresh = page.filter(m => (m.timestamp || m.time || 0) > lastTs);
//     if (fresh.length) {
//       const atBottom = box.scrollHeight - box.scrollTop - box.clientHeight < 60;
//       messages = messages.concat(fresh);
//       renderThread(atBottom);
//     }
//     chatTimer = setTimeout(pollNew, 5000);
//   }
 
//   function stopChat() {
//     if (chatTimer) { clearTimeout(chatTimer); chatTimer = null; }
//   }
 
//   // ---- Actions ----
//   function openChat(chat) {
//     current = chat; view = "chat";
//     threadTargetId = "waThread";             // render into the built-in panel
//     $("screenList").classList.remove("active");
//     $("screenChat").classList.add("active");
//     $("waBack").style.display = "inline";
//     $("waHeadTitle").textContent = chat.name;
//     $("waHeadSub").textContent = chat.number;
//     $("waHeadAvatar").textContent = (chat.name.trim()[0] || "?").toUpperCase();
//     if (listTimer) { clearTimeout(listTimer); listTimer = null; }
//     loadInitial().then(() => { stopChat(); pollNew(); });
//   }
 
//   /**
//   * Load a conversation into ANY container (e.g. a Bootstrap modal) with
//   * real-time polling. Call when the modal opens.
//   *   WA.loadThread('myModalBody', '9198xxxxxxxx', 'John Doe');
//   */
//   function loadThread(containerId, number, name, chatId) {
//     stopChat();
//     threadTargetId = containerId;
//     current = { number: number, chatId: chatId || "", name: name || number };
//     messages = []; offset = 0; hasMoreOlder = true;
 
//     // Ensure the container is a bounded scroll area, so "Load earlier" is reachable
//     // and scroll-to-bottom actually works (a tab pane has auto height by default).
//     const box = $(containerId);
//     if (box) {
//       const cs = getComputedStyle(box);
//       if (cs.overflowY !== "auto" && cs.overflowY !== "scroll") box.style.overflowY = "auto";
//       if (cs.height === "auto" || box.offsetHeight < 80) box.style.height = box.style.height || "440px";
//     }
 
//     loadInitial().then(() => { stopChat(); pollNew(); });
//   }
 
//   /** Stop the realtime polling — call when the modal closes. */
//   function stopThread() {
//     stopChat();
//     current = null;
//   }
 
//   function showList() {
//     view = "list"; current = null;
//     if (chatTimer) { clearTimeout(chatTimer); chatTimer = null; }
//     $("screenChat").classList.remove("active");
//     $("screenList").classList.add("active");
//     $("waBack").style.display = "none";
//     $("waHeadTitle").textContent = "WhatsApp";
//     $("waHeadAvatar").innerHTML = '<i class="fa fa-whatsapp"></i>';
//     refreshList();
//   }
 
//   async function send(inputId) {
//     const input = $(inputId || "waInput"); if (!input) return;
//     const text = input.value.trim();
//     if (!text || !current) return;
//     input.value = "";
//     // optimistic bubble
//     messages.push({ body: text, fromMe: true, timestamp: Math.floor(Date.now() / 1000) });
//     renderThread(true);
//     await api("send", { number: current.number, chatId: current.chatId, message: text });
//     pollNew();
//   }
 
//   function toggle() { isOpen ? close() : open(); }
//   function open() {
//     isOpen = true; $("waPanel").classList.add("open");
//     showList();
//   }
//   function close() {
//     isOpen = false; $("waPanel").classList.remove("open");
//     if (listTimer) clearTimeout(listTimer);
//     stopChat();
//     listTimer = null;
//   }
 
//   window.toggleWA = toggle; // launcher button
 
//   // public API (return MUST be last)
//   return { toggle, open, close, openChat, showList, loadOlder, send, loadThread, stopThread };
// })();
 

// window.loadInitialMessage = function (containerId, phonenumber, name='') {
//     const el = document.getElementById(containerId);

//     if (!phonenumber || !String(phonenumber).trim()) {
//         if (el) {
//             el.innerHTML = '<div class="wa-state">No phone number available.</div>';
//         }
//         return false;
//     }

//     // Normalize phone
//     let phone = String(phonenumber).replace(/\D/g, '');
//     phone = '91' + phone.slice(-10);

//     // Make container scrollable so "Load earlier messages" works
//     if (el) {
//         el.style.height = '500px';
//         el.style.overflowY = 'auto';
//     }

//     WA.loadThread(containerId, phone, name || phone);

//     return true;
// };
 
// // Stop realtime polling when the user leaves the WhatsApp tab
// if (window.jQuery) {
//     jQuery(document).on('hidden.bs.tab', 'a[href="#lead_whatsapp"]', function () {
//         WA.stopThread();
//     });
// }
 </script>

<script>
    console.log("okkkk");
/**
 * WhatsApp CRM widget — talks to the bridge JSON API.
 * Drop this into a Perfex view/footer. Requires constants:
 *   WHATSAPP_HOST          e.g. https://wa.educationvibes.in
 *   WHATSAPP_SHARED_SECRET must equal the bridge .env SHARED_SECRET
 */
window.WA = (function () {
  const HOST  = "<?= rtrim(WHATSAPP_HOST, '/') ?>/";
  const USER  = "<?= get_staff_user_id() ?>";
  // per-user token = HMAC_SHA256(userId, SHARED_SECRET) — generated server-side.
  const hash  = "<?= hash_hmac('sha256', (string) get_staff_user_id(), WHATSAPP_SHARED_SECRET) ?>";
  // AES key for payload encryption = SHA-256(SHARED_SECRET), as hex (64 chars).
  // This is the HASH of the secret (one-way), never the secret itself, so it
  // can safely live in the page. Must match crypto-util.js on the bridge.
  const ENC_KEY = "<?= hash('sha256', WHATSAPP_SHARED_SECRET) ?>";
  const LIMIT = 30;

  // ---- Payload encryption (AES-256-GCM via WebCrypto) --------------------
  // Mirrors crypto-util.js: blob = base64url( iv(12) | ciphertext | tag(16) ).
  // WebCrypto appends the 16-byte GCM tag to the ciphertext, so the byte layout
  // lines up with the Node side automatically.
  const waCrypto = (function () {
    let keyPromise = null;
    function hexToBytes(hex) {
      const a = new Uint8Array(hex.length / 2);
      for (let i = 0; i < a.length; i++) a[i] = parseInt(hex.substr(i * 2, 2), 16);
      return a;
    }
    function getKey() {
      if (!keyPromise) {
        keyPromise = crypto.subtle.importKey(
          "raw", hexToBytes(ENC_KEY), { name: "AES-GCM" }, false, ["encrypt", "decrypt"]);
      }
      return keyPromise;
    }
    function b64urlFromBytes(bytes) {
      let s = "";
      for (let i = 0; i < bytes.length; i++) s += String.fromCharCode(bytes[i]);
      return btoa(s).replace(/\+/g, "-").replace(/\//g, "_").replace(/=+$/, "");
    }
    function bytesFromB64url(str) {
      let s = String(str).replace(/-/g, "+").replace(/_/g, "/");
      while (s.length % 4) s += "=";
      const bin = atob(s);
      const out = new Uint8Array(bin.length);
      for (let i = 0; i < bin.length; i++) out[i] = bin.charCodeAt(i);
      return out;
    }
    async function encrypt(obj) {
      const key = await getKey();
      const iv  = crypto.getRandomValues(new Uint8Array(12));
      const pt  = new TextEncoder().encode(JSON.stringify(obj));
      const ctTag = new Uint8Array(await crypto.subtle.encrypt({ name: "AES-GCM", iv }, key, pt));
      const blob = new Uint8Array(iv.length + ctTag.length);
      blob.set(iv, 0); blob.set(ctTag, iv.length);
      return b64urlFromBytes(blob);
    }
    async function decrypt(blob) {
      const key = await getKey();
      const raw = bytesFromB64url(blob);
      const iv  = raw.subarray(0, 12);
      const ctTag = raw.subarray(12);
      const pt  = await crypto.subtle.decrypt({ name: "AES-GCM", iv }, key, ctTag);
      return JSON.parse(new TextDecoder().decode(pt));
    }
    return { encrypt, decrypt };
  })();

  let isOpen = false, view = "list";
  let listTimer = null, chatTimer = null;
  let current = null;                 // { number, chatId, name }
  let messages = [], offset = 0, hasMoreOlder = true, loadingOlder = false;
  let threadTargetId = "waThread";    // where the conversation renders (panel OR a modal)

  // ---- helpers ----
  // Build an encrypted GET URL: every param (incl. user/token) is packed into a
  // single AES-GCM blob carried as ?d=<blob>. Nothing readable hits the wire.
  async function url(p, extra) {
    const params = Object.assign({ user: USER, token: hash }, extra || {});
    const d = await waCrypto.encrypt(params);
    return HOST + p + "?d=" + encodeURIComponent(d);
  }
  // Encrypted /media URL. Memoized per (id+flags) so the same image keeps a
  // stable URL across re-renders -> browser + bridge disk cache still hit
  // (a fresh random IV each call would otherwise defeat caching).
  const _mediaUrlCache = {};
  async function mediaUrl(id, opts) {
    const key = id + "|" + (opts && opts.download ? "d" : "");
    if (!_mediaUrlCache[key]) {
      const params = { user: USER, token: hash, id: id };
      if (opts && opts.download) params.download = 1;
      _mediaUrlCache[key] = waCrypto.encrypt(params).then(d => HOST + "media?d=" + encodeURIComponent(d));
    }
    return _mediaUrlCache[key];
  }
  const esc = s => String(s == null ? "" : s).replace(/&/g,"&amp;").replace(/</g,"&lt;").replace(/>/g,"&gt;");
  const $ = id => document.getElementById(id);

  function fmtTime(ts) {
    if (!ts) return "";
    const d = new Date((String(ts).length > 10 ? ts : ts * 1000));
    return d.toLocaleTimeString([], { hour: "2-digit", minute: "2-digit" });
  }
  function fmtDay(ts) {
    if (!ts) return "";
    const d = new Date((String(ts).length > 10 ? ts : ts * 1000));
    return d.toLocaleDateString([], { day: "2-digit", month: "short", year: "numeric" });
  }

  // Render a media message by type. `m` is a message object from /messages.
  // The media URL is encrypted (async), so we emit the element WITHOUT a src and
  // tag it with data-wa-mid; hydrateMedia() fills in the real src/href after the
  // HTML is in the DOM. data-wa-dl marks links that should download (documents).
  function media_show(m) {
    const id = m.id || "";
    if (!id) return esc(m.body || "[media]");
    const cap = m.body ? '<div class="wa-cap">' + esc(m.body) + '</div>' : '';
    const mid = ' data-wa-mid="' + esc(id) + '"';
    switch (m.type) {
      case "image":
      case "sticker":
        return '<img class="wa-media"' + mid + ' ' +
              'style="max-width:220px;border-radius:8px;cursor:pointer;display:block" ' +
              'onclick="window.open(this.src,\'_blank\')" ' +
              'onerror="this.replaceWith(document.createTextNode(\'[image unavailable]\'))">' + cap;
      case "video":
      case "gif":
        return '<video class="wa-media"' + mid + ' controls preload="metadata" ' +
              'style="max-width:240px;border-radius:8px;display:block"></video>' + cap;
      case "audio":
      case "ptt":   // voice note
        return '<audio' + mid + ' controls preload="none" style="max-width:240px"></audio>' + cap;
      case "document":
      default:
        return '<a class="wa-doc"' + mid + ' data-wa-dl="1" target="_blank" rel="noopener">' +
              '📎 ' + esc(m.body || "Download file") + '</a>';
    }
  }

  // After media HTML lands in the DOM, resolve each element's encrypted URL and
  // assign it (src for img/video/audio, href for document links).
  function hydrateMedia(box) {
    if (!box) return;
    box.querySelectorAll("[data-wa-mid]").forEach(el => {
      if (el.dataset.waHydrated) return;
      el.dataset.waHydrated = "1";
      const id = el.getAttribute("data-wa-mid");
      const download = el.getAttribute("data-wa-dl") === "1";
      mediaUrl(id, { download }).then(u => {
        if (el.tagName === "A") el.href = u; else el.src = u;
      }).catch(() => {});
    });
  }

  // ---- API ----
  // Decrypt a response. The bridge answers encrypted requests with { d: <blob> };
  // tolerate a plain JSON body too (e.g. some pre-auth error responses).
  async function readJson(r) {
    const j = await r.json();
    if (j && typeof j.d === "string") return waCrypto.decrypt(j.d);
    return j;
  }
  // GET endpoints: params encrypted into ?d=<blob>.
  async function api(p, extra) {
    try { const r = await fetch(await url(p, extra)); return r.ok ? await readJson(r) : null; }
    catch (e) { console.error("WA " + p + " failed:", e); return null; }
  }
  // POST endpoints (send/read/logout): body is { d: <blob> } over JSON.
  async function apiPost(p, extra) {
    try {
      const d = await waCrypto.encrypt(Object.assign({ user: USER, token: hash }, extra || {}));
      const r = await fetch(HOST + p, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ d })
      });
      return r.ok ? await readJson(r) : null;
    } catch (e) { console.error("WA " + p + " failed:", e); return null; }
  }

  // ---- List screen ----
  async function refreshList() {
    if (!isOpen || view !== "list") return;
    const status = await api("status");
    if (!status) { $("waList").innerHTML = '<div class="wa-state">Can\'t reach WhatsApp service. Retrying…</div>'; }
    else if (status.connected !== true) { renderQR(); $("waHeadSub").textContent = "Scan to connect"; }
    else {
      $("waHeadSub").textContent = "online";
      const data = await api("chats");
      renderChats((data && data.chats) || []);
    }
    listTimer = setTimeout(refreshList, (status && status.connected) ? 12000 : 4000);
  }

  function renderQR() {
    $("waList").innerHTML =
      '<div class="wa-state"><p>Open WhatsApp → Linked devices → Link a device</p>' +
      '<img id="qrImage" alt="QR"><small>Waiting for scan…</small></div>';
    const img = $("qrImage"); img.onerror = () => console.error("QR load failed");
    url("qr.png").then(u => { img.src = u; });
  }

  function renderChats(chats) {
    if (!chats.length) { $("waList").innerHTML = '<div class="wa-state">No conversations yet.</div>'; return; }
    $("waList").innerHTML = chats.map(c => {
      const name = c.name || c.number || "Unknown";
      const last = (c.lastMessage && c.lastMessage.body) ||
                  (typeof c.lastMessage === "string" ? c.lastMessage : "") || c.last_message || "";
      const ts   = c.timestamp || (c.lastMessage && c.lastMessage.timestamp) || c.time || 0;
      const unread = c.unreadCount || c.unread || 0;
      const initial = (name.trim()[0] || "?").toUpperCase();
      return '<div class="wa-chat" onclick=\'WA.openChat(' + JSON.stringify({
                number: c.number || "", chatId: c.chatId || c.id || "", name: name
             }) + ')\'>' +
        '<div class="wa-avatar">' + esc(initial) + '</div>' +
        '<div class="wa-meta"><div class="wa-name">' + esc(name) + '</div>' +
        '<div class="wa-last">' + esc(last) + '</div></div>' +
        '<div style="display:flex;flex-direction:column;align-items:flex-end">' +
          '<span class="wa-time">' + esc(fmtTime(ts)) + '</span>' +
          (unread ? '<span class="wa-badge">' + esc(unread) + '</span>' : '') +
        '</div></div>';
    }).join("");
  }

  // ---- Conversation screen ----
  function renderThread(scrollBottom) {
    const box = $(threadTargetId);
    if (!box) { stopChat(); return; }   // container gone (e.g. modal closed) -> stop
    let html = hasMoreOlder ? '<div class="wa-loadmore"><button onclick="WA.loadOlder()">Load earlier messages</button></div>' : '';
    let lastDay = "";
    messages.forEach(m => {
      const out = m.fromMe === true || m.direction === "out";
      const ts  = m.timestamp || m.time || 0;
      const day = fmtDay(ts);
      if (day && day !== lastDay) { html += '<div class="wa-daysep"><span>' + esc(day) + '</span></div>'; lastDay = day; }

      const body  = (m.body != null ? m.body : (m.message || ""));
      const inner = (m.hasMedia === true) ? media_show(m) : esc(body);
      html += '<div class="wa-row"><div class="wa-bubble ' + (out ? "out" : "in") + '">' +
              inner + '<span class="wa-stamp">' + esc(fmtTime(ts)) + '</span></div></div>';
    });
    box.innerHTML = html;
    hydrateMedia(box);   // resolve encrypted media URLs now that elements exist
    if (scrollBottom) {
      const toBottom = function () { box.scrollTop = box.scrollHeight; };
      requestAnimationFrame(toBottom);
      setTimeout(toBottom, 150);   // covers fonts / late media reflow on first open
    }
  }

  function sortAsc(arr) {
    return arr.slice().sort((a, b) => (a.timestamp || a.time || 0) - (b.timestamp || b.time || 0));
  }

  async function loadInitial() {
    const box = $(threadTargetId);
    if (box) box.innerHTML = '<div class="wa-state">Loading messages…</div>';
    const data = await api("messages", { number: current.number, chatId: current.chatId, limit: LIMIT, offset: 0 });
    const page = (data && (data.messages || data.data)) || [];
    messages = sortAsc(page);
    offset = page.length;
    hasMoreOlder = page.length === LIMIT;
    renderThread(true);
  }

  async function loadOlder() {
    if (loadingOlder || !hasMoreOlder || !current) return;
    loadingOlder = true;
    const box = $(threadTargetId); if (!box) { loadingOlder = false; return; }
    const prevH = box.scrollHeight;
    const data = await api("messages", { number: current.number, chatId: current.chatId, limit: LIMIT, offset: offset });
    const page = (data && (data.messages || data.data)) || [];
    messages = sortAsc(page.concat(messages));
    offset += page.length;
    hasMoreOlder = page.length === LIMIT;
    renderThread(false);
    box.scrollTop = box.scrollHeight - prevH;   // keep position after prepending
    loadingOlder = false;
  }

  async function pollNew() {
    if (!current) return;
    const box = $(threadTargetId);
    if (!box) { stopChat(); return; }   // target removed -> stop polling
    const data = await api("messages", { number: current.number, chatId: current.chatId, limit: LIMIT, offset: 0 });
    const page = sortAsc((data && (data.messages || data.data)) || []);
    const lastTs = messages.length ? (messages[messages.length - 1].timestamp || messages[messages.length - 1].time || 0) : 0;
    const fresh = page.filter(m => (m.timestamp || m.time || 0) > lastTs);
    if (fresh.length) {
      const atBottom = box.scrollHeight - box.scrollTop - box.clientHeight < 60;
      messages = messages.concat(fresh);
      renderThread(atBottom);
    }
    chatTimer = setTimeout(pollNew, 5000);
  }

  function stopChat() {
    if (chatTimer) { clearTimeout(chatTimer); chatTimer = null; }
  }

  // ---- Actions ----
  function openChat(chat) {
    current = chat; view = "chat";
    threadTargetId = "waThread";             // render into the built-in panel
    $("screenList").classList.remove("active");
    $("screenChat").classList.add("active");
    $("waBack").style.display = "inline";
    $("waHeadTitle").textContent = chat.name;
    $("waHeadSub").textContent = chat.number;
    $("waHeadAvatar").textContent = (chat.name.trim()[0] || "?").toUpperCase();
    if (listTimer) { clearTimeout(listTimer); listTimer = null; }
    loadInitial().then(() => { stopChat(); pollNew(); });
  }

  /**
  * Load a conversation into ANY container (e.g. a Bootstrap modal) with
  * real-time polling. Call when the modal opens.
  *   WA.loadThread('myModalBody', '9198xxxxxxxx', 'John Doe');
  */
  function loadThread(containerId, number, name, chatId) {
    stopChat();
    threadTargetId = containerId;
    current = { number: number, chatId: chatId || "", name: name || number };
    messages = []; offset = 0; hasMoreOlder = true;

    const box = $(containerId);
    if (box) {
      const cs = getComputedStyle(box);
      if (cs.overflowY !== "auto" && cs.overflowY !== "scroll") box.style.overflowY = "auto";
      if (cs.height === "auto" || box.offsetHeight < 80) box.style.height = box.style.height || "440px";
    }

    loadInitial().then(() => { stopChat(); pollNew(); });
  }

  /** Stop the realtime polling — call when the modal closes. */
  function stopThread() {
    stopChat();
    current = null;
  }

  function showList() {
    view = "list"; current = null;
    if (chatTimer) { clearTimeout(chatTimer); chatTimer = null; }
    $("screenChat").classList.remove("active");
    $("screenList").classList.add("active");
    $("waBack").style.display = "none";
    $("waHeadTitle").textContent = "WhatsApp";
    $("waHeadAvatar").innerHTML = '<i class="fa fa-whatsapp"></i>';
    refreshList();
  }

  async function send(inputId) {
    const input = $(inputId || "waInput"); if (!input) return;
    const text = input.value.trim();
    if (!text || !current) return;
    input.value = "";
    // optimistic bubble
    messages.push({ body: text, fromMe: true, timestamp: Math.floor(Date.now() / 1000) });
    renderThread(true);
    await apiPost("send", { number: current.number, chatId: current.chatId, message: text });
    pollNew();
  }

  function toggle() { isOpen ? close() : open(); }
  function open() {
    isOpen = true; $("waPanel").classList.add("open");
    showList();
  }
  function close() {
    isOpen = false; $("waPanel").classList.remove("open");
    if (listTimer) clearTimeout(listTimer);
    stopChat();
    listTimer = null;
  }

  window.toggleWA = toggle; // launcher button

  // public API (return MUST be last)
  return { toggle, open, close, openChat, showList, loadOlder, send, loadThread, stopThread };
})();

// /**
//  * Global helper for the lead tab:
//  *   loadInitialMessage('whatsapp-feed', '<?= $lead->phonenumber ?>')
//  * Loads that lead's WhatsApp chat into the given container, with realtime polling.
//  */
// window.loadInitialMessage = function (containerId, phonenumber, name) {
//     const el = document.getElementById(containerId);

//     if (!phonenumber || !String(phonenumber).trim()) {
//         if (el) { el.innerHTML = '<div class="wa-state">No phone number available.</div>'; }
//         return false;
//     }

//     // Normalize phone -> 91 + last 10 digits
//     let phone = String(phonenumber).replace(/\D/g, '');
//     phone = '91' + phone.slice(-10);

//     if (el) {
//         el.style.height = '500px';
//         el.style.overflowY = 'auto';
//     }

//     WA.loadThread(containerId, phone, name || phone);
//     return true;
// };

// // Stop realtime polling when the user leaves the WhatsApp tab
// if (window.jQuery) {
//     jQuery(document).on('hidden.bs.tab', 'a[href="#lead_whatsapp"]', function () {
//         WA.stopThread();
//     });
// }
</script>



<?php } ?>





