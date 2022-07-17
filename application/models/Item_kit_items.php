<?php
class Item_kit_items extends MY_Model
{
	/*
	Gets item kit items for a particular item kit
	*/
	function get_info($item_kit_id)
	{
		$this->db->select('item_kit_items.*,items.is_service as is_service');
		$this->db->from('item_kit_items');
		$this->db->join('items','items.item_id = item_kit_items.item_id');
		$this->db->where('item_kit_id',$item_kit_id);
		//return an array of item kit items for an item
		return $this->db->get()->result();
	}
	
	function get_info_kits($item_kit_id)
	{
		$this->db->select('item_kit_item_kits.item_kit_item_kit as item_kit_id, item_kit_item_kits.quantity as quantity,item_kits.name as name');
		$this->db->from('item_kit_item_kits');
		$this->db->join('item_kits','item_kits.item_kit_id = item_kit_item_kits.item_kit_item_kit');
		$this->db->where('item_kit_item_kits.item_kit_id',$item_kit_id);
		//return an array of item kit items for an item
		return $this->db->get()->result();
	}
	
	/*
	Inserts or updates an item kit's items
	*/
	function save(&$item_kit_items_data, $item_kit_id)
	{
		//Run these queries as a transaction, we want to make sure we do all or nothing
		$this->db->trans_start();

		$this->delete($item_kit_id);
		
		foreach ($item_kit_items_data as $row)
		{
			$row['item_kit_id'] = $item_kit_id;
			$this->db->insert('item_kit_items',$row);		
		}
		
		$this->db->trans_complete();
		return true;
	}
	
	/*
	Inserts or updates an item kit's items
	*/
	function save_item_kits(&$item_kit_item_kits_data, $item_kit_id)
	{
		//Run these queries as a transaction, we want to make sure we do all or nothing
		$this->db->trans_start();

		$this->delete_item_kit_items($item_kit_id);
		
		foreach ($item_kit_item_kits_data as $row)
		{
			$row['item_kit_id'] = $item_kit_id;
			$this->db->insert('item_kit_item_kits',$row);		
		}
		
		$this->db->trans_complete();
		return true;
	}
	
	/*
	Deletes item kit items given an item kit
	*/
	function delete($item_kit_id)
	{
		return $this->db->delete('item_kit_items', array('item_kit_id' => $item_kit_id)); 
	}

	/*
	Deletes item kit items given an item kit
	*/
	function delete_item_kit_items($item_kit_id)
	{
		return $this->db->delete('item_kit_item_kits', array('item_kit_id' => $item_kit_id)); 
	}
	

	/**
	 * Get kits with item
	 * @param type $ite_id
	 * @return type
	 */
	function get_kits_have_item($item_id,$variation_id = FALSE)
	{
	    $this->db->from('item_kit_items');
	    $this->db->where('item_id',$item_id);	 
			if ($variation_id)
			{
		    $this->db->where('item_variation_id',$variation_id);	 
			}   
	    return $this->db->get()->result_array();
	}
}
?>
