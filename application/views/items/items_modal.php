<div class="modal-dialog customer-recent-sales">
	<div class="modal-content">
		<div class="modal-header" id="myTabHeader">
			<button type="button" class="close" data-dismiss="modal" aria-label=<?php echo json_encode(lang('common_close')); ?>><span aria-hidden="true" class="ti-close"></span></button>
			<div class="modal-item-details">
					<h4 class="modal-title"><?php echo H($item_info->name).' ['.lang('common_id').': '.$item_info->item_id.']'; ?></h4>
			</div>
			<nav>
        <ul id="myTab" class="nav nav-tabs nav-justified">
					<li class="active"><a href="#ItemInfo" data-toggle="tab"><?php echo lang('common_item_info'); ?></a></li>
          <li class=""><a href="#Pricing" data-toggle="tab"><?php echo lang('common_pricing'); ?></a></li>
					<li class=""><a href="#Inventory" data-toggle="tab"><?php echo lang('common_inventory'); ?></a></li>
					<li class=""><a href="#Images" data-toggle="tab"><?php echo lang('common_images'); ?></a></li>
        </ul>
			</nav>
		</div>
		
		<div class="modal-body" id="myTabModalBody">
						
			<div class="tab-content">
				<div class="tab-pane active" id="ItemInfo">
					<div class="panel panel-piluku">
						<div class="panel-heading">
							<div class="panel-title">
								<h3><span class="ion-information-circled"></span> <?php echo lang('common_item_information'); ?>
								<div class="panel-options custom">
			 						<?php if ($this->Employee->has_module_action_permission('items','add_update', $this->Employee->get_logged_in_employee_info()->person_id) or $item_info->name=="")	{ ?>
										<a href="<?php echo site_url("items/view/".$item_info->item_id."?redirect=".$redirect)?>" class="btn btn-default pull-right"><?php echo lang("common_edit") ?></a>
									<?php } ?>
								</div>
								</h3> 
							</div>
						</div>
						
						<table class="table table-bordered table-hover table-striped">
							<tr><td width="40%"><?php echo lang('common_category'); ?></td> <td><?php echo H($category); ?></td></tr>
							<?php if($item_info->description) { ?><tr><td width="40%"><?php echo lang('common_description'); ?></td> <td> <?php echo clean_html($item_info->description); ?></td></tr><?php } ?>
							<tr><td width="40%"><?php echo lang('common_item_id'); ?></td> <td><?php echo H($item_info->item_id); ?></td></tr>
							<?php if($item_info->product_id) { ?><tr><td><?php echo lang('common_product_id'); ?></td> <td><?php echo H($item_info->product_id); ?></td></tr><?php } ?>
							<?php if($item_info->item_number) { ?><tr><td><?php echo lang('common_item_number_expanded'); ?></td> <td><?php echo H($item_info->item_number); ?></td></tr><?php } ?>
							<?php if (isset($additional_item_numbers) && $additional_item_numbers->num_rows() > 0) {?>
								<tr> <td colspan="2"><strong><?php echo lang('common_additional_item_numbers'); ?></strong></td></tr>
								<?php foreach($additional_item_numbers->result() as $additional_item_number) { ?>
									<tr><td colspan="2"><?php echo H($additional_item_number->item_number); ?></td></tr>
								<?php } ?>
							<?php } ?>
							<?php if($manufacturer) { ?><tr> <td><?php echo lang('common_manufacturer'); ?></td> <td> <?php echo H($manufacturer); ?></td></tr><?php } ?>
							<?php if($item_info->size) { ?><tr> <td><?php echo lang('common_size'); ?></td> <td> <?php echo H($item_info->size); ?></td></tr><?php } ?>
							<?php if(isset($supplier)) { ?><tr><td><?php echo lang('common_supplier'); ?></td><td><?php echo $supplier; ?></td></tr><?php } ?>
							<?php if($item_location_info->location) { ?><tr><td><?php echo lang('common_location'); ?></td> <td> <?php echo $item_location_info->location; ?></td></tr><?php } ?>
							<tr> <td><?php echo lang('items_allow_alt_desciption'); ?></td> <td> <?php echo $item_info->allow_alt_description ? lang('common_yes') : lang('common_no'); ?></td></tr>
							<tr> <td><?php echo lang('items_is_serialized'); ?></td> <td> <?php echo $item_info->is_serialized ? lang('common_yes') : lang('common_no'); ?></td></tr>
							<?php if($this->config->item("ecommerce_platform")) { ?>
							<tr> <td><?php echo lang('items_is_ecommerce'); ?></td> <td> <?php echo $item_info->is_ecommerce ? lang('common_yes') : lang('common_no'); ?></td></tr>
							<?php } ?>		
							<tr> <td><?php echo lang('common_generate_barcodes'); ?></td> <td><?php echo anchor('items/barcodes/'.$item_info->item_id,lang('common_print'))?></td></tr>
							
							<?php
						  for($k=1;$k<=NUMBER_OF_PEOPLE_CUSTOM_FIELDS;$k++) 
						  {
						 	 $item_custom_field_name = $this->Item->get_custom_field($k);
							 
							 if ($item_custom_field_name)
							 {
							 	 $item_custom_field_value = $item_info->{"custom_field_${k}_value"};
								 
								if ($this->Item->get_custom_field($k,'type') == 'checkbox')
								{
									$format_function = 'boolean_as_string';
								}
								elseif($this->Item->get_custom_field($k,'type') == 'date')
								{
									$format_function = 'date_as_display_date';				
								}
								elseif($this->Item->get_custom_field($k,'type') == 'email')
								{
									$format_function = 'strsame';					
								}
								elseif($this->Item->get_custom_field($k,'type') == 'url')
								{
									$format_function = 'strsame';					
								}
								elseif($this->Item->get_custom_field($k,'type') == 'phone')
								{
									$format_function = 'strsame';					
								}
								elseif($this->Item->get_custom_field($k,'type') == 'image')
								{
									$this->load->helper('url');
									$format_function = 'file_id_to_image_thumb_right';					
								}
								elseif($this->Item->get_custom_field($k,'type') == 'file')
								{
									$this->load->helper('url');
									$format_function = 'file_id_to_download_link';					
								}
								else
								{
									$format_function = 'strsame';
								}
								?>
	 								<tr><td><?php echo $item_custom_field_name; ?></td> <td><?php echo $format_function($item_custom_field_value);?></td></tr>
							 <?php	
							 }
								
						 }
							?>
						</table>
						
					</div>
					<?php if($item_variations) { ?>
					<div class="panel panel-piluku">
						<div class="panel-heading">
							<div class="panel-title">
								<h3><span class="ion-ios-toggle-outline"></span> <?php echo lang('common_item_variations'); ?>
								<div class="panel-options custom">
			 						<?php if ($this->Employee->has_module_action_permission('items','add_update', $this->Employee->get_logged_in_employee_info()->person_id) or $item_info->name=="")	{ ?>
										<a href="<?php echo site_url("items/variations/".$item_info->item_id."?redirect=".$redirect)?>" class="btn btn-default pull-right"><?php echo lang("common_edit") ?></a>
									<?php } ?>
								</div>
								</h3> 
							</div>
						</div>
						<table class="table table-bordered table-hover table-striped">
							<tr><th><?php echo lang("common_name"); ?></th><th><?php echo lang("common_attributes"); ?></th></tr>
							<?php foreach($item_variations as $item_variation) { ?>
							<tr><td width="40%"><?php echo $item_variation['name']; ?></td><td><?php echo implode(', ',array_column($item_variation['attributes'],'label')); ?></td></tr>
							<?php } ?>
						</table>
						
					</div>
					<?php } ?>
				</div>
				<div class="tab-pane" id="Pricing">
					<div class="panel panel-piluku">
						<div class="panel-heading">
							<div class="panel-title">
								<h3><span class="ion-cash"></span> <?php echo lang('common_pricing'); ?>
								<div class="panel-options custom">
		 						<?php if ($this->Employee->has_module_action_permission('items','add_update', $this->Employee->get_logged_in_employee_info()->person_id) or $item_info->name=="")	{ ?>
									<a href="<?php echo site_url("items/pricing/".$item_info->item_id."?redirect=".$redirect)?>" class="btn btn-default pull-right"><?php echo lang("common_edit") ?></a>
								<?php } ?>
								</div>
								</h3> 
							</div>
						</div>
						
	 				 <table class="table table-bordered table-hover table-striped">
	 						
	 						<tr><td width="25%"><?php echo lang('common_unit_price'); ?></td><td colspan='5'><strong><?php echo to_currency($item_info->unit_price, 10); ?></strong></td></tr>
	 						<?php if ($this->Employee->has_module_action_permission('items','see_cost_price', $this->Employee->get_logged_in_employee_info()->person_id) or $item_info->name=="")	{ ?>
	 						<tr><td><?php echo lang('common_cost_price'); ?></td><td colspan='5'><span id='cost_price_value' style="display: none;"><?php echo to_currency($item_info->cost_price, 10); ?></span> <a id="cost_price_expand_collapse" href="javascript:void(0);">+</a></td></tr>
	 						<?php } ?>
	 						<?php foreach($tier_prices as $tier_price) { ?>
	 							<tr><td><?php echo H($tier_price['name']) ?></td><td colspan='5'><?php echo $tier_price['value']; ?></td></tr>
	 						<?php } ?>
	 						<?php if($item_info->promo_price) { ?><tr><td><?php echo lang('items_promo_price'); ?></td><td colspan='5'><?php echo to_currency($item_info->promo_price, 10); ?></td></tr><?php } ?>
	 						<?php if($item_info->start_date) { ?><tr><td><?php echo lang('common_start_date'); ?></td><td colspan='5'><?php echo $item_info->start_date; ?></td></tr><?php } ?>
	 						<?php if($item_info->end_date) { ?><tr><td><?php echo lang('common_end_date'); ?></td><td colspan='5'><?php echo $item_info->end_date; ?></td></tr><?php } ?>
							
							
	 						<?php if(count($item_variations) > 0) { ?>
	 							<tr>
	 								<th width="25%" class="text-center"><span class="item_information_heading"><?php echo lang('common_variation'); ?></span></th>
	 								<th width="15%" class="text-center"><span class="item_information_heading"><?php echo lang('common_unit_price'); ?></span></th>
			 						<?php if ($this->Employee->has_module_action_permission('items','see_cost_price', $this->Employee->get_logged_in_employee_info()->person_id) or $item_info->name=="")	{ ?>
	 									<th width="15%" class="text-center"><span class="item_information_heading"><?php echo lang('common_cost_price'); ?></span></th>
	 								<?php } ?>
									<th width="15%" class="text-center"><span class="item_information_heading"><?php echo lang('common_promo_price'); ?></span></th>
	 								<th width="15%" class="text-center"><span class="item_information_heading"><?php echo lang('common_start_date'); ?></span></th>
	 								<th width="15%" class="text-center"><span class="item_information_heading"><?php echo lang('common_end_date'); ?></span></th>				
	 							</tr>
	 							<?php foreach($item_variations as $item_variation) { ?>
	 							<tr>
	 								<td width="25%"><?php echo $item_variation['name']; ?></td>
	 								<td width="15%"><?php echo to_currency($item_variation['unit_price']); ?></td>
			 						<?php if ($this->Employee->has_module_action_permission('items','see_cost_price', $this->Employee->get_logged_in_employee_info()->person_id) or $item_info->name=="")	{ ?>
	 									<td width="15%"><?php echo to_currency($item_variation['cost_price']); ?></td>
									<?php } ?>
	 								<td width="15%"><?php echo to_currency($item_variation['promo_price']); ?></td>
									<td width="15%"><?php echo $item_variation['start_date'] ? $item_variation['start_date'] : lang('common_not_set'); ?></td>
									<td width="15%"><?php echo $item_variation['end_date'] ? $item_variation['end_date'] : lang('common_not_set'); ?></td>
	 							</tr>
	 							<?php } ?>
	 						<?php } ?>
	 					</table>
						
					</div>
				 
				</div>
			
				<div class="tab-pane" id="Inventory">
					<div class="panel panel-piluku">
						<div class="panel-heading">
							<div class="panel-title">
								<h3><span class="ion-android-clipboard"></span> <?php echo lang('common_inventory'); ?>
								<div class="panel-options custom">
		 						<?php if ($this->Employee->has_module_action_permission('items','add_update', $this->Employee->get_logged_in_employee_info()->person_id) or $item_info->name=="")	{ ?>
									<a href="<?php echo site_url("items/inventory/".$item_info->item_id."?redirect=".$redirect)?>" class="btn btn-default pull-right"><?php echo lang("common_edit") ?></a>
								<?php } ?>
								</div>
								</h3> 
							</div>
						</div>
						
 				
					<?php foreach($authed_locations as $authed_location_id) {?>
						<h4 style="padding: 10px;">
							<?php
							$location_info = $this->Location->get_info($authed_location_id);
							$location_name = $location_info->name;
							echo $location_name;
							?>
						</h4>
					<table class="table table-bordered table-hover table-striped">	
 						<?php if(count($item_variation_location) > 0) { ?>
 							<tr>
 								<th width="25%"><span class="item_information_heading"><?php echo lang('common_variation'); ?></span></th>
 								<th width="25%"><span class="item_information_heading"><?php echo lang('common_quantity'); ?></span></th>
 								<th width="25%"><span class="item_information_heading"><?php echo lang('items_reorder_level'); ?></span></th>
 								<th width="25%"><span class="item_information_heading"><?php echo lang('common_replenish_level'); ?></span></th>
 							</tr>
							
 							<?php foreach($item_variation_location as $item_variation_id => $item_variation) { ?>
 							<tr><td width="25%"><?php echo $item_variation['name']; ?></td><td width="25%"><?php echo to_quantity($item_variation_location_info_all[$authed_location_id][$item_variation_id]->quantity); ?></td><td width="25%"><?php echo to_quantity($item_variation['reorder_level']); ?></td><td width="25%"><?php echo to_quantity($item_variation['replenish_level']); ?></td></tr>
 							<?php } ?>
 						<?php } else { ?>
 							<tr><td width="30%"><?php echo lang('items_quantity'); ?></td> <td> <?php echo to_quantity($item_location_info_all[$authed_location_id]->quantity); ?></td></tr>
 							<tr><td><?php echo lang('items_reorder_level'); ?></td> <td> <?php echo to_quantity($reorder_level[$authed_location_id]); ?></td></tr>
 						<?php } ?>
 					</table>
						<?php } ?>
					</div>
					
					<?php if(!empty($suspended_receivings)) { ?>
					<div class="panel panel-piluku">
						<div class="panel-heading">
							<div class="panel-title">
								<h3><span class="ion-android-clipboard"></span> <?php echo lang('receivings_list_of_suspended'); ?></h3> 
							</div>
						</div>
						<table class="table table-bordered table-hover table-striped" width="1200px">
							<tr>
								<th><?php echo lang('receivings_id');?></th>
								<th><?php echo lang('items_quantity');?></th>
							</tr>
				
							<?php foreach($suspended_receivings as $receiving_item) {?>
								<tr>
									<td style="text-align: center;"><?php echo anchor('receivings/receipt/'.$receiving_item['receiving_id'], 'RECV '.$receiving_item['receiving_id'], array('target' => '_blank'));?></td>
									<td style="text-align: center;"><?php echo to_quantity($receiving_item['quantity_purchased']);?></td>
								</tr>
							<?php } ?>
						</table>
					</div>
					<?php } ?>
					
				</div>
				<div class="tab-pane" id="Images">
					<div class="panel panel-piluku">
						<div class="panel-heading">
							<div class="panel-title">
								<h3><span class="ion-android-clipboard"></span> <?php echo lang('common_images'); ?>
								<div class="panel-options custom">
		 						<?php if ($this->Employee->has_module_action_permission('items','add_update', $this->Employee->get_logged_in_employee_info()->person_id) or $item_info->name=="")	{ ?>
									<a href="<?php echo site_url("items/images/".$item_info->item_id."?redirect=".$redirect)?>" class="btn btn-default pull-right"><?php echo lang("common_edit") ?></a>
								<?php } ?>
								</div>
								</h3> 
							</div>
						</div>
						
						<div class="panel-body">
							
							<div class="row">
								
								<?php foreach($item_images as $image) { ?>
							  <div class="col-sm-6 col-md-4">
							    <div class="thumbnail">
							      <?php echo img(array('src' => app_file_url($image['image_id']),'class'=>' img-polaroid')); ?>
							      <!-- <div class="caption">
											<p>...</p>
							      </div> -->
							    </div>
							  </div>
								<?php } ?>
							</div>
							
						</div>
					</div>
				
				</div>
			</div><!-- end tabs -->
		</div>
	</div>
</div>

<script>
	$("#cost_price_expand_collapse").click(function()
	{
		if ($(this).text() == '+')
		{
			$("#cost_price_value").show();
			$(this).text('-');
		}
		else
		{
			$("#cost_price_value").hide();			
			$(this).text('+');
		}
	});
</script>


