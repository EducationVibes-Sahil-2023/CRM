<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php include_once(APPPATH . 'views/admin/includes/helpers_bottom.php'); ?>

<?php hooks()->do_action('before_js_scripts_render'); ?>

<?php echo app_compile_scripts();

/**
 * Global function for custom field of type hyperlink
 */
echo get_custom_fields_hyperlink_js_function(); ?>
<?php
/**
 * Check for any alerts stored in session
 */
app_js_alerts();
?>
<?php
/**
 * Check pusher real time notifications
 */
if (get_option('pusher_realtime_notifications') == 1) { ?>
   <script type="text/javascript">
      $(function() {
         // Enable pusher logging - don't include this in production
         // Pusher.logToConsole = true;
         <?php $pusher_options = hooks()->apply_filters('pusher_options', array(['disableStats' => true]));
         if (!isset($pusher_options['cluster']) && get_option('pusher_cluster') != '') {
            $pusher_options['cluster'] = get_option('pusher_cluster');
         }
         ?>
         var pusher_options = <?php echo json_encode($pusher_options); ?>;
         var pusher = new Pusher("<?php echo get_option('pusher_app_key'); ?>", pusher_options);
         var channel = pusher.subscribe('notifications-channel-<?php echo get_staff_user_id(); ?>');
         channel.bind('notification', function(data) {
            fetch_notifications();
         });
      });
   </script>
<?php } ?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.0/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/2.0.5/FileSaver.min.js"></script>
<?php
if (is_admin()) {
?>
   <script>
      var facebook_notification = "<?= !empty($this->session->userdata("Facebook_Error")) ? $this->session->userdata("Facebook_Error") : '' ?>"
      var facebook_notification_show = "<?= !empty($this->session->userdata("Facebook_Error_show")) ? $this->session->userdata("Facebook_Error_show") : 0 ?>"
      if (facebook_notification_show == 1) {
         var html_fb_notification = `<div class = "alert alert-primary facebook-notification-div"
      role = "alert" >  <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        ` + facebook_notification + `
         </div>`;
         $(".content").prepend(html_fb_notification);
      }
   </script>
<?php
}
?>

<?php app_admin_footer(); ?>