<?php
trait saleTrait
{
	private function sale_id_to_array($sale_id)
	{
		$sale_info = $this->Sale->get_info($sale_id)->row_array();
		date_default_timezone_set($this->Location->get_info_for_key('timezone',$sale_info['location_id']));
		
		$response = array();
		$receipt_cart = PHPPOSCartSale::get_instance_from_sale_id($sale_id);
		$receipt_cart->clear_exchange_details();
		$response['sale_id'] = $sale_id;
		$response['sale_time'] = date(get_date_format().' '.get_time_format(), strtotime($sale_info['sale_time']));
		$response['location_id'] = $sale_info['location_id'];
		$response['rule_id'] = $sale_info['rule_id'];
		$response['points_used'] = to_quantity($sale_info['points_used']);
		$response['points_gained'] = to_quantity($sale_info['points_gained']);
		$response['employee_id'] = $sale_info['employee_id'];
		$response['deleted'] = (boolean)$sale_info['deleted'];
		$response['comment'] = $sale_info['comment'];
		$response['store_account_payment'] = (boolean)$sale_info['store_account_payment'];
		$response['register_id'] = $sale_info['register_id'];
		$response['mode'] = $receipt_cart->get_mode();
		
		$this->load->model('Customer');
		$response['customer_id'] = $receipt_cart->customer_id ? $receipt_cart->customer_id : NULL;
		$customer = $this->Customer->get_info($receipt_cart->customer_id ? $receipt_cart->customer_id : NULL);
		$response['customer_first_name'] = $customer->first_name;
		$response['customer_last_name'] = $customer->last_name;
		$response['customer_email'] = $customer->email;
		$response['customer_phone_number'] = $customer->phone_number;
		$response['customer_address_1'] = $customer->address_1;
		$response['customer_address_2'] = $customer->address_2;
		$response['customer_city'] = $customer->city;
		$response['customer_state'] = $customer->state;
		$response['customer_zip'] = $customer->zip;
		$response['customer_country'] = $customer->country;
		$response['customer_comments'] = $customer->comments;
		$response['customer_internal_notes'] = $customer->internal_notes;
		$response['customer_company_name'] = $customer->company_name;
		$response['customer_tier_id'] = (int)$customer->tier_id;
		$response['customer_account_number'] = $customer->account_number;
		$response['customer_taxable'] = (boolean)$customer->taxable;
		$response['customer_tax_certificate'] = $customer->tax_certificate;

		$response['customer_override_default_tax'] = (boolean)$customer->override_default_tax;
		$response['customer_tax_class_id'] = (int)$customer->tax_class_id;
		$response['customer_balance'] = (float)$customer->balance;
		$response['customer_credit_limit'] = (float)$customer->credit_limit;
		$response['customer_disable_loyalty'] = (boolean)$customer->disable_loyalty;
		$response['customer_points'] = (int)$customer->points;
		$response['customer_image_url'] = $customer->image_id ? secure_app_file_url($customer->image_id) : '';
		$response['customer_created_at'] = $customer->create_date ? date(get_date_format().' '.get_time_format(), strtotime($customer->create_date)) : NULL;
		$response['customer_location_id'] = $customer->location_id ? (int)$customer->location_id : NULL;
		
		$response['show_comment_on_receipt'] = (boolean)$receipt_cart->show_comment_on_receipt;
		$response['selected_tier_id'] = $receipt_cart->selected_tier_id;
		$response['sold_by_employee_id'] = $receipt_cart->sold_by_employee_id ? $receipt_cart->sold_by_employee_id : NULL;
		$response['discount_reason'] = $receipt_cart->discount_reason;
		$response['excluded_taxes'] = $receipt_cart->get_excluded_taxes();
		$response['has_delivery'] = (boolean)$receipt_cart->has_delivery;
		$response['delivery'] = $receipt_cart->delivery->to_array();
		$response['paid_store_account_ids'] = array();
		$response['suspended'] = $receipt_cart->suspended;
		$response['subtotal'] = $receipt_cart->get_subtotal();
		$response['tax'] = $receipt_cart->get_tax_total_amount();
		$response['total'] = $receipt_cart->get_total();
		$response['tip'] = to_currency_no_money($sale_info['tip']);
		$response['profit'] = to_currency_no_money($sale_info['profit']);
		$response['custom_fields'] = array();
		$response['return_sale_id'] = $sale_info['return_sale_id'];
		$payments = $receipt_cart->get_payments();
		
		for($k=1;$k<=NUMBER_OF_PEOPLE_CUSTOM_FIELDS;$k++)
		{
			if($this->Sale->get_custom_field($k) !== false)
			{
				$field = array();
				$field['label']= $this->Sale->get_custom_field($k);
				if($this->Sale->get_custom_field($k,'type') == 'date')
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
			
			if (property_exists($cart_item,'item_id'))
			{
				$cart_item_row['item_id'] = $cart_item->item_id;
				$cart_item_row['variation_id'] = $cart_item->variation_id;
			}
			else
			{
				$cart_item_row['item_kit_id'] = $cart_item->item_kit_id;
			}
			
			$cart_item_row['quantity'] = to_quantity($cart_item->quantity);
			$cart_item_row['unit_price'] = to_currency_no_money($cart_item->unit_price);
			$cart_item_row['cost_price'] = to_currency_no_money($cart_item->cost_price);
			$cart_item_row['discount'] = to_quantity($cart_item->discount);
			$cart_item_row['name'] = $cart_item->name;
			$cart_item_row['item_number'] = $cart_item->item_number;
			$cart_item_row['product_id'] = $cart_item->product_id;
			$cart_item_row['description'] = $cart_item->description;
			$cart_item_row['serialnumber'] = $cart_item->serialnumber;
			$cart_item_row['size'] = $cart_item->size;
			$cart_item_row['tier_id'] = $cart_item->tier_id ? $cart_item->tier_id : NULL;
			$response['cart_items'][] = $cart_item_row;
		}
		
		return $response;
	}
	
}
