<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once('PHPPOSCartItem.php');

class PHPPOSCartItemSale extends PHPPOSCartItem
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
	
	public $tier_id;
	public $tier_name;
	public $damaged_qty;
	
	public $rule = array();
	
	public function __construct(array $params = array())
	{		
		$params['type'] = 'sale';
		$this->tier_id = 0;
		$this->tier_name = lang('common_none');
		parent::__construct($params);
	}
	
	function get_price_for_item()
	{
		$CI =& get_instance();			
		
		$item_id = $this->item_id;
		
		$giftcard_item_id  = $CI->Item->get_item_id(lang('common_giftcard'));
		if ($item_id  != $giftcard_item_id)
		{
			$tier_id = $this->tier_id ? $this->tier_id : ($this->cart && $this->cart->selected_tier_id ? $this->cart->selected_tier_id : NULL);		
		}
		else
		{
			$tier_id = NULL;
		}
		$variation_id = $this->variation_id;
		$quantity_unit_quantity = $this->quantity_unit_quantity ? $this->quantity_unit_quantity : 1;
		return $CI->Item->get_sale_price(array('item_id' => $item_id,'tier_id' => $tier_id,'variation_id' => $variation_id,'quantity_unit_quantity' => $quantity_unit_quantity));	
	}	
	
	function get_price_exclusive_of_tax()
	{
		$CI =& get_instance();

		$sale_id = $this->cart->get_previous_receipt_id();
		
		$price_to_use = $this->unit_price;
		
		$item_info = $CI->Item->get_info($this->get_id());
		if($item_info->tax_included)
		{
			if ($sale_id && !$this->cart->is_editing_previous)
			{
				$CI->load->helper('items');
				$price_to_use = get_price_for_item_excluding_taxes($this->line, $this->unit_price, $sale_id);
			}
			else
			{
				$CI->load->helper('items');
				$price_to_use = get_price_for_item_excluding_taxes($this->item_id, $this->unit_price);
			}
		}
		
		return $price_to_use;
	}
	
	function out_of_stock()
	{						
		$CI =& get_instance();
		$CI->load->model('Sale');
		$CI->load->model('Item_location');		
		$item_id = $this->get_id();
		$suspended_change_sale_id = $this->cart->get_previous_receipt_id();
		$quantity_in_sale = 0;
		
		if ($suspended_change_sale_id)
		{
			$suspended_type = $CI->Sale->get_info($suspended_change_sale_id)->row()->suspended;
			
			//Not an estiamte
			if ($suspended_type != 2)
			{
				$quantity_in_sale = $CI->Sale->get_quantity_sold_for_item_in_sale($suspended_change_sale_id, $item_id,$this->variation_id);			
			}
		}
		if ($this->variation_id)
		{
			$CI->load->model('Item_variation_location');
			$item_location_quantity = $CI->Item_variation_location->get_location_quantity($this->variation_id);
		}
		else
		{
			$item_location_quantity = $CI->Item_location->get_location_quantity($item_id);
		}
		
		if ($this->variation_id)
		{
			$quanity_added = $this->cart->get_quantity_already_added_for_variation_sales($item_id,$this->variation_id);
		}
		else
		{
			$quanity_added = $this->cart->get_quantity_already_added_sales($item_id);
		}
		
		//If $item_location_quantity is NULL we don't track quantity
		if (!$this->is_service && $item_location_quantity !== NULL && $item_location_quantity - $quanity_added  + $quantity_in_sale < 0)
		{
			return true;
		}
		
		return false;
	}
		
	function will_be_out_of_stock($additional_quantity)
	{
		$CI =& get_instance();
		$CI->load->model('Sale');
		$CI->load->model('Item_location');		
		$item_id = $this->get_id();
		
		$suspended_change_sale_id = $this->cart->get_previous_receipt_id();
		
		if ($suspended_change_sale_id)
		{
			$suspended_type = $CI->Sale->get_info($suspended_change_sale_id)->row()->suspended;
			
			//Not an estiamte
			if ($suspended_type != 2)
			{
				$quantity_in_sale = $CI->Sale->get_quantity_sold_for_item_in_sale($suspended_change_sale_id, $item_id);
			
				$additional_quantity -= $quantity_in_sale;
			}
		}
				
		if ($this->variation_id)
		{
			$CI->load->model('Item_variation_location');
			$item_location_quantity = $CI->Item_variation_location->get_location_quantity($this->variation_id);
			$quanity_added = $this->cart->get_quantity_already_added_for_variation_sales($item_id,$this->variation_id) + $additional_quantity;
		}
		else
		{
			$item_location_quantity = $CI->Item_location->get_location_quantity($item_id);
			$quanity_added = $this->cart->get_quantity_already_added_sales($item_id) + $additional_quantity;
		}
		
		
		//If $item_location_quantity is NULL we don't track quantity
		if (!$this->is_service && $item_location_quantity !== NULL && $item_location_quantity - $quanity_added < 0)
		{
			return true;
		}
		
		return false;
	}
	
	public function get_subtotal($sale_id = FALSE)
	{
		$CI =& get_instance();
		$CI->load->helper('items');
		
		if ($this->tax_included)
		{
			$price_to_use = get_price_for_item_excluding_taxes($this->item_id, $this->unit_price,$sale_id);
	    return to_currency_no_money($this->get_modifiers_subtotal_including_tax() + ($price_to_use*$this->quantity-$price_to_use*$this->quantity*$this->discount/100),10);
		}
		else
		{
			$price_to_use = $this->unit_price;				
    	return to_currency_no_money($this->get_modifiers_subtotal() + ($price_to_use*$this->quantity-$price_to_use*$this->quantity*$this->discount/100));
		}
	}
}