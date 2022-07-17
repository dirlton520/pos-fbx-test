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
</script>
