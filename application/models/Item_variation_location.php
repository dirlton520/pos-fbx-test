<?php
class Item_variation_location extends MY_Model
{	
	function get_variations_with_quantity($item_id, $location=false)
	{
		$this->load->model('Item_variations');
		
		if(!$location)
		{
			$location= $this->Employee->get_logged_in_employee_current_location_id();
		}
		
		$this->db->from('item_variations');
		$this->db->where('item_id',$item_id);
		$this->db->where('deleted',0);
		
		$return = array();
		
		foreach($this->db->get()->result_array() as $result)
		{
			$item_variation_id = $result['id'];
			
			$this->db->from('item_variations');
			$this->db->join('location_item_variations','location_item_variations.item_variation_id=item_variations.id and location_item_variations.location_id='.$location,'left');
			$this->db->where('item_variations.id', $item_variation_id);
			
			$variations = $this->db->get()->result_array();
			foreach($variations as $variation)
			{
				$return[$item_variation_id]['name'] = implode(', ', array_column($this->Item_variations->get_attributes($item_variation_id), 'label'));
				$return[$item_variation_id]['quantity'] = $variation['quantity'];
				$return[$item_variation_id]['item_number'] = $variation['item_number'];
				$return[$item_variation_id]['reorder_level'] = $variation['reorder_level'];
				$return[$item_variation_id]['replenish_level'] = $variation['replenish_level'];
				$return[$item_variation_id]['cost_price'] = $variation['cost_price'];
				$return[$item_variation_id]['unit_price'] = $variation['unit_price'];
			}
		}
		
		return $return;
	}
	
	function exists($item_variation_id,$location=false)
	{
		if(!$location)
		{
			$location= $this->Employee->get_logged_in_employee_current_location_id();
		}
		
		$this->db->from('location_item_variations');
		$this->db->where('item_variation_id',$item_variation_id);
		$this->db->where('location_id',$location);
		$query = $this->db->get();

		return ($query->num_rows()==1);
	}
	
	function save($item_variation_location_data, $item_variation_id=-1, $location_id=false)
	{
		if(!$location_id)
		{
			$location_id= $this->Employee->get_logged_in_employee_current_location_id();
		}
		
		//Save to tick last modifed for sync
		$this->load->model('Item_variations');
		$empty_data = array();
		$this->Item_variations->save($empty_data,$item_variation_id);
		
		if (!$this->exists($item_variation_id,$location_id))
		{
			$item_variation_location_data['item_variation_id'] = $item_variation_id;
			$item_variation_location_data['location_id'] = $location_id;
			
			if (isset($item_variation_location_data['unit_price']) || isset($item_variation_location_data['cost_price']))
			{		
				$item_info = $this->Item_variations->get_info($item_variation_id);
				$item_id = $item_info->item_id;
					
				$this->Item->save_price_history($item_id,$item_variation_location_data['item_variation_id'],$item_variation_location_data['location_id'],isset($item_variation_location_data['unit_price']) ? $item_variation_location_data['unit_price'] : NULL,isset($item_variation_location_data['cost_price']) ? $item_variation_location_data['cost_price'] : NULL, TRUE);
			}
			
			
			return $this->db->insert('location_item_variations',$item_variation_location_data);
		}


		if (isset($item_variation_location_data['unit_price']) || isset($item_variation_location_data['cost_price']))
		{
			$item_info = $this->Item_variations->get_info($item_variation_id);
			$item_id = $item_info->item_id;
			
			$this->Item->save_price_history($item_id,$item_variation_id,$location_id,isset($item_variation_location_data['unit_price']) ? $item_variation_location_data['unit_price'] : NULL,isset($item_variation_location_data['cost_price']) ? $item_variation_location_data['cost_price'] : NULL);
		}

		$this->db->where('item_variation_id',$item_variation_id);
		$this->db->where('location_id',$location_id);
		return $this->db->update('location_item_variations',$item_variation_location_data);
	}
	
	function save_quantity($quantity, $item_variation_id, $location_id=false)
	{
		if(!$location_id)
		{
			$location_id= $this->Employee->get_logged_in_employee_current_location_id();
		}
		
		//Save to tick last modifed for sync
		$this->load->model('Item_variations');
		$empty_data = array();
		$this->Item_variations->save($empty_data,$item_variation_id);
		
		$sql = 'INSERT INTO '.$this->db->dbprefix('location_item_variations'). ' (quantity, item_variation_id, location_id)'
		    . ' VALUES (?, ?, ?)'
		    . ' ON DUPLICATE KEY UPDATE quantity = ?'; 
		
		return $this->db->query($sql, array($quantity, $item_variation_id, $location_id,$quantity));	
	}
	
	/*
	Gets information about multiple items locations
	*/
	function get_multiple_info($variations_ids,$location=false)
	{
		if(!$location)
		{
			$location= $this->Employee->get_logged_in_employee_current_location_id();
		}
		
		$this->db->from('location_item_variations');
		$this->db->where('location_id',$location);
		
		if (!empty($variations_ids))
		{
			$this->db->group_start();
			$variations_ids_chunk = array_chunk($variations_ids,25);
			foreach($variations_ids_chunk as $variations_ids)
			{
				$this->db->or_where_in('item_variation_id',$variations_ids);
			}
			$this->db->group_end();
		}
		else
		{
			$this->db->where('1', '2', FALSE);
		}
		
		
		$this->db->order_by("item_variation_id", "asc");
		return $this->db->get();
	}
	
	
	function get_info($item_variation_id,$location=false, $can_cache = false)
	{
		if ($can_cache)
		{
			static $cache;
		}
		
		if(!$location)
		{
			$location= $this->Employee->get_logged_in_employee_current_location_id();
		}
		
		if (is_array($item_variation_id))
		{
			$item_variation_locations = $this->get_multiple_info($item_variation_id,$location)->result();
			
			foreach($item_variation_locations as $item_variation_location)
			{
				if ($can_cache)
				{
					$cache[$item_variation_location->item_variation_id.'|'.$location] = $item_variation_location;
				}
			}
			
			return $item_variation_locations;
		}
		
		if ($can_cache)
		{			
			if (isset($cache[$item_variation_id.'|'.$location]))
			{
				return $cache[$item_variation_id.'|'.$location];
			}
		}
		
		$this->db->from('location_item_variations');
		$this->db->where('item_variation_id',$item_variation_id);
		$this->db->where('location_id',$location);
		$query = $this->db->get();

		if($query->num_rows()==1)
		{
			$row = $query->row();
		
			$cache[$item_variation_id.'|'.$location] = $row;
			
			return $cache[$item_variation_id.'|'.$location];
		
		}
		else
		{
			//Get empty base parent object, as $item_variation_id is NOT an item_location
			$item_location_obj=new stdClass();

			//Get all the fields from item_locations table
			$fields = array('item_variation_id','location_id','quantity','reorder_level','replenish_level','cost_price','unit_price');

			foreach ($fields as $field)
			{
				$item_location_obj->$field='';
			}
			
			$cache[$item_variation_id.'|'.$location] = $item_location_obj;
			return $cache[$item_variation_id.'|'.$location];
			
		}

	}

	function get_location_quantity($item_variation_id,$location=false)
	{
		if(!$location)
		{
			$location= $this->Employee->get_logged_in_employee_current_location_id();
		}
		
		$this->db->from('location_item_variations');
		$this->db->where('item_variation_id',$item_variation_id);
		$this->db->where('location_id',$location);
		$query = $this->db->get();

		if($query->num_rows()==1)
		{
			$row=$query->row();
			return $row->quantity;
		}

		return NULL;
	}
	
	function get_all_location_quantity($item_variation_id)
	{
		$this->db->select('SUM(quantity) as quantity');
		$this->db->from('location_item_variations');
		$this->db->where('item_variation_id',$item_variation_id);
		$query = $this->db->get();

		if($query->num_rows()==1)
		{
			$row=$query->row();
			return $row->quantity;
		}

		return 0;
	}
	
}
?>
