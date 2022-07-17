<?php $this->load->view("partial/header"); ?>
<div id="status"><?php echo lang('common_wait');?> <?php echo img(array('src' => base_url().'assets/img/ajax-loader.gif')); ?></div>

<div class="panel panel-piluku">
	<div class="panel-body">
	   <h4 id="title"><?php echo lang('sales_please_swipe_credit_card_on_machine');?></h4>
	</div>
</div>

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
				    processed_data.push({
						'name': m[0], 
					 	'value': m[1]
					 });
				}		
				
				$.ajax(SITE_URL+"/sales/set_sequence_no_emv", {
					type: 'POST',
					data: {sequence_no:processed_data.SequenceNo},
					success: function(data, textStatus, jqXHR)
					{
						post_submit('<?php echo site_url('sales/finish_cc_processing'); ?>', 'POST', processed_data);
					},
					error: function(jqXHR, textStatus, errorThrown)
					{
						post_submit('<?php echo site_url('sales/finish_cc_processing'); ?>', 'POST', processed_data);
					}			
				});
			},
			error: function()
			{
				$("#title").html("<span class='text-danger'> " + <?php echo json_encode(lang('sales_unable_to_connect_to_credit_card_terminal')); ?> + "</span>");
				$("#status").html("<a class='btn btn-primary btn-lg m-b-20' href='<?php echo site_url('sales'); ?>'>&laquo; <?php echo lang('sales_register'); ?>");
			},
			cache: true,
			headers: { 'Invoke-Control': '<?php echo $invoke_control;?>' }
		});
	}, function error()
	{
		var https_test_url = <?php echo json_encode(site_url('testing/server_side_https')); ?>;
		if (location.protocol !== 'https:')
		{			
			$.ajax(https_test_url,{
			method: 'get',
			success:function(json)
			{
				json = $.parseJSON(json);
				if (json.success)
				{
					bootbox.confirm(<?php echo json_encode(lang('sales_https_required_cc_redirect')); ?>,function(confirm_msg)
					{
						if (confirm_msg)
						{						
							window.location = <?php echo json_encode(site_url('sales','https')) ?>;
						}
					});			
				}
				else
				{
					bootbox.alert(<?php echo json_encode(lang('sales_https_required_cc')); ?>, function()
					{
						window.location = <?php echo json_encode(site_url('sales')) ?>;
					
					});
				}	
			},
			error:function() {
				
				bootbox.alert(<?php echo json_encode(lang('sales_https_required_cc')); ?>, function()
				{
					window.location = <?php echo json_encode(site_url('sales')) ?>;
					
				});
		   	}});
		}
	});
});
</script>