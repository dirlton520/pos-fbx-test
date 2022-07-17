<?php $this->load->view("partial/header"); ?>
<div class="spinner" id="grid-loader" style="display:none">
	<div class="rect1"></div>
	<div class="rect2"></div>
	<div class="rect3"></div>
</div>
<!-- Notes Modal -->
<div class="modal fade sale_item_notes_modal" id="sale_item_notes_modal" tabindex="-1" role="dialog" aria-labelledby="sale_item_note" aria-hidden="true">
	<div class="modal-dialog customer-recent-sales" style = "width: 500px;">
		<div class="modal-content">
	        <div class="modal-header">
	          	<button type="button" class="close" data-dismiss="modal" aria-label=<?php echo json_encode(lang('common_close')); ?>><span style = "font-size: 30px;" aria-hidden="true">&times;</span></button>
	          	<h5 style = "font-size: 20px;text-transform: none;" class="modal-title"><?php echo lang('sales_enter_note'); ?></h5>
	        </div>
	        <div class="modal-body">
				<?php echo form_open_multipart('work_orders/save_repaired_item_notes/',array('id'=>'sale_item_notes_form')); ?>
						
				<div class="form-group">
					<?php echo form_label(lang('sales_note'), 'sale_item_note',array('class'=>'control-label wide')); ?>
					<?php echo form_input(array(
						'type'  => 'text',
						'name'  => 'sale_item_note',
						'id'    => 'sale_item_note',
						'class'=> 'form-control form-inps input_radius',
					)); ?>
				</div>
				<div class="form-group">
					<?php echo form_label(lang('sales_detailed_note'), 'sale_item_detailed_notes',array('class'=>'control-label wide')); ?>
					<?php echo form_textarea(array(
						'name'=>'sale_item_detailed_notes',
						'id'=>'sale_item_detailed_notes',
						'class'=>'form-control text-area input_radius',
						'cols'=>'17')
					);?>
				</div>

				<div class="form-group">	
					<?php echo form_label(lang('sales_internal_note').':', 'sale_item_note_internal',array('class'=>'control-label wide','style'=>'padding-top:4px;')); ?>
					<?php echo form_checkbox(array(
						'name'=>'sale_item_note_internal',
						'id'=>'sale_item_note_internal',
						'value'=>'sale_item_note_internal',
						));?>
						<label for="sale_item_note_internal" style="padding-left: 10px;"><span></span></label>
				</div>
				
				<input type="hidden" name="item_id_being_repaired" id="item_id_being_repaired" value="<?php echo $item_being_repaired_info['item_id']; ?>">
				<input type="hidden" name="sale_id" id="sale_id" value="<?php echo $work_order_info['sale_id']; ?>">
				<input type="hidden" name="note_id" id="note_id" value="">

				<div class="form-actions">
					<?php
						echo form_submit(array(
							'name'=>'sale_item_notes_save_btn',
							'id'=>'sale_item_notes_save_btn',
							'value'=>lang('common_save'),
							'class'=>'submit_button pull-right btn btn-primary sale_item_notes_save_btn')
						);
						
						echo form_input(array(
							'type' =>'button',
							'value'=>lang('common_cancel'),
							'data-dismiss' => 'modal',
							'style' => 'margin-right: 10px;',
							'class'=>'pull-right btn btn-warning')
						);

						
					?>
					<div class="clearfix">&nbsp;</div>
				</div>
				<?php echo form_close(); ?>		
	        </div>
    	</div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<!-- Note Image Modal -->
<div class="modal fade" id="sale_item_notes_image_modal" tabindex="-1" role="dialog">
  <div class="modal-dialog" style="width: 550px;">
    <div class="modal-content" style="background-color: #abe1db;">
        <div class="modal-body">
            <img src="" class="img-responsive sale_item_notes_image">
		</div>
		<div class="text-center" style="padding-bottom: 15px;">
			<button type="button" class="btn btn-primary" data-dismiss="modal"><?php echo lang('common_close') ?></button>
		</div>
	</div>
  </div>
</div>

<div class="work_order_edit_page_holder">
<?php echo form_open('work_orders/save/'.$work_order_info['id'],array('id'=>'work_order_form','class'=>'')); ?>

	<div class="row">
		<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
			<div class="workorder-badge">
				<div class="workorder_info">
					<div class="workorder_id_and_date">
						<div class="work_order_id">
							<span class="font-weight-bold"><?php echo lang('work_orders_work_order').': '; ?></span><?php echo $work_order_info['sale_id']; ?>
						</div>

						<div class="work_order_date">
							<?php echo date(get_date_format(), strtotime($work_order_info['sale_time'])); ?>
						</div>
					</div>

					<div class="workorder_status">
						<?php echo work_order_status_badge($work_order_info['status']); ?>
					</div>

					<div class="change_status">
						<?php 
							echo form_dropdown('change_status', $change_status_array,'', 'class="form-control" id="change_status"'); 
						?>
					</div>
				</div>
				
				
				<ul class="list-inline pull-right">
					<li><?php echo anchor(site_url('sales/edit/').$work_order_info['sale_id'],lang('work_orders_edit_sale'), array('class'=>'btn btn-primary btn-lg')); ?></li>
					<li><?php echo anchor(site_url('work_orders/print_work_order/'.$work_order_info['id']), lang('work_orders_print'), array('class'=>'btn btn-primary btn-lg')); ?></li>
					<li><?php echo anchor('', lang('work_orders_service_tag'), array('class'=>'btn btn-primary btn-lg service_tag_btn')); ?></li>
					<li><?php echo anchor(site_url('work_orders'), ' ' . lang('common_done'), array('class'=>'btn btn-primary btn-lg ion-android-exit','id'=>'done_btn')); ?></li>
				</ul>
			</div>
		</div>
	</div>

	<div class="row">
		<div class="col-lg-6 col-md-6 col-sm-12 col-xs-12 pr">
			<div class="panel panel-piluku customer_info">
				<div class="panel-heading">
					<h3 class="panel-title"><i class="ion-person"></i> <?php echo lang("common_customer"); ?></h3>
				</div>

				<div class="panel-body">
					<ul class="customer_name_address_ul list-style-none">
						<li class="customer_name"><?php echo $customer_info['first_name'].' '.$customer_info['last_name']; ?></li>
						<li><?php echo $customer_info['address_1'].' '.$customer_info['address_2']; ?></li>
						<li><?php echo $customer_info['city'].','.$customer_info['state'].' '.$customer_info['zip']; ?></li>
					</ul>

					<ul class="customer_email_phonenumber_ul list-style-none">
						<li><a class="text-decoration-underline" href = "mailto:<?php echo $customer_info['email']; ?>"><?php echo $customer_info['email']; ?></a></li>
						<li><a class="text-decoration-underline" href = "tel:<?php echo $customer_info['phone_number']; ?>"><?php echo $customer_info['phone_number']; ?></a></li>
					</ul>
					<div class='clearfix'></div>
				</div><!--/panel-body -->
			</div><!-- /panel-piluku -->
		</div>

		<div class="col-lg-6 col-md-6 col-sm-12 col-xs-12 pl">
			<div class="panel panel-piluku item_being_repaired_info">
				<div class="panel-heading">
					<h3 class="panel-title"><i class="icon ti-harddrive"></i> <?php echo lang("work_orders_item_being_repaired"); ?></h3>
				</div>

				<div class="panel-body">
					<div class='row item_name_and_warranty'>	
						<div class='col-md-8'>
							<a tabindex="-1" href="<?php echo site_url('home/view_item_modal/'.$item_being_repaired_info['item_id'])."?redirect=work_orders/view/".$work_order_id; ?>" data-toggle="modal" data-target="#myModal"><?php echo H($item_being_repaired_info['name']); ?></a>
						</div>

						<div class='col-md-4 warranty_repair'>
							<?php echo form_checkbox(array(
								'name'=>'warranty',
								'id'=>'warranty',
								'value'=>'warranty',
								'checked'=>$work_order_info['warranty'],
								));?>
							<label for="warranty"><span></span></label>
							<?php echo form_label(lang('work_orders_warranty_repair'), 'warranty',array('class'=>'control-label wide','style'=>'margin-right:38px;')); ?>
						</div>
					</div>

					<dl class="dl-horizontal">
						<dt><?php echo lang('common_description') ?></dt>
						<dd><?php echo H($item_being_repaired_info['description']); ?></dd>

						<dt><?php echo lang('common_category') ?></dt>
						<dd><?php echo $this->Category->get_full_path($item_being_repaired_info['category_id']); ?></dd>
						
						<?php if($item_being_repaired_info['is_serialized']){ ?>
							<dt><?php echo lang('common_serial_number') ?></dt>
							<dd><?php echo H($item_being_repaired_info['serialnumber']); ?></dd>
						<?php } ?>
						
						<dt><?php echo lang('common_item_number_expanded') ?></dt>
						<dd><?php echo H($item_being_repaired_info['item_number']); ?></dd>
					</dl>
				</div><!--/panel-body -->
			</div><!-- /panel-piluku -->
		</div>
	</div>

	<div class="row">
		<div class="col-lg-3 col-md-3 col-sm-12 col-xs-12 pr">
			<div class="panel panel-piluku technician_info">
				<div class="panel-heading">
					<h3 class="panel-title"><i class="ion-person"></i> <?php echo lang("work_orders_technician"); ?></h3>
				</div>

				<div class="panel-body">
					<?php 
						if(!$work_order_info['employee_id']){
							echo form_dropdown('employee_id', $employees, $work_order_info['employee_id'], 'class="form-inps" id="employee_id"');
						}
						else{
					?>
						<ul class="selected_technician_info list-style-none">
							<li class="technician_name"><?php echo $work_order_info['employee_name']; ?></li>
							<li><a class="text-decoration-underline" href = "mailto:<?php echo $work_order_info['email']; ?>"><?php echo $work_order_info['email']; ?></a></li>
							<li><a class="text-decoration-underline" href = "tel:<?php echo $work_order_info['phone_number']; ?>"><?php echo $work_order_info['phone_number']; ?></a></li>
						</ul>

						<a class="text-decoration-underline change_technician" href = "<?php echo site_url('work_orders/remove_technician') ?>"><?php echo lang('work_orders_change_technician'); ?></a>
					<?php 
						}
					?>
				</div><!--/panel-body -->
			</div><!-- /panel-piluku -->
		</div>

		<div class="col-lg-3 col-md-3 col-sm-12 col-xs-12 pl pr">
			<div class="panel panel-piluku estimated_repair_date_info">
				<div class="panel-heading">
					<h3 class="panel-title"><i class="icon ti-calendar"></i> <?php echo lang("work_orders_estimated_repair_date"); ?></h3>
				</div>

				<div class="panel-body">
					<div class="input-group date">
						<span class="input-group-addon"><i class="ion-calendar"></i></span>
						<?php echo form_input(array(
							'name'=>'estimated_repair_date',
							'id'=>'estimated_repair_date',
							'class'=>'form-control form-inps datepicker',
							'value'=>$work_order_info['estimated_repair_date'] ? date(get_date_format().' '.get_time_format(), strtotime($work_order_info['estimated_repair_date'])) : '')
						);?> 
					</div>  
				</div><!--/panel-body -->
			</div><!-- /panel-piluku -->
		</div>

		<div class="col-lg-6 col-md-6 col-sm-12 col-xs-12 pl">
			<div class="panel panel-piluku estimates_info">
				<div class="panel-heading">
					<h3 class="panel-title"><i class="ion-cash"></i> <?php echo lang("work_orders_estimates"); ?></h3>
				</div>

				<div class="panel-body">
					<div class="row">
						<div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
							<?php echo form_input(array(
								'class'=>'form-control',
								'name'=>'estimated_parts',
								'id'=>'estimated_parts',
								'value'=>$work_order_info['estimated_parts'] ? to_currency_no_money($work_order_info['estimated_parts']) : '',
								'placeholder' => lang("work_orders_estimated_parts")
							)); ?>
						</div>

						<div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
							<?php echo form_input(array(
								'class'=>'form-control',
								'name'=>'estimated_labor',
								'id'=>'estimated_labor',
								'value'=>$work_order_info['estimated_labor'] ? to_currency_no_money($work_order_info['estimated_labor']) : '',
								'placeholder' => lang("work_orders_estimated_labor")
							)); ?>
						</div>
					</div>
				</div><!--/panel-body -->
			</div><!-- /panel-piluku -->
		</div>
	</div>

	<div class="row">
		<div class="col-lg-6 col-md-6 col-sm-12 col-xs-12 pr">
			<div class="panel panel-piluku parts_and_labor_info">
				<div class="panel-heading">
					<div class="row">
						<div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
							<div class="parts_and_labor_info_title">
								<i class="ion-hammer"></i>
								<span><?php echo lang('work_orders_parts_and_labor') ?></span>
							</div>
						</div>	

						<div class="col-lg-8 col-md-8 col-sm-12 col-xs-12">
							<div class="item_search">
								<div class="input-group">
									<!-- Css Loader  -->
									<div class="spinner" id="ajax-loader" style="display:none">
										<div class="rect1"></div>
										<div class="rect2"></div>
										<div class="rect3"></div>
									</div>
									
									<span class="input-group-addon">
										<?php echo anchor("items/view/-1","<i class='icon ti-pencil-alt'></i>", array('class'=>'none add-new-item','title'=>lang('common_new_item'), 'id' => 'new-item', 'tabindex'=> '-1')); ?>
									</span>
									<input type="text" id="item" name="item"  class="add-item-input pull-left keyboardTop form-control" placeholder="<?php echo lang('common_start_typing_item_name'); ?>" data-title="<?php echo lang('common_item_name'); ?>">
								</div>
							</div>
						</div>		
					</div>
					<!-- <h3 class="panel-title"><i class="icon ti-harddrive"></i> <?php echo lang("work_orders_item_being_repaired"); ?></h3> -->
				</div>

				<div class="panel-body">
					<div class="work_order_items">
						<div class="register-box register-items paper-cut">
							<div class="register-items-holder">
								<table id="register" class="table table-hover">

									<thead>
										<tr class="register-items-header">
											<th></th>
											<th><?php echo lang('work_orders_quantity'); ?></th>
											<th><?php echo lang('work_orders_item_name'); ?></th>
											<th><?php echo lang('work_orders_price'); ?></th>
											<th><?php echo lang('work_orders_total'); ?></th>
										</tr>
									</thead>
							
									<tbody class="register-item-content">
									<?php 
										
										$total = 0;
										foreach($work_order_items as $item) {
											
											$total+=$item['item_unit_price']*$item['quantity_purchased']; 
									?>
											<tr class="register-item-details">
												<td class="text-center"> <?php echo anchor("work_orders/delete_item/".$work_order_info['sale_id']."/".$item['line'],'<i class="icon ion-android-cancel"></i>', array('class' => 'delete-item'));?> </td>
												<td class="text-center">
													<a href="#" id="quantity_<?php echo $item['item_id'];?>" class="xeditable" data-type="text"  data-validate-number="true"  data-pk="1" data-name="quantity" data-url="<?php echo site_url('work_orders/edit_sale_item_quantity/'.$item['sale_id'].'/'.$item['item_id'].($item['item_variation_id']?'/'.$item['item_variation_id']:'')); ?>" data-title="<?php echo lang('common_quantity') ?>"><?php echo to_quantity($item['quantity_purchased']); ?></a>
												</td>
												<td class="text-center">
														<?php
															echo $item['item_name'];
															if($item['item_variation_id']){
																echo '-'.$this->Item_variations->get_info($item['item_variation_id'])->name;
															}
														
														?>
												</td>
												<td class="text-center">
													<a href="#" id="unit_price_<?php echo $item['item_id'];?>" class="xeditable" data-type="text"  data-validate-number="true"  data-pk="1" data-name="unit_price" data-url="<?php echo site_url('work_orders/edit_sale_item_unit_price/'.$item['sale_id'].'/'.$item['item_id'].($item['item_variation_id']?'/'.$item['item_variation_id']:'')); ?>" data-value="<?php echo H(to_currency_no_money($item['item_unit_price'])); ?>" data-title="<?php echo lang('common_price') ?>"><?php echo to_currency($item['item_unit_price']); ?></a>
												</td>
												<td class="text-center">
														<?php echo to_currency($item['item_unit_price']*$item['quantity_purchased']); ?>
												</td>
												
											</tr>
									<?php 
											
										}  
									
									?>  
									</tbody>
									
									<tfoot>
										<tr class="register-items-header">
											<td colspan="4" class="text-left"><strong><?php echo lang('common_total');?></strong></td>
											<td class="text-center"><?php echo to_currency($total);?></td>
										</tr>
									</tfoot>
									
								</table>
							</div>
							
						</div>
					</div>
				</div><!--/panel-body -->
			</div><!-- /panel-piluku -->

			
		</div>

		<div class="col-lg-6 col-md-6 col-sm-12 col-xs-12 pl">
			<div class="panel panel-piluku notes_info">
				<div class="panel-heading notes_info_title">
					<h3 class="panel-title"><i class="ion-ios-paper-outline"></i> <?php echo lang("work_orders_notes"); ?></h3><i class="ion-android-add new_note_icon"></i>
				</div>

				<div class="panel-body">
					<div class="notes">
						<?php foreach($notes as $note){ ?>
							<div class="note <?php echo $note['internal']?'interal_note':''; ?>">
								<div class="text-right">
									<a href="" class="edit_note_btn" title="<?php echo lang('common_edit'); ?>" data-note_id="<?php echo $note['note_id']; ?>" data-note="<?php echo $note['note']; ?>" data-detailed_notes="<?php echo $note['detailed_notes']; ?>" data-internal="<?php echo $note['internal']; ?>"><i class="ion-edit" aria-hidden="true"></i></a>
									<a href="" class="delete_note_btn" title="<?php echo lang('common_delete'); ?>" data-note_id="<?php echo $note['note_id']; ?>"><i class="ion-android-delete" aria-hidden="true"></i></a>
								</div>
								<?php 
									// echo "<div class='text-right'>".$note['first_name'].' '.$note['last_name']."</div>"; 
									echo "<span class='font-weight-bold'>".date(get_date_format().' '.get_time_format(), strtotime($note['note_timestamp']))."&nbsp&nbsp</span>";
									echo $note['note'];
									echo "<br />";
									echo $note['detailed_notes'];
									echo "<div class='note_employee_name'>".$note['first_name'].' '.$note['last_name']."</div>"; 
								?>
							</div>
						<?php } ?>
					</div>
				</div><!--/panel-body -->
			</div><!-- /panel-piluku -->
		</div>
	</div>

	<div class="row">
		<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
			<div class="panel panel-piluku images_info">
				<div class="panel-heading">
					<h3 class="panel-title"><i class="ion-images"></i> <?php echo lang("work_orders_images"); ?></h3>
				</div>

				<div class="panel-body">
					<div class="form-group">
						<div class="col-sm-6 col-md-4 col-lg-4">
							<div class="dropzone dz-clickable" id="dropzoneUpload">
								<div class="dz-message">
									<?php echo lang('common_drag_and_drop_or_click'); ?>
								</div>
							</div>
						</div>
						<div class="col-sm-6 col-md-8 col-lg-8">
							<div class="owl-carousel owl-theme note_images">
								<?php foreach($work_order_images as $key => $image){ ?>
									<div class="item text-center"><a href="" class="delete_work_order_image" data-index="<?php echo $key; ?>"><?php echo lang('common_delete'); ?></a><img class="owl_carousel_item_img m-t-10" src="<?php echo app_file_url($image); ?>" /></div>
								<?php } ?>
							</div>
						</div>
					</div>
				</div><!--/panel-body -->
			</div><!-- /panel-piluku -->
		</div>
	</div>


	<div class="row">
		<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
			<div class="panel panel-piluku files_info">
				<div class="panel-heading">
					<h3 class="panel-title"><i class="ion-folder"></i> <?php echo lang("common_files"); ?></h3>
				</div>

				<div class="panel-body">
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
						<div class="row" style="padding-left: 10px;">
							<div class="form-group"  style="padding-left: 10px;">
								<?php echo form_label(lang('common_file').' '.$k.':', 'files_'.$k,array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label')); ?>
								<div class="col-sm-9 col-md-9 col-lg-10">
									<div class="file-upload">
										<input type="file" name="files[]" id="files_<?php echo $k; ?>" >
									</div>
								</div>
							</div>
						</div>
					<?php } ?>

				</div><!--/panel-body -->
			</div><!-- /panel-piluku -->
		</div>
	</div>

	<div class="row">
		<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
			<div class="panel panel-piluku additional_info">
				<div class="panel-heading">
					<h3 class="panel-title"><i class="ion-information"></i> <?php echo lang("work_orders_additional_information"); ?></h3>
				</div>

				<div class="panel-body">
					<div class="row">
					<?php for($k=1;$k<=NUMBER_OF_PEOPLE_CUSTOM_FIELDS;$k++) { ?>
						<?php
						$custom_field = $this->Work_order->get_custom_field($k);
						if($custom_field !== FALSE)
						{ ?>
							<div class="col-xs-12 col-sm-12 col-md-6 col-lg-6 m-b-20">
								<div class="form-group">
								<?php echo form_label($custom_field . ' :', "custom_field_${k}_value", array('class'=>'col-sm-5 col-md-5 col-lg-4 control-label ')); ?>
															
								<div class="col-sm-7 col-md-7 col-lg-8">
										<?php if ($this->Work_order->get_custom_field($k,'type') == 'checkbox') { ?>
											
											<?php echo form_checkbox("custom_field_${k}_value", '1', (boolean)$work_order_info_object->{"custom_field_${k}_value"},"id='custom_field_${k}_value'");?>
											<label for="<?php echo "custom_field_${k}_value"; ?>"><span></span></label>
											
										<?php } elseif($this->Work_order->get_custom_field($k,'type') == 'date') { ?>
											
												<?php echo form_input(array(
												'name'=>"custom_field_${k}_value",
												'id'=>"custom_field_${k}_value",
												'class'=>"custom_field_${k}_value".' form-control',
												'value'=>is_numeric($work_order_info_object->{"custom_field_${k}_value"}) ? date(get_date_format(), $work_order_info_object->{"custom_field_${k}_value"}) : '')
												);?>									
												<script>
													var $field = <?php echo "\$('#custom_field_${k}_value')"; ?>;
												$field.datetimepicker({format: JS_DATE_FORMAT, locale: LOCALE, ignoreReadonly: IS_MOBILE ? true : false});	
													
												</script>
													
										<?php } elseif($this->Work_order->get_custom_field($k,'type') == 'dropdown') { ?>
												
												<?php 
												$choices = explode('|',$this->Work_order->get_custom_field($k,'choices'));
												$select_options = array();
												foreach($choices as $choice)
												{
													$select_options[$choice] = $choice;
												}
												echo form_dropdown("custom_field_${k}_value", $select_options, $work_order_info_object->{"custom_field_${k}_value"}, 'class="form-control"');?>
												
										<?php } else {
										
												echo form_input(array(
												'name'=>"custom_field_${k}_value",
												'id'=>"custom_field_${k}_value",
												'class'=>"custom_field_${k}_value".' form-control',
												'value'=>$work_order_info_object->{"custom_field_${k}_value"})
												);?>									
										<?php } ?>
									</div>
								</div>
							</div>
						<?php } //end if?>
						<?php } //end for loop?>
					</div>
				</div><!--/panel-body -->
			</div><!-- /panel-piluku -->
		</div>
	</div>
<?php echo form_close(); ?>
</div>


<script type='text/javascript'>
	var work_order_id = <?php echo $work_order_info['id']; ?>;
	$(".customer_info").height($(".item_being_repaired_info").height());

	date_time_picker_field($('.datepicker'), JS_DATE_FORMAT+ " "+JS_TIME_FORMAT);

	var $form = $('#work_order_form');

	$(document).ready(function(){
		var $owl = $('.note_images');
		$owl.trigger('destroy.owl.carousel');

		$owl.owlCarousel({
			loop:false,
			margin:10,
			nav:true,
			navText:['<i class="ion-ios-arrow-back"></i>','<i class="ion-ios-arrow-forward"></i>'],
			dots:false,
			items:4
		});

		$("#employee_id").select2();
	});

	$("#change_status").change(function(){
		var status = $(this).val();
		if(status != ''){
			bootbox.confirm(<?php echo json_encode(lang("work_orders_confirm_status_change"));?>, function(result)
			{
				if (result)
				{
					$('#grid-loader').show();
					event.preventDefault();
					var selected = get_selected_values();

					$.post('<?php echo site_url("work_orders/change_status/");?>', {work_order_ids : ["<?php echo $work_order_info['id']; ?>"],status:status},function(response) {
						$('#grid-loader').hide();
						show_feedback(response.success ? 'success' : 'error', response.message,response.success ? <?php echo json_encode(lang('common_success')); ?> : <?php echo json_encode(lang('common_error')); ?>);

						//Refresh if success
						if (response.success)
						{
							setTimeout(function(){window.location.reload();},800);
						}
					}, "json");
				}
			});
		}

	});

	$(".new_note_icon").click(function(){
		
		$("#sale_item_note").val('');
		$("#sale_item_detailed_notes").val('');
		$("#sale_item_note_internal").prop('checked',false);
		$("#note_id").val('');

		$(".sale_item_notes_modal").modal('show');

		$(".sale_item_notes_modal").on('shown.bs.modal', function (e) {
			$('#sale_item_note').focus();
		});
	});

	$(document).on('click','.note_images .owl_carousel_item_img',function(){
		$(".sale_item_notes_image").attr('src',$(this).attr('src'));
		$("#sale_item_notes_image_modal").modal('show');
	});

	$("#sale_item_notes_form").submit(function(event){
		event.preventDefault();
		
		if($("#sale_item_note").val() == ''){
			show_feedback('error','<?php echo lang('work_orders_please_enter_note'); ?>','<?php echo lang('common_error'); ?>');
			$("#sale_item_note").focus();
			return;
		}
		
		$("#sale_item_notes_form").ajaxSubmit({ 
			success: function(response, statusText, xhr, $form){
				$(".sale_item_notes_modal").modal('hide');
				window.location.reload();
			}
		});
		
	});

	Dropzone.autoDiscover = false;
	Dropzone.options.dropzoneUpload = {
		url:"<?php echo site_url('work_orders/workorder_images_upload'); ?>",
		autoProcessQueue:true,
		acceptedFiles: "image/*",
		uploadMultiple: true,
		parallelUploads: 100,
		maxFiles: 100,
		addRemoveLinks:true,
		dictRemoveFile:"Remove",
		init:function(){
			myDropzone = this;
			this.on("success", function(file, responseText) {
				window.location.reload();
			});
		}
	};
	$('#dropzoneUpload').dropzone();

	myDropzone.on('sending', function(file, xhr, formData){
		formData.append('work_order_id', work_order_id);
	});

	$('.delete-item').click(function(event)
	{
		event.preventDefault();
		$.post($(this).attr('href'),function(response) {
			window.location.reload();
		});
	});

	if ($("#item").length)
	{
		$( "#item" ).autocomplete({
			source: '<?php echo site_url("work_orders/item_search");?>',
			delay: 150,
			autoFocus: false,
			minLength: 0,
			select: function( event, ui ) 
			{
				item_select(ui.item.value);
			},
		}).data("ui-autocomplete")._renderItem = function (ul, item) {
		return $("<li class='item-suggestions'></li>")
			.data("item.autocomplete", item)
			.append('<a class="suggest-item"><div class="item-image">' +
						'<img src="' + item.image + '" alt="">' +
					'</div>' +
					'<div class="details">' +
						'<div class="name">' + 
							item.label +
						'</div>' +
						'<span class="attributes">' + '<?php echo lang("common_category"); ?>' + ' : <span class="value">' + (item.category ? item.category : <?php echo json_encode(lang('common_none')); ?>) + '</span></span>' +
						(typeof item.quantity !== 'undefined' && item.quantity!==null ? '<span class="attributes">' + '<?php echo lang("common_quantity"); ?>' + ' <span class="value">'+item.quantity + '</span></span>' : '' )+
						(item.attributes ? '<span class="attributes">' + '<?php echo lang("common_attributes"); ?>' + ' : <span class="value">' +  item.attributes + '</span></span>' : '' ) +
					
					'</div>')
			.appendTo(ul);
			};

			$('#item').bind('keypress', function(e) {
				if(e.keyCode==13)
				{
					e.preventDefault();
					$.get('<?php echo site_url("work_orders/item_search");?>', {term: $("#item").val()}, function(response)
					{
						var data = JSON.parse(response);
						if(data.length == 1){
							item_select(data[0].value);
						}
					});
				}
			});
	}

	
	function item_select(item_id){
		$("#ajax-loader").show();
		$.post("<?php echo site_url('work_orders/add_sale_item') ?>", {item_id:item_id,sale_id:"<?php echo $work_order_info['sale_id']; ?>"},function(response) {
			$('#ajax-loader').hide();

			//Refresh if success
			if (response.success)
			{
				window.location.reload();
			}
			else{
				$("#item").val('');
				show_feedback('error', response.message,<?php echo json_encode(lang('common_error')); ?>);
			}
		},'json');
	}

	$('.xeditable').editable({
    	validate: function(value) {
            if ($.isNumeric(value) == '' && $(this).data('validate-number')) {
					return <?php echo json_encode(lang('common_only_numbers_allowed')); ?>;
            }
        },
    	success: function(response, newValue) {
			window.location.reload();
		}
    });

    $('.xeditable').on('shown', function(e, editable) {

    	editable.input.postrender = function() {
				//Set timeout needed when calling price_to_change.editable('show') (Not sure why)
				setTimeout(function() {
	         editable.input.$input.select();
			}, 200);
	    };
	});

	$('#done_btn').click(function(e)
	{
		var $that = $(this);
	
		e.preventDefault();

		$('#grid-loader').show();

		$form.ajaxSubmit({
			success: function(response,status)
			{
				$('#grid-loader').hide();
				window.location = $that.attr('href');

			},
			dataType:'json'
		});
	});

	$("#employee_id").change(function(){
		$('#grid-loader').show();
		$.post('<?php echo site_url("work_orders/select_technician/");?>', {work_order_id : work_order_id,employee_id:$(this).val()},function(response) {
			$('#grid-loader').hide();
			window.location.reload();
		});
	});

	$(".change_technician").click(function(e){
		e.preventDefault();

		$.post('<?php echo site_url("work_orders/remove_technician/");?>', {work_order_id : work_order_id},function(response) {
			window.location.reload();
		});
	});

	$('.service_tag_btn').click(function(e)
	{
		var default_to_raw_printing = "<?php echo $this->config->item('default_to_raw_printing'); ?>";
		if(default_to_raw_printing == "1"){
			$(this).attr('href','<?php echo site_url("work_orders/raw_print_service_tag");?>/'+work_order_id);
		}
		else{
			$(this).attr('href','<?php echo site_url("work_orders/print_service_tag");?>/'+work_order_id);
		}
	});

	$(".delete_note_btn").click(function(e){
		var note_id = $(this).data('note_id');
		e.preventDefault();
		bootbox.confirm(<?php echo json_encode(lang('work_orders_note_delete_confirmation')); ?>, function(result)
		{
			if(result)
			{
				$.post('<?php echo site_url("work_orders/delete_note");?>', {note_id : note_id},function(response) {	
					show_feedback(response.success ? 'success' : 'error', response.message,response.success ? <?php echo json_encode(lang('common_success')); ?> : <?php echo json_encode(lang('common_error')); ?>);
					if (response.success)
					{
						window.location.reload();
					}
				}, "json");

			}
		});
	})

	$(".edit_note_btn").click(function(e){
		e.preventDefault();

		var note_id = $(this).data('note_id');
		var note = $(this).data('note');
		var detailed_notes = $(this).data('detailed_notes');
		var internal = $(this).data('internal');
		
		$("#note_id").val(note_id);
		$("#sale_item_note").val(note);
		$("#sale_item_detailed_notes").val(detailed_notes);
		if(internal){
			$("#sale_item_note_internal").prop('checked',true);
		}
		else{
			$("#sale_item_note_internal").prop('checked',false);
		}

		$(".sale_item_notes_modal").modal('show');
	});

	$(".delete_work_order_image").click(function(e){
		e.preventDefault();
		var image_index = $(this).data('index');
		bootbox.confirm(<?php echo json_encode(lang('work_orders_image_delete_confirmation')); ?>, function(result)
		{
			if(result)
			{
				$.post('<?php echo site_url("work_orders/delete_work_order_image");?>', {work_order_id : work_order_id,image_index : image_index},function(response) {	
					show_feedback(response.success ? 'success' : 'error', response.message,response.success ? <?php echo json_encode(lang('common_success')); ?> : <?php echo json_encode(lang('common_error')); ?>);
					if (response.success)
					{
						window.location.reload();
					}
				}, "json");

			}
		});

	});

	$('.delete_file').click(function(e){
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
<?php $this->load->view("partial/footer"); ?>
