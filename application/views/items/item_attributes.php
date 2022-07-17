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
 <?php echo form_open($controller_name.'/save_attributes',array('id'=>'save_item_attributes','class'=>'form-horizontal')); ?>
	
	<div class="col-md-12">
		<div class="panel panel-piluku">
			<div class="panel-heading">
				<?php echo lang('items_manage_attributes'); ?>
			</div>
			
			<div class="panel-body">
					<div class="row form-group">	
							<div class="col-md-12 col-sm-12 col-lg-12">
								<div class="table-responsive">
									<table id="attributes" class="table">
										<thead>
											<tr>
												<th width="30%"><?php echo lang('common_name'); ?></th>
												<th width="60%"><?php echo lang('items_values'); ?></th>
												<th width="10%"><?php echo lang('common_delete'); ?></th>
											</tr>
										</thead>
								
										<tbody>
									
										<?php
										 foreach($item_attributes->result_array() as $item_attribute) { 
											 $attribute_id = $item_attribute['id'];
											 $values_for_attribute = array();
											 $values_for_attribute_str = '';
											 foreach($this->Item_attribute_value->get_values_for_attribute($attribute_id)->result_array() as $attr_value_row)
											 {
											 	$values_for_attribute[] = $attr_value_row['name'];
											 }
											 
											 
											 $values_for_attribute_str = implode('|',$values_for_attribute);
											?>
											<tr data-index="<?php echo H($attribute_id); ?>">
												<td class="item_attribute_name">
													<input type="text" class="attributes form-control" size="1" name="attributes[<?php echo H($attribute_id); ?>][name]" value="<?php echo H($item_attribute['name']);?>" />
												</td>
												
												<td class="item_attribute_values top">
													<input type="text" class="attributes form-control" size="20" name="attributes[<?php echo H($attribute_id); ?>][values]" value="<?php echo H($values_for_attribute_str);?>" />
												</td>
												
												<td>
													<a class="delete_attribute"><?php echo lang('common_delete'); ?></a>
												</td>	
										</tr>
								
										<?php } ?>
										</tbody>
									</table>
							
									<a href="javascript:void(0);" class="add_item_attribute"><?php echo lang('items_add_attribute'); ?></a>
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
<?php
echo form_close();
?>
<script type='text/javascript'>
	
	$('.item_attribute_values input').selectize({
		delimiter: '|',
		create: true,
		render: {
	      option_create: function(data, escape) {
				var add_new = <?php echo json_encode(lang('common_add_value')) ?>;
	        return '<div class="create">'+escape(add_new)+' <strong>' + escape(data.input) + '</strong></div>';
	      }
		},
	});
	
	var attribute_index = -1;
	
	$(document).on('click', '.add_item_attribute', function(e) {
		
		var $tbody = $("#attributes").find("tbody");
		
		$tbody.append('<tr data-index="' + attribute_index +'">' +
			'<td class="item_attribute_name">' +
				'<input type="text" data-index="-1" size="1" class="attributes form-control" name="attributes['+ attribute_index +'][name]" value="" />' +
			'</td>' +
			'<td class="item_attribute_values top">' +
				'<input type="text" data-index="-1" size="20" class="attributes form-control" name="attributes['+ attribute_index +'][values]" value="" />' +
			'</td>'
			);
		
		$tr = $tbody.find('tr').last();
		
		$tr.find('.item_attribute_values input').selectize({
			delimiter: '|',
			create: true,
			render: {
		      option_create: function(data, escape) {
					var add_new = <?php echo json_encode(lang('common_add_value')) ?>;
		        return '<div class="create">'+escape(add_new)+' <strong>' + escape(data.input) + '</strong></div>';
		      }
			},
		});
			
		$tr.append(
			'<td>' +
				'<a class="delete_attribute"><?php echo lang('common_delete'); ?></a>' +
			'</td>'
			);
			
			attribute_index --;
		});
		
		
		$(document).on('click', '.delete_attribute', function(e) {
			var $tr = $(this).closest("tr");
			var index = $tr.data('index');
						
			$tr.remove();
			
			if(index > 0)
			{
				$("#save_item_attributes").append('<input type="hidden" class="delete_attribute" name="attributes_to_delete[]" value="'+ index +'" />');
			}
		});
		
		
		var submitting = false;
		
		$('#save_item_attributes').validate({
			submitHandler:function(form)
			{
				if (submitting) return;
				submitting = true;
				$(form).ajaxSubmit({
				success:function(response)
				{
		
					//Don't let the tiers, taxes, providers, methods double submitted, so we change the name
					$('.attributes').filter(function() {
					    return parseInt($(this).data('index')) < 0;
					}).attr('name','items_added[]');
				
					if(response.success)
					{
						show_feedback('success',response.message,<?php echo json_encode(lang('common_success')); ?>);
					}
					else
					{
						show_feedback('error',response.message,<?php echo json_encode(lang('common_error')); ?>);
					}
					submitting = false;
					
					<?php if ($redirect) { ?>
						window.location = <?php echo json_encode(site_url($redirect)); ?>
					<?php } ?>
						
				},
				dataType:'json'
			});

			}
		});
		
		$('#done_button').click(function(e) {
			
			e.preventDefault();
			$('#save_item_attributes').ajaxSubmit({success: function()
			{
				window.location = $(e.target).attr('href');
			}});
			
		});
</script>
<?php $this->load->view('partial/footer'); ?>