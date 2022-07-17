<?php
class Item_kit_location extends MY_Model
{

	function exists($item_kit_id,$location=false)
	{
		if(!$location)
		{
			$location= $this->Employee->get_logged_in_employee_current_location_id();
		}
		$this->db->from('location_item_kits');
		$this->db->where('item_kit_id',$item_kit_id);
		$this->db->where('location_id',$location);
		$query = $this->db->get();

		return ($query->num_rows()==1);
	}
	
	
	function save($item_location_data,$item_kit_id=-1,$location_id=false)
	{
		if (empty($item_location_data))
		{
			return TRUE;
		}
		
		if(!$location_id)
		{
			$location_id= $this->Employee->get_logged_in_employee_current_location_id();
		}
		
		if (!$this->exists($item_kit_id,$location_id))
		{
			if (isset($item_location_data['unit_price']) || isset($item_location_data['cost_price']))
			{
				$this->Item_kit->save_price_history($item_kit_id,$location_id,isset($item_location_data['unit_price']) ? $item_location_data['unit_price'] : NULL,isset($item_location_data['cost_price']) ? $item_location_data['cost_price'] : NULL, TRUE);
			}
			
			return $this->db->insert('location_item_kits',$item_location_data);
		}


		if (isset($item_location_data['unit_price']) || isset($item_location_data['cost_price']))
		{
			$this->Item_kit->save_price_history($item_kit_id,$location_id,isset($item_location_data['unit_price']) ? $item_location_data['unit_price'] : NULL,isset($item_location_data['cost_price']) ? $item_location_data['cost_price'] : NULL);
		}

		$this->db->where('item_kit_id',$item_kit_id);
		$this->db->where('location_id',$location_id);
		return $this->db->update('location_item_kits',$item_location_data);
		
	}
	
	function get_info($item_kit_id,$location=false, $can_cache = false)
	{
		if ($can_cache)
		{
			static $cache;
		}
		
		if(!$location)
		{
			$location= $this->Employee->get_logged_in_employee_current_location_id();
		}
		
		if (is_array($item_kit_id))
		{
			$item_locations = $this->get_multiple_info($item_kit_id,$location)->result();
			
			foreach($item_locations as $item_location)
			{
				if ($can_cache)
				{
					$cache[$item_location->item_kit_id.'|'.$location] = $item_location;
				}
			}
			
			return $item_locations;
		}
		
		
		if ($can_cache)
		{			
			if (isset($cache[$item_kit_id.'|'.$location]))
			{
				return $cache[$item_kit_id.'|'.$location];
			}
		}
		
		$this->db->from('location_item_kits');
		$this->db->where('item_kit_id',$item_kit_id);
		$this->db->where('location_id',$location);
		$query = $this->db->get();

		if($query->num_rows()==1)
		{
			$row = $query->row();
			
			//Store a boolean indicating if the price has been overwritten
			$row->is_overwritten = ($row->cost_price !== NULL ||
			$row->unit_price !== NULL ||
			$this->is_tier_overwritten($item_kit_id, $location));
			$cache[$item_kit_id.'|'.$location] = $row;
			return $cache[$item_kit_id.'|'.$location];
		
		}
		else
		{
			//Get empty base parent object, as $item_kit_id is NOT an item_kit_location
			$item_kit_location_obj=new stdClass();

			//Get all the fields from item_kit_locations table
			$fields = array('location_id','item_kit_id','unit_price','cost_price','override_default_tax','tax_class_id');
			foreach ($fields as $field)
			{
				$item_kit_location_obj->$field='';
			}
			
			$item_kit_location_obj->is_overwritten = FALSE;
			$cache[$item_kit_id.'|'.$location] = $item_kit_location_obj;
			return $cache[$item_kit_id.'|'.$location];
		}

		return FALSE;
	}
	
	/*
	Gets information about multiple item kits locations
	*/
	function get_multiple_info($item_kit_ids,$location=false)
	{
		if(!$location)
		{
			$location= $this->Employee->get_logged_in_employee_current_location_id();
		}
		
		$this->db->from('location_item_kits');
		$this->db->where('location_id',$location);
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
		
		$this->db->order_by("item_kit_id", "asc");
		return $this->db->get();
	}
	
	
	function get_tier_price_row($tier_id,$item_kit_id, $location_id)
	{
		$this->db->from('location_item_kits_tier_prices');
		$this->db->where('tier_id',$tier_id);
		$this->db->where('item_kit_id ',$item_kit_id);
		$this->db->where('location_id ',$location_id);
		return $this->db->get()->row();
	}
		
	function delete_tier_price($tier_id, $item_kit_id, $location_id)
	{
		$this->db->where('tier_id', $tier_id);
		$this->db->where('item_kit_id', $item_kit_id);
		$this->db->where('location_id', $location_id);
		$this->db->delete('location_item_kits_tier_prices');
	}
	
	function delete_all_tier_prices($item_id,$item_kit_id)
	{
		$this->db->where('item_kit_id', $item_kit_id);
		$this->db->where('location_id', $location_id);
		$this->db->delete('location_item_kits_tier_prices');
	}
	
	
	function tier_exists($tier_id, $item_kit_id, $location_id)
	{
		$this->db->from('location_item_kits_tier_prices');
		$this->db->where('tier_id',$tier_id);
		$this->db->where('item_kit_id',$item_kit_id);
		$this->db->where('location_id',$location_id);
		$query = $this->db->get();

		return ($query->num_rows()>=1);
		
	}
	
	function save_item_tiers($tier_data,$item_kit_id, $location_id)
	{	
		if($this->tier_exists($tier_data['tier_id'],$item_kit_id,$location_id))
		{
			$this->db->where('tier_id', $tier_data['tier_id']);
			$this->db->where('item_kit_id', $item_kit_id);
			$this->db->where('location_id', $location_id);

			return $this->db->update('location_item_kits_tier_prices',$tier_data);
			
		}

		return $this->db->insert('location_item_kits_tier_prices',$tier_data);	
	}

	function is_tier_overwritten($item_kit_id, $location_id)
	{
		$this->db->from('location_item_kits_tier_prices');
		$this->db->where('item_kit_id',$item_kit_id);
		$this->db->where('location_id',$location_id);
		$query = $this->db->get();

		return ($query->num_rows()>=1);
	}
}
?>
