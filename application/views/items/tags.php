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
				<?php echo form_open_multipart('items/save_tag/',array('id'=>'tags_form','class'=>'form-horizontal')); ?>
							
				<div class="form-group">
					<?php echo form_label(lang('common_tag').':', 'tag_name',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label wide')); ?>
					<div class="col-sm-9 col-md-9 col-lg-9">
						<?php echo form_input(array(
							'type'  => 'text',
							'name'  => 'tag_name',
							'id'    => 'tag_name',
							'value' => '',
							'class'=> 'form-control form-inps',
						)); ?>
					</div>
				</div>
																
				<div class="form-group">	
				<?php echo form_label(lang('common_do_not_sell_at_locations').':', null,array('class'=>'col-sm-3 col-md-3 col-lg-2 col-sm-3 col-md-3 col-lg-2 control-label ')); ?>
					<div class="col-sm-9 col-md-9 col-lg-10">
					<ul id="locations_list" class="list-inline">
					<?php
					
					$locations = array();
					foreach($this->Location->get_all()->result() as $row)
					{
						$locations[$row->location_id] = array('name' => $row->name);
					}
					
					foreach($locations as $location_id => $location) 
					{
						$checkbox_options = array(
						'name' => 'ban_locations[]',
						'class' => 'ban_locations',
						'id' => 'ban_locations'.$location_id,
						'value' => $location_id,
						'checked' => false,
						);
																	
						echo '<li>'.form_checkbox($checkbox_options). '<label for="ban_locations'.$location_id.'"><span></span></label> '.$location['name'].'</li>';
					}
					?>
					</ul>
					</div>
				</div>
				
				
				<?php
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

<?php echo form_open('items/save_tag/',array('id'=>'tag_form','class'=>'form-horizontal')); ?>
<div class="row <?php echo $redirect ? 'manage-table' :''; ?>">
	<div class="col-md-12">
		<div class="panel panel-piluku">
			<div class="panel-heading"><?php echo lang("items_manage_tags"); ?></div>
			<div class="panel-body">
				<a href="javascript:void(0);" class="add_tag" data-tag_id="0">[<?php echo lang('items_add_tag'); ?>]</a>
					<div id="tag_list" class="tag-tree">
						<?php echo $tag_list; ?>
					</div>
				<a href="javascript:void(0);" class="add_tag" data-tag_id="0">[<?php echo lang('items_add_tag'); ?>]</a>
			</div>
		</div>
	</div>
</div><!-- /row -->

<?php  echo form_close(); ?>
</div>

			
<script type='text/javascript'>


	$("#tags_form").submit(function(event)
	{
		event.preventDefault();

		$(this).ajaxSubmit({ 
			success: function(response, statusText, xhr, $form){
				show_feedback(response.success ? 'success' : 'error', response.message, response.success ? <?php echo json_encode(lang('common_success')); ?> : <?php echo json_encode(lang('common_error')); ?>);
				if(response.success)
				{
					$("#tag-input-data").modal('hide');
					$('#tag_list').load("<?php echo site_url("items/tag_list"); ?>");
					
				}		
			},
			dataType:'json',
		});
	});

	$(document).on('click', ".edit_tag",function()
	{
		$("#tagModalDialogTitle").html(<?php echo json_encode(lang('common_edit')); ?>);
				
		var tag_id = $(this).data('tag_id');
		$("#tags_form").attr('action',SITE_URL+'/items/save_tag/'+tag_id);
		
		$("#tags_form").find('#tag_name').val($(this).data('name'));
		
		$('#del_image').prop('checked',false);
		
		$(".hide_from_grid_checkbox").prop('checked',false);
		$.getJSON(SITE_URL+'/items/get_hidden_locations_for_tag/'+tag_id, function(locations)
		{
			for(var k=0;k<locations.length;k++)
			{
				$("#locations_"+locations[k]+"_hide_from_grid").prop('checked',true);
			}
		});
		
		$("#tag-modal .ban_locations").prop('checked', false);
		$.getJSON("<?php echo site_url('items/get_banned_locations_for_tag');?>/"+tag_id,function (banned_location_ids)
		{
			for(var k=0;k<banned_location_ids.length;k++)
			{
				var location_id = banned_location_ids[k];
			
				$("#ban_locations"+location_id).prop('checked',true);
			
			}
		});
		
		//show
		$("#tag-input-data").modal('show');
	});

$(document).on('click', ".add_tag",function()
{
	bootbox.prompt(<?php echo json_encode(lang('items_please_enter_tag_name')); ?>, function(tag_name)
	{
		if (tag_name)
		{
			$.post('<?php echo site_url("items/save_tag");?>', {tag_name : tag_name},function(response) {
			
				show_feedback(response.success ? 'success' : 'error', response.message,response.success ? <?php echo json_encode(lang('common_success')); ?> : <?php echo json_encode(lang('common_error')); ?>);

				//Refresh tree if success
				if (response.success)
				{
					$('#tag_list').load("<?php echo site_url("items/tag_list"); ?>");
				}
			}, "json");

		}
	});
});

$(document).on('click', ".delete_tag",function()
{
	var tag_id = $(this).data('tag_id');
	if (tag_id)
	{
		bootbox.confirm(<?php echo json_encode(lang('items_tag_delete_confirmation')); ?>, function(result)
		{
			if (result)
			{
				$.post('<?php echo site_url("items/delete_tag");?>', {tag_id : tag_id},function(response) {
				
					show_feedback(response.success ? 'success' : 'error', response.message,response.success ? <?php echo json_encode(lang('common_success')); ?> : <?php echo json_encode(lang('common_error')); ?>);

					//Refresh tree if success
					if (response.success)
					{
						$('#tag_list').load("<?php echo site_url("items/tag_list"); ?>");
					}
				}, "json");
			}
		});
	}
	
});

</script>
<?php $this->load->view('partial/footer'); ?>
