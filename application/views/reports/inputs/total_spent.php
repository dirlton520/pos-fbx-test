<div class="form-group">
	<?php echo form_label(lang('reports_total_spent').':', 'total_spent_condition', array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label  ')); ?> 
	<div class="col-sm-9 col-md-2 col-lg-2">							
		<?php echo form_dropdown('total_spent_condition',array('any' => lang('reports_any_amount'), 'greater_than' => lang('reports_sales_generator_selectCondition_7'), 'less_than' => lang('reports_sales_generator_selectCondition_8'), 'equal_to' => lang('reports_sales_generator_selectCondition_9')), $this->input->get('total_spent_condition'), 'id="total_spent_condition" class="form-control"'); ?>
	</div>
</div>

<div class="form-group" style="display: none;" id="total_spent_amount_container">
		<?php echo form_label(lang('common_amount').':', 'total_spent_amount', array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label  ')); ?> 
		<div class="col-sm-9 col-md-2 col-lg-2">							
			<input type="text" class="form-control total_spent_amount" name="total_spent_amount" id="total_spent_amount" value="<?php echo H($this->input->get('total_spent_amount'));?>">
		</div>
</div>					

<script>
	$(document).ready(function()
	{
		$("#total_spent_condition").change(function()
		{
			if ($(this).val() != 'any')
			{
				$("#total_spent_amount_container").show();
			}
			else
			{
				$("#total_spent_amount_container").hide();				
			}
		});
		
		if ($('#total_spent_condition').val() != 'any')
		{
			$("#total_spent_amount_container").show();
		}
		
	});
</script>