<?php
class Supplier extends Person
{	
	/*
	Determines if a given person_id is a customer
	*/
	function exists($person_id)
	{
		$this->load->helper('text');
		if (does_contain_only_digits($person_id))
		{
			$this->db->from('suppliers');	
			$this->db->join('people', 'people.person_id = suppliers.person_id');
			$this->db->where('suppliers.person_id',$person_id);
			$query = $this->db->get();
			return ($query->num_rows()==1);
		}
		return FALSE;
	}
	
	/*
	Returns all the suppliers
	*/
	function get_all($deleted = 0,$limit=10000, $offset=0,$col='company_name',$order='asc')
	{
		if (!$deleted)
		{
			$deleted = 0;
		}
		
		$order_by = '';
		if (!$this->config->item('speed_up_search_queries'))
		{
			$order_by = "ORDER BY ".$col." ".$order;
		}
		
		$people=$this->db->dbprefix('people');
		$suppliers=$this->db->dbprefix('suppliers');
		$data=$this->db->query("SELECT *,${people}.person_id as pid 
						FROM ".$people."
						JOIN ".$suppliers." ON 										                       
						".$people.".person_id = ".$suppliers.".person_id
						WHERE deleted =$deleted $order_by
						LIMIT  ".$offset.",".$limit);		
						
		return $data;	
	}
	
	function get_name($supplier_id)
	{
		$supplier_info = $this->get_info($supplier_id);
		
		if ($supplier_info->company_name)
		{
			return $supplier_info->company_name;
		}
		
		if ($supplier_info->first_name)
		{
			return $supplier_info->first_name. ' '.$supplier_info->last_name;			
		}
		return lang('common_none');
	}
	
	
	function account_number_exists($account_number)
	{
		$this->db->from('suppliers');	
		$this->db->where('account_number',$account_number);
		$query = $this->db->get();
		
		return ($query->num_rows()==1);
	}
	
	function supplier_id_from_account_number($account_number)
	{
		$this->db->from('suppliers');	
		$this->db->where('account_number',$account_number);
		$query = $this->db->get();
		
		if ($query->num_rows()==1)
		{
			return $query->row()->person_id;
		}
		
		return false;
	}
	
	function count_all($deleted=0)
	{
		if (!$deleted)
		{
			$deleted = 0;
		}
		
		$this->db->from('suppliers');
		$this->db->where('deleted',$deleted);
		return $this->db->count_all_results();
	}
	
	/*
	Gets information about a particular supplier
	*/
	function get_info($supplier_id, $can_cache = FALSE)
	{
		if ($can_cache)
		{
			static $cache = array();
		
			if (isset($cache[$supplier_id]))
			{
				return $cache[$supplier_id];
			}
		}
		else
		{
			$cache = array();
		}
				
		$this->db->from('suppliers');	
		$this->db->join('people', 'people.person_id = suppliers.person_id');
		$this->db->where('suppliers.person_id',$supplier_id);
		$query = $this->db->get();
		
		if($query->num_rows()==1)
		{
			$cache[$supplier_id] = $query->row();
			return $cache[$supplier_id];
		}
		else
		{
			//Get empty base parent object, as $supplier_id is NOT an supplier
			$person_obj=parent::get_info(-1);
			
			//Get all the fields from supplier table
			$fields = array('id','person_id','company_name','account_number','override_default_tax','balance','deleted','tax_class_id','custom_field_1_value','custom_field_2_value','custom_field_3_value','custom_field_4_value','custom_field_5_value','custom_field_6_value','custom_field_7_value','custom_field_8_value','custom_field_9_value','custom_field_10_value', 'internal_notes');
			
			//append those fields to base parent object, we we have a complete empty object
			foreach ($fields as $field)
			{
				$person_obj->$field='';
			}
			
			return $person_obj;
		}
	}
	
	/*
	Gets information about multiple suppliers
	*/
	function get_multiple_info($suppliers_ids)
	{
		$this->db->from('suppliers');
		$this->db->join('people', 'people.person_id = suppliers.person_id');		
		$this->db->where_in('suppliers.person_id',$suppliers_ids);
		$this->db->order_by("last_name", "asc");
		return $this->db->get();		
	}
	
	/*
	Inserts or updates a suppliers
	*/
	function save_supplier(&$person_data, &$supplier_data,$supplier_id=false)
	{
		$success=false;
		//Run these queries as a transaction, we want to make sure we do all or nothing
		$this->db->trans_start();
		if(parent::save($person_data,$supplier_id))
		{
			if ($supplier_id && $this->exists($supplier_id))
			{
				$supp_info = $this->get_info($supplier_id);
				
				$current_balance = $supp_info->balance;
				
				//Insert store balance transaction when manually editing
				if (isset($supplier_data['balance']) && $supplier_data['balance'] != $current_balance)
				{
		 			$store_account_transaction = array(
		   		'supplier_id'=>$supplier_id,
		   		'receiving_id'=>NULL,
					'comment'=>lang('common_manual_edit_of_balance'),
		      'transaction_amount'=>$supplier_data['balance'] - $current_balance,
					'balance'=>$supplier_data['balance'],
					'date' => date('Y-m-d H:i:s')
					);
					
					$this->db->insert('supplier_store_accounts',$store_account_transaction);
				}
			}
						
			if (!$supplier_id or !$this->exists($supplier_id))
			{
				$supplier_data['person_id'] = $person_data['person_id'];
				$success = $this->db->insert('suppliers',$supplier_data);
				if(!$success)
				{
					unset($supplier_data['person_id']);
					unset($person_data['person_id']);
				}		
			}
			else
			{
				
				if (!empty($supplier_data))
				{
					$this->db->where('person_id', $supplier_id);
					$success = $this->db->update('suppliers',$supplier_data);
				}
				else
				{
					$success = TRUE;
				}
			}			
		}
		
		$this->db->trans_complete();
		return $success;
	}
	
	/*
	Deletes one supplier
	*/
	function delete($supplier_id)
	{
		$this->db->where('person_id', $supplier_id);
		return $this->db->update('suppliers', array('deleted' => 1));
	}
	
	/*
	Deletes a list of suppliers
	*/
	function delete_list($supplier_ids)
	{
		$this->db->where_in('person_id',$supplier_ids);
		return $this->db->update('suppliers', array('deleted' => 1));
 	}
	
	/*
	undeletes one supplier
	*/
	function undelete($supplier_id)
	{
		$this->db->where('person_id', $supplier_id);
		return $this->db->update('suppliers', array('deleted' => 0));
	}
	
	/*
	undeletes a list of suppliers
	*/
	function undelete_list($supplier_ids)
	{
		$this->db->where_in('person_id',$supplier_ids);
		return $this->db->update('suppliers', array('deleted' => 0));
 	}
	

	/*
	Get search suggestions to find suppliers
	*/
	function get_supplier_search_suggestions($search,$deleted = 0,$limit=25)
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
		
			$this->db->select("company_name,email,image_id,suppliers.person_id", false);
			$this->db->from('suppliers');
			$this->db->join('people','suppliers.person_id=people.person_id');	
			$this->db->where('deleted', $deleted);
			$this->db->like("company_name",$search,!$this->config->item('speed_up_search_queries') ? 'both' : 'after');
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
		
			$this->load->helper('array');
			uasort($temp_suggestions, 'sort_assoc_array_by_name');
			foreach($temp_suggestions as $key => $value)
			{
				$suggestions[]=array('value'=> $key, 'label' => $value['name'],'avatar'=>$value['avatar'],'subtitle'=>$value['email']);
			}

			$this->db->select("first_name,last_name,email,image_id,suppliers.person_id", false);
			$this->db->from('suppliers');
			$this->db->join('people','suppliers.person_id=people.person_id');	
		
			$this->db->where("(first_name LIKE '".(!$this->config->item('speed_up_search_queries') ? '%' : '').$this->db->escape_like_str($search)."%' or 
			last_name LIKE '".(!$this->config->item('speed_up_search_queries') ? '%' : '').$this->db->escape_like_str($search)."%' or 
			full_name LIKE '".(!$this->config->item('speed_up_search_queries') ? '%' : '').$this->db->escape_like_str($search)."%') and deleted=$deleted");			

			$this->db->limit($limit);	
		
			$by_name = $this->db->get();
				
			$temp_suggestions = array();
			foreach($by_name->result() as $row)
			{
				$data = array(
						'name' => $row->first_name.' '.$row->last_name,
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
		
		
			$this->db->select("first_name, last_name, email,image_id,suppliers.person_id", false);
			$this->db->from('suppliers');
			$this->db->join('people','suppliers.person_id=people.person_id');	
			$this->db->where('deleted', $deleted);
			$this->db->like('email', $search,!$this->config->item('speed_up_search_queries') ? 'both' : 'after');			
			$this->db->limit($limit);
		
			$by_email = $this->db->get();

			$temp_suggestions = array();
			foreach($by_email->result() as $row)
			{
				$data = array(
						'name' => $row->first_name.' '.$row->last_name,
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

			$this->db->select("phone_number,email,image_id,suppliers.person_id", false);
			$this->db->from('suppliers');
			$this->db->join('people','suppliers.person_id=people.person_id');	
			$this->db->where('deleted', $deleted);
			$this->db->like('phone_number', $search,!$this->config->item('speed_up_search_queries') ? 'both' : 'after');			
			$this->db->limit($limit);	
			
			$by_phone = $this->db->get();
		
			$temp_suggestions = array();
			foreach($by_phone->result() as $row)
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
		
			$this->db->select("account_number,email,image_id,suppliers.person_id", false);
			$this->db->from('suppliers');
			$this->db->join('people','suppliers.person_id=people.person_id');	
			$this->db->where('deleted', $deleted);
			$this->db->like('account_number', $search,!$this->config->item('speed_up_search_queries') ? 'both' : 'after');			
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
						$this->db->select('custom_field_'.$k.'_value as custom_field, email,image_id, suppliers.person_id', false);						
					}
					else
					{
						$this->db->select('FROM_UNIXTIME(custom_field_'.$k.'_value, "'.get_mysql_date_format().'") as custom_field, email,image_id, suppliers.person_id', false);
					}
					$this->db->from('suppliers');
					$this->db->join('people','suppliers.person_id=people.person_id');	
					$this->db->where('deleted',$deleted);
				
					if ($this->get_custom_field($k,'type') != 'date')
					{
						$this->db->like("custom_field_${k}_value",$search,!$this->config->item('speed_up_search_queries') ? 'both' : 'after');
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
			
		
		//only return $limit suggestions
		$suggestions = array_map("unserialize", array_unique(array_map("serialize", $suggestions)));
		if(count($suggestions) > $limit)
		{
			$suggestions = array_slice($suggestions, 0,$limit);
		}
		return $suggestions;
	
	}

	/*
	Preform a search on suppliers
	*/
	function search($search, $deleted = 0,$limit=20,$offset=0,$column='last_name',$orderby='asc',$search_field = NULL)
	{
		if (!$deleted)
		{
			$deleted = 0;
		}
		//The queries are done as 2 unions to speed up searches to use indexes.
	 //When doing OR WHERE across 2 tables; performance is not good
	 $this->db->select('*,people.person_id as pid');
		$this->db->from('suppliers');
		$this->db->join('people','suppliers.person_id=people.person_id');	

		if ($search)
		{
				if ($search_field)
				{
					$this->db->where("$search_field LIKE '".(!$this->config->item('speed_up_search_queries') ? '%' : '').$this->db->escape_like_str($search)."%' and deleted=$deleted");		
				}
				else
				{
					$this->db->where("(first_name LIKE '".(!$this->config->item('speed_up_search_queries') ? '%' : '').$this->db->escape_like_str($search)."%' or 
					last_name LIKE '".(!$this->config->item('speed_up_search_queries') ? '%' : '').$this->db->escape_like_str($search)."%' or 
					email LIKE '".(!$this->config->item('speed_up_search_queries') ? '%' : '').$this->db->escape_like_str($search)."%' or 
					phone_number LIKE '".(!$this->config->item('speed_up_search_queries') ? '%' : '').$this->db->escape_like_str($search)."%' or 
					account_number LIKE '".(!$this->config->item('speed_up_search_queries') ? '%' : '').$this->db->escape_like_str($search)."%' or 
					company_name LIKE '".(!$this->config->item('speed_up_search_queries') ? '%' : '').$this->db->escape_like_str($search)."%' or
					full_name LIKE '".(!$this->config->item('speed_up_search_queries') ? '%' : '').$this->db->escape_like_str($search)."%') and deleted=$deleted");		
				}
		}
		else
		{
			$this->db->where('deleted',$deleted);
		}	
			
		$people_search = $this->db->get_compiled_select();
		 $this->db->select('*,people.person_id as pid');
		$this->db->from('suppliers');
		$this->db->join('people','suppliers.person_id=people.person_id');	
		
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
							$custom_fields[$k]="custom_field_${k}_value LIKE '".(!$this->config->item('speed_up_search_queries') ? '%' : '').$this->db->escape_like_str($search)."%'";
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
		
				$this->db->where("$custom_fields and deleted=$deleted");		
		}
		else
		{
			$this->db->where('deleted',$deleted);
		}	

		$supplier_search = $this->db->get_compiled_select();

		$order_by = '';
		if (!$this->config->item('speed_up_search_queries'))
		{
			$order_by = " ORDER BY $column $orderby ";
		}			

		return $this->db->query($people_search." UNION ".$supplier_search." $order_by LIMIT $limit OFFSET $offset");			
		
	}
	
	function search_count_all($search, $deleted = 0, $limit=10000,$search_field = NULL)
	{
		if (!$deleted)
		{
				$deleted = 0;
		}
		
		//The queries are done as 2 unions to speed up searches to use indexes.
	 //When doing OR WHERE across 2 tables; performance is not good
		$this->db->select('*,people.person_id as pid');
		$this->db->from('suppliers');
		$this->db->join('people','suppliers.person_id=people.person_id');	

		if ($search)
		{
				if ($search_field)
				{
					$this->db->where("$search_field LIKE '".(!$this->config->item('speed_up_search_queries') ? '%' : '').$this->db->escape_like_str($search)."%' and deleted=$deleted");		
				}
				else
				{
					$this->db->where("(first_name LIKE '".(!$this->config->item('speed_up_search_queries') ? '%' : '').$this->db->escape_like_str($search)."%' or 
					last_name LIKE '".(!$this->config->item('speed_up_search_queries') ? '%' : '').$this->db->escape_like_str($search)."%' or 
					email LIKE '".(!$this->config->item('speed_up_search_queries') ? '%' : '').$this->db->escape_like_str($search)."%' or 
					phone_number LIKE '".(!$this->config->item('speed_up_search_queries') ? '%' : '').$this->db->escape_like_str($search)."%' or 
					account_number LIKE '".(!$this->config->item('speed_up_search_queries') ? '%' : '').$this->db->escape_like_str($search)."%' or 
					company_name LIKE '".(!$this->config->item('speed_up_search_queries') ? '%' : '').$this->db->escape_like_str($search)."%' or
					full_name LIKE '".(!$this->config->item('speed_up_search_queries') ? '%' : '').$this->db->escape_like_str($search)."%') and deleted=$deleted");		
				}
		}
		else
		{
			$this->db->where('deleted',$deleted);
		}	
			
		$people_search = $this->db->get_compiled_select();
		$this->db->select('*,people.person_id as pid');
		$this->db->from('suppliers');
		$this->db->join('people','suppliers.person_id=people.person_id');	
	
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
							$custom_fields[$k]="custom_field_${k}_value LIKE '".(!$this->config->item('speed_up_search_queries') ? '%' : '').$this->db->escape_like_str($search)."%'";
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
			
				$this->db->where("$custom_fields and deleted=$deleted");		
		}
		else
		{
			$this->db->where('deleted',$deleted);
		}	

		$supplier_search = $this->db->get_compiled_select();

		$result = $this->db->query($people_search." UNION ".$supplier_search);			
		return $result->num_rows();
	}
	
	function find_supplier_id($search)
	{
		if ($search)
		{
			$this->db->select("suppliers.person_id");
			$this->db->from('suppliers');
			$this->db->join('people','suppliers.person_id=people.person_id');
				
			$this->db->group_start();
			$this->db->or_where('first_name',$search);
			$this->db->or_where('last_name',$search);
			$this->db->or_where('full_name',$search);
			$this->db->or_where('company_name',$search);
			$this->db->or_where('email',$search);
			$this->db->group_end();
			$this->db->where('deleted',0);
			$query = $this->db->get();
		
			if ($query->num_rows() > 0)
			{
				return $query->row()->person_id;
			}
		}
		
		return null;
	}
	
	function cleanup()
	{
		$supplier_data = array('account_number' => null);
		$this->db->where('deleted', 1);
		$this->db->update('suppliers',$supplier_data);
		
		$people_table = $this->db->dbprefix('people');
		$app_files_table = $this->db->dbprefix('app_files');
		$suppliers_table = $this->db->dbprefix('suppliers');
		$this->db->query('SET FOREIGN_KEY_CHECKS = 0');
		$this->db->query("UPDATE $people_table SET image_id = NULL WHERE person_id IN (SELECT person_id FROM $suppliers_table WHERE deleted = 1)");
		$this->db->query('SET FOREIGN_KEY_CHECKS = 1');
		return TRUE;
	}
	
	function get_displayable_columns()
	{
		$columns = array(
			'person_id' => 											array('sort_column' => 'pid', 'label' => lang('common_person_id')),
			'full_name' => 											array('sort_column' => 'full_name','label' => lang('common_name'),'data_function' => 'supplier_name_data_function','format_function' => 'supplier_name_formatter','html' => TRUE),
			'first_name' => 										array('sort_column' => 'first_name','label' => lang('common_first_name'),'data_function' => 'supplier_name_data_function','format_function' => 'supplier_name_formatter','html' => TRUE),
			'last_name' => 											array('sort_column' => 'last_name','label' => lang('common_last_name'),'data_function' => 'supplier_name_data_function','format_function' => 'supplier_name_formatter','html' => TRUE),
			'company_name' => 									array('sort_column' => 'company_name','label' => lang('common_company')),
			'account_number' => 								array('sort_column' => 'account_number','label' => lang('suppliers_account_number')),
			'email' => 													array('sort_column' => 'email','label' => lang('common_email'),'format_function' => 'email_formatter','html' => TRUE),
			'phone_number' => 									array('sort_column' => 'phone_number','label' => lang('common_phone_number'),'format_function' => 'tel','html' => TRUE),
			'comments' => 											array('sort_column' => 'comments','label' => lang('common_comments')),
			'internal_notes' => 											array('sort_column' => 'internal_notes','label' => lang('common_internal_notes')),
			'balance' => 												array('sort_column' => 'balance','label' => lang('common_balance'),'data_function' => 'supplier_balance_data','format_function' => 'supplier_balance_formatter','html' => TRUE),
			'address_1' => 											array('sort_column' => 'address_1','label' => lang('common_address_1')),
			'address_2' => 											array('sort_column' => 'address_2','label' => lang('common_address_2')),
			'city' => 													array('sort_column' => 'city','label' => lang('common_city')),
			'state' => 													array('sort_column' => 'state','label' => lang('common_state')),
			'zip' => 														array('sort_column' => 'zip','label' => lang('common_zip')),
			'country' => 												array('sort_column' => 'country','label' => lang('common_country')),
		);
		
		for($k=1;$k<=NUMBER_OF_PEOPLE_CUSTOM_FIELDS;$k++)
		{
			if($this->Supplier->get_custom_field($k) !== false)
			{
				$field = array();
				$field['sort_column'] = "custom_field_${k}_value";
				$field['label']= $this->Supplier->get_custom_field($k);
			
				if ($this->Supplier->get_custom_field($k,'type') == 'checkbox')
				{
					$format_function = 'boolean_as_string';
				}
				elseif($this->Supplier->get_custom_field($k,'type') == 'date')
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
		return array('person_id','company_name','last_name','first_name','email','phone_number');
	}
	
	function get_custom_field($number,$key="name")
	{
		static $config_data;
		
		if (!$config_data)
		{
			$config_data = unserialize($this->config->item('supplier_custom_field_prefs'));
		}
		
		return isset($config_data["custom_field_${number}_${key}"]) && $config_data["custom_field_${number}_${key}"] ? $config_data["custom_field_${number}_${key}"] : FALSE;
	}	
}
?>
