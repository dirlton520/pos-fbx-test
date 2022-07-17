<?php $this->load->view("partial/header"); ?>
	<div class="row">
		<div class="col-md-12">
			<div class="panel panel-piluku">
					<?php if (isset($success) && $success) { ?>
						<div class="alert alert-success text-center">
							<h4><strong><?php echo lang('timeclocks_request_time_off_succcess'); ?></strong></h4>
						</div>
						<?php } else { ?>
						
						<div class="panel-heading">
					      <h3 class="panel-title"><?php echo lang("timeclocks_request_time_off"); ?></h3>
						</div>
													
				<div class="panel-body">
					<form action="" method="post" class="form-horizontal">
						<div class="form-group">
							<?php echo form_label(lang('common_start_date').':', 'start_day',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label   ')); ?>
							<div id="report_date_range_complex" class="col-sm-9 col-md-9 col-lg-10">
								<div class="row">
									<div class="col-md-6">
										<div class="input-group input-daterange" id="day_picker">
											<span class="input-group-addon bg date-picker"><?php echo lang('common_start_date'); ?></span>
						             <input type="text" class="form-control date" name="start_day" id="start_day" value="<?php echo $this->input->post('start_day') ? date(get_date_format(),strtotime($this->input->post('start_day'))) : date(get_date_format()); ?>">
						        </div>
									</div>
								</div>
							</div>
						</div>
						
						<div class="form-group">
							<?php echo form_label(lang('common_end_date').':', 'end_day',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label   ')); ?>
							<div id="report_date_range_complex" class="col-sm-9 col-md-9 col-lg-10">
								<div class="row">
									<div class="col-md-6">
										<div class="input-group input-daterange" id="day_picker">
											<span class="input-group-addon bg date-picker"><?php echo lang('common_end_date'); ?></span>
						             <input type="text" class="form-control date" name="end_day" id="end_day" value="<?php echo $this->input->post('end_day') ? date(get_date_format(),strtotime($this->input->post('end_day'))) : date(get_date_format()); ?>">
						        </div>
									</div>
								</div>
							</div>
						</div>
						
						<div class="form-group">
							<?php echo form_label(lang('timeclocks_hours_requested_off').':', 'hours_requested',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label   ')); ?>
							<div class="col-sm-9 col-md-9 col-lg-10">
								<div class="row">
									<div class="col-md-6">
											<?php echo form_input(array(
												'name'=>'hours_requested',
												'id'=>'hours_requested',
												'class'=>'form-control',
												'value'=>to_quantity($this->input->post('hours_requested'), false))
												);?>
									</div>
								</div>
							</div>
						</div>

										
						<div class="form-group">
							<?php echo form_label(lang('timeclocks_is_paid').':', 'is_paid',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label   ')); ?>
							<div class="col-sm-9 col-md-9 col-lg-10">
								<div class="row">
									<div class="col-md-6">
										<?php echo form_checkbox(array(
											'name'=>'is_paid',
											'id'=>'is_paid',
											'class' => 'is_paid_checkbox delete-checkbox',
											'value'=>1,
											'checked' => $this->input->post('is_paid'),
										));
										?>
										<label for="is_paid"><span></span></label>
									</div>
								</div>
							</div>
						</div>
						
						<!-- TODO MAKE DROPDOWN via store config-->
						<div class="form-group">
							<?php echo form_label(lang('timeclocks_reason').':', 'reason',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label   ')); ?>
							<div class="col-sm-9 col-md-9 col-lg-10">
								<div class="row">
									<div class="col-md-6">
											<?php echo form_input(array(
												'name'=>'reason',
												'id'=>'reason',
												'class'=>'form-control',
												'value'=>$this->input->post('reason'))
												);?>
									</div>
								</div>
							</div>
						</div>
						<?php
						echo form_submit(array(
							'name'=>'submitf',
							'id'=>'submitf',
							'value'=>lang('common_submit'),
							'class'=>' submit_button btn btn-lg btn-primary pull-right')
						);
						?>
					</form>
					<?php } ?>
					</div>
				</div>
			</div>
	</div>
		
<script type="text/javascript">
	date_time_picker_field_report($('#start_day'), JS_DATE_FORMAT);
	date_time_picker_field_report($('#end_day'), JS_DATE_FORMAT);
	
</script>

<?php $this->load->view("partial/footer"); ?>