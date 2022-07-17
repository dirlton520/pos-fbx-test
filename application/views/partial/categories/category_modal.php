<div class="modal fade category-input-data" id="category-input-data" tabindex="-1" role="dialog" aria-labelledby="categoryData" aria-hidden="true">
    <div class="modal-dialog customer-recent-sales">
      	<div class="modal-content">
	        <div class="modal-header">
	          	<button type="button" class="close" data-dismiss="modal" aria-label=<?php echo json_encode(lang('common_close')); ?>><span aria-hidden="true">&times;</span></button>
	          	<h4 class="modal-title" id="categoryModalDialogTitle">&nbsp;</h4>
	        </div>
	        <div class="modal-body">
				<!-- Form -->
				<?php echo form_open_multipart('items/save_category/',array('id'=>'categories_form','class'=>'form-horizontal')); ?>
				
				<div class="form-group">
					<?php echo form_label(lang('common_parent_category').':', 'parent_id',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label wide')); ?>
					<div class="col-sm-9 col-md-9 col-lg-9">
						<?php echo form_dropdown('parent_id', $categories, '0', 'class="form-control form-inps" id="parent_id"');?>
					</div>
				</div>
			
				<div class="form-group">
					<?php echo form_label(lang('common_category_name').':', 'category_name',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label wide')); ?>
					<div class="col-sm-9 col-md-9 col-lg-9">
						<?php echo form_input(array(
							'type'  => 'text',
							'name'  => 'category_name',
							'id'    => 'category_name',
							'value' => '',
							'class'=> 'form-control form-inps',
						)); ?>
					</div>
				</div>
												
				<div class="form-group">
					<?php echo form_label(lang('common_category_color').':', 'category_color',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label')); ?>
					<div class="col-sm-9 col-md-9 col-lg-9">
						<?php echo form_input(array(
							'class'=>'form-control form-inps',
							'name'=>'category_color',
							'id'=>'category_color',
							'value'=>'')
						);?>
					</div>
				</div>
				
				<div class="form-group">
					<?php echo form_label(lang('common_category_image').':', 'category_image',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label wide')); ?>
					<div class="col-sm-9 col-md-9 col-lg-9">
						<div class="image-upload">
							<?php echo form_input(array(
								'type'  => 'file',
								'name'  => 'category_image',
								'id'    => 'category_image',
								'class' => 'filestyle form-control form-inps',
								'data-icon' => 'false'
							)); ?>
						</div>
					</div>
				</div>
				
				<?php if ($this->config->item("ecommerce_platform")) { ?>
					<div class="form-group">
					<?php echo form_label(lang('items_exclude_from_e_commerce').':', 'category_image_delete',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label wide')); ?>
					<div class="col-sm-9 col-md-9 col-lg-9">
						<?php echo form_checkbox(array(
							'name'=>'exclude_from_e_commerce',
							'id'=>'exclude_from_e_commerce',
							'class'=>'delete-checkbox',
							'value'=>1,
							'checked' => FALSE,
						));?>
						<label for="exclude_from_e_commerce"><span></span></label>
					</div>
				</div>
				
				<?php } ?>
				
				<div id="preview-section" class="form-group" style="display:none;">
					<?php echo form_label(lang('image_preview').':', 'category_image_preview',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label wide')); ?>
					<div class="col-sm-9 col-md-9 col-lg-9">
						<img id="image-preview" src="#" alt="preview" style="max-width: 100%;">
					</div>
					
					<?php echo form_label(lang('common_del_image').':', 'category_image_delete',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label wide')); ?>
					<div class="col-sm-9 col-md-9 col-lg-9">
						<?php echo form_checkbox(array(
							'name'=>'del_image',
							'id'=>'del_image',
							'class'=>'delete-checkbox',
							'value'=>1
						));?>
						<label for="del_image"><span></span></label>
					</div>
				</div>
				
				
				
				<?php
				if ($this->Location->count_all() > 1)
				{
				foreach($this->Location->get_all()->result() as $location) { 
					
					echo form_hidden('locations['.$location->location_id.'][dummy_value_prevent_notice_and_get_loop_to_run]','1');
				?>
				<div class="form-group">
					<?php echo form_label($location->name.' '.lang('common_hide_from_grid').':', '', array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label wide')); ?>
					<div class="col-sm-9 col-md-9 col-lg-10">
						<?php echo form_checkbox(array(
							'name'=>'locations['.$location->location_id.'][hide_from_grid]',
							'id'=>'locations_'.$location->location_id.'_hide_from_grid',
							'class' => 'hide_from_grid_checkbox delete-checkbox',
							'value'=>1,));
						?>
						<label for="<?php echo 'locations_'.$location->location_id.'_hide_from_grid' ?>"><span></span></label>
					</div>
				</div>
				
				<?php } ?>
				<?php } ?>
				
				
				
				<div class="form-group">
					<?php echo form_label(lang('common_info_popup').':', 'info_popup',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label wide')); ?>
					<div class="col-sm-9 col-md-9 col-lg-10">
						<?php echo form_textarea(array(
							'name'=>'category_info_popup',
							'id'=>'category_info_popup',
							'value'=>'',
							'class'=>'form-control  text-area',
							'rows'=>'5',
							'cols'=>'17')
						);?>
					</div>
				</div>
				
				
				
				<div class="form-actions">
					<?php
						echo form_submit(array(
							'name'=>'submitf',
							'id'=>'submitf',
							'value'=>lang('common_save'),
							'class'=>'submit_button pull-right btn btn-primary')
						);
					?>
					<div class="clearfix">&nbsp;</div>
				</div>
			
				<?php echo form_close(); ?>
	        </div>
    	</div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
<script>
	$('#parent_id').selectize({
		create: false,
		render: {
	    item: function(item, escape) {
				var item = '<div class="item">'+ escape($('<div>').html(item.text).text()) +'</div>';
				return item;
	    },
	    option: function(item, escape) {
				var option = '<div class="option">'+ escape($('<div>').html(item.text).text()) +'</div>';
				return option;
	    }
		}
	});
	
	
	$(function() {
		$('#category_color').colorpicker();
	});
	
	$('#category_image').change(function (e) {
		$("#categories_form").find('#image-preview').attr('src', URL.createObjectURL(e.target.files[0]));
	    $('#preview-section').show();
	});
	
</script>
