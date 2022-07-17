<div class="modal-dialog">
	<div class="modal-content">
	
		<div class="modal-header" id="myTabHeader">
			<button type="button" class="close" data-dismiss="modal" aria-label=<?php echo json_encode(lang('common_close')); ?>><span aria-hidden="true" class="ti-close"></span></button>
			<nav>
        <ul id="myTab" class="nav nav-tabs nav-justified">
					<li class="active"><a href="#ShippingMethod" data-toggle="tab"><?php echo lang("deliveries_delivery_method"); ?></a></li>
          <li class=""><a href="#ShippingInfo" data-toggle="tab"><?php echo lang("common_delivery_address"); ?></a></li>
					<li class=""><a href="#ShippingCost" data-toggle="tab"><?php echo lang("deliveries_delivery_fees"); ?></a></li>
        </ul>
			</nav>
		</div>
		<div class="modal-body" id="myTabModalBody">
		
					<form class="form-horizontal" id="delivery_form" method="post" action="">
				  <div class="tab-content">
						 <div class="tab-pane active" id="ShippingMethod">
									
							<ul id="pickup_or_delivery" class="nav nav-pills nav-justified well">
								<li role="presentation" data-value="0" class="<?php echo !$delivery_info['is_pickup'] ? 'active' : '' ?> dropdown piluku-dropdown">
										
										<?php
											$first_provider = current($providers_with_methods);
											$default_time_in_days = 1;
											
											if($first_provider)
											{
												foreach($first_provider['methods'] as $method)
												{
													if($method['is_default'] == 1)
													{
														$default_time_in_days = $method['time_in_days'];
													}
												}
											}
																
											$today = date(get_date_format().' '.get_time_format());
											$estiamted_delivery = date(get_date_format().' '.get_time_format(), strtotime('+'.$default_time_in_days.' days'));				
											
										?>
										
								    <a <?php echo count($providers_with_methods) > 1 ? 'class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false" ' : 'href="#deliveryFields" data-toggle="pill" ' ?>
								       <?php echo isset($first_provider['name']) ? 'data-value="'. $first_provider['id']. '" >' . $first_provider['name'] . (count($providers_with_methods) > 1 ?  '<span class="caret"></span>' : '') : "> Delivery"; ?>
								    </a>
										
								    <ul class="dropdown-menu delivery-menu">
											
											<?php 
												foreach($providers_with_methods as $provider)
												{
													$id = $provider['id'];
													$name = $provider['name'];
												
													if ($first_provider['id'] == $id)
													{
														echo "<li class='". (!$delivery_info['is_pickup'] ? 'active' : '')  . "'><a href='#deliveryFields' data-value='$id' data-toggle='pill'>$name</a></li>";
													
													} else {
														
														echo "<li><a href='#deliveryFields' data-value='$id' data-toggle='pill'>$name</a></li>";
														
													}
												
												}
											?>
								   	</ul>
								  </li>
							  <li data-value="1" role="presentation" class="<?php echo $delivery_info['is_pickup'] ? 'active' : '' ?>"><a id="pickup" href="#pickupFields" data-toggle="pill"><?php echo lang("deliveries_in_store_pickup"); ?></a></li>
							</ul>
						
							<div class="tab-content">
							  <div class="tab-pane fade <?php echo !$delivery_info['is_pickup'] ? 'active in' : '' ?>" id="deliveryFields">
										
										<?php
																					
											foreach($providers_with_methods as $provider)
											{
												$provider_id = $provider["id"];
												
												if($first_provider['id'] == $provider_id)
												{
													echo "<div data-value='$provider_id' class='form-group delivery-rate row'>";
												} else {
													echo "<div data-value='$provider_id' class='form-group delivery-rate row hidden'>";
												}
											
											  echo form_label(lang("deliveries_shipping_rate").':', 'carrier',array('class'=>'col-sm-3 col-md-3 col-lg-3 control-label wide'));
											
												echo "<div  class='btn-group col-sm-9 col-md-9 col-lg-9' role='group' data-toggle='buttons' aria-label='Shipping Methods'>";
											
												foreach($provider['methods'] as $method)
												{
													$method_id = $method['id'];
													$method_name = $method['name'];
													$is_default = $method['is_default'];
													$time_in_days = $method['time_in_days'];
													$fee = $method['fee'];
													
													if(($is_default == 1 && !isset($delivery_info['shipping_method_id']))|| (isset($delivery_info['shipping_method_id']) && $delivery_info['shipping_method_id'] == $method['id']))
													{
														echo "<a data-is-default='$is_default' data-time-in-days='$time_in_days' data-fee='$fee' class='btn btn-default delivery-rate-btn active'>";			
													} 
													else
													{
														echo "<a data-is-default='$is_default' data-time-in-days='$time_in_days' data-fee='$fee' class='btn btn-default delivery-rate-btn'>";
													}
													
														echo "<input name='delivery_rate' value='$method_id' type='radio'>$method_name</a>";
												}
											
												echo "</div>";
											
												echo "</div>";
											} 
										?>
										
										
										<div class="form-group">
											<?php echo form_label(lang('deliveries_delivery_employee').':', 'delivery_employee_person_id',array('class'=>'col-sm-3 col-md-3 col-lg-3 control-label')); ?>
											<div class="col-sm-9 col-md-9 col-lg-9">
												<?php 
												$employees = array('' => lang('common_none'));

												foreach($this->Employee->get_all()->result() as $employee)
												{
													$employees[$employee->person_id] = $employee->first_name .' '.$employee->last_name;
												}
												echo form_dropdown('delivery_employee_person_id', $employees, $delivery_info['delivery_employee_person_id'], 'class="form-control" id="delivery_employee_person_id"');
												?>
								
											</div>
					
										</div>

									<div class="form-group row">
										<label for="tracking_number" class="col-sm-3 col-md-3 col-lg-3 control-label "><?php echo lang("deliveries_tracking_number"); ?> :</label>
										<div class="col-sm-9 col-md-9 col-lg-9">
												<?php echo form_input(
												array(
													'name' => 'tracking_number',
													'id' => 'tracking_number',
													'class' => 'form-control',
													'value' => $delivery_info['tracking_number'] ? $delivery_info['tracking_number'] : ($delivery_info['sale_id'] ? ($this->config->item('sale_prefix') ? $this->config->item('sale_prefix') : 'POS').$delivery_info['sale_id'] : ( $this->cart->get_previous_receipt_id() ?  ($this->config->item('sale_prefix') ? $this->config->item('sale_prefix') : 'POS').$this->cart->get_previous_receipt_id() : '') )
												)
											); ?>
										</div>
						 			</div>	
										
									<div class="form-group row">
											<label for="estimated_shipping_date" class="col-sm-3 col-md-3 col-lg-3 control-label "><?php echo lang("deliveries_expected_processing"); ?> :</label>
						 					<div class="col-sm-9 col-md-9 col-lg-9">
						 					    <div class="input-group date">
						 							<span class="input-group-addon bg"><i class="ion ion-ios-calendar-outline"></i></span>
						 							<?php echo form_input(array(
					 						        'name'=>'estimated_shipping_date',
					 						        'id'=>'estimated_shipping_date',
					 										'class'=>'form-control datepicker',
					 						        'value'=> isset($delivery_info['estimated_shipping_date']) ? date(get_date_format().' '.get_time_format(), strtotime($delivery_info['estimated_shipping_date'])) : ''
													));?>
						 					    </div>
						 					</div>
						 			</div>
						
					 				<div class="form-group row">
										<label for="estimated_delivery_date" class="col-sm-3 col-md-3 col-lg-3 control-label "><?php echo lang("deliveries_expected_delivery"); ?> :</label>
					 					<div class="col-sm-9 col-md-9 col-lg-9">
					 					    <div class="input-group date">
					 							<span class="input-group-addon bg"><i class="ion ion-ios-calendar-outline"></i></span>
					 							<?php echo form_input(array(
					 						        'name'=>'estimated_delivery_date',
					 						        'id'=>'estimated_delivery_date',
					 										'class'=>'form-control datepicker',
					 						        'value'=> isset($delivery_info['estimated_delivery_or_pickup_date']) ? date(get_date_format().' '.get_time_format(), strtotime($delivery_info['estimated_delivery_or_pickup_date'])) : ''
					 						    ));?> 
					 					    </div>
					 				    </div>
					 				</div>
									
							  </div>
							  <div class="tab-pane <?php echo $delivery_info['is_pickup'] ? 'active in' : '' ?> fade" id="pickupFields">
									
					 				<div class="form-group row">
										<label for="pick_up_date" class="col-sm-3 col-md-3 col-lg-3 control-label "><?php echo lang('deliveries_expected_pick_up') ?> :</label>
					 					<!-- <?php echo form_label('In-Store Pickup '.':', 'pick_up_date',array('class'=>'col-sm-3 col-md-3 col-lg-3 control-label text-info wide')); ?> -->
					 					<div class="col-sm-9 col-md-9 col-lg-9">
					 					    <div class="input-group date" data-date="">
					 							<span class="input-group-addon bg"><i class="ion ion-ios-calendar-outline"></i></span>
					 							<?php echo form_input(array(
					 						        'name'=>'pick_up_date',
					 						        'id'=>'pick_up_date',
					 										'class'=>'form-control datepicker',
					 						        'value'=> isset($delivery_info['estimated_delivery_or_pickup_date']) ? date(get_date_format().' '.get_time_format(), strtotime($delivery_info['estimated_delivery_or_pickup_date'])) : ''
					 						    ));?> 
					 					    </div>
					 				    </div>
					 				</div>
							  </div>
								
								<div class="form-group row">	
									<label for="comment" class="col-sm-3 col-md-3 col-lg-3 control-label "><?php echo lang("deliveries_delivery_comment"); ?> :</label>
									<div class="col-sm-9 col-md-9 col-lg-9">
										<textarea name="del_comment" cols="17" rows="5" id="del_comment" class="form-control text-area"><?php echo $delivery_info['comment']; ?></textarea>
									</div>
								</div>
								
								<div class="form-group">
									<?php echo form_label(lang('deliveries_status').':', 'deliveries_status', array('class'=>'col-sm-3 col-md-3 col-lg-3 control-label')); ?>
									<div class="col-sm-9 col-md-9 col-lg-9">
										<?php 
										echo form_dropdown('deliveries_status', $deliveries_status, $delivery_info['status'], 'class="form-control" id="deliveries_status"');
										?>
									</div>
			
								</div>



								<div class="form-group">
									<?php echo form_label(lang('common_category').':', 'category_id', array('class'=>'col-sm-3 col-md-3 col-lg-3 control-label')); ?>
									<div class="col-sm-9 col-md-9 col-lg-9">
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
									<?php echo form_label(lang('common_duration').':', 'duration', array('class'=>'col-sm-3 col-md-3 col-lg-3 control-label')); ?>
									<div class="col-sm-9 col-md-9 col-lg-9">
										<?php

										$duration = array();
										for($i=30; $i<=2400; $i=$i+15){
											$duration[$i] = sprintf('%02d', floor($i / 60)).' Hours, '.sprintf('%02d',($i -   floor($i / 60) * 60))." Minutes";
										}
										
										echo form_dropdown('duration', $duration, $delivery_info['duration'], 'class="form-control" id="duration"'); ?>
						
									</div>
			
								</div>
								
							</div>
						
						</div><!-- end tab-pane -->
						
		         <div class="tab-pane" id="ShippingInfo">
							<div class="form-group row">
								<label for="first_name" class="col-sm-3 col-md-3 col-lg-3 control-label "><?php echo lang("common_first_name"); ?> :</label>
								<div class="col-sm-9 col-md-9 col-lg-9">
									<input type="text" name="first_name" value="<?php echo $delivery_person_info['first_name']; ?>" class="form-control" id="first_name">
								</div>
							</div>

							<div class="form-group row">
								<label for="last_name" class="col-sm-3 col-md-3 col-lg-3 control-label "><?php echo lang("common_last_name"); ?> :</label>
								<div class="col-sm-9 col-md-9 col-lg-9">
									<input type="text" name="last_name" value="<?php echo $delivery_person_info['last_name']; ?>" class="form-control" id="last_name">
								</div>
							</div>
					
							<div class="form-group row">
								<label for="phone_number" class="col-sm-3 col-md-3 col-lg-3 control-label "><?php echo lang("common_phone_number"); ?> :</label>
								<div class="col-sm-9 col-md-9 col-lg-9">
									<input type="text" name="phone_number" value="<?php echo $delivery_person_info['phone_number']; ?>" class="form-control" id="phone_number">
								</div>
							</div>
							
							<div class="form-group row">
								<label for="email" class="col-sm-3 col-md-3 col-lg-3 control-label "><?php echo lang("common_email"); ?> :</label>
								<div class="col-sm-9 col-md-9 col-lg-9">
									<input type="text" name="email" value="<?php echo $delivery_person_info['email']; ?>" class="form-control" id="email">
								</div>
							</div>
							
					
							<div class="form-group row">	
								<label for="address_1" class="col-sm-3 col-md-3 col-lg-3 control-label "><?php echo lang("common_address_1"); ?> :</label>
								<div class="col-sm-9 col-md-9 col-lg-9">
									<input type="text" name="address_1" value="<?php echo $delivery_person_info['address_1']; ?>" class="form-control" id="address_1">
								</div>
							</div>

							<div class="form-group row">	
								<label for="address_2" class="col-sm-3 col-md-3 col-lg-3 control-label "><?php echo lang("common_address_2"); ?> :</label>
								<div class="col-sm-9 col-md-9 col-lg-9">
									<input type="text" name="address_2" value="<?php echo $delivery_person_info['address_2']; ?>" class="form-control" id="address_2">
								</div>
							</div>

							<div class="form-group row">	
								<label for="city" class="col-sm-3 col-md-3 col-lg-3 control-label "><?php echo lang("common_city"); ?> :</label>
								<div class="col-sm-9 col-md-9 col-lg-9">
									<input type="text" name="city" value="<?php echo $delivery_person_info['city']; ?>" class="form-control " id="city">
								</div>
							</div>

							<div class="form-group row">	
								<label for="state" class="col-sm-3 col-md-3 col-lg-3 control-label "><?php echo lang("common_state"); ?> :</label>
								<div class="col-sm-9 col-md-9 col-lg-9">
									<input type="text" name="state" value="<?php echo $delivery_person_info['state']; ?>" class="form-control " id="state">
								</div>
							</div>

							<div class="form-group row">	
								<label for="zip" class="col-sm-3 col-md-3 col-lg-3 control-label "><?php echo lang("common_zip"); ?> :</label>
								<div class="col-sm-9 col-md-9 col-lg-9">
									<input type="text" name="zip" value="<?php echo $delivery_person_info['zip']; ?>" class="form-control " id="zip">
								</div>
							</div>

							<div class="form-group row">	
								<label for="country" class="col-sm-3 col-md-3 col-lg-3 control-label "><?php echo lang("common_country"); ?> :</label>
								<div class="col-sm-9 col-md-9 col-lg-9">
									<input type="text" name="country" value="<?php echo $delivery_person_info['country']; ?>" class="form-control " id="country">
								</div>
							</div>
					
		     	 </div><!-- end tab-pane -->
					 
	 			 	<div class="tab-pane" id="ShippingCost">
		 				<div class="form-group row">
		 					<label for="delivery_fee" class="col-sm-3 col-md-3 col-lg-3 control-label "><?php echo lang("deliveries_delivery_fees"); ?> :</label>
		 					<div class="col-sm-9 col-md-9 col-lg-9">
								<div class="input-group">
								  <span class="input-group-addon">$</span>
		 							<input type="text" name="delivery_fee" value="<?php echo to_currency_no_money($delivery_fee); ?>" class="form-control" id="delivery_fee">
								</div>
		 					</div>
		 				</div>
						
						<div class="form-group row">	
							<?php echo form_label(lang('delivery_shipping_zone').' :', 'shipping_zone_id', array('class'=>'col-sm-3 col-md-3 col-lg-3 control-label')); ?>							
							<div class="col-sm-9 col-md-9 col-lg-9">
							<?php echo form_dropdown('shipping_zone_id', $shipping_zones, $shipping_zone_id , array('id' =>'shipping_zone_id','class' => 'shipping_zone_id form-control'));?>
							</div>
						</div>
						
						<div class="form-group row">	
							<?php echo form_label(lang('common_tax_class').' :', 'tax_class_id',array('class'=>'col-sm-3 col-md-3 col-lg-3 control-label')); ?>							
							<div class="col-sm-9 col-md-9 col-lg-9">
							<?php echo form_dropdown('tax_class_id', $tax_classes, $delivery_tax_group_id , array('id' =>'tax_class_id','class' => 'tax_class_id form-control'));?>
							</div>
						</div>
						
						
			 
			</div>
		</div><!-- end modal-body -->
		<div class="modal-footer">
			<button type="button" id="cancel_delivery" data-dismiss="modal" class="btn btn-default"><?php echo lang('delivery_cancel_delivery'); ?></button>
			<button type="submit" class="btn btn-primary"><?php echo lang('deliveries_update'); ?></button>
			</form>
			
		</div><!-- end modal-footer -->
	</div><!-- end modal-content -->
</div><!-- end modal-dialog -->

<script type='text/javascript'>
	var shipping_zone_info = <?php echo json_encode($shipping_zone_info); ?>;
	var shipping_zone_id = <?php echo json_encode($shipping_zone_id); ?>;
	var zip_zones = <?php echo json_encode($zip_zones); ?>;
	
	$(document).ready(function()
	{
			date_time_picker_field($('.datepicker'), JS_DATE_FORMAT+" "+JS_TIME_FORMAT);
			
			function reset_form()
			{
				$('.btn-group>.active').removeClass('active');
				reset_rate();
			}
			
			$("#pickup_or_delivery li > a ").not(".dropdown-toggle").on('click', function() {
				reset_form();
				
				$zone_id_field = $("#shipping_zone_id");
				
				if($(this).attr('id') === 'pickup')
				{
					$fee_field = $("#delivery_fee");
					$fee_field.data('rate-fee', 0);
					$fee_field.val(0);
					shipping_zone_id = $zone_id_field.val();
					$zone_id_field.val(0);
				}
				else
				{
					if($(this).data('toggle') !== 'dropdown')
					{
						var provider_id = $(this).data("value");
				
						$(".delivery-rate").each(function() {
							if($(this).data("value") === provider_id)
							{
								$(this).removeClass("hidden");
								$(this).find('.btn-group > a').each(function() {
									if($(this).data('is-default') === 1)
									{
										$(this).addClass('active');
										$(this).trigger('click');
									}
								});
							}
							else
							{
								$(this).addClass("hidden");
							}
					
						});
				
					  $(this).parents(".dropdown").find("a").eq(0).html($(this).text() + ' <span class="caret"></span>');
					  $(this).parents(".dropdown").find("a").eq(0).val($(this).data('value'));	
					}
					
					$zone_id_field.val(shipping_zone_id);
				}

			});
			
			function zip_change() 
			{
				var zip = $('#zip').val();
				
				for(var zip_key in zip_zones)
				{
					var zone = zip_zones[zip_key];
					var search = new RegExp(zip_key, 'g');
					if (zip.match(search))
					{
						$('#shipping_zone_id').val(zone).trigger('change');
						break;
					}
				}
			}
			
			$('#zip').on('input', function() {
				zip_change();
			});
			
			function zone_change(init_load) {
				if(shipping_zone_info[$('#shipping_zone_id').val()] !== undefined)
				{
					$("#delivery_fee").data('zone-fee', parseFloat(shipping_zone_info[$('#shipping_zone_id').val()]['fee']));
					
					if (!init_load)
					{
						$("#tax_class_id").val(shipping_zone_info[$('#shipping_zone_id').val()]['tax_class_id']);
					}
				} else {
						$("#delivery_fee").data('zone-fee', 0);
				}
			};
			
			zone_change();
			
			$('#shipping_zone_id').on('change', function() {
				zone_change(false);
				set_rate();
			});
			
			zone_change(true);
			
			function reset_rate()
			{
				$('#pick_up_date').data("DateTimePicker").clear();
				$('#estimated_shipping_date').data("DateTimePicker").clear();
				$('#estimated_delivery_date').data("DateTimePicker").clear();
				$("#delivery_fee").val(0);
				$('#delivery_fee').data('rate-fee', 0);
			}
			
			function set_rate($r) {
				$fee_field = $("#delivery_fee");
				var is_pickup = $("#pickup_or_delivery li.active").data('value');
				var fee;

				if(!is_pickup || $r)
				{
					var init;
					if($r)
					{
						init = false;
					} else {
						init = true;
					}
					
					$r = $r || $(".delivery-rate-btn.active");
					
					$est_shipping_picker = $('#estimated_shipping_date').data("DateTimePicker");
					$est_delivery_picker = $('#estimated_delivery_date').data("DateTimePicker");

					if($r.data("time-in-days") && $r.data("time-in-days").length !== 0)
					{
						
						var defaultDate = moment();
						defaultDate.set('hour', 0);
						defaultDate.set('minute', 0);
						
						if(!init)
						{ //we dont want to overwrite dates on update
							$est_shipping_picker.date(defaultDate);
						}

						defaultDate.add($r.data("time-in-days"), 'days');
						
						if(!init)
						{ //we dont want to overwrite dates on update
							$est_delivery_picker.date(defaultDate);
						}
						
					}

					fee = $r.length !== 0 ? parseFloat($r.data("fee")) : 0;

					$fee_field.data('rate-fee', fee);

					if(!isNaN($fee_field.data('zone-fee')))
					{
						fee += $fee_field.data('zone-fee');
					}
				} else {
					//pickup
					fee = 0;
				}

				$fee_field.val(fee);
				
			}
			
			<?php 
			if(!$delivery_fee) 
			{ 
			?>
				set_rate();
			<?php
			}
			?>
			if(shipping_zone_info[$('#shipping_zone_id').val()] !== undefined)
			{
				$("#delivery_fee").data('zone-fee', parseFloat(shipping_zone_info[$('#shipping_zone_id').val()]['fee']));
			} else {
				$("#delivery_fee").data('zone-fee', 0);
			}
			
			if(!$("#delivery_fee").val())
			{
				set_rate();
			}
			
			$(".delivery-rate-btn").on("click", function() {
				set_rate($(this));
			});
			
	});
	
	var saved = false;
	
	$("#cancel_delivery").click(function()
	{
		$.post(<?php echo json_encode(site_url('sales/set_delivery'));?>,{delivery:0}, function(response)
		{
			$("#register_container").html(response);
		});
	});
	
	$("#delivery_form").submit(function()
	{
		$('#myModal').modal('hide');
		
		$.post('<?php echo site_url("sales/set_delivery_info");?>', { delivery_info: get_delivery_info(), delivery_person_info: get_delivery_person_info(), delivery_tax_group_id: get_delivery_tax_group_id(), delivery_fee: get_delivery_fee()}, function(response)
		{
			$("#register_container").html(response);
		});			
	
		saved = true;
		return false;
	});
	$(".btn-primary").off("click");
	$('#myModal').off('hidden.bs.modal');
	$('#myModal').on('hidden.bs.modal', function (e) {
		if(!saved)
		{
			$("#toggle_delivery").trigger("click");
		}
	});
	
	function get_delivery_fee()
	{
		return $('#delivery_fee').val();
	}
	
	function get_delivery_tax_group_id() {
		return $('#tax_class_id').val();
	}
	
	function get_delivery_info() {
		var is_pickup = $("#pickup_or_delivery li.active").data('value');
		
		var delivery_info = {
			is_pickup: is_pickup,
			comment: $('#del_comment').val(),
			delivery_employee_person_id: $("#delivery_employee_person_id").val(),
		};
		
		if(!is_pickup)
		{
			if($(".delivery-rate-btn.active").find('input').val())
			{
				delivery_info.shipping_method_id = $(".delivery-rate-btn.active").find('input').val();
			}
			
			if($("#shipping_zone_id").val())
			{
				delivery_info.shipping_zone_id = $("#shipping_zone_id").val();
			}
			
			delivery_info.tracking_number = $('#tracking_number').val();
		}
		
		if($('#estimated_shipping_date').val())
		{
			delivery_info.estimated_shipping_date = is_pickup ?  null : $('#estimated_shipping_date').data("DateTimePicker").date().format('YYYY-MM-DD HH:mm:ss');
		}
		
		if($('#pick_up_date').val() || $('#estimated_delivery_date').val())
		{
			delivery_info.estimated_delivery_or_pickup_date = is_pickup ? $('#pick_up_date').data("DateTimePicker").date().format('YYYY-MM-DD HH:mm:ss') : $('#estimated_delivery_date').data("DateTimePicker").date().format('YYYY-MM-DD HH:mm:ss');
		}
		
		if ($('.delivery-rate-btn.active input').eq(0).val())
		{
			delivery_info['shipping_method_id'] = $('.delivery-rate-btn.active input').eq(0).val();
		}
		
		delivery_info['status'] = $('#deliveries_status').val();
	
		delivery_info['category_id'] = $('#category_id').val();

		delivery_info['duration'] = $('#duration').val();
		
		
		return delivery_info;
		
	}
	
	function get_delivery_person_info() {
		
		var delivery_person_info = {
			first_name: $('#first_name').val(),
			last_name: $('#last_name').val(),
			phone_number: $('#phone_number').val(),
			email: $('#email').val(),
			address_1: $('#address_1').val(),
			address_2: $('#address_2').val(),
			city: $('#city').val(),
			state: $('#state').val(),
			zip: $('#zip').val(),
			country: $('#country').val(),
		}
		
		return delivery_person_info;
		
	}
	
</script>