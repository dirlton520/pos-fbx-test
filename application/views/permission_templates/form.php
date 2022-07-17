<?php $this->load->view("partial/header"); ?>
<div class="row" id="form">
	<div class="spinner" id="grid-loader" style="display:none">
		<div class="rect1"></div>
		<div class="rect2"></div>
		<div class="rect3"></div>
	</div>
	<div class="col-md-12">
		<?php
		echo form_open_multipart('permission_templates/save/' . (isset($template_info->id) ? $template_info->id : ''), array('id' => 'template_form', 'class' => 'form-horizontal'));
		?>

		<div class="panel panel-piluku">
			<div class="panel-heading">
				<h3 class="panel-title">
					<i class="ion-edit"></i>
					<?php echo lang("permission_templates_new"); ?>
					<small>(<?php echo lang('common_fields_required_message'); ?>)</small>
				</h3>
			</div>

			<div class="panel-body">

				<div class="form-group">
					<?php
					$required = "required";
					echo form_label(lang('permission_template_name') . ':', 'permission_template_name', array('class' => $required . ' col-sm-3 col-md-3 col-lg-2 control-label ')); ?>
					<div class="col-sm-9 col-md-9 col-lg-10">
						<?php echo form_input(array(
							'class' => 'form-control',
							'name' => 'permission_template_name',
							'id' => 'permission_template_name',
							'value' => $template_info->name
						)); ?>
					</div>
				</div>
				<?php if ($template_id != -1) { ?>
					<div class="form-group">
						<?php echo form_label(lang('update_all_employees_with_template_assinged') . ':', 'update_all_employees_with_template_assinged', array('class' => 'col-sm-3 col-md-3 col-lg-2 control-label ')); ?>
						<div class="col-sm-9 col-md-9 col-lg-10">
							<?php
							$update_all_employees = array(
								'name' => "update_all_employees_with_template_assinged",
								'id' => 'update_all_employees_with_template_assinged',
								'value' => 1
							);

							echo form_checkbox($update_all_employees);
							?>
							<label for="update_all_employees_with_template_assinged"><span></span></label>
						</div>
					</div>
				<?php } ?>
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

				<div class="alert alert-info text-center" role="alert">
					<?php echo lang("template_permission_desc"); ?>
				</div>

				<?php
				foreach ($all_modules->result() as $module) {
					$checkbox_options = array(
						'name' => 'permissions[]',
						'id' => 'permissions' . $module->module_id,
						'value' => $module->module_id,
						'checked' => $this->Permission_template->has_module_permission($module->module_id, $template_info->id, FALSE, TRUE),
						'class' => 'module_checkboxes '
					);

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
										<span style="color:#EAC841;" onclick="getTemplateLocation('<?php echo 'lmodule_' . $module->module_id; ?>')" class="iconi" id="<?php echo 'lmodule_head' . $module->module_id; ?>" aria-haspopup="true">
											<i class="icon ti-location-pin arrow" aria-hidden="true"></i><?php echo lang('common_override_location'); ?>
										</span>
									<?php } ?>
									<div class="drop-menu">
										<div>
											<input onclick="selectAllLocation('select-all-<?php echo $module->module_id; ?>')" id="select-all-<?php echo $module->module_id; ?>" type="checkbox" name="<?php echo 'select-all-' . $module->module_id; ?>">
											<label for="select-all-<?php echo $module->module_id; ?>" class="text_align"><b>Select All</b></label>
										</div>
										<hr>

										<?php foreach ($locations as $lmk => $lmv) {
											$tmp_checkbox_id = 'module-location-' . $module->module_id . "-" . $lmk;
											$module_location_checkbox = array(
												'name' => "module_location[]",
												'id' => $tmp_checkbox_id,
												'value' => $module->module_id . "|" . $lmk,
												'checked' => $this->Permission_template->check_module_has_location($action_locations, $module->module_id, $lmk),
												'data-temp_name' => 'select-all-' . $module->module_id
											);

										?>
											<div>
												<?php echo form_checkbox($module_location_checkbox); ?>
												<label for="<?php echo 'module-location-' . $module->module_id . "-" . $lmk; ?>" class="text_align"><?php echo $lmv['name']; ?></label>
											</div>
										<?php } ?>

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
									'id' => 'permissions_actions' . $module_action->module_id . "|" . $module_action->action_id,
									'value' => $module_action->module_id . "|" . $module_action->action_id,
									'checked' => $this->Permission_template->has_module_action_permission($module->module_id, $module_action->action_id, $template_info->id, FALSE, TRUE)
								);
							?>
								<li class="list-group-item permission-action-item" id="<?php echo 'permissions-actions-' . $module_action->module_id . "-" . $module_action->action_id . '-ext-' . $mk; ?>">
									<?php echo form_checkbox($checkbox_options) . '<label for="permissions_actions' . $module_action->module_id . "|" . $module_action->action_id . '"><span></span></label>'; ?>
									<span class="text-info"><?php echo lang($module_action->action_name_key); ?></span>
									<span class="text-info pull-right">
										<div class="drop-down">

											<?php
											if ($this->Location->count_all() > 1) {
											?>
												<span class="iconi" onclick="getTemplateLocation('<?php echo 'permissions-actions-' . $module_action->module_id . "-" . $module_action->action_id . '-ext-' . $mk; ?>')" aria-haspopup="true">
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
												foreach ($locations as $lk => $lv) {
													$checkbox_id = 'permissions-actions' . $lk . $module_action->module_id . "-" . $module_action->action_id . '-ext-' . $mk;
													$location_checkbox = array(
														'name' => "action-location[]",
														'id' => $checkbox_id,
														'value' => $module_action->module_id . "|" . $module_action->action_id . "|" . $lk,
														'checked' => $this->Permission_template->check_action_has_template_location($action_locations, $module->module_id, $module_action->action_id, $lk),
														'data-temp_name' => 'select-all-' . $module_action->module_id . "-" . $module_action->action_id
													);
												?>
													<div>
														<?php echo form_checkbox($location_checkbox); ?>
														<label for="<?php echo $checkbox_id; ?>" class="text_align"><?php echo $lv['name']; ?></label>
													</div>
												<?php } ?>
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
	//validation and submit handling
	$(document).ready(function() {

		setTimeout(function() {
			$(":input:visible:first", "#template_form").focus();
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

		$('#template_form').validate({
			submitHandler: function(form) {
				$.post('<?php echo site_url("permission_templates/check_duplicate"); ?>', {
						term: $('#permission_template_name').val(),
					}, function(data) {
						<?php if (!$template_info->id) { ?>
							if (data.duplicate) {
								bootbox.confirm(<?php echo json_encode(lang('permission_template_duplicate_exists')); ?>, function(result) {
									if (result) {
										doTemplateSubmit(form);
									}
								});
							} else {
								doTemplateSubmit(form);
							}
						<?php } else { ?>
							doTemplateSubmit(form);
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
				permission_template_name: "required",
				"locations[]": "required"
			},
			messages: {
				permission_template_name: <?php echo json_encode(lang('permission_template_name_required')); ?>,
				"locations[]": <?php echo json_encode(lang('template_one_location_required')); ?>
			}
		});
	});

	var submitting = false;

	function doTemplateSubmit(form) {
		$("#grid-loader").show();
		if (submitting) return;
		submitting = true;

		$(form).ajaxSubmit({
			success: function(response) {
				$("#grid-loader").hide();
				submitting = false;
				if (response.success) {
					show_feedback('success', response.message, <?php echo json_encode(lang('common_success')); ?>);
					$("html, body").animate({
						scrollTop: 0
					}, "slow");
					$(".form-group").removeClass('has-success has-error');
					window.location.href = '<?php echo site_url('permission_templates'); ?>';
				} else {
					show_feedback('error', response.message, <?php echo json_encode(lang('common_error')); ?>);
					$("html, body").animate({
						scrollTop: 0
					}, "slow");
					$(".form-group").removeClass('has-success has-error');
				}
			},
			<?php if (!$template_info->id) { ?>
				resetForm: true,
			<?php } ?>
			dataType: 'json'
		});
	}


	function getTemplateLocation(id) {
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