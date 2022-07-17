<div class="row">
	<div class="col-md-12">
	<?php if(isset($pagination) && $pagination) {  ?>
		<div class="pagination hidden-print alternate text-center" id="pagination_top" >
			<?php print_r($pagination);?>
		</div>
	<?php }  ?>
		<div class="panel panel-piluku reports-printable">
			<div class="panel-heading">
				<?php echo lang('reports_reports'); ?> - <?php echo $title ?>
				<small class="reports-range"><?php echo $subtitle ?></small>
				<br /><small class="reports-range"><?php echo lang('reports_generation_date').' '.date(get_date_format().' '.get_time_format()); ?></small>
				<?php if($key) { ?>
					<a href="<?php echo site_url("reports/delete_saved_report/".$key);?>" class="btn btn-primary text-white hidden-print delete_saved_report pull-right"> <?php echo lang('reports_unsave_report'); ?></a>	
				<?php } else { ?>
					<button class="btn btn-primary text-white hidden-print save_report_button pull-right" data-message="<?php echo H(lang('reports_enter_report_name'));?>"> <?php echo lang('reports_save_report'); ?></button>
				<?php } ?>				
				
			</div>
			<div class="panel-body">
				<div class="table-responsive">
				<table class="table table-hover detailed-reports table-reports table-bordered  tablesorter" id="sortable_table">
					<thead>
						<tr align="center" style="font-weight:bold">
							<td class="hidden-print"><a href="#" class="expand_all" >+</a></td>
							<?php foreach ($headers['summary'] as $header) { ?>
							<td align="<?php echo $header['align']; ?>"><?php echo $header['data']; ?></td>
							<?php } ?>
						
						</tr>
					</thead>
					<tbody>
						<?php 
						//echo '<pre>'; print_r ($summary_data); echo '</pre>'; exit;
						$ids=array();
						foreach ($summary_data as $key=>$row) { 
						
						$ids[]=$row[0]['sale_id'];
						?>
						<tr>
							<td class="hidden-print"><a href="#" id="<?php echo $row[0]['sale_id']; ?>" class="expand" style="font-weight: bold;">+</a></td>
							<?php foreach ($row as $cell) { ?>
							<td align="<?php echo $cell['align']; ?>"><?php echo $cell['data']; ?></td>
							<?php } ?>
						</tr>
						<tr class="sale_details" id="res_<?php echo $row[0]['sale_id']; ?>" style="display:none;">
						</tr>
						<?php } 
						$ids=implode(',',$ids);
						?>
					</tbody>
				</table>
				</div>
				<div id="report_summary" class="tablesorter pull-right report report-sumary">
					<?php foreach($overall_summary_data as $name=>$value) { ?>
						<div class="summary_row">
							<span class="name"><?php echo lang('reports_'.$name) ?></span>
							<span class="value"><?php echo to_currency($value) ?></span>
						</div>
					<?php }?>
				</div>
			</div>
		</div>
		<?php if(isset($pagination) && $pagination) {  ?>
			<div class="pagination hidden-print alternate text-center" id="pagination_top" >
				<?php echo $pagination;?>
			</div>
		<?php }  ?>
	</div>
</div>

<script type="text/javascript" language="javascript">
var base_sheet_url = '';
$(document).ready(function()
{
	$(".tablesorter a.expand").click(function(event)
	{
		$(event.target).parent().parent().next().find('td.innertable').toggle();
		
		if ($(event.target).text() == '+')
		{
			$(event.target).text('-');
			id=$(event.target).attr("id");
			show_report_details(id);
		}
		else
		{
			$(event.target).text('+');
		}
		return false;
	});
	
	$(".tablesorter a.expand_all").click(function(event)
	{
		$('td.innertable').toggle();
		
		if ($(event.target).text() == '+')
		{
			$(event.target).text('-');
			$(".tablesorter a.expand").text('-');
			
			ids='<?php echo $ids; ?>';
				show_report_details(ids);
			
		}
		else
		{
			$(event.target).text('+');
			$(".tablesorter a.expand").text('+');
		}
		return false;
	});
	
	$(".generate_barcodes_from_recv").click(function()
	{
		base_sheet_url = $(this).attr('href');
		$("#skip-labels").modal('show');
		return false;
	
	});
		
	$("#generate_barcodes_form").submit(function(e)
	{
		e.preventDefault()
		var num_labels_skip = $("#num_labels_skip").val() ? $("#num_labels_skip").val() : 0;
		var url = base_sheet_url+'/'+num_labels_skip;
		window.location = url;
		return false;
	});
});

function print_report()
{
	window.print();
}

function show_report_details(ids){
        if(ids){
            var report_model = '<?php echo $report_model; ?>';
			var params=<?php echo json_encode($params);?>;
			var url = '<?php echo site_url('reports/get_report_details_sales_generator'); ?>';
            var ids = ids.split(',');
			$.ajax({
                url: url,
				type: 'POST',
				data:{'ids':ids,'key':report_model,'params':JSON.stringify(params)},
				datatype: 'json',
				cache: false,
				success:function(data){
				
				var obj = JSON.parse(data);
				var headers = obj.headers['details'];
				var cellData= obj.details_data;
				var summary= obj.headers['summary'];
				for (i = 0; i < ids.length; i++) { 
					
					var res = '#res_'+ids[i];
					
					var tableData='<td colspan="' + (summary.length+1) +'" class="innertable"><table class="table table-bordered">';
					tableData+='<thead>';
					tableData+='<tr>';
					$.each(headers, function (k, v) {
						tableData += '<th align="'+ v.align + '">' + v.data + '</th>';					
					});
					tableData +='</tr></thead>';
					
					tableData+='<tbody>';
					$.each(cellData, function (x) {
					var transData= cellData[x];
						$.each(transData, function (key, value){
							var rowId=key;
							var rowData=value;
							if(rowId == ids[i])
							{
								tableData+='<tr>';
								$.each(rowData, function (a,b) {
									if(b.data == null){b.data='';}
									tableData += '<td align="'+ b.align + '">' + b.data + '</td>';					
								});
								tableData+='</tr>';
								
							}
						
						});
						
					});
					tableData+='</tbody>';
					tableData+='</table></td>';
					
					//document.getElementById(res).innerHTML = "";
					$(res).empty();
					$(res).append(tableData);
					$(res).css('display','');
				}
				
				},
				error: function(xhr, ajaxOptions, thrownError) {
				alert(thrownError);
				}
				
               
            });
        }
    }

$(document).ready(function()
{
	$('#print_button').click(function(e){
		e.preventDefault();
		print_report();
	});
});
</script>