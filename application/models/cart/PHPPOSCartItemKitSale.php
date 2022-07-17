<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once('PHPPOSCartItemKit.php');

class PHPPOSCartItemKitSale extends PHPPOSCartItemKit
{
	public $regular_price;
	public $change_cost_price;
	public $max_discount_percent;
	public $max_edit_price;
	public $min_edit_price;
	public $tax_included;
	public $is_ebt_item;
	public $disable_loyalty;
	public $required_age;
	public $verify_age;
	public $info_popup;
	public $dynamic_pricing;

	public $tier_id;
	public $tier_name;

	public $rule = array();

	public function __construct(array $params = array())
	{		
		$params['type'] = 'sale';
		$this->tier_id = 0;
		$this->tier_name = lang('common_none');
		parent::__construct($params);
	}
	
	function get_cost_price_for_kit()
	{
		$return = 0;
		
		foreach($this->get_items() as $item)
		{
			$return+=$item->cost_price*$item->quantity;
		}
		
		//We need to do this to match behavior of returning just 0 (price rules when cost is 0)
		return !$return ? '0.00': $return;
	}
	
	function get_selling_price_for_kit()
	{
		$return = 0;
		
		foreach($this->get_items() as $item)
		{
			$return+=$item->unit_price*$item->quantity;
		}
	
		//We need to do this to match behavior of returning just 0 (price rules when selling is 0)
		return !$return ? '0.00': $return;
	}	
	
	function get_price_for_item_kit()
	{
		$CI =& get_instance();			
		
		$item_kit_id = $this->item_kit_id;
		$tier_id = $this->tier_id ? $this->tier_id : $this->cart->selected_tier_id;		
		return $CI->Item_kit->get_sale_price(array('item_kit_id' => $item_kit_id,'tier_id' => $tier_id));	
	}	
	
	
	function get_price_exclusive_of_tax()
	{
		$CI =& get_instance();
		
		$sale_id = $this->cart->get_previous_receipt_id();		
		$price_to_use = $this->unit_price;
		
		$item_kit_info = $CI->Item_kit->get_info($this->get_id());
		if($item_kit_info->tax_included)
		{
			if ($sale_id && !$this->cart->is_editing_previous)
			{
				$CI->load->helper('item_kits');
				$price_to_use = get_price_for_item_kit_excluding_taxes($this->line, $this->unit_price, $sale_id);
			}
			else
			{
				$CI->load->helper('item_kits');
				$price_to_use = get_price_for_item_kit_excluding_taxes($this->item_kit_id, $this->unit_price);
			}
		}
		
		return $price_to_use;
	}
	public function get_id()
	{
		return $this->item_kit_id;
	}
	
	function out_of_stock()
	{
		$CI =& get_instance();
		$CI->load->model('Sale');
		$CI->load->model('Item_location');		
		$CI->load->model('Item_variation_location');		
		$CI->load->model('Item_kit');		
		$CI->load->model('Item_kit_items');		
		
		$kit_id = $this->get_id();
		
 		$suspended_change_sale_id = $this->cart->get_previous_receipt_id();
 		$quantity_in_sale = 0;
		
 		if ($suspended_change_sale_id)
 		{
			$suspended_type = $CI->Sale->get_info($suspended_change_sale_id)->row()->suspended;
			
			//Not an estiamte
			if ($suspended_type != 2)
			{
 				$quantity_in_sale = $CI->Sale->get_quantity_sold_for_item_kit_in_sale($suspended_change_sale_id, $kit_id);			
			}
		}
		 
	    //Get All Items for Kit
	    $kit_items = $CI->Item_kit_items->get_info($kit_id);

	    //Check each item
	    foreach ($kit_items as $item)
	    {
				if (!$item->is_service)
				{
					if ($item->item_variation_id)
					{
						$item_location_quantity = $CI->Item_variation_location->get_location_quantity($item->item_variation_id);
						$item_already_added = $this->cart->get_quantity_already_added_for_variation($item->item_id,$item->item_variation_id);
					}
					else
					{
						$item_location_quantity = $CI->Item_location->get_location_quantity($item->item_id);
						$item_already_added = $this->cart->get_quantity_already_added($item->item_id);
					}

					if ($item_location_quantity !== NULL && $item_location_quantity - $item_already_added + $CI->Item_kit->get_quantity_to_be_added_from_kit($kit_id, $item->item_id, $quantity_in_sale) < 0)
					{
			  			return true;
					}
				}			
	    }
	    return false;
	}
	
	function will_be_out_of_stock($additional_quantity)
	{
			$CI =& get_instance();
			$CI->load->model('Sale');
			$CI->load->model('Item_location');		
			$CI->load->model('Item_variation_location');		
			$CI->load->model('Item_kit');		
			$CI->load->model('Item_kit_items');		
		
			$kit_id = $this->get_id();
			$suspended_change_sale_id = $this->cart->get_previous_receipt_id();
		
			if ($suspended_change_sale_id)
			{
				$suspended_type = $CI->Sale->get_info($suspended_change_sale_id)->row()->suspended;
			
				//Not an estiamte
				if ($suspended_type != 2)
				{
					$quantity_in_sale = $CI->Sale->get_quantity_sold_for_item_kit_in_sale($suspended_change_sale_id, $kit_id);
			
					$additional_quantity -= $quantity_in_sale;
				}
			}
		
	    //Get All Items for Kit
	    $kit_items = $CI->Item_kit_items->get_info($kit_id);

	    //Check each item
	    foreach ($kit_items as $item)
	    {
				if (!$item->is_service)
				{
					if ($item->item_variation_id)
					{
						$item_location_quantity = $CI->Item_variation_location->get_location_quantity($item->item_variation_id);
						$item_already_added = $this->cart->get_quantity_already_added_for_variation($item->item_id,$item->item_variation_id) + $additional_quantity;
					}
					else
					{
						$item_location_quantity = $CI->Item_location->get_location_quantity($item->item_id);
						$item_already_added = $this->cart->get_quantity_already_added($item->item_id) + $CI->Item_kit->get_quantity_to_be_added_from_kit($kit_id, $item->item_id, $additional_quantity);
					}
					if ($item_location_quantity !== NULL && $item_location_quantity - $item_already_added < 0)
					{
			  			return true;
						}	
					}
			}
	    return false;
	}
	
	public function get_subtotal($sale_id = FALSE)
	{
		$CI =& get_instance();
		
		$CI->load->helper('item_kits');
		
		if ($this->tax_included)
		{			
			$price_to_use = get_price_for_item_kit_excluding_taxes($this->item_kit_id, $this->unit_price,$sale_id);
	    return to_currency_no_money($this->get_modifiers_subtotal_including_tax() + ($price_to_use*$this->quantity-$price_to_use*$this->quantity*$this->discount/100),10);
		}
		else
		{
			$price_to_use = $this->unit_price;				
    	return to_currency_no_money($this->get_modifiers_subtotal() + ($price_to_use*$this->quantity-$price_to_use*$this->quantity*$this->discount/100));
		}
	}
}