<div class="row">
	<div class="col-md-12">
		<div class="panel panel-piluku">
			<div class="panel-heading">
				<?php echo lang('reports_reports'); ?> - <?php echo lang('reports_profit_and_loss') ?>
				<?php echo $subtitle ?>
				<?php if($key) { ?>
					<a href="<?php echo site_url("reports/delete_saved_report/".$key);?>" class="btn btn-primary text-white hidden-print delete_saved_report pull-right"> <?php echo lang('reports_unsave_report'); ?></a>	
				<?php } else { ?>
					<button class="btn btn-primary text-white hidden-print save_report_button pull-right" data-message="<?php echo H(lang('reports_enter_report_name'));?>"> <?php echo lang('reports_save_report'); ?></button>
				<?php } ?>				
			</div>
			<div class="panel-body">
				<div class="col-md-6">
					<h3><?php echo lang('reports_sales'); ?></h3>
					<div class="table-responsive">
						<table class="table table-bordered table-reports  table-striped table-hover  tablesorter">
							<?php foreach($details_data['sales_by_payments'] as $sale_payment) { ?>
							<tr>
								<td><?php echo $sale_payment['payment_type']; ?></td>
								<td style="text-align: right;"><?php echo to_currency($sale_payment['payment_amount']); ?></td>
							</tr>

							<?php } ?>
							<tr>
								<td colspan="2">-</td>
							</tr>
							<?php foreach($details_data['sales_by_category'] as $category) { ?>
							<tr>
								<td><?php echo $this->Category->get_full_path($category['category_id']); ?></td>
								<td style="text-align: right;"><?php echo to_currency($category['total']); ?></td>
							</tr>

							<?php } ?>
							
						</table>
					</div>

					<h3><?php echo lang('reports_returns'); ?></h3>
					<div class="table-responsive">
						<table class="table table-bordered table-striped table-hover  tablesorter">
							
							<?php foreach($details_data['returns_by_payments'] as $sale_payment) { ?>
							<tr>
								<td><?php echo $sale_payment['payment_type']; ?></td>
								<td style="text-align: right;"><?php echo to_currency($sale_payment['payment_amount']); ?></td>
							</tr>
							<?php } ?>
							
							<tr>
								<td colspan="2">-</td>
							</tr>
							
							<?php foreach($details_data['returns_by_category'] as $category) { ?>
							<tr>
								<td><?php echo $category['category']; ?></td>
								<td style="text-align: right;"><?php echo to_currency($category['total']); ?></td>
							</tr>

							<?php } ?>
						</table>
					</div>

					<h3><?php echo lang('reports_discounts'); ?></h3>
					<div class="table-responsive">
						<table class="table table-bordered table-striped table-hover  tablesorter">
							<tr>
								<td><?php echo lang('common_discount'); ?></td>
								<td style="text-align: right;"><?php echo to_currency($details_data['discount_total']['discount']); ?></td>
							</tr>
						</table>
					</div>
					
					<h3><?php echo lang('reports_taxes'); ?></h3>
					<div class="table-responsive">
						<table class="table table-bordered table-striped table-hover  tablesorter">
							<tr>
								<td><?php echo lang('reports_taxes'); ?></td>
								<td style="text-align: right;"><?php echo to_currency($details_data['taxes']['tax']); ?></td>
							</tr>
						</table>
					</div>
					

					<h3><?php echo lang('reports_total_profit_and_loss'); ?></h3>
					<div class="table-responsive">
						<table class="table table-bordered table-striped table-hover  tablesorter">
							<tr>
								<td><?php echo lang('reports_total'); ?></td>
								<td style="text-align: right;"><?php echo to_currency($details_data['total']); ?></td>
							</tr>
						</table>
					</div>

				</div>
				<div class="col-md-6">
					<h3><?php echo lang('reports_receivings'); ?></h3>
					<div class="table-responsive">
						<table class="table table-bordered table-striped table-hover  tablesorter">
							<?php foreach($details_data['receivings_by_category'] as $category) { ?>
							<tr>
								<td><?php echo $category['category']; ?></td>
								<td style="text-align: right;"><?php echo to_currency($category['total']); ?></td>
							</tr>

							<?php } ?>
						</table>
					</div>
					
					<?php
					if($this->Employee->has_module_action_permission('reports','view_expenses',$this->Employee->get_logged_in_employee_info()->person_id))
					{
					?>
						<br />
						<h2><?php echo lang('reports_expenses'); ?></h2>
						<div class="table-responsive">
						<table style="width: 40%;" class="table table-bordered table-striped table-hover  tablesorter">
							<tr>
								<td><?php echo lang('reports_total'); ?></td>
								<td style="text-align: right;"><?php echo to_currency($details_data['expense_amount']); ?></td>
							</tr>
						</table>
						</div>
						<br />
					<?php } ?>
					
					<br />
					<h2><?php echo lang('reports_commission'); ?></h2>
					<div class="table-responsive">
					<table style="width: 40%;" class="table table-bordered table-striped table-hover  tablesorter">
						<tr>
							<td><?php echo lang('reports_commission'); ?></td>
							<td style="text-align: right;"><?php echo to_currency($details_data['commission']); ?></td>
						</tr>
					</table>
					</div>
					<br />
					
					<?php
					if($this->Employee->has_module_action_permission('reports','show_profit',$this->Employee->get_logged_in_employee_info()->person_id))
					{
					?>
						<h3><?php echo lang('common_profit'); ?></h3>
						<div class="table-responsive">
							<table class="table table-bordered table-striped table-hover  tablesorter">
								<tr>
									<td><?php echo lang('reports_total'); ?></td>
									<td style="text-align: right;"><?php echo to_currency($details_data['profit']); ?></td>
								</tr>
							</table>
						</div>
					<?php } ?>
				</div>
			</div>
		</div>
	</div>
	<br />        
</div>
</div>
</div>
</div>