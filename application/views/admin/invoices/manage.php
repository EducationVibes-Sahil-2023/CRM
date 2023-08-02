<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
	<div class="content">
		<div class="row">
			<?php
			include_once(APPPATH . 'views/admin/invoices/filter_params.php');
			$this->load->view('admin/invoices/list_template');
			?>
		</div>
	</div>
</div>
<?php $this->load->view('admin/includes/modals/sales_attach_file'); ?>
<script>
	var hidden_columns = [2, 6, 7, 8];
</script>
<?php init_tail(); ?>
<script>
	$(function() {
		init_invoice();
	});


	$("select[name='lead_type[]']").change(function() {
		let lead_type = $(this).val();
		lead_type = Array.isArray(lead_type) ? lead_type.join(",") : "";
		$("._filters._hidden_inputs").find("input[name='lead_types']").val(lead_type);
	});

	// Handler for changes to the select element with name "clientid"
	$("select[name='clientid']").change(function() {
		let applicant_id = $(this).val();
		$("._filters._hidden_inputs").find("input[name='applicant_id']").val(applicant_id);
	});

	$("select[name='invoice_status[]']").change(function() {
		let invoice_status = $(this).val();
		$("._filters._hidden_inputs").find("input[name='invoice_status']").val(invoice_status);
	});

	$("input[name='from_date']").change(function() {
		let from_date = $(this).val();
		$("._filters._hidden_inputs").find("input[name='from_date']").val(from_date);
	});

	$("input[name='to_date']").change(function() {
		let to_date = $(this).val();
		$("._filters._hidden_inputs").find("input[name='to_date']").val(to_date);
	});

	function filter() {

		if (to_date != '') {
			if (from_date == '') {
				$("#from_date").focus();
				return false;
			}
		}

		if (from_date != '') {
			if (to_date == '') {
				$("#to_date").focus();
				return false;
			}
		}

		show_loader("apply_filter");
		// get_invoice_summary();
		periodFilter();

	}

	function periodFilter() {
		$(".table-invoices").DataTable().ajax.reload(null, false).on('draw.dt', function() {
			hide_loader("apply_filter");
		});
	}

	var xhr; // Define xhr variable here

	function get_invoice_summary() {
		var u = {};
		var f = $("._hidden_inputs._filters input");
		u["lead_type"] = $("input[name='lead_type[]']").val();

		$.each(f, function() {
			u[$(this).attr("name")] = '[name="' + $(this).attr("name") + '"]';
		});

		if (xhr != null) {
			xhr.abort();
		}

		xhr = $.ajax({
			type: "POST",
			url: "invoices/get_invoice_summary",
			data: u,
			dataType: "JSON",
			cache: false,
			success: function(data) {
				$("#stats-top").remove();
				$(".panel-body._buttons").prepend(data);
			}
		});

		return false;
	} // Add this closing bracket
</script>
</body>

</html>