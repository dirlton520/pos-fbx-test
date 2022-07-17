<?php $this->load->view("partial/header"); 
$this->load->helper('demo');
?>

		<?php
		if(isset($announcement))
		{
		?>
     <div class="text-center">
				<?php echo $announcement; ?>
			</div>
		<?php
		}
		?>


		<?php
		if (is_on_phppos_host()) {
		?>
			<?php if (isset($trial_on) && $trial_on === true) { ?>
				<div class="col-md-12">
					<div class="panel">
						<div class="panel-body">					
						   <div class="alert alert-success">
						    <?php echo lang('login_trail_info'). ' '.date(get_date_format(), strtotime($cloud_customer_info['trial_end_date'])).'. '.lang('login_trial_info_2'); ?>
						    </div>
						    <a class="btn btn-block btn-success" href="https://phppointofsale.com/update_billing.php?store_username=<?php echo $cloud_customer_info['username'];?>&username=<?php echo $this->Employee->get_logged_in_employee_info()->username; ?>&password=<?php echo $this->Employee->get_logged_in_employee_info()->password; ?>" target="_blank"><?php echo lang('common_update_billing_info');?></a>
							</div>
						</div>
					</div>
			<?php } ?>


			<?php if (isset($subscription_payment_failed) && $subscription_payment_failed === true) { ?>
				<div class="col-md-12">
					<div class="panel">
						<div class="panel-body">
						   <div class="alert alert-danger">
						        <?php echo lang('login_payment_failed_text'); ?>
						    </div>
						    <a class="btn btn-block btn-success" href="https://phppointofsale.com/update_billing.php?store_username=<?php echo $cloud_customer_info['username'];?>&username=<?php echo $this->Employee->get_logged_in_employee_info()->username; ?>&password=<?php echo $this->Employee->get_logged_in_employee_info()->password; ?>" target="_blank"><?php echo lang('common_update_billing_info');?></a>
							</div>
						</div>
					</div>
			<?php } ?>

			<?php if (isset($subscription_cancelled_within_5_days) && $subscription_cancelled_within_5_days === true) { ?>
				<div class="col-md-12">
					<div class="panel">
						<div class="panel-body">
						    <div class="alert alert-danger">
						        <?php echo lang('login_resign_text'); ?>
						    </div>
							<a class="btn btn-block btn-sm btn-success" href="https://phppointofsale.com/update_billing.php?store_username=<?php echo $cloud_customer_info['username'];?>&username=<?php echo $this->Employee->get_logged_in_employee_info()->username; ?>&password=<?php echo $this->Employee->get_logged_in_employee_info()->password; ?>" target="_blank"><?php echo lang('login_resignup');?></a>
						</ul>
					</div>
				</div>
			</div>
			<?php } ?>
		<?php } ?>

	<?php if (isset($can_show_setup_wizard) && $can_show_setup_wizard) { ?>
		
		<style>

			#setup_wizard .col-md-2 {
			    text-align: center;
			}
			#setup_wizard p {
			    height: 33px;
			}
			.wizard_step_done img {
			    opacity: 0.4;
			}
			.wizard_step_done .btn-info {
			    color: #fff;
			    background-color: #4c4c4c;
			    border-color: #4c4c4c;
			    width: 50%;
			}
			#setup_wizard .btn-info {
			    width: 50%;
			}
			.wizard_step_done {
			  text-decoration: line-through;
			}
			
			</style>
		<div class="row" id="setup_wizard_container">
		<div class="col-md-12">
			<div class="panel">
				<div class="panel-body">
					<a id="dismiss_setup_wizard" href="<?php echo site_url('home/dismiss_setup_wizard') ?>" class="pull-right text-danger"><?php echo lang('common_dismiss'); ?></a>
					<h4><?php echo lang('home_setup_wizard');?></h4>
					<hr />

				    <div id="setup_wizard">
					    <div class="col-md-2 <?php echo $this->config->item('wizard_configure_company') ? 'wizard_step_done' : '';?>">
							<img src="<?php echo base_url('assets/img/gear.png') ?>"/>
							<h4><?php echo lang('module_config');?></h4>
							<p><?php echo lang('home_wizard_configure_company');?></p>
							<span><?php echo anchor('config',lang('common_go').' &raquo;', array('class' => 'btn btn-info',' style' => 'margin-left: 10px;'));?></span>
						</div>
						<div class="col-md-2 <?php echo $this->config->item('wizard_configure_locations') ? 'wizard_step_done' : '';?>">
							<img src="<?php echo base_url('assets/img/building.png') ?>"/>
							<h4><?php echo lang('module_locations');?></h4>
							<p><?php echo lang('home_wizard_configure_locations');?></p>
							<span><?php echo anchor('locations',lang('common_go').' &raquo;', array('class' => 'btn btn-info',' style' => 'margin-left: 10px;'));?></span>
						</div>
						<div class="col-md-2 <?php echo $this->config->item('wizard_add_inventory') ? 'wizard_step_done' : '';?>">
							<img src="<?php echo base_url('assets/img/product.png') ?> "/>
							<h4><?php echo lang('module_items');?></h4>
							<p><?php echo lang('home_wizard_add_inventory');?></p>
							<span><?php echo anchor('items/view/-1',lang('common_go').' &raquo;', array('class' => 'btn btn-info',' style' => 'margin-left: 10px;'));?></span>
						</div>
						<div class="col-md-2 <?php echo $this->config->item('wizard_edit_employees') ? 'wizard_step_done' : '';?>">
							<img src="<?php echo base_url('assets/img/user-group-man-man.png') ?>"/>
							<h4><?php echo lang('module_employees');?></h4>
							<p><?php echo lang('home_wizard_edit_employees');?></p>
							<span><?php echo anchor('employees',lang('common_go').' &raquo;', array('class' => 'btn btn-info',' style' => 'margin-left: 10px;'));?></span>
						</div>
						<div class="col-md-2 <?php echo $this->config->item('wizard_add_customer') ? 'wizard_step_done' : '';?>">
							<img src="<?php echo base_url('assets/img/add-user-group-man-man.png') ?>"/>
							<h4><?php echo lang('module_customers');?></h4>
							<p><?php echo lang('home_wizard_add_customer');?></p>
							<span><?php echo anchor('customers/view/-1',lang('common_go').' &raquo;', array('class' => 'btn btn-info',' style' => 'margin-left: 10px;'));?></span>
						</div>
						<div class="col-md-2 <?php echo $this->config->item('wizard_create_sale') ? 'wizard_step_done' : '';?>">
							<img src="<?php echo base_url('assets/img/cash-register.png') ?>"/>
							<h4><?php echo lang('module_sales');?></h4>
							<p><?php echo lang('home_wizard_create_sale');?></p>
							<span><?php echo anchor('sales',lang('common_go').' &raquo;', array('class' => 'btn btn-info',' style' => 'margin-left: 10px;'));?></span>
						</div>

						
							
				    </div>
				</ul>
			</div>
		</div>
	</div>
</div>
	<?php } ?>
	
	
	<?php if (!is_on_demo_host() && $can_show_reseller_promotion) { ?>
	
	<div class="row " id="reseller_container">
		<div class="col-md-12">
			<div class="panel">
				<div class="panel-body">
					<a id="dismiss_reseller" href="<?php echo site_url('home/dismiss_reseller_message') ?>" class="pull-right text-danger"><?php echo lang('common_dismiss'); ?></a>
					<div id="reseller_activate_container">
						<h3><a href="https://phppointofsale.com/resellers_signup.php" target="_blank"><?php echo lang('home_resellers_program'); ?></a></h3>
						<p><?php echo lang('home_reseller_program_signup')?></p>
						<a href="https://phppointofsale.com/resellers_signup.php" class="reseller_description btn btn-primary" target="_blank">
							<?php echo lang('home_signup_now');?>
						</a>
					</div>
				</div>
			</div>
		</div>
	</div>
	<?php } ?>
	
	<?php if (!is_on_demo_host() && $can_show_feedback_promotion) { ?>
	
	<div class="row " id="feedback_container">
		<div class="col-md-12">
			<div class="panel">
				<div class="panel-body">
					<a id="dismiss_feedback" href="<?php echo site_url('home/dismiss_feedback_message') ?>" class="pull-right text-danger"><?php echo lang('common_dismiss'); ?></a>
					<div id="reseller_activate_container">
						<h3><a href="https://feedback.phppointofsale.com" target="_blank"><?php echo lang('home_feedback'); ?></a></h3>
						<p><?php echo lang('home_feedback_program')?></p>
						<a href="https://feedback.phppointofsale.com" class="reseller_description btn btn-primary" target="_blank">
							<?php echo lang('home_visit_now');?>
						</a>
					</div>
				</div>
			</div>
		</div>
	</div>
	<?php } ?>
	
	

<?php if ($can_show_mercury_activate) { ?>
	<!-- mercury activation message -->
	<div class="row " id="mercury_container">
		<div class="col-md-12">
			<div class="panel">
				<div class="panel-body">
					<a id="dismiss_mercury" href="<?php echo site_url('home/dismiss_mercury_message') ?>" class="pull-right text-danger"><?php echo lang('common_dismiss'); ?></a>
					<div id="mercury_activate_container">
						<h3><a href="http://phppointofsale.com/credit_card_processing.php" target="_blank"><?php echo lang('common_credit_card_processing'); ?></a></h3>
						<a href="http://phppointofsale.com/credit_card_processing.php" class="mercury_description" target="_blank">
							<?php echo lang('home_mercury_activate_promo_text');?>
						</a>
					</div>
				</div>
			</div>
		</div>
	</div>
<?php  } ?>
	<?php if (!is_on_demo_host() && $can_show_bluejay) { ?>
	
	<div class="row " id="bluejay_container">
		<div class="col-md-12">
			<div class="panel">
				<div class="panel-body">
					<a id="dismiss_bluejay" href="<?php echo site_url('home/dismiss_bluejay_message') ?>" class="pull-right text-danger"><?php echo lang('common_dismiss'); ?></a>
					<div id="reseller_activate_container">
						<h3><a href="https://phppointofsale.com/resellers_signup.php" target="_blank"><?php echo lang('home_bluejay_reviews'); ?></a></h3>
						<p><?php echo lang('home_bluejay')?></p>
						<a href="http://bluejayreviews.com/phppos" class="reseller_description btn btn-primary" target="_blank">
							<?php echo lang('home_signup_now');?>
						</a>
					</div>
				</div>
			</div>
		</div>
	</div>
	

	<?php } ?>
<?php 
$this->load->helper('demo');
if (!is_on_demo_host() && !$this->config->item('hide_test_mode_home') && !$this->config->item('disable_test_mode')) { ?>
	<?php if($this->config->item('test_mode')) { ?>
		<div class="alert alert-danger">
			<strong><?php echo lang('common_in_test_mode'); ?>. <a href="sales/disable_test_mode"></strong>
			<a href="<?php echo site_url('home/disable_test_mode'); ?>" id="disable_test_mode"><?php echo lang('common_disable_test_mode');?></a>
		</div>
	<?php } ?>

	<?php if(!$this->config->item('test_mode')  && !$this->config->item('disable_test_mode')) { ?>
		<div class="row " id="test_mode_container">
			<div class="col-md-12">
				<div class="panel">
					<div class="panel-body text-center">
						<a id="dismiss_test_mode" href="<?php echo site_url('home/dismiss_test_mode') ?>" class="pull-right text-danger"><?php echo lang('common_dismiss'); ?></a>
							<strong><?php echo anchor(site_url('home/enable_test_mode'), '<i class="ion-ios-settings-strong"></i> '.lang('common_enable_test_mode'),array('id'=>'enable_test_mode')); ?></strong>
							<p><?php echo lang('common_test_mode_desc')?></p>
						</div>
					</div>
				</div>
			</div>

	<?php } ?>
<?php } ?>


<div class="text-center">					

	<?php if ($this->Employee->has_module_action_permission('reports', 'view_dashboard_stats', $this->Employee->get_logged_in_employee_info()->person_id) && (!$this->agent->is_mobile() || $this->agent->is_tablet())) { ?>
	
	<?php
	if ($this->config->item('ecommerce_cron_running')) {
	?>
	<!-- ecommerce progress bar -->
	<div class="row" id="ecommerce_progress_container">
		<div class="col-md-12">
			<div class="panel">
				<div class="panel-heading">
					<h5><?php echo lang('home_ecommerce_platform_sync')?></h5>
				</div>
				<div class="panel-body">
					<div id="progress_bar">
						<div class="progress">
						  <div class="progress-bar progress-bar-striped active" id="progessbar" role="progressbar"
						  aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width:0%">
						    <span id="progress_percent">0</span>% <span id="progress_message"></span>
						  </div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	
	<script>
	function check_ecommerce_status()
	{
		$.getJSON(SITE_URL+'/home/get_ecommerce_sync_progress', function(response)
		{
			set_progress(response.percent_complete,response.message);
		
			if (response.running)
			{
				setTimeout(check_ecommerce_status,5000);
			}
		});
	}
	
	function set_progress(percent, message)
	{
		$("#progress_container").show();
		$('#progessbar').attr('aria-valuenow', percent).css('width',percent+'%');
		$('#progress_percent').html(percent);
		if (message !='')
		{
			$("#progress_message").html('('+message+')');
		}
		else
		{
			$("#progress_message").html('');
		}
		
	}
	check_ecommerce_status();
	</script>
	
	<?php } ?>
	
	<?php
	if ($this->config->item('qb_cron_running')) {
	?>
	<!-- quickbooks progress bar -->
	<div class="row" id="quickbooks_progress_container">
		<div class="col-md-12">
			<div class="panel">
				<div class="panel-heading">
					<h5><?php echo lang('home_quickbooks_platform_sync')?></h5>
				</div>
				<div class="panel-body">
					<div id="progress_bar">
						<div class="progress">
						  <div class="progress-bar progress-bar-striped active" id="qb_progessbar" role="progressbar"
						  aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width:0%">
						    <span id="qb_progress_percent">0</span>% <span id="qb_progress_message"></span>
						  </div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	
	<script>
	function check_quickbooks_status()
	{
		$.getJSON(SITE_URL+'/home/get_qb_sync_progress', function(response)
		{
			set_qb_progress(response.percent_complete,response.message);
		
			if (response.running)
			{
				setTimeout(check_quickbooks_status,5000);
			}
		});
	}
	
	function set_qb_progress(percent, message)
	{
		$("#qb_progress_container").show();
		$('#qb_progessbar').attr('aria-valuenow', percent).css('width',percent+'%');
		$('#qb_progress_percent').html(percent);
		if (message !='')
		{
			$("#qb_progress_message").html('('+message+')');
		}
		else
		{
			$("#qb_progress_message").html('');
		}
		
	}
	check_quickbooks_status();
	</script>
	
	<?php } ?>
	
	
	<div class="row">
		
		<div class="col-lg-3 col-md-6 col-sm-6 col-xs-12">
			<a href="<?php echo site_url('sales'); ?>">
				<div class="dashboard-stats">
					<div class="left">
						<h3 class="flatBluec"><?php echo $total_sales; ?></h3>
						<h4><?php echo lang('common_total')." ".lang('module_sales'); ?></h4>
					</div>
					<div class="right flatBlue">
						<i class="ion ion-ios-cart-outline"></i>
					</div>
				</div>
			</a>
		</div>
		
		<div class="col-lg-3 col-md-6 col-sm-6 col-xs-12">
			<a href="<?php echo site_url('customers'); ?>">
				<div class="dashboard-stats" id="totalCustomers">
					<div class="left">
						<h3 class="flatGreenc"><?php echo $total_customers; ?></h3>
						<h4><?php echo lang('common_total')." ".lang('module_customers'); ?></h4>
					</div>
					<div class="right flatGreen">
						<i class="ion ion-ios-people-outline"></i>
					</div>
				</div>
			</a>
		</div>
		
		<div class="col-lg-3 col-md-6 col-sm-6 col-xs-12">
			<a href="<?php echo site_url('items'); ?>">
				<div class="dashboard-stats">
					<div class="left">
						<h3 class="flatRedc"><?php echo $total_items; ?></h3>
						<h4><?php echo lang('common_total')." ".lang('module_items'); ?></h4>
					</div>
					<div class="right flatRed">
						<i class="icon ti-harddrive"></i>
					</div>
				</div>
			</a>
		</div>
		
		<div class="col-lg-3 col-md-6 col-sm-6 col-xs-12">
			<a href="<?php echo site_url('item_kits'); ?>">
				<div class="dashboard-stats">
					<div class="left">
						<h3 class="flatOrangec"><?php echo $total_item_kits; ?></h3>
						<h4><?php echo lang('common_total')." ".lang('module_item_kits'); ?></h4>
					</div>
					<div class="right flatOrange">
						<i class="ion ion-filing"></i>
					</div>
				</div>
			</a>
		</div>
	</div>
</div>

<?php } ?>

	<?php if(!$this->config->item('hide_expire_dashboard') && count($expiring_items) > 0) { ?> 
		<h3 class="text-center"><?php echo lang('home_items_expiring_soon')?></h3>
		<div class="row manage-table">
			<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
		<div class="panel panel-piluku">
		<div class="panel-body nopadding table_holder table-responsive" id="table_holder">
		<table class="table">
			<tr>
				<th><?php echo lang('common_name')?></th>
				<th><?php echo lang('common_location')?></th>
				<th><?php echo lang('common_expire_date')?></th>
				<th><?php echo lang('reports_quantity_expiring')?></th>
				<th><?php echo lang('common_category')?></th>
				<th><?php echo lang('common_item_number')?></th>
				<th><?php echo lang('common_product_id')?></th>
			</tr>
			
			<?php foreach($expiring_items as $eitem) { ?>
					<tr>
						<td><?php echo $eitem['name'];?></td>
						<td><?php echo $eitem['location_name'];?></td>
						<td><?php echo date(get_date_format(),strtotime($eitem['expire_date']));?></td>
						<td><?php echo to_quantity($eitem['quantity_expiring']);?></td>
						<td><?php echo $eitem['category'];?></td>
						<td><?php echo $eitem['item_number'];?></td>
						<td><?php echo $eitem['product_id'];?></td>
					</tr>
					
			<?php } ?>
		</table>
	</div>
</div>
</div>
</div>
	<?php } ?>

<h5 class="text-center"><?php echo lang('home_welcome_message');?></h5>

<div class="row quick-actions">

	<?php if ($this->Employee->has_module_permission('sales', $this->Employee->get_logged_in_employee_info()->person_id)) {	?>
		<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
			<div class="list-group">
					<a class="list-group-item" href="<?php echo site_url('sales'); ?>"> <i class="icon ti-shopping-cart"></i> <?php echo lang('common_start_new_sale'); ?></a>
			</div>
		</div>
	<?php } ?>


	<?php if ($this->Employee->has_module_permission('receivings', $this->Employee->get_logged_in_employee_info()->person_id)) { ?>
		<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
			<div class="list-group">
					<a class="list-group-item" href="<?php echo site_url('receivings'); ?>"> <i class="icon ti-cloud-down"></i> <?php echo lang('home_receivings_start_new_receiving'); ?></a>
			</div>
		</div>
	<?php } ?>	
	
  <?php if ($this->Employee->has_module_action_permission('reports', 'view_closeout', $this->Employee->get_logged_in_employee_info()->person_id)) { ?>
		<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
			<div class="list-group">
					<a class="list-group-item" href="<?php echo site_url('reports/generate/closeout?report_type=simple&report_date_range_simple=TODAY');?>&export_excel=0"> <i class="ion-clock"></i> <?php echo lang('home_todays_closeout_report'); ?></a>
			</div>
		</div>
	<?php } ?>
	
	<?php if ($this->Employee->has_module_action_permission('reports', 'view_sales', $this->Employee->get_logged_in_employee_info()->person_id)) { ?>
		<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
			<div class="list-group">
					<a class="list-group-item" href="<?php echo site_url('reports/generate/detailed_sales?report_type=simple&report_date_range_simple=TODAY&sale_type=all&with_time=1&excel_export=0');?>&export_excel=0"> <i class="ion-clock"></i> <?php echo lang('home_todays_detailed_sales_report'); ?></a>
			</div>
		</div>
	<?php } ?>
	
	<?php if ($this->Employee->has_module_action_permission('reports', 'view_items', $this->Employee->get_logged_in_employee_info()->person_id)) { ?>
		<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
			<div class="list-group">
					<a class="list-group-item" href="<?php echo site_url('reports/generate/summary_items?category_id=&supplier_id=&sale_type=all&items_to_show=items_with_sales&report_type=simple&report_date_range_simple=TODAY&export_excel=0&with_time=1');?>"> <i class="ion-stats-bars"></i> <?php echo lang('home_todays_summary_items_report'); ?></a>
			</div>
		</div>
	<?php } ?>
	
	
	<?php foreach($saved_reports as $key => $report) { 
		
	$report_url = $report['url'];
	$report_url.=(parse_url($report['url'], PHP_URL_QUERY) ? '&' : '?') . "key=$key";
	?>
	
	<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
		<div class="list-group">
				<a class="list-group-item" href="<?php echo site_url($report_url);?>"> <i class="icon ti-star"></i> <?php echo $report['name']; ?></a>
		</div>
	</div>
	<?php } ?>
	
</div>

<?php if ($this->Employee->has_module_action_permission('reports', 'view_dashboard_stats', $this->Employee->get_logged_in_employee_info()->person_id)) { ?>
<div class="row ">
		<div class="col-md-12">
			<div class="panel">
				<div class="panel-body">
					
					<?php if (can_display_graphical_report()) { ?>
					<div class="panel-heading">
						<h4 class="text-center"><?php echo lang('common_sales_info') ?></h4>	
					</div>
					<!-- Nav tabs -->
                    <ul class="nav nav-tabs piluku-tabs" role="tablist">
                        <li role="presentation" class="active"><a href="#month" data-type="monthly" aria-controls="month" role="tab"><?php echo lang('common_month') ?></a></li>
                        <li role="presentation"><a href="#week" data-type="weekly" aria-controls="week" role="tab"><?php echo lang('common_week') ?></a></li>
                    </ul>

                    <!-- Tab panes -->
                    <div class="tab-content piluku-tab-content">
                        <div role="tabpanel" class="tab-pane active" id="month">
                        	<div class="chart">
                        		<?php if(isset($month_sale) && !isset($month_sale['message'])){ ?>
									<canvas id="charts" width="400" height="100"></canvas>		
								<?php } else{ 
									echo $month_sale['message'];
									 } ?>
							</div>
                        </div>
                        <div role="tabpanel" class="tab-pane" id="week">
                        	
                       	</div>
                    </div>
						<?php } ?>
				</div>
			</div>
		</div>
	</div>
	<?php } ?>
</div>

<?php if($choose_location && count($authenticated_locations) > 1){ ?>
	

<!-- Modal -->
<div class="modal fade" id="choose_location_modal" tabindex="-1" role="dialog" aria-labelledby="chooseLocation" data-keyboard="false" data-backdrop="static">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title" id="chooseLocation"><?php echo lang('common_locations_choose_location'); ?></h4>
      </div>
      <div class="modal-body">
        <ul class="list-inline choose-location-home">
        	<?php foreach ($authenticated_locations as $key => $value) { ?>
				<li><a class="set_employee_current_location_after_login" data-location-id="<?php echo $key; ?>" href="<?php echo site_url('home/set_employee_current_location_id/'.$key) ?>"> <?php echo $value; ?> </a></li>
			<?php } ?>
        </ul>
      </div>
    </div>
  </div>
</div>
<?php } ?>


<!-- Location Message to employee -->
<script>
	$(document).ready(function(){


		$("#dismiss_mercury").click(function(e){
			e.preventDefault();
			$.get($(this).attr('href'));
			$("#mercury_container").fadeOut();
			
		});
		
		$("#dismiss_reseller").click(function(e){
			e.preventDefault();
			$.get($(this).attr('href'));
			$("#reseller_container").fadeOut();
			
		});
		
		$("#dismiss_feedback").click(function(e){
			e.preventDefault();
			$.get($(this).attr('href'));
			$("#feedback_container").fadeOut();
			
		});
		
		$("#dismiss_bluejay").click(function(e){
			e.preventDefault();
			$.get($(this).attr('href'));
			$("#bluejay_container").fadeOut();
			
		});
		
		
		
		$("#dismiss_setup_wizard").click(function(e){
			e.preventDefault();
			$.get($(this).attr('href'));
			$("#setup_wizard_container").fadeOut();
			
		});
		

		$("#dismiss_test_mode").click(function(e){
			e.preventDefault();
			$.get($(this).attr('href'));
			$("#test_mode_container").fadeOut();
		});
	
		<?php if($choose_location && count($authenticated_locations) > 1) { ?>
			
			$('#choose_location_modal').modal('show');

			$(".set_employee_current_location_after_login").on('click',function(e)
			{
				e.preventDefault();

				var location_id = $(this).data('location-id');
				$.ajax({
				    type: 'POST',
				    url: '<?php echo site_url('home/set_employee_current_location_id'); ?>',
				    data: { 
				        'employee_current_location_id': location_id, 
				    },
				    success: function(){

				    	window.location = <?php echo json_encode(site_url('home')); ?>;
				    }
				});
				
			});
			
		<?php } ?>


		<?php if(isset($month_sale) && !isset($month_sale['message'])){ ?>
			var data = {
				labels: <?php echo $month_sale['day'] ?>,
				datasets: [
				{
					fillColor : "#5d9bfb",
					strokeColor : "#5d9bfb",
					highlightFill : "#5d9bfb",
					highlightStroke : "#5d9bfb",
					data: <?php echo $month_sale['amount'] ?>
				}
				]
			};
			var ctx = document.getElementById("charts").getContext("2d");
			var myBarChart = new Chart(ctx).Bar(data, {
				responsive : true
			});
		<?php } ?>

	        

		$('.piluku-tabs a').on('click',function(e) {
			e.preventDefault();
			$('.piluku-tabs li').removeClass('active');
			$(this).parent('li').addClass('active');
			var type = $(this).attr('data-type');
			$.post('<?php echo site_url("home/sales_widget/'+type+'"); ?>', function(res)
			{
				var obj = jQuery.parseJSON(res);
				if(obj.message)
				{
					$(".chart").html(obj.message);
					return false;
				}
				
				renderChart(obj.day, obj.amount);
				
				myBarChart.update();
			});
		});

		function renderChart(label,data){

		    $(".chart").html("").html('<canvas id="charts" width="400" height="400"></canvas>');
		    var lineChartData = {
		        labels : label,
		        datasets : [
		            {
		                fillColor : "#5d9bfb",
						strokeColor : "#5d9bfb",
						highlightFill : "#5d9bfb",
						highlightStroke : "#5d9bfb",
		                data : data
		            }
		        ]

		    }
		    var canvas = document.getElementById("charts");
		    var ctx = canvas.getContext("2d");

		    myLine = new Chart(ctx).Bar(lineChartData, {
		        responsive: true,
		        maintainAspectRatio: false
		    });
		}
	});
</script>

<?php $this->load->view("partial/footer"); ?>