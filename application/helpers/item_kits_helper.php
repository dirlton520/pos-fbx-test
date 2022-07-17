<?php
function get_item_kits_barcode_data($item_kits_ids)
{
	$CI =& get_instance();	
	
	$hide_prices = $CI->config->item('hide_price_on_barcodes');
	
	$result = array();

	$item_kit_ids = explode('~', $item_kits_ids);
	foreach ($item_kit_ids as $item_kit_id)
	{
		$barcode_number = 'KIT '.number_pad($item_kit_id,$CI->config->item('zerofill_barcode') ? $CI->config->item('zerofill_barcode') : 10);		
		$item_kit_info = $CI->Item_kit->get_info($item_kit_id);
		
		if (!$item_kit_info->is_barcoded)
		{
			continue;
		}
		
		if ($id_to_show_on_barcode = $CI->config->item('id_to_show_on_barcode'))
		{
			if ($id_to_show_on_barcode == 'id')
			{
				$barcode_number = 'KIT '.number_pad($item_kit_id,$CI->config->item('zerofill_barcode') ? $CI->config->item('zerofill_barcode') : 10);		
			}
			elseif($id_to_show_on_barcode == 'number')
			{
				$barcode_number = $item_kit_info->item_kit_number ? $item_kit_info->item_kit_number : ('KIT '.number_pad($item_kit_id,$CI->config->item('zerofill_barcode') ? $CI->config->item('zerofill_barcode') : 10));
			}
			elseif($id_to_show_on_barcode == 'product_id')
			{
				$barcode_number = $item_kit_info->product_id ? $item_kit_info->product_id : ('KIT '.number_pad($item_kit_id,$CI->config->item('zerofill_barcode') ? $CI->config->item('zerofill_barcode') : 10));
			}
		}
		
		$item_kit_location_info = $CI->Item_kit_location->get_info($item_kit_id);
		
		$item_kit_price = (double)$item_kit_location_info->unit_price ? $item_kit_location_info->unit_price : $item_kit_info->unit_price;
		
		if($CI->config->item('barcode_price_include_tax'))
		{
			if($item_kit_info->tax_included)
			{
				$result[] = array('name' => (!$hide_prices ? '<span style="font-size:10pt;font-weight:bold;">'.to_currency($item_kit_price).'</span> ' : '').($CI->config->item('hide_name_on_barcodes') ? '' : ($item_kit_info->barcode_name ? $item_kit_info->barcode_name : $item_kit_info->name)), 'id'=> $barcode_number);
			}
			else
			{				
				$result[] = array('name' =>(!$hide_prices ? '<span style="font-size:10pt;font-weight:bold;">'.to_currency(get_price_for_item_kit_including_taxes($item_kit_id,$item_kit_price)).'</span> ': '').($CI->config->item('hide_name_on_barcodes') ? '' : ($item_kit_info->barcode_name ? $item_kit_info->barcode_name : $item_kit_info->name)), 'id'=> $barcode_number);
	  	 	}
	  }
	  else
	  {
		if ($item_kit_info->tax_included)
		{
		    $result[] = array('name' =>(!$hide_prices ? '<span style="font-size:10pt;font-weight:bold;">'.to_currency(get_price_for_item_kit_excluding_taxes($item_kit_id, $item_kit_price)).'</span> ' : '').($CI->config->item('hide_name_on_barcodes') ? '' : ($item_kit_info->barcode_name ? $item_kit_info->barcode_name : $item_kit_info->name)), 'id'=> $barcode_number);
		}
		else
		{
	    	$result[] = array('name' =>(!$hide_prices ? '<span style="font-size:10pt;font-weight:bold;">'.to_currency($item_kit_price).'</span> ' : '').($CI->config->item('hide_name_on_barcodes') ? '' : ($item_kit_info->barcode_name ? $item_kit_info->barcode_name : $item_kit_info->name)), 'id'=> $barcode_number);
	  	}
	  }
	}
	
	return $result;
}

function get_price_for_item_kit_excluding_taxes($item_kit_id_or_line, $item_kit_price_including_tax, $sale_id = FALSE)
{
	$return = FALSE;
	$CI =& get_instance();

	if ($sale_id !== FALSE)
	{
		$tax_info = $CI->Sale->get_sale_item_kits_taxes($sale_id,$item_kit_id_or_line);
	}	
	else
	{
		$tax_info = $CI->Item_kit_taxes_finder->get_info($item_kit_id_or_line);
	}
	
	if (count($tax_info) == 2 && $tax_info[1]['cumulative'] == 1)
	{
		$return = $item_kit_price_including_tax/(1+($tax_info[0]['percent'] /100) + ($tax_info[1]['percent'] /100) + (($tax_info[0]['percent'] /100) * (($tax_info[1]['percent'] /100))));
	}
	else //0 or more taxes NOT cumulative
	{
		$total_tax_percent = 0;
		
		foreach($tax_info as $tax)
		{
			$total_tax_percent+=$tax['percent'];
		}
		
		$return = $item_kit_price_including_tax/(1+($total_tax_percent /100));
	}
	
	if ($return !== FALSE)
	{
		return to_currency_no_money($return, 10);
	}
	
	return FALSE;	
}

function get_price_for_item_kit_including_taxes($item_kit_id_or_line, $item_kit_price_excluding_tax, $sale_id = FALSE)
{
	$return = FALSE;
	$CI =& get_instance();
	
	if ($sale_id !== FALSE)
	{
		$tax_info = $CI->Sale->get_sale_item_kits_taxes($sale_id,$item_kit_id_or_line);
	}	
	else
	{
		$tax_info = $CI->Item_kit_taxes_finder->get_info($item_kit_id_or_line);
	}
	
	if (count($tax_info) == 2 && $tax_info[1]['cumulative'] == 1)
	{
		$first_tax = ($item_kit_price_excluding_tax*($tax_info[0]['percent']/100));
		$second_tax = ($item_kit_price_excluding_tax + $first_tax) *($tax_info[1]['percent']/100);
		$return = $item_kit_price_excluding_tax + $first_tax + $second_tax;
	}	
	else //0 or more taxes NOT cumulative
	{
		$total_tax_percent = 0;
		
		foreach($tax_info as $tax)
		{
			$total_tax_percent+=$tax['percent'];
		}
		
		$return = $item_kit_price_excluding_tax*(1+($total_tax_percent /100));
	}
	
	if ($return !== FALSE)
	{
		return to_currency_no_money($return, 10);
	}
	
	return FALSE;
}

function get_commission_for_item_kit($cart,$item_kit_id, $price,$cost, $quantity, $discount)
{
	if ($price == 0)
	{
		return 0;
	}
	
	$CI =& get_instance();	
	
	$employee_id=$cart->sold_by_employee_id;
	$sales_person_info = $CI->Employee->get_info($employee_id);
	$employee_id=$CI->Employee->get_logged_in_employee_info() ? $CI->Employee->get_logged_in_employee_info()->person_id  : $cart->employee_id;
	$logged_in_employee_info = $CI->Employee->get_info($employee_id);
	
	$item_kit_info = $CI->Item_kit->get_info($item_kit_id);
	
	if ($item_kit_info->commission_fixed !== NULL)
	{
		return $quantity*$item_kit_info->commission_fixed;
	}
	elseif($item_kit_info->commission_percent !== NULL)
	{
		$commission_percent_type = $item_kit_info->commission_percent_type == 'profit' ? 'profit' : 'selling_price';
		
		if ($commission_percent_type == 'selling_price')
		{
			return to_currency_no_money(($price*$quantity-$price*$quantity*$discount/100)*($item_kit_info->commission_percent/100));			
		}
		else //Profit
		{
			return to_currency_no_money((($price*$quantity-$price*$quantity*$discount/100) - ($cost*$quantity)) * ($item_kit_info->commission_percent/100));				
		}
	}
	elseif($CI->config->item('select_sales_person_during_sale'))
	{
		if($sales_person_info->commission_percent > 0)
		{
			$commission_percent_type = $sales_person_info->commission_percent_type == 'profit' ? 'profit' : 'selling_price';
			
			if ($commission_percent_type == 'selling_price')
			{
				return to_currency_no_money(($price*$quantity-$price*$quantity*$discount/100)*((float)($sales_person_info->commission_percent)/100));
			}
			else
			{
				return to_currency_no_money((($price*$quantity-$price*$quantity*$discount/100) - ($cost*$quantity)) * ($sales_person_info->commission_percent/100));				
			}
		}
		
		$commission_percent_type = $CI->config->item('commission_percent_type') == 'profit' ? 'profit' : 'selling_price';
		
		if ($commission_percent_type == 'profit')
		{
			return to_currency_no_money((($price*$quantity-$price*$quantity*$discount/100) - ($cost*$quantity)) * ((float)($CI->config->item('commission_default_rate'))/100));				
		}
		else
		{
			return to_currency_no_money(($price*$quantity-$price*$quantity*$discount/100)*((float)($CI->config->item('commission_default_rate'))/100));
		}
		
	}
	elseif($logged_in_employee_info->commission_percent > 0)
	{
		$commission_percent_type = $logged_in_employee_info->commission_percent_type == 'profit' ? 'profit' : 'selling_price';
		
		if ($commission_percent_type == 'selling_price')
		{
			return to_currency_no_money(($price*$quantity-$price*$quantity*$discount/100)*((float)($logged_in_employee_info->commission_percent)/100));
		}
		else
		{
			return to_currency_no_money((($price*$quantity-$price*$quantity*$discount/100) - ($cost*$quantity)) * ($logged_in_employee_info->commission_percent/100));				
		}
	}
	else
	{
		$commission_percent_type = $CI->config->item('commission_percent_type') == 'profit' ? 'profit' : 'selling_price';
		
		if ($commission_percent_type == 'profit')
		{
			return to_currency_no_money((($price*$quantity-$price*$quantity*$discount/100) - ($cost*$quantity)) * ((float)($CI->config->item('commission_default_rate'))/100));				
		}
		else
		{
			return to_currency_no_money(($price*$quantity-$price*$quantity*$discount/100)*((float)($CI->config->item('commission_default_rate'))/100));
		}
	}
}
?>