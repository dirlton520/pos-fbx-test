<?php
trait receivingTrait
{
	private function recv_id_to_array($receiving_id)
	{
		$receiving_info = $this->Receiving->get_info($receiving_id)->row_array();
		date_default_timezone_set($this->Location->get_info_for_key('timezone',$receiving_info['location_id']));
		
		$response = array();
		$receipt_cart = PHPPOSCartRecv::get_instance_from_recv_id($receiving_id);
		$response['receiving_id'] = $receiving_id;
		$response['receiving_time'] = date(get_date_format().' '.get_time_format(), strtotime($receiving_info['receiving_time']));
		$response['location_id'] = $receiving_info['location_id'];
		$response['employee_id'] = $receiving_info['employee_id'];
		$response['deleted'] = (boolean)$receiving_info['deleted'];
		$response['comment'] = $receiving_info['comment'];
		$response['mode'] = $receipt_cart->get_mode();
		$response['is_po'] = (boolean)$receipt_cart->is_po;
		
		$this->load->model('Supplier');
		$response['supplier_id'] = $receipt_cart->supplier_id ? $receipt_cart->supplier_id : NULL;
		$supplier = $this->Supplier->get_info($receipt_cart->supplier_id ? $receipt_cart->supplier_id : NULL);
		$response['supplier_first_name'] = $supplier->first_name;
		$response['supplier_last_name'] = $supplier->last_name;
		$response['supplier_email'] = $supplier->email;
		$response['supplier_phone_number'] = $supplier->phone_number;
		$response['supplier_address_1'] = $supplier->address_1;
		$response['supplier_address_2'] = $supplier->address_2;
		$response['supplier_city'] = $supplier->city;
		$response['supplier_state'] = $supplier->state;
		$response['supplier_zip'] = $supplier->zip;
		$response['supplier_country'] = $supplier->country;
		$response['supplier_comments'] = $supplier->comments;
		$response['supplier_company_name'] = $supplier->company_name;
		$response['supplier_account_number'] = $supplier->account_number;
		$response['supplier_override_default_tax'] = (boolean)$supplier->override_default_tax;
		$response['supplier_tax_class_id'] = (int)$supplier->tax_class_id;
		$response['supplier_balance'] = (float)$supplier->balance;
		$response['supplier_image_url'] = $supplier->image_id ? secure_app_file_url($supplier->image_id) : '';
		$response['supplier_created_at'] = $supplier->create_date ? date(get_date_format().' '.get_time_format(), strtotime($supplier->create_date)) : NULL;
		
		
		$response['excluded_taxes'] = $receipt_cart->get_excluded_taxes();
		$response['paid_store_account_ids'] = array();
		$response['suspended'] = $receipt_cart->suspended;
		$response['transfer_location_id'] = $receipt_cart->transfer_location_id;
		$response['subtotal'] = $receipt_cart->get_subtotal();
		$response['tax'] = $receipt_cart->get_tax_total_amount();
		$response['total'] = $receipt_cart->get_total();
		$response['shipping_cost'] = $receipt_cart->shipping_cost;
		
		$payments = $receipt_cart->get_payments();
		
		for($k=1;$k<=NUMBER_OF_PEOPLE_CUSTOM_FIELDS;$k++)
		{
			if($this->Receiving->get_custom_field($k) !== false)
			{
				$field = array();
				$field['label']= $this->Receiving->get_custom_field($k);
				if($this->Receiving->get_custom_field($k,'type') == 'date')
				{
					$field['value'] = date_as_display_date($receipt_cart->{"custom_field_{$k}_value"});
				}
				else
				{
					$field['value'] = $receipt_cart->{"custom_field_{$k}_value"};
				}
				
				$response['custom_fields'][$field['label']] = $field['value'];
			}

		}
		
		for($k=0;$k<count($payments);$k++)
		{
			$payments[$k]->payment_amount = to_currency_no_money($payments[$k]->payment_amount );
		}
		
		$response['payments'] = $payments;
		foreach(array_keys($receipt_cart->get_paid_store_account_ids()) as $paid_store_account_id)
		{
			$response['paid_store_account_ids'][] = $paid_store_account_id;
		}
		$response['cart_items'] = array();
		
		foreach($receipt_cart->get_items() as $cart_item)
		{
			$cart_item_row = array();
			$cart_item_row['item_id'] = $cart_item->item_id;
			$cart_item_row['quantity'] = to_quantity($cart_item->quantity);
			$cart_item_row['quantity_received'] = to_quantity($cart_item->quantity_received);
			$cart_item_row['unit_price'] = to_currency_no_money($cart_item->unit_price);
			$cart_item_row['cost_price'] = to_currency_no_money($cart_item->cost_price);
			$cart_item_row['discount'] = to_quantity($cart_item->discount);
			$cart_item_row['description'] = $cart_item->description;
			$cart_item_row['name'] = $cart_item->name;
			$cart_item_row['item_number'] = $cart_item->item_number;
			$cart_item_row['product_id'] = $cart_item->product_id;
			$cart_item_row['serialnumber'] = $cart_item->serialnumber;
			$cart_item_row['serial_range'] = $cart_item->serial_range;
			$cart_item_row['size'] = $cart_item->size;
			$response['cart_items'][] = $cart_item_row;
		}
		
		return $response;
	}
}
