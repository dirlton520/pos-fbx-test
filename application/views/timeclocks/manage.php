<?php $this->load->view("partial/header"); ?>
	<div class="row">
		<div class="col-md-12">
			<div class="panel">
				<div class="panel-body">
					
					<?php
					if($is_clocked_at_another_location)
					{
						echo '<h3 class="text-danger text-center">'.lang('timeclocks_already_clocked_in_at_antoher_location').'</h3>';
					}
					?>
					<?php echo lang('common_comments'); ?>:		
					<?php echo form_textarea(array(
									'name'=>'comment',
									'id'=>'comment',
									'value'=>'',
									'class'=>'form-control text-area',
									'rows'=>'3',
									'cols'=>'20')
						);?>	
					<br>
					<div class="form-group timeclocks" id="clock_out_actions">
						<ul class="list-inline">
							<?php
							if (!$is_clocked_in)
							{
							?>
							<li>
								<?php echo anchor("timeclocks/in", '<i class="ion-log-in"></i> '.lang('common_clock_in'), array('id' => 'clock_in', 'class'=>'btn btn-primary')); ?>
							</li>
							<?php
							}
							else
							{
							?>
							<li>
								<?php echo anchor("timeclocks/out", '<i class="ion-log-out"></i> '.lang('common_clock_out'), array('id' => 'clock_out', 'class'=>'btn btn-primary')); ?>
							</li>
							<li>
								<?php echo lang('common_or'); ?>
							</li>
							<li>
								<input type="button" id="logout_without_closing" class="btn btn-danger" value="<?php echo lang('timeclocks_logout_without_clock_out'); ?>">
							</li>
							
							<?php 
							} 
							?>
							
							<li>
								<?php echo anchor("timeclocks/punches", '<i class="ion-chevron-down-round"></i> '.lang('timeclocks_my_punches'), array('id' => 'punches', 'class'=>'btn btn-primary')); ?>
							</li>
							
							<?php if ($this->config->item('timeclock_pto')) { ?>
							<li class="pull-right">
								<?php echo anchor("timeclocks/request_time_off", '<i class="ion-chevron-down-round"></i> '.lang('timeclocks_request_time_off').' &raquo;', array('id' => 'punches', 'class'=>'btn btn-primary')); ?>
							</li>
							
							<li class="pull-right">
								<?php echo anchor("timeclocks/time_off", '<i class="ion-chevron-down-round"></i> '.lang('timeclocks_my_time_off_requests'), array('id' => 'punches', 'class'=>'btn btn-primary')); ?>
							</li>
							<?php } ?>
							
						</ul>
					</div>
					
					<div id="clock_out_completed_actions" style="display: none;">
						<ul class="list-inline">						
							<li>
								<input type="button" id="logout_after_clockout" class="btn btn-primary" value="<?php echo lang('common_logout'); ?>">
							</li>
						</ul>
					</div>
				</div>
			</div>
		</div>
	</div>

	
		
<script type="text/javascript">
	$("#clock_in").click(function()
	{
		var that = this;
		$.post($(this).attr('href'), {comment: $('#comment').val()}, function(response)
		{
			if (response.success)
			{
				$(that).fadeOut();
				show_feedback(response.success ? 'success' : 'error', response.message,response.success ? <?php echo json_encode(lang('common_success')); ?> : <?php echo json_encode(lang('common_error')); ?>);
			}	
		}, 'json');
		return false;
	});
	
	$("#clock_out").click(function()
	{
		var that = this;
		$.post($(this).attr('href'), {comment: $('#comment').val()}, function(response)
		{
			<?php if ($this->config->item('logout_on_clock_out')) { ?>
				window.location = '<?php echo site_url('home/logout'); ?>';
			<?php } else { ?>
			
			show_feedback(response.success ? 'success' : 'error', response.message,response.success ? <?php echo json_encode(lang('common_success')); ?> : <?php echo json_encode(lang('common_error')); ?>);
			
			if (response.success)
			{
				$("#clock_out_actions").fadeOut(function()
				{
					$("#clock_out_completed_actions").fadeIn();
				});
				

			}	
			<?php } ?>		
		}, 'json');
		return false;
	});
	
	
	$("#logout_without_closing").click(function()
	{
		bootbox.confirm(<?php echo json_encode(lang('common_confirm_timeclock_logout')); ?>, function(result)
		{
			if(result)
			{
				window.location = '<?php echo site_url('home/logout'); ?>';
			}
		});
	});
	
	$("#logout_after_clockout").click(function()
	{
		window.location = '<?php echo site_url('home/logout'); ?>';	
	});
	
</script>

<?php $this->load->view("partial/footer"); ?>