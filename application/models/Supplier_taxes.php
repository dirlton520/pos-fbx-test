<?php
class Supplier_taxes extends MY_Model
{
	/*
	Gets tax info for a particular item
	*/
	function get_info($supplier_id)
	{
		$this->db->from('suppliers_taxes');
		$this->db->where('supplier_id',$supplier_id);
		$this->db->order_by('cumulative');
		$this->db->order_by('id');
		//return an array of taxes for an item
		return $this->db->get()->result_array();
	}
	
	/*
	Inserts or updates an item's taxes
	*/
	function save(&$suppliers_taxes_data, $supplier_id)
	{
		//Run these queries as a transaction, we want to make sure we do all or nothing
		$this->db->trans_start();
		$current_taxes = $this->get_info($supplier_id);
		
		//Delete and add
		if (count($current_taxes) != count($suppliers_taxes_data))
		{
			$this->delete($supplier_id);
		
			foreach ($suppliers_taxes_data as $row)
			{
				$row['supplier_id'] = $supplier_id;
				$this->db->insert('suppliers_taxes',$row);		
			}
		}
		else //Update
		{
			for($k=0;$k<count($current_taxes);$k++)
			{
				$current_tax = $current_taxes[$k];
				$new_tax = $suppliers_taxes_data[$k];
				
				$this->db->where('id', $current_tax['id']);
				$this->db->update('suppliers_taxes', $new_tax);
			}
			
		}
		$this->db->trans_complete();
		return true;
	}
	
	/*
	Deletes taxes given an item
	*/
	function delete($supplier_id)
	{
		return $this->db->delete('suppliers_taxes', array('supplier_id' => $supplier_id)); 
	}
}
?>
