<?php
require_once (APPPATH."traits/receivingTrait.php");

class Receiving extends MY_Model
{
	use receivingTrait;
	
	public function __construct()
	{
      parent::__construct();
		$this->load->model('Inventory');	
	}
	
	function is_receiving_deleted($receiving_id)
	{
		$query = $this->get_info($receiving_id);
		
		if($query->num_rows()==1)
		{
			return $query->row()->deleted;
		}
		
		return FALSE;
		
	}
	
	function is_receiving_undeleted($receiving_id)
	{
		$query = $this->get_info($receiving_id);
		
		if($query->num_rows()==1)
		{
			return !$query->row()->deleted;
		}
		
		return FALSE;
		
	}
	
	
	function is_store_accounts_paid_receiving_already_exist($recv_id)
	{
		$this->db->select('receiving_id');
		$this->db->from('supplier_store_accounts_paid_receivings');
		$this->db->where('receiving_id',$recv_id);
		
		$query = $this->db->get();

		return ($query->num_rows()>=1);
	}
	
	
	public function get_info($receiving_id)
	{
		$this->db->from('receivings');
		$this->db->where('receiving_id',$receiving_id);
		return $this->db->get();
	}

	function exists($receiving_id)
	{
		$this->db->from('receivings');
		$this->db->where('receiving_id',$receiving_id);
		$query = $this->db->get();

		return ($query->num_rows()==1);
	}
	
	function update($receiving_data, $receiving_id)
	{
		$this->db->where('receiving_id', $receiving_id);
		$success = $this->db->update('receivings',$receiving_data);
		
		return $success;
	}

	function save ($cart)
	{
		$exchange_rate = $cart->get_exchange_rate() ? $cart->get_exchange_rate() : 1;
		$exchange_name = $cart->get_exchange_name() ? $cart->get_exchange_name() : '';
		$exchange_currency_symbol = $cart->get_exchange_currency_symbol() ? $cart->get_exchange_currency_symbol() : '';
		$exchange_currency_symbol_location = $cart->get_exchange_currency_symbol_location() ? $cart->get_exchange_currency_symbol_location() : '';
		$exchange_number_of_decimals = ($cart->get_exchange_currency_number_of_decimals() !== '' && $cart->get_exchange_currency_number_of_decimals() !== NULL ) ? $cart->get_exchange_currency_number_of_decimals() : '';
		$exchange_thousands_separator = $cart->get_exchange_currency_thousands_separator() ? $cart->get_exchange_currency_thousands_separator() : '';
		$exchange_decimal_point = $cart->get_exchange_currency_decimal_point() ? $cart->get_exchange_currency_decimal_point() : '';

		$items = $cart->get_items();
		$supplier_id=$cart->supplier_id;
		$employee_id=$cart->employee_id ? $cart->employee_id : $this->Employee->get_logged_in_employee_info()->person_id;
		$comment =  $cart->comment ? $cart->comment : '';
		$payments =$cart->get_payments();
		$receiving_id=$cart->receiving_id;
		$mode = $cart->get_mode();
		$change_cart_date = $cart->change_date_enable ?  $cart->change_cart_date : false;
		$is_po =  $cart->is_po ? 1 : 0;
		$location_id=$cart->transfer_location_id;
		$store_account_payment = $cart->get_mode() == 'store_account_payment' ? 1 : 0;
		$suspended = $this->cart->suspended ? $this->cart->suspended : 0;
		$store_account_in_all_languages = get_all_language_values_for_key('common_store_account','common');
		
		$balance = 0;
		//Add up balances for all languages
		foreach($store_account_in_all_languages as $store_account_lang)
		{
			$balance+= $cart->get_payment_amount($store_account_lang)*pow($exchange_rate,-1);
		}
		if(count($items)==0)
			return -1;


		$is_new_receiving = $receiving_id ? false : true;

		if ($receiving_id)
		{
			$before_save_receiving_info = $this->get_info($receiving_id)->row();
		}
		else
		{
			$before_save_receiving_info = FALSE;
		}

		$deleted_taxes = $cart->get_excluded_taxes();
		$override_taxes = $cart->get_override_taxes();
		$payment_types='';
		foreach($payments as $payment_id=>$payment)
		{
			$payment_types=$payment_types.$payment->payment_type.': '.($exchange_rate == 1 ? to_currency($payment->payment_amount) : to_currency_as_exchange($cart,$payment->payment_amount)).'<br />';
		}
		
		//Clear currency exchange so it is saved right values for totals
		$cart->clear_exchange_details();

		//Reset payments back to regular default currency
		
		for($k=0;$k<count($payments);$k++)
		{
			$payments[$k]->payment_amount = $payments[$k]->payment_amount*pow($exchange_rate,-1);
		}

		$total_quantity_received = 0;
		$recv_total_qty = $cart->get_total_quantity(); 
		$recv_subtotal = $cart->get_subtotal();
		$recv_total = $cart->get_total();
		$recv_tax = $recv_total - $recv_subtotal;
		
		$receivings_data = array(
		'supplier_id'=> $supplier_id > 0 ? $supplier_id : null,
		'employee_id'=>$employee_id,
		'payment_type'=>$payment_types,
		'comment'=>$comment,
		'suspended' => $suspended,
		'location_id' => $cart->location_id ? $cart->location_id : ($cart->transfer_from_location_id ? $cart->transfer_from_location_id : $this->Employee->get_logged_in_employee_current_location_id()),
		'transfer_to_location_id' => $location_id > 0 ? $location_id : NULL,
		'deleted' => 0,
		'deleted_by' => NULL,
		'deleted_taxes' =>  $deleted_taxes? serialize($deleted_taxes) : NULL,
		'is_po' => $is_po,
		'store_account_payment' => $store_account_payment,
		'total_quantity_purchased' => $recv_total_qty,
		'subtotal' => $recv_subtotal,
		'total' => $recv_total,
		'tax' => $recv_tax,
		'profit' =>0,//Will update when recv complete,
		'last_modified' => $receiving_id ? date('Y-m-d H:i:s') : NULL,
		'override_taxes' =>  $override_taxes? serialize($override_taxes) : NULL,
		'shipping_cost' => $cart->shipping_cost ? $cart->shipping_cost: NULL,

		'exchange_rate' 					=> $exchange_rate,
		'exchange_name' 					=> $exchange_name,
		'exchange_currency_symbol' 			=> $exchange_currency_symbol,
		'exchange_currency_symbol_location' => $exchange_currency_symbol_location,
		'exchange_number_of_decimals' 		=> $exchange_number_of_decimals,
		'exchange_thousands_separator' 		=> $exchange_thousands_separator,

		
		);
		
		for($k=1;$k<=NUMBER_OF_PEOPLE_CUSTOM_FIELDS;$k++) 
		{
			$receivings_data["custom_field_${k}_value"] = $this->cart->{"custom_field_${k}_value"};
		}
		
				
		if($receiving_id)
		{
			$receivings_data['receiving_time']=$before_save_receiving_info->receiving_time;
		}
		else
		{
			$receivings_data['receiving_time'] = date('Y-m-d H:i:s');
		}
		
			
		$recv_profit = 0;
		//Run these queries as a transaction, we want to make sure we do all or nothing
		$this->db->trans_start();
		
		if($change_cart_date) 
		{
			$receiving_time = strtotime($change_cart_date);
			if($receiving_time !== FALSE)
			{
				$receivings_data['receiving_time']=date('Y-m-d H:i:s', strtotime($change_cart_date));
			}
		}
		
		$store_account_payment_amount = 0;
		
		if ($store_account_payment)
		{
			$store_account_payment_amount = $cart->get_total();
		}
		
		//Only update balance + store account payments if we are NOT suspended
		if (!$suspended)
		{
	   	  //Update customer store account balance
			  if($supplier_id > 0 && $balance)
			  {
				  $this->db->set('balance','balance+'.$balance,false);
				  $this->db->where('person_id', $supplier_id);
				  $this->db->update('suppliers');
			  }
			  
		     //Update customer store account if payment made
			if($supplier_id > 0 && $store_account_payment_amount)
			{
				$this->db->set('balance','balance-'.$store_account_payment_amount,false);
				$this->db->where('person_id', $supplier_id);
				$this->db->update('suppliers');
			 }
		 }
		 		 
		 $previous_store_account_amount = 0;

		 //If we have a previous recv but it wasn't suspended
		 if ($receiving_id !== FALSE && $before_save_receiving_info && !$before_save_receiving_info->suspended)
		 {
			 $previous_store_account_amount = $this->get_store_account_payment_total($receiving_id);
		 }
		
		
		if ($receiving_id)
		{
			$previous_receiving_items = $this->get_receiving_items($receiving_id)->result_array();
			//Delete previoulsy receving so we can overwrite data
			$this->delete($receiving_id, true);
			
			
			$this->db->where('receiving_id', $receiving_id);
			$this->db->update('receivings', $receivings_data);
		}
		else
		{
			$previous_receiving_items = array();
			$this->db->insert('receivings',$receivings_data);
			$receiving_id = $this->db->insert_id();
		}
		
		
		//store_accounts_paid_receivings
		$paid_receivings = $cart->get_paid_store_account_ids();
		if (!empty($paid_receivings))
		{			
			foreach(array_keys($cart->get_paid_store_account_ids()) as $receiving_id_paid)
			{
				
				$store_account_in_all_languages = get_all_language_values_for_key('common_store_account','common');
		
				$this->db->select('SUM(payment_amount) as full_receiving_charge', false);
				$this->db->from('receivings');
				$this->db->join('receivings_payments', 'receivings.receiving_id = receivings_payments.receiving_id');
				$this->db->where_in('receivings_payments.payment_type', $store_account_in_all_languages);
				$this->db->where('receivings.deleted',0);
				$this->db->where_in('receivings.receiving_id', $receiving_id_paid);				
				$this->db->order_by('receiving_time');
				$full_receiving_charge_row = $this->db->get()->row_array();
				$full_receiving_charge = $full_receiving_charge_row['full_receiving_charge'];
				
				if ($this->is_store_accounts_paid_receiving_already_exist($receiving_id_paid))
				{
					$this->db->select('partial_payment_amount');
					$this->db->from('supplier_store_accounts_paid_receivings');
					$this->db->where('receiving_id',$receiving_id_paid);
					$paid_amount_row_for_receiving = $this->db->get()->row_array();
					$already_paid_amount = $paid_amount_row_for_receiving['partial_payment_amount'];
					
					$is_paid_in_full = $full_receiving_charge <= $cart->paid_store_account_amounts[$receiving_id_paid] + $already_paid_amount;
					$new_partial_payment_amount = $cart->paid_store_account_amounts[$receiving_id_paid] + $already_paid_amount;
					$this->db->where('receiving_id',$receiving_id_paid);
					$this->db->update('supplier_store_accounts_paid_receivings',array('partial_payment_amount' => !$is_paid_in_full ? $new_partial_payment_amount : 0));
				}
				else
				{
					$is_paid_in_full = $full_receiving_charge <= $cart->paid_store_account_amounts[$receiving_id_paid];
					$this->db->insert('supplier_store_accounts_paid_receivings',array('receiving_id' => $receiving_id_paid,'store_account_payment_receiving_id' => $receiving_id,'partial_payment_amount' => !$is_paid_in_full ? $cart->paid_store_account_amounts[$receiving_id_paid] : 0));
				}
			}
		}		
		
		//Only update store account payments if we are not suspended
		if (!$suspended)
		{
			// Our supplier switched from before; add special logic
			if ($balance && $before_save_receiving_info && $before_save_receiving_info->supplier_id && $before_save_receiving_info->supplier_id != $supplier_id)
			{
				
				$store_account_transaction = array(
				   'supplier_id'=>$supplier_id,
				   'receiving_id'=>$receiving_id,
					'comment'=>$comment,
				   'transaction_amount'=>$balance,
					'balance'=>$this->Supplier->get_info($supplier_id)->balance,
					'date' => date('Y-m-d H:i:s')
				);

				$this->db->insert('supplier_store_accounts',$store_account_transaction);
				
				
				$store_account_transaction = array(
				   'supplier_id'=>$before_save_receiving_info->supplier_id,
				   'receiving_id'=>$receiving_id,
					'comment'=>$comment,
				   'transaction_amount'=>-$previous_store_account_amount,
					'balance'=>$this->Supplier->get_info($before_save_receiving_info->supplier_id)->balance,
					'date' => date('Y-m-d H:i:s')
				);

				$this->db->insert('supplier_store_accounts',$store_account_transaction);
				
			}
			elseif($supplier_id > 0 && $balance)
			{				
			 	$store_account_transaction = array(
 				   'supplier_id'=>$supplier_id,
 				   'receiving_id'=>$receiving_id,
					'comment'=>$comment,
			      'transaction_amount'=>$balance - $previous_store_account_amount,
					'balance'=>$this->Supplier->get_info($supplier_id)->balance,
					'date' => date('Y-m-d H:i:s')
				);
				
				if ($balance - $previous_store_account_amount)
				{
					$this->db->insert('supplier_store_accounts',$store_account_transaction);
				}
			 } 
			 elseif ($supplier_id > 0 && $previous_store_account_amount) //We had a store account payment before has one...We need to log this
			 {
 			 	$store_account_transaction = array(
  				   'supplier_id'=>$before_save_receiving_info->supplier_id,
  				   'receiving_id'=>$receiving_id,
 					'comment'=>$comment,
 			      'transaction_amount'=> -$previous_store_account_amount,
 					'balance'=>$this->Supplier->get_info($before_save_receiving_info->supplier_id)->balance,
 					'date' => date('Y-m-d H:i:s')
 				);

 				$this->db->insert('supplier_store_accounts',$store_account_transaction);
				
			 } //We switched customers for a receiving
			 //insert store account payment transaction 
			if($supplier_id > 0 && $store_account_payment)
			{
			 	$store_account_transaction = array(
			        'supplier_id'=>$supplier_id,
   				   'receiving_id'=>$receiving_id,
					'comment'=>$comment,
			       	'transaction_amount'=> -$store_account_payment_amount,
					'balance'=>$this->Supplier->get_info($supplier_id)->balance,
					'date' => date('Y-m-d H:i:s')
				);

				$this->db->insert('supplier_store_accounts',$store_account_transaction);
			 }
		 }		
		
		foreach($payments as $payment_id=>$payment)
		{
			$receivings_payments_data = array
			(
				'receiving_id'=>$receiving_id,
				'payment_type'=>$payment->payment_type,
				'payment_amount'=>$payment->payment_amount,
				'payment_date' => isset($override_payment_time) ? $override_payment_time: $payment->payment_date,
			);
			$this->db->insert('receivings_payments',$receivings_payments_data);
		}
		
		
		$store_account_item_id = $this->Item->get_store_account_item_id();
		$recv_profit = 0;
		
		$this->load->model('Item_variations');
		
		foreach($items as $line=>$item)
		{


			$cur_item_info = $this->Item->get_info($item->item_id);
			$cur_item_location_info = $this->Item_location->get_info($item->item_id,$cart->location_id ? $cart->location_id : ($cart->transfer_from_location_id ? $cart->transfer_from_location_id : $this->Employee->get_logged_in_employee_current_location_id()));
			
			if (isset($item->variation_id) && $item->variation_id)
			{
				$cur_item_variation_location_info = $this->Item_variation_location->get_info($item->variation_id,$cart->location_id ? $cart->location_id : ($cart->transfer_from_location_id ? $cart->transfer_from_location_id : $this->Employee->get_logged_in_employee_current_location_id()));
				$cur_item_variation_info = $this->Item_variations->get_info($item->variation_id);
			}
			else
			{
				if (isset($cur_item_variation_info))
				{
					unset($cur_item_variation_info);
				}
				
				if (isset($cur_item_variation_location_info))
				{
					unset($cur_item_variation_location_info);
				}
			}
			
			if($cur_item_info->unit_price !== $item->selling_price || (isset($cur_item_variation_info) && $cur_item_variation_info->unit_price != $item->selling_price))
			{
				if ((isset($cur_item_variation_info) && $cur_item_variation_info->unit_price != $item->selling_price))
				{
					if ($cur_item_variation_info->unit_price || (!$cur_item_variation_info->unit_price && $cur_item_info->unit_price !== $item->selling_price ))
					{
						$selling_price_item_variation_data = array('unit_price'=>$item->selling_price);
						$this->Item_variations->save($selling_price_item_variation_data, $item->variation_id);
					}
				}
				else
				{
					if (!$item->serialnumber)
					{
						if ($item->quantity_unit_quantity === NULL)
						{
							$selling_price_item_data = array('unit_price'=>$item->selling_price);
							$this->Item->save($selling_price_item_data,$item->item_id);
						}
					}
				}
			}
			
			if($cur_item_location_info->unit_price !== $item->location_selling_price || (isset($cur_item_variation_info) && $cur_item_variation_info->unit_price != $item->selling_price))
			{
				if ($item->location_selling_price !=0)
				{
					if ((isset($cur_item_variation_info) && $cur_item_variation_info->unit_price != $item->selling_price))
					{
						$selling_price_item_variation_data = array('unit_price'=>$item->location_selling_price);
						$this->Item_variation_location->save($selling_price_item_variation_data,$item->variation_id,$cart->location_id ? $cart->location_id : ($cart->transfer_from_location_id ? $cart->transfer_from_location_id : $this->Employee->get_logged_in_employee_current_location_id()));
						
					}
					else
					{
						$selling_price_item_data = array('unit_price'=>$item->location_selling_price);
						$this->Item_location->save($selling_price_item_data,$item->item_id,$cart->location_id ? $cart->location_id : ($cart->transfer_from_location_id ? $cart->transfer_from_location_id : $this->Employee->get_logged_in_employee_current_location_id()));
					}
				}
			}
			
			
			if ($item->item_id != $store_account_item_id)
			{
				if(isset($cur_item_variation_info) && $cur_item_variation_info->cost_price)
				{
					$cost_price = $cur_item_variation_info->cost_price;
				}
				else
				{
					$cost_price = ($cur_item_location_info && $cur_item_location_info->cost_price) ? $cur_item_location_info->cost_price : $cur_item_info->cost_price;
				}
			}
			else // Set cost price = price so we have no profit
			{
				$cost_price = $item->unit_price;
			}
			
			$item_unit_price_before_tax = $item->unit_price;
			
			$expire_date = NULL;
			
			if ($item->expire_date)
			{
				$expire_date = date('Y-m-d', strtotime($item->expire_date));				
			}
			
			$quantity_received = 0;
			
			if ($suspended != 0 && $item->quantity_received !== NULL)
			{
				$quantity_received = $item->quantity_received;
				$total_quantity_received+=$item->quantity_received;
			}
			elseif($suspended==0)
			{
				$quantity_received = $item->quantity;
				$total_quantity_received+=$item->quantity;
			}
			
			$recv_item_subtotal = $item->get_subtotal();
			$recv_item_total = $item->get_total();
			$recv_item_tax = $recv_item_total - $recv_item_subtotal;
			$recv_item_profit = $item->get_profit();
						
			$recv_profit+=$recv_item_profit;	
			
			$recv_items_override_taxes = $item->get_override_taxes();
				
			$receivings_items_data = array
			(
				'receiving_id'=>$receiving_id,
				'item_id'=>$item->item_id,
				'item_variation_id' => $item->variation_id ? $item->variation_id : NULL,
				'line'=>$line,
				'description'=>$item->description,
				'serialnumber'=>$item->serialnumber,
				'quantity_purchased'=>$item->quantity,
				'quantity_received'=>$quantity_received,
				'discount_percent'=>$item->discount,
				'item_cost_price' => $cost_price,
				'item_unit_price'=>$item->unit_price,
				'expire_date' => $expire_date,
				'subtotal' => $recv_item_subtotal,
				'total' => $recv_item_total,
				'tax' => $recv_item_tax,
				'profit' =>$recv_item_profit,
				'override_taxes' =>  $recv_items_override_taxes? serialize($recv_items_override_taxes) : NULL,	
				'unit_quantity' => $item->quantity_unit_quantity !== NULL ? $item->quantity_unit_quantity : NULL,
				'items_quantity_units_id' => $item->quantity_unit_id !== NULL ? $item->quantity_unit_id : NULL,		
			);
			
			// Remove all White Spaces & Comma
			$array_preg     		= preg_replace('/[ ,]+/', ',', $item->serialnumber);
			$array_preg 			= preg_replace('/\s+/', ',', $array_preg);
			$get_serial_range 		= explode(',', $array_preg);
	
			foreach ($get_serial_range as $key => $serial_range) 
			{
				if (!empty($serial_range)) {
					$this->load->model('Item_serial_number');
					$serial_cost_price = NULL;
					$serial_unit_price = NULL;
					
					if ($cur_item_info->cost_price != $item->unit_price)
					{
						$serial_cost_price = $item->unit_price;				
					}
						
					if ($cur_item_info->unit_price != $item->selling_price)
					{
						$serial_unit_price= $item->selling_price;
					}
					
					$this->Item_serial_number->add_serial($item->item_id, $serial_range,$serial_cost_price,$serial_unit_price);
				}
				
			}				
			
			$this->db->insert('receivings_items',$receivings_items_data);			
			
			if ($suspended == 0)
			{
				if (!$item->serialnumber)
				{
					if ($this->config->item('calculate_average_cost_price_from_receivings'))
					{
						if ($item->quantity_unit_quantity !== NULL)
						{
							$quantity_unit_cost_price = $item->unit_price;				
							$quantity_unit_selling_price= $item->selling_price;
							
							if (isset($quantity_unit_cost_price) || isset($quantity_unit_selling_price))
							{
								$quantity_unit_data = array(
								'item_id' => $item->item_id,
								'unit_price' => $quantity_unit_selling_price,
								'cost_price' => $quantity_unit_cost_price,
								);
							
								$this->Item->save_unit_quantity($quantity_unit_data, $item->quantity_unit_id);
							}
						}
						else
						{
							$receivings_items_data['item_unit_price_before_tax'] = $item_unit_price_before_tax;
							$this->calculate_and_update_average_cost_price_for_item($item->item_id, $item->variation_id, $receivings_items_data,$cart);
							unset($receivings_items_data['item_unit_price_before_tax']);
						}
					}
				}
			}
			
			//Update stock quantity IF not a service item
			if (!$cur_item_info->is_service)
			{
				
				//This means we never adjusted quantity_received so we should accept all
				if ($suspended == 0 && $item->quantity_received === NULL)
				{	
					$inventory_to_add = $item->quantity;
				}
				else
				{					
					if ($suspended == 0)
					{
						$inventory_to_add = $item->quantity;
					}
					else
					{
						$inventory_to_add = $item->quantity_received;
					}
				
				}
				
				$inventory_to_add = $inventory_to_add*($item->quantity_unit_quantity !== NULL ? $item->quantity_unit_quantity : 1);
				if ($item->variation_id)
				{
					//If we have a null quanity set it to 0, otherwise use the value
					$cur_item_variation_location_info->quantity = $cur_item_variation_location_info->quantity !== '' ? $cur_item_variation_location_info->quantity : 0;
					
					if ($inventory_to_add !=0)
					{
						$this->Item_variation_location->save_quantity( ($cur_item_variation_location_info->quantity ?  $cur_item_variation_location_info->quantity  : 0) + $inventory_to_add, $item->variation_id,$cart->location_id ? $cart->location_id : ($cart->transfer_from_location_id ? $cart->transfer_from_location_id : $this->Employee->get_logged_in_employee_current_location_id()));
						
						$recv_remarks ='RECV '.$receiving_id;
						$inv_data = array
						(
							'trans_date'=>date('Y-m-d H:i:s'),
							'trans_items'=>$item->item_id,
							'trans_user'=>$employee_id,
							'trans_comment'=>$recv_remarks,
							'trans_inventory'=>$inventory_to_add,
							'location_id'=>$cart->location_id ? $cart->location_id : ($cart->transfer_from_location_id ? $cart->transfer_from_location_id : $this->Employee->get_logged_in_employee_current_location_id()),
							'item_variation_id' => $item->variation_id,
							'trans_current_quantity' => ($cur_item_variation_location_info->quantity ?  $cur_item_variation_location_info->quantity  : 0) + $inventory_to_add,
							
						);
						$this->Inventory->insert($inv_data);
					}
					
				}
				else
				{
					//If we have a null quanity set it to 0, otherwise use the value
					$cur_item_location_info->quantity = $cur_item_location_info->quantity !== '' ? $cur_item_location_info->quantity : 0;
				
					if ($inventory_to_add !=0)
					{
						$this->Item_location->save_quantity($cur_item_location_info->quantity + $inventory_to_add, $item->item_id,$cart->location_id ? $cart->location_id : ($cart->transfer_from_location_id ? $cart->transfer_from_location_id : $this->Employee->get_logged_in_employee_current_location_id()));

						$recv_remarks ='RECV '.$receiving_id;
						$inv_data = array
						(
							'trans_date'=>date('Y-m-d H:i:s'),
							'trans_items'=>$item->item_id,
							'trans_user'=>$employee_id,
							'trans_comment'=>$recv_remarks,
							'trans_inventory'=>$inventory_to_add,
							'location_id'=>$cart->location_id ? $cart->location_id : ($cart->transfer_from_location_id ? $cart->transfer_from_location_id : $this->Employee->get_logged_in_employee_current_location_id()),
							'trans_current_quantity' => $cur_item_location_info->quantity + $inventory_to_add,
						
						);
						$this->Inventory->insert($inv_data);
					}
				}
			}
			
			if($suspended  == 0 && $mode=='transfer' && $location_id && !$cur_item_info->is_service)
			{				
				
				if ($item->variation_id)
				{
					$item_loc_var_to_save_qty = ($this->Item_variation_location->get_location_quantity($item->variation_id,$location_id) + ($item->quantity * -1))*($item->quantity_unit_quantity !== NULL ? $item->quantity_unit_quantity : 1);
					$this->Item_variation_location->save_quantity($item_loc_var_to_save_qty,$item->variation_id,$location_id);

					if (!isset($inv_data))
					{
						$inv_data = array
						(
							'trans_date'=>date('Y-m-d H:i:s'),
							'trans_items'=>$item->item_id,
							'trans_user'=>$employee_id,
							'trans_comment'=>'RECV '.$receiving_id,
							'item_variation_id' => $item->variation_id,
						);
					}
				
					//Change values from $inv_data above and insert
					$inv_data['trans_inventory']=($item->quantity*($item->quantity_unit_quantity !== NULL ? $item->quantity_unit_quantity : 1)) * -1;
					$inv_data['location_id']=$location_id;
					$inv_data['trans_current_quantity'] = $item_loc_var_to_save_qty;
					$this->Inventory->insert($inv_data);
					
				}
				else
				{
					$item_loc_qty_to_save = ($this->Item_location->get_location_quantity($item->item_id,$location_id) + ($item->quantity * -1))*($item->quantity_unit_quantity !== NULL ? $item->quantity_unit_quantity : 1);
					$this->Item_location->save_quantity($item_loc_qty_to_save,$item->item_id,$location_id);
				
					if (!isset($inv_data))
					{
						$inv_data = array
						(
							'trans_date'=>date('Y-m-d H:i:s'),
							'trans_items'=>$item->item_id,
							'trans_user'=>$employee_id,
							'trans_comment'=>'RECV '.$receiving_id,
						);
					}
				
					//Change values from $inv_data above and insert
					$inv_data['trans_inventory']=($item->quantity*($item->quantity_unit_quantity !== NULL ? $item->quantity_unit_quantity : 1)) * -1;
					$inv_data['location_id']=$location_id;
					$inv_data['trans_current_quantity'] = $item_loc_qty_to_save;
					
					$this->Inventory->insert($inv_data);
				}
			}		

			if ($this->config->item('charge_tax_on_recv'))
			{
				foreach($this->Item_taxes_finder->get_info($item->item_id,'receiving',$item) as $row)
				{
					$tax_name = $row['percent'].'% ' . $row['name'];
	
					//Only save receiving if the tax has NOT been deleted
					if (!in_array($tax_name, $cart->get_excluded_taxes()))
					{	
						$this->db->insert('receivings_items_taxes', array(
							'receiving_id' 	=>$receiving_id,
							'item_id' 	=>$item->item_id,
							'line'      =>$line,
							'name'		=>$row['name'],
							'percent' 	=>$row['percent'],
							'cumulative'=>$row['cumulative']
						));
					}
				}
			}
		}	
		
		//TODO update for ($item->quantity_unit_quantity !== NULL ? $item->quantity_unit_quantity : 1)	
		$this->update(array('profit'=> $recv_profit,'total_quantity_received' => $total_quantity_received),$receiving_id);
		$this->db->trans_complete();
		
		if ($this->db->trans_status() === FALSE)
		{
			return -1;
		}
		
		if (!$cart->skip_webhook)
		{
			if($this->config->item('new_receiving_web_hook') && $is_new_receiving)
			{
				$this->load->helper('webhook');
				do_webhook($this->recv_id_to_array($receiving_id),$this->config->item('new_receiving_web_hook'));
			}	
			elseif($this->config->item('edit_recv_web_hook'))
			{
				$this->load->helper('webhook');
				do_webhook($this->recv_id_to_array($receiving_id),$this->config->item('edit_recv_web_hook'));			
			}
		}
		
		return $receiving_id;
	}
	
	function get_store_account_payment_total($receiving_id)
	{
		$this->db->select('SUM(payment_amount) as store_account_payment_total', false);
		$this->db->from('receivings_payments');
		$this->db->where('receiving_id', $receiving_id);
		$store_account_in_all_languages = get_all_language_values_for_key('common_store_account','common');
		$this->db->where_in('payment_type', $store_account_in_all_languages);
		
		$recevings_payments = $this->db->get()->row_array();	
		
		return $recevings_payments['store_account_payment_total'] ? $recevings_payments['store_account_payment_total'] : 0;
	}
	
	function delete($receiving_id, $all_data = false, $update_quantity = true)
	{
		$recv_info = $this->get_info($receiving_id)->row_array();	
		
		$suspended = $recv_info['suspended'];
		
		$employee_id=$this->Employee->get_logged_in_employee_info()->person_id ? $this->Employee->get_logged_in_employee_info()->person_id : 1;
		
		if ($suspended  == 0)
		{
			$this->update_store_account($receiving_id);
			
			//Only insert store account transaction if we aren't deleting the whole sale.
			//When deleting the whole sale save() takes care of this
			if (!$all_data)
			{
		 		$previous_store_account_amount = $this->get_store_account_payment_total($receiving_id);
			
				if ($previous_store_account_amount)
				{	
					$store_account_transaction = array(
			   		'supplier_id'=>$recv_info['supplier_id'],
			      	'receiving_id'=>$receiving_id,
						'comment'=>$recv_info['comment'],
			      	'transaction_amount'=>-$previous_store_account_amount,
						'balance'=>$this->Supplier->get_info($recv_info['supplier_id'])->balance,
						'date' => date('Y-m-d H:i:s')
					);
					$this->db->insert('supplier_store_accounts',$store_account_transaction);
				}
			}
			
		}
		
		if ($update_quantity)
		{
			$this->db->select('unit_quantity,receivings.location_id, item_id, quantity_purchased, quantity_received, transfer_to_location_id,item_variation_id');
			$this->db->from('receivings_items');
			$this->db->join('receivings', 'receivings.receiving_id = receivings_items.receiving_id');
			$this->db->where('receivings.receiving_id', $receiving_id);
		
			foreach($this->db->get()->result_array() as $receiving_item_row)
			{
				
				$receiving_location_id = $receiving_item_row['location_id'];
				$cur_item_info = $this->Item->get_info($receiving_item_row['item_id']);	
				
				$previous_amount_received = $receiving_item_row['quantity_received'];
	
				if ($suspended != 0)
				{
					$inventory_to_remove = $receiving_item_row['quantity_received'];
				}
				else
				{
					$inventory_to_remove = $receiving_item_row['quantity_purchased'];
				}
				
				$inventory_to_remove = $inventory_to_remove*($receiving_item_row['unit_quantity'] !== NULL ? $receiving_item_row['unit_quantity'] : 1);
				
				if ($receiving_item_row['item_variation_id'])
				{
					$cur_item_variation_location_info = $this->Item_variation_location->get_info($receiving_item_row['item_variation_id'],$receiving_location_id);
		
					if ($inventory_to_remove !=0)
					{
						$this->Item_variation_location->save_quantity($cur_item_variation_location_info->quantity - $inventory_to_remove,$receiving_item_row['item_variation_id'],$receiving_location_id);
		
						$recv_remarks ='RECV '.$receiving_id;
						$inv_data = array
						(
							'trans_date'=>date('Y-m-d H:i:s'),
							'trans_items'=>$receiving_item_row['item_id'],
							'trans_user'=>$employee_id,
							'trans_comment'=>$recv_remarks,
							'trans_inventory'=>$inventory_to_remove*-1,
							'location_id'=>$receiving_location_id,
							'item_variation_id' => $receiving_item_row['item_variation_id'],
							'trans_current_quantity' => $cur_item_variation_location_info->quantity - $inventory_to_remove,
						);
						$this->Inventory->insert($inv_data);
					}
				}
				else
				{
					$cur_item_location_info = $this->Item_location->get_info($receiving_item_row['item_id'],$receiving_location_id);
		
		
					if ($inventory_to_remove !=0)
					{
						$this->Item_location->save_quantity($cur_item_location_info->quantity - $inventory_to_remove,$receiving_item_row['item_id'],$receiving_location_id);
		
						$recv_remarks ='RECV '.$receiving_id;
						$inv_data = array
						(
							'trans_date'=>date('Y-m-d H:i:s'),
							'trans_items'=>$receiving_item_row['item_id'],
							'trans_user'=>$employee_id,
							'trans_comment'=>$recv_remarks,
							'trans_inventory'=>$inventory_to_remove*-1,
							'location_id'=>$receiving_location_id,
							'trans_current_quantity' => $cur_item_location_info->quantity - $inventory_to_remove,
							
						);
						$this->Inventory->insert($inv_data);
					}
				}
		
		
				if ($suspended  == 0 && $receiving_item_row['transfer_to_location_id'])
				{
					
					if ($receiving_item_row['item_variation_id'])
					{
						$cur_item_variation_location_transfer_info = $this->Item_variation_location->get_info($receiving_item_row['item_variation_id'], $receiving_item_row['transfer_to_location_id']);
				
						$this->Item_variation_location->save_quantity($cur_item_variation_location_transfer_info->quantity + $inventory_to_remove,$receiving_item_row['item_variation_id'], $receiving_item_row['transfer_to_location_id']);
		
						$receiving_remarks ='RECV '.$receiving_id;
						$inv_data = array
							(
							'trans_date'=>date('Y-m-d H:i:s'),
							'trans_items'=>$receiving_item_row['item_id'],
							'trans_user'=>$employee_id,
							'trans_comment'=>$receiving_remarks,
							'trans_inventory'=>$inventory_to_remove * 1,
							'location_id'=>$receiving_item_row['transfer_to_location_id'],
							'item_variation_id' => $receiving_item_row['item_variation_id'],
							'trans_current_quantity' => $cur_item_variation_location_transfer_info->quantity + $inventory_to_remove,
							
							);
							$this->Inventory->insert($inv_data);
						
					}
					else
					{
						$cur_item_location_transfer_info = $this->Item_location->get_info($receiving_item_row['item_id'], $receiving_item_row['transfer_to_location_id']);
				
						$this->Item_location->save_quantity($cur_item_location_transfer_info->quantity + $inventory_to_remove,$receiving_item_row['item_id'], $receiving_item_row['transfer_to_location_id']);

						$receiving_remarks ='RECV '.$receiving_id;
						$inv_data = array
							(
							'trans_date'=>date('Y-m-d H:i:s'),
							'trans_items'=>$receiving_item_row['item_id'],
							'trans_user'=>$employee_id,
							'trans_comment'=>$receiving_remarks,
							'trans_inventory'=>$inventory_to_remove * 1,
							'location_id'=>$receiving_item_row['transfer_to_location_id'],
							'trans_current_quantity' => $cur_item_location_transfer_info->quantity + $inventory_to_remove,
							
							);
							$this->Inventory->insert($inv_data);
						
					}	
				}
			 
			}
		}
		
		if ($all_data)
		{
			$this->db->delete('receivings_items', array('receiving_id' => $receiving_id));
			$this->db->delete('receivings_items_taxes', array('receiving_id' => $receiving_id));
			$this->db->delete('receivings_payments', array('receiving_id' => $receiving_id));
		}
		
		$this->db->where('receiving_id', $receiving_id);
		return $this->db->update('receivings', array('deleted' => 1,'deleted_by'=>$employee_id,'last_modified' => date('Y-m-d H:i:s')));
	}
	
	function undelete($receiving_id)
	{
		$employee_id=$this->Employee->get_logged_in_employee_info()->person_id;
	
		$recv_info = $this->get_info($receiving_id)->row_array();		
		$suspended = $recv_info['suspended'];
		
		if ($suspended == 0)
		{
			$this->update_store_account($receiving_id,1);
			
		 	$previous_store_account_amount = $this->get_store_account_payment_total($receiving_id);
			if ($previous_store_account_amount)
			{					
			 	$store_account_transaction = array(
			   		'supplier_id'=>$recv_info['supplier_id'],
			      	'receiving_id'=>$receiving_id,
						'comment'=>$recv_info['comment'],
			      'transaction_amount'=>$previous_store_account_amount,
				'balance'=>$this->Supplier->get_info($recv_info['supplier_id'])->balance,
				'date' => date('Y-m-d H:i:s')
				);
				$this->db->insert('supplier_store_accounts',$store_account_transaction);
			}
			
		}
		
		$this->db->select('unit_quantity,receivings.location_id, item_id, quantity_purchased, quantity_received, transfer_to_location_id,item_variation_id');
		$this->db->from('receivings_items');
		$this->db->join('receivings', 'receivings.receiving_id = receivings_items.receiving_id');
		$this->db->where('receivings.receiving_id', $receiving_id);
	
		foreach($this->db->get()->result_array() as $receiving_item_row)
		{
			$receiving_location_id = $receiving_item_row['location_id'];
			$cur_item_info = $this->Item->get_info($receiving_item_row['item_id']);	
			$previous_amount_received = $receiving_item_row['quantity_received'];
			
			if ($suspended != 0)
			{
				$inventory_to_add = $receiving_item_row['quantity_received'];
			}
			else
			{
				$inventory_to_add = $receiving_item_row['quantity_purchased'];
			}
			
			$inventory_to_add = $inventory_to_add*($receiving_item_row['unit_quantity'] !== NULL ? $receiving_item_row['unit_quantity'] : 1);
			
			if ($receiving_item_row['item_variation_id'])
			{
				$cur_item_variation_location_info = $this->Item_variation_location->get_info($receiving_item_row['item_variation_id'],$receiving_location_id);
		
				if ($inventory_to_add !=0)
				{
					$this->Item_variation_location->save_quantity($cur_item_variation_location_info->quantity + $inventory_to_add,$receiving_item_row['item_variation_id'],$receiving_location_id);

					$recv_remarks ='RECV '.$receiving_id;
					$inv_data = array
					(
						'trans_date'=>date('Y-m-d H:i:s'),
						'trans_items'=>$receiving_item_row['item_id'],
						'trans_user'=>$employee_id,
						'trans_comment'=>$recv_remarks,
						'trans_inventory'=>$inventory_to_add,
						'location_id'=>$receiving_location_id,
						'item_variation_id' => $receiving_item_row['item_variation_id'],
						'trans_current_quantity' => $cur_item_variation_location_info->quantity + $inventory_to_add,
						
					);
					$this->Inventory->insert($inv_data);
		
				}
			}
			else
			{
				$cur_item_location_info = $this->Item_location->get_info($receiving_item_row['item_id'],$receiving_location_id);
		
				if ($inventory_to_add !=0)
				{
					$this->Item_location->save_quantity($cur_item_location_info->quantity + $inventory_to_add,$receiving_item_row['item_id'],$receiving_location_id);
					
					$recv_remarks ='RECV '.$receiving_id;
					$inv_data = array
					(
						'trans_date'=>date('Y-m-d H:i:s'),
						'trans_items'=>$receiving_item_row['item_id'],
						'trans_user'=>$employee_id,
						'trans_comment'=>$recv_remarks,
						'trans_inventory'=>$inventory_to_add,
						'location_id'=>$receiving_location_id,
						'trans_current_quantity' => $cur_item_location_info->quantity + $inventory_to_add,

					);
					$this->Inventory->insert($inv_data);
		
				}
			}
	
				if ($suspended == 0 && $receiving_item_row['transfer_to_location_id'])
				{
					$cur_item_location_transfer_info = $this->Item_location->get_info($receiving_item_row['item_id'], $receiving_item_row['transfer_to_location_id']);
					
					$this->Item_location->save_quantity($cur_item_location_transfer_info->quantity - $inventory_to_add,$receiving_item_row['item_id'], $receiving_item_row['transfer_to_location_id']);
			
					$receiving_remarks ='RECV '.$receiving_id;
					$inv_data = array
						(
						'trans_date'=>date('Y-m-d H:i:s'),
						'trans_items'=>$receiving_item_row['item_id'],
						'trans_user'=>$employee_id,
						'trans_comment'=>$receiving_remarks,
						'trans_inventory'=>$inventory_to_add * -1,
						'location_id'=>$receiving_item_row['transfer_to_location_id'],
						'trans_current_quantity' => $cur_item_location_transfer_info->quantity - $inventory_to_add,
						);
						$this->Inventory->insert($inv_data);
				}
				
				unset($inv_data);
		 
		}
		
		
		
		$this->db->where('receiving_id', $receiving_id);
		return $this->db->update('receivings', array('deleted' => 0,'deleted_by'=>NULL,'last_modified' => date('Y-m-d H:i:s')));
	}
	
	function update_store_account($receiving_id,$undelete=0)
	{
		//update if Store account payment exists
		$this->db->from('receivings_payments');
		$store_account_in_all_languages = get_all_language_values_for_key('common_store_account','common');
		$this->db->where_in('payment_type', $store_account_in_all_languages);
		$this->db->where('receiving_id',$receiving_id);
		$to_be_paid_result = $this->db->get();
		
		$supplier_id=$this->get_supplier($receiving_id)->person_id;
		
		
		if($to_be_paid_result->num_rows() >=1)
		{
			foreach($to_be_paid_result->result() as $to_be_paid)
			{
				if($to_be_paid->payment_amount) 
				{
					//update supplier balance
					if($undelete==0)
					{
						$this->db->set('balance','balance-'.$to_be_paid->payment_amount,false);
					}
					else
					{
						$this->db->set('balance','balance+'.$to_be_paid->payment_amount,false);
					}
					$this->db->where('person_id', $supplier_id);
					$this->db->update('suppliers'); 
				
				}
			}			
		}
	}


	function get_receiving_items($receiving_id)
	{
		$this->db->from('receivings_items');
		$this->db->where('receiving_id',$receiving_id);
		$this->db->order_by('line');
		return $this->db->get();
	}

	function get_supplier($receiving_id)
	{
		$this->db->from('receivings');
		$this->db->where('receiving_id',$receiving_id);
		return $this->Supplier->get_info($this->db->get()->row()->supplier_id);
	}

	function calculate_and_update_average_cost_price_for_item($item_id,$variation_id,$current_receivings_items_data,$cart)
	{
		//Dont calculate averages unless we receive quanitity > 0
		if ($current_receivings_items_data['quantity_purchased'] > 0 || ($cart->transfer_location_id && $this->config->item('update_cost_price_on_transfer')))
		{
			if ($cart->transfer_location_id)
			{
				$current_receivings_items_data['quantity_purchased'] = abs($current_receivings_items_data['quantity_purchased']);
			}
			
			$cost_price_avg = false;
			$averaging_method = $this->config->item('averaging_method');
		
			$cur_item_info = $this->Item->get_info($item_id);
			$cur_item_location_info = $this->Item_location->get_info($item_id);
		
			if ($averaging_method == 'moving_average')
			{
				if ($variation_id)
				{
					$this->load->model('Item_variations');
					$cur_item_variation_info = $this->Item_variations->get_info($variation_id);
				}
				if (isset($cur_item_variation_info) && $cur_item_variation_info->cost_price)
				{
					$current_cost_price = $cur_item_variation_info->cost_price;
				}
				else
				{
					$current_cost_price = ($cur_item_location_info && $cur_item_location_info->cost_price) ? $cur_item_location_info->cost_price : $cur_item_info->cost_price;			
				}
				
				if($variation_id)
				{
					$this->load->model('Item_variation_location');
					$current_quantity = $this->Item_variation_location->get_all_location_quantity($variation_id);
				}
				else
				{
					$current_quantity = $this->Item_location->get_all_location_quantity($item_id);
				}
				
				$current_inventory_value = $current_cost_price * $current_quantity;
			
				$received_cost_price = $current_receivings_items_data['item_unit_price_before_tax'] * (1 - ($current_receivings_items_data['discount_percent']/100));
				$received_quantity = $current_receivings_items_data['quantity_purchased'];
				$new_inventory_value = $received_cost_price * $received_quantity;
			
			  if ($current_inventory_value > 0)
				{
					$cost_price_avg = ($current_inventory_value + $new_inventory_value) / ($current_quantity + $received_quantity);
				}
				else
				{
					$cost_price_avg = $received_cost_price;
				}
			
			}
			elseif ($averaging_method == 'historical_average')
			{
				if ($variation_id)
				{
					$this->load->model('Item_variations');
					$cur_item_variation_info = $this->Item_variations->get_info($variation_id);
				}
				
				if (isset($cur_item_variation_info) && $cur_item_variation_info->cost_price)
				{
					$result = $this->db->query("SELECT ROUND((SUM(item_unit_price*quantity_purchased-item_unit_price*quantity_purchased*discount_percent/100)) / SUM(quantity_purchased),10) as cost_price_average 
					FROM ".$this->db->dbprefix('receivings_items'). '
					WHERE quantity_purchased > 0 and item_variation_id='.$this->db->escape($item_id))->result();				
				}
				elseif ($cur_item_location_info && $cur_item_location_info->cost_price)
				{
					$location_id = $this->Employee->get_logged_in_employee_current_location_id();
					$result = $this->db->query("SELECT ROUND((SUM(item_unit_price*quantity_purchased-item_unit_price*quantity_purchased*discount_percent/100)) / SUM(quantity_purchased),10) as cost_price_average 
					FROM ".$this->db->dbprefix('receivings_items').' '.
					'JOIN '.$this->db->dbprefix('receivings').' ON '.$this->db->dbprefix('receivings').'.receiving_id = '.$this->db->dbprefix('receivings_items').'.receiving_id '.
					'WHERE quantity_purchased > 0 and item_id='.$this->db->escape($item_id).' and location_id = '.$this->db->escape($location_id))->result();
				}
				else
				{
					$result = $this->db->query("SELECT ROUND((SUM(item_unit_price*quantity_purchased-item_unit_price*quantity_purchased*discount_percent/100)) / SUM(quantity_purchased),10) as cost_price_average 
					FROM ".$this->db->dbprefix('receivings_items'). '
					WHERE quantity_purchased > 0 and item_id='.$this->db->escape($item_id))->result();				
				}
			
				$cost_price_avg = $result[0]->cost_price_average;
			}
			elseif ($averaging_method == 'dont_average') //Don't average just use current price
			{
				$this->load->model('Item_variations');
				$cur_item_variation_info = $this->Item_variations->get_info($variation_id);
				
				$cost_price_avg = $current_receivings_items_data['item_unit_price_before_tax'];
			}
		
			if ($cost_price_avg !== FALSE)
			{
				$cost_price_avg = to_currency_no_money($cost_price_avg, 10);
				if (isset($cur_item_variation_info) && $cur_item_variation_info->cost_price)
				{
					$this->Item_variations->save(array('cost_price' => $cost_price_avg), $variation_id);
				}
				elseif ($cur_item_location_info && $cur_item_location_info->cost_price)
				{
					$item_location_data = array('cost_price' => $cost_price_avg);
					
					//We are doing a transfer
					if ($cart->transfer_location_id)
					{
						$this->Item_location->save($item_location_data,$item_id,$cart->location_id ? $cart->location_id : ($cart->transfer_from_location_id ? $cart->transfer_from_location_id : $this->Employee->get_logged_in_employee_current_location_id()));						
						$this->Item_location->save($item_location_data,$item_id,$cart->transfer_location_id);						
					}
					else
					{
						$this->Item_location->save($item_location_data,$item_id);						
					}
				}				
				else
				{
					//Update cost price					
					$item_data = array('cost_price'=>$cost_price_avg);
					$this->Item->save($item_data,$item_id);
				}
			}
		}
	}

	function calculate_cost_price_preview($item_id,$variation_id,$price, $additional_quantity, $discount_percent)
	{
		if ($additional_quantity > 0)
		{
			$cost_price_avg = false;
			$averaging_method = $this->config->item('averaging_method');
		
			$cur_item_info = $this->Item->get_info($item_id);
			$cur_item_location_info = $this->Item_location->get_info($item_id);
			
			if ($averaging_method == 'moving_average')
			{
				if ($variation_id)
				{
					$this->load->model('Item_variations');
					$cur_item_variation_info = $this->Item_variations->get_info($variation_id);
				}
				if (isset($cur_item_variation_info) && $cur_item_variation_info->cost_price)
				{
					$current_cost_price = $cur_item_variation_info->cost_price;
				}
				else
				{
					$current_cost_price = ($cur_item_location_info && $cur_item_location_info->cost_price) ? $cur_item_location_info->cost_price : $cur_item_info->cost_price;			
				}
				
				if($variation_id)
				{
					$this->load->model('Item_variation_location');
					$current_quantity = $this->Item_variation_location->get_all_location_quantity($variation_id);
				}
				else
				{
					$current_quantity = $this->Item_location->get_all_location_quantity($item_id);
				}
				
				$current_inventory_value = (float)$current_cost_price * (float)$current_quantity;
			
				$received_cost_price = (float)$price * (1 - ((float)$discount_percent/100));
				$received_quantity = $additional_quantity;
				$new_inventory_value = (float)$received_cost_price * (float)$received_quantity;
			
			  if ($current_inventory_value > 0 )
				{
					$cost_price_avg = ((float)$current_inventory_value + (float)$new_inventory_value) / ((float)$current_quantity + (float)$received_quantity);
				}
				else
				{
					$cost_price_avg = $received_cost_price;
				}
			}
			elseif ($averaging_method == 'historical_average')
			{
				if ($variation_id)
				{
					$this->load->model('Item_variations');
					$cur_item_variation_info = $this->Item_variations->get_info($variation_id);
				}
				
				if (isset($cur_item_variation_info) && $cur_item_variation_info->cost_price)
				{
					$result = $this->db->query("SELECT ROUND((SUM(item_unit_price*quantity_purchased-item_unit_price*quantity_purchased*discount_percent/100)),10) as cost_price_sum,  SUM(quantity_purchased) as cost_price_quantity_sum
					FROM ".$this->db->dbprefix('receivings_items').' '.
					'JOIN '.$this->db->dbprefix('receivings').' ON '.$this->db->dbprefix('receivings').'.receiving_id = '.$this->db->dbprefix('receivings_items').'.receiving_id '.
					'WHERE quantity_purchased > 0 and item_variation_id='.$this->db->escape($variation_id))->result();
				}
				elseif ($cur_item_location_info && $cur_item_location_info->cost_price)
				{
					
					$location_id = $this->Employee->get_logged_in_employee_current_location_id();
					$result = $this->db->query("SELECT ROUND((SUM(item_unit_price*quantity_purchased-item_unit_price*quantity_purchased*discount_percent/100)),10) as cost_price_sum,  SUM(quantity_purchased) as cost_price_quantity_sum
					FROM ".$this->db->dbprefix('receivings_items').' '.
					'JOIN '.$this->db->dbprefix('receivings').' ON '.$this->db->dbprefix('receivings').'.receiving_id = '.$this->db->dbprefix('receivings_items').'.receiving_id '.
					'WHERE quantity_purchased > 0 and item_id='.$this->db->escape($item_id).' and location_id = '.$this->db->escape($location_id))->result();
				}
				else
				{
					$result = $this->db->query("SELECT ROUND((SUM(item_unit_price*quantity_purchased-item_unit_price*quantity_purchased*discount_percent/100)),10) as cost_price_sum,  SUM(quantity_purchased) as cost_price_quantity_sum
					FROM ".$this->db->dbprefix('receivings_items'). '
					WHERE quantity_purchased > 0 and item_id='.$this->db->escape($item_id))->result();				
				}
				
				$cost_price_sum = $result[0]->cost_price_sum + ($price*$additional_quantity-$price*$additional_quantity*$discount_percent/100);
				$cost_price_quantity_sum = $result[0]->cost_price_quantity_sum + $additional_quantity;
				
				$cost_price_avg = $cost_price_sum/$cost_price_quantity_sum;
			}
			elseif ($averaging_method == 'dont_average') //Don't average just use current price
			{
				$cost_price_avg = $price;
			}
		
			return to_currency($cost_price_avg,10);
		}
	
		return lang('common_na');
	}
	
	function get_all_suspended($suspended = 1,$location_column = 'receivings.location_id')
	{		
		$location_id = $this->Employee->get_logged_in_employee_current_location_id();		
		
		$this->db->select('suppliers.*,people.*,receivings.*');
		$this->db->from('receivings');
		$this->db->join('suppliers', 'receivings.supplier_id = suppliers.person_id', 'left');
		$this->db->join('people', 'suppliers.person_id = people.person_id', 'left');
		$this->db->where('receivings.deleted', 0);
		
		if (is_array($suspended))
		{
			$this->db->where_in('suspended', $suspended);
		}
		else
		{
			$this->db->where('suspended', $suspended);			
		}
		$this->db->where($location_column, $location_id);			
		
		$this->db->order_by('receiving_id');
		$receivings = $this->db->get()->result_array();
				
		$receiving_ids = array();
		
		foreach($receivings as $receiving)
		{
			$receiving_ids[] = $receiving['receiving_id'];
		}
		
		$all_payments_for_receivings = $this->_get_all_receiving_payments($receiving_ids);	
				
		for($k=0;$k<count($receivings);$k++)
		{
			$item_names = array();
			$this->db->select('name');
			$this->db->from('items');
			$this->db->join('receivings_items', 'receivings_items.item_id = items.item_id');
			$this->db->where('receiving_id', $receivings[$k]['receiving_id']);
		
			foreach($this->db->get()->result_array() as $row)
			{
				$item_names[] = $row['name'];
			}
						
			$receivings[$k]['items'] = implode(', ', $item_names);
			
			
			$receivings[$k]['last_payment_date'] = lang('common_none');			
			$receiving_total = $this->get_receiving_total($receivings[$k]['receiving_id']);		
			$amount_paid = 0;
			$receiving_id = $receivings[$k]['receiving_id'];
						
			$payment_data = array();
			
			if (isset($all_payments_for_receivings[$receiving_id]))
			{
				$total_receiving_balance = $receiving_total;		
				
				foreach($all_payments_for_receivings[$receiving_id] as $payment_row)
				{
					//Postive receiving total, positive payment
					if ($receiving_total >= 0 && $payment_row['payment_amount'] >=0)
					{
						$payment_amount = $payment_row['payment_amount'] <= $total_receiving_balance ? $payment_row['payment_amount'] : $total_receiving_balance;
					}//Negative receiving total negative payment
					elseif ($receiving_total < 0 && $payment_row['payment_amount']  < 0)
					{
						$payment_amount = $payment_row['payment_amount'] >= $total_receiving_balance ? $payment_row['payment_amount'] : $total_receiving_balance;
					}//Positive Receiving total negative payment
					elseif($receiving_total >= 0 && $payment_row['payment_amount']  < 0)
					{
						$payment_amount = $total_receiving_balance != 0 ? $payment_row['payment_amount'] : 0;
					}//Negtive receiving total postive payment
					elseif($receiving_total < 0 && $payment_row['payment_amount']  >= 0)
					{
						$payment_amount = $total_receiving_balance != 0 ? $payment_row['payment_amount'] : 0;
					}				
			
					$total_receiving_balance-=$payment_amount;	
					$amount_paid+=	$payment_amount;	
					
					
					$receivings[$k]['last_payment_date'] = date(get_date_format().' '.get_time_format(), strtotime($payment_row['payment_date']));		
				}
			}
			
			$receivings[$k]['receiving_total'] = $receiving_total;
			$receivings[$k]['amount_due'] = $receiving_total - $amount_paid;
			$receivings[$k]['amount_paid'] = $amount_paid;
		}
		
		return $receivings;
	}
	
	function get_suspended_receivings_displayable_columns()
	{
		$return  = array(
			'receiving_id' => array('sort_column' => 'receiving_id', 'label' => lang('receivings_id')),
			'receiving_time' => array('sort_column' => 'receiving_time', 'label' => lang('common_date')),
			'supplier_id' => array('sort_column' => 'supplier_id', 'label' => lang('common_supplier')),
			'items' => array('sort_column' => 'items', 'label' => lang('reports_items')),
			'receiving_total' => array('html' => TRUE,'sort_column' => 'receiving_total', 'label' => lang('common_total'), 'format_function' => 'to_currency'),
			'amount_paid' => array('html' => TRUE,'sort_column' => 'amount_paid', 'label' => lang('common_amount_paid'), 'format_function' => 'to_currency'),
			'last_payment_date' => array('sort_column' => 'last_payment_date', 'label' => lang('common_last_payment_date')),
			'amount_due' => array('html' => TRUE,'sort_column' => 'amount_due', 'label' => lang('common_amount_due'), 'format_function' => 'to_currency'),
			'comment' => array('sort_column' => 'comment', 'label' => lang('common_comments'))
		);	
			
		
	  for($k=1;$k<=NUMBER_OF_PEOPLE_CUSTOM_FIELDS;$k++) 
	  {
	 	 $sale_custom_field = $this->get_custom_field($k);
		 if ($sale_custom_field)
		 {
		 	
			$field['sort_column'] = $sale_custom_field;
			$field['label']= $this->get_custom_field($k);
			 
			if ($this->get_custom_field($k,'type') == 'checkbox')
			{
				$format_function = 'boolean_as_string';
			}
			elseif($this->get_custom_field($k,'type') == 'date')
			{
				$format_function = 'date_as_display_date';				
			}
			elseif($this->get_custom_field($k,'type') == 'email')
			{
				$format_function = 'strsame';					
			}
			elseif($this->get_custom_field($k,'type') == 'url')
			{
				$format_function = 'strsame';					
			}
			elseif($this->get_custom_field($k,'type') == 'phone')
			{
				$format_function = 'strsame';					
			}
			elseif($this->get_custom_field($k,'type') == 'image')
			{
				$this->load->helper('url');
				$format_function = 'file_id_to_image_thumb_right';					
			}
			elseif($this->get_custom_field($k,'type') == 'file')
			{
				$this->load->helper('url');
				$format_function = 'file_id_to_download_link';					
			}
			else
			{
				$format_function = 'strsame';
			}
			
			$field['format_function'] = $format_function;
			
			$return["custom_field_${k}_value"] = $field;
		 }
		
	 	}
		
		
		return $return;
			
	}
	
	function get_suspended_receivings_default_columns()
	{
		return array('receiving_id','receiving_time','supplier_id','items','receiving_total','amount_paid','last_payment_date','amount_due','comment');
	}
	
	function get_suspended_receivings_for_item($item_id)
	{
		$this->db->from('receivings');
		$this->db->join('receivings_items', 'receivings.receiving_id = receivings_items.receiving_id');
		$this->db->where('receivings.suspended', '1');
		$this->db->where('receivings.deleted', '0');
		$this->db->where('receivings_items.item_id', $item_id);
		
		return $this->db->get()->result_array();
	}
	
	function get_receiving_items_taxes($receiving_id, $line = FALSE)
	{
		$item_where = '';
		
		if ($line!==FALSE)
		{
			$item_where = 'and '.$this->db->dbprefix('receivings_items').'.line = '.$line;
		}

		$query = $this->db->query('SELECT name, line, percent, cumulative, item_unit_price as price, quantity_purchased as quantity, discount_percent as discount '.
		'FROM '. $this->db->dbprefix('receivings_items_taxes'). ' JOIN '.
		$this->db->dbprefix('receivings_items'). ' USING (receiving_id, item_id, line) '.
		'WHERE '.$this->db->dbprefix('receivings_items_taxes').".receiving_id = $receiving_id".' '.$item_where.' '.
		'ORDER BY '.$this->db->dbprefix('receivings_items').'.line,'.$this->db->dbprefix('receivings_items').'.item_id,cumulative,name,percent');
		return $query->result_array();
	}
	
	function get_deleted_taxes($receiving_id)
	{
		$this->db->from('receivings');
		$this->db->where('receiving_id',$receiving_id);
		
		$deleted_taxes = $this->db->get()->row()->deleted_taxes;
		return $deleted_taxes ? unserialize($deleted_taxes) : array();
	}
	
	function get_override_taxes($receiving_id)
	{
		$this->db->from('receivings');
		$this->db->where('receiving_id',$receiving_id);
		$override_taxes = $this->db->get()->row()->override_taxes;
		return $override_taxes ? unserialize($override_taxes) : array();
	}
	
	function get_payment_options_with_language_keys()
	{		
		
		$payment_options=array(
		lang('common_cash') => 'common_cash',
		lang('common_check') => 'common_check',
		lang('common_giftcard') => 'common_giftcard',
		lang('common_debit') => 'common_debit',
		lang('common_credit') => 'common_credit',
		lang('common_store_account') => 'common_store_account',
		);
		
		foreach($this->Appconfig->get_additional_payment_types() as $additional_payment_type)
		{
			$payment_options[$additional_payment_type] = $additional_payment_type;
		}
		
		return $payment_options;
	}
	
	
	function get_payment_options($cart)
	{
		$payment_options=array(
			lang('common_cash') => lang('common_cash'),
			lang('common_check') => lang('common_check'),
			lang('common_debit') => lang('common_debit'),
			lang('common_credit') => lang('common_credit')
		);
		
    $CI =& get_instance();
		if($this->config->item('suppliers_store_accounts') && $cart->get_mode() != 'store_account_payment') 
		{
			$payment_options=array_merge($payment_options,	array(lang('common_store_account') => lang('common_store_account')		
			));
		}
		
		
		foreach($this->Appconfig->get_additional_payment_types() as $additional_payment_type)
		{
			$payment_options[$additional_payment_type] = $additional_payment_type;
		}
		
		$deleted_payment_types = $this->config->item('deleted_payment_types');
		$deleted_payment_types = explode(',',$deleted_payment_types);
		
		foreach($deleted_payment_types as $deleted_payment_type)
		{
			foreach($payment_options as $payment_option)
			{
				if ($payment_option == $deleted_payment_type)
				{
					unset($payment_options[$payment_option]);
				}
			}
		}
		
		return $payment_options;
	
	}
	
	function get_receiving_total($recv_id,$subtotal = false)
	{		
		$row = $this->get_info($recv_id)->row_array();
		if (isset($row['total']) && !$subtotal)
		{
			return $row['total'];
		}
		elseif(isset($row['subtotal']) && $subtotal)
		{
			return $row['subtotal'];
		}
		
		return 0;
	}
	
	function get_recv_payments($recv_id)
	{
		$this->db->from('receivings_payments');
		$this->db->where('receiving_id',$recv_id);
		return $this->db->get();
	}
	
	function get_unpaid_store_account_recv_ids($supplier_id,$limit = 30)
	{
		
		$store_account_in_all_languages = get_all_language_values_for_key('common_store_account','common');
		
		$this->db->select('supplier_store_accounts.receiving_id');
		$this->db->from('supplier_store_accounts');
		$this->db->join('receivings_payments', 'supplier_store_accounts.receiving_id = receivings_payments.receiving_id');
		$this->db->join('receivings', 'supplier_store_accounts.receiving_id = receivings.receiving_id');
		$this->db->where($this->db->dbprefix('supplier_store_accounts').'.supplier_id',$supplier_id);
		$this->db->where($this->db->dbprefix('supplier_store_accounts').'.receiving_id IS NOT NULL');
		$this->db->where($this->db->dbprefix('supplier_store_accounts').'.receiving_id NOT IN (SELECT receiving_id FROM '.$this->db->dbprefix('supplier_store_accounts_paid_receivings').' WHERE partial_payment_amount=0 and receiving_id IS NOT NULL)');
		$this->db->where_in('receivings_payments.payment_type', $store_account_in_all_languages);
		$this->db->where('receivings.deleted',0);
		$this->db->where('receivings.supplier_id',$supplier_id);
		$this->db->order_by('date');
		
		$receiving_ids = array();
		
		foreach($this->db->get()->result_array() as $row)
		{
			$receiving_ids[] = $row['receiving_id'];
		}
		
		return $receiving_ids;
	}
	
	function get_unpaid_store_account_recvs($receiving_ids)
	{
		$store_account_in_all_languages = get_all_language_values_for_key('common_store_account','common');
		
		$this->db->select('receivings.receiving_id, receiving_time, SUM(payment_amount) - COALESCE(partial_payment_amount,0) as payment_amount,receivings.comment', false);
		$this->db->from('receivings');
		
		$this->db->where('receivings.deleted',0);
		$this->db->join('receivings_payments', 'receivings.receiving_id = receivings_payments.receiving_id');
		$this->db->join('supplier_store_accounts_paid_receivings', 'supplier_store_accounts_paid_receivings.receiving_id = receivings_payments.receiving_id','left');
		
		$this->db->where_in('receivings_payments.payment_type', $store_account_in_all_languages);
		
		if (!empty($receiving_ids))
		{
			$this->db->where_in('receivings.receiving_id', $receiving_ids);
		}
		else
		{
			$this->db->where_in('receivings.receiving_id', array(0));				
		}
		$this->db->order_by('receiving_time');
		$this->db->group_by('receiving_id');
		return $this->db->get()->result_array();
	}
	
	function mark_all_unpaid_receivings_paid($supplier_id = '')
	{
		$this->db->select('supplier_store_accounts.receiving_id');
		$this->db->from('supplier_store_accounts');
		if ($supplier_id)
		{
			$this->db->where('supplier_id',$supplier_id);
		}
		
		$this->db->where($this->db->dbprefix('supplier_store_accounts').'.receiving_id NOT IN (SELECT '.$this->db->dbprefix('supplier_store_accounts_paid_receivings').'.receiving_id FROM '.$this->db->dbprefix('supplier_store_accounts_paid_receivings').' WHERE partial_payment_amount=0 and '.$this->db->dbprefix('supplier_store_accounts_paid_receivings').'.receiving_id is NOT NULL)');
		$this->db->order_by('date');
	
		foreach($this->db->get()->result_array() as $row)
		{
			if ($this->is_store_accounts_paid_receiving_already_exist($row['receiving_id']))
			{
				$this->db->where('receiving_id',$row['receiving_id']);
				$this->db->update('supplier_store_accounts_paid_receivings',array('partial_payment_amount' => 0));
			}
			else
			{
				$this->db->insert('supplier_store_accounts_paid_receivings',array('partial_payment_amount' => 0,'receiving_id' => $row['receiving_id'],'store_account_payment_receiving_id' => NULL));
			}
		}
	}
	
	
	function get_payment_data($payments_by_receiving,$receivings_totals)
	{
		static $foreign_language_to_cur_language = array();
		
		if (!$foreign_language_to_cur_language)
		{
			$this->load->helper('directory');
			$language_folder = directory_map(APPPATH.'language',1);
		
			$languages = array();
				
			foreach($language_folder as $language_folder)
			{
				$languages[] = substr($language_folder,0,strlen($language_folder)-1);
			}	
			
			$cur_lang = array();
			foreach($this->get_payment_options_with_language_keys() as $cur_lang_value => $lang_key)
			{
				$cur_lang[$lang_key] = $cur_lang_value;
			}
		
		
			foreach($languages as $language)
			{
				$this->lang->load('common', $language);
				
				foreach($this->get_payment_options_with_language_keys() as $cur_lang_value => $lang_key)
				{
					if (strpos($lang_key,'common') !== FALSE)
					{
						$foreign_language_to_cur_language[lang($lang_key)] = $cur_lang[$lang_key];
					}		
					else
					{
						$foreign_language_to_cur_language[$cur_lang_value] = $cur_lang_value;						
					}			
				}
			}
				
			//Switch back
			$this->lang->switch_to($this->config->item('language'));
		}
		$payment_data = array();
		
		$receiving_ids = array_keys($payments_by_receiving);
		$all_payments_for_receivings = $this->_get_all_receiving_payments($receiving_ids);
		
		foreach($all_payments_for_receivings as $receiving_id => $payment_rows)
		{
			if (isset($receivings_totals[$receiving_id]))
			{
				$total_receiving_balance = $receivings_totals[$receiving_id];		
				foreach($payment_rows as $payment_row)
				{
					//Postive receiving total, positive payment
					if ($receivings_totals[$receiving_id] >= 0 && $payment_row['payment_amount'] >=0)
					{
						$payment_amount = $payment_row['payment_amount'] <= $total_receiving_balance ? $payment_row['payment_amount'] : $total_receiving_balance;
					}//Negative receiving total negative payment
					elseif ($receivings_totals[$receiving_id] < 0 && $payment_row['payment_amount']  < 0)
					{
						$payment_amount = $payment_row['payment_amount'] >= $total_receiving_balance ? $payment_row['payment_amount'] : $total_receiving_balance;
					}//Positive Sale total negative payment
					elseif($receivings_totals[$receiving_id] >= 0 && $payment_row['payment_amount']  < 0)
					{
						$payment_amount = $total_receiving_balance != 0 ? $payment_row['payment_amount'] : 0;
					}//Negtive receiving total postive payment
					elseif($receivings_totals[$receiving_id] < 0 && $payment_row['payment_amount']  >= 0)
					{
						$payment_amount = $total_receiving_balance != 0 ? $payment_row['payment_amount'] : 0;
					}
					
					if (!isset($foreign_language_to_cur_language[$payment_row['payment_type']]) || !isset($payment_data[$foreign_language_to_cur_language[$payment_row['payment_type']]]))
					{
						$payment_key = NULL;
						
						//Gift card
						if (strpos($payment_row['payment_type'],':') !== FALSE && !isset($foreign_language_to_cur_language[$payment_row['payment_type']]))
						{
			   	         	list($giftcard_translation, $giftcard_number) = explode(":",$payment_row['payment_type']);
							$foreign_language_to_cur_language[$payment_row['payment_type']] = $foreign_language_to_cur_language[$giftcard_translation].':'.$giftcard_number;
							
							if (!isset($payment_data[$foreign_language_to_cur_language[$payment_row['payment_type']]]))
							{
								$payment_data[$foreign_language_to_cur_language[$payment_row['payment_type']]] = array('payment_type' => $foreign_language_to_cur_language[$payment_row['payment_type']], 'payment_amount' => 0 );							
							}
							
							$payment_key = $foreign_language_to_cur_language[$payment_row['payment_type']];
						}
						elseif(isset($foreign_language_to_cur_language[$payment_row['payment_type']]))
						{
							if (!isset($payment_data[$foreign_language_to_cur_language[$payment_row['payment_type']]]))
							{
								$payment_data[$foreign_language_to_cur_language[$payment_row['payment_type']]] = array('payment_type' => $foreign_language_to_cur_language[$payment_row['payment_type']], 'payment_amount' => 0 );
							}
							$payment_key = $foreign_language_to_cur_language[$payment_row['payment_type']];
						}
						else
						{
							if (!isset($payment_data[$payment_row['payment_type']]))
							{
								$payment_data[$payment_row['payment_type']] = array('payment_type' => $payment_row['payment_type'], 'payment_amount' => 0 );
							}
							$payment_key = $payment_row['payment_type']; 
						}
					}
					else
					{
						$payment_key = $foreign_language_to_cur_language[$payment_row['payment_type']];
					}
					
					$exists = $this->_does_payment_exist_in_array($payment_row['payment_id'], $payments_by_receiving[$receiving_id]);
					
					
					if (($total_receiving_balance != 0 || 
						($receivings_totals[$receiving_id] >= 0 && $payment_row['payment_amount']  < 0) ||
						($receivings_totals[$receiving_id] < 0 && $payment_row['payment_amount']  >= 0)) && $exists)
					{
						$payment_data[$payment_key]['payment_amount'] += $payment_amount;
					}

					$total_receiving_balance-=$payment_amount;					
				}
			}
		}
		
		return $payment_data;
	}
	
	function _does_payment_exist_in_array($payment_id, $payments)
	{
		foreach($payments as $payment)
		{
			if($payment['payment_id'] == $payment_id)
			{
				return TRUE;
			}
		}
		
		return FALSE;
	}
		
	function _get_all_receiving_payments($receiving_ids)
	{
		$return = array();
		
		if (count($receiving_ids) > 0)
		{
			$this->db->select('receivings_payments.*, receivings.receiving_time');
      	$this->db->from('receivings_payments');
      	$this->db->join('receivings', 'receivings.receiving_id=receivings_payments.receiving_id');
			
			$this->db->group_start();
			$receiving_ids_chunk = array_chunk($receiving_ids,25);
			foreach($receiving_ids_chunk as $receiving_ids)
			{
				$this->db->or_where_in('receivings_payments.receiving_id', $receiving_ids);
			}
			$this->db->group_end();
			$this->db->order_by('payment_date');
			
			$result = $this->db->get()->result_array();
			
			foreach($result as $row)
			{
				$return[$row['receiving_id']][] = $row;
			}
		}
		return $return;
	}
		
	function get_payment_data_grouped_by_receiving($payments_by_receiving,$receivings_totals)
	{
		static $foreign_language_to_cur_language = array();
		
		if (!$foreign_language_to_cur_language)
		{
		$this->load->helper('directory');
			$language_folder = directory_map(APPPATH.'language',1);
		
			$languages = array();
				
			foreach($language_folder as $language_folder)
			{
				$languages[] = substr($language_folder,0,strlen($language_folder)-1);
			}
		
			$cur_lang = array();
			foreach($this->get_payment_options_with_language_keys() as $cur_lang_value => $lang_key)
			{
				$cur_lang[$lang_key] = $cur_lang_value;
			}
		
		
			foreach($languages as $language)
			{
				$this->lang->load('common', $language);
			
				foreach($this->get_payment_options_with_language_keys() as $cur_lang_value => $lang_key)
				{
					if (strpos($lang_key,'common') !== FALSE)
					{
						$foreign_language_to_cur_language[lang($lang_key)] = $cur_lang[$lang_key];
					}		
					else
					{
						$foreign_language_to_cur_language[$cur_lang_value] = $cur_lang_value;						
					}			
				}
			}
				
			//Switch back
			$this->lang->switch_to($this->config->item('language'));
		}
		
		$payment_data = array();
		
		$receiving_ids = array_keys($payments_by_receiving);
		$all_payments_for_receivings = $this->_get_all_receiving_payments($receiving_ids);
		
		foreach($all_payments_for_receivings as $receiving_id => $payment_rows)
		{
			if (isset($receivings_totals[$receiving_id]))
			{
				$total_receiving_balance = $receivings_totals[$receiving_id];
			
				foreach($payment_rows as $payment_row)
				{
					//Postive receiving total, positive payment
					if ($receivings_totals[$receiving_id] >= 0 && $payment_row['payment_amount'] >=0)
					{
						$payment_amount = $payment_row['payment_amount'] <= $total_receiving_balance ? $payment_row['payment_amount'] : $total_receiving_balance;
					}//Negative receiving total negative payment
					elseif ($receivings_totals[$receiving_id] < 0 && $payment_row['payment_amount']  < 0)
					{
						$payment_amount = $payment_row['payment_amount'] >= $total_receiving_balance ? $payment_row['payment_amount'] : $total_receiving_balance;
					}//Positive Sale total negative payment
					elseif($receivings_totals[$receiving_id] >= 0 && $payment_row['payment_amount']  < 0)
					{
						$payment_amount = $total_receiving_balance != 0 ? $payment_row['payment_amount'] : 0;
					}//Negtive receiving total postive payment
					elseif($receivings_totals[$receiving_id] < 0 && $payment_row['payment_amount']  >= 0)
					{
						$payment_amount = $total_receiving_balance != 0 ? $payment_row['payment_amount'] : 0;
					}			
			
			
					if (!isset($foreign_language_to_cur_language[$payment_row['payment_type']]) || !isset($payment_data[$receiving_id][$foreign_language_to_cur_language[$payment_row['payment_type']]]))
					{
						$payment_key = NULL;
						
						//Gift card
						if (strpos($payment_row['payment_type'],':') !== FALSE && !isset($foreign_language_to_cur_language[$payment_row['payment_type']]))
						{
			   	         	list($giftcard_translation, $giftcard_number) = explode(":",$payment_row['payment_type']);
							$foreign_language_to_cur_language[$payment_row['payment_type']] = $foreign_language_to_cur_language[$giftcard_translation].':'.$giftcard_number;							
							
							if (!isset($payment_data[$receiving_id][$foreign_language_to_cur_language[$payment_row['payment_type']]]))
							{
								$payment_data[$receiving_id][$foreign_language_to_cur_language[$payment_row['payment_type']]] = array('receiving_id' => $receiving_id,'payment_type' => $foreign_language_to_cur_language[$payment_row['payment_type']], 'payment_amount' => 0,'payment_date' => $payment_row['payment_date'], 'receiving_time' => $payment_row['receiving_time'] );
							}
							
							$payment_key = $foreign_language_to_cur_language[$payment_row['payment_type']];
							
						}
						elseif(isset($foreign_language_to_cur_language[$payment_row['payment_type']]))
						{
							if (!isset($payment_data[$receiving_id][$foreign_language_to_cur_language[$payment_row['payment_type']]]))
							{
								$payment_data[$receiving_id][$foreign_language_to_cur_language[$payment_row['payment_type']]] = array('receiving_id' => $receiving_id,'payment_type' => $foreign_language_to_cur_language[$payment_row['payment_type']], 'payment_amount' => 0,'payment_date' => $payment_row['payment_date'], 'receiving_time' => $payment_row['receiving_time'] );
							}
							$payment_key = $foreign_language_to_cur_language[$payment_row['payment_type']];
							
						}
						else
						{
							if (!isset($payment_data[$receiving_id][$payment_row['payment_type']]))
							{
								$payment_data[$receiving_id][$payment_row['payment_type']] = array('receiving_id' => $receiving_id,'payment_type' => $payment_row['payment_type'], 'payment_amount' => 0,'payment_date' => $payment_row['payment_date'], 'receiving_time' => $payment_row['receiving_time'] );
							}
							
							$payment_key = $payment_row['payment_type']; 
							
						}
					}
					else
					{
						$payment_key = $foreign_language_to_cur_language[$payment_row['payment_type']];
					}
					
					
					$exists = $this->_does_payment_exist_in_array($payment_row['payment_id'], $payments_by_receiving[$receiving_id]);
				
					if (($total_receiving_balance != 0 || 
						($receivings_totals[$receiving_id] >= 0 && $payment_row['payment_amount']  < 0) ||
						($receivings_totals[$receiving_id] < 0 && $payment_row['payment_amount']  >= 0)) && $exists)
					{
						$payment_data[$receiving_id][$payment_key]['payment_amount'] += $payment_amount;
					}
				
					$total_receiving_balance-=$payment_amount;
				}
			}
		}
		
		return $payment_data;
	}
	
	function get_store_accounts_paid_receivings($store_account_payment_receiving_id)
	{
		$this->db->from('supplier_store_accounts_paid_receivings');
		$this->db->where('store_account_payment_receiving_id',$store_account_payment_receiving_id);
		
		$return = array();
		
		foreach($this->db->get()->result_array() as $row)
		{
			$return[] = array('receiving_id' =>$row['receiving_id'],'partial_payment_amount' => $row['partial_payment_amount']);
		}
		
		return $return;
	}
	
	public function get_store_account_info($receiving_id)
	{
      $this->db->from('supplier_store_accounts');
      $this->db->where('receiving_id',$receiving_id);
      return $this->db->get();
	}
	
	function get_custom_field($number,$key="name")
	{
		static $config_data;
		
		if (!$config_data)
		{
			$config_data = unserialize($this->config->item('receiving_custom_field_prefs'));
		}
		
		return isset($config_data["custom_field_${number}_${key}"]) && $config_data["custom_field_${number}_${key}"] ? $config_data["custom_field_${number}_${key}"] : FALSE;
	}

	
	function get_receiving_ids_for_range($start_date, $end_date)
	{
		$this->db->select('receiving_id');
		$this->db->from('receivings');
		$this->db->where('receiving_time BETWEEN '.$this->db->escape($start_date).' and '.$this->db->escape($end_date));
		$this->db->where('deleted',0);
		
		$receiving_ids = array();
		foreach($this->db->get()->result_array() as $row)
		{
			$receiving_ids[] = $row['receiving_id'];
		}
		
		return $receiving_ids;
	}
	
	function get_last_receiving_id($location_id = FALSE)
	{
		if ($location_id === FALSE)
		{
			$location_id = $this->Employee->get_logged_in_employee_current_location_id();
		}
		
		$this->db->select('receiving_id');
		$this->db->from('receivings');
		$this->db->where('deleted', 0);
		$this->db->where('location_id', $location_id);
		$this->db->order_by('receiving_id DESC');
		$this->db->limit(1);
		$query = $this->db->get();
		
		if ($row = $query->row_array())
		{
			return $row['receiving_id'];
		}
		
		return FALSE;
		
	}

	function receiving_item_line_update($receiving_id,$item_id, $item_class,$receipt_line_sort_order){
		if($item_class=="item"){
			$this->db->where('receiving_id', $receiving_id);
			$this->db->where('item_id', $item_id);
			$this->db->update('receivings_items',array('receipt_line_sort_order'=>$receipt_line_sort_order));
		}else if($item_class="item_kit"){
		}
		return true;
	}

	function get_exchange_details($receiving_id)
	{
    $this->db->from('receivings');
    $this->db->where('receiving_id',$receiving_id);
    $row = $this->db->get()->row();
	
		return $row->exchange_rate.'|'.$row->exchange_name.'|'.$row->exchange_currency_symbol.'|'.$row->exchange_currency_symbol_location.'|'.$row->exchange_number_of_decimals.'|'.$row->exchange_thousands_separator.'|'.$row->exchange_decimal_point;
		
	}

	function get_receiving_payments($receiving_id)
	{
		$this->db->from('receivings_payments');
		$this->db->where('receiving_id',$receiving_id);
		return $this->db->get();
	}
}

?>
