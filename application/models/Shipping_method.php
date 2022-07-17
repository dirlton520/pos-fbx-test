<?php
class Shipping_method extends MY_Model
{
	/*
	Determines if a given method_id is a method
	*/
	function exists($method_id)
	{
		$this->db->from('shipping_methods');
		$this->db->where('id', $method_id);
		
		$query = $this->db->get();
		
		return ($query->num_rows()==1);
	}
	
	function get_all($provider_id = NULL)
	{
		$this->db->from('shipping_methods');
		$this->db->where('deleted', 0);
		
		
		if ($provider_id)
		{
			$this->db->where('shipping_provider_id',$provider_id);
		}
		
		$this->db->order_by('id');
		
		return $this->db->get();
	}

	function count_all()
	{
		$this->db->from('shipping_methods');
		$this->db->where('deleted', 0);
		
		
		return $this->db->count_all_results();
	}
	
	/*
	Inserts or updates a shipping methods
	*/
	function save(&$method_data, $method_id = false)
	{		
		if (!$method_id or !$this->exists($method_id))
		{
			if($this->db->insert('shipping_methods',$method_data))
			{
				$method_data['id'] = $this->db->insert_id();
				return true;
			}
			return false;
		}

		$this->db->where('id', $method_id);
		return $this->db->update('shipping_methods', $method_data);
	}
	
	function delete($id)
	{	
		$this->db->where('id', $id);
		return $this->db->update('shipping_methods', array('deleted' => 1));
	}

}
?>