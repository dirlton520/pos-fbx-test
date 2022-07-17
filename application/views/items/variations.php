<?php $this->load->view("partial/header"); ?>
<?php $query = http_build_query(array('redirect' => $redirect, 'progression' => $progression ? 1 : null, 'quick_edit' => $quick_edit ? 1 : null)); ?>
<?php $manage_query = http_build_query(array('redirect' => uri_string().($query ? "?".$query : ""), 'progression' => $progression ? 1 : null, 'quick_edit' => $quick_edit ? 1 : null)); ?>


	<div class="spinner" id="grid-loader" style="display:none">
	  <div class="rect1"></div>
	  <div class="rect2"></div>
	  <div class="rect3"></div>
	</div>

<div class="manage_buttons">
	<div class="row">
		<div class="<?php echo isset($redirect) ? 'col-xs-9 col-sm-10 col-md-10 col-lg-10': 'col-xs-12 col-sm-12 col-md-12' ?> margin-top-10">
			<div class="modal-item-info padding-left-10">
				<div class="modal-item-details margin-bottom-10">
					<?php if(!$item_info->item_id) { ?>
			    <span class="modal-item-name new"><?php echo lang('items_new'); ?></span>
					<?php } else { ?>
		    	<span class="modal-item-name"><?php echo H($item_info->name).' ['.lang('common_id').': '.$item_info->item_id.']'; ?></span>
					<span class="modal-item-category"><?php echo H($category); ?></span>
					<?php } ?>
				</div>
			</div>	
		</div>
		<?php if(isset($redirect) && !$progression) { ?>
		<div class="col-xs-3 col-sm-2 col-md-2 col-lg-2 margin-top-10">
			<div class="buttons-list">
				<div class="pull-right-btn">
				<?php echo 
					anchor(site_url($redirect), ' ' . lang('common_done'), array('class'=>'outbound_link btn btn-primary btn-lg ion-android-exit', 'title'=>''));
				?>
				</div>
			</div>
		</div>
		<?php } ?>
	</div>
</div>

<?php if(!$quick_edit) { ?>
<?php $this->load->view('partial/nav', array('progression' => $progression, 'query' => $query, 'item_info' => $item_info)); ?>
<?php } ?>

<?php echo form_open('items/save_variations/'.(!isset($is_clone) ? $item_info->item_id : ''),array('id'=>'item_form','class'=>'form-horizontal')); ?>


<div class="col-md-12">

			<div class="panel panel-piluku">
				<div class="panel-heading">
		      <h3 class="panel-title"><i class="ion-ios-toggle-outline"></i> <?php echo lang("common_quantity_units"); ?> <small>(<?php echo lang('common_fields_required_message'); ?>)</small></h3>
					
				</div>	
				<div class="panel-body">
					
					<div class="form-group no-padding-right">	
					<?php echo form_label(lang('common_quantity_units').':', '',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label ')); ?>
						<div class="col-md-9 col-sm-9 col-lg-10">
							<div class="table-responsive">
								<table id="price_quantity_units" class="table">
									<thead>
										<tr>
										<th><?php echo lang('common_name'); ?></th>
										<th><?php echo lang('common_quantity'); ?></th>
										<th><?php echo lang('common_cost_price'); ?></th>
										<th><?php echo lang('common_unit_price'); ?></th>
										<th><?php echo lang('common_item_number'); ?></th>
										<th><?php echo lang('common_delete'); ?></th>
										</tr>
									</thead>
									
									<tbody>
																			
									<?php foreach($item_quantity_units as $iqu) { ?>
										<tr>
											<td><input type="text" data-index="<?php echo $iqu->id ?>" class="quantity_units_to_edit form-control" name="quantity_units_to_edit[<?php echo $iqu->id; ?>][unit_name]" value="<?php echo H($iqu->unit_name); ?>" /></td>
											<td><input type="text" data-index="<?php echo $iqu->id ?>" class="quantity_units_to_edit form-control" name="quantity_units_to_edit[<?php echo $iqu->id; ?>][unit_quantity]" value="<?php echo H(to_quantity($iqu->unit_quantity)); ?>" /></td>
											<td><input type="text" data-index="<?php echo $iqu->id ?>" class="quantity_units_to_edit form-control" name="quantity_units_to_edit[<?php echo $iqu->id; ?>][cost_price]" value="<?php echo H($iqu->cost_price !== NULL ? to_currency_no_money($iqu->cost_price) : '' ); ?>" /></td>
											<td><input type="text" data-index="<?php echo $iqu->id ?>" class="quantity_units_to_edit form-control" name="quantity_units_to_edit[<?php echo $iqu->id; ?>][unit_price]" value="<?php echo H($iqu->unit_price !== NULL ? to_currency_no_money($iqu->unit_price) : '' ); ?>" /></td>
											<td><input type="text" data-index="<?php echo $iqu->id ?>" class="quantity_units_to_edit form-control" name="quantity_units_to_edit[<?php echo $iqu->id; ?>][quantity_unit_item_number]" value="<?php echo H($iqu->quantity_unit_item_number !== NULL ? $iqu->quantity_unit_item_number : '' ); ?>" /></td>
										<td>
											<a class="delete_quantity_unit" href="javascript:void(0);" data-quantity_unit-id='<?php echo $iqu->id; ?>'><?php echo lang('common_delete'); ?></a>
											</td>
									</tr>
									<?php } ?>
									</tbody>
								</table>
								
								<a href="javascript:void(0);" id="add_quantity_unit"><?php echo lang('common_add'); ?></a>
								</div>
							</div>
						</div>
					</div>

</div>
	
<div class="row <?php echo $redirect ? 'manage-table' :''; ?>">
	<div class="col-md-12">
		<div class="panel panel-piluku">
			<div class="panel-heading">
	      <h3 class="panel-title"><i class="ion-ios-toggle-outline"></i> <?php echo lang("items_variations"); ?> <small>(<?php echo lang('common_fields_required_message'); ?>)</small></h3>
				
				<div class="panel-options custom pagination pagination-top hidden-print text-center" id="pagination_top">
					<?php
					if (isset($prev_item_id) && $prev_item_id)
					{
							echo anchor('items/variations/'.$prev_item_id, '<span class="hidden-xs ion-chevron-left"> '.lang('items_prev_item').'</span>');
					}
					if (isset($next_item_id) && $next_item_id)
					{
							echo anchor('items/variations/'.$next_item_id,'<span class="hidden-xs">'.lang('items_next_item').' <span class="ion-chevron-right"></span</span>');
					}
					?>
	  		</div>
			</div>
			<div class="panel-body">
		
			<div class="form-group">
				<label class="col-sm-3 col-md-3 col-lg-2 control-label"><?php echo lang('items_attributes').':' ?></label>
				<div class="col-sm-9 col-md-9 col-lg-10">

					<div class="input-group">
						<?php echo form_dropdown('', $attribute_select_options, '','class="form-control" id="available_attributes"');?>
						<span class="input-group-btn">
						        <button id="add_attribute" class="btn btn-primary" type="button"><?php echo lang('common_add'); ?></button>
						</span>
					</div>

					<table id="attributes" class="table">
						<thead>
							<tr>
								<th><?php echo lang('common_name'); ?></th>
								<th><?php echo lang('common_values'); ?></th>
								<th><?php echo lang('common_delete'); ?></th>
							</tr>
						</thead>

						<tbody>
							<?php if (isset($attributes) && $attributes) {?>
									<?php foreach($attributes as $id => $attribute) {
										$values = '';
									
										if(isset($attribute['attr_values']))
										{
											$values = implode('|', array_values(array_column($attribute['attr_values'], 'name')));
										}
										
									?>
									<tr>
										<td><?php echo H($attribute['name']); ?> </td>
										<td><input type="text" class="form-control form-inps attribute_values <?php echo $attribute['item_id'] ? 'custom' : '' ?>" size="50" data-attr-id="<?php echo $id; ?>" data-attr-name="<?php echo H($attribute['name']); ?>" name="attributes[<?php echo $id; ?>]" value="<?php echo H($values); ?>" /></td>
										<td><a class="delete_attribute <?php echo $attribute['item_id'] ? 'custom' : '' ?>" href="javascript:void(0);"><?php echo lang('common_delete'); ?></a></td>
									</tr>
								<?php } ?>
							<?php } ?>
						</tbody>
					</table>
					<div class="p-top-5">
						<?php echo anchor("items/manage_attributes".($manage_query ? '?'.$manage_query : ''),lang('common_manage_attributes'),array('class' => 'outbound_link','title'=>lang('common_manage_attributes')));?>
					</div>
				</div>
			</div>

			<?php if ($item_info->item_id && !isset($is_clone)) { ?>
			<div class="form-group">
				<label class="col-sm-3 col-md-3 col-lg-2 control-label"><?php echo lang('common_item_variations').':' ?></label>
				<div class="col-sm-9 col-md-9 col-lg-10">
					<table id="item_variations" class="table">
						<thead>
							<tr>
								<th><?php echo lang('common_name'); ?></th>
								<th><?php echo lang('common_attributes'); ?></th>
								<th><?php echo lang('common_item_number'); ?></th>
									<?php if ($this->config->item("ecommerce_platform")) { ?>
										<th class="text-center"><?php echo lang('items_is_ecommerce'); ?></th>
									<?php } ?>
									<th><?php echo lang('common_variation_id'); ?></th>
								<th><?php echo lang('common_delete'); ?></th>
							</tr>
						</thead>

						<tbody>
							<?php if (isset($item_variations) && $item_variations) { ?>
							<?php foreach($item_variations as $item_variation_id => $item_variation) { ?>
									<tr>
										<td><input type="text" class="form-control form-inps item_variation_name" size="20" name="item_variations[name][]" value='<?php echo H($item_variation['name']); ?>' /></td>
										<td><input type="text" class="form-control form-inps item_variation_attributes" size="50" name="item_variations[attributes][]" data-selectize-value='<?php echo H(json_encode($item_variation['attributes'])); ?>' /></td>
										<td><input type="text" class="form-control form-inps item-variation-numbers" size="10" name="item_variations[item_number][]" value="<?php echo H($item_variation['item_number']); ?>" />
											<?php if ($this->config->item("ecommerce_platform")) { ?>
												<td class="text-center">
												<?php echo form_dropdown('item_variations[is_ecommerce][]',array('1' => lang('common_yes'),'0' => lang('common_no')),$item_variation['is_ecommerce'],'class="form-control"');?>
											</td>
											<?php } ?>
											
											
										<td><?php echo $item_info->item_id.'#'.$item_variation_id ?></td>
												<input type="hidden" class="item_variation_id" name="item_variations[item_variation_id][]" value="<?php echo H($item_variation_id); ?>" /></td>
										<td><a class="delete_item_variation" href="javascript:void(0);"><?php echo lang('common_delete'); ?></a></td>
									</tr>
								<?php } ?>
							<?php } ?>
						</tbody>
					</table>

					<a href="javascript:void(0);" id="add_item_variation"><?php echo lang('items_add_item_variation'); ?></a><br /><br /><br /><br /><br /><br />
					<a href="<?php echo site_url('items/auto_create_variations/'.$item_info->item_id);?>" id="auto_create_all_cariations" class="btn btn-success"><?php echo lang('items_auto_create_variations'); ?></a>
				</div>
			</div>
			<?php } //end item variations ?>
		
			</div><!-- /panel-body-->
		</div><!--/panel-piluku-->
		
	</div><!-- end col -->
	
	<div class="col-md-12">

				<div class="panel panel-piluku">
					<div class="panel-heading">
			      <h3 class="panel-title"><i class="ion-android-list"></i> <?php echo lang("common_modifiers"); ?></h3>
					
					</div>	
					<div class="panel-body">
					
						<div class="form-group no-padding-right">	
							
								<?php
								foreach($this->Item_modifier->get_all()->result_array() as $modifier)
								{
								?>
								<?php echo form_label($modifier['name'].':', '',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label wide')); ?>
								<div class="col-sm-9 col-md-9 col-lg-10">
								<?php echo form_checkbox(array(
									'name'=>'modifiers[]',
									'id'=>'modifier_'.$modifier['id'],
									'class' => 'modifier',
									'value'=>$modifier['id'],
									'checked'=>(boolean)(($this->Item_modifier->item_has_modifier($item_info->item_id,$modifier['id'])))));
								?>
								<label for="modifier_<?php echo $modifier['id']; ?>"><span></span></label>
							</div>
							
							<?php } ?>
						</div>
							</div>
						</div>

	</div>
</div><!-- /row -->

<?php echo form_hidden('redirect', isset($redirect) ? $redirect : ''); ?>
<?php echo form_hidden('progression', isset($redirect) ? $progression : ''); ?>
<?php echo form_hidden('quick_edit', isset($quick_edit) ? $quick_edit : ''); ?>


<div class="form-actions">
	<?php
		echo form_submit(array(
			'name'=>'submitf',
			'id'=>'submitf',
			'value'=>lang('common_save'),
			'class'=>'submit_button floating-button btn btn-lg btn-primary')
		);
	?>
</div>
			
<script type='text/javascript'>
	<?php $this->load->view("partial/common_js"); ?>
	
	function init_attribute_values_for_item_variations($selector)
	{
			$selector.each(function() {
			
				$selectizeInstance = $(this).selectize({
					delimiter: '|',
					loadThrottle : 215,
					persist: false,
					valueField: 'value',
					labelField: 'label',
					searchField: 'label',
					preload: true,
					create: false,
					onInitialize: function() {
							var data = this.$input.attr('data-selectize-value');
						
							if(!data)
							{
								return;
							}
						
					    var existingOptions = JSON.parse(data);
					    var self = this;
					    if(Object.prototype.toString.call( existingOptions ) === "[object Array]") {
					        existingOptions.forEach( function (existingOption) {
					            self.addOption(existingOption);
					            self.addItem(existingOption[self.settings.valueField]);
					        });
					    }
					    else if (typeof existingOptions === 'object') {
					        self.addOption(existingOptions);
					        self.addItem(existingOptions[self.settings.valueField]);
					    }
					},
					onItemRemove: function(value_removed, $item)
					{
						var attribute_value_label = $item.html();
	
						this.addOption({label: attribute_value_label, value: value_removed});
						this.refreshOptions();
					},
					onItemAdd: function(value_added, $item)
	        {                                       
	        	var attribute_value_label = $item.html();
	          var attribute = attribute_value_label.split(": ")[0];
						
	          var that = this;
												
	          $.each( this.options, function( key, value ) 
	          {       
              if(value.label.split(": ")[0] == attribute && value.value != value_added)
              {
								// remove from selected
                that.removeItem(value.value);
                that.refreshItems();
              }
	          });
	        },
					load: function(query, callback) {
						if (!query.length) return callback();
						$.ajax({
							url:'<?php echo site_url("items/attribute_values_for_item_variations/").$item_info->item_id;?>' +'?term='+encodeURIComponent(query),
							type: 'GET',
							error: function() {
								callback();
							},
							success: function(res) {
								res = $.parseJSON(res);
								callback(res);
							}
						});
					}
				
				});			
			});
	}
		
	init_attribute_values_for_item_variations($('.item_variation_attributes'));

	$(document).on('click', '.delete_item_variation', function()
	{
		var $tr = $(this).closest('tr');
		var item_variation_id = $(this).closest('tr').find('.item_variation_id').val();
	
		if(item_variation_id > 0)
		{
			$("#item_form").append('<input type="hidden" name="item_variations_to_delete[]" value="'+ item_variation_id +'" />');
		}
	
		$tr.remove();
	});


	$("#add_item_variation").click(function()
	{
		$.ajax({
			url:'<?php echo site_url("items/attribute_values_for_item_variations/").$item_info->item_id;?>',
			type: 'GET',
			success: function(res) {
				res = $.parseJSON(res);
				if(res.length >= 1)
				{
					$tr = $('<tr>');
					$tr.append($('<td><input type="text" class="form-control form-inps item_variation_name" size="20" name="item_variations[name][]" value="" /></td>'));
		
					$td = $('<td>');
					$input = $('<input type="text" class="form-control form-inps item_variation_attributes" size="50" name="item_variations[attributes][]" value="" data-selectize-value="" />')
					$td.append($input).appendTo($tr);
		

					$tr.append($(
						'<td><input type="text" class="form-control form-inps" size="1" name="item_variations[item_number][]" value="" />'+
						'<input type="hidden" class="item_variation_id" name="item_variations[item_variation_id][]" value="" />'+
						'</td>'
					));
					
					
					
					<?php if ($this->config->item("ecommerce_platform")) { ?>
					$tr.append($(
						'<td class="text-center"><select class="form-control" name="item_variations[is_ecommerce][]" value="1"><option value="1"><?php echo lang('common_yes');?></option><option value="0"><?php echo lang('common_no');?></option></select>'+
						'</td>'
					));
					<?php } ?>
					$tr.append($(
						'<td><?php echo lang('common_none'); ?></td>'
					));
					
					$tr.append($(
						'<td><a class="delete_item_variation" href="javascript:void(0);"><?php echo lang('common_delete'); ?></a></td>'
					));
	
					$("#item_variations tbody").append($tr);
	
					init_attribute_values_for_item_variations($input);
				}
				else
				{
					bootbox.alert(<?php echo json_encode(lang('items_must_add_item_attributes_first')); ?>);
				}
			}
		});
	});

	function init_attribute_values($selector, prepopulate)
	{
			prepopulate = typeof prepopulate !== 'undefined' ? prepopulate : false;
			$selector.each(function() {
				var attr_id = $(this).data('attr-id');
				var	custom = $(this).hasClass('custom');
				
				$(this).selectize({
					delimiter: '|',
					loadThrottle : 215,
					persist: false,
					valueField: 'label',
					labelField: 'label',
					searchField: 'label',
					onItemAdd: function(value_added, $item) {
						$.ajax({
							url:'<?php echo site_url("items/add_attribute_value_to_item/").$item_info->item_id ;?>',
							type: 'POST',
							data: {attr_id:attr_id,value_added:value_added}
						});
					},
					onItemRemove: function(value_removed, $item) {
						var attribute_value_label = $item.text();
						if(!custom)
						{
							this.addOption({label: attribute_value_label, value: value_removed});
							this.refreshOptions();
						}
												
						$.ajax({
							url:'<?php echo site_url("items/remove_attribute_value_for_item/").$item_info->item_id ;?>',
							type: 'POST',
							data: {attr_id:attr_id,value_removed:value_removed},
							success: function(attr_value_id) {
								if(attr_value_id){
				       		$('.item_variation_attributes.selectized').each(function() {
										var childSelectize = $(this)[0].selectize;
										childSelectize.removeItem(attr_value_id);
										childSelectize.removeOption(attr_value_id);
										childSelectize.refreshItems();
    								childSelectize.refreshOptions();
									});
								}
							}
						});
					},
					create: custom,
					createFilter: function(input) {
						for (cur_option in this.options) 
						{
							var option_label = this.options[cur_option].label.toLowerCase();
							if (input.toLowerCase() == option_label)
							{
								return false;
							}
						}
						return true;
					},
					render: {
					    option_create: function(data, escape) {
							var add_new = <?php echo json_encode(lang('common_add_new_attribute_value')) ?>;
					      return '<div class="create">'+escape(add_new)+' <strong>' + escape(data.input) + '</strong></div>';
					    }
					},
					load: function(query, callback) {
						if (!query.length) return callback();
						$.ajax({
							url:'<?php echo site_url("items/attribute_values/");?>'+attr_id+'?term='+encodeURIComponent(query),
							type: 'GET',
							error: function() {
								callback();
							},
							success: function(res) {
								res = $.parseJSON(res);
								callback(res);
							}
						});
					}
				});
				
				var selectize = $(this)[0].selectize;
				
				if(prepopulate)
				{					
					$.get('<?php echo site_url("$controller_name/get_values_for_attribute");?>'+'/'+ attr_id, {}, function(response)
					{
						selectize.addOption(response);		
						
						for(var k=0;k<response.length;k++)
						{
							selectize.addItem(response[k]['label']);
						}
					}, 'json');
				}
				
			});
	}
	
	init_attribute_values($('.attribute_values'));
	
	
	$('#add_attribute').on('click', function() {
		var selected_value = $('#available_attributes').val();
		
		if(selected_value == 0)
		{	
			bootbox.prompt({
			  title: <?php echo json_encode(lang('items_please_enter_custom_attribute_name')); ?>,
			  value: $(this).data('name'),
			  callback: function(attribute_name) {
			  	if (attribute_name)
			  	{
						//ajax to make new custom attribute
			  		$.post('<?php echo site_url("items/add_custom_attribute_to_item/").$item_info->item_id;?>', 
						{ name : attribute_name }, function(response) {	
			  			show_feedback(response.success ? 'success' : 'error', response.message,response.success ? <?php echo json_encode(lang('common_success')); ?> : <?php echo json_encode(lang('common_error')); ?>);
			  			if (response.success)
			  			{
								$custom_attribute_values_input = $('<input type="text" value="" class="form-control form-inps attribute_values custom" size="40" name="attributes['+ response.attribute_id +']">');
								$custom_attribute_values_input.data('attr-id', response.attribute_id);
								$custom_attribute_values_input.data('attr-name', attribute_name);
								
								$tr = $('<tr>');
								$tr.append($('<td>').html(attribute_name));
								$tr.append($('<td>').append($custom_attribute_values_input));
			
								$tr.append($('<td><a class="delete_attribute custom" href="javascript:void(0);"><?php echo lang('common_delete'); ?></a></td>'));
			
								$("#attributes").append($tr);
			
								init_attribute_values($custom_attribute_values_input, false);
								
			  			}
			  		}, "json");
			  	}
			  }
			});
		}
		if(selected_value > 0)
		{
			$selected_option = $('#available_attributes').find("option:selected");
			var attr_name = $selected_option.text();
			
  		$.post('<?php echo site_url("items/add_attribute_to_item/").$item_info->item_id;?>', 
			{ attr_id : selected_value }, function(response) {	
  			show_feedback(response.success ? 'success' : 'error', response.message,response.success ? <?php echo json_encode(lang('common_success')); ?> : <?php echo json_encode(lang('common_error')); ?>);
  			if (response.success)
  			{
					$selected_option.remove();
		
					$attribute_values_input = $('<input type="text" value="" class="form-control form-inps attribute_values" size="40" name="attributes['+selected_value+']">');
					$attribute_values_input.data('attr-id', selected_value);
					$attribute_values_input.data('attr-name', attr_name);
			
					$tr = $('<tr>');
					$tr.append($('<td>').text(attr_name));
					$tr.append($('<td>').append($attribute_values_input));
						
					$tr.append($('<td><a class="delete_attribute" href="javascript:void(0);"><?php echo lang('common_delete'); ?></a></td>'));
				
					$("#attributes").append($tr);
		
					init_attribute_values($attribute_values_input, true);
  			}
  		}, "json");			
		}
	});

	$(document).on('click', '.delete_attribute', function() {
		var custom = $(this).hasClass('custom');
		var $tr = $(this).closest('tr');
		var $input = $tr.find('.attribute_values.selectized');
		
		var selectize = $input[0].selectize;
		var to_remove = [];
		
		$.each(selectize.items, function(index, value)
		{
			to_remove.push(value);
		});
		
		bootbox.confirm(<?php echo json_encode(lang('items_confirm_remove_item_attribute')); ?>, function(response)
		{
			if (response)
			{
				for(var k=0;k<to_remove.length;k++)
				{					
					selectize.removeItem(to_remove[k]);
					selectize.refreshItems();
				}
				
				var attr_id = $input.data('attr-id');
				var attr_name = $input.data('attr-name');
	
				$.ajax({
					url:'<?php echo site_url("items/remove_attribute_for_item/").$item_info->item_id;?>',
					type: 'POST',
					data: {attr_id:attr_id},
					success: function(res) {
						$tr.remove();
						if(!custom)
						{
							var $option = $('<option>').val(attr_id).text(attr_name);
							$("#available_attributes").append($option);
						}
				}});				
			}
		});	
	});
	
	$('#item_form').validate({
			submitHandler:function(form)
			{			
				var args = {
					next: {
						label: <?php echo json_encode(lang('common_edit').' '.lang('common_pricing')) ?>,
						url: <?php echo json_encode(site_url("items/pricing/".($item_info->item_id ? $item_info->item_id : -1)."?$query")); ?>
					}
				};
		
				doItemSubmit(form, args);
			}
	});
	
	$("#auto_create_all_cariations").click(function(e)
	{
		e.preventDefault();
		bootbox.confirm(<?php echo json_encode(lang('items_confirm_auto_variations')); ?>, function(response)
		{
			if (response)
			{
				$('#item_form').ajaxSubmit({
				success:function(response)
				{
					window.location = $("#auto_create_all_cariations").attr('href');
				}});
			}
		});
	});
	
	$('.item-variation-numbers').selectize({
		delimiter: '|',
		create: true,
		render: {
	      option_create: function(data, escape) {
				var add_new = <?php echo json_encode(lang('common_add_value')) ?>;
	        return '<div class="create">'+escape(add_new)+' <strong>' + escape(data.input) + '</strong></div>';
	      }
		},
	})
	
	$(".delete_quantity_unit").click(function()
	{
		$("#item_form").append('<input type="hidden" name="quantity_units_to_delete[]" value="'+$(this).data('quantity_unit-id')+'" />');
		$(this).parent().parent().remove();
	});
	
	
	var add_index = -1;
	
	$("#add_quantity_unit").click(function()
	{		
		$("#price_quantity_units tbody").append('<tr><td><input type="text" class="quantity_units_to_edit form-control" data-index="'+add_index+'" name="quantity_units_to_edit['+add_index+'][unit_name]" value="" /></td><td><input type="text" class="quantity_units_to_edit form-control" data-index="'+add_index+'" name="quantity_units_to_edit['+add_index+'][unit_quantity]" value=""/></td><td><input type="text" class="quantity_units_to_edit form-control" data-index="'+add_index+'" name="quantity_units_to_edit['+add_index+'][cost_price]" value=""/></td><td><input type="text" class="quantity_units_to_edit form-control" data-index="'+add_index+'" name="quantity_units_to_edit['+add_index+'][unit_price]" value=""/></td><td><input type="text" class="quantity_units_to_edit form-control quantity-unit-add-number" data-index="'+add_index+'" name="quantity_units_to_edit['+add_index+'][quantity_unit_item_number]" value=""/></td><td>&nbsp;</td></tr>');
		add_index--;
	});
	
	$(document).on('change', ".quantity-unit-add-number",function(e)
	{
		var $that = $(this);;
		$.post(<?php echo json_encode(site_url('items/does_quantity_unit_exist')); ?>,{number: $(this).val()},function(json)
		{
			if (json.exists)
			{
				bootbox.alert(<?php echo json_encode(lang('items_item_quantity_unit_number_exists')); ?>);
				$that.val('');
			}
		},'json');
	});
	
</script>
<?php  echo form_close(); ?>
</div>
<?php $this->load->view('partial/footer'); ?>
