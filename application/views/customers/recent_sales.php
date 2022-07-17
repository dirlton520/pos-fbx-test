<div class="modal-dialog modal2">
	<div class="modal-content">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-label=<?php echo json_encode(lang('common_close')); ?>><span aria-hidden="true" class="ti-close"></span></button>
			<h4 class="modal-title"><?php echo lang("customers_basic_information"); ?></h4>
		</div>
		<div class="modal-body nopadding">
			<table class="table table-bordered table-hover table-striped">
				<tr>
					<td>
						<?php echo lang('reports_sales_generator_selectField_1'); ?>	
					</td>
					<td>
						<?php echo H($customer_info->first_name.' '.$customer_info->last_name); ?>
					</td>
				</tr>
				<tr>
					<td>
						<?php echo lang('common_address_1'); ?>
					</td>
					<td>
						<?php echo H($customer_info->address_1); ?>
					</td>
				</tr>
				<tr>
					<td>
						<?php echo lang('common_address_2'); ?>
					</td>
					<td>
						<?php echo H($customer_info->address_2); ?>
					</td>
				</tr>
				<tr>
					<td>
						<?php echo lang('common_city'); ?>
					</td>
					<td>
						<?php echo H($customer_info->city); ?>
					</td>
				</tr>
				<tr>
					<td>
						<?php echo lang('common_state'); ?>
					</td>
					<td>
						<?php echo H($customer_info->state); ?>
					</td>
				</tr>
				<tr>
					<td>
						<?php echo lang('common_country'); ?>
					</td>
					<td>
						<?php echo H($customer_info->country); ?>
					</td>
				</tr>
				<tr>
					<td>
						<?php echo lang('common_zip'); ?>
					</td>
					<td>
						<?php echo H($customer_info->zip); ?>
					</td>
				</tr>
				
				<?php					
				if($this->config->item('customers_store_accounts') && $this->Employee->has_module_action_permission('customers', 'edit_store_account_balance', $this->Employee->get_logged_in_employee_info()->person_id)) 
				{
				?>
					<tr>
						<td><?php echo lang('common_store_account_balance');?></td>
						<td class="<?php echo $customer_info->credit_limit!== NULL && $customer_info->balance > $customer_info->credit_limit ? 'credit_limit_warning' : 'credit_limit_ok'; ?>"><?php echo to_currency($customer_info->balance);?></td>
					</tr>

					<tr>
						<td><?php echo lang('common_credit_limit');?></td>
						<td class="<?php echo $customer_info->credit_limit!== NULL && $customer_info->balance > $customer_info->credit_limit ? 'credit_limit_warning' : 'credit_limit_ok'; ?>"><?php echo $customer_info->credit_limit!== NULL ? to_currency($customer_info->credit_limit) : lang('common_not_set');?></td>
					</tr>
				<?php
				}
				?>
				
				
			</table>
		</div>
	</div>
</div>