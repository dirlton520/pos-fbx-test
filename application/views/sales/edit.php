<?php $this->load->view("partial/header"); ?>
<div class="row" id="form">
	<div class="col-md-12">
	<div class="panel panel-piluku">
			<div class="panel-heading">
				<?php echo lang('sales_edit_sale').' '.$this->config->item('sale_prefix').' '.$sale_info['sale_id']; ?>
			</div>
			<div class="panel-body">

				<?php echo form_open("sales/save/".$sale_info['sale_id'],array('id'=>'sales_edit_form','class'=>'form-horizontal')); ?>


				<div class="form-group">	
					<?php echo form_label(lang('sales_receipt').':', 'sales_receipt',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label ')); ?>
					<div class="col-sm-9 col-md-9 col-lg-10 sale-s">
						<?php echo anchor('sales/receipt/'.$sale_info['sale_id'], $this->config->item('sale_prefix').' '.$sale_info['sale_id'], array('target' => '_blank'));?>
					</div>
				</div>

<?php
if($this->Employee->has_module_action_permission('sales', 'change_sale_date', $this->Employee->get_logged_in_employee_info()->person_id))
{
?>
				<div class="form-group">	
					<?php echo form_label(lang('common_date').':', 'date',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label ')); ?>
					<div class="col-sm-9 col-md-3 col-lg-3">
						<div class="input-group">
							<span class="input-group-addon bg">
	                           <i class="ion ion-ios-calendar-outline"></i>
	                       	</span>
	                       	<?php echo form_input(array('name'=>'date','value'=>date(get_date_format()." ".get_time_format(), strtotime($sale_info['sale_time'])), 'id'=>'date', 'class'=>'form-control'));?>
						</div>
					</div>
				</div>
<?php	
}
else
{
	echo form_hidden('date',date(get_date_format()." ".get_time_format(), strtotime($sale_info['sale_time'])));
}
?>
			

				<div class="form-group">	
					<?php echo form_label(lang('sales_customer').':', 'customer',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label ')); ?>
					<div class="col-sm-9 col-md-3 col-lg-3 sale-s">
						
						<?php if (!$store_account_payment && !$is_ecommerce && !$store_account_charge && !$this->config->item('enable_customer_loyalty_system')) { ?>
						<select id="customer_id" name="customer_id" class="add-customer-input search" > 
						 <?php	if($sale_info['customer_id'])
						 	{ 
								echo '<option value="'.$sale_info['customer_id'].'" selected="selected">'.$selected_customer_name.'</option>';

						  	}
						  ?>
						</select>
						<?php }elseif($selected_customer_name) {?>
							<?php echo '<span>'.$selected_customer_name.'</span>'; ?>
							<?php echo '<input type="hidden" name="customer_id" value="'.$sale_info['customer_id'].'" />'; ?>
						<?php } ?>
						&nbsp;
						<?php if ($sale_info['customer_id'] && isset($selected_customer_email) && $selected_customer_email) { ?>
						<?php echo anchor('sales/email_receipt/'.$sale_info['sale_id'], lang('common_email_receipt'), array('id' => 'email_receipt'));?>
						<?php }?>
					</div>
				</div>


				<div class="form-group">	
					<?php echo form_label(lang('common_employee').':', 'employee',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label ')); ?>
					<div class="col-sm-9 col-md-3 col-lg-3 sale-s">
						<?php echo form_dropdown('employee_id', $employees, $sale_info['employee_id'], 'id="employee_id" class="span3"');?>
					</div>
				</div>


				<div class="form-group">	
					<?php echo form_label(lang('sales_comments_receipt').':', 'sales_comments_receipt',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label ')); ?>
					<div class="col-sm-9 col-md-9 col-lg-10 sale-s">
						<?php echo form_checkbox(array(
							'name'=>'show_comment_on_receipt',
							'id'=>'show_comment_on_receipt',
							'value'=>'1',
							'checked'=>(boolean)$sale_info['show_comment_on_receipt'])
						);
						?>
						<label for="show_comment_on_receipt"><span></span></label>
					</div>
				</div>

				<div class="form-group">	
					<?php echo form_label(lang('common_comment').':', 'comment',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label ')); ?>
					<div class="col-sm-9 col-md-3 col-lg-3">
						<?php echo form_textarea(array('name'=>'comment','value'=>$sale_info['comment'],'rows'=>'4','cols'=>'23', 'id'=>'comment', 'class'=>'form-control text-area'));?>
					</div>
				</div>


				<div class="form-group">
					<div class="col-sm-9 col-md-3 col-md-offset-2">
						<?php
						echo form_submit(array(
							'name'=>'submit_edit',
							'id'=>'submit_edit',
							'value'=>lang('common_save'),
							'class'=>' btn btn-primary')
						);
						?>
					</div>
				</div>

			</form>

			<?php 
			
			if (!$is_ecommerce) 
			{
			
				if ($sale_info['deleted'])
				{
					?>
					<?php echo form_open("sales/undelete/".$sale_info['sale_id'],array('id'=>'sales_undelete_form','class'=>'form-horizontal')); 
					echo form_hidden('do_undelete','1');
					?>

					<div class="form-group">	
						<?php echo form_label(lang('common_deleted_by').':&nbsp;', 'deleted_by',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label')); ?>

						<div class="controls" style="padding-top:7px;">
							<?php echo anchor('employees/view/'.$sale_info['deleted_by'], $this->Employee->get_info($sale_info['deleted_by'])->first_name.' '.$this->Employee->get_info($sale_info['deleted_by'])->last_name, array('target' => '_blank'));?>
						</div>
					</div>
					
					<?php if (!$store_account_payment && !$is_purchase_points) { ?>
					<div class="form-group">
						<div class="col-md-9 col-md-offset-2">
							<?php
							echo form_submit(array(
								'name'=>'submit_undelete',
								'id'=>'submit_undelete',
								'value'=>lang('sales_undelete_entire_sale'),
								'class'=>' btn btn-primary')
							);
							?>	
						</div>
					
					</div>
					<?php } ?>
				</form>
				<?php
			}
			else
			{
				if ((!$store_account_payment && !$is_purchase_points && $this->Employee->has_module_action_permission('sales', 'edit_sale', $this->Employee->get_logged_in_employee_info()->person_id))) {

					$edit_sale_url = $sale_info['suspended'] > 0 ? 'unsuspend' : 'change_sale';

					echo form_open("sales/$edit_sale_url/".$sale_info['sale_id'],array('id'=>'sales_change_form','class'=>'form-horizontal')); ?>

					<div class="form-group">
						<div class="col-md-9 col-md-offset-2">
							<?php
								echo form_submit(array(
									'name'=>'submit_change_sale',
									'id'=>'submit_change_sale',
									'value'=>lang('sales_change_sale'),
									'class'=>' btn btn-primary')
								); 
							
							?>	
						</div>
					</div>
				</form>
			<?php
			}
			if ($this->Employee->has_module_action_permission('sales', 'delete_sale', $this->Employee->get_logged_in_employee_info()->person_id) && !$is_purchase_points)
			{ 
				echo form_open("sales/delete/".$sale_info['sale_id'],array('id'=>'sales_delete_form','class'=>'form-horizontal')); 
				echo form_hidden('do_delete','1');
				?>
			
				<?php
						if ($this->Sale->can_void_cc_sale($sale_info['sale_id']))
						{
						?>
						<div class="form-group">	
							<?php echo form_label(lang('sales_void_and_refund_credit_card').':', 'sales_void_and_refund_credit_card',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label ')); ?>
							<div class="col-sm-9 col-md-9 col-lg-10 sale-s">
								<?php echo form_checkbox(array(
									'name'=>'sales_void_and_refund_credit_card',
									'id'=>'sales_void_and_refund_credit_card',
									'value'=>'1',
									'checked'=> '1',
								));
								?>
								<label for="sales_void_and_refund_credit_card"><span></span></label>
							</div>
						</div>
					<?php
					}
					elseif($this->Sale->can_void_cc_return($sale_info['sale_id']))
					{
						?>
						<div class="form-group">	
							<?php echo form_label(lang('sales_void_and_cancel_return').':', 'sales_void_and_cancel_return',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label ')); ?>
							<div class="col-sm-9 col-md-9 col-lg-10 sale-s">
								<?php echo form_checkbox(array(
									'name'=>'sales_void_and_cancel_return',
									'id'=>'sales_void_and_cancel_return',
									'value'=>'1',
									'checked'=> '1',
								));
								?>
								<label for="sales_void_and_cancel_return"><span></span></label>
							</div>
						</div>
					<?php
					}
					?>

				<?php if (!$store_account_payment) { ?>

				<div class="form-group">
					<div class="col-md-9 col-md-offset-2">
						<?php
						echo form_submit(array(
							'name'=>'submit_delete',
							'id'=>'submit_delete',
							'value'=>lang('sales_delete_entire_sale'),
							'class'=>' btn btn-danger')
						);
						?>
					</div>
				</div>
				<?php } ?>
			</form>
			<?php
		} 
	}
}
?>
</div>
</div>
</div>
<?php $this->load->view("partial/footer"); ?>

<script type="text/javascript" language="javascript">
	$(document).ready(function()
	{	
		$("#employee_id").selectize();
		
		$('#customer_id').selectize({
			valueField: 'value',
			labelField: 'label',
			searchField: 'label',
			loadThrottle: 215,
			options: [],
			placeholder: "<?php echo lang('common_search'); ?> <?php echo lang('module_customers'); ?>",
			create: false,
			render: {
				option: function(item, escape) {
					return '<div class="customer-suggestion-badge suggestions">' +
								'<div class="details">' +
									'<a href="#" class="name">' +
									escape(item.label) +
									'</a>' +
								'</div>' +
							'</div>';
				}
			},

			load: function(query, callback) {
				if (!query.length) return callback();
				$.ajax({
					url:'<?php echo site_url("sales/customer_search");?>'+'?term='+encodeURIComponent(query),
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


		$("#email_receipt").click(function()
		{
			$.get($(this).attr('href'), function()
			{
				show_feedback('success', <?php echo json_encode(lang('common_receipt_sent')); ?>, <?php echo json_encode(lang('common_success')); ?>);
			});

			return false;
		});
				
		date_time_picker_field($('#date'), JS_DATE_FORMAT+ " "+JS_TIME_FORMAT);
		
		$("#sales_delete_form").submit(function()
		{
			var deleteForm = this;
			
			bootbox.confirm(<?php echo json_encode(lang("sales_delete_confirmation")); ?>, function(result)
			{
				if(result)
				{
					deleteForm.submit();
				}
			});
			
			return false;
		});

		$("#sales_undelete_form").submit(function()
		{
			var unDeleteForm = this;
			
			bootbox.confirm(<?php echo json_encode(lang("sales_undelete_confirmation")); ?>, function(result)
			{
				if(result)
				{
					unDeleteForm.submit();
				}
			});
			
			return false;
		});

		$('#sales_edit_form').validate({
			submitHandler:function(form)
			{
				$(form).ajaxSubmit({
					success:function(response)
					{
						if(response.success)
						{
							submitting = false;
							show_feedback('success', response.message, <?php echo json_encode(lang('common_success')); ?>);

						}
						else
						{
							submitting = false;
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