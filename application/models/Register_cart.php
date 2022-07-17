<?php
class Register_cart extends MY_Model
{
	/*
	Gets information about a particular register
	*/
	function get_data($register_id)
	{
		if ($register_id)
		{
			$this->db->from('registers_cart');	
			$this->db->where('register_id',$register_id);
			$query = $this->db->get();
		
			if($query->num_rows()==1)
			{
				$row = $query->row();
				return unserialize($row->data);
			}
		}
		return NULL;
	}
	
	function get_data_for_key($key, $register_id)
	{
		if ($register_id)
		{
			$data = $this->get_data($register_id);
		
			if (isset($data[$key]))
			{
				return $data[$key];
			}
		}
		return NULL;
	}
	
	function exists($register_id)
	{
		$this->db->from('registers_cart');	
		$this->db->where('register_id',$register_id);
		$query = $this->db->get();
		
		return ($query->num_rows()==1);
	}
	
	function add_data($data_to_add, $register_id)
	{
		if ($register_id)
		{
			if (is_array($data_to_add))
			{
				return $this->set_data(array_merge($this->get_data($register_id), $data_to_add), $register_id);
			}
		}
		
		return FALSE;
	}
	
	function remove_data($key, $register_id)
	{
		if ($register_id)
		{
			$data = $this->get_data($register_id);
		
			if (isset($data[$key]))
			{
				unset($data[$key]);
				return $this->set_data($data,$register_id);
			}
		}
		
		return FALSE;
	}
			
	function set_data($data,$register_id)
	{
		if ($register_id)
		{
			$data = array(
	        'data'  => serialize($data),
			);
		
			if ($this->exists($register_id))
			{
				$this->db->where('register_id', $register_id);
				return $this->db->update('registers_cart', $data);
			}
			else
			{
	         $data['register_id'] = $register_id;
				return $this->db->insert('registers_cart', $data);	
			}
		}
		
		return FALSE;
	}
	
	function clear_data($register_id)
	{
		if ($register_id)
		{
			return $this->set_data(NULL, $register_id);
		}
		
		return FALSE;
	}

}
?>
