<?php
class Item_attribute extends MY_Model
{
	/*
	Gets information about a particular attribute by name or id (string or int)
	*/
	function get_info($attribute_name_or_id, $can_cache = TRUE, $item_id = null)
	{
		$lookup_field = NULL;
		
		if (is_int($attribute_name_or_id))
		{
			$lookup_field = 'id';
			$attribute_name_or_id = (int)$attribute_name_or_id;
		}
		else
		{
			$lookup_field = 'name';
			$attribute_name_or_id = (string)$attribute_name_or_id;
		}
		
		if ($can_cache)
		{
			static $cache  = array();
		}		
		else
		{
			$cache = array();
		}
		
		if (isset($cache[$lookup_field.'|'.$attribute_name_or_id.'|'.$item_id]))
		{
			return $cache[$lookup_field.'|'.$attribute_name_or_id.'|'.$item_id];
		}
					
		$this->db->from('attributes');
		$this->db->where($lookup_field,$attribute_name_or_id);
		$this->db->where('item_id', $item_id);
		
		$query = $this->db->get();

		if($query->num_rows()>=1)
		{
			$cache[$lookup_field.'|'.$attribute_name_or_id.'|'.$item_id] = $query->row();
			return $cache[$lookup_field.'|'.$attribute_name_or_id.'|'.$item_id];
		}
		else
		{
			//Get empty base parent object, as $item_id is NOT an item
			$attribute_obj=new stdClass();

			//Get all the fields from attributes table
			$fields = array('id','item_id','ecommerce_attribute_id','name','deleted','last_modified');

			foreach ($fields as $field)
			{
				$attribute_obj->$field='';
			}
			
			return $attribute_obj;
		}
	}
	
	function get_available_attributes_for_item($item_id)
	{
		$this->db->from('attributes');
		$this->db->where('id NOT IN (SELECT attribute_id FROM '.$this->db->dbprefix('item_attributes').' WHERE item_id = '.$this->db->escape($item_id).')', NULL, FALSE);
		$this->db->group_start();
		$this->db->where('item_id IS NULL');
		$this->db->or_where("attributes.item_id", $item_id);
		$this->db->group_end();
		$this->db->where('deleted', 0);
	
		$return = array();
		
		foreach($this->db->get()->result_array() as $result)
		{
			$return[$result['id']] = array('name' => $result['name']);
		}
		
		return $return;
	}
	
	function get_attributes_for_item($item_id)
	{
		$this->db->select('id, name');
		$this->db->from('attributes');
		$this->db->join('item_attributes', 'attributes.id = item_attributes.attribute_id');
		$this->db->where('item_attributes.item_id', $item_id);
		$this->db->where('deleted', 0);
		
		return $this->db->get()->result_array();
	}
	
	public function has_attributes($item_id)
	{
		static $number_of_attributes;
		
		//For performance we check if we have any attributes
		if ($number_of_attributes)
		{
			if ($number_of_attributes == 0)
			{
				return false;
			}
		}
		else
		{
			$number_of_attributes = $this->count_all();
		}
		
		if ($number_of_attributes)
		{
			return true;
		}
		
		$this->db->select('id, name');
		$this->db->from('attributes');
		$this->db->join('item_attributes', 'attributes.id = item_attributes.attribute_id');
		$this->db->where('item_attributes.item_id', $item_id);
		$this->db->where('deleted', 0);
		
		return $this->db->count_all_results() > 0;
	}
	
	function get_attributes_for_item_with_attribute_values($item_id)
	{
		$this->db->select('id, name, ecommerce_attribute_id, attributes.item_id');
		$this->db->from('attributes');
		$this->db->join('item_attributes', 'attributes.id = item_attributes.attribute_id');
		$this->db->where('item_attributes.item_id', $item_id);
		$this->db->where('deleted', 0);
		$attrs_for_item = $this->db->get()->result_array();
	
		$return = array();
		
		$this->load->model('Item_attribute_value');
		foreach($attrs_for_item as $attr_item)
		{
			$attr_id = $attr_item['id'];
			$return[$attr_id]['name'] = $attr_item['name'];
			$return[$attr_id]['ecommerce_attribute_id'] = $attr_item['ecommerce_attribute_id'];
			$return[$attr_id]['item_id'] = $attr_item['item_id'];
			
			$attr_values = $this->Item_attribute_value->get_attribute_values_for_item($item_id, $attr_id)->result_array();
			$return[$attr_id]['attr_values'] = array();
			foreach($attr_values as $attr_value)
			{
				$return[$attr_id]['attr_values'][$attr_value['attribute_value_id']] = array(
					"name" => $attr_value['attribute_value_name'],
					"ecommerce_attribute_term_id" => $attr_value['ecommerce_attribute_term_id']
				);
			}
		}
		
		return $return;
	}
	
	function exists($id)
	{
		$this->db->from('attributes');
		$this->db->where('id', $id);
		
		$query = $this->db->get();
		
		return ($query->num_rows()==1);
	}
	
	function attribute_name_exists($attr_name,$item_id = NULL)
	{
		$this->db->from('attributes');
		$this->db->where('name',(string)$attr_name);
		$this->db->where('item_id',$item_id);
		$query = $this->db->get();

		return ($query->num_rows()==1);
	}
	
	function get_attribute_id($attr_name,$item_id = NULL)
	{
		$this->db->select('id');
		$this->db->from('attributes');
		$this->db->where('name',(string)$attr_name);
		$this->db->where('item_id',$item_id);
		$query = $this->db->get();

		if ($query->num_rows()==1)
		{
			return $query->row()->id;
		}
		
		return NULL;
	}
	
	function get_attributes_for_ecommerce($attribute_ids = array())
	{
		$this->db->from('attributes');
		
		if (!empty($attribute_ids))
		{
			if(is_array($attribute_ids))
			{
				$this->db->group_start();
				$attribute_ids_chunk = array_chunk($attribute_ids,25);
				foreach($attribute_ids_chunk as $attribute_ids)
				{
					$this->db->or_where_in('id',$attribute_ids);
				}
				$this->db->group_end();
			}
			else
			{
				$this->db->where('id', $attribute_ids);
			}
		}
		
		$this->db->where('item_id IS NULL');
		
		$this->db->group_start();
		$this->db->where('deleted',0);
		$this->db->group_end();
		
		$this->db->or_group_start();
		$this->db->where('deleted',1);
		$this->db->where('ecommerce_attribute_id!=""');
		$this->db->where('ecommerce_attribute_id!="0"');
		$this->db->group_end();
		
		$attrs = $this->db->get()->result_array();
		
		$return = array();
		
		$this->load->model('Item_attribute_value');
		
		foreach($attrs as $attr)
		{
			$attr_id = $attr['id'];
			$attr_name = $attr['name'];
			$ecommerce_attribute_id = $attr['ecommerce_attribute_id'];
			$deleted = $attr['deleted'];
			
			$return[$attr_id]['name'] = $attr_name;
			$return[$attr_id]['ecommerce_attribute_id'] = $ecommerce_attribute_id;
			$return[$attr_id]['deleted'] = $deleted;
		}
		
		return $return;
	}
	
	function get_all()
	{
		$this->db->from('attributes');
		$this->db->where('deleted', 0);
		
		$this->db->order_by('id');
		return $this->db->get();
	}
	
	function get_all_global()
	{
		$this->db->from('attributes');
		$this->db->where('deleted', 0);
		$this->db->where('item_id IS NULL');
		
		$this->db->order_by('id');
		return $this->db->get();
	}
	
	function get_all_indexed_by_name_and_item_id()
	{
		$this->load->model('Item_attribute_value');
		
		$results = $this->get_all()->result_array();
		
		$return = array();
		foreach($results as $result)
		{
			$terms = $this->Item_attribute_value->get_values_for_attribute($result['id'])->result_array();
			
			foreach($terms as $term)
			{
				$result['terms'][strtoupper($term['name'])] = $term;
			}
			
			$return[strtoupper($result['name'])][$result['item_id'] ? $result['item_id'] : 0] = $result;
		}
		
		return $return;
	}

	function count_all()
	{
		$this->db->from('attributes');
		$this->db->where('deleted', 0);
		
		return $this->db->count_all_results();
	}

	function save(&$attribute_data, $attribute_id = false)
	{	
		$attribute_data['last_modified'] = date('Y-m-d H:i:s');
		
		if (isset($attribute_data['name']) && !$attribute_id)
		{ //lookup existing attribute with same name (even if deleted)
			
			//item_id set for custom attributes
			$item_id = isset($attribute_data['item_id']) ? $attribute_data['item_id'] : null;
			
			$attribute_id = $this->Item_attribute->get_info($attribute_data['name'], true, $item_id)->id;
			
			$attribute_data['deleted'] = 0;
		}
		
		if (!$attribute_id or !$this->exists($attribute_id))
		{
			if($this->db->insert('attributes',$attribute_data))
			{
				$attribute_data['id'] = $this->db->insert_id();
				return $this->db->insert_id();
			}
			return false;
		}

		$this->db->where('id', $attribute_id);
		if($this->db->update('attributes', $attribute_data))
		{
			return $attribute_id;
		} 
		
		return false;		
	}
	
	function save_item_attributes($attribute_ids, $item_id,$delete = TRUE)
	{
		if ($delete)
		{
			$this->db->delete('item_attributes', array('item_id' => $item_id));
		}
		
		foreach($attribute_ids as $attribute_id)
		{
			if ($delete)
			{
				if(!$this->db->insert('item_attributes',array('attribute_id' => $attribute_id,'item_id' => $item_id)))
				{
					return false;
				}
			}
			else
			{
				if(!$this->db->replace('item_attributes',array('attribute_id' => $attribute_id,'item_id' => $item_id)))
				{
					return false;
				}
			}
		}
		
		return true;
	}
	
	function delete($attribute_id)
	{	
		$this->db->where('id', $attribute_id);
		$this->db->update('attributes', array('deleted' => 1, 'last_modified' => date('Y-m-d H:i:s')));
		
		$this->load->model('Item_attribute_value');
		$this->Item_attribute_value->delete($attribute_id);
		
		return true;
	}
	
	function delete_item_attribute($item_id, $attribute_id)
	{
		return $this->db->delete('item_attributes', array('item_id' => $item_id, 'attribute_id' => $attribute_id)); 
	}


  function search_count_all($search, $deleted=0,$limit = 10000) 
	{
	if (!$deleted)
	{
		$deleted = 0;
	}
		
	$this->db->from('attributes');
				 
	if ($search)
	{
			$this->db->where("attributes.name LIKE '".$this->db->escape_like_str($search)."%' and deleted=$deleted");			
	}
	else
	{
		$this->db->where('attributes.deleted',$deleted);
	}

	$this->db->limit($limit);
    $result = $this->db->get();
    return $result->num_rows();
  }

  /*
    Preform a search on attributes
   */

  function search($search, $deleted=0,$limit = 20, $offset = 0, $column = 'id', $orderby = 'asc') {
				
		if (!$deleted)
		{
			$deleted = 0;
		}
		
		
 		$this->db->from('attributes');
		if ($search)
		{
				$this->db->where("attributes.name LIKE '".$this->db->escape_like_str($search)."%' and deleted=$deleted");			
		}
		else
		{
			$this->db->where('attributes.deleted',$deleted);
		}
	
     $this->db->order_by($column,$orderby);
 
     $this->db->limit($limit);
    $this->db->offset($offset);
		$return = array();
		
		return $this->db->get();
  }
	

}
?>