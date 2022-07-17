
<div class="row hidden-print">
	
	<div class="col-md-12">
		
		<div class="panel panel-piluku">
			
			<div class="panel-heading report-options">
				<?php echo $input_report_title; ?>
				<?php if (isset($output_data) && $output_data) { ?>
							<div class="table_buttons pull-right">
								<button type="button" class="btn btn-more expand-collapse" data-toggle="dropdown" aria-expanded="false"><i id="expand-collapse-icon" class="ion-chevron-down"></i></button>
							</div>
				<?php } ?>
			</div>
			<div id="options" class="panel-body" <?php echo (isset($output_data) && $output_data) ? 'style="display:none;"' : ''; ?>>
				
				<form class="form-horizontal form-horizontal-mobiles" id="report_input_form" method="get" action="<?php echo site_url('reports/generate/'.$report); ?>">
					<?php 
					$this->load->helper('view');
					foreach($input_params as $input_param) 
					{
							load_cleaned_view('reports/inputs/'.$input_param['view'],$input_param);
					} 
					?>
					
				</form>
			</div>
		</div>
</div>
</div>
<script>
$('#generate_report').click(function(e){
     e.preventDefault();
     $('#options').slideToggle(function() {
     	$('#report_input_form').submit();
     });
 });

</script>