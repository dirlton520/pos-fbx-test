<?php
class Employee extends Person
{
	/*
	Determines if a given person_id is an employee
	*/
	function exists($person_id)
	{
		$this->db->from('employees');	
		$this->db->join('people', 'people.person_id = employees.person_id');
		$this->db->where('employees.person_id',$person_id);
		$query = $this->db->get();
		
		return ($query->num_rows()==1);
	}
	
	function getDefaultRegister($person_id,$location_id = false)
	{
		
		if ($location_id === FALSE)
		{
			$this->db->from('employee_registers');
			$this->db->where('employee_id',$person_id);
			$return = $this->db->get()->row_array();
	
			if (isset($return['register_id']))
			{
				return $return;
			}
		}
		else
		{
			$this->db->from('employee_registers');
			$this->db->join('registers', 'employee_registers.register_id = registers.register_id');
			$this->db->join('locations','locations.location_id = registers.location_id');
			$this->db->where('employee_id',$person_id);
			$this->db->where('registers.location_id',$location_id);
			$return = $this->db->get()->row_array();
			if (isset($return['register_id']))
			{
				return $return;
			}
		}
		return NULL;
	}
	
	
		
	function employee_username_exists($username)
	{
		$this->db->from('employees');	
		$this->db->join('people', 'people.person_id = employees.person_id');
		$this->db->where('employees.username',$username);
		$query = $this->db->get();
		
		
		if($query->num_rows()==1)
		{
			return $query->row()->username;
		}
	}	
	
	/*
	Returns all the employees
	*/
	function get_all($deleted = 0,$limit=10000, $offset=0,$col='last_name',$order='asc',$show_inactive=false,$location_id = '')
	{	
		if (!$deleted)
		{
			$deleted = 0;
		}
		
		$order_by = '';
		if (!$this->config->item('speed_up_search_queries'))
		{
			$order_by = "ORDER BY ".$col." ". $order;
		}
		
		$inactive = '1=1';
		if (!$show_inactive)
		{
			$inactive = 'inactive=0';
		}
		
		$location = '1=1';
		if ($location_id)
		{
			$location = "location_id=$location_id";
		}
		
		$employees=$this->db->dbprefix('employees');
		$employees_locations=$this->db->dbprefix('employees_locations');
		$people=$this->db->dbprefix('people');
		$data=$this->db->query("SELECT *,${people}.person_id as pid 
						FROM ".$people."
						JOIN ".$employees." ON 										                       
						".$people.".person_id = ".$employees.".person_id
						LEFT JOIN ".$employees_locations." ON 										                       
						".$employees.".person_id = ".$employees_locations.".employee_id
						WHERE deleted =$deleted and $inactive and $location GROUP BY $people.person_id $order_by 
						LIMIT  ".$offset.",".$limit);		
						
		return $data;
	}
	
	function count_all($deleted=0,$location_id = '')
	{
		if (!$deleted)
		{
			$deleted = 0;
		}
		
		$this->db->from('employees');
		$this->db->join('employees_locations','employees_locations.employee_id=employees.person_id','LEFT');	
		$this->db->where('deleted',$deleted);
		
		if ($location_id)
		{
			$this->db->where('location_id',$location_id);
		}
		
		$this->db->group_by('employees.person_id');
		return $this->db->count_all_results();
	}
	
	/*
	Gets information about a particular employee
	*/
	function get_info($employee_id, $can_cache = TRUE)
	{
		if ($can_cache)
		{
			static $cache = array();
		
			if (isset($cache[$employee_id]))
			{
				return $cache[$employee_id];
			}
		}
		else
		{
			$cache = array();
		}
		$this->db->from('employees');	
		$this->db->join('people', 'people.person_id = employees.person_id');
		$this->db->where('employees.person_id',$employee_id);
		$query = $this->db->get();

		if($query->num_rows()==1)
		{
			$cache[$employee_id] = $query->row();
			if(!property_exists($cache[$employee_id],'allowed_ip_address') || !$cache[$employee_id]->allowed_ip_address)
				$cache[$employee_id]->allowed_ip_address = array();
			else
				$cache[$employee_id]->allowed_ip_address = unserialize($cache[$employee_id]->allowed_ip_address);
			return $cache[$employee_id];
		}
		else
		{
			//Get empty base parent object, as $employee_id is NOT an employee
			$person_obj=parent::get_info(-1);
			
			//Get all the fields from employee table
			$fields = array('dark_mode','login_start_time','login_end_time','id','username','password','force_password_change','always_require_password','person_id','language','commission_percent','commission_percent_type','hourly_pay_rate','not_required_to_clock_in','inactive','reason_inactive','hire_date','employee_number','birthday','termination_date','deleted','custom_field_1_value','custom_field_2_value','custom_field_3_value','custom_field_4_value','custom_field_5_value','custom_field_6_value','custom_field_7_value','custom_field_8_value','custom_field_9_value','custom_field_10_value','max_discount_percent','allowed_ip_address');

			//append those fields to base parent object, we we have a complete empty object
			foreach ($fields as $field)
			{
				$person_obj->$field='';
			}
			
			return $person_obj;
		}
	}
	
	/*
	Gets information about multiple employees
	*/
	function get_multiple_info($employee_ids)
	{
		$this->db->from('employees');
		$this->db->join('people', 'people.person_id = employees.person_id');		
		$this->db->where_in('employees.person_id',$employee_ids);
		$this->db->order_by("last_name", "asc");
		return $this->db->get();		
	}

	
	/*
	Gets information about multiple employees from multiple locations
	*/
	function get_multiple_locations_employees($location_ids)
	{
		$this->db->select('employee_id');
		$this->db->from('employees_locations');
		$this->db->where_in('location_id',$location_ids);
		$this->db->distinct();
		return $this->db->get();		
	}
	
	function save_profile(&$person_data, &$employee_data, $employee_id)
	{
		$success=false;
		
		//Run these queries as a transaction, we want to make sure we do all or nothing
		$this->db->trans_start();
			
		if(parent::save($person_data,$employee_id))
		{
			if (!$employee_id or !$this->exists($employee_id))
			{
				$employee_data['person_id'] = $employee_id = $person_data['person_id'];
				$success = $this->db->insert('employees',$employee_data);
			}
			else
			{
				$this->db->where('person_id', $employee_id);
				$success = $this->db->update('employees',$employee_data);		
			}	
		}		
		$this->db->trans_complete();		
		return $success;	
	}
	/*
	Inserts or updates an employee
	*/
	function save_employee(&$person_data, &$employee_data,&$permission_data, &$permission_action_data, &$location_data, $employee_id=false, $action_location=array(), $module_location=array())
	{
		$success=false;
		
		//Run these queries as a transaction, we want to make sure we do all or nothing
		$this->db->trans_start();
			
		if(parent::save($person_data,$employee_id))
		{
			if (!$employee_id or !$this->exists($employee_id))
			{
				$employee_data['person_id'] = $employee_id = $person_data['person_id'];
				$success = $this->db->insert('employees',$employee_data);
			}
			else
			{
				if (!empty($employee_data))
				{
					$this->db->where('person_id', $employee_id);
					$success = $this->db->update('employees',$employee_data);
				}
				else
				{
					$success = TRUE;
				}
			}
			
			
			//We have either inserted or updated a new employee, now lets set permissions. We need to have path array !empty. (Api will call this method but with empty permissions)
			if($success)
			{
				//First lets clear out any permissions the employee currently has.
				$success=$this->db->delete('permissions', array('person_id' => $employee_id));
				
				//Now insert the new permissions
				if($success)
				{
					foreach($permission_data as $allowed_module)
					{
						$success = $this->db->insert('permissions',
						array(
						'module_id'=>$allowed_module,
						'person_id'=>$employee_id));
					}
				}
				
				//First lets clear out any permissions actions the employee currently has.
				$success=$this->db->delete('permissions_actions', array('person_id' => $employee_id));
				
				//Now insert the new permissions actions
				if($success)
				{
					foreach($permission_action_data as $permission_action)
					{
						list($module, $action) = explode('|', $permission_action);
						$success = $this->db->insert('permissions_actions',
						array(
						'module_id'=>$module,
						'action_id'=>$action,
						'person_id'=>$employee_id));
					}
				}
				
				
				//module_loation array
				$data_permissions_locations = array();
				foreach($module_location as $mlk => $mlv){
					$element = explode("|",$mlv);
					$data_permissions_locations[] = array(
						'module_id' => $element[0],
						'person_id' => $employee_id,
						'location_id' => $element[1]
					);
				}
				
				//action_location array
				$data_permissions_actions_locations = array();
				foreach($action_location as $alk => $alv){
					$element = explode("|",$alv);
					
					$data_permissions_actions_locations[] = array(
						'module_id' => $element[0],
						'action_id' => $element[1],
						'location_id' => $element[2],
						'person_id' => $employee_id
					);
				}
				
				if (!empty($data_permissions_locations))
				{
					//permissions_locations module_id, person_id, location_id
					$success = $this->db->delete('permissions_locations', array('person_id' => $employee_id));
					$this->db->insert_batch('permissions_locations', $data_permissions_locations);
				}
				
				if (!empty($data_permissions_actions_locations))
				{
					//permissions_actions_locations module_id, person_id, action_id, location_id
					$success=$this->db->delete('permissions_actions_locations', array('person_id' => $employee_id));
					$this->db->insert_batch('permissions_actions_locations', $data_permissions_actions_locations);
				}
				
				
			}
	
				$success=$this->db->delete('employees_locations', array('employee_id' => $employee_id));
				
				//Now insert the new employee locations
				if($success)
				{
					if ($location_data !== FALSE)
					{
						foreach($location_data as $location_id)
						{
							$success = $this->db->insert('employees_locations',
							array(
							'employee_id'=>$employee_id,
							'location_id'=>$location_id
							));
						}
					}	
				}
		}
		$this->db->trans_complete();		
		return $success;
	}
	
	function set_language($language_id,$employee_id)
	{

		$this->db->where('person_id', $employee_id);
		return $this->db->update('employees', array('language' => $language_id));
	}
	
	function set_language_session($language_id)
	{
		$this->session->set_userdata('language', $language_id);
	}
	/*
	Deletes one employee
	*/
	function delete($employee_id)
	{
		$this->db->where('person_id', $employee_id);
		return $this->db->update('employees', array('deleted' => 1));		
	}
	
	/*
	Deletes a list of employees
	*/
	function delete_list($employee_ids)
	{
		//Don't let employee delete their self
		if(in_array($this->get_logged_in_employee_info()->person_id,$employee_ids))
			return false;

			$this->db->where_in('person_id',$employee_ids);
			return $this->db->update('employees', array('deleted' => 1));
 	}
	
	
	
	
	/*
	undeletes one employee
	*/
	function undelete($employee_id)
	{
		$this->db->where('person_id', $employee_id);
		return $this->db->update('employees', array('deleted' => 0));
	}
	
	/*
	undeletes a list of employees
	*/
	function undelete_list($employee_ids)
	{
		$this->db->where_in('person_id',$employee_ids);
		return $this->db->update('employees', array('deleted' => 0));
 	}
	
	
		
	function check_duplicate($term)
	{
		$this->db->from('employees');
		$this->db->join('people','employees.person_id=people.person_id');	
		$this->db->where('deleted',0);		
		$query = $this->db->where("full_name = ".$this->db->escape($term));
		$query=$this->db->get();
		
		if($query->num_rows()>0)
		{
			return true;
		}	
	}
	
	/*
	Get search suggestions to find employees
	*/
	function get_search_suggestions($search,$deleted = 0,$limit=5)
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
		
			$this->db->select("first_name, last_name, email,image_id,employees.person_id", FALSE);
			$this->db->from('employees');
			$this->db->join('people','employees.person_id=people.person_id');
		
			$this->db->where("(first_name LIKE '".$this->db->escape_like_str($search)."%' or 
			last_name LIKE '".$this->db->escape_like_str($search)."%' or 
			full_name LIKE '".$this->db->escape_like_str($search)."%') and deleted=$deleted");			
		
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
		
		
			$this->load->helper('array');
			uasort($temp_suggestions, 'sort_assoc_array_by_name');
			
			foreach($temp_suggestions as $key => $value)
			{
				$suggestions[]=array('value'=> $key, 'label' => $value['name'],'avatar'=>$value['avatar'],'subtitle'=>$value['email']);		
			}
		
			$this->db->select("first_name, last_name, email,image_id,employees.person_id", FALSE);
			$this->db->from('employees');
			$this->db->join('people','employees.person_id=people.person_id');
			$this->db->where('deleted', $deleted);
			$this->db->like('email', $search,'after');			
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
		
			$this->db->select("username, email,image_id,employees.person_id", FALSE);
			$this->db->from('employees');
			$this->db->join('people','employees.person_id=people.person_id');	
			$this->db->where('deleted', $deleted);
			$this->db->like('username', $search,'after');			
			$this->db->limit($limit);
		
			$by_username = $this->db->get();
			$temp_suggestions = array();
			foreach($by_username->result() as $row)
			{
				$data = array(
						'name' => $row->username,
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


			$this->db->select("phone_number, email,image_id,employees.person_id", FALSE);
			$this->db->from('employees');
			$this->db->join('people','employees.person_id=people.person_id');	
			$this->db->where('deleted', $deleted);
			$this->db->like('phone_number', $search,'after');
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
		
			for($k=1;$k<=NUMBER_OF_PEOPLE_CUSTOM_FIELDS;$k++)
			{
				if ($this->get_custom_field($k)) 
				{
					$this->load->helper('date');
					if ($this->get_custom_field($k,'type') != 'date')
					{
						$this->db->select('custom_field_'.$k.'_value as custom_field, email,image_id, employees.person_id', false);						
					}
					else
					{
						$this->db->select('FROM_UNIXTIME(custom_field_'.$k.'_value, "'.get_mysql_date_format().'") as custom_field, email,image_id, employees.person_id', false);
					}
					$this->db->from('employees');
					$this->db->join('people','employees.person_id=people.person_id');	
					$this->db->where('deleted',$deleted);
				
					if ($this->get_custom_field($k,'type') != 'date')
					{
						$this->db->like("custom_field_${k}_value",$search,'after');
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
		
		
			foreach($temp_suggestions as $key => $value)
			{
				$suggestions[]=array('value'=> $key, 'label' => $value['name'],'avatar'=>$value['avatar'],'subtitle'=>$value['email']);		
			}
		
		//only return $limit suggestions
		$suggestions = array_map("unserialize", array_unique(array_map("serialize", $suggestions)));
		if(count($suggestions) > $limit)
		{
			$suggestions = array_slice($suggestions, 0,$limit);
		}
		
		$suggestions = array_map("unserialize", array_unique(array_map("serialize", $suggestions)));
		
		return $suggestions;
	}
		
	function search($search, $deleted = 0,$limit=20,$offset=0,$column='last_name',$orderby='asc',$search_field = NULL,$location_id = '')
	{		
		if (!$deleted)
		{
			$deleted = 0;
		}
		
		//The queries are done as 2 unions to speed up searches to use indexes.
	 //When doing OR WHERE across 2 tables; performance is not good
	 $this->db->select('employees_locations.location_id,employees.*,people.*,people.person_id as pid');
		$this->db->from('employees');
		$this->db->join('employees_locations','employees_locations.employee_id=employees.person_id','left');	
		$this->db->join('people','employees.person_id=people.person_id');	
		$this->db->group_by('employees.person_id');
		if ($location_id)
		{
			$this->db->where('location_id',$location_id);
		}

		if ($search)
		{
				if ($search_field)
				{
					$this->db->where("$search_field LIKE '".$this->db->escape_like_str($search)."%' and deleted=$deleted");		
				}
				else
				{
					$this->db->where("(first_name LIKE '".$this->db->escape_like_str($search)."%' or 
					last_name LIKE '".$this->db->escape_like_str($search)."%' or 
					email LIKE '".$this->db->escape_like_str($search)."%' or 
					phone_number LIKE '".$this->db->escape_like_str($search)."%' or 
					username LIKE '".$this->db->escape_like_str($search)."%' or
					full_name LIKE '".$this->db->escape_like_str($search)."%') and deleted=$deleted");		
				}		
		}
		else
		{
			$this->db->where('deleted',$deleted);
		}	
			
		$people_search = $this->db->get_compiled_select();
	
 	 $this->db->select('employees_locations.location_id,employees.*,people.*,people.person_id as pid');
		$this->db->from('employees');
		$this->db->join('employees_locations','employees_locations.employee_id=employees.person_id','left');	
		$this->db->join('people','employees.person_id=people.person_id');	
		$this->db->group_by('employees.person_id');
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
						$custom_fields[$k]="custom_field_${k}_value LIKE '".$this->db->escape_like_str($search)."%'";
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

		$employee_search = $this->db->get_compiled_select();

		$order_by = '';
		if (!$this->config->item('speed_up_search_queries'))
		{
			$order_by = " ORDER BY $column $orderby ";
		}			

		return $this->db->query($people_search." UNION ".$employee_search." $order_by LIMIT $limit OFFSET $offset");
		
	}
	
	function search_count_all($search,$deleted=0, $limit=10000,$search_field = NULL,$location_id = '')
	{
		if (!$deleted)
		{
			$deleted = 0;
		}
		
		//The queries are done as 2 unions to speed up searches to use indexes.
	 //When doing OR WHERE across 2 tables; performance is not good
		$this->db->from('employees');
		$this->db->join('employees_locations','employees_locations.employee_id=employees.person_id','left');	
		$this->db->join('people','employees.person_id=people.person_id');	
		$this->db->group_by('employees.person_id');
		if ($location_id)
		{
			$this->db->where('location_id',$location_id);
		}
		
		if ($search)
		{
				if ($search_field)
				{
					$this->db->where("$search_field LIKE '".$this->db->escape_like_str($search)."%' and deleted=$deleted");		
				}
				else
				{
					$this->db->where("(first_name LIKE '".$this->db->escape_like_str($search)."%' or 
					last_name LIKE '".$this->db->escape_like_str($search)."%' or 
					email LIKE '".$this->db->escape_like_str($search)."%' or 
					phone_number LIKE '".$this->db->escape_like_str($search)."%' or 
					username LIKE '".$this->db->escape_like_str($search)."%' or
					full_name LIKE '".$this->db->escape_like_str($search)."%') and deleted=$deleted");		
				}		
		}
		else
		{
			$this->db->where('deleted',$deleted);
		}	
	
		$people_search = $this->db->get_compiled_select();

		$this->db->from('employees');
		$this->db->join('employees_locations','employees_locations.employee_id=employees.person_id','left');	
		$this->db->join('people','employees.person_id=people.person_id');	
		$this->db->group_by('employees.person_id');
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
						$custom_fields[$k]="custom_field_${k}_value LIKE '".$this->db->escape_like_str($search)."%'";
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

		$employee_search = $this->db->get_compiled_select();


		$result = $this->db->query($people_search." UNION ".$employee_search);			
		return $result->num_rows();
	}


	/*
	Attempts to login employee and set session. Returns boolean based on outcome.
	*/
	function login($username, $password)
	{
		//Username Query
		$query = $this->db->get_where('employees', array('username' => $username,'password'=>md5($password), 'deleted'=> 0 ,'inactive' => 0), 1);
		if ($query->num_rows() ==1)
		{
			if ($this->login_failed_time_period($username,$password))
			{
				return FALSE;
			}
			
			$row=$query->row();
			$this->session->set_userdata('person_id', $row->person_id);
			return true;
		}
		
		//Employee Number Query
		$query = $this->db->get_where('employees', array('employee_number' => $username,'password'=>md5($password), 'deleted'=> 0 ,'inactive' => 0), 1);
		if ($query->num_rows() ==1)
		{
			if ($this->login_failed_time_period($username,$password))
			{
				return FALSE;
			}
			
			$row=$query->row();
			$this->session->set_userdata('person_id', $row->person_id);
			return true;
		}
		
		return false;
	}
	
	function login_failed_time_period($username, $password)
	{
		$query = $this->db->get_where('employees', array('username' => $username,'password'=>md5($password), 'deleted'=> 0 ,'inactive' => 0), 1);
		if ($query->num_rows() ==1)
		{
			
			$row=$query->row();
			
			//if we don't have time restirctions set this we can login without checking
			if ($row->login_start_time === NULL || $row->login_end_time === NULL)
			{
				return FALSE;
			}
			
			$cur_timezone = date_default_timezone_get();			
			date_default_timezone_set($this->Location->get_info_for_key('timezone',1));
			$now = time();
			$start_time = strtotime($row->login_start_time);
			$end_time = strtotime($row->login_end_time);
			date_default_timezone_set($cur_timezone);
			
			if ($now >= $start_time && $now <=$end_time)
			{
				return FALSE;
			}
			else
			{
				return TRUE;
			}			
		}
		
		return FALSE;
	}
	
	function login_no_password($username)
	{
		//Username Query
		$query = $this->db->get_where('employees', array('username' => $username, 'deleted'=> 0 ,'inactive' => 0), 1);
		if ($query->num_rows() ==1)
		{
			$row=$query->row();
			$this->session->set_userdata('person_id', $row->person_id);
			return true;
		}
		
		//Employee Number Query
		$query = $this->db->get_where('employees', array('employee_number' => $username, 'deleted'=> 0 ,'inactive' => 0), 1);
		if ($query->num_rows() ==1)
		{
			$row=$query->row();
			$this->session->set_userdata('person_id', $row->person_id);
			return true;
		}
		
		return false;
	}
	
	/*
	Logs out a user by destorying all session data and redirect to login
	*/
	function logout($redirect_to_login = TRUE)
	{
		$this->session->sess_destroy();
		
		if ($redirect_to_login)
		{
			redirect('login');
		}
	}
	
	/*
	Determins if a employee is logged in
	*/
	function is_logged_in()
	{
		return $this->session->userdata('person_id')!=false;
	}
	
	/*
	Gets information about the currently logged in employee.
	*/
	function get_logged_in_employee_info()
	{
		if($this->is_logged_in())
		{
			$ret = $this->get_info($this->session->userdata('person_id'));
			if ($this->session->userdata('language'))
			{
				$ret->language = $this->session->userdata('language');
			}
			return $ret;
		}
		
		return false;
	}
	
	/*
	Gets the current employee's location. If they have more than 1, then a user can change during session
	*/
	function get_logged_in_employee_current_location_id()
	{
		if($this->is_logged_in())
		{
			//If we have a location in the session
			if ($this->session->userdata('employee_current_location_id')!==NULL)
			{
				return $this->session->userdata('employee_current_location_id');
			}
			
			//Return the first location user is authenticated for
			return current($this->get_authenticated_location_ids($this->session->userdata('person_id')));
		}
		
		return FALSE;
	}
	
	function get_current_location_info()
	{
		return $this->Location->get_info($this->get_logged_in_employee_current_location_id());
	}
		
	function set_employee_current_location_id($location_id)
	{
		if ($this->is_location_authenticated($location_id))
		{
			$this->session->set_userdata('employee_current_location_id', $location_id);
		}
	}
	
	/*
	Gets the current employee's register id (if set)
	*/
	function get_logged_in_employee_current_register_id()
	{
		if($this->is_logged_in())
		{
			//If we have a register in the session
			if ($this->session->userdata('employee_current_register_id')!==NULL)
			{
				return $this->session->userdata('employee_current_register_id');
			}
			
			return NULL;
		}
		
		return NULL;
	}
	
	function set_employee_current_register_id($register_id)
	{
		$this->session->set_userdata('employee_current_register_id', $register_id);
	}
	
	
	/*
	Determins whether the employee specified employee has access the specific module.
	*/
	function has_module_permission($module_id,$person_id,$location_id = FALSE,$global_only = FALSE)
	{
		//if no module_id is null, allow access
		if($module_id==null)
		{
			return true;
		}
		
		if ($location_id === FALSE)
		{
			$location_id = $this->get_logged_in_employee_current_location_id();
		}
		
		static $cache;
		
		if (isset($cache[$module_id.'|'.$person_id.'|'.$location_id.'|'.($global_only ? '1' : '0')]))
		{
			return $cache[$module_id.'|'.$person_id.'|'.$location_id.'|'.($global_only ? '1' : '0')];
		}
		
		
		if ($global_only)
		{
			$query = $this->db->get_where('permissions', array('person_id' => $person_id,'module_id'=>$module_id), 1);
			$cache[$module_id.'|'.$person_id.'|'.$location_id.'|'.($global_only ? '1' : '0')] =  $query->num_rows() == 1;
			
		}
		else
		{	
			//Don't include location id to see if anywhere overrides it
			$this->db->from('permissions_locations');
			$this->db->where("permissions_locations.person_id",$person_id);
			$this->db->where('permissions_locations.module_id',$module_id);
			
			$query = $this->db->get();
			
			//Can be overwritten at many locations
			$is_overridden = $query->num_rows() >=1;
			
			if($is_overridden)
			{
				$this->db->from('permissions_locations');
				$this->db->where("permissions_locations.person_id",$person_id);
				$this->db->where('permissions_locations.module_id',$module_id);
				$this->db->where('permissions_locations.location_id',$location_id);
				
				$query = $this->db->get();
				$cache[$module_id.'|'.$person_id.'|'.$location_id.'|'.($global_only ? '1' : '0')] =   $query->num_rows() ==1;
				
			}
			else
			{
				$query = $this->db->get_where('permissions', array('person_id' => $person_id,'module_id'=>$module_id), 1);
				$cache[$module_id.'|'.$person_id.'|'.$location_id.'|'.($global_only ? '1' : '0')] =  $query->num_rows() == 1;
			}
		}
				
		return $cache[$module_id.'|'.$person_id.'|'.$location_id.'|'.($global_only ? '1' : '0')];
	}
	
	function has_module_action_permission($module_id, $action_id, $person_id,$location_id = FALSE,$global_only = FALSE)
	{
		//if no module_id is null, allow access
		if($module_id==null)
		{
			return true;
		}
		
		if ($location_id === FALSE)
		{
			$location_id = $this->get_logged_in_employee_current_location_id();
		}
		
		static $cache;
		
		if (isset($cache[$module_id.'|'.$action_id.'|'.$person_id.'|'.$location_id.'|'.($global_only ? '1' : '0')]))
		{
			return $cache[$module_id.'|'.$action_id.'|'.$person_id.'|'.$location_id.'|'.($global_only ? '1' : '0')];
		}
			
		if ($global_only)
		{
			$this->db->select('permissions_actions.*');
			$this->db->from('permissions_actions');
			$this->db->where("permissions_actions.person_id",$person_id);
			$this->db->where('permissions_actions.module_id',$module_id);
			$this->db->where('permissions_actions.action_id',$action_id);
			$query = $this->db->get();
			$cache[$module_id.'|'.$action_id.'|'.$person_id.'|'.$location_id.'|'.($global_only ? '1' : '0')] =  $query->num_rows() == 1;
			
		}
		else
		{	
			//Don't include location id to see if anywhere overrides it
			$this->db->from('permissions_actions_locations');
			$this->db->where("permissions_actions_locations.person_id",$person_id);
			$this->db->where('permissions_actions_locations.module_id',$module_id);
			$this->db->where('permissions_actions_locations.action_id',$action_id);
			$query = $this->db->get();
			
			//Can be overwritten at many locations
			$is_overridden = $query->num_rows() >=1;
			
			if($is_overridden)
			{
				$this->db->from('permissions_actions_locations');
				$this->db->where('permissions_actions_locations.location_id',$location_id);
				$this->db->where("permissions_actions_locations.person_id",$person_id);
				$this->db->where('permissions_actions_locations.module_id',$module_id);
				$this->db->where('permissions_actions_locations.action_id',$action_id);
				$query = $this->db->get();
				$cache[$module_id.'|'.$action_id.'|'.$person_id.'|'.$location_id.'|'.($global_only ? '1' : '0')] =   $query->num_rows() ==1;
				
			}
			else
			{
				$this->db->select('permissions_actions.*');
				$this->db->from('permissions_actions');
				$this->db->where("permissions_actions.person_id",$person_id);
				$this->db->where('permissions_actions.module_id',$module_id);
				$this->db->where('permissions_actions.action_id',$action_id);
				$query = $this->db->get();
				$cache[$module_id.'|'.$action_id.'|'.$person_id.'|'.$location_id.'|'.($global_only ? '1' : '0')] =  $query->num_rows() == 1;
			}
		}
		
		return $cache[$module_id.'|'.$action_id.'|'.$person_id.'|'.$location_id.'|'.($global_only ? '1' : '0')];
	}
	
	function get_employee_by_username_or_email($username_or_email)
	{
		$this->db->from('employees');	
		$this->db->join('people', 'people.person_id = employees.person_id');
		$this->db->where('username',$username_or_email);
		$this->db->or_where('email',$username_or_email);
		$query = $this->db->get();
		
		if ($query->num_rows() == 1)
		{
			return $query->row();
		}
		
		return false;
	}
	
	function update_employee_password($employee_id, $password, $force_password_change = 0)
	{
		$employee_data = array('password' => $password, 'force_password_change' => $force_password_change);
		$this->db->where('person_id', $employee_id);
		$success = $this->db->update('employees',$employee_data);
		
		return $success;
	}
		
	function cleanup()
	{
		$employee_data = array('username' => null);
		$this->db->where('deleted', 1);
		$this->db->update('employees',$employee_data);
		$people_table = $this->db->dbprefix('people');
		$app_files_table = $this->db->dbprefix('app_files');
		$employees_table = $this->db->dbprefix('employees');
		$this->db->query('SET FOREIGN_KEY_CHECKS = 0');
		$this->db->query("UPDATE $people_table SET image_id = NULL WHERE person_id IN (SELECT person_id FROM $employees_table WHERE deleted = 1)");
		$this->db->query('SET FOREIGN_KEY_CHECKS = 1');
		return TRUE;
	}
		
	function get_employee_id($username)
	{
		$query = $this->db->get_where('employees', array('username' => $username, 'deleted'=>0), 1);
		if ($query->num_rows() ==1)
		{
			$row=$query->row();
			return $row->person_id;
		}
		
		$query = $this->db->get_where('employees', array('employee_number' => $username, 'deleted'=>0), 1);
		if ($query->num_rows() ==1)
		{
			$row=$query->row();
			return $row->person_id;
		}
		
		return false;
	}
	
	function get_authenticated_location_ids($employee_id,$include_location_wise_permissions = false)
	{
		static $cache;
		
		if (isset($cache[$employee_id.'|'.($include_location_wise_permissions ? '1' : '0')]))
		{
			return $cache[$employee_id.'|'.($include_location_wise_permissions ? '1' : '0')];
		}
		
		
		$this->db->select('employees_locations.location_id');
		$this->db->from('employees_locations');
		$this->db->join('locations', 'locations.location_id = employees_locations.location_id');
		$this->db->where('employee_id', $employee_id);
		$this->db->where('deleted', 0);
		
		
		if ($include_location_wise_permissions)
		{			
			$employee_location_query = $this->db->get_compiled_select();
			$this->db->select('permissions_locations.location_id');
			$this->db->from('permissions_locations');
			$this->db->where('person_id', $employee_id);
			
			$employee_location_location_query = $this->db->get_compiled_select();
		
			$location_ids = array();
		
			foreach($this->db->query($employee_location_query." UNION ".$employee_location_location_query.' order by location_id asc')->result_array() as $location)
			{
				$location_ids[] = $location['location_id'];
			}
			$cache[$employee_id.'|'.($include_location_wise_permissions ? '1' : '0')] = $location_ids;
			
			
		}
		else
		{
			$this->db->order_by('location_id', 'asc');

			$location_ids = array();
		
			foreach($this->db->get()->result_array() as $location)
			{
				$location_ids[] = $location['location_id'];
			}
			$cache[$employee_id.'|'.($include_location_wise_permissions ? '1' : '0')] = $location_ids;
		}
		
		
		return $location_ids;
	}
	
	function is_location_authenticated($location_id)
	{
		
		if ($employee = $this->get_logged_in_employee_info())
		{
			$this->db->select('location_id');
			$this->db->from('employees_locations');
			$this->db->where('employee_id', $employee->person_id);
			$this->db->where('location_id', $location_id);
			
			$employee_location_query = $this->db->get_compiled_select();
			$this->db->select('permissions_locations.location_id');
			$this->db->from('permissions_locations');
			$this->db->where('person_id', $employee->person_id);
			$this->db->where('location_id', $location_id);
			
			$employee_location_location_query = $this->db->get_compiled_select();
			
			$query = $employee_location_query." UNION ".$employee_location_location_query;
			$result = $this->db->query($query);

			return $result->num_rows() == 1;
		}
		
		return FALSE;
	}
	
	function is_employee_authenticated($employee_id, $location_id)
	{
		static $authed_employees;
		
		if (!$authed_employees)
		{
			$this->db->select('employee_id');
			$this->db->from('employees_locations');
			$this->db->where('location_id', $location_id);
			$result = $this->db->get();
			$authed_employees = array();
			
			foreach($result->result_array() as $employee)
			{
				$authed_employees[$employee['employee_id']] = TRUE;
			}	
		}
		return isset($authed_employees[$employee_id]) && $authed_employees[$employee_id]; 
	}
	
	function clock_in($comment, $employee_id = false, $location_id = false)
	{
		if ($employee_id === FALSE)
		{
			$employee_id = $this->get_logged_in_employee_info()->person_id;
		}
		
		if ($location_id === FALSE)
		{
			$location_id = $this->get_logged_in_employee_current_location_id();
		}
		
		return $this->db->insert('employees_time_clock', array(
			'employee_id' => $employee_id,
			'location_id' => $location_id,
			'clock_in' => date('Y-m-d H:i:s'),
			'clock_in_comment' => $comment,
			'clock_out_comment' => '',
			'ip_address_clock_in' => $this->input->ip_address(),
		));
		
	}
	
	function clock_out($comment, $employee_id = false, $location_id = false)
	{
		if ($employee_id === FALSE)
		{
			$employee_id = $this->get_logged_in_employee_info()->person_id;
		}
		
		$cur_emp_info = $this->get_info($employee_id);
		
		if ($location_id === FALSE)
		{
			$location_id = $this->get_logged_in_employee_current_location_id();
		}
		
		if ($this->is_clocked_in($employee_id, $location_id))
		{
			$this->db->limit(1);
			$this->db->where('clock_in !=','0000-00-00 00:00:00');
			$this->db->where('clock_out','0000-00-00 00:00:00');
			$this->db->where('employee_id',$employee_id);
			$this->db->where('location_id',$location_id);
			return $this->db->update('employees_time_clock', array('clock_out' => date('Y-m-d H:i:s'), 'clock_out_comment' => $comment, 'hourly_pay_rate' => $cur_emp_info->hourly_pay_rate,'ip_address_clock_out' => $this->input->ip_address()));
		}
		
		return FALSE;
	}
	
	function is_clocked_in($employee_id = false, $location_id = false)
	{
		if ($employee_id === FALSE)
		{
			$employee_id = $this->get_logged_in_employee_info()->person_id;
		}
		
		if ($location_id === FALSE)
		{
			$location_id = $this->get_logged_in_employee_current_location_id();
		}
		
		$this->db->from('employees_time_clock');
		$this->db->where('clock_in !=','0000-00-00 00:00:00');
		$this->db->where('clock_out','0000-00-00 00:00:00');
		$this->db->where('employee_id',$employee_id);
		$this->db->where('location_id',$location_id);
		
		$query = $this->db->get();
		if($query->num_rows())
		return true	;
		else
		return false;
	
	 }
	 
	 function delete_timeclock($id)
	 {
		 return $this->db->delete('employees_time_clock', array('id' => $id));
	 }
	 
	 function delete_time_off($id)
	 {
		 $this->db->where('id', $id);
		 return $this->db->update('employees_time_off', array('deleted' => 1));
	 }
	 
	 function get_timeclock($id)
	 {
 		$this->db->from('employees_time_clock');	
		$this->db->where('id', $id);
 		$query = $this->db->get();
		
 		if($query->num_rows()==1)
 		{
 			return $query->row();
 		}
		else
		{
			//Get empty object
			$timeclock_obj=new stdClass();
			
			//Get all the fields from employee table
			$fields = array('id','employee_id','location_id','clock_in','clock_out','clock_in_comment','clock_out_comment','hourly_pay_rate','ip_address_clock_in','ip_address_clock_out');			
			//append those fields to base parent object, we we have a complete empty object
			foreach ($fields as $field)
			{
				$timeclock_obj->$field='';
			}
			
			return $timeclock_obj;
		}
		
		
		return false;
	 }
	 
	function save_timeclock($data)
	{
		$save_data = array();
		
		$clock_in_time = strtotime($data['clock_in']);
		$clock_out_time = strtotime($data['clock_out']);
		
		if ($clock_in_time !== FALSE)
		{
			$save_data['clock_in'] = date('Y-m-d H:i:s', $clock_in_time);
		}
		
		if ($clock_out_time !== FALSE)
		{
			$save_data['clock_out'] = date('Y-m-d H:i:s', $clock_out_time);
		}
		
		$save_data['employee_id'] = $data['employee_id'];
		$save_data['location_id'] = $data['location_id'];
		$save_data['clock_in_comment'] = $data['clock_in_comment'];
		$save_data['clock_out_comment'] = $data['clock_out_comment'];
		$save_data['hourly_pay_rate'] = $data['hourly_pay_rate'];
		if ($this->exists($save_data['employee_id']))
		{
			if ($data['id'] == -1)
			{
				return $this->db->insert('employees_time_clock', $save_data);
			}
			else
			{
				$this->db->where('id', $data['id']);
				return $this->db->update('employees_time_clock', $save_data);
			}
		}	
		
		return FALSE;
	}

	function save_message($data)
	{
		$message_data = array(
		'message'=>$data['message'],
		'created_at' => date('Y-m-d H:i:s'),
		'sender_id'=>$this->get_logged_in_employee_info()->person_id,
		);
		

			if($this->db->insert('messages', $message_data))
			{
				$message_id = $this->db->insert_id();


				if($data['all_employees']=="all")
				{
					
					if($data["all_locations"]=="all")
					{
						$employee_ids = array();

						foreach ($this->Location->get_all()->result() as $location)
						{
							$location_ids[] = $location->location_id;
						}

						$employee_ids = $this->get_multiple_locations_employees($location_ids)->result_array();

					}
					else
					{
						$employee_ids = $this->get_multiple_locations_employees($data['locations'])->result_array();

					}

					//Prepare the employees ids format 
					$person_ids = array();
					foreach ($employee_ids as $value) {

						$message_receiver = array(
						'message_id'=>$message_id,
						'receiver_id'=>$value['employee_id'],
					);	
						
						$this->db->insert('message_receiver',$message_receiver);		

					}

					return true;

				}
				else
				{
					foreach ($data["employees"] as $employee_id) {
							$message_receiver = array(
								'message_id'=>$message_id,
								'receiver_id'=>$employee_id,
							);	
								
								$this->db->insert('message_receiver',$message_receiver);	
					}

					return true;
				}

				return false;

				
			}
		
		
	}

	function get_messages($limit=20, $offset=0)
	{

		$logged_employee_id = $this->get_logged_in_employee_info()->person_id;

		$this->db->from('messages');
		$this->db->join('message_receiver','messages.id=message_receiver.message_id');	
		$this->db->where('receiver_id',$logged_employee_id);		
		$this->db->limit($limit,$offset);		
		$this->db->where('messages.deleted',0);		
		$this->db->order_by("created_at", "desc");
		$this->db->limit($limit);
		$this->db->offset($offset);
		$query=$this->db->get();

		return $query->result_array();
	}

	function get_messages_count()
	{
		$logged_employee_id = $this->get_logged_in_employee_info()->person_id;
		
		$this->db->from('messages');
		$this->db->join('message_receiver','messages.id=message_receiver.message_id');	
		$this->db->where('receiver_id',$logged_employee_id);		
		$this->db->where('messages.deleted',0);
		
		return $this->db->count_all_results();
	}
	
	function get_sent_messages($limit=20, $offset=0)
	{

		$logged_employee_id = $this->get_logged_in_employee_info()->person_id;
		$this->db->select('messages.*, GROUP_CONCAT(DISTINCT '.$this->db->dbprefix('people').'.first_name, " ",'.$this->db->dbprefix('people').'.last_name SEPARATOR ", ") as sent_to', false);
		$this->db->from('messages');
		$this->db->join('message_receiver', 'message_receiver.message_id = messages.id');
		$this->db->join('people', 'people.person_id = message_receiver.receiver_id');
		$this->db->where('sender_id',$logged_employee_id);		
		$this->db->where('messages.deleted',0);		
		$this->db->order_by("created_at", "desc");
		$this->db->group_by('messages.id');
		$this->db->limit($limit);
		$this->db->offset($offset);
		
		$query=$this->db->get();
		return $query->result_array();
	}
	
	function get_sent_messages_count()
	{

		$logged_employee_id = $this->get_logged_in_employee_info()->person_id;
		$this->db->from('messages');
		$this->db->where('sender_id',$logged_employee_id);		
		$this->db->where('messages.deleted',0);		
		
		return $this->db->count_all_results();
	}

	function get_unread_messages_count($limit=20, $offset=0)
	{
		$logged_employee_id = $this->get_logged_in_employee_info()->person_id;
		$this->db->from('message_receiver');
		$this->db->join('messages','messages.id=message_receiver.message_id');	
		$this->db->where('receiver_id',$logged_employee_id);		
		$this->db->where('message_read',0);		
		$this->db->where('deleted',0);
		
		return $this->db->count_all_results();
	}	 

	function read_message($message_id)
	{

		$logged_employee_id = $this->get_logged_in_employee_info()->person_id;
		$this->db->where('receiver_id',$logged_employee_id);		
		$this->db->where('id', $message_id);
		return $this->db->update('message_receiver', array('message_read' => 1));		
	}

	function delete_message($message_id)
	{
		$this->db->where('id', $message_id);
		return $this->db->update('messages', array('deleted' => 1));		
	}
	
	function get_supplier_columns_to_display()
	{
		$all_columns = $this->Supplier->get_displayable_columns();
		
		$columns_to_display = array();
		
		$this->load->model('Employee_appconfig');
		if ($choices = $this->Employee_appconfig->get('supplier_column_prefs'))
		{
			$columns_to_display_keys = unserialize($choices);
		}
		else
		{
			$columns_to_display_keys = $this->Supplier->get_default_columns();

		}
		
		foreach($columns_to_display_keys as $key)
		{
			$columns_to_display[$key] = $all_columns[$key];
		}
	
		return $columns_to_display;
	}
	
	function get_customer_columns_to_display()
	{
		$all_columns = $this->Customer->get_displayable_columns();
		
		$columns_to_display = array();
		
		$this->load->model('Employee_appconfig');
		if ($choices = $this->Employee_appconfig->get('customer_column_prefs'))
		{
			$columns_to_display_keys = unserialize($choices);
		}
		else
		{
			$columns_to_display_keys = $this->Customer->get_default_columns();

		}
		
		foreach($columns_to_display_keys as $key)
		{
			$columns_to_display[$key] = $all_columns[$key];
		}
	
		return $columns_to_display;
		
	}
	
	function get_sale_order_columns_to_display()
	{
		$this->load->model('Delivery');
		$all_columns = $this->Delivery->get_displayable_columns();
		
		$columns_to_display = array();
		
		$this->load->model('Employee_appconfig');
		$choices = $this->Employee_appconfig->get('sale_orders_column_prefs');
	
		if ($choices)
		{
			$columns_to_display_keys = unserialize($choices);
		}
		else
		{

			$columns_to_display_keys = $this->Delivery->get_default_columns();
			
		}
		
		foreach($columns_to_display_keys as $key)
		{
			$columns_to_display[$key] = $all_columns[$key];
		}
	
		return $columns_to_display;
		
	}
	
	function get_item_columns_to_display()
	{
		static $has_cost_price_permission;
		
		if (!$has_cost_price_permission)
		{
			$has_cost_price_permission = $this->has_module_action_permission('items','see_cost_price', $this->get_logged_in_employee_info()->person_id);
		}
		
		$this->load->model('Item');
		
		$all_columns = $this->Item->get_displayable_columns();
		
		
		$columns_to_display = array();
		
		$this->load->model('Employee_appconfig');
		if ($choices = $this->Employee_appconfig->get('item_column_prefs'))
		{
			$columns_to_display_keys = unserialize($choices);
		}
		else
		{
			$columns_to_display_keys = $this->Item->get_default_columns();

		}
		
		foreach($columns_to_display_keys as $key)
		{
			if (isset($all_columns[$key]))
			{
				$columns_to_display[$key] = $all_columns[$key];
			}
		}
		
		if (!$has_cost_price_permission)
		{
			if (isset($columns_to_display['cost_price']))
			{
				unset($columns_to_display['cost_price']);
			}

			if (isset($columns_to_display['location_cost_price']))
			{
				unset($columns_to_display['location_cost_price']);
			}
		}
		
		return $columns_to_display;
	}
	
	function get_suspended_sales_columns_to_display(){
		$this->load->model('Sale');
		
		$all_columns = $this->Sale->get_suspended_sales_displayable_columns();
		
		$columns_to_display = array();
		
		$this->load->model('Employee_appconfig');
		if ($choices = $this->Employee_appconfig->get('suspended_sales_column_prefs'))
		{
			$columns_to_display_keys = unserialize($choices);
		}
		else
		{
			$columns_to_display_keys = $this->Sale->get_suspended_sales_default_columns();

		}
		
		foreach($columns_to_display_keys as $key)
		{
			if (isset($all_columns[$key]))
			{
				$columns_to_display[$key] = $all_columns[$key];
			}
		}
		
		return $columns_to_display;
		
	}
	
	function get_suspended_receivings_columns_to_display(){
		$this->load->model('Sale');
		
		$all_columns = $this->Receiving->get_suspended_receivings_displayable_columns();
		
		$columns_to_display = array();
		
		$this->load->model('Employee_appconfig');
		if ($choices = $this->Employee_appconfig->get('suspended_receivings_column_prefs'))
		{
			$columns_to_display_keys = unserialize($choices);
		}
		else
		{
			$columns_to_display_keys = $this->Receiving->get_suspended_receivings_default_columns();

		}
		
		foreach($columns_to_display_keys as $key)
		{
			if (isset($all_columns[$key]))
			{
				$columns_to_display[$key] = $all_columns[$key];
			}
		}
		
		return $columns_to_display;
		
	}
	
	function get_item_kit_columns_to_display()
	{
		static $has_cost_price_permission;
		
		if (!$has_cost_price_permission)
		{
			$has_cost_price_permission = $this->has_module_action_permission('item_kits','see_cost_price', $this->get_logged_in_employee_info()->person_id);
		}
		
		$this->load->model('Item_kit');
		
		$all_columns = $this->Item_kit->get_displayable_columns();
		
		
		$columns_to_display = array();
		
		$this->load->model('Employee_appconfig');
		if ($choices = $this->Employee_appconfig->get('item_kit_column_prefs'))
		{
			$columns_to_display_keys = unserialize($choices);
		}
		else
		{
			$columns_to_display_keys = $this->Item_kit->get_default_columns();

		}
		
		foreach($columns_to_display_keys as $key)
		{
			$columns_to_display[$key] = $all_columns[$key];
		}
		
		if (!$has_cost_price_permission)
		{
			if (isset($columns_to_display['cost_price']))
			{
				unset($columns_to_display['cost_price']);
			}
			
			if (isset($columns_to_display['location_cost_price']))
			{
				unset($columns_to_display['location_cost_price']);
			}
		}
		
		return $columns_to_display;
	}
	
	
	function get_employee_columns_to_display()
	{
		
		$all_columns = $this->get_displayable_columns();
		
		$columns_to_display = array();
		
		$this->load->model('Employee_appconfig');
		if ($choices = $this->Employee_appconfig->get('employee_column_prefs'))
		{
			$columns_to_display_keys = unserialize($choices);
		}
		else
		{
			$columns_to_display_keys = $this->get_default_columns();

		}
		
		foreach($columns_to_display_keys as $key)
		{
			$columns_to_display[$key] = $all_columns[$key];
		}
	
		return $columns_to_display;
	}
	
	function get_custom_field($number,$key="name")
	{
		static $config_data;
		
		if (!$config_data)
		{
			$config_data = unserialize($this->config->item('employee_custom_field_prefs'));
		}
		
		return isset($config_data["custom_field_${number}_${key}"]) && $config_data["custom_field_${number}_${key}"] ? $config_data["custom_field_${number}_${key}"] : FALSE;
	}
	
	
	function get_displayable_columns()
	{
		$this->lang->load('items');
		$columns = array(
			'person_id' => 											array('sort_column' => 'pid', 'label' => lang('common_person_id')),
			'full_name' => 											array('sort_column' => 'full_name','label' => lang('common_name'),'data_function' => 'customer_name_data_function','format_function' => 'customer_name_formatter','html' => TRUE),
			'first_name' => 										array('sort_column' => 'first_name','label' => lang('common_first_name'),'data_function' => 'customer_name_data_function','format_function' => 'customer_name_formatter','html' => TRUE),
			'last_name' => 											array('sort_column' => 'last_name','label' => lang('common_last_name'),'data_function' => 'customer_name_data_function','format_function' => 'customer_name_formatter','html' => TRUE),
			'email' => 													array('sort_column' => 'email','label' => lang('common_email'),'format_function' => 'email_formatter','html' => TRUE),
			'username' => 											array('sort_column' => 'username','label' => lang('common_username')),
			'employee_number' => 								array('sort_column' => 'employee_number','label' => lang('common_employees_number')),
			'hire_date' => 											array('sort_column' => 'hire_date','label' => lang('employees_hire_date'),'format_function' => 'date_as_display_date'),
			'birthday' => 											array('sort_column' => 'birthday','label' => lang('employees_birthday'),'format_function' => 'date_as_display_date'),
			'phone_number' => 									array('sort_column' => 'phone_number','label' => lang('common_phone_number'),'format_function' => 'tel','html' => TRUE),
			'comments' => 											array('sort_column' => 'comments','label' => lang('common_comments')),
			'address_1' => 											array('sort_column' => 'address_1','label' => lang('common_address_1')),
			'address_2' => 											array('sort_column' => 'address_2','label' => lang('common_address_2')),
			'city' => 													array('sort_column' => 'city','label' => lang('common_city')),
			'state' => 													array('sort_column' => 'state','label' => lang('common_state')),
			'zip' => 														array('sort_column' => 'zip','label' => lang('common_zip')),
			'country' => 												array('sort_column' => 'country','label' => lang('common_country')),
			'force_password_change' => 					array('sort_column' => 'force_password_change','label' => lang('employees_force_password_change_upon_login'),'format_function' => 'boolean_as_string'),
			'always_require_password' => 				array('sort_column' => 'always_require_password','label' => lang('employees_always_require_password'),'format_function' => 'boolean_as_string'),
			'inactive' => 											array('sort_column' => 'inactive','label' => lang('employees_inactive'),'format_function' => 'boolean_as_string'),
			'reason_inactive' => 											array('sort_column' => 'reason_inactive','label' => lang('employees_reason_inactive')),
			'language' => 											array('sort_column' => 'language','label' => lang('common_language'),'format_function' => 'ucwords'),
			'commission_percent' => 						array('sort_column' => 'commission_percent','label' => lang('common_commission_default_rate'),'format_function' => 'to_quantity'),
			'commission_percent_type' => 				array('sort_column' => 'commission_percent_type','label' => lang('items_commission_percent_type'),'format_function' => 'commission_percent_type_formater'),
			'hourly_pay_rate' => 								array('sort_column' => 'hourly_pay_rate','label' => lang('common_hourly_pay_rate'),'format_function' => 'to_currency'),			
			'not_required_to_clock_in' => 								array('sort_column' => 'not_required_to_clock_in','label' => lang('employees_not_required_to_clock_in'),'format_function' => 'boolean_as_string'),			
		);
		
		for($k=1;$k<=NUMBER_OF_PEOPLE_CUSTOM_FIELDS;$k++)
		{
			if($this->get_custom_field($k) !== false)
			{
				$field = array();
				$field['sort_column'] = "custom_field_${k}_value";
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
		
	function get_time_off_request($id)
	{
		$this->db->from('employees_time_off');	
		$this->db->where('id',$id);
		return $this->db->get()->row_array();
	}
	
	function request_time_off($data,$id = false)
	{
		if ($id)
		{
			$this->db->where('id',$id);
			return $this->db->update('employees_time_off',$data);
		}
		
		return $this->db->insert('employees_time_off',$data);
	}
	
	function approve_time_off($id)
	{
		$request = $this->get_time_off_request($id);
		
		if ($request['is_paid'])
		{
			$this->add_pto($request);
		}
		
		$this->db->where('id',$id);
		return $this->db->update('employees_time_off',array('approved' => 1,'employee_approved_person_id' => $this->get_logged_in_employee_info()->person_id));
	}
	
	function add_pto($request)
	{
		$employee_id = $request['employee_requested_person_id'];
		$cur_emp_info = $this->get_info($employee_id);
		$location_id = $request['employee_requested_location_id'];
		$clock_in = date('Y-m-d H:i:s',strtotime($request['end_day']));
		$clock_out = date('Y-m-d H:i:s', strtotime('+'.to_quantity($request['hours_requested']*60).' minutes',strtotime($request['end_day'])));
		return $this->db->insert('employees_time_clock', array(
			'employee_id' => $employee_id,
			'location_id' => $location_id,
			'clock_in' => $clock_in,
			'clock_out' => $clock_out,
			'clock_in_comment' => '',
			'clock_out_comment' => $request['reason'],
			'hourly_pay_rate' => $cur_emp_info->hourly_pay_rate ? $cur_emp_info->hourly_pay_rate : 0,
			'ip_address_clock_in' => $this->input->ip_address(),
		));
	}
	
	function get_action_wise_employee_location($employee_id, $module_id = null){
		$this->db->from('permissions_locations');
		$this->db->where('person_id',$employee_id);
		$data['permissions_locations'] = $this->db->get()->result();
		
		
		if($module_id != null){
			$this->db->from('permissions_actions_locations');
			$this->db->where(array('person_id' => $employee_id, 'module_id' => $module_id));
			$data['permissions_actions_locations'] = $this->db->get()->result();
		}else{
			$this->db->from('permissions_actions_locations');
			$this->db->where('person_id',$employee_id);
			$data['permissions_actions_locations'] = $this->db->get()->result();
		}

		return $data;
	}
	
	
	function check_action_has_employee_location($result_set, $module_id, $action_id, $location_id){
		$result_set = $result_set['permissions_actions_locations'];
		foreach($result_set as $rk => $rv){
			if($module_id == $rv->module_id && $action_id == $rv->action_id && $location_id == $rv->location_id){
				return true;
			}
		}
		return false;
	}
	
	function check_module_has_location($result_set, $module_id, $location_id){
		$result_set = $result_set['permissions_locations'];
		foreach($result_set as $rk => $rv){
			if($module_id == $rv->module_id && $location_id == $rv->location_id){
				return true;
			}
		}
		return false;
	}
	
	
	function get_employee_module_wise_location($person_id, $module_id){
		$this->db->select('locations.location_id,locations.name as location_name');
		$this->db->from('permissions_locations');
		$this->db->join('locations', 'locations.location_id = permissions_locations.location_id','left');
		$this->db->where(array('person_id' => $person_id, 'module_id' => $module_id));
		$query = $this->db->get();
		return $query->result_array();
	}
	
	function get_employee_module_action_wise_location($person_id, $module_id, $action_id){
		$this->db->select('locations.location_id,locations.name as location_name');
		$this->db->from('permissions_actions_locations');
		$this->db->join('locations', 'locations.location_id = permissions_actions_locations.location_id','left');
		$this->db->where(array('person_id' => $person_id, 'module_id' => $module_id, 'action_id' => $action_id));
		$query = $this->db->get();
		return $query->result_array();
	}
	
	function datatable_language(){
		$table_lang = array(
	    "sEmptyTable" =>     lang('common_not_found'),
	    "sInfo" =>           lang('common_showing')." _START_ ".lang('common_to')." _END_ ".lang('common_of')." _TOTAL_ ".lang('common_entries'),
	    "sInfoEmpty" =>      lang('common_showing')." 0 ".lang('common_to')." 0 ".lang('common_of')." 0 ".lang('common_entries'),
	    "sInfoFiltered" =>   "(".lang('common_filtered')." ".lang('common_from')." _MAX_ ".lang('common_total')." ".lang('common_entries').")",
	    //"sInfoPostFix" =>    "",
	    //"sThousands" =>      ",",
	    "sLengthMenu" =>     lang("common_show")." _MENU_ ".lang("common_entries"),
	    "sLoadingRecords" => lang("common_loading"),
	    "sProcessing" =>     lang("common_processing"),
	    "sSearch" =>         lang("common_search").":",
	    "sZeroRecords" =>    lang('common_not_found'),
	    "oPaginate" => array(
	        "sFirst" =>      lang('common_first'),
	        "sLast" =>       lang('common_last'),
	        "sNext" =>       lang('common_next'),
	        "sPrevious" =>   lang('common_previous')
	    ),
	    /*
	    "oAria" => array(
	        "sSortAscending" =>  ": activate to sort column ascending",
	        "sSortDescending" => ": activate to sort column descending"
	    )
	    */
		);
		
		return json_encode($table_lang);
	}

	//Santosh Changes
	function get_inventory_count_columns_to_display()
	{
		static $has_cost_price_permission;
		
		if (!$has_cost_price_permission)
		{
			$has_cost_price_permission = $this->has_module_action_permission('items','see_cost_price', $this->get_logged_in_employee_info()->person_id);
		}
		
		$this->load->model('Inventory');
		
		$all_columns = $this->Inventory->get_displayable_columns();
		
		
		$columns_to_display = array();
		
		$this->load->model('Employee_appconfig');
		if ($choices = $this->Employee_appconfig->get('item_count_column_prefs'))
		{
			$columns_to_display_keys = unserialize($choices);
		}
		else
		{
			$columns_to_display_keys = $this->Inventory->get_default_columns();

		}
		
		foreach($columns_to_display_keys as $key)
		{
			if (isset($all_columns[$key]))
			{
				$columns_to_display[$key] = $all_columns[$key];
			}
		}
		
		if (!$has_cost_price_permission)
		{
			if (isset($columns_to_display['cost_price']))
			{
				unset($columns_to_display['cost_price']);
			}

			if (isset($columns_to_display['location_cost_price']))
			{
				unset($columns_to_display['location_cost_price']);
			}
		}
		
		return $columns_to_display;
	}
	
	function get_item_not_count_columns_to_display()
	{
		static $has_cost_price_permission;
		
		if (!$has_cost_price_permission)
		{
			$has_cost_price_permission = $this->has_module_action_permission('items','see_cost_price', $this->get_logged_in_employee_info()->person_id);
		}
		
		$this->load->model('Inventory');
		
		$all_columns = $this->Inventory->get_item_not_count_displayable_columns();
		
		
		$columns_to_display = array();
		
		$this->load->model('Employee_appconfig');
		if ($choices = $this->Employee_appconfig->get('item_not_count_column_prefs'))
		{
			$columns_to_display_keys = unserialize($choices);
		}
		else
		{
			$columns_to_display_keys = $this->Inventory->get_item_not_count_default_columns();

		}
		
		foreach($columns_to_display_keys as $key)
		{
			if (isset($all_columns[$key]))
			{
				$columns_to_display[$key] = $all_columns[$key];
			}
		}
		
		if (!$has_cost_price_permission)
		{
			if (isset($columns_to_display['cost_price']))
			{
				unset($columns_to_display['cost_price']);
			}

			if (isset($columns_to_display['location_cost_price']))
			{
				unset($columns_to_display['location_cost_price']);
			}
		}
		
		return $columns_to_display;
	}
	//END
	
	function get_work_order_columns_to_display()
	{
		$this->load->model('Work_order');
		$all_columns = $this->Work_order->get_displayable_columns();
		
		$columns_to_display = array();
		
		$this->load->model('Employee_appconfig');
		if ($choices = $this->Employee_appconfig->get('work_orders_column_prefs'))
		{
			$columns_to_display_keys = unserialize($choices);
		}
		else
		{
			$columns_to_display_keys = $this->Work_order->get_default_columns();

		}
		
		foreach($columns_to_display_keys as $key)
		{
			$columns_to_display[$key] = $all_columns[$key];
		}
	
		return $columns_to_display;
		
	}	

	function get_logged_employees_locations()
	{	
		$employee_id 			= $this->session->userdata('person_id');
		$employees_locations 	= $this->get_authenticated_location_ids($employee_id);

		foreach ($employees_locations as $key => $location) {
			$this->db->from('locations');
			$this->db->where('location_id', $location);
			$data[$key] = $this->db->get()->row();
		}
		return $data;
	}
	
}
?>
