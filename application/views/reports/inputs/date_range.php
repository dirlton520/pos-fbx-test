<?php
$compare_to = isset($compare_to) && $compare_to;
$compare_suffix = $compare_to ? '_compare' : '';
$compare_middle_suffix = $compare_to ? 'compare_' : '';

if(isset($report_type) && $report_type != 'complex' || $this->input->get('report_type'.$compare_suffix) == 'simple' || !$this->input->get('report_type'.$compare_suffix))
{
	$report_type_value = 'simple';
}
else
{
	$report_type_value = 'complex';
}

?>	
<input type="hidden" name="report_type<?php echo $compare_suffix; ?>" id="report_type<?php echo $compare_suffix; ?>" value='<?php echo $report_type_value; ?>' />

<?php if ($compare_to) { ?>
<div class="form-group">
		<?php echo form_label(lang('reports_compare_to_date_range').':', 'compare_to_label'.$compare_suffix, array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label')); ?>
		<div class="col-sm-9 col-md-9 col-lg-10">
		<?php echo form_checkbox(array(
			'name'=>'compare_to',
			'id'=>'compare_to',
			'value'=>'compare_to',
			'checked' => $this->input->get('compare_to'),
			));?>
			<label for="compare_to"><span></span></label>
		</div>
			
</div>
<div id="compare_to_holder" style='display: none;'>
<?php } ?>

<div class="form-group">
	<?php echo form_label(lang('reports_date_range').':', 'date_range'.$compare_suffix,array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label   ')); ?>

	<div class="col-sm-3 col-md-3 col-lg-2">
		<?php echo form_dropdown('report_date_range_simple'.$compare_suffix,$compare_to ? $report_date_range_simple_compare : $report_date_range_simple, isset($date_range_simple_value) ? $date_range_simple_value : $this->input->get('report_date_range_simple'.$compare_suffix), 'id="report_date_range_simple'.$compare_suffix.'" class="form-control"'); ?>
	</div>
	
	<div id="report_date_range_complex<?php echo $compare_suffix; ?>" class="col-sm-6 col-md-6 col-lg-8 <?php echo $report_type_value == 'simple' ? 'hidden' : '' ?>">
		<div class="row">
			<div class="col-md-6">
				<div class="input-group input-daterange" id="reportrange">
					<span class="input-group-addon bg date-picker"><?php echo lang('reports_from'); ?></span>
             <input type="text" class="form-control start_date" name="start_date<?php echo $compare_suffix; ?>" id="start_date<?php echo $compare_suffix; ?>" <?php echo isset($start_date_value) ? "value='$start_date_value'" : "value='".$this->input->get('start_date_'.$compare_middle_suffix.'formatted')."'" ?>>
        </div>
			</div>
			
			<div class="col-md-6">
				<div class="input-group input-daterange" id="reportrange1">
        <span class="input-group-addon bg date-picker"><?php echo lang('reports_to'); ?></span>
       <input type="text" class="form-control end_date" name="end_date<?php echo $compare_suffix; ?>" id="end_date<?php echo $compare_suffix; ?>" <?php echo isset($end_date_value) ? "value='$end_date_value'" : "value='".$this->input->get('end_date_'.$compare_middle_suffix.'formatted')."'" ?>>
      	</div>	
			</div>
		</div>
	</div>
	
</div>


<?php if ($compare_to) { ?>
	<!-- End container that hides compare to part -->
</div>
<?php } ?>

<script>	
	$(document).ready(function()
	{
		$("#report_date_range_comple")
		$("#start_date<?php echo $compare_suffix; ?>").click(function(){
			$("#report_type<?php echo $compare_suffix; ?>").val('complex');
			$('#report_date_range_complex<?php echo $compare_suffix; ?>').show();			
		}); 
		
		$("#end_date<?php echo $compare_suffix; ?>").click(function(){
			$("#report_type<?php echo $compare_suffix; ?>").val('complex');
			$('#report_date_range_complex<?php echo $compare_suffix; ?>').show();			
		});
	
		$("#report_date_range_simple<?php echo $compare_suffix; ?>").change(function()
		{
			if ($(this).val() == 'CUSTOM')
			{
				$("#report_type<?php echo $compare_suffix; ?>").val('complex');
				$('#report_date_range_complex<?php echo $compare_suffix; ?>').removeClass('hidden');
			}
			else {
				$("#report_type<?php echo $compare_suffix; ?>").val('simple');
				$('#report_date_range_complex<?php echo $compare_suffix; ?>').addClass('hidden');
			}
			
		});
	
		<?php
		if (isset($with_time) && $with_time)
		{
		?>
	  	date_time_picker_field_report($('#start_date<?php echo $compare_suffix; ?>'), JS_DATE_FORMAT+ " "+JS_TIME_FORMAT);
	  	date_time_picker_field_report($('#end_date<?php echo $compare_suffix; ?>'), JS_DATE_FORMAT+ " "+JS_TIME_FORMAT);
		
		<?php
		}
		else
		{
			?>
	  	date_time_picker_field_report($('#start_date<?php echo $compare_suffix; ?>'), JS_DATE_FORMAT);
	  	date_time_picker_field_report($('#end_date<?php echo $compare_suffix; ?>'), JS_DATE_FORMAT);			
		<?php
		}
		?>
		
 	 if($("#simple_radio<?php echo $compare_suffix; ?>").data('start-checked') == 'checked')
 	 {
 	 		$("#simple_radio<?php echo $compare_suffix; ?>").prop('checked', true);
 	 }
 	 else if($("#complex_radio<?php echo $compare_suffix; ?>").data('start-checked'))
 	 {
 			$("#complex_radio<?php echo $compare_suffix; ?>").prop('checked', true);	 	
 	 }
		
	
		<?php if ($compare_to) { ?>	
		$("#compare_to").click(function()
		{
			
			if ($(this).prop('checked'))
			{
				$('#compare_to_holder').show();
			}
			else
			{
				$('#compare_to_holder').hide();				
			}
		});
	
	
		//On initiall load
		if ($('#compare_to').prop('checked'))
		{
			$('#compare_to_holder').show();
		}
	
     	
		$("#report_date_range_simple_compare").change(function()
		{
			$("#simple_radio_compare").prop('checked', true);
		});
	
	<?php } ?>
	 
		<?php if (!isset($compare_to) || !$compare_to) { ?>
	 
		$("#report_input_form,#salesReportGenerator").submit(function()
		{
			<?php
			//End date values should be at end of day
			if ((!isset($with_time) || $with_time == false) && (!isset($end_date_end_of_day) || $end_date_end_of_day === TRUE))
			{
				?>
				$("#end_date").val($("#end_date").val()+' 23:59:59');
		
				<?php if ($compare_to) { ?>
					$("#end_date_compare").val($("#end_date_compare").val()+' 23:59:59');			
					<?php
				}
			}
			?>
		});
		
<?php } ?>
	 
});

</script>

<?php 
if (isset($compare_to) && $compare_to)
{
	if (isset($with_time) && $with_time) 
	{ 
		echo form_hidden('compare_with_time',1);
	}
	else 
	{ 
		echo form_hidden('compare_with_time',0);
	}

	if (isset($end_date_end_of_day) && $end_date_end_of_day) 
	{ 
		echo form_hidden('compare_end_date_end_of_day',1);
	}
	else 
	{ 
		echo form_hidden('compare_end_date_end_of_day',0);
	}
	
}
else
{
	if (isset($with_time) && $with_time) 
	{ 
		echo form_hidden('with_time',1);
	}
	else 
	{ 
		echo form_hidden('with_time',0);
	}
	
	if (isset($end_date_end_of_day) && $end_date_end_of_day) 
	{ 
		echo form_hidden('end_date_end_of_day',1);
	}
	else 
	{ 
		echo form_hidden('end_date_end_of_day',0);
	}
	
}