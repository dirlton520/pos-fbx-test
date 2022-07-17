<?php
$this->load->view("partial/header_standalone");
?>
<br />
<div class="row">
<form action="" method="POST">
		<div class="col-sm-9 col-md-9 col-lg-10">	
	<div class="form-group">
		<?php echo form_label(lang('common_email').'/'.lang('common_phone_number').':', 'email_phone',array('class'=>'col-sm-3 col-md-3 col-lg-2')); ?>
		<div class="col-sm-9 col-md-9 col-lg-10">
	
		<?php echo form_input(array(
		'name'=>'email_phone',
		'id'=>'email_phone',
		'value' => '',
		'class'=> 'form-control',
			)
		);?>
		
		<br />
		<input type="submit" class="btn btn-primary" value="<?php echo lang('common_submit'); ?>">
		
	</div>
</div>
</div>

</form>
<?php
$this->load->view("partial/footer_standalone");
?>