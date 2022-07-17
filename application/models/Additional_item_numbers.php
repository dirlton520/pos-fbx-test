<?php
class Additional_item_numbers extends MY_Model
{
	/*
	Returns all the item numbers for a given item
	*/
	function get_item_numbers($item_id)
	{
		$this->db->from('additional_item_numbers');
		$this->db->where('item_id',$item_id);
		$this->db->where('item_variation_id',NULL);
		return $this->db->get();
	}
	
	/*
	Returns all the item numbers for item and variation for a given item
	*/
	function get_item_numbers_for_variation($item_id,$item_variation_id)
	{
		$this->db->from('additional_item_numbers');
		$this->db->where('item_id',$item_id);
		$this->db->where('item_variation_id',$item_variation_id);
		return $this->db->get();
	}
	
	
	function get_all()
	{
		$this->db->from('additional_item_numbers');
		$this->db->where('item_variation_id',NULL);
		
		$return = array();
		
		foreach($this->db->get()->result_array() as $result)
		{
			$return[$result['item_id']][] = $result['item_number'];
		}
		
		return $return;
	}
	
	function save_variation($item_id,$variation_id,$additional_item_numbers)
	{
		$this->db->trans_start();

		$this->db->delete('additional_item_numbers', array('item_id' => $item_id,'item_variation_id' => $variation_id));
		
		foreach($additional_item_numbers as $item_number)
		{
			if ($item_number!='')
			{
				$this->db->insert('additional_item_numbers', array('item_id' => $item_id,'item_variation_id' => $variation_id, 'item_number' => $item_number));
			}
		}
		
		$this->db->trans_complete();
		
		return $this->db->trans_status();
		
	}
	
	function save($item_id, $additional_item_numbers)
	{
		$this->db->trans_start();

		$this->db->delete('additional_item_numbers', array('item_id' => $item_id,'item_variation_id' => NULL));

		foreach($additional_item_numbers as $item_number)
		{
			if ($item_number!='')
			{
				$this->db->insert('additional_item_numbers', array('item_id' => $item_id, 'item_number' => $item_number));
			}
		}
		
		$this->db->trans_complete();
		
		return $this->db->trans_status();
	}
	
	function delete($item_id)
	{
		return $this->db->delete('additional_item_numbers', array('item_id' => $item_id,'item_variation_id' => NULL));
	}
	
	function delete_variation($item_id,$item_variation_id)
	{
		return $this->db->delete('additional_item_numbers', array('item_id' => $item_id,'item_variation_id' => $item_variation_id));		
	}
	
	function cleanup()
	{
		$addit_items_table = $this->db->dbprefix('additional_item_numbers');
		$items_table = $this->db->dbprefix('items');
		$item_variations_table = $this->db->dbprefix('item_variations');
		
		$this->db->query("DELETE FROM $addit_items_table WHERE item_id IN (SELECT item_id FROM $items_table WHERE deleted = 1)");
		return $this->db->query("DELETE FROM $addit_items_table WHERE item_variation_id IN (SELECT id FROM $item_variations_table WHERE deleted = 1)");
		
	}
	
	function get_item_id($item_number)
	{
		$this->db->from('additional_item_numbers');
		$this->db->where('item_number',$item_number);

		$query = $this->db->get();

		if($query->num_rows() >= 1)
		{
			$row = $query->row();
			$return = $row->item_id;
			
			if ($row->item_variation_id)
			{
				$return.='#'.$row->item_variation_id;
			}
			
			return $return;
		}
		
		return FALSE;
	}
}
?>
