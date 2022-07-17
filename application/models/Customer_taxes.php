<?php
class Customer_taxes extends MY_Model
{
	/*
	Gets tax info for a particular item
	*/
	function get_info($customer_id)
	{
		$this->db->from('customers_taxes');
		$this->db->where('customer_id',$customer_id);
		$this->db->order_by('cumulative');
		$this->db->order_by('id');
		//return an array of taxes for an item
		return $this->db->get()->result_array();
	}
	
	/*
	Inserts or updates an item's taxes
	*/
	function save(&$customers_taxes_data, $customer_id)
	{
		//Run these queries as a transaction, we want to make sure we do all or nothing
		$this->db->trans_start();
		$current_taxes = $this->get_info($customer_id);
		
		//Delete and add
		if (count($current_taxes) != count($customers_taxes_data))
		{
			$this->delete($customer_id);
		
			foreach ($customers_taxes_data as $row)
			{
				$row['customer_id'] = $customer_id;
				$this->db->insert('customers_taxes',$row);		
			}
		}
		else //Update
		{
			for($k=0;$k<count($current_taxes);$k++)
			{
				$current_tax = $current_taxes[$k];
				$new_tax = $customers_taxes_data[$k];
				
				$this->db->where('id', $current_tax['id']);
				$this->db->update('customers_taxes', $new_tax);
			}
			
		}
		$this->db->trans_complete();
		return true;
	}
	
	/*
	Deletes taxes given an item
	*/
	function delete($customer_id)
	{
		return $this->db->delete('customers_taxes', array('customer_id' => $customer_id)); 
	}
}
?>
