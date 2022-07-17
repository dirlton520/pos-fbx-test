<?php $this->load->view("partial/header"); ?>
	<!-- Css Loader  -->
	<div class="spinner" id="grid-loader" style="display:none">
		<div class="rect1"></div>
		<div class="rect2"></div>
		<div class="rect3"></div>
	</div>
	<div class="container-fluid">
		<div class="row manage-table">
			<div class="panel panel-piluku">
				<div class="panel-heading">
					<h3 class="panel-title hidden-print">
						 <?php echo lang('sales_list_of_suspended_sales'); ?>
					</h3>
					
						<form id="config_columns">
						<div class="piluku-dropdown btn-group table_buttons pull-right m-left-20">
							<button type="button" class="btn btn-more dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
								<i class="ion-gear-a"></i>
							</button>
							
							<ul id="sortable" class="dropdown-menu dropdown-menu-left col-config-dropdown" role="menu">
									<li class="dropdown-header"><a id="reset_to_default" class="pull-right"><span class="ion-refresh"></span> <?php echo lang('common_reset'); ?></a><?php echo lang('common_column_configuration'); ?></li>
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

						<form id="config_filters">
						<div class="piluku-dropdown btn-group table_buttons pull-right m-left-20">
						<button id="config_filter_btn" type="button" class="btn btn-more dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
							<i class="ion-funnel"></i>
						</button>
							</button>
							
							<ul id="sortable" class="dropdown-menu dropdown-menu-left col-config-dropdown" role="menu">
								<li class="dropdown-header"><a id="reset_to_default" class="pull-right"><span class="ion-refresh"></span> <?php echo lang('common_reset'); ?></a><?php echo lang('common_suspended_types'); ?></li>
								
								<?php
								$layaway_type_type_checked = $this->session->userdata('search_suspended_sale_types') === NULL || (in_array(1,$this->session->userdata('search_suspended_sale_types')));	
								
								if ($layaway_type_type_checked)
								{
									$layaway_type_type_checked = 'checked ="checked" ';
								}
								else
								{
									$layaway_type_type_checked='';
								}
								
								
								$estimate_type_type_checked = $this->session->userdata('search_suspended_sale_types') === NULL || (in_array(2,$this->session->userdata('search_suspended_sale_types')));	
								
								if ($estimate_type_type_checked)
								{
									$estimate_type_type_checked = 'checked ="checked" ';
								}
								else
								{
									$estimate_type_type_checked='';
								}
								
									
								?>
								<li class="sort"><a><input <?php echo $layaway_type_type_checked; ?> name="suspended_types[]" type="checkbox" class="columns" id="layaway" value="1"><label class="sortable_column_name" for="layaway"><span></span><?php echo H($this->config->item('user_configured_layaway_name') ? $this->config->item('user_configured_layaway_name') : lang('common_layaway')); ?></label></a></li>									
								<li class="sort"><a><input <?php echo $estimate_type_type_checked; ?> name="suspended_types[]" type="checkbox" class="columns" id="estimate" value="2"><label class="sortable_column_name" for="estimate"><span></span><?php echo H($this->config->item('user_configured_estimate_name') ? $this->config->item('user_configured_estimate_name') : lang('common_estimate')); ?></label></a></li>									

								<?php 
								foreach ($suspended_sale_types as $sale_suspend_type) { 
								$suspened_type_type_checked = $this->session->userdata('search_suspended_sale_types') === NULL || (in_array($sale_suspend_type['id'],$this->session->userdata('search_suspended_sale_types')));	
								
								if ($suspened_type_type_checked)
								{
									$suspened_type_type_checked = 'checked ="checked" ';
								}
								else
								{
									$suspened_type_type_checked='';
								}
								?>
								<li class="sort"><a><input <?php echo $suspened_type_type_checked; ?> name="suspended_types[]" type="checkbox" class="columns" id="custom_type_<?php echo H($sale_suspend_type['id']); ?>" value="<?php echo H($sale_suspend_type['id']); ?>"><label class="sortable_column_name" for="custom_type_<?php echo H($sale_suspend_type['id']); ?>"><span></span><?php echo H($sale_suspend_type['name']); ?></label></a></li>									
								<?php } ?>

							</ul>
						</div>
					</form>
				</div>
			
				<div class="panel-body nopadding">
					<div class="col-md-12" id="button-panel" style="display:none;">
						<?php if ($this->Employee->has_module_action_permission($controller_name, 'delete_suspended_sale', $this->Employee->get_logged_in_employee_info()->person_id)) {?>				
							<?php echo  anchor("$controller_name/delete_suspended_sale", '<span class="ion-trash-a"></span> '.'<span class="hidden-xs">'.lang("common_delete").'</span>', array('id'=>'delete_sale','class'=>'btn btn-red btn-lg','title'=>lang("common_delete"))); ?>
						<?php } ?>
					</div>
				</div>

				<div class="panel-body nopadding table_holder table-responsive" id="table_holder">
					<?php echo $manage_table; ?>
				</div>
		</div>
	</div>
</div>
<?php $this->load->view("partial/footer"); ?>

<script type="text/javascript">
	
	function reload_items_table()
	{
		$("#table_holder").load(<?php echo json_encode(site_url("$controller_name/reload_table")); ?>, function(){
			attachEvents();
		});
	}
	
	function attachEvents()
	{
		$("#config_columns input[type=checkbox]").change( function(e) {
				var columns = $("#config_columns input:checkbox:checked").map(function(){
      		return $(this).val();
    		}).get();
				
				$.post(<?php echo json_encode(site_url("$controller_name/save_column_prefs")); ?>, {columns:columns}, function(json)
				{
					reload_items_table();
				});
				
		});
		
		$(".form_delete_suspended_sale").submit(function()
		{
			var formDelete = this;
			bootbox.confirm(<?php echo json_encode(lang("sales_delete_confirmation")); ?>, function(result)
			{
				if (result)
				{
					formDelete.submit();
				}		
			});
			
			return false;
			
		});

		$(".form_email_receipt_suspended_sale").ajaxForm({success: function()
		{
			bootbox.alert("<?php echo lang('common_receipt_sent'); ?>");
		}});		
	}
	
	$(document).ready(function(){

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
		
		$("#config_columns a").on("click", function(e) {
			e.preventDefault();
			
			if($(this).attr("id") == "reset_to_default")
			{
				//Send a get request wihtout columns will clear column prefs
				$.get(<?php echo json_encode(site_url("$controller_name/save_column_prefs")); ?>, function()
				{
					reload_items_table();
					var $checkboxs = $("#config_columns a").find("input[type=checkbox]");
					$checkboxs.prop("checked", false);
					
					<?php foreach($default_columns as $default_col) { ?>
							$("#config_columns a").find('#'+<?php echo json_encode($default_col);?>).prop("checked", true);
					<?php } ?>
				});
			}
			
			if(!$(e.target).hasClass("handle"))
			{
				var $checkboxs = $(this).find("input[type=checkbox]");
				$checkboxs.prop("checked", !$checkboxs.prop("checked")).trigger("change");
			}
			
			return false;
		});
		
		$("#config_filters input[type=checkbox]").change( function(e) {
				var suspended_types = $("#config_filters input:checkbox:checked").map(function(){
      				return $(this).val();
    			}).get();
				
				$.post(<?php echo json_encode(site_url("$controller_name/set_search_suspended_sale_types")); ?>, {suspended_types:suspended_types}, function(json)
				{
					reload_items_table();
				});
				
		});
		
		$("#config_filters a").on("click", function(e) {
			e.preventDefault();
			
			if($(this).attr("id") == "reset_to_default")
			{
 				$.get(<?php echo json_encode(site_url("$controller_name/set_search_suspended_sale_types")); ?>, function()
				{
					reload_items_table();
					var $checkboxs = $("#config_filters a").find("input[type=checkbox]");
					$checkboxs.prop("checked", true);
					
					<?php foreach($default_columns as $default_col) { ?>
							$("#config_filters a").find('#'+<?php echo json_encode($default_col);?>).prop("checked", true);
					<?php } ?>
				});
			}
			
			if(!$(e.target).hasClass("handle"))
			{
				var $checkboxs = $(this).find("input[type=checkbox]");
				$checkboxs.prop("checked", !$checkboxs.prop("checked")).trigger("change");
			}
			
			return false;
		});

		$(document).on('change', "#select_all", function (e) {
			if ($(this).prop('checked')) {
				$("#dTable tbody :checkbox").each(function () {
					$(this).prop('checked', true);
					$(this).parent().parent().find("td").addClass('selected').css("backgroundColor", "");
				});
				get_checked_items();
			}else{
				$("#dTable tbody :checkbox").each(function () {
					$(this).prop('checked', false);
					$(this).parent().parent().find("td").removeClass('selected');
				});
				get_checked_items();
			}			
		});

		var $chkboxes_container = $('#dTable tbody tr');
		var lastChecked = null;

		$(document).on('click', "#dTable tbody tr", function row_click(event) {
			var checkbox = $(this).find(":checkbox");
			checkbox.prop('checked', !checkbox.prop('checked'));

			//event.preventDefault();
			if (!lastChecked) {
				lastChecked = this;

				if (checkbox.prop('checked')) {
					$(this).find("td").addClass('selected').css("backgroundColor", "");
				}
				else {
					$(this).find("td").removeClass('selected').css("backgroundColor", "");
				}

				return;
			}

			if (event.shiftKey) {
				var start = $chkboxes_container.index(this);
				var end = $chkboxes_container.index(lastChecked);

				var $chkboxes_containers = $chkboxes_container.slice(Math.min(start, end), Math.max(start, end) + 1);
				$($chkboxes_containers).each(function () {
					$(this).find('input[type="checkbox"]').prop('checked', true);
					$(this).find("td").addClass('selected');
				});

				//$chkboxes_container.slice(Math.min(start, end), Math.max(start, end) + 1).find('input[type="checkbox"]').prop('checked', true);
			}

			lastChecked = this;

			if (checkbox.prop('checked')) {
				$(this).find("td").addClass('selected').css("backgroundColor", "");
			}
			else {
				$(this).find("td").removeClass('selected').css("backgroundColor", "");
			}
		});

		function get_checked_items(){
			var selected_values = new Array();
			$("#dTable tbody :checkbox:checked").each(function() {
				selected_values.push($(this).val());
			});
			if(selected_values.length > 0){
				$("#button-panel").show("medium");
			}else{
				$("#button-panel").hide("medium");
			}

			return selected_values;
		}

		$("#dTable tbody :checkbox").change(function () {
			get_checked_items();
		});


		$("#delete_sale").click(function(e)
		{
			e.preventDefault();
			bootbox.confirm(<?php echo json_encode(lang("sales_delete_confirmation")); ?>, function(result)
			{
				if (result)
				{	$(".spinner").show();
					var suspended_sale_id = get_checked_items();
					$.post(<?php echo json_encode(site_url("$controller_name/delete_suspended_sale")); ?>, {suspended_sale_id:suspended_sale_id}, function(json)
					{
						//reload_items_table();
						location.reload();
						$("#delete_sale").hide("medium");
						$(".spinner").hide();
					});
				}		
			});
			
			return false;
			
		});

	});

	attachEvents();
</script>