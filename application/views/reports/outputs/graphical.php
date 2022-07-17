<?php
$company = ($company = $this->Location->get_info_for_key('company')) ? $company : $this->config->item('company');
?>
<div class="row">
	<?php foreach($summary_data as $name=>$value) { ?>
	    <div class="col-md-3 col-xs-12 col-sm-6 summary-data">
	        <div class="info-seven primarybg-info">
	            <div class="logo-seven"><i class="ti-widget dark-info-primary"></i></div>
							
							<?php
							if( $name == 'sales_per_time_period')
							{
				            echo str_replace(' ','&nbsp;', to_quantity($value));
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

<div class="row">
	<div id="report_summary"  class="repors-summarys col-md-12 ">
		<div class="panel panel-piluku">
			<div class="panel-heading">
				<?php echo lang('reports_reports'); ?> - <?php echo $company; ?> <?php echo $title ?>
				<?php if($key) { ?>
					<a href="<?php echo site_url("reports/delete_saved_report/".$key);?>" class="btn btn-primary text-white hidden-print delete_saved_report pull-right"> <?php echo lang('reports_unsave_report'); ?></a>	
				<?php } else { ?>
					<button class="btn btn-primary text-white hidden-print save_report_button pull-right" data-message="<?php echo H(lang('reports_enter_report_name'));?>"> <?php echo lang('reports_save_report'); ?></button>
				<?php } ?>
			</div>
			<div class="panel-body">
				<div id="chart_wrapper">
					<div id="chart-legend" class="chart-legend"></div>
					<canvas id="chart"></canvas>
				</div>
			</div>
		</div>
	</div>
	</div>
</div>

<script type="text/javascript">
	<?php $this->load->view('reports/outputs/graphs/'.$graph); ?>
</script>