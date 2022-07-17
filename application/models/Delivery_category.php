<?php
class Delivery_category extends MY_Model
{
	function count_all($show_hidden = TRUE)
	{
		$location_id= $this->Employee->get_logged_in_employee_current_location_id();
		
		$this->db->from('delivery_categories');
		if (!$show_hidden)
		{
			$this->db->where('id NOT IN (SELECT tag_id FROM phppos_grid_hidden_tags WHERE location_id='.$location_id.')');
		}
		
		return $this->db->count_all_results();
	}
	
	function get_all_for_ecommerce()
	{
		$this->db->from('delivery_categories');
		$return = array();
		
		foreach($this->db->get()->result_array() as $result)
		{
			$return[$result['id']] = array('name' => $result['name'], 'deleted'=> $result['deleted'], 'ecommerce_tag_id'=> $result['ecommerce_tag_id']);
		}
		
		return $return;
	}
	
	function get_all($limit=10000, $offset=0, $col='name', $order='asc')
	{
		$this->db->from('delivery_categories');
		$this->db->where('deleted', 0);
		
		if (!$this->config->item('speed_up_search_queries'))
		{
			$this->db->order_by($col, $order);
		}
		
		$this->db->limit($limit);
		$this->db->offset($offset);
		
		$return = array();
		
		foreach($this->db->get()->result_array() as $result)
		{
			$return[$result['id']] = array('name' => $result['name'], 'color' => $result['color']);
		}
		
		return $return;
	}
	
	function get_multiple_info($ids)
	{
		$this->db->from('delivery_categories');
		$this->db->where_in('id',$ids);
		$this->db->order_by("name", "asc");
		return $this->db->get();		
	}
	
	
	function save($name, $color, $id = FALSE)
	{
		if ($id == FALSE)
		{
			if ($name)
			{
				if($this->db->insert('delivery_categories',array('name' => $name, 'color' => $color, 'last_modified' => date('Y-m-d H:i:s'))))
				{
					return $this->db->insert_id();
				}
			}
			
			return FALSE;
		}
		else
		{
			$this->db->where('id', $id);
			if ($this->db->update('delivery_categories',array('name' => $name, 'color' => $color, 'last_modified' => date('Y-m-d H:i:s'))))
			{
				return $id;
			}
		}
		return FALSE;
	}
	
	/*
	Deletes one tag
	*/
	function delete($id)
	{		
		$this->db->where('id', $id);
		return $this->db->update('delivery_categories', array('deleted' => 1, 'name' => NULL, 'last_modified' => date('Y-m-d H:i:s')));
	}
	
	
	function get_tags_for_item($item_id)
	{
		$this->db->select('tags.name, tags.id');
		$this->db->from('items_tags');
		$this->db->join('delivery_categories', 'items_tags.tag_id=tags.id');
		$this->db->where('items_tags.item_id', $item_id);
		
		$return = array();
		
		foreach($this->db->get()->result_array() as $result)
		{
			$return[] = $result['name'];
		}
		
		return $return;
	}

	function get_tag_ids_for_item($item_id)
	{
		$this->db->select('tags.id');
		$this->db->from('items_tags');
		$this->db->join('delivery_categories', 'items_tags.tag_id=tags.id');
		$this->db->where('items_tags.item_id', $item_id);
		
		$return = array();
		
		foreach($this->db->get()->result_array() as $result)
		{
			$return[] = $result['id'];
		}
		
		return $return;
	}
	
	function category_id_exists($id)	
	{
		$this->db->from('delivery_categories');
		$this->db->where('id',$id);
		$query = $this->db->get();

		return ($query->num_rows()==1);
	}
	
	function category_name_exists($name)
	{
		$this->db->from('delivery_categories');
		$this->db->where('name',(string)$name);
		$query = $this->db->get();

		return ($query->num_rows()==1);
	}


		
	function get_category_id_by_name($name)
	{
		$this->db->from('delivery_categories');
		$this->db->where('name', (string)$name);
		
		$query = $this->db->get();

		if($query->num_rows()==1)
		{
			$row = $query->row();
			return $row->id;
		}
		
		return FALSE;
		
	}
	
	function get_name_by_category_id($id)
	{
		$this->db->from('delivery_categories');
		$this->db->where('id',$id);
		
		$query = $this->db->get();

		if($query->num_rows()==1)
		{
			$row = $query->row();
			return $row->name;
		}
		
		return FALSE;
		
	}
	
	function get_category_suggestions($search, $limit = 25)
	{
		if (!trim($search))
		{
			return array();
		}
		
			$this->db->select("id,name", FALSE);
			$this->db->from('delivery_categories');
			$this->db->order_by('name');
			$this->db->like("name",$search,'after');			
			$this->db->limit($limit);
			$this->db->where('deleted',0);
		
		$return = array();
		
		foreach($this->db->get()->result_array() as $search_result)
		{
			$return[] = array('id' =>$search_result['id'], 'label' =>$search_result['name'], 'value' => $search_result['id']);
		}
		
		return $return;
	}
	
	function save_tags_for_item($item_id, $tags)
	{
		//Remove current tags for item
		$this->db->delete('items_tags', array('item_id' => $item_id));
		
		$all_tags_list = array();
		$tags = explode(',', $tags);
		foreach($tags as $tag)
		{
			if ($tag != '')
			{
				$tag = trim($tag);
				
				if (is_numeric($tag) && $this->tag_id_exists($tag)) //Numeric Tag ID
				{
					$all_tags_list[] = $this->get_name_by_tag_id($tag);
					
					$this->db->insert('items_tags', array('item_id' => $item_id, 'tag_id' => $tag));
				}
				elseif ($this->tag_name_exists($tag)) //Named tag
				{
					$all_tags_list[] = $tag;
					
					$tag_id = $this->get_tag_id_by_name($tag);
					$this->db->insert('items_tags', array('item_id' => $item_id, 'tag_id' => $tag_id));
				}
				else //Create new tag
				{
					$all_tags_list[] = $tag;
					$this->db->insert('delivery_categories', array('name' => $tag));
					$tag_id = $this->db->insert_id();
				
					$this->db->insert('items_tags', array('item_id' => $item_id, 'tag_id' => $tag_id));
				}
			}
		}
		
		$this->db->where('item_id',$item_id);
		$this->db->update('items',array('delivery_categories' => implode(',',$all_tags_list)));
		
		
		return TRUE;
	}
	

  function search_count_all($search, $deleted=0,$limit = 10000) 
	{
	if (!$deleted)
	{
		$deleted = 0;
	}
		
	$this->db->from('delivery_categories');
				 
	if ($search)
	{
			$this->db->where("tags.name LIKE '".$this->db->escape_like_str($search)."%' and deleted=$deleted");			
	}
	else
	{
		$this->db->where('tags.deleted',$deleted);
	}

	$this->db->limit($limit);
    $result = $this->db->get();
    return $result->num_rows();
  }

  /*
    Preform a search on tags
   */

  function search($search, $deleted=0,$limit = 20, $offset = 0, $column = 'id', $orderby = 'asc') {
				
		if (!$deleted)
		{
			$deleted = 0;
		}
		
		
 		$this->db->from('delivery_categories');
		if ($search)
		{
				$this->db->where("tags.name LIKE '".$this->db->escape_like_str($search)."%' and deleted=$deleted");			
		}
		else
		{
			$this->db->where('tags.deleted',$deleted);
		}
	
     $this->db->order_by($column,$orderby);
 
     $this->db->limit($limit);
    $this->db->offset($offset);
		$return = array();
		
		foreach($this->db->get()->result_array() as $result)
		{
			$return[$result['id']] = array('name' => $result['name']);
		}
		
		return $return;
		
  }
	
  function get_info($id) {
      $this->db->from('delivery_categories');
      $this->db->where('id', ($id));
      $query = $this->db->get();

      if ($query->num_rows() == 1) {
          return $query->row();
      } else {
          //Get empty base parent object, as $supplier_id is NOT an supplier
          $fields = array('id','color','last_modified','deleted','name');			
					
          $tag_obj = new stdClass;
          //Get all the fields from Expenses table
          //append those fields to base parent object, we we have a complete empty object
          foreach ($fields as $field) {
              $tag_obj->$field = '';
          }
          return $tag_obj;
      }
  }
}