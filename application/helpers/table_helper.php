<?php
/*
Gets the html table to manage people.
*/
function get_people_manage_table($people,$controller)
{
	$CI =& get_instance();
	$CI->load->model('Employee');
	$controller_name=strtolower(get_class($CI));
	$params = $CI->session->userdata($controller_name.'_search_data') ? $CI->session->userdata($controller_name.'_search_data') : array('deleted' => 0);
	$table='<table class="table tablesorter table-hover" id="sortable_table">';	
	
	
	if ($controller_name == 'customers')
	{
		$columns_to_display = $CI->Employee->get_customer_columns_to_display();
	}
	elseif($controller_name == 'suppliers')
	{
		$CI->load->model('Supplier');
		$columns_to_display = $CI->Employee->get_supplier_columns_to_display();		
	}
	elseif($controller_name == 'employees')
	{
		$CI->load->model('Employee');
		$columns_to_display = $CI->Employee->get_employee_columns_to_display();		
	}
		
	$headers[] = array('label' => '<input type="checkbox" id="select_all" /><label for="select_all"><span></span></label>', 'sort_column' => '');
	if(!$params['deleted'])
	{
		$headers[] = array('label' => lang('common_actions'), 'sort_column' => '');
	}
	
	
	foreach(array_values($columns_to_display) as $value)
	{
		$headers[] = H($value);
	}
	
	$headers[] = array('label' => '&nbsp;', 'sort_column' => '');
		
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
			$table.="<th data-sort-column='$sort_col' class='rightmost'>$label</th>";
		}
		else
		{
			$table.="<th data-sort-column='$sort_col'>$label</th>";		
		}
	}
	$table.='</tr></thead><tbody>';
	$table.=get_people_manage_table_data_rows($people,$controller);
	$table.='</tbody></table>';
	return $table;
}

/*
Gets the html data rows for the people.
*/
function get_people_manage_table_data_rows($people,$controller)
{
	$CI =& get_instance();
	$table_data_rows='';
	$controller_name=strtolower(get_class($CI));
	$params = $CI->session->userdata($controller_name.'_search_data') ? $CI->session->userdata($controller_name.'_search_data') : array('deleted' => 0);
	
	
	foreach($people->result() as $person)
	{
		$table_data_rows.=get_person_data_row($person,$controller);
	}
	
	if($people->num_rows()==0 && $controller_name != 'employees')
	{
		$table_data_rows.="<tr><td colspan='1000'><span class='col-md-12 text-center' ><span class='text-warning'>".lang('common_no_persons_to_display')."</span>";
		
		if(!$params['deleted'])
		{
			$table_data_rows.="&nbsp;&nbsp;<a class='btn btn-primary' href='". site_url($controller_name.'/excel_import?redirect=customers') ."'>". lang($controller_name.'_import_'.$controller_name)."</a>";
		}
		
		$table_data_rows.="</span></tr>";
	}
	elseif($people->num_rows()==0 && $controller_name == 'employees')
	{
		$table_data_rows.="<tr><td colspan='1000'><span class='col-md-12 text-center' ><span class='text-warning'>".lang('common_no_persons_to_display')."</span></span></tr>";		
	}
	
	return $table_data_rows;
}

function get_person_data_row($person,$controller)
{
	$CI =& get_instance();
	$CI->load->helper('people');
	
	$controller_name=strtolower(get_class($CI));
	$params = $CI->session->userdata($controller_name.'_search_data') ? $CI->session->userdata($controller_name.'_search_data') : array('deleted' => 0);
	
	$avatar_url=$person->image_id ?  app_file_url($person->image_id) : base_url('assets/assets/images/avatar-default.jpg');

	$table_data_row='<tr>';
	
	if ($controller_name =='customers')
	{
		$table_data_row.="<td><input type='checkbox' id='${controller_name}_$person->person_id' value='".$person->person_id."'/><label for='${controller_name}_$person->person_id'><span></span></label></td>";
		if(!$params['deleted'])
		{
		$table_data_row.='<td class="actions">'.
						'<div class="piluku-dropdown dropdown btn-group table_buttons upordown">
						  <a href="'.site_url($controller_name."/view/$person->person_id/2").'" role="button" class="btn btn-more edit_action">'.lang('common_edit').'</a>
							<button type="button" class="btn btn-more dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
								<i class="ion-more"></i>
							</button>
							<ul class="dropdown-menu dropdown-menu-left " role="menu">';
			
		$table_data_row.= '<li>'. anchor($controller_name."/redeem_series/$person->person_id", '<i class="ion-cash"></i> ' .  lang('common_redeem_series') , array('class'=>'','title'=>lang('common_redeem_series'))).'</li>';
		

		$table_data_row.= '</ul>
						</div>'
			.'</td>';
		}
	}
	elseif ($controller_name != 'employees')
	{
		$table_data_row.="<td><input type='checkbox' id='${controller_name}_$person->person_id' value='".$person->person_id."'/><label for='${controller_name}_$person->person_id'><span></span></label></td>";
		if(!$params['deleted'])
		{						  
			$table_data_row.='<td class=""><div class="piluku-dropdown dropdown btn-group table_buttons upordown">'.anchor($controller_name."/view/$person->person_id/2	", lang('common_edit') ,array('class'=>'btn btn-more edit_action','title'=>lang($controller_name.'_update'))).'</div></li>'.'</td>';	
		}
	}
	else
	{
		$table_data_row.="<td><input type='checkbox' id='item_$person->person_id' value='".$person->person_id."'/><label for='item_$person->person_id'><span></span></label></td>";
		if(!$params['deleted'])
		{
			
								
			$table_data_row.='<td class="actions">'.
							'<div class="piluku-dropdown dropdown btn-group table_buttons upordown">
							  <a href="'.site_url($controller_name."/view/$person->person_id?redirect=items").'" role="button" class="btn btn-more edit_action">'.lang('common_edit').'</a>
								<button type="button" class="btn btn-more dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
									<i class="ion-more"></i>
								</button>
								<ul class="dropdown-menu dropdown-menu-left " role="menu">';
							
									$table_data_row.= '<li>'. anchor($controller_name."/clone_employee/$person->person_id/", '<i class="ion-ios-browsers-outline clone-item"></i> ' . lang('common_clone') .' ' . lang('common_employee'), array('class'=>'clone_manage_table','title'=>lang('common_clone'))).'</li>';
							
								$table_data_row.= '</ul>
							</div>'
				.'</td>';	
		}
		
	}	
	
	if ($controller_name == 'customers')
	{
		$displayable_columns = $CI->Employee->get_customer_columns_to_display();
	}
	elseif($controller_name == 'suppliers')
	{
		$CI->load->model('Supplier');
		$displayable_columns = $CI->Employee->get_supplier_columns_to_display();		
	}
	elseif($controller_name == 'employees')
	{
		$CI->load->model('Employee');
		$displayable_columns = $CI->Employee->get_employee_columns_to_display();		
	}
		
		
		$CI->load->helper('text');
		$CI->load->helper('date');
		$CI->load->helper('currency');
		foreach($displayable_columns as $column_id => $column_values)
		{
			$val = $person->{$column_id};
			if (isset($column_values['format_function']))
			{
				if (isset($column_values['data_function']))
				{
					$data_function = $column_values['data_function'];
					$data = $data_function($person);
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
	if ($avatar_url)
	{	
		$table_data_row.="<td><a href='$avatar_url' class='rollover'><img src='".$avatar_url."' alt='".H($person->full_name)."' class='img-polaroid' width='45' /></a></td>";
	}
	
	$table_data_row.='</tr>';
	return $table_data_row;	
}



/*
Gets the html table to manage items.
*/
function get_items_manage_table($items,$controller)
{
	$CI =& get_instance();
	$CI->load->model('Employee');
	
	$controller_name=strtolower(get_class($CI));
	$params = $CI->session->userdata($controller_name.'_search_data') ? $CI->session->userdata($controller_name.'_search_data') : array('deleted' => 0);
	
	$table='<table class="table tablesorter table-hover" id="sortable_table">';	
	$columns_to_display = $CI->Employee->get_item_columns_to_display();

	$headers[] = array('label' => '<input type="checkbox" id="select_all" /><label for="select_all"><span></span></label>', 'sort_column' => '');
	if(!$params['deleted'])
	{
		$headers[] = array('label' => lang('common_actions'), 'sort_column' => '');
	}
	
	
	foreach(array_values($columns_to_display) as $value)
	{
		$headers[] = H($value);
	}
	
	$headers[] = array('label' => '&nbsp;', 'sort_column' => '');
		
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
			$table.="<th data-sort-column='$sort_col' class='rightmost'>$label</th>";
		}
		else
		{
			$table.="<th data-sort-column='$sort_col'>$label</th>";		
		}
	}
	$table.='</tr></thead><tbody>';
	$table.=get_items_manage_table_data_rows($items,$controller);
	$table.='</tbody></table>';
	return $table;
}

/*
Gets the html data rows for the items.
*/
function get_items_manage_table_data_rows($items,$controller)
{
	$CI =& get_instance();
	
	$controller_name=strtolower(get_class($CI));
	$params = $CI->session->userdata($controller_name.'_search_data') ? $CI->session->userdata($controller_name.'_search_data') : array('deleted' => 0);
	
	$table_data_rows='';
	
	foreach($items->result() as $item)
	{
		$table_data_rows.=get_item_data_row($item,$controller);
	}
	
	if($items->num_rows()==0)
	{
		$table_data_rows.="<tr>
			<td colspan='1000'><span class='col-md-12 text-center' ><span class='text-warning'>".lang('items_no_items_to_display')."</span>";
		
		if(!$params['deleted'])
		{
			$table_data_rows.="&nbsp;&nbsp;<a class='btn btn-primary' href='". site_url('items/excel_import?redirect=items') ."'>". lang('items_import_items')."</a>";
		}
		
		$table_data_rows.="</span></td></tr>";
	}
		
	return $table_data_rows;
}

function get_item_data_row($item,$controller)
{
	$CI =& get_instance();
	$low_inventory_class = "";
		
	$reorder_level = $item->location_reorder_level ? $item->location_reorder_level : $item->reorder_level;

	$controller_name=strtolower(get_class($CI));
	$params = $CI->session->userdata($controller_name.'_search_data') ? $CI->session->userdata($controller_name.'_search_data') : array('deleted' => 0);
	$has_edit_quantity_permission = $CI->Employee->has_module_action_permission('items','edit_quantity', $CI->Employee->get_logged_in_employee_info()->person_id);
	
	$avatar_url=$item->image_id ?  cacheable_app_file_url($item->image_id) : base_url('assets/assets/images/default.png');

	$table_data_row='<tr>';
	$table_data_row.="<td><input type='checkbox' id='item_$item->item_id' value='".$item->item_id."'/><label for='item_$item->item_id'><span></span></label></td>";
	
	if(!$params['deleted'])
	{
					
		$table_data_row.='<td class="actions">'.
						'<div class="piluku-dropdown dropdown btn-group table_buttons upordown">
		  				 <a href="'.site_url($controller_name."/view/$item->item_id?redirect=items").'" role="button" class="btn btn-more edit_action">'.lang('common_edit').'</a>
	  <a href="'.site_url($controller_name."/clone_item/$item->item_id?redirect=items").'" role="button" class="clone_manage_table btn btn-more edit_action">'.lang('common_clone').'</a>
							<button type="button" class="btn btn-more dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
								<i class="ion-more"></i>
							</button>
							<ul class="dropdown-menu dropdown-menu-left " role="menu">';
			
		if ($has_edit_quantity_permission)
		{			
			$table_data_row.= '<li>'. anchor($controller_name."/inventory/$item->item_id?redirect=items", '<i class="ion-android-clipboard"></i> ' .  lang('items_edit_inventory') , array('class'=>'','title'=>lang('items_edit_inventory'))).'</li>';
		}
		$table_data_row.= '<li>'. anchor($controller_name."/pricing/$item->item_id?redirect=items", '<i class="ion-cash"></i> ' .  lang('items_edit_pricing') , array('class'=>'','title'=>lang('items_edit_inventory'))).'</li>';
		
		$table_data_row.= '<li>'. anchor($controller_name."/barcodes/$item->item_id?redirect=items", '<i class="ion-android-print"></i> ' .  lang('common_print') .' ' . lang('common_barcodes') , array('class'=>'','title'=>lang('common_barcodes'))).'</li>';

		$table_data_row.= '</ul>
						</div>'
			.'</td>';
	}
							
		$displayable_columns = $CI->Employee->get_item_columns_to_display();
		$CI->load->helper('text');
		$CI->load->helper('date');
		$CI->load->helper('currency');
		foreach($displayable_columns as $column_id => $column_values)
		{
			if (property_exists($item,$column_id))
			{
				$val = $item->{$column_id};
			}
			
			if (isset($column_values['format_function']))
			{
				if (isset($column_values['data_function']))
				{
					$data_function = $column_values['data_function'];
					$data = $data_function($item);
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
	if ($avatar_url)
	{	
		$table_data_row.="<td><a href='$avatar_url' class='rollover'><img src='".$avatar_url."' alt='".H($item->name)."' class='img-polaroid' width='45' /></a></td>";
	}
	
	$table_data_row.='</tr>';
	return $table_data_row;
}


/*
Gets the html table to manage items.
*/
function get_suspended_sales_manage_table($items,$controller)
{
	$CI =& get_instance();
	$CI->load->model('Employee');
	$lang_url = base_url().'index.php/home/datatable_language';
	$controller_name=strtolower(get_class($CI));
	
	$table='<table class="table table-bordered table-striped table-hover data-table" id="dTable">';	
	$columns_to_display = $CI->Employee->get_suspended_sales_columns_to_display();
	
	$headers[] = array('label' => '<input type="checkbox" id="select_all" /><label for="select_all"><span></span></label>', 'sort_column' => '');

	foreach(array_values($columns_to_display) as $value)
	{
		$headers[] = H($value);
	}
	
	$headers[] = array('label' => lang('common_unsuspend'), 'sort_column' => '');
	$headers[] = array('label' => lang('sales_receipt'), 'sort_column' => '');
	$headers[] = array('label' => lang('common_email_receipt'), 'sort_column' => '');
	
	if ($CI->Employee->has_module_action_permission('sales', 'delete_suspended_sale', $CI->Employee->get_logged_in_employee_info()->person_id)){
		$headers[] = array('label' => lang('common_delete'), 'sort_column' => '');
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
			$table.="<th data-sort-column='$sort_col' class='rightmost'>$label</th>";
		}
		else
		{
			$table.="<th data-sort-column='$sort_col'>$label</th>";		
		}
	}
	$table.='</tr></thead><tbody>';
	$table.=get_suspended_sales_manage_table_data_rows($items,$controller);
	$table.='</tbody></table>';

	$number_per_page = $CI->config->item('number_of_items_per_page') ? (int)$CI->config->item('number_of_items_per_page') : 20;
	$dropdown_values = [10, $number_per_page, 25, 50, 100];
	asort($dropdown_values);
	
	array_push($dropdown_values, -1);
	$dropdown_values = array_values(array_unique($dropdown_values));

	$dropdown_option = [10, $number_per_page, 25, 50, 100];
	asort($dropdown_option);
	array_push($dropdown_option, "All");
	$dropdown_option = array_values(array_unique($dropdown_option));

	$length_dropdown = [$dropdown_values, $dropdown_option];
	$table_options = array(
		"sPaginationType" => "bootstrap",
		"bSort" => false,
		"bLengthChange" => true,
		"iDisplayLength" => $number_per_page,
		"aLengthMenu" => $length_dropdown,
		"bStateSave" => true,
		"oLanguage" => array(
			"sUrl" => $lang_url
		),
	);

	$dt_option = json_encode($table_options, JSON_UNESCAPED_SLASHES);

	$table.='<script type="text/javascript">$(document).ready(function(){$("#dTable").dataTable('.$dt_option.');});</script>';
	return $table;
}

/*
Gets the html data rows for the items.
*/
function get_suspended_sales_manage_table_data_rows($items,$controller)
{
	$CI =& get_instance();
	
	$controller_name=strtolower(get_class($CI));
	
	$table_data_rows='';
	$items = json_decode(json_encode($items));
	foreach($items as $item)
	{
		$table_data_rows.=get_suspended_sales_data_row($item,$controller);
	}
	/*
	if(empty($items))
	{
		$table_data_rows.="<tr><td colspan='1000'><span class='col-md-12 text-center' ><span class='text-warning'>".lang('common_not_found')."</span>";
		$table_data_rows.="</span></td></tr>";
	}
	*/
	return $table_data_rows;
}

function get_suspended_sales_data_row($item,$controller)
{
	$CI =& get_instance();
	$CI->load->model('Customer');
	$low_inventory_class = "";

	$controller_name=strtolower(get_class($CI));


	$table_data_row='<tr>';

		$table_data_row.="<td><input type='checkbox' id='item_$item->sale_id' value='".$item->sale_id."'/><label for='item_$item->sale_id'><span></span></label></td>";
							
		$displayable_columns = $CI->Employee->get_suspended_sales_columns_to_display();
		$CI->load->helper('text');
		$CI->load->helper('date');
		$CI->load->helper('currency');
		foreach($displayable_columns as $column_id => $column_values)
		{
			if (property_exists($item,$column_id))
			{
				$val = $item->{$column_id};
			}
			
			if (isset($column_values['format_function']))
			{
				if (isset($column_values['data_function']))
				{
					$data_function = $column_values['data_function'];
					$data = $data_function($item);
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
			
			if($column_id == 'sale_id'){
				$val = ($CI->config->item('sale_prefix') ? $CI->config->item('sale_prefix') : 'POS' ). ' '.$item->sale_id;
			}
			
			if($column_id == 'sale_time'){
				$val = date(get_date_format(). ' @ '.get_time_format(),strtotime($item->sale_time));
			}
			
			if($column_id == 'sale_type_name'){
				$val = $item->suspended == 1  ? ($CI->config->item('user_configured_layaway_name') ? $CI->config->item('user_configured_layaway_name') : lang('common_layaway')) : ($item->suspended > 2 ? $item->sale_type_name : lang('common_estimate'));
			}
			
			if($column_id == 'customer_id'){
				if ($item->customer_id){
					if($item->company_name) {
						$val = $item->first_name. ' '. $item->last_name.' ('.$item->company_name.')';
					}
					else {
						$val =  $item->first_name. ' '. $item->last_name;
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
		
		$table_data_row.='<td>'; 
		
		if ($CI->Employee->has_module_action_permission('sales', 'edit_suspended_sale', $CI->Employee->get_logged_in_employee_info()->person_id))
		{
			$table_data_row.= form_open('sales/unsuspend');
			$table_data_row.= form_hidden('suspended_sale_id', $item->sale_id);
			
			$table_data_row.='<input type="submit" name="submit" value="'.lang('common_unsuspend').'" id="submit_unsuspend" class="btn btn-primary" />';
			$table_data_row.= form_close();
		}
		$table_data_row.='</td>';
		
		$table_data_row.='<td>';
			$table_data_row.= form_open('sales/receipt/'.$item->sale_id, array('method'=>'get', 'class' => 'form_receipt_suspended_sale'));
			$table_data_row.='<input type="submit" name="submit" value="'.lang('common_recp').'" class="btn btn-primary" />';
			$table_data_row.=form_close();
		$table_data_row.='</td>';
		
		$table_data_row.='<td>';
		if ($item->email) 
		{
			$table_data_row .= form_open('sales/email_receipt/'.$item->sale_id, array('method'=>'get', 'class' => 'form_email_receipt_suspended_sale'));
				$table_data_row .= '<input type="submit" name="submit" value="'.lang('common_email').'" class="btn btn-primary" />';
			$table_data_row .= form_close();
		}
		
		$table_data_row .= '</td>';
		if ($CI->Employee->has_module_action_permission('sales', 'delete_suspended_sale', $CI->Employee->get_logged_in_employee_info()->person_id)){
			$table_data_row .= '<td>';
			 	$table_data_row .=  form_open('sales/delete_suspended_sale', array('class' => 'form_delete_suspended_sale'));
				$table_data_row .=  form_hidden('suspended_sale_id', $item->sale_id);
				$table_data_row .= '<input type="submit" name="submitf" value="'.lang('common_delete').'" id="submit_delete" class="btn btn-danger">';
				$table_data_row .= form_close();
			$table_data_row .= '</td>';
		}
	
	$table_data_row.='</tr>';
	return $table_data_row;
}


/*
Gets the html table to manage items.
*/
function get_suspended_receivings_manage_table($items,$controller)
{
	$CI =& get_instance();
	$CI->load->model('Employee');
	$lang_url = base_url().'index.php/home/datatable_language';
	
	$controller_name=strtolower(get_class($CI));
	
	$table='<table class="table table-bordered table-striped table-hover data-table" id="dTable">';	
	$columns_to_display = $CI->Employee->get_suspended_receivings_columns_to_display();
	
	$headers[] = array('label' => '<input type="checkbox" id="select_all" /><label for="select_all"><span></span></label>', 'sort_column' => '');

	foreach(array_values($columns_to_display) as $value)
	{
		$headers[] = H($value);
	}

	$headers[] = array('label' => lang('common_unsuspend'), 'sort_column' => '');
	$headers[] = array('label' => lang('receivings_receipt'), 'sort_column' => '');
	$headers[] = array('label' => lang('common_email_receipt'), 'sort_column' => '');
	$headers[] = array('label' => lang('common_delete'), 'sort_column' => '');
		
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
			$table.="<th data-sort-column='$sort_col' class='rightmost'>$label</th>";
		}
		else
		{
			$table.="<th data-sort-column='$sort_col'>$label</th>";		
		}
	}
	$table.='</tr></thead><tbody>';
	$table.=get_suspended_receivings_manage_table_data_rows($items,$controller);
	$table.='</tbody></table>';
	$number_per_page = $CI->config->item('number_of_items_per_page') ? (int)$CI->config->item('number_of_items_per_page') : 20;
	$dropdown_values = [10, $number_per_page, 25, 50, 100];
	asort($dropdown_values);
	array_push($dropdown_values, -1);
	$dropdown_values = array_values(array_unique($dropdown_values));
	
	$dropdown_option = [10, $number_per_page, 25, 50, 100];
	asort($dropdown_option);
	array_push($dropdown_option, "All");
	$dropdown_option = array_values(array_unique($dropdown_option));
	$length_dropdown = [$dropdown_values, $dropdown_option];
	
	$table_options = array(
		"sPaginationType" => "bootstrap",
		"bSort" => false,
		"bLengthChange" => true,
		"iDisplayLength" => $number_per_page,
		"aLengthMenu" => $length_dropdown,
		"bStateSave" => true,
		"oLanguage" => array(
			"sUrl" => $lang_url
		),
	);

	$dt_option = json_encode($table_options, JSON_UNESCAPED_SLASHES);

	$table.='<script type="text/javascript">$(document).ready(function(){$("#dTable").dataTable('.$dt_option.');});</script>';
	return $table;
}

/*
Gets the html data rows for the items.
*/
function get_suspended_receivings_manage_table_data_rows($items,$controller)
{
	$CI =& get_instance();
	
	$controller_name=strtolower(get_class($CI));
	
	$table_data_rows='';
	$items = json_decode(json_encode($items));
	foreach($items as $item)
	{
		$table_data_rows.=get_suspended_receivings_data_row($item,$controller);
	}
	/*
	if(empty($items))
	{
		$table_data_rows.="<tr><td colspan='1000'><span class='col-md-12 text-center' ><span class='text-warning'>".lang('common_not_found')."</span>";
		$table_data_rows.="</span></td></tr>";
	}
	*/
	return $table_data_rows;
}

function get_suspended_receivings_data_row($item,$controller)
{
	$CI =& get_instance();
	$CI->load->model('Supplier');
	$low_inventory_class = "";

	$controller_name=strtolower(get_class($CI));


	$table_data_row='<tr>';
	$table_data_row.="<td><input type='checkbox' id='item_$item->receiving_id' value='".$item->receiving_id."'/><label for='item_$item->receiving_id'><span></span></label></td>";
							
		$displayable_columns = $CI->Employee->get_suspended_receivings_columns_to_display();
		$CI->load->helper('text');
		$CI->load->helper('date');
		$CI->load->helper('currency');
		foreach($displayable_columns as $column_id => $column_values)
		{
			if (property_exists($item,$column_id))
			{
				$val = $item->{$column_id};
			}
			
			if (isset($column_values['format_function']))
			{
				if (isset($column_values['data_function']))
				{
					$data_function = $column_values['data_function'];
					$data = $data_function($item);
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
			
			if($column_id == 'receiving_id'){
				$val = 'RECV '.$item->receiving_id;
			}
			
			if($column_id == 'receiving_time'){
				$val = date(get_date_format(). ' @ '.get_time_format(),strtotime($item->receiving_time));
			}
			
			if($column_id == 'supplier_id'){
				if ($item->supplier_id){
					if($item->company_name) {
						$val = $item->first_name. ' '. $item->last_name.' ('.$item->company_name.')';
					}
					else {
						$val =  $item->first_name. ' '. $item->last_name;
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
		
		$table_data_row.='<td>'; 
			$table_data_row.= form_open('receivings/unsuspend');
			$table_data_row.= form_hidden('suspended_receiving_id', $item->receiving_id);
			
			$table_data_row.='<input type="submit" name="submit" value="'.lang('common_unsuspend').'" id="submit_unsuspend" class="btn btn-primary" />';
			$table_data_row.= form_close();
		$table_data_row.='</td>';
		
		$table_data_row.='<td>';
			$table_data_row.= form_open('receivings/receipt/'.$item->receiving_id, array('method'=>'get', 'class' => 'form_receipt_suspended_recv'));
			$table_data_row.='<input type="submit" name="submit" value="'.lang('common_recp').'" class="btn btn-primary" />';
			$table_data_row.=form_close();
		$table_data_row.='</td>';
		
		$table_data_row.='<td>';
		if ($item->email) 
		{			
			$table_data_row .= form_open('receivings/email_receipt/'.$item->receiving_id, array('method'=>'get', 'class' => 'form_email_receipt_suspended_recv'));
				$table_data_row .= '<input type="submit" name="submit" value="'.($item->is_po ? lang('receivings_email_po') : lang('common_email_receipt')).'" class="btn btn-primary" />';
			$table_data_row .= form_close();
		}
		
		$table_data_row .= '</td>';
			$table_data_row .= '<td>';
			 	$table_data_row .=  form_open('receivings/delete_suspended_receiving', array('class' => 'form_delete_suspended_recv'));
				$table_data_row .=  form_hidden('suspended_receiving_id', $item->receiving_id);
				$table_data_row .= '<input type="submit" name="submitf" value="'.lang('common_delete').'" id="submit_delete" class="btn btn-danger">';
				$table_data_row .= form_close();
			$table_data_row .= '</td>';
	
	$table_data_row.='</tr>';
	return $table_data_row;
}
 

/*
Gets the html table to manage items.
*/
function get_locations_manage_table($locations,$controller)
{
	$CI =& get_instance();
	
	$controller_name=strtolower(get_class($CI));
	$params = $CI->session->userdata($controller_name.'_search_data') ? $CI->session->userdata($controller_name.'_search_data') : array('deleted' => 0);
	
	$table='<table class="tablesorter table table-hover" id="sortable_table">';	
	$headers[] = array('label' => '<input type="checkbox" id="select_all" /><label for="select_all"><span></span></label>', 'sort_column' => '');
	
	if(!$params['deleted'])
	{
		$headers[] = array('label' => lang('common_edit'), 'sort_column' => '');
	}
	
	$headers[] = array('label' => lang('locations_location_id'), 'sort_column' => 'location_id');
	$headers[] = array('label' => lang('locations_name'), 'sort_column' => 'name');
	$headers[] = array('label' => lang('locations_address'), 'sort_column' => 'address');
	$headers[] = array('label' => lang('locations_phone'), 'sort_column' => 'phone');
	$headers[] = array('label' => lang('locations_email'), 'sort_column' => 'email');
		
		
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
			$table.="<th data-sort-column='$sort_col' class='rightmost'>$label</th>";
		}
		else
		{
			$table.="<th data-sort-column='$sort_col'>$label</th>";		
		}
	}
	
	$table.='</tr></thead><tbody>';
	$table.=get_locations_manage_table_data_rows($locations,$controller);
	$table.='</tbody></table>';
	return $table;
}

/*
Gets the html data rows for the items.
*/
function get_locations_manage_table_data_rows($locations,$controller)
{
	$CI =& get_instance();
	$table_data_rows='';
	
	foreach($locations->result() as $location)
	{
		$table_data_rows.=get_location_data_row($location,$controller);
	}
	
	if($locations->num_rows()==0)
	{
		$table_data_rows.="<tr><td colspan='1000'><span class='col-md-12 text-center text-warning' >".lang('locations_no_locations_to_display')."</span></td></tr>";
	}
	
	return $table_data_rows;
}

function get_location_data_row($location,$controller)
{
	$CI =& get_instance();
	
	$controller_name=strtolower(get_class($CI));
	$params = $CI->session->userdata($controller_name.'_search_data') ? $CI->session->userdata($controller_name.'_search_data') : array('deleted' => 0);
	
	$table_data_row='<tr>';
	$table_data_row.="<td><input type='checkbox' id='location_$location->location_id' value='".$location->location_id."'/><label for='location_$location->location_id'><span></span></label></td>";
	
	if(!$params['deleted'])
	{
		$table_data_row.='<td>'.anchor($controller_name."/view/$location->location_id/2", lang('common_edit'),array('class'=>' ','title'=>lang($controller_name.'_update'))).'</td>';
	}
	
	$table_data_row.='<td>'.$location->location_id.'</td>';
	$table_data_row.='<td>'.H($location->name).'</td>';
	$table_data_row.='<td>'.H($location->address).'</td>';
	$table_data_row.='<td>'.H($location->phone).'</td>';
	$table_data_row.='<td>'.H($location->email).'</td>';
	
	$table_data_row.='</tr>';
	return $table_data_row;
}

/*
Gets the html table to manage giftcards.
*/
function get_giftcards_manage_table( $giftcards, $controller )
{
	$CI =& get_instance();
	$controller_name=strtolower(get_class($CI));
	$params = $CI->session->userdata($controller_name.'_search_data') ? $CI->session->userdata($controller_name.'_search_data') : array('deleted' => 0);
	
	$table='<table class="tablesorter table table-hover" id="sortable_table">';	
	$headers[] = array('label' => '<input type="checkbox" id="select_all" /><label for="select_all"><span></span></label>', 'sort_column' => '');
	
	if(!$params['deleted'])
	{
		$headers[] = array('label' => lang('common_edit'), 'sort_column' => '');
	}
	
	$headers[] = array('label' => lang('common_giftcards_giftcard_number'), 'sort_column' => 'giftcard_number');
	$headers[] = array('label' => lang('common_giftcards_card_value'), 'sort_column' => 'value');
	$headers[] = array('label' => lang('common_description'), 'sort_column' => 'description');
	$headers[] = array('label' => lang('common_customer_name'), 'sort_column' => 'last_name');
	$headers[] = array('label' => lang('common_active').'/'.lang('common_inactive'), 'sort_column' => 'inactive');
	
	if ($CI->Location->get_info_for_key('integrated_gift_cards'))
	{
		$headers[] = array('label' => lang('common_integrated_gift_card'), 'sort_column' => 'inactive');
	}
	
	$headers[] = array('label' => lang('common_clone'), 'sort_column' => '');
		
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
			$table.="<th data-sort-column='$sort_col' class='rightmost'>$label</th>";
		}
		else
		{
			$table.="<th data-sort-column='$sort_col'>$label</th>";		
		}
	}
	
	$table.='</tr></thead><tbody>';
	$table.=get_giftcards_manage_table_data_rows( $giftcards, $controller );
	$table.='</tbody></table>';
	return $table;
}

/*
Gets the html data rows for the giftcard.
*/
function get_giftcards_manage_table_data_rows( $giftcards, $controller )
{
	$CI =& get_instance();
	$table_data_rows='';
	
	foreach($giftcards->result() as $giftcard)
	{
		$table_data_rows.=get_giftcard_data_row( $giftcard, $controller );
	}
	
	if($giftcards->num_rows()==0)
	{
		$table_data_rows.="<tr><td colspan='1000'><span class='col-md-12 text-center' ><span class='text-warning'>".lang('giftcards_no_giftcards_to_display')."</span>&nbsp;&nbsp;<a class='btn btn-primary' href='". site_url('giftcards/excel_import') ."'>". lang('giftcards_import_giftcards')."</a></span></tr>";
	}
	
	return $table_data_rows;
}

function get_giftcard_data_row($giftcard,$controller)
{
	$CI =& get_instance();
	$controller_name=strtolower(get_class($CI));
	$params = $CI->session->userdata($controller_name.'_search_data') ? $CI->session->userdata($controller_name.'_search_data') : array('deleted' => 0);
	
	$link = site_url('reports/generate/detailed_'.$controller_name.'?customer_id='.$giftcard->customer_id.'&export_excel=0&giftcard_number='.$giftcard->giftcard_number);
	$cust_info = $CI->Customer->get_info($giftcard->customer_id);
	
	$table_data_row='<tr>';
	
	if (!$giftcard->integrated_gift_card)
	{
		$table_data_row.="<td><input type='checkbox' id='giftcard_$giftcard->giftcard_id' value='".$giftcard->giftcard_id."'/><label for='giftcard_$giftcard->giftcard_id'><span></span></label></td>";
	}
	else
	{
		$table_data_row.="<td>&nbsp;</td>";
	}
	
	if(!$params['deleted'])
	{
		$table_data_row.='<td>'.anchor($controller_name."/view/$giftcard->giftcard_id/2	", lang('common_edit'),array('class'=>' ','title'=>lang($controller_name.'_update'))).'</td>';
	}
	
	$table_data_row.='<td>'.H($giftcard->giftcard_number).'</td>';
	$table_data_row.='<td>'.to_currency(H($giftcard->value), 10).'</td>';
	$table_data_row.='<td>'.H($giftcard->description).'</td>';
	$table_data_row.='<td><a target="blank" class="underline" href="'.$link.'">'.H($cust_info->first_name). ' '.H($cust_info->last_name).'</a></td>';
	$table_data_row.='<td>'.($giftcard->inactive ? lang('common_inactive') : lang('common_active')).'</td>';
	
	if ($CI->Location->get_info_for_key('integrated_gift_cards'))
	{
		$table_data_row.='<td>'.($giftcard->integrated_gift_card ? lang('common_yes') : lang('common_no')).'</td>';
	}
	
	if (!$giftcard->integrated_gift_card)
	{
		$table_data_row.='<td class="rightmost">'.anchor($controller_name."/clone_giftcard/$giftcard->giftcard_id", lang('common_clone'),array('class'=>'clone_manage_table','title'=>lang('common_clone'))).'</td>';			
	}
	else
	{
		$table_data_row.="<td>&nbsp;</td>";
	}
	$table_data_row.='</tr>';
	return $table_data_row;
}

/*
Gets the html table to manage item kits.
*/
function get_item_kits_manage_table( $item_kits, $controller )
{
	$CI =& get_instance();
	$CI->load->model('Employee');
	
	$controller_name=strtolower(get_class($CI));
	$params = $CI->session->userdata($controller_name.'_search_data') ? $CI->session->userdata($controller_name.'_search_data') : array('deleted' => 0);
	
	$table='<table class="table tablesorter table-hover" id="sortable_table">';	
	$columns_to_display = $CI->Employee->get_item_kit_columns_to_display();

	$headers[] = array('label' => '<input type="checkbox" id="select_all" /><label for="select_all"><span></span></label>', 'sort_column' => '');
	
	if(!$params['deleted'])
	{
		$headers[] = array('label' => lang('common_actions'), 'sort_column' => '');
	}
	
	foreach(array_values($columns_to_display) as $value)
	{
		$headers[] = H($value);
	}
	
	$headers[] = array('label' => '&nbsp;', 'sort_column' => '');
		
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
			$table.="<th data-sort-column='$sort_col' class='rightmost'>$label</th>";
		}
		else
		{
			$table.="<th data-sort-column='$sort_col'>$label</th>";		
		}
	}
	$table.='</tr></thead><tbody>';
	$table.=get_item_kits_manage_table_data_rows($item_kits,$controller);
	$table.='</tbody></table>';
	return $table;
}

/*
Gets the html data rows for the item kits.
*/
function get_item_kits_manage_table_data_rows( $item_kits, $controller )
{
	$CI =& get_instance();
	
	$table_data_rows='';
	
	foreach($item_kits->result() as $item_kit)
	{
		$table_data_rows.=get_item_kit_data_row( $item_kit, $controller );
	}
	
	if($item_kits->num_rows()==0)
	{
		$table_data_rows.="<tr><td colspan='1000'><span class='col-md-12 text-center text-warning' >".lang('item_kits_no_item_kits_to_display')."</span></td></tr>";
	}
	
	return $table_data_rows;
}

function get_item_kit_data_row($item_kit,$controller)
{
	$CI =& get_instance();
	$controller_name=strtolower(get_class($CI));
	$params = $CI->session->userdata($controller_name.'_search_data') ? $CI->session->userdata($controller_name.'_search_data') : array('deleted' => 0);
	
	$controller_name=strtolower(get_class($CI));
	
	$has_cost_price_permission = $CI->Employee->has_module_action_permission('item_kits','see_cost_price', $CI->Employee->get_logged_in_employee_info()->person_id);
		
	$avatar_url=$item_kit->main_image_id ?  cacheable_app_file_url($item_kit->main_image_id) : base_url('assets/assets/images/default.png');
		
	$table_data_row ='<tr>';
	$table_data_row.="<td><input type='checkbox' id='item_kit_$item_kit->item_kit_id' value='".$item_kit->item_kit_id."'/><label for='item_kit_$item_kit->item_kit_id'><span></span></label></td>";
	
	if(!$params['deleted'])
	{
		
							
		$table_data_row.='<td class="actions">'.
						'<div class="piluku-dropdown dropdown btn-group table_buttons upordown">
						 <a href="'.site_url($controller_name."/view/$item_kit->item_kit_id?redirect=item_kits").'" role="button" class="btn btn-more edit_action">'.lang('common_edit').'</a>
						<button type="button" class="btn btn-more dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
							<span class="ion-more"></span>
						</button>
						<ul class="dropdown-menu dropdown-menu-left " role="menu">';
						
						$table_data_row.='<li>'. anchor($controller_name."/pricing/$item_kit->item_kit_id?redirect=item_kits/", '<i class="ion-cash"></i> ' . lang('common_edit').' ' . lang('common_pricing') ,array('class'=>' ','title'=>lang($controller_name.'_update'))).'</li>';

						$table_data_row.= '<li>'. anchor($controller_name."/clone_item_kit/$item_kit->item_kit_id", '<i class="ion-ios-browsers-outline"></i> ' . lang('common_clone') .' ' . lang('common_item_kit'), array('class'=>'clone_manage_table','title'=>lang('common_clone'))).'</li>';
						
						$table_data_row.= '</ul>
					</div>'
		.'</td>';
	}
		
	$displayable_columns = $CI->Employee->get_item_kit_columns_to_display();
	$CI->load->helper('text');
	$CI->load->helper('date');
	$CI->load->helper('currency');
	foreach($displayable_columns as $column_id => $column_values)
	{
		$val = $item_kit->{$column_id};
		if (isset($column_values['format_function']))
		{
			if (isset($column_values['data_function']))
			{
				$data_function = $column_values['data_function'];
				$data = $data_function($item_kit);
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
		
	if ($avatar_url)
	{	
		$table_data_row.="<td><a href='$avatar_url' class='rollover'><img src='".$avatar_url."' alt='".H($item_kit->name)."' class='img-polaroid' width='45' /></a></td>";
	}
	
	$table_data_row.='</tr>';
	return $table_data_row;
}


function get_expenses_manage_table($expenses,$controller)
{
	$CI =& get_instance();
	$controller_name=strtolower(get_class($CI));
	$params = $CI->session->userdata($controller_name.'_search_data') ? $CI->session->userdata($controller_name.'_search_data') : array('deleted' => 0);
	
	$table='<table class="tablesorter table table-hover" id="sortable_table">';

	$headers[] = array('label' => '<input type="checkbox" id="select_all" /><label for="select_all"><span></span></label>', 'sort_column' => '');
	if(!$params['deleted'])
	{
		$headers[] = array('label' => lang('common_edit'), 'sort_column' => '');
	}
	
	$headers[] = array('label' => lang('expenses_id'), 'sort_column' => 'id');
	$headers[] = array('label' => lang('expenses_type'), 'sort_column' => 'expense_type');
	$headers[] = array('label' => lang('expenses_description'), 'sort_column' => 'expense_description');
	$headers[] = array('label' => lang('common_category'), 'sort_column' => 'category');
	$headers[] = array('label' => lang('expenses_date'), 'sort_column' => 'expense_date');
	$headers[] = array('label' => lang('expenses_amount'), 'sort_column' => 'expense_amount');
	$headers[] = array('label' => lang('common_payment'), 'sort_column' => 'expense_payment_type');
	$headers[] = array('label' => lang('common_tax'), 'sort_column' => 'expense_tax');
	$headers[] = array('label' => lang('common_recipient_name'), 'sort_column' => 'employee_recv');
	$headers[] = array('label' => lang('common_approved_by'), 'sort_column' => 'employee_appr');
	$headers[] = array('label' => '&nbsp;', 'sort_column' => '');
		
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
			$table.="<th data-sort-column='$sort_col' class='rightmost'>$label</th>";
		}
		else
		{
			$table.="<th data-sort-column='$sort_col'>$label</th>";		
		}
	}
	$table.='</tr></thead><tbody>';
	$table.=get_expenses_manage_table_data_rows($expenses,$controller);
	$table.='</tbody></table>';
	return $table;
}
/*
Gets the html data rows for the items.
*/
function get_expenses_manage_table_data_rows($expenses,$controller)
{
	$CI =& get_instance();
	
	$table_data_rows='';
	
	foreach($expenses->result() as $expense)
	{
		$table_data_rows.=get_expenses_data_row($expense,$controller);
	}
	
	if($expenses->num_rows()==0)
	{
		$table_data_rows.="<tr><td colspan='1000'><span class='col-md-12 text-center text-warning' >".lang('expenses_no_expenses_to_display')."</span></td></tr>";
	}
	
	return $table_data_rows;
}

function get_expenses_data_row($expense,$controller)
{
	$CI =& get_instance();
	$controller_name=strtolower(get_class($CI));
	$params = $CI->session->userdata($controller_name.'_search_data') ? $CI->session->userdata($controller_name.'_search_data') : array('deleted' => 0);
	
	$controller_name=strtolower(get_class($CI));
	$table_data_row='<tr>';
	$table_data_row.="<td><input type='checkbox' id='expenses_$expense->id' value='".$expense->id."'/><label for='expenses_$expense->id'><span></span></label></td>";
	
	if(!$params['deleted'])
	{
		$table_data_row.='<td>'.anchor($controller_name."/view/$expense->id/2	", lang('common_edit'),array('class'=>'','title'=>lang($controller_name.'_update'))).'</td>';
	}
	
	$table_data_row.='<td>'.$expense->id.'</td>';
	$table_data_row.='<td>'.H($expense->expense_type).'</td>';
	$table_data_row.='<td>'.H($expense->expense_description).'</td>';
	$table_data_row.='<td>'.H($CI->Expense_category->get_full_path($expense->category_id)).'</td>';
	$table_data_row.='<td>'.date(get_date_format(), strtotime($expense->expense_date)).'</td>';
	$table_data_row.='<td>'.to_currency($expense->expense_amount).'</td>';
	$table_data_row.='<td>'.H($expense->expense_payment_type).'</td>';
	$table_data_row.='<td>'.to_currency($expense->expense_tax).'</td>';
	$table_data_row.='<td>'.H($expense->employee_recv).'</td>';
	$table_data_row.='<td>'.H($expense->employee_appr).'</td>';

	$expense_image_url = $expense->expense_image_id? app_file_url($expense->expense_image_id) : base_url('assets/assets/images/default.png');
	if ($expense_image_url)
	{	
		$table_data_row.="<td><a href='$expense_image_url' class='rollover'><img src='".$expense_image_url."' alt='".H($expense->expense_amount)."' class='img-polaroid' width='45' /></a></td>";
	}

	$table_data_row.='</tr>';
	return $table_data_row;
}

function get_appointments_manage_table($appointments,$controller)
{
	$CI =& get_instance();
	$controller_name=strtolower(get_class($CI));
	$params = $CI->session->userdata($controller_name.'_search_data') ? $CI->session->userdata($controller_name.'_search_data') : array('deleted' => 0);
	
	$table='<table class="tablesorter table table-hover" id="sortable_table">';

	$headers[] = array('label' => '<input type="checkbox" id="select_all" /><label for="select_all"><span></span></label>', 'sort_column' => '');
	if(!$params['deleted'])
	{
		$headers[] = array('label' => lang('common_edit'), 'sort_column' => '');
	}
	
	$headers[] = array('label' => lang('common_id'), 'sort_column' => 'id');
	$headers[] = array('label' => lang('common_category'), 'sort_column' => 'id');
	$headers[] = array('label' => lang('appointments_appointment_person'), 'sort_column' => 'person.last_name');
	$headers[] = array('label' => lang('common_employee'), 'sort_column' => 'employee.last_name');
	$headers[] = array('label' => lang('appointments_start_date'), 'sort_column' => 'start_time');
	$headers[] = array('label' => lang('appointments_end_date'), 'sort_column' => 'end_time');
	$headers[] = array('label' => lang('common_notes'), 'sort_column' => 'notes');
		
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
			$table.="<th data-sort-column='$sort_col' class='rightmost'>$label</th>";
		}
		else
		{
			$table.="<th data-sort-column='$sort_col'>$label</th>";		
		}
	}
	$table.='</tr></thead><tbody>';
	$table.=get_appointments_manage_table_data_rows($appointments,$controller);
	$table.='</tbody></table>';
	return $table;
}
/*
Gets the html data rows for the items.
*/
function get_appointments_manage_table_data_rows($appointments,$controller)
{
	$CI =& get_instance();
	
	$table_data_rows='';
	
	foreach($appointments->result() as $expense)
	{
		$table_data_rows.=get_appointments_data_row($expense,$controller);
	}
	
	if($appointments->num_rows()==0)
	{
		$table_data_rows.="<tr><td colspan='1000'><span class='col-md-12 text-center text-warning' >".lang('appointments_no_appointments_to_display')."</span></td></tr>";
	}
	
	return $table_data_rows;
}


function get_appointments_data_row($appointment,$controller)
{
	$CI =& get_instance();
	$controller_name=strtolower(get_class($CI));
	$params = $CI->session->userdata($controller_name.'_search_data') ? $CI->session->userdata($controller_name.'_search_data') : array('deleted' => 0);
	
	$controller_name=strtolower(get_class($CI));
	$table_data_row='<tr>';
	$table_data_row.="<td><input type='checkbox' id='appointments_$appointment->id' value='".$appointment->id."'/><label for='appointments_$appointment->id'><span></span></label></td>";
	
	if(!$params['deleted'])
	{
		$table_data_row.='<td>'.anchor($controller_name."/view/$appointment->id/2	", lang('common_edit'),array('class'=>'','title'=>lang($controller_name.'_update'))).'</td>';
	}
	
	$table_data_row.='<td>'.$appointment->id.'</td>';
	$table_data_row.='<td>'.H($appointment->appointment_type).'</td>';
	$table_data_row.='<td>'.H($appointment->person).'</td>';
	$table_data_row.='<td>'.H($appointment->employee).'</td>';
	$table_data_row.='<td>'.date(get_date_format().' '.get_time_format(), strtotime($appointment->start_time)).'</td>';
	$table_data_row.='<td>'.date(get_date_format().' '.get_time_format(), strtotime($appointment->end_time)).'</td>';
	$table_data_row.='<td>'.H($appointment->notes).'</td>';
	
	$table_data_row.='</tr>';
	return $table_data_row;
}

/*
Gets the html table to manage items.
*/
function get_permission_template_manage_table($templates,$controller)
{
	$CI =& get_instance();
	
	$controller_name=strtolower(get_class($CI));
	$params = $CI->session->userdata($controller_name.'_search_data') ? $CI->session->userdata($controller_name.'_search_data') : array('deleted' => 0);
	
	$table='<table class="tablesorter table table-hover" id="sortable_table">';	
	$headers[] = array('label' => '<input type="checkbox" id="select_all" /><label for="select_all"><span></span></label>', 'sort_column' => '');
	
	if(!$params['deleted'])
	{
		$headers[] = array('label' => lang('common_edit'), 'sort_column' => '');
	}
	
	$headers[] = array('label' => lang('template_id'), 'sort_column' => 'id');
	$headers[] = array('label' => lang('template_name'), 'sort_column' => 'name');
		
		
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
			$table.="<th data-sort-column='$sort_col' class='rightmost'>$label</th>";
		}
		else
		{
			$table.="<th data-sort-column='$sort_col'>$label</th>";		
		}
	}
	
	$table.='</tr></thead><tbody>';
	$table.=get_permission_template_manage_table_data_rows($templates,$controller);
	$table.='</tbody></table>';
	return $table;
}

/*
Gets the html data rows for the items.
*/
function get_permission_template_manage_table_data_rows($templates,$controller)
{
	$CI =& get_instance();
	$table_data_rows='';
	
	foreach($templates->result() as $template)
	{
		$table_data_rows.=get_permission_template_data_row($template,$controller);
	}
	
	if($templates->num_rows()==0)
	{
		$table_data_rows.="<tr><td colspan='1000'><span class='col-md-12 text-center text-warning' >".lang('no_permission_tamplate_to_display')."</span></td></tr>";
	}
	
	return $table_data_rows;
}

function get_permission_template_data_row($template,$controller)
{
	$CI =& get_instance();
	
	$controller_name=strtolower(get_class($CI));
	$params = $CI->session->userdata($controller_name.'_search_data') ? $CI->session->userdata($controller_name.'_search_data') : array('deleted' => 0);
	
	$table_data_row='<tr>';
	$table_data_row.="<td><input type='checkbox' id='location_$template->id' value='".$template->id."'/><label for='location_$template->id'><span></span></label></td>";
	
	if(!$params['deleted'])
	{
		$table_data_row.='<td>'.anchor($controller_name."/view/$template->id/2", lang('common_edit'),array('class'=>' ','title'=>lang($controller_name.'_update'))).'</td>';
	}
	
	$table_data_row.='<td>'.$template->id.'</td>';
	$table_data_row.='<td>'.H($template->name).'</td>';
	
	$table_data_row.='</tr>';
	return $table_data_row;
}

?>
