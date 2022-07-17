<?php
class Item_attribute_value extends MY_Model
{
	/*
	Gets information about a particular attribute by name or id (string or int)
	*/
	function lookup($attribute_value, $attribute_id, $can_cache = TRUE)
	{
		if ($can_cache)
		{
			static $cache  = array();
		}		
		else
		{
			$cache = array();
		}
		
		if (isset($cache[$attribute_id.'|'.$attribute_value]))
		{
			return $cache[$attribute_id.'|'.$attribute_value];
		}
					
		$this->db->from('attribute_values');
		$this->db->where('attribute_id',$attribute_id);
		$this->db->where('name',(string)$attribute_value);
		
		$query = $this->db->get();

		if($query->num_rows()==1)
		{
			$cache[$attribute_id.'|'.$attribute_value] = $query->row();
			return $cache[$attribute_id.'|'.$attribute_value];
		}
		else
		{
			//Get empty base parent object, as $item_id is NOT an item
			$attribute_obj=new stdClass();

			//Get all the fields from attributes table
			$fields = array('id','ecommerce_attribute_term_id','attribute_id','name','deleted','last_modified');

			foreach ($fields as $field)
			{
				$attribute_obj->$field='';
			}
			
			return $attribute_obj;
		}
	}
	
	
	/*
	Determines if a given method_id is a method
	*/
	function exists($value,$attribute_id)
	{
		$this->db->from('attribute_values');
		$this->db->where('name', $value);
		$this->db->where('attribute_id', $attribute_id);
		
		$query = $this->db->get();
		
		return ($query->num_rows()==1);
	}
	
	function get_attribute_value_id($value,$attribute_id)
	{
		$this->db->from('attribute_values');
		$this->db->where('name', $value);
		$this->db->where('attribute_id', $attribute_id);
		
		$query = $this->db->get();
		
		if ($query->num_rows()==1)
		{
			return $query->row()->id;
		}
		
		return FALSE;
	}
	
	function get_info($id)
	{
		$this->db->from('attribute_values');
		
		$this->db->where('id', $id);
		
		$this->db->limit(1);
		
		return $this->db->get();
	}
	
	
	function get_all_attribute_values_for_ecommerce()
	{
		$this->db->select("attribute_values.name, attribute_values.id, ecommerce_attribute_term_id, attribute_values.attribute_id, attribute_values.deleted, attributes.ecommerce_attribute_id");
		$this->db->from('attribute_values');
		
		$this->db->join('attributes', 'attribute_values.attribute_id = attributes.id');
		
		$this->db->where('attributes.ecommerce_attribute_id!=""');
		$this->db->where('attributes.ecommerce_attribute_id!="0"');
		$this->db->where('attributes.item_id IS NULL');
		
		$this->db->where('attribute_values.deleted', 0);
		
		$this->db->or_group_start();
		$this->db->where('attribute_values.deleted', 1);
		$this->db->where('ecommerce_attribute_term_id IS NOT NULL');
		$this->db->where('attributes.ecommerce_attribute_id!=""');
		$this->db->where('attributes.ecommerce_attribute_id!="0"');
		$this->db->where('attributes.item_id IS NULL');
		$this->db->group_end();
		
		$result = $this->db->get()->result_array();
		$return = array();
		
		foreach($result as $attr_value)
		{
			$return[$attr_value['ecommerce_attribute_id']][] = $attr_value;
		}
		
		return $return;
	}
	
	function get_values_for_attribute($attribute_id)
	{
		if ($attribute_id)
		{
			$this->db->from('attribute_values');
			$this->db->where('deleted', 0);
			$this->db->where('attribute_id',$attribute_id);
			
			return $this->db->get();
		}
		
		return null;
	}
	
	function get_attribute_values_for_item($item_id, $attr_id = NULL)
	{
		$this->db->select('item_attribute_values.*, attribute_values.name as attribute_value_name, attribute_values.ecommerce_attribute_term_id');
		$this->db->from('item_attribute_values');
		$this->db->join('attribute_values', 'attribute_values.id=item_attribute_values.attribute_value_id');
		$this->db->where('item_id', $item_id);
		
		if ($attr_id !== NULL)
		{
			$this->db->where('attribute_id', $attr_id);
		}
		
		return $this->db->get();
	}
	
	function get_attribute_value_suggestions_for_item_variations($item_id, $search, $limit = 25)
	{
		$return = array();
		
		if ($search && !trim($search,":"))
		{
			return $return;
		}
		
		$this->db->select("attribute_values.id as id,  CONCAT(" . $this->db->dbprefix('attributes') . ".name , ': ', " . $this->db->dbprefix('attribute_values') . ".name) as name", false);
		$this->db->from('attribute_values');
		$this->db->join('attributes', 'attribute_values.attribute_id = attributes.id');
		$this->db->join('item_attribute_values', 'attribute_values.id = item_attribute_values.attribute_value_id');

		$this->db->order_by('attribute_values.name');
		
		if($search)
		{
			$this->db->group_start();
			$this->db->like("attribute_values.name",$search,!$this->config->item('speed_up_search_queries') ? 'both' : 'after');
			$this->db->or_like("attributes.name",$search,!$this->config->item('speed_up_search_queries') ? 'both' : 'after');
			$this->db->group_end();
		}

		$this->db->where('attribute_values.deleted',0);
		$this->db->where('item_attribute_values.item_id', $item_id);

		$this->db->limit($limit);

		foreach($this->db->get()->result_array() as $search_result)
		{
			$return[] = array('label' =>$search_result['name'], 'value' => $search_result['id'],);
		}
			
		return $return;
	}
	
	
	function get_attribute_value_suggestions($attribute_id, $search, $limit = 25)
	{
		if (!trim($search))
		{
			return array();
		}
		
		$this->db->select("id, name", FALSE);
		
		$this->db->from('attribute_values');
		
		$this->db->where('attribute_id', $attribute_id);

		$this->db->order_by('attribute_values.name');
		
		$this->db->like("attribute_values.name",$search,!$this->config->item('speed_up_search_queries') ? 'both' : 'after');
		
		$this->db->limit($limit);
		$this->db->where('attribute_values.deleted',0);
		
		$return = array();
		
		foreach($this->db->get()->result_array() as $search_result)
		{
			$return[] = array('label' =>$search_result['name'], 'value' => $search_result['id']);
		}
		
		return $return;
	}
	
	function get_all($value = NULL)
	{
		$this->db->from('attribute_values');
		$this->db->where('deleted', 0);
		
		
		if ($value)
		{
			$this->db->where('name',$value);
		}
		
		
		return $this->db->get();
	}

	function count_all()
	{
		$this->db->from('attribute_values');
		$this->db->where('deleted', 0);
		
		return $this->db->count_all_results();
	}
	
	function delete_all()
	{
		return $this->db->empty_table('attribute_values');
	}
	
	/*
	Inserts or updates an Attribute
	*/
	function save($name, $attribute_id)
	{
		$data = array('name' => $name, 'attribute_id' => $attribute_id);
		$sql = $this->db->insert_string('attribute_values', $data) . ' ON DUPLICATE KEY UPDATE `name`='.$this->db->escape($name).', `deleted` = 0';
		$this->db->query($sql);
		$insert_id = $this->db->insert_id();
				
		if ($insert_id)
		{
			return $insert_id;
		}
		
		$row = $this->lookup($name,$attribute_id);
		return $row->id;
	}
	
	function save_item_attribute_values($item_id, $attribute_values)
	{
		foreach($attribute_values as $attr_value)
		{			
			$this->db->replace('item_attribute_values',array(
				'item_id' => $item_id,
				'attribute_value_id' => $attr_value,
			));
		}
	}
	
	function delete($attribute_id, $name = null)
	{	
		if($name)
		{
			$this->db->where('name', $name);
		}
		
		$this->db->where('attribute_id', $attribute_id);
		return $this->db->update('attribute_values', array('deleted' => 1));
	}
	
	function delete_attribute_value($attribute_value_id)
	{
		$this->db->where('id', $attribute_value_id);
		return $this->db->update('attribute_values', array('deleted' => 1));
	}
	
	function delete_item_attribute_value($item_id, $attribute_value_id)
	{
		$item_variation_attribute_values = $this->db->dbprefix('item_variation_attribute_values');
		$item_variations = $this->db->dbprefix('item_variations');
		
		$this->db->query("DELETE $item_variation_attribute_values 
			FROM $item_variation_attribute_values 
		  INNER JOIN $item_variations ON $item_variation_attribute_values.item_variation_id = $item_variations.id 
			WHERE $item_variations.item_id = ".$this->db->escape($item_id).
			" AND $item_variation_attribute_values.attribute_value_id = ".$this->db->escape($attribute_value_id));
			
		return $this->db->delete('item_attribute_values', array('item_id' => $item_id, 'attribute_value_id' => $attribute_value_id)); 
		
	}
}
?>