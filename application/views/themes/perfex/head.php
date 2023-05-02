<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<?php 

	$url = $this->uri->segment(2);
	if(in_array($url,array('upload_documents','basic_details','admission_preferences','academic_details','declaration','preview'))){
		$invoice_status_arr = check_invoice_status();
		if(is_array($invoice_status_arr) && count($invoice_status_arr) > 0){
			redirect(site_url('clients/invoices'));
		}
	}
?>

<!DOCTYPE html>
<html lang="<?php echo $locale; ?>">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="user-scalable=no, width=device-width, initial-scale=1, maximum-scale=1">
	<title><?php if (isset($title)){ echo $title; } ?></title>
	<?php echo compile_theme_css(); ?>
	<script src="<?php echo base_url('assets/plugins/jquery/jquery.min.js'); ?>"></script>
	<?php app_customers_head(); ?>
</head>
<body class="customers<?php if(is_mobile()){echo ' mobile';}?><?php if(isset($bodyclass)){echo ' ' . $bodyclass; } ?>" <?php if($isRTL == 'true'){ echo 'dir="rtl"';} ?>>
	<?php hooks()->do_action('customers_after_body_start'); ?>
