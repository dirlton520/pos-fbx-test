<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once('PHPPOSCart.php');

require_once('PHPPOSCartItemSale.php');
require_once('PHPPOSCartItemKitSale.php');
require_once('PHPPOSCartPaymentSale.php');

require_once('PHPPOSCartDelivery.php');
require_once APPPATH.'libraries/taxjar/vendor/autoload.php';		

class PHPPOSCartSale extends PHPPOSCart
{		
	public $sale_id;
	public $return_sale_id;
	public $customer_id;
	public $show_comment_on_receipt;
	public $selected_tier_id;
	public $previous_tier_id;
	public $sold_by_employee_id;
	public $discount_reason;
	public $has_delivery;
	public $delivery;
	
	public $partial_transactions;
	public $use_cc_saved_info;
	public $invoice_no;
	public $prompt_for_card;
	public $ebt_voucher;
	public $ebt_voucher_no;
	public $ebt_auth_code;
	public $save_credit_card_info;
	public $use_saved_cc_info;
	
	public $redeem_discount;
	
	public $sale_exchange_details;
	
	public $coupons;
	
	public $is_ecommerce;
	public $integrated_gift_card_balances;	
	//Age of customer; used for age verified items
	public $age;
	
	public $taxjar_taxes;
	
	public $was_last_edit_quantity;
	public function __construct(array $params=array())
	{
		self::setup_defaults();
		parent::__construct($params);
	}
	
	public function is_valid_receipt($receipt_sale_id)
	{
		$CI =& get_instance();
		
		//POS #
		$pieces = explode(' ',$receipt_sale_id);
		if(count($pieces)==2 && strtolower($pieces[0]) == strtolower($CI->config->item('sale_prefix') ? $CI->config->item('sale_prefix') : 'POS'))
		{
			return $CI->Sale->exists($pieces[1]);
		}
		return false;	
	}
	
	public function return_order($receipt_sale_id)
	{
		$CI =& get_instance();
		
		$pieces = explode(' ',$receipt_sale_id);
		
		if(count($pieces)==2 && strtolower($pieces[0]) == strtolower($CI->config->item('sale_prefix') ? $CI->config->item('sale_prefix') : 'POS'))
		{
			$sale_id = $pieces[1];
		}
		else
		{
			$sale_id = $receipt_sale_id;
		}
		
		$this->return_sale_id = $sale_id;
		$previous_cart = PHPPOSCartSale::get_instance_from_sale_id($sale_id, NULL, TRUE, FALSE);
		
		for($k=1;$k<=NUMBER_OF_PEOPLE_CUSTOM_FIELDS;$k++) 
		{
			$this->{"custom_field_${k}_value"} =$previous_cart->{"custom_field_${k}_value"};
			$this->{"work_order_custom_field_${k}_value"} =$previous_cart->{"work_order_custom_field_${k}_value"};
		}
		
		$this->set_excluded_taxes($previous_cart->get_excluded_taxes());
		
		$this->customer_id = $previous_cart->customer_id;
		$this->selected_tier_id = $previous_cart->selected_tier_id;
		$this->has_delivery = $previous_cart->get_has_delivery();
		$this->return_cart_items($previous_cart->get_items());
		
		
		$CI->load->model('Sale');
		$sale_info = $CI->Sale->get_info($sale_id)->row();
		
		if ($sale_info->store_account_payment)
		{
			$this->set_mode('store_account_payment');
			foreach($this->get_items() as $item)
			{
				$item->quantity  = abs($item->quantity);
				$item->unit_price = $item->unit_price*-1;
			}
			
			$this->save();
		}
		
		$this->set_excluded_taxes($previous_cart->get_excluded_taxes());
		
		if ($CI->Delivery->has_delivery_for_sale($sale_id))
		{
			$this->set_delivery_person_info($CI->Delivery->get_delivery_person_info_by_sale_id($sale_id));
			
			$delivery_info = $CI->Delivery->get_info_by_sale_id($sale_id)->row_array();
			unset($delivery_info['sale_id']);
			unset($delivery_info['id']);
			$this->set_delivery_info($delivery_info);
			$this->set_delivery_tax_group_id($CI->Delivery->get_delivery_tax_group_id($sale_id));
			$this->set_has_delivery(1);
		}
		$this->save();
	}
	public static function get_instance_from_sale_id($sale_id,$cart_id = NULL,$is_editing_previous = FALSE,$copy_price_rules = TRUE)
	{
		//MAKE SURE YOU NEVER set location_id, employee_id, or register_id in this method
		//This is because this will overwrite whatever we actual have for our context.
		//Setting these properties are just for the API
		
		$CI =& get_instance();
		$CI->load->model('Tier');
		$CI->load->model('Customer');
		$CI->load->model('Item_modifier');
		$cart = new PHPPOSCartSale(array('sale_id' => $sale_id,'cart_id' => $cart_id,'mode' => 'sale','is_editing_previous' => $is_editing_previous));
		$sale_info = $CI->Sale->get_info($sale_id)->row_array();
		$work_order_info = $CI->Work_order->get_info_by_sale_id($sale_id)->row_array();
		$cart->return_sale_id = $sale_info['return_sale_id'];
		$paid_store_accounts = $CI->Sale->get_store_accounts_paid_sales($sale_id);
		
		for($k=1;$k<=NUMBER_OF_PEOPLE_CUSTOM_FIELDS;$k++) 
		{
			$cart->{"custom_field_${k}_value"} = $sale_info["custom_field_${k}_value"];
			if ($work_order_info)
			{
				$cart->{"work_order_custom_field_${k}_value"} = $work_order_info["custom_field_${k}_value"];
			}
		}
		
		foreach($paid_store_accounts as $paid_store_account)
		{
			$cart->add_paid_store_account_payment_id($paid_store_account['sale_id'],$paid_store_account['partial_payment_amount']);
		}
		
		foreach($CI->Sale->get_sale_items($sale_id)->result() as $row)
		{
			$item_props = array();
			
			$modifiers = $CI->Sale->get_sale_item_modifiers($row->sale_id,$row->item_id,$row->line)->result_array();
			
			$modifier_unit_total = 0;
			$modifier_unit_cost_total = 0;
			
			foreach($modifiers as $modifier)
			{
				$modifier_unit_total += $modifier['unit_price'];
				$modifier_unit_cost_total += $modifier['cost_price'];
				$modifier_item_info = $CI->Item_modifier->get_sale_modifier_item_info($modifier['modifier_item_id'],$row->sale_id,$row->item_id,$row->line);
				
				$display_name = to_currency($modifier_item_info['unit_price']).': '.$modifier_item_info['modifier_name'].' > '.$modifier_item_info['modifier_item_name'];
				
				$item_props['modifier_items'][$modifier['modifier_item_id']] = array('display_name' => $display_name, 'unit_price' => $modifier['unit_price'],'cost_price' => $modifier['cost_price']);
			}
			
			$cur_item_info = $CI->Item->get_info($row->item_id);

			$item_props['cart'] = $cart;
			$item_props['item_id'] = $row->item_id;
			$item_props['is_series_package'] = $cur_item_info->is_series_package;
			
			if ($row->series_id)
			{
				$series_info = $CI->Customer->get_series_info($row->series_id);
				$item_props['series_quantity'] = $series_info->quantity_remaining;
				$CI->load->helper('date');
				$item_props['series_days_to_use_within'] = days_until($series_info->expire_date);
			}
			
			$item_props['variation_id'] = $row->item_variation_id;
			$item_props['tier_id'] = $row->tier_id ? $row->tier_id : 0;	
			$tinfo = $CI->Tier->get_info($row->tier_id);
			$item_props['tier_name'] = $tinfo->name;
			
			if($row->item_variation_id)
			{
				$CI->load->model('Item_variations');
				$variations = $CI->Item_variations->get_variations($row->item_id);
				$item_props['variation_choices']= array();
		
				foreach($variations as $item_variation_id=>$variation)
				{
					
					$item_props['variation_choices'][$item_variation_id] = $variation['name'] ? $variation['name'] : implode(', ', array_column($variation['attributes'],'label'));
				}
				
				if ($row->item_variation_id)
				{
					$item_props['variation_name'] = $item_props['variation_choices'][$row->item_variation_id];
				}			
			}
			
			$item_props['taxable'] = $row->tax!=0;
			$item_props['tax_included'] = $cur_item_info->tax_included;
			
			$item_props['existed_previously'] = TRUE;
			$item_props['line'] = $row->line;
			$item_props['receipt_line_sort_order'] = $row->receipt_line_sort_order;
			$item_props['name'] = $cur_item_info->name;
			$item_props['category_id'] = $cur_item_info->category_id;
			$item_props['size'] = $cur_item_info->size;
			$item_props['item_number'] = $cur_item_info->item_number;
			$item_props['main_image_id'] = $cur_item_info->image_id;
			$item_props['product_id'] = $cur_item_info->product_id;
			$item_props['allow_alt_description'] = $cur_item_info->allow_alt_description;
			$item_props['is_serialized'] = $cur_item_info->is_serialized;
			
			$item_props['quantity'] = $row->quantity_purchased;
			$item_props['damaged_qty'] = $row->damaged_qty;
			$item_props['unit_price'] = $row->item_unit_price - $modifier_unit_total;
			$item_props['regular_price'] = $row->regular_item_unit_price_at_time_of_sale  - $modifier_unit_total;
			$item_props['allow_price_override_regardless_of_permissions'] = $cur_item_info->allow_price_override_regardless_of_permissions;
						
			if ($item_props['tax_included'] && $cart->is_editing_previous && $item_props['taxable'])
			{
				$CI->load->helper('items');
				
				if ($row->item_id == $CI->Item->get_item_id_for_flat_discount_item() && $CI->config->item('flat_discounts_discount_tax'))
				{
					$tax_info = $CI->Sale->get_sale_items_taxes_by_item_id($sale_id,$row->item_id);
					
					$total_tax_percent = 0;
	
					foreach($tax_info as $tax)
					{
						$total_tax_percent+=$tax['percent'];
					}
	
					$item_props['unit_price'] = $item_props['unit_price']*(1+($total_tax_percent /100));
					
				}
				else
				{
					$item_props['unit_price'] = to_currency_no_money(get_price_for_item_including_taxes($row->item_id, $item_props['unit_price']));
				}
			}
			
			$quantity_units = $CI->Item->get_quantity_units($row->item_id);
			$item_props['quantity_units'] = array();
			foreach($quantity_units as $qu)
			{
				$item_props['quantity_units'][$qu->id] = $qu->unit_name;
			}			
			$item_props['quantity_unit_id'] = $row->items_quantity_units_id;
			$item_props['quantity_unit_quantity'] = $row->unit_quantity;
						
			//Sale or layaway or we aren't editing a previous sale then we want to show cost price in db
			if($sale_info['suspended'] <=1 || !$is_editing_previous || $CI->config->item('dont_recalculate_cost_price_when_unsuspending_estimates'))
			{
				
				if ($row->items_quantity_units_id)
				{
					$qui = $CI->Item->get_quantity_unit_info($row->items_quantity_units_id);
					
					$item_props['cost_price'] = ($row->item_cost_price/$row->unit_quantity) - $modifier_unit_cost_total;
				}
				else
				{
					$item_props['cost_price'] = $row->item_cost_price - $modifier_unit_cost_total;
				}
			}
			else //estiamtes and custom sale types
			{
				$cur_item_location_info = $CI->Item_location->get_info($row->item_id);
				
				if ($row->item_variation_id)
				{
					$cur_item_variation_info = $CI->Item_variations->get_info($row->item_variation_id);
				}
				else
				{
					$cur_item_variation_info = FALSE;
				}
				
				if (isset($cur_item_variation_info) && $cur_item_variation_info && $cur_item_variation_info->cost_price)
				{
					 $item_props['cost_price'] = $cur_item_variation_info->cost_price;
				}
				elseif($row->items_quantity_units_id)
				{
					$qui = $CI->Item->get_quantity_unit_info($row->items_quantity_units_id);
				 	$item_props['cost_price'] = $qui->cost_price;				
				}
				else
				{
					$item_props['cost_price'] = ($cur_item_location_info && $cur_item_location_info->cost_price) ? $cur_item_location_info->cost_price : $cur_item_info->cost_price;
				}
				
			}
			$item_props['change_cost_price'] = $cur_item_info->change_cost_price;
			$item_props['max_discount_percent'] = $cur_item_info->max_discount_percent;
			$item_props['max_edit_price'] = $cur_item_info->max_edit_price;
			$item_props['min_edit_price'] = $cur_item_info->min_edit_price;
			$item_props['is_ebt_item'] = $cur_item_info->is_ebt_item;
			$item_props['disable_loyalty'] = $cur_item_info->disable_loyalty;
			$item_props['discount'] = $row->discount_percent;
			$item_props['description'] = $row->description;
			$item_props['serialnumber'] = $row->serialnumber;
			$item_props['quantity_received'] = $row->quantity_received;
			
			$item_props['system_item'] = $cur_item_info->system_item;
			
			$CI->load->model('Tag');
			$item_props['tag_ids'] = $CI->Tag->get_tag_ids_for_item($row->item_id); 			
			
			$item_props['loyalty_multiplier'] = $row->loyalty_multiplier ? $row->loyalty_multiplier : 1;

			$item = new PHPPOSCartItemSale($item_props);
			
			if ($row->override_taxes)
			{
				$item->set_override_taxes(unserialize($row->override_taxes));
			}
			
			if (count($item->get_taxes()) == 0 )
			{
				$item->tax_included = 0;
			}
			
			if ($copy_price_rules)
			{
				if ($row->rule_id)
				{
					$CI->load->model('Price_rule');
					$rule = $CI->Price_rule->get_rule_info($row->rule_id);
				
					//Don't do this for advanced discount
					if (!($rule['type'] == 'advanced_discount'))
					{
						$item->rule = $rule;
						$item->rule['rule_discount'] = $cart->get_rule_discount($rule,$item);
					
					}
				}
			}
			
			$cart->add_item($item);
		}

		foreach($CI->Sale->get_sale_item_kits($sale_id)->result() as $row)
		{
			$item_kit_props = array();
			
			$modifiers = $CI->Sale->get_sale_item_kit_modifiers($row->sale_id,$row->item_kit_id,$row->line)->result_array();
			
			$modifier_unit_total = 0;
			$modifier_unit_cost_total = 0;
			
			foreach($modifiers as $modifier)
			{
				$modifier_unit_total += $modifier['unit_price'];
				$modifier_unit_cost_total += $modifier['cost_price'];
				
				$modifier_item_info = $CI->Item_modifier->get_sale_modifier_item_kit_info($modifier['modifier_item_id'],$row->sale_id,$row->item_kit_id,$row->line);
				
				$display_name = to_currency($modifier_item_info['unit_price']).': '.$modifier_item_info['modifier_name'].' > '.$modifier_item_info['modifier_item_name'];
				
				$item_kit_props['modifier_items'][$modifier['modifier_item_id']] = array('display_name' => $display_name, 'unit_price' => $modifier['unit_price'],'cost_price' => $modifier['cost_price']);
			}
			
			$cur_item_kit_info = $CI->Item_kit->get_info($row->item_kit_id);
			$item_kit_props['cart'] = $cart;
			$item_kit_props['item_kit_id'] = $row->item_kit_id;
			$item_kit_props['tier_id'] = $row->tier_id ? $row->tier_id : 0;	
			$tinfo = $CI->Tier->get_info($row->tier_id);
			$item_kit_props['tier_name'] = $tinfo->name;
			
			$item_kit_props['taxable'] = $row->tax!=0;
			$item_kit_props['tax_included'] = $cur_item_kit_info->tax_included;
			
			$item_kit_props['existed_previously'] = TRUE;
			$item_kit_props['line'] = $row->line;
			$item_kit_props['receipt_line_sort_order'] = $row->receipt_line_sort_order;
			$item_kit_props['name'] = $cur_item_kit_info->name;
			$item_kit_props['item_number'] = $cur_item_kit_info->item_kit_number;
			$item_kit_props['main_image_id'] = $cur_item_kit_info->main_image_id;
			
			$item_kit_props['product_id'] = $cur_item_kit_info->product_id;
			
			$item_kit_props['quantity'] = $row->quantity_purchased;
			$item_kit_props['unit_price'] = $row->item_kit_unit_price - $modifier_unit_total;
			$item_kit_props['regular_price'] = $row->regular_item_kit_unit_price_at_time_of_sale - $modifier_unit_total;
			
			if ($item_kit_props['tax_included'] && $cart->is_editing_previous && $item_kit_props['taxable'])
			{
				$CI->load->helper('item_kits');
				$item_kit_props['unit_price'] = to_currency_no_money(get_price_for_item_kit_including_taxes($row->item_kit_id, $item_kit_props['unit_price']));
			}
			
			$item_kit_props['cost_price'] = $row->item_kit_cost_price - $modifier_unit_cost_total;
			$item_kit_props['change_cost_price'] = $cur_item_kit_info->change_cost_price;
			$item_kit_props['max_discount_percent'] = $cur_item_kit_info->max_discount_percent;
			$item_kit_props['max_edit_price'] = $cur_item_kit_info->max_edit_price;
			$item_kit_props['min_edit_price'] = $cur_item_kit_info->min_edit_price;
			$item_kit_props['is_ebt_item'] = $cur_item_kit_info->is_ebt_item;
			$item_kit_props['disable_loyalty'] = $cur_item_kit_info->disable_loyalty;
			
			$item_kit_props['discount'] = $row->discount_percent;
			$item_kit_props['description'] = $row->description;
			
			$CI->load->model('Tag');
			$item_kit_props['tag_ids'] = $CI->Tag->get_tag_ids_for_item_kit($row->item_kit_id); 			
			
			$item_kit_props['loyalty_multiplier'] = $row->loyalty_multiplier ? $row->loyalty_multiplier : 1;
			
			$item_kit = new PHPPOSCartItemKitSale($item_kit_props);
			if ($row->override_taxes)
			{
				$item_kit->set_override_taxes(unserialize($row->override_taxes));
			}
			
			if (count($item_kit->get_taxes()) == 0 )
			{
				$item_kit->tax_included = 0;
			}
			
			if ($copy_price_rules)
			{
				if ($row->rule_id)
				{
					$CI->load->model('Price_rule');
					$rule = $CI->Price_rule->get_rule_info($row->rule_id);
					$item_kit->rule = $rule;
					$item->rule['rule_discount'] = $cart->get_rule_discount($rule,$item_kit);
				}
			}

			$cart->add_item($item_kit);
		}

		$cart->customer_id = $CI->Sale->get_customer($sale_id)->person_id;
		$cart->show_comment_on_receipt = $sale_info['show_comment_on_receipt'];
		$cart->is_ecommerce = $sale_info['is_ecommerce'];
		$cart->suspended = $sale_info['suspended'];
		$cart->comment = $sale_info['comment'];
		$cart->set_exchange_details($CI->Sale->get_exchange_details($sale_id));
		
		$exchange_rate = $cart->get_exchange_rate() ? $cart->get_exchange_rate() : 1;
		foreach($CI->Sale->get_sale_payments($sale_id)->result_array() as $row)
		{
			$row['payment_amount'] = $row['payment_amount']*$exchange_rate;
			$cart->add_payment(new PHPPOSCartPaymentSale($row));
		}
		

		$cart->set_excluded_taxes($CI->Sale->get_deleted_taxes($sale_id));
		$cart->set_override_taxes($CI->Sale->get_override_taxes($sale_id));
		$CI->load->model('Delivery');
		
		if ($CI->Delivery->has_delivery_for_sale($sale_id))
		{
			$cart->set_delivery_person_info($CI->Delivery->get_delivery_person_info_by_sale_id($sale_id));
			$cart->set_delivery_info($CI->Delivery->get_info_by_sale_id($sale_id)->row_array());
			$cart->set_delivery_tax_group_id($CI->Delivery->get_delivery_tax_group_id($sale_id));
			$cart->set_has_delivery(1);
		}
		
		$cart->selected_tier_id = $sale_info['tier_id'];
		$cart->sold_by_employee_id = $sale_info['sold_by_employee_id'];
		$cart->discount_reason = $sale_info['discount_reason'];
		return $cart;
		
	}
		
	public static function get_instance($cart_id)
	{
		static $instance = array();
		
		if (isset($instance[$cart_id]))
		{
			return $instance[$cart_id];
		}
		
		$CI =& get_instance();
		if ($data = $CI->session->userdata($cart_id))
		{
			$instance[$cart_id] = unserialize($data);			
			return $instance[$cart_id];
		}
		return new PHPPOSCartSale(array('cart_id' => $cart_id, 'mode' => 'sale'));
	}
	
	
	function setup_defaults()
	{
		$this->set_mode('sale');
		$this->sale_id = NULL;
		$this->return_sale_id = NULL;
		$this->customer_id = NULL;
		$this->discount_reason = '';
		$this->sold_by_employee_id = NULL;
		$this->show_comment_on_receipt = FALSE;
		$this->has_delivery = FALSE;
		$this->selected_tier_id = 0;
		$this->previous_tier_id = 0;
		$this->partial_transactions = array();
		$this->delivery = new PHPPOSCartDelivery();
		$this->use_cc_saved_info = FALSE;
		$this->invoice_no = '';
		$this->prompt_for_card = FALSE;
		$this->ebt_voucher = '';
		$this->ebt_auth_code = '';
		$this->ebt_voucher_no = '';
		$this->save_credit_card_info = FALSE;
		$this->use_saved_cc_info = FALSE;
		$this->redeem_discount = FALSE;
		$this->sale_exchange_details = '';		
		$this->coupons = array();
		$this->age = NULL;
		$this->integrated_gift_card_balances = array();	
		$this->taxjar_taxes = array();	
		$this->was_last_edit_quantity = FALSE;
	}
	
	public function get_previous_receipt_id()
	{
		return $this->sale_id;
	}
		
	public function add_item_kit(PHPPOSCartItemKit $item_kit_to_add,$options = array())
	{
		$CI =& get_instance();
				
		//if we have prices set then we want to add as a single unit
		if($item_kit_to_add->cost_price!==NULL && $item_kit_to_add->unit_price!==NULL)
		{
			if(isset($options['replace']) && $options['replace'])
			{
				if (!($similar_item = $this->find_similiar_item($item_kit_to_add)))
				{
					return $this->add_item($item_kit_to_add);
				}	
				else
				{
					return $this->replace($this->get_item_index($similar_item), $item_kit_to_add);
				}
			}
			else
			{
				if ((isset($options['no_group']) && $options['no_group']) || $CI->config->item('do_not_group_same_items') || !($similar_item = $this->find_similiar_item($item_kit_to_add)))
				{
					$this->add_item($item_kit_to_add);
				}	
				else
				{
					//If our similiar item has a rule on it; then we want to add directly to cart instead of merging so rules apply correctly
					if(isset($similar_item->rule['type']) && (in_array($similar_item->rule['type'], array('buy_x_get_discount','buy_x_get_y_free', 'simple_discount'))))
					{
						return $this->add_item($item_kit_to_add);
					}
					else
					{
						return $this->merge_item($item_kit_to_add, $similar_item);	
					}
				}
			}	
		}
		else
		{
			for($k=0;$k<abs($item_kit_to_add->quantity);$k++)
			{
		    foreach($item_kit_to_add->get_items() as $item_kit_item)
		    {
					if($item_kit_to_add->quantity < 0)
					{
							$item_kit_item->quantity = $item_kit_item->quantity*-1;
					}
					
					if ((isset($options['no_group']) && $options['no_group']) || $CI->config->item('do_not_group_same_items') || !($similar_item = $this->find_similiar_item($item_kit_item)))
					{
						$this->add_item($item_kit_item);
					}	
					else
					{
						$this->merge_item($item_kit_item, $similar_item);
					}
				}		
			}
		}
		
		return TRUE;
	}
	
	function is_tax_inclusive()
	{
		$CI =& get_instance();
		
		$is_tax_inclusive = FALSE;
		
		foreach($this->get_items() as $item)
		{
			if (get_class($item) == 'PHPPOSCartItemSale')
			{
				$cur_item_info = $CI->Item->get_info($item->item_id);
				if ($cur_item_info->tax_included)
				{
					$is_tax_inclusive = TRUE;
					break;
				}
			}
			elseif(get_class($item) == 'PHPPOSCartItemKitSale')
			{
				$cur_item_kit_info = $CI->Item_kit->get_info($item->item_kit_id);
				
				if ($cur_item_kit_info->tax_included)
				{
					$is_tax_inclusive = TRUE;
					break;
				}
				
			}
		}
		
		return $is_tax_inclusive;		
	}
	
	public function __toString()
	{
		$CI =& get_instance();
		$CI->load->model('Sale');
		$sale_data = $this->to_array();
		
		if (isset($sale_data['sale_id']) && $sale_data['sale_id'])
		{
			$sale_info = $CI->Sale->get_info($sale_data['sale_id'])->row();
			$sale_time = date(get_date_format().' '.get_time_format(), strtotime($sale_info->sale_time));
		}
		else
		{
			$sale_time = date(get_date_format().' '.get_time_format());
		}
		
		$return = lang('common_company').': '.$CI->config->item('company')."\n";
		$return .= lang('common_sale_date').': '.$sale_time."\n";
		$return .= lang('common_sale_id').': '.$sale_data['sale_id']."\n";
		if ($sale_data['customer'])
		{
			$return .= lang('common_customer_name').': '.$sale_data['customer']."\n";
		}
		$return .= lang('common_sub_total').': '.str_replace('<span style="white-space:nowrap;">-</span>', '-',to_currency($this->get_subtotal()))."\n";
		$return .= lang('common_tax').': '.str_replace('<span style="white-space:nowrap;">-</span>', '-',to_currency($this->get_total() - $this->get_subtotal()))."\n";
		$return .= lang('common_total').': '.str_replace('<span style="white-space:nowrap;">-</span>', '-',to_currency($this->get_total()))."\n";
		
		if ( $CI->Location->get_info_for_key('twilio_sms_from'))
		{
			require_once (APPPATH."libraries/hashids/vendor/autoload.php");
		
			$hashids = new Hashids\Hashids(base_url());
			$sms_id = $hashids->encode($sale_data['sale_id']);
		
			$return .= site_url('r/'.$sms_id);
		}
		return $return;
		
	}
	
	public function to_array()
	{
		$CI =& get_instance();
		
		$data = array();
		$data['show_comment_on_receipt'] = $this->show_comment_on_receipt;
		$data['is_ecommerce'] = $this->is_ecommerce;
		$data['return_sale_id'] = $this->return_sale_id;
		$customer_id = $this->customer_id;
		
		if($customer_id)
		{
			$data['customer_id'] = $customer_id;
			$cust_info=$CI->Customer->get_info($customer_id);
			$data['customer']=$cust_info->first_name.' '.$cust_info->last_name.($cust_info->account_number==''  ? '':' - '.$cust_info->account_number);
			$data['customer_company']= $cust_info->company_name;
			$data['customer_has_address'] = $CI->Customer->does_customer_have_address($customer_id);
			$data['customer_balance'] = $cust_info->balance;
			$data['is_over_credit_limit'] = $CI->Customer->is_over_credit_limit($customer_id,$this->get_payment_amount(lang('common_store_account')));
			$data['customer_credit_limit'] = $cust_info->credit_limit;
			$data['sales_until_discount'] = ($CI->config->item('number_of_sales_for_discount')) ? (float)$CI->config->item('number_of_sales_for_discount') - (float)$cust_info->current_sales_for_discount : 0;
			
			$data['customer_address_1'] = $cust_info->address_1;
			$data['customer_address_2'] = $cust_info->address_2;
			$data['customer_city'] = $cust_info->city;
			$data['customer_state'] = $cust_info->state;
			$data['customer_zip'] = $cust_info->zip;
			$data['customer_country'] = $cust_info->country;
			$data['customer_phone'] = $cust_info->phone_number;
			$data['customer_email'] = $cust_info->email;
			$data['avatar']=$cust_info->image_id ?  app_file_url($cust_info->image_id) : base_url()."assets/img/user.png"; //can be changed to  base_url()."img/avatar.png" if it is required
			$data['disable_loyalty'] = $cust_info->disable_loyalty;
			$data['points'] = to_currency_no_money($cust_info->points);
			$data['customer_cc_token'] = $cust_info->cc_token;
			$data['customer_cc_preview'] = $cust_info->cc_preview;
			$data['save_credit_card_info'] = $this->save_credit_card_info;
			$data['use_saved_cc_info'] = $this->use_saved_cc_info;
			$data['customer_points'] = $cust_info->points;
			
			if($CI->config->item('customers_store_accounts'))
			{
				$data['customer_balance_for_sale'] = $cust_info->balance;
			}
		}
		else
		{
	 	 $data['customer_id'] = NULL;
	 	 $data['customer'] = NULL;
		}
		
		$data['selected_tier_id'] = $this->selected_tier_id ? $this->selected_tier_id : 0;
		$data['previous_tier_id'] = $this->previous_tier_id ? $this->previous_tier_id : 0;
		$data['sold_by_employee_id'] = $this->sold_by_employee_id;
		$data['discount_reason'] = $this->discount_reason;	
		$data['is_tax_inclusive'] = $this->is_tax_inclusive();
		
		$data['has_delivery'] = $this->has_delivery;
		$data['delivery_info'] = $this->delivery->delivery_info;
		$data['delivery_tax_group_id'] = $this->delivery->delivery_tax_group_id;
		$data['delivery_person_info'] = $this->delivery->delivery_person_info;
				
		$data['exchange_rate'] = $this->get_exchange_rate();
		$data['exchange_name'] = $this->get_exchange_name();
		$data['exchange_symbol'] = $this->get_exchange_currency_symbol();
		$data['exchange_symbol_location'] = $this->get_exchange_currency_symbol_location();
		$data['exchange_number_of_decimals'] = $this->get_exchange_currency_number_of_decimals();
		$data['exchange_thousands_separator'] = $this->get_exchange_currency_thousands_separator();
		$data['exchange_decimal_point'] = $this->get_exchange_currency_decimal_point();
		$data['exchange_details'] = $this->get_exchange_details();
		
		$data['sale_id'] = $this->sale_id;
		$data['partial_transactions'] = $this->partial_transactions;
		$data['use_cc_saved_info'] = $this->use_cc_saved_info;
		$data['invoice_no'] = $this->invoice_no;
		$data['prompt_for_card'] = $this->prompt_for_card;
		$data['ebt_voucher'] = $this->ebt_voucher;
		$data['ebt_voucher_no'] = $this->ebt_voucher_no;
		$data['ebt_auth_code'] = $this->ebt_auth_code;
		$data['save_credit_card_info'] = $this->save_credit_card_info;
		$data['use_saved_cc_info'] = $this->use_saved_cc_info;
		$data['redeem_discount'] = $this->redeem_discount;
		$data['coupon_codes'] = $this->get_coupons();
		$data['integrated_gift_card_balances'] = $this->integrated_gift_card_balances;
		return array_merge(parent::to_array(),$data);
	}
	
	function get_disabled_rules_subtotal()
	{
		$exchange_rate = $this->get_exchange_rate() ? $this->get_exchange_rate() : 1;
		
		$subtotal = 0;		
		
		foreach($this->get_items() as $line => $item)
		{		
			//If we are looking up a previous sale but not editing it the price is already exclusive of tax	
			if ($this->get_previous_receipt_id() && !$this->is_editing_previous)
			{
				$price_to_use = $item->unit_price;				
			}
			else
			{
				$price_to_use = $item->get_price_exclusive_of_tax();
			}
			
			$price_to_use+=$item->get_modifier_price_exclusive_of_tax();
			
			if ($item->disable_from_price_rules)
			{
				if ($item->tax_included)
				{
		    		$subtotal+=to_currency_no_money(($price_to_use*$item->quantity-$price_to_use*$item->quantity*$item->discount/100),10);
				}
				else
				{
	    			$subtotal+=to_currency_no_money(($price_to_use*$item->quantity-$price_to_use*$item->quantity*$item->discount/100));
				
				}
			}
		}

		return to_currency_no_money($subtotal*$exchange_rate);
		
	}
	
	public function get_subtotal()
	{
		$exchange_rate = $this->get_exchange_rate() ? $this->get_exchange_rate() : 1;
		
		$subtotal = 0;		
		
		foreach($this->get_items() as $line => $item)
		{		
			//If we are looking up a previous sale but not editing it the price is already exclusive of tax	
			if ($this->get_previous_receipt_id() && !$this->is_editing_previous)
			{
				$price_to_use = $item->unit_price;				
			}
			else
			{
				$price_to_use = $item->get_price_exclusive_of_tax();
			}
			
			$price_to_use+=$item->get_modifier_price_exclusive_of_tax();
			if ($item->tax_included)
			{
		    	$subtotal+=to_currency_no_money(($price_to_use*$item->quantity-$price_to_use*$item->quantity*$item->discount/100),10);
			}
			else
			{
	    	$subtotal+=to_currency_no_money(($price_to_use*$item->quantity-$price_to_use*$item->quantity*$item->discount/100));
				
			}
		}

		return to_currency_no_money($subtotal*$exchange_rate);
	}
		
	function get_total()
	{
		$CI =& get_instance();
		$exchange_rate = $this->get_exchange_rate() ? $this->get_exchange_rate() : 1;
		
		$sale_id = $this->get_previous_receipt_id();
				
		$total = 0;
		foreach($this->get_items() as $item)
		{
			//If we are looking up a previous sale but not editing it the price is already exclusive of tax	
			if ($this->get_previous_receipt_id() && !$this->is_editing_previous)
			{
				$price_to_use = $item->unit_price;	
				$price_to_use+=$item->get_modifier_unit_total();			
			}
			else
			{
				$price_to_use = $item->get_price_exclusive_of_tax();
				$price_to_use+=$item->get_modifier_price_exclusive_of_tax();
			}
			
			
			
			if (isset($item->tax_included) && $item->tax_included)
			{
		    	$total+=to_currency_no_money(($price_to_use*$item->quantity-$price_to_use*$item->quantity*$item->discount/100),10);
				
			}
			else
			{
		    	$total+=to_currency_no_money(($price_to_use*$item->quantity-$price_to_use*$item->quantity*$item->discount/100));
				
			}
		}
		
		foreach($this->get_taxes($sale_id) as $tax)
		{
			$total+=$tax;
		}
		$total = $CI->config->item('round_cash_on_sales') && $this->has_cash_payment() ?  round_to_nearest_05($total) : $total;
		return to_currency_no_money($total*$exchange_rate);
	}	
		
	function get_quantity_already_added_for_variation($item_id,$variation_id, $look_in_kits = true)
	{
		$CI =& get_instance();
		$item_id = str_replace('|FORCE_ITEM_ID|','',$item_id);
		
		$items = $this->get_items('PHPPOSCartItemSale');
		$quanity_already_added = 0;
		foreach ($items as $item)
		{
			if($item->variation_id==$variation_id)
			{
				$quanity_already_added+=$item->quantity;
			}
		}
		
		if($look_in_kits)
		{
			//Check Item Kist for this item
			$all_kits = $CI->Item_kit_items->get_kits_have_item($item_id,$variation_id);
			
			foreach($all_kits as $kits)
			{
			    $kit_quantity = $this->get_kit_quantity_already_added($kits['item_kit_id']);
			    if($kit_quantity > 0)
			    {
						$quanity_already_added += ($kit_quantity * $kits['quantity']);
			    }
			}
		}
		
		return $quanity_already_added;
	}
	
	function get_quantity_already_added($item_id, $look_in_kits = true)
	{
		$item_id = str_replace('|FORCE_ITEM_ID|','',$item_id);
		$CI =& get_instance();
		
		$items = $this->get_items('PHPPOSCartItemSale');
		$quanity_already_added = 0;
		foreach ($items as $item)
		{
			if($item->item_id==$item_id && $item->variation_id === NULL)
			{
				$quanity_already_added+=$item->quantity;
			}
		}
		
		if($look_in_kits)
		{
			//Check Item Kist for this item
			$all_kits = $CI->Item_kit_items->get_kits_have_item($item_id);

			foreach($all_kits as $kits)
			{
			    $kit_quantity = $this->get_kit_quantity_already_added($kits['item_kit_id']);
			    if($kit_quantity > 0)
			    {
						$quanity_already_added += ($kit_quantity * $kits['quantity']);
			    }
			}
		}
		
		return $quanity_already_added;
	}
	
	function get_quantity_already_added_for_variation_sales($item_id,$variation_id, $look_in_kits = true)
	{
		$CI =& get_instance();
		$item_id = str_replace('|FORCE_ITEM_ID|','',$item_id);
		
		$items = $this->get_items('PHPPOSCartItemSale');
		$quanity_already_added = 0;
		foreach ($items as $item)
		{
			if($item->variation_id==$variation_id)
			{
				$quanity_already_added+=($item->quantity*($item->quantity_unit_quantity !== NULL ? $item->quantity_unit_quantity : 1));
			}
		}
		
		if($look_in_kits)
		{
			//Check Item Kist for this item
			$all_kits = $CI->Item_kit_items->get_kits_have_item($item_id,$variation_id);
			
			foreach($all_kits as $kits)
			{
			    $kit_quantity = $this->get_kit_quantity_already_added($kits['item_kit_id']);
			    if($kit_quantity > 0)
			    {
						$quanity_already_added += ($kit_quantity * $kits['quantity']);
			    }
			}
		}
		
		return $quanity_already_added;
	}
	
	function get_quantity_already_added_sales($item_id, $look_in_kits = true)
	{
		$item_id = str_replace('|FORCE_ITEM_ID|','',$item_id);
		$CI =& get_instance();
		
		$items = $this->get_items('PHPPOSCartItemSale');
		$quanity_already_added = 0;
		foreach ($items as $item)
		{
			if($item->item_id==$item_id && $item->variation_id === NULL)
			{
				$quanity_already_added+=$item->quantity*($item->quantity_unit_quantity !== NULL ? $item->quantity_unit_quantity : 1);
			}
		}
		
		if($look_in_kits)
		{
			//Check Item Kist for this item
			$all_kits = $CI->Item_kit_items->get_kits_have_item($item_id);

			foreach($all_kits as $kits)
			{
			    $kit_quantity = $this->get_kit_quantity_already_added($kits['item_kit_id']);
			    if($kit_quantity > 0)
			    {
						$quanity_already_added += ($kit_quantity * $kits['quantity']);
			    }
			}
		}
		
		return $quanity_already_added;
	}
	
	
	function get_kit_quantity_already_added($kit_id)
	{
	    $items = $this->get_items('PHPPOSCartItemKitSale');
	    $quanity_already_added = 0;
	    foreach ($items as $item)
	    {
		    if($item->item_kit_id==$kit_id)
		    {
					$quanity_already_added+=$item->quantity;
		    }
	    }
	    return $quanity_already_added;
	}
	
	public function destroy()
	{
		parent::destroy();
		$this->clear_cc_info();
		self::setup_defaults();
	}
	
	/*
	* This function is called when a customer added/removed (effects tiers) or tier changed
	* It scans item and item kits to see if there price is at a default value
	* If a price is at a default value, it is changed to match the tier. Should only be called from a controller
	*/
	function determine_new_prices_for_tier_change()
	{
		$CI =& get_instance();
		$items = $this->get_items();
		foreach ($items as $line=>$item )
		{
			if (get_class($item) == 'PHPPOSCartItemSale')
			{
				$price=$item->unit_price;
				$item_id=$item->item_id;
				$item_info = $CI->Item->get_info($item_id);
				$item_location_info = $CI->Item_location->get_info($item_id);
				
				if ($item->variation_id)
				{
					$CI->load->model('Item_variations');
					$CI->load->model('Item_variation_location');
					$item_variation_info = $CI->Item_variations->get_info($item->variation_id);
					$item_variation_location_info = $CI->Item_variation_location->get_info($item->variation_id);
				}
				$previous_price = FALSE;
			
				if ($this->previous_tier_id || $item->quantity_unit_id)
				{
					$current_tier = $this->selected_tier_id;
					//Set selected_tier to previous tier then calculate price
					$this->selected_tier_id = $this->previous_tier_id;
					$previous_price = $item->get_price_for_item();
					//Set back to current_tier it was before
					$this->selected_tier_id = $current_tier;					
				}
				$previous_price = to_currency_no_money($previous_price, 10);
				$price = to_currency_no_money($price, 10);
				
				if((isset($item_variation_info) && $price == $item_variation_info->unit_price  || (isset($item_variation_location_info) && $price == $item_variation_location_info->unit_price)) || $price==$item_info->unit_price || $price == $item_location_info->unit_price || (($price == $previous_price) && (($price !=0 && $previous_price!=0) || $this->previous_tier_id)))
				{	
					$item->unit_price = $item->get_price_for_item();		
				}
			}
			elseif (get_class($item) == 'PHPPOSCartItemKitSale')
			{
				$price=$item->unit_price;
				$item_kit_id=$item->item_kit_id;
				$item_kit_info = $CI->Item_kit->get_info($item_kit_id);
				$item_kit_location_info = $CI->Item_kit_location->get_info($item_kit_id);
				$previous_price = FALSE;
			
				if ($this->previous_tier_id)
				{
					$current_tier = $this->selected_tier_id;
					//Set selected_tier to previous tier then calculate price
					$this->selected_tier_id = $this->previous_tier_id;
					$previous_price = $item->get_price_for_item_kit();
					//Set back to current_tier it was before
					$this->selected_tier_id = $current_tier;					
				}
				
				$previous_price = to_currency_no_money($previous_price, 10);
				$price = to_currency_no_money($price, 10);
						
				if($price==$item_kit_info->unit_price || $price == $item_kit_location_info->unit_price || (($price == $previous_price) && (($price !=0 && $previous_price!=0) || $this->previous_tier_id)))
				{
					$item->unit_price= $item->get_price_for_item_kit();		
				}
			}
		}
	}
	
	function get_index_for_flat_discount_item()
	{
		$CI =& get_instance();
		
		$item_id_for_flat_discount_item = $CI->Item->get_item_id_for_flat_discount_item();
		
		$items = $this->get_items('PHPPOSCartItemSale');
		foreach ($items as $index=>$item )
		{
			if ($item->item_id == $item_id_for_flat_discount_item)
			{
				return $index;
			}
		}
		
		return FALSE;
		
	}
	
	function get_flat_discount_amount()
	{
		$discount_flat_index = $this->get_index_for_flat_discount_item();
		
		if ($discount_flat_index  !== FALSE)
		{
			return $this->get_item($discount_flat_index)->get_total();
		}
		
		return 0;
	}
		
	function get_discount_all_fixed()
	{
		$index_for_fixed_discount = $this->get_index_for_flat_discount_item();
		
		if ($index_for_fixed_discount!== FALSE)
		{
			$cart_items = $this->get_items();
			$item = $cart_items[$index_for_fixed_discount];
			
			return to_currency_no_money($item->unit_price * -$item->quantity);
		}
		
		return NULL;
	}
	
	function get_discount_all_percent()
	{
		$percent_discount = NULL;
		$first_item = NULL;
		
		$index_for_fixed_discount = $this->get_index_for_flat_discount_item();
		$items = $this->get_items();
		
		if (count($items) > 0)
		{
			foreach ($items as $index=>$item )
			{
				if ($index !== $index_for_fixed_discount)
				{
					$first_item = $items[$index];
					break;
				}
			}
			if (isset($first_item))
			{
				$percent_discount = $first_item->discount;
			
				foreach ($items as $index=>$item )
				{
					if ($index !== $index_for_fixed_discount)
					{
						if ($item->discount == $percent_discount)
						{
							$percent_discount = $item->discount;
						}
						else
						{
							$percent_discount = NULL;
							break;
						}
					}
				}
			}
		}
		return $percent_discount;
	}
	
	function discount_all($percent_discount)
	{
		$CI =& get_instance();
		
		$max_discount_global = $CI->Employee->get_logged_in_employee_info()->max_discount_percent;
	
		//Try globally
		if ($max_discount_global === NULL)
		{
			$max_discount_global = $CI->config->item('max_discount_percent') !== '' ? $CI->config->item('max_discount_percent') : NULL;
		}
	
		$items = $this->get_items();
		
		foreach($items as $index=>$item)
		{
			if ($index !== $this->get_index_for_flat_discount_item())
			{
				if(($max_discount_global !== NULL && floatval($percent_discount) > floatval($max_discount_global)) || ($item->max_discount_percent !== NULL && floatval($percent_discount) > floatval($item->max_discount_percent)))
				{
					return false;
				}
			}
		}
		
		
		foreach($items as $index=>$item)
		{
			if ($index !== $this->get_index_for_flat_discount_item())
			{
				$item->discount = $percent_discount;
			}
		}
		return true;
	}
	
	function clear_delivery()
	{
		$this->delivery = new PHPPOSCartDelivery();
		$this->has_delivery = FALSE;
	}
	
	
	function get_has_delivery()
	{
		return $this->has_delivery;
	}

	function set_has_delivery($has_delivery)
	{
		$this->has_delivery = $has_delivery;
	}

	function get_delivery_person_info()
	{
			return $this->delivery->delivery_person_info;
	}

	function set_delivery_person_info($delivery_person_info)
	{
			$this->delivery->delivery_person_info = $delivery_person_info;
	}

	function get_delivery_info()
	{
			return $this->delivery->delivery_info;
	}

	function set_delivery_info($delivery_info)
	{
		$this->delivery->delivery_info = $delivery_info;
	}

	function get_delivery_tax_group_id()
	{
			return $this->delivery->delivery_tax_group_id;
	}

	function set_delivery_tax_group_id($delivery_tax_group_id)
	{
		$this->delivery->delivery_tax_group_id = $delivery_tax_group_id;
	}
	
	function get_index_for_delivery_item()
	{
		$CI =& get_instance();
		
		$item_id_for_delivery_item = $CI->Item->get_item_id_for_delivery_item();
		
		$items = $this->get_items('PHPPOSCartItemSale');
		foreach ($items as $index=>$item )
		{
			if ($item->item_id == $item_id_for_delivery_item)
			{
				return $index;
			}
		}
		
		return FALSE;
		
	}
	
	function get_delivery_item_price_in_cart()
	{
		$line = $this->get_index_for_delivery_item();
		
		if ($line !== FALSE)
		{
			$item = $this->get_item($line);
			if($item)
			{
				return $item->unit_price;
			}
		}
		return 0;
	}
	
	function get_delivery_item_price_in_cart_with_quantity()
	{
		$line = $this->get_index_for_delivery_item();
		
		if ($line !== FALSE)
		{
			$item = $this->get_item($line);
			if($item)
			{
				return $item->unit_price*$item->quantity;
			}
		}
		return 0;
	}
	
	function get_exchange_rate()
	{
		$details = $this->sale_exchange_details;
  	@list($rate, $name,$currency_symbol,$currency_symbol_location,$number_of_decimals,$thousands_separator,$decimal_point) = explode("|",$details);
		
		return $rate ? $rate : 1;
	}
	
	function get_exchange_name()
	{
		$details = $this->sale_exchange_details;
  	@list($rate, $name,$currency_symbol,$currency_symbol_location,$number_of_decimals,$thousands_separator,$decimal_point) = explode("|",$details);
		
		return $name;
	}

	function get_exchange_currency_symbol()
	{
		$CI =& get_instance();
		
		$details = $this->sale_exchange_details;
  	@list($rate, $name,$currency_symbol,$currency_symbol_location,$number_of_decimals,$thousands_separator,$decimal_point) = explode("|",$details);
		return $currency_symbol ? $currency_symbol : ($CI->config->item('currency_symbol') ? $CI->config->item('currency_symbol') : '$');
	}
	
	function get_exchange_currency_symbol_location()
	{
		$details = $this->sale_exchange_details;
  	@list($rate, $name,$currency_symbol,$currency_symbol_location,$number_of_decimals,$thousands_separator,$decimal_point) = explode("|",$details);
		
		return $currency_symbol_location ? $currency_symbol_location : 'before';
		
	}
		
	function get_exchange_currency_number_of_decimals()
	{
		$details = $this->sale_exchange_details;
  	@list($rate, $name,$currency_symbol,$currency_symbol_location,$number_of_decimals,$thousands_separator,$decimal_point) = explode("|",$details);
		
		return $number_of_decimals !=='' ? $number_of_decimals : '';
		
	}
		
	function get_exchange_currency_thousands_separator()
	{
		$details = $this->sale_exchange_details;
  	@list($rate, $name,$currency_symbol,$currency_symbol_location,$number_of_decimals,$thousands_separator,$decimal_point) = explode("|",$details);
		
		return $thousands_separator ? $thousands_separator : ',';
		
	}
		
	function get_exchange_currency_decimal_point()
	{
		$details = $this->sale_exchange_details;
  	@list($rate, $name,$currency_symbol,$currency_symbol_location,$number_of_decimals,$thousands_separator,$decimal_point) = explode("|",$details);
		
		return $decimal_point ? $decimal_point : '.';
	}
		
	function get_exchange_details()
	{
		return $this->sale_exchange_details;
	}
	
	function set_exchange_details($rate_det)
	{
		$this->sale_exchange_details = $rate_det;
	}
	
	function clear_exchange_details() 	
	{
		$this->sale_exchange_details = NULL;
	}	
	
	function has_discount()
	{
		$items = $this->get_items();
		$line_for_fixed_discount = $this->get_index_for_flat_discount_item();
		
		if ($line_for_fixed_discount && $items[$line_for_fixed_discount]->unit_price!=0)
		{
			return true;
		}
		
		if (count($items) > 0)
		{
			foreach ($items as $line=>$item )
			{
				if ($item->discount != 0)
				{
					return true;
				}
			}
		}
	
		return false;
	}
	
	function get_partial_transactions()
	{
		return $this->partial_transactions;
	}
	
	function set_partial_transactions($partial_transactions)
	{
		$this->partial_transactions = $partial_transactions;
	}
	
	function add_partial_transaction($partial_transaction)
	{
		$this->partial_transactions[] = $partial_transaction;
	}
	
	function delete_partial_transactions()
	{
		$this->partial_transactions = array();
	}
	
	function clear_cc_info()
	{
		$CI =& get_instance();
		
		$CI->session->unset_userdata('ref_no');
		$CI->session->unset_userdata('auth_code');
		$CI->session->unset_userdata('masked_account');
		$CI->session->unset_userdata('cc_token');
		$CI->session->unset_userdata('acq_ref_data');
		$CI->session->unset_userdata('process_data');
		$CI->session->unset_userdata('card_issuer');
		$CI->session->unset_userdata('entry_method');
		$CI->session->unset_userdata('tip_amount');
		$CI->session->unset_userdata('aid');
		$CI->session->unset_userdata('tvr');
		$CI->session->unset_userdata('iad');
		$CI->session->unset_userdata('tsi');
		$CI->session->unset_userdata('arc');
		$CI->session->unset_userdata('cvm');
		$CI->session->unset_userdata('tran_type');
		$CI->session->unset_userdata('application_label');
		$CI->session->unset_userdata('ebt_balance');
		$CI->session->unset_userdata('text_response');
		$CI->session->unset_userdata('cc_signature');
		$CI->session->unset_userdata('CC_SUCCESS');		
	}
	
	function get_ebt_total_amount_to_charge()
	{
		$total = 0;
		
		foreach($this->get_items() as $line=>$item)
		{
			if ($item->is_ebt_item)
			{
	    	$total+=$item->get_subtotal();
			}
		}
		
		if ($total >= 0)
		{
			return min($total,$this->get_amount_due());
		}
		else
		{
			return max($total,$this->get_amount_due());			
		}
	}	
	
	function change_credit_card_payments_to_partial()
	{
		$payments=$this->get_payments();
		
		foreach($payments as $payment_id=>$payment)
		{
			//If we have a credit payment, change it to partial credit card so we can process again
			if ($payment->payment_type == lang('common_credit'))
			{
				$payments[$payment_id]->payment_type =  lang('sales_partial_credit');
			}
		}		
	}	
	
	function get_amount_due_round()
	{
		$CI =& get_instance();
		
		$amount_due=0;
		$payment_total = $this->get_payments_total();
		$sales_total= $CI->config->item('round_cash_on_sales') ?  round_to_nearest_05($this->get_total()) : $this->get_total();
		$amount_due=to_currency_no_money($sales_total - $payment_total);
		return $amount_due;
	}
	
	function update_register_cart_data()
	{
		$CI =& get_instance();
		$data = array();
		$data['cart'] = $this->get_items();
		$data['subtotal'] = $this->get_subtotal();
		$data['tax'] = $this->get_tax_total_amount();
		$data['amount_due'] = $this->get_amount_due();
		$data['exchange_rate'] = $this->get_exchange_rate();
		$data['exchange_name'] = $this->get_exchange_name();
		$data['exchange_symbol'] = $this->get_exchange_currency_symbol();
		$data['exchange_symbol_location'] = $this->get_exchange_currency_symbol_location();
		$data['exchange_number_of_decimals'] = $this->get_exchange_currency_number_of_decimals();
		$data['exchange_thousands_separator'] = $this->get_exchange_currency_thousands_separator();
		$data['exchange_decimal_point'] = $this->get_exchange_currency_decimal_point();
		
		$customer_id = $this->customer_id;
		if($customer_id)
		{
			$info=$CI->Customer->get_info($customer_id);
			$data['customer']=$info->first_name.' '.$info->last_name.($info->company_name==''  ? '':' ('.$info->company_name.')');
			$data['customer_email']=$info->email;
			$data['customer_balance'] = $info->balance;
			$data['avatar']=$info->image_id ?  app_file_url($info->image_id) : base_url()."assets/img/user.png"; //can be changed to  base_url()."img/avatar.png" if it is required
		}
		else
		{
			$data['customer']= NULL;
		}

		$data['payments'] = $this->get_payments();
		$data['total'] = $this->get_total();
		$CI->load->model('Register_cart');
		$CI->Register_cart->set_data($data,$CI->Employee->get_logged_in_employee_current_register_id());		
		
	}
	
	
	function save()
	{
		parent::save();
		$this->update_register_cart_data();
	}
	
	function do_price_rules($params=array())
	{
		if($this->get_mode() == 'store_account_payment')
		{
			return FALSE;
		}
		
		if (isset($params['line']))
		{
			$cart_row = $this->get_item($params['line']);
						
			if (property_exists($cart_row,'item_id'))
			{
				$params['item'] = $cart_row;
			}
			else
			{
				$params['item_kit'] = $cart_row;
			}
		}
		
		
		if(isset($params['item']))
		{
			if ($params['item']->disable_from_price_rules)
			{
				return FALSE;
			}
			
			if ($params['item']->variation_id)
			{
				$params['quantity'] = $this->get_quantity_already_added_for_variation($params['item']->item_id, $params['item']->variation_id, false);
			}
			else
			{
				$params['quantity'] = $this->get_quantity_already_added($params['item']->item_id, false);
			}
		}
		
		if(isset($params['item_kit']))
		{
			if ($params['item_kit']->disable_from_price_rules)
			{
				return FALSE;
			}
			
			$params['quantity'] = $this->get_kit_quantity_already_added($params['item_kit']->item_kit_id);
		}
		
		$params['coupons'] =  $this->get_coupons();
		
		$item_or_kit = isset($params['item']) ? $params['item'] : $params['item_kit'];
		
		$rules = $this->do_price_rule_for_items_and_item_kits($params);
	
		//This means we aren't getting back an index array and we need to conver the rule to an indexed array
		//$rules can be an array of associtive arrays or just one associative array which we want to convert to index array
		if (!isset($rules[0]))
		{
			$rules = array($rules);
		}
		
		foreach($rules as $rule)
		{
			$item_or_kit_rule_applied = FALSE;
			$spend_item_applied = FALSE;
			
			if ($rule)
			{
				switch($rule['type'])
				{
					case 'simple_discount':
					 $item_or_kit_rule_applied = $this->apply_buy_x_get_y($rule, $params);
					break;
			
					case 'buy_x_get_y_free':
						$item_or_kit_rule_applied = $this->apply_buy_x_get_y($rule,$params);
					break;

					case 'buy_x_get_discount': 
						$item_or_kit_rule_applied = $this->apply_buy_x_get_y($rule,$params);
					break;

					case 'advanced_discount': 
						$item_or_kit_rule_applied = $this->apply_advanced_discount($rule,$params);
					break;
			
					default:
						$this->cleanup_price_rule_items($params);
					break;
				}
			}
			else
			{
				$this->cleanup_price_rule_items($params);				
			}
				
			$spending_rule = $this->do_spending_price_rule($params);
			
			if ($spending_rule)
			{
				switch($spending_rule['type'])
				{
					case 'spend_x_get_discount':
						$spend_item_applied = $this->apply_spend_x_get_discount($spending_rule);
					break;
			
					default:
						$this->cleanup_price_rule_discounts();
					break;
				}			
			}
			else
			{
				$this->cleanup_price_rule_discounts();				
			}
		
		
			if (is_array($item_or_kit_rule_applied))
			{
				foreach($item_or_kit_rule_applied as $item_or_kit)
				{
					$item_or_kit->rule['rule_discount'] = $this->get_rule_discount($rule,$item_or_kit);
				}
			}
			else
			{
				if ($item_or_kit_rule_applied !== FALSE)
				{
					$item_or_kit_rule_applied->rule['rule_discount'] = $this->get_rule_discount($rule,$item_or_kit_rule_applied);
				}
			}
		
			if($spend_item_applied !== FALSE)
			{
					$spend_item_applied->rule['rule_discount'] = $this->get_rule_discount($spending_rule,$spend_item_applied);
			}
		}
		
		return TRUE;
	}
	
	function get_rule_discount($rule,$item)
	{
		$CI =& get_instance();
		
		$quantity_total = $item->quantity;
		$price = $item->unit_price;
		
		if (isset($rule['type']) && $rule['type'] == 'buy_x_get_y_free')
		{		
			$CI->load->model('Item');
			$CI->load->model('Item_kit');
			$regular_price = property_exists($item,'item_kit_id') ? $CI->Item_kit->get_sale_price(array('tier_id' => $this->selected_tier_id,'item_kit_id' => $item->item_kit_id)) : $CI->Item->get_sale_price(array('tier_id' => $this->selected_tier_id,'item_id' => $item->item_id)); 
			return $quantity_total * $regular_price;
		}
		elseif (isset($rule['type']) && ($rule['type'] == 'buy_x_get_discount' || $rule['type'] == 'simple_discount'))
		{		
			if(isset($item->rule['percent_off']))
			{
				return $quantity_total * $price * ($item->rule['percent_off']/100);
			} 
			elseif(isset($item->rule['fixed_off']))
			{
				return $quantity_total * $item->rule['fixed_off'];
			}
		}
		elseif(isset($rule['type']) && ($rule['type'] == 'advanced_discount'))
		{			
			if(isset($item->rule['discount_per_unit_percent']))
			{
				return $quantity_total * $price * ($item->rule['discount_per_unit_percent']/100);
			}
			elseif(isset($item->rule['discount_per_unit_fixed']))
			{
				return $quantity_total * $item->rule['discount_per_unit_fixed'];
			}
		}
		elseif(isset($rule['type']) && $rule['type'] == 'spend_x_get_discount')
		{
			return abs($quantity_total) * $price;
		}
		
		return 0;
	}
	
	
	//TODO need to fix for mix and match
	function cleanup_price_rule_items($params)
	{
		$items = $this->get_items();
		
		foreach($items as $line => $item)
		{
			if($this->is_price_rule_discount_item_line($line))
			{
				//if buyxgety but no rule returned remove it from cart	
				if((property_exists($item,'item_kit_id') && isset($params['item_kit'])) && $item->item_kit_id == $params['item_kit']->item_kit_id)
				{
					if(isset($params['apply_coupons_only']) && $params['apply_coupons_only'])
					{
						$id = $item->item_kit_id;
						$kit = true;
						$reg_line = $this->get_price_rule_non_discount_item_line($id, $kit);
							
						$reg_item = $this->get_item($reg_line);
						$reg_item->quantity = $this->get_kit_quantity_already_added($id);
					}
					
					$this->delete_item($line);
				}
				if((property_exists($item,'item_id') && isset($params['item'])) && $item->item_id == $params['item']->item_id && $item->variation_id == $params['item']->variation_id)
				{
					if(isset($params['apply_coupons_only']) && $params['apply_coupons_only'])
					{
						$id = $item->item_id;
						$kit = false;
						$reg_line = $this->get_price_rule_non_discount_item_line($id.($item->variation_id ? '#'.$params['item']->variation_id : ''), $kit);
						$reg_item = $this->get_item($reg_line);
						$reg_item->quantity = $params['item']->variation_id ? $this->get_quantity_already_added_for_variation($id,$params['item']->variation_id, false) : $this->get_quantity_already_added($id);						
					}
					
					$this->delete_item($line);
				}
				if($item->quantity == 0)
				{
					$this->delete_item($line);
				}
			}
			
			if($this->is_price_rule_advanced_discount_item_line($line))
			{
				//if customrule but no rule returned remove rule 
				if(property_exists($item,'item_kit_id') && $item->item_kit_id == $params['item_kit']->item_kit_id)
				{
					$this->get_item($line)->rule = array();
					$this->get_item($line)->unit_price = $this->get_item($line)->regular_price;
					$this->get_item($line)->discount = 0;
				}
				if(property_exists($item,'item_id') && $item->item_id == $params['item']->item_id && $item->variation_id == $params['item']->variation_id)
				{
					$this->get_item($line)->rule = array();
					$this->get_item($line)->unit_price = $this->get_item($line)->regular_price;
					$this->get_item($line)->discount = 0;
				}
			}
		}		
	}
	
	function cleanup_price_rule_discounts()
	{
		$items = $this->get_items();
		
		foreach($items as $line => $item)
		{
			if($this->is_price_rule_discount_line($line))
			{				
					$this->delete_item($line);
			}
		}
	}
		
	private function get_number_of_free_or_discount_items($rule,$params)
	{
		$is_edit = (isset($params['line']) && !isset($params['apply_coupons_only']));
		$is_delete = (isset($params['delete']) && $params['delete']);
		$CI =& get_instance();
		
		if(isset($params['item_kit']))
		{
			$id = $params['item_kit']->item_kit_id;
			$kit = true;
		} 
		else
		{
			$id = $params['item']->item_id;
			$kit = false;
		}
		
		$is_bogo_rule = $rule['type'] === 'buy_x_get_y_free';
		
		$items_to_buy =  $rule['items_to_buy'];		
		$items_to_get =  $is_bogo_rule || (int)$rule['items_to_get']  ? $rule['items_to_get'] : 1;
				
		if((string)(int)$items_to_buy != $items_to_buy || (string)(int)$items_to_get != $items_to_get)
		{
			return false;
		}
		
		$max = $rule['num_times_to_apply'] * $items_to_get;
		
		if($kit)
		{
			$quantity_of_item_in_cart = $this->get_kit_quantity_already_added($id);
		} 
		else 
		{
			if ($params['item']->variation_id)
			{
				$quantity_of_item_in_cart = $this->get_quantity_already_added_for_variation($id,$params['item']->variation_id, false);
			}
			else
			{
				$quantity_of_item_in_cart = $this->get_quantity_already_added($id, false);
			}
		}
		
		if($is_edit)
		{	
			//discount item line
			if($this->is_price_rule_discount_item_line($params['line']))
			{
				$line_item = $this->get_item($params['line']);
				$discount_item_quantity = (int)$line_item->quantity;
			}
			//regular item line
			else 
			{
				$line_item = $this->get_item($params['line']);
				$reg_item_quantity = (int)$line_item->quantity;
				
				//we want to subtract remander to get number of times to discount applies
				
				if($items_to_buy > 0)
				{
					$r = $reg_item_quantity % $items_to_buy;

					$number_of_times_discount_applies = ($reg_item_quantity - $r) / $items_to_buy;
					
					$discount_item_quantity = $number_of_times_discount_applies*$items_to_get;
					
				}
				else
				{
					$r = ($quantity_of_item_in_cart % ($items_to_buy + $items_to_get));
		
					$number_of_times_discount_applies = ($quantity_of_item_in_cart - $r) / ($items_to_buy + $items_to_get);
			
					$discount_item_quantity = $number_of_times_discount_applies*$items_to_get;
				}
				
			}
		}
		else
		{
			
			//$remainder = $a % $b;
			$r = ($quantity_of_item_in_cart % ($items_to_buy + $items_to_get));
		
			$number_of_times_discount_applies = ($quantity_of_item_in_cart - $r) / ($items_to_buy + $items_to_get);
						
			$discount_item_quantity = $number_of_times_discount_applies*$items_to_get;
			
			
		}
		
		if($max && $discount_item_quantity > $max)
		{
				$discount_item_quantity = $max;
		}
		
		if(!$is_edit && !$is_delete && $r == $items_to_buy && ($max == 0 || ($discount_item_quantity != $max)))
		{
			if(!$CI->config->item('disable_price_rules_dialog'))
			{
				if ($rule['type'] != 'simple_discount')
				{
					if (isset($params['item']))
					{
						$CI->view_data['item_to_add'] = $id.(isset($params['item']) && $params['item']->variation_id ? '#'.$params['item']->variation_id : '').'|FORCE_ITEM_ID|';
					}
					else
					{
						$CI->view_data['item_to_add'] = 'KIT '.$id;
					}
				
					$CI->view_data['number_to_add'] = $is_bogo_rule || (int)$rule['items_to_get']  ? $rule['items_to_get'] : 1;
				}
			}
		}
		return $discount_item_quantity;
	}
	 
	private function get_number_of_discount_items_in_cart($id, $kit = false)
	{
		$variation_id = NULL;
		
		if (($item_identifer_parts = explode('#', str_replace('|FORCE_ITEM_ID|','',$id))) !== false)
		{
			if (isset($item_identifer_parts[1]))
			{
				$id = $item_identifer_parts[0];
				$variation_id = $item_identifer_parts[1];
			}
		}
		
		
		$discounted_item_qty = 0;
		$items = $this->get_items();
		
		foreach ($items as $line=>$item )
		{
			if (($kit && property_exists($item,'item_kit_id') && $item->item_kit_id == $id) || (!$kit && property_exists($item,'item_id') && $item->item_id == $id && $item->item_id == $id && $item->variation_id == $variation_id))
			{
				if(isset($item->rule['type']))
				{
					if($item->rule['type'] == 'buy_x_get_y_free')
					{
						$discounted_item_qty += $item->quantity;
					}
					
					if($item->rule['type'] == 'buy_x_get_discount')
					{
						$discounted_item_qty += $item->quantity;
					}
					
					if($item->rule['type'] == 'simple_discount')
					{
						$discounted_item_qty += $item->quantity;
					}
				}
			}
		}
			
		return $discounted_item_qty;
	}
	
	private function get_number_of_discount_items_in_cart_mix_and_match($rule)
	{
		$CI =& get_instance();
		
		$discounted_item_qty = 0;
		$items= ($CI->Price_rule->get_items_in_cart_that_apply_to_price_rule($rule,$this));
		
		foreach ($items as $line=>$item )
		{
			if(isset($item->rule['type']))
			{
				if($item->rule['type'] == 'buy_x_get_y_free')
				{
					$discounted_item_qty += $item->quantity;
				}
				
				if($item->rule['type'] == 'buy_x_get_discount')
				{
					$discounted_item_qty += $item->quantity;
				}
				
				if($item->rule['type'] == 'simple_discount')
				{
					$discounted_item_qty += $item->quantity;
				}
			}
		}
			
		return $discounted_item_qty;
	}
	
	private function get_number_of_non_discount_items_in_cart_mix_and_match($rule)
	{
		$CI =& get_instance();
		
		$non_discounted_item_qty = 0;
		$items= ($CI->Price_rule->get_items_in_cart_that_apply_to_price_rule($rule,$this));
		
		foreach ($items as $line=>$item )
		{
			if(!isset($item->rule['type']))
			{
					$non_discounted_item_qty += $item->quantity;
			}
		}
			
		return $non_discounted_item_qty;
	}
	
	function is_price_rule_discount_line($line)
	{
		$item = $this->get_item($line);
		
		if(isset($item->rule['type']) && $item->rule['type'] == 'spend_x_get_discount')
		{
			return true;
		}
		
		return false;
	}
	
	function is_price_rule_discount_item_line($line)
	{
		$item = $this->get_item($line);
		
		if(isset($item->rule['type']) && $item->rule['type'] == 'buy_x_get_y_free' && !$item->rule['mix_and_match'])
		{
			return true;
		}
		
		elseif(isset($item->rule['type']) && $item->rule['type'] == 'buy_x_get_discount' && !$item->rule['mix_and_match'])
		{
			return true;
		}
		
		elseif(isset($item->rule['type']) && $item->rule['type'] == 'simple_discount')
		{
			return true;
		}
		
		return false;
		
	}
	
	function is_price_rule_advanced_discount_item_line($line)
	{
		$item = $this->get_item($line);
		
		if(isset($item->rule['type']) && $item->rule['type'] == 'advanced_discount')
		{
			return true;
		}
		
		return false;
		
	}
		
	function get_price_rule_non_discount_item_line($id, $kit = false)
	{
		$variation_id = NULL;
		
		if (($item_identifer_parts = explode('#', str_replace('|FORCE_ITEM_ID|','',$id))) !== false)
		{
			if (isset($item_identifer_parts[1]))
			{
				$id = $item_identifer_parts[0];
				$variation_id = $item_identifer_parts[1];
			}
		}
		
		$items = $this->get_items();

		foreach (array_reverse($items, TRUE) as $line=>$item )
		{
			if (($kit && property_exists($item,'item_kit_id') && $item->item_kit_id == $id) || (!$kit && property_exists($item,'item_id') && $item->item_id == $id && $item->variation_id == $variation_id))
			{
				if(empty($item->rule) || isset($item->rule['type']) && ($item->rule['type'] != 'buy_x_get_y_free' && $item->rule['type'] != 'buy_x_get_discount' && $item->rule['type'] != 'simple_discount'))
				{
					return $line;
				}
			}
		}
		
		return FALSE;
	}
	
	function get_all_lines_for_item($id, $kit = false)
	{
		$variation_id = NULL;
		
		if (($item_identifer_parts = explode('#', str_replace('|FORCE_ITEM_ID|','',$id))) !== false)
		{
			if (isset($item_identifer_parts[1]))
			{
				$id = $item_identifer_parts[0];
				$variation_id = $item_identifer_parts[1];
			}
		}
		
		$items = $this->get_items();
		
		$return = array();
		
		foreach ($items as $line=> $item)
		{
			if (($kit && property_exists($item,'item_kit_id') && $item->item_kit_id == $id) || (!$kit && property_exists($item,'item_id') && $item->item_id == $id && $item->variation_id == $variation_id))
			{
					$return[] = $line;
			}
		}
		
		return $return;
	}
	
	function get_price_rule_discount_item_line($id, $kit = false)
	{
		$variation_id = NULL;
		
		if (($item_identifer_parts = explode('#', str_replace('|FORCE_ITEM_ID|','',$id))) !== false)
		{
			if (isset($item_identifer_parts[1]))
			{
				$id = $item_identifer_parts[0];
				$variation_id = $item_identifer_parts[1];
			}
		}
		$cart = $this->get_items();		

		foreach($cart as $key => $line)
		{
			if(($kit && property_exists($line,'item_kit_id') && $line->item_kit_id == $id) || (!$kit && property_exists($line,'item_id') && $line->item_id == $id && $line->variation_id == $variation_id))
			{
				if(isset($line->rule['type']) && $line->rule['type'] == 'buy_x_get_y_free')
				{
					return $key;
				}
				elseif(isset($line->rule['type']) && $line->rule['type'] == 'buy_x_get_discount')
				{
					return $key;
				}
				elseif(isset($line->rule['type']) && $line->rule['type'] == 'simple_discount')
				{
					return $key;
				}
			}		
		}
		
		return FALSE;
	}
	
	function can_apply_mix_and_match_rule($rule)
	{
		$quantity_of_rule_applied = $this->get_quantity_of_rule_applied($rule);
		$quantity_of_rule_of_non_applied_items_in_rule = $this->get_quantity_of_rule_of_non_applied_items_in_rule($rule);		
		$is_bogo_rule = $rule['type'] === 'buy_x_get_y_free';
		
		$items_to_get =  $is_bogo_rule || (int)$rule['items_to_get']  ? $rule['items_to_get'] : 1;
		$items_to_buy =  (int)$rule['items_to_buy'];
		
		if ($this->was_last_edit_quantity)
		{
			$times_rule_can_be_applied = (int)$quantity_of_rule_of_non_applied_items_in_rule ? (int)($quantity_of_rule_of_non_applied_items_in_rule/$items_to_buy) : 0;
			$times_rule_has_been_applied_already = (int)$quantity_of_rule_applied ? (int)($quantity_of_rule_applied/$items_to_get) : 0;	
		}
		else
		{
			$times_rule_can_be_applied = (int)$quantity_of_rule_of_non_applied_items_in_rule ? (int)(($quantity_of_rule_of_non_applied_items_in_rule + $quantity_of_rule_applied)/($items_to_buy + $items_to_get)) : 0;
			$times_rule_has_been_applied_already = (int)$quantity_of_rule_applied ? (int)($quantity_of_rule_applied/$items_to_get) : 0;
		}
		
		$max = $rule['num_times_to_apply'] ? $rule['num_times_to_apply'] : PHP_INT_MAX;
		
		if ($times_rule_has_been_applied_already < $max)
		{
			return $times_rule_can_be_applied > $times_rule_has_been_applied_already;	
		}
		
		return FALSE;
	}
	
	function get_quantity_of_rule_of_non_applied_items_in_rule($rule)
	{
		$CI =& get_instance();
		
		$items_in_cart_rule_applies= ($CI->Price_rule->get_items_in_cart_that_apply_to_price_rule($rule,$this));
		
		$quantity = 0;
		foreach($items_in_cart_rule_applies as $item)
		{
			if (empty($item->rule['id']) || ($item->rule['id'] != $rule['id']))
			{
				$quantity+=$item->quantity;
			}
		}
		
		return $quantity;
		
	}
	
	function get_quantity_of_rule_applied($rule)
	{
		$quantity = 0;
		foreach($this->get_items() as $item)
		{
			if (isset($item->rule['id']) && $item->rule['id'] == $rule['id'])
			{
				$quantity+=$item->quantity;
			}
		}
		
		return $quantity;
	}
			
	function apply_buy_x_get_y_mix_and_match($rule,$params)
	{
		if (!$this->can_apply_mix_and_match_rule($rule))
		{
			return false;
		}		
		
		$CI =& get_instance();

		$quantity_of_rule_applied = $this->get_quantity_of_rule_applied($rule);
		
		$CI->load->model('Price_rule');
		$items_in_cart_rule_applies= ($CI->Price_rule->get_items_in_cart_that_apply_to_price_rule($rule,$this));
		
		$is_bogo_rule = $rule['type'] === 'buy_x_get_y_free';
		$items_to_buy =  $rule['items_to_buy'];		
		$items_to_get =  $is_bogo_rule || (int)$rule['items_to_get']  ? $rule['items_to_get'] : 1;
		$is_edit = (isset($params['line']) && !isset($params['apply_coupons_only']));
		
		$return = FALSE;
		
		if(isset($params['item_kit']))
		{
			$id = $params['item_kit']->item_kit_id;
			$item_or_kit_to_add = $params['item_kit'];
			$kit = true;
		}
		else
		{
			$id = $params['item']->item_id;
			$item_or_kit_to_add = $params['item'];
			$kit = false;
		}

		if($is_edit)
		{
			$item = $this->get_item($params['line']);

			if($CI->config->item('do_not_group_same_items') || (property_exists($item,'is_serialized') && $item->is_serialized))
			{
				return FALSE;
			}

			if (!$this->was_last_edit_quantity)
			{
				return FALSE;
			}
		}
		
		$discounted_item_line = $this->get_price_rule_discount_item_line($id.(isset($params['item']) && $params['item']->variation_id ? '#'.$params['item']->variation_id : ''), $kit);
		$line_to_edit = $this->get_price_rule_non_discount_item_line($id.(isset($params['item']) && $params['item']->variation_id ? '#'.$params['item']->variation_id : ''), $kit);
		
		
		$number_of_discounted_items_for_rule = $this->get_number_of_discount_items_in_cart_mix_and_match($rule);
		$number_of_non_discounted_items_for_rule = $this->get_number_of_non_discount_items_in_cart_mix_and_match($rule);
		$quantity_of_item_in_cart = $number_of_discounted_items_for_rule + $number_of_non_discounted_items_for_rule;
		
		$quantity_of_rule_applied = $this->get_quantity_of_rule_applied($rule);
		$quantity_of_rule_of_non_applied_items_in_rule = $this->get_quantity_of_rule_of_non_applied_items_in_rule($rule);		


		//Editing quantity has different logic on changing cart; it adds free based on how much you edit
		if ($this->was_last_edit_quantity && !isset($params['apply_coupons_only']))
		{
			$item_quantity_to_be_subtracted = 0;
			
			$times_rule_can_be_applied = (int)$quantity_of_rule_of_non_applied_items_in_rule ? (int)($quantity_of_rule_of_non_applied_items_in_rule/$items_to_buy) : 0;
			$times_rule_can_be_applied = min($times_rule_can_be_applied,$rule['num_times_to_apply'] ? $rule['num_times_to_apply'] : PHP_INT_MAX);
			$times_rule_has_been_applied_already = (int)$quantity_of_rule_applied ? (int)($quantity_of_rule_applied/$items_to_get) : 0;
			$times_rule_can_be_applied_now = $times_rule_can_be_applied - $times_rule_has_been_applied_already;
			
			$discounted_item_quantity_to_be_added = ($times_rule_can_be_applied_now * $items_to_get);
			
		}
		else //Logic here is to preserve how many were added and shuffle free and discounted
		{
			$times_rule_can_be_applied = (int)$quantity_of_rule_of_non_applied_items_in_rule ? (int)(($quantity_of_rule_of_non_applied_items_in_rule + $quantity_of_rule_applied)/($items_to_buy + $items_to_get)) : 0;
			$times_rule_can_be_applied = min($times_rule_can_be_applied,$rule['num_times_to_apply'] ? $rule['num_times_to_apply'] : PHP_INT_MAX);
			
			$times_rule_has_been_applied_already = (int)$quantity_of_rule_applied ? (int)($quantity_of_rule_applied/$items_to_get) : 0;
			$times_rule_can_be_applied_now = $times_rule_can_be_applied - $times_rule_has_been_applied_already;

			$discounted_item_quantity_to_be_added = ($times_rule_can_be_applied_now * $items_to_get);
			$item_quantity_to_be_subtracted = $discounted_item_quantity_to_be_added;
			
		}
				
		//get per item discount price
		if(!$is_bogo_rule)
		{
			$CI->load->model('Item_kit');
			$CI->load->model('Item');
			
			$price_to_use = $kit ? $CI->Item_kit->get_sale_price(array('tier_id' => $this->selected_tier_id,'item_kit_id' => $id)) : $CI->Item->get_sale_price(array('tier_id' => $this->selected_tier_id,'item_id' => $id, 'variation_id' => $params['item']->variation_id));
			$flat_discount = isset($rule['fixed_off']) ? $rule['fixed_off'] : 0;
			
			$discount = isset($rule['percent_off']) ? $rule['percent_off'] : 0;
			$price = $price_to_use - $flat_discount;
			
		} 
		else 
		{
			$discount = 0;
			$price = 0;
			$flat_discount = 0;
		}
		
		//Case where we can update existing items in this transaction
		if ($this->get_item($line_to_edit)->quantity - $item_quantity_to_be_subtracted >=0)
		{
			if($kit)
			{
				$kit_item = new PHPPOSCartItemKitSale(array('unit_price' => $price,'discount' => $discount,'cart' => $this,'scan' => 'KIT '. $id,'quantity' => $discounted_item_quantity_to_be_added,'rule' => $rule));
			
				if ($discounted_item_line!== FALSE)
				{
					//SET rule
					$this->get_item($discounted_item_line)->rule = $rule;
					$this->merge_item($kit_item, $this->get_item($discounted_item_line));
					$return = $this->get_item($discounted_item_line);
				}
				else
				{
					$this->add_item($kit_item);
					$return = $kit_item;
				}
			}
			else 
			{
				$item = new PHPPOSCartItemSale(array('unit_price' => $price,'discount' => $discount,'cart' => $this,'scan' => $id.(isset($params['item']) && $params['item']->variation_id ? '#'.$params['item']->variation_id : '').'|FORCE_ITEM_ID|','quantity' => $discounted_item_quantity_to_be_added,'rule' => $rule));
			
				if ($discounted_item_line!== FALSE)
				{
					$this->get_item($discounted_item_line)->rule = $rule;
					$this->merge_item($item, $this->get_item($discounted_item_line));
					$return = $this->get_item($discounted_item_line);
				
				}
				else
				{
					//ADD item
					$this->add_item($item);
					$return = $item;
				}
			}
			
			$this->get_item($line_to_edit)->quantity-=$item_quantity_to_be_subtracted;
			
		}
		else
		{
			$items_in_cart_rule_applies= ($CI->Price_rule->get_items_in_cart_that_apply_to_price_rule($rule,$this));
			
			foreach($items_in_cart_rule_applies as $cart_item)
			{
				//no need to keep applying
				if ($item_quantity_to_be_subtracted == 0)
				{
					break;
				}
				
				if (!$cart_item->rule)
				{
					//Turn this item into rule
					$qty_rule_now_applies = min($item_quantity_to_be_subtracted,$cart_item->quantity);
					
					$split_items = $cart_item->quantity > $item_quantity_to_be_subtracted;
					
					if ($split_items)
					{
						//We don't have enough quantity in one item that we need to subtract
						
						//Item that has regular price						
						$new_cart_item_regular_price = clone $cart_item;
						$new_cart_item_regular_price->quantity = $cart_item->quantity - $qty_rule_now_applies;
						
						//Discounted item
						$cart_item->rule = $rule;
						$cart_item->quantity = $qty_rule_now_applies;
						
						if ($is_bogo_rule)
						{
							$cart_item->unit_price =0;
						}
						else
						{
							$cart_item->unit_price -= $flat_discount;							
						}
						$cart_item->discount = $discount;
						$cart_item->rule['rule_discount'] = $this->get_rule_discount($rule,$cart_item);
						
						$this->add_item($new_cart_item_regular_price);
						
					}
					else
					{
						$cart_item->rule = $rule;
						if ($is_bogo_rule)
						{
							$cart_item->unit_price =0;
						}
						else
						{
							$cart_item->unit_price -= $flat_discount;
							
						}
						$cart_item->discount = $discount;
						$cart_item->rule['rule_discount'] = $this->get_rule_discount($rule,$cart_item);
					}
					
					$item_quantity_to_be_subtracted-=$qty_rule_now_applies;
				}

			}
		}
		
		if ($this->get_item($line_to_edit)->quantity == 0)
		{
			$this->delete_item($line_to_edit);
		}
		
		return $return;
		
	}	
	
	function apply_buy_x_get_y($rule,$params)
	{			
		
		$serial_number = NULL;
		
		if ($rule['mix_and_match'])
		{
			return $this->apply_buy_x_get_y_mix_and_match($rule,$params);
		}
		
		$return = FALSE;
		
		$CI =& get_instance();
		$is_bogo_rule = $rule['type'] === 'buy_x_get_y_free';
		$is_edit = (isset($params['line']) && !isset($params['apply_coupons_only']));
		
		if(isset($params['item_kit']))
		{
			$id = $params['item_kit']->item_kit_id;
			$item_or_kit_to_add = $params['item_kit'];
			$kit = true;
		}
		else
		{
			$id = $params['item']->item_id;
			$item_or_kit_to_add = $params['item'];
			$serial_number = $params['item']->serialnumber;
			$kit = false;
		}
				
		if($is_edit)
		{
			$item = $this->get_item($params['line']);
			$serial_number = $item->serialnumber;

			if($CI->config->item('do_not_group_same_items') || (property_exists($item,'is_serialized') && $item->is_serialized))
			{
				return FALSE;
			}
			
			if (!$this->was_last_edit_quantity)
			{
				return FALSE;
			}
		}
		
		//check number of free items we qualify for
		$number_of_discounted_items = $this->get_number_of_free_or_discount_items($rule,$params);
		
		if($number_of_discounted_items > 0)
		{
			$discounted_item_line = $this->get_price_rule_discount_item_line($id.(isset($params['item']) && $params['item']->variation_id ? '#'.$params['item']->variation_id : ''), $kit);
			$line_to_edit = $this->get_price_rule_non_discount_item_line($id.(isset($params['item']) && $params['item']->variation_id ? '#'.$params['item']->variation_id : ''), $kit);
			
			if ($line_to_edit !== FALSE)
			{
				$line_to_edit_info = $this->get_item($line_to_edit);
			}
									
			if($is_edit)
			{
				$items_to_buy =  $rule['items_to_buy'];		
				$items_to_get =  $is_bogo_rule || (int)$rule['items_to_get']  ? $rule['items_to_get'] : 1;
				
				if($this->is_price_rule_discount_item_line($params['line']))
				{
					$reg_quantity = $number_of_discounted_items*($items_to_buy/$items_to_get);
					
				} 
				else 
				{
					$line_item = $this->get_item($params['line']);
					$reg_quantity = $line_item->quantity;
				}
				
			} 
			else 
			{
				//subtract items to get free from total qty in cart
				$reg_quantity = $params['quantity']-$number_of_discounted_items;
			}	
			
			
			$current_quantity_of_discounted_item = $this->get_number_of_discount_items_in_cart($id.(isset($params['item']) && $params['item']->variation_id ? '#'.$params['item']->variation_id : ''), $kit);
			
			if (isset($line_to_edit_info) && $line_to_edit_info && $line_to_edit_info->quantity > 0)
			{
				if($CI->config->item('do_not_group_same_items') || (isset($line_to_edit_info->is_serialized) && $line_to_edit_info->is_serialized))
				{
					if($current_quantity_of_discounted_item < $number_of_discounted_items)
					{
						$this->delete_item($line_to_edit);
						$discounted_item_line = FALSE;
					}	
				}
				else 
				{
					if($reg_quantity > 0)
					{
						$this->get_item($line_to_edit)->quantity = $reg_quantity;
					}
					else
					{
						$this->delete_item($line_to_edit);
						$discounted_item_line = $this->get_price_rule_discount_item_line($id.(property_exists($item_or_kit_to_add,'variation_id') && $item_or_kit_to_add->variation_id ? '#'.$item_or_kit_to_add->variation_id : ''), $kit);						
					}
				}
			}
			elseif($line_to_edit !== FALSE)
			{				
					$this->delete_item($line_to_edit);
					$discounted_item_line = FALSE;
			}
			
			//do some math here		
			$discounted_item_quantity_to_be_added =  $number_of_discounted_items - $current_quantity_of_discounted_item;
			if($discounted_item_quantity_to_be_added !== 0)
			{
				//get per item discount price
				if(!$is_bogo_rule)
				{
					$CI->load->model('Item_kit');
					$CI->load->model('Item');
					
					$price_to_use = $kit ? $CI->Item_kit->get_sale_price(array('tier_id' => $this->selected_tier_id,'item_kit_id' => $id)) : $CI->Item->get_sale_price(array('tier_id' => $this->selected_tier_id,'item_id' => $id, 'variation_id' => $params['item']->variation_id));
					$flat_discount = isset($rule['fixed_off']) ? $rule['fixed_off'] : 0;
					
					$discount = isset($rule['percent_off']) ? $rule['percent_off'] : 0;
					$price = $price_to_use - $flat_discount;
					
				} else {
					$discount = 0;
					$price = 0;
				}
				
				//add/subtract from discounted items
				if($kit)
				{
					$kit_item = new PHPPOSCartItemKitSale(array('unit_price' => $price,'discount' => $discount,'cart' => $this,'scan' => 'KIT '. $id,'quantity' => $discounted_item_quantity_to_be_added,'rule' => $rule));
					
					if ($discounted_item_line!== FALSE)
					{
						//SET rule
						$this->get_item($discounted_item_line)->rule = $rule;
						$this->merge_item($kit_item, $this->get_item($discounted_item_line));
						$return = $this->get_item($discounted_item_line);
					}
					else
					{
						$this->add_item($kit_item);
						$return = $kit_item;
					}
				}
				else 
				{
					$item = new PHPPOSCartItemSale(array('serialnumber' => $serial_number,'unit_price' => $price,'discount' => $discount,'cart' => $this,'scan' => $id.(isset($params['item']) && $params['item']->variation_id ? '#'.$params['item']->variation_id : '').'|FORCE_ITEM_ID|','quantity' => $discounted_item_quantity_to_be_added,'rule' => $rule));
					
					if ($discounted_item_line!== FALSE)
					{
						$this->get_item($discounted_item_line)->rule = $rule;
						$this->merge_item($item, $this->get_item($discounted_item_line));
						$return = $this->get_item($discounted_item_line);
						
					}
					else
					{
						//ADD item
						$this->add_item($item);
						$return = $item;
					}
				}
			} 
			elseif ($number_of_discounted_items <= 0)
			{
				$this->delete_item($discounted_item_line);
			}
		}	
		else 
		{
			$this->cleanup_price_rule_items($params);
		}
		
		return $return;
	}
		
	function apply_spend_x_get_discount($rule)
	{
		$CI =& get_instance();
		
		$CI->load->model('Item');
		
		if ($rule['percent_off'])
		{
			$discount_amount = to_currency_no_money($rule['spend_amount']*($rule['percent_off']/100));
		}
		else
		{
			$discount_amount = to_currency_no_money($rule['fixed_off']);
		}
		
		$rule_spend_amount = $rule['spend_amount'];
		$sub_total = $this->get_subtotal() - $this->get_flat_discount_amount() - $this->get_disabled_rules_subtotal();
		
		$max = $rule['num_times_to_apply'];
		
		$r = fmod($sub_total, $rule_spend_amount);
		$number_of_times_discount_applies = ($sub_total - $r) / $rule_spend_amount;
		
		if($max > 0 && $number_of_times_discount_applies > $max)
		{
			$number_of_times_discount_applies = $max;
		}
		
		$discount_index = $this->get_index_for_flat_discount_item();
		if ($discount_index !== FALSE)
		{
			$this->delete_item($discount_index);
		}
		
		$item_id = $CI->Item->create_or_update_flat_discount_item();
		
		$neg_apply = $number_of_times_discount_applies*-1;
		
		$item = new PHPPOSCartItemSale(array('unit_price' => to_currency_no_money($discount_amount),'discount' => 0,'cart' => $this,'scan' => $item_id.'|FORCE_ITEM_ID|','quantity' => $neg_apply,'rule' => $rule));
		$this->add_item($item);
		return $item;
	}
	
	function get_total_quantity_that_rule_could_apply_to($rule)
	{
		
		$CI =& get_instance();		
		$items= ($CI->Price_rule->get_items_in_cart_that_apply_to_price_rule($rule,$this));
		
		$total_quantity_in_rule = 0;
		foreach ($items as $line=>$item )
		{
			$total_quantity_in_rule+=$item->quantity;
		}
		return $total_quantity_in_rule;
	}
	
	function can_apply_advanced_mix_and_match_rule($rule)
	{
		$CI =& get_instance();		
		
		$total_quantity_in_rule = $this->get_total_quantity_that_rule_could_apply_to($rule);
		
		$price_breaks = $CI->Price_rule->get_price_breaks_in_quantity_order($rule['id']);
		$min_quantity = PHP_INT_MAX;
		
		//Use first price break to determine the minimu quantity for price break to apply
		if(isset($price_breaks[0]))
		{
			$min_quantity = $price_breaks[0]['item_qty_to_buy'];
		}
		
		return $total_quantity_in_rule>=$min_quantity;
	}
	
	function cleanup_advanced_mix_and_match_rule($rule)
	{
		$items = $this->get_items();
		
		foreach($items as $line => $item)
		{
			if($this->is_price_rule_advanced_discount_item_line($line) && $rule['id'] == $this->get_item($line)->rule['id'])
			{
				$this->get_item($line)->rule = array();
				$this->get_item($line)->unit_price = $this->get_item($line)->regular_price;
				$this->get_item($line)->discount = 0;
			}
		}
	}
	
	function apply_advanced_discount_mix_and_match($rule,$params)
	{
		$CI =& get_instance();		
		
		
		if (!$this->can_apply_advanced_mix_and_match_rule($rule))
		{
			$this->cleanup_advanced_mix_and_match_rule($rule);
			return false;
		}		
		
		$total_quantity_in_rule = $this->get_total_quantity_that_rule_could_apply_to($rule);
		$price_breaks = $CI->Price_rule->get_price_breaks_in_quantity_order($rule['id']);
		
		$price_break = $price_breaks[0];
					
		foreach($price_breaks as $price_break_search)
		{
			if ($total_quantity_in_rule >= (double)$price_break_search['item_qty_to_buy'])
			{
				$price_break = $price_break_search;
			}
			else
			{
				break;
			}
		}
				
		$CI =& get_instance();		
		
		$discount_percent = NULL;
		$discount_flat = NULL;
		
		if(isset($price_break['discount_per_unit_fixed']))
		{
			$discount_flat = $price_break['discount_per_unit_fixed'];
			$rule['discount_per_unit_fixed'] = $discount_flat;
		}
		
		elseif(isset($price_break['discount_per_unit_percent']))
		{
			$discount_percent = $price_break['discount_per_unit_percent'];
			$rule['discount_per_unit_percent'] = $discount_percent;
		}
		
		
		$items= ($CI->Price_rule->get_items_in_cart_that_apply_to_price_rule($rule,$this));
		
		
		$return = array();
		
		foreach ($items as $line=>$item )
		{
			if ($discount_flat !== NULL)
			{
				
				if(property_exists($item,'item_kit_id'))
				{
					$CI->load->model('Item_kit');
					$price_to_use = $CI->Item_kit->get_sale_price(array('tier_id' => $this->selected_tier_id,'item_kit_id' => $item->item_kit_id));	
				}
				else
				{
					$quantity_unit_id = $item->quantity_unit_id;
					
					$CI->load->model('Item');
					$price_to_use = $CI->Item->get_sale_price(array('quantity_unit_id' => $quantity_unit_id,'tier_id' => $this->selected_tier_id,'item_id' => $item->item_id, 'variation_id' => $item->variation_id));	
				}
				
				$item->unit_price = $price_to_use - $discount_flat;
			}
			
			if ($discount_percent !== NULL)
			{
				$item->discount = $discount_percent;
			}
			
			$item->rule = $rule;
			
			$return[] = $item;
		}
		
		return $return;
	}
	
	function apply_advanced_discount($rule, $params)
	{
		
		if ($rule['mix_and_match'])
		{
			return $this->apply_advanced_discount_mix_and_match($rule,$params);
		}
		
		if(isset($params['item_kit']))
		{
			$id = $params['item_kit']->item_kit_id;
			$kit = true;
		}
		else
		{
			$id = $params['item']->item_id;
			$variation_id = $params['item']->variation_id;
			$kit = false;
		}
		
		
		$discount_percent = NULL;
		$discount_flat = NULL;
		
		if(isset($rule['discount_per_unit_fixed']))
		{
			$discount_flat = $rule['discount_per_unit_fixed'];
		}
		
		elseif(isset($rule['discount_per_unit_percent']))
		{
			$discount_percent = $rule['discount_per_unit_percent'];
		}
		
		$CI =& get_instance();
		
		if($kit)
		{
			$CI->load->model('Item_kit');
			$price_to_use = $CI->Item_kit->get_sale_price(array('tier_id' => $this->selected_tier_id,'item_kit_id' => $id));	
		}
		else
		{
			$CI->load->model('Item');
			$quantity_unit_id = $params['item']->quantity_unit_id;
			
			$price_to_use = $CI->Item->get_sale_price(array('quantity_unit_id' => $quantity_unit_id,'tier_id' => $this->selected_tier_id,'item_id' => $id, 'variation_id' => $variation_id));	
		}
		
		if($discount_flat)
		{
			$price_to_use = $price_to_use -$discount_flat;
		}
		
		$item_lines_to_apply_discount = $this->get_all_lines_for_item($id.(isset($params['item']) && $params['item']->variation_id ? '#'.$params['item']->variation_id : ''), $kit);
		
		$return = array();
		
		foreach($item_lines_to_apply_discount as $line)
		{			
			
			if (!$this->get_item($line)->has_edit_price)
			{
				$this->get_item($line)->unit_price = $price_to_use;
			}
			
			if ($discount_percent !== NULL)
			{
				$this->get_item($line)->discount = $discount_percent;
			}
			$this->get_item($line)->rule = $rule;
			$return[] = $this->get_item($line);
		}
		
		return $return;
	}
	
	function do_price_rule_for_items_and_item_kits($params)
	{
		$CI =& get_instance();
		
		$rule = $CI->Price_rule->get_price_rule_for_item($params);
					
		return $rule;
	}
	
	function do_spending_price_rule($params)
	{
		$CI =& get_instance();
		
		$sub_total = $this->get_subtotal() - $this->get_flat_discount_amount() - $this->get_disabled_rules_subtotal();		
		$rule = $CI->Price_rule->get_price_rule_for_spending($params, $sub_total);
		return $rule;
	}
	
	
	function get_spending_price_rule_id()
	{
		$cart = $this->get_items();
		
		foreach($cart as $line=>$item)
		{
			
			if(isset($item->rule['type']) && $item->rule['type'] == 'spend_x_get_discount')
			{
				
				return $item->rule['id'];
			}
		}
		
		return null;
	}
	
	function get_spending_price_rule_discount()
	{
		$cart = $this->get_items();
		
		foreach($cart as $line=>$item)
		{
			
			if(isset($item->rule['type']) && $item->rule['type'] == 'spend_x_get_discount')
			{
				
				return $item->unit_price;
			}
		}
		
		return null;
	}
		
	function process_barcode_scan($barcode_scan_data,$options = array())
	{
		$CI =& get_instance();
		$CI->load->model('Item_kit_items');
		$mode = $this->get_mode();
		
		$qty_multiplier = 1;
		
		if($this->has_quantity_multiplier($barcode_scan_data))
		{
			$qty_multiplier = $this->get_quantity_multiplier($barcode_scan_data);
			
			$barcode_scan_data = substr($barcode_scan_data,$this->get_multiplier_finish_pos($barcode_scan_data) + 1);
		}
		
		if (isset($options['quantity']))
		{
			$quantity = $options['quantity']*$qty_multiplier;
		}
		else
		{
			$quantity = ($mode=="sale" || $mode == 'estimate' ? 1:-1)*$qty_multiplier;
		}
		
		$CI->load->model('Item_serial_number');
		
		$verify_age_needed = false;
		
		if($this->is_valid_receipt($barcode_scan_data) && $mode=='return')
		{
			$this->return_order($barcode_scan_data);
		}
		elseif($this->is_valid_item_kit($barcode_scan_data))
		{
			$item_kit_to_add = new PHPPOSCartItemKitSale(array('cart' => $this,'scan' => $barcode_scan_data,'quantity' => $quantity));
			
			if ($item_kit_to_add->default_quantity !== NULL)
			{
				@$item_kit_to_add->quantity = ($mode=="sale" || $mode == 'estimate' ? 1:-1)*$item_kit_to_add->default_quantity*$qty_multiplier;
			}
			
			if($this->find_similiar_item($item_kit_to_add) === FALSE)
			{
				$CI->view_data['info_popup_message'] = $item_kit_to_add->info_popup;		
				
				if (!$CI->view_data['info_popup_message'])
				{
					//Check category
					if ($item_kit_to_add->category_id)
					{
						$cat_info = $CI->Category->get_info($item_kit_to_add->category_id);
					
						if ($cat_info->category_info_popup)
						{
							$CI->view_data['info_popup_message'] = $cat_info->category_info_popup;
						}
					}
				}
				
			}
			
			$this->validate_and_add_cart_item($item_kit_to_add,$options);
			
			$item_kit_item_kits = $CI->Item_kit_items->get_info_kits($item_kit_to_add->get_id());
			foreach($item_kit_item_kits as $row)
			{
				$item_kit_item_kit_to_add = new PHPPOSCartItemKitSale(array('cart' => $this,'scan' => 'KIT '.$row->item_kit_id,'quantity' => $row->quantity));
				$this->validate_and_add_cart_item($item_kit_item_kit_to_add,array('quantity' => $quantity));
			}
			
			if ($CI->config->item('verify_age_for_products'))
			{
				$verify_age_needed = $item_kit_to_add->verify_age;
			}	
			
		}
		elseif($this->is_valid_item($barcode_scan_data))
		{
			$serialnumber = $CI->Item_serial_number->get_item_id($barcode_scan_data)!== FALSE ? $barcode_scan_data : NULL;
			if ($serialnumber)
			{
				$serial_number_price = $CI->Item_serial_number->get_price_for_serial($serialnumber);
				$serial_number_cost_price = $CI->Item_serial_number->get_cost_price_for_serial($serialnumber);
			}
			$item_to_add = new PHPPOSCartItemSale(array('cart' => $this,'scan' => $barcode_scan_data,'serialnumber' => $serialnumber,'quantity' => $quantity,'unit_price' => isset($serial_number_price) && $serial_number_price ? $serial_number_price : null,'cost_price' => isset($serial_number_cost_price) && $serial_number_cost_price ? $serial_number_cost_price : null));
			
			if ($item_to_add->default_quantity !== NULL && $item_to_add->default_quantity !== "")
			{
				@$item_to_add->quantity = ($mode=="sale" || $mode == 'estimate' ? 1:-1)*$item_to_add->default_quantity*$qty_multiplier;
			}
			
			if($this->find_similiar_item($item_to_add) === FALSE)
			{
				$CI->view_data['info_popup_message'] = $item_to_add->info_popup;
				
				if (!$CI->view_data['info_popup_message'])
				{
					//Check category
					if ($item_to_add->category_id)
					{
						$cat_info = $CI->Category->get_info($item_to_add->category_id);
					
						if ($cat_info->category_info_popup)
						{
							$CI->view_data['info_popup_message'] = $cat_info->category_info_popup;
						}
					}
				}
				
			}
			
			$this->validate_and_add_cart_item($item_to_add,$options);
			

			/**
			* @author Arslan Tariq
			* @param  Get Item Attributes and 
			* @return Create Array Item Variation, Explode Variation for Child 
			* Fetch Attributes Names
			**/
			$CI->load->model('Item_attribute');
			$item_attributes_available = $CI->Item_attribute->get_attributes_for_item($item_to_add->item_id);
			/* Fetch Variation Values */
			$variation = $item_to_add->variation_choices_model;
			if (!empty($variation)) {
			    $attributes_available   = array();
			    $attributes_final_array = array();
			    foreach ($variation as $variation_id => $single_variation) {
			        $variation_temp = array();
			        $variation_temp = explode(", ", trim($single_variation));

			        foreach ($variation_temp as $single_temp) {
			            $attributes_available[$variation_id][] = explode(": ", trim($single_temp))[1];
			        }
			    }
			    /*
				** Variations Loop for Child
				*/
			    foreach ($attributes_available as $key => $attibute) {
			        $total_index = count($attibute);
				        switch($total_index):
				        	case 0:
				        		@$attributes_final_array[$attibute[0]][$key] = NULL;
				        		break;
				        	case 1:
				        		@$attributes_final_array[$attibute[0]][$key] = NULL;
				        		break;
				        	case 2:
				        		@$attributes_final_array[$attibute[0]][$attibute[1]][$key] = NULL;
				        		break;
				        	case 3:
				        		@$attributes_final_array[$attibute[0]][$attibute[1]][$attibute[2]][$key] = NULL;
				        		break;
				        	case 4:
				        		@$attributes_final_array[$attibute[0]][$attibute[1]][$attibute[2]][$attibute[3]][$key] = NULL;
				        		break;
				        	case 5:
				        		@$attributes_final_array[$attibute[0]][$attibute[1]][$attibute[2]][$attibute[3]][$attibute[4]][$key] = NULL;			
				        		break;
				        	case 6:
				        		@$attributes_final_array[$attibute[0]][$attibute[1]][$attibute[2]][$attibute[3]][$attibute[4]][$attibute[5]][$key] = NULL;		
				        		break;
				        	case 7:
				        		@$attributes_final_array[$attibute[0]][$attibute[1]][$attibute[2]][$attibute[3]][$attibute[4]][$attibute[5]][$attibute[6]][$key] = NULL;		
				        		break;
				        	case 7:
				        		@$attributes_final_array[$attibute[0]][$attibute[1]][$attibute[2]][$attibute[3]][$attibute[4]][$attibute[5]][$attibute[6]][$key][$attibute[7]][$key] = NULL;		
				        		break;			
				        endswitch;
			    }

			    /*
				** Fetch Items Counts 
				** Get Cart Line Number
				*/
				$all_items 	= $this->get_items();
				/*
				** If Variation Exist Show Model
				*/
				
				$variations_for_item = $CI->Item_variations->get_variations($item_to_add->item_id);
				
				if (count($variations_for_item) && !$item_to_add->variation_id) {
					echo "<script language=\"javascript\">$('#choose_var').modal('show');</script>";
				}

				$CI->session->unset_userdata('popup');
				$CI->session->set_userdata('popup',$attributes_final_array);
				$CI->view_data['popup'] 		= $item_attributes_available;
				$CI->view_data['attributes']	= $item_attributes_available;
				$CI->view_data['show_model'] 	= $attributes_final_array;
				/*
				** Return Attributes Name and Model
				** END VARIATION 
				*/
			}
			if ($CI->config->item('verify_age_for_products'))
			{
				$verify_age_needed = $item_to_add->verify_age;
			}
		}
		
		if ($verify_age_needed && !$this->is_cart_age_verified())
		{
			$CI->view_data['do_verify_age'] = TRUE;			
		}
		
		
				
		if ($this->is_tax_inclusive() && count($this->get_excluded_taxes()) > 0)
		{
			$CI->view_data['warning'] = lang('sales_cannot_delete_taxes_if_using_tax_inclusive_items');
			$this->set_excluded_taxes(array());
		}
				
		if ($CI->config->item('edit_item_price_if_zero_after_adding'))
		{
			$last_item_price = $this->get_last_item_added_price();
			if ($last_item_price == 0 && $last_item_price !== FALSE)
			{
				$CI->view_data['price_zero'] = TRUE;
			}
		}		

		//We were able to add; now check if the last $line is below cost price
		if (isset($CI->view_data['success']) && $CI->view_data['success'])
		{
			
			if ($CI->config->item('scan_and_set_sales') || ((isset($item_kit_to_add) && $item_kit_to_add->default_quantity !== NULL && $item_kit_to_add->default_quantity == 0) || ( isset($item_to_add) && $item_to_add->default_quantity !== NULL && $item_to_add->default_quantity == 0 )))
			{
					$CI->view_data['quantity_set'] = TRUE;
			}
			
			$all_items = $this->get_items();
			
			if(isset($all_items[count($all_items) - 1]))
			{
			 	$last_item = $all_items[count($all_items) - 1];

	  	  if (!isset($last_item->rule) && $last_item->below_cost_price())
	  	  {
	 			  if ($CI->config->item('do_not_allow_below_cost'))
	 			  {
					  $this->delete_item(count($all_items) - 1);
					
			 			$CI->view_data['error'] = lang('sales_selling_item_below_cost');
					  $CI->view_data['success'] = FALSE;
	 			  }
	 			  else
	 			  {
	 				  $CI->view_data['warning'] = lang('sales_selling_item_below_cost');
	 			  }
	 		  }
			}
		}
		elseif($CI->config->item('allow_scan_of_customer_into_item_field'))
		{
			$CI->view_data = $this->select_customer($barcode_scan_data);
		}
		
	}
	
	public function validate_and_add_cart_item(PHPPOSCartItemBase $item_to_add,$options = array())
	{
		$CI =& get_instance();
		
	  if ($item_to_add->below_cost_price())
	  {
			if ($CI->config->item('do_not_allow_below_cost'))
			{
				$CI->view_data['error'] = lang('sales_selling_item_below_cost');
				return FALSE;
			}
			else
			{
				$CI->view_data['warning'] = lang('sales_selling_item_below_cost');
			}
		}
		
		if(get_class($item_to_add) == 'PHPPOSCartItemSale')
		{
			return $this->do_validate_and_add_cart_item($item_to_add,$options);
		}
		elseif(get_class($item_to_add) == 'PHPPOSCartItemKitSale')
		{
			return $this->do_validate_and_add_cart_item_kit($item_to_add,$options);			
		}
		
		return FALSE;
	}
	
	public function add_cart_item(PHPPOSCartItemBase $item_to_add,$options = array())
	{
		if(get_class($item_to_add) == 'PHPPOSCartItemSale')
		{
			return $this->do_add_cart_item($item_to_add,$options);
		}
		elseif(get_class($item_to_add) == 'PHPPOSCartItemKitSale')
		{
			return $this->do_add_cart_item_kit($item_to_add,$options);			
		}
		return FALSE;
		
	}
	
	private function do_add_cart_item(PHPPOSCartItemSale $item_to_add,$options = array())
	{
		$CI =& get_instance();
		
		if(isset($options['replace']) && $options['replace'])
		{
			if (!($similar_item = $this->find_similiar_item($item_to_add)))
			{
				return $this->add_item($item_to_add);
			}	
			else
			{
				return $this->replace($this->get_item_index($similar_item), $item_to_add);
			}
		}
		else
		{
			if ($item_to_add->is_serialized || (isset($options['no_group']) && $options['no_group']) || $CI->config->item('do_not_group_same_items') || !($similar_item = $this->find_similiar_item($item_to_add)))
			{
				return $this->add_item($item_to_add);
			}	
			else
			{
				//If our similiar item has a rule on it; then we want to add directly to cart instead of merging so rules apply correctly
				if(isset($similar_item->rule['type']) && (in_array($similar_item->rule['type'], array('buy_x_get_discount','buy_x_get_y_free', 'simple_discount'))))
				{
					return $this->add_item($item_to_add);
				}
				else
				{
					return $this->merge_item($item_to_add, $similar_item);	
				}
			}
		}
		
		return TRUE;
	}
	
	private function do_add_cart_item_kit(PHPPOSCartItemKitSale $item_kit_to_add,$options = array())
	{
		$CI =& get_instance();
		
		if ($item_kit_to_add->dynamic_pricing)
		{
			$item_kit_to_add->cost_price = $item_kit_to_add->get_cost_price_for_kit();
			$item_kit_to_add->unit_price = $item_kit_to_add->get_selling_price_for_kit();
		}
		
		return $this->add_item_kit($item_kit_to_add,$options);	
		return TRUE;
		
	}
	
	private function do_validate_and_add_cart_item(PHPPOSCartItemSale $item_to_add,$options = array())
	{
		$CI =& get_instance();
		
		$item_id = $item_to_add->item_id;
		$store_account_item_id = $CI->Item->get_store_account_item_id();
		$quantity = $item_to_add->quantity;
				
		if (isset($options['props']))
		{
			foreach($options['props'] as $key=>$value)
			{
				$item_to_add->$key = $value;
			}
		}
		
		if ($item_to_add->product_id == lang('common_giftcard'))
		{
			$CI->view_data['error']=lang('sales_unable_to_add_item');
			return FALSE;			
		}
		
		if (!$item_to_add->validate())
		{
			$CI->view_data['error']=lang('sales_unable_to_add_item');
			return FALSE;
		}
		if ($item_to_add->serialnumber && $this->is_serial_number_in_cart($item_to_add->serialnumber))
		{
			$CI->view_data['error']=lang('common_serialnumber_already_added');
			return FALSE;			
		}
		
		if ($this->get_mode() != 'estimate' && $item_to_add->will_be_out_of_stock($quantity))
		{
			if ($CI->config->item('do_not_allow_out_of_stock_items_to_be_sold'))
			{
					$CI->view_data['error']=lang('sales_unable_to_add_item_out_of_stock');
					return FALSE;
			}
			else
			{
				$CI->view_data['warning'] = lang('sales_quantity_less_than_zero');
			}
		}
		
		$this->do_add_cart_item($item_to_add,$options);
		
		if(isset($options['run_price_rules']) && $options['run_price_rules'] == TRUE)
		{
			$price_rule_params = array('item' => $item_to_add);
		 	$this->do_price_rules($price_rule_params);
		}
		
		$CI->view_data['success']= TRUE;
		$CI->view_data['success_no_message']= TRUE;
		
		return TRUE;
	}
	
	private function do_validate_and_add_cart_item_kit(PHPPOSCartItemKitSale $item_kit_to_add,$options = array())
	{
		$CI =& get_instance();
		
		$item_kit_id = $item_kit_to_add->item_kit_id;
		$quantity = $item_kit_to_add->quantity;
		
		if (isset($options['props']))
		{
			foreach($options['props'] as $key=>$value)
			{
				$item_kit_to_add->$key = $value;
			}
		}
		
		if (!$item_kit_to_add->validate())
		{
			$CI->view_data['error']=lang('sales_unable_to_add_item');
			return FALSE;
		}
		
		if ($this->get_mode() != 'estimate' && $item_kit_to_add->will_be_out_of_stock($quantity))
		{
			if ($CI->config->item('do_not_allow_out_of_stock_items_to_be_sold'))
			{
					$CI->view_data['error']=lang('sales_unable_to_add_item_out_of_stock');
					return FALSE;
			}
			else
			{
				$CI->view_data['warning'] = lang('sales_quantity_less_than_zero');
			}
		}
		$this->do_add_cart_item_kit($item_kit_to_add,$options);
		
		if(isset($options['run_price_rules']) && $options['run_price_rules'] == TRUE)
		{
			
			//if we have prices set then we want to add as a single unit
			if(!$item_kit_to_add->cost_price || !$item_kit_to_add->unit_price)
			{
		    foreach($item_kit_to_add->get_items($item_kit_to_add) as $item_kit_item)
				{
					$price_rule_params = array('item' => $item_kit_item);
				 	$this->do_price_rules($price_rule_params);
				}
			}
			else
			{
				$price_rule_params = array('item_kit' => $item_kit_to_add);
			 	$this->do_price_rules($price_rule_params);
			}
		}
		$CI->view_data['success']= TRUE;
		$CI->view_data['success_no_message']= TRUE;

		 return TRUE;
	}
	
	function set_coupons($coupons)
	{
		$this->coupons = $coupons;
		$price_rule_params = array();
		
		//Build up price_rules_params so we can apply them at the end; if we do it in loop the indexes changes of cart as we modify
  	foreach($this->get_items() as $line=>$item)
	  {
			if($line !== $this->get_index_for_flat_discount_item())
			{
				//if the item still exists
				if(($item_or_kit = $this->get_item($line))!==FALSE)
				{
					if (property_exists($item_or_kit,'item_id'))
					{
						$price_rule_params[] = array('item' => $item_or_kit, 'apply_coupons_only' => true);
					}
					else
					{
						$price_rule_params[] = array('item_kit' => $item_or_kit, 'apply_coupons_only' => true);
					}
				}
			}			
		}
		
		//Apply rules
		foreach($price_rule_params as $params)
		{
			$this->do_price_rules($params);
		}
	}
	
	function get_coupons()
	{
		return $this->coupons;
	}
	
	public function is_cart_age_verified()
	{
		$oldest_age = $this->get_cart_oldest_age();
		
		return $oldest_age === NULL || $this->age >= $oldest_age;
	}
	
	public function get_cart_oldest_age()
	{
		$oldest_age = NULL;
		
		foreach($this->get_items() as $item)
		{
			if ($item->verify_age && ($oldest_age == NULL || ($item->required_age >= $oldest_age)))
			{
				$oldest_age = $item->required_age;
			}
		}
		
		return $oldest_age;
	}
	
	public function select_customer($customer_id)
	{
		$data = array();
		$CI =& get_instance();
		$CI->load->model('Customer');
		
		if (strpos($customer_id,'|FORCE_PERSON_ID|') !== FALSE)
		{
			$customer_id = str_replace('|FORCE_PERSON_ID|','',$customer_id);
		}
		elseif ($CI->Customer->account_number_exists($customer_id))
		{
			$customer_id = $CI->Customer->customer_id_from_account_number($customer_id);
		}
		
		if ($CI->Customer->exists($customer_id))
		{
			$customer_info=$CI->Customer->get_info($customer_id);
		
		
			if ($customer_info->customer_info_popup)
			{
				$CI->view_data['info_popup_message'] = $customer_info->customer_info_popup;				
			}
			
		
			if ($customer_info->tier_id)
			{
				$this->previous_tier_id = $this->selected_tier_id;
				$this->selected_tier_id = $customer_info->tier_id;
				
				if ($this->previous_tier_id != $this->selected_tier_id)
				{
					$this->determine_new_prices_for_tier_change();
				}
			}
			
			$this->customer_id = $customer_id;
			if($CI->config->item('automatically_email_receipt'))
			{
				$this->email_receipt = 1;
			}

			if($CI->config->item('automatically_sms_receipt'))
			{
				$this->sms_receipt = 1;
			}
			
			if ($CI->config->item('point_value'))
			{
				$max_points = min(ceil($this->get_amount_due() / $CI->config->item('point_value')), (int)$customer_info->points);
			
				if ($max_points > 0)
				{
					$data['number_of_points_to_use'] = $max_points;
				}
			}
		}
		else
		{
			$data['error']=lang('sales_unable_to_add_customer');
		}
		$this->delete_all_paid_store_account_payment_ids();
		$this->save();
		
		return $data;
		
	}
	
	public function has_series_packages()
	{
		foreach($this->get_items() as $item)
		{
			if ($item->is_series_package)
			{
				return TRUE;
			}
		}
		
		return FALSE;
	}
	
	public function get_total_discount()
	{
		$CI =& get_instance();
		
		$CI->load->model('Item');
		$total_discount = 0;
		
		foreach($this->get_items() as $item)
		{
			
			//Gift cards don't count
			if ($item->name == lang('common_giftcard'))
			{
				continue;
			}
			
			if (property_exists($item,'item_id') && $item->item_id == $CI->Item->get_item_id_for_flat_discount_item())
			{
				$total_discount+=-1*$item->unit_price*$item->quantity;
			}
			else
			{
				
				if ($item->tax_included)
				{
					if (property_exists($item,'item_id'))
					{
						$price = get_price_for_item_including_taxes($item->item_id, $item->unit_price);
					}
					else
					{
						$price = get_price_for_item_kit_including_taxes($item->item_kit_id, $item->unit_price);
					}
					$total_discount+=$price*$item->quantity*$item->discount/100;
					
					
					if ($item->regular_price >= $price)
					{
						$total_discount+=($item->regular_price-$price)*$item->quantity;				
					}
					
				}
				else
				{
					$total_discount+=$item->unit_price*$item->quantity*$item->discount/100;
					
					if ($item->regular_price >= $item->unit_price)
					{
						$total_discount+=($item->regular_price-$item->unit_price)*$item->quantity;				
					}
				}
				
			}
			
		}
		
		return $total_discount;
	}
	
	public function get_taxes_taxjar()
	{
		$CI =& get_instance();
		
		$delivery_person_info = $this->delivery->delivery_person_info;
		
		$key_data = array();
		$key_data['location_address'] = $CI->Location->get_info_for_key('address');
		$key_data['delivery_address'] = $this->has_delivery ? $delivery_person_info : FALSE;
		$taxjar_key = md5(serialize($key_data));
		
		$return = array();
		
		if (!isset($this->taxjar_taxes[$taxjar_key]))
		{				
			if ($CI->config->item('tax_jar_location'))
			{
				$taxes = $this->fetch_taxjar_taxes_rates_for_location();
				
				if ($taxes && $taxes->combined_rate)
				{	
					$return = array();
				
					if ((float)$taxes->state_rate)
					{
						$return[] = array(
						'id' => -1,
						'name' => $taxes->state.' '.lang('common_state'),
						'percent' => $taxes->state_rate*100,
						'cumulative' => 0
						);
					}
				
					if ((float)$taxes->county_rate)
					{
						$return[] = array(
						'id' => -1,
						'name' => $taxes->county.' '.lang('common_county'),
						'percent' => $taxes->county_rate*100,
						'cumulative' => 0
						);
					}
				
					if ((float)$taxes->city_rate)
					{
						$return[] = array(
						'id' => -1,
						'name' => $taxes->city.' '.lang('common_city'),
						'percent' => $taxes->city_rate*100,
						'cumulative' => 0
						);
					}

					if ((float)$taxes->combined_district_rate)
					{
						$return[] = array(
						'id' => -1,
						'name' => lang('common_special_district'),
						'percent' => $taxes->combined_district_rate*100,
						'cumulative' => 0
						);
					}				
				
					$this->taxjar_taxes[$taxjar_key] = $return;
					$this->save();
				
				}
				else
				{
					return array();
				}
			}
			else
			{
				$order_taxes = $this->fetch_taxjar_taxes_taxes_for_order();
				
				if ($order_taxes && $order_taxes->amount_to_collect)
				{
					if ($order_taxes->breakdown->state_tax_rate)
					{
						if ($order_taxes->breakdown->state_tax_rate)
						{
							$return[] = array(
								'id' => -1,
								'name' => $order_taxes->jurisdictions->state.' '.lang('common_state'),
								'percent' => $order_taxes->breakdown->state_tax_rate*100,
								'cumulative' => 0
							);
						}

						if ($order_taxes->breakdown->county_tax_rate)
						{
							$return[] = array(
								'id' => -1,
								'name' => $order_taxes->jurisdictions->county.' '.lang('common_county'),
								'percent' => $order_taxes->breakdown->county_tax_rate*100,
								'cumulative' => 0
							);
						}
			
						if ($order_taxes->breakdown->city_tax_rate)
						{
							$return[] = array(
								'id' => -1,
								'name' => $order_taxes->jurisdictions->city.' '.lang('common_city'),
								'percent' => $order_taxes->breakdown->city_tax_rate*100,
								'cumulative' => 0
							);
						}
			
						if ($order_taxes->breakdown->special_tax_rate)
						{
							$return[] = array(
								'id' => -1,
								'name' => lang('common_special_district'),
								'percent' => $order_taxes->breakdown->special_tax_rate*100,
								'cumulative' => 0
							);
						}
					}
				
					$this->taxjar_taxes[$taxjar_key] = $return;
					$this->save();
				
				}
				else
				{
					return array();
				}
			}
			
		}
		
		return $this->taxjar_taxes[$taxjar_key];
	}
	
	public function fetch_taxjar_taxes_taxes_for_order()
	{
		$CI =& get_instance();
		
		$client = TaxJar\Client::withApiKey($CI->config->item('taxjar_api_key'));
		$delivery_person_info = $this->delivery->delivery_person_info;
		
		try
		{
			$counter = 1;
			
			$line_items = array();
			foreach($this->get_items() as $item)
			{
				$line_items[] = array(
				'id' => $counter,
				'quantity' => $item->quantity,
				'unit_price' => $item->unit_price,
				'discount' => ($item->unit_price*$item->quantity)*($item->discount/100)	
				);
				$counter++;
			}
			
			$CI->load->helper('text');
			$addressParts = addressToParts($CI->Location->get_info_for_key('address'));
			
			$taxes_for_order = [
		  'from_country' => 'US', 
		  'from_zip' => $addressParts['zip'],
		  'from_state' => $addressParts['state'],
		  'from_city' => $addressParts['city'],
		  'from_street' => $addressParts['street'],
		  'amount' => $this->get_subtotal()-$this->get_delivery_item_price_in_cart(),
		  'shipping' => $this->get_delivery_item_price_in_cart(),
			'line_items' => $line_items,
			
				/*'nexus_addresses' => [
					[
					"id" => "1",
					"country" => "US",
					"zip" => $addressParts['zip'],
					"state" => $addressParts['state'],
					"city" => $addressParts['city'],
					"street" => $addressParts['street'],
					]
				]*/];
				
				if ($delivery_person_info)
				{
				  $taxes_for_order['to_country'] = 'US';
				  $taxes_for_order['to_zip'] = $delivery_person_info['zip'];
				  $taxes_for_order['to_state'] = $delivery_person_info['state'];
				  $taxes_for_order['to_city'] = $delivery_person_info['city'];
				  $taxes_for_order['to_street'] = $delivery_person_info['address_1'];
				}
				else
				{
				  $taxes_for_order['to_country'] = 'US';
				  $taxes_for_order['to_zip'] = $addressParts['zip'];
				  $taxes_for_order['to_state'] = $addressParts['state'];
				  $taxes_for_order['to_city'] = $addressParts['city'];
				  $taxes_for_order['to_street'] = $addressParts['street'];
				}
			 return $client->taxForOrder($taxes_for_order);
			}
			catch(Exception $e)
			{
				$CI =& get_instance();
	
	 			$CI->view_data['error'] = lang('common_taxjar_error').' '.$e->getMessage();
				return FALSE;
			}
	}
	
	public function fetch_taxjar_taxes_rates_for_location()
	{
			$CI =& get_instance();

			$client = TaxJar\Client::withApiKey($CI->config->item('taxjar_api_key'));
			$delivery_person_info = $this->delivery->delivery_person_info;
			$addressParts = addressToParts($CI->Location->get_info_for_key('address'));
			if ($delivery_person_info)
			{

			  $zip = $delivery_person_info['zip'];

			  $tax_rate_params['country'] = 'US';
			  $tax_rate_params['state'] = $delivery_person_info['state'];
			  $tax_rate_params['city'] = $delivery_person_info['city'];
			  $tax_rate_params['street'] = $delivery_person_info['address_1'];
			}
			else
			{
			  $zip = $addressParts['zip'];
				
			  $tax_rate_params['country'] = 'US';
			  $tax_rate_params['state'] = $addressParts['state'];
			  $tax_rate_params['city'] = $addressParts['city'];
			  $tax_rate_params['street'] = $addressParts['street'];
			}
		
			try
			{
				$return = $client->ratesForLocation($zip, $tax_rate_params);
			}
			catch(Exception $e)
			{
				$CI =& get_instance();
	
	 			$CI->view_data['error'] = lang('common_taxjar_error').' '.$e->getMessage();
				return FALSE;
			}
			
			return $return;
	}
	
	public function get_fee_amount()
	{
		$fee_item_id = $this->get_index_for_credit_card_fee_item();
		
		if ($fee_item_id !== FALSE)
		{
			return $this->get_item($fee_item_id)->unit_price;
		}
		
		return 0;
	}
	
	
	function get_index_for_credit_card_fee_item()
	{
		$CI =& get_instance();
		
		$item_id_for_flat_credit_card_fee_item = $CI->Item->get_item_id_for_fee_item();
		
		$items = $this->get_items('PHPPOSCartItemSale');
		foreach ($items as $index=>$item )
		{
			if ($item->item_id == $item_id_for_flat_credit_card_fee_item)
			{
				return $index;
			}
		}
		
		return FALSE;
		
	}
	
}
