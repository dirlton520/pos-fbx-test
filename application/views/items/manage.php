<?php $this->load->view("partial/header"); ?>

<script type="text/javascript">
	
	function reload_items_table()
	{
		clearSelections();
			$("#table_holder").load(<?php echo json_encode(site_url("$controller_name/reload_table")); ?>);
	}
	
	$(document).ready(function()
	{	
		$("#fields").select2({dropdownAutoWidth : true});
		$("#category_id").select2({dropdownAutoWidth : true});
				
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
		
		
		$("#config_columns input[type=checkbox]").change(
			function(e) {
				var columns = $("#config_columns input:checkbox:checked").map(function(){
      		return $(this).val();
    		}).get();
				
				$.post(<?php echo json_encode(site_url("$controller_name/save_column_prefs")); ?>, {columns:columns}, function(json)
				{
					reload_items_table();
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
		
		enable_cleanup(<?php echo json_encode(lang("items_confirm_cleanup"));?>);

		$('#generate_barcodes').click(function()
		{
			var selected = get_selected_values();
			
			if (selected.length == 0)
			{
				bootbox.alert(<?php echo json_encode(lang('common_must_select_item_for_barcode')); ?>);
				return false;
			}

			$("#skip-labels").modal('show');
			return false;
		});
		
		$("#generate_barcodes_form").submit(function()
		{
			var selected = get_selected_values();
			var num_labels_skip = $("#num_labels_skip").val() ? $("#num_labels_skip").val() : 0;
			var url = '<?php echo site_url("items/generate_barcodes");?>'+'/'+selected.join('~')+'/'+num_labels_skip;
			window.location = url;
			return false;
		});

		$('#generate_barcode_labels').click(function()
		{
			var selected = get_selected_values();
			if (selected.length == 0)
			{
				bootbox.alert(<?php echo json_encode(lang('common_must_select_item_for_barcode')); ?>);
				return false;
			}

			$(this).attr('href','<?php echo site_url("items/generate_barcode_labels");?>/'+selected.join('~'));
		});		

		<?php if ($this->session->flashdata('manage_success_message')) { ?>
			show_feedback('success', <?php echo json_encode($this->session->flashdata('manage_success_message')); ?>, <?php echo json_encode(lang('common_success')); ?>);
			<?php } ?>
		});


function toggleFieldsAndCategoriesSearchListener(x) {		
    if (sm.matches) { 			
			$('#fields').prop('disabled', false);
			$("#category_id").prop('disabled', false);
    } 
		else
		{
			$('#fields').val("all");
			$("#category_id").val("");
			
			$('#fields').prop('disabled', true);
			$("#category_id").prop('disabled', true);
    }
}

var sm = window.matchMedia("(min-width: 768px)");
toggleFieldsAndCategoriesSearchListener(sm) // Call listener function at run time
sm.addListener(toggleFieldsAndCategoriesSearchListener) // Attach listener function on state changes
		

function post_bulk_form_submit(response)
{
	$("#myModal").modal('hide');
	show_feedback(response.success ? 'success' : 'error',response.message,response.success ? <?php echo json_encode(lang('common_success')); ?> : <?php echo json_encode(lang('common_error')); ?>);
	reload_items_table();
}

function select_inv()
{	
	bootbox.confirm(<?php echo json_encode(lang('items_select_all_message')); ?>, function(result)
	{
		if (result)
		{
			$('#select_inventory').val(1);
			$('#selectall').css('display','none');
			$('#selectnone').css('display','block');
			$.post('<?php echo site_url("items/select_inventory");?>', {select_inventory: $('#select_inventory').val()});
		}
	});
}
function select_inv_none()
{
	$('#select_inventory').val(0);
	$('#selectnone').css('display','none');
	$('#selectall').css('display','block');
	$.post('<?php echo site_url("items/clear_select_inventory");?>', {select_inventory: $('#select_inventory').val()});	
}

$.post('<?php echo site_url("items/clear_select_inventory");?>', {select_inventory: $('#select_inventory').val()});	

</script>


<div class="modal fade skip-labels" id="skip-labels" role="dialog" aria-labelledby="skipLabels" aria-hidden="true">
    <div class="modal-dialog customer-recent-sales">
      	<div class="modal-content">
	        <div class="modal-header">
	          	<button type="button" class="close" data-dismiss="modal" aria-label=<?php echo json_encode(lang('common_close')); ?>><span aria-hidden="true">&times;</span></button>
	          	<h4 class="modal-title" id="skipLabels"><?php echo lang('common_skip_labels') ?></h4>
	        </div>
	        <div class="modal-body">
				
	          	<?php echo form_open("items/generate_barcodes", array('id'=>'generate_barcodes_form','autocomplete'=> 'off')); ?>				
				<input type="text" class="form-control text-center" name="num_labels_skip" id="num_labels_skip" placeholder="<?php echo lang('common_skip_labels') ?>">
					<?php echo form_submit('generate_barcodes_form',lang("common_submit"),'class="btn btn-block btn-primary"'); ?>
				<?php echo form_close(); ?>
				
	        </div>
    	</div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->


<nav class="navbar manage_buttons">
			
	<!-- Css Loader  -->
	<div class="spinner" id="ajax-loader" style="display:none">
	  <div class="rect1"></div>
	  <div class="rect2"></div>
	  <div class="rect3"></div>
	</div>
		
		<div class="manage-row-options hidden">
		
		<div class="email_buttons items">
			<div class="row">
				
			<div class="col-md-12 pull-left">
			<?php if(!$deleted) { ?>
			
			<?php if ($this->Employee->has_module_action_permission($controller_name, 'add_update', $this->Employee->get_logged_in_employee_info()->person_id)) {?>
			<?php echo
				anchor("$controller_name/bulk_edit/",
				'<span class="">'.lang("common_edit").'</span>',
				array('id'=>'bulk_edit','data-toggle'=>'modal','data-target'=>'#myModal',
					'class' => 'btn btn-primary btn-lg  disabled',
					'title'=>lang('items_edit_multiple_items'))); 
			?>
			<?php } ?>
			
			<a href="#" class="btn btn-lg btn-select-all btn-primary"><span class="ion-android-checkbox-outline"></span> <span class="hidden-xs"><?php echo lang('common_select_all'); ?></span></a>
			
			<div class="btn-group piluku-dropdown" role="group">
			  <button class="btn btn-primary btn-lg dropdown-toggle" type="button" id="dropdownMenu1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
			    <?php echo lang("common_labels"); ?>
			    <span class="caret"></span>
			  </button>
			  <ul class="dropdown-menu" aria-labelledby="dropdownMenu1">
			    <li><?php echo anchor("$controller_name/generate_barcode_labels", lang("common_label_printer"), array('id' => 'generate_barcode_labels')); ?></li>
			    <li><?php echo anchor("$controller_name/generate_barcodes", lang("common_standard_printer"), array('id' => 'generate_barcodes')); ?></li>
			  </ul>
			</div>
			
			<a href="#" class="btn btn-lg btn-clear-selection btn-warning"><span class="ion-close-circled"></span> <span class="hidden-xs"><?php echo lang('common_clear_selection'); ?></span></a>
		
			<?php if ($this->Employee->has_module_action_permission($controller_name, 'delete', $this->Employee->get_logged_in_employee_info()->person_id)) {?>				
			<?php echo 
				anchor("$controller_name/delete",
				'<span class="ion-trash-a"></span> '.'<span class="hidden-xs">'.lang("common_delete").'</span>',
				array('id'=>'delete','class'=>'btn btn-red btn-lg','title'=>lang("common_delete"))); 
			?>
			<?php } ?>
		<?php } else { ?>
				<?php if ($this->Employee->has_module_action_permission($controller_name, 'delete', $this->Employee->get_logged_in_employee_info()->person_id)) {?>
				<?php echo anchor("$controller_name/undelete",
						'<span class="ion-trash-a"></span> '.'<span class="hidden-xs">'.lang("common_undelete").'</span>',
						array('id'=>'delete','class'=>'btn btn-green btn-lg disabled delete_inactive','title'=>lang("common_undelete"))); ?>
				<?php } ?>

				<a href="#" class="btn btn-lg btn-clear-selection btn-warning"><span class="ion-close-circled"></span> <?php echo lang('common_clear_selection'); ?></a>		
		<?php } ?>
		</div><!-- end col-->
		</div> <!-- end row -->
			
		
		</div>
	</div>

	<div class="row">

		<div class="col-md-9 col-sm-10 col-xs-10">
			
			<?php echo form_open("$controller_name/search",array('id'=>'search_form', 'autocomplete'=> 'off', 'class'=>'')); ?>
				<div class="search search-items no-left-border">
					<ul class="list-inline">
						<li>
							&nbsp;
							<input type="text" class="form-control" name='search' id='search' value="<?php echo H($search); ?>" placeholder="<?php echo $deleted ? lang('common_search_deleted') : lang('common_search'); ?> <?php echo lang('module_'.$controller_name); ?>"/>
						</li>
						<li class="hidden-xs">
							<?php
 						 $searchable_fields = array(
 							'all'=>lang('common_all'),
 							$this->db->dbprefix('items').'.item_id' => lang('common_item_id'),
 							$this->db->dbprefix('items').'.item_number' => lang('common_item_number_expanded'),
 							$this->db->dbprefix('items').'.product_id' => lang('common_product_id'),
 							$this->db->dbprefix('items').'.name' => lang('common_item_name'),
 							$this->db->dbprefix('items').'.description' => lang('common_description'),
 							$this->db->dbprefix('items').'.size' => lang('common_size'),
 							$this->db->dbprefix('items').'.cost_price' => lang('common_cost_price'),
 							$this->db->dbprefix('items').'.unit_price' => lang('common_unit_price'),
 							$this->db->dbprefix('items').'.promo_price' => lang('items_promo_price'),
 							$this->db->dbprefix('location_items').'.quantity' =>lang('items_quantity'),
 							$this->db->dbprefix('items').'.reorder_level' => lang('items_reorder_level'),
 							$this->db->dbprefix('suppliers').'.company_name' => lang('common_supplier'),
 							$this->db->dbprefix('manufacturers').'.name' => lang('common_manufacturer'),
 							$this->db->dbprefix('tags').'.name' => lang('common_tag'),
							);
							for($k=1;$k<=NUMBER_OF_PEOPLE_CUSTOM_FIELDS;$k++)
							{
								if($this->Item->get_custom_field($k) !== false)
								{
									$searchable_fields[$this->db->dbprefix('items').".custom_field_${k}_value"] = $this->Item->get_custom_field($k);
								}
							}
							?>
						<?php echo lang('common_fields'); ?>: 
						<?php echo form_dropdown('fields', 
						 $searchable_fields,$fields, 'class="" id="fields"');
							?>
							</li>
						<li class="hidden-xs">
							<?php echo lang('common_category'); ?>: 	
							<?php echo form_dropdown('category_id', $categories,$category_id, 'class="" id="category_id"'); ?>
						</li>
						<li>
							<button type="submit" class="btn btn-primary btn-lg"><span class="ion-ios-search-strong"></span><span class="hidden-xs hidden-sm"> <?php echo lang("common_search"); ?></span></button>
						</li>
						<li>
							<div class="clear-block items-clear-block <?php echo ($search=='') ? 'hidden' : ''  ?>">
								<a class="clear" href="<?php echo site_url($controller_name.'/clear_state'); ?>">
									<i class="ion ion-close-circled"></i>
								</a>	
							</div>
						</li>
					</ul>
				</div>
			<?php echo form_close() ?>

		</div>
		<div class="col-md-3 col-sm-2 col-xs-2">
			<div class="buttons-list items-buttons">
				<div class="pull-right-btn">
      	<div class="spinner hidden" id="ajax-loader">
            <div class="rect1"></div>
            <div class="rect2"></div>
            <div class="rect3"></div>
          </div>
					<?php if ($deleted) 
					{
						echo 
						anchor("$controller_name/toggle_show_deleted/0",
							'<span class="ion-android-exit"></span> <span class="hidden-xs">'.lang('common_done').'</span>',
							array('class'=>'btn btn-primary btn-lg toggle_deleted','title'=> lang('common_done')));
					}	
					?>     
					<?php if ($this->Employee->has_module_action_permission($controller_name, 'add_update', $this->Employee->get_logged_in_employee_info()->person_id) && !$deleted) {?>				
						<?php
						 $query = http_build_query(array('redirect' => 'items', 'progression' =>  1, 'quick_edit' => null));
						 echo	anchor("$controller_name/view/-1?".$query,
							'<span class="ion-plus"></span> '.lang($controller_name.'_new'),
							array('class'=>'btn btn-primary btn-lg hidden-sm hidden-xs', 
								'title'=>lang($controller_name.'_new')));
						?>
					<?php } ?>
					<?php if(!$deleted) { ?>
					
					<div class="piluku-dropdown btn-group">
						<button type="button" class="btn btn-more dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
						<span class="hidden-xs ion-android-more-horizontal"> </span>
						<i class="visible-xs ion-android-more-vertical"></i>
					</button>
					<ul class="dropdown-menu" role="menu">
						
						<?php if ($this->Employee->has_module_action_permission($controller_name, 'add_update', $this->Employee->get_logged_in_employee_info()->person_id)) { ?>				
						<li class="visible-sm visible-xs">
							<?php echo 
								anchor("$controller_name/view/-1?redirect=items&progression=1",
								'<span class="ion-plus-round"> '.lang('common_add').' '.lang($controller_name.'_new').'</span>',
								array('class'=>'', 
									'title'=>lang($controller_name.'_new')));
							?>
						</li>
						<?php } ?>
						
						<li>
							<?php echo anchor("$controller_name/manage_attributes?redirect=items",
									'<span class="ion-ios-toggle-outline"> '.lang('items_manage_attributes').'</span>',
									array('id'=>'manage_attributes', 'class'=>'','title'=> lang('items_manage_attributes'))); 
								?>
						</li>
						<li>

							<?php echo anchor("$controller_name/manage_modifiers?redirect=items",
									'<span class="ion-android-list"> '.lang('common_manage_modifiers').'</span>',
									array('id'=>'manage_modifiers', 'class'=>'','title'=> lang('common_manage_modifiers'))); 
								?>
						</li>
						
						<?php if ($this->Employee->has_module_action_permission($controller_name, 'manage_categories', $this->Employee->get_logged_in_employee_info()->person_id)) {?>
						<li>
							<?php echo anchor("$controller_name/manage_categories?redirect=items",
								'<span class="ion-ios-folder-outline"> ' . lang("items_manage_categories").'</span>',
								array('class'=>'', 'title'=>lang('items_manage_categories')));
							?>
						</li>
						<?php } ?>
						
						<?php if ($this->Employee->has_module_action_permission($controller_name, 'manage_tags', $this->Employee->get_logged_in_employee_info()->person_id)) {?>
						<li>
							<?php echo anchor("$controller_name/manage_tags?redirect=items",
								'<span class="ion-ios-pricetag-outline"> ' . lang("items_manage_tags").'</span>',
								array('class'=>'', 'title'=>lang('items_manage_tags')));
							?>
						</li>
						<?php } ?>		
						
						<?php if ($this->Employee->has_module_action_permission($controller_name, 'manage_manufacturers', $this->Employee->get_logged_in_employee_info()->person_id)) {?>
						<li>
							<?php echo anchor("$controller_name/manage_manufacturers?redirect=items",
								'<span class="ion-settings"> ' . lang("items_manage_manufacturers").'</span>',
								array('class'=>'', 'title'=>lang('items_manage_manufacturers')));
							?>
						</li>
						<?php } ?>		
						
						
						<?php if ($this->Employee->has_module_action_permission($controller_name, 'count_inventory', $this->Employee->get_logged_in_employee_info()->person_id)) {?>
						<li>
							<?php echo anchor("$controller_name/count?redirect=items",
								'<span class="ion-ios-paper-outline"> '.lang("items_count_inventory").'</span>',
								array('class'=>'', 'title'=>lang('items_count_inventory')));
							?>
						</li>			
						<?php } ?>

						<?php if ($this->Employee->has_module_action_permission($controller_name, 'add_update', $this->Employee->get_logged_in_employee_info()->person_id)) {?>				
							<li>
								<?php echo anchor("$controller_name/excel_import?redirect=items",
								'<span class="ion-ios-download-outline"> '.lang("common_excel_import").'</span>',
								array('class'=>' ', 'title'=>lang('common_excel_import')));
								?>
							</li>
							
							<?php if ($this->Employee->has_module_action_permission($controller_name, 'excel_export', $this->Employee->get_logged_in_employee_info()->person_id)) {?>				
							
							<li>
								<?php echo anchor("$controller_name/excel_export/",
								'<span class="ion-ios-upload-outline"> '.lang("common_excel_export").'</span>',
								array('class'=>' ', 'title'=>lang('common_excel_export')));
								?>
							</li>
							
							<?php } ?>
							
							
							<?php if ($this->Employee->has_module_action_permission($controller_name, 'view_inventory_print_list', $this->Employee->get_logged_in_employee_info()->person_id)) {?>				
							<li>
								<?php echo anchor("$controller_name/inventory_print_list/",
								'<span class="ion-printer"> '.lang("items_inventory_print_list").'</span>',
								array('class'=>' ', 'title'=>lang('items_inventory_print_list')));
								?>
							</li>
							
							<li>
								<?php echo anchor("$controller_name/inventory_print_list/1",
								'<span class="ion-printer"> '.lang("items_inventory_print_list_summary").'</span>',
								array('class'=>' ', 'title'=>lang('items_inventory_print_list_summary')));
								?>
							</li>
							
							<?php } ?>
							
							
						<?php }?>
						<?php if ($this->Employee->has_module_action_permission($controller_name, 'delete', $this->Employee->get_logged_in_employee_info()->person_id)) {?>
							<li>
								<?php echo 
									anchor("$controller_name/cleanup",
									'<span class="ion-loop"> '.lang("items_cleanup_old_items").'</span>',
									array('id'=>'cleanup', 'class'=>'','title'=>lang("items_cleanup_old_items"))); 
								?>
							</li>
						<?php }?>
						<li>
							<?php echo anchor("$controller_name/custom_fields", '<span class="ion-wrench"> '.lang('common_custom_field_config').'</span>',
								array('id'=>'custom_fields', 'class'=>'','title'=> lang('common_custom_field_config'))); ?>
						</li>
						
						<?php if ($this->Employee->has_module_action_permission($controller_name, 'delete', $this->Employee->get_logged_in_employee_info()->person_id)) {?>
							<li>
									<?php echo anchor("$controller_name/toggle_show_deleted/1", '<span class="ion-trash-a"> '.lang($controller_name."_manage_deleted").'</span>',
										array('class'=>'toggle_deleted','title'=> lang($controller_name."_manage_deleted"))); ?>
							</li>
						<?php }?>
						</ul>
					</div>
					<?php } ?>
				</div>
			</div>
		</div>
	</div>
</nav>
													

<div class="row alert-select-all">
	<div class="col-md-12">

		<div id="selectall" class="selectall text-center" onclick="select_inv()">
			<div class="alert alert-warning">
				<?php echo lang('items_all').' '.lang('items_select_inventory').' <strong>'.lang('items_for_current_search').'</strong>'; ?>
			</div>
		</div>

		<div id="selectnone" class="selectnone text-center" onclick="select_inv_none()" >
			<div class="alert alert-success">
				<?php echo '<strong>'.lang('items_selected_inventory_total').' '.lang('items_select_inventory_none').'</strong>'; ?>
			</div>
		</div>
		
		<?php echo form_input(array(
			'name'=>'select_inventory',
			'id'=>'select_inventory',
			'style'=>'display:none',
			)); 
		?>
	</div>
</div>
															
	<div class="container-fluid">
		<div class="row manage-table">
			<div class="panel panel-piluku">
				<div class="panel-heading">
					<h3 class="panel-title">
					<?php echo ($deleted ? lang('common_deleted').' ' : '').lang('module_'.$controller_name); ?>
						<span title="<?php echo $total_rows; ?> total <?php echo $controller_name?>" class="badge bg-primary tip-left" id="manage_total_items"><?php echo $total_rows; ?></span>
						<form id="config_columns">
						<div class="piluku-dropdown btn-group table_buttons pull-right m-left-20">
							<button type="button" class="btn btn-more dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
								<i class="ion-gear-a"></i>
							</button>
							
							<ul id="sortable" class="dropdown-menu dropdown-menu-left col-config-dropdown" role="menu">
									<li class="dropdown-header"><a id="reset_to_default" class="pull-right"><span class="ion-refresh"></span> Reset</a><?php echo lang('common_column_configuration'); ?></li>
																		
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
						<div class="panel-options custom">
								<div class="pagination pagination-top hidden-print  text-center" id="pagination_top">
									<?php echo $pagination;?>		
								</div>
						</div>
					</h3>
					
					
					
				</div>
				<div class="panel-body nopadding table_holder table-responsive" id="table_holder" >
					<?php echo $manage_table; ?>			
				</div>		
				
			</div>
		</div>
	</div>
<div class="text-center">
	<div class="row pagination hidden-print alternate text-center" id="pagination_bottom" >
		<?php echo $pagination;?>
	</div>
</div>
</div>
<?php $this->load->view("partial/footer"); ?>
