<?php echo form_open('login/switch_user/'.($reload ? '1' : '0' ),array('id'=>'login_form','class'=>'form-horizontal')); ?>

<div class="modal-dialog">
	<div class="modal-content">
		<div class="modal-header">
			<?php 
			$this->load->helper('demo');
			if (is_on_demo_host()) { ?>
				<button type="button" class="close" data-dismiss="modal" aria-label=<?php echo json_encode(lang('common_close')); ?>><span aria-hidden="true" class="ti-close"></span></button>
			<?php } ?>
			<h4 class="modal-title"> <?php echo lang('common_switch_user'); ?></h4>
		</div>
		<div class="modal-body ">

			<div class="row">
				<div class="col-md-12">
					<i id="spin" class="fa fa-spinner fa fa-spin  hidden"></i>
					<span id="error_message" class="text-danger">&nbsp;</span>

					<div class="form-group">
					<?php echo form_label(lang('common_employee').':', 'employee',array('class'=>'col-sm-3 col-md-3 col-lg-3 control-label  required wide')); ?>
						<div class="col-sm-9 col-md-9 col-lg-9">
							<?php echo form_dropdown('username', $employees, $this->Employee->get_logged_in_employee_info()->username, 'class="form-control" id="username"');?>
						</div>
					</div>

					<div class="not_fast_user_switching" style="display: none;">
						<div class="form-group">
							<?php echo form_label(lang('login_password').':', 'password',array('class'=>'col-sm-3 col-md-3 col-lg-3 control-label  required wide')); ?>
							<div class="col-sm-9 col-md-9 col-lg-9">
								<?php echo form_password(array(
								'name'=>'password', 
								'id' => 'password',
								'value'=>'',
								'class'=>'form-control',
								'size'=>'20')); ?>
							</div>
						</div>
					
						<script type="text/javascript">
						$("#password").focus();
						</script>
					</div>
					
					<div class="fast_user_switching" style="display: none">
						<h2 class='text-center'><?php echo lang('common_or'); ?></h2>
						
						<div class="form-group">
							<?php echo form_label(lang('common_employees_number').' / '.lang('common_username').':', 'username_or_account_number',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label  required wide')); ?>
							<div class="col-sm-9 col-md-9 col-lg-10">
								<?php echo form_input(array(
								'type' => 'text',
								'name'=>'username_or_account_number', 
								'id' => 'username_or_account_number',
								'value'=>'',
								'class'=>'form-control',
								'size'=>'20')); ?>
							</div>
						</div>
						
						<script type="text/javascript">
						$("#username_or_account_number").focus();
						</script>
					</div>

				</div>	
			</div>
		</div>
	
		<div class="modal-footer">
			<div class="form-acions">
				<?php
				echo form_submit(array(
					'name'=>'submit',
					'id'=>'submit',
					'value'=>lang('common_save'),
					'class'=>'submit_button btn btn-primary btn-block btn-lg')
				);
				?>
			</div>
		</div>
			
	</div>
</div>
	
<?php echo form_close(); ?>

<script type='text/javascript'>
//validation and submit handling
$('#username').selectize();
$("#username").change(user_changed);
$("#username").ready(user_changed);

function user_changed()
{
	var username = $("#username").val();
	$.post(<?php echo json_encode(site_url('login/can_fast_switch'));?>, {username: username}, function(response)
	{
		if (response.allowed)
		{
			$(".fast_user_switching").show();
			$(".not_fast_user_switching").hide();
		}
		else
		{
			$(".fast_user_switching").hide();
			$(".not_fast_user_switching").show();
			$("#password").focus();			
		}
	
	},'json');
}

$("#login_form").ajaxForm({
	success:function(response)
	{
		$('#spin').addClass('hidden');
		if(!response.success)
		{
			$('#error_message').html(response.message);
		}
		else
		{
			if (response.reload == 0) 
			{
				if (response.is_clocked_in_or_timeclock_disabled)
				{
					$(".avatar_info").text(response.name);
					$(".avatar_width img").attr('src', response.avatar);
					$('#myModalDisableClose').modal('hide');	
					$("#item").focus();
					show_feedback('success',<?php echo json_encode(lang('login_swich_user_success')); ?>,<?php echo json_encode(lang('common_success')); ?>);
				}
				else
				{
					window.location = '<?php echo site_url('timeclocks'); ?>';
				}
				
									
			}
			else 
			{
				$('#myModalDisableClose').modal('hide');
				
				if (response.is_clocked_in_or_timeclock_disabled)
				{
					window.location.reload(true);								
				}
				else
				{
					window.location = '<?php echo site_url('timeclocks'); ?>';
				}
			} 
		}
	},
	dataType:'json'
});

</script>