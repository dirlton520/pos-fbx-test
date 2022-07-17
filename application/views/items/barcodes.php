<?php $this->load->view("partial/header"); ?>
<?php 
$hidden = array('item_id' => $item_info->item_id);
echo form_open('items/print_barcodes/'.$item_info->item_id,array('id'=>'item_form','class'=>'form-horizontal'), $hidden); ?>

	<div class="row">
		<div class="col-md-12">
			<div class="panel panel-piluku">
				<div class="panel-heading"><?php echo lang("common_basic_information"); ?></div>
				<div class="panel-body">
					
					<div class="form-group">
						<?php echo form_label(lang('common_item_number_expanded').':', null,array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label')); ?>
						<div class="col-sm-9 col-md-9 col-lg-10 form-text">
							<?php echo $item_info->item_number; ?>
						</div>
					</div>

					<div class="form-group">
						<?php echo form_label(lang('common_item_name').':', null,array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label')); ?>
						<div class="col-sm-9 col-md-9 col-lg-10 form-text">
							<?php echo $item_info->name ?>
						</div>
					</div>

					<div class="form-group">
						<?php echo form_label(lang('common_category').':', null,array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label')); ?>
						<div class="col-sm-9 col-md-9 col-lg-10 form-text">
							<?php echo $this->Item->get_category($item_info->category_id); ?>
						</div>
					</div>
					
					<?php if(!count($item_variations) > 0) { ?>
					<div class="form-group">
						<?php echo form_label(lang('items_number_of_barcodes').':', null,array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label')); ?>
						<div class="col-sm-9 col-md-9 col-lg-10">
							<?php 
							$numbers = array();
							foreach(range(1, 50) as $number) 
							{ 
								$numbers[$number] = $number;
							}
							?> 
						
	 						<?php echo form_dropdown('items_number_of_barcodes', $numbers,
	 						1 , 'class="form-control" id="items_number_of_barcodes"');
							?>
							
						</div>
					</div>
					
					<div class="form-group">
						<?php echo form_label(lang('common_barcode_labels').':', null,array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label')); ?>
						<div class="col-sm-9 col-md-9 col-lg-10">
							<?php echo form_submit('barcode_labels_action',lang("common_barcode_labels"),'class="btn btn-primary"'); ?>
						</div>
					</div>
				
					<div class="form-group">
						<?php echo form_label(lang('common_barcode_sheet').':', null,array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label')); ?>
						<div class="col-sm-9 col-md-9 col-lg-10">
							<?php echo form_submit('barcode_sheet_action',lang("common_barcode_sheet"),'class="btn btn-primary" id="generate_barcode_sheet"'); ?>
						</div>
					</div>
					
					<?php } else { ?>
						
						<div class="form-group">
							<?php echo form_label(lang('items_number_of_barcodes').':', null,array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label')); ?>
							<div class="col-sm-9 col-md-9 col-lg-10">
								<table class="table table-striped table-hover custom-table">
									<thead>
										<tr>
											<th></th>
											<th><?php echo lang("common_item_number"); ?></th>
											<th><?php echo lang("items_attributes"); ?></th>
										
											
										</tr>
									</thead>
									<tbody>
										<?php foreach($item_variations as $item_variation_id => $item_variation) { ?>
											<tr>
												<td><input type="text" class="form-control number_of_barcodes_variations" name="item_variations_number_of_barcodes[<?php echo $item_variation_id; ?>]" ></td>	
												
												<td><?php echo $item_variation['item_number']; ?></td>
												<td>
													<?php
													$description = $item_variation['name'];;
													foreach($item_variation['attributes'] as $attribute)
													{
														$description .= ' ('.$attribute['label'].')' . "<br>";
													}
													
													echo $description;
													?>
												</td>
											</tr>
										<?php } ?>
									</tbody>
								</table>
							</div>
						</div>
						
						<div class="form-group">
							<?php echo form_label(lang('common_barcode_labels').':', null,array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label')); ?>
							<div class="col-sm-9 col-md-9 col-lg-10">
								<?php echo form_submit('barcode_labels_action',lang("common_barcode_labels"),'class="btn btn-primary"'); ?>
							</div>
						</div>
				
						<div class="form-group">
							<?php echo form_label(lang('common_barcode_sheet').':', null,array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label')); ?>
							<div class="col-sm-9 col-md-9 col-lg-10">
								<?php echo form_submit('barcode_sheet_action',lang("common_barcode_sheet"),'class="btn btn-primary" id="generate_barcode_sheet"'); ?>
							</div>
						</div>
						
					<?php } ?>
					</div>
					
					<div class="modal fade skip-labels" id="skip-labels" role="dialog" aria-labelledby="skipLabels" aria-hidden="true">
					    <div class="modal-dialog customer-recent-sales">
					      	<div class="modal-content">
						        <div class="modal-header">
						          	<button type="button" class="close" data-dismiss="modal" aria-label=<?php echo json_encode(lang('common_close')); ?>><span aria-hidden="true">&times;</span></button>
						          	<h4 class="modal-title" id="skipLabels"><?php echo lang('common_skip_labels') ?></h4>
						        </div>
						        <div class="modal-body">
				
									<input type="text" class="form-control text-center" name="skip" id="skip" placeholder="<?php echo lang('common_skip_labels') ?>">
										<?php echo form_submit('barcode_sheet_action',lang("common_submit"),'class="btn btn-block btn-primary"'); ?>
				
						        </div>
					    	</div><!-- /.modal-content -->
					    </div><!-- /.modal-dialog -->
					</div><!-- /.modal -->
					
					<?php  echo form_close(); ?>
					
			</div>
			
		</div>
	</div>
			
<script type='text/javascript'>

	$("#generate_barcode_sheet").click(function()
	{
		$("#skip-labels").modal('show');
		return false;
	});

</script>
<?php $this->load->view('partial/footer'); ?>
