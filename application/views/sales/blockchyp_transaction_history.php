<?php $this->load->view("partial/header"); ?>

	<div class="container-fluid">
		
		<?php
		if ($this->input->get('error'))
		{
		?>
		<div class="alert alert-danger">
			<strong><?php echo H($this->input->get('error'));?></strong>
		</div>
		<?php
		}
		elseif ($this->input->get('success'))
		{
			?>
			<div class="alert alert-success">
				<strong><?php echo H($this->input->get('success'));?></strong>
			</div>
			<?php
		}
		?>
		<form action="" method="get">


		<div id="report_date_range_complex" class="col-sm-12 col-md-12 col-lg-12">
			<div class="row">
				<div class="col-md-5">
					<div class="input-group input-daterange" id="reportrange">
						<span class="input-group-addon bg date-picker"><?php echo lang('reports_from'); ?></span>
	             <input type="text" class="form-control start_date" name="start_date" id="start_date" value="<?php echo $start_date; ?>">
	        </div>
				</div>
	
				<div class="col-md-5">
					<div class="input-group input-daterange" id="reportrange1">
	        <span class="input-group-addon bg date-picker"><?php echo lang('reports_to'); ?></span>
	       <input type="text" class="form-control end_date" name="end_date" id="end_date" value="<?php echo $end_date; ?>">
	      	</div>	
				</div>
				
				<div class="col-md-2">
					<input type="submit" class="btn btn-primary" value="<?php echo lang('common_filter'); ?>">
				</div>
			</div>
		</div>

		</form>
		
		<div class="row manage-table">
			<div class="panel panel-piluku">
				<div class="panel-heading">
					<h3 class="panel-title hidden-print">
						 <?php echo lang('sales_list_of_credit_transactions'); ?>
					</h3>
				</div>
				<div class="panel-body nopadding table_holder table-responsive">
						<table class="table table-bordered table-striped table-hover data-table" id="dTable">
						<thead>
							
							<tr>
								<th><?php echo lang('common_date'); ?></th>
								<th><?php echo lang('common_id'); ?></th>
								<th><?php echo lang('common_sale_id'); ?></th>
								<th><?php echo lang('common_approved'); ?></th>
								<th><?php echo lang('sales_response_description'); ?></th>
								<th><?php echo lang('sales_card_holder'); ?></th>
								<th><?php echo lang('common_amount'); ?></th>
								<th><?php echo lang('sales_transaction_type'); ?></th>
								<th><?php echo lang('sales_entry_method'); ?></th>
								<th><?php echo lang('common_payment_type'); ?></th>
								<th><?php echo lang('sales_masked_card'); ?></th>
								<th><?php echo lang('sales_void_return'); ?></th>
							</tr>
						</thead>
						<tbody>
					<?php
					foreach ($transactions as $transaction)
					{
					?>
						<tr>
							<td><?php echo date(get_date_format().' '.get_time_format(),strtotime($transaction['timestamp']));?></td>
							<td><?php echo $transaction['transactionId'];?></td>
							<td>
							<?php
							if ($sale_id = $this->Sale->get_sale_id_from_payment_ref_no($transaction['transactionId']))
							{
								echo anchor('sales/receipt/'.$sale_id,$this->config->item('sale_prefix').' '.$sale_id, array('target' => '_blank'));
							}
							else
							{
								echo lang('common_unknown');
							}
							?>
							</td>
							<td><?php echo $transaction['approved'] ? lang('common_yes') : lang('common_no');?></td>
							<td><?php echo $transaction['responseDescription'];?></td>
							<td><?php echo $transaction['cardHolder'];?></td>
							<td><?php echo to_currency(make_currency_no_money($transaction['authorizedAmount']));?></td>
							<td><?php echo $transaction['transactionType'];?></td>
							<td><?php echo @$transaction['entryMethod'];?></td>
							<td><?php echo @$transaction['paymentType'];?></td>
							<td><?php echo @$transaction['maskedPan'];?></td>
							<td>
								<?php
								if ($transaction['transactionType'] == 'charge' && $transaction['approved'])
								{
							 		echo form_open('sales/void_return_by_transaction_id/'.$transaction['transactionId'], array('class' => 'form_void'));
									?>
									<input type="hidden" name="start_date" value="<?php echo $start_date; ?>">
									<input type="hidden" name="end_date" value="<?php echo $end_date; ?>">
									<input type="submit" name="submitf" value="<?php echo lang('common_void'); ?>" id="submit_delete" class="btn btn-danger">
									<?php echo form_close(); ?>
								<?php
								}
								?>
							</td>
							
						</tr>
					<?php
					}
					?>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>
<?php $this->load->view("partial/footer"); ?>



<script type="text/javascript">
date_time_picker_field_report($('#start_date'), JS_DATE_FORMAT);
date_time_picker_field_report($('#end_date'), JS_DATE_FORMAT);			
	
var datatable = $('#dTable').dataTable({
	"sPaginationType": "bootstrap",
	"bSort" : true,
	"aaSorting": [],//Disable initial sort
	"iDisplayLength": 50,
	"aLengthMenu": <?php echo json_encode($length_dropdown); ?>
});


$("#dTable").on("submit", ".form_void", function(e){ 
	var void_form = this;
	
	e.preventDefault();
	
	bootbox.dialog({ 
	    title:  <?php echo json_encode(lang('sales_void_title')); ?>,
	    message: <?php echo json_encode(lang('sales_confirm_void')); ?>,
	    size: 'large',
	    onEscape: true,
	    backdrop: true,
	    buttons: {
	        void_full: {
	            label: <?php echo json_encode(lang('sales_void_full_amount')); ?>,
	            className: 'btn-danger',
	            callback: function(){
					e.currentTarget.submit();
	            }
	        },
	        void_partial: {
	            label: <?php echo json_encode(lang('sales_void_partial_amount')); ?>,
	            className: 'btn-danger',
	            callback: function(){
					//Have to do a timeout so we can prompt again once closed
					setTimeout(function(){
						bootbox.prompt({
							title: <?php echo json_encode(lang('sales_please_enter_refund_amount')); ?>,
							inputType: 'text',
							value: '',
							callback: function(amount) {
								if (amount) {

									$('<input>').attr({
									    type: 'hidden',
									    name: 'amount',
										value: amount
									}).appendTo(void_form);

									e.currentTarget.submit();

								}
							}
						});						
					},150);
	            }
	        },
	        cancel: {
	            label: <?php echo json_encode(lang('common_cancel')); ?>,
	            className: 'btn-info'
	        }	   
		}
	})	
});

</script>
