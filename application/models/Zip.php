<?php
class Zip extends MY_Model
{
	/*
	Determines if a given method_id is a method
	*/
	function exists($zip)
	{
		$this->db->from('zips');
		$this->db->where('name', $zip);
		
		$query = $this->db->get();
		
		return ($query->num_rows()==1);
	}
	
	function lookup($zip)
	{
		$this->db->from('zips');
		$this->db->where("'$zip' REGEXP name");
		
		$this->db->limit(1);
		return $this->db->get();
	}
	
	function get_zips_for_zone($zone_id)
	{
		if ($zone_id)
		{
		
			$this->db->from('zips');
			$this->db->where('deleted', 0);
		
			$this->db->where('shipping_zone_id',$zone_id);
			
			$this->db->order_by('order');
			
			return $this->db->get();
			
		}
		
		return null;
	}
	
	
	function get_all($zip = NULL)
	{
		$this->db->from('zips');
		$this->db->where('deleted', 0);
		
		
		if ($zip)
		{
			$this->db->where('name',$zip);
		}
		
		$this->db->order_by('order');
		
		return $this->db->get();
	}

	function count_all()
	{
		$this->db->from('zips');
		$this->db->where('deleted', 0);
		
		return $this->db->count_all_results();
	}
	
	function delete_all()
	{
		return $this->db->empty_table('zips');
	}
	
	/*
	Inserts or updates a shipping methods
	*/
	function save($zip, $order, $zone_id)
	{
		return $this->db->replace('zips',array('name' => $zip, 'order' => $order, 'shipping_zone_id' => $zone_id));
	}
	
	function delete($id)
	{	
		$this->db->where('id', $id);
		return $this->db->update('zips', array('deleted' => 1));
	}

}
?>