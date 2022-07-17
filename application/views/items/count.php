<?php $this->load->view("partial/header"); ?>
<div class="manage_buttons">
<div class="buttons-list">
	<div class="pull-right-btn">
		<?php 
			echo anchor('items/new_count', lang('items_new_inventory_count'),array('class'=>'btn btn-primary btn-lg')).'&nbsp;';
			if ($status == 'closed') 	
			{ 
			 	echo anchor('items/count/open', lang('items_show_open_counts'),array('class'=>'btn btn-success btn-lg'));
			}
			else
			{
				echo anchor('items/count/closed', lang('items_show_closed_counts'),array('class'=>'btn btn-warning btn-lg'));	
			}
		?>
	</div>
</div>

</div>

	<div class="container-fluid">
		<div class="row manage-table">
			<div class="panel panel-piluku">
			<div class="panel-heading">
				<h3 class="panel-title">
					<?php echo lang('items_count_inventory')?>
					<span class="panel-options custom">
						<?php if($pagination) {  ?>
							<div class="pagination pagination-top hidden-print  text-center" id="pagination_top">
								<?php echo $pagination;?>		
							</div>
						<?php }  ?>
					</span>
				</h3>
			</div>
			<div class="panel-body">
				<div class="widget-content nopadding table_holder table-responsive" >
					<table class="table table-hover">
						<thead>
							<tr>
								<th class="text-center"><?php echo lang('items_count_date');?></th>
								<th class="text-center"><?php echo lang('items_items_counted');?></th>
								<th class="text-center"><?php echo lang('common_employee');?></th>
								<th class="text-center"><?php echo lang('common_status');?></th>
								<th class="text-center"><?php echo lang('common_comments');?></th>
								<th class="text-center"><?php echo lang('items_delete_count');?></th>
								<th class="text-center"><?php echo $status == 'closed' ? lang('items_view_count') : lang('items_continue_count');?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach($counts as $count_row) { ?>
								<tr class="text-center">
									<td><?php echo date(get_date_format().' '.get_time_format(), strtotime($count_row['count_date']));?></td>
									<td><?php echo $this->Inventory->get_number_of_items_counted($count_row['id']);?></td>
									<td>
										<?php 
											$emp_info = $this->Employee->get_info($count_row['employee_id']);
											echo H($emp_info->first_name.' '.$emp_info->last_name); 
										?>
									</td>
									<td>
										<?php
											$status = '';
											switch($count_row['status'])
											{
												case 'open':
													$status = lang('common_open');
												break;

												case 'closed':
													$status = lang('common_closed');
												break;
											}
										?>
										<a href="#" id="status_<?php echo $count_row['id']; ?>" class="xeditable" data-type="select" data-pk="<?php echo $count_row['id']; ?>" data-name="status" data-url="<?php echo site_url('items/edit_count'); ?>" data-title="<?php echo H(lang('items_edit_status')); ?>"><?php echo $status; ?></a>
									</td>
									<td><a href="#" id="comment_<?php echo $count_row['id']; ?>" class="xeditable" data-type="text" data-pk="<?php echo $count_row['id']; ?>" data-name="comment" data-url="<?php echo site_url('items/edit_count'); ?>" data-title="<?php echo H(lang('items_edit_comment')); ?>"><?php echo $count_row['comment'] ? H($count_row['comment']) : 'None'; ?></a></td>
									<td><?php echo anchor('items/delete_inventory_count/'.$count_row['id'].'/'.$count_row['status'], lang('items_delete_count'),array('class'=>'btn btn-danger delete-count'));?></td>
									<td><?php echo anchor('items/do_count/'.$count_row['id'], $count_row['status']=='closed' ? lang('items_view_count') : lang('items_continue_count'),array('class'=>' btn btn-success'));?></td>
								</tr>	
								
								<script type='text/javascript'>
								$(document).ready(function(){									
									
									<?php if ($count_row['status'] != 'closed') { ?>
									
									$('#status_<?php echo $count_row['id']; ?>').editable({
									     value: <?php echo json_encode($count_row['status']); ?>,    
									     source: [
									          {value: 'open', text: <?php echo json_encode(lang('items_open')); ?>},
											 	 {value: 'closed', text: <?php echo json_encode(lang('items_closed')); ?>}
									       ]
									 });
								 <?php
								 } 
								 ?>
 									$('#comment_<?php echo $count_row['id']; ?>').editable();
								});
								</script>
							<?php } ?>
						</tbody>
					</table>		
				</div>	
			</div><!-- /panel-body -->
		</div>	
	
		<?php if($pagination) {  ?>
			<div class="text-center">
				<div class="pagination hidden-print alternate text-center" id="pagination_bottom" >
					<?php echo $pagination;?>
				</div>
			</div>
		<?php } ?>
	</div>
</div>
</div>


<script type='text/javascript'>
$(document).ready(function(){
	
	
	$(".delete-count").click(function(e)
	{
		e.preventDefault();
		var $that = $(this);
		bootbox.confirm(<?php echo json_encode(lang('items_delete_count_confirm')); ?>,function(result)
		{
			if(result)
			{
				window.location = $that.attr('href');
		
			}
		});
	});

	
	$('#count').editable();
	
   $('.xeditable').on('shown', function(e, editable) {

		$(this).closest('.table-responsive').css('overflow-x','hidden');

	   	editable.input.postrender = function() {
			//Set timeout needed when calling price_to_change.editable('show') (Not sure why)
			setTimeout(function() {
	        editable.input.$input.select();
			}, 200);
		};
	});

   	$('.xeditable').on('hidden', function(e, editable) {
		$(this).closest('.table-responsive').css('overflow-x','auto');
	});

	
	
});
</script>
<?php $this->load->view('partial/footer'); ?>