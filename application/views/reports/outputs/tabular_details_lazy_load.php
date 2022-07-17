<?php
$company = ($company = $this->Location->get_info_for_key('company')) ? $company : $this->config->item('company');

if($export_excel == 1)
{
	$this->load->view('reports/outputs/tabular_details_excel_export');
}
?>
<div class="modal fade skip-labels" id="skip-labels" role="dialog" aria-labelledby="skipLabels" aria-hidden="true">
    <div class="modal-dialog customer-recent-sales">
      	<div class="modal-content">
	        <div class="modal-header">
	          	<button type="button" class="close" data-dismiss="modal" aria-label=<?php echo json_encode(lang('common_close')); ?>><span aria-hidden="true">&times;</span></button>
	          	<h4 class="modal-title" id="skipLabels"><?php echo lang('common_skip_labels') ?></h4>
	        </div>
	        <div class="modal-body">
				
	          	<?php echo form_open("items/generate_barcodes", array('id'=>'generate_barcodes_form','autocomplete'=> 'off')); ?>				
				<input type="text" class="form-control text-center" name="num_labels_skip" id="num_labels_skip" placeholder="<?php echo lang('common_skip_labels') ?>">
					<?php echo form_submit('generate_barcodes_form',lang("common_submit"),'class="btn btn-block btn-primary"'); ?>
				<?php echo form_close(); ?>
				
	        </div>
    	</div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<div class="row">
	<?php foreach($overall_summary_data as $name=>$value) { ?>
	    <div class="col-md-3 col-xs-12 col-sm-6 ">
	        <div class="info-seven primarybg-info">
	            <div class="logo-seven hidden-print"><i class="ti-widget dark-info-primary"></i></div>
	            <?php 
							
							if($name == 'number_items_counted' || $name == 'points_used' || $name =='points_gained')
							{
								
								echo to_quantity($value);								
							}
							else
							{
								echo to_currency($value);
	            
							}
							?>
							<p><?php echo lang('reports_'.$name); ?></p>
	        </div>
	    </div>
	<?php }?>
</div>

<?php if(isset($pagination) && $pagination) {  ?>
	<div class="pagination hidden-print alternate text-center" id="pagination_top" >
		<?php echo $pagination;?>
	</div>
<?php }  ?>
	
	
<div class="row">
	<div class="col-md-12">
		<div class="panel panel-piluku reports-printable">
			<div class="panel-heading">
				<form id="config_columns" class="report-config hidden-print">
				<div class="piluku-dropdown btn-group table_buttons pull-right m-left-20">
				<input type="hidden" name="url_segment" id="url_segment" value="<?php echo $this->uri->segment(3); ?>">
					<button type="button" class="btn btn-more dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
						<i class="ion-gear-a"></i>
					</button>
						<ul id="" class="dropdown-menu dropdown-menu-left col-config-dropdown" role="menu">
							<li class="dropdown-header"><a id="reset_to_default" class="pull-right"><span class="ion-refresh"></span> Reset</a><?php echo lang('common_column_configuration'); ?></li>
																
							<?php $i = 0; foreach($headersshow as $col_key) {
								$checked = '';
								if($col_key['view'] == 1) {
									$checked = 'checked ="checked" ';
								}
								?>
								<li class="col<?php echo $i; ?>"><a><input <?php echo $checked; ?> name="selected_columns[]" type="checkbox" class="columns" id="<?php echo $col_key['column_id']; ?>" value="<?php echo $col_key['column_id']; ?>"><label class="sortable_column_name" for="<?php echo $col_key['column_id']; ?>"><span></span><?php echo H($col_key['data']); ?></label><span class=""></span></a></li>									
							<?php } ?>
						</ul>
				</div>
				</form>
				<?php echo lang('reports_reports'); ?> - <?php echo $company; ?> <?php echo $title ?>
				<small class="reports-range"><?php echo $subtitle ?></small>
				<br /><small class="reports-range"><?php echo lang('reports_generation_date').' '.date(get_date_format().' '.get_time_format()); ?></small>
				<button class="btn btn-primary text-white hidden-print print_button pull-right"> <?php echo lang('common_print'); ?> </button>	
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
							<?php foreach ($headersshow as $header) { ?>
							<td align="<?php echo $header['align']; ?>" class="colsho <?php echo $header['column_id']; ?>" style="<?php if($header['view'] == 0) { ?>display:none;<?php } ?>"><?php echo $header['data']; ?></td>
							<?php } ?>
						
						</tr>
					</thead>
					<tbody>
						<?php 
						$ids=array();
						foreach ($summary_data as $key=>$row) { 
						$ids[]=$row[0]['detail_id'];
						?>
						<tr>
							<td class="hidden-print"><a href="#" id="<?php echo $row[0]['detail_id']; ?>" class="expand" style="font-weight: bold;">+</a></td>
							<?php foreach ($row as $cell) { ?>
							<td align="<?php echo $cell['align']; ?>"><?php echo $cell['data']; ?></td>
							<?php } ?>
						</tr>
						<tr class="sale_details" id="res_<?php echo $row[0]['detail_id']; ?>" style="display:none;">
						</tr>
						<?php } 
						$ids=implode(',',$ids);
						?>
					</tbody>
				</table>
				</div>
				<div class="text-center">
					<button class="btn btn-primary text-white hidden-print print_button"  > <?php echo lang('common_print'); ?> </button>	
				</div>
			</div>
		</div>
	</div>
</div>
	
	<?php if(isset($pagination) && $pagination) {  ?>
		<div class="pagination hidden-print alternate text-center" id="pagination_top" >
			<?php echo $pagination;?>
		</div>
	<?php }  ?>
</div>
<?php 
foreach ($headersshow as $header) { 
	if($header['view'] == 0) {
?>
<script>
	var $th = $(".<?php echo $header['column_id']; ?>");
	var $td = $th.closest('table').find('td:nth-child('+($th.index()+1)+')');
	$th.hide();
	$td.hide();
	$(".innertable td").show();
	
</script>
<?php 
	}
}
?>

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
			var url = '<?php echo site_url('reports/get_report_details'); ?>';
			var params = <?php echo json_encode($this->input->get());?>;
			
            var ids = ids.split(',');
			$.ajax({
                url: url,
				type: 'POST',
				data:{'ids':ids,'key':report_model, params:JSON.stringify(params)},
				datatype: 'json',
				cache: false,
				success:function(data){
				
				var obj = JSON.parse(data);
				var headers = obj.headers['details'];
				var summary = obj.headers['summary'];
				var cellData= obj.details_data;

				for (i = 0; i < ids.length; i++) { 
					
					var res = '#res_'+ids[i];
					
					var tableData='<td colspan="100" class="innertable"><table class="table table-bordered">';
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
	$('.print_button').click(function(e){
		e.preventDefault();
		print_report();
	});
});

</script>