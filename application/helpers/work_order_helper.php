<?php

/*
Gets the html table to manage work orders.
*/
function get_work_orders_manage_table($orders,$controller)
{
	$CI =& get_instance();
	$CI->load->model('Employee');
	$table='<table class="table tablesorter table-hover" id="sortable_table">';	
	$columns_to_display = $CI->Employee->get_work_order_columns_to_display();

	$headers[] = array('label' => '<input type="checkbox" id="select_all" /><label for="select_all"><span></span></label>', 'sort_column' => '');

	$has_edit_permission = $CI->Employee->has_module_action_permission('work_orders','edit', $CI->Employee->get_logged_in_employee_info()->person_id);
	
	$controller_name=strtolower(get_class($CI));
	$params = $CI->session->userdata($controller_name.'_search_data') ? $CI->session->userdata($controller_name.'_search_data') : array('deleted' => 0);
	
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
			$table.='<th>'.lang('work_orders_collect_payment').'</th>';
			
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
	$table.=get_work_orders_manage_table_data_rows($orders,$controller);
	$table.='</tbody></table>';
	return $table;
}

/*
Gets the html data rows for the work orders.
*/
function get_work_orders_manage_table_data_rows($orders,$controller)
{
	$CI =& get_instance();
	$table_data_rows='';
	
	foreach($orders->result() as $order)
	{
		$table_data_rows.=get_work_order_data_row($order,$controller);
	}
	
	if($orders->num_rows()==0)
	{
		$table_data_rows.="<tr>
			<td colspan='13'><span class='col-md-12 text-center' ><span class='text-warning'>".lang('work_orders_no_work_orders')."</span></span></td>
		</tr>";
	}
		
	return $table_data_rows;
}

function work_order_status($status)
{
	$CI =& get_instance();
	
	return $CI->Work_order->get_status_name($CI->Work_order->get_status_info($status)->name);
}

function work_order_status_badge($status)
{
	$CI =& get_instance();	

	$status_info = $CI->Work_order->get_status_info($status);

	return '<div class="badge badge-work_order" style="background-color:'.$status_info->color.'">'.$CI->Work_order->get_status_name($status_info->name).'</div>';
}

function date_time_to_date($date_time)
{
	return date(get_date_format(), strtotime($date_time));
}

function get_work_order_data_row($order,$controller)
{
		$CI =& get_instance();	
		$controller_name=strtolower(get_class($CI));
		
		$params = $CI->session->userdata($controller_name.'_search_data') ? $CI->session->userdata($controller_name.'_search_data') : array('deleted' => 0);

		$table_data_row='<tr>';
		$table_data_row.="<td><input type='checkbox' id='order_$order->id' value='".$order->id."'/><label for='item_$order->id'><span></span></label></td>";		
		$displayable_columns = $CI->Employee->get_work_order_columns_to_display();
		$CI->load->helper('text');
		$CI->load->helper('date');
		$CI->load->helper('currency');
		
		$has_edit_permission = $CI->Employee->has_module_action_permission('work_orders','edit', $CI->Employee->get_logged_in_employee_info()->person_id);
		
   		$edit_sale_url = $order->suspended ? 'unsuspend' : 'change_sale';
		
		if($order->suspended > 0){
			$table_data_row.='<td>'.anchor("sales/$edit_sale_url/$order->sale_id",lang('work_orders_collect_payment'),array('title'=>lang('work_orders_collect_payment'),'class'=>'btn btn-primary btn-pay')).'</td>';
		}else{
			$table_data_row.='<td></td>';
		}
		
		
		if ($has_edit_permission && !$params['deleted'])
		{
			$table_data_row.='<td class="">'.anchor($controller_name."/view/$order->id", lang('common_edit'),array('class'=>' ','title'=>lang($controller_name.'_update'))).'</td>';		
		}
	
		foreach($displayable_columns as $column_id => $column_values)
		{
			$val = $order->{$column_id};
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
					$val = $format_function($val);					
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


?>