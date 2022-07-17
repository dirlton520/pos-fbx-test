<?php
class Item_kit extends MY_Model
{
	/*
	Determines if a given item_id is an item kit
	*/
	function exists($item_kit_id)
	{
		$this->db->from('item_kits');
		$this->db->where('item_kit_id',$item_kit_id);
		$query = $this->db->get();

		return ($query->num_rows()==1);
	}

	
	function get_custom_field($number,$key="name")
	{
		static $config_data;
		
		if (!$config_data)
		{
			$config_data = unserialize($this->config->item('item_kit_custom_field_prefs'));
		}
		
		return isset($config_data["custom_field_${number}_${key}"]) && $config_data["custom_field_${number}_${key}"] ? $config_data["custom_field_${number}_${key}"] : FALSE;
	}
	
	
	function get_quantity_to_be_added_from_kit($kit_id, $item_id,$quantity)
	{		
		$item_id = str_replace('|FORCE_ITEM_ID|','',$item_id);
		$item_kit_items = $this->Item_kit_items->get_info($kit_id);
		
		foreach ($item_kit_items as $item_kit_item)
		{
			if ($item_id == $item_kit_item->item_id)
			{
				return $quantity * $item_kit_item->quantity;
			}
		}
		
		return 0;
	}

	/*
	Returns all the item kits
	*/
	function get_all($deleted= 0,$limit=10000, $offset=0,$col='name',$ord='asc')
	{
		if (!$deleted)
		{
			$deleted = 0;
		}
		
		$current_location=$this->Employee->get_logged_in_employee_current_location_id() ? $this->Employee->get_logged_in_employee_current_location_id() : 1;
		$see_all_item_kits = $this->Employee->has_module_action_permission('item_kits','see_all_item_kits',$this->Employee->get_logged_in_employee_info()->person_id);

		$this->db->select('item_kits.*, categories.name as category,
		location_item_kits.unit_price as location_unit_price,
		location_item_kits.cost_price as location_cost_price,
		tax_classes.name as tax_group
		');
		$this->db->from('item_kits');
		$this->db->join('tax_classes', 'tax_classes.id = item_kits.tax_class_id', 'left');
		$this->db->join('categories', 'categories.id = item_kits.category_id','left');
		$this->db->join('location_item_kits', 'location_item_kits.item_kit_id = item_kits.item_kit_id and location_item_kits.location_id = '.$current_location, 'left');
		if(!$see_all_item_kits){
			$this->db->join('location_ban_item_kits', 'location_ban_item_kits.item_kit_id = item_kits.item_kit_id and location_ban_item_kits.location_id = '.$current_location, 'left');
			$this->db->where('location_ban_item_kits.location_id IS NULL');
		}
		$this->db->where('item_kits.deleted',$deleted);

		if (!$this->config->item('speed_up_search_queries'))
		{
			$this->db->order_by($col, $ord);
		}
		$this->db->limit($limit);
		$this->db->offset($offset);
		return $this->db->get();
	}

	function count_all($deleted = 0)
	{
		
		if (!$deleted)
		{
			$deleted = 0;
		}
		$location_id =$this->Employee->get_logged_in_employee_current_location_id() ? $this->Employee->get_logged_in_employee_current_location_id() : 1;
		$see_all_item_kits = $this->Employee->has_module_action_permission('item_kits','see_all_item_kits',$this->Employee->get_logged_in_employee_info()->person_id);
		
		$this->db->from('item_kits');

		if(!$see_all_item_kits){
			$this->db->join('location_ban_item_kits', 'location_ban_item_kits.item_kit_id = item_kits.item_kit_id and location_ban_item_kits.location_id ='.$location_id, 'left');
			$this->db->where('location_ban_item_kits.location_id IS NULL');
		}

		$this->db->where('deleted',$deleted);
		return $this->db->count_all_results();
	}

	/*
	Gets information about a particular item kit
	*/
	function get_info($item_kit_id, $can_cache = TRUE)
	{
		if ($can_cache)
		{
			static $cache  = array();
		}		
		else
		{
			$cache = array();
		}
		
		if (is_array($item_kit_id))
		{
			$item_kits = $this->get_multiple_info($item_kit_id)->result();
			
			foreach($item_kits as $item_kit)
			{
				$cache[$item_kit->item_kit_id] = $item_kit;
				
			}
			
			return $item_kits;
		}
		else
		{
			if (isset($cache[$item_kit_id]))
			{
				return $cache[$item_kit_id];
			}
		}
		
		//If we are NOT an int return empty item
		if (!is_numeric($item_kit_id))
		{
			//Get empty base parent object, as $item_kit_id is NOT an item kit
			$item_obj=new stdClass();

			//Get all the fields from items table
			$fields = array('barcode_name','info_popup','item_kit_id','item_kit_number','product_id','name','category_id','manufacturer_id','description','tax_included','unit_price','cost_price','override_default_tax','is_ebt_item','commission_percent','commission_percent_type','commission_fixed','change_cost_price','disable_loyalty','deleted','tax_class_id','max_discount_percent','max_edit_price','min_edit_price','custom_field_1_value','custom_field_2_value','custom_field_3_value','custom_field_4_value','custom_field_5_value','custom_field_6_value','custom_field_7_value','custom_field_8_value','custom_field_9_value','custom_field_10_value','required_age','verify_age','allow_price_override_regardless_of_permissions','only_integer','is_barcoded','item_kit_inactive','default_quantity','dynamic_pricing','is_favorite','loyalty_multiplier');


			foreach ($fields as $field)
			{
				$item_obj->$field='';
			}

			return $item_obj;
		}

		//KIT #
		$pieces = explode(' ',$item_kit_id);

		if (count($pieces) == 2)
		{
			$item_kit_id = (int)$pieces[1];
		}

		$this->db->from('item_kits');
		$this->db->where('item_kit_id',$item_kit_id);

		$query = $this->db->get();

		if($query->num_rows()==1)
		{
			$cache[$item_kit_id] = $query->row();
			return $cache[$item_kit_id];
		}
		else
		{
			//Get empty base parent object, as $item_kit_id is NOT an item kit
			$item_obj=new stdClass();

			//Get all the fields from items table
			$fields = array('barcode_name','info_popup','item_kit_id','item_kit_number','product_id','name','category_id','manufacturer_id','description','tax_included','unit_price','cost_price','override_default_tax','is_ebt_item','commission_percent','commission_percent_type','commission_fixed','change_cost_price','disable_loyalty','deleted','tax_class_id','max_discount_percent','max_edit_price','min_edit_price','custom_field_1_value','custom_field_2_value','custom_field_3_value','custom_field_4_value','custom_field_5_value','custom_field_6_value','custom_field_7_value','custom_field_8_value','custom_field_9_value','custom_field_10_value','required_age','verify_age','allow_price_override_regardless_of_permissions','only_integer','is_barcoded','item_kit_inactive','default_quantity','dynamic_pricing','is_favorite','loyalty_multiplier');
				
			foreach ($fields as $field)
			{
				$item_obj->$field='';
			}

			return $item_obj;
		}
	}
	
	function check_duplicate($term)
	{
			$this->db->from('item_kits');
			$this->db->where('deleted',0);
			$query = $this->db->where("name = ".$this->db->escape($term));
			$query=$this->db->get();
			
			if($query->num_rows()>0)
			{
				return true;
			}
	}
		
	//returns an int or false
	public function lookup_item_kit_id($item_kit_identifer)
	{
		$result = false;
    	$item_lookup_order = unserialize($this->config->item('item_lookup_order'));
		foreach($item_lookup_order as $item_lookup_number)
		{
			switch ($item_lookup_number) 
			{
		    case 'item_id':
						$result = $this->lookup_item_kit_by_item_kit_id($item_kit_identifer);
		        break;
		    case 'item_number':
		        $result = $this->lookup_item_kit_by_item_kit_number($item_kit_identifer);
		        break;
		    case 'product_id':
			      $result = $this->lookup_item_kit_by_product_id($item_kit_identifer);
		        break;
			}
			
			if ($result !== FALSE)
			{
				$location_id= $this->Employee->get_logged_in_employee_current_location_id() ? $this->Employee->get_logged_in_employee_current_location_id() : 1;
				if($this->is_item_kit_ban($result, $location_id))
				{
					$result = FALSE;
				}else{
					return $result;
				}
			}
		}
		
		return FALSE;
	}

	private function lookup_item_kit_by_item_kit_id($item_kit_id)
	{
		if (does_contain_only_digits($item_kit_id))
		{
			if($this->exists($item_kit_id))
			{
				return (int)$item_kit_id;
			}	
	
		}	
		return false;
	}
	
	//return item_id
	private function lookup_item_kit_by_item_kit_number($item_kit_number)
	{
		$this->db->from('item_kits');
		$this->db->where('item_kit_number',$item_kit_number);

		$query = $this->db->get();

		if($query->num_rows() >= 1)
		{
			return $query->row()->item_kit_id;
		}
		
		return false;
	}
	
	private function lookup_item_kit_by_product_id($product_id)
	{
		$this->db->from('item_kits');
		$this->db->where('product_id', $product_id); 

		$query = $this->db->get();

		if($query->num_rows() >= 1)
		{
			return $query->row()->item_kit_id;
		}
		
		return false;
	}
	
	/*
	Get an item_kit_id given an item kit number
	*/
	function get_item_kit_id($item_kit_number)
	{
		$this->db->from('item_kits');
		$this->db->where('item_kit_number',$item_kit_number);
		$this->db->or_where('product_id', $item_kit_number);
		$query = $this->db->get();

		if($query->num_rows() >= 1)
		{
			return $query->row()->item_kit_id;
		}

		return false;
	}
	
	/*
	Gets information about multiple item kits
	*/
	function get_multiple_info($item_kit_ids)
	{
		$this->db->from('item_kits');
		if (!empty($item_kit_ids))
		{
			$this->db->group_start();
			$item_kit_ids_chunk = array_chunk($item_kit_ids,25);
			foreach($item_kit_ids_chunk as $item_kit_ids)
			{
				$this->db->or_where_in('item_kit_id',$item_kit_ids);
			}
			$this->db->group_end();
		}
		else
		{
			$this->db->where('1', '2', FALSE);
		}
		
		$this->db->order_by("name", "asc");
		return $this->db->get();
	}
	
	function get_next_id($item_kit_id)
	{
		$item_kits_table = $this->db->dbprefix('item_kits');
		$result = $this->db->query("SELECT item_kit_id FROM $item_kits_table WHERE item_kit_id = (select min(item_kit_id) from $item_kits_table where deleted = 0 and item_kit_id > ".$this->db->escape($item_kit_id).")");

		if($result->num_rows() > 0)
		{
			$row = $result->result();
			return $row[0]->item_kit_id;
		}

		return FALSE;
	}

	function get_prev_id($item_kit_id)
	{
		$item_kits_table = $this->db->dbprefix('item_kits');
		$result = $this->db->query("SELECT item_kit_id FROM $item_kits_table WHERE item_kit_id = (select max(item_kit_id) from $item_kits_table where deleted = 0 and item_kit_id < ".$this->db->escape($item_kit_id).")");

		if($result->num_rows() > 0)
		{
			$row = $result->result();
			return $row[0]->item_kit_id;
		}

		return FALSE;
	}

	/*
	Inserts or updates an item kit
	*/
	function save(&$item_kit_data,$item_kit_id=false)
	{
		if (empty($item_kit_data))
		{
			return true;
		}
		
		if (!$item_kit_id or !$this->exists($item_kit_id))
		{
			if($this->db->insert('item_kits',$item_kit_data))
			{
				$item_kit_data['item_kit_id']=$this->db->insert_id();
				
				if (isset($item_kit_data['unit_price']) || isset($item_kit_data['cost_price']))
				{
					$this->save_price_history($item_kit_data['item_kit_id'],NULL,isset($item_kit_data['unit_price']) ? $item_kit_data['unit_price'] : NULL,isset($item_kit_data['cost_price']) ? $item_kit_data['cost_price'] : NULL, TRUE);
				}
				return true;
			}
			return false;
		}


		if (isset($item_kit_data['unit_price']) || isset($item_kit_data['cost_price']))
		{
			$this->save_price_history($item_kit_id,NULL,isset($item_kit_data['unit_price']) ? $item_kit_data['unit_price'] : NULL,isset($item_kit_data['cost_price']) ? $item_kit_data['cost_price'] : NULL);
		}

		$this->db->where('item_kit_id', $item_kit_id);
		return $this->db->update('item_kits',$item_kit_data);
	}

	/*
	Deletes one item kit
	*/
	function delete($item_kit_id)
	{
		$this->db->where('item_kit_id', $item_kit_id);
		return $this->db->update('item_kits', array('deleted' => 1));
	}

	/*
	Deletes a list of item kits
	*/
	function delete_list($item_kit_ids)
	{
		$this->db->where_in('item_kit_id',$item_kit_ids);
		return $this->db->update('item_kits', array('deleted' => 1));
 	}

	/*
	Deletes one item kit
	*/
	function undelete($item_kit_id)
	{
		$this->db->where('item_kit_id', $item_kit_id);
		return $this->db->update('item_kits', array('deleted' => 0));
	}

	/*
	Deletes a list of item kits
	*/
	function undelete_list($item_kit_ids)
	{
		$this->db->where_in('item_kit_id',$item_kit_ids);
		return $this->db->update('item_kits', array('deleted' => 0));
 	}
	
 	/*
	Get search suggestions to find kits
	*/
	function get_manage_item_kits_search_suggestions($search,$deleted=0,$limit=25)
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

		$location_id= $this->Employee->get_logged_in_employee_current_location_id() ? $this->Employee->get_logged_in_employee_current_location_id() : 1;
		$see_all_item_kits = $this->Employee->has_module_action_permission('item_kits','see_all_item_kits',$this->Employee->get_logged_in_employee_info()->person_id);
		
		for($k=1;$k<=NUMBER_OF_PEOPLE_CUSTOM_FIELDS;$k++)
		{
			if ($this->get_custom_field($k)) 
			{
				$this->load->helper('date');
				if ($this->get_custom_field($k,'type') != 'date')
				{
					$this->db->select('custom_field_'.$k.'_value as custom_field, item_kits.*, categories.name as category', false);						
				}
				else
				{
					$this->db->select('FROM_UNIXTIME(custom_field_'.$k.'_value, "'.get_mysql_date_format().'") as custom_field, item_kits.*, categories.name as category', false);
				}
				$this->db->join('categories', 'categories.id = item_kits.category_id','left');
				
				$this->db->from('item_kits');

				$this->db->join('location_ban_item_kits', 'location_ban_item_kits.item_kit_id = item_kits.item_kit_id and location_ban_item_kits.location_id ='.$location_id, 'left');
				$this->db->where('location_ban_item_kits.location_id IS NULL');

				$this->db->where('item_kits.deleted',$deleted);
			
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
						'avatar' => base_url()."assets/img/user.png" 
					);
					$data['label'] = $row->custom_field;
					
					$temp_suggestions[$row->item_kit_id] = $data;

				}
		
				foreach($temp_suggestions as $key => $value)
				{
					$suggestions[]=array('value'=> $key, 'label' => $value['name'],'avatar'=>$value['avatar'],'subtitle'=>'');		
				}
			}			
		}
		
			$this->db->select('item_kits.*, categories.name as category');
			$this->db->from('item_kits');
			$this->db->join('categories', 'categories.id = item_kits.category_id','left');
			if(!$see_all_item_kits){
				$this->db->join('location_ban_item_kits', 'location_ban_item_kits.item_kit_id = item_kits.item_kit_id and location_ban_item_kits.location_id = '.$location_id,'left');
			}
			$this->db->like('item_kits.name',$search,!$this->config->item('speed_up_search_queries') ? 'both' : 'after');
			$this->db->where('item_kits.deleted',$deleted);
			if(!$see_all_item_kits){
				$this->db->where('location_ban_item_kits.location_id IS NULL',NULL);
			}
			$this->db->limit($limit);
			$by_name = $this->db->get();
			$temp_suggestions = array();
			foreach($by_name->result() as $row)
			{
				$data = array(
					'name' => $row->name,
					'subtitle' => $row->category,
					'avatar' => base_url()."assets/img/user.png" 
					 );
				$temp_suggestions[$row->item_kit_id] = $data;
			}
		
			foreach($temp_suggestions as $key => $value)
			{
				$suggestions[]=array('value'=> $key, 'label' => $value['name'],'avatar'=>$value['avatar'],'subtitle'=>$value['subtitle'] ? $value['subtitle'] : lang('common_none'));		
			}

			$this->db->select('item_kits.*, categories.name as category');
			$this->db->from('item_kits');
			$this->db->join('categories', 'categories.id = item_kits.category_id','left');
			$this->db->join('location_ban_item_kits', 'location_ban_item_kits.item_kit_id = item_kits.item_kit_id and location_ban_item_kits.location_id = '.$location_id,'left');
			$this->db->like('item_kit_number',$search,!$this->config->item('speed_up_search_queries') ? 'both' : 'after');
			$this->db->where('item_kits.deleted',$deleted);
			$this->db->where('location_ban_item_kits.location_id IS NULL', NULL);

			$this->db->limit($limit);
			$by_item_kit_number = $this->db->get();
			$temp_suggestions = array();
			foreach($by_item_kit_number->result() as $row)
			{
				$data = array(
					'name' => $row->item_kit_number,
					'subtitle' => $row->category,
					'avatar' => base_url()."assets/img/user.png" 
					 );
				$temp_suggestions[$row->item_kit_id] = $data;
			}

			foreach($temp_suggestions as $key => $value)
			{
				$suggestions[]=array('value'=> $key, 'label' => $value['name'],'avatar'=>$value['avatar'],'subtitle'=>$value['subtitle'] ? $value['subtitle'] : lang('common_none'));		
			}

			$this->db->select('item_kits.*, categories.name as category');
			$this->db->from('item_kits');
			$this->db->join('categories', 'categories.id = item_kits.category_id','left');
			$this->db->join('location_ban_item_kits', 'location_ban_item_kits.item_kit_id = item_kits.item_kit_id and location_ban_item_kits.location_id = '.$location_id,'left');
			$this->db->like('product_id',$search,!$this->config->item('speed_up_search_queries') ? 'both' : 'after');
			$this->db->where('item_kits.deleted',$deleted);
			$this->db->where('location_ban_item_kits.location_id IS NULL',NULL);
			$this->db->limit($limit);
			$by_product_id = $this->db->get();
			$temp_suggestions = array();
			foreach($by_product_id->result() as $row)
			{
				$data = array(
					'name' => $row->product_id,
					'subtitle' => $row->category,
					'avatar' => base_url()."assets/img/user.png" 
					 );
				$temp_suggestions[$row->item_kit_id] = $data;
			}

			foreach($temp_suggestions as $key => $value)
			{
				$suggestions[]=array('value'=> $key, 'label' => $value['name'],'avatar'=>$value['avatar'],'subtitle'=>$value['subtitle'] ? $value['subtitle'] : lang('common_none'));		

			}

			$this->db->from('item_kits_tags');
			$this->db->join('tags', 'item_kits_tags.tag_id=tags.id');
			$this->db->like('name',$search,!$this->config->item('speed_up_search_queries') ? 'both' : 'after');
			$this->db->where('deleted',$deleted);
			$this->db->limit($limit);

			$by_tags = $this->db->get();
			$temp_suggestions = array();

			foreach($by_tags->result() as $row)
			{
				$data = array(
					'name' => $row->name,
					'subtitle' => '',
					'avatar' => base_url()."assets/img/user.png" 
					 );
				$temp_suggestions[$row->item_kit_id] = $data;
			}


			foreach($temp_suggestions as $key => $value)
			{
				$suggestions[]=array('value'=> $key, 'label' => $value['name'],'avatar'=>$value['avatar'],'subtitle'=>$value['subtitle'] ? $value['subtitle'] : lang('common_none'));		

			}
		
		//only return $limit suggestions
		$suggestions = array_map("unserialize", array_unique(array_map("serialize", $suggestions)));
		if(count($suggestions) > $limit)
		{
			$suggestions = array_slice($suggestions, 0,$limit);
		}
		return $suggestions;

	}
	
	function get_item_kit_search_suggestions_sales_recv($search,$deleted=0,$price_field = 'unit_price', $limit=25,$hide_inactive = false)
	{
		if (!trim($search))
		{
			return array();
		}
		
		$location_id= $this->Employee->get_logged_in_employee_current_location_id() ? $this->Employee->get_logged_in_employee_current_location_id() : 1;
		
		if ($price_field == 'cost_price')
		{
			$has_cost_price_permission = $this->Employee->has_module_action_permission('item_kits','see_cost_price', $this->Employee->get_logged_in_employee_info()->person_id);
			
			if (!$has_cost_price_permission)
			{
				$price_field = FALSE;
			}
		}
		
		
		if (!$deleted)
		{
			$deleted = 0;
		}
		
		$suggestions = array();
		
			$this->db->select("item_kits.*,categories.name as category", false);
			$this->db->from('item_kits');
			$this->db->join('categories', 'categories.id = item_kits.category_id','left');
			$this->db->join('location_ban_item_kits', 'location_ban_item_kits.item_kit_id = item_kits.item_kit_id and location_ban_item_kits.location_id = '.$location_id,'left');
			$this->db->like($this->db->dbprefix('item_kits').'.name', $search,!$this->config->item('speed_up_search_queries') ? 'both' : 'after');
			$this->db->where('item_kits.deleted',$deleted);
			$this->db->where('location_ban_item_kits.location_id IS NULL',NULL);

			if ($hide_inactive)
			{
				$this->db->where('item_kit_inactive',0);
			}
			$this->db->limit($limit);
			$by_name = $this->db->get();

			$temp_suggestions = array();
		
			foreach($by_name->result() as $row)
			{
				$data = array(
					'image' => $row->main_image_id ?  app_file_url($row->main_image_id) : base_url()."assets/img/item-kit.png" ,
					'category' => $row->category,
					'item_kit_number' => $row->item_kit_number,
				);

				if ($row->category)
				{
					$data['label'] = $row->name . ' ('.$row->category.') - '.($price_field ? to_currency($row->$price_field) : '');
					$temp_suggestions['KIT '.$row->item_kit_id] =  $data;
				}
				else
				{
					$data['label'] = $row->name.' - '.($price_field ? to_currency($row->$price_field) : '');
					$temp_suggestions['KIT '.$row->item_kit_id] = $data;
				}
			}
			$this->load->helper('array');
			uasort($temp_suggestions, 'sort_assoc_array_by_label');
			
			foreach($temp_suggestions as $key => $value)
			{
				$suggestions[]=array('value'=> $key, 'label' => $value['label'],  'image' => $value['image'], 'category' => $value['category'], 'item_kit_number' => $value['item_kit_number']);		
			}
		
			$this->db->select("item_kits.*,categories.name as category", false);
			$this->db->from('item_kits');
			$this->db->join('categories', 'categories.id = item_kits.category_id','left');
			$this->db->join('location_ban_item_kits', 'location_ban_item_kits.item_kit_id = item_kits.item_kit_id and location_ban_item_kits.location_id = '.$location_id,'left');
			$this->db->like($this->db->dbprefix('item_kits').'.item_kit_number', $search,!$this->config->item('speed_up_search_queries') ? 'both' : 'after');
			$this->db->where('item_kits.deleted',$deleted);
			$this->db->where('location_ban_item_kits.location_id IS NULL',NULL);
			if ($hide_inactive)
			{
				$this->db->where('item_kit_inactive',0);
			}
			
			$this->db->limit($limit);
			$by_item_kit_number = $this->db->get();
			

			$temp_suggestions = array();
		
			foreach($by_item_kit_number->result() as $row)
			{
				$data = array(
						'label' => $row->item_kit_number.' - '.($price_field ? to_currency($row->$price_field) : ''),
						'image' => $row->main_image_id ?  app_file_url($row->main_image_id) : base_url()."assets/img/item-kit.png" ,
						'category' => $row->category,
						'item_kit_number' => $row->item_kit_number,
					);

				$temp_suggestions['KIT '.$row->item_kit_id] = $data;
			}
			
			uasort($temp_suggestions, 'sort_assoc_array_by_label');
			
			foreach($temp_suggestions as $key => $value)
			{
				$suggestions[]=array('value'=> $key, 'label' => $value['label'],  'image' => $value['image'], 'category' => $value['category'], 'item_kit_number' => $value['item_kit_number']);		
			}

			$this->db->select("item_kits.*,categories.name as category", false);
			$this->db->from('item_kits');
			$this->db->join('categories', 'categories.id = item_kits.category_id','left');
			$this->db->join('location_ban_item_kits', 'location_ban_item_kits.item_kit_id = item_kits.item_kit_id and location_ban_item_kits.location_id = '.$location_id,'left');
			$this->db->like($this->db->dbprefix('item_kits').'.product_id', $search,!$this->config->item('speed_up_search_queries') ? 'both' : 'after');
			$this->db->where('item_kits.deleted',$deleted);
			$this->db->where('location_ban_item_kits.location_id IS NULL',NULL);
			if ($hide_inactive)
			{
				$this->db->where('item_kit_inactive',0);
			}

			$this->db->limit($limit);
		
			$by_product_id = $this->db->get();
		
			$temp_suggestions = array();
		
			foreach($by_product_id->result() as $row)
			{
				$data = array(
						'label' => $row->product_id.' - '.($price_field ? to_currency($row->$price_field) : ''),
						'image' => $row->main_image_id ?  app_file_url($row->main_image_id) : base_url()."assets/img/item-kit.png" ,
						'category' => $row->category,
						'item_kit_number' => $row->item_kit_number,
					);

				$temp_suggestions['KIT '.$row->item_kit_id] = $data;
			}
			
			uasort($temp_suggestions, 'sort_assoc_array_by_label');
			
			foreach($temp_suggestions as $key => $value)
			{
				$suggestions[]=array('value'=> $key, 'label' => $value['label'], 'image' => $value['image'], 'category' => $value['category'], 'item_kit_number' => $value['item_kit_number']);		
			}
		
		for($k=count($suggestions)-1;$k>=0;$k--)
		{
			if (!$suggestions[$k]['label'])
			{
				unset($suggestions[$k]);
			}
		}
		
		$suggestions = array_values($suggestions);
		
		//only return $limit suggestions
		$suggestions = array_map("unserialize", array_unique(array_map("serialize", $suggestions)));
		if(count($suggestions) > $limit)
		{
			$suggestions = array_slice($suggestions, 0,$limit);
		}
		
		return $suggestions;

	}
	
	
	
	function search($search, $deleted=0,$category_id = false, $limit=20,$offset=0,$column='name',$orderby='asc', $fields = 'all')
	{
		
		$location_id= $this->Employee->get_logged_in_employee_current_location_id() ? $this->Employee->get_logged_in_employee_current_location_id() : 1;
		$see_all_item_kits = $this->Employee->has_module_action_permission('item_kits','see_all_item_kits',$this->Employee->get_logged_in_employee_info()->person_id);

		$custom_fields = array();
		for($k=1;$k<=NUMBER_OF_PEOPLE_CUSTOM_FIELDS;$k++)
		{					
			if ($this->get_custom_field($k) !== FALSE)
			{
				if ($this->get_custom_field($k,'type') != 'date')
				{
					$custom_fields[$k]=$this->db->dbprefix('item_kits').".custom_field_${k}_value LIKE '".$this->db->escape_like_str($search)."%' ESCAPE '!'";
				}
				else
				{							
					$custom_fields[$k]= "(".$this->db->dbprefix('item_kits').".custom_field_${k}_value IS NOT NULL and ".$this->db->dbprefix('item_kits').".custom_field_${k}_value != 0 and FROM_UNIXTIME(".$this->db->dbprefix('item_kits').".custom_field_${k}_value, '%Y-%m-%d') = ".$this->db->escape(date('Y-m-d', strtotime($search))).')';					
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
		
		
		
		if (!$deleted)
		{
			$deleted = 0;
		}
		
		$current_location=$this->Employee->get_logged_in_employee_current_location_id() ? $this->Employee->get_logged_in_employee_current_location_id() : 1;
		
		if (!$this->config->item('speed_up_search_queries'))
		{
			$this->db->distinct();
		}
		
		if ($category_id)
		{
			if ($this->config->item('include_child_categories_when_searching_or_reporting'))
			{	
				$this->load->model('Category');
				
				$category_ids = $this->Category->get_category_id_and_children_category_ids_for_category_id($category_id);			
			}
			else
			{
				$category_ids = array($category_id);
			}
		}
		
		$this->db->select('item_kits.*, categories.name as category,
		location_item_kits.unit_price as location_unit_price,
		location_item_kits.cost_price as location_cost_price,tax_classes.name as tax_group');
		$this->db->from('item_kits');		
		$this->db->join('tax_classes', 'tax_classes.id = item_kits.tax_class_id', 'left');
		$this->db->join('location_item_kits', 'location_item_kits.item_kit_id = item_kits.item_kit_id and location_item_kits.location_id = '.$current_location, 'left');
		$this->db->join('item_kits_tags', 'item_kits_tags.item_kit_id = item_kits.item_kit_id', 'left');
		$this->db->join('tags', 'tags.id = item_kits_tags.tag_id', 'left');
		
		$this->db->join('categories', 'categories.id = item_kits.category_id','left');
		if(!$see_all_item_kits)
			$this->db->join('location_ban_item_kits', 'location_ban_item_kits.item_kit_id = item_kits.item_kit_id and location_ban_item_kits.location_id = '.$location_id,'left');
		
		if ($fields == $this->db->dbprefix('manufacturers').'.name')
		{
			$this->db->join('manufacturers', 'item_kits.manufacturer_id = manufacturers.id', 'left');
		}		
		
		if ($fields == 'all')
		{
			if ($search)
			{
					$this->db->where("(".$this->db->dbprefix('item_kits').".name LIKE '".(!$this->config->item('speed_up_search_queries') ? '%' : '').$this->db->escape_like_str($search).
					"%' ESCAPE '!' or item_kit_number LIKE '".(!$this->config->item('speed_up_search_queries') ? '%' : '').$this->db->escape_like_str($search)."%' ESCAPE '!'".
					" or phppos_item_kits.item_kit_id LIKE '".(!$this->config->item('speed_up_search_queries') ? '%' : '').$this->db->escape_like_str($search)."%' ESCAPE '!'".
					" or CONCAT('KIT ',phppos_item_kits.item_kit_id) LIKE '".(!$this->config->item('speed_up_search_queries') ? '%' : '').$this->db->escape_like_str($search)."%' ESCAPE '!'".
					"or product_id LIKE '".(!$this->config->item('speed_up_search_queries') ? '%' : '').$this->db->escape_like_str($search)."%' ESCAPE '!' or
					".$this->db->dbprefix('tags').".name LIKE '".(!$this->config->item('speed_up_search_queries') ? '%' : '').$this->db->escape_like_str($search)."%' ESCAPE '!' or
					description LIKE '".(!$this->config->item('speed_up_search_queries') ? '%' : '').$this->db->escape_like_str($search)."%' ESCAPE '!' or $custom_fields) and ".$this->db->dbprefix('item_kits').".deleted=$deleted");
			}
		}
		else
		{
			if ($search)
			{
				//Exact Match fields
				if ($fields == $this->db->dbprefix('item_kits').'.item_kit_id'|| $fields == $this->db->dbprefix('item_kits').'.cost_price' 
				|| $fields == $this->db->dbprefix('item_kits').'.unit_price' || $fields == $this->db->dbprefix('tags').'.name')
				{
					$this->db->where("$fields = ".$this->db->escape($search)." and ".$this->db->dbprefix('item_kits').".deleted=$deleted");								
				}
				else
				{
						$this->db->like($fields,$search,!$this->config->item('speed_up_search_queries') ? 'both' : 'after');
						$this->db->where($this->db->dbprefix('item_kits').".deleted=$deleted");																		
				}
			}
		}
		
		if(isset($category_ids) && !empty($category_ids)) 
		{
			$this->db->where_in('categories.id', $category_ids);
		}
		
		if (!$this->config->item('speed_up_search_queries'))
		{
			$this->db->order_by($column, $orderby);
		}
		
		if (!$search) //If we don't have a search make sure we filter out deleted items
		{
			$this->db->where('item_kits.deleted', $deleted);
		}
		if(!$see_all_item_kits)
			$this->db->where('location_ban_item_kits.location_id IS NULL',NULL);

		$this->db->limit($limit);
		$this->db->offset($offset);
		return $this->db->get();
	}
	

	function search_count_all($search, $deleted=0,$category_id = FALSE, $limit=10000)
	{
		$location_id= $this->Employee->get_logged_in_employee_current_location_id() ? $this->Employee->get_logged_in_employee_current_location_id() : 1;
		$custom_fields = array();
		for($k=1;$k<=NUMBER_OF_PEOPLE_CUSTOM_FIELDS;$k++)
		{					
			if ($this->get_custom_field($k) !== FALSE)
			{
				if ($this->get_custom_field($k,'type') != 'date')
				{
					$custom_fields[$k]=$this->db->dbprefix('item_kits').".custom_field_${k}_value LIKE '".$this->db->escape_like_str($search)."%' ESCAPE '!'";
				}
				else
				{							
					$custom_fields[$k]= "(".$this->db->dbprefix('item_kits').".custom_field_${k}_value IS NOT NULL and ".$this->db->dbprefix('item_kits').".custom_field_${k}_value != 0 and FROM_UNIXTIME(".$this->db->dbprefix('item_kits').".custom_field_${k}_value, '%Y-%m-%d') = ".$this->db->escape(date('Y-m-d', strtotime($search))).')';					
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
		
		
		if (!$deleted)
		{
			$deleted = 0;
		}
		
		if ($this->config->item('speed_up_search_queries'))
		{
			return $limit;
		}
		
		$see_all_item_kits = $this->Employee->has_module_action_permission('item_kits','see_all_item_kits',$this->Employee->get_logged_in_employee_info()->person_id);
		
		if ($category_id)
		{
			if ($this->config->item('include_child_categories_when_searching_or_reporting'))
			{	
				$this->load->model('Category');
				
				$category_ids = $this->Category->get_category_id_and_children_category_ids_for_category_id($category_id);			
			}
			else
			{
				$category_ids = array($category_id);
			}
		}
		
		
		$this->db->from('item_kits');
		$this->db->join('item_kits_tags', 'item_kits_tags.item_kit_id = item_kits.item_kit_id', 'left');
		$this->db->join('tags', 'tags.id = item_kits_tags.tag_id', 'left');
		$this->db->join('categories', 'categories.id = item_kits.category_id','left');
		if(!$see_all_item_kits)
			$this->db->join('location_ban_item_kits', 'location_ban_item_kits.item_kit_id = item_kits.item_kit_id and location_ban_item_kits.location_id = '.$location_id,'left');

		if ($search)
		{
			$this->db->where("(".$this->db->dbprefix('item_kits').".name LIKE '".(!$this->config->item('speed_up_search_queries') ? '%' : '').$this->db->escape_like_str($search).
			"%' ESCAPE '!' or item_kit_number LIKE '".(!$this->config->item('speed_up_search_queries') ? '%' : '').$this->db->escape_like_str($search)."%' ESCAPE '!'".
			" or phppos_item_kits.item_kit_id LIKE '".(!$this->config->item('speed_up_search_queries') ? '%' : '').$this->db->escape_like_str($search)."%' ESCAPE '!'".
			" or CONCAT('KIT ',phppos_item_kits.item_kit_id) LIKE '".(!$this->config->item('speed_up_search_queries') ? '%' : '').$this->db->escape_like_str($search)."%' ESCAPE '!'".
			"or product_id LIKE '".(!$this->config->item('speed_up_search_queries') ? '%' : '').$this->db->escape_like_str($search)."%' ESCAPE '!' or
			".$this->db->dbprefix('tags').".name LIKE '".(!$this->config->item('speed_up_search_queries') ? '%' : '').$this->db->escape_like_str($search)."%' ESCAPE '!' or
			description LIKE '".(!$this->config->item('speed_up_search_queries') ? '%' : '').$this->db->escape_like_str($search)."%' ESCAPE '!' or $custom_fields) and ".$this->db->dbprefix('item_kits').".deleted=$deleted");
		}
		else
		{
			$this->db->where('item_kits.deleted',$deleted);
		}

		if(!$see_all_item_kits)
			$this->db->where('location_ban_item_kits.location_id IS NULL',NULL);

		if(isset($category_ids) && !empty($category_ids)) 
		{
			$this->db->where_in('categories.id', $category_ids);
		}

		$result=$this->db->get();
		return $result->num_rows();
	}

	function get_tier_price_row($tier_id,$item_kit_id)
	{
		$this->db->from('item_kits_tier_prices');
		$this->db->where('tier_id',$tier_id);
		$this->db->where('item_kit_id ',$item_kit_id);
		return $this->db->get()->row();
	}

	function delete_tier_price($tier_id, $item_kit_id)
	{
		$this->db->where('tier_id', $tier_id);
		$this->db->where('item_kit_id', $item_kit_id);
		$this->db->delete('item_kits_tier_prices');
	}
	
	function delete_all_tier_prices($item_kit_id)
	{
		$this->db->where('item_kit_id', $item_kit_id);
		return $this->db->delete('item_kits_tier_prices');
	}

	function tier_exists($tier_id, $item_kit_id)
	{
		$this->db->from('item_kits_tier_prices');
		$this->db->where('tier_id',$tier_id);
		$this->db->where('item_kit_id',$item_kit_id);
		$query = $this->db->get();

		return ($query->num_rows()>=1);

	}

	function save_item_tiers($tier_data,$item_kit_id)
	{
		if($this->tier_exists($tier_data['tier_id'],$item_kit_id))
		{
			$this->db->where('tier_id', $tier_data['tier_id']);
			$this->db->where('item_kit_id', $item_kit_id);

			return $this->db->update('item_kits_tier_prices',$tier_data);

		}

		return $this->db->insert('item_kits_tier_prices',$tier_data);
	}
	
	function cleanup()
	{
		$item_kit_data = array('item_kit_number' => null, 'product_id' => null);
		$this->db->where('deleted', 1);
		return $this->db->update('item_kits',$item_kit_data);
	}
	
	function get_displayable_columns()
	{
		$this->lang->load('items');
		$this->load->helper('items');
		$return = array(
			'item_kit_id' => 										array('sort_column' => 'item_kits.item_kit_id', 'label' => lang('common_item_kit_id')),
			'item_kit_number' => 								array('sort_column' => 'item_kits.item_kit_number','label' => lang('common_item_number_expanded')),
			'product_id' => 										array('sort_column' => 'item_kits.product_id','label' => lang('common_product_id')),
			'name' => 													array('sort_column' => 'item_kits.name', 'label' => lang('common_name'), 'data_function' =>'item_kit_name_data_function', 'format_function' => 'item_kit_name_formatter','html' => TRUE),
			'barcode_name' => 									array('sort_column' => 'item_kits.barcode_name', 'label' => lang('common_barcode_name'), 'data_function' =>'item_kit_name_data_function', 'format_function' => 'item_kit_name_formatter','html' => TRUE),
			'category' => 											array('sort_column' => 'category','label' => lang('common_category')),
			'category_id' => 										array('sort_column' => 'category','label' => lang('common_category_full_path'),'format_function' => 'get_full_category_path'),
			'cost_price' => 										array('sort_column' => 'item_kits.cost_price','label' => lang('common_cost_price'),'format_function' => 'to_currency_and_edit_item_kit_price','data_function' => 'item_kit_name_data_function', 'html' => TRUE),
			'location_cost_price' => 						array('sort_column' => 'location_cost_price','label' => lang('common_location_cost_price'),'format_function' => 'to_currency_and_edit_location_item_kit_price','data_function' => 'item_kit_name_data_function', 'html' => TRUE),
			'location_unit_price' => 						array('sort_column' => 'location_unit_price','label' => lang('common_location_unit_price'),'format_function' => 'to_currency_and_edit_location_item_kit_price','data_function' => 'item_kit_name_data_function', 'html' => TRUE),
			'unit_price' => 										array('sort_column' => 'item_kits.unit_price','label' => lang('common_unit_price'),'format_function' => 'to_currency_and_edit_item_kit_price','data_function' => 'item_kit_name_data_function', 'html' => TRUE),
			'dynamic_pricing'  => 							array('sort_column' => 'item_kits.dynamic_pricing','label' => lang('common_dynamic_pricing'),'format_function' => 'boolean_as_string'),				
			'tax_group' => 											array('sort_column' => 'tax_group','label' => lang('common_tax_class')),
			'description' => 										array('sort_column' => 'item_kits.description','label' => lang('common_description')),
			'info_popup' => 										array('sort_column' => 'info_popup','label' => lang('common_info_popup')),
			'tax_included' => 									array('sort_column' => 'item_kits.tax_included','label' => lang('common_prices_include_tax'),'format_function' => 'boolean_as_string'),
			'override_default_tax'  => 					array('sort_column' => 'item_kits.override_default_tax','label' => lang('common_override_default_tax'),'format_function' => 'boolean_as_string'),		
			'is_ebt_item'  => 									array('sort_column' => 'item_kits.is_ebt_item','label' => lang('common_is_ebt_item'),'format_function' => 'boolean_as_string'),		
			'commission_percent'  => 						array('sort_column' => 'item_kits.commission_percent','label' => lang('items_commission_percent')),		
			'commission_percent_type'  => 			array('sort_column' => 'item_kits.commission_percent_type','label' => lang('items_commission_percent_type'),'format_function' => 'commission_percent_type_formater'),		
			'commission_fixed'  => 							array('sort_column' => 'item_kits.commission_fixed','label' => lang('items_commission_fixed')),		
			'change_cost_price'  => 						array('sort_column' => 'item_kits.change_cost_price','label' => lang('common_change_cost_price_during_sale'),'format_function' => 'boolean_as_string'),		
			'disable_loyalty'  => 							array('sort_column' => 'item_kits.disable_loyalty','label' => lang('common_disable_loyalty'),'format_function' => 'boolean_as_string'),				
			'max_discount_percent'  => 					array('sort_column' => 'item_kits.max_discount_percent','label' => lang('common_max_discount_percent'),'format_function' => 'to_percent'),
			'min_edit_price'  => 								array('sort_column' => 'item_kits.min_edit_price','label' => lang('common_min_edit_price'),'format_function' => 'to_currency'),
			'max_edit_price'  => 								array('sort_column' => 'item_kits.max_edit_price','label' => lang('common_max_edit_price'),'format_function' => 'to_currency'),
			'allow_price_override_regardless_of_permissions'  => 	array('sort_column' => 'allow_price_override_regardless_of_permissions','label' => character_limiter(lang('common_allow_price_override_regardless_of_permissions'),38),'format_function' => 'boolean_as_string'),		
			'only_integer'  => 									array('sort_column' => 'only_integer','label' => character_limiter(lang('common_only_integer'),38),'format_function' => 'boolean_as_string'),		
			'is_barcoded'  => 									array('sort_column' => 'is_barcoded','label' => lang('common_is_barcoded'),'format_function' => 'boolean_as_string'),		
			'item_kit_inactive'  => 									array('sort_column' => 'item_kit_inactive','label' => lang('common_inactive'),'format_function' => 'boolean_as_string'),		
			'is_favorite'  => 									array('sort_column' => 'is_favorite','label' => lang('common_is_favorite'),'format_function' => 'boolean_as_string'),		
			'loyalty_multiplier'  => 									array('sort_column' => 'loyalty_multiplier','label' => lang('common_loyalty_multiplier')),		
		);
		
		if ($this->config->item('verify_age_for_products'))
		{
			$return['verify_age'] = array('sort_column' => 'verify_age','label' => lang('common_requires_age_verification'),'format_function' => 'boolean_as_string');		
			$return['required_age'] = array('sort_column' => 'required_age','label' => lang('common_required_age'),'format_function' => 'to_quantity');		
		}
		
		for($k=1;$k<=NUMBER_OF_PEOPLE_CUSTOM_FIELDS;$k++)
		{
			if($this->Item_kit->get_custom_field($k) !== false)
			{
				$field = array();
				$field['sort_column'] ="custom_field_${k}_value";
				$field['label']= $this->Item_kit->get_custom_field($k);
			
				if ($this->Item_kit->get_custom_field($k,'type') == 'checkbox')
				{
					$format_function = 'boolean_as_string';
				}
				elseif($this->Item_kit->get_custom_field($k,'type') == 'date')
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
				$return["custom_field_${k}_value"] = $field;
			}
		}
		
		return $return;
		
	}
	
	function get_default_columns()
	{
		return array('item_kit_id','item_kit_number','name','category_id','cost_price','unit_price');
	}
	
	/*
	Gets sale price for item kit given an array of parameters. Current keys are item_id and tier_id
	*/
	
	public function get_sale_price(array $params)
	{
		$item_kit_id = $params['item_kit_id'];
		$tier_id = isset($params['tier_id']) ? $params['tier_id'] : FALSE;
		
		$item_kit_info = $this->Item_kit->get_info($item_kit_id);
		$item_kit_location_info = $this->Item_kit_location->get_info($item_kit_id);
		
		$item_kit_tier_row = $this->Item_kit->get_tier_price_row($tier_id, $item_kit_id);
		$item_kit_location_tier_row = $this->Item_kit_location->get_tier_price_row($tier_id, $item_kit_id, $this->Employee->get_logged_in_employee_current_location_id());
		
		$tier_info = $this->Tier->get_info($tier_id);
		
		if (!empty($item_kit_location_tier_row) && $item_kit_location_tier_row->unit_price)
		{
			return to_currency_no_money($item_kit_location_tier_row->unit_price, $this->config->item('round_tier_prices_to_2_decimals') ? 2 : 10);
		}
		elseif (!empty($item_kit_location_tier_row) && $item_kit_location_tier_row->percent_off)
		{
			$item_kit_unit_price = (double)$item_kit_location_info->unit_price ? $item_kit_location_info->unit_price : $item_kit_info->unit_price;
			return to_currency_no_money($item_kit_unit_price *(1-($item_kit_location_tier_row->percent_off/100)), $this->config->item('round_tier_prices_to_2_decimals') ? 2 : 10);
		}
		elseif (!empty($item_kit_location_tier_row) && $item_kit_location_tier_row->cost_plus_percent)
		{
			$item_kit_cost_price = (double)$item_kit_location_info->cost_price ? $item_kit_location_info->cost_price : $item_kit_info->cost_price;
			return to_currency_no_money($item_kit_cost_price *(1+($item_kit_location_tier_row->cost_plus_percent/100)), $this->config->item('round_tier_prices_to_2_decimals') ? 2 : 10);
		}
		elseif (!empty($item_kit_location_tier_row) && $item_kit_location_tier_row->cost_plus_fixed_amount)
		{
			$item_kit_cost_price = (double)$item_kit_location_info->cost_price ? $item_kit_location_info->cost_price : $item_kit_info->cost_price;
			return to_currency_no_money($item_kit_cost_price + $item_kit_location_tier_row->cost_plus_fixed_amount, $this->config->item('round_tier_prices_to_2_decimals') ? 2 : 10);
		}
		elseif (!empty($item_kit_tier_row) && $item_kit_tier_row->unit_price)
		{
			return to_currency_no_money($item_kit_tier_row->unit_price, $this->config->item('round_tier_prices_to_2_decimals') ? 2 : 10);
		}
		elseif (!empty($item_kit_tier_row) && $item_kit_tier_row->percent_off)
		{
			$item_kit_unit_price = (double)$item_kit_location_info->unit_price ? $item_kit_location_info->unit_price : $item_kit_info->unit_price;
			return to_currency_no_money($item_kit_unit_price *(1-($item_kit_tier_row->percent_off/100)), $this->config->item('round_tier_prices_to_2_decimals') ? 2 : 10);
		}
		elseif (!empty($item_kit_tier_row) && $item_kit_tier_row->cost_plus_percent)
		{
			$item_kit_cost_price = (double)$item_kit_location_info->cost_price ? $item_kit_location_info->cost_price : $item_kit_info->cost_price;
			return to_currency_no_money($item_kit_cost_price *(1+($item_kit_tier_row->cost_plus_percent/100)), $this->config->item('round_tier_prices_to_2_decimals') ? 2 : 10);
		}
		elseif (!empty($item_kit_tier_row) && $item_kit_tier_row->cost_plus_fixed_amount)
		{
			$item_kit_cost_price = (double)$item_kit_location_info->cost_price ? $item_kit_location_info->cost_price : $item_kit_info->cost_price;
			return to_currency_no_money($item_kit_cost_price  + $item_kit_tier_row->cost_plus_fixed_amount, $this->config->item('round_tier_prices_to_2_decimals') ? 2 : 10);
		}
		elseif($tier_info->default_percent_off)
		{
			$item_kit_unit_price = (double)$item_kit_location_info->unit_price ? $item_kit_location_info->unit_price : $item_kit_info->unit_price;
			return to_currency_no_money($item_kit_unit_price *(1-($tier_info->default_percent_off/100)), $this->config->item('round_tier_prices_to_2_decimals') ? 2 : 10);
		}
		elseif($tier_info->default_cost_plus_percent)
		{
			$item_kit_cost_price = (double)$item_kit_location_info->cost_price ? $item_kit_location_info->cost_price : $item_kit_info->cost_price;
			return to_currency_no_money($item_kit_cost_price *(1+($tier_info->default_cost_plus_percent/100)), $this->config->item('round_tier_prices_to_2_decimals') ? 2 : 10);
		}
		elseif($tier_info->default_cost_plus_fixed_amount)
		{
			$item_kit_cost_price = (double)$item_kit_location_info->cost_price ? $item_kit_location_info->cost_price : $item_kit_info->cost_price;
			return to_currency_no_money($item_kit_cost_price + $tier_info->default_cost_plus_fixed_amount, $this->config->item('round_tier_prices_to_2_decimals') ? 2 : 10);
		}
		else
		{
			$item_kit_unit_price = (double)$item_kit_location_info->unit_price ? $item_kit_location_info->unit_price : $item_kit_info->unit_price;
			return to_currency_no_money($item_kit_unit_price, 10);
		}		
		
	}
	
	function save_price_history($item_kit_id,$location_id,$unit_price,$cost_price, $force = FALSE)
	{
		$employee_id = $this->Employee->get_logged_in_employee_info() && $this->Employee->get_logged_in_employee_info()->person_id ? $this->Employee->get_logged_in_employee_info()->person_id : 1;
		
		if ($location_id)
		{
			$item_kit_info = $this->Item_kit_location->get_info($item_kit_id,$location_id);			
		}
		else
		{
			$item_kit_info = $this->get_info($item_kit_id);
		}
		
		if ($item_kit_info->unit_price != $unit_price || $item_kit_info->cost_price!=$cost_price || $force)
		{
			$this->db->insert('item_kits_pricing_history', array(
			'on_date' => date('Y-m-d H:i:s'),
			'employee_id' => $employee_id,
			'item_kit_id' => $item_kit_id,
			'location_id' => $location_id,
			'unit_price' => $unit_price,
			'cost_price' => $cost_price,
			));
		}
	}
	
	function add_hidden_item_kit($item_kit_id,$location_id = false)
	{
		if (!$location_id)
		{
			$location_id= $this->Employee->get_logged_in_employee_current_location_id() ? $this->Employee->get_logged_in_employee_current_location_id() : 1;
		}
		
		return $this->db->replace('grid_hidden_item_kits',array('item_kit_id' => $item_kit_id,'location_id' => $location_id));
	}
	
	function remove_hidden_item_kit($item_kit_id,$location_id = false)
	{
		if (!$location_id)
		{
			$location_id= $this->Employee->get_logged_in_employee_current_location_id() ? $this->Employee->get_logged_in_employee_current_location_id() : 1;
		}
		
		$this->db->where('item_kit_id',$item_kit_id);
		$this->db->where('location_id',$location_id);
		
		return $this->db->delete('grid_hidden_item_kits');
	}
	
	function is_item_kit_hidden($item_kit_id,$location_id = false)
	{
		if (!$location_id)
		{
			$location_id= $this->Employee->get_logged_in_employee_current_location_id() ? $this->Employee->get_logged_in_employee_current_location_id() : 1;
		}
		$this->db->from('grid_hidden_item_kits');
		$this->db->where('item_kit_id',$item_kit_id);
		$this->db->where('location_id',$location_id);
		
		$query = $this->db->get();

		return ($query->num_rows()==1);
		
	}
	
	function add_ban_item_kit($item_kit_id,$location_id = false)
	{
		if (!$location_id)
		{
			$location_id= $this->Employee->get_logged_in_employee_current_location_id() ? $this->Employee->get_logged_in_employee_current_location_id() : 1;
		}
		
		return $this->db->replace('location_ban_item_kits',array('item_kit_id' => $item_kit_id,'location_id' => $location_id));
	}

	function remove_ban_item_kit($item_kit_id,$location_id = false)
	{
		if (!$location_id)
		{
			$location_id= $this->Employee->get_logged_in_employee_current_location_id() ? $this->Employee->get_logged_in_employee_current_location_id() : 1;
		}
		
		$this->db->where('item_kit_id',$item_kit_id);
		$this->db->where('location_id',$location_id);
		
		return $this->db->delete('location_ban_item_kits');
	}
	
	function is_item_kit_ban($item_kit_id,$location_id = false)
	{
		if (!$location_id)
		{
			$location_id= $this->Employee->get_logged_in_employee_current_location_id() ? $this->Employee->get_logged_in_employee_current_location_id() : 1;
		}
		$this->db->from('location_ban_item_kits');
		$this->db->where('item_kit_id',$item_kit_id);
		$this->db->where('location_id',$location_id);
		
		$query = $this->db->get();

		return ($query && $query->num_rows()==1);
		
	}
	
	function get_item_kit_images($item_kit_id)
	{
		$this->db->from('item_kit_images');
		$this->db->where('item_kit_id',$item_kit_id);
		$this->db->order_by('id');
	  return $this->db->get()->result_array();
	}
	
	
	
	function add_image($item_kit_id,$image_id)
	{
		$this->db->insert('item_kit_images', array('item_kit_id' => $item_kit_id, 'image_id' => $image_id));
	}
	
	function set_main_image($item_kit_id,$image_id)
	{
		$this->db->where('item_kit_id', $item_kit_id);
		$this->db->update('item_kits', array('main_image_id' => $image_id));
	}
	
	
	function delete_image($image_id)
	{
		$this->db->where('main_image_id', $image_id);
		$this->db->update('item_kits', array('main_image_id' => NULL));
		
	  $this->db->where('image_id',$image_id);
		$this->db->delete('item_kit_images');
		$this->load->model('Appfile');
		return $this->Appfile->delete($image_id);
	}
	
	public function delete_all_images($item_kit_id)
	{
		$this->db->where('item_kit_id', $item_kit_id);
		$this->db->update('item_kits',array('main_image_id' => NULL));
		
		$this->db->from('item_kit_images');
		$this->db->where('item_kit_id',$item_kit_id);
		
		foreach($this->db->get()->result_array() as $row)
		{
			$this->delete_image($row['image_id']);
		}
	}
	
	function save_image_metadata($image_id, $title, $alt_text)
	{
		$this->db->where('image_id', $image_id);
		$this->db->update('item_kit_images', array('title' => $title,'alt_text' => $alt_text));
	}
	
	function get_secondary_categories($item_kit_id)
	{
		$this->db->from('item_kits_secondary_categories');
		$this->db->where('item_kit_id',$item_kit_id);
		return $this->db->get();
	}
	
	function save_secondory_category($item_kit_id,$category_id,$sec_category_id = NULL)
	{
		if ($sec_category_id > 0)
		{
			$this->db->where('id',$sec_category_id);
			$this->db->update('item_kits_secondary_categories',array('item_kit_id' => $item_kit_id,'category_id' => $category_id));
		}
		else
		{
			$this->db->replace('item_kits_secondary_categories',array('item_kit_id' => $item_kit_id,'category_id' => $category_id));
		}
	}

	function delete_secondory_category($sec_category_id)
	{
		$this->db->where('id',$sec_category_id);
		$this->db->delete('item_kits_secondary_categories');
	}
}
?>