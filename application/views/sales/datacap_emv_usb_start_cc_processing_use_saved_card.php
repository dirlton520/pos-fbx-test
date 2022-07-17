<?php $this->load->view("partial/header"); ?>
<div id="please_wait"><?php echo lang('common_wait');?> <?php echo img(array('src' => base_url().'assets/img/ajax-loader.gif')); ?></div>
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
		$("#formCheckout").ajaxSubmit({
			success:function(response)
			{
				var data = response.split("&");
				var processed_data = [];

				for(var i = 0; i < data.length; i++)
				{
				    var m = data[i].split("=");
				    processed_data.push({
						'name': m[0], 
					 	'value': m[1]
					 });
				}
				
				post_submit('<?php echo site_url('sales/finish_cc_processing_saved_card'); ?>', 'POST', processed_data);					
			},
			error: function()
			{
				$("#title").html(<?php echo json_encode(lang('sales_unable_to_connect_to_credit_card_terminal')); ?>);
				$("#status").html("<a href='<?php echo site_url('sales'); ?>'>&laquo; <?php echo lang('sales_register'); ?>");
			},
			cache: true,
			headers: { 'Invoke-Control': 'PDCX'}
		});
	});
</script>