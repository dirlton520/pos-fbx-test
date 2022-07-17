<?php $this->load->view("partial/header"); ?>
<div class="container-fluid">
	<div class="row manage-table">
		<div class="panel panel-piluku">
			<div class="panel-heading hidden-print">
				<button class="btn btn-primary text-white hidden-print print_button pull-left" onclick="window.location='<?php echo site_url('items/do_count/'.$count_id); ?>'"> &laquo; <?php echo lang('common_back'); ?> </button>
				
				<h3 class="panel-title">
						<div class="pagination pagination-top hidden-print  text-center" id="pagination_top">
							<?php echo $pagination;?>		
						</div>
					</span>
				</h3>
				<button class="btn btn-primary text-white hidden-print print_button " onclick="window.print();"> <?php echo lang('common_print'); ?> </button>
				<!-- Santosh Changes -->
							<form id="config_columns" class="pull-right">
								<div class="piluku-dropdown btn-group table_buttons pull-right m-left-20">
									<button type="button" class="btn btn-more dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
										<i class="ion-gear-a"></i>
									</button>
									
									<ul id="sortable" class="dropdown-menu dropdown-menu-left col-config-dropdown" role="menu">
											<li class="dropdown-header"><a id="reset_to_default" class="pull-right"><span class="ion-refresh"></span> Reset</a><?php echo lang('common_column_configuration'); ?></li>
																				
											<?php foreach($all_columns as $col_key => $col_value) { 
												$checked = '';
												
												if (isset($selected_columns[$col_key]))
												{
													$checked = 'checked ="checked" ';
												}
												?>
												<li class="sort"><a><input <?php echo $checked; ?> name="selected_columns[]" type="checkbox" class="columns" id="<?php echo $col_key; ?>" value="<?php echo $col_key; ?>"><label class="sortable_column_name" for="<?php echo $col_key; ?>"><span></span><?php echo H($col_value['label']); ?></label><span class="handle ion-drag"></span></a></li>									
											<?php } ?>
										</ul>
								</div>
							</form>
							<!-- END  -->
				
			<?php
			$categories = array();
			$categories[''] =lang('common_all').' '.lang('reports_categories');

			$categories_phppos= $this->Category->sort_categories_and_sub_categories($this->Category->get_all_categories_and_sub_categories());

			foreach($categories_phppos as $key=>$value)
			{
				$name = $this->config->item('show_full_category_path') ? str_repeat('&nbsp;&nbsp;', $value['depth']).$this->Category->get_full_path($key) : str_repeat('&nbsp;&nbsp;', $value['depth']).$value['name'];
				$categories[$key] = $name;
			}
			
			?>
			<br /><br />
				<form action="" method="get" id="filter_form" style="display: inline;">
						<?php echo form_dropdown('category',$categories, $this->input->get('category'), 'id="category" class=""'); ?>
					</form>
			</div>
			<div class="panel-body nopadding table_holder table-responsive" >
				<table class="table table-bordered table-striped table-reports tablesorter" id="sortable_table">
					<thead>
						<tr>
							<?php $tableArr = []; foreach ($selected_columns as $key=> $value) { array_push($tableArr,$key);?>
							<th align="center"><?php echo $value['label']; ?></th>
							<?php } ?>
						</tr>
					</thead>
					<tbody>
						
						<?php foreach ($items_not_counted as $key1=> $row) { ?>
						<tr>
							<?php foreach ($tableArr as  $table_column) { ?>

							<td align="center"><?php echo $row[$table_column]; ?></td>
							<?php } ?>
						</tr>
						<?php } ?> 
					</tbody>
				</table>
				
			</div>		
			
		</div>
	</div>

<div class="row pagination hidden-print alternate text-center" id="pagination_bottom" >
	<?php echo $pagination;?>
</div>

</div>
<?php $this->load->view("partial/footer"); ?>
<script>
	function reload_items_table()
	{
		clearSelections();
		location.reload();
	}

$(document).ready(function()
{
	//Santosh Changes
	 $("#sortable").sortable({
			items : '.sort',
			containment: "#sortable",
			cursor: "move",
			handle: ".handle",
			revert: 100,
			update: function( event, ui ) {
				$input = ui.item.find("input[type=checkbox]");
				$input.trigger('change');
			}
		});
		
		$("#sortable").disableSelection();
		
		$("#config_columns a").on("click", function(e) {
			e.preventDefault();
			
			if($(this).attr("id") == "reset_to_default")
			{
				//Send a get request wihtout columns will clear column prefs
				$.get(<?php echo json_encode(site_url("$controller_name/save_item_not_count_column_prefs")); ?>, function()
				{
					reload_items_table();
					var $checkboxs = $("#config_columns a").find("input[type=checkbox]");
					$checkboxs.prop("checked", false);
					
					<?php foreach($default_columns as $default_col) { ?>
							$("#config_columns a").find('#'+<?php echo json_encode($default_col);?>).prop("checked", true);
					<?php } ?>
				});
			}
			
			if(!$(e.target).hasClass("handle"))
			{
				var $checkboxs = $(this).find("input[type=checkbox]");
				$checkboxs.prop("checked", !$checkboxs.prop("checked")).trigger("change");
			}
			
			return false;
		});
		
		
		$("#sortable input[type=checkbox]").change(

			function(e) {
				//alert();
				var columns = $("#sortable input:checkbox:checked").map(function(){
      		return $(this).val();
    		}).get();
				$.post(<?php echo json_encode(site_url("$controller_name/save_item_not_count_column_prefs")); ?>, {columns:columns}, function(json)
				{
					reload_items_table();
				});
				
		});
		
	//END
});
$("#category").change(function()
{
	$("#filter_form").submit();
});
$("#category").select2({dropdownAutoWidth : true});
</script>