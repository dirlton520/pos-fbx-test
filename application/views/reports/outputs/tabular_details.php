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
	    <div class="col-md-3 col-xs-12 col-sm-6 summary-data">
	        <div class="info-seven primarybg-info">
	            <div class="logo-seven hidden-print"><i class="ti-widget dark-info-primary"></i></div>
	            <?php 
							
							if($name == 'total_items_in_inventory' || $name == 'number_items_counted')
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
						<tr>
							<th><a href="#" class="expand_all" >+</a></th>
							<?php foreach ($headersshow as $header) { ?>
							<th align="<?php echo $header['align']; ?>" class="colsho <?php echo $header['column_id']; ?>" style="<?php if($header['view'] == 0) { ?>display:none;<?php } ?>"><?php echo $header['data']; ?></th>
							<?php } ?>
						
						</tr>
					</thead>
					<tbody>
						<?php foreach ($summary_data as $key=>$row) { ?>
						<tr>
							<?php if (isset($details_data[$key])) {?>
							<td><a href="#" class="expand" style="font-weight: bold;">+</a></td>
							<?php } else { ?>
								<td>&nbsp;</td>
							<?php } ?>
							<?php foreach ($row as $cell) { ?>
							<td align="<?php echo $cell['align']; ?>"><?php echo $cell['data']; ?></td>
							<?php } ?>
						</tr>
						<?php
						if (isset($details_data[$key]))
						{
						?>
						<tr>
							<td colspan="100" class="innertable" style="display:none;">
								<table class="table table-bordered">
									<thead>
										<tr>
											<?php foreach ($headers['details'] as $header) { ?>
											<th align="<?php echo $header['align']; ?>"><?php echo $header['data']; ?></th>
											<?php } ?>
										</tr>
									</thead>

									<tbody>
										<?php foreach ($details_data[$key] as $row2) { ?>
											<tr>
												<?php foreach ($row2 as $cell) { ?>
												<td align="<?php echo $cell['align']; ?>"><?php echo $cell['data']; ?></td>
												<?php } ?>
											</tr>
										<?php } ?>
									</tbody>
								</table>
							</td>
						</tr>
						<?php
						}
						?>
						<?php } ?>
					</tbody>
				</table>
				</div>
				<div class="text-center">
					<button class="btn btn-primary text-white hidden-print print_button"> <?php echo lang('common_print'); ?> </button>	
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
$(document).ready(function()
{
	$('.print_button').click(function(e){
		e.preventDefault();
		print_report();
	});
});

function init_table_sorting()
{
	//Only init if there is more than one row
	if($('.tablesorter tbody tr').length >1)
	{
		$("#sortable_table").tablesorter(); 
	}
}

<?php if (empty($details_data)) { ?>
$(document).ready(function()
{
	init_table_sorting();
});
<?php } ?>

</script>