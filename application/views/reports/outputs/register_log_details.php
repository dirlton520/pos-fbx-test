<div class="row">

	<div class="text-center">
		<button class="btn btn-primary text-white hidden-print" id="print_button" onclick="window.print();"> <?php echo lang('common_print'); ?> </button>
		<?php if($key) { ?>
			<a href="<?php echo site_url("reports/delete_saved_report/".$key);?>" class="btn btn-primary text-white hidden-print delete_saved_report pull-right"> <?php echo lang('reports_unsave_report'); ?></a>	
		<?php } else { ?>
			<button class="btn btn-primary text-white hidden-print save_report_button pull-right" data-message="<?php echo H(lang('reports_enter_report_name'));?>"> <?php echo lang('reports_save_report'); ?></button>
		<?php } ?>
	</div>
	<br />

	<div class="col-md-12">			
		<?php
		
		if($register_log[0]->shift_end=='0000-00-00 00:00:00')
		{
			$shift_end=lang('reports_register_log_open');	
		}
		else
		{
			$shift_end = date(get_date_format(). ' '.get_time_format(), strtotime($register_log[0]->shift_end));
		}
		?>
		
		<div class="row" id="register_log_details">
			<div class="col-lg-4 col-md-12">
				
			<ul class="list-group">
				<li class="list-group-item"><?php echo lang('reports_register_log_id'). ': <strong class="pull-right">'. $register_log[0]->register_log_id; ?></strong></li>
				<li class="list-group-item"><?php echo lang('common_register_name'). ': <strong class="pull-right">'. $register_log[0]->register_name; ?></strong></li>
				<li class="list-group-item"><?php echo lang('reports_employee_open'). ': <strong class="pull-right">'. $register_log[0]->open_first_name.' '.$register_log[0]->open_last_name; ?></strong></li>
				<li class="list-group-item"><?php echo lang('reports_close_employee'). ': <strong class="pull-right">'.$register_log[0]->close_first_name.' '.$register_log[0]->close_last_name;  ?></strong></li>
				<li class="list-group-item"><?php echo lang('reports_shift_start'). ': <strong class="pull-right">'. date(get_date_format(). ' '.get_time_format(), strtotime($register_log[0]->shift_start)); ?></strong></li>
				<li class="list-group-item"><?php echo lang('reports_shift_end'). ': <strong class="pull-right">'. $shift_end; ?></strong></li>
				<li class="list-group-item"><?php echo lang('reports_notes'). ': <strong class="pull-right">'. $register_log[0]->notes; ?></strong></li>
			</ul>
			
					<?php foreach ($register_log as $register_log_row) {?>
				<ul class="list-group">
						<li class="list-group-item"><?php echo (strpos($register_log_row->payment_type,'common_') !== FALSE ? lang($register_log_row->payment_type) : $register_log_row->payment_type).' '.lang('common_open_amount'). ': <strong class="pull-right">'. to_currency($register_log_row->open_amount); ?></strong></li>
					
						<?php if ($register_log_row->payment_type == 'common_cash') {?>
							<?php foreach($this->Register->get_cash_count_details($register_log_row->register_log_id,'open') as $denom=>$count) { ?>
								<li class="list-group-item"><?php echo $denom; ?>:  <strong class="pull-right"><?php echo to_quantity($count); ?></strong> </span></li>
							<?php } ?>
						<?php } ?>
						
						
						<li class="list-group-item"><?php echo (strpos($register_log_row->payment_type,'common_') !== FALSE ? lang($register_log_row->payment_type) : $register_log_row->payment_type).' '.lang('reports_close_amount'). ': <strong class="pull-right">'. to_currency($register_log_row->close_amount); ?></strong></li>
					  
						<?php if ($register_log_row->payment_type == 'common_cash') {?>
							<?php foreach($this->Register->get_cash_count_details($register_log_row->register_log_id,'close') as $denom=>$count) { ?>
								<li class="list-group-item"><?php echo $denom; ?>:  <strong class="pull-right"><?php echo to_quantity($count); ?></strong> </span></li>
							<?php } ?>						
						<?php } ?>
						
						
						<?php if ($register_log_row->payment_type == 'common_cash') {?>
							<li class="list-group-item"><?php echo lang('sales_amount_of_cash_to_desposit_in_bank'); ?>:  <strong class="pull-right"><?php echo to_currency($register_log_row->close_amount-$this->config->item('amount_of_cash_to_be_left_in_drawer_at_closing')); ?></strong> </span></li>
						<?php } ?>
						<li class="list-group-item"><?php echo (strpos($register_log_row->payment_type,'common_') !== FALSE ? lang($register_log_row->payment_type) : $register_log_row->payment_type).' '.lang('common_sales'). ': <strong class="pull-right">'. to_currency($register_log_row->payment_sales_amount); ?></strong></li>
						<li class="list-group-item"><?php echo (strpos($register_log_row->payment_type,'common_') !== FALSE ? lang($register_log_row->payment_type) : $register_log_row->payment_type).' '.lang('common_total_additions'). ': <strong class="pull-right">'. to_currency($register_log_row->total_payment_additions); ?></strong></li>
						<li class="list-group-item"><?php echo (strpos($register_log_row->payment_type,'common_') !== FALSE ? lang($register_log_row->payment_type) : $register_log_row->payment_type).' '.lang('common_total_subtractions'). ': <strong class="pull-right">'. to_currency($register_log_row->total_payment_subtractions); ?></strong></li>
						<li class="list-group-item"><?php echo lang('reports_difference'). ': <strong class="pull-right">'. to_currency($register_log_row->difference); ?></strong></li>
				</ul>
						<?php } ?>
			</div>

			<div class="col-lg-8  col-md-12">
				<div class="panel panel-piluku">
					<div class="panel-heading">
						<h3 class="panel-title">
							<?php echo lang('reports_adds_and_subs');?>
						</h3>
					</div>
					<div class="panel-body nopadding table_holder  table-responsive" >
						<table class="table  table-hover table-reports table-bordered">
							<thead>
								<tr>
									<th><?php echo lang('reports_date')?></th>
									<th><?php echo lang('reports_employee')?></th>
									<th><?php echo lang('common_payment')?></th>
									<th><?php echo lang('common_amount')?></th>
									<th><?php echo lang('reports_notes')?></th>
								</tr>
							</thead>
							<tbody>
							<?php 
								if ($register_log_details != FALSE)
								{
									foreach($register_log_details as $row) {?>
									<tr>
										<td><?php echo date(get_date_format(). ' '.get_time_format(), strtotime($row['date']));?></td>
										<td><?php echo $row['employee_name'];?></td>
										<td><?php echo lang($row['payment_type']);?></td>
										<td><?php echo to_currency($row['amount']);?></td>
										<td><?php echo $row['note'];?></td>
									</tr>
									<?php } 	
								}
								?>
							</tbody>
						</table>
					</div>		
				</div>
			</div>
			<!-- Col-md-6 -->

		</div> 
		<!-- row -->

	</div>
</div>