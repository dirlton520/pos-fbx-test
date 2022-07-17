<div class="modal-dialog edit-taxes">
	<div class="modal-content">
		<div class="modal-header" id="myTabHeader">
			<button type="button" class="close" data-dismiss="modal" aria-label=<?php echo json_encode(lang('common_close')); ?>><span aria-hidden="true" class="ti-close"></span></button>
			<div class="modal-item-details">
					<h4 class="modal-title"><?php echo lang('common_edit_taxes'); ?></h4>
			</div>
		</div>
		
		<div class="modal-body" id="myTabModalBody">
						
						<?php echo form_open($controller_name.'/save_tax_overrides'.(isset($line) ? '_line/'.$line: ''),array('id'=>'tax_form','class'=>'form-horizontal')); ?>
							<div class="row">
								<div class="col-md-12">
						
						<div class="form-group">	
							<?php echo form_label(lang('common_tax_class').': ', 'tax_class',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label')); ?>
							<div class="col-sm-9 col-md-9 col-lg-10">
							<?php echo form_dropdown('tax_class', $tax_classes, $tax_class_selected, array('id' =>'tax_class','class' => 'form-control tax_class'));?>
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
									'value'=> isset($tax_info[0]['name']) ? $tax_info[0]['name'] : '')
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
									'value'=> isset($tax_info[0]['percent']) ? $tax_info[0]['percent'] : '')
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
									'value'=> isset($tax_info[1]['name']) ? $tax_info[1]['name'] : '')
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
									'value'=> isset($tax_info[1]['percent']) ? $tax_info[1]['percent'] : '')
								);?>
								<div class="tax-percent-icon">%</div>
								<div class="clear"></div>
								<?php echo form_checkbox('tax_cumulatives[]', '1', (isset($tax_info[1]['cumulative']) && $tax_info[1]['cumulative']) ? (boolean)$tax_info[1]['cumulative'] : false, 'class="cumulative_checkbox" id="tax_cumulatives"'); ?>
								<label for="tax_cumulatives"><span></span></label>
							    <span class="cumulative_label">
									<?php echo lang('common_cumulative'); ?>
							    </span>
							</div>
						</div>
	                 
						<div class="more_taxes_container">
							<div class="form-group">
								<?php echo form_label(lang('common_tax_3').':', 'tax_percent_3',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label wide')); ?>
								<div class="col-sm-9 col-md-9 col-lg-10">
									<?php echo form_input(array(
										'name'=>'tax_names[]',
										'id'=>'tax_percent_3',
										'size'=>'8',
										'class'=>'form-control form-inps margin10',
										'placeholder' => lang('common_tax_name'),
										'value'=> isset($tax_info[2]['name']) ? $tax_info[2]['name'] : '')
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
										'value'=> isset($tax_info[2]['percent']) ? $tax_info[2]['percent'] : '')
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
									'value'=> isset($tax_info[3]['name']) ? $tax_info[3]['name'] : '')
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
									'value'=> isset($tax_info[3]['percent']) ? $tax_info[3]['percent'] : '')
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
										'value'=> isset($tax_info[4]['name']) ? $tax_info[4]['name'] : '')
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
										'value'=> isset($tax_info[4]['percent']) ? $tax_info[4]['percent'] : '')
									);?>
								<div class="tax-percent-icon">%</div>
								<div class="clear"></div>
								<?php echo form_hidden('tax_cumulatives[]', '0'); ?>
								</div>
							</div>
							<div class="form-actions">
								<?php
									echo form_submit(array(
										'name'=>'submitf',
										'id'=>'submitf',
										'value'=>lang('common_save'),
										'class'=>'submit_button btn btn-lg btn-primary')
									);
								?>
							</div>
							
						</div>
					</div>
				</div>
				
			</form>
		</div>
	</div>
</div>
<script>
$("#tax_form").submit(function(e)
{
	e.preventDefault();
	//If we don't have prop checked for tax_cumulatives add another tax_cumulatives[] = 0 to form
	if (!$("#tax_cumulatives").prop('checked'))
	{
		$('<input>').attr({
		    type: 'hidden',
		    name: 'tax_cumulatives[]',
				value: '0'
		}).appendTo('#tax_form');
	}
	$('#myModal').modal('hide');
	$("#tax_form").ajaxSubmit({
		success:function(response)
		{
			$("#register_container").html(response);
		}
	});	
});	
</script>