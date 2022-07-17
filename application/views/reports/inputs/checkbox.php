<div class="form-group">	
	<?php echo form_label($checkbox_label.':', $checkbox_name,array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label ')); ?>
	<div class="col-sm-9 col-md-9 col-lg-10">
		<?php echo form_checkbox(array(
			'name'=>$checkbox_name,
			'id'=>$checkbox_name,
			'value'=>'1',
			'checked' => $this->input->get($checkbox_name),
			));?>
		<label for=<?php echo json_encode($checkbox_name); ?>><span></span></label>
	</div>
</div>
