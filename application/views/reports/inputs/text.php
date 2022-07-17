<div class="form-group">
	<?php echo form_label($label.':', $name, array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label')); ?>
	<div class="col-sm-2 col-md-2 col-lg-2" id='<?php echo $name;?>'>								
		<?php echo form_input(array(
			'name'=>$name,
			'id'=>$name,
			'class'=>'form-control',
			'size'=>'20',
			'value'=>$this->input->get($name) ? $this->input->get($name) : (isset($default) ? $default : 50)));
		?>									
	</div>
</div>