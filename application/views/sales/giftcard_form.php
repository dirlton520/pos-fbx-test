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
				<?php echo form_open('giftcards/save_item/'.$item_id,array('id'=>'giftcard_form','class'=>'form-horizontal')); ?>

					<div class="control-group">
						<?php echo form_label(lang('common_giftcards_giftcard_number').':', 'description',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label wide')); ?>
						<div class="col-sm-9 col-md-9 col-lg-10">
							<?php echo form_input(array(
								'name'=>'description',
								'size'=>'8',
								'id'=>'description',
								'class'=>'form-control',
								)
							);?>
						</div>
					    <div style="clear:both;"></div>
					</div>
					<div style="clear:both;"></div>
					<div class="control-group">
						<?php echo form_label(lang('common_giftcards_card_value').':', 'unit_price',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label wide')); ?>
						<div class="col-sm-9 col-md-9 col-lg-10">
						<?php echo form_input(array(
							'name'=>'unit_price',
							'size'=>'8',
							'class'=>'form-control',
							'id'=>'unit_price')
						);?>
						</div>
					</div>
					<?php echo form_hidden('redirect', 1); ?>
					<?php echo form_hidden('sale_or_receiving', 'sale'); ?>
					<?php echo form_hidden('is_service', 1); ?>
					<?php echo form_hidden('sale', 1); ?>
					<?php echo form_hidden('item_number', lang('common_giftcard')); ?>
					<?php echo form_hidden('product_id', lang('common_giftcard')); ?>
					<?php echo form_hidden('name', lang('common_giftcard')); ?>
					<?php echo form_hidden('category', lang('common_giftcard')); ?>
					<?php echo form_hidden('size', '');?>
					<?php echo form_hidden('quantity', ''); ?>
					<?php echo form_hidden('allow_alt_description', '1'); ?>
					<?php echo form_hidden('is_serialized', '1'); ?>
					<?php echo form_hidden('override_default_tax', '1'); ?>
					<?php echo form_hidden('cost_price', 0); ?>
					<?php echo form_hidden('system_item', 1); ?>
					<?php echo form_hidden('disable_loyalty', $this->config->item('disable_gift_cards_sold_from_loyalty') ? 1 : 0); ?>
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
	<?php if (!$this->config->item('disable_giftcard_detection')) { ?>
	giftcard_swipe_field($('#description'));
	<?php
	}
	?>
    setTimeout(function(){$(":input:visible:first","#giftcard_form").focus();},100);
	var submitting = false;
	$('#giftcard_form').validate({
		submitHandler:function(form)
		{
			if (submitting) return;
			submitting = true;
			
			var ajaxSubmitData = {
				success:function(response)
				{
					$('#spin').addClass('hidden');
					submitting = false;
					show_feedback('success', response.message, <?php echo json_encode(lang('common_success')); ?>);

					if(response.redirect==1)
					{ 
						if (response.sale_or_receiving == 'sale')
						{
							$.post('<?php echo site_url("sales/add_giftcard");?>', {item: response.item_id+"|FORCE_ITEM_ID|"}, function()
							{
								window.location.href = '<?php echo site_url('sales/index/1'); ?>'
							});
						}
					}
				},
				dataType:'json'
			};
			$.getJSON(SITE_URL+'/sales/giftcard_exists_and_balance?giftcard_number='+encodeURIComponent($('#description').val()), function(giftcard_balance_lookup)
			{
				if (giftcard_balance_lookup.exists)
				{
					bootbox.confirm(<?php echo json_encode(lang('sales_giftcard_exists_do_you_want_to_add').' '.lang('sales_the_balance_of_card_is')); ?>+' '+giftcard_balance_lookup.value, function(response)
					{
						submitting = false;
						
						if (response)
						{
							$(form).ajaxSubmit(ajaxSubmitData);
						}
					});
				}
				else
				{
					submitting = false;
					$(form).ajaxSubmit(ajaxSubmitData);					
				}
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
			description:
			{
				required:true
			},
			unit_price:
			{
				required:true,
				number:true
			}
   		},
		messages:
		{
			description:
			{
				required:<?php echo json_encode(lang('common_giftcards_number_required')); ?>,
				remote:<?php echo json_encode(lang('common_giftcards_exists')); ?>
			},
			unit_price:
			{
				required:<?php echo json_encode(lang('common_giftcards_value_required')); ?>,
				number:<?php echo json_encode(lang('common_giftcards_value')); ?>
			}
		}
	});
});
</script>
<?php $this->load->view("partial/footer"); ?>