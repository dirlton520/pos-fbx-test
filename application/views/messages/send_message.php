<?php $this->load->view("partial/header"); ?>

<div class="manage_buttons">
	<div class="row">
		<div class="col-md-3">
			
		</div>
		<div class="col-md-9">	
			<div class="buttons-list">
				<div class="pull-right-btn">
					<a href="<?php echo site_url('messages'); ?>" id="new-person-btn" class="btn btn-success btn-lg"><span class="ion-ios-filing"> <?php echo lang('messages_inbox') ?></span></a>
					<?php if ($this->Employee->has_module_action_permission('messages', 'send_message', $this->Employee->get_logged_in_employee_info()->person_id)) {?>
					<a href="<?php echo site_url('messages/sent_messages'); ?>" id="sent_messages" class="btn btn-warning btn-lg"><span class="ion-paper-airplane"> <?php echo lang('messages_sent_messages') ?></span></a>
					<?php } ?>

				</div>
			</div>
		</div>				
	</div>
</div>


<div class="row manage-table" id="form">
	<div class="spinner" id="grid-loader" style="display:none">
	  <div class="rect1"></div>
	  <div class="rect2"></div>
	  <div class="rect3"></div>
	</div>
	<div class="col-md-12">
		<?php echo form_open_multipart('messages/save_message/',array('id'=>'send_message_form','class'=>'form-horizontal')); 	?>
		<div class="panel panel-piluku">
			<div class="panel-heading">
				<?php echo lang("common_messages_basic_info"); ?> (<small><?php echo lang('common_fields_required_message'); ?></small>)
			</div>
			
			<div class="panel-body">
				
					<?php if ($this->Location->count_all() > 1) { ?>
				
					<div class="form-group">	
						<?php echo form_label('Locations :', 'locations',array('class'=>'required col-sm-3 col-md-3 col-lg-2 control-label ')); ?>
						<div class="col-sm-9 col-md-9 col-lg-10">
							<div class="input-group">
								<span class="input-group-addon bg message-input">
									<input type="checkbox" aria-label="All" value="all" name="all_locations" id="all_locations" > 
									<label for="all_locations"><span></span>All</label>
								</span>
								<?php echo form_dropdown('locations[]',$locations, '', 'id="locations" class="input-medium" multiple="multiple"'); ?>
							</div><!-- /input-group -->
						</div>
					</div>
					<?php } else { ?>
						<?php echo form_hidden('locations[]',$this->Employee->get_logged_in_employee_current_location_id()); ?>
					<?php } ?>

					<div class="form-group">	
						<?php echo form_label('Employees :', 'employees',array('class'=>'required col-sm-3 col-md-3 col-lg-2 control-label ')); ?>
						<div class="col-sm-9 col-md-9 col-lg-10">
							<div class="input-group">
								<span class="input-group-addon bg message-input">
									<input type="checkbox" aria-label="All" value="all" name="all_employees" id="all_employees" > 
									<label for="all_employees"><span></span>All</label>
								</span>
								<?php echo form_dropdown('employees[]',$employees, '', 'id="employees" class="input-medium" multiple="multiple"'); ?>
							</div><!-- /input-group -->
						</div>
					</div>
				
				
					<div class="form-group">	
						<?php echo form_label(lang('common_employees_message').':', 'message',array('class'=>'required col-sm-3 col-md-3 col-lg-2 control-label ')); ?>
						<div class="col-sm-9 col-md-9 col-lg-10">
							<?php echo form_textarea(array(
								'name'=>'message',
								'id'=>'message',
								'class'=>'form-control text-area company_names',
								'value'=>'')
								);?>
						</div>
					</div>

				
					<div class="form-controls">	
						<ul class="list-inline pull-right">
							<li>
								<?php
								echo form_submit(array(
									'name'=>'submitf',
									'id'=>'submitf',
									'value'=>lang('common_send'),
									'class'=>' btn btn-primary')
								);
								?>
							</li>
						</ul>
					</div>
			</div>
		</div>
				<?php echo form_close(); ?>
	</div>
</div>
</div>

 
<script type='text/javascript'>
	//validation and submit handling
	$(document).ready(function()
	{
		$("#employees,#locations").select2();

		//Location toggle checkbox for all 
		$('#all_locations').on('change',function(){
			
			$("#locations").prop("disabled", false);

			if($(this).prop('checked'))
				  $("#locations").prop("disabled", true);
		});

		//Employee toggle checkbox for all 
		$('#all_employees').on('change',function(){
			
			$("#employees").prop("disabled", false);

			if($(this).prop('checked'))
				  $("#employees").prop("disabled", true);
		});

		setTimeout(function(){$(":input:visible:first","#send_message_form").focus();},100);
		
		$('#locations').on("select2-close", function(e) { 
			fetch_employees();
		});

		$('#locations').on("select2-removed", function(e) { 
			fetch_employees();
		});
		

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

						if(response.status)
						{
							window.location.href = '<?php echo site_url('messages/sent_messages'); ?>'	
						}
						else
						{
							show_feedback(response.success ? 'success' : 'error', response.message,response.success ? <?php echo json_encode(lang('common_success')); ?> : <?php echo json_encode(lang('common_error')); ?>);
						}
						
					
					},
				
					dataType:'json'
				});
			},
			rules: 
			{
				employees: "required",
				locations: "required",
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
					employees: <?php echo json_encode(lang('messages_employees_required')); ?>,
					locations: <?php echo json_encode(lang('messages_locations_required')); ?>,
					message: <?php echo json_encode(lang('messages_must_write_message')); ?>,
		
			}
		});
	});

	function fetch_employees()
	{
 	   $("#employees").val(null).trigger("change"); 
	   $("#employees").select2('destroy');
	   	var postForm = { 
            'selected_locations'     : $("#locations").val() 
        };

			$.ajax({ 
    	    type      : 'POST', 
            url       : <?php echo json_encode(site_url('messages/get_locations_employees')); ?>,
		        data      : postForm, 
        	dataType  : 'json',
        	success   : function(data) {

        		$('#employees').find('option').remove().end();

				 	$.each(data.employees, function(id, name) {   
				     $('#employees')
				         .append($("<option value="+id+">"+ name +"</option>")
			         ); 
				});
		    },
    	});

		$("#employees").select2();
	}
</script>

<?php $this->load->view("partial/footer"); ?>