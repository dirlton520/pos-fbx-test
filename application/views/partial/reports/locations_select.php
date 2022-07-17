<?php 
$locations_to_use = $authenticated_locations;

if (isset($can_view_inventory_at_all_locations) && $can_view_inventory_at_all_locations)
{
	$locations_to_use = $all_locations_in_system;
}

if (count($locations_to_use) > 1) {?>		
<div class="form-group">	
	<?php echo form_label(isset($label) ? $label : lang('common_locations').':', null,array('class'=>'col-sm-3 col-md-3 col-lg-2 col-sm-3 col-md-3 col-lg-2 control-label')); ?>
		<div class="col-sm-9 col-md-9 col-lg-10">
		<ul id="reports_locations_list" class="list-inline">
			<?php
			echo '<li>'.form_checkbox(
				array(
								'id' => 'select_all',
								'class' => 'all_checkboxes',
								'name' => 'select_all',
								'value' => '1',
							)
				). '<label for="select_all"><span></span><strong>'.lang('common_select_all').'</strong></label></li>';
			foreach($locations_to_use as $location_id => $location_name) 
			{
				$checkbox_options = array(
				'id' => 'reports_selected_location_ids'.$location_id,
				'class' => 'reports_selected_location_ids_checkboxes',
				'name' => 'location_ids[]',
				'value' => $location_id,
				'checked' => in_array($location_id, Report::get_selected_location_ids()),
			);
																
				echo '<li>'.form_checkbox($checkbox_options). '<label for="reports_selected_location_ids'.$location_id.'"><span></span>'.$location_name.'</label></li>';
			}
		?>
		</ul>
	</div>
	
</div>
<script>
$("#select_all").click(function(e)
{
	
	if(!$(this).prop('checked'))
	{
		$(".reports_selected_location_ids_checkboxes").prop('checked',false);
	}
	else
	{
		$(".reports_selected_location_ids_checkboxes").prop('checked', true);
		check_boxes();
	}
	
});
$('.reports_selected_location_ids_checkboxes').click(function()
{
	check_boxes();
});
check_boxes();
function check_boxes()
{
	var total_checkboxes = $(".reports_selected_location_ids_checkboxes").length;
	var checked_boxes = 0;
	$(".reports_selected_location_ids_checkboxes").each(function( index ) {
		if ($(this).prop('checked'))
		{
			checked_boxes++;
		}
	});

	if (checked_boxes == total_checkboxes)
	{
		$("#select_all").prop('checked', true);
	}
	else
	{
		$("#select_all").prop('checked', false);
	}
}
	
</script>
<?php } ?>