<?php $this->load->view("partial/header"); ?>
<div class="row">
	<div class="col-md-12">
			<div class="panel panel-piluku">
				<div class="panel-heading">
	                <h3 class="panel-title">
	                    <i class="ion-edit"></i> 
	                    <?php echo lang("common_giftcards_basic_information"); ?>
    					<small>(<?php echo lang('common_fields_required_message'); ?>)</small>
	                </h3>
		        </div>

			<div class="panel-body">
				<?php echo form_open('sales/'.(isset($refill) && $refill ? 'do_refill_integrated_giftcard' : 'add_integrated_giftcard'),array('id'=>'giftcard_form','class'=>'form-horizontal')); ?>
				
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
					<div class="control-group">
						<?php echo form_label(lang('common_giftcards_card_value').':', 'value',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label wide')); ?>
						<div class="col-sm-9 col-md-9 col-lg-10">
						<?php echo form_input(array(
							'name'=>'value',
							'size'=>'8',
							'class'=>'form-control',
							'id'=>'value')
						);?>
						</div>
					</div>
					<div class="clear"></div>
					<div class="form-actions pull-right">
					<?php
					echo form_submit(array(
						'name'=>'submit',
						'id'=>'submit',
						'value'=>lang('common_save'),
						'class'=>'btn btn-primary')
					);
					?>
					</div>
					<div class="clear"></div>
					<input type="hidden" name="integrated_auth_code" id ="integrated_auth_code" value="" />
					<input type="hidden" name="giftcard_number" id ="giftcard_number" value="" />
					
				<?php
				echo form_close();
				?>
			</div>
		</div>
	</div>
</div>
</div>
</div>
</div>
<script type='text/javascript'>

//validation and submit handling
$(document).ready(function()
{  
	setTimeout(function(){$("#value").focus();},100);
	$('#giftcard_form').validate({
		submitHandler:function(form)
		{
			show_feedback('warning', <?php echo json_encode(lang('common_process_giftcard_on_machine')); ?>, <?php echo json_encode(lang('common_waiting')); ?>);
			
			<?php if(isset($refill) && $refill) { ?>
			reload_integrated_giftcard(parseFloat($("#value").val()).toFixed(2),$("#manually_enter_card").prop('checked'),<?php echo json_encode(get_object_vars(get_giftcard_processor())); ?>,				
			<?php } else {?>
			issue_integrated_giftcard(parseFloat($("#value").val()).toFixed(2),$("#manually_enter_card").prop('checked'),<?php echo json_encode(get_object_vars(get_giftcard_processor())); ?>,				
			<?php } ?>
			function success(response)
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
				
				if (typeof processed_data.AuthCode !== 'undefined')
				{
					$("#integrated_auth_code").val(decodeURIComponent(processed_data.AuthCode.replace(/\+/g, '%20')));
				}
				
				if (processed_data.CmdStatus == 'Approved')
				{
					$(form).ajaxSubmit({
					success:function(fresponse)
					{
						$('#spin').addClass('hidden');
						show_feedback('success', fresponse.message, <?php echo json_encode(lang('common_success')); ?>);
						$.post('<?php echo site_url("sales/add");?>', {item: fresponse.item_id+"|FORCE_ITEM_ID|"}, function()
						{
							window.location.href = '<?php echo site_url('sales/index/1'); ?>'
						});
					},
					dataType:'json'
					});
				}
				else
				{
					show_feedback('error',decodeURIComponent(processed_data.TextResponse.replace(/\+/g, '%20')), <?php echo json_encode(lang('common_error')); ?>);
				}
			},function error()
			{
				
			});
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
			value:
			{
				required:true,
				number:true
			}
   	},
		messages:
		{
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