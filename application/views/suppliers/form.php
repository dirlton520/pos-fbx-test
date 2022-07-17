<?php $this->load->view("partial/header"); ?>

		
<div class="row">
	<div class="spinner" id="grid-loader" style="display:none">
	  <div class="rect1"></div>
	  <div class="rect2"></div>
	  <div class="rect3"></div>
	</div>
	<div class="col-md-12">

		<?php if($person_info->person_id)  { ?>
			<div class="panel">
				<div class="panel-body ">
					<div class="user-badge">
						<?php echo $person_info->image_id ? '<div class="user-badge-avatar">'.img(array('src' => app_file_url($person_info->image_id),'class'=>'img-polaroid img-polaroid-s')).'</div>' : '<div class="user-badge-avatar">'.img(array('src' => base_url('assets/assets/images/avatar-default.jpg'),'class'=>'img-polaroid','id'=>'image_empty')).'</div>'; ?>
						<div class="user-badge-details">
							<?php echo H($person_info->company_name); ?>
						<p><?php echo H($person_info->first_name.' '.$person_info->last_name); ?></p>
						</div>
						<ul class="list-inline pull-right">
							<?php
								$one_year_ago = date('Y-m-d', strtotime('-1 year'));
								$today = date('Y-m-d').'%2023:59:59';	
							?>
							<li><a target="_blank" href="<?php echo site_url('reports/generate/specific_supplier?report_type=complex&start_date='.$one_year_ago.'&start_date_formatted='.date(get_date_format().' '.get_time_format(), strtotime($one_year_ago)).'&end_date='.$today.'&end_date_formatted='.date(get_date_format().' '.get_time_format(), strtotime(date('Y-m-d').' 23:59:59')).'&supplier_id='.$person_info->person_id.'&receiving_type=all&export_excel=0'); ?>" class="btn btn-success"><?php echo lang('common_view_report'); ?></a></li>
							<?php if ($person_info->email) { ?>
								<li><a href="mailto:<?php echo H($person_info->email); ?>" class="btn btn-primary"><?php echo lang('common_send_email'); ?></a></li>
							<?php } ?>
						</ul>
					</div>
				</div>
			</div>
		<?php } ?>
	<?php echo form_open_multipart('suppliers/save/'.$person_info->person_id,array('id'=>'supplier_form','class'=>'form-horizontal')); ?>

			<div class="panel panel-piluku">
				<div class="panel-heading">
	                <h3 class="panel-title">
	                    <i class="ion-edit"></i> 
	                    <?php echo lang("suppliers_basic_information"); ?>
    					<small>(<?php echo lang('common_fields_required_message'); ?>)</small>
	                </h3>
		        </div>

			<div class="panel-body">
				<div class="form-group">
				
						<?php echo form_label(lang('suppliers_company_name').':', 'company_name', array('class'=>'required col-sm-3 col-md-3 col-lg-2 control-label')); ?>
						<div class="col-sm-9 col-md-9 col-lg-10 cmp-inps">
						<?php echo form_input(array(
							'class'=>'form-control form-inps',
							'name'=>'company_name',
							'id'=>'company_name',
							'value'=>$person_info->company_name)
							);?>
						</div>
					</div>
				
					<?php $this->load->view("people/form_basic_info"); ?>

					<div class="form-group">	
				<?php echo form_label(lang('common_internal_notes').':', 'internal_notes',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label ')); ?>
					<div class="col-sm-9 col-md-9 col-lg-10">
					<?php echo form_textarea(array(
						'name'=>'internal_notes',
						'id'=>'internal_notes',
						'class'=>'form-control text-area',
						'value'=>$person_info->internal_notes,
						'rows'=>'5',
						'cols'=>'17')		
					);?>
					</div>
				</div>

					<div class="form-group">
						<?php echo form_label(lang('suppliers_account_number').':', 'account_number', array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label ')); ?>
						<div class="col-sm-9 col-md-9 col-lg-10">
							<?php echo form_input(array(
								'class'=>'form-control form-inps',
								'name'=>'account_number',
								'id'=>'account_number',
								'value'=>$person_info->account_number)
							);?>
						</div>
					</div>
					
					<?php	
									
					if($this->config->item('suppliers_store_accounts') && $this->Employee->has_module_action_permission('suppliers', 'edit_store_account_balance', $this->Employee->get_logged_in_employee_info()->person_id)) 
					{
					?>
					<div class="form-group">	
						<?php echo form_label(lang('common_store_account_balance').':', 'balance',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label ')); ?>
						<div class="col-sm-9 col-md-9 col-lg-10">
							<?php echo form_input(array(
								'name'=>'balance',
								'id'=>'balance',
								'class'=>'form-control balance',
								'value'=>$person_info->balance ? to_currency_no_money($person_info->balance) : '0.00')
								);?>
							</div>
						</div>

					<?php
					}
					?>
					
					
					<?php if ($this->config->item('charge_tax_on_recv')) { ?>
					
					
							<div class="form-group override-taxes-container">
								<?php echo form_label(lang('supplier_override_default_tax_for_recv').':', '',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label wide')); ?>
								<div class="col-sm-9 col-md-9 col-lg-10">
									<?php echo form_checkbox(array(
										'name'=>'override_default_tax',
										'id'=>'override_default_tax',
										'class' => 'override_default_tax_checkbox delete-checkbox',
										'value'=>1,
										'checked'=>(boolean)$person_info->override_default_tax));
									?>
									<label for="override_default_tax"><span></span></label>
								</div>
							</div>

							<div class="tax-container main <?php if (!$person_info->override_default_tax){echo 'hidden';} ?>">
								
								<div class="form-group">	
									<?php echo form_label(lang('common_tax_class').': ', 'tax_class',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label')); ?>
									<div class="col-sm-9 col-md-9 col-lg-10">
									<?php echo form_dropdown('tax_class', $tax_classes, $person_info->tax_class_id, array('id' =>'tax_class', 'class' => 'form-control tax_class'));?>
									</div>
								</div>
					
								<div class="form-group">
									<h4 class="text-center"><?php echo lang('common_or') ?></h4>
								</div>
								
									
								<div class="form-group">
									<?php echo form_label(lang('common_tax_1').':', 'tax_percent_1',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label wide')); ?>
									<div class="col-sm-9 col-md-9 col-lg-10">
										<?php echo form_input(array(
											'name'=>'tax_names[]',
											'id'=>'tax_percent_1',
											'size'=>'8',
											'class'=>'form-control margin10 form-inps',
											'placeholder' => lang('common_tax_name'),
											'value'=> isset($supplier_tax_info[0]['name']) ? $supplier_tax_info[0]['name'] : ($this->Location->get_info_for_key('default_tax_1_name') ? $this->Location->get_info_for_key('default_tax_1_name') : $this->config->item('default_tax_1_name')))
										);?>
									</div>
				                    <label class="col-sm-3 col-md-3 col-lg-2 control-label wide" for="tax_percent_name_1">&nbsp;</label>
									<div class="col-sm-9 col-md-9 col-lg-10">
										<?php echo form_input(array(
											'name'=>'tax_percents[]',
											'id'=>'tax_percent_name_1',
											'size'=>'3',
											'class'=>'form-control form-inps-tax',
											'placeholder' => lang('common_tax_percent'),
											'value'=> isset($supplier_tax_info[0]['percent']) ? $supplier_tax_info[0]['percent'] : '')
										);?>
										<div class="tax-percent-icon">%</div>
										<div class="clear"></div>
										<?php echo form_hidden('tax_cumulatives[]', '0'); ?>
									</div>
								</div>

								<div class="form-group">
									<?php echo form_label(lang('common_tax_2').':', 'tax_percent_2',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label wide')); ?>
									<div class="col-sm-9 col-md-9 col-lg-10">
										<?php echo form_input(array(
											'name'=>'tax_names[]',
											'id'=>'tax_percent_2',
											'size'=>'8',
											'class'=>'form-control form-inps margin10',
											'placeholder' => lang('common_tax_name'),
											'value'=> isset($supplier_tax_info[1]['name']) ? $supplier_tax_info[1]['name'] : ($this->Location->get_info_for_key('default_tax_2_name') ? $this->Location->get_info_for_key('default_tax_2_name') : $this->config->item('default_tax_2_name')))
										);?>
									</div>
				                    <label class="col-sm-3 col-md-3 col-lg-2 control-label text-info wide">&nbsp;</label>
									<div class="col-sm-9 col-md-9 col-lg-10">
										<?php echo form_input(array(
											'name'=>'tax_percents[]',
											'id'=>'tax_percent_name_2',
											'size'=>'3',
											'class'=>'form-control form-inps-tax',
											'placeholder' => lang('common_tax_percent'),
											'value'=> isset($supplier_tax_info[1]['percent']) ? $supplier_tax_info[1]['percent'] : '')
										);?>
										<div class="tax-percent-icon">%</div>
										<div class="clear"></div>
										<?php echo form_checkbox('tax_cumulatives[]', '1', (isset($supplier_tax_info[1]['cumulative']) && $supplier_tax_info[1]['cumulative']) ? (boolean)$supplier_tax_info[1]['cumulative'] : (boolean)$this->config->item('default_tax_2_cumulative'), 'class="cumulative_checkbox" id="tax_cumulatives"'); ?>
										<label for="tax_cumulatives"><span></span></label>
									    <span class="cumulative_label">
											<?php echo lang('common_cumulative'); ?>
									    </span>
									</div>
								</div>
	                 
								<div class="col-sm-9 col-sm-offset-3 col-md-9 col-md-offset-3 col-lg-9 col-lg-offset-3"  style="visibility: <?php echo isset($supplier_tax_info[2]['name']) ? 'hidden' : 'visible';?>">
									<a href="javascript:void(0);" class="show_more_taxes"><?php echo lang('common_show_more');?> &raquo;</a>
								</div>
								<div class="more_taxes_container" style="display: <?php echo isset($supplier_tax_info[2]['name']) ? 'block' : 'none';?>">
									<div class="form-group">
										<?php echo form_label(lang('common_tax_3').':', 'tax_percent_3',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label wide')); ?>
										<div class="col-sm-9 col-md-9 col-lg-10">
											<?php echo form_input(array(
												'name'=>'tax_names[]',
												'id'=>'tax_percent_3',
												'size'=>'8',
												'class'=>'form-control form-inps margin10',
												'placeholder' => lang('common_tax_name'),
												'value'=> isset($supplier_tax_info[2]['name']) ? $supplier_tax_info[2]['name'] : ($this->Location->get_info_for_key('default_tax_3_name') ? $this->Location->get_info_for_key('default_tax_3_name') : $this->config->item('default_tax_3_name')))
											);?>
										</div>
				                        <label class="col-sm-3 col-md-3 col-lg-2 control-label wide">&nbsp;</label>
										<div class="col-sm-9 col-md-9 col-lg-10">
											<?php echo form_input(array(
												'name'=>'tax_percents[]',
												'id'=>'tax_percent_name_3',
												'size'=>'3',
												'class'=>'form-control form-inps-tax margin10',
												'placeholder' => lang('common_tax_percent'),
												'value'=> isset($supplier_tax_info[2]['percent']) ? $supplier_tax_info[2]['percent'] : '')
											);?>
										<div class="tax-percent-icon">%</div>
										<div class="clear"></div>
										<?php echo form_hidden('tax_cumulatives[]', '0'); ?>
										</div>
									</div>

									<div class="form-group">
									<?php echo form_label(lang('common_tax_4').':', 'tax_percent_4',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label wide')); ?>
										<div class="col-sm-9 col-md-9 col-lg-10">
										<?php echo form_input(array(
											'name'=>'tax_names[]',
											'id'=>'tax_percent_4',
											'size'=>'8',
											'class'=>'form-control  form-inps margin10',
											'placeholder' => lang('common_tax_name'),
											'value'=> isset($supplier_tax_info[3]['name']) ? $supplier_tax_info[3]['name'] : ($this->Location->get_info_for_key('default_tax_4_name') ? $this->Location->get_info_for_key('default_tax_4_name') : $this->config->item('default_tax_4_name')))
										);?>
										</div>
				                        <label class="col-sm-3 col-md-3 col-lg-2 control-label wide">&nbsp;</label>
										<div class="col-sm-9 col-md-9 col-lg-10">
										<?php echo form_input(array(
											'name'=>'tax_percents[]',
											'id'=>'tax_percent_name_4',
											'size'=>'3',
											'class'=>'form-control form-inps-tax', 
											'placeholder' => lang('common_tax_percent'),
											'value'=> isset($supplier_tax_info[3]['percent']) ? $supplier_tax_info[3]['percent'] : '')
										);?>
										<div class="tax-percent-icon">%</div>
										<div class="clear"></div>
										<?php echo form_hidden('tax_cumulatives[]', '0'); ?>
										</div>
									</div>
						
									<div class="form-group">
									<?php echo form_label(lang('common_tax_5').':', 'tax_percent_5',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label wide')); ?>
										<div class="col-sm-9 col-md-9 col-lg-10">
											<?php echo form_input(array(
												'name'=>'tax_names[]',
												'id'=>'tax_percent_5',
												'size'=>'8',
												'class'=>'form-control  form-inps margin10',
												'placeholder' => lang('common_tax_name'),
												'value'=> isset($supplier_tax_info[4]['name']) ? $supplier_tax_info[4]['name'] : ($this->Location->get_info_for_key('default_tax_5_name') ? $this->Location->get_info_for_key('default_tax_5_name') : $this->config->item('default_tax_5_name')))
											);?>
										</div>
				                        <label class="col-sm-3 col-md-3 col-lg-2 control-label wide">&nbsp;</label>
										<div class="col-sm-9 col-md-9 col-lg-10">
											<?php echo form_input(array(
												'name'=>'tax_percents[]',
												'id'=>'tax_percent_name_5',
												'size'=>'3',
												'class'=>'form-control form-inps-tax margin10',
												'placeholder' => lang('common_tax_percent'),
												'value'=> isset($supplier_tax_info[4]['percent']) ? $supplier_tax_info[4]['percent'] : '')
											);?>
										<div class="tax-percent-icon">%</div>
										<div class="clear"></div>
										<?php echo form_hidden('tax_cumulatives[]', '0'); ?>
										</div>
									</div>
								</div> <!--End more Taxes Container-->
				                <div class="clear"></div>
							</div>
					
					<?php } ?>
					
				 <?php for($k=1;$k<=NUMBER_OF_PEOPLE_CUSTOM_FIELDS;$k++) { ?>
					<?php
					 $custom_field = $this->Supplier->get_custom_field($k);
					 if($custom_field !== FALSE) { 
						$required = false;
						$required_text = '';
						if($this->Supplier->get_custom_field($k,'required') && in_array($current_location,$this->Supplier->get_custom_field($k,'locations'))){
							$required = true;
							$required_text = 'required';
						}
						 
						 ?>
						 <div class="form-group">
						 <?php echo form_label($custom_field . ' :', "custom_field_${k}_value", array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label '.$required_text)); ?>
						 							
						 <div class="col-sm-9 col-md-9 col-lg-10">
								<?php if ($this->Supplier->get_custom_field($k,'type') == 'checkbox') { ?>
									
									<?php echo form_checkbox("custom_field_${k}_value", '1', (boolean)$person_info->{"custom_field_${k}_value"},"id='custom_field_${k}_value'");?>
									<label for="<?php echo "custom_field_${k}_value"; ?>"><span></span></label>
									
								<?php } elseif($this->Supplier->get_custom_field($k,'type') == 'date') { ?>
									
										<?php echo form_input(array(
										'name'=>"custom_field_${k}_value",
										'id'=>"custom_field_${k}_value",
										'class'=>"custom_field_${k}_value".' form-control',
										'value'=>is_numeric($person_info->{"custom_field_${k}_value"}) ? date(get_date_format(), $person_info->{"custom_field_${k}_value"}) : '',
										($required ? $required_text : $required_text) => ($required ? $required_text : $required_text)
										)
										);?>									
										<script type="text/javascript">
											var $field = <?php echo "\$('#custom_field_${k}_value')"; ?>;
									    $field.datetimepicker({format: JS_DATE_FORMAT, locale: LOCALE, ignoreReadonly: IS_MOBILE ? true : false});	
											
										</script>
											
								<?php } elseif($this->Supplier->get_custom_field($k,'type') == 'dropdown') { ?>
										
										<?php 
										$choices = explode('|',$this->Supplier->get_custom_field($k,'choices'));
										$select_options = array('' => lang('common_please_select'));
										foreach($choices as $choice)
										{
											$select_options[$choice] = $choice;
										}
										echo form_dropdown("custom_field_${k}_value", $select_options, $person_info->{"custom_field_${k}_value"}, 'class="form-control" '.$required_text);?>
										
									<?php } elseif($this->Supplier->get_custom_field($k,'type') == 'image') {
										echo form_input(
											array(
												'name'=>"custom_field_${k}_value",
												'id'=>"custom_field_${k}_value",
												'type' => 'file',
												'class'=>"custom_field_${k}_value".' form-control',
												'accept'=>".png,.jpg,.jpeg,.gif"
											),
											NULL,
											$person_info->{"custom_field_${k}_value"} ? "" : $required_text
										);
							
										if ($person_info->{"custom_field_${k}_value"})
										{
											echo "<img width='30%' src='".app_file_url($person_info->{"custom_field_${k}_value"})."' />";
											echo "<div class='delete-custom-image'><a href='".site_url('suppliers/delete_custom_field_value/'.$person_info->person_id.'/'.$k)."'>".lang('common_delete')."</a></div>";
										}
									 ?>
									<?php 
								}
 							 elseif($this->Supplier->get_custom_field($k,'type') == 'file')
 							 {
								echo form_input(
									array(
									  'name'=>"custom_field_${k}_value",
									  'id'=>"custom_field_${k}_value",
									  'type' => 'file',
									  'class'=>"custom_field_${k}_value".' form-control'
									),
								  NULL,
								  $person_info->{"custom_field_${k}_value"} ? "" : $required_text
								  );

 								 if ($person_info->{"custom_field_${k}_value"})
 								 {
 								 	echo anchor('suppliers/download/'.$person_info->{"custom_field_${k}_value"},$this->Appfile->get_file_info($person_info->{"custom_field_${k}_value"})->file_name,array('target' => '_blank'));
 								 	echo "<div class='delete-custom-image'><a href='".site_url('suppliers/delete_custom_field_value/'.$person_info->person_id.'/'.$k)."'>".lang('common_delete')."</a></div>";
 								 }
						 		
 							 } 
								else 
								{
								
										echo form_input(array(
										'name'=>"custom_field_${k}_value",
										'id'=>"custom_field_${k}_value",
										'class'=>"custom_field_${k}_value".' form-control',
										'value'=>$person_info->{"custom_field_${k}_value"},
										($required ? $required_text : $required_text) => ($required ? $required_text : $required_text)
										)
										);?>									
								<?php } ?>
							</div>
						</div>
					<?php } //end if?>
					<?php } //end for loop?>
					<div class="panel panel-piluku">
						<div class="panel-heading">
			                <h3 class="panel-title">
			                    <i class="ion-folder"></i> 
			                    <?php echo lang("common_files"); ?>
			                </h3>
				        </div>
		
						<?php if (count($files)) {?>
									<ul class="list-group">
								<?php foreach($files as $file){?>
						  	<li class="list-group-item permission-action-item">
									<?php echo anchor($controller_name.'/delete_file/'.$file->file_id,'<i class="icon ion-android-cancel text-danger" style="font-size: 120%"></i>', array('class' => 'delete_file'));?>	
									<?php echo anchor($controller_name.'/download/'.$file->file_id,$file->file_name,array('target' => '_blank'));?>
								</li>
								<?php } ?>
							</ul>
						<?php } ?>
						<h4 style="padding: 20px;"><?php echo lang('common_add_files');?></h4>
						<?php for($k=1;$k<=5;$k++) { ?>
						<div class="form-group"  style="padding-left: 10px;">
				    	<?php echo form_label(lang('common_file').' '.$k.':', 'files_'.$k,array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label ')); ?>
							<div class="col-sm-9 col-md-9 col-lg-10">
				      	<div class="file-upload">
				        	<input type="file" name="files[]" id="files_<?php echo $k; ?>" >
				         </div>
				      </div>
						</div>
						<?php } ?>
						</div>

					<?php echo form_hidden('redirect', $redirect); ?>

					<div class="form-actions pull-right">

						<?php
						if ($redirect == 1)
						{
							echo form_button(array(
								'name' => 'cancel',
								'id' => 'cancel',
								'class' => 'btn btn-danger',
								'value' => 'true',
								'content' => lang('common_cancel')
								));

						}
						?>

						<?php
						echo form_submit(array(
							'name'=>'submitf',
							'id'=>'submitf',
							'value'=>lang('common_save'),
							'class'=>'btn btn-primary btn-lg submit_button floating-button btn-large')
						);
						?>

					</div>
			</div>
		</div>
			<?php  echo form_close(); ?>
	</div>
</div>
</div>

<script type='text/javascript'>
//validation and submit handling
$(document).ready(function()
{
	$(".override_default_tax_checkbox").change(function()
	{
		$(this).parent().parent().next().toggleClass('hidden')
	});
	
	$("#cancel").click(cancelAddSupplier);
	
	setTimeout(function(){$(":input:visible:first","#supplier_form").focus();},100);
	var submitting = false;
	$('#image_id').imagePreview({ selector : '#avatar' }); // Custom preview container
	
	$('#supplier_form').validate({
		submitHandler:function(form)
		{
$('#grid-loader').show();
			if (submitting) return;
			submitting = true;
			$(form).ajaxSubmit({
				success:function(response)
				{
$('#grid-loader').hide();
					submitting = false;					
					show_feedback(response.success ? 'success' : 'error',response.message,response.success ? <?php echo json_encode(lang('common_success')); ?> : <?php echo json_encode(lang('common_error')); ?>);
					
					if(response.redirect==1 && response.success)
					{ 
						$.post('<?php echo site_url("receivings/select_supplier");?>', {supplier: response.person_id}, function()
						{
							window.location.href = '<?php echo site_url('receivings'); ?>';
						});					
					}
					if(response.redirect==2 && response.success)
					{ 
						window.location.href = '<?php echo site_url('suppliers'); ?>';
					}
					else
					{
						$("html, body").animate({ scrollTop: 0 }, "slow");
						$(".form-group").removeClass('has-success has-error');
					}

				},

				<?php if(!$person_info->person_id) { ?>
					resetForm: true,
					<?php } ?>
					dataType:'json'
				});

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
			<?php if(!$person_info->person_id) { ?>
				account_number:
				{
					remote: 
					{ 
						url: "<?php echo site_url('suppliers/account_number_exists');?>", 
						type: "post"

					} 
				},
				<?php } ?>
				company_name: "required",
				<?php for($k=1;$k<=NUMBER_OF_PEOPLE_CUSTOM_FIELDS;$k++) { 
				$custom_field = $this->Supplier->get_custom_field($k);
				if($custom_field !== FALSE) {
					if( $this->Supplier->get_custom_field($k,'required') && in_array($current_location, $this->Supplier->get_custom_field($k,'locations'))){
						if(($this->Supplier->get_custom_field($k,'type') == 'file' || $this->Supplier->get_custom_field($k,'type') == 'image') && !$person_info->{"custom_field_${k}_value"}){
							echo "custom_field_${k}_value: 'required',\n";
						}
						
						if(($this->Supplier->get_custom_field($k,'type') != 'file' && $this->Supplier->get_custom_field($k,'type') != 'image')){
							echo "custom_field_${k}_value: 'required',\n";
						}
					}
				}
			}
			?>
			},
			messages: 
			{
				<?php if(!$person_info->person_id) { ?>
					account_number:
					{
						remote: <?php echo json_encode(lang('common_account_number_exists')); ?>
					},
					<?php } ?>
					company_name: <?php echo json_encode(lang('suppliers_company_name_required')); ?>,
					last_name: <?php echo json_encode(lang('common_last_name_required')); ?>,
					<?php for($k=1;$k<=NUMBER_OF_PEOPLE_CUSTOM_FIELDS;$k++) { 
					$custom_field = $this->Supplier->get_custom_field($k);
					if($custom_field !== FALSE) {
						if( $this->Supplier->get_custom_field($k,'required') && in_array($current_location, $this->Supplier->get_custom_field($k,'locations'))){
							if(($this->Supplier->get_custom_field($k,'type') == 'file' || $this->Supplier->get_custom_field($k,'type') == 'image') && !$person_info->{"custom_field_${k}_value"}){
								$error_message = json_encode($custom_field." ".lang('is_required'));
								echo "custom_field_${k}_value: $error_message,\n";
							}

							if(($this->Supplier->get_custom_field($k,'type') != 'file' && $this->Supplier->get_custom_field($k,'type') != 'image')){
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

function cancelAddSupplier()
{	
	bootbox.confirm(<?php echo json_encode(lang('suppliers_are_you_sure_cancel')); ?>,function(result)
	{
		if (result)
		{
			window.location = <?php echo json_encode(site_url('receivings')); ?>;
		}
	});
	
}
$('.delete_file').click(function(e)
{
	e.preventDefault();
	var $link = $(this);
	bootbox.confirm(<?php echo json_encode(lang('common_confirm_file_delete')); ?>, function(response)
	{
		if (response)
		{
			$.get($link.attr('href'), function()
			{
				$link.parent().fadeOut();
			});
		}
	});
	
});

</script>
<?php $this->load->view('partial/footer')?>