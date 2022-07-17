<?php $this->load->view("partial/header"); ?>
<div id="status"><?php echo lang('common_wait');?> <?php echo img(array('src' => base_url().'assets/img/ajax-loader.gif')); ?></div>

<div class="panel panel-piluku">
	<div class="panel-body">
	   <h4 id="title"><?php echo lang('sales_please_swipe_credit_card_on_machine');?></h4>
	</div>
</div>

<form id="formCheckout" method="post" action="<?php echo site_url('sales/start_cc_processing_trans_cloud'); ?>">
</form>
<?php $this->load->view("partial/footer"); ?>

<script>
$(document).ready(function()
{
	setTimeout(function()
	{
		$("#formCheckout").submit();	
	},0)
});
</script>