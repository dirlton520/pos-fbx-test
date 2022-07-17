<?php $this->load->view("partial/header"); ?>
<div id="status"><?php echo lang('common_wait');?> <?php echo img(array('src' => base_url().'assets/img/ajax-loader.gif')); ?></div>

<div class="panel panel-piluku">
	<div class="panel-body">
	   <h4 id="title"><?php echo lang('sales_please_swipe_credit_card_on_machine');?></h4>
	</div>
</div>
<?php
if(isset($balance)) { ?>
	<script>
	bootbox.alert(<?php echo json_encode(lang('common_balance').': '.to_currency($balance)); ?>, function()
	{
		window.location = '<?php echo site_url('sales');?>';
	});
	</script>
<?php
$this->load->view("partial/footer");

exit;
}
?>
<form id="formCheckout" method="post" action="<?php echo $form_url; ?>">
	<?php foreach($post_data as $key=>$value) { ?>
		<?php echo form_hidden($key, $value);?>
	<?php } ?>
</form>
<?php $this->load->view("partial/footer"); ?>

<script>
delete $.ajaxSettings.headers["cache-control"];

$(document).ready(function()
{

 	 var data = {};
 	 <?php
 	 foreach($reset_params['post_data'] as $name=>$value)
 	 {
 		 if ($name && $value)
 		 {
 		 ?>
	 		 data['<?php echo $name; ?>'] = '<?php echo $value; ?>';
 	 	 <?php 
 		 }
 	 }
 	 ?>	
	
	mercury_emv_pad_reset(<?php echo json_encode($reset_params['post_host']); ?>, <?php echo $this->Location->get_info_for_key('listener_port'); ?>, data, function()
	{
		$("#formCheckout").ajaxSubmit({
			success:function(response)
			{
				var data = response.split("&");
				var processed_data = [];

				for(var i = 0; i < data.length; i++)
				{
				    var m = data[i].split("=");
				    processed_data[m[0]] = m[1];
				}
										
				var balance = <?php echo json_encode(lang('common_balance'));?>;
				bootbox.alert(balance+": "+decodeURI(processed_data.Balance), function()
				{
					window.location = '<?php echo site_url('sales');?>';
				});
			},
			error: function()
			{
				$("#title").html("<span class='text-danger'> " + <?php echo json_encode(lang('sales_unable_to_connect_to_credit_card_terminal')); ?> + "</span>");
				$("#status").html("<a class='btn btn-primary btn-lg m-b-20' href='<?php echo site_url('sales'); ?>'>&laquo; <?php echo lang('sales_register'); ?>");
			},
			cache: true,
			headers: { 'Invoke-Control': 'PDCX' }
		});
	});
});
</script>