<?php $this->load->view("partial/header"); ?>
<?php $this->load->view('partial/categories/category_modal', array('categories' => $categories));?>

<?php $query = http_build_query(array('redirect' => $redirect, 'progression' => $progression ? 1 : null, 'quick_edit' => $quick_edit ? 1 : null)); ?>
<?php $manage_query = http_build_query(array('redirect' => uri_string().($query ? "?".$query : ""), 'progression' => $progression ? 1 : null, 'quick_edit' => $quick_edit ? 1 : null)); ?>

<div class="spinner" id="grid-loader" style="display:none">
  <div class="rect1"></div>
  <div class="rect2"></div>
  <div class="rect3"></div>
</div>
	

<div class="manage_buttons">
	<div class="row">
		<div class="<?php echo isset($redirect) ? 'col-xs-9 col-sm-10 col-md-10 col-lg-10': 'col-xs-12 col-sm-12 col-md-12' ?> margin-top-10">
			<div class="modal-item-info padding-left-10">
				<div class="modal-item-details margin-bottom-10">
					<?php if(!$item_kit_info->item_kit_id) { ?>
			    <span class="modal-item-name new"><?php echo lang('item_kits_new'); ?></span>
					<?php } else { ?>
		    	<span class="modal-item-name"><?php echo H($item_kit_info->name); ?></span>
					<span class="modal-item-category"><?php echo H($category); ?></span>
					<?php } ?>
				</div>
			</div>	
		</div>
		<?php if(isset($redirect)) { ?>
		<div class="col-xs-3 col-sm-2 col-md-2 col-lg-2 margin-top-10">
			<div class="buttons-list">
				<div class="pull-right-btn">
				<?php echo 
					anchor(site_url($redirect), ' ' . lang('common_done'), array('class'=>'outbound_link btn btn-primary btn-lg ion-android-exit', 'title'=>''));
				?>
				</div>
			</div>
		</div>
		<?php } ?>
	</div>
</div>

<?php if(!$quick_edit) { ?>
<?php $this->load->view('partial/nav', array('progression' => $progression, 'query' => $query, 'item_kit_info' => $item_kit_info)); ?>
<?php } ?>

<?php echo form_open_multipart('item_kits/save/'.(!isset($is_clone) ? $item_kit_info->item_kit_id : ''),array('id'=>'item_kit_form','class'=>'form-horizontal')); ?>
<div class="row" id="form">
	
	<div class="col-md-12">
				
		<div class="panel panel-piluku">
			<div class="panel-heading">
        <h3 class="panel-title"><i class="ion-information-circled"></i> <?php echo lang("common_item_kit_information"); ?> <small>(<?php echo lang('common_fields_required_message'); ?>)</small></h3>
				
				<div class="panel-options custom pagination pagination-top hidden-print text-center" id="pagination_top">
					<?php
					if (isset($prev_item_kit_id) && $prev_item_kit_id)
					{
							echo anchor('item_kits/view/'.$prev_item_kit_id, '<span class="hidden-xs ion-chevron-left"> '.lang('item_kits_prev_item_kit').'</span>');
					}
					if (isset($next_item_kit_id) && $next_item_kit_id)
					{
							echo anchor('item_kits/view/'.$next_item_kit_id,'<span class="hidden-xs">'.lang('item_kits_next_item_kit').' <span class="ion-chevron-right"></span</span>');
					}
					?>
	  		</div>
				
		  </div>
			<div class="panel-body">
				
				<div class="form-group">
					<?php echo form_label(lang('item_kits_name').':', 'name',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label  required')); ?>
					<div class="col-sm-9 col-md-9 col-lg-10">
					<?php echo form_input(array(
						'class'=>'form-control form-inps',
						'name'=>'name',
						'id'=>'name',
						'value'=>$item_kit_info->name)
					);?>
					</div>
				</div>
				
				<div class="form-group">
					<?php echo form_label(lang('common_barcode_name').':', 'name',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label wide')); ?>
					<div class="col-sm-9 col-md-9 col-lg-10">
						<?php echo form_input(array(
							'name'=>'barcode_name',
							'id'=>'barcode_name',
							'class'=>'form-control form-inps',
							'value'=>$item_kit_info->barcode_name)
						);?>
					</div>
				</div>
				

				<div class="form-group">
					<?php echo form_label(lang('common_category').':', 'category_id',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label  required wide')); ?>
					<div class="col-sm-9 col-md-9 col-lg-10">
						<?php echo form_dropdown('category_id', $categories,$item_kit_info->category_id, 'class="form-control form-inps" id="category_id"');?>
						<?php if ($this->Employee->has_module_action_permission('items', 'manage_categories', $this->Employee->get_logged_in_employee_info()->person_id)) {?>
								<div>
									<a href="javascript:void(0);" id="add_category"><?php echo lang('common_add_category'); ?></a>
								</div>
						<?php } ?>
					</div>
				</div>
				
				<?php
				foreach($this->Item_kit->get_secondary_categories($item_kit_info->item_kit_id)->result() as $sec_category)
				{
				?>
					<div class="form-group">
						<?php echo form_label(lang('common_secondary_category').':', 'secondary_category_id_'.$sec_category->id,array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label  wide')); ?>
						<div class="col-sm-9 col-md-9 col-lg-10">
							<?php echo form_dropdown('secondary_categories['.$sec_category->id.']', $categories,$sec_category->category_id, 'class="form-control form-inps secondary_category" id="secondary_category_id_'.$sec_category->id.'"');?>
							<div>
							<a data-index="<?php echo $sec_category->id ?>" href="javascript:void(0)" class="delete_secondary_category"><?php echo lang('common_delete');?></a>
							</div>
						</div>
						
						
					</div>
				<?php
				}
				?>
				
				<div class="form-group">
					<div class="col-sm-9 col-md-9 col-lg-10">

					<a href="javascript:void(0);" id="add_secondary_category"><?php echo lang('common_add_secondary_category'); ?></a>
					</div>
				</div>
				
				<div class="form-group">
					<?php echo form_label(lang('common_item_number_expanded').':', 'name',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label  ')); ?>
					<div class="col-sm-9 col-md-9 col-lg-10">
					<?php echo form_input(array(
						'class'=>'form-control form-inps',
						'name'=>'item_kit_number',
						'id'=>'item_kit_number',
						'value'=>$item_kit_info->item_kit_number)
					);?>
					</div>
				</div>
				
				<div class="form-group">
					<?php echo form_label(lang('common_product_id').':', 'product_id',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label wide')); ?>
					<div class="col-sm-9 col-md-9 col-lg-10">
						<?php echo form_input(array(
							'name'=>'product_id',
							'id'=>'product_id',
							'class'=>'form-control form-inps',
							'value'=>$item_kit_info->product_id)
						);?>
					</div>
				</div>
				
				<div class="form-group">
					<?php echo form_label(lang('common_manufacturer').':', 'manufacturer_id',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label wide')); ?>
					<div class="col-sm-9 col-md-9 col-lg-10">
						<?php echo form_dropdown('manufacturer_id', $manufacturers, $selected_manufacturer,'class="form-control" id="manufacturer_id"');?>
						<?php if ($this->Employee->has_module_action_permission('items', 'manage_manufacturers', $this->Employee->get_logged_in_employee_info()->person_id)) {?>
						<div>
							<?php echo anchor("items/manage_manufacturers".($manage_query ? '?'.$manage_query : ''),lang('common_manage_manufacturers'),array('title'=>lang('common_manage_manufacturers')));?>
						</div>
						<?php } ?>
						
					</div>
				</div>

				<div class="form-group">
					<?php echo form_label(lang('common_tags').':', 'tags',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label wide')); ?>
					<div class="col-sm-9 col-md-9 col-lg-10">
					<?php echo form_input(array(
						'name'=>'tags',
						'id'=>'tags',
						'class'=>'form-control form-inps',
						'value' => $tags,
					));?>
					
					<?php if ($this->Employee->has_module_action_permission('items', 'manage_tags', $this->Employee->get_logged_in_employee_info()->person_id)) {?>
							<div>
								<?php echo anchor("items/manage_tags".($manage_query ? '?'.$manage_query : ''),lang('items_manage_tags'),array('title'=>lang('items_manage_tags')));?>
							</div>
					<?php } ?>
					</div>
				</div>
				

				<div class="form-group">
					<?php echo form_label(lang('item_kits_description').':', 'description',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label  ')); ?>
					<div class="col-sm-9 col-md-9 col-lg-10">
					<?php echo form_textarea(array(
						'name'=>'description',
						'id'=>'description',
						'class'=>'form-control text-area',
						'value'=>$item_kit_info->description,
						'rows'=>'5',
						'cols'=>'17')
					);?>
					</div>
				</div>

				<div class="form-group">
					<?php echo form_label(lang('common_info_popup').':', 'info_popup',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label wide')); ?>
					<div class="col-sm-9 col-md-9 col-lg-10">
						<?php echo form_textarea(array(
							'name'=>'info_popup',
							'id'=>'info_popup',
							'value'=>$item_kit_info->info_popup,
							'class'=>'form-control  text-area',
							'rows'=>'5',
							'cols'=>'17')
						);?>
					</div>
				</div>
				
				<div class="form-group">
						<?php echo form_label(lang('common_inactive').':', '',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label wide')); ?>
						<div class="col-sm-9 col-md-9 col-lg-10">
						<?php echo form_checkbox(array(
							'name'=>'item_kit_inactive',
							'id'=>'item_kit_inactive',
							'class' => 'item_kit_inactive delete-checkbox',
							'value'=>1,
							'checked'=>(boolean)(($item_kit_info->item_kit_inactive))));
						?>
						<label for="item_kit_inactive"><span></span></label>
					</div>
				</div>
				
				<div class="form-group">
						<?php echo form_label(lang('common_is_favorite').':', '',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label wide')); ?>
						<div class="col-sm-9 col-md-9 col-lg-10">
						<?php echo form_checkbox(array(
							'name'=>'is_favorite',
							'id'=>'is_favorite',
							'class' => 'is_favorite',
							'value'=>1,
							'checked'=>(boolean)(($item_kit_info->is_favorite))));
						?>
						<label for="is_favorite"><span></span></label>
					</div>
				</div>
				

						<div class="form-group">
					
						<?php echo form_label(lang('common_is_barcoded').':', '',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label wide')); ?>
						<div class="col-sm-9 col-md-9 col-lg-10">
						<?php echo form_checkbox(array(
							'name'=>'is_barcoded',
							'id'=>'is_barcoded',
							'class' => 'is_barcoded delete-checkbox',
							'value'=>1,
							'checked'=>(boolean)(($item_kit_info->is_barcoded)) || !$item_kit_info->item_kit_id));
						?>
						<label for="is_barcoded"><span></span></label>
					</div>
				</div>

					<div class="form-group is-service-toggle">
						<?php echo form_label(lang('common_default_quantity').':', 'default_quantity',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label wide')); ?>
						<div class="col-sm-9 col-md-9 col-lg-10">
							<?php echo form_input(array(
								'name'=>'default_quantity',
								'id'=>'default_quantity',
								'class'=>'form-control form-inps',
								'value'=>$item_kit_info->default_quantity ? to_quantity($item_kit_info->default_quantity, FALSE) : '')
							);?>
						</div>
					</div>				


				<?php
				if ($this->config->item('enable_customer_loyalty_system') && $this->config->item('loyalty_option') == 'advanced')
				{
				?>
				
				<div class="form-group">
					<?php echo form_label(lang('common_disable_loyalty').':', 'disable_loyalty',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label wide')); ?>
					<div class="col-sm-9 col-md-9 col-lg-10">
						<?php echo form_checkbox(array(
							'name'=>'disable_loyalty',
							'id'=>'disable_loyalty',
								'class'=>'delete-checkbox',
							'value'=>1,
							'checked'=>($item_kit_info->disable_loyalty)? 1 : 0)
						);?>
						<label for="disable_loyalty"><span></span></label>
					</div>
				</div>
				
				<?php
				}
				?>
				
				<?php if($this->config->item('loyalty_option') == 'advanced'){?>
				<div class="form-group">	
					<?php echo form_label(lang('common_loyalty_multiplier').':', 'loyalty_multiplier', array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label wide')); ?>
					<div class="col-sm-9 col-md-9 col-lg-10">
						<?php echo form_input(array(
						'class'=>'form-control form-inps',
						'name'=>'loyalty_multiplier',
						'id'=>'loyalty_multiplier',
						'value'=>$item_kit_info->loyalty_multiplier ? to_quantity($item_kit_info->loyalty_multiplier, false) : ''));?>
					</div>
				</div>
				<?php }?>
				
				<?php
				if ($this->config->item('enable_ebt_payments')) { ?>
					<div class="form-group">
					
					<?php echo form_label(lang('common_is_ebt_item').':', '',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label wide')); ?>
					<div class="col-sm-9 col-md-9 col-lg-10">
						<?php echo form_checkbox(array(
						'name'=>'is_ebt_item',
						'id'=>'is_ebt_item',
						'class' => 'is_ebt_item delete-checkbox',
						'value'=>1,
						'checked'=>(boolean)(($item_kit_info->is_ebt_item))));
					?>
					<label for="is_ebt_item"><span></span></label>
				</div>
			</div>
			<?php } ?>
			
			<?php if ($this->config->item('verify_age_for_products')) { ?>
				<div class="form-group">
					<?php echo form_label(lang('common_requires_age_verification').':', 'verify_age',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label wide')); ?>
					<div class="col-sm-9 col-md-9 col-lg-10">
						<?php echo form_checkbox(array(
							'name'=>'verify_age',
							'id'=>'verify_age',
								'class'=>'delete-checkbox',
							'value'=>1,
							'checked'=>($item_kit_info->verify_age)? 1 : 0)
						);?>
						<label for="verify_age"><span></span></label>
					</div>
				</div>

				<div class="form-group <?php if (!$item_kit_info->verify_age){echo 'hidden';} ?>" id="required_age_container">
					<?php echo form_label(lang('common_required_age').':', 'required_age',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label wide')); ?>
					<div class="col-sm-9 col-md-9 col-lg-10">
						<?php echo form_input(array(
							'name'=>'required_age',
							'id'=>'required_age',
							'class'=>'form-control form-inps',
							'value' => $item_kit_info->item_kit_id ? $item_kit_info->required_age : $this->config->item('default_age_to_verify'),
						));?>
					</div>
				</div>
			<?php } ?>

			 <?php for($k=1;$k<=NUMBER_OF_PEOPLE_CUSTOM_FIELDS;$k++) { ?>
				<?php
				 $custom_field = $this->Item_kit->get_custom_field($k);
				 if($custom_field !== FALSE) { 
					 
					$required = false;
					$required_text = '';
					if($this->Item_kit->get_custom_field($k,'required') && in_array($current_location,$this->Item_kit->get_custom_field($k,'locations'))){
						$required = true;
						$required_text = 'required';
					}
					 
					 ?>
					 <div class="form-group">
					 <?php echo form_label($custom_field . ' :', "custom_field_${k}_value", array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label '.$required_text)); ?>
					 							
					 <div class="col-sm-9 col-md-9 col-lg-10">
							<?php if ($this->Item_kit->get_custom_field($k,'type') == 'checkbox') { ?>
								
								<?php echo form_checkbox("custom_field_${k}_value", '1', (boolean)$item_kit_info->{"custom_field_${k}_value"},"id='custom_field_${k}_value' $required_text");?>
								<label for="<?php echo "custom_field_${k}_value"; ?>"><span></span></label>
								
							<?php } elseif($this->Item_kit->get_custom_field($k,'type') == 'date') { ?>
								
									<?php echo form_input(array(
									'name'=>"custom_field_${k}_value",
									'id'=>"custom_field_${k}_value",
									'class'=>"custom_field_${k}_value".' form-control',
									'value'=>is_numeric($item_kit_info->{"custom_field_${k}_value"}) ? date(get_date_format(), $item_kit_info->{"custom_field_${k}_value"}) : '',
									($required ? $required_text : $required_text) => ($required ? $required_text : $required_text)
									)
									);?>									
									<script type="text/javascript">
										var $field = <?php echo "\$('#custom_field_${k}_value')"; ?>;
								    $field.datetimepicker({format: JS_DATE_FORMAT, locale: LOCALE, ignoreReadonly: IS_MOBILE ? true : false});	
										
									</script>
										
							<?php } elseif($this->Item_kit->get_custom_field($k,'type') == 'dropdown') { ?>
									
									<?php 
									$choices = explode('|',$this->Item_kit->get_custom_field($k,'choices'));
									$select_options = array('' => lang('common_please_select'));
									foreach($choices as $choice)
									{
										$select_options[$choice] = $choice;
									}
									echo form_dropdown("custom_field_${k}_value", $select_options, $item_kit_info->{"custom_field_${k}_value"}, 'class="form-control" '.$required_text);?>
									
								<?php } elseif($this->Item_kit->get_custom_field($k,'type') == 'image') {
										echo form_input(
											array(
												'name'=>"custom_field_${k}_value",
												'id'=>"custom_field_${k}_value",
												'type' => 'file',
												'class'=>"custom_field_${k}_value".' form-control',
												'accept'=>".png,.jpg,.jpeg,.gif"
											),
											NULL,
											$item_kit_info->{"custom_field_${k}_value"} ? "" : $required_text
										);
							
										if ($item_kit_info->{"custom_field_${k}_value"})
										{
											echo "<img width='30%' src='".app_file_url($item_kit_info->{"custom_field_${k}_value"})."' />";
											echo "<div class='delete-custom-image'><a href='".site_url('item_kits/delete_custom_field_value/'.$item_kit_info->item_kit_id.'/'.$k)."'>".lang('common_delete')."</a></div>";
										}
									
							 	}
 							 elseif($this->Item_kit->get_custom_field($k,'type') == 'file')
 							 {
								echo form_input(
									array(
									  'name'=>"custom_field_${k}_value",
									  'id'=>"custom_field_${k}_value",
									  'type' => 'file',
									  'class'=>"custom_field_${k}_value".' form-control'
									),
								  NULL,
								  $item_kit_info->{"custom_field_${k}_value"} ? "" : $required_text
							  );

 								 if ($item_kit_info->{"custom_field_${k}_value"})
 								 {
 								 	echo anchor('item_kits/download/'.$item_kit_info->{"custom_field_${k}_value"},$this->Appfile->get_file_info($item_kit_info->{"custom_field_${k}_value"})->file_name,array('target' => '_blank'));
 								 	echo "<div class='delete-custom-image'><a href='".site_url('item_kits/delete_custom_field_value/'.$item_kit_info->item_kit_id.'/'.$k)."'>".lang('common_delete')."</a></div>";
 								 }
							 		
 							 	} 
								else 
								{
							
									echo form_input(array(
									'name'=>"custom_field_${k}_value",
									'id'=>"custom_field_${k}_value",
									'class'=>"custom_field_${k}_value".' form-control',
									'value'=>$item_kit_info->{"custom_field_${k}_value"},
									($required ? $required_text : $required_text) => ($required ? $required_text : $required_text)
									)
									);?>									
							<?php } ?>
						</div>
					</div>
				<?php } //end if?>
				<?php } //end for loop?>

			<?php echo form_hidden('redirect', isset($redirect) ? $redirect : ''); ?>
			<?php echo form_hidden('progression', isset($progression) ? $progression : ''); ?>
			<?php echo form_hidden('quick_edit', isset($quick_edit) ? $quick_edit : ''); ?>
							
							
							<div class="col-md-12">

										<div class="panel panel-piluku">
											<div class="panel-heading">
									      <h3 class="panel-title"><i class="ion-android-list"></i> <?php echo lang("common_modifiers"); ?></h3>
					
											</div>	
											<div class="panel-body">
					
												<div class="form-group no-padding-right">	
							
														<?php
														foreach($this->Item_modifier->get_all()->result_array() as $modifier)
														{
														?>
														<?php echo form_label($modifier['name'].':', '',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label wide')); ?>
														<div class="col-sm-9 col-md-9 col-lg-10">
														<?php echo form_checkbox(array(
															'name'=>'modifiers[]',
															'id'=>'modifier_'.$modifier['id'],
															'class' => 'modifier',
															'value'=>$modifier['id'],
															'checked'=>(boolean)(($this->Item_modifier->item_kit_has_modifier($item_kit_info->item_kit_id,$modifier['id'])))));
														?>
														<label for="modifier_<?php echo $modifier['id']; ?>"><span></span></label>
													</div>
							
													<?php } ?>
												</div>
													</div>
												</div>

							</div>
				
				
				
				
				
				
				
				
				
				
							
			<div class="form-actions pull-right">
				<?php
				echo form_submit(array(
					'name'=>'submit',
					'id'=>'submit',
					'value'=>lang('common_save'),
					'class'=>'submit_button floating-button btn btn-lg btn-primary')
				);
				?>
			</div>

	
			</div>
		</div>
	</div>
</div>
<?php echo form_close(); ?>
</div>

<script id="secondary-category-template" type="text/x-handlebars-template">

	<div class="form-group">
		<?php echo form_label(lang('common_secondary_category').':', 'secondary_category_id_{{index}}',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label  wide')); ?>
		<div class="col-sm-9 col-md-9 col-lg-10">
			<?php echo form_dropdown('secondary_categories[{{index}}]', $categories,'', 'class="form-control form-inps" id="secondary_category_id_{{index}}"');?>
		</div>
	</div>
</script>

<script type='text/javascript'>
<?php $this->load->view("partial/common_js"); ?>

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

function get_taxes()
{
	var taxes = [];
	
	if (!$("#override_default_tax").prop('checked'))
	{
		var default_taxes = <?php echo json_encode($this->Item_kit_taxes_finder->get_info($item_kit_info->item_kit_id)) ?>;
	
		for(var k = 0;k<default_taxes.length;k++)
		{
			taxes.push({'percent': parseFloat(default_taxes[k]['percent']), 'cumulative':default_taxes[k]['cumulative'] == 1});
		}	
	}
	else
	{
		var k=0;
		
		$('.tax-container.main input[name="tax_percents[]"]').each(function()
		{
			if ($(this).val())
			{
				taxes.push({'percent': parseFloat($(this).val()), 'cumulative': k==1 && $("#tax_cumulatives").prop('checked')});
			}
			
			k++;
		});	
	}
	return taxes;
	
}

function get_total_tax_percent()
{
	var total_tax_percent = 0;
	var taxes = get_taxes();
	for(var k = 0;k<taxes.length;k++)
	{
		total_tax_percent += parseFloat(taxes[k]['percent']);
	}
	
	return total_tax_percent;
}

function are_taxes_cumulative()
{
	var taxes = get_taxes();
	
	return (taxes.length == 2 && taxes[1].cumulative);
}

function calculate_markup_percent()
{
	if ($("#tax_included").prop('checked') )
	{
		var cost_price = parseFloat($('#cost_price').val());
		var unit_price = parseFloat($('#unit_price').val());

		var cumulative = are_taxes_cumulative();
		
		if (!cumulative)
		{
			//Margin amount
			//(100*.1)
			//100 + (100*.1) = 118.80 * .08 
	
			//cost price 100.00
			//8% tax
			//margin 10%
			//110.00 before tax
			//selling price 118.80
			//100 * 1.1 = profit 10%	
	
	
			// X = COST PRICE
			// Y = MARGIN PERCENT
			// Z = SELLING PRICE
			// Q = TAX PERCENT
			//100 * (1+ (10/100)) = 118.80 - (100 * (1+ (10/100)) * 8/100);
	
			//X * (1+Y/100) = Z - (X * (1+(Y/100)) * Q/100)
			//Y = -(100 ((Q+100) X-100 Z))/((Q+100) X) and (Q+100) X!=0

			var tax_percent = parseFloat(get_total_tax_percent());
		
			var Z = unit_price;
			var X = cost_price;
			var Q = tax_percent;
			var markup_percent = -(100*((Q+100)*X-100*Z))/((Q+100)*X);
		}
		else
		{
			var taxes = get_taxes();
			var tax_1 = 1+(taxes[0]['percent']/100);
			var tax_2 = 1+(taxes[1]['percent']/100);
			markup_percent = (unit_price / (cost_price * tax_1 * tax_2) - 1) * 100;
		}

	}
	else
	{
		var cost_price = parseFloat($('#cost_price').val());
		var unit_price = parseFloat($('#unit_price').val());
		var markup_percent =  -100 + (100*(unit_price/cost_price));
	}

	markup_percent = parseFloat(Math.round(markup_percent * 100) / 100).toFixed(<?php echo json_encode($decimals); ?>);
	
	$('#margin').val(markup_percent + '%');
}

function calculate_markup_price()
{		
	if ($("#tax_included").prop('checked') )
	{		
		var cost_price = parseFloat($('#cost_price').val());
		var markup_percent = parseFloat($("#margin").val());
		
		var cumulative = are_taxes_cumulative();
		
		if (!cumulative)
		{
			//Margin amount
			//(100*.1)
			//100 + (100*.1) = 118.80 * .08 
	
			//cost price 100.00
			//8% tax
			//margin 10%
			//110.00 before tax
			//selling price 118.80
			//100 * 1.1 = profit 10%	
	
	
			// X = COST PRICE
			// Y = MARGIN PERCENT
			// Z = SELLING PRICE
			// Q = TAX PERCENT
			//100 * (1+ (10/100)) = 118.80 - (100 * (1+ (10/100)) * 8/100);
	
			//X * (1+Y/100) = Z - (X * (1+(Y/100)) * Q/100)
			//Z = (Q X Y+100 Q X+100 X Y+10000 X)/10000
			
			var tax_percent = get_total_tax_percent();
				
			var X = cost_price;
			var Y = markup_percent;
			var Q = tax_percent;
		
			var markup_price = (Q*X*Y+100*Q*X+100*X*Y+10000*X)/10000;		
		}
		else
		{
			var marked_up_price_before_tax = cost_price * (1+(markup_percent/100));
			
			var taxes = get_taxes();
			var cumulative_tax_percent = taxes[1]['percent'];
			
			var first_tax = (marked_up_price_before_tax*(taxes[0]['percent']/100));
			var second_tax = (marked_up_price_before_tax + first_tax) *(taxes[1]['percent']/100);
			var markup_price = marked_up_price_before_tax + first_tax + second_tax;
		}
		
		markup_price = parseFloat(Math.round(markup_price * 100) / 100).toFixed(<?php echo json_encode($decimals); ?>);
	}
	else
	{
		var cost_price = parseFloat($('#cost_price').val());
		var markup_percent = parseFloat($("#margin").val());

		var markup_price = cost_price + (cost_price / 100 * (markup_percent));
		markup_price = parseFloat(Math.round(markup_price * 100) / 100).toFixed(<?php echo json_encode($decimals); ?>);
		
	}

	$('#unit_price').val(markup_price);
}

setTimeout(function(){$(":input:visible:first","#item_kit_form").focus();},100);

//validation and submit handling
$(document).ready(function()
{
	$('#category_id').selectize({
		create: true,
		render: {
	    item: function(item, escape) {
				var item = '<div class="item">'+ escape($('<div>').html(item.text).text()) +'</div>';
				return item;
	    },
	    option: function(item, escape) {
				var option = '<div class="option">'+ escape($('<div>').html(item.text).text()) +'</div>';
				return option;
	    },
      option_create: function(data, escape) {
			var add_new = <?php echo json_encode(lang('common_new_category')) ?>;
        return '<div class="create">'+escape(add_new)+' <strong>' + escape(data.input) + '</strong></div>';
      }
		}
	});
		
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
				url:'<?php echo site_url("item_kits/tags");?>'+'?term='+encodeURIComponent(query),
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
	
	$('#item_kit_form').validate({
		ignore: ':hidden:not([class~=selectized]),:hidden > .selectized, .selectize-control .selectize-input input',
		submitHandler:function(form)
		{
			var args = {
				next: {
					label: <?php echo json_encode(lang('common_edit').' '.lang('common_items')) ?>,
					url: <?php echo json_encode(site_url("item_kits/items/".($item_kit_info->item_kit_id ? $item_kit_info->item_kit_id : -1)."?$query")); ?>,
				}
			};
			
			$.post('<?php echo site_url("item_kits/check_duplicate");?>', {term: $('#name').val()},function(data) {
			<?php if(!$item_kit_info->item_kit_id) { ?>
			if(data.duplicate)
			{
				bootbox.confirm(<?php echo json_encode(lang('common_items_duplicate_exists'));?>, function(result)
				{
					if(result)
					{
						doItemSubmit(form, args);
					}
				});
			}
			else
			{
				doItemSubmit(form, args);
			}
			<?php } else { ?>
				doItemSubmit(form, args);
			<?php } ?>
			} , "json");
		},
	errorClass: "text-danger",
	errorElement: "span",
	highlight:function(element, errorClass, validClass) {
		$(element).parents('.form-group').removeClass('has-success').addClass('has-error');
	},
	unhighlight: function(element, errorClass, validClass) {
		$(element).parents('.form-group').removeClass('has-error').addClass('has-success');
	},
		rules:
			{
				name:"required",
				category_id:"required",
				<?php for($k=1;$k<=NUMBER_OF_PEOPLE_CUSTOM_FIELDS;$k++) { 
				$custom_field = $this->Item_kit->get_custom_field($k);
				if($custom_field !== FALSE) {
					if( $this->Item_kit->get_custom_field($k,'required') && in_array($current_location, $this->Item_kit->get_custom_field($k,'locations'))){
						if(($this->Item_kit->get_custom_field($k,'type') == 'file' || $this->Item_kit->get_custom_field($k,'type') == 'image') && !$item_kit_info->{"custom_field_${k}_value"}){
							echo "custom_field_${k}_value: 'required',\n";
						}
						
						if(($this->Item_kit->get_custom_field($k,'type') != 'file' && $this->Item_kit->get_custom_field($k,'type') != 'image')){
							echo "custom_field_${k}_value: 'required',\n";
						}
					}
				}
			}
			?>
			},
			messages:
			{
				name:<?php echo json_encode(lang('common_item_name_required')); ?>,
				category_id:<?php echo json_encode(lang('common_category_required')); ?>,	
				<?php for($k=1;$k<=NUMBER_OF_PEOPLE_CUSTOM_FIELDS;$k++) { 
					$custom_field = $this->Item_kit->get_custom_field($k);
					if($custom_field !== FALSE) {
						if( $this->Item_kit->get_custom_field($k,'required') && in_array($current_location, $this->Item_kit->get_custom_field($k,'locations'))){
							if(($this->Item_kit->get_custom_field($k,'type') == 'file' || $this->Item_kit->get_custom_field($k,'type') == 'image') && !$item_kit_info->{"custom_field_${k}_value"}){
								$error_message = json_encode($custom_field." ".lang('is_required'));
								echo "custom_field_${k}_value: $error_message,\n";
							}

							if(($this->Item_kit->get_custom_field($k,'type') != 'file' && $this->Item_kit->get_custom_field($k,'type') != 'image')){
								$error_message = json_encode($custom_field." ".lang('is_required'));
								echo "custom_field_${k}_value: $error_message,\n";
							}
						}
					}
				}
				?>
			}
	});
});

$("#verify_age").click(function()
{
	if ($('#verify_age').prop('checked'))
	{
		$("#required_age_container").removeClass('hidden');	
	}
	else
	{
		$("#required_age_container").addClass('hidden');
	}
	
});


$("#categories_form").submit(function(event)
{
	event.preventDefault();

	$(this).ajaxSubmit({ 
		success: function(response, statusText, xhr, $form){
			show_feedback(response.success ? 'success' : 'error', response.message, response.success ? <?php echo json_encode(lang('common_success')); ?> : <?php echo json_encode(lang('common_error')); ?>);
			if(response.success)
			{
				$("#category-input-data").modal('hide');
				
				var category_id_selectize = $("#category_id")[0].selectize
				category_id_selectize.clearOptions();
				category_id_selectize.addOption(response.categories);		
				category_id_selectize.addItem(response.selected, true);
				
			}		
		},
		dataType:'json',
	});
});


var secondary_category_index = -1;
var secondary_category_template = Handlebars.compile(document.getElementById("secondary-category-template").innerHTML);

$(document).on('click', "#add_secondary_category",function()
{
	$("#add_secondary_category").parent().parent().before(secondary_category_template({index: secondary_category_index}));
	secondary_category_index -= 1;
});

$(document).on('click', '.delete_secondary_category', function(e) {
	var index = $(this).data('index');
	$(this).parent().parent().parent().remove();
	
	if(index > 0)
	{
		$("#item_kit_form").append('<input type="hidden" class="secondary_categories_to_delete" name="secondary_categories_to_delete[]" value="'+ index +'" />');
	}
});


$(document).on('click', "#add_category",function()
{
	$("#categoryModalDialogTitle").html(<?php echo json_encode(lang('common_add_category')); ?>);
	var parent_id = $("#category_id").val();
	
	$parent_id_select = $('#parent_id');
	$parent_id_select[0].selectize.setValue(parent_id, false);
	
	$("#categories_form").attr('action',SITE_URL+'/items/save_category');
	
	//Clear form
	$(":file").filestyle('clear');
	$("#categories_form").find('#category_name').val("");
	$("#categories_form").find('#category_color').val("");
	$('#category_color').colorpicker('setValue', '');
	$("#categories_form").find('#category_image').val("");
	$("#categories_form").find('#image-preview').attr('src','');
	$('#del_image').prop('checked',false);
	$('#preview-section').hide();
	
	//show
	$("#category-input-data").modal('show');
});

</script>
<?php $this->load->view("partial/footer"); ?>
