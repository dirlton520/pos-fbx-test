<?php $this->load->view("partial/header"); ?>
<script type="text/javascript">
	function reload_people_table() {
		clearSelections();
		$("#table_holder").load(<?php echo json_encode(site_url("$controller_name/reload_table")); ?>);
	}


	$(document).ready(function() {
		$("#location_id").select2({
			dropdownAutoWidth: true
		});

		enable_sorting("<?php echo site_url("$controller_name/sorting"); ?>");
		enable_select_all();
		enable_checkboxes();
		enable_row_selection();
		enable_search('<?php echo site_url("$controller_name"); ?>', <?php echo json_encode(lang("common_confirm_search")); ?>);

		<?php if (!$deleted) { ?>
			enable_delete(<?php echo json_encode(lang($controller_name . "_confirm_delete")); ?>, <?php echo json_encode(lang($controller_name . "_none_selected")); ?>);
		<?php } else { ?>
			enable_delete(<?php echo json_encode(lang($controller_name . "_confirm_undelete")); ?>, <?php echo json_encode(lang($controller_name . "_none_selected")); ?>);
		<?php } ?>

		<?php if ($this->session->flashdata('manage_success_message')) { ?>
			show_feedback('success', <?php echo json_encode($this->session->flashdata('manage_success_message')); ?>, <?php echo json_encode(lang('common_success')); ?>);
		<?php } ?>
	});
</script>
<div class="manage_buttons">

	<!-- Css Loader  -->
	<div class="spinner" id="ajax-loader" style="display:none">
		<div class="rect1"></div>
		<div class="rect2"></div>
		<div class="rect3"></div>
	</div>

	<div class="manage-row-options hidden">
		<div class="email_buttons people">
			<?php if (!$deleted) { ?>

				<?php if ($this->Employee->has_module_action_permission('employees', 'delete', $this->Employee->get_logged_in_employee_info()->person_id)) { ?>
					<?php echo anchor(
						"$controller_name/delete",
						'<span class="ion-trash-a"></span> ' . '<span class="hidden-xs">' . lang("common_delete") . '</span>',
						array('id' => 'delete', 'class' => 'btn btn-red btn-lg disabled delete_inactive', 'title' => lang("common_delete"))
					); ?>
				<?php } ?>
				<a href="#" class="btn btn-lg btn-clear-selection btn-warning"><span class="ion-close-circled"></span> <span class="hidden-xs"><?php echo lang('common_clear_selection'); ?></span></a>
			<?php } else { ?>
				<?php if ($this->Employee->has_module_action_permission('employees', 'delete', $this->Employee->get_logged_in_employee_info()->person_id)) { ?>
					<?php echo anchor(
						"$controller_name/undelete",
						'<span class="ion-trash-a"></span> ' . '<span class="hidden-xs">' . lang("common_undelete") . '</span>',
						array('id' => 'delete', 'class' => 'btn btn-green btn-lg disabled delete_inactive', 'title' => lang("common_undelete"))
					); ?>
				<?php } ?>
				<a href="#" class="btn btn-lg btn-clear-selection btn-warning"><span class="ion-close-circled"></span> <?php echo lang('common_clear_selection'); ?></a>		
			<?php } ?>
		</div>
	</div>
	<div class="row">
		<div class="col-md-8 col-sm-8 col-xs-8">
			<?php echo form_open("$controller_name/search", array('id' => 'search_form', 'autocomplete' => 'off')); ?>
			<div class="search no-left-border">
				<ul class="list-inline">
					<li>
						<input type="text" class="form-control" name='search' id='search' value="<?php echo H($search); ?>" placeholder="<?php echo $deleted ? lang('common_search_deleted') : lang('common_search'); ?> <?php echo lang('module_' . $controller_name); ?>" />
					</li>

					<li>
						<button type="submit" class="btn btn-primary btn-lg"><span class="ion-ios-search-strong"></span><span class="hidden-xs hidden-sm"> <?php echo lang("common_search"); ?></span></button>
					</li>
					<li>
						<div class="clear-block <?php echo ($search == '') ? 'hidden' : ''  ?>">
							<a class="clear" href="<?php echo site_url($controller_name . '/clear_state'); ?>">
								<i class="ion ion-close-circled"></i>
							</a>
						</div>
					</li>
				</ul>
			</div>
			</form>

		</div>
		<div class="col-md-4 col-sm-4 col-xs-4">
			<div class="buttons-list">
				<div class="pull-right-btn">
					<?php if ($this->Employee->has_module_action_permission('employees', 'add_update', $this->Employee->get_logged_in_employee_info()->person_id) && !$deleted) { ?>
					<?php echo anchor(
							"$controller_name/view/-1/",
							'<span class="ion-plus"> ' . lang($controller_name . '_new') . '</span>',
							array('id' => 'new-person-btn', 'class' => 'btn btn-primary btn-lg hidden-sm hidden-xs', 'title' => lang($controller_name . '_new'))
						);
					}
					?>
					<?php if ($deleted) {
						echo
							anchor(
								"$controller_name/toggle_show_deleted/0",
								'<span class="ion-android-exit"></span> <span class="hidden-xs">' . lang('common_done') . '</span>',
								array('class' => 'btn btn-primary btn-lg toggle_deleted', 'title' => lang('common_done'))
							);
					}
					?>

					<?php if(!$deleted){ ?> 
						<div class="piluku-dropdown btn-group">
							<button type="button" class="btn btn-more dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
								<span class="hidden-xs ion-android-more-horizontal"> </span>
								<i class="visible-xs ion-android-more-vertical"></i>
							</button>
							<ul class="dropdown-menu" role="menu">

							<?php if ($this->Employee->has_module_action_permission('employees', 'add_update', $this->Employee->get_logged_in_employee_info()->person_id)) { ?>
								<li class="visible-sm visible-xs">
									<?php echo anchor("$controller_name/view/-1/", '<span class="ion-plus-round"> '.lang('common_add').' '.lang($controller_name.'_new').'</span>',
										array('class'=>'', 'title'=>lang($controller_name.'_new'))); ?>
								</li>
							<?php } ?>

							<?php if ($this->Employee->has_module_action_permission('employees', 'delete', $this->Employee->get_logged_in_employee_info()->person_id)) {?>
								<?php if ($controller_name =='permission_templates') {?>
								<li>
									<?php echo anchor("$controller_name/toggle_show_deleted/1", '<span class="ion-trash-a"> '.lang($controller_name."_manage_deleted").'</span>',
										array('class'=>'toggle_deleted','title'=> lang($controller_name."_manage_deleted"))); ?>
								</li>
								<?php } ?>
							<?php } ?>

							</ul>
						</div>
					<?php } ?>

				</div>
			</div>
		</div>
	</div>
</div>

<div class="container-fluid">
	<div class="row manage-table">
		<div class="panel panel-piluku">
			<div class="panel-heading">
				<h3 class="panel-title">
					<?php echo ($deleted ? lang('common_deleted') . ' ' : '') . lang('module_' . $controller_name); ?>
					<span title="<?php echo $total_rows; ?> total <?php echo $controller_name ?>" class="badge bg-primary tip-left" id="manage_total_items"><?php echo $total_rows; ?></span>

					<span class="panel-options custom">
						<div class="pagination pagination-top hidden-print  text-center" id="pagination_top">
							<?php echo $pagination; ?>
						</div>
					</span>
				</h3>
			</div>
			<div class="panel-body nopadding table_holder table-responsive" id="table_holder">
				<?php echo $manage_table; ?>
			</div>

		</div>
		<div class="text-center">
			<div class="pagination hidden-print alternate text-center" id="pagination_bottom">
				<?php echo $pagination; ?>
			</div>
		</div>
	</div>
</div>
<?php $this->load->view("partial/footer"); ?>