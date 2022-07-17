<?php
class Tier extends MY_Model
{
	function get_info_by_name($tier_name)
	{
		$this->db->from('price_tiers');	
		$this->db->where('name',$tier_name);
		$query = $this->db->get();
		
		if($query->num_rows()==1)
		{
			$tier_id = $query->row()->id;
		}
		else
		{
			$tier_id = 0;
		}
		
		return $this->get_info($tier_id);
	}
	/*
	Gets information about a particular tier
	*/
	function get_info($tier_id)
	{
		$this->db->from('price_tiers');	
		$this->db->where('id',$tier_id);
		$query = $this->db->get();
		
		if($query->num_rows()==1)
		{
			return $query->row();
		}
		else
		{
			$tier_obj = new stdClass;
			
			//Get all the fields from price_tiers table
			$fields = array('id','order','name','default_percent_off','default_cost_plus_percent','default_cost_plus_fixed_amount');			
			
			//append those fields to base parent object, we we have a complete empty object
			foreach ($fields as $field)
			{
				$tier_obj->$field='';
			}
			
			return $tier_obj;
		}
	}
	
	function get_multiple_info($tier_ids)
	{
		$this->db->from('price_tiers');
		if (!empty($tier_ids))
		{
			$this->db->group_start();
			$tier_ids_chunk = array_chunk($tier_ids,25);
			foreach($tier_ids_chunk as $tier_ids)
			{
				$this->db->or_where_in('id',$tier_ids);
			}
			$this->db->group_end();
		}
		else
		{
			$this->db->where('1', '2', FALSE);
		}
		
		$this->db->order_by("id", "asc");
		return $this->db->get();
	}
	
	
	/*
	Determines if a given tier_id is a tier
	*/
	function exists($tier_id)
	{
		$this->db->from('price_tiers');	
		$this->db->where('id',$tier_id);
		$query = $this->db->get();
		
		return ($query->num_rows()==1);
	}
	
	function get_all()
	{
		$this->db->from('price_tiers');
		$this->db->order_by('order');
		return $this->db->get();
	}

	function count_all()
	{
		$this->db->from('price_tiers');
		return $this->db->count_all_results();
	}
	
	/*
	Inserts or updates a tier
	*/
	function save(&$tier_data,$tier_id=false)
	{
		if (!$tier_id or !$this->exists($tier_id))
		{
			if($this->db->insert('price_tiers',$tier_data))
			{
				$tier_data['id']=$this->db->insert_id();
				return true;
			}
			return false;
		}

		$this->db->where('id', $tier_id);
		return $this->db->update('price_tiers',$tier_data);
	}
	
	function delete($tier_id)
	{
		//Make sure customers don't belong to tier anymore
		$this->db->where('tier_id', $tier_id);
		$this->db->update('customers', array('tier_id' => NULL));
		
		//Make sure sales doesn't have a tier anymore
		$this->db->where('tier_id', $tier_id);
		$this->db->update('sales', array('tier_id' => NULL));
		
		$this->db->where('id', $tier_id);
		return $this->db->delete('price_tiers'); 
	}
	
	public function get_tier_search_suggestions($search)
	{
		$suggestions = array();
		
		$this->db->from('price_tiers');
		$this->db->like('price_tiers.name', $search,!$this->config->item('speed_up_search_queries') ? 'both' : 'after');
		
		foreach($this->db->get()->result_array() as $row)
		{
			$suggestions[]=array('value'=> $row['id'], 'label' => $row['name']);		
		}
		
		return $suggestions;
	}
}
?>
