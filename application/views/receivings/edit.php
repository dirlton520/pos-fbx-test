<?php $this->load->view("partial/header"); ?>
<div class="row" id="form">
	<div class="col-md-12">
		<div class="panel piluku-panel">
			<div class="panel-heading">
				<h4 class=""><?php echo lang('receivings_register')." - ".lang('receivings_edit_receiving'); ?>  RECV <?php echo $receiving_info['receiving_id']; ?>	</h4>
			</div>
			<div class="panel-body">
					<?php echo form_open("receivings/save/".$receiving_info['receiving_id'],array('id'=>'receivings_edit_form','class'=>'form-horizontal')); ?>
						<div class="form-group">
						<?php echo form_label(lang('receivings_receipt').':', 'receipt',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label')); ?>
							<div class="col-sm-9 col-md-9 col-lg-10 sale-s">
								<?php echo anchor('receivings/receipt/'.$receiving_info['receiving_id'], 'RECV '.$receiving_info['receiving_id'], array('target' => '_blank'));?>
							</div>
						</div>
		
						<div class="form-group">
						<?php echo form_label(lang('common_date').':', 'date',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label')); ?>
							<div class="col-sm-9 col-md-9 col-lg-10 sale-s">
								<?php echo form_input(array('name'=>'date','value'=>date(get_date_format()." ".get_time_format(), strtotime($receiving_info['receiving_time'])), 'id'=>'date'));?>
							</div>
						</div>
		
		
						<div class="form-group">
						<?php echo form_label(lang('receivings_supplier').':', 'supplier',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label')); ?>
							<div class="col-sm-9 col-md-9 col-lg-10 sale-s">
								<?php if (!$store_account_payment && !$store_account_charge) { ?>
									<?php echo form_dropdown('supplier_id', $suppliers, $receiving_info['supplier_id'], 'id="supplier_id"');?>
								<?php } elseif($selected_supplier_name) { ?>
									<?php echo '<span>'.$selected_supplier_name.'</span>'; ?>
									<?php echo '<input type="hidden" name="supplier_id" value="'.$receiving_info['supplier_id'].'" />'; ?>
								<?php } ?>
							</div>
						</div>
		
						<div class="form-group">
						<?php echo form_label(lang('common_employee').':', 'employee',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label')); ?>
							<div class="col-sm-9 col-md-9 col-lg-10 sale-s">
								<?php echo form_dropdown('employee_id', $employees, $receiving_info['employee_id'], 'id="employee_id"');?>
							</div>
						</div>
		
						<div class="form-group">
						<?php echo form_label(lang('common_comment').':', 'comment',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label')); ?>
							<div class="col-sm-9 col-md-9 col-lg-10">
								<?php echo form_textarea(array('name'=>'comment','value'=>$receiving_info['comment'],'rows'=>'14','cols'=>'23', 'id'=>'comment','class'=>'form-control textarea'));?>
							</div>
						</div>						
						
					<div class="form-actions pull-right">
					<?php
					echo form_submit(array(
						'name'=>'submit',
						'id'=>'submit',
						'value'=>lang('common_save'),
						'class'=>'btn btn-primary float_left submitzz',
						'style'=>'margin-right:15px')
					);
					?>
					</div>
				</form>
				

					<?php if ($receiving_info['deleted'] && !$store_account_payment) { ?>
					
						<?php echo form_open("receivings/undelete/".$receiving_info['receiving_id'],array('id'=>'receivings_undelete_form')); ?>
							<?php echo form_submit(array(
								'name'=>'undelete_submit_form',
								'id'=>'undelete_submit_form',
								'value'=>lang('receivings_undelete_entire_sale'),
								'class'=>'btn btn-primary submitzz')
							); ?>
						</form>
    				
					<?php } else { ?>
					
						<?php 
						 if ((!$store_account_payment && $this->Employee->has_module_action_permission('receivings', 'edit_receiving', $this->Employee->get_logged_in_employee_info()->person_id)) && (empty($currentReceivingAccoutingId))) {
					   		$edit_recv_url = $receiving_info['suspended'] ? 'unsuspend' : 'change_recv';
							echo form_open("receivings/$edit_recv_url/".$receiving_info['receiving_id'],array('id'=>'receivings_change_form')); ?>
					
							<?php echo form_submit(array(
								'name'=>'edit_submit_form',
								'id'=>'edit_submit_form',
								'value'=>lang('receivings_edit'),
								'class'=>'btn btn-primary float_left submitzz') 
							); ?>
					
						</form>		
						<br />

						<?php }	?>
					
						<?php 
						 if (!$store_account_payment && $this->Employee->has_module_action_permission('receivings', 'delete_receiving', $this->Employee->get_logged_in_employee_info()->person_id)){ ?>
					
							<?php echo form_open("receivings/delete/".$receiving_info['receiving_id'],array('id'=>'receivings_delete_form')); ?>
								<?php echo form_submit(array(
									'name'=>'delete_submit_form',
									'id'=>'delete_submit_form',
									'value'=>lang('receivings_delete_entire_receiving'),
									'class'=>'btn btn-danger delete_button delete_btnz') 
								); ?>
							</form>
							<?php } ?>
						</div>
					<?php } ?>
			</div>
		</div>
	</div>
</div>

<?php $this->load->view("partial/footer"); ?>

<script type="text/javascript" language="javascript">
$(document).ready(function()
{	
	$("#employee_id").select2();
	$("#supplier_id").select2();
	
	date_time_picker_field($('#date'), JS_DATE_FORMAT+ " "+JS_TIME_FORMAT);
	
	$("#receivings_undelete_form").submit(function()
	{
		var unDeleteForm = this;
		
		bootbox.confirm(<?php echo json_encode(lang("receivings_undelete_confirmation")); ?>, function(result)
		{
			if (result)
			{
				unDeleteForm.submit();
			}
		});
		
		return false;
		
	});
	
	$("#receivings_delete_form").submit(function()
	{
		var deleteForm = this;
		bootbox.confirm(<?php echo json_encode(lang("receivings_delete_confirmation")); ?>, function(result)
		{
			if (result)
			{
				deleteForm.submit();
			}
		});
		
		return false;
	});
	
	var submitting = false;
	$('#receivings_edit_form').validate({
		submitHandler:function(form)
		{
			if (submitting) return;
			submitting = true;
			
			$(form).ajaxSubmit({
			success:function(response)
			{
				submitting = false;
				if(response.success)
				{
					show_feedback('success', response.message, <?php echo json_encode(lang('common_success')); ?>);
				}
				else
				{
					show_feedback('error', response.message, <?php echo json_encode(lang('common_error')); ?>);					
				}
			},
			dataType:'json'
		});

		},
		errorLabelContainer: "#error_message_box",
 		wrapper: "li",
		rules: 
		{
   		},
		messages: 
		{
		}
	});
});
</script>