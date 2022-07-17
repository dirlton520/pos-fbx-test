<?php $this->load->view("partial/header"); ?>

<div class="modal fade skip-labels" id="skip-labels" role="dialog" aria-labelledby="skipLabels" aria-hidden="true">
    <div class="modal-dialog customer-recent-sales">
      	<div class="modal-content">
	        <div class="modal-header">
	          	<button type="button" class="close" data-dismiss="modal" aria-label=<?php echo json_encode(lang('common_close')); ?>><span aria-hidden="true">&times;</span></button>
	          	<h4 class="modal-title" id="skipLabels"><?php echo lang('common_skip_labels') ?></h4>
	        </div>
	        <div class="modal-body">
				
	          	<?php echo form_open("items/generate_barcodes_from_count/$count_id", array('id'=>'generate_barcodes_form','autocomplete'=> 'off')); ?>				
				<input type="text" class="form-control text-center" name="num_labels_skip" id="num_labels_skip" placeholder="<?php echo lang('common_skip_labels') ?>">
					<?php echo form_submit('generate_barcodes_form',lang("common_submit"),'class="btn btn-block btn-primary"'); ?>
				<?php echo form_close(); ?>
				
	        </div>
    	</div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
		<ul class="list-inline text-right">
			<!-- <li>
				<?php
				echo form_open("$controller_name/do_count/$count_id");
            $options = array(
                            '' => 'Select',
                             '2' => '2',
                             '50' => '50',
                             '100' => '100');
            echo form_dropdown('sel',$options,'','class="form-control form-inps" id="type"');
            //echo form_dropdown('type', $rule_types, $rule, 'class="form-control form-inps" id="type"');
			echo form_submit('submit',lang('common_submit'),'class="btn btn-primary btn-lg" id="type"');
				?>
			</li> -->
			<li>
				<?php echo form_open("$controller_name/do_count/$count_id",array('id'=>'search_form','method'=>'get', 'autocomplete'=> 'off')); ?>
				<input type="text" class="form-control" name ='search' id='search1' value="<?php echo H($this->input->get('search')); ?>" placeholder="<?php echo H(lang('items_search_by_barcode'));?>"/>
				
			</li>
			<li>
				<input type="submit" value="<?php echo H(lang('common_search')); ?>" class="btn btn-primary btn-lg"/>
				</form>	
			</li>

			<?php if ($count_info->status == 'open') { ?>		
			<li>
				<?php echo anchor('items/excel_import_count', lang('common_excel_import'),array('class'=>'btn btn-success btn-lg'));?>
			</li>
			<?php } ?>
			
			<li>
				<?php echo anchor('items/generate_barcodes_from_count/'.$count_id, lang('common_barcode_sheet'),array('target' => '_blank','class'=>'btn btn-success btn-lg', 'id' => 'generate_barcodes'));?>
			</li>
			
			<li>
				<?php echo anchor('items/generate_barcodes_labels_from_count/'.$count_id, lang('common_barcode_labels'),array('target' => '_blank','class'=>'btn btn-success btn-lg'));?>
			</li>
			
			<li>
				<?php echo anchor('items/count_not_counted/0/'.$count_id, lang('items_show_items_not_counted').' &raquo;',array('class'=>'btn btn-warning btn-lg', 'id' => 'show_items_not_counted'));?>
			</li>
			
			<li>
				<?php echo anchor('items/count_not_counted/1/'.$count_id, lang('items_not_counted_in_stock').' &raquo;',array('class'=>'btn btn-warning btn-lg', 'id' => 'show_items_not_counted'));?>
			</li>
			
			
		</ul>

<div id="content-header" class="hidden-print">
	<div class="col-lg-12 col-md-12 no-padding-left visible-lg visible-md">
</div>
</div>
<div id="count_container">
	<?php $this->load->view("items/do_count_data"); ?>
</div>

<script type='text/javascript'>
	
	$("#generate_barcodes").click(function()
	{
		$("#skip-labels").modal('show');
		return false;
	});

	$("#generate_barcodes_form").submit(function()
	{
		var selected = get_selected_values();
		var num_labels_skip = $("#num_labels_skip").val() ? $("#num_labels_skip").val() : 0;
		var url = '<?php echo site_url("items/generate_barcodes_from_count/$count_id");?>/'+num_labels_skip;
		window.location = url;
		return false;
	});

</script>
	
	

<?php $this->load->view('partial/footer'); ?>