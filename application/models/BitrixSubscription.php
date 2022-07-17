<?php
class BitrixSubscription extends CI_Model
{
	/*
	Determines if a given subscription_id is a method
	*/
	function exists($subscriptionId)
	{
		$this->db->from('bitrix_subscription');
		$this->db->where('id', $subscriptionId);		
		$query = $this->db->get();		
		return ($query->num_rows()==1);
	}
	
	function get_all($subscriptionId = NULL)
	{
		$this->db->from('bitrix_subscription');		
		if ($subscriptionId)
		{
			$this->db->where('id',$subscriptionId);
		}		
		return $this->db->get();
	}

	function count_all()
	{
		$this->db->from('bitrix_subscription');		
		return $this->db->count_all_results();
	}
	
	function delete_all()
	{
		return $this->db->empty_table('bitrix_subscription');
	}
	
	function save($data = [])
	{	 
		if (isset($data['id']) && !empty($data['id'])) {
			return $this->db->update('bitrix_subscription', $data);
		}
		else {
			return $this->db->insert('bitrix_subscription', $data);
		}
	}
	
	function delete($id)
	{	
		return $this->db->delete('bitrix_subscription', array('id' => $id));
	}
	function deleteByValue($data)
	{	
		return $this->db->delete('bitrix_subscription',$data);
	}

}
?>