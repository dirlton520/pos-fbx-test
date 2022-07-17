<?php $this->load->view("partial/header"); 
$tracking_cash = false;
$track_payment_types =  $this->config->item('track_payment_types') ? unserialize($this->config->item('track_payment_types')) : array();

if ($this->config->item('track_payment_types') && !empty($track_payment_types))
{
	$payment_types = unserialize($this->config->item('track_payment_types'));
	$tracking_cash = in_array('common_cash',$payment_types);
}

?>
<?php
if(isset($update))
{
echo form_open('sales/edit_register/'.$register_log_id . $continue, array('id'=>'closing_amount_form','class'=>'form-horizontal'));
}
else
{
	echo form_open('sales/closeregister' . $continue, array('id'=>'closing_amount_form','class'=>'form-horizontal'));	
}
?>

<div class="row">
	<div class="col-md-12">
		<div class="panel panel-piluku">
			<div class="panel-heading">
				<?php echo lang('sales_closing_amount_desc'); ?>
			</div>
			<div class="panel-body">
				
					<?php
					if($tracking_cash)
					{
					?>
					
					<div class="col-md-6">
						<div class="table-responsive">
							<table class="table table-striped text-center opening_bal">
							<tr>
								<th><?php echo lang('common_denomination');?></th>
								<th><?php echo lang('common_count');?></th>
							</tr>
							<?php foreach($denominations as $denomination) { ?>
								<tr>
									<td><?php echo $denomination['name']; ?></td>
									<td>
										<div class="form-group table-form-group">
											<?php echo form_input(array(
												'name'=>'denoms['.$denomination['id'].']',
												'id'=>'denom_'.$denomination['id'],
												'data-value' => $denomination['value'],
												'value' => isset($denoms[$denomination['id']]) && $denoms[$denomination['id']] ? $denoms[$denomination['id']] : '',
												'class'=> 'form-control denomination',
												)
											);?>
										</div>
									</td>
								</tr>
								
							<?php } ?>

						</table>
						</div>
					</div>
					
					<?php } ?>
					
					<div class="col-md-6">
						
						<ul class="text-error" id="error_message_box"></ul>
						
							<h3 class="text-right"><?php echo anchor("reports/register_log_details/$register_log_id", lang('common_det'), array('target' => '_blank')); ?></h3>

							
					
						<div class="col-md-12 form">
							<?php foreach(unserialize($this->config->item('track_payment_types')) as $payment_type_track) { ?>

							<?php if (isset($closeout_amounts[$payment_type_track])) {?>
							 
							 <?php if (!$this->config->item('do_not_show_closing')) { ?>
							<ul class="list-group close-amount">
							  <li class="list-group-item"><?php echo (strpos($payment_type_track,'common_') !== FALSE ? lang($payment_type_track) : $payment_type_track).' '.lang('common_open_amount'); ?>:  <span class="pull-right"><?php echo to_currency($open_amounts[$payment_type_track]); ?></span></li>
							  <li class="list-group-item"><?php echo (strpos($payment_type_track,'common_') !== FALSE ? lang($payment_type_track) : $payment_type_track).' '.lang('common_sales'); ?>:  <span class="pull-right"><?php echo to_currency($payment_sales[$payment_type_track]); ?></span></li>
							  <li class="list-group-item"><?php echo (strpos($payment_type_track,'common_') !== FALSE  ? lang($payment_type_track) : $payment_type_track).' '.lang('common_total_additions'); ?> 
									<?php if(!isset($update)) { ?>
										[<?php echo anchor('sales/register_add_subtract/add/'.$payment_type_track.'/closeregister', lang('common_edit')); ?>]:  <span class="pull-right"><?php echo to_currency($total_payment_additions[$payment_type_track]); ?> </span></li>
							  	<?php } ?>
								<li class="list-group-item"><?php echo (strpos($payment_type_track,'common_') !== FALSE ? lang($payment_type_track) : $payment_type_track).' '.lang('common_total_subtractions'); ?> 
									<?php if(!isset($update)) { ?>
										[<?php echo anchor('sales/register_add_subtract/subtract/'.$payment_type_track.'/closeregister', lang('common_edit')); ?>]:  <span class="pull-right"><?php echo to_currency($total_payment_subtractions[$payment_type_track]); ?> </span></li>
						 	 		 <?php } ?>
							 	<?php
								if ($payment_type_track == 'common_cash' && $this->config->item('amount_of_cash_to_be_left_in_drawer_at_closing'))
								{
									?>
							  <li class="list-group-item"><?php echo lang('sales_amount_of_cash_to_desposit_in_bank'); ?>:  <span class="pull-right"><?php echo to_currency($closeout_amounts[$payment_type_track]-$this->config->item('amount_of_cash_to_be_left_in_drawer_at_closing')); ?> </span></li>
									<?php
								}
							 	?>
							  	<li class="list-group-item active"><?php echo sprintf(lang('sales_closing_amount_approx'), ''); ?> <span class="pull-right text-success total-amount"><?php echo to_currency($closeout_amounts[$payment_type_track]); ?></span></li>
							</ul>
							<?php } ?>
						
											<?php if(isset($open_amount_editable)) { ?>
												<div class="form-group controll-croups1">
												<?php echo form_label( (strpos($payment_type_track,'common_') !== FALSE ? lang($payment_type_track) : $payment_type_track).' '.lang('common_opening_amount').':', 'opening_amount',array('class'=>'control-label')); ?>
												<?php echo form_input(array(
												'name'=>'opening_amount['.$payment_type_track.']',
													'class'=>'form-control',
													'value'=>$closeout_amounts[$payment_type_track] ? to_currency_no_money($open_amounts[$payment_type_track]): '')
													);?>
												</div>
										 <?php } ?>

											<div class="form-group controll-croups1">
											<?php echo form_label((strpos($payment_type_track,'common_') !== FALSE ? lang($payment_type_track) : $payment_type_track).' '.lang('common_closing_amount').':', 'closing_amount',array('class'=>'control-label')); ?>
											<?php echo form_input(array(
											'name'=>'closing_amount['.$payment_type_track.']',
												'class'=>'form-control closing_amount',
												'value'=>!$this->config->item('do_not_show_closing') && isset($closeout_amounts[$payment_type_track]) ? to_currency_no_money($closeout_amounts[$payment_type_track]): '')
												);?>
											</div>
											<?php } ?>
											<hr />
											<?php } /*endforeach*/?>
											
											<div class="form-group controll-croups1">
											<?php echo form_label(lang('sales_notes').':', 'notes',array('class'=>'control-label')); ?>
											<?php echo form_textarea(array(
												'name'=>'notes',
												'id'=>'notes',
												'class'=>'form-control text-area',
												'value'=>$notes ? $notes: '')
												);?>
											</div>
											
											<div class="from-group text-right">
												<?php echo anchor_popup(site_url('sales/open_drawer'), '<i class="ion-android-open"></i> '.lang('common_pop_open_cash_drawer'),array('class'=>'', 'target' => '_blank')); ?>
											</div>
											
											<br />
											
											<div class="form-group form-actions1">
												<input type="button" id="close_submit" class="btn btn-primary" value="<?php echo lang('common_submit'); ?>">
											</div>
											
											<?php if(!isset($update)) {  ?>
											<div style="text-align: center;">
												<h3><?php echo lang('common_or'); ?></h3>					
												<input type="button" id="logout_without_closing" class="btn btn-danger" value="<?php echo lang('sales_logout_without_closing_register'); ?>">
											</div>
											<?php }  ?>
										</div>
						</div>
					

			</div>
		</div>
	</div>
</div>
<?php
	echo form_close();
?>
<script type='text/javascript'>

//validation and submit handling
$(document).ready(function(e)
{
	$(".closing_amount").eq(0).focus();
	
	$("input").keypress(function (e) {
	    if (e.keyCode == 13) {
	    	e.preventDefault();
	       	confirm_submit();
	    }
	 });

	$('#close_submit').click(function(){
		confirm_submit();
	});
	var submitting = false;

	$(".closing_amount").eq(0).focus();
	
	jQuery.extend(jQuery.validator.messages, {
	    required: <?php echo json_encode(lang('sales_amount_required')); ?>
	});
	
	$('#closing_amount_form').validate();
	
	
	$("#logout_without_closing").click(function()
	{
		window.location = '<?php echo site_url('home/logout'); ?>';
	});
	
	function calculate_total()
	{
		var total = 0;
		
		$(".denomination").each(function( index ) 
		{
			if ($(this).val())
			{
				total+= $(this).data('value') * $(this).val();
			}
		});
		
		$(".closing_amount").eq(0).val(parseFloat(Math.round(total * 100) / 100).toFixed(<?php echo $this->config->item('number_of_decimals') !== NULL && $this->config->item('number_of_decimals') != '' ? (int)$this->config->item('number_of_decimals') : 2; ?>));
	}
	
	$(".denomination").change(calculate_total);
	$(".denomination").keyup(calculate_total);
});

//TODO make work for all payment types
function confirm_submit()
{
		bootbox.confirm(<?php echo json_encode(lang('sales_confirm_closing')); ?>,function(result)
		{
			if (result)
			{
				$('#closing_amount_form').submit();			
			}
		});			
}
</script>
<?php $this->load->view('partial/footer.php'); ?>