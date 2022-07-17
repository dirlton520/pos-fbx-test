<?php
$this->load->view("partial/header");
?>
<h1 id="success_message" class="text-warning text-center" style="display: none;"><?php echo lang('sales_delete_successful'); ?></h1>
<h1 id="error_message" class="text-error" style="display: none;"><?php echo lang('sales_delete_unsuccessful'); ?></h1>
<div id="please_wait"><?php echo lang('common_wait');?> <?php echo img(array('src' => base_url().'assets/img/ajax-loader.gif')); ?></div>

<?php
for ($k = 0;$k<count($transactions);$k++)
{
?>
	<form id="formCheckout_<?php echo $k; ?>" method="post" action="<?php echo $form_url; ?>">
		<?php echo form_hidden('HostOrIP', $HostOrIP);?>
		<?php echo form_hidden('IpPort', $IpPort);?>
		<?php echo form_hidden('MerchantID', $MerchantID);?>
		<?php echo form_hidden('ComPort', $ComPort);?>
		<?php echo form_hidden('TStream', 'Transaction');?>
		<?php echo form_hidden('SecureDevice', $SecureDevice);?>
		<?php echo form_hidden('Memo', $Memo);?>
		<?php echo form_hidden('LaneID', $LaneID);?>
		<?php echo form_hidden('OperatorID', $OperatorID);?>
		<?php echo form_hidden('TranType', 'Credit');?>
		<?php echo form_hidden('TranCode', 'VoidReturnByRecordNo');?>
		<?php echo form_hidden('Frequency', 'OneTime');?>
		<?php echo form_hidden('InvoiceNo', $transactions[$k]['InvoiceNo']);?>
		<?php echo form_hidden('RefNo', $transactions[$k]['RefNo']);?>
		<?php echo form_hidden('RecordNo', $transactions[$k]['RecordNo']);?>
		<?php echo form_hidden('InvokeControl', $transactions[$k]['InvokeControl']);?>
		<?php
		if ($TerminalID)
		{
			echo form_hidden('TerminalID', $TerminalID);
		}
		?>
		<?php if ($transactions[$k]['AuthCode']) { ?>
			<?php echo form_hidden('AuthCode', $transactions[$k]['AuthCode']);?>
		<?php } ?>
		
		<?php echo form_hidden('Purchase', $transactions[$k]['Purchase']);?>
		<?php echo form_hidden('AcqRefData', $transactions[$k]['AcqRefData']);?>
		<?php if ($transactions[$k]['ProcessData']) { ?>
			<?php echo form_hidden('ProcessData', $transactions[$k]['ProcessData']);?>
		<?php } ?>
		
		<?php echo form_hidden('SequenceNo', $SequenceNo);?>			
	</form>
<?php
}
?>
<script>
delete $.ajaxSettings.headers["cache-control"];

var num_return_transactions_to_void = <?php echo count($transactions); ?>;
var max_index = num_return_transactions_to_void - 1;

if (num_return_transactions_to_void > 0)
{
	void_return_request(0);
}

var sale_void_success = true;

function void_return_request(index)
{	
	if (index > max_index)
	{
		<?php
		if(isset($sale_id))//Delete sale
		{
		?>
			$("#please_wait").hide();
			
			if (sale_void_success)
			{
				$.getJSON('<?php echo site_url("sales/delete_sale_only/$sale_id");?>', function(response) 
				{
					if(response.success)
					{
						show_feedback('success',response.message,<?php echo json_encode(lang('common_success')); ?>);			
						$("#success_message").show();
					}
					else
					{
						show_feedback('error',response.message,<?php echo json_encode(lang('common_error')); ?>);			
						$("#error_message").show();
					}
				});
			}
			else
			{
				$("#error_message").show();				
			}
			
		<?php	
		}
		?>
		return;
	}
	
	$("#formCheckout_"+index).ajaxSubmit({
		success:function(response)
		{
			var data = response.split("&");
			var processed_data = [];

			for(var i = 0; i < data.length; i++)
			{
			    var m = data[i].split("=");
			    processed_data[m[0]] = m[1];
			}			
						
			if (processed_data.CmdStatus != 'Approved')
			{
				sale_void_success = false;
				show_feedback('error',<?php echo json_encode(lang('sales_attempted_to_reverse_transactions_failed_please_contact_support'));?>,<?php echo json_encode(lang('common_error')); ?>);			
			}
			else
			{
				show_feedback('success',<?php echo json_encode(lang('sales_sale_deleted_and_voided'));?>,<?php echo json_encode(lang('common_success')); ?>);			
				
			}
						
			$.post(SITE_URL+"/sales/set_sequence_no_emv", {sequence_no:processed_data.SequenceNo}, function()
			{
				$("#formCheckout_"+index+1).find('input[name=SequenceNo]').val(processed_data.SequenceNo);
				void_return_request(index + 1);
			});
		},
		error: function()
		{
			show_feedback('error',<?php echo json_encode(lang('sales_attempted_to_reverse_transactions_failed_please_contact_support'));?>,<?php echo json_encode(lang('common_error')); ?>);			
		},
		cache: true,
		headers: { 'Invoke-Control': $("#formCheckout_"+index).find('input[name=InvokeControl]').val() }
	});
}

</script>

<?php $this->load->view("partial/footer");?>