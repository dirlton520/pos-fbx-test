<?php $this->load->view("partial/header"); ?>
<?php echo form_open('receivings/create_po/',array('id'=>'item_form','class'=>'form-horizontal')); ?>

		<div class="panel">
			<div class="panel-body">
				
				<div class="form-group">	
					<?php echo form_label(lang('common_supplier').':', 'supplier_id',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label wide')); ?>
					<div class="col-sm-9 col-md-9 col-lg-10">
						<?php echo form_dropdown('supplier_id', $suppliers, $selected_supplier,'id="supplier_id"');?>
					</div>
				</div>
				
				<div class="form-group">
					<?php echo form_label(lang('common_category').':', 'category', array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label ')); ?> 
					<div class="col-sm-9 col-md-2 col-lg-2">
						<?php echo form_dropdown('category',$categories, '', 'id="category"'); ?>
					</div>
				</div>
				
				<div class="form-group">	
				<?php echo form_label(lang('receivings_criteria').':', 'criteria_id',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label wide')); ?>
					<div class="col-sm-9 col-md-9 col-lg-10">
					<?php echo form_dropdown('criteria', $criterias, '','class="form-control" id="criteria_id"');?>
					</div>
				</div>

				<div class="form-group">	
					<?php echo form_label(lang('receivings_clear_current_items_in_cart').':', 'clear_current_items_in_cart',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label ')); ?>
					<div class="col-sm-9 col-md-9 col-lg-10">
					<?php echo form_checkbox(array(
						'name'=>'clear_current_items_in_cart',
						'id'=>'clear_current_items_in_cart',
						'value'=>'clear_current_items_in_cart',
						'checked'=> FALSE));?>
						<label for="clear_current_items_in_cart"><span></span></label>
					</div>
				</div>
				
				<div class="form-group">	
					<div class="col-sm-9 col-md-9 col-lg-10 col-md-offset-2">
						<?php echo form_submit(array(
							'name'=>'submitf',
							'id'=>'submitf',
							'value'=>lang('common_save'),
							'class'=>'submit_button btn-lg btn btn-primary')
						); ?>
					</div>
				</div>

			</div>
		</div>
<?php echo form_close(); ?>
</div>
<script>
	$("#category").select2();
	$("#supplier_id").select2();
</script>

<?php $this->load->view("partial/footer"); ?>