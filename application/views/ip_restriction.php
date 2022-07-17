<?php $this->load->view("partial/header"); ?>


<div class="container">
	<div class="alert alert-danger no-access">
		<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
		<?php echo lang('common_cannot_access_with_ip').':'.' <strong>'.$ip.'</strong>';  ?>
	</div>
</div>

 <?php $this->load->view('partial/footer.php'); ?>