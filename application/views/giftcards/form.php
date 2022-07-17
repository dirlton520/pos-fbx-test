<?php $this->load->view("partial/header"); ?>
<div class="row">
	<div class="col-md-12">

				<?php echo form_open('giftcards/save/'.(!isset($is_clone) ? $giftcard_info->giftcard_id : ''),array('id'=>'giftcard_form','class'=>'form-horizontal')); ?>
			<div class="panel panel-piluku">
				<div class="panel-heading">
	                <h3 class="panel-title">
	                    <i class="ion-edit"></i> 
	                    <?php echo lang("common_giftcards_basic_information"); ?>
    					<small>(<?php echo lang('common_fields_required_message'); ?>)</small>
	                </h3>
		        </div>

			<div class="panel-body">
				
				<?php
					if ($this->Location->get_info_for_key('integrated_gift_cards')) {
				?>
				<div class="form-group">	
					<?php echo form_label(lang('common_integrated_gift_card').':', 'integrated_gift_card',array('class'=>'wide col-sm-3 col-md-3 col-lg-2 control-label wide')); ?>
					<div class="col-sm-9 col-md-9 col-lg-10">
						
					<?php 
					$checkbox_integrated = array(
						'name'=>'integrated_gift_card',
						'id'=>'integrated_gift_card',
						'class'=>'delete-checkbox',
						'value'=>1,
						'checked'=>($giftcard_info->integrated_gift_card ? 1 : 0));
						
					if ($giftcard_id != -1)
					{
						$checkbox_integrated['disabled'] = 'disabled';
						echo form_hidden('integrated_gift_card',1);
					}						
					echo form_checkbox($checkbox_integrated);?>
					<label for="integrated_gift_card"><span></span></label>
					

					</div>
				</div>
				<?php } ?>
				
				<div class="form-group" id="manually_enter_card_holder">	
					<?php echo form_label(lang('common_prompt_for_card').':', 'manually_enter_card',array('class'=>'wide col-sm-3 col-md-3 col-lg-2 control-label wide')); ?>
					<div class="col-sm-9 col-md-9 col-lg-10">
					<?php echo form_checkbox(array(
						'name'=>'manually_enter_card',
						'id'=>'manually_enter_card',
						'class'=>'delete-checkbox',
						'value'=>1,
					));?>
					<label for="manually_enter_card"><span></span></label>
					

					</div>
				</div>
					<div class="form-group" id="giftcard_number_holder">	
						<?php echo form_label(lang('common_giftcards_giftcard_number').':', 'giftcard_number',array('class'=>'required wide col-sm-3 col-md-3 col-lg-2 control-label required wide')); ?>
						<div class="col-sm-9 col-md-9 col-lg-10">
							<?php echo form_input(array(
								'name'=>'giftcard_number',
								'size'=>'8',
								'id'=>'giftcard_number',
								'class'=>'form-control form-inps',
								'value'=>$giftcard_info->giftcard_number)
								);?>
						</div>
					</div>


						<div class="form-group">	
						<?php echo form_label(lang('common_description').':', 'description',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label')); ?>
							<div class="col-sm-9 col-md-9 col-lg-10">
							<?php echo form_textarea(array(
								'name'=>'description',
								'id'=>'description',
								'class'=>'form-control text-area',
								'rows'=>'4',
								'cols'=>'30',
								'value'=>$giftcard_info->description));?>
							</div>
						</div>

				<?php if ($this->Employee->has_module_action_permission('giftcards','edit_giftcard_value', $this->Employee->get_logged_in_employee_info()->person_id)  || $giftcard_id == -1) { ?>

					<div class="form-group">	
						<?php echo form_label(lang('common_giftcards_card_value').':', 'value',array('class'=>'required wide col-sm-3 col-md-3 col-lg-2 control-label required wide')); ?>
						<div class="col-sm-9 col-md-9 col-lg-10">
							<?php echo form_input(array(
							'name'=>'value',
							'size'=>'8',
							'class'=>'form-control form-inps ',
							'id'=>'value',
							'value'=>$giftcard_info->value ? to_currency_no_money($giftcard_info->value, 10) : '')
							);?>
						</div>
					</div>
					
					<?php } else { ?>
						
						<div class="form-group">	
							<?php echo form_label(lang('common_giftcards_card_value').':', '',array('class'=>'required wide col-sm-3 col-md-3 col-lg-2 control-label required wide')); ?>
							<div class="col-sm-9 col-md-9 col-lg-10">
								<h5><?php echo $giftcard_info->value ? to_currency_no_money($giftcard_info->value, 10) : ''; ?></h5>
							</div>
						</div>
					
					<?php	
						echo form_hidden('value', $giftcard_info->value);
					}
					?> 
					<div class="form-group">	
						<?php echo form_label(lang('common_customer_name').':', 'choose_customer',array('class'=>'wide col-sm-3 col-md-3 col-lg-2 control-label wide')); ?>
						<div class="col-sm-9 col-md-9 col-lg-10">
                            
						<input type="text" name="choose_customer" id="choose_customer" class="form-control" value="<?php echo $giftcard_info->customer_id ? $selected_customer_name : ''; ?>">
						
						<input type="hidden" id="customer_id" name="customer_id" class="form-control" value="<?php echo $giftcard_info->customer_id ? $giftcard_info->customer_id : ''; ?>">

						</div>
					</div>
					
					<div class="form-group">	
						<?php echo form_label(lang('common_inactive').':', 'inactive',array('class'=>'wide col-sm-3 col-md-3 col-lg-2 control-label wide')); ?>
						<div class="col-sm-9 col-md-9 col-lg-10">
						<?php echo form_checkbox(array(
							'name'=>'inactive',
							'id'=>'inactive',
							'class'=>'delete-checkbox',
							'value'=>1,
							'checked'=>($giftcard_info->inactive ? 1 : 0)
						));?>
						<label for="inactive"><span></span></label>
						

						</div>
					</div>
					
					<?php if(!isset($is_clone)) { ?>
						
						<h5><?php echo lang('giftcards_log')?>:</h5>
						<div id="giftcard_log">
							<?php echo $giftcard_logs; ?>
						</div>
					<?php } ?>
						
					<?php echo form_hidden('redirect', $redirect); ?>
				
					<div class="form-actions pull-right">
						<?php echo form_submit(array(
						'name'=>'submit',
						'id'=>'submit',
						'value'=>lang('common_save'),
						'class'=>'btn submit_button floating-button btn-lg btn-primary')
						); ?>	
					</div>
					
					<?php if (!$giftcard_id != -1 && $giftcard_info->integrated_gift_card) { ?>
					<div class="form-actions pull-left">
						<?php echo form_input(array(
						'name'=>'delete',
						'type' => 'button',
						'id'=>'delete',
						'value'=>lang('common_delete'),
						'class'=>'btn delete_button btn-lg btn-danger')
						); ?>	
					</div>
					<?php } ?>
			</div>
		</div>
			<input type="hidden" name="integrated_auth_code" id ="integrated_auth_code" value="<?php echo $giftcard_info->integrated_auth_code; ?>" />
			<?php echo form_close(); ?>
	</div>
</div>
</div>

<script type='text/javascript'>
$("#delete").click(function()
{
	bootbox.confirm(<?php echo json_encode(lang('giftcards_confirm_delete')); ?>, function(response)
	{
		if (response)
		{
			void_issue_integrated_giftcard(parseFloat($("#value").val()).toFixed(2),$("#integrated_auth_code").val(),$("#manually_enter_card").prop('checked'),<?php echo json_encode(get_giftcard_processor() ? get_object_vars(get_giftcard_processor()) : FALSE); ?>,
			function success(response)
			{
				var data = response.split("&");
				var processed_data = {};

				for(var i = 0; i < data.length; i++)
				{
				    var m = data[i].split("=");
				    processed_data[m[0]] = m[1];
				}

				if (processed_data.CmdStatus == 'Approved')
				{
					show_feedback('success', <?php echo json_encode(lang('giftcards_successful_deleted')); ?>, <?php echo json_encode(lang('common_success')); ?>);
					$.post('<?php echo site_url('giftcards/delete'); ?>', {ids: [<?php echo $giftcard_id; ?>]}, function()
					{
						window.location.href = '<?php echo site_url('giftcards'); ?>';
					});
				}
				else
				{
					show_feedback('error',decodeURIComponent(processed_data.TextResponse.replace(/\+/g, '%20')), <?php echo json_encode(lang('common_error')); ?>);
				}
			},
			function error()
			{
				
			});
		}
	});
});
function check_integrated_giftcard()
{
	if ($("#integrated_gift_card").prop('checked'))
	{
		$("#giftcard_number_holder").hide();
		$("#manually_enter_card_holder").show();
	}
	else
	{
		$("#giftcard_number_holder").show();
		$("#manually_enter_card_holder").hide();
	}
}	

function processAddGiftcard(form)
{
	$(form).ajaxSubmit({
	success:function(response)
	{
		$('#grid-loader').hide();
		show_feedback(response.success ? 'success' : 'error',response.message, response.success ? <?php echo json_encode(lang('common_success')); ?>  : <?php echo json_encode(lang('common_error')); ?>);
		if(response.redirect==2 && response.success)
		{
			window.location.href = '<?php echo site_url('giftcards'); ?>';
		}
		else
		{
			$("html, body").animate({ scrollTop: 0 }, "slow");
			$(".form-group").removeClass('has-success has-error');
		}
	},
	<?php if(!$giftcard_info->giftcard_id) { ?>
	resetForm:true,
	<?php } ?>
	dataType:'json'
	});
}

function integrated_giftcard_success(response)
{
	var data = response.split("&");
	var processed_data = {};

	for(var i = 0; i < data.length; i++)
	{
	    var m = data[i].split("=");
	    processed_data[m[0]] = m[1];
	}
	
	if (typeof processed_data.AcctNo !== 'undefined')
	{
		$("#giftcard_number").val(decodeURIComponent(processed_data.AcctNo.replace(/\+/g, '%20')));
	}
	
	//Only save for new gift cards
	if(<?php echo $giftcard_id; ?> == -1 && typeof processed_data.AuthCode !== 'undefined')
	{
		$("#integrated_auth_code").val(decodeURIComponent(processed_data.AuthCode.replace(/\+/g, '%20')));
	}
	
	if (processed_data.Balance)
	{
		$("#value").val(parseFloat(decodeURIComponent(processed_data.Balance.replace(/\+/g, '%20'))).toFixed(2))
	}
	
	if (processed_data.CmdStatus == 'Approved')
	{
		processAddGiftcard(giftcard_form);
	}
	else
	{
		show_feedback('error',decodeURIComponent(processed_data.TextResponse.replace(/\+/g, '%20')), <?php echo json_encode(lang('common_error')); ?>);
	}
}

function integrated_giftcard_error()
{

}

$("#integrated_gift_card").click(check_integrated_giftcard);
<?php if (!$this->config->item('disable_giftcard_detection')) { ?>
	giftcard_swipe_field($('#giftcard_number'));
<?php
}
?>			
	//validation and submit handling
	$(document).ready(function()
	{
			check_integrated_giftcard();
			$( "#choose_customer" ).autocomplete({
		 		source: '<?php echo site_url("giftcards/suggest_customer");?>',
				delay: 500,
		 		autoFocus: false,
		 		minLength: 0,
		 		select: function( event, ui ) 
		 		{
					event.preventDefault();
					$("#choose_customer").val(decodeHtml(ui.item.label));
					$("#customer_id").val(decodeHtml(ui.item.value));
		 		}
			}).data("ui-autocomplete")._renderItem = function (ul, item) {
		         return $("<li class='customer-badge suggestions'></li>")
		             .data("item.autocomplete", item)
			           .append('<a class="suggest-item"><div class="avatar">' +
									'<img src="' + item.avatar + '" alt="">' +
								'</div>' +
								'<div class="details">' +
									'<div class="name">' + 
										item.label +
									'</div>' + 
									'<span class="email">' +
										item.subtitle + 
									'</span>' +
								'</div></a>')
		             .appendTo(ul);
		     };
	     
	    setTimeout(function(){$(":input:visible:first","#giftcard_form").focus();},100);
			$('#giftcard_form').validate({
			submitHandler:function(form)
			{
				giftcard_form = form;
        if(!$("#choose_customer").val())
        {
						$("#customer_id").val("");
        }
				
				$('#grid-loader').show();
				
				if($("#integrated_gift_card").prop('checked'))
				{
						//new gift card
						if (<?php echo $giftcard_id; ?> == -1)
						{
							show_feedback('warning', <?php echo json_encode(lang('common_process_giftcard_on_machine')); ?>, <?php echo json_encode(lang('common_waiting')); ?>);
							issue_integrated_giftcard(parseFloat($("#value").val()).toFixed(2),$("#manually_enter_card").prop('checked'),<?php echo json_encode(get_giftcard_processor() ? get_object_vars(get_giftcard_processor()) : FALSE); ?>,integrated_giftcard_success,integrated_giftcard_error); 
						}
						else //Existing gift card
						{
							show_feedback('warning', <?php echo json_encode(lang('common_process_giftcard_on_machine')); ?>, <?php echo json_encode(lang('common_waiting')); ?>);
							var new_amount = parseFloat($("#value").val()).toFixed(2);
							var amount_difference = new_amount - <?php echo $giftcard_info->value ? $giftcard_info->value : 0; ?>;
							
							if (amount_difference > 0)
							{
								reload_integrated_giftcard(parseFloat(amount_difference).toFixed(2),$("#manually_enter_card").prop('checked'),<?php echo json_encode(get_giftcard_processor() ? get_object_vars(get_giftcard_processor()) : FALSE); ?>,integrated_giftcard_success,integrated_giftcard_error); 
							}
							else if(amount_difference < 0 )
							{
								sale_integrated_giftcard(Math.abs(parseFloat(amount_difference)).toFixed(2),$("#manually_enter_card").prop('checked'),<?php echo json_encode(get_giftcard_processor() ? get_object_vars(get_giftcard_processor()) : FALSE); ?>,integrated_giftcard_success,integrated_giftcard_error); 
							}
							else
							{
								processAddGiftcard(form);
							}
						}
				}
				else
				{
					processAddGiftcard(form);
				}
				
			},
			errorClass: "text-danger",
			errorElement: "span",
			highlight:function(element, errorClass, validClass) {
				$(element).parents('.form-group').removeClass('has-success').addClass('has-error');
			},
			unhighlight: function(element, errorClass, validClass) {
				$(element).parents('.form-group').removeClass('has-error').addClass('has-success');
			},
			rules:
			{
				giftcard_number:
				{
					<?php if(!$giftcard_info->giftcard_id) { ?>
					remote: 
					    { 
						url: "<?php echo site_url('giftcards/giftcard_exists');?>", 
						type: "post"
		
					    }, 
					<?php } ?>
					required:true
	
				},
				value:
				{
					required:true,
					number:true
				}
	   		},
			messages:
			{
				giftcard_number:
				{
					<?php if(!$giftcard_info->giftcard_id) { ?>
					remote:<?php echo json_encode(lang('common_giftcards_exists')); ?>,
					<?php } ?>
					required:<?php echo json_encode(lang('common_giftcards_number_required')); ?>,

				},
				value:
				{
					required:<?php echo json_encode(lang('common_giftcards_value_required')); ?>,
					number:<?php echo json_encode(lang('common_giftcards_value')); ?>
				}
			}
		});
	});
</script>
<?php $this->load->view("partial/footer"); ?>