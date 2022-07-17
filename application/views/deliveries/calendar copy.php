<?php $this->load->view("partial/header"); ?>
<div class="manage_buttons">
<div class="manage-row-options hidden">
	<div class="email_buttons text-center">		
		
	</div>
</div>
	<div class="row hidden-print">
		<div class="col-md-9 col-sm-9 col-xs-10">
			
			<div class="date search no-left-border">
				<ul class="list-inline">
					<li>
						<input type="text" name="start_date" value="<?php echo $selected_date ?>" id="date" placeholder="<?php echo lang('deliveries_select_date'); ?>" class="form-control datepicker">
					</li>
					<li>
						
						<div class="btn-group " role="group" aria-label="...">
						  <a href="<?php echo H($monthly_url); ?>" class="btn btn-default <?php echo (!$week && !$day) ? 'active' : '' ?>"><?php echo lang('common_month'); ?></a>
						  <a href="<?php echo H($weekly_url); ?>" class="btn btn-default <?php echo ($week && !$day) ? 'active' : '' ?>"><?php echo lang('common_week'); ?></a>
						  <a href="<?php echo H($daily_url); ?>" class="btn btn-default <?php echo $day ? 'active' : '' ?>"><?php echo lang('common_day'); ?></a>
						</div>
												
					</li>
				</ul>	
			</div>
			
		</div>
		<div class="col-md-3 col-sm-3 col-xs-2">	
			<div class="buttons-list">
				<div class="pull-right-btn">
					<!-- right buttons-->
					<div class="btn-group" role="group" aria-label="...">
						<?php echo anchor('deliveries', '<span class="ion-ios-arrow-back"></span>', array('class' => 'btn btn-more hidden-xs')) ?>
						<div class="piluku-dropdown btn-group">
							<button type="button" class="btn btn-more dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
								<span class="visible-xs ion-android-more-vertical"></span>
								<span class="hidden-xs ion-calendar"></span> <span class="hidden-xs hidden-sm"><?php echo lang('deliveries_calendars'); ?></span>
							</button>
							<ul class="dropdown-menu" role="menu">
							<?php foreach($date_fields as $date_field_choice_value => $date_field_choice_display) { ?>
								<li>
								<?php if ($date_field_choice_value != $date_field) { ?>
										<?php echo anchor('deliveries/calendar/'.$date_field_choice_value.'/'.$year.'/'.$month.'/'.$week.'/'.$day, $date_field_choice_display)?>
									<?php } else { ?>
										<?php echo anchor('deliveries/calendar/'.$date_field_choice_value.'/'.$year.'/'.$month.'/'.$week.'/'.$day, $date_field_choice_display, array('class' => 'active'))?>
									<?php } ?>
								</li>
							<?php } ?>
							</ul>
						</div>
						
						
						
					</div>
					<?php if($deleted) { 
						echo 
						anchor("$controller_name/toggle_show_deleted/0",
							'<span class="ion-android-exit"></span> <span class="hidden-xs">'.lang('common_done').'</span>',
							array('class'=>'btn btn-primary btn-lg toggle_deleted','title'=> lang('common_done')));
					} ?>
			</div>
		</div>				
	</div>
</div>
</div>

<div class="main-content">
	<div class="container-fluid">
			<div class="row manage-table">
				<div class="panel panel-piluku">
					<div class="panel-heading">
					<h3 class="panel-title">
						<?php echo $date_fields[$date_field] . ' ' . lang('common_calendar') ?>
					</h3>
					</div>
					<div class="panel-body nopadding table_holder table-responsive" id="table_holder">
						<?php echo $calendar;?>
					</div>
					
				</div>


				<!-- New Calender -->
				<div class="panel panel-piluku">
					<div class="panel-heading">
						<h3 class="panel-title">
							<?php echo $date_fields[$date_field] . ' ' . lang('common_calendar') ?>
						</h3>
					</div>
					<div class="panel-body">
						<div class="col-md-12">
							<div id='calendar'></div>
						</div>
					</div>
				</div>

			</div>
		</div>
</div>
<script>
	var date_field = "<?php echo $date_field; ?>";
	
	date_time_picker_field($('.datepicker'), JS_DATE_FORMAT);
	var $date = $("#date");
	var picker = $date.data("DateTimePicker");
	
	$date.on('dp.change', function (e) {
		window.location = SITE_URL + '/deliveries/calendar/' + date_field +'/'+ e.date.format('YYYY')+'/'+ e.date.format('M') +'/'+ '-1' +'/'+ e.date.format('D');
	});
	
</script>

<script type="text/javascript">
	document.addEventListener('DOMContentLoaded', function() {
	var calendarEl = document.getElementById('calendar');
	var calendar = new FullCalendar.Calendar(calendarEl, {
		headerToolbar: {
			left: 'prev,next today',
			center: 'title',
			right: 'dayGridMonth,timeGridWeek,timeGridDay' //dayGridMonth,timeGridWeek,timeGridDay,listMonth
		},
		//initialDate: '2020-09-12',
		navLinks: true, // can click day/week names to navigate views
		businessHours: true, // display business hours
		editable: true,
		selectable: true,
	});
	calendar.render();
	});
</script>

<?php $this->load->view("partial/footer"); ?>
