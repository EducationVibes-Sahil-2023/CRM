<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<style>
  .whatsapp-qr-scanner {
    display: inline-block;
    float: right;
    height: 200px;
  }

  .whatsapp-qr-scanner {
    text-align: center;
    /* Center align the content */
  }

  .qr_scanner.opacity {
    opacity: 0.1;
  }

  .qr_scanner {
    display: block;
    /* Ensure the image is a block element */
    max-height: 100%;
    /* Make sure it doesn't exceed its container width */
    margin: 0 auto;
    /* Center horizontally */
  }

  .whatsapp-logout-button {
    position: absolute;
    top: 45%;
    right: 10%;
    z-index: 999;
  }
</style>
<div id="wrapper">
  <div class="content">
    <div class="row">
      <?php if (($staff_p->staffid == get_staff_user_id() || is_admin()) && !$this->input->get('notifications')) { ?>
        <div class="col-md-12">
          <div class="panel_s">
            <div class="panel-body no-padding-bottom">
              <?php $this->load->view('admin/staff/stats'); ?>
            </div>
          </div>
        </div>
      <?php } ?>
      <?php hooks()->do_action('before_staff_myprofile'); ?>
      <div class="col-md-5<?php if ($this->input->get('notifications')) {
                            echo ' hide';
                          } ?>">
        <div class="panel_s">

          <div class="panel-body">
            <h4 class="no-margin">
              <?php echo _l('staff_profile_string'); ?>
            </h4>
            <hr class="hr-panel-heading" />
            <?php if ($staff_p->active == 0) { ?>
              <div class="alert alert-danger text-center"><?php echo _l('staff_profile_inactive_account'); ?></div>
              <hr />
            <?php } ?>
            <div class="button-group mtop10 pull-right">
              <?php if (!empty($staff_p->facebook)) { ?>
                <a href="<?php echo html_escape($staff_p->facebook); ?>" target="_blank" class="btn btn-default btn-icon"><i class="fa fa-facebook"></i></a>
              <?php } ?>
              <?php if (!empty($staff_p->linkedin)) { ?>
                <a href="<?php echo html_escape($staff_p->linkedin); ?>" class="btn btn-default btn-icon"><i class="fa fa-linkedin"></i></a>
              <?php } ?>
              <?php if (!empty($staff_p->skype)) { ?>
                <a href="skype:<?php echo html_escape($staff_p->skype); ?>" data-toggle="tooltip" title="<?php echo html_escape($staff_p->skype); ?>" target="_blank" class="btn btn-default btn-icon"><i class="fa fa-skype"></i></a>
              <?php } ?>
              <?php if (has_permission('staff', '', 'edit') && has_permission('staff', '', 'view')) { ?>
                <a href="<?php echo admin_url('staff/member/' . $staff_p->staffid); ?>" class="btn btn-default btn-icon"><i class="fa fa-pencil-square"></i></a>
              <?php } ?>
            </div>
            <div class="clearfix"></div>
            <?php if (is_admin($staff_p->staffid)) { ?>
              <p class="pull-right text-info"><?php echo _l('staff_admin_profile'); ?></p>
            <?php } ?>
            <?php echo staff_profile_image($staff_p->staffid, array('staff-profile-image-thumb'), 'thumb'); ?>
            <?php if (has_permission('whatsapp', '', 'view')) { ?>
              <div class="whatsapp-qr-scanner"></div>
            <?php } ?>
            <div class="profile mtop20 display-inline-block">
              <h4>
                <?php echo $staff_p->firstname . ' ' . $staff_p->lastname; ?>
                <?php if ($staff_p->last_activity && $staff_p->staffid != get_staff_user_id()) { ?>
                  <small> - <?php echo _l('last_active'); ?>:
                    <span class="text-has-action" data-toggle="tooltip" data-title="<?php echo _dt($staff_p->last_activity); ?>">
                      <?php echo time_ago($staff_p->last_activity); ?>
                    </span>
                  </small>
                <?php } ?>
              </h4>
              <p class="display-block"><i class="fa fa-envelope"></i> <a href="mailto:<?php echo $staff_p->email; ?>"><?php echo $staff_p->email; ?></a></p>
              <?php if ($staff_p->phonenumber != '') { ?>
                <p><i class="fa fa-phone-square"></i> <?php echo $staff_p->phonenumber; ?></p>
              <?php } ?>
              <?php if (count($staff_departments) > 0) { ?>
                <div class="form-group mtop10">
                  <label for="departments" class="control-label"><?php echo _l('staff_profile_departments'); ?></label>
                  <div class="clearfix"></div>
                  <?php
                  foreach ($departments as $department) { ?>
                    <?php
                    foreach ($staff_departments as $staff_department) {
                      if ($staff_department['departmentid'] == $department['departmentid']) { ?>
                        <div class="chip-circle"><?php echo $staff_department['name']; ?></div>
                    <?php }
                    }
                    ?>
                  <?php } ?>
                </div>
              <?php } ?>
            </div>
          </div>
        </div>

        <div class="panel_s">
          <div class="panel-body">
            <h4 class="no-margin">
              <?php echo "Assects Allocation" ?>
            </h4>
            <hr class="hr-panel-heading" />

            <div class="_filters _hidden_inputs hidden staff_projects_filter">
              <?php echo form_hidden('staff_id', $staff_p->staffid); ?>
            </div>
            <?php
            $table_data = array(
              _l('time'),
              _l('asset_name'),
              _l('acction_code'),
              _l('action'),
              _l('quantity_as_qty'),
              _l('acction_from'),
              _l('acction_to'),
            );
            render_datatable($table_data, 'table_action');
            ?>
            <br>
            <hr>
            <br>
            <h4 class="no-margin">
              <?php echo "Assects Revoke" ?>
            </h4>
            <hr>
            <?php
            $table_data_revoke = array(
              _l('time'),
              _l('asset_name'),
              _l('acction_code'),
              _l('action'),
              _l('quantity_as_qty'),
              _l('acction_from'),
              _l('acction_to'),
            );
            render_datatable($table_data_revoke, 'table_action_revoke');
            ?>
          </div>
        </div>

        <?php if (($staff_p->staffid == get_staff_user_id() || is_admin()) && !$this->input->get('notifications')) { ?>
          <div class="panel_s">
            <div class="panel-body">
              <h4 class="no-margin">
                <?php echo _l('projects'); ?>
              </h4>
              <hr class="hr-panel-heading" />
              <div class="_filters _hidden_inputs hidden staff_projects_filter">
                <?php echo form_hidden('staff_id', $staff_p->staffid); ?>
              </div>
              <?php render_datatable(array(
                _l('project_name'),
                _l('project_start_date'),
                _l('project_deadline'),
                _l('project_status'),
              ), 'staff-projects', [], [
                'data-last-order-identifier' => 'my-projects',
                'data-default-order'  => get_table_last_order('my-projects'),
              ]); ?>
            </div>
          </div>
        <?php } ?>


      </div>
      <?php if ($staff_p->staffid == get_staff_user_id()) { ?>
        <div class="col-md-7<?php if ($this->input->get('notifications')) {
                              echo ' col-md-offset-2';
                            } ?>">
          <div class="panel_s">
            <div class="panel-body">
              <h4 class="no-margin">
                <?php echo _l('staff_profile_notifications'); ?>

              </h4>
              <a href="#" onclick="mark_all_notifications_as_read_inline(); return false;"><?php echo _l('mark_all_as_read'); ?></a>
              <hr class="hr-panel-heading" />
              <div id="notifications">
              </div>
              <a href="#" class="btn btn-info loader"><?php echo _l('load_more'); ?></a>
            </div>
          </div>
        </div>
      <?php } ?>
    </div>
  </div>
</div>
<?php init_tail(); ?>
<script>
  $(function() {
    var notifications = $('#notifications');
    if (notifications.length > 0) {
      var page = 0;
      var total_pages = '<?php echo $total_pages; ?>';
      $('.loader').on('click', function(e) {
        e.preventDefault();
        if (page <= total_pages) {
          $.post(admin_url + 'staff/notifications', {
            page: page
          }).done(function(response) {
            response = JSON.parse(response);
            var notifications = '';
            $.each(response, function(i, obj) {
              notifications += '<div class="notification-wrapper" data-notification-id="' + obj.id + '">';
              notifications += '<div class="notification-box-all' + (obj.isread_inline == 0 ? ' unread' : '') + '">';
              var link_notification = '';
              var link_class_indicator = '';
              if (obj.link) {
                link_notification = ' data-link="' + admin_url + obj.link + '"';
                link_class_indicator = ' notification_link';
              }
              notifications += obj.profile_image;
              notifications += '<div class="media-body' + link_class_indicator + '"' + link_notification + '>';
              notifications += '<div class="description">';
              if (obj.from_fullname) {
                notifications += obj.from_fullname + ' - ';
              }
              notifications += obj.description;
              notifications += '</div>';
              notifications += '<small class="text-muted text-right text-has-action" data-placement="right" data-toggle="tooltip" data-title="' + obj.full_date + '">' + obj.date + '</small>';
              if (obj.isread_inline == 0) {
                notifications += '<a href="#" class="text-muted pull-right not-mark-as-read-inline notification-profile" onclick="set_notification_read_inline(' + obj.id + ')" data-placement="left" data-toggle="tooltip" data-title="<?php echo _l('mark_as_read'); ?>"><small><i class="fa fa-circle-thin" aria-hidden="true"></i></a></small>';
              }
              notifications += '</div>';
              notifications += '</div>';
              notifications += '</div>';
            });

            $('#notifications').append(notifications);
            page++;
          });

          if (page >= total_pages - 1) {
            $(".loader").addClass("disabled");
          }
        }
      });

      $('.loader').click();
    }
  });

  initDataTable('.table-table_action', admin_url + 'assets/table_action_allocate/allocation?action_to=<?= isset($staff_p) ?  $staff_p->staffid : null ?>');
  initDataTable('.table-table_action_revoke', admin_url + 'assets/table_action_allocate/revoke?action_to=<?= isset($staff_p) ?  $staff_p->staffid : null ?>');
</script>
</body>

</html>