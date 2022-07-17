<?php $this->load->view("partial/header"); ?>

<script type="text/javascript">
	
	function check_filters_active_inactive()
	{
		var $checkboxs = $("#config_filters a").find("input[type=checkbox]");
	
		if($checkboxs.filter(':checked').length > 0 || $('#shipping_start').data("DateTimePicker").date() !== null || $('#delivery_start').data("DateTimePicker").date() !== null)
		{
			$("#config_filter_btn").addClass('active');
		} else {
			$("#config_filter_btn").removeClass('active');	
		}	
	}
	
	function save_filters()
	{
		
		$("form#config_filters input[type=hidden]").each(function (i) {
			if (this.value == '') {
				$(this).attr("disabled",true);
			} else {
				$(this).attr("disabled",false);
			}
    	});
								
		$("#config_filters").ajaxSubmit({
			success:function(response)
			{
				reload_delivery_table();
			},
			dataType:'json',
			resetForm: false
		});
	}
	
	function date_time_callback()
	{
		check_filters_active_inactive();
		save_filters();
	}
	
	function reload_delivery_table()
	{
		clearSelections();
		$("#table_holder").load(<?php echo json_encode(site_url("$controller_name/reload_delivery_table")); ?>);
	}
	
	$(document).ready(function()
	{	
		$("#sortable").sortable({
			items : '.sort',
			containment: "#sortable",
			cursor: "move",
			handle: ".handle",
			revert: 100,
			update: function( event, ui ) {
				$input = ui.item.find("input[type=checkbox]");
				$input.trigger('change');
			}
		});
		
		$("#sortable").disableSelection();
		
		
		$("#config_filters a.filter_action").on("click", function(e) {
			e.preventDefault();
			
			var $checkboxs = $("#config_filters a").find("input[type=checkbox]");
			
			if($(this).attr("id") == "reset_filters_to_default")
			{
				$checkboxs.prop("checked", false);
				$('#shipping_start').data("DateTimePicker").clear();
				$('#shipping_end').data("DateTimePicker").clear();
				
				$('#delivery_start').data("DateTimePicker").clear();
				$('#delivery_end').data("DateTimePicker").clear();
			}
			
			var $checkbox = $(this).find("input[type=checkbox]");
			if($checkbox.length == 1)
			{
				$checkbox.prop("checked", !$checkbox.prop("checked")).trigger("change");
			}
						
			check_filters_active_inactive();
			save_filters();
			
			return false;
		});

		$(document).on(
		    'click.bs.dropdown.data-api', 
		    '[data-toggle="collapse"]', 
		    function (e) { e.stopPropagation() }
		);
		
		$("#config_columns a").on("click", function(e) {
			e.preventDefault();
			
			if($(this).attr("id") == "reset_to_default")
			{
				//Send a get request wihtout columns will clear column prefs
				$.get(<?php echo json_encode(site_url("$controller_name/save_column_prefs")); ?>, function()
				{
					reload_delivery_table();
					var $checkboxs = $("#config_columns a").find("input[type=checkbox]");
					$checkboxs.prop("checked", false);
					
					<?php foreach($default_columns as $default_col) { ?>
							$("#config_columns a").find('#'+<?php echo json_encode($default_col);?>).prop("checked", true);
					<?php } ?>
				});
			}
			
			if(!$(e.target).hasClass("handle"))
			{
				var $checkbox = $(this).find("input[type=checkbox]");
				
				if($checkbox.length == 1)
				{
					$checkbox.prop("checked", !$checkbox.prop("checked")).trigger("change");
				}
			}
			
			return false;
		});
		
		
		$("#config_columns input[type=checkbox]").change(
			function(e) {
				var columns = $("#config_columns input:checkbox:checked").map(function(){
      		return $(this).val();
    		}).get();
				
				$.post(<?php echo json_encode(site_url("$controller_name/save_column_prefs")); ?>, {columns:columns}, function(json)
				{
					reload_delivery_table();
				});
				
		});
		
		
		enable_sorting("<?php echo site_url("$controller_name/sorting"); ?>");
		enable_select_all();
		enable_checkboxes();
		enable_row_selection();
		enable_search('<?php echo site_url("$controller_name");?>',<?php echo json_encode(lang("common_confirm_search"));?>);
		
		<?php if(!$deleted) { ?>
			enable_delete(<?php echo json_encode(lang($controller_name."_confirm_delete"));?>,<?php echo json_encode(lang($controller_name."_none_selected"));?>);
		<?php } else { ?>
			enable_delete(<?php echo json_encode(lang($controller_name."_confirm_undelete"));?>,<?php echo json_encode(lang($controller_name."_none_selected"));?>);
		<?php } ?>
	});
</script>


<div class="manage_buttons">
<div class="manage-row-options hidden">
	<div class="email_buttons deliveries text-center">		
		
	<?php if(!$deleted) { ?>
		<?php if ($this->Employee->has_module_action_permission($controller_name, 'delete', $this->Employee->get_logged_in_employee_info()->person_id)) {?>
		<?php echo anchor("$controller_name/delete",
			'<span class="ion-trash-a"></span> <span class="hidden-xs">'.lang('common_delete').'</span>'
			,array('id'=>'delete', 'class'=>'btn btn-red btn-lg disabled delete_inactive ','title'=>lang("common_delete"))); ?>
		<?php } ?>

		<a href="#" class="btn btn-lg btn-clear-selection btn-warning"><span class="ion-close-circled"></span> <span class="hidden-xs"><?php echo lang('common_clear_selection'); ?></span></a>
		
	<?php } else { ?>
			<?php if ($this->Employee->has_module_action_permission($controller_name, 'delete', $this->Employee->get_logged_in_employee_info()->person_id)) {?>
			<?php echo anchor("$controller_name/undelete",
					'<span class="ion-trash-a"></span> '.'<span class="hidden-xs">'.lang("common_undelete").'</span>',
					array('id'=>'delete','class'=>'btn btn-green btn-lg disabled delete_inactive','title'=>lang("common_undelete"))); ?>
			<?php } ?>

			<a href="#" class="btn btn-lg btn-clear-selection btn-warning"><span class="ion-close-circled"></span> <?php echo lang('common_clear_selection'); ?></a>		
	<?php } ?>
	
	<?php if ($this->Employee->has_module_action_permission($controller_name, 'edit', $this->Employee->get_logged_in_employee_info()->person_id)) {

		$statuses = array('' => lang('common_change_status'));

		foreach($delivery_statuses as $status_id => $status){
			$statuses[$status_id] = $status['name'];
		}

		echo form_dropdown('change_status', $statuses, '', 'class="form-control change_delivery_status" id="change_status"'); 
	?>


	<div class="piluku-dropdown btn-group">
		<ul class="dropdown-menu" role="menu">
				<?php //foreach($delivery_statuses as $status_key=>$status_text) { ?>
				<li>
					<?php //echo anchor("$controller_name/set_status/$status_key", '<span class="ion-android-checkmark-circle"></span> '.'<span>'.$status_text['name'].'</span>', array('class'=>'btn btn-lg status_change','title'=>$status_text['name'])); ?>
					
				</li>
			<?php //} ?>
		</ul>
	</div>
	
	<?php } ?>
		
	</div>
</div>

	<div class="row">
		<div class="col-md-8 col-sm-10 col-xs-10">
			<?php echo form_open("$controller_name/search",array('id'=>'search_form', 'autocomplete'=> 'off')); ?>
				<div class="search no-left-border">
					<input type="text" class="form-control" name ='search' id='search' value="<?php echo H($search); ?>" placeholder="<?php echo $deleted ? lang('common_search_deleted') : lang('common_search'); ?> <?php echo lang('module_'.$controller_name); ?>"/>
				</div>
					<div class="clear-block <?php echo ($search=='') ? 'hidden' : ''  ?>">
						<a class="clear" href="<?php echo site_url($controller_name.'/clear_state'); ?>">
							<i class="ion ion-close-circled"></i>
						</a>	
					</div>
			</form>	
		</div>
		<div class="col-md-4 col-sm-2 col-xs-2">	
			<div class="buttons-list">
				<div class="pull-right-btn">
					<!-- right buttons-->
					
					<?php if ($this->Employee->has_module_action_permission($controller_name, 'add_update', $this->Employee->get_logged_in_employee_info()->person_id) && !$deleted) {?>
					<?php echo anchor("$controller_name/view/-1/",
						'<span class="ion-plus"> '.lang($controller_name.'_new').'</span>',
						array('id' => 'new-person-btn', 'class'=>'btn btn-primary btn-lg hidden-sm hidden-xs', 'title'=>lang($controller_name.'_new')));
					}	
					?>

					
					<div class="piluku-dropdown btn-group">
					<button type="button" class="btn btn-more dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
						<span class="visible-xs ion-android-more-vertical"></span>
						<span class="hidden-xs"><span class="ion-calendar"></span> <?php echo lang('deliveries_calendars'); ?></span>
					</button>
					<ul class="dropdown-menu" role="menu">
						
						<?php if ($this->Employee->has_module_action_permission($controller_name, 'delete', $this->Employee->get_logged_in_employee_info()->person_id)) {?>
							<li>
									<?php echo anchor("$controller_name/toggle_show_deleted/1", '<span class="ion-trash-a"> '.lang($controller_name."_manage_deleted").'</span>',
										array('class'=>'toggle_deleted visible-xs','title'=> lang($controller_name."_manage_deleted"))); ?>
							</li>
						<?php }?>

						<?php if ($this->Employee->has_module_action_permission($controller_name, 'manage_categories', $this->Employee->get_logged_in_employee_info()->person_id)) {?>
						<li>
							<?php echo anchor("$controller_name/manage_categories?redirect=deliveries",
							'<span class="ion-ios-folder-outline"> ' . lang("items_manage_categories").'</span>',
							array('class'=>'visible-xs', 'title'=>lang('items_manage_categories')));
							?>
						</li>
						<?php } ?>

						<?php if ($this->Employee->has_module_action_permission($controller_name, 'manage_statuses', $this->Employee->get_logged_in_employee_info()->person_id)) {?>				
							<li>
								<?php echo anchor("$controller_name/manage_statuses?redirect=deliveries",'<span class="ion-settings"> '.lang('module_manage_statuses').'</span>',
									array('class'=>'manage_statuses visible-xs','title'=>lang('module_manage_statuses'))); ?>
							</li>
						<?php } ?>
						
						<?php foreach($date_fields as $date_field_choice_value => $date_field_choice_display) { ?>
								<li>
									<?php echo anchor('deliveries/calendar/'.$date_field_choice_value.'/', $date_field_choice_display)?>
								</li>
						<?php } ?>
					</ul>
					</div>
					
					<?php if($deleted) { 
						echo 
						anchor("$controller_name/toggle_show_deleted/0",
							'<span class="ion-android-exit"></span> <span class="hidden-xs">'.lang('common_done').'</span>',
							array('class'=>'btn btn-primary btn-lg toggle_deleted','title'=> lang('common_done')));
					} ?>
					
					<?php if(!$deleted) { ?>
								
					<div class="piluku-dropdown btn-group hidden-xs">
						<button type="button" class="btn btn-more dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
						<span class="hidden-xs ion-android-more-horizontal"> </span>
						<i class="visible-xs ion-android-more-vertical"></i>
					</button>
					<ul class="dropdown-menu" role="menu">
						<?php if ($this->Employee->has_module_action_permission($controller_name, 'delete', $this->Employee->get_logged_in_employee_info()->person_id)) {?>
							<li>
									<?php echo anchor("$controller_name/toggle_show_deleted/1", '<span class="ion-trash-a"> '.lang($controller_name."_manage_deleted").'</span>',
										array('class'=>'toggle_deleted','title'=> lang($controller_name."_manage_deleted"))); ?>
							</li>
						<?php } ?>


						<?php if ($this->Employee->has_module_action_permission($controller_name, 'manage_categories', $this->Employee->get_logged_in_employee_info()->person_id)) {?>
						<li>
							<?php echo anchor("$controller_name/manage_categories?redirect=deliveries",
							'<span class="ion-ios-folder-outline"> ' . lang("items_manage_categories").'</span>',
							array('class'=>'', 'title'=>lang('items_manage_categories')));
							?>
						</li>
						<?php } ?>

						<?php if ($this->Employee->has_module_action_permission($controller_name, 'manage_statuses', $this->Employee->get_logged_in_employee_info()->person_id)) {?>				
							<li>
								<?php echo anchor("$controller_name/manage_statuses?redirect=deliveries",'<span class="ion-settings"> '.lang('module_manage_statuses').'</span>',
									array('class'=>'manage_statuses','title'=>lang('module_manage_statuses'))); ?>
							</li>
						<?php } ?>

						<li>
							<?php echo anchor("$controller_name/manage_template?redirect=deliveries",'<span class="ion-email"> '.lang('deliveries_manage_email_template').'</span>',
									array('class'=>'manage_statuses','title' => lang('deliveries_manage_email_template'))); ?>
							</li>

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
					<?php echo ($deleted ? lang('common_deleted').' ' : '').lang('module_'.$controller_name); ?>
					
					<form id="config_columns">
						<div class="piluku-dropdown btn-group table_buttons pull-right">
							<button type="button" class="btn btn-more dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
								<i class="ion-gear-a"></i>
							</button>

							<ul id="sortable" class="dropdown-menu dropdown-menu-left col-config-dropdown" role="menu">
									<li class="dropdown-header"><a id="reset_to_default" class="pull-right"><span class="ion-refresh"></span> <?php echo lang('common_reset'); ?></a> <?php echo lang('common_column_configuration'); ?></li>
											
									<?php foreach($all_columns as $col_key => $col_value) { 
										$checked = '';
			
										if (isset($selected_columns[$col_key]))
										{
											$checked = 'checked ="checked" ';
										}
										?>
										<li class="sort"><a><input <?php echo $checked; ?> name="selected_columns[]" type="checkbox" class="columns" id="<?php echo $col_key; ?>" value="<?php echo $col_key; ?>"><label class="sortable_column_name" for="<?php echo $col_key; ?>"><span></span><?php echo H($col_value['label']); ?></label><span class="handle ion-drag"></span></a></li>									
									<?php } ?>
								</ul>
						</div>
						</form>
						
						<form id="config_filters" method="post" action="<?php echo site_url('deliveries/save_filters'); ?>">
						<div id="filter_dropdown_widget" class="piluku-dropdown btn-group table_buttons pull-right keepopen">
							<button id="config_filter_btn" type="button" class="btn btn-more dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
								<i class="ion-funnel"></i>
							</button>

							<ul id="filter_dropdown" class="dropdown-menu dropdown-menu-left col-config-dropdown" role="menu">
													
									<li class="dropdown-header no-border filter-header-top">
										<a id="reset_filters_to_default" class="pull-right filter_action"><span class="ion-refresh"></span> Reset</a><?php echo lang('common_column_filters'); ?>
									</li>
									
									<span class="panel">
									<li data-toggle="collapse" data-target="#status_container" data-parent="#filter_dropdown" class="dropdown-header filter-header"><i class="plus-minus expand-collapse-icon glyphicon glyphicon-plus"></i> <?php echo lang('deliveries_status'); ?> :</li>
									<li id="status_container" class="collapse in">
										<?php foreach($delivery_statuses as $id => $row) {?>
											<a class="filter_action"><input name="status[]" type="checkbox" class="columns" id="status_id_<?php echo $id;?>" value="<?php echo $id;?>" <?php echo (isset($filters['status']) && in_array($id, $filters['status'])) ? 'checked="checked"' : '' ?>><label class="filterable_column_name" for="status_id_<?php echo $id;?>"><span></span><?php echo $row['name']; ?></label></a>
										<?php } ?>
									</li>
									</span>

									<span class="panel">
									<li data-toggle="collapse" data-target="#category_container" data-parent="#filter_dropdown" class="dropdown-header filter-header"><i class="plus-minus expand-collapse-icon glyphicon glyphicon-plus"></i> <?php echo lang('common_category'); ?> :</li>
									<li id="category_container" class="collapse">
										<?php foreach($delivery_categories as $id => $row) {?>
											<a class="filter_action"><input name="category[]" type="checkbox" class="columns" id="category_id_<?php echo $id;?>" value="<?php echo $id;?>" <?php echo (isset($filters['category']) && in_array($id, $filters['category'])) ? 'checked="checked"' : '' ?>><label class="filterable_column_name" for="category_id_<?php echo $id;?>"><span></span><?php echo $row['name']; ?></label></a>
										<?php } ?>
									</li>
									</span>

									<span class="panel">
									<li data-toggle="collapse" data-target="#in_store_pickup" data-parent="#filter_dropdown" class="dropdown-header filter-header"><i class="plus-minus expand-collapse-icon glyphicon glyphicon-plus"></i> <?php echo lang('deliveries_instore_pickup'); ?> :</li>
									<li id="in_store_pickup" class="collapse">
										<a class="filter_action"><input name="is_pickup[]" type="checkbox" class="columns" id="Pickup1" value="1" <?php echo (isset($filters['is_pickup']) && in_array('1',$filters['is_pickup'])) ? 'checked="checked"' : '' ?>><label class="filterable_column_name" for="Pickup1"><span></span><?php echo lang('common_yes'); ?></label></a>
										<a class="filter_action"><input name="is_pickup[]" type="checkbox" class="columns" id="Pickup0" value="0" <?php echo (isset($filters['is_pickup']) &&  in_array('0',$filters['is_pickup'])) ? 'checked="checked"' : '' ?>><label class="filterable_column_name" for="Pickup0"><span></span><?php echo lang('common_no'); ?></label></a>
									</li>
									</span>
									
									
									<span class="panel">
									<li data-toggle="collapse" data-target="#shipping_start_container" data-parent="#filter_dropdown" class="dropdown-header filter-header"><i class="plus-minus expand-collapse-icon glyphicon glyphicon-plus"></i> <?php echo lang('deliveries_shipping_date_start'); ?> :</li>
									<li id="shipping_start_container" class="panel collapse" >											
										<div style="overflow:hidden;">
										    <div class="form-group">
										        <div class="row">
										            <div class="col-md-12">
										                <div id="shipping_start" data-date="<?php echo isset($filters['shipping_start']) ? $filters['shipping_start'] : $default_start_date; ?>"></div>
										            </div>
										        </div>
										    </div>
										</div>
									</li>
									</span>
									
									<span class="panel">
									<li data-toggle="collapse" data-target="#shipping_end_container" data-parent="#filter_dropdown" class="dropdown-header filter-header"><i class="plus-minus expand-collapse-icon glyphicon glyphicon-plus"></i> <?php echo lang('deliveries_shipping_date_end'); ?> :</li>
									<li id="shipping_end_container" class="panel collapse" >
								
										<div style="overflow:hidden;">
										    <div class="form-group">
										        <div class="row">
										            <div class="col-md-12">
																		<div id="shipping_end" data-date="<?php echo isset($filters['shipping_end']) ? $filters['shipping_end'] : $default_end_date; ?>"></div>
										            </div>
										        </div>
										    </div>
										</div>
									</li>
									</span>
									
									<span class="panel">
									<li data-toggle="collapse" data-target="#delivery_start_container" data-parent="#filter_dropdown" class="dropdown-header filter-header"><i class="plus-minus expand-collapse-icon glyphicon glyphicon-plus"></i> <?php echo lang('deliveries_delivery_date_start'); ?> :</li>
									<li id="delivery_start_container" class="panel collapse">
										<div style="overflow:hidden;">
										    <div class="form-group">
										        <div class="row">
										            <div class="col-md-12">
																		<div id="delivery_start" data-date="<?php echo isset($filters['delivery_start']) ? $filters['delivery_start'] : $default_start_date; ?>"></div>
										            </div>
										        </div>
										    </div>
									   
										</div>
									</li>
								</span>

								<span class="panel">
								<li data-toggle="collapse" data-target="#delivery_end_container" data-parent="#filter_dropdown" class="dropdown-header filter-header"><i class="plus-minus expand-collapse-icon glyphicon glyphicon-plus"></i> <?php echo lang('deliveries_delivery_date_end'); ?> :</li>
								<li id="delivery_end_container" class="collapse">									
									<div style="overflow:hidden;">
									    <div class="form-group">
									        <div class="row">
									            <div class="col-md-12">
																	<div id="delivery_end" data-date="<?php echo isset($filters['delivery_end']) ? $filters['delivery_end'] : $default_end_date; ?>"></div>
															</div>
									        </div>
									    </div>
									</div>
								</li>
								</span>
								
								<?php if(count($locations) > 1){ ?>
								<span class="panel">
									<li data-toggle="collapse" data-target="#location_container" data-parent="#filter_dropdown" class="dropdown-header filter-header"><i class="plus-minus expand-collapse-icon glyphicon glyphicon-plus"></i> <?php echo lang('common_locations'); ?> :</li>
									<li id="location_container" class="collapse">
										<a class="filter_action"><input type="checkbox" class="columns" id="select_all_location"><label class="filterable_column_name" for="select_all_location"><span></span><?php echo lang('common_select_all'); ?></label></a>
										<?php
											foreach ($locations as $location_id => $location) {
												$checkbox_options = array(
													'name' => 'locations[]',
													'class' => 'location_checkboxes columns',
													'id' => 'locations' . $location->location_id,
													'value' => $location->location_id,
													'checked' => isset($filters['locations']) && in_array($location->location_id, $filters['locations']),
												);
												echo '<a class="filter_action">' . form_checkbox($checkbox_options) . '<label for="locations' . $location->location_id . '" class="filterable_column_name"><span></span>'. $location->name .'</label></a>';
											}
										?>
									</li>
								</span>
								<?php } ?>

								<span class="panel">
									<li data-toggle="collapse" data-target="#deliveries_with_or_without_sales" data-parent="#filter_dropdown" class="dropdown-header filter-header"><i class="plus-minus expand-collapse-icon glyphicon glyphicon-plus"></i> <?php echo lang('deliveries_with_or_without_sales'); ?> :</li>
									<li id="deliveries_with_or_without_sales" class="collapse">
										<a class="filter_action"><input name="deliveries_with_or_without_sales[]" type="checkbox" class="columns" id="deliveries_with_sales" value="with_sales" <?php echo (isset($filters['deliveries_with_or_without_sales']) && in_array('with_sales',$filters['deliveries_with_or_without_sales'])) ? 'checked="checked"' : '' ?>><label class="filterable_column_name" for="deliveries_with_sales"><span></span><?php echo lang('deliveries_with_sales'); ?></label></a>
										<a class="filter_action"><input name="deliveries_with_or_without_sales[]" type="checkbox" class="columns" id="deliveries_without_sales" value="without_sales" <?php echo (isset($filters['deliveries_with_or_without_sales']) &&  in_array('without_sales',$filters['deliveries_with_or_without_sales'])) ? 'checked="checked"' : '' ?>><label class="filterable_column_name" for="deliveries_without_sales"><span></span><?php echo lang('deliveries_without_sales'); ?></label></a>
									</li>
									</span>

							</ul>
						</div>
						<script type="text/javascript">
							date_time_picker_inline_linked($('#shipping_start'), $('#shipping_end'), JS_DATE_FORMAT+ " "+JS_TIME_FORMAT, date_time_callback);
							date_time_picker_inline_linked($('#delivery_start'), $('#delivery_end'), JS_DATE_FORMAT+ " "+JS_TIME_FORMAT, date_time_callback);
							
							$(document).ready(function(){
								// Add minus icon for collapse element which is open by default
								$(".collapse.in").each(function(){
							  	$(this).siblings(".filter-header").find(".glyphicon").addClass("glyphicon-minus").removeClass("glyphicon-plus");
							  });
      
							  // Toggle plus minus icon on show hide of collapse element
							  $(".collapse").on('show.bs.collapse', function(){
							  	$(this).parent().find(".glyphicon").removeClass("glyphicon-plus").addClass("glyphicon-minus");
							  }).on('hide.bs.collapse', function(){
							    $(this).parent().find(".glyphicon").removeClass("glyphicon-minus").addClass("glyphicon-plus");
							  });

									$("#select_all_location").change(function() {
										if (this.checked) {
											$(".location_checkboxes").each(function() {
												this.checked=true;
											});
										} else {
											$(".location_checkboxes").each(function() {
												this.checked=false;
											});
										}
									});

									$(".location_checkboxes").change(function () {
										if ($(this).is(":checked")) {
											var isAllChecked = 0;

											$(".location_checkboxes").each(function() {
												if (!this.checked)
													isAllChecked = 1;
											});

											if (isAllChecked == 0) {
												$("#select_all_location").prop("checked", true);
											}     
										}
										else {
											$("#select_all_location").prop("checked", false);
										}
									});

								});
							
							$(document).on('change', '.filter_value', function(e) {
							});
									
						</script>
						
						</form>
					
					<span title="<?php echo $total_rows; ?> total <?php echo $controller_name?>" class="badge bg-primary tip-left" id="manage_total_items"><?php echo $total_rows; ?></span>
					<span class="panel-options custom">
							<div class="pagination pagination-top hidden-print  text-center" id="pagination_top">
								<?php echo $pagination;?>		
							</div>
					</span>
				</h3>
			</div>
				<div class="panel-body nopadding table_holder table-responsive" id="table_holder">
					<?php echo $manage_table; ?>			
				</div>
		</div>	
		<div class="text-center">
		<div class="pagination hidden-print alternate text-center" id="pagination_bottom" >
			<?php echo $pagination;?>
		</div>
		</div>
	</div>
</div>

<div class="spinner" id="grid-loader" style="display:none;">
	<div class="rect1"></div>
	<div class="rect2"></div>
	<div class="rect3"></div>
</div>

<script type="text/javascript">
	$(document).ready(function() 
	{
		<?php if ($this->session->flashdata('success')) { ?>
		show_feedback('success', <?php echo json_encode($this->session->flashdata('success')); ?>, <?php echo json_encode(lang('common_success')); ?>);
		<?php } ?>

		<?php if ($this->session->flashdata('error')) { ?>
		show_feedback('error', <?php echo json_encode($this->session->flashdata('error')); ?>, <?php echo json_encode(lang('common_error')); ?>);
		<?php } ?>	
		
		
		$("#change_status").change(function(){
			var status = $(this).val();
			if(status != ''){
				bootbox.confirm(<?php echo json_encode(lang($controller_name."_confirm_status_change"));?>, function(result)
				{
					if (result)
					{
						$('#grid-loader').show();
						event.preventDefault();
						var selected = get_selected_values();
						
						$.post('<?php echo site_url("$controller_name/change_status/");?>', {delivery_ids : selected, status:status},function(response) {
							$('#grid-loader').hide();
							show_feedback(response.success ? 'success' : 'error', response.message,response.success ? <?php echo json_encode(lang('common_success')); ?> : <?php echo json_encode(lang('common_error')); ?>);

							//Refresh tree if success
							if (response.success)
							{
								reload_delivery_table();
								//setTimeout(function(){location.href = location.href;},800);
							}
						}, "json");
					}
				});
			}

		});
	});
</script>

<style>
.change_delivery_status{
	display:inline !important;
	width:10% !important;
}
#grid-loader{
	top:0px;
}
</style>
	
<?php $this->load->view("partial/footer"); ?>

