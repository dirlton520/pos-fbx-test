<div class="container-fluid">

<div class="row register">
	<div class="col-lg-12 col-md-12 no-padding-left no-padding-right">
		
		<?php if ($count_info->status == 'open') { ?>
			<div class="register-box register-items-form">
				<div class="item-form">
					<!-- Item adding form -->
					<?php echo form_open("items/add_item_to_inventory_count",array('id'=>'add_item_form','class'=>'form-inline', 'autocomplete'=> 'off')); ?>
						<div class="input-group input-group-mobile contacts">
							<span class="input-group-addon register-mode <?php echo $mode; ?>-mode dropdown">
								<?php echo anchor("#","<i class='icon ti-panel'></i> <span class='register-btn-text'>".$modes[$mode]."</span>", array('class'=>'none active','tabindex'=>'-1','title'=>$modes[$mode], 'id' => 'select-mode-1', 'data-target' => '#', 'data-toggle' => 'dropdown', 'aria-haspopup' => 'true', 'role' => 'button', 'aria-expanded' => 'false')); ?>
						        <ul class="dropdown-menu sales-dropdown">
						        <?php foreach ($modes as $key => $value) {
						        	if($key!=$mode){
						        ?>
						        	<li><a tabindex="-1" href="#" data-mode="<?php echo H($key); ?>" class="change-mode"><?php echo $value;?></a></li>
						        <?php }  
							  	} ?>
	        					</ul>
							</span>						
						</div>
						
						<div class="input-group contacts register-input-group">						
							<input type="text" id="item" name="item"  class="add-item-input items-count pull-left" placeholder="<?php echo H(lang('common_start_typing_item_name')); ?>">
							<span class="input-group-addon register-mode <?php echo $mode; ?>-mode dropdown inventory-count">
								<?php echo anchor("#","<i class='icon ti-panel'></i> <span class='register-btn-text'>".$modes[$mode]."</span>", array('class'=>'none active','tabindex'=>'-1','title'=>$modes[$mode], 'id' => 'select-mode-1', 'data-target' => '#', 'data-toggle' => 'dropdown', 'aria-haspopup' => 'true', 'role' => 'button', 'aria-expanded' => 'false')); ?>
						        <ul class="dropdown-menu sales-dropdown">
						        <?php foreach ($modes as $key => $value) {
						        	if($key!=$mode){
						        ?>
						        	<li><a tabindex="-1" href="#" data-mode="<?php echo H($key); ?>" class="change-mode"><?php echo $value;?></a></li>
						        <?php }  
							  	} ?>
	        					</ul>
							</span>
							<!-- Santosh Changes -->
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
							<!-- END  -->
							
							
						</div>

					</form>
				</div>
			</div>
			<?php  } ?>
		</div>
	
		<?php if($pagination) {  ?>
			<div class="pagination alternate hidden-print m-t-10 text-center" id="pagination_top">
				<?php echo $pagination;?>		
			</div>
		<?php }  ?>
	</div>
	<div class="row register">
		<div class="col-lg-12 col-md-12 no-padding-left no-padding-right">

		<div class="register-box register-items paper-cut">
			<div class="register-items-holder table-responsive">
				<table id="register" class="table table-hover">
					<thead>
						<tr class="register-items-header">
							<?php $tableArr = []; foreach($selected_columns as $sel_col_key => $sel_col_value) { 
								array_push($tableArr,$sel_col_key);
								if($sel_col_key == 'actual_quantity')
								{
									if ($this->Employee->has_module_action_permission('items', 'see_count_when_count_inventory', $this->Employee->get_logged_in_employee_info()->person_id)) { 
									?>
									<th><?php echo $sel_col_value['label'];?></th>
									<?php }}else{ ?>
									<th><?php echo $sel_col_value['label'];?></th>
									<?php }}
							?>
							<?php if ($count_info->status == 'open') { ?>
								<th><?php echo lang('common_delete');?></th>
							<?php } ?>

							
						</tr>
					</thead>
				
					<tbody class="register-item-content">
						<?php foreach($items_counted as $key=> $counted_item) { ?>
							<tr class="register-item-details">
								<?php foreach($tableArr as $table_column) { ?>
									<?php if($table_column == 'name'){ ?>
										<td><a href="<?php echo site_url('home/view_item_modal').'/'.$counted_item['item_id']; ?>" data-toggle="modal" class="register-item-name count-items" data-target="#myModal"><?php echo H($counted_item['name']).' ('.H($counted_item['category']).')'; ?></a></td>
									<?php }else if($table_column == 'item_variation_id'){?>
										<?php if ($count_info->status == 'open') { ?>
											<?php if(isset($counted_item['variations'])) { ?>
												<td class="text-center"><a href="#" id="variation" class="xeditable" data-type="select" data-pk="<?php echo H($counted_item['id']); ?>" data-name="variation" data-url="<?php echo site_url('items/edit_count_item'); ?>" data-title="<?php echo H(lang('items_edit_variation')); ?>" data-value="<?php echo $counted_item['item_variation_id']; ?>" data-source="<?php echo H(json_encode($counted_item['variations'])); ?>" data-prepend=<?php echo json_encode(lang('common_empty')); ?>></a></td>
											<?php } else {  ?>
												<td class="text-center"><?php echo lang('common_none'); ?></td>
											<?php } ?>
										<?php } else {  ?>
											<?php if(isset($counted_item['variations'])) { ?>
												<td class="text-center"><?php echo $counted_item['variations'][$counted_item['item_variation_id']]; ?></td>
											<?php } else {  ?>
												<td class="text-center"><?php echo lang('common_none'); ?></td>
											<?php } ?>
										<?php } ?>

									<?php }else if($table_column == 'count') { ?>
										<td class="text-center"><a href="#" id="count" class="xeditable" data-type="text" data-pk="<?php echo H($counted_item['id']); ?>" data-name="quantity" data-url="<?php echo site_url('items/edit_count_item'); ?>" data-title="<?php echo H(lang('items_edit_count')); ?>"><?php echo to_quantity($counted_item['count']); ?></a></td>

									<?php }else if($table_column == 'actual_quantity'){ ?>
										<?php if ($this->Employee->has_module_action_permission('items', 'see_count_when_count_inventory', $this->Employee->get_logged_in_employee_info()->person_id)) {?>
											<td class="text-center actual_quantity"><?php echo to_quantity($counted_item['actual_quantity']);?></td>
										<?php } ?>

									<?php }else if($table_column == 'comment'){	?>
										<td class="text-center"><a href="#" id="comment" class="xeditable" data-type="text" data-pk="<?php echo H($counted_item['id']); ?>" data-name="comment" data-url="<?php echo site_url('items/edit_count_item'); ?>" data-title="<?php echo H(lang('items_edit_comment')); ?>"><?php echo $counted_item['comment'] ? H($counted_item['comment']): 'None'; ?></a></td>
									
									<?php }else if($table_column == 'total_value_by_cost_price'){	?>
										<td class="text-right">
											<?php 
												$total_value_by_cost_price = $counted_item['raw_cost_price'] * $counted_item['count'];
												echo to_currency($total_value_by_cost_price);
											?>
										</td>

									<?php }else if($table_column == 'total_value_by_selling_price'){	?>
										<td class="text-right">
											<?php 
												$total_value_by_selling_price = $counted_item['raw_unit_price'] * $counted_item['count'];
												echo to_currency($total_value_by_selling_price);
											?>
										</td>

									<?php }else{?>
										<td class="text-center"><a href="#" id="<?php echo  $table_column;?>"  data-name="<?php echo  $table_column;?>"  data-title="<?php echo  $table_column;?>"><?php echo $counted_item[$table_column]; ?></a></td>
									<?php } ?>
								<?php }	?>
								
								<?php if ($count_info->status == 'open') { ?>
									<td class="text-center"><?php echo anchor('items/delete_inventory_count_item/'.$counted_item['item_id'].($counted_item['item_variation_id'] ? rawurlencode('#').$counted_item['item_variation_id'] : ''), 'Delete Count Item',array('class' =>'text-danger delete-link'));?></td>
								<?php } ?>
							</tr>
						<?php } ?>
					</tbody>

					<tfoot>
						<tr class="register-items-header">
							<?php foreach($tableArr as $key => $table_column) { ?>
								<?php if($key === 0 && $table_column != 'total_value_by_cost_price' && $table_column != 'total_value_by_selling_price'){ ?>
									<th><?php echo lang('common_total');?></th>
								<?php } else if($table_column == 'name'){?>
									<th><?php echo lang('common_total');?></th>
								<?php }else{ ?>
									<?php if($table_column == 'total_value_by_cost_price'){?>
										<th style="text-align:right;"><?php echo to_currency($grand_total_value_by_cost_price);?></th>
									<?php }else if($table_column == 'total_value_by_selling_price'){?>
										<th style="text-align:right;"><?php echo to_currency($grand_total_value_by_selling_price);?></th>
									<?php }else{?>
										<th></th>
									<?php } ?>
								<?php } ?>
							<?php } ?>

							<?php if ($count_info->status == 'open') { ?>
								<th></th>
							<?php } ?>
						</tr>
					</tfoot>
				</table>
			</div>
		</div>

		<?php if($pagination) {  ?>
			<div class="pagination alternate hidden-print m-b-10 text-center" id="pagination_top">
				<?php echo $pagination;?>		
			</div>
		<?php }  ?>

<?php if ($count_info->status == 'open') { ?>

	<ul class="list-inline count-items-buttons">
		<?php if ($this->Employee->has_module_action_permission('items','edit_quantity', $this->Employee->get_logged_in_employee_info()->person_id))
		{
			echo "<li>".anchor('items/finish_count/1', lang('items_close_finish_count_update_inventory'),array('class'=>"btn btn-danger btn-lg finish-count"))."</li>";
		} ?>
		
		<li>
			<?php echo anchor('items/finish_count/0', lang('items_close_finish_count_do_not_update_inventory'),array('class'=>"btn btn-warning btn-lg finish-count"));?>
		</li>
		<li>
			<?php echo anchor('items/count', lang('items_continue_count_later'),array('class'=>'btn btn-primary btn-lg'));?>
		</li>
	</ul>
<?php } ?>

<br />


<script type='text/javascript'>
	
var mode = <?php echo json_encode($mode); ?>;
function reload_items_table()
	{
		clearSelections();
		location.reload();
	}

$(document).ready(function()
{
	//Santosh Changes
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
		
		$("#sortable a").on("click", function(e) {
			e.preventDefault();
			
			if($(this).attr("id") == "reset_to_default")
			{
				//Send a get request wihtout columns will clear column prefs
				$.get(<?php echo json_encode(site_url("$controller_name/save_inventory_column_prefs")); ?>, function()
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
		
		
		$("#sortable input[type=checkbox]").change(

			function(e) {
				var columns = $("#sortable input:checkbox:checked").map(function(){
      		return $(this).val();
    		}).get();
				$.post(<?php echo json_encode(site_url("$controller_name/save_inventory_column_prefs")); ?>, {columns:columns}, function(json)
				{
					reload_items_table();
				});
				
		});
		
	//END

	// if #mode is changed
	$('.change-mode').click(function(e){
		e.preventDefault();
		
		$.post('<?php echo site_url("items/change_count_mode");?>', {mode: $(this).data('mode')}, function(response)
		{
			$("#count_container").html(response);
		});
	});
	
	
	<?php if ($count_info->status == 'open') { ?>
		
	  $('.xeditable').editable({
	  	success: function(response, newValue) {
				var $tr = $(this).closest('tr');
				var quantity_field = $tr.find('a[data-name="quantity"]');
				var variation_field = $tr.find('a[data-name="variation"]');
				var actual_quantity_field = $tr.find('.actual_quantity');
				
				
			
				var is_variation_edit = variation_field.eq(0)[0] == $(this).eq(0)[0];
				var is_quantity_edit = quantity_field.eq(0)[0] == $(this).eq(0)[0];
					
				if(is_variation_edit)
				{
					var result = jQuery.parseJSON(response);
					
					var delete_link  = $tr.find('.delete-link');
					delete_link.attr('href',result.delete_href);
					if (result.actual_quantity)
					{
						actual_quantity_field.text(result.actual_quantity);
					}
					else
					{
						actual_quantity_field.text(<?php echo json_encode(lang('common_not_set')); ?>);
					}		
				}
								
				if(!is_quantity_edit)
				{
					var qty = quantity_field.eq(0).editable("getValue").quantity;
					if(qty == 0)
					{
						setTimeout(function() {
				        quantity_field.editable('show');
						}, 50);
					}					
				}
				
				if(is_quantity_edit){
					$("#count_container").html(response);
				}
				
	  	}
	  });
		
    $('.xeditable').on('shown', function(e, editable) {
		
 		$(this).closest('.table-responsive').css('overflow-x','hidden');

 	   	editable.input.postrender = function() {
 			//Set timeout needed when calling quantity_to_change.editable('show') (Not sure why)
 			setTimeout(function() {
 	        editable.input.$input.select();
 			}, 200);
 		};
 	});

 	$('.xeditable').on('hidden', function(e, editable) {
 		$(this).closest('.table-responsive').css('overflow-x','auto');
 	});
	
  	$('.xeditable').on('save', function(e, params) {
 		$("#item").focus();
  	});

 	$("#item").focus();
 	$('#add_item_form').ajaxForm({target: "#count_container", success: itemAddSuccess, error: itemAddError });
	
 	$( "#item" ).autocomplete({
  		source: '<?php echo site_url("items/item_search");?>',
 		delay: 500,
  		autoFocus: false,
  		minLength: 0,
  		select: function( event, ui ) 
  		{
  			$.post('<?php echo site_url("items/add_item_to_inventory_count");?>', {item: decodeHtml(ui.item.value) }, function(response)
 			{
 				$("#count_container").html(response);
 				$('#item').focus();
 				itemAddSuccess();
 			});	
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
 							(item.attributes ? '<span class="attributes">' + '<?php echo lang("common_attributes"); ?>' + ' : <span class="value">' +  item.attributes + '</span></span>' : '' ) +
							
 						'</div>')
              .appendTo(ul);
      };	
	
 	$(".finish-count").click(function(e)
 	{
 		e.preventDefault();
 		var $that = $(this);
		
 		bootbox.confirm(<?php echo json_encode(lang('items_confirm_finish_count')); ?>, function(result)
 		{
 			if(result)
 			{
 				$.getJSON($that.attr('href'), function(response) {
					
 					if(!response.success)
 					{
 						show_feedback('error',response.message,<?php echo json_encode(lang('common_error')); ?>);
 					} else {
 						show_feedback('success',response.message,<?php echo json_encode(lang('common_success')); ?>);
 						setTimeout(function() {
 							window.location = <?php echo json_encode(site_url('items/count')); ?>
 						},1000)
 					}
					
 				});
 			}
 		});
 	});
 });

 function itemAddSuccess(responseText, statusText)
 {
	if (ENABLE_SOUNDS)
	{
		$.playSound(BASE_URL + 'assets/sounds/success');
	}
	 
 	if (mode == 'scan_and_set') {
		
 		var $tr = $('.register-item-content').children('tr:first');
 		var quantity_to_change = $tr.find('a[data-name="quantity"]');
 		var variation_to_change = $tr.find('a[data-name="variation"]');
		
 		if(variation_to_change.length > 0 && !variation_to_change.val() && variation_to_change.eq(0).editable("getValue").variation == '')
 		{
 			variation_to_change.editable('show');
 		}
 		else
 		{
 			quantity_to_change.editable('show');
 		}
  	}
 }

 function itemAddError(responseText, statusText)
 {
 	show_feedback('error',<?php echo json_encode(lang('items_inventory_count_error')); ?>,<?php echo json_encode(lang('common_error')); ?>);
 	$('#item').val('');
 }
 
<?php } ?>
 
</script>