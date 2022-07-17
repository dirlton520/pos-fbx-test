<div class="form-group">
	<?php echo form_label(lang('common_giftcards_giftcard_number').':', 'giftcard_number', array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label')); ?>
	<div class="col-sm-9 col-md-9 col-lg-10" id='giftcard_number'>								
		<?php echo form_input(array(
			'name'=>'giftcard_number',
			'id'=>'giftcard_number',
			'class'=>'form-control',
			'size'=>'20',
			'value'=>$this->input->get('giftcard_number')));
		?>									
	</div>
</div>

<script>
	<?php if (!$this->config->item('disable_giftcard_detection')) { ?>
		giftcard_swipe_field($('#giftcard_number'));
	<?php } ?>
</script>