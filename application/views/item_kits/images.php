<?php $this->load->view("partial/header"); ?>

<?php $query = http_build_query(array('redirect' => $redirect, 'progression' => $progression ? 1 : null, 'quick_edit' => $quick_edit ? 1 : null)); ?>
<?php $manage_query = http_build_query(array('redirect' => uri_string().($query ? "?".$query : ""), 'progression' => $progression ? 1 : null, 'quick_edit' => $quick_edit ? 1 : null)); ?>

<div class="spinner" id="grid-loader" style="display:none;">
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
<?php $this->load->view('partial/nav', array('progression' => $progression, 'query' => $query, 'item_kit_info' => $item_kit_info)); ?>
<?php } ?>

<div class="row <?php echo $redirect ? 'manage-table' :''; ?>" id="form">
	<div class="col-md-12">
		
	<?php echo form_open_multipart('item_kits/save_images/'.(!isset($is_clone) ? $item_kit_info->item_kit_id : ''),array('id'=>'item_form','class'=>'form-horizontal')); ?>
	
	<?php $data = array(
    'type'  => 'hidden',
    'name'  => 'has_files',
    'id'    => 'has_files',
    'value' => '0',
	);

	echo form_input($data); ?>
	
	<div class="panel panel-piluku">
		<div class="panel-heading">
	      <h3 class="panel-title"><i class="ion-images"></i> <?php echo lang("common_upload_images"); ?> <small>(<?php echo lang('common_fields_required_message'); ?>)</small></h3>
				
		</div>

			<div class="panel-body">
				
				<div class="form-group">
	      	<?php echo form_label(lang('common_select_images').':', 'image_id',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label ')); ?>
					<div class="col-sm-9 col-md-9 col-lg-10">
	        	<div class="image-upload">
	          	<input type="file" name="image_files[]" id="image_id" class="filestyle" data-icon="false" multiple accept="image/png,image/gif,image/jpeg" >
	           </div>
	        </div>
				</div>

				<div class="row">
				<div id="image_preview" class="item_image_preview"></div>
				</div>
			</div><!--/panel-body -->
		</div><!-- /panel-piluku -->
		
			<?php if($item_kit_images) { ?>
			<div class="row">
			<div class="col-sm-12 col-md-12 col-lg-12">
			<section id="pinBoot">
			<?php
			$i = 0;
			foreach($item_kit_images as $item_kit_image) {
				$i ++;
			?>
			<article class="white-panel">
				<div class="form-group">
					<?php echo form_label(lang('common_del_image').':', 'del_image',array('class'=>'col-sm-9 col-md-9 col-lg-8 control-label')); ?>

					<?php echo form_checkbox(array(
						'name'=>'del_images['.$item_kit_image['image_id'].']',
						'id'=>'del_image_'.$item_kit_image['image_id'],
						'class'=>'delete-checkbox',
						'value'=>1
					));?>
					<label for="del_image_<?php echo $item_kit_image['image_id']; ?>"><span></span></label>
				</div>
				<?php echo img(array('src' => app_file_url($item_kit_image['image_id']),'class'=>'img-polaroid img-polaroid-s')); ?>
				
				<?php 
				
				echo form_label(lang('items_image_title').':', 'item_image_title',array('class'=>'control-label'));
				
				$data = array(
				        'name'          => 'titles['.$item_kit_image['image_id'].']',
				        'id'            => 'titles_'.$item_kit_image['image_id'],
				        'placeholder'   => lang('items_enter_a_title'),
				        'class'         => 'form-control',
								'value'					=> $item_kit_image['title'],

				);

				echo form_input($data);

				echo form_label(lang('items_image_alt_text').':', 'item_image_alt_text',array('class'=>'control-label'));

				$data = array(
				        'name'          => 'alt_texts['.$item_kit_image['image_id'].']',
				        'id'            => 'alt_texts_'.$item_kit_image['image_id'],
				        'placeholder'   => lang('items_enter_alt_text'),
				        'class'         => 'form-control',
								'value'					=> $item_kit_image['alt_text'],
				);

				echo form_input($data); 
				
				
				$options = array('' => lang('common_none'));
				
				?>
				
					<?php echo form_label(lang('items_main_image').':', 'main_image_'.$item_kit_image['image_id'],array('class'=>'control-label')); ?>

					<?php echo form_checkbox(array(
						'name'=>'main_image['.$item_kit_image['image_id'].']',
						'id'=>'main_image_'.$item_kit_image['image_id'],
						'class'=>'main-image',
						'value'=>1,
						'checked'=> $item_kit_info->main_image_id == $item_kit_image['image_id']
					));?>
					<label for="main_image_<?php echo $item_kit_image['image_id']; ?>"><span></span></label>
				
			</article>	
			<?php } ?>
			</section>
			</div><!-- end col -->
			</div><!-- end row -->
		<?php } ?>
		
	<?php echo form_hidden('redirect', isset($redirect) ? $redirect : ''); ?>
	<?php echo form_hidden('progression', isset($progression) ? $progression : ''); ?>
	<?php echo form_hidden('quick_edit', isset($quick_edit) ? $quick_edit : ''); ?>
	
	<div class="form-actions">
		<?php
		if (isset($redirect) && $redirect == 'sales')
		{
			echo form_button(array('name' => 'cancel', 'id' => 'cancel', 'class' => 'submit_button btn btn-lg btn-danger', 'value' => 'true', 'content' => lang('common_cancel')));
		}
		?>
		<?php echo form_submit(array('name'=>'submitf', 'id'=>'submitf', 'value'=>lang('common_save'), 'class'=>'submit_button floating-button btn btn-lg btn-primary')); ?>
	</div>
	<?php echo form_close(); ?>
	
</div>
		
<script type='text/javascript'>
<?php $this->load->view("partial/common_js"); ?>

$(document).ready(function()
{
	$('#image_id').itemImagePreview({ selector : '#image_preview' }); // Custom preview container

  setTimeout(function(){$(":input:visible:first","#item_form").focus();},100);

	$('#item_form').validate({
		submitHandler: function(form)
		{
			var args = {
				next:
				{
					label: <?php echo json_encode(lang('common_edit').' '.lang('common_locations')) ?>,
					url: <?php echo json_encode(site_url("item_kits/location_settings/".($item_kit_info->item_kit_id ? $item_kit_info->item_kit_id : -1)."?$query")); ?>,
				}
			};

			doItemSubmit(form, args);
		}

	});
});

$(document).ready(function() {
	$('#pinBoot').pinterest_grid({
		no_columns: 4,
		padding_x: 10,
		padding_y: 10,
		markup_bottom: 50,
		single_column_breakpoint: 700
	});
});

//new image preview
(function($){
	$.fn.itemImagePreview = function(params){
		$(this).change(function(evt){
			$(params.selector).html('');
			if(typeof FileReader == "undefined") return true; // File reader not available.

			var fileInput = $(this);
			
	    if(fileInput.val())
			{
				$('#has_files').val('1');
			} else {
				$('#has_files').val('0');
			}
			
			var files = evt.target.files; // FileList object
			var files_to_skip;
			
			$(document).on('hide.bs.collapse', '.image-preview-column',function(e) {
				var index = $(this).data('file-index');
				var fileName = $(this).data('file-name');
				var fileDisplay = $('.bootstrap-filestyle').find('input').eq(0);
				var fileDisplayValues = fileDisplay.val().split(', ');
				fileDisplayValues.splice(fileDisplayValues.indexOf(fileName), 1);
				fileDisplayValues = fileDisplayValues.join(', ');
				fileDisplay.val(fileDisplayValues);
				
				$('<input>').attr({
				    type: 'hidden',
				    name: 'ignore[]',
						value: index
				}).appendTo(fileInput.closest('form'));
				
			});
			
			// Loop through the FileList and render image files as thumbnails.
			for (var i = 0, f; f = files[i]; i++) {

				// Only process image files.
				if (!f.type.match('image.*')) {
					continue;
				}

				var reader = new FileReader();
				var j = 0;
				// Closure to capture the file information.
				reader.onload = (function(theFile) {
					return function(e) {
						// Render thumbnail.
						var panelTemplateTop =
						'<div class="col-lg-4 col-md-4 col-xs-12 image-preview-column fade in collapse" data-file-name="'+theFile.name+'" data-file-index="'+j+'" id="image-'+j+'">' +
							'<div class="panel panel-piluku panel-equal">' +
								'<div class="panel-heading" style="min-height: 45px;">'+ theFile.name +'<a data-toggle="collapse" href="#image-'+j+'" class="close">&times </a></div>' +
									'<div class="panel-body">' +
										'<div class="thumbnail item_image_preview_thumb">';

						var panelTemplateBottom =
									'</div>' +
								'</div>' +
							'</div>' +
						'</div>';

						var columnTemplate = panelTemplateTop +
								'<img class="file-input-thumb" width="150" src="' + e.target.result + '" title="' + theFile.name + '"/>' +
								panelTemplateBottom;


						if(j % 3 == 0)
						{
							//var rowTemplate = '<div class="row">';
							//$(params.selector).append(rowTemplate)
						}
						
						$(params.selector).append(columnTemplate);
						//$(params.selector).find('.row').last().append(columnTemplate);
						j++;
					};
					
				})(f);

				// Read in the image file as a data URL.
				reader.readAsDataURL(f);
			}
		});
	};
})(jQuery);



<?php if ($this->session->flashdata('manage_success_message')) { ?>
	show_feedback('success', <?php echo json_encode($this->session->flashdata('manage_success_message')); ?>, <?php echo json_encode(lang('common_success')); ?>);
<?php } ?>

$(".main-image").click(function()
{
	var check_status = $(this).prop('checked');
	$(".main-image").prop('checked',false);
	if (check_status)
	{
		$(this).prop('checked',true);
	}
});
</script>
</div>

<?php $this->load->view('partial/footer'); ?>
