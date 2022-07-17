<?php
$this->load->helper('sale');
$is_integrated_credit_sale = is_sale_integrated_cc_processing($cart);
$is_ebt_sale = is_ebt_sale($cart);
$company = ($company = $this->Location->get_info_for_key('company', isset($override_location_id) ? $override_location_id : FALSE)) ? $company : $this->config->item('company');
$company_logo = ($company_logo = $this->Location->get_info_for_key('company_logo', isset($override_location_id) ? $override_location_id : FALSE)) ? $company_logo : $this->config->item('company_logo');
$item_custom_fields_to_display = array();
$sale_custom_fields_to_display = array();
$item_kit_custom_fields_to_display = array();
$customer_custom_fields_to_display = array();
$employee_custom_fields_to_display = array();
$tax_id = ($tax_id = $this->Location->get_info_for_key('tax_id', isset($override_location_id) ? $override_location_id : FALSE)) ? $tax_id : $this->config->item('tax_id');


for($k=1;$k<=NUMBER_OF_PEOPLE_CUSTOM_FIELDS;$k++) 
{
 $item_custom_field = $this->Item->get_custom_field($k,'show_on_receipt');
 $sale_custom_field = $this->Sale->get_custom_field($k,'show_on_receipt');
 $item_kit_custom_field = $this->Item_kit->get_custom_field($k,'show_on_receipt');
 $customer_custom_field = $this->Customer->get_custom_field($k,'show_on_receipt');
 $employee_custom_field = $this->Employee->get_custom_field($k,'show_on_receipt');
 
 if ($item_custom_field)
 {
 	$item_custom_fields_to_display[] = $k;
 }

 if ($sale_custom_field)
 {
 	$sale_custom_fields_to_display[] = $k;
 }
 
 if ($item_kit_custom_field)
 {
	 	$item_kit_custom_fields_to_display[] = $k;
 }
 
 if ($customer_custom_field)
 {
 	 	$customer_custom_fields_to_display[] = $k;
 }
 
 if ($employee_custom_field)
 {
 		$employee_custom_fields_to_display[] = $k;
 }
}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<!--[if !mso]><!-->
		<meta http-equiv="X-UA-Compatible" content="IE=edge" />
	<!--<![endif]-->
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<title></title>
	<!--[if (gte mso 9)|(IE)]>
	<style type="text/css">
		table {border-collapse: collapse !important;}
	</style>
	<![endif]-->
	<style type="text/css">
	
		body {
			Margin: 0;
			padding: 0;
			min-width: 100%;
			background-color: #E8EBF1;
			line-height: 20px;
		}
		table {
			border-spacing: 0;
			font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
			color: #555555;
			font-size: 13px;
		}
		td {
			padding: 0;
		}
		img {
			border: 0;
		}
		.wrapper {
			width: 100%;
			table-layout: fixed;
			-webkit-text-size-adjust: 100%;
			-ms-text-size-adjust: 100%;
		}
		.webkit {
			max-width: 600px;
			background-color: #FFFFFF;
			margin-top: 30px;
			border-radius: 6px;
			border:1px solid #DCE0E6;
		}
		.outer {
			Margin: 0 auto;
			width: 100%;
			max-width: 600px;
		}
		.inner {
			padding: 10px;
		}
		.inner.no-padding {
			padding: 0px;
		}
		.contents {
			width: 100%;
		}
		p {
			Margin: 0;
		}
		a {
			color: #ee6a56;
			text-decoration: underline;
		}
		.h1 {
			font-size: 21px;
			font-weight: bold;
			Margin-bottom: 18px;
		}
		.h2 {
			font-size: 18px;
			font-weight: bold;
			Margin-bottom: 12px;
		}
		.full-width-image img {
			width: 100%;
			max-width: 600px;
			height: auto;
		}
		.border-right
		{
			border-right: 1px solid #DCE0E6;
		}
		.border-left
		{
			border-left: 1px solid #DCE0E6;
		}
		.primary-color
		{
			color:#2196F3;
		}
		.text-right
		{
			text-align: right !important;
		}
		.receipt-header
		{
			text-align: center !important;
			height: 48px;
			background-color: #2196F3;
			color: #FFFFFF;
			border-top-left-radius: 6px;
			border-top-right-radius: 6px;
		}

		.one-column .contents {
			text-align: left;
		}
		.one-column p {
			font-size: 13px;
			Margin-bottom: 10px;
		}


		.two-column {
			text-align: center;
			font-size: 0;
			border-bottom: 1px solid #DCE0E6;
		}
		.two-column .column {
			width: 100%;
			max-width: 299px;
			display: inline-block;
			vertical-align: top;

		}
		.two-column .contents {
			font-size: 13px;
			text-align: left;
		}
		.two-column img {
			width: 100%;
			max-width: 280px;
			height: auto;
		}
		.two-column .text {
			padding-top: 0px;
		}


		.three-column {
			text-align: center;
			font-size: 0;
			padding-top: 10px;
			padding-bottom: 10px;
		}
		.three-column .column {
			width: 100%;
			max-width: 200px;
			display: inline-block;
			vertical-align: top;
		}
		.three-column img {
			width: 100%;
			max-width: 180px;
			height: auto;
		}
		.three-column .contents {
			font-size: 13px;
			text-align: center;
		}
		.three-column .text {
			padding-top: 10px;
		}


		.left-sidebar {
			text-align: center;
			font-size: 0;
		}
		.left-sidebar .column {
			width: 100%;
			display: inline-block;
			vertical-align: middle;
		}
		.left-sidebar .left {
			max-width: 100px;
		}
		.left-sidebar .right {
			max-width: 500px;
		}
		.left-sidebar .img {
			width: 100%;
			max-width: 80px;
			height: auto;
		}
		.left-sidebar .contents {
			font-size: 13px;
			text-align: center;
		}
		.left-sidebar a {
			color: #85ab70;
		}


		.right-sidebar {
			text-align: center;
			font-size: 0;
		}
		.right-sidebar .column {
			width: 100%;
			display: inline-block;
			vertical-align: middle;
		}
		.right-sidebar .left {
			max-width: 100px;
		}
		.right-sidebar .right {
			max-width: 500px;
		}
		.right-sidebar .img {
			width: 100%;
			max-width: 80px;
			height: auto;
		}
		.right-sidebar .contents {
			font-size: 13px;
			text-align: center;
		}
		.right-sidebar a {
			color: #70bbd9;
		}

		.items-table 
		{
			padding-top: 10px !important;
		}

		.padding-right
		{
			padding-right: 10px;
		}

		.item-row td
		{
			padding-top: 10px !important;
			padding-left: 10px;
			border:1px solid #DCE0E6;
			border-bottom-width: 0px;
			border-right-width: 0px;
			padding-bottom: 10px;
		}

		.item-row:last-child td
		{
			
			border:1px solid #DCE0E6;
			border-bottom-width: 1px;

		}

		.item-row:first-child
		{
			
			border:1px solid #DCE0E6;

		}

		.items-table th
		{
			background-color: #F5F5F5;
			height: 32px;
		}


		@media screen and (max-width: 400px) {
			.two-column .column,
			.three-column .column {
				max-width: 100% !important;
			}
			.two-column img {
				max-width: 100% !important;
			}
			.three-column img {
				max-width: 50% !important;
			}
		}

		@media screen and (min-width: 401px) and (max-width: 620px) {
			.three-column .column {
				max-width: 33% !important;
			}
			.two-column .column {
				max-width: 50% !important;
			}
		}
	</style>
</head>
<body style="Margin:0;padding-top:0;padding-bottom:0;padding-right:0;padding-left:0;min-width:100%;background-color:#E8EBF1;line-height:20px;" >	
	
	<center class="wrapper" style="width:100%;table-layout:fixed;-webkit-text-size-adjust:100%;-ms-text-size-adjust:100%;" >
		<?php
			if (version_compare(PHP_VERSION, '7.2', '>=') && function_exists('bcadd'))
			{
				require_once (APPPATH."libraries/hashids/vendor/autoload.php");
			
				$hashids = new Hashids\Hashids(base_url());
				$sms_id = $hashids->encode($sale_id_raw);
			
				echo '<br /><p>'.anchor(site_url('r/'.$sms_id),lang('sales_view_receipt_on_web_page')).'</p>';
			}
	
		?>
		
		<div class="webkit" style="max-width:600px;background-color:#FFFFFF;margin-top:30px;border-radius:6px;border-width:1px;border-style:solid;border-color:#DCE0E6;" >
			<!--[if (gte mso 9)|(IE)]>
			<table width="600" align="center" style="border-spacing:0;font-family:'Helvetica Neue', Helvetica, Arial, sans-serif;color:#555555;font-size:13px;" >
			<tr>
			<td style="padding-top:0;padding-bottom:0;padding-right:0;padding-left:0;" >
			<![endif]-->
			<table class="outer" align="center" style="border-spacing:0;font-family:'Helvetica Neue', Helvetica, Arial, sans-serif;color:#555555;font-size:13px;Margin:0 auto;width:100%;max-width:600px;" >
				<tr>
					<td class="one-column" style="padding-top:0;padding-bottom:0;padding-right:0;padding-left:0;" >
						<table width="100%" style="border-spacing:0;font-family:'Helvetica Neue', Helvetica, Arial, sans-serif;color:#555555;font-size:13px;" >
							<tr>
								<td class="inner contents receipt-header" style="padding-top:10px;padding-bottom:10px;padding-right:10px;padding-left:10px;width:100%;height:48px;background-color:#2196F3;color:#FFFFFF;border-top-left-radius:6px;border-top-right-radius:6px;text-align:center !important;" >
									<?php echo isset($sale_type) ? H($sale_type) : H($receipt_title); ?> #<?php echo H($sale_id); ?>
									<br />
									<?php if (isset($deleted) && $deleted) {?>
					            	<span class="text-danger" style="color: #df6c6e;"><strong><?php echo lang('sales_deleted_voided'); ?></strong></span>
										<br />
									<?php } ?>
									
									<div id="sale_time"><?php echo H($transaction_time); ?></div>
								</td>
							</tr>
						</table>
					</td>
				</tr>
				<tr>
					<td class="two-column" style="padding-top:0;padding-bottom:0;padding-right:0;padding-left:0;text-align:center;font-size:0;border-bottom-width:1px;border-bottom-style:solid;border-bottom-color:#DCE0E6;" >
						<!--[if (gte mso 9)|(IE)]>
						<table width="100%" style="border-spacing:0;font-family:'Helvetica Neue', Helvetica, Arial, sans-serif;color:#555555;font-size:13px;" >
						<tr>
						<td width="50%" valign="top" style="padding-top:0;padding-bottom:0;padding-right:0;padding-left:0;" >
						<![endif]-->
						<div class="column" style="width:100%; max-width:299px;display:inline-block;vertical-align:top;" >
							<table width="100%" style="border-spacing:0;font-family:'Helvetica Neue', Helvetica, Arial, sans-serif;color:#555555;font-size:13px;" >
								<tr>
									<td class="inner" style="padding-top:10px;padding-bottom:10px;padding-right:10px;padding-left:10px;" >
										<table class="contents" style="border-spacing:0;font-family:'Helvetica Neue', Helvetica, Arial, sans-serif;color:#555555;width:100%;font-size:13px;text-align:left;" >
											<tr>
												<td class="text" style="padding-bottom:0;padding-right:0;padding-left:0;padding-top:0px;" >
													<?php													
														if ($company_logo)
														{
															$this->load->helper('file');
														?>
														<img style="width:100px;" src="<?php echo secure_app_file_url($company_logo); ?>" />
														<br />
													<?php } ?>
													<b><?php echo H($company); ?></b>
													<?php if ($tax_id) { ?>
													<br />
													<?php echo '<b>'.lang('common_tax_id').'</b>: '.H($tax_id); ?>
													<?php } ?>
													<br />
													<?php echo nl2br(H($this->Location->get_info_for_key('address',isset($override_location_id) ? $override_location_id : FALSE))); ?>
													<br />
													<?php echo H($this->Location->get_info_for_key('phone',isset($override_location_id) ? $override_location_id : FALSE)); ?>
				  			          <?php if($this->config->item('website')) { ?>													
														<br />
														<a href="<?php echo H(prep_url($this->config->item('website'))); ?>" class="primary-color" style="text-decoration:underline;color:#2196F3;"><?php echo H($this->config->item('website')); ?></a>
													<?php } ?>
												</td>
											</tr>
										</table>
									</td>
								</tr>
							</table>
						</div>
						<!--[if (gte mso 9)|(IE)]>
						</td><td width="50%" valign="top" style="padding-top:0;padding-bottom:0;padding-right:0;padding-left:0;" >
						<![endif]-->
						<div class="column border-left" style="border-left-width:1px;border-left-style:solid;border-left-color:#DCE0E6;width:100%;max-width:299px;display:inline-block;vertical-align:top;" >
							<table width="100%" style="border-spacing:0;font-family:'Helvetica Neue', Helvetica, Arial, sans-serif;color:#555555;font-size:13px;" >
								<tr>
									<td class="inner" style="padding-top:10px;padding-bottom:10px;padding-right:10px;padding-left:10px;" >
										<table class="contents" style="border-spacing:0;font-family:'Helvetica Neue', Helvetica, Arial, sans-serif;color:#555555;width:100%;font-size:13px;text-align:left;" >
											<tr>
												<td class="text" style="padding-bottom:0;padding-right:0;padding-left:0;padding-top:0px;" >
												<?php if(isset($customer)) { ?>
													<b><?php echo lang('common_customer') ?> : </b> <?php echo H($customer); ?> <br />
													<?php if(!empty($customer_company)) { ?><?php echo '<b>'.lang('common_company').": </b>".H($customer_company); ?><br /><?php } ?>
													<?php if (!empty($customer_city)) { echo "<b>".H($customer_address_1). ' '.H($customer_address_2)." : </b>".H($customer_city.' '.$customer_state.', '.$customer_zip);} ?>

													<?php if (!empty($customer_country)) { echo H($customer_country); } ?>

													<b><?php echo lang('common_phone_number') ?> : </b><?php echo H($customer_phone); ?> <br />
													
													<?php if (!$this->config->item('hide_email_on_receipts')) { ?>													
														<b><?php echo lang('common_email') ?> : </b><?php echo H($customer_email); ?> <br />
													<?php } ?>
													
													<?php
													foreach($customer_custom_fields_to_display as $custom_field_id)
													{
													?>
															<?php 
																$customer_info = $this->Customer->get_info($customer_id);
												
																if ($customer_info->{"custom_field_${custom_field_id}_value"})
																{
																?>
					                    	<div class="invoice-desc">
																<?php

																if ($this->Customer->get_custom_field($custom_field_id,'type') == 'checkbox')
																{
																	$format_function = 'boolean_as_string';
																}
																elseif($this->Customer->get_custom_field($custom_field_id,'type') == 'date')
																{
																	$format_function = 'date_as_display_date';				
																}
																elseif($this->Customer->get_custom_field($custom_field_id,'type') == 'email')
																{
																	$format_function = 'strsame';					
																}
																elseif($this->Customer->get_custom_field($custom_field_id,'type') == 'url')
																{
																	$format_function = 'strsame';					
																}
																elseif($this->Customer->get_custom_field($custom_field_id,'type') == 'phone')
																{
																	$format_function = 'strsame';					
																}
																else
																{
																	$format_function = 'strsame';
																}
												
																echo '<b>'.($this->Customer->get_custom_field($custom_field_id,'hide_field_label') ? '' : $this->Customer->get_custom_field($custom_field_id,'name').':').'</b> '.$format_function($customer_info->{"custom_field_${custom_field_id}_value"}).'<br />';
																?>
															</div>
															<?php
														}
													}
													?>

												<?php } ?>
												
												<?php if(isset($delivery_person_info)) { ?>
													<br />
													<b><?php echo lang('deliveries_shipping_address') ?> : </b> <br />
													<b><?php echo lang('common_name').": ".H($delivery_person_info['first_name'].' '.$delivery_person_info['last_name']); ?></b><br />

													<?php if(!empty($delivery_person_info['address_1']) || !empty($delivery_person_info['address_2'])){ ?><span><?php echo lang('common_address'); ?> : <?php echo H($delivery_person_info['address_1']. ' '.$delivery_person_info['address_2']); ?></span><?php } ?><br />
													<?php if (!empty($delivery_person_info['city'])) { echo '<span>'.H($delivery_person_info['city'].' '.$delivery_person_info['state'].', '.$delivery_person_info['zip']).'</span>';} ?><br />
													<?php if (!empty($delivery_person_info['country'])) { echo '<span>'.H($delivery_person_info['country']).'</span>';} ?><br />
													<?php if(!empty($delivery_person_info['phone'])){ ?><span><?php echo lang('common_phone_number'); ?> : <?php echo H($delivery_person_info['phone']); ?></span><?php } ?><br />
													<?php if(!empty($delivery_person_info['email'])){ ?><span><?php echo lang('common_email'); ?> : <?php echo H($delivery_person_info['email']); ?></span><?php } ?><br />
												<?php } ?>
												
												<?php if(!empty($delivery_info['estimated_delivery_or_pickup_date']) || !empty($delivery_info['tracking_number']) ||  !empty($delivery_info['comment'])) {?>
														<span><?php echo lang('deliveries_delivery_information');?>:</span><br />
														<?php if(!empty($delivery_info['estimated_delivery_or_pickup_date'])){ ?><span><?php echo lang('deliveries_estimated_delivery_or_pickup_date'); ?> : <?php echo date(get_date_format().' '.get_time_format(),strtotime($delivery_info['estimated_delivery_or_pickup_date'])); ?></span><br /><?php } ?>
														<?php if(!empty($delivery_info['tracking_number'])){ ?><span><?php echo lang('deliveries_tracking_number'); ?> : <?php echo H($delivery_info['tracking_number']); ?></span><br /><?php } ?>
														<?php if(!empty($delivery_info['comment'])){ ?><span><?php echo lang('common_comment'); ?> : <?php echo H($delivery_info['comment']); ?></span><br /><?php } ?>
												<?php } ?>
												
												</td>

											</tr>
										</table>
									</td>
								</tr>
							</table>
						</div>
						<!--[if (gte mso 9)|(IE)]>
						</td>
						</tr>
						</table>
						<![endif]-->
					</td>
				</tr>
					<tr>
					<td class="two-column" style="padding-top:0;padding-bottom:0;padding-right:0;padding-left:0;text-align:center;font-size:0;border-bottom-width:1px;border-bottom-style:solid;border-bottom-color:#DCE0E6;" >
						<!--[if (gte mso 9)|(IE)]>
						<table width="100%" style="border-spacing:0;font-family:'Helvetica Neue', Helvetica, Arial, sans-serif;color:#555555;font-size:13px;" >
						<tr>
						<td width="50%" valign="top" style="padding-top:0;padding-bottom:0;padding-right:0;padding-left:0;" >
						<![endif]-->
						<div class="column" style="width:100%;max-width:299px;display:inline-block;vertical-align:top;" >
							<table width="100%" style="border-spacing:0;font-family:'Helvetica Neue', Helvetica, Arial, sans-serif;color:#555555;font-size:13px;" >
								<tr>
									<td class="inner" style="padding-top:10px;padding-bottom:10px;padding-right:10px;padding-left:10px;" >
										<table class="contents" style="border-spacing:0;font-family:'Helvetica Neue', Helvetica, Arial, sans-serif;color:#555555;width:100%;font-size:13px;text-align:left;" >
											<tr>
												<td class="text" style="padding-bottom:0;padding-right:0;padding-left:0;padding-top:0px;" >
												
												<?php if ($register_name) { ?>
													<?php echo "<b>".lang('common_register_name').':</b> '.H($register_name); ?>
												<?php } ?>
												
												<?php if ($tier) { ?>
													<?php echo "<b>".lang('common_tier_name').':</b> '.H($tier); ?>
												<?php } ?>


												</td>
											</tr>
										</table>
									</td>
								</tr>
							</table>
						</div>
						<!--[if (gte mso 9)|(IE)]>
						</td><td width="50%" valign="top" style="padding-top:0;padding-bottom:0;padding-right:0;padding-left:0;" >
						<![endif]-->
						<div class="column" style="width:100%;max-width:299px;display:inline-block;vertical-align:top;" >
							<table width="100%" style="border-spacing:0;font-family:'Helvetica Neue', Helvetica, Arial, sans-serif;color:#555555;font-size:13px;" >
								<tr>
									<td class="inner" style="padding-top:10px;padding-bottom:10px;padding-right:10px;padding-left:10px;" >
										<table class="contents" style="border-spacing:0;font-family:'Helvetica Neue', Helvetica, Arial, sans-serif;color:#555555;width:100%;font-size:13px;text-align:left;" >
											<tr>
												<td class="text text-right" style="padding-bottom:0;padding-right:0;padding-left:0;text-align:right !important;padding-top:0px;" >
														<?php echo "<b>".lang('common_employee').":</b> ".H($employee); ?>		

							<?php
							foreach($employee_custom_fields_to_display as $custom_field_id)
							{
							?>
									<?php 
									
										$employee_info = $this->Employee->get_info($sold_by_employee_id);
										
										if ($employee_info->{"custom_field_${custom_field_id}_value"})
										{
										?>
                  	<div class="invoice-desc">
										<?php

										if ($this->Employee->get_custom_field($custom_field_id,'type') == 'checkbox')
										{
											$format_function = 'boolean_as_string';
										}
										elseif($this->Employee->get_custom_field($custom_field_id,'type') == 'date')
										{
											$format_function = 'date_as_display_date';				
										}
										elseif($this->Employee->get_custom_field($custom_field_id,'type') == 'email')
										{
											$format_function = 'strsame';					
										}
										elseif($this->Employee->get_custom_field($custom_field_id,'type') == 'url')
										{
											$format_function = 'strsame';					
										}
										elseif($this->Employee->get_custom_field($custom_field_id,'type') == 'phone')
										{
											$format_function = 'strsame';					
										}
										else
										{
											$format_function = 'strsame';
										}
										
										echo '<b><span>'.lang('common_employee').' '.($this->Employee->get_custom_field($custom_field_id,'hide_field_label') ? '' : $this->Employee->get_custom_field($custom_field_id,'name').':').'</b> '.$format_function($employee_info->{"custom_field_${custom_field_id}_value"}).'<br />';
										?>
									</div>
									<?php
								}
							}
							?>
														<?php 
														if($this->Location->get_info_for_key('enable_credit_card_processing',isset($override_location_id) ? $override_location_id : FALSE))
														{
															if (!$this->config->item('hide_merchant_id_from_receipt'))
															{
																echo '<br/><b>'.lang('common_merchant_id').':</b> '.H($this->Location->get_merchant_id(isset($override_location_id) ? $override_location_id : FALSE));
															}
														}
														?>
												</td>
											</tr>
										</table>
									</td>
								</tr>
							</table>
						</div>
						<!--[if (gte mso 9)|(IE)]>
						</td>
						</tr>
						</table>
						<![endif]-->
					</td>
				</tr>
				<tr>
					<td class="one-column" style="padding-top:0;padding-bottom:0;padding-right:0;padding-left:0;" >
						<table width="100%" style="border-spacing:0;font-family:'Helvetica Neue', Helvetica, Arial, sans-serif;color:#555555;font-size:13px;" >
							<tr>
								<td class="inner no-padding" style="padding-top:10px;padding-bottom:10px;padding-right:10px;padding-left:10px;" >
									<table width="100%" class="items-table" style="border-spacing:0;font-family:'Helvetica Neue', Helvetica, Arial, sans-serif;color:#555555;font-size:13px;padding-top:10px !important;" >
										<tr>
											<?php
												$column_width = "100px";
												$total_columns = 4;
										 	
											 	if($discount_exists) { $column_width = "75px"; $total_columns = 5; } 
											 ?>

											<th width="300px" style="background-color:#F5F5F5;height:32px;" ><?php echo lang('common_item'); ?></th>
											<th width="<?php echo $column_width ?>" style="background-color:#F5F5F5;height:32px;" ><?php echo lang('common_price'); ?></th>
											<th width="<?php echo $column_width ?>" style="background-color:#F5F5F5;height:32px;" ><?php echo lang('common_quantity'); ?></th>

											<?php if($discount_exists) { ?>
												<th width="<?php echo $column_width ?>" style="background-color:#F5F5F5;height:32px;" ><?php echo lang('common_discount_percent'); ?></th>
											<?php } ?>

											<th width="<?php echo $column_width ?>" style="background-color:#F5F5F5;height:32px;" ><?php echo lang('common_total'); ?></th>
										</tr>
										

										<?php
										if ($discount_item_line = $cart->get_index_for_flat_discount_item())
										{
											$discount_item = $cart->get_item($discount_item_line);
											$cart->delete_item($discount_item_line);
											$cart->add_item($discount_item,false);
											$cart_items = $cart->get_items();
										}
										
											foreach(array_reverse($cart_items, true) as $line=>$item)
											{
												
												if ($item->tax_included)
												{
													if (get_class($item) == 'PHPPOSCartItemSale')
													{
														if ($item->tax_included)
														{
															$this->load->helper('items');
															$unit_price = get_price_for_item_including_taxes($item->item_id, $item->unit_price);
														}
													}
													else
													{
														if ($item->tax_included)
														{
															$this->load->helper('item_kits');
															$unit_price = get_price_for_item_kit_including_taxes($item->item_kit_id, $item->unit_price);
														}
													}
												}
												else
												{
													$unit_price = $item->unit_price;
												}
												
												$item_number_for_receipt = false;
												
												if ($this->config->item('show_item_id_on_receipt'))
												{
													switch($this->config->item('id_to_show_on_sale_interface'))
													{
														case 'number':
														$item_number_for_receipt = $item->item_number;
														break;
													
														case 'product_id':
														$item_number_for_receipt = $item->product_id;
														break;
													
														case 'id':
														$item_number_for_receipt = $item->item_id;
														break;
													
														default:
														$item_number_for_receipt = $item->item_number;
														break;
													}
												}
											?>
										<tr class="text-center item-row">
											<td style="padding-right:0;padding-top:10px !important;padding-left:10px;border-width:1px;border-style:solid;border-color:#DCE0E6;border-bottom-width:0px;border-right-width:0px;padding-bottom:10px;" >
												<?php echo H($item->name).((get_class($item) == 'PHPPOSCartItemSale' && $item->variation_name) ? '- '.H($item->variation_name) : '' ); ?><?php if ($item_number_for_receipt){ ?> - <?php echo H($item_number_for_receipt); ?><?php } ?><?php if (!$this->config->item('hide_desc_emailed_receipts') && $item->description){ ?> - <?php echo clean_html($item->description); ?><?php } ?><?php if ($item->size){ ?> (<?php echo H($item->size); ?>)<?php } ?>
												
												<?php
												if (property_exists($item,'quantity_unit_quantity') && $item->quantity_unit_quantity !== NULL)
												{													?>
	                    	<div class="invoice-desc">
														<?php 
															echo 	lang('common_quantity_unit_name'). ': '.$item->quantity_units[$item->quantity_unit_id].', '.lang('common_quantity_units').': ' .H(to_quantity($item->quantity_unit_quantity));
          									?>
													</div>
												
												<?php } ?>
												
											</td>
											<td align="center" style="padding-right:0;padding-top:10px !important;padding-left:10px;border-width:1px;border-style:solid;border-color:#DCE0E6;border-bottom-width:0px;border-right-width:0px;padding-bottom:10px;" >
												<?php echo to_currency($unit_price+$item->get_modifier_unit_total(),10); ?>
											</td>
											<td  align="center" style="padding-right:0;padding-top:10px !important;padding-left:10px;border-width:1px;border-style:solid;border-color:#DCE0E6;border-bottom-width:0px;border-right-width:0px;padding-bottom:10px;" >
												<?php echo to_quantity($item->quantity);?>
											</td>
											<?php if($discount_exists) { ?>
												<td  align="center" style="padding-right:0;padding-top:10px !important;padding-left:10px;border-width:1px;border-style:solid;border-color:#DCE0E6;border-bottom-width:0px;border-right-width:0px;padding-bottom:10px;" >
													<?php echo to_quantity($item->discount); ?>
												</td>
											<?php } ?>
										
											<td  align="center" style="padding-right:0;padding-top:10px !important;padding-left:10px;border-width:1px;border-style:solid;border-color:#DCE0E6;border-bottom-width:0px;border-right-width:0px;padding-bottom:10px;" >
												<?php echo to_currency(+$item->get_modifiers_subtotal()+($unit_price*$item->quantity-$item->unit_price*$item->quantity*$item->discount/100),10); ?>
											</td>
										</tr>
										
										
										<?php
										foreach($item_custom_fields_to_display as $custom_field_id)
										{
										?>
												<?php 
												if(get_class($item) == 'PHPPOSCartItemSale' && $this->Item->get_custom_field($custom_field_id) !== false)
												{
													$item_info = $this->Item->get_info($item->item_id);
													
													if ($item_info->{"custom_field_${custom_field_id}_value"})
													{
													?>
													<tr class="text-center item-row"><td colspan="5">
													<?php

													if ($this->Item->get_custom_field($custom_field_id,'type') == 'checkbox')
													{
														$format_function = 'boolean_as_string';
													}
													elseif($this->Item->get_custom_field($custom_field_id,'type') == 'date')
													{
														$format_function = 'date_as_display_date';				
													}
													elseif($this->Item->get_custom_field($custom_field_id,'type') == 'email')
													{
														$format_function = 'strsame';					
													}
													elseif($this->Item->get_custom_field($custom_field_id,'type') == 'url')
													{
														$format_function = 'strsame';					
													}
													elseif($this->Item->get_custom_field($custom_field_id,'type') == 'phone')
													{
														$format_function = 'strsame';					
													}
													else
													{
														$format_function = 'strsame';
													}
													
													echo ($this->Item->get_custom_field($custom_field_id,'hide_field_label') ? '' : $this->Item->get_custom_field($custom_field_id,'name').':').' '.$format_function($item_info->{"custom_field_${custom_field_id}_value"});
													?>
												</td></tr>
												<?php
												}
											}
										}
											
										foreach($item_kit_custom_fields_to_display as $custom_field_id)
										{
											if(get_class($item) == 'PHPPOSCartItemKitSale' && $this->Item_kit->get_custom_field($custom_field_id) !== false && $this->Item_kit->get_custom_field($custom_field_id) !== false)
											{
													$item_info = $this->Item_kit->get_info($item->item_kit_id);
													
													if ($item_info->{"custom_field_${custom_field_id}_value"})
													{
													?>
													<tr class="text-center item-row"><td colspan="5">
													<?php

													if ($this->Item_kit->get_custom_field($custom_field_id,'type') == 'checkbox')
													{
														$format_function = 'boolean_as_string';
													}
													elseif($this->Item_kit->get_custom_field($custom_field_id,'type') == 'date')
													{
														$format_function = 'date_as_display_date';				
													}
													elseif($this->Item_kit->get_custom_field($custom_field_id,'type') == 'email')
													{
														$format_function = 'strsame';					
													}
													elseif($this->Item_kit->get_custom_field($custom_field_id,'type') == 'url')
													{
														$format_function = 'strsame';					
													}
													elseif($this->Item_kit->get_custom_field($custom_field_id,'type') == 'phone')
													{
														$format_function = 'strsame';					
													}
													else
													{
														$format_function = 'strsame';
													}
													
													echo ($this->Item_kit->get_custom_field($custom_field_id,'hide_field_label') ? '' : $this->Item_kit->get_custom_field($custom_field_id,'name').':').' '.$format_function($item_info->{"custom_field_${custom_field_id}_value"});
													?>
												</td></tr>
												<?php
												}
											}
										?>
										<?php
										}
										?>
										
										<?php } ?>
										<?php
										foreach($sale_custom_fields_to_display as $custom_field_id)
										{
											if($this->Sale->get_custom_field($custom_field_id) !== false && $this->Sale->get_custom_field($custom_field_id) !== false)
											{											
													if ($cart->{"custom_field_${custom_field_id}_value"})
													{
													?>						
													<?php

													if ($this->Sale->get_custom_field($custom_field_id,'type') == 'checkbox')
													{
														$format_function = 'boolean_as_string';
													}
													elseif($this->Sale->get_custom_field($custom_field_id,'type') == 'date')
													{
														$format_function = 'date_as_display_date';				
													}
													elseif($this->Sale->get_custom_field($custom_field_id,'type') == 'email')
													{
														$format_function = 'strsame';					
													}
													elseif($this->Sale->get_custom_field($custom_field_id,'type') == 'url')
													{
														$format_function = 'strsame';					
													}
													elseif($this->Sale->get_custom_field($custom_field_id,'type') == 'phone')
													{
														$format_function = 'strsame';					
													}
													else
													{
														$format_function = 'strsame';
													}
													?>
													
													<tr class="text-center item-row">
														<td colspan="1000" style="padding-right:0;padding-top:10px !important;padding-left:10px;border-width:1px;border-style:solid;border-color:#DCE0E6;border-bottom-width:0px;border-right-width:0px;padding-bottom:10px;" >
															<?php echo $this->Sale->get_custom_field($custom_field_id,'name'); ?><br />
															<?php echo $format_function($cart->{"custom_field_${custom_field_id}_value"}); ?>
					
														</td>
													</tr>
													
													<?php
												}
											}
										}
										?>
										<?php if ($exchange_name) { ?>
										
										<tr class="text-center item-row">
											<td colspan="<?php echo $total_columns-1; ?>" class=" padding-right" align="right" style="padding-right:10px;padding-top:10px !important;padding-left:10px;border-width:1px;border-style:solid;border-color:#DCE0E6;border-bottom-width:0px;border-right-width:0px;padding-bottom:10px;" >
												<?php echo lang('common_exchange_to').' '.H($exchange_name); ?>
											</td>
											<td  align="center" style="padding-right:0;padding-top:10px !important;padding-left:10px;border-width:1px;border-style:solid;border-color:#DCE0E6;border-bottom-width:0px;border-right-width:0px;padding-bottom:10px;" >
												x <?php echo to_currency_no_money($exchange_rate); ?>
											</td>
										</tr>
										
									<?php } ?>
									
									<tr class="text-center item-row">
										<td colspan="<?php echo $total_columns-1; ?>" class=" padding-right" align="right" style="padding-right:10px;padding-top:10px !important;padding-left:10px;border-width:1px;border-style:solid;border-color:#DCE0E6;border-bottom-width:0px;border-right-width:0px;padding-bottom:10px;" >
											<?php echo lang('common_sub_total'); ?>
										</td>
										<td  align="center" style="padding-right:0;padding-top:10px !important;padding-left:10px;border-width:1px;border-style:solid;border-color:#DCE0E6;border-bottom-width:0px;border-right-width:0px;padding-bottom:10px;" >
												<?php if (isset($exchange_name) && $exchange_name) { 
													echo to_currency_as_exchange($cart,$subtotal);
												?>
												<?php } else {  ?>
												<?php echo to_currency($subtotal); ?>				
												<?php
												}
												?>
										</td>
									</tr>
									
									
									<?php $total_tax = 0; foreach ($taxes as $name => $value) { $total_tax += $value; ?>
											<tr class="text-center item-row">
												<td colspan="<?php echo $total_columns-1; ?>" class=" padding-right" align="right" style="padding-right:10px;padding-top:10px !important;padding-left:10px;border-width:1px;border-style:solid;border-color:#DCE0E6;border-bottom-width:0px;border-right-width:0px;padding-bottom:10px;" >
													<?php echo $name; ?>:
												</td>
												<td  align="center" style="padding-right:0;padding-top:10px !important;padding-left:10px;border-width:1px;border-style:solid;border-color:#DCE0E6;border-bottom-width:0px;border-right-width:0px;padding-bottom:10px;" >
													<?php if (isset($exchange_name) && $exchange_name) { 
														echo to_currency_as_exchange($cart,$value*$exchange_rate);					
													?>
													<?php } else {  ?>
													<?php echo to_currency($value); ?>				
													<?php
													}
													?>
												</td>
											</tr>
										<?php }; $total_tax_amount = to_currency($total_tax); ?>


										<tr class="text-center item-row">
											<td colspan="<?php echo $total_columns-1; ?>" class=" padding-right" align="right" style="padding-right:10px;padding-top:10px !important;padding-left:10px;border-width:1px;border-style:solid;border-color:#DCE0E6;border-bottom-width:0px;border-right-width:0px;padding-bottom:10px;" >
												<b><?php echo lang('common_total'); ?></b>
											</td>
											<td  align="center" style="padding-right:0;padding-top:10px !important;padding-left:10px;border-width:1px;border-style:solid;border-color:#DCE0E6;border-bottom-width:0px;border-right-width:0px;padding-bottom:10px;" >
												<b> 
												
												<?php if (isset($exchange_name) && $exchange_name) { 
													?>
													<?php echo $total_invoice_amount = $this->config->item('round_cash_on_sales') && $is_sale_cash_payment ?  to_currency_as_exchange($cart,round_to_nearest_05($total)) : to_currency_as_exchange($cart,$total); ?>				
												<?php } else {  ?>
												<?php echo $total_invoice_amount = $this->config->item('round_cash_on_sales') && $is_sale_cash_payment ?  to_currency(round_to_nearest_05($total)) : to_currency($total); ?>				
												<?php
												}
												?>
												</b>
											</td>
										</tr>

									  	<tr><td colspan="<?php echo $total_columns; ?>">&nbsp;</td></tr>

									    <?php 
											foreach($payments as $payment_id=>$payment) 
											{ 	
												if ($payment->payment_type == lang('common_store_account'))
												{
													$amount_due=$payment->payment_amount;
												}
												?>
											<tr class="text-center item-row">
												<td colspan="<?php echo $total_columns-2; ?>" class=" padding-right" align="right" style="padding-right:10px;padding-top:10px !important;padding-left:10px;border-width:1px;border-style:solid;border-color:#DCE0E6;border-bottom-width:0px;border-right-width:0px;padding-bottom:10px;" >
													<?php echo (isset($show_payment_times) && $show_payment_times) ?  date(get_date_format().' '.get_time_format(), strtotime($payment->payment_date)) : lang('common_payment'); ?>
												</td>

												<?php if ($is_integrated_credit_sale || $is_ebt_sale || sale_has_partial_credit_card_payment($cart) || sale_has_partial_ebt_payment($cart)) { ?>
													<td  align="center" style="padding-right:0;padding-top:10px !important;padding-left:10px;border-width:1px;border-style:solid;border-color:#DCE0E6;border-bottom-width:0px;border-right-width:0px;padding-bottom:10px;" ><?php $splitpayment=explode(':',$payment->payment_type); echo H($splitpayment[0]); ?> </td>											 
													<td  align="center" style="padding-right:0;padding-top:10px !important;padding-left:10px;border-width:1px;border-style:solid;border-color:#DCE0E6;border-bottom-width:0px;border-right-width:0px;padding-bottom:10px;" ><?php echo H($payment->card_issuer. ' '.$payment->truncated_card); ?></td>											 
												<?php } else { ?>
													<td  align="center" style="padding-right:0;padding-top:10px !important;padding-left:10px;border-width:1px;border-style:solid;border-color:#DCE0E6;border-bottom-width:0px;border-right-width:0px;padding-bottom:10px;" ><?php $splitpayment=explode(':',$payment->payment_type); echo H($splitpayment[0]); ?> </td>											 
												<?php } ?>


												<td  align="center" style="padding-right:0;padding-top:10px !important;padding-left:10px;border-width:1px;border-style:solid;border-color:#DCE0E6;border-bottom-width:0px;border-right-width:0px;padding-bottom:10px;" >
													
													<?php 
													if (isset($exchange_name) && $exchange_name) { 
														?>
														<?php echo $this->config->item('round_cash_on_sales') && $payment->payment_type == lang('common_cash') ?  to_currency_as_exchange($cart,round_to_nearest_05($payment->payment_amount)) : to_currency_as_exchange($cart,$payment->payment_amount); ?>				
													<?php } else {  ?>
													<?php echo $this->config->item('round_cash_on_sales') && $payment->payment_type == lang('common_cash') ?  to_currency(round_to_nearest_05($payment->payment_amount)) : to_currency($payment->payment_amount); ?>				
													<?php
													}
									
									
													?>
												</td>
											</tr>
										<?php } ?>
										
										<?php foreach($payments as $payment) {?>
											<?php if (strpos($payment->payment_type, lang('common_giftcard'))=== 0) {?>
												<?php $giftcard_payment_row = explode(':', $payment->payment_type); ?>
												<td colspan="<?php echo $total_columns-2; ?>" class=" padding-right" align="right" style="padding-right:10px;padding-top:10px !important;padding-left:10px;border-width:1px;border-style:solid;border-color:#DCE0E6;border-bottom-width:0px;border-right-width:0px;padding-bottom:10px;" ><?php echo lang('sales_giftcard_balance'); ?></td>											 
												<td  align="center" style="padding-right:0;padding-top:10px !important;padding-left:10px;border-width:1px;border-style:solid;border-color:#DCE0E6;border-bottom-width:0px;border-right-width:0px;padding-bottom:10px;" ><?php echo H($payment->payment_type);?></td>											 
												<td  align="center" style="padding-right:0;padding-top:10px !important;padding-left:10px;border-width:1px;border-style:solid;border-color:#DCE0E6;border-bottom-width:0px;border-right-width:0px;padding-bottom:10px;" ><?php echo to_currency($this->Giftcard->get_giftcard_value(end($giftcard_payment_row))); ?></td>												
											<?php }?>
										<?php }?> 
										

									  	<tr><td colspan="<?php echo $total_columns; ?>">&nbsp;</td></tr>

										<?php if ($amount_change >= 0) { ?>
										<tr>
											<td  colspan="<?php echo $total_columns-1; ?>"   align="center" style="padding-right:0;padding-top:10px !important;padding-left:10px;border-width:1px;border-style:solid;border-color:#DCE0E6;border-bottom-width:0px;border-right-width:0px;padding-bottom:10px;" ><?php echo lang('common_change_due'); ?></td>
											<td  align="center" style="padding-right:0;padding-top:10px !important;padding-left:10px;border-width:1px;border-style:solid;border-color:#DCE0E6;border-bottom-width:0px;border-right-width:0px;padding-bottom:10px;" >
												
												<?php if (isset($exchange_name) && $exchange_name) { 
													?>
													<?php echo $this->config->item('round_cash_on_sales')  && $is_sale_cash_payment ?  to_currency_as_exchange($cart,round_to_nearest_05($amount_change)) : to_currency_as_exchange($cart,$amount_change); ?>				
												<?php } else {  ?>
												<?php echo $this->config->item('round_cash_on_sales')  && $is_sale_cash_payment ?  to_currency(round_to_nearest_05($amount_change)) : to_currency($amount_change); ?>				
												<?php
												}
												?>
												
												
												
											</td>
										</tr>
										<?php } else { ?>
											<tr>
												<td  colspan="<?php echo $total_columns-1; ?>"   align="center" style="padding-right:0;padding-top:10px !important;padding-left:10px;border-width:1px;border-style:solid;border-color:#DCE0E6;border-bottom-width:0px;border-right-width:0px;padding-bottom:10px;" ><?php echo lang('common_amount_due'); ?></td>
												<td  align="center" style="padding-right:0;padding-top:10px !important;padding-left:10px;border-width:1px;border-style:solid;border-color:#DCE0E6;border-bottom-width:0px;border-right-width:0px;padding-bottom:10px;" >
													
													<?php if (isset($exchange_name) && $exchange_name) { 
														?>
													<?php echo $this->config->item('round_cash_on_sales')  && $is_sale_cash_payment ?  to_currency_as_exchange($cart,round_to_nearest_05($amount_change * -1)) : to_currency_as_exchange($cart,$amount_change * -1); ?>
													<?php } else {  ?>
													<?php echo $this->config->item('round_cash_on_sales')  && $is_sale_cash_payment ?  to_currency(round_to_nearest_05($amount_change * -1)) : to_currency($amount_change * -1); ?>
													<?php
													}
													?>
													
												</td>
											</tr>	
										<?php } ?>
										
										<?php if (isset($customer_balance_for_sale) && (float)$customer_balance_for_sale && !$this->config->item('hide_store_account_balance_on_receipt')) { ?>
											<tr>
												<td  colspan="<?php echo $total_columns-1; ?>"   align="center" style="padding-right:0;padding-top:10px !important;padding-left:10px;border-width:1px;border-style:solid;border-color:#DCE0E6;border-bottom-width:0px;border-right-width:0px;padding-bottom:10px;" ><?php echo lang('sales_customer_account_balance'); ?></td>
												<td  align="center" style="padding-right:0;padding-top:10px !important;padding-left:10px;border-width:1px;border-style:solid;border-color:#DCE0E6;border-bottom-width:0px;border-right-width:0px;padding-bottom:10px;" >
												<?php echo to_currency($customer_balance_for_sale); ?> </td>
											</tr>
										<?php } ?>
										
										<?php if (!$disable_loyalty && $this->config->item('enable_customer_loyalty_system') && isset($sales_until_discount) && !$this->config->item('hide_sales_to_discount_on_receipt') && $this->config->item('loyalty_option') == 'simple') {?>
											<tr>
												<td  colspan="<?php echo $total_columns-1; ?>"   align="center" style="padding-right:0;padding-top:10px !important;padding-left:10px;border-width:1px;border-style:solid;border-color:#DCE0E6;border-bottom-width:0px;border-right-width:0px;padding-bottom:10px;" ><?php echo lang('common_sales_until_discount'); ?></td>
												<td  align="center" style="padding-right:0;padding-top:10px !important;padding-left:10px;border-width:1px;border-style:solid;border-color:#DCE0E6;border-bottom-width:0px;border-right-width:0px;padding-bottom:10px;" >
												<?php echo $sales_until_discount <= 0 ? lang('sales_redeem_discount_for_next_sale') : to_quantity($sales_until_discount); ?> </td>
											</tr>
										<?php
										}
										?>
										
										<?php if (!$disable_loyalty && $this->config->item('enable_customer_loyalty_system') && isset($customer_points) && !$this->config->item('hide_points_on_receipt') && $this->config->item('loyalty_option') == 'advanced') {?>
											<tr>
												<td  colspan="<?php echo $total_columns-1; ?>"   align="center" style="padding-right:0;padding-top:10px !important;padding-left:10px;border-width:1px;border-style:solid;border-color:#DCE0E6;border-bottom-width:0px;border-right-width:0px;padding-bottom:10px;" ><?php echo lang('common_points'); ?></td>
												<td  align="center" style="padding-right:0;padding-top:10px !important;padding-left:10px;border-width:1px;border-style:solid;border-color:#DCE0E6;border-bottom-width:0px;border-right-width:0px;padding-bottom:10px;" >
												<?php echo to_currency_no_money($customer_points); ?> </td>
											</tr>
										<?php
										}
										?>
										
										
										
										<?php if (isset($ref_no) && $ref_no) { ?>
											<tr>
												<td  colspan="<?php echo $total_columns-1; ?>"   align="center" style="padding-right:0;padding-top:10px !important;padding-left:10px;border-width:1px;border-style:solid;border-color:#DCE0E6;border-bottom-width:0px;border-right-width:0px;padding-bottom:10px;" ><?php echo lang('sales_ref_no'); ?></td>
												<td  align="center" style="padding-right:0;padding-top:10px !important;padding-left:10px;border-width:1px;border-style:solid;border-color:#DCE0E6;border-bottom-width:0px;border-right-width:0px;padding-bottom:10px;" ><?php echo H($ref_no); ?></td>
											</tr>	
										<?php } ?>
										
										<?php if (isset($auth_code) && $auth_code) { ?>
											<tr>
												<td  colspan="<?php echo $total_columns-1; ?>"   align="center" style="padding-right:0;padding-top:10px !important;padding-left:10px;border-width:1px;border-style:solid;border-color:#DCE0E6;border-bottom-width:0px;border-right-width:0px;padding-bottom:10px;" ><?php echo lang('sales_auth_code'); ?></td>
												<td  align="center" style="padding-right:0;padding-top:10px !important;padding-left:10px;border-width:1px;border-style:solid;border-color:#DCE0E6;border-bottom-width:0px;border-right-width:0px;padding-bottom:10px;" ><?php echo H($auth_code); ?></td>
											</tr>	
										<?php } ?>
										
										<?php
										if ($this->config->item('show_total_discount_on_receipt')) { ?>
										
										<tr>
											<td  colspan="<?php echo $total_columns-1; ?>"   align="center" style="padding-right:0;padding-top:10px !important;padding-left:10px;border-width:1px;border-style:solid;border-color:#DCE0E6;border-bottom-width:0px;border-right-width:0px;padding-bottom:10px;" ><?php echo lang('sales_total_discount'); ?></td>
											<td  align="center" style="padding-right:0;padding-top:10px !important;padding-left:10px;border-width:1px;border-style:solid;border-color:#DCE0E6;border-bottom-width:0px;border-right-width:0px;padding-bottom:10px;" ><?php echo to_currency($cart->get_total_discount()); ?></td>
										</tr>	
										
										<?php
										}	
										?>
									</table>
								</td>
							</tr>
						</table>
					</td>
				</tr>
				<tr>
					<td class="one-column" style="padding-top:0;padding-bottom:0;padding-right:0;padding-left:0;" >
						<table width="100%" style="border-spacing:0;font-family:'Helvetica Neue', Helvetica, Arial, sans-serif;color:#555555;font-size:13px;" >
							<tr>
								<td class="inner contents" style="padding-top:10px;padding-bottom:10px;padding-right:10px;padding-left:10px;width:100%;text-align:left;" >
									<p style="Margin:0;font-size:13px;Margin-bottom:10px;" >
										<?php 
											if(isset($show_comment_on_receipt) && $show_comment_on_receipt == 1)
											{ 
												echo lang('common_comments').": ". H($comment); 
											} 
										?>
									</p>
								</td>
							</tr>
						</table>
					</td>
				</tr>
				<tr>
					<td class="one-column" style="padding-top:0;padding-bottom:0;padding-right:0;padding-left:0;" >
						<table width="100%" style="border-spacing:0;font-family:'Helvetica Neue', Helvetica, Arial, sans-serif;color:#555555;font-size:13px;" >
							<tr>
								<td class="inner contents" style="padding-top:10px;padding-bottom:10px;padding-right:10px;padding-left:10px;width:100%;text-align:left;" >
									<p style="Margin:0;font-size:13px;Margin-bottom:10px;" >
										<?php echo nl2br(H($this->config->item('return_policy'))); ?>
									</p>
								</td>
							</tr>
						</table>
					</td>
				</tr>
				
				<?php
				if ($this->config->item('paypal_me') && $sale_type != lang('common_estimate'))
				{					
					if (isset($amount_due) && $amount_due) 
					{
						$this->lang->load('reports');
					?>
						<tr class="text-center item-row">
							<td  colspan="<?php echo $total_columns; ?>"align="center" style="padding-right:0;padding-top:10px !important;padding-left:10px;border-width:1px;border-style:solid;border-color:#DCE0E6;border-bottom-width:0px;border-right-width:0px;padding-bottom:10px;" >
								<h2><?php echo anchor('https://paypal.me/'.$this->config->item('paypal_me').'/'.to_currency_no_money($amount_due),lang('reports_pay_with_paypal'));?></h2>
							</td>
						</tr>
						
						<?php
					}
				}
					
				?>
		
		
		<?php if ($this->config->item('show_qr_code_for_sale')  && !$store_account_payment) {?>

		<?php 	
			$qrcode = '';
			if ($this->config->item('qr_code_format') == 'sale_summary_info') { 
				$qrcode_info = array(
					lang('common_company').': '.$this->config->item('company'),
					lang('common_tax_id').': '.$this->config->item('tax_id'),
					lang('sales_invoice_date').': '.H($transaction_time),
					lang('common_total', '', array(), TRUE).': '.strip_tags($total_invoice_amount),
					lang('common_tax', '', array(), TRUE).': '.strip_tags($total_tax_amount)
				);
				$qrcode = implode(",".PHP_EOL, $qrcode_info);
			} else if ($this->config->item('qr_code_format') == 'saudi_arabia_digital_receipt'){

				require_once(APPPATH . "libraries/Tlvstr.php");

				$qrdata = array(
					'seller_name' => $this->config->item('company') ? $this->config->item('company') : '', 
					'tax_number' => $this->config->item('tax_id') ? $this->config->item('tax_id') : '',
					'invoice_date' => date(DATE_ISO8601,strtotime($transaction_time)),
					'invoice_total_amount' => make_currency_no_money(strip_tags($total_invoice_amount)),
					'invoice_tax_amount' => make_currency_no_money(strip_tags($total_tax_amount)),
				);
				
				$tlvstr = new Tlvstr($qrdata);
				$qrcode = $tlvstr->generate();

			} else { 
				require_once(APPPATH . "libraries/hashids/vendor/autoload.php");

				$hashids = new Hashids\Hashids(base_url());
				$sms_id = $hashids->encode($sale_id_raw);
				$qrcode = site_url('r/' . $sms_id);
			} 
		?>
			
			<tr class="text-center item-row">
				<td  colspan="<?php echo $total_columns; ?>"align="center" style="padding-right:0;padding-top:10px !important;padding-left:10px;border-width:1px;border-style:solid;border-color:#DCE0E6;border-bottom-width:0px;border-right-width:0px;padding-bottom:10px;" >
					<?php	
						$file = file_get_contents_curl(site_url('qrcodegenerator/index?qrcode='.urlencode($qrcode)));
						$base64_file_data = base64_encode($file);
					?>
					<img style="width:100px;" src="data:image/png;base64,<?php echo $base64_file_data ?>" />
				</td>
			</tr>
			
			<?php } ?>
								
			</table>
			<!--[if (gte mso 9)|(IE)]>
			</td>
			</tr>
			</table>
			<![endif]-->
		</div>
	</center>
</body>
</html>