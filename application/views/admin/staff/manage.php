<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
	<div class="content">
		<div class="row">
			<div class="col-md-12">

				<div class="panel_s">
					<div class="panel-body">
						<?php if (has_permission('staff', '', 'create')) { ?>
							<div class="_buttons">
								<a href="<?php echo admin_url('staff/member'); ?>" class="btn btn-info pull-left display-block"><?php echo _l('new_staff'); ?></a>
							</div>
							<div class="clearfix"></div>
							<hr class="hr-panel-heading" />
						<?php } ?>
						<div class="clearfix"></div>
						<?php
						$table_data = array(
							_l('staff_dt_name'),
							'Emp Code',
							_l('staff_dt_email'),
							_l('phone'),
							'Alternative No.',
							'Department',
							'Office Region',
							'Lead State Region',
							'Counsellor',
							_l('role'),
							_l('staff_dt_last_Login'),
							_l('staff_dt_active'),
						);
						$custom_fields = get_custom_fields('staff', array('show_on_table' => 1));
						foreach ($custom_fields as $field) {
							array_push($table_data, $field['name']);
						}
						render_datatable($table_data, 'staff');
						?>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<div class="modal fade" id="delete_staff" tabindex="-1" role="dialog">
	<div class="modal-dialog" role="document">
		<?php echo form_open(admin_url('staff/delete', array('delete_staff_form'))); ?>
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title"><?php echo _l('delete_staff'); ?></h4>
			</div>
			<div class="modal-body">
				<div class="delete_id">
					<?php echo form_hidden('id'); ?>
				</div>
				<p><?php echo _l('delete_staff_info'); ?></p>
				<?php
				echo render_select('transfer_data_to', $staff_members, array('staffid', array('firstname', 'lastname')), 'staff_member', get_staff_user_id(), array(), array(), '', '', false);
				?>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
				<button type="submit" class="btn btn-danger _delete"><?php echo _l('confirm'); ?></button>
			</div>
		</div><!-- /.modal-content -->
		<?php echo form_close(); ?>
	</div><!-- /.modal-dialog -->
</div><!-- /.modal -->


<div class="modal fade" id="edit_phonenumber" tabindex="-1" role="dialog">
	<div class="modal-dialog" role="document">
		<form method="POST" id="staff_contact_edit" onsubmit="return false;">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title"><?php echo _l('Edit Phonenumber'); ?></h4>
				</div>
				<div class="modal-body">
					<div class="form-group">
					    	<input type="hidden" id="alternative_phonenumber_status">
						<input type="hidden" id="staff_id">
						<input type="number" class="form-control" id="staff_contact">
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
					<button type="submit" class="btn btn-success"><?php echo _l('save'); ?></button>
				</div>
			</div><!-- /.modal-content -->
			<?php echo form_close(); ?>
	</div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<?php init_tail(); ?>
<script>
	var staff_data_table = ""
	$(function() {
		staff_data_table = initDataTable('.table-staff', window.location.href);
	});

	function delete_staff_member(id) {
		$('#delete_staff').modal('show');
		$('#transfer_data_to').find('option').prop('disabled', false);
		$('#transfer_data_to').find('option[value="' + id + '"]').prop('disabled', true);
		$('#delete_staff .delete_id input').val(id);
		$('#transfer_data_to').selectpicker('refresh');
	}

	function edit_staff_phone_number(id, number,alternative_phonenumber_status=0) {
		$("#staff_contact").val(number);
		$("#staff_id").val(id);
		$("#alternative_phonenumber_status").val(alternative_phonenumber_status);
		$('#edit_phonenumber').modal('show');
	}

	$("#staff_contact_edit").submit(function() {
		var formData = new FormData();
		var phonenumber = $("#staff_contact").val();
		var staffid = $("#staff_id").val();
		var alternative_phonenumber_status = $("#alternative_phonenumber_status").val();
		formData.append("staffid", staffid);
		if(alternative_phonenumber_status==1)
		{
		    formData.append("alternative_phonenumber", phonenumber);
		}
		else{
		formData.append("phonenumber", phonenumber);
		}
		
		
		// Assuming you are using CSRF protection, add the CSRF token to the form data
		formData.append("<?= $this->security->get_csrf_token_name() ?>", "<?= $this->security->get_csrf_hash() ?>");

		$.ajax({
			url: "<?= admin_url('staff/edit_phonenumber') ?>",
			type: "POST",
			processData: false, // Don't process the data
			contentType: false, // Don't set contentType
			data: formData,
			success: function(response) {
				// Parse the JSON response
				var response_data = JSON.parse(response);
				if (response_data.status == 1) {
					alert_float("success", response_data.message, 1500);
					$('#edit_phonenumber').modal('hide');
					staff_data_table.ajax.reload();
				} else {
					alert_float("danger", response_data.message, 500);
				}
			},
			error: function(xhr, status, error) {
				// Handle error
				console.error("Error saving data:", error);
				// You can implement error handling logic here, such as displaying an error message to the user
			}
		});


	})
</script>
</body>

</html>