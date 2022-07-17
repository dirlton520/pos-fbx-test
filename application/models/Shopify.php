<?php

require_once ("interfaces/Ecom.php");

class Shopify extends Ecom
{
	private $shopify_store_username;
	private $base_url_api;
	private $api_code_token;
	function __construct()
	{
		ini_set('memory_limit','1024M');
		parent::__construct();
		$this->api_code_token = $this->config->item('shopify_oauth_token');
		$this->shopify_store_username = $this->config->item('shopify_shop');
		$this->base_url_api = 'https://'.$this->shopify_store_username.'.myshopify.com';		
	}
	
	private function kill_if_needed()
	{
		if ($this->Appconfig->get_raw_kill_ecommerce_cron())
		{
			if (is_cli())
			{
				echo date(get_date_format().' h:i:s ').': KILLING CRON'."\n";
			}
			
			$this->Appconfig->save('kill_ecommerce_cron',0);
			echo json_encode(array('success' => TRUE, 'cancelled' => TRUE, 'sync_date' => date('Y-m-d H:i:s')));
			$this->save_log();
			die();
		}
	}
	
	
	function check_shopify_paid()
	{
		return $this->config->item('shopify_charge_id');
		
	}
	public function cancel_subscription()
	{
		$is_test = (!defined("ENVIRONMENT") or ENVIRONMENT == 'development') ? TRUE: NULL;
		$charge_id = $this->config->item('shopify_charge_id');
		$response = $this->do_delete("/admin/api/2021-04/recurring_application_charges/$charge_id.json");
		return TRUE;
	}
	function create_subscription()
	{
		$is_test = (!defined("ENVIRONMENT") or ENVIRONMENT == 'development') ? TRUE: NULL;
		
		$charge_response = $this->do_post('/admin/api/2021-04/recurring_application_charges.json',array('recurring_application_charge' =>
		array(
			"name" => "PHP POS Shopify",
			"price" => to_currency_no_money(SHOPIFY_PRICE),
			"return_url" => site_url('ecommerce/shopify_return_url_subscription'),
			"test" => $is_test,
			'trial_days' => 14
			)
		));
		
		
		if (isset($charge_response['recurring_application_charge']['confirmation_url']))
		{
			$this->Appconfig->save('shopify_was_cancelled',0);
			redirect($charge_response['recurring_application_charge']['confirmation_url']);
		}
		
		return FALSE;
	}
	
	function get_headers_from_curl_response($response)
	{
		$headers = array();

		$header_text = substr($response, 0, strpos($response, "\r\n\r\n"));

		foreach (explode("\r\n", $header_text) as $i => $line)
		if ($i === 0)
			$headers['http_code'] = $line;
		else
		{
			list ($key, $value) = explode(': ', $line);
			$headers[strtolower($key)] = $value;
		}

		return $headers;
	}
		
	private function do_get($end_point,$append_base_url_api = TRUE)
	{
		$this->kill_if_needed();
		
		$ch = curl_init(($append_base_url_api ? $this->base_url_api : '' ).$end_point);  
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
		curl_setopt($ch, CURLOPT_HEADER, 1);  
		//Don't verify ssl...just in case a server doesn't have the ability to verify
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		$headers = array(  
	    	'X-Shopify-Access-Token: '.$this->api_code_token,  
			'Content-Type: application/json',                                                                              
		);
				
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		$response = curl_exec($ch);
		
		if ($response === FALSE)
		{
			return FALSE;
		}
		
		$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
		$headers = $this->get_headers_from_curl_response(substr($response, 0, $header_size));
		$body = json_decode(substr($response, $header_size), TRUE);	
		
		
		//Pause for 10 seconds as we are close to rate limit
		list($api_calls,$api_limit) = explode('/',$headers['x-shopify-shop-api-call-limit']);
				
		if($api_calls/$api_limit >= .95)
		{
			sleep(10);			
		}

		
		curl_close($ch);

   		return array('body' => $body,'headers' => $headers);
		
	}
	
	private function do_post($end_point,$data)
	{				
		$this->kill_if_needed();
		
		$ch = curl_init($this->base_url_api.$end_point);  
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");                                                                     
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));                                                                  
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);   
		curl_setopt($ch, CURLOPT_HEADER, 1);  
		
		//Don't verify ssl...just in case a server doesn't have the ability to verify
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		$headers = array(  
	    	'X-Shopify-Access-Token: '.$this->api_code_token,     
			'Content-Type: application/json',                                                                           
		);				
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		
		$response = curl_exec($ch);
		
		if ($response === FALSE)
		{
			return FALSE;
		}
		
		$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
		$headers = $this->get_headers_from_curl_response(substr($response, 0, $header_size));
		$body = json_decode(substr($response, $header_size), TRUE);	
		
		//Pause for 10 seconds as we are close to rate limit
		list($api_calls,$api_limit) = explode('/',$headers['x-shopify-shop-api-call-limit']);
		
		if($api_calls/$api_limit >= .95)
		{
			sleep(10);			
		}
		curl_close($ch);
		
	   return $body;
	}
	
	private function do_put($end_point,$data)
	{		
		$this->kill_if_needed();
		
		$ch = curl_init($this->base_url_api.$end_point);  
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");                                                                     
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));                                                                  
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);   
		curl_setopt($ch, CURLOPT_HEADER, 1);  
		
		//Don't verify ssl...just in case a server doesn't have the ability to verify
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		$headers = array(  
	    	'X-Shopify-Access-Token: '.$this->api_code_token,     
			'Content-Type: application/json',                                                                           
		);
		
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		
		$response = curl_exec($ch);
		
		if ($response === FALSE)
		{
			return FALSE;
		}
		
		$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
		$headers = $this->get_headers_from_curl_response(substr($response, 0, $header_size));
		$body = json_decode(substr($response, $header_size), TRUE);	
		
		//Pause for 10 seconds as we are close to rate limit
		list($api_calls,$api_limit) = explode('/',$headers['x-shopify-shop-api-call-limit']);
		
		if($api_calls/$api_limit >= .95)
		{
			sleep(10);			
		}
		curl_close($ch);
	   return $body;
		
	}
	
	private function do_delete($end_point)
	{	
		$this->kill_if_needed();
		
		$ch = curl_init($this->base_url_api.$end_point);  
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");                                                                     
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);   
		//Don't verify ssl...just in case a server doesn't have the ability to verify
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_HEADER, 1); 
		$headers = array(  
	    	'X-Shopify-Access-Token: '.$this->api_code_token,     
			'Content-Type: application/json',                                                                           
		);
		
		
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		$response = curl_exec($ch);
		
		if ($response === FALSE)
		{
			return FALSE;
		}
		
		$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
		$headers = $this->get_headers_from_curl_response(substr($response, 0, $header_size));
		$body = json_decode(substr($response, $header_size), TRUE);	
		
		//Pause for 10 seconds as we are close to rate limit
		list($api_calls,$api_limit) = explode('/',$headers['x-shopify-shop-api-call-limit']);
		
		if($api_calls/$api_limit >= .95)
		{
			sleep(10);			
		}
		curl_close($ch);
	   return $body;
		
	}
	
		
	//This is a weird function. This is called when updating inventory for an item with variations
	public function save_item_variations($item_id)
	{
		if (!$this->check_shopify_paid())
		{
			$this->log(lang('shopify_not_paid'));
			return;
		}
		$item = $this->get_items_for_ecommerce($item_id)->row();
		$this->save_item($item);
	}

	//This is a weird function.
	//This function will save bits of data directly to e-commerce platform for managing stock or not. It is called in 2 places in application/controllers/Items.php. It uses a woo commerce format; but we can translate it so it works with shopify
	public function update_item_from_phppos_to_ecommerce($item_id, $data = array())
	{
		if (!$this->check_shopify_paid())
		{
			$this->log(lang('shopify_not_paid'));
			return;
		}
		
		if ($data['manage_stock'])
		{
			$quantity = $data['stock_quantity'];
			$item_info = $this->Item->get_info($item_id);
			
			$inv_data = array();
			
			//This is a variation when we pass in ecommerce_inventory_item_id
			if (isset($data['ecommerce_inventory_item_id']))
			{
				//Update stock level
				$this->db->where('ecommerce_inventory_item_id', $data['ecommerce_inventory_item_id']);
				$this->db->update('item_variations',array('ecommerce_variation_quantity' => (int)$quantity));
				
				$inv_data['inventory_item_id'] = $data['ecommerce_inventory_item_id'];
			}
			else//regular item
			{
				//Update stock level
				$this->db->where('ecommerce_inventory_item_id', $item_info->ecommerce_inventory_item_id);
				$this->db->update('items',array('ecommerce_product_quantity' => (int)$quantity));
				
				$inv_data['inventory_item_id'] = $item_info->ecommerce_inventory_item_id;
			}
			$inv_data['available'] = (int)$quantity;
			if(!$this->config->item('do_not_allow_out_of_stock_items_to_be_sold')){
				$inv_data['inventory_policy'] = 'continue';
			}
			$inv_data['location_id'] = $this->config->item('shopify_location_id');
			$this->do_post('/admin/api/2021-04/inventory_levels/set.json',$inv_data);			
		}		
	}
	
	private function save_item($item)
	{
		if (!$this->check_shopify_paid())
		{
			$this->log(lang('shopify_not_paid'));
			return;
		}
		
		$this->log(lang('common_save').': '.$item->name);
		$item_id = $item->item_id;
		$data = $this->make_product_data($item);
		
		if($ecom_prod_id = $this->get_ecommerce_product_id_for_item_id($item_id))
		{
			$result = $this->do_put("/admin/api/2021-04/products/$ecom_prod_id.json",$data);
		}
		else
		{
			$result = $this->do_post('/admin/api/2021-04/products.json',$data);
		}
		
		if ($result === FALSE)
		{
			return;
		}
				
		if (isset($result['product']['id']))
		{					
			$ecommerce_inventory_item_id = $result['product']['variants'][0]['inventory_item_id'];
			
			$this->link_item($item_id, $result['product']['id'], $data['product']['ecommerce_product_quantity'], date('Y-m-d H:i:s',strtotime($result['product']['updated_at'])),$ecommerce_inventory_item_id);	
						
			$item_images = $this->get_all_item_images_for_ecommerce_with_main_image_1st($item_id);
			$image_counter = 0;
			foreach($result['product']['images'] as $shopify_image)
			{
				$image_file_id = $item_images[$image_counter]['image_id'];
				$this->Item->link_image_to_ecommerce($image_file_id, $shopify_image['id']);				
				$image_counter++;
			}
						
			//In shopify everything is a variation (always has 1 variation). If there is more than 1 variation then we would treat it is variations in php pos or if the product title is NOT Default Title then we have a variation product
			if (count($result['product']['variants']) > 1 || $result['product']['variants'][0]['title'] != 'Default Title')
			{
				$phppos_item_variations = $this->get_item_variations_for_ecommerce($item_id, TRUE);
				
				$counter = 0;
				foreach($phppos_item_variations as $phppos_variation_id => $phppos_item_variation)
				{
					
					//This happens when a variation changes attributes or goes from no --> yes for is_ecommerce
					//Passing in null makes it so we don't change the value we stored before variation change
					if ($result['product']['variants'][$counter]['inventory_quantity'] == 0 && $phppos_item_variation['ecommerce_variation_id'])
					{
						$qty = NULL;
					}//Brand new variation
					elseif($phppos_item_variation['ecommerce_variation_id'] === NULL)
					{
						$qty = (int)$this->get_item_variation_quantity($phppos_variation_id);
					}
					else
					{
						$qty = $result['product']['variants'][$counter]['inventory_quantity'];
					}
					$this->link_item_variation($phppos_variation_id, $result['product']['variants'][$counter]['id'], $qty, date('Y-m-d H:i:s',strtotime($result['product']['variants'][$counter]['updated_at'])),$result['product']['variants'][$counter]['inventory_item_id']);
					$counter++;
				}
				
			}
				
				
			if (count($result['product']['variants']) > 1 || $result['product']['variants'][0]['title'] != 'Default Title')
			{
				$counter = 0;
				
				foreach($phppos_item_variations as $phppos_variation_id => $phppos_item_variation)
				{
					$inv_data = array();
					$inv_data['inventory_item_id'] = $result['product']['variants'][$counter]['inventory_item_id'];
					$inv_data['available'] = (int)$this->get_item_variation_quantity($phppos_variation_id);
					$inv_data['location_id'] = $this->config->item('shopify_location_id');
					if(!$this->config->item('do_not_allow_out_of_stock_items_to_be_sold')){
						$inv_data['inventory_policy'] = 'continue';
					}
					
					if ($this->do_post('/admin/api/2021-04/inventory_levels/set.json',$inv_data) === FALSE)
					{
						continue;
					}
					
					$ecommerce_variation_inventory_item_id = $result['product']['variants'][$counter]['inventory_item_id'];
					$inventory_item_data = array('inventory_item' => array('cost' => $phppos_item_variation['cost_price']));
					if ($this->do_put("/admin/api/2021-04/inventory_items/$ecommerce_variation_inventory_item_id.json",$inventory_item_data) === FALSE)
					{
						continue;
					}
					
					foreach($this->get_item_variation_images_for_ecommerce($phppos_variation_id) as $item_image)
					{
						$varient_id = $result['product']['variants'][$counter]['id'];
						$image_id = $item_image['ecommerce_image_id'];
						
						$var_image_data = array('variant' => array('id' => (int)$varient_id,'image_id' => (int)$image_id));						
						if ($this->do_put("/admin/api/2021-04/variants/$varient_id.json",$var_image_data) === FALSE)
						{
							continue;
						}
					}
					
					$counter++;
					
					
				}
				
			}
			else
			{
				$inv_data = array();
				$inv_data['inventory_item_id'] = $ecommerce_inventory_item_id;
				$inv_data['available'] = (int)$this->get_item_quantity($item_id);
				$inv_data['location_id'] = $this->config->item('shopify_location_id');
				if(!$this->config->item('do_not_allow_out_of_stock_items_to_be_sold')){
					$inv_data['inventory_policy'] = 'continue';
				}
				$this->do_post('/admin/api/2021-04/inventory_levels/set.json',$inv_data);

				$inventory_item_data = array('inventory_item' => array('cost' => $item->cost_price));
				$this->do_put("/admin/api/2021-04/inventory_items/$ecommerce_inventory_item_id.json",$inventory_item_data);
				
			}
			
			
			return $result['product']['id'];
		
		
		}
		
		return FALSE;
	}
	
	//This function will save an item to shopify. It will create a new or update an existing. This should work with variable and non variable
	public function save_item_from_phppos_to_ecommerce($item_id)
	{	
		if (!$this->check_shopify_paid())
		{
			$this->log(lang('shopify_not_paid'));
			return;
		}
				
		$this->log(lang("save_item_from_phppos_to_ecommerce").' '. $item_id);

		try
		{	
			$item = $this->get_items_for_ecommerce($item_id)->row();
			$this->save_item($item);

		}
		catch (Exception $e)
		{
			$this->log($e->getMessage());
		}
		
	}

	function get_tags($use_cache = TRUE)
	{
		if (!$this->check_shopify_paid())
		{
			$this->log(lang('shopify_not_paid'));
			return;
		}
		
		//No need to sync tags in shopify as it is just CSV for an item
	}

	function get_categories($use_cache = TRUE)
	{
		if (!$this->check_shopify_paid())
		{
			$this->log(lang('shopify_not_paid'));
			return;
		}
		
		//No need to sync categories in shopify as it is just a string
	}

	function sync_inventory_changes()
	{	
		if (!$this->check_shopify_paid())
		{
			$this->log(lang('shopify_not_paid'));
			return;
		}
					
		$this->log(lang("sync_inventory_changes"));
		
		$response = $this->do_get('/admin/api/2021-04/products.json');
		$this->process_sync_inventory_changes($response);
		
		//This gets called. See how Woo.php model does this. We need to sync inventory items between Shopfiy and php pos
		
		//There could be changes in shopify and php pos that has happened since last sync and need to make sure both systems have the right values. This needs to work with variation items as well as regular items
	}
	
	function process_sync_inventory_changes($response)
	{
		if (!$this->check_shopify_paid())
		{
			$this->log(lang('shopify_not_paid'));
			return;
		}
		
		if ($response === FALSE)
		{
			return;
		}
		
		$result_products = $response['body']['products'];
		
		$shopify_product_ids = array(-1);
		$shopify_variation_ids = array(-1);
		
		foreach($result_products as $shopify_product)
		{
			//Regular non variant products
			if (count($shopify_product['variants']) > 1 || $shopify_product['variants'][0]['title'] != 'Default Title')
			{
				foreach($shopify_product['variants'] as $variant)
				{
					$shopify_variation_ids[] = $variant['id'];
				}
			}
			else
			{
				$shopify_product_ids[] = $shopify_product['id'];
			}
		}

		$this->db->select('items.*,SUM(phppos_location_items.quantity) as quantity', FALSE);
		$this->db->from('items');
		$this->db->join('location_items','items.item_id = location_items.item_id','left');
		$this->db->where('items.deleted',0);
		$this->db->where_in('location_id',$this->ecommerce_store_locations);
		$this->db->where_in('ecommerce_product_id', $shopify_product_ids);
		$this->db->group_by('items.item_id');
		$items_result = $this->db->get();

		$items_info = array();
		foreach($items_result->result_array() as $item_result)
		{
			$items_info[$item_result['ecommerce_product_id']] = $item_result;
		}
				
		$this->db->select('item_variations.*,SUM(phppos_location_item_variations.quantity) as quantity', FALSE);
		$this->db->from('item_variations');
		$this->db->where('item_variations.deleted',0);
		$this->db->join('location_item_variations','item_variations.id = location_item_variations.item_variation_id','left');
		$this->db->where_in('location_id',$this->ecommerce_store_locations);
		$this->db->where_in('ecommerce_variation_id', $shopify_variation_ids);
		$this->db->group_by('item_variations.id');
		$items_variation_result = $this->db->get();

		$item_varations_info = array();
		foreach($items_variation_result->result_array() as $item_variation_result)
		{
			$item_varations_info[$item_variation_result['ecommerce_variation_id']] = $item_variation_result;
		}
		
		foreach($result_products as $shopify_product)
		{
			//Variant product
			if (count($shopify_product['variants']) > 1 || $shopify_product['variants'][0]['title'] != 'Default Title')
			{
				foreach($shopify_product['variants'] as $shopify_variation)
				{
					
					if (isset($item_varations_info[$shopify_variation['id']]))
					{
						@$item_id=$item_varations_info[$shopify_variation['id']]['item_id'];
						
						$item_quantity=$shopify_quantity="";
						$shopify_quantity=$shopify_variation['inventory_quantity'];
						@$item_variation_id=$item_varations_info[$shopify_variation['id']]['id'];
						if($item_variation_id!=NULL)
						{
							$item_quantity=$item_varations_info[$shopify_variation['id']]['quantity'];
						}
						if($item_quantity==="" && $shopify_quantity==="")
						{
							//quantity field not available in shopifycommerce and phppos
							$actual_quantity=0;
						}
						else if($item_quantity==="")
						{
							//quantity field not available in phppos but available in shopifycommerce
							$actual_quantity=$shopify_quantity;
						}
						else if($shopify_quantity==="")
						{
							//quantity field not available in shopifycommerce but available in phppos
							$actual_quantity=$item_quantity;
						}
						else
						{
							//quantity field present both on shopifycommerce and phppos
							$prev_quantity=   $item_varations_info[$shopify_variation['id']]['ecommerce_variation_quantity'];
							$pos_difference = $prev_quantity - $item_quantity;
							$shopify_difference = $prev_quantity - $shopify_quantity;
							$difference_sum	= $pos_difference + $shopify_difference;
							$actual_quantity = $prev_quantity - $difference_sum;
						}


						@$the_ecommerce_quantity = $item_varations_info[$shopify_variation['id']]['ecommerce_variation_quantity']; 
						if ($actual_quantity != $the_ecommerce_quantity)
						{
							$this->db->where('ecommerce_variation_id', $shopify_variation['id']);
							$this->db->update('item_variations',array('ecommerce_variation_quantity' => (int)$actual_quantity));
						}

						//update quantity to shopifycommerce
						if( $actual_quantity != $shopify_quantity )
						{
							$stock_set = array('ecommerce_inventory_item_id' => $item_varations_info[$shopify_variation['id']]['ecommerce_inventory_item_id'],'manage_stock' => TRUE, 'stock_quantity' => (int)$actual_quantity);							
							$this->update_item_from_phppos_to_ecommerce($item_id,$stock_set);
							
						}
						//update quantity to phppos
						if( $actual_quantity != $item_quantity)
						{
							$difference = (int)$actual_quantity - (int)$item_quantity;
							$current_location_quantity= $this->Item_variation_location->get_location_quantity($item_variation_id,$this->ecommerce_store_location);
							$updated_quantity = $current_location_quantity + $difference;

							if($item_variation_id!=NULL && $difference!=0){
							$cron_job_entry=lang('shopify_cron_job_entry');
							$this->db->insert('inventory',array('trans_date'=>date('Y-m-d H:i:s'),'trans_current_quantity' => $updated_quantity,'trans_items' => $item_varations_info[$shopify_variation['id']]['item_id'],'item_variation_id' => $item_varations_info[$shopify_variation['id']]['id'],'trans_user'=>1,'trans_comment'=>$cron_job_entry,'trans_inventory'=> $difference,'location_id'=>$this->ecommerce_store_location));

							$this->db->where(array('item_variation_id' => $item_variation_id,'location_id'=>$this->ecommerce_store_location));
							$this->log(lang("item inventory changed in php pos").' '.$item_variation_id .' ('.$updated_quantity.')');
							$this->db->update('location_item_variations',array('quantity'=>$updated_quantity));

							}
						}
					}
				}				
			}
			else //Regular non variant product
			{

				$item_quantity=$shopify_quantity="";
				$shopify_quantity=$shopify_product['variants'][0]['inventory_quantity'];
				
				@$item_id=$items_info[$shopify_product['id']]['item_id'];
				if($item_id!=NULL)
				{
					$item_quantity=$items_info[$shopify_product['id']]['quantity'];
				}
				if($item_quantity==="" && $shopify_quantity==="")
				{
					//quantity field not available in shopifycommerce and phppos
					$actual_quantity=0;
				}
				else if($item_quantity==="")
				{
					//quantity field not available in phppos but available in shopifycommerce
					$actual_quantity=$shopify_quantity;
				}
				else if($shopify_quantity==="")
				{
					//quantity field not available in shopifycommerce but available in phppos
					$actual_quantity=$item_quantity;
				}
				else
				{
					//quantity field present both on shopifycommerce and phppos
					$prev_quantity=   $items_info[$shopify_product['id']]['ecommerce_product_quantity'];
					$pos_difference = $prev_quantity - $item_quantity;
					$shopify_difference = $prev_quantity - $shopify_quantity;
					$difference_sum	= $pos_difference + $shopify_difference;
					$actual_quantity = $prev_quantity - $difference_sum;
				}


				@$the_ecommerce_quantity = $items_info[$shopify_product['id']]['ecommerce_product_quantity'];
				if ($actual_quantity != $the_ecommerce_quantity)
				{
					$this->db->where('ecommerce_product_id', $shopify_product['id']);
					$this->db->update('items',array('ecommerce_product_quantity' => (int)$actual_quantity));
				}

				//update quantity to shopifycommerce
				if( $actual_quantity != $shopify_quantity )
				{
					$stock_set = array('manage_stock' => TRUE, 'stock_quantity' => (int)$actual_quantity);
					$this->update_item_from_phppos_to_ecommerce($item_id,$stock_set);
					$this->log("put : products/".$shopify_product['id']);
					$this->log(lang('item inventory changed in shopify')." ".$shopify_product['id'] .' ('.to_quantity($actual_quantity).')');
				}
				//update quantity to phppos
				if( $actual_quantity != $item_quantity)
				{
					$difference = (int)$actual_quantity - (int)$item_quantity;
					$current_location_quantity= $this->Item_location->get_location_quantity($item_id,$this->ecommerce_store_location);
					$updated_quantity = $current_location_quantity + $difference;;

					if($item_id!=NULL && $difference!=0){
					$cron_job_entry=lang('shopify_cron_job_entry');
					$this->db->insert('inventory',array('trans_date'=>date('Y-m-d H:i:s'),'trans_current_quantity' => $updated_quantity,'trans_items' => $item_id,'trans_user'=>1,'trans_comment'=>$cron_job_entry,'trans_inventory'=> $difference,'location_id'=>$this->ecommerce_store_location));

					$this->db->where(array('item_id' => $item_id,'location_id'=>$this->ecommerce_store_location));
					$this->log(lang("item inventory changed in php pos").' '.$item_id .' ('.$updated_quantity.')');
					$this->db->update('location_items',array('quantity'=>$updated_quantity));

					}
				}
			}
			
		}
		
		if (isset($response['headers']['link']))
		{
			$matches = array();
		
			//This case finds matches when there are next AND previous links
			preg_match("/, <(.*)>; rel=\"next\"/", $response['headers']['link'], $matches);
			if (isset($matches[1]))
			{		
				$this->process_sync_inventory_changes($this->do_get($matches[1], FALSE));
			}
			else//This just finds when only next link
			{
				$matches = array();
				preg_match("/<(.*)>; rel=\"next\"/", $response['headers']['link'], $matches);
			
				if (isset($matches[1]))
				{		
					$this->process_sync_inventory_changes($this->do_get($matches[1], FALSE));
				}
			
			}
		}
	}
	
	private function make_category($category_id)
	{
		$smart_collection = array();
		$smart_collection['smart_collection']['title'] = $this->Category->get_full_path($category_id);
		$category = (array) $this->Category->get_info($category_id);
		if ($category['image_id'])
		{
			$file = $this->Appfile->get($category['image_id']);
			$file_name = $file->file_name;
			$file_data = $file->file_data;
		
			$image_data = array('attachment' => base64_encode($file_data));
			$smart_collection['smart_collection']['image'] = $image_data;
		}
		
		$smart_collection['smart_collection']['rules'][] = array(
		    "column" => "type",
		    "relation" => "equals",
		    "condition" => $this->Category->get_full_path($category_id),
		);
		
		return $smart_collection;
		
	}
	public function save_category($category_id)
	{
		if (!$this->check_shopify_paid())
		{
			$this->log(lang('shopify_not_paid'));
			return;
		}
		
		$smart_collection = $this->make_category($category_id);
		$response = $this->do_post('/admin/api/2021-04/smart_collections.json',$smart_collection);
		$ecommerce_category_id = $response['smart_collection']['id'];
		$this->link_category($category_id, $ecommerce_category_id);
		
	}

	public function update_category($category_id)
	{
		if (!$this->check_shopify_paid())
		{
			$this->log(lang('shopify_not_paid'));
			return;
		}
		
		$category = $this->Category->get_info($category_id);
		if (!$category->ecommerce_category_id)
		{
			$this->save_category($category_id);
			return;
		}
		
		$smart_collection = $this->make_category($category_id);
		
		$this->do_put('/admin/api/2021-04/smart_collections/'.$category->ecommerce_category_id.'.json',$smart_collection);
	}

	public function delete_category($category_id)
	{
		if (!$this->check_shopify_paid())
		{
			$this->log(lang('shopify_not_paid'));
			return;
		}
		
		$category = $this->Category->get_info($category_id);
		
		$this->do_delete('/admin/api/2021-04/smart_collections/'.$category->ecommerce_category_id.'.json');
	}

	public function save_tag($tag_name)
	{
		if (!$this->check_shopify_paid())
		{
			$this->log(lang('shopify_not_paid'));
			return;
		}
		
		//No need to do in shopify

	}

	public function delete_tag($tag_id)
	{
		if (!$this->check_shopify_paid())
		{
			$this->log(lang('shopify_not_paid'));
			return;
		}
		
		//No need to do in shopify
	}

	public function export_phppos_categories_to_ecommerce($root_category_id = null)
	{
		if (!$this->check_shopify_paid())
		{
			$this->log(lang('shopify_not_paid'));
			return;
		}
		
		//Categories --> Smart Collections
		
		foreach($this->Category->get_all_for_ecommerce() as $category_id => $category)
		{  		
			$this->log($this->Category->get_full_path($category_id));
				
			//New Smart Collection
			if (!$category['ecommerce_category_id'])
			{
				$this->save_category($category_id);
			}
			else
			{
				$this->update_category($category_id);
			}
			
		}
	}

	public function export_phppos_tags_to_ecommerce()
	{
		if (!$this->check_shopify_paid())
		{
			$this->log(lang('shopify_not_paid'));
			return;
		}
		
		//No need to do in shopify
	}

	function export_phppos_items_to_ecommerce()
	{
		if (!$this->check_shopify_paid())
		{
			$this->log(lang('shopify_not_paid'));
			return;
		}
		
		$this->log(lang("export_phppos_items_to_ecommerce"));
		
		//Use these items and export them to shopify data format
		//In php pos are items can be varient or non varient. It needs to handle both cases
		//Also needs to handle if the product changes from non varient to varient and visa versa
		$items_to_export_to_shopify = $this->get_items_for_ecommerce();
		
		while ($item = $items_to_export_to_shopify->unbuffered_row('object'))
		{
			if (!$item->deleted)
			{
				$this->save_item($item);
			}
		}
	}

	function import_ecommerce_items_into_phppos()
	{
		if (!$this->check_shopify_paid())
		{
			$this->log(lang('shopify_not_paid'));
			return;
		}
		
		$this->log(lang("import_ecommerce_items_into_phppos"));
		$response = $this->do_get('/admin/api/2021-04/products.json');
		$this->process_import_ecommerce_items_into_phppos($response);
		
	}
	
	private function process_import_ecommerce_items_into_phppos($response)
	{
		if (!$this->check_shopify_paid())
		{
			$this->log(lang('shopify_not_paid'));
			return;
		}
		
		if ($response === FALSE)
		{
			return;
		}
		
		
		$products = $response['body']['products'];
		
		$ecom_ids = array_column($products,'id');
				
		if (!empty($ecom_ids))
		{
			if(is_array($ecom_ids))
			{
				$this->db->from('items');

				$this->db->group_start();
				$ecom_ids_chunk = array_chunk($ecom_ids,25);
				foreach($ecom_ids_chunk as $ecom_ids)
				{
					$this->db->or_where_in('ecommerce_product_id',$ecom_ids);
				}
				$this->db->group_end();
				$result = $this->db->get();

				$phppos_items = array();
				while($row = $result->unbuffered_row('array'))
				{
					$phppos_items[$row['ecommerce_product_id']] = $row;
				}
			}
		}
		
		
		foreach($products as $product)
		{
			$item_row = isset($phppos_items[$product['id']]) ? $phppos_items[$product['id']] : FALSE;
			$item_id = isset($phppos_items[$product['id']]['item_id']) ? $phppos_items[$product['id']]['item_id'] : FALSE;
		
			$item_last_modified = isset($item_row['last_modified']) ? strtotime($item_row['last_modified']) : 0;
			$ecommerce_last_modified = strtotime($product['updated_at']);

			if($ecommerce_last_modified > $item_last_modified)
			{
				$inventory_id = $product['variants'][0]['inventory_item_id'];
				$inventory_item_response = $this->do_get("/admin/api/2021-04/inventory_items/$inventory_id.json");
				
				if ($inventory_item_response === FALSE)
				{
					continue;
				}
				
				if (isset($inventory_item_response['body']['inventory_item']['cost']) && $inventory_item_response['body']['inventory_item']['cost'])
				{
					$product['cost'] = $inventory_item_response['body']['inventory_item']['cost'];
				}
			
				for($k=0;$k<count($product['variants']); $k++)
				{
					if (isset($product['variants'][$k]['inventory_item_id']) && $product['variants'][$k]['inventory_item_id'])
					{
						$inventory_id = $product['variants'][$k]['inventory_item_id'];
						$inventory_item_response = $this->do_get("/admin/api/2021-04/inventory_items/$inventory_id.json");
						
						if ($inventory_item_response === FALSE)
						{
							continue;
						}
						
						if (isset($inventory_item_response['body']['inventory_item']['cost']) && $inventory_item_response['body']['inventory_item']['cost'])
						{
							$product['variants'][$k]['cost'] = $inventory_item_response['body']['inventory_item']['cost'];
			
						}
					}
				}
				
				$item_id = $this->add_update_item_from_ecommerce_to_phppos($product, $item_row);
				$item_row = (array)$this->Item->get_info($item_id);
			}
		}
		
		
		if (isset($response['headers']['link']))
		{
			$matches = array();
		
			//This case finds matches when there are next AND previous links
			preg_match("/, <(.*)>; rel=\"next\"/", $response['headers']['link'], $matches);
			if (isset($matches[1]))
			{		
				$this->process_import_ecommerce_items_into_phppos($this->do_get($matches[1], FALSE));
			}
			else//This just finds when only next link
			{
				$matches = array();
				preg_match("/<(.*)>; rel=\"next\"/", $response['headers']['link'], $matches);
			
				if (isset($matches[1]))
				{		
					$this->process_import_ecommerce_items_into_phppos($this->do_get($matches[1], FALSE));
				}
			
			}
		}
	}
	
	private function add_update_item_from_ecommerce_to_phppos($shopify_product, $item_row = array())
	{
		if (!$this->check_shopify_paid())
		{
			$this->log(lang('shopify_not_paid'));
			return;
		}
		
		$this->log(lang("add_update_item_from_ecommerce_to_phppos").": ".$shopify_product['title']);
		
		//make sure to save back to the right number field
		$sync_field = $this->config->item('sku_sync_field') ? $this->config->item('sku_sync_field') : 'item_number';
		
		static $phppos_cats;

		if (!$phppos_cats)
		{
			$this->load->model('Category');
			$phppos_cats = array_flip($this->Category->get_all_categories_and_sub_categories_as_indexed_by_category_id(FALSE));
		}

		static $suppliers;

		if (!$suppliers)
		{
			$this->load->model('Supplier');
			foreach($this->Supplier->get_all()->result_array() as $supplier_row)
			{
				if (isset($supplier_row['company_name']) && $supplier_row['company_name'])
				{
					$suppliers[$supplier_row['company_name']] = $supplier_row['person_id'];
				}
		
				if (isset($supplier_row['first_name']) && $supplier_row['first_name'])
				{
					$suppliers[$supplier_row['first_name'].' '.$$supplier_row['last_name']] = $supplier_row['person_id'];
				}
			}
		}
		
		
		$item_id = isset($item_row['item_id']) ? $item_row['item_id'] : false;
		$product_name = $shopify_product['title'];
		$weight = $shopify_product['variants'][0]['weight'];
		$weight_unit = $shopify_product['variants'][0]['weight_unit'];
		$product_id = $shopify_product['id'];
		$quantity = $shopify_product['variants'][0]['inventory_quantity'];
		
		//We only want to save item number if we have no variants (which means 1 variants as all things are variants in shopify)
		$item_number = $shopify_product['variants'][0]['sku'] && count($shopify_product['variants']) == 1 ? $shopify_product['variants'][0]['sku'] : FALSE;
		$barcode = $shopify_product['variants'][0]['barcode'] && count($shopify_product['variants']) == 1 ? $shopify_product['variants'][0]['barcode'] : FALSE;
		$product_description = $shopify_product['body_html'] ? $shopify_product['body_html'] : '';
		$product_short_description = $shopify_product['body_html'] ? $shopify_product['body_html'] : '';
		$last_modified =  date('Y-m-d H:i:s');
		$product_category=$shopify_product['product_type'];
		$product_variants = $shopify_product['variants'];
		$product_tags = explode(', ',$shopify_product['tags']);
		$taxable = TRUE;
		
		if (isset($shopify_product['variants'][0]['taxable']))
		{
			$taxable = (boolean)$shopify_product['variants'][0]['taxable'];
		}
		
		if (isset($phppos_cats[str_replace(' > ','|',$product_category)]))
		{
			$product_category = $phppos_cats[$product_category];
		}
		elseif($product_category)
		{
			$product_category = $this->Category->save($product_category);
			//We want to do this so the cache gets broken
			$phppos_cats = NULL;
		}
		else
		{
			$product_category = NULL;
		}
		
		
		$item_array = array(
			'name'=>$product_name,
			'description' => $product_short_description,
			'long_description' => $product_description,
			'category_id'=>$product_category,
			'ecommerce_product_id'=>$product_id,
			'ecommerce_inventory_item_id' => $shopify_product['variants'][0]['inventory_item_id'],
			'ecommerce_last_modified' => $last_modified,
			'last_modified' => $last_modified,
			'tax_included' => $this->config->item('prices_include_tax') ? 1 : 0,
			'weight' => $weight,
			'weight_unit' => $weight_unit,
			'override_default_tax' => $taxable ? 0 : 1,
		);
		
		if ($shopify_product['vendor'])
		{
			if (isset($suppliers[$shopify_product['vendor']]))
			{
				$item_array['supplier_id'] = $suppliers[$shopify_product['vendor']];
			}
			else
			{
				//Make a new supplier and save
				$person_data = array('first_name' => '', 'last_name' => '');
				$supplier_data = array('company_name' => $shopify_product['vendor']);
				$this->Supplier->save_supplier($person_data, $supplier_data);
				$item_array['supplier_id'] = $supplier_data['person_id'];
				$suppliers[$shopify_product['vendor']] = $item_array['supplier_id'];
			}
		}
		//New item
		if (!$item_id)
		{
			$item_array['commission_percent'] = NULL;
			$item_array['commission_fixed'] = NULL;
			$item_array['commission_percent_type'] = '';
		}
		else
		{
			//Don't overwrite category for existing items in case we are using parent/child
			unset($item_array['category_id']);
		}
		
		if ($product_variants[0]['price'] && !$this->config->item('online_price_tier'))
		{
			$item_array['unit_price'] = $product_variants[0]['price'];
		}
		
		//Non variations
		if (!(count($product_variants) > 1 || $product_variants[0]['title'] != 'Default Title'))
		{
			if ($product_variants[0]['compare_at_price'])
			{
				$item_array['promo_price'] =  $item_array['unit_price'];
				$item_array['start_date'] = NULL;
				$item_array['end_date'] = NULL;
				$item_array['unit_price'] = $product_variants[0]['compare_at_price'];
			}
			else
			{
				$item_array['promo_price'] =  NULL;
			}		
		}
		if (isset($shopify_product['cost']) && $shopify_product['cost'])
		{
			$item_array['cost_price'] = $shopify_product['cost'];
		}
		
		if ($item_number)
		{
			if ($sync_field != 'item_id')
			{
				$item_array[$sync_field] = $item_number;
			}

			if(!$item_id)
			{
				$this->load->model('Item');
				$item_id = $this->Item->get_item_id($item_number);
			}
		}
		
		if ($barcode)
		{
			//Save the barcode field as the other field we didn't use for $sync_field
			if ($sync_field == 'item_number')
			{
				$item_array['product_id'] = $barcode;
			}
			elseif($sync_field == 'product_id' && $item->item_number)
			{
				$item_array['item_number'] = $barcode;
			}
			
		}


		$this->load->model('Item_location');
		$item_location_info = $this->Item_location->get_info($item_id,$this->config->item('ecom_store_location') ? $this->config->item('ecom_store_location') : 1);
		$this->load->model('Item');

		//If we cannot save item then stop trying anything else
		if (!$this->Item->save($item_array,$item_id))
		{
			return;
		}
		
		$new_item = !$item_id;

		$item_id = isset($item_array['item_id']) ? $item_array['item_id'] : $item_id;

		if(count($product_tags)>0)
		{
			$this->load->model('Tag');
			$this->Tag->save_tags_for_item($item_id, implode(',',$product_tags));
		}
		
		if (isset($shopify_product['images'][0]) && $shopify_product['images'][0]['id'])
		{			
			foreach($shopify_product['images'] as $shopify_image)
			{
				$image_file_id = $this->get_image_file_id_for_ecommerce_image($shopify_image['id']);

				if(!$image_file_id)
				{
					@$image_contents = file_get_contents_curl($shopify_image['src']);
					$tmpFilename = tempnam(ini_get('upload_tmp_dir'), 'shopify');
					file_put_contents($tmpFilename,$image_contents);

					$config['image_library'] = 'gd2';
					$config['source_image']	= $tmpFilename;
					$config['create_thumb'] = FALSE;
					$config['maintain_ratio'] = TRUE;
					$config['width']	 = 1200;
					$config['height']	= 900;
					$this->image_lib->initialize($config);
					$this->image_lib->resize();
					$this->load->model('Appfile');
					$image_contents = file_get_contents($tmpFilename);


					if ($image_contents)
					{
						$image_file_id = $this->Appfile->save(basename($shopify_image['src']), $image_contents);
					}
					
					if (isset($image_file_id))
					{
						$this->Item->add_image($item_id, $image_file_id);
						$this->Item->link_image_to_ecommerce($image_file_id, $shopify_image['id']);

						//Features image
						if ($shopify_product['images'][0]['id'] == $shopify_image['id'])
						{
							$this->Item->set_main_image($item_id, $image_file_id);
						}
					}
				}
				
				//TODO see if we have image metadata and/or variation linkage
  			// $this->Item->save_image_metadata($image_file_id, $shopify_image['name'],$shopify_image['alt']);
			}
		}


		if (count($product_variants) > 1 || $product_variants[0]['title'] != 'Default Title')
		{

			foreach($product_variants as $product_variant)
			{
				$new_variation = FALSE;
				
				$variant_id = $product_variant['id'];
				$ecommerce_item_id = $product_variant['product_id'];
				$price = $product_variant['price'];
				
				if (isset($product_variant['cost']))
				{
					$cost = $product_variant['cost'];
				}
				$sku = $product_variant['sku'];
				$barcode = $product_variant['barcode'];
				$shopify_image_id = $product_variant['image_id'];
				$attribute_value_ids = array();
				
				for($attribute_counter = 1; $attribute_counter<=3;$attribute_counter++)
				{
					$option_value = $product_variant["option$attribute_counter"];
					
					if ($option_value === NULL)
					{
						break;
					}
					
					
					$attr_name = $shopify_product['options'][$attribute_counter-1]['name'];
					
					if (!$this->Item_attribute->attribute_name_exists($attr_name,$item_id) && !$this->Item_attribute->attribute_name_exists($attr_name))
					{
						$item_attr_data = array('name' => $attr_name, 'item_id' => $item_id);
						
						$attribute_ids_to_save = array();
						$attribute_ids_to_save[] = $this->Item_attribute->save($item_attr_data);
						$this->Item_attribute->save_item_attributes($attribute_ids_to_save, $item_id, false);
					}
					else
					{
						$attribute_ids_to_save = array();
						
						//Item level
						if ($attr_id = $this->Item_attribute->get_attribute_id($attr_name,$item_id))
						{
							$attribute_ids_to_save[] = $attr_id;
						}//Global
						elseif($attr_id =  $this->Item_attribute->get_attribute_id($attr_name))
						{
							$attribute_ids_to_save[] = $attr_id;
						}
						
						$this->Item_attribute->save_item_attributes($attribute_ids_to_save, $item_id, false);
					}
					
					//item level
					$attribute_id = $this->get_attribute_id_from_ecommerce_attribute_name($attr_name, $item_id);
					
					if (!$attribute_id)
					{
						//global level
						$attribute_id = $this->get_attribute_id_from_ecommerce_attribute_name($attr_name, NULL);
					}
					
					if (!$this->Item_attribute_value->exists($option_value,$attribute_id))
					{
						$attribute_value_ids_to_save = array();
						$attribute_value_ids_to_save[] = $this->Item_attribute_value->save($option_value, $attribute_id);
						
						$this->Item_attribute_value->save_item_attribute_values($item_id, $attribute_value_ids_to_save);
						
					}
					else
					{
						$attribute_value_ids_to_save = array();
						$attribute_value_ids_to_save[] = $this->Item_attribute_value->get_attribute_value_id($option_value,$attribute_id);
						
						$this->Item_attribute_value->save_item_attribute_values($item_id, $attribute_value_ids_to_save);
					}
										
					$attribute_value_id = $this->lookup_attribute_value_id_from_attribute_id_and_option($attribute_id, $option_value,$item_id);
					
					if (!$attribute_value_id)
					{
						//global
						$attribute_value_id = $this->lookup_attribute_value_id_from_attribute_id_and_option($attribute_id, $option_value);
						$attribute_value_ids[]=$attribute_value_id;
					}
					else
					{
						//item level
						$attribute_value_ids[]=$attribute_value_id;
					}
				}	
				
				//attempt to match with existing variations
				$variation_id = $this->Item_variations->lookup($item_id, $attribute_value_ids);
								
				if (!$variation_id)
				{
					$new_variation = TRUE;
				}
				
				$ecommerce_last_modified = date('Y-m-d H:i:s',strtotime($product_variant['updated_at']));
				
				$item_variation = array(
					'item_id' => $item_id,
					'ecommerce_variation_id' => $variant_id,
					'ecommerce_inventory_item_id' => $product_variant['inventory_item_id'],
					'ecommerce_last_modified' => $ecommerce_last_modified,
					'last_modified' => $ecommerce_last_modified,
					'item_number' => $sku ? $sku : null,
					'deleted' => 0,
				);
				
				if (isset($cost))
				{
					$item_variation['cost_price'] = $cost;					
				}
				
				
				
				if ($price  && !$this->config->item('online_price_tier'))
				{
					$item_variation['unit_price'] = $price;
				}


				if ($product_variant['compare_at_price'])
				{
					$item_variation['promo_price'] =  $item_variation['unit_price'];
					$item_variation['unit_price'] = $product_variant['compare_at_price'];
					$item_variation['start_date'] = NULL;
					$item_variation['end_date'] = NULL;
				}
				else
				{
					$item_variation['promo_price'] = NULL;
				}


				$variation_id = $this->Item_variations->save($item_variation, $variation_id, $attribute_value_ids);
				
				
				if ($barcode)
				{
					$this->load->model('Additional_item_numbers');
					$this->Additional_item_numbers->save_variation($item_id, $variation_id, array($barcode));				
				}
				
				
				
				if ($shopify_image_id)
				{
					$this->Item->set_variation_for_ecommerce_image($shopify_image_id,$variation_id);
				}
					
				//This is a brand new variation we want to make sure we setup stock correctly
				if ($variation_id && $new_variation && $product_variant['inventory_quantity'] !== NULL)
				{
					$ecommerce_product_quantity_data = array('ecommerce_variation_quantity' => $product_variant['inventory_quantity']);
					$this->Item_variations->save($ecommerce_product_quantity_data,$variation_id);

				  	$item_variation_location_data = array(
		            'item_variation_id'=>$variation_id,
		            'location_id'=>$this->ecommerce_store_location,
		            'quantity'=>$product_variant['inventory_quantity']
		     	 	);
					$item_variation_location_data = array('item_variation_id'=>$variation_id,'location_id'=>$this->ecommerce_store_location,'quantity'=>$product_variant['inventory_quantity']);
					$this->load->model('Item_variation_location');
					$this->Item_variation_location->save($item_variation_location_data, $variation_id, $this->ecommerce_store_location);
				}
				
			}
		}
		else //Regular products
		{
			//This is a brand new item we want to make sure we setup stock correctly
			if ($new_item && $quantity !== NULL)
			{
				$ecommerce_product_quantity_data = array('ecommerce_product_quantity' => $quantity);
				$this->Item->save($ecommerce_product_quantity_data,$item_id);

				$location_item_array = array('item_id'=>$item_id,'location_id'=>$this->ecommerce_store_location,'quantity'=>$quantity);
				$this->load->model('Item_location');
				$this->Item_location->save($location_item_array, $item_id, $this->ecommerce_store_location);
			}
		}
		
		
		//make sure to reset last modified data so it has right data and doesnt't double sync. 
		//last_modified get changes after intial save due to other mods to items
		$last_modified_data = array(
			'ecommerce_last_modified' => $last_modified,
			'last_modified' => $last_modified
		);
		
		$this->Item->save($last_modified_data,$item_id);
	}

	public function delete_item($item_id)
	{
		if (!$this->check_shopify_paid())
		{
			$this->log(lang('shopify_not_paid'));
			return;
		}
		
		$shopify_product_id = $this->get_ecommerce_product_id_for_item_id($item_id);
		$this->reset_item($item_id);
		$this->do_delete("/admin/api/2021-04/products/$shopify_product_id.json");
	}

	public function delete_items($item_ids)
	{
		if (!$this->check_shopify_paid())
		{
			$this->log(lang('shopify_not_paid'));
			return;
		}
		
		foreach($item_ids as $item_id)
		{
			$this->delete_item($item_id);
		}
	}

	public function undelete_item($item_id)
	{
		if (!$this->check_shopify_paid())
		{
			$this->log(lang('shopify_not_paid'));
			return;
		}
		
		$this->reset_item($item_id);
		$this->save_item_from_phppos_to_ecommerce($item_id);
	}

	public function undelete_items($item_ids)
	{
		if (!$this->check_shopify_paid())
		{
			$this->log(lang('shopify_not_paid'));
			return;
		}
		
		foreach($item_ids as $item_id)
		{
			$this->undelete_item($item_id);
		}
	}
	
	public function undelete_all()
	{
		if (!$this->check_shopify_paid())
		{
			$this->log(lang('shopify_not_paid'));
			return;
		}
		
		//don't implement. Woo does NOT also
	}
	

	function import_ecommerce_tags_into_phppos()
	{
		if (!$this->check_shopify_paid())
		{
			$this->log(lang('shopify_not_paid'));
			return;
		}
		
		//No need to do in shopify
	}

	function import_ecommerce_categories_into_phppos()
	{
		if (!$this->check_shopify_paid())
		{
			$this->log(lang('shopify_not_paid'));
			return;
		}
		
		//No need to do in shopify
	}

	function import_ecommerce_attributes_into_phppos()
	{
		if (!$this->check_shopify_paid())
		{
			$this->log(lang('shopify_not_paid'));
			return;
		}
		
		//No need to do in shopify
	}

	function export_phppos_attributes_to_ecommerce()
	{
		if (!$this->check_shopify_paid())
		{
			$this->log(lang('shopify_not_paid'));
			return;
		}
		
		//No need to do in shopify
	}

	function import_ecommerce_orders_into_phppos()
	{		
		if (!$this->check_shopify_paid())
		{
			$this->log(lang('shopify_not_paid'));
			return;
		}
		
		$this->log(lang("import_ecommerce_orders_into_phppos"));
		
		if ($this->config->item('ecommerce_only_sync_completed_orders'))
		{
			$response = $this->do_get('/admin/api/2021-04/orders.json?status=closed');			
		}
		else
		{
			$response = $this->do_get('/admin/api/2021-04/orders.json?status=any');
		}
		$this->process_import_ecommerce_orders_into_phppos($response);	
	}
	
	function save_custom_line_item($line_unit_price,$line_cost_price,$total_tax,$item_id,$sale_id,$line_index,$quantity=1)
	{
		if (!$this->check_shopify_paid())
		{
			$this->log(lang('shopify_not_paid'));
			return;
		}
		
		$line_unit_price = (float)$line_unit_price;
		$line_cost_price = (float)$line_cost_price;
		$total_tax = (float)$total_tax;

		if ($line_unit_price)
		{
			if ($line_unit_price)
			{
				$tax_percent = (float)($total_tax/$line_unit_price)*100;
			}
			else
			{
				$tax_percent = 0;
			}

			$sales_items = array();

			$sales_items['sale_id'] = $sale_id;
			$sales_items['item_id'] = $item_id;
			$line_unit_price = $line_unit_price;

			$sales_items['quantity_purchased'] = $quantity;
			$sales_items['line'] = $line_index;
			$sales_items['item_unit_price'] = $line_unit_price;
			$sales_items['item_cost_price'] = $line_cost_price;

			$sales_items['subtotal']=$line_unit_price*$quantity;
			$sales_items['total']=($line_unit_price*$quantity)+$total_tax;
			$sales_items['tax']=$total_tax;
			$sales_items['profit']=0;

			$this->db->insert('sales_items',$sales_items);

			if ($tax_percent)
			{
				$sales_items_taxes = array(
					'name' => lang('common_sales_tax_1'),
					'sale_id' => $sale_id,
					'item_id' => $item_id,
					'line' => $line_index,
					'percent' => round($tax_percent,2),
				);

				$this->db->insert('sales_items_taxes',$sales_items_taxes);
			}
		}

	}

 	private function save_line_item($line_item,$sale_id,$line_index)
	{
		if (!$this->check_shopify_paid())
		{
			$this->log(lang('shopify_not_paid'));
			return;
		}
		
		$sales_items = array();

		$shopify_product_id = $line_item['product_id'];
		$shopify_variation_id = $line_item['variant_id'];
		$phppos_item_id = $this->get_item_id_for_ecommerce_product($shopify_product_id);
		$phppos_variation_id = $this->get_variation_id_for_ecommerce_product_variation($shopify_variation_id);

		$sales_items['sale_id'] = $sale_id;
		$sales_items['item_id'] = $phppos_item_id;
		$sales_items['item_variation_id'] = $phppos_variation_id;
		$quantity = $line_item['quantity'];
		$subtotal = $line_item['price']*$quantity;
		
		$total_tax = 0;
		$tax_percent = 0;
		
		foreach($line_item['tax_lines'] as $taxes)
		{
			$total_tax+=$taxes['price'];
			$tax_percent+=$taxes['rate']*100;
		}

		$total = $subtotal+$total_tax;

		$unit_subtotal = (float)$quantity ? $subtotal/$quantity : $quantity;


		$sales_items['quantity_purchased'] = $quantity;
		$sales_items['line'] = $line_index;
		$sales_items['item_unit_price'] = $subtotal/$quantity;
		$item_info = $this->Item->get_info($phppos_item_id);
		$item_location_info = $this->Item_location->get_info($phppos_item_id);
		$variation_info = $this->Item_variations->get_info($phppos_variation_id);

		if ($variation_info && $variation_info->unit_price)
		{
			$sales_items['regular_item_unit_price_at_time_of_sale'] = $variation_info->unit_price;
		}
		else
		{
			$sales_items['regular_item_unit_price_at_time_of_sale'] = ($item_location_info && $item_location_info->unit_price) ? $item_location_info->unit_price : $item_info->unit_price;
		}


		if ($variation_info && $variation_info->cost_price)
		{
			$sales_items['item_cost_price'] = $variation_info->cost_price;
		}
		else
		{
			$sales_items['item_cost_price'] = $item_location_info->cost_price ? $item_location_info->cost_price : $item_info->cost_price;
		}

		$profit = ((double)$sales_items['item_unit_price']* (double)$quantity) - ((double)$sales_items['item_cost_price'] * (double)$quantity);

		$sales_items['subtotal']=$subtotal;
		$sales_items['total']=$subtotal+$total_tax;
		$sales_items['tax']=$total_tax;
		$sales_items['profit']= $profit;

		$this->db->insert('sales_items',$sales_items);

		if ($tax_percent)
		{
			$sales_items_taxes = array(
				'name' => lang('common_sales_tax_1'),
				'sale_id' => $sale_id,
				'item_id' => $phppos_item_id,
				'line' => $line_index,
				'percent' => round($tax_percent,2),
			);

			$this->db->insert('sales_items_taxes',$sales_items_taxes);
		}
	}

	function save_delivery($order,$sale_id,$customer_id)
	{
		if (!$this->check_shopify_paid())
		{
			$this->log(lang('shopify_not_paid'));
			return;
		}
		
		$actual_shipping_date = $order['closed_at'] ? date('Y-m-d H:i:s',strtotime($order['closed_at'])) : NULL;
		$estimated_shipping_date = $order['processed_at'] ? date('Y-m-d H:i:s',strtotime($order['processed_at'])) : NULL;

		$data = array(
			'sale_id' => $sale_id,
			'shipping_address_person_id' => $customer_id,
			'actual_shipping_date' =>$actual_shipping_date,
			'estimated_shipping_date' =>$estimated_shipping_date,
		);
		
		$this->Delivery->save($data);
	}
	
	private function get_sale_totals($order)
	{
		if (!$this->check_shopify_paid())
		{
			$this->log(lang('shopify_not_paid'));
			return;
		}
		
		$return = array('total_quantity_purchased' => 0,'profit' => 0);

		$line_items = $order['line_items'];
		foreach($line_items as $line_item)
		{
			$shopify_product_id = $line_item['product_id'];
			$shopify_variation_id = $line_item['variant_id'];

			$phppos_item_id = $this->get_item_id_for_ecommerce_product($shopify_product_id);
			$phppos_variation_id = $this->get_variation_id_for_ecommerce_product_variation($shopify_variation_id);

			$quantity = $line_item['quantity'];
			$unit_subtotal = $line_item['price'];

			$item_info = $this->Item->get_info($phppos_item_id);
			$item_location_info = $this->Item_location->get_info($phppos_item_id);
			$variation_info = $this->Item_variations->get_info($phppos_variation_id);


			if ($variation_info && $variation_info->cost_price)
			{
				$item_cost_price = $variation_info->cost_price;
			}
			else
			{
				$item_cost_price = $item_location_info->cost_price ? $item_location_info->cost_price : $item_info->cost_price;
			}
			$return['profit'] += ((double)$unit_subtotal * (double)$quantity) - ((double)$item_cost_price * (double)$quantity);
			$return['total_quantity_purchased']+=$quantity;

		}
		return $return;
	}
	
	
	
	private function save_shopify_customer_from_order($order)
	{
		if (!$this->check_shopify_paid())
		{
			$this->log(lang('shopify_not_paid'));
			return;
		}
		
		@$customer_shipping = $order['shipping_address'];
		
		if (isset($customer_billing) && !empty($customer_billing))
		{
			$customer_billing = $order['billing_address'];
			$customer = array_merge($customer_billing,$customer_shipping);			
		}
		elseif(isset($customer_shipping))
		{
			$customer = $customer_shipping;
		}


		//If this info is empty for shipping then get from billing
		$empty_shipping_key_checks = array('first_name','last_name','phone','address1','address2','city','state','postcode','country','company');
		foreach($empty_shipping_key_checks as $key_check)
		{
			if(isset($customer_billing))
			{
				if(!isset($customer[$key_check]) && !$customer[$key_check])
				{
					$customer[$key_check] = $customer_billing[$key_check];
				}
			}
		}
		
		if ($order['email'])
		{
			//Existing customer lookup by email
			if ($order['email'] && ($phppos_customer_info = $this->Customer->get_info_by_email($order['email'])))
			{
				$sale_customer_id = $phppos_customer_info->person_id;
			}
			else
			{
				$person_data = array(
				'first_name'=>$customer['first_name'] ? $customer['first_name'] : '',
				'last_name'=>$customer['last_name'] ? $customer['last_name'] : '',
				'email'=>$order['email'],
				'phone_number'=>$customer['phone'] ? $customer['phone'] : '',
				'address_1'=>$customer['address1'] ? $customer['address1'] : '',
				'address_2'=>$customer['address2'] ? $customer['address2'] : '',
				'city'=>$customer['city'] ? $customer['city'] : '',
				'state'=>$customer['province'] ? $customer['province'] : '',
				'zip'=>$customer['zip'] ? $customer['zip'] : '',
				'country'=>$customer['country'] ? $customer['country'] : '',
				);


				$customer_data=array(
					'company_name' => $customer['company'] ? $customer['company'] : '',
				);

				$this->Customer->save_customer($person_data, $customer_data);

				$sale_customer_id = $person_data['person_id'];
			}

			return $sale_customer_id;
		}

		return NULL;
	}
	
	private function process_import_order($order)
	{
		if (!$this->check_shopify_paid())
		{
			$this->log(lang('shopify_not_paid'));
			return;
		}
		
		$this->log(lang('common_import_order').' #'.$order['id']);
		$sales_data = array();
		
		$sales_totals = $this->get_sale_totals($order);
		$shopify_id = $order['id'];
		$customer_id = $this->save_shopify_customer_from_order($order);		
		$sale_id = $this->get_sale_id_for_ecommerce_order_id($order['id']);
		$sales_data['employee_id'] = 1;
		
		//If we are importing orders suspended we don't want to overwrite this after 1st import so we don't break edits
		if ($sale_id && $this->config->item('import_ecommerce_orders_suspended'))
		{
			return;
		}
		
		if (!$sale_id && $this->config->item('import_ecommerce_orders_suspended'))
		{	
			$sales_data['suspended'] = $this->config->item('ecommerce_suspended_sale_type_id');
		}
		
		$sales_data['sale_time'] = date('Y-m-d H:i:s',strtotime($order['processed_at']));
		$sales_data['location_id'] = $this->ecommerce_store_location;
		$sales_data['customer_id'] = $customer_id;
		$sales_data['is_ecommerce'] = 1;
				
		$sales_data['subtotal'] = $order['subtotal_price'];
		$sales_data['total'] = $order['total_price'];
		$sales_data['tax'] = $order['total_tax'];
		
		$sales_data['profit'] = $sales_totals['profit'];
		$sales_data['total_quantity_purchased'] = $sales_totals['total_quantity_purchased'];
		$sales_data['comment'] = 'shopify #'.$shopify_id;
		$sales_data['ecommerce_order_id'] = $shopify_id;
		$sales_data['ecommerce_status'] = '';
		$sales_data['payment_type'] = lang('common_online');

		if ($sale_id)
		{
			$this->db->where('sale_id', $sale_id);
			$this->db->update('sales',$sales_data);

			//Delete sale data
			$this->db->delete('sales_payments', array('sale_id' => $sale_id));
			$this->db->delete('sales_items_taxes', array('sale_id' => $sale_id));
			$this->db->delete('sales_items', array('sale_id' => $sale_id));
			$this->db->delete('sales_item_kits_taxes', array('sale_id' => $sale_id));
			$this->db->delete('sales_item_kits', array('sale_id' => $sale_id));
			$this->db->delete('sales_coupons', array('sale_id' => $sale_id));
			$this->db->delete('sales_deliveries', array('sale_id' => $sale_id));
		}
		else
		{
			$this->db->insert('sales',$sales_data);
			$sale_id = $this->db->insert_id();
		}

		$this->db->insert('sales_payments',
	      array(
	         'sale_id'=> $sale_id, 'payment_date' => $sales_data['sale_time'] ,'payment_type' =>lang('common_online'),
	         'payment_amount' =>  $order['total_price'],
	      )
	   );

		if ($customer_id)
		{
			$this->save_delivery($order,$sale_id,$customer_id);
		}

		$line_items = $order['line_items'];

		$counter = 0;
		foreach($line_items as $line_item)
		{
			$this->save_line_item($line_item,$sale_id,$counter);
			$counter++;
		}

		if (isset($order['total_shipping_price_set']['shop_money']['amount']) && (float)$order['total_shipping_price_set']['shop_money']['amount'])
		{
			$this->save_custom_line_item($order['total_shipping_price_set']['shop_money']['amount'],$order['total_shipping_price_set']['shop_money']['amount'],0,$this->Item->create_or_update_delivery_item(FALSE),$sale_id,$counter, 1);
			$counter++;
		}

	}
	
	function process_import_ecommerce_orders_into_phppos($response)
	{
		if (!$this->check_shopify_paid())
		{
			$this->log(lang('shopify_not_paid'));
			return;
		}
		
		if ($response === FALSE)
		{
			return;
		}
		
		if(isset($response['body']['orders']))
		{
			foreach($response['body']['orders'] as $order)
			{
				$this->process_import_order($order);
			}
		}	
		if (isset($response['headers']['link']))
		{
			$matches = array();
		
			//This case finds matches when there are next AND previous links
			preg_match("/, <(.*)>; rel=\"next\"/", $response['headers']['link'], $matches);
			if (isset($matches[1]))
			{		
				$this->process_import_ecommerce_orders_into_phppos($this->do_get($matches[1], FALSE));
			}
			else//This just finds when only next link
			{
				$matches = array();
				preg_match("/<(.*)>; rel=\"next\"/", $response['headers']['link'], $matches);
			
				if (isset($matches[1]))
				{		
					$this->process_import_ecommerce_orders_into_phppos($this->do_get($matches[1], FALSE));
				}
			
			}
		}
	}
	
	function get_tax_class_rates($phppos_tax_class_id,$use_cache = TRUE)
	{
		if (!$this->check_shopify_paid())
		{
			$this->log(lang('shopify_not_paid'));
			return;
		}
		
		//No need to do in shopify
	}

	function get_tax_classes($use_cache = TRUE)
	{
		if (!$this->check_shopify_paid())
		{
			$this->log(lang('shopify_not_paid'));
			return;
		}
		
		//No need to do in shopify
	}

	function import_tax_classes_into_phppos()
	{
		if (!$this->check_shopify_paid())
		{
			$this->log(lang('shopify_not_paid'));
			return;
		}
		
		//No need to do in shopify
	}

	function export_tax_classes_into_phppos()
	{
		if (!$this->check_shopify_paid())
		{
			$this->log(lang('shopify_not_paid'));
			return;
		}
		
		//No need to do in shopify
	}

	public function save_tax_class($tax_class_id)
	{
		if (!$this->check_shopify_paid())
		{
			$this->log(lang('shopify_not_paid'));
			return;
		}
		
		//No need to do in shopify
	}

	function import_shipping_classes_into_phppos()
	{
		if (!$this->check_shopify_paid())
		{
			$this->log(lang('shopify_not_paid'));
			return;
		}
		
		//No need to do in shopify
	}
	
	private function make_product_data($item)
	{	
		if (!$this->check_shopify_paid())
		{
			$this->log(lang('shopify_not_paid'));
			return;
		}
					
		$item_id = $item->item_id;
		$this->load->model('Item_location');
		$item_location_info = $this->Item_location->get_info($item_id,$this->config->item('ecom_store_location') ? $this->config->item('ecom_store_location') : 1);
		
		$this->load->model('Item_variations');
		$variations = $this->Item_variations->get_all($item_id);
		$this->load->model('Item_taxes_finder');
		
		$taxable = $this->Item_taxes_finder->is_taxable($item_id);
		
		//Need to figure out quantity
		$quantity = $item->quantity;
		
		if ($this->config->item('online_price_tier'))
		{
			$this->load->model('Tier');
			$this->load->model('Item_location');
			$this->load->model('Item');
			$online_price = $this->Item->get_sale_price(array('item_id' => $item_id,'tier_id' => $this->config->item('online_price_tier')));	
		}
		else
		{
			$online_price = to_currency_no_money($item_location_info->unit_price ? $item_location_info->unit_price : $item->unit_price);
		}
				
		$data = array(
			'title' =>$item->name,
			'body_html' =>$item->long_description ? $item->long_description : $item->description,
			'product_type' => $this->Category->get_full_path($item->category_id),
			'published_scope' => 'web',
			'published' => TRUE,
			'status' => $item->item_inactive ? 'archived' : 'active',
			'inventory_management' => (isset($item->is_service) && $item->is_service) ? NULL : 'shopify',
			'ecommerce_product_quantity' => $this->ecommerce_store_locations ? $this->get_item_quantity($item_id) : $item->quantity
		);

		if(!$this->config->item('do_not_allow_out_of_stock_items_to_be_sold')){
			$data['inventory_policy'] = 'continue';
		}
		
		if ($item->supplier_id)
		{
			$data['vendor'] = $this->Supplier->get_name($item->supplier_id);
		}
				
		$item_variations = $this->get_item_variations_for_ecommerce($item_id, TRUE);
		
		
		if (count($item_variations) == 0)
		{
			$data['variants'][0]['price'] = $online_price;		
			$data['variants'][0]['inventory_management'] = (isset($item->is_service) && $item->is_service) ? NULL : 'shopify';		
			$data['variants'][0]['taxable'] = $taxable;
			$data['variants'][0]['weight'] = $item->weight;
			$data['variants'][0]['weight_unit'] = $item->weight_unit ? $item->weight_unit : 'lb';
			if(!$this->config->item('do_not_allow_out_of_stock_items_to_be_sold')){
				$data['variants'][0]['inventory_policy'] = 'continue';
			}
			if (!$item_location_info->promo_price)
			{
				if ($item->promo_price)
				{
					if (!$item->start_date && !$item->end_date)
					{
						$data['variants'][0]['price'] = to_currency_no_money($item->promo_price);;		
						$data['variants'][0]['compare_at_price'] = $online_price;
					}	
				}
			}
			else
			{
				if ($item_location_info->promo_price)
				{
					if (!$item->start_date && !$item->end_date)
					{
						$data['variants'][0]['price'] = to_currency_no_money($item_location_info->promo_price);;		
						$data['variants'][0]['compare_at_price'] = $online_price;
					}
				}
			}
		}
			
		$sync_field = $this->config->item('sku_sync_field') ? $this->config->item('sku_sync_field') : 'item_number';
		
		if (count($item_variations) == 0)
		{
			if($item->$sync_field)
			{
				$data['variants'][0]['sku'] = $item->$sync_field;
			}
			//Save the barcode field as the other field we didn't use for $sync_field
			if ($sync_field == 'item_number' && $item->product_id)
			{
				$data['variants'][0]['barcode'] = $item->product_id;
			}
			elseif($sync_field == 'product_id' && $item->item_number)
			{
				$data['variants'][0]['barcode'] = $item->item_number;			
			}
			elseif($item->item_number)
			{			
				$data['variants'][0]['barcode'] = $item->item_number;			
			}
		}
		
		$item_images = $this->get_all_item_images_for_ecommerce_with_main_image_1st($item_id);
		
		if(count($item_images) > 0 && !$this->config->item('do_not_upload_images_to_ecommerce'))
		{
			$data['images'] = array();
			
			$this->load->model('Appfile');
			
			$position = 1;
			foreach($item_images as $item_image)
			{
				$file = $this->Appfile->get($item_image['image_id']);
				$file_name = $file->file_name;
				$file_data = $file->file_data;
				
				$image_data = array('attachment' => base64_encode($file_data), 'position' => $position);
				if($item_image['ecommerce_image_id'])
				{
					$image_data['id'] = $item_image['ecommerce_image_id'];
				}
				$data['images'][] = $image_data;
				$position ++;
			}	
		}
		elseif(count($item_images)  == 0 && !$this->config->item('do_not_upload_images_to_ecommerce'))
		{
			$data['images'] = array();
		}
		
		if (isset($item->tags))
		{
			$data['tags'] = $item->tags;
		}
		if ($item->supplier_id)
		{
			$data['vendor'] = $this->Supplier->get_name($item->supplier_id);
		}
		
		$options_for_variations = array();
		
		foreach($item_variations as $variation_id => $item_variation)
		{			
			$variation = array();
			
			$variation['taxable'] = $taxable;
			$variation['weight'] = $item->weight;
			$variation['weight_unit'] = $item->weight_unit ? $item->weight_unit : 'lb';
			
			if ($item_variation['item_number'])
			{
				$variation['sku'] = $item_variation['item_number'];
			}
			
			if ($this->config->item('online_price_tier'))
			{
				$this->load->model('Tier');
				$this->load->model('Item_location');
				$this->load->model('Item');
				$online_price = $this->Item->get_sale_price(array('item_id' => $item_id,'variation_id' => $item_variation['id'],'tier_id' => $this->config->item('online_price_tier')));	
			}
			else
			{
				$online_price = $item_variation['unit_price'] ? to_currency_no_money($item_variation['unit_price']) : '';
			}
			
			$variation['price'] = $online_price;
			
						
			if ($item_variation['promo_price'])
			{
				if (!$item_variation['start_date'] && !$item_variation['end_date'])
				{
					$variation['price'] = to_currency_no_money($item_variation['promo_price']);		
					$variation['compare_at_price'] = $online_price;
				}
			}
			
			
			$k=1;
			
			foreach($item_variation['attributes'] as $attribute)
			{
				$option_name = $attribute['attribute_name'];
				$option = $attribute['attribute_value_name'];
				$variation['option'.$k] = $option;		
				$variation['inventory_management'] = (isset($item->is_service) && $item->is_service) ? NULL : 'shopify';
				
				if (!isset($options_for_variations[$option_name]))
				{
					$options_for_variations[$option_name]['name'] = $option_name;
					$options_for_variations[$option_name]['values'] = array();
				}
				
				if (!in_array($option,$options_for_variations[$option_name]['values']))
				{
					$options_for_variations[$option_name]['values'][] = $option;
				}
				
				$k++;
			}
			
			$data['variants'][] = $variation;
			
		}
		
		$options = array();
		foreach($options_for_variations as $the_option)
		{
			$options[] = $the_option;
		}
		
		if (count($options) > 0)
		{
			$data['options'] = $options;		
		}
		
		$return = array();
		$return['product'] = $data;
		return $return;
	}
}
?>