<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once('PHPPOSCart.php');

require_once('PHPPOSCartItemRecv.php');
require_once('PHPPOSCartItemKitRecv.php');
require_once('PHPPOSCartPaymentRecv.php');

class PHPPOSCartRecv extends PHPPOSCart
{		
	public $receiving_id;
	public $supplier_id;
	public $transfer_location_id;
	public $transfer_from_location_id;
	public $is_po;
	public $shipping_cost;
	public $receiving_exchange_details;
	
	public function __construct(array $params=array())
	{
		self::setup_defaults();
		parent::__construct($params);
	}
	
	public function is_valid_receipt($receipt_receiving_id)
	{
		//RECV #
		$pieces = explode(' ',$receipt_receiving_id);
		if(count($pieces)==2 && strtolower($pieces[0]) == 'recv')
		{
			$CI =& get_instance();
			return $CI->Receiving->exists($pieces[1]);
		}
		return false;	
	}
	
	public function return_order($receipt_receiving_id)
	{
		$pieces = explode(' ',$receipt_receiving_id);
		
		if(count($pieces)==2 && strtolower($pieces[0]) == 'recv')
		{
			$receiving_id = $pieces[1];
		}
		else
		{
			$receiving_id = $receipt_receiving_id;
		}
		
		$previous_cart = PHPPOSCartRecv::get_instance_from_recv_id($receiving_id);
		
		for($k=1;$k<=NUMBER_OF_PEOPLE_CUSTOM_FIELDS;$k++) 
		{
			$this->{"custom_field_${k}_value"} =$previous_cart->{"custom_field_${k}_value"};
		}
		
		$this->supplier_id = $previous_cart->supplier_id;
		$this->transfer_location_id = $previous_cart->transfer_location_id;
		$this->transfer_from_location_id = $previous_cart->transfer_from_location_id;
		$this->is_po = $previous_cart->is_po;
		$this->return_cart_items($previous_cart->get_items());
	}
	public static function get_instance_from_recv_id($receiving_id,$cart_id = NULL)
	{
		//MAKE SURE YOU NEVER set location_id, employee_id, or register_id in this method
		//This is because this will overwrite whatever we actual have for our context.
		//Setting these properties are just for the API
		
		$CI =& get_instance();
		$recv_info = $CI->Receiving->get_info($receiving_id)->row_array();
		
		$cart = new PHPPOSCartRecv(array('receiving_id' => $receiving_id,'cart_id' => $cart_id,'mode' => 'receive'));
		$paid_store_accounts = $CI->Receiving->get_store_accounts_paid_receivings($receiving_id);
		
		foreach($paid_store_accounts as $paid_store_account)
		{
			$cart->add_paid_store_account_payment_id($paid_store_account['receiving_id'],$paid_store_account['partial_payment_amount']);
		}
		
		foreach($CI->Receiving->get_receiving_items($receiving_id)->result() as $row)
		{
			$item_props = array();
			
			$cur_item_info = $CI->Item->get_info($row->item_id);
			$cur_item_location_info = $CI->Item_location->get_info($row->item_id,$recv_info['location_id']);

			$item_props['cart'] = $cart;
			$item_props['item_id'] = $row->item_id;
			
			$item_props['variation_id'] = $row->item_variation_id;
			
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
			
			$item_props['existed_previously'] = TRUE;
			$item_props['line'] = $row->line;
			$item_props['receipt_line_sort_order'] = $row->receipt_line_sort_order;
			$item_props['name'] = $cur_item_info->name;
			$item_props['category_id'] = $cur_item_info->category_id;
			$item_props['item_number'] = $cur_item_info->item_number;
			$item_props['product_id'] = $cur_item_info->product_id;
			$item_props['allow_alt_description'] = $cur_item_info->allow_alt_description;
			$item_props['is_serialized'] = $cur_item_info->is_serialized;
			$item_props['cost_price_preview'] = calculate_average_cost_price_preview($row->item_id, $row->item_variation_id,$row->item_unit_price, $row->quantity_purchased,$row->discount_percent);
			
			$item_props['quantity'] = $row->quantity_purchased;
			$item_props['unit_price'] = $row->item_unit_price;
			
			if(!isset($params['selling_price']))
			{
				$cur_item_variation_info = $CI->Item_variations->get_info($row->item_variation_id);
				
				if ($cur_item_variation_info && (double)$cur_item_variation_info->unit_price)
				{
					$item_props['selling_price'] = $cur_item_variation_info->unit_price;
				}
				else
				{
					$item_props['selling_price'] = $cur_item_info->unit_price;
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
			
			
			if ($cur_item_location_info->unit_price)
			{
				$item_props['location_selling_price'] = $cur_item_location_info->unit_price;
			}
			$item_props['cost_price'] = $row->item_cost_price;
			$item_props['discount'] = $row->discount_percent;
			$item_props['description'] = $row->description;
			$item_props['serialnumber'] = $row->serialnumber;
			$item_props['quantity_received'] = $row->quantity_received;
			$item_props['expire_date'] = $row->expire_date;
			$item_props['system_item'] = $cur_item_info->system_item;
			$item_props['size'] = $cur_item_info->size;
			$CI->load->model('Tag');
			$item_props['tag_ids'] = $CI->Tag->get_tag_ids_for_item($row->item_id); 			
			
			$item = new PHPPOSCartItemRecv($item_props);
			
			if ($row->override_taxes)
			{
				$item->set_override_taxes(unserialize($row->override_taxes));
			}
			
			$cart->add_item($item);
		}

		$cart->supplier_id = $CI->Receiving->get_supplier($receiving_id)->person_id;
		
		for($k=1;$k<=NUMBER_OF_PEOPLE_CUSTOM_FIELDS;$k++) 
		{
			$cart->{"custom_field_${k}_value"} = $recv_info["custom_field_${k}_value"];
		}
		
		
		$cart->set_exchange_details($CI->Receiving->get_exchange_details($receiving_id));
		
		$exchange_rate = $cart->get_exchange_rate() ? $cart->get_exchange_rate() : 1;
		foreach($CI->Receiving->get_receiving_payments($receiving_id)->result_array() as $row)
		{

			$row['payment_amount'] = $row['payment_amount']*$exchange_rate;
			$cart->add_payment(new PHPPOSCartPaymentRecv($row));
		}

		$cart->suspended = $recv_info['suspended'];
		$cart->is_po = $recv_info['is_po'];
		
		$cart->comment = $recv_info['comment'];
		$cart->transfer_location_id = $recv_info['transfer_to_location_id'];

		if ($recv_info['transfer_to_location_id'])
		{
			$cart->set_mode('transfer');
			$cart->transfer_from_location_id = $recv_info['location_id'];
		}
		
		$cart->set_excluded_taxes($CI->Receiving->get_deleted_taxes($receiving_id));
		$cart->set_override_taxes($CI->Receiving->get_override_taxes($receiving_id));
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
		return new PHPPOSCartRecv(array('cart_id' => $cart_id, 'mode' => 'receive'));
	}
	
	
	function setup_defaults()
	{		
		$this->set_mode('receive');
		$this->receiving_id = NULL;		
		$this->supplier_id = NULL;
		$this->location_id = NULL;
		$this->transfer_location_id = NULL;
		$this->transfer_from_location_id = NULL;
		$this->is_po = FALSE;
		$this->shipping_cost = NULL;
		$this->receiving_exchange_details = '';		
		
	}
	
	public function get_previous_receipt_id()
	{
		return $this->receiving_id;
	}
	
	function set_mode($mode)
	{
		parent::set_mode($mode);
		if ($mode == 'purchase_order')
		{
			$this->is_po = TRUE;
		}
		else
		{
			$this->is_po = FALSE;
		}
	}
	
	//Adds a kit to a recv. Normally we wouldn't have $CI->do_not_group_same_items checked in here as models should be dumb
	//However in this case with a receiving an item kit does NOT directly get added and instead items get added so when we pull back
	//receiving we never have a kit
	public function add_item_kit(PHPPOSCartItemKit $item_kit_to_add,$options = array())
	{
		$CI =& get_instance();
		
		for($k=0;$k<abs($item_kit_to_add->quantity);$k++)
		{			
	    foreach($item_kit_to_add->get_items($item_kit_to_add) as $item_kit_item)
	    {
				if($item_kit_to_add->quantity < 0)
				{
						$item_kit_item->quantity = $item_kit_item->quantity*-1;
				}
			
				if ($CI->config->item('do_not_group_same_items') || !($similar_item = $this->find_similiar_item($item_kit_item)))
				{
					$this->add_item($item_kit_item);
				}	
				else
				{
					$this->merge_item($item_kit_item, $similar_item);
			
				}
	    }
		}		
		return TRUE;
	}
	
	public function to_array()
	{
		$CI =& get_instance();		
		
		$data = array();
		$data['suspended']  = $this->suspended;
		$data['supplier_id']= $this->supplier_id;
		if($data['supplier_id'])
		{
			$supplier_info=$CI->Supplier->get_info($data['supplier_id']);
						
			$data['supplier']=$supplier_info->company_name;
			if ($supplier_info->first_name || $supplier_info->last_name)
			{
				$data['supplier'] .= ' ('.$supplier_info->first_name.' '.$supplier_info->last_name.')';
			}
			
			$data['supplier_address_1'] = $supplier_info->address_1;
			$data['supplier_address_2'] = $supplier_info->address_2;
			$data['supplier_balance'] = $supplier_info->balance;
			$data['has_balance'] = $supplier_info->balance > 0;
			$data['supplier_city'] = $supplier_info->city;
			$data['supplier_state'] = $supplier_info->state;
			$data['supplier_zip'] = $supplier_info->zip;
			$data['supplier_country'] = $supplier_info->country;
			$data['supplier_phone'] = $supplier_info->phone_number;
			$data['supplier_email'] = $supplier_info->email;
			$data['avatar']=$supplier_info->image_id ?  app_file_url($supplier_info->image_id) : base_url()."assets/img/user.png";			
		}
		
		$location_id=$this->transfer_location_id;
		if($location_id)
		{
			$info=$CI->Location->get_info($location_id);
			$data['location']=$info->name;
			$data['location_id']=$location_id;
		}
		
		
		$location_id=$this->transfer_from_location_id;
		if($location_id)
		{
			$info=$CI->Location->get_info($location_id);
			$data['location_from']=$info->name;
			$data['location_from_id']=$location_id;
		}
		

		$data['is_po'] = $this->is_po;
		$data['change_date_enable'] = $this->change_date_enable;
		$data['change_cart_date'] = $this->change_cart_date;

		$data['exchange_rate'] 					= $this->get_exchange_rate();
		$data['exchange_name'] 					= $this->get_exchange_name();
		$data['exchange_symbol'] 				= $this->get_exchange_currency_symbol();
		$data['exchange_symbol_location'] 		= $this->get_exchange_currency_symbol_location();
		$data['exchange_number_of_decimals'] 	= $this->get_exchange_currency_number_of_decimals();
		$data['exchange_thousands_separator'] 	= $this->get_exchange_currency_thousands_separator();
		$data['exchange_decimal_point'] 		= $this->get_exchange_currency_decimal_point();
		$data['exchange_details'] 				= $this->get_exchange_details();

		return array_merge(parent::to_array(),$data);
	}
		
	public function destroy()
	{
		parent::destroy();
		self::setup_defaults();
	}
	
	function add_item(PHPPOSCartItemBase $item,$add_to_end = TRUE)
	{
		$CI =& get_instance();		
		$CI->load->helper('items');
		$item->cost_price_preview = calculate_average_cost_price_preview($item->item_id,$item->variation_id, $item->unit_price, $item->quantity,$item->discount);
		
		$CI->view_data['success']= TRUE;
		$CI->view_data['success_no_message']= TRUE;
		
		return parent::add_item($item,$add_to_end);
	}
	
	function merge_item($item_merge_from, $item_merge_into)
	{
		parent::merge_item($item_merge_from,$item_merge_into);
		$CI =& get_instance();		
		$CI->load->helper('items');
		
		$CI->view_data['success']= TRUE;
		$CI->view_data['success_no_message']= TRUE;
		
		$item_merge_into->cost_price_preview = calculate_average_cost_price_preview($item_merge_into->item_id,$item_merge_into->variation_id, $item_merge_into->unit_price, $item_merge_into->quantity,$item_merge_into->discount);
	}
	
	
	function process_barcode_scan($barcode_scan_data,$options = array())
	{
		$CI =& get_instance();		
		
		$CI->load->model('Item_kit_items');
		
		$qty_multiplier = 1;
		
		if($this->has_quantity_multiplier($barcode_scan_data))
		{
			$qty_multiplier = $this->get_quantity_multiplier($barcode_scan_data);
			
			$barcode_scan_data = substr($barcode_scan_data,$this->get_multiplier_finish_pos($barcode_scan_data) + 1);
		}
		
		$mode = $this->get_mode();
		$quantity = ($mode=="receive" || $mode=="purchase_order" ? 1:-1)*$qty_multiplier;

		if($this->is_valid_receipt($barcode_scan_data) && $mode=='return')
		{
			$this->return_order($barcode_scan_data);
		}
		elseif($this->is_valid_item_kit($barcode_scan_data))
		{
			$item_kit_to_add = new PHPPOSCartItemKitRecv(array('scan' => $barcode_scan_data,'quantity' => $quantity));
			
			if ($item_kit_to_add->default_quantity !== NULL)
			{
				@$item_kit_to_add->quantity = ($mode=="receive" || $mode=="purchase_order" ? 1:-1)*$item_kit_to_add->default_quantity*$qty_multiplier;
			}
			
			if($item_kit_to_add->validate())
			{
				$this->add_item_kit($item_kit_to_add);
				
				$item_kit_item_kits = $CI->Item_kit_items->get_info_kits($item_kit_to_add->get_id());
				foreach($item_kit_item_kits as $row)
				{
					$item_kit_item_kit_to_add = new PHPPOSCartItemKitRecv(array('cart' => $this,'scan' => 'KIT '.$row->item_kit_id,'quantity' => $row->quantity*$item_kit_to_add->quantity));
					$this->add_item_kit($item_kit_item_kit_to_add);
				}
			}
			else
			{
				$CI->view_data['error']=lang('receivings_unable_to_add_item');
			}
		}
		else //Item
		{
			
			$CI->load->model('Item_serial_number');
			
			$serialnumber = $CI->Item_serial_number->get_item_id($barcode_scan_data)!== FALSE ? $barcode_scan_data : NULL;
			if ($serialnumber)
			{
				$serial_number_price = $CI->Item_serial_number->get_price_for_serial($serialnumber);
				$serial_number_cost_price = $CI->Item_serial_number->get_cost_price_for_serial($serialnumber);
			}
			
			$item_to_add = new PHPPOSCartItemRecv(array(
				'serialnumber' => $serialnumber,
				'selling_price' => isset($serial_number_price) && $serial_number_price ? $serial_number_price : null,
				'unit_price' => isset($serial_number_cost_price) && $serial_number_cost_price ? $serial_number_cost_price : null,
				'scan' => $barcode_scan_data,
				'quantity' => $quantity,
				'cart' => $this));
			/**
			* @author Arslan Tariq
			* @param  Get Item Attributes and 
			* @return Create Array Item Variation, Explode Variation for Child 
			* Fetch Attributes Names
			**/
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

				$CI->session->unset_userdata('rec_popup');
				$CI->session->set_userdata('rec_popup',$attributes_final_array);
				$CI->view_data['show_model'] 	= $attributes_final_array;
				/*
				** Return Attributes Name and Model
				** END VARIATION 
				*/
			}
			
			if ($item_to_add->default_quantity !== NULL && $item_to_add->default_quantity !== "")
			{
				@$item_to_add->quantity = ($mode=="receive" || $mode=="purchase_order" ? 1:-1)*$item_to_add->default_quantity*$qty_multiplier;
			}
			
			//If we don't have an item_id then we know it isn't valid
			if ($item_to_add->validate())
			{
				
				if ($CI->config->item('scan_and_set_recv') || (isset($item_to_add) && $item_to_add->default_quantity !== NULL && $item_to_add->default_quantity == 0 ))
				{
						$CI->view_data['quantity_set'] = TRUE;
				}
				
				if ($item_to_add->is_serialized || $CI->config->item('do_not_group_same_items') || !($similar_item = $this->find_similiar_item($item_to_add)))
				{
					$this->add_item($item_to_add);
					$all_items 	= $this->get_items();
				}	
				else
				{
					$this->merge_item($item_to_add, $similar_item);
				}
			}
			else
			{
				$CI->view_data['error']=lang('receivings_unable_to_add_item');
			}
		}
	}
	
	function will_be_out_of_stock()
	{
		$CI =& get_instance();
		$CI->load->model('Sale');
		$CI->load->model('Item_location');	
		
		foreach($this->get_items() as $cart_item)
		{
			$item_id = $cart_item->get_id();
			$quanity_added = abs($this->get_total_quantity_of_similar_items($cart_item));
						
				
			if ($cart_item->variation_id)
			{
				$CI->load->model('Item_variation_location');
				$item_location_quantity = $CI->Item_variation_location->get_location_quantity($cart_item->variation_id,$this->transfer_from_location_id);
			}
			else
			{
				$item_location_quantity = $CI->Item_location->get_location_quantity($item_id,$this->transfer_from_location_id);
			}
								
			if (!$cart_item->is_service && $item_location_quantity - $quanity_added < 0)
			{
				return true;
			}
		}			
		return false;
		
	}


	function get_exchange_rate()
	{
		$details = $this->receiving_exchange_details;
  		@list($rate, $name,$currency_symbol,$currency_symbol_location,$number_of_decimals,$thousands_separator,$decimal_point) = explode("|",$details);

		return $rate ? $rate : 1;
	}
	
	function get_exchange_name()
	{
		$details = $this->receiving_exchange_details;
  	@list($rate, $name,$currency_symbol,$currency_symbol_location,$number_of_decimals,$thousands_separator,$decimal_point) = explode("|",$details);
		
		return $name;
	}

	function get_exchange_currency_symbol()
	{
		$CI =& get_instance();
		
		$details = $this->receiving_exchange_details;
  	@list($rate, $name,$currency_symbol,$currency_symbol_location,$number_of_decimals,$thousands_separator,$decimal_point) = explode("|",$details);
		return $currency_symbol ? $currency_symbol : ($CI->config->item('currency_symbol') ? $CI->config->item('currency_symbol') : '$');
	}
	
	function get_exchange_currency_symbol_location()
	{
		$details = $this->receiving_exchange_details;
  	@list($rate, $name,$currency_symbol,$currency_symbol_location,$number_of_decimals,$thousands_separator,$decimal_point) = explode("|",$details);
		
		return $currency_symbol_location ? $currency_symbol_location : 'before';
		
	}
		
	function get_exchange_currency_number_of_decimals()
	{
		$details = $this->receiving_exchange_details;
  	@list($rate, $name,$currency_symbol,$currency_symbol_location,$number_of_decimals,$thousands_separator,$decimal_point) = explode("|",$details);
		
		return $number_of_decimals !=='' ? $number_of_decimals : '';
		
	}
		
	function get_exchange_currency_thousands_separator()
	{
		$details = $this->receiving_exchange_details;
  	@list($rate, $name,$currency_symbol,$currency_symbol_location,$number_of_decimals,$thousands_separator,$decimal_point) = explode("|",$details);
		
		return $thousands_separator ? $thousands_separator : ',';
		
	}
		
	function get_exchange_currency_decimal_point()
	{
		$details = $this->receiving_exchange_details;
  	@list($rate, $name,$currency_symbol,$currency_symbol_location,$number_of_decimals,$thousands_separator,$decimal_point) = explode("|",$details);
		
		return $decimal_point ? $decimal_point : '.';
	}
		
	function get_exchange_details()
	{
		return $this->receiving_exchange_details;
	}
	
	function set_exchange_details($rate_det)
	{
		$this->receiving_exchange_details = $rate_det;
	}
	
	function clear_exchange_details() 	
	{
		$this->receiving_exchange_details = NULL;
	}

	function get_total()
	{
		$exchange_rate 	= $this->get_exchange_rate() ? $this->get_exchange_rate() : 1;
		$total 			= 0;
		foreach($this->get_items() as $item)
		{
			$total+=$item->get_subtotal();
		}

		foreach(array_values($this->get_taxes()) as $tax)
		{
			$total+=$tax;
		}
		
		return to_currency_no_money($total*$exchange_rate);
	}

	public function get_subtotal()
	{
		$exchange_rate 	= $this->get_exchange_rate() ? $this->get_exchange_rate() : 1;
		$subtotal 		= 0;
		foreach($this->get_items() as $item)
		{
			$subtotal+=$item->get_subtotal();
		}
		
		return to_currency_no_money($subtotal*$exchange_rate);
	}
}