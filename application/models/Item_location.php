<?php
class Item_location extends MY_Model
{

	function exists($item_id,$location=false)
	{
		if(!$location)
		{
			$location= $this->Employee->get_logged_in_employee_current_location_id();
		}
		$this->db->from('location_items');
		$this->db->where('item_id',$item_id);
		$this->db->where('location_id',$location);
		$query = $this->db->get();

		return ($query->num_rows()==1);
	}
	
	
	function save($item_location_data,$item_id=-1,$location_id=false)
	{
		if (empty($item_location_data))
		{
			return TRUE;
		}
		if(!$location_id)
		{
			$location_id= $this->Employee->get_logged_in_employee_current_location_id();
		}
		
		//Save to tick last modifed for sync
		$this->load->model('Item');
		$empty_data = array();
		$this->Item->save($empty_data,$item_id);
		
		if (!$this->exists($item_id,$location_id))
		{
			$item_location_data['item_id'] = $item_id;
			$item_location_data['location_id'] = $location_id;
			
			if (isset($item_location_data['unit_price']) || isset($item_location_data['cost_price']))
			{
				$this->Item->save_price_history($item_location_data['item_id'],NULL,$item_location_data['location_id'],isset($item_location_data['unit_price']) ? $item_location_data['unit_price'] : NULL,isset($item_location_data['cost_price']) ? $item_location_data['cost_price'] : NULL, TRUE);
			}
			
			return $this->db->insert('location_items',$item_location_data);
		}


		if (isset($item_location_data['unit_price']) || isset($item_location_data['cost_price']))
		{
			$this->Item->save_price_history($item_id,NULL,$location_id,isset($item_location_data['unit_price']) ? $item_location_data['unit_price'] : NULL,isset($item_location_data['cost_price']) ? $item_location_data['cost_price'] : NULL);
		}

		$this->db->where('item_id',$item_id);
		$this->db->where('location_id',$location_id);
		return $this->db->update('location_items',$item_location_data);
		
	}
	
	function save_quantity($quantity, $item_id, $location_id=false)
	{
		if(!$location_id)
		{
			$location_id= $this->Employee->get_logged_in_employee_current_location_id();
		}
		
		//Save to tick last modifed for sync
		$this->load->model('Item');
		$empty_data = array();
		$this->Item->save($empty_data,$item_id);
		
		$sql = 'INSERT INTO '.$this->db->dbprefix('location_items'). ' (quantity, item_id, location_id)'
		    . ' VALUES (?, ?, ?)'
		    . ' ON DUPLICATE KEY UPDATE quantity = ?'; 
		
		return $this->db->query($sql, array($quantity, $item_id, $location_id,$quantity));		
	}
	
	/*
	Updates multiple item locations at once
	*/
	function update_multiple($item_location_data, $item_ids,$select_inventory=0, $location_id = false)
	{
		$this->load->model('Item');
		
		$quantity_change = isset($item_location_data['quantity']);
		
		if(!$location_id)
		{
			$location_id= $this->Employee->get_logged_in_employee_current_location_id();
		}

		if($select_inventory)
		{
			if ($this->Item->is_empty_search())
			{	
				if ($quantity_change)
				{
					$comment =lang('items_bulk_edit');
								
					$employee_id=$this->Employee->get_logged_in_employee_info()->person_id;
				
					$items = $this->Item->get_all($this->Item->count_all())->result_object();
				
					foreach($items as $item)
					{
						$item_id = $item->item_id;
						$cur_quantity = $item->quantity;
						$inventory_change_amt = $item_location_data['quantity'] - $cur_quantity;
						
						//Save to tick last modifed for sync
						$this->load->model('Item');
						$empty_data = array();
						$this->Item->save($empty_data,$item_id);
						
						if ($inventory_change_amt)
						{
							$inv_data = array
								(
								'trans_date'=>date('Y-m-d H:i:s'),
								'trans_items'=>$item_id,
								'trans_user'=>$employee_id,
								'trans_comment'=>$comment,
								'trans_inventory'=>$inventory_change_amt,
								'location_id'=>$location_id,
								'trans_current_quantity' => $item_location_data['quantity'],
							);
							$this->Inventory->insert($inv_data);
						}
					}
				}			
				
				$this->db->where('location_id', $location_id);
				return $this->db->update('location_items',$item_location_data);
			}
			else
			{
				$item_ids = array();
				$total_items = $this->Item->count_all();
			
				$params = $this->session->userdata('items_search_data') ? $this->session->userdata('items_search_data') : array('offset' => 0, 'order_col' => 'item_id', 'order_dir' => 'asc', 'search' => FALSE, 'category_id' => FALSE, 'fields' => 'all');
				$result = $this->Item->search(isset($params['search']) ? $params['search'] : '',0,isset($params['category_id']) ? $params['category_id'] : '',$total_items,0,'name','asc', isset($params['fields']) ? $params['fields']: 'all');
			
				foreach($result->result() as $row)
				{
					$item_ids[] = $row->item_id;
				}
				
				if ($quantity_change)
				{
					$comment =lang('items_bulk_edit');
					$employee_id=$this->Employee->get_logged_in_employee_info()->person_id;				
					foreach($item_ids as $item_id)
					{
						$cur_quantity = $this->get_location_quantity($item_id, $location_id);
						$inventory_change_amt = $item_location_data['quantity'] - $cur_quantity;
						
						if ($inventory_change_amt)
						{
							$inv_data = array
								(
								'trans_date'=>date('Y-m-d H:i:s'),
								'trans_items'=>$item_id,
								'trans_user'=>$employee_id,
								'trans_comment'=>$comment,
								'trans_inventory'=>$inventory_change_amt,
								'location_id'=>$location_id,
								'trans_current_quantity' => $item_location_data['quantity'],
								
							);
							$this->Inventory->insert($inv_data);
						}
					}
				}
				
				
				$this->load->helper('database');
				return create_and_execute_large_update_query_location_items($item_ids, $location_id, $item_location_data);
			}
		}
		else
		{			
			if ($quantity_change)
			{
				$comment =lang('items_bulk_edit');
				$employee_id=$this->Employee->get_logged_in_employee_info()->person_id;				
				foreach($item_ids as $item_id)
				{
					$cur_quantity = $this->get_location_quantity($item_id, $location_id);
					$inventory_change_amt = $item_location_data['quantity'] - $cur_quantity;
					
					if ($inventory_change_amt)
					{
						$inv_data = array
							(
							'trans_date'=>date('Y-m-d H:i:s'),
							'trans_items'=>$item_id,
							'trans_user'=>$employee_id,
							'trans_comment'=>$comment,
							'trans_inventory'=>$inventory_change_amt,
							'location_id'=>$location_id,
							'trans_current_quantity' => $item_location_data['quantity'],
							
						);
						$this->Inventory->insert($inv_data);
					}
				}
			}
			
			$this->load->helper('database');
			return create_and_execute_large_update_query_location_items($item_ids, $location_id, $item_location_data);
		}
	}
	
	
	function get_info($item_id,$location=false, $can_cache = false)
	{
		if ($can_cache)
		{
			static $cache;
		}
		
		if(!$location)
		{
			$location= $this->Employee->get_logged_in_employee_current_location_id();
		}
		
		if (is_array($item_id))
		{
			$item_locations = $this->get_multiple_info($item_id,$location)->result();
			
			foreach($item_locations as $item_location)
			{
				if ($can_cache)
				{
					$cache[$item_location->item_id.'|'.$location] = $item_location;
				}
			}
			
			return $item_locations;
		}
		
		
		if ($can_cache)
		{			
			if (isset($cache[$item_id.'|'.$location]))
			{
				return $cache[$item_id.'|'.$location];
			}
		}
		
		$this->db->from('location_items');
		$this->db->where('item_id',$item_id);
		$this->db->where('location_id',$location);
		$query = $this->db->get();

		if($query->num_rows()==1)
		{
			$row = $query->row();
			
			//Store a boolean indicating if the price has been overwritten
			$row->is_overwritten = ($row->cost_price !== NULL ||
			$row->unit_price !== NULL ||
			$row->promo_price !== NULL || 
			$this->is_tier_overwritten($item_id, $location));
			$cache[$item_id.'|'.$location] = $row;
			
			return $cache[$item_id.'|'.$location];
		
		}
		else
		{
			//Get empty base parent object, as $item_id is NOT an item_location
			$item_location_obj=new stdClass();

			//Get all the fields from item_locations table
			$fields = array('location_id','item_id','location','cost_price','unit_price','promo_price','start_date','end_date','quantity','reorder_level','override_default_tax','tax_class_id','replenish_level');			
			
			foreach ($fields as $field)
			{
				$item_location_obj->$field='';
			}
			
			$item_location_obj->is_overwritten = FALSE;
			$cache[$item_id.'|'.$location] = $item_location_obj;
			return $cache[$item_id.'|'.$location];
			
		}

	}
	
	/*
	Gets information about multiple items locations
	*/
	function get_multiple_info($item_ids,$location=false)
	{
		if(!$location)
		{
			$location= $this->Employee->get_logged_in_employee_current_location_id();
		}
		
		$this->db->from('location_items');
		$this->db->where('location_id',$location);
		
		if (!empty($item_ids))
		{
			$this->db->group_start();
			$item_ids_chunk = array_chunk($item_ids,25);
			foreach($item_ids_chunk as $item_ids)
			{
				$this->db->or_where_in('item_id',$item_ids);
			}
			$this->db->group_end();
		}
		else
		{
			$this->db->where('1', '2', FALSE);
		}
		
		
		$this->db->order_by("item_id", "asc");
		return $this->db->get();
	}
	
	function get_all_location_quantity($item_id)
	{
		$this->db->select('SUM(quantity) as quantity');
		$this->db->from('location_items');
		$this->db->where('item_id',$item_id);
		$query = $this->db->get();

		if($query->num_rows()==1)
		{
			$row=$query->row();
			return $row->quantity;
		}

		return 0;
	}
	
	function get_location_quantity($item_id,$location=false)
	{
		if(!$location)
		{
			$location= $this->Employee->get_logged_in_employee_current_location_id();
		}
		
		$this->db->from('location_items');
		$this->db->where('item_id',$item_id);
		$this->db->where('location_id',$location);
		$query = $this->db->get();

		if($query->num_rows()==1)
		{
			$row=$query->row();
			return $row->quantity;
		}

		return NULL;
	}
	
	function get_tier_price_row($tier_id,$item_id, $location_id)
	{
		$this->db->from('location_items_tier_prices');
		$this->db->where('tier_id',$tier_id);
		$this->db->where('item_id ',$item_id);
		$this->db->where('location_id ',$location_id);
		return $this->db->get()->row();
	}
		
	function delete_tier_price($tier_id, $item_id, $location_id)
	{
		$this->db->where('tier_id', $tier_id);
		$this->db->where('item_id', $item_id);
		$this->db->where('location_id', $location_id);
		$this->db->delete('location_items_tier_prices');
	}
	
	function delete_all_tier_prices($item_id,$location_id)
	{
		$this->db->where('item_id', $item_id);
		$this->db->where('location_id', $location_id);
		return $this->db->delete('location_items_tier_prices');
	}
	
	function tier_exists($tier_id, $item_id, $location_id)
	{
		$this->db->from('location_items_tier_prices');
		$this->db->where('tier_id',$tier_id);
		$this->db->where('item_id',$item_id);
		$this->db->where('location_id',$location_id);
		$query = $this->db->get();

		return ($query->num_rows()>=1);
		
	}
	
	function save_item_tiers($tier_data,$item_id, $location_id)
	{	
		if($this->tier_exists($tier_data['tier_id'],$item_id,$location_id))
		{
			$this->db->where('tier_id', $tier_data['tier_id']);
			$this->db->where('item_id', $item_id);
			$this->db->where('location_id', $location_id);

			return $this->db->update('location_items_tier_prices',$tier_data);
			
		}

		return $this->db->insert('location_items_tier_prices',$tier_data);	
	}

	function is_tier_overwritten($item_id, $location_id)
	{
		$this->db->from('location_items_tier_prices');
		$this->db->where('item_id',$item_id);
		$this->db->where('location_id',$location_id);
		$query = $this->db->get();

		return ($query->num_rows()>=1);
	}
		
	
	function get_suspended_inventory($item_id,$location = false)
	{
		if(!$location)
		{
			$location= $this->Employee->get_logged_in_employee_current_location_id();
		}
		
		$this->db->select('SUM(quantity_purchased) as quantity', false);
		$this->db->from('sales_items');
		$this->db->join('sales', 'sales.sale_id = sales_items.sale_id');
		$this->db->where('sales.deleted', 0);
		$this->db->where('sales.suspended =1');
		$this->db->where('location_id', $location);
		$this->db->where('item_id', $item_id);
		
		$return = $this->db->get()->row_array();
		
		return $return['quantity'];
		
	}
	
}
?>
