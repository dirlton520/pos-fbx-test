<?php $this->load->view("partial/header"); ?>
<?php echo form_open_multipart('deliveries/save/'.$this->uri->segment('3').'?redirect='.$this->input->get('redirect'),array('id'=>'edit_delivery_form','class'=>'form-horizontal')); 	?>
		<div class="panel panel-piluku">
			<div class="panel-heading">
				<?php echo lang("deliveries_basic_info"); ?> (<small><?php echo lang('common_fields_required_message'); ?></small>)
			</div>
			<?php $this_sale_info = $this->Sale->get_info($delivery_info['sale_id'])->row(); ?>
			<div class="spinner" id="grid-loader" style="display:none">
				<div class="rect1"></div>
				<div class="rect2"></div>
				<div class="rect3"></div>
			</div>
			<div class="panel-body">
			
				<div class="<?php echo ($this_sale_info) ? "col-md-12" : "col-md-6"; ?>">
					<?php
			
					if($this_sale_info){
					if (!$this_sale_info->is_ecommerce)
					{
					?>
					<div class="form-group">
						<?php echo form_label(lang('common_actions').':', 'edit_sale',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label wide')); ?>
						<div class="col-sm-9 col-md-9 col-lg-10">
							<?php
								echo anchor(site_url('sales/change_sale/'.$delivery_info['sale_id']), lang('deliveries_edit_sale'), array('id' => 'edit_sale', 'class' => 'btn btn-primary'));
							?>
						</div>
					</div>
					<?php }} ?>
					
					<div class="form-group">
						<?php echo form_label(lang('deliveries_delivery_employee').':', 'status',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label wide')); ?>
						<div class="col-sm-9 col-md-9 col-lg-10">
							<?php 
							
							$employees = array('' => lang('common_none'));

							foreach($this->Employee->get_all()->result() as $employee)
							{
								$employees[$employee->person_id] = $employee->first_name .' '.$employee->last_name;
							}
							
							echo form_dropdown('delivery_employee_person_id', $employees, $delivery_info['delivery_employee_person_id'] ? $delivery_info['delivery_employee_person_id'] : ($this->config->item('default_employee_for_deliveries') ? $this->config->item('default_employee_for_deliveries') : $this->Employee->get_logged_in_employee_info()->person_id), 'class="form-inps" id="delivery_employee_person_id"');
							?>
									
						</div>
						
					</div>
					
					
					<div class="form-group">
						<?php echo form_label(lang('deliveries_status').':', 'status',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label wide')); ?>
						<div class="col-sm-9 col-md-9 col-lg-10">
							<?php 
							$status =$delivery_info['status']; 

							echo form_dropdown('status', $change_status_array, $status, 'class="form-control form-inps" id="status"'); 	
							?>
						</div>
						
					</div>


					<div class="form-group">
						<?php echo form_label(lang('common_category').':', 'category_id', array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label')); ?>
						<div class="col-sm-9 col-md-9 col-lg-10">
							<?php 


							$cats = array('' => lang('common_none'));

							foreach($categories as $key => $category)
							{
								$cats[$key] = $category["name"];
							}

							echo form_dropdown('category_id', $cats, $delivery_info['category_id'], 'class="form-control" id="category_id"'); ?>

						</div>

					</div>


					<div class="form-group">
						<?php echo form_label(lang('common_duration').':', 'duration', array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label')); ?>
						<div class="col-sm-9 col-md-9 col-lg-10">
							<?php

							$duration = array();
							for($i=30; $i<=2400; $i=$i+15){
								$duration[$i] = sprintf('%02d', floor($i / 60)).' Hours, '.sprintf('%02d',($i -   floor($i / 60) * 60))." Minutes";
							}
							
							echo form_dropdown('duration', $duration, $delivery_info['duration'], 'class="form-control" id="duration"'); ?>

						</div>

					</div>


					<div class="form-group">
						<?php echo form_label(lang('common_location').':', 'location_id', array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label')); ?>
						<div class="col-sm-9 col-md-9 col-lg-10">
							<?php

							$location_array = array();
							foreach($locations as $location){
								$location_array[$location->location_id] = $location->name;
							}
							
							echo form_dropdown('location_id', $location_array, $delivery_info['location_id'], 'class="form-control" id="location_id"'); ?>

						</div>

					</div>

					
					<div class="form-group">
						<?php echo form_label(lang('common_first_name').':', 'first_name',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label wide')); ?>
						<div class="col-sm-9 col-md-9 col-lg-10">
							<?php echo form_input(array(
								'name'=>'first_name',
								'id'=>'first_name',
								'class'=>'form-control form-inps',
								'value'=>$delivery_person_info['first_name'])
							);?>
						</div>
					</div>
					
					<div class="form-group">
						<?php echo form_label(lang('common_last_name').':', 'last_name',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label wide')); ?>
						<div class="col-sm-9 col-md-9 col-lg-10">
							<?php echo form_input(array(
								'name'=>'last_name',
								'id'=>'last_name',
								'class'=>'form-control form-inps',
								'value'=>$delivery_person_info['last_name'])
							);?>
						</div>
					</div>
					
					
					<div class="form-group">
						<?php echo form_label(lang('common_email').':', 'email',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label wide')); ?>
						<div class="col-sm-9 col-md-9 col-lg-10">
							<?php echo form_input(array(
								'name'=>'email',
								'id'=>'email',
								'class'=>'form-control form-inps',
								'value'=>$delivery_person_info['email'])
							);?>
						</div>
					</div>
					
					
					<div class="form-group">
						<?php echo form_label(lang('common_phone_number').':', 'phone_number',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label wide')); ?>
						<div class="col-sm-9 col-md-9 col-lg-10">
							<?php echo form_input(array(
								'name'=>'phone_number',
								'id'=>'phone_number',
								'class'=>'form-control form-inps',
								'value'=>$delivery_person_info['phone_number'])
							);?>
						</div>
					</div>
					
					
					
					<div class="form-group">
						<?php echo form_label(lang('common_address_1').':', 'address_1',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label wide')); ?>
						<div class="col-sm-9 col-md-9 col-lg-10">
							<?php echo form_input(array(
								'name'=>'address_1',
								'id'=>'address_1',
								'class'=>'form-control form-inps',
								'value'=>$delivery_person_info['address_1'])
							);?>
						</div>
					</div>
					
					<div class="form-group">
						<?php echo form_label(lang('common_address_2').':', 'address_2',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label wide')); ?>
						<div class="col-sm-9 col-md-9 col-lg-10">
							<?php echo form_input(array(
								'name'=>'address_2',
								'id'=>'address_2',
								'class'=>'form-control form-inps',
								'value'=>$delivery_person_info['address_2'])
							);?>
						</div>
					</div>
					
					<div class="form-group">
						<?php echo form_label(lang('common_city').':', 'city',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label wide')); ?>
						<div class="col-sm-9 col-md-9 col-lg-10">
							<?php echo form_input(array(
								'name'=>'city',
								'id'=>'city',
								'class'=>'form-control form-inps',
								'value'=>$delivery_person_info['city'])
							);?>
						</div>
					</div>
					
					<div class="form-group">
						<?php echo form_label(lang('common_state').':', 'state',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label wide')); ?>
						<div class="col-sm-9 col-md-9 col-lg-10">
							<?php echo form_input(array(
								'name'=>'state',
								'id'=>'state',
								'class'=>'form-control form-inps',
								'value'=>$delivery_person_info['state'])
							);?>
						</div>
					</div>
					
					<div class="form-group">
						<?php echo form_label(lang('common_zip').':', 'zip',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label wide')); ?>
						<div class="col-sm-9 col-md-9 col-lg-10">
							<?php echo form_input(array(
								'name'=>'zip',
								'id'=>'zip',
								'class'=>'form-control form-inps',
								'value'=>$delivery_person_info['zip'])
							);?>
						</div>
					</div>
					
					<div class="form-group">
						<?php echo form_label(lang('common_country').':', 'country',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label wide')); ?>
						<div class="col-sm-9 col-md-9 col-lg-10">
							<?php echo form_input(array(
								'name'=>'country',
								'id'=>'country',
								'class'=>'form-control form-inps',
								'value'=>$delivery_person_info['country'])
							);?>
						</div>
					</div>
					
					<div class="form-group">
						<?php echo form_label(lang('deliveries_tracking_number').':', 'tracking_number',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label wide')); ?>
						<div class="col-sm-9 col-md-9 col-lg-10">
							<?php echo form_input(array(
								'name'=>'tracking_number',
								'id'=>'tracking_number',
								'class'=>'form-control form-inps',
								'value'=>$delivery_info['tracking_number'] ? $delivery_info['tracking_number'] : ($delivery_info['sale_id'] ? $this->config->item('sale_prefix').$delivery_info['sale_id'] : '') 
								)
							);?>
						</div>
					</div>
					
						<div class="form-group">	
					<?php echo form_label(lang('common_comments').':', 'comment',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label ')); ?>
						<div class="col-sm-9 col-md-9 col-lg-10">
						<?php echo form_textarea(array(
							'name'=>'comment',
							'id'=>'comment',
							'class'=>'form-control text-area',
							'value'=>$delivery_info['comment'],
							'rows'=>'5',
							'cols'=>'17')		
						);?>
						</div>
					</div>
					
					
					
					<div id="is_pickup_field" class="form-group">	
						<?php echo form_label(lang('deliveries_is_pickup').':', 'is_pickup',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label')); ?>
						<div class="col-sm-9 col-md-9 col-lg-10">
						
							<?php 	
							
							$data = array(
											'class'					=> 'form-control form-inps',
											'readonly'			=> true,
									'id'            => 'is_pickup',
									'value'         => $delivery_info['is_pickup'] === '1' ?  lang('common_yes') : lang('common_no'),
											'data-toggle'		=> 'tooltip',
											'data-placement' => 'top',
											'title' 				=> lang('deliveries_edit_sale_tool_tip')
							);

							echo form_input($data);

							?>
		
						</div>	
					</div>
					
					<div id="provider_field" class="form-group <?php echo $delivery_info['is_pickup'] === '1' ? 'hidden' : '' ?>">
						<?php echo form_label(lang('deliveries_shipping_provider').':', 'shipping_provider',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label wide')); ?>
						<div class="col-sm-9 col-md-9 col-lg-10">
							<?php 
							$selected_method = $delivery_info['shipping_method_id']; 
							
							
							$providers = array();
							$providers[''] = lang('common_none');
							
							$selected_provider = '';
							foreach($providers_with_methods as $provider)
							{
								if($selected_provider === '')
								{
									foreach($provider['methods'] as $method)
									{
										if($method['id'] == $selected_method)
										{
											$selected_provider = $method['shipping_provider_id'];
											break;
										}	
									}
								}
								
								
								$providers[$provider['id']] = $provider['name'];
							}
							
							$data = array(
											'class'					=> 'form-control form-inps',
											'readonly'			=> true,
									'id'            => 'provider',
									'value'         => $providers[$selected_provider],
											'data-toggle'		=> 'tooltip',
											'data-placement' => 'top',
											'title' 				=> lang('deliveries_edit_sale_tool_tip')
							);

							echo form_input($data);
							
							?>
							
						</div>
					</div>
					
					<div id="method_field" class="form-group <?php echo $delivery_info['is_pickup'] === '1' ? 'hidden' : '' ?>">
						<?php echo form_label(lang('deliveries_shipping_method').':', 'shipping_method',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label wide')); ?>
						<div class="col-sm-9 col-md-9 col-lg-10">
							<?php
							
							$selected_method = $delivery_info['shipping_method_id']; 
							
							$methods = array();
							$methods[''] = lang('common_none');
							
							foreach($providers_with_methods as $provider)
							{
								if($provider['id'] == $selected_provider)
								{
									foreach($provider['methods'] as $method)
									{
										$methods[$method['id']] = $method['name'];
									}
								}
							}
							
							$data = array(
											'class'					=> 'form-control form-inps',
											'readonly'			=> true,
									'id'            => 'method',
									'value'         => $methods[$selected_method],
											'data-toggle'		=> 'tooltip',
											'data-placement' => 'top',
											'title' 				=> lang('deliveries_edit_sale_tool_tip')
							);

							echo form_input($data);
							?>
							
						</div>
					</div>
					
					<div id="estimated_shipping_date_field" class="form-group <?php echo $delivery_info['is_pickup'] === '1' ? 'hidden' : '' ?>">
						<?php echo form_label(lang('deliveries_estimated_shipping_date').':', 'estimated_shipping_date',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label text-info wide')); ?>
						<div class="col-sm-9 col-md-9 col-lg-10">
							<div class="input-group date" data-date="<?php echo $delivery_info['estimated_shipping_date'] ? date(get_date_format(), strtotime($delivery_info['estimated_shipping_date'])) : ''; ?>">
								<span class="input-group-addon bg"><i class="ion ion-ios-calendar-outline"></i></span>
								<?php echo form_input(array(
									'name'=>'estimated_shipping_date',
									'id'=>'estimated_shipping_date',
											'class'=>'form-control datepicker',
									'value'=>$delivery_info['estimated_shipping_date'] ? date(get_date_format().' '.get_time_format(), strtotime($delivery_info['estimated_shipping_date'])) : ''
								));?> 
							</div>
						</div>
					</div>
					
					<div class="form-group">
						<?php echo form_label(($delivery_info['is_pickup'] === '1' ? lang('deliveries_estimated_pickup_date') : lang('deliveries_estimated_delivery_date')) . ':', 'estimated_delivery_or_pickup_date',array('id' => 'estimated_delivery_or_pickup_date_label', 'class'=>'col-sm-3 col-md-3 col-lg-2 control-label text-info wide')); ?>
						<div class="col-sm-9 col-md-9 col-lg-10">
							<div class="input-group date" data-date="<?php echo $delivery_info['estimated_delivery_or_pickup_date'] ? date(get_date_format(), strtotime($delivery_info['estimated_delivery_or_pickup_date'])) : ''; ?>">
								<span class="input-group-addon bg"><i class="ion ion-ios-calendar-outline"></i></span>
								<?php echo form_input(array(
								'name' => 'estimated_delivery_or_pickup_date',
								'id' => 'estimated_delivery_or_pickup_date',
										'class' => 'form-control datepicker',
								'value' => $delivery_info['estimated_delivery_or_pickup_date'] ? date(get_date_format().' '.get_time_format(), strtotime($delivery_info['estimated_delivery_or_pickup_date'])) : ''
								));?> 
							</div>
						</div>
					</div>
					
					<div id="actual_shipping_date_field" class="form-group <?php echo $delivery_info['is_pickup'] === '1' ? 'hidden' : '' ?>">
						<?php echo form_label(lang('deliveries_actual_shipping_date').':', 'actual_shipping_date',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label text-info wide')); ?>
						<div class="col-sm-9 col-md-9 col-lg-10">
							<div class="input-group date" data-date="<?php echo $delivery_info['actual_shipping_date'] ? date(get_date_format(), strtotime($delivery_info['actual_shipping_date'])) : ''; ?>">
								<span class="input-group-addon bg"><i class="ion ion-ios-calendar-outline"></i></span>
								<?php echo form_input(array(
									'name'=>'actual_shipping_date',
									'id'=>'actual_shipping_date',
											'class'=>'form-control datepicker',
									'value'=>$delivery_info['actual_shipping_date'] ? date(get_date_format().' '.get_time_format(), strtotime($delivery_info['actual_shipping_date'])) : ''
								));?> 
							</div>
						</div>
					</div>
					
					<div class="form-group">
						<?php echo form_label(($delivery_info['is_pickup'] === '1' ? lang('deliveries_actual_pickup_date') : lang('deliveries_actual_delivery_date')).':', 'actual_delivery_or_pickup_date',array('id' => 'actual_delivery_or_pickup_date_label', 'class'=>'col-sm-3 col-md-3 col-lg-2 control-label text-info wide')); ?>
						<div class="col-sm-9 col-md-9 col-lg-10">
							<div class="input-group date" data-date="<?php echo $delivery_info['actual_delivery_or_pickup_date'] ? date(get_date_format(), strtotime($delivery_info['actual_delivery_or_pickup_date'])) : ''; ?>">
								<span class="input-group-addon bg"><i class="ion ion-ios-calendar-outline"></i></span>
								<?php echo form_input(array(
								'name' => 'actual_delivery_or_pickup_date',
								'id' => 'actual_delivery_or_pickup_date',
										'class' => 'form-control datepicker',
								'value' => $delivery_info['actual_delivery_or_pickup_date'] ? date(get_date_format().' '.get_time_format(), strtotime($delivery_info['actual_delivery_or_pickup_date'])) : ''
								));?> 
							</div>
						</div>
					</div>

							
					<div class="form-controls form-actions">	
						<ul class="list-inline pull-right">
							<li>
								<?php
									echo form_submit(array(
										'name'=>'submitf',
										'id'=>'submitf',
										'value'=>lang('common_save'),
										'class'=>'submit_button floating-button btn btn-primary')
									);
								?>
							</li>
						</ul>
					</div>
				</div>
				
				<?php if($this_sale_info){?>
				
				<?php } else {?>
				<div class="col-md-6">
					<div class="input-group contacts register-input-group">
						<span class="input-group-addon">
							<?php echo anchor("items/view/-1?redirect=deliveries/index/1&progression=1", "<i class='icon ti-pencil-alt'></i>", array('class' => 'none add-new-item', 'title' => lang('common_new_item'), 'id' => 'new-item', 'tabindex' => '-1')); ?>
						</span>
						<input type="text" id="item" name="item" data-is_open="false" class="add-item-input pull-left keyboardTop form-control" placeholder="<?php echo lang('common_start_typing_item_name'); ?>" data-title="<?php echo lang('common_item_name'); ?>">
					</div>

					<div id="item_container"><?php echo $delivery_items; ?></div>
				</div>
				<?php } ?>
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
				
				
			</div> <!-- close pannel body -->
			<?php echo form_close(); ?>
			<script type="text/javascript">
				$(document).ready(function(){
				    $('[data-toggle="tooltip"]').tooltip(); 
					$("#delivery_employee_person_id").select2();
				});
				
				date_time_picker_field($('.datepicker'), JS_DATE_FORMAT+ " "+JS_TIME_FORMAT);
							
				<?php if($this_sale_info){?>
				
				<?php } else {?>
					setTimeout(function() {
						$('#item').focus();
					}, 10);
				<?php } ?>
				
				$("#item").bind("keydown click focus", function(event) {
					if(event.keyCode == 13) {
						event.preventDefault();
						if($(this).data("is_open") == false){
							$('#grid-loader').show();
							var item_str = $(this).val();
							var item_label = "";
							$.ajax({
								url: '<?php echo site_url("deliveries/add_item"); ?>',
								type: 'POST',
								data: {item: item_str, item_label: "", is_manual: 1},
							}).done(function( response ) {
								//if item has variations
								//need to add code for variation selection

								$("#item_container").html(response);
								
								$("#item").val("");
								$('#grid-loader').hide();
							}).success(function(res){
								
							});;
						}
						return false;
					}
				});



				$(document).ready(function(){
					if ($("#item").length) {                                                                                                                           

						<?php
						if ($this->Employee->has_module_action_permission('sales', 'allow_item_search_suggestions_for_sales', $this->Employee->get_logged_in_employee_info()->person_id)) {
						?>
							$("#item").autocomplete({
								source: '<?php echo site_url("deliveries/item_search"); ?>',
								delay: 500,
								autoFocus: true,
								minLength: 0,
								selectFirst: true,
								select: function(event, ui) {
									$('#grid-loader').show();
									var item_str = decodeHtml(ui.item.value);
									var item_label = decodeHtml(ui.item.label);
									$.ajax({
										url: '<?php echo site_url("deliveries/add_item"); ?>',
										type: 'POST',
										data: {item: item_str, item_label: item_label, is_manual: 0},
									}).done(function( response ) {
										$("#item_container").html(response);
										$("#item").val("");
										$('#item').focus();
										$('#grid-loader').hide();
									});
								},
							}).data("ui-autocomplete")._renderItem = function(ul, item) {
								return $("<li class='item-suggestions'></li>")
									.data("item.autocomplete", item)
									.append('<a class="suggest-item"><div class="item-image">' +
										'<img src="' + item.image + '" alt="">' +
										'</div>' +
										'<div class="details">' +
										'<div class="name">' +
										decodeHtml(item.label) +
										'</div>' +
										'<span class="attributes">' + '<?php echo lang("common_category"); ?>' + ' : <span class="value">' + (item.category ? item.category : <?php echo json_encode(lang('common_none')); ?>) + '</span></span>' +
										(typeof item.quantity !== 'undefined' && item.quantity !== null ? '<span class="attributes">' + '<?php echo lang("common_quantity"); ?>' + ' <span class="value">' + item.quantity + '</span></span>' : '') +
										(item.attributes ? '<span class="attributes">' + '<?php echo lang("common_attributes"); ?>' + ' : <span class="value">' + item.attributes + '</span></span>' : '') +
										'</div>')
									.appendTo(ul);
							};
						<?php } ?>
						}

						$('#add_item_form').bind('keypress', function(e) {
							if (e.keyCode == 13) {
								e.preventDefault();
							}
						});

						$(document).on('click', '.delete-item', function(e){
							e.preventDefault();
							var item_id = $(this).data('item_id');
							var item_kit_id = $(this).data('item_kit_id');
							var item_variation_id = $(this).data('item_variation_id');
							$('#grid-loader').show();
							$.ajax({
								url: '<?php echo site_url("deliveries/delete_delivery_item"); ?>',
								type: 'POST',
								data: {item_id: item_id, item_kit_id: item_kit_id, item_variation_id: item_variation_id},
								}).done(function( response ) {
								$("#item_container").html(response);
								$('#item').focus();
								$('#grid-loader').hide();
							});
						});

						$("#item").bind('autocompleteopen', function(event, ui) {
							$(this).data('is_open', true);
						});

						$("#item").bind('autocompleteclose', function(event, ui) {
							$(this).data('is_open', false);
						});

						$(document).on("click", ".popup_button", function(){
							var item_id  = $(this).data("item_id");
							var item_variation_id = $(this).data("item_variation_id");
							$('#grid-loader').show();

							$.ajax({
								url: '<?php echo site_url("deliveries/add_item"); ?>',
								type: 'POST',
								cache: false,
								data: {item: "na", item_label: "na", is_manual: 0, item_id: item_id, item_variation_id: item_variation_id},
							}).done(function( response ) {
								$("#item_container").html(response);
								$("#item").val("");
								$('#choose_var').modal('hide');
								$('body').removeClass('modal-open');
								$('#item').focus();
								$('#grid-loader').hide();
							});
						});


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

				});
			</script>
<?php $this->load->view("partial/footer"); ?>
		
