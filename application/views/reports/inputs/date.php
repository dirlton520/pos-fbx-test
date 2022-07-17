<div class="form-group">
	<?php echo form_label(lang('common_date').':', 'date',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label   ')); ?>
	
	<div id="report_date_range_complex" class="col-sm-6 col-md-6 col-lg-8">
		<div class="row">
			<div class="col-md-6">
				<div class="input-group input-daterange" id="day_picker">
					<span class="input-group-addon bg date-picker"><?php echo lang('common_day'); ?></span>
             <input type="text" class="form-control date" name="date" id="date" value="<?php echo $this->input->get('date') ? date(get_date_format(),strtotime($this->input->get('date'))) : date(get_date_format()); ?>">
        </div>
			</div>
		</div>
	</div>
	
</div>

<script>	
$(document).ready(function()
{	
	date_time_picker_field_report($('#date'), JS_DATE_FORMAT);
});

</script>