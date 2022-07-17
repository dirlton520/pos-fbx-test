<div class="row hidden-print">
	<div class="col-md-3 col-xs-12 col-sm-6 summary-data">
		<div class="info-seven primarybg-info">
			<div class="logo-seven hidden-print"><i class="ti-widget dark-info-primary"></i></div>
			<?php
			echo to_currency($total_amount_due);
			echo '<p>' . lang('reports_total_amount_due') . '</p>';
			?>

		</div>
	</div>
</div>
<?php if (isset($pagination) && $pagination) {  ?>
	<div class="pagination hidden-print alternate text-center fg-toolbar ui-toolbar" id="pagination_top">
		<?php echo $pagination; ?>
	</div>
<?php }  ?>

<div class="row">
	<div class="col-md-12">
		<div class="panel panel-piluku">
			<div class="panel-heading hidden-print">
				<?php echo lang('reports_reports'); ?> - <?php echo $title ?>
				<?php if ($key) { ?>
					<a href="<?php echo site_url("reports/delete_saved_report/" . $key); ?>" class="btn btn-primary text-white hidden-print delete_saved_report pull-right"> <?php echo lang('reports_unsave_report'); ?></a>
				<?php } else { ?>
					<button class="btn btn-primary text-white hidden-print save_report_button pull-right" data-message="<?php echo H(lang('reports_enter_report_name')); ?>"> <?php echo lang('reports_save_report'); ?></button>
				<?php } ?>
			</div>
			<div class="panel-body">
				<?php
				$counter = 0;
				foreach ($report_data as $data) {
				$amount_due = 0;
				?>
					<div id="statement_header" class="store_account_address">
						<div id="company_name" class="form-heading"><?php echo $this->config->item('company'); ?></div>
						<?php if ($this->config->item('company_logo')) { ?>
							<div id="company_logo"><?php echo img(array('src' => $this->Appconfig->get_logo_image())); ?></div>
						<?php } ?>
						<div id="company_address"><?php echo nl2br($this->Location->get_info_for_key('address')); ?></div>
						<div id="company_phone"><?php echo $this->Location->get_info_for_key('phone'); ?></div>
						<?php if ($this->config->item('website')) { ?>
							<div id="website"><?php echo $this->config->item('website'); ?></div>
						<?php } ?>
					</div>

					<?php

					if ($data['customer_info']->company_name) {
						$customer_title = $data['customer_info']->company_name;
					} else {
						$customer_title = $data['customer_info']->first_name . ' ' . $data['customer_info']->last_name;
					}
					?>
					<div class="pull-right"><label class="label label-primary"><?php echo $subtitle; ?></label></div>

					<div class="store_account_address">
						<span><strong><?php echo $customer_title . ' ' . ($data['customer_info']->account_number ? $data['customer_info']->account_number : ''); ?></strong></span><br />
						<?php if ($data['customer_info']->address_1) { ?>
							<span><?php echo $data['customer_info']->address_1 . ' ' . $data['customer_info']->address_2; ?></span><br />
							<span><?php echo $data['customer_info']->city . ', ' . $data['customer_info']->state . ' ' . $data['customer_info']->zip; ?></span>
						<?php } ?>
					</div>

					<div class="table-responsive">
						<table class="table table-striped table-hover data-table tablesorter" id="sortable_table">
							<thead>
								<tr>
									<?php if ($location_count > 1) { ?>
										<td><?php echo lang('common_location'); ?></td>
									<?php } ?>
									<td><?php echo lang('reports_date'); ?></td>
									<td><?php echo lang('reports_sale_id'); ?></td>
									<td><?php echo lang('reports_total'); ?></td>
									<td><?php echo lang('reports_payment'); ?></td>
									<td><?php echo lang('reports_due'); ?></td>
									<?php if (!$hide_items) { ?>
										<td><?php echo lang('reports_items'); ?></td>
									<?php } ?>
								</tr>
							</thead>
							<tbody>
								<?php
								foreach ($data['layaway_transactions'] as $transaction) {
									$hide_row = $this->input->get('hide_paid') && ($transaction['sale_total'] !== NULL && $transaction['sale_total'] == 0);
									 if (!$hide_row) { ?>
										<tr>
											<?php if ($location_count > 1) { ?>
												<td><b><?php echo $transaction['location_name']; ?></b></td>
											<?php } ?>
											<td><b><?php echo date(get_date_format() . ' ' . get_time_format(), strtotime($transaction['sale_time'])); ?></b></td>
											<td><b><?php echo $transaction['sale_id'] ? anchor('sales/receipt/' . $transaction['sale_id'], $this->config->item('sale_prefix') . ' ' . $transaction['sale_id'], array('target' => '_blank')) : '-'; ?></b></td>
											<td><b><?php echo $transaction['sale_total'] > 0 ? to_currency($transaction['sale_total']) : to_currency(0); ?></b></td>
											<td><b><?php //echo to_currency(0); 
													?></td>
											<td><b><?php echo $transaction['sale_total'] > 0 ? to_currency($transaction['sale_total']) : to_currency(0); ?></b></td>
											<?php if (!$hide_items) { ?>
												<td><b><?php echo $transaction['items']; ?></b></td>
											<?php } ?>
										</tr>

										<?php
										$amount_due += $transaction['amount_due'];
										$balance = $transaction['sale_total'];
										$new_balance = 0;
										foreach ($transaction["all_payments"] as $payment) {
											if ($new_balance == 0) {
												$new_balance = ($balance - $payment['payment_amount']);
											} else {
												$new_balance = ($new_balance - $payment['payment_amount']);
											}

											?>

											<tr>
												<?php if ($location_count > 1) { ?>
													<td><?php echo $payment['location_name']; ?></td>
												<?php } ?>
												<td><?php echo date(get_date_format() . ' ' . get_time_format(), strtotime($payment['payment_date'])); ?></td>
												<td><?php //echo $payment['sale_id'] ? anchor('sales/receipt/' . $payment['sale_id'], $this->config->item('sale_prefix') . ' ' . $payment['sale_id'], array('target' => '_blank')) : '-'; 
													?></td>
												<td><?php //echo to_currency(0); 
													?></td>
												<td><?php echo $payment['payment_amount'] > 0 ? to_currency($payment['payment_amount']) : to_currency(0); ?></td>
												<td><?php echo $new_balance > 0 ? to_currency($new_balance) : to_currency(0); ?></td>
												<?php if (!$hide_items) { ?>
													<td><?php echo $payment['payment_type']; ?></td>
												<?php } ?>
											</tr>

											<?php
										}
									}
								}
								?>
							</tbody>
						</table>
					</div>

					<div class="row">
						<div class="col-md-3 col-xs-12 col-sm-6 ">
							<div class="info-seven primarybg-info">
								<div class="logo-seven"><i class="ti-widget dark-info-primary"></i></div>
								<?php echo to_currency($amount_due); ?>
								<p><?php echo lang('common_amount_due'); ?></p>
							</div>
						</div>
					</div>

					<?php
					if ($this->config->item('layaway_statement_message')) {
					?>
						<div class="row">
							<div class="col-md-12 col-xs-12 col-sm-12 ">
								<?php echo $this->config->item('layaway_statement_message'); ?>
							</div>
						</div>
					<?php
					}
					?>

					<?php if ($data['customer_info']->email) { ?>

						<div class="row">
							<div class="col-md-3 col-xs-12 col-sm-6 ">
								<?php
								$email_url = str_replace('reports/generate/layaway_statements', 'reports/layaway_statements_email_customer', current_url());
								$email_url = str_replace('customer_id=' . $this->input->get('customer_id'), 'customer_id=' . $data['customer_info']->person_id, $email_url);
								echo anchor(
									$email_url,
									"<div class='small_button'>" . lang('reports_email_statement') . "</div>",
									array('class' => 'btn btn-primary none email_statement hidden-print', 'title' => lang('reports_email_statement'))
								);
								?>

							</div>
						</div>
					<?php } ?>

					<?php if ($counter != count($report_data) - 1) { ?>
						<div class="page-break" style="page-break-before: always;"></div>
					<?php } ?>
					<?php $counter++; ?>
				<?php } ?>

				<div class="text-center">
					<button class="btn btn-primary text-white hidden-print" id="print_button"> <?php echo lang('common_print'); ?> </button>
				</div>

			</div>
		</div>
	</div>
</div>

<?php if (isset($pagination) && $pagination) {  ?>
	<div class="pagination hidden-print alternate text-center fg-toolbar ui-toolbar" id="pagination_top">
		<?php echo $pagination; ?>
	</div>
<?php }  ?>

<script type="text/javascript">
	$('.email_statement').click(function(e) {
		e.preventDefault();
		$.get($(this).attr('href'));
		show_feedback('success', <?php echo json_encode(lang('reports_email_sent')); ?>, <?php echo json_encode(lang('common_success')); ?>);
	})

	function print_report() {
		window.print();
	}

	$(document).ready(function() {
		$('#print_button').click(function(e) {
			e.preventDefault();
			print_report();
		});
	});
</script>