<?php $this->load->view("partial/header"); ?>
<?php
	$this->load->helper('sale');
	
	$work_order_custom_fields_to_display  = array();
	
    for($k=1;$k<=NUMBER_OF_PEOPLE_CUSTOM_FIELDS;$k++) 
    {
   	 $work_order_custom_field = $this->Work_order->get_custom_field($k,'show_on_receipt');
	 	 
   	 if ($work_order_custom_field)
   	 {
   	 	$work_order_custom_fields_to_display[] = $k;
   	 }
    }
	
?>

<div class="manage_buttons hidden-print">
	<div class="row">
		<div class="col-md-12">	
			<div class="buttons-list">
				<div class="pull-right-btn">
					<ul class="list-inline">
						<li>
							<button class="btn btn-primary btn-lg hidden-print" id="print_button" onclick="print_work_order()" > <?php echo lang('common_print'); ?> </button>		
						</li>
					</ul>
				</div>
			</div>				
		</div>
	</div>
</div>

<div class="row manage-table receipt_<?php echo $this->config->item('receipt_text_size') ? $this->config->item('receipt_text_size') : 'small';?>" id="receipt_wrapper">
	
	<?php
		foreach ($datas as $key => $data){
			$company = ($company = $this->Location->get_info_for_key('company', isset($data['override_location_id']) ? $data['override_location_id'] : FALSE)) ? $company : $this->config->item('company');
			$website = ($website = $this->Location->get_info_for_key('website', isset($data['override_location_id']) ? $data['override_location_id'] : FALSE)) ? $website : $this->config->item('website');
			$company_logo = ($company_logo = $this->Location->get_info_for_key('company_logo', isset($data['override_location_id']) ? $data['override_location_id'] : FALSE)) ? $company_logo : $this->config->item('company_logo');	
			
			$page_break_after = ($key == count($datas) -1) ? 'auto' : 'always';
	?>
		<div class="col-md-12" id="receipt_wrapper_inner" style = "page-break-after:<?php echo  $page_break_after;?>">
			<div class="panel panel-piluku">
				<div class="panel-body panel-pad">
					<div class="row">
						<!-- from address-->
						<div class="col-md-4 col-sm-4 col-xs-12">
							<ul class="list-unstyled invoice-address" style="margin-bottom:2px;">
								<?php if($company_logo) {?>
									<li class="invoice-logo">
										<?php echo img(array('src' => $this->Appfile->get_url_for_file($company_logo))); ?>
									</li>
								<?php } ?>
								<li class="company-title"><?php echo H($company); ?></li>
								
								<?php if ($this->Location->count_all() > 1) { ?>
									<li><?php echo H($this->Location->get_info_for_key('name', isset($data['override_location_id']) ? $data['override_location_id'] : FALSE)); ?></li>
								<?php } ?>
								
								<li><?php echo nl2br(H($this->Location->get_info_for_key('address', isset($data['override_location_id']) ? $data['override_location_id'] : FALSE))); ?></li>
								<li><?php echo H($this->Location->get_info_for_key('phone', isset($data['override_location_id']) ? $data['override_location_id'] : FALSE)); ?></li>
								<?php if($website) { ?>
												<li><?php echo H($website);?></li>
												<?php } ?>
							</ul>
						</div>
						<!--  sales-->
						<div class="col-md-4 col-sm-4 col-xs-12">
							<ul class="list-unstyled invoice-detail" style="margin-bottom:2px;">
								<li>
									<strong><?php echo H($data['transaction_time']) ?></strong>
								</li>
								<li><span><?php echo lang('common_workorder').":"; ?></span><?php echo H(rawurldecode($data['sale_id'])); ?></li>
								<li><?php echo $sale_type; ?></li>

								<?php
								if ($this->Register->count_all(isset($data['override_location_id']) ? $data['override_location_id'] : FALSE) > 1 && $data['register_name'])
								{
								?>
									<li><span><?php echo lang('common_register_name').':'; ?></span><?php echo H($data['register_name']); ?></li>		
								<?php
								}
								?>				
								
								<?php
								if ($data['tier'])
								{
								?>
									<li><span><?php echo $this->config->item('override_tier_name') ? $this->config->item('override_tier_name') : lang('common_tier_name').':'; ?></span><?php echo H($data['tier']); ?></li>		
								<?php
								}
								?>

								<li><span><?php echo lang('common_employee').":"; ?></span><?php echo H($data['employee']); ?></li>
								
							</ul>
						</div>
								
						<!-- to address-->
						<div class="col-md-4 col-sm-4 col-xs-12">
						<?php if($data['customer'] != 'no_customer!') { ?>
							<ul class="list-unstyled invoice-address invoiceto" style="margin-bottom:2px;">
										<?php if (!$this->config->item('remove_customer_name_from_receipt')) { ?>
											<li><?php echo lang('common_customer').": ".H($data['customer']); ?></li>
										<?php } ?>
										<?php if (!$this->config->item('remove_customer_company_from_receipt')) { ?>
											<?php if(!empty($data['customer_company'])) { ?><li><?php echo lang('common_company').": ".H($data['customer_company']); ?></li><?php } ?>
										<?php } ?>

										<?php if (!$this->config->item('remove_customer_contact_info_from_receipt')) { ?>
											<?php if(!empty($data['customer_address_1'])){ ?><li><?php echo lang('common_address'); ?> : <?php echo H($data['customer_address_1']. ' '.$data['customer_address_2']); ?></li><?php } ?>
											<?php if (!empty($data['customer_city'])) { echo '<li>'.H($data['customer_city'].' '.$data['customer_state'].', '.$data['customer_zip']).'</li>';} ?>
											<?php if (!empty($data['customer_country'])) { echo '<li>'.H($data['customer_country']).'</li>';} ?>			
											<?php if(!empty($data['customer_phone'])){ ?><li><?php echo lang('common_phone_number'); ?> : <?php echo H($data['customer_phone']); ?></li><?php } ?>
											<?php if(!empty($data['customer_email'])){ ?><li><?php echo lang('common_email'); ?> : <?php echo H($data['customer_email']); ?></li><?php } ?>
										<?php } ?>
							</ul>
									
							<?php } ?>
						</div>
								
						<!-- delivery address-->
						<div class="col-md-4 col-sm-4 col-xs-12">
							
						<?php if(isset($data['delivery_person_info'])) { ?>
							<ul class="list-unstyled invoice-address" style="margin-bottom:10px;">
									
									
										<li class="invoice-to"><?php echo lang('work_orders_shipping_address');?>:</li>
										<li><?php echo lang('common_name').": ".H($data['delivery_person_info']['first_name'].' '.$data['delivery_person_info']['last_name']); ?></li>
										
										<?php if(!empty($data['delivery_person_info']['address_1']) || !empty($data['delivery_person_info']['address_2'])){ ?><li><?php echo lang('common_address'); ?> : <?php echo H($data['delivery_person_info']['address_1']. ' '.$data['delivery_person_info']['address_2']); ?></li><?php } ?>
										<?php if (!empty($data['delivery_person_info']['city'])) { echo '<li>'.H($data['delivery_person_info']['city'].' '.$data['delivery_person_info']['state'].', '.$data['delivery_person_info']['zip']).'</li>';} ?>
										<?php if (!empty($data['delivery_person_info']['country'])) { echo '<li>'.H($data['delivery_person_info']['country']).'</li>';} ?>			
										<?php if(!empty($data['delivery_person_info']['phone'])){ ?><li><?php echo lang('common_phone_number'); ?> : <?php echo H($data['delivery_person_info']['phone']); ?></li><?php } ?>
										<?php if(!empty($data['delivery_person_info']['email'])){ ?><li><?php echo lang('common_email'); ?> : <?php echo H($data['delivery_person_info']['email']); ?></li><?php } ?>
							</ul>
									<?php } ?>
						</div>
								
					</div>
					<!-- invoice heading-->
					<?php 
						$x_col = 6;
						$xs_col = 4;
						if($data['discount_exists'])
						{
							$x_col = 4;
							$xs_col = 3;
						}
					?>
					<br /><br /><br />
					<div class="invoice-table">
						<div class="row">
							<div class="col-md-6 col-sm-6 col-xs-6">
								<div class="invoice-head item-name"><strong><?php echo lang('common_item_being_repaired'); ?></strong></div>
							</div>
										
							<div class="col-md-6 col-sm-6 col-xs-6">
								<div class="invoice-head text-left item-notes"><strong><?php echo lang('common_notes'); ?></strong></div>
							</div>
						</div>
					</div>				
					<!-- Items -->
					<?php if (count($data['sales_items']) > 0) { ?>
						
					<!-- Items table -->
					<?php
					}
					$current_category = FALSE;

					foreach($data['sales_items'] as $item)
					{
						$item_number_for_receipt = false;
					
						if ($this->config->item('show_item_id_on_receipt'))
						{
							switch($this->config->item('id_to_show_on_sale_interface'))
							{
								case 'number':
								$item_number_for_receipt = H($item['item_number']);
								break;
							
								case 'product_id':
								$item_number_for_receipt = H($item['product_id']);
								break;
							
								case 'id':
								$item_number_for_receipt = H($item['item_id']); 
								break;
							
								default:
								$item_number_for_receipt = H($item['item_number']);
								break;
							}
						}
					?>
					<!-- invoice items-->
					<div class="invoice-table-content">
						<div class="row">
							
						<div class="col-md-6 col-sm-6 col-xs-6">
							<div class="invoice-content invoice-con">
								<div class="invoice-content-heading"><?php echo H($item['name']); ?><?php if ($item_number_for_receipt){ ?> - <?php echo $item_number_for_receipt; ?><?php } ?><?php if ($item['size']){ ?> (<?php echo H($item['size']); ?>)<?php } ?></div>
								
								<div class="invoice-desc">
									<?php
										if (isset($item['item_variation_id']))
										{
											$this->load->model('Item_variations');
											echo H($this->Item_variations->get_variation_name($item['item_variation_id']));
										}
									?>
								</div>
								
								<?php if (!$this->config->item('hide_desc_on_receipt') && isset($item['description']) && !$item['description']=="" ) {?>
									<div class="invoice-desc"><?php echo clean_html($item['description']); ?></div>
								<?php } ?>

								<?php if(isset($item['serialnumber']) && $item['serialnumber'] !=""){ ?>
									<div class="invoice-desc"><?php echo H($item['serialnumber']); ?></div>
								<?php } ?>
								
			
							</div>
						</div>					
						
						<div class="col-md-6 col-sm-6 col-xs-6">
								<div class="invoice-content item-notes text-left text-transform-none">
									<?php 
										$sales_items_notes = $this->Sale->get_sales_items_notes_info($data['sale_id_raw'],$item['item_id'],$item['line']);
										foreach($sales_items_notes as $sales_items_note){
											if(!$sales_items_note['internal']){
												echo date(get_date_format().' '.get_time_format(), strtotime($sales_items_note['note_timestamp'])).': '.H($sales_items_note['note']).'<br />'; 
												echo H($sales_items_note['detailed_notes']).'<br />'; 
											}
										}
									?>
												
								</div>
							</div>			
						</div>
					</div>
									
					<?php } ?>





					
					<?php
					$work_order_info = $data['work_order_info'];
					foreach($work_order_custom_fields_to_display as $custom_field_id)
					{
						if($this->Work_order->get_custom_field($custom_field_id) !== false && $this->Work_order->get_custom_field($custom_field_id) !== false)
						{											
								if ($work_order_info->{"custom_field_${custom_field_id}_value"})
								{
								?>						
								<?php

								if ($this->Work_order->get_custom_field($custom_field_id,'type') == 'checkbox')
								{
									$format_function = 'boolean_as_string';
								}
								elseif($this->Work_order->get_custom_field($custom_field_id,'type') == 'date')
								{
									$format_function = 'date_as_display_date';				
								}
								elseif($this->Work_order->get_custom_field($custom_field_id,'type') == 'email')
								{
									$format_function = 'strsame';					
								}
								elseif($this->Work_order->get_custom_field($custom_field_id,'type') == 'url')
								{
									$format_function = 'strsame';					
								}
								elseif($this->Work_order->get_custom_field($custom_field_id,'type') == 'phone')
								{
									$format_function = 'strsame';					
								}
								elseif($this->Work_order->get_custom_field($custom_field_id,'type') == 'image')
								{
									$this->load->helper('url');
									$format_function = 'file_id_to_image_thumb_right';					
								}
								elseif($this->Work_order->get_custom_field($custom_field_id,'type') == 'file')
								{
									$this->load->helper('url');
									$format_function = 'file_id_to_download_link';					
								}
								else
								{
									$format_function = 'strsame';
								}
								?>
								<div class="invoice-table-content">
								<div class="row">
									<div class="col-md-6 col-sm-6 col-xs-6">
			       			 	<div class="invoice-content invoice-con">
			         			 <div class="invoice-content-heading"><?php
											 if (!$this->Work_order->get_custom_field($custom_field_id,'hide_field_label'))
											 {
												 echo $this->Work_order->get_custom_field($custom_field_id,'name');
											 }
											 else
											 {
											 	echo $format_function($work_order_info->{"custom_field_${custom_field_id}_value"});
											 }
										 
			         			 ?></div>
												<div class="invoice-desc"><?php 
													if (!$this->Work_order->get_custom_field($custom_field_id,'hide_field_label'))
													{
														echo $format_function($work_order_info->{"custom_field_${custom_field_id}_value"}); 
													}	
													?>
													</div>
											</div>
			       			 </div>
							</div>
						</div>
								<?php
							}
						}
					?>
					<?php
					}
					?>


					<?php
					if($work_order_info->estimated_labor)
					{
					?>
					
					<div class="row">
						<div class="col-md-4 col-sm-12 col-xs-12">
							<?php
							echo lang('work_orders_estimated_labor').': <strong>'.to_currency($work_order_info->estimated_labor).'</strong>';
							?>
						</div>
					</div>
					<?php
					}
					?>
					
					
					<?php
					if($work_order_info->estimated_parts)
					{
					?>
					
					<div class="row">
						<div class="col-md-4 col-sm-12 col-xs-12">
							<?php
							echo lang('work_orders_estimated_parts').': <strong>'.to_currency($work_order_info->estimated_parts).'</strong>';
							?>
						</div>
					</div>
					<?php
					}
					?>
					

					<!-- invoice footer-->
					<div class="row">
						<div class="col-md-4 col-sm-12 col-xs-12">
							<?php if($data['show_comment_on_receipt']==1)
								{
									echo H($data['comment']) ;
								}
							?>
						</div>
						<div class="<?php echo $data['show_comment_on_receipt']==1 ? 'col-md-4 col-sm-12 col-xs-12' : 'col-md-12 col-sm-12 col-xs-12'; ?>">
							<?php if (!$this->config->item('hide_barcode_on_sales_and_recv_receipt')) {?>
											<div id='barcode' class="invoice-policy">
											<?php 
												$rawurlencode_sale_id = rawurlencode($data['sale_id']);
												echo "<img src='".site_url('barcode/index/svg')."?barcode=".$rawurlencode_sale_id."&text=".$rawurlencode_sale_id."' />"; 
											?>
										</div>
										<?php } ?>
						</div>
					</div>
				</div>
				<!--container-->
			</div>		
		</div>
	<?php } ?>
</div>

<?php $this->load->view("partial/footer"); ?>

<script type="text/javascript">
function print_work_order()
 {
 	window.print();
 }
 </script>
