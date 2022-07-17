<?php
class Shipping_provider extends MY_Model
{
	/*
	Determines if a given provider_id exists
	*/
	function exists($provider_id)
	{
		$this->db->from('shipping_providers');
		$this->db->where('id', $provider_id);
		
		$query = $this->db->get();
		
		return ($query->num_rows()==1);
	}
	
	/*
	Gets information about a particular provider
	*/
	function get_info($provider_id)
	{
		$this->db->from('shipping_providers');
			
		$this->db->where('id',$provider_id);
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
		$this->db->from('shipping_providers');
		$this->db->where('deleted', 0);
		
		$this->db->order_by('order');
		return $this->db->get();
	}

	function count_all()
	{
		$this->db->from('shipping_providers');
		$this->db->where('deleted', 0);
		
		return $this->db->count_all_results();
	}
	
	/*
	Inserts or updates a shipping provider
	*/
	function save(&$provider_data, $provider_id = false)
	{
		if (!$provider_id or !$this->exists($provider_id))
		{
			if($this->db->insert('shipping_providers',$provider_data))
			{
				$provider_data['id'] = $this->db->insert_id();
				return true;
			}
			return false;
		}

		$this->db->where('id', $provider_id);
		return $this->db->update('shipping_providers', $provider_data);
	}
	
	function delete($provider_id)
	{	
		$this->db->where('id', $provider_id);
		return $this->db->update('shipping_providers', array('deleted' => 1));
	}

}
?>
