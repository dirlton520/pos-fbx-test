<div class="form-group">
	<?php echo form_label($dropdown_label.':', $dropdown_name, array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label ')); ?> 
	<div class="col-sm-9 col-md-2 col-lg-2">
		<?php echo form_dropdown($dropdown_name,$dropdown_options, $this->input->get($dropdown_name) ? $this->input->get($dropdown_name) : $dropdown_selected_value, 'id="'.$dropdown_name.'" class="form-control"'); ?>
	</div>
</div>