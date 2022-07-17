<?php
class Customer extends Person
{	
	/*
	Determines if a given person_id is a customer
	*/
	function exists($person_id)
	{
		$this->db->from('customers');	
		$this->db->join('people', 'people.person_id = customers.person_id');
		$this->db->where('customers.person_id',$person_id);
		$query = $this->db->get();
		
		return ($query->num_rows()==1);
	}

	function account_number_exists($account_number)
	{
		$this->db->from('customers');	
		$this->db->where('account_number',$account_number);
		$query = $this->db->get();
		
		return ($query->num_rows()==1);
	}
	
	function customer_id_from_account_number($account_number)
	{
		$this->db->from('customers');	
		$this->db->where('account_number',$account_number);
		$query = $this->db->get();
		
		if ($query->num_rows()==1)
		{
			return $query->row()->person_id;
		}
		
		return false;
	}
	
	/*
	Returns all the customers
	*/
	function get_all($location_id = '',$deleted = 0,$limit=10000, $offset=0,$col='last_name',$order='asc')
	{
		if (!$deleted)
		{
			$deleted = 0;
		}
		
		$order_by = '';
		
		if (!$this->config->item('speed_up_search_queries'))
		{
			$order_by="ORDER BY ".$col." ". $order;
		}
		
		$location_where = '';
		
		if ($location_id)
		{
			$location_where = 'and location_id = '.$location_id;
		}
		
		$people=$this->db->dbprefix('people');
		$customers=$this->db->dbprefix('customers');
		$price_tiers=$this->db->dbprefix('price_tiers');
		$data=$this->db->query("SELECT *,${people}.person_id as pid 
						FROM ".$people."
						STRAIGHT_JOIN ".$customers." ON 										                       
						".$people.".person_id = ".$customers.".person_id
						LEFT JOIN ".$price_tiers." ON 										                       
						".$price_tiers.".id = ".$customers.".tier_id
						WHERE deleted =$deleted $location_where $order_by 
						LIMIT  ".$offset.",".$limit);		
						
		return $data;
	}
	
	function count_all($location_id = '',$deleted = 0)
	{
		if (!$deleted)
		{
			$deleted = 0;
		}
		
		if ($location_id)
		{
			$this->db->where('location_id',$location_id);
		}
		
		
		$this->db->from('customers');
		$this->db->where('deleted',$deleted);
		return $this->db->count_all_results();
	}
	
	function get_info_by_email($email)
	{
		$this->db->select('customers.person_id');
		$this->db->from('customers');	
		$this->db->join('people', 'people.person_id = customers.person_id');
		$this->db->where('people.email',$email);
		$query = $this->db->get();
		
		if($query->num_rows() >= 1)
		{
			return $this->get_info($query->row()->person_id);
		}
		
		return FALSE;
	}
	/*
	Gets information about a particular customer
	*/
	function get_info($customer_id,$can_cache = FALSE)
	{
		if ($can_cache)
		{
			static $cache  = array();
		
			if (isset($cache[$customer_id]))
			{
				return $cache[$customer_id];
			}
		}
		else
		{
			$cache = array();
		}
		$this->db->from('customers');	
		$this->db->join('people', 'people.person_id = customers.person_id');
		$this->db->where('customers.person_id',$customer_id);
		$query = $this->db->get();
		
		if($query->num_rows()==1)
		{
			$cache[$customer_id] = $query->row();
			return $cache[$customer_id];
		}
		else
		{
			//Get empty base parent object, as $customer_id is NOT an customer
			$person_obj=parent::get_info(-1);
			
			//Get all the fields from customer table
			$fields = array('always_sms_receipt','auto_email_receipt','customer_info_popup','id','person_id','account_number','override_default_tax','company_name','balance','credit_limit','points','disable_loyalty','current_spend_for_points','current_sales_for_discount','taxable','tax_certificate','cc_token','cc_ref_no','cc_preview','card_issuer','tier_id','deleted','tax_class_id','custom_field_1_value','custom_field_2_value','custom_field_3_value','custom_field_4_value','custom_field_5_value','custom_field_6_value','custom_field_7_value','custom_field_8_value','custom_field_9_value','custom_field_10_value','location_id','internal_notes');
			//append those fields to base parent object, we we have a complete empty object
			foreach ($fields as $field)
			{
				$person_obj->$field='';
			}
			
			$cache[$customer_id] = $person_obj;
			return $person_obj;
		}
	}
	
	/*
	Gets information about multiple customers
	*/
	function get_multiple_info($customer_ids)
	{
		$this->db->from('customers');
		$this->db->join('people', 'people.person_id = customers.person_id');		
		$this->db->where_in('customers.person_id',$customer_ids);
		$this->db->order_by("last_name", "asc");
		return $this->db->get();		
	}
	
	/*
	Inserts or updates a customer
	*/
	function save_customer(&$person_data, &$customer_data,$customer_id=false,$skip_webhook = false)
	{
		$new_customer_action = $customer_id == -1 || $customer_id === false;
		
		$success=false;
		//Run these queries as a transaction, we want to make sure we do all or nothing
		$this->db->trans_start();
		if(parent::save($person_data,$customer_id))
		{
			if ($customer_id && $this->exists($customer_id))
			{
				$cust_info = $this->get_info($customer_id);
				
				$current_balance = $cust_info->balance;
				
				//Insert store balance transaction when manually editing
				if (isset($customer_data['balance']) && $customer_data['balance'] != $current_balance)
				{
		 			$store_account_transaction = array(
		   		'customer_id'=>$customer_id,
		   		'sale_id'=>NULL,
					'comment'=>lang('common_manual_edit_of_balance'),
		      'transaction_amount'=>$customer_data['balance'] - $current_balance,
					'balance'=>$customer_data['balance'],
					'date' => date('Y-m-d H:i:s')
					);
					
					$this->db->insert('store_accounts',$store_account_transaction);
				}
			}
						
			if (!$customer_id or !$this->exists($customer_id))
			{
				$customer_data['person_id'] = $person_data['person_id'];
				$success = $this->db->insert('customers',$customer_data);
				if(!$success)
				{
					unset($customer_data['person_id']);
					unset($person_data['person_id']);
				}
				elseif($this->config->item('new_customer_web_hook'))
				{
					$run_webhook = TRUE;
				}
			}
			else
			{
				
				if ($this->config->item('edit_customer_web_hook'))
				{
					$run_webhook = TRUE;
				}
				
				if (!empty($customer_data))
				{
					$this->db->where('person_id', $customer_id);
					$success = $this->db->update('customers',$customer_data);
				}
				else
				{
					$success = TRUE;
				}
			}			
		}
		
		$this->db->trans_complete();
		
		if ($skip_webhook)
		{
			$run_webhook = FALSE;
		}
		
		if (isset($run_webhook) && $run_webhook)
		{
			$this->load->helper('webhook');
			
			if ($new_customer_action)
			{
				if ($this->config->item('new_customer_web_hook'))
				{
					do_webhook(array_merge($person_data,$customer_data),$this->config->item('new_customer_web_hook'));
				}
			}
			else
			{
				if ($this->config->item('edit_customer_web_hook'))
				{
					$customer_data['person_id'] = $customer_id;
					do_webhook(array_merge($person_data,$customer_data),$this->config->item('edit_customer_web_hook'));
				}
			}
		}
		return $success;
	}
	
	
	/*
	Deletes one customer
	*/
	function delete($customer_id)
	{
		$this->db->where('person_id', $customer_id);
		return $this->db->update('customers', array('deleted' => 1));
	}
	
	/*
	Deletes a list of customers
	*/
	function delete_list($customer_ids)
	{
		$this->db->where_in('person_id',$customer_ids);
		return $this->db->update('customers', array('deleted' => 1));
 	}
	
	
	/*
	undeletes one customer
	*/
	function undelete($customer_id)
	{
		$this->db->where('person_id', $customer_id);
		return $this->db->update('customers', array('deleted' => 0));
	}
	
	/*
	undeletes a list of customers
	*/
	function undelete_list($customer_ids)
	{
		$this->db->where_in('person_id',$customer_ids);
		return $this->db->update('customers', array('deleted' => 0));
 	}
	
	function check_duplicate($name,$email,$phone_number)
	{
		if (!$email)
		{
			//Set to an email no one would have
			$email = 'no-reply@mg.phppointofsale.com';
		}
		
		if(!$phone_number)
		{
			//Set to phone number no one would have
			$phone_number = '555-555-5555';
		}
		
		$this->db->from('customers');
		$this->db->join('people','customers.person_id=people.person_id');	
		$this->db->where('deleted',0);		
		$this->db->where("full_name = ".$this->db->escape($name).' or email='.$this->db->escape($email).' or phone_number='.$this->db->escape($phone_number));
		$query=$this->db->get();
		if($query->num_rows()>0)
		{
			return true;
		}
		
		return false;
	}
	
	function get_customer_search_suggestions($search,$deleted = 0,$limit=25)
	{
		
		if (!trim($search))
		{
			return array();
		}
		
		if (!$deleted)
		{
			$deleted = 0;
		}
		
		
		$suggestions = array();
		
			$current_location = $this->Employee->get_logged_in_employee_current_location_id();
			$this->db->from('customers');
			$this->db->join('people','customers.person_id=people.person_id');	
		
			$this->db->where("(first_name LIKE '".($this->config->item('customer_allow_partial_match') ? '%' : '').$this->db->escape_like_str($search)."%' or 
			last_name LIKE '".($this->config->item('customer_allow_partial_match') ? '%' : '').$this->db->escape_like_str($search)."%' or 
			full_name LIKE '".($this->config->item('customer_allow_partial_match') ? '%' : '').$this->db->escape_like_str($search)."%') and deleted=$deleted and (location_id IS NULL or location_id = $current_location)");			
		
			$this->db->limit($limit);	
			$by_name = $this->db->get();
			$temp_suggestions = array();
		
			foreach($by_name->result() as $row)
			{
				$name_label = $row->first_name.' '.$row->last_name.' ('.$row->person_id.($row->account_number ? ', '.$row->account_number : '').')';
				
				if ($row->phone_number)
				{
					$name_label.=' ('.$row->phone_number.')';
				}
				
				$data = array(
					'name' => $name_label,
					'email' => $row->email,
					'avatar' => $row->image_id ?  app_file_url($row->image_id) : base_url()."assets/img/user.png" 
					 );
				$temp_suggestions[$row->person_id] = $data;
			}
		
			$this->load->helper('array');
			uasort($temp_suggestions, 'sort_assoc_array_by_name');
			
			foreach($temp_suggestions as $key => $value)
			{
				$suggestions[]=array('value'=> $key, 'label' => $value['name'],'avatar'=>$value['avatar'],'subtitle'=>$value['email']);		
			}
		
			$this->db->from('customers');
			$this->db->join('people','customers.person_id=people.person_id');	
			$this->db->where('deleted',$deleted);
			$this->db->where("(location_id IS NULL or location_id = $current_location)");	
			$this->db->like("account_number",$search,$this->config->item('customer_allow_partial_match') ? 'both' : 'after');
			$this->db->limit($limit);
			$by_account_number = $this->db->get();
		
		
			$temp_suggestions = array();
		
			foreach($by_account_number->result() as $row)
			{
				$data = array(
						'name' => $row->account_number,
						'email' => $row->email,
						'avatar' => $row->image_id ?  app_file_url($row->image_id) : base_url()."assets/img/user.png" 
						);

				$temp_suggestions[$row->person_id] = $data;

			}
			
			uasort($temp_suggestions, 'sort_assoc_array_by_name');
			
			foreach($temp_suggestions as $key => $value)
			{
				$suggestions[]=array('value'=> $key, 'label' => $value['name'],'avatar'=>$value['avatar'],'subtitle'=>$value['email']);		
			}
			
			
			for($k=1;$k<=NUMBER_OF_PEOPLE_CUSTOM_FIELDS;$k++)
			{
				if ($this->get_custom_field($k)) 
				{
					$this->load->helper('date');
					if ($this->get_custom_field($k,'type') != 'date')
					{
						$this->db->select('custom_field_'.$k.'_value as custom_field, email,image_id, customers.person_id', false);						
					}
					else
					{
						$this->db->select('FROM_UNIXTIME(custom_field_'.$k.'_value, "'.get_mysql_date_format().'") as custom_field, email,image_id, customers.person_id', false);
					}
					$this->db->from('customers');
					$this->db->join('people','customers.person_id=people.person_id');	
					$this->db->where('deleted',$deleted);
					$this->db->where("(location_id IS NULL or location_id = $current_location)");
					if ($this->get_custom_field($k,'type') != 'date')
					{
						$this->db->like("custom_field_${k}_value",$search,$this->config->item('customer_allow_partial_match') ? 'both' : 'after');
					}
					else
					{
						$this->db->where("custom_field_${k}_value IS NOT NULL and custom_field_${k}_value != 0 and FROM_UNIXTIME(custom_field_${k}_value, '%Y-%m-%d') = ".$this->db->escape(date('Y-m-d', strtotime($search))), NULL, false);					
					}
					$this->db->limit($limit);
					$by_custom_field = $this->db->get();
		
					$temp_suggestions = array();
		
					foreach($by_custom_field->result() as $row)
					{
						$data = array(
								'name' => $row->custom_field,
								'email' => $row->email,
								'avatar' => $row->image_id ?  app_file_url($row->image_id) : base_url()."assets/img/user.png" 
								);

						$temp_suggestions[$row->person_id] = $data;

					}
			
					uasort($temp_suggestions, 'sort_assoc_array_by_name');
			
					foreach($temp_suggestions as $key => $value)
					{
						$suggestions[]=array('value'=> $key, 'label' => $value['name'],'avatar'=>$value['avatar'],'subtitle'=>$value['email']);		
					}
				}			
			}
		
			
			$this->db->from('customers');
			$this->db->join('people','customers.person_id=people.person_id');	
			$this->db->where('deleted',$deleted);		
			$this->db->like("email",$search,$this->config->item('customer_allow_partial_match') ? 'both' : 'after');
			$this->db->limit($limit);
			$this->db->where("(location_id IS NULL or location_id = $current_location)");
			$by_email = $this->db->get();
		
			$temp_suggestions = array();
		
			foreach($by_email->result() as $row)
			{
				$data = array(
						'name' => $row->first_name.'&nbsp;'.$row->last_name,
						'email' => $row->email,
						'avatar' => $row->image_id ?  app_file_url($row->image_id) : base_url()."assets/img/user.png" 
						);

				$temp_suggestions[$row->person_id] = $data;
			}
		
		
			uasort($temp_suggestions, 'sort_assoc_array_by_name');
			
			foreach($temp_suggestions as $key => $value)
			{
				$suggestions[]=array('value'=> $key, 'label' => $value['email'],'avatar'=>$value['avatar'],'subtitle'=>$value['email']);
			}
			
			$this->db->from('customers');
			$this->db->join('people','customers.person_id=people.person_id');	
			$this->db->where('deleted',$deleted);
			$this->db->where("(location_id IS NULL or location_id = $current_location)");		
			$this->db->like("phone_number",$search,$this->config->item('customer_allow_partial_match') ? 'both' : 'after');
			$this->db->limit($limit);
			$by_phone_number = $this->db->get();
		
		
			$temp_suggestions = array();
		
			foreach($by_phone_number->result() as $row)
			{
				$data = array(
						'name' => $row->phone_number,
						'email' => $row->email,
						'avatar' => $row->image_id ?  app_file_url($row->image_id) : base_url()."assets/img/user.png" 
						);

				$temp_suggestions[$row->person_id] = $data;
			}
		
			uasort($temp_suggestions, 'sort_assoc_array_by_name');
			
			foreach($temp_suggestions as $key => $value)
			{
				$suggestions[]=array('value'=> $key, 'label' => $value['name'],'avatar'=>$value['avatar'],'subtitle'=>$value['email']);
			}
		
			$this->db->from('customers');
			$this->db->join('people','customers.person_id=people.person_id');
			$this->db->where("(location_id IS NULL or location_id = $current_location)");	
			$this->db->where('deleted',$deleted);		
			$this->db->like("company_name",$search,$this->config->item('customer_allow_partial_match') ? 'both' : 'after');
			$this->db->limit($limit);
			$by_company_name = $this->db->get();
		
			$temp_suggestions = array();
		
			foreach($by_company_name->result() as $row)
			{
				$data = array(
						'name' => $row->company_name,
						'email' => $row->email,
						'avatar' => $row->image_id ?  app_file_url($row->image_id) : base_url()."assets/img/user.png" 
						);

				$temp_suggestions[$row->person_id] = $data;
			}
		
			uasort($temp_suggestions, 'sort_assoc_array_by_name');
		
			foreach($temp_suggestions as $key => $value)
			{
				$suggestions[]=array('value'=> $key, 'label' => $value['name'],'avatar'=>$value['avatar'],'subtitle'=>$value['email']);
			}
		
		//Cleanup blank entries
		for($k=count($suggestions)-1;$k>=0;$k--)
		{
			if (!$suggestions[$k]['label'])
			{
				unset($suggestions[$k]);
			}
		}
		
		//Probably not needed; but doesn't hurt
		$suggestions = array_values($suggestions);
		
		
		//only return $limit suggestions
		$suggestions = array_map("unserialize", array_unique(array_map("serialize", $suggestions)));
		if(count($suggestions) > $limit)
		{
			$suggestions = array_slice($suggestions, 0,$limit);
		}
		return $suggestions;

	}
	/*
	Preform a search on customers
	
	
	
	/*
	Preform a search on customers
	*/
	function search($search, $location_id = '',$deleted = 0,$limit=20,$offset=0,$column='last_name',$orderby='asc',$search_field = NULL)
	{
		if (!$deleted)
		{
			$deleted = 0;
		}
		
	  //The queries are done as 2 unions to speed up searches to use indexes.
	 	//When doing OR WHERE across 2 tables; performance is not good
		$this->db->select('*,people.person_id as pid');
		$this->db->from('customers');
		$this->db->join('people','customers.person_id=people.person_id');	
		$this->db->join('price_tiers','customers.tier_id=price_tiers.id','left');	
		
		if ($search)
		{
				if ($search_field)
				{
					$this->db->where("$search_field LIKE '".($this->config->item('customer_allow_partial_match') ? '%' : '').$this->db->escape_like_str($search)."%' and deleted=$deleted");		
				}
				else
				{
					$this->db->where("(first_name LIKE '".($this->config->item('customer_allow_partial_match') ? '%' : '').$this->db->escape_like_str($search)."%' or 
					last_name LIKE '".($this->config->item('customer_allow_partial_match') ? '%' : '').$this->db->escape_like_str($search)."%' or 
					email LIKE '".($this->config->item('customer_allow_partial_match') ? '%' : '').$this->db->escape_like_str($search)."%' or 
					phone_number LIKE '".($this->config->item('customer_allow_partial_match') ? '%' : '').$this->db->escape_like_str($search)."%' or 
					full_name LIKE '".($this->config->item('customer_allow_partial_match') ? '%' : '').$this->db->escape_like_str($search)."%') and deleted=$deleted");		
				}
		}
		else
		{
			$this->db->where('customers.deleted',$deleted);
		}	
			
		if ($location_id)
		{
			$this->db->where('location_id',$location_id);
		}
			
		$people_search = $this->db->get_compiled_select();

		$this->db->select('*,people.person_id as pid');
		$this->db->from('customers');
		$this->db->join('people','customers.person_id=people.person_id');	
		
		if ($location_id)
		{
			$this->db->where('location_id',$location_id);
		}
		
		$this->db->join('price_tiers','customers.tier_id=price_tiers.id','left');	
		if ($search_field !== NULL)
		{
			$this->db->where('1=2');
		}
		elseif ($search)
		{
				$custom_fields = array();
				for($k=1;$k<=NUMBER_OF_PEOPLE_CUSTOM_FIELDS;$k++)
				{					
					if ($this->get_custom_field($k) !== FALSE)
					{
						if ($this->get_custom_field($k,'type') != 'date')
						{
							$custom_fields[$k]="custom_field_${k}_value LIKE '".($this->config->item('customer_allow_partial_match') ? '%' : '').$this->db->escape_like_str($search)."%'";
						}
						else
						{							
							$custom_fields[$k]= "custom_field_${k}_value IS NOT NULL and custom_field_${k}_value != 0 and FROM_UNIXTIME(custom_field_${k}_value, '%Y-%m-%d') = ".$this->db->escape(date('Y-m-d', strtotime($search)));					
						}
			
					}	
				}
	
				if (!empty($custom_fields))
				{				
					$custom_fields = implode(' or ',$custom_fields);
				}
				else
				{
					$custom_fields='1=2';
				}
		
				$this->db->where("($custom_fields or account_number LIKE '".($this->config->item('customer_allow_partial_match') ? '%' : '').$this->db->escape_like_str($search)."%' or 
				company_name LIKE '".($this->config->item('customer_allow_partial_match') ? '%' : '').$this->db->escape_like_str($search)."%') and deleted=$deleted");
			
		
		}
		else
		{
			$this->db->where('customers.deleted',$deleted);
		}	

		$customer_search = $this->db->get_compiled_select();

		$order_by = '';
		if (!$this->config->item('speed_up_search_queries'))
		{
			$order_by = " ORDER BY $column $orderby ";
		}			

		return $this->db->query($people_search." UNION ".$customer_search." $order_by LIMIT $limit OFFSET $offset");	
	}
	
	function search_count_all($search, $location_id = '',$deleted = 0,$limit=10000,$search_field = NULL)
	{
		if (!$deleted)
		{
			$deleted = 0;
		}
		
		//The queries are done as 2 unions to speed up searches to use indexes.
	 //When doing OR WHERE across 2 tables; performance is not good
		$this->db->from('customers');
		$this->db->join('people','customers.person_id=people.person_id');	


		if ($search)
		{
				if ($search_field)
				{
					$this->db->where("$search_field LIKE '".($this->config->item('customer_allow_partial_match') ? '%' : '').$this->db->escape_like_str($search)."%' and deleted=$deleted");		
				}
				else
				{
					$this->db->where("(first_name LIKE '".($this->config->item('customer_allow_partial_match') ? '%' : '').$this->db->escape_like_str($search)."%' or 
					last_name LIKE '".($this->config->item('customer_allow_partial_match') ? '%' : '').$this->db->escape_like_str($search)."%' or 
					email LIKE '".($this->config->item('customer_allow_partial_match') ? '%' : '').$this->db->escape_like_str($search)."%' or 
					phone_number LIKE '".($this->config->item('customer_allow_partial_match') ? '%' : '').$this->db->escape_like_str($search)."%' or 
					full_name LIKE '".($this->config->item('customer_allow_partial_match') ? '%' : '').$this->db->escape_like_str($search)."%') and deleted=$deleted");		
				}
		}
		else
		{
			$this->db->where('deleted',$deleted);
		}	
		
		if ($location_id)
		{
			$this->db->where('location_id',$location_id);
		}
		
			
		$people_search = $this->db->get_compiled_select();

		$this->db->from('customers');
		$this->db->join('people','customers.person_id=people.person_id');	
		
		if ($location_id)
		{
			$this->db->where('location_id',$location_id);
		}
		
		if ($search_field !== NULL)
		{
			$this->db->where('1=2');
		}
		elseif ($search)
		{
	
			$custom_fields = array();
			for($k=1;$k<=NUMBER_OF_PEOPLE_CUSTOM_FIELDS;$k++)
			{					
				if ($this->get_custom_field($k) !== FALSE)
				{
					if ($this->get_custom_field($k,'type') != 'date')
					{
						$custom_fields[$k]="custom_field_${k}_value LIKE '".($this->config->item('customer_allow_partial_match') ? '%' : '').$this->db->escape_like_str($search)."%'";
					}
					else
					{							
						$custom_fields[$k]= "custom_field_${k}_value IS NOT NULL and custom_field_${k}_value != 0 and FROM_UNIXTIME(custom_field_${k}_value, '%Y-%m-%d') = ".$this->db->escape(date('Y-m-d', strtotime($search)));					
					}
		
				}	
			}

			if (!empty($custom_fields))
			{				
				$custom_fields = implode(' or ',$custom_fields);
			}
			else
			{
				$custom_fields='1=2';
			}
	
				$this->db->where("($custom_fields or account_number LIKE '".($this->config->item('customer_allow_partial_match') ? '%' : '').$this->db->escape_like_str($search)."%' or 
				company_name LIKE '".($this->config->item('customer_allow_partial_match') ? '%' : '').$this->db->escape_like_str($search)."%') and deleted=$deleted");		
		}
		else
		{
			$this->db->where('deleted',$deleted);
		}	

		$customer_search = $this->db->get_compiled_select();

		$result = $this->db->query($people_search." UNION ".$customer_search);			
		return $result->num_rows();
	}
	
	function cleanup()
	{
		$customer_data = array('account_number' => null);
		$this->db->where('deleted', 1);
		$this->db->update('customers',$customer_data);
		
		$people_table = $this->db->dbprefix('people');
		$app_files_table = $this->db->dbprefix('app_files');
		$customers_table = $this->db->dbprefix('customers');
		$this->db->query('SET FOREIGN_KEY_CHECKS = 0');
		$this->db->query("UPDATE $people_table SET image_id = NULL WHERE person_id IN (SELECT person_id FROM $customers_table WHERE deleted = 1)");
		$this->db->query('SET FOREIGN_KEY_CHECKS = 1');
		return TRUE;
		
	}
	
	function get_displayable_columns()
	{
		$columns = array(
			'person_id' => 											array('sort_column' => 'pid', 'label' => lang('common_person_id')),
			'full_name' => 											array('sort_column' => 'full_name','label' => lang('common_name'),'data_function' => 'customer_name_data_function','format_function' => 'customer_name_formatter','html' => TRUE),
			'first_name' => 										array('sort_column' => 'first_name','label' => lang('common_first_name'),'data_function' => 'customer_name_data_function','format_function' => 'customer_name_formatter','html' => TRUE),
			'last_name' => 											array('sort_column' => 'last_name','label' => lang('common_last_name'),'data_function' => 'customer_name_data_function','format_function' => 'customer_name_formatter','html' => TRUE),
			'company_name' => 									array('sort_column' => 'company_name','label' => lang('common_company')),
			'account_number' => 								array('sort_column' => 'account_number','label' => lang('customers_account_number')),
			'email' => 													array('sort_column' => 'email','label' => lang('common_email'),'format_function' => 'email_formatter','html' => TRUE),
			'phone_number' => 									array('sort_column' => 'phone_number','label' => lang('common_phone_number'),'format_function' => 'tel','html' => TRUE),
			'comments' => 											array('sort_column' => 'comments','label' => lang('common_comments')),
			'balance' => 												array('sort_column' => 'balance','label' => lang('common_balance'),'data_function' => 'customer_balance_data','format_function' => 'customer_balance_formatter','html' => TRUE),
			'credit_limit' => 									array('sort_column' => 'credit_limit','label' => lang('common_credit_limit'),'format_function' => 'to_currency'),
			'disable_loyalty' => 								array('sort_column' => 'disable_loyalty','label' => lang('common_disable_loyalty'),'format_function' => 'boolean_as_string'),
			'points' => 												array('sort_column' => 'points','label' => lang('common_points'),'format_function' => 'to_quantity'),
			'current_spend_for_points' => 			array('sort_column' => 'current_spend_for_points','label' => lang('customers_amount_to_spend_for_next_point'),'format_function' => 'amount_to_spend_for_next_point_formatter', 'data_function' => 'amount_to_spend_for_next_point_data'),
			'current_sales_for_discount' => 		array('sort_column' => 'current_sales_for_discount','label' => lang('common_sales_until_discount'),'format_function' => 'sales_until_discount_formatter', 'data_function' => 'sales_until_discount_data'),
			'address_1' => 											array('sort_column' => 'address_1','label' => lang('common_address_1')),
			'address_2' => 											array('sort_column' => 'address_2','label' => lang('common_address_2')),
			'city' => 													array('sort_column' => 'city','label' => lang('common_city')),
			'state' => 													array('sort_column' => 'state','label' => lang('common_state')),
			'zip' => 														array('sort_column' => 'zip','label' => lang('common_zip')),
			'country' => 												array('sort_column' => 'country','label' => lang('common_country')),
			'override_default_tax' => 					array('sort_column' => 'override_default_tax','label' => lang('customers_override_default_tax_for_sale'),'format_function' => 'boolean_as_string'),			
			'taxable' => 												array('sort_column' => 'taxable','label' => lang('common_taxable'),'format_function' => 'boolean_as_string'),			
			'name' => 													array('sort_column' => 'city','label' => lang('common_tier_name')),
			'internal_notes' => 								array('sort_column' => 'internal_notes','label' => lang('common_internal_notes'),'format_function' => 'nl2br','html' => TRUE),
			'auto_email_receipt' =>							array('sort_column' => 'auto_email_receipt','label' => lang('customers_auto_email_receipt'),'format_function' => 'boolean_as_string'),			
			'always_sms_receipt' =>							array('sort_column' => 'always_sms_receipt','label' => lang('customers_always_sms_receipt'),'format_function' => 'boolean_as_string'),			
		);
		
		for($k=1;$k<=NUMBER_OF_PEOPLE_CUSTOM_FIELDS;$k++)
		{
			if($this->Customer->get_custom_field($k) !== false)
			{
				$field = array();
				$field['sort_column'] ="custom_field_${k}_value";
				$field['label']= $this->Customer->get_custom_field($k);
			
				if ($this->Customer->get_custom_field($k,'type') == 'checkbox')
				{
					$format_function = 'boolean_as_string';
				}
				elseif($this->Customer->get_custom_field($k,'type') == 'date')
				{
					$format_function = 'date_as_display_date';				
				}
				elseif($this->get_custom_field($k,'type') == 'email')
				{
					$this->load->helper('url');
					$format_function = 'mailto';					
					$field['html'] = TRUE;
				}
				elseif($this->get_custom_field($k,'type') == 'url')
				{
					$this->load->helper('url');
					$format_function = 'anchor_or_blank';					
					$field['html'] = TRUE;
				}
				elseif($this->get_custom_field($k,'type') == 'phone')
				{
					$this->load->helper('url');
					$format_function = 'tel';					
					$field['html'] = TRUE;
				}
				elseif($this->get_custom_field($k,'type') == 'image')
				{
					$this->load->helper('url');
					$format_function = 'file_id_to_image_thumb';					
					$field['html'] = TRUE;
				}
				elseif($this->get_custom_field($k,'type') == 'file')
				{
					$this->load->helper('url');
					$format_function = 'file_id_to_download_link';					
					$field['html'] = TRUE;
				}
				else
				{
					$format_function = 'strsame';
				}
				$field['format_function'] = $format_function;
				$columns["custom_field_${k}_value"] = $field;
			}
		}
		
		return $columns;
		
	}
	
	function get_default_columns()
	{
		return array('person_id','full_name','email','phone_number');
	}
	
	function get_custom_field($number,$key="name")
	{
		static $config_data;
		
		if (!$config_data)
		{
			$config_data = unserialize($this->config->item('customer_custom_field_prefs'));
		}
		
		return isset($config_data["custom_field_${number}_${key}"]) && $config_data["custom_field_${number}_${key}"] ? $config_data["custom_field_${number}_${key}"] : FALSE;
	}
	
	function does_customer_have_address($customer_id)
	{
		if($customer_id)
		{
			$cust_info=$this->get_info($customer_id);
			$required_address_fields = array($cust_info->address_1, $cust_info->city, $cust_info->state, $cust_info->zip);
			
			foreach ($required_address_fields as $field) {
				if(!isset($field) || empty($field))
				{
					return false;
				}
			}
			
			return true;
		}
		
		return FALSE;
	}
	
	function is_over_credit_limit($customer_id,$balance_to_add=0)
	{
		if($customer_id)
		{
			$cust_info=$this->get_info($customer_id);
			return $cust_info->credit_limit !== NULL && $cust_info->balance + $balance_to_add > $cust_info->credit_limit;
		}
		
		return FALSE;
	}
		
	function add_new_series($series_data)
	{
		$this->db->insert('customers_series',$series_data);
		return $this->db->insert_id();
	}
	
	function delete_series($series_id)
	{
		$this->db->where('series_id',$series_id);
		$this->db->update('sales_items',array('series_id' => NULL));
		
		$this->db->where('id',$series_id);
		$this->db->delete('customers_series');
	}
	
	function get_series_info($series_id)
	{
		$this->db->from('customers_series');
		$this->db->where('id',$series_id);
		return $this->db->get()->row();
	}
	
	function delete_series_by_sale_id($sale_id)
	{
		$this->db->where('sale_id',$sale_id);
		$this->db->update('sales_items',array('series_id' => NULL));
		
		$this->db->where('sale_id',$sale_id);
		$this->db->delete('customers_series');
	}
	
	function update_series($series_id,$series_data)
	{
		$series_info = $this->get_series_info($series_id);
		$quantity_before = $series_info->quantity_remaining;
		
		if (isset($series_data['quantity_remaining']) && $series_data['quantity_remaining']!=$quantity_before)
		{
			$log_data = array('series_id' => $series_id,'date' => date('Y-m-d H:i:s'),'quantity_used' => $series_data['quantity_remaining']-$quantity_before);
			$this->db->insert('customers_series_log',$log_data);
		}
		
		$this->db->where('id',$series_id);
		return $this->db->update('customers_series',$series_data);
	}
	
	function get_series_for_customer($customer_id)
	{
		$this->db->select('items.name,customers_series.id,people.first_name,people.last_name,sales.sale_time,customers_series.quantity_remaining,customers_series.expire_date');
		$this->db->from('customers_series');
		$this->db->join('items', 'items.item_id = customers_series.item_id');
		$this->db->join('sales', 'sales.sale_id = customers_series.sale_id');
		$this->db->join('people', 'people.person_id = customers_series.customer_id');
		$this->db->where('customers_series.customer_id', $customer_id);
		$this->db->order_by('customers_series.expire_date');
		
		return $this->db->get()->result_array();
	}

	// Custom Function Start
	function get_info_by_name($name)
	{
		$people=$this->db->dbprefix('people');
		$customers=$this->db->dbprefix('customers');
		$data=$this->db->query("SELECT * FROM ".$customers."
						STRAIGHT_JOIN ".$people." ON 										                       
						".$people.".person_id = ".$customers.".person_id
						WHERE ".$people.".full_name = '$name'");		
						
		return $data->row();
	}

	// Custom Function Ends
	
	function merge($customers,$customer_to_merge)
	{
		$new_balance = 0;
		$points = 0;
		foreach($customers as $customer_id)
		{
			$cust_info = $this->get_info($customer_id);
			$new_balance+=$cust_info->balance;
			$points+=$cust_info->points;
		}
		
		$cust_info = $this->get_info($customer_to_merge);
		$new_balance+=$cust_info->balance;			
		$points+=$cust_info->points;			
		
		if (count($customers) > 0)
		{
			
			$this->db->trans_start();
		
			$this->db->where_in('customer_id',$customers);
			$this->db->update('customers_taxes',array('customer_id' =>$customer_to_merge));
		
			$this->db->where_in('customer_id',$customers);
			$this->db->update('giftcards',array('customer_id' =>$customer_to_merge));
		
			$this->db->where_in('customer_id',$customers);
			$this->db->update('sales',array('customer_id' =>$customer_to_merge));
		
			$this->db->where_in('customer_id',$customers);
			$this->db->update('store_accounts',array('customer_id' =>$customer_to_merge));
		
		
			$this->db->where_in('customer_id',$customers);
			$this->db->update('customers_series',array('customer_id' =>$customer_to_merge));
					
			$this->db->where_in('person_id',$customers);
			$this->db->update('customers',array('deleted' =>1));

			$this->db->where_in('person_id',$customer_to_merge);
			$this->db->update('customers',array('balance' =>$new_balance));

			$this->db->where_in('person_id',$customer_to_merge);
			$this->db->update('customers',array('points' =>$points));
		
			$this->db->trans_complete();
		}
		return TRUE;
	}
	
}
?>
