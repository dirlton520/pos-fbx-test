<?php $this->load->view("partial/header"); 
$tracking_cash = false;

$track_payment_types =  $this->config->item('track_payment_types') ? unserialize($this->config->item('track_payment_types')) : array();

if ($this->config->item('track_payment_types') && !empty($track_payment_types))
{
	$payment_types = unserialize($this->config->item('track_payment_types'));
	$tracking_cash = in_array('common_cash',$payment_types);
}
?>
<style scoped>
	a
	{
		text-decoration: none !important;
	}
</style>

<?php echo form_open('sales', array('id'=>'opening_amount_form')); ?>

<div class="row">
	<div class="col-md-12">
		<div class="panel panel-piluku">
			<div class="panel-heading">
				<?php echo lang('sales_opening_amount_desc'); ?>
			</div>
			<div class="panel-body">
				
				<?php
				if($tracking_cash)
				{
				?>
				<div class="col-md-4">
					<div class="table-responsive">
						<table class="table table-striped table-hover text-center opening_bal">
							<tr>
								<th><?php echo lang('common_denomination');?></th>
								<th><?php echo lang('common_count');?></th>
							</tr>

							<?php foreach($denominations as $denomination) { ?>
							<tr>
								<td><?php echo $denomination['name']; ?></td>
								<td>
									<div class="form-group">
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
				
				<div class="col-md-8">
					<?php
					$reg_info = $this->Register->get_info($this->Employee->get_logged_in_employee_current_register_id());
					$reg_name =  '&nbsp;<span class="badge bg-primary">'.$reg_info->name.'&nbsp;(<small>'.lang('sales_change_register').'</small>)</span>';
					?>

						
						
					<?php foreach(unserialize($this->config->item('track_payment_types')) as $payment_type_track) { ?>
					<div class="form-group clearfix">
						
						<div class="from-group text-center">
							<?php echo lang('sales_previous_closing_amount');?>: <?php echo to_currency(isset($previous_closings[$payment_type_track]) ? $previous_closings[$payment_type_track] : 0);?>
						</div>
                        <?php echo form_label((strpos($payment_type_track, 'common_') !== FALSE ? lang($payment_type_track) : $payment_type_track). ' '.lang('common_opening_amount').':', 'opening_amount',array('class'=>'control-label col-md-12')); ?>
                        <div class="col-md-12 text-center">
                            <div class="input-group col-md-12 text-center">
                                <?php echo form_input(array(
								'name'=>'opening_amount['.$payment_type_track.']',
								'class'=>'form-control opening_amount',
								'value'=>'',
								'required' => '')
								);?>
                            </div>
                            <!-- /input-group -->
                        </div>
                    </div>
										<hr />
							<?php } ?>

								<span class="input-group-btn bg">
                                    <?php echo form_submit(array(
										'name'=>'submit',
										'id'=>'submit',
										'value'=>lang('common_save'),
										'class'=>'btn btn-primary')
									);
									?>
                                </span>

					<div class="from-group text-center">
						<h3><?php echo lang('common_or'); ?></h3>					
						<?php echo lang('common_register_name');?>: <?php echo anchor('sales/clear_register', $reg_name);?>
					</div>
					<br />
					<div class="from-group text-right">
						<?php echo anchor_popup(site_url('sales/open_drawer'), '<i class="ion-android-open"></i> '.lang('common_pop_open_cash_drawer'),array('class'=>'', 'target' => '_blank')); ?>
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
	$(document).ready(function()
	{
		$(".opening_amount").eq(0).focus();
		
		jQuery.extend(jQuery.validator.messages, {
		    required: <?php echo json_encode(lang('sales_amount_required')); ?>
		});
		
		$('#opening_amount_form').validate();
		
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
			
			$(".opening_amount").eq(0).val(parseFloat(Math.round(total * 100) / 100).toFixed(<?php echo $this->config->item('number_of_decimals') !== NULL && $this->config->item('number_of_decimals') != '' ? (int)$this->config->item('number_of_decimals') : 2; ?>));
		}
		
		$(".denomination").change(calculate_total);
		$(".denomination").keyup(calculate_total);

	});
</script>
<?php $this->load->view('partial/footer.php'); ?>