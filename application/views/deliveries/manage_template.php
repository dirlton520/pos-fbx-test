<?php $this->load->view("partial/header"); ?>

<?php echo form_open('deliveries/save_template/',array('id'=>'template_form','class'=>'form-horizontal')); ?>
<div class="row <?php echo $redirect ? 'manage-table' :''; ?>">


	<div class="col-md-8 form-horizontal">
		<div class="panel panel-piluku">
			<div class="panel-heading">
				<?php echo lang('deliveries_manage_email_template');?>
				<p class="pull-right btn btn-primary preview_enable"><?php echo lang('common_preview')?></p>
				<p class="pull-right btn btn-primary preview_disable hide"><?php echo lang('common_edit')?></p>
			</div>
			<div class="panel-body">
				<div id="statuses_list" class="status-tree">
					<textarea name="email_template" cols="17" rows="7" id="template" class="form-control text-area" spellcheck="false"></textarea>
					<span class="preview-text-area hide" id="preview"></span>
				</div>
			</div>
		</div>
	</div>

	<div class="col-md-4 form-horizontal">
		<div class="panel panel-piluku">
			<div class="panel-heading">
				<?php echo lang('common_status');?>
				<small>(<?php echo lang('common_required');?>)</small>
			</div>
			<div class="panel-body">
				<select name="status_id" id="status_id" class="form-control change_delivery_status">
					<option value=""><?php echo lang('common_please_select');?></option>
					<option value="0" data-status_value="<?php echo $default;?>"><?php echo lang('common_default');?></option>
					<?php   
						$statuses = array('' => lang('common_change_status'));
						foreach($delivery_statuses as $status_id => $status) { ?>
							<option value="<?php echo $status_id;?>" data-status_value="<?php echo str_ireplace('<br />', "\r\n", $status['data']);?>">
								<?php echo $status['name'];?>
							</option>
					<?php } ?>
				</select>
			</div>
		</div>
		<div class="panel panel-piluku">
			<div class="panel-heading"><?php echo lang('common_shortcode');?></div>
			<div class="panel-body shortcuts">
				<a href="javascript:void(0);" class="add_status" data-value="%company_name%">
					<?php echo lang('common_company');?>
				</a> <br>
				<a href="javascript:void(0);" class="add_status" data-value="%sale_id%">
					<?php echo lang('common_sale_id');?>
				</a> <br>
				<a href="javascript:void(0);" class="add_status" data-value="%tracking_number%">
					<?php echo lang('deliveries_tracking_number');?>
				</a> <br>
				<a href="javascript:void(0);" class="add_status" data-value="%ecommerce_id%">
					<?php echo lang('common_ecommerce_id');?>
				</a><br>
				<a href="javascript:void(0);" class="add_status" data-value="%delivery_id%">
					<?php echo lang('common_delivery_id');?>
				</a><br>
				<a href="javascript:void(0);" class="add_status" data-value="%delivery_status%">
					<?php echo lang('common_status');?>
				</a><br>
				<a href="javascript:void(0);" class="add_status" data-value="%estimated_shipping_date%">
					<?php echo lang('deliveries_estimated_shipping_date');?>
				</a><br>
				<a href="javascript:void(0);" class="add_status" data-value="%actual_shipping_date%">
					<?php echo lang('deliveries_actual_shipping_date');?>
				</a><br>
				<a href="javascript:void(0);" class="add_status" data-value="%estimated_delivery_or_pickup_date%">
					<?php echo lang('deliveries_estimated_delivery_or_pickup_date');?>
				</a><br>
				<a href="javascript:void(0);" class="add_status" data-value="%actual_delivery_or_pickup_date%">
					<?php echo lang('deliveries_actual_delivery_or_pickup_date');?>
				</a><br>
			</div>
		</div>
	</div>
</div><!-- /row -->
<div class="form-actions">
	<?php
		echo form_submit(array(
			'name'	=>	'submitf',
			'id'	=>	'submitf',
			'value'	=>	lang('common_save'),
			'class'	=>	'submit_button floating-button btn btn-primary'
		));
	?>
</div>

<script type='text/javascript'>	

$(document).ready(function()
{
    


    $("#template_form").submit(function(event)
		{
			event.preventDefault();
			$(this).ajaxSubmit({ 
				success: function(response, statusText, xhr, $form){
					show_feedback(response.success ? 'success' : 'error', response.message, response.success ? <?php echo json_encode(lang('common_success')); ?> : <?php echo json_encode(lang('common_error')); ?>);
					if(response.success)
					{
						
					}		
				},
				dataType:'json',
			});
		});
 
 	// On Shortcuts Click Get Value and Replace with Text
	$('.shortcuts a').click(function() { 

		

        val 		= $(this).data('value');
        var text 	= $('#template').val();
		$('#template').insertAtCursor(val);
    }); 

	// On Change Status ID Get data-status_value and Replace with Textarea
	$("#status_id").change(function() {
		var option_value 		 	= $("#status_id :selected").val();
        var option_status_value 	= $(this).children("option:selected").data('status_value');
        var option_name 			= $(this).children("option:selected").text();
        $('.text-area').val(option_status_value);
       	
        
        if (option_value >= '0') {
        	if (option_status_value) {
        		var text_content = $.trim($(".text-area").val());
        		$.trim($(".text-area").val(text_content));
        	} else {
        		renderTemplate(option_name);
        	}
    	}
    });

	// On Preview Button Click Hide Text Editor, Hide Preview Button & Show Edit Button
	$(".preview_enable").click(function(){
	    $("#template").toggleClass("hide");
	    $("#preview").toggleClass("show");
	    $(".preview_disable").removeClass("hide");
	    $(".preview_enable").addClass("hide");

	    renderTemplate();
	});

	// On Edit Button Click Hide Preview Editor, Hide Edit Button & Show Preview Button
	$(".preview_disable").click(function() {
		$("#template").toggleClass("hide");
	    $("#preview").toggleClass("show");
	    $(".preview_enable").removeClass("hide");
	    $(".preview_disable").addClass("hide");

	    unrenderTemplate();
	});


	// Render Template 
	function renderTemplate(status_value)
	{
		var text_content = $.trim($(".text-area").val());

		if (!text_content) {
			pre_template 	= "Your Order # %sale_id% status is";
			preview 		= pre_template+ ' ' +$.trim(status_value);
	
		} else {
			preview = text_content.replaceAll(/\n/g, "<br />")
					.replaceAll('%company_name%', '<?php echo $this->config->item('company');?>')
					.replaceAll('%sale_id%', "<?php echo $this->config->item('sale_prefix')?> 125")
					.replaceAll('%tracking_number%', "5263")
					.replaceAll('%ecommerce_id%', "3410")
					.replaceAll('%delivery_id%', "01")
					.replaceAll('%delivery_status%', 'Delivery Status')
					.replaceAll('%estimated_shipping_date%', "<?php echo lang('deliveries_estimated_shipping_date');?>")
					.replaceAll('%actual_shipping_date%', "<?php echo lang('deliveries_actual_shipping_date');?>")
					.replaceAll('%estimated_delivery_or_pickup_date%', "<?php echo lang('deliveries_estimated_delivery_or_pickup_date');?>")
					.replaceAll('%actual_delivery_or_pickup_date%', "<?php echo lang('deliveries_actual_delivery_or_pickup_date');?>"); 
		}
		$.trim($(".text-area").val(preview));
		$('.preview-text-area').html(preview);
	}

	// Render Template 
	function unrenderTemplate()
	{

		var text_content = $.trim($(".text-area").val());

		preview = text_content.replaceAll(/\<br\>/g, "\n").replaceAll(/\<br \/\>/g, "\n")
					.replaceAll('<?php echo $this->config->item('company');?>', '%company_name%')
					.replaceAll("<?php echo $this->config->item('sale_prefix')?> 125",'%sale_id%')
					.replaceAll("5263", '%tracking_number%')
					.replaceAll("3410", '%ecommerce_id%')
					.replaceAll("01", '%delivery_id%')
					.replaceAll('Delivery Status', '%delivery_status%')
					.replaceAll("<?php echo lang('deliveries_estimated_shipping_date');?>", '%estimated_shipping_date%')
					.replaceAll("<?php echo lang('deliveries_actual_shipping_date');?>", '%actual_shipping_date%')
					.replaceAll("<?php echo lang('deliveries_estimated_delivery_or_pickup_date');?>", '%estimated_delivery_or_pickup_date%')
					.replaceAll("<?php echo lang('deliveries_actual_delivery_or_pickup_date');?>", '%actual_delivery_or_pickup_date%'); 
		$.trim($(".text-area").val(preview));		
	}

	$.fn.extend({
	    insertAtCursor: function(option_value) {
	        this.each(function() {
	            if (document.selection) {
	                this.focus();
	                var sel = document.selection.createRange();
	                sel.text = option_value;
	                this.focus();
	            } else if (this.selectionStart || this.selectionStart == '0') {
	                var startPos = this.selectionStart;
	                var endPos = this.selectionEnd;
	                var scrollTop = this.scrollTop;
	                this.value = this.value.substring(0, startPos) +
	                    option_value + this.value.substring(endPos, this.value.length);
	                this.focus();
	                this.selectionStart = startPos + option_value.length;
	                this.selectionEnd = startPos + option_value.length;
	                this.scrollTop = scrollTop;
	            } else {
	                this.value += option_value;
	                this.focus();
	            }
	        });
	        return this;
	    }
	});
});
</script>
<?php $this->load->view('partial/footer'); ?>
