<?php
class BitrixFieldMapping extends CI_Model
{
	/*
	Determines if a given subscription_id is a method
	*/
	function existsByCode($code)
	{
		$this->db->from('bitrix_field_mapping');
		$this->db->where('bitrix_property_code', $code);		
		$query = $this->db->get();		
		return ($query->num_rows()==1);
	}

	function getPropertyByCode($code) {
		$this->db->from('bitrix_field_mapping');
		$this->db->where('bitrix_field_code', $code);		
		$query = $this->db->get();		
		if ($query->num_rows()==1) {				
			return $query->row()->id;
		}
		else {
			return 0;
		}		
	}

	function getList($catalogId = NULL) {
		$this->db->from('bitrix_field_mapping');
		if (!empty($catalogId)) {
			$this->db->where('catalog_id', $catalogId);
		}
		$list = $this->db->get();
		return (!empty($list))?$list->result_array():[];
	}

	function count_all()
	{
		$this->db->from('bitrix_field_mapping');		
		return $this->db->count_all_results();
	}
	
	function delete_all()
	{
		return $this->db->empty_table('bitrix_field_mapping');
	}
	
	function save($data = [])
	{	 
		if (isset($data['id']) && !empty($data['id'])) {
			return 	$this->db->where('id', $data['id'])->update('bitrix_field_mapping', $data);
			
		}
		else {
			return $this->db->insert('bitrix_field_mapping', $data);
		}
	}
	
	function delete($id)
	{	
		return $this->db->delete('bitrix_field_mapping', array('id' => $id));
	}

}
?>