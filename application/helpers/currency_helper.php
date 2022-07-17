<?php

function to_currency_format($number, $decimals,$currency_symbol,$symbol_location,$number_of_decimals,$thousands_separator,$decimal_point)
{
	
	$number = (float)$number;
	$decimals_system_decide = true;
	
	if ($number_of_decimals !== NULL && $number_of_decimals!= '')
	{
		$decimals = (int)$number_of_decimals;
		$decimals_system_decide = false;
	}
	
	if($number >= 0)
	{
		if ($symbol_location == 'after')
		{
			$ret = number_format($number, $decimals, $decimal_point, $thousands_separator).$currency_symbol;
		}
		else
		{
			$ret = $currency_symbol.number_format($number, $decimals, $decimal_point, $thousands_separator);			
		}
   }
   else
   {
		if ($symbol_location == 'after')
	   	{
			$ret = '<span style="white-space:nowrap;">-</span>'.number_format(abs($number), $decimals, $decimal_point, $thousands_separator).$currency_symbol;
		}
		else
		{
			$ret = '<span style="white-space:nowrap;">-</span>'.$currency_symbol.number_format(abs($number), $decimals, $decimal_point, $thousands_separator);
		}
   }

	 if ($decimals_system_decide && $decimals >=2)
	 {
 		if ($symbol_location == 'after')
		{
   		 	return preg_replace('/(?<=\d{2})0+'.preg_quote($currency_symbol).'$/', $currency_symbol, $ret);
		}
		else
		{
   			 return preg_replace('/(?<=\d{2})0+$/', '', $ret);
		}
	 }
	 else
	 {
		 return $ret;
	 }
}

function to_currency_as_exchange_register_cart($number, $decimals = 2)
{
	$CI =& get_instance();
	$CI->load->model('Register_cart');
	$register_id = $CI->Employee->get_logged_in_employee_current_register_id();

	$symbol_location = $CI->Register_cart->get_data_for_key('exchange_symbol_location',$register_id);
	$number_of_decimals = $CI->Register_cart->get_data_for_key('exchange_number_of_decimals',$register_id);
	$thousands_separator = $CI->Register_cart->get_data_for_key('exchange_thousands_separator',$register_id);
	$decimal_point = $CI->Register_cart->get_data_for_key('exchange_decimal_point',$register_id);
	$currency_symbol = $CI->Register_cart->get_data_for_key('exchange_symbol',$register_id);
	$decimal_point = $CI->Register_cart->get_data_for_key('exchange_decimal_point',$register_id);
	
	return to_currency_format($number, $decimals,$currency_symbol,$symbol_location,$number_of_decimals,$thousands_separator,$decimal_point);
}
function to_currency_as_exchange($cart,$number, $decimals = 2)
{
	$currency_symbol = $cart->get_exchange_currency_symbol();
	$symbol_location = $cart->get_exchange_currency_symbol_location();
	$number_of_decimals = $cart->get_exchange_currency_number_of_decimals();
	$thousands_separator = $cart->get_exchange_currency_thousands_separator();
	$decimal_point = $cart->get_exchange_currency_decimal_point();
	
	return to_currency_format($number, $decimals,$currency_symbol,$symbol_location,$number_of_decimals,$thousands_separator,$decimal_point);
	
}
function to_currency($number, $decimals = 2, $show_not_set = TRUE)
{
	$CI =& get_instance();
	
	$currency_symbol = $CI->config->item('currency_symbol') ? $CI->config->item('currency_symbol') : '$';
	$symbol_location = $CI->config->item('currency_symbol_location');
	$number_of_decimals = $CI->config->item('number_of_decimals');
	$thousands_separator = $CI->config->item('thousands_separator') ? $CI->config->item('thousands_separator') : ',';
	$decimal_point = $CI->config->item('decimal_point') ? $CI->config->item('decimal_point') : '.';
	
	if($show_not_set && $number === NULL)
	{
		return lang('common_not_set');
	}
	
	return to_currency_format($number, $decimals,$currency_symbol,$symbol_location,$number_of_decimals,$thousands_separator,$decimal_point);	
}

function round_to_nearest_05($amount)
{
	return round($amount * 2, 1) / 2;
}

function to_currency_no_money($number, $decimals = 2,$use_defined_decimal_point = false)
{	
	$number = (float)$number;
	$CI =& get_instance();
	
	$decimals_system_decide = true;
	
	//Only use override if decimals passed in is less than 5 and we have configured a decimal override
	if ($decimals <=5 && $CI->config->item('number_of_decimals') !== NULL && $CI->config->item('number_of_decimals')!= '')
	{
		$decimals = (int)$CI->config->item('number_of_decimals');
		$decimals_system_decide = false;
	}
	
	$decimal_point = $CI->config->item('decimal_point') ? $CI->config->item('decimal_point') : '.';
	
	 $ret = number_format($number, $decimals, $use_defined_decimal_point ? $decimal_point : '.', '');
	 
	 if ($decimals_system_decide && $decimals >=2)
	 {
		 return preg_replace('/(?<=\d{2})0+$/', '', $ret);
	 }
	 else
	 {
		 return $ret;
	 }
		 
}

function make_currency_no_money($value, $decimals = 2)
{
	$CI =& get_instance();
	if($value)
	{
		$value = str_replace($CI->config->item('currency_symbol'), "", $value);
		$value = str_replace($CI->config->item('thousands_separator'), "", $value);
		$value = str_replace($CI->config->item('decimal_point'), ".", $value);
		return to_currency_no_money($value, $decimals);
	}
	return '';
}

function to_quantity($val, $show_not_set = TRUE)
{
	if ($val !== NULL)
	{
		return $val == (int)$val ? (int)$val : rtrim($val, '0');		
	}
	
	if ($show_not_set)
	{
		return lang('common_not_set');
	}
	
	return '';
	
}

function promo_price_format($val)
{
	if ($val !== NULL)
	{
		return to_currency($val);
	}
	
	return lang('common_not_set');
}
?>