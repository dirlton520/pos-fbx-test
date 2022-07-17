<div class="form-group">
	<?php echo form_label(lang('reports_export_to_excel').':', '', array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label  ')); ?> 
	<div class="col-sm-9 col-md-9 col-lg-10">
		<input type="radio" name="export_excel" id="export_excel_yes" value='1' <?php echo $this->input->get('export_excel') == '1' ? 'checked="checked"' : '';?>> <?php echo lang('common_yes'); ?>  &nbsp;
		<label for="export_excel_yes"><span></span></label>
		<input type="radio" name="export_excel" id="export_excel_no" value='0' <?php echo !$this->input->get('export_excel') ? 'checked="checked"' : '';?> /> <?php echo lang('common_no'); ?> &nbsp;
		<label for="export_excel_no"><span></span></label>
	</div>
</div>