<?php $this->load->view("partial/header"); ?>
<?php $query = http_build_query(array('redirect' => $redirect, 'progression' => $progression ? 1 : null, 'quick_edit' => $quick_edit ? 1 : null)); ?>

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
					<?php if(!$item_info->item_id) { ?>
			    <span class="modal-item-name new"><?php echo lang('items_new'); ?></span>
					<?php } else { ?>
		    	<span class="modal-item-name"><?php echo H($item_info->name).' ['.lang('common_id').': '.$item_info->item_id.']'; ?></span>
					<span class="modal-item-category"><?php echo H($category); ?></span>
					<?php } ?>
				</div>
			</div>	
		</div>
		<?php if(isset($redirect) && !$progression) { ?>
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
<?php $this->load->view('partial/nav', array('progression' => $progression, 'query' => $query, 'item_info' => $item_info)); ?>
<?php } ?>

<?php echo form_open('items/save_inventory/'.$item_info->item_id,array('id'=>'item_form','class'=>'form-horizontal')); ?>
	<div class="row <?php echo $redirect ? 'manage-table' :''; ?>">
		<div class="col-md-12">
			<div class="panel panel-piluku">
				<div class="panel-heading">
		      <h3 class="panel-title"><i class="ion-android-clipboard"></i> <?php echo lang("common_inventory"); ?> <small>(<?php echo lang('common_fields_required_message'); ?>)</small></h3>
					
					<div class="panel-options custom pagination pagination-top hidden-print text-center" id="pagination_top">
						<?php
						if (isset($prev_item_id) && $prev_item_id)
						{
								echo anchor('items/inventory/'.$prev_item_id, '<span class="hidden-xs ion-chevron-left"> '.lang('items_prev_item').'</span>');
						}
						if (isset($next_item_id) && $next_item_id)
						{
								echo anchor('items/inventory/'.$next_item_id,'<span class="hidden-xs">'.lang('items_next_item').' <span class="ion-chevron-right"></span</span>');
						}
						?>
		  		</div>
				</div>
				
				
				
				<div class="panel-body">
					
					
					
					<div class="form-group is-service-toggle <?php if ($item_info->is_service){ echo 'hidden';} ?>">
						<?php echo form_label(lang('items_reorder_level').':', 'reorder_level',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label wide')); ?>
						<div class="col-sm-9 col-md-9 col-lg-10">
							<?php echo form_input(array(
								'name'=>'reorder_level',
								'id'=>'reorder_level',
								'class'=>'form-control form-inps',
								'value'=>$item_info->reorder_level || $item_info->item_id ? to_quantity($item_info->reorder_level, FALSE) : $this->config->item('default_reorder_level_when_creating_items'))
							);?>
						</div>
					</div>				
				
					<div class="form-group is-service-toggle <?php if ($item_info->is_service){echo 'hidden';} ?>">
						<?php echo form_label(lang('common_replenish_level').':', 'replenish_level',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label wide')); ?>
						<div class="col-sm-9 col-md-9 col-lg-10">
							<?php echo form_input(array(
								'name'=>'replenish_level',
								'id'=>'replenish_level',
								'class'=>'form-control form-inps',
								'value'=>$item_info->replenish_level ? to_quantity($item_info->replenish_level) :'')
							);?>
						</div>
					</div>
				
					<div class="form-group">
						<?php echo form_label(lang('items_days_to_expiration').':', 'expire_days',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label wide')); ?>
						<div class="col-sm-9 col-md-9 col-lg-10">
							<?php echo form_input(array(
								'name'=>'expire_days',
								'id'=>'expire_days',
								'class'=>'form-control form-inps',
								'value'=>$item_info->expire_days || $item_info->item_id ? to_quantity($item_info->expire_days, FALSE) : $this->config->item('default_days_to_expire_when_creating_items'))
							);?>
						</div>
					</div>
					
					
					<?php if(!count($item_variations) > 0) { ?>
					
					<div class="form-group">
						<?php echo form_label(lang('items_current_quantity').':', null,array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label')); ?>
						<div class="col-sm-9 col-md-9 col-lg-10 form-text">
							<?php echo form_input(array(
								'name'=>'current_qty',
								'id'=>'current_qty',
								'placeholder' => to_quantity(null),
								'data-original' => isset($item_location_info->quantity) ? to_quantity($item_location_info->quantity) : to_quantity(''),
								'value' => to_quantity($item_location_info->quantity),
								'class'=>'form-control'
								)
							);?>
						</div>
					</div>
					
					<div class="form-group">
						<?php echo form_label(lang('items_suspended_inventory').':', null,array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label')); ?>
						<div class="col-sm-9 col-md-9 col-lg-10 form-text">
							<?php
							$suspended_inventory = $this->Item_location->get_suspended_inventory($item_info->item_id);
							
							echo to_quantity($suspended_inventory);
							?>
						</div>
					</div>
									
					<div class="form-group hidden-print">
						<?php echo form_label(lang('reports_damaged_qty').':', 'damaged_qty',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label')); ?>
						<div class="col-sm-9 col-md-9 col-lg-10">
							<?php echo form_input(array(
							'name'=>'damaged_qty',
							'id'=>'damaged_qty',
							'class'=>'form-control'
								)
							);?>
						</div>
					</div>
					
					<div class="form-group hidden-print" id="damaged_reason_container" style="display: none;">
						<?php echo form_label(lang('items_damaged_reason').':', 'damaged_reason',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label')); ?>
						<div class="col-sm-9 col-md-9 col-lg-10">
							<?php echo form_dropdown('damaged_reason', $this->Appconfig->get_damaged_reasons_options(), '','class="form-control" id="damaged_reason"'); ?>
							
						</div>
					</div>
					
					
					
					<?php if ($this->Employee->has_module_action_permission('items','edit_quantity', $this->Employee->get_logged_in_employee_info()->person_id)) { ?>
						<div class="form-group hidden-print">
							<?php echo form_label(lang('items_add_minus').':', 'newquantity',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label')); ?>
							<div class="col-sm-9 col-md-9 col-lg-10">
								<?php echo form_input(array(
								'name'=>'add_subtract',
								'id'=>'add_subtract',
								'class'=>'form-control'
									)
								);?>
							</div>
						</div>
						
						
						
						<div class="form-group hidden-print">
							<?php echo form_label(lang('common_items_inventory_comments').':', 'trans_comment',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label')); ?>
							<div class="col-sm-9 col-md-9 col-lg-10">
								<?php echo form_textarea(array(
								'name'=>'trans_comment',
								'id'=>'trans_comment',
								'class'=>'form-control text-area',
								'rows'=>'3',
								'cols'=>'17')		
								);?>
							</div>
						</div>
					  <?php } //edit qty permissions ?>
						
						<?php } else {  ?>
						
						<div class="form-group">
							<?php echo form_label(lang('items_variations').':', null,array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label')); ?>
							<div class="col-sm-9 col-md-9 col-lg-10">
								<table class="table table-striped table-hover custom-table">
									<thead>
										<tr>
											<th><?php echo lang("common_name"); ?></th>
											<th><?php echo lang("items_attributes"); ?></th>
											<th><?php echo lang("common_item_number"); ?></th>
											<th class="is-service-toggle <?php if ($item_info->is_service){echo 'hidden';} ?>"><?php echo lang('items_reorder_level'); ?></th>
											<th class="is-service-toggle <?php if ($item_info->is_service){echo 'hidden';} ?>"><?php echo lang('common_replenish_level'); ?></th>
											<th><?php echo lang("items_quantity"); ?></th>
											<?php if ($this->Employee->has_module_action_permission('items','edit_quantity', $this->Employee->get_logged_in_employee_info()->person_id)) { ?>
											<th><?php echo lang("reports_damaged_qty"); ?></th>
											<th><?php echo lang("items_damaged_reason"); ?></th>
											<th><?php echo lang("items_add_minus"); ?></th>
											<th class="hidden-xs"><?php echo lang("common_items_inventory_comments"); ?></th>
											<?php } ?>
										</tr>
									</thead>
									<tbody>
										<?php foreach($item_variations as $item_variation_id => $item_variation) { ?>
											<tr>
												<td><?php echo H($item_variation['name']); ?></td>
												
												<td>
													<?php
													$description = '';
													foreach($item_variation['attributes'] as $attribute)
													{
														$description .= H($attribute['label']) . "<br>";
													}
													
													echo $description;
													?>
												</td>
												<td><?php echo H($item_variation['item_number']); ?></td>
												<td class="is-service-toggle <?php if ($item_info->is_service){ echo 'hidden'; } ?>"><input type="text" class="form-control form-inps" size="5" name="item_variations[<?php echo $item_variation_id; ?>][reorder_level]" value='<?php echo H(to_quantity($item_variation['reorder_level'], false)); ?>' /></td>
												<td class="is-service-toggle <?php if ($item_info->is_service){ echo 'hidden'; } ?>"><input type="text" class="form-control form-inps" size="5" name="item_variations[<?php echo $item_variation_id; ?>][replenish_level]" value='<?php echo H(to_quantity($item_variation['replenish_level'], false)); ?>' /></td>
												<?php if ($this->Employee->has_module_action_permission('items','edit_quantity', $this->Employee->get_logged_in_employee_info()->person_id)) { ?>
												<td><input type="text" class="form-control variation_current_qty" name="item_variations[<?php echo $item_variation_id; ?>][current_qty]" data-original="<?php echo isset($item_variation_location_info[$item_variation_id]['quantity']) ? to_quantity($item_variation_location_info[$item_variation_id]['quantity']) : to_quantity(''); ?>" value="<?php echo isset($item_variation_location_info[$item_variation_id]['quantity']) ? to_quantity($item_variation_location_info[$item_variation_id]['quantity']) : ''; ?>" placeholder="<?php echo to_quantity(null); ?>"></td>
												<td><input type="text" class="form-control damaged_qty" name="item_variations[<?php echo $item_variation_id; ?>][damaged_qty]" value=""></td>
												<td>
													
													<?php echo form_dropdown("item_variations[$item_variation_id][damaged_reason]", $this->Appconfig->get_damaged_reasons_options(), '','class="form-control"'); ?>
													
												</td>
												<td><input type="text" class="form-control variation_add_subtract" name="item_variations[<?php echo $item_variation_id; ?>][add_subtract]" value=""></td>
												<td class="hidden-xs"><input type="text" class="form-control" name="item_variations[<?php echo $item_variation_id; ?>][comments]" value=""></td>
												<?php } else { ?>
												<td></td>
												<?php
												}
												?>
											</tr>
										<?php } ?>
									</tbody>
								</table>
							</div>
						</div>
						
						<?php } ?>

					
				</div>
				
				<?php echo form_hidden('redirect', isset($redirect) ? $redirect : ''); ?>
				<?php echo form_hidden('progression', isset($progression) ? $progression : ''); ?>
				<?php echo form_hidden('quick_edit', isset($quick_edit) ? $quick_edit : ''); ?>
				
				
				<div class="form-actions">
					<?php
						echo form_submit(array(
							'name'=>'submitf',
							'id'=>'submitf',
							'value'=>lang('common_save'),
							'class'=>'submit_button floating-button btn btn-lg btn-primary hidden-print')
						);
					?>
				</div>
				
				<?php  echo form_close(); ?>
				

			</div>
			
			
			
			
			
			
			<?php if ($pagination) { ?>
				<div class="pagination hidden-print alternate text-center" id="pagination_top" >
					<?php echo $pagination;?>
				</div>
			<?php } ?>
			<div class="panel">
				<div class="panel-body">
					<ul class="list-inline pull-left">
						<li><a target="_blank" href="<?php echo site_url('reports/generate/detailed_inventory?report_date_range_simple=LAST_7&report_type=simple&item_id='.$item_info->item_id.'&export_excel=0&with_time=1&end_date_end_of_day=0'); ?>" class="btn btn-success"><?php echo lang('common_view_report').' ['.lang('reports_last_7').']'; ?></a></li>
						<li><a target="_blank" href="<?php echo site_url('reports/generate/detailed_inventory?report_date_range_simple=LAST_30&report_type=simple&item_id='.$item_info->item_id.'&export_excel=0&with_time=1&end_date_end_of_day=0'); ?>" class="btn btn-success"><?php echo lang('common_view_report').' ['.lang('common_last_30_days').']'; ?></a></li>
						<li><a target="_blank" href="<?php echo site_url('reports/generate/detailed_inventory?report_date_range_simple=THIS_YEAR&report_type=simple&item_id='.$item_info->item_id.'&export_excel=0&with_time=1&end_date_end_of_day=0'); ?>" class="btn btn-success"><?php echo lang('common_view_report').' ['.lang('reports_this_year').']'; ?></a></li>
					</ul>
					
					<table class="table table-striped table-hover custom-table">
						<thead>
							<tr>
								<th><?php echo lang("items_inventory_tracking"); ?></th>
								<th><?php echo lang("common_employee"); ?></th>
								<th><?php echo lang("common_variation"); ?></th>
								<th><?php echo lang("common_items_in_out_qty"); ?></th>
								<th><?php echo lang("common_qty_in_stock"); ?></th>
								<th><?php echo lang("items_remarks"); ?></th>
								<th><?php echo lang("common_qty_name"); ?></th>
								<th><?php echo lang("common_quantity_units"); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach($inventory_data as $row) { ?>
								<tr>
									<td><?php echo date(get_date_format(). ' '.get_time_format(), strtotime($row['trans_date']))?></td>
									<td>
										<?php
											$person_id = $row['trans_user'];
											$employee = $this->Employee->get_info($person_id);
											echo $employee->first_name." ".$employee->last_name;
										?>
									</td>
									<td><?php echo H($row['variation'])?></td>
									<td><?php echo to_quantity($row['trans_inventory']);?></td>
									<td><?php echo to_quantity($row['trans_current_quantity']);?></td>
									<?php
									$row['trans_comment'] = H($row['trans_comment']);
									$row['trans_comment'] = preg_replace('/'.$this->config->item('sale_prefix').' ([0-9]+)/', '<span class="sale">'.anchor('sales/receipt/$1', $row['trans_comment']).'</span>', $row['trans_comment']);
									$row['trans_comment'] = preg_replace('/RECV ([0-9]+)/', '<span class="receiving">'.anchor('receivings/receipt/$1', $row['trans_comment']).'</span>', $row['trans_comment']);
									?>
									<td>
										<?php 
										//Editable text
										if ($this->Employee->has_module_action_permission('items', 'can_edit_inventory_comment', $this->Employee->get_logged_in_employee_info()->person_id) && $row['trans_comment'] == strip_tags($row['trans_comment']))
										{
											echo anchor('items/inventory_comment_edit/'.$row['trans_id'],$row['trans_comment'] ? $row['trans_comment'] : lang('common_empty'), array('data-value' => H($row['trans_comment']),'data-type' => 'text','data-name' => 'trans_comment','data-pk' => $row['trans_id'],'class' => 'xeditable','data-title' => lang('common_edit'),'data-url' => site_url('items/inventory_comment_edit/'.$row['trans_id'])));
										}
										else
										{
											echo $row['trans_comment'];										
										}
										?>
									</td>

									<td><?php echo $row['quantity_unit_name'];?></td>
									<td><?php echo to_quantity($row['quantity_unit_quantity']);?></td>
									
								</tr>
							<?php } ?>
						</tbody>
					</table>
					<script>
						$('.xeditable').editable();
					</script>
					<div class="text-center">
						<button class="btn btn-primary btn-lg text-white hidden-print" id="print_button" ><span class="ion-printer"></span> <?php echo lang('common_print'); ?> </button>	
					</div>
				</div>
			</div>
			<?php if ($pagination) { ?>
				<div class="pagination hidden-print alternate text-center" id="pagination_bottom" >
					<?php echo $pagination;?>
				</div>
			<?php } ?>
		</div>
	</div>
			
<script type='text/javascript'>
<?php $this->load->view("partial/common_js"); ?>
	
function print_inventory()
 {
 	window.print();
 }
//validation and submit handling
$(document).ready(function()
{	
	$("#generate_barcode_labels").click(function()
	{
		var barcodes = [];
		
		var number_of_barcodes = $("#items_number_of_barcodes").val();
		
		if (number_of_barcodes <= 50)
		{
			for(var k=0;k<number_of_barcodes;k++)
			{
				barcodes.push($(this).data('item-id'));
			}
		
			window.open($(this).attr('href')+"/"+barcodes.join("~"),'_blank');
		}
		
		return false;
	});
	
	$('#print_button').click(function(e){
		e.preventDefault();
		$('.content').addClass('no-margin');
		print_inventory();
		$('.content').removeClass('no-margin');
	});

	
	$('#item_form').validate({
		submitHandler:function(form)
		{
			var args = {
				next: {
					label: <?php echo json_encode(lang('common_edit').' '.lang('common_locations')) ?>,
					url: <?php echo json_encode(site_url("items/location_settings/".($item_info->item_id ? $item_info->item_id : -1)."?$query")); ?>,
				}
			};
		
			doItemSubmit(form, args);
		},
			errorClass: "help-inline",
			errorElement: "span",
			highlight:function(element, errorClass, validClass) {
				$(element).parents('.form-group').addClass('text-danger');
			},
			unhighlight: function(element, errorClass, validClass) {
				$(element).parents('.form-group').removeClass('text-danger');
				$(element).parents('.form-group').addClass('text-success');
			},
		rules: 
		{
			newquantity:
			{
				number:true
			}
   		},
		messages: 
		{
			
			newquantity:
			{
				required:<?php echo json_encode(lang('items_quantity_required')); ?>,
				number:<?php echo json_encode(lang('items_quantity_number')); ?>
			}
		}
	});
});

$(document).on('keyup', '#current_qty', function() {
	var new_qty = $(this).val();
	var original_qty = $(this).data('original');
	
	var add_subtract_val = new_qty - original_qty; 
	
	var $add_subtract_input = $('#add_subtract');
	
	$add_subtract_input.val(add_subtract_val);
});

$(document).on('keyup', '#add_subtract', function() {
	var add_subtract = $(this).val();
	
	var $qty_input = $('#current_qty');
	var original_qty = $qty_input.data('original');
	
	var qty = Number(original_qty) + Number(add_subtract); 
	
	$qty_input.val(qty);
});

$(document).on('keyup', '#damaged_qty', function()
{
	$("#add_subtract").val($(this).val()*-1);
	$("#add_subtract").trigger('keyup');
	
	if ($(this).val())
	{
		$("#damaged_reason_container").show();		
	}
	else
	{
		$("#damaged_reason_container").hide();
	}
});



$(document).on('keyup', '.variation_current_qty', function() {
	var new_qty = $(this).val();
	var original_qty = $(this).data('original');
	
	var add_subtract_val = new_qty - original_qty; 
	
	var $add_subtract_input = $(this).closest('tr').find('.variation_add_subtract');
	
	$add_subtract_input.val(add_subtract_val);
});

$(document).on('keyup', '.variation_add_subtract', function() {
	var add_subtract = $(this).val();
	
	var $qty_input = $(this).closest('tr').find('.variation_current_qty');
	var original_qty = $qty_input.data('original');
	
	var qty = Number(original_qty) + Number(add_subtract); 
	
	$qty_input.val(qty);
});

$(document).on('keyup', '.damaged_qty', function()
{
	var $add_subtract_input = $(this).closest('tr').find('.variation_add_subtract');
	$add_subtract_input.val($(this).val()*-1);
	$add_subtract_input.trigger('keyup');
});


</script>
<?php $this->load->view('partial/footer'); ?>
