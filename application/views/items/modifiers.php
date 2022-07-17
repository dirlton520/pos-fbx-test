<?php $this->load->view("partial/header"); ?>

<?php if(isset($redirect)) { ?>
<div class="manage_buttons">
	<div class="row">
		<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 margin-top-10">
			<div class="buttons-list">
				<div class="pull-right-btn">
				<?php echo 
					anchor(site_url($redirect), ' ' . lang('common_done'), array('id' => 'done_button','class'=>'btn btn-primary btn-lg ion-android-exit', 'title'=>''));
				?>
				</div>
			</div>
		</div>
	</div>
</div>
<?php } ?>

<div class="row <?php echo $redirect ? 'manage-table' :''; ?>" id="form">
	
	<div class="col-md-12">
		<div class="panel panel-piluku">
			<div class="panel-heading">
				<?php echo lang('common_manage_modifiers'); ?>
			</div>
			
			<div class="panel-body">
					<div class="row form-group">	
							<div class="col-md-12 col-sm-12 col-lg-12">
								<div class="table-responsive">
									<table id="modifiers" class="table">
										<thead>
											<tr>
												<th width="15%"><?php echo lang('common_edit'); ?></th>
												<th width="15%"><?php echo lang('common_name'); ?></th>
												<th width="60%"><?php echo lang('items_values'); ?></th>
												<th width="10%"><?php echo lang('common_delete'); ?></th>
											</tr>
										</thead>
								
										<tbody>
									
										<?php
										 foreach($item_modifiers->result_array() as $modifier) 
										 { 
											 $modifer_items = $this->Item_modifier->get_modifier_items($modifier['id'])->result_array();
											 $all_modifiers = implode(', ',array_column($modifer_items,'name'));
											?>
											<tr data-id="<?php echo H($modifier['id']); ?>">
												<td>
													<a class="edit_modifier" href="<?php echo site_url('items/modifier/'.$modifier['id']); ?>"><?php echo lang('common_edit'); ?></a>
												</td>	
												<td class="item_modifier_name">
													<?php echo H($modifier['name']); ?>
												</td>
												
												<td class="item_modifier_values top">
													<?php echo $all_modifiers; ?>
												</td>
												
												<td>
													<a class="delete_modifier"><?php echo lang('common_delete'); ?></a>
												</td>	
										</tr>
								
										<?php } ?>
										</tbody>
									</table>
										<a href="<?php echo site_url('items/modifier'); ?>" class="add_item_modifier" style="margin:10px"><?php echo lang('items_add_modifier'); ?></a>
							
									</div>
								</div>
							</div>
							
							
												  
 						<div class="form-actions">
 						<?php echo form_submit(array(
 							'name'=>'submitf',
 							'id'=>'submitf',
 							'value'=>lang('common_save'),
 							'class'=>'submit_button floating-button btn btn-lg btn-primary')); ?>
 						</div>			
						 	 
				</div>
			</div>
	</div>
 </div>
</div>
<script type='text/javascript'>
	
	$(document).on('click', '.delete_modifier', function(e) {
		
		var $tr = $(this).closest("tr");
		var id = $tr.data('id');
		
		bootbox.confirm(<?php echo json_encode(lang('items_confirm_delete_mod')); ?>, function(response)
		{
			if (response)
			{
				$.post(<?php echo json_encode(site_url('items/delete_modifier'));?>,{'id': id}, function()
				{
					$tr.remove();
				});
				
			}
		});
		
		
	});
</script>
<?php $this->load->view('partial/footer'); ?>