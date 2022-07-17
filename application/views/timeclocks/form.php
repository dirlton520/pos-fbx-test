<?php $this->load->view("partial/header"); ?>
	<div class="container-fluid" id="form">
		<div class="row">	
			<div class="panel panel-piluku">
				<div class="panel-heading">
					<?php echo lang("timeclocks_timeclock_info"); ?>
				</div>
				<div class="panel-body">
				<?php echo form_open('timeclocks/save/'.$id.'?'.$_SERVER['QUERY_STRING'],array('id'=>'timeclock_form','class'=>'form-horizontal')); ?>

				<div class="form-group">	
					<?php echo form_label(lang('common_employee').':', 'employee',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label ')); ?>
					<div class="col-sm-9 col-md-2 col-lg-2 sale-s">
						<?php echo form_dropdown('employee_id', $employees, $employee_id, 'id="employee_id" class="span3"');?>
					</div>
				</div>
				
				<div class="form-group">	
					<?php echo form_label(lang('common_clock_in').':', 'clock_in',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label ')); ?>
					<div class="col-sm-9 col-md-9 col-lg-10">
						<?php echo form_input(array('name'=>'clock_in','value'=>date(get_date_format(). ' '.get_time_format(), strtotime($in)), 'id'=>'clock_in', 'class'=>'form-control'));?>
					</div>
				</div>
				
				
				<div class="form-group">	
					<?php echo form_label(lang('common_clock_out').':', 'clock_out',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label ')); ?>
					<div class="col-sm-9 col-md-9 col-lg-10">
						<?php echo form_input(array('name'=>'clock_out','value'=> ($out != '0000-00-00 00:00:00' ? date(get_date_format(). ' '.get_time_format(), strtotime($out)) : ''), 'id'=>'clock_out', 'class'=>'form-control'));?>
					</div>
				</div>
				
				<div class="form-group">	
				<?php echo form_label(lang('common_clock_in_comment').':', 'clock_in_comment',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label ')); ?>
					<div class="col-sm-9 col-md-9 col-lg-10">
					<?php echo form_textarea(array(
						'name'=>'clock_in_comment',
						'id'=>'clock_in_comment',
						'class'=>'form-control text-area',
						'value'=>$in_comment,
						'rows'=>'5',
						'cols'=>'17')		
					);?>
					</div>
				</div>
				
				
				<div class="form-group">	
				<?php echo form_label(lang('common_clock_out_comment').':', 'clock_out_comment',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label ')); ?>
					<div class="col-sm-9 col-md-9 col-lg-10">
					<?php echo form_textarea(array(
						'name'=>'clock_out_comment',
						'id'=>'clock_out_comment',
						'class'=>'form-control text-area',
						'value'=>$out_comment,
						'rows'=>'5',
						'cols'=>'17')		
					);?>
					</div>
				</div>
				
				<div class="form-group">	
					<?php echo form_label(lang('common_hourly_pay_rate').':', 'hourly_pay_rate',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label ')); ?>
					<div class="col-sm-9 col-md-9 col-lg-10">
						<?php echo form_input(array('name'=>'hourly_pay_rate','value'=>$hourly_pay_rate ? to_currency_no_money($hourly_pay_rate,2) : '', 'id'=>'hourly_pay_rate', 'class'=>'form-control'));?>
					</div>
				</div>
				
				
				<div class="form-actions pull-right">
					<?php echo form_submit(array(
					'name'=>'submitf',
					'id'=>'submitf',
					'value'=>lang('common_save'),
					'class'=>'btn btn-primary')
					); ?>	
				</div>
				<?php echo form_hidden('location_id', $location_id);?>
				<?php echo form_close(); ?>
			</div>
		</div>
	</div>
</div>
<script type="text/javascript">
$("#employee_id").select2();

$("#timeclock_form").submit(function()
{
  $('input[type=submit]', this).attr('disabled', 'disabled');
});
date_time_picker_field($("#clock_in"),JS_DATE_FORMAT+ " "+JS_TIME_FORMAT);
date_time_picker_field($("#clock_out"),JS_DATE_FORMAT+ " "+JS_TIME_FORMAT);

</script>
<?php $this->load->view("partial/footer"); ?>