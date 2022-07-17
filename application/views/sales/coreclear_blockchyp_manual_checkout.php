<?php $this->load->view("partial/header"); ?>

<div class="row">
	<div class="col-md-12">
		<div class="panel panel-piluku">
			<div class="panel-body relative">
				<div class="spinner" id="grid-loader" style="display:none">
				  <div class="rect1"></div>
				  <div class="rect2"></div>
				  <div class="rect3"></div>
				</div>
				<h2><?php echo lang('common_amount').': '?> <span class="text-success"><?php echo $cc_amount ?></span></h2>
				<div id="coreclear_checkout">
					<?php echo form_open('sales/start_cc_processing_coreclear2/',array('id'=>'coreclear_checkout_form','class'=>'form-horizontal', 'autocomplete'=> 'off'));  ?>
						<div id="cc_info">
							<ul id="error_message_box" class="text-danger"></ul>
							
							<input type="text" id="cc_number" name = "cc_number" class="form-control" placeholder="<?php echo H(lang('sales_credit_card_no')); ?>">
							<input type="text" id="cc_exp_date" name="cc_exp_date" class="form-control" placeholder="<?php echo H(lang('sales_exp_date').'(MM/YYYY)'); ?>">

							<?php 
							echo form_button(array(
							'name' => 'cancel',
							'id' => 'cancel',
							'class' => 'submit_button btn btn-danger',
							'value' => 'true',
							'content' => lang('common_cancel')
							));


							echo form_submit(array(
								'name'=>'submitf',
								'id'=>'submitf',
								'value'=>lang('common_save'),
								'class'=>'submit_button btn btn-primary ')); ?>
						</div>
					<?php echo form_close();?>
				</div>
			</div>
		</div>
	</div>

	<div class="col-md-12 text-center cancel_process_btn_div m-t-10" style="display:none">
		<?php 
			echo form_button(array(
			'name' => 'cancel',
			'id' => 'cancel_process',
			'class' => 'submit_button btn btn-danger',
			'value' => 'true',
			'content' => lang('common_cancel')
			));
		?>					
	</div>	

<script type="text/javascript">
$(document).ready(function()
{
	var i = 0;
	$("#cancel").click(cancelCC);
	$("#cancel_process").click(cancel_process);
		
	$("#coreclear_checkout_form").submit(function()
	{

		$("#grid-loader").show();
		$(".cancel_process_btn_div").show();

		var $form = $('#coreclear_checkout_form');
		
		$form.get(0).submit();
		return false;
	});	
});

function cancelCC()
{
	bootbox.confirm(<?php echo json_encode(lang('sales_cc_are_you_sure_cancel')); ?>, function(result)
	{
		if (result)
		{
			window.location = <?php echo json_encode(site_url('sales/cancel_cc_processing')); ?>;
		}
	});
}

function cancel_process()
{
	window.location = <?php echo json_encode(site_url('sales/cancel_cc_processing')); ?>;
}

</script>
<?php $this->load->view("partial/footer"); ?>
