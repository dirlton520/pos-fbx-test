<?php $this->load->view("partial/header"); ?>

	<div class="container-fluid">
		
		<?php
		if ($this->input->get('error'))
		{
		?>
		<div class="alert alert-danger">
			<strong><?php echo H($this->input->get('error'));?></strong>
		</div>
		<?php
		}
		elseif ($this->input->get('success'))
		{
			?>
			<div class="alert alert-success">
				<strong><?php echo H($this->input->get('success'));?></strong>
			</div>
			<?php
		}
		?>
		<form action="" method="get">


		<div id="report_date_range_complex" class="col-sm-12 col-md-12 col-lg-12">
			<div class="row">
				<div class="col-md-5">
					<div class="input-group input-daterange" id="reportrange">
						<span class="input-group-addon bg date-picker"><?php echo lang('reports_from'); ?></span>
	             <input type="text" class="form-control start_date" name="start_date" id="start_date" value="<?php echo $start_date; ?>">
	        </div>
				</div>
	
				<div class="col-md-5">
					<div class="input-group input-daterange" id="reportrange1">
	        <span class="input-group-addon bg date-picker"><?php echo lang('reports_to'); ?></span>
	       <input type="text" class="form-control end_date" name="end_date" id="end_date" value="<?php echo $end_date; ?>">
	      	</div>	
				</div>
				
				<div class="col-md-2">
					<input type="submit" class="btn btn-primary" value="<?php echo lang('common_filter'); ?>">
				</div>
			</div>
		</div>

		</form>
		
		<div class="row manage-table">
			<div class="panel panel-piluku">
				<div class="panel-heading">
					<h3 class="panel-title hidden-print">
						 <?php echo lang('sales_list_of_batches'); ?>
					</h3>
				</div>
				<div class="panel-body nopadding table_holder table-responsive">
						<table class="table table-bordered table-striped table-hover data-table" id="dTable">
						<thead>
							
							<tr>
								<th><?php echo lang('sales_batch_id'); ?></th>
								<th><?php echo lang('common_amount'); ?></th>
								<th><?php echo lang('common_status'); ?></th>
								<th><?php echo lang('common_start_date'); ?></th>
								<th><?php echo lang('common_end_date'); ?></th>
								<th><?php echo lang('common_details'); ?></th>
							</tr>
						</thead>
						<tbody>
					<?php
					foreach ($batches as $batch)
					{
					?>
					<tr id="res_<?php echo $batch['batchId']; ?>">
							<td>
							<a href="#" id="<?php echo $batch['batchId']; ?>" class="expand" style="font-weight: bold;">+</a>
								<?php echo $batch['batchId'];?>
							</td>							
							<td><?php echo to_currency(make_currency_no_money($batch['capturedAmount']));?></td>							
							<td><?php echo $batch['open'] ? lang('common_open') : lang('common_closed');?></td>							
							<td><?php echo date(get_date_format().' '.get_time_format(),strtotime($batch['openDate']));?></td>							
							<td><?php echo date(get_date_format().' '.get_time_format(),strtotime($batch['closeDate']));?></td>		
							<td><?php echo anchor('#',lang('common_details'),array('class' => 'batch_details', 'data-batch-id' => $batch['batchId'])); ?></td>							
												
						</tr>
					<?php
					}
					?>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>
<?php $this->load->view("partial/footer"); ?>



<script type="text/javascript">
date_time_picker_field_report($('#start_date'), JS_DATE_FORMAT);
date_time_picker_field_report($('#end_date'), JS_DATE_FORMAT);			
	
var datatable = $('#dTable').dataTable({
	"sPaginationType": "bootstrap",
	"bSort" : true,
	"aaSorting": [],//Disable initial sort
	"iDisplayLength": 50,
	"aLengthMenu": <?php echo json_encode($length_dropdown); ?>
});

$("#dTable").on("click", ".batch_details", function(e){ 	
	e.preventDefault();
	var batch_id = $(this).data('batch-id');
	$.post(<?php echo json_encode(site_url('sales/batch_details')); ?>,{batch_id:batch_id},function(response)
	{
		var total_terminal_transactions = 0;
		if (response.volumeByTerminal.length)
		{
			for(var k=0;k<response.volumeByTerminal.length;k++)
			{
				var terminal = response.volumeByTerminal[k];
				total_terminal_transactions+=terminal.transactionCount;
			}
		}
		else
		{
			total_terminal_transactions = response.transactionCount;
		}
		var total_non_terminal_transactions = response.transactionCount - total_terminal_transactions;
		
		var info="";
		info+="<h3>Totals</h3>";
		
		info+="Captured Amount: "+response.capturedAmount;
		info+="<br />";
		info+="Terminal Transaction Count: "+response.transactionCount;
		info+="<br />";
		
		if (total_non_terminal_transactions !=0)
		{
			info+="Non Terminal Transaction Count: "+total_non_terminal_transactions;
			info+="<br />";
		}
		info+="Deposit Amount: "+response.expectedDeposit;
		info+="<br />";
		info+="<br />";
		
		if (response.volumeByTerminal.length)
		{
			info+="<h3>Per Register</h3>";
		
			for(var k=0;k<response.volumeByTerminal.length;k++)
			{
				var terminal = response.volumeByTerminal[k];
			
				info+="Terminal Name: "+terminal.terminalName;
				info+="<br />";
				info+="Captured Amount: "+terminal.capturedAmount;
				info+="<br />";
				info+="Transaction Count: "+terminal.transactionCount;
				info+="<br />";
				info+="<br />";
			}
		}
		bootbox.alert(info);
	},'json');
	
});


$(document).on('click','a.expand',function(event)
{
	$(event.target).parent().parent().next().find('td.innertable').toggle();
	
	if ($(event.target).text() == '+')
	{
		$(event.target).text('-');
		id=$(event.target).attr("id");
		show_batch_details(id);
	}
	else
	{
		$(event.target).text('+');
	}
	return false;
});

function show_batch_details(id){
	if(id){
		var url = '<?php echo site_url('sales/get_transastions_for_batch'); ?>';
		
		$.ajax({
			url: url,
			type: 'POST',
			data:{'batch_id':id},
			datatype: 'json',
			cache: false,
			success:function(data){
				var obj = JSON.parse(data);
				var headers = obj.headers;
				var cellData= obj.details_data;
				var res = '#res_'+id;
				var tableData='<tr id="res_'+id+'_detail"><td colspan="100" class="innertable"><table class="table table-bordered">';
				tableData+='<thead>';
				tableData+='<tr>';
				$.each(headers, function (k, v) {
					tableData += '<th align="'+ v.align + '">' + v.data + '</th>';					
				});
				tableData +='</tr></thead>';
				
				tableData+='<tbody>';
				
				for(var k=0;k<cellData.length;k++)
				{
					tableData+='<tr>';
					
					for(var j=0;j<cellData[k].length;j++)
					{
						var cell = cellData[k][j];
						tableData += '<td align="'+ cell.align + '">' + cell.data + '</td>';
					}
					tableData+='</tr>';
					
				}
				tableData+='</tbody>';
				tableData+='</table></td></tr>';
				
				$(res+'_detail').remove();
				$(res).after(tableData);
				
			
			},
			error: function(xhr, ajaxOptions, thrownError) {
			alert(thrownError);
			}
		});
	}
}


</script>