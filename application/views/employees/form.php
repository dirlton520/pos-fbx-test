<?php $this->load->view("partial/header"); ?>
<div class="row" id="form">
	<div class="spinner" id="grid-loader" style="display:none">
		<div class="rect1"></div>
		<div class="rect2"></div>
		<div class="rect3"></div>
	</div>
	<div class="col-md-12">


		<?php if ($person_info->person_id && !isset($is_clone)) { ?>
			<div class="panel">
				<div class="panel-body ">
					<div class="user-badge">
						<?php echo $person_info->image_id ? '<div class="user-badge-avatar">' . img(array('src' => app_file_url($person_info->image_id), 'class' => 'img-polaroid img-polaroid-s')) . '</div>' : '<div class="user-badge-avatar">' . img(array('src' => base_url('assets/assets/images/avatar-default.jpg'), 'class' => 'img-polaroid')) . '</div>'; ?>
						<div class="user-badge-details">
							<?php echo $person_info->first_name . ' ' . $person_info->last_name; ?>
							<p><?php echo $person_info->username; ?></p>
						</div>
						<ul class="list-inline pull-right">
							<?php
							$one_year_ago = date('Y-m-d', strtotime('-1 year'));
							$today = date('Y-m-d') . '%2023:59:59';
							?>
							<li><a target="_blank" href="<?php echo site_url('reports/generate/specific_employee?employee_type=logged_in_employee&report_type=complex&start_date=' . $one_year_ago . '&start_date_formatted=' . date(get_date_format() . ' ' . get_time_format(), strtotime($one_year_ago)) . '&end_date=' . $today . '&end_date_formatted=' . date(get_date_format() . ' ' . get_time_format(), strtotime(date('Y-m-d') . ' 23:59:59')) . '&employee_id=' . $person_info->person_id . '&sale_type=all&export_excel=0'); ?>" class="btn btn-success"><?php echo lang('common_view_report'); ?></a></li>
							<?php if ($person_info->email) { ?>
								<li><a href="mailto:<?php echo $person_info->email; ?>" class="btn btn-primary"><?php echo lang('common_send_email'); ?></a></li>
							<?php } ?>
						</ul>
					</div>
				</div>
			</div>
		<?php } ?>

		<?php $current_employee_editing_self = $this->Employee->get_logged_in_employee_info()->person_id == $person_info->person_id;
		echo form_open_multipart('employees/save/' . (!isset($is_clone) ? $person_info->person_id : ''), array('id' => 'employee_form', 'class' => 'form-horizontal'));
		?>


		<div class="panel panel-piluku">
			<div class="panel-heading">
				<h3 class="panel-title">
					<i class="ion-edit"></i>
					<?php echo lang("employees_basic_information"); ?>
					<small>(<?php echo lang('common_fields_required_message'); ?>)</small>
				</h3>
			</div>

			<div class="panel-body">

				<?php $this->load->view("people/form_basic_info"); ?>



				<div class="form-group offset1">
					<?php echo form_label(lang('employees_login_start_time') . ':', 'login_start_time', array('class' => 'col-sm-3 col-md-3 col-lg-2 control-label text-info wide')); ?>
					<div class="col-sm-9 col-md-9 col-lg-10">
						<div class="input-group date">
							<span class="input-group-addon bg">
								<i class="ion ion-ios-calendar-outline"></i>
							</span>
							<?php echo form_input(array(
								'name' => 'login_start_time',
								'id' => 'login_start_time',
								'class' => 'form-control timepicker',
								'value' => $person_info->login_start_time ? date(get_time_format(), strtotime($person_info->login_start_time)) : ''
							)); ?>
						</div>
					</div>
				</div>


				<div class="form-group offset1">
					<?php echo form_label(lang('employees_login_end_time') . ':', 'login_end_time', array('class' => 'col-sm-3 col-md-3 col-lg-2 control-label text-info wide')); ?>
					<div class="col-sm-9 col-md-9 col-lg-10">
						<div class="input-group date">
							<span class="input-group-addon bg">
								<i class="ion ion-ios-calendar-outline"></i>
							</span>
							<?php echo form_input(array(
								'name' => 'login_end_time',
								'id' => 'login_end_time',
								'class' => 'form-control timepicker',
								'value' => $person_info->login_end_time ? date(get_time_format(), strtotime($person_info->login_end_time)) : ''
							)); ?>
						</div>
					</div>
				</div>





				<div class="form-group">
					<?php echo form_label(lang('common_override_price_adjustments') . ':', 'override_price_adjustments', array('class' => 'col-sm-3 col-md-3 col-lg-2 control-label')); ?>
					<div class="col-sm-9 col-md-9 col-lg-10">
						<?php
						echo	form_checkbox(array(
							'name' => 'override_price_adjustments',
							'id' => 'override_price_adjustments',
							'value' => 1,
							'checked' => $person_info->override_price_adjustments,
						));
						echo '<label for="override_price_adjustments"><span></span></label>';;
						?>
					</div>
				</div>



				<div class="form-group">
					<?php echo form_label(lang('common_max_discount_percent') . ':', 'max_discount_percent', array('class' => 'col-sm-3 col-md-3 col-lg-2 control-label')); ?>
					<div class="col-sm-9 col-md-9 col-lg-10">
						<div class="input-group">
							<?php echo form_input(array(
								'name' => 'max_discount_percent',
								'id' => 'max_discount_percent',
								'class' => 'form-control',
								'value' => $person_info->max_discount_percent
							)); ?>
							<span class="input-group-addon">%</span>
						</div>
					</div>
				</div>


				<div class="form-group">
					<?php echo form_label(lang('common_commission_default_rate') . ':', 'commission_percent', array('class' => 'col-sm-3 col-md-3 col-lg-2 control-label')); ?>
					<div class="col-sm-9 col-md-9 col-lg-10">
						<div class="input-group">
							<?php echo form_input(array(
								'name' => 'commission_percent',
								'id' => 'commission_percent',
								'class' => 'form-control',
								'value' => to_quantity($person_info->commission_percent, FALSE)
							)); ?>
							<span class="input-group-addon">%</span>
						</div>
					</div>
				</div>

				<div class="form-group">
					<?php echo form_label(lang('common_commission_percent_calculation') . ': ', 'commission_percent_type', array('class' => 'col-sm-3 col-md-3 col-lg-2 control-label')); ?>
					<div class="col-sm-9 col-md-9 col-lg-10">
						<?php echo form_dropdown(
							'commission_percent_type',
							array(
								'selling_price'  => lang('common_unit_price'),
								'profit'    => lang('common_profit'),
							),
							$person_info->commission_percent_type,
							array(
								'class' => 'form-control',
								'id' => 'commission_percent_type'
							)
						)
						?>
					</div>
				</div>


				<?php if ($this->config->item('timeclock')) { ?>
					<div class="form-group">
						<?php echo form_label(lang('common_hourly_pay_rate'), 'hourly_pay_rate', array('class' => 'col-sm-3 col-md-3 col-lg-2 control-label')); ?>
						<div class="col-sm-9 col-md-9 col-lg-10">
							<div class="input-group">
								<div class="input-group-addon"><?php echo $this->config->item('currency_symbol'); ?></div>
								<?php echo form_input(array(
									'name' => 'hourly_pay_rate',
									'id' => 'hourly_pay_rate',
									'class' => 'form-control',
									'value' => $person_info->hourly_pay_rate ? to_currency_no_money($person_info->hourly_pay_rate, 2) : ''
								)); ?>
							</div>


						</div>
					</div>
				<?php
				} else {
					echo form_hidden('hourly_pay_rate', 0);
				}
				?>


				<div class="form-group offset1">
					<?php echo form_label(lang('employees_hire_date') . ':', 'hire_date', array('class' => 'col-sm-3 col-md-3 col-lg-2 control-label text-info wide')); ?>
					<div class="col-sm-9 col-md-9 col-lg-10">
						<div class="input-group date">
							<span class="input-group-addon bg">
								<i class="ion ion-ios-calendar-outline"></i>
							</span>
							<?php echo form_input(array(
								'name' => 'hire_date',
								'id' => 'hire_date',
								'class' => 'form-control datepicker',
								'value' => $person_info->hire_date ? date(get_date_format(), strtotime($person_info->hire_date)) : ''
							)); ?>
						</div>
					</div>
				</div>


				<div class="form-group offset1">
					<?php echo form_label(lang('employees_birthday') . ':', 'birthday', array('class' => 'col-sm-3 col-md-3 col-lg-2 control-label text-info wide')); ?>
					<div class="col-sm-9 col-md-9 col-lg-10">
						<div class="input-group date">
							<span class="input-group-addon bg">
								<i class="ion ion-ios-calendar-outline"></i>
							</span>
							<?php echo form_input(array(
								'name' => 'birthday',
								'id' => 'birthday',
								'class' => 'form-control datepicker',
								'value' => $person_info->birthday ? date(get_date_format(), strtotime($person_info->birthday)) : ''
							)); ?>
						</div>
					</div>
				</div>


				<div class="form-group">
					<?php echo form_label(lang('common_employees_number') . ':', 'employee_number', array('class' => 'col-sm-3 col-md-3 col-lg-2 control-label')); ?>
					<div class="col-sm-9 col-md-9 col-lg-10">
						<?php echo form_input(array(
							'name' => 'employee_number',
							'id' => 'employee_number',
							'class' => 'form-control',
							'value' => $person_info->employee_number
						)); ?>
					</div>
				</div>




				<div class="form-group">
					<?php echo form_label(lang('common_language') . ':', 'language', array('class' => 'col-sm-3 col-md-3 col-lg-2 col-sm-3 col-md-3 col-lg-2 control-label  required')); ?>
					<div class="col-sm-9 col-md-9 col-lg-10">
						<?php echo form_dropdown(
							'language',
							array(
								'english'  => 'English',
								'indonesia'    => 'Indonesia',
								'spanish'   => 'Español',
								'french'    => 'Fançais',
								'italian'    => 'Italiano',
								'german'    => 'Deutsch',
								'dutch'    => 'Nederlands',
								'portugues'    => 'Portugues',
								'arabic' => 'العَرَبِيةُ‎‎',
								'khmer' => 'Khmer',
								'vietnamese' => 'Vietnamese',
								'chinese' => '中文',
								'chinese_traditional' => '繁體中文',
								'tamil' => 'Tamil'
							),
							$person_info->language ? $person_info->language : $this->Appconfig->get_raw_language_value(),
							'class="form-control" id="language"'
						);
						?>
					</div>
				</div>


				<div class="form-group">
					<?php echo form_label(lang('common_default_register') . ':', 'language', array('class' => 'col-sm-3 col-md-3 col-lg-2 col-sm-3 col-md-3 col-lg-2 control-label ')); ?>
					<div class="col-sm-9 col-md-9 col-lg-10">
						<?php echo form_dropdown('default_register', $registers, $default_register, 'class="form-control"'); ?>
					</div>
				</div>


				<?php if (count($locations) == 1) { ?>
					<?php
					echo form_hidden('locations[]', current(array_keys($locations)));
					?>
				<?php } else { ?>
					<div class="form-group">
						<?php echo form_label(lang('common_locations') . ':', null, array('class' => 'col-sm-3 col-md-3 col-lg-2 col-sm-3 col-md-3 col-lg-2 control-label  required')); ?>
						<div class="col-sm-9 col-md-9 col-lg-10">
							<ul id="locations_list" class="list-inline">
								<?php
								echo '<li>' . form_checkbox(
									array(
										'id' => 'select_all',
										'class' => 'all_checkboxes',
										'name' => 'select_all',
										'value' => '1',
									)
								) . '<label for="select_all"><span></span><strong>' . lang('common_select_all') . '</strong></label></li>';

								foreach ($locations as $location_id => $location) {
									$checkbox_options = array(
										'name' => 'locations[]',
										'class' => 'location_checkboxes',
										'id' => 'locations' . $location_id,
										'value' => $location_id,
										'checked' => $location['has_access'],
									);

									if (!$location['can_assign_access']) {
										$checkbox_options['disabled'] = 'disabled';

										//Only send permission if checked
										if ($checkbox_options['checked']) {
											echo form_hidden('locations[]', $location_id);
										}
									}

									echo '<li>' . form_checkbox($checkbox_options) . '<label for="locations' . $location_id . '"><span></span></label> ' . $location['name'] . '</li>';
								}
								?>
							</ul>
						</div>
					</div>
				<?php } ?>

				<?php for ($k = 1; $k <= NUMBER_OF_PEOPLE_CUSTOM_FIELDS; $k++) { ?>
					<?php
					$custom_field = $this->Employee->get_custom_field($k);
					if ($custom_field !== FALSE) { 
						
						$required = false;
						$required_text = '';
						if($this->Employee->get_custom_field($k,'required') && in_array($current_location,$this->Employee->get_custom_field($k,'locations'))){
							$required = true;
							$required_text = 'required';
						}
						
						?>
						<div class="form-group">
							<?php echo form_label($custom_field . ' :', "custom_field_${k}_value", array('class' => 'col-sm-3 col-md-3 col-lg-2 control-label '.$required_text)); ?>

							<div class="col-sm-9 col-md-9 col-lg-10">
								<?php if ($this->Employee->get_custom_field($k, 'type') == 'checkbox') { ?>

									<?php echo form_checkbox("custom_field_${k}_value", '1', (bool) $person_info->{"custom_field_${k}_value"}, "id='custom_field_${k}_value' $required_text"); ?>
									<label for="<?php echo "custom_field_${k}_value"; ?>"><span></span></label>

								<?php } elseif ($this->Employee->get_custom_field($k, 'type') == 'date') { ?>

									<?php echo form_input(array(
										'name' => "custom_field_${k}_value",
										'id' => "custom_field_${k}_value",
										'class' => "custom_field_${k}_value" . ' form-control',
										'value' => is_numeric($person_info->{"custom_field_${k}_value"}) ? date(get_date_format(), $person_info->{"custom_field_${k}_value"}) : '',
										($required ? $required_text : $required_text) => ($required ? $required_text : $required_text)
									)); ?>
									<script type="text/javascript">
										var $field = <?php echo "\$('#custom_field_${k}_value')"; ?>;
										$field.datetimepicker({
											format: JS_DATE_FORMAT,
											locale: LOCALE,
											ignoreReadonly: IS_MOBILE ? true : false
										});
									</script>

								<?php } elseif ($this->Employee->get_custom_field($k, 'type') == 'dropdown') { ?>

									<?php
									$choices = explode('|', $this->Employee->get_custom_field($k, 'choices'));
									$select_options = array('' => lang('common_please_select'));
									foreach ($choices as $choice) {
										$select_options[$choice] = $choice;
									}
									echo form_dropdown("custom_field_${k}_value", $select_options, $person_info->{"custom_field_${k}_value"}, 'class="form-control" '.$required_text); ?>

								<?php } elseif ($this->Employee->get_custom_field($k, 'type') == 'image') {
										echo form_input(
											array(
												'name'=>"custom_field_${k}_value",
												'id'=>"custom_field_${k}_value",
												'type' => 'file',
												'class'=>"custom_field_${k}_value".' form-control',
												'accept'=>".png,.jpg,.jpeg,.gif"
											),
											NULL,
											$person_info->{"custom_field_${k}_value"} ? "" : $required_text
										);

									if ($person_info->{"custom_field_${k}_value"}) {
										echo "<img width='30%' src='" . app_file_url($person_info->{"custom_field_${k}_value"}) . "' />";
										echo "<div class='delete-custom-image'><a href='" . site_url('employees/delete_custom_field_value/' . $person_info->person_id . '/' . $k) . "'>" . lang('common_delete') . "</a></div>";
									}
								?>
								<?php
								} elseif ($this->Employee->get_custom_field($k, 'type') == 'file') {
									echo form_input(
										array(
										  'name'=>"custom_field_${k}_value",
										  'id'=>"custom_field_${k}_value",
										  'type' => 'file',
										  'class'=>"custom_field_${k}_value".' form-control'
										),
									  NULL,
									  $person_info->{"custom_field_${k}_value"} ? "" : $required_text
								  	);

									if ($person_info->{"custom_field_${k}_value"}) {
										echo anchor('employees/download/' . $person_info->{"custom_field_${k}_value"}, $this->Appfile->get_file_info($person_info->{"custom_field_${k}_value"})->file_name, array('target' => '_blank'));
										echo "<div class='delete-custom-image'><a href='" . site_url('employees/delete_custom_field_value/' . $person_info->person_id . '/' . $k) . "'>" . lang('common_delete') . "</a></div>";
									}
								} else {

									echo form_input(array(
										'name' => "custom_field_${k}_value",
										'id' => "custom_field_${k}_value",
										'class' => "custom_field_${k}_value" . ' form-control',
										'value' => $person_info->{"custom_field_${k}_value"},
										($required ? $required_text : $required_text) => ($required ? $required_text : $required_text)
									)); ?>
								<?php } ?>
							</div>
						</div>
					<?php } //end if
					?>
				<?php } //end for loop
				?>


				<?php echo form_hidden('redirect_code', $redirect_code); ?>

				<div class="form-actions pull-right">
					<?php
					echo form_submit(array(
						'name' => 'submitf',
						'id' => 'submitf',
						'value' => lang('common_save'),
						'class' => 'btn submit_button floating-button btn-primary btn-lg float_right'
					));

					?>
				</div>

			</div>
		</div>

		<div class="panel panel-piluku">
			<div class="panel-heading">
				<h3 class="panel-title">
					<i class="ion-folder"></i>
					<?php echo lang("common_files"); ?>
				</h3>
			</div>

			<?php if (count($files)) { ?>
				<ul class="list-group">
					<?php foreach ($files as $file) { ?>
						<li class="list-group-item permission-action-item">

							<?php echo anchor($controller_name . '/delete_file/' . $file->file_id, '<i class="icon ion-android-cancel text-danger" style="font-size: 120%"></i>', array('class' => 'delete_file')); ?>
							<?php echo anchor($controller_name . '/download/' . $file->file_id, $file->file_name, array('target' => '_blank')); ?>
						</li>
					<?php } ?>
				</ul>
			<?php } ?>
			<h4 style="padding: 20px;"><?php echo lang('common_add_files'); ?></h4>
			<?php for ($k = 1; $k <= 5; $k++) { ?>
				<div class="form-group" style="padding-left: 10px;">
					<?php echo form_label(lang('common_file') . ' ' . $k . ':', 'files_' . $k, array('class' => 'col-sm-3 col-md-3 col-lg-2 control-label ')); ?>
					<div class="col-sm-9 col-md-9 col-lg-10">
						<div class="file-upload">
							<input type="file" name="files[]" id="files_<?php echo $k; ?>">
						</div>
					</div>
				</div>
			<?php } ?>
		</div>


		<div class="panel panel-piluku">
			<div class="panel-heading">
				<h3 class="panel-title">
					<i class="ion-locked"></i>
					<?php echo lang("common_login_info"); ?>
				</h3>
			</div>

			<div class="panel-body">
				<div class="form-group">
					<?php echo form_label(lang('common_username') . ':', 'username', array('class' => 'col-sm-3 col-md-3 col-lg-2 control-label required')); ?>
					<div class="col-sm-9 col-md-9 col-lg-10">
						<?php echo form_input(array(
							'name' => 'username',
							'id' => 'username',
							'class' => 'form-control',
							'value' => $person_info->username
						)); ?>
					</div>
				</div>

				<div class="form-group">
					<?php echo form_label(lang('common_password') . ':', 'password', array('class' => 'col-sm-3 col-md-3 col-lg-2 control-label')); ?>
					<div class="col-sm-9 col-md-9 col-lg-10">
						<?php echo form_password(array(
							'name' => 'password',
							'id' => 'password',
							'class' => 'form-control',
							'autocomplete' => 'off',
						)); ?>
					</div>
				</div>

				<div class="form-group">
					<?php echo form_label(lang('common_repeat_password') . ':', 'repeat_password', array('class' => 'col-sm-3 col-md-3 col-lg-2 control-label')); ?>
					<div class="col-sm-9 col-md-9 col-lg-10">
						<?php echo form_password(array(
							'name' => 'repeat_password',
							'id' => 'repeat_password',
							'class' => 'form-control',
							'autocomplete' => 'off',
						)); ?>
					</div>
				</div>


				<div class="form-group">
					<?php echo form_label(lang('employees_force_password_change_upon_login') . ':', 'force_password_change', array('class' => 'col-sm-3 col-md-3 col-lg-2 control-label')); ?>
					<div class="col-sm-9 col-md-9 col-lg-10">
						<?php
						echo	form_checkbox(array(
							'name' => 'force_password_change',
							'id' => 'force_password_change',
							'value' => 1,
							'checked' => $person_info->force_password_change,
						));
						echo '<label for="force_password_change"><span></span></label>';;
						?>
					</div>
				</div>

				<div class="form-group">
					<?php echo form_label(lang('employees_always_require_password') . ':', 'always_require_password', array('class' => 'col-sm-3 col-md-3 col-lg-2 control-label')); ?>
					<div class="col-sm-9 col-md-9 col-lg-10">
						<?php
						echo	form_checkbox(array(
							'name' => 'always_require_password',
							'id' => 'always_require_password',
							'value' => 1,
							'checked' => $person_info->always_require_password,
						));
						echo '<label for="always_require_password"><span></span></label>';;
						?>
					</div>
				</div>

				<?php if ($this->config->item('timeclock')) { ?>
					<div class="form-group">
						<?php echo form_label(lang('employees_not_required_to_clock_in') . ':', 'not_required_to_clock_in', array('class' => 'col-sm-3 col-md-3 col-lg-2 control-label')); ?>
						<div class="col-sm-9 col-md-9 col-lg-10">
							<?php
							echo	form_checkbox(array(
								'name' => 'not_required_to_clock_in',
								'id' => 'not_required_to_clock_in',
								'value' => 1,
								'checked' => $person_info->not_required_to_clock_in,
							));
							echo '<label for="not_required_to_clock_in"><span></span></label>';;
							?>
						</div>
					</div>
				<?php } ?>
				
				<div class="form-group">	
				<?php echo form_label(lang('common_dark_mode').':', 'dark_mode',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label')); ?>
					<div class="col-sm-9 col-md-9 col-lg-10">
						<?php
						echo	form_checkbox(array(
							'name' => 'dark_mode',
							'id' => 'dark_mode',
							'value' => 1,
							'checked' => $person_info->dark_mode,
							));
							echo '<label for="dark_mode"><span></span></label>';;
						?>
					</div>
				</div>
				
				
				<div class="form-group">	
				<?php echo form_label(lang('employees_inactive').':', 'inactive',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label')); ?>
					<div class="col-sm-9 col-md-9 col-lg-10">
						<?php
						echo	form_checkbox(array(
							'name' => 'inactive',
							'id' => 'inactive',
							'value' => 1,
							'checked' => $person_info->inactive,
						));
						echo '<label for="inactive"><span></span></label>';;
						?>
					</div>
				</div>

				<div id="inactive_info">
					<div class="form-group">
						<?php echo form_label(lang('employees_reason_inactive') . ':', 'reason_inactive', array('class' => 'col-sm-3 col-md-3 col-lg-2 control-label ')); ?>
						<div class="col-sm-9 col-md-9 col-lg-10">
							<?php echo form_textarea(array(
								'name' => 'reason_inactive',
								'id' => 'reason_inactive',
								'class' => 'form-control text-area',
								'value' => $person_info->reason_inactive,
								'rows' => '5',
								'cols' => '17'
							)); ?>
						</div>
					</div>

					<div class="form-group offset1">
						<?php echo form_label(lang('employees_termination_date') . ':', 'termination_date', array('class' => 'col-sm-3 col-md-3 col-lg-2 control-label text-info wide')); ?>
						<div class="col-sm-9 col-md-9 col-lg-10">
							<div class="input-group date">
								<span class="input-group-addon bg">
									<i class="ion ion-ios-calendar-outline"></i>
								</span>
								<?php echo form_input(array(
									'name' => 'termination_date',
									'id' => 'termination_date',
									'class' => 'form-control datepicker',
									'value' => $person_info->termination_date ? date(get_date_format(), strtotime($person_info->termination_date)) : ''
								)); ?>
							</div>
						</div>
					</div>
				</div>

				<div class="form-group">
				<?php echo form_label(lang('employees_acess_ip_range').':', 'employees_acess_ip_range',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label')); ?>
					<div class="col-sm-9 col-md-9 col-lg-10">
						<input id="allowed_ip_address" name='allowed_ip_address' value='<?php echo implode(",", $person_info->allowed_ip_address); ?>' placeholder=<?php echo json_encode(lang('employees_enter_ip'));?>; ?>
					</div>
				</div>
			</div>
		</div>

		<div class="panel panel-piluku">
			<div class="panel-heading">
				<h3 class="panel-title">
					<i class="ion-android-checkbox-outline"></i>
					<?php echo lang("employees_permission_info"); ?><br>
				</h3>
			</div>

			<div class="panel-body">

				<div class="alert alert-info text-center" role="alert">
					<?php echo lang("employees_permission_desc"); ?>
				</div>

				<?php 
					$templates = array('' => lang('common_none'));
					foreach($permission_templates->result() as $template){
						$templates[$template->id] = $template->name;
					}
				?>

				<div class="form-group">
					<?php echo form_label(lang('permission_templates') . ': ', 'permission_templates', array('class' => 'col-sm-3 col-md-3 col-lg-2 control-label')); ?>
					<div class="col-sm-9 col-md-9 col-lg-10">
						<?php echo form_dropdown(
							'permission_templates',
							$templates,
							$person_info->template_id,
							array(
								'class' => 'form-control',
								'id' => 'permission_templates'
							)
						)
						?>
					</div>
				</div>

				<?php
				foreach ($all_modules->result() as $module) {
					$checkbox_options = array(
						'name' => 'permissions[]',
						'id' => 'permissions' . $module->module_id,
						'value' => $module->module_id,
						'checked' => $this->Employee->has_module_permission($module->module_id, $person_info->person_id, FALSE, TRUE),
						'class' => 'module_checkboxes '
					);

					if ($logged_in_employee_id != 1) {
						if (($current_employee_editing_self && $checkbox_options['checked']) || !$this->Employee->has_module_permission($module->module_id, $logged_in_employee_id, FALSE, TRUE)) {
							$checkbox_options['disabled'] = 'disabled';

							//Only send permission if checked
							if ($checkbox_options['checked']) {
								echo form_hidden('permissions[]', $module->module_id);
							}
						}
					}
				?>
					<div class="panel panel-piluku">
						<div class="panel-heading list-group-item" id="<?php echo 'lmodule_' . $module->module_id; ?>">
							<?php echo form_checkbox($checkbox_options) . '<label for="permissions' . $module->module_id . '"><span></span></label>'; ?>
							<span class="text-success"><?php echo lang('module_' . $module->module_id); ?>:&nbsp;</span>
							<span class="text-warning"><?php echo lang('module_' . $module->module_id . '_desc'); ?></span>


							<span class="text-info pull-right">
								<div class="drop-down">
									<?php
									if ($this->Location->count_all() > 1) {
									?>
										<span style="color:#EAC841;" onclick="getEmployeeLocation('<?php echo 'lmodule_' . $module->module_id; ?>')" class="iconi" id="<?php echo 'lmodule_head' . $module->module_id; ?>" aria-haspopup="true">
											<i class="icon ti-location-pin arrow" aria-hidden="true"></i><?php echo lang('common_override_location'); ?>
										</span>
									<?php } ?>
									<div class="drop-menu">
										<div>
											<input onclick="selectAllLocation('select-all-<?php echo $module->module_id; ?>')" id="select-all-<?php echo $module->module_id; ?>" type="checkbox" name="<?php echo 'select-all-' . $module->module_id; ?>">
											<label for="select-all-<?php echo $module->module_id; ?>" class="text_align"><b>Select All</b></label>
										</div>
										<hr>

										<?php foreach ($locations as $lmk => $lmv) :
											$tmp_checkbox_id = 'module-location-' . $module->module_id . "-" . $lmk;
											$module_location_checkbox = array(
												'name' => "module_location[]",
												'id' => $tmp_checkbox_id,
												'value' => $module->module_id . "|" . $lmk,
												'checked' => $this->Employee->check_module_has_location($action_locations, $module->module_id, $lmk),
												'data-temp_name' => 'select-all-' . $module->module_id
											);

										?>
											<div>
												<?php echo form_checkbox($module_location_checkbox); ?>
												<label for="<?php echo 'module-location-' . $module->module_id . "-" . $lmk; ?>" class="text_align"><?php echo $lmv['name']; ?></label>
											</div>
										<?php endforeach; ?>

									</div>
								</div>
							</span>

						</div>

						<ul class="list-group">
							<?php
							foreach ($this->Module_action->get_module_actions($module->module_id)->result() as $mk => $module_action) {
								$checkbox_options = array(
									'name' => 'permissions_actions[]',
									'data-module-checkbox-id' => 'permissions' . $module->module_id,
									'class' => 'module_action_checkboxes',
									'id' => 'permissions_actions' . $module_action->module_id . "-" . $module_action->action_id,
									'value' => $module_action->module_id . "|" . $module_action->action_id,
									'checked' => $this->Employee->has_module_action_permission($module->module_id, $module_action->action_id, $person_info->person_id, FALSE, TRUE)
								);

								if ($logged_in_employee_id != 1) {
									if (($current_employee_editing_self && $checkbox_options['checked']) || (!$this->Employee->has_module_action_permission($module->module_id, $module_action->action_id, $logged_in_employee_id, FALSE, TRUE))) {
										$checkbox_options['disabled'] = 'disabled';

										//Only send permission if checked
										if ($checkbox_options['checked']) {
											echo form_hidden('permissions_actions[]', $module_action->module_id . "|" . $module_action->action_id);
										}
									}
								}

							?>
								<li class="list-group-item permission-action-item" id="<?php echo 'permissions-actions-' . $module_action->module_id . "-" . $module_action->action_id . '-ext-' . $mk; ?>">
									<?php echo form_checkbox($checkbox_options) . '<label for="permissions_actions' . $module_action->module_id . "-" . $module_action->action_id . '"><span></span></label>'; ?>
									<span class="text-info"><?php echo lang($module_action->action_name_key); ?></span>
									<span class="text-info pull-right">
										<div class="drop-down">

											<?php
											if ($this->Location->count_all() > 1) {
											?>
												<span class="iconi" onclick="getEmployeeLocation('<?php echo 'permissions-actions-' . $module_action->module_id . "-" . $module_action->action_id . '-ext-' . $mk; ?>')" aria-haspopup="true">
													<i class="icon ti-location-pin arrow" aria-hidden="true"></i><?php echo lang('common_override_location'); ?>
												</span>
											<?php } ?>
											<div class="drop-menu">
												<div>
													<input onclick="selectAllLocation('select-all-<?php echo $module_action->module_id . "-" . $module_action->action_id; ?>')" id="select-all-<?php echo $module_action->module_id . "-" . $module_action->action_id; ?>" type="checkbox" name="<?php echo 'select-all-' . $module_action->module_id . "-" . $module_action->action_id; ?>">
													<label for="select-all-<?php echo $module_action->module_id . "-" . $module_action->action_id; ?>" class="text_align"><b>Select All</b></label>
												</div>
												<hr>
												<?php
												foreach ($locations as $lk => $lv) :
													$checkbox_id = 'permissions-actions' . $lk . $module_action->module_id . "-" . $module_action->action_id . '-ext-' . $mk;
													$location_checkbox = array(
														'name' => "action-location[]",
														'id' => $checkbox_id,
														'value' => $module_action->module_id . "|" . $module_action->action_id . "|" . $lk,
														'checked' => $this->Employee->check_action_has_employee_location($action_locations, $module->module_id, $module_action->action_id, $lk),
														'data-temp_name' => 'select-all-' . $module_action->module_id . "-" . $module_action->action_id
													);
												?>
													<div>
														<?php echo form_checkbox($location_checkbox); ?>
														<label for="<?php echo $checkbox_id; ?>" class="text_align"><?php echo $lv['name']; ?></label>
													</div>
												<?php endforeach; ?>
											</div>
										</div>
									</span>
								</li>

							<?php } ?>
						</ul>
					</div>
				<?php } ?>

			</div>
		</div>
		<?php echo form_close(); ?>
	</div>
</div>
</div>

<script type='text/javascript'>
	$('#image_id').imagePreview({
		selector: '#avatar'
	}); // Custom preview container

	//validation and submit handling
	$(document).ready(function() {
		date_time_picker_field($(".datepicker"), JS_DATE_FORMAT + " " + JS_TIME_FORMAT);
		date_time_picker_field($(".timepicker"), JS_TIME_FORMAT);
		$("#inactive").change(check_inactive);

		check_inactive();

		function check_inactive() {
			if ($("#inactive").prop('checked')) {
				$("#inactive_info").show();
			} else {
				$("#inactive_info").hide();
			}
		}

		setTimeout(function() {
			$(":input:visible:first", "#employee_form").focus();
		}, 100);

		$(".module_checkboxes").change(function() {
			if ($(this).prop('checked')) {
				$(this).parent().parent().find('.module_action_checkboxes').not(':disabled').prop('checked', true);
			} else {
				$(this).parent().parent().find('.module_action_checkboxes').not(':disabled').prop('checked', false);
			}
		});

		$(".module_action_checkboxes").change(function() {
			if ($(this).prop('checked')) {
				$('#' + $(this).data('module-checkbox-id')).prop('checked', true);
			}
		});

		$('#employee_form').validate({
			submitHandler: function(form) {
				$.post('<?php echo site_url("employees/check_duplicate"); ?>', {
						term: $('#first_name').val() + ' ' + $('#last_name').val()
					}, function(data) {
						<?php if (!$person_info->person_id) { ?>
							if (data.duplicate) {
								bootbox.confirm(<?php echo json_encode(lang('employees_duplicate_exists')); ?>, function(result) {
									if (result) {
										doEmployeeSubmit(form);
									}
								});
							} else {
								doEmployeeSubmit(form);
							}
						<?php } else { ?>
							doEmployeeSubmit(form);
						<?php } ?>
					}, "json")
					.error(function() {});
			},
			ignore: '',
			errorClass: "text-danger",
			errorElement: "p",
			errorPlacement: function(error, element) {
				error.insertBefore(element);
			},
			highlight: function(element, errorClass, validClass) {
				$(element).parents('.form-group').removeClass('has-success').addClass('has-error');
			},
			unhighlight: function(element, errorClass, validClass) {
				$(element).parents('.form-group').removeClass('has-error').addClass('has-success');
			},
			rules: {
				first_name: "required",
				<?php for($k=1;$k<=NUMBER_OF_PEOPLE_CUSTOM_FIELDS;$k++) { 
					$custom_field = $this->Employee->get_custom_field($k);
					if($custom_field !== FALSE) {
						if( $this->Employee->get_custom_field($k,'required') && in_array($current_location, $this->Employee->get_custom_field($k,'locations'))){
							if(($this->Employee->get_custom_field($k,'type') == 'file' || $this->Employee->get_custom_field($k,'type') == 'image') && !$person_info->{"custom_field_${k}_value"}){
								echo "custom_field_${k}_value: 'required',\n";
							}
							
							if(($this->Employee->get_custom_field($k,'type') != 'file' && $this->Employee->get_custom_field($k,'type') != 'image')){
								echo "custom_field_${k}_value: 'required',\n";
							}
						}
					}
				}
					?>


				username: {
					<?php if (!$person_info->person_id) { ?>
						remote: {
							url: "<?php echo site_url('employees/exmployee_exists'); ?>",
							type: "post"
						},
					<?php } ?>
					required: true,
					minlength: 1
				},

				password: {
					<?php
					if ($person_info->person_id == "") {
					?>
						required: true,
					<?php
					}
					?>
					minlength: 1
				},
				repeat_password: {
					equalTo: "#password"
				},
				email: {
					"required": true
				},
				"locations[]": "required"
			},
			messages: {
				first_name: <?php echo json_encode(lang('common_first_name_required')); ?>,
				last_name: <?php echo json_encode(lang('common_last_name_required')); ?>,
				<?php for($k=1;$k<=NUMBER_OF_PEOPLE_CUSTOM_FIELDS;$k++) { 
					$custom_field = $this->Employee->get_custom_field($k);
					if($custom_field !== FALSE) {
						if( $this->Employee->get_custom_field($k,'required') && in_array($current_location, $this->Employee->get_custom_field($k,'locations'))){
							if(($this->Employee->get_custom_field($k,'type') == 'file' || $this->Employee->get_custom_field($k,'type') == 'image') && !$person_info->{"custom_field_${k}_value"}){
								$error_message = json_encode($custom_field." ".lang('is_required'));
								echo "custom_field_${k}_value: $error_message,\n";
							}

							if(($this->Employee->get_custom_field($k,'type') != 'file' && $this->Employee->get_custom_field($k,'type') != 'image')){
								$error_message = json_encode($custom_field." ".lang('is_required'));
								echo "custom_field_${k}_value: $error_message,\n";
							}
						}
					}
				}
				?>

				username: {
					<?php if (!$person_info->person_id) { ?>
						remote: <?php echo json_encode(lang('employees_username_exists')); ?>,
					<?php } ?>
					required: <?php echo json_encode(lang('common_username_required')); ?>,
					minlength: <?php echo json_encode(lang('common_username_minlength')); ?>
				},
				password: {
					<?php
					if ($person_info->person_id == "") {
					?>
						required: <?php echo json_encode(lang('employees_password_required')); ?>,
					<?php
					}
					?>
					minlength: <?php echo json_encode(lang('common_password_minlength')); ?>
				},
				repeat_password: {
					equalTo: <?php echo json_encode(lang('common_password_must_match')); ?>
				},
				email: <?php echo json_encode(lang('common_email_invalid_format')); ?>,
				"locations[]": <?php echo json_encode(lang('employees_one_location_required')); ?>
			}
		});

		$(document).on('change','#permission_templates',function(){
			$(".module_checkboxes, .module_action_checkboxes, input[name='action-location[]'], input[name='module_location[]']").prop('checked',false);
			
			var template_id = $(this).val();

			$.post('<?php echo site_url("employees/get_permission_template_wise_modules_actions_locations"); ?>', {
					template_id: template_id
				}, function(data) {
					console.log(data)
					$.each(data, function(key, value){
						if(value === true){
							$("#"+key).prop('checked',value);
						}
					});
				}, "json")
				.error(function() {});
			});
	});

	var submitting = false;

	function doEmployeeSubmit(form) {
		$("#grid-loader").show();
		if (submitting) return;
		submitting = true;

		$(form).ajaxSubmit({
			success: function(response) {
				$("#grid-loader").hide();
				submitting = false;
				if (response.redirect_code == 1 && response.success) {
					if (response.success) {
						show_feedback('success', response.message, <?php echo json_encode(lang('common_success')); ?>);
					} else {
						show_feedback('error', response.message, <?php echo json_encode(lang('common_error')); ?>);
					}
				} else if (response.redirect_code == 2 && response.success) {
					window.location.href = '<?php echo site_url('employees'); ?>';
				} else if (response.success) {
					show_feedback('success', response.message, <?php echo json_encode(lang('common_success')); ?>);
					$("html, body").animate({
						scrollTop: 0
					}, "slow");
					$(".form-group").removeClass('has-success has-error');
				} else {
					show_feedback('error', response.message, <?php echo json_encode(lang('common_error')); ?>);
					$("html, body").animate({
						scrollTop: 0
					}, "slow");
					$(".form-group").removeClass('has-success has-error');
				}
			},

			<?php if (!$person_info->person_id) { ?>
				resetForm: true,
			<?php } ?>
			dataType: 'json'
		});
	}

	$('.delete_file').click(function(e) {
		e.preventDefault();
		var $link = $(this);
		bootbox.confirm(<?php echo json_encode(lang('common_confirm_file_delete')); ?>, function(response) {
			if (response) {
				$.get($link.attr('href'), function() {
					$link.parent().fadeOut();
				});
			}
		});

	});


	function getEmployeeLocation(id) {
		var listid = ".list-group-item#" + id + " .drop-menu";
		var listarow = ".list-group-item#" + id + " .arrow";

		if ($(listid).hasClass('current')) {
			$('.drop-menu').removeClass('current');
		} else {
			$(listarow).animate({
				top: '-5px'
			});
			$(listarow).animate({
				top: '0px'
			});
			$(listarow).animate({
				top: '-5px'
			});
			$(listarow).animate({
				top: '0px'
			});
			$('.drop-menu').removeClass('current');
			$(listid).toggleClass('current');
		}
	}

	function selectAllLocation(id_name) {
		var name = ($('#' + id_name).attr("name"));

		if ($('#' + id_name).prop("checked") == true) {
			$('input[data-temp_name=' + name + ']').prop('checked', true);
		} else if ($('#' + id_name).prop("checked") == false) {
			$('input[data-temp_name=' + name + ']').prop('checked', false);
		}
	}

	$("#select_all").click(function(e) {

		if (!$(this).prop('checked')) {
			$(".location_checkboxes").prop('checked', false);
		} else {
			$(".location_checkboxes").prop('checked', true);
			check_boxes();
		}

	});
	$('.location_checkboxes').click(function() {
		check_boxes();
	});
	check_boxes();

	function check_boxes() {
		var total_checkboxes = $(".location_checkboxes").length;
		var checked_boxes = 0;
		$(".location_checkboxes").each(function(index) {
			if ($(this).prop('checked')) {
				checked_boxes++;
			}
		});

		if (checked_boxes == total_checkboxes) {
			$("#select_all").prop('checked', true);
		} else {
			$("#select_all").prop('checked', false);
		}
	}
	
	$("#allowed_ip_address").selectize({
		create: true,
		render: {
	      option_create: function(data, escape) {
				var add_new = <?php echo json_encode(lang('common_add_new_ip')) ?>;
	        return '<div class="create">'+escape(add_new)+' <strong>' + escape(data.input) + '</strong></div>';
	      }
		},
	});
	


</script>

<?php $this->load->view("partial/footer"); ?>
<style>
	.drop-menu input {
		display: inline-block;
	}

	.list-group-item .iconi {
		position: relative;
		top: 0;
		left: -15px;
		transform: translate(-50%, -50%);
		width: 80px;
		height: 60px;
		cursor: pointer;
	}

	.list-group-item .arrow {
		position: absolute;
		top: 0;
		left: -15px;
		animation: arrow 700ms linear infinite;
	}

	.list-group-item .open>.dropdown-menu {
		display: grid;
		position: relative;
		padding: 5px;
		left: -45px;
	}

	.list-group-item .open>.dropdown-menu:before {
		position: absolute;
		display: block;
		content: '';
		bottom: 100%;
		top: 5px;
		right: -4px;
		width: 7px;
		height: 7px;
		margin-bottom: -4px;
		border-top: 1px solid #b5b5b5;
		border-right: 1px solid #b5b5b5;
		background: #fff;
		transform: rotate(45deg);
		transition: all .4s ease-in-out;
	}

	.list-group-item .drop-down,
	.dropup {
		position: relative;
	}

	.list-group-item .drop-menu {
		position: absolute;
		top: 100%;
		right: 95px;
		z-index: 1000;
		visibility: hidden;
		float: left;
		min-width: 160px;
		padding: 8px;
		margin: 2px 0 0;
		font-size: 14px;
		text-align: left;
		list-style: none;
		background-color: #fff;
		-webkit-background-clip: padding-box;
		background-clip: padding-box;
		border: 1px solid #ccc;
		border: 1px solid rgba(0, 0, 0, .15);
		border-radius: 4px;
		-webkit-box-shadow: 0 6px 12px rgba(0, 0, 0, .175);
		box-shadow: 0 6px 12px rgba(0, 0, 0, .175);
		width: max-content;
		top: 20px;
		opacity: 0;
	}

	.list-group-item .current.drop-menu {
		visibility: visible;
		top: -3px;
		transition: all .6s;
		opacity: 1;
	}

	.list-group-item input+label {
		font-weight: 400;
		cursor: pointer;
	}

	.list-group-item input:checked+label {
		font-weight: 600;
		color: #6cadd1;
	}

	.list-group-item .drop-menu.current:before {
		position: absolute;
		display: block;
		content: '';
		bottom: 100%;
		top: 5px;
		right: -4px;
		width: 7px;
		height: 7px;
		margin-bottom: -4px;
		border-top: 1px solid #b5b5b5;
		border-right: 1px solid #b5b5b5;
		background: #fff;
		transform: rotate(45deg);
		transition: all .4s ease-in-out;
	}

	.list-group-item .text_align {
		transform: translateY(-2px);
		display: inline-block;
	}

	.list-group-item .text-info {
		margin-top: 8px;
	}

	.list-group-item i.icon.ti-location-pin.arrow {
		transform: translateY(2px);
	}

	.list-group-item hr {
		margin-top: 3px;
		margin-bottom: 8px;
		border: 0;
		border-top: 1px solid #eeeeee;
	}
</style>