<?php
class Shipping_zone extends MY_Model
{
	/*
	Determines if a given provider_id exists
	*/
	function exists($zone_id)
	{
		$this->db->from('shipping_zones');
		$this->db->where('id', $zone_id);
		
		$query = $this->db->get();
		
		return ($query->num_rows()==1);
	}
	
	/*
	Gets information about a particular provider
	*/
	function get_info($provider_id)
	{
		$this->db->from('shipping_zones');
			
		$this->db->where('id',$zone_id);
		$this->db->where('deleted', 0);
		
		$query = $this->db->get();
		
		if($query->num_rows()==1)
		{
			return $query->row();
		}
		else
		{
			return false;
		}
	}
	
	function get_all()
	{
		$this->db->from('shipping_zones');
		$this->db->where('deleted', 0);
		
		$this->db->order_by('order');
		return $this->db->get();
	}

	function count_all()
	{
		$this->db->from('shipping_zones');
		$this->db->where('deleted', 0);
		
		return $this->db->count_all_results();
	}
	
	/*
	Inserts or updates a shipping zone
	*/
	function save(&$zone_data, $zone_id = false)
	{
		if (!$zone_id or !$this->exists($zone_id))
		{
			if($this->db->insert('shipping_zones',$zone_data))
			{
				$zone_data['id'] = $this->db->insert_id();
				return true;
			}
			return false;
		}

		$this->db->where('id', $zone_id);
		return $this->db->update('shipping_zones', $zone_data);
	}
	
	function delete($zone_id)
	{	
		$this->db->where('id', $zone_id);
		return $this->db->update('shipping_zones', array('deleted' => 1));
	}

}
?>
