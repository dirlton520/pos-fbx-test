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
					<!--<li>
						<input type="text" name="start_date" value="<?php echo $selected_date ?>" id="date" placeholder="<?php echo lang('deliveries_select_date'); ?>" class="form-control datepicker">
					</li>-->
				</ul>
			</div>
		</div>
		<div class="col-md-3 col-sm-3 col-xs-2">
			<div class="buttons-list">
				<div class="pull-right-btn">
					<!-- right buttons-->
					<?php if ($this->Employee->has_module_action_permission('deliveries', 'add_update', $this->Employee->get_logged_in_employee_info()->person_id) && !$deleted) { ?>
					<?php echo anchor(
							"deliveries/view/-1/",
							'<span class="ion-plus"> ' . lang('deliveries_new') . '</span>',
							array('id' => 'new-person-btn', 'class' => 'btn btn-primary btn-lg hidden-sm hidden-xs', 'title' => lang('deliveries_new'))
						);
					}
					?>

					<a class="printBtn btn btn-success" id="printbutton">Print</a>

					<div class="btn-group" role="group" aria-label="...">
						<?php echo anchor('deliveries', '<span class="ion-ios-arrow-back"></span>', array('class' => 'btn btn-more hidden-xs')) ?>
						<div class="piluku-dropdown btn-group">
							<button type="button" class="btn btn-more dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
								<span class="visible-xs ion-android-more-vertical"></span>
								<span class="hidden-xs ion-calendar"></span> <span class="hidden-xs hidden-sm"><?php echo lang('deliveries_calendars'); ?></span>
							</button>
							<ul class="dropdown-menu" role="menu">
								<?php foreach ($date_fields as $date_field_choice_value => $date_field_choice_display) { ?>
									<li>
										<?php if ($date_field_choice_value != $date_field) { ?>
											<?php echo anchor('deliveries/calendar/' . $date_field_choice_value, $date_field_choice_display) ?>
										<?php } else { ?>
											<?php echo anchor('deliveries/calendar/' . $date_field_choice_value, $date_field_choice_display, array('class' => 'active')) ?>
										<?php } ?>
									</li>
								<?php } ?>
							</ul>
						</div>
					</div>
					<?php if ($deleted) {
						echo
						anchor(
							"$controller_name/toggle_show_deleted/0",
							'<span class="ion-android-exit"></span> <span class="hidden-xs">' . lang('common_done') . '</span>',
							array('class' => 'btn btn-primary btn-lg toggle_deleted', 'title' => lang('common_done'))
						);
					} ?>
				</div>
			</div>
		</div>
	</div>
</div>
<div id="divhidden"></div>
<a href=""></a>
<div class="main-content">
	<div class="container-fluid">
		<div class="row manage-table">
			<!-- New Calender -->
			<div class="panel panel-piluku">
				<div class="panel-heading">
					<h3 class="panel-title">
						<?php echo $date_fields[$date_field] . ' ' . lang('common_calendar') ?>
					</h3>
				</div>
				<div class="spinner" id="grid-loader" style="display:none">
					<div class="rect1"></div>
					<div class="rect2"></div>
					<div class="rect3"></div>
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



<script type="text/javascript">
	document.addEventListener('DOMContentLoaded', function() {
		var date_field = "<?php echo $date_field; ?>";
		var calendarEl = document.getElementById('calendar');

		var iv = localStorage.getItem("fcDefaultView") || 'dayGridMonth';
		var id = localStorage.getItem("fcDefaultDate") || new Date();

		var calendar = new FullCalendar.Calendar(calendarEl, {
			schedulerLicenseKey: '0673400841-fcs-1614016042',
			initialView: iv,
			initialDate: new Date(id),
			locale: '<?php echo get_js_locale();?>',
			headerToolbar: {
				left: 'prev,next today',
				center: 'title',
				right: 'dayGridMonth,timeGridWeek,timeGridDay,listMonth' //dayGridMonth,timeGridWeek,timeGridDay,listMonth
			},
			aspectRatio: 2,
			navLinks: true, // can click day/week names to navigate views
			businessHours: true, // display business hours
			editable: true,
			selectable: true,
			selectHelper: true,
			datesSet: function(dateInfo) {
				var starD = new Date(dateInfo.startStr);
				var endD = new Date(dateInfo.endStr);

				var midDate = new Date((starD.getTime() + endD.getTime()) / 2);

				localStorage.setItem("fcDefaultView", dateInfo.view.type);
				localStorage.setItem("fcDefaultDate", new Date(midDate));
			},

			eventSources: [{
				url: SITE_URL + '/deliveries/get_calendar',
				method: 'POST',
				extraParams: {
					date_field: date_field,
					action_type: 'get_events'
				},
				failure: function() {
					alert('<?php echo lang('deliveries_calendar_error_while_fetching_events');?>');
				}
			}],

			eventDrop: function(arg) {
				var start = arg.event.start.toDateString() + ' ' + arg.event.start.getHours() + ':' + arg.event.start.getMinutes() + ':' + arg.event.start.getSeconds();
				if (arg.event.end == null) {
					end = start;
				} else {
					var end = arg.event.end.toDateString() + ' ' + arg.event.end.getHours() + ':' + arg.event.end.getMinutes() + ':' + arg.event.end.getSeconds();
				}

				$.ajax({
					url: SITE_URL + '/deliveries/calendar',
					type: "POST",
					data: {
						id: arg.event.id,
						start: start,
						end: end,
						date_field: date_field,
						action_type: 'update_event'
					},
				});
			},

			eventResize: function(arg) {
				var start = arg.event.start.toDateString() + ' ' + arg.event.start.getHours() + ':' + arg.event.start.getMinutes() + ':' + arg.event.start.getSeconds();
				var end = arg.event.end.toDateString() + ' ' + arg.event.end.getHours() + ':' + arg.event.end.getMinutes() + ':' + arg.event.end.getSeconds();

				$.ajax({
					url: SITE_URL + '/deliveries/calendar',
					type: "POST",
					data: {
						id: arg.event.id,
						start: start,
						end: end,
						date_field: date_field,
						action_type: 'update_event'
					},
				});
			},

			eventClick: function(arg) {
				var id = arg.event.id;
				$('body').find('#myModal').load('<?php echo site_url('deliveries/view_delivery_modal/') ?>' + id + '?redirect=deliveries/calendar/' + date_field);
				$('#myModal').modal('show');
			}
		});

		calendar.render();

		$("#printbutton").click(function(e) {
			window.print();
		});
		/*
			date_time_picker_field($('.datepicker'), JS_DATE_FORMAT);
			var $date = $("#date");
			var picker = $date.data("DateTimePicker");

			$date.on('dp.change', function(e) {
			
				calendar.changeView(iv, e.date.format('YYYY-M-D'));

				//window.location = SITE_URL + '/deliveries/calendar/' + date_field + '/' + e.date.format('YYYY') + '/' + e.date.format('M') + '/' + '-1' + '/' + e.date.format('D');
			});
		*/
	});
</script>

<?php $this->load->view("partial/footer"); ?>`-