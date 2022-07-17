<?php $this->load->view("partial/header"); ?>

<div class="modal fade new_work_order_modal" id="new_work_order_modal" tabindex="-1" role="dialog" aria-labelledby="new_work_order" aria-hidden="true">
	<div class="modal-dialog customer-recent-sales">
		<div class="modal-content">
			<div class="spinner" id="grid-loader1" style="display:none">
				<div class="rect1"></div>
				<div class="rect2"></div>
				<div class="rect3"></div>
			</div>
	        <div class="modal-header">
	          	<button type="button" class="close" data-dismiss="modal" aria-label=<?php echo json_encode(lang('common_close')); ?>><span style = "font-size: 30px;" aria-hidden="true">&times;</span></button>
	          	<h5 style = "font-size: 20px;text-transform: none;" class="modal-title"><?php echo lang('work_orders_new_work_order'); ?></h5>
	        </div>
	        <div class="modal-body">
				<?php echo form_open_multipart('work_orders/save_new_work_order/',array('id'=>'new_work_order_form')); ?>
				
				<div class="panel panel-piluku customer_info">
					<div class="panel-heading">
						<div class="row">
							<div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
								<h3 class="panel-title"><i class="fas fa-user"></i> <?php echo lang("common_customer"); ?></h3>
							</div>	

							<div class="col-lg-8 col-md-8 col-sm-12 col-xs-12">
								<div class="customer_search">
									<div class="input-group">
										<span class="input-group-addon">
											<?php echo anchor("customers/view/-1/3?redirect_new_order=work_orders/index/0/1","<i class='ion-person-add'></i>", array('class'=>'none','title'=>lang('common_new_customer'), 'id' => 'new-customer', 'tabindex'=> '-1')); ?>
										</span>
										<input type="text" id="customer" name="customer" class="add-customer-input keyboardLeft form-control" data-title="<?php echo lang('common_customer_name'); ?>" placeholder="<?php echo lang('sales_start_typing_customer_name');?>">
									</div>
								</div>
							</div>		
						</div>
					</div>

					<div class="panel-body">
						<?php if($customer_id_for_new){ ?>
							<ul class="customer_name_address_ul list-style-none">
								<li class="customer_name font-weight-bold"><?php echo $customer_info->first_name.' '.$customer_info->last_name; ?></li>
								<li class="customer_address"><?php echo $customer_info->address_1.' '.$customer_info->address_2; ?></li>
								<li class="customer_city_state_zip"><?php echo $customer_info->city.','.$customer_info->state.' '.$customer_info->zip; ?></li>
							</ul>

							<ul class="customer_email_phonenumber_ul list-style-none">
								<li><a class="customer_email text-decoration-underline" href = "mailto:<?php echo $customer_info->email; ?>"><?php echo $customer_info->email; ?></a></li>
								<li><a class="customer_phonenumber text-decoration-underline" href = "tel:<?php echo $customer_info->phone_number; ?>"><?php echo $customer_info->phone_number; ?></a></li>
							</ul>
						<?php }else{ ?>
							<ul class="customer_name_address_ul" style="list-style: none">
								<li class="customer_name font-weight-bold"></li>
								<li class="customer_address"></li>
								<li class="customer_city_state_zip"></li>
							</ul>

							<ul class="customer_email_phonenumber_ul" style="list-style: none">
								<li><a class="customer_email text-decoration-underline" href = ""></a></li>
								<li><a class="customer_phonenumber text-decoration-underline" href = ""></a></li>
							</ul>
						<?php }?>
						<div class='clearfix'></div>
					</div><!--/panel-body -->
				</div><!-- /panel-piluku -->
				
				<div class="panel panel-piluku item_being_repaired_info">
					<div class="panel-heading">
						<div class="row">
							<div class="col-lg-4 col-md-4 col-sm-12 col-xs-12 item_being_repaired_info_title">
								<h3 class="panel-title"><i class="icon ti-harddrive"></i> <?php echo lang("work_orders_item_being_repaired"); ?></h3>
							</div>	

							<div class="col-lg-8 col-md-8 col-sm-12 col-xs-12">
								<div class="item_search">
									<div class="input-group">
										<span class="input-group-addon">
											<?php echo anchor("items/view/-1?redirect=work_orders/index/0&progression=1","<i class='icon ti-pencil-alt'></i>", array('class'=>'none add-new-item','title'=>lang('common_new_item'), 'id' => 'new-item', 'tabindex'=> '-1')); ?>
										</span>
										<input type="text" id="item" name="item"  class="add-item-input pull-left keyboardTop form-control" placeholder="<?php echo lang('common_start_typing_item_name'); ?>" data-title="<?php echo lang('common_item_name'); ?>">
									</div>
								</div>
							</div>		
						</div>
					</div>

					<div class="panel-body">
						<?php if($item_id_for_new){ ?>
							<dl class="dl-horizontal item_infomation">
								<dt><?php echo lang('common_description') ?></dt>
								<dd class="item_description"><?php echo clean_html($item_info->name.' '.$item_info->description); ?></dd>

								<dt><?php echo lang('common_category') ?></dt>
								<dd class="item_category"><?php echo $category_full_path; ?></dd>
								
								<dt class="serial <?php echo !$item_info->is_serialized ? 'hidden' : ''; ?>"><?php echo lang('common_serial_number') ?></dt>
								<dd class="serial <?php echo !$item_info->is_serialized ? 'hidden' : ''; ?>">
									<a href="#" id="serial_number" class="xeditable" data-value="<?php echo $item_serial_number_for_new; ?>" data-type="text" data-pk="1" data-title="<?php echo H(lang('common_serial_number')); ?>"></a>
								</dd>
								
								<dt><?php echo lang('common_item_number_expanded') ?></dt>
								<dd class="item_number"><?php echo $item_info->item_number; ?></dd>
							</dl>
						<?php }else{ ?>
							<dl class="dl-horizontal hidden item_infomation">
								<dt><?php echo lang('common_description') ?></dt>
								<dd class="item_description"></dd>

								<dt><?php echo lang('common_category') ?></dt>
								<dd class="item_category"></dd>
								
								<dt class="serial hidden"><?php echo lang('common_serial_number') ?></dt>
								<dd class="serial hidden">
									<a href="#" id="serial_number" class="xeditable" data-type="text" data-pk="1" data-title="<?php echo H(lang('common_serial_number')); ?>"></a>
								</dd>
								
								<dt><?php echo lang('common_item_number_expanded') ?></dt>
								<dd class="item_number"></dd>
							</dl>
						<?php }?>
					</div><!--/panel-body -->
				</div><!-- /panel-piluku -->

				<input type="hidden" name="customer_id" id="customer_id" value="<?php echo $customer_id_for_new; ?>">
				<input type="hidden" name="item_id" id="item_id" value="<?php echo $item_id_for_new; ?>">
				<input type="hidden" name="item_serial_number" id="item_serial_number" value="<?php echo $item_serial_number_for_new; ?>">

				
				<div class="form-actions">
					<?php
						echo form_submit(array(
							'name'=>'sale_item_notes_save_btn',
							'id'=>'sale_item_notes_save_btn',
							'value'=>lang('common_save'),
							'class'=>'submit_button pull-right btn btn-primary sale_item_notes_save_btn')
						);
						
						echo form_input(array(
							'type' =>'button',
							'value'=>lang('common_cancel'),
							'data-dismiss' => 'modal',
							'style' => 'margin-right: 10px;',
							'class'=>'pull-right btn btn-warning')
						);

						
					?>
					<div class="clearfix">&nbsp;</div>
				</div>
				<?php echo form_close(); ?>		
	        </div>
    	</div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<div class="spinner" id="grid-loader" style="display:none">
	<div class="rect1"></div>
	<div class="rect2"></div>
	<div class="rect3"></div>
</div>

<script type="text/javascript">

	function reload_work_order_table()
	{
		clearSelections();
		$("#table_holder").load(<?php echo json_encode(site_url("$controller_name/reload_work_order_table")); ?>);
	}
	
	$(document).ready(function()
	{	
		$("#technician").select2({dropdownAutoWidth : true});

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
					reload_work_order_table();
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
					reload_work_order_table();
				});
				
		});
		
		
		enable_sorting("<?php echo site_url("$controller_name/sorting"); ?>");
		enable_select_all();
		enable_checkboxes();
		enable_row_selection();
		enable_search('<?php echo site_url("$controller_name/suggest");?>',<?php echo json_encode(lang("common_confirm_search"));?>);
		
		<?php if(!$deleted) { ?>
			enable_delete(<?php echo json_encode(lang($controller_name."_confirm_delete"));?>,<?php echo json_encode(lang($controller_name."_none_selected"));?>);
		<?php } else { ?>
			enable_delete(<?php echo json_encode(lang($controller_name."_confirm_undelete"));?>,<?php echo json_encode(lang($controller_name."_none_selected"));?>);
		<?php } ?>
		
		$('#print_work_order_btn').click(function()
		{
			var selected = get_selected_values();
			
			$(this).attr('href','<?php echo site_url("$controller_name/print_work_order");?>/'+selected.join('~'));
		});	

		$('#print_service_tag_btn').click(function()
		{
			var selected = get_selected_values();
			if (selected.length == 0)
			{
				bootbox.alert(<?php echo json_encode(lang('common_must_select_item_for_barcode')); ?>);
				return false;
			}

			var default_to_raw_printing = "<?php echo $this->config->item('default_to_raw_printing'); ?>";
			if(default_to_raw_printing == "1"){
				$(this).attr('href','<?php echo site_url("work_orders/raw_print_service_tag");?>/'+selected.join('~'));
			}
			else{
				$(this).attr('href','<?php echo site_url("work_orders/print_service_tag");?>/'+selected.join('~'));
			}
		});	

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
						
						$.post('<?php echo site_url("$controller_name/change_status/");?>', {work_order_ids : selected,status:status},function(response) {
							$('#grid-loader').hide();
							show_feedback(response.success ? 'success' : 'error', response.message,response.success ? <?php echo json_encode(lang('common_success')); ?> : <?php echo json_encode(lang('common_error')); ?>);

							//Refresh tree if success
							if (response.success)
							{
								setTimeout(function(){location.href = location.href;},800);
							}
						}, "json");
					}
				});
			}

		});

		$(".excel_export_btn").click(function(e){
			var selected = get_selected_values();
			$(this).attr('href','<?php echo site_url("$controller_name/excel_export_selected_rows");?>/'+selected.join('~'));
		});
	});
</script>

<?php if(count($status_boxes) > 0){ ?>
<div class="work_order_status_box">
	<?php
		foreach($status_boxes as $status_box){
	?>
		<button class="btn btn-lg status_box_btn <?php echo $status_box['id'] == $status?'selected_status':''; ?>" data-status_id="<?php echo $status_box['id']; ?>" style="background-color:<?php echo $status_box['color']; ?>"><span class="status_name"><?php echo $this->Work_order->get_status_name($status_box['name']); ?></span><br><span class="total_number"><?php echo $status_box['total_number']; ?></span></button>
	<?php } ?>
</div>
<?php } ?>

<div class="manage_buttons">
<!-- Css Loader  -->
<div class="spinner" id="ajax-loader" style="display:none">
	<div class="rect1"></div>
	<div class="rect2"></div>
	<div class="rect3"></div>
</div>
<div class="manage-row-options hidden">
	<div class="email_buttons work_orders text-center">		
		
	<?php if(!$deleted) { ?>
		<?php if ($this->Employee->has_module_action_permission($controller_name, 'delete', $this->Employee->get_logged_in_employee_info()->person_id)) {?>
		<?php echo anchor("$controller_name/delete",
			'<span class="ion-trash-a"></span> <span class="hidden-xs">'.lang('common_delete').'</span>'
			,array('id'=>'delete', 'class'=>'btn btn-red btn-lg disabled delete_inactive ','title'=>lang("common_delete"))); ?>
		<?php } ?>

		<a href="#" class="btn btn-lg btn-clear-selection btn-warning"><span class="ion-close-circled"></span> <span class="hidden-xs"><?php echo lang('common_clear_selection'); ?></span></a>
		<a href="#" class="btn btn-lg btn-primary" id="print_work_order_btn"><?php echo lang('work_orders_print_work_order'); ?></a>
		<a href="#" class="btn btn-lg btn-primary" id="print_service_tag_btn"><?php echo lang('work_orders_print_service_tag'); ?></a>
		<?php 
			echo form_dropdown('change_status', $change_status_array,'', 'class="" id="change_status"'); 
		?>
		<a href="#" class="btn btn-lg btn-success excel_export_btn"><?php echo lang('common_excel_export'); ?></a>
	
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
		<div class="col-md-9 col-sm-10 col-xs-10">
			<?php echo form_open("$controller_name/search",array('id'=>'search_form', 'autocomplete'=> 'off')); ?>
				<div class="search no-left-border">
					<ul class="list-inline">
						<li class="hidden-xs">
							<?php echo lang('work_orders_technician'); ?>: 	
							<?php 
								echo form_dropdown('technician', $employees,$technician, 'class="" id="technician"'); 
							?>
						</li>
						<li>
							<input type="text" class="form-control" name ='search' id='search' value="<?php echo H($search); ?>" placeholder="<?php echo $deleted ? lang('common_search_deleted') : lang('common_search'); ?> <?php echo lang('module_'.$controller_name); ?>"/>
						</li>
						<li class="hidden-xs">
							<?php echo form_label(lang('work_orders_hide_completed_work_orders').':', 'hide_completed_work_orders',array('class'=>'control-label ')); ?>	
							<br />
							<?php echo form_checkbox(array(
							'name'=>'hide_completed_work_orders',
							'id'=>'hide_completed_work_orders',
							'value'=>'1',
							'checked'=>$hide_completed_work_orders?true:false));?>
							<label for="hide_completed_work_orders"><span></span></label>
						</li>
						<li>
							<button type="submit" class="btn btn-primary btn-lg"><span class="ion-ios-search-strong"></span><span class="hidden-xs hidden-sm"> <?php echo lang("common_search"); ?></span></button>
						</li>
						<li>
							<div class="clear-block <?php echo ($search=='' && $status == '' && $technician == '') ? 'hidden' : ''  ?>">
								<a class="clear" href="<?php echo site_url($controller_name.'/clear_state'); ?>">
									<i class="ion ion-close-circled"></i>
								</a>	
							</div>
						</li>
					</ul>
				</div>
				<input type="hidden" name="status" id="status" value="<?php echo $status; ?>">

			</form>	
		</div>
		<div class="col-md-3 col-sm-2 col-xs-2">	
			<div class="buttons-list">
				<div class="pull-right-btn">
					<!-- right buttons-->
					<?php if ($this->Employee->has_module_action_permission($controller_name, 'edit', $this->Employee->get_logged_in_employee_info()->person_id) && !$deleted) {?>
					<?php echo anchor("",
						'<span class="ion-plus"> '.lang('work_orders_new_work_order').'</span>',
						array('id' => 'new_work_order_btn', 'class'=>'btn btn-primary btn-lg', 'title'=>lang('work_orders_new_work_order')));
					}	
					?>
					<?php if($deleted) { 
						echo 
						anchor("$controller_name/toggle_show_deleted/0",
							'<span class="ion-android-exit"></span> <span class="hidden-xs">'.lang('common_done').'</span>',
							array('class'=>'btn btn-primary btn-lg toggle_deleted','title'=> lang('common_done')));
					} ?>
					
					<?php if(!$deleted) { ?>
								
					<div class="piluku-dropdown btn-group">
						<button type="button" class="btn btn-more dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
						<span class="hidden-xs ion-android-more-horizontal"> </span>
						<i class="visible-xs ion-android-more-vertical"></i>
					</button>
					<ul class="dropdown-menu" role="menu">
						<?php if ($this->Employee->has_module_action_permission($controller_name, 'manage_statuses', $this->Employee->get_logged_in_employee_info()->person_id)) {?>				
							<li>
								<?php echo anchor("$controller_name/manage_statuses?redirect=work_orders",'<span class="ion-settings"> '.lang('module_manage_statuses').'</span>',
									array('class'=>'manage_statuses','title'=>lang('module_manage_statuses'))); ?>
							</li>
						<?php } ?>

						<li>
							<?php echo anchor("$controller_name/custom_fields", '<span class="ion-wrench"> '.lang('common_custom_field_config').'</span>',array('id'=>'custom_fields', 'class'=>'','title'=> lang('common_custom_field_config'))); ?>
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
						
						
					<span title="<?php echo $total_rows; ?> total work orders" class="badge bg-primary tip-left" id="manage_total_items"><?php echo $total_rows; ?></span>
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
<script type="text/javascript">

	$(document).ready(function() 
	{
		<?php if ($this->session->flashdata('success')) { ?>
		show_feedback('success', <?php echo json_encode($this->session->flashdata('success')); ?>, <?php echo json_encode(lang('common_success')); ?>);
		<?php } ?>

		<?php if ($this->session->flashdata('error')) { ?>
		show_feedback('error', <?php echo json_encode($this->session->flashdata('error')); ?>, <?php echo json_encode(lang('common_error')); ?>);
		<?php } ?>

		<?php if($open_new){?>
			$("#new_work_order_btn").trigger('click');
		<?php } ?>
	});

	$("#new_work_order_btn").click(function(e){
		e.preventDefault();
		$("#new_work_order_modal").modal('show');
	
		$("#new_work_order_modal").on('shown.bs.modal', function (e) {
	    	$('#item').focus();
		});
	
	
	});
		

	$( "#customer" ).autocomplete({
		source: '<?php echo site_url("work_orders/customer_search");?>',
		delay: 150,
		autoFocus: false,
		minLength: 0,
		appendTo:'#new_work_order_modal',
		select: function( event, ui ) 
		{
			$('#customer_id').val(decodeHtml(ui.item.value));

			$.post('<?php echo site_url("work_orders/select_customer");?>', {customer: decodeHtml(ui.item.value) }, function(response)
			{
				var customer_info = response.customer_data;
				$('#customer').val('');
				$('.customer_name').html(customer_info.first_name+' '+customer_info.last_name);
				$('.customer_address').html(customer_info.address_1+' '+customer_info.address_2);
				$('.customer_city_state_zip').html(customer_info.city+','+customer_info.state+' '+customer_info.zip);

				$('.customer_email').html(customer_info.email);
				$('.customer_email').attr('href','mailto:'+customer_info.email);

				$('.customer_phonenumber').html(customer_info.phone_number);
				$('.customer_phonenumber').attr('href','tel:'+customer_info.phone_number);
				
				
			},'json');
		},
	}).data("ui-autocomplete")._renderItem = function (ul, item) {
			return $("<li class='customer-badge suggestions'></li>")
				.data("item.autocomplete", item)
				.append('<a class="suggest-item"><div class="avatar">' +
							'<img src="' + item.avatar + '" alt="">' +
						'</div>' +
						'<div class="details">' +
							'<div class="name">' + 
								item.label +
							'</div>' + 
							'<span class="email">' +
								item.subtitle + 
							'</span>' +
						'</div></a>')
				.appendTo(ul);
		};

	if ($("#item").length)
	{
		$( "#item" ).autocomplete({
			source: '<?php echo site_url("work_orders/item_search");?>',
			delay: 150,
			autoFocus: false,
			minLength: 0,
			appendTo:'#new_work_order_modal',
			select: function( event, ui ) 
			{
				item_select(ui.item.value);
			},
		}).data("ui-autocomplete")._renderItem = function (ul, item) {
		return $("<li class='item-suggestions'></li>")
			.data("item.autocomplete", item)
			.append('<a class="suggest-item"><div class="item-image">' +
						'<img src="' + item.image + '" alt="">' +
					'</div>' +
					'<div class="details">' +
						'<div class="name">' + 
							item.label +
						'</div>' +
						'<span class="attributes">' + '<?php echo lang("common_category"); ?>' + ' : <span class="value">' + (item.category ? item.category : <?php echo json_encode(lang('common_none')); ?>) + '</span></span>' +
						(typeof item.quantity !== 'undefined' && item.quantity!==null ? '<span class="attributes">' + '<?php echo lang("common_quantity"); ?>' + ' <span class="value">'+item.quantity + '</span></span>' : '' )+
						(item.attributes ? '<span class="attributes">' + '<?php echo lang("common_attributes"); ?>' + ' : <span class="value">' +  item.attributes + '</span></span>' : '' ) +
					
					'</div>')
			.appendTo(ul);
			};

			$('#item').bind('keypress', function(e) {
				if(e.keyCode==13)
				{
					e.preventDefault();
					$.get('<?php echo site_url("work_orders/item_search");?>', {term: $("#item").val()}, function(response)
					{
						var data = JSON.parse(response);
						if(data.length == 1){
							item_select(data[0].value);
						}
					});
				}
			});
	}

	
	function item_select(item_id){
		$('#item_id').val(item_id);

		$.post("<?php echo site_url('work_orders/select_item_being_repaired') ?>", {item_id:item_id},function(response) {
			$('#item').val('');

			var item_info = response.item_info;
			$('.item_description').html(item_info.name+' '+item_info.description);
			$('.item_category').html(response.category_full_path);
			$('.item_number').html(item_info.item_number);

			$('#item_serial_number').val('');
			$('#serial_number').editable('setValue',"");

			if(item_info.is_serialized == '1'){
				$('.serial').removeClass('hidden');
			}
			else{
				$('.serial').addClass('hidden');
			}

			var item_tmcm = response.item_tmcm;
			if(item_tmcm == ''){
				$('.type_manufacturer_caliber_model_dt').addClass('hidden');
				$('.type_manufacturer_caliber_model_dd').addClass('hidden');
			}
			else{
				$('.type_manufacturer_caliber_model_dd').html(item_tmcm['type']+' | '+item_tmcm['manufacturer']+' | '+item_tmcm['caliber']+' | '+item_tmcm['model']);

				$('.type_manufacturer_caliber_model_dt').removeClass('hidden');
				$('.type_manufacturer_caliber_model_dd').removeClass('hidden');
			}

			$('.item_infomation').removeClass('hidden');

		},'json');
	}

	$('#serial_number').editable({
    	success: function(response, newValue) {
			$('#item_serial_number').val(newValue);
			
		}
    });

	$("#new_work_order_form").submit(function(e)
	{
		e.preventDefault();
		var customer_id = $("#customer_id").val();
		var item_id = $("#item_id").val();
		var item_serial_number = $("#item_serial_number").val();

		if(customer_id == ''){
			show_feedback('error','<?php echo lang('work_orders_must_select_customer'); ?>','<?php echo lang('common_error'); ?>');
			return false;
		}
		if(item_id == ''){
			show_feedback('error','<?php echo lang('work_orders_must_select_item'); ?>','<?php echo lang('common_error'); ?>');
			return false;
		}
		if(!$(".serial").hasClass("hidden")){
			if(item_serial_number == ''){
				show_feedback('error','<?php echo lang('work_orders_must_enter_serial_number'); ?>','<?php echo lang('common_error'); ?>');
				return false;
			}
		}
		$("#grid-loader1").show()
		$(this).ajaxSubmit({ 
			success: function(response, statusText, xhr, $form){
				$("#grid-loader1").hide()
				if(response.success)
				{
					location.href="<?php echo site_url('work_orders/view/'); ?>"+response.work_order_id;
				}
				else{
					if(response.missing_required_information){
						bootbox.confirm(response.message, function(result)
						{
							if(result)
							{
								location.href="<?php echo site_url('items/view/'); ?>"+item_id+"?redirect=work_orders/index/0&progression=1";
							}
						});
					}
					else{
						show_feedback('error', response.message,<?php echo json_encode(lang('common_error')); ?>);
					}
				}		
			},
			dataType:'json',
		});
	});

	$(".status_box_btn").click(function(){
		$(".status_box_btn").removeClass('selected_status');
		$(this).addClass('selected_status');
		$("#status").val($(this).data('status_id'));
		$("#search_form").submit();
	});
</script>
	
<?php $this->load->view("partial/footer"); ?>

