<div class="modal-dialog customer-recent-sales">
	<div class="modal-content">
		<div class="modal-header" >
			<button type="button" class="close" data-dismiss="modal" aria-label=<?php echo json_encode(lang('common_close')); ?>><span aria-hidden="true" class="ti-close"></span></button>
			<div class="modal-item-details">
					<h4 class="modal-title"><?php echo lang('config_add_key'); ?></h4>
			</div>
		</div>
		
		<div class="modal-body">
				<?php echo form_open('config/save_api_key',array('id'=>'api_key_form','class'=>'form-horizontal')); ?>
				
				<div class="form-group">
					<?php echo form_label(lang('common_description').':', 'description',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label wide')); ?>
					<div class="col-sm-9 col-md-9 col-lg-9">
						<?php echo form_input(array(
							'type'  => 'text',
							'name'  => 'description',
							'id'    => 'description',
							'value' => '',
							'class'=> 'form-control form-inps',
						)); ?>
					</div>
				</div>
				
				<div class="form-group">
					<?php echo form_label(lang('config_api_key').':', 'parent_id',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label wide')); ?>
					<div class="col-sm-9 col-md-9 col-lg-9">
						<div class="input-group">
							<?php echo form_input(array(
								'type'  => 'text',
								'name'  => 'api_key',
								'id'    => 'api_key',
								'value' => $api_key,
								'class'=> 'form-control form-inps',
								'readonly' => 'readonly'
							)); ?>
				      <span class="input-group-btn">
				        <button id="copy_btn" class="btn btn-default" type="button" data-container="body" data-toggle="tooltip" data-placement="left" title="<?php echo lang("config_key_copied_to_clipboard") ?>"><?php echo lang('common_copy_to_clipboard'); ?></button>
				      </span>
				    </div><!-- /input-group -->
					</div>
				</div>
				
				<div class="form-group">
					<?php echo form_label(lang('config_permissions').':', 'permissions',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label wide')); ?>
					<div class="col-sm-9 col-md-9 col-lg-9">
						<?php echo form_dropdown('level', array('1' => lang('config_read'),'2' => lang('config_read_write')), '', 'class="form-control form-inps" id="permissions"');?>
					</div>
				</div>
				<div class="form-actions">
					<?php
						echo form_submit(array(
							'name'=>'submitf',
							'id'=>'submitf',
							'value'=>lang('config_add_key'),
							'class'=>'submit_button pull-right btn btn-primary')
						);
					?>
					<div class="clearfix">&nbsp;</div>
				</div>
				
			<?php form_close()?>
		</div>
	</div>
</div>

<script>
	$(function () {
	  $('[data-toggle="tooltip"]').tooltip(
			{
				  trigger: 'manual',
					container: '.modal-body',
					placement: 'top'
			}
	  )
	});
	
	$('#copy_btn').on('click', function(e)
	{
	  $('#api_key').select();
	  document.execCommand("copy");
		
		$('#copy_btn').tooltip('show');
		
	  setTimeout(function() {
	    $('#copy_btn').tooltip('hide');
	  }, 1000);
	});
	
	$("#api_key_form").submit(function(event)
	{
		event.preventDefault();
		
		bootbox.confirm(<?php echo json_encode(lang('config_submit_api_key')); ?>, function(response)
		{
			if (response)
			{
				$("#api_key_form").get(0).submit();
			}
		});
	});
</script>