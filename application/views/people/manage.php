<?php $this->load->view("partial/header"); ?>
<script type="text/javascript">
	
	function reload_people_table()
	{
		clearSelections();
		$("#table_holder").load(<?php echo json_encode(site_url("$controller_name/reload_table")); ?>);
	}
	
	
	$(document).ready(function() 
	{
		$("#location_id").select2({dropdownAutoWidth : true});
		
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
					reload_people_table();
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
					reload_people_table();
				});
				
		});
		
		enable_sorting("<?php echo site_url("$controller_name/sorting"); ?>");
		enable_select_all();
		enable_checkboxes();
		enable_row_selection();
		enable_search('<?php echo site_url("$controller_name");?>',<?php echo json_encode(lang("common_confirm_search"));?>);
		enable_email('<?php echo site_url("$controller_name/mailto")?>');
		
		<?php if(!$deleted) { ?>
			enable_delete(<?php echo json_encode(lang($controller_name."_confirm_delete"));?>,<?php echo json_encode(lang($controller_name."_none_selected"));?>);
		<?php } else { ?>
			enable_delete(<?php echo json_encode(lang($controller_name."_confirm_undelete"));?>,<?php echo json_encode(lang($controller_name."_none_selected"));?>);
		<?php } ?>
			
		enable_cleanup(<?php echo json_encode(lang($controller_name."_confirm_cleanup"));?>);
		
				<?php if ($this->session->flashdata('manage_success_message')) { ?>
					show_feedback('success', <?php echo json_encode($this->session->flashdata('manage_success_message')); ?>, <?php echo json_encode(lang('common_success')); ?>);
				<?php } ?>
			
				$('#labels').click(function()
				{
					var selected = get_selected_values();
					if (selected.length == 0)
					{
						bootbox.alert(<?php echo json_encode(lang('common_must_select_customer_for_labels')); ?>);
						return false;
					}

					$(this).attr('href','<?php echo site_url("$controller_name/mailing_labels");?>/'+selected.join('~'));
				});
				
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
					var url = '<?php echo site_url("customers/generate_barcodes");?>'+'/'+selected.join('~')+'/'+num_labels_skip;
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

					$(this).attr('href','<?php echo site_url("customers/generate_barcode_labels");?>/'+selected.join('~'));
				});		
			
			
				$('#merge').click(function()
				{
					var selected = get_selected_values();
			
					if (selected.length == 0)
					{
						bootbox.alert(<?php echo json_encode(lang('common_must_select_item_for_barcode')); ?>);
						return false;
					}

					$("#customer_to_merge").empty();
					$.post(<?php echo json_encode(site_url("$controller_name/get_customers_info")); ?>, {customers:selected}, function(json)
					{
						for(var k=0;k<json.length;k++)
						{
							var customer_person_id = json[k]['person_id'];
							var customer_name = json[k]['full_name'];
							$("#customer_to_merge").append('<option value='+customer_person_id+'>'+customer_name+'</option>')
						}
						$("#merge-customers").modal('show');
					},'json');
					
					return false;
				});
				
				$("#merge_customers_form").submit(function(e)
				{
					e.preventDefault();
					var selected = get_selected_values();
			
					$.post(<?php echo json_encode(site_url("$controller_name/merge_customers")); ?>, {customers:selected,customer_to_merge:$("#customer_to_merge").val()}, function(json)
					{
						$("#merge-customers").modal('hide');
						show_feedback('success', <?php echo json_encode(lang('customers_merge_successful')); ?>, <?php echo json_encode(lang('common_success')); ?>);
						
						reload_people_table();
					});
						
				});


				$('#send_message').click(function()
				{

					var selected = get_selected_values();
					if (selected.length == 0)
					{
						bootbox.alert(<?php echo json_encode(lang('common_must_select_person_to_send_message')); ?>);
						return false;
					}

					$("#sendMessage").modal('show');
					$('#sendMessage').on('shown.bs.modal', function() {
						$('#text_message').focus();
					});
					return false;
				});
		
				$("#send_message_form").submit(function()
				{
					var selected_persons = get_selected_values();
					console.log(selected_persons);
					var text_message = $("#text_message").val();

					$.each(selected_persons, function(i,param){
						$('<input />').attr('type', 'hidden')
							.attr('name', 'selected_persons[]')
							.attr('value', param)
							.appendTo('#send_message_form');
					});

					$.post('<?php echo site_url("customers/send_message");?>', $(this).serialize(), function(result){
						var res = JSON.parse(result);

						var message = '';

						$.each( res.response, function( i, val ) {
							message += val + "<br>";
						});

						if(message != ''){
							bootbox.alert(message);
							return false;
						}
						$("#text_message").val('');
						$("#sendMessage").modal('hide');
						show_feedback('success', <?php echo json_encode(lang('common_sms_sent_successfully')); ?>, <?php echo json_encode(lang('common_success')); ?>);
						
						var url = '<?php echo site_url("customers")?>';
						setInterval(function(){ window.location = url; }, 1000);
						return false;
					});

					return false;
				});
		}); 
		
				
</script>

<?php if ($controller_name == 'customers') { ?>
<div class="modal fade skip-labels" id="skip-labels" role="dialog" aria-labelledby="skipLabels" aria-hidden="true">
    <div class="modal-dialog customer-recent-sales">
      	<div class="modal-content">
	        <div class="modal-header">
	          	<button type="button" class="close" data-dismiss="modal" aria-label=<?php echo json_encode(lang('common_close')); ?>><span aria-hidden="true">&times;</span></button>
	          	<h4 class="modal-title" id="skipLabels"><?php echo lang('common_skip_labels') ?></h4>
	        </div>
	        <div class="modal-body">
				
	          	<?php echo form_open("customers/generate_barcodes", array('id'=>'generate_barcodes_form','autocomplete'=> 'off')); ?>				
				<input type="text" class="form-control text-center" name="num_labels_skip" id="num_labels_skip" placeholder="<?php echo lang('common_skip_labels') ?>">
					<?php echo form_submit('generate_barcodes_form',lang("common_submit"),'class="btn btn-block btn-primary"'); ?>
				<?php echo form_close(); ?>
				
	        </div>
    	</div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->



<div class="modal fade skip-labels" id="merge-customers" role="dialog" aria-labelledby="skipLabels" aria-hidden="true">
    <div class="modal-dialog customer-recent-sales">
      	<div class="modal-content">
	        <div class="modal-header">
	          	<button type="button" class="close" data-dismiss="modal" aria-label=<?php echo json_encode(lang('common_close')); ?>><span aria-hidden="true">&times;</span></button>
	          	<h4 class="modal-title" id="skipLabels"><?php echo lang('customers_merge_customers') ?></h4>
	        </div>
	        <div class="modal-body">
				
	          	<?php echo form_open("customers/do_merge", array('id'=>'merge_customers_form','autocomplete'=> 'off')); ?>
							<label for="customer_to_merge"><?php echo lang('customers_merge_into');?></label>
							<select id="customer_to_merge" name="customer_to_merge" class="form form-control">
							</select>
							<br />
							
					<?php echo form_submit('merge_customers_form',lang("common_submit"),'class="btn btn-block btn-primary"'); ?>
				<?php echo form_close(); ?>
				
	        </div>
    	</div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<!-- Modal to send SMS-->
<div class="modal fade send-message" id="sendMessage" role="dialog" aria-labelledby="sendMessageLabels" aria-hidden="true">
    <div class="modal-dialog customer-recent-sales">
      	<div class="modal-content">
	        <div class="modal-header">
	          	<button type="button" class="close" data-dismiss="modal" aria-label=<?php echo json_encode(lang('common_close')); ?>><span aria-hidden="true">&times;</span></button>
	          	<h4 class="modal-title" id="sendMessageLabels"><?php echo lang('common_write_your_message') ?></h4>
	        </div>
	        <div class="modal-body">
				<?php echo form_open("customers/send_message", array('id'=>'send_message_form','autocomplete'=> 'off')); ?>
					<!-- <label for="text_message"><?php echo lang('common_write_your_message'); ?></label>-->
					<textarea id="text_message" name="text_message" class="form-control" rows="10" required></textarea>
				<?php echo form_submit('send_message_form', lang("common_submit"),'class="btn btn-block btn-primary"'); ?>
				<?php echo form_close(); ?>
	        </div>
    	</div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
<!-- Modal to send SMS end -->

<?php } ?>
<div class="manage_buttons">

	<!-- Css Loader  -->
	<div class="spinner" id="ajax-loader" style="display:none">
	  <div class="rect1"></div>
	  <div class="rect2"></div>
	  <div class="rect3"></div>
	</div>

<div class="manage-row-options hidden">
	<div class="email_buttons people">
		<?php if(!$deleted) { ?>
		<a class="btn btn-primary btn-lg disabled email email_inactive" title="<?php echo lang("common_email");?>" id="email" href="<?php echo current_url(). '#'; ?>" >
			<span class="ion-email"> <?php echo lang('common_email'); ?></span>
		</a>
		
		<a class="btn btn-primary btn-lg labels" title="<?php echo lang("common_mailing_labels");?>" id="labels" href="<?php echo current_url(). '#'; ?>" >
			<span class="ion-android-list"></span> <span class="hidden-xs"><?php echo lang('common_mailing_labels'); ?></span>
		</a>
		
		<?php if ($controller_name =='customers') {  ?>
		
		<?php echo 
			anchor("$controller_name/generate_barcode_labels",
			'<span class="ion-ios-barcode"></span> <span class="hidden-xs">'.lang("common_barcode_labels").'</span>',
			array('id'=>'generate_barcode_labels', 
				'class' => 'btn btn-primary btn-lg  disabled',
				'title'=>lang('common_barcode_labels'))); 
		?>
		<?php echo 
			anchor("$controller_name/generate_barcodes",
			'<span class="ion-document"></span> <span class="hidden-xs">'.lang("common_barcode_sheet").'</span>',
			array('id'=>'generate_barcodes', 
				'class' => 'btn btn-primary btn-lg  disabled',
				'target' => '_blank',
				'title'=>lang('common_barcode_sheet'))); 
				
		
				echo 
					anchor("$controller_name/merge",
					'<span class="ion-document"></span> <span class="hidden-xs">'.lang("common_merge").'</span>',
					array('id'=>'merge', 
						'class' => 'btn btn-primary btn-lg  disabled',
						'target' => '_blank',
						'title'=>lang('common_merge'))); 


						echo 
						anchor("$controller_name/send_sms",
						'<span class="ion-android-phone-portrait"></span> <span class="hidden-xs">'.lang("common_send_message").'</span>',
						array('id'=>'send_message', 
							'class' => 'btn btn-primary btn-lg',
							'target' => '_blank',
							'title'=>lang('common_send_message'))); 
		
				
		}
		?>
		
		<?php if ($this->Employee->has_module_action_permission($controller_name, 'delete', $this->Employee->get_logged_in_employee_info()->person_id)) {?>
		<?php echo anchor("$controller_name/delete",
				'<span class="ion-trash-a"></span> '.'<span class="hidden-xs">'.lang("common_delete").'</span>',
				array('id'=>'delete','class'=>'btn btn-red btn-lg disabled delete_inactive','title'=>lang("common_delete"))); ?>
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
	</div>
</div>
	<div class="row">
		<div class="col-md-8 col-sm-8 col-xs-8">
			<?php echo form_open("$controller_name/search",array('id'=>'search_form', 'autocomplete'=> 'off')); ?>
					<div class="search no-left-border">
						<ul class="list-inline">
							<li>
								<input type="text" class="form-control" name ='search' id='search' value="<?php echo H($search); ?>" placeholder="<?php echo $deleted ? lang('common_search_deleted') : lang('common_search'); ?> <?php echo lang('module_'.$controller_name); ?>"/>
							</li>
							
							<?php
							if ($controller_name == 'customers' && $this->Location->count_all() > 1) {
							?>
							<li class="hidden-xs">
								<?php echo lang('common_location'); ?>: 	
								<?php echo form_dropdown('location_id', $locations,$location_id, 'class="" id="location_id"'); ?>
							</li>
							
							<?php } ?>
							<li>
								<button type="submit" class="btn btn-primary btn-lg"><span class="ion-ios-search-strong"></span><span class="hidden-xs hidden-sm"> <?php echo lang("common_search"); ?></span></button>
							</li>
							<li>
								<div class="clear-block <?php echo ($search=='') ? 'hidden' : ''  ?>">
									<a class="clear" href="<?php echo site_url($controller_name.'/clear_state'); ?>">
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
					<?php if ($this->Employee->has_module_action_permission($controller_name, 'add_update', $this->Employee->get_logged_in_employee_info()->person_id) && !$deleted) {?>
					<?php echo anchor("$controller_name/view/-1/",
						'<span class="ion-plus"> '.lang($controller_name.'_new').'</span>',
						array('id' => 'new-person-btn', 'class'=>'btn btn-primary btn-lg hidden-sm hidden-xs', 'title'=>lang($controller_name.'_new')));
					}	
					?>
					<?php if ($deleted) 
					{
						echo 
						anchor("$controller_name/toggle_show_deleted/0",
							'<span class="ion-android-exit"></span> <span class="hidden-xs">'.lang('common_done').'</span>',
							array('class'=>'btn btn-primary btn-lg toggle_deleted','title'=> lang('common_done')));
					}	
					?>
					
					<?php if(!$deleted) { ?>
					<div class="piluku-dropdown btn-group">
						<button type="button" class="btn btn-more dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
							<span class="hidden-xs ion-android-more-horizontal"> </span>
							<i class="visible-xs ion-android-more-vertical"></i>
						</button>
						<ul class="dropdown-menu" role="menu">
							
							<?php if ($this->Employee->has_module_action_permission($controller_name, 'add_update', $this->Employee->get_logged_in_employee_info()->person_id)) { ?>
								<li class="visible-sm visible-xs">
									<?php echo anchor("$controller_name/view/-1/", '<span class="ion-plus-round"> '.lang('common_add').' '.lang($controller_name.'_new').'</span>',
										array('class'=>'', 'title'=>lang($controller_name.'_new'))); ?>
								</li>
							<?php } ?>
							<?php if ($controller_name =='customers' || $controller_name == 'suppliers') { ?>
								<li>	
									<?php echo anchor("$controller_name/excel_import/", '<span class="ion-ios-download-outline"> '.lang('common_excel_import').'</span>',
									 array('class'=>'hidden-xs','title'=>lang('common_excel_import'))); ?>
								</li>
							<?php } ?>
							<?php if ($this->Employee->has_module_action_permission($controller_name, 'excel_export', $this->Employee->get_logged_in_employee_info()->person_id)) {?>				
							
							<?php if ($controller_name == 'customers' || $controller_name == 'employees' || $controller_name == 'suppliers') { ?>
								<li>
									<?php echo anchor("$controller_name/excel_export",'<span class="ion-ios-upload-outline"> '.lang('common_excel_export').'</span>',
										array('class'=>'hidden-xs import','title'=>lang('common_excel_export'))); ?>
								</li>
							<?php } ?>
							<?php } ?>
							<?php if ($this->Employee->has_module_action_permission($controller_name, 'add_update', $this->Employee->get_logged_in_employee_info()->person_id)) { ?>
								<?php if ($controller_name =='customers' or $controller_name =='employees' or $controller_name =='suppliers') {?>
								<li>
									<?php echo anchor("$controller_name/cleanup", '<span class="ion-loop"> '.lang($controller_name."_cleanup_old_customers").'</span>',
										array('id'=>'cleanup','class'=>'','title'=> lang($controller_name."_cleanup_old_customers"))); ?>
								</li>
								<?php } ?>
							<?php } ?>
							<?php if ($controller_name =='customers' || $controller_name == 'suppliers' || $controller_name == 'employees') {?>
								<li>
									<?php echo anchor("$controller_name/custom_fields", '<span class="ion-wrench"> '.lang('common_custom_field_config').'</span>',
										array('id'=>'custom_fields', 'class'=>'','title'=> lang('common_custom_field_config'))); ?>
								</li>
							<?php } ?>
							
							<?php if ($this->Employee->has_module_action_permission($controller_name, 'delete', $this->Employee->get_logged_in_employee_info()->person_id)) {?>
								<?php if ($controller_name =='customers' or $controller_name =='employees' or $controller_name =='suppliers') {?>
								<li>
									<?php echo anchor("$controller_name/toggle_show_deleted/1", '<span class="ion-trash-a"> '.lang($controller_name."_manage_deleted").'</span>',
										array('class'=>'toggle_deleted','title'=> lang($controller_name."_manage_deleted"))); ?>
								</li>
								<?php } ?>
							<?php } ?>


							<?php if ($this->Employee->has_module_action_permission($controller_name, 'add_update', $this->Employee->get_logged_in_employee_info()->person_id) && !$deleted) {?>
								<?php if ($controller_name =='employees') {?>
								<li>
									<?php echo anchor("permission_templates", '<span class="ion-ios-list-outline"> '.lang("permission_templates").'</span>',
										array('id'=>'permission_templates','title'=> lang("permission_templates"))); ?>
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
<?php $this->load->view("partial/footer"); ?>