<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

abstract class PHPPOSCart
{	
	private $cart_id;
		
	private $cart_items;
	private $payments;
	private $excluded_taxes;
	private $mode;
	private $paid_store_account_ids;
	public $paid_store_account_amounts;
	
	//MAKE SURE YOU NEVER set location_id, employee_id, or register_id anywhere except API
	//This is because this will overwrite whatever we actual have for our context
	//Setting these properties are just for the API
	public $employee_id;
	public $location_id;
	public $register_id;
	
	public $comment;
	public $selected_payment;
	public $email_receipt;
	public $sms_receipt;
	public $suspended;
	public $change_date_enable;
	public $change_cart_date;
		
	public $is_editing_previous;
	
	public $custom_field_1_value;
	public $custom_field_2_value;
	public $custom_field_3_value;
	public $custom_field_4_value;
	public $custom_field_5_value;
	public $custom_field_6_value;
	public $custom_field_7_value;
	public $custom_field_8_value;
	public $custom_field_9_value;
	public $custom_field_10_value;


	public $work_order_custom_field_1_value;
	public $work_order_custom_field_2_value;
	public $work_order_custom_field_3_value;
	public $work_order_custom_field_4_value;
	public $work_order_custom_field_5_value;
	public $work_order_custom_field_6_value;
	public $work_order_custom_field_7_value;
	public $work_order_custom_field_8_value;
	public $work_order_custom_field_9_value;
	public $work_order_custom_field_10_value;
	
	public $override_tax_names;
	public $override_tax_percents;
	public $override_tax_cumulatives;
	public $override_tax_class;
	
	public $details_collapsed;
	public $skip_webhook;
	
	public $offset;
	public $limit;

	public $sort_column;
	public $sort_type;

	public function __construct(array $params = array())
	{
		self::setup_defaults();
		
		//params that get overwritten if any passed in
		foreach($params as $name=>$value)
		{
	 		if (property_exists($this,$name))
	 		{
	 	 	 	$this->$name = $value;
			}
		}
	}
	
		
	public function setup_defaults()
	{
		$CI =& get_instance();
		if (!isset($CI->view_data))
		{
			$CI->view_data = array();
		}
		
		$this->employee_id = NULL;
		$this->location_id = NULL;
		$this->register_id = NULL;
		$this->cart_items = array();
		$this->payments = array();
		$this->excluded_taxes = array();
		$this->mode = NULL;
		$this->paid_store_account_ids = array();
		$this->comment = '';
		$this->selected_payment = '';
		$this->email_receipt = FALSE;
		$this->sms_receipt = FALSE;
		$this->suspended = 0;
		$this->change_date_enable = FALSE;
		$this->change_cart_date = NULL;
		
		$this->is_editing_previous = FALSE;
		
		$this->custom_field_1_value = NULL;
		$this->custom_field_2_value = NULL;
		$this->custom_field_3_value = NULL;
		$this->custom_field_4_value = NULL;
		$this->custom_field_5_value = NULL;
		$this->custom_field_6_value = NULL;
		$this->custom_field_7_value = NULL;
		$this->custom_field_8_value = NULL;
		$this->custom_field_9_value = NULL;
		$this->custom_field_10_value = NULL;

		$this->work_order_custom_field_1_value = NULL;
		$this->work_order_custom_field_2_value = NULL;
		$this->work_order_custom_field_3_value = NULL;
		$this->work_order_custom_field_4_value = NULL;
		$this->work_order_custom_field_5_value = NULL;
		$this->work_order_custom_field_6_value = NULL;
		$this->work_order_custom_field_7_value = NULL;
		$this->work_order_custom_field_8_value = NULL;
		$this->work_order_custom_field_9_value = NULL;
		$this->work_order_custom_field_10_value = NULL;
		
		$this->override_tax_names = NULL;
		$this->override_tax_percents = NULL;
		$this->override_tax_cumulatives = NULL;
		$this->override_tax_class = NULL;
		$this->skip_webhook = FALSE;
		$this->offset = 0;

		$this->sort_column = NULL;
		$this->sort_type = NULL;

		$this->limit = $CI->config->item('number_of_items_per_page') ? (int)$CI->config->item('number_of_items_per_page') : 20;
		
		if (get_class($this) == 'PHPPOSCartSale' && $CI->config->item('collapse_sales_ui_by_default'))
		{
			$this->details_collapsed = TRUE;			
		}
		elseif(get_class($this) == 'PHPPOSCartRecv' && $CI->config->item('collapse_recv_ui_by_default'))
		{
			
		}
		else
		{
			$this->details_collapsed = FALSE;
		}
	}
	
	public function get_cart_id()
	{
		return $this->cart_id ? $this->cart_id : '';
	}
	
	function get_mode()
	{
		return $this->mode;
	}

	function set_mode($mode)
	{
		$this->mode = $mode;
	}
	
	function remove_mode()
	{
		$this->mode = NULL;
	}
	
	//This method prevents properties from being set that don't exist
	public function __set($property, $value)
	{
	    //Checking for non-existing properties
	    if (!property_exists($this, $property)) 
	    {
	        throw new Exception("Property {$property} does not exist");
	    }
	    $this->$property = $value;
	}

	function has_cash_payment()
	{
		foreach($this->get_payments() as $payment)
		{
			if($payment->payment_type ==  lang('common_cash'))
			{
				return true;
			}
		}
		
		return false;
	}
	
	public function get_payments_by_type($type)
	{
		$payments = array();
		foreach($this->get_payments() as $payment)
		{
			if ($payment->payment_type == $type)
			{
				$payments[] = $payment;
			}
		}
		
		return $payments;
	}

	//Gets total payment amount for a given type
	public function get_payment_amount($type)
	{
		$total = 0;
		
		foreach($this->get_payments_by_type($type) as $payment)
		{
			$total+=$payment->payment_amount;
		}
		
		return $total;
	}
	public function get_item($index)
	{		
		$items = $this->get_items();
		if (isset($items[$index]))
		{
			return $items[$index];
		}
		
		return FALSE;
	}	
	
	function get_item_index($item)
	{
		$items = $this->get_items();
		foreach($items as $k=>$item_in_cart)
		{
			if ($item_in_cart == $item)
			{
				return $k;
			}
		}
		return FALSE;
	}
	
	function sort_items($sort_receipt_column = 'name')
	{
		if ($sort_receipt_column == 'name')
		{
			usort($this->cart_items, array($this, "compare_by_name"));
		}
		elseif($sort_receipt_column == 'item_number')
		{
			usort($this->cart_items, array($this, "compare_by_item_number"));			
		}
		elseif($sort_receipt_column == 'product_id')
		{
			usort($this->cart_items, array($this, "compare_by_product_id"));			
		}
	}
	
	function compare_by_name($a, $b)
	{
	    return strcmp($b->name, $a->name);
	}

	function compare_by_item_number($a, $b)
	{
	    return strcmp($b->item_number, $a->item_number);
	}

	function compare_by_product_id($a, $b)
	{
	    return strcmp($b->product_id, $a->product_id);
	}
	
	public function get_items($class=NULL)
	{
		if ($class===NULL)
		{
			return $this->cart_items;
		}
		
		return array_filter($this->cart_items, function($value) use ($class)
		{
			return get_class($value) == $class;
		});
	}
	
	function get_items_with_item_id($item_id)
	{
		$cart_items = array();
		
		foreach($this->cart_items as $cart_item)
		{
			if(property_exists($cart_item,'item_id') && $cart_item->item_id == $item_id)
			{
				$cart_items[] = $cart_item;
			}
		}
		
		return $cart_items;
	}
	
	function get_item_kits_with_item_kit_id($item_kit_id)
	{
		$cart_item_kits = array();
		
		foreach($this->cart_items as $cart_item)
		{
			if(property_exists($cart_item,'item_kit_id') && $cart_item->item_kit_id == $item_kit_id)
			{
				$cart_item_kits[] = $cart_item;
			}
		}
		
		return $cart_item_kits;
	}
	
	function get_items_with_category_id($category_id)
	{
		$cart_items_that_apply = array();
		
		foreach($this->cart_items as $cart_item)
		{
			if ($cart_item->category_id == $category_id)
			{
				$cart_items_that_apply[] = $cart_item;
			}
		}
		
		return $cart_items_that_apply;
	}
	
	function get_items_with_manufacturer_id($manufacturer_id)
	{
		$cart_items_that_apply = array();
		
		foreach($this->cart_items as $cart_item)
		{
			if ($cart_item->manufacturer_id == $manufacturer_id)
			{
				$cart_items_that_apply[] = $cart_item;
			}
		}
		
		return $cart_items_that_apply;
	}
	
	function get_items_with_tag_id($tag_id)
	{
		$cart_items_that_apply = array();
		
		foreach($this->cart_items as $cart_item)
		{
			foreach($cart_item->tag_ids as $tag_id_in_cart)
			{
				if ($tag_id == $tag_id_in_cart)
				{
					$cart_items_that_apply[] = $cart_item;
					break;
				}
			}
		}
		
		return $cart_items_that_apply;
	}
	
	
	public function empty_items()
	{
		$this->cart_items = array();
	}
	
	public abstract function add_item_kit(PHPPOSCartItemKit $item_to_add,$options = array());
	
	public function add_item(PHPPOSCartItemBase $item_to_add,$add_to_end = TRUE)
	{
		$item_to_add->cart = $this;
		
		if($add_to_end)
		{
			$this->cart_items[] = $item_to_add;
		}
		else
		{
			array_unshift($this->cart_items,$item_to_add);
		}
		return TRUE;
	}

	public function replace($index, PHPPOSCartItemBase $item_replace)
	{
		$item_replace->cart = $this;
		$this->cart_items[$index] = $item_replace;
		return TRUE;
	}
	
	function get_similar_items($item_to_search_for)
	{
		$return = array();
		foreach($this->get_items(get_class($item_to_search_for)) as $item)
		{
			if ($item->get_id() == $item_to_search_for->get_id() && (!property_exists($item,'variation_id') || $item->variation_id == $item_to_search_for->variation_id) && (!property_exists($item,'quantity_unit_id') || $item->quantity_unit_id == $item_to_search_for->quantity_unit_id))
			{
				$return[] = $item;
			}
		}
		
		return $return;
	}
	
	function get_total_quantity_of_similar_items($item_to_search_for)
	{
		$items = $this->get_similar_items($item_to_search_for);
		
		$total_quantity = 0;
		
		foreach($items as $item)
		{
			$total_quantity+=$item->quantity;
		}
		
		return $total_quantity;
	}
	
	//Finds exsting items by comparing IDS and variation if they have property; they must also be in the same class to find a similar item
	//This method prefers finding items that do NOT have price rules
	public function find_similiar_item($item_to_search_for,$exclude = array())
	{		
		$return = FALSE;
		
		foreach($this->get_items(get_class($item_to_search_for)) as $item)
		{
			if (!in_array($item,$exclude) && $item->get_id() == $item_to_search_for->get_id() && (!property_exists($item,'variation_id') || $item->variation_id == $item_to_search_for->variation_id) && (!property_exists($item,'quantity_unit_id') || $item->quantity_unit_id == $item_to_search_for->quantity_unit_id))
			{
					$return = $item;
			}
		}
		
		//Try to get an item without a rule (loop though one more time)
		if (isset($return->rule) && $return->rule)
		{
			foreach($this->get_items(get_class($item_to_search_for)) as $item)
			{
				if (!in_array($item,$exclude) && $item->get_id() == $item_to_search_for->get_id() && (!property_exists($item,'variation_id') || $item->variation_id == $item_to_search_for->variation_id) && (!property_exists($item,'quantity_unit_id') || $item->quantity_unit_id == $item_to_search_for->quantity_unit_id))
				{
					//Prefer item without rule
					if (!$item->rule)
					{
						$return = $item;
					}
				}
			}
		}
		
		return $return;
	}
	
	public function move_item_to_top_of_cart($index)
	{
		$move_to_top_value = $this->cart_items[$index];

		unset($this->cart_items[$index]);
		$this->cart_items[] = $move_to_top_value;

		$move_to_top_value->receipt_line_sort_order = count($this->cart_items) + 1;
		$this->make_clean_order();
		$this->cart_items = array_values($this->cart_items);
	}
	
	public function merge_item($item_merge_from, $item_merge_into)
	{
		$CI =& get_instance();
		
		$index = 0;
		foreach($this->get_items($CI->config->item('always_put_last_added_item_on_top_of_cart') ? NULL : get_class($item_merge_into)) as $item)
		{
			if ($item === $item_merge_into)
			{
				$item_merge_into->quantity+=$item_merge_from->quantity;				
				if ($CI->config->item('always_put_last_added_item_on_top_of_cart'))
				{
					$this->move_item_to_top_of_cart($index);
				}
				
				return TRUE;
			}
			
			$index++;
		}
		
		return FALSE;
	}
	
	public function delete_item($index)
	{
		if (isset($this->cart_items[$index]))
		{
			unset($this->cart_items[$index]);
			$this->cart_items = array_values($this->cart_items);
		}
	}
			
	//Adds a payment to the cart
	public function add_payment(PHPPOSCartPaymentBase $payment)
	{
		$this->payments[] = $payment;
		return TRUE;
	}
	
	public function edit_payment($index,$data)
	{
		if (isset($this->payments[$index]))
		{
			foreach($data as $prop=>$value)
			{
				$this->payments[$index]->$prop = $value;
			}
		}
	}
	
	public function get_payment_ids($payment_type)
	{
		$payment_ids = array();
		
		$payments=$this->get_payments();
		
		for($k=0;$k<count($payments);$k++)
		{
			if ($payments[$k]->payment_type == $payment_type)
			{
				$payment_ids[] = $k;
			}
		}
		
		return $payment_ids;
	}
	
	public function delete_payment($payments)
	{
		if (is_array($payments))
		{
			foreach($payments as $payment_id)
			{
				unset($this->payments[$payment_id]);
			}
		}
		elseif (isset($this->payments[$payments]))
		{
			unset($this->payments[$payments]);
		}
		
		if (empty($this->payments))
		{
			$this->payments = array();
		}
		else
		{
			$this->payments = array_values($this->payments);
		}
	}
	
	public function set_payments(array $payments)
	{
		$this->payments = $payments;
	}
	
	//Gets payments for the cart
	public function get_payments()
	{
		return $this->payments;
	}
		
	function get_excluded_taxes() 
	{
		return $this->excluded_taxes;
	}

	function add_excluded_tax($name) 
	{
		if (!in_array($name, $this->excluded_taxes))
		{
			$this->excluded_taxes[] = $name;
			return TRUE;
		}
		return FALSE;
	}
	
	function delete_excluded_tax($name) 
	{
		if (in_array($name, $this->excluded_taxes))
		{
			unset($this->excluded_taxes[array_search($name,$this->excluded_taxes)]);
			$this->excluded_taxes = array_values($this->excluded_taxes);
			return TRUE;
		}
		return FALSE;
	}
	
	
	function get_paid_store_account_ids() 
	{
		return $this->paid_store_account_ids;
	}

	function add_paid_store_account_payment_id($id,$amount) 
	{
		$this->paid_store_account_ids[$id] = TRUE;
		$this->paid_store_account_amounts[$id] = $amount;
		return TRUE;
	}
	
	function delete_paid_store_account_id($id) 
	{
		unset($this->paid_store_account_ids[$id]);
		unset($this->paid_store_account_amounts[$id]);
		return TRUE;
	}
	
	function delete_all_paid_store_account_payment_ids()
	{
		$this->paid_store_account_ids = array();
		$this->paid_store_account_amounts = array();
	}
	
	function set_excluded_taxes(array $recv_excluded_taxes)
	{
		$this->excluded_taxes = $recv_excluded_taxes;
	}
	
	public function destroy()
	{
		self::setup_defaults();
	}
	
	public function get_subtotal()
	{
		$subtotal = 0;
		foreach($this->get_items() as $item)
		{
			$subtotal+=$item->get_subtotal();
		}
		
		return to_currency_no_money($subtotal);
	}

	function get_payments_total($payment_type = NULL)
	{
		$payments_total = 0;
		foreach($this->get_payments() as $payment)
		{
			if ($payment_type === NULL || $payment->payment_type == $payment_type)
			{
				$payments_total+=$payment->payment_amount;
			}
		}
		
		return to_currency_no_money($payments_total);
	}
	

	public function get_amount_due()
	{
		return to_currency_no_money($this->get_total() - $this->get_payments_total());
	}
		
	public function do_payments_cover_total()
	{
		$total_payments = 0;

		foreach($this->get_payments() as $payment)
		{
			$total_payments += $payment->payment_amount;
		}
		/* Changed the conditional to account for floating point rounding */
		if ( ( $this->get_mode() == 'sale' || $this->get_mode() == 'receive' || $this->get_mode() == 'purchase_order' || $this->get_mode() == 'store_account_payment' ) && (( to_currency_no_money( $this->get_total() ) - $total_payments ) > 1e-6 ) )
		{
			return false;
		}
	
		return true;
	}
	
	function get_total()
	{
		$total = 0;
		foreach($this->get_items() as $item)
		{
			$total+=$item->get_subtotal();
		}

		foreach(array_values($this->get_taxes()) as $tax)
		{
			$total+=$tax;
		}
		
		return to_currency_no_money($total);
	}
	
	function get_total_quantity()
	{
		$cart_count = 0;
		$CI =& get_instance();
		
		$CI->load->helper('language');
		
		$giftcard_langs = get_all_language_values_for_key('common_giftcard','giftcards');
		$purchase_points_langs = get_all_language_values_for_key('common_purchase_points','common');
		foreach($this->get_items() as $item)
	  { 
			if (!$item->system_item || in_array($item->product_id,$giftcard_langs) || in_array($item->product_id,$purchase_points_langs))
			{
	 	 		$cart_count = $cart_count + $item->quantity;
			}
		}
	 
	 return $cart_count;
	}
	
	
	public function get_taxes()
	{
		$taxes = array();
		foreach($this->get_items() as $line=>$item)
		{
			$item_taxes = $item->get_taxes();
			
			foreach($item_taxes as $name => $tax_amount)
			{
				if (!isset($taxes[$name]))
				{
					$taxes[$name] = 0;
				}
				
				$taxes[$name] += $tax_amount;
			}	
		}
		
		return $taxes;
	}
	
	function get_tax_total_amount()
	{
		$taxes = $this->get_taxes();
		$total_tax = 0;
		foreach($taxes as $name=>$value) 
		{
			$total_tax+=$value;
	 	}
		
		return to_currency_no_money($total_tax);
	}
	
	public abstract function is_valid_receipt($receipt_id);
	
	//Any string could be a valid item as long as not empty or null
	function is_valid_item($item)
	{
		return $item !='' && $item!== NULL;
	}
	
	public function is_valid_item_kit($item_kit_id)
	{
		$CI =& get_instance();
	
		//KIT #
		$pieces = explode(' ',$item_kit_id);

		if(count($pieces)==2 && strtolower($pieces[0]) == 'kit')
		{
			return $CI->Item_kit->exists($pieces[1]);
		}
		else
		{
			return $CI->Item_kit->get_item_kit_id($item_kit_id) !== FALSE;
		}
	}
	
	function get_valid_item_kit_id($item_kit_id)
	{
		$CI =& get_instance();
		
		//KIT #
		$pieces = explode(' ',$item_kit_id);

		if(count($pieces)==2 && strtolower($pieces[0]) == 'kit')
		{
			return $pieces[1];
		}
		else
		{
			return $CI->Item_kit->get_item_kit_id($item_kit_id);
		}
	}
	
	
	public abstract function return_order($id);
	
	function does_discount_exists()
	{
		foreach($this->cart_items as $item)
		{
			if($item->discount!=0)
			{
				return TRUE;
			}
		}
		return FALSE;
	}
	
	
	//Saves the state of the cart to session
	public function save()
	{		
		if ($this->cart_id !== NULL)
		{
			$CI =& get_instance();	
			$CI->session->set_userdata($this->cart_id, serialize($this));
		}
	}
	
	function return_cart_items($cart_items)
	{
		$this->cart_items = $cart_items;
		
		foreach($this->get_items() as $item)
		{
			$item->cart = $this;
			$item->quantity=-$item->quantity;
		}
	}
	
	public function to_array()
	{
		$data = array();
		$data['cart']=$this;
		$data['cart_items']=$this->get_items();
		$data['items_in_cart'] = count($data['cart_items']);
		$data['subtotal']=$this->get_subtotal();
		$data['payments']=$this->get_payments();
		$data['taxes']=$this->get_taxes();
		$data['total']=$this->get_total();
		$data['amount_due'] = $this->get_amount_due();
		$data['comment'] = $this->comment;
		$data['discount_exists'] = $this->does_discount_exists();
		$data['mode'] = $this->mode;
		$data['selected_payment'] = $this->selected_payment;
		$data['payments_cover_total'] = $this->do_payments_cover_total();
		$data['email_receipt'] = $this->email_receipt;
		$data['sms_receipt'] = $this->sms_receipt;
		$data['store_account_payment'] = $this->get_mode() == 'store_account_payment' ? 1 : 0;
		$data['change_date_enable'] = $this->change_date_enable;
		$data['suspended'] = $this->suspended;
		$data['paid_store_account_ids'] = $this->paid_store_account_ids;
		$data['change_cart_date'] = $this->change_cart_date;
		$data['details_collapsed'] = $this->details_collapsed;
		
		$CI =& get_instance();
		
		if ($CI->config->item('taxes_summary_on_receipt'))
		{
			$data['taxable_subtotal'] = $this->get_taxable_subtotal();
			$data['non_taxable_subtotal'] = $this->get_non_taxable_subtotal();;
		}
		return $data;
	}
	
	function get_taxable_subtotal()
	{
		$subtotal = 0;
		
		foreach($this->cart_items as $item)
		{
			if ($item->taxable)
			{
				$subtotal+=$item->get_subtotal(property_exists($this,'sale_id') && $this->sale_id ? $this->sale_id : FALSE);
			}
		}
		
		return $subtotal;
	}
	
	function get_non_taxable_subtotal()
	{
		$subtotal = 0;
		
		foreach($this->cart_items as $item)
		{
			if (!$item->taxable)
			{
				$subtotal+=$item->get_subtotal();
			}
		}
		return $subtotal;
		
	}
	
	function get_tax_subtotal($tax_name)
	{
		$subtotal = 0;
		foreach($this->get_items() as $item)
		{
			$item_taxes = $item->get_taxes();
			
			$tax_found = false;
			
			foreach(array_keys($item_taxes) as $search_tax_name)
			{
				if ($search_tax_name == $tax_name)
				{
					$tax_found = TRUE;
					break;
				}
			}
			
			if ($tax_found)
			{
				$subtotal+=to_currency_no_money($item->get_modifiers_subtotal() + ( $item->unit_price*$item->quantity-$item->unit_price*$item->quantity*$item->discount/100));
			}
		}
		
		return $subtotal;
	}
	
	function validate_payment($payment_type,$payment_amount,$payment_date = false)
	{
		$payment_date = $payment_date !== FALSE ? $payment_date : date('Y-m-d H:i:s');
		
		foreach($this->get_payments() as $payment)
		{
			if ($payment_type == $payment->payment_type && $payment->payment_amount == $payment_amount)
			{
				//Do a check based on timestamp to be a little more relaxed
				
				//If payment amount is within 5 seconds deny it
				$seconds_diff = strtotime($payment_date) - strtotime($payment->payment_date);
				if ($seconds_diff < 5)
				{
					return FALSE;
				}
			}
		}
		
		return TRUE;
	}
	
	function can_convert_cart_from_sale_to_return()
	{
		$cart = $this->get_items();
		
		if (!$cart || count($cart) == 0)
		{
			return FALSE;
		}
		
		foreach($cart as $cart_item)
		{	
			if ($cart_item->quantity < 0)
			{
				return false;
			}
		}
		unset($cart_item);
		
		return TRUE;
	}
	
	function do_convert_cart_from_sale_to_return()
	{
		$cart = $this->get_items();
		
		foreach($cart as $cart_item)
		{
			$cart_item->quantity = -1 * abs($cart_item->quantity);
		}
	}
	
	function can_convert_cart_from_return_to_sale()
	{
		$cart = $this->get_items();
		
		if (!$cart || count($cart) == 0)
		{
			return FALSE;
		}
		
		foreach($cart as $cart_item)
		{	
			if ($cart_item->quantity > 0)
			{
				return false;
			}
		}
		unset($cart_item);
		
		return TRUE;
	}
	
	function do_convert_cart_from_return_to_sale()
	{
		$cart = $this->get_items();
		
		foreach($cart as $cart_item)
		{
			$cart_item->quantity = 1 * abs($cart_item->quantity);
		}
	}
	
	function get_last_item_added_price()
	{
		$items = $this->get_items();
		
		if (!empty($items))
		{
			//Get last element then reset pointer so nothing gets messed
			$last_item = end($items);
			reset($items);
			return $last_item->unit_price;
		}		
	
		return FALSE;
	}
	
	function do_all_variation_items_have_variation_selected()
	{
		foreach($this->get_items() as $cart_item)
		{
			//If we have variation choices but don't have a variation id
			if (!empty($cart_item->variation_choices) && !$cart_item->variation_id)
			{
				return FALSE;
			}
		}
		return true;
	}
	
	public function edit_subtotal($new_subtotal)
	{
		$cur_subtotal = $this->get_subtotal();
		$subtotal_change = $new_subtotal - $cur_subtotal;
				
		foreach($this->get_items() as $item)
		{
			if ($cur_subtotal == 0)
			{
				if ($cur_subtotal == 0)
				{
					$percentage_of_cart = 1;
				}
				else
				{
					$percentage_of_cart = 0;
				}
			}
			else
			{
				$percentage_of_cart = ($item->get_subtotal() + $item->get_modifiers_subtotal()) / $cur_subtotal;		
			}
			
			$item_sub_total = $item->get_subtotal() + ($subtotal_change * $percentage_of_cart);	
			$item->unit_price = -1*((100*$item_sub_total)/($item->quantity*($item->discount-100)));
			
		}	
	}
	
	public function get_override_tax_info()
	{
		$tax_info = array();
		
		for($k=0;$k<5;$k++)
		{
			if(isset($this->override_tax_names[$k]) && $this->override_tax_names[$k])
			{
				$tax_info[$k]['name'] = $this->override_tax_names[$k];
				$tax_info[$k]['percent'] = $this->override_tax_percents[$k];
				$tax_info[$k]['cumulative'] = $this->override_tax_cumulatives[$k];
			}
		}
		
		return $tax_info;
	}
	
	public function get_override_taxes()
	{
		if ($this->override_tax_names)
		{
			return array(
			'override_tax_names' => $this->override_tax_names,
			'override_tax_percents' => $this->override_tax_percents,
			'override_tax_cumulatives' => $this->override_tax_cumulatives,
			);
		}
		
		return NULL;
	}
	
	function set_override_taxes($override_taxes)
	{
		$this->override_tax_names = isset($override_taxes['override_tax_names']) ? $override_taxes['override_tax_names'] : NULL;
		$this->override_tax_percents = isset($override_taxes['override_tax_percents']) ? $override_taxes['override_tax_percents'] : NULL;
		$this->override_tax_cumulatives = isset($override_taxes['override_tax_cumulatives']) ? $override_taxes['override_tax_cumulatives'] : NULL;
		$this->save();
		
	}
	
	function is_tax_overrided()
	{
		$tax_info = $this->get_override_tax_info();
		
		return !empty($tax_info) || $this->override_tax_class;
	}
		
	function has_quantity_multiplier($barcode_scan_data)
	{
		$multiplier_position = strpos($barcode_scan_data,'*');
		
		if($multiplier_position !== FALSE)
		{
			$mult_number = substr($barcode_scan_data,0,$multiplier_position);
			
			if (is_numeric($mult_number))
			{
				return TRUE;
			}
		}
		
		return FALSE;
	}
	
	function get_quantity_multiplier($barcode_scan_data)
	{
		$multiplier_position = strpos($barcode_scan_data,'*');
		
		if($multiplier_position !== FALSE)
		{
			$mult_number = substr($barcode_scan_data,0,$multiplier_position);
			
			if (is_numeric($mult_number))
			{
				return $mult_number;
			}
		}
		
		return 1;
		
	}
	
	function get_multiplier_finish_pos($barcode_scan_data)
	{
		return strpos($barcode_scan_data,'*');
	}
	
	function is_serial_number_in_cart($serialnumber)
	{
		$items = $this->get_items();
		foreach($items as $item)
		{
			if($item->serialnumber && $item->serialnumber == $serialnumber)
			{
				return TRUE;
			}
		}
		
		return FALSE;
	}

	abstract function get_previous_receipt_id();
	abstract function process_barcode_scan($barcode_scan_data,$options = array());

	//type asc, desc
	public function sort($column, $type){

		$this->sort_column = $column;
		$this->sort_type = $type;

		$items = $this->get_items();

		usort($items, array("PHPPOSCart", "cmp1"));

		foreach($items as $key=> $item){
			$item->receipt_line_sort_order = ($key+1);
		}
	}

	public function drag_drop($drag_index, $drop_index){

		if($drag_index == $drop_index) return;
		$items = $this->cart_items;
		$item_drag = $items[$drag_index];
		$item_drop = $items[$drop_index];

		$drag_index_order = $item_drag->receipt_line_sort_order;
		$drop_index_order = $item_drop->receipt_line_sort_order;

		$min_order = min($drag_index_order, $drop_index_order);
		$max_order = max($drag_index_order, $drop_index_order);

		$arrow = '';
		if($drag_index_order < $drop_index_order) $arrow = 'up';
		if($drag_index_order > $drop_index_order) $arrow = 'down';

		foreach($items as $index=> $item){
			if($item->receipt_line_sort_order >= $min_order && $item->receipt_line_sort_order <= $max_order ){
				if($arrow == 'up'){
					if($index == $drag_index){
						$item->receipt_line_sort_order = $max_order;
					}else{
						$item->receipt_line_sort_order = $item->receipt_line_sort_order - 1;
					}
				}else if($arrow == 'down'){
					if($index == $drag_index){
						$item->receipt_line_sort_order = $min_order;
					}else{
						$item->receipt_line_sort_order = $item->receipt_line_sort_order + 1;
					}
				}
			}
		}

		$this->sort_column = "";
		$this->make_clean_order();
	}

	public function cmp1($a, $b)
	{
		if($this->sort_column == "total"){
			$a1 = $a->unit_price * $a->quantity - $a->unit_price * $a->quantity * $a->discount / 100;
			$b1 = $b->unit_price * $b->quantity - $b->unit_price * $b->quantity * $b->discount / 100;
		}else{
			$a1 = $a->{$this->sort_column};
			$b1 = $b->{$this->sort_column};
		}
		if ($a1 == $b1) {
			return 0;
		}
		if($this->sort_type == 'desc')
			return ($b1 < $a1) ? -1 : 1;
		return ($a1 < $b1) ? -1 : 1;
	}

	public function sort_clean(){
		$this->sort_column =NULL;
		$this->sort_type =NULL;
	}

	public function get_list_sort_by_receipt_sort_order($items=array()){
		$items = empty($items) ? $this->cart_items : $items;
		foreach($items as $key=> $item){
			$item->line_index = $key;
		}
		$line = array_column($items, 'receipt_line_sort_order');
		array_multisort($line, SORT_ASC, $items);
		return $items;
	}

	public function make_clean_order(){
		//make clean order
		$items = $this->cart_items;
		$line = array_column($items, 'receipt_line_sort_order');
		array_multisort($line, SORT_ASC, $items);
		foreach($items as $key=> $item){
			$item->receipt_line_sort_order = ($key+1);
		}
	}
}
