<!DOCTYPE html>
<html class="<?php echo $this->config->item('language');?>">
<head>
	<meta charset="UTF-8" />
    <title><?php 
		 $this->load->helper('demo');
	 	 $company = ($company = $this->Location->get_info_for_key('company')) ? $company : $this->config->item('company');
		 echo !is_on_demo_host() ?  $company.' -- '.lang('common_powered_by').' PHP Point Of Sale' : 'Demo - PHP Point Of Sale | Easy to use Online POS Software' ?></title>
	<link rel="icon" href="<?php echo base_url();?>favicon.ico" type="image/x-icon"/>	
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0"/> <!--320-->
	<base href="<?php echo base_url();?>" />
	
	<?php
	if ($this->agent->browser() == 'Chrome')
	{
	?>
	<style>
	@page {
		margin: 0;
		padding: 0;
	}
	</style>
	<?php } ?>
	
	<style>
		@media print {
			.invoice-table-content{ page-break-inside: avoid !important; -webkit-page-break-inside: avoid !important; }
			.panel_inventory_print_list .panel-body{padding:40px 30px !important; border: 0 !important;}
			.panel_inventory_print_list .report-header{display:block !important;}
		}
	</style>
	
	<link rel="icon" href="<?php echo base_url();?>favicon.ico" type="image/x-icon"/>
	<?php 
	$this->load->helper('assets');
	foreach(get_css_files() as $css_file) { ?>
		<link rel="stylesheet" type="text/css" href="<?php echo base_url().$css_file['path'].'?'.ASSET_TIMESTAMP;?>" />
	<?php } ?>
	<?php foreach(get_js_files() as $js_file) { ?>
		<script src="<?php echo base_url().$js_file['path'].'?'.ASSET_TIMESTAMP;?>" type="text/javascript" charset="UTF-8"></script>
	<?php } ?>	
</head>
<body>