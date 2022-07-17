<?php
class Manufacturer extends MY_Model
{
	
	/*
	Gets information about a particular supplier
	*/
	function get_info($manufacturer_id, $can_cache = FALSE)
	{
		if ($can_cache)
		{
			static $cache = array();
		
			if (isset($cache[$manufacturer_id]))
			{
				return $cache[$manufacturer_id];
			}
		}
		else
		{
			$cache = array();
		}
				
		$this->db->from('manufacturers');	
		$this->db->where('id',$manufacturer_id);
		$query = $this->db->get();
		
		if($query->num_rows()==1)
		{
			$cache[$manufacturer_id] = $query->row();
			return $cache[$manufacturer_id];
		}
		else
		{
			$man_obj = new stdclass();
			
			//Get all the fields from manufacturer table
			$fields = array('id','deleted','name');			
			
			//append those fields to base parent object, we we have a complete empty object
			foreach ($fields as $field)
			{
				$man_obj->$field='';
			}
			
			return $man_obj;
		}
	}
	
	function get_multiple_info($manufacturer_ids)
	{
		$this->db->from('manufacturers');
		$this->db->where_in('id',$manufacturer_ids);
		$this->db->order_by("name", "asc");
		return $this->db->get();		
	}
	
	
	
	function count_all()
	{
		$this->db->from('manufacturers');
		return $this->db->count_all_results();
	}
	
	
	function get_all($limit=10000, $offset=0,$col='name',$order='asc')
	{
		$this->db->from('manufacturers');
		$this->db->where('deleted',0);
		if (!$this->config->item('speed_up_search_queries'))
		{
			$this->db->order_by($col, $order);
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
	
	function save($manufacturer_name, $manufacturer_id = FALSE)
	{
		if ($manufacturer_id == FALSE)
		{
			if ($manufacturer_name)
			{
				if($this->db->insert('manufacturers',array('name' => $manufacturer_name)))
				{
					return $this->db->insert_id();
				}
			}
			
			return FALSE;
		}
		else
		{
			$this->db->where('id', $manufacturer_id);
			if ($this->db->update('manufacturers',array('name' => $manufacturer_name)))
			{
				return $manufacturer_id;
			}
		}
		return FALSE;
	}
	
	/*
	Deletes one manufacturer
	*/
	function delete($manufacturer_id)
	{		
		$this->db->where('id', $manufacturer_id);
		return $this->db->update('manufacturers', array('deleted' => 1, 'name' => NULL));
	}
	
		
	function manufacturer_id_exists($manufacturer_id)	
	{
		$this->db->from('manufacturers');
		$this->db->where('id',$manufacturer_id);
		$query = $this->db->get();

		return ($query->num_rows()==1);
	}
	
	function manufacturer_name_exists($manufacturer_name)
	{
		$this->db->from('manufacturers');
		$this->db->where('name',$manufacturer_name);
		$query = $this->db->get();

		return ($query->num_rows()==1);
	}
	
		
	function get_manufacturer_id_by_name($manufacturer_name)
	{
		$this->db->from('manufacturers');
		$this->db->where('name', $manufacturer_name);
		
		$query = $this->db->get();

		if($query->num_rows()==1)
		{
			$row = $query->row();
			return $row->id;
		}
		
		return FALSE;
		
	}
	
	function get_manufacturer_suggestions($search, $limit = 25)
	{
		if (!trim($search))
		{
			return array();
		}
		
			$this->db->select("id,name", FALSE);
			$this->db->from('manufacturers');
			$this->db->order_by('name');
			$this->db->like("name",$search,'after');			
			$this->db->limit($limit);
			$this->db->where('deleted',0);
		
		$return = array();
		
		foreach($this->db->get()->result_array() as $search_result)
		{
			$return[] = array('id' => $search_result['id'],'label' =>$search_result['name'], 'value' => $search_result['id']);
		}
		
		return $return;
	}	
	
  function search_count_all($search, $deleted=0,$limit = 10000) 
	{
	if (!$deleted)
	{
		$deleted = 0;
	}
		
	$this->db->from('manufacturers');
				 
	if ($search)
	{
			$this->db->where("manufacturers.name LIKE '".$this->db->escape_like_str($search)."%' and deleted=$deleted");			
	}
	else
	{
		$this->db->where('manufacturers.deleted',$deleted);
	}

	$this->db->limit($limit);
    $result = $this->db->get();
    return $result->num_rows();
  }

  /*
    Preform a search on manufacturers
   */

  function search($search, $deleted=0,$limit = 20, $offset = 0, $column = 'id', $orderby = 'asc') {
				
		if (!$deleted)
		{
			$deleted = 0;
		}
		
		
 		$this->db->from('manufacturers');
		if ($search)
		{
				$this->db->where("manufacturers.name LIKE '".$this->db->escape_like_str($search)."%' and deleted=$deleted");			
		}
		else
		{
			$this->db->where('manufacturers.deleted',$deleted);
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
}