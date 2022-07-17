<?php

function get_price_rules_manage_table($price_rules,$controller)
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
	
	$headers[] = array('label' => lang('price_rules_id'), 'sort_column' => 'id');
	$headers[] = array('label' => lang('price_rules_name'), 'sort_column' => 'name');
	$headers[] = array('label' => lang('price_rules_start_date'), 'sort_column' => 'start_date');
	$headers[] = array('label' => lang('price_rules_end_date'), 'sort_column' => 'end_date');
	$headers[] = array('label' => lang('price_rules_type'), 'sort_column' => 'type');
	$headers[] = array('label' => lang('common_coupon_code'), 'sort_column' => 'coupon_code');
	$headers[] = array('label' => lang('price_rules_status'), 'sort_column' => 'active');
		
		
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
	$table.=get_price_rules_manage_table_data_rows($price_rules,$controller_name);
	$table.='</tbody></table>';
	return $table;
}

function get_price_rules_manage_table_data_rows( $priceRules, $controller_name )
{
	$CI =& get_instance();
	$table_data_rows='';
	
	foreach($priceRules->result() as $rule)
	{
		$table_data_rows.=get_price_rule_data_row( $rule, $controller_name );
	}
	
	if($priceRules->num_rows() == 0)
	{
		$table_data_rows.="<tr><td  colspan='1000'><span class='col-md-12 text-center text-warning' >".lang('price_rules_no_rule')."</span></td></tr>";
	}
	
	return $table_data_rows;
}

function get_price_rule_data_row($rule,$controller_name)
{
	$CI =& get_instance();
	$controller_name=strtolower(get_class($CI));
	
	$table_data_row='<tr>';
	$table_data_row.="<td><input type='checkbox' id='pricerule_".$rule->id."' value='".$rule->id."'/><label for='pricerule_".$rule->id."'><span></span></label></td>";
	
	$params = $CI->session->userdata($controller_name.'_search_data') ? $CI->session->userdata($controller_name.'_search_data') : array('deleted' => 0);
	
	if(!$params['deleted'])
	{
		$table_data_row.='<td>'.anchor($controller_name."/view/$rule->id	", lang('common_edit'),array('class'=>' ','title'=>lang($controller_name.'_update'))).'</td>';		
	}
	$table_data_row.='<td>'.$rule->id.'</td>';
	$table_data_row.='<td>'.H($rule->name).'</td>';
	$table_data_row.='<td>'.($rule->start_date ? date(get_date_format(),strtotime($rule->start_date)) : '').'</td>';
	$table_data_row.='<td>'.($rule->end_date ? date(get_date_format(),strtotime($rule->end_date)) : '').'</td>';
	if ($rule->type)
	{
		$table_data_row.='<td>'.lang($rule->type).'</td>';
	}
	else
	{
		$table_data_row.='<td>'.lang('common_none').'</td>';		
	}
	
	if ($rule->coupon_code)
	{
		$table_data_row.='<td>'.$rule->coupon_code.'</td>';
	}
	else
	{
		$table_data_row.='<td>'.lang('common_none').'</td>';		
	}
	
	$table_data_row.='<td>'.($rule->active==0 ? lang('common_inactive') : lang('common_active')).'</td>';
	//$table_data_row.='<td class="rightmost">'.anchor($controller_name."/rule_details/$rule->id", lang('price_rules_view_rule'),array('class'=>' ','title'=>lang('common_clone'))).'</td>';			
	
	$table_data_row.='</tr>';
	return $table_data_row;
}

?>