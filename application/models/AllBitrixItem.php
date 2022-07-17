<?php
class AllBitrixItem extends CI_Model
{
	/*
	Determines if a given subscription_id is a method
	*/
	function exists($itemId)
	{
		$this->db->from('all_bitrix_items');
		$this->db->where('item_id', $itemId);		
		$query = $this->db->get();		
		return ($query->num_rows()==1);
	}

	function getByItemIdAndType($itemId, $type)
	{
		$this->db->from('all_bitrix_items');
		$this->db->where('item_id', $itemId);
		$this->db->where('type', $type);		
		$query = $this->db->get();		
		if ($query->num_rows()==1) {				
			return $query->row()->id;
		}
		else {
			return 0;
		}
	}

	function getAllSectionsAsBitrixResponse()
	{
		$this->db->from('all_bitrix_items');
		$this->db->where('type', 1);		
		$query = $this->db->get();		
		if ($query->num_rows() > 0) {			
			$result['result'] = [];
			foreach($query->result_array() as $item) {
				$result['result'][] =	['ID' => $item['item_id'], 'SECTION_ID' => ($item['parent_id'] > 0)?$item['parent_id']:'', 'NAME' => $item['title']];	
			}
			return $result;
		}
		else {
			return false;
		}
	}

	function getItemsCountByParentId($parentId, $collectSections = true)
	{
		$this->db->from('all_bitrix_items');
		$this->db->where('parent_id', $parentId);
		if (!$collectSections) {
			$this->db->where('type', 2);
		}
		return $this->db->count_all_results();
	}

	function getAllSyncedItems($limit = 0, $cpage = 1) {		
		$this->db->select('all_bitrix_items.*');
		$this->db->from('bitrix_subscription');
		$this->db->join('all_bitrix_items', 'bitrix_subscription.value = all_bitrix_items.item_id');
		if ($limit > 0) {
			$cpage = $cpage-1;
			$start = $limit*$cpage;
			$this->db->limit($limit, $start);	
		}
		$query = $this->db->get();
		if ($query->num_rows() > 0) {		
			return $query->result_array();
		}
		else {
			return false;
		}		
	}

	function getAllSyncedItemsCount() {
		$this->db->select('all_bitrix_items.*');
		$this->db->from('bitrix_subscription');
		$this->db->join('all_bitrix_items', 'bitrix_subscription.value = all_bitrix_items.item_id');
		return $this->db->count_all_results();		
	}

	function getItemOnProductSearch($productId, $limit = 0, $cpage = 1) {
		$this->db->from('all_bitrix_items');
		$this->db->where('type', 2);
		$this->db->group_start();
		$this->db->like('item_id', $productId);
		$this->db->or_like('title', $productId);
		$this->db->group_end();
		if ($limit > 0) {
			$cpage = $cpage-1;
			$start = $limit*$cpage;
			$this->db->limit($limit, $start);
		}
		$query = $this->db->get();
		if ($query->num_rows() > 0) {	
			return $query->result_array();
		}
		else {
			return false;
		}		
	}

	function getItemOnProductSearchCount($productId, $limit = 0, $cpage = 1) {
		$this->db->from('all_bitrix_items');
		$this->db->where('type', 2);
			$this->db->like('item_id', $productId);
			$this->db->or_like('title', $productId);
		
		return $this->db->count_all_results();
	}	

	function getItemsByParentId($parentId, $limit = 0, $cpage = 1, $collectSections = true)
	{
		$this->db->from('all_bitrix_items');
		$this->db->where('parent_id', $parentId);
		if (!$collectSections) {
			$this->db->where('type', 2);
		}
		$this->db->order_by("type", "asc");

		if ($limit > 0) {
			$cpage = $cpage-1;
			$start = $limit*$cpage;
			$this->db->limit($limit, $start);	
		}

		$query = $this->db->get();
		if ($query->num_rows() > 0) {	
			return $query->result_array();
		}
		else {
			return false;
		}
	}	
	
	function get_all($itemId = NULL)
	{
		$this->db->from('all_bitrix_items');		
		if ($itemId)
		{
			$this->db->where('item_id',$itemId);
		}		
		return $this->db->get();
	}

	function count_all()
	{
		$this->db->from('all_bitrix_items');		
		return $this->db->count_all_results();
	}
	
	function delete_all()
	{
		return $this->db->empty_table('all_bitrix_items');
	}
	
	function save($data = [])
	{	 
		if (isset($data['id']) && !empty($data['id'])) {
			return 	$this->db->where('id', $data['id'])->update('all_bitrix_items', $data);
			
		}
		else {
			return $this->db->insert('all_bitrix_items', $data);
		}
	}
	
	function delete($id)
	{	
		return $this->db->delete('all_bitrix_items', array('id' => $id));
	}
	function deletebyValue($id, $type)
	{	
		return $this->db->delete('all_bitrix_items', array('item_id' => $id,'type'=>$type));
	}

}
?>