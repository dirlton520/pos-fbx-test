<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once('PHPPOSCartItemBase.php');

abstract class PHPPOSCartItemKit extends PHPPOSCartItemBase
{
	public $item_kit_id;
	public $type;
	public $item_kit_inactive;
	public $loyalty_multiplier;
	
	public function __construct(array $params = array())
	{		
		parent::__construct($params);
		if (!($this->type == 'sale' || $this->type == 'receiving'))
		{
		   trigger_error("A PHPPOSCartItemKit MUST have a type of sale or receiving", E_USER_ERROR);
		}
		
		//If we pass in a scan then we need to parse it and load up data into object
		if($this->scan)
		{
			$CI =& get_instance();
			
			if (strpos(strtolower($this->scan), 'kit') !== FALSE)
			{
				//KIT #
				$pieces = explode(' ',$this->scan);
				
				//We call the lookup function so it can pass though item banning
				$this->item_kit_id = $CI->Item_kit->lookup_item_kit_id((int)$pieces[1]);
				
			}
			else
			{
				$CI =& get_instance();			
				//Lookup item based on lookup order defined in store config
				$this->item_kit_id = $CI->Item_kit->lookup_item_kit_id($this->scan);
			}
			
			$this->load_item_kit_defaults($params);
		}
	}
	
	private function load_item_kit_defaults($params)
	{					
		if($this->type == 'sale')
		{
			$CI =& get_instance();			
			$cur_item_kit_info = $CI->Item_kit->get_info($this->item_kit_id);
			$cur_item_kit_location_info = $CI->Item_kit_location->get_info($this->item_kit_id);
			
			//Load up this object with any properties in cur_item_info that also exist in this class or parent
			foreach($cur_item_kit_info as $key=>$value)
			{
				//Only write to properties that exist and not already set
				if (property_exists($this,$key) && $this->$key === NULL && !isset($params[$key]))
				{
					$this->$key = $value;
				}
			}
			
			if(!isset($params['discount']))
			{
				$this->discount = 0;
			}
		
			if(!isset($params['max_discount_percent']))
			{
				$this->max_discount_percent = $cur_item_kit_info->max_discount_percent;
			}
			
			if(!isset($params['max_edit_price']))
			{
				$this->max_edit_price = $cur_item_kit_info->max_edit_price;
			}
			if(!isset($params['min_edit_price']))
			{
				$this->min_edit_price = $cur_item_kit_info->min_edit_price;
			}
			
			if(!isset($params['unit_price']))
			{
				$this->unit_price = $this->get_price_for_item_kit();
			}
			if(!isset($params['cost_price']))
			{
				$this->cost_price = ($cur_item_kit_location_info && $cur_item_kit_location_info->cost_price) ? $cur_item_kit_location_info->cost_price : $cur_item_kit_info->cost_price;
			}			
			
			if(!isset($params['regular_price']))
			{
				$this->regular_price = ($cur_item_kit_location_info && $cur_item_kit_location_info->unit_price) ? $cur_item_kit_location_info->unit_price : $cur_item_kit_info->unit_price;
			}
			
			if(!isset($params['taxable']))
			{
				$tax_info = $CI->Item_kit_taxes_finder->get_info($this->item_kit_id,$this->type);
				$this->taxable = !empty($tax_info);	
			}
			
			if(!isset($params['is_ebt_item']))
			{
				$this->is_ebt_item = $cur_item_kit_info->is_ebt_item;
			}

			if(!isset($params['disable_loyalty']))
			{
				$this->disable_loyalty = $cur_item_kit_info->disable_loyalty;
			}
			
			if (!isset($params['verify_age']))
			{
				$this->verify_age = $cur_item_kit_info->verify_age; 			
			}
			
			if (!isset($params['info_popup']))
			{
				$this->info_popup = $cur_item_kit_info->info_popup; 			
			}
			

			if (!isset($params['required_age']))
			{
				$this->required_age = $cur_item_kit_info->required_age; 			
			}
			
			if (!isset($params['modifier_items']))
			{
				$this->modifier_items = array();
			}
			
		}
		elseif($this->type == 'receiving')
		{
			$CI =& get_instance();			
			$cur_item_kit_info = $CI->Item_kit->get_info($this->item_kit_id);
			
			if (!isset($params['default_quantity']))
			{
				$this->default_quantity = $cur_item_kit_info->default_quantity; 			
			}
		}
		
		if (!isset($params['item_kit_inactive']))
		{
			$this->item_kit_inactive = $cur_item_kit_info->item_kit_inactive; 			
		}

		if (!isset($params['loyalty_multiplier']))
		{
			$this->loyalty_multiplier = $cur_item_kit_info->loyalty_multiplier ? $cur_item_kit_info->loyalty_multiplier : 1; 			
		}
		
		if (!isset($params['tag_ids']))
		{
			$CI->load->model('Tag');
			$this->tag_ids = $CI->Tag->get_tag_ids_for_item_kit($this->item_kit_id); 			
		}
	
	}
	
	public function get_id()
	{
		return $this->item_kit_id;
	}
	
	public function validate()
	{
		$CI =& get_instance();
		$CI->load->model('Item_kit');
		return $this->get_id() && !$CI->Item_kit->get_info($this->get_id())->deleted && !$CI->Item_kit->get_info($this->get_id())->item_kit_inactive; 
	}
	
	public function get_items()
	{
		$CI =& get_instance();
		$return = array();
		
		foreach ($CI->Item_kit_items->get_info($this->item_kit_id) as $item_kit_item)
		{
			if (get_class($this) == 'PHPPOSCartItemKitRecv')
			{
				$item = new PHPPOSCartItemRecv(array('cart' => $this->cart, 'scan' => $item_kit_item->item_id.($item_kit_item->item_variation_id ? '#'.$item_kit_item->item_variation_id : '').'|FORCE_ITEM_ID|','quantity' => $item_kit_item->quantity));
			}
			else
			{
				$item = new PHPPOSCartItemSale(array('cart' => $this->cart,'scan' => $item_kit_item->item_id.($item_kit_item->item_variation_id ? '#'.$item_kit_item->item_variation_id : '').'|FORCE_ITEM_ID|','quantity' => $item_kit_item->quantity));				
			}
			$return[] = $item;
		}
		
		return $return;
	}
}