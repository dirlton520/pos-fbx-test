<?php $this->load->view("partial/header"); ?>

<?php if($redirect) { ?>
<div class="manage_buttons">
	<div class="row">
		<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 margin-top-10">
			<div class="buttons-list">
				<div class="pull-right-btn">
				<?php echo 
					anchor(site_url($redirect), ' ' . lang('common_done'), array('class'=>'btn btn-primary btn-lg ion-android-exit', 'title'=>''));
				?>
				</div>
			</div>
		</div>
	</div>
</div>
<?php } ?>

<div class="modal fade status_modal" id="status_modal" tabindex="-1" role="dialog" aria-labelledby="status" aria-hidden="true">
    <div class="modal-dialog customer-recent-sales">
		<div class="modal-content">
	        <div class="modal-header">
	          	<button type="button" class="close" data-dismiss="modal" aria-label=<?php echo json_encode(lang('common_close')); ?>><span aria-hidden="true">&times;</span></button>
	          	<h4 class="modal-title" id="statusModalDialogTitle">&nbsp;</h4>
	        </div>
	        <div class="modal-body">
				<!-- Form -->
				<?php echo form_open_multipart('deliveries/save_status/',array('id'=>'statuses_form','class'=>'form-horizontal')); ?>
				
				<div class="form-group">
					<?php echo form_label(lang('common_name').':', 'status_name',array('class'=>'col-sm-4 col-md-4 col-lg-3 control-label wide')); ?>
					<div class="col-sm-8 col-md-8 col-lg-9">
						<?php echo form_input(array(
							'type'  => 'text',
							'name'  => 'status_name',
							'id'    => 'status_name',
							'value' => '',
							'class'=> 'form-control form-inps',
						)); ?>
					</div>
				</div>

				<div class="form-group">
					<?php echo form_label(lang('common_description').':', 'status_description',array('class'=>'col-sm-4 col-md-4 col-lg-3 control-label wide')); ?>
					<div class="col-sm-8 col-md-8 col-lg-9">
						<?php echo form_input(array(
							'type'  => 'text',
							'name'  => 'status_description',
							'id'    => 'status_description',
							'value' => '',
							'class'=> 'form-control form-inps',
						)); ?>
					</div>
				</div>

				<div class="form-group">
					<?php echo form_label(lang('deliveries_notify_customer_via_email').':', 'notify_by_email',array('class'=>'col-sm-4 col-md-4 col-lg-3 control-label wide')); ?>
					<div class="col-sm-8 col-md-8 col-lg-9">
						<?php echo form_checkbox(array(
							'name'=>'notify_by_email',
							'id'=>'notify_by_email',
							'class'=>'delete-checkbox',
							'value'=>1)
						);?>
						<label for="notify_by_email"><span></span></label>
					</div>
				</div>
				
				<?php
				if ($this->Location->get_info_for_key('twilio_sms_from'))
				{
				?>
				<div class="form-group">
					<?php echo form_label(lang('deliveries_notify_customer_via_sms').':', 'notify_by_sms',array('class'=>'col-sm-4 col-md-4 col-lg-3 control-label wide')); ?>
					<div class="col-sm-8 col-md-8 col-lg-9">
						<?php echo form_checkbox(array(
							'name'=>'notify_by_sms',
							'id'=>'notify_by_sms',
							'class'=>'delete-checkbox',
							'value'=>1)
						);?>
						<label for="notify_by_sms"><span></span></label>
					</div>
				</div>	
				<?php } ?>	
			
				<div class="form-group">
					<?php echo form_label(lang('common_color').':', 'status_color',array('class'=>'col-sm-4 col-md-4 col-lg-3 control-label')); ?>
					<div class="col-sm-8 col-md-8 col-lg-9">
						<?php echo form_input(array(
							'class'=>'form-control form-inps',
							'name'=>'status_color',
							'id'=>'status_color',
							'value'=>'',
							'autocomplete' => 'off')
						);?>
					</div>
				</div>

				<div class="form-actions">
					<?php
						echo form_submit(array(
							'name'=>'submitf',
							'id'=>'submitf',
							'value'=>lang('common_save'),
							'class'=>'submit_button pull-right btn btn-primary')
						);
					?>
					<div class="clearfix">&nbsp;</div>
				</div>
			
				<?php echo form_close(); ?>
	        </div>
    	</div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<div class="row <?php echo $redirect ? 'manage-table' :''; ?>">
	<div class="col-md-12 form-horizontal">
		<div class="panel panel-piluku">
			<div class="panel-heading"><?php echo lang("deliveries_manage_statuses"); ?></div>
			<div class="panel-body">
				<a href="javascript:void(0);" class="add_status" data-status_id="0">[<?php echo lang('deliveries_add_status'); ?>]</a>
					<div id="statuses_list" class="status-tree">
						<?php echo $statuses_list; ?>
					</div>
				<a href="javascript:void(0);" class="add_status" data-status_id="0">[<?php echo lang('deliveries_add_status'); ?>]</a>
			</div>
		</div>
	</div>
</div><!-- /row -->

				
<script type='text/javascript'>	
	$(function() {
		$('#status_color').colorpicker();
	});

	$(document).on('click', ".edit_status",function()
	{
		$("#statusModalDialogTitle").html(<?php echo json_encode(lang('deliveries_edit_status')); ?>);
		
		var status_id = $(this).data('status_id');
		$("#statuses_form").attr('action',SITE_URL+'/deliveries/save_status/'+status_id);
		
		//Populate form
		$("#statuses_form").find('#status_name').val($(this).data('name'));
		$("#statuses_form").find('#status_description').val($(this).data('description'));
		$("#notify_by_email").prop("checked", $(this).data('notify_by_email'));
		$("#notify_by_sms").prop("checked", $(this).data('notify_by_sms'));
		$("#statuses_form").find('#status_color').val($(this).data('color'));

		//show
		$("#status_modal").modal('show');
	});
	
	$(document).on('click', ".add_status",function()
	{
		$("#statusModalDialogTitle").html(<?php echo json_encode(lang('deliveries_add_status')); ?>);
		
		$("#statuses_form").attr('action',SITE_URL+'/deliveries/save_status');

		//Clear form
		$("#statuses_form").find('#status_name').val("");
		$("#statuses_form").find('#status_description').val("");
		$("#notify_by_email").prop("checked", 0);
		$("#notify_by_sms").prop("checked", 0);
		$("#statuses_form").find('#status_color').val("");

		//show
		$("#status_modal").modal('show');
		
	});

	$("#statuses_form").submit(function(event)
	{
		event.preventDefault();
		$(this).ajaxSubmit({ 
			success: function(response, statusText, xhr, $form){
				show_feedback(response.success ? 'success' : 'error', response.message, response.success ? <?php echo json_encode(lang('common_success')); ?> : <?php echo json_encode(lang('common_error')); ?>);
				if(response.success)
				{
					$("#status_modal").modal('hide');
					$('#statuses_list').load("<?php echo site_url("deliveries/statuses_list"); ?>");
				}		
			},
			dataType:'json',
		});
	});

$(document).on('click', ".delete_status",function()
{
	var status_id = $(this).data('status_id');
	if (status_id)
	{
		bootbox.confirm(<?php echo json_encode(lang('deliveries_status_delete_confirmation')); ?>, function(result)
		{
			if(result)
			{
				$.post('<?php echo site_url("deliveries/delete_status");?>', {status_id : status_id},function(response) {
					show_feedback(response.success ? 'success' : 'error', response.message,response.success ? <?php echo json_encode(lang('common_success')); ?> : <?php echo json_encode(lang('common_error')); ?>);

					//Refresh tree if success
					if (response.success)
					{
						$('#statuses_list').load("<?php echo site_url("deliveries/statuses_list"); ?>");
					}
				}, "json");
			}
		});
	}
});



</script>
<?php $this->load->view('partial/footer'); ?>
