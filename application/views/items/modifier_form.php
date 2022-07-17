<?php $this->load->view("partial/header"); ?>

<div class="row" id="form">
	
	<div class="spinner" id="grid-loader" style="display:none">
	  <div class="rect1"></div>
	  <div class="rect2"></div>
	  <div class="rect3"></div>
	</div>
	<div class="col-md-12">
		 <?php echo form_open('items/save_modifier/'.$modifier_info->id,array('id'=>'modifier_form','class'=>'form-horizontal')); ?>
		<div class="panel panel-piluku">
			<div class="panel-heading">
                    <h3 class="panel-title">
                        <i class="ion-edit"></i> <?php if(!$modifier_info->id) { echo lang('items_new_modifier'); } else { echo lang('items_update_modifier'); } ?>
								<small>(<?php echo lang('common_fields_required_message'); ?>)</small>
	                </h3>
						 
            </div>
			<div class="panel-body">
				
				<div class="form-group">
				<?php echo form_label(lang('common_name').':', 'name_input', array('class'=>'required col-sm-3 col-md-3 col-lg-2 control-label')); ?>
				<div class="col-sm-9 col-md-9 col-lg-10 cmp-inps">
					<?php echo form_input(array(
						'class'=>'form-control form-inps',
						'name'=>'name',
						'id'=>'name_input',
						'value'=>$modifier_info->name)
					);?>
					</div>
				</div>
				
				
				<div class="form-group no-padding-right">	
				<?php echo form_label(lang('common_items').':', '',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label ')); ?>
					<div class="col-md-9 col-sm-9 col-lg-10">
						<div class="table-responsive">
							<table id="price_modifier_items" class="table">
								<thead>
									<tr>
									<th><?php echo lang('common_name'); ?></th>
									<th><?php echo lang('common_cost_price'); ?></th>
									<th><?php echo lang('common_unit_price'); ?></th>
									<th><?php echo lang('common_delete'); ?></th>
									</tr>
								</thead>
								
								<tbody>
				
				<?php
				foreach($modifier_items as $item)
				{
				?>																		
						<tr>
							<td><input type="text" data-index="<?php echo $item['id'] ?>" class="modifier_items form-control" name="modifier_items[<?php echo $item['id']; ?>][name]" value="<?php echo H($item['name']); ?>" /></td>
							<td><input type="text" data-index="<?php echo $item['id'] ?>" class="modifier_items form-control" name="modifier_items[<?php echo $item['id']; ?>][cost_price]" value="<?php echo H($item['cost_price'] !== NULL ? to_currency_no_money($item['cost_price']) : '' ); ?>" /></td>
							<td><input type="text" data-index="<?php echo $item['id'] ?>" class="modifier_items form-control" name="modifier_items[<?php echo $item['id']; ?>][unit_price]" value="<?php echo H($item['unit_price'] !== NULL ? to_currency_no_money($item['unit_price']) : '' ); ?>" /></td>
						<td>
							<a class="delete_modifier_item" href="javascript:void(0);" data-modifier_item-id='<?php echo $item['id']; ?>'><?php echo lang('common_delete'); ?></a>
							</td>
					</tr>
				<?php
				}
				?>
				
							</tbody>
						</table>
						
						<a href="javascript:void(0);" id="add_modifier_item"><?php echo lang('common_add'); ?></a>
						</div>
					</div>
				</div>
				
				


<div class="form-actions pull-right">
<?php
echo form_submit(array(
	'name'=>'submitf',
	'id'=>'submitf',
	'value'=>lang('common_save'),
	'class'=>'btn btn-primary btn-lg submit_button floating-button btn-large')
	);
	?>

	
			</div>
		</div>
	</div>
	<?php echo form_close(); ?>
</div>
</div>
</div>

<script type='text/javascript'>
	
	$(".delete_modifier_item").click(function()
	{
		$("#modifier_form").append('<input type="hidden" name="modifier_items_to_delete[]" value="'+$(this).data('modifier_item-id')+'" />');
		$(this).parent().parent().remove();
	});
	
	
	var add_index = -1;
	
	$("#add_modifier_item").click(function()
	{		
		$("#price_modifier_items tbody").append('<tr><td><input type="text" class="modifier_items form-control" data-index="'+add_index+'" name="modifier_items['+add_index+'][name]" value="" /></td><td><input type="text" class="modifier_items form-control" data-index="'+add_index+'" name="modifier_items['+add_index+'][cost_price]" value=""/></td><td><input type="text" class="modifier_items form-control" data-index="'+add_index+'" name="modifier_items['+add_index+'][unit_price]" value=""/></td><td>&nbsp;</td></tr>');
		add_index--;
	});
	
</script>
<?php $this->load->view('partial/footer')?>
