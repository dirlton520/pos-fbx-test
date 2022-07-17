<?php $this->load->view("partial/header"); ?>


<?php if (isset($needs_auth) && $needs_auth) {?>
	<?php echo form_open('locations/check_auth',array('id'=>'location_form_auth','class'=>'form-horizontal')); ?>

	<div class="row">
		<div class="col-md-12">
			<div class="panel">
				<div class="panel-body">
					<h3 style="margin-left: 80px;"><a href="http://phppointofsale.com/buy_additional.php" target="_blank"><?php echo lang('locations_purchase_additional_licenses'); ?> &raquo;</a></h3>
					<?php if (validation_errors()) {?>
				        <div class="alert alert-danger">
				            <strong><?php echo lang('common_error'); ?></strong>
				            <?php echo validation_errors(); ?>
				        </div>
			        <?php } ?>
					<div class="form-group">
						<?php echo form_label(lang('locations_purchase_email').':', 'name',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label')); ?>
						<div class="col-sm-9 col-md-9 col-lg-10">
							<?php echo form_input(array(
								'class'=>'form-control form-inps',
								'name'=>'purchase_email',
								'id'=>'purchase_email')
							);?>
						</div>	
					</div>
					<div class="form-actions pull-right">
						<?php
						echo form_submit(array(
							'name'=>'submitf',
							'id'=>'submitf',
							'value'=>lang('common_save'),
							'class'=>'submit_button floating-button btn btn-lg btn-primary')
						);
						?>
					</div>
				</div>
			</div>
		</div>
	</div>
	
	<?php form_close(); ?>
<?php } else {?>

	<?php echo form_open_multipart('locations/save/'.$location_info->location_id,array('id'=>'location_form','class'=>'form-horizontal','autocomplete'=> 'off')); ?>
		<div class="row" id="form">
			
			<div class="col-md-12">				
				<div class="panel panel-piluku">
					<div class="panel-heading">
		                <h3 class="panel-title">
		                    <i class="ion-edit"></i> 
		                    <?php echo lang("locations_basic_information"); ?>
	    					<small>(<?php echo lang('common_fields_required_message'); ?>)</small>
		                </h3>
			        </div>

					<div class="panel-body">

						<div class="form-group">
							<?php echo form_label(lang('locations_name').':', 'name',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label required')); ?>
							<div class="col-sm-9 col-md-9 col-lg-10">
								<?php echo form_input(array(
									'class'=>'form-control form-inps',
									'name'=>'name',
									'id'=>'name',
									'value'=>$location_info->name)
								);?>
							</div>
						</div>

						<div class="form-group">
							<?php echo form_label(lang('locations_color').':', 'name',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label')); ?>
							<div class="col-sm-9 col-md-9 col-lg-10">
								<?php echo form_input(array(
									'class'=>'form-control form-inps',
									'name'=>'color',
									'id'=>'color',
									'value'=>$location_info->color)
								);?>
							</div>
						</div>
						
					<div class="form-group">	
						<?php echo form_label(lang('common_company').':', 'company',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label ')); ?>
						<div class="col-sm-9 col-md-9 col-lg-10 input-field">
							<?php echo form_input(array(
								'class'=>'validate form-control form-inps',
							'name'=>'company',
							'id'=>'company',
							'value'=>$location_info->company));?>
						</div>
					</div>
					
					<div class="form-group">	
						<?php echo form_label(lang('common_tax_id').':', 'tax_id',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label ')); ?>
						<div class="col-sm-9 col-md-9 col-lg-10 input-field">
							<?php echo form_input(array(
								'class'=>'validate form-control form-inps',
							'name'=>'tax_id',
							'id'=>'tax_id',
							'value'=>$location_info->tax_id));?>
						</div>
					</div>
					
					
					<div class="form-group">	
						<?php echo form_label(lang('common_company_logo').':', 'company_logo',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label ')); ?>
						<div class="col-sm-9 col-md-9 col-lg-10">
							
							<input type="file" name="company_logo" id="company_logo" class="filestyle" data-icon="false">  	
						</div>	
					</div>
					<div class="form-group">	
						<?php echo form_label(lang('common_delete_logo').':', 'delete_logo',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label ')); ?>
						<div class="col-sm-9 col-md-9 col-lg-10">
							<?php echo form_checkbox('delete_logo', '1', null,'id="delete_logo"');?>
							<label for="delete_logo"><span></span></label>
						</div>	
					</div>
					<div class="form-group">	
						<?php echo form_label(lang('common_website').':', 'website',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label ')); ?>
						<div class="col-sm-9 col-md-9 col-lg-10 input-field">
						<?php echo form_input(array(
							'class'=>'form-control form-inps',
							'name'=>'website',
							'id'=>'website',
							'value'=>$location_info->website));?>
						</div>
					</div>

						<div class="form-group">
							<?php echo form_label(lang('locations_address').':', 'address',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label required')); ?>
							<div class="col-sm-9 col-md-9 col-lg-10">
								
								<?php echo form_textarea(array(
									'name'=>'address',
									'id'=>'address',
									'class'=>'form-control text-area',
									'rows'=>'4',
									'cols'=>'30',
									'value'=>$location_info->address));?>								
							</div>
						</div>

						<div class="form-group">
							<?php echo form_label(lang('locations_phone').':', 'phone',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label required')); ?>
							<div class="col-sm-9 col-md-9 col-lg-10">
								<?php echo form_input(array(
									'class'=>'form-control form-inps',
									'name'=>'phone',
									'id'=>'phone',
									'value'=>$location_info->phone)
								);?>
							</div>
						</div>
					
						<div class="form-group">
							<?php echo form_label(lang('locations_fax').':', 'fax',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label')); ?>
							<div class="col-sm-9 col-md-9 col-lg-10">
								<?php echo form_input(array(
									'class'=>'form-control form-inps',
									'name'=>'fax',
									'id'=>'fax',
									'value'=>$location_info->fax)
								);?>
							</div>
						</div>

						<div class="form-group">
							<?php echo form_label(lang('locations_email').':', 'email',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label')); ?>
							<div class="col-sm-9 col-md-9 col-lg-10">
								<?php echo form_input(array(
									'type'=>'text',
									'class'=>'form-control form-inps',
									'name'=>'email',
									'id'=>'email',
									'value'=>$location_info->email)
								);?>
							</div>
						</div>
						
						<div class="form-group">
							<?php echo form_label(lang('locations_cc_email').':', 'cc_email',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label')); ?>
							<div class="col-sm-9 col-md-9 col-lg-10">
								<?php echo form_input(array(
									'type'=>'text',
									'class'=>'form-control form-inps',
									'name'=>'cc_email',
									'id'=>'cc_email',
									'value'=>$location_info->cc_email)
								);?>
							</div>
						</div>
						
						<div class="form-group">
							<?php echo form_label(lang('locations_bcc_email').':', 'bcc_email',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label')); ?>
							<div class="col-sm-9 col-md-9 col-lg-10">
								<?php echo form_input(array(
									'type'=>'text',
									'class'=>'form-control form-inps',
									'name'=>'bcc_email',
									'id'=>'bcc_email',
									'value'=>$location_info->bcc_email)
								);?>
							</div>
						</div>
						
						<div class="form-group">
							<?php echo form_label(lang('locations_email_sales_email').':', 'email_sales_email',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label')); ?>
							<div class="col-sm-9 col-md-9 col-lg-10">
								<?php echo form_input(array(
									'type'=>'text',
									'class'=>'form-control form-inps',
									'name'=>'email_sales_email',
									'id'=>'email_sales_email',
									'value'=>$location_info->email_sales_email)
								);?>
							</div>
						</div>
						
						
						<div class="form-group">
							<?php echo form_label(lang('locations_email_receivings_email').':', 'email_receivings_email',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label')); ?>
							<div class="col-sm-9 col-md-9 col-lg-10">
								<?php echo form_input(array(
									'type'=>'text',
									'class'=>'form-control form-inps',
									'name'=>'email_receivings_email',
									'id'=>'email_receivings_email',
									'value'=>$location_info->email_receivings_email)
								);?>
							</div>
						</div>
						
						
						<div class="form-group">	
						<?php echo form_label(lang('common_return_policy').':', 'return_policy',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label')); ?>
							<div class="col-sm-9 col-md-9 col-lg-10">
							<?php echo form_textarea(array(
								'name'=>'return_policy',
								'id'=>'return_policy',
								'class'=>'form-control text-area',
								'rows'=>'4',
								'cols'=>'30',
								'value'=>$location_info->return_policy));?>
							</div>
						</div>
						
						
						<div class="form-group">
							<?php echo form_label(lang('reports_employees').':', 'employees',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label')); ?>
							<div class="col-sm-9 col-md-9 col-lg-10">
								<select class="form-control" name="employees[]" id="employees" multiple>
									<?php  
										foreach($employees as $person_id => $employee)
										{
											$selected = ($employee['has_access'] == true) ? 'selected' : '';
											echo '<option value="'.$person_id.'" '.$selected.'> '.H($employee['name']).'</option>';
										}
									?>
								</select>		
							</div>
						</div>						
						
						<div class="form-group add-register-table">	
							<div class="spinner" id="grid-loader" style="display:none">
							  <div class="rect1"></div>
							  <div class="rect2"></div>
							  <div class="rect3"></div>
							</div>
							
							<?php echo form_label(lang('locations_registers').':', null,array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label ')); ?>
							<div class="table-responsive m-lr-15">
								<table id="price_registers" class="table">
									<thead>
										<tr>
										<th><?php echo lang('common_register_name'); ?></th>
										<th class="card_connect_hsn register-cc-field card_connect_info"><?php echo lang('locations_card_connect_hsn'); ?></th>
										<th class="emv_terminal_id register-cc-field"><?php echo lang('locations_terminal_id'); ?></th>
										<th class="register-cc-field register-cc-field-datacap"><?php echo lang('locations_ip_tran_device_id'); ?></th>
										<th class="register-cc-field register-cc-field-datacap"><?php echo lang('locations_pinpad_ip'); ?></th>
										<th class="register-cc-field register-cc-field-datacap"><?php echo lang('locations_pinpad_port'); ?></th>
										<th class="register-cc-field register-cc-field-datacap"><?php echo lang('locations_card_reader_actions'); ?></th>
										<th><?php echo lang('common_delete'); ?></th>
										</tr>
									</thead>
									
									<tbody>
									<?php 
									$counter = 0;
									foreach($registers->result() as $register) { ?>
										<tr><td><span class="dot terminal"></span><input type="text" class="form-control next-to-status" name="registers_to_edit[<?php echo $register->register_id; ?>][name]" value="<?php echo H($register->name); ?>" /></td>
											
											<td class="card_connect_info"><input id="card_connect_hsn_<?php echo $counter;?>" type="text" class="form-control card_connect_hsn register-cc-field" name="registers_to_edit[<?php echo $register->register_id; ?>][card_connect_hsn]" value="<?php echo H($register->card_connect_hsn); ?>" /></td>											
											<td class="emv_terminal_id register-cc-field"><input id="terminal_id_<?php echo $counter;?>" type="text" class="form-control emv_terminal_id register-cc-field" name="registers_to_edit[<?php echo $register->register_id; ?>][emv_terminal_id]" value="<?php echo H($register->emv_terminal_id); ?>" /></td>											
											<td><input type="text" class="form-control iptran register-cc-field register-cc-field-datacap" name="registers_to_edit[<?php echo $register->register_id; ?>][iptran_device_id]" value="<?php echo H($register->iptran_device_id); ?>" /></td>
											<td><input id="pinpad_ip_<?php echo $counter;?>" type="text" class="form-control emv_pinpad_ip register-cc-field register-cc-field-datacap" name="registers_to_edit[<?php echo $register->register_id; ?>][emv_pinpad_ip]" value="<?php echo H($register->emv_pinpad_ip); ?>" /></td>											
											<td><input id="pinpad_port_<?php echo $counter;?>" type="text" class="form-control emv_pinpad_port register-cc-field register-cc-field-datacap" name="registers_to_edit[<?php echo $register->register_id; ?>][emv_pinpad_port]" value="<?php echo H($register->emv_pinpad_port); ?>" /></td>											
											
											
											<td>
												<a class="update_parameters_ip_tran register-cc-field register-cc-field-datacap" href="javascript:void(0);"><?php echo lang('locations_update_params_ip_tran'); ?></a><span class="register-cc-field register-cc-field-datacap"> / </span>
												<a class="init_ip_tran register-cc-field register-cc-field-datacap" href="javascript:void(0);"><?php echo lang('locations_init_mercury_emv'); ?></a> 
												<?php if ((!defined("ENVIRONMENT") or ENVIRONMENT == 'development')) { ?>
												<span class="register-cc-field register-cc-field-datacap"> / </span><a class="test_mode_ip_tran register-cc-field register-cc-field-datacap" href="javascript:void(0);"><?php echo lang('common_test_mode'); ?></a> 													
												<?php } ?>
											</td>
											<td>
										<a class="delete_register" href="javascript:void(0);" data-register-id='<?php echo $register->register_id; ?>'><?php echo lang('common_delete'); ?></a>
									</td></tr>
									<?php 
									$counter++;
								} 
								?>
									</tbody>
								</table>
								<a href="javascript:void(0);" id="add_register"><?php echo lang('locations_add_register'); ?></a>
							</div>
						</div>
						<script>
						
						
						$(document).ready(refresh_terminal_status_if_needed);
						$(document).on("change", '#blockchyp_api_key,#blockchyp_bearer_token,#blockchyp_signing_key,.emv_terminal_id,.emv_terminal_id input', refresh_terminal_status_if_needed);
					
						function refresh_terminal_status_if_needed()
						{
							var cc_processor = $("#credit_card_processor").val();
				
							if (cc_processor == 'coreclear2' && $('#enable_credit_card_processing').prop('checked'))
							{
								refresh_terminal_status();
							}
						
						}
						function refresh_terminal_status()
						{
							$('.dot.terminal').each(async function(index, value)
							{
								var $dot_terminal = $(value);
								var terminal_name = $(value).parent().parent().find('.emv_terminal_id input').val();
							
								var blockchyp_api_key = $('#blockchyp_api_key').val();
								var blockchyp_bearer_token = $('#blockchyp_bearer_token').val();
								var blockchyp_signing_key = $('#blockchyp_signing_key').val();
							
								const terminal_status = await $.getJSON(SITE_URL+'/locations/get_blockchyp_terminal_status?terminalName='+encodeURIComponent(terminal_name)+'&blockchyp_api_key='+encodeURIComponent(blockchyp_api_key)+'&blockchyp_bearer_token='+encodeURIComponent(blockchyp_bearer_token)+'&blockchyp_signing_key='+encodeURIComponent(blockchyp_signing_key));
							
								if(terminal_status.online)
								{
									$dot_terminal.removeClass('red').addClass('green').css('visibility','visible');
								}
								else
								{
									$dot_terminal.removeClass('green').addClass('red').css('visibility','visible');
								}
							
							});
						
						}
						</script>

						<div class="form-group">
							<?php echo form_label("<a href='https://phppointofsale.com/mercury_activate.php' target='_blank'>".lang('locations_enable_credit_card_processing').'</a>:', 'enable_credit_card_processing',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label'), FALSE); ?>
							<div class="col-sm-9 col-md-9 col-lg-10">
								<?php echo form_checkbox(array(
								'name'=>'enable_credit_card_processing',
								'id'=>'enable_credit_card_processing',
								'value'=>'1',
								'checked'=>$location_info->enable_credit_card_processing));?>
								<label for="enable_credit_card_processing"><span></span></label>
							</div>
						</div>

						<div id="merchant_information">
							
							<div class="form-group">	
								<?php echo form_label(lang('locations_credit_card_processor').':', 'credit_card_processor',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label')); ?>
								<div class="col-sm-9 col-md-9 col-lg-10">
								<?php echo form_dropdown('credit_card_processor', array('coreclear2' =>'PHP POS Payments (Powered by coreCLEAR)','mercury' => 'Worldpay (Formally Vantiv/Mercury)','card_connect' => 'CardConnect','square' => 'Square', 'heartland' => 'Heartland', 'evo' => 'EVO','worldpay' => 'Worldpay','firstdata' => 'First Data', 'stripe' => 'Stripe','braintree' => 'Braintree', 'other_usb' => lang('locations_other_emv_processor')), $location_info->credit_card_processor, 'class="form-control" id="credit_card_processor"');
									?>
								</div>
							</div>
							
							
							<div class="form-group">	
								<?php echo form_label(lang('locations_disable_confirmation_option_for_emv_credit_card').':', 'use_integrated_ebt',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label')); ?>
								<div class="col-sm-9 col-md-9 col-lg-10">
								<?php echo form_checkbox(array(
									'name'=>'disable_confirmation_option_for_emv_credit_card',
									'id'=>'disable_confirmation_option_for_emv_credit_card',
									'value'=>'1',
									'checked'=>$location_info->disable_confirmation_option_for_emv_credit_card));?>
									<label for="disable_confirmation_option_for_emv_credit_card"><span></span></label>
								</div>
							</div>
							
							<div class="form-group">	
								<?php echo form_label(lang('locations_use_integrated_ebt').':', 'use_integrated_ebt',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label')); ?>
								<div class="col-sm-9 col-md-9 col-lg-10">
								<?php echo form_checkbox(array(
									'name'=>'ebt_integrated',
									'id'=>'ebt_integrated',
									'value'=>'1',
									'checked'=>$location_info->ebt_integrated));?>
									<label for="ebt_integrated"><span></span></label>
								</div>
							</div>
							
							<div id="card_connect_info">

								<div class="form-group">	
								<?php echo form_label(lang('common_merchant_id').':', 'card_connect_mid',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label')); ?>
									<div class="col-sm-9 col-md-9 col-lg-10">
									<?php echo form_input(array(
										'class'=>'form-control form-inps',
										'name'=>'card_connect_mid',
										'id'=>'card_connect_mid',
										'autocomplete'=>'off',
										'value'=>$location_info->card_connect_mid));?>
									</div>
								</div>
								
								<div class="form-group">	
								<?php echo form_label(lang('common_rest_username').':', 'card_connect_rest_username',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label')); ?>
									<div class="col-sm-9 col-md-9 col-lg-10">
									<?php echo form_input(array(
										'class'=>'form-control form-inps',
										'name'=>'card_connect_rest_username',
										'id'=>'card_connect_rest_username',
										'autocomplete'=>'off',
										'value'=>$location_info->card_connect_rest_username));?>
									</div>
								</div>
								
								<div class="form-group">	
								<?php echo form_label(lang('common_rest_password').':', 'card_connect_rest_password',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label')); ?>
									<div class="col-sm-9 col-md-9 col-lg-10">
									<?php echo form_input(array(
										'class'=>'form-control form-inps',
										'name'=>'card_connect_rest_password',
										'id'=>'card_connect_rest_password',
										'autocomplete'=>'off',
										'value'=>$location_info->card_connect_rest_password));?>
									</div>
								</div>

							</div>
							<div id="emv_info">
								<div class="form-group">	
								<?php echo form_label(lang('locations_emv_terminal_id').':', 'emv_merchant_id',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label')); ?>
									<div class="col-sm-9 col-md-9 col-lg-10">
									<?php echo form_input(array(
										'class'=>'form-control form-inps',
										'name'=>'emv_merchant_id',
										'id'=>'emv_merchant_id',
										'autocomplete'=>'off',
										'value'=>$location_info->emv_merchant_id));?>
									</div>
								</div>
						
								<div class="form-group">	
								<?php echo form_label(lang('locations_com_port').':', 'com_port',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label')); ?>
									<div class="col-sm-9 col-md-9 col-lg-10">
									<?php echo form_input(array(
										'class'=>'form-control form-inps',
										'name'=>'com_port',
										'id'=>'com_port',
										'autocomplete'=>'off',
										'value'=>$location_info->com_port));?> (<?php echo lang('locations_com_9_is_default');?>)
									</div>
								</div>


								<div class="form-group">	
								<?php echo form_label(lang('locations_listener_port').':', 'listener_port',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label')); ?>
									<div class="col-sm-9 col-md-9 col-lg-10">
									<?php echo form_input(array(
										'class'=>'form-control form-inps',
										'name'=>'listener_port',
										'id'=>'listener_port',
										'autocomplete'=>'off',
										'value'=>$location_info->listener_port));?> (<?php echo lang('locations_3333_is_default_port_for_listener');?>)
									</div>
								</div>
								
							<div class="form-group">	
								<?php echo form_label(lang('common_integrated_gift_cards').':', 'integrated_gift_cards',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label')); ?>
								<div class="col-sm-9 col-md-9 col-lg-10">
								<?php echo form_checkbox(array(
									'name'=>'integrated_gift_cards',
									'id'=>'integrated_gift_cards',
									'value'=>'1',
									'checked'=>$location_info->integrated_gift_cards));?>
									<label for="integrated_gift_cards"><span></span></label>
								</div>
							</div>								
								
						
								<div class="form-group">	
								<?php echo form_label(lang('locations_net_e_pay_server').':', 'net_e_pay_server',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label')); ?>
									<div class="col-sm-9 col-md-9 col-lg-10">
									<?php echo form_input(array(
										'class'=>'form-control form-inps',
										'name'=>'net_e_pay_server',
										'id'=>'net_e_pay_server',
										'autocomplete'=>'off',
										'value'=>$location_info->net_e_pay_server));?> (<?php echo lang('locations_net_e_pay_info');?>)
									</div>
								</div>
								
								<div class="form-group">	
								<?php echo form_label(lang('locations_secure_device_override_emv').':', 'secure_device_override_emv',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label')); ?>
									<div class="col-sm-9 col-md-9 col-lg-10">
									<?php echo form_input(array(
										'class'=>'form-control form-inps',
										'name'=>'secure_device_override_emv',
										'id'=>'secure_device_override_emv',
										'autocomplete'=>'off',
										'value'=>$location_info->secure_device_override_emv));?>
									</div>
								</div>
								
								
								<div class="form-group">	
								<?php echo form_label(lang('locations_secure_device_override_non_emv').':', 'secure_device_override_non_emv',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label')); ?>
									<div class="col-sm-9 col-md-9 col-lg-10">
									<?php echo form_input(array(
										'class'=>'form-control form-inps',
										'name'=>'secure_device_override_non_emv',
										'id'=>'secure_device_override_non_emv',
										'autocomplete'=>'off',
										'value'=>$location_info->secure_device_override_non_emv));?>
									</div>
								</div>
								
								
								<div class="form-group" id="init_mercury_emv_wrapper" style="display:none;">	
								<?php echo form_label('&nbsp;', 'locations_init_mercury_emv',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label')); ?>
									<div class="col-sm-9 col-md-9 col-lg-10">
								 <div id="ajax-loader" style="text-align:center;display:none"><?php echo img(array('src' => base_url().'assets/img/ajax-loader.gif')); ?></div>
									<button type="button" id="locations_init_mercury_emv" class="btn btn-primary btn-block"><?php echo lang('locations_init_mercury_emv'); ?></button>
									</div>
								</div>							
						</div>
							
						<div id="mercury_hosted_checkout_info">
							<div class="form-group">	
							<?php echo form_label(lang('locations_hosted_checkout_merchant_id').':', 'hosted_checkout_merchant_id',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label')); ?>
								<div class="col-sm-9 col-md-9 col-lg-10">
								<?php echo form_input(array(
									'class'=>'form-control form-inps',
									'name'=>'hosted_checkout_merchant_id',
									'id'=>'hosted_checkout_merchant_id',
									'autocomplete'=>'off',
									'value'=>$location_info->hosted_checkout_merchant_id));?>
								</div>
							</div>

							<div class="form-group">	
							<?php echo form_label(lang('locations_hosted_checkout_merchant_password').':', 'hosted_checkout_merchant_password',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label')); ?>
								<div class="col-sm-9 col-md-9 col-lg-10">
								<?php echo form_input(array(
									'name'=>'hosted_checkout_merchant_password',
									'id'=>'hosted_checkout_merchant_password',
									'autocomplete'=>'off',
									'class'=>'form-control form-inps',
									'value'=>$location_info->hosted_checkout_merchant_password));?>
								<span id="hosted_checkout_merchant_password_note"><?php echo lang('locations_mercury_password_note'); ?></span>
								</div>
							</div>
						</div>

						<div id="square_info">						
							<div class="form-group">	
								<?php echo form_label(lang('locations_currency_code').':', 'square_currency_code',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label '), false); ?>
								<div class="col-sm-9 col-md-9 col-lg-10">
									<?php echo form_input(array(
										'class'=>'form-control form-inps',
									'name'=>'square_currency_code',
									'id'=>'square_currency_code',
									'value'=>$location_info->square_currency_code ? $location_info->square_currency_code : 'USD'));?>
								</div>
							</div>

							<div class="form-group">	
								<?php echo form_label(lang('locations_currency_multiplier').':', 'square_currency_multiplier',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label '), false); ?>
								<div class="col-sm-9 col-md-9 col-lg-10">
									<?php echo form_input(array(
										'class'=>'form-control form-inps',
									'name'=>'square_currency_multiplier',
									'id'=>'square_currency_multiplier',
									'value'=>$location_info->square_currency_multiplier ? $location_info->square_currency_multiplier : '100'));?>
								</div>
							</div>
							
							<?php echo form_hidden('square_location_id','');?>
						</div>
						
						<div id="stripe_info">						
							<div class="form-group">	
								<?php echo form_label('<a href="https://support.stripe.com/questions/which-currencies-does-stripe-support" target="_blank">'.lang('locations_currency_code').'</a>:', 'stripe_currency_code',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label '), false); ?>
								<div class="col-sm-9 col-md-9 col-lg-10">
									<?php echo form_input(array(
										'class'=>'form-control form-inps',
									'name'=>'stripe_currency_code',
									'id'=>'stripe_currency_code',
									'value'=>$location_info->stripe_currency_code ? $location_info->stripe_currency_code : 'usd'));?>
								</div>
							</div>
						
							<div class="form-group">	
							<?php echo form_label(lang('locations_stripe_private').':', 'stripe_private',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label')); ?>
								<div class="col-sm-9 col-md-9 col-lg-10">
								<?php echo form_input(array(
									'class'=>'form-control form-inps',
									'name'=>'stripe_private',
									'id'=>'stripe_private',
									'autocomplete'=>'off',
									'value'=>$location_info->stripe_private));?>
								</div>
							</div>
							
							<div class="form-group">	
							<?php echo form_label(lang('locations_stripe_public').':', 'stripe_public',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label')); ?>
								<div class="col-sm-9 col-md-9 col-lg-10">
								<?php echo form_input(array(
									'class'=>'form-control form-inps',
									'name'=>'stripe_public',
									'id'=>'stripe_public',
									'autocomplete'=>'off',
									'value'=>$location_info->stripe_public));?>
								</div>
							</div>
						</div>
						
						<div id="coreclear2_info">						
							<div class="form-group">	
								<?php echo form_label(lang('locations_blockchyp_api_key').':', 'blockchyp_api_key',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label')); ?>
								<div class="col-sm-9 col-md-9 col-lg-10">
								<?php echo form_input(array(
									'class'=>'form-control form-inps',
									'name'=>'blockchyp_api_key',
									'id'=>'blockchyp_api_key',
									'autocomplete'=>'off',
									'value'=>$location_info->blockchyp_api_key));?>
								</div>
							</div>
						
						
							<div class="form-group">	
								<?php echo form_label(lang('locations_blockchyp_bearer_token').':', 'blockchyp_bearer_token',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label')); ?>
								<div class="col-sm-9 col-md-9 col-lg-10">
								<?php echo form_input(array(
									'class'=>'form-control form-inps',
									'name'=>'blockchyp_bearer_token',
									'id'=>'blockchyp_bearer_token',
									'autocomplete'=>'off',
									'value'=>$location_info->blockchyp_bearer_token));?>
								</div>
							</div>
						
						
							<div class="form-group">	
								<?php echo form_label(lang('locations_blockchyp_signing_key').':', 'blockchyp_signing_key',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label')); ?>
								<div class="col-sm-9 col-md-9 col-lg-10">
								<?php echo form_input(array(
									'class'=>'form-control form-inps',
									'name'=>'blockchyp_signing_key',
									'id'=>'blockchyp_signing_key',
									'autocomplete'=>'off',
									'value'=>$location_info->blockchyp_signing_key));?>
								</div>
							</div>
						

							<div class="form-group">
								<?php echo form_label(lang('locations_blockchyp_test_mode').':', 'blockchyp_test_mode',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label'), FALSE); ?>
								<div class="col-sm-9 col-md-9 col-lg-10">
									<?php echo form_checkbox(array(
									'name'=>'blockchyp_test_mode',
									'id'=>'blockchyp_test_mode',
									'value'=>'1',
									'checked'=>$location_info->blockchyp_test_mode));?>
									<label for="blockchyp_test_mode"><span></span></label>
								</div>
							</div>
												
						
						</div>
						
						
						<div id="braintree_info">						
							<div class="form-group">	
								<?php echo form_label(lang('common_merchant_id').':', 'braintree_merchant_id',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label ')); ?>
								<div class="col-sm-9 col-md-9 col-lg-10">
									<?php echo form_input(array(
										'class'=>'form-control form-inps',
									'name'=>'braintree_merchant_id',
									'id'=>'braintree_merchant_id',
									'value'=>$location_info->braintree_merchant_id));?>
								</div>
							</div>
						
							<div class="form-group">	
							<?php echo form_label(lang('locations_braintree_public_key').':', 'braintree_public_key',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label')); ?>
								<div class="col-sm-9 col-md-9 col-lg-10">
								<?php echo form_input(array(
									'class'=>'form-control form-inps',
									'name'=>'braintree_public_key',
									'id'=>'braintree_public_key',
									'autocomplete'=>'off',
									'value'=>$location_info->braintree_public_key));?>
								</div>
							</div>
							
							<div class="form-group">	
							<?php echo form_label(lang('locations_braintree_private_key').':', 'braintree_private_key',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label')); ?>
								<div class="col-sm-9 col-md-9 col-lg-10">
								<?php echo form_input(array(
									'class'=>'form-control form-inps',
									'name'=>'braintree_private_key',
									'id'=>'braintree_private_key',
									'autocomplete'=>'off',
									'value'=>$location_info->braintree_private_key));?>
								</div>
							</div>
						</div>						
					</div>
					
					
						<div class="form-group">	
							<?php echo form_label(lang('locations_disable_markup_markdown').':', 'disable_markup_markdown',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label')); ?>
							<div class="col-sm-9 col-md-9 col-lg-10">
							<?php echo form_checkbox(array(
								'name'=>'disable_markup_markdown',
								'id'=>'disable_markup_markdown',
								'value'=>'1',
								'checked'=>$location_info->disable_markup_markdown));?>
								<label for="disable_markup_markdown"><span></span></label>
							</div>
						</div>
						
						<div class="form-group">	
						<?php echo form_label(lang('locations_auto_reports_email').':', 'auto_reports_email',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label'), FALSE); ?>
							<div class="col-sm-9 col-md-9 col-lg-10">
							<?php echo form_input(array(
								'class'=>'form-control form-inps',
								'name'=>'auto_reports_email',
								'id'=>'auto_reports_email',
								'value'=>$location_info->auto_reports_email));?>
							</div>
						</div>
						
						<div class="form-group">	
						<?php echo form_label(lang('locations_auto_reports_email_time').':', 'auto_reports_email_time',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label'), FALSE); ?>
							<div class="col-sm-9 col-md-9 col-lg-10">
								<?php
								$this->load->helper('date');
								?>
								<?php echo form_dropdown('auto_reports_email_time', get_hours_range(), $location_info->auto_reports_email_time ? date('H:i',strtotime($location_info->auto_reports_email_time)) : '' , 'class="form-control" id="auto_reports_email_time"'); ?>
								
							</div>
						</div>
						
						<div class="form-group">	
						<?php echo form_label(lang('locations_auto_reports_day').':', 'auto_reports_day',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label'), FALSE); ?>
							<div class="col-sm-9 col-md-9 col-lg-10">
								<?php echo form_dropdown('auto_reports_day', array('previous_day' => lang('locations_previous_day'),'current_day' => lang('locations_current_day')), $location_info->auto_reports_day , 'class="form-control" id="auto_reports_day"'); ?>
							</div>
						</div>
						
					
						<div class="form-group">	
							<?php echo form_label(lang('locations_receive_stock_alert').':', 'receive_stock_alert',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label')); ?>
							<div class="col-sm-9 col-md-9 col-lg-10">
							<?php echo form_checkbox(array(
								'name'=>'receive_stock_alert',
								'id'=>'receive_stock_alert',
								'value'=>'1',
								'checked'=>$location_info->receive_stock_alert));?>
								<label for="receive_stock_alert"><span></span></label>
							</div>
						</div>


						<div class="form-group" id="stock_alert_email_container">	
						<?php echo form_label(lang('locations_stock_alert_email').':', 'stock_alert_email',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label')); ?>
							<div class="col-sm-9 col-md-9 col-lg-10">
								<?php echo form_input(array(
								'type'=>'text',
								'class'=>'form-control form-inps',
								'name'=>'stock_alert_email',
								'id'=>'stock_alert_email',
								'value'=>$location_info->stock_alert_email));?>
							</div>
						</div>
						
						<div class="form-group" id="stock_alerts_just_order_level_container">	
						<?php echo form_label(lang('locations_stock_alerts_just_order_level').':', 'stock_alerts_just_order_level',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label')); ?>
							<div class="col-sm-9 col-md-9 col-lg-10">
								<?php echo form_checkbox(array(
									'name'=>'stock_alerts_just_order_level',
									'id'=>'stock_alerts_just_order_level',
									'class' => 'stock_alerts_just_order_level_checkbox delete-checkbox',
									'value'=>1,
									'checked'=>$location_info->stock_alerts_just_order_level));
								?>
								<label for="stock_alerts_just_order_level"><span></span></label>
							</div>
						</div>
						
						
						<div class="form-group">	
							<?php echo form_label(lang('locations_timezone').':', 'timezone',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label required')); ?>
							<div class="col-sm-9 col-md-9 col-lg-10">
							<?php echo form_dropdown('timezone', $all_timezones, $location_info->timezone, 'class="form-control" id="timezone"');
								?>
							</div>
						</div>

						<div class="form-group">	
						<?php echo form_label(anchor('http://mailchimp.com', lang('locations_mailchimp_api_key'), array('target' => '_blank')).':', 'mailchimp_api_key',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label'), FALSE); ?>
							<div class="col-sm-9 col-md-9 col-lg-10">
							<?php echo form_input(array(
								'class'=>'form-control form-inps',
								'name'=>'mailchimp_api_key',
								'id'=>'mailchimp_api_key',
								'value'=>$location_info->mailchimp_api_key));?>
							</div>
						</div>


						<?php
						if ($this->Location->get_info_for_key('mailchimp_api_key'))
						{
							$this->load->helper('mailchimp');
						?>
									<div class="form-group">
							<div class="column">	
								<?php echo form_label(lang('common_default_mailing_lists').':', 'mailchimp_mailing_lists',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label ')); ?>
							</div>
	
						    <div class="column">
								<ul style="list-style: none; float:left;">
							<?php
							$default_mailchimp_lists = array();
								
							if ($this->Location->get_info_for_key('default_mailchimp_lists'))
							{
								$default_mailchimp_lists =	unserialize($this->Location->get_info_for_key('default_mailchimp_lists'));
							}
							
							if (!$default_mailchimp_lists)
							{
								$default_mailchimp_lists = array();								
							}
							
							
							foreach(get_all_mailchimps_lists() as $list)
							{
								echo '<li>';
								echo form_checkbox(array('name'=> 'default_mailchimp_lists[]',
								'id' => $list['id'],
								'value' => $list['id'],
								'checked' => in_array($list['id'],$default_mailchimp_lists),
								'label'	=> $list['id']));
		
								echo '<label for="'.$list['id'].'"><span></span></label> '.$list['name'];
								echo '</li>';
							}
							?>
							</ul>
							</div>
							<div class="cleared"></div>
						</div>
						<?php
						}
						?> 


						<div class="form-group">	
						<?php echo form_label(anchor('http://platform.ly', lang('locations_platformly_api_key'), array('target' => '_blank')).':', 'platformly_api_key',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label'), FALSE); ?>
							<div class="col-sm-9 col-md-9 col-lg-10">
							<?php echo form_input(array(
								'class'=>'form-control form-inps',
								'name'=>'platformly_api_key',
								'id'=>'platformly_api_key',
								'value'=>$location_info->platformly_api_key));?>
							</div>
						</div>

						<div class="form-group">	
						<?php echo form_label(anchor('http://platform.ly', lang('locations_platformly_project_id'), array('target' => '_blank')).':', 'platformly_project_id',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label'), FALSE); ?>
							<div class="col-sm-9 col-md-9 col-lg-10">
							<?php echo form_input(array(
								'class'=>'form-control form-inps',
								'name'=>'platformly_project_id',
								'id'=>'platformly_project_id',
								'value'=>$location_info->platformly_project_id));?>
							</div>
						</div>

						<div class="form-group">	
						<?php echo form_label(anchor('https://www.twilio.com/', lang('locations_twilio_sid'), array('target' => '_blank')).':', 'twilio_sid',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label'), FALSE); ?>
							<div class="col-sm-9 col-md-9 col-lg-10">
							<?php echo form_input(array(
								'class'=>'form-control form-inps',
								'name'=>'twilio_sid',
								'id'=>'twilio_sid',
								'value'=>$location_info->twilio_sid));?>
							</div>
						</div>
	
						<div class="form-group">	
						<?php echo form_label(anchor('https://www.twilio.com/', lang('locations_twilio_token'), array('target' => '_blank')).':', 'twilio_token',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label'), FALSE); ?>
							<div class="col-sm-9 col-md-9 col-lg-10">
							<?php echo form_input(array(
								'class'=>'form-control form-inps',
								'name'=>'twilio_token',
								'id'=>'twilio_token',
								'value'=>$location_info->twilio_token));?>
							</div>
						</div>

						<div class="form-group">	
						<?php echo form_label(anchor('https://www.twilio.com/', lang('locations_twilio_sms_from_number'), array('target' => '_blank')).':', 'twilio_sms_from',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label'), FALSE); ?>
							<div class="col-sm-9 col-md-9 col-lg-10">
							<?php echo form_input(array(
								'class'=>'form-control form-inps',
								'name'=>'twilio_sms_from',
								'id'=>'twilio_sms_from',
								'value'=>$location_info->twilio_sms_from));?>
							</div>
						</div>
	
	
	<div class="form-group override-taxes-container">
		<?php echo form_label(lang('common_override_default_tax').':', '',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label wide')); ?>
		<div class="col-sm-9 col-md-9 col-lg-10">
			<?php echo form_checkbox(array(
				'name'=>'override_default_tax',
				'id'=>'override_default_tax',
				'class' => 'override_default_tax_checkbox delete-checkbox',
				'value'=>1,
				'checked'=>$location_info->tax_class_id || $location_info->default_tax_1_rate));
			?>
			<label for="override_default_tax"><span></span></label>
		</div>
	</div>
	
	
	<div class="tax-container main <?php if (!($location_info->tax_class_id || $location_info->default_tax_1_rate)){echo 'hidden';} ?>">	
						<div class="form-group">	
							<?php echo form_label(lang('common_tax_class').': ', 'tax_class',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label')); ?>
							<div class="col-sm-9 col-md-9 col-lg-10">
							<?php echo form_dropdown('tax_class', $tax_classes, $location_info->tax_class_id, array('id' =>'tax_class', 'class' => 'form-control tax_class'));?>
							</div>
						</div>
	
						<div style="display:<?php echo $location_info->tax_class_id ? 'none' : 'block';?>">
						<div class="form-group">
							<h4 class="text-center"><?php echo lang('common_or') ?></h4>
						</div>

						<div class="form-group">	
							<?php echo form_label(lang('common_default_tax_rate_1').':', 'default_tax_1_rate',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label')); ?>
							<div class="col-sm-4 col-md-4 col-lg-5">
								<?php echo form_input(array(
								'class'=>'form-control form-inps',
								'placeholder' => lang('common_tax_name'),
								'name'=>'default_tax_1_name',
								'id'=>'default_tax_1_name',
								'size'=>'10',
								'value'=>$location_info->default_tax_1_name ? $location_info->default_tax_1_name : lang('common_sales_tax_1')));?>
							</div>

							<div class="col-sm-4 col-md-4 col-lg-5">
								<div class="input-group">
									<?php echo form_input(array(
									'class'=>'form-control form-inps-tax',
									'placeholder' => lang('common_tax_percent'),
									'name'=>'default_tax_1_rate',
									'id'=>'default_tax_1_rate',
									'size'=>'4',
									'value'=>$location_info->default_tax_1_rate));?>
								  <span class="input-group-addon">%</span>
								</div>
							</div>
						</div>

						<div class="form-group">	
							<?php echo form_label(lang('common_default_tax_rate_2').':', 'default_tax_1_rate',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label')); ?>
							<div class="col-sm-4 col-md-4 col-lg-5">
								<?php echo form_input(array(
								'class'=>'form-control form-inps',
								'placeholder' => lang('common_tax_name'),
								'name'=>'default_tax_2_name',
								'id'=>'default_tax_2_name',
								'size'=>'10',
								'value'=>$location_info->default_tax_2_name ? $location_info->default_tax_2_name : lang('common_sales_tax_2')));?>
							</div>

							<div class="col-sm-4 col-md-4 col-lg-5">
								<div class="input-group">
									<?php echo form_input(array(
									'class'=>'form-control form-inps-tax'	,
									'placeholder' => lang('common_tax_percent'),
									'name'=>'default_tax_2_rate',
									'id'=>'default_tax_2_rate',
									'size'=>'4',
									'value'=>$location_info->default_tax_2_rate));?>
								  <span class="input-group-addon">%</span>
								</div>
								<div class="clear"></div>
								<?php echo form_checkbox('default_tax_2_cumulative', '1', $location_info->default_tax_2_cumulative ? true : false, 'class="cumulative_checkbox" id="default_tax_2_cumulative"');  ?>
								<label for="default_tax_2_cumulative"><span></span></label>
								<span class="cumulative_label">
									<?php echo lang('common_cumulative'); ?>
								</span>
							</div>
						</div>
					
						<div class="col-sm-9 col-sm-offset-3 col-md-9 col-md-offset-3 col-lg-9 col-lg-offset-3" style="display: <?php echo $location_info->default_tax_3_rate ? 'none' : 'block';?>">
							<a href="javascript:void(0);" class="show_more_taxes btn btn-orange btn-round"><?php echo lang('common_show_more');?> &raquo;</a>
						</div>
					
						<div class="more_taxes_container" style="display: <?php echo $location_info->default_tax_3_rate ? 'block' : 'none';?>">
							
							<div class="form-group">	
								<?php echo form_label(lang('common_default_tax_rate_3').':', 'default_tax_3_rate',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label')); ?>
								<div class="col-sm-4 col-md-4 col-lg-5">
									<?php echo form_input(array(
									'class'=>'form-control form-inps',
									'placeholder' => lang('common_tax_name'),
									'name'=>'default_tax_3_name',
									'id'=>'default_tax_3_name',
									'size'=>'10',
									'value'=>$location_info->default_tax_3_name ? $location_info->default_tax_3_name : ''));?>
								</div>

								<div class="col-sm-4 col-md-4 col-lg-5">
									<div class="input-group">
										<?php echo form_input(array(
										'class'=>'form-control form-inps',
										'placeholder' => lang('common_tax_percent'),
										'name'=>'default_tax_3_rate',
										'id'=>'default_tax_3_rate',
										'size'=>'4',
										'value'=>$location_info->default_tax_3_rate));?>
									  <span class="input-group-addon">%</span>
									</div>
								</div>
							</div>

							<div class="form-group">	
								<?php echo form_label(lang('common_default_tax_rate_4').':', 'default_tax_4_rate',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label')); ?>
								<div class="col-sm-4 col-md-4 col-lg-5">
									<?php echo form_input(array(
									'class'=>'form-control form-inps',
									'placeholder' => lang('common_tax_name'),
									'name'=>'default_tax_4_name',
									'id'=>'default_tax_4_name',
									'size'=>'10',
									'value'=>$location_info->default_tax_4_name ? $location_info->default_tax_4_name : ''));?>
								</div>

								<div class="col-sm-4 col-md-4 col-lg-5">
									<div class="input-group">
										<?php echo form_input(array(
										'class'=>'form-control form-inps',
										'name'=>'default_tax_4_rate',
										'placeholder' => lang('common_tax_percent'),
										'id'=>'default_tax_4_rate',
										'size'=>'4',
										'value'=>$location_info->default_tax_4_rate));?>
									  <span class="input-group-addon">%</span>
									</div>
								</div>
							</div>

							<div class="form-group">	
								<?php echo form_label(lang('common_default_tax_rate_5').':', 'default_tax_5_rate',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label')); ?>
								<div class="col-sm-4 col-md-4 col-lg-5">
									<?php echo form_input(array(
									'class'=>'form-control form-inps',
									'placeholder' => lang('common_tax_name'),
									'name'=>'default_tax_5_name',
									'id'=>'default_tax_5_name',
									'size'=>'10',
									'value'=>$location_info->default_tax_5_name ? $location_info->default_tax_5_name : ''));?>
								</div>

								<div class="col-sm-4 col-md-4 col-lg-5">
									<div class="input-group">
										<?php echo form_input(array(
										'class'=>'form-control form-inps',
										'name'=>'default_tax_5_rate',
										'placeholder' => lang('common_tax_percent'),
										'id'=>'default_tax_5_rate',
										'size'=>'4',
										'value'=>$location_info->default_tax_5_rate));?>
									  <span class="input-group-addon">%</span>
									</div>
								</div>
							</div>
						</div><!--End more Taxes Container-->		
					</div>
				</div>
						
						<?php echo form_hidden('redirect', $redirect); ?>

						<div class="form-actions pull-right">
							<?php
							if ($purchase_email)
							{
								echo form_hidden('purchase_email', $purchase_email);
							}
							
							echo form_submit(array(
								'name'=>'submitf',
								'id'=>'submitf',
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
<?php }?>

		

<script type='text/javascript'>
	var submitting = false;
	var add_register_index = 0;
		//validation and submit handling
		$(document).ready(function()
		{			
			//Turn on Credit card processing if we type in IP Tran
	  		 $(document).on('keyup', ".iptran",function()
			 {
					var is_ip_tran_on = false;

					$(".iptran").each(function( index ) 
					{
						if ($(this).val()!= '')
						{
							is_ip_tran_on = true;
						}
					});

					if (is_ip_tran_on && !$("#enable_credit_card_processing").prop('checked'))
					{
						$("#enable_credit_card_processing").click();
					}
					
					check_ip_tran_init();
			  });
			
			check_ip_tran_init();
			
			function check_ip_tran_init()
			{
				var hide_init = false;
			
				$(".iptran").each(function()
				{
					if ($(this).val())
					{
						hide_init = true;
					}
				});
			
				if (hide_init)
				{
					$("#init_mercury_emv_wrapper").hide();		  						
				}
				else
				{
					$("#init_mercury_emv_wrapper").show();		  	
				}
			}
			
			$(document).on('click','.update_parameters_ip_tran', function()
			{
				var emv_merchant_id = $("#emv_merchant_id").val();
				var ip_tran_id = $(this).parent().prev().find('.iptran').val();
				$("#grid-loader").show();	
				
				$.post('<?php echo site_url("locations/mercury_ip_tran_update_parameters");?>',{device_id: ip_tran_id, merchant_id : emv_merchant_id}, function(response)
				{
					show_feedback(response.success ? 'success' : 'error', response.message,response.success ? <?php echo json_encode(lang('common_success')); ?> : <?php echo json_encode(lang('common_error')); ?>);
					$("#grid-loader").hide();
				},'json');
			});
			
			
			$(document).on('click','.init_ip_tran', function()
			{
				var emv_merchant_id = $("#emv_merchant_id").val();
				var ip_tran_id = $(this).parent().prev().find('.iptran').val();
				$("#grid-loader").show();	
				
				$.post('<?php echo site_url("locations/mercury_ip_tran_emv_param_download");?>',{device_id: ip_tran_id, merchant_id : emv_merchant_id}, function(response)
				{
					show_feedback(response.success ? 'success' : 'error', response.message,response.success ? <?php echo json_encode(lang('common_success')); ?> : <?php echo json_encode(lang('common_error')); ?>);
					$("#grid-loader").hide();
				},'json');
			});


			$(document).on('click','.test_mode_ip_tran', function()
			{
				var emv_merchant_id = $("#emv_merchant_id").val();
				var ip_tran_id = $(this).parent().prev().find('.iptran').val();
				$("#grid-loader").show();	
				
				$.post('<?php echo site_url("locations/mercury_ip_tran_emv_test_mode");?>',{device_id: ip_tran_id, merchant_id : emv_merchant_id}, function(response)
				{
					show_feedback(response.success ? 'success' : 'error', response.message,response.success ? <?php echo json_encode(lang('common_success')); ?> : <?php echo json_encode(lang('common_error')); ?>);
					$("#grid-loader").hide();
				},'json');
			});
			
					
					
			$(document).on('keyup', "#emv_merchant_id",function()
			{
				check_emv_merchant_id();
				
			});
			check_emv_merchant_id();
			
			
			function check_emv_merchant_id()
			{
				if ($("#emv_merchant_id").val()!='')
				{
					$("#init_mercury_emv_wrapper").show();
				}
				else
				{
					$("#init_mercury_emv_wrapper").hide();
				}
			} 	
			
			$("#locations_init_mercury_emv").click(function()
			{
				$("#ajax-loader").show();
				$("#locations_init_mercury_emv").hide();							
				
				var credit_card_processor = $("#credit_card_processor").val();
				var emv_merchant_id = $("#emv_merchant_id").val();
				var com_port = $("#com_port").val();
				var listener_port = $("#listener_port").val();
				var net_e_pay_server = $("#net_e_pay_server").val();
				var secure_device_override_emv= $('#secure_device_override_emv').val();
				var secure_device_override_non_emv = $('#secure_device_override_non_emv').val();
 				var terminal_id_0 = $("#terminal_id_0").val();
				var ebt_integrated = $("#ebt_integrated").prop('checked') ? 1 : 0;
				var integrated_gift_cards = $("#integrated_gift_cards").prop('checked') ? 1 : 0;
				
				$.post('<?php echo site_url("locations/save_emv_data/".$location_info->location_id);?>', 
				{ebt_integrated:ebt_integrated, terminal_id: terminal_id_0, secure_device_override_emv: secure_device_override_emv, secure_device_override_non_emv: secure_device_override_non_emv, credit_card_processor: credit_card_processor, emv_merchant_id: emv_merchant_id, com_port: com_port, listener_port:listener_port, net_e_pay_server:net_e_pay_server, integrated_gift_cards:integrated_gift_cards}, function(response) {
					
					if(response.success)
					{
						var emv_param_download_init_params = response.emv_param_download_init_params;

 				   	emv_param_download_init_params['post_data']['ComPort'] = com_port;
				   	emv_param_download_init_params['post_data']['MerchantID'] = emv_merchant_id;
						
		 				terminal_id_0 = $("#terminal_id_0").val();
						
						if (terminal_id_0)
						{
					   	emv_param_download_init_params['post_data']['TerminalID'] = terminal_id_0;
						}
						
						emv_param_download(emv_param_download_init_params['post_host'], listener_port, emv_param_download_init_params.post_data, <?php echo json_encode(lang('locations_init_device_success')); ?>, <?php echo json_encode(lang('locations_unable_to_init_device'));?>, function()
						{
							$("#ajax-loader").hide();
							$("#locations_init_mercury_emv").show();							
						});
					}
					else
					{
						$("#ajax-loader").hide();
						$("#locations_init_mercury_emv").show();
					}
				}, 'json');
			});
			
			$('#employees').selectize();
			
			$('#cc_email').selectize({
			    delimiter: ',',
			    persist: false,
			    create: function(input) {
			        return {
			            value: input,
			            text: input
			        }
			    }
			});
			
			$('#bcc_email').selectize({
			    delimiter: ',',
			    persist: false,
			    create: function(input) {
			        return {
			            value: input,
			            text: input
			        }
			    }
			});
			
      $('#color').colorpicker();
			
			$(".delete_register").click(function()
			{
				$("#location_form").append('<input type="hidden" name="registers_to_delete[]" value="'+$(this).data('register-id')+'" />');
				$(this).parent().parent().remove();
			});
	
			$("#add_register").click(function()
			{
				$("#price_registers tbody").append('<tr><td><span class="dot terminal"></span><input type="text" class="registers_to_add form-control next-to-status" name="registers_to_add['+add_register_index+'][name]" value="" /></td><td class="card_connect_info"><input type="text" class="registers_to_add form-control register-cc-field" name="registers_to_add['+add_register_index+'][card_connect_hsn]" value="" /></td><td><input type="text" class="registers_to_add form-control register-cc-field" name="registers_to_add['+add_register_index+'][emv_terminal_id]" value="" /></td><td><input type="text" class="registers_to_add form-control iptran register-cc-field register-cc-field-datacap" name="registers_to_add['+add_register_index+'][iptran_device_id]" value="" /></td><td><input type="text" class="registers_to_add form-control iptran register-cc-field register-cc-field-datacap" name="registers_to_add['+add_register_index+'][emv_pinpad_ip]" value="" /></td><td><input type="text" class="registers_to_add form-control iptran register-cc-field register-cc-field-datacap" name="registers_to_add['+add_register_index+'][emv_pinpad_port]" value="" /></td><td><a class="update_parameters_ip_tran register-cc-field register-cc-field-datacap" href="javascript:void(0);"><?php echo lang('locations_update_params_ip_tran'); ?></a><span class="register-cc-field register-cc-field-datacap"> / </span><a class="init_ip_tran register-cc-field register-cc-field-datacap" href="javascript:void(0);"><?php echo lang('locations_init_mercury_emv'); ?></a> </td><td>&nbsp;</td></tr>');
				check_credit_card_processor();
				add_register_index++;
			});
						

			if ($("#location_form_auth").length == 1)
			{
			    setTimeout(function(){$(":input:visible:first","#location_form_auth").focus();},100);
			}
			else
			{
			    setTimeout(function(){$(":input:visible:first","#location_form").focus();},100);				
			}
			var submitting = false;
			$('#location_form').validate({
				submitHandler:function(form)
				{
					if (submitting) return;
					submitting = true;
					$('#grid-loader').show();
					$(form).ajaxSubmit({
					success:function(response)
					{
						//Don't let the registers be double submitted, so we change the name
						$(".registers_to_add").attr('name', 'registers_added[]');
						
						$('#grid-loader').hide();
						submitting = false;						
						show_feedback(response.success ? 'success' : 'error', response.message,response.success ? <?php echo json_encode(lang('common_success')); ?> +' #' + response.location_id : <?php echo json_encode(lang('common_error')); ?>);
						
						
						if(response.redirect==2 && response.success)
						{
							window.location.href = '<?php echo site_url('locations'); ?>';
						}
						else
						{
							$("html, body").animate({ scrollTop: 0 }, "slow");
							$(".form-group").removeClass('has-success has-error');
						}
										
					},
					<?php if(!$location_info->location_id) { ?>
					resetForm: true,
					<?php } ?>
					dataType:'json'
				});

				},
				ignore: '',
				errorClass: "text-danger",
				errorElement: "p",
				errorPlacement: function(error, element) {
				    error.insertBefore(element);
				},
					highlight:function(element, errorClass, validClass) {
						$(element).parents('.form-group').removeClass('has-success').addClass('has-error');
					},
					unhighlight: function(element, errorClass, validClass) {
						$(element).parents('.form-group').removeClass('has-error').addClass('has-success');
					},
				rules:
				{
					name:
					{
						required:true,
					},
					phone:
					{
						required:true
					},
					address:
					{
						required:true
					},
					timezone:
					{
						required: true
					},
					"employees[]": "required"
					
		   		},
				messages:
				{
					name:
					{
						required:<?php echo json_encode(lang('locatoins_name_required')); ?>,

					},
					phone:
					{
						required:<?php echo json_encode(lang('locations_phone_required')); ?>,
						number:<?php echo json_encode(lang('locations_phone_valid')); ?>
					},
					address:
					{
						required:<?php echo json_encode(lang('locations_address_required')); ?>
					},
					timezone:
					{
						required:<?php echo json_encode(lang('locations_timezone_required_field')); ?>
					},
					"employees[]": <?php echo json_encode(lang('locations_one_employee_required')); ?>
					
				}
			});
			
			$("#enable_credit_card_processing").change(check_enable_credit_card_processing).ready(check_enable_credit_card_processing);

			$("#credit_card_processor").change(check_credit_card_processor).ready(check_credit_card_processor);
			
			function check_enable_credit_card_processing()
			{
				register_cc_fields_show_hide();
				
				if($("#enable_credit_card_processing").prop('checked'))
				{
					check_credit_card_processor();
					$("#merchant_information").show();
				}
				else
				{
					$("#merchant_information").hide();
				}

			}
			
			function register_cc_fields_show_hide()
			{
				if($("#enable_credit_card_processing").prop('checked'))
				{
					$(".register-cc-field").show();
				}
				else
				{
					$(".register-cc-field").hide();
				}
			}
			
			function check_credit_card_processor()
			{
				var cc_processor = $("#credit_card_processor").val();
				if (cc_processor == 'mercury')
				{
					$("#emv_info").show();
					$("#mercury_hosted_checkout_info").show();
					$("#stripe_info").hide();
					$("#braintree_info").hide();
					$("#square_info").hide();					
					$("#card_connect_info,.card_connect_info").hide();		
					$("#coreclear2_info").hide();		
					$(".register-cc-field-datacap").show();	
				}
				else if(cc_processor == 'heartland' || cc_processor == 'evo' || cc_processor == 'worldpay' || cc_processor == 'firstdata' || cc_processor == 'other_usb')
				{
					$("#emv_info").show();
					$("#mercury_hosted_checkout_info").hide();
					$("#stripe_info").hide();
					$("#braintree_info").hide();
					$("#square_info").hide();
					$("#card_connect_info,.card_connect_info").hide();	
					$("#coreclear2_info").hide();
					$(".register-cc-field-datacap").show();	
					
				}
				else if (cc_processor == 'stripe')
				{
					$("#emv_info").hide();
					$("#mercury_hosted_checkout_info").hide();
					$("#stripe_info").show();
					$("#braintree_info").hide();
					$("#square_info").hide();
					$("#card_connect_info,.card_connect_info").hide();
					$("#coreclear2_info").hide();
					$(".register-cc-field-datacap").hide();	
					
				}
				else if (cc_processor == 'braintree')
				{
					$("#emv_info").hide();
					$("#mercury_hosted_checkout_info").hide();
					$("#stripe_info").hide();
					$("#braintree_info").show();
					$("#square_info").hide();
					$("#card_connect_info,.card_connect_info").hide();	
					$("#coreclear2_info").hide();
					$(".register-cc-field-datacap").hide();
				}
				else if (cc_processor == 'square')
				{
					$("#square_info").show();
					$("#emv_info").hide();
					$("#mercury_hosted_checkout_info").hide();
					$("#stripe_info").hide();
					$("#braintree_info").hide();
					$("#card_connect_info,.card_connect_info").hide();
					$("#coreclear2_info").hide();
					$(".register-cc-field-datacap").hide();	
				}
				else if(cc_processor == 'card_connect')
				{
					$("#card_connect_info,.card_connect_info").show();
					$("#square_info").hide();
					$("#emv_info").hide();
					$("#mercury_hosted_checkout_info").hide();
					$("#stripe_info").hide();
					$("#braintree_info").hide();
					$("#coreclear2_info").hide();
					$(".register-cc-field-datacap").hide();	
					$(".emv_terminal_id.register-cc-field").hide();
				}
				else if (cc_processor == 'coreclear2')
				{
					$("#card_connect_info,.card_connect_info").hide();
					$("#square_info").hide();
					$("#emv_info").hide();
					$("#mercury_hosted_checkout_info").hide();
					$("#stripe_info").hide();
					$("#braintree_info").hide();
					
					$("#coreclear2_info").show();
					$("th.emv_terminal_id.register-cc-field").text("<?php echo lang('locations_terminal_id') ?>");
					$(".emv_terminal_id.register-cc-field").show();
					$(".register-cc-field-datacap").hide();	
					
				}
			}
			
			$("#receive_stock_alert").change(check_enable_stock_alert).ready(check_enable_stock_alert);
			
			function check_enable_stock_alert()
			{
				if($("#receive_stock_alert").prop('checked'))
				{
					$("#stock_alert_email_container").show();
					$("#stock_alerts_just_order_level_container").show();
				}
				else
				{
					$("#stock_alert_email_container").hide();
					$("#stock_alerts_just_order_level_container").hide();
				}

			}
			
		});
	

		$(".override_default_tax_checkbox").change(function()
		{
			$(this).parent().parent().next().toggleClass('hidden')
		});

</script>
<?php $this->load->view('partial/footer'); ?>