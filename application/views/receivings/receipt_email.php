<?php
$company = ($company = $this->Location->get_info_for_key('company', isset($override_location_id) ? $override_location_id : FALSE)) ? $company : $this->config->item('company');
$company_logo = ($company_logo = $this->Location->get_info_for_key('company_logo', isset($override_location_id) ? $override_location_id : FALSE)) ? $company_logo : $this->config->item('company_logo');
$item_custom_fields_to_display = array();
$supplier_custom_fields_to_display = array();
$receiving_custom_fields_to_display = array();

for($k=1;$k<=NUMBER_OF_PEOPLE_CUSTOM_FIELDS;$k++) 
{
 $item_custom_field = $this->Item->get_custom_field($k,'show_on_receipt');
 $supplier_custom_field = $this->Supplier->get_custom_field($k,'show_on_receipt');
 $recv_custom_field = $this->Receiving->get_custom_field($k,'show_on_receipt');
 
 if ($recv_custom_field)
 {
 	$receiving_custom_fields_to_display[] = $k;
 }
 
 if ($item_custom_field)
 {
 	$item_custom_fields_to_display[] = $k;
 }
 
 if ($supplier_custom_field)
 {
 	 	$supplier_custom_fields_to_display[] = $k;
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
										<?php if (!isset($transfer_to_location)) {?>
									<?php echo $is_po ? lang('receivings_purchase_order') : H($receipt_title); ?> #<?php echo $is_po ? H($receiving_id_raw) : H($receiving_id); ?>
											<?php } else { 
												?>
												<?php echo lang('receivings_transfer_id')?> #<?php echo H($receiving_id_raw);?>
											<?php
											} ?>
									<br />
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
						<div class="column" style="width:100%; height:100%;max-width:299px;display:inline-block;vertical-align:top;" >
							<table width="100%" style="border-spacing:0;font-family:'Helvetica Neue', Helvetica, Arial, sans-serif;color:#555555;font-size:13px;" >
								<tr>
									<td class="inner" style="padding-top:10px;padding-bottom:10px;padding-right:10px;padding-left:10px;" >
										<table class="contents" style="border-spacing:0;font-family:'Helvetica Neue', Helvetica, Arial, sans-serif;color:#555555;width:100%;font-size:13px;text-align:left;" >
											<tr>
												<td class="text" style="padding-bottom:0;padding-right:0;padding-left:0;padding-top:0px;" >
													<?php
														$this->load->helper('file');
														if ($company_logo)
														{
															?>
															<img style="width:100px;" src="<?php echo secure_app_file_url($company_logo); ?>" />
													<?php
														}
													?>
													
													<br>
													<b><?php echo H($company); ?></b>
													<br />
													<?php echo nl2br(H($this->Location->get_info_for_key('address',isset($override_location_id) ? $override_location_id : FALSE))); ?>
													<br />
													<?php echo $this->Location->get_info_for_key('phone',isset($override_location_id) ? $override_location_id : FALSE); ?>
				  			               	 <?php if($this->config->item('website')) { ?>													
														<br />
														<a href="<?php echo prep_url(H($this->config->item('website'))); ?>" class="primary-color" style="text-decoration:underline;color:#2196F3;"><?php echo H($this->config->item('website')); ?></a>
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
						<div class="column border-left" style="border-left-width:1px;border-left-style:solid;border-left-color:#DCE0E6;width:100%; height:100%;max-width:299px;display:inline-block;vertical-align:top;" >
							<table width="100%" style="border-spacing:0;font-family:'Helvetica Neue', Helvetica, Arial, sans-serif;color:#555555;font-size:13px;" >
								<tr>
									<td class="inner" style="padding-top:10px;padding-bottom:10px;padding-right:10px;padding-left:10px;" >
										<table class="contents" style="border-spacing:0;font-family:'Helvetica Neue', Helvetica, Arial, sans-serif;color:#555555;width:100%;font-size:13px;text-align:left;" >
											<tr>
												<td class="text" style="padding-bottom:0;padding-right:0;padding-left:0;padding-top:0px;" >
												<?php if(isset($supplier)) { ?>
													<b><?php echo lang('common_supplier') ?> : </b> <?php echo H($supplier); ?> <br />
													<?php if (!empty($supplier_city)) { echo "<b>".H($supplier_address_1. ' '.$supplier_address_2." : </b>".$supplier_city.' '.$supplier_state.', '.$supplier_zip);} ?>

													<?php if (!empty($supplier_country)) { echo H($supplier_country); } ?>

													<b><?php echo lang('common_phone_number') ?> : </b><?php echo H($supplier_phone); ?> <br />
													<b><?php echo lang('common_email') ?> : </b><?php echo H($supplier_email); ?> <br />
													
													<?php
													foreach($supplier_custom_fields_to_display as $custom_field_id)
													{
													?>
															<?php 
									
																$supplier_info = $this->Supplier->get_info($supplier_id);
										
																if ($supplier_info->{"custom_field_${custom_field_id}_value"})
																{
																?>
						                  	<div class="invoice-desc">
																<?php

																if ($this->Supplier->get_custom_field($custom_field_id,'type') == 'checkbox')
																{
																	$format_function = 'boolean_as_string';
																}
																elseif($this->Supplier->get_custom_field($custom_field_id,'type') == 'date')
																{
																	$format_function = 'date_as_display_date';				
																}
																elseif($this->Supplier->get_custom_field($custom_field_id,'type') == 'email')
																{
																	$format_function = 'strsame';					
																}
																elseif($this->Supplier->get_custom_field($custom_field_id,'type') == 'url')
																{
																	$format_function = 'strsame';					
																}
																elseif($this->Supplier->get_custom_field($custom_field_id,'type') == 'phone')
																{
																	$format_function = 'strsame';					
																}
																else
																{
																	$format_function = 'strsame';
																}
										
																echo '<b>'.lang('common_supplier').' '.($this->Supplier->get_custom_field($custom_field_id,'hide_field_label') ? '' : $this->Supplier->get_custom_field($custom_field_id,'name').':').'</b> '.$format_function($supplier_info->{"custom_field_${custom_field_id}_value"}).'<br />';
																?>
															</div>
															<?php
														}
													}
													?>
													
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
			</table>
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
										<tr border="1">
											<?php
												$column_width = "75px";
												$total_columns = 5;
												
												if (!$this->config->item('hide_size_field'))
												{
													$total_columns++;
												}
												
												if ($this->config->item('show_selling_price_on_recv'))
												{
													$total_columns++;
												}
											 ?>

											<th width="300px" style="background-color:#F5F5F5;height:32px;" ><?php echo lang('common_item'); ?></th>
											<?php
											if (!$this->config->item('hide_size_field'))
											{
											?>
											<th width="<?php echo $column_width ?>" style="background-color:#F5F5F5;height:32px;" ><?php echo lang('common_size'); ?></th>
											<?php
											}
											?>
											<th width="<?php echo $column_width ?>" style="background-color:#F5F5F5;height:32px;" ><?php echo lang('common_price'); ?></th>
											
											<?php
											if ($this->config->item('show_selling_price_on_recv'))
											{
											?>
												<th width="<?php echo $column_width ?>" style="background-color:#F5F5F5;height:32px;" ><?php echo lang('common_unit_price'); ?></th>
											<?php
											}
											?>
											<th width="<?php echo $column_width ?>" style="background-color:#F5F5F5;height:32px;" ><?php echo lang('common_quantity'); ?></th>
											<th width="<?php echo $column_width ?>" style="background-color:#F5F5F5;height:32px;" ><?php echo lang('common_discount_percent'); ?></th>
											<th width="<?php echo $column_width ?>" style="background-color:#F5F5F5;height:32px;" ><?php echo lang('common_total'); ?></th>
										</tr>
										
										<?php
										$number_of_items_sold = 0;
										$number_of_items_returned = 0;
											foreach(array_reverse($cart_items, true) as $line=>$item)
											{
							 				 if ($item->quantity > 0 && $item->name != lang('common_store_account_payment') && $item->name != lang('common_discount') && $item->name != lang('common_refund') && $item->name != lang('common_fee'))
							 				 {
							 			 		 $number_of_items_sold = $number_of_items_sold + $item->quantity;
							 				 }
							 				 elseif ($item->quantity < 0 && $item->name != lang('common_store_account_payment') && $item->name != lang('common_discount') && $item->name != lang('common_refund') && $item->name != lang('common_fee'))
							 				 {
							 			 		 $number_of_items_returned = $number_of_items_returned + abs($item->quantity);
							 				 }
												
												?>
												
												<?php
												$item_number_for_receipt = false;
					
												if ($this->config->item('show_item_id_on_recv_receipt'))
												{
													switch($this->config->item('id_to_show_on_sale_interface'))
													{
														case 'number':
														$item_number_for_receipt = property_exists($item,'item_number') ? H($item->item_number) : '';
														break;
						
														case 'product_id':
														$item_number_for_receipt = property_exists($item,'product_id') ? H($item->product_id) : ''; 
														break;
						
														case 'id':
														$item_number_for_receipt = property_exists($item,'item_id') ? H($item->item_id) : ''; 
														break;
						
														default:
														$item_number_for_receipt = property_exists($item,'item_number') ? H($item->item_number) : '';
														break;
													}
												}
												?>
												

												<tr class="text-center item-row">
													<td style="padding-right:0;padding-top:10px !important;padding-left:10px;border-width:1px;border-style:solid;border-color:#DCE0E6;border-bottom-width:0px;border-right-width:0px;padding-bottom:10px;" >
														<?php echo $item->name.($item->variation_name ? '- '.H($item->variation_name) : '' ); ?><?php if ($item_number_for_receipt){ ?> - <?php echo $item_number_for_receipt; ?><?php } ?><?php if (!$this->config->item('hide_desc_emailed_receipts') && $item->description){ ?> - <?php echo clean_html($item->description); ?><?php } ?>
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
													
													<?php
													if (!$this->config->item('hide_size_field'))
													{
													?>
													<td align="center" style="padding-right:0;padding-top:10px !important;padding-left:10px;border-width:1px;border-style:solid;border-color:#DCE0E6;border-bottom-width:0px;border-right-width:0px;padding-bottom:10px;" >
														<?php echo $item->size; ?>
													</td>
													<?php
													}
													?>
													<td align="center" style="padding-right:0;padding-top:10px !important;padding-left:10px;border-width:1px;border-style:solid;border-color:#DCE0E6;border-bottom-width:0px;border-right-width:0px;padding-bottom:10px;" >
														<?php echo to_currency($item->unit_price,10); ?>
													</td>
													
													<?php
													if ($this->config->item('show_selling_price_on_recv'))
													{
													?>
														<td align="center" style="padding-right:0;padding-top:10px !important;padding-left:10px;border-width:1px;border-style:solid;border-color:#DCE0E6;border-bottom-width:0px;border-right-width:0px;padding-bottom:10px;" >
															<?php echo to_currency($item->selling_price,10); ?>
														</td>
													<?php
													}
													?>
													
													<td  align="center" style="padding-right:0;padding-top:10px !important;padding-left:10px;border-width:1px;border-style:solid;border-color:#DCE0E6;border-bottom-width:0px;border-right-width:0px;padding-bottom:10px;" >
														<?php echo to_quantity($item->quantity);?>
													</td>
													<td  align="center" style="padding-right:0;padding-top:10px !important;padding-left:10px;border-width:1px;border-style:solid;border-color:#DCE0E6;border-bottom-width:0px;border-right-width:0px;padding-bottom:10px;" >
														<?php echo to_quantity($item->discount); ?>
													</td>
										
													<td  align="center" style="padding-right:0;padding-top:10px !important;padding-left:10px;border-width:1px;border-style:solid;border-color:#DCE0E6;border-bottom-width:0px;border-right-width:0px;padding-bottom:10px;" >
														<?php echo to_currency($item->unit_price*$item->quantity-$item->unit_price*$item->quantity*$item->discount/100,10); ?>
													</td>
												</tr>
												
												<?php
												foreach($item_custom_fields_to_display as $custom_field_id)
												{
												?>
														<?php 
														if(get_class($item) == 'PHPPOSCartItemRecv' && $this->Item->get_custom_field($custom_field_id) !== false)
														{
															$item_info = $this->Item->get_info($item->item_id);
													
															if ($item_info->{"custom_field_${custom_field_id}_value"})
															{
															?>
															<tr class="text-center item-row"><td colspan="1000">
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
												?>
												<?php
												}
												?>
												
										<?php } ?>

										<?php
										foreach($receiving_custom_fields_to_display as $custom_field_id)
										{
											if($this->Receiving->get_custom_field($custom_field_id) !== false && $this->Receiving->get_custom_field($custom_field_id) !== false)
											{											
													if ($cart->{"custom_field_${custom_field_id}_value"})
													{
													?>						
													<?php

													if ($this->Receiving->get_custom_field($custom_field_id,'type') == 'checkbox')
													{
														$format_function = 'boolean_as_string';
													}
													elseif($this->Receiving->get_custom_field($custom_field_id,'type') == 'date')
													{
														$format_function = 'date_as_display_date';				
													}
													elseif($this->Receiving->get_custom_field($custom_field_id,'type') == 'email')
													{
														$format_function = 'strsame';					
													}
													elseif($this->Receiving->get_custom_field($custom_field_id,'type') == 'url')
													{
														$format_function = 'strsame';					
													}
													elseif($this->Receiving->get_custom_field($custom_field_id,'type') == 'phone')
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
															<?php echo $this->Receiving->get_custom_field($custom_field_id,'name'); ?><br />
															<?php echo $format_function($cart->{"custom_field_${custom_field_id}_value"}); ?>
					
														</td>
													</tr>
													
													<?php
												}
											}
										}
										?>
										<tr class="text-center item-row">
											<td colspan="<?php echo $total_columns-1; ?>" class=" padding-right" align="right" style="padding-right:10px;padding-top:10px !important;padding-left:10px;border-width:1px;border-style:solid;border-color:#DCE0E6;border-bottom-width:0px;border-right-width:0px;padding-bottom:10px;" >
												<?php echo lang('common_sub_total'); ?>
											</td>
											<td  align="center" style="padding-right:0;padding-top:10px !important;padding-left:10px;border-width:1px;border-style:solid;border-color:#DCE0E6;border-bottom-width:0px;border-right-width:0px;padding-bottom:10px;" >
												<?php echo to_currency($subtotal); ?>
											</td>
										</tr>
									
										<?php foreach($taxes as $name=>$value) { ?>
											<tr class="text-center item-row">
												<td colspan="<?php echo $total_columns-1; ?>" class=" padding-right" align="right" style="padding-right:10px;padding-top:10px !important;padding-left:10px;border-width:1px;border-style:solid;border-color:#DCE0E6;border-bottom-width:0px;border-right-width:0px;padding-bottom:10px;" >
													<?php echo $name; ?>:
												</td>
												<td  align="center" style="padding-right:0;padding-top:10px !important;padding-left:10px;border-width:1px;border-style:solid;border-color:#DCE0E6;border-bottom-width:0px;border-right-width:0px;padding-bottom:10px;" >
													<?php echo to_currency($value); ?>
												</td>
											</tr>
										<?php }; ?>


										<tr class="text-center item-row">
											<td colspan="<?php echo $total_columns-1; ?>" class=" padding-right" align="right" style="padding-right:10px;padding-top:10px !important;padding-left:10px;border-width:1px;border-style:solid;border-color:#DCE0E6;border-bottom-width:0px;border-right-width:0px;padding-bottom:10px;" >
												<b><?php echo lang('common_total'); ?></b>
											</td>
											<td  align="center" style="padding-right:0;padding-top:10px !important;padding-left:10px;border-width:1px;border-style:solid;border-color:#DCE0E6;border-bottom-width:0px;border-right-width:0px;padding-bottom:10px;" >
												<b> <?php echo to_currency($total); ?></b>
											</td>
										</tr>

									  	<tr><td colspan="<?php echo $total_columns; ?>">&nbsp;</td></tr>

									    <?php
											 
											foreach($payments as $payment_id=>$payment) { 
												
												if ($payment->payment_type == lang('common_store_account'))
												{
													$amount_due=$payment->payment_amount;
												}
												
												?>
											<tr class="text-center item-row">
												<td colspan="<?php echo $total_columns-2; ?>" class=" padding-right" align="right" style="padding-right:10px;padding-top:10px !important;padding-left:10px;border-width:1px;border-style:solid;border-color:#DCE0E6;border-bottom-width:0px;border-right-width:0px;padding-bottom:10px;" >
													<?php echo (isset($show_payment_times) && $show_payment_times) ?  date(get_date_format().' '.get_time_format(), strtotime($payment->payment_date)) : lang('common_payment'); ?>
												</td>

												<td  align="center" style="padding-right:0;padding-top:10px !important;padding-left:10px;border-width:1px;border-style:solid;border-color:#DCE0E6;border-bottom-width:0px;border-right-width:0px;padding-bottom:10px;" ><?php $splitpayment=explode(':',$payment->payment_type); echo $splitpayment[0]; ?> </td>											 


												<td  align="center" style="padding-right:0;padding-top:10px !important;padding-left:10px;border-width:1px;border-style:solid;border-color:#DCE0E6;border-bottom-width:0px;border-right-width:0px;padding-bottom:10px;" >
													<?php echo to_currency($payment->payment_amount); ?>
												</td>
											</tr>
										<?php } ?>
										
										<?php if (isset($supplier_balance_for_sale) && (double)$supplier_balance_for_sale && !$this->config->item('hide_store_account_balance_on_receipt')) {?>
											
											
											<td colspan="<?php echo $total_columns-1; ?>" class=" padding-right" align="right" style="padding-right:10px;padding-top:10px !important;padding-left:10px;border-width:1px;border-style:solid;border-color:#DCE0E6;border-bottom-width:0px;border-right-width:0px;padding-bottom:10px;" >
												<b><?php echo lang('receivings_supplier_account_balance'); ?></b>
											</td>
											<td  align="center" style="padding-right:0;padding-top:10px !important;padding-left:10px;border-width:1px;border-style:solid;border-color:#DCE0E6;border-bottom-width:0px;border-right-width:0px;padding-bottom:10px;" >
												<b><?php echo to_currency($supplier_balance_for_sale); ?></b>
											</td>											
										<?php
										}
										?>
										
										<?php
										if ($this->config->item('paypal_me')) 
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
										
										
										
									</table>
								</td>
							</tr>
						</table>
					</td>
				</tr>
				
				<?php if ($number_of_items_sold) { ?>
					
					<tr>
						<td class="one-column" style="padding-top:0;padding-bottom:0;padding-right:0;padding-left:0;" >
							<table width="100%" style="border-spacing:0;font-family:'Helvetica Neue', Helvetica, Arial, sans-serif;color:#555555;font-size:13px;" >
								<tr>
									<td class="inner contents" style="padding-top:10px;padding-bottom:10px;padding-right:10px;padding-left:10px;width:100%;text-align:left;" >
										<p style="Margin:0;font-size:13px;Margin-bottom:10px;" >
											<?php 
											echo lang('common_items_purchased').": ". to_quantity($number_of_items_sold); 
											?>
										</p>
									</td>
								</tr>
							</table>
						</td>
					</tr>
					
				<?php } ?>
			
				<?php if ($number_of_items_returned) { ?>
				
				<tr>
					<td class="one-column" style="padding-top:0;padding-bottom:0;padding-right:0;padding-left:0;" >
						<table width="100%" style="border-spacing:0;font-family:'Helvetica Neue', Helvetica, Arial, sans-serif;color:#555555;font-size:13px;" >
							<tr>
								<td class="inner contents" style="padding-top:10px;padding-bottom:10px;padding-right:10px;padding-left:10px;width:100%;text-align:left;" >
									<p style="Margin:0;font-size:13px;Margin-bottom:10px;" >
										<?php 
										echo lang('common_items_returned').": ". to_quantity($number_of_items_returned); 
										?>
									</p>
								</td>
							</tr>
						</table>
					</td>
				</tr>
				
				<?php } ?>
				
				<tr>
					<td class="one-column" style="padding-top:0;padding-bottom:0;padding-right:0;padding-left:0;" >
						<table width="100%" style="border-spacing:0;font-family:'Helvetica Neue', Helvetica, Arial, sans-serif;color:#555555;font-size:13px;" >
							<tr>
								<td class="inner contents" style="padding-top:10px;padding-bottom:10px;padding-right:10px;padding-left:10px;width:100%;text-align:left;" >
									<p style="Margin:0;font-size:13px;Margin-bottom:10px;" >
										<?php 
										echo lang('common_comments').": ". H($comment); 
										?>
									</p>
								</td>
							</tr>
						</table>
					</td>
				</tr>
				
				</table>
			</td>
		</tr>
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