<?php
if($export_excel == 1)
{
	//Clean all buffers
	while (ob_get_level())
	{
		ob_end_clean();
	}
	$rows = array();
	$row = array();
	foreach ($headers as $header) 
	{
		$row[] = strip_tags($header['data']);
	}
	
	$rows[] = $row;
	
	foreach($data as $datarow)
	{
		$row = array();
		foreach($datarow as $cell)
		{
			$row[] = str_replace('<span style="white-space:nowrap;">-</span>', '-', strip_tags($cell['data']));
		}
		$rows[] = $row;
	}
	$this->load->helper('spreadsheet');
	array_to_spreadsheet($rows, strip_tags($title) . '.'.($this->config->item('spreadsheet_format') == 'XLSX' ? 'xlsx' : 'csv'), true);
	exit;
}
?>
<?php $this->load->view("partial/header"); ?>
<div class="row">
	<?php foreach($summary_data as $name=>$value) { ?>
	    <div class="col-md-3 col-xs-12 col-sm-6 summary-data">
	        <div class="info-seven primarybg-info">
	            <div class="logo-seven hidden-print"><i class="ti-widget dark-info-primary"></i></div>
					
					<?php
					if($name == 'hours')
					{
		            echo str_replace(' ','&nbsp;', to_quantity($value));
		            echo '<p>'.lang('reports_'.$name).'</p>';
					}
					elseif(!is_numeric($value))
					{
		            echo $value;
		            echo '<p>'.lang('reports_'.$name).'</p>';						
					}
					else
					{
		            echo to_currency($value);
		            echo '<p>'.lang('reports_'.$name).'</p>';
					}
					?>
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
				<?php echo lang('reports_reports'); ?> - <?php echo $title ?>
				<small class="reports-range"><?php echo $subtitle ?></small>
			</div>
			<div class="panel-body">
				<div class="table-responsive">
					<table class="table table-bordered table-striped table-reports tablesorter" id="sortable_table">
						<thead>
							<tr>
								<?php foreach ($headers as $header) { ?>
								<th align="<?php echo $header['align'];?>"><?php echo $header['data']; ?></th>
								<?php } ?>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($data as $row) { ?>
							<tr>
								<?php foreach ($row as $cell) { ?>
								<td align="<?php echo $cell['align'];?>"><?php echo $cell['data']; ?></td>
								<?php } ?>
							</tr>
							<?php } ?>
						</tbody>
					</table>
				</div>
				<div class="text-center">
					<button class="btn btn-primary text-white hidden-print" id="print_button"  > <?php echo lang('common_print'); ?> </button>	
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


<script type="text/javascript" language="javascript">
function init_table_sorting()
{
	//Only init if there is more than one row
	if($('.tablesorter tbody tr').length >1)
	{
		$("#sortable_table").tablesorter(); 
	}
}
function print_report()
{
	window.print();
}
$(document).ready(function()
{
	
	init_table_sorting();
	
	var headIndex = 2;

	$("#sortable_table").stacktable({headIndex: headIndex});

	$('#print_button').click(function(e){
		e.preventDefault();
		print_report();
	});
});
</script>
<?php $this->load->view("partial/footer"); ?>