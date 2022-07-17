<?php

/*
Gets the html table to manage items.
*/
function get_orders_manage_table($orders,$controller)
{
	$CI =& get_instance();
	$CI->load->model('Employee');
	$table='<table class="table tablesorter table-hover" id="sortable_table">';	
	$columns_to_display = $CI->Employee->get_sale_order_columns_to_display();

	$headers[] = array('label' => '<input type="checkbox" id="select_all" /><label for="select_all"><span></span></label>', 'sort_column' => '');

	$has_edit_permission = $CI->Employee->has_module_action_permission('deliveries','edit', $CI->Employee->get_logged_in_employee_info()->person_id);
	
	$controller_name=strtolower(get_class($CI));
	$params = $CI->session->userdata($controller_name.'_orders_search_data') ? $CI->session->userdata($controller_name.'_orders_search_data') : array('deleted' => 0);
	
	if ($has_edit_permission && !$params['deleted'])
	{
		$headers[] = array('label' => lang('common_edit'), 'sort_column' => '');
	}

	foreach(array_values($columns_to_display) as $value)
	{

		$headers[] = H($value);
	}

	$table.='<thead><tr>';
	$count = 0;
	foreach($headers as $header)
	{
		$count++;
		$label = $header['label'];
		$sort_col = $header['sort_column'];
		if ($count == 1)
		{
			$table.="<th data-sort-column='$sort_col' class='leftmost'>$label</th>";
		}
		elseif ($count == count($headers))
		{
			$table.="<th data-sort-column='$sort_col'>$label</th>";
		}
		else
		{
			$table.="<th data-sort-column='$sort_col'>$label</th>";		
		}
	}
	$table.='</tr></thead><tbody>';
	$table.=get_orders_manage_table_data_rows($orders,$controller);
	$table.='</tbody></table>';
	return $table;
}

/*
Gets the html data rows for the items.
*/
function get_orders_manage_table_data_rows($orders,$controller)
{
	$CI =& get_instance();
	$table_data_rows='';
	
	foreach($orders->result() as $order)
	{
		$table_data_rows.=get_order_data_row($order,$controller);
	}
	
	if($orders->num_rows()==0)
	{
		$table_data_rows.="<tr>
			<td colspan='13'><span class='col-md-12 text-center' ><span class='text-warning'>".lang('deliveries_no_deliveries')."</span></span></td>
		</tr>";
	}
		
	return $table_data_rows;
}

function delivery_status($status)
{	
	if($status){
		$CI =& get_instance();	

		$status_info = $CI->Delivery->get_status_info($status);

		return $CI->Delivery->get_status_name($status_info->name);
	}
	return '';
}

function get_order_data_row($order,$controller)
{
		$CI =& get_instance();	
		$controller_name=strtolower(get_class($CI));
		
		$params = $CI->session->userdata($controller_name.'_orders_search_data') ? $CI->session->userdata($controller_name.'_orders_search_data') : array('deleted' => 0);

		$table_data_row='<tr>';
		$table_data_row.="<td><input type='checkbox' id='order_$order->id' value='".$order->id."'/><label for='item_$order->id'><span></span></label></td>";		
		$displayable_columns = $CI->Employee->get_sale_order_columns_to_display();
		$CI->load->helper('text');
		$CI->load->helper('date');
		$CI->load->helper('currency');
		
		$has_edit_permission = $CI->Employee->has_module_action_permission('deliveries','edit', $CI->Employee->get_logged_in_employee_info()->person_id);
		
		if ($has_edit_permission && !$params['deleted'])
		{
			$table_data_row.='<td class="">'.anchor($controller_name."/view/$order->id/2?redirect=deliveries", lang('common_edit'),array('class'=>' ','title'=>lang($controller_name.'_update'))).'</td>';		
		}	
		foreach($displayable_columns as $column_id => $column_values)
		{
			
			$val = $order->{$column_id};

			if($column_id == 'sale_id' && $val == ''){
				$table_data_row.='<td></td>';
				continue;
			}

			if (isset($column_values['format_function']))
			{
				if (isset($column_values['data_function']))
				{
					$data_function = $column_values['data_function'];
					$data = $data_function($order);
				}
				
				$format_function = $column_values['format_function'];
				
				if (isset($data))
				{
					$val = $format_function($val,$data);
				}
				else
				{
					if($column_id == "status" && $CI->config->item('delivery_color_based_on') == "category" && $order->category_color){
						$val = $format_function($val, $order->category_color);
					}else{
						$val = $format_function($val);
					}
									
				}
			}
			
			if (!isset($column_values['html']) || !$column_values['html'])
			{
				$val = H($val);
			}
			
			$table_data_row.='<td>'.$val.'</td>';
			//Unset for next round of the loop
			unset($data);
		}	
	
	$table_data_row.='</tr>';
	return $table_data_row;
}

function delivery_category_badge($category)
{
	if($category){
		$CI =& get_instance();	

		$cat_info = $CI->Delivery->get_category_info($category);
		return '<div class="badge badge-work_order" style="background-color:'.$cat_info->color.'">'.$cat_info->name.'</div>';
	}
	return '';
	
}
function delivery_status_badge($status)
{
	if($status){
		$CI =& get_instance();	

		$status_info = $CI->Delivery->get_status_info($status);
		return '<div class="badge badge-work_order" style="background-color:'.$status_info->color.'">'.$CI->Delivery->get_status_name($status_info->name).'</div>';
	}
	return '';
}
?>