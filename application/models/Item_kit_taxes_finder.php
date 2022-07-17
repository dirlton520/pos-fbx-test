<?php

class Item_kit_taxes_finder extends MY_Model
{
	/*
	Gets tax info for a particular item kit
	*/
	function get_info($item_kit_id, $transaction_type = 'sale',$the_current_item_kit=null) //Can be sale or receiving)
	{
		$CI =& get_instance();			
		$location_id_to_use = $CI && property_exists($CI,'cart') && $CI->cart->location_id ? $CI->cart->location_id : false;
		
		$this->load->model('Tax_class');
		
		if($transaction_type == 'sale')
		{			
			if ($the_current_item_kit && $the_current_item_kit->is_tax_overrided())
			{
				if ($the_current_item_kit->override_tax_class)
				{
					return $this->Tax_class->get_taxes($the_current_item_kit->override_tax_class);
				}
				else
				{
					$return = array();
				
					foreach($the_current_item_kit->get_override_tax_info() as $tax_rate_override)
					{
						$return[] = array(
						'id' => -1,
						'item_kit_id' => $item_kit_id,
						'name' => $tax_rate_override['name'],
						'percent' => $tax_rate_override['percent'],
						'cumulative' => $tax_rate_override['cumulative'],
						);
					}
				
					return $return;
				}
			}
			
			if($CI && property_exists($CI,'cart') && $CI->cart->is_tax_overrided())
			{
				if ($CI->cart->override_tax_class)
				{
					return $this->Tax_class->get_taxes($CI->cart->override_tax_class);
				}
				else
				{
					$return = array();
				
					foreach($CI->cart->get_override_tax_info() as $tax_rate_override)
					{
						$return[] = array(
						'id' => -1,
						'item_kit_id' => $item_kit_id,
						'name' => $tax_rate_override['name'],
						'percent' => $tax_rate_override['percent'],
						'cumulative' => $tax_rate_override['cumulative'],
						);
					}
				
					return $return;
				}
			}
			
			$this->load->model('Customer');
						
			if ($CI && property_exists($CI,'cart') && $delivery_tax_group_id = $this->cart->get_delivery_tax_group_id())
			{
				return $this->Tax_class->get_taxes($delivery_tax_group_id);					
			}
			
			
			if ($CI && property_exists($CI,'cart') && $this->cart->customer_id && $this->cart->get_mode() !='store_account_payment')
			{
				$customer_id = $this->cart->customer_id;
				$customer_info = $this->Customer->get_info($customer_id);
								
				if($customer_info->override_default_tax)
				{
					if ($customer_info->tax_class_id)
					{
						return $this->Tax_class->get_taxes($customer_info->tax_class_id);
					}
					
					$this->load->model('Customer_taxes');
					
					return $this->Customer_taxes->get_info($customer_id);
				}
				
				if (!$customer_info->taxable)
				{
					return array();
				}
			}
		}
		
		
		$item_kit_location_info = $this->Item_kit_location->get_info($item_kit_id, $location_id_to_use, true);
		if($item_kit_location_info->override_default_tax)
		{
			if ($item_kit_location_info->tax_class_id)
			{
				return $this->Tax_class->get_taxes($item_kit_location_info->tax_class_id);
			}
			
			return $this->Item_kit_location_taxes->get_info($item_kit_id);
		}
		
		$item_kit_info = $this->Item_kit->get_info($item_kit_id);
		
		if($transaction_type == 'sale')
		{
			$CI->load->helper('sale');
			if ($CI && property_exists($CI,'cart') && is_ebt_sale_not_ebt_cash($this->cart) && $this->config->item('enable_ebt_payments') && $item_kit_info->is_ebt_item)
			{
				return array();
			}

		}
		
		$this->load->model('Item_kit_taxes');

		if($item_kit_info->override_default_tax)
		{
			if ($item_kit_info->tax_class_id)
			{
				return $this->Tax_class->get_taxes($item_kit_info->tax_class_id);
			}
			
			return $this->Item_kit_taxes->get_info($item_kit_id);
		}
		
		if($CI->config->item('taxjar_api_key') && $transaction_type == 'sale' && property_exists($CI,'cart') && method_exists($CI->cart,'get_taxes_taxjar'))
		{
			$taxjar_taxes = $CI->cart->get_taxes_taxjar();
		
			if ($taxjar_taxes !== FALSE)
			{
				return $taxjar_taxes;
			}
		}
			
		$location_tax_class = $this->Location->get_info_for_key('tax_class_id',$location_id_to_use);
		
		if ($location_tax_class)
		{
			return $this->Tax_class->get_taxes($location_tax_class);
		}
				
		//Location Config
		$default_tax_1_rate = $this->Location->get_info_for_key('default_tax_1_rate',$location_id_to_use);
		$default_tax_1_name = $this->Location->get_info_for_key('default_tax_1_name',$location_id_to_use);
				
		$default_tax_2_rate = $this->Location->get_info_for_key('default_tax_2_rate',$location_id_to_use);
		$default_tax_2_name = $this->Location->get_info_for_key('default_tax_2_name',$location_id_to_use);
		$default_tax_2_cumulative = $this->Location->get_info_for_key('default_tax_2_cumulative',$location_id_to_use) ? $this->Location->get_info_for_key('default_tax_2_cumulative',$location_id_to_use) : 0;

		$default_tax_3_rate = $this->Location->get_info_for_key('default_tax_3_rate',$location_id_to_use);
		$default_tax_3_name = $this->Location->get_info_for_key('default_tax_3_name',$location_id_to_use);

		$default_tax_4_rate = $this->Location->get_info_for_key('default_tax_4_rate',$location_id_to_use);
		$default_tax_4_name = $this->Location->get_info_for_key('default_tax_4_name',$location_id_to_use);

		$default_tax_5_rate = $this->Location->get_info_for_key('default_tax_5_rate',$location_id_to_use);
		$default_tax_5_name = $this->Location->get_info_for_key('default_tax_5_name',$location_id_to_use);
		
		if ($default_tax_1_rate && is_numeric($default_tax_1_rate))
		{
			$return[] = array(
				'id' => -1,
				'item_kit_id' => $item_kit_id,
				'name' => $default_tax_1_name,
				'percent' => $default_tax_1_rate,
				'cumulative' => 0
			);
		}
		
		if ($default_tax_2_rate && is_numeric($default_tax_2_rate))
		{
			$return[] = array(
				'id' => -1,
				'item_kit_id' => $item_kit_id,
				'name' => $default_tax_2_name,
				'percent' => $default_tax_2_rate,
				'cumulative' => $default_tax_2_cumulative
			);
		}
				
		if ($default_tax_3_rate && is_numeric($default_tax_3_rate))
		{
			$return[] = array(
				'id' => -1,
				'item_kit_id' => $item_kit_id,
				'name' => $default_tax_3_name,
				'percent' => $default_tax_3_rate,
				'cumulative' => 0
			);
		}
		
		
		if ($default_tax_4_rate && is_numeric($default_tax_4_rate))
		{
			$return[] = array(
				'id' => -1,
				'item_kit_id' => $item_kit_id,
				'name' => $default_tax_4_name,
				'percent' => $default_tax_4_rate,
				'cumulative' => 0
			);
		}
		
		if ($default_tax_5_rate && is_numeric($default_tax_5_rate))
		{
			$return[] = array(
				'id' => -1,
				'item_kit_id' => $item_kit_id,
				'name' => $default_tax_5_name,
				'percent' => $default_tax_5_rate,
				'cumulative' => 0
			);
		}
		
		
		if (!empty($return))
		{
			return $return;
		}
		
		$store_config_tax_class = $this->config->item('tax_class_id');
		
		if ($store_config_tax_class)
		{
			return $this->Tax_class->get_taxes($store_config_tax_class);
		}
		
		
		//Global Store Config
		
		$default_tax_1_rate = $this->config->item('default_tax_1_rate');
		$default_tax_1_name = $this->config->item('default_tax_1_name');
				
		$default_tax_2_rate = $this->config->item('default_tax_2_rate');
		$default_tax_2_name = $this->config->item('default_tax_2_name');
		$default_tax_2_cumulative = $this->config->item('default_tax_2_cumulative') ? $this->config->item('default_tax_2_cumulative') : 0;

		$default_tax_3_rate = $this->config->item('default_tax_3_rate');
		$default_tax_3_name = $this->config->item('default_tax_3_name');

		$default_tax_4_rate = $this->config->item('default_tax_4_rate');
		$default_tax_4_name = $this->config->item('default_tax_4_name');

		$default_tax_5_rate = $this->config->item('default_tax_5_rate');
		$default_tax_5_name = $this->config->item('default_tax_5_name');
		
		$return = array();
		
		if ($default_tax_1_rate && is_numeric($default_tax_1_rate))
		{
			$return[] = array(
				'id' => -1,
				'item_kit_id' => $item_kit_id,
				'name' => $default_tax_1_name,
				'percent' => $default_tax_1_rate,
				'cumulative' => 0
			);
		}
		
		if ($default_tax_2_rate && is_numeric($default_tax_2_rate))
		{
			$return[] = array(
				'id' => -1,
				'item_kit_id' => $item_kit_id,
				'name' => $default_tax_2_name,
				'percent' => $default_tax_2_rate,
				'cumulative' => $default_tax_2_cumulative
			);
		}

		if ($default_tax_3_rate && is_numeric($default_tax_3_rate))
		{
			$return[] = array(
				'id' => -1,
				'item_kit_id' => $item_kit_id,
				'name' => $default_tax_3_name,
				'percent' => $default_tax_3_rate,
				'cumulative' => 0
			);
		}

		if ($default_tax_4_rate && is_numeric($default_tax_4_rate))
		{
			$return[] = array(
				'id' => -1,
				'item_kit_id' => $item_kit_id,
				'name' => $default_tax_4_name,
				'percent' => $default_tax_4_rate,
				'cumulative' => 0
			);
		}

		if ($default_tax_5_rate && is_numeric($default_tax_5_rate))
		{
			$return[] = array(
				'id' => -1,
				'item_kit_id' => $item_kit_id,
				'name' => $default_tax_5_name,
				'percent' => $default_tax_5_rate,
				'cumulative' => 0
			);
		}		
				
		return $return;
	}
}
?>