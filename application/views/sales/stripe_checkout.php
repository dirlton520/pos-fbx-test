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
				<div id="stripe_checkout">
					<?php echo form_open('sales/finish_cc_processing/',array('id'=>'stripe_checkout_form','class'=>'form-horizontal', 'autocomplete'=> 'off'));  ?>
						<ul id="error_message_box" class="text-danger"></ul>
						
							<input type="text" id="swipe" class="form-control" placeholder="<?php echo H(lang('sales_swipe_cc')); ?>">
							<input type="text" data-stripe="number" id="cc_number" class="form-control" placeholder="<?php echo H(lang('sales_credit_card_no')); ?>">
							<input data-stripe="name" type="text" id="cc_holder" class="form-control" placeholder="<?php echo H(lang('sales_credit_card_holder_name')); ?>">
							<input type="text" data-stripe="exp_month" id="cc_exp_month" class="form-control" placeholder="<?php echo H(lang('common_month')); ?>">
							<input type="text" data-stripe="exp_year" id="cc_exp_year" class="form-control" placeholder="<?php echo H(lang('common_year')); ?>">
							<input type="text" id="cc_cvv2" class="form-control" placeholder="CVV2">
							<input type="text" data-stripe="address_zip" id="zip" class="form-control" placeholder=<?php echo json_encode(lang('common_zip')); ?>>

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

<script type="text/javascript" src="https://js.stripe.com/v2/"></script>
<script src="<?php echo base_url().'assets/js/parse_cc_track.js'.'?'.ASSET_TIMESTAMP;?>" type="text/javascript" charset="UTF-8"></script>
<script type="text/javascript">
$(document).ready(function()
{
	$("#cancel").click(cancelCC);
	
	Stripe.setPublishableKey(<?php echo json_encode($this->Location->get_info_for_key('stripe_public')); ?>);
	
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
	
	$("#stripe_checkout_form").submit(function()
	{
		$("#grid-loader").show();
		$("#submitf").hide();
	
		//IF we have a ccv then have stripe process it..otherwise don't
		if ($("#cc_cvv2").val())
		{
			$("#cc_cvv2").attr('data-stripe','cvc');
		}
		else
		{
			$("#cc_cvv2").attr('data-stripe','');				
		}
	
		Stripe.card.createToken($("#stripe_checkout_form").get(0), function(status, response)
		{
		   var $form = $('#stripe_checkout_form');
			
		   if (response.error) 
			{
		     // Show the errors on the form
		     $('#error_message_box').html('<li>'+response.error.message+'</li>');
			  $('#error_message_box').show()
		  
	  			$("#grid-loader").hide();
	  			$("#submitf").show();
				return false;
		   } 
			else 
			{
		     // response contains id and card, which contains additional card details
		     var token = response.id;
		     // Insert the token into the form so it gets submitted to the server
		     $form.append($('<input type="hidden" name="stripeToken" />').val(token));
		     // and submit
		     $form.get(0).submit();
		   }
		});
		
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
