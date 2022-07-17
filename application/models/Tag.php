<?php
class Tag extends MY_Model
{
	function count_all($show_hidden = TRUE)
	{
		$location_id= $this->Employee->get_logged_in_employee_current_location_id();
		
		$this->db->from('tags');
		if (!$show_hidden)
		{
			$this->db->where('id NOT IN (SELECT tag_id FROM phppos_grid_hidden_tags WHERE location_id='.$location_id.')');
		}
		
		return $this->db->count_all_results();
	}
	
	function get_all_for_ecommerce()
	{
		$this->db->from('tags');
		$return = array();
		
		foreach($this->db->get()->result_array() as $result)
		{
			$return[$result['id']] = array('name' => $result['name'], 'deleted'=> $result['deleted'], 'ecommerce_tag_id'=> $result['ecommerce_tag_id']);
		}
		
		return $return;
	}
	
	function get_all($limit=10000, $offset=0,$col='name',$order='asc',$show_hidden = TRUE)
	{
		$location_id= $this->Employee->get_logged_in_employee_current_location_id();
		
		$this->db->from('tags');
		$this->db->where('deleted', 0);
		
		if (!$this->config->item('speed_up_search_queries'))
		{
			$this->db->order_by($col, $order);
		}
		
		if (!$show_hidden)
		{
			$this->db->where('id NOT IN (SELECT tag_id FROM phppos_grid_hidden_tags WHERE location_id='.$location_id.')');
		}
		
		$this->db->limit($limit);
		$this->db->offset($offset);
		
		$return = array();
		
		foreach($this->db->get()->result_array() as $result)
		{
			$return[$result['id']] = array('name' => $result['name']);
		}
		
		return $return;
	}
	
	function get_multiple_info($tag_ids)
	{
		$this->db->from('tags');
		$this->db->where_in('id',$tag_ids);
		$this->db->order_by("name", "asc");
		return $this->db->get();		
	}
	
	
	function save($tag_name, $tag_id = FALSE,$banned_locations = array())
	{
		if (!$banned_locations)
		{
			$banned_locations = array();
		}
		
		if ($tag_id == FALSE)
		{
			if ($tag_name)
			{
				if($this->db->insert('tags',array('name' => $tag_name, 'last_modified' => date('Y-m-d H:i:s'))))
				{
					$tag_id = $this->db->insert_id();
				}
			}
		}
		else
		{
			$this->db->where('id', $tag_id);
			$this->db->update('tags',array('name' => $tag_name, 'last_modified' => date('Y-m-d H:i:s')));
		}
		
		$this->db->where('tag_id',$tag_id);
		$this->db->delete('location_ban_tags');
		
		
		foreach($banned_locations as $banned_location)
		{
			$this->db->insert('location_ban_tags',array('tag_id' => $tag_id,'location_id' => $banned_location));
		}
		
		return $tag_id;
	}
	
	/*
	Deletes one tag
	*/
	function delete($tag_id)
	{		
		$this->db->where('id', $tag_id);
		return $this->db->update('tags', array('deleted' => 1, 'name' => NULL, 'last_modified' => date('Y-m-d H:i:s')));
	}
	
	
	function get_tags_for_item($item_id)
	{
		$this->db->select('tags.name, tags.id');
		$this->db->from('items_tags');
		$this->db->join('tags', 'items_tags.tag_id=tags.id');
		$this->db->where('items_tags.item_id', $item_id);
		
		$return = array();
		
		foreach($this->db->get()->result_array() as $result)
		{
			$return[] = $result['name'];
		}
		
		return $return;
	}
	
	function get_tags_for_item_kit($item_kit_id)
	{
		$this->db->select('tags.name, tags.id');
		$this->db->from('item_kits_tags');
		$this->db->join('tags', 'item_kits_tags.tag_id=tags.id');
		$this->db->where('item_kits_tags.item_kit_id', $item_kit_id);
		
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
		$this->db->join('tags', 'items_tags.tag_id=tags.id');
		$this->db->where('items_tags.item_id', $item_id);
		
		$return = array();
		
		foreach($this->db->get()->result_array() as $result)
		{
			$return[] = $result['id'];
		}
		
		return $return;
	}
	
	function get_tag_ids_for_item_kit($item_kit_id)
	{
		$this->db->select('tags.id');
		$this->db->from('item_kits_tags');
		$this->db->join('tags', 'item_kits_tags.tag_id=tags.id');
		$this->db->where('item_kits_tags.item_kit_id', $item_kit_id);
		
		$return = array();
		
		foreach($this->db->get()->result_array() as $result)
		{
			$return[] = $result['id'];
		}
		
		return $return;
	}
	
	
	function tag_id_exists($tag_id)	
	{
		$this->db->from('tags');
		$this->db->where('id',$tag_id);
		$query = $this->db->get();

		return ($query->num_rows()==1);
	}
	
	function tag_name_exists($tag_name)
	{
		$this->db->from('tags');
		$this->db->where('name',(string)$tag_name);
		$query = $this->db->get();

		return ($query->num_rows()==1);
	}
	
	function get_tag_id_by_ecommerce_tag_id($ecommerce_tag_id)
	{
		$this->db->from('tags');
		$this->db->where('ecommerce_tag_id', $ecommerce_tag_id);
		
		$query = $this->db->get();

		if($query->num_rows()==1)
		{
			$row = $query->row();
			return $row->id;
		}
		
		return FALSE;
	}
	
	function get_ecommerce_tag_id($tag_id)
	{
		$this->db->from('tags');
		$this->db->where('id', $tag_id);
		
		$query = $this->db->get();

		if($query->num_rows()==1)
		{
			$row = $query->row();
			return $row->ecommerce_tag_id;
		}
		
		return FALSE;
	}
		
	function get_tag_id_by_name($tag_name)
	{
		$this->db->from('tags');
		$this->db->where('name', (string)$tag_name);
		
		$query = $this->db->get();

		if($query->num_rows()==1)
		{
			$row = $query->row();
			return $row->id;
		}
		
		return FALSE;
		
	}
	
	function get_name_by_tag_id($tag_id)
	{
		$this->db->from('tags');
		$this->db->where('id',$tag_id);
		
		$query = $this->db->get();

		if($query->num_rows()==1)
		{
			$row = $query->row();
			return $row->name;
		}
		
		return FALSE;
		
	}
	
	function get_tag_suggestions($search, $limit = 25)
	{
		if (!trim($search))
		{
			return array();
		}
		
			$this->db->select("id,name", FALSE);
			$this->db->from('tags');
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
					$this->db->insert('tags', array('name' => $tag));
					$tag_id = $this->db->insert_id();
				
					$this->db->insert('items_tags', array('item_id' => $item_id, 'tag_id' => $tag_id));
				}
			}
		}
		
		$this->db->where('item_id',$item_id);
		$this->db->update('items',array('tags' => implode(',',$all_tags_list)));
		
		
		return TRUE;
	}
	
	function save_tags_for_item_kit($item_kit_id, $tags)
	{
		//Remove current tags for item
		$this->db->delete('item_kits_tags', array('item_kit_id' => $item_kit_id));
		
		$tags = explode(',', $tags);
		foreach($tags as $tag)
		{
			if ($tag != '')
			{
				$tag = trim($tag);
				
				if (is_numeric($tag) && $this->tag_id_exists($tag)) //Numeric Tag ID
				{
					$this->db->insert('item_kits_tags', array('item_kit_id' => $item_kit_id, 'tag_id' => $tag));
				}
				elseif ($this->tag_name_exists($tag)) //Named tag
				{
					$tag_id = $this->get_tag_id_by_name($tag);
					$this->db->insert('item_kits_tags', array('item_kit_id' => $item_kit_id, 'tag_id' => $tag_id));
				}
				else //Create new tag
				{
					$this->db->insert('tags', array('name' => $tag));
					$tag_id = $this->db->insert_id();
				
					$this->db->insert('item_kits_tags', array('item_kit_id' => $item_kit_id, 'tag_id' => $tag_id));
				}
			}
		}
		
		return TRUE;
	}
	
	
  function search_count_all($search, $deleted=0,$limit = 10000) 
	{
	if (!$deleted)
	{
		$deleted = 0;
	}
		
	$this->db->from('tags');
				 
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
		
		
 		$this->db->from('tags');
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
	
  function get_info($tag_id) {
      $this->db->from('tags');
      $this->db->where('id', ($tag_id));
      $query = $this->db->get();

      if ($query->num_rows() == 1) {
          return $query->row();
      } else {
          //Get empty base parent object, as $supplier_id is NOT an supplier
          $fields = array('id','ecommerce_tag_id','last_modified','deleted','name');			
					
          $tag_obj = new stdClass;
          //Get all the fields from Expenses table
          //append those fields to base parent object, we we have a complete empty object
          foreach ($fields as $field) {
              $tag_obj->$field = '';
          }
          return $tag_obj;
      }
  }
  
  function get_banned_locations($tag_id)
  {
	$this->db->from('location_ban_tags');
	$this->db->where('tag_id',$tag_id);

	$banned_location_ids = array();
	foreach($this->db->get()->result_array() as $row)
	{
		$banned_location_ids[] = $row['location_id'];
	}
	
	return $banned_location_ids;
  	
  }
	
	function add_hidden_tag($tag_id,$location_id = false)
	{
		if (!$location_id)
		{
			$location_id= $this->Employee->get_logged_in_employee_current_location_id();
		}
		
		return $this->db->replace('grid_hidden_tags',array('tag_id' => $tag_id,'location_id' => $location_id));
	}
	
	function remove_hidden_tag($tag_id,$location_id = false)
	{
		if (!$location_id)
		{
			$location_id= $this->Employee->get_logged_in_employee_current_location_id();
		}
		
		$this->db->where('tag_id',$tag_id);
		$this->db->where('location_id',$location_id);
		
		return $this->db->delete('grid_hidden_tags');
	}
	
	function is_tag_hidden($tag_id,$location_id = false)
	{
		if (!$location_id)
		{
			$location_id= $this->Employee->get_logged_in_employee_current_location_id();
		}
		$this->db->from('grid_hidden_tags');
		$this->db->where('tag_id',$tag_id);
		$this->db->where('location_id',$location_id);
		
		$query = $this->db->get();

		return ($query->num_rows()==1);
		
	}
	
	
	
}