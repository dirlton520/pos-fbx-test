<?php $this->load->view("partial/header"); ?>


<div class="container">
	<div class="alert alert-danger no-access">
		<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
		<?php echo lang('error_no_permission_module').' <strong>'.$module_name.'</strong>';  ?>
	</div>
</div>

 <?php $this->load->view('partial/footer.php'); ?>