<div class="modal-dialog">
	<div class="modal-content customer-recent-sales">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-label=<?php echo json_encode(lang('common_close')); ?>><span aria-hidden="true" class="ti-close"></span></button>
			<h4 class="modal-title"> <?php echo lang('common_edit_profile'); ?></h4>
		</div>
		<div class="modal-body ">
			<div class="row" id="form">
				
				<div class="spinner" id="grid-loader" style="display:none">
				  <div class="rect1"></div>
				  <div class="rect2"></div>
				  <div class="rect3"></div>
				</div>
				<div class="col-md-12">
					<?php 
						echo form_open('home/do_edit_profile',array('id'=>'employee_form','class'=>'form-horizontal'));
					?>

					<?php $this->load->view("people/form_basic_info"); ?>

					<legend class="page-header text-info"> &nbsp; &nbsp; <?php echo lang("common_login_info"); ?></legend>
					<div class="form-group">	
					<?php echo form_label(lang('common_username').':', 'username',array('class'=>'col-sm-3 col-md-4 col-lg-3 control-label required')); ?>
					<div class="col-sm-9 col-md-8 col-lg-9">
						<?php echo form_input(array(
							'name'=>'username',
							'id'=>'username',
							'class'=>'form-control',
							'value'=>$person_info->username));?>
						</div>
					</div>

					<div class="form-group">	
					<?php echo form_label(lang('common_password').':', 'password',array('class'=>'col-sm-3 col-md-4 col-lg-3 control-label')); ?>
					<div class="col-sm-9 col-md-8 col-lg-9">
						<?php echo form_password(array(
							'name'=>'password',
							'id'=>'password',
							'class'=>'form-control',
							'autocomplete'=>'off',
						));?>
						</div>
					</div>

					<div class="form-group">	
					<?php echo form_label(lang('common_repeat_password').':', 'repeat_password',array('class'=>'col-sm-3 col-md-4 col-lg-3 control-label')); ?>
					<div class="col-sm-9 col-md-8 col-lg-9">
						<?php echo form_password(array(
							'name'=>'repeat_password',
							'id'=>'repeat_password',
							'class'=>'form-control',
							'autocomplete'=>'off',
						));?>
						</div>
					</div>

					<div class="form-group">	
					<?php echo form_label(lang('common_language').':', 'language',array('class'=>'col-sm-3 col-md-4 col-lg-3 col-sm-3 col-md-4 col-lg-3 control-label  required')); ?>
						<div class="col-sm-9 col-md-8 col-lg-9">
						<?php echo form_dropdown('language', array(
							'english'  => 'English',
							'indonesia'    => 'Indonesia',
							'spanish'   => 'Español', 
							'french'    => 'Fançais',
							'italian'    => 'Italiano',
							'german'    => 'Deutsch',
							'dutch'    => 'Nederlands',
							'portugues'    => 'Portugues',
							'arabic' => 'العَرَبِيةُ‎‎',
							'khmer' => 'Khmer',
							'vietnamese' => 'Vietnamese',
							'chinese' => '中文',
							'chinese_traditional' => '繁體中文',
							'tamil' => 'Tamil',
							),
							$person_info->language ? $person_info->language : $this->Appconfig->get_raw_language_value(), 'class="form-control"');
							?>
						</div>
					</div>


					<div class="modal-footer">
						<div class="form-acions">
							<?php
									echo form_submit(array(
										'name'=>'submitf',
										'id'=>'submitf',
										'value'=>lang('common_save'),
										'class'=>'btn btn-primary btn-block float_right btn-lg')
									);

							?>
						</div>
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
					
$('#image_id').imagePreview({ selector : '#avatar' }); // Custom preview container

//validation and submit handling
$(document).ready(function()
{
    setTimeout(function(){$(":input:visible:first","#employee_form").focus();},100);

	$('#employee_form').validate({
		submitHandler:function(form)
		{
			doEmployeeSubmit(form);
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
			first_name: "required",

			username:
			{
				required:true,
				minlength: 1
			},

			password:
			{
				minlength: 1
			},	
			repeat_password:
			{
 				equalTo: "#password"
			},
    		email: {
				"required": true
			}
		},
		messages: 
		{
     		first_name: <?php echo json_encode(lang('common_first_name_required')); ?>,
     		last_name: <?php echo json_encode(lang('common_last_name_required')); ?>,
     		username:
     		{
     			required: <?php echo json_encode(lang('common_username_required')); ?>,
     			minlength: <?php echo json_encode(lang('common_username_minlength')); ?>
     		},
			password:
			{
				minlength: <?php echo json_encode(lang('common_password_minlength')); ?>
			},
			repeat_password:
			{
				equalTo: <?php echo json_encode(lang('common_password_must_match')); ?>
     		},
     		email: <?php echo json_encode(lang('common_email_invalid_format')); ?>
		}
	});
});

var submitting = false;

function doEmployeeSubmit(form)
{
$('#grid-loader').show();
	if (submitting) return;
	submitting = true;

	$(form).ajaxSubmit({
	success:function(response)
		{
$('#grid-loader').hide();
			submitting = false;
			$('#myModal').modal('hide');
			if (response.success)
			{
				show_feedback('success', response.message, <?php echo json_encode(lang('common_success')); ?>+' #' + response.person_id);
			}
			else
			{
				show_feedback('error', response.message, <?php echo json_encode(lang('common_error')); ?>);
			}
			
		},
		dataType:'json'
	});
}
</script>
