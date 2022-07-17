<?php $this->load->view("partial/header"); ?>


<div class="modal fade tag-input-data" id="tag-input-data" tabindex="-1" role="dialog" aria-labelledby="tagData" aria-hidden="true">
    <div class="modal-dialog customer-recent-sales">
      	<div class="modal-content">
	        <div class="modal-header">
	          	<button type="button" class="close" data-dismiss="modal" aria-label=<?php echo json_encode(lang('common_close')); ?>><span aria-hidden="true">&times;</span></button>
	          	<h4 class="modal-title" id="tagModalDialogTitle">&nbsp;</h4>
	        </div>
	        <div class="modal-body">
				<!-- Form -->
				<?php echo form_open_multipart('deliveries/save_category/', array('id'=>'categories_form','class'=>'form-horizontal')); ?>
							
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
							'value'=>'',
							'autocomplete' => "off")
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



<?php if(isset($redirect)) { ?>
<div class="manage_buttons">
	<div class="row">
		<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 margin-top-10">
			<div class="buttons-list">
				<div class="pull-right-btn">
				<?php echo 
					anchor(site_url($redirect), ' ' . lang('common_done'), array('class'=>'btn btn-primary btn-lg ion-android-exit', 'title'=>''));
				?>
				</div>
			</div>
		</div>
	</div>
</div>
<?php } ?>

<?php echo form_open('deliveries/save_category/',array('id'=>'tag_form','class'=>'form-horizontal')); ?>
<div class="row <?php echo $redirect ? 'manage-table' :''; ?>">
	<div class="col-md-12">
		<div class="panel panel-piluku">
			<div class="panel-heading"><?php echo lang("items_manage_categories"); ?></div>
			<div class="panel-body">
				<a href="javascript:void(0);" class="add_category" data-delivery_id="0">[<?php echo lang('common_add_category'); ?>]</a>
					<div id="category_list" class="tag-tree">
						<?php echo $category_list; ?>
					</div>
				<a href="javascript:void(0);" class="add_category" data-delivery_id="0">[<?php echo lang('common_add_category'); ?>]</a>
			</div>
		</div>
	</div>
</div><!-- /row -->

<?php  echo form_close(); ?>
</div>

			
<script type='text/javascript'>

	$(function() {
		$('#category_color').colorpicker();
	});

	$("#categories_form").submit(function(event)
	{
		event.preventDefault();

		$(this).ajaxSubmit({ 
			success: function(response, statusText, xhr, $form){
				show_feedback(response.success ? 'success' : 'error', response.message, response.success ? <?php echo json_encode(lang('common_success')); ?> : <?php echo json_encode(lang('common_error')); ?>);
				if(response.success)
				{
					$("#tag-input-data").modal('hide');
					$('#category_list').load("<?php echo site_url("deliveries/category_list"); ?>");
					
				}		
			},
			dataType:'json',
		});
	});

	$(document).on('click', ".edit_category",function()
	{
		$("#tagModalDialogTitle").html(<?php echo json_encode(lang('common_edit')); ?>);
		
		var category_id = $(this).data('category_id');
		$("#categories_form").attr('action', SITE_URL+'/deliveries/save_category/'+category_id);
		
		$("#categories_form").find('#category_name').val($(this).data('name'));

		$("#categories_form").find('#category_color').val($(this).data('color'));
		$('#category_color').colorpicker('setValue', $(this).data('color'));

		$("#tag-input-data").modal('show');
	});

	$(document).on('click', ".add_category",function()
	{
		$("#tagModalDialogTitle").html(<?php echo json_encode(lang('common_add_category')); ?>);

		$("#categories_form").attr('action', SITE_URL+'/deliveries/save_category/');
		
		$("#categories_form").find('#category_name').val('');

		$("#categories_form").find('#category_color').val('');
		$('#category_color').colorpicker('setValue', '');

		$("#tag-input-data").modal('show');
	});

	$(document).on('click', ".delete_category",function()
	{
		var category_id = $(this).data('category_id');
		if (category_id)
		{
			bootbox.confirm(<?php echo json_encode(lang('items_category_delete_confirmation')); ?>, function(result)
			{
				if (result)
				{
					$.post('<?php echo site_url("deliveries/delete_category");?>', {category_id : category_id},function(response) {
					
						show_feedback(response.success ? 'success' : 'error', response.message,response.success ? <?php echo json_encode(lang('common_success')); ?> : <?php echo json_encode(lang('common_error')); ?>);

						//Refresh tree if success
						if (response.success)
						{
							$('#category_list').load("<?php echo site_url("deliveries/category_list"); ?>");
						}
					}, "json");
				}
			});
		}
		
	});

</script>
<?php $this->load->view('partial/footer'); ?>
