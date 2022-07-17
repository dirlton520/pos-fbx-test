<?php
class Sale_types extends MY_Model
{
	function can_remove_quantity($sale_type_id)
	{
		$info = $this->get_info($sale_type_id);
		
		return $info->remove_quantity;
	}
	function get_info($sale_type_id)
	{
		$this->db->from('sale_types');	
		$this->db->where('id',$sale_type_id);
		$query = $this->db->get();
		
		if($query->num_rows()==1)
		{
			return $query->row();
		}
		else
		{
			$sale_type_obj = new stdClass;
			
			//Get all the fields from price_sale_types table
			$fields = array('id','name','sort','system_sale_type');			
			//append those fields to base parent object, we we have a complete empty object
			foreach ($fields as $field)
			{
				$sale_type_obj->$field='';
			}
			
			return $sale_type_obj;
		}
	}

	function get_all($exclude = NULL)
	{
		$this->db->from('sale_types');
		$this->db->where('system_sale_type',0);
		
		if ($exclude)
		{
			$this->db->where('id!=',$exclude);
		}
		$this->db->order_by('sort');
		return $this->db->get();
	}
	
	
	function exists($sale_type_id)
	{
		$this->db->from('sale_types');	
		$this->db->where('id',$sale_type_id);
		$query = $this->db->get();
		
		return ($query->num_rows()==1);
	}
	
	function save(&$sale_type_data,$sale_type_id=false)
	{
		if ($sale_type_id < 0 or !$this->exists($sale_type_id))
		{
			if($this->db->insert('sale_types',$sale_type_data))
			{
				$sale_type_data['id']=$this->db->insert_id();
				return true;
			}
			return false;
		}

		$this->db->where('id', $sale_type_id);
		return $this->db->update('sale_types',$sale_type_data);
	}
	
	function delete($sale_type_id)
	{
		//Migrate all suspended sale types to estimate
		$this->db->where('suspended', $sale_type_id);
		$this->db->update('sales', array('suspended' => 2));
		
		$this->db->where('id', $sale_type_id);
		return $this->db->delete('sale_types'); 
	}
	
}
?>