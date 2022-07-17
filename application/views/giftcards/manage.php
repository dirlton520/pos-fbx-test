<?php $this->load->view("partial/header"); ?>
<script type="text/javascript">
$(document).ready(function()
{	 
	 enable_sorting("<?php echo site_url("$controller_name/sorting"); ?>");
	 enable_select_all();
    enable_checkboxes();
    enable_row_selection();
    enable_search('<?php echo site_url("$controller_name");?>',<?php echo json_encode(lang("common_confirm_search"));?>);
		<?php if (!$this->config->item('disable_giftcard_detection')) { ?>
			giftcard_swipe_field($("#search"));
		<?php } ?>
		<?php if(!$deleted) { ?>
			enable_delete(<?php echo json_encode(lang($controller_name."_confirm_delete"));?>,<?php echo json_encode(lang($controller_name."_none_selected"));?>);
		<?php } else { ?>
			enable_delete(<?php echo json_encode(lang($controller_name."_confirm_undelete"));?>,<?php echo json_encode(lang($controller_name."_none_selected"));?>);
		<?php } ?>
			enable_cleanup(<?php echo json_encode(lang("giftcards_confirm_cleanup"));?>);
				
	$('#generate_barcodes').click(function()
	{
		var selected = get_selected_values();
		
		if (selected.length == 0)
		{
			bootbox.alert(<?php echo json_encode(lang('common_must_select_item_for_barcode')); ?>);
			return false;
		}

		$("#skip-labels").modal('show');
		return false;
	});
	
	$("#generate_barcodes_form").submit(function()
	{
		var selected = get_selected_values();
		var num_labels_skip = $("#num_labels_skip").val() ? $("#num_labels_skip").val() : 0;
		var url = '<?php echo site_url("giftcards/generate_barcodes");?>'+'/'+selected.join('~')+'/'+num_labels_skip;
		window.location = url;
		return false;
	});

	$('#generate_barcode_labels').click(function()
    {
    	var selected = get_selected_values();
    	if (selected.length == 0)
    	{
    		bootbox.alert(<?php echo json_encode(lang('common_must_select_item_for_barcode')); ?>);
    		return false;
    	}

    	$(this).attr('href','<?php echo site_url("giftcards/generate_barcode_labels");?>/'+selected.join('~'));
    });
	 
	 <?php if ($this->session->flashdata('manage_success_message')) { ?>
	 	show_feedback('success', <?php echo json_encode($this->session->flashdata('manage_success_message')); ?>, <?php echo json_encode(lang('common_success')); ?>);
	 <?php } ?>

});

function init_table_sorting()
{
	//Only init if there is more than one row
	if($('.tablesorter tbody tr').length >1)
	{
		$("#sortable_table").tablesorter(
		{
			sortList: [[1,0]],
			headers:
			{
				0: { sorter: false},
				3: { sorter: false}
			}
		});
	}
}

</script>

<div class="modal fade skip-labels" id="skip-labels" role="dialog" aria-labelledby="skipLabels" aria-hidden="true">
    <div class="modal-dialog customer-recent-sales">
      	<div class="modal-content">
	        <div class="modal-header">
	          	<button type="button" class="close" data-dismiss="modal" aria-label=<?php echo json_encode(lang('common_close')); ?>><span aria-hidden="true">&times;</span></button>
	          	<h4 class="modal-title" id="skipLabels"><?php echo lang('common_skip_labels') ?></h4>
	        </div>
	        <div class="modal-body">
				
	          	<?php echo form_open("items/generate_barcodes", array('id'=>'generate_barcodes_form','autocomplete'=> 'off')); ?>				
				<input type="text" class="form-control text-center" name="num_labels_skip" id="num_labels_skip" placeholder="<?php echo lang('common_skip_labels') ?>">
					<?php echo form_submit('generate_barcodes_form',lang("common_submit"),'class="btn btn-block btn-primary"'); ?>
				<?php echo form_close(); ?>
				
	        </div>
    	</div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<div class="manage_buttons">
	<!-- Css Loader  -->
	<div class="spinner" id="ajax-loader" style="display:none">
	  <div class="rect1"></div>
	  <div class="rect2"></div>
	  <div class="rect3"></div>
	</div>

	<div class="manage-row-options hidden">
		<div class="email_buttons giftcards">
			<?php if(!$deleted) { ?>
			<?php echo 
				anchor("$controller_name/generate_barcode_labels",
				'<span class="">'.lang("common_barcode_labels").'</span>',
				array('id'=>'generate_barcode_labels', 
					'class' => 'btn btn-primary btn-lg hidden-xs disabled',
					'title'=>lang('common_barcode_labels'))); 
			?>
			<?php echo 
				anchor("$controller_name/generate_barcodes",
				'<span class="">'.lang("common_barcode_sheet").'</span>',
				array('id'=>'generate_barcodes', 
					'class' => 'btn btn-primary btn-lg hidden-xs disabled',
					'target' => '_blank',
					'title'=>lang('common_barcode_sheet'))); 
			?>
			
			<a href="#" class="btn btn-lg btn-clear-selection btn-warning"><span class="ion-close-circled"></span> <?php echo lang('common_clear_selection'); ?></a>
			
			<?php echo 
				anchor("$controller_name/delete",
				'<span class="ion-trash-a"></span> '.'<span class="hidden-xs">'.lang("common_delete").'</span>',
				array('id'=>'delete','class'=>'btn btn-red btn-lg disabled','title'=>lang("common_delete"))); 
			?>
			
			<?php } else { ?>
				
			<?php if ($this->Employee->has_module_action_permission($controller_name, 'delete', $this->Employee->get_logged_in_employee_info()->person_id)) {?>
			<?php echo anchor("$controller_name/undelete",
					'<span class="ion-trash-a"></span> '.'<span class="hidden-xs">'.lang("common_undelete").'</span>',
					array('id'=>'delete','class'=>'btn btn-green btn-lg disabled delete_inactive','title'=>lang("common_undelete"))); ?>
			<?php } ?>

			<a href="#" class="btn btn-lg btn-clear-selection btn-warning"><span class="ion-close-circled"></span> <?php echo lang('common_clear_selection'); ?></a>
			
			<?php } ?>
		</div>
	</div>
	<div class="row">
		<div class="col-md-9 col-sm-10 col-xs-10">
			<?php echo form_open("$controller_name/search",array('id'=>'search_form', 'autocomplete'=> 'off')); ?>
				<div class="search no-left-border">
					<input type="text" class="form-control" name ='search' id='search' value="<?php echo H($search); ?>" placeholder="<?php echo $deleted ? lang('common_search_deleted') : lang('common_search'); ?> <?php echo lang('module_'.$controller_name); ?>"/>
				</div>
				<div class="clear-block <?php echo ($search=='') ? 'hidden' : ''  ?>">
					<a class="clear" href="<?php echo site_url($controller_name.'/clear_state'); ?>">
						<i class="ion ion-close-circled"></i>
					</a>	
				</div>
			</form>
			
		</div>
		<div class="col-md-3 col-sm-2 col-xs-2">	
			<div class="buttons-list">
				<div class="pull-right-btn">
					<?php if ($deleted) 
					{
						echo 
						anchor("$controller_name/toggle_show_deleted/0", '<span class="ion-android-exit"></span> <span class="hidden-xs">'.lang('common_done').'</span>',
							array('class'=>'btn btn-primary btn-lg toggle_deleted','title'=> lang('common_done')));
					}	
					?>
					<?php 
					if($this->Employee->has_module_action_permission($controller_name, 'add_update', $this->Employee->get_logged_in_employee_info()->person_id) && !$deleted)
					{
						echo anchor("$controller_name/view/-1/", '<span class="ion-plus"></span> '.lang($controller_name.'_new'),
							array('class'=>'btn btn-primary btn-lg hidden-sm hidden-xs','title'=>lang($controller_name.'_new')));
					}
					?>
					<?php if(!$deleted) { ?>
						
					<div class="piluku-dropdown btn-group">
						<button type="button" class="btn btn-more dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
						<span class="visible-xs ion-android-more-vertical"></span>
						<span class="hidden-xs ion-android-more-horizontal"></span>
					</button>
					<ul class="dropdown-menu" role="menu">
						<li class="visible-sm visible-xs">
							<?php echo 
								anchor("$controller_name/view/-1/",
								'<span class="ion-plus-round"> '.lang('common_add').' '.lang($controller_name.'_new').'</span>',
								array('class'=>'', 
									'title'=>lang($controller_name.'_new')));
							?>
						</li>
						<li>
							<?php echo anchor("$controller_name/excel_import/",
							'<span class="ion-ios-download-outline"> '.lang("common_excel_import").'</span>',
							array('class'=>' ',
								'title'=>lang('common_excel_import')));
							?>
						</li>
						
						<?php if ($this->Employee->has_module_action_permission($controller_name, 'excel_export', $this->Employee->get_logged_in_employee_info()->person_id)) {?>				
						
						<li>
							<?php echo anchor("$controller_name/excel_export",
							'<span class="ion-ios-upload-outline"> '.lang("common_excel_export").'</span>',
								array('class'=>'hidden-xs'));
							?>
						</li>
						<?php } ?>
						<li>
							<?php echo anchor("http://giftcards.phppointofsale.com",
							'<span class="ion-loop"> '.lang("giftcards_buy").'</span>',
								array('class'=>'hidden-xs', 'target'=>'_blank'));
							?>
						</li>
						<?php if ($this->Employee->has_module_action_permission($controller_name, 'delete', $this->Employee->get_logged_in_employee_info()->person_id)) {?>
							<li>
								<?php echo 
									anchor("$controller_name/cleanup",
									'<span class="ion-loop"> '.lang("giftcards_cleanup").'</span>',
									array('id'=>'cleanup', 'class'=>'','title'=>lang("giftcards_cleanup"))); 
								?>
							</li>
						<?php }?>
						
						<?php if ($this->Employee->has_module_action_permission($controller_name, 'delete', $this->Employee->get_logged_in_employee_info()->person_id)) {?>
							<li>
									<?php echo anchor("$controller_name/toggle_show_deleted/1", '<span class="ion-trash-a"> '.lang($controller_name."_manage_deleted").'</span>',
										array('class'=>'toggle_deleted','title'=> lang($controller_name."_manage_deleted"))); ?>
							</li>
						<?php }?>
					</ul>
					</div>
				<?php } ?>
				</div>
			</div>
		</div>
	</div>
</div>


	<div class="container-fluid">
		<div class="row manage-table">
			<div class="panel panel-piluku">
				<div class="panel-heading">
				<h3 class="panel-title">
					<?php echo ($deleted ? lang('common_deleted').' ' : '').lang('module_'.$controller_name); ?>
					<span title="<?php echo $total_rows; ?> total <?php echo $controller_name?>" class="badge bg-primary tip-left" id="manage_total_items"><?php echo $total_rows; ?></span>
					<span class="panel-options custom">
						<div class="pagination  pagination-top hidden-print alternate text-center" id="pagination_top" >
							<?php echo $pagination;?>
						</div>
					</span>
				</h3>
			</div>
			<div class="panel-body nopadding table_holder table-responsive"  >
					<?php echo $manage_table; ?>			
			</div>		
			
		</div>
	</div>
</div>
<div class="row pagination hidden-print alternate text-center" id="pagination_bottom" >
	<?php echo $pagination;?>
</div>
</div>
<?php $this->load->view("partial/footer"); ?>