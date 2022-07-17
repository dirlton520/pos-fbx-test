<?php
function get_item_variations_barcode_data($item_variation_ids)
{
	$CI =& get_instance();	
	$CI->load->model('Item');
	$CI->load->model('Item_variations');
	
	$hide_prices = $CI->config->item('hide_price_on_barcodes');
	
	$result = array();
	
	$item_variation_ids = explode('~', $item_variation_ids);
		
	foreach ($item_variation_ids as $item_variation_id)
	{
		$item_variation_info = $CI->Item_variations->get_info($item_variation_id);
		
		$item_id = $item_variation_info->item_id;
		
		$number = $item_id."#".$item_variation_id;
		$barcode_number = number_pad($number,$CI->config->item('zerofill_barcode') ? $CI->config->item('zerofill_barcode') : 10);
		$item_info = $CI->Item->get_info($item_id);
		if (!$item_info->is_barcoded)
		{
			continue;
		}
		
		if ($CI->config->item('display_item_name_first_for_variation_name'))
		{
			$barcode_name = $CI->config->item('hide_name_on_barcodes') ? '' : ($item_info->barcode_name ? $item_info->barcode_name : $item_info->name).' '.($item_variation_info->name ? $item_variation_info->name : $CI->Item_variations->get_variation_name($item_variation_id)).($item_info->size ? ' ('.$item_info->size.')' : '');			
		}
		else
		{
			$barcode_name = $CI->config->item('hide_name_on_barcodes') ? '' : ($item_variation_info->name ? $item_variation_info->name : $CI->Item_variations->get_variation_name($item_variation_id)).' '.($item_info->barcode_name ? $item_info->barcode_name : $item_info->name).($item_info->size ? ' ('.$item_info->size.')' : '');
		}
		if ($id_to_show_on_barcode = $CI->config->item('id_to_show_on_barcode'))
		{
			if($id_to_show_on_barcode == 'number')
			{
				$barcode_number = $item_variation_info->item_number ? $item_variation_info->item_number : $item_id."#".$item_variation_id;
			}
			else
			{
				$number = $item_id."#".$item_variation_id;
				$barcode_number = number_pad($number,$CI->config->item('zerofill_barcode') ? $CI->config->item('zerofill_barcode') : 10);
			}
		}
		
	
		$item_variation_location_info = $CI->Item_variation_location->get_info($item_id);
		
		$item_location_info = $CI->Item_location->get_info($item_id);
		
		$today =  strtotime(date('Y-m-d'));
		$is_item_variation_location_promo = false; // there's no promo field for item location variation table
		$is_item_location_promo = ($item_location_info->start_date === NULL && $item_location_info->end_date === NULL && $item_location_info->promo_price) || ($item_location_info->start_date !== NULL && $item_location_info->end_date !== NULL) && (strtotime($item_location_info->start_date) <= $today && strtotime($item_location_info->end_date) >= $today);

		$is_item_variation_promo = ($item_variation_info->start_date === NULL && $item_variation_info->end_date === NULL && $item_variation_info->promo_price) || ($item_variation_info->start_date !== NULL && $item_variation_info->end_date !== NULL) && (strtotime($item_variation_info->start_date) <= $today && strtotime($item_variation_info->end_date) >= $today);
		$is_item_promo = ($item_info->start_date === NULL && $item_info->end_date === NULL && $item_info->promo_price) || ($item_info->start_date !== NULL && $item_info->end_date !== NULL) && (strtotime($item_info->start_date) <= $today && strtotime($item_info->end_date) >= $today);

		$regular_item_variation_price = (double)$item_variation_location_info->unit_price ? $item_variation_location_info->unit_price : $item_variation_info->unit_price;
		$regular_item_price = (double)$item_location_info->unit_price ? $item_location_info->unit_price : $item_info->unit_price;

		if(!$regular_item_variation_price)
			$regular_item_variation_price = $regular_item_price;

		$item_variation_price = "";
		if($is_item_variation_location_promo){
			//skip 
		}
		elseif($is_item_variation_promo){
			$item_variation_price = $item_variation_info->promo_price;
		}
		elseif ((double)$item_variation_info->unit_price)
		{
			$item_variation_price = $item_variation_info->unit_price;
		}
		elseif ($is_item_location_promo)
		{
			$item_variation_price = $item_location_info->promo_price;
		}
		elseif ($is_item_promo)
		{
			$item_variation_price = $item_info->promo_price;
		}
		else
		{
			$item_variation_price = (double)$item_location_info->unit_price ? $item_location_info->unit_price : $item_info->unit_price;
		}		
		
		if($CI->config->item('barcode_price_include_tax'))
		{
			if($item_info->tax_included)
			{
				$result[] = array('description' => $item_info->description, 'name' => !$hide_prices ? (($is_item_location_promo || $is_item_promo ? '<span style="text-decoration: line-through;font-weight:bold;">'.to_currency($regular_item_variation_price).'</span> ' : ' ').'<span class="item-price-barcode" style="font-weight:bold;">'.to_currency($item_variation_price).'</span> '.$barcode_name) : $barcode_name , 'id'=> $barcode_number);
			}
			else
			{				
				$result[] = array('description' => $item_info->description, 'name' => !$hide_prices ? (($is_item_location_promo || $is_item_promo ? '<span style="text-decoration: line-through;font-weight:bold;">'.to_currency(get_price_for_item_including_taxes($item_id,$regular_item_variation_price)).'</span> ' : ' ').'<span class="item-price-barcode" style="font-weight:bold;">'.to_currency(get_price_for_item_including_taxes($item_id,$item_variation_price)).'</span> '.$barcode_name) : $barcode_name, 'id'=> $barcode_number);
	  	}
	  }
	  else
	  {
		if ($item_info->tax_included)
		{
		    $result[] = array('description' => $item_info->description, 'name' => !$hide_prices ? (($is_item_location_promo || $is_item_promo ? '<span style="text-decoration: line-through;font-weight:bold;">'.to_currency(get_price_for_item_excluding_taxes($item_id, $regular_item_variation_price)).'</span> ' : ' ').'<span class="item-price-barcode" style="font-weight:bold;">'.to_currency(get_price_for_item_excluding_taxes($item_id, $item_variation_price)).'</span> '.$barcode_name) : $barcode_name, 'id'=> $barcode_number);
		}
		else
		{
			$result[] = array('description' => $item_info->description, 'name' => !$hide_prices ? (($is_item_location_promo || $is_item_promo ? '<span style="text-decoration: line-through;font-weight:bold;">'.to_currency($regular_item_variation_price).'</span> ' : ' ').'<span class="item-price-barcode" style="font-weight:bold;">'.to_currency($item_variation_price).'</span> '.$barcode_name) : $barcode_name, 'id'=> $barcode_number);
	  	}
	  }
	}
	
	
	return $result;
}

function get_items_barcode_data($item_ids, $predefined_barcode=false)
{
	$CI =& get_instance();	
	
	$hide_prices = $CI->config->item('hide_price_on_barcodes');
	
	$result = array();

	$item_ids = explode('~', $item_ids);
	foreach ($item_ids as $item_id)
	{
		$barcode_number = number_pad($item_id,$CI->config->item('zerofill_barcode') ? $CI->config->item('zerofill_barcode') : 10);	
			
		$item_info = $CI->Item->get_info($item_id);
		
		if (!$item_info->is_barcoded)
		{
			continue;
		}
		
		$barcode_name = $CI->config->item('hide_name_on_barcodes') ? '' : ($item_info->barcode_name ? $item_info->barcode_name : $item_info->name).($item_info->size ? ' ('.$item_info->size.')' : '');
		
		if ($id_to_show_on_barcode = $CI->config->item('id_to_show_on_barcode'))
		{
			if ($id_to_show_on_barcode == 'id')
			{
				$barcode_number = number_pad($item_id,$CI->config->item('zerofill_barcode') ? $CI->config->item('zerofill_barcode') : 10);
			}
			elseif($id_to_show_on_barcode == 'number')
			{
				$barcode_number = $item_info->item_number ? $item_info->item_number : number_pad($item_id,$CI->config->item('zerofill_barcode') ? $CI->config->item('zerofill_barcode') : 10);
			}
			elseif($id_to_show_on_barcode == 'product_id')
			{
				$barcode_number = $item_info->product_id ? $item_info->product_id : number_pad($item_id,$CI->config->item('zerofill_barcode') ? $CI->config->item('zerofill_barcode') : 10);
			}
		}
				
		$item_location_info = $CI->Item_location->get_info($item_id);
		
		$today =  strtotime(date('Y-m-d'));
		$is_item_location_promo = ($item_location_info->start_date === NULL && $item_location_info->end_date === NULL && $item_location_info->promo_price) || ($item_location_info->start_date !== NULL && $item_location_info->end_date !== NULL) && (strtotime($item_location_info->start_date) <= $today && strtotime($item_location_info->end_date) >= $today);
		$is_item_promo = ($item_info->start_date === NULL && $item_info->end_date === NULL && $item_info->promo_price) || ($item_info->start_date !== NULL && $item_info->end_date !== NULL) && (strtotime($item_info->start_date) <= $today && strtotime($item_info->end_date) >= $today);
				
		$regular_item_price = (double) $item_location_info->unit_price ? $item_location_info->unit_price : $item_info->unit_price;
		
		if ($is_item_location_promo)
		{
			$item_price = $item_location_info->promo_price;
		}
		elseif ($is_item_promo)
		{
			$item_price = $item_info->promo_price;
		}
		else
		{
			$item_price = (double) $item_location_info->unit_price ? $item_location_info->unit_price : $item_info->unit_price;
		}		
		
		$barcode_number = $predefined_barcode ? $predefined_barcode : $barcode_number;
		
		if($CI->config->item('barcode_price_include_tax'))
		{
			if($item_info->tax_included)
			{
				$result[] = array('description' => $item_info->description, 'name' => !$hide_prices ? (($is_item_location_promo || $is_item_promo ? '<span style="text-decoration: line-through;font-weight:bold;">'.to_currency($regular_item_price).'</span> ' : ' ').'<span class="item-price-barcode" style="font-weight:bold;">'.to_currency($item_price).'</span> '.$barcode_name) : $barcode_name , 'id'=> $barcode_number);
			}
			else
			{				
				$result[] = array('description' => $item_info->description, 'name' => !$hide_prices ? (($is_item_location_promo || $is_item_promo ? '<span style="text-decoration: line-through;font-weight:bold;">'.to_currency(get_price_for_item_including_taxes($item_id,$regular_item_price)).'</span> ' : ' ').'<span class="item-price-barcode" style="font-weight:bold;">'.to_currency(get_price_for_item_including_taxes($item_id,$item_price)).'</span> '.$barcode_name) : $barcode_name, 'id'=> $barcode_number);
	  	 	}
	  }
	  else
	  {
		if ($item_info->tax_included)
		{
		    $result[] = array('description' => $item_info->description, 'name' => !$hide_prices ? (($is_item_location_promo || $is_item_promo ? '<span style="text-decoration: line-through;font-weight:bold;">'.to_currency(get_price_for_item_excluding_taxes($item_id, $regular_item_price)).'</span> ' : ' ').'<span class="item-price-barcode" style="font-weight:bold;">'.to_currency(get_price_for_item_excluding_taxes($item_id, $item_price)).'</span> '.$barcode_name) : $barcode_name, 'id'=> $barcode_number);
		}
		else
		{
			$result[] = array('description' => $item_info->description, 'name' => !$hide_prices ? (($is_item_location_promo || $is_item_promo ? '<span style="text-decoration: line-through;font-weight:bold;">'.to_currency($regular_item_price).'</span> ' : ' ').'<span class="item-price-barcode" style="font-weight:bold;">'.to_currency($item_price).'</span> '.$barcode_name) : $barcode_name, 'id'=> $barcode_number);
	  	}
	  }
	}
	return $result;
}

function calculate_average_cost_price_preview($item_id,$variation_id, $price, $additional_quantity,$discount_percent)
{
	$CI =& get_instance();	
	
	if ($CI->config->item('calculate_average_cost_price_from_receivings'))
	{
		$CI->load->model('Receiving');
		return $CI->Receiving->calculate_cost_price_preview($item_id, $variation_id,$price, $additional_quantity, $discount_percent);
	}
	return false;
}


function get_price_for_item_excluding_taxes($item_id_or_line, $item_price_including_tax, $sale_id = FALSE, $receiving_id = FALSE)
{
	$return = FALSE;
	$CI =& get_instance();
	
	if ($sale_id !== FALSE)
	{
		$tax_info = $CI->Sale->get_sale_items_taxes($sale_id, $item_id_or_line);
	}	
	elseif($receiving_id !== FALSE)
	{
		$tax_info = $CI->Receiving->get_receiving_items_taxes($receiving_id, $item_id_or_line);
	}
	else
	{
		$tax_info = $CI->Item_taxes_finder->get_info($item_id_or_line);
	}
	
	if (count($tax_info) == 2 && $tax_info[1]['cumulative'] == 1)
	{
		$return = $item_price_including_tax/(1+($tax_info[0]['percent'] /100) + ($tax_info[1]['percent'] /100) + (($tax_info[0]['percent'] /100) * (($tax_info[1]['percent'] /100))));
	}
	else //0 or more taxes NOT cumulative
	{
		$total_tax_percent = 0;
		
		foreach($tax_info as $tax)
		{
			$total_tax_percent+=$tax['percent'];
		}
		
		$return = $item_price_including_tax/(1+($total_tax_percent /100));
	}
	
	if ($return !== FALSE)
	{
		return to_currency_no_money($return, 10);
	}
	
	return FALSE;
}

function get_price_for_item_including_taxes($item_id_or_line, $item_price_excluding_tax, $sale_id = FALSE, $receiving_id = FALSE)
{
	$return = FALSE;
	$CI =& get_instance();
	if ($sale_id !== FALSE)
	{
		$tax_info = $CI->Sale->get_sale_items_taxes($sale_id,$item_id_or_line);
	}	
	elseif($receiving_id !== FALSE)
	{
		$tax_info = $CI->Receiving->get_receiving_items_taxes($receiving_id, $item_id_or_line);
	}
	else
	{
		$tax_info = $CI->Item_taxes_finder->get_info($item_id_or_line);
	}
	
	if (count($tax_info) == 2 && $tax_info[1]['cumulative'] == 1)
	{
		$first_tax = ($item_price_excluding_tax*($tax_info[0]['percent']/100));
		$second_tax = ($item_price_excluding_tax + $first_tax) *($tax_info[1]['percent']/100);
		$return = $item_price_excluding_tax + $first_tax + $second_tax;
	}	
	else //0 or more taxes NOT cumulative
	{
		$total_tax_percent = 0;
		
		foreach($tax_info as $tax)
		{
			$total_tax_percent+=$tax['percent'];
		}
		
		$return = $item_price_excluding_tax*(1+($total_tax_percent /100));
	}

	
	if ($return !== FALSE)
	{
		return to_currency_no_money($return, 10);
	}
	
	return FALSE;
}

function get_commission_for_item($cart,$item_id, $price, $cost, $quantity,$discount)
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
	
	$item_info = $CI->Item->get_info($item_id);
	
	if ($item_info->commission_fixed !== NULL)
	{
		return $quantity*$item_info->commission_fixed;
	}
	elseif($item_info->commission_percent !== NULL)
	{
		$commission_percent_type = $item_info->commission_percent_type == 'profit' ? 'profit' : 'selling_price';
		
		if ($commission_percent_type == 'selling_price')
		{
			return to_currency_no_money(($price*$quantity-$price*$quantity*$discount/100)*($item_info->commission_percent/100));			
		}
		else //Profit
		{
			return to_currency_no_money((($price*$quantity-$price*$quantity*$discount/100) - ($cost*$quantity)) * ($item_info->commission_percent/100));				
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

function cache_item_and_item_kit_cart_info($cart_items)
{
	$CI =& get_instance();
	$item_ids = array();
	$item_kit_ids = array();
	$variation_ids = array();
	
	foreach($cart_items as $cart_item)
	{
		if (property_exists($cart_item,'item_id'))
		{
			$item_ids[] = $cart_item->item_id;
		}
		elseif (property_exists($cart_item,'item_kit_id'))
		{
			$item_kit_ids[] = $cart_item->item_kit_id;			
		}
		elseif (property_exists($cart_item,'variation_id') && $cart_item->variation_id)
		{
			$variation_ids[] = $cart_item->variation_id;
		}
	}
	
	$CI->Item->get_info($item_ids);
	$CI->Item_kit->get_info($item_kit_ids);

	$CI->Item_location->get_info($item_ids, false, true);
	$CI->Item_kit_location->get_info($item_kit_ids, false, true);
	
	$CI->Item_variation_location->get_info($variation_ids, false, true);
	
}

//This function returns item_id,variation_id, variation_name, and variation_choices from a scanned item string.
//example scans:
/*
1#1 (item id 1 and variation number 1)
UPC (item scanned as a UPC which gets parsed an finds item_id)
*/ 
function parse_item_scan_data($scan)
{
	$CI =& get_instance();
	
	$return = array();
	$return['item_id'] = NULL;
	$return['variation_id'] = NULL;
	$return['quantity_unit_id'] = NULL;
	$return['variation_name'] = '';
	$return['variation_choices'] = array();
	$return['variation_choices_model'] = array();

	if (($item_identifer_parts = explode('#', str_replace('|FORCE_ITEM_ID|','',$scan))) !== false)
	{
		if (isset($item_identifer_parts[1]))
		{
			$return['variation_id'] = $item_identifer_parts[1];
		}
	}

	//We are forcing to use item_id
	if (strpos($scan,'|FORCE_ITEM_ID|') !== FALSE)
	{
		$scan = str_replace('|FORCE_ITEM_ID|','',$scan);
		//Lookup item based on just store config; ignore all other fields
		$return['item_id'] = $CI->Item->lookup_item_id($scan,array('item_number','product_id','additional_item_numbers','item_variation_item_number','serial_numbers'));
	}
	else
	{
		//Lookup item based on lookup order defined in store config
		$return['item_id'] = $CI->Item->lookup_item_id($scan);			
	}


	//Do a check again as $item_id returned from lookup_item_id will have @ if quantity unit variation
	if (($item_identifer_parts = explode('@',$return['item_id'])) !== false)
	{
		if (isset($item_identifer_parts[1]))
		{
			$return['item_id'] = $item_identifer_parts[0];
			$return['quantity_unit_id'] = $item_identifer_parts[1];
		}
	}
	
	//Do a check again as $item_id returned from lookup_item_id will have # if variation
	if (($item_identifer_parts = explode('#',$return['item_id'])) !== false)
	{
		if (isset($item_identifer_parts[1]))
		{
			$return['item_id'] = $item_identifer_parts[0];
			$return['variation_id'] = $item_identifer_parts[1];
		}
	}
	
	if(!$return['item_id'])
	{
		return false;
	}
	
	
	$CI->load->model('Item_variations');
	$variations = $CI->Item_variations->get_variations($return['item_id']);
	
	foreach($variations as $item_variation_id=>$variation)
	{	
		
		if ($variation['name'])
		{
			$return['variation_choices'][$item_variation_id] 		= $variation['name'];
			/*
			** Add Variation Choices Model for Model
			*/
			
		}
		else
		{
			$return['variation_choices'][$item_variation_id] = implode(', ', array_column($variation['attributes'],'label'));
		}
		$return['variation_choices_model'][$item_variation_id] 	= implode(', ', array_column($variation['attributes'],'label'));
	}
	
	if ($return['variation_id'])
	{
		$return['variation_name'] = $return['variation_choices'][$return['variation_id']];

	}
	
	
	return $return;
	
}

function parse_scale_data($scan)
{
	$CI =& get_instance();
	$CI->load->model('Item');
	$return = array();
	
	$scale_format = $CI->config->item('scale_format');
	
	$number_start_index = FALSE;
	$number_end_index = FALSE;
	$price_start_index = FALSE;
	$price_end_index = FALSE;
	
	if (!$scale_format || $scale_format == 'scale_1')
	{
		$number_start_index = 1;
		$number_end_index = 5;
		$price_start_index = 7;
		$price_end_index = 10;
	}
	elseif($scale_format == 'scale_2')
	{
		$number_start_index = 1;
		$number_end_index = 5;
		$price_start_index = 6;
		$price_end_index = 10;
		
	}
	elseif($scale_format == 'scale_3')
	{
		$number_start_index = 1;
		$number_end_index = 5;
		$price_start_index = 7;
		$price_end_index = 11;
	
	}
	elseif($scale_format == 'scale_4')
	{
		$number_start_index = 1;
		$number_end_index = 5;
		$price_start_index = 6;
		$price_end_index = 11;
	}
	elseif($scale_format == 'scale_5')
	{
		$number_start_index = 1;
		$number_end_index = 5;
		$quantity_start_index = 6;
		$quantity_end_index = 11;
	}
	elseif($scale_format == 'scale_6')
	{
		$number_start_index = 2;
		$number_end_index = 6;
		$quantity_start_index = 7;
		$quantity_end_index = 11;
	}
	elseif($scale_format == 'scale_7')
	{
		$number_start_index = 2;
		$number_end_index = 7;
		$quantity_start_index = 8;
		$quantity_end_index = 11;
	}
	
	$item_number = substr($scan,$number_start_index,($number_end_index+1) - $number_start_index);
	
	$item_id = $CI->Item->lookup_item_id($item_number);
	
	if(!$item_id)
		return false;
	
	$return['item_id'] = $item_id;

	$item_info = $CI->Item->get_info($item_id);
	$item_location_info = $CI->Item_location->get_info($item_id);
	
	$today =  strtotime(date('Y-m-d'));
	$is_item_location_promo = ($item_location_info->start_date !== NULL && $item_location_info->end_date !== NULL) && (strtotime($item_location_info->start_date) <= $today && strtotime($item_location_info->end_date) >= $today);
	$is_item_promo = ($item_info->start_date !== NULL && $item_info->end_date !== NULL) && (strtotime($item_info->start_date) <= $today && strtotime($item_info->end_date) >= $today);	
	
	if ($is_item_location_promo)
	{
		$item_price = $item_location_info->promo_price;
	}
	elseif ($is_item_promo)
	{
		$item_price = $item_info->promo_price;
	}
	else
	{
		$item_price = $item_location_info->unit_price ? $item_location_info->unit_price : $item_info->unit_price;
	}		
	
	$item_cost_price = $item_location_info->cost_price ? $item_location_info->cost_price : $item_info->cost_price;
	
	
	if ($scale_format == 'scale_5' || $scale_format == 'scale_6' || $scale_format == 'scale_7')
	{
		$divide_by = $CI->config->item('scale_divide_by') ? $CI->config->item('scale_divide_by')  : 100;
		
		$total_quantity = substr($scan,$quantity_start_index,($quantity_end_index+1) - $quantity_start_index)/$divide_by;
		$sell_quantity = $total_quantity;
		$cost_quantity = $total_quantity;
		
		$return['sell_quantity'] = $sell_quantity;
		$return['sell_price'] = $item_price;
	
		$return['cost_quantity'] = $cost_quantity;
		$return['cost_price'] = $item_cost_price;
	}
	else
	{
		$divide_by = $CI->config->item('scale_divide_by') ? $CI->config->item('scale_divide_by')  : 100;
		$total_price = substr($scan,$price_start_index,($price_end_index+1) - $price_start_index)/$divide_by;
		$sell_quantity = $total_price/$item_price;
		$cost_quantity = $total_price/$item_cost_price;
	
		$return['sell_quantity'] = $sell_quantity;
		$return['sell_price'] = $item_price;
	
		$return['cost_quantity'] = $cost_quantity;
		$return['cost_price'] = $item_cost_price;
	}
	
	return $return;
	
}	

function commission_percent_type_format($percent_type)
{
	if ($percent_type == 'selling_price')
	{
		return lang('common_unit_price');		
	}
	return lang('common_profit');
	
}

?>