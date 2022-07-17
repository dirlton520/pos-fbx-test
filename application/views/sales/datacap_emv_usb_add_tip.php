<form id="formCheckout" method="post" action="<?php echo $form_url; ?>">
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
		<?php echo form_hidden('TranCode', 'AdjustByRecordNo');?>
		<?php echo form_hidden('Frequency', 'OneTime');?>
		<?php echo form_hidden('InvoiceNo', $transaction['InvoiceNo']);?>
		<?php echo form_hidden('RefNo', $transaction['RefNo']);?>
		<?php echo form_hidden('RecordNo', $transaction['RecordNo']);?>
		<?php echo form_hidden('AuthCode', $transaction['AuthCode']);?>
		<?php echo form_hidden('Purchase', $transaction['Purchase']);?>
		<?php echo form_hidden('Gratuity', $transaction['Gratuity']);?>
		<?php echo form_hidden('AcqRefData', $transaction['AcqRefData']);?>
		<?php echo form_hidden('InvokeControl', $transaction['InvokeControl']);?>
		<?php
		if ($TerminalID)
		{
			echo form_hidden('TerminalID', $TerminalID);
		}
		?>
		<?php if ($transaction['ProcessData']) { ?>
			<?php echo form_hidden('ProcessData', $transaction['ProcessData']);?>
		<?php } ?>
		<?php echo form_hidden('SequenceNo', $SequenceNo);?>
	</form>
<?php
?>
<script>
delete $.ajaxSettings.headers["cache-control"];

add_tip_request();

var sale_void_success = true;

function add_tip_request()
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
			
			if (processed_data.CmdStatus != 'Approved')
			{
				sale_void_success = false;
				show_feedback('error',<?php echo json_encode(lang('sales_cannot_add_tip'));?>,<?php echo json_encode(lang('common_error')); ?>);			
			}
			else
			{
				show_feedback('success',<?php echo json_encode(lang('sales_tip_added_successfully'));?>,<?php echo json_encode(lang('sales_tip_added_successfully')); ?>);			
			}
					
			$.post(SITE_URL+"/sales/set_sequence_no_emv", {sequence_no:processed_data.SequenceNo}, function()
			{
				$("#formCheckout").find('input[name=SequenceNo]').val(processed_data.SequenceNo);
			});
		},
		error: function()
		{
			show_feedback('error',<?php echo json_encode(lang('sales_cannot_add_tip'));?>,<?php echo json_encode(lang('common_error')); ?>);			
		},
		cache: true,
		headers: { 'Invoke-Control': $("#formCheckout").find('input[name=InvokeControl]').val() }
	});
}

</script>