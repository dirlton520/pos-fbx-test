<?php $this->load->view("partial/header"); ?>

<?php $query = http_build_query(array('redirect' => $redirect, 'progression' => $progression ? 1 : null, 'quick_edit' => $quick_edit ? 1 : null)); ?>

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
					<?php if(!$item_kit_info->item_kit_id) { ?>
			    <span class="modal-item-name new"><?php echo lang('item_kits_new'); ?></span>
					<?php } else { ?>
		    	<span class="modal-item-name"><?php echo H($item_kit_info->name); ?></span>
					<span class="modal-item-category"><?php echo H($category); ?></span>
					<?php } ?>
				</div>
			</div>	
		</div>
		<?php if(isset($redirect)) { ?>
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
<?php $this->load->view('partial/nav', array('progression' => $progression, 'query' => $query, 'item_kit_info' => $item_kit_info)); ?>
<?php } ?>

<?php echo form_open('item_kits/save_items/'.(!isset($is_clone) ? $item_kit_info->item_kit_id : ''),array('id'=>'item_kit_form','class'=>'form-horizontal')); ?>
<div class="row <?php echo $redirect ? 'manage-table' :''; ?>">
	<div class="col-md-12">
		<div class="panel panel-piluku">
			<div class="panel-heading">
        <h3 class="panel-title"><i class="icon ti-harddrive"></i> <?php echo lang('item_kits_items_added');?> <small>(<?php echo lang('common_fields_required_message'); ?>)</small></h3>
				
				<div class="panel-options custom pagination pagination-top hidden-print text-center" id="pagination_top">
					<?php
					if (isset($prev_item_kit_id) && $prev_item_kit_id)
					{
							echo anchor('item_kits/items/'.$prev_item_kit_id, '<span class="hidden-xs ion-chevron-left"> '.lang('item_kits_prev_item_kit').'</span>');
					}
					if (isset($next_item_kit_id) && $next_item_kit_id)
					{
							echo anchor('item_kits/items/'.$next_item_kit_id,'<span class="hidden-xs">'.lang('item_kits_next_item_kit').' <span class="ion-chevron-right"></span</span>');
					}
					?>
	  		</div>
				
			</div>

			<div class="panel-body">
				<div class="col-sm-offset-3 col-md-offset-3 col-lg-offset-2 col-sm-9 col-md-9 col-lg-10">
				<span class="help-block"><?php echo lang('item_kits_desc'); ?></span>
				</div>
				<div class="form-group">
					<?php echo form_label(lang('item_kits_add_item').':', 'item',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label  ')); ?>
					<div class="col-sm-9 col-md-9 col-lg-10">
						<?php echo form_input(array(
							'class'=>'form-control form-inps',
							'name'=>'item',
							'id'=>'item'
						));?>
					</div>
					
				</div>

				<table id="item_kit_items" class="table table-bordered table-striped text-success text-center">
					<tr>
						<th><?php echo lang('common_delete');?></th>
						<th><?php echo lang('item_kits_item');?></th>
						<th><?php echo lang('item_kits_quantity');?></th>
					</tr>

					<?php foreach ($item_kit_items as $item_kit_item) {?>
						<tr class="item_kit_item_row">
							<?php
							$item_info = $this->Item->get_info($item_kit_item->item_id);
							?>
							<td><a  href="#" onclick='return deleteItemKitRow(this);'><i class='ion-ios-trash-outline fa-2x text-danger'></i></a></td>
							<td><?php echo H($item_info->name.( $item_kit_item->item_variation_id ? '- '.$this->Item_variations->get_variation_name($item_kit_item->item_variation_id) : '').($item_info->item_number ? ': '.$item_info->item_number : '')); ?></td>

							<td>
								<div class="form-group table-form-group">
									<input class='form-control quantity' id='item_kit_item_<?php echo $item_kit_item->item_id.($item_kit_item->item_variation_id ? '#'.$item_kit_item->item_variation_id : ''); ?>' type='text' name=item_kit_item[<?php echo $item_kit_item->item_id.($item_kit_item->item_variation_id ? '#'.$item_kit_item->item_variation_id : '') ?>] value='<?php echo to_quantity($item_kit_item->quantity); ?>'/>	
								</div>
							</td>
						</tr>
					<?php } ?>
					
					
					<?php foreach ($item_kit_item_kits as $item_kit_item_kit) {?>
						<tr class="item_kit_item_row">
							<?php
							$item_info = $this->Item_kit->get_info($item_kit_item_kit->item_kit_id);
							?>
							<td><a  href="#" onclick='return deleteItemKitRow(this);'><i class='ion-ios-trash-outline fa-2x text-danger'></i></a></td>
							<td><?php echo H($item_kit_item_kit->name); ?></td>

							<td>
								<div class="form-group table-form-group">
									<input class='form-control quantity' id='item_kit_item_KIT<?php echo $item_kit_item_kit->item_kit_id; ?>' type='text' name=item_kit_item[KIT<?php echo $item_kit_item_kit->item_kit_id ?>] value='<?php echo to_quantity($item_kit_item_kit->quantity); ?>'/>	
								</div>
							</td>
						</tr>
					<?php } ?>
					
					
				</table>
			</div>
		</div>
		
	</div>
</div><!-- /row -->

<?php echo form_hidden('redirect', isset($redirect) ? $redirect : ''); ?>
<?php echo form_hidden('progression', isset($progression) ? $progression : ''); ?>
<?php echo form_hidden('quick_edit', isset($quick_edit) ? $quick_edit : ''); ?>

<div class="form-actions">
	<?php
		echo form_submit(array(
			'name'=>'submitf',
			'id'=>'submitf',
			'value'=>lang('common_save'),
			'class'=>'submit_button floating-button btn btn-lg btn-primary hidden-print')
		);
	?>
</div>

<?php  echo form_close(); ?>
</div>

			
<script type='text/javascript'>
<?php $this->load->view("partial/common_js"); ?>

	setTimeout(function(){$(":input:visible:first","#item_kit_form").focus();},100);
	
	//Add payment to the sale when hit enter on amount tendered input
	$('#item').bind('keypress', function(e) {
		if(e.keyCode==13)
		{
			e.preventDefault();
		
			$.post('<?php echo site_url('item_kits/get_item_info'); ?>', {item_number: $("#item").val()}, function(response)
			{
				if(response)
				{
					addItemToKit(response.item_id,response.name);
				}
				else
				{
					show_feedback('error', <?php echo json_encode(lang('items_kit_unable_to_add_item'));?>, <?php echo json_encode(lang('common_error'));?>);
				}
			
			},'json');
		
		}
	});
	
	function addItemToKit(value,label)
	{
		$( "#item" ).val("");
		if ($('[id="item_kit_item_'+value+'"]').length ==1)
		{
			$('[id="item_kit_item_'+value+'"]').val(parseFloat($('[id="item_kit_item_'+value+'"]').val()) + 1);
		}
		else
		{
			$("#item_kit_items").append("<tr class='item_kit_item_row'><td><a  href='#' onclick='return deleteItemKitRow(this);'><i class='ion-ios-trash-outline fa-2x text-danger'></i></a></td><td>"+label+"</td><td><div class='form-group table-form-group'><input class='quantity form-control' id='item_kit_item_"+value+"' type='text' name=item_kit_item["+value+"] value='1'/></td></tr>");
		}
	}

	$( "#item" ).autocomplete({
 		source: '<?php echo site_url("item_kits/item_search");?>',
		delay: 500,
 		autoFocus: false,
 		minLength: 0,
 		select: function( event, ui ) 
 		{
			addItemToKit(decodeHtml(ui.item.value.replace('KIT ','KIT')),decodeHtml(ui.item.label+(ui.item.attributes ? '- '+ui.item.attributes : '')+(ui.item.item_number ? ': '+ui.item.item_number : '')));
			return false;

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
						'</span>' +
					'</div>')
           .appendTo(ul);
   };
	
	var submitting = false;
	
	$('#item_kit_form').validate({
		submitHandler:function(form)
		{
			var args = {
				next: {
					label: <?php echo json_encode(lang('common_edit').' '.lang('common_pricing')) ?>,
					url: <?php echo json_encode(site_url("item_kits/pricing/".($item_kit_info->item_kit_id ? $item_kit_info->item_kit_id : -1)."?$query")); ?>,
				}
			};
			
			doItemSubmit(form, args);
		},
		errorClass: "text-danger",
		errorElement: "span",
		highlight:function(element, errorClass, validClass) {
			$(element).parents('.form-group').removeClass('has-success').addClass('has-error');
		},
		unhighlight: function(element, errorClass, validClass) {
			$(element).parents('.form-group').removeClass('has-error').addClass('has-success');
		}
	});
	
	function deleteItemKitRow(link)
	{
		$(link).parent().parent().remove();
		return false;
	}
	
</script>
<?php $this->load->view('partial/footer'); ?>
