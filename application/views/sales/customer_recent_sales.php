<style>
	.customer-recent-sales {
		min-width: 90% !important;
	}
</style>
<div class="modal-dialog customer-recent-sales">
	<div class="modal-content">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-label=<?php echo json_encode(lang('common_close')); ?>><span aria-hidden="true" class="ti-close"></span></button>
			<h5 class="modal-title"><?php echo lang('sales_recent_sales') . ' ' . H($customer); ?></h5>
			<h6><?php echo $customer_comments; ?></h6>
		</div>
		<div class="modal-body nopadding">
			<ul class="nav nav-tabs">
				<li class="active"><a data-toggle="tab" href="#completed"><?php echo lang('sales_completed_sales'); ?></a></li>
				<li><a data-toggle="tab" href="#suspended"><?php echo lang('sales_suspended_sales'); ?></a></li>
				<li><a data-toggle="tab" href="#info"><?php echo lang('customers_basic_information'); ?></a></li>
			</ul>

			<div class="tab-content">
				
				<div id="info" class="tab-pane fade in">
					
					<table class="table table-bordered table-hover table-striped" style="width: 80%;margin:10px auto;padding:20px;">
						<?php
						 $customer_info = $this->Customer->get_info($customer_id);
						 $addresss = $customer_info->address_1.'<br />'.$customer_info->address_2.'<br />'.$customer_info->city.' '.$customer_info->state.','.$customer_info->zip;
						 
						 ?>
						 
 						<tr><td width="40%"><?php echo lang('common_name')?></td> <td><?php echo $customer_info->full_name;?></td></tr>
 						<tr><td width="40%"><?php echo lang('common_email')?></td> <td><?php echo $customer_info->email;?></td></tr>
 						<tr><td width="40%"><?php echo lang('common_phone_number')?></td> <td><?php echo $customer_info->phone_number;?></td></tr>
 						<tr><td width="40%"><?php echo lang('common_address')?></td> <td><?php echo $addresss;?></td></tr>
 						<tr><td width="40%"><?php echo lang('common_comments')?></td> <td><?php echo nl2br($customer_info->comments);?></td></tr>
 						<tr><td width="40%"><?php echo lang('common_internal_notes')?></td> <td><?php echo nl2br($customer_info->internal_notes);?></td></tr>
 						<tr><td width="40%"><?php echo lang('common_customer_info_popup')?></td> <td><?php echo nl2br($customer_info->customer_info_popup);?></td></tr>
 						<tr><td width="40%"><?php echo lang('common_company')?></td> <td><?php echo $customer_info->company_name;?></td></tr>
 						<tr><td width="40%"><?php echo lang('customers_account_number')?></td> <td><?php echo $customer_info->account_number;?></td></tr>
						 
					<?php
					  for($k=1;$k<=NUMBER_OF_PEOPLE_CUSTOM_FIELDS;$k++) 
					  {
					 	 $customer_custom_field_name = $this->Customer->get_custom_field($k);
						 if ($customer_custom_field_name)
						 {
						 	 $customer_custom_field_value = $customer_info->{"custom_field_${k}_value"};
							 
							if ($this->Customer->get_custom_field($k,'type') == 'checkbox')
							{
								$format_function = 'boolean_as_string';
							}
							elseif($this->Customer->get_custom_field($k,'type') == 'date')
							{
								$format_function = 'date_as_display_date';				
							}
							elseif($this->Customer->get_custom_field($k,'type') == 'email')
							{
								$format_function = 'strsame';					
							}
							elseif($this->Customer->get_custom_field($k,'type') == 'url')
							{
								$format_function = 'strsame';					
							}
							elseif($this->Customer->get_custom_field($k,'type') == 'phone')
							{
								$format_function = 'strsame';					
							}
							elseif($this->Customer->get_custom_field($k,'type') == 'image')
							{
								$this->load->helper('url');
								$format_function = 'file_id_to_image_thumb_right';					
							}
							elseif($this->Customer->get_custom_field($k,'type') == 'file')
							{
								$this->load->helper('url');
								$format_function = 'file_id_to_download_link';					
							}
							else
							{
								$format_function = 'strsame';
							}
							?>
 								<tr><td><?php echo $customer_custom_field_name; ?></td> <td><?php echo $format_function($customer_custom_field_value);?></td></tr>
						 <?php	
						 }
							
					 }
						?>
					</table>
					
				</div>
				
				<div id="completed" class="tab-pane fade in active">
					<table id="recent_sales" class="table">
						<tr>
							<th><?php echo lang('common_date'); ?></th>
							<th><?php echo lang('sales_delveried_to'); ?></th>
							<th><?php echo lang('common_payments'); ?></th>
							<th><?php echo lang('common_items_purchased'); ?></th>
							<th><?php echo lang('common_recp'); ?></th>
							<?php if (!$this->config->item('disable_sale_cloning')) { ?>
								<th><?php echo lang('common_clone'); ?></th>
							<?php } ?>
							<th><?php echo lang('common_comment'); ?></th>
						</tr>

						<?php foreach ($recent_sales as $sale) { ?>
							<tr class="table-row-hover">
								<td><?php echo date(get_date_format() . ' @ ' . get_time_format(), strtotime($sale['sale_time'])); ?></td>
								<td><?php echo $sale['delivered_to']; ?></td>
								<td><?php echo $sale['payment_type']; ?></td>
								<td><?php echo to_quantity($sale['items_purchased']); ?></td>
								<td><?php echo anchor('sales/receipt/' . $sale['sale_id'], lang('sales_receipt'), array('target' => '_blank')); ?></td>
								<?php if (!$this->config->item('disable_sale_cloning')) { ?>
									<td><?php echo anchor('sales/clone_sale/' . $sale['sale_id'], lang('common_clone')); ?></td>
								<?php } ?>
								<td><?php echo $sale['comment']; ?></td>
							</tr>
						<?php } ?>
					</table>
				</div>
				<div id="suspended" class="tab-pane fade">
					<table id="recent_sales" class="table">
						<tr>
							<th><?php echo lang('sales_suspended_sale_id'); ?></th>
							<th><?php echo lang('common_date'); ?></th>
							<th><?php echo lang('common_type'); ?></th>

							<th><?php echo lang('sales_customer'); ?></th>

							<th><?php echo lang('reports_items'); ?></th>
							<th><?php echo lang('common_total'); ?></th>
							<th><?php echo lang('common_amount_paid'); ?></th>

							<th><?php echo lang('common_amount_due'); ?></th>
							<th><?php echo lang('common_last_payment_date'); ?></th>
							<th><?php echo lang('common_comments'); ?></th>

							<th><?php echo lang('common_unsuspend'); ?></th>
							<th><?php echo lang('sales_receipt'); ?></th>
							<th><?php echo lang('common_email_receipt'); ?></th>

							<?php if ($this->Employee->has_module_action_permission('sales', 'delete_suspended_sale', $this->Employee->get_logged_in_employee_info()->person_id)) { ?>
								<th><?php echo lang('common_delete'); ?></th>
							<?php } ?>

						</tr>

						<?php foreach ($suspended_sales as $ssale) { ?>
							<tr class="table-row-hover">
								<td><?php echo ($this->config->item('sale_prefix') ? $this->config->item('sale_prefix') : 'POS') . ' ' . $ssale['sale_id']; ?></td>
								<td><?php echo date(get_date_format() . ' @ ' . get_time_format(), strtotime($ssale['sale_time'])); ?></td>
								<td><?php echo $ssale['suspended'] == 1  ? ($this->config->item('user_configured_layaway_name') ? $this->config->item('user_configured_layaway_name') : lang('common_layaway')) : ($ssale['suspended'] > 2 ? $ssale['sale_type_name'] : lang('common_estimate')); ?></td>
								<td>
									<?php
									if (isset($ssale['customer_id'])) {
										$customer = $this->Customer->get_info($ssale['customer_id']);
										$company_name = $customer->company_name;
										if ($company_name) {
											$val = $customer->first_name . ' ' . $customer->last_name . ' (' . $customer->company_name . ')';
										} else {
											$val =  $customer->first_name . ' ' . $customer->last_name;
										}

										echo $val;
									}
									?>
								</td>
								<td><?php echo $ssale['items']; ?></td>
								<td><?php echo to_currency($ssale['sale_total']); ?></td>
								<td><?php echo to_currency($ssale['amount_paid']); ?></td>
								
								<td><?php echo to_currency($ssale['amount_due']); ?></td>
								<td><?php echo $ssale['last_payment_date'] !== lang('common_none') ? date(get_date_format() . ' @ ' . get_time_format(), strtotime($ssale['last_payment_date'])) : lang('common_none'); ?></td>
								<td><?php echo $ssale['comment']; ?></td>

								<td>
									<?php
									echo form_open('sales/unsuspend');
									echo form_hidden('suspended_sale_id', $ssale['sale_id']);

									echo '<input type="submit" name="submit" value="' . lang('common_unsuspend') . '" id="submit_unsuspend" class="btn btn-primary" />';
									echo form_close();
									?>
								</td>

								<td>
									<?php
									echo form_open('sales/receipt/' . $ssale['sale_id'], array('method' => 'get', 'class' => 'form_receipt_suspended_sale'));
									echo '<input type="submit" name="submit" value="' . lang('common_recp') . '" id="submit_receipt" class="btn btn-primary" />';
									echo form_close();
									?>
								</td>

								<td>
									<?php
									if ($ssale['email']) {
										echo form_open('sales/email_receipt/' . $ssale['sale_id'], array('method' => 'get', 'class' => 'form_email_receipt_suspended_sale'));
										echo '<input type="submit" name="submit" value="' . lang('common_email') . '" id="submit_receipt" class="btn btn-primary" />';
										echo form_close();
									}
									?>
								</td>

								<?php
								if ($this->Employee->has_module_action_permission('sales', 'delete_suspended_sale', $this->Employee->get_logged_in_employee_info()->person_id)) {
									echo '<td>';
									echo form_open('sales/delete_suspended_sale', array('class' => 'form_delete_suspended_sale'));
									echo form_hidden('suspended_sale_id', $ssale['sale_id']);
									echo form_hidden('redirection', 'sales');
									echo '<input type="submit" name="submitf" value="' . lang('common_delete') . '" id="submit_delete" class="btn btn-danger">';
									echo form_close();
									echo '</td>';
								}
								?>
							</tr>
						<?php } ?>
					</table>
				</div>
			</div>


		</div>
	</div>
</div>
<script type="text/javascript">
	$(".form_delete_suspended_sale").submit(function() {
		var formDelete = this;
		bootbox.confirm(<?php echo json_encode(lang("sales_delete_confirmation")); ?>, function(result) {
			if (result) {
				formDelete.submit();
			}
		});

		return false;

	});

	$(".form_email_receipt_suspended_sale").ajaxForm({
		success: function() {
			bootbox.alert("<?php echo lang('common_receipt_sent'); ?>");
		}
	});
</script>