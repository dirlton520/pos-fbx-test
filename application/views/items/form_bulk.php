<div id="content-header" class="hidden-print bulk-pop">
<div class="modal-dialog customer-recent-sales">
	<div class="modal-content">
		<div class="spinner hidden" id="grid-loader" >
		  <div class="rect1"></div>
		  <div class="rect2"></div>
		  <div class="rect3"></div>
		</div>
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-label=<?php echo json_encode(lang('common_close')); ?>><span aria-hidden="true" class="ti-close"></span></button>
			<h5 class="modal-title"><?php echo lang("items_edit_multiple_items"); ?></h5>
		</div>
		<div class="modal-body">
			<?php echo form_open('items/bulk_update/',array('id'=>'bulk_item_form','class'=>'form-horizontal')); ?>
						
						<div class="form-group row">
							<?php echo form_label(lang('common_category').':', 'category_id',array('class'=>'control-label col-xs-3')); ?>
							
							<div class="col-xs-9">
								<?php echo form_dropdown('category_id', $categories, '','class="form-control "');?>
							</div>
						</div>
						
						
						<div class="form-group row">	
							<?php echo form_label(lang('common_tags').':', 'category',array('class'=>'control-label col-xs-3')); ?>
							<div class="col-xs-9">
							<?php echo form_input(array(
								'name'=>'tags',
								'class'=>'form-control',
								'id'=>'tags')
								);?>
							</div>
						</div>

						<div class="form-group row">	
							<?php echo form_label(lang('common_supplier').':', 'supplier',array('class'=>'control-label col-xs-3')); ?>
							<div class="col-xs-9">
								<?php echo form_dropdown('supplier_id', $suppliers, '','class="form-control"');?>
							</div>
						</div>

						<div class="form-group row">	
							<?php echo form_label(lang('common_manufacturer').':', 'supplier',array('class'=>'control-label col-xs-3')); ?>
							<div class="col-xs-9">
								<?php echo form_dropdown('manufacturer_id', $manufacturers, '','class="form-control"');?>
							</div>
						</div>
												

						
						<div class="form-group row">	
							<?php echo form_label(lang('common_cost_price').':', 'item_cost_price_method',array('class'=>'control-label col-xs-3')); ?>
							<div class="col-xs-9">
								<?php echo form_dropdown('item_cost_price_method', $item_cost_price_choices, '', 'id="item_cost_price_method" class="form-control"');?>
							</div>
						</div>

						<?php
						if ($this->config->item('enable_customer_loyalty_system') && $this->config->item('loyalty_option') == 'advanced') {
						?>
							
							<div class="form-group row">	
								<?php echo form_label(lang('common_disable_loyalty').':', 'disable_loyalty',array('class'=>'control-label col-xs-3')); ?>
								<div class="col-xs-9">
									<?php echo form_dropdown('disable_loyalty', $disable_loyalty_choices, '', 'id="disable_loyalty" class="form-control"');?>
								</div>
							</div>
							
							<div class="form-group row">	
								<?php echo form_label(lang('common_loyalty_multiplier').':', 'loyalty_multiplier',array('class'=>'control-label col-xs-3')); ?>
								<div class="col-xs-9">
								<?php echo form_input(array(
									'name'=>'loyalty_multiplier',
									'class'=>'form-control',
									'id'=>'loyalty_multiplier')
									);?>
								</div>
							</div>
							
						
							
						<?php } ?>

						<div id="item_cost_price_container" class="cost-price-container hidden">	
							<div class="form-group row">	
								<?php echo form_label(lang('items_cost_price_value').':', 'cost_price',array('class'=>'control-label col-xs-3')); ?>
								<div class="col-xs-9">
								<?php echo form_input(array(
									'name'=>'cost_price',
									'class'=>'form-control',
									'id'=>'cost_price')
									);?>
								</div>
							</div>
						</div>
						
						<div class="form-group row">	
							<?php echo form_label(lang('common_change_cost_price_during_sale').':', 'change_cost_price',array('class'=>'control-label col-xs-3')); ?>
							<div class="col-xs-9">
								<?php echo form_dropdown('change_cost_price', $change_cost_price_during_sale_choices, '', 'id="change_cost_price" class="form-control"');?>
							</div>
						</div>
						
						
						<?php
						if ($this->config->item('enable_ebt_payments')) { ?>
						
						<div class="form-group row">	
							<?php echo form_label(lang('common_is_ebt_item').':', 'is_ebt_item',array('class'=>'control-label col-xs-3')); ?>
							<div class="col-xs-9">
								<?php echo form_dropdown('is_ebt_item', $change_is_ebt_item_during_sale_choices, '', 'id="is_ebt_item" class="form-control"');?>
							</div>
						</div>
						<?php } ?>
						
				
						<div class="form-group row">	
							<?php echo form_label(lang('common_unit_price').':', 'item_unit_price_method',array('class'=>'control-label col-xs-3')); ?>
							<div class="col-xs-9">
								<?php echo form_dropdown('item_unit_price_method', $item_unit_price_choices, '', 'id="item_unit_price_method" class="form-control"');?>
							</div>
						</div>


						<div id="item_unit_price_container" class="unit-price-container hidden">	
							<div class="form-group row">	
								<?php echo form_label(lang('items_unit_price_value').':', 'unit_price',array('class'=>'control-label col-xs-3')); ?>
								<div class="col-xs-9">
								<?php echo form_input(array(
									'name'=>'unit_price',
									'class'=>'form-control',
									'id'=>'unit_price')
									);?>
								</div>
							</div>
						</div>
							
						<?php foreach($this->Tier->get_all()->result() as $tier) { ?>	
							<div class="form-group row">
								<?php echo form_label($tier->name.':', 'tier_'.$tier->id,array('class'=>'control-label col-xs-3')); ?>
								<div class="col-xs-9">
									<?php echo form_input(array(
										'name'=>'tier_values['.$tier->id.']',
										'size'=>'8',
										'id'=>'tier_'.$tier->id,
										'class'=>'form-control form-inps margin10',
										'value'=>  '')
									);?>

									<?php echo form_dropdown('tier_types['.$tier->id.']', array('unit_price' => lang('common_fixed_price'), 'percent_off' => lang('common_percent_off'), 'cost_plus_percent' => lang('common_cost_plus_percent'),'cost_plus_fixed_amount' => lang('common_cost_plus_fixed_amount')), '','class="form-control"');?>
								</div>
							</div>
							
						<?php } ?>
						
						<div class="form-group row">	
							<?php echo form_label(lang('items_promo_price').':', 'item_promo_price_method',array('class'=>'control-label col-xs-3')); ?>
							<div class="col-xs-9">
								<?php echo form_dropdown('item_promo_price_method', $item_promo_price_choices, '', 'id="item_promo_price_method" class="form-control"');?>
							</div>
						</div>


						<div id="item_promo_price_container" class="promo-price-container hidden">	
							<div class="form-group row">	
								<?php echo form_label(lang('items_promo_price_value').':', 'promo_price',array('class'=>'control-label col-xs-3')); ?>
								<div class="col-xs-9">
									<?php echo form_input(array(
									'name'=>'promo_price',
									'class'=>'form-control',
									'id'=>'promo_price')
									);?>
								</div>
							</div>
							
							<div id="use_selling_price_container" style="display: none;" class="row">
								<div class="col-xs-9 col-xs-offset-3">
								<?php echo form_checkbox('use_selling_price', '1', false, "id='use_selling_price'"); ?>
							    <label for="use_selling_price"><span></span></label>
									<?php echo lang('items_use_selling_price');?>
								</div>
							</div>
							
						</div>
						
						<div class="form-group row">
							<?php echo form_label(lang('items_promo_start_date').':', 'start_date',array('class'=>'control-label col-xs-3')); ?>
							<div class="col-xs-9">
								<div class="input-group date" data-date="">
									<span class="input-group-addon bg">
										<i class="ion ion-ios-calendar-outline"></i>
									</span>
									<?php echo form_input(array(
										'name'=>'start_date',
										'id'=>'start_date',
										'class'=>'form-control datepicker',
										'value' => '')
										);?> 
								</div>
							</div>
						</div>


						<div class="form-group row">
							<?php echo form_label(lang('items_promo_end_date').':', 'end_date',array('class'=>'control-label col-xs-3')); ?>
							<div class="col-xs-9">
								<div class="input-group date" data-date="">
									<span class="input-group-addon bg">
										<i class="ion ion-ios-calendar-outline"></i>
									</span>
									<?php echo form_input(array(
										'name'=>'end_date',
										'id'=>'end_date',
										'class'=>'form-control datepicker',
										'value'=>'')
										);?> 
								</div>
							</div>
						</div>
						


					<div class="form-group row">	
						<?php echo form_label(lang('common_override_default_commission').':', 'override_default_commission',array('class'=>'control-label col-xs-3')); ?>
						<div class="col-xs-9">
							<?php echo form_dropdown('override_default_commission', $override_default_commission_choices, '', 'id="override_default_commission" class="form-control"');?>
						</div>
					</div>
						
					<div id="commission_container" class="commission-container hidden">

						<div class="commission-container">
							<div class="form-group row">
								<?php echo form_label(lang('reports_commission'), 'commission_value',array('class'=>'control-label col-xs-3')); ?>
								<div class='col-xs-9'>
									<?php echo form_input(array(
										'name'=>'commission_value',
										'id'=>'commission_value',
										'size'=>'8',
										'class'=>'form-control', 
										'value'=> '')
									);?>
									<?php echo form_dropdown('commission_type', array('percent' => lang('common_percentage'), 'fixed' => lang('common_fixed_amount')),'','id="commission_type" class="form-control"');?>
								</div>
							</div>
							
							<div id="commission-percent-calculation-container" class="form-group row">	
								<?php echo form_label(lang('common_commission_percent_calculation').': ', 'commission_percent_type',array('class'=>'control-label col-xs-3')); ?>
								<div class="col-xs-9">
								<?php echo form_dropdown('commission_percent_type', array(
									'selling_price'  => lang('common_unit_price'),
									'profit'    => lang('common_profit')),
									'class="form-control"'
									);
									?>
								</div>
							</div>
						</div>
					</div>

						<div class="form-group row">	
							<?php echo form_label(lang('common_override_default_tax').':', 'override_default_tax',array('class'=>'control-label col-xs-3')); ?>
							<div class="col-xs-9">
								<?php echo form_dropdown('override_default_tax', $override_default_tax_choices, '', 'id="override_default_tax" class="form-control"');?>
							</div>
						</div>
						
						


						<div id="tax_container" class="tax-container hidden">	
							
							<div class="form-group row">	
								<?php echo form_label(lang('common_tax_class').':', 'tax_class_id',array('class'=>'control-label col-xs-3')); ?>
								<div class="col-xs-9">
									<?php echo form_dropdown('tax_class_id', $tax_classes, '', 'id="tax_class_id" class="form-control"');?>
								</div>
							</div>
							
							<div class="form-group">
								<h4 class="text-center"><?php echo lang('common_or') ?></h4>
							</div>
							
							<div class="form-group row">	
								<?php echo form_label(lang('common_tax_1').':', 'tax_percent_1',array('class'=>'control-label col-xs-3')); ?>
								<div class="col-xs-9">
									<?php echo form_input(array(
										'name'=>'tax_names[]',
										'id'=>'tax_name_1',
										'size'=>'8',
										'class'=>'form-control',
										'placeholder' =>lang('common_tax_name'),
										));?>

									<?php echo form_input(array(
										'name'=>'tax_percents[]',
										'id'=>'tax_percent_name_1',
										'size'=>'3',
										'class'=>'form-control',
										'placeholder' =>lang('common_tax_percent'),
										));?>
										%
									<?php echo form_hidden('tax_cumulatives[]', '0'); ?>
								</div>
							</div>

							<div class="form-group row">	
								<?php echo form_label(lang('common_tax_2').':', 'tax_percent_2',array('class'=>'control-label col-xs-3')); ?>
								<div class="col-xs-9">
									<?php echo form_input(array(
										'name'=>'tax_names[]',
										'id'=>'tax_name_2',
										'size'=>'8',
										'class'=>'form-control',
										'placeholder' =>lang('common_tax_name'),
										));?>

									<?php echo form_input(array(
										'name'=>'tax_percents[]',
										'id'=>'tax_percent_name_2',
										'size'=>'3',
										'class'=>'form-control',
										'placeholder' =>lang('common_tax_percent'),
										));?>
										%
									<?php echo form_checkbox('tax_cumulatives[]', '1', isset($item_tax_info[1]['cumulative']) && $item_tax_info[1]['cumulative'] ? true : false, "id='tax_cumulatives'"); ?>
							    <label for="tax_cumulatives"><span></span></label>
							    <span class="cumulative_label">
									 <?php echo lang('common_cumulative'); ?>
					    		</span>
								</div>
							</div>
							
							<span id="non_cumulatives">
							<div class="form-group row">	
								<?php echo form_label(lang('common_tax_3').':', 'tax_percent_3',array('class'=>'control-label col-xs-3')); ?>
								<div class="col-xs-9">
									<?php echo form_input(array(
										'name'=>'tax_names[]',
										'id'=>'tax_name_3',
										'class'=>'form-control',
										'placeholder' =>lang('common_tax_name'),
										'size'=>'8',
										));?>

									<?php echo form_input(array(
										'name'=>'tax_percents[]',
										'id'=>'tax_percent_name_3',
										'class'=>'form-control',
										'size'=>'3',
										'placeholder' =>lang('common_tax_percent'),
										));?>
										%
									<?php echo form_hidden('tax_cumulatives[]', '0'); ?>
								</div>
							</div>

							<div class="form-group row">	
								<?php echo form_label(lang('common_tax_4').':', 'tax_percent_4',array('class'=>'control-label col-xs-3')); ?>
								<div class="col-xs-9">
									<?php echo form_input(array(
										'name'=>'tax_names[]',
										'id'=>'tax_name_4',
										'size'=>'8',
										'class'=>'form-control',
										'placeholder' =>lang('common_tax_name'),
										));?>

									<?php echo form_input(array(
										'name'=>'tax_percents[]',
										'id'=>'tax_percent_name_4',
										'size'=>'3',
										'class'=>'form-control',
										'placeholder' =>lang('common_tax_percent'),
										));?>
										%
									<?php echo form_hidden('tax_cumulatives[]', '0'); ?>
								</div>
							</div>
							
							<div class="form-group row">	
								<?php echo form_label(lang('common_tax_5').':', 'tax_percent_5',array('class'=>'control-label col-xs-3')); ?>
								<div class="col-xs-9">
									<?php echo form_input(array(
										'name'=>'tax_names[]',
										'id'=>'tax_name_5',
										'size'=>'8',
										'class'=>'form-control',
										'placeholder' =>lang('common_tax_name'),
										));?>

									<?php echo form_input(array(
										'name'=>'tax_percents[]',
										'id'=>'tax_percent_name_5',
										'size'=>'3',
										'class'=>'form-control',
										'placeholder' =>lang('common_tax_percent'),
										));?>
										%
									<?php echo form_hidden('tax_cumulatives[]', '0'); ?>
								</div>
							</div>
						</div>
						</span>
						
						<div class="form-group row">
							<?php echo form_label(lang('common_prices_include_tax').':', 'tax_included',array('class'=>'control-label col-xs-3')); ?>
							<div class="col-xs-9">
								<?php echo form_dropdown('tax_included', $tax_included_choices, '','class="form-control"');?>
							</div>
						</div>

						<div class="form-group row">
							<?php echo form_label(lang('items_is_service').':', 'is_service', array('class'=>'control-label col-xs-3')); ?>
							<div class="col-xs-9">
								<?php echo form_dropdown('is_service', $is_service_choices, '','class="form-control"');?>
							</div>
						</div>
						
						
						<div class="form-group row">
							<?php echo form_label(lang('common_disable_from_price_rules').':', 'disable_from_price_rules', array('class'=>'control-label col-xs-3')); ?>
							<div class="col-xs-9">
								<?php echo form_dropdown('disable_from_price_rules', $disable_from_price_rules_choices, '','class="form-control"');?>
							</div>
						</div>
						
						
						<div class="form-group row">
							<?php echo form_label(lang('common_inactive').':', 'inactive', array('class'=>'control-label col-xs-3')); ?>
							<div class="col-xs-9">
								<?php echo form_dropdown('item_inactive', $inactive_choices, '','class="form-control"');?>
							</div>
						</div>
						
						
						
						<div class="form-group row">
							<?php echo form_label(lang('common_is_favorite').':', 'is_favorite', array('class'=>'control-label col-xs-3')); ?>
							<div class="col-xs-9">
								<?php echo form_dropdown('is_favorite', $favorite_choices, '','class="form-control"');?>
							</div>
						</div>
						
						
						
						
						<?php if ($this->config->item("ecommerce_platform")) { ?>
						<div class="form-group row">
							<?php echo form_label(lang('items_is_ecommerce').':', 'is_service',array('class'=>'control-label col-xs-3')); ?>
							<div class="col-xs-9">
								<?php echo form_dropdown('is_ecommerce', $is_ecommerce_choices, '','class="form-control"');?>
							</div>
						</div>
						<?php } ?>
						<?php if ($this->Employee->has_module_action_permission('items','edit_quantity', $this->Employee->get_logged_in_employee_info()->person_id)) { ?>
						<div class="form-group row">	
							<?php echo form_label(lang('items_quantity').':', 'quantity',array('class'=>'control-label col-xs-3')); ?>
							<div class="col-xs-9">
								<?php echo form_input(array(
									'name'=>'quantity',
									'type'=>'number',
									'class'=>'form-control',
									'id'=>'quantity')
								);?>
							</div>
						</div>
					<?php } ?>

						<div class="form-group row">	
							<?php echo form_label(lang('items_reorder_level').':', 'reorder_level',array('class'=>'control-label col-xs-3')); ?>
							<div class="col-xs-9">
								<?php echo form_input(array(
									'name'=>'reorder_level',
									'class'=>'form-control',
									'id'=>'reorder_level')
								);?>
							</div>
						</div>


						<div class="form-group row">	
							<?php echo form_label(lang('common_replenish_level').':', 'replenish_level',array('class'=>'control-label col-xs-3')); ?>
							<div class="col-xs-9">
								<?php echo form_input(array(
									'name'=>'replenish_level',
									'class'=>'form-control',
									'id'=>'replenish_level')
								);?>
							</div>
						</div>
												
						<div class="form-group row">
							<?php echo form_label(lang('items_days_to_expiration').':', 'allow_alt_description',array('class'=>'control-label col-xs-3')); ?>
							<div class="col-xs-9">
								<?php echo form_input(array(
									'name'=>'expire_days',
									'class'=>'form-control',
									'id'=>'expire_days')
								);?>
							</div>
						</div>

						<div class="form-group row">
							<?php echo form_label(lang('items_allow_alt_desciption').':', 'allow_alt_description',array('class'=>'control-label col-xs-3')); ?>
							<div class="col-xs-9">
								<?php echo form_dropdown('allow_alt_description', $allow_alt_desciption_choices, '','class="form-control"');?>
							</div>
						</div>

						<div class="form-group row">
							<?php echo form_label(lang('items_is_serialized').':', 'is_serialized',array('class'=>'control-label col-xs-3')); ?>
							<div class="col-xs-9">
								<?php echo form_dropdown('is_serialized', $serialization_choices, '','class="form-control"');?>
							</div>
						</div>
						
						<div class="form-group row">
							<?php echo form_label(lang('common_is_barcoded').':', 'is_barcoded',array('class'=>'control-label col-xs-3')); ?>
							<div class="col-xs-9">
								<?php echo form_dropdown('is_barcoded', $is_barcoded_choices, '','class="form-control"');?>
							</div>
						</div>
						
						
						<?php if ($this->config->item('verify_age_for_products')) { ?>
						
						<div class="form-group row">
							<?php echo form_label(lang('common_requires_age_verification').':', 'verify_age',array('class'=>'control-label col-xs-3')); ?>
							<div class="col-xs-9">
								<?php echo form_dropdown('verify_age', $verify_age_choices, '','class="form-control"');?>
							</div>
						</div>
						
						
						<div class="form-group row">
							<?php echo form_label(lang('common_required_age').':', 'required_age',array('class'=>'control-label col-xs-3')); ?>
							<div class="col-xs-9">
								<?php echo form_input(array(
									'name'=>'required_age',
									'class'=>'form-control',
									'id'=>'required_age')
								);?>
							</div>
						</div>
						
						
						
						<?php  } ?>
						
		        <div class="form-group">
							<?php echo form_label(lang('items_dimensions').':', 'dimensions',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label wide')); ?>
							<div class="col-sm-9 col-md-9 col-lg-10">
								<?php echo form_input(array(
									'name'=>'length',
									'id'=>'length',
									'placeholder' => lang('items_length'),
									'class'=>'form-control form-inps',
									'value'=>'')
								);?><br />
								<?php echo form_input(array(
									'name'=>'width',
									'id'=>'width',
									'placeholder' => lang('items_width'),
									'class'=>'form-control form-inps',
									'value'=>'')
								);?><br />
								<?php echo form_input(array(
									'name'=>'height',
									'id'=>'height',
									'placeholder' => lang('items_height'),
									'class'=>'form-control form-inps',
									'value'=> '')
								);?>
						
							</div>
						</div>
						
		        <div class="form-group">
							<?php echo form_label(lang('items_weight').':', 'weight',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label wide')); ?>
							<div class="col-sm-9 col-md-9 col-lg-10">
								<?php echo form_input(array(
									'name'=>'weight',
									'id'=>'weight',
									'class'=>'form-control form-inps',
									'value'=>'')
								);?>
							</div>
						</div>
						
						
				        <div class="form-group">
						<?php echo form_label(lang('common_description').':', 'description',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label wide')); ?>
							<div class="col-sm-9 col-md-9 col-lg-10">
							<?php echo form_textarea(array(
								'name'=>'description',
								'class'=>'form-control text-area',
								'rows'=>'4',
								'cols'=>'30',
								'value'=>''));?>
							</div>
						</div>
						
						
				        <div class="form-group">
						<?php echo form_label(lang('common_long_description').':', 'long_description',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label wide')); ?>
							<div class="col-sm-9 col-md-9 col-lg-10">
							<?php echo form_textarea(array(
								'name'=>'long_description',
								'class'=>'form-control text-area',
								'rows'=>'4',
								'cols'=>'30',
								'value'=>''));?>
							</div>
						</div>
						
						
						<?php if ($this->config->item("ecommerce_platform")) { ?>
						<div class="form-group">
							<?php echo form_label(lang('items_ecommerce_shipping_class').':', 'ecommerce_shipping_class_id',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label wide ')); ?>
							<div class="col-sm-9 col-md-9 col-lg-10">
								<?php echo form_dropdown('ecommerce_shipping_class_id', $ecommerce_shipping_classes, '','class="form-control" id="ecommerce_shipping_class_id"');?>
							</div>
						</div>
						<?php } ?>
						
						
						
		 			 <?php for($k=1;$k<=NUMBER_OF_PEOPLE_CUSTOM_FIELDS;$k++) { ?>
		 				<?php
		 				 $custom_field = $this->Item->get_custom_field($k);
		 				 if($custom_field !== FALSE)
		 				 { ?>
		 					 <div class="form-group row">
		 					 <?php echo form_label($custom_field . ' :', "custom_field_${k}_value", array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label ')); ?>
					 							
							<div class="col-xs-9">
		 							<?php if ($this->Item->get_custom_field($k,'type') == 'checkbox') { ?>
								
		 								<?php echo form_checkbox("custom_field_${k}_value", '1','',"id='custom_field_${k}_value'");?>
		 								<label for="<?php echo "custom_field_${k}_value"; ?>"><span></span></label>
								
		 							<?php } elseif($this->Item->get_custom_field($k,'type') == 'date') { ?>
								
		 									<?php echo form_input(array(
		 									'name'=>"custom_field_${k}_value",
		 									'id'=>"custom_field_${k}_value",
		 									'class'=>"custom_field_${k}_value".' form-control',
		 									'value'=> '')
		 									);?>									
		 									<script>
		 										var $field = <?php echo "\$('#custom_field_${k}_value')"; ?>;
		 								    $field.datetimepicker({format: JS_DATE_FORMAT, locale: LOCALE, ignoreReadonly: IS_MOBILE ? true : false});	
										
		 									</script>
										
		 							<?php } elseif($this->Item->get_custom_field($k,'type') == 'dropdown') { ?>
									
		 									<?php 
		 									$choices = explode('|',$this->Item->get_custom_field($k,'choices'));
		 									$select_options = array('' => lang('common_do_nothing'));
		 									foreach($choices as $choice)
		 									{
		 										$select_options[$choice] = $choice;
		 									}
		 									echo form_dropdown("custom_field_${k}_value", $select_options, "", 'class="form-control"');?>
									
		 							<?php } else {
							
		 									echo form_input(array(
		 									'name'=>"custom_field_${k}_value",
		 									'id'=>"custom_field_${k}_value",
		 									'class'=>"custom_field_${k}_value".' form-control',
		 									'value'=>"")
		 									);?>									
		 							<?php } ?>
		 						</div>
		 					</div>
		 				<?php } //end if?>
		 				<?php } //end for loop?>
						
						
						<div class='modal-footer'>
							<div class="form-controls">
								<?php
								echo form_submit(array(
									'name'=>'submit',
									'id'=>'submit',
									'value'=>lang('common_save'),
									'class'=>'btn btn-primary')
								); ?>
							</div>
						</div>
						<?php echo form_close(); ?>
		</div>
	</div>
</div>
</div>

<script type='text/javascript'>

function commission_change()
{
	if ($("#commission_type").val() == 'percent')
	{
		$("#commission-percent-calculation-container").show();
	}
	else
	{
		$("#commission-percent-calculation-container").hide();						
	}
}
$("#commission_type").change(commission_change);
$(document).ready(commission_change);

//validation and submit handling
$(document).ready(function()
{
	setTimeout(function(){$(":input:visible:first","#bulk_item_form").focus();},100);
	
	$('#tags').selectize({
		delimiter: ',',
		loadThrottle : 215,
		persist: false,
		valueField: 'value',
		labelField: 'label',
		searchField: 'label',
		create: true,
		render: {
	      option_create: function(data, escape) {
				var add_new = <?php echo json_encode(lang('common_add_new_tag')) ?>;
	        return '<div class="create">'+escape(add_new)+' <strong>' + escape(data.input) + '</strong></div>';
	      }
		},
		load: function(query, callback) {
			if (!query.length) return callback();
			$.ajax({
				url:'<?php echo site_url("items/tags");?>'+'?term='+encodeURIComponent(query),
				type: 'GET',
				error: function() {
					callback();
				},
				success: function(res) {
					res = $.parseJSON(res);
					callback(res);
				}
			});
		}
	});
		
	date_time_picker_field($('#bulk_item_form .datepicker'), JS_DATE_FORMAT);
	
	$("#override_default_tax").change(function()
	{
		if ($(this).val() == '1')
		{
			$("#tax_container").removeClass('hidden');
		}
		else
		{
			$("#tax_container").addClass('hidden');
		}
	});
	
	$("#tax_cumulatives").change(function() {
		
		if(this.checked) {
			
			$("#non_cumulatives").addClass("hidden");
			$("#non_cumulatives").find("input:text").each(function() {
				$(this).val('');
			});
			
		} else {
			$("#non_cumulatives").removeClass("hidden");
		}
	});
	
	$("#override_default_commission").change(function()
	{
		if ($(this).val() == '1')
		{
			$("#commission_container").removeClass('hidden');
		}
		else
		{
			$("#commission_container").addClass('hidden');
		}
	});
	
	$("#item_cost_price_method").change(function()
	{
		if ($(this).val() != '')
		{			
			$("#item_cost_price_container").removeClass('hidden');
		}
		else
		{
			$("#item_cost_price_container").addClass('hidden');	
		}
	});
	
	$("#item_unit_price_method").change(function()
	{
		if ($(this).val() != '')
		{			
			$("#item_unit_price_container").removeClass('hidden');
		}
		else
		{
			$("#item_unit_price_container").addClass('hidden');	
		}
	});
	
	$("#item_promo_price_method").change(function()
	{
		if ($(this).val() != '' && $(this).val() != 'remove_promo')
		{			
			$("#item_promo_price_container").removeClass('hidden');
		}
		else
		{
			$("#item_promo_price_container").addClass('hidden');	
		}
	});
	
	$("#item_promo_price_method").change(function()
	{
		if ($(this).val() == 'percent')
		{
			$("#use_selling_price_container").show();			
		}
		else
		{
			$("#use_selling_price_container").hide();
		}
	});
	
	

	var submitting = false;
	
	$('#bulk_item_form').validate({
		submitHandler:function(form)
		{
			if (submitting) return;			
			bootbox.confirm(<?php echo json_encode(lang('items_confirm_bulk_edit')); ?>, function(result)
			{
				if(result)
				{
					//Get the selected ids and create hidden fields to send with ajax submit.
					var selected_item_ids=get_selected_values();
					for(k=0;k<selected_item_ids.length;k++)
					{
						$(form).append("<input type='hidden' name='item_ids[]' value='"+selected_item_ids[k]+"' />");
					}
				
					$('#grid-loader').removeClass('hidden');
					submitting = true;
					$(form).ajaxSubmit({
						success:function(response)
						{
							post_bulk_form_submit(response);
							$('#grid-loader').addClass('hidden');
							submitting = false;
						},
						dataType:'json'
					});
				}
				else
				{
					//Issue because of double modal...cannot scroll if NOT confirmed. This is a hack...tried to use events but doesn't work
					setTimeout(function()
					{
						$('body').addClass('modal-open');
					},1000)
				}
			});

		},
		errorClass: "text-danger",
		errorElement: "span",
		rules: 
		{
			"tax_percents[]":
			{
				number:true
			},
			reorder_level:
			{
				number:true
			}
		},
		messages: 
		{
			"tax_percents[]":
			{
				number:<?php echo json_encode(lang('items_tax_percent_number')); ?>
			},
			reorder_level:
			{
				number:<?php echo json_encode(lang('items_reorder_level_number')); ?>
			}
		}
	});
});
</script>
