<div class="modal-dialog">
	<div class="modal-content">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-label=<?php echo json_encode(lang('common_close')); ?>><span aria-hidden="true" class="ti-close"></span></button>
			<h4 class="modal-title"><?php echo lang("common_messages_basic_info"); ?></h4>
		</div>
		<div class="modal-body">	
			<?php echo form_open_multipart('messages/save_message/',array('id'=>'send_message_form','class'=>'form-horizontal')); 	?>
			
			<div class="spinner" id="grid-loader" style="display:none">
			  <div class="rect1"></div>
			  <div class="rect2"></div>
			  <div class="rect3"></div>
			</div>
					<div class="form-group">	
					<?php echo form_label(lang('common_employees_sending_to'), 'sending_to',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label ')); ?>
					<div class="col-sm-9 col-md-9 col-lg-10">
						<?php echo form_input(array(
							'name'=>'employee[]',
							'id'=>'sending_to',
							'class'=>'form-control company_names',
							'disabled'=>'disabled',
							'value'=>$employee->first_name.' '.$employee->last_name)
							);?>
						</div>
					</div>
					
					<?php echo form_hidden('employees[]',$employee->person_id) ?>
				
					<div class="form-group">	
					<?php echo form_label(lang('common_employees_message'), 'message',array('class'=>'required col-sm-3 col-md-3 col-lg-2 control-label ')); ?>
					<div class="col-sm-9 col-md-9 col-lg-10">
						<?php echo form_textarea(array(
							'name'=>'message',
							'id'=>'message',
							'class'=>'form-control text-area company_names',
							'value'=>'')
							);?>
						</div>
					</div>

				
				<div class="form-group">	
				
				<div class="form-actions col-md-12">
					<?php
					echo form_submit(array(
						'name'=>'submitf',
						'id'=>'submitf',
						'value'=>lang('common_save'),
						'class'=>' btn btn-primary pull-right')
					);
					?>
				</div>
				<?php echo form_close(); ?>
		</div>
	</div>
</div>
 
	<script type='text/javascript'>
	//validation and submit handling
	$(document).ready(function()
	{
		setTimeout(function(){$(":input:visible:first","#send_message_form").focus();},100);
	
		var submitting = false;
		$('#send_message_form').validate({
			submitHandler:function(form)
			{
$('#grid-loader').show();
				if (submitting) return;
				submitting = true;
				$(form).ajaxSubmit({
					success:function(response)
					{
$('#grid-loader').hide();
						submitting = false;						
						show_feedback(response.status ? 'success' : 'error' ,<?php echo json_encode(lang('common_successfully_sent_message')); ?>, response.status ? <?php echo json_encode(lang('common_success')); ?> : <?php echo json_encode(lang('common_error')); ?>);
						$('#myModal').modal('hide');
					
					},
				
					dataType:'json'
				});
			},
			rules: 
			{
				message: "required",
			},
			errorClass: "text-danger",
			errorElement: "span",
				highlight:function(element, errorClass, validClass) {
					$(element).parents('.form-group').removeClass('has-success').addClass('has-error');
				},
				unhighlight: function(element, errorClass, validClass) {
					$(element).parents('.form-group').removeClass('has-error').addClass('has-success');
				},
			messages: 
			{
					message: <?php echo json_encode(lang('messages_must_write_message')); ?>
		
			}
		});
	});


</script>

