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
					<div id="braintree_checkout">

						<?php echo form_open('sales/finish_cc_processing/',array('id'=>'braintree_checkout_form','class'=>'form-horizontal', 'autocomplete'=> 'off'));  ?>
							<input type="text" id="swipe" class="form-control" placeholder="<?php echo H(lang('sales_swipe_cc')); ?>">
							<input type="text" data-braintree-name="number" id="cc_number" class="form-control" placeholder="<?php echo H(lang('sales_credit_card_no')); ?>">
							<input data-braintree-name="cardholder_name" type="text" id="cc_holder" class="form-control" placeholder="<?php echo H(lang('sales_credit_card_holder_name')); ?>">
							<input type="text" data-braintree-name="expiration_month" id="cc_exp_month" class="form-control" placeholder="<?php echo H(lang('common_month')); ?>">
							<input type="text" data-braintree-name="expiration_year" id="cc_exp_year" class="form-control" placeholder="<?php echo H(lang('common_year')); ?>">
							<input type="text" data-braintree-name="cvv" id="cc_cvv2" class="form-control" placeholder="CVV2">
							<input type="text" data-braintree-name="postal_code" id="zip" class="form-control" placeholder=<?php echo json_encode(lang('common_zip')); ?>>

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


<script src="<?php echo base_url().'assets/js/braintree.js'.'?'.ASSET_TIMESTAMP;?>" type="text/javascript" charset="UTF-8"></script>
<script src="<?php echo base_url().'assets/js/parse_cc_track.js'.'?'.ASSET_TIMESTAMP;?>" type="text/javascript" charset="UTF-8"></script>
<script type="text/javascript">
$(document).ready(function()
{
	$("#cancel").click(cancelCC);
	
	braintree.setup(<?php echo json_encode($braintree_clent_token); ?> , "custom", {id: "braintree_checkout_form"});
	
	$("#swipe").focus();
	
	$("#swipe").keypress(function(e)
	{
		var TrackData=$(this).val() ? $(this).val(): '';
		if(TrackData!='')
		{
			if(e.keyCode==13)
			{				
				e.preventDefault();
				parseSwipe(TrackData);
			}
		}
	});

	$("#braintree_checkout_form").submit(function()
	{
		$("#grid-loader").show();
		$("#submitf").hide();
		
		//IF we have a zip then have braintree process it..otherwise don't
		if (!$("#zip").val())
		{
			$("#zip").remove();
		}
		
		
		return true;
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

function parseSwipe(TrackData)
{
	var p=new SwipeParserObj(TrackData);
	if(p.account)
	{
		$('#cc_number').val(p.account);
		$('#cc_holder').val(p.account_name)
		$('#cc_exp_month').val(p.exp_month);
		$('#cc_exp_year').val(p.exp_year);
		$('#cc_cvv2').val('');
		$("#swipe").val('');
		
		<?php if ($this->config->item('prompt_for_ccv_swipe')) { ?>
			$("#cc_cvv2").focus();
			<?php } else { ?>
			$("#submitf").click();
		<?php } ?>
	}
	else
	{
		$("#swipe").val('');
		bootbox.alert(<?php echo json_encode(lang("sales_invalid_swipe")); ?>);
	}
}

</script>
<?php $this->load->view("partial/footer"); ?>
