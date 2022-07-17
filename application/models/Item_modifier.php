<?php
class Item_modifier extends MY_Model
{	
	
	function get_modifier_item_info($id)
	{
		$this->db->select('modifier_items.name as modifier_item_name, modifiers.name as modifier_name,modifier_items.unit_price,modifier_items.cost_price');
		$this->db->from('modifier_items');	
		$this->db->join('modifiers','modifier_items.modifier_id = modifiers.id');
		$this->db->where('modifier_items.id',$id);
		$query = $this->db->get();
		if($query->num_rows()==1)
		{
			return $query->row_array();
		}
		
		return FALSE;
	
	}
	
	function get_sale_modifier_item_kit_info($modifer_item_kit_id,$sale_id,$item_kit_id,$line)
	{
		$this->db->select('modifier_items.name as modifier_item_name, modifiers.name as modifier_name,sales_item_kits_modifier_items.unit_price,sales_item_kits_modifier_items.cost_price');
		$this->db->from('sales_item_kits_modifier_items');
		$this->db->join('modifier_items','modifier_items.id = sales_item_kits_modifier_items.modifier_item_id');	
		$this->db->join('modifiers','modifier_items.modifier_id = modifiers.id');
		$this->db->where('sales_item_kits_modifier_items.modifier_item_id',$modifer_item_kit_id);
		$this->db->where('sales_item_kits_modifier_items.sale_id',$sale_id);
		$this->db->where('sales_item_kits_modifier_items.item_kit_id',$item_kit_id);
		$this->db->where('sales_item_kits_modifier_items.line',$line);
		
		$query = $this->db->get();
		if($query->num_rows()==1)
		{
			return $query->row_array();
		}
		
		return FALSE;
		
	}
	
	function get_sale_modifier_item_info($modifer_item_id,$sale_id,$item_id,$line)
	{
		$this->db->select('modifier_items.name as modifier_item_name, modifiers.name as modifier_name,sales_items_modifier_items.unit_price,sales_items_modifier_items.cost_price');
		$this->db->from('sales_items_modifier_items');
		$this->db->join('modifier_items','modifier_items.id = sales_items_modifier_items.modifier_item_id');	
		$this->db->join('modifiers','modifier_items.modifier_id = modifiers.id');
		$this->db->where('sales_items_modifier_items.modifier_item_id',$modifer_item_id);
		$this->db->where('sales_items_modifier_items.sale_id',$sale_id);
		$this->db->where('sales_items_modifier_items.item_id',$item_id);
		$this->db->where('sales_items_modifier_items.line',$line);
		
		$query = $this->db->get();
		if($query->num_rows()==1)
		{
			return $query->row_array();
		}
		
		return FALSE;
		
	}
	
	function get_info($id = 0)
	{
		$this->db->from('modifiers');	
		$this->db->where('id',$id);
		$query = $this->db->get();
		
		if($query->num_rows()==1)
		{
			return $query->row();
		}
		else
		{
			$mod_obj=new stdClass();
			
			//Get all the fields from customer table
			$fields = array('id','name','sort_order', 'deleted');
			//append those fields to base parent object, we we have a complete empty object
			foreach ($fields as $field)
			{
				$mod_obj->$field='';
			}
			
			return $mod_obj;
		}
	}
	
	function get_modifiers_for_item(PHPPOSCartItemBase $item)
	{
		if (get_class($item) == 'PHPPOSCartItemSale')
		{
			$this->db->select('modifiers.*');
			$this->db->from('modifiers');
			$this->db->join('items_modifiers','items_modifiers.modifier_id = modifiers.id');
			$this->db->where('modifiers.deleted', 0);
			$this->db->where('items_modifiers.item_id', $item->get_id());
			$this->db->order_by('modifiers.sort_order');
			return $this->db->get();		
		}
		else
		{
			$this->db->select('modifiers.*');
			$this->db->from('modifiers');
			$this->db->join('item_kits_modifiers','item_kits_modifiers.modifier_id = modifiers.id');
			$this->db->where('modifiers.deleted', 0);
			$this->db->where('item_kits_modifiers.item_kit_id', $item->get_id());
			$this->db->order_by('modifiers.sort_order');
			return $this->db->get();		
		}
	}
	
	function get_all()
	{
		$this->db->from('modifiers');
		$this->db->where('deleted', 0);
		$this->db->order_by('sort_order');
		return $this->db->get();
	}
	
	function save($id, $modifier_data, $modifier_items_data,$modifier_items_to_delete)
	{
		if ($id)
		{
			$this->db->where('id',$id);
			$this->db->update('modifiers',$modifier_data);
		}
		else
		{
			$this->db->insert('modifiers',$modifier_data);
			$id = $this->db->insert_id();
		}
		
		if (!is_array($modifier_items_to_delete) && $modifier_items_to_delete === TRUE) //Delete all
		{
			$this->db->where('modifier_id',$id);
			$this->db->update('modifier_items',array('deleted' => 1));
		}
		
		foreach($modifier_items_data as $modifier_item_id => $modifier_item)
		{
			if ($modifier_item_id > 0)
			{
				$this->db->where('id',$modifier_item_id);
				$this->db->update('modifier_items',$modifier_item);
			}
			else
			{
				$modifier_item['modifier_id'] = $id;
				$this->db->insert('modifier_items',$modifier_item);
			}
		}
		
		
		if (is_array($modifier_items_to_delete))
		{
			foreach($modifier_items_to_delete as $modifier_to_delete)
			{
				$this->db->where('id',$modifier_to_delete);
				$this->db->update('modifier_items',array('deleted' => 1));
			}
		}
		return $id;
	}
	
	function delete($id)
	{
		$this->db->where('id',$id);
		return $this->db->update('modifiers',array('deleted' => 1));
	}
	
	function get_modifier_items($modifier_id)
	{
		$this->db->from('modifier_items');
		$this->db->where('modifier_id',$modifier_id);
		$this->db->where('deleted', 0);
		
		return $this->db->get();
	}
	
	function item_has_modifier($item_id,$modifier_id)
	{
		$this->db->from('items_modifiers');
		$this->db->where('item_id',$item_id);
		$this->db->where('modifier_id',$modifier_id);
		
		$query = $this->db->get();

		return ($query->num_rows()==1);
	}
	
	function item_save_modifiers($item_id,$modifier_ids)
	{
		$this->db->where('item_id',$item_id);
		$this->db->delete('items_modifiers');
		
		foreach($modifier_ids as $modifier_id)
		{
			$this->db->insert('items_modifiers',array('item_id' => $item_id,'modifier_id' => $modifier_id));
		}
	}
	
	
	function item_kit_has_modifier($item_kit_id,$modifier_id)
	{
		$this->db->from('item_kits_modifiers');
		$this->db->where('item_kit_id',$item_kit_id);
		$this->db->where('modifier_id',$modifier_id);
		
		$query = $this->db->get();

		return ($query->num_rows()==1);
	}
	
	function item_kit_save_modifiers($item_kit_id,$modifier_ids)
	{
		$this->db->where('item_kit_id',$item_kit_id);
		$this->db->delete('item_kits_modifiers');
		
		foreach($modifier_ids as $modifier_id)
		{
			$this->db->insert('item_kits_modifiers',array('item_kit_id' => $item_kit_id,'modifier_id' => $modifier_id));
		}
	}
	
}
?>